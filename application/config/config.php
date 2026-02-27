<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ─────────────────────────────────────────────────────────────────────────────
//  BASE URL  — change to your live domain before deployment
// ─────────────────────────────────────────────────────────────────────────────

// $config['base_url'] = 'http://localhost/Grader/school/';   // ← update this
$config['base_url'] =
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . '/Grader/school/';

$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
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
//  FIX: Set a strong random key before going live.
//  Generate with: php -r "echo bin2hex(random_bytes(32));"
// ─────────────────────────────────────────────────────────────────────────────
$config['encryption_key'] = 'd1d4f05775cd80b86a38bb3673bb6d064c006c5588214e76bb7df69b64540957';  // ← REQUIRED

// ─────────────────────────────────────────────────────────────────────────────
//  SESSION SETTINGS
//  FIX: secure cookies, strict SameSite, HttpOnly, longer expiry
// ─────────────────────────────────────────────────────────────────────────────
$config['sess_driver']           = 'files';
$config['sess_cookie_name']      = 'grader_session';     // ← not the default ci_session
$config['sess_samesite']         = 'Lax';             // FIX: Strict > Lax for admin
$config['sess_expiration']       = 7200;                 // 2 hours
$config['sess_save_path']        = NULL;
$config['sess_match_ip']         = FALSE;
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
$config['cookie_secure']   = FALSE;    // FIX: TRUE once HTTPS is live
$config['cookie_httponly'] = TRUE;    // FIX: prevents JavaScript from reading the cookie
$config['cookie_samesite'] = 'Lax';

$config['standardize_newlines']  = FALSE;
$config['global_xss_filtering']  = FALSE;

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
$config['csrf_exclude_uris'] = [];

$config['compress_output'] = FALSE;
$config['time_reference']  = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';
