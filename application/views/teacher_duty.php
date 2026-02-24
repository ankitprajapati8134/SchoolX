<div class="content-wrapper">
    <div class="page_container">

        <div class="erp-card">

            <!-- ================= HEADER ================= -->
            <div class="erp-header">
                <div class="erp-title">
                    Assign / Update Teacher Duties
                    <span class="erp-subtitle">Manage Class & Subject Responsibilities</span>
                </div>
            </div>

            <!-- ================= FORM SECTION ================= -->
            <div class="erp-section">
                <div class="section-title">Duty Assignment Form</div>

                <form id="duty-form">
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="class">Class <span class="req">*</span></label>
                            <select id="class" name="class" class="form-select" required>
                                <option value="" disabled selected>Select Class</option>
                                <?php
                                $uniqueClasses = [];
                                foreach ($Classes as $class) {
                                    $uniqueClasses[$class['class_name']] = true;
                                }
                                ksort($uniqueClasses);
                                foreach ($uniqueClasses as $className => $_) :
                                ?>
                                    <option value="<?= htmlspecialchars($className) ?>">
                                        <?= htmlspecialchars($className) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="section">Section <span class="req">*</span></label>
                            <select id="section" name="section" class="form-select" required>
                                <option value="" disabled selected>Select Section</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject <span class="req">*</span></label>
                            <select id="subject" name="subject" class="form-select" required disabled>
                                <option value="" disabled selected>Select Subject</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="teacher-name">Teacher <span class="req">*</span></label>
                            <select id="teacher-name" name="teacher-name" class="form-select" required>
                                <option value="" disabled selected>Select Teacher</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="duty-type">Duty Type <span class="req">*</span></label>
                            <select id="duty-type" name="duty-type" class="form-select" required>
                                <option value="" disabled selected>Select Duty Type</option>
                                <option value="SubjectTeacher">Subject Teacher</option>
                                <option value="ClassTeacher">Class Teacher</option>
                            </select>
                        </div>

                        <div class="form-group time-wrapper">
                            <label>Duty Time</label>
                            <div class="time-flex">
                                <div>
                                    <label for="start-time">Start Time</label>
                                    <input type="time" id="start-time" name="start-time" class="time-input" />
                                </div>
                                <div>
                                    <label for="end-time">End Time</label>
                                    <input type="time" id="end-time" name="end-time" class="time-input" />
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="buttons">
                        <button type="button" class="btn btn-success assign" id="assign" disabled>Assign</button>
                        <button type="button" class="btn btn-warning update" onclick="updateDuty()" style="display:none;">Update</button>
                    </div>
                </form>
            </div>

            <!-- ================= TABLE SECTION ================= -->
            <div class="erp-section">
                <div class="section-title">Assigned Duties List</div>

                <div class="table-container">
                    <table id="teacher-table" class="table example">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Duty Type</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($duties)) : ?>
                                <?php foreach ($duties as $duty) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($duty['class'] ?? 'Unassigned') ?></td>
                                        <td><?= htmlspecialchars($duty['section'] ?? 'Unassigned') ?></td>
                                        <td><?= htmlspecialchars($duty['subject'] ?? 'Unassigned') ?></td>
                                        <td><?= htmlspecialchars($duty['teacher_name'] ?? '') ?></td>
                                        <td>
                                            <span class="badge-duty">
                                                <?= htmlspecialchars($duty['duty_type'] ?? 'Unassigned') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($duty['duty_time'] ?? 'Unassigned') ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" onclick="fillForm(this)">Update Duty</button>
                                            <button class="btn btn-danger btn-sm" onclick="markInactive(this)">Inactive Duty</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center">No duties assigned</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

        document.body.appendChild(alertBox);

        setTimeout(() => {
            alertBox.remove();
        }, 3000);
    }



    document.addEventListener('DOMContentLoaded', function() {

        const classSelect = document.getElementById('class');
        const sectionSelect = document.getElementById('section');
        const teacherSelect = document.getElementById('teacher-name');
        const subjectSelect = document.getElementById('subject');
        const dutyTypeSelect = document.getElementById('duty-type');

        const assignButton = document.querySelector(".assign");
        const updateButton = document.querySelector(".update");

        const teachers = <?php echo json_encode($teachers); ?>;

        /* ===========================
           LOAD TEACHERS
        =========================== */

        teacherSelect.innerHTML =
            '<option value="" disabled selected>Select Teacher</option>';

        teachers.forEach(teacher => {
            const option = document.createElement('option');
            option.value = teacher;
            option.textContent = teacher;
            teacherSelect.appendChild(option);
        });

        /* ===========================
           CLASS CHANGE → LOAD SECTIONS
        =========================== */

        classSelect.addEventListener('change', function() {

            const selectedClass = this.value;

            // Reset subject dropdown
            subjectSelect.innerHTML =
                '<option value="" disabled selected>Select Subject</option>';
            subjectSelect.disabled = true;

            if (!selectedClass) {
                sectionSelect.innerHTML =
                    '<option value="" disabled selected>Select Section</option>';
                return;
            }

            sectionSelect.innerHTML =
                '<option value="" disabled selected>Loading...</option>';

            fetch('get_sections_by_class', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        class_name: selectedClass
                    })
                })
                .then(response => response.json())
                .then(sections => {

                    sectionSelect.innerHTML =
                        '<option value="" disabled selected>Select Section</option>';

                    if (!sections || sections.length === 0) {
                        sectionSelect.innerHTML =
                            '<option value="" disabled selected>No Sections Found</option>';
                        return;
                    }

                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section;
                        option.textContent = section;
                        sectionSelect.appendChild(option);
                    });

                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                    sectionSelect.innerHTML =
                        '<option value="" disabled selected>Select Section</option>';
                });
        });

        /* ===========================
           SECTION CHANGE → LOAD SUBJECTS
        =========================== */

        if (!isUpdating) {
            subjectSelect.selectedIndex = 0;
        }
    });

    let selectedRow = null;

    let originalData = {};
    let isUpdating = false;

    function fillForm(button) {
        isUpdating = true;

        selectedRow = $(button).closest('tr');
        const data = selectedRow.find("td");

        let className = data.eq(0).text().trim();
        let section = data.eq(1).text().trim();
        let subject = data.eq(2).text().trim();
        let teacherName = data.eq(3).text().trim();
        let dutyType = data.eq(4).text().trim();
        let dutyTime = data.eq(5).text().trim();

        // ✅ STORE ORIGINAL DATA FOR UPDATE
        originalData = {
            class_name: className,
            section: section,
            subject: subject,
            teacher_name: teacherName,
            duty_type: dutyType
        };

        $("#class").val(className);

        fetch('get_sections_by_class', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    class_name: className
                })
            })
            .then(response => response.json())
            .then(sections => {

                let sectionSelect = $("#section");
                sectionSelect.empty().append('<option value="">Select Section</option>');

                if (sections && sections.length > 0) {
                    sections.forEach(sec => {
                        sectionSelect.append(new Option(sec, sec));
                    });
                    sectionSelect.val(section);
                }

                return fetch('fetch_subjects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        class_name: className,
                        section: section
                    })
                });

            })
            .then(response => response.json())
            .then(subjects => {

                let subjectSelect = $("#subject");
                subjectSelect.empty().append('<option value="">Select Subject</option>');

                if (subjects && subjects.length > 0) {

                    subjects.forEach(sub => {
                        subjectSelect.append(new Option(sub, sub));
                    });

                    subjectSelect.prop("disabled", false);

                    subjectSelect.val(subject);
                }
            })
            .catch(error => console.error("Update load error:", error));

        $("#teacher-name").val(teacherName);
        $("#duty-type").val(dutyType);

        let timeMatch = dutyTime.match(/(\d{1,2}:\d{2}\s*[APMapm]+)\s*-\s*(\d{1,2}:\d{2}\s*[APMapm]+)/);

        if (timeMatch) {
            $("#start-time").val(convertTo24HourFormat(timeMatch[1].trim()));
            $("#end-time").val(convertTo24HourFormat(timeMatch[2].trim()));
        } else {
            $("#start-time, #end-time").val('');
        }

        $(".assign").hide();
        $(".update").show().prop("disabled", false).text("Update");
    }


    function convertTo24HourFormat(timeStr) {
        let time = timeStr.match(/(\d{1,2}):(\d{2})\s*([APMapm]+)/);
        if (!time) {
            console.warn("Invalid time format:", timeStr);
            return ''; // Return an empty string if the format is incorrect
        }

        let hours = parseInt(time[1], 10);
        let minutes = time[2];
        let meridian = time[3].toUpperCase();

        if (meridian === "PM" && hours < 12) {
            hours += 12;
        } else if (meridian === "AM" && hours === 12) {
            hours = 0;
        }

        let convertedTime = `${hours.toString().padStart(2, '0')}:${minutes}`;
        console.log(`Converted Time (${timeStr} → 24H):`, convertedTime); // Debug log
        return convertedTime;
    }



    function updateDuty() {

        if (!selectedRow) {
            showAlert("error", "No duty selected.");
            return;
        }

        const btn = $(".update");
        btn.prop("disabled", true).text("Updating...");

        let classValue = $("#class").val();
        let subjectValue = $("#subject").val();
        let teacherName = $("#teacher-name").val();
        let dutyType = $("#duty-type").val();

        if (!classValue || !subjectValue || !teacherName || !dutyType) {
            showAlert("warning", "Please fill all required fields.");
            btn.prop("disabled", false).text("Update");
            return;
        }

        let startTime = $("#start-time").val();
        let endTime = $("#end-time").val();
        let timeSlot = (startTime && endTime) ?
            `${formatTime(startTime)}-${formatTime(endTime)}` :
            "";

        $.ajax({
            url: "assign_duty",
            type: "POST",
            dataType: "json",
            data: {
                class_name: classValue,
                section: $("#section").val(),
                subject: subjectValue,
                teacher_name: teacherName,
                duty_type: dutyType,
                time_slot: timeSlot,

                is_update: true,

                original_class: originalData.class_name,
                original_section: originalData.section,
                original_subject: originalData.subject,
                original_teacher: originalData.teacher_name,
                original_duty_type: originalData.duty_type
            },
            success: function(response) {

                if (response.status === "success") {
                    showAlert("success", "Duty updated successfully.");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert("error", response.message || "Update failed.");
                    btn.prop("disabled", false).text("Update");
                }
            },
            error: function() {
                showAlert("error", "Server error while updating.");
                btn.prop("disabled", false).text("Update");
            }
        });
    }


    function formatTime(time) {
        let [hours, minutes] = time.split(":");
        let ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12;
        return `${hours}:${minutes} ${ampm}`;
    }


    function resetForm() {

        document.getElementById('duty-form').reset();

        $("#subject").prop("disabled", true);

        $(".update").hide();
        $(".assign")
            .show()
            .prop("disabled", true)
            .text("Assign");

        selectedRow = null;
    }



    function markInactive(button) {

        if (!confirm("Are you sure you want to mark this duty as inactive?")) {
            return;
        }

        let row = button.closest('tr');

        let className = row.cells[0].textContent.trim();
        let section = row.cells[1].textContent.trim();
        let subject = row.cells[2].textContent.trim();
        let teacherName = row.cells[3].textContent.trim();

        $.ajax({
            url: 'markInactive_duty',
            type: 'POST',
            dataType: 'json',
            data: {
                class_name: className,
                section: section,
                subject: subject,
                teacher_name: teacherName
            },
            success: function(response) {

                if (response.status === "success") {
                    showAlert("success", "Duty marked inactive.");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert("error", response.message || "Failed to mark inactive.");
                }
            },
            error: function() {
                showAlert("error", "Server error while marking inactive.");
            }
        });
    }


    $(document).ready(function() {
        function checkFormFields() {
            let classValue = $("#class").val();
            let sectionValue = $("#section").val();
            let subjectValue = $("#subject").val();
            let teacherName = $("#teacher-name").val();
            let dutyType = $("#duty-type").val();

            if (classValue && subjectValue && teacherName && dutyType) {
                $("#assign").prop("disabled", false);
            } else {
                $("#assign").prop("disabled", true);
            }
        }

        $("#class, #section, #subject, #teacher-name, #duty-type").on("change", checkFormFields);

        $("#assign").click(function() {

            let classValue = $("#class").val();
            let subjectValue = $("#subject").val();
            let teacherName = $("#teacher-name").val();
            let dutyType = $("#duty-type").val();

            if (!classValue || !subjectValue || !teacherName || !dutyType) {
                showAlert("warning", "Please fill required fields");
                return;
            }

            const btn = $(this);
            btn.prop("disabled", true).text("Assigning...");

            let startTime = $("#start-time").val();
            let endTime = $("#end-time").val();

            let timeSlot = (startTime && endTime) ?
                `${formatTime(startTime)}-${formatTime(endTime)}` :
                "";

            $.ajax({
                url: "assign_duty",
                type: "POST",
                dataType: "json",
                data: {
                    class_name: classValue,
                    section: $("#section").val(),
                    subject: subjectValue,
                    teacher_name: teacherName,
                    duty_type: dutyType,
                    time_slot: timeSlot
                },
                success: function(response) {

                    if (response.status === "success") {

                        showAlert("success", "Duty assigned successfully");
                        resetForm();
                        setTimeout(() => location.reload(), 1500);

                    } else {

                        showAlert("error", response.message || "Something went wrong");
                        btn.prop("disabled", false).text("Assign");
                    }
                },
                error: function() {

                    showAlert("error", "Something went wrong");
                    btn.prop("disabled", false).text("Assign");
                }
            });
        });



        function formatTime(time) {
            let [hours, minutes] = time.split(":");
            let ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;
            return `${hours}:${minutes} ${ampm}`;
        }
    });
