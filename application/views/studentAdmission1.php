<div class="content-wrapper">

    <section class="content-header custom-header">

        <h1 class="page-title">
            <i class="fa fa-user-plus text-primary"></i>
            Add New Student
        </h1>

        <ol class="breadcrumb custom-breadcrumb">
            <li>
                <a href="<?= base_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= base_url('student/all_student') ?>">
                    All Students
                </a>
            </li>
            <li class="active">Add Student</li>
        </ol>

    </section>

    <section class="content">
        <div class="box box-primary erp-card">
            <div class="box-body">
                <form action="<?php echo base_url() . 'student/studentAdmission' ?>"
                    method="post"
                    id="add_student_form"
                    enctype="multipart/form-data">

                    <!-- ================= BASIC INFORMATION ================= -->
                    <fieldset class="styled-fieldset">
                        <legend><i class="fa fa-user"></i> Student Basic Information</legend>

                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label for="user_id">Student ID</label>
                                <input type="text" name="user_id" id="user_id"
                                    value="<?= $user_Id ?>" class="form-control" readonly>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="sname">Student Name</label>
                                <input type="text" name="Name" id="sname"
                                    class="form-control" placeholder="Enter Student Name" required>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="class_name">Class *</label>
                                <select id="class_name" name="class"
                                    class="form-control" required>
                                    <option value="" disabled selected>Select Class</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="section">Section *</label>
                                <select id="section" name="section"
                                    class="form-control" required>
                                    <option value="" disabled selected>Select Section</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label for="dob">Date of Birth</label>
                                <input type="date" name="dob" id="dob"
                                    class="form-control" required>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender"
                                    class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="admission_date">Admission Date</label>
                                <input type="date" name="admission_date"
                                    id="admission_date" class="form-control" required>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="email_user">Email</label>
                                <input type="email" name="email"
                                    id="email_user" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" name="phone_number"
                                    id="phone_number" class="form-control" required>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="blood_group">Blood Group</label>
                                <select name="blood_group" id="blood_group"
                                    class="form-control" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="category">Category</label>
                                <select name="category" id="category"
                                    class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="General">General</option>
                                    <option value="OBC">OBC</option>
                                    <option value="SC">SC</option>
                                    <option value="ST">ST</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label>Additional Subjects *</label>
                                <div id="subject_checkbox_group">
                                    <p class="text-muted mb-0">Select a class to view subjects.</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= Parents Details ================= -->
                    <fieldset class="styled-fieldset">

                        <legend><i class="fa fa-phone"></i> Parents Details</legend>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="father_name">Father's Name</label>
                                <input type="text"
                                    name="father_name"
                                    id="father_name"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="father_occupation">Father's Occupation</label>
                                <input type="text"
                                    name="father_occupation"
                                    id="father_occupation"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="guard_contact">Father's Contact</label>
                                <input type="text"
                                    name="guard_contact"
                                    id="guard_contact"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="guard_relation">Guardian Relation</label>
                                <input type="text"
                                    name="guard_relation"
                                    id="guard_relation"
                                    class="form-control"
                                    required>
                            </div>


                            <div class="form-group col-md-3">
                                <label for="mother_name">Mother's Name</label>
                                <input type="text"
                                    name="mother_name"
                                    id="mother_name"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="mother_occupation">Mother's Occupation</label>
                                <input type="text"
                                    name="mother_occupation"
                                    id="mother_occupation"
                                    class="form-control"
                                    required>
                            </div>


                        </div>
                    </fieldset>


                    <!-- ================= ADDRESS ================= -->
                    <fieldset class="styled-fieldset">

                        <legend><i class="fa fa-map-marker"></i> Address Details</legend>


                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="street">Street</label>
                                <input type="text"
                                    name="street"
                                    id="street"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="state">State</label>
                                <select name="state"
                                    id="state"
                                    class="form-control"
                                    required>
                                    <option value="">Select State</option>

                                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                    <option value="Assam">Assam</option>
                                    <option value="Bihar">Bihar</option>
                                    <option value="Chhattisgarh">Chhattisgarh</option>
                                    <option value="Goa">Goa</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Himachal Pradesh">Himachal Pradesh</option>
                                    <option value="Jharkhand">Jharkhand</option>
                                    <option value="Karnataka">Karnataka</option>
                                    <option value="Kerala">Kerala</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Manipur">Manipur</option>
                                    <option value="Meghalaya">Meghalaya</option>
                                    <option value="Mizoram">Mizoram</option>
                                    <option value="Nagaland">Nagaland</option>
                                    <option value="Odisha">Odisha</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Sikkim">Sikkim</option>
                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                    <option value="Telangana">Telangana</option>
                                    <option value="Tripura">Tripura</option>
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                    <option value="Uttarakhand">Uttarakhand</option>
                                    <option value="West Bengal">West Bengal</option>

                                    <!-- Union Territories -->
                                    <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                    <option value="Chandigarh">Chandigarh</option>
                                    <option value="Dadra and Nagar Haveli and Daman and Diu">
                                        Dadra and Nagar Haveli and Daman and Diu
                                    </option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                    <option value="Ladakh">Ladakh</option>
                                    <option value="Lakshadweep">Lakshadweep</option>
                                    <option value="Puducherry">Puducherry</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="city">District</label>
                                <select name="city"
                                    id="city"
                                    class="form-control"
                                    required>
                                    <option value="">Select District</option>
                                </select>
                            </div>




                            <div class="form-group col-md-3">
                                <label for="postal_code">Postal Code</label>
                                <input type="text"
                                    name="postal_code"
                                    id="postal_code"
                                    class="form-control"
                                    required>
                            </div>

                        </div>
                    </fieldset>

                    <!-- ================= PREVIOUS SCHOOL ================= -->
                    <fieldset class="styled-fieldset">

                        <legend><i class="fa fa-university"></i> Previous Schools Details</legend>


                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label for="pre_class">Previous Class</label>
                                <input type="text"
                                    name="pre_class"
                                    id="pre_class"
                                    class="form-control"
                                    required>
                            </div>


                            <div class="form-group col-md-3">
                                <label for="pre_school">Previous School</label>
                                <input type="text"
                                    name="pre_school"
                                    id="pre_school"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="pre_marks">Previous Marks%</label>
                                <input type="text"
                                    name="pre_marks"
                                    id="pre_marks"
                                    class="form-control"
                                    required>
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= OTHER DETAILS ================= -->
                    <fieldset class="styled-fieldset">

                        <legend><i class="fa fa-info-circle"></i> Other Students Details</legend>


                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label for="religion">Religion</label>
                                <select name="religion"
                                    id="religion"
                                    class="form-control"
                                    onchange="toggleOtherReligion(this)"
                                    required>
                                    <option value="">Select Religion</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Muslim">Muslim</option>
                                    <option value="Sikh">Sikh</option>
                                    <option value="Jain">Jain</option>
                                    <option value="Buddh">Buddh</option>
                                    <option value="Christian">Christian</option>
                                    <option value="Other">Other</option>
                                </select>

                                <input type="text"
                                    name="other_religion"
                                    id="other_religion"
                                    class="form-control mt-2"
                                    placeholder="Please specify"
                                    style="display:none;">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="nationality">Nationality</label>
                                <input type="text"
                                    name="nationality"
                                    id="nationality"
                                    class="form-control"
                                    required>
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= DOCUMENTS ================= -->
                    <fieldset class="styled-fieldset">

                        <legend><i class="fa fa-file-text-o"></i> Documents Details</legend>


                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="birthCertificate">Birth Certificate</label>
                                <input type="file"
                                    name="birthCertificate"
                                    id="birthCertificate"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="aadharCard">Aadhar Card</label>
                                <input type="file"
                                    name="aadharCard"
                                    id="aadharCard"
                                    class="form-control"
                                    required>
                            </div>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="schoolLeavingCertificate">Transfer Certificate</label>
                                <input type="file"
                                    name="transferCertificate"
                                    id="transferCertificate"
                                    class="form-control"
                                    required>
                            </div>

                        </div>
                    </fieldset>


                    <fieldset class="styled-fieldset">

                        <div class="form-row">

                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Fees to be exempted for the student</label>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select_all_exempted_fees">
                                    <label class="form-check-label" for="select_all_exempted_fees">
                                        Select All Fees
                                    </label>
                                </div>

                                <div class="form-check-group">
                                    <?php if (isset($exemptedFees) && is_array($exemptedFees)): ?>
                                        <?php foreach ($exemptedFees as $feeType => $fees): ?>
                                            <?php if (is_array($fees)): // ensure that fees is an array 
                                            ?>
                                                <?php foreach ($fees as $feeKey => $feeValue): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="fee_<?php echo htmlspecialchars($feeKey); ?>"
                                                            name="exempted_fees_multiple[]"
                                                            value="<?php echo htmlspecialchars($feeKey); ?>">
                                                        <label class="form-check-label" for="fee_<?php echo htmlspecialchars($feeKey); ?>">
                                                            <?php echo htmlspecialchars($feeKey); ?>
                                                            (<?php echo htmlspecialchars($feeType); ?>)
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No fee options available</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group col-md-8">
                                <label for="student_photo">
                                    <i class="glyphicon glyphicon-user"></i> Passport Size Photo
                                </label>

                                <input type="file"
                                    class="form-control"
                                    name="student_photo"
                                    id="student_photo"
                                    accept="image/*"
                                    onchange="previewPassportPhoto(event)"
                                    required>

                                <img id="passportPhotoPreview"
                                    class="img-thumbnail mt-2"
                                    src="<?= base_url('tools/dist/img/kids.jpg') ?>"
                                    style="width:170px;height:200px;object-fit:cover;">
                            </div>

                        </div>

                    </fieldset>




                    <div class="form-row mt-4">
                        <div class="form-group col-md-12 text-right">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fa fa-refresh"></i> Reset
                            </button>

                            <button type="button"
                                id="submitStudentForm"
                                onclick="previewFormBeforeSubmit(event)"
                                class="btn btn-primary ml-2">
                                <i class="fa fa-paper-plane"></i> Preview
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>




