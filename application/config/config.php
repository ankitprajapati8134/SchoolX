<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ─────────────────────────────────────────────────────────────────────────────
//  BASE URL  — change to your live domain before deployment
// ─────────────────────────────────────────────────────────────────────────────

// $config['base_url'] = 'http://localhost/Grader/school/';   // ← update this
$_is_https = (
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
);
// SEC-13 FIX: Validate HTTP_HOST against allowlist to prevent host-header injection
$_allowed_hosts = ['localhost', '127.0.0.1', 'localhost:8080'];
if (getenv('APP_HOST')) $_allowed_hosts[] = getenv('APP_HOST');
$_host = (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $_allowed_hosts, true))
    ? $_SERVER['HTTP_HOST']
    : 'localhost';
$config['base_url'] =
    ($_is_https ? 'https' : 'http')
    . '://' . $_host
    . '/Grader/school/';

$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = TRUE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = TRUE;
$config['composer_autoload'] = realpath(APPPATH . '../vendor/autoload.php');

// ─────────────────────────────────────────────────────────────────────────────
//  ALLOWED URI CHARACTERS
//  Apostrophes needed for class names like "8th 'A'"
// ─────────────────────────────────────────────────────────────────────────────
$config['permitted_uri_chars'] = "a-z 0-9~%.:_\\-\\'\\+\\ ";

$config['enable_query_strings'] = FALSE;
$config['controller_trigger']   = 'c';
$config['function_trigger']     = 'm';
$config['directory_trigger']    = 'd';
$config['allow_get_array']      = TRUE;

// ─────────────────────────────────────────────────────────────────────────────
//  ERROR LOGGING
//  FIX: Set to 1 (errors only) on production — level 4 fills disk fast
// ─────────────────────────────────────────────────────────────────────────────
$config['log_threshold']        = 1;   // PRODUCTION: errors only
$config['log_path']             = '';
$config['log_file_extension']   = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format']      = 'Y-m-d H:i:s';

$config['error_views_path']  = '';
$config['cache_path']        = '';
$config['cache_query_string'] = FALSE;

// ─────────────────────────────────────────────────────────────────────────────
//  ENCRYPTION KEY
//  Loaded from .env file — NEVER hardcode in source code.
//  Generate with: php -r "echo bin2hex(random_bytes(32));"
// ─────────────────────────────────────────────────────────────────────────────
// Load .env if not already loaded
if (file_exists(FCPATH . '.env')) {
    $envLines = file(FCPATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $envLine) {
        $envLine = trim($envLine);
        if ($envLine === '' || $envLine[0] === '#') continue;
        if (strpos($envLine, '=') === false) continue;
        list($envKey, $envVal) = array_map('trim', explode('=', $envLine, 2));
        $envVal = trim($envVal, '"\'');
        if (!array_key_exists($envKey, $_ENV)) {
            $_ENV[$envKey] = $envVal;
            putenv("{$envKey}={$envVal}");
        }
    }
}
$config['encryption_key'] = getenv('ENCRYPTION_KEY') ?: 'CHANGE_ME_SET_IN_ENV_FILE';  // ← REQUIRED — set in .env
if ($config['encryption_key'] === 'CHANGE_ME_SET_IN_ENV_FILE') {
    die('FATAL: Set ENCRYPTION_KEY in your .env file before running in production.');
}

// ─────────────────────────────────────────────────────────────────────────────
//  SESSION SETTINGS
//  FIX: secure cookies, strict SameSite, HttpOnly, longer expiry
// ─────────────────────────────────────────────────────────────────────────────
$config['sess_driver']           = 'files';
$config['sess_cookie_name']      = 'grader_session';     // ← not the default ci_session
$config['sess_samesite']         = 'Strict';           // M-08 FIX: Strict prevents cross-site cookie leakage
$config['sess_expiration']       = 7200;                 // 2 hours
$config['sess_save_path']        = NULL;
$config['sess_match_ip']         = TRUE;   // H-06 FIX: bind session to client IP — prevents session hijacking
$config['sess_time_to_update']   = 300;
$config['sess_regenerate_destroy'] = TRUE;               // FIX: destroy old session on regenerate

// ─────────────────────────────────────────────────────────────────────────────
//  COOKIE SETTINGS
//  FIX: httponly=TRUE prevents JS access to session cookie
//       secure=TRUE forces HTTPS-only cookie (enable when SSL is active)
// ─────────────────────────────────────────────────────────────────────────────
$config['cookie_prefix']   = '';
$config['cookie_domain']   = '';
$config['cookie_path']     = '/';
$config['cookie_secure']   = $_is_https;  // Auto-enabled when serving over HTTPS
$config['cookie_httponly'] = TRUE;    // FIX: prevents JavaScript from reading the cookie
$config['cookie_samesite'] = 'Strict'; // M-08 FIX: Strict prevents cross-site cookie leakage

$config['standardize_newlines']  = FALSE;
$config['global_xss_filtering']  = TRUE;   // SEC-2 FIX: apply XSS filter to all input globally

// ─────────────────────────────────────────────────────────────────────────────
//  CSRF PROTECTION
//  FIX: ENABLED. csrf_regenerate=FALSE is correct for AJAX apps — setting TRUE
//       causes token mismatch when multiple tabs are open.
// ─────────────────────────────────────────────────────────────────────────────
$config['csrf_protection']  = TRUE;
$config['csrf_token_name']  = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_token';
$config['csrf_expire']      = 7200;
$config['csrf_regenerate']  = FALSE;   // Keep FALSE for AJAX apps

// ── SA panel CSRF handled by MY_Superadmin_Controller (session-based, not cookie). ──
// The SA and school-admin panels share the same cookie domain/name, so CI3's
// cookie-based check collides when both are open.  We exclude all SA routes
// (except the login form POST) and verify CSRF manually in the base controller
// using a token stored in the authenticated SA session.
$config['csrf_exclude_uris'] = [
    'superadmin/dashboard(.*)',
    'superadmin/schools(.*)',
    'superadmin/plans(.*)',
    'superadmin/reports(.*)',
    'superadmin/monitor(.*)',
    'superadmin/backups(.*)',
    'superadmin/debug(.*)',
    'superadmin/migration(.*)',
    'superadmin/login(.*)',
    'superadmin/csrf_token',
];

$config['compress_output'] = FALSE;
$config['time_reference']  = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';
