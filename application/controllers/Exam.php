<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Exam controller — full ERP-grade redesign
 *
 * Firebase structure:
 *   Central: Schools/{school}/{year}/Exams/{EXM0001}/...
 *   Per-section copy: Schools/{school}/{year}/Class 9th/Section A/Exams/{EXM0001}/{date}/{subject}
 *
 * Methods:
 *   index()          — list all exams
 *   create()         — GET: form; POST AJAX: save
 *   view($id)        — exam detail page
 *   delete($id)      — cleanup per-section + delete central, redirect
 *   update_status()  — POST AJAX: update Status field
 *   get_subjects()   — GET AJAX: subjects for a class
 *   manage_exam()    — backward-compat redirect to index
 */
class Exam extends MY_Controller
{
    const ALLOWED_TYPES    = ['Mid-Term', 'Final Term', 'Unit Test', 'Weekly Test', 'Pre-Board', 'Annual'];
    const ALLOWED_SCALES   = ['Percentage', 'A-F Grades', 'O-E Grades', '10-Point', 'Pass/Fail'];
    const ALLOWED_STATUSES = ['Draft', 'Published', 'Completed'];

    public function __construct()
    {
        parent::__construct();
    }

    // ── Backward compatibility ────────────────────────────────────────────
    public function manage_exam()
    {
        redirect('exam');
    }

    // ── index() — Exam list ───────────────────────────────────────────────
    public function index()
    {
        $school = $this->school_name;
        $year   = $this->session_year;

        $raw   = $this->firebase->get("Schools/{$school}/{$year}/Exams") ?? [];
        $exams = [];
        foreach ($raw as $id => $e) {
            if ($id === 'Count' || !is_array($e)) continue;
            $exams[] = array_merge(['id' => $id], $e);
        }
        usort($exams, function ($a, $b) {
            return ($b['CreatedAt'] ?? 0) <=> ($a['CreatedAt'] ?? 0);
        });

        $this->load->view('include/header');
        $this->load->view('exam/index', ['exams' => $exams]);
        $this->load->view('include/footer');
    }

    // ── create() — GET: form; POST AJAX: save ────────────────────────────
    public function create()
    {
        $school    = $this->school_name;
        $year      = $this->session_year;
        $structure = $this->get_class_structure();

        if ($this->input->method() === 'post') {
            header('Content-Type: application/json');

            // — Exam name
            $name = trim((string) $this->input->post('examName'));
            if (!preg_match('/^[\w\s\-\.]{2,80}$/u', $name)) {
                $this->json_error('Invalid exam name. Use letters, digits, spaces, hyphens, or dots (2–80 chars).', 400);
            }

            // — Exam type
            $type = trim((string) $this->input->post('examType'));
            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                $this->json_error('Invalid exam type.', 400);
            }

            // — Status
            $status = trim((string) $this->input->post('examStatus'));
            if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                $this->json_error('Invalid exam status.', 400);
            }

            // — Grading scale
            $scale = strip_tags(trim((string) $this->input->post('gradingScale')));
            if (!in_array($scale, self::ALLOWED_SCALES, true)) {
                $this->json_error('Invalid grading scale.', 400);
            }

            // — Passing percent
            $passingPct = (int) $this->input->post('passingPercent');
            if ($passingPct < 1 || $passingPct > 100) {
                $this->json_error('PassingPercent must be 1–100.', 400);
            }

            // — Dates
            $startDt = DateTime::createFromFormat('Y-m-d', trim((string) $this->input->post('startDate')));
            $endDt   = DateTime::createFromFormat('Y-m-d', trim((string) $this->input->post('endDate')));
            if (!$startDt || !$endDt) {
                $this->json_error('Invalid date format.', 400);
            }
            if ($startDt > $endDt) {
                $this->json_error('Start date must not be after end date.', 400);
            }

            // — Instructions
            $instructions = [];
            $idx          = 0;
            foreach (explode("\n", (string) $this->input->post('generalInstructions')) as $line) {
                $c = trim(preg_replace('/^[•\-\*\s]+/', '', $line));
                if ($c !== '') $instructions[$idx++] = $c;
            }

            // — Schedule
            $scheduleJson = (string) $this->input->post('examSchedule');
            if (empty($scheduleJson)) {
                $this->json_error('Exam schedule is empty. Please add at least one row.', 400);
            }
            $scheduleRows = json_decode($scheduleJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($scheduleRows)) {
                $this->json_error('Invalid schedule data.', 400);
            }
            if (empty($scheduleRows)) {
                $this->json_error('Exam schedule has no entries. Please add at least one row.', 400);
            }

            // — Generate EXM ID
            $examId = $this->generate_exam_id();