<!-- =========================================
     ADMISSION PREVIEW MODAL
========================================= -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content admission-preview">

            <!-- ================= HEADER ================= -->
            <div class="modal-header preview-header text-center">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <p class="school-session">Student Admission Confirmation</p>
            </div>

            <div class="modal-body preview-body">

                <!-- ================= STUDENT BASIC BLOCK ================= -->
                <div class="preview-card">

                    <div class="row">
                        <div class="col-sm-9">
                            <h3 class="student-name" id="previewName"></h3>

                            <table class="table table-bordered preview-table">
                                <tr>
                                    <th width="30%">Student Admission ID</th>
                                    <td id="previewId"></td>
                                </tr>
                                <tr>
                                    <th>Class</th>
                                    <td>
                                        <span id="previewClass"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Section</th>
                                    <td>
                                        <span id="previewSection"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Admission Date</th>
                                    <td id="previewAdmissionDate"></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-sm-3 text-right">
                            <img id="previewPhoto" class="preview-photo">
                        </div>
                    </div>

                </div>


                <!-- ================= ACADEMIC DETAILS ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-graduation-cap"></i> Academic Details</h4>

                    <table class="table table-striped preview-table">
                        <tr>
                            <th>DOB</th>
                            <td id="previewDob"></td>

                            <th>Gender</th>
                            <td id="previewGender"></td>
                        </tr>

                        <tr>
                            <th>Blood Group</th>
                            <td id="previewBloodGroup"></td>

                            <th>Category</th>
                            <td id="previewCategory"></td>
                        </tr>

                        <tr>
                            <th>Additional Subjects</th>
                            <td colspan="3" id="previewSubjects"></td>
                        </tr>
                    </table>
                </div>


                <!-- ================= CONTACT DETAILS ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-phone"></i> Contact Details</h4>

                    <table class="table table-striped preview-table">
                        <tr>
                            <th>Phone</th>
                            <td id="previewPhone"></td>

                            <th>Email</th>
                            <td id="previewEmail"></td>
                        </tr>

                        <tr>
                            <th>Nationality</th>
                            <td id="previewNationality"></td>

                            <th>Religion</th>
                            <td id="previewReligion"></td>
                        </tr>
                    </table>
                </div>


                <!-- ================= PARENT DETAILS ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-users"></i> Parent Details</h4>

                    <table class="table table-striped preview-table">
                        <tr>
                            <th>Father Name</th>
                            <td id="previewFatherName"></td>

                            <th>Father Occupation</th>
                            <td id="previewFatherOccupation"></td>
                        </tr>

                        <tr>
                            <th>Mother Name</th>
                            <td id="previewMotherName"></td>

                            <th>Mother Occupation</th>
                            <td id="previewMotherOccupation"></td>
                        </tr>

                        <tr>
                            <th>Parents Contact</th>
                            <td colspan="3" id="previewGuardianContact"></td>
                        </tr>
                        <tr>
                            <th>Guardian Relation</th>
                            <td colspan="3" id="previewGuardianRelation"></td>
                        </tr>

                    </table>
                </div>


                <!-- ================= ADDRESS ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-map-marker"></i> Address</h4>

                    <p class="address-block">
                        <span id="previewStreet"></span>,
                        <span id="previewCity"></span>,
                        <span id="previewState"></span> -
                        <span id="previewPostalCode"></span>
                    </p>
                </div>


                <!-- ================= PREVIOUS SCHOOL ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-university"></i> Previous School Details</h4>

                    <table class="table table-striped preview-table">
                        <tr>
                            <th>Previous Class</th>
                            <td id="previewPreClass"></td>

                            <th>Marks %</th>
                            <td id="previewPreMarks"></td>
                        </tr>

                        <tr>
                            <th>School Name</th>
                            <td colspan="3" id="previewPreSchool"></td>
                        </tr>
                    </table>
                </div>

                <!-- ================= DOCUMENTS ================= -->
                <div class="preview-card">
                    <h4 class="section-title">
                        <i class="fa fa-file-text"></i> Uploaded Documents
                    </h4>

                    <table class="table table-bordered preview-table">
                        <tr>
                            <th width="30%">Birth Certificate</th>
                            <td>
                                <span id="previewBirthCertificateName"></span>
                                &nbsp;
                                <a href="#" target="_blank" id="previewBirthCertificateView"
                                    class="btn btn-xs btn-info" style="display:none;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <th>Aadhar Card</th>
                            <td>
                                <span id="previewAadharCardName"></span>
                                &nbsp;
                                <a href="#" target="_blank" id="previewAadharCardView"
                                    class="btn btn-xs btn-info" style="display:none;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <th>School Leaving Certificate</th>
                            <td>
                                <span id="previewSchoolLeavingName"></span>
                                &nbsp;
                                <a href="#" target="_blank" id="previewSchoolLeavingView"
                                    class="btn btn-xs btn-info" style="display:none;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    </table>

                    <small class="text-muted">
                        If document is incorrect, click "Edit" and re-upload correct file.
                    </small>
                </div>


                <!-- ================= FEES ================= -->
                <div class="preview-card">
                    <h4 class="section-title"><i class="fa fa-money"></i> Fee Exemptions</h4>
                    <p id="previewFees"></p>
                </div>


            </div>

            <!-- ================= FOOTER ================= -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-edit"></i> Edit
                </button>

                <button type="button" class="btn btn-success" onclick="submitFinalForm()">
                    <i class="fa fa-check"></i>Final Submit
                </button>
            </div>

        </div>
    </div>
