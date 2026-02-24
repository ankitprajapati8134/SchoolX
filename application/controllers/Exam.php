<?php
class Exam extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }



    public function manage_exam()
    {
       
        $school_name = $this->school_name;
        $session_year = $this->session_year;


        // Fetch class-section mapping from Firebase
        $classesPath = "Schools/$school_name/$session_year/Classes";
        $classesData = $this->firebase->get($classesPath);

        $classSectionMap = [];
        if ($classesData) {

            foreach ($classesData as $className => $classDetails) {
                if (isset($classDetails['Section']) && is_array($classDetails['Section'])) {
                    $sectionKeys = array_keys($classDetails['Section']);
                    $firstSection = $sectionKeys[0] ?? ''; // Get the first section
                    if (!empty($firstSection)) {
                        $classSectionMap[$className] = $className . " '" . $firstSection . "'";
                    }
                }
            }
        }

        // Check if the request is POST
        if ($this->input->method() === 'post') {
           
            $examName = $this->input->post('examName');
            $gradingScale = $this->input->post('gradingScale');

            $generalInstructionsRaw = $this->input->post('generalInstructions');
            $startDate = $this->input->post('startDate');
            $endDate = $this->input->post('endDate');
            $examScheduleJson = $this->input->post('examSchedule');


            // Early validation for empty schedule
            if (empty($examScheduleJson)) {
                echo json_encode(['status' => 'error', 'message' => 'Exam schedule is empty. Please provide schedule data.']);
                return;
            }
            // Map input fields to their respective values
            $dateInputs = ['startDate' => $startDate, 'endDate' => $endDate];
            $formattedDates = []; // Store formatted dates

            foreach ($dateInputs as $inputField => $dateValue) {
                if (!empty($dateValue)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $dateValue); // Parse the date in yyyy-mm-dd format
                    if ($dateObj) {
                        $formattedDates[$inputField] = $dateObj->format('d-m-Y'); // Format to dd-mm-yyyy and assign to the correct key
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Invalid ' . ucfirst($inputField) . ' format.']);
                        return;
                    }
                } else {
                    $formattedDates[$inputField] = ''; // Set empty if the field is missing
                }
            }




            $generalInstructionsArray = array_map(function ($instruction) {
                // Remove bullets like •, -, * and extra whitespace
                return trim(preg_replace('/^[•\-\*\s]+/', '', $instruction));
            }, explode("\n", $generalInstructionsRaw));

            // Prepare the data with numeric keys
            $generalInstructions = [];
            foreach ($generalInstructionsArray as $key => $instruction) {
                if (!empty($instruction)) {
                    $generalInstructions[$key] = $instruction;
                }
            }



            $examData = [
                'gradingScale' => $gradingScale,
                'startDate' => $formattedDates['startDate'],
                'endDate' => $formattedDates['endDate'],
                'generalInstructions' => $generalInstructions
            ];


            $firebasePath = "Schools/$school_name/$session_year/Exams/Main Exams/$examName";
            $this->firebase->set($firebasePath, $examData);


            // Decode and process the exam schedule
            $examSchedule = json_decode($examScheduleJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format in exam schedule.']);
                return;
            }

            foreach ($examSchedule as $details) {
                $className = $details['className'] ?? null;
                $subject = $details['subject'] ?? null;
                $time = $details['time'] ?? null;
                $totalMarks = $details['totalMarks'] ?? null;
                $examDate = $details['date'] ?? null;

                // Validate required fields
                if (!$className || !$subject || !$time || !$totalMarks || !$examDate) {
                    log_message('error', "Missing schedule data for class: $className on date: $examDate");
                    continue;
                }

                // Format the date from dd/mm/yyyy to dd-mm-yyyy
                $formattedDate = DateTime::createFromFormat('d/m/Y', $examDate);
                if (!$formattedDate) {
                    log_message('error', "Invalid date format for date: $examDate");
                    continue;
                }
                $currentDate = $formattedDate->format('d-m-Y');

                // Convert time to 12-hour format with AM/PM
                $timeParts = explode(' - ', $time);
                if (count($timeParts) === 2) {
                    $startTime = DateTime::createFromFormat('H:i', trim($timeParts[0]));
                    $endTime = DateTime::createFromFormat('H:i', trim($timeParts[1]));

                    if ($startTime && $endTime) {
                        $formattedTime = $startTime->format('h:iA') . ' - ' . $endTime->format('h:iA');
                    } else {
                        log_message('error', "Invalid time format for: $time");
                        continue;
                    }
                } else {
                    log_message('error', "Invalid time format for: $time");
                    continue;
                }


                // Fetch the combined class-section
                $combinedClassSection = $classSectionMap[$className] ?? '';
                if ($combinedClassSection) {
                    $schedulePath = "Schools/$school_name/$session_year/$combinedClassSection/Exams/$examName/Details/$currentDate/$subject";
                    $this->firebase->set($schedulePath, [
                        'time' => $formattedTime,
                        'totalMarks' => $totalMarks
                    ]);
                } else {
                    log_message('error', "No section mapping found for class: $className");
                }
            }


            echo json_encode(['status' => 'success', 'message' => 'Exam and schedule saved successfully.']);
        } else {
            // Firebase path for fetching classes and sections
            $classesPath = "Schools/$school_name/$session_year/Classes";
            $classesData = $this->firebase->get($classesPath);

            $classNames = [];
            $sections = [];
            $subjects = [];

            if ($classesData) {
                foreach ($classesData as $className => $classDetails) {
                    $classNames[] = $className;

                    if (isset($classDetails['Section']) && is_array($classDetails['Section'])) {
                        $sectionKeys = array_keys($classDetails['Section']);
                        $sections[$className] = $sectionKeys;

                        if (!empty($sectionKeys)) {
                            $sectionOnly = $sectionKeys[0];
                            $combinedClassSection = $className . " '" . $sectionOnly . "'";

                            // Firebase path for subjects
                            $subjectsPath = "Schools/$school_name/$session_year/$combinedClassSection/Subjects";
                            $subjects[$className] = $this->firebase->get($subjectsPath) ?? [];
                        } else {
                            $subjects[$className] = [];
                        }
                    } else {
                        $sections[$className] = [];
                        $subjects[$className] = [];
                    }
                }
            }

            // Prepare data for view
            $data = [
                'school_name' => $school_name,
                'classNames'  => $classNames,
                'sections'    => $sections,
                'subjects'    => $subjects
            ];

            $this->load->view('include/header');
            $this->load->view('manage_exam', $data);
            $this->load->view('include/footer');
        }
    }
}
