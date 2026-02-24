<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <div>
                <h2 class="header"><i class="fa fa-user"></i> Admin Administration</h2>
            </div>

            <div class="nav-tabs">
                <a href="#" class="active" onclick="showSection('make-user')">Make Admin</a>
                <a href="#" onclick="showSection('update-password')">Update Password</a>
                <a href="#" onclick="showSection('user-listing')">Admin Listing</a>
            </div>

            <div id="make-user" class="content active">
                <div class="icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <div class="form">
                    <form id="add_admin_form">

                        <div class="form-container">

                            <div class="form-column">
                                <h2>Personal Information</h2>
                                <div class="form-group">
                                    <label for="name">Name <span style="color: red;">*</span></label>
                                    <input type="text" id="name" name="name" placeholder="Enter full name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email ID <span style="color: red;">*</span></label>
                                    <input type="email" id="email" name="email" placeholder="Enter email address"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number <span style="color: red;">*</span></label>
                                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
                                </div>
                                <div class="form-group">
                                    <label for="dob">Date of Birth <span style="color: red;">*</span></label>
                                    <input type="date" id="dob" name="dob" required>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender <span style="color: red;">*</span></label>
                                    <select id="gender" name="gender" required>
                                        <option value="" disabled selected>Select gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role <span style="color: red;">*</span></label>
                                    <select id="role" name="role" required>
                                        <option value="" disabled selected>Select role</option>
                                        <option value="Super Admin">Super Admin</option>
                                        <option value="Accountant">Accountant</option>
                                        <option value="Academic Admin">Academic Admin</option>
                                    </select>
                                </div>
                            </div>


                            <div class="form-column">
                                <h2>Credentials</h2>
                                <div class="form-group">
                                    <label for="adminId">Admin ID <span style="color: red;">*</span></label>
                                    <input type="text" id="admin" name="admin"
                                        value="<?php echo isset($adminId) ? $adminId : 'NA'; ?>" required disabled>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password <span style="color: red;">*</span></label>
                                    <div class="password-container">
                                        <input type="password" id="password" name="password"
                                            placeholder="Enter password" required>
                                        <span toggle="#password" class="fa fa-eye toggle-password"></span>

                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="confirm_password"> Confirm Password <span style="color: red;">*</span></label>
                                    <div class="password-container">

                                        <input type="password" id="confirm_password" name="confirm_password"
                                            placeholder="Re-enter password" required>
                                        <span toggle="#confirm_password" class="fa fa-eye toggle-password"></span>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="buttons">
                            <button type="submit" class="btn btn-danger">Create New Admin</button>

                        </div>
                    </form>
                </div>
            </div>





            <!-- Update Password Section -->
            <div id="update-password" class="content">
                <div class="icon">
                    <i class="fa fa-key"></i>
                </div>
                <div class="form">
                    <form id="updatePasswordForm">
                        <div class="form-group">
                            <label>Select Admin <span style="color: red;">*</span></label>
                            <select name="admin_id" required>
                                <option value="" disabled selected>Select Admin</option>
                                <?php if (!empty($adminList)): ?>
                                    <?php foreach ($adminList as $admin): ?>
                                        <option value="<?= explode(' - ', $admin)[0] ?>"><?= $admin ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No admins found</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>New Password <span style="color: red;">*</span></label>
                            <div class="password-container">
                                <input type="password" id="newPassword" name="newPassword" required>
                                <span toggle="#newPassword" class="fa fa-eye toggle-password"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password <span style="color: red;">*</span></label>
                            <div class="password-container">
                                <input type="password" id="confirmPassword" name="confirmPassword" required>
                                <span toggle="#confirmPassword" class="fa fa-eye toggle-password"></span>
                            </div>
                        </div>
                        <div class="buttons">
                            <button type="submit" class="btn update-password">Update Password</button>

                        </div>
                    </form>
                </div>
            </div>

            <!-- user listing -->
            <div id="user-listing" class="content">
                <div class="user-list">
                    <table class="table table-striped table-bordered dataTable">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Admin Id</th>
                                <th>Admin Name</th>
                                <th>Admin Role</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $srNo = 1;
                            foreach ($activeAdmins as $admin) { ?>
                                <tr>
                                    <td><?php echo $srNo++; ?></td>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><?php echo $admin['name']; ?></td>
                                    <td><?php echo $admin['role']; ?></td>
                                    <td><strong><?php echo $admin['status']; ?></strong></td>
                                    <td>
                                        <button class="btn-action view-details" data-id="<?php echo $admin['id']; ?>">
                                            <i class="fa fa-eye"> View</i>
                                        </button>

                                    </td>
                                </tr>
                            <?php } ?>
                            <?php foreach ($inactiveAdmins as $admin) { ?>
                                <tr>
                                    <td><?php echo $srNo++; ?></td>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><?php echo $admin['name']; ?></td>
                                    <td><?php echo $admin['role']; ?></td>
                                    <td><?php echo $admin['status']; ?></td>
                                    <td>
                                        <button class="btn-action view-details" data-id="<?php echo $admin['id']; ?>">
                                            <i class="fa fa-eye"> View</i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>

            </div>

            <div id="viewAdminModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>
                        <strong>Admin Information: <span id="modal-id"></span></strong>
                    </h2>


                    <form id="user-info-form">
                        <label for="modal-name">Name:</label>
                        <input type="text" id="modal-name" name="name" value="" disabled />

                        <label for="modal-email">Email:</label>
                        <input type="email" id="modal-email" name="email" value="" disabled />

                        <label for="modal-phone">Phone Number:</label>
                        <input type="text" id="modal-phone" name="phone" value="" disabled />

                        <label for="modal-dob">Date of Birth:</label>
                        <input type="date" id="modal-dob" name="dob" value="" disabled />

                        <label for="modal-gender">Gender:</label>
                        <select id="modal-gender" name="gender" disabled>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>

                        <label for="modal-role">Role:</label>
                        <select id="modal-role" name="role" value="" disabled>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Accountant">Accountant</option>
                            <option value="Academic Admin">Academic Admin</option>
                        </select>

                        <label for="modal-status">Status:</label>
                        <label for="modal-status" class="switch">
                            <input type="checkbox" id="modal-status" disabled>
                            <span class="slider round"></span>
                        </label>


                        <div class="button-container">
                            <button type="button" id="edit-btn" onclick="enableEditing()">Edit</button>
                            <button type="button" id="save-btn" onclick="saveChanges()"
                                style="display: none;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function enableEditing() {
        const inputs = document.querySelectorAll("#user-info-form input, #user-info-form select");
        inputs.forEach(input => input.disabled = false); // Enable all input fields
        document.getElementById("edit-btn").style.display = "none";
        document.getElementById("save-btn").style.display = "block";
    }

    function showSection(sectionId) {
        // Remove active class from all tabs
        document.querySelectorAll('.nav-tabs a').forEach(tab => {
            tab.classList.remove('active');
        });

        // Add active class to clicked tab
        event.target.classList.add('active');

        // Hide all content sections
        document.querySelectorAll('.content').forEach(section => {
            section.classList.remove('active');
        });

        // Show the selected content section
        document.getElementById(sectionId).classList.add('active');
    }

    function saveChanges() {
        const modalId = document.getElementById("modal-id").textContent.trim();
        if (!modalId) {
            alert("Modal ID is missing. Cannot save data.");
            return;
        }

        // Collect form data
        const adminData = {
            Name: document.getElementById("modal-name").value || "N/A",
            Email: document.getElementById("modal-email").value || "N/A",
            PhoneNumber: document.getElementById("modal-phone").value || "N/A",
            DOB: document.getElementById("modal-dob").value.split("-").reverse().join("-") || "N/A", // yyyy-mm-dd to dd-mm-yyyy
            Gender: document.getElementById("modal-gender").value || "N/A",
            Role: document.getElementById("modal-role").value || "N/A",
            Status: document.getElementById("modal-status").checked ? "Active" : "Inactive",
        };

        // Send data to server using AJAX
        $.ajax({
            url: "<?php echo base_url() . 'admin/updateUserData' ?>", // Replace with your actual controller/method
            type: "POST",
            data: {
                modal_id: modalId,
                user_data: adminData,
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    alert("Data saved successfully!");
                    window.location.reload(); // Reload the page after successful insertion
                } else {
                    alert("Failed to save data: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error saving data:", error);
                alert("Failed to save data. Please try again.");
            },
        });
    }


    // jQuery AJAX for form submission
    $('#add_admin_form').submit(function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        // Validation: Check if passwords match
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        $.ajax({
            url: "<?php echo base_url() . 'admin/manage_admin' ?>", // Your controller method URL
            type: 'POST',
            data: formData,
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        alert('Admin created successfully!');
                        window.location.reload(); // Reload the page after successful insertion
                    } else {
                        alert(res.message || 'Failed to create admin.');
                    }
                } catch (err) {
                    console.error("Invalid response from server:", response);
                    alert('Unexpected error occurred. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('Error while creating admin!');
            }
        });
    });


    // JavaScript for toggling password visibility
    document.querySelectorAll(".toggle-password").forEach(function(toggle) {
        toggle.addEventListener("click", function() {
            const input = document.querySelector(this.getAttribute("toggle"));
            if (input.type === "password") {
                input.type = "text";
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye");
            }
        });
    });


    // Form submission Of Update Password (AJAX)
    $("#updatePasswordForm").submit(function(e) {
        e.preventDefault(); // Prevent form submission

        var adminId = $("select[name='admin_id']").val(); // Get the admin_id from the <select> dropdown
        var newPassword = $("#newPassword").val(); // Get the new password
        var confirmPassword = $("#confirmPassword").val(); // Get the confirm password



        // Prepare data to send via AJAX
        $.ajax({
            url: '<?= base_url("admin/manage_admin") ?>', // Adjust URL as needed
            method: 'POST',
            data: {
                admin_id: adminId,
                newPassword: newPassword,
                confirmPassword: confirmPassword
            },
            success: function(response) {
                try {
                    // Assuming response is in JSON format
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        alert(result.message); // Success message
                        location.reload();
                    } else {
                        alert(result.message || 'Error updating password');
                    }
                } catch (e) {
                    alert(
                        'An error occurred: Invalid response from the server'
                    ); // Handle invalid JSON response
                }
            },
            error: function(xhr, status, error) {
                alert('Error updating password');
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const viewButtons = document.querySelectorAll(".view-details");
        const modal = document.getElementById("viewAdminModal");
        const closeModalButtons = document.querySelectorAll(".close, .close-modal");

        const adminName = document.getElementById("modal-name");
        const adminRole = document.getElementById("modal-role");
        const adminEmail = document.getElementById("modal-email");
        const adminPhone = document.getElementById("modal-phone");
        const adminDOB = document.getElementById("modal-dob");
        const adminGender = document.getElementById("modal-gender");
        const adminStatus = document.getElementById("modal-status");



        // Open Modal and Fetch Admin Details
        viewButtons.forEach((button) => {
            button.addEventListener("click", function() {
                const adminId = this.getAttribute("data-id");

                // Fetch admin details via AJAX
                fetch("<?php echo base_url(); ?>admin/manage_admin", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: new URLSearchParams({
                            admin_id: adminId,
                        }),
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status === "error") {
                            alert(data.message);
                            return;
                        }

                        const adminData = data.data;

                        // Populate modal fields with admin data
                        document.getElementById("modal-id").textContent = adminId || "N/A";

                        document.getElementById("modal-name").value = adminData.Name || "N/A";
                        document.getElementById("modal-email").value = adminData.Email || "N/A";
                        document.getElementById("modal-phone").value = adminData.PhoneNumber ||
                            "N/A";

                        // Format DOB for input[type="date"]
                        const dob = adminData.DOB ? adminData.DOB.split("-").reverse().join(
                            "-") : ""; // Convert dd-mm-yyyy to yyyy-mm-dd
                        document.getElementById("modal-dob").value = dob;

                        document.getElementById("modal-gender").value = adminData.Gender ||
                            "N/A";
                        document.getElementById("modal-role").value = adminData.Role || "N/A";

                        // Set status toggle
                        const statusToggle = document.getElementById("modal-status");
                        statusToggle.checked = adminData.Status === "Active";

                        // Display the modal
                        modal.style.display = "flex";
                    })
                    .catch((error) => {
                        console.error("Error fetching admin details:", error);
                        alert("Failed to fetch admin details.");
                    });
            });
        });


        // Close Modal
        closeModalButtons.forEach((button) => {
            button.addEventListener("click", function() {
                resetModal();
                modal.style.display = "none";
            });
        });

        // Close Modal on Outside Click
        window.addEventListener("click", function(event) {
            if (event.target === modal) {
                resetModal();
                modal.style.display = "none";
            }
        });

        // Reset Modal Function
        function resetModal() {
            // Disable all inputs in the form
            const inputs = document.querySelectorAll("#user-info-form input, #user-info-form select");
            inputs.forEach(input => input.disabled = true);

            // Reset buttons
            document.getElementById("edit-btn").style.display = "block";
            document.getElementById("save-btn").style.display = "none";
        }

    });
    //Form submission of Update Admins
    $(document).ready(function() {

        // Open modal and fill data
        $('.edit-details').click(function() {
            var adminId = $(this).data('id');

            // Get current admin data (you could fetch this from the database or data attributes)
            $.ajax({
                url: 'manage_admin', // Adjust URL for getting admin details
                type: 'GET',
                data: {
                    id: adminId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Assuming the response is a JSON object with the admin details
                        $('#adminId').val(response.data.id);
                        $('#adminName').val(response.data.name);
                        $('#adminEmail').val(response.data.email);
                        $('#adminPhone').val(response.data.phone);
                        $('#adminRole').val(response.data.role);
                        $('#adminDOB').val(response.data.dob);
                        $('#adminGender').val(response.data.gender);
                        $('#editAdminModal').show(); // Show modal
                    } else {
                        alert("Failed to fetch admin details.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data: " + error);
                }
            });
        });

        // Close modal
        $('.close, .close-modal').click(function() {
            $('#editAdminModal').hide();
        });

        // AJAX form submission
        $('#editAdminForm').submit(function(e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = $(this).serialize(); // Get all form data

            $.ajax({
                url: 'admin/edit_admin', // Update URL to your edit controller
                type: 'POST',
                data: formData,
                success: function(response) {
                    // Handle success (e.g., show success message or reload data)
                    alert("Admin details updated successfully.");
                    $('#editAdminModal').hide(); // Close the modal
                    location.reload(); // Reload the page to see updated data
                },
                error: function(xhr, status, error) {
                    // Handle error
                    alert("An error occurred while updating details.");
                }
            });
        });
    });
