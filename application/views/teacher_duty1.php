<div class="content-wrapper">
    <div class="page_container">
        <div class="erp-card">

            <div class="title-bar">Assign/Update Teacher Duties</div>

            <br />
            <form id="duty-form">
                <div class="form-grid">
                    <div class="filter-group">
                        <label for="class">Class <span style="color:red">*</span></label>
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


                    <!-- Section Dropdown -->
                    <div class="filter-group">
                        <label for="section">Section <span style="color:red">*</span></label>
                        <select id="section" name="section" class="form-select" required>
                            <option value="" disabled selected>Select Section</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="subject">Subject<span style="color: red;">*</span></label>
                        <select id="subject" name="subject" class="form-select" required disabled>
                            <option value="" disabled selected>Select Subject</option>

                        </select>
                    </div>
                    <div class="form-group">
                        <label for="teacher-name">Teacher Name<span style="color: red;">*</span></label>
                        <select id="teacher-name" name="teacher-name" class="form-select" required>
                            <option value="" disabled selected>Select Teacher</option>

                        </select>
                    </div>
                    <div class="form-group">
                        <label for="duty-type">Duty Type<span style="color: red;">*</span></label>
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
                    <button type="button" class="btn btn-warning update" onclick="updateDuty()"
                        style="display: none;">Update</button>

                </div>
            </form>


            <div class="table-container">
                <table id="teacher-table" class="table table-striped example">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>

                            <th>Subject</th>
                            <th>Teacher Name</th>
                            <th>Duty Type</th>
                            <th>Duty Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($duties)) : ?>
                            <?php foreach ($duties as $duty) : ?>
                                <tr>
                                    <td><?php echo !empty($duty['class']) ? htmlspecialchars($duty['class']) : 'Unassigned'; ?>
                                    </td>
                                    <td><?php echo !empty($duty['section']) ? htmlspecialchars($duty['section']) : 'Unassigned'; ?>
                                    </td>
                                    <td><?php echo !empty($duty['subject']) ? htmlspecialchars($duty['subject']) : 'Unassigned'; ?>
                                    </td>
                                    <td><?php echo !empty($duty['teacher_name']) ? htmlspecialchars($duty['teacher_name']) : ''; ?>
                                    </td>
                                    <td><?php echo !empty($duty['duty_type']) ? htmlspecialchars($duty['duty_type']) : 'Unassigned'; ?>
                                    </td>
                                    <td><?php echo !empty($duty['duty_time']) ? htmlspecialchars($duty['duty_time']) : 'Unassigned'; ?>
                                    </td>

                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="fillForm(this)">Update Duty</button>
                                        <button class="btn btn-danger btn-sm" onclick="markInactive(this)">Mark
                                            Inactive</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">No duties assigned</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

        sectionSelect.addEventListener('change', function() {

            const selectedClass = classSelect.value;
            const selectedSection = this.value;

            if (!selectedClass || !selectedSection) {
                subjectSelect.innerHTML =
                    '<option value="" disabled selected>Select Subject</option>';
                subjectSelect.disabled = true;
                return;
            }

            subjectSelect.innerHTML =
                '<option value="" disabled selected>Loading...</option>';

            fetch('fetch_subjects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        class_name: selectedClass,
                        section: selectedSection
                    })
                })
                .then(response => response.json())
                .then(subjects => {

                    subjectSelect.innerHTML =
                        '<option value="" disabled selected>Select Subject</option>';

                    if (!subjects || subjects.length === 0) {
                        subjectSelect.innerHTML =
                            '<option value="" disabled selected>No Subjects Found</option>';
                        subjectSelect.disabled = true;
                        return;
                    }

                    subjects.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub;
                        option.textContent = sub;
                        subjectSelect.appendChild(option);
                    });

                    subjectSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading subjects:', error);
                    subjectSelect.innerHTML =
                        '<option value="" disabled selected>Select Subject</option>';
                    subjectSelect.disabled = true;
                });
        });

    });

    let selectedRow = null;


    function fillForm(button) {

        selectedRow = $(button).closest('tr');
        const data = selectedRow.find("td");

        let className = data.eq(0).text().trim();
        let section = data.eq(1).text().trim();
        let subject = data.eq(2).text().trim();
        let teacherName = data.eq(3).text().trim();
        let dutyType = data.eq(4).text().trim();
        let dutyTime = data.eq(5).text().trim();

        // 🔹 Set class dropdown
        $("#class").val(className);

        // 🔹 STEP 1: Load Sections
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

                // 🔹 STEP 2: Load ASSIGNED subjects from Teacher Duties
                // 🔹 STEP 2: Load FULL CLASS SUBJECT LIST (Master List)
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

                    let cleanSubject = subject.trim().toLowerCase();
                    let matched = false;

                    subjectSelect.find("option").each(function() {
                        if ($(this).val().trim().toLowerCase() === cleanSubject) {
                            $(this).prop("selected", true);
                            matched = true;
                        }
                    });

                    // if (!matched) {
                    //     console.warn("Subject not matched:", subject);
                    // }
                    if (!matched) {
                        showAlert("error", "Assigned subject not found in master subject list.");
                    }

                }
            })
            .catch(error => console.error("Update load error:", error));

        // 🔹 Other fields
        $("#teacher-name").val(teacherName);
        $("#duty-type").val(dutyType);

        let timeMatch = dutyTime.match(/(\d{1,2}:\d{2}\s*[APMapm]+)\s*-\s*(\d{1,2}:\d{2}\s*[APMapm]+)/);

        if (timeMatch) {
            $("#start-time").val(convertTo24HourFormat(timeMatch[1].trim()));
            $("#end-time").val(convertTo24HourFormat(timeMatch[2].trim()));
        } else {
            $("#start-time, #end-time").val('');
        }

        // $(".assign").hide();
        // $(".update").show();

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
                time_slot: timeSlot
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
   PAGE BACKGROUND
