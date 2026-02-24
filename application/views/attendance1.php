<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <div class="title-bar">
                <h3>Student Attendance</h3>
            </div>

            <!-- Default Message -->
            <div id="defaultMessage" class="default-message">
                <p>Please select a class and month to view attendance.</p>
            </div>

            <div class="container">
                <form id="attendanceForm">
                    <div class="filters">
                        <!-- <div class="filter-group">
                            <label for="class">Class <span style="color: red;">*</span></label>
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
                                usort($textClasses, fn($a, $b) => strcasecmp($a['class_name'], $b['class_name']));
                                usort($numericClasses, fn($a, $b) => $a['numeric_value'] <=> $b['numeric_value']);
                                $sortedClasses = array_merge($textClasses, $numericClasses);
                                foreach ($sortedClasses as $class) :
                                    $classSection = $class['class_name'] . " '" . $class['section'] . "'";
                                ?>
                                    <option value="<?= htmlspecialchars($classSection) ?>">
                                        <?= htmlspecialchars($classSection) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div> -->


                        <!-- Class Dropdown -->
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


                        <div class="filter-group">
                            <label for="selectMonth">Select Month <span style="color: red;">*</span></label>
                            <select id="selectMonth" required>
                                <option value="" disabled selected>Select Month</option>
                                <?php foreach (["April", "May", "June", "July", "August", "September", "October", "November", "December", "January", "February", "March"] as $month): ?>
                                    <option value="<?= $month ?>"><?= $month ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="searchbutton">Search For Attendance</label>


                            <button id="searchButton">Search</button>
                        </div>
                    </div>
                </form>

                <!-- Heading for Attendance -->
                <h3 id="attendanceHeading" class="attendance-heading" style="display: none;"></h3>

                <div id="attendanceContainer" style="display: none;">
                    <h3 id="tableTitle" class="text-center"></h3>
                    <div class="table-container">
                        <table id="attendance-table" class="display">
                            <thead id="tableHeader"></thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                </div>
                <!-- Color Code Indication -->
                <div class="legend">
                    <p><span class="legend-box present"></span> P - Present</p>
                    <p><span class="legend-box absent"></span> A - Absent</p>
                    <p><span class="legend-box leave"></span> L - Leave</p>
                    <p><span class="legend-box vacant"></span> V - Vacant</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal" id="attendanceModal">
    <div class="modal-content">
        <div class="modal-header">Student Attendance Details</div>
        <div class="modal-body" id="modalContent"></div>
        <button class="modal-close" id="closeModal">Close</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<script>
