<div class="content-wrapper">
    <div class="page_container">
        <div class="box">
            <div style="padding-top:20px; padding-left: 10px; padding-right: 20px;">
                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-10">
                        <div class="section-header"> 📝 Staff Registration Edit Form </div>

                        <form action="<?php echo base_url() . 'staff/edit_staff/' . $staff_data['User ID'] ?>"
                            method="post" id="edit_staff_form" enctype="multipart/form-data">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">

                            <h3>Personal Information</h3>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="photo">Upload Photo <span style="color: red;">*</span></label>
                                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                                    <?php if (!empty($staff_data['Photo URL'])): ?>
                                    <img src="<?php echo $staff_data['Photo URL']; ?>" alt="Staff Photo" width="100"
                                        height="100">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="aadhar_card">Upload Aadhar Card <span
                                            style="color: red;">*</span></label>
                                    <input type="file" name="aadhar_card" id="aadhar_card" class="form-control"
                                        accept=".pdf, .jpg, .jpeg, .png">
                                    <?php if (!empty($staff_data['Aadhar URL'])): ?>
                                    <a href="<?php echo $staff_data['Aadhar URL']; ?>" target="_blank">View Uploaded
                                        Aadhar Card</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="user_id">User ID</label>
                                    <input type="text" name="user_id" required="required" id="user_id"
                                        value="<?php echo $staff_data['User ID'] ?>" class="form-control"
                                        placeholder="Enter User ID" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="name">Name</label>
                                    <input type="text" name="Name" required="required" id="name"
                                        value="<?php echo $staff_data['Name'] ?>" class="form-control"
                                        placeholder="Enter Staff Name">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="father_name">Father's Name <span style="color: red;">*</span></label>
                                    <input type="text" name="father_name" id="father_name" class="form-control"
                                        value="<?php echo $staff_data['Father Name'] ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="email">Email</label>
                                    <input type="email" name="Email" required="required" id="email"
                                        value="<?php echo $staff_data['Email'] ?>" class="form-control"
                                        placeholder="Enter Email">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="gender">Select Gender</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="Male"
                                            <?php echo ($staff_data['Gender'] == 'Male') ? 'selected' : '' ?>>Male
                                        </option>
                                        <option value="Female"
                                            <?php echo ($staff_data['Gender'] == 'Female') ? 'selected' : '' ?>>Female
                                        </option>
                                        <option value="Other"
                                            <?php echo ($staff_data['Gender'] == 'Other') ? 'selected' : '' ?>>Other
                                        </option>

                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="text" name="phone_number" required="required" id="phone_number"
                                        value="<?php echo $staff_data['Phone Number'] ?>" class="form-control"
                                        placeholder="Enter Phone Number">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="dob">Staff DOB <span style="color: red;">*</span></label>
                                    <input type="date" name="DOB" id="dob" class="form-control"
                                        value="<?php echo isset($staff_data['DOB']) ? date('Y-m-d', strtotime($staff_data['DOB'])) : ''; ?>"
                                        required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="blood_group">Blood Group</label>
                                    <select name="blood_group" id="blood_group" class="form-control" required>
                                        <?php
                                        $bloodGroups = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-", "Unknown"];
                                        $currentBloodGroup = $staff_data['blood_group'] ?? $staff_data['Blood Group'] ?? ''; // Fetch current blood group
                                        foreach ($bloodGroups as $group): ?>
                                        <option value="<?= $group; ?>"
                                            <?= ($currentBloodGroup === $group) ? 'selected' : ''; ?>>
                                            <?= $group; ?>
                                        </option>
                                        <?php endforeach; ?>

                                        ?>
                                    </select>
                                </div>
                            </div>


                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="religion">Religion <span style="color: red;">*</span></label>
                                    <select name="religion" id="religion" class="form-control" required>
                                        <option value="">Select Religion</option>
                                        <option value="Hindu"
                                            <?php echo ($staff_data['Religion'] == 'Hindu') ? 'selected' : '' ?>>Hindu
                                        </option>
                                        <option value="Muslim"
                                            <?php echo ($staff_data['Religion'] == 'Muslim') ? 'selected' : '' ?>>Muslim
                                        </option>
                                        <option value="Sikh"
                                            <?php echo ($staff_data['Religion'] == 'Sikh') ? 'selected' : '' ?>>Sikh
                                        </option>
                                        <option value="Jain"
                                            <?php echo ($staff_data['Religion'] == 'Jain') ? 'selected' : '' ?>>Jain
                                        </option>
                                        <option value="Buddh"
                                            <?php echo ($staff_data['Religion'] == 'Buddh') ? 'selected' : '' ?>>Buddh
                                        </option>
                                        <option value="Christian"
                                            <?php echo ($staff_data['Religion'] == 'Christian') ? 'selected' : '' ?>>
                                            Christian</option>
                                        <option value="Other"
                                            <?php echo ($staff_data['Religion'] == 'Other') ? 'selected' : '' ?>>Other
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="category">Select Category <span style="color: red;">*</span></label>
                                    <select name="category" id="category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <option value="General"
                                            <?php echo ($staff_data['Category'] == 'General') ? 'selected' : '' ?>>
                                            General</option>
                                        <option value="OBC"
                                            <?php echo ($staff_data['Category'] == 'OBC') ? 'selected' : '' ?>>OBC
                                        </option>
                                        <option value="SC"
                                            <?php echo ($staff_data['Category'] == 'SC') ? 'selected' : '' ?>>SC
                                        </option>
                                        <option value="ST"
                                            <?php echo ($staff_data['Category'] == 'ST') ? 'selected' : '' ?>>ST
                                        </option>
                                    </select>
                                </div>
                            </div>


                            <h3>Address Details</h3>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="city">City</label>
                                    <input type="text" name="city" id="city" class="form-control"
                                        placeholder="Enter City"
                                        value="<?php echo $staff_data['Address']['City'] ?? ''; ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="street">Street</label>
                                    <input type="text" name="street" id="street" class="form-control"
                                        placeholder="Enter Street"
                                        value="<?php echo $staff_data['Address']['Street'] ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="state">State</label>
                                    <input type="text" name="state" id="state" class="form-control"
                                        placeholder="Enter State"
                                        value="<?php echo $staff_data['Address']['State'] ?? ''; ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" name="postalcode" id="postal_code" class="form-control"
                                        placeholder="Enter Postal Code"
                                        value="<?php echo $staff_data['Address']['PostalCode'] ?? ''; ?>" required>
                                </div>
                            </div>


                            <h3>Emergency Contact Details</h3>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="emergency_contact_name">Emergency Contact Name <span
                                            style="color: red;">*</span></label>
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                                        class="form-control"
                                        value="<?php echo $staff_data['emergencyContact']['name'] ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="emergency_contact_phone">Emergency Contact Phone <span
                                            style="color: red;">*</span></label>
                                    <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone"
                                        class="form-control"
                                        value="<?php echo $staff_data['emergencyContact']['phoneNumber'] ?>" required>
                                </div>
                            </div>



                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="staff_position">Staff Position <span
                                            style="color: red;">*</span></label>
                                    <input type="text" name="position" id="staff_position" class="form-control"
                                        value="<?php echo $staff_data['Position'] ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="date_of_joining">Date of Joining <span
                                            style="color: red;">*</span></label>
                                    <input type="date" name="date_of_joining" id="date_of_joining" class="form-control"
                                        value="<?php echo date('Y-m-d', strtotime($staff_data['Date Of Joining'])) ?>"
                                        readonly required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="employment_type">Employment Type <span
                                            style="color: red;">*</span></label>
                                    <input type="text" name="employment_type" id="employment_type" class="form-control"
                                        value="<?php echo $staff_data['Employment Type'] ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="emergency_contact_name">Teacher Department<span
                                            style="color: red;">*</span></label>
                                    <input type="text" id="teacher_department" name="department" class="form-control"
                                        value="<?php echo $staff_data['Department'] ?>" required>
                                </div>
                            </div>

                            <h3>Qualification Details</h3>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_number">Work Experience <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="teacher_experience"
                                        name="teacher_experience"
                                        value="<?php echo $staff_data['qualificationDetails']['experience'] ?? ''; ?>"
                                        required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_holder_name">Highest Qualification<span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="qualification" name="qualification"
                                        value="<?php echo $staff_data['qualificationDetails']['highestQualification'] ?? ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="university">University<span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="university" name="university"
                                        value="<?php echo $staff_data['qualificationDetails']['university'] ?? ''; ?>"
                                        required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="year_of_passing">Year of Passing<span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="year_of_passing" name="year_of_passing"
                                        value="<?php echo $staff_data['qualificationDetails']['yearOfPassing'] ?? ''; ?>"
                                        required>
                                </div>
                            </div>

                            <h3>Bank Details</h3>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_number">Account Number<span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="account_number" name="account_number"
                                        value="<?php echo $staff_data['bankDetails']['accountNumber'] ?? ''; ?>"
                                        required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_holder_name">Account Holder Name<span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="account_holder" name="account_holder"
                                        value="<?php echo $staff_data['bankDetails']['accountHolderName'] ?? ''; ?>"
                                        required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="bank_name">Bank Name<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name"
                                            value="<?php echo $staff_data['bankDetails']['bankName'] ?? ''; ?>"
                                            required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="ifsc_code">IFSC Code<span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="bank_ifsc" name="bank_ifsc"
                                            value="<?php echo $staff_data['bankDetails']['ifscCode'] ?? ''; ?>"
                                            required>
                                    </div>
                                </div>
                            </div>




                            <div class="form-group text-center col-md-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                            <div class="form-group text-center col-sm-3">
                                <button type="cancel" class="btn btn-danger" onclick="goBack()">Cancel</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function goBack() {
    window.history.back();
}

$(document).ready(function() {
    // Handling the form submission using Ajax
    $('#edit_staff_form').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get the form data
        var formData = $(this).serialize();

        // Make the Ajax request
        $.ajax({
            url: $(this).attr('action'), // Using the form action URL
            type: 'POST', // Request type
            data: formData, // Data from the form
            dataType: 'json', // Response type expected
            success: function(response) {
                // Success callback
                if (response.status === 'success') {
                    alert('Staff data updated successfully!');
                    window.location.href = '<?php echo base_url("staff/all_staff"); ?>';
                    // Optionally, redirect or update UI as needed
                } else {
                    alert('Failed to update staff data.');
                }
            },
            error: function(xhr, status, error) {
                // Error callback
                alert('An error occurred: ' + error);
            }
        });
    });
});
</script>

<style>
h3 {
    border-left: 5px solid #007bff;
    padding-left: 10px;
    /* Optional: adds space between the border and the text */
}

.form-row {
    margin-bottom: 20px;
    display: flex;
    /* gap: 20px; */

}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}


/* .form-group input[type="file"] {
        padding: 5px;
    } */

.section-header {
    text-align: center;
    color: #ffffff;
    background-color: #4CAF50;
    font-size: 26px;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 5px;
}
</style>