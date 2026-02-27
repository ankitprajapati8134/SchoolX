<div class="content-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h2>Add New Staff</h2>

        <div class="breadcrumb-custom">
            <a href="<?= base_url('dashboard'); ?>">Dashboard</a>
            <span>/</span>
            <a href="<?= base_url('staff/all_staff'); ?>">All Staff</a>
            <span>/</span>
            <span class="active">Add Staff</span>
        </div>
    </div>

    <form action="<?php echo base_url('staff/new_staff'); ?>"
        method="post"
        id="add_staff_form"
        enctype="multipart/form-data">

        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
        <!-- ================= BIG MAIN CARD ================= -->
        <div class="main-card">

            <!-- ===== BASIC INFORMATION ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-user"></i>
                    Staff Basic Information
                </div>

                <div class="form-grid">

                    <div class="form-field">
                        <label>Staff Name *</label>
                        <input type="text" id="name" name="Name" required>
                    </div>

                    <div class="form-field">
                        <label>Staff ID *</label>
                        <input type="text" id="user_id" name="user_id"
                            value="<?= $staffIdCount ?>" readonly>
                    </div>

                    <div class="form-field"> <label>Date of Birth *</label> <input type="date" id="dob" name="dob" required> </div>
                    <div class="form-field"> <label>Email *</label> <input type="email" id="email_user" name="email" required> </div>


                    <div class="form-field">
                        <label>Blood Group *</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select</option>
                            <option>A+</option>
                            <option>A-</option>
                            <option>B+</option>
                            <option>B-</option>
                            <option>O+</option>
                            <option>O-</option>
                            <option>AB+</option>
                            <option>AB-</option>
                            <option>Unknown</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Religion *</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select</option>
                            <option>Hindu</option>
                            <option>Muslim</option>
                            <option>Sikh</option>
                            <option>Jain</option>
                            <option>Buddh</option>
                            <option>Christian</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select</option>
                            <option>General</option>
                            <option>OBC</option>
                            <option>SC</option>
                            <option>ST</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                            <option>Other</option>
                        </select>
                    </div>



                    <div class="form-field">
                        <label>Staff Position *</label>
                        <input type="text" id="staff_position" name="staff_position" required>
                    </div>

                    <div class="form-field">
                        <label>Date of Joining *</label>
                        <input type="date"
                            id="date_of_joining"
                            name="date_of_joining"
                            value="<?php echo date('Y-m-d'); ?>"
                            readonly required>
                    </div>
                    <div class="form-field">
                        <label>Upload Staff Photo *</label>
                        <input type="file" id="photo" name="Photo" accept="image/*" required>
                    </div>

                    <div class="form-field">
                        <label>Upload Aadhar *</label>
                        <input type="file" id="aadhar" name="Aadhar" accept="image/*,.pdf" required>
                    </div>

                </div>
            </div>

            <!-- ===== GUARDIAN ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-users"></i>
                    Guardian Details
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Father Name *</label>
                        <input type="text" id="father_name" name="father_name" required>
                    </div>

                    <div class="form-field">
                        <label>Phone Number *</label>
                        <input type="tel" id="phone_number" name="phone_number"
                            pattern="[0-9]{10}" maxlength="10" required>
                    </div>

                    <div class="form-field">
                        <label>Emergency Contact Name *</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" required>
                    </div>
                    <div class="form-field">
                        <label>Emergency Contact *</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                            pattern="[0-9]{10}" maxlength="10" required>
                    </div>
                </div>


            </div>



            <!-- ===== ADDRESS ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-map-marker"></i>
                    Address Details
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Street *</label>
                        <input type="text" id="street" name="street" required>
                    </div>

                    <div class="form-field">
                        <label>City *</label>
                        <input type="text" id="city" name="city" required>
                    </div>

                    <div class="form-field">
                        <label>State *</label>
                        <input type="text" id="state" name="state" required>
                    </div>

                    <div class="form-field">
                        <label>Postal Code *</label>
                        <input type="text" id="postal_code" name="postal_code" required>
                    </div>
                </div>
            </div>



            <!-- ===== QUALIFICATION ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-graduation-cap"></i>
                    Staff Qualification Details
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Employment Type *</label>
                        <input type="text" id="employment_type" name="employment_type" required>
                    </div>

                    <div class="form-field">
                        <label>Highest Qualification *</label>
                        <input type="text" id="qualification" name="qualification" required>
                    </div>

                    <div class="form-field">
                        <label>University *</label>
                        <input type="text" id="university" name="university" required>
                    </div>

                    <div class="form-field">
                        <label>Year of Passing *</label>
                        <input type="text" id="year_of_passing" name="year_of_passing" required>
                    </div>

                    <div class="form-field">
                        <label>Work Experience (Years) *</label>
                        <input type="text" id="teacher_experience" name="teacher_experience" required>
                    </div>

                    <div class="form-field">
                        <label>Teacher Department *</label>
                        <input type="text" id="teacher_department" name="department" required>
                    </div>
                </div>
            </div>

            <!-- ===== BANK ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-bank"></i>
                    Bank Details
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Bank Name *</label>
                        <input type="text" id="bank_name" name="bank_name" required>
                    </div>

                    <div class="form-field">
                        <label>Account Holder *</label>
                        <input type="text" id="account_holder" name="account_holder" required>
                    </div>

                    <div class="form-field">
                        <label>Account Number *</label>
                        <input type="text" id="account_number" name="account_number" required>
                    </div>

                    <div class="form-field">
                        <label>IFSC Code *</label>
                        <input type="text" id="bank_ifsc" name="bank_ifsc" required>
                    </div>
                </div>
            </div>

            <!-- ===== SALARY ===== -->
            <div class="inner-section-card">
                <div class="section-title">
                    <i class="fa fa-money"></i>
                    Salary Details
                </div>

                <div class="form-grid-2">
                    <div class="form-field">
                        <label>Allowances *</label>
                        <input type="number" id="allowances" name="allowances" required>
                    </div>

                    <div class="form-field">
                        <label>Basic Salary *</label>
                        <input type="number" id="basicSalary" name="basicSalary" required>
                    </div>
                </div>
            </div>


            <div class="submit-area">

                <button type="reset" class="btn btn-secondary">
                    <i class="fa fa-refresh"></i> Reset
                </button>


                <button type="button" id="preview_button" class="btn-submit">
                    Preview
                </button>
            </div>

        </div> <!-- END MAIN CARD -->



    </form>
