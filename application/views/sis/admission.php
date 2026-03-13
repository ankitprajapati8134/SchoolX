<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
html { font-size: 16px !important; }
.adm-wrap { max-width:860px; margin:0 auto; padding:24px 20px; }
.adm-card { background:var(--bg2); border:1px solid var(--border); border-radius:12px; padding:28px; }
.adm-card h2 { margin:0 0 24px; font-size:1.2rem; color:var(--t1); font-family:var(--font-b);
    display:flex; align-items:center; gap:10px; border-bottom:1px solid var(--border); padding-bottom:14px; }
.adm-card h2 i { color:var(--gold); }

.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px 20px; }
@media(max-width:600px){ .form-grid{grid-template-columns:1fr;} }
.form-grid .full { grid-column:1/-1; }

.fg { display:flex; flex-direction:column; gap:5px; }
.fg label { font-size:.86rem; color:var(--t3); font-family:var(--font-m); }
.fg input, .fg select, .fg textarea {
    padding:9px 12px; border:1px solid var(--border); border-radius:6px;
    background:var(--bg3); color:var(--t1); font-size:.88rem; }
.fg input:focus, .fg select:focus { outline:none; border-color:var(--gold); box-shadow:0 0 0 2px var(--gold-ring); }
.fg textarea { resize:vertical; min-height:70px; }

.form-section { font-size:.84rem; font-family:var(--font-m); color:var(--gold);
    text-transform:uppercase; letter-spacing:.06em; margin:20px 0 4px; grid-column:1/-1; }

.btn-row { display:flex; gap:12px; margin-top:24px; }
.btn-primary { padding:10px 24px; background:var(--gold); color:#fff; border:none;
    border-radius:7px; cursor:pointer; font-size:.9rem; font-family:var(--font-m); }
.btn-primary:hover { background:var(--gold2); }
.btn-secondary { padding:10px 20px; background:var(--bg3); color:var(--t2); border:1px solid var(--border);
    border-radius:7px; cursor:pointer; font-size:.9rem; }

.alert { padding:12px 16px; border-radius:7px; font-size:.88rem; display:none; margin-bottom:16px; }
.alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
</style>

