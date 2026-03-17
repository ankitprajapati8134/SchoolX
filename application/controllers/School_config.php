<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * School_config — Core School Configuration
 *
 * Manages all foundational school configuration:
 *   - School profile (display name, contact, logo, affiliation)
 *   - Academic sessions (list, add, set active)
 *   - Board configuration (type, grading pattern, grade scale)
 *   - Master class list (ordinal + foundational classes) with soft-delete
 *   - Sections per class per session
 *   - Subject assignments per class
 *   - Stream configuration (for Classes 11-12)
 *
 * Firebase paths:
 *   Schools/{school}/Config/Profile/          — school profile fields
 *   Schools/{school}/Config/Board/            — board + grading config
 *   Schools/{school}/Config/Classes/          — master class list array
 *   Schools/{school}/Config/Streams/          — stream definitions
 *   Schools/{school}/Config/ActiveSession     — active session string
 *   Schools/{school}/Sessions                 — session list (existing)
 *   Schools/{school}/Subject_list/{key}/      — subjects (existing)
 *   Schools/{school}/{session}/Class {n}/     — class nodes (existing)
 *   System/Schools/{school_id}/profile        — onboarding profile (canonical)
 */
class School_config extends MY_Controller
{
    /** Only Admin/Principal may configure school settings */
    private const ADMIN_ROLES = ['Admin', 'Principal'];

