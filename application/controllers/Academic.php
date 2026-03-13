<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Academic Management Controller
 *
 * Unified hub for curriculum planning, academic calendar,
 * master timetable grid, and substitute teacher management.
 *
 * Firebase paths (new):
 *   Schools/{school}/{session}/Academic/Curriculum/{classSection}/{subject}/topics/
 *   Schools/{school}/{session}/Academic/Calendar/{eventId}
 *   Schools/{school}/{session}/Academic/Substitutes/{id}
 *
 * Reads existing paths:
 *   Schools/{school}/{session}/Time_table_settings
 *   Schools/{school}/{session}/Class {n}/Section {X}/Time_table
 *   Schools/{school}/{session}/Teachers
 *   Schools/{school}/Subject_list/{classNum}
 */
class Academic extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ══════════════════════════════════════════════════════════════════════
       PAGE LOAD
    ══════════════════════════════════════════════════════════════════════ */

    public function index()
    {
        $data['session_year'] = $this->session_year;
        $data['school_name']  = $this->school_name;
        $data['school_id']    = $this->school_id;

        $this->load->view('include/header');
        $this->load->view('academic/index', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════════════════
       SHARED: CLASS / SUBJECT / TEACHER DATA
    ══════════════════════════════════════════════════════════════════════ */

    /**
     * Return all class-sections + subjects for dropdowns
     */
    public function get_classes_subjects()
    {
        $school  = $this->school_name;
        $session = $this->session_year;

        $classes  = $this->_get_session_classes();
        $subjects = [];

        // Build subject map per class number
        $subjectList = $this->firebase->get("Schools/{$school}/Subject_list") ?? [];
        if (is_array($subjectList)) {
            foreach ($subjectList as $classNum => $subs) {
                if (!is_array($subs)) continue;
                foreach ($subs as $code => $sub) {
                    if (!is_array($sub)) continue;
                    $subjects[$classNum][] = [
                        'code' => $code,
                        'name' => $sub['subject_name'] ?? $sub['name'] ?? (string) $code,
                    ];
                }
            }
        }

        return $this->json_success([
            'classes'  => $classes,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Return all teachers for the current session (batch read)
     */
    public function get_all_teachers()
    {
        $school    = $this->school_name;
        $school_id = $this->school_id;
        $session   = $this->session_year;

        $teacherIds = $this->firebase->get("Schools/{$school}/{$session}/Teachers") ?? [];
        if (!is_array($teacherIds)) $teacherIds = [];

        // Batch read — single Firebase call instead of N+1
        $allProfiles = $this->firebase->get("Users/Teachers/{$school_id}") ?? [];
        if (!is_array($allProfiles)) $allProfiles = [];

        $teachers = [];
        foreach ($teacherIds as $id => $val) {
            $profile = $allProfiles[$id] ?? [];
            $teachers[] = [
                'id'   => $id,
                'name' => is_array($profile) ? ($profile['Name'] ?? $id) : $id,
            ];
        }

        return $this->json_success(['teachers' => $teachers]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       CURRICULUM PLANNING
    ══════════════════════════════════════════════════════════════════════ */

    public function get_curriculum()
    {
        $classSection = trim($this->input->post('class_section') ?? '');
        $subject      = trim($this->input->post('subject') ?? '');

        if (empty($classSection) || empty($subject)) {
            return $this->json_error('Class and subject required');
        }

        // Sanitize path segments to prevent traversal
        $classSection = str_replace(['/', '\\', '..'], '', $classSection);
        $subject      = str_replace(['/', '\\', '..'], '', $subject);

        $path = "Schools/{$this->school_name}/{$this->session_year}/Academic/Curriculum/{$classSection}/{$subject}";
        $data = $this->firebase->get($path) ?? [];

        $topics = [];
        if (is_array($data) && isset($data['topics']) && is_array($data['topics'])) {
            $topics = array_values($data['topics']);
        }

        return $this->json_success([
            'topics'        => $topics,
            'class_section' => $classSection,
            'subject'       => $subject,
        ]);
    }

    public function save_curriculum()
    {
        $classSection = trim($this->input->post('class_section') ?? '');
        $subject      = trim($this->input->post('subject') ?? '');
        $topicsRaw    = $this->input->post('topics');

        if (empty($classSection) || empty($subject)) {
            return $this->json_error('Class and subject required');
        }

        $classSection = str_replace(['/', '\\', '..'], '', $classSection);
        $subject      = str_replace(['/', '\\', '..'], '', $subject);

        $topics = is_string($topicsRaw) ? json_decode($topicsRaw, true) : $topicsRaw;
        if (!is_array($topics)) $topics = [];

        // Sanitize topics
        $clean = [];
        foreach ($topics as $i => $t) {
            if (!is_array($t) || empty(trim($t['title'] ?? ''))) continue;
            $clean[] = [
                'title'          => trim($t['title']),
                'chapter'        => trim($t['chapter'] ?? ''),
                'est_periods'    => max(0, (int)($t['est_periods'] ?? 0)),
                'status'         => in_array($t['status'] ?? '', ['not_started', 'in_progress', 'completed'])
                                        ? $t['status'] : 'not_started',
                'completed_date' => ($t['status'] ?? '') === 'completed' ? ($t['completed_date'] ?? date('Y-m-d')) : '',
                'sort_order'     => $i,
            ];
        }

        $path = "Schools/{$this->school_name}/{$this->session_year}/Academic/Curriculum/{$classSection}/{$subject}";
        $this->firebase->set($path, ['topics' => $clean, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->json_success(['topics' => $clean]);
    }

    public function update_topic_status()
    {
        $classSection = str_replace(['/', '\\', '..'], '', trim($this->input->post('class_section') ?? ''));
        $subject      = str_replace(['/', '\\', '..'], '', trim($this->input->post('subject') ?? ''));
        $index        = (int)($this->input->post('index') ?? -1);
        $status       = trim($this->input->post('status') ?? '');

        if (empty($classSection) || empty($subject) || $index < 0) {
            return $this->json_error('Invalid parameters');
        }
        if (!in_array($status, ['not_started', 'in_progress', 'completed'])) {
            return $this->json_error('Invalid status');
        }

        $path = "Schools/{$this->school_name}/{$this->session_year}/Academic/Curriculum/{$classSection}/{$subject}/topics/{$index}";
        $update = ['status' => $status];
        if ($status === 'completed') $update['completed_date'] = date('Y-m-d');
        else $update['completed_date'] = '';

        $this->firebase->update($path, $update);

        return $this->json_success(['index' => $index, 'status' => $status]);
    }

    public function delete_topic()
    {
        $classSection = str_replace(['/', '\\', '..'], '', trim($this->input->post('class_section') ?? ''));
        $subject      = str_replace(['/', '\\', '..'], '', trim($this->input->post('subject') ?? ''));
        $index        = (int)($this->input->post('index') ?? -1);

        if (empty($classSection) || empty($subject) || $index < 0) {
            return $this->json_error('Invalid parameters');
        }

        // Read all topics, remove by index, re-index, save
        $path = "Schools/{$this->school_name}/{$this->session_year}/Academic/Curriculum/{$classSection}/{$subject}";
        $data = $this->firebase->get($path) ?? [];
        $topics = (is_array($data) && isset($data['topics'])) ? array_values($data['topics']) : [];

        if (!isset($topics[$index])) {
            return $this->json_error('Topic not found');
        }

        array_splice($topics, $index, 1);
        // Re-index sort_order
        foreach ($topics as $i => &$t) { $t['sort_order'] = $i; }
        unset($t);

        $this->firebase->set($path, ['topics' => $topics, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->json_success(['topics' => $topics]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       ACADEMIC CALENDAR
    ══════════════════════════════════════════════════════════════════════ */

    public function get_calendar_events()
    {
        $month = trim($this->input->post('month') ?? '');  // YYYY-MM or empty for all
        $path  = "Schools/{$this->school_name}/{$this->session_year}/Academic/Calendar";
        $raw   = $this->firebase->get($path) ?? [];

        $events = [];
        if (is_array($raw)) {
            foreach ($raw as $id => $ev) {
                if (!is_array($ev)) continue;
                // Optional month filter — include events that overlap the month
                if ($month !== '') {
                    $evStart = $ev['start_date'] ?? '';
                    $evEnd   = $ev['end_date'] ?? $evStart;
                    $monthStart = $month . '-01';
                    $monthEnd   = date('Y-m-t', strtotime($monthStart));
                    // Skip if event ends before month starts or starts after month ends
                    if ($evEnd < $monthStart || $evStart > $monthEnd) continue;
                }
                $ev['id'] = $id;
                $events[] = $ev;
            }
        }

        // Sort by start_date
        usort($events, fn($a, $b) => strcmp($a['start_date'] ?? '', $b['start_date'] ?? ''));

        return $this->json_success(['events' => $events]);
    }

    public function save_event()
    {
        $id         = trim($this->input->post('id') ?? '');
        $title      = trim($this->input->post('title') ?? '');
        $type       = trim($this->input->post('type') ?? 'event');
        $startDate  = trim($this->input->post('start_date') ?? '');
        $endDate    = trim($this->input->post('end_date') ?? '') ?: $startDate;
        $desc       = trim($this->input->post('description') ?? '');

        if (empty($title) || empty($startDate)) {
            return $this->json_error('Title and start date are required');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !strtotime($startDate)) {
            return $this->json_error('Invalid start date format');
        }
        if ($endDate !== $startDate && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || !strtotime($endDate))) {
            return $this->json_error('Invalid end date format');
        }
        if ($endDate < $startDate) {
            return $this->json_error('End date cannot be before start date');
        }

        $validTypes = ['holiday', 'exam', 'meeting', 'event', 'activity'];
        if (!in_array($type, $validTypes)) $type = 'event';

        $data = [
            'title'       => $title,
            'type'        => $type,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'description' => $desc,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        $basePath = "Schools/{$this->school_name}/{$this->session_year}/Academic/Calendar";

        if ($id !== '') {
            // Update existing
            $this->firebase->update("{$basePath}/{$id}", $data);
        } else {
            // Create new
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->firebase->push($basePath, $data);
        }

        return $this->json_success(['id' => $id, 'event' => $data]);
    }

    public function delete_event()
    {
        $id = trim($this->input->post('id') ?? '');
        if (empty($id)) return $this->json_error('Event ID required');

        $this->firebase->delete(
            "Schools/{$this->school_name}/{$this->session_year}/Academic/Calendar", $id
        );

        return $this->json_success([]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       MASTER TIMETABLE
    ══════════════════════════════════════════════════════════════════════ */

    public function get_master_timetable()
    {
        $school  = $this->school_name;
        $session = $this->session_year;

        // 1. Timetable settings
        $settings = $this->firebase->get("Schools/{$school}/{$session}/Time_table_settings") ?? [];
        if (!is_array($settings)) $settings = [];

        // Normalize recesses
        $recesses = [];
        if (isset($settings['Recesses']) && is_array($settings['Recesses'])) {
            foreach ($settings['Recesses'] as $r) {
                if (is_array($r) && isset($r['after_period'], $r['duration'])) {
                    $recesses[] = ['after_period' => (int)$r['after_period'], 'duration' => (int)$r['duration']];
                }
            }
        }

        $settingsClean = [
            'start_time'       => $settings['Start_time'] ?? '9:00AM',
            'end_time'         => $settings['End_time'] ?? '3:00PM',
            'no_of_periods'    => (int)($settings['No_of_periods'] ?? 6),
            'length_of_period' => (float)($settings['Length_of_period'] ?? 45),
            'recesses'         => $recesses,
        ];

        // 2. All class-section timetables
        $classes    = $this->_get_session_classes();
        $timetables = [];

        foreach ($classes as $cls) {
            $key   = $cls['class_key'];
            $sec   = $cls['section'];
            $label = $cls['label'];
            $path  = "Schools/{$school}/{$session}/{$key}/Section {$sec}/Time_table";
            $tt    = $this->firebase->get($path);
            $timetables[$label] = is_array($tt) ? $tt : [];
        }

        return $this->json_success([
            'settings'   => $settingsClean,
            'timetables' => $timetables,
            'classes'    => $classes,
        ]);
    }

    public function save_period()
    {
        $classKey   = str_replace(['/', '\\', '..'], '', trim($this->input->post('class_key') ?? ''));
        $section    = str_replace(['/', '\\', '..'], '', trim($this->input->post('section') ?? ''));
        $day        = trim($this->input->post('day') ?? '');
        $periodIdx  = (int)($this->input->post('period_index') ?? -1);
        $subject    = trim($this->input->post('subject') ?? '');

        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (empty($classKey) || empty($section) || !in_array($day, $validDays) || $periodIdx < 0) {
            return $this->json_error('Missing or invalid parameters');
        }

        $school  = $this->school_name;
        $session = $this->session_year;

        // Read only the specific day's timetable (not full timetable) to reduce race window
        $dayPath = "Schools/{$school}/{$session}/{$classKey}/Section {$section}/Time_table/{$day}";
        $dayTt   = $this->firebase->get($dayPath) ?? [];
        if (!is_array($dayTt)) $dayTt = [];

        // Pad array if needed
        while (count($dayTt) <= $periodIdx) {
            $dayTt[] = '';
        }

        $dayTt[$periodIdx] = $subject;

        // Write only the specific day back (narrower write = less race risk)
        $this->firebase->set($dayPath, $dayTt);

        return $this->json_success(['day' => $day, 'period_index' => $periodIdx, 'subject' => $subject]);
    }

    /**
     * Detect timetable conflicts: checks if a subject is already assigned
     * to another class-section in the same period on the same day.
     *
     * NOTE: Timetable cells store subject names, not teacher names.
     * So we check if the same subject is double-booked in the same period.
     * For true teacher conflict detection, timetable would need to store
     * teacher assignments per cell.
     */
    public function detect_conflicts()
    {
        $subject   = trim($this->input->post('subject') ?? '');
        $day       = trim($this->input->post('day') ?? '');
        $periodIdx = (int)($this->input->post('period_index') ?? -1);
        $excludeClass   = trim($this->input->post('exclude_class') ?? '');
        $excludeSection = trim($this->input->post('exclude_section') ?? '');

        if (empty($subject) || empty($day) || $periodIdx < 0) {
            return $this->json_success(['conflict' => false]);
        }

        $school  = $this->school_name;
        $session = $this->session_year;
        $classes = $this->_get_session_classes();

        foreach ($classes as $cls) {
            if ($cls['class_key'] === $excludeClass && $cls['section'] === $excludeSection) continue;

            $path = "Schools/{$school}/{$session}/{$cls['class_key']}/Section {$cls['section']}/Time_table/{$day}";
            $periods = $this->firebase->get($path);
            if (!is_array($periods)) continue;

            $subjectAtPeriod = $periods[$periodIdx] ?? '';
            if ($subjectAtPeriod !== '' && strcasecmp($subjectAtPeriod, $subject) === 0) {
                return $this->json_success([
                    'conflict' => true,
                    'message'  => "{$subject} is already assigned to {$cls['label']} on {$day} period " . ($periodIdx + 1),
                ]);
            }
        }

        return $this->json_success(['conflict' => false]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       SUBSTITUTE MANAGEMENT
    ══════════════════════════════════════════════════════════════════════ */

    public function get_substitutes()
    {
        $date     = trim($this->input->post('date') ?? '');     // YYYY-MM-DD
        $dateFrom = trim($this->input->post('date_from') ?? ''); // range start
        $dateTo   = trim($this->input->post('date_to') ?? '');   // range end
        $path     = "Schools/{$this->school_name}/{$this->session_year}/Academic/Substitutes";
        $raw      = $this->firebase->get($path) ?? [];

        $records = [];
        if (is_array($raw)) {
            foreach ($raw as $id => $rec) {
                if (!is_array($rec)) continue;
                $recDate = $rec['date'] ?? '';
                // Single date filter
                if ($date !== '' && $recDate !== $date) continue;
                // Range filter
                if ($dateFrom !== '' && $recDate < $dateFrom) continue;
                if ($dateTo !== '' && $recDate > $dateTo) continue;
                $rec['id'] = $id;
                $records[] = $rec;
            }
        }

        usort($records, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        // Default: limit to 100 most recent if no filter provided
        if ($date === '' && $dateFrom === '' && $dateTo === '') {
            $records = array_slice($records, 0, 100);
        }

        return $this->json_success(['substitutes' => $records]);
    }

    public function save_substitute()
    {
        $id             = trim($this->input->post('id') ?? '');
        $dateStart      = trim($this->input->post('date') ?? '');
        $dateEnd        = trim($this->input->post('date_end') ?? '') ?: $dateStart;
        $absentId       = trim($this->input->post('absent_teacher_id') ?? '');
        $absentName     = trim($this->input->post('absent_teacher_name') ?? '');
        $substituteId   = trim($this->input->post('substitute_teacher_id') ?? '');
        $substituteName = trim($this->input->post('substitute_teacher_name') ?? '');
        $classSection   = str_replace(['/', '\\', '..'], '', trim($this->input->post('class_section') ?? ''));
        $periodsRaw     = $this->input->post('periods');
        $subject        = trim($this->input->post('subject') ?? '');
        $reason         = trim($this->input->post('reason') ?? '');

        // ── Required field validation ──
        if (empty($dateStart) || empty($absentId) || empty($substituteId) || empty($classSection)) {
            return $this->json_error('Date, both teachers, and class are required');
        }

        // ── Date format validation ──
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart) || !strtotime($dateStart)) {
            return $this->json_error('Invalid start date format');
        }
        if ($dateEnd !== $dateStart && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd) || !strtotime($dateEnd))) {
            return $this->json_error('Invalid end date format');
        }
        if ($dateEnd < $dateStart) {
            return $this->json_error('End date cannot be before start date');
        }

        // ── Same teacher check ──
        if ($absentId === $substituteId) {
            return $this->json_error('Absent teacher and substitute cannot be the same person');
        }

        // ── Period validation ──
        $periods = is_string($periodsRaw) ? json_decode($periodsRaw, true) : $periodsRaw;
        if (!is_array($periods)) $periods = [];
        $periods = array_values(array_unique(array_filter(array_map('intval', $periods), fn($p) => $p >= 1)));

        // Validate against timetable settings
        $settings = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Time_table_settings") ?? [];
        $maxPeriods = (int)($settings['No_of_periods'] ?? 10);
        $periods = array_filter($periods, fn($p) => $p <= $maxPeriods);
        sort($periods);

        if (empty($periods)) {
            return $this->json_error('At least one valid period is required (max: ' . $maxPeriods . ')');
        }

        $basePath = "Schools/{$this->school_name}/{$this->session_year}/Academic/Substitutes";

        // ── Duplicate / conflict detection ──
        $existing = $this->firebase->get($basePath) ?? [];
        if (is_array($existing)) {
            foreach ($existing as $exId => $ex) {
                if (!is_array($ex)) continue;
                if ($id !== '' && $exId === $id) continue; // skip self on edit
                if (($ex['status'] ?? '') === 'cancelled') continue;

                $exDate = $ex['date'] ?? '';
                $exDateEnd = $ex['date_end'] ?? $exDate;

                // Check date overlap
                if ($dateStart > $exDateEnd || $dateEnd < $exDate) continue;

                $exPeriods = is_array($ex['periods'] ?? null) ? $ex['periods'] : [];

                // Check 1: Same absent teacher, same class, overlapping periods
                if (($ex['absent_teacher_id'] ?? '') === $absentId
                    && ($ex['class_section'] ?? '') === $classSection
                    && !empty(array_intersect($periods, $exPeriods))) {
                    return $this->json_error('A substitute is already assigned for this teacher, class, and periods on overlapping dates (ID: ' . $exId . ')');
                }

                // Check 2: Substitute teacher double-booked
                if (($ex['substitute_teacher_id'] ?? '') === $substituteId
                    && !empty(array_intersect($periods, $exPeriods))) {
                    return $this->json_error($substituteName . ' is already covering another class during period(s) ' . implode(',', array_intersect($periods, $exPeriods)) . ' on overlapping dates');
                }
            }
        }

        $adminName = $this->session->userdata('admin_name') ?? 'Admin';

        $data = [
            'date'                    => $dateStart,
            'date_end'                => $dateEnd,
            'absent_teacher_id'       => $absentId,
            'absent_teacher_name'     => $absentName,
            'substitute_teacher_id'   => $substituteId,
            'substitute_teacher_name' => $substituteName,
            'class_section'           => $classSection,
            'periods'                 => $periods,
            'subject'                 => $subject,
            'reason'                  => $reason,
            'updated_at'              => date('Y-m-d H:i:s'),
            'updated_by'              => $adminName,
        ];

        if ($id !== '') {
            // Preserve original status on edit
            $current = $this->firebase->get("{$basePath}/{$id}");
            $data['status'] = is_array($current) ? ($current['status'] ?? 'assigned') : 'assigned';
            $data['created_at'] = is_array($current) ? ($current['created_at'] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s');
            $data['created_by'] = is_array($current) ? ($current['created_by'] ?? $adminName) : $adminName;
            $this->firebase->set("{$basePath}/{$id}", $data);
        } else {
            $data['status']     = 'assigned';
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $adminName;
            $id = $this->firebase->push($basePath, $data);
        }

        return $this->json_success(['id' => $id]);
    }

    public function update_substitute()
    {
        $id     = trim($this->input->post('id') ?? '');
        $status = trim($this->input->post('status') ?? '');

        if (empty($id)) return $this->json_error('Substitute ID required');
        if (!in_array($status, ['assigned', 'completed', 'cancelled'])) {
            return $this->json_error('Invalid status');
        }

        $adminName = $this->session->userdata('admin_name') ?? 'Admin';
        $path = "Schools/{$this->school_name}/{$this->session_year}/Academic/Substitutes/{$id}";
        $this->firebase->update($path, [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $adminName,
        ]);

        return $this->json_success(['id' => $id, 'status' => $status]);
    }

    public function delete_substitute()
    {
        $id = trim($this->input->post('id') ?? '');
        if (empty($id)) return $this->json_error('Substitute ID required');

        $this->firebase->delete(
            "Schools/{$this->school_name}/{$this->session_year}/Academic/Substitutes", $id
        );

        return $this->json_success([]);
    }

    /**
     * Get a teacher's timetable schedule for substitute availability check
     */
    public function get_teacher_schedule()
    {
        $teacherId = trim($this->input->post('teacher_id') ?? '');
        $date      = trim($this->input->post('date') ?? '');

        if (empty($teacherId)) return $this->json_error('Teacher ID required');

        $school  = $this->school_name;
        $session = $this->session_year;

        // Determine day of week from date
        $dayName = '';
        if ($date !== '' && strtotime($date)) {
            $dayName = date('l', strtotime($date)); // "Monday", "Tuesday", etc.
        }

        $classes  = $this->_get_session_classes();
        $schedule = [];

        // Also check existing substitute assignments for this date
        $basePath = "Schools/{$school}/{$session}/Academic/Substitutes";
        $allSubs  = $this->firebase->get($basePath) ?? [];
        $busyPeriods = []; // periods where this teacher is already covering

        if (is_array($allSubs)) {
            foreach ($allSubs as $sId => $sub) {
                if (!is_array($sub)) continue;
                if (($sub['status'] ?? '') === 'cancelled') continue;
                $subDate    = $sub['date'] ?? '';
                $subDateEnd = $sub['date_end'] ?? $subDate;
                if ($date < $subDate || $date > $subDateEnd) continue;
                if (($sub['substitute_teacher_id'] ?? '') === $teacherId) {
                    $busyPeriods = array_merge($busyPeriods, is_array($sub['periods'] ?? null) ? $sub['periods'] : []);
                }
            }
        }
        $busyPeriods = array_unique($busyPeriods);

        // Get timetable settings for period count
        $settings   = $this->firebase->get("Schools/{$school}/{$session}/Time_table_settings") ?? [];
        $maxPeriods = (int)($settings['No_of_periods'] ?? 6);

        return $this->json_success([
            'teacher_id'   => $teacherId,
            'date'         => $date,
            'day'          => $dayName,
            'busy_periods' => array_values($busyPeriods),
            'max_periods'  => $maxPeriods,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       HELPERS
    ══════════════════════════════════════════════════════════════════════ */

    private function _get_session_classes()
    {
        $school  = $this->school_name;
        $session = $this->session_year;
        $classes = [];

        $keys = $this->firebase->shallow_get("Schools/{$school}/{$session}");
        if (!is_array($keys)) return $classes;

        foreach ($keys as $key) {
            if (strpos($key, 'Class ') !== 0) continue;
            $sectionKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}/{$key}");
            if (!is_array($sectionKeys)) continue;
            foreach ($sectionKeys as $sk) {
                if (strpos($sk, 'Section ') !== 0) continue;
                $secLetter = str_replace('Section ', '', $sk);
                $classes[] = [
                    'class_key'     => $key,
                    'section'       => $secLetter,
                    'label'         => $key . ' / Section ' . $secLetter,
                    'class_section' => $key . " '" . $secLetter . "'",
                ];
            }
        }
        return $classes;
    }
}
