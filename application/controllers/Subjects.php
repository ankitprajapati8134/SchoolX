<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Subjects extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }



    public function manage_subjects()
    {
        $school_id    = $this->parent_db_key;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        // ---------------- POST: SAVE SUBJECTS ----------------
        if ($this->input->method() === 'post') {

            // ------------------ INPUTS ------------------
            $schoolName   = $this->input->post('school_name') ?: $school_name;
            $rawClassName = trim($this->input->post('class_name') ?? '');

            $subjectsRaw      = json_decode($this->input->post('subjects') ?? '[]', true) ?: [];
            $additionalsRaw   = json_decode($this->input->post('additionalsubjects') ?? '[]', true) ?: [];
            $patternTypeInput = (int) $this->input->post('pattern_type');

            if (defined('GRADER_DEBUG') && GRADER_DEBUG) log_message('debug', 'manage_subjects POST RAW: ' . json_encode($_POST));

            // ------------------ CLASS NORMALIZATION ------------------
            $raw = strtolower($rawClassName);
            $classKey = null;

            if (strpos($raw, 'nursery') !== false) {
                $classKey = 'Nursery';
            } elseif (strpos($raw, 'lkg') !== false) {
                $classKey = 'LKG';
            } elseif (strpos($raw, 'ukg') !== false) {
                $classKey = 'UKG';
            } elseif (strpos($raw, 'playgroup') !== false || strpos($raw, 'play') !== false) {
                $classKey = 'Playgroup';
            } elseif (preg_match('/\d+/', $rawClassName, $m)) {
                $classKey = (int) $m[0];
            }

            if ($classKey === null) {
                log_message('error', 'manage_subjects: Invalid class_name ' . $rawClassName);
                echo '0';
                return;
            }

            // ------------------ SUBJECT LIST ------------------
            $allSubjects = array_merge($subjectsRaw, $additionalsRaw);
            if (empty($allSubjects)) {
                log_message('error', 'manage_subjects: No subjects received');
                echo '0';
                return;
            }

            // ------------------ FIREBASE BASE PATH ------------------
            $subjectListBase = "Schools/{$schoolName}/Subject_list";

            // ------------------ SAVE PATTERN TYPE (11 / 12 ONLY) ------------------
            if (in_array($classKey, [11, 12], true) && $patternTypeInput > 0) {
                $this->firebase->set(
                    "{$subjectListBase}/{$classKey}/pattern_type",
                    $patternTypeInput
                );
            }

            // ------------------ SAVE SUBJECTS ------------------
            foreach ($allSubjects as $i => $item) {

                $subjectName = trim($item['name'] ?? '');
                if ($subjectName === '') continue;

                $category = trim($item['category'] ?? 'Core');
                $group    = trim($item['group'] ?? '');

                // ------------------ STREAM (ONLY FOR 11 / 12) ------------------
                $stream = 'common';

                if (in_array($classKey, [11, 12], true)) {

                    if (!empty($item['stream'])) {
                        $stream = ucfirst(trim($item['stream']));
                    } elseif (!empty($group) && strpos($group, 'Streams:') === 0) {
                        [, $streamName] = explode(':', $group, 2);
                        $stream = ucfirst(trim($streamName));
                    }
                }

                // ------------------ SUBJECT CODE ------------------
                if (is_numeric($classKey)) {
                    // Numeric classes → 101, 1201 etc
                    $code = (int) ($classKey . str_pad($i + 1, 2, '0', STR_PAD_LEFT));
                } else {
                    // String classes → NUR01, LKG01, UKG01, PLA01
                    $prefix = strtoupper(substr($classKey, 0, 3));
                    $code   = $prefix . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                }

                // ------------------ PAYLOAD ------------------
                if (in_array($classKey, [11, 12], true)) {
                    $payload = [
                        'category'     => $category,
                        'stream'       => $stream,
                        'subject_name' => $subjectName,
                        'subject_code' => $code
                    ];
                } else {
                    $payload = [
                        'category'     => $category,
                        'subject_name' => $subjectName,
                        'subject_code' => $code
                    ];
                }

                if (defined('GRADER_DEBUG') && GRADER_DEBUG) log_message('debug', "manage_subjects WRITE {$classKey}/{$code}: " . json_encode($payload));

                // ------------------ SAVE TO FIREBASE ------------------
                $this->firebase->set(
                    "{$subjectListBase}/{$classKey}/{$code}",
                    $payload
                );
            }

            echo '1';
            return;
        } else {
            // ---------------- GET: LOAD PAGE ----------------
            // Build class/section list from the correct Firebase structure:
            // Schools/{school}/{session}/Class 9th/Section A/...
            $classesData     = [];
            $sessionClassKeys = $this->firebase->shallow_get(
                'Schools/' . $school_name . '/' . $session_year
            );
            foreach ($sessionClassKeys as $classKey) {
                if (strpos($classKey, 'Class ') !== 0) continue;
                $sectionKeys = $this->firebase->shallow_get(
                    'Schools/' . $school_name . '/' . $session_year . '/' . $classKey
                );
                foreach ($sectionKeys as $sectionKey) {
                    if (strpos($sectionKey, 'Section ') !== 0) continue;
                    $classesData[] = [
                        'school_name' => $school_name,
                        'class_name'  => $classKey,
                        'section'     => str_replace('Section ', '', $sectionKey),
                    ];
                }
            }

            $data['selected_school'] = $school_name;
            $data['Classes']         = $classesData;

            $this->load->view('include/header');
            $this->load->view('manage_subjects', $data);
            $this->load->view('include/footer');
        }
    }

    public function fetch_subjects()
    {
        $school_id = $this->parent_db_key;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        $classRaw = trim(strtolower($this->input->post('class') ?? ''));
        if (empty($classRaw)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Class name required']);
            return;
        }

        $foundationalClasses = [
            'playgroup' => 'Playgroup',
            'nursery'   => 'Nursery',
            'lkg'       => 'LKG',
            'ukg'       => 'UKG'
        ];

        $classRange = '';
        $isFoundational = false;

        foreach ($foundationalClasses as $keyword => $range) {
            if (strpos($classRaw, $keyword) !== false) {
                $classRange = $range;
                $isFoundational = true;
                break;
            }
        }

        if (!$isFoundational) {
            preg_match('/\d+/', $classRaw, $matches);
            $classNumber = isset($matches[0]) ? (int)$matches[0] : null;

            if ($classNumber >= 1 && $classNumber <= 5) {
                $classRange = '1-5';
            } elseif ($classNumber >= 6 && $classNumber <= 8) {
                $classRange = '6-8';
            } elseif ($classNumber >= 9 && $classNumber <= 10) {
                $classRange = '9-10';
            } elseif ($classNumber >= 11 && $classNumber <= 12) {
                $classRange = '11-12';
            } else {
                $classRange = '';
            }
        }

        $this->load->library('firebase');
        $firebasePath = "/Subject Master_List/CBSE/CBSE_Pattern_2025_26/{$classRange}/";
        $subjectGroups = $this->firebase->get($firebasePath);

        $response = [];
        $collectedRules = []; // Store all rules

        // Go through each category, picking compulsory/info/rules
        if (!empty($subjectGroups)) {
            foreach ($subjectGroups as $groupName => $groupData) {
                // Process Assessment and Rules at classrange root
                if ($groupName === "Assessment") {
                    $response["Assessment"] = $groupData;
                    continue;
                }
                if ($groupName === "rules") {
                    // Merge these rules into top-level Rules
                    if (is_array($groupData)) {
                        $collectedRules = array_merge($collectedRules, $groupData);
                    } else {
                        $collectedRules[] = $groupData;
                    }
                    continue;
                }
                // For Streams, merge any found rules too
                if ($groupName === "Streams") {
                    $response["Streams"] = $groupData;
                    // Check for rules inside streams
                    foreach ($groupData as $streamKey => $stream) {
                        if (isset($stream["restrictions"])) {
                            $collectedRules = array_merge($collectedRules, $stream["restrictions"]);
                        }
                        if (isset($stream["note"])) {
                            $collectedRules[] = $stream["note"];
                        }
                        // You can expand merging as needed for other rule keys
                    }
                    continue;
                }

                // Standard category processing
                $category = [];
                // Get compulsory flag if exists
                $category["compulsory"] = isset($groupData["compulsory"]) ? $groupData["compulsory"] : false;

                // Get options (subjects list)
                if (isset($groupData["options"])) {
                    $category["options"] = $groupData["options"];
                } elseif (is_array($groupData)) {
                    // Handle case where groupData is only array of subject names
                    $category["options"] = $groupData;
                } else {
                    $category["options"] = [];
                }

                // Get category-level rules, if exist, and merge them to global
                foreach ($groupData as $key => $value) {
                    if (!in_array($key, ["compulsory", "options"])) {
                        if (!isset($category["rules"])) $category["rules"] = [];
                        $category["rules"][$key] = $value;
                        // Also collect for global Rules output
                        $collectedRules[$groupName . "_" . $key] = $value;
                    }
                }

                $response[$groupName] = $category;
            }
        }

        // Merge in global CBSE language list if desired for certain categories
        // --- example: you may place this logic per your requirements ---

        // Append full merged rules to the result
        if (!empty($collectedRules)) {
            $response["rules"] = $collectedRules;
        }

        echo json_encode($response);
    }

    public function get_class_details()
    {
        header('Content-Type: application/json');

        $school_name = $this->school_name;
        $session_year = $this->session_year;

        $schoolName = $this->input->post('school_name');
        $className = trim((string) $this->input->post('class_name'));
        $section = trim((string) $this->input->post('section'));
        $classIsNumeric = is_numeric($className);

        if (is_string($className) && strpos($className, 'Class') === false) {
            if ($classIsNumeric) {
                $classNumber = filter_var($className, FILTER_SANITIZE_NUMBER_INT); // Extract numeric part
                $suffix = '';
                if ($classNumber % 10 == 1 && $classNumber % 100 != 11) {
                    $suffix = 'st';
                } elseif ($classNumber % 10 == 2 && $classNumber % 100 != 12) {
                    $suffix = 'nd';
                } elseif ($classNumber % 10 == 3 && $classNumber % 100 != 13) {
                    $suffix = 'rd';
                } else {
                    $suffix = 'th';
                }
                $className = "Class {$classNumber}{$suffix}";
            } else {
                $className = "Class {$className}";
            }
        }


        $classSection = $className . " '" . trim($section) . "'";
        $classSection = $this->safe_path_segment($classSection, 'class_section');
        $path = "Schools/{$school_name}/{$session_year}/{$classSection}";

        $classData = $this->CM->select_data($path);

        if ($classData) {
            // Extract subjects and additional subjects
            // Convert Subjects & AdditionalSubjects to arrays if they are associative
            $subjectCodes = isset($classData['Subjects']) ? array_keys($classData['Subjects']) : [];
            $additionalCodes = isset($classData['AdditionalSubjects']) ? array_keys($classData['AdditionalSubjects']) : [];

            // Remove additional subjects from subjects list
            $filteredCodes = array_values(array_diff($subjectCodes, $additionalCodes));

            // Look up subject names from Subject_list
            $raw = strtolower(trim($className));
            if (strpos($raw, 'nursery') !== false) { $numKey = 'Nursery'; }
            elseif (strpos($raw, 'lkg') !== false) { $numKey = 'LKG'; }
            elseif (strpos($raw, 'ukg') !== false) { $numKey = 'UKG'; }
            elseif (strpos($raw, 'playgroup') !== false || strpos($raw, 'play') !== false) { $numKey = 'Playgroup'; }
            elseif (preg_match('/\d+/', $className, $m)) { $numKey = (int) $m[0]; }
            else { $numKey = null; }

            $nameMap = [];
            if ($numKey !== null) {
                $subjectListData = $this->firebase->get("Schools/{$school_name}/Subject_list/{$numKey}") ?? [];
                if (is_array($subjectListData)) {
                    foreach ($subjectListData as $code => $sub) {
                        if (!is_array($sub)) continue;
                        $nameMap[$code] = $sub['subject_name'] ?? $sub['name'] ?? (string) $code;
                    }
                }
            }

            // Build enriched arrays: each item has code + name
            $subjects = [];
            foreach ($filteredCodes as $code) {
                $subjects[] = [
                    'code' => (string) $code,
                    'name' => $nameMap[$code] ?? (string) $code,
                ];
            }
            $optionalSubjects = [];
            foreach ($additionalCodes as $code) {
                $optionalSubjects[] = [
                    'code' => (string) $code,
                    'name' => $nameMap[$code] ?? (string) $code,
                ];
            }

            $response = [
                "subjects"         => $subjects,
                "optionalSubjects" => $optionalSubjects,
                "timetable"        => $classData['Time_table'] ?? ""
            ];

            echo json_encode($response);
        } else {
            echo json_encode([]);
        }
    }


    public function update_class_details()
    {
        $session_year = $this->session_year;
        $school_name  = $this->school_name;

        if ($this->input->method() == 'post') {
            $schoolName = $school_name; // use session school, not POST
            $prevClassName = trim((string) $this->input->post('prev_class_name'));
            $prevSection = trim((string) $this->input->post('prev_section'));
            $className = trim((string) $this->input->post('edit_class_name'));
            $section = trim((string) $this->input->post('edit_section'));
            $subjects = json_decode($this->input->post('subjects'), true) ?: [];
            $optionalSubjects = json_decode($this->input->post('optional_subjects'), true) ?: [];

            // Ensure class naming format
            $className = $this->format_class_name($className);
            $prevClassName = $this->format_class_name($prevClassName);

            if (empty($section)) {
                $section = 'A';
            }

            $sections = explode(',', $section);
            $prevClassSection = "$prevClassName '$prevSection'";
            $newClassSection = "$className '$section'";
            $prevClassSection = $this->safe_path_segment($prevClassSection, 'prev_class_section');
            $newClassSection  = $this->safe_path_segment($newClassSection, 'new_class_section');

            $prevPath = "Schools/{$schoolName}/{$session_year}/{$prevClassSection}";
            $newPath = "Schools/{$schoolName}/{$session_year}/{$newClassSection}";

            // Step 1: Copy previous class data
            $prevData = $this->CM->get_data($prevPath);
            if (!$prevData) {
                echo "error: Previous class data not found";
                return;
            }

            // Step 2: Remove previous class-section node (classes live at session root, NOT /Classes/)
            $this->CM->delete_data("Schools/$schoolName/$session_year/$prevClassSection", null);

            // Copy previous data to the new location
            $this->CM->addKey_pair_data($newPath, $prevData);

            // Step 4: Update subjects & additional subjects
            foreach ($sections as $section) {
                $path = "Schools/$schoolName/$session_year/$className '$section'";

                // Merging subjects and ensuring key-value structure
                $mergedSubjects = array_fill_keys($subjects, "");  // Regular subjects
                $optionalSubjectsArray = array_fill_keys($optionalSubjects, "");  // Optional subjects

                // Data structure for Firebase
                $data = [
                    'Subjects' => $mergedSubjects,
                    'AdditionalSubjects' => $optionalSubjectsArray  // Keeping optional subjects separate
                ];
                // Save data to Firebase
                $this->CM->addKey_pair_data($path, $data);
            }

            // Step 5: Handle timetable file replacement
            $prevClassData = $this->firebase->get("Schools/$schoolName/$session_year/$className '$section'");
            if (!empty($prevClassData['Time_table'])) {
                $prevFilePath = $this->extract_firebase_storage_path($prevClassData['Time_table']);
                $this->CM->delete_file_from_firebase($prevFilePath);
            }

            if (isset($_FILES['timetable_file']) && $_FILES['timetable_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['timetable_file'];
                $folderName = "$className '$section'";
                $uploadResult = $this->CM->handleFileUpload($file, $schoolName, $folderName, 'time_table', true);

                if ($uploadResult) {
                    $this->CM->addKey_pair_data("Schools/$schoolName/$session_year/$className '$section'/", ['Time_table' => $uploadResult]);
                } else {
                    echo "Failed to upload new timetable";
                    return;
                }
            }

            echo "success";
        }
    }

    
    private function format_class_name($className)
    {
        if (is_string($className) && strpos($className, 'Class') === false) {
            if (is_numeric($className)) {
                $classNumber = filter_var($className, FILTER_SANITIZE_NUMBER_INT);
                $suffix = ($classNumber % 10 == 1 && $classNumber % 100 != 11) ? 'st' : (($classNumber % 10 == 2 && $classNumber % 100 != 12) ? 'nd' : (($classNumber % 10 == 3 && $classNumber % 100 != 13) ? 'rd' : 'th'));
                return "Class {$classNumber}{$suffix}";
            } else {
                return "Class {$className}";
            }
        }
        return $className;
    }

    /**
     * Extracts Firebase Storage file path from a URL.
     */
    private function extract_firebase_storage_path($url)
    {
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        return str_replace(['/v0/b/graders-1c047.appspot.com/o/', '%2F'], ['', '/'], urldecode($parsedUrl));
    }


    public function upload_timetable()
    {
        $session_year = $this->session_year;
        $school_name  = $this->school_name;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $schoolName = $school_name; // use session school, not POST
            $className  = trim((string) $this->input->post('class_name'));
            $section    = trim((string) $this->input->post('section'));

            // Check if the class name and section are provided
            if (empty($className) || empty($section)) {
                echo "Class name or section is missing.";
                return;
            }

            $folderName = "$className '$section'"; // Combine class name and section

            // Handle file upload
            if (!isset($_FILES['timetable_file']) || $_FILES['timetable_file']['error'] !== UPLOAD_ERR_OK) {
                echo "Error uploading file.";
                return;
            }

            // Upload the file to Firebase Storage using Common_model method
            $file = $_FILES['timetable_file'];
            $uploadResult = $this->CM->handleFileUpload($file, $schoolName, $folderName, 'time_table', true);

            if ($uploadResult) {
                // File uploaded successfully
                // Example: Store the timetable URL in Firebase Realtime Database
                $firebasePath = "Schools/$schoolName/$session_year/$className '$section'/";
                $updateData = ['Time_table' => $uploadResult]; // Assuming $uploadResult is the URL
                $updateResult = $this->CM->addKey_pair_data($firebasePath, $updateData);
                if ($updateResult) {
                    redirect('classes/manage_classes');
                } else {
                    echo "Failed to store URL in Firebase.";
                }
            } else {
                // File upload failed
                echo "Failed to upload file to Firebase Storage.";
            }
        } else {
            // Handle invalid request method
            echo "Invalid request method.";
        }
    }

    public function delete_class()
    {
        $session_year = $this->session_year;
        $school_name  = $this->school_name;

        if ($this->input->method() == 'post') {
            $className = trim((string) $this->input->post('class_name'));
            $section   = trim((string) $this->input->post('section'));

            if (!$className || !$section) {
                echo '0';
                return;
            }

            // Classes live at session root as "Class 9th 'A'" nodes, NOT under /Classes/
            $classSection = $className . " '" . trim($section) . "'";
            $classSection = $this->safe_path_segment($classSection, 'class_section');
            $sectionPath = "Schools/{$school_name}/{$session_year}/{$classSection}";

            // Check for enrolled students before deleting
            $students = $this->firebase->get("{$sectionPath}/Students/List");
            if (!empty($students) && is_array($students)) {
                echo 'Cannot delete: section has ' . count($students) . ' enrolled student(s). Transfer them first.';
                return;
            }

            $result = $this->CM->delete_data("Schools/{$school_name}/{$session_year}", $classSection);

            if ($result) {
                redirect(base_url() . 'classes/manage_classes/');
            } else {
                echo '0';
            }
        } else {
            echo 'Invalid request method.';
        }
    }


    public function class_profile()
    {
        $school_id = $this->parent_db_key;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        // Get the encoded class name from the URL
        $encodedClassSection = $this->input->get('class_name');
        $decodedClassSection = urldecode($encodedClassSection);

        // Fetch data from Firebase
        $classPath = "Schools/$school_name/$session_year/{$decodedClassSection}";
        $classData = $this->firebase->get($classPath);

        if (!$classData) {
            show_error('No data found for the specified class.', 404);
            return;
        }

        // Process student data
        $students = [];
        if (isset($classData['Students']['List']) && is_array($classData['Students']['List'])) {
            foreach ($classData['Students']['List'] as $userId => $studentName) {
                // Skip invalid or empty user IDs or missing student names
                if (empty($userId) || empty($studentName) || $studentName === 'Not Found') {
                    continue;
                }

                // Fetch father name
                $fatherNamePath = "Users/Parents/$school_id/{$userId}/Father Name";
                $fatherName = $this->firebase->get($fatherNamePath);

                $students[] = [
                    'user_id' => $userId,
                    'student_name' => $studentName,
                    'father_name' => $fatherName ?? 'Not Found'
                ];
            }
        }


        // Process subjects and additional subjects
        $subjects = [];
        $additionalSubjects = [];
        $additionalSubjectStudents = [];

        if (isset($classData['Subjects']) && is_array($classData['Subjects'])) {
            foreach (array_keys($classData['Subjects']) as $subject) {
                $subjects[] = $subject;
            }
        }

        if (isset($classData['AdditionalSubjects']) && is_array($classData['AdditionalSubjects'])) {
            foreach (array_keys($classData['AdditionalSubjects']) as $subject) {
                $additionalSubjects[] = $subject;

                // Fetch student list for this optional subject
                $subjectListKey = "List_{$subject}";
                if (isset($classData[$subjectListKey]) && is_array($classData[$subjectListKey])) {
                    $additionalSubjectStudents[$subject] = array_keys($classData[$subjectListKey]);
                } else {
                    $additionalSubjectStudents[$subject] = []; // No students found for this optional subject
                }
            }
        }

        // Get time table URL
        $timeTableUrl = $classData['Time_table'] ?? null;

        // Pass data to the view
        $data = [
            'class_name' => $decodedClassSection,
            'class_teacher' => $classData['ClassTeacher'] ?? 'Not Assigned',
            'total_students' => count($students),
            'students' => $students,
            'subjects' => $subjects,
            'additionalSubjects' => $additionalSubjects,
            'additionalSubjectStudents' => $additionalSubjectStudents, // User IDs grouped by optional subject
            'time_table_url' => $timeTableUrl
        ];
        $this->load->view('include/header');
        $this->load->view('class_profile', $data);
        $this->load->view('include/footer');
    }


    public function get_class_data($class_name)
    {
        $school_id = $this->parent_db_key;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        // Decode the class name to handle any special characters or spaces
        $class_name = urldecode($class_name);

        // Construct the Firebase path using the decoded class_name
        $class_path = "Schools/$school_name/$session_year/{$class_name}";

        // Fetch class data from Firebase
        $class_data = $this->firebase->get($class_path);

        if (!$class_data || !is_array($class_data)) {
            return $this->json_error('No data found for this class');
        }

        $students = [];
        if (isset($class_data['Students']['List']) && is_array($class_data['Students']['List'])) {
            foreach ($class_data['Students']['List'] as $user_id => $student_name) {
                $father_name = $this->firebase->get("Users/Parents/$school_id/{$user_id}/Father Name") ?? 'N/A';
                $students[] = [
                    'user_id' => $user_id,
                    'student_name' => $student_name,
                    'father_name' => $father_name
                ];
            }
        }

        return $this->json_success([
            'class_teacher'  => $class_data['Class Teacher'] ?? 'N/A',
            'total_students' => count($students),
            'students'       => $students
        ]);
    }


}