</div>


<script>

    function showAlert(type, message) {

        const colors = {
            success: "#28a745",
            error: "#dc3545",
            warning: "#ffc107",
            info: "#17a2b8"
        };

        const alertBox = document.createElement("div");
        alertBox.innerText = message;
        alertBox.style.position = "fixed";
        alertBox.style.top = "20px";
        alertBox.style.right = "20px";
        alertBox.style.padding = "12px 18px";
        alertBox.style.background = colors[type] || "#333";
        alertBox.style.color = "#fff";
        alertBox.style.borderRadius = "6px";
        alertBox.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";
        alertBox.style.zIndex = "9999";
        alertBox.style.fontSize = "13px";
        alertBox.style.animation = "fadeIn 0.3s ease";
        document.body.appendChild(alertBox);

        setTimeout(() => {
            alertBox.remove();
        }, 3000);
    }

    function validateAdmissionForm() {

        let isValid = true;

        // Remove old errors
        $(".form-group").removeClass("has-error");
        $(".error-message").remove();
        $("#subject_checkbox_group .error-message").remove(); // clear subject errors too




        function getValue(id) {
            const el = document.getElementById(id);
            if (!el) return '';
            return (el.value || '').trim();
        }

        function showError(inputId, message) {
            const input = $("#" + inputId);
            if (input.length) {
                input.closest(".form-group").addClass("has-error");
                input.after('<span class="error-message text-danger small d-block mt-1">' +
                    '<i class="fa fa-exclamation-circle"></i> ' + message + '</span>');
            }
            isValid = false;
        }

        /* ===============================
           BASIC VALIDATION
        =============================== */

        if (!getValue("sname")) {
            showError("sname", "Student name is required");
        }

        if (!getValue("class_name")) {
            showError("class_name", "Please select class");
        }

        if (!getValue("section")) {
            showError("section", "Please select section");
        }

        if (!getValue("dob")) {
            showError("dob", "Date of birth is required");
        }

        if (!getValue("admission_date")) {
            showError("admission_date", "Admission date is required");
        }

        /* ===============================
           EMAIL VALIDATION
        =============================== */

        const email = getValue("email_user");
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            showError("email_user", "Enter valid email address");
        }

        /* ===============================
           PHONE VALIDATION
        =============================== */

        const phone = getValue("phone_number");
        const phonePattern = /^[6-9]\d{9}$/;

        if (!phonePattern.test(phone)) {
            showError("phone_number", "Enter valid 10-digit mobile number");
        }

        /* ===============================
           POSTAL CODE VALIDATION
        =============================== */

        const postal = getValue("postal_code");
        const postalPattern = /^[1-9][0-9]{5}$/;

        if (!postalPattern.test(postal)) {
            showError("postal_code", "Enter valid 6-digit PIN code");
        }

        /* ===============================
           MARKS VALIDATION
        =============================== */

        const marks = getValue("pre_marks");
        const marksPattern = /^[0-9]{1,3}%?$/;

        if (!marksPattern.test(marks)) {
            showError("pre_marks", "Enter valid percentage (e.g. 85%)");
        }

        /* ===============================
           SUBJECT VALIDATION
        =============================== */

        const subjectBox = document.getElementById("subject_checkbox_group");
        const allSubjectBoxes = subjectBox ?
            subjectBox.querySelectorAll('input[type="checkbox"]') : [];
        const checkedSubjects = subjectBox ?
            subjectBox.querySelectorAll('input[type="checkbox"]:checked').length :
            0;

        // Only require a subject if subjects were actually loaded for the selected class
        if (allSubjectBoxes.length > 0 && checkedSubjects === 0) {
            const span = document.createElement('span');
            span.className = 'error-message text-danger small d-block mt-1';
            span.innerHTML = '<i class="fa fa-exclamation-circle"></i> Select at least one subject';
            subjectBox.parentNode.appendChild(span);
            isValid = false;
        }

        /* ===============================
           FILE VALIDATION
        =============================== */

        function validateFile(inputId, allowedTypes, maxSizeMB) {

            const fileInput = document.getElementById(inputId);
            if (!fileInput) return;
            if (!fileInput.files.length) {
                showError(inputId, "File is required");
                return;
            }

            const file = fileInput.files[0];

            if (!allowedTypes.includes(file.type)) {
                showError(inputId, "Invalid file format (allowed: PDF, JPG, PNG)");
            }

            if (file.size > maxSizeMB * 1024 * 1024) {
                showError(inputId, "File size must be under " + maxSizeMB + "MB");
            }
        }

        const docTypes = ["image/jpeg", "image/png", "application/pdf"];
        validateFile("birthCertificate", docTypes, 2);
        validateFile("aadharCard", docTypes, 2);
        validateFile("transferCertificate", docTypes, 2);
        validateFile("student_photo", ["image/jpeg", "image/png", "image/webp"], 2);



        // validateFile("birthCertificate", ["image/jpeg", "image/png", "application/pdf"], 2);
        // validateFile("aadharCard", ["image/jpeg", "image/png", "application/pdf"], 2);
        // // validateFile("schoolLeavingCertificate", ["image/jpeg", "image/png", "application/pdf"], 2);
        // validateFile("transferCertificate", ["image/jpeg", "image/png", "application/pdf"], 2);

        // validateFile("student_photo", ["image/jpeg", "image/png"], 2);

        /* ===============================
           SCROLL TO FIRST ERROR
        =============================== */

        if (!isValid) {
            const firstError = document.querySelector(".has-error, .error-message");
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }
        }

        return isValid;
    }


    function previewFormBeforeSubmit(event) {
        event.preventDefault();

        var form = document.getElementById("add_student_form");

        if (!validateAdmissionForm()) {
            return;
        }

        // Fill preview data
        fillPreviewData();

        // Show Bootstrap 3 modal
        $('#previewModal').modal('show');
    }


    function fillPreviewData() {

        const getValue = id => document.getElementById(id)?.value || '';

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.innerText = value || '';
        };

        const setImage = (id, src) => {
            const el = document.getElementById(id);
            if (el) el.src = src;
        };

        /* ================= BASIC INFO ================= */

        setText("previewName", getValue("sname"));
        setText("previewId", getValue("user_id"));
        setText("previewClass", getValue("class_name"));
        setText("previewSection", getValue("section"));
        setText("previewAdmissionDate", getValue("admission_date"));

        setText("previewDob", getValue("dob"));
        setText("previewGender", getValue("gender"));
        setText("previewBloodGroup", getValue("blood_group"));
        setText("previewCategory", getValue("category"));

        setText("previewPhone", getValue("phone_number"));
        setText("previewEmail", getValue("email_user"));
        setText("previewNationality", getValue("nationality"));

        /* ================= RELIGION ================= */

        let religion = getValue("religion");
        if (religion === "Other") {
            religion = getValue("other_religion");
        }
        setText("previewReligion", religion);

        /* ================= PARENTS ================= */

        setText("previewFatherName", getValue("father_name"));
        setText("previewFatherOccupation", getValue("father_occupation"));
        setText("previewMotherName", getValue("mother_name"));
        setText("previewMotherOccupation", getValue("mother_occupation"));
        setText("previewGuardianContact", getValue("guard_contact"));
        setText("previewGuardianRelation", getValue("guard_relation"));

        /* ================= ADDRESS ================= */

        setText("previewStreet", getValue("street"));
        setText("previewCity", getValue("city"));
        setText("previewState", getValue("state"));
        setText("previewPostalCode", getValue("postal_code"));

        /* ================= PREVIOUS SCHOOL ================= */

        setText("previewPreClass", getValue("pre_class"));
        setText("previewPreSchool", getValue("pre_school"));
        setText("previewPreMarks", getValue("pre_marks"));

        /* ================= SUBJECTS ================= */

        const subjectBox = document.getElementById("subject_checkbox_group");
        let selectedSubjects = [];

        if (subjectBox) {
            selectedSubjects = Array.from(
                subjectBox.querySelectorAll('input[type="checkbox"]:checked')
            ).map(cb => cb.value);
        }

        setText("previewSubjects",
            selectedSubjects.length ? selectedSubjects.join(", ") : "None"
        );

        /* ================= FEES ================= */

        const selectedFees = Array.from(
            document.querySelectorAll('input[name="exempted_fees_multiple[]"]:checked')
        ).map(cb => cb.value);

        setText("previewFees",
            selectedFees.length ? selectedFees.join(", ") : "None"
        );

        /* ================= DOCUMENTS ================= */

        handleDocumentPreview(
            "birthCertificate",
            "previewBirthCertificateName",
            "previewBirthCertificateView"
        );

        handleDocumentPreview(
            "aadharCard",
            "previewAadharCardName",
            "previewAadharCardView"
        );

        // handleDocumentPreview(
        //     "schoolLeavingCertificate",
        //     "previewSchoolLeavingName",
        //     "previewSchoolLeavingView"
        // );

        handleDocumentPreview(
            "transferCertificate",
            "previewSchoolLeavingName",
            "previewSchoolLeavingView"
        );


        /* ================= PHOTO ================= */

        const photoPreview = document.getElementById("passportPhotoPreview");
        if (photoPreview) {
            setImage("previewPhoto", photoPreview.src);
        }
    }



    function handleDocumentPreview(inputId, nameId, viewId) {
        const input = document.getElementById(inputId);
        const nameSpan = document.getElementById(nameId);
        const viewBtn = document.getElementById(viewId);

        if (!input || !nameSpan || !viewBtn) return;

        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            nameSpan.innerText = file.name;

            const fileURL = URL.createObjectURL(file);
            viewBtn.href = fileURL;
            viewBtn.style.display = "inline-block";
        } else {
            nameSpan.innerText = "Not Uploaded";
            viewBtn.style.display = "none";
        }
    }

    // function previewPassportPhoto(event) {
    //     const file = event.target.files[0];
    //     if (!file) return;

    //     const reader = new FileReader();
    //     reader.onload = function(e) {
    //         document.getElementById("passportPhotoPreview").src = e.target.result;
    //     };
    //     reader.readAsDataURL(file);
    // }
    
    
    function previewPassportPhoto(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        const preview = document.getElementById('passportPhotoPreview');
        if (!preview) return; // FIX: null-check before .src assignment

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }



    function submitFinalForm() {

        const form = document.getElementById('add_student_form');
        if (!form) { // FIX: guard before new FormData(form)
            showAlert('error', 'Form not found — please refresh the page.');
            return;
        }

        const formData = new FormData(form);
        const btn = document.querySelector('#previewModal .btn-success');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        }

        // /* ===============================
        //    ADDITIONAL SUBJECTS
        // =============================== */
        // const subjectBox = document.getElementById("subject_checkbox_group");

        // if (subjectBox) {
        //     const selectedSubjects = Array.from(
        //         subjectBox.querySelectorAll('input[type="checkbox"]:checked')
        //     ).map(cb => cb.value.trim());

        //     selectedSubjects.forEach(sub => {
        //         formData.append('additional_subjects[]', sub);
        //     });
        // }

        // const url = form.action;

        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                let res;
                try {
                    res = typeof response === 'string' ? JSON.parse(response) : response;
                } catch (e) {
                    showAlert('error', 'Unexpected server response. Please try again.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-check"></i> Final Submit';
                    }
                    return;
                }

                if (res.status === 'success') {
                    $('#previewModal').modal('hide');
                    showAlert('success', res.message || 'Student admitted successfully!');
                    setTimeout(() => location.reload(), 1600);
                } else {
                    showAlert('error', res.message || 'Submission failed. Please try again.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-check"></i> Final Submit';
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                showAlert('error', 'Server error — please try again.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-check"></i> Final Submit';
                }
            }
        });
    }

    function toggleOtherReligion(selectElement) {
        const otherInput = document.getElementById('other_religion');
        if (!otherInput) return;
        if (selectElement.value === 'Other') {
            otherInput.style.display = 'block';
            otherInput.required = true;
        } else {
            otherInput.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
        }
    }


    /* =========================================
       SELECT ALL FEES CHECKBOX
    ========================================= */
    document.addEventListener("DOMContentLoaded", function() {

        const selectAll = document.getElementById("select_all_exempted_fees");
        const feeCheckboxes = document.querySelectorAll('input[name="exempted_fees_multiple[]"]');

        if (selectAll) {
            selectAll.addEventListener("change", function() {
                feeCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }

        feeCheckboxes.forEach(cb => {
            cb.addEventListener("change", function() {
                const allChecked = Array.from(feeCheckboxes).every(c => c.checked);
                selectAll.checked = allChecked;
            });
        });

    });


    /* =========================================
       CLASS → SECTION → SUBJECT FLOW
    ========================================= */
    document.addEventListener('DOMContentLoaded', function() {

        const classSelect = document.getElementById('class_name');
        const sectionSelect = document.getElementById('section');
        const subjectBox = document.getElementById('subject_checkbox_group');

        if (!classSelect || !sectionSelect || !subjectBox) return;


        /* ===== FETCH CLASSES ===== */
        fetch('get_classes')
            .then(res => res.json())
            .then(classes => {
                if (!Array.isArray(classes)) return;

                classes.forEach(cls => {
                    const opt = document.createElement('option');
                    opt.value = cls;
                    opt.textContent = cls;
                    classSelect.appendChild(opt);
                });
            })
            .catch(err => console.error('Class fetch failed:', err));


        /* ===== CLASS CHANGE → FETCH SECTIONS ===== */
        classSelect.addEventListener('change', function() {

            const selectedClass = this.value;

            sectionSelect.innerHTML =
                '<option value="" disabled selected>Loading sections...</option>';

            subjectBox.innerHTML =
                '<p class="text-muted">Select Section to view subjects.</p>';

            fetch('get_sections_by_class', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        class_name: selectedClass
                    })
                })
                .then(res => res.json())
                .then(sections => {

                    sectionSelect.innerHTML =
                        '<option value="" disabled selected>Select Section</option>';

                    if (!Array.isArray(sections)) return;

                    sections.forEach(sec => {
                        const opt = document.createElement('option');
                        opt.value = sec;
                        opt.textContent = sec;
                        sectionSelect.appendChild(opt);
                    });

                })
                .catch(err => {
                    console.error('Section fetch failed:', err);
                });

        });


        /* ===== SECTION CHANGE → FETCH SUBJECTS ===== */
        // sectionSelect.addEventListener('change', function() {

        //     const selectedClass = classSelect.value;

        //     subjectBox.innerHTML =
        //         '<p class="text-muted">Loading subjects...</p>';

        //     fetch('fetch_subjects', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json'
        //             },
        //             body: JSON.stringify({
        //                 class_name: selectedClass
        //             })
        //         })
        //         .then(res => res.json())
        //         .then(subjects => {

        //             subjectBox.innerHTML = '';

        //             if (!Array.isArray(subjects) || subjects.length === 0) {
        //                 subjectBox.innerHTML =
        //                     '<p class="text-muted">No additional subjects available.</p>';
        //                 return;
        //             }

        //             subjects.forEach(subject => {

        //                 const id = `sub_${subject.replace(/\s+/g, '_')}`;

        //                 subjectBox.insertAdjacentHTML('beforeend', `
        //             <div class="form-check mb-1">
        //                 <input type="checkbox"
        //                        class="form-check-input"
        //                        name="additional_subjects[]"
        //                        id="${id}"
        //                        value="${subject}">
        //                 <label class="form-check-label" for="${id}">
        //                     ${subject}
        //                 </label>
        //             </div>
        //         `);
        //             });

        //         })
        //         .catch(err => {
        //             console.error('Subject fetch failed:', err);
        //             subjectBox.innerHTML =
        //                 '<p class="text-danger">Failed to load subjects.</p>';
        //         });

        // });


        /* ===== SECTION CHANGE → FETCH SUBJECTS ===== */
        sectionSelect.addEventListener('change', function() {

            const selectedClass = classSelect.value;
            const selectedSection = sectionSelect.value;

            if (!selectedClass || !selectedSection) {
                subjectBox.innerHTML =
                    '<p class="text-muted">Select class & section first.</p>';
                return;
            }

            subjectBox.innerHTML =
                '<p class="text-muted">Loading subjects...</p>';

            fetch('fetch_subjects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        class_name: selectedClass
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error("Invalid JSON response");
                    return res.json();
                })
                .then(subjects => {

                    subjectBox.innerHTML = '';

                    if (!Array.isArray(subjects) || subjects.length === 0) {
                        subjectBox.innerHTML =
                            '<p class="text-muted">No additional subjects available.</p>';
                        return;
                    }

                    subjects.forEach(subject => {

                        // ✅ SAFE ID
                        const id = `sub_${subject.replace(/[^a-zA-Z0-9]/g, '_')}`;

                        // ✅ wrapper
                        const wrap = document.createElement('div');
                        wrap.className = 'form-check mb-1';

                        // ✅ checkbox
                        const inp = document.createElement('input');
                        inp.type = 'checkbox';
                        inp.className = 'form-check-input';
                        inp.name = 'additional_subjects[]';
                        inp.id = id;
                        inp.value = subject;

                        // ✅ label
                        const lbl = document.createElement('label');
                        lbl.className = 'form-check-label';
                        lbl.htmlFor = id;
                        lbl.textContent = subject;

                        wrap.appendChild(inp);
                        wrap.appendChild(lbl);

                        subjectBox.appendChild(wrap);
                    });

                })
                .catch(err => {
                    console.error('Subject fetch failed:', err);
                    subjectBox.innerHTML =
                        '<p class="text-danger">Failed to load subjects.</p>';
                });

        });


    });


    const stateDistricts = {
        "Uttar Pradesh": ["Agra", "Aligarh", "Allahabad", "Ambedkar Nagar", "Amethi", "Amroha", "Auraiya", "Azamgarh", "Baghpat", "Bahraich", "Ballia", "Balrampur", "Banda", "Barabanki", "Bareilly", "Basti", "Bhadohi", "Bijnor", "Budaun", "Bulandshahr", "Chandauli", "Chitrakoot", "Deoria", "Etah", "Etawah", "Faizabad", "Farrukhabad", "Fatehpur", "Firozabad", "Gautam Buddha Nagar", "Ghaziabad", "Ghazipur", "Gonda", "Gorakhpur", "Hamirpur", "Hapur", "Hardoi", "Hathras", "Jalaun", "Jaunpur", "Jhansi", "Kannauj", "Kanpur Dehat", "Kanpur Nagar", "Kasganj", "Kaushambi", "Kushinagar", "Lakhimpur Kheri", "Lalitpur", "Lucknow", "Maharajganj", "Mahoba", "Mainpuri", "Mathura", "Mau", "Meerut", "Mirzapur", "Moradabad", "Muzaffarnagar", "Pilibhit", "Pratapgarh", "Raebareli", "Rampur", "Saharanpur", "Sambhal", "Sant Kabir Nagar", "Shahjahanpur", "Shamli", "Shravasti", "Siddharthnagar", "Sitapur", "Sonbhadra", "Sultanpur", "Unnao", "Varanasi"],
        "Delhi": ["Central Delhi", "East Delhi", "New Delhi", "North Delhi", "North East Delhi", "North West Delhi", "South Delhi", "South East Delhi", "South West Delhi", "West Delhi"],
        "Maharashtra": ["Mumbai", "Pune", "Nagpur", "Nashik", "Thane", "Aurangabad", "Solapur", "Kolhapur", "Amravati", "Nanded", "Sangli", "Jalgaon", "Latur"]
    };

    const stateEl = document.getElementById('state');
    if (stateEl) {
        stateEl.addEventListener('change', function() {
            const distSel = document.getElementById('city');
            if (!distSel) return;
            distSel.innerHTML = '<option value="">Select District</option>';
            const list = stateDistricts[this.value];
            if (list) {
                list.forEach(d => {
                    const o = document.createElement('option');
                    o.value = d;
                    o.textContent = d;
                    distSel.appendChild(o);
                });
            } else {
                distSel.innerHTML = '<option value="">No districts found</option>';
            }
        });
    }


    // document.getElementById('state').addEventListener('change', function() {

    //     const state = this.value;
    //     const districtSelect = document.getElementById('city');

    //     districtSelect.innerHTML = '<option value="">Select District</option>';

    //     if (stateDistricts[state]) {

    //         stateDistricts[state].forEach(function(district) {
    //             const option = document.createElement('option');
    //             option.value = district;
    //             option.textContent = district;
    //             districtSelect.appendChild(option);
    //         });

    //     } else {

    //         districtSelect.innerHTML = '<option value="">No District Found</option>';
    //     }

    // });
