<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin controller
 *
 * SECURITY FIXES:
 * [FIX-1]  Removed duplicate auth check in __construct — MY_Controller handles it.
 * [FIX-2]  Hardcoded school ID '1111' replaced with $this->school_id from session.
 * [FIX-3]  manage_admin: password update uses password_hash (was plaintext).
 * [FIX-4]  All Firebase paths use session school_id (not hardcoded '1111').
 * [FIX-5]  updateUserData: user can only update their own school's admin data.
 * [FIX-6]  Debug echo / print_r calls removed from production code.
 */
class Admin extends MY_Controller
{
    private const ADMIN_ROLES = ['Admin'];
    private const VIEW_ROLES  = ['Admin', 'Principal', 'Teacher'];

    public function __construct()
    {
        parent::__construct();
        // [FIX-1] No duplicate auth here — MY_Controller __construct handles it.
    }

    public function index()
    {
        // Dashboard is the landing page — any authenticated admin can see it.
        // MY_Controller __construct already enforces authentication.
        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        // Fetch school logo
        $school_logo_url = $this->firebase->get("Schools/{$school_name}/Logo");
        if (!$school_logo_url) {
            $school_logo_url = base_url('tools/dist/img/default-school.png');
        }

        $data = [
            'admin_name'      => $this->admin_name,
            'admin_role'      => $this->admin_role,
            'school_id'       => $school_id,
            'admin_id'        => $this->admin_id,
            'schoolName'      => $school_name,
            'Session'         => $session_year,
            'school_logo_url' => $school_logo_url,
        ];

        $this->load->view('include/header', $data);
        $this->load->view('home', $data);
        $this->load->view('include/footer');
    }

    // ====================================================================
    //  DASHBOARD DATA — single AJAX endpoint (max 5 Firebase reads)
    // ====================================================================

    public function get_dashboard_data()
    {
        header('Content-Type: application/json');

        $school    = $this->school_name;
        $session   = $this->session_year;
        $role      = $this->admin_role;

        // ── Read 1: Lightweight Students_Index (OPT: replaces full Users/Parents tree)
        $index = $this->firebase->get("Schools/{$school}/SIS/Students_Index");
        $studentCount = 0;
        $classDist    = [];
        $genderDist   = ['Male' => 0, 'Female' => 0, 'Other' => 0];
        $sectionSet   = [];

        if (is_array($index)) {
            foreach ($index as $sid => $s) {
                if (!is_array($s) || empty($s['name'])) continue;
                // Skip non-active students (TC, Inactive, Withdrawn)
                $status = $s['status'] ?? 'Active';
                if ($status !== 'Active') continue;

                $studentCount++;

                $cls = trim($s['class'] ?? 'Unknown');
                $sec = trim($s['section'] ?? '');
                $classDist[$cls] = ($classDist[$cls] ?? 0) + 1;
                if ($cls && $sec) $sectionSet["{$cls}|{$sec}"] = true;

                $g = strtolower(trim($s['gender'] ?? ''));
                if ($g === 'male' || $g === 'm')        $genderDist['Male']++;
                elseif ($g === 'female' || $g === 'f')  $genderDist['Female']++;
                elseif ($g !== '')                        $genderDist['Other']++;
            }
        }
        uksort($classDist, 'strnatcasecmp');

        // ── Read 2: Teachers (shallow count) ──────────────────────────────
        $teacherKeys  = $this->firebase->shallow_get("Schools/{$school}/{$session}/Teachers");
        $teacherCount = is_array($teacherKeys) ? count($teacherKeys) : 0;

        // ── Read 3: Session root (shallow) for class count ────────────────
        $sessionKeys = $this->firebase->shallow_get("Schools/{$school}/{$session}");
        $classCount  = 0;
        if (is_array($sessionKeys)) {
            foreach ($sessionKeys as $key) {
                if (strpos($key, 'Class ') === 0) $classCount++;
            }
        }

        // ── Read 4: Receipt Index (fees) — skip for Teacher role ──────────
        // SEC-8: All financial data (fees_collected, monthly_fees) is guarded
        // behind this role check. Teachers see zeroed values only.
        $feesCollected = 0;
        $monthlyFees   = [];

        if ($role !== 'Teacher') {
            $receipts = $this->firebase->get("Schools/{$school}/{$session}/Accounts/Receipt_Index");
            if (is_array($receipts)) {
                foreach ($receipts as $rNo => $r) {
                    if (!is_array($r)) continue;
                    $amt = (float) ($r['amount'] ?? 0);
                    $feesCollected += $amt;

                    $dateStr = $r['date'] ?? '';
                    if ($dateStr) {
                        $ts = strtotime($dateStr);
                        if ($ts) {
                            $monthKey = date('Y-m', $ts);
                            $monthlyFees[$monthKey] = ($monthlyFees[$monthKey] ?? 0) + $amt;
                        }
                    }
                }
            }
            ksort($monthlyFees);
        }

        // ── Read 5: Events ────────────────────────────────────────────────
        $eventsRaw      = $this->firebase->get("Schools/{$school}/Events/List");
        $upcoming       = [];
        $ongoing        = [];
        $recent         = [];
        $calendarEvents = [];
        $today          = date('Y-m-d');

        if (is_array($eventsRaw)) {
            foreach ($eventsRaw as $eid => $evt) {
                if (!is_array($evt)) continue;
                $start  = $evt['start_date'] ?? '';
                $end    = $evt['end_date']   ?? $start;
                $status = $evt['status']     ?? 'scheduled';

                $item = [
                    'id'       => $eid,
                    'title'    => $evt['title']    ?? '',
                    'category' => $evt['category'] ?? 'event',
                    'start'    => $start,
                    'end'      => $end,
                    'status'   => $status,
                    'location' => $evt['location'] ?? '',
                ];

                if ($start) {
                    $calendarEvents[] = ['date' => $start, 'title' => $item['title']];
                }

                if ($status === 'cancelled') continue;

                if ($start >= $today && $status === 'scheduled') {
                    $upcoming[] = $item;
                } elseif ($status === 'ongoing' || ($start <= $today && $end >= $today)) {
                    $ongoing[] = $item;
                } elseif ($status === 'completed') {
                    $recent[] = $item;
                }
            }

            usort($upcoming, fn($a, $b) => strcmp($a['start'], $b['start']));
            usort($recent,   fn($a, $b) => strcmp($b['start'], $a['start']));
            $upcoming = array_slice($upcoming, 0, 5);
            $recent   = array_slice($recent, 0, 3);
        }

        echo json_encode([
            'role'              => $role,
            'stats'             => [
                'students'       => $studentCount,
                'teachers'       => $teacherCount,
                'classes'        => $classCount,
                'sections'       => count($sectionSet),
                'fees_collected' => $feesCollected,
            ],
            'students_by_class' => $classDist,
            'gender'            => $genderDist,
            'monthly_fees'      => $monthlyFees,
            'events'            => [
                'upcoming' => $upcoming,
                'ongoing'  => $ongoing,
                'recent'   => $recent,
            ],
            'calendar_events'   => $calendarEvents,
        ]);
    }

