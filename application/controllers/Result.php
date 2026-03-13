<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Result controller — Dynamic Result Management System
 *
 * Firebase structure (under Schools/{school}/{year}/Results/):
 *   Templates/{examId}/{classKey}/{sectionKey}/{subject}/
 *     Components/ { 0: {Name:"Theory",MaxMarks:80}, ... }
 *     TotalMaxMarks: 100
 *   Marks/{examId}/{classKey}/{sectionKey}/{subject}/{userId}/
 *     {ComponentName}: marks, Total, Absent, SavedAt
 *   Computed/{examId}/{classKey}/{sectionKey}/{userId}/
 *     TotalMarks, MaxMarks, Percentage, Grade, PassFail, Rank
 *     Subjects/ { English: {Total,MaxMarks,Percentage,Grade,PassFail} }
 *   CumulativeConfig/
 *     Exams/ { EXM0001: {Weight:40,Label:"Mid-Term"}, ... }
 *     TotalWeight: 100
 *   Cumulative/{classKey}/{sectionKey}/{userId}/
 *     WeightedTotal, Grade, PassFail, Rank
 *     Subjects/ { English: {WeightedScore,Grade,PassFail} }
 *
 * classKey  = "Class 9th"   (full prefix)
 * sectionKey= "Section A"   (full prefix)
 *
 * NOTE: compute_grade() thresholds must stay in sync with JS in marks_sheet.php
 *
 * RBAC:
 *   Super Admin / Admin — full access (templates, marks, compute, cumulative)
 *   Teacher             — save_marks (own assigned classes/subjects only),
 *                          view marks_sheet, marks_entry, class_result, student_result
 */