</script>



<style>
    body {
        font-size: 15px;
        color: #333;
    }

    /* ===== ERP PAGE BACKGROUND ===== */
    .content-wrapper {
        background: #f4f6f9;
        padding: 15px 20px;
    }

    /* ===== HEADER / BREADCRUMB AREA ===== */
    .custom-header {
        margin-bottom: 18px;
        border-bottom: 1px solid #e6e6e6;
        padding-bottom: 10px;
    }

    .custom-header .page-title {
        font-size: 27px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .content-header>.breadcrumb {
        float: right;
        background: transparent;
        margin-top: 0;
        margin-bottom: 0;
        font-size: 15px;
        padding: 7px 5px;
        position: absolute;
        top: 15px;
        right: 10px;
        border-radius: 2px;
    }

    .custom-header .page-title small {
        font-size: 20px;
        color: #888;
        margin-left: 6px;
        font-weight: 400;
    }

    .custom-breadcrumb {
        float: none !important;
        position: static !important;
        padding: 0;
        margin-top: 6px;
        background: transparent;
        font-size: 13px;
    }

    .custom-breadcrumb>li>a {
        color: #3c8dbc;
        font-weight: 500;
    }

    .custom-breadcrumb>.active {
        color: #777;
    }


    /* ===== FORM CONTROLS ===== */
    .form-control {
        height: 42px;
        border-radius: 6px;
        border: 1px solid #dcdcdc;
        transition: all 0.2s ease;
        box-shadow: none;
        font-size: 15px;
        /* Increased */
    }

    .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: 0 0 0 2px rgba(60, 141, 188, 0.15);
    }



    /* ===== CARD ===== */
    .erp-card {
        border-radius: 10px;
        border: 1px solid #e6e6e6;
        background: #fff;
        padding: 22px;
        font-size: 15px;
        /* Increased */
    }

    /* ===== FIELDSET CARD STYLE (Separated ERP Sections) ===== */
    .styled-fieldset {
        background: #ffffff;
        border-radius: 10px;
        padding: 26px;
        margin-bottom: 30px;
        border: 1px solid #e4e7ed;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        position: relative;
        font-size: 15px;
        /* Increased */
    }

    /* Section Header Line */
    .styled-fieldset legend {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        padding: 0 10px;
        width: auto;
        border: none;
        margin-bottom: 20px;
        position: relative;
    }

    /* Decorative Left Accent Line */
    .styled-fieldset legend::before {
        content: "";
        position: absolute;
        left: -15px;
        top: 2px;
        height: 16px;
        width: 4px;
        background: #3c8dbc;
        border-radius: 2px;
    }

    .styled-fieldset legend i {
        color: #3c8dbc;
        margin-right: 6px;
    }

    /* Subtle Hover Elevation */
    .styled-fieldset:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
    }


    /* ===== LABELS ===== */
    label {
        font-weight: 500;
        margin-bottom: 6px;
        color: #444;
        font-size: 14px;
        /* Better hierarchy */
    }

    /* ===== SUBJECT BOX ===== */
    #subject_checkbox_group {
        min-height: 42px;
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 15px;
    }

    .form-check-group {
        max-height: 170px;
        overflow-y: auto;
        padding: 12px;
        border: 1px solid #eee;
        border-radius: 6px;
        background: #fafafa;
        font-size: 14px;
    }

    /* ===== BUTTONS ===== */
    .btn-primary,
    .btn-outline-secondary {
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
    }

    /* ===== IMAGE PREVIEW ===== */
    #passportPhotoPreview {
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    /* ===== PREVIEW MODAL DESIGN ===== */
    .admission-preview {
        border-radius: 6px;
    }

    .preview-header {
        background: #2c3e50;
        color: white;
        padding: 18px;
    }

    .school-title {
        margin: 0;
        font-weight: 600;
    }

    .school-session {
        margin: 5px 0 0;
        font-size: 13px;
        opacity: 0.85;
    }

    .preview-body {
        background: #f9fafc;
        padding: 20px;
    }

    .preview-card {
        background: white;
        padding: 16px;
        margin-bottom: 18px;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        margin-bottom: 14px;
        font-weight: 600;
        border-left: 4px solid #3c8dbc;
        padding-left: 8px;
        font-size: 14px;
    }

    .preview-table th {
        background: #f4f6f9;
        font-weight: 600;
        width: 22%;
        font-size: 13px;
    }

    .preview-table td {
        font-size: 13px;
    }

    .preview-photo {
        width: 120px;
        height: 150px;
        object-fit: cover;
        border: 1px solid #ddd;
        padding: 3px;
        background: white;
    }

    .address-block {
        background: #f4f6f9;
        padding: 10px;
        border-radius: 4px;
        font-size: 13px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .form-group {
            margin-bottom: 16px;
        }

        .custom-header .page-title {
            font-size: 18px;
        }
    }
