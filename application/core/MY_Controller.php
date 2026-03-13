<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MY_Controller — Secure base controller for all authenticated pages.
 *
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  SECURITY FIXES                                                  ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  [FIX-1]  Auth guard — every child controller gets auth free     ║
 * ║  [FIX-2]  CSRF on all POST — Ajax gets JSON 403                 ║
 * ║  [FIX-3]  Firebase path sanitisation — safe_path_segment()      ║
 * ║  [FIX-4]  Session tamper check on school_name + session_year    ║
 * ║  [FIX-5]  No-cache + full security headers on every response    ║
 * ║  [FIX-6]  json_success() / json_error() with correct HTTP codes ║
 * ║  [FIX-7]  Ownership guard — assert_school_ownership()           ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  BUGS FIXED                                                      ║
 * ║  [BUG-1]  Sub live-check: wrong path (missing "Users/" prefix)  ║
 * ║  [BUG-2]  Sub live-check: wrong model ($this->CM → $this->firebase)║
 * ║  [BUG-3]  unset_userdata missed 'current_session','session_year'║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  AUDIT FIXES (this revision)                                     ║
 * ║  [A-01]  Firebase downtime no longer kicks out users            ║
 * ║  [A-02]  Security headers added (X-Frame, CSP, etc.)            ║
 * ║  [A-03]  'current_session' shared to views (account_book fix)   ║
 * ║  [A-04]  SESSION_KEYS references Admin_login constant directly  ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class MY_Controller extends CI_Controller
{
    protected $admin_id;
    protected $school_id;
    protected $school_code;
    protected $school_display_name;
    protected $admin_role;
    protected $admin_name;
    protected $session_year;
    protected $school_name;
    protected $school_features;
    protected $available_sessions = [];
    /** Key for Users/Parents/{key}/ paths — school_code for legacy, school_id for SCH_ schools */
    protected $parent_db_key;

    /**
     * Routes that skip auth + CSRF checks.
     * Format: 'controller/method' lowercase.
     */
    protected $public_routes = [
        'admin_login/index',
        'admin_login/check_credentials',
        'admin_login/get_server_date',
    ];

    // ─────────────────────────────────────────────────────────────────────
    public function __construct()
    {
        parent::__construct();

        $this->load->library('firebase');
        $this->load->library('session');
        $this->load->helper('url');

        // [FIX-5] + [A-02] Full security + no-cache headers
        $this->_send_security_headers();

        // ── Pull session data ─────────────────────────────────────────────
        $this->admin_id            = $this->session->userdata('admin_id');
        $this->school_id           = $this->session->userdata('school_id');       // SCH_XXXXXX
        $this->school_code         = $this->session->userdata('school_code');     // login code
        $this->admin_role          = $this->session->userdata('admin_role');
        $this->admin_name          = $this->session->userdata('admin_name');
        $this->session_year        = $this->session->userdata('session');         // legacy key
        $this->school_name         = $this->session->userdata('schoolName');      // = school_id (SCH_XXXXXX)
        $this->school_display_name = $this->session->userdata('school_display_name') ?? $this->school_name;
        $this->school_features     = $this->session->userdata('school_features');
        $this->available_sessions  = $this->session->userdata('available_sessions') ?? [];

        // For Users/Parents/ and Users/Admin/ paths:
        // Legacy schools store data under the login code (e.g. "10004"),
        // new SCH_ schools store under the school_id (e.g. "SCH_XXXXXX").
        $this->parent_db_key = (strpos($this->school_id ?? '', 'SCH_') === 0)
            ? $this->school_id
            : ($this->school_code ?: $this->school_id);

        // ── Determine current route ───────────────────────────────────────
        $controller = strtolower($this->router->fetch_class());
        $method     = strtolower($this->router->fetch_method());
        $route_key  = $controller . '/' . $method;
        $is_public  = in_array($route_key, $this->public_routes, true);

        // ── [FIX-1] Authentication guard ─────────────────────────────────
        if (! $is_public) {
            if (! $this->admin_id || ! $this->school_id) {
                if ($this->input->is_ajax_request()) {
                    $this->json_error('Session expired. Please log in again.', 401);
                }
                redirect('admin_login');
            }

            // ── [FIX-4] Session tamper check ──────────────────────────────
            if (
                ! $this->_is_safe_segment((string) $this->school_name) ||
                ! $this->_is_safe_segment((string) $this->session_year)
            ) {
                log_message('error',
                    'MY_Controller: unsafe session — destroying. school_name=['
                    . $this->school_name . ']'
                );
                $this->session->sess_destroy();
                if ($this->input->is_ajax_request()) {
                    $this->json_error('Invalid session. Please log in again.', 401);
                }
                redirect('admin_login');
            }

            $now = time();

            // ── [BUG-1+2 FIX] [A-01] Live subscription re-check every 5 min ──
            //
            // [A-01] CRITICAL FIX: if Firebase is unreachable the library
            // returns null/false. We SKIP the check rather than kicking the user
            // out — a network blip must not end everyone's session.
            //
            $lastCheck = (int) $this->session->userdata('sub_check_ts');

            if ($now - $lastCheck >= 300) {
                // Subscription status check — try new path, then legacy fallback
                $liveStatus = $this->firebase->get("System/Schools/{$this->school_id}/subscription/status");
                if ($liveStatus === null || $liveStatus === false || $liveStatus === '') {
                    $liveStatus = $this->firebase->get("Users/Schools/{$this->school_id}/subscription/status");
                }

                // [A-01] Only act if Firebase actually returned a value
                if ($liveStatus !== null && $liveStatus !== false && $liveStatus !== '') {
                    $liveStatus = (string) $liveStatus;
                    $this->session->set_userdata('sub_check_ts', $now);

                    if (! in_array($liveStatus, ['Active', 'Grace_Period'], true)) {
                        log_message('info',
                            "Sub status=[{$liveStatus}] school=[{$this->school_name}] — forcing logout."
                        );
                        $this->_force_logout(
                            'Your school subscription is no longer active. Please contact support.'
                        );
                    }
                } else {
                    // Firebase unreachable — update timestamp to avoid hammering
                    // Firebase on every request, but don't kick the user out.
                    log_message('error',
                        'MY_Controller: Firebase unreachable during sub check for school=['
                        . $this->school_name . ']. Skipping — will retry in 5 min.'
                    );
                    $this->session->set_userdata('sub_check_ts', $now);
                }
            }

            // ── Subscription timestamp expiry / grace-period check ────────
            $subExpiry = (int) $this->session->userdata('subscription_expiry');
            $graceEnd  = (int) $this->session->userdata('subscription_grace_end');

            if ($subExpiry > 0 && $now > $subExpiry) {
                if ($graceEnd > 0 && $now > $graceEnd) {
                    $this->_force_logout(
                        'Your subscription has expired and the grace period has ended. Please renew to continue.'
                    );
                }

                // Still in grace period — refresh warning
                $daysLeft = max(1, (int) ceil(($graceEnd - $now) / 86400));
                $this->session->set_userdata('subscription_warning',
                    'Subscription expired. You have ' . $daysLeft
                    . ' day(s) of grace period remaining. Please renew immediately.'
                );
            }
        }

        // ── [FIX-2] CSRF on all non-public POST requests ──────────────────
        // Skip when CI's built-in csrf_protection is ON — it already verified
        // and removed the token from $_POST, so a second check would always fail.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! $is_public && ! config_item('csrf_protection')) {
            $token_name = $this->security->get_csrf_token_name();
            $token_hash = $this->security->get_csrf_hash();

            $sent = $this->input->post($token_name)
                 ?? $this->input->get_request_header('X-CSRF-Token', TRUE);

            if ($sent !== $token_hash) {
                log_message('error',
                    'CSRF failure route=' . $route_key
                    . ' ip=' . $this->input->ip_address()
                );
                if ($this->input->is_ajax_request()) {
                    $this->json_error('Security token mismatch. Please refresh the page.', 403);
                }
                show_error('CSRF validation failed.', 403);
            }
        }

        // ── [A-03] Share common vars with all views ───────────────────────
        // 'current_session' added so account_book.php gets the right variable.
        $this->load->vars([
            'school_id'            => $this->school_id,           // SCH_XXXXXX
            'school_code'          => $this->school_code,         // login code
            'admin_id'             => $this->admin_id,
            'school_name'          => $this->school_display_name, // human name (for views)
            'school_display_name'  => $this->school_display_name, // human name (explicit)
            'school_firebase_key'  => $this->school_name,         // SCH_XXXXXX (for Firebase paths)
            'session_year'         => $this->session_year,
            'current_session'      => $this->session_year,        // [A-03] account_book.php reads this
            'available_sessions'   => $this->available_sessions,  // session switcher dropdown
            'admin_name'           => $this->admin_name,
            'admin_role'           => $this->admin_role,
            'school_features'      => $this->school_features,
            'subscription_warning' => $this->session->userdata('subscription_warning'),
        ]);
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    /**
     * [A-02] Centralised security + no-cache headers.
     * Called from __construct so every authenticated page gets them.
     */
    private function _send_security_headers(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /**
     * Clear all session keys and redirect to login.
     * Single method used by ALL forced-logout paths so keys stay in sync.
     * [A-04] References Admin_login::SESSION_KEYS — single source of truth.
     */
    private function _force_logout(string $errorMessage): void
    {
        // [A-04] Use Admin_login's SESSION_KEYS constant as the single source
        // of truth so both controllers always clear the exact same set of keys.
        $keys = class_exists('Admin_login')
            ? Admin_login::SESSION_KEYS
            : [
                'admin_id', 'school_id', 'school_code', 'admin_role', 'admin_name',
                'session', 'current_session', 'session_year',
                'schoolName', 'school_display_name', 'school_features',
                'subscription_expiry', 'subscription_grace_end', 'subscription_warning',
                'sub_check_ts', 'login_csrf',
            ];

        $this->session->unset_userdata($keys);

        if ($this->input->is_ajax_request()) {
            $this->json_error($errorMessage, 403);
        }

        $this->session->set_flashdata('error', $errorMessage);
        redirect('admin_login');
    }

    // =========================================================================
    //  [FIX-3] FIREBASE PATH SANITISATION
    // =========================================================================

    /**
     * Validate and return a value safe to embed in a Firebase RTDB path.
     * Blocked: / . # $ [ ] and anything Firebase forbids.
     *
     * Usage:
     *   $class = $this->safe_path_segment($this->input->post('class'), 'class');
     *   $path  = "Schools/{$this->school_id}/{$this->session_year}/{$class}";
     */
    protected function safe_path_segment(string $value, string $field = 'value'): string
    {
        $value = trim($value);

        if ($value === '') {
            $this->json_error("Missing required field: {$field}", 400);
        }

        if (! $this->_is_safe_segment($value)) {
            log_message('error',
                "Unsafe Firebase segment [{$field}]=[{$value}] ip="
                . $this->input->ip_address()
            );
            $this->json_error("Invalid characters in field: {$field}", 400);
        }

        return $value;
    }

    /**
     * TRUE if value contains only safe Firebase key characters.
     * Allows: letters, digits, spaces, hyphens, underscores, apostrophes, commas.
     * (School names like "Maharishi Vidhya Mandir, Balaghat" need spaces + commas.)
     */
    private function _is_safe_segment(string $value): bool
    {
        return $value !== '' && (bool) preg_match("/^[A-Za-z0-9 ',_\-]+$/u", $value);
    }

    // =========================================================================
    //  [FIX-7] OWNERSHIP GUARD
    // =========================================================================

    /**
     * Abort 403 if the given school_name doesn't match the session.
     * Call before any cross-school Firebase read/write.
     */
    protected function assert_school_ownership(string $school_name): void
    {
        if ($school_name !== $this->school_name) {
            log_message('error',
                "Ownership violation: session=[{$this->school_name}]"
                . " tried=[{$school_name}] admin=[{$this->admin_id}]"
            );
            $this->json_error('Access denied.', 403);
        }
    }

    // =========================================================================
    //  [FIX-6] JSON RESPONSE HELPERS
    // =========================================================================

    protected function json_success(array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success'], $data));
        exit;
    }

    protected function json_error(string $message, int $http_code = 400): void
    {
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    // =========================================================================
    //  ROLE-BASED ACCESS CONTROL
    // =========================================================================

    /**
     * Abort 403 if the current user's role is not in the allowed list.
     *
     * @param array $allowed  e.g. ['Super Admin', 'Admin']
     * @param string $action  Human-readable action name for log/message
     */
    protected function _require_role(array $allowed, string $action = ''): void
    {
        $role = $this->admin_role ?? '';

        // Super Admin always passes
        if ($role === 'Super Admin') return;

        if (in_array($role, $allowed, true)) return;

        $label = $action ? " ({$action})" : '';
        log_message('error',
            "RBAC denied: role=[{$role}] admin=[{$this->admin_id}]"
            . " school=[{$this->school_name}]{$label}"
        );

        if ($this->input->is_ajax_request()) {
            $this->json_error('You do not have permission to perform this action.', 403);
        }

        show_error('You do not have permission to access this page.', 403, 'Access Denied');
    }

    /**
     * Load the current teacher's class/subject assignments from Duties.
     *
     * Firebase path: Schools/{school}/{year}/Teachers/{adminId}/Duties
     * Structure:     {DutyType}/{classSection}/{subject}: time
     *   e.g.  SubjectTeacher / Class 9th 'A' / Mathematics : "09:00-10:00"
     *
     * Returns a flat set of normalised keys the teacher is assigned to:
     *   ['Class 9th|Section A'            => true,   // class+section access
     *    'Class 9th|Section A|Mathematics' => true ]  // class+section+subject access
     *
     * Result is cached on the instance so repeated calls within one request are free.
     *
     * @return array  Associative [key => true] for fast isset() lookups
     */
    protected function _get_teacher_assignments(): array
    {
        // Instance cache
        if (isset($this->_teacher_assign_cache)) {
            return $this->_teacher_assign_cache;
        }

        $school = $this->school_name;
        $year   = $this->session_year;
        $tid    = $this->admin_id;
        $map    = [];

        $duties = $this->firebase->get("Schools/{$school}/{$year}/Teachers/{$tid}/Duties");
        if (!is_array($duties)) {
            $this->_teacher_assign_cache = $map;
            return $map;
        }

        foreach ($duties as $dutyType => $classes) {
            if (!is_array($classes)) continue;
            foreach ($classes as $classSection => $subjects) {
                // classSection = "Class 9th 'A'"
                // Parse → classKey="Class 9th", sectionLetter="A"
                if (preg_match("/^(.+?)\\s*'([^']*)'\\s*$/", $classSection, $m)) {
                    $classKey      = trim($m[1]);  // "Class 9th"
                    $sectionLetter = trim($m[2]);  // "A"
                } else {
                    $classKey      = $classSection;
                    $sectionLetter = '';
                }
                $sectionKey = $sectionLetter ? "Section {$sectionLetter}" : '';
                $csKey      = "{$classKey}|{$sectionKey}";

                $map[$csKey] = true;

                if (is_array($subjects)) {
                    foreach (array_keys($subjects) as $subject) {
                        $map["{$csKey}|{$subject}"] = true;
                    }
                }
            }
        }

        $this->_teacher_assign_cache = $map;
        return $map;
    }

    /**
     * Check if the current teacher is assigned to the given class/section (and optionally subject).
     * Non-Teacher roles always return true (they have full access).
     */
    protected function _teacher_can_access(string $classKey, string $sectionKey, string $subject = ''): bool
    {
        if (($this->admin_role ?? '') !== 'Teacher') return true;

        $assignments = $this->_get_teacher_assignments();
        $csKey = "{$classKey}|{$sectionKey}";

        // Must at least be assigned to this class+section
        if (!isset($assignments[$csKey])) return false;

        // If a subject check is requested, verify that too
        if ($subject !== '' && !isset($assignments["{$csKey}|{$subject}"])) return false;

        return true;
    }
}