document.addEventListener("DOMContentLoaded", function () {

    const classDropdown = document.getElementById("class");
    const sectionDropdown = document.getElementById("section");
    const attendanceForm = document.getElementById("attendanceForm");

    /* =====================================================
       LOAD CLASSES ON PAGE LOAD
    ====================================================== */

    fetch("<?= base_url('student/get_classes') ?>")
        .then(response => response.json())
        .then(classes => {

            classDropdown.innerHTML =
                '<option value="" disabled selected>Select Class</option>';

            classes.forEach(className => {
                const option = document.createElement("option");
                option.value = className;
                option.textContent = className;
                classDropdown.appendChild(option);
            });

        })
        .catch(error => console.error("Error loading classes:", error));


    /* =====================================================
       LOAD SECTIONS WHEN CLASS CHANGES
    ====================================================== */

    classDropdown.addEventListener("change", function () {

        const selectedClass = this.value;

        sectionDropdown.innerHTML =
            '<option value="" disabled selected>Loading...</option>';

        fetch("<?= base_url('student/get_sections_by_class') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                class_name: selectedClass
            })
        })
        .then(response => response.json())
        .then(sections => {

            sectionDropdown.innerHTML =
                '<option value="" disabled selected>Select Section</option>';

            sections.forEach(section => {
                const option = document.createElement("option");
                option.value = section;
                option.textContent = section;
                sectionDropdown.appendChild(option);
            });

        })
        .catch(error => {
            console.error("Error loading sections:", error);
            sectionDropdown.innerHTML =
                '<option value="" disabled selected>Select Section</option>';
        });

    });


    /* =====================================================
       SUBMIT ATTENDANCE FORM
    ====================================================== */

    attendanceForm.addEventListener("submit", function (e) {

        e.preventDefault();

        const searchButton = document.getElementById("searchButton");
        const selectedClass = classDropdown.value;
        const selectedSection = sectionDropdown.value;
        const selectedMonth = document.getElementById("selectMonth").value;

        const defaultMessage = document.getElementById("defaultMessage");
        const tableContainer = document.getElementById("attendanceContainer");
        const tableHeader = document.getElementById("tableHeader");
        const tableBody = document.getElementById("tableBody");
        const tableTitle = document.getElementById("tableTitle");

        if (!selectedClass || !selectedSection || !selectedMonth) {
            alert("Please select Class, Section and Month.");
            return;
        }

        tableContainer.style.display = "none";
        defaultMessage.style.display = "none";
        tableTitle.innerHTML = "";

        if ($.fn.DataTable.isDataTable('#attendance-table')) {
            $('#attendance-table').DataTable().destroy();
        }

        tableHeader.innerHTML = "";
        tableBody.innerHTML = "";

        searchButton.textContent = "Searching...";
        searchButton.disabled = true;

        fetch("<?= base_url('student/fetchAttendance') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `class=${encodeURIComponent(selectedClass)}&section=${encodeURIComponent(selectedSection)}&month=${encodeURIComponent(selectedMonth)}`
        })
        .then(response => response.json())
        .then(data => {

            searchButton.textContent = "Search";
            searchButton.disabled = false;

            if (data.error) {
                alert(data.error);
                return;
            }

            tableContainer.style.display = "block";
            tableTitle.innerHTML =
                `Attendance for ${selectedClass} - ${selectedSection} in ${selectedMonth}`;

            const { students, daysInMonth, sundays } = data;

            const headerRow = document.createElement("tr");
            headerRow.innerHTML = `<th>User ID</th><th>Student Name</th>`;

            for (let i = 1; i <= daysInMonth; i++) {

                const th = document.createElement("th");
                th.innerHTML =
                    `${i}<br><span class="day-name">${
                        new Date(
                            data.year,
                            new Date(Date.parse(data.month + " 1, 2020")).getMonth(),
                            i
                        ).toLocaleDateString('en-US', { weekday: 'short' })
                    }</span>`;

                if (sundays.includes(i)) th.classList.add("sunday");

                headerRow.appendChild(th);
            }

            tableHeader.style.backgroundColor = "#006400";
            tableHeader.style.color = "#FFFFFF";
            tableHeader.appendChild(headerRow);

            students.forEach(student => {

                const row = document.createElement("tr");
                row.setAttribute("data-user-id", student.userId);
                row.setAttribute("data-student-name", student.name);

                row.innerHTML =
                    `<td>${student.userId}</td><td class="student-name">${student.name}</td>`;

                for (let i = 0; i < daysInMonth; i++) {

                    const td = document.createElement("td");
                    const status = student.attendance[i] || "V";

                    let statusClass = "vacant";
                    if (status === "P") statusClass = "present";
                    else if (status === "A") statusClass = "absent";
                    else if (status === "L") statusClass = "leave";

                    td.textContent = status;
                    td.classList.add(statusClass);

                    if (sundays.includes(i + 1)) td.classList.add("sunday");

                    row.appendChild(td);
                }

                tableBody.appendChild(row);
            });

            $('#attendance-table').DataTable({
                destroy: true,
                paging: true,
                searching: true,
                info: true,
                dom: 'Bfrtip'
            });

        })
        .catch(error => {
            console.error("Error fetching attendance:", error);
            searchButton.textContent = "Search";
            searchButton.disabled = false;
        });

    });


    /* =====================================================
       ROW CLICK + MODAL
    ====================================================== */

    $(document).on("click", "#attendance-table tbody tr", function () {
        $("#attendance-table tbody tr").removeClass("selected");
        $(this).addClass("selected");
    });

    $(document).on("dblclick", "#attendance-table tbody tr", function () {

        const studentName = $(this).attr("data-student-name");
        const attendanceData = $(this).find("td").map(function () {
            return $(this).text();
        }).get().slice(2);

        const present = attendanceData.filter(a => a === "P").length;
        const absent = attendanceData.filter(a => a === "A").length;
        const leave = attendanceData.filter(a => a === "L").length;

        $("#modalContent").html(`
            <p><strong>Name:</strong> ${studentName}</p>
            <p><strong>Total Present:</strong> ${present}</p>
            <p><strong>Total Absent:</strong> ${absent}</p>
            <p><strong>Total Leave:</strong> ${leave}</p>
        `);

        $("#modalOverlay, #attendanceModal").fadeIn();
    });

    $("#closeModal, #modalOverlay").on("click", function () {
        $("#modalOverlay, #attendanceModal").fadeOut();
        $("#attendance-table tbody tr").removeClass("selected");
                                    
    });

});
</script>