</style>



<!-- <style>
/* ============================================================
   STUDENT ADMISSION — FIXED CSS
   Targets: label contrast, input visibility, section headers,
   card definition, breadcrumb, page title
============================================================ */

/* ── FONT ── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* ── PAGE SHELL ── */
.content-wrapper {
    background: #eef1f6 !important;
    padding: 20px 24px !important;
    font-family: 'Inter', 'Segoe UI', sans-serif !important;
}

/* ── PAGE TITLE ── */
.custom-header {
    margin-bottom: 20px;
    border-bottom: none !important;
    padding-bottom: 12px;
}

.custom-header .page-title,
.content-header h1.page-title {
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #1a2332 !important;        /* near-black — was #2c3e50 but too grey */
    letter-spacing: -0.01em;
}

.custom-header .page-title i,
.content-header h1.page-title i {
    color: #2563eb !important;        /* vivid blue icon */
}

/* ── BREADCRUMB ── */
.custom-breadcrumb,
.breadcrumb {
    background: transparent !important;
    padding: 0 !important;
    margin-top: 5px !important;
    font-size: 12.5px !important;
    float: none !important;
    position: static !important;
}

.custom-breadcrumb > li > a,
.breadcrumb > li > a {
    color: #2563eb !important;
    font-weight: 500;
    text-decoration: none;
}

