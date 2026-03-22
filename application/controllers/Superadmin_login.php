<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Superadmin_login
 *
 * Authenticates against the existing admin credential store:
 *   Users/Admin/{school_id}/{admin_id}
 *
 * Access is granted only when Role === 'Super Admin'.
 * Rate limiting stored in Firebase at RateLimit/SA/{ip} (no MySQL needed).
 */
class Superadmin_login extends CI_Controller
{
    private const DUMMY_HASH    = '$2y$10$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I/p7';
    private const MAX_ID_LEN    = 32;
    private const MAX_SCHOOL_LEN= 16;
    private const MAX_PW_LEN    = 72;
    private const IP_MAX_FAILS  = 10;
    private const IP_WINDOW_SEC = 1800; // 30 minutes

    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'firebase']);
        $this->load->helper('url');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/csrf_token
    // Returns the correct CSRF token for the current context:
    //   • SA session active  → session-based sa_csrf_token (used by MY_Superadmin_Controller)
    //   • No SA session      → CI3 cookie token (for the login form POST)
    //
    // The SA panel uses session-based CSRF to avoid cookie collision with the
    // school-admin panel.  Test runners must call this endpoint after login
    // (not before) to receive the session token.
    // ─────────────────────────────────────────────────────────────────────────
    public function csrf_token()
    {
        $sa_id = $this->session->userdata('sa_id');

        if (!empty($sa_id)) {
            // SA session active — generate/fetch the session-based CSRF token.
            // Mirrors the same initialisation logic in MY_Superadmin_Controller
            // so calling this endpoint is equivalent to loading any SA page.
            if (!$this->session->userdata('sa_csrf_token')) {
                $this->session->set_userdata('sa_csrf_token', bin2hex(random_bytes(32)));
            }
            $token = $this->session->userdata('sa_csrf_token');
            $name  = 'csrf_token';
        } else {
            // No SA session — return CI3 cookie token for the login form.
            $token = $this->security->get_csrf_hash();
            $name  = $this->security->get_csrf_token_name();
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode([
                 'csrf_name'  => $name,
                 'csrf_token' => $token,
             ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/login
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        if ($this->session->userdata('sa_id')) {
            redirect('superadmin/dashboard');
            return;
        }
        $this->load->view('superadmin/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/login/authenticate
    // ─────────────────────────────────────────────────────────────────────────
    public function authenticate()
    {
        if ($this->input->method() !== 'post') {
            redirect('superadmin/login');
            return;
        }

        // Manual CSRF check — CI3's cookie-based CSRF is excluded for all SA
        // routes because the school-admin panel shares the same cookie and
        // overwrites the token, causing 403 on the SA login form.
        $csrfName  = $this->security->get_csrf_token_name();
        $csrfSent  = trim((string) $this->input->post($csrfName));
        $csrfCookie = trim((string) ($this->input->cookie($csrfName) ?? ''));
        // Accept if the submitted token matches EITHER the cookie or the current hash
        $csrfHash  = $this->security->get_csrf_hash();
        if ($csrfSent === '' || ($csrfSent !== $csrfCookie && $csrfSent !== $csrfHash)) {
            $this->_json(['status' => 'error', 'message' => 'Security token expired. Please refresh the page and try again.']);
            return;
        }

        $ip  = $this->input->ip_address();
        $ip  = ($ip === '::1') ? '127.0.0.1' : $ip;
        $now = time();

        // ── Rate limit check (Firebase) ──────────────────────────────────────
        if ($this->_is_ip_blocked($ip, $now)) {
            $this->_json(['status' => 'error', 'message' => 'Too many failed attempts. Try again in 30 minutes.']);
            return;
        }

        // ── Read + validate inputs ───────────────────────────────────────────
        $adminId  = trim((string) $this->input->post('admin_id',  TRUE));
        $schoolId = trim((string) $this->input->post('school_id', TRUE));
        $password = (string) $this->input->post('password', FALSE);  // R5-SEC-1: bypass XSS filter for passwords

        if ($adminId === '' || $schoolId === '' || $password === '') {
            $this->_json(['status' => 'error', 'message' => 'All fields are required.']);
            return;
        }

        if (strlen($adminId) > self::MAX_ID_LEN || strlen($schoolId) > self::MAX_SCHOOL_LEN || strlen($password) > self::MAX_PW_LEN) {
            $this->_record_fail($ip, $now);
            $this->_json(['status' => 'error', 'message' => 'Invalid credentials.']);
            return;
        }

        // Firebase path injection guard — block dangerous chars (. # $ [ ] /)
        // Spaces are allowed to support "Our Panel" namespace
        if (preg_match('/[.#$\[\]\/]/', $adminId) || preg_match('/[.#$\[\]\/]/', $schoolId)) {
            $this->_record_fail($ip, $now);
            $this->_json(['status' => 'error', 'message' => 'Invalid credentials.']);
            return;
        }

        // ── Determine user type ───────────────────────────────────────────────
        // Type A: Developer / Manager  → school_id = "Our Panel"  (no role check needed)
        // Type B: School Super Admin   → any other school_id, Role must = "Super Admin"
        $isDevPanel = (trim($schoolId) === 'Our Panel');

        // ── Authenticate via MongoDB (Node.js Auth API) ──────────────────
        $adminData     = null;
        $valid         = false;
        $authSource    = 'mongodb';
        $firebaseProfile = null;

        $this->load->library('auth_client');
        $result = $this->auth_client->web_login($adminId, '', $password, $ip);

        if (!empty($result['success'])) {
            $valid = true;
            $userData = $result['user'] ?? [];
            $firebaseProfile = $result['firebaseProfile'] ?? [];

            $adminData = [
                'Name'   => $userData['name'] ?? $firebaseProfile['Name'] ?? $adminId,
                'Email'  => $userData['email'] ?? $firebaseProfile['Email'] ?? '',
                'Role'   => $firebaseProfile['Role'] ?? $userData['role'] ?? 'Super Admin',
                'Status' => $firebaseProfile['Status'] ?? 'Active',
                'Credentials' => $firebaseProfile['Credentials'] ?? [],
                'Profile'     => $firebaseProfile['Profile'] ?? [],
                'AccessHistory' => $firebaseProfile['AccessHistory'] ?? [],
            ];
        } else if (!empty($result['unavailable'])) {
            // Auth API down — fall back to Firebase
            $authSource = 'firebase';
            try {
                $raw = $this->firebase->get("Users/Admin/{$schoolId}/{$adminId}");
                $adminData = is_array($raw) ? $raw : null;
            } catch (Exception $e) {
                log_message('error', 'SA Login: Firebase error — ' . $e->getMessage());
            }

            if ($adminData !== null) {
                $storedHash = (string) ($adminData['Credentials']['Password']
                           ?? $adminData['Password']
                           ?? self::DUMMY_HASH);
                $valid = password_verify($password, $storedHash);
            } else {
                password_verify($password, self::DUMMY_HASH);
            }
        }

        if (!$valid) {
            $this->_record_fail($ip, $now);
            $this->_json(['status' => 'error', 'message' => 'Invalid credentials.']);
            return;
        }

        // ── Role / namespace check ────────────────────────────────────────────
        if (!$isDevPanel) {
            $role = (string) ($adminData['Role'] ?? '');
            if (strtolower(trim($role)) !== 'super admin') {
                $this->_record_fail($ip, $now);
                $this->_json(['status' => 'error', 'message' => 'Access denied. Super Admin role required.']);
                return;
            }
        }

        // ── Account status check ─────────────────────────────────────────────
        $status = (string) ($adminData['Status'] ?? 'Active');
        if (!empty($adminData['Status']) && $status !== 'Active') {
            $this->_json(['status' => 'error', 'message' => 'Account is inactive. Contact support.']);
            return;
        }

        // ── Success ───────────────────────────────────────────────────────────
        $this->_clear_fail($ip);

        // Clear any school admin session data to prevent session bleed-through
        $this->session->unset_userdata([
            'admin_id', 'school_id', 'school_code', 'admin_role', 'admin_name',
            'session', 'current_session', 'session_year', 'schoolName',
            'school_display_name', 'school_features', 'available_sessions',
            'subscription_expiry', 'subscription_grace_end', 'subscription_warning',
            'sub_check_ts', 'login_csrf',
        ]);

        $this->session->sess_regenerate(TRUE);

        // Resolve name + email — developer records may use different field names
        $resolvedName  = (string) ($adminData['Name']  ?? $adminData['name']  ?? $adminId);
        $resolvedEmail = (string) ($adminData['Email'] ?? $adminData['email'] ?? '');
        $resolvedRole  = $isDevPanel ? 'developer' : 'superadmin';

        $this->session->set_userdata([
            'sa_id'    => $adminId,
            'sa_name'  => $resolvedName,
            'sa_role'  => $resolvedRole,
            'sa_email' => $resolvedEmail,
        ]);

        try {
            $updatePath = $isDevPanel
                ? "Users/Admin/Our Panel/{$adminId}"
                : "Users/Admin/{$schoolId}/{$adminId}/AccessHistory";
            $this->firebase->update($updatePath, [
                'SA_LastLogin'   => date('Y-m-d H:i:s'),
                'SA_LastLoginIP' => $ip,
            ]);
        } catch (Exception $e) { /* non-critical */ }

        $this->_json(['status' => 'success', 'redirect' => base_url('superadmin/dashboard')]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/login/forgot_password
    // ─────────────────────────────────────────────────────────────────────────
    public function forgot_password()
    {
        $this->load->view('superadmin/forgot_password');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/login/send_otp
    // ─────────────────────────────────────────────────────────────────────────
    public function send_otp()
    {
        if ($this->input->method() !== 'post') { redirect('superadmin/login'); return; }

        $adminId = trim((string) $this->input->post('admin_id', TRUE));
        if (empty($adminId)) {
            $this->_json(['status' => 'error', 'message' => 'Admin ID is required.']);
            return;
        }

        $this->load->library('auth_client');
        $result = $this->auth_client->forgot_password($adminId);

        $this->_json([
            'status'       => !empty($result['success']) ? 'success' : 'error',
            'message'      => $result['message'] ?? 'Request failed.',
            'email_masked' => $result['email_masked'] ?? '',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/login/verify_otp
    // ─────────────────────────────────────────────────────────────────────────
    public function verify_otp()
    {
        if ($this->input->method() !== 'post') { redirect('superadmin/login'); return; }

        $adminId = trim((string) $this->input->post('admin_id', TRUE));
        $otp     = trim((string) $this->input->post('otp', TRUE));

        if (empty($adminId) || empty($otp)) {
            $this->_json(['status' => 'error', 'message' => 'Admin ID and OTP are required.']);
            return;
        }

        $this->load->library('auth_client');
        $result = $this->auth_client->verify_otp($adminId, $otp);

        $this->_json([
            'status'      => !empty($result['success']) ? 'success' : 'error',
            'message'     => $result['message'] ?? 'Verification failed.',
            'resetToken'  => $result['resetToken'] ?? '',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/login/reset_password
    // ─────────────────────────────────────────────────────────────────────────
    public function reset_password()
    {
        if ($this->input->method() !== 'post') { redirect('superadmin/login'); return; }

        $adminId      = trim((string) $this->input->post('admin_id', TRUE));
        $resetToken   = trim((string) $this->input->post('reset_token', TRUE));
        $newPassword  = (string) $this->input->post('new_password', FALSE);

        if (empty($adminId) || empty($resetToken) || empty($newPassword)) {
            $this->_json(['status' => 'error', 'message' => 'All fields are required.']);
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->_json(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
            return;
        }

        $this->load->library('auth_client');
        $result = $this->auth_client->reset_password_otp($adminId, $resetToken, $newPassword);

        $this->_json([
            'status'  => !empty($result['success']) ? 'success' : 'error',
            'message' => $result['message'] ?? 'Password reset failed.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/login/logout
    // ─────────────────────────────────────────────────────────────────────────
    public function logout()
    {
        $this->session->unset_userdata(['sa_id', 'sa_name', 'sa_role', 'sa_email', 'sa_csrf_token']);
        $this->session->sess_destroy();
        // Prevent browser back-button cache from restoring the session
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        redirect('superadmin/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE — Firebase-based rate limiting (no MySQL dependency)
    // ─────────────────────────────────────────────────────────────────────────

    private function _ip_path(string $ip): string
    {
        return 'RateLimit/SA/' . str_replace(['.', ':'], '-', $ip);
    }

    private function _is_ip_blocked(string $ip, int $now): bool
    {
        try {
            $rec = $this->firebase->get($this->_ip_path($ip));
            if (!is_array($rec)) return false;
            if (($now - (int)($rec['windowStart'] ?? 0)) > self::IP_WINDOW_SEC) return false;
            return (int)($rec['fails'] ?? 0) >= self::IP_MAX_FAILS;
        } catch (Exception $e) { return false; }
    }

    private function _record_fail(string $ip, int $now): void
    {
        try {
            $path = $this->_ip_path($ip);
            $rec  = $this->firebase->get($path);
            if (!is_array($rec) || ($now - (int)($rec['windowStart'] ?? 0)) > self::IP_WINDOW_SEC) {
                $this->firebase->update($path, ['windowStart' => $now, 'fails' => 1]);
            } else {
                $this->firebase->update($path, ['fails' => (int)($rec['fails'] ?? 0) + 1]);
            }
        } catch (Exception $e) { /* non-critical */ }
    }

    private function _clear_fail(string $ip): void
    {
        try {
            $this->firebase->update($this->_ip_path($ip), ['fails' => 0, 'windowStart' => 0]);
        } catch (Exception $e) {}
    }

    private function _json(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
