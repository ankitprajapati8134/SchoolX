<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AdminUsers - ERP Identity & Access Management (IAM)
 *
 * Central administrator management for each school tenant.
 * Manages admin accounts, RBAC roles/permissions, and login audit logs.
 *
 * Firebase paths (aligned with Admin_login.php & Superadmin_schools.php):
 *   Users/Admin/{school_code}/{adminId}       - admin user profiles
 *   Schools/{school}/Roles/{roleName}          - role permission sets
 *
 * Admin record schema (matches onboarding + login validator):
 *   Status        : 'Active' | 'Disabled'
 *   Role          : top-level role string (Admin_login reads this)
 *   Name          : top-level name string (Admin_login reads this)
 *   Credentials   : { Password: '<bcrypt>' }
 *   Profile       : { name, email, phone, role, school, school_id, firebase_id, created_at, createdBy }
 *   AccessHistory : { LastLogin, LoginIP, LoginAttempts, LockedUntil, IsLoggedIn }
 */
class AdminUsers extends MY_Controller
{
    /* -- Default permission sets (seeded on first use) ------------- */
    private const DEFAULT_ROLES = [
        'Admin' => [
            'label'       => 'Administrator',
            'description' => 'Full system access',
            'permissions' => ['SIS','Fees','Accounting','Attendance','Examinations','Results',
                              'LMS','Certificates','HR','Events','Communication','Operations',
                              'Academic','Reports','Configuration','Admin Users'],
            'is_system'   => true,
        ],
        'Principal' => [
            'label'       => 'Principal',
            'description' => 'Academic modules, student data, reports',
            'permissions' => ['SIS','Attendance','Examinations','Results','LMS','Certificates',
                              'Academic','Reports','Events','Communication'],
            'is_system'   => true,
        ],
        'Accountant' => [
            'label'       => 'Accountant',
            'description' => 'Fees, accounting, receipts',
            'permissions' => ['Fees','Accounting','Reports'],
            'is_system'   => true,
        ],
        'HR Manager' => [
            'label'       => 'HR Manager',
            'description' => 'HR, payroll, leave management',
            'permissions' => ['HR','Reports'],
            'is_system'   => true,
        ],
        'Academic Coordinator' => [
            'label'       => 'Academic Coordinator',
            'description' => 'Academic planner, timetable, examinations',
            'permissions' => ['Academic','Examinations','Results','LMS','Attendance'],
            'is_system'   => true,
        ],
        'Staff' => [
            'label'       => 'Staff',
            'description' => 'View-only access',
            'permissions' => [],
            'is_system'   => true,
        ],
    ];

    /* -- All available modules for permission assignment ----------- */
    private const AVAILABLE_MODULES = [
        'SIS','Fees','Accounting','Attendance','Examinations','Results',
        'LMS','Certificates','HR','Events','Communication','Operations',
        'Academic','Reports','Configuration','Admin Users',
    ];

    public function __construct()
    {
        parent::__construct();
        require_permission('Admin Users');
    }

    /**
     * Base Firebase path for admin records.
     * Uses school_code (login code) - same key Admin_login.php reads/writes.
     */
    private function _admin_base(): string
    {
        return "Users/Admin/{$this->school_code}";
    }

    /**
     * Normalize a raw Firebase admin record into the flat format the view expects.
     * Handles both top-level keys (Role, Name, Status) and nested Profile/ keys.
     */
    private function _normalize_admin(string $aid, array $a): array
    {
        $created = $a['Profile']['created_at'] ?? '';
        return [
            'adminId'   => $aid,
            'name'      => $a['Name'] ?? $a['Profile']['name'] ?? '',
            'email'     => $a['Profile']['email'] ?? '',
            'phone'     => $a['Profile']['phone'] ?? '',
            'role'      => $a['Role'] ?? $a['Profile']['role'] ?? '',
            'status'    => strtolower($a['Status'] ?? 'Active'),
            'createdAt' => is_numeric($created) ? date('Y-m-d', (int)$created) : (string)$created,
            'lastLogin' => $a['AccessHistory']['LastLogin'] ?? '',
        ];
    }

    // -------------------------------------------------------------------------
    // GET  /admin_users
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->_require_role(['Super Admin', 'Admin', 'Principal'], 'admin_users_view');

        $data = [
            'page_title'        => 'Admin Users',
            'available_modules' => self::AVAILABLE_MODULES,
        ];

        $this->load->view('include/header', $data);
        $this->load->view('admin_users/index', $data);
        $this->load->view('include/footer');
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/get_dashboard
    // -------------------------------------------------------------------------

