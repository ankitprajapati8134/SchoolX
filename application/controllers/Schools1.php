<?php



defined('BASEPATH') OR exit('No direct script access allowed');

class Schools extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->library('session');
    }


    public function manage_school() { 
            if ($this->input->method() == 'post') {     
            // Get form data
            
            $schoolName = $this->input->post('school_name');
    
            // Fetch the current count from Firebase for students
            $currentSchoolCount = $this->CM->get_data('School_ids/Count');
           
            // Increment the count to generate the new school ID
            $newSchoolId = $currentSchoolCount;



            // Handle file uploads
            $logoUrl = $this->CM->handleFileUpload($_FILES['school_logo'], $schoolName, 'school_logos', 'school_logos', true);
            $holidaysUrl = $this->CM->handleFileUpload($_FILES['Holidays'], $schoolName, 'holidays', 'holidays', true);
            $academicUrl = $this->CM->handleFileUpload($_FILES['Academic'], $schoolName, 'academic', 'academic', true);
    
            // Retrieve existing data from Firebase
            $existingData = $this->CM->select_data('Schools/' . $schoolName);
    
            // Prepare data to insert into Firebase
            $dataToInsert = $existingData ?: [];
    
            // Update URLs if files were uploaded, otherwise preserve existing data
            if ($logoUrl) {
                $dataToInsert['Logo'] = $logoUrl;
            } elseif (isset($existingData['Logo'])) {
                $dataToInsert['Logo'] = $existingData['Logo'];
            } else {
                $dataToInsert['Logo'] = 'No logo';
            }
    
            if ($holidaysUrl) {
                $dataToInsert['Holidays'] = $holidaysUrl;
            } elseif (isset($existingData['Holidays'])) {
                $dataToInsert['Holidays'] = $existingData['Holidays'];
            }
    
            if ($academicUrl) {
                $dataToInsert['Academic calendar'] = $academicUrl;
            } elseif (isset($existingData['Academic calendar'])) {
                $dataToInsert['Academic calendar'] = $existingData['Academic calendar'];
            }
    
            // Add other data if necessary
            $dataToInsert += [
                'Activities' => $existingData['Activities'] ?? ['1' => 'activity_1_url', '2' => 'activity_2_url'],
                'Features' => $existingData['Features'] ?? [
                    'Assignment' => 'features/Assignment.png',
                    'Attendance' => 'features/Attendance.png',
                    'Notification' => 'features/Notification.png',
                    'Profile' => 'features/Attendance.png',
                    'Syllabus' => 'features/Syllabus.png',
                    'Time_Table' => ''
                ],
                'Teachers' => $existingData['Teachers'] ?? [''],
                'Total Classes' => $existingData['Total Classes'] ?? ['Classes Done' => '121', 'Total' => '300']
            ];
    
            // Insert data into Firebase
            $result1 = $this->CM->addKey_pair_data('School_ids/', [$newSchoolId => $schoolName]);
            if ($result1) {
                $result2 = $this->CM->addKey_pair_data('Schools/' . $schoolName . '/', $dataToInsert);
                // echo $result2 ? '1' : '0'; // Echo 1 if data is inserted, otherwise 0

            if ($result2) {
                // Increment the Count in Firebase after successful insertion
                $newSchoolCount = $newSchoolId + 1;
                $this->CM->addKey_pair_data('School_ids/', ['Count' => $newSchoolCount]);
                echo '1'; // Echo 1 if data is inserted successfully
            } else {
                echo '0'; // Echo 0 if data is not inserted into Schools
            }
                    
        } else {
            echo '0'; // If data is not inserted into School_ids, echo 0
        }        
    } else {

            // // Fetch the current count from Firebase
            $currentSchoolCount = $this->CM->get_data('School_ids/Count');

            $schoolIds = $this->CM->select_data('School_ids');
                // echo '<pre>' . print_r($schoolIds, true) . '</pre>';

            $schools = [];
    
            foreach ($schoolIds as $schoolId => $schoolName) {
                // Skip the 'count' key
                if ($schoolId === 'Count') {
                    continue;// Skip the 'Count' key
                }

            // Retrieve the school data
            $schoolData = $this->CM->select_data('Schools/' . $schoolName);

            $schoolData['school_id'] = $schoolId;
            $schoolData['school_name'] = $schoolName;

            // echo '<pre>' . print_r($schoolData['school_id'], true) . '</pre>';


            // Validate logo URL from Firebase Storage, or set to 'No logo' if not found
            $logoPath = $schoolName . '/school_logos/school_logos.jpg';
            $logoUrl = $this->CM->get_file_url($logoPath);
            $schoolData['Logo'] = $logoUrl ?: 'No logo';

            $schools[] = $schoolData;

            }
            
            
            $data['Schools'] = $schools;
            $data['currentSchoolCount'] = $currentSchoolCount; // Pass current count to the view

            $this->load->view('include/header');
            $this->load->view('manage_school', $data);
            $this->load->view('include/footer');
        }
    }


