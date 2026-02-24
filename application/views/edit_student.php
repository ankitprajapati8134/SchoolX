<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/* ── getDocUrls helper ── */
if (!function_exists('getDocUrls')) {
    function getDocUrls($docNode)
    {
        if (is_array($docNode)) {
            return [
                'url'       => $docNode['url']       ?? '',
                'thumbnail' => $docNode['thumbnail'] ?? ''
            ];
        }
        return [
            'url'       => (string)($docNode ?? ''),
            'thumbnail' => ''
        ];
    }
}

/* ── Extract doc nodes ── */
$birthCert = getDocUrls($student_data['Doc']['Birth Certificate']    ?? '');
$aadhar    = getDocUrls($student_data['Doc']['Aadhar Card']          ?? '');
$transfer  = getDocUrls($student_data['Doc']['Transfer Certificate'] ?? '');
$profilePic = $student_data['Profile Pic'] ?? '';
?>



<div class="content-wrapper">
    <!-- ================= PAGE HEADER ================= -->
    <section class="content-header">
        <h1 class="page-title">
            <i class="fa fa-users text-primary"></i>
            Edit Student
        </h1>
        <ol class="breadcrumb custom-breadcrumb">
            <li>
                <a href="<?= base_url('dashboard'); ?>">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= base_url('student/all_student'); ?>">
                    All Students
                </a>
            </li>
            <li class="active">Edit Student</li>
        </ol>
    </section>

    <div class="box box-primary erp-card">
        <div class="box-body">

            <form action="<?= base_url('student/edit_student/' . $student_data['User Id']); ?>" method="post"
                enctype="multipart/form-data" id="edit_student_form">

                <!-- ================= BASIC INFORMATION ================= -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fa fa-user"></i> Student Basic Information
                    </div>

                    <div class="row">

                        <div class="form-group col-md-3">
                            <label>Student ID</label>
                            <input type="text" name="user_id" value="<?= $student_data['User Id']; ?>"
                                class="form-control" readonly>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Student Name</label>
                            <input type="text" name="Name" value="<?= $student_data['Name']; ?>" class="form-control"
                                required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Class</label>
                            <input type="text" name="class" value="<?= $student_data['Class']; ?>" class="form-control"
                                readonly>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Section</label>
                            <input type="text" name="section" value="<?= $student_data['Section']; ?>"
                                class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Date of Birth</label>
                            <input type="text" name="dob" value="<?= $student_data['DOB'] ?? '' ?>"
                                class="form-control datepicker" placeholder="dd-mm-yyyy" required>
                        </div>


                        <div class="form-group col-md-3">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="Male" <?= $student_data['Gender'] == 'Male' ? 'selected' : '' ?>>Male
                                </option>
                                <option value="Female" <?= $student_data['Gender'] == 'Female' ? 'selected' : '' ?>>
                                    Female</option>
                                <option value="Other" <?= $student_data['Gender'] == 'Other' ? 'selected' : '' ?>>
                                    Other</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Admission Date</label>
                            <input type="text" name="admission_date"
                                value="<?= $student_data['Admission Date'] ?? '' ?>" class="form-control datepicker"
                                placeholder="dd-mm-yyyy" required>
                        </div>


                        <div class="form-group col-md-3">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= $student_data['Email']; ?>" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Phone Number</label>
                            <input type="text" name="phone_number" value="<?= $student_data['Phone Number']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Blood Group</label>
                            <select name="blood_group" class="form-control">
                                <?php $groups = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-", "Unknown"];
                                foreach ($groups as $g): ?>
                                    <option value="<?= $g ?>" <?= $student_data['Blood Group'] == $g ? 'selected' : '' ?>>
                                        <?= $g ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="General" <?= $student_data['Category'] == 'General' ? 'selected' : '' ?>>
                                    General</option>
                                <option value="OBC" <?= $student_data['Category'] == 'OBC' ? 'selected' : '' ?>>OBC
                                </option>
                                <option value="SC" <?= $student_data['Category'] == 'SC' ? 'selected' : '' ?>>SC
                                </option>
                                <option value="ST" <?= $student_data['Category'] == 'ST' ? 'selected' : '' ?>>ST
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Additional Subjects</label>
                            <div class="subject-box">

                                <?php
                                $selectedSubjects = [];

                                if (!empty($additional_subjects) && is_array($additional_subjects)) {
                                    foreach ($additional_subjects as $key => $value) {

                                        if (is_string($key) && trim($key) !== '') {
                                            $selectedSubjects[] = strtolower(trim($key));
                                        }

                                        if (is_string($value) && trim($value) !== '') {
                                            $selectedSubjects[] = strtolower(trim($value));
                                        }
                                    }
                                }

                                $selectedSubjects = array_unique($selectedSubjects);
                                ?>

                                <?php if (!empty($allSubjects) && is_array($allSubjects)): ?>

                                    <?php foreach ($allSubjects as $subject):

                                        $subjectTrimmed = trim($subject);
                                        $subjectLower   = strtolower($subjectTrimmed);

                                    ?>

                                        <div class="form-check">
                                            <input type="checkbox"
                                                class="form-check-input"
                                                name="additional_subjects[]"
                                                value="<?= htmlspecialchars($subjectTrimmed); ?>"
                                                <?= in_array($subjectLower, $selectedSubjects, true) ? 'checked' : ''; ?>>

                                            <label class="form-check-label">
                                                <?= htmlspecialchars($subjectTrimmed); ?>
                                            </label>
                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <small class="text-muted">No additional subjects available.</small>
                                <?php endif; ?>

                            </div>



                        </div>
                    </div>
                </div>



                <div class="section-card">
                    <div class="section-title">
                        <i class="fa fa-users"></i> Parents Details
                    </div>
                    <div class="row">

                        <div class="form-group col-md-3">
                            <label>Father Name</label>
                            <input type="text" name="father_name" value="<?= $student_data['Father Name']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Father Occupation</label>
                            <input type="text" name="father_occupation"
                                value="<?= $student_data['Father Occupation']; ?>" class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Mother Name</label>
                            <input type="text" name="mother_name" value="<?= $student_data['Mother Name']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Mother Occupation</label>
                            <input type="text" name="mother_occupation"
                                value="<?= $student_data['Mother Occupation']; ?>" class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Guardian Contact</label>
                            <input type="text" name="guard_contact" value="<?= $student_data['Guard Contact']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Guardian Relation</label>
                            <input type="text" name="guard_relation" value="<?= $student_data['Guard Relation']; ?>"
                                class="form-control" required>
                        </div>
                    </div>
                </div>



                <div class="section-card">
                    <div class="section-title">
                        <i class="fa fa-map-marker"></i> Address Details
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Street</label>
                            <input type="text" name="street" value="<?= $student_data['Address']['Street']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>City</label>
                            <input type="text" name="city" value="<?= $student_data['Address']['City']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>State</label>
                            <input type="text" name="state" value="<?= $student_data['Address']['State']; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code" value="<?= $student_data['Address']['PostalCode']; ?>"
                                class="form-control" required>
                        </div>
                    </div>
                </div>


                <div class="section-card">
                    <div class="section-title"><i class="fa fa-university"></i> Previous School Details</div>

                    <div class="row">

                        <div class="form-group col-md-4">
                            <label>Previous Class</label>
                            <input type="text" name="pre_class" value="<?= $student_data['Pre Class'] ?? ''; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Previous School</label>
                            <input type="text" name="pre_school" value="<?= $student_data['Pre School'] ?? ''; ?>"
                                class="form-control" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Previous Marks (%)</label>
                            <input type="text" name="pre_marks" value="<?= $student_data['Pre Marks'] ?? ''; ?>"
                                class="form-control" required>
                        </div>
                    </div>
                </div>


                <div class="section-card">
                    <div class="section-title">
                        <i class="fa fa-money"></i> Exempted Fees
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="select_all_exempted_fees">
                                <label class="form-check-label">
                                    Select All Fees
                                </label>
                            </div>

                            <div class="subject-box">
                                <?php
                                // array_keys() gives ["Bus Fees", "Tuition Fee", ...]
                                // These are the fee NAMES stored in Firebase as keys
                                $selectedFees = is_array($selected_exempted_fees)
                                    ? array_keys($selected_exempted_fees)
                                    : [];
                                ?>

                                <?php if (!empty($exemptedFees) && is_array($exemptedFees)): ?>
                                    <?php foreach ($exemptedFees as $feeType => $fees): ?>
                                        <?php if (!is_array($fees)) continue; ?>
                                        <?php foreach ($fees as $feeKey => $feeValue):
                                            $feeKey = trim($feeKey);
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input exempted-fee-checkbox"
                                                    type="checkbox"
                                                    name="exempted_fees_multiple[]"
                                                    value="<?= htmlspecialchars($feeKey) ?>"
                                                    <?= in_array($feeKey, $selectedFees, true) ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    <?= htmlspecialchars($feeKey) ?>
                                                    <small class="text-muted">
                                                        (<?= htmlspecialchars($feeType) ?>)
                                                    </small>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <small class="text-muted">No fee options available.</small>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>



                <!-- ================= DOCUMENTS & PHOTO ================= -->
                <div class="section-card">
                    <div class="section-title"><i class="fa fa-file-text-o"></i> Documents &amp; Photo</div>

                    <div class="row">

                        <!-- ── BIRTH CERTIFICATE ── -->
                        <div class="form-group col-md-4">
                            <label>Birth Certificate</label>

                            <?php if ($birthCert['url']): ?>
                                <div class="existing-doc mb-2 d-flex align-items-center" style="gap:10px;">
                                    <?php if ($birthCert['thumbnail']): ?>
                                        <img src="<?= htmlspecialchars($birthCert['thumbnail']) ?>"
                                            alt="Preview"
                                            style="width:50px;height:65px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                    <?php else: ?>
                                        <!-- No thumbnail: show generic file icon -->
                                        <div style="width:50px;height:65px;background:#f1f5f9;border-radius:4px;
                                                    display:flex;align-items:center;justify-content:center;
                                                    border:1px solid #ddd;color:#64748b;font-size:20px;">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= htmlspecialchars($birthCert['url']) ?>"
                                            target="_blank"
                                            class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View Existing
                                        </a>
                                        <p class="text-muted small mb-0 mt-1">Upload below to replace</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file"
                                name="birthCertificate"
                                id="birthCertificate"
                                class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <small class="text-muted">PDF, JPG, PNG — max 2 MB</small>
                        </div>

                        <!-- ── AADHAR CARD ── -->
                        <div class="form-group col-md-4">
                            <label>Aadhar Card</label>

                            <?php if ($aadhar['url']): ?>
                                <div class="existing-doc mb-2 d-flex align-items-center" style="gap:10px;">
                                    <?php if ($aadhar['thumbnail']): ?>
                                        <img src="<?= htmlspecialchars($aadhar['thumbnail']) ?>"
                                            alt="Preview"
                                            style="width:50px;height:65px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                    <?php else: ?>
                                        <div style="width:50px;height:65px;background:#f1f5f9;border-radius:4px;
                                                    display:flex;align-items:center;justify-content:center;
                                                    border:1px solid #ddd;color:#64748b;font-size:20px;">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= htmlspecialchars($aadhar['url']) ?>"
                                            target="_blank"
                                            class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View Existing
                                        </a>
                                        <p class="text-muted small mb-0 mt-1">Upload below to replace</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file"
                                name="aadharCard"
                                id="aadharCard"
                                class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <small class="text-muted">PDF, JPG, PNG — max 2 MB</small>
                        </div>

                        <!-- ── TRANSFER CERTIFICATE ── -->
                        <div class="form-group col-md-4">
                            <label>Transfer Certificate</label>

                            <?php if ($transfer['url']): ?>
                                <div class="existing-doc mb-2 d-flex align-items-center" style="gap:10px;">
                                    <?php if ($transfer['thumbnail']): ?>
                                        <img src="<?= htmlspecialchars($transfer['thumbnail']) ?>"
                                            alt="Preview"
                                            style="width:50px;height:65px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                    <?php else: ?>
                                        <div style="width:50px;height:65px;background:#f1f5f9;border-radius:4px;
                                                    display:flex;align-items:center;justify-content:center;
                                                    border:1px solid #ddd;color:#64748b;font-size:20px;">
                                            <i class="fa fa-file-text-o"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= htmlspecialchars($transfer['url']) ?>"
                                            target="_blank"
                                            class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View Existing
                                        </a>
                                        <p class="text-muted small mb-0 mt-1">Upload below to replace</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file"
                                name="transferCertificate"
                                id="transferCertificate"
                                class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <small class="text-muted">PDF, JPG, PNG — max 2 MB</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Student Photo</label>

                            <div class="mb-2">
                                <img id="passportPhotoPreview"
                                    src="<?= htmlspecialchars($profilePic ?: base_url('tools/dist/img/kids.jpg')) ?>"
                                    class="img-thumbnail"
                                    style="width:170px;height:200px;object-fit:cover;display:block;">
                            </div>

                            <input type="file"
                                name="student_photo"
                                id="student_photo"
                                class="form-control"
                                accept="image/*"
                                onchange="previewPassportPhoto(event)">
                            <small class="text-muted">JPG, PNG, WEBP — max 2 MB. Upload to replace current photo.</small>
                        </div>

                    </div>
                </div>




                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-danger" onclick="goBack()">
                            <i class="fa fa-times"></i> Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Student
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    if (typeof showAlert === 'undefined') {
        function showAlert(type, message) {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };
            const box = document.createElement('div');
            box.innerText = message;
            Object.assign(box.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '12px 18px',
                background: colors[type] || '#333',
                color: '#fff',
                borderRadius: '6px',
                boxShadow: '0 4px 10px rgba(0,0,0,0.2)',
                zIndex: '9999',
                fontSize: '13px'
            });
            document.body.appendChild(box);
            setTimeout(() => box.remove(), 3000);
        }
    }

    function previewPassportPhoto(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        const preview = document.getElementById('passportPhotoPreview');
        if (!preview) return;
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    }

    function goBack() { window.history.back(); }

    document.addEventListener('DOMContentLoaded', function () {

        const form = document.getElementById('edit_student_form');
        if (!form) return;

        /* ── Guard flag — prevents double submission ──
           Set to true the moment AJAX fires.
           Reset to false only on error so user can retry.
           On success we redirect anyway so no reset needed.
        ── */
        let isSubmitting = false;

        form.addEventListener('submit', function (e) {
            e.preventDefault();  // always stop native HTML form POST
            e.stopImmediatePropagation(); // stop any other submit listeners on this form

            /* If already in flight, do nothing */
            if (isSubmitting) return;
            isSubmitting = true;

            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            }

            $.ajax({
                url:         form.getAttribute('action'),
                type:        'POST',
                data:        new FormData(form),
                processData: false,
                contentType: false,
                dataType:    'json',
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.photo_notice) {
                            showAlert('info', response.photo_notice);
                        }
                        showAlert('success', 'Student updated successfully!');
                        setTimeout(function () {
                            window.location.href = '<?= base_url("student/all_student") ?>';
                        }, 1500);
                        /* no reset — we're redirecting */
                    } else {
                        showAlert('error', response.message || 'Failed to update student.');
                        isSubmitting = false; // allow retry
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa fa-save"></i> Update Student';
                        }
                    }
                },
                error: function (xhr) {
                    let msg = 'Server error — please try again.';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.message) msg = res.message;
                    } catch (err) {}
                    showAlert('error', msg);
                    isSubmitting = false; // allow retry
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-save"></i> Update Student';
                    }
                }
            });
        });

        /* ── Datepicker ── */
        if (typeof $.fn.datepicker !== 'undefined') {
            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true
            });
        }

        /* ── Select all exempted fees ── */
        const selectAll  = document.getElementById('select_all_exempted_fees');
        const checkboxes = document.querySelectorAll('.exempted-fee-checkbox');

        if (selectAll && checkboxes.length) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    selectAll.checked = Array.from(checkboxes).every(c => c.checked);
                });
            });
        }

    });