</script>



<style>
    /* ===============================
   GLOBAL LAYOUT
================================ */
    .content-wrapper {
        background: #f4f6f9;
        padding: 35px;
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    /* ===============================
   MAIN ERP CARD
================================ */
    .erp-card {
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #f1f1f1;
    }

    /* ===============================
   HEADER
================================ */
    .erp-header {
        background: linear-gradient(90deg, #ffc107, #ffb300);
        padding: 22px 35px;
        color: #fff;
    }

    .erp-title {
        font-size: 22px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .erp-subtitle {
        font-size: 13px;
        opacity: 0.9;
        margin-top: 5px;
    }

    /* ===============================
   SECTION
================================ */
    .erp-section {
        padding: 30px 35px;
        border-bottom: 1px solid #f2f2f2;
    }

    .section-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 22px;
        color: #222;
    }

    /* ===============================
   FORM GRID
================================ */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
        color: #333;
    }

    .req {
        color: #e53935;
    }

    /* ===============================
   INPUTS
================================ */
    .form-select,
    .time-input {
        height: 48px;
        padding: 0 16px;
        border: 1px solid #e4e6eb;
        border-radius: 12px;
        background: #fcfcfd;
        font-size: 13px;
        transition: all 0.25s ease;
    }

    .form-select:hover,
    .time-input:hover {
        border-color: #ffc107;
    }

    .form-select:focus,
    .time-input:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.15);
        background: #fff;
        outline: none;
    }

    /* Disabled */
    .form-select:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* ===============================
   DUTY TIME BLOCK