.custom-breadcrumb > li > a:hover,
.breadcrumb > li > a:hover {
    text-decoration: underline;
}

.custom-breadcrumb > li.active,
.breadcrumb > li.active {
    color: #64748b !important;
    font-weight: 400;
}

.custom-breadcrumb > li + li::before,
.breadcrumb > li + li::before {
    content: "›" !important;
    color: #94a3b8 !important;
    padding: 0 5px !important;
}

/* ── OUTER BOX ── */
.box.box-primary.erp-card {
    border: none !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06) !important;
    background: transparent !important;
    padding: 0 !important;
}

.box.box-primary.erp-card > .box-body {
    padding: 0 !important;
    background: transparent !important;
}

/* ── SECTION CARDS (styled-fieldset) ── */
.styled-fieldset {
    background: #ffffff !important;
    border-radius: 10px !important;
    padding: 0 0 20px 0 !important;
    margin-bottom: 16px !important;
    border: 1px solid #d1d9e6 !important;      /* stronger border than before */
    box-shadow: 0 1px 4px rgba(0,0,0,0.05) !important;
    overflow: hidden;
}

.styled-fieldset:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08) !important;
    border-color: #b8c6dd !important;
}

/* ── LEGEND / SECTION HEADER ── */
.styled-fieldset legend {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    width: 100% !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    color: #1e293b !important;           /* strong dark — was #2c3e50 */
    letter-spacing: 0.01em;
    text-transform: uppercase;
    padding: 12px 20px !important;
    margin: 0 0 20px 0 !important;
    background: #f1f5f9 !important;      /* light blue-grey header band */
    border-bottom: 1px solid #dde3ed !important;
    border-radius: 10px 10px 0 0 !important;
    position: relative !important;
}