<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f7fa;
    }

    .header {
        background-color: #2c3e50;
        color: white;
        text-align: center;
        padding: 15px 0;
        font-size: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .title-bar {
        background-color: #007bff;
        color: white;
        margin-top: 15px;
        font-weight: bold;
        text-align: center;
        padding: 2px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .container {
        width: 97%;
        padding-right: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding-bottom: 20px;
        font-size: 16px;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9fafb;
    }

    .filters .filter-group {
        flex: 1 1 calc(33.33% - 20px);
        margin: 10px;
    }

    .filters label {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 8px;
        display: block;
        color: #333;
    }

    .filters select,
    .filters button {
        width: 100%;
        padding: 7px;
        font-size: 16px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .filters button {
        background-color: #3498db;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .filters button:hover {
        background-color: #2980b9;
    }



    /* Table Wrapper for Horizontal Scrolling */
    .table-container {
        overflow-x: auto;
        width: 100%;
    }

    /* Styling for the Attendance Table */
    .attendance-table {
        width: max-content;
        border-collapse: collapse;
        margin-top: 20px;
    }

    /* Table Header */
    .attendance-table th {
        background-color: #006400;
        /* Dark Green */
        color: white;
        font-weight: bold;
        text-align: center;
        border: 1px solid #ddd;
        padding: 12px;
        white-space: nowrap;
    }

    /* UserID & Name columns should have extra padding */
    .attendance-table td:first-child,
    .attendance-table td:nth-child(2) {
        padding: 15px;
        font-weight: bold;
        text-align: left;
    }

    /* Cell Borders */
    .attendance-table td {
        border: 1px solid #000;
        text-align: center;
        padding: 12px;
        font-size: 14px;
        min-width: 50px;
        white-space: nowrap;
    }

    /* Modal Overlay */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    /* Modal Box */
    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        padding: 20px;
    }

    /* Modal Content Centering */
    .modal-content {
        text-align: center;
    }

    /* Modal Header */
    .modal-header {
        font-size: 22px;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 15px;
    }

    /* Modal Body */
    .modal-body {
        font-size: 18px;
        font-weight: bold;
    }

    /* Close Button */
    .modal-close {
        margin-top: 15px;
        padding: 8px 20px;
        font-size: 16px;
        font-weight: bold;
        color: white;
        background: #dc3545;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .modal-close:hover {
        background: #c82333;
    }

    .default-message {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 20px;
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 10px;
        margin: 20px;
    }

    .selected {
        background-color: #b3e5fc !important;
    }




    .legend {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
        font-weight: bold;
    }

    .legend-box {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        /* Makes it circular */
        margin-right: 8px;
        border: 1px solid #000;
        /* Optional: Adds a thin border for better visibility */
    }

    .present {
        background: #00ff00;
    }

    /* Green */
    .absent {
        background: #ff0000;
    }

    /* Red */
    .leave {
        background: #ffff00;
    }

    /* Yellow */
    .vacant {
        background: #ffe0b2;
    }

    .attendance-table th {
        background-color: #006400;
        /* Dark Green */
        color: white;
        font-weight: bold;
        text-align: center;
        border: 1px solid #ddd;
        padding: 12px;
        white-space: nowrap;
    }

    /* Light Orange */

    @media print {
        body {
            background: white;
        }

        .header,
        .filters,
        .footer-buttons {
            display: none;
            /* Hide unnecessary elements */
        }

        .table-container {
            overflow: visible !important;
            /* Ensure the whole table prints */
        }

        .attendance-table {
            width: 100%;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid black;
            padding: 10px;
        }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .filters {
            flex-direction: column;
        }

        .filters .filter-group {
            flex: 1 1 100%;
            margin: 10px 0;
        }

        .footer-buttons {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .attendance-table th,
        .attendance-table td {
            font-size: 12px;
            padding: 8px;
        }
    }

    @media (max-width: 480px) {
        .header {
            font-size: 18px;
            padding: 10px 0;
        }

        .filters label,
        .footer-buttons .btn {
            font-size: 14px;
        }

        .attendance-table {
            font-size: 12px;
        }
    }
</style>