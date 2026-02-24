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
        $this->verify_csrf();

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
        $school_id    = $this->school_id;
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

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            echo json_encode([]);
            return;
        }

        $result = [];

        foreach ($data as $key => $value) {

            $name = trim((string)$key);

            /* =====================================
           NORMALIZE PRE-PRIMARY CLASSES
        ===================================== */

            // Handles: Nursery, Class Nursery
            if (preg_match('/^(Class\s+)?(Nursery|LKG|UKG)$/i', $name, $m)) {

                $clean = ucfirst(strtolower($m[2])); // Nursery / Lkg / Ukg

                $result[] = [
                    'key'   => $clean,
                    'label' => $clean
                ];
                continue;
            }

            /* =====================================
           NUMERIC CLASSES ONLY
        ===================================== */

            if (preg_match('/^Class\s+\d+(st|nd|rd|th)$/i', $name)) {
                $result[] = [
                    'key'   => $name,
                    'label' => $name
                ];
                continue;
            }
        }

        /**
         * SORT ORDER:
         * Nursery → LKG → UKG → Class 1st → Class 12th
         */
        usort($result, function ($a, $b) {

            $order = [
                'nursery' => 0,
                'lkg'     => 1,
                'ukg'     => 2
            ];

            $aKey = strtolower($a['key']);
            $bKey = strtolower($b['key']);

            if (isset($order[$aKey]) && isset($order[$bKey])) {
                return $order[$aKey] <=> $order[$bKey];
            }

            if (isset($order[$aKey])) return -1;
            if (isset($order[$bKey])) return 1;

            preg_match('/\d+/', $aKey, $aNum);
            preg_match('/\d+/', $bKey, $bNum);

            return ((int)$aNum[0]) <=> ((int)$bNum[0]);
        });

        echo json_encode(array_values($result));
    }



    public function fetch_class_sections()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;
        $class_name   = $this->input->post('class_name');

        if (!$class_name) {
            echo json_encode([]);
            return;
        }

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

        $class_name   = $this->input->post('class_name');
        $section_name = trim($this->input->post('section_name'));
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
        $class  = $this->input->post('class_name');

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

    // public function get_class_details()
    // {
    //     $school_name = $this->school_name;

    //     // New path
    //     $path = "Schools/{$school_name}/Subject_list";

    //     $classData = $this->CM->select_data($path);

    //     if (!$classData || !is_array($classData)) {
    //         echo json_encode([]);
    //         return;
    //     }

    //     $classes = [];

    //     foreach ($classData as $classNumber => $data) {
    //         if (!is_numeric($classNumber)) continue;

    //         $classes[] = [
    //             'value' => $classNumber,
    //             'label' => $this->format_class_name($classNumber)
    //         ];
    //     }

    //     // Sort numerically (5,6,7,11,12)
    //     usort($classes, function ($a, $b) {
    //         return (int)$a['value'] <=> (int)$b['value'];
    //     });

    //     echo json_encode($classes);
    // }


    public function get_class_details()
    {
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

        $class   = $this->input->post('class_name');
        $section = $this->input->post('section_name');


        if (!$class || !$section) {
            log_message('error', 'Missing class or section');
            echo json_encode([]);
            return;
        }

        $school_name  = $this->school_name;
        $school_id    = $this->school_id;
        $session_year = $this->session_year;

        $sectionPath = "Schools/$school_name/$session_year/{$class}/{$section}/Students/List";

        $studentList = $this->firebase->get($sectionPath);

        if (is_object($studentList)) {
            $studentList = (array) $studentList;
        }

        if (!is_array($studentList) || empty($studentList)) {
            log_message('error', 'Student list empty or invalid');
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

        $class   = $this->input->post('class_name');
        $section = $this->input->post('section_name');

        if (!$class || !$section) {
            echo json_encode(['max_strength' => 0]);
            return;
        }

        $path = "Schools/$school_name/$session_year/{$class}/{$section}";
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
        $school_name = $this->school_name;
        $session_year   = $this->session_year;

        $class        = $this->input->post('class_name');
        $section      = $this->input->post('section_name');
        $max_strength = (int) $this->input->post('max_strength');

        if (!$class || !$section || $max_strength <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid input'
            ]);
            return;
        }

        $path = "Schools/$school_name/$session_year/{$class}/{$section}/max_strength";

        // 🔑 CREATE or UPDATE safely
        $this->firebase->set($path, $max_strength);

        echo json_encode([
            'status' => 'success'
        ]);
    }



    public function load_timetable_partial()
    {
        $data = [];

        $data['class_name']   = $this->input->post('class_name');
        $data['section_name'] = $this->input->post('section_name');
        $data['session_year'] = $this->session_year;
        $data['school_name']  = $this->school_name;

        echo $this->load->view(
            'partials/timetable_view',
            $data,
            true
        );
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

    private function get_section_students_array($class, $section)
    {
        $school_name  = $this->school_name;
        $school_id    = $this->school_id;
        $session_year = $this->session_year;

        $path = "Schools/$school_name/$session_year/$class/$section/Students/List";
        $studentList = $this->firebase->get($path);

        if (is_object($studentList)) {
            $studentList = (array)$studentList;
        }

        if (!is_array($studentList)) {
            return [];
        }

        $students = [];

        foreach ($studentList as $stuId => $stuName) {
            $profile = $this->firebase->get("Users/Parents/$school_id/$stuId");
            if (is_object($profile)) $profile = (array)$profile;

            $students[] = [
                'id'          => $stuId,
                'name'        => $profile['Name'] ?? $stuName,
                'phone'       => $profile['Phone Number'] ?? '-',
                'photo'       => $profile['Profile Pic'] ?? null,
                'last_result' => $profile['Last_result'] ?? 'N/A'
            ];
        }

        return $students;
    }



    public function get_timetable_settings()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $path = "Schools/{$school_name}/{$session_year}/Time_table_settings";
        $data = $this->firebase->get($path);

        // Normalize root
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            echo json_encode([]);
            return;
        }

        /* =====================================================
       ✅ NEW STRUCTURE (AUTHORITATIVE)
    ===================================================== */
        if (isset($data['Recesses']) && is_array($data['Recesses'])) {

            $normalizedRecesses = [];

            foreach ($data['Recesses'] as $r) {

                if (is_object($r)) {
                    $r = (array) $r;
                }

                if (
                    isset($r['after_period'], $r['duration']) &&
                    is_numeric($r['after_period']) &&
                    is_numeric($r['duration'])
                ) {
                    $normalizedRecesses[] = [
                        'after_period' => (int) $r['after_period'],
                        'duration'     => (int) $r['duration']
                    ];
                }
            }

            echo json_encode([
                'Start_time'       => $data['Start_time'] ?? null,
                'End_time'         => $data['End_time'] ?? null,
                'No_of_periods'    => isset($data['No_of_periods']) ? (int)$data['No_of_periods'] : 0,
                'Length_of_period' => isset($data['Length_of_period']) ? (float)$data['Length_of_period'] : 0,
                'Recesses'         => $normalizedRecesses
            ]);
            return;
        }

        /* =====================================================
       🔄 BACKWARD COMPATIBILITY (OLD DATA)
    ===================================================== */
        $convertedRecesses = [];

        if (isset($data['Recess_breaks']) && is_array($data['Recess_breaks'])) {

            foreach ($data['Recess_breaks'] as $range) {

                if (!is_string($range) || !str_contains($range, '-')) {
                    continue;
                }

                [$from, $to] = array_map('trim', explode('-', $range));

                $from24 = $this->ampmTo24($from);
                $to24   = $this->ampmTo24($to);

                $fromMin = $this->toMinutes($from24);
                $toMin   = $this->toMinutes($to24);

                if ($toMin > $fromMin) {
                    $convertedRecesses[] = [
                        'after_period' => null,
                        'duration'     => $toMin - $fromMin
                    ];
                }
            }
        }

        echo json_encode([
            'Start_time'       => $data['Start_time'] ?? null,
            'End_time'         => $data['End_time'] ?? null,
            'No_of_periods'    => isset($data['No_of_periods']) ? (int)$data['No_of_periods'] : 0,
            'Length_of_period' => isset($data['Length_of_period']) ? (float)$data['Length_of_period'] : 0,
            'Recesses'         => $convertedRecesses
        ]);
    }



    private function toMinutes($time24)
    {
        if (!$time24 || !str_contains($time24, ':')) return 0;

        [$h, $m] = explode(':', $time24);
        return ((int)$h * 60) + (int)$m;
    }


    private function toAmPm($time24)
    {
        if (!$time24) return null;

        $dt = DateTime::createFromFormat('H:i', $time24);
        if (!$dt) return null;

        return $dt->format('h:iA');
    }

    private function ampmTo24($time)
    {
        if (!$time) return null;

        $dt = DateTime::createFromFormat('h:iA', strtoupper(trim($time)));
        if (!$dt) return null;

        return $dt->format('H:i');
    }




    public function load_timetable()
    {
        header('Content-Type: application/json');

        $class   = $this->input->post('class_name');
        $section = $this->input->post('section_name');

        if (!$class || !$section) {
            echo json_encode([]);
            return;
        }

        $path = "Schools/{$this->school_name}/{$this->session_year}/{$class}/{$section}/Time_table";

        $data = $this->firebase->get($path);

        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        echo json_encode(is_array($data) ? $data : []);
    }


    public function save_timetable_settings()
    {
        header('Content-Type: application/json');

        $school = $this->school_name;
        $year   = $this->session_year;

        $startRaw = $this->input->post('start_time'); // HH:mm
        $endRaw   = $this->input->post('end_time');   // HH:mm
        $periods  = (int) $this->input->post('no_of_periods');
        $recesses = $this->input->post('recesses') ?? [];

        if (!$startRaw || !$endRaw || $periods <= 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Invalid timetable input'
            ]);
            return;
        }

        // Convert times to minutes
        $startMin = $this->toMinutes($startRaw);
        $endMin   = $this->toMinutes($endRaw);

        if ($endMin <= $startMin) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'End time must be after start time'
            ]);
            return;
        }

        // 🔥 Calculate total recess minutes (NEW STRUCTURE)
        $recessMinutes = 0;

        foreach ($recesses as $recess) {
            if (
                is_array($recess) &&
                isset($recess['duration']) &&
                (int)$recess['duration'] > 0
            ) {
                $recessMinutes += (int) $recess['duration'];
            }
        }

        $availableMinutes = $endMin - $startMin - $recessMinutes;

        if ($availableMinutes <= 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Recess duration exceeds available time'
            ]);
            return;
        }

        // ✅ AUTHORITATIVE calculation
        $periodLength = round($availableMinutes / $periods, 1);

        $data = [
            'Start_time'        => $this->toAmPm($startRaw),
            'End_time'          => $this->toAmPm($endRaw),
            'No_of_periods'     => $periods,
            'Length_of_period'  => $periodLength,
            'Recesses'          => array_values($recesses)
        ];

        $path = "Schools/{$school}/{$year}/Time_table_settings";
        $this->firebase->set($path, $data);

        echo json_encode([
            'status' => 'success',
            'length' => $periodLength
        ]);
    }





    public function fetch_subjects_for_timetable()
    {
        header('Content-Type: application/json');

        $school_name = $this->school_name;
        $class_name  = $this->input->post('class_name'); // "Class 8th"

        if (!$class_name) {
            echo json_encode([
                'class_subjects' => [],
                'all_subjects'   => []
            ]);
            return;
        }

        // 🔑 Extract numeric class (Class 8th → 8)
        if (!preg_match('/(\d+)/', $class_name, $m)) {
            echo json_encode([
                'class_subjects' => [],
                'all_subjects'   => []
            ]);
            return;
        }

        $requestedClassNum = (int)$m[1];

        $path = "Schools/{$school_name}/Subject_list";
        $data = $this->firebase->get($path);

        if (is_object($data)) $data = (array)$data;
        if (!is_array($data)) {
            echo json_encode([
                'class_subjects' => [],
                'all_subjects'   => []
            ]);
            return;
        }

        $classSubjects = [];
        $allSubjects   = [];

        foreach ($data as $classNum => $subjects) {

            // ❌ Ignore non-numeric or empty class nodes
            if (!is_numeric($classNum)) continue;

            if (is_object($subjects)) $subjects = (array)$subjects;
            if (!is_array($subjects) || empty($subjects)) continue;

            foreach ($subjects as $subject) {

                if (is_object($subject)) $subject = (array)$subject;
                if (empty($subject['subject_name'])) continue;

                $name = trim($subject['subject_name']);

                // 🔹 ALL SUBJECTS (unique by name)
                $allSubjects[$name] = [
                    'name' => $name
                ];

                // 🔹 SUBJECTS FOR SELECTED CLASS
                if ((int)$classNum === $requestedClassNum) {
                    $classSubjects[$name] = [
                        'name' => $name
                    ];
                }
            }
        }

        echo json_encode([
            'class_subjects' => array_values($classSubjects),
            'all_subjects'   => array_values($allSubjects)
        ]);
    }




    public function save_timetable()
    {
        header('Content-Type: application/json');

        $class   = $this->input->post('class_name');
        $section = $this->input->post('section_name');
        $raw     = $this->input->post('timetable');

        $timetable = json_decode($raw, true);

        if (!is_array($timetable)) {
            echo json_encode(['status' => 'error']);
            return;
        }

        $path = "Schools/{$this->school_name}/{$this->session_year}/{$class}/{$section}/Time_table";

        // 🔥 ONE SAFE WRITE
        $this->firebase->set($path, $timetable);

        echo json_encode(['status' => 'success']);
    }


    public function class_profile()
    {
        $school_id = $this->school_id;
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
        log_message('error', 'Class Profile Data: ' . print_r($data, true));

        $this->load->view('include/header');
        $this->load->view('class_profile', $data);
        $this->load->view('include/footer');
    }


    public function loadClassesForTransfer()
    {
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
        $studentIds  = $this->input->post('student_ids');
        $fromClass   = $this->input->post('from_class');
        $fromSection = $this->input->post('from_section');
        $toClass     = $this->input->post('to_class');
        $toSection   = $this->input->post('to_section');

        $school_id = $this->school_id;
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

        if ($fromClass === $toClass && $fromSection === $toSection) {
            echo json_encode(['status' => 'error', 'message' => 'Same section selected']);
            return;
        }

        $fromPath = "Schools/{$school}/{$session}/{$fromClass}/{$fromSection}/Students";
        $toPath   = "Schools/{$school}/{$session}/{$toClass}/{$toSection}/Students";

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






      // public function section_students($class_slug, $section_slug)
    // {
    //     $data['class_name']   = 'Class ' . urldecode($class_slug);
    //     $data['section_name'] = 'Section ' . urldecode($section_slug);

    //     $this->load->view('include/header');
    //     $this->load->view('section_students', $data);
    //     $this->load->view('include/footer');
    // }

    // public function fetch_classes_grid()
    // {
    //     header('Content-Type: application/json');

    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     $path = "Schools/{$school_name}/{$session_year}";
    //     $data = $this->firebase->get($path);

    //     $result = [];

    //     if (is_object($data)) {
    //         $data = (array) $data;
    //     }

    //     if (!is_array($data)) {
    //         echo json_encode([]);
    //         exit;
    //     }

    //     foreach ($data as $key => $value) {

    //         /**
    //          * ACCEPT BOTH:
    //          * Class_8th
    //          * Class 8th
    //          */
    //         if (!preg_match('/^Class[ _]\d+(st|nd|rd|th)$/i', $key)) {
    //             continue;
    //         }

    //         $result[] = [
    //             'key'   => $key,
    //             'label' => $key // already human-readable
    //         ];
    //     }

    //     // Sort numerically (8th, 9th, 10th)
    //     usort($result, function ($a, $b) {
    //         preg_match('/\d+/', $a['key'], $aNum);
    //         preg_match('/\d+/', $b['key'], $bNum);
    //         return (int)$aNum[0] <=> (int)$bNum[0];
    //     });

    //     echo json_encode(array_values($result));
    //     exit;
    // }
    // public function get_timetable_settings()
    // {
    //     header('Content-Type: application/json');

    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     $path = "Schools/{$school_name}/{$session_year}/Time_table_settings";
    //     $data = $this->firebase->get($path);

    //     if (is_object($data)) {
    //         $data = (array) $data;
    //     }

    //     if (!is_array($data)) {
    //         echo json_encode([]);
    //         return;
    //     }

    //     if (isset($data['Recesses']) && is_array($data['Recesses'])) {
    //         echo json_encode($data);
    //         return;
    //     }

    //     // 🧹 backward compatibility (old data)
    //     $recesses = [];
    //     foreach ($data as $key => $value) {
    //         if (str_starts_with($key, 'Recess_break')) {
    //             $recesses[] = $value;
    //         }
    //     }

    //     $data['Recess_breaks'] = $recesses;
    //     if (!isset($data['No_of_periods'])) {
    //         $data['No_of_periods'] = 0; // backward compatibility
    //     }

    //     echo json_encode($data);
    // }



    // public function load_students_partial()
    // {
    //     $class   = $this->input->post('class_name');
    //     $section = $this->input->post('section_name');

    //     if (!$class || !$section) {
    //         echo '<p class="text-muted">Invalid class or section</p>';
    //         return;
    //     }

    //     $students = $this->get_section_students_array($class, $section);

    //     echo $this->load->view(
    //         'partials/students_table', // ✅ correct filename
    //         ['students' => $students],
    //         true
    //     );
    // }

    // public function save_timetable()
    // {
    //     header('Content-Type: application/json');

    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;
    //     $class_name   = $this->input->post('class_name');
    //     $section_name = $this->input->post('section_name');
    //     $timetableRaw = $this->input->post('timetable');

    //     if (!$class_name || !$section_name || !$timetableRaw) {
    //         echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    //         return;
    //     }

    //     $timetable = json_decode($timetableRaw, true);

    //     if (json_last_error() !== JSON_ERROR_NONE || !is_array($timetable)) {
    //         echo json_encode(['status' => 'error', 'message' => 'Invalid timetable format']);
    //         return;
    //     }

    //     $basePath = "Schools/{$school_name}/{$session_year}/{$class_name}/{$section_name}/Time_table";

    //     // 🔥 Clear old data
    //     $this->firebase->delete($basePath);

    //     foreach ($timetable as $day => $slots) {

    //         // 🔐 SANITIZE DAY KEY (🔥 THIS WAS MISSING)
    //         if (!is_string($day) || trim($day) === '') {
    //             continue;
    //         }

    //         $safeDay = preg_replace('/[.#$\[\]\/]/', '_', trim($day));

    //         if ($safeDay === '') {
    //             continue;
    //         }

    //         if (!is_array($slots) || empty($slots)) {
    //             continue;
    //         }

    //         $safeDaySlots = [];

    //         foreach ($slots as $timeKey => $subject) {

    //             if (!is_string($timeKey) || trim($timeKey) === '') {
    //                 continue;
    //             }

    //             // 🔐 Sanitize time-slot key
    //             $safeKey = preg_replace('/[.#$\[\]\/]/', '_', trim($timeKey));

    //             if ($safeKey === '') {
    //                 continue;
    //             }

    //             // 🔐 Ensure value is scalar (Firebase-safe)
    //             if (!is_scalar($subject)) {
    //                 continue;
    //             }

    //             $safeDaySlots[$safeKey] = $subject;
    //         }

    //         if (!empty($safeDaySlots)) {
    //             $this->firebase->set("$basePath/$safeDay", $safeDaySlots);
    //         }
    //     }

    //     echo json_encode(['status' => 'success']);
    // }