    public function __construct()
    {
        parent::__construct();
        require_permission('Configuration');
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_index');
        $this->load->view('include/header');
        $this->load->view('school_config/index');
        $this->load->view('include/footer');
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/get_config
    // Returns all config sections in one shot for initial page load
    // ─────────────────────────────────────────────────────────────────────
    public function get_config()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_get_config');
        $school    = $this->school_name;
        $school_id = $this->school_id;

        $profile  = $this->firebase->get("Schools/{$school}/Config/Profile") ?? [];
        $board    = $this->firebase->get("Schools/{$school}/Config/Board")   ?? [];
        $classes  = $this->firebase->get("Schools/{$school}/Config/Classes") ?? [];
        $streams  = $this->firebase->get("Schools/{$school}/Config/Streams") ?? [];
        $sessions = $this->firebase->get("Schools/{$school}/Sessions")       ?? [];
        $activeSess = $this->firebase->get("Schools/{$school}/Config/ActiveSession") ?? $this->session_year;

        // ── Issue 1: Profile fallback to onboarding data ────────────────
        // Onboarding writes profile to System/Schools/{school}/profile.
        // If Config/Profile is empty or missing key fields, merge onboarding
        // data so the form is pre-filled on first open.
        if (!is_array($profile)) $profile = [];

        // Fallback: if Config/Profile is empty, pull from canonical
        // System/Schools/{school} (written by manage_school/edit_school)
        if (empty($profile['display_name'])) {
            $canonical = $this->firebase->get("System/Schools/{$school}") ?? [];
            if (is_array($canonical) && !empty($canonical)) {
                $fieldMap = [
                    'School Name'        => 'display_name',
                    'City'               => 'city',
                    'Address'            => 'address',
                    'Email'              => 'email',
                    'Phone Number'       => 'phone',
                    'Logo'               => 'logo_url',
                    'State'              => 'state',
                    'Pincode'            => 'pincode',
                    'Website'            => 'website',
                    'School Principal'   => 'principal_name',
                    'Affiliated To'      => 'affiliation_board',
                    'Affiliation Number' => 'affiliation_no',
                    'Mobile Number'      => 'phone',
                ];
                foreach ($fieldMap as $srcKey => $destKey) {
                    if (empty($profile[$destKey]) && !empty($canonical[$srcKey])) {
                        $profile[$destKey] = $canonical[$srcKey];
                    }
                }
            }
        }

        // Normalise classes to plain array, filter soft-deleted for UI
        $classes = is_array($classes) ? array_values($classes) : [];

        // If Config/Classes is empty, enumerate live classes from Firebase so that
        // schools with existing data (before School_config was introduced) can still
        // use the Sections and Subjects tabs without manually seeding the class list.
        if (empty($classes)) {
            $sessionRoot = $this->firebase->shallow_get("Schools/{$school}/{$this->session_year}");
            $order       = 0;
            foreach ($sessionRoot as $nodeKey) {
                if (strpos($nodeKey, 'Class ') !== 0) continue;
                $raw     = trim(str_replace('Class ', '', $nodeKey)); // "9th", "LKG", "Nursery"
                $lower   = strtolower($raw);
                $isFound = in_array($lower, ['nursery', 'lkg', 'ukg', 'playgroup'], true);
                $key     = $isFound ? strtoupper($raw) : (string) (int) preg_replace('/\D/', '', $raw);
                if ($key === '' || $key === '0') continue;
                $classes[] = [
                    'key'             => $key,
                    'label'           => $nodeKey,
                    'type'            => $isFound ? 'foundational' : 'primary',
                    'order'           => $order++,
                    'streams_enabled' => in_array($key, ['11', '12'], true),
                    'deleted'         => false,
                ];
            }
        }

        // Normalise sessions to plain array.
        $sessions = is_array($sessions)
            ? array_values(array_filter($sessions, 'is_string'))
            : [];

        // ── Sync PHP session cache to match Firebase ──────────────────────
        if (!empty($sessions)) {
            $this->session->set_userdata('available_sessions', $sessions);
        }

        $this->json_success([
            'profile'        => is_array($profile)  ? $profile  : [],
            'board'          => is_array($board)     ? $board    : [],
            'classes'        => $classes,
            'streams'        => is_array($streams)   ? $streams  : [],
            'sessions'       => $sessions,
            'active_session' => (string) $activeSess,
            'firebase_path'  => "Schools/{$school}/Sessions",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_profile
    // ─────────────────────────────────────────────────────────────────────
    public function save_profile()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_profile');
        $school    = $this->school_name;
        $school_id = $this->school_id;

        $allowed = [
            'display_name', 'address', 'city', 'state', 'pincode',
            'phone', 'email', 'website', 'principal_name',
            'affiliation_board', 'affiliation_no', 'established_year',
        ];

        $data = [];
        foreach ($allowed as $field) {
            $val = trim((string) $this->input->post($field, TRUE));
            if ($val !== '') {
                $data[$field] = $val;
            }
        }

        if (empty($data)) {
            return $this->json_error('No data provided.');
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json_error('Invalid email address.');
        }

        if (!empty($data['phone']) && !preg_match('/^[\d\s\+\-\(\)]+$/', $data['phone'])) {
            return $this->json_error('Invalid phone number.');
        }

        if (!empty($data['established_year'])) {
            $yr = (int) $data['established_year'];
            if ($yr < 1800 || $yr > (int) date('Y')) {
                return $this->json_error('Invalid established year.');
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $ok = $this->firebase->update("Schools/{$school}/Config/Profile", $data);
        if (!$ok) {
            return $this->json_error('Failed to save profile. Please try again.');
        }

        // ── Dual-write to System/Schools/{school}/profile (canonical profile) ──
        // schoolProfile() and manage_school list read from this path using
        // title-case field names like "Address", "Phone Number", etc.
        $canonicalMap = [
            'display_name'     => 'School Name',
            'address'          => 'Address',
            'phone'            => 'Phone Number',
            'email'            => 'Email',
            'website'          => 'Website',
            'principal_name'   => 'School Principal',
            'affiliation_board'=> 'Affiliated To',
            'affiliation_no'   => 'Affiliation Number',
            'city'             => 'City',
            'state'            => 'State',
            'pincode'          => 'Pincode',
        ];
        $canonicalData = [];
        foreach ($canonicalMap as $configKey => $profileKey) {
            if (!empty($data[$configKey])) {
                $canonicalData[$profileKey] = $data[$configKey];
            }
        }
        if (!empty($canonicalData)) {
            $this->firebase->update("System/Schools/{$school}/profile", $canonicalData);
        }

        log_audit('Configuration', 'save_profile', $school, 'Updated school profile');

        $this->json_success(['message' => 'Profile saved successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/upload_logo
    // ─────────────────────────────────────────────────────────────────────
    public function upload_logo()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_upload_logo');
        $school    = $this->school_name;
        $school_id = $this->school_id;

        if (empty($_FILES['logo']['name'])) {
            return $this->json_error('No file uploaded.');
        }

        $tempDir = APPPATH . 'temp/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $config = [
            'upload_path'   => $tempDir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('logo')) {
            return $this->json_error($this->upload->display_errors('', ''));
        }

        $info       = $this->upload->data();
        $localPath  = $info['full_path'];
        $safe       = preg_replace('/[^A-Za-z0-9_\-]/', '_', $school);
        $remotePath = "schools/{$safe}/logo/" . $info['file_name'];

        $uploaded = $this->firebase->uploadFile($localPath, $remotePath);
        @unlink($localPath);

        if (!$uploaded) {
            return $this->json_error('Failed to upload logo to storage.');
        }

        $url = $this->firebase->getDownloadUrl($remotePath);
        $this->firebase->update("Schools/{$school}/Config/Profile", [
            'logo_url'        => $url,
            'logo_updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Dual-write logo to canonical profile (System/Schools/{school}/profile)
        $this->firebase->update("System/Schools/{$school}/profile", [
            'logo_url' => $url,
        ]);

        $this->json_success(['logo_url' => $url, 'message' => 'Logo uploaded successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/upload_document
    // Uploads Holidays Calendar or Academic Calendar to Firebase Storage
    // and stores the URL at Config/Profile/{holidays_calendar|academic_calendar}
    // ─────────────────────────────────────────────────────────────────────
    public function upload_document()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_upload_document');
        $school = $this->school_name;
        $type   = trim((string) $this->input->post('doc_type', TRUE));

        $allowed = ['holidays_calendar', 'academic_calendar'];
        if (!in_array($type, $allowed, true)) {
            return $this->json_error('Invalid document type.');
        }

        if (empty($_FILES['document']['name'])) {
            return $this->json_error('No file uploaded.');
        }

        $tempDir = APPPATH . 'temp/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $config = [
            'upload_path'   => $tempDir,
            'allowed_types' => 'pdf|jpg|jpeg|png|gif|webp|doc|docx',
            'max_size'      => 5120, // 5 MB
            'encrypt_name'  => TRUE,
        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('document')) {
            return $this->json_error($this->upload->display_errors('', ''));
        }

        $info       = $this->upload->data();
        $localPath  = $info['full_path'];
        $safe       = preg_replace('/[^A-Za-z0-9_\-]/', '_', $school);
        $folder     = $type === 'holidays_calendar' ? 'holidays' : 'academic';
        $remotePath = "schools/{$safe}/{$folder}/" . $info['file_name'];

        $uploaded = $this->firebase->uploadFile($localPath, $remotePath);
        @unlink($localPath);

        if (!$uploaded) {
            return $this->json_error('Failed to upload document to storage.');
        }

        $url = $this->firebase->getDownloadUrl($remotePath);

        // Write to Config/Profile
        $this->firebase->update("Schools/{$school}/Config/Profile", [
            $type        => $url,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Dual-write to System/Schools canonical path
        $canonKey = $type === 'holidays_calendar' ? 'Holidays' : 'Academic calendar';
        $this->firebase->update("System/Schools/{$school}/profile", [
            $canonKey => $url,
        ]);

        $label = $type === 'holidays_calendar' ? 'Holidays Calendar' : 'Academic Calendar';
        $this->json_success(['url' => $url, 'message' => "{$label} uploaded successfully."]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_board
    // ─────────────────────────────────────────────────────────────────────
    public function save_board()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_board');
        $school = $this->school_name;

        $type            = trim((string) $this->input->post('type', TRUE));
        $customBoardName = trim((string) $this->input->post('custom_board_name', TRUE));
        $gradingPattern  = trim((string) $this->input->post('grading_pattern', TRUE));
        $passingMarks    = (int) $this->input->post('passing_marks');
        $gradeScaleRaw   = (string) $this->input->post('grade_scale');

        // Issue 3: Added IB to valid types
        $validTypes    = ['CBSE', 'ICSE', 'State', 'IB', 'Custom'];
        $validPatterns = ['marks', 'grades', 'cgpa'];

        if (!in_array($type, $validTypes, true)) {
            return $this->json_error('Invalid board type. Allowed: ' . implode(', ', $validTypes));
        }

        if (!in_array($gradingPattern, $validPatterns, true)) {
            return $this->json_error('Invalid grading pattern. Allowed: ' . implode(', ', $validPatterns));
        }

        $data = [
            'type'            => $type,
            'grading_pattern' => $gradingPattern,
            'passing_marks'   => max(0, min(100, $passingMarks)),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // Issue 3: Save custom name for State, IB, and Custom types
        if (in_array($type, ['State', 'IB', 'Custom'], true) && $customBoardName !== '') {
            $data['custom_board_name'] = $customBoardName;
        }

        // Grade scale — only for grades / cgpa patterns
        if ($gradingPattern !== 'marks' && $gradeScaleRaw !== '') {
            $gradeScale = json_decode($gradeScaleRaw, true);
            if (is_array($gradeScale)) {
                $clean = [];
                foreach ($gradeScale as $entry) {
                    $grade  = trim((string) ($entry['grade']   ?? ''));
                    $minPct = (float)              ($entry['min_pct'] ?? 0);
                    $maxPct = (float)              ($entry['max_pct'] ?? 100);
                    if ($grade !== '' && $minPct >= 0 && $maxPct <= 100 && $minPct < $maxPct) {
                        $clean[] = ['grade' => $grade, 'min_pct' => $minPct, 'max_pct' => $maxPct];
                    }
                }
                if (!empty($clean)) {
                    $data['grade_scale'] = $clean;
                }
            }
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/Board", $data);
        if (!$ok) {
            return $this->json_error('Failed to save board configuration.');
        }

        $this->json_success(['message' => 'Board configuration saved.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_classes
    // Saves the complete master class list (with soft-delete support)
    // ─────────────────────────────────────────────────────────────────────
    public function save_classes()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_classes');
        $school     = $this->school_name;
        $rawClasses = json_decode($this->input->post('classes') ?? '[]', true);

        if (!is_array($rawClasses) || empty($rawClasses)) {
            return $this->json_error('No classes provided.');
        }

        $validTypes = ['foundational', 'primary', 'middle', 'secondary', 'senior'];
        $clean      = [];

        foreach ($rawClasses as $i => $cls) {
            $key   = trim((string) ($cls['key']   ?? ''));
            $label = trim((string) ($cls['label'] ?? ''));
            $type  = trim((string) ($cls['type']  ?? 'primary'));
            $order = (int) ($cls['order'] ?? $i);

            if ($key === '' || $label === '') {
                continue;
            }

            if (!preg_match('/^[A-Za-z0-9_]+$/', $key)) {
                continue;
            }

            if (!in_array($type, $validTypes, true)) {
                $type = 'primary';
            }

            // Issue 7: Preserve soft-delete flag
            $clean[] = [
                'key'             => $key,
                'label'           => $label,
                'type'            => $type,
                'order'           => $order,
                'streams_enabled' => !empty($cls['streams_enabled']),
                'deleted'         => !empty($cls['deleted']),
            ];
        }

        if (empty($clean)) {
            return $this->json_error('No valid classes found in request.');
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/Classes", $clean);
        if (!$ok) {
            return $this->json_error('Failed to save class list.');
        }

        $this->json_success(['message' => 'Class list saved.', 'count' => count($clean)]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/activate_classes
    // Issue 5: Creates class nodes in the active session for saved classes
    // ─────────────────────────────────────────────────────────────────────
    public function activate_classes()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_activate_classes');
        $school      = $this->school_name;
        $sessionYear = trim((string) $this->input->post('session', TRUE));

        if ($sessionYear === '') {
            $sessionYear = $this->session_year;
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $sessionYear)) {
            return $this->json_error('Invalid session format.');
        }

        $classes = $this->firebase->get("Schools/{$school}/Config/Classes") ?? [];
        if (!is_array($classes) || empty($classes)) {
            return $this->json_error('No classes configured. Save class list first.');
        }

        $created = 0;
        $skipped = 0;

        foreach ($classes as $cls) {
            if (!is_array($cls)) continue;
            // Skip soft-deleted classes
            if (!empty($cls['deleted'])) continue;

            $key       = $cls['key'] ?? '';
            if ($key === '') continue;

            $classNode = $this->_class_node_name($key);
            $path      = "Schools/{$school}/{$sessionYear}/{$classNode}";

            // Only create if doesn't already exist (avoid overwriting student data)
            if ($this->firebase->exists($path)) {
                $skipped++;
                continue;
            }

            $ok = $this->firebase->set($path, [
                'created_at'  => date('Y-m-d H:i:s'),
                'created_by'  => 'School_config',
            ]);
            if ($ok) $created++;
        }

        $this->json_success([
            'message' => "{$created} class(es) activated in {$sessionYear}. {$skipped} already existed.",
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/soft_delete_class
    // Issue 7: Soft-delete a class (set deleted flag, don't remove data)
    // ─────────────────────────────────────────────────────────────────────
    public function soft_delete_class()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_soft_delete_class');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        $classes = $this->firebase->get("Schools/{$school}/Config/Classes") ?? [];
        if (!is_array($classes)) {
            return $this->json_error('No classes configured.');
        }

        $found = false;
        foreach ($classes as &$cls) {
            if (is_array($cls) && ($cls['key'] ?? '') === $classKey) {
                $cls['deleted']    = true;
                $cls['deleted_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($cls);

        if (!$found) {
            return $this->json_error('Class not found.');
        }

        // Bug #43 FIX: Check for enrolled students before soft-deleting.
        // Scan all sections under the class node in the active session.
        $classNode  = $this->_class_node_name($classKey);
        $classPath  = "Schools/{$school}/{$this->session_year}/{$classNode}";
        $sectionKeys = $this->firebase->shallow_get($classPath);
        if (is_array($sectionKeys)) {
            foreach ($sectionKeys as $secKey) {
                if (strpos($secKey, 'Section ') !== 0) continue;
                $students = $this->firebase->shallow_get("{$classPath}/{$secKey}/Students/List");
                if (!empty($students)) {
                    return $this->json_error(
                        "Cannot delete: students are enrolled in {$classNode} / {$secKey}. Transfer or remove students first."
                    );
                }
            }
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/Classes", array_values($classes));
        if (!$ok) {
            return $this->json_error('Failed to update class.');
        }

        $this->json_success(['message' => "Class '{$classKey}' soft-deleted. It can be restored later."]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/restore_class
    // Issue 7: Restore a soft-deleted class
    // ─────────────────────────────────────────────────────────────────────
    public function restore_class()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_restore_class');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        $classes = $this->firebase->get("Schools/{$school}/Config/Classes") ?? [];
        if (!is_array($classes)) {
            return $this->json_error('No classes configured.');
        }

        $found = false;
        foreach ($classes as &$cls) {
            if (is_array($cls) && ($cls['key'] ?? '') === $classKey) {
                $cls['deleted']    = false;
                $cls['deleted_at'] = null;
                $found = true;
                break;
            }
        }
        unset($cls);

        if (!$found) {
            return $this->json_error('Class not found.');
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/Classes", array_values($classes));
        if (!$ok) {
            return $this->json_error('Failed to update class.');
        }

        $this->json_success(['message' => "Class '{$classKey}' restored."]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/seed_streams
    // Issue 6: Seed standard streams (Science, Commerce, Arts, General)
    // ─────────────────────────────────────────────────────────────────────
    public function seed_streams()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_seed_streams');
        $school = $this->school_name;

        $defaults = [
            'Science'  => ['key' => 'Science',  'label' => 'Science',  'enabled' => true],
            'Commerce' => ['key' => 'Commerce', 'label' => 'Commerce', 'enabled' => true],
            'Arts'     => ['key' => 'Arts',     'label' => 'Arts',     'enabled' => true],
            'General'  => ['key' => 'General',  'label' => 'General',  'enabled' => true],
        ];

        $existing = $this->firebase->get("Schools/{$school}/Config/Streams") ?? [];
        if (!is_array($existing)) $existing = [];

        $added   = 0;
        $skipped = 0;

        foreach ($defaults as $key => $streamData) {
            if (isset($existing[$key])) {
                $skipped++;
                continue;
            }
            $this->firebase->set("Schools/{$school}/Config/Streams/{$key}", $streamData);
            $existing[$key] = $streamData;
            $added++;
        }

        $this->json_success([
            'message' => "{$added} stream(s) seeded. {$skipped} already existed.",
            'streams' => $existing,
            'added'   => $added,
            'skipped' => $skipped,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/get_sections
    // ─────────────────────────────────────────────────────────────────────
    public function get_sections()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_get_sections');
        $school      = $this->school_name;
        $classKey    = trim((string) $this->input->post('class_key', TRUE));
        $sessionYear = trim((string) $this->input->post('session',   TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        if ($sessionYear === '') {
            $sessionYear = $this->session_year;
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $sessionYear)) {
            return $this->json_error('Invalid session format.');
        }

        $classNode   = $this->_class_node_name($classKey);
        $sectionKeys = $this->firebase->shallow_get("Schools/{$school}/{$sessionYear}/{$classNode}");
        $sections    = array_values(array_filter($sectionKeys, function($k) {
            return strpos($k, 'Section ') === 0;
        }));

        $this->json_success(['sections' => $sections, 'class_node' => $classNode]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_section
    // ─────────────────────────────────────────────────────────────────────
    public function save_section()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_section');
        $school        = $this->school_name;
        $classKey      = trim((string) $this->input->post('class_key', TRUE));
        $sectionLetter = strtoupper(trim((string) $this->input->post('section', TRUE)));
        $sessionYear   = trim((string) $this->input->post('session',   TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        if (!preg_match('/^[A-Z]$/', $sectionLetter)) {
            return $this->json_error('Section must be a single capital letter (A-Z).');
        }

        if ($sessionYear === '') {
            $sessionYear = $this->session_year;
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $sessionYear)) {
            return $this->json_error('Invalid session format.');
        }

        $classNode   = $this->_class_node_name($classKey);
        $sectionNode = "Section {$sectionLetter}";
        $path        = "Schools/{$school}/{$sessionYear}/{$classNode}/{$sectionNode}";

        if ($this->firebase->exists($path)) {
            return $this->json_error("{$classNode} / {$sectionNode} already exists in {$sessionYear}.");
        }

        $ok = $this->firebase->set($path, ['created_at' => date('Y-m-d H:i:s')]);
        if (!$ok) {
            return $this->json_error('Failed to create section.');
        }

        $this->json_success([
            'message'      => "{$classNode} / {$sectionNode} created.",
            'class_node'   => $classNode,
            'section_node' => $sectionNode,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/delete_section
    // ─────────────────────────────────────────────────────────────────────
    public function delete_section()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_delete_section');
        $school        = $this->school_name;
        $classKey      = trim((string) $this->input->post('class_key', TRUE));
        $sectionLetter = strtoupper(trim((string) $this->input->post('section', TRUE)));
        $sessionYear   = trim((string) $this->input->post('session',   TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        if (!preg_match('/^[A-Z]$/', $sectionLetter)) {
            return $this->json_error('Invalid section.');
        }

        if ($sessionYear === '') {
            $sessionYear = $this->session_year;
        }

        $classNode   = $this->_class_node_name($classKey);
        $sectionNode = "Section {$sectionLetter}";
        $path        = "Schools/{$school}/{$sessionYear}/{$classNode}/{$sectionNode}";

        if (!$this->firebase->exists($path)) {
            return $this->json_error('Section not found.');
        }

        // Safety: refuse if students are enrolled
        $students = $this->firebase->shallow_get("{$path}/Students/List");
        if (!empty($students)) {
            return $this->json_error('Cannot delete: students are enrolled in this section.');
        }

        $ok = $this->firebase->delete($path);
        if (!$ok) {
            return $this->json_error('Failed to delete section.');
        }

        $this->json_success(['message' => "{$classNode} / {$sectionNode} deleted."]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/get_subjects
    // ─────────────────────────────────────────────────────────────────────
    public function get_subjects()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_get_subjects');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        $numKey  = $this->_numeric_class_key($classKey);
        $rawData = $this->firebase->get("Schools/{$school}/Subject_list/{$numKey}") ?? [];

        $subjects = [];
        if (is_array($rawData)) {
            foreach ($rawData as $code => $sub) {
                if ($code === 'pattern_type') continue;
                if (is_array($sub)) {
                    $subjects[] = [
                        'code'     => $code,
                        'name'     => $sub['subject_name'] ?? $sub['name'] ?? (string) $code,
                        'category' => $sub['category'] ?? 'Core',
                        'stream'   => $sub['stream'] ?? 'common',
                    ];
                }
            }
        }

        $this->json_success([
            'subjects'     => $subjects,
            'pattern_type' => isset($rawData['pattern_type']) ? (int) $rawData['pattern_type'] : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/get_suggested_subjects
    // Returns subjects from Subject Master_List grouped by category
    // for the school's configured board + mapped class range.
    // ─────────────────────────────────────────────────────────────────────
    public function get_suggested_subjects()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_get_suggested_subjects');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));

        if ($classKey === '') {
            return $this->json_error('class_key is required.');
        }

        // 1. Read school's board config
        $board = $this->firebase->get("Schools/{$school}/Config/Board") ?? [];
        $boardType = trim($board['type'] ?? '');
        if ($boardType === '') {
            return $this->json_error('No board configured. Set your board in the Board tab first.');
        }

        // 2. Map class key to master list range
        $classRange = $this->_class_key_to_range($classKey);
        if ($classRange === '') {
            return $this->json_error("Cannot determine class range for '{$classKey}'.");
        }

        // 3. Find latest pattern for this board
        // shallow_get returns indexed array of key names like ["CBSE_Pattern_2025_26", "tree", "_created"]
        // Filter to only keys containing "Pattern" (the naming convention from SA Master List Manager)
        $boardPath   = "Subject Master_List/{$boardType}";
        $patternKeys = $this->firebase->shallow_get($boardPath);

        if (!is_array($patternKeys) || empty($patternKeys)) {
            return $this->json_error("No subject patterns found for board: {$boardType}.");
        }

        $patterns = array_filter($patternKeys, function ($k) {
            return stripos($k, 'Pattern') !== false;
        });
        $patterns = array_values($patterns);
        rsort($patterns); // latest pattern first (e.g. 2026_27 > 2025_26)
        if (empty($patterns)) {
            return $this->json_error("No patterns available for board: {$boardType}. Keys found: " . implode(', ', $patternKeys));
        }
        $pattern = $patterns[0];

        // 4. Read the class range data
        $masterData = $this->firebase->get("Subject Master_List/{$boardType}/{$pattern}/{$classRange}");
        if (!is_array($masterData) || empty($masterData)) {
            return $this->json_error("No subjects found for {$boardType} / {$pattern} / range {$classRange}.");
        }

        // 5. Build grouped response
        $groups = [];
        foreach ($masterData as $groupName => $groupData) {
            if (in_array($groupName, ['Assessment', 'rules', 'Streams', '_created'], true)) continue;
            if (!is_array($groupData)) continue;

            $compulsory = !empty($groupData['compulsory']);
            $options    = $groupData['options'] ?? $groupData;
            if (!is_array($options)) continue;

            $subjects = [];
            foreach ($options as $subjectName) {
                if (!is_string($subjectName) || trim($subjectName) === '') continue;
                $subjects[] = trim($subjectName);
            }
            if (empty($subjects)) continue;

            // Derive category from group name
            $category = $compulsory ? 'Core' : 'Elective';
            if (stripos($groupName, 'Language') !== false) $category = 'Language';
            if (stripos($groupName, 'Vocational') !== false) $category = 'Vocational';
            if (stripos($groupName, 'Additional') !== false) $category = 'Additional';

            $groups[] = [
                'group'      => $groupName,
                'compulsory' => $compulsory,
                'category'   => $category,
                'subjects'   => $subjects,
            ];
        }

        if (empty($groups)) {
            return $this->json_error("No subject groups found for range {$classRange}.");
        }

        $this->json_success([
            'board'      => $boardType,
            'pattern'    => $pattern,
            'classRange' => $classRange,
            'groups'     => $groups,
        ]);
    }

    /**
     * Map a class key (e.g. "9th", "Nursery") to the master list range.
     */
    private function _class_key_to_range(string $classKey): string
    {
        $lower = strtolower(trim($classKey));
        $foundational = [
            'playgroup' => 'Playgroup', 'play' => 'Playgroup',
            'nursery'   => 'Nursery',
            'lkg'       => 'LKG',
            'ukg'       => 'UKG',
        ];
        foreach ($foundational as $kw => $range) {
            if (strpos($lower, $kw) !== false) return $range;
        }
        preg_match('/\d+/', $classKey, $m);
        $n = isset($m[0]) ? (int) $m[0] : 0;
        if ($n >= 1 && $n <= 5)  return '1-5';
        if ($n >= 6 && $n <= 8)  return '6-8';
        if ($n >= 9 && $n <= 10) return '9-10';
        if ($n >= 11 && $n <= 12) return '11-12';
        return '';
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_subject
    // ─────────────────────────────────────────────────────────────────────
    public function save_subject()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_subject');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));
        $name     = trim((string) $this->input->post('name',      TRUE));
        $category = trim((string) $this->input->post('category',  TRUE)) ?: 'Core';
        $stream   = trim((string) $this->input->post('stream',    TRUE)) ?: 'common';
        $code     = trim((string) $this->input->post('code',      TRUE));

        if ($classKey === '' || $name === '') {
            return $this->json_error('class_key and name are required.');
        }

        // Issue 8: Added Assessment category
        $validCategories = ['Core', 'Elective', 'Additional', 'Language', 'Vocational', 'Assessment'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'Core';
        }

        $numKey = $this->_numeric_class_key($classKey);

        // Issue 8: Check for duplicate subject name in same class
        $existing = $this->firebase->get("Schools/{$school}/Subject_list/{$numKey}") ?? [];
        if (is_array($existing)) {
            foreach ($existing as $existCode => $existSub) {
                if ($existCode === 'pattern_type') continue;
                $existName = $existSub['subject_name'] ?? $existSub['name'] ?? '';
                if (is_array($existSub) && $existName !== '') {
                    if (strtolower(trim($existName)) === strtolower($name) && $existCode !== $code) {
                        return $this->json_error("Subject '{$name}' already exists in this class.");
                    }
                }
            }
        }

        // Auto-generate code if not provided
        if ($code === '') {
            $count = is_array($existing)
                ? count(array_filter(array_keys($existing), function($k) { return $k !== 'pattern_type'; }))
                : 0;

            if (is_numeric($numKey)) {
                $code = (string) ((int) $numKey * 100 + $count + 1);
            } else {
                $prefix = strtoupper(substr($numKey, 0, 3));
                $code   = $prefix . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
            }
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $code)) {
            return $this->json_error('Invalid subject code.');
        }

        $subjectData = [
            'subject_name' => $name,
            'name'         => $name,
            'category'     => $category,
            'subject_code' => $code,
        ];

        if ($stream !== 'common') {
            $subjectData['stream'] = $stream;
        }

        $ok = $this->firebase->set("Schools/{$school}/Subject_list/{$numKey}/{$code}", $subjectData);
        if (!$ok) {
            return $this->json_error('Failed to save subject.');
        }

        $this->json_success(['message' => "Subject '{$name}' saved.", 'code' => $code]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/delete_subject
    // ─────────────────────────────────────────────────────────────────────
    public function delete_subject()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_delete_subject');
        $school   = $this->school_name;
        $classKey = trim((string) $this->input->post('class_key', TRUE));
        $code     = trim((string) $this->input->post('code',      TRUE));

        if ($classKey === '' || $code === '') {
            return $this->json_error('class_key and code are required.');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $code)) {
            return $this->json_error('Invalid subject code.');
        }

        $numKey = $this->_numeric_class_key($classKey);
        $ok     = $this->firebase->delete("Schools/{$school}/Subject_list/{$numKey}", $code);
        if (!$ok) {
            return $this->json_error('Failed to delete subject.');
        }

        $this->json_success(['message' => 'Subject deleted.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/save_stream
    // ─────────────────────────────────────────────────────────────────────
    public function save_stream()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_save_stream');
        $school    = $this->school_name;
        $streamKey = trim((string) $this->input->post('stream_key', TRUE));
        $label     = trim((string) $this->input->post('label',      TRUE));
        $enabled   = (bool) $this->input->post('enabled');

        if ($streamKey === '' || $label === '') {
            return $this->json_error('stream_key and label are required.');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $streamKey)) {
            return $this->json_error('Invalid stream key. Use letters, digits, underscores only.');
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/Streams/{$streamKey}", [
            'key'     => $streamKey,
            'label'   => $label,
            'enabled' => $enabled,
        ]);

        if (!$ok) {
            return $this->json_error('Failed to save stream.');
        }

        $this->json_success(['message' => "Stream '{$label}' saved."]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/delete_stream
    // ─────────────────────────────────────────────────────────────────────
    public function delete_stream()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_delete_stream');
        $school    = $this->school_name;
        $streamKey = trim((string) $this->input->post('stream_key', TRUE));

        if ($streamKey === '' || !preg_match('/^[A-Za-z0-9_]+$/', $streamKey)) {
            return $this->json_error('Invalid stream key.');
        }

        $ok = $this->firebase->delete("Schools/{$school}/Config/Streams", $streamKey);
        if (!$ok) {
            return $this->json_error('Failed to delete stream.');
        }

        $this->json_success(['message' => 'Stream deleted.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET   /school_config/test_sessions  (dev diagnostic — debug only)
    // ─────────────────────────────────────────────────────────────────────
    public function test_sessions()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_test_sessions');
        if (!defined('GRADER_DEBUG') || !GRADER_DEBUG) { show_404(); return; }
        $school = $this->school_name;
        $path   = "Schools/{$school}/Sessions";

        $fromFirebase = $this->firebase->get($path) ?? [];
        $fromFirebase = is_array($fromFirebase)
            ? array_values(array_filter($fromFirebase, 'is_string'))
            : [];

        $fromPhpSession = $this->session->userdata('available_sessions') ?? [];
        $fromPhpSession = is_array($fromPhpSession) ? array_values($fromPhpSession) : [];

        $activeConfig    = $this->firebase->get("Schools/{$school}/Config/ActiveSession");
        $sessionYearPhp  = (string) ($this->session->userdata('session') ?? $this->session_year);

        sort($fromFirebase);
        $fbSorted  = $fromFirebase;
        $phpSorted = $fromPhpSession;
        sort($phpSorted);
        $inSync = ($fbSorted === $phpSorted);

        $this->json_success([
            'firebase_path'        => $path,
            'firebase_sessions'    => $fromFirebase,
            'php_session_sessions' => $fromPhpSession,
            'in_sync'              => $inSync,
            'divergence'           => [
                'only_in_firebase'    => array_values(array_diff($fromFirebase,    $fromPhpSession)),
                'only_in_php_session' => array_values(array_diff($fromPhpSession, $fromFirebase)),
            ],
            'current_session_year' => $sessionYearPhp,
            'active_config_node'   => (string) ($activeConfig ?? ''),
            'school_name'          => $school,
            'school_id'            => $this->school_id,
            'note'                 => 'If firebase_sessions differs from php_session_sessions, call sync_sessions (POST) to reconcile.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/sync_sessions
    // ─────────────────────────────────────────────────────────────────────
    public function sync_sessions()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_sync_sessions');
        $school = $this->school_name;
        $path   = "Schools/{$school}/Sessions";

        $fromFirebase = $this->firebase->get($path) ?? [];
        $sessions = is_array($fromFirebase)
            ? array_values(array_filter($fromFirebase, 'is_string'))
            : [];

        if (empty($sessions)) {
            $this->json_success([
                'sessions' => [],
                'synced'   => false,
                'message'  => 'Firebase Sessions list is empty. PHP session unchanged. Verify the Firebase path.',
                'firebase_path' => $path,
            ]);
            return;
        }

        $this->session->set_userdata('available_sessions', $sessions);

        $this->json_success([
            'sessions'      => $sessions,
            'synced'        => true,
            'message'       => 'PHP session synced from Firebase. Header dropdown will update on next render.',
            'firebase_path' => $path,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config/csrf_token
    // ─────────────────────────────────────────────────────────────────────
    public function csrf_token()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_csrf_token');
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'csrf_name'  => $this->security->get_csrf_token_name(),
                'csrf_token' => $this->security->get_csrf_hash(),
            ]));
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config/test_profile  (dev diagnostic — debug only)
    // ─────────────────────────────────────────────────────────────────────
    public function test_profile()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_test_profile');
        if (!defined('GRADER_DEBUG') || !GRADER_DEBUG) { show_404(); return; }
        $school = $this->school_name;
        $data   = $this->firebase->get("Schools/{$school}/Config/Profile") ?? [];
        $this->json_success(['data' => is_array($data) ? $data : []]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config/test_classes  (dev diagnostic — debug only)
    // ─────────────────────────────────────────────────────────────────────
    public function test_classes()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_test_classes');
        if (!defined('GRADER_DEBUG') || !GRADER_DEBUG) { show_404(); return; }
        $school = $this->school_name;
        $raw    = $this->firebase->get("Schools/{$school}/Config/Classes") ?? [];
        $data   = is_array($raw) ? array_values($raw) : [];
        $this->json_success(['data' => $data]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config/test_sections  (dev diagnostic — debug only)
    // ─────────────────────────────────────────────────────────────────────
    public function test_sections()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_test_sections');
        if (!defined('GRADER_DEBUG') || !GRADER_DEBUG) { show_404(); return; }
        $school  = $this->school_name;
        $session = $this->session_year;

        $classKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}") ?? [];
        $data = [];

        foreach ($classKeys as $classKey) {
            if (strpos($classKey, 'Class ') !== 0) continue;
            $sectionKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}/{$classKey}") ?? [];
            $sections = [];
            foreach ($sectionKeys as $sectionKey) {
                if (strpos($sectionKey, 'Section ') !== 0) continue;
                $sections[] = $sectionKey;
            }
            if (!empty($sections)) {
                $data[$classKey] = $sections;
            }
        }

        $this->json_success(['data' => $data, 'session' => $session]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET  /school_config/test_subjects  (dev diagnostic — debug only)
    // ─────────────────────────────────────────────────────────────────────
    public function test_subjects()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_test_subjects');
        if (!defined('GRADER_DEBUG') || !GRADER_DEBUG) { show_404(); return; }
        $school = $this->school_name;
        $raw    = $this->firebase->get("Schools/{$school}/Subject_list") ?? [];
        $this->json_success(['data' => is_array($raw) ? $raw : []]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/add_session
    // ─────────────────────────────────────────────────────────────────────
    public function add_session()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_add_session');
        $school  = $this->school_name;
        $session = trim((string) $this->input->post('session', TRUE));

        if (!preg_match('/^\d{4}-\d{2}$/', $session)) {
            return $this->json_error('Session must be in YYYY-YY format (e.g. 2025-26).');
        }

        $startYear = (int) substr($session, 0, 4);
        $endYY     = (int) substr($session, 5, 2);
        if ($endYY !== ($startYear + 1) % 100) {
            return $this->json_error('Invalid session: end year must follow start year (e.g. 2025-26).');
        }

        $sessions = $this->firebase->get("Schools/{$school}/Sessions") ?? [];
        $sessions = is_array($sessions)
            ? array_values(array_filter($sessions, 'is_string'))
            : [];

        if (in_array($session, $sessions, true)) {
            return $this->json_error("Session {$session} already exists.");
        }

        $sessions[] = $session;
        sort($sessions);

        $ok = $this->firebase->set("Schools/{$school}/Sessions", $sessions);
        if (!$ok) {
            return $this->json_error('Failed to save session.');
        }

        $this->session->set_userdata('available_sessions', $sessions);

        $this->json_success(['message' => "Session {$session} added.", 'sessions' => $sessions]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST  /school_config/set_active_session
    // Issue 2: Now also updates all 3 PHP session keys so the entire
    // application switches immediately without re-login.
    // ─────────────────────────────────────────────────────────────────────
    public function set_active_session()
    {
        $this->_require_role(self::ADMIN_ROLES, 'school_config_set_active_session');
        $school  = $this->school_name;
        $session = trim((string) $this->input->post('session', TRUE));

        if (!preg_match('/^\d{4}-\d{2}$/', $session)) {
            return $this->json_error('Invalid session format.');
        }

        // Must exist in the sessions list
        $sessions = $this->firebase->get("Schools/{$school}/Sessions") ?? [];
        $sessions = is_array($sessions)
            ? array_values(array_filter($sessions, 'is_string'))
            : [];
        if (!in_array($session, $sessions, true)) {
            return $this->json_error('Session not found. Add it first.');
        }

        $ok = $this->firebase->set("Schools/{$school}/Config/ActiveSession", $session);
        if (!$ok) {
            return $this->json_error('Failed to update active session.');
        }

        // ── Issue 2 Fix: Update all 3 PHP session keys ─────────────────
        // These are the same 3 keys that Admin::switch_session() updates,
        // ensuring full compatibility with the header session-switcher and
        // every controller that reads $this->session_year.
        $this->session->set_userdata([
            'session'         => $session,
            'current_session' => $session,
            'session_year'    => $session,
        ]);

        // Also update the controller property for this request
        $this->session_year = $session;

        log_audit('Configuration', 'set_active_session', $session, "Changed active session to {$session}");

        $this->json_success([
            'message'        => "Active session set to {$session}. All modules will now use this session.",
            'active_session' => $session,
        ]);
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    private function _class_node_name(string $classKey): string
    {
        static $map = [
            'nursery'   => 'Nursery',
            'lkg'       => 'LKG',
            'ukg'       => 'UKG',
            'playgroup' => 'Playgroup',
        ];
        $lower = strtolower(trim($classKey));
        if (isset($map[$lower])) {
            return 'Class ' . $map[$lower];
        }
        $num    = (int) $classKey;
        $suffix = $this->_ordinal_suffix($num);
        return "Class {$num}{$suffix}";
    }

    private function _numeric_class_key(string $classKey): string
    {
        $lower = strtolower(trim($classKey));
        if ($lower === 'nursery')   return 'Nursery';
        if ($lower === 'lkg')       return 'LKG';
        if ($lower === 'ukg')       return 'UKG';
        if ($lower === 'playgroup') return 'Playgroup';
        return (string) (int) $classKey;
    }

    private function _ordinal_suffix(int $n): string
    {
        if ($n >= 11 && $n <= 13) return 'th';
        switch ($n % 10) {
            case 1:  return 'st';
            case 2:  return 'nd';
            case 3:  return 'rd';
            default: return 'th';
        }
    }
}
