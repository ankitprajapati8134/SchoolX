<?php
class Admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('firebase');
        $this->load->library('session');
        $this->load->helper('url');

        // Prevent caching for sensitive pages
        if (!$this->session->userdata('admin_id')) {
            // User is logged out, redirect them to login if they try to access restricted pages
            redirect('admin_login');
        }

        // Prevent caching of the page by setting HTTP headers
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function index()
    {
        // Check if the admin is logged in
        if ($this->session->userdata('admin_id')) {

            $this->load->library('firebase');
            $firebase = new Firebase();

            // The admin is logged in, proceed with the page load
            $admin_name = $this->session->userdata('admin_name'); // Get safely
            $admin_role = $this->session->userdata('admin_role'); // Get safely
            $school_id = $this->session->userdata('school_id'); // Get safely
            $admin_id = $this->session->userdata('admin_id'); // Get safely
            $schoolName = $this->session->userdata('schoolName'); // Get safely
            $Session = $this->session->userdata('session');



            // Step 2: Get the school logo from Schools/{schoolName}/Logo
            $logo_path = "Schools/$schoolName/Logo";
            $school_logo_url = $firebase->get($logo_path);
            if (!$school_logo_url) {
                // Default fallback image if logo is missing
                $school_logo_url = base_url('tools/dist/img/default-school.png');
            }


            // Prepare data for the view
            $data['admin_name'] = $admin_name;
            $data['admin_role'] = $admin_role;
            $data['school_id'] = $school_id;
            $data['admin_id'] = $admin_id;
            $data['schoolName'] = $schoolName;
            $data['Session'] = $Session;

            $data['school_logo_url'] = $school_logo_url;




            // Load views with data
            $this->load->view('include/header', $data);
            $this->load->view('home', $data);
            $this->load->view('include/footer');
        } else {
            // Redirect to login page if admin is not logged in
            redirect('admin_login');
        }
    }

    // public function manage_admin()
    // {
    //     $school_id = $this->school_id;
    //     $session_year = $this->session_year;
    //     $school_name = $this->school_name;
    //     $admin_role = $this->admin_role;


    //     if ($this->input->method() == 'post') {

    //         // Check if adminId is provided to fetch a single admin
    //         $adminId = $this->input->post('admin_id');


    //         // Check if request is for password update
    //         if ($this->input->post('newPassword') && $this->input->post('confirmPassword') && $this->input->post('admin_id')) {
    //             $adminId = $this->input->post('admin_id');
    //             $newPassword = $this->input->post('newPassword');
    //             $confirmPassword = $this->input->post('confirmPassword');

    //             // Validate passwords
    //             if ($newPassword !== $confirmPassword) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Passwords do not match!']);
    //                 return;
    //             }

    //             // Hash the new password
    //             $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    //             // Update the password in Firebase
    //             $updateData = ['Credentials/Password' => $hashedPassword];
    //             $updateStatus = $this->firebase->update('Users/Admin/' . $school_id . '/' . $adminId, $updateData);

    //             if ($updateStatus) {
    //                 echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    //             } else {
    //                 echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
    //             }
    //             return;
    //         }

    //         if ($adminId) {
    //             // Fetch details of the specific admin
    //             $adminDetails = $this->firebase->get('Users/Admin/' . $school_id . '/' . $adminId);

    //             if ($adminDetails) {
    //                 echo json_encode(['status' => 'success', 'data' => $adminDetails]);
    //             } else {
    //                 echo json_encode(['status' => 'error', 'message' => 'Admin not found.']);
    //             }
    //             return;
    //         }



    //         // Only Super Admin can create new admins
    //         if ($admin_role !== 'Super Admin') {
    //             echo json_encode(['status' => 'error', 'message' => 'Only Super Admins can create new admins.']);
    //             return;
    //         }

    //         // Step 1: Fetch plan info from correct path
    //         $subscriptionPath = "Users/Schools/$school_name/subscription";
    //         $subscriptionData = $this->firebase->get($subscriptionPath);

    //         $planName = isset($subscriptionData['Plan']['Name']) ? $subscriptionData['Plan']['Name'] : '';
    //         $createdAdmin = isset($subscriptionData['Plan']['Created admin']) ? (int)$subscriptionData['Plan']['Created admin'] : 0;
    //         $maxAdmin = isset($subscriptionData['Plan']['Max admin']) ? (int)$subscriptionData['Plan']['Max admin'] : 0;


    //         // Step 2: Apply admin creation limits
    //         if ($planName === 'Basic plan' && $createdAdmin >= 2) {
    //             echo json_encode(['status' => 'error', 'message' => 'Basic plan allows only 2 admins. Upgrade Plan to create more.']);
    //             return;
    //         } elseif ($planName === 'Premium plan' && $createdAdmin >= 5) {
    //             echo json_encode(['status' => 'error', 'message' => 'Premium plan allows only 5 admins. Upgrade plan to create more.']);
    //             return;
    //         } elseif ($planName === 'Pro plan') {
    //             // Unlimited, continue
    //         } elseif ($maxAdmin > 0 && $createdAdmin >= $maxAdmin) {
    //             echo json_encode(['status' => 'error', 'message' => 'Admin limit reached for your subscription.']);
    //             return;
    //         }


    //         // Add a new admin
    //         $name = $this->input->post('name');
    //         $email = $this->input->post('email');
    //         $phone = $this->input->post('phone');
    //         $dob = $this->input->post('dob');
    //         $gender = $this->input->post('gender');
    //         $role = $this->input->post('role');
    //         $password = $this->input->post('password');

    //         // Fetch current admin count from Firebase
    //         $fetchedAdminData = $this->firebase->get('Users/Admin/' . $school_id);
    //         $count = isset($fetchedAdminData['Count']) ? (int)$fetchedAdminData['Count'] : 0;

    //         // Generate adminId
    //         $adminId = 'ADM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    //         // Hash the password
    //         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    //         // Prepare admin data
    //         $adminData = [
    //             'AccessHistory' => [
    //                 'LastLogin' => date('c'),
    //                 'LoginAttempts' => 0,
    //                 'LoginIP' => $this->input->ip_address(),
    //                 'IsLoggedIn' => false
    //             ],
    //             'Created On' => date('c'),
    //             'Credentials' => [
    //                 'Id' => $adminId,
    //                 'Password' => $hashedPassword,
    //                 'TwoFactorAuthentication' => [
    //                     'Enabled' => false,
    //                     'Method' => 'OTP via SMS'
    //                 ]
    //             ],
    //             'DOB' => date('d-m-Y', strtotime($dob)),
    //             'Email' => $email,
    //             'Gender' => $gender,
    //             'Name' => $name,
    //             'PhoneNumber' => $phone,
    //             'Privileges' => ['accountmanagement' => ''],
    //             'Role' => $role,
    //             'Status' => 'Active'
    //         ];

    //         // Insert the new admin
    //         $this->firebase->set('Users/Admin/' . $school_id . '/' . $adminId, $adminData);

    //         // Insert the new admins in the Schools Path
    //         $adminSchoolPath = "Schools/$school_name/$session_year/Admins/$adminId";
    //         $this->firebase->set($adminSchoolPath, ['Name' => $name]);



    //         // Update the count in Firebase
    //         $newCount = $count + 1;
    //         $this->firebase->update('Users/Admin/' . $school_id, ['Count' => $newCount]);

    //         // Respond with success message
    //         echo json_encode(['status' => 'success']);
    //         return;
    //     } else {

    //         // Fetch all admins for main page
    //         $fetchedAdminData = $this->firebase->get('Users/Admin/' . $school_id);
    //         $data['adminList'] = [];
    //         $data['activeAdmins'] = [];
    //         $data['inactiveAdmins'] = [];
    //         $data['adminId'] = null;

    //         if (isset($fetchedAdminData) && is_array($fetchedAdminData)) {
    //             $count = isset($fetchedAdminData['Count']) ? (int)$fetchedAdminData['Count'] : 0;
    //             $data['adminId'] = 'ADM' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    //             foreach ($fetchedAdminData as $key => $value) {
    //                 if ($key !== 'Count' && is_array($value)) {
    //                     $adminId = $key;
    //                     $adminName = isset($value['Name']) ? $value['Name'] : 'Unknown';
    //                     $adminRole = isset($value['Role']) ? $value['Role'] : 'Unknown';
    //                     $status = isset($value['Status']) ? $value['Status'] : 'Unknown';

    //                     if ($status === 'Active') {
    //                         $data['adminList'][] = "$adminId - $adminName - $adminRole";
    //                     }

    //                     $adminData = [
    //                         'id' => $adminId,
    //                         'name' => $adminName,
    //                         'role' => $adminRole,
    //                         'status' => $status
    //                     ];

    //                     if ($status === 'Active') {
    //                         $data['activeAdmins'][] = $adminData;
    //                     } elseif ($status === 'Inactive') {
    //                         $data['inactiveAdmins'][] = $adminData;
    //                     }
    //                 }
    //             }
    //         }

    //         // Load the views
    //         $this->load->view('include/header');
    //         $this->load->view('manage_admin', $data);
    //         $this->load->view('include/footer');
    //     }
    // }




    public function manage_admin()
    {
        $school_id = $this->school_id;
        $session_year = $this->session_year;
        $school_name = $this->school_name;
        $admin_role = $this->admin_role;

        if ($this->input->method() == 'post') {

            log_message('debug', 'manage_admin(): POST request initiated.');

            // Check if adminId is provided to fetch a single admin
            $adminId = $this->input->post('admin_id');

            // Check if request is for password update
            if ($this->input->post('newPassword') && $this->input->post('confirmPassword') && $this->input->post('admin_id')) {
                $adminId = $this->input->post('admin_id');
                $newPassword = $this->input->post('newPassword');
                $confirmPassword = $this->input->post('confirmPassword');

                log_message('debug', "Password update request for Admin ID: $adminId");

                if ($newPassword !== $confirmPassword) {
                    log_message('error', 'Password mismatch.');
                    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match!']);
                    return;
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateData = ['Credentials/Password' => $hashedPassword];
                $updateStatus = $this->firebase->update("Users/Admin/$school_id/$adminId", $updateData);

                log_message('debug', "Password update status for $adminId: " . ($updateStatus ? 'success' : 'fail'));

                echo json_encode([
                    'status' => $updateStatus ? 'success' : 'error',
                    'message' => $updateStatus ? 'Password updated successfully' : 'Failed to update password'
                ]);
                return;
            }

            if ($adminId) {
                log_message('debug', "Fetching admin data for Admin ID: $adminId");

                $adminDetails = $this->firebase->get("Users/Admin/$school_id/$adminId");

                if ($adminDetails) {
                    echo json_encode(['status' => 'success', 'data' => $adminDetails]);
                } else {
                    log_message('error', "Admin not found: $adminId");
                    echo json_encode(['status' => 'error', 'message' => 'Admin not found.']);
                }
                return;
            }

            if ($admin_role !== 'Super Admin') {
                log_message('error', 'Only Super Admin can create new admins.');
                echo json_encode(['status' => 'error', 'message' => 'Only Super Admins can create new admins.']);
                return;
            }

            $subscriptionPath = "Users/Schools/$school_name/subscription";
            $subscriptionData = $this->firebase->get($subscriptionPath);
            log_message('debug', "Subscription data fetched from $subscriptionPath");

            $planName = $subscriptionData['Plan']['Name'] ?? '';
            $createdAdmin = (int)($subscriptionData['Plan']['Created admin'] ?? 0);
            $maxAdmin = (int)($subscriptionData['Plan']['Max admin'] ?? 0);

            log_message('debug', "Plan: $planName | Created admin: $createdAdmin | Max admin: $maxAdmin");

            if ($planName === 'Basic plan' && $createdAdmin >= 2) {
                echo json_encode(['status' => 'error', 'message' => 'Basic plan allows only 2 admins. Upgrade Plan to create more.']);
                return;
            } elseif ($planName === 'Premium plan' && $createdAdmin >= 5) {
                echo json_encode(['status' => 'error', 'message' => 'Premium plan allows only 5 admins. Upgrade plan to create more.']);
                return;
            } elseif ($maxAdmin > 0 && $createdAdmin >= $maxAdmin) {
                echo json_encode(['status' => 'error', 'message' => 'Admin limit reached for your subscription.']);
                return;
            }

            // Add a new admin
            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $phone = $this->input->post('phone');
            $dob = $this->input->post('dob');
            $gender = $this->input->post('gender');
            $role = $this->input->post('role');
            $password = $this->input->post('password');

            $countPath = "Users/Admin/$school_id/Count";
            $currentCount = (int)$this->firebase->get($countPath);

            log_message('debug', "Current Count: $currentCount");

            // Get existing max ID
            $fetchedAdminData = $this->firebase->get("Users/Admin/$school_id");
            $maxId = 0;
            if (is_array($fetchedAdminData)) {
                foreach ($fetchedAdminData as $key => $value) {
                    if (preg_match('/ADM(\d{4})/', $key, $matches)) {
                        $num = (int)$matches[1];
                        $maxId = max($maxId, $num);
                    }
                }
            }

            $nextAdminNumber = $maxId + 1;
            $adminId = 'ADM' . str_pad($nextAdminNumber, 4, '0', STR_PAD_LEFT);

            log_message('debug', "New Admin ID generated: $adminId");

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $adminData = [
                'AccessHistory' => [
                    'LastLogin' => date('c'),
                    'LoginAttempts' => 0,
                    'LoginIP' => $this->input->ip_address(),
                    'IsLoggedIn' => false
                ],
                'Created On' => date('c'),
                'Credentials' => [
                    'Id' => $adminId,
                    'Password' => $hashedPassword,
                    'TwoFactorAuthentication' => [
                        'Enabled' => false,
                        'Method' => 'OTP via SMS'
                    ]
                ],
                'DOB' => date('d-m-Y', strtotime($dob)),
                'Email' => $email,
                'Gender' => $gender,
                'Name' => $name,
                'PhoneNumber' => $phone,
                'Privileges' => ['accountmanagement' => ''],
                'Role' => $role,
                'Status' => 'Active'
            ];

            $this->firebase->set("Users/Admin/$school_id/$adminId", $adminData);
            $this->firebase->update("Users/Admin/$school_id", ['Count' => $nextAdminNumber]);
            log_message('debug', "Admin $adminId inserted. Count updated to $nextAdminNumber");

            $adminSchoolPath = "Schools/$school_name/$session_year/Admins/$adminId";
            $this->firebase->set($adminSchoolPath, ['Name' => $name]);

            if ($planName !== 'Pro plan') {
                $newCreatedAdmin = $createdAdmin + 1;
                $this->firebase->update("Users/Schools/$school_name/subscription/Plan", ['Created admin' => $newCreatedAdmin]);
                log_message('debug', "Created admin count updated to $newCreatedAdmin");
            }

            echo json_encode(['status' => 'success']);
            return;
        } else {
            $fetchedAdminData = $this->firebase->get("Users/Admin/$school_id");
            $data['adminList'] = [];
            $data['activeAdmins'] = [];
            $data['inactiveAdmins'] = [];
            $data['adminId'] = null;
            $maxId = 0;

            if (is_array($fetchedAdminData)) {
                foreach ($fetchedAdminData as $key => $value) {
                    if ($key !== 'Count' && is_array($value)) {
                        if (preg_match('/ADM(\d{4})/', $key, $matches)) {
                            $num = (int)$matches[1];
                            $maxId = max($maxId, $num);
                        }

                        $adminData = [
                            'id' => $key,
                            'name' => $value['Name'] ?? 'Unknown',
                            'role' => $value['Role'] ?? 'Unknown',
                            'status' => $value['Status'] ?? 'Unknown'
                        ];

                        $status = $adminData['status'];
                        if ($status === 'Active') {
                            $data['adminList'][] = "{$adminData['id']} - {$adminData['name']} - {$adminData['role']}";
                            $data['activeAdmins'][] = $adminData;
                        } elseif ($status === 'Inactive') {
                            $data['inactiveAdmins'][] = $adminData;
                        }
                    }
                }
            }

            $nextAdminNumber = $maxId + 1;
            $data['adminId'] = 'ADM' . str_pad($nextAdminNumber, 4, '0', STR_PAD_LEFT);

            log_message('debug', "Next Admin ID on page load: {$data['adminId']}");

            $this->load->view('include/header');
            $this->load->view('manage_admin', $data);
            $this->load->view('include/footer');
        }
    }





    public function edit_admin()
    {

        $school_id = $this->school_id;
        $school_name = $this->school_name;
        $session_year = $this->session_year;

        // Load model
        $this->load->model('Admin_model');

        // Get the POST data
        $admin_id = $this->input->post('admin_id');
        $admin_name = $this->input->post('admin_name');
        $admin_email = $this->input->post('admin_email');
        $admin_phone = $this->input->post('admin_phone');
        $admin_role = $this->input->post('admin_role');
        $admin_dob = $this->input->post('admin_dob');
        $admin_gender = $this->input->post('admin_gender');

        // Prepare data for updating
        $update_data = [
            'name' => $admin_name,
            'email' => $admin_email,
            'phone' => $admin_phone,
            'role' => $admin_role,
            'dob' => $admin_dob,
            'gender' => $admin_gender
        ];

        // Call model method to update the admin details
        $update_result = $this->firebase->update('Users/Admin/' . $school_id . '/' . $admin_id, $update_data);

        // Return a response (you can customize this based on success or failure)
        if ($update_result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    public function updateUserData()
    {
        $school_id = $this->school_id;

        // Get POST data
        $modalId = $this->input->post('modal_id'); // ADM0001
        $userData = $this->input->post('user_data'); // Array of user data

        // Validate modal ID and user data
        if (empty($modalId) || empty($userData)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            return;
        }

        // Firebase path
        $path = "Users/Admin/$school_id/{$modalId}";

        // Save data to Firebase
        try {
            $this->firebase->update($path, $userData);
            echo json_encode(['success' => true, 'message' => 'Data updated successfully.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating data: ' . $e->getMessage()]);
        }
    }
}