    public function manage_admin()
    {
        $this->_require_role(self::ADMIN_ROLES);
        // [FIX-2] Use parent_db_key for Users/Admin paths (works for both legacy and SCH_ schools)
        $school_id = $this->parent_db_key;

        if ($this->input->method() === 'post') {
            header('Content-Type: application/json');

            $adminId = $this->safe_path_segment(trim((string) $this->input->post('admin_id')), 'admin_id');

            // ── Password update ───────────────────────────────────────────
            if ($this->input->post('newPassword') && $this->input->post('confirmPassword') && $adminId) {
                $newPassword     = $this->input->post('newPassword');
                $confirmPassword = $this->input->post('confirmPassword');

                if ($newPassword !== $confirmPassword) {
                    $this->json_error('Passwords do not match.', 400);
                }
                if (strlen($newPassword) < 8) {
                    $this->json_error('Password must be at least 8 characters.', 400);
                }
                if (strlen($newPassword) > 72) {
                    $this->json_error('Password must not exceed 72 characters.', 400);
                }

                // [FIX-3] Hash password before storing
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $result = $this->firebase->update(
                    "Users/Admin/{$school_id}/{$adminId}",
                    ['Credentials' => ['Password' => $hashedPassword]]
                );

                if ($result !== false) {
                    $this->json_success(['message' => 'Password updated successfully.']);
                } else {
                    $this->json_error('Failed to update password.', 500);
                }
            }

            // ── Fetch single admin ────────────────────────────────────────
            if ($adminId && !$this->input->post('name')) {
                $adminDetails = $this->firebase->get("Users/Admin/{$school_id}/{$adminId}");

                if ($adminDetails) {
                    // Strip credentials before returning to UI
                    unset($adminDetails['Credentials']);
                    $this->json_success(['data' => $adminDetails]);
                } else {
                    $this->json_error('Admin not found.', 404);
                }
            }

            // ── Add new admin ─────────────────────────────────────────────
            $name     = trim((string) $this->input->post('name'));
            $email    = trim((string) $this->input->post('email'));
            $phone    = trim((string) $this->input->post('phone'));
            $dob      = trim((string) $this->input->post('dob'));
            $gender   = trim((string) $this->input->post('gender'));
            $role     = trim((string) $this->input->post('role'));
            $password = (string) $this->input->post('password');

            if (!$name || !$email || !$password || !$role) {
                $this->json_error('Required fields missing.', 400);
            }
            if (strlen($password) < 8) {
                $this->json_error('Password must be at least 8 characters.', 400);
            }
            if (strlen($password) > 72) {
                $this->json_error('Password must not exceed 72 characters.', 400);
            }

            // Fetch current admin count
            $fetchedAdminData = $this->firebase->get("Users/Admin/{$school_id}");
            $count = isset($fetchedAdminData['Count']) ? (int) $fetchedAdminData['Count'] : 0;
            $newAdminId = 'ADM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            // [FIX-3] Hash new admin password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $adminData = [
                'AccessHistory' => [
                    'LastLogin'     => date('c'),
                    'LoginAttempts' => 0,
                    'LoginIP'       => $this->input->ip_address(),
                ],
                'Created On' => date('c'),
                'Credentials' => [
                    'Id'       => $newAdminId,
                    'Password' => $hashedPassword,
                ],
                'DOB'         => $dob ? date('d-m-Y', strtotime($dob)) : '',
                'Email'       => $email,
                'Gender'      => $gender,
                'Name'        => $name,
                'PhoneNumber' => $phone,
                'Role'        => $role,
                'Status'      => 'Active',
            ];

            $this->firebase->set("Users/Admin/{$school_id}/{$newAdminId}", $adminData);
            $this->firebase->update("Users/Admin/{$school_id}", ['Count' => $count + 1]);

            $this->json_success(['message' => 'Admin created.', 'adminId' => $newAdminId]);

        } else {
            // ── GET: List all admins ──────────────────────────────────────
            $fetchedAdminData = $this->firebase->get("Users/Admin/{$school_id}");

            $data = [
                'adminList'     => [],
                'activeAdmins'  => [],
                'inactiveAdmins'=> [],
                'adminId'       => null,
            ];

            if (is_array($fetchedAdminData)) {
                $count = isset($fetchedAdminData['Count']) ? (int) $fetchedAdminData['Count'] : 0;
                $data['adminId'] = 'ADM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

                foreach ($fetchedAdminData as $key => $value) {
                    if ($key === 'Count' || !is_array($value)) {
                        continue;
                    }

                    $status = $value['Status'] ?? 'Unknown';
                    $entry  = [
                        'id'     => $key,
                        'name'   => $value['Name']  ?? 'Unknown',
                        'role'   => $value['Role']  ?? 'Unknown',
                        'status' => $status,
                    ];

                    if ($status === 'Active') {
                        $data['activeAdmins'][]  = $entry;
                        $data['adminList'][]     = "{$key} - {$entry['name']} - {$entry['role']}";
                    } else {
                        $data['inactiveAdmins'][] = $entry;
                    }
                }
            }

            $this->load->view('include/header');
            $this->load->view('manage_admin', $data);
            $this->load->view('include/footer');
        }
    }

    public function edit_admin()
    {
        $this->_require_role(self::ADMIN_ROLES);
        header('Content-Type: application/json');

        $school_id = $this->parent_db_key;
        $admin_id  = trim((string) $this->input->post('admin_id'));

        if (!$admin_id) {
            $this->json_error('Admin ID required.', 400);
        }
        $admin_id = $this->safe_path_segment($admin_id, 'admin_id');

        // [FIX-2] Use session school_id; strip credentials fields from update
        $update_data = [
            'Name'        => trim((string) $this->input->post('admin_name')),
            'Email'       => trim((string) $this->input->post('admin_email')),
            'PhoneNumber' => trim((string) $this->input->post('admin_phone')),
            'Role'        => trim((string) $this->input->post('admin_role')),
            'DOB'         => trim((string) $this->input->post('admin_dob')),
            'Gender'      => trim((string) $this->input->post('admin_gender')),
        ];

        $result = $this->firebase->update("Users/Admin/{$school_id}/{$admin_id}", $update_data);

        if ($result !== false) {
            $this->json_success();
        } else {
            $this->json_error('Update failed.', 500);
        }
    }

    // =========================================================================
    //  SESSION MANAGEMENT
    // =========================================================================

    /**
     * POST: Switch the active academic session for the current user.
     * The new year must already exist in the user's available_sessions list
     * (whitelist check prevents path injection and cross-school access).
     */
    public function switch_session(): void
    {
        $this->_require_role(self::ADMIN_ROLES);
        if ($this->input->method() !== 'post') {
            $this->json_error('Method not allowed.', 405);
        }

        $new_year = trim((string) $this->input->post('session_year'));

        if (!preg_match('/^\d{4}-\d{2}$/', $new_year)) {
            $this->json_error('Invalid session year format.', 400);
        }

        // Whitelist — must be in this school's available sessions
        $available = $this->session->userdata('available_sessions') ?? [];
        if (!in_array($new_year, $available, true)) {
            $this->json_error('Session not available for your school.', 403);
        }

        // Update all three key aliases so every controller/view stays in sync
        $this->session->set_userdata([
            'session'         => $new_year,  // MY_Controller reads this
            'current_session' => $new_year,  // Account controller reads this
            'session_year'    => $new_year,  // Account_model reads this
        ]);

        // Persist active session to Firebase so it survives re-login
        try {
            $this->firebase->set("Schools/{$this->school_name}/Config/ActiveSession", $new_year);
        } catch (Exception $e) {
            log_message('error', 'switch_session: ActiveSession persist failed — ' . $e->getMessage());
        }

        log_message('info',
            "Session switched to [{$new_year}] admin=[{$this->admin_id}] school=[{$this->school_name}]"
        );
        $this->json_success(['session_year' => $new_year]);
    }

    /**
     * POST: Create a new academic session year in Firebase.
     * Restricted to Super Admin role.
     */
    public function create_session(): void
    {
        // Only Super Admin can create new academic sessions
        $this->_require_role(['Super Admin']);
        if ($this->input->method() !== 'post') {
            $this->json_error('Method not allowed.', 405);
        }

        $new_year = trim((string) $this->input->post('session_year'));

        if (!preg_match('/^\d{4}-\d{2}$/', $new_year)) {
            $this->json_error('Invalid format. Use YYYY-YY (e.g. 2026-27).', 400);
        }

        // Validate YY matches YYYY+1 (e.g. 2026-27 is valid, 2026-99 is not)
        [$yearPart, $yyPart] = explode('-', $new_year);
        $expectedYY = substr((string)((int)$yearPart + 1), -2);
        if ($yyPart !== $expectedYY) {
            $this->json_error(
                "Year mismatch: {$yearPart}-{$yyPart} should be {$yearPart}-{$expectedYY}.", 400
            );
        }

        $available = $this->session->userdata('available_sessions') ?? [];
        if (in_array($new_year, $available, true)) {
            $this->json_error('This session already exists.', 409);
        }

        // Create the Firebase node with an audit trail stub
        $nodeWritten = $this->firebase->set("Schools/{$this->school_name}/{$new_year}/Created", [
            'by' => $this->admin_id,
            'at' => date('Y-m-d H:i:s'),
        ]);

        if ($nodeWritten === false) {
            $this->json_error('Could not reach Firebase. Check your server\'s internet connection.', 503);
        }

        // Update the Sessions index node
        $available[] = $new_year;
        rsort($available);
        $indexWritten = $this->firebase->set("Schools/{$this->school_name}/Sessions", $available);

        if ($indexWritten === false) {
            // Node was created but index failed — still usable, log a warning
            log_message('error',
                "Session [{$new_year}] node created but Sessions index update failed. school=[{$this->school_name}]"
            );
        }

        // Update PHP session only after Firebase confirms the write
        $this->session->set_userdata('available_sessions', $available);

        log_message('info',
            "New session [{$new_year}] created by admin=[{$this->admin_id}] school=[{$this->school_name}]"
        );
        $this->json_success(['session_year' => $new_year, 'available_sessions' => $available]);
    }

    /**
     * [FIX-5] updateUserData: scoped to the session school only.
     */
    public function updateUserData()
    {
        $this->_require_role(self::ADMIN_ROLES);
        header('Content-Type: application/json');

        $school_id = $this->parent_db_key;
        $modalId   = trim((string) $this->input->post('modal_id'));
        $userData  = $this->input->post('user_data');

        if (!$modalId || !is_array($userData)) {
            $this->json_error('Invalid input data.', 400);
        }
        $modalId = $this->safe_path_segment($modalId, 'modal_id');

        // Prevent updating Credentials via this endpoint
        unset($userData['Credentials'], $userData['AccessHistory']);

        try {
            $this->firebase->update("Users/Admin/{$school_id}/{$modalId}", $userData);
            $this->json_success(['message' => 'Data updated successfully.']);
        } catch (Exception $e) {
            log_message('error', 'Admin updateUserData: ' . $e->getMessage());
            $this->json_error('Error updating data.', 500);
        }
    }
}