class Result extends MY_Controller
{
    /** Roles allowed to design templates, compute results, configure cumulative. */
    const ADMIN_ROLES = ['Super Admin', 'Admin'];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('exam_engine');
        $this->exam_engine->init($this->firebase, $this->school_name, $this->session_year);
    }

    // ══════════════════════════════════════════════════════════════════
    // PAGE VIEWS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Hub dashboard — shows exam cards with template/marks/computed status.
     */
    public function index()
    {
        $school = $this->school_name;
        $year   = $this->session_year;

        $raw   = $this->firebase->get("Schools/{$school}/{$year}/Exams") ?? [];
        $exams = [];
        foreach ($raw as $id => $e) {
            if ($id === 'Count' || !is_array($e)) continue;
            if (($e['Status'] ?? '') === 'Draft') continue; // only Published/Completed
            $exams[] = array_merge(['id' => $id], $e);
        }
        usort($exams, fn($a, $b) => ($b['CreatedAt'] ?? 0) <=> ($a['CreatedAt'] ?? 0));

        $this->load->view('include/header');
        $this->load->view('result/index', ['exams' => $exams]);
        $this->load->view('include/footer');
    }

    /**
     * Template designer — set components (Theory, Practical, etc.) per subject.
     */
    public function template_designer($examId = null)
    {
        $this->_require_role(self::ADMIN_ROLES, 'design exam template');

        $school    = $this->school_name;
        $year      = $this->session_year;
        $structure = $this->exam_engine->get_class_structure();
        $exams     = $this->exam_engine->get_active_exams();

        $data = [
            'structure' => $structure,
            'exams'     => $exams,
            'examId'    => $examId,
            'exam'      => null,
            'subjects'  => [],
        ];

        if ($examId) {
            $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
            if ($exam && is_array($exam)) {
                $data['exam'] = array_merge(['id' => $examId], $exam);
            }
        }

        $this->load->view('include/header');
        $this->load->view('result/template_designer', $data);
        $this->load->view('include/footer');
    }

    /**
     * Marks entry selector — pick exam, class, section; shows subject grid with progress.
     */
    public function marks_entry($examId = null)
    {
        $school    = $this->school_name;
        $year      = $this->session_year;
        $structure = $this->exam_engine->get_class_structure();
        $exams     = $this->exam_engine->get_active_exams();

        $data = [
            'structure' => $structure,
            'exams'     => $exams,
            'examId'    => $examId,
            'exam'      => null,
        ];

        if ($examId) {
            $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
            if ($exam && is_array($exam)) {
                $data['exam'] = array_merge(['id' => $examId], $exam);
            }
        }

        $this->load->view('include/header');
        $this->load->view('result/marks_entry', $data);
        $this->load->view('include/footer');
    }

    /**
     * Marks sheet — data-entry table for one exam+class+section+subject.
     *
     * URL segments are URL-encoded; decode here.
     */
    public function marks_sheet($examId = null, $classKey = null, $sectionKey = null, $subject = null)
    {
        $school = $this->school_name;
        $year   = $this->session_year;

        // Decode URL segments
        $examId     = $examId     ? urldecode($examId)     : null;
        $classKey   = $classKey   ? urldecode($classKey)   : null;
        $sectionKey = $sectionKey ? urldecode($sectionKey) : null;
        $subject    = $subject    ? urldecode($subject)    : null;

        if (!$examId || !$classKey || !$sectionKey || !$subject) {
            $this->session->set_flashdata('error', 'Missing parameters.');
            redirect('result/marks_entry');
        }

        // Load exam metadata
        $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
        if (!$exam || !is_array($exam)) {
            $this->session->set_flashdata('error', 'Exam not found.');
            redirect('result/marks_entry');
        }
        $exam = array_merge(['id' => $examId], $exam);

        // Guard: template must exist before marks entry
        $template = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}/{$subject}"
        );
        if (!$template || empty($template['Components'])) {
            $this->session->set_flashdata(
                'error',
                "No template found for {$subject}. Please design the template first."
            );
            redirect("result/template_designer/{$examId}");
        }

        // Load student list
        $studentList = $this->firebase->get(
            "Schools/{$school}/{$year}/{$classKey}/{$sectionKey}/Students/List"
        ) ?? [];
        if (!is_array($studentList)) $studentList = [];

        // Load existing marks
        $existingMarks = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subject}"
        ) ?? [];
        if (!is_array($existingMarks)) $existingMarks = [];

        $data = [
            'examId'        => $examId,
            'exam'          => $exam,
            'classKey'      => $classKey,
            'sectionKey'    => $sectionKey,
            'subject'       => $subject,
            'template'      => $template,
            'studentList'   => $studentList,
            'existingMarks' => $existingMarks,
        ];

        $this->load->view('include/header');
        $this->load->view('result/marks_sheet', $data);
        $this->load->view('include/footer');
    }

    /**
     * Class result table — computed results for an exam+class+section.
     */
    public function class_result($examId = null)
    {
        $school    = $this->school_name;
        $year      = $this->session_year;
        $structure = $this->exam_engine->get_class_structure();
        $exams     = $this->exam_engine->get_active_exams();

        $data = [
            'structure' => $structure,
            'exams'     => $exams,
            'examId'    => $examId,
            'exam'      => null,
        ];

        if ($examId) {
            $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
            if ($exam && is_array($exam)) {
                $data['exam'] = array_merge(['id' => $examId], $exam);
            }
        }

        $this->load->view('include/header');
        $this->load->view('result/class_result', $data);
        $this->load->view('include/footer');
    }

    /**
     * Student result — all exams in tabs for one student.
     */
    public function student_result($userId = null)
    {
        $school = $this->school_name;
        $year   = $this->session_year;

        if (!$userId) {
            redirect('result');
        }

        // Load student profile
        $profile = $this->firebase->get("Users/Parents/{$this->parent_db_key}/{$userId}") ?? [];
        if (!is_array($profile)) $profile = [];

        $studentName = $profile['Name'] ?? $profile['name'] ?? 'Unknown Student';
        $className   = $profile['Class']   ?? '';
        $section     = $profile['Section'] ?? '';
        $classKey    = $className ? "Class {$className}" : '';
        $sectionKey  = $section   ? "Section {$section}" : '';

        // Load all active exams
        $exams = $this->exam_engine->get_active_exams();

        // Load computed results for this student across all exams
        $results = [];
        foreach ($exams as $exam) {
            if (!$classKey || !$sectionKey) continue;
            $computed = $this->firebase->get(
                "Schools/{$school}/{$year}/Results/Computed/{$exam['id']}/{$classKey}/{$sectionKey}/{$userId}"
            );
            if ($computed && is_array($computed)) {
                $results[$exam['id']] = $computed;
            }
        }

        $data = [
            'userId'      => $userId,
            'profile'     => $profile,
            'studentName' => $studentName,
            'classKey'    => $classKey,
            'sectionKey'  => $sectionKey,
            'exams'       => $exams,
            'results'     => $results,
        ];

        $this->load->view('include/header');
        $this->load->view('result/student_result', $data);
        $this->load->view('include/footer');
    }

  
    // public function report_card($userId = null, $examId = null)
    // {
    //     $school = $this->school_name;
    //     $year   = $this->session_year;

    //     if (!$userId || !$examId) { redirect('result'); }

    //     // Load exam
    //     $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
    //     if (!$exam || !is_array($exam)) { redirect('result'); }
    //     $exam = array_merge(['id' => $examId], $exam);

    //     // Load student profile
    //     $profile = $this->firebase->get("Users/Parents/{$school}/{$userId}") ?? [];
    //     if (!is_array($profile)) $profile = [];

    //     $className  = $profile['Class']   ?? '';
    //     $section    = $profile['Section'] ?? '';
    //     $classKey   = $className ? "Class {$className}" : '';
    //     $sectionKey = $section   ? "Section {$section}" : '';

    //     // Load computed result
    //     $computed = null;
    //     if ($classKey && $sectionKey) {
    //         $computed = $this->firebase->get(
    //             "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}/{$userId}"
    //         );
    //     }

    //     // Load templates for all subjects (to get component details)
    //     $templates = [];
    //     if ($classKey && $sectionKey) {
    //         $examTemplates = $this->firebase->get(
    //             "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}"
    //         ) ?? [];
    //         if (is_array($examTemplates)) {
    //             $templates = $examTemplates;
    //         }
    //     }

    //     // Load raw marks for this student
    //     $marks = [];
    //     if ($classKey && $sectionKey) {
    //         $subjects = array_keys($templates);
    //         foreach ($subjects as $subj) {
    //             $sm = $this->firebase->get(
    //                 "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subj}/{$userId}"
    //             );
    //             if ($sm && is_array($sm)) {
    //                 $marks[$subj] = $sm;
    //             }
    //         }
    //     }

    //     // Load school info
    //     $schoolInfo = $this->firebase->get("Schools/{$school}/Info") ?? [];
    //     if (!is_array($schoolInfo)) $schoolInfo = [];

    //     $data = [
    //         'userId'      => $userId,
    //         'examId'      => $examId,
    //         'exam'        => $exam,
    //         'profile'     => $profile,
    //         'classKey'    => $classKey,
    //         'sectionKey'  => $sectionKey,
    //         'computed'    => $computed,
    //         'templates'   => $templates,
    //         'marks'       => $marks,
    //         'schoolInfo'  => $schoolInfo,
    //         'schoolName'  => $school,
    //         'sessionYear' => $year,
    //     ];

    //     // Report card is standalone — no header/footer chrome
    //     $this->load->view('result/report_card', $data);
    // }

    /**
     * Cumulative — config (weights) + class-level weighted result table.
     */
    public function report_card($userId = null, $examId = null)
    {
        $school = $this->school_name;
        $year   = $this->session_year;

        if (!$userId || !$examId) {
            redirect('result');
        }

        // ─────────────────────────────
        // Load exam
        // ─────────────────────────────
        $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
        if (!$exam || !is_array($exam)) {
            redirect('result');
        }

        $exam = array_merge(['id' => $examId], $exam);

        // ─────────────────────────────
        // Load student profile
        // ─────────────────────────────
        $school_id  = $this->parent_db_key;
        $profile    = $this->firebase->get("Users/Parents/{$school_id}/{$userId}") ?? [];
        if (!is_array($profile)) $profile = [];

        $className  = trim($profile['Class']   ?? '');
        $section    = trim($profile['Section'] ?? '');

        $classKey   = $className ? "Class {$className}"   : '';
        $sectionKey = $section   ? "Section {$section}"   : '';

        // ─────────────────────────────
        // Load computed result
        // ─────────────────────────────
        $computed = [];
        if ($classKey && $sectionKey) {
            $c = $this->firebase->get(
                "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}/{$userId}"
            );
            if (is_array($c)) $computed = $c;
        }

        if (!is_array($computed) || empty($computed)) {
            $computed = [
                'TotalMarks' => 0,
                'MaxMarks'   => 0,
                'Percentage' => 0,
                'Grade'      => '',
                'PassFail'   => '',
                'Rank'       => '',
                'Subjects'   => [],
            ];
        }

        // ─────────────────────────────
        // Load templates
        // ─────────────────────────────
        $templates = [];
        if ($classKey && $sectionKey) {
            $t = $this->firebase->get(
                "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}"
            );
            if (is_array($t)) $templates = $t;
        }

        // ─────────────────────────────
        // Load marks for student
        // ─────────────────────────────
        $marks = [];
        if ($classKey && $sectionKey) {
            foreach ($templates as $subject => $tmp) {
                $m = $this->firebase->get(
                    "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subject}/{$userId}"
                );
                if (is_array($m)) {
                    $marks[$subject] = $m;
                }
            }
        }

        // ─────────────────────────────
        // Load school info
        // ─────────────────────────────
        $schoolInfo = $this->firebase->get("Schools/{$school}/Info") ?? [];
        if (!is_array($schoolInfo)) $schoolInfo = [];

        // ─────────────────────────────
        // Send to view
        // ─────────────────────────────
        $data = [
            'userId'      => $userId,
            'examId'      => $examId,
            'exam'        => $exam,
            'profile'     => $profile,
            'classKey'    => $classKey,
            'sectionKey'  => $sectionKey,
            'computed'    => $computed,
            'templates'   => $templates,
            'marks'       => $marks,
            'schoolInfo'  => $schoolInfo,
            'schoolName'  => $school,
            'sessionYear' => $year,
        ];

        $this->load->view('result/report_card', $data);
    }


    public function cumulative()
    {
        $this->_require_role(self::ADMIN_ROLES, 'cumulative results');

        $school    = $this->school_name;
        $year      = $this->session_year;
        $structure = $this->exam_engine->get_class_structure();
        $exams     = $this->exam_engine->get_active_exams();

        $config = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/CumulativeConfig"
        ) ?? [];
        if (!is_array($config)) $config = [];

        $data = [
            'structure' => $structure,
            'exams'     => $exams,
            'config'    => $config,
        ];

        $this->load->view('include/header');
        $this->load->view('result/cumulative', $data);
        $this->load->view('include/footer');
    }

    // ══════════════════════════════════════════════════════════════════
    // AJAX ENDPOINTS
    // ══════════════════════════════════════════════════════════════════

    /**
     * POST AJAX — Save component definitions (template) for exam+class+section+subject.
     */
    public function save_template()
    {
        $this->_require_role(self::ADMIN_ROLES, 'save exam template');
        header('Content-Type: application/json');

        $school = $this->school_name;
        $year   = $this->session_year;

        $examId     = trim((string) $this->input->post('examId'));
        $classKey   = trim((string) $this->input->post('classKey'));
        $sectionKey = trim((string) $this->input->post('sectionKey'));
        $subject    = trim((string) $this->input->post('subject'));
        $compsJson  = (string) $this->input->post('components');

        if (!$examId || !$classKey || !$sectionKey || !$subject) {
            $this->json_error('Missing required fields.', 400);
        }
        if (strpos($classKey, 'Class ') !== 0) {
            $this->json_error('Invalid class key.', 400);
        }
        if (strpos($sectionKey, 'Section ') !== 0) {
            $this->json_error('Invalid section key.', 400);
        }

        $components = json_decode($compsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($components) || empty($components)) {
            $this->json_error('Invalid components data.', 400);
        }

        // Validate and build components array
        $totalMax    = 0;
        $compsClean  = [];
        foreach ($components as $i => $comp) {
            $name     = trim(strip_tags((string) ($comp['name']     ?? '')));
            $maxMarks = (int) ($comp['maxMarks'] ?? 0);
            if (!$name || $maxMarks < 1 || $maxMarks > 999) {
                $this->json_error("Component #{$i}: name required, maxMarks must be 1–999.", 400);
            }
            $totalMax        += $maxMarks;
            $compsClean[$i]   = ['Name' => $name, 'MaxMarks' => $maxMarks];
        }

        $template = [
            'Components'    => $compsClean,
            'TotalMaxMarks' => $totalMax,
            'CreatedAt'     => (int) round(microtime(true) * 1000),
            'CreatedBy'     => $this->admin_id ?? '',
        ];

        $path = "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}/{$subject}";
        $this->firebase->set($path, $template);

        $this->json_success(['message' => 'Template saved.', 'totalMaxMarks' => $totalMax]);
    }

    /**
     * GET AJAX — Fetch template JSON for pre-population.
     */
    public function get_template()
    {
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $examId     = trim((string) $this->input->get('examId'));
        $classKey   = trim((string) $this->input->get('classKey'));
        $sectionKey = trim((string) $this->input->get('sectionKey'));
        $subject    = trim((string) $this->input->get('subject'));

        if (!$examId || !$classKey || !$sectionKey || !$subject) {
            echo json_encode(['template' => null]);
            return;
        }

        $path     = "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}/{$subject}";
        $template = $this->firebase->get($path);

        echo json_encode(['template' => $template]);
    }

    /**
     * POST AJAX — Batch-save marks for all students in one subject.
     *
     * Payload: JSON in marksData field.
     * { examId, classKey, sectionKey, subject,
     *   students: [{userId, absent, marks:{ComponentName:value,...}, total}] }
     */
    public function save_marks()
    {
        header('Content-Type: application/json');

        $school = $this->school_name;
        $year   = $this->session_year;

        $raw = (string) $this->input->post('marksData');
        if (empty($raw)) {
            $this->json_error('No marks data received.', 400);
        }

        $payload = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            $this->json_error('Invalid JSON payload.', 400);
        }

        $examId     = trim((string) ($payload['examId']     ?? ''));
        $classKey   = trim((string) ($payload['classKey']   ?? ''));
        $sectionKey = trim((string) ($payload['sectionKey'] ?? ''));
        $subject    = trim((string) ($payload['subject']    ?? ''));

        // RBAC: Teachers can only save marks for their assigned classes/subjects
        if (!$this->_teacher_can_access($classKey, $sectionKey, $subject)) {
            $this->json_error('You are not assigned to this class/subject.', 403);
        }
        $students   = $payload['students'] ?? [];

        if (!$examId || !$classKey || !$sectionKey || !$subject) {
            $this->json_error('Missing required fields.', 400);
        }
        if (!is_array($students)) {
            $this->json_error('Students data must be an array.', 400);
        }

        $savedAt  = (int) round(microtime(true) * 1000);
        $savedBy  = $this->admin_id ?? '';
        $basePath = "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subject}";
        $count    = 0;

        foreach ($students as $stu) {
            $userId = trim((string) ($stu['userId'] ?? ''));
            if (!$userId) continue;

            $absent   = !empty($stu['absent']);
            $rawMarks = is_array($stu['marks'] ?? null) ? $stu['marks'] : [];
            $total    = $absent ? 0 : (int) ($stu['total'] ?? 0);

            // Sanitize component marks
            $marksClean = [];
            foreach ($rawMarks as $comp => $val) {
                $comp = strip_tags(trim((string) $comp));
                if ($comp) {
                    $marksClean[$comp] = $absent ? 0 : max(0, (int) $val);
                }
            }

            $entry = array_merge($marksClean, [
                'Total'   => $total,
                'Absent'  => $absent,
                'SavedAt' => $savedAt,
                'SavedBy' => $savedBy,
            ]);

            $this->firebase->set("{$basePath}/{$userId}", $entry);
            $count++;
        }

        $this->json_success(['message' => "Marks saved for {$count} student(s)."]);
    }

    /**
     * GET AJAX — Fetch marks for a subject (pre-populate marks sheet).
     */
    public function get_marks()
    {
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $examId     = trim((string) $this->input->get('examId'));
        $classKey   = trim((string) $this->input->get('classKey'));
        $sectionKey = trim((string) $this->input->get('sectionKey'));
        $subject    = trim((string) $this->input->get('subject'));

        if (!$examId || !$classKey || !$sectionKey || !$subject) {
            echo json_encode(['marks' => (object)[]]);
            return;
        }

        $path  = "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subject}";
        $marks = $this->firebase->get($path) ?? [];

        echo json_encode(['marks' => is_array($marks) ? $marks : (object)[]]);
    }

    /**
     * POST AJAX — Compute grades/ranks for an exam+class+section → write Computed node.
     */
    public function compute_results()
    {
        $this->_require_role(self::ADMIN_ROLES, 'compute results');
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $examId     = trim((string) $this->input->post('examId'));
        $classKey   = trim((string) $this->input->post('classKey'));
        $sectionKey = trim((string) $this->input->post('sectionKey'));

        if (!$examId || !$classKey || !$sectionKey) {
            $this->json_error('Missing required fields.', 400);
        }

        $exam = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
        if (!$exam || !is_array($exam)) {
            $this->json_error('Exam not found.', 404);
        }

        $scale      = $exam['GradingScale']   ?? 'Percentage';
        $passingPct = (int) ($exam['PassingPercent'] ?? 33);

        // Load all subject templates for this class/section
        $templatesNode = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}"
        ) ?? [];
        if (!is_array($templatesNode) || empty($templatesNode)) {
            $this->json_error('No templates found. Please design templates first.', 400);
        }

        // Load all marks for this class/section
        $allMarksNode = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}"
        ) ?? [];
        if (!is_array($allMarksNode)) $allMarksNode = [];

        // Collect all unique student IDs across all subjects
        $allUserIds = [];
        foreach ($allMarksNode as $subj => $stuMarks) {
            if (is_array($stuMarks)) {
                foreach (array_keys($stuMarks) as $uid) {
                    $allUserIds[$uid] = true;
                }
            }
        }
        $allUserIds = array_keys($allUserIds);

        if (empty($allUserIds)) {
            $this->json_error('No marks entered yet for this class/section.', 400);
        }

        // Per student: aggregate subjects
        $studentResults = [];
        foreach ($allUserIds as $uid) {
            $totalMarks = 0;
            $maxMarks   = 0;
            $subjects   = [];
            $allPass    = true;

            foreach ($templatesNode as $subj => $tmpl) {
                if (!is_array($tmpl)) continue;
                $subjMax    = (int) ($tmpl['TotalMaxMarks'] ?? 0);
                $stuMarks   = $allMarksNode[$subj][$uid] ?? [];
                $absent     = !empty($stuMarks['Absent']);
                $subjTotal  = $absent ? 0 : (int) ($stuMarks['Total'] ?? 0);
                $subjPct    = $subjMax > 0 ? ($subjTotal / $subjMax * 100) : 0;
                $subjGrade  = $absent ? 'AB' : $this->exam_engine->compute_grade($subjPct, $scale);
                $subjPass   = $absent ? 'Fail' : $this->exam_engine->compute_pass_fail($subjPct, $passingPct);

                if ($subjPass === 'Fail') $allPass = false;

                $subjects[$subj] = [
                    'Total'      => $subjTotal,
                    'MaxMarks'   => $subjMax,
                    'Percentage' => round($subjPct, 2),
                    'Grade'      => $subjGrade,
                    'PassFail'   => $subjPass,
                    'Absent'     => $absent,
                ];

                $totalMarks += $subjTotal;
                $maxMarks   += $subjMax;
            }

            $overallPct   = $maxMarks > 0 ? ($totalMarks / $maxMarks * 100) : 0;
            $overallGrade = $this->exam_engine->compute_grade($overallPct, $scale);
            $overallPass  = $allPass ? $this->exam_engine->compute_pass_fail($overallPct, $passingPct) : 'Fail';

            $studentResults[$uid] = [
                'TotalMarks' => $totalMarks,
                'MaxMarks'   => $maxMarks,
                'Percentage' => round($overallPct, 2),
                'Grade'      => $overallGrade,
                'PassFail'   => $overallPass,
                'Subjects'   => $subjects,
                'ComputedAt' => (int) round(microtime(true) * 1000),
            ];
        }

        // Sort by Percentage desc → assign competition ranks
        uasort($studentResults, fn($a, $b) => $b['Percentage'] <=> $a['Percentage']);
        $this->exam_engine->assign_ranks_assoc($studentResults, 'Percentage');

        // Write to Computed node (one set() per student)
        $basePath = "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}";
        foreach ($studentResults as $uid => $res) {
            $this->firebase->set("{$basePath}/{$uid}", $res);
        }

        $this->json_success([
            'message' => 'Results computed for ' . count($studentResults) . ' student(s).',
            'count'   => count($studentResults),
        ]);
    }

    /**
     * POST AJAX — Save exam weights for cumulative calculation.
     */
    public function save_cumulative_config()
    {
        $this->_require_role(self::ADMIN_ROLES, 'save cumulative config');
        header('Content-Type: application/json');

        $school    = $this->school_name;
        $year      = $this->session_year;
        $configRaw = (string) $this->input->post('config');

        if (empty($configRaw)) {
            $this->json_error('No config data.', 400);
        }

        $config = json_decode($configRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
            $this->json_error('Invalid JSON.', 400);
        }

        $examsConfig  = $config['exams'] ?? [];
        $totalWeight  = 0;
        $examsClean   = [];

        foreach ($examsConfig as $examId => $item) {
            $weight = (int) ($item['weight'] ?? 0);
            $label  = strip_tags(trim((string) ($item['label'] ?? $examId)));
            if ($weight < 0 || $weight > 100) {
                $this->json_error("Weight for {$examId} must be 0–100.", 400);
            }
            $totalWeight         += $weight;
            $examsClean[$examId]  = ['Weight' => $weight, 'Label' => $label];
        }

        if ($totalWeight !== 100) {
            $this->json_error("Total weight must be exactly 100 (got {$totalWeight}).", 400);
        }

        $payload = ['Exams' => $examsClean, 'TotalWeight' => 100];
        $this->firebase->set("Schools/{$school}/{$year}/Results/CumulativeConfig", $payload);

        $this->json_success(['message' => 'Cumulative config saved.']);
    }

    /**
     * POST AJAX — Compute and write Cumulative results for a class/section.
     */
    public function compute_cumulative()
    {
        $this->_require_role(self::ADMIN_ROLES, 'compute cumulative');
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $classKey   = trim((string) $this->input->post('classKey'));
        $sectionKey = trim((string) $this->input->post('sectionKey'));

        if (!$classKey || !$sectionKey) {
            $this->json_error('Missing classKey or sectionKey.', 400);
        }

        // Load config
        $config = $this->firebase->get("Schools/{$school}/{$year}/Results/CumulativeConfig") ?? [];
        if (!is_array($config) || empty($config['Exams'])) {
            $this->json_error('No cumulative config found. Save config first.', 400);
        }
        if ((int) ($config['TotalWeight'] ?? 0) !== 100) {
            $this->json_error('TotalWeight must be 100.', 400);
        }

        $examWeights = $config['Exams'];
        $examIds     = array_keys($examWeights);

        // Load computed results per exam
        $allExamResults = [];
        foreach ($examIds as $examId) {
            $node = $this->firebase->get(
                "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}"
            ) ?? [];
            if (is_array($node)) {
                $allExamResults[$examId] = $node;
            }
        }

        if (empty($allExamResults)) {
            $this->json_error('No computed results found for any included exam.', 400);
        }

        // Gather all student IDs
        $allUids = [];
        foreach ($allExamResults as $examId => $stuMap) {
            foreach (array_keys($stuMap) as $uid) {
                $allUids[$uid] = true;
            }
        }

        $studentCumulative = [];
        foreach (array_keys($allUids) as $uid) {
            $weightedTotal    = 0;
            $subjectWeighted  = [];
            $anyFail          = false;

            foreach ($examIds as $examId) {
                $weight    = (int) ($examWeights[$examId]['Weight'] ?? 0);
                $stuResult = $allExamResults[$examId][$uid] ?? null;
                if (!$stuResult) continue;

                $stuPct  = (float) ($stuResult['Percentage'] ?? 0);
                $weightedTotal += ($stuPct * $weight / 100);

                if (($stuResult['PassFail'] ?? '') === 'Fail') $anyFail = true;

                foreach ($stuResult['Subjects'] ?? [] as $subj => $subjData) {
                    $subjPct = (float) ($subjData['Percentage'] ?? 0);
                    if (!isset($subjectWeighted[$subj])) $subjectWeighted[$subj] = 0;
                    $subjectWeighted[$subj] += ($subjPct * $weight / 100);
                }
            }

            // Load grading scale from first available exam
            $scale      = 'Percentage';
            $passingPct = 33;
            foreach ($examIds as $examId) {
                $examMeta = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$examId}");
                if ($examMeta && is_array($examMeta)) {
                    $scale      = $examMeta['GradingScale']  ?? 'Percentage';
                    $passingPct = (int) ($examMeta['PassingPercent'] ?? 33);
                    break;
                }
            }

            $overallGrade = $this->exam_engine->compute_grade($weightedTotal, $scale);
            $overallPass  = $anyFail ? 'Fail' : $this->exam_engine->compute_pass_fail($weightedTotal, $passingPct);

            $subjResults = [];
            foreach ($subjectWeighted as $subj => $ws) {
                $subjResults[$subj] = [
                    'WeightedScore' => round($ws, 2),
                    'Grade'         => $this->exam_engine->compute_grade($ws, $scale),
                    'PassFail'      => $this->exam_engine->compute_pass_fail($ws, $passingPct),
                ];
            }

            $studentCumulative[$uid] = [
                'WeightedTotal' => round($weightedTotal, 2),
                'Grade'         => $overallGrade,
                'PassFail'      => $overallPass,
                'Subjects'      => $subjResults,
                'ComputedAt'    => (int) round(microtime(true) * 1000),
            ];
        }

        // Sort and assign ranks
        uasort($studentCumulative, fn($a, $b) => $b['WeightedTotal'] <=> $a['WeightedTotal']);
        $this->exam_engine->assign_ranks_assoc($studentCumulative, 'WeightedTotal');

        // Write
        $basePath = "Schools/{$school}/{$year}/Results/Cumulative/{$classKey}/{$sectionKey}";
        foreach ($studentCumulative as $uid => $res) {
            $this->firebase->set("{$basePath}/{$uid}", $res);
        }

        $this->json_success([
            'message' => 'Cumulative computed for ' . count($studentCumulative) . ' student(s).',
            'count'   => count($studentCumulative),
        ]);
    }

    /**
     * GET AJAX — Fetch cumulative results for cumulative view.
     * Returns {students:[...], subjects:[...]} JSON.
     */
    public function get_cumulative_data()
    {
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $classKey   = trim((string) $this->input->get('classKey'));
        $sectionKey = trim((string) $this->input->get('sectionKey'));

        if (!$classKey || !$sectionKey) {
            echo json_encode(['students' => [], 'subjects' => []]);
            return;
        }

        $cumulative = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Cumulative/{$classKey}/{$sectionKey}"
        ) ?? [];

        if (!is_array($cumulative) || empty($cumulative)) {
            echo json_encode(['students' => [], 'subjects' => []]);
            return;
        }

        $studentList = $this->firebase->get(
            "Schools/{$school}/{$year}/{$classKey}/{$sectionKey}/Students/List"
        ) ?? [];
        if (!is_array($studentList)) $studentList = [];

        $subjects = [];
        foreach ($cumulative as $uid => $res) {
            if (!is_array($res)) continue;
            foreach (array_keys($res['Subjects'] ?? []) as $s) {
                $subjects[$s] = true;
            }
        }
        $subjects = array_keys($subjects);
        sort($subjects);

        $rows = [];
        foreach ($cumulative as $uid => $res) {
            if (!is_array($res)) continue;
            $rows[] = [
                'uid'          => $uid,
                'name'         => is_string($studentList[$uid] ?? null) ? $studentList[$uid] : $uid,
                'rank'         => $res['Rank']          ?? '—',
                'weightedTotal' => $res['WeightedTotal'] ?? 0,
                'grade'        => $res['Grade']         ?? '',
                'passFail'     => $res['PassFail']      ?? '',
                'subjects'     => $res['Subjects']      ?? [],
            ];
        }
        usort($rows, fn($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

        echo json_encode(['students' => $rows, 'subjects' => $subjects]);
    }

    /**
     * GET AJAX — Fetch computed results for class_result view.
     * Returns {students:[...], subjects:[...]} JSON.
     */
    public function get_class_result_data()
    {
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $examId     = trim((string) $this->input->get('examId'));
        $classKey   = trim((string) $this->input->get('classKey'));
        $sectionKey = trim((string) $this->input->get('sectionKey'));

        if (!$examId || !$classKey || !$sectionKey) {
            echo json_encode(['students' => [], 'subjects' => []]);
            return;
        }

        $computed = $this->firebase->get(
            "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}"
        ) ?? [];

        if (!is_array($computed) || empty($computed)) {
            echo json_encode(['students' => [], 'subjects' => []]);
            return;
        }

        $studentList = $this->firebase->get(
            "Schools/{$school}/{$year}/{$classKey}/{$sectionKey}/Students/List"
        ) ?? [];
        if (!is_array($studentList)) $studentList = [];

        // Collect subject names
        $subjects = [];
        foreach ($computed as $uid => $res) {
            if (!is_array($res)) continue;
            foreach (array_keys($res['Subjects'] ?? []) as $s) {
                $subjects[$s] = true;
            }
        }
        $subjects = array_keys($subjects);
        sort($subjects);

        $rows = [];
        foreach ($computed as $uid => $res) {
            if (!is_array($res)) continue;
            $rows[] = [
                'uid'      => $uid,
                'name'     => is_string($studentList[$uid] ?? null) ? $studentList[$uid] : $uid,
                'rank'     => $res['Rank']      ?? '—',
                'total'    => $res['TotalMarks'] ?? 0,
                'maxMarks' => $res['MaxMarks']   ?? 0,
                'pct'      => $res['Percentage'] ?? 0,
                'grade'    => $res['Grade']      ?? '',
                'passFail' => $res['PassFail']   ?? '',
                'subjects' => $res['Subjects']   ?? [],
            ];
        }
        usort($rows, fn($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

        echo json_encode(['students' => $rows, 'subjects' => $subjects]);
    }

    /**
     * GET AJAX — Template + marks + computed status per exam for a class/section.
     */
    public function get_exam_status()
    {
        header('Content-Type: application/json');

        $school     = $this->school_name;
        $year       = $this->session_year;
        $examId     = trim((string) $this->input->get('examId'));
        $classKey   = trim((string) $this->input->get('classKey'));
        $sectionKey = trim((string) $this->input->get('sectionKey'));

        if (!$examId || !$classKey || !$sectionKey) {
            echo json_encode(['status' => null]);
            return;
        }

        // Templates
        $templatesNode = $this->firebase->shallow_get(
            "Schools/{$school}/{$year}/Results/Templates/{$examId}/{$classKey}/{$sectionKey}"
        );
        $templateCount = count($templatesNode);

        // Count subjects with marks
        $marksCount = 0;
        foreach ($templatesNode as $subj) {
            $mNode = $this->firebase->shallow_get(
                "Schools/{$school}/{$year}/Results/Marks/{$examId}/{$classKey}/{$sectionKey}/{$subj}"
            );
            if (!empty($mNode)) $marksCount++;
        }

        // Computed
        $computedNode  = $this->firebase->shallow_get(
            "Schools/{$school}/{$year}/Results/Computed/{$examId}/{$classKey}/{$sectionKey}"
        );
        $computedCount = count($computedNode);

        echo json_encode([
            'status' => [
                'templates' => $templateCount,
                'marks'     => $marksCount,
                'computed'  => $computedCount,
            ],
        ]);
    }

}