/* Remove old ::before pseudo blue bar — replaced by left border below */
.styled-fieldset legend::before {
    display: none !important;
}

/* Blue left accent on the header band */
.styled-fieldset legend::after {
    content: "" !important;
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    bottom: 0 !important;
    width: 4px !important;
    background: #2563eb !important;
    border-radius: 10px 0 0 0 !important;
}

.styled-fieldset legend i {
    color: #2563eb !important;
    font-size: 14px !important;
    margin-right: 0 !important;          /* gap handles spacing */
}

/* Fieldset inner padding for form content */
.styled-fieldset .form-row,
.styled-fieldset > .form-row,
.styled-fieldset > div {
    padding: 0 20px !important;
}

/* ── LABELS ── */
.styled-fieldset label,
.form-group label {
    font-size: 12.5px !important;
    font-weight: 600 !important;
    color: #374151 !important;           /* was #444 — now much crisper */
    margin-bottom: 5px !important;
    letter-spacing: 0.01em;
    display: block;
}

/* ── FORM CONTROLS ── */
.styled-fieldset .form-control,
.form-control {
    height: 38px !important;
    font-size: 13.5px !important;
    color: #1e293b !important;           /* dark input text */
    background-color: #ffffff !important;
    border: 1.5px solid #c4cdd8 !important;   /* was #dcdcdc — much more visible */
    border-radius: 7px !important;
    padding: 0 12px !important;
    box-shadow: none !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    font-family: 'Inter', 'Segoe UI', sans-serif !important;
}