            // — Save central metadata (without Schedule key — added incrementally below)
            $examMeta = [
                'Name'                => $name,
                'Type'                => $type,
                'Status'              => $status,
                'GradingScale'        => $scale,
                'PassingPercent'      => $passingPct,
                'StartDate'           => $startDt->format('d-m-Y'),
                'EndDate'             => $endDt->format('d-m-Y'),
                'GeneralInstructions' => $instructions ?: (object)[],
                'CreatedAt'           => (int) round(microtime(true) * 1000),
                'CreatedBy'           => $this->admin_id ?? '',
            ];
            $this->firebase->set("Schools/{$school}/{$year}/Exams/{$examId}", $examMeta);

            // — Process schedule rows
            $savedCount = 0;
            foreach ($scheduleRows as $row) {
                if (!is_array($row)) continue;

                $className  = trim((string) ($row['className']   ?? ''));
                $subject    = strip_tags(trim((string) ($row['subject']     ?? '')));
                $startTime  = trim((string) ($row['startTime']  ?? ''));
                $endTime    = trim((string) ($row['endTime']    ?? ''));
                $totalMarks = is_numeric($row['totalMarks']  ?? '') ? (int) $row['totalMarks'] : null;
                $passMks    = is_numeric($row['passingMarks'] ?? '') ? (int) $row['passingMarks'] : null;
                $dateRaw    = trim((string) ($row['date'] ?? ''));

                if (!$className || !$subject || !$startTime || !$endTime || $totalMarks === null || !$dateRaw) {
                    log_message('error', 'Exam::create — incomplete row skipped: ' . json_encode($row));
                    continue;
                }

                $dateDt = DateTime::createFromFormat('d/m/Y', $dateRaw);
                if (!$dateDt) {
                    log_message('error', "Exam::create — bad date [{$dateRaw}], skipping.");
                    continue;
                }
                $dateKey = $dateDt->format('d-m-Y');

                $stDt = DateTime::createFromFormat('H:i', $startTime);
                $etDt = DateTime::createFromFormat('H:i', $endTime);
                if (!$stDt || !$etDt) {
                    log_message('error', "Exam::create — bad time [{$startTime}-{$endTime}], skipping.");
                    continue;
                }
                $timeStr = $stDt->format('h:iA') . '-' . $etDt->format('h:iA');

                if ($passMks === null) {
                    $passMks = (int) round($totalMarks * $passingPct / 100);
                }

                $entry = [
                    'Time'         => $timeStr,
                    'TotalMarks'   => $totalMarks,
                    'PassingMarks' => $passMks,
                ];

                // Save to each section of the class
                $sections = $structure[$className] ?? [];
                if (empty($sections)) {
                    log_message('error', "Exam::create — no sections for [{$className}], skipping.");
                    continue;
                }
                foreach ($sections as $sectionLetter) {
                    $sectionKey = "Section {$sectionLetter}";
                    // Central schedule copy
                    $this->firebase->set(
                        "Schools/{$school}/{$year}/Exams/{$examId}/Schedule/{$className}/{$sectionKey}/{$dateKey}/{$subject}",
                        $entry
                    );
                    // Per-section copy
                    $this->firebase->set(
                        "Schools/{$school}/{$year}/{$className}/{$sectionKey}/Exams/{$examId}/{$dateKey}/{$subject}",
                        $entry
                    );
                }
                $savedCount++;
            }

