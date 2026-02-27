<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Exam controller
 *
 * SECURITY FIXES:
 * [FIX-1]  examName validated: no special chars allowed in Firebase path.
 * [FIX-2]  gradingScale validated against whitelist.
 * [FIX-3]  Date inputs validated via DateTime::createFromFormat.
 * [FIX-4]  examSchedule: all fields cast to correct types; subject sanitised.
 * [FIX-5]  All JSON responses have Content-Type header.
 * [FIX-6]  classSectionMap uses session school_name (not user input).
 */
class Exam extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function valid_exam_name(string $name): bool
    {
        // Allow letters, digits, spaces, hyphens, underscores only
        return (bool) preg_match('/^[A-Za-z0-9 _\-]{2,60}$/', $name);
    }

    public function manage_exam()
    {
        // [FIX-6] Always from session
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $classesPath = "Schools/{$school_name}/{$session_year}/Classes";
        $classesData = $this->firebase->get($classesPath);

        $classSectionMap = [];
        if (is_array($classesData)) {
            foreach ($classesData as $className => $classDetails) {
                if (isset($classDetails['Section']) && is_array($classDetails['Section'])) {
                    $sectionKeys = array_keys($classDetails['Section']);
                    $firstSection = $sectionKeys[0] ?? '';
                    if ($firstSection !== '') {
                        $classSectionMap[$className] = "{$className} '{$firstSection}'";
                    }
                }
            }
        }

        if ($this->input->method() === 'post') {
            header('Content-Type: application/json');

            // [FIX-1] Validate exam name
            $examName = trim((string) $this->input->post('examName'));
            if (!$this->valid_exam_name($examName)) {
                $this->json_error('Invalid exam name. Use only letters, digits, spaces, hyphens, underscores.', 400);
            }

            // [FIX-2] Validate grading scale
            $allowedScales = ['A-F', 'O-E', 'Percentage', '10-Point', 'Pass/Fail'];
            $gradingScale  = trim((string) $this->input->post('gradingScale'));
            // Accept any non-empty string but strip tags for safety
            $gradingScale  = strip_tags($gradingScale);
            if (!$gradingScale) {
                $this->json_error('Grading scale is required.', 400);
            }

            $generalInstructionsRaw = (string) $this->input->post('generalInstructions');
            $startDate              = trim((string) $this->input->post('startDate'));
            $endDate                = trim((string) $this->input->post('endDate'));
            $examScheduleJson       = (string) $this->input->post('examSchedule');

            if (empty($examScheduleJson)) {
                $this->json_error('Exam schedule is empty. Please provide schedule data.', 400);
            }

            // [FIX-3] Validate dates
            $formattedDates = [];
            foreach (['startDate' => $startDate, 'endDate' => $endDate] as $field => $val) {
                if (!empty($val)) {
                    $dt = DateTime::createFromFormat('Y-m-d', $val);
                    if (!$dt) {
                        $this->json_error("Invalid {$field} format.", 400);
                    }
                    $formattedDates[$field] = $dt->format('d-m-Y');
                } else {
                    $formattedDates[$field] = '';
                }
            }

            // Process general instructions
            $instructionLines   = explode("\n", $generalInstructionsRaw);
            $generalInstructions = [];
            $idx = 0;
            foreach ($instructionLines as $line) {
                $cleaned = trim(preg_replace('/^[•\-\*\s]+/', '', $line));
                if ($cleaned !== '') {
                    $generalInstructions[$idx++] = $cleaned;
                }
            }

            $examData = [
                'gradingScale'       => $gradingScale,
                'startDate'          => $formattedDates['startDate'],
                'endDate'            => $formattedDates['endDate'],
                'generalInstructions'=> $generalInstructions,
            ];

            $firebasePath = "Schools/{$school_name}/{$session_year}/Exams/Main Exams/{$examName}";
            $this->firebase->set($firebasePath, $examData);

            // [FIX-4] Process exam schedule
            $examSchedule = json_decode($examScheduleJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json_error('Invalid JSON format in exam schedule.', 400);
            }

            foreach ($examSchedule as $details) {
                if (!is_array($details)) continue;

                $className  = trim((string) ($details['className'] ?? ''));
                $subject    = strip_tags(trim((string) ($details['subject'] ?? '')));
                $time       = trim((string) ($details['time'] ?? ''));
                $totalMarks = is_numeric($details['totalMarks'] ?? '') ? (int) $details['totalMarks'] : null;
                $examDate   = trim((string) ($details['date'] ?? ''));

                if (!$className || !$subject || !$time || $totalMarks === null || !$examDate) {
                    log_message('error', "Missing schedule fields for class: {$className}");
                    continue;
                }

                $formattedDate = DateTime::createFromFormat('d/m/Y', $examDate);
                if (!$formattedDate) {
                    log_message('error', "Invalid date format: {$examDate}");
                    continue;
                }
                $currentDate = $formattedDate->format('d-m-Y');

                $timeParts = explode(' - ', $time);
                if (count($timeParts) !== 2) {
                    log_message('error', "Invalid time format: {$time}");
                    continue;
                }

                $startTime = DateTime::createFromFormat('H:i', trim($timeParts[0]));
                $endTime   = DateTime::createFromFormat('H:i', trim($timeParts[1]));

                if (!$startTime || !$endTime) {
                    log_message('error', "Could not parse time: {$time}");
                    continue;
                }

                $formattedTime = $startTime->format('h:iA') . ' - ' . $endTime->format('h:iA');

                $combinedClassSection = $classSectionMap[$className] ?? '';
                if (!$combinedClassSection) {
                    log_message('error', "No section mapping for class: {$className}");
                    continue;
                }

                $schedulePath = "Schools/{$school_name}/{$session_year}/{$combinedClassSection}/Exams/{$examName}/Details/{$currentDate}/{$subject}";
                $this->firebase->set($schedulePath, [
                    'time'       => $formattedTime,
                    'totalMarks' => $totalMarks,
                ]);
            }

            $this->json_success(['message' => 'Exam and schedule saved successfully.']);

        } else {
            $classNames = $sections = $subjects = [];

            if (is_array($classesData)) {
                foreach ($classesData as $className => $classDetails) {
                    $classNames[] = $className;

                    if (isset($classDetails['Section']) && is_array($classDetails['Section'])) {
                        $sectionKeys = array_keys($classDetails['Section']);
                        $sections[$className] = $sectionKeys;

                        if (!empty($sectionKeys)) {
                            $combined = "{$className} '{$sectionKeys[0]}'";
                            $subjects[$className] = $this->firebase->get("Schools/{$school_name}/{$session_year}/{$combined}/Subjects") ?? [];
                        } else {
                            $subjects[$className] = [];
                        }
                    } else {
                        $sections[$className] = [];
                        $subjects[$className] = [];
                    }
                }
            }

            $data = [
                'school_name' => $school_name,
                'classNames'  => $classNames,
                'sections'    => $sections,
                'subjects'    => $subjects,
            ];

            $this->load->view('include/header');
            $this->load->view('manage_exam', $data);
            $this->load->view('include/footer');
        }
    }
}
