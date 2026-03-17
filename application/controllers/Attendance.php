<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Advanced Attendance System Controller
 *
 * Features: Student & Staff attendance, Biometric/RFID/Face Recognition integration,
 * Late arrival tracking, Analytics, Mobile API compatibility.
 */
class Attendance extends MY_Controller
{
    /** Roles for attendance settings and device management */
    private const MANAGE_ROLES = ['Admin', 'Principal'];

    /** Roles that may mark attendance */
    private const MARK_ROLES   = ['Admin', 'Principal', 'Teacher'];

    /** Roles that may view attendance data */
    private const VIEW_ROLES   = ['Admin', 'Principal', 'Teacher'];

    /** Routes that skip session auth (use API-key auth instead) */
    protected $public_routes = [
        'admin_login/index',
        'admin_login/check_credentials',
        'admin_login/get_server_date',
        'attendance/api_punch',
    ];

    /** Valid attendance mark characters */
    private $valid_marks = ['P', 'A', 'L', 'H', 'T', 'V'];

    public function __construct()
    {
        parent::__construct();
        // Skip RBAC for API routes (auth handled separately)
        $method = strtolower($this->router->fetch_method());
        if ($method !== 'api_punch') {
            require_permission('Attendance');
        }
    }

    /** Month names → numbers */
    private $month_map = [
        'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
        'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
        'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
    ];

    /** Indian academic year month order */
    private $academic_months = [
        'April','May','June','July','August','September',
        'October','November','December','January','February','March'
    ];

    /* ================================================================
       GROUP A: PAGE LOADS
       ================================================================ */

    /**
     * Dashboard — today's summary cards, recent punches
     */
    public function index()
    {
        $this->_require_role(self::VIEW_ROLES);
        $data = [];
        $this->load->view('include/header', $data);
        $this->load->view('attendance/index', $data);
        $this->load->view('include/footer');
    }

    /**
     * Student attendance marking page
     */
    public function student_attendance()
    {
        $this->_require_role(self::VIEW_ROLES);
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $data['Classes'] = $this->_build_class_list();
        $data['months']  = $this->academic_months;

        $this->load->view('include/header', $data);
        $this->load->view('attendance/student', $data);
        $this->load->view('include/footer');
    }

    /**
     * Staff attendance marking page
     */
    public function staff_attendance()
    {
        $this->_require_role(self::VIEW_ROLES);
        $data['months'] = $this->academic_months;

        $this->load->view('include/header', $data);
        $this->load->view('attendance/staff', $data);
        $this->load->view('include/footer');
    }

    /**
     * Settings page — thresholds, holidays, working days, devices
     */
    public function settings()
    {
        $this->_require_role(self::MANAGE_ROLES, 'att_settings');
        $this->load->view('include/header');
        $this->load->view('attendance/settings');
        $this->load->view('include/footer');
    }

    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $this->_require_role(self::VIEW_ROLES);
        $data['Classes'] = $this->_build_class_list();
        $data['months']  = $this->academic_months;

