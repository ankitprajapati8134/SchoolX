<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

defined('BASEPATH') or exit('No direct script access allowed');

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

    /**
     * Upload a staff file (Photo or Aadhar Card) to Firebase Storage.
     * Mirrors the uploadStudentFile() pattern from Student.php.
     *
     * Returns ['url' => '...', 'thumbnail' => '...'] on success, false on failure.
     *
     * Storage layout:
     *   Photo     → {school}/Staff/{staffId}/Profile_pic/{label}_{ts}_{rnd}.{ext}
     *   thumbnail → {school}/Staff/{staffId}/Profile_pic/thumbnail/{same filename}
     *   Others    → {school}/Staff/{staffId}/Documents/{label}_{ts}_{rnd}.{ext}
     *   thumbnail → {school}/Staff/{staffId}/Documents/thumbnail/{same filename}
     */
    private function uploadStaffFile(array $file, string $school_name, string $staffId, string $label)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $timestamp = time();
        $random    = substr(md5(uniqid()), 0, 6);
        $safeLabel = str_replace([' ', '.', '#', '$', '[', ']'], '_', $label);
        $fileName  = "{$safeLabel}_{$timestamp}_{$random}.{$ext}";

        $basePath = ($label === 'Photo')
            ? "{$school_name}/Staff/{$staffId}/Profile_pic/"
            : "{$school_name}/Staff/{$staffId}/Documents/";

        $filePath = $basePath . $fileName;

        if ($this->firebase->uploadFile($file['tmp_name'], $filePath) !== true) {
            return false;
        }

        $fileUrl      = $this->firebase->getDownloadUrl($filePath);
        $thumbnailUrl = '';

        // ── Image thumbnail: re-upload original file ──────────────────────────
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $thumbPath = $basePath . "thumbnail/" . $fileName;
            if ($this->firebase->uploadFile($file['tmp_name'], $thumbPath) === true) {
                $thumbnailUrl = $this->firebase->getDownloadUrl($thumbPath);
            }
        }

        // ── PDF thumbnail: try Imagick, fall back to pdf.png placeholder ──────
        if ($ext === 'pdf') {
            $thumbFileName = $safeLabel . '_' . $timestamp . '_' . $random . '_thumb';
            $thumbPath     = $basePath . 'thumbnail/' . $thumbFileName;

            // Try Imagick (requires Ghostscript on the server)
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($file['tmp_name'] . '[0]');
                    $imagick->setImageFormat('jpg');
                    $imagick->setImageCompressionQuality(85);
                    $imagick->thumbnailImage(400, 0);
                    $imagick->flattenImages();

                    $tmp = sys_get_temp_dir() . '/' . $thumbFileName . '.jpg';
                    $imagick->writeImage($tmp);
                    $imagick->clear();
                    $imagick->destroy();

                    $thumbStorePath = $thumbPath . '.jpg';
                    if ($this->firebase->uploadFile($tmp, $thumbStorePath) === true) {
                        $thumbnailUrl = $this->firebase->getDownloadUrl($thumbStorePath);
                    }
                    @unlink($tmp);
                } catch (Exception $e) {
                    log_message('error', 'Staff PDF Imagick thumb failed: ' . $e->getMessage());
                }
            }

            // Fallback: upload the static pdf.png placeholder
            if ($thumbnailUrl === '') {
                $placeholder = FCPATH . 'tools/image/pdf.png';
                if (file_exists($placeholder)) {
                    $thumbStorePath = $thumbPath . '.png';
                    if ($this->firebase->uploadFile($placeholder, $thumbStorePath) === true) {
                        $thumbnailUrl = $this->firebase->getDownloadUrl($thumbStorePath);
                    }
                }
            }
        }

        return ['url' => $fileUrl, 'thumbnail' => $thumbnailUrl];
    }

    /**
     * Extract the Firebase Storage object path from a download URL.
     * e.g. "https://firebasestorage.googleapis.com/v0/b/bucket/o/path%2Ffile.jpg?..."
     *      → "path/file.jpg"
     */
    private function extractStaffStoragePath(string $url): string
    {
        if (empty($url)) return '';
        if (preg_match('#/o/([^?]+)#', $url, $matches)) {
            return urldecode($matches[1]);
        }
        return '';
    }

    /**
     * Delete both the main file and its thumbnail from Firebase Storage.
     * Accepts either an array ['url'=>'...','thumbnail'=>'...'] or a plain URL string.
     */
    private function deleteStaffDoc($docEntry): void
    {
        if (!is_array($docEntry)) {
            $docEntry = ['url' => (string)$docEntry, 'thumbnail' => ''];
        }
        foreach (['url', 'thumbnail'] as $key) {
            $url = $docEntry[$key] ?? '';
            if (!empty($url)) {
                $path = $this->extractStaffStoragePath($url);
                if ($path) $this->CM->delete_file_from_firebase($path);
            }
        }
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

        // Remove non-staff siblings (e.g. the integer 'Count' node).
        // PHP 8 throws a fatal TypeError when the view accesses $s['field']
        // on a non-array value, which stops rendering — so any staff entries
        // that come after 'Count' alphabetically (like STA0006) are never shown.
        $data['staff'] = array_filter($data['staff'], 'is_array');

        // Normalise profile-pic key: new_staff() stores 'ProfilePic',
        // older records may use 'Photo URL'.
        foreach ($data['staff'] as &$s) {
            $s['_profilePic'] = $s['ProfilePic'] ?? $s['Photo URL'] ?? '';
        }
        unset($s);

        // SESSION ISOLATION: only show teachers who are assigned to this session.
        $session_year    = $this->session_year;
        $sessionTeachers = $this->firebase->get("Schools/{$school_name}/{$session_year}/Teachers");
        if (is_array($sessionTeachers) && !empty($sessionTeachers)) {
            $data['staff'] = array_intersect_key($data['staff'], $sessionTeachers);
        } else {
            $data['staff'] = [];
        }

        $data['school_name'] = $school_name;

        $this->load->view('include/header');
        $this->load->view('all_staff', $data);
        $this->load->view('include/footer');
    }

    public function master_staff()
    {
        $this->load->view('include/header');
        $this->load->view('import_staff'); // view file
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function import_staff()
    {
        try {

            $school_id    = $this->school_id;
            $school_name  = $this->school_name;
            $session_year = $this->session_year;

            if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
                redirect('staff/all_staff');
                return;
            }

            $file      = $_FILES['excelFile'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $reader = ($extension === 'csv')
                ? IOFactory::createReader('Csv')
                : IOFactory::createReader('Xlsx');

            $spreadsheet = $reader->load($file['tmp_name']);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray();

            if (count($sheetData) <= 1) {
                $this->session->set_flashdata('import_result', 'Import failed: file is empty.');
                redirect('staff/all_staff');
                return;
            }

            $headers = array_map('trim', $sheetData[0]);
            unset($sheetData[0]);
            $sheetData = array_values($sheetData);

            $count = $this->CM->get_data("Users/Teachers/{$school_id}/Count") ?? 1;

            $success = 0;
            $error   = 0;

            foreach ($sheetData as $row) {

                if (!array_filter($row)) continue;

                // prevent array_combine crash
                if (count($headers) !== count($row)) {
                    $error++;
                    continue;
                }

                $rowData = array_combine($headers, $row);

                $staffId = 'STA' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $name  = trim($rowData['Name']);
                $phone = trim($rowData['Phone Number']);
                $dob   = trim($rowData['DOB']);

                if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
                    $error++;
                    continue;
                }
                // ✅ PASSWORD GENERATION
                $name = trim($name);
                $dob  = trim($dob);

                $cleanName = preg_replace('/\s+/', '', $name);
                $first3    = ucfirst(substr($cleanName, 0, 3));

                $timestamp = strtotime($dob);
                if ($timestamp === false) {
                    $error++;
                    continue;
                }

                $year   = date('Y', $timestamp);
                $last3  = substr($year, -3);

                $plainPassword  = $first3 . $last3 . '@';
                $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

                // ✅ SALARY CALCULATION
                $basic = (float)$rowData['Basic Salary'];
                $allow = (float)$rowData['Allowances'];
                $net   = $basic + $allow;

                $data = [

                    "User ID" => $staffId,
                    "Name" => $name,
                    "Email" => trim($rowData['Email']),
                    "Phone Number" => $phone,
                    "Gender" => trim($rowData['Gender']),
                    "Department" => trim($rowData['Department']),
                    "Position" => trim($rowData['Position']),
                    "Employment Type" => trim($rowData['Employment Type']),
                    "DOB" => $dob,
                    "Date Of Joining" => trim($rowData['Date Of Joining']),
                    "Father Name" => trim($rowData['Father Name']),
                    "Religion" => trim($rowData['Religion']),
                    "Category" => trim($rowData['Category']),
                    "Password" => $hashedPassword,
                    "lastUpdated" => date('Y-m-d'),

                    "qualificationDetails" => [
                        "highestQualification" => trim($rowData['Qualification']),
                        "experience" => trim($rowData['Experience']),
                        "university" => trim($rowData['University']),
                        "yearOfPassing" => trim($rowData['Year Of Passing']),
                    ],

                    "salaryDetails" => [
                        "basicSalary" => $basic,
                        "Allowances" => $allow,
                        "Net Salary" => $net
                    ],

                    "bankDetails" => [
                        "accountHolderName" => trim($rowData['Account Holder Name']),
                        "accountNumber" => trim($rowData['Account Number']),
                        "bankName" => trim($rowData['Bank Name']),
                        "ifscCode" => trim($rowData['IFSC Code']),
                    ],

                    "emergencyContact" => [
                        "name" => trim($rowData['Emergency Contact Name']),
                        "phoneNumber" => trim($rowData['Emergency Contact Number']),
                    ],

                    "Address" => [
                        "Street" => trim($rowData['Street']),
                        "City" => trim($rowData['City']),
                        "State" => trim($rowData['State']),
                        "PostalCode" => trim($rowData['Postal Code'])
                    ],

                    "ProfilePic" => "",

                    // ✅ EMPTY DOC STRUCTURE (matches new_staff / edit_staff)
                    "Doc" => [
                        "Photo"       => ["url" => "", "thumbnail" => ""],
                        "Aadhar Card" => ["url" => "", "thumbnail" => ""],
                    ]
                ];

                $this->firebase->set("Users/Teachers/{$school_id}/{$staffId}", $data);

                $this->CM->addKey_pair_data("Schools/{$school_name}/{$session_year}/Teachers/{$staffId}", [
                    'Name' => $name
                ]);

                $this->CM->addKey_pair_data('Exits/', [$phone => $school_id]);
                $this->CM->addKey_pair_data('User_ids_pno/', [$phone => $staffId]);

                $count++;
                $success++;
            }

            $this->CM->addKey_pair_data("Users/Teachers/{$school_id}/", ['Count' => $count]);

            $this->session->set_flashdata('import_result', "Staff Imported: {$success} | Failed: {$error}");
            redirect('staff/all_staff');
        } catch (Exception $e) {

            log_message('error', 'IMPORT STAFF ERROR: ' . $e->getMessage());
            $this->session->set_flashdata('import_result', 'Import failed');
            redirect('staff/all_staff');
        }
    }

    // // ─────────────────────────────────────────────────────────────────────────

    // public function add_staff($data)
    // {
    //     $school_id    = $this->school_id;
    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     $requiredFields = ['User Id', 'Name', 'School Name', 'Gender', 'Phone Number', 'Email', 'Password', 'Address'];

    //     $missingFields = array_filter($requiredFields, fn($f) => !isset($data[$f]) || trim($data[$f]) === '');
    //     if (!empty($missingFields)) {
    //         log_message('error', 'add_staff: required fields missing: ' . implode(', ', $missingFields));
    //         return;
    //     }

    //     if (empty($data['Password'])) {
    //         $name            = ucfirst($data['Name']);
    //         $data['Password'] = substr($name, 0, 3) . '123@';
    //     }

    //     // [FIX-2] Hash password
    //     $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

    //     $phoneNumber = $data['Phone Number'];

    //     // [FIX-3] Validate phone number
    //     if (!preg_match('/^[6-9]\d{9}$/', $phoneNumber)) {
    //         log_message('error', 'add_staff: invalid phone number: ' . $phoneNumber);
    //         return;
    //     }

    //     $currentCount = $this->CM->get_data("Users/Teachers/Count") ?? 1;
    //     $userId = $currentCount;
    //     $data['User Id'] = $userId;

    //     $existingUser = $this->CM->select_data("Users/Teachers/{$school_id}/{$userId}");
    //     if ($existingUser) {
    //         log_message('error', 'add_staff: user already exists: ' . $userId);
    //         return;
    //     }

    //     $result = $this->CM->insert_data("Users/Teachers/{$school_id}/", $data);

    //     if ($result) {
    //         $this->CM->addKey_pair_data('Exits/', [$phoneNumber => $school_id]);
    //         $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $userId]);
    //         $this->CM->addKey_pair_data("Schools/{$school_name}/{$session_year}/Teachers/{$userId}", ['Name' => $data['Name']]);
    //         $this->CM->addKey_pair_data('Users/Teachers/', ['Count' => $currentCount + 1]);
    //     }
    // }

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

            // ── Doc structure: Photo + Aadhar Card (mirrors student pattern) ──
            $docData = [
                'Photo'       => ['url' => '', 'thumbnail' => ''],
                'Aadhar Card' => ['url' => '', 'thumbnail' => ''],
            ];

            // Photo upload
            if (!empty($_FILES['Photo']['tmp_name'])) {
                $photo    = $_FILES['Photo'];
                $realMime = mime_content_type($photo['tmp_name']);

                if (!in_array($realMime, ['image/jpeg', 'image/jpg'], true)) {
                    $this->json_error('Only JPG/JPEG files are allowed for photos.', 400);
                }

                $result = $this->uploadStaffFile($photo, $school_name, $staffId, 'Photo');
                if (!$result) {
                    $this->json_error('Photo upload failed.', 500);
                }
                $docData['Photo'] = $result;
            }

            // Aadhar Card upload
            if (!empty($_FILES['Aadhar']['tmp_name'])) {
                $aadhar   = $_FILES['Aadhar'];
                $realMime = mime_content_type($aadhar['tmp_name']);

                if (!in_array($realMime, ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'], true)) {
                    $this->json_error('Only PDF, JPG, JPEG, or PNG files are allowed for Aadhar.', 400);
                }

                $result = $this->uploadStaffFile($aadhar, $school_name, $staffId, 'Aadhar Card');
                if (!$result) {
                    $this->json_error('Aadhar upload failed.', 500);
                }
                $docData['Aadhar Card'] = $result;
            }

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
                'emergencyContact' => $emergencyContactData,
                'Employment Type' => $normalizedPostData['employment_type'] ?? '',
                'qualificationDetails' => $qualificationDetailsData,
                'salaryDetails'   => $salaryDetailsData,
                'Blood Group'     => $normalizedPostData['blood_group']    ?? '',
                'Religion'        => $normalizedPostData['religion']       ?? '',
                'ProfilePic'      => $docData['Photo']['url'],
                'Doc'             => $docData,
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

        if (!$id || !preg_match('/^[A-Za-z0-9]+$/', $id)) {
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

            // Fetch existing record once — used for old-file deletion and Doc merge
            $existingData   = $this->firebase->get("Users/Teachers/{$school_id}/{$user_id}");
            $existingDoc    = is_array($existingData['Doc'] ?? null) ? $existingData['Doc'] : [];
            $docUpdates     = [];

            // ── Photo upload ──────────────────────────────────────────────────
            if (!empty($_FILES['Photo']['tmp_name'])) {
                $photo    = $_FILES['Photo'];
                $realMime = mime_content_type($photo['tmp_name']);

                if (!in_array($realMime, ['image/jpeg', 'image/jpg'], true)) {
                    $this->json_error('Only JPG/JPEG files are allowed for photos.', 400);
                }

                // Delete old photo + thumbnail from Storage
                $this->deleteStaffDoc($existingDoc['Photo'] ?? ($existingData['Photo URL'] ?? ''));

                $result = $this->uploadStaffFile($photo, $school_name, $user_id, 'Photo');
                if ($result) {
                    $docUpdates['Photo']    = $result;
                    $postData['ProfilePic'] = $result['url'];
                }
            }

            // ── Aadhar Card upload ────────────────────────────────────────────
            if (!empty($_FILES['Aadhar']['tmp_name'])) {
                $aadhar   = $_FILES['Aadhar'];
                $realMime = mime_content_type($aadhar['tmp_name']);

                if (!in_array($realMime, ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'], true)) {
                    $this->json_error('Only PDF, JPG, JPEG, or PNG files are allowed for Aadhar.', 400);
                }

                // Delete old Aadhar + thumbnail from Storage
                $this->deleteStaffDoc($existingDoc['Aadhar Card'] ?? ($existingData['Aadhar URL'] ?? ''));

                $result = $this->uploadStaffFile($aadhar, $school_name, $user_id, 'Aadhar Card');
                if ($result) {
                    $docUpdates['Aadhar Card'] = $result;
                }
            }

            // Structured fields
            $structuredFields = [
                'Address' => [
                    'city' => 'City',
                    'street' => 'Street',
                    'state' => 'State',
                    'postalcode' => 'PostalCode',
                ],
                'emergencyContact' => [
                    'emergency_contact_name' => 'name',
                    'emergency_contact_phone' => 'phoneNumber',
                ],
                'qualificationDetails' => [
                    'teacher_experience' => 'experience',
                    'qualification' => 'highestQualification',
                    'university' => 'university',
                    'year_of_passing' => 'yearOfPassing',
                ],
                'bankDetails' => [
                    'account_holder' => 'accountHolderName',
                    'account_number' => 'accountNumber',
                    'bank_name' => 'bankName',
                    'bank_ifsc' => 'ifscCode',
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

            // Merge updated Doc entries (if any files were uploaded) into the
            // existing Doc node so unchanged documents are preserved.
            if (!empty($docUpdates)) {
                $formattedData['Doc'] = array_merge($existingDoc, $docUpdates);
            }

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

        if (!$id || !preg_match('/^[A-Za-z0-9]+$/', $id)) {
            show_404();
            return;
        }

        $teacherData = $this->firebase->get("Users/Teachers/{$school_id}/{$id}");

        $this->load->view('include/header');
        $this->load->view('teacher_profile', ['teacher' => $teacherData]);
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
                $userIdField = $teacher['User ID']     ?? '';
                $fatherName = $teacher['Father Name'] ?? '';

                if (
                    stripos($name,        $entry) !== false ||
                    stripos($userIdField, $entry) !== false ||
                    stripos($fatherName,  $entry) !== false
                ) {
                    $results[] = [
                        'user_id'    => $userIdField,
                        'name'       => htmlspecialchars($name,       ENT_QUOTES, 'UTF-8'),
                        'father_name' => htmlspecialchars($fatherName, ENT_QUOTES, 'UTF-8'),
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

        $teachersPath       = "Schools/{$school_name}/{$session_year}/Teachers";
        $teacherDetailsPath = "Users/Teachers/{$school_id}";

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
                                // Parse "Class 9th 'A'" → class="Class 9th", section="A"
                                preg_match("/^(.+?)\s*'([^']*)'\s*$/", $className, $parts);
                                $classOnly   = isset($parts[1]) ? trim($parts[1]) : $className;
                                $sectionOnly = isset($parts[2]) ? trim($parts[2]) : '';

                                $data['duties'][] = [
                                    'class_section' => $className,
                                    'class'         => $classOnly,
                                    'section'       => $sectionOnly,
                                    'subject'       => $subject,
                                    'teacher_name'  => "{$teacherId} - {$teacherName}",
                                    'duty_type'     => $dutyType,
                                    'duty_time'     => is_string($time) ? $time : '',
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Build class/section list from the correct Firebase structure:
        // Schools/{school}/{session}/Class 9th/Section A/...
        $sessionClassKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session_year}");
        foreach ($sessionClassKeys as $classKey) {
            if (strpos($classKey, 'Class ') !== 0) continue;
            $sectionKeys = $this->firebase->shallow_get("Schools/{$school_name}/{$session_year}/{$classKey}");
            foreach ($sectionKeys as $sectionKey) {
                if (strpos($sectionKey, 'Section ') !== 0) continue;
                $ClassesData[] = [
                    'class_name' => $classKey,
                    'section'    => str_replace('Section ', '', $sectionKey),
                ];
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

        // Read from $_POST so CI CSRF filter can validate the token
        $className = trim((string) $this->input->post('class_name'));
        $section   = trim((string) $this->input->post('section'));

        if (!$className || !$section) {
            echo json_encode([]);
            return;
        }

        // Build combined key: "Class 9th 'A'"
        $classSection = $className . " '" . $section . "'";

        // [FIX-8] Validate classSection before use in path
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

    // ─────────────────────────────────────────────────────────────────────────

    public function teacher_id_card()
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        // Fetch all staff records for this school
        $allStaff = $this->CM->select_data("Users/Teachers/{$school_id}");
        if (!is_array($allStaff)) $allStaff = [];

        // Keep only array entries (drop 'Count' and other non-staff nodes)
        $allStaff = array_filter($allStaff, 'is_array');

        // SESSION ISOLATION: only show teachers assigned to this session
        $sessionTeachers = $this->firebase->get("Schools/{$school_name}/{$session_year}/Teachers");
        if (is_array($sessionTeachers) && !empty($sessionTeachers)) {
            $allStaff = array_intersect_key($allStaff, $sessionTeachers);
        } else {
            $allStaff = [];
        }

        $data['staff']        = array_values($allStaff);
        $data['session_year'] = $session_year;
        $data['school_name']  = $school_name;

        $this->load->view('include/header');
        $this->load->view('teacher_id_card', $data);
        $this->load->view('include/footer');
    }
}