    public function get_dashboard(): void
    {
        $this->_require_role(['Super Admin', 'Admin', 'Principal'], 'admin_users_dashboard');

        try {
            $admins = $this->firebase->get($this->_admin_base()) ?? [];

            $total = 0; $active = 0; $disabled = 0;
            $recent = [];

            foreach ($admins as $aid => $a) {
                if (!is_array($a)) continue;
                $total++;
                $status = $a['Status'] ?? 'Active';
                if ($status === 'Active') $active++;
                else $disabled++;

                // Collect last-login info for recent activity
                $lastLogin = $a['AccessHistory']['LastLogin'] ?? '';
                if (!empty($lastLogin)) {
                    $recent[] = [
                        'adminId'   => $aid,
                        'adminName' => $a['Name'] ?? $a['Profile']['name'] ?? $aid,
                        'loginTime' => $lastLogin,
                        'ipAddress' => $a['AccessHistory']['LoginIP'] ?? '',
                        'status'    => 'success',
                        'device'    => '-',
                    ];
                }
            }

            // Sort by loginTime descending, take top 10
            usort($recent, fn($a, $b) => strcmp($b['loginTime'] ?? '', $a['loginTime'] ?? ''));
            $recent = array_slice($recent, 0, 10);

            $this->json_success([
                'total'    => $total,
                'active'   => $active,
                'disabled' => $disabled,
                'recent'   => $recent,
            ]);
        } catch (Exception $e) {
            $this->json_error('Failed to load dashboard data.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/get_admins
    // -------------------------------------------------------------------------

    public function get_admins(): void
    {
        $this->_require_role(['Super Admin', 'Admin', 'Principal'], 'admin_users_list');

        try {
            $raw  = $this->firebase->get($this->_admin_base()) ?? [];
            $rows = [];
            foreach ($raw as $aid => $a) {
                if (!is_array($a)) continue;
                $rows[] = $this->_normalize_admin($aid, $a);
            }
            usort($rows, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
            $this->json_success(['admins' => $rows]);
        } catch (Exception $e) {
            $this->json_error('Failed to load admin users.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/create_admin
    // -------------------------------------------------------------------------

    public function create_admin(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'create_admin');

        $admin_id = trim($this->input->post('admin_id',  TRUE) ?? '');
        $name     = trim($this->input->post('name',      TRUE) ?? '');
        $email    = strtolower(trim($this->input->post('email', TRUE) ?? ''));
        $phone    = trim($this->input->post('phone',     TRUE) ?? '');
        $role     = trim($this->input->post('role',       TRUE) ?? '');
        $password = (string)($this->input->post('password', FALSE) ?? '');

        if (empty($admin_id) || empty($name) || empty($email) || empty($role) || empty($password)) {
            $this->json_error('Login ID, name, email, role, and password are required.');
            return;
        }
        // Validate admin_id format (same rules as Admin_login::_is_safe_id)
        if (!preg_match('/^[A-Za-z0-9_\-]+$/', $admin_id)) {
            $this->json_error('Login ID must contain only letters, numbers, hyphens, and underscores.');
            return;
        }
        if (strlen($admin_id) > 32) {
            $this->json_error('Login ID must be 32 characters or less.');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json_error('Invalid email address.');
            return;
        }
        if (strlen($password) < 8) {
            $this->json_error('Password must be at least 8 characters.');
            return;
        }
        if (strlen($password) > 72) {
            $this->json_error('Password must be 72 characters or less.');
            return;
        }

        $admin_id = $this->safe_path_segment($admin_id, 'admin_id');
        $role     = $this->safe_path_segment($role, 'role');
        $base     = $this->_admin_base();
        $school   = $this->school_name;

        try {
            // Verify the role exists
            $role_data = $this->firebase->get("Schools/{$school}/Roles/{$role}");
            if (empty($role_data)) {
                $this->_seed_default_roles($school);
                $role_data = $this->firebase->get("Schools/{$school}/Roles/{$role}");
                if (empty($role_data)) {
                    $this->json_error("Role '{$role}' does not exist.");
                    return;
                }
            }

            // Check if admin_id already exists
            $existing = $this->firebase->get("{$base}/{$admin_id}");
            if (!empty($existing) && is_array($existing)) {
                $this->json_error("An admin with Login ID '{$admin_id}' already exists.");
                return;
            }

            // Check duplicate email across all admins
            $all_admins = $this->firebase->get($base) ?? [];
            foreach ($all_admins as $a) {
                if (is_array($a) && strtolower($a['Profile']['email'] ?? '') === $email) {
                    $this->json_error('An admin with this email already exists.');
                    return;
                }
            }

            $hashed_pw = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $now       = time();

            // Schema matches Superadmin_schools.php onboarding + Admin_login.php expectations
            $admin_data = [
                'Status'      => 'Active',
                'Role'        => $role,
                'Name'        => $name,
                'Credentials' => ['Password' => $hashed_pw],
                'Profile'     => [
                    'name'        => $name,
                    'email'       => $email,
                    'phone'       => $phone,
                    'role'        => $role,
                    'school'      => $this->school_display_name,
                    'school_id'   => $this->school_code,
                    'firebase_id' => $this->school_id,
                    'created_at'  => $now,
                    'createdBy'   => $this->admin_id,
                ],
                'AccessHistory' => ['LoginAttempts' => 0],
            ];

            $this->firebase->set("{$base}/{$admin_id}", $admin_data);

            log_audit('AdminUsers', 'create_admin', $admin_id, "Created admin '{$name}' with role '{$role}'");

            $this->json_success([
                'message'  => "Admin '{$name}' created successfully. Login ID: {$admin_id}",
                'admin_id' => $admin_id,
            ]);
        } catch (Exception $e) {
            log_message('error', 'AdminUsers::create_admin - ' . $e->getMessage());
            $this->json_error('Failed to create admin user.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/update_admin
    // -------------------------------------------------------------------------

    public function update_admin(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'update_admin');

        $admin_id = trim($this->input->post('admin_id', TRUE) ?? '');
        $name     = trim($this->input->post('name',     TRUE) ?? '');
        $email    = strtolower(trim($this->input->post('email', TRUE) ?? ''));
        $phone    = trim($this->input->post('phone',    TRUE) ?? '');
        $role     = trim($this->input->post('role',      TRUE) ?? '');

        if (empty($admin_id) || empty($name) || empty($email) || empty($role)) {
            $this->json_error('Admin ID, name, email, and role are required.');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json_error('Invalid email address.');
            return;
        }

        $admin_id = $this->safe_path_segment($admin_id, 'admin_id');
        $role     = $this->safe_path_segment($role, 'role');
        $base     = $this->_admin_base();

        try {
            $existing = $this->firebase->get("{$base}/{$admin_id}");
            if (empty($existing) || !is_array($existing)) {
                $this->json_error('Admin user not found.');
                return;
            }

            // Duplicate email check (exclude self)
            $all = $this->firebase->get($base) ?? [];
            foreach ($all as $aid => $a) {
                if (!is_array($a) || $aid === $admin_id) continue;
                if (strtolower($a['Profile']['email'] ?? '') === $email) {
                    $this->json_error('Another admin already uses this email.');
                    return;
                }
            }

            // Update top-level fields that Admin_login reads
            $this->firebase->update("{$base}/{$admin_id}", [
                'Name' => $name,
                'Role' => $role,
            ]);

            // Update Profile sub-node
            $this->firebase->update("{$base}/{$admin_id}/Profile", [
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'role'      => $role,
                'updatedAt' => time(),
                'updatedBy' => $this->admin_id,
            ]);

            log_audit('AdminUsers', 'update_admin', $admin_id, "Updated admin '{$name}'");

            $this->json_success(['message' => "Admin '{$name}' updated."]);
        } catch (Exception $e) {
            log_message('error', 'AdminUsers::update_admin - ' . $e->getMessage());
            $this->json_error('Failed to update admin user.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/disable_admin
    // -------------------------------------------------------------------------

    public function disable_admin(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'disable_admin');

        $admin_id   = $this->safe_path_segment(trim($this->input->post('admin_id', TRUE) ?? ''), 'admin_id');
        $new_status = trim($this->input->post('status', TRUE) ?? '');

        // Map lowercase view values to the capitalized values Admin_login expects
        $status_map = ['active' => 'Active', 'disabled' => 'Disabled'];
        if (!isset($status_map[$new_status])) {
            $this->json_error('Status must be "active" or "disabled".');
            return;
        }

        // Cannot disable yourself
        if ($admin_id === $this->admin_id) {
            $this->json_error('You cannot change your own status.');
            return;
        }

        $base = $this->_admin_base();
        try {
            $existing = $this->firebase->get("{$base}/{$admin_id}");
            if (empty($existing) || !is_array($existing)) {
                $this->json_error('Admin user not found.');
                return;
            }

            // Admin_login checks: ($adminData['Status'] ?? '') !== 'Active'
            $this->firebase->update("{$base}/{$admin_id}", [
                'Status' => $status_map[$new_status],
            ]);

            $name  = $existing['Name'] ?? $existing['Profile']['name'] ?? $admin_id;
            $label = $new_status === 'active' ? 'enabled' : 'disabled';

            log_audit('AdminUsers', 'toggle_status', $admin_id, "Admin '{$name}' {$label}");

            $this->json_success(['message' => "Admin '{$name}' {$label}."]);
        } catch (Exception $e) {
            $this->json_error('Failed to update admin status.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/delete_admin
    // -------------------------------------------------------------------------

    public function delete_admin(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'delete_admin');

        $admin_id = $this->safe_path_segment(trim($this->input->post('admin_id', TRUE) ?? ''), 'admin_id');

        if ($admin_id === $this->admin_id) {
            $this->json_error('You cannot delete your own account.');
            return;
        }

        $base = $this->_admin_base();
        try {
            $existing = $this->firebase->get("{$base}/{$admin_id}");
            if (empty($existing) || !is_array($existing)) {
                $this->json_error('Admin user not found.');
                return;
            }

            $name = $existing['Name'] ?? $existing['Profile']['name'] ?? $admin_id;
            $this->firebase->delete($base, $admin_id);

            log_audit('AdminUsers', 'delete_admin', $admin_id, "Deleted admin '{$name}'");

            $this->json_success(['message' => "Admin '{$name}' deleted."]);
        } catch (Exception $e) {
            $this->json_error('Failed to delete admin user.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/reset_password
    // -------------------------------------------------------------------------

    public function reset_password(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'reset_password');

        $admin_id     = $this->safe_path_segment(trim($this->input->post('admin_id', TRUE) ?? ''), 'admin_id');
        $new_password = (string)($this->input->post('new_password', FALSE) ?? '');

        if (strlen($new_password) < 8) {
            $this->json_error('Password must be at least 8 characters.');
            return;
        }
        if (strlen($new_password) > 72) {
            $this->json_error('Password must be 72 characters or less.');
            return;
        }

        $base = $this->_admin_base();
        try {
            $existing = $this->firebase->get("{$base}/{$admin_id}");
            if (empty($existing) || !is_array($existing)) {
                $this->json_error('Admin user not found.');
                return;
            }

            // Update Credentials/Password - same nested path Admin_login reads
            $hashed = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->firebase->update("{$base}/{$admin_id}/Credentials", [
                'Password' => $hashed,
            ]);

            $name = $existing['Name'] ?? $existing['Profile']['name'] ?? $admin_id;

            log_audit('AdminUsers', 'reset_password', $admin_id, "Password reset for '{$name}'");

            $this->json_success(['message' => "Password reset for '{$name}'."]);
        } catch (Exception $e) {
            $this->json_error('Failed to reset password.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/get_roles
    // -------------------------------------------------------------------------

    public function get_roles(): void
    {
        $this->_require_role(['Super Admin', 'Admin', 'Principal'], 'view_roles');

        $school = $this->school_name;
        try {
            $raw = $this->firebase->get("Schools/{$school}/Roles") ?? [];
            if (empty($raw) || !is_array($raw)) {
                $this->_seed_default_roles($school);
                $raw = $this->firebase->get("Schools/{$school}/Roles") ?? [];
            }

            $roles = [];
            foreach ($raw as $name => $r) {
                if (!is_array($r)) continue;
                $roles[] = array_merge(['role_name' => $name], $r);
            }

            $this->json_success([
                'roles'   => $roles,
                'modules' => self::AVAILABLE_MODULES,
            ]);
        } catch (Exception $e) {
            $this->json_error('Failed to load roles.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/save_role
    // -------------------------------------------------------------------------

    public function save_role(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'save_role');

        $role_name   = trim($this->input->post('role_name',   TRUE) ?? '');
        $label       = trim($this->input->post('label',        TRUE) ?? '');
        $description = trim($this->input->post('description',  TRUE) ?? '');
        $permissions = $this->input->post('permissions') ?? [];

        if (empty($role_name) || empty($label)) {
            $this->json_error('Role name and label are required.');
            return;
        }

        $role_name = $this->safe_path_segment($role_name, 'role_name');

        if (!is_array($permissions)) $permissions = [];
        // Whitelist permissions against available modules
        $permissions = array_values(array_intersect($permissions, self::AVAILABLE_MODULES));

        $school = $this->school_name;
        try {
            // Check if editing a system role - only allow permission changes
            $existing = $this->firebase->get("Schools/{$school}/Roles/{$role_name}");
            $is_system = is_array($existing) && !empty($existing['is_system']);

            $role_data = [
                'label'       => $label,
                'description' => $description,
                'permissions' => $permissions,
                'updatedAt'   => date('Y-m-d H:i:s'),
                'updatedBy'   => $this->admin_id,
            ];

            if (!$is_system) {
                $role_data['is_system'] = false;
                if (empty($existing)) {
                    $role_data['createdAt'] = date('Y-m-d H:i:s');
                    $role_data['createdBy'] = $this->admin_id;
                }
            }

            $this->firebase->update("Schools/{$school}/Roles/{$role_name}", $role_data);

            // Refresh current admin's cached permissions if their role was just modified
            if ($role_name === $this->admin_role) {
                $this->session->set_userdata('rbac_permissions', $permissions);
            }

            log_audit('AdminUsers', 'save_role', $role_name, "Saved role '{$label}' with " . count($permissions) . " permissions");

            $this->json_success(['message' => "Role '{$label}' saved."]);
        } catch (Exception $e) {
            $this->json_error('Failed to save role.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/delete_role
    // -------------------------------------------------------------------------

    public function delete_role(): void
    {
        $this->_require_role(['Super Admin', 'Admin'], 'delete_role');

        $role_name = $this->safe_path_segment(trim($this->input->post('role_name', TRUE) ?? ''), 'role_name');
        $school    = $this->school_name;

        try {
            $existing = $this->firebase->get("Schools/{$school}/Roles/{$role_name}");
            if (empty($existing) || !is_array($existing)) {
                $this->json_error('Role not found.');
                return;
            }
            if (!empty($existing['is_system'])) {
                $this->json_error('System roles cannot be deleted.');
                return;
            }

            // Check if any admin uses this role
            $admins = $this->firebase->get($this->_admin_base()) ?? [];
            foreach ($admins as $a) {
                if (is_array($a)) {
                    $aRole = $a['Role'] ?? $a['Profile']['role'] ?? '';
                    $aName = $a['Name'] ?? $a['Profile']['name'] ?? '';
                    if ($aRole === $role_name) {
                        $this->json_error("Cannot delete: role is assigned to admin '{$aName}'.");
                        return;
                    }
                }
            }

            $this->firebase->delete("Schools/{$school}/Roles", $role_name);

            log_audit('AdminUsers', 'delete_role', $role_name, "Deleted role '{$role_name}'");

            $this->json_success(['message' => "Role '{$role_name}' deleted."]);
        } catch (Exception $e) {
            $this->json_error('Failed to delete role.');
        }
    }

    // -------------------------------------------------------------------------
    // POST  /admin_users/get_login_logs
    // Aggregates AccessHistory from each admin record (no centralized log exists)
    // -------------------------------------------------------------------------

    public function get_login_logs(): void
    {
        $this->_require_role(['Super Admin', 'Admin', 'Principal'], 'view_login_logs');

        try {
            $admins = $this->firebase->get($this->_admin_base()) ?? [];
            $rows = [];

            foreach ($admins as $aid => $a) {
                if (!is_array($a)) continue;
                $access    = $a['AccessHistory'] ?? [];
                $lastLogin = $access['LastLogin'] ?? '';
                if (empty($lastLogin)) continue;

                $rows[] = [
                    'adminId'   => $aid,
                    'adminName' => $a['Name'] ?? $a['Profile']['name'] ?? $aid,
                    'loginTime' => $lastLogin,
                    'ipAddress' => $access['LoginIP'] ?? '',
                    'status'    => 'success',
                    'device'    => '-',
                    'isOnline'  => !empty($access['IsLoggedIn']),
                ];
            }

            usort($rows, fn($a, $b) => strcmp($b['loginTime'] ?? '', $a['loginTime'] ?? ''));

            $this->json_success([
                'logs'  => $rows,
                'total' => count($rows),
            ]);
        } catch (Exception $e) {
            $this->json_error('Failed to load login logs.');
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE: Seed default roles if none exist
    // -------------------------------------------------------------------------

    private function _seed_default_roles(string $school): void
    {
        try {
            foreach (self::DEFAULT_ROLES as $name => $config) {
                $this->firebase->set("Schools/{$school}/Roles/{$name}", array_merge($config, [
                    'createdAt' => date('Y-m-d H:i:s'),
                    'createdBy' => 'system',
                ]));
            }
        } catch (Exception $e) {
            log_message('error', 'AdminUsers: Failed to seed default roles - ' . $e->getMessage());
        }
    }
}