================================ */
    .time-wrapper {
        grid-column: span 2;
        background: #fafafa;
        padding: 22px;
        border-radius: 14px;
        border: 1px solid #ececec;
    }

    .time-flex {
        display: flex;
        gap: 25px;
    }

    .time-flex div {
        flex: 1;
    }

    /* ===============================
   BUTTONS
================================ */
    .buttons {
        margin-top: 35px;
        display: flex;
        justify-content: flex-end;
        gap: 18px;
    }

    .btn {
        font-size: 13px;
        font-weight: 600;
        border-radius: 30px;
        padding: 10px 30px;
        transition: all 0.25s ease;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #218838);
        border: none;
        color: #fff;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.25);
    }

    .btn-success:hover {
        transform: translateY(-2px);
    }

    .btn-warning {
        background: #ffc107;
        border: none;
        color: #000;
    }

    .btn-danger {
        background: #e74c3c;
        border: none;
        color: #fff;
    }

    /* ===============================
   TABLE
================================ */
    .table-container {
        margin-top: 10px;
    }

    #teacher-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    #teacher-table thead th {
        background: #ffc107;
        color: #fff;
        padding: 16px;
        font-size: 13px;
        font-weight: 600;
        text-align: left;
    }

    #teacher-table tbody tr {
        transition: all 0.2s ease;
    }

    #teacher-table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #teacher-table tbody tr:hover {
        background: #fff8e1;
    }

    #teacher-table tbody td {
        padding: 16px;
        font-size: 13px;
        color: #333;
        border-bottom: 1px solid #f2f2f2;
    }

    /* Table Buttons */
    #teacher-table .btn-warning,
    #teacher-table .btn-danger {
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 12px;
        font-weight: 500;
    }

    #teacher-table .btn-warning:hover,
    #teacher-table .btn-danger:hover {
        opacity: 0.9;
    }

    /* Badge */
    .badge-duty {
        background: rgba(255, 193, 7, 0.18);
        color: #000;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
</style>