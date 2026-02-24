<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller — Base controller for all protected pages.
 *
 * CHANGES vs original
 * ───────────────────
 * 1. Auth guard moved BEFORE header() calls so redirect works cleanly.
 * 2. CSRF protection helper verify_csrf() — call at top of every POST method.
 * 3. Firebase path sanitisation helper safe_path_segment() — call before
 *    any user-supplied value is interpolated into a Firebase path.
 * 4. Boot-time session value validation — tampered school_name/session_year
 *    destroys the session and redirects to login.
 * 5. json_success() / json_error() helpers to standardise all JSON responses.
 */
class MY_Controller extends CI_Controller
{
    protected $admin_id;
    protected $school_id;
    protected $admin_role;
    protected $admin_name;
    protected $session_year;
    protected $school_name;
    protected $school_features;

    public function __construct()
    {
        parent::__construct();

        $this->load->library(['session', 'firebase']);
        $this->load->helper('url');

        // Pull session data
        $this->admin_id        = $this->session->userdata('admin_id');
        $this->school_id       = $this->session->userdata('school_id');
        $this->admin_role      = $this->session->userdata('admin_role');
        $this->admin_name      = $this->session->userdata('admin_name');
        $this->session_year    = $this->session->userdata('session');
        $this->school_name     = $this->session->userdata('schoolName');
        $this->school_features = $this->session->userdata('school_features');

        // [FIX #1] Authentication guard — moved BEFORE header() calls
        if (!$this->admin_id || !$this->school_id) {
            redirect('admin_login');
        }

        // [FIX #3] Validate session path segments at boot
        // If stored school_name or session_year contain path-traversal
        // characters we destroy the session — it has been tampered with.
        if (
            !$this->_is_safe_segment((string) $this->school_name) ||
            !$this->_is_safe_segment((string) $this->session_year)
        ) {
            log_message('error', 'MY_Controller: unsafe session value detected — destroying session.');
            $this->session->sess_destroy();
            redirect('admin_login');
        }

        // Share variables with all views
        $this->load->vars([
            'school_id'       => $this->school_id,
            'admin_id'        => $this->admin_id,
            'school_name'     => $this->school_name,
            'session_year'    => $this->session_year,
            'admin_name'      => $this->admin_name,
            'admin_role'      => $this->admin_role,
            'school_features' => $this->school_features,
        ]);

        // Cache-control headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    // ================================================================
     protected function verify_csrf()
    {
        if ($this->input->method() === 'post') {

            if ($this->security->csrf_verify() === FALSE) {

                log_message('error', 'CSRF failed → ' . $this->uri->uri_string());

                $this->json_error(
                    'Request verification failed. Refresh the page and try again.',
                    403
                );
            }
        }
    }

   protected function safe_path_segment($value, $field = 'value')
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->json_error("Missing required field: {$field}");
        }

        if (!$this->_is_safe_segment($value)) {

            log_message(
                'error',
                "Unsafe Firebase path segment → {$field}: {$value}"
            );

            $this->json_error("Invalid characters in field: {$field}");
        }

        return $value;
    }


    private function _is_safe_segment($value)
    {
        return (bool) preg_match('/^[A-Za-z0-9 _\-]+$/', $value);
    }

     protected function json_success($data = [])
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success'
            ] + $data))
            ->_display();

        exit;
    }

    protected function json_error($message = 'Something went wrong', $httpCode = 400)
    {
        $this->output
            ->set_status_header($httpCode)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => 'error',
                'message' => $message
            ]))
            ->_display();

        exit;
    }
}

    // ================================================================
    //  [FIX #2] CSRF PROTECTION HELPER
    //
    //  Call $this->verify_csrf() at the top of every POST method.
    //
    //  AJAX setup — add to your main JS file once:
    //
    //    var CI_CSRF_NAME  = '<?= $this->security->get_csrf_token_name() ?>';
    //    var CI_CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
    //
    //    $.ajaxSetup({
    //        beforeSend: function(xhr, settings) {
    //            if (settings.type === 'POST') {
    //                if (typeof settings.data === 'string') {
    //                    settings.data += '&' + CI_CSRF_NAME + '=' + CI_CSRF_TOKEN;
    //                }
    //            }
    //        }
    //    });

    

    