.styled-fieldset .form-control:focus,
.form-control:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    outline: none !important;
    background: #fff !important;
}

/* Placeholder text — stronger than default */
.form-control::placeholder {
    color: #94a3b8 !important;
    font-style: normal !important;
    font-size: 13px !important;
}

/* Readonly / disabled fields */
.form-control[readonly],
.form-control[disabled] {
    background-color: #f1f5f9 !important;
    color: #64748b !important;
    border-color: #dde3ed !important;
    cursor: not-allowed !important;
    font-family: 'Inter', monospace !important;
    font-weight: 500;
    letter-spacing: 0.02em;
}

/* Select arrow polish */
select.form-control {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2364748b' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    padding-right: 32px !important;
}

/* ── SUBJECT CHECKBOX BOX ── */
#subject_checkbox_group {
    min-height: 40px !important;
    background: #f8fafc !important;
    border: 1.5px dashed #c4cdd8 !important;
    border-radius: 7px !important;
    padding: 10px 12px !important;
    font-size: 13px !important;
    color: #1e293b !important;
}

#subject_checkbox_group p.text-muted {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    margin: 0 !important;
}

#subject_checkbox_group .form-check {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 3px 0;
}

#subject_checkbox_group .form-check input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: #2563eb;
    cursor: pointer;
    flex-shrink: 0;
}

#subject_checkbox_group .form-check label {
    margin: 0 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    color: #374151 !important;
    cursor: pointer;
}

/* ── FEE CHECKBOX GROUP ── */
.form-check-group {
    max-height: 170px !important;
    overflow-y: auto !important;
    padding: 10px 12px !important;
    border: 1.5px solid #c4cdd8 !important;
    border-radius: 7px !important;
    background: #f8fafc !important;
    font-size: 13px !important;
}

.form-check-group .form-check {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 3px 0;
}

.form-check-group .form-check input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: #2563eb;
    cursor: pointer;
}

.form-check-group .form-check label {
    margin: 0 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    color: #374151 !important;
    cursor: pointer;
}

/* Select all bar */
.form-check:has(#select_all_exempted_fees) label,
label[for="select_all_exempted_fees"] {
    font-size: 12.5px !important;
    font-weight: 600 !important;
    color: #1e293b !important;
}

/* ── PHOTO PREVIEW ── */
#passportPhotoPreview {
    border-radius: 8px !important;
    border: 2px solid #d1d9e6 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    display: block;
}

/* ── FILE INPUTS ── */
input[type="file"].form-control {
    height: auto !important;
    padding: 6px 12px !important;
    cursor: pointer !important;
    font-size: 13px !important;
    color: #374151 !important;
}

/* ── FORM GROUP SPACING ── */
.form-group {
    margin-bottom: 16px !important;
}

/* ── BUTTONS ── */
.btn-primary {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    padding: 8px 22px !important;
    border-radius: 7px !important;
    box-shadow: 0 2px 6px rgba(37,99,235,0.25) !important;
    transition: background 0.15s, box-shadow 0.15s !important;
}

.btn-primary:hover,
.btn-primary:focus {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    box-shadow: 0 4px 14px rgba(37,99,235,0.35) !important;
}

.btn-outline-secondary,
.btn-default {
    background: #fff !important;
    border: 1.5px solid #c4cdd8 !important;
    color: #374151 !important;
    font-weight: 500 !important;
    font-size: 13.5px !important;
    padding: 8px 20px !important;
    border-radius: 7px !important;
}

.btn-outline-secondary:hover,
.btn-default:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}

/* ── PREVIEW MODAL ── */
.admission-preview {
    border-radius: 10px !important;
    overflow: hidden;
}

.preview-header {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
    padding: 20px 24px !important;
}

.preview-header .school-session {
    color: rgba(255,255,255,0.82) !important;
    font-size: 13px !important;
    margin: 0 !important;
}

.preview-body {
    background: #f1f5f9 !important;
    padding: 20px !important;
}

.preview-card {
    background: #fff !important;
    border: 1px solid #dde3ed !important;
    border-radius: 8px !important;
    padding: 16px !important;
    margin-bottom: 14px !important;
    box-shadow: none !important;
}

.section-title {
    font-size: 12.5px !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    border-left: 3px solid #2563eb !important;
    padding-left: 8px !important;
    margin-bottom: 12px !important;
}

.preview-table th {
    background: #f1f5f9 !important;
    font-weight: 600 !important;
    color: #374151 !important;
    font-size: 12.5px !important;
    vertical-align: middle !important;
}

.preview-table td {
    font-size: 13px !important;
    color: #1e293b !important;
    vertical-align: middle !important;
}

.address-block {
    background: #f1f5f9 !important;
    border-radius: 6px !important;
    padding: 10px 12px !important;
    font-size: 13px !important;
    color: #374151 !important;
    border: 1px solid #dde3ed !important;
}

/* ── VALIDATION ERROR ── */
.error-message {
    font-size: 11.5px !important;
    color: #dc2626 !important;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 3px !important;
}

.has-error .form-control {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1) !important;
}

.has-error label {
    color: #dc2626 !important;
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #c4cdd8; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .content-wrapper { padding: 12px !important; }
    .styled-fieldset { margin-bottom: 12px !important; }
    .custom-header .page-title { font-size: 18px !important; }
    .form-group { margin-bottom: 12px !important; }
}
</style> -->

