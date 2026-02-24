<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Staff extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function all_staff()
    {
        $school_id   = $this->school_id;
        $school_name = $this->school_name;

        /* ── Fetch all records under Users/Teachers/{school_id} ── */
        $raw = $this->CM->select_data('Users/Teachers/' . $school_id);

        $staffList = [];

        if (is_array($raw)) {
            foreach ($raw as $key => $record) {

                /* Skip the 'Count' meta-key Firebase stores alongside records */
                if ($key === 'Count' || !is_array($record)) {
                    continue;
                }

                $record['_profilePic'] =
                    $record['Doc']['Photo']['url']
                    ?? $record['ProfilePic']
                    ?? $record['Doc']['ProfilePic']
                    ?? $record['Photo URL']
                    ?? '';

                $staffList[] = $record;
            }
        }

        /* ── Sort by Name A→Z (case-insensitive) ── */
        usort($staffList, function ($a, $b) {
            return strcasecmp($a['Name'] ?? '', $b['Name'] ?? '');
        });

        $data['staff']       = $staffList;
        $data['school_name'] = $school_name;
        $data['total_staff'] = count($staffList);

        $this->load->view('include/header');
        $this->load->view('all_staff', $data);
        $this->load->view('include/footer');
    }



    // public function all_staff()
    // {

    //     $school_id = $this->school_id;
    //     $school_name = $this->school_name;

    //     $data['staff'] = $this->CM->select_data('Users/Teachers/' . $school_id);

    //     if (!is_array($data['staff'])) {
    //         $data['staff'] = []; // Ensure staff is an array
    //     }


    //     $data['schools'] = $this->CM->select_data('School_ids');


    //     // Fetch the school name for the given school ID
    //     $data['school_name'] = $school_name;



    //     $this->load->view('include/header');
    //     $this->load->view('all_staff', $data);
    //     $this->load->view('include/footer');
    // }


    public function import_staff()
    {
        try {
            if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['excelFile'];

                $file_mimes = array(
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/xls',
                    'text/xlsx'
                );

                if (in_array($file['type'], $file_mimes)) {
                    $arr_file = explode('.', $file['name']);
                    $extension = strtolower(end($arr_file));

                    $reader = ($extension == 'csv') ? IOFactory::createReader('Csv') : IOFactory::createReader('Xlsx');
                    $spreadsheet = $reader->load($file['tmp_name']);
                    $sheetData = $spreadsheet->getActiveSheet()->toArray();

                    // Remove the first row (School Name)
                    array_shift($sheetData);

                    // Get the headers from the first row and remove it
                    $headers = array_map('trim', $sheetData[0]);
                    unset($sheetData[0]); // Remove header row
                    $sheetData = array_values($sheetData); // Reindex array

                    // Normalize headers: Trim spaces and remove unwanted characters
                    $headers = array_map(function ($header) {
                        return trim(preg_replace('/[\[\]]/', '', $header));
                    }, $headers);

                    // Combine headers with their normalized versions
                    $headers = array_combine($headers, $headers);
                    $headers = $this->CM->normalizeKeys($headers);

                    // Define required fields
                    $requiredFields = [
                        'User Id',
                        'Name',
                        'School Name',
                        'Gender',
                        'Phone Number',
                        'Email',
                        'Password',
                        'Address'
                    ];

                    // Check if required fields exist in the header
                    $missingRequiredFields = array_diff($requiredFields, array_keys($headers));
                    if (!empty($missingRequiredFields)) {
                        echo "Required fields missing from header: " . implode(', ', $missingRequiredFields);
                        return;
                    }

                    $successCount = 0;
                    $errorCount = 0;

                    foreach ($sheetData as $i => $row) {
                        if (array_filter($row)) {
                            $data = array_map(function ($value) {
                                return is_string($value) ? trim($value) : $value;
                            }, array_combine(array_keys($headers), $row));

                            // Format data to match payload structure
                            $formattedData = [
                                'User Id' => $data['User Id'] ?? '',
                                'Name' => $data['Name'] ?? '',
                                'Email' => $data['Email'] ?? '',
                                'Phone Number' => $data['Phone Number'] ?? '',
                                'Gender' => $data['Gender'] ?? '',
                                'School Name' => $data['School Name'] ?? '',
                                'Address' => $data['Address'] ?? '',
                                'Password' => $data['Password'] ?? ''
                            ];

                            // Check for missing or empty required fields
                            $missingFields = [];
                            foreach ($requiredFields as $field) {
                                if (!isset($formattedData[$field]) || empty(trim($formattedData[$field]))) {
                                    $missingFields[] = $field;
                                }
                            }

                            // If any required fields are missing, log the error
                            if (!empty($missingFields)) {
                                $error = "Error: Required fields missing for row $i. Missing fields: " . implode(', ', $missingFields);
                                error_log($error); // Log detailed error
                                echo $error . "<br>";
                                $errorCount++;
                                continue;
                            }

                            // Proceed with staff registration
                            $this->add_staff($formattedData);
                            $successCount++;
                        } else {
                            echo "Skipping empty row $i.<br>";
                        }
                    }

                    echo "<br>Import completed. Successfully processed rows: $successCount. <br>Rows with errors: $errorCount.";
                    redirect(base_url() . 'staff/manage_staff'); // Change this to your actual controller/method

                } else {
                    echo "Invalid file type.";
                }
            } else {
                echo "No file uploaded or file upload error.";
            }
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
        }
    }



    public function add_staff($data)
    {
        $school_id = $this->school_id;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        // Define required fields
        $requiredFields = [
            'User Id',
            'Name',
            'School Name',
            'Gender',
            'Phone Number',
            'Email',
            'Password',
            'Address'
        ];

        // Check for missing fields
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            echo 'Error: Required fields missing - ' . implode(', ', $missingFields);
            return;
        }


        // if (empty($data['Password'])) {
        //     $name = isset($data['Name']) ? $data['Name'] : '';
        //     if (!empty($name)) {
        //         $password = substr($name, 0, 3) . '123@';
        //         $data['Password'] = $password;
        //     } 

        // Generate password if not provided
        if (empty($data['Password'])) {
            $name = isset($data['Name']) ? $data['Name'] : '';
            if (!empty($name)) {
                // Ensure the first letter of the name is capitalized
                $name = ucfirst($name);
                $password = substr($name, 0, 3) . '123@';
                $data['Password'] = $password;
            } else {
                echo 'Error: Password field cannot be empty';
                return;
            }
        }

        if (isset($data['Phone Number']) && isset($data['User Id']) && isset($data['Name'])) {
            $phoneNumber = $data['Phone Number'];
            $userId = $data['User Id'];
            $staffName = $data['Name'];
            $schoolName = $data['School Name'];  // Ensure this is also set in the form

            // Fetch the current count from Firebase
            $currentCount = $this->CM->get_data('Users/Teachers/Count');
            if ($currentCount === null) {
                $currentCount = 1; // Initialize count if it doesn't exist
            }

            // Set the new staff ID
            $userId = $currentCount; // Explicitly cast to string
            $data['User Id'] = $userId;

            // Check if user already exists
            // $existingUser = $this->CM->select_data('Users/Teachers/1111/' . $userId);
            $existingUser = $this->CM->select_data("Users/Teachers/$school_id/$userId");
            if ($existingUser) {
                echo 'Error: User already exists';
                return;
            }

            // Insert the data in database
            $result = $this->CM->insert_data("Users/Teachers/$school_id/", $data);

            if ($result) {
                // Insert the phone number => school ID pair into "Exits"
                $this->CM->addKey_pair_data('Exits/', [$phoneNumber => $school_id]);

                // Insert the phone number => user ID pair into "User_ids_pno"
                $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $userId]);

                // Add the staff name inside the school
                $staffData = ['Name' => $staffName];
                $this->CM->addKey_pair_data("Schools/$school_name/$session_year/Teachers/$userId", $staffData);

                // Increment and update the count in Firebase
                $newCount = $currentCount + 1;
                $this->CM->addKey_pair_data('Users/Teachers/', ['Count' => $newCount]);

                echo '1';
            } else {
                echo '0';
            }
        } else {
            echo "Error: Required fields missing";
        }
    }



    public function new_staff()
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        /* ── Staff ID counter ── */
        $staffIdCount = $this->CM->get_data('Users/Teachers/' . $school_id . '/Count');
        if ($staffIdCount === null) {
            $staffIdCount = 1;
        }

        // STA0001, STA0002 …
        $generatedStaffId = 'STA' . str_pad($staffIdCount, 4, '0', STR_PAD_LEFT);

        $data['schoolName']   = $school_name;
        $data['staffIdCount'] = $staffIdCount;
        $data['user_Id']      = $generatedStaffId;

        /* ════════════════════════════════════════════
       HANDLE POST
    ════════════════════════════════════════════ */
        if ($this->input->method() === 'post') {

            $postData           = $this->input->post();
            $normalizedPostData = [];
            foreach ($postData as $key => $value) {
                $normalizedPostData[urldecode($key)] = $value;
            }

            /* ── Required fields ── */
            $staffId     = $normalizedPostData['user_id']      ?? '';
            $staffName   = $normalizedPostData['Name']         ?? '';
            $phoneNumber = $normalizedPostData['phone_number'] ?? '';

            if (empty($staffId) || empty($staffName)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
                return;
            }

            /* ── Format dates ── */
            $formattedData = [];
            $dates = ['dob' => 'DOB', 'date_of_joining' => 'dateOfJoining'];

            foreach ($dates as $field => $outputKey) {
                $dateValue = $normalizedPostData[$field] ?? '';
                if (!empty($dateValue)) {
                    $dateObj = DateTime::createFromFormat('Y-m-d', $dateValue);
                    if ($dateObj) {
                        $formattedData[$outputKey] = $dateObj->format('d-m-Y');
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Invalid ' . ucfirst($field) . ' format.']);
                        return;
                    }
                } else {
                    $formattedData[$outputKey] = '';
                }
            }

            /* ════════════════════════════════════════════
           STAFF PHOTO UPLOAD
        ════════════════════════════════════════════ */
            if (empty($_FILES['Photo']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'Staff photo is required.']);
                return;
            }

            $photo    = $_FILES['Photo'];
            $photoExt = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));

            if (!in_array($photoExt, ['jpg', 'jpeg'])) {
                echo json_encode(['status' => 'error', 'message' => 'Only JPG or JPEG files are allowed for the staff photo.']);
                return;
            }

            $photoUpload = $this->uploadStaffFile($photo, $school_name, $staffId, 'profile', 'profile');

            if (!$photoUpload) {
                echo json_encode(['status' => 'error', 'message' => 'Photo upload failed.']);
                return;
            }

            /* ════════════════════════════════════════════
           AADHAR DOCUMENT UPLOAD
        ════════════════════════════════════════════ */
            if (empty($_FILES['Aadhar']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'Aadhar document is required.']);
                return;
            }

            $aadhar    = $_FILES['Aadhar'];
            $aadharExt = strtolower(pathinfo($aadhar['name'], PATHINFO_EXTENSION));

            if (!in_array($aadharExt, ['jpg', 'jpeg', 'png', 'pdf'])) {
                echo json_encode(['status' => 'error', 'message' => 'Only PDF, JPG, JPEG or PNG allowed for Aadhar.']);
                return;
            }

            $mimeType     = mime_content_type($aadhar['tmp_name']);
            $isValidPdf   = ($mimeType === 'application/pdf'      && $aadharExt === 'pdf');
            $isValidImage = (strpos($mimeType, 'image') !== false && in_array($aadharExt, ['jpg', 'jpeg', 'png']));

            if (!$isValidPdf && !$isValidImage) {
                echo json_encode(['status' => 'error', 'message' => 'Aadhar file type mismatch. Please re-upload.']);
                return;
            }

            $aadharUpload = $this->uploadStaffFile($aadhar, $school_name, $staffId, 'Aadhar Card', 'document');

            if (!$aadharUpload) {
                echo json_encode(['status' => 'error', 'message' => 'Aadhar upload failed.']);
                return;
            }

            /* ── Net Salary ── */
            $basicSalary = isset($normalizedPostData['basicSalary']) ? (float)$normalizedPostData['basicSalary'] : 0;
            $allowances  = isset($normalizedPostData['allowances'])  ? (float)$normalizedPostData['allowances']  : 0;
            $netSalary   = $basicSalary + $allowances;

            /* ── Nested objects ── */
            $addressData = [
                'City'       => $normalizedPostData['city']        ?? '',
                'PostalCode' => $normalizedPostData['postal_code'] ?? '',
                'State'      => $normalizedPostData['state']       ?? '',
                'Street'     => $normalizedPostData['street']      ?? '',
            ];

            $bankDetailsData = [
                'accountHolderName' => $normalizedPostData['account_holder'] ?? '',
                'accountNumber'     => $normalizedPostData['account_number'] ?? '',
                'bankName'          => $normalizedPostData['bank_name']      ?? 'Unknown',
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

            $salaryDetailsData = [
                'Allowances'  => $normalizedPostData['allowances']  ?? '',
                'basicSalary' => $normalizedPostData['basicSalary'] ?? '',
                'Net Salary'  => $netSalary,
            ];

            $password = !empty($staffName) ? (substr($staffName, 0, 3) . '123@') : '';

            /* ── Staff data ── */
            $staffData = [
                'Name'             => $staffName,
                'User ID'          => $staffId,
                'Phone Number'     => $phoneNumber,
                'Position'         => $normalizedPostData['staff_position']  ?? '',
                'Password'         => $password,
                'Father Name'      => $normalizedPostData['father_name']     ?? '',
                'DOB'              => $formattedData['DOB'],
                'Email'            => $normalizedPostData['email']           ?? '',
                'Gender'           => $normalizedPostData['gender']          ?? '',
                'Category'         => $normalizedPostData['category']        ?? '',
                'Date Of Joining'  => $formattedData['dateOfJoining'],
                'Address'          => $addressData,
                'bankDetails'      => $bankDetailsData,
                'Department'       => $normalizedPostData['department']      ?? '',
                'emergencyContact' => $emergencyContactData,
                'Employment Type'  => $normalizedPostData['employment_type'] ?? '',
                'qualificationDetails' => $qualificationDetailsData,
                'salaryDetails'    => $salaryDetailsData,
                'Blood Group'      => $normalizedPostData['blood_group']     ?? '',
                'Religion'         => $normalizedPostData['religion']        ?? '',
                'lastUpdated'      => date('Y-m-d'),
                'ProfilePic'       => $photoUpload['document'] ?? '',
                'Doc'              => [
                    'Aadhar Card' => [
                        'url'       => $aadharUpload['document']  ?? '',
                        'thumbnail' => $aadharUpload['thumbnail'] ?? '',
                    ],
                    'Photo' => [
                        'url'       => $photoUpload['document']  ?? '',
                        'thumbnail' => $photoUpload['thumbnail'] ?? '',
                    ],
                ],
            ];

            /* ── Save to Firebase Realtime DB ── */
            $result = $this->firebase->set('Users/Teachers/' . $school_id . '/' . $staffId, $staffData);

            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save staff data.']);
                return;
            }

            /* ── Final mappings ── */
            $this->CM->addKey_pair_data('Exits/',        [$phoneNumber => $school_id]);
            $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $staffId]);
            $this->CM->addKey_pair_data('Users/Teachers/' . $school_id . '/', ['Count' => $staffIdCount + 1]);
            $this->CM->addKey_pair_data(
                "Schools/{$school_name}/{$session_year}/Teachers/{$staffId}",
                ['Name' => $staffName]
            );

            echo json_encode(['status' => 'success', 'message' => 'Staff added successfully!']);
            return;
        }

        /* ── GET: load view ── */
        $this->load->view('include/header');
        $this->load->view('new_staff', $data);
        $this->load->view('include/footer');
    }



    private function uploadStaffFile($file, $schoolName, $staffId, $label, $mode = 'document')
    {
        /* ── 1. Sanitise label for filename  e.g. "Aadhar Card" → "Aadhar_Card" ── */
        $safeLabel = str_replace(' ', '_', $label);

        /* ── 2. Unique filename base ── */
        $timestamp    = time();
        $randSuffix   = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $baseFilename = "{$safeLabel}_{$timestamp}_{$randSuffix}";


        if ($mode === 'profile') {
            $docStoragePath   = "Staff/{$staffId}/Profile_pic/{$baseFilename}.{$ext}";
            $thumbStoragePath = "Staff/{$staffId}/Profile_pic/thumbnail/{$baseFilename}.{$ext}";
        } else {
            $docStoragePath   = "Staff/{$staffId}/Documents/{$baseFilename}.{$ext}";
            $thumbStoragePath = "Staff/{$staffId}/Documents/thumbnail/{$baseFilename}.png";
        }

        /* ── 4. Upload the original file to Firebase Storage ── */
        $documentUrl = $this->CM->handleFileUpload(
            $file,
            $schoolName,      // handleFileUpload prepends this — do NOT put it in $docStoragePath
            $docStoragePath,  // path WITHOUT schoolName prefix
            $staffId,
            true              // isPublic → return signed download URL
        );

        if (empty($documentUrl)) {
            log_message('error', "uploadStaffFile: original upload failed — label={$label}, mode={$mode}");
            return false;
        }

        /* ── 5. Generate thumbnail on local disk ── */
        $tmpThumbPath   = tempnam(sys_get_temp_dir(), 'staff_thumb_') . '.png';
        $thumbGenerated = false;

        if ($ext === 'pdf') {
            /*
         * PDF → render first page as PNG using Imagick.
         * If Imagick is not installed we gracefully fall back
         * to using the original URL as the thumbnail.
         */
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($file['tmp_name'] . '[0]'); // first page only
                    $imagick->setImageFormat('png');
                    $imagick->thumbnailImage(300, 0);               // 300 px wide, auto height
                    $imagick->writeImage($tmpThumbPath);
                    $imagick->clear();
                    $imagick->destroy();
                    $thumbGenerated = true;
                } catch (Exception $e) {
                    log_message('error', 'uploadStaffFile Imagick error: ' . $e->getMessage());
                }
            } else {
                log_message('error', 'uploadStaffFile: Imagick not loaded — cannot thumbnail PDF');
            }
        } else {
            /* Image file → resize with GD (built into XAMPP/PHP by default) */
            $src = null;
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $src = @imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'png':
                    $src = @imagecreatefrompng($file['tmp_name']);
                    break;
                case 'webp':
                    $src = @imagecreatefromwebp($file['tmp_name']);
                    break;
            }

            if ($src) {
                $origW  = imagesx($src);
                $origH  = imagesy($src);
                $thumbW = 300;
                $thumbH = (int)round($origH * ($thumbW / $origW));

                $thumb = imagecreatetruecolor($thumbW, $thumbH);

                // Preserve PNG transparency
                if ($ext === 'png') {
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                    imagefilledrectangle($thumb, 0, 0, $thumbW, $thumbH, $transparent);
                }

                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbW, $thumbH, $origW, $origH);
                imagepng($thumb, $tmpThumbPath);
                imagedestroy($src);
                imagedestroy($thumb);
                $thumbGenerated = true;
            } else {
                log_message('error', "uploadStaffFile: GD could not read image — ext={$ext}");
            }
        }

        /* ── 6. Upload thumbnail to Firebase Storage ── */
        $thumbnailUrl = '';

        if ($thumbGenerated && file_exists($tmpThumbPath) && filesize($tmpThumbPath) > 0) {

            /*
         * IMPORTANT: handleFileUpload reads from tmp_name on disk.
         * We must pass the real temp file path, not a fake array.
         * We build a proper $_FILES-style array pointing at the
         * thumbnail file we just wrote to disk.
         */
            $thumbFileArray = [
                'name'     => $baseFilename . '.png',
                'type'     => 'image/png',
                'tmp_name' => $tmpThumbPath,   // ← real path on disk ✓
                'error'    => UPLOAD_ERR_OK,
                'size'     => filesize($tmpThumbPath),
            ];

            $thumbnailUrl = $this->CM->handleFileUpload(
                $thumbFileArray,
                $schoolName,        // prepended internally → "Demo/Staff/.../thumbnail/..."
                $thumbStoragePath,  // path WITHOUT schoolName
                $staffId,
                true
            );

            @unlink($tmpThumbPath); // clean up local temp file

            if (empty($thumbnailUrl)) {
                log_message('error', "uploadStaffFile: thumbnail upload failed for {$label} — falling back to document URL");
                $thumbnailUrl = $documentUrl;
            }
        } else {
            // Could not generate thumbnail — use original URL as fallback
            log_message('error', "uploadStaffFile: thumbnail generation failed for {$label} — falling back to document URL");
            $thumbnailUrl = $documentUrl;
            if (file_exists($tmpThumbPath)) {
                @unlink($tmpThumbPath);
            }
        }

        /* ── 7. Return ── */
        return [
            'document'  => $documentUrl,
            'thumbnail' => $thumbnailUrl,
        ];
    }



    // public function new_staff()
    // {

    //     $school_id = $this->school_id;
    //     $school_name = $this->school_name;
    //     $session_year = $this->session_year;

    //     $staffIdCount = $this->CM->get_data('Users/Teachers/' . $school_id . '/' . 'Count');
    //     if ($staffIdCount === null) {
    //         $staffIdCount = 1; // Initialize count if it doesn't exist
    //     }

    //     // Pass school name to the view and load it
    //     $data['schoolName'] = $school_name;
    //     $data['staffIdCount'] = $staffIdCount;

    //     // Set the new student ID as string
    //     $userId = $staffIdCount;
    //     $data['user_Id'] = $userId;
    //     // echo '<pre>' . print_r($data, true) . '</pre>';

    //     if ($this->input->method() == 'post') {
    //         $postData = $this->input->post();
    //         $normalizedPostData = [];
    //         foreach ($postData as $key => $value) {
    //             $normalizedKey = str_replace('%20', ' ', urldecode($key));
    //             $normalizedPostData[$normalizedKey] = $value;
    //         }

    //         // Validate required fields

    //         $staffId = $normalizedPostData['user_id'] ?? '';
    //         $staffName = $normalizedPostData['Name'] ?? '';
    //         $phoneNumber = $normalizedPostData['phone_number'] ?? '';

    //         if (empty($staffId) || empty($staffName)) {
    //             echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    //             return;
    //         }


    //         $dates = ['dob' => 'DOB', 'date_of_joining' => 'dateOfJoining']; // Array of fields to be formatted
    //         $formattedData = []; // Store formatted dates
    //         foreach ($dates as $field => $outputKey) {
    //             $dateValue = $normalizedPostData[$field] ?? ''; // Get the value of the field
    //             if (!empty($dateValue)) {
    //                 $dateObj = DateTime::createFromFormat('Y-m-d', $dateValue); // Parse the date in yyyy-mm-dd format
    //                 if ($dateObj) {
    //                     $formattedData[$outputKey] = $dateObj->format('d-m-Y'); // Format to dd-mm-yyyy and assign to the correct key
    //                 } else {
    //                     echo json_encode(['status' => 'error', 'message' => 'Invalid ' . ucfirst($field) . ' format.']);
    //                     return;
    //                 }
    //             } else {
    //                 $formattedData[$outputKey] = ''; // Set empty if the field is missing
    //             }
    //         }


    //         // Initialize the docUrls array
    //         $docUrls = [];

    //         // Handle photo upload
    //         if (!empty($_FILES['Photo']['tmp_name'])) {
    //             $photo = $_FILES['Photo'];
    //             $photoExtension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));

    //             // Validate photo file type
    //             $allowedPhotoExtensions = ['jpg', 'jpeg'];
    //             if (!in_array($photoExtension, $allowedPhotoExtensions)) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Only JPG or JPEG files are allowed for photos.']);
    //                 return;
    //             }

    //             // Check if the school folder exists
    //             $schoolFolderExists = $this->CM->checkIfSchoolFolderExists($school_name);

    //             // Construct the path based on school folder existence
    //             if ($schoolFolderExists === true) {
    //                 $photoPath = "Staff/$staffId/Documents/photo_$staffId.$photoExtension";
    //             } else {
    //                 // $photoPath = 'Schools/' . $schoolName . '/Staff/' . $staffId . '/Documents/photo_. $staffId . $photoExtension';

    //                 $photoPath = "$school_name/Staff/$staffId/Documents/photo_$staffId.$photoExtension";
    //             }

    //             // $photoPath = $schoolName . '/' . $staffId . '/Documents/photo_' . $staffId . '.' . $photoExtension;
    //             $photoUrl = $this->CM->handleFileUpload($photo, $school_name, $photoPath, $staffId, true);

    //             if (!empty($photoUrl)) {
    //                 $docUrls['ProfilePic'] = $photoUrl;
    //                 // $docUrls['ProfilePic'] = $photoPath; // Store path instead of the URL

    //             } else {
    //                 echo json_encode(['status' => 'error', 'message' => 'Photo upload failed.']);
    //                 return;
    //             }
    //         }


    //         // Handle Aadhar upload
    //         if (!empty($_FILES['Aadhar']['tmp_name'])) {
    //             $aadhar = $_FILES['Aadhar'];
    //             $aadharExtension = strtolower(pathinfo($aadhar['name'], PATHINFO_EXTENSION));

    //             // Validate Aadhar file type
    //             $allowedAadharExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    //             if (!in_array($aadharExtension, $allowedAadharExtensions)) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Only PDF, JPG, JPEG, or PNG files are allowed for Aadhar.']);
    //                 return;
    //             }

    //             // Get MIME type of uploaded file
    //             $fileMimeType = mime_content_type($aadhar['tmp_name']);

    //             // Verify file type and enforce correct extension
    //             if ($fileMimeType === 'application/pdf' && $aadharExtension !== 'pdf') {
    //                 echo json_encode(['status' => 'error', 'message' => 'Uploaded file is not a valid PDF.']);
    //                 return;
    //             } elseif (strpos($fileMimeType, 'image') !== false && !in_array($aadharExtension, ['jpg', 'jpeg', 'png'])) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Uploaded file is not a valid image.']);
    //                 return;
    //             }

    //             // Check if the school folder exists in Firebase Storage (before file uploads)
    //             $schoolFolderExists = $this->CM->checkIfSchoolFolderExists($school_name);

    //             // Construct the path based on school folder existence
    //             if ($schoolFolderExists === true) {
    //                 $aadharPath = "Staff/$staffId/Documents/aadhar_$staffId.$aadharExtension";
    //             } else {
    //                 // $photoPath = 'Schools/' . $schoolName . '/Staff/' . $staffId . '/Documents/photo_. $staffId . $photoExtension';

    //                 $aadharPath = "$school_name/Staff/$staffId/Documents/aadhar_$staffId.$aadharExtension";
    //             }


    //             // Upload file to Firebase
    //             $aadharUrl = $this->CM->handleFileUpload($aadhar, $school_name, $aadharPath, $staffId, true);

    //             if (!empty($aadharUrl)) {
    //                 $docUrls['Aadhar'] = $aadharUrl;
    //             } else {
    //                 echo json_encode(['status' => 'error', 'message' => 'Aadhar upload failed.']);
    //                 return;
    //             }
    //         }


    //         // Save Doc object in the database
    //         $docDataurl = [

    //             'ProfilePic' => $docUrls['ProfilePic'] ?? '',
    //             'Aadhar' => $docUrls['Aadhar'] ?? ''
    //         ];


    //         // Process address details
    //         $addressData = [
    //             "City" => $normalizedPostData['city'] ?? '',
    //             "PostalCode" => $normalizedPostData['postal_code'] ?? '',
    //             "State" => $normalizedPostData['state'] ?? '',
    //             "Street" => $normalizedPostData['street'] ?? ''
    //         ];

    //         $bankDetailsData = [
    //             "accountHolderName" => $normalizedPostData['account_holder'] ?? '',
    //             "accountNumber" => $normalizedPostData['account_number'] ?? '',
    //             "bankName" => $normalizedPostData['bank_name'] ?? 'Unknown', // Default value
    //             "ifscCode" => $normalizedPostData['bank_ifsc'] ?? ''
    //         ];

    //         $emergencyContactData = [
    //             "name" => $normalizedPostData['emergency_contact_name'] ?? '',
    //             "phoneNumber" => $normalizedPostData['emergency_contact_phone'] ?? ''
    //         ];

    //         $qualificationDetailsData = [
    //             "experience" => $normalizedPostData['teacher_experience'] ?? '',
    //             "highestQualification" => $normalizedPostData['qualification'] ?? '',
    //             "university" => $normalizedPostData['university'] ?? '',
    //             "yearOfPassing" => $normalizedPostData['year_of_passing'] ?? ''
    //         ];

    //         // Extract salary details from the normalized post data
    //         $basicSalary = isset($normalizedPostData['basicSalary']) ? (float)$normalizedPostData['basicSalary'] : 0;
    //         $allowances = isset($normalizedPostData['allowances']) ? (float)$normalizedPostData['allowances'] : 0;

    //         // Calculate Net Salary
    //         $netSalary = $basicSalary + $allowances;

    //         $salaryDetailsData = [
    //             "Allowances" => $normalizedPostData['allowances'] ?? '',
    //             "basicSalary" => $normalizedPostData['basicSalary'] ?? '',
    //             "Net Salary" => $netSalary
    //         ];


    //         // Generate password based on staff name
    //         $Staffpass = $normalizedPostData['Name'] ?? '';
    //         if (!empty($Staffpass)) {
    //             $password = substr($Staffpass, 0, 3) . '123@';
    //             $normalizedPostData['Password'] = $password;
    //         } else {
    //             $normalizedPostData['Password'] = '';
    //         }
    //         log_message('debug', 'Generated Staff password: ' . $normalizedPostData['Password']);



    //         // Prepare staff data for Firebase
    //         $staffData = [
    //             "Name" => $staffName,
    //             "User ID" => $staffId,
    //             "Phone Number" => $normalizedPostData['phone_number'] ?? '',
    //             "Position" => $normalizedPostData['staff_position'] ?? '',
    //             "Password" => $normalizedPostData['Password'],
    //             "Father Name" => $normalizedPostData['father_name'] ?? '',
    //             "DOB" => $formattedData['DOB'],
    //             "Email" => $normalizedPostData['email'] ?? '',
    //             "Gender" => $normalizedPostData['gender'] ?? '',
    //             "Category" => $normalizedPostData['category'] ?? '',
    //             "Date Of Joining" => $formattedData['dateOfJoining'],
    //             "Address" => $addressData,
    //             "bankDetails" => $bankDetailsData,
    //             "Department" => $normalizedPostData['department'] ?? '',
    //             "emergencyContact" => $emergencyContactData,
    //             "Employment Type" => $normalizedPostData['employment_type'] ?? '',
    //             "qualificationDetails" => $qualificationDetailsData,
    //             "salaryDetails" => $salaryDetailsData,
    //             "Blood Group" => $normalizedPostData['blood_group'] ?? '',
    //             "Religion" => $normalizedPostData['religion'] ?? '',
    //             "ProfilePic" => $docUrls['ProfilePic'] ?? '',
    //             "Doc" => $docDataurl,
    //             "lastUpdated" => date('Y-m-d'),
    //         ];

    //         $StaffPath = 'Users/Teachers/' . $school_id . '/' . $staffId;
    //         $result = $this->firebase->set($StaffPath, $staffData);

    //         if ($result) {
    //             // Insert the phone number => school ID pair into "Exits" and User_ids_pno
    //             $this->CM->addKey_pair_data('Exits/', [$phoneNumber => $school_id]);

    //             $this->CM->addKey_pair_data('User_ids_pno/', [$phoneNumber => $staffId]);

    //             // Increment and update the count in Firebase
    //             $newCount = $staffIdCount + 1;
    //             $this->CM->addKey_pair_data('Users/Teachers/' . $school_id . '/', ['Count' => $newCount]);

    //             // Add the staff name inside the school
    //             $staffData = ['Name' => $staffName];
    //             $this->CM->addKey_pair_data("Schools/$school_name/$session_year/Teachers/$userId", $staffData);


    //             echo json_encode(['status' => 'success']);
    //             exit;
    //         } else {
    //             echo json_encode(['status' => 'error']);
    //             exit;
    //         }
    //     }


    //     $this->load->view('include/header');
    //     $this->load->view('new_staff', $data);
    //     $this->load->view('include/footer');
    // }




    public function delete_staff($id)
    {

        $school_id = $this->school_id;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        // Retrieve the staff's data to get the phone number and school name
        // $staff = $this->CM->select_data('Users/Teachers/1111' . '/' . $id);
        $staff = $this->CM->select_data("Users/Teachers/$school_id/$id");
        // echo '<pre>' . print_r($staff, true) . '</pre>';

        if ($staff && isset($staff['Phone Number']) && isset($staff['School Name'])) {
            $phoneNumber = $staff['Phone Number'];
            $schoolName = $staff['School Name'];

            // Delete the Staff from the specific school path
            $this->CM->delete_data("Schools/$school_name/$session_year/Teachers", $id);

            // Optional: Delete from Exits and User_ids_pno if needed
            $this->CM->delete_data('Exits', $phoneNumber);
            $this->CM->delete_data('User_ids_pno', $phoneNumber);

            // Redirect to the Staff registration page after successful deletion
            redirect(base_url() . 'staff/manage_staff/');
        } else {
            // Handle the error if the staff data is not found
            redirect(base_url() . 'staff/manage_staff/');
        }
    }



    public function edit_staff($user_id)
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        /* ════════════════════════════════════════════
       POST — save updated data
    ════════════════════════════════════════════ */
        if ($this->input->method() === 'post') {

            $postData           = $this->input->post();
            $normalizedPostData = [];
            foreach ($postData as $key => $value) {
                $normalizedPostData[urldecode($key)] = $value;
            }

            // Never allow user_id to be overwritten
            unset($normalizedPostData['user_id'], $normalizedPostData['User ID']);

            /* ── Fetch existing record (needed for fallback URLs + old phone) ── */
            $existingData   = $this->firebase->get('Users/Teachers/' . $school_id . '/' . $user_id);
            $oldPhoneNumber = $existingData['Phone Number'] ?? null;
            $oldName        = $existingData['Name']         ?? null;

            /* ── Existing Doc URLs (used as fallback if no new file uploaded) ── */
            $existingProfilePic       = $existingData['ProfilePic']                       ?? '';
            $existingPhotoUrl         = $existingData['Doc']['Photo']['url']               ?? $existingProfilePic;
            $existingPhotoThumb       = $existingData['Doc']['Photo']['thumbnail']         ?? $existingProfilePic;
            $existingAadharUrl        = $existingData['Doc']['Aadhar Card']['url']         ?? '';
            $existingAadharThumb      = $existingData['Doc']['Aadhar Card']['thumbnail']   ?? '';

            /* ════════════════════════════════════════════
           STAFF PHOTO  (optional on edit)
        ════════════════════════════════════════════ */
            $newProfilePic  = $existingProfilePic;
            $newPhotoUrl    = $existingPhotoUrl;
            $newPhotoThumb  = $existingPhotoThumb;

            if (!empty($_FILES['Photo']['tmp_name'])) {

                $photo    = $_FILES['Photo'];
                $photoExt = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));

                if (!in_array($photoExt, ['jpg', 'jpeg'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Only JPG or JPEG allowed for staff photo.']);
                    return;
                }

                $photoUpload = $this->uploadStaffFile($photo, $school_name, $user_id, 'profile', 'profile');

                if (!$photoUpload) {
                    echo json_encode(['status' => 'error', 'message' => 'Photo upload failed.']);
                    return;
                }

                $newProfilePic = $photoUpload['document']  ?? '';
                $newPhotoUrl   = $photoUpload['document']  ?? '';
                $newPhotoThumb = $photoUpload['thumbnail'] ?? '';
            }

            /* ════════════════════════════════════════════
           AADHAR DOCUMENT  (optional on edit)
        ════════════════════════════════════════════ */
            $newAadharUrl   = $existingAadharUrl;
            $newAadharThumb = $existingAadharThumb;

            if (!empty($_FILES['Aadhar']['tmp_name'])) {

                $aadhar    = $_FILES['Aadhar'];
                $aadharExt = strtolower(pathinfo($aadhar['name'], PATHINFO_EXTENSION));

                if (!in_array($aadharExt, ['jpg', 'jpeg', 'png', 'pdf'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Only PDF, JPG, JPEG or PNG allowed for Aadhar.']);
                    return;
                }

                $mimeType     = mime_content_type($aadhar['tmp_name']);
                $isValidPdf   = ($mimeType === 'application/pdf'      && $aadharExt === 'pdf');
                $isValidImage = (strpos($mimeType, 'image') !== false && in_array($aadharExt, ['jpg', 'jpeg', 'png']));

                if (!$isValidPdf && !$isValidImage) {
                    echo json_encode(['status' => 'error', 'message' => 'Aadhar file type mismatch.']);
                    return;
                }

                $aadharUpload = $this->uploadStaffFile($aadhar, $school_name, $user_id, 'Aadhar Card', 'document');

                if (!$aadharUpload) {
                    echo json_encode(['status' => 'error', 'message' => 'Aadhar upload failed.']);
                    return;
                }

                $newAadharUrl   = $aadharUpload['document']  ?? '';
                $newAadharThumb = $aadharUpload['thumbnail'] ?? '';
            }

            /* ── Format dates ── */
            $formattedDOB = '';
            if (!empty($normalizedPostData['DOB'])) {
                $d = DateTime::createFromFormat('Y-m-d', $normalizedPostData['DOB']);
                if ($d) $formattedDOB = $d->format('d-m-Y');
            }

            $formattedDOJ = '';
            if (!empty($normalizedPostData['date_of_joining'])) {
                $d = DateTime::createFromFormat('Y-m-d', $normalizedPostData['date_of_joining']);
                if ($d) $formattedDOJ = $d->format('d-m-Y');
            }
            // Preserve existing DOJ if not submitted (field is readonly)
            if (empty($formattedDOJ)) {
                $formattedDOJ = $existingData['Date Of Joining'] ?? '';
            }

            /* ── Net Salary ── */
            $basicSalary = isset($normalizedPostData['basicSalary']) ? (float)$normalizedPostData['basicSalary'] : 0;
            $allowances  = isset($normalizedPostData['allowances'])  ? (float)$normalizedPostData['allowances']  : 0;
            $netSalary   = $basicSalary + $allowances;

            /* ── Build updated staff data ── */
            $formattedData = [
                'Name'            => $normalizedPostData['Name']            ?? ($existingData['Name']            ?? ''),
                'Phone Number'    => $normalizedPostData['phone_number']    ?? ($existingData['Phone Number']    ?? ''),
                'Position'        => $normalizedPostData['position']        ?? ($existingData['Position']        ?? ''),
                'Father Name'     => $normalizedPostData['father_name']     ?? ($existingData['Father Name']     ?? ''),
                'DOB'             => $formattedDOB,
                'Email'           => $normalizedPostData['Email']           ?? ($existingData['Email']           ?? ''),
                'Gender'          => $normalizedPostData['gender']          ?? ($existingData['Gender']          ?? ''),
                'Category'        => $normalizedPostData['category']        ?? ($existingData['Category']        ?? ''),
                'Date Of Joining' => $formattedDOJ,
                'Blood Group'     => $normalizedPostData['blood_group']     ?? ($existingData['Blood Group']     ?? ''),
                'Religion'        => $normalizedPostData['religion']        ?? ($existingData['Religion']        ?? ''),
                'Department'      => $normalizedPostData['department']      ?? ($existingData['Department']      ?? ''),
                'Employment Type' => $normalizedPostData['employment_type'] ?? ($existingData['Employment Type'] ?? ''),
                'lastUpdated'     => date('Y-m-d'),

                // ── File URLs ──
                'ProfilePic'      => $newProfilePic,

                // ── Doc block — same structure as new_staff() ──
                'Doc'             => [
                    'Photo'       => [
                        'url'       => $newPhotoUrl,
                        'thumbnail' => $newPhotoThumb,
                    ],
                    'Aadhar Card' => [
                        'url'       => $newAadharUrl,
                        'thumbnail' => $newAadharThumb,
                    ],
                ],

                // ── Nested objects ──
                'Address' => [
                    'City'       => $normalizedPostData['city']        ?? ($existingData['Address']['City']       ?? ''),
                    'Street'     => $normalizedPostData['street']      ?? ($existingData['Address']['Street']     ?? ''),
                    'State'      => $normalizedPostData['state']       ?? ($existingData['Address']['State']      ?? ''),
                    'PostalCode' => $normalizedPostData['postalcode']  ?? ($existingData['Address']['PostalCode'] ?? ''),
                ],

                'emergencyContact' => [
                    'name'        => $normalizedPostData['emergency_contact_name']  ?? ($existingData['emergencyContact']['name']        ?? ''),
                    'phoneNumber' => $normalizedPostData['emergency_contact_phone'] ?? ($existingData['emergencyContact']['phoneNumber'] ?? ''),
                ],

                'qualificationDetails' => [
                    'experience'           => $normalizedPostData['teacher_experience'] ?? ($existingData['qualificationDetails']['experience']           ?? ''),
                    'highestQualification' => $normalizedPostData['qualification']      ?? ($existingData['qualificationDetails']['highestQualification'] ?? ''),
                    'university'           => $normalizedPostData['university']         ?? ($existingData['qualificationDetails']['university']           ?? ''),
                    'yearOfPassing'        => $normalizedPostData['year_of_passing']    ?? ($existingData['qualificationDetails']['yearOfPassing']        ?? ''),
                ],

                'bankDetails' => [
                    'accountHolderName' => $normalizedPostData['account_holder'] ?? ($existingData['bankDetails']['accountHolderName'] ?? ''),
                    'accountNumber'     => $normalizedPostData['account_number'] ?? ($existingData['bankDetails']['accountNumber']     ?? ''),
                    'bankName'          => $normalizedPostData['bank_name']      ?? ($existingData['bankDetails']['bankName']          ?? ''),
                    'ifscCode'          => $normalizedPostData['bank_ifsc']      ?? ($existingData['bankDetails']['ifscCode']          ?? ''),
                ],

                'salaryDetails' => [
                    'basicSalary' => $normalizedPostData['basicSalary'] ?? ($existingData['salaryDetails']['basicSalary'] ?? ''),
                    'Allowances'  => $normalizedPostData['allowances']  ?? ($existingData['salaryDetails']['Allowances']  ?? ''),
                    'Net Salary'  => $netSalary > 0 ? $netSalary : ($existingData['salaryDetails']['Net Salary'] ?? 0),
                ],
            ];

            /* ── Save to Firebase ── */
            $updateRes = $this->CM->update_data('Users/Teachers/' . $school_id, $user_id, $formattedData);

            if (!$updateRes) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update staff data.']);
                return;
            }

            /* ── Phone number change → update Exits + User_ids_pno ── */
            $newPhoneNumber = $formattedData['Phone Number'];
            if ($newPhoneNumber && $newPhoneNumber !== $oldPhoneNumber) {
                if ($oldPhoneNumber) {
                    $this->firebase->delete('Exits',       $oldPhoneNumber);
                    $this->firebase->delete('User_ids_pno', $oldPhoneNumber);
                }
                $this->CM->addKey_pair_data('Exits/',        [$newPhoneNumber => $school_id]);
                $this->CM->addKey_pair_data('User_ids_pno/', [$newPhoneNumber => $user_id]);
            }

            /* ── Name change → update Schools record ── */
            $newName = $formattedData['Name'];
            if ($newName && $newName !== $oldName) {
                $this->firebase->set(
                    "Schools/{$school_name}/{$session_year}/Teachers/{$user_id}",
                    ['Name' => $newName]
                );
            }

            echo json_encode(['status' => 'success', 'message' => 'Staff updated successfully!']);
            return;
        }

        /* ════════════════════════════════════════════
       GET — load edit view
    ════════════════════════════════════════════ */
        $staffData = $this->CM->select_data('Users/Teachers/' . $school_id . '/' . $user_id);

        if (empty($staffData) || ($staffData['User ID'] ?? '') !== $user_id) {
            log_message('error', 'edit_staff: data not found for ID: ' . $user_id);
            show_404();
            return;
        }

        $this->load->view('include/header');
        $this->load->view('edit_staff', ['staff_data' => $staffData]);
        $this->load->view('include/footer');
    }


    // public function edit_staff($user_id)
    // {

    //     $school_id = $this->school_id;
    //     $school_name = $this->school_name;
    //     $session_year = $this->session_year;


    //     if ($this->input->method() == 'post') {
    //         $postData = $this->input->post();

    //         // Remove 'user_id' from postData to prevent re-updating
    //         unset($postData['user_id'], $postData['User ID']);

    //         // Handle file uploads (photo & Aadhar card)
    //         $uploadPaths = [];
    //         $fileFields = ['photo', 'aadhar_card']; // Define which files to upload

    //         foreach ($fileFields as $field) {
    //             if (!empty($_FILES[$field]['name'])) {
    //                 // Upload file to Firebase Storage
    //                 $filePath = $_FILES[$field]['tmp_name'];
    //                 $fileName = $user_id . '_' . $field . '.' . pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    //                 $firebasePath = 'staff/' . $user_id . '/' . $fileName;

    //                 $uploadResult = $this->CM->upload_to_firebase_storage($firebasePath, $filePath);

    //                 if ($uploadResult['status'] == 'success') {
    //                     $uploadPaths[$field] = $uploadResult['url']; // Store uploaded file URL
    //                 }
    //             }
    //         }

    //         // Add uploaded file URLs to postData
    //         if (!empty($uploadPaths['photo'])) {
    //             $postData['Photo URL'] = $uploadPaths['photo'];
    //         }
    //         if (!empty($uploadPaths['aadhar_card'])) {
    //             $postData['Aadhar URL'] = $uploadPaths['aadhar_card'];
    //         }

    //         // Define structured fields and their Firebase field mappings
    //         $structuredFields = [
    //             'Address' => [
    //                 'city' => 'City',
    //                 'street' => 'Street',
    //                 'state' => 'State',
    //                 'postalcode' => 'PostalCode'
    //             ],
    //             'emergencyContact' => [
    //                 'emergency_contact_name' => 'name',
    //                 'emergency_contact_phone' => 'phoneNumber'
    //             ],
    //             'qualificationDetails' => [
    //                 'teacher_experience' => 'experience',
    //                 'qualification' => 'highestQualification',
    //                 'university' => 'university',
    //                 'year_of_passing' => 'yearOfPassing'
    //             ],
    //             'bankDetails' => [
    //                 'account_holder' => 'accountHolderName',
    //                 'account_number' => 'accountNumber',
    //                 'bank_name' => 'bankName',
    //                 'bank_ifsc' => 'ifscCode'
    //             ]
    //         ];

    //         // Initialize an empty array to hold structured data
    //         $structuredData = [];

    //         // Extract structured data *before* processing
    //         foreach ($structuredFields as $category => $fields) {
    //             foreach ($fields as $fieldKey => $firebaseKey) {
    //                 if (isset($postData[$fieldKey])) {
    //                     $structuredData[$category][$firebaseKey] = $postData[$fieldKey];
    //                     unset($postData[$fieldKey]); // Properly remove fields before formatting
    //                 }
    //             }
    //         }

    //         // Now, format the remaining post data for Firebase
    //         $formattedData = $this->CM->formatAndPrepareFirebaseData($postData);

    //         // Merge structured data into the formatted output
    //         $formattedData = array_merge($formattedData, $structuredData);



    //         // Format DOB correctly
    //         if (isset($formattedData['DOB'])) {
    //             // Convert the date from Y-m-d to d-m-Y format
    //             $formattedDate = date('d-m-Y', strtotime($formattedData['DOB']));
    //             $formattedData['DOB'] = $formattedDate; // Update with the correct format
    //         } else {
    //             $formattedData['DOB'] = ''; // In case DOB is not provided, set empty
    //         }

    //         // Format Date Of Joining correctly
    //         if (isset($formattedData['Date Of Joining'])) {
    //             $formattedData['Date Of Joining'] = date('d-m-Y', strtotime($formattedData['Date Of Joining']));
    //         } else {
    //             $formattedData['Date Of Joining'] = ''; // Set empty if not provided
    //         }


    //         // Fetch existing staff data to check for changes
    //         $existingData = $this->firebase->get('Users/Teachers/' . $school_id . '/' . $user_id);
    //         $oldPhoneNumber = isset($existingData['Phone Number']) ? $existingData['Phone Number'] : null;
    //         ////////////////////////////////////////////           
    //         $oldName = isset($existingData['Name']) ? $existingData['Name'] : null;
    //         // $oldUserId = isset($existingData['User Id']) ? $existingData['User Id'] : null;



    //         //////////////////////////////////  
    //         // Update data in Users/Teachers/10001
    //         $updateRes = $this->CM->update_data('Users/Teachers/' . $school_id, $user_id, $formattedData);

    //         if ($updateRes) {
    //             // Check if the phone number has been changed
    //             if (isset($formattedData['Phone Number']) && $formattedData['Phone Number'] !== $oldPhoneNumber) {
    //                 // Delete the old phone number entries in Exits and User_ids_pno
    //                 if ($oldPhoneNumber) {
    //                     $this->firebase->delete('Exits', $oldPhoneNumber);
    //                     $this->firebase->delete('User_ids_pno', $oldPhoneNumber);
    //                 }

    //                 // Add the new phone number entries in Exits and User_ids_pno
    //                 $newPhoneNumber = $formattedData['Phone Number'];
    //                 $newUserId = $formattedData['User ID'];

    //                 $this->CM->update_data('', 'Exits/', [$newPhoneNumber => $school_id]);

    //                 $this->CM->update_data('', 'User_ids_pno/', [$newPhoneNumber => $newUserId]);
    //             }


    //             if ($user_id) {
    //                 // Update the teacher's name in Schools/$schoolName/Teachers/$staffId
    //                 $teacherName = isset($formattedData['Name']) ? $formattedData['Name'] : null;

    //                 if ($teacherName && $teacherName !== $oldName) {
    //                     $updatePath = "Schools/$school_name/$session_year/Teachers/$user_id";
    //                     $this->firebase->set($updatePath, ['Name' => $teacherName]);
    //                 }
    //             }

    //             echo json_encode(['status' => 'success']);
    //         } else {
    //             echo json_encode(['status' => 'failure']);
    //         }
    //     } else {
    //         // Fetch the staff data
    //         $data['staff_data'] = $this->CM->select_data('Users/Teachers/' . $school_id . '/' . $user_id);
    //         // echo '<pre>' . print_r($data, true) . '</pre>';

    //         if (!empty($data['staff_data']) && isset($data['staff_data']['User ID']) && $data['staff_data']['User ID'] == $user_id) {
    //             $this->load->view('include/header');
    //             $this->load->view('edit_staff', ['staff_data' => $data['staff_data']]);
    //             $this->load->view('include/footer');
    //         } else {
    //             log_message('error', 'Staff data not found for ID: ' . $user_id);
    //             show_404(); // Show a 404 error if the staff data is not found
    //         }
    //     }
    // }

    public function teacher_profile($id)
    {
        $school_id = $this->school_id;

        // Construct Firebase path
        $firebasePath = "Users/Teachers/$school_id/$id";

        // Fetch data from Firebase
        $studentData = $this->firebase->get($firebasePath);


        // Prepare data to pass to the view
        $data = [
            'teacher' => $studentData,

            // 'monthlyfee' => $monthlyFee
        ];



        $this->load->view('include/header');
        $this->load->view('teacher_profile', $data);
        $this->load->view('include/footer');
    }


    public function search_teacher()
    {

        // Initialize variables
        $searchResults = [];
        // $searchQuery = '';

        // Check if a search is triggered by name or user ID
        if ($this->input->post('search_name')) {
            $searchQuery = $this->input->post('search_name');
            // Call the search_by_name method to get search results
            $searchResults = $this->search_by_name($searchQuery);
        }

        // Make sure the output is clean and JSON-encoded
        header('Content-Type: application/json');
        // Return the search results as JSON
        echo json_encode($searchResults);
        exit;
    }
    private function search_by_name($entry)
    {
        $school_id = $this->school_id;


        // Fetch data from Firebase based on the name
        $searchResults = [];
        $students = $this->CM->get_data('Users/Teachers/' . $school_id); // Path to fetch all students
        // echo '<pre>' . print_r($students, true) . '</pre>';

        if (!empty($students)) {
            foreach ($students as $userId => $student) {

                // Only include the data you need for the search and ensure the fields exist
                $name = isset($student['Name']) ? $student['Name'] : '';
                $userIdField = isset($student['User ID']) ? $student['User ID'] : '';
                $fatherName = isset($student['Father Name']) ? $student['Father Name'] : '';


                // Perform the search on the selected fields
                if (
                    stripos($name, $entry) !== false ||
                    stripos($userIdField, $entry) !== false ||
                    stripos($fatherName, $entry) !== false
                ) {


                    // If a match is found, add the student data to the results
                    $searchResults[] = [
                        'user_id' => $userIdField,
                        'name' => $name,
                        'father_name' => $fatherName

                    ];
                }
            }
        }
        return $searchResults;
    }

    public function teacher_duty()
    {
        $school_id   = $this->school_id;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        $basePath     = "Schools/{$school_name}/{$session_year}";
        $teachersPath = "{$basePath}/Teachers";
        $teacherDetailsPath = "Users/Teachers/{$school_id}";

        $allData    = $this->CM->get_data($basePath);
        $teacherIds = $this->CM->get_data($teachersPath);

        $data['teachers'] = [];
        $data['duties']   = [];
        $ClassesData      = [];

        /* ===============================
       BUILD CLASS & SECTION LIST
    =============================== */

        if (!empty($allData) && is_array($allData)) {

            foreach ($allData as $key => $value) {

                if (strpos($key, 'Class ') === 0 && is_array($value)) {

                    foreach ($value as $sectionKey => $sectionValue) {

                        if (strpos($sectionKey, 'Section ') === 0) {

                            $ClassesData[] = [
                                'class_name' => $key,
                                'section'    => str_replace('Section ', '', $sectionKey),
                            ];
                        }
                    }
                }
            }
        }

        $data['Classes'] = $ClassesData;

        /* ===============================
       PROCESS TEACHERS (ONLY ONCE)
    =============================== */

        $seen = []; // prevent duplicates

        if (!empty($teacherIds) && is_array($teacherIds)) {

            foreach ($teacherIds as $teacherId => $value) {

                if (!is_array($value)) continue;

                // Get teacher name
                $teacherName = $this->CM->get_data($teacherDetailsPath . '/' . $teacherId . '/Name');

                if (empty($teacherName) && isset($value['Name'])) {
                    $teacherName = $value['Name'];
                }

                if (empty($teacherName)) continue;

                $fullTeacherName = $teacherId . ' - ' . $teacherName;
                $data['teachers'][] = $fullTeacherName;

                /* ===============================
               PROCESS DUTIES
            =============================== */

                if (!empty($value['Duties']) && is_array($value['Duties'])) {

                    foreach ($value['Duties'] as $dutyType => $classes) {

                        if (!is_array($classes)) continue;

                        foreach ($classes as $className => $sections) {

                            if (!is_array($sections)) continue;

                            foreach ($sections as $sectionName => $subjects) {

                                if (!is_array($subjects)) continue;

                                foreach ($subjects as $subject => $info) {

                                    $time = is_array($info)
                                        ? ($info['time'] ?? '')
                                        : (string)$info;

                                    $uniqueKey = implode('|', [
                                        $teacherId,
                                        $dutyType,
                                        $className,
                                        $sectionName,
                                        $subject
                                    ]);

                                    if (isset($seen[$uniqueKey])) continue;

                                    $seen[$uniqueKey] = true;

                                    $data['duties'][] = [
                                        'class'        => $className,
                                        'section'      => str_replace('Section ', '', $sectionName),
                                        'subject'      => $subject,
                                        'teacher_name' => $fullTeacherName,
                                        'duty_type'    => $dutyType,
                                        'duty_time'    => $time
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->load->view('include/header');
        $this->load->view('teacher_duty', $data);
        $this->load->view('include/footer');
    }


    public function get_sections_by_class()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class_name = $this->input->post('class_name');

        if (empty($class_name)) {
            echo json_encode([]);
            return;
        }

        $path = "Schools/{$school_name}/{$session_year}/{$class_name}";
        $classData = $this->CM->get_data($path);

        $sections = [];

        if (!empty($classData) && is_array($classData)) {

            foreach ($classData as $key => $value) {

                if (strpos($key, 'Section ') === 0) {
                    $sections[] = str_replace('Section ', '', $key);
                }
            }
        }

        echo json_encode($sections);
    }


    public function fetch_subjects()
    {
        $school_name = $this->school_name;

        $class_name = $this->input->post('class_name');
        $section    = $this->input->post('section');

        if (empty($class_name)) {
            echo json_encode([]);
            return;
        }

        // Extract numeric part from "Class 4"
        if (preg_match('/\d+/', $class_name, $matches)) {
            $classNumber = $matches[0];
        } else {
            echo json_encode([]);
            return;
        }

        // Path: Schools/{School}/Subject_list/{ClassNumber}
        $subjectsPath = "Schools/{$school_name}/Subject_list/{$classNumber}";

        $subjectsData = $this->CM->get_data($subjectsPath);

        $subjects = [];

        if (!empty($subjectsData) && is_array($subjectsData)) {

            foreach ($subjectsData as $key => $value) {

                if (is_array($value) && isset($value['subject_name'])) {
                    $subjects[] = $value['subject_name'];
                }
            }
        }

        echo json_encode($subjects);
    }

    // public function assign_duty()
    // {
    //     $school_id    = $this->school_id;
    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     header("Content-Type: application/json");

    //     $className   = $this->input->post('class_name');
    //     $section     = $this->input->post('section');
    //     $subject     = $this->input->post('subject');
    //     $teacherName = $this->input->post('teacher_name');
    //     $dutyType    = $this->input->post('duty_type');
    //     $timeSlot    = $this->input->post('time_slot');

    //     if (!$className || !$section || !$subject || !$teacherName || !$dutyType) {
    //         echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    //         return;
    //     }

    //     if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacherName, $matches)) {
    //         echo json_encode(["status" => "error", "message" => "Invalid teacher format"]);
    //         return;
    //     }

    //     $teacherID       = $matches[1];
    //     $teacherOnlyName = $matches[2];

    //     /* ==============================
    //    🔹 FORMAT SECTION PROPERLY
    // ============================== */

    //     $sectionKey = (stripos($section, 'Section ') === 0)
    //         ? $section
    //         : "Section " . $section;

    //     /* ==============================
    //    🔹 FIREBASE PATHS
    // ============================== */

    //     $teacherDutyPath = "Schools/$school_name/$session_year/Teachers/$teacherID/Duties/$dutyType/$className/$sectionKey/$subject";

    //     $classSubjectPath = "Schools/$school_name/$session_year/$className/$sectionKey/Subjects/$subject";

    //     $classTeacherPath = "Schools/$school_name/$session_year/$className/$sectionKey/ClassTeacher";

    //     /* ==============================
    //    🔹 VALIDATIONS (same as before)
    // ============================== */

    //     // if (!empty($this->firebase->get($classSubjectPath))) {
    //     //     echo json_encode([
    //     //         "status" => "error",
    //     //         "message" => "This subject is already assigned."
    //     //     ]);
    //     //     return;
    //     // }

    //     $isUpdate = $this->input->post('is_update');

    //     $originalClass   = $this->input->post('original_class');
    //     $originalSection = $this->input->post('original_section');
    //     $originalSubject = $this->input->post('original_subject');
    //     $originalTeacher = $this->input->post('original_teacher');
    //     $originalDuty    = $this->input->post('original_duty_type');

    //     $originalTeacherNameOnly = null;

    //     if ($originalTeacher && preg_match('/^(\d+)\s-\s(.+)$/', $originalTeacher, $m)) {
    //         $originalTeacherNameOnly = $m[2]; // extract only name
    //     }



    //     $existingSubject = $this->firebase->get($classSubjectPath);

    //     if (!empty($existingSubject) && is_array($existingSubject)) {

    //         $existingTeachers = array_keys($existingSubject);

    //         if ($isUpdate) {

    //             // Allow update if subject belongs to same teacher
    //             if (
    //                 !in_array($originalTeacherNameOnly, $existingTeachers) &&
    //                 !in_array($teacherOnlyName, $existingTeachers)
    //             ) {

    //                 echo json_encode([
    //                     "status" => "error",
    //                     "message" => "This subject is already assigned."
    //                 ]);
    //                 return;
    //             }
    //         } else {

    //             // New assign and subject already assigned
    //             echo json_encode([
    //                 "status" => "error",
    //                 "message" => "This subject is already assigned."
    //             ]);
    //             return;
    //         }
    //     }




    //     if ($dutyType === "ClassTeacher") {

    //         $existingClassTeacher = $this->firebase->get($classTeacherPath);

    //         if (!empty($existingClassTeacher)) {

    //             if ($isUpdate) {

    //                 // Extract original teacher name only
    //                 $originalTeacherNameOnly = null;
    //                 if ($originalTeacher && preg_match('/^(\d+)\s-\s(.+)$/', $originalTeacher, $m)) {
    //                     $originalTeacherNameOnly = $m[2];
    //                 }

    //                 // Allow update if:
    //                 // 1. Same teacher already assigned
    //                 // 2. OR original record was different section
    //                 if ($existingClassTeacher !== $teacherOnlyName) {

    //                     echo json_encode([
    //                         "status" => "error",
    //                         "message" => "Class teacher already assigned."
    //                     ]);
    //                     return;
    //                 }
    //             } else {

    //                 echo json_encode([
    //                     "status" => "error",
    //                     "message" => "Class teacher already assigned."
    //                 ]);
    //                 return;
    //             }
    //         }
    //     }



    //     /* ==============================
    //    🔹 STORE DUTY (STRUCTURED)
    // ============================== */

    //     $teacherDutyData = [
    //         "time" => $timeSlot ?: "",
    //         "name" => $teacherOnlyName
    //     ];

    //     $this->firebase->set($teacherDutyPath, $teacherDutyData);

    //     /* ==============================
    //    🔹 STORE PROFILE PIC UNDER CLASS
    // ============================== */

    //     $profilePicPath = "/Users/Teachers/$school_id/$teacherID/Doc/ProfilePic";
    //     $profilePicURL  = $this->firebase->get($profilePicPath);

    //     if (!$profilePicURL) {
    //         $profilePicURL = base_url('tools/image/default-school.jpeg');
    //     }

    //     $this->firebase->set(
    //         $classSubjectPath . "/" . $teacherOnlyName,
    //         $profilePicURL
    //     );

    //     /* ==============================
    //    🔹 HANDLE CLASS TEACHER
    // ============================== */

    //     if ($dutyType === "ClassTeacher") {
    //         $this->firebase->set($classTeacherPath, $teacherOnlyName);
    //     }

    //     echo json_encode([
    //         "status" => "success",
    //         "message" => "Duty assigned successfully"
    //     ]);
    // }




    public function assign_duty()
    {
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        header("Content-Type: application/json");

        $className   = $this->input->post('class_name');
        $section     = $this->input->post('section');
        $subject     = $this->input->post('subject');
        $teacherName = $this->input->post('teacher_name');
        $dutyType    = $this->input->post('duty_type');
        $timeSlot    = $this->input->post('time_slot');

        $isUpdate = $this->input->post('is_update');

        $originalClass   = $this->input->post('original_class');
        $originalSection = $this->input->post('original_section');
        $originalSubject = $this->input->post('original_subject');
        $originalTeacher = $this->input->post('original_teacher');
        $originalDuty    = $this->input->post('original_duty_type');

        if (!$className || !$section || !$subject || !$teacherName || !$dutyType) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            return;
        }

        if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacherName, $matches)) {
            echo json_encode(["status" => "error", "message" => "Invalid teacher format"]);
            return;
        }

        $teacherID       = $matches[1];
        $teacherOnlyName = $matches[2];

        $sectionKey = (stripos($section, 'Section ') === 0)
            ? $section
            : "Section " . $section;

        $teacherDutyPath = "Schools/$school_name/$session_year/Teachers/$teacherID/Duties/$dutyType/$className/$sectionKey/$subject";
        $classSubjectPath = "Schools/$school_name/$session_year/$className/$sectionKey/Subjects/$subject";
        $classTeacherPath = "Schools/$school_name/$session_year/$className/$sectionKey/ClassTeacher";

        /* ===================================================
       STEP 1: VALIDATE FIRST (DO NOT DELETE YET)
    =================================================== */

        // Validate subject duplicate
        $existingSubject = $this->firebase->get($classSubjectPath);

        if (!empty($existingSubject) && is_array($existingSubject)) {

            $existingTeachers = array_keys($existingSubject);

            if (!in_array($teacherOnlyName, $existingTeachers)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "This subject is already assigned."
                ]);
                return;
            }
        }

        // Validate ClassTeacher
        if ($dutyType === "ClassTeacher") {

            $existingClassTeacher = $this->firebase->get($classTeacherPath);

            if (!empty($existingClassTeacher) && $existingClassTeacher !== $teacherOnlyName) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Class teacher already assigned."
                ]);
                return;
            }
        }

        /* ===================================================
       STEP 2: NOW SAFE TO DELETE OLD RECORD (IF UPDATE)
    =================================================== */

        if ($isUpdate && $originalTeacher) {

            if (preg_match('/^(\d+)\s-\s(.+)$/', $originalTeacher, $m)) {

                $oldTeacherID = $m[1];
                $oldTeacherName = $m[2];

                $oldSectionKey = (stripos($originalSection, 'Section ') === 0)
                    ? $originalSection
                    : "Section " . $originalSection;

                $oldDutyPath = "Schools/$school_name/$session_year/Teachers/$oldTeacherID/Duties/$originalDuty/$originalClass/$oldSectionKey/$originalSubject";

                $this->firebase->delete($oldDutyPath);

                $oldSubjectPath = "Schools/$school_name/$session_year/$originalClass/$oldSectionKey/Subjects/$originalSubject/$oldTeacherName";
                $this->firebase->delete($oldSubjectPath);

                if ($originalDuty === "ClassTeacher") {
                    $oldClassTeacherPath = "Schools/$school_name/$session_year/$originalClass/$oldSectionKey/ClassTeacher";
                    $this->firebase->delete($oldClassTeacherPath);
                }
            }
        }

        /* ===================================================
       STEP 3: INSERT NEW RECORD
    =================================================== */

        $teacherDutyData = [
            "time" => $timeSlot ?: "",
            "name" => $teacherOnlyName
        ];

        $this->firebase->set($teacherDutyPath, $teacherDutyData);

        $profilePicPath = "/Users/Teachers/$school_id/$teacherID/Doc/ProfilePic";
        $profilePicURL  = $this->firebase->get($profilePicPath);

        if (!$profilePicURL) {
            $profilePicURL = base_url('tools/image/default-school.jpeg');
        }

        $this->firebase->set(
            $classSubjectPath . "/" . $teacherOnlyName,
            $profilePicURL
        );

        if ($dutyType === "ClassTeacher") {
            $this->firebase->set($classTeacherPath, $teacherOnlyName);
        }

        echo json_encode([
            "status" => "success",
            "message" => $isUpdate ? "Duty updated successfully" : "Duty assigned successfully"
        ]);
    }



    public function fetch_assigned_subjects()
    {
        header("Content-Type: application/json");

        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $class_name  = $this->input->post('class_name');
        $section     = $this->input->post('section');
        $teacher_name = $this->input->post('teacher_name');
        $duty_type   = $this->input->post('duty_type');

        if (!$class_name || !$section || !$teacher_name || !$duty_type) {
            echo json_encode([]);
            return;
        }

        // Extract teacher ID
        if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacher_name, $matches)) {
            echo json_encode([]);
            return;
        }

        $teacherID = $matches[1];

        $sectionKey = "Section " . $section;

        $path = "Schools/$school_name/$session_year/Teachers/$teacherID/Duties/$duty_type/$class_name/$sectionKey";

        $subjects = $this->firebase->get($path);

        if (!$subjects || !is_array($subjects)) {
            echo json_encode([]);
            return;
        }

        echo json_encode(array_keys($subjects));
    }


    public function markInactive_duty()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        header("Content-Type: application/json");

        $class_name  = $this->input->post('class_name');
        $section     = $this->input->post('section');
        $subject     = $this->input->post('subject');
        $teacher_name = $this->input->post('teacher_name');

        if (!$class_name || !$section || !$subject || !$teacher_name) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            return;
        }

        if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacher_name, $matches)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid teacher format']);
            return;
        }

        $teacherID = $matches[1];
        $teacherOnlyName = $matches[2];

        $sectionKey = (stripos($section, 'Section ') === 0)
            ? $section
            : "Section " . $section;

        /* ============================
       DELETE TEACHER DUTY
    ============================ */

        $teacherDutyPath =
            "Schools/$school_name/$session_year/Teachers/$teacherID/Duties";

        $duties = $this->firebase->get($teacherDutyPath);

        if (!empty($duties)) {

            foreach ($duties as $dutyType => $classes) {

                if (isset($classes[$class_name][$sectionKey][$subject])) {

                    $this->firebase->delete(
                        "$teacherDutyPath/$dutyType/$class_name/$sectionKey/$subject"
                    );

                    // Remove empty section if needed
                    $remainingSubjects = $this->firebase->get(
                        "$teacherDutyPath/$dutyType/$class_name/$sectionKey"
                    );

                    if (empty($remainingSubjects)) {
                        $this->firebase->delete(
                            "$teacherDutyPath/$dutyType/$class_name/$sectionKey"
                        );
                    }

                    break;
                }
            }
        }

        /* ============================
       REMOVE FROM CLASS SUBJECT
    ============================ */

        $classSubjectPath =
            "Schools/$school_name/$session_year/$class_name/$sectionKey/Subjects/$subject/$teacherOnlyName";

        $this->firebase->delete($classSubjectPath);

        /* ============================
       REMOVE CLASS TEACHER IF NEEDED
    ============================ */

        $classTeacherPath =
            "Schools/$school_name/$session_year/$class_name/$sectionKey/ClassTeacher";

        $currentClassTeacher = $this->firebase->get($classTeacherPath);

        if ($currentClassTeacher == $teacherOnlyName) {
            $this->firebase->delete($classTeacherPath);
        }

        echo json_encode([
            "status" => "success",
            "message" => "Duty marked inactive successfully"
        ]);
    }




    // public function fetch_subjects()
    // {
    //     $school_name = $this->school_name;
    //     $session_year = $this->session_year;

    //     $postData = json_decode(file_get_contents('php://input'), true);

    //     if (isset($postData['classSection'])) {
    //         $classSection = $postData['classSection'];
    //         $subjectsPath = 'Schools/' . $school_name  . '/' . 'Subject_list';

    //         // Fetch subjects from Firebase
    //         $subjects = $this->CM->get_data($subjectsPath);

    //         // Return the subjects as a JSON response
    //         echo json_encode(array_keys($subjects));
    //     } else {
    //         echo json_encode([]);
    //     }
    // }

    // public function assign_duty()
    // {
    //     $school_id = $this->school_id;
    //     $school_name = $this->school_name;
    //     $session_year = $this->session_year;

    //     header("Content-Type: application/json");

    //     // $this->load->library('firebase');

    //     // $classSection = $this->input->post('class_section');
    //     // $subject = $this->input->post('subject');
    //     // $teacherName = $this->input->post('teacher_name');
    //     // $dutyType = $this->input->post('duty_type');
    //     // $timeSlot = $this->input->post('time_slot');

    //     // if (!$classSection || !$subject || !$teacherName || !$dutyType) {
    //     //     echo json_encode([
    //     //         "status" => "error",
    //     //         "message" => "Missing required fields"
    //     //     ]);
    //     //     return;
    //     // }

    //     $className = $this->input->post('class_name');
    //     $section   = $this->input->post('section');
    //     $subject   = $this->input->post('subject');
    //     $teacherName = $this->input->post('teacher_name');
    //     $dutyType  = $this->input->post('duty_type');
    //     $timeSlot  = $this->input->post('time_slot');

    //     if (!$className || !$section || !$subject || !$teacherName || !$dutyType) {
    //         echo json_encode([
    //             "status" => "error",
    //             "message" => "Missing required fields"
    //         ]);
    //         return;
    //     }


    //     if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacherName, $matches)) {
    //         echo json_encode([
    //             "status" => "error",
    //             "message" => "Invalid teacher format",
    //             "received_teacherName" => $teacherName
    //         ]);
    //         return;
    //     }

    //     $teacherID = $matches[1];
    //     $teacherOnlyName = $matches[2];


    //     // 🔹 Store Teacher's Duty
    //     // $firebasePath = "Schools/$school_name/$session_year/Teachers/$teacherID/Duties/$dutyType/$classSection";
    //     $firebasePath = "Schools/$school_name/$session_year/Teachers/$teacherID/Duties/$dutyType/$className/$section";

    //     $data = [$subject => $timeSlot ?: ""];
    //     $updateResponse = $this->firebase->update($firebasePath, $data);

    //     // 🔹 Fetch Profile Picture URL

    //     $profilePicPath = "/Users/Teachers/$school_id/$teacherID/Doc/ProfilePic";
    //     $profilePicURL = $this->firebase->get($profilePicPath);

    //     if (!$profilePicURL) {
    //         $profilePicURL = "http://localhost/Grader/school/tools/image/default-school.jpeg";
    //     }

    //     // 🔹 Store Profile Picture under Class & Subject
    //     // $classPath = "Schools/$school_name/$session_year/$classSection/Subjects/$subject";
    //     $classPath = "Schools/$school_name/$session_year/$className/$section/Subjects/$subject";

    //     $profileData = [$teacherOnlyName => $profilePicURL]; // Store as Key-Value Pair
    //     $profileUpdateResponse = $this->firebase->update($classPath, $profileData);

    //     // 🔹 Handle Class Teacher Assignment
    //     if ($dutyType === "ClassTeacher") {
    //         // $classTeacherPath = "Schools/$school_name/$session_year/$classSection/ClassTeacher";
    //         $classTeacherPath = "Schools/$school_name/$session_year/$className/$section/ClassTeacher";

    //         $this->firebase->set($classTeacherPath, $teacherOnlyName);
    //     }

    //     echo json_encode([
    //         "status" => ($updateResponse !== false && $profileUpdateResponse !== false) ? "success" : "error",
    //         "message" => "Duty assigned successfully",
    //         "firebase_paths" => [
    //             "teacher_duty" => $firebasePath,
    //             "class_subject_teacher" => $classPath
    //         ],
    //         "firebase_responses" => [
    //             "duty_update" => $updateResponse,
    //             "profile_update" => $profileUpdateResponse
    //         ]
    //     ]);
    // }




    // public function markInactive_duty()
    // {

    //     $school_id = $this->school_id;
    //     $school_name = $this->school_name;
    //     $session_year = $this->session_year;

    //     header("Content-Type: application/json");

    //     // $this->load->library('firebase');

    //     // Retrieve POST data
    //     $class_name = $this->input->post('class_name');
    //     $subject = $this->input->post('subject');
    //     $teacher_name = $this->input->post('teacher_name');

    //     // Validate required fields
    //     if (!$class_name || !$subject || !$teacher_name) {
    //         echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    //         return;
    //     }

    //     // Extract teacher ID from teacher name (assuming format "ID - Name")
    //     if (!preg_match('/^(\d+)\s-\s(.+)$/', $teacher_name, $matches)) {
    //         echo json_encode([
    //             "status" => "error",
    //             "message" => "Invalid teacher format",
    //             "received_teacherName" => $teacher_name
    //         ]);
    //         return;
    //     }

    //     $teacherID = $matches[1];
    //     $teacherOnlyName = $matches[2]; // Extracted teacher name only

    //     // Firebase path for the teacher's duties
    //     $dutyPath = "Schools/$school_id/$session_year/Teachers/$teacherID/Duties";

    //     // Retrieve existing duties
    //     $duties = $this->firebase->get($dutyPath);
    //     $dutyDeleted = false;

    //     if ($duties) {
    //         foreach ($duties as $dutyType => $classes) {
    //             if (isset($classes[$class_name][$subject])) {
    //                 unset($classes[$class_name][$subject]); // Remove the subject and time slot

    //                 // Ensure we don't pass an empty array to Firebase (prevents unwanted deletions)
    //                 $updateData = count($classes[$class_name]) > 0 ? [$class_name => $classes[$class_name]] : null;

    //                 // Update Firebase for teacher's duty
    //                 if ($updateData) {
    //                     $this->firebase->update("$dutyPath/$dutyType", $updateData);
    //                 } else {
    //                     // If no more subjects are left under this class, remove the class entry
    //                     $this->firebase->delete("$dutyPath/$dutyType/$class_name");
    //                 }

    //                 $dutyDeleted = true;
    //                 // 🔹 If duty is Class Teacher, clear the stored teacher's name
    //                 if ($dutyType === "ClassTeacher") {
    //                     $classTeacherPath = "Schools/$school_name/$session_year/$class_name/ClassTeacher";
    //                     $this->firebase->set($classTeacherPath, "");
    //                 }
    //                 break;
    //             }
    //         }
    //     }

    //     if (!$dutyDeleted) {
    //         echo json_encode(['status' => 'error', 'message' => 'Duty not found']);
    //         return;
    //     }

    //     // 🔹 Remove teacher entry from the subject while ensuring subject node is not deleted if it's the last teacher
    //     $subjectPath = "Schools/$school_name/$session_year/$class_name/Subjects/$subject";

    //     // Fetch current teacher list in the subject
    //     $subjectTeachers = $this->firebase->get($subjectPath);

    //     if ($subjectTeachers === false) {
    //         echo json_encode(['status' => 'error', 'message' => 'Failed to fetch subject teachers data']);
    //         return;
    //     }

    //     if (isset($subjectTeachers[$teacherOnlyName])) {
    //         unset($subjectTeachers[$teacherOnlyName]); // Remove teacher

    //         if (count($subjectTeachers) > 0) {
    //             // If other teachers exist, update the node
    //             $this->firebase->update($subjectPath, $subjectTeachers);
    //         } else {
    //             // If it was the last teacher, set the subject to an empty string instead of deleting
    //             $this->firebase->set($subjectPath, "");
    //         }
    //     }

    //     // 🔹 Remove profile picture entry
    //     $profilePicPath = "$subjectPath/$teacherOnlyName";
    //     $profileDeleteResponse = $this->firebase->delete($profilePicPath);

    //     // 🔹 Remove profile picture entry
    //     $profilePicPath = "$subjectPath/$teacherOnlyName";
    //     $profileDeleteResponse = $this->firebase->delete($profilePicPath);

    //     // Respond with success message
    //     echo json_encode([
    //         "status" => "success",
    //         "message" => "Teacher removed, profile picture deleted, and duty marked inactive.",
    //         "deleted_paths" => [
    //             "duty" => "$dutyPath/$dutyType/$class_name/$subject",
    //             "teacher_entry" => $subjectPath,
    //             "profile_pic" => $profilePicPath
    //         ],
    //         "firebase_responses" => [
    //             "duty_deletion" => $dutyDeleted ? "Deleted successfully" : "Failed to delete",
    //             "teacher_entry_deletion" => count($subjectTeachers) > 0 ? "Updated successfully" : "Set to empty value",
    //             "profile_pic_deletion" => $profileDeleteResponse !== false ? "Deleted successfully" : "Failed to delete"
    //         ]
    //     ]);
    // }





    // private function isTimeConflict($newTime, $existingTime)
    // {
    //     if (empty($newTime) || empty($existingTime)) return false;

    //     list($newStart, $newEnd) = explode('-', $newTime);
    //     list($oldStart, $oldEnd) = explode('-', $existingTime);

    //     $newStart = strtotime($newStart);
    //     $newEnd   = strtotime($newEnd);
    //     $oldStart = strtotime($oldStart);
    //     $oldEnd   = strtotime($oldEnd);

    //     return ($newStart < $oldEnd && $newEnd > $oldStart);
    // }
}