</script>

<style>
    .content-wrapper {
        padding: 20px;
        background-color: #f8f9fa;
    }

    /* Container */
    .container {
        width: 75%;
        /* margin: 30px auto; */
        background-color: #fff;
        border: 1px solid #ccc;
        /* padding: 20px; */
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .header {
        background-color: #1b7fcc;
        color: white;
        padding: 15px 20px;
        font-size: 20px;
        display: flex;
        align-items: center;

        justify-content: center;
        /* Centers the content horizontally */
        text-align: center;
    }

    .header i {
        margin-right: 10px;
    }

    /* Navigation Tabs */
    .nav-tabs {
        background-color: #f9f9f9;
        padding: 10px 20px;
        border-bottom: 1px solid #ccc;
    }

    .nav-tabs a {
        text-decoration: none;
        color: #888;
        margin-right: 20px;
        font-weight: bold;
        cursor: pointer;
    }

    .nav-tabs a.active {
        color: #1b7fcc;
        border-bottom: 2px solid #1b7fcc;
    }

    /* Content Section */
    .content {
        display: none;
        padding: 40px;
    }

    .content.active {
        display: flex;
    }

    .icon {
        font-size: 120px;
        color: #555;
        margin-right: 50px;
    }

    .form {
        width: 100%;
    }

    /* Form Styling */
    .form-container {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        /* Space between columns */
    }

    .form-column {
        flex: 1;
        /* Equal width for both columns */
        padding: 10px;
        border: 1px solid #ccc;
        /* Optional: Adds a border for separation */
        border-radius: 5px;
        background-color: #f9f9f9;
        /* Optional: Light background for better visibility */
    }

    h2 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    /* User List Styling */
    .user-list {
        width: 100%;
    }

    .user-list table {
        width: 100%;
        border-collapse: collapse;
    }

    .user-list th,
    .user-list td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
    }

    .user-list th {
        background-color: #1b7fcc;
        color: white;
    }

    .form-group {
        margin-bottom: 20px;
        position: relative;
    }

    /* Styling for the password container */
    .password-container {
        position: relative;
        width: 100%;
    }

    /* Styling for the password input field */
    .password-input {
        width: 100%;
        padding-right: 40px;
        /* Add padding for the icon */
        padding-left: 10px;
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: border-color 0.3s ease;
    }

    /* Add focus effect for the input */
    .password-input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    /* Styling for the toggle icon */
    .toggle-password {
        position: absolute;
        top: 50%;
        right: 10px;
        /* Adjust spacing from the right edge */
        transform: translateY(-50%);
        font-size: 18px;
        color: #888;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    /* Hover effect for the toggle icon */
    .toggle-password:hover {
        color: #007bff;
    }

    /* General styling for the action buttons */
    .btn-action {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
        margin: 0 5px;
        color: #007bff;
    }

    .btn-action:hover {
        color: #0056b3;
    }


    .buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        text-align: center;
    }

    .btn {
        padding: 10px 20px;
        font-size: 14px;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* .btn.make-user {
        background-color: #1b7fcc;
    } */

    .btn.update-password {
        background-color: #28a745;
    }

    .btn.close {
        background-color: #888;
    }

    button.btn {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button.btn-secondary {
        background: #6c757d;
        color: #fff;
    }

    /* button.btn-secondary:hover {
    background: #5a6268;
} */






    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 25px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 54px;
        height: 28px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 28px;
    }

    .slider.round:before {
        border-radius: 50%;
    }


    .modal-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        /* Default to a smaller width for mobile */
        max-width: 600px;
        /* Limit the width for larger screens */
        max-height: 80vh;
        /* Limit the height to 80% of the viewport */
        position: relative;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        /* Enable scrolling if content exceeds height */
    }

    .modal-content .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #888;
    }

    .modal-content .close:hover {
        color: red;
    }



    .modal-content h2 {
        text-align: center;
        margin-bottom: 20px;
    }


    .modal-content form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .modal-content label {
        font-weight: bold;
    }

    .modal-content input,
    .modal-content select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
    }

    .modal-content .button-container {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .modal-content button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    #edit-btn {
        background-color: #007bff;
        color: white;
    }

    #save-btn {
        background-color: #28a745;
        color: white;
    }


    /* Column styles */
    .table.example th,
    .table.example td {
        text-align: center;
        /* Center-align text */
        padding: 20px 25px;
        /* Adjust padding as needed */
        overflow: hidden;
        /* Prevent content overflow */
        text-overflow: ellipsis;
        /* Add ellipsis for overflowing text */
        white-space: nowrap;
        /* Prevent text wrapping */
    }
</style>