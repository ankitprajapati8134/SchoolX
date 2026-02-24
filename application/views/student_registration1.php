<div class="content-wrapper">
    <div class="title-bar">
        <i class="fa fa-school" style="font-size: 24px; margin-right: 10px;"></i>
        <span><?php echo isset($schoolName) ? htmlspecialchars($schoolName) : 'School Name'; ?></span>
    </div>
    <div class="container">

        <div>
            <div class="section-header"> 📝 Student Admission Form </div>
            <form action="<?php echo base_url() . 'student/studentAdmission' ?>" method="post" id="add_student_form"
                enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <!-- <label>Passport Size Photo</label> -->
                        <div class="passport-photo-wrapper">
                            <label>Passport Size Photo</label>
                            <input type="file" name="student_photo" id="student_photo" class="form-control"
                                accept="image/*" onchange="previewPassportPhoto(event)" required />
                            <div class="photo-preview-wrapper mt-3">
                                <img id="passportPhotoPreview" src="" alt="Passport Photo Preview"
                                    style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #ccc;" />
                            </div>

                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="sname">Full Name</label>
                            <input type="text" name="Name" id="sname" class="form-control" value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="user_id">Student ID</label>
                            <input type="text" name="user_id" id="user_id" value="<?= $studentIdCount ?>"
                                class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="class">Class<span style="color: red;">*</span></label>
                            <select id="class" name="class" class="form-select" required>
                                <option value="" disabled selected>Select Class</option>
                                <?php
                                $textClasses = [];
                                $numericClasses = [];

                                foreach ($Classes as $class) {
                                    $className = $class['class_name'];

                                    if (preg_match('/\d+/', $className, $matches)) {
                                        $class['numeric_value'] = intval($matches[0]);
                                        $numericClasses[] = $class;
                                    } else {
                                        $textClasses[] = $class;
                                    }
                                }

                                usort($textClasses, function ($a, $b) {
                                    return strcasecmp($a['class_name'], $b['class_name']);
                                });

                                usort($numericClasses, function ($a, $b) {
                                    return $a['numeric_value'] <=> $b['numeric_value'];
                                });

                                $sortedClasses = array_merge($textClasses, $numericClasses);

                                foreach ($sortedClasses as $class) :
                                    $classSection = $class['class_name'] . " '" . $class['section'] . "'";
                                ?>
                                    <option value="<?= htmlspecialchars($classSection) ?>">
                                        <?= htmlspecialchars($classSection) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>




                        <div class="form-group col-md-6">
                            <label>Fees to be exempted for the student</label>
                            <!-- Select All Checkbox -->
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
                    </div>




                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="subject_checkbox_group">Optional Subjects <span
                                    style="color: red;">*</span></label>

                            <div id="subject_checkbox_group" class="border p-2 rounded"
                                style="min-height: 50px; background-color: #f9f9f9;">
                                <p style="font-style: italic; color: #777;">Select a class to view subjects.</p>
                            </div>
                        </div>

                    </div>




                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_user">Email</label>
                            <input type="email" name="email" id="email_user" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="General">General</option>
                                <option value="OBC">OBC</option>
                                <option value="SC">SC</option>
                                <option value="ST">ST</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admission_date">Admission Date</label>
                            <input type="date" name="admission_date" id="admission_date" class="form-control"
                                value="" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="guard_contact">Guardian's Contact</label>
                            <input type="text" name="guard_contact" id="guard_contact" class="form-control" value=""
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="guard_name">Guardian's Name</label>
                            <input type="text" name="guard_name" id="guard_name" class="form-control" value=""
                                required>
                        </div>
                    </div>


                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label for="guard_relation">Guardian Relation</label>
                            <input type="text" name="guard_relation" id="guard_relation" class="form-control"
                                value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pre_class">Previous Class</label>
                            <input type="text" name="pre_class" id="pre_class" class="form-control" value=""
                                required>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pre_school">Previous School</label>
                            <input type="text" name="pre_school" id="pre_school" class="form-control" value=""
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pre_marks">Previous Marks</label>
                            <input type="text" name="pre_marks" id="pre_marks" class="form-control" value=""
                                required>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="street">Street</label>
                            <input type="text" name="street" id="street" class="form-control" value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control" value="" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="state">State</label>
                            <input type="text" name="state" id="state" class="form-control" value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" value=""
                                required>
                        </div>

                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="religion">Religion</label>
                            <select name="religion" id="religion" class="form-control"
                                onchange="toggleOtherReligion(this)" required>
                                <option value="">Select Religion</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Muslim">Muslim</option>
                                <option value="Sikh">Sikh</option>
                                <option value="Jain">Jain</option>
                                <option value="Buddh">Buddh</option>
                                <option value="Christian">Christian</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="other_religion" id="other_religion" class="form-control mt-2"
                                placeholder="Please specify" style="display: none;">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nationality">Nationality</label>
                            <input type="text" name="nationality" value="" id="nationality" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="bloodGroup">Blood Group</label>
                            <select name="blood_group" id="blood_group" class="form-control" required>
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
                        <div class="form-group col-md-6">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control" value="" required>
                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fname">Father's Name</label>
                            <input type="text" name="father_name" id="father_name" class="form-control" value=""
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fatherOccupation">Father's Occupation</label>
                            <input type="text" name="father_occupation" id="father_occupation" class="form-control"
                                value="" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="mname">Mother's Name</label>
                            <input type="text" name="mother_name" id="mother_name" class="form-control" value=""
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="motherOccupation">Mother's Occupation</label>
                            <input type="text" name="mother_occupation" id="mother_occupation" class="form-control"
                                value="" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="birthCertificate">Birth Certificate</label>
                            <input type="file" name="birthCertificate" id="birthCertificate" class="form-control"
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="aadharCard">Aadhar Card</label>
                            <input type="file" name="aadharCard" id="aadharCard" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="schoolLeavingCertificate">Previous School Leaving Certificate</label>
                            <input type="file" name="schoolLeavingCertificate" id="schoolLeavingCertificate"
                                class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="button" id="submitStudentForm" onclick="previewFormBeforeSubmit(event)"
                            class="btn btn-success">Submit</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>