</div>

<!-- ================= STAFF PREVIEW MODAL ================= -->
<div class="modal fade" id="staffPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h4 class="modal-title">
                    <i class="fa fa-eye"></i> Staff Registration Preview
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="previewContent">
                <!-- Dynamic preview -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Edit Details
                </button>

                <button type="button" id="confirmSubmitBtn" class="btn btn-primary">
                    Confirm & Submit
                </button>

            </div>

        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {

        const form = document.getElementById("add_staff_form");
        const previewBtn = document.getElementById("preview_button");
        const confirmBtn = document.getElementById("confirmSubmitBtn");
        const previewContent = document.getElementById("previewContent");

        /* ============================================================
           HELPER FUNCTIONS
        ============================================================ */

        function getValue(id) {
            const el = document.getElementById(id);
            return el ? el.value.trim() : "-";
        }

        function getSelectedText(id) {
            const el = document.getElementById(id);
            return el && el.selectedIndex >= 0 ?
                el.options[el.selectedIndex].text :
                "-";
        }

        /* ============================================================
           PREVIEW BUTTON
        ============================================================ */

        if (previewBtn) {
            previewBtn.addEventListener("click", function() {

                // Validate entire form before preview
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const photoFile = document.getElementById("photo").files[0];
                const aadharFile = document.getElementById("aadhar").files[0];

                /* ================= PHOTO ================= */
                let photoPreview = "";
                if (photoFile) {
                    const photoURL = URL.createObjectURL(photoFile);
                    photoPreview = `
                    <div style="text-align:center;margin-bottom:25px;">
                        <img src="${photoURL}" class="preview-photo">
                    </div>
                `;
                }

                /* ================= AADHAR ================= */
                let documentPreview = "";
                if (aadharFile) {
                    const fileURL = URL.createObjectURL(aadharFile);
                    documentPreview = `
                    <tr>
                        <td class="preview-label-cell">Aadhar Document</td>
                        <td class="preview-value-cell" colspan="3">
                            <div style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                                <span>${aadharFile.name}</span>
                                <a href="${fileURL}" target="_blank" class="document-eye-btn">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
                }

                /* ============================================================
                   COMPLETE PREVIEW HTML
                ============================================================ */

                const previewHTML = `
                ${photoPreview}

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-user"></i> Basic Information
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Staff Name</td>
                            <td class="preview-value-cell">${getValue("name")}</td>
                            <td class="preview-label-cell">Staff ID</td>
                            <td class="preview-value-cell">${getValue("user_id")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">DOB</td>
                            <td class="preview-value-cell">${getValue("dob")}</td>
                            <td class="preview-label-cell">Gender</td>
                            <td class="preview-value-cell">${getSelectedText("gender")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Blood Group</td>
                            <td class="preview-value-cell">${getSelectedText("blood_group")}</td>
                            <td class="preview-label-cell">Category</td>
                            <td class="preview-value-cell">${getSelectedText("category")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Religion</td>
                            <td class="preview-value-cell">${getSelectedText("religion")}</td>
                            <td class="preview-label-cell">Email</td>
                            <td class="preview-value-cell">${getValue("email_user")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Position</td>
                            <td class="preview-value-cell">${getValue("staff_position")}</td>
                            <td class="preview-label-cell">Joining Date</td>
                            <td class="preview-value-cell">${getValue("date_of_joining")}</td>
                        </tr>
                        ${documentPreview}
                    </table>
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-phone"></i> Contact Details
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Father Name</td>
                            <td class="preview-value-cell">${getValue("father_name")}</td>
                            <td class="preview-label-cell">Phone</td>
                            <td class="preview-value-cell">${getValue("phone_number")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Emergency Name</td>
                            <td class="preview-value-cell">${getValue("emergency_contact_name")}</td>
                            <td class="preview-label-cell">Emergency Contact</td>
                            <td class="preview-value-cell">${getValue("emergency_contact_phone")}</td>
                        </tr>
                    </table>
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-map-marker"></i> Address Details
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Street</td>
                            <td class="preview-value-cell">${getValue("street")}</td>
                            <td class="preview-label-cell">City</td>
                            <td class="preview-value-cell">${getValue("city")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">State</td>
                            <td class="preview-value-cell">${getValue("state")}</td>
                            <td class="preview-label-cell">Postal Code</td>
                            <td class="preview-value-cell">${getValue("postal_code")}</td>
                        </tr>
                    </table>
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-graduation-cap"></i> Qualification Details
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Employment Type</td>
                            <td class="preview-value-cell">${getValue("employment_type")}</td>
                            <td class="preview-label-cell">Qualification</td>
                            <td class="preview-value-cell">${getValue("qualification")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">University</td>
                            <td class="preview-value-cell">${getValue("university")}</td>
                            <td class="preview-label-cell">Year of Passing</td>
                            <td class="preview-value-cell">${getValue("year_of_passing")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Experience</td>
                            <td class="preview-value-cell">${getValue("teacher_experience")}</td>
                            <td class="preview-label-cell">Department</td>
                            <td class="preview-value-cell">${getValue("teacher_department")}</td>
                        </tr>
                    </table>
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-bank"></i> Bank Details
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Bank Name</td>
                            <td class="preview-value-cell">${getValue("bank_name")}</td>
                            <td class="preview-label-cell">Account Holder</td>
                            <td class="preview-value-cell">${getValue("account_holder")}</td>
                        </tr>
                        <tr>
                            <td class="preview-label-cell">Account Number</td>
                            <td class="preview-value-cell">${getValue("account_number")}</td>
                            <td class="preview-label-cell">IFSC</td>
                            <td class="preview-value-cell">${getValue("bank_ifsc")}</td>
                        </tr>
                    </table>
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class="fa fa-money"></i> Salary Details
                    </div>
                    <table class="preview-table">
                        <tr>
                            <td class="preview-label-cell">Allowances</td>
                            <td class="preview-value-cell">${getValue("allowances")}</td>
                            <td class="preview-label-cell">Basic Salary</td>
                            <td class="preview-value-cell">${getValue("basicSalary")}</td>
                        </tr>
                    </table>
                </div>
            `;

                previewContent.innerHTML = previewHTML;
                $('#staffPreviewModal').modal('show');
            });
        }

        /* ============================================================
           CONFIRM & SUBMIT (AJAX)
        ============================================================ */

        if (confirmBtn) {
            confirmBtn.addEventListener("click", function() {

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const formData = new FormData(form);

                confirmBtn.disabled = true;
                confirmBtn.innerHTML = "Saving...";

                fetch("<?= base_url('staff/new_staff'); ?>", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {

                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = "Confirm & Submit";

                        if (data.status === "success") {

                            $('#staffPreviewModal').modal('hide');

                            alert("Staff saved successfully.");

                            form.reset();

                            setTimeout(() => {
                                window.location.reload();
                            }, 800);

                        } else {
                            alert("Error saving staff.");
                        }

                    })
                    .catch(error => {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = "Confirm & Submit";
                        alert("Server error.");
                    });
            });
        }

    });
</script>




<style>
    /* ================= GLOBAL FIX ================= */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    /* ================= PAGE WRAPPER ================= */
    .content-wrapper {
        background: #f4f6f9;
        padding: 20px 25px;
        /* Reduced from 30px */
    }


    /* ================= PAGE HEADER ================= */
    .page-header {
        margin-bottom: 15px;
        /* Reduced spacing below header */
    }

    .page-header h2 {
        margin-bottom: 8px;
        /* Reduce header text spacing */
        font-size: 22px;
        font-weight: 600;
    }

    /* ================= BREADCRUMB ================= */
    .breadcrumb-custom {
        font-size: 14px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .breadcrumb-custom a {
        color: #495057;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #1e88e5;
    }

    .breadcrumb-custom span {
        color: #adb5bd;
    }

    .breadcrumb-custom .active {
        color: #8a9096;
        font-weight: 600;
        cursor: default;
    }


    /* ================= MAIN CARD ================= */
    .main-card {
        background: #ffffff;
        border-radius: 14px;
        /* Slightly reduced */
        padding: 25px;
        /* Reduced from 30px */
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e5e5;
        margin-top: 10px;
        /* Reduced gap from header */

    }


    /* ================= INNER SECTION CARD ================= */
    .inner-section-card {
        background: #f9fafc;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #e6e6e6;
        overflow: hidden;
    }


    /* ================= SECTION TITLE ================= */
    .section-title {
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 20px;
        border-left: 4px solid #1e88e5;
        padding-left: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }


    /* ================= FORM GRID ================= */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        width: 100%;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }


    /* ================= FORM FIELDS ================= */
    .form-field {
        display: flex;
        flex-direction: column;
    }

    .form-field label {
        font-size: 14px;
        margin-bottom: 6px;
        font-weight: 500;
        color: #495057;
    }

    .form-field input,
    .form-field select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #dcdfe3;
        background: #fff;
        font-size: 14px;
        transition: border 0.2s ease;
    }

    .form-field input:focus,
    .form-field select:focus {
        border-color: #1e88e5;
        outline: none;
    }

    .form-field input[type="file"] {
        padding: 6px;
    }


    /* ================= SUBMIT AREA ================= */
    .submit-area {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-submit {
        background: #1e88e5;
        color: #fff;
        padding: 10px 35px;
        border-radius: 8px;
        border: none;
        transition: background 0.2s ease;
    }

    .btn-submit:hover {
        background: #1565c0;
    }


    /* ================= PREVIEW MODAL ================= */
    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
        padding: 30px;
    }


    /* ================= PREVIEW PHOTO ================= */
    .preview-photo {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #1e88e5;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }


    /* ================= PREVIEW SECTION ================= */
    .preview-section {
        margin-bottom: 35px;
        background: #ffffff;
        border-radius: 14px;
        padding: 25px 30px;
        border: 1px solid #e3e6ea;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .preview-section-title {
        font-size: 17px;
        font-weight: 600;
        border-left: 4px solid #1e88e5;
        padding-left: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }


    /* ================= PREVIEW TABLE ================= */
    .preview-table {
        width: 100%;
        border-collapse: collapse;
    }

    .preview-table td {
        padding: 14px 16px;
        border: 1px solid #edf0f3;
        font-size: 14px;
    }

    .preview-label-cell {
        background: #f4f6f9;
        font-weight: 600;
        width: 20%;
        color: #495057;
    }

    .preview-value-cell {
        background: #ffffff;
        width: 30%;
    }


    /* ================= DOCUMENT BUTTON ================= */
    .document-eye-btn {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        background: #1e88e5;
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        transition: background 0.2s ease;
    }

    .document-eye-btn:hover {
        background: #1565c0;
    }


    /* ================= RESPONSIVE ================= */
    @media (max-width: 992px) {

        .form-grid,
        .form-grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .content-wrapper {
            padding: 15px;
        }

        .main-card {
            padding: 20px;
        }

        .inner-section-card {
            padding: 20px;
        }

        .preview-section {
            padding: 20px;
        }
    }
</style>