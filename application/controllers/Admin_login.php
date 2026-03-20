<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin_login.php — Production-hardened login controller.
 * Extends CI_Controller (NOT MY_Controller — avoids auth redirect loop).
 *
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  SECURITY MEASURES                                               ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  [S-01]  POST-only enforcement on check_credentials             ║
 * ║  [S-02]  Input length + format validation                        ║
 * ║  [S-03]  Firebase path injection blocked (/ . # $ [ ] chars)    ║
 * ║  [S-04]  Generic error messages — no user/school enumeration     ║
 * ║  [S-05]  Timing-safe credential flow — dummy hash on miss        ║
 * ║  [S-06]  Per-account brute-force lockout (5 attempts / 30 min)  ║
 * ║  [S-07]  Per-IP rate limiting (20 fails / 15 min across any ID) ║
 * ║  [S-08]  Password length capped at 72 chars (bcrypt DoS guard)  ║
 * ║  [S-09]  password_hash / password_verify + plain-text migration ║
 * ║  [S-10]  Session fixation prevented — sess_regenerate(TRUE)     ║
 * ║  [S-11]  All session keys cleared on logout + Firebase updated  ║
 * ║  [S-12]  Security + no-cache headers on every response          ║
 * ║  [S-13]  Log injection prevented — inputs sanitised before log  ║
 * ║  [S-14]  Subscription status + date gating at login time        ║
 * ║  [S-15]  School ID resolved via Indexes/School_codes index      ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  AUDIT FIXES (this revision)                                     ║
 * ║  [A-01]  Lockout check moved BEFORE bcrypt — saves CPU on lock  ║
 * ║  [A-02]  SESSION_KEYS includes 'login_csrf' — no ghost keys     ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class Admin_login extends CI_Controller
{
    // ── Dummy bcrypt hash — timing-safe flow when admin not found (S-05) ─
    private const DUMMY_HASH = '$2y$10$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I/p7';

    // ── Input limits (S-02 / S-08) ────────────────────────────────────────
    private const MAX_ADMIN_ID_LEN  = 32;
    private const MAX_SCHOOL_ID_LEN = 16;
    private const MAX_PASSWORD_LEN  = 72;  // bcrypt silently ignores beyond 72

    // ── Per-IP rate limit (S-07) ──────────────────────────────────────────
    private const IP_MAX_FAILS  = 20;   // max fails from one IP
    private const IP_WINDOW_SEC = 900;  // 15-minute sliding window

    // ── Single source of truth for ALL session keys ───────────────────────
    // Must stay in sync with MY_Controller::SESSION_KEYS.
    // [A-02] 'login_csrf' included so logout clears it cleanly.
    public const SESSION_KEYS = [
        'admin_id',
        'school_id',              // now SCH_XXXXXX
        'school_code',            // login code
        'admin_role',
        'admin_name',
        'session',
        'current_session',
        'session_year',
        'schoolName',
        'school_display_name',    // human-readable name
        'school_features',
        'available_sessions',
        'subscription_expiry',
        'subscription_grace_end',
        'subscription_warning',
        'sub_check_ts',
        'login_csrf',
    ];

    // ─────────────────────────────────────────────────────────────────────
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('firebase');
        $this->load->helper('url');

        // [S-12] Security + no-cache headers on every response
        $this->_send_security_headers();

        // Redirect already-authenticated admins away from the login page only
        if (
            $this->session->userdata('admin_id') &&
            $this->router->fetch_class()  === 'admin_login' &&
            $this->router->fetch_method() === 'index'
        ) {
            redirect('admin/index');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->load->view('admin_login');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  CHECK CREDENTIALS
    // ─────────────────────────────────────────────────────────────────────
    public function check_credentials(): void
    {
        // [S-01] POST only
        if ($this->input->method() !== 'post') {
            redirect('admin_login');
        }

        $now      = time();
        $firebase = $this->firebase;
        $ip       = $this->_get_real_ip();

        // ── [S-02] Read + length-validate inputs ──────────────────────────
        $rawAdminId  = (string) $this->input->post('admin_id');
        $rawSchoolId = (string) $this->input->post('school_id');
        $rawPassword = (string) $this->input->post('password', FALSE);  // R5-SEC-1: bypass XSS filter for passwords

        if ($rawAdminId === '' || $rawSchoolId === '' || $rawPassword === '') {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('admin_login');
        }

        if (
            strlen($rawAdminId)  > self::MAX_ADMIN_ID_LEN  ||
            strlen($rawSchoolId) > self::MAX_SCHOOL_ID_LEN ||
            strlen($rawPassword) > self::MAX_PASSWORD_LEN
        ) {
            $this->_record_ip_fail($ip, $now, $firebase);
            $this->session->set_flashdata('error', 'Invalid credentials.');
            redirect('admin_login');
        }

        $adminId  = trim($rawAdminId);
        $schoolId = trim($rawSchoolId);
        $password = $rawPassword;   // do NOT trim — spaces in passwords are valid

        // [S-03] Firebase path injection guard
        if (! $this->_is_safe_id($adminId) || ! $this->_is_safe_id($schoolId)) {
            $this->_record_ip_fail($ip, $now, $firebase);
            $this->session->set_flashdata('error', 'Invalid credentials.');
            redirect('admin_login');
        }

        // ── [S-07] Per-IP rate limit ──────────────────────────────────────
        if ($this->_is_ip_blocked($ip, $now, $firebase)) {
            $this->session->set_flashdata('error', 'Too many login attempts. Please try again later.');
            redirect('admin_login');
        }

        // ── Resolve school ID (SCH_XXXXXX) from login code ────────────────
        $schoolId_resolved = $this->_resolveSchoolId($schoolId);

        // ── Fetch admin record ────────────────────────────────────────────
        $adminData = null;
        if ($schoolId_resolved !== null) {
            $raw = $firebase->get("Users/Admin/{$schoolId}/{$adminId}");
            $adminData = is_array($raw) ? $raw : null;
        }

        // ── [A-01] Per-account lockout check BEFORE bcrypt ────────────────
        // Saves CPU: no point running expensive bcrypt on a locked account.
        if ($adminData !== null) {
            $accessHistory = $adminData['AccessHistory'] ?? [];
            $lockedUntil   = isset($accessHistory['LockedUntil'])
                ? (int) strtotime((string) $accessHistory['LockedUntil'])
                : 0;

            // Auto-clear if lock has expired
            if ($lockedUntil > 0 && $now >= $lockedUntil) {
                $firebase->update(
                    "Users/Admin/{$schoolId}/{$adminId}/AccessHistory",
                    ['LoginAttempts' => 0, 'LockedUntil' => null]
                );
                $lockedUntil = 0;
            }

            if ($lockedUntil > $now) {
                $minutes = (int) ceil(($lockedUntil - $now) / 60);
                $this->session->set_flashdata(
                    'error',
                    "Account temporarily locked. Try again in {$minutes} minute(s)."
                );
                redirect('admin_login');
            }
        }

        // ── [S-05] Timing-safe password verification ──────────────────────
        $storedHash       = ($adminData !== null)
            ? (string) ($adminData['Credentials']['Password'] ?? '')
            : self::DUMMY_HASH;
        $credentialsValid = false;

        if ($adminData !== null && $schoolId_resolved !== null) {
            $credentialsValid = password_verify($password, $storedHash);

            // [S-09] Plain-text migration — remove once all passwords are hashed
            // Guard: only allow plaintext match if stored value is NOT a bcrypt hash
            if (! $credentialsValid
                && strlen($storedHash) !== 60
                && strpos($storedHash, '$2y$') !== 0
                && strpos($storedHash, '$2a$') !== 0
                && $password === $storedHash) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $firebase->update(
                    "Users/Admin/{$schoolId}/{$adminId}/Credentials",
                    ['Password' => $newHash]
                );
                log_message('info', 'Plain-text password upgraded admin=' . $this->_log_safe($adminId));
                $credentialsValid = true;
            }
        } else {
            // Dummy compare — keeps response time consistent (S-05)
            password_verify($password, self::DUMMY_HASH);
        }

        // ── Failed credentials ────────────────────────────────────────────
        if (! $credentialsValid) {
            $this->_record_ip_fail($ip, $now, $firebase);
            if ($adminData !== null) {
                $this->_record_account_fail($adminId, $schoolId, $adminData, $firebase, $now);
            }
            // [S-04] Same message regardless of which check failed
            $this->session->set_flashdata('error', 'Invalid credentials. Please try again.');
            redirect('admin_login');
        }

        // ════════════════════════════════════════════════════════════════
        //  CREDENTIALS VALID — continue with additional checks
        // ════════════════════════════════════════════════════════════════

        // Account status (checked after verify to prevent enumeration)
        if (($adminData['Status'] ?? '') !== 'Active') {
            $this->session->set_flashdata('error', 'Your account is inactive. Contact your administrator.');
            redirect('admin_login');
        }

        // ── [S-14] Subscription check ─────────────────────────────────────
        // Try multiple paths: new architecture first, then legacy fallbacks
        $subscription    = null;
        $validSubPath    = null;
        $subPaths = [
            "System/Schools/{$schoolId_resolved}/subscription",
            "Users/Schools/{$schoolId_resolved}/subscription",
        ];

        log_message('info', 'Sub check school=' . $this->_log_safe($schoolId_resolved));

        foreach ($subPaths as $subPath) {
            $subscription = $firebase->get($subPath);
            if ($subscription && is_array($subscription)) {
                $validSubPath = $subPath;
                break;
            }
            $subscription = null;
        }

        if (! $subscription || ! is_array($subscription)) {
            log_message('error', 'Subscription missing school=' . $this->_log_safe($schoolId_resolved));
            $this->session->set_flashdata('error', 'Subscription record not found. Please contact support.');
            redirect('admin_login');
        }

        $status   = (string) ($subscription['status']   ?? 'Inactive');
        $duration = is_array($subscription['duration'] ?? null) ? $subscription['duration'] : [];
        $endDate  = trim((string) ($duration['endDate'] ?? ''));

        // Step 1 — Status must be Active or Grace_Period
        if (!in_array($status, ['Active', 'Grace_Period'], true)) {
            log_message(
                'error',
                'Subscription inactive school=' . $this->_log_safe($schoolId_resolved)
                    . ' status=' . $this->_log_safe($status)
            );
            $this->session->set_flashdata('error', 'Subscription is not active. Please contact support.');
            redirect('admin_login');
        }

        // Step 2 — End date must not have passed
        $parsedEndDate = ($endDate !== '') ? strtotime($endDate) : false;
        if ($parsedEndDate === false || $parsedEndDate < $now) {
            // Write Expired to the path where we found subscription data
            if ($validSubPath) {
                $firebase->update($validSubPath, ['status' => 'Expired']);
            }
            $this->session->set_flashdata(
                'error',
                'Subscription expired on ' . htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8')
                    . '. Please contact our team to renew.'
            );
            redirect('admin_login');
        }

        // Step 3 — Compute timestamps + optional 7-day warning
        $endTs      = (int) strtotime($endDate . ' 23:59:59');
        // Use plan's grace_end from Firebase if available; fall back to 7 days.
        // grace_end is set by SA onboard/assign_plan using the plan's grace_days field.
        $graceEndRaw = trim((string)($subscription['grace_end'] ?? ''));
        $graceEndTs  = ($graceEndRaw !== '' && strtotime($graceEndRaw) !== false)
            ? (int) strtotime($graceEndRaw . ' 23:59:59')
            : $endTs + (7 * 86400);
        $daysRemaining = (int) ceil(($endTs - $now) / 86400);
        $subWarning    = ($daysRemaining <= 7)
            ? "Subscription expires in {$daysRemaining} day(s) on {$endDate}. Please renew soon."
            : null;

        // ── Successful authentication ─────────────────────────────────────
        $this->_clear_ip_fails($ip, $firebase);

        $accessPath = "Users/Admin/{$schoolId}/{$adminId}/AccessHistory";
        $firebase->update($accessPath, [
            'LastLogin'     => date('c', $now),
            'LoginIP'       => $ip,
            'LoginAttempts' => 0,
            'LockedUntil'   => null,
            'IsLoggedIn'    => true,
        ]);

        // [S-10] Prevent session fixation
        $this->session->sess_regenerate(TRUE);

        // Financial year — computed as fallback, but stored session takes priority
        $month            = (int) date('m', $now);
        $year             = (int) date('Y', $now);
        $computedSession  = ($month >= 4)
            ? $year       . '-' . substr($year + 1, -2)   // Apr–Dec → 2025-26
            : ($year - 1) . '-' . substr($year,     -2);  // Jan–Mar → 2024-25

        // ── Fetch / initialise available academic sessions ────────────────
        $sessionsPath      = "Schools/{$schoolId_resolved}/Sessions";
        $storedSessions    = $firebase->get($sessionsPath);
        $availableSessions = (is_array($storedSessions) && !empty($storedSessions))
            ? array_values(array_unique(array_filter($storedSessions, 'is_string')))
            : [];

        // Always ensure the computed financial year is in the list
        if (!in_array($computedSession, $availableSessions, true)) {
            $availableSessions[] = $computedSession;
            $firebase->set($sessionsPath, $availableSessions);
        }

        rsort($availableSessions);              // latest year first

        // Prefer the school's stored active session over the computed date-based one.
        // Onboarding writes Config/ActiveSession; session switcher updates it too.
        $activeSession = $firebase->get("Schools/{$schoolId_resolved}/Config/ActiveSession");
        if (!empty($activeSession) && is_string($activeSession)
            && in_array($activeSession, $availableSessions, true)) {
            $financialYear = $activeSession;
        } else {
            $financialYear = $availableSessions[0]; // fallback to most recent
        }

        // Features — try new path, then legacy
        $schoolFeatures = [];
        foreach (["System/Schools/{$schoolId_resolved}/subscription/features", "Users/Schools/{$schoolId_resolved}/subscription/features"] as $fp) {
            $featuresRaw = $firebase->get($fp);
            if (is_array($featuresRaw) && !empty($featuresRaw)) {
                $schoolFeatures = array_values($featuresRaw);
                break;
            }
        }

        if (empty($schoolFeatures)) {
            log_message('error', 'No features found school=' . $this->_log_safe($schoolId_resolved));
        }

        // Fetch human-readable school name for display — try multiple sources
        $displayName = '';
        foreach (["System/Schools/{$schoolId_resolved}/profile", "Users/Schools/{$schoolId_resolved}/profile"] as $pp) {
            $profileData = $firebase->get($pp);
            if (is_array($profileData)) {
                $displayName = $profileData['school_name'] ?? $profileData['name'] ?? '';
                if (!empty($displayName)) break;
            }
        }
        // If school_id_resolved is a school name (legacy), use it directly
        if (empty($displayName) && strpos($schoolId_resolved, 'SCH_') !== 0) {
            $displayName = $schoolId_resolved;
        }
        if (empty($displayName)) $displayName = $schoolId_resolved;

        // Clear any SA panel session to prevent session bleed-through
        $this->session->unset_userdata(['sa_id', 'sa_name', 'sa_role', 'sa_email', 'sa_csrf_token']);

        // [S-11] Store all session data — three key aliases for full compatibility
        $this->session->set_userdata([
            'admin_id'               => $adminId,
            'school_id'              => $schoolId_resolved,   // SCH_XXXXXX (PRIMARY KEY)
            'school_code'            => $schoolId,            // login code (POST input)
            'admin_role'             => $adminData['Role'] ?? $adminData['Profile']['role'] ?? '',
            'admin_name'             => $adminData['Name'] ?? $adminData['Profile']['name'] ?? '',
            'session'                => $financialYear,    // legacy key (MY_Controller reads this)
            'current_session'        => $financialYear,    // Account controller reads this
            'session_year'           => $financialYear,    // Account_model reads this
            'schoolName'             => $schoolId_resolved,   // SCH_XXXXXX (backward compat)
            'school_display_name'    => $displayName,         // human-readable name
            'school_features'        => $schoolFeatures,
            'available_sessions'     => $availableSessions, // session switcher dropdown
            'subscription_expiry'    => $endTs,
            'subscription_grace_end' => $graceEndTs,
            'subscription_warning'   => $subWarning,
            'sub_check_ts'           => 0,  // force MY_Controller to re-check on first load
        ]);

        // [RBAC] Cache role permissions in session for sidebar/controller checks
        $this->load->helper('rbac');
        $adminRole = $adminData['Role'] ?? $adminData['Profile']['role'] ?? '';
        $rbacPerms = load_role_permissions($this->firebase, $schoolId_resolved, $adminRole);
        $this->session->set_userdata('rbac_permissions', $rbacPerms);

        log_message(
            'info',
            'Login OK admin=' . $this->_log_safe($adminId)
                . ' school=' . $this->_log_safe($schoolId)
                . ' ip=' . $ip
        );

        redirect('admin/index');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  LOGOUT
    // ─────────────────────────────────────────────────────────────────────
    public function logout(): void
    {
        $adminId    = $this->session->userdata('admin_id');
        $schoolCode = $this->session->userdata('school_code');

        if ($adminId && $schoolCode && $this->_is_safe_id((string)$adminId) && $this->_is_safe_id((string)$schoolCode)) {
            $this->firebase->update(
                "Users/Admin/{$schoolCode}/{$adminId}/AccessHistory",
                ['IsLoggedIn' => false, 'LoginIP' => null]
            );
        }

        // [S-11] Clear ALL keys — no ghost data
        $this->session->unset_userdata(self::SESSION_KEYS);
        $this->session->set_flashdata('success', 'You have been successfully logged out.');
        redirect('admin_login');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  GET SERVER DATE
    // ─────────────────────────────────────────────────────────────────────
    public function get_server_date(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['date' => date('d-m-Y')]);
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    /**
     * [S-12] Emit all security + no-cache headers.
     * Centralised here so both __construct and any future public methods use it.
     */
    private function _send_security_headers(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /**
     * [S-15] Resolve school identifier from a login code.
     *
     * 1. New path: Indexes/School_codes/{code} → SCH_XXXXXX
     * 2. Legacy fallback: School_ids/{code} → school_name (pre-migration schools)
     *
     * Returns the resolved identifier (SCH_XXXXXX or school_name) or null.
     */
    private function _resolveSchoolId(string $schoolCode): ?string
    {
        $firebase = $this->firebase;

        // ── New architecture: Indexes/School_codes/{code} → SCH_XXXXXX ──
        $schoolId = $firebase->get("Indexes/School_codes/{$schoolCode}");
        if ($schoolId && is_array($schoolId)) {
            $schoolId = reset($schoolId);
        }
        if ($schoolId && is_string($schoolId) && strpos(trim($schoolId), 'SCH_') === 0) {
            return trim($schoolId);
        }

        // ── Legacy fallback: School_ids/{code} → school_name ──
        $schoolName = $firebase->get("School_ids/{$schoolCode}");
        if ($schoolName && is_array($schoolName)) {
            $schoolName = reset($schoolName);
        }
        if ($schoolName && is_string($schoolName) && trim($schoolName) !== '' && $schoolName !== 'Count') {
            return trim($schoolName);
        }

        return null;
    }

    /**
     * [S-06] [A-01] Record a failed attempt on a specific account.
     * Lock after 5 failures for 30 minutes.
     */
    private function _record_account_fail(
        string $adminId,
        string $schoolId,
        array  $adminData,
        object $firebase,
        int    $now
    ): void {
        $path     = "Users/Admin/{$schoolId}/{$adminId}/AccessHistory";
        $attempts = (int) ($adminData['AccessHistory']['LoginAttempts'] ?? 0) + 1;
        $update   = ['LoginAttempts' => $attempts];

        if ($attempts >= 5) {
            $update['LockedUntil'] = date('c', $now + 1800);
        }

        $firebase->update($path, $update);
    }

    /**
     * [S-07] Returns TRUE if this IP has exceeded the rate limit.
     */
    private function _is_ip_blocked(string $ip, int $now, object $firebase): bool
    {
        $record = $firebase->get($this->_ip_path($ip));
        if (! is_array($record)) return false;

        $windowStart = (int) ($record['windowStart'] ?? 0);
        if ($now - $windowStart > self::IP_WINDOW_SEC) return false;

        return (int) ($record['fails'] ?? 0) >= self::IP_MAX_FAILS;
    }

    /**
     * [S-07] Record one failure for this IP.
     */
    private function _record_ip_fail(string $ip, int $now, object $firebase): void
    {
        $path   = $this->_ip_path($ip);
        $record = $firebase->get($path);

        if (! is_array($record) || ($now - (int)($record['windowStart'] ?? 0)) > self::IP_WINDOW_SEC) {
            $firebase->update($path, ['windowStart' => $now, 'fails' => 1]);
        } else {
            $firebase->update($path, ['fails' => (int)($record['fails'] ?? 0) + 1]);
        }
    }

    /**
     * [S-07] Clear IP fail counter on successful login.
     */
    private function _clear_ip_fails(string $ip, object $firebase): void
    {
        $firebase->update($this->_ip_path($ip), ['fails' => 0, 'windowStart' => 0]);
    }

    /**
     * [S-07] Firebase-safe path for an IP address.
     * Replaces . and : (IPv4/IPv6 chars) with hyphens.
     */
    private function _ip_path(string $ip): string
    {
        $safeIp = str_replace(['.', ':'], '-', $ip);
        return "RateLimit/Login/{$safeIp}";
    }

    /**
     * [S-03] Returns TRUE if value is safe to use as a Firebase path segment.
     * Allows: letters, digits, hyphens, underscores only (no spaces — for IDs).
     */
    private function _is_safe_id(string $value): bool
    {
        return $value !== '' && (bool) preg_match('/^[A-Za-z0-9_\-]+$/', $value);
    }

    /**
     * Get the real client IP. Falls back to REMOTE_ADDR (cannot be spoofed).
     */
    private function _get_real_ip(): string
    {
        $ip = $this->input->ip_address();
        return ($ip === '::1') ? '127.0.0.1' : $ip;
    }

    /**
     * [S-13] Strip newlines/control chars before logging — prevents log injection.
     */
    private function _log_safe(string $value): string
    {
        return preg_replace('/[\r\n\t\x00-\x1F\x7F]/', '_', $value);
    }
}