<div id="previewModal" class="form-wrapper"
    style="display: none; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; max-width: 800px; margin: auto;">
    <h3 class="text-center" style="margin-bottom: 20px;">Preview Admission Form Details</h3>
    <div class="row" style="max-height: 500px; overflow-y: auto;">
        <!-- Left Section: Passport Photo -->
        <div class="col-md-4 text-center">
            <div class="photo-preview-wrapper">
                <img id="previewPhoto" src="" alt="Photo Preview"
                    style="width: 200px; height: 200px; object-fit: cover; border: 2px solid #ccc; border-radius: 5px;" />
            </div>
        </div>

        <!-- Right Section: Form Details -->
        <div class="col-md-8">
            <table class="table table-bordered" style="background-color: #fff;">
                <tbody>
                    <!-- Full Name -->
                    <tr>
                        <th style="width: 40%; background-color: #f2f2f2;"><strong>Student Name</strong></th>
                        <td id="previewName"></td>
                    </tr>

                    <!-- Student ID -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Student ID</strong></th>
                        <td id="previewId"></td>
                    </tr>

                    <!-- Class & Section -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Class</strong></th>
                        <td id="previewClass"></td>
                    </tr>

                    <!-- Optional Subject -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Subjects</strong></th>
                        <td id="previewSubject"></td>
                    </tr>

                    <!-- Section -->
                    <!-- <tr>
                        <th style="background-color: #f2f2f2;"><strong>Section</strong></th>
                        <td id="previewSection"></td>
                    </tr> -->

                    <!-- Phone Number -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Phone Number</strong></th>
                        <td id="previewPhone"></td>
                    </tr>

                    <!-- Email -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Email</strong></th>
                        <td id="previewEmail"></td>
                    </tr>

                    <!-- Category -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Category</strong></th>
                        <td id="previewCategory"></td>
                    </tr>

                    <!-- Admission Date -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Admission Date</strong></th>
                        <td id="previewAdmissionDate"></td>
                    </tr>

                    <!-- Guardian's Contact -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian's Contact</strong></th>
                        <td id="previewGuardContact"></td>
                    </tr>

                    <!-- Guardian's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian's Name</strong></th>
                        <td id="previewGuardName"></td>
                    </tr>

                    <!-- Guardian Relation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian Relation</strong></th>
                        <td id="previewGuardRelation"></td>
                    </tr>

                    <!-- Previous Class -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous Class</strong></th>
                        <td id="previewPreClass"></td>
                    </tr>

                    <!-- Previous School -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous School</strong></th>
                        <td id="previewPreSchool"></td>
                    </tr>

                    <!-- Previous Marks -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous Marks</strong></th>
                        <td id="previewPreMarks"></td>
                    </tr>

                    <!-- Street -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Street</strong></th>
                        <td id="previewStreet"></td>
                    </tr>

                    <!-- City -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>City</strong></th>
                        <td id="previewCity"></td>
                    </tr>

                    <!-- State -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>State</strong></th>
                        <td id="previewState"></td>
                    </tr>

                    <!-- Postal Code -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Postal Code</strong></th>
                        <td id="previewPostalCode"></td>
                    </tr>

                    <!-- Religion -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Religion</strong></th>
                        <td id="previewReligion"></td>
                    </tr>

                    <!-- Nationality -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Nationality</strong></th>
                        <td id="previewNationality"></td>
                    </tr>

                    <!-- Blood Group -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Blood Group</strong></th>
                        <td id="previewBloodGroup"></td>
                    </tr>

                    <!-- Gender -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Gender</strong></th>
                        <td id="previewGender"></td>
                    </tr>

                    <!-- Date of Birth -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Date of Birth</strong></th>
                        <td id="previewDob"></td>
                    </tr>

                    <!-- Father's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Father's Name</strong></th>
                        <td id="previewFatherName"></td>
                    </tr>

                    <!-- Father's Occupation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Father's Occupation</strong></th>
                        <td id="previewFatherOccupation"></td>
                    </tr>

                    <!-- Mother's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Mother's Name</strong></th>
                        <td id="previewMotherName"></td>
                    </tr>

                    <!-- Mother's Occupation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Mother's Occupation</strong></th>
                        <td id="previewMotherOccupation"></td>
                    </tr>

                    <tr>
                        <th><strong>Birth Certificate</strong></th>
                        <td id="previewBirthCertificate"></td>
                    </tr>

                    <tr>
                        <th><strong>Aadhar Card</strong></th>
                        <td id="previewAadharCard"></td>
                    </tr>

                    <tr>
                        <th><strong>School Leaving Certificate</strong></th>
                        <td id="previewSchoolLeavingCertificate"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buttons -->
    <div class="form-group text-center" style="margin-top: 20px;">
        <button onclick="submitFinalForm()" class="btn btn-success">Confirm & Submit</button>
        <button onclick="closePreview()" class="btn btn-default">Close</button>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function previewPassportPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("passportPhotoPreview").src = e.target.result;
                // console.log("Photo Preview Source:", document.getElementById("passportPhotoPreview").src);

            };
            reader.readAsDataURL(file);
        }
    }


    // Preview Form Before Submission
    function previewFormBeforeSubmit(event) {
        event.preventDefault(); // Prevent form submission
        showPreview(); // Show preview modal

    }

    // Function to show the preview modal
    function showPreview() {
        const name = document.getElementById("sname").value.trim();
        const studentId = document.getElementById("user_id").value.trim();
        const photoSrc = document.getElementById("passportPhotoPreview").src.trim();
        // const selectedClass = document.getElementById("selectClass").value.trim();
        // const selectedSection = document.getElementById("selectSection").value.trim();
        const selectedClassSection = document.getElementById("class").value.trim(); // Example: "Class 10th 'A'"
        // const selectedSubject = document.getElementById("subject").value.trim();
        // const selectedSubjects = Array.from(document.getElementById("subject").selectedOptions)
        //     .map(option => option.value.trim());

        const subjectElement = document.getElementById("subject_checkbox_group");

        if (subjectElement) {
        const selectedSubjects = Array.from(subjectElement.querySelectorAll('input[type="checkbox"]:checked'))
            .map(checkbox => checkbox.value.trim());

            document.getElementById("previewSubject").innerText = selectedSubjects.join(", ");
        } else {
            console.warn("Subject select element not found!");
        }


        const phone = document.getElementById("phone_number").value.trim();
        const email = document.getElementById("email_user").value.trim();
        const category = document.getElementById("category").value.trim();
        const admissionDate = document.getElementById("admission_date").value.trim();
        const guardianContact = document.getElementById("guard_contact").value.trim();
        const guardianName = document.getElementById("guard_name").value.trim();
        const guardianRelation = document.getElementById("guard_relation").value.trim();
        const previousClass = document.getElementById("pre_class").value.trim();
        const previousSchool = document.getElementById("pre_school").value.trim();
        const previousMarks = document.getElementById("pre_marks").value.trim();
        const street = document.getElementById("street").value.trim();
        const city = document.getElementById("city").value.trim();
        const state = document.getElementById("state").value.trim();
        const postalCode = document.getElementById("postal_code").value.trim();
        const religion = document.getElementById("religion").value.trim();
        const nationality = document.getElementById("nationality").value.trim();
        const bloodGroup = document.getElementById("blood_group").value.trim();
        const gender = document.getElementById("gender").value.trim();

        // const gender = document.querySelector('input[name="gender"]:checked') ? document.querySelector('input[name="gender"]:checked').value : '';
        const dob = document.getElementById("dob").value.trim();
        const fatherName = document.getElementById("father_name").value.trim();
        const fatherOccupation = document.getElementById("father_occupation").value.trim();
        const motherName = document.getElementById("mother_name").value.trim();
        const motherOccupation = document.getElementById("mother_occupation").value.trim();

        if (!name || !studentId || !photoSrc || !selectedClassSection) {
            alert("Please fill out all fields and select a photo.");
            return;
        }

        document.getElementById("previewName").innerText = name;
        document.getElementById("previewId").innerText = studentId;
        document.getElementById("previewClass").innerText = selectedClassSection;
        //  document.getElementById("previewSubject").innerText = selectedSubject;


        // document.getElementById("previewClass").innerText = selectedClass;
        // document.getElementById("previewSection").innerText = selectedSection;
        document.getElementById("previewPhoto").src = photoSrc;

        document.getElementById("previewPhone").innerText = phone;
        document.getElementById("previewEmail").innerText = email;
        document.getElementById("previewCategory").innerText = category;
        document.getElementById("previewAdmissionDate").innerText = admissionDate;
        document.getElementById("previewGuardContact").innerText = guardianContact;
        document.getElementById("previewGuardName").innerText = guardianName;
        document.getElementById("previewGuardRelation").innerText = guardianRelation;
        document.getElementById("previewPreClass").innerText = previousClass;
        document.getElementById("previewPreSchool").innerText = previousSchool;
        document.getElementById("previewPreMarks").innerText = previousMarks;
        document.getElementById("previewStreet").innerText = street;
        document.getElementById("previewCity").innerText = city;
        document.getElementById("previewState").innerText = state;
        document.getElementById("previewPostalCode").innerText = postalCode;
        document.getElementById("previewReligion").innerText = religion;
        document.getElementById("previewNationality").innerText = nationality;
        document.getElementById("previewBloodGroup").innerText = bloodGroup;
        document.getElementById("previewGender").innerText = gender;
        document.getElementById("previewDob").innerText = dob;
        document.getElementById("previewFatherName").innerText = fatherName;
        document.getElementById("previewFatherOccupation").innerText = fatherOccupation;
        document.getElementById("previewMotherName").innerText = motherName;
        document.getElementById("previewMotherOccupation").innerText = motherOccupation;

        // Show modal
        document.getElementById("previewModal").style.display = "block";
    }


    function submitFinalForm() {
        // Prevent form submission to handle it manually
        event.preventDefault();

        // Get form data using FormData
        var formData = new FormData(document.getElementById('add_student_form'));

        // Get data from the preview modal
        const name = document.getElementById("previewName").innerText.trim();
        const studentId = document.getElementById("previewId").innerText.trim();

        const selectedClassSection = document.getElementById("previewClass").innerText.trim();
        // const selectedSubject = document.getElementById("previewSubject").innerText.trim();

        const selectedSubjects = document.getElementById("previewSubject").innerText.trim();
        if (selectedSubjects) {
            selectedSubjects.split(",").forEach(sub => {
                formData.append('subject[]', sub.trim());
            });
        }


        // const selectedClass = document.getElementById("previewClass").innerText.trim();
        // const selectedSection = document.getElementById("previewSection").innerText.trim();



        const phone = document.getElementById("previewPhone").innerText.trim(); // Phone number from preview
        const email = document.getElementById("previewEmail").innerText.trim(); // Email from preview
        const category = document.getElementById("previewCategory").innerText.trim(); // Category from preview
        const admissionDate = document.getElementById("previewAdmissionDate").innerText
            .trim(); // Admission Date from preview
        const guardianContact = document.getElementById("previewGuardContact").innerText.trim(); // Guardian's Contact
        const guardianName = document.getElementById("previewGuardName").innerText.trim(); // Guardian's Name
        const guardianRelation = document.getElementById("previewGuardRelation").innerText.trim(); // Guardian Relation
        const previousClass = document.getElementById("previewPreClass").innerText.trim(); // Previous Class
        const previousSchool = document.getElementById("previewPreSchool").innerText.trim(); // Previous School
        const previousMarks = document.getElementById("previewPreMarks").innerText.trim(); // Previous Marks
        const street = document.getElementById("previewStreet").innerText.trim(); // Street from preview
        const city = document.getElementById("previewCity").innerText.trim(); // City from preview
        const state = document.getElementById("previewState").innerText.trim(); // State from preview
        const postalCode = document.getElementById("previewPostalCode").innerText.trim(); // Postal Code from preview
        const religion = document.getElementById("previewReligion").innerText.trim(); // Religion from preview
        const nationality = document.getElementById("previewNationality").innerText.trim(); // Nationality from preview
        const bloodGroup = document.getElementById("previewBloodGroup").innerText.trim(); // Blood Group from preview
        const gender = document.getElementById("previewGender").innerText.trim(); // Gender from preview
        const dob = document.getElementById("previewDob").innerText.trim(); // Date of Birth from preview
        const fatherName = document.getElementById("previewFatherName").innerText.trim(); // Father's Name from preview
        const fatherOccupation = document.getElementById("previewFatherOccupation").innerText.trim(); // Father's Occupation
        const motherName = document.getElementById("previewMotherName").innerText.trim(); // Mother's Name from preview
        const motherOccupation = document.getElementById("previewMotherOccupation").innerText.trim(); // Mother's Occupation

        // Get the student photo URL (it may be a base64 or URL)
        const photoSrc = document.getElementById("passportPhotoPreview") ? document.getElementById("passportPhotoPreview")
            .src.trim() : '';

        // Check if all fields have values from the preview modal
        if (!name || !studentId || !photoSrc || !selectedClassSection) {
            alert("Please fill out all fields.");
            return; // Prevent form submission if any required field is missing
        }


        // Remove "Class" prefix from selectedClass and format it
        // var processedClassValue = selectedClassSection.replace(/^(Class\s*)+/i, ''); // removes one or more leading "Class "
        // var combinedClassSection = `Class ${processedClassValue.trim()}`; // properly formatted

        // Add the preview modal values to the FormData object
        formData.append('Name', name);
        formData.append('user_id', studentId);
        formData.append('student_photo', document.getElementById('student_photo').files[0]); // Add the actual file
        formData.append('class_section', selectedClassSection); // Add the class and section
        // formData.append('subject', selectedSubject);
        // Convert to array if needed
       


        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('category', category);
        formData.append('admission_date', admissionDate);
        formData.append('guardian_contact', guardianContact);
        formData.append('guardian_name', guardianName);
        formData.append('guardian_relation', guardianRelation);
        formData.append('previous_class', previousClass);
        formData.append('previous_school', previousSchool);
        formData.append('previous_marks', previousMarks);
        formData.append('street', street);
        formData.append('city', city);
        formData.append('state', state);
        formData.append('postal_code', postalCode);
        formData.append('religion', religion);
        formData.append('nationality', nationality);
        formData.append('blood_group', bloodGroup);
        formData.append('gender', gender);
        formData.append('dob', dob);
        formData.append('father_name', fatherName);
        formData.append('father_occupation', fatherOccupation);
        formData.append('mother_name', motherName);
        formData.append('mother_occupation', motherOccupation);
        // Add the server-generated photo URL to FormData
        // formData.append('photo_url', photoSrc); // Append the photo URL here

        formData.append('student_photo', document.getElementById('student_photo').files[0]);

        const url = $('#add_student_form').attr("action"); // Get the form's action URL

        // Send the form data via AJAX
        $.ajax({
            url: url, // Your controller method URL
            type: 'POST',
            data: formData,
            processData: false, // Important to keep data as FormData
            contentType: false, // Do not set content type for FormData
            success: function(response) {
                alert('Student Admission Successful!');
                location.reload(); // Optionally reload the page after success
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('There was an error submitting the form.');
            }
        });
    }



    // Function to close the preview modal
    function closePreview() {
        document.getElementById("previewModal").style.display = "none";
    }



    function toggleOtherReligion(selectElement) {
        const otherReligionInput = document.getElementById('other_religion');
        if (selectElement.value === 'Other') {
            otherReligionInput.style.display = 'block';
            otherReligionInput.required = true; // Make it a required field
        } else {
            otherReligionInput.style.display = 'none';
            otherReligionInput.value = ''; // Clear the input value
            otherReligionInput.required = false; // Remove the required attribute
        }
    }



    document.addEventListener("DOMContentLoaded", function() {
        // Select the "Select All" checkbox
        var selectAllCheckbox = document.getElementById("select_all_exempted_fees");
        // Select all fee checkboxes by name
        var feeCheckboxes = document.querySelectorAll('input[name="exempted_fees_multiple[]"]');

        // Add event listener for change on the "Select All" checkbox
        selectAllCheckbox.addEventListener("change", function() {
            feeCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Optionally, if you want to un-check "Select All" when any fee checkbox is manually deselected:
        feeCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener("change", function() {
                if (!checkbox.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // If all fee checkboxes are checked, set the "Select All" checkbox to checked.
                    var allChecked = Array.from(feeCheckboxes).every(function(cb) {
                        return cb.checked;
                    });
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
    });

   
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class');
        const subjectContainer = document.getElementById('subject_checkbox_group');

        classSelect.addEventListener('change', function() {
            const selectedClass = classSelect.value;

            if (selectedClass) {
                fetch('fetch_subjects', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            classSection: selectedClass
                        })
                    })
                    .then(response => response.json())
                    .then(subjects => {
                        // Clear previous content
                        subjectContainer.innerHTML = '';

                        if (subjects.length === 0) {
                            subjectContainer.innerHTML = '<p>No subjects available for this class.</p>';
                            return;
                        }

                        // Create checkboxes
                        subjects.forEach(subject => {
                            const checkboxId = `subject_${subject.replace(/\s+/g, '_')}`;

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'optional_subjects[]';
                            checkbox.value = subject;
                            checkbox.id = checkboxId;
                            checkbox.classList.add('form-check-input');

                            const label = document.createElement('label');
                            label.htmlFor = checkboxId;
                            label.classList.add('form-check-label', 'ms-1');
                            label.textContent = subject;

                            const wrapper = document.createElement('div');
                            wrapper.classList.add('form-check', 'mb-1');
                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);

                            subjectContainer.appendChild(wrapper);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching subjects:', error);
                        subjectContainer.innerHTML =
                            '<p style="color:red;">Failed to load subjects.</p>';
                    });
            } else {
                subjectContainer.innerHTML =
                    '<p style="font-style: italic; color: #777;">Select a class to view subjects.</p>';
            }
        });
    });



</script>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
    }

    /* Content Wrapper Styling */
    .content-wrapper {
        padding: 20px;
        background-color: #ecf0f5;
    }

    .container {
        width: 98%;
        padding-right: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding-bottom: 20px;
        font-size: 16px;
    }

    .title-bar {
        background-color: #007bff;
        color: white;
        margin-top: 5px;
        font-weight: bold;
        text-align: center;
        font-size: 24px;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .section-header {
        font-size: 18px;
        font-weight: bold;
        margin-top: 20px;
        color: #007bff;
    }

    .buttons {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 20px;
    }

    .footer {
        background-color: #343a40;
        color: white;
        padding: 10px;
        text-align: center;
        margin-top: 20px;
    }

    button {
        min-width: 120px;
        /* Ensures uniform button size */
    }

    .footer {
        background-color: #343a40;
        color: white;
        padding: 10px;
        text-align: center;
        margin-top: 20px;
    }

    .section-header {
        text-align: center;
        color: #ffffff;
        background-color: #4CAF50;
        font-size: 26px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    #previewModal {
        display: none;
        /* Hidden by default */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        padding: 20px;
        width: 90%;
        max-width: 500px;
    }
</style>



































<!-- Latest new code of the studentadmission code 


<div class="content-wrapper">
    <!-- <div class="title-bar">
        <i class="fa fa-school" style="font-size: 20px;"></i>
        <span><?php echo isset($school_name) ? htmlspecialchars($school_name) : 'School Name'; ?></span>
    </div> -->
    <div class="container">

        <div>
            <div class="section-header"> 📝 Student Admission Form </div>
            <form action="<?php echo base_url() . 'student/studentAdmission' ?>" method="post" id="add_student_form"
                enctype="multipart/form-data">

                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-user"></i>Student Basic Information</legend>
                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="user_id">Student ID</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                <input type="text" name="user_id" id="user_id" value="<?= $user_Id  ?>"
                                    class="form-control" readonly>
                            </div>


                        </div>


                        <div class="form-group col-md-6">
                            <label for="sname">Student Name</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" name="Name" id="sname" class="form-control"
                                    placeholder="Enter Student Name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">

                            <label for="dob">Date of Birth</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="date" name="dob" id="dob" class="form-control" value="" required>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="class">Class <span style="color: red;">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-graduation-cap"></i>
                                </span>
                                <select id="class" name="class" class="form-control" required>
                                    <option value="" disabled selected>Select Class</option>
                                    <?php
                                    $textClasses = [];
                                    $numericClasses = [];

                                    foreach ($Classes as $class) {
                                        $className = $class['class_name'];

                                        if (preg_match('/\d+/', $className, $matches)) {
                                            $class['numeric_value'] = intval($matches[0]);
                                            $numericClasses[] = $class;
                                        } else {
                                            $textClasses[] = $class;
                                        }
                                    }

                                    usort($textClasses, function ($a, $b) {
                                        return strcasecmp($a['class_name'], $b['class_name']);
                                    });

                                    usort($numericClasses, function ($a, $b) {
                                        return $a['numeric_value'] <=> $b['numeric_value'];
                                    });

                                    $sortedClasses = array_merge($textClasses, $numericClasses);

                                    foreach ($sortedClasses as $class) :
                                        $classSection = $class['class_name'] . " '" . $class['section'] . "'";
                                    ?>
                                        <option value="<?= htmlspecialchars($classSection) ?>">
                                            <?= htmlspecialchars($classSection) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="subject_checkbox_group">Optional Subjects <span
                                    style="color: red;">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-book"></i></span>
                                <div id="subject_checkbox_group" class="border p-2 rounded"
                                    style="min-height: 50px; background-color: #f9f9f9;">
                                    <p style="font-style: italic; color: #777;">Select a class to view subjects.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </fieldset>



                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-phone"></i> Contact and Admission Details</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="phone">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" name="phone_number" id="phone_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_user">Email</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" name="email" id="email_user" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="category">Category</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                <select name="category" id="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="General">General</option>
                                    <option value="OBC">OBC</option>
                                    <option value="SC">SC</option>
                                    <option value="ST">ST</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admission_date">Admission Date</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="date" name="admission_date" id="admission_date" class="form-control"
                                    value="" required>
                            </div>

                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="guard_contact">Guardian's Contact</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" name="guard_contact" id="guard_contact" class="form-control" value=""
                                    required>
                            </div>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="guard_name">Guardian's Name</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" name="guard_name" id="guard_name" class="form-control" value=""
                                    required>
                            </div>
                        </div>
                    </div>
                </fieldset>


                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-university"></i> Previous Schools Details</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="guard_relation">Guardian Relation</label>
                            <input type="text" name="guard_relation" id="guard_relation" class="form-control" value=""
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pre_class">Previous Class</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-university"></i></span>
                                <input type="text" name="pre_class" id="pre_class" class="form-control" value=""
                                    required>
                            </div>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="pre_school">Previous School</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-university"></i></span>
                                <input type="text" name="pre_school" id="pre_school" class="form-control" value=""
                                    required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pre_marks">Previous Marks</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-bar-chart"></i></span>
                                <input type="text" name="pre_marks" id="pre_marks" class="form-control" value=""
                                    required>
                            </div>
                        </div>
                    </div>
                </fieldset>



                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-map-marker"></i> Address Details</legend>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="street">Street</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-road"></i></span>
                                <input type="text" name="street" id="street" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="city">City</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                <input type="text" name="city" id="city" class="form-control" value="" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="state">State</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                                <input type="text" name="state" id="state" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="postal_code">Postal Code</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="text" name="postal_code" id="postal_code" class="form-control" value="" required>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-info-circle"></i> Other Students Details</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="religion">Religion</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-om"></i></span>
                                <select name="religion" id="religion" class="form-control"
                                    onchange="toggleOtherReligion(this)" required>
                                    <option value="">Select Religion</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Muslim">Muslim</option>
                                    <option value="Sikh">Sikh</option>
                                    <option value="Jain">Jain</option>
                                    <option value="Buddh">Buddh</option>
                                    <option value="Christian">Christian</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <input type="text" name="other_religion" id="other_religion" class="form-control mt-2"
                                placeholder="Please specify" style="display: none;">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nationality">Nationality</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                                <input type="text" name="nationality" value="" id="nationality" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="bloodGroup">Blood Group</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-tint"></i></span>
                                <select name="blood_group" id="blood_group" class="form-control" required>
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
                        </div>
                        <div class="form-group col-md-6">
                            <label for="gender">Gender</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-venus-mars"></i></span>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>


                </fieldset>

                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-users"></i> Guardian Details</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fname">Father's Name</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" name="father_name" id="father_name" class="form-control" value=""
                                    required>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="fatherOccupation">Father's Occupation</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <input type="text" name="father_occupation" id="father_occupation" class="form-control"
                                    value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="mname">Mother's Name</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <input type="text" name="mother_name" id="mother_name" class="form-control" value=""
                                    required>
                            </div>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="motherOccupation">Mother's Occupation</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-briefcase"></i></span>
                                <input type="text" name="mother_occupation" id="mother_occupation" class="form-control"
                                    value="" required>
                            </div>
                        </div>
                    </div>
                </fieldset>


                <fieldset class="styled-fieldset">
                    <legend><i class="fa fa-file-text-o"></i> Documents Details</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="birthCertificate">Birth Certificate</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-paperclip"></i></span>
                                <input type="file" name="birthCertificate" id="birthCertificate" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="aadharCard">Aadhar Card</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-paperclip"></i></span>
                                <input type="file" name="aadharCard" id="aadharCard" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="schoolLeavingCertificate">Previous School Leaving Certificate</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-paperclip"></i></span>
                                <input type="file" name="schoolLeavingCertificate" id="schoolLeavingCertificate"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Fees to be exempted for the student</label>
                            <!-- Select All Checkbox -->
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
                    </div>

                </fieldset>


                <fieldset class="styled-fieldset">
                    <legend><i class="fas fa-file-image"></i> Passport Size Photo</legend>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="student_photo">
                                <i class="glyphicon glyphicon-user"></i> Passport Size Photo
                            </label>
                            <input type="file" class="form-control" name="student_photo" id="student_photo" accept="image/*"
                                onchange="previewPassportPhoto(event)" required>
                            <img id="passportPhotoPreview" class="img-thumbnail"
                                src="<?= base_url('tools/dist/img/kids.jpg') ?>" alt="Passport Photo Preview"
                                style="margin-top: 10px; width: 170px; height: 200px; object-fit: cover; border: 2px solid #ccc;">
                        </div>
                    </div>
                </fieldset>

                <div class="form-row">
                    <div class="form-group col-md-12 text-right">
                        <button type="reset" class="btn btn-default">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <button type="button" id="submitStudentForm" onclick="previewFormBeforeSubmit(event)"
                            class="btn btn-success">
                            <i class="fa fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="previewModal" class="form-wrapper"
    style="display: none; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; max-width: 800px; margin: auto;">
    <h3 class="text-center" style="margin-bottom: 20px;">Preview Admission Form Details</h3>
    <div class="row" style="max-height: 500px; overflow-y: auto;">
        <!-- Left Section: Passport Photo -->
        <div class="col-md-4 text-center">
            <div class="photo-preview-wrapper">
                <img id="previewPhoto" src="" alt="Photo Preview"
                    style="width: 200px; height: 200px; object-fit: cover; border: 2px solid #ccc; border-radius: 5px;" />
            </div>
        </div>

        <!-- Right Section: Form Details -->
        <div class="col-md-8">
            <table class="table table-bordered" style="background-color: #fff;">
                <tbody>
                    <!-- Full Name -->
                    <tr>
                        <th style="width: 40%; background-color: #f2f2f2;"><strong>Student Name</strong></th>
                        <td id="previewName"></td>
                    </tr>

                    <!-- Student ID -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Student ID</strong></th>
                        <td id="previewId"></td>
                    </tr>

                    <!-- Class & Section -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Class</strong></th>
                        <td id="previewClass"></td>
                    </tr>

                    <!-- Optional Subject -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong> Optional Subjects</strong></th>
                        <td id="previewSubject"></td>
                    </tr>

                    <!-- Phone Number -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Phone Number</strong></th>
                        <td id="previewPhone"></td>
                    </tr>

                    <!-- Email -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Email</strong></th>
                        <td id="previewEmail"></td>
                    </tr>

                    <!-- Category -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Category</strong></th>
                        <td id="previewCategory"></td>
                    </tr>

                    <!-- Admission Date -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Admission Date</strong></th>
                        <td id="previewAdmissionDate"></td>
                    </tr>

                    <!-- Guardian's Contact -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian's Contact</strong></th>
                        <td id="previewGuardContact"></td>
                    </tr>

                    <!-- Guardian's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian's Name</strong></th>
                        <td id="previewGuardName"></td>
                    </tr>

                    <!-- Guardian Relation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Guardian Relation</strong></th>
                        <td id="previewGuardRelation"></td>
                    </tr>

                    <!-- Previous Class -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous Class</strong></th>
                        <td id="previewPreClass"></td>
                    </tr>

                    <!-- Previous School -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous School</strong></th>
                        <td id="previewPreSchool"></td>
                    </tr>

                    <!-- Previous Marks -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Previous Marks</strong></th>
                        <td id="previewPreMarks"></td>
                    </tr>

                    <!-- Street -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Street</strong></th>
                        <td id="previewStreet"></td>
                    </tr>

                    <!-- City -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>City</strong></th>
                        <td id="previewCity"></td>
                    </tr>

                    <!-- State -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>State</strong></th>
                        <td id="previewState"></td>
                    </tr>

                    <!-- Postal Code -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Postal Code</strong></th>
                        <td id="previewPostalCode"></td>
                    </tr>

                    <!-- Religion -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Religion</strong></th>
                        <td id="previewReligion"></td>
                    </tr>

                    <!-- Nationality -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Nationality</strong></th>
                        <td id="previewNationality"></td>
                    </tr>

                    <!-- Blood Group -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Blood Group</strong></th>
                        <td id="previewBloodGroup"></td>
                    </tr>

                    <!-- Gender -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Gender</strong></th>
                        <td id="previewGender"></td>
                    </tr>

                    <!-- Date of Birth -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Date of Birth</strong></th>
                        <td id="previewDob"></td>
                    </tr>

                    <!-- Father's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Father's Name</strong></th>
                        <td id="previewFatherName"></td>
                    </tr>

                    <!-- Father's Occupation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Father's Occupation</strong></th>
                        <td id="previewFatherOccupation"></td>
                    </tr>

                    <!-- Mother's Name -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Mother's Name</strong></th>
                        <td id="previewMotherName"></td>
                    </tr>

                    <!-- Mother's Occupation -->
                    <tr>
                        <th style="background-color: #f2f2f2;"><strong>Mother's Occupation</strong></th>
                        <td id="previewMotherOccupation"></td>
                    </tr>

                    <tr>
                        <th><strong>Birth Certificate</strong></th>
                        <td id="previewBirthCertificate"></td>
                    </tr>

                    <tr>
                        <th><strong>Aadhar Card</strong></th>
                        <td id="previewAadharCard"></td>
                    </tr>

                    <tr>
                        <th><strong>School Leaving Certificate</strong></th>
                        <td id="previewSchoolLeavingCertificate"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buttons -->
    <div class="form-group text-center" style="margin-top: 20px;">
        <button onclick="submitFinalForm()" class="btn btn-success">Confirm & Submit</button>
        <button onclick="closePreview()" class="btn btn-default">Close</button>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function previewPassportPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("passportPhotoPreview").src = e.target.result;
                // console.log("Photo Preview Source:", document.getElementById("passportPhotoPreview").src);

            };
            reader.readAsDataURL(file);
        }
    }


    // Preview Form Before Submission
    function previewFormBeforeSubmit(event) {
        event.preventDefault(); // Prevent form submission
        showPreview(); // Show preview modal

    }

    // Function to show the preview modal
    function showPreview() {
        const name = document.getElementById("sname").value.trim();
        const studentId = document.getElementById("user_id").value.trim();
        const photoSrc = document.getElementById("passportPhotoPreview").src.trim();
        const selectedClassSection = document.getElementById("class").value.trim(); // Example: "Class 10th 'A'"
        const subjectElement = document.getElementById("subject_checkbox_group");

        if (subjectElement) {
            const selectedSubjects = Array.from(subjectElement.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value.trim());

            document.getElementById("previewSubject").innerText = selectedSubjects.join(", ");
        } else {
            console.warn("Subject select element not found!");
        }

        const phone = document.getElementById("phone_number").value.trim();
        const email = document.getElementById("email_user").value.trim();
        const category = document.getElementById("category").value.trim();
        const admissionDate = document.getElementById("admission_date").value.trim();
        const guardianContact = document.getElementById("guard_contact").value.trim();
        const guardianName = document.getElementById("guard_name").value.trim();
        const guardianRelation = document.getElementById("guard_relation").value.trim();
        const previousClass = document.getElementById("pre_class").value.trim();
        const previousSchool = document.getElementById("pre_school").value.trim();
        const previousMarks = document.getElementById("pre_marks").value.trim();
        const street = document.getElementById("street").value.trim();
        const city = document.getElementById("city").value.trim();
        const state = document.getElementById("state").value.trim();
        const postalCode = document.getElementById("postal_code").value.trim();
        const religion = document.getElementById("religion").value.trim();
        const nationality = document.getElementById("nationality").value.trim();
        const bloodGroup = document.getElementById("blood_group").value.trim();
        const gender = document.getElementById("gender").value.trim();

        const dob = document.getElementById("dob").value.trim();
        const fatherName = document.getElementById("father_name").value.trim();
        const fatherOccupation = document.getElementById("father_occupation").value.trim();
        const motherName = document.getElementById("mother_name").value.trim();
        const motherOccupation = document.getElementById("mother_occupation").value.trim();

        if (!name || !studentId || !photoSrc || !selectedClassSection) {
            alert("Please fill out all fields and select a photo.");
            return;
        }

        document.getElementById("previewName").innerText = name;
        document.getElementById("previewId").innerText = studentId;
        document.getElementById("previewClass").innerText = selectedClassSection;
        document.getElementById("previewPhoto").src = photoSrc;
        document.getElementById("previewPhone").innerText = phone;
        document.getElementById("previewEmail").innerText = email;
        document.getElementById("previewCategory").innerText = category;
        document.getElementById("previewAdmissionDate").innerText = admissionDate;
        document.getElementById("previewGuardContact").innerText = guardianContact;
        document.getElementById("previewGuardName").innerText = guardianName;
        document.getElementById("previewGuardRelation").innerText = guardianRelation;
        document.getElementById("previewPreClass").innerText = previousClass;
        document.getElementById("previewPreSchool").innerText = previousSchool;
        document.getElementById("previewPreMarks").innerText = previousMarks;
        document.getElementById("previewStreet").innerText = street;
        document.getElementById("previewCity").innerText = city;
        document.getElementById("previewState").innerText = state;
        document.getElementById("previewPostalCode").innerText = postalCode;
        document.getElementById("previewReligion").innerText = religion;
        document.getElementById("previewNationality").innerText = nationality;
        document.getElementById("previewBloodGroup").innerText = bloodGroup;
        document.getElementById("previewGender").innerText = gender;
        document.getElementById("previewDob").innerText = dob;
        document.getElementById("previewFatherName").innerText = fatherName;
        document.getElementById("previewFatherOccupation").innerText = fatherOccupation;
        document.getElementById("previewMotherName").innerText = motherName;
        document.getElementById("previewMotherOccupation").innerText = motherOccupation;

        // Show modal
        document.getElementById("previewModal").style.display = "block";
    }


    function submitFinalForm() {
        // Prevent form submission to handle it manually
        event.preventDefault();

        // Get form data using FormData
        var formData = new FormData(document.getElementById('add_student_form'));

        // Get data from the preview modal
        const name = document.getElementById("previewName").innerText.trim();
        const studentId = document.getElementById("previewId").innerText.trim();

        const selectedClassSection = document.getElementById("previewClass").innerText.trim();
        // const selectedSubject = document.getElementById("previewSubject").innerText.trim();

        const selectedSubjects = document.getElementById("previewSubject").innerText.trim();
        if (selectedSubjects) {
            selectedSubjects.split(",").forEach(sub => {
                formData.append('subject[]', sub.trim());
            });
        }


        // const selectedClass = document.getElementById("previewClass").innerText.trim();
        // const selectedSection = document.getElementById("previewSection").innerText.trim();



        const phone = document.getElementById("previewPhone").innerText.trim(); // Phone number from preview
        const email = document.getElementById("previewEmail").innerText.trim(); // Email from preview
        const category = document.getElementById("previewCategory").innerText.trim(); // Category from preview
        const admissionDate = document.getElementById("previewAdmissionDate").innerText
            .trim(); // Admission Date from preview
        const guardianContact = document.getElementById("previewGuardContact").innerText.trim(); // Guardian's Contact
        const guardianName = document.getElementById("previewGuardName").innerText.trim(); // Guardian's Name
        const guardianRelation = document.getElementById("previewGuardRelation").innerText.trim(); // Guardian Relation
        const previousClass = document.getElementById("previewPreClass").innerText.trim(); // Previous Class
        const previousSchool = document.getElementById("previewPreSchool").innerText.trim(); // Previous School
        const previousMarks = document.getElementById("previewPreMarks").innerText.trim(); // Previous Marks
        const street = document.getElementById("previewStreet").innerText.trim(); // Street from preview
        const city = document.getElementById("previewCity").innerText.trim(); // City from preview
        const state = document.getElementById("previewState").innerText.trim(); // State from preview
        const postalCode = document.getElementById("previewPostalCode").innerText.trim(); // Postal Code from preview
        const religion = document.getElementById("previewReligion").innerText.trim(); // Religion from preview
        const nationality = document.getElementById("previewNationality").innerText.trim(); // Nationality from preview
        const bloodGroup = document.getElementById("previewBloodGroup").innerText.trim(); // Blood Group from preview
        const gender = document.getElementById("previewGender").innerText.trim(); // Gender from preview
        const dob = document.getElementById("previewDob").innerText.trim(); // Date of Birth from preview
        const fatherName = document.getElementById("previewFatherName").innerText.trim(); // Father's Name from preview
        const fatherOccupation = document.getElementById("previewFatherOccupation").innerText.trim(); // Father's Occupation
        const motherName = document.getElementById("previewMotherName").innerText.trim(); // Mother's Name from preview
        const motherOccupation = document.getElementById("previewMotherOccupation").innerText.trim(); // Mother's Occupation

        // Get the student photo URL (it may be a base64 or URL)
        const photoSrc = document.getElementById("passportPhotoPreview") ? document.getElementById("passportPhotoPreview")
            .src.trim() : '';

        // Check if all fields have values from the preview modal
        if (!name || !studentId || !photoSrc || !selectedClassSection) {
            alert("Please fill out all fields.");
            return; // Prevent form submission if any required field is missing
        }


        // Remove "Class" prefix from selectedClass and format it
        // var processedClassValue = selectedClassSection.replace(/^(Class\s*)+/i, ''); // removes one or more leading "Class "
        // var combinedClassSection = `Class ${processedClassValue.trim()}`; // properly formatted

        // Add the preview modal values to the FormData object
        formData.append('Name', name);
        formData.append('user_id', studentId);
        formData.append('student_photo', document.getElementById('student_photo').files[0]); // Add the actual file
        formData.append('class_section', selectedClassSection); // Add the class and section
        // formData.append('subject', selectedSubject);
        // Convert to array if needed



        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('category', category);
        formData.append('admission_date', admissionDate);
        formData.append('guardian_contact', guardianContact);
        formData.append('guardian_name', guardianName);
        formData.append('guardian_relation', guardianRelation);
        formData.append('previous_class', previousClass);
        formData.append('previous_school', previousSchool);
        formData.append('previous_marks', previousMarks);
        formData.append('street', street);
        formData.append('city', city);
        formData.append('state', state);
        formData.append('postal_code', postalCode);
        formData.append('religion', religion);
        formData.append('nationality', nationality);
        formData.append('blood_group', bloodGroup);
        formData.append('gender', gender);
        formData.append('dob', dob);
        formData.append('father_name', fatherName);
        formData.append('father_occupation', fatherOccupation);
        formData.append('mother_name', motherName);
        formData.append('mother_occupation', motherOccupation);
        // Add the server-generated photo URL to FormData
        // formData.append('photo_url', photoSrc); // Append the photo URL here

        formData.append('student_photo', document.getElementById('student_photo').files[0]);

        const url = $('#add_student_form').attr("action"); // Get the form's action URL

        // Send the form data via AJAX
        $.ajax({
            url: url, // Your controller method URL
            type: 'POST',
            data: formData,
            processData: false, // Important to keep data as FormData
            contentType: false, // Do not set content type for FormData
            success: function(response) {
                alert('Student Admission Successful!');
                location.reload(); // Optionally reload the page after success
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('There was an error submitting the form.');
            }
        });
    }



    // Function to close the preview modal
    function closePreview() {
        document.getElementById("previewModal").style.display = "none";
    }



    function toggleOtherReligion(selectElement) {
        const otherReligionInput = document.getElementById('other_religion');
        if (selectElement.value === 'Other') {
            otherReligionInput.style.display = 'block';
            otherReligionInput.required = true; // Make it a required field
        } else {
            otherReligionInput.style.display = 'none';
            otherReligionInput.value = ''; // Clear the input value
            otherReligionInput.required = false; // Remove the required attribute
        }
    }



    document.addEventListener("DOMContentLoaded", function() {
        // Select the "Select All" checkbox
        var selectAllCheckbox = document.getElementById("select_all_exempted_fees");
        // Select all fee checkboxes by name
        var feeCheckboxes = document.querySelectorAll('input[name="exempted_fees_multiple[]"]');

        // Add event listener for change on the "Select All" checkbox
        selectAllCheckbox.addEventListener("change", function() {
            feeCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Optionally, if you want to un-check "Select All" when any fee checkbox is manually deselected:
        feeCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener("change", function() {
                if (!checkbox.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // If all fee checkboxes are checked, set the "Select All" checkbox to checked.
                    var allChecked = Array.from(feeCheckboxes).every(function(cb) {
                        return cb.checked;
                    });
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class');
        const subjectContainer = document.getElementById('subject_checkbox_group');

        classSelect.addEventListener('change', function() {
            const selectedClass = classSelect.value;

            if (selectedClass) {
                fetch('fetch_subjects', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            classSection: selectedClass
                        })
                    })
                    .then(response => response.json())
                    .then(subjects => {
                        // Clear previous content
                        subjectContainer.innerHTML = '';

                        if (subjects.length === 0) {
                            subjectContainer.innerHTML = '<p>No subjects available for this class.</p>';
                            return;
                        }

                        // Create checkboxes
                        subjects.forEach(subject => {
                            const checkboxId = `subject_${subject.replace(/\s+/g, '_')}`;

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'optional_subjects[]';
                            checkbox.value = subject;
                            checkbox.id = checkboxId;
                            checkbox.classList.add('form-check-input');

                            const label = document.createElement('label');
                            label.htmlFor = checkboxId;
                            label.classList.add('form-check-label', 'ms-1');
                            label.textContent = subject;

                            const wrapper = document.createElement('div');
                            wrapper.classList.add('form-check', 'mb-1');
                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);

                            subjectContainer.appendChild(wrapper);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching subjects:', error);
                        subjectContainer.innerHTML =
                            '<p style="color:red;">Failed to load subjects.</p>';
                    });
            } else {
                subjectContainer.innerHTML =
                    '<p style="font-style: italic; color: #777;">Select a class to view subjects.</p>';
            }
        });
    });
</script>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
    }

    /* Content Wrapper Styling */
    .content-wrapper {
        padding: 20px;
        background-color: #ecf0f5;
    }

    .container {
        width: 98%;
        padding-right: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding-bottom: 20px;
        font-size: 16px;
    }

    .title-bar {
        background-color: #f39c12;
        /* Yellow-light theme */
        color: #ffffff;
        /* White text for contrast */
        margin-top: 10px;
        font-weight: bold;
        text-align: center;
        font-size: 20px;
        /* Reduced size slightly to avoid overpowering */
        padding: 8px 12px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }



    .buttons {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 20px;
    }

    .footer {
        background-color: #343a40;
        color: white;
        padding: 10px;
        text-align: center;
        margin-top: 20px;
    }

    button {
        min-width: 120px;
        /* Ensures uniform button size */
    }

    .footer {
        background-color: #343a40;
        color: white;
        padding: 10px;
        text-align: center;
        margin-top: 20px;
    }

    .section-header {
        text-align: center;
        color: #ffffff;
        /* White text */
        background-color: #f39c12;
        /* Primary yellow theme color */
        font-size: 26px;
        font-weight: bold;
        padding: 10px 15px;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        /* Slightly deeper shadow for visibility */
        margin-bottom: 20px;
        letter-spacing: 1px;
    }


    #previewModal {
        display: none;
        /* Hidden by default */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        padding: 20px;
        width: 90%;
        max-width: 500px;
    }


    .styled-fieldset {
        border: 2px solid #f39c12;
        /* Yellow theme border */
        border-radius: 10px;
        padding: 20px;
        background: linear-gradient(to right, #fff3cd, #ffffff);
        /* Light yellow background */
        box-shadow: 0 0 10px rgba(243, 156, 18, 0.2);
        /* Subtle yellow shadow */
        transition: all 0.3s ease-in-out;
        margin-bottom: 20px;
    }

    .styled-fieldset:hover {
        box-shadow: 0 0 15px rgba(243, 156, 18, 0.4);
        /* Stronger yellow shadow */
        background: linear-gradient(to right, #ffe8a1, #ffffff);
        /* On hover, brighter yellow blend */
    }

    .styled-fieldset legend {
        width: auto;
        padding: 0 15px;
        font-size: 1.25rem;
        font-weight: bold;
        color: #f39c12;
        /* Matches navbar text and logo */
        border-bottom: none;
    }
</style>

-->















