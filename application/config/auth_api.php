<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Auth API Configuration
|--------------------------------------------------------------------------
| Unified authentication API (Node.js + MongoDB).
| Web login: PHP calls /internal/web-login, then creates PHP session.
| Mobile login: Apps call /mobile/login directly for JWT tokens.
|
| Set environment variables AUTH_API_URL and AUTH_INTERNAL_SECRET
| in production. The fallback values are for local development only.
*/

$config['auth_api_base_url']        = getenv('AUTH_API_URL') ?: 'http://localhost:3000';
$config['auth_api_internal_key']    = getenv('AUTH_INTERNAL_SECRET') ?: '8b9bb3bd40f1757454022b5bed876753b1183e30317674d1c4380a8c9284e9cf';
$config['auth_api_timeout']         = 10;   // total request timeout (seconds) — higher for cloud
$config['auth_api_connect_timeout'] = 5;    // connection timeout (seconds)