</script>


<!-- <script>
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



    function goBack() {
        window.history.back();
    }

    $(document).ready(function() {

        $('#edit_student_form').on('submit', function(e) {

            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',

                success: function(response) {

                    if (response.status === 'success') {

                        if (response.photo_notice) {
                            showAlert("info", response.photo_notice);
                        }

                        showAlert("success", "Student updated successfully!");

                        setTimeout(function() {
                            window.location.href =
                                '<?php echo base_url("student/all_student"); ?>';
                        }, 1500);

                    } else {
                        showAlert("error", "Failed to update student.");
                    }
                },

                error: function(xhr) {
                    showAlert("error", "Server error occurred.");
                }
            });

        });

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true
        });


    });

    document.getElementById('select_all_exempted_fees')
        ?.addEventListener('change', function() {

            const checkboxes =
                document.querySelectorAll('.exempted-fee-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });

        });
</script> -->


<style>
    /* PAGE BACKGROUND */
    .content-wrapper {
        background: #f4f6f9;
        padding: 15px;
    }

    /* PAGE HEADER */
    .content-header {
        margin-bottom: 20px;
    }

    .content-header h1 {
        font-weight: 600;
        font-size: 22px;
        margin-bottom: 6px;
    }

    /* BREADCRUMB */
    .content-header .breadcrumb {
        float: none !important;
        position: relative;
        right: auto;
        top: auto;
        margin-top: 6px;
        padding-left: 0;
        background: transparent;
        font-size: 13px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .custom-breadcrumb>li+li:before {
        content: ">";
        padding: 0 6px;
        color: #999;
    }

    /* MAIN CARD */
    .erp-card {
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        border: none;
        margin-top: 15px;
    }

    /* SECTION CARD */
    .section-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        border-top: 3px solid #007bff;
    }

    /* SECTION TITLE */
    .section-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 18px;
        color: #2c3e50;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 8px;
        color: #007bff;
    }

    /* FORM */
    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        font-weight: 600;
        font-size: 12.5px;
        color: #555;
    }

    .form-control {
        border-radius: 6px;
        border: 1px solid #dcdcdc;
        height: 36px;
        font-size: 13px;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.12);
    }

    /* SUBJECT BOX */
    .subject-box {
        border: 1px solid #e5e5e5;
        padding: 12px;
        border-radius: 6px;
        max-height: 150px;
        overflow-y: auto;
        background: #fafafa;
    }

    /* PHOTO */
    .img-thumbnail {
        border-radius: 8px;
    }

    /* BUTTONS */
    .btn-primary,
    .btn-danger {
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 13px;
    }
</style>