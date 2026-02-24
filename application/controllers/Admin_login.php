<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_login extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        // Load Firebase helper or service
        $this->load->library('firebase'); // assumes you created one
    }

    // Function to check if the user is logged in
    public function is_logged_in()
    {
        if (!$this->session->userdata('admin_id')) {
            // Redirect to login if not logged in
            redirect('admin_login');
        }
    }

    public function index()
    {
        $this->load->view('admin_login');
    }

    public function check_credentials()
    {
        $adminId = $this->input->post('admin_id');
        $schoolId = $this->input->post('school_id');
        $password = $this->input->post('password');

        $path2 = "School_ids/$schoolId";
        $firebase = new Firebase(); // your helper

        $schoolName = $firebase->get($path2);


        // ✅ Step 1: Get admin data
        $path = "Users/Admin/$schoolId/$adminId";
        $adminData = $firebase->get($path);

        if (!$adminData) {
            $this->session->set_flashdata('error', 'Admin ID or School ID not found.');
            redirect('admin_login');
        }

        if ($adminData['Status'] !== 'Active') {
            $this->session->set_flashdata('error', 'Account is not active.');
            redirect('admin_login');
        }

        // ✅ Check if already logged in
        if (!empty($adminData['AccessHistory']['IsLoggedIn']) && $adminData['AccessHistory']['IsLoggedIn'] === true) {
            $this->session->set_flashdata('error', 'This admin is already logged in on another device.');
            redirect('admin_login');
        }




        // ✅ Step 2: Subscription Check
        $subscriptionPath = "Users/Schools/$schoolName/subscription";
        $subscription = $firebase->get($subscriptionPath);
        // Log attempt to fetch subscription
        log_message('info', "Checking subscription for school: $schoolName at path: $subscriptionPath");


        if (!$subscription || $subscription['status'] !== 'Active') {
            log_message('error', "Subscription inactive or missing for school: $schoolName. Data: " . json_encode($subscription));
            $this->session->set_flashdata('error', 'Subscription is not active.');
            redirect('admin_login');
        }

        $endDate = $subscription['duration']['endDate'] ?? null;
        // log_message('info', "Subscription end date for school '$schoolName': " . ($endDate ?: 'Not found'));


        if (!$endDate || strtotime($endDate) < time()) {
            // log_message('error', "Subscription expired for school: $schoolName. End date: " . ($endDate ?: 'null'));
            $this->session->set_flashdata('error', 'Subscription has expired on ' . $endDate . '. Please Renew your Subscription by contacting our Team.');
            redirect('admin_login');
        }
        // Log success if subscription is valid
        // log_message('info', "Subscription valid for school: $schoolName. Ends on: $endDate");


        // === ACCESS CONTROL ===
        $accessHistory = $adminData['AccessHistory'] ?? [];
        $loginAttempts = $accessHistory['LoginAttempts'] ?? 0;
        $lockedUntil = isset($accessHistory['LockedUntil']) ? strtotime($accessHistory['LockedUntil']) : 0;
        $now = time();

        // === AUTO RESET after lockout expires ===
        if ($lockedUntil > 0 && $now >= $lockedUntil) {
            // Reset login attempts and clear lock
            $firebase->update("$path/AccessHistory", [
                'LoginAttempts' => 0,
                'LockedUntil' => null
            ]);
            $loginAttempts = 0; // Also reset locally
            $lockedUntil = 0;
        }

        // === STILL LOCKED? ===
        if ($lockedUntil > $now) {
            $remaining = ceil(($lockedUntil - $now) / 60);
            $this->session->set_flashdata('error', "Too many failed attempts. Please try again in $remaining minute(s).");
            redirect('admin_login');
        }

        // === PASSWORD CHECK ===
        $storedPassword = $adminData['Credentials']['Password'];
        if ($password !== $storedPassword) {
            $loginAttempts++;
            $updateData = [
                'LoginAttempts' => $loginAttempts
            ];

            if ($loginAttempts >= 3) {
                $lockedUntilTime = date('c', strtotime('+30 minutes'));
                $updateData['LockedUntil'] = $lockedUntilTime;

                // Optional: send email alert here
                // $this->send_login_alert($adminData['Email'], $adminData['Name'], $adminId, $schoolId);

                $this->session->set_flashdata('error', 'Too many failed login attempts. Account locked for 30 minutes.');
            } else {
                $remaining = 3 - $loginAttempts;
                $this->session->set_flashdata('error', "Incorrect password. $remaining attempt(s) left.");
            }

            $firebase->update("$path/AccessHistory", $updateData);
            redirect('admin_login');
        }

        // === SUCCESSFUL LOGIN ===
        $ipAddress = $this->input->ip_address();
        if ($ipAddress === '::1') $ipAddress = '127.0.0.1';

        $firebase->update("$path/AccessHistory", [
            'LastLogin' => date('c'),
            'LoginIP' => $ipAddress,
            'LoginAttempts' => 0,
            'LockedUntil' => null,
            'IsLoggedIn' => true
        ]);

        // Regenerate session ID to prevent session fixation
        $this->session->sess_regenerate(TRUE);

        // === Calculate Current Financial Year ===
        $month = (int)date('m');
        $year = (int)date('Y');

        if ($month >= 4) {
            // April to December -> currentYear-nextYear
            $currentYear = $year;
            $nextYear = $year + 1;
        } else {
            // January to March -> previousYear-currentYear
            $currentYear = $year - 1;
            $nextYear = $year;
        }

        $financialYear = $currentYear . '-' . substr($nextYear, -2);

        $featuresPath = "Users/Schools/$schoolName/subscription/features";
        $featuresData = $firebase->get($featuresPath);
        // Ensure it's an array (in case Firebase returns an object)
        $features = is_array($featuresData) ? array_values($featuresData) : [];

        if (empty($features)) {
            // Optional: Handle if no features found or there's a connection issue
            log_message('error', "No features found or failed to fetch features for $schoolName");
        }
        // Set session data
        $this->session->set_userdata([
            'admin_id'      => $adminId,
            'school_id'     => $schoolId,
            'admin_role'    => $adminData['Role'],
            'admin_name'    => $adminData['Name'],
            'session'  => $financialYear,
            'schoolName'  => $schoolName,
            'school_features'  => $features

            // ✅ Added financial year session
        ]);

        log_message('info', 'Admin logged in: ' . json_encode([
            'admin_id'   => $adminId,
            'school_id'  => $schoolId,
            'admin_role' => $adminData['Role'],
            'admin_name' => $adminData['Name'],
            'schoolName'  => $schoolName,

            'session'  => $financialYear,
            'school_features'  => $features  // ✅ Added financial year session

        ]));

        redirect('admin/index');
    }

    public function get_server_date()
    {
        echo json_encode(['date' => date('d-m-Y')]); // Format: DD-MM-YYYY
    }

    public function logout()
    {

        $admin_id = $this->session->userdata('admin_id');
        $school_id = $this->session->userdata('school_id');


        // ✅ Mark as logged out in Firebase
        if ($admin_id && $school_id) {
            $path = "Users/Admin/$school_id/$admin_id/AccessHistory";
            $this->firebase->update($path, [
                'IsLoggedIn' => false,
                'LoginIP' => null
            ]);
        }
        // Set the flashdata message before session destruction
        $this->session->set_flashdata('error', 'You have been successfully logged out.');

        // Clear session variables
        $this->session->unset_userdata('admin_id');
        $this->session->unset_userdata('school_id');
        $this->session->unset_userdata('admin_role');
        $this->session->unset_userdata('admin_name');

        // Destroy the entire session (optional)
        $this->session->sess_destroy();

        // Redirect to login page
        redirect('admin_login');
    }
}
