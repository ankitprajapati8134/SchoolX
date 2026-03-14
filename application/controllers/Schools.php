<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schools extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  BUG FIXES IN THIS CONTROLLER:
    //
    //  1. edit_school() GET branch: echo '<pre>print_r()</pre>' was left in
    //     production code — removed.
    //
    //  2. edit_school() POST: $files array uses $_FILES directly without
    //     checking is_uploaded_file for each; harmless but cleaned up.
    //
    //  3. manage_school() POST: normalizeKeys() converts underscored POST
    //     keys to title-case spaced keys e.g. "subscription_plan" →
    //     "Subscription Plan". The code then reads 'subscription plan'
    //     (lowercase) which never matches after normalisation → all
    //     subscription fields were NULL. Fixed by reading the correct
    //     normalised keys.
    //
    //  4. manage_school() POST: features checkbox array arrives as
    //     'features' (after normalise), but was read as
    //     $normalizedFormData['features'] which is fine — however it was
    //     never cast to array safely. Added (array) cast.
    //
    //  5. manage_school() GET: foreach on $schoolIds could fatal if
    //     select_data() returns null/false. Added null-check guard.
    //
    //  6. deleteMedia(): method='DELETE' from JS but CI's $this->input
    //     doesn't parse DELETE bodies. The URL param ?url= is used via
    //     $_GET which is correct — left as-is but documented.
    //
    //  7. fetchGalleryMedia(): path had leading slash "/Schools/..." which
    //     is inconsistent with how the rest of the app uses Firebase paths
    //     — standardised to no leading slash.
    //
    //  8. schoolProfile(): schoolData cast from object to array defensively.
    //
    //  9. uploadMedia(): ffmpeg path is hardcoded Windows path — wrapped in
    //     a configurable constant with graceful fallback so Linux/prod
    //     servers don't crash on video uploads.
    // ──────────────────────────────────────────────────────────────────────

    public function fees()
    {
        $this->load->view('include/header');
        $this->load->view('fees');
        $this->load->view('include/footer');
    }

    // ── Delete School (Super Admin only) ────────────────────────────────
    public function delete_school($schoolId)
    {
        if ($this->admin_role !== 'Super Admin') {
            show_error('Access denied. Super Admin only.', 403);
            return;
        }
        $schoolName = $this->CM->get_school_name_by_id($schoolId);

        if ($schoolName) {
            $result1             = $this->CM->delete_data('Schools', $schoolName);
            $result2             = $this->CM->delete_data('Indexes/School_codes', $schoolId);
            $deleteStorageResult = $this->CM->delete_folder_from_firebase_storage($schoolName . '/');

            if ($result1 && $result2 && $deleteStorageResult) {
                $currentSchoolCount = $this->CM->get_data('Indexes/School_codes/Count');
                $newSchoolCount     = max(0, (int)$currentSchoolCount - 1);
                $this->CM->addKey_pair_data('Indexes/School_codes/', ['Count' => $newSchoolCount]);
            }
        }

        redirect('schools/manage_school');
    }

    // ── Edit School (Super Admin only) ──────────────────────────────────
    public function edit_school($schoolId)
    {
        if ($this->admin_role !== 'Super Admin') {
            show_error('Access denied. Super Admin only.', 403);
            return;
        }
        $session_year  = $this->session_year;
        $schoolDetails = $this->CM->get_school_name_by_id($schoolId);

        // BUG FIX #1 — get_school_name_by_id may return just a string (the name)
        if (!is_array($schoolDetails)) {
            $schoolDetails = [
                'School Id'   => $schoolId,
                'School Name' => $schoolDetails
            ];
        }

        if ($this->input->method() === 'post') {
            $postData       = $this->input->post();
            $normalizedData = $this->CM->normalizeKeys($postData);
            $newSchoolName  = $normalizedData['School Name'] ?? '';
            $oldSchoolName  = $schoolDetails['School Name']  ?? null;

            if (empty($newSchoolName)) {
                echo '0';
                return;
            }

            $oldFolderPath = $oldSchoolName . '/';
            $newFolderPath = $newSchoolName . '/';

            // BUG FIX #2 — only pass files that were actually uploaded
            $files = [];
            foreach (['school_logos', 'holidays', 'academic'] as $key) {
                if (isset($_FILES[$key]) && is_uploaded_file($_FILES[$key]['tmp_name'] ?? '')) {
                    $files[$key] = $_FILES[$key];
                } else {
                    $files[$key] = ['tmp_name' => '', 'name' => ''];
                }
            }

            $changeSchoolName = $oldSchoolName && $oldSchoolName !== $newSchoolName;
            $updateFiles      = !empty(array_filter($files, fn($f) => !empty($f['tmp_name'])));

            $updatedFiles = $this->CM->update_files_and_folder_in_firebase_storage(
                $oldFolderPath, $newFolderPath, $files, $changeSchoolName, $updateFiles
            );

            if ($updatedFiles === false) {
                echo '0';
                return;
            }

            $existingData = $this->CM->get_data('Schools/' . $oldSchoolName . '/' . $session_year);
            $dataToUpdate = $existingData ?: [];

            if (isset($updatedFiles['school_logos'])) $dataToUpdate['Logo']              = $updatedFiles['school_logos'];
            if (isset($updatedFiles['holidays']))     $dataToUpdate['Holidays']           = $updatedFiles['holidays'];
            if (isset($updatedFiles['academic']))     $dataToUpdate['Academic calendar']  = $updatedFiles['academic'];

            if ($changeSchoolName) {
                if ($existingData) {
                    $res1 = $this->CM->update_data('Schools/' . $newSchoolName . '/' . $session_year, null, $dataToUpdate);
                    if ($res1) {
                        $this->CM->delete_data('Schools/', $oldSchoolName . '/' . $session_year);
                        $res2 = $this->CM->update_data('', 'Indexes/School_codes/', [$schoolId => $newSchoolName]);
                        if (!$res2) { echo '0'; return; }
                    } else { echo '0'; return; }
                } else { echo '0'; return; }
            } else {
                $this->CM->update_data('Schools/' . $newSchoolName . '/' . $session_year, null, $dataToUpdate);
            }

            $userData        = $this->CM->select_data('System/Schools/' . $oldSchoolName);
            $userDataToUpdate = $userData ?: [];

            foreach ($normalizedData as $key => $value) {
                if (!in_array($key, ['School Name', 'school_logos', 'holidays', 'academic'])) {
                    $userDataToUpdate[$key] = $value;
                }
            }
            if (isset($updatedFiles['school_logos'])) $userDataToUpdate['Logo'] = $updatedFiles['school_logos'];
            $userDataToUpdate['School Name'] = $newSchoolName;

            if ($changeSchoolName) {
                $this->CM->update_data('System/Schools/' . $newSchoolName, null, $userDataToUpdate);
                $this->CM->delete_data('System/Schools/', $oldSchoolName);
            } else {
                $this->CM->update_data('System/Schools/' . $oldSchoolName, null, $userDataToUpdate);
            }

            echo '1';

        } else {
            // BUG FIX #1 — removed debug echo '<pre>' that was in production
            $data['school'] = $schoolDetails;

            if (!empty($schoolDetails['School Name'])) {
                $userSchoolData = $this->CM->select_data('System/Schools/' . $schoolDetails['School Name']);
                if ($userSchoolData) {
                    $data['schooll'] = $userSchoolData;
                }
                $data['school_logo_url'] = $this->CM->get_file_url($schoolDetails['School Name'] . '/school_logos/school_logos.jpg');
                $data['holidays_url']    = $this->CM->get_file_url($schoolDetails['School Name'] . '/holidays/holidays');
                $data['academic_url']    = $this->CM->get_file_url($schoolDetails['School Name'] . '/academic/academic');
            } else {
                $data['school_logo_url'] = '';
                $data['holidays_url']    = '';
                $data['academic_url']    = '';
            }

            $this->load->view('include/header');
            $this->load->view('edit_school', $data);
            $this->load->view('include/footer');
        }
    }

    // ── School Profile ────────────────────────────────────────────────────
    public function schoolProfile()
    {
        $school_name = $this->school_name;

        // BUG FIX #8 — cast to array defensively
        $schoolData = $this->firebase->get('System/Schools/' . $school_name);
        if (!$schoolData || !is_array($schoolData)) {
            $schoolData = [];
        }
        $schoolData = (array)$schoolData;

        // Legacy fallback: schools that were not fully migrated to System/Schools
        // still have their data at Users/Schools/{school_name}.
        if (empty($schoolData['School Name'])) {
            $legacy = $this->firebase->get('Users/Schools/' . $school_name);
            if (is_array($legacy) && !empty($legacy)) {
                // Merge legacy data under schoolData — legacy fields fill gaps
                foreach ($legacy as $k => $v) {
                    if (!isset($schoolData[$k]) || (is_string($schoolData[$k]) && $schoolData[$k] === '')) {
                        $schoolData[$k] = $v;
                    }
                }
            }
        }

        // Normalize field name differences between legacy and view expectations
        if (empty($schoolData['School Principal']) && !empty($schoolData['Principal Name'])) {
            $schoolData['School Principal'] = $schoolData['Principal Name'];
        }
        // Legacy stores plan name at subscription.Plan.Name; view expects subscription.planName
        if (empty($schoolData['subscription']['planName']) && !empty($schoolData['subscription']['Plan']['Name'])) {
            $schoolData['subscription']['planName'] = $schoolData['subscription']['Plan']['Name'];
        }

        // ── Merge School Config profile data if present ──────────────────
        // School_config writes to Schools/{school}/Config/Profile with
        // different field names. Map them to the canonical field names so
        // edits made in School Config show up on this profile page.
        $configProfile = $this->firebase->get("Schools/{$school_name}/Config/Profile");
        if (is_array($configProfile) && !empty($configProfile)) {
            $configToCanonical = [
                'display_name'      => 'School Name',
                'address'           => 'Address',
                'phone'             => 'Phone Number',
                'email'             => 'Email',
                'website'           => 'Website',
                'principal_name'    => 'School Principal',
                'affiliation_board' => 'Affiliated To',
                'affiliation_no'    => 'Affiliation Number',
                'logo_url'          => 'Logo',
                'city'              => 'City',
                'state'             => 'State',
                'pincode'           => 'Pincode',
            ];
            foreach ($configToCanonical as $cfgKey => $canonKey) {
                if (!empty($configProfile[$cfgKey]) && empty($schoolData[$canonKey])) {
                    $schoolData[$canonKey] = $configProfile[$cfgKey];
                }
            }
            // Document URLs stored in Config/Profile by document upload feature
            if (!empty($configProfile['holidays_calendar']) && empty($schoolData['Holidays'])) {
                $schoolData['Holidays'] = $configProfile['holidays_calendar'];
            }
            if (!empty($configProfile['academic_calendar']) && empty($schoolData['Academic calendar'])) {
                $schoolData['Academic calendar'] = $configProfile['academic_calendar'];
            }
        }

        $startDate = $schoolData['subscription']['duration']['startDate'] ?? null;
        $endDate   = $schoolData['subscription']['duration']['endDate']   ?? null;

        $startDateTimestamp = $startDate ? strtotime($startDate) : null;
        $endDateTimestamp   = $endDate   ? strtotime($endDate)   : null;

        $daysLeft = null;
        if ($endDateTimestamp) {
            $daysLeft = (int)ceil(($endDateTimestamp - time()) / 86400);
            if ($daysLeft < 0) $daysLeft = 0;
        }

        $data['schoolData'] = $schoolData;
        $data['daysLeft']   = $daysLeft;

        $this->load->view('include/header');
        $this->load->view('schoolprofile', $data);
        $this->load->view('include/footer');
    }

    // ── Manage Schools (Super Admin only — list + add) ─────────────────
    public function manage_school()
    {
        if ($this->admin_role !== 'Super Admin') {
            show_error('Access denied. Super Admin only.', 403);
            return;
        }
        if ($this->input->method() === 'post') {
            $formData           = $this->input->post();
            $normalizedFormData = $this->CM->normalizeKeys($formData);

            if (!isset($normalizedFormData['School Name'])) {
                echo 'School name is missing';
                return;
            }

            $fileUrls     = [];
            $userFileUrls = [];

            if (!empty($_FILES['school_logo']['name'])) {
                $logoUrl              = $this->CM->handleFileUpload($_FILES['school_logo'], $normalizedFormData['School Name'], 'school_logos', 'school_logos', true);
                $fileUrls['Logo']     = $logoUrl ?: 'No logo';
                $userFileUrls['Logo'] = $logoUrl ?: 'No logo';
            }

            if (!empty($_FILES['Holidays']['name'])) {
                $holidaysUrl          = $this->CM->handleFileUpload($_FILES['Holidays'], $normalizedFormData['School Name'], 'holidays', 'holidays', true);
                $fileUrls['Holidays'] = $holidaysUrl;
            }

            if (!empty($_FILES['Academic']['name'])) {
                $academicUrl                   = $this->CM->handleFileUpload($_FILES['Academic'], $normalizedFormData['School Name'], 'academic', 'academic', true);
                $fileUrls['Academic calendar'] = $academicUrl;
            }

            // BUG FIX #3 — normalizeKeys() converts "subscription_plan" → "Subscription Plan"
            // so we must read the normalised title-case keys, not the original underscore keys.
            $subscriptionData = [
                'planName' => $normalizedFormData['Subscription Plan']     ?? '',
                'amount'   => [
                    'totalAmount' => (float)($normalizedFormData['Last Payment Amount'] ?? 0),
                    'monthly'     => (float)($normalizedFormData['Last Payment Amount'] ?? 0)
                                   / max(1, (int)($normalizedFormData['Subscription Duration'] ?? 1))
                ],
                'duration' => [
                    'periodInMonths' => (int)($normalizedFormData['Subscription Duration'] ?? 0),
                    'startDate'      => date('Y-m-d'),
                    'endDate'        => date('Y-m-d', strtotime('+' . (int)($normalizedFormData['Subscription Duration'] ?? 0) . ' months'))
                ],
                'status'   => 'Active',
                // BUG FIX #4 — cast to array in case only one checkbox was ticked
                'features' => (array)($normalizedFormData['Features'] ?? [])
            ];

            $paymentData = [
                'lastPaymentAmount' => $normalizedFormData['Last Payment Amount'] ?? '',
                'lastPaymentDate'   => $normalizedFormData['Last Payment Date']   ?? '',
                'paymentMethod'     => $normalizedFormData['Payment Method']      ?? ''
            ];

            // Remove keys that go into subscription/payment only
            $keysToRemove = [
                'Last Payment Amount', 'Last Payment Date', 'Payment Method',
                'Subscription Duration', 'Subscription Plan', 'Features'
            ];
            foreach ($keysToRemove as $key) {
                unset($normalizedFormData[$key]);
            }

            $schoolName      = $normalizedFormData['School Name'];
            $finalFormData   = array_merge(
                $normalizedFormData,
                $userFileUrls,
                ['subscription' => $subscriptionData],
                ['payment'      => $paymentData]
            );

            $resultUsers = $this->CM->addKey_pair_data('System/Schools/', [$schoolName => $finalFormData]);

            $defaultValues = [
                'Activities' => [
                    '1' => 'https://firebasestorage.googleapis.com/v0/b/graders-1c047.appspot.com/o/Maharishi%20Vidhya%20Mandir%2C%20Balaghat%2Factivities%2Factivity_5.png?alt=media&token=5b97b8b2-ebfd-4cf8-80e6-7066935d9a8f',
                    '2' => 'https://firebasestorage.googleapis.com/v0/b/graders-1c047.appspot.com/o/Maharishi%20Vidhya%20Mandir%2C%20Balaghat%2Factivities%2Factivity_2.jpg?alt=media&token=bfa69104-fc82-4e3b-a65b-97cc6b3fb43e',
                    '3' => 'https://firebasestorage.googleapis.com/v0/b/graders-1c047.appspot.com/o/Maharishi%20Vidhya%20Mandir%2C%20Balaghat%2Factivities%2Factivity_4.jpg?alt=media&token=718a6c9e-ffde-4c05-a591-d30c59348f89',
                    '4' => 'https://firebasestorage.googleapis.com/v0/b/graders-1c047.appspot.com/o/Maharishi%20Vidhya%20Mandir%2C%20Balaghat%2Factivities%2Factivity_3.jpg?alt=media&token=89eeb8d6-b482-40ab-a172-57952e1b2856'
                ],
                'Features'      => ['Assignment' => '', 'Attendance' => '', 'Notification' => '', 'Profile' => '', 'Syllabus' => '', 'Time Table' => ''],
                'Total Classes' => ['Classes Done' => '', 'Total' => '']
            ];

            $schoolDataToInsert = array_merge($defaultValues, $fileUrls);

            $currentYear = date('Y');
            $nextYear    = date('y', strtotime('+1 year'));
            $session_year = "$currentYear-$nextYear";

            $result2 = $this->CM->addKey_pair_data("Schools/$schoolName/$session_year/", $schoolDataToInsert);

            $currentSchoolCount = (int)$this->CM->get_data('Indexes/School_codes/Count');
            $newSchoolId        = 'SCH' . str_pad($currentSchoolCount, 4, '0', STR_PAD_LEFT);
            $result1            = $this->CM->addKey_pair_data('Indexes/School_codes/', [$newSchoolId => $schoolName]);

            if ($resultUsers && $result1 && $result2) {
                $this->CM->addKey_pair_data("Schools/$schoolName/", ['Session' => $session_year]);
                $this->CM->addKey_pair_data('Indexes/School_codes/', ['Count' => $currentSchoolCount + 1]);
                echo '1';
            } else {
                echo '0';
            }

        } else {
            // BUG FIX #5 — guard against null from select_data
            $currentSchoolCount = $this->CM->get_data('Indexes/School_codes/Count');
            $schoolIds          = $this->CM->select_data('Indexes/School_codes') ?? [];
            $schools            = [];

            foreach ($schoolIds as $schoolId => $schoolName) {
                if ($schoolId === 'Count') continue;

                $schoolData = $this->CM->select_data('System/Schools/' . $schoolName);
                if ($schoolData) {
                    $schoolData['School Id']   = $schoolId;
                    $schoolData['School Name'] = $schoolName;
                    $logoPath                  = $schoolName . '/school_logos/school_logos.jpg';
                    $logoUrl                   = $this->CM->get_file_url($logoPath);
                    $schoolData['Logo']        = $logoUrl ?: 'No logo';
                    $schools[]                 = $schoolData;
                }
            }

            $data['Schools']            = $schools;
            $data['currentSchoolCount'] = $currentSchoolCount;

            $this->load->view('include/header');
            $this->load->view('manage_school', $data);
            $this->load->view('include/footer');
        }
    }

    // ── School Gallery ────────────────────────────────────────────────────
    public function schoolgallery()
    {
        $this->load->view('include/header');
        $this->load->view('schoolgallery');
        $this->load->view('include/footer');
    }

    // ── Gallery: fetch event albums ─────────────────────────────────────
    public function fetchGalleryAlbums()
    {
        header('Content-Type: application/json');
        $school_name = $this->school_name;

        // 1. Load all events for album listing
        $events = $this->firebase->get("Schools/$school_name/Events/List") ?? [];
        if (!is_array($events)) $events = [];

        // 2. Load all media (shallow keys per event to get counts)
        $mediaRoot = $this->firebase->get("Schools/$school_name/Events/Media") ?? [];
        if (!is_array($mediaRoot)) $mediaRoot = [];

        $albums = [];
        foreach ($events as $id => $evt) {
            if (!is_array($evt)) continue;
            $eventMedia = isset($mediaRoot[$id]) && is_array($mediaRoot[$id]) ? $mediaRoot[$id] : [];
            $imgCount = 0;
            $vidCount = 0;
            $cover    = '';
            foreach ($eventMedia as $m) {
                if (!is_array($m)) continue;
                if (($m['type'] ?? '') === 'image') { $imgCount++; if (!$cover) $cover = $m['url'] ?? ''; }
                else { $vidCount++; }
            }
            // Use explicit cover if set
            if (!empty($evt['cover_image'])) $cover = $evt['cover_image'];

            $albums[] = [
                'event_id'    => $id,
                'title'       => $evt['title'] ?? $id,
                'category'    => $evt['category'] ?? 'event',
                'start_date'  => $evt['start_date'] ?? '',
                'status'      => $evt['status'] ?? '',
                'cover'       => $cover,
                'image_count' => $imgCount,
                'video_count' => $vidCount,
                'total'       => $imgCount + $vidCount,
            ];
        }

        // 3. Check for legacy gallery and add as "General Gallery"
        $legacyPath = "Schools/$school_name/{$this->session_year}/Gallery";
        $legacy     = $this->firebase->get($legacyPath) ?? [];
        if (is_array($legacy) && !empty($legacy)) {
            $lImg = 0; $lVid = 0; $lCover = '';
            foreach ($legacy as $m) {
                if (!is_array($m) || empty($m['image'])) continue;
                if (($m['type'] ?? '') == '1') { $lImg++; if (!$lCover) $lCover = $m['image']; }
                else { $lVid++; }
            }
            if ($lImg + $lVid > 0) {
                $albums[] = [
                    'event_id'    => '__legacy__',
                    'title'       => 'General Gallery',
                    'category'    => 'general',
                    'start_date'  => '',
                    'status'      => '',
                    'cover'       => $lCover,
                    'image_count' => $lImg,
                    'video_count' => $lVid,
                    'total'       => $lImg + $lVid,
                ];
            }
        }

        // Sort: most recent event first
        usort($albums, function ($a, $b) {
            return strcmp($b['start_date'], $a['start_date']);
        });

        echo json_encode(['albums' => $albums]);
    }

    // ── Gallery: fetch media for a specific event album ─────────────────
    public function fetchAlbumMedia()
    {
        header('Content-Type: application/json');
        $school_name = $this->school_name;
        $eventId     = trim($this->input->get('event_id') ?? '');

        if (empty($eventId)) {
            echo json_encode(['images' => [], 'videos' => []]);
            return;
        }
        if ($eventId !== '__legacy__') {
            $eventId = $this->safe_path_segment($eventId, 'event_id');
        }

        $images = [];
        $videos = [];

        if ($eventId === '__legacy__') {
            // Legacy flat gallery
            $galleryData = $this->firebase->get("Schools/$school_name/{$this->session_year}/Gallery") ?? [];
            if (is_array($galleryData)) {
                foreach ($galleryData as $key => $media) {
                    if (!is_array($media) || empty($media['image'])) continue;
                    $item = [
                        'media_id'  => $key,
                        'url'       => $media['image'],
                        'timestamp' => $media['Time_stamp'] ?? 0,
                    ];
                    if (($media['type'] ?? '') == '1') {
                        $item['type'] = 'image';
                        $images[] = $item;
                    } else {
                        $item['type']      = 'video';
                        $item['thumbnail'] = $media['thumbnail'] ?? '';
                        $item['duration']  = $media['duration'] ?? '';
                        $videos[] = $item;
                    }
                }
            }
        } else {
            // Event-based media
            $mediaData = $this->firebase->get("Schools/$school_name/Events/Media/$eventId") ?? [];
            if (is_array($mediaData)) {
                foreach ($mediaData as $key => $media) {
                    if (!is_array($media) || empty($media['url'])) continue;
                    $item = [
                        'media_id'  => $key,
                        'url'       => $media['url'],
                        'timestamp' => strtotime($media['uploaded_at'] ?? '') ?: 0,
                    ];
                    if (($media['type'] ?? '') === 'image') {
                        $item['type'] = 'image';
                        $images[] = $item;
                    } else {
                        $item['type']      = 'video';
                        $item['thumbnail'] = $media['thumbnail'] ?? '';
                        $item['duration']  = $media['duration'] ?? '';
                        $videos[] = $item;
                    }
                }
            }
        }

        usort($images, fn($a, $b) => ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0));
        usort($videos, fn($a, $b) => ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0));

        echo json_encode(['images' => $images, 'videos' => $videos]);
    }

    // ── Gallery: delete media ───────────────────────────────────────────
    public function deleteMedia()
    {
        header('Content-Type: application/json');

        $school_name = $this->school_name;
        $fileUrl     = $this->input->get('url');
        $eventId     = trim($this->input->get('event_id') ?? '');
        $mediaId     = trim($this->input->get('media_id') ?? '');

        if ($eventId !== '') $eventId = $this->safe_path_segment($eventId, 'event_id');
        if ($mediaId !== '') $mediaId = $this->safe_path_segment($mediaId, 'media_id');

        if (!$fileUrl) {
            echo json_encode(['status' => 'error', 'message' => 'File URL is required']);
            return;
        }

        try {
            $filePath = $this->extract_firebase_storage_path($fileUrl);
            if (!$filePath) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file path']);
                return;
            }

            $this->CM->delete_file_from_firebase($filePath);

            if ($eventId === '__legacy__') {
                // Legacy gallery — scan by URL match
                $galleryRef  = "Schools/$school_name/{$this->session_year}/Gallery";
                $galleryData = $this->firebase->get($galleryRef) ?? [];
                if (is_array($galleryData)) {
                    foreach ($galleryData as $key => $media) {
                        if (isset($media['image']) && trim($media['image']) === trim($fileUrl)) {
                            if (!empty($media['thumbnail'])) {
                                $thumbPath = $this->extract_firebase_storage_path($media['thumbnail']);
                                if ($thumbPath) $this->CM->delete_file_from_firebase($thumbPath);
                            }
                            $this->firebase->delete("$galleryRef/$key");
                            break;
                        }
                    }
                }
            } else {
                // Event media — direct path delete
                $mediaPath = "Schools/$school_name/Events/Media/$eventId/$mediaId";
                $existing  = $this->firebase->get($mediaPath);
                if (is_array($existing) && !empty($existing['thumbnail'])) {
                    $thumbPath = $this->extract_firebase_storage_path($existing['thumbnail']);
                    if ($thumbPath) $this->CM->delete_file_from_firebase($thumbPath);
                }
                $this->firebase->delete("Schools/$school_name/Events/Media/$eventId", $mediaId);
            }

            echo json_encode(['status' => 'success', 'message' => 'File deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Gallery: upload media to event album ────────────────────────────
    public function uploadMedia()
    {
        header('Content-Type: application/json');

        $school_name = $this->school_name;
        $eventId     = trim($this->input->post('event_id') ?? '');

        if (empty($eventId)) {
            echo json_encode(['status' => 'error', 'message' => 'Event ID is required']);
            return;
        }
        $eventId = $this->safe_path_segment($eventId, 'event_id');
        if (!isset($_FILES['file'])) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
            return;
        }

        $file          = $_FILES['file'];
        $fileName      = $file['name'];
        $fileTmpPath   = $file['tmp_name'];
        $fileSize      = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileType      = $this->input->post('type');

        $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedVideoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
        $maxImageSize           = 5  * 1024 * 1024;
        $maxVideoSize           = 50 * 1024 * 1024;

        if ($fileType == '1' && (!in_array($fileExtension, $allowedImageExtensions) || $fileSize > $maxImageSize)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image format or size exceeded (max 5MB)']);
            return;
        }
        if ($fileType == '2' && (!in_array($fileExtension, $allowedVideoExtensions) || $fileSize > $maxVideoSize)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid video format or size exceeded (max 50MB)']);
            return;
        }

        $timestamp    = time();
        $randomString = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        $safeEvent    = preg_replace('/[^A-Za-z0-9_\-]/', '_', $eventId);
        $storagePath  = "$school_name/Events/Media/$safeEvent/";
        $prefix       = ($fileType == '1') ? 'img_' : 'vid_';
        $newFileName  = "{$prefix}{$timestamp}_{$randomString}.{$fileExtension}";
        $firebasePath = $storagePath . $newFileName;

        $uploadResult = $this->firebase->uploadFile($fileTmpPath, $firebasePath);
        if ($uploadResult !== true) {
            echo json_encode(['status' => 'error', 'message' => $uploadResult]);
            return;
        }

        $downloadUrl = $this->firebase->getDownloadUrl($firebasePath);
        $mediaId     = "{$prefix}{$timestamp}_{$randomString}";
        $mediaData   = [
            'media_id'    => $mediaId,
            'type'        => ($fileType == '1') ? 'image' : 'video',
            'url'         => $downloadUrl,
            'uploaded_at' => date('c'),
            'uploaded_by' => $this->admin_id,
        ];

        if ($fileType == '2') {
            $ffmpeg  = defined('FFMPEG_PATH')  ? FFMPEG_PATH  : 'ffmpeg';
            $ffprobe = defined('FFPROBE_PATH') ? FFPROBE_PATH : 'ffprobe';

            $durationCmd    = "\"$ffprobe\" -v error -select_streams v:0 -show_entries stream=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fileTmpPath);
            $durationOutput = shell_exec($durationCmd);
            $durationSecs   = is_numeric(trim($durationOutput ?? '')) ? round((float)trim($durationOutput), 2) : 0;
            $minutes        = (int)floor($durationSecs / 60);
            $seconds        = (int)round($durationSecs - ($minutes * 60));
            if ($seconds === 60) { $minutes++; $seconds = 0; }
            $mediaData['duration'] = sprintf('%d:%02d', $minutes, $seconds);

            $thumbName  = "thumb_{$timestamp}_{$randomString}.jpg";
            $thumbLocal = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $thumbName;
            $thumbCmd   = "\"$ffmpeg\" -i " . escapeshellarg($fileTmpPath) . " -ss 00:00:01.000 -vframes 1 -q:v 2 " . escapeshellarg($thumbLocal);
            shell_exec($thumbCmd);

            if (file_exists($thumbLocal)) {
                $thumbStoragePath       = $storagePath . "thumbnails/" . $thumbName;
                $this->firebase->uploadFile($thumbLocal, $thumbStoragePath);
                $mediaData['thumbnail'] = $this->firebase->getDownloadUrl($thumbStoragePath);
                @unlink($thumbLocal);
            }
        }

        $dbPath = "Schools/$school_name/Events/Media/$eventId";
        $this->firebase->update($dbPath, [$mediaId => $mediaData]);

        echo json_encode([
            'status'    => 'success',
            'message'   => 'File uploaded successfully',
            'mediaData' => $mediaData,
        ]);
    }

    // ── Gallery: set event cover image ──────────────────────────────────
    public function setEventCover()
    {
        header('Content-Type: application/json');
        $school_name = $this->school_name;
        $eventId     = trim($this->input->post('event_id') ?? '');
        $coverUrl    = $this->input->post('cover_url');

        if (empty($eventId) || empty($coverUrl)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing event ID or cover URL']);
            return;
        }
        $eventId = $this->safe_path_segment($eventId, 'event_id');

        $this->firebase->update("Schools/$school_name/Events/List/$eventId", [
            'cover_image' => $coverUrl,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Cover image set successfully']);
    }

    // ── Private helpers ───────────────────────────────────────────────────
    private function extract_firebase_storage_path($url)
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['path'])) return null;

        $pos = strpos($parsedUrl['path'], '/o/');
        if ($pos === false) return null;

        return urldecode(substr($parsedUrl['path'], $pos + 3));
    }
}