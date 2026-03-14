<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Classes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function ensure_class_exists()
    {


        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class_label = $this->safe_path_segment(
            $this->input->post('class_name'),
            'class_name'
        );

        $classPath = "Schools/{$school_name}/{$session_year}/{$class_label}";

        $existing = $this->firebase->get($classPath);


        if (!$existing) {
            $this->firebase->set($classPath, new stdClass());
        }

        $this->json_success([
            'class' => $class_label
        ]);
    }

    public function manage_classes()
    {
        $school_id    = $this->parent_db_key;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;


        $this->load->view('include/header');
        $this->load->view('manage_classes');
        $this->load->view('include/footer');
    }





    public function section_students($class_slug, $section_slug)
    {
        $class = urldecode($class_slug);

        // Numeric class → add prefix
        if (is_numeric($class) || preg_match('/^\d+/', $class)) {
            $data['class_name'] = 'Class ' . $class;
        } else {
            // Nursery / LKG / UKG
            $data['class_name'] = $class;
        }

        $data['section_name'] = 'Section ' . urldecode($section_slug);

        $this->load->view('include/header');
        $this->load->view('section_students', $data);
        $this->load->view('include/footer');
    }

   public function fetch_classes_grid()
{
    header('Content-Type: application/json');

    $school_name  = $this->school_name;
    $session_year = $this->session_year;

    $path = "Schools/{$school_name}/{$session_year}";
    $data = $this->firebase->get($path);

    if (is_object($data)) $data = (array)$data;

    if (!is_array($data)) {
        echo json_encode([]);
        return;
    }

    $result = [];

    foreach ($data as $key => $value) {
        $name = trim((string)$key);

        if (is_numeric($name)) continue;
        if (is_object($value)) $value = (array)$value;
        if (!is_array($value)) continue;

        foreach ($value as $childKey => $childVal) {
            if (preg_match('/^Section\s+[A-Z]$/', $childKey)) {
                if (preg_match('/^Class\s+(Nursery|LKG|UKG)$/i', $name, $m)) {
                    $label = ucfirst(strtolower($m[1]));
                } else {
                    $label = $name;
                }

                $result[] = [
                    'key'   => $name,
                    'label' => $label
                ];
                break;
            }
        }
    }

    echo json_encode(array_values($result));
}



    public function fetch_class_sections()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;
        $class_name   = trim((string) $this->input->post('class_name'));

        if (!$class_name) {
            echo json_encode([]);
            return;
        }

        $class_name = $this->safe_path_segment($class_name, 'class_name');
        $path = "Schools/{$school_name}/{$session_year}/{$class_name}";
        $classData = $this->firebase->get($path);

        if (!$classData) {
            echo json_encode([]);
            return;
        }

        // 🔑 Normalize Firebase response
        if (is_object($classData)) {
            $classData = (array) $classData;
        }

        if (!is_array($classData)) {
            echo json_encode([]);
            return;
        }

        $sections = [];

        foreach ($classData as $key => $sectionData) {

            // ✅ Only accept top-level "Section X"
            if (!preg_match('/^Section\s+[A-Z]$/', $key)) {
                continue;
            }

            // Normalize section
            if (is_object($sectionData)) {
                $sectionData = (array) $sectionData;
            }

            $students = [];
            $studentCount = 0;

            // 🔑 Count from Section → Students → List
            if (
                isset($sectionData['Students']) &&
                isset($sectionData['Students']['List']) &&
                is_array($sectionData['Students']['List'])
            ) {
                foreach ($sectionData['Students']['List'] as $stuId => $stuName) {
                    $students[] = [
                        'id'   => $stuId,
                        'name' => $stuName
                    ];
                }

                $studentCount = count($students);
            }

            $sections[] = [
                'name'         => $key,
                'strength'     => $studentCount,
                'max_strength' => $sectionData['max_strength'] ?? 0,
                'students'     => $students
            ];
        }

        echo json_encode($sections);
    }

    public function add_section()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class_name   = trim((string) $this->input->post('class_name'));
        $section_name = trim((string) $this->input->post('section_name'));
        $max_strength = (int) $this->input->post('max_strength');

        if (!$class_name || !$section_name || $max_strength <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid input'
            ]);
            return;
        }

        // Normalize section name
        if (stripos($section_name, 'Section') !== 0) {
            $section_name = 'Section ' . strtoupper($section_name);
        }

        $class_name   = $this->safe_path_segment($class_name, 'class_name');
        $section_name = $this->safe_path_segment($section_name, 'section_name');
        $path = "Schools/{$school_name}/{$session_year}/{$class_name}/{$section_name}";

        // Prevent overwrite
        $existing = $this->firebase->get($path);
        if ($existing) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Section already exists'
            ]);
            return;
        }

        $this->firebase->set($path, [
            'max_strength' => $max_strength,
            'Students'     => new stdClass()
        ]);

        echo json_encode([
            'status'        => 'success',
            'section'       => $section_name,
            'max_strength'  => $max_strength
        ]);
    }

    public function fetch_sections_list()
    {
        header('Content-Type: application/json');

        $school = $this->school_name;
        $year   = $this->session_year;
        $class  = trim((string) $this->input->post('class_name'));

        if (!$class) {
            echo json_encode([]);
            return;
        }

        $class = $this->safe_path_segment($class, 'class_name');
        $path = "Schools/{$school}/{$year}/{$class}";
        $data = $this->firebase->get($path);

        if (is_object($data)) $data = (array)$data;

        $sections = [];

        foreach ($data as $key => $val) {
            if (preg_match('/^Section\s+[A-Z]$/', $key)) {
                $sections[] = $key;
            }
        }

        sort($sections); // A, B, C...

        echo json_encode($sections);
    }

    public function get_class_details()
    {
        header('Content-Type: application/json');

        $school_name = $this->school_name;

        $path = "Schools/{$school_name}/Subject_list";
        $classData = $this->CM->select_data($path);

        if (!$classData || !is_array($classData)) {
            echo json_encode([]);
            return;
        }

        $classes = [];

        foreach ($classData as $key => $data) {

            $name = trim((string)$key);

            // ✅ CASE 1: Nursery / LKG / UKG
            if (preg_match('/^(Nursery|LKG|UKG)$/i', $name)) {
                $classes[] = [
                    'value' => $name,
                    'label' => ucfirst(strtolower($name))
                ];
                continue;
            }

            // ✅ CASE 2: Numeric classes (4,5,6,8,12)
            if (is_numeric($name)) {
                $classes[] = [
                    'value' => $name,
                    'label' => $this->format_class_name($name) // Class 4th
                ];
            }
        }

        /**
         * SORT ORDER:
         * Nursery → LKG → UKG → Class 1st → Class 12th
         */
        usort($classes, function ($a, $b) {

            $order = [
                'nursery' => 0,
                'lkg'     => 1,
                'ukg'     => 2
            ];

            $aVal = strtolower($a['value']);
            $bVal = strtolower($b['value']);

            // Pre-primary order
            if (isset($order[$aVal]) && isset($order[$bVal])) {
                return $order[$aVal] <=> $order[$bVal];
            }

            if (isset($order[$aVal])) return -1;
            if (isset($order[$bVal])) return 1;

            // Numeric order
            return (int)$aVal <=> (int)$bVal;
        });

        echo json_encode(array_values($classes));
    }




    public function view($class_slug = null)
    {
        if (!$class_slug) {
            show_404();
            return;
        }

        // Convert slug to Firebase key
        // 8th → Class 8th
        $class_name = 'Class ' . urldecode($class_slug);

        $data['class_name'] = $class_name;

        $this->load->view('include/header');
        $this->load->view('class_profile', $data);
        $this->load->view('include/footer');
    }


    public function fetch_section_students()
    {
        header('Content-Type: application/json');

        $class   = trim((string) $this->input->post('class_name'));
        $section = trim((string) $this->input->post('section_name'));

        if (!$class || !$section) {
            echo json_encode([]);
            return;
        }

        $class   = $this->safe_path_segment($class, 'class_name');
        $section = $this->safe_path_segment($section, 'section_name');

        $school_name  = $this->school_name;
        $school_id    = $this->parent_db_key;
        $session_year = $this->session_year;

        $sectionPath = "Schools/{$school_name}/{$session_year}/{$class}/{$section}/Students/List";

        $studentList = $this->firebase->get($sectionPath);

        if (is_object($studentList)) {
            $studentList = (array) $studentList;
        }

        if (!is_array($studentList) || empty($studentList)) {
            echo json_encode([]);
            return;
        }

        $students = [];

        foreach ($studentList as $stuId => $stuName) {



            $userPath = "Users/Parents/{$school_id}/{$stuId}";
            $profile  = $this->firebase->get($userPath);


            if (is_object($profile)) {
                $profile = (array) $profile;
            }

            $students[] = [
                'id'          => $stuId,
                'name'        => $profile['Name'] ?? $stuName,
                'phone' => $profile['Phone Number'] ?? '-',
                'photo' => $profile['Profile Pic'] ?? null,
                'last_result' => $profile['Last_result'] ?? 'N/A'
            ];
        }



        echo json_encode($students);
    }




    public function get_section_settings()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class   = trim((string) $this->input->post('class_name'));
        $section = trim((string) $this->input->post('section_name'));

        if (!$class || !$section) {
            echo json_encode(['max_strength' => 0]);
            return;
        }

        $class   = $this->safe_path_segment($class, 'class_name');
        $section = $this->safe_path_segment($section, 'section_name');
        $path = "Schools/{$school_name}/{$session_year}/{$class}/{$section}";
        $data = $this->firebase->get($path);

        // Normalize Firebase response
        if (is_object($data)) {
            $data = (array) $data;
        }

        // If section node missing or invalid
        if (!is_array($data)) {
            echo json_encode(['max_strength' => 0]);
            return;
        }

        // ✅ ONLY RULE THAT MATTERS
        if (array_key_exists('max_strength', $data) && is_numeric($data['max_strength'])) {
            echo json_encode([
                'max_strength' => (int) $data['max_strength']
            ]);
            return;
        }

        // ❌ If key does NOT exist → return 0
        echo json_encode([
            'max_strength' => 0
        ]);
    }


    public function save_section_settings()
    {
        header('Content-Type: application/json');
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class        = trim((string) $this->input->post('class_name'));
        $section      = trim((string) $this->input->post('section_name'));
        $max_strength = (int) $this->input->post('max_strength');

        if (!$class || !$section || $max_strength <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid input'
            ]);
            return;
        }

        $class   = $this->safe_path_segment($class, 'class_name');
        $section = $this->safe_path_segment($section, 'section_name');
        $path = "Schools/{$school_name}/{$session_year}/{$class}/{$section}/max_strength";

        // 🔑 CREATE or UPDATE safely
        $this->firebase->set($path, $max_strength);

        echo json_encode([
            'status' => 'success'
        ]);
    }






    private function format_class_name($className)
    {
        if (!is_numeric($className)) {
            return $className;
        }

        $num = (int)$className;

        if ($num % 100 >= 11 && $num % 100 <= 13) {
            $suffix = 'th';
        } else {
            switch ($num % 10) {
                case 1:
                    $suffix = 'st';
                    break;
                case 2:
                    $suffix = 'nd';
                    break;
                case 3:
                    $suffix = 'rd';
                    break;
                default:
                    $suffix = 'th';
            }
        }

        return "Class {$num}{$suffix}";
    }

    public function loadClassesForTransfer()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        // 🔥 Path directly under session
        $basePath = 'Schools/' . $school_name . '/' . $session_year;

        // Fetch all session-level data
        $sessionData = $this->CM->get_data($basePath);

        $data = [
            'classes'  => [],
            'sections' => []
        ];

        if (!is_array($sessionData)) {
            echo json_encode($data);
            return;
        }

        foreach ($sessionData as $nodeKey => $nodeValue) {

            // ✅ Only allow keys starting with "Class "
            // ❌ Reject "Class 8th A", "Class 8th 'A'" etc.
            if (
                strpos($nodeKey, 'Class ') !== 0 ||      // must start with "Class "
                preg_match('/Class\s.+\s[A-Z]$/', $nodeKey) // exclude "Class 8th A"
            ) {
                continue;
            }

            if (!is_array($nodeValue)) continue;

            // Register class
            $data['classes'][$nodeKey] = $nodeKey;

            // Extract sections (keys starting with "Section ")
            $sections = [];

            foreach ($nodeValue as $sectionKey => $sectionValue) {
                if (strpos($sectionKey, 'Section ') === 0) {
                    $sections[] = $sectionKey;
                }
            }

            $data['sections'][$nodeKey] = $sections;
        }

        echo json_encode($data);
    }

    public function transfer_students()
    {
        header('Content-Type: application/json');

        $studentIds  = $this->input->post('student_ids');
        $fromClass   = trim((string) $this->input->post('from_class'));
        $fromSection = trim((string) $this->input->post('from_section'));
        $toClass     = trim((string) $this->input->post('to_class'));
        $toSection   = trim((string) $this->input->post('to_section'));

        $school_id = $this->parent_db_key;
        $school    = $this->school_name;
        $session   = $this->session_year;

        if (
            empty($studentIds) ||
            !$fromClass || !$fromSection ||
            !$toClass || !$toSection
        ) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            return;
        }

        $fromClass   = $this->safe_path_segment($fromClass, 'from_class');
        $fromSection = $this->safe_path_segment($fromSection, 'from_section');
        $toClass     = $this->safe_path_segment($toClass, 'to_class');
        $toSection   = $this->safe_path_segment($toSection, 'to_section');

        if ($fromClass === $toClass && $fromSection === $toSection) {
            echo json_encode(['status' => 'error', 'message' => 'Same section selected']);
            return;
        }

        $fromPath = "Schools/{$school}/{$session}/{$fromClass}/{$fromSection}/Students";
        $toPath   = "Schools/{$school}/{$session}/{$toClass}/{$toSection}/Students";

        // Check target section capacity before transfer
        $maxStrength = $this->firebase->get("Schools/{$school}/{$session}/{$toClass}/{$toSection}/max_strength");
        $toStudentsCheck = $this->firebase->get("{$toPath}/List") ?? [];
        $currentCount = is_array($toStudentsCheck) ? count($toStudentsCheck) : 0;
        $transferCount = count($studentIds);
        if ($maxStrength && ($currentCount + $transferCount) > (int)$maxStrength) {
            echo json_encode(['status' => 'error', 'message' => "Target section capacity exceeded ({$currentCount}/{$maxStrength}). Cannot add {$transferCount} more student(s)."]);
            return;
        }

        $fromStudents = (array) $this->CM->get_data($fromPath);
        $toStudents   = (array) $this->CM->get_data($toPath);

        // 🔥 Remove numeric junk keys
        $fromStudents = array_filter($fromStudents, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
        $toStudents   = array_filter($toStudents, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);

        $fromList = isset($fromStudents['List']) && is_array($fromStudents['List'])
            ? $fromStudents['List']
            : [];

        $toList = isset($toStudents['List']) && is_array($toStudents['List'])
            ? $toStudents['List']
            : [];

        foreach ($studentIds as $stuId) {

            if (!isset($fromList[$stuId])) continue;

            $studentName = $fromList[$stuId];

            /* ===============================
           1️⃣ MOVE STUDENT DATA (AS-IS)
        =============================== */

            // Copy FULL student node
            if (isset($fromStudents[$stuId])) {
                $toStudents[$stuId] = $fromStudents[$stuId];
                unset($fromStudents[$stuId]);
            }

            // Move List entry
            $toList[$stuId] = $studentName;
            unset($fromList[$stuId]);

            /* ===============================
           2️⃣ UPDATE USER PROFILE (CLEAN)
        =============================== */

            $cleanClass   = trim(str_ireplace('Class', '', $toClass));
            $cleanSection = trim(str_ireplace('Section', '', $toSection));

            $userPath = "Users/Parents/{$school_id}/{$stuId}";

            $this->firebase->update($userPath, [
                'Class'   => $cleanClass,
                'Section' => $cleanSection
            ]);
        }

        $fromStudents['List'] = $fromList;
        $toStudents['List']   = $toList;

        $this->firebase->set($fromPath, $fromStudents);
        $this->firebase->set($toPath, $toStudents);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Students transferred successfully'
        ]);
    }
}