        $this->load->view('include/header', $data);
        $this->load->view('attendance/analytics', $data);
        $this->load->view('include/footer');
    }

    /**
     * Punch log viewer
     */
    public function punch_log()
    {
        $this->_require_role(self::VIEW_ROLES);
        $this->load->view('include/header');
        $this->load->view('attendance/punch_log');
        $this->load->view('include/footer');
    }

    /* ================================================================
       GROUP B: STUDENT ATTENDANCE AJAX
       ================================================================ */

    /**
     * Fetch attendance grid for a class/section/month
     * POST: class (e.g. "Class 9th"), section ("A"), month ("April")
     */
    public function fetch_student_attendance()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_student_att');
        $class   = trim((string) $this->input->post('class'));
        $section = trim((string) $this->input->post('section'));
        $month   = trim((string) $this->input->post('month'));

        if (!$class || !$section || !$month) {
            return $this->json_error('Class, section, and month are required.');
        }

        $class   = $this->safe_path_segment($class, 'class');
        $section = $this->safe_path_segment($section, 'section');

        // H-01 FIX: Teachers can only view attendance for their assigned classes
        if (!$this->_teacher_can_access($class, "Section {$section}")) {
            return $this->json_error('You are not assigned to this class/section.', 403);
        }

        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        // Resolve section root (new format "Class 8th/Section A" or legacy "Class 8th 'A'")
        $sectionRoot = $this->_resolve_section_root($class, $section);
        $allStudents = $this->firebase->get("{$sectionRoot}/Students");

        if (!is_array($allStudents)) {
            return $this->json_success([
                'students'    => [],
                'daysInMonth' => $daysInMonth,
                'sundays'     => $this->_get_sundays($year, $monthNum),
                'holidays'    => $this->_get_holidays_for_month($month, $year),
                'month'       => $month,
                'year'        => $year,
            ]);
        }

        // Build student list: prefer Students/List index, fall back to student data nodes
        $list = $this->_extract_student_list($allStudents);

        if (empty($list)) {
            return $this->json_success([
                'students'    => [],
                'daysInMonth' => $daysInMonth,
                'sundays'     => $this->_get_sundays($year, $monthNum),
                'holidays'    => $this->_get_holidays_for_month($month, $year),
                'month'       => $month,
                'year'        => $year,
            ]);
        }

        $attKey = "{$month} {$year}";

        // Batch-read all late metadata for this month in 1 read
        $allLate = $this->firebase->get("Schools/{$school}/{$session}/Attendance/Late/{$attKey}");
        if (!is_array($allLate)) $allLate = [];

        $students = [];
        foreach ($list as $studentId => $studentName) {
            if (!is_string($studentId) || trim($studentId) === '') continue;

            // Extract attendance from the batch-read data
            $attStr = '';
            if (isset($allStudents[$studentId]['Attendance'][$attKey])
                && is_string($allStudents[$studentId]['Attendance'][$attKey])) {
                $attStr = $allStudents[$studentId]['Attendance'][$attKey];
            }

            // Pad the attendance string to daysInMonth (JS expects a string, not array)
            $attStr = str_pad($attStr, $daysInMonth, 'V');

            $lateData = isset($allLate[$studentId]) && is_array($allLate[$studentId])
                ? $allLate[$studentId] : [];

            $students[] = [
                'id'         => $studentId,
                'name'       => is_string($studentName) ? $studentName : (string) $studentId,
                'attendance' => $attStr,
                'late'       => $lateData,
            ];
        }

        // Sort by name
        usort($students, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $this->json_success([
            'students'    => $students,
            'daysInMonth' => $daysInMonth,
            'sundays'     => $this->_get_sundays($year, $monthNum),
            'holidays'    => $this->_get_holidays_for_month($month, $year),
            'month'       => $month,
            'year'        => $year,
        ]);
    }

    /**
     * Save full month attendance for multiple students
     * POST: class, section, month, attendance (JSON: {studentId: "PPAPLL...", ...}), late (JSON: {studentId: {day: time}})
     */
    public function save_student_attendance()
    {
        $this->_require_role(self::MARK_ROLES, 'save_student_att');
        $class   = trim((string) $this->input->post('class'));
        $section = trim((string) $this->input->post('section'));
        $month   = trim((string) $this->input->post('month'));
        $attData = $this->input->post('attendance');
        $lateData = $this->input->post('late');

        if (!$class || !$section || !$month || !$attData) {
            return $this->json_error('Missing required fields.');
        }

        $class   = $this->safe_path_segment($class, 'class');
        $section = $this->safe_path_segment($section, 'section');

        // H-01 FIX: Teachers can only mark attendance for their assigned classes
        if (!$this->_teacher_can_access($class, "Section {$section}")) {
            return $this->json_error('You are not assigned to this class/section.', 403);
        }

        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if (is_string($attData)) {
            $attData = json_decode($attData, true);
        }
        if (is_string($lateData)) {
            $lateData = json_decode($lateData, true);
        }

        if (!is_array($attData)) {
            return $this->json_error('Invalid attendance data.');
        }

        $saved = 0;
        foreach ($attData as $studentId => $attString) {
            $studentId = trim((string) $studentId);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $studentId)) continue;

            $attString = strtoupper(trim((string) $attString));
            // Validate each character
            $attString = substr($attString, 0, $daysInMonth);
            $cleanStr = '';
            for ($i = 0; $i < strlen($attString); $i++) {
                $ch = $attString[$i];
                $cleanStr .= in_array($ch, $this->valid_marks) ? $ch : 'V';
            }
            // Pad
            $cleanStr = str_pad($cleanStr, $daysInMonth, 'V');

            $sectionRoot = $this->_resolve_section_root($class, $section);
            $attPath = "{$sectionRoot}/Students/{$studentId}/Attendance/{$attKey}";
            $this->firebase->set($attPath, $cleanStr);
            $saved++;

            // Save late metadata if present
            if (is_array($lateData) && isset($lateData[$studentId]) && is_array($lateData[$studentId])) {
                foreach ($lateData[$studentId] as $day => $time) {
                    $day = (int) $day;
                    if ($day < 1 || $day > $daysInMonth) continue;
                    $time = preg_replace('/[^0-9:]/', '', (string) $time);
                    if ($time) {
                        $latePath = "Schools/{$school}/{$session}/Attendance/Late/{$attKey}/{$studentId}/{$day}";
                        $this->firebase->set($latePath, ['time' => $time]);
                    }
                }
            }
        }

        return $this->json_success(['saved' => $saved]);
    }

    /**
     * Quick-mark single student, single day
     * POST: class, section, month, student_id, day (1-31), mark (P/A/L/H/T)
     */
    public function mark_student_day()
    {
        $this->_require_role(self::MARK_ROLES, 'mark_student_day');
        $class      = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section    = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');
        $month      = trim((string) $this->input->post('month'));
        $studentId  = trim((string) $this->input->post('student_id'));
        $day        = (int) $this->input->post('day');
        $mark       = strtoupper(trim((string) $this->input->post('mark')));
        $lateTime   = trim((string) $this->input->post('late_time'));

        if (!$class || !$section || !$month || !$studentId || !$day || !$mark) {
            return $this->json_error('Missing required fields.');
        }
        // H-01 FIX: Teachers can only mark attendance for their assigned classes
        if (!$this->_teacher_can_access($class, "Section {$section}")) {
            return $this->json_error('You are not assigned to this class/section.', 403);
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $studentId)) {
            return $this->json_error('Invalid student ID.');
        }
        if (!in_array($mark, $this->valid_marks)) {
            return $this->json_error('Invalid attendance mark.');
        }
        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if ($day < 1 || $day > $daysInMonth) {
            return $this->json_error('Invalid day number.');
        }

        // Read existing attendance string
        $sectionRoot = $this->_resolve_section_root($class, $section);
        $attPath = "{$sectionRoot}/Students/{$studentId}/Attendance/{$attKey}";
        $existing = $this->firebase->get($attPath);
        $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
        $attStr = str_pad($attStr, $daysInMonth, 'V');

        // Replace the character at position (day - 1)
        $attStr[$day - 1] = $mark;
        $this->firebase->set($attPath, $attStr);

        // Handle late time — set if T, clean up if changed FROM T
        $latePath = "Schools/{$school}/{$session}/Attendance/Late/{$attKey}/{$studentId}/{$day}";
        if ($mark === 'T' && $lateTime) {
            $lateTime = preg_replace('/[^0-9:]/', '', $lateTime);
            $this->firebase->set($latePath, ['time' => $lateTime]);
        } elseif ($mark !== 'T') {
            $this->firebase->delete($latePath);
        }

        return $this->json_success(['mark' => $mark, 'day' => $day]);
    }

    /**
     * Bulk-mark all students in a section for a specific day
     * POST: class, section, month, day, mark
     */
    public function bulk_mark_student()
    {
        $this->_require_role(self::MARK_ROLES, 'bulk_mark_student');
        $class   = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');
        $month   = trim((string) $this->input->post('month'));
        $day     = (int) $this->input->post('day');
        $mark    = strtoupper(trim((string) $this->input->post('mark')));

        if (!$class || !$section || !$month || !$day || !$mark) {
            return $this->json_error('Missing required fields.');
        }
        // H-01 FIX: Teachers can only bulk-mark attendance for their assigned classes
        if (!$this->_teacher_can_access($class, "Section {$section}")) {
            return $this->json_error('You are not assigned to this class/section.', 403);
        }
        if (!in_array($mark, $this->valid_marks)) {
            return $this->json_error('Invalid mark.');
        }
        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if ($day < 1 || $day > $daysInMonth) {
            return $this->json_error('Invalid day.');
        }

        $sectionRoot = $this->_resolve_section_root($class, $section);
        $allStudents = $this->firebase->get("{$sectionRoot}/Students");
        $list = is_array($allStudents) ? $this->_extract_student_list($allStudents) : [];
        if (empty($list)) {
            return $this->json_error('No students found.');
        }

        $count = 0;
        foreach ($list as $studentId => $name) {
            if (!is_string($studentId) || trim($studentId) === '') continue;

            $attPath = "{$sectionRoot}/Students/{$studentId}/Attendance/{$attKey}";
            $existing = $this->firebase->get($attPath);
            $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
            $attStr = str_pad($attStr, $daysInMonth, 'V');
            $attStr[$day - 1] = $mark;
            $this->firebase->set($attPath, $attStr);
            $count++;
        }

        return $this->json_success(['marked' => $count]);
    }

    /**
     * Individual student attendance summary (full session)
     * POST: student_id, class, section
     */
    public function get_student_summary()
    {
        $this->_require_role(self::VIEW_ROLES, 'student_summary');
        $studentId = trim((string) $this->input->post('student_id'));
        $class     = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section   = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');

        if (!$studentId || !$class || !$section) {
            return $this->json_error('Missing required fields.');
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $studentId)) {
            return $this->json_error('Invalid student ID.');
        }

        $sectionRoot = $this->_resolve_section_root($class, $section);
        $basePath = "{$sectionRoot}/Students/{$studentId}/Attendance";
        $allAtt = $this->firebase->get($basePath);

        $summary = [];
        $totals = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'T' => 0, 'V' => 0, 'total_days' => 0];

        if (is_array($allAtt)) {
            foreach ($allAtt as $monthKey => $attStr) {
                if (!is_string($attStr)) continue;
                $stats = $this->_compute_month_stats($attStr);
                $summary[$monthKey] = $stats;
                foreach (['P', 'A', 'L', 'H', 'T', 'V'] as $ch) {
                    $totals[$ch] += $stats[$ch];
                }
                $totals['total_days'] += strlen($attStr);
            }
        }

        $working = $totals['P'] + $totals['A'] + $totals['L'] + $totals['T'];
        $totals['attendance_pct'] = $working > 0
            ? round(($totals['P'] + $totals['T']) / $working * 100, 1)
            : 0;

        return $this->json_success([
            'months'  => $summary,
            'totals'  => $totals,
        ]);
    }

    /* ================================================================
       GROUP C: STAFF ATTENDANCE AJAX
       ================================================================ */

    /**
     * Fetch staff attendance for a month
     * POST: month
     */
    public function fetch_staff_attendance()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_staff_att');
        $month = trim((string) $this->input->post('month'));

        if (!$month || !isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);
        $attKey = "{$month} {$year}";

        // Batch-read: Teachers node (all profiles), staff attendance, and late data in 3 reads
        $allTeachers = $this->firebase->get("Schools/{$school}/{$session}/Teachers");
        $allStaffAtt = $this->firebase->get("Schools/{$school}/{$session}/Staff_Attendance/{$attKey}");
        $allStaffLate = $this->firebase->get("Schools/{$school}/{$session}/Staff_Attendance/Late/{$attKey}");

        if (!is_array($allStaffAtt)) $allStaffAtt = [];
        if (!is_array($allStaffLate)) $allStaffLate = [];
        $staffList = [];

        if (is_array($allTeachers)) {
            foreach ($allTeachers as $staffId => $profile) {
                if (!is_string($staffId) || trim($staffId) === '') continue;
                $name = is_array($profile) ? ($profile['Name'] ?? $staffId) : (string) $staffId;

                // Extract attendance from batch-read
                $attStr = isset($allStaffAtt[$staffId]) && is_string($allStaffAtt[$staffId])
                    ? $allStaffAtt[$staffId] : '';
                // Pad the attendance string to daysInMonth (JS expects a string, not array)
                $attStr = str_pad($attStr, $daysInMonth, 'V');

                // Extract late data from batch-read
                $lateData = isset($allStaffLate[$staffId]) && is_array($allStaffLate[$staffId])
                    ? $allStaffLate[$staffId] : [];

                $staffList[] = [
                    'id'         => $staffId,
                    'name'       => $name,
                    'attendance' => $attStr,
                    'late'       => $lateData,
                ];
            }
        }

        usort($staffList, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $this->json_success([
            'staff'       => $staffList,
            'daysInMonth' => $daysInMonth,
            'sundays'     => $this->_get_sundays($year, $monthNum),
            'holidays'    => $this->_get_holidays_for_month($month, $year),
            'month'       => $month,
            'year'        => $year,
        ]);
    }

    /**
     * Save staff attendance for a month
     * POST: month, attendance (JSON: {staffId: "PPAP...", ...}), late (JSON)
     */
    public function save_staff_attendance()
    {
        $this->_require_role(self::MARK_ROLES, 'save_staff_att');
        $month   = trim((string) $this->input->post('month'));
        $attData = $this->input->post('attendance');
        $lateData = $this->input->post('late');

        if (!$month || !$attData) {
            return $this->json_error('Missing required fields.');
        }
        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if (is_string($attData)) $attData = json_decode($attData, true);
        if (is_string($lateData)) $lateData = json_decode($lateData, true);
        if (!is_array($attData)) return $this->json_error('Invalid data.');

        $saved = 0;
        foreach ($attData as $staffId => $attString) {
            $staffId = trim((string) $staffId);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $staffId)) continue;

            $cleanStr = $this->_sanitize_att_string($attString, $daysInMonth);

            $attPath = "Schools/{$school}/{$session}/Staff_Attendance/{$attKey}/{$staffId}";
            $this->firebase->set($attPath, $cleanStr);
            $saved++;

            if (is_array($lateData) && isset($lateData[$staffId]) && is_array($lateData[$staffId])) {
                foreach ($lateData[$staffId] as $day => $time) {
                    $day = (int) $day;
                    if ($day < 1 || $day > $daysInMonth) continue;
                    $time = preg_replace('/[^0-9:]/', '', (string) $time);
                    if ($time) {
                        $latePath = "Schools/{$school}/{$session}/Staff_Attendance/Late/{$attKey}/{$staffId}/{$day}";
                        $this->firebase->set($latePath, ['time' => $time]);
                    }
                }
            }
        }

        return $this->json_success(['saved' => $saved]);
    }

    /**
     * Quick-mark single staff member, single day
     * POST: month, staff_id, day, mark, late_time (optional)
     */
    public function mark_staff_day()
    {
        $this->_require_role(self::MARK_ROLES, 'mark_staff_day');
        $month    = trim((string) $this->input->post('month'));
        $staffId  = trim((string) $this->input->post('staff_id'));
        $day      = (int) $this->input->post('day');
        $mark     = strtoupper(trim((string) $this->input->post('mark')));
        $lateTime = trim((string) $this->input->post('late_time'));

        if (!$month || !$staffId || !$day || !$mark) {
            return $this->json_error('Missing required fields.');
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $staffId)) {
            return $this->json_error('Invalid staff ID.');
        }
        if (!in_array($mark, $this->valid_marks)) {
            return $this->json_error('Invalid mark.');
        }
        if (!isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if ($day < 1 || $day > $daysInMonth) {
            return $this->json_error('Invalid day.');
        }

        $attPath = "Schools/{$school}/{$session}/Staff_Attendance/{$attKey}/{$staffId}";
        $existing = $this->firebase->get($attPath);
        $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
        $attStr = str_pad($attStr, $daysInMonth, 'V');
        $attStr[$day - 1] = $mark;
        $this->firebase->set($attPath, $attStr);

        // Handle late time — set if T, clean up if changed FROM T
        $latePath = "Schools/{$school}/{$session}/Staff_Attendance/Late/{$attKey}/{$staffId}/{$day}";
        if ($mark === 'T' && $lateTime) {
            $lateTime = preg_replace('/[^0-9:]/', '', $lateTime);
            $this->firebase->set($latePath, ['time' => $lateTime]);
        } elseif ($mark !== 'T') {
            $this->firebase->delete($latePath);
        }

        return $this->json_success(['mark' => $mark, 'day' => $day]);
    }

    /**
     * Bulk-mark all staff for a day
     * POST: month, day, mark
     */
    public function bulk_mark_staff()
    {
        $this->_require_role(self::MARK_ROLES, 'bulk_mark_staff');
        $month = trim((string) $this->input->post('month'));
        $day   = (int) $this->input->post('day');
        $mark  = strtoupper(trim((string) $this->input->post('mark')));

        if (!$month || !$day || !$mark) {
            return $this->json_error('Missing required fields.');
        }
        if (!in_array($mark, $this->valid_marks) || !isset($this->month_map[$month])) {
            return $this->json_error('Invalid input.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";
        $monthNum = $this->month_map[$month];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        if ($day < 1 || $day > $daysInMonth) {
            return $this->json_error('Invalid day.');
        }

        $teacherKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}/Teachers");
        if (!is_array($teacherKeys)) {
            return $this->json_error('No staff found.');
        }

        $count = 0;
        foreach ($teacherKeys as $staffId => $v) {
            if (!is_string($staffId) || trim($staffId) === '') continue;
            $attPath = "Schools/{$school}/{$session}/Staff_Attendance/{$attKey}/{$staffId}";
            $existing = $this->firebase->get($attPath);
            $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
            $attStr = str_pad($attStr, $daysInMonth, 'V');
            $attStr[$day - 1] = $mark;
            $this->firebase->set($attPath, $attStr);
            $count++;
        }

        return $this->json_success(['marked' => $count]);
    }

    /* ================================================================
       GROUP D: SETTINGS AJAX
       ================================================================ */

    /**
     * Get attendance settings
     */
    public function get_settings()
    {
        $this->_require_role(self::MANAGE_ROLES, 'get_settings');
        $path = "Schools/{$this->school_name}/Config/Attendance";
        $config = $this->firebase->get($path);

        $defaults = [
            'late_threshold_student' => '08:30',
            'late_threshold_staff'   => '09:00',
            'working_days'           => ['Mon','Tue','Wed','Thu','Fri','Sat'],
            'biometric_enabled'      => false,
            'rfid_enabled'           => false,
            'face_recognition_enabled' => false,
        ];

        if (is_array($config)) {
            $config = array_merge($defaults, $config);
        } else {
            $config = $defaults;
        }

        return $this->json_success(['config' => $config]);
    }

    /**
     * Save attendance settings
     * POST: JSON config fields
     */
    public function save_settings()
    {
        $this->_require_role(self::MANAGE_ROLES, 'save_settings');
        $allowed = [
            'late_threshold_student', 'late_threshold_staff',
            'working_days', 'biometric_enabled', 'rfid_enabled', 'face_recognition_enabled',
        ];

        $data = [];
        foreach ($allowed as $key) {
            $val = $this->input->post($key);
            if ($val !== null) {
                if (in_array($key, ['biometric_enabled', 'rfid_enabled', 'face_recognition_enabled'])) {
                    $data[$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                } elseif ($key === 'working_days' && is_string($val)) {
                    $data[$key] = json_decode($val, true) ?: [];
                } else {
                    $data[$key] = $val;
                }
            }
        }

        if (empty($data)) {
            return $this->json_error('No settings to save.');
        }

        $path = "Schools/{$this->school_name}/Config/Attendance";
        $this->firebase->update($path, $data);

        return $this->json_success(['message' => 'Settings saved.']);
    }

    /**
     * Get holidays list
     */
    public function get_holidays()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_holidays');
        $path = "Schools/{$this->school_name}/Config/Attendance/holidays";
        $holidays = $this->firebase->get($path);

        return $this->json_success([
            'holidays' => is_array($holidays) ? $holidays : [],
        ]);
    }

    /**
     * Save holidays
     * POST: holidays (JSON object: {"YYYY-MM-DD": "Holiday Name", ...})
     */
    public function save_holidays()
    {
        $this->_require_role(self::MANAGE_ROLES, 'save_holidays');
        $holidays = $this->input->post('holidays');
        if (is_string($holidays)) {
            $holidays = json_decode($holidays, true);
        }
        if (!is_array($holidays)) {
            return $this->json_error('Invalid holidays data.');
        }

        // Validate date formats and sanitize names
        $clean = [];
        foreach ($holidays as $date => $name) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $clean[$date] = trim((string) $name);
            }
        }

        $path = "Schools/{$this->school_name}/Config/Attendance/holidays";
        $this->firebase->set($path, $clean);

        return $this->json_success(['saved' => count($clean)]);
    }

    /* ================================================================
       GROUP E: DEVICE MANAGEMENT AJAX
       ================================================================ */

    /**
     * Fetch registered devices
     */
    public function fetch_devices()
    {
        $this->_require_role(self::MANAGE_ROLES, 'fetch_devices');
        $path = "Schools/{$this->school_name}/Config/Devices";
        $devices = $this->firebase->get($path);

        $list = [];
        if (is_array($devices)) {
            foreach ($devices as $id => $dev) {
                if (!is_array($dev)) continue;
                $list[] = [
                    'id'        => $id,
                    'name'      => $dev['name'] ?? '',
                    'type'      => $dev['type'] ?? 'unknown',
                    'location'  => $dev['location'] ?? '',
                    'status'    => $dev['status'] ?? 'inactive',
                    'last_ping' => $dev['last_ping'] ?? '',
                    'created_at' => $dev['created_at'] ?? '',
                ];
            }
        }

        return $this->json_success(['devices' => $list]);
    }

    /**
     * Register a new device
     * POST: name, type (biometric|rfid|face_recognition), location
     */
    public function register_device()
    {
        $this->_require_role(self::MANAGE_ROLES, 'register_device');
        $name     = trim((string) $this->input->post('name'));
        $type     = trim((string) $this->input->post('type'));
        $location = trim((string) $this->input->post('location'));

        if (!$name || !$type) {
            return $this->json_error('Device name and type are required.');
        }
        if (!in_array($type, ['biometric', 'rfid', 'face_recognition'])) {
            return $this->json_error('Invalid device type.');
        }

        // Generate API key
        $rawKey  = bin2hex(random_bytes(32));
        $keyHash = hash('sha256', $rawKey);
        $deviceId = 'DEV_' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $deviceData = [
            'name'        => $name,
            'type'        => $type,
            'location'    => $location,
            'status'      => 'active',
            'api_key_hash' => $keyHash,
            'created_at'  => date('c'),
            'last_ping'   => '',
        ];

        // Save device record
        $this->firebase->set("Schools/{$this->school_name}/Config/Devices/{$deviceId}", $deviceData);

        // Save API key lookup — dual-write to both school-scoped and System-level index
        $keyData = [
            'device_id'   => $deviceId,
            'school_name' => $this->school_name,
        ];
        $this->firebase->set("Schools/{$this->school_name}/Config/API_Keys/{$keyHash}", $keyData);
        $this->firebase->set("System/API_Keys/{$keyHash}", $keyData);

        return $this->json_success([
            'device_id' => $deviceId,
            'api_key'   => $rawKey,
            'message'   => 'Device registered. Save the API key — it will not be shown again.',
        ]);
    }

    /**
     * Update device config
     * POST: device_id, name, location, status
     */
    public function update_device()
    {
        $this->_require_role(self::MANAGE_ROLES, 'update_device');
        $deviceId = trim((string) $this->input->post('device_id'));
        if (!$deviceId || !preg_match('/^[A-Za-z0-9_]+$/', $deviceId)) {
            return $this->json_error('Invalid device ID.');
        }

        $updates = [];
        foreach (['name', 'location', 'status'] as $field) {
            $val = $this->input->post($field);
            if ($val !== null) {
                $updates[$field] = trim((string) $val);
            }
        }
        if (isset($updates['status']) && !in_array($updates['status'], ['active', 'inactive'])) {
            return $this->json_error('Invalid status.');
        }

        if (empty($updates)) {
            return $this->json_error('Nothing to update.');
        }

        $path = "Schools/{$this->school_name}/Config/Devices/{$deviceId}";
        $this->firebase->update($path, $updates);

        return $this->json_success(['message' => 'Device updated.']);
    }

    /**
     * Delete a device
     * POST: device_id
     */
    public function delete_device()
    {
        $this->_require_role(self::MANAGE_ROLES, 'delete_device');
        $deviceId = trim((string) $this->input->post('device_id'));
        if (!$deviceId || !preg_match('/^[A-Za-z0-9_]+$/', $deviceId)) {
            return $this->json_error('Invalid device ID.');
        }

        // Get key hash to delete from both API_Keys lookups
        $devPath = "Schools/{$this->school_name}/Config/Devices/{$deviceId}";
        $device = $this->firebase->get($devPath);
        if (is_array($device) && !empty($device['api_key_hash'])) {
            $hash = $device['api_key_hash'];
            $this->firebase->delete("Schools/{$this->school_name}/Config/API_Keys/{$hash}");
            $this->firebase->delete("System/API_Keys/{$hash}");
        }

        $this->firebase->delete($devPath);

        return $this->json_success(['message' => 'Device deleted.']);
    }

    /**
     * Regenerate API key for a device
     * POST: device_id
     */
    public function regenerate_key()
    {
        $this->_require_role(self::MANAGE_ROLES, 'regenerate_key');
        $deviceId = trim((string) $this->input->post('device_id'));
        if (!$deviceId || !preg_match('/^[A-Za-z0-9_]+$/', $deviceId)) {
            return $this->json_error('Invalid device ID.');
        }

        $devPath = "Schools/{$this->school_name}/Config/Devices/{$deviceId}";
        $device = $this->firebase->get($devPath);
        if (!is_array($device)) {
            return $this->json_error('Device not found.');
        }

        // Delete old key lookup from both indexes
        if (!empty($device['api_key_hash'])) {
            $oldHash = $device['api_key_hash'];
            $this->firebase->delete("Schools/{$this->school_name}/Config/API_Keys/{$oldHash}");
            $this->firebase->delete("System/API_Keys/{$oldHash}");
        }

        // Generate new key
        $rawKey  = bin2hex(random_bytes(32));
        $keyHash = hash('sha256', $rawKey);

        $keyData = [
            'device_id'   => $deviceId,
            'school_name' => $this->school_name,
        ];
        $this->firebase->update($devPath, ['api_key_hash' => $keyHash]);
        $this->firebase->set("Schools/{$this->school_name}/Config/API_Keys/{$keyHash}", $keyData);
        $this->firebase->set("System/API_Keys/{$keyHash}", $keyData);

        return $this->json_success([
            'api_key' => $rawKey,
            'message' => 'New API key generated. Save it — it will not be shown again.',
        ]);
    }

    /* ================================================================
       GROUP F: DEVICE API ENDPOINT (API-key auth, no session)
       ================================================================ */

    /**
     * Receive punch from biometric/RFID/face-recognition device
     * POST JSON: { person_id, person_type (student|staff), direction (in|out),
     *              punch_time (ISO8601), confidence (0-1), class, section }
     * Header: X-API-Key: <raw_key>
     */
    public function api_punch()
    {
        header('Content-Type: application/json');

        $auth = $this->_validate_api_key();
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid API key.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body.']);
            return;
        }

        $personId   = trim($input['person_id'] ?? '');
        $personType = trim($input['person_type'] ?? '');
        $direction  = trim($input['direction'] ?? 'in');
        $punchTime  = trim($input['punch_time'] ?? date('c'));
        $confidence = (float) ($input['confidence'] ?? 1.0);
        $class      = trim($input['class'] ?? '');
        $section    = trim($input['section'] ?? '');

        // Sanitize class/section to prevent Firebase path injection (public endpoint)
        if ($class && !preg_match('/^[A-Za-z0-9 \'_\-]+$/', $class)) $class = '';
        if ($section && !preg_match('/^[A-Za-z0-9 \'_\-]+$/', $section)) $section = '';
        if ($direction && !in_array($direction, ['in', 'out'])) $direction = 'in';

        if (!$personId || !$personType) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'person_id and person_type required.']);
            return;
        }
        if (!in_array($personType, ['student', 'staff'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'person_type must be student or staff.']);
            return;
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $personId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid person_id format.']);
            return;
        }

        // ── C-05 FIX: Verify person_id belongs to the authenticated school ──
        $schoolName_pre = $auth['school_name'];
        // Resolve parent_db_key for this school (legacy schools use school_code, SCH_ schools use school_id)
        $schoolMeta = $this->firebase->get("System/Schools/{$schoolName_pre}");
        $parentDbKey = $schoolName_pre; // default
        if (is_array($schoolMeta)) {
            if (!empty($schoolMeta['school_code']) && strpos($schoolName_pre, 'SCH_') !== 0) {
                $parentDbKey = $schoolMeta['school_code'];
            }
        }
        if ($personType === 'student') {
            $personCheck = $this->firebase->get("Users/Parents/{$parentDbKey}/{$personId}/Name");
            if (!$personCheck) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Person ID does not belong to this school.']);
                return;
            }
        } elseif ($personType === 'staff') {
            $staffCheck = $this->firebase->get("Users/Teachers/{$schoolName_pre}/{$personId}/Name");
            if (!$staffCheck) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Staff ID does not belong to this school.']);
                return;
            }
        }

        // Reject low-confidence face recognition punches
        $deviceInfo_pre = $this->firebase->get("Schools/{$auth['school_name']}/Config/Devices/{$auth['device_id']}");
        $devType = is_array($deviceInfo_pre) ? ($deviceInfo_pre['type'] ?? '') : '';
        if ($devType === 'face_recognition' && $confidence < 0.75) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Confidence too low for face recognition.', 'confidence' => $confidence]);
            return;
        }

        $schoolName = $auth['school_name'];
        $deviceId   = $auth['device_id'];

        // Determine session year from school config
        $activeSession = $this->firebase->get("Schools/{$schoolName}/Config/ActiveSession");
        if (!$activeSession) {
            $sessions = $this->firebase->get("System/Schools/{$schoolName}/Sessions");
            $activeSession = is_array($sessions) ? end($sessions) : date('Y') . '-' . (date('Y') + 1);
        }
        $session = is_string($activeSession) ? $activeSession : (string) $activeSession;

        // Device type already fetched during confidence check (reuse $deviceInfo_pre)
        $deviceType = $devType ?: 'unknown';

        // Parse punch time
        $ts = strtotime($punchTime);
        if (!$ts) $ts = time();
        $dateStr = date('Y-m-d', $ts);
        $timeStr = date('H:i', $ts);
        $dayOfMonth = (int) date('j', $ts);
        $monthName  = date('F', $ts);
        $yearNum    = (int) date('Y', $ts);
        $attKey     = "{$monthName} {$yearNum}";
        $monthNum   = (int) date('n', $ts);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $yearNum);

        // Dedup check — reject if same person punched within 5 minutes
        $existingPunches = $this->firebase->get("Schools/{$schoolName}/{$session}/Attendance/Punch_Log/{$dateStr}");
        if (is_array($existingPunches)) {
            $dedupWindow = 300; // 5 minutes
            foreach ($existingPunches as $pId => $pData) {
                if (!is_array($pData)) continue;
                if (($pData['person_id'] ?? '') !== $personId) continue;
                if (($pData['direction'] ?? '') !== $direction) continue;
                $prevTs = strtotime($pData['punch_time'] ?? '');
                if ($prevTs && abs($ts - $prevTs) < $dedupWindow) {
                    http_response_code(409);
                    echo json_encode([
                        'status'  => 'duplicate',
                        'message' => 'Duplicate punch within 5-minute window.',
                        'person_id' => $personId,
                    ]);
                    return;
                }
            }
        }

        // Log punch
        $punchData = [
            'person_id'   => $personId,
            'person_type' => $personType,
            'device_id'   => $deviceId,
            'device_type' => $deviceType,
            'punch_time'  => date('c', $ts),
            'direction'   => $direction,
            'confidence'  => $confidence,
            'processed'   => true,
        ];
        if ($class) $punchData['class'] = $class;
        if ($section) $punchData['section'] = $section;

        $this->firebase->push("Schools/{$schoolName}/{$session}/Attendance/Punch_Log/{$dateStr}", $punchData);

        // Update last_ping on device
        $this->firebase->update("Schools/{$schoolName}/Config/Devices/{$deviceId}", [
            'last_ping' => date('c'),
        ]);

        // Determine mark (P or T based on late threshold)
        $config = $this->firebase->get("Schools/{$schoolName}/Config/Attendance");
        $threshold = '08:30';
        if (is_array($config)) {
            $threshold = $personType === 'staff'
                ? ($config['late_threshold_staff'] ?? '09:00')
                : ($config['late_threshold_student'] ?? '08:30');
        }

        $mark = 'P';
        if ($direction === 'in' && $timeStr > $threshold) {
            $mark = 'T'; // Late
        }

        // Write attendance (only for 'in' direction)
        if ($direction === 'in') {
            if ($personType === 'student' && $class && $section) {
                $secRoot = $this->_resolve_section_root($class, $section);
                $attPath = "{$secRoot}/Students/{$personId}/Attendance/{$attKey}";
                $existing = $this->firebase->get($attPath);
                $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
                $attStr = str_pad($attStr, $daysInMonth, 'V');
                if ($attStr[$dayOfMonth - 1] === 'V') {
                    $attStr[$dayOfMonth - 1] = $mark;
                    $this->firebase->set($attPath, $attStr);
                }

                if ($mark === 'T') {
                    $this->firebase->set(
                        "Schools/{$schoolName}/{$session}/Attendance/Late/{$attKey}/{$personId}/{$dayOfMonth}",
                        ['time' => $timeStr, 'threshold' => $threshold]
                    );
                }
            } elseif ($personType === 'staff') {
                $attPath = "Schools/{$schoolName}/{$session}/Staff_Attendance/{$attKey}/{$personId}";
                $existing = $this->firebase->get($attPath);
                $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
                $attStr = str_pad($attStr, $daysInMonth, 'V');
                if ($attStr[$dayOfMonth - 1] === 'V') {
                    $attStr[$dayOfMonth - 1] = $mark;
                    $this->firebase->set($attPath, $attStr);
                }

                if ($mark === 'T') {
                    $this->firebase->set(
                        "Schools/{$schoolName}/{$session}/Staff_Attendance/Late/{$attKey}/{$personId}/{$dayOfMonth}",
                        ['time' => $timeStr, 'threshold' => $threshold]
                    );
                }
            }
        }

        echo json_encode([
            'status'    => 'success',
            'mark'      => $mark,
            'person_id' => $personId,
            'time'      => $timeStr,
            'direction' => $direction,
        ]);
    }

    /* ================================================================
       GROUP G: ANALYTICS AJAX
       ================================================================ */

    /**
     * Fetch class-wise attendance analytics for a month
     * POST: month, class (optional — if empty, all classes)
     */
    public function fetch_analytics()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_analytics');
        $month = trim((string) $this->input->post('month'));
        $classFilter = trim((string) $this->input->post('class'));

        if (!$month || !isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";

        $classList = $this->_build_class_list();
        $analytics = [];

        foreach ($classList as $cls) {
            $cName = $cls['class_name'];
            $sec   = $cls['section'];

            if ($classFilter && $cName !== $classFilter) continue;

            // Batch-read entire section's Students node (1 read per section)
            $secRoot = $this->_resolve_section_root($cName, $sec);
            $allStudents = $this->firebase->get("{$secRoot}/Students");
            if (!is_array($allStudents)) continue;
            $list = $this->_extract_student_list($allStudents);
            if (empty($list)) continue;

            $classTotals = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'T' => 0, 'V' => 0, 'students' => 0];

            foreach ($list as $studentId => $name) {
                if (!is_string($studentId) || trim($studentId) === '') continue;
                $attStr = isset($allStudents[$studentId]['Attendance'][$attKey])
                    && is_string($allStudents[$studentId]['Attendance'][$attKey])
                    ? $allStudents[$studentId]['Attendance'][$attKey] : '';
                if (!$attStr) continue;

                $stats = $this->_compute_month_stats($attStr);
                foreach (['P', 'A', 'L', 'H', 'T', 'V'] as $ch) {
                    $classTotals[$ch] += $stats[$ch];
                }
                $classTotals['students']++;
            }

            $working = $classTotals['P'] + $classTotals['A'] + $classTotals['L'] + $classTotals['T'];
            $present_pct = $working > 0
                ? round(($classTotals['P'] + $classTotals['T']) / $working * 100, 1)
                : 0;

            $analytics[] = [
                'class'       => $cName,
                'section'     => $sec,
                'label'       => str_replace('Class ', '', $cName) . ' ' . $sec,
                'students'    => $classTotals['students'],
                'present_pct' => $present_pct,
                'absent_pct'  => $working > 0 ? round($classTotals['A'] / $working * 100, 1) : 0,
                'late_count'  => $classTotals['T'],
                'totals'      => $classTotals,
            ];
        }

        return $this->json_success(['analytics' => $analytics, 'month' => $month, 'year' => $year]);
    }

    /**
     * Monthly trend — attendance percentage per month across the session
     * POST: class (optional), section (optional)
     */
    public function fetch_monthly_trend()
    {
        $this->_require_role(self::VIEW_ROLES, 'monthly_trend');
        $classFilter   = trim((string) $this->input->post('class'));
        $sectionFilter = trim((string) $this->input->post('section'));

        $school  = $this->school_name;
        $session = $this->session_year;

        // Build class list ONCE outside the loop (was N calls before)
        $classList = $this->_build_class_list();

        // Pre-fetch all student data per section (1 read per section instead of N per student per month)
        $sectionData = [];
        foreach ($classList as $cls) {
            if ($classFilter && $cls['class_name'] !== $classFilter) continue;
            if ($sectionFilter && $cls['section'] !== $sectionFilter) continue;
            $key = $cls['class_name'] . '|' . $cls['section'];
            $secRoot = $this->_resolve_section_root($cls['class_name'], $cls['section']);
            $sectionData[$key] = $this->firebase->get("{$secRoot}/Students");
        }

        $trend = [];
        foreach ($this->academic_months as $month) {
            $year    = $this->_resolve_year($month);
            $attKey  = "{$month} {$year}";
            $monthNum = $this->month_map[$month];

            $monthEnd = mktime(23, 59, 59, $monthNum, cal_days_in_month(CAL_GREGORIAN, $monthNum, $year), $year);
            if ($monthEnd > time()) {
                continue;
            }

            $totalP = 0; $totalWork = 0;

            foreach ($sectionData as $secKey => $allStudents) {
                if (!is_array($allStudents)) continue;
                $secList = $this->_extract_student_list($allStudents);
                if (empty($secList)) continue;

                foreach ($secList as $studentId => $name) {
                    if (!is_string($studentId)) continue;
                    $attStr = isset($allStudents[$studentId]['Attendance'][$attKey])
                        && is_string($allStudents[$studentId]['Attendance'][$attKey])
                        ? $allStudents[$studentId]['Attendance'][$attKey] : '';
                    if (!$attStr) continue;
                    $stats = $this->_compute_month_stats($attStr);
                    $working = $stats['P'] + $stats['A'] + $stats['L'] + $stats['T'];
                    $totalP += $stats['P'] + $stats['T'];
                    $totalWork += $working;
                }
            }

            $trend[] = [
                'month'       => $month,
                'year'        => $year,
                'present_pct' => $totalWork > 0 ? round($totalP / $totalWork * 100, 1) : 0,
            ];
        }

        return $this->json_success(['trend' => $trend]);
    }

    /**
     * Individual report — single student or staff member full session
     * POST: person_id, person_type (student|staff), class (if student), section (if student)
     */
    public function fetch_individual_report()
    {
        $this->_require_role(self::VIEW_ROLES, 'individual_report');
        $personId   = trim((string) $this->input->post('person_id'));
        $personType = trim((string) $this->input->post('person_type'));
        $class      = trim((string) $this->input->post('class'));
        $section    = trim((string) $this->input->post('section'));

        if (!$personId || !$personType) {
            return $this->json_error('person_id and person_type required.');
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $personId)) {
            return $this->json_error('Invalid person ID.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;

        // Validate class/section before the loop (not inside it)
        if ($personType === 'student') {
            if (!$class || !$section) {
                return $this->json_error('Class and section required for student report.');
            }
            $class   = $this->safe_path_segment($class, 'class');
            $section = $this->safe_path_segment($section, 'section');
        }

        // Look up person name for confirmation
        $personName = '';
        $personClass = '';
        $personSection = '';
        if ($personType === 'student') {
            $profile = $this->firebase->get("Users/Parents/{$this->parent_db_key}/{$personId}");
            if (is_array($profile)) {
                $personName    = $profile['Name'] ?? $profile['name'] ?? '';
                $personClass   = $profile['Class'] ?? '';
                $personSection = $profile['Section'] ?? '';
            }
        } else {
            $staffData = $this->firebase->get("Users/Teachers/{$this->school_id}/{$personId}");
            if (is_array($staffData)) {
                $personName = $staffData['Name'] ?? $staffData['Profile']['name'] ?? '';
            }
        }

        $monthlyData = [];
        $grandTotals = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'T' => 0, 'V' => 0];

        foreach ($this->academic_months as $month) {
            $year   = $this->_resolve_year($month);
            $attKey = "{$month} {$year}";

            if ($personType === 'student') {
                $secRoot = $this->_resolve_section_root($class, $section);
                $attPath = "{$secRoot}/Students/{$personId}/Attendance/{$attKey}";
            } else {
                $attPath = "Schools/{$school}/{$session}/Staff_Attendance/{$attKey}/{$personId}";
            }

            $attStr = $this->firebase->get($attPath);
            if (!is_string($attStr)) {
                $monthlyData[] = ['month' => $month, 'year' => $year, 'stats' => null];
                continue;
            }

            $stats = $this->_compute_month_stats($attStr);
            $working = $stats['P'] + $stats['A'] + $stats['L'] + $stats['T'];
            $stats['present_pct'] = $working > 0
                ? round(($stats['P'] + $stats['T']) / $working * 100, 1)
                : 0;

            $monthlyData[] = ['month' => $month, 'year' => $year, 'stats' => $stats];

            foreach (['P', 'A', 'L', 'H', 'T', 'V'] as $ch) {
                $grandTotals[$ch] += $stats[$ch];
            }
        }

        $gWork = $grandTotals['P'] + $grandTotals['A'] + $grandTotals['L'] + $grandTotals['T'];
        $grandTotals['present_pct'] = $gWork > 0
            ? round(($grandTotals['P'] + $grandTotals['T']) / $gWork * 100, 1)
            : 0;

        return $this->json_success([
            'person_name'    => $personName,
            'person_class'   => $personClass,
            'person_section' => $personSection,
            'person_id'      => $personId,
            'person_type'    => $personType,
            'months'         => $monthlyData,
            'totals'         => $grandTotals,
        ]);
    }

    /**
     * Compute and cache summary for a month
     * POST: month
     */
    public function compute_summary()
    {
        $this->_require_role(self::VIEW_ROLES, 'compute_summary');
        $month = trim((string) $this->input->post('month'));
        if (!$month || !isset($this->month_map[$month])) {
            return $this->json_error('Invalid month.');
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $year    = $this->_resolve_year($month);
        $attKey  = "{$month} {$year}";

        $classList = $this->_build_class_list();
        $summaryPath = "Schools/{$school}/{$session}/Attendance/Summary/Students/{$attKey}";

        foreach ($classList as $cls) {
            $cName = $cls['class_name'];
            $sec   = $cls['section'];
            $csKey = str_replace(' ', '_', $cName) . '_' . $sec;

            // Batch-read entire section's Students node
            $secRoot = $this->_resolve_section_root($cName, $sec);
            $allStudents = $this->firebase->get("{$secRoot}/Students");
            if (!is_array($allStudents)) continue;
            $list = $this->_extract_student_list($allStudents);
            if (empty($list)) continue;

            $studentStats = [];
            $totalStudents = 0;
            $avgPct = 0;

            foreach ($list as $studentId => $name) {
                if (!is_string($studentId)) continue;
                $attStr = isset($allStudents[$studentId]['Attendance'][$attKey])
                    && is_string($allStudents[$studentId]['Attendance'][$attKey])
                    ? $allStudents[$studentId]['Attendance'][$attKey] : '';
                if (!$attStr) continue;

                $stats = $this->_compute_month_stats($attStr);
                $working = $stats['P'] + $stats['A'] + $stats['L'] + $stats['T'];
                $pct = $working > 0 ? round(($stats['P'] + $stats['T']) / $working * 100, 1) : 0;

                $studentStats[$studentId] = array_merge($stats, ['pct' => $pct]);
                $totalStudents++;
                $avgPct += $pct;
            }

            $this->firebase->set("{$summaryPath}/{$csKey}", [
                'total_students'  => $totalStudents,
                'avg_present_pct' => $totalStudents > 0 ? round($avgPct / $totalStudents, 1) : 0,
                'students'        => $studentStats,
            ]);
        }

        return $this->json_success(['message' => 'Summary computed.']);
    }

    /**
     * Fetch punch log for a date
     * POST: date (YYYY-MM-DD)
     */
    public function fetch_punch_log()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_punch_log');
        $date = trim((string) $this->input->post('date'));
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $path = "Schools/{$this->school_name}/{$this->session_year}/Attendance/Punch_Log/{$date}";
        $log = $this->firebase->get($path);

        $punches = [];
        if (is_array($log)) {
            foreach ($log as $id => $punch) {
                if (!is_array($punch)) continue;
                $punch['id'] = $id;
                $punches[] = $punch;
            }
        }

        return $this->json_success(['punches' => $punches, 'date' => $date]);
    }

    /* ================================================================
       GROUP H: MOBILE API (session auth — teacher app)
       ================================================================ */

    /**
     * Get classes/sections the logged-in teacher is assigned to
     */
    public function api_get_classes()
    {
        $this->_require_role(self::VIEW_ROLES, 'api_get_classes');
        header('Content-Type: application/json');
        $classes = $this->_build_class_list();
        return $this->json_success(['classes' => $classes]);
    }

    /**
     * Get student list for a class/section
     * POST: class, section
     */
    public function api_get_students()
    {
        $this->_require_role(self::VIEW_ROLES, 'api_get_students');
        $class   = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');

        if (!$class || !$section) {
            return $this->json_error('Class and section required.');
        }

        $secRoot = $this->_resolve_section_root($class, $section);
        $allStudents = $this->firebase->get("{$secRoot}/Students");
        $list = is_array($allStudents) ? $this->_extract_student_list($allStudents) : [];

        $students = [];
        if (!empty($list)) {
            foreach ($list as $id => $name) {
                if (!is_string($id) || trim($id) === '') continue;
                $students[] = ['id' => $id, 'name' => is_string($name) ? $name : (string) $id];
            }
            usort($students, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        }

        return $this->json_success(['students' => $students]);
    }

    /**
     * Get today's attendance for a class/section
     * POST: class, section
     */
    public function api_get_attendance()
    {
        $this->_require_role(self::VIEW_ROLES, 'api_get_attendance');
        $class   = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');

        if (!$class || !$section) {
            return $this->json_error('Class and section required.');
        }

        $today    = date('j');
        $month    = date('F');
        $year     = (int) date('Y');
        $attKey   = "{$month} {$year}";

        // Batch-read entire section's Students node (1 read instead of N)
        $secRoot = $this->_resolve_section_root($class, $section);
        $allStudents = $this->firebase->get("{$secRoot}/Students");

        $result = [];
        $list = is_array($allStudents) ? $this->_extract_student_list($allStudents) : [];
        if (!empty($list)) {
            foreach ($list as $id => $name) {
                if (!is_string($id) || trim($id) === '') continue;
                $todayMark = 'V';
                if (isset($allStudents[$id]['Attendance'][$attKey])
                    && is_string($allStudents[$id]['Attendance'][$attKey])
                    && strlen($allStudents[$id]['Attendance'][$attKey]) >= $today) {
                    $todayMark = $allStudents[$id]['Attendance'][$attKey][$today - 1];
                }
                $result[] = [
                    'id'   => $id,
                    'name' => is_string($name) ? $name : (string) $id,
                    'mark' => $todayMark,
                ];
            }
            usort($result, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        }

        return $this->json_success([
            'students' => $result,
            'date'     => date('Y-m-d'),
            'month'    => $month,
            'year'     => $year,
            'day'      => (int) $today,
        ]);
    }

    /**
     * Teacher marks attendance for today from mobile app
     * POST: class, section, attendance (JSON: {student_id: "P"|"A"|"L"|"T"|"H", ...}),
     *        late_times (JSON: {student_id: "08:47", ...})
     */
    public function api_mark_attendance()
    {
        $this->_require_role(self::MARK_ROLES, 'api_mark_attendance');
        $class   = $this->safe_path_segment(trim((string) $this->input->post('class')), 'class');
        $section = $this->safe_path_segment(trim((string) $this->input->post('section')), 'section');
        $attData = $this->input->post('attendance');
        $lateTimes = $this->input->post('late_times');

        if (!$class || !$section || !$attData) {
            return $this->json_error('class, section, and attendance required.');
        }
        // H-01 FIX: Teachers can only mark attendance for their assigned classes
        if (!$this->_teacher_can_access($class, "Section {$section}")) {
            return $this->json_error('You are not assigned to this class/section.', 403);
        }

        if (is_string($attData)) $attData = json_decode($attData, true);
        if (is_string($lateTimes)) $lateTimes = json_decode($lateTimes, true);
        if (!is_array($attData)) return $this->json_error('Invalid attendance data.');

        $school  = $this->school_name;
        $session = $this->session_year;
        $today   = (int) date('j');
        $month   = date('F');
        $year    = (int) date('Y');
        $attKey  = "{$month} {$year}";
        $monthNum = (int) date('n');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        $saved = 0;
        foreach ($attData as $studentId => $mark) {
            $studentId = trim((string) $studentId);
            if (!preg_match('/^[A-Za-z0-9_]+$/', $studentId)) continue;
            $mark = strtoupper(trim((string) $mark));
            if (!in_array($mark, $this->valid_marks)) continue;

            $secRoot = $this->_resolve_section_root($class, $section);
            $attPath = "{$secRoot}/Students/{$studentId}/Attendance/{$attKey}";
            $existing = $this->firebase->get($attPath);
            $attStr = is_string($existing) ? $existing : str_repeat('V', $daysInMonth);
            $attStr = str_pad($attStr, $daysInMonth, 'V');
            $attStr[$today - 1] = $mark;
            $this->firebase->set($attPath, $attStr);
            $saved++;

            // Late time
            if ($mark === 'T' && is_array($lateTimes) && !empty($lateTimes[$studentId])) {
                $lateTime = preg_replace('/[^0-9:]/', '', (string) $lateTimes[$studentId]);
                if ($lateTime) {
                    $latePath = "Schools/{$school}/{$session}/Attendance/Late/{$attKey}/{$studentId}/{$today}";
                    $this->firebase->set($latePath, ['time' => $lateTime]);
                }
            }
        }

        return $this->json_success(['saved' => $saved, 'date' => date('Y-m-d')]);
    }

    /* ================================================================
       PRIVATE HELPERS
       ================================================================ */

    /**
     * Resolve calendar year for a month within the academic session
     * April–December → session start year, January–March → session end year
     */
    private function _resolve_year(string $month): int
    {
        $parts = explode('-', $this->session_year);
        $startYear = (int) ($parts[0] ?? date('Y'));
        $endYear   = (int) ($parts[1] ?? ($startYear + 1));

        // Handle 2-digit years (e.g. "25-26" → 2025, 2026)
        if ($startYear < 100) $startYear += 2000;
        if ($endYear < 100)   $endYear += 2000;

        $monthNum = $this->month_map[$month] ?? 0;
        return ($monthNum >= 4) ? $startYear : $endYear;
    }

    /**
     * Get Sunday day numbers for a month
     */
    private function _get_sundays(int $year, int $month): array
    {
        $sundays = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            if (date('w', mktime(0, 0, 0, $month, $d, $year)) == 0) {
                $sundays[] = $d;
            }
        }
        return $sundays;
    }

    /**
     * Get holiday day numbers for a month from config
     */
    private function _get_holidays_for_month(string $monthName, int $year): array
    {
        $config = $this->firebase->get("Schools/{$this->school_name}/Config/Attendance/holidays");
        if (!is_array($config)) return [];

        $monthNum = $this->month_map[$monthName] ?? 0;
        $holidays = [];

        foreach ($config as $date => $name) {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) continue;
            if ((int) $m[1] === $year && (int) $m[2] === $monthNum) {
                $holidays[(int) $m[3]] = is_string($name) ? $name : '';
            }
        }

        return $holidays;
    }

    /**
     * Validate X-API-Key header, return school/device info or false
     */
    private function _validate_api_key()
    {
        $rawKey = $this->input->get_request_header('X-API-Key', true);
        if (!$rawKey) {
            $rawKey = $this->input->get_request_header('X-Api-Key', true);
        }
        if (!$rawKey || strlen($rawKey) < 16) return false;

        // ── C-03 FIX: Rate limit failed API key attempts — max 20 per IP per 15 min ──
        $clientIp = $this->input->ip_address();
        $ipKey    = preg_replace('/[^a-zA-Z0-9]/', '_', $clientIp);
        $ratePath = "System/RateLimits/api_key/{$ipKey}";
        $rateData = $this->firebase->get($ratePath);
        $windowStart = time() - 900;
        if (is_array($rateData)) {
            $recentCount = 0;
            foreach ($rateData as $ts => $v) {
                if ((int) $ts >= $windowStart) $recentCount++;
                else $this->firebase->delete($ratePath, (string) $ts);
            }
            if ($recentCount >= 20) {
                log_message('error', "API key rate limit exceeded for IP: {$clientIp}");
                return false;
            }
        }

        $keyHash = hash('sha256', $rawKey);

        // M-09 FIX: File-based API key cache to avoid Firebase read on every biometric punch.
        // Cache valid keys for 5 minutes. Cache file keyed by key hash.
        $cacheDir  = APPPATH . 'cache/api_keys/';
        $cacheFile = $cacheDir . $keyHash . '.json';
        $cacheTTL  = 300; // 5 minutes

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['school_name'])) {
                return $cached;
            }
        }

        // We need to search across all schools — but we don't know the school yet
        // So we check System-level key index first
        $lookup = $this->firebase->get("System/API_Keys/{$keyHash}");
        if (is_array($lookup) && !empty($lookup['school_name'])) {
            $this->_cache_api_key($cacheDir, $cacheFile, $lookup);
            return $lookup;
        }

        // Fallback: if the school name is passed in the request header — sanitize to prevent path injection
        $schoolHint = trim($_SERVER['HTTP_X_SCHOOL'] ?? '');
        if ($schoolHint && preg_match('/^[A-Za-z0-9 _\-]+$/', $schoolHint)) {
            $lookup = $this->firebase->get("Schools/{$schoolHint}/Config/API_Keys/{$keyHash}");
            if (is_array($lookup)) {
                $lookup['school_name'] = $schoolHint;
                $this->_cache_api_key($cacheDir, $cacheFile, $lookup);
                return $lookup;
            }
        }

        // Log failed attempt for rate limiting
        $this->firebase->set("{$ratePath}/" . time() . '_' . mt_rand(1000, 9999), 1);

        return false;
    }

    /**
     * M-09 FIX: Write validated API key data to file cache.
     */
    private function _cache_api_key(string $cacheDir, string $cacheFile, array $data): void
    {
        try {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0700, true);
            }
            file_put_contents($cacheFile, json_encode($data), LOCK_EX);
            chmod($cacheFile, 0600);
        } catch (Exception $e) {
            log_message('error', 'API key cache write failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract student list from a Students node.
     * Handles two data layouts:
     *   1. Standard: Students/List/{id: name} + Students/{id}/{data}
     *   2. No-List:  Students/{id}/{Name: "...", ...} (List sub-key missing)
     *
     * Returns associative array: [ studentId => studentName, ... ]
     */
    private function _extract_student_list(array $studentsNode): array
    {
        // Prefer the explicit List index
        if (!empty($studentsNode['List']) && is_array($studentsNode['List'])) {
            return $studentsNode['List'];
        }

        // Fallback: build list from student data nodes
        $list = [];
        foreach ($studentsNode as $key => $val) {
            // Skip known non-student keys
            if ($key === 'List' || is_numeric($key)) continue;
            // Student nodes are arrays with a Name field
            if (is_array($val) && isset($val['Name'])) {
                $list[$key] = (string) $val['Name'];
            }
        }
        return $list;
    }

    /**
     * Resolve the section root path, supporting both new and legacy formats.
     *
     * New format:    Schools/{school}/{session}/Class 8th/Section A
     * Legacy format: Schools/{school}/{session}/Class 8th 'A'
     *
     * Checks new format first; falls back to legacy if no Students/List found.
     * Caches per class+section so subsequent calls don't re-read Firebase.
     */
    private $_section_root_cache = [];

    private function _resolve_section_root(string $class, string $section): string
    {
        $cacheKey = "{$class}|{$section}";
        if (isset($this->_section_root_cache[$cacheKey])) {
            return $this->_section_root_cache[$cacheKey];
        }

        $school  = $this->school_name;
        $session = $this->session_year;

        // New format: Class 8th/Section A — check List or direct student keys
        $newRoot = "Schools/{$school}/{$session}/{$class}/Section {$section}";
        $stuKeys = $this->firebase->shallow_get("{$newRoot}/Students");
        if (!empty($stuKeys)) {
            $this->_section_root_cache[$cacheKey] = $newRoot;
            return $newRoot;
        }

        // Legacy format: Class 8th 'A'
        $legacyRoot = "Schools/{$school}/{$session}/{$class} '{$section}'";
        $legacyStuKeys = $this->firebase->shallow_get("{$legacyRoot}/Students");
        if (!empty($legacyStuKeys)) {
            $this->_section_root_cache[$cacheKey] = $legacyRoot;
            return $legacyRoot;
        }

        // Default to new format if neither has students
        $this->_section_root_cache[$cacheKey] = $newRoot;
        return $newRoot;
    }

    /**
     * Build class/section list from session tree.
     * Supports both new format (Class 8th/Section A) and legacy (Class 8th 'A').
     */
    private function _build_class_list(): array
    {
        $school  = $this->school_name;
        $session = $this->session_year;
        $classes = [];
        $seen    = [];

        $keys = $this->firebase->shallow_get("Schools/{$school}/{$session}");
        if (!is_array($keys)) return $classes;

        foreach ($keys as $classKey) {
            if (strpos($classKey, 'Class ') !== 0) continue;

            // New format: "Class 8th" with "Section A" sub-keys
            $sectionKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}/{$classKey}");
            if (is_array($sectionKeys)) {
                foreach ($sectionKeys as $secKey) {
                    if (strpos($secKey, 'Section ') !== 0) continue;
                    $sectionLetter = str_replace('Section ', '', $secKey);
                    $fp = "{$classKey}|{$sectionLetter}";
                    if (!isset($seen[$fp])) {
                        $seen[$fp] = true;
                        $classes[] = [
                            'class_name' => $classKey,
                            'section'    => $sectionLetter,
                        ];
                    }
                }
            }

            // Legacy format: "Class 8th 'A'" — combined key at session level
            if (preg_match("/^(Class\s+\S+)\s+'([A-Z])'\s*$/", $classKey, $m)) {
                $fp = "{$m[1]}|{$m[2]}";
                if (!isset($seen[$fp])) {
                    $seen[$fp] = true;
                    $classes[] = [
                        'class_name' => $m[1],
                        'section'    => $m[2],
                    ];
                }
            }
        }

        return $classes;
    }

    /**
     * Compute P/A/L/H/T/V counts from an attendance string
     */
    private function _compute_month_stats(string $attStr): array
    {
        $stats = ['P' => 0, 'A' => 0, 'L' => 0, 'H' => 0, 'T' => 0, 'V' => 0];
        for ($i = 0; $i < strlen($attStr); $i++) {
            $ch = strtoupper($attStr[$i]);
            if (isset($stats[$ch])) {
                $stats[$ch]++;
            } else {
                $stats['V']++;
            }
        }
        return $stats;
    }

    /**
     * Sanitize an attendance string to only valid characters, padded to length
     */
    private function _sanitize_att_string(string $raw, int $daysInMonth): string
    {
        $raw = strtoupper(trim($raw));
        $raw = substr($raw, 0, $daysInMonth);
        $clean = '';
        for ($i = 0; $i < strlen($raw); $i++) {
            $clean .= in_array($raw[$i], $this->valid_marks) ? $raw[$i] : 'V';
        }
        return str_pad($clean, $daysInMonth, 'V');
    }
}
