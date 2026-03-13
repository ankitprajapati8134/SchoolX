<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Student Information System (SIS) Controller
 *
 * Handles: SIS dashboard, student list, admission, profile management,
 *          batch promotion, transfer certificates, documents, history, ID cards.
 *
 * Firebase schema additions:
 *   Users/Parents/{school_id}/{userId}/History/{push_key}
 *       { action, description, changed_by, changed_at, metadata:{} }
 *   Users/Parents/{school_id}/{userId}/TC/
 *       { tc_no, issued_date, issued_by, reason, destination, status:active|cancelled }
 *   Schools/{school_name}/SIS/TC_Counter           → integer
 *   Schools/{school_name}/SIS/Promotions/{batch_id}/
 *       { session_from, session_to, promoted_at, promoted_by,
 *         from_class, to_class, students:[{userId, name}] }
 */
class Sis extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ══════════════════════════════════════════════════════════════════════
       DASHBOARD
    ══════════════════════════════════════════════════════════════════════ */

    public function index()
    {
        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        // Read lightweight index instead of full Users/Parents tree (OPT 1)
        $index = $this->firebase->get("Schools/{$school_name}/SIS/Students_Index") ?? [];
        if (!is_array($index)) $index = [];

        // Auto-build index if empty (first visit or after data migration)
        if (empty($index)) {
            $index = $this->_build_index_from_parents($school_id, $school_name);
        }

        // Enrolled in current session (OPT 3: single bulk read)
        $enrolledIds = $this->_get_enrolled_ids();

        $totalStudents = count($index);
        $tcCount       = 0;
        $classCounts   = [];

        foreach ($index as $uid => $entry) {
            if (!is_array($entry)) continue;
            $status = $entry['status'] ?? 'Active';

            // TC count
            if ($status === 'TC') $tcCount++;

            // Class-wise enrolled count
            if (isset($enrolledIds[$uid])) {
                $cls = trim($entry['class'] ?? 'Unknown');
                $classCounts[$cls] = ($classCounts[$cls] ?? 0) + 1;
            }
        }
        ksort($classCounts);

        $enrolledCount = 0;
        foreach ($enrolledIds as $uid => $_) {
            if (isset($index[$uid])) $enrolledCount++;
        }

        // Recent promotions
        $promotions = $this->firebase->get("Schools/{$school_name}/SIS/Promotions") ?? [];
        if (!is_array($promotions)) $promotions = [];
        arsort($promotions);
        $recentPromotions = array_slice($promotions, 0, 5, true);

        $data['total_students']    = $totalStudents;
        $data['enrolled_count']    = $enrolledCount;
        $data['tc_count']          = $tcCount;
        $data['class_counts']      = $classCounts;
        $data['recent_promotions'] = $recentPromotions;
        $data['session_year']      = $session;

        $this->load->view('include/header');
        $this->load->view('sis/index', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════════════════
       STUDENT LIST
    ══════════════════════════════════════════════════════════════════════ */

    public function students()
    {
        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        // Build class/section structure for filter dropdowns
        $classMap = [];
        $sessionClassKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session}");
        if (is_array($sessionClassKeys)) {
            foreach ($sessionClassKeys as $classKey) {
                if (strpos($classKey, 'Class ') !== 0) continue;
                $sectionKeys = $this->firebase->shallow_get(
                    "Schools/{$school_name}/{$session}/{$classKey}"
                );
                $sections = [];
                if (is_array($sectionKeys)) {
                    foreach ($sectionKeys as $sk) {
                        if (strpos($sk, 'Section ') === 0) {
                            $sections[] = str_replace('Section ', '', $sk);
                        }
                    }
                }
                $ordinal = str_replace('Class ', '', $classKey);
                $classMap[$ordinal] = $sections;
            }
        }

        $data['class_map']    = $classMap;
        $data['session_year'] = $session;

        $this->load->view('include/header');
        $this->load->view('sis/students', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════════════════
       ADMISSION
    ══════════════════════════════════════════════════════════════════════ */

    public function admission()
    {
        $school_name = $this->school_name;
        $session     = $this->session_year;

        // Build class/section map for dropdowns (same pattern as students())
        $classMap = [];
        $sessionClassKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session}");
        if (is_array($sessionClassKeys)) {
            foreach ($sessionClassKeys as $classKey) {
                if (strpos($classKey, 'Class ') !== 0) continue;
                $sectionKeys = $this->firebase->shallow_get(
                    "Schools/{$school_name}/{$session}/{$classKey}"
                );
                $sections = [];
                if (is_array($sectionKeys)) {
                    foreach ($sectionKeys as $sk) {
                        if (strpos($sk, 'Section ') === 0) {
                            $sections[] = str_replace('Section ', '', $sk);
                        }
                    }
                }
                $ordinal = str_replace('Class ', '', $classKey);
                $classMap[$ordinal] = $sections;
            }
        }

        $data['class_map']    = $classMap;
        $data['session_year'] = $session;

        $this->load->view('include/header');
        $this->load->view('sis/admission', $data);
        $this->load->view('include/footer');
    }

    public function save_admission()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        // ── Basic fields ────────────────────────────────────────────────
        $name        = trim($this->input->post('name')           ?? '');
        $userId      = trim($this->input->post('user_id')       ?? '');
        $classOrd    = trim($this->input->post('class')         ?? '');   // "9th"
        $section     = trim($this->input->post('section')       ?? '');   // "A"
        $phone       = trim($this->input->post('phone_number')  ?? $this->input->post('phone') ?? '');
        $email       = trim($this->input->post('email')         ?? '');
        $rollNo      = trim($this->input->post('roll_no')       ?? '');

        // ── Dates — format dd-mm-YYYY to match Student.php ──────────
        $rawDob  = trim($this->input->post('dob') ?? '');
        $rawAdm  = trim($this->input->post('admission_date') ?? '');
        $dob     = $rawDob ? date('d-m-Y', strtotime($rawDob)) : '';
        $admDate = $rawAdm ? date('d-m-Y', strtotime($rawAdm)) : date('d-m-Y');

        // ── Personal ────────────────────────────────────────────────
        $gender      = trim($this->input->post('gender')        ?? '');
        $category    = trim($this->input->post('category')      ?? '');
        $bloodGroup  = trim($this->input->post('blood_group')   ?? '');
        $religion    = trim($this->input->post('religion')      ?? '');
        $nationality = trim($this->input->post('nationality')   ?? '');

        // ── Family ──────────────────────────────────────────────────
        $father       = trim($this->input->post('father_name')       ?? '');
        $fatherOcc    = trim($this->input->post('father_occupation') ?? '');
        $mother       = trim($this->input->post('mother_name')       ?? '');
        $motherOcc    = trim($this->input->post('mother_occupation') ?? '');
        $guardContact = trim($this->input->post('guard_contact')     ?? '');
        $guardRelation= trim($this->input->post('guard_relation')    ?? '');

        // ── Previous Education ──────────────────────────────────────
        $preClass  = trim($this->input->post('pre_class')  ?? '');
        $preSchool = trim($this->input->post('pre_school') ?? '');
        $preMarks  = trim($this->input->post('pre_marks')  ?? '');
        if ($preMarks !== '' && substr($preMarks, -1) !== '%') {
            $preMarks .= '%';
        }

        // ── Address (separate fields matching Student.php) ──────────
        $street     = trim($this->input->post('street')      ?? $this->input->post('address') ?? '');
        $city       = trim($this->input->post('city')        ?? '');
        $state      = trim($this->input->post('state')       ?? '');
        $postalCode = trim($this->input->post('postal_code') ?? '');

        if (empty($name) || empty($classOrd) || empty($section)) {
            return $this->json_error('Name, class, and section are required.');
        }

        // Sanitize path segments (exits with json_error on invalid input)
        $this->safe_path_segment($classOrd, 'class');
        $this->safe_path_segment($section, 'section');

        // Auto-generate userId with retry/verify pattern (avoids race condition)
        if (empty($userId)) {
            $generated = $this->_nextStudentId($school_id);
            if (!$generated) {
                return $this->json_error('Failed to generate student ID. Please try again.');
            }
            $userId = $generated;
        } else {
            $this->safe_path_segment($userId, 'User ID');
        }

        // Check for duplicate — ensures no existing profile is overwritten
        $existing = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (!empty($existing)) {
            return $this->json_error("Student ID {$userId} already exists.");
        }

        // ── Password — same generation method as Student.php ────────
        $password = $this->_generatePassword($name, $dob);

        // ── Photo & Document uploads ────────────────────────────────
        $classKeyForPath = "Class {$classOrd}";
        $combinedClassPath = "{$classKeyForPath}/Section {$section}";

        $profilePicUrl = '';
        $docData = [
            'Birth Certificate'    => ['url' => '', 'thumbnail' => ''],
            'Aadhar Card'          => ['url' => '', 'thumbnail' => ''],
            'Transfer Certificate' => ['url' => '', 'thumbnail' => ''],
            'Photo'                => ['url' => '', 'thumbnail' => ''],
        ];

        // Student photo (optional in SIS — can upload later via documents page)
        if (!empty($_FILES['student_photo']['tmp_name']) && is_uploaded_file($_FILES['student_photo']['tmp_name'])) {
            $photoResult = $this->_uploadStudentFile(
                $_FILES['student_photo'], $school_name, $combinedClassPath, $userId, 'profile', 'profile'
            );
            if ($photoResult) {
                $profilePicUrl = $photoResult['document'];
                $docData['Photo'] = ['url' => $photoResult['document'], 'thumbnail' => $photoResult['thumbnail']];
            }
        }

        // Documents (Birth Certificate, Aadhar Card, Transfer Certificate)
        $docInputs = [
            'birthCertificate'    => 'Birth Certificate',
            'aadharCard'          => 'Aadhar Card',
            'transferCertificate' => 'Transfer Certificate',
        ];
        foreach ($docInputs as $inputKey => $label) {
            if (!empty($_FILES[$inputKey]['tmp_name']) && is_uploaded_file($_FILES[$inputKey]['tmp_name'])) {
                $uploadResult = $this->_uploadStudentFile(
                    $_FILES[$inputKey], $school_name, $combinedClassPath, $userId, $label, 'document'
                );
                if ($uploadResult) {
                    $docData[$label] = ['url' => $uploadResult['document'], 'thumbnail' => $uploadResult['thumbnail']];
                }
            }
        }

        // ── Build student data — exact schema match with Student.php ─
        $studentData = [
            'Name'           => $name,
            'User Id'        => $userId,
            'DOB'            => $dob,
            'Admission Date' => $admDate,

            'Class'          => $classOrd,
            'Section'        => $section,

            'Phone Number'   => $phone,
            'Email'          => $email,
            'Password'       => $password,

            'Category'       => $category,
            'Gender'         => $gender,
            'Blood Group'    => $bloodGroup,
            'Religion'       => $religion,
            'Nationality'    => $nationality,

            'Father Name'        => $father,
            'Father Occupation'  => $fatherOcc,
            'Mother Name'        => $mother,
            'Mother Occupation'  => $motherOcc,
            'Guard Contact'      => $guardContact,
            'Guard Relation'     => $guardRelation,

            'Pre Class'      => $preClass,
            'Pre School'     => $preSchool,
            'Pre Marks'      => $preMarks,

            'Address' => [
                'Street'     => $street,
                'City'       => $city,
                'State'      => $state,
                'PostalCode' => $postalCode,
            ],

            'Profile Pic'    => $profilePicUrl,
            'Doc'            => $docData,

            'Roll No'        => $rollNo,
            'Status'         => 'Active',
        ];

        // Save profile
        $this->firebase->set("Users/Parents/{$school_id}/{$userId}", $studentData);

        // Enroll in session roster
        $classKey   = "Class {$classOrd}";
        $sectionKey = "Section {$section}";
        $rosterPath = "Schools/{$school_name}/{$session}/{$classKey}/{$sectionKey}/Students/List/{$userId}";
        $this->firebase->set($rosterPath, $name);

        // Phone → school mapping (matches Student.php)
        if (!empty($phone)) {
            $this->firebase->update("Exits", [$phone => $school_id]);
            $this->firebase->update("User_ids_pno", [$phone => $userId]);
        }

        // Update Students_Index (OPT 1)
        $this->_update_student_index($school_name, $userId, $name, $classOrd, $section, 'Active');

        // Log history
        $this->_log_history($school_id, $userId, 'ADMISSION',
            "Student admitted to Class {$classOrd} / Section {$section} ({$session})",
            ['class' => $classOrd, 'section' => $section, 'session' => $session]
        );

        return $this->json_success(['message' => 'Student admitted successfully.', 'user_id' => $userId]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       PROFILE
    ══════════════════════════════════════════════════════════════════════ */

    public function profile($userId = null)
    {
        if (empty($userId) || !$this->safe_path_segment($userId)) show_404();

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) show_404();

        $history = $this->firebase->get(
            "Users/Parents/{$school_id}/{$userId}/History"
        ) ?? [];
        if (!is_array($history)) $history = [];

        // Sort history newest first
        uasort($history, fn($a, $b) =>
            strcmp($b['changed_at'] ?? '', $a['changed_at'] ?? '')
        );

        $data['student']      = $student;
        $data['history']      = $history;
        $data['school_name']  = $school_name;
        $data['session_year'] = $session;

        $this->load->view('include/header');
        $this->load->view('sis/profile', $data);
        $this->load->view('include/footer');
    }

    public function update_profile()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id = $this->parent_db_key;
        $userId    = trim($this->input->post('user_id'));

        if (empty($userId)) return $this->json_error('User ID required.');
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        // Field names must exactly match those written by Student.php
        $allowed = [
            'Name', 'Father Name', 'Mother Name', 'Father Occupation', 'Mother Occupation',
            'Guard Contact', 'Guard Relation',
            'DOB', 'Gender', 'Blood Group', 'Category', 'Religion', 'Nationality',
            'Phone Number',   // existing field — NOT "Phone"
            'Email',
            'Roll No', 'Pre School', 'Pre Class', 'Pre Marks',
        ];

        $updates = [];
        foreach ($allowed as $field) {
            $val = $this->input->post($field);
            if ($val !== null) {
                $updates[$field] = trim($val);
            }
        }

        // Address is a nested object — posted as Address[Street], Address[City], etc.
        $addrPost = $this->input->post('Address');
        if (is_array($addrPost)) {
            $existing = $this->firebase->get("Users/Parents/{$school_id}/{$userId}/Address") ?? [];
            $existing = is_array($existing) ? $existing : [];
            $merged   = $existing;
            foreach (['Street', 'City', 'State', 'PostalCode'] as $sub) {
                if (isset($addrPost[$sub])) {
                    $merged[$sub] = trim($addrPost[$sub]);
                }
            }
            $updates['Address'] = $merged;
        }

        if (empty($updates)) {
            return $this->json_error('No valid fields to update.');
        }

        $this->firebase->update("Users/Parents/{$school_id}/{$userId}", $updates);

        // Sync Students_Index if name changed (OPT 1)
        if (isset($updates['Name'])) {
            $this->firebase->update(
                "Schools/{$this->school_name}/SIS/Students_Index/{$userId}",
                ['name' => $updates['Name']]
            );
        }

        $changed = implode(', ', array_keys($updates));
        $this->_log_history($school_id, $userId, 'PROFILE_UPDATE',
            "Profile updated: {$changed}", $updates
        );

        return $this->json_success(['message' => 'Profile updated successfully.']);
    }

    /* ══════════════════════════════════════════════════════════════════════
       STUDENT PROMOTION
    ══════════════════════════════════════════════════════════════════════ */

    public function promote()
    {
        $school_name = $this->school_name;
        $session     = $this->session_year;

        $classMap = [];
        $sessionClassKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session}");
        if (is_array($sessionClassKeys)) {
            foreach ($sessionClassKeys as $classKey) {
                if (strpos($classKey, 'Class ') !== 0) continue;
                $sectionKeys = $this->firebase->shallow_get(
                    "Schools/{$school_name}/{$session}/{$classKey}"
                );
                $sections = [];
                if (is_array($sectionKeys)) {
                    foreach ($sectionKeys as $sk) {
                        if (strpos($sk, 'Section ') === 0) {
                            $sections[] = str_replace('Section ', '', $sk);
                        }
                    }
                }
                $ordinal = str_replace('Class ', '', $classKey);
                $classMap[$ordinal] = $sections;
            }
        }

        $data['class_map']    = $classMap;
        $data['session_year'] = $session;

        $this->load->view('include/header');
        $this->load->view('sis/promote', $data);
        $this->load->view('include/footer');
    }

    public function promote_preview()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        $fromClass   = trim($this->input->post('from_class'));   // "9th"
        $fromSection = trim($this->input->post('from_section')); // "A" or "all"

        if (empty($fromClass)) return $this->json_error('Source class is required.');
        if (!$this->safe_path_segment($fromClass)) return $this->json_error('Invalid class value.');
        if ($fromSection && $fromSection !== 'all' && !$this->safe_path_segment($fromSection)) {
            return $this->json_error('Invalid section value.');
        }

        $students = $this->_get_students_in_class($fromClass, $fromSection, $session);

        return $this->json_success([
            'message'      => 'Preview ready.',
            'students'     => array_values($students),
            'count'        => count($students),
            'from_class'   => $fromClass,
            'from_section' => $fromSection,
        ]);
    }

    public function execute_promotion()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        $fromClass   = trim($this->input->post('from_class'));
        $fromSection = trim($this->input->post('from_section'));
        $toClass     = trim($this->input->post('to_class'));
        $toSection   = trim($this->input->post('to_section'));
        $toSession   = trim($this->input->post('to_session') ?? '') ?: $session;

        if (empty($fromClass) || empty($toClass) || empty($toSection)) {
            return $this->json_error('Source class, destination class, and section are required.');
        }
        if (!$this->safe_path_segment($fromClass)) return $this->json_error('Invalid source class.');
        if ($fromSection && $fromSection !== 'all' && !$this->safe_path_segment($fromSection)) {
            return $this->json_error('Invalid source section.');
        }
        if (!$this->safe_path_segment($toClass))   return $this->json_error('Invalid destination class.');
        if (!$this->safe_path_segment($toSection))  return $this->json_error('Invalid destination section.');

        // Fix 3: Validate toSession format (YYYY-YY) — reject arbitrary strings
        if (!preg_match('/^\d{4}-\d{2}$/', $toSession)) {
            $toSession = $session;
        }

        $students = $this->_get_students_in_class($fromClass, $fromSection, $session);
        if (empty($students)) {
            return $this->json_error('No students found in the selected class/section.');
        }

        $adminName  = $this->session->userdata('admin_name') ?? 'Admin';
        $promoted   = [];
        $now        = date('Y-m-d H:i:s');
        $batchId    = date('YmdHis');
        $oldClassKey   = "Class {$fromClass}";
        $newClassKey   = "Class {$toClass}";
        $newSectionKey = "Section {$toSection}";
        $historyDesc   = "Promoted from Class {$fromClass}/{$fromSection} to Class {$toClass}/{$toSection} ({$toSession})";
        $historyMeta   = [
            'from_class' => $fromClass, 'from_section' => $fromSection,
            'to_class' => $toClass, 'to_section' => $toSection, 'to_session' => $toSession,
        ];

        // OPT 2: Build single multi-path update instead of N × 5 individual operations
        $batchUpdates = [];
        $counter      = 0;

        foreach ($students as $userId => $studentInfo) {
            $name = $studentInfo['name'] ?? $userId;
            $counter++;

            // Update profile class/section
            $batchUpdates["Users/Parents/{$school_id}/{$userId}/Class"]   = $toClass;
            $batchUpdates["Users/Parents/{$school_id}/{$userId}/Section"] = $toSection;

            // Remove from old roster (null = delete)
            $actualSection = ($fromSection === 'all')
                ? ($studentInfo['section'] ?? '')
                : $fromSection;
            if (!empty($actualSection)) {
                $batchUpdates["Schools/{$school_name}/{$session}/{$oldClassKey}/Section {$actualSection}/Students/List/{$userId}"] = null;
            }

            // Add to new roster
            $batchUpdates["Schools/{$school_name}/{$toSession}/{$newClassKey}/{$newSectionKey}/Students/List/{$userId}"] = $name;

            // Update Students_Index (OPT 1)
            $batchUpdates["Schools/{$school_name}/SIS/Students_Index/{$userId}/class"]   = $toClass;
            $batchUpdates["Schools/{$school_name}/SIS/Students_Index/{$userId}/section"] = $toSection;

            // History entry (generate key client-side for batch inclusion)
            $histKey = $batchId . '_' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $batchUpdates["Users/Parents/{$school_id}/{$userId}/History/{$histKey}"] = [
                'action'      => 'PROMOTION',
                'description' => $historyDesc,
                'changed_by'  => $adminName,
                'changed_at'  => $now,
                'metadata'    => $historyMeta,
            ];

            $promoted[] = ['user_id' => $userId, 'name' => $name];
        }

        // Promotion batch record
        $batchUpdates["Schools/{$school_name}/SIS/Promotions/{$batchId}"] = [
            'session_from' => $session,
            'session_to'   => $toSession,
            'promoted_at'  => $now,
            'promoted_by'  => $adminName,
            'from_class'   => $fromClass,
            'from_section' => $fromSection,
            'to_class'     => $toClass,
            'to_section'   => $toSection,
            'students'     => $promoted,
            'count'        => count($promoted),
        ];

        // Single atomic write for entire promotion batch
        $this->firebase->update("", $batchUpdates);

        return $this->json_success([
            'message'  => count($promoted) . ' student(s) promoted successfully.',
            'promoted' => $promoted,
            'skipped'  => [],
            'batch_id' => $batchId,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       TRANSFER CERTIFICATES
    ══════════════════════════════════════════════════════════════════════ */

    public function tc_list()
    {
        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;

        // Primary: read from TC index (O(1), populated by issue_tc/cancel_tc)
        $tcIndex = $this->firebase->get("Schools/{$school_name}/SIS/TC_Index") ?? [];
        if (!is_array($tcIndex)) $tcIndex = [];

        $tcRecords = [];
        if (!empty($tcIndex)) {
            // Fast path: build records directly from index
            foreach ($tcIndex as $tcKey => $tc) {
                if (!is_array($tc)) continue;
                $tcRecords[] = [
                    'user_id'    => $tc['user_id']     ?? '',
                    'name'       => $tc['student_name'] ?? $tc['name'] ?? '',
                    'class'      => $tc['class']        ?? '',
                    'section'    => $tc['section']      ?? '',
                    'tc_key'     => $tc['tc_key']       ?? $tcKey,
                    'tc_no'      => $tc['tc_no']        ?? '',
                    'issued_date'=> $tc['issued_date']  ?? '',
                    'issued_by'  => $tc['issued_by']    ?? '',
                    'destination'=> $tc['destination']  ?? '',
                    'status'     => $tc['status']       ?? '',
                ];
            }
        } else {
            // Fallback: full student scan (backward-compat with data issued before index existed)
            $allStudents = $this->firebase->get("Users/Parents/{$school_id}") ?? [];
            if (!is_array($allStudents)) $allStudents = [];
            foreach (['Count', 'TC Students', ''] as $k) unset($allStudents[$k]);

            foreach ($allStudents as $uid => $student) {
                if (!is_array($student)) continue;
                $tcs = $student['TC'] ?? [];
                if (!is_array($tcs)) continue;
                foreach ($tcs as $tcKey => $tc) {
                    if (!is_array($tc)) continue;
                    $tcRecords[] = [
                        'user_id'    => $uid,
                        'name'       => $student['Name']    ?? $uid,
                        'class'      => $student['Class']   ?? '',
                        'section'    => $student['Section'] ?? '',
                        'tc_key'     => $tcKey,
                        'tc_no'      => $tc['tc_no']        ?? '',
                        'issued_date'=> $tc['issued_date']  ?? '',
                        'issued_by'  => $tc['issued_by']    ?? '',
                        'destination'=> $tc['destination']  ?? '',
                        'status'     => $tc['status']       ?? '',
                    ];
                }
            }
        }

        // Sort by issued_date desc
        usort($tcRecords, fn($a, $b) =>
            strcmp($b['issued_date'] ?? '', $a['issued_date'] ?? '')
        );

        // Fix 5: Server-side pagination (50 per page)
        $perPage    = 50;
        $page       = max(1, (int)($this->input->get('page') ?? 1));
        $total      = count($tcRecords);
        $totalPages = (int)ceil($total / $perPage);
        $offset     = ($page - 1) * $perPage;
        $pagedTcs   = array_slice($tcRecords, $offset, $perPage);

        $data['tc_records']   = $pagedTcs;
        $data['tc_total']     = $total;
        $data['tc_page']      = $page;
        $data['tc_per_page']  = $perPage;
        $data['tc_pages']     = $totalPages;
        $data['session_year'] = $session;

        $this->load->view('include/header');
        $this->load->view('sis/tc_list', $data);
        $this->load->view('include/footer');
    }

    public function issue_tc()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;

        $userId      = trim($this->input->post('user_id')      ?? '');
        $reason      = trim($this->input->post('reason')      ?? '') ?: 'Transfer';
        $destination = trim($this->input->post('destination') ?? '');

        if (empty($userId)) return $this->json_error('Student ID required.');
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) return $this->json_error('Student not found.');

        // Check not already TC issued
        $existing = $student['TC'] ?? [];
        if (is_array($existing)) {
            foreach ($existing as $tc) {
                if (is_array($tc) && ($tc['status'] ?? '') === 'active') {
                    return $this->json_error('An active TC is already issued for this student.');
                }
            }
        }

        $tcNo      = $this->_get_tc_number($school_name);
        $adminName = $this->session->userdata('admin_name') ?? 'Admin';
        $tcData    = [
            'tc_no'       => $tcNo,
            'issued_date' => date('Y-m-d'),
            'issued_by'   => $adminName,
            'reason'      => $reason,
            'destination' => $destination,
            'status'      => 'active',
            'student_name'=> $student['Name'] ?? $userId,
            'class'       => $student['Class'] ?? '',
            'section'     => $student['Section'] ?? '',
        ];

        // Save TC under student profile
        $tcKey = $this->firebase->push("Users/Parents/{$school_id}/{$userId}/TC", $tcData);

        // Mark student status as TC
        $this->firebase->update("Users/Parents/{$school_id}/{$userId}", ['Status' => 'TC']);

        // Sync Students_Index (OPT 1)
        $this->firebase->update(
            "Schools/{$school_name}/SIS/Students_Index/{$userId}",
            ['status' => 'TC']
        );

        // Write to TC index for O(1) listing (avoids full student scan in tc_list)
        $this->firebase->set("Schools/{$school_name}/SIS/TC_Index/{$tcKey}", array_merge($tcData, [
            'user_id' => $userId,
            'tc_key'  => $tcKey,
        ]));

        // Log history
        $this->_log_history($school_id, $userId, 'TC_ISSUED',
            "Transfer Certificate issued (TC#{$tcNo}) — Reason: {$reason}",
            ['tc_no' => $tcNo, 'destination' => $destination]
        );

        return $this->json_success([
            'message' => "Transfer Certificate {$tcNo} issued.",
            'tc_no'   => $tcNo,
            'tc_key'  => $tcKey,
            'user_id' => $userId,
        ]);
    }

    public function print_tc($userId = null, $tcKey = null)
    {
        if (empty($userId)) show_404();
        if (!preg_match('/^[A-Za-z0-9_\-]+$/', $userId)) show_404();
        if (!empty($tcKey) && !preg_match('/^[A-Za-z0-9_\-]+$/', $tcKey)) show_404();

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;

        // Fetch student profile — retry once on failure (Firebase connectivity is intermittent)
        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if ($student === null || $student === false) {
            usleep(300000); // 300ms
            $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        }
        if (empty($student)) {
            log_message('error', "print_tc: student not found — school_id={$school_id} userId={$userId}");

            // If Firebase is intermittently down, try reading TC data from the TC_Index as fallback
            if ($tcKey) {
                $indexTc = $this->firebase->get("Schools/{$school_name}/SIS/TC_Index/{$tcKey}");
                if (!empty($indexTc) && is_array($indexTc)) {
                    // Build minimal student data from the TC_Index entry
                    $student = [
                        'User Id' => $indexTc['user_id'] ?? $userId,
                        'Name'    => $indexTc['student_name'] ?? $indexTc['name'] ?? $userId,
                        'Class'   => $indexTc['class'] ?? '',
                        'Section' => $indexTc['section'] ?? '',
                    ];
                    $tc = $indexTc;
                }
            }
            if (empty($student)) show_404();
        }

        if (!isset($tc)) {
            $tc = null;
            if ($tcKey) {
                $tc = $this->firebase->get("Users/Parents/{$school_id}/{$userId}/TC/{$tcKey}");
            }
            // Fallback: get the active TC
            if (empty($tc) && is_array($student['TC'] ?? null)) {
                foreach ($student['TC'] as $k => $t) {
                    if (is_array($t) && ($t['status'] ?? '') === 'active') {
                        $tc = $t;
                        break;
                    }
                }
            }
        }

        if (empty($tc)) {
            log_message('error', "print_tc: TC not found — school_id={$school_id} userId={$userId} tcKey={$tcKey}");
            show_404();
        }

        // School profile for header
        $schoolProfile = $this->firebase->get("System/Schools/{$school_name}/profile") ?? [];

        $data['student']       = $student;
        $data['tc']            = $tc;
        $data['school_profile']= $schoolProfile;
        $data['school_name']   = $school_name;

        // Standalone print view (no header/footer chrome)
        $this->load->view('sis/tc_print', $data);
    }

    public function cancel_tc()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $userId      = trim($this->input->post('user_id'));
        $tcKey       = trim($this->input->post('tc_key'));

        if (empty($userId) || empty($tcKey)) {
            return $this->json_error('User ID and TC key required.');
        }
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');
        if (!$this->safe_path_segment($tcKey))  return $this->json_error('Invalid TC key.');

        $cancelled = ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')];
        $this->firebase->update("Users/Parents/{$school_id}/{$userId}/TC/{$tcKey}", $cancelled);

        // Mirror cancellation to TC index
        $this->firebase->update("Schools/{$school_name}/SIS/TC_Index/{$tcKey}", $cancelled);

        $this->firebase->update("Users/Parents/{$school_id}/{$userId}", ['Status' => 'Active']);

        // Sync Students_Index (OPT 1)
        $this->firebase->update(
            "Schools/{$school_name}/SIS/Students_Index/{$userId}",
            ['status' => 'Active']
        );

        $this->_log_history($school_id, $userId, 'TC_CANCELLED',
            'Transfer Certificate cancelled — student re-activated.'
        );

        return $this->json_success(['message' => 'TC cancelled and student re-activated.']);
    }

    /* ══════════════════════════════════════════════════════════════════════
       STUDENT WITHDRAWAL & STATUS
    ══════════════════════════════════════════════════════════════════════ */

    /**
     * Soft-withdraw a student: mark Inactive, remove from session roster, log.
     * Does NOT delete any data — student profile and documents are preserved.
     */
    public function withdraw_student()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $session     = $this->session_year;
        $userId      = trim($this->input->post('user_id'));
        $reason      = trim($this->input->post('reason') ?? '') ?: 'Withdrawn';

        if (empty($userId)) return $this->json_error('User ID required.');
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student) || !is_array($student)) {
            return $this->json_error('Student not found.');
        }

        if (($student['Status'] ?? '') === 'Inactive') {
            return $this->json_error('Student is already inactive.');
        }

        // Mark as Inactive
        $this->firebase->update("Users/Parents/{$school_id}/{$userId}", ['Status' => 'Inactive']);

        // Sync Students_Index (OPT 1)
        $this->firebase->update(
            "Schools/{$school_name}/SIS/Students_Index/{$userId}",
            ['status' => 'Inactive']
        );

        // Remove from current session roster
        $classOrd   = $student['Class']   ?? '';
        $sectionLtr = $student['Section'] ?? '';
        if (!empty($classOrd) && !empty($sectionLtr)) {
            $this->firebase->delete(
                "Schools/{$school_name}/{$session}/Class {$classOrd}/Section {$sectionLtr}/Students/List",
                $userId
            );
        }

        $this->_log_history($school_id, $userId, 'WITHDRAWAL',
            "Student withdrawn: {$reason}",
            ['reason' => $reason, 'session' => $session, 'class' => $classOrd, 'section' => $sectionLtr]
        );

        return $this->json_success(['message' => 'Student withdrawn and marked Inactive.']);
    }

    /**
     * Toggle or explicitly set a student's Status field (Active / Inactive).
     * TC status is managed through issue_tc / cancel_tc, not here.
     */
    public function change_status()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id = $this->parent_db_key;
        $userId    = trim($this->input->post('user_id'));
        $newStatus = trim($this->input->post('status'));

        if (empty($userId)) return $this->json_error('User ID required.');
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');
        if (!in_array($newStatus, ['Active', 'Inactive'], true)) {
            return $this->json_error('Status must be Active or Inactive.');
        }

        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) return $this->json_error('Student not found.');

        $this->firebase->update("Users/Parents/{$school_id}/{$userId}", ['Status' => $newStatus]);

        // Sync Students_Index (OPT 1)
        $this->firebase->update(
            "Schools/{$this->school_name}/SIS/Students_Index/{$userId}",
            ['status' => $newStatus]
        );

        $this->_log_history($school_id, $userId, 'STATUS_CHANGE',
            "Status changed to {$newStatus}", ['status' => $newStatus]
        );

        return $this->json_success(['message' => "Status updated to {$newStatus}."]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       DOCUMENTS
    ══════════════════════════════════════════════════════════════════════ */

    public function documents($userId = null)
    {
        if (empty($userId) || !$this->safe_path_segment($userId)) show_404();

        $school_id = $this->parent_db_key;
        $student   = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) show_404();

        $data['student'] = $student;

        $this->load->view('include/header');
        $this->load->view('sis/documents', $data);
        $this->load->view('include/footer');
    }

    public function upload_document()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id = $this->parent_db_key;
        $userId    = trim($this->input->post('user_id'));
        $docLabel  = trim($this->input->post('doc_label'));

        // Sanitize label — Firebase keys cannot contain . $ # [ ] /
        $docLabel = trim(preg_replace('/[.\$#\[\]\/]/', '_', $docLabel));

        if (empty($userId) || empty($docLabel)) {
            return $this->json_error('User ID and document label are required.');
        }
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        if (empty($_FILES['document']['name'])) {
            return $this->json_error('No file uploaded.');
        }

        // ── Fix 1: Extension whitelist ────────────────────────────────────
        $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $ext  = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        $mime = $_FILES['document']['type'];

        if (!in_array($ext, $allowedExt, true)) {
            return $this->json_error('Invalid file type. Allowed: JPG, PNG, GIF, WebP, PDF.');
        }
        if (!in_array($mime, $allowedMime, true)) {
            return $this->json_error('Invalid MIME type for uploaded file.');
        }
        // ── Fix 2: File size limit (5 MB) ─────────────────────────────────
        if ($_FILES['document']['size'] > 5 * 1024 * 1024) {
            return $this->json_error('File too large. Maximum allowed size is 5 MB.');
        }
        if ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            return $this->json_error('File upload error (code ' . $_FILES['document']['error'] . ').');
        }

        $storagePath = "Students/{$school_id}/{$userId}/docs/{$docLabel}";
        try {
            $url      = $this->firebase->uploadFile($storagePath, $_FILES['document']['tmp_name'],
                            $mime);
            $thumbUrl = '';
            // Generate thumbnail for images
            if (strpos($_FILES['document']['type'], 'image/') === 0) {
                $thumbUrl = $url; // use same URL (or generate thumb separately)
            }

            $this->firebase->update(
                "Users/Parents/{$school_id}/{$userId}/Doc",
                [$docLabel => ['url' => $url, 'thumbnail' => $thumbUrl,
                               'uploaded_at' => date('Y-m-d H:i:s')]]
            );

            $this->_log_history($school_id, $userId, 'DOCUMENT_UPLOAD',
                "Document uploaded: {$docLabel}", ['doc_label' => $docLabel]
            );

            return $this->json_success(['message' => 'Document uploaded.', 'url' => $url]);
        } catch (\Exception $e) {
            return $this->json_error('Upload failed: ' . $e->getMessage());
        }
    }

    public function delete_document()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id = $this->parent_db_key;
        $userId    = trim($this->input->post('user_id'));
        $docLabel  = trim($this->input->post('doc_label'));

        // Sanitize label — same as upload_document()
        $docLabel = trim(preg_replace('/[.\$#\[\]\/]/', '_', $docLabel));

        if (empty($userId) || empty($docLabel)) {
            return $this->json_error('User ID and doc label required.');
        }
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        $this->firebase->delete("Users/Parents/{$school_id}/{$userId}/Doc", $docLabel);

        $this->_log_history($school_id, $userId, 'DOCUMENT_DELETE',
            "Document deleted: {$docLabel}", ['doc_label' => $docLabel]
        );

        return $this->json_success(['message' => 'Document deleted.']);
    }

    /* ══════════════════════════════════════════════════════════════════════
       HISTORY
    ══════════════════════════════════════════════════════════════════════ */

    public function history($userId = null)
    {
        if (empty($userId) || !$this->safe_path_segment($userId)) show_404();

        $school_id = $this->parent_db_key;
        $student   = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) show_404();

        $history = $this->firebase->get("Users/Parents/{$school_id}/{$userId}/History") ?? [];
        if (!is_array($history)) $history = [];

        uasort($history, fn($a, $b) =>
            strcmp($b['changed_at'] ?? '', $a['changed_at'] ?? '')
        );

        $data['student'] = $student;
        $data['history'] = $history;

        $this->load->view('include/header');
        $this->load->view('sis/history', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════════════════
       ID CARD
    ══════════════════════════════════════════════════════════════════════ */

    public function id_card()
    {
        // The existing student/id_card is the full-featured implementation (774 lines).
        // Redirect there to avoid duplication and keep a single source of truth.
        redirect('student/id_card');
    }

    /**
     * One-time utility: rebuild the Students_Index from the full Users/Parents tree.
     * Call via GET: sis/rebuild_index — idempotent, safe to re-run.
     */
    public function rebuild_index()
    {
        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;

        $allStudents = $this->firebase->get("Users/Parents/{$school_id}") ?? [];
        if (!is_array($allStudents)) $allStudents = [];
        foreach (['Count', 'TC Students', ''] as $k) unset($allStudents[$k]);

        $index = [];
        foreach ($allStudents as $uid => $s) {
            if (!is_array($s) || empty($s['User Id'])) continue;
            $index[$uid] = [
                'name'    => $s['Name']    ?? '',
                'class'   => $s['Class']   ?? '',
                'section' => $s['Section'] ?? '',
                'status'  => $s['Status']  ?? 'Active',
            ];
        }

        $this->firebase->set("Schools/{$school_name}/SIS/Students_Index", $index);

        return $this->json_success([
            'message' => 'Students_Index rebuilt.',
            'count'   => count($index),
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       AJAX HELPERS
    ══════════════════════════════════════════════════════════════════════ */

    public function search_student()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $query       = strtolower(trim($this->input->post('query')));
        $classFilter = trim($this->input->post('class') ?? '');
        $secFilter   = trim($this->input->post('section') ?? '');
        $page        = max(1, (int)($this->input->post('page') ?? 1));
        $perPage     = 30;

        // OPT 1: Read lightweight index instead of full Users/Parents tree
        $index = $this->firebase->get("Schools/{$school_name}/SIS/Students_Index") ?? [];
        if (!is_array($index)) $index = [];

        // Auto-build index if empty
        if (empty($index)) {
            $index = $this->_build_index_from_parents($school_id, $school_name);
        }

        $enrolledIds = $this->_get_enrolled_ids();

        // Filter using index fields (name, class, section, status) + userId
        $filtered = [];
        foreach ($index as $uid => $entry) {
            if (!is_array($entry)) continue;
            if (!isset($enrolledIds[$uid])) continue;
            if ($classFilter && ($entry['class'] ?? '') !== $classFilter) continue;
            if ($secFilter   && ($entry['section'] ?? '') !== $secFilter) continue;
            if ($query) {
                $haystack = strtolower(($entry['name'] ?? '') . ' ' . $uid);
                if (strpos($haystack, $query) === false) continue;
            }
            $filtered[$uid] = $entry;
        }

        // Sort by class then name
        uasort($filtered, function ($a, $b) {
            $c = strcmp($a['class'] ?? '', $b['class'] ?? '');
            return $c ?: strcmp($a['name'] ?? '', $b['name'] ?? '');
        });

        $total     = count($filtered);
        $offset    = ($page - 1) * $perPage;
        $pagedKeys = array_slice(array_keys($filtered), $offset, $perPage);

        // Fetch full profiles only for the current page (max 30)
        $results = [];
        foreach ($pagedKeys as $uid) {
            $entry   = $filtered[$uid];
            $profile = $this->firebase->get("Users/Parents/{$school_id}/{$uid}");

            $photo = '';
            if (is_array($profile)) {
                if (!empty($profile['Profile Pic']) && is_string($profile['Profile Pic'])) {
                    $photo = $profile['Profile Pic'];
                } elseif (!empty($profile['Doc']['Photo'])) {
                    $p = $profile['Doc']['Photo'];
                    $photo = is_array($p) ? ($p['url'] ?? '') : (string)$p;
                }
            }

            $results[] = [
                'user_id'     => $uid,
                'name'        => $entry['name'] ?? '',
                'father_name' => is_array($profile) ? ($profile['Father Name'] ?? '') : '',
                'class'       => $entry['class'] ?? '',
                'section'     => $entry['section'] ?? '',
                'phone'       => is_array($profile) ? ($profile['Phone Number'] ?? '') : '',
                'status'      => $entry['status'] ?? 'Active',
                'photo'       => $photo,
            ];
        }

        return $this->json_success([
            'students' => $results,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function get_student()
    {
        if ($this->input->method() !== 'post') {
            return $this->json_error('POST required');
        }

        $school_id = $this->parent_db_key;
        $userId    = trim($this->input->post('user_id'));

        if (empty($userId)) return $this->json_error('User ID required.');
        if (!$this->safe_path_segment($userId)) return $this->json_error('Invalid User ID.');

        $student = $this->firebase->get("Users/Parents/{$school_id}/{$userId}");
        if (empty($student)) return $this->json_error('Student not found.');

        return $this->json_success(['student' => $student]);
    }

    public function get_classes()
    {
        $school_name = $this->school_name;
        $session     = $this->session_year;

        $classKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session}");
        $classes   = [];
        if (is_array($classKeys)) {
            foreach ($classKeys as $k) {
                if (strpos($k, 'Class ') === 0) {
                    $classes[] = str_replace('Class ', '', $k);
                }
            }
        }

        return $this->json_success(['classes' => $classes]);
    }

    public function get_sections()
    {
        $school_name = $this->school_name;
        $session     = $this->session_year;
        $classOrd    = trim($this->input->get('class') ?? $this->input->post('class') ?? '');

        if (empty($classOrd)) return $this->json_error('Class required.');
        if (!$this->safe_path_segment($classOrd)) return $this->json_error('Invalid class value.');

        $classKey    = "Class {$classOrd}";
        $sectionKeys = $this->firebase->shallow_get(
            "Schools/{$school_name}/{$session}/{$classKey}"
        );

        $sections = [];
        if (is_array($sectionKeys)) {
            foreach ($sectionKeys as $sk) {
                if (strpos($sk, 'Section ') === 0) {
                    $sections[] = str_replace('Section ', '', $sk);
                }
            }
        }

        return $this->json_success(['sections' => $sections]);
    }

    /* ══════════════════════════════════════════════════════════════════════
       PRIVATE HELPERS
    ══════════════════════════════════════════════════════════════════════ */

    /**
     * Build Students_Index from Users/Parents data and persist it.
     * Called automatically when the index is empty (first visit or migration).
     */
    private function _build_index_from_parents(string $school_id, string $school_name): array
    {
        $allStudents = $this->firebase->get("Users/Parents/{$school_id}") ?? [];
        if (!is_array($allStudents)) return [];
        foreach (['Count', 'TC Students', ''] as $k) unset($allStudents[$k]);

        $index = [];
        foreach ($allStudents as $uid => $s) {
            if (!is_array($s) || empty($s['User Id'])) continue;
            $index[$uid] = [
                'name'    => $s['Name']    ?? '',
                'class'   => $s['Class']   ?? '',
                'section' => $s['Section'] ?? '',
                'status'  => $s['Status']  ?? 'Active',
            ];
        }

        // Persist so subsequent requests use the fast index path
        if (!empty($index)) {
            $this->firebase->set("Schools/{$school_name}/SIS/Students_Index", $index);
        }

        return $index;
    }

    /**
     * Generate student password — exact copy of Student.php::generatePassword().
     * Format: Ucfirst(first 3 letters of name) + first 4 DOB digits + @
     */
    private function _generatePassword(string $name, string $dob): string
    {
        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name);
        $prefix    = strtolower(substr($cleanName, 0, 3));
        $dobPart   = preg_replace('/[^0-9]/', '', $dob);
        $suffix    = substr($dobPart, 0, 4);
        return ucfirst($prefix) . $suffix . '@';
    }

    /**
     * Upload a student file to Firebase Storage — mirrors Student.php::uploadStudentFile().
     * Returns ['document' => url, 'thumbnail' => url] or false on failure.
     */
    private function _uploadStudentFile(
        array  $file,
        string $schoolName,
        string $combinedClassPath,
        string $studentId,
        string $folderLabel,
        string $type = 'document'
    ) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed   = ($type === 'profile')
            ? ['jpg', 'jpeg', 'png', 'webp']
            : ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($ext, $allowed, true)) return false;
        if ($file['size'] > 5 * 1024 * 1024) return false;

        $timestamp = time();
        $random    = substr(md5(uniqid()), 0, 6);
        $safeLabel = str_replace([' ', '.', '#', '$', '[', ']'], '_', $folderLabel);
        $fileName  = "{$safeLabel}_{$timestamp}_{$random}.{$ext}";

        $basePath = "{$schoolName}/Students/{$combinedClassPath}/{$studentId}/";

        if ($type === 'profile') {
            $documentPath = $basePath . "Profile_pic/{$fileName}";
        } else {
            $documentPath = $basePath . "Documents/{$fileName}";
        }

        if ($this->firebase->uploadFile($file['tmp_name'], $documentPath) !== true) {
            return false;
        }

        $documentUrl  = $this->firebase->getDownloadUrl($documentPath);
        $thumbnailUrl = '';

        // Generate thumbnail for images
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $thumbFolder = ($type === 'profile') ? 'Profile_pic' : 'Documents';
            $thumbPath   = $basePath . "{$thumbFolder}/thumbnail/{$fileName}";
            if ($this->firebase->uploadFile($file['tmp_name'], $thumbPath) === true) {
                $thumbnailUrl = $this->firebase->getDownloadUrl($thumbPath);
            }
        }

        return [
            'document'  => $documentUrl,
            'thumbnail' => $thumbnailUrl,
        ];
    }

    /**
     * Write/update the lightweight Students_Index entry for a student.
     * Path: Schools/{sn}/SIS/Students_Index/{userId}
     */
    private function _update_student_index(
        string $schoolName,
        string $userId,
        string $name,
        string $class,
        string $section,
        string $status
    ): void {
        $this->firebase->set("Schools/{$schoolName}/SIS/Students_Index/{$userId}", [
            'name'    => $name,
            'class'   => $class,
            'section' => $section,
            'status'  => $status,
        ]);
    }

    /**
     * Append an entry to the student's History log.
     */
    private function _log_history(
        string $schoolId,
        string $userId,
        string $action,
        string $description,
        array  $metadata = []
    ): void {
        $adminName = $this->session->userdata('admin_name') ?? 'System';
        $entry = [
            'action'      => $action,
            'description' => $description,
            'changed_by'  => $adminName,
            'changed_at'  => date('Y-m-d H:i:s'),
            'metadata'    => $metadata,
        ];
        $this->firebase->push("Users/Parents/{$schoolId}/{$userId}/History", $entry);
    }

    /**
     * Generate next student ID with retry/verify pattern to avoid race conditions.
     * Format: STU0001, STU0002, etc.
     */
    private function _nextStudentId(string $schoolId): ?string
    {
        $path = "Users/Parents/{$schoolId}/Count";
        $maxRetries = 5;

        for ($i = 0; $i < $maxRetries; $i++) {
            $current = $this->firebase->get($path);
            $current = !empty($current) ? (int)$current : 0;
            $next    = $current + 1;
            $userId  = 'STU' . str_pad($next, 4, '0', STR_PAD_LEFT);

            // Write the incremented counter
            $this->firebase->set($path, $next);

            // Verify the counter wasn't overwritten by a concurrent request
            $verify = $this->firebase->get($path);
            if ((int)$verify !== $next) {
                usleep(50000 * ($i + 1));
                continue;
            }

            // Verify no existing student profile at this ID
            $existing = $this->firebase->get("Users/Parents/{$schoolId}/{$userId}");
            if (!empty($existing)) {
                // ID already taken — bump counter and retry
                usleep(50000 * ($i + 1));
                continue;
            }

            return $userId;
        }

        // Fallback: timestamp-based ID to avoid blocking admission
        $fallback = (int)(microtime(true) * 1000) % 999999;
        $userId   = 'STU' . str_pad($fallback, 6, '0', STR_PAD_LEFT);
        $this->firebase->set($path, $fallback);
        return $userId;
    }

    /**
     * Get the next TC serial number and increment the counter.
     * Format: TC-{school_code}-{YYYY}-{0001}
     */
    private function _get_tc_number(string $schoolName): string
    {
        $counterPath = "Schools/{$schoolName}/SIS/TC_Counter";
        $current     = (int)($this->firebase->get($counterPath) ?? 0);
        $next        = $current + 1;
        $this->firebase->set($counterPath, $next);
        $year   = date('Y');
        $code   = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($schoolName, 0, 6)));
        return "TC-{$code}-{$year}-" . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Build map of enrolled student IDs for the current session.
     * Returns [ userId => true ]
     *
     * OPT 3: Single bulk read of the session root instead of 1 + C + S per-section reads.
     */
    private function _get_enrolled_ids(): array
    {
        $school_name = $this->school_name;
        $session     = $this->session_year;
        $enrolledIds = [];

        $sessionRoot = $this->firebase->get("Schools/{$school_name}/{$session}");
        if (!is_array($sessionRoot)) return $enrolledIds;

        foreach ($sessionRoot as $classKey => $classData) {
            if (strpos($classKey, 'Class ') !== 0 || !is_array($classData)) continue;
            foreach ($classData as $sectionKey => $sectionData) {
                if (strpos($sectionKey, 'Section ') !== 0 || !is_array($sectionData)) continue;
                $list = $sectionData['Students']['List'] ?? [];
                if (is_array($list)) {
                    foreach (array_keys($list) as $sid) {
                        $enrolledIds[$sid] = true;
                    }
                }
            }
        }

        return $enrolledIds;
    }

    /**
     * Get students enrolled in a specific class (and optionally section).
     * Returns [ userId => ['name'=>..., 'class'=>..., 'section'=>...] ]
     */
    private function _get_students_in_class(
        string $classOrd,
        string $section,
        string $session
    ): array {
        $school_id   = $this->parent_db_key;
        $school_name = $this->school_name;
        $classKey    = "Class {$classOrd}";
        $students    = [];

        if ($section && $section !== 'all') {
            $sectionKey = "Section {$section}";
            $list = $this->firebase->get(
                "Schools/{$school_name}/{$session}/{$classKey}/{$sectionKey}/Students/List"
            );
            if (is_array($list)) {
                foreach ($list as $uid => $name) {
                    $students[$uid] = [
                        'user_id' => $uid,
                        'name'    => $name,
                        'class'   => $classOrd,
                        'section' => $section,
                    ];
                }
            }
        } else {
            // All sections in this class
            $sectionKeys = $this->firebase->shallow_get(
                "Schools/{$school_name}/{$session}/{$classKey}"
            );
            if (is_array($sectionKeys)) {
                foreach ($sectionKeys as $sk) {
                    if (strpos($sk, 'Section ') !== 0) continue;
                    $sec  = str_replace('Section ', '', $sk);
                    $list = $this->firebase->get(
                        "Schools/{$school_name}/{$session}/{$classKey}/{$sk}/Students/List"
                    );
                    if (is_array($list)) {
                        foreach ($list as $uid => $name) {
                            $students[$uid] = [
                                'user_id' => $uid,
                                'name'    => $name,
                                'class'   => $classOrd,
                                'section' => $sec,
                            ];
                        }
                    }
                }
            }
        }

        return $students;
    }
}