            $this->json_success(['examId' => $examId, 'message' => "Exam created successfully ({$savedCount} entries saved)."]);
            return;
        }

        // GET — build subjects map
        $subjects = [];
        foreach ($structure as $classKey => $sectionLetters) {
            if (empty($sectionLetters)) continue;
            $firstSection       = "Section {$sectionLetters[0]}";
            $subjectsRaw        = $this->firebase->get("Schools/{$school}/{$year}/{$classKey}/{$firstSection}/Subjects") ?? [];
            $subjects[$classKey] = array_keys(is_array($subjectsRaw) ? $subjectsRaw : []);
        }

        $this->load->view('include/header');
        $this->load->view('exam/create', [
            'classNames' => array_keys($structure),
            'subjects'   => $subjects,
        ]);
        $this->load->view('include/footer');
    }

    // ── view($id) ────────────────────────────────────────────────────────
    public function view($id = null)
    {
        if (!$id) { redirect('exam'); }

        $school = $this->school_name;
        $year   = $this->session_year;
        $exam   = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$id}");

        if (!$exam || !is_array($exam)) { redirect('exam'); }

        $this->load->view('include/header');
        $this->load->view('exam/view', ['examId' => $id, 'exam' => $exam]);
        $this->load->view('include/footer');
    }

    // ── delete($id) ──────────────────────────────────────────────────────
    public function delete($id = null)
    {
        if (!$id) { redirect('exam'); }

        $school   = $this->school_name;
        $year     = $this->session_year;
        $schedule = $this->firebase->get("Schools/{$school}/{$year}/Exams/{$id}/Schedule") ?? [];

        // Remove per-section copies
        foreach ($schedule as $classKey => $sectionData) {
            if (!is_array($sectionData)) continue;
            foreach (array_keys($sectionData) as $sectionKey) {
                $this->firebase->delete("Schools/{$school}/{$year}/{$classKey}/{$sectionKey}/Exams/{$id}");
            }
        }

        // Delete central record
        $this->firebase->delete("Schools/{$school}/{$year}/Exams/{$id}");

        // Cascade: remove Results nodes (Templates, Marks, Computed) for this exam
        $this->firebase->delete("Schools/{$school}/{$year}/Results/Templates/{$id}");
        $this->firebase->delete("Schools/{$school}/{$year}/Results/Marks/{$id}");
        $this->firebase->delete("Schools/{$school}/{$year}/Results/Computed/{$id}");
        // Remove from CumulativeConfig
        $this->firebase->delete("Schools/{$school}/{$year}/Results/CumulativeConfig/Exams/{$id}");

        redirect('exam');
    }

    // ── update_status() — POST AJAX ──────────────────────────────────────
    public function update_status()
    {
        header('Content-Type: application/json');

        $id     = trim((string) $this->input->post('examId'));
        $status = trim((string) $this->input->post('status'));

        if (!$id || !in_array($status, self::ALLOWED_STATUSES, true)) {
            $this->json_error('Invalid parameters.', 400);
        }

        $school = $this->school_name;
        $year   = $this->session_year;
        $this->firebase->update("Schools/{$school}/{$year}/Exams/{$id}", ['Status' => $status]);
        $this->json_success(['message' => 'Status updated to ' . $status . '.']);
    }

    // ── get_subjects() — GET AJAX ────────────────────────────────────────
    public function get_subjects()
    {
        header('Content-Type: application/json');

        $school   = $this->school_name;
        $classKey = trim((string) $this->input->get('class')); // e.g. "Class 9th"

        if (!$classKey) {
            echo json_encode(['subjects' => []]);
            return;
        }

        // Convert "Class 9th" → Subject_list key (matches Subjects.php logic)
        $raw = strtolower($classKey);
        if (strpos($raw, 'nursery') !== false) {
            $listKey = 'Nursery';
        } elseif (strpos($raw, 'lkg') !== false) {
            $listKey = 'LKG';
        } elseif (strpos($raw, 'ukg') !== false) {
            $listKey = 'UKG';
        } elseif (strpos($raw, 'playgroup') !== false || strpos($raw, 'play') !== false) {
            $listKey = 'Playgroup';
        } elseif (preg_match('/\d+/', $classKey, $m)) {
            $listKey = (int) $m[0]; // 9, 10, 11 …
        } else {
            echo json_encode(['subjects' => []]);
            return;
        }

        $raw  = $this->firebase->get("Schools/{$school}/Subject_list/{$listKey}") ?? [];
        $names = [];
        if (is_array($raw)) {
            foreach ($raw as $code => $data) {
                if ($code === 'pattern_type') continue;
                if (is_array($data) && !empty($data['subject_name'])) {
                    $names[] = $data['subject_name'];
                }
            }
        }

        echo json_encode(['subjects' => $names]);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Build [classKey => [sectionLetters]] from the session root using shallow_get.
     */
    private function get_class_structure(): array
    {
        $school      = $this->school_name;
        $year        = $this->session_year;
        $structure   = [];
        $sessionKeys = $this->firebase->shallow_get("Schools/{$school}/{$year}");

        foreach ($sessionKeys as $classKey) {
            if (strpos($classKey, 'Class ') !== 0) continue;
            $sectionKeys    = $this->firebase->shallow_get("Schools/{$school}/{$year}/{$classKey}");
            $sectionLetters = [];
            foreach ($sectionKeys as $sk) {
                if (strpos($sk, 'Section ') !== 0) continue;
                $sectionLetters[] = str_replace('Section ', '', $sk);
            }
            $structure[$classKey] = $sectionLetters;
        }

        return $structure;
    }

    /**
     * Generate next sequential EXM ID (EXM0001, EXM0002, …).
     * Increments the Count node under Exams/.
     */
    private function generate_exam_id(): string
    {
        $school = $this->school_name;
        $year   = $this->session_year;
        $count  = (int) ($this->firebase->get("Schools/{$school}/{$year}/Exams/Count") ?? 0);
        $count++;
        $this->firebase->set("Schools/{$school}/{$year}/Exams/Count", $count);
        return 'EXM' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