<div class="content-wrapper">
<div class="adm-wrap">
    <div style="margin-bottom:16px;">
        <a href="<?= base_url('sis') ?>" style="color:var(--t3);font-size:.85rem;text-decoration:none;">
            <i class="fa fa-arrow-left"></i> SIS Dashboard
        </a>
    </div>

    <div class="adm-card">
        <h2><i class="fa fa-user-plus"></i> New Student Admission</h2>

        <div id="alertBox" class="alert"></div>

        <form id="admissionForm" enctype="multipart/form-data">
            <div class="form-grid">

                <!-- ── Student Information ─────────────────────────── -->
                <div class="form-section">Student Information</div>

                <div class="fg">
                    <label>Full Name *</label>
                    <input type="text" name="name" placeholder="Student full name" required>
                </div>
                <div class="fg">
                    <label>Student ID (auto-generated if blank)</label>
                    <input type="text" name="user_id" placeholder="e.g. STU0001">
                </div>
                <div class="fg">
                    <label>Date of Birth</label>
                    <input type="date" name="dob">
                </div>
                <div class="fg">
                    <label>Admission Date</label>
                    <input type="date" name="admission_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="fg">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select...</option>
                        <option>Male</option><option>Female</option><option>Other</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Category</label>
                    <select name="category">
                        <option value="">Select...</option>
                        <option>General</option><option>OBC</option><option>SC</option><option>ST</option><option>EWS</option><option>Other</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Blood Group</label>
                    <select name="blood_group">
                        <option value="">Select...</option>
                        <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Religion</label>
                    <input type="text" name="religion" placeholder="e.g. Hindu, Muslim, Christian">
                </div>
                <div class="fg">
                    <label>Nationality</label>
                    <input type="text" name="nationality" placeholder="e.g. Indian" value="Indian">
                </div>
                <div class="fg">
                    <label>Roll Number</label>
                    <input type="text" name="roll_no" placeholder="e.g. 01">
                </div>

                <!-- ── Contact ─────────────────────────────────────── -->
                <div class="form-section">Contact Information</div>

                <div class="fg">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" placeholder="e.g. +91-9XXXXXXXXX">
                </div>
                <div class="fg">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="student@email.com">
                </div>

                <!-- ── Family Information ──────────────────────────── -->
                <div class="form-section">Family Information</div>

                <div class="fg">
                    <label>Father's Name</label>
                    <input type="text" name="father_name" placeholder="Father's full name">
                </div>
                <div class="fg">
                    <label>Father's Occupation</label>
                    <input type="text" name="father_occupation" placeholder="e.g. Business, Service">
                </div>
                <div class="fg">
                    <label>Mother's Name</label>
                    <input type="text" name="mother_name" placeholder="Mother's full name">
                </div>
                <div class="fg">
                    <label>Mother's Occupation</label>
                    <input type="text" name="mother_occupation" placeholder="e.g. Homemaker, Teacher">
                </div>
                <div class="fg">
                    <label>Guardian Contact</label>
                    <input type="tel" name="guard_contact" placeholder="Guardian phone number">
                </div>
                <div class="fg">
                    <label>Guardian Relation</label>
                    <input type="text" name="guard_relation" placeholder="e.g. Uncle, Grandfather">
                </div>

                <!-- ── Address ─────────────────────────────────────── -->
                <div class="form-section">Address</div>

                <div class="fg full">
                    <label>Street</label>
                    <input type="text" name="street" placeholder="House No., Street, Locality">
                </div>
                <div class="fg">
                    <label>City</label>
                    <input type="text" name="city" placeholder="City / Town">
                </div>
                <div class="fg">
                    <label>State</label>
                    <input type="text" name="state" placeholder="State">
                </div>
                <div class="fg">
                    <label>Postal Code</label>
                    <input type="text" name="postal_code" placeholder="PIN Code">
                </div>

                <!-- ── Previous Education ──────────────────────────── -->
                <div class="form-section">Previous Education</div>

                <div class="fg">
                    <label>Previous Class</label>
                    <input type="text" name="pre_class" placeholder="e.g. 8th">
                </div>
                <div class="fg">
                    <label>Previous School</label>
                    <input type="text" name="pre_school" placeholder="School name">
                </div>
                <div class="fg">
                    <label>Previous Marks (%)</label>
                    <input type="text" name="pre_marks" placeholder="e.g. 85">
                </div>

                <!-- ── Academic Placement ──────────────────────────── -->
                <div class="form-section">Academic Placement</div>

                <div class="fg">
                    <label>Class *</label>
                    <select name="class" id="classSelect" required>
                        <option value="">Select Class...</option>
                        <?php foreach ($class_map as $ord => $secs): ?>
                        <option value="<?= htmlspecialchars($ord) ?>">Class <?= htmlspecialchars($ord) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg">
                    <label>Section *</label>
                    <select name="section" id="sectionSelect" required>
                        <option value="">Select Section...</option>
                    </select>
                </div>

                <!-- ── Photo & Documents ───────────────────────────── -->
                <div class="form-section">Photo &amp; Documents</div>

                <div class="fg">
                    <label>Student Photo</label>
                    <input type="file" name="student_photo" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="fg">
                    <label>Birth Certificate</label>
                    <input type="file" name="birthCertificate" accept="image/*,application/pdf">
                </div>
                <div class="fg">
                    <label>Aadhar Card</label>
                    <input type="file" name="aadharCard" accept="image/*,application/pdf">
                </div>
                <div class="fg">
                    <label>Transfer Certificate</label>
                    <input type="file" name="transferCertificate" accept="image/*,application/pdf">
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Save Admission</button>
                <button type="reset" class="btn-secondary">Clear</button>
                <a href="<?= base_url('sis/students') ?>" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Student List</a>
            </div>
        </form>
    </div>
</div>
</div>

<script>
var csrfName  = document.querySelector('meta[name="csrf-name"]').content;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const CLASS_MAP = <?= json_encode($class_map) ?>;

document.getElementById('classSelect').addEventListener('change', function () {
    const cls = this.value;
    const sec = document.getElementById('sectionSelect');
    sec.innerHTML = '<option value="">Select Section...</option>';
    if (cls && CLASS_MAP[cls]) {
        CLASS_MAP[cls].forEach(s => {
            sec.innerHTML += `<option value="${s}">Section ${s}</option>`;
        });
    }
});

document.getElementById('admissionForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn  = this.querySelector('button[type=submit]');
    const alert = document.getElementById('alertBox');
    btn.disabled = true; btn.textContent = 'Saving...';
    alert.style.display = 'none';

    const fd = new FormData(this);
    fd.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/save_admission') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save Admission';
        if (data.status === 'success') {
            alert.className = 'alert alert-success';
            alert.textContent = data.message + ' (ID: ' + (data.user_id || '') + ')';
            alert.style.display = 'block';
            document.getElementById('admissionForm').reset();
            document.getElementById('sectionSelect').innerHTML = '<option value="">Select Section...</option>';
        } else {
            alert.className = 'alert alert-error';
            alert.textContent = data.message || 'Error saving admission.';
            alert.style.display = 'block';
        }
    })
    .catch(() => {
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save Admission';
        alert.className = 'alert alert-error';
        alert.textContent = 'Network error. Please try again.';
        alert.style.display = 'block';
    });
});
</script>
