<?php

class NoticeAnnouncement extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $path    = 'Schools/' . $school_name . '/' . $session_year . '/All Notices';
        $notices = $this->firebase->get($path);

        $data['notices'] = is_array($notices) ? $notices : [];
        $this->load->view('notice_announcement/list', $data);
    }

    // ── Fetch recent notices (called by header bell via AJAX) ─────
    public function fetch_recent_notices()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getRecentNotices(5));
    }

    // ── Private helper ────────────────────────────────────────────
    private function getRecentNotices($limit = 5)
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $path    = 'Schools/' . $school_name . '/' . $session_year . '/All Notices';
        $notices = $this->firebase->get($path);

        // Guard: Firebase returned null or non-array
        if (empty($notices) || !is_array($notices)) return [];

        // Remove the Count key — it is not a notice
        unset($notices['Count']);

        $noticeList = [];
        foreach ($notices as $id => $notice) {
            if (!is_array($notice)) continue;

            // FIX: accept BOTH 'Timestamp' (what we save) and 'Time_Stamp' (legacy)
            $ts = $notice['Timestamp'] ?? $notice['Time_Stamp'] ?? null;
            if ($ts === null) continue;

            $notice['id']         = $id;
            $notice['Time_Stamp'] = $ts;   // normalise key for sorting
            $noticeList[]         = $notice;
        }

        usort($noticeList, fn($a, $b) => $b['Time_Stamp'] <=> $a['Time_Stamp']);

        return array_slice($noticeList, 0, $limit);
    }

    // ── Search users ──────────────────────────────────────────────
    public function search_users()
    {
        header('Content-Type: application/json');

        $query        = strtolower(trim($this->input->get('query') ?? ''));
        $school_name  = $this->school_name;
        $session_year = $this->session_year;
        $results      = [];

        // Admins
        $adminsData = $this->firebase->get("Schools/$school_name/$session_year/Admins");
        if (is_array($adminsData)) {
            foreach ($adminsData as $adminId => $admin) {
                if (!is_array($admin)) continue;
                $name = $admin['Name'] ?? '';
                if (stripos($name, $query) !== false || stripos((string)$adminId, $query) !== false) {
                    $results[] = ['label' => "$name ($adminId)", 'type' => 'Admin',   'id' => $adminId, 'name' => $name];
                }
            }
        }

        // Teachers
        $teachersData = $this->firebase->get("Schools/$school_name/$session_year/Teachers");
        if (is_array($teachersData)) {
            foreach ($teachersData as $teacherId => $teacher) {
                if (!is_array($teacher)) continue;
                $name = $teacher['Name'] ?? '';
                if (stripos($name, $query) !== false || stripos((string)$teacherId, $query) !== false) {
                    $results[] = ['label' => "$name ($teacherId)", 'type' => 'Teacher', 'id' => $teacherId, 'name' => $name];
                }
            }
        }

        // Students — new path: Class 8th / Section A / Students / List
        // We iterate Classes node to find class+section combos
        $schoolData = $this->firebase->get("Schools/$school_name/$session_year");
        if (is_array($schoolData)) {
            foreach ($schoolData as $classKey => $classData) {
                if (!is_array($classData) || stripos($classKey, 'Class ') !== 0) continue;

                // New structure: classData has section keys like "Section A"
                foreach ($classData as $sectionKey => $sectionData) {
                    if (!is_array($sectionData) || stripos($sectionKey, 'Section ') !== 0) continue;

                    $studentList = $sectionData['Students']['List'] ?? null;
                    if (!is_array($studentList)) continue;

                    // Display label uses "Class 8th / Section A" format
                    $classLabel = "$classKey / $sectionKey";

                    foreach ($studentList as $studentId => $studentName) {
                        if (stripos((string)$studentName, $query) !== false ||
                            stripos((string)$studentId,   $query) !== false) {
                            $results[] = [
                                'label' => "$studentName ($studentId) [$classKey|$sectionKey]",
                                'type'  => 'Student',
                                'id'    => $studentId,
                                'name'  => $studentName,
                                'class' => $classLabel,           // display
                                'class_key'   => $classKey,       // "Class 8th"
                                'section_key' => $sectionKey,     // "Section A"
                            ];
                        }
                    }
                }
            }
        }

        echo json_encode($results);
    }

    // ── Create notice ─────────────────────────────────────────────
    public function create_notice()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;
        $admin_id     = $this->admin_id;

        $base_path   = "Schools/{$school_name}/{$session_year}/All Notices";
        $classesPath = "Schools/{$school_name}/{$session_year}/Classes";
        $classesData = $this->firebase->get($classesPath);

        // ── Build class list for dropdown ─────────────────────────
        // FIX: guard against null return from Firebase
        $data['classes'] = [];
        if (is_array($classesData)) {
            foreach ($classesData as $className => $classDetails) {
                if (!is_array($classDetails)) continue;

                // New structure: sections are separate keys under class
                // Firebase: Classes / 8th / Section / A  (old)
                // OR:       Class 8th / Section A / ...  (new)
                // Support BOTH so the dropdown always populates.

                if (isset($classDetails['Section']) && is_array($classDetails['Section'])) {
                    // OLD structure — Classes/8th/Section/A
                    foreach ($classDetails['Section'] as $sectionName => $sectionDetails) {
                        $classNode    = "Class $className";
                        $sectionNode  = "Section $sectionName";
                        $displayLabel = "$classNode / $sectionNode";
                        $data['classes']["$classNode|$sectionNode"] = $displayLabel;
                    }
                } else {
                    // NEW structure — classes stored with full names like "Class 8th"
                    // and sections as child keys "Section A"
                    foreach ($classDetails as $subKey => $subVal) {
                        if (stripos($subKey, 'Section ') === 0 && is_array($subVal)) {
                            $classNode    = $className;   // already "Class 8th"
                            $sectionNode  = $subKey;      // "Section A"
                            $displayLabel = "$classNode / $sectionNode";
                            $data['classes']["$classNode|$sectionNode"] = $displayLabel;
                        }
                    }
                }
            }
        }

        // ── POST handler ──────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title       = trim($_POST['title']       ?? '');
            $description = trim($_POST['description'] ?? '');
            $to_ids      = [];

            if (!empty($this->input->post('to_id_json'))) {
                $to_ids = json_decode($this->input->post('to_id_json'), true) ?? [];
            }

            if (empty($to_ids)) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'No recipients selected.']));
                return;
            }

            // Create notice node
            $current_data  = $this->firebase->get($base_path);
            $current_count = is_array($current_data) && isset($current_data['Count'])
                ? (int)$current_data['Count'] : 0;
            $notice_id = 'NOT' . str_pad($current_count, 4, '0', STR_PAD_LEFT);

            $new_notice = [
                'Title'       => $title,
                'Description' => $description,
                'From Id'     => $admin_id,
                'From Type'   => 'Admin',
                'Timestamp'   => [".sv" => "timestamp"],
                'To Id'       => [],
            ];
            $this->firebase->set("{$base_path}/{$notice_id}", $new_notice);
            $this->firebase->set("{$base_path}/Count", $current_count + 1);

            // Wait for Firebase server timestamp to resolve
            usleep(500000);

            $stored_notice   = $this->firebase->get("{$base_path}/{$notice_id}");
            $actualTimestamp = (is_array($stored_notice) && isset($stored_notice['Timestamp']))
                ? $stored_notice['Timestamp']
                : round(microtime(true) * 1000);

            $sanitized_to_ids = [];

            foreach ($to_ids as $key => $label) {
                log_message('debug', "create_notice: key=$key label=$label");

                // ── Class|Section format (new dropdown) ───────────
                // Key like: "Class 8th|Section A"
                if (strpos($key, '|') !== false && !preg_match('/^(STU|TEA|ADM)/', $key)) {
                    $parts      = explode('|', $key, 2);
                    $classNode  = trim($parts[0]);   // "Class 8th"
                    $sectionNode = trim($parts[1]);  // "Section A"

                    // New Firebase path: Class 8th / Section A / Notification / NOT0000
                    $classPath = "Schools/{$school_name}/{$session_year}/{$classNode}/{$sectionNode}/Notification/{$notice_id}";
                    $this->firebase->set($classPath, $actualTimestamp);
                    $sanitized_to_ids[$key] = "";
                    log_message('debug', "create_notice: class path=$classPath");
                }

                // ── Old merged class format (legacy fallback) ─────
                // Key like: "Class 8th 'A'"  (kept in case old data flows through)
                elseif (preg_match("/^Class\s+.+\s+'.+'$/", $key)) {
                    $parts         = explode(" '", $key);
                    $classOnlyRaw  = trim($parts[0]);
                    $sectionOnly   = rtrim($parts[1] ?? '', "'");
                    $classNode     = $classOnlyRaw;           // "Class 8th"
                    $sectionNode   = "Section $sectionOnly";  // "Section A"

                    $classPath = "Schools/{$school_name}/{$session_year}/{$classNode}/{$sectionNode}/Notification/{$notice_id}";
                    $this->firebase->set($classPath, $actualTimestamp);
                    $sanitized_to_ids[$key] = "";
                }

                // ── Student ───────────────────────────────────────
                elseif (preg_match('/^STU[0-9]+$/', $key)) {
                    // Label format: "Name (STU0005) [Class 8th|Section A]"
                    if (preg_match('/\[(.*?)\|(.*?)\]/', $label, $m)) {
                        $classNode   = trim($m[1]);  // "Class 8th"
                        $sectionNode = trim($m[2]);  // "Section A"
                        $studentPath = "Schools/{$school_name}/{$session_year}/{$classNode}/{$sectionNode}/Students/{$key}/Notification/{$notice_id}";
                        $this->firebase->set($studentPath, $actualTimestamp);
                        log_message('debug', "create_notice: student path=$studentPath");
                    } else {
                        log_message('error', "create_notice: cannot parse class from label: $label");
                    }
                    $sanitized_to_ids[$key] = "";
                }

                // ── Teacher ───────────────────────────────────────
                elseif (preg_match('/^TEA[0-9]+$/', $key)) {
                    if ($key !== $admin_id) {
                        $this->firebase->set(
                            "Schools/{$school_name}/{$session_year}/Teachers/{$key}/Received/{$notice_id}",
                            $actualTimestamp
                        );
                    }
                    $sanitized_to_ids[$key] = "";
                }

                // ── Admin ─────────────────────────────────────────
                elseif (preg_match('/^ADM[0-9]+$/', $key)) {
                    if ($key !== $admin_id) {
                        $this->firebase->set(
                            "Schools/{$school_name}/{$session_year}/Admins/{$key}/Received/{$notice_id}",
                            $actualTimestamp
                        );
                    }
                    $sanitized_to_ids[$key] = "";
                }

                // ── Bulk / Announcement ───────────────────────────
                else {
                    $safe_key         = str_replace(['.','#','$','[',']','/','\''], '_', $key);
                    $announcementPath = "Schools/{$school_name}/{$session_year}/Announcements/{$safe_key}/{$notice_id}";
                    $this->firebase->set($announcementPath, $actualTimestamp);
                    $sanitized_to_ids[$safe_key] = "";
                    log_message('debug', "create_notice: announcement path=$announcementPath");
                }
            }

            // Sender's Sent log
            $this->firebase->set(
                "Schools/{$school_name}/{$session_year}/Admins/{$admin_id}/Sent/{$notice_id}",
                $actualTimestamp
            );

            // Update notice with final To Id + resolved timestamp
            $this->firebase->update("{$base_path}/{$notice_id}", [
                'To Id'     => $sanitized_to_ids,
                'Timestamp' => $actualTimestamp,
            ]);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Notice sent successfully.']));

        } else {
            // GET — show the form
            $notices          = $this->firebase->get($base_path);
            $data['notices']  = is_array($notices) ? $notices : [];
            $this->load->view('include/header');
            $this->load->view('create_notice', $data);
            $this->load->view('include/footer');
        }
    }

    // ── Delete notice ─────────────────────────────────────────────
    public function delete($id)
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;
        $path = 'Schools/' . $school_name . '/' . $session_year . '/All Notices/' . $id;
        $this->firebase->set($path, null);   // FIX: was using $this->firebase_db which doesn't exist
        redirect('NoticeAnnouncement');
    }
}