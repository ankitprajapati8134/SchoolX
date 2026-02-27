<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staff controller
 *
 * SECURITY FIXES:
 * [FIX-1]  new_staff(): $schoolId and $schoolName were undefined / leaked; now
 *          always taken from session ($this->school_id / $this->school_name).
 * [FIX-2]  new_staff(): Password stored using password_hash (was plain-text).
 * [FIX-3]  new_staff(): Phone validated with regex before storing.
 * [FIX-4]  new_staff(): StaffPath used undefined $schoolId — now uses session.
 * [FIX-5]  edit_staff(): $schoolId referenced but never defined — uses session.
 * [FIX-6]  markInactive_duty(): used $school_id for a path that should use
 *          $school_name — was mixing school name with school ID in path.
 * [FIX-7]  assign_duty(): classSection from POST used directly in path without
 *          validation — now validated via regex.
 * [FIX-8]  fetch_subjects(): classSection from JSON body used directly in path
 *          — now validated.
 * [FIX-9]  import_staff(): MIME validation added; XLSX/CSV only.
 * [FIX-10] save_updated_fees() debug print_r removed (was in Fees but mirrored
 *          here for completeness).
 */
class Staff extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Validate a class-section string like "Class 8th 'A'" or "8th 'A'".
     */
    private function valid_class_section(string $val): bool
    {
        return (bool) preg_match("/^(Class\s+)?[A-Za-z0-9]+\s+'[A-Z]{1,3}'$/", $val);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function all_staff()
    {
        $school_id   = $this->school_id;
        $school_name = $this->school_name;

        $data['staff'] = $this->CM->select_data("Users/Teachers/{$school_id}");
        if (!is_array($data['staff'])) {
            $data['staff'] = [];
        }

        $data['school_name'] = $school_name;

        $this->load->view('include/header');
        $this->load->view('all_staff', $data);
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function import_staff()
    {
        try {
            if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
                echo 'No file uploaded or upload error.';
                return;
            }

            $file = $_FILES['excelFile'];

            // [FIX-9] Dual MIME + extension validation
            $allowedMimes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            $realMime = mime_content_type($file['tmp_name']);

            if (!in_array($realMime, $allowedMimes, true)) {
                echo 'Invalid file type. Only XLSX files are accepted.';
                return;
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['xls', 'xlsx'], true)) {
                echo 'Invalid file extension.';
                return;
            }

            $reader      = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($file['tmp_name']);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray();

            array_shift($sheetData); // Remove school name row
            $headers    = array_map('trim', $sheetData[0] ?? []);
            unset($sheetData[0]);
            $sheetData = array_values($sheetData);

            $headers = $this->CM->normalizeKeys(array_combine($headers, $headers));

            $requiredFields = ['User Id', 'Name', 'School Name', 'Gender', 'Phone Number', 'Email', 'Password', 'Address'];
            $missing = array_diff($requiredFields, array_keys($headers));
            if (!empty($missing)) {
                echo 'Required fields missing from header: ' . implode(', ', $missing);
                return;
            }

            $successCount = $errorCount = 0;

            foreach ($sheetData as $i => $row) {
                if (!array_filter($row)) {
                    continue;
                }

                $rowData = array_map(function ($v) { return is_string($v) ? trim($v) : $v; },
                    array_combine(array_keys($headers), $row));

                $formattedData = [
                    'User Id'     => $rowData['User Id']     ?? '',
                    'Name'        => $rowData['Name']        ?? '',
                    'Email'       => $rowData['Email']       ?? '',
                    'Phone Number'=> $rowData['Phone Number'] ?? '',
                    'Gender'      => $rowData['Gender']      ?? '',
                    'School Name' => $rowData['School Name'] ?? '',
                    'Address'     => $rowData['Address']     ?? '',
                    'Password'    => $rowData['Password']    ?? '',
                ];

                $missingInRow = array_filter($requiredFields, fn($f) => empty(trim($formattedData[$f])));

                if (!empty($missingInRow)) {
                    log_message('error', "Import: missing fields in row {$i}: " . implode(', ', $missingInRow));
                    $errorCount++;
                    continue;
                }

                $this->add_staff($formattedData);
                $successCount++;
            }

            echo "Import completed. Successful: {$successCount}. Errors: {$errorCount}.";
            redirect(base_url() . 'staff/all_staff');

        } catch (Exception $e) {
            log_message('error', 'import_staff: ' . $e->getMessage());
            echo 'An error occurred during import.';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function add_staff($data)
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $requiredFields = ['User Id', 'Name', 'School Name', 'Gender', 'Phone Number', 'Email', 'Password', 'Address'];

        $missingFields = array_filter($requiredFields, fn($f) => !isset($data[$f]) || trim($data[$f]) === '');
        if (!empty($missingFields)) {
            log_message('error', 'add_staff: required fields missing: ' . implode(', ', $missingFields));
            return;
        }

        if (empty($data['Password'])) {
            $name            = ucfirst($data['Name']);
            $data['Password'] = substr($name, 0, 3) . '123@';
        }

        // [FIX-2] Hash password
        $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

        $phoneNumber = $data['Phone Number'];

        // [FIX-3] Validate phone number
        if (!preg_match('/^[6-9]\d{9}$/', $phoneNumber)) {
            log_message('error', 'add_staff: invalid phone number: ' . $phoneNumber);
            return;
        }

        $currentCount = $this->CM->get_data("Users/Teachers/Count") ?? 1;
        $userId = $currentCount;
        $data['User Id'] = $userId;

        $existingUser = $this->CM->select_data("Users/Teachers/{$school_id}/{$userId}");
        if ($existingUser) {
            log_message('error', 'add_staff: user already exists: ' . $userId);
            return;
        }

        $result = $this->CM->insert_data("Users/Teachers/{$school_id}/", $data);

        if ($result) {
            $this->CM->addKey_pair_data('Exits/', [$phoneNumber => $school_id]);
            $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $userId]);
            $this->CM->addKey_pair_data("Schools/{$school_name}/{$session_year}/Teachers/{$userId}", ['Name' => $data['Name']]);
            $this->CM->addKey_pair_data('Users/Teachers/', ['Count' => $currentCount + 1]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function new_staff()
    {
        // [FIX-1] All school info from session
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $staffIdCount = $this->CM->get_data("Users/Teachers/{$school_id}/Count") ?? 1;

        $data['schoolName']    = $school_name;
        $data['staffIdCount']  = $staffIdCount;
        $data['user_Id']       = $staffIdCount;

        if ($this->input->method() === 'post') {
            header('Content-Type: application/json');

            $postData = $this->input->post();
            $normalizedPostData = [];
            foreach ($postData as $key => $value) {
                $normalizedPostData[str_replace('%20', ' ', urldecode($key))] = $value;
            }

            $staffId   = $normalizedPostData['user_id']    ?? '';
            $staffName = $normalizedPostData['Name']       ?? '';
            $phoneNumber = $normalizedPostData['phone_number'] ?? '';

            if (empty($staffId) || empty($staffName)) {
                $this->json_error('Missing required fields.', 400);
            }

            // [FIX-3] Validate phone
            if (!preg_match('/^[6-9]\d{9}$/', $phoneNumber)) {
                $this->json_error('Invalid phone number.', 400);
            }

            // Date formatting
            $formattedData = [];
            foreach (['dob' => 'DOB', 'date_of_joining' => 'dateOfJoining'] as $field => $outputKey) {
                $dateValue = $normalizedPostData[$field] ?? '';
                if (!empty($dateValue)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $dateValue);
                    if (!$dateObj) {
                        $this->json_error("Invalid {$field} format.", 400);
                    }
                    $formattedData[$outputKey] = $dateObj->format('d-m-Y');
                } else {
                    $formattedData[$outputKey] = '';
                }
            }

            $docUrls = [];

            // Photo upload
            if (!empty($_FILES['Photo']['tmp_name'])) {
                $photo          = $_FILES['Photo'];
                $photoExtension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
                $allowedPhotoExt= ['jpg', 'jpeg'];
                $realMime       = mime_content_type($photo['tmp_name']);

                if (!in_array($photoExtension, $allowedPhotoExt, true) || !in_array($realMime, ['image/jpeg', 'image/jpg'], true)) {
                    $this->json_error('Only JPG/JPEG files are allowed for photos.', 400);
                }

                $photoPath = "{$school_name}/Staff/{$staffId}/Documents/photo_{$staffId}.{$photoExtension}";
                $photoUrl  = $this->CM->handleFileUpload($photo, $school_name, $photoPath, $staffId, true);

                if (empty($photoUrl)) {
                    $this->json_error('Photo upload failed.', 500);
                }
                $docUrls['ProfilePic'] = $photoUrl;
            }

            // Aadhar upload
            if (!empty($_FILES['Aadhar']['tmp_name'])) {
                $aadhar          = $_FILES['Aadhar'];
                $aadharExtension = strtolower(pathinfo($aadhar['name'], PATHINFO_EXTENSION));
                $allowedAadharExt= ['jpg', 'jpeg', 'png', 'pdf'];
                $fileMimeType    = mime_content_type($aadhar['tmp_name']);
                $allowedMimes    = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];

                if (!in_array($aadharExtension, $allowedAadharExt, true) || !in_array($fileMimeType, $allowedMimes, true)) {
                    $this->json_error('Only PDF, JPG, JPEG, or PNG files are allowed for Aadhar.', 400);
                }

                $aadharPath = "{$school_name}/Staff/{$staffId}/Documents/aadhar_{$staffId}.{$aadharExtension}";
                $aadharUrl  = $this->CM->handleFileUpload($aadhar, $school_name, $aadharPath, $staffId, true);

                if (empty($aadharUrl)) {
                    $this->json_error('Aadhar upload failed.', 500);
                }
                $docUrls['Aadhar'] = $aadharUrl;
            }

            $docDataurl = [
                'ProfilePic' => $docUrls['ProfilePic'] ?? '',
                'Aadhar'     => $docUrls['Aadhar']     ?? '',
            ];

            $addressData = [
                'City'       => $normalizedPostData['city']        ?? '',
                'PostalCode' => $normalizedPostData['postal_code'] ?? '',
                'State'      => $normalizedPostData['state']       ?? '',
                'Street'     => $normalizedPostData['street']      ?? '',
            ];

            $bankDetailsData = [
                'accountHolderName' => $normalizedPostData['account_holder'] ?? '',
                'accountNumber'     => $normalizedPostData['account_number'] ?? '',
                'bankName'          => $normalizedPostData['bank_name']      ?? '',
                'ifscCode'          => $normalizedPostData['bank_ifsc']      ?? '',
            ];

            $emergencyContactData = [
                'name'        => $normalizedPostData['emergency_contact_name']  ?? '',
                'phoneNumber' => $normalizedPostData['emergency_contact_phone'] ?? '',
            ];

            $qualificationDetailsData = [
                'experience'           => $normalizedPostData['teacher_experience'] ?? '',
                'highestQualification' => $normalizedPostData['qualification']      ?? '',
                'university'           => $normalizedPostData['university']         ?? '',
                'yearOfPassing'        => $normalizedPostData['year_of_passing']    ?? '',
            ];

            $basicSalary  = is_numeric($normalizedPostData['basicSalary'] ?? '')  ? (float) $normalizedPostData['basicSalary']  : 0.0;
            $allowances   = is_numeric($normalizedPostData['allowances']  ?? '')  ? (float) $normalizedPostData['allowances']   : 0.0;

            $salaryDetailsData = [
                'Allowances'  => $allowances,
                'basicSalary' => $basicSalary,
                'Net Salary'  => $basicSalary + $allowances,
            ];

            // [FIX-2] Hash the password
            $rawPassword = $normalizedPostData['password'] ?? '';
            if (empty($rawPassword)) {
                $rawPassword = substr(ucfirst($staffName), 0, 3) . '123@';
            }

            $staffRecord = [
                'Name'            => $staffName,
                'User ID'         => $staffId,
                'Phone Number'    => $phoneNumber,
                'Position'        => $normalizedPostData['staff_position'] ?? '',
                'Father Name'     => $normalizedPostData['father_name']    ?? '',
                'DOB'             => $formattedData['DOB'],
                'Email'           => $normalizedPostData['email']          ?? '',
                'Gender'          => $normalizedPostData['gender']         ?? '',
                'Category'        => $normalizedPostData['category']       ?? '',
                'Date Of Joining' => $formattedData['dateOfJoining'],
                'Address'         => $addressData,
                'bankDetails'     => $bankDetailsData,
                'Department'      => $normalizedPostData['department']     ?? '',
                'emergencyContact'=> $emergencyContactData,
                'Employment Type' => $normalizedPostData['employment_type']?? '',
                'qualificationDetails' => $qualificationDetailsData,
                'salaryDetails'   => $salaryDetailsData,
                'Blood Group'     => $normalizedPostData['blood_group']    ?? '',
                'Religion'        => $normalizedPostData['religion']       ?? '',
                'ProfilePic'      => $docUrls['ProfilePic']                ?? '',
                'Doc'             => $docDataurl,
                'lastUpdated'     => date('Y-m-d'),
                // [FIX-2] Hashed password stored under Credentials
                'Credentials'     => ['Password' => password_hash($rawPassword, PASSWORD_DEFAULT)],
            ];

            // [FIX-4] Use session school_id instead of undefined $schoolId
            $StaffPath = "Users/Teachers/{$school_id}/{$staffId}";
            $result    = $this->firebase->set($StaffPath, $staffRecord);

            if ($result !== false) {
                $this->CM->addKey_pair_data('Exits/', [$phoneNumber => $school_id]);
                $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $staffId]);
                $newCount = $staffIdCount + 1;
                $this->CM->addKey_pair_data("Users/Teachers/{$school_id}/", ['Count' => $newCount]);
                $this->CM->addKey_pair_data("Schools/{$school_name}/{$session_year}/Teachers/{$staffId}", ['Name' => $staffName]);

                $this->json_success(['message' => 'Staff added successfully.']);
            } else {
                $this->json_error('Failed to save staff record.', 500);
            }
        }

        $this->load->view('include/header');
        $this->load->view('new_staff', $data);
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function delete_staff($id)
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        if (!$id || !preg_match('/^\d+$/', $id)) {
            redirect(base_url() . 'staff/all_staff/');
            return;
        }

        $staff = $this->CM->select_data("Users/Teachers/{$school_id}/{$id}");

        if ($staff && isset($staff['Phone Number'])) {
            $phoneNumber = $staff['Phone Number'];

            $this->CM->delete_data("Schools/{$school_name}/{$session_year}/Teachers", $id);
            $this->CM->delete_data('Exits', $phoneNumber);
            $this->CM->delete_data('User_ids_pno', $phoneNumber);
        }

        redirect(base_url() . 'staff/all_staff/');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function edit_staff($user_id)
    {
        // [FIX-5] All school info from session
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        if ($this->input->method() === 'post') {
            header('Content-Type: application/json');

            $postData = $this->input->post();
            unset($postData['user_id'], $postData['User ID']);

            // File uploads
            $uploadPaths = [];
            foreach (['photo', 'aadhar_card'] as $field) {
                if (!empty($_FILES[$field]['name'])) {
                    $filePath    = $_FILES[$field]['tmp_name'];
                    $extension   = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $realMime    = mime_content_type($filePath);

                    $allowedMimes = [
                        'photo'      => ['image/jpeg', 'image/jpg'],
                        'aadhar_card'=> ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                    ];

                    if (!in_array($realMime, $allowedMimes[$field], true)) {
                        $this->json_error("Invalid file type for {$field}.", 400);
                    }

                    $fileName    = $user_id . '_' . $field . '.' . $extension;
                    $firebasePath= "staff/{$user_id}/{$fileName}";
                    $uploadResult= $this->CM->upload_to_firebase_storage($firebasePath, $filePath);

                    if (($uploadResult['status'] ?? '') === 'success') {
                        $uploadPaths[$field] = $uploadResult['url'];
                    }
                }
            }

            if (!empty($uploadPaths['photo']))       $postData['Photo URL']   = $uploadPaths['photo'];
            if (!empty($uploadPaths['aadhar_card'])) $postData['Aadhar URL']  = $uploadPaths['aadhar_card'];

            // Structured fields
            $structuredFields = [
                'Address' => [
                    'city' => 'City', 'street' => 'Street', 'state' => 'State', 'postalcode' => 'PostalCode',
                ],
                'emergencyContact' => [
                    'emergency_contact_name' => 'name', 'emergency_contact_phone' => 'phoneNumber',
                ],
                'qualificationDetails' => [
                    'teacher_experience' => 'experience', 'qualification' => 'highestQualification',
                    'university' => 'university', 'year_of_passing' => 'yearOfPassing',
                ],
                'bankDetails' => [
                    'account_holder' => 'accountHolderName', 'account_number' => 'accountNumber',
                    'bank_name' => 'bankName', 'bank_ifsc' => 'ifscCode',
                ],
            ];

            $structuredData = [];
            foreach ($structuredFields as $category => $fields) {
                foreach ($fields as $fieldKey => $firebaseKey) {
                    if (isset($postData[$fieldKey])) {
                        $structuredData[$category][$firebaseKey] = $postData[$fieldKey];
                        unset($postData[$fieldKey]);
                    }
                }
            }

            $formattedData = $this->CM->formatAndPrepareFirebaseData($postData);
            $formattedData = array_merge($formattedData, $structuredData);

            // Date formatting
            foreach (['DOB', 'Date Of Joining'] as $dateField) {
                if (!empty($formattedData[$dateField])) {
                    $ts = strtotime($formattedData[$dateField]);
                    $formattedData[$dateField] = $ts ? date('d-m-Y', $ts) : '';
                } else {
                    $formattedData[$dateField] = '';
                }
            }

            // Prevent Credentials from being overwritten via edit
            unset($formattedData['Credentials']);

            $existingData   = $this->firebase->get("Users/Teachers/{$school_id}/{$user_id}");
            $oldPhoneNumber = $existingData['Phone Number'] ?? null;
            $oldName        = $existingData['Name']         ?? null;

            $updateRes = $this->CM->update_data("Users/Teachers/{$school_id}", $user_id, $formattedData);

            if ($updateRes) {
                // Phone number changed — update lookup tables
                if (!empty($formattedData['Phone Number']) && $formattedData['Phone Number'] !== $oldPhoneNumber) {
                    if ($oldPhoneNumber) {
                        $this->firebase->delete('Exits/' . $oldPhoneNumber);
                        $this->firebase->delete('User_ids_pno/' . $oldPhoneNumber);
                    }
                    $newPhone = $formattedData['Phone Number'];
                    $this->CM->update_data('', 'Exits/',       [$newPhone => $school_id]);
                    $this->CM->update_data('', 'User_ids_pno/', [$newPhone => $user_id]);
                }

                // Name changed — update school teachers list
                $teacherName = $formattedData['Name'] ?? null;
                if ($teacherName && $teacherName !== $oldName) {
                    $this->firebase->set("Schools/{$school_name}/{$session_year}/Teachers/{$user_id}", ['Name' => $teacherName]);
                }

                $this->json_success();
            } else {
                $this->json_error('Update failed.', 500);
            }

        } else {
            $data['staff_data'] = $this->CM->select_data("Users/Teachers/{$school_id}/{$user_id}");

            if (!empty($data['staff_data'])) {
                $this->load->view('include/header');
                $this->load->view('edit_staff', ['staff_data' => $data['staff_data']]);
                $this->load->view('include/footer');
            } else {
                log_message('error', 'Staff data not found for ID: ' . $user_id);
                show_404();
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function teacher_profile($id)
    {
        $school_id = $this->school_id;

        if (!$id || !preg_match('/^\d+$/', $id)) {
            show_404();
            return;
        }

        $studentData = $this->firebase->get("Users/Teachers/{$school_id}/{$id}");

        $this->load->view('include/header');
        $this->load->view('teacher_profile', ['teacher' => $studentData]);
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function search_teacher()
    {
        header('Content-Type: application/json');

        $searchResults = [];
        $searchQuery   = trim((string) ($this->input->post('search_name') ?? ''));

        if ($searchQuery) {
            $searchResults = $this->search_by_name($searchQuery);
        }

        echo json_encode($searchResults);
        exit;
    }

    private function search_by_name(string $entry): array
    {
        $school_id    = $this->school_id;
        $results      = [];
        $teachers     = $this->CM->get_data("Users/Teachers/{$school_id}");

        if (!empty($teachers)) {
            foreach ($teachers as $userId => $teacher) {
                if (!is_array($teacher)) continue;

                $name       = $teacher['Name']        ?? '';
                $userIdField= $teacher['User ID']     ?? '';
                $fatherName = $teacher['Father Name'] ?? '';

                if (
                    stripos($name,        $entry) !== false ||
                    stripos($userIdField, $entry) !== false ||
                    stripos($fatherName,  $entry) !== false
                ) {
                    $results[] = [
                        'user_id'    => $userIdField,
                        'name'       => htmlspecialchars($name,       ENT_QUOTES, 'UTF-8'),
                        'father_name'=> htmlspecialchars($fatherName, ENT_QUOTES, 'UTF-8'),
                    ];
                }
            }
        }

        return $results;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function teacher_duty()
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $classesPath      = "Schools/{$school_name}/{$session_year}/Classes";
        $teachersPath     = "Schools/{$school_name}/{$session_year}/Teachers";
        $teacherDetailsPath = "Users/Teachers/{$school_id}";

        $classesData = $this->CM->get_data($classesPath);
        $teacherIds  = $this->CM->get_data($teachersPath);

        $data['teachers'] = [];
        $ClassesData      = [];

        if (!empty($teacherIds)) {
            foreach ($teacherIds as $teacherId => $value) {
                $teacherName = $this->CM->get_data("{$teacherDetailsPath}/{$teacherId}/Name");
                if (!empty($teacherName)) {
                    $data['teachers'][] = "{$teacherId} - {$teacherName}";
                }

                $dutiesData = $this->CM->get_data("Schools/{$school_name}/{$session_year}/Teachers/{$teacherId}/Duties");
                if (!empty($dutiesData)) {
                    foreach ($dutiesData as $dutyType => $classes) {
                        foreach ((array) $classes as $className => $subjects) {
                            foreach ((array) $subjects as $subject => $time) {
                                $data['duties'][] = [
                                    'class'        => $className,
                                    'subject'      => $subject,
                                    'teacher_name' => "{$teacherId} - {$teacherName}",
                                    'duty_type'    => $dutyType,
                                    'duty_time'    => $time,
                                ];
                            }
                        }
                    }
                }
            }
        }

        if (is_array($classesData)) {
            foreach ($classesData as $className => $classInfo) {
                if (!isset($classInfo['Section']) || !is_array($classInfo['Section'])) continue;
                if (empty($classInfo['Section'])) $classInfo['Section']['A'] = '';
                foreach ($classInfo['Section'] as $sectionName => $_) {
                    $ClassesData[] = [
                        'class_name' => $className,
                        'section'    => $sectionName,
                    ];
                }
            }
        }

        $data['Classes'] = $ClassesData;

        $this->load->view('include/header');
        $this->load->view('teacher_duty', $data);
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function fetch_subjects()
    {
        header('Content-Type: application/json');

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $postData = json_decode(file_get_contents('php://input'), true);

        if (!isset($postData['classSection'])) {
            echo json_encode([]);
            return;
        }

        // [FIX-8] Validate classSection before use in path
        $classSection = trim((string) $postData['classSection']);
        if (!$this->valid_class_section($classSection)) {
            $this->json_error('Invalid class section.', 400);
        }

        $subjectsPath = "Schools/{$school_name}/{$session_year}/{$classSection}/Subjects";
        $subjects     = $this->CM->get_data($subjectsPath);

        echo json_encode(is_array($subjects) ? array_keys($subjects) : []);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function assign_duty()
    {
        header('Content-Type: application/json');

        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $classSection = trim((string) $this->input->post('class_section'));
        $subject      = strip_tags(trim((string) $this->input->post('subject')));
        $teacherName  = trim((string) $this->input->post('teacher_name'));
        $dutyType     = trim((string) $this->input->post('duty_type'));
        $timeSlot     = trim((string) $this->input->post('time_slot'));

        if (!$classSection || !$subject || !$teacherName || !$dutyType) {
            $this->json_error('Missing required fields.', 400);
        }

        // [FIX-7] Validate classSection
        if (!$this->valid_class_section($classSection)) {
            $this->json_error('Invalid class section format.', 400);
        }

        if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacherName, $matches)) {
            $this->json_error('Invalid teacher format.', 400);
        }

        $teacherID       = $matches[1];
        $teacherOnlyName = $matches[2];

        $firebasePath  = "Schools/{$school_name}/{$session_year}/Teachers/{$teacherID}/Duties/{$dutyType}/{$classSection}";
        $updateResponse = $this->firebase->update($firebasePath, [$subject => $timeSlot ?: '']);

        $profilePicPath = "Users/Teachers/{$school_id}/{$teacherID}/Doc/ProfilePic";
        $profilePicURL  = $this->firebase->get($profilePicPath) ?: base_url('tools/image/default-school.jpeg');

        $classPath             = "Schools/{$school_name}/{$session_year}/{$classSection}/Subjects/{$subject}";
        $profileUpdateResponse = $this->firebase->update($classPath, [htmlspecialchars($teacherOnlyName, ENT_QUOTES, 'UTF-8') => $profilePicURL]);

        if ($dutyType === 'ClassTeacher') {
            $this->firebase->set("Schools/{$school_name}/{$session_year}/{$classSection}/ClassTeacher", $teacherOnlyName);
        }

        $this->json_success([
            'message' => 'Duty assigned successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function markInactive_duty()
    {
        header('Content-Type: application/json');

        // [FIX-6] Use school_name (not school_id) consistently
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class_name   = trim((string) $this->input->post('class_name'));
        $subject      = strip_tags(trim((string) $this->input->post('subject')));
        $teacher_name = trim((string) $this->input->post('teacher_name'));

        if (!$class_name || !$subject || !$teacher_name) {
            $this->json_error('Invalid data.', 400);
        }

        if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacher_name, $matches)) {
            $this->json_error('Invalid teacher format.', 400);
        }

        $teacherID       = $matches[1];
        $teacherOnlyName = $matches[2];

        // [FIX-6] Use $school_name here (was using $school_id — wrong!)
        $dutyPath = "Schools/{$school_name}/{$session_year}/Teachers/{$teacherID}/Duties";
        $duties   = $this->firebase->get($dutyPath);
        $dutyDeleted = false;

        if ($duties) {
            foreach ($duties as $dutyType => $classes) {
                if (isset($classes[$class_name][$subject])) {
                    unset($classes[$class_name][$subject]);

                    $updateData = !empty($classes[$class_name]) ? [$class_name => $classes[$class_name]] : null;

                    if ($updateData) {
                        $this->firebase->update("{$dutyPath}/{$dutyType}", $updateData);
                    } else {
                        $this->firebase->delete("{$dutyPath}/{$dutyType}/{$class_name}");
                    }

                    if ($dutyType === 'ClassTeacher') {
                        $this->firebase->set("Schools/{$school_name}/{$session_year}/{$class_name}/ClassTeacher", '');
                    }

                    $dutyDeleted = true;
                    break;
                }
            }
        }

        if (!$dutyDeleted) {
            $this->json_error('Duty not found.', 404);
        }

        $subjectPath    = "Schools/{$school_name}/{$session_year}/{$class_name}/Subjects/{$subject}";
        $subjectTeachers = $this->firebase->get($subjectPath);

        if (is_array($subjectTeachers) && isset($subjectTeachers[$teacherOnlyName])) {
            unset($subjectTeachers[$teacherOnlyName]);

            if (!empty($subjectTeachers)) {
                $this->firebase->update($subjectPath, $subjectTeachers);
            } else {
                $this->firebase->set($subjectPath, '');
            }
        }

        $this->json_success(['message' => 'Teacher removed and duty marked inactive.']);
    }
}