////////////THIS CODE WORKS FINE WITHOUT THE USE OF COUNT

    // public function manage_school() {
    //     // if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    //         if ($this->input->method() == 'post') {     
    //         // Get form data
    //         $schoolId = $this->input->post('school_id');
    //         $schoolName = $this->input->post('school_name');
    
    //         // Fetch the current count from Firebase for students
    //         $currentCount = $this->CM->get_data('School_ids/Count');
    //         if ($currentCount === null) {
    //             $currentCount = 1; // Initialize count if it doesn't exist
    //         }

    //         //Set the new student ID as string
    //         $schoolId = $currentCount;
    //         $data['User_Id'] = $schoolId;


    //         // //Set the new student ID as string
    //         // $schoolId = $currentCount;

    //         // // Check if the school name already exists in School_ids
    //         // $schoolIds = $this->CM->select_data('School_ids');
    //         // foreach ($schoolIds as $id => $name) {
    //         //     if ($name == $schoolName) {
    //         //         echo '0'; // School name already exists, return 0
    //         //         return;
    //         //     }
    //         // }

    //         // Handle file uploads
    //         $logoUrl = $this->CM->handleFileUpload($_FILES['school_logo'], $schoolName, 'school_logos', 'school_logo', true);
    //         $holidaysUrl = $this->CM->handleFileUpload($_FILES['Holidays'], $schoolName, 'holidays', 'holidays', true);
    //         $academicUrl = $this->CM->handleFileUpload($_FILES['Academic'], $schoolName, 'academic', 'academic', true);
    
    //         // Retrieve existing data from Firebase
    //         $existingData = $this->CM->select_data('Schools/' . $schoolName);
    
    //         // Prepare data to insert into Firebase
    //         $dataToInsert = $existingData ?: [];
    
    //         // Update URLs if files were uploaded, otherwise preserve existing data
    //         if ($logoUrl) {
    //             $dataToInsert['Logo'] = $logoUrl;
    //         } elseif (isset($existingData['Logo'])) {
    //             $dataToInsert['Logo'] = $existingData['Logo'];
    //         } else {
    //             $dataToInsert['Logo'] = 'No logo';
    //         }
    
    //         if ($holidaysUrl) {
    //             $dataToInsert['Holidays'] = $holidaysUrl;
    //         } elseif (isset($existingData['Holidays'])) {
    //             $dataToInsert['Holidays'] = $existingData['Holidays'];
    //         }
    
    //         if ($academicUrl) {
    //             $dataToInsert['Academic calendar'] = $academicUrl;
    //         } elseif (isset($existingData['Academic calendar'])) {
    //             $dataToInsert['Academic calendar'] = $existingData['Academic calendar'];
    //         }
    
    //         // Add other data if necessary
    //         $dataToInsert += [
    //             'Activities' => $existingData['Activities'] ?? ['1' => 'activity_1_url', '2' => 'activity_2_url'],
    //             'Features' => $existingData['Features'] ?? [
    //                 'Assignment' => 'features/Assignment.png',
    //                 'Attendance' => 'features/Attendance.png',
    //                 'Notification' => 'features/Notification.png',
    //                 'Profile' => 'features/Attendance.png',
    //                 'Syllabus' => 'features/Syllabus.png',
    //                 'Time_Table' => ''
    //             ],
    //             'Teachers' => $existingData['Teachers'] ?? [''],
    //             'Total Classes' => $existingData['Total Classes'] ?? ['Classes Done' => '121', 'Total' => '300']
    //         ];
    
    //         // Insert data into Firebase
    //         $result1 = $this->CM->addKey_pair_data('School_ids/', [$schoolId => $schoolName]);
    //         if ($result1) {
    //             $result2 = $this->CM->addKey_pair_data('Schools/' . $schoolName . '/', $dataToInsert);
    //             echo $result2 ? '1' : '0'; // Echo 1 if data is inserted, otherwise 0

    //             // if ($result2) {
    //             //     // Increment count in Firebase after successful insertion
    //             //     $newCount = $currentCount + 1;
    //             //     $this->CM->addKey_pair_data('School_ids/', ['Count' => $newCount]);
    //             // // echo '<pre>' . print_r($currentCount, true) . '</pre>';

                    
    //             } else {
    //                 echo '0'; // If data is not inserted into School_ids, echo 0
    //             }       
    
            
    //     } else {

    //         // // Fetch current count from Firebase for display in the form
    //         // $currentCount = $this->CM->get_data('School_ids/Count');            
                
    //         $schoolIds = $this->CM->select_data('School_ids');
    //             // echo '<pre>' . print_r($schoolIds, true) . '</pre>';

    //         $schools = [];
    
    //         foreach ($schoolIds as $schoolId => $schoolName) {
    //             // Skip the 'count' key
    //             if ($schoolId === 'Count') {
    //                 continue;
    //             }

    //         // Retrieve the school data
    //         $schoolData = $this->CM->select_data('Schools/' . $schoolName);
            
    //         // // Check if school exists in Schools collection
    //         // if (!$schoolData) {
    //         //     continue;
    //         // }

    //         $schoolData['school_id'] = $schoolId;
    //         $schoolData['school_name'] = $schoolName;

    //         // Validate logo URL from Firebase Storage, or set to 'No logo' if not found
    //         $logoPath = $schoolName . '/school_logos/school_logo.jpg';
    //         $logoUrl = $this->CM->get_file_url($logoPath);
    //         $schoolData['Logo'] = $logoUrl ?: 'No logo';

    //         $schools[] = $schoolData;

    //         }
            
    //         $data['Schools'] = $schools;
    //         // $data['schoolCount']= $currentCount;

    //         $this->load->view('include/header');
    //         $this->load->view('manage_school', $data);
    //         $this->load->view('include/footer');
    //     }
    // }

    

    
    public function delete_school($schoolId) {
        // Fetch school name from School_ids using $schoolId
        $schoolName = $this->CM->get_school_name_by_id($schoolId);
    
        if ($schoolName) {
            
            // Delete the school data from Schools
            $result1 = $this->CM->delete_data('Schools', $schoolName);
            // Delete the school data from Schools
            $result2 = $this->CM->delete_data('School_ids', $schoolId);
    
            // Delete the school's entire folder from Firebase Storage
            $deleteStorageResult = $this->CM->delete_folder_from_firebase_storage($schoolName . '/');
    
            // Check if all deletions were successful
            if ($result1 && $result2 && $deleteStorageResult) {

                    // Fetch the current count from School_ids
                $currentSchoolCount = $this->CM->get_data('School_ids/Count');

                // Decrement the count by 1
                $newSchoolCount = $currentSchoolCount - 1;

                // Update the count in Firebase
                $this->CM->addKey_pair_data('School_ids/', ['Count' => $newSchoolCount]);

                // Redirect to manage_school page with success message
                redirect('schools/manage_school');
            } else {
                // Redirect to manage_school page with error message
                // $this->session->set_flashdata('message', 'Failed to delete school.');
                redirect('schools/manage_school');
            }
        }else {
            // Redirect to manage_school page with error message if schoolName is not found
            // $this->session->set_flashdata('message', 'School not found.');
            redirect('schools/manage_school');
        }
    }
    

    public function edit_school($schoolId) {
        $schoolDetails = $this->CM->get_school_name_by_id($schoolId); //get school id inside the variable schoolDetails
    
        if (!is_array($schoolDetails)) {
            $schoolDetails = [
                'school_id' => $schoolId,
                'school_name' => $schoolDetails
            ];
        }
    
        if ($this->input->method() == 'post') {
            $postData = $this->input->post();
            $newSchoolName = $postData['school_name'];
            $oldSchoolName = isset($schoolDetails['school_name']) ? $schoolDetails['school_name'] : null;
    
            $oldFolderPath = $oldSchoolName . '/';
            $newFolderPath = $newSchoolName . '/';
    
            $files = [
                'school_logos' => $_FILES['school_logos'],
                'holidays' => $_FILES['holidays'],
                'academic' => $_FILES['academic']
            ];
    
            $changeSchoolName = $oldSchoolName && $oldSchoolName !== $newSchoolName;
            $updateFiles = !empty(array_filter($files, fn($file) => isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])));
    
            $updatedFiles = $this->CM->update_files_and_folder_in_firebase_storage($oldFolderPath, $newFolderPath, $files, $changeSchoolName, $updateFiles);
    
            if ($updatedFiles === false) {
                echo '0';
                return;
            }

            // Retrieve existing data from Firebase
        $existingData = $this->CM->get_data('Schools/' . $oldSchoolName);

        // Prepare data to update in Firebase
        $dataToUpdate = $existingData ?: [];

        if (isset($updatedFiles['school_logos'])) {
            $dataToUpdate['Logo'] = $updatedFiles['school_logos'];
        }

        if (isset($updatedFiles['holidays'])) {
            $dataToUpdate['Holidays'] = $updatedFiles['holidays'];
        }

        if (isset($updatedFiles['academic'])) {
            $dataToUpdate['Academic calendar'] = $updatedFiles['academic'];
        }

        if ($changeSchoolName) {
            if ($existingData) {
                $res1 = $this->CM->update_data('Schools/' . $newSchoolName, null, $dataToUpdate);

                if ($res1) {
                    $this->CM->delete_data('Schools/', $oldSchoolName);
                    $res2 = $this->CM->update_data('', 'School_ids/', [$schoolId => $newSchoolName]);

                    if (!$res2) {
                        echo '0';
                        return;
                    }
                } else {
                    echo '0';
                    return;
                }
            } else {
                echo '0';
                return;
            }
        } else {
            // Update the data in Firebase without changing the school name
            $this->CM->update_data('Schools/' . $newSchoolName, null, $dataToUpdate);
        }
    
            echo '1';
        } else {
            $data['school'] = $schoolDetails;
    
            if (!empty($schoolDetails['school_name'])) {
                $data['school_logo_url'] = $this->CM->get_file_url($schoolDetails['school_name'] . '/school_logos/school_logos.jpg');
                $data['holidays_url'] = $this->CM->get_file_url($schoolDetails['school_name'] . '/holidays/holidays.jpg');
                $data['academic_url'] = $this->CM->get_file_url($schoolDetails['school_name'] . '/academic/academic.jpg');
            } else {
                $data['school_logo_url'] = '';
                $data['holidays_url'] = '';
                $data['academic_url'] = '';
            }
    
            $this->load->view('include/header');
            $this->load->view('edit_school', $data);
            $this->load->view('include/footer');
        }   
    }

    
}
    ?>