================================ */
.content-wrapper {
    background: #f4f6f9;
    padding: 30px;
    min-height: 100vh;
}

/* ===============================
   ERP MAIN CARD
================================ */
.erp-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

/* ===============================
   HEADER BAR
================================ */
.title-bar {
    background: #ffc107;
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    padding: 16px 24px;
    letter-spacing: 0.5px;
}

/* ===============================
   FORM SECTION
================================ */
#duty-form {
    padding: 25px;
    border-bottom: 1px solid #f0f0f0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
}

/* Labels */
.form-group label,
.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
}

/* Inputs */
.form-select,
.time-input {
    height: 44px;
    padding: 10px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fafbfc;
    font-size: 13px;
    transition: all 0.2s ease;
}

.form-select:focus,
.time-input:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    background: #fff;
    outline: none;
}

/* ===============================
   TIME SECTION
================================ */
.time-wrapper {
    grid-column: span 2;
}

.time-flex {
    display: flex;
    gap: 15px;
}

.time-flex div {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* ===============================
   BUTTONS
================================ */
.buttons {
    margin-top: 25px;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn {
    padding: 9px 22px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-success {
    background: #28a745;
    border: none;
}

.btn-success:hover {
    background: #218838;
}

.btn-warning {
    background: #ffc107;
    border: none;
    color: #000;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    border-radius: 6px;
}

/* ===============================
   TABLE SECTION
================================ */
.table-container {
    padding: 25px;
}

#teacher-table {
    width: 100%;
    border-collapse: collapse;
}

#teacher-table thead th {
    background: #ffc107;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 14px;
    text-align: left;
}

#teacher-table tbody tr {
    border-bottom: 1px solid #f2f2f2;
    transition: 0.2s ease;
}

#teacher-table tbody tr:hover {
    background: #fff8e1;
}

#teacher-table tbody td {
    padding: 14px;
    font-size: 13px;
    vertical-align: middle;
}

/* Action buttons inside table */
#teacher-table .btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* ===============================
   RESPONSIVE
================================ */
@media (max-width: 768px) {
    .time-wrapper {
        grid-column: span 1;
    }

    .time-flex {
        flex-direction: column;
    }
}

</style>

body {
font-family: Arial, sans-serif;
background-color: #f8f9fa;
margin: 0;
}

.container {
width: 98%;
padding: 10px;
border: 1px solid #ccc;
border-radius: 5px;
background-color: white;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
margin-top: 10px;
}

.title-bar {
background-color: #007bff;
color: white;
font-weight: bold;
text-align: center;
font-size: 24px;
padding: 10px;
border-radius: 5px;
margin-bottom: 20px;
}

.form-grid {
display: grid;
grid-template-columns: repeat(5, 1fr);
gap: 8px;
}

.form-group {
display: flex;
flex-direction: column;
}

.form-group label {
font-weight: bold;
margin-bottom: 8px;
}

.form-select {
height: 40px;
padding: 8px 12px;
border: 1px solid #ccc;
border-radius: 4px;
background-color: #f9f9f9;
transition: border-color 0.3s;
}

.form-select:focus {
border-color: #007bff;
outline: none;
box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

.buttons {
margin-top: 20px;
display: flex;
justify-content: flex-end;
gap: 20px;
}

.btn {
padding: 8px 16px;
font-size: 12px;
border-radius: 5px;
cursor: pointer;
transition: background-color 0.3s;
}

.table-container {
margin-top: 20px;
}

table th {
background-color: #006400;
color: white;
padding: 8px;
font-weight: bold;
}

table tbody tr {
border-bottom: 1px solid #ddd;
cursor: pointer;
}

table tbody tr:hover {
background-color: #b0b0b0 !important;
}

/* Ensure the time-group is in one row */
.time-group {
display: flex;
/* align-items: center; Vertically align items */
justify-content: space-between;
/* Evenly space out elements */
gap: 15px;
/* Space between labels and inputs */
flex-wrap: nowrap;
/* Prevent wrapping to a new line */
}

/* Optional: Style for input fields */
.time-group .time-input {
flex: 1;
/* Allows inputs to take equal space */
max-width: 150px;
/* Optional: limit input width */
}

.time-group label {
margin-right: 5px;
/* Adds space between label and input */
}
</style> -->