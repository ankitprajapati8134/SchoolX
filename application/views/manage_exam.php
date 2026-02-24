<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <div class="title-bar">Add Exam</div>
            <div class="add-exam">

                <form action="<?php echo base_url() . 'exam/manage_exam' ?>" method="post" id="add-exam-form">
                    <!-- Exam Information -->
                    <div class="form-section">
                        <h3>Exam Information</h3>
                        <div class="form-grid">
                            <div>
                                <label for="examName">Exam Name:</label>
                                <input type="text" name="examName" id="examName" placeholder="Enter exam name"
                                    required />
                            </div>
                            <div>
                                <label for="gradingScale">Grading Scale:</label>
                                <select id="gradingScale" name="gradingScale">
                                    <option value="A+ to F">A+ to F</option>
                                    <option value="Percentage">Percentage</option>
                                </select>
                            </div>
                            <div>
                                <label for="startDate">Start Date:</label>
                                <input type="date" name="startDate" id="startDate" required />
                            </div>
                            <div>
                                <label for="endDate">End Date:</label>
                                <input type="date" name="endDate" id="endDate" required />
                            </div>

                        </div>
                    </div>

                    <!-- Dynamic Table Section -->
                    <div id="scheduleSection" class="form-section">
                        <h3>Exam Schedule</h3>
                        <p>Fill in exam details after entering Exam Information.</p>
                    </div>

                    <input type="hidden" id="examScheduleInput" name="examSchedule">


                    <div class="form-section">
                        <h3>General Instructions</h3>
                        <textarea name="generalInstructions" id="generalInstructions" rows="5"
                            placeholder="Enter each instruction on a new line..."></textarea>
                    </div>

                    <!-- <div class="form-actions"> -->
                    <div class="form-section">
                        <button class="btn btn-success btn-lg" type="submit">Save Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Class Modal -->
<div id="selectionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Select an Option</h3>
        <div id="modalOptions"></div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
            const startDateInput = document.getElementById("startDate");
            const endDateInput = document.getElementById("endDate");
            const scheduleSection = document.getElementById("scheduleSection");
            const totalmarks = document.getElementById("totalmarks");

            const classList = <?php echo json_encode($classNames ?? []); ?>;
            const subjectsList = <?php echo json_encode($subjects ?? []); ?>;

            let selectedClass = '';
            let modalCallback = null;

            let selectionModal = document.getElementById("selectionModal");
            const modalTitle = document.getElementById("modalTitle");
            const modalOptions = document.getElementById("modalOptions");
            const textarea = document.getElementById("generalInstructions");




            endDateInput.addEventListener("change", () => {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (startDate && endDate && startDate <= endDate) {
                    generateScheduleTable(startDate, endDate);
                }
            });

            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }

            function generateScheduleTable(startDate, endDate) {
                scheduleSection.innerHTML = "<h3>Exam Schedule</h3>";
                let currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    const formattedDate = formatDate(currentDate);
                    const section = document.createElement("div");
                    section.classList.add("date-section");
                    section.innerHTML = `
                <h4>${formattedDate}</h4>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Time</th>
                            <th>Total Marks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${generateRow(formattedDate)}
                    </tbody>
                </table>
            `;
                    scheduleSection.appendChild(section);
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            }


            function generateRow(date) {
                return `
                <tr>
                    <td class="text-center"><strong>${date}</strong></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="openModal('Class', this)">Select Class</button>
                        <span class="selected-class" data-class="" style="margin-left: 10px;"></span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="openModal('Subject', this)" disabled>Select Subject</button>
                        <span class="selected-subject" data-subject="" style="margin-left: 10px;"></span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; width: 100%;">
                            <label>Start Time:</label>
                            <input type="time" class="start-time-input" required />
                            <label>End Time:</label>
                            <input type="time" class="end-time-input" required />
                        </div>

                    </td>
                    <td>
                        <input type="number" class="marks-input" value="100" placeholder="Total Marks" required />
                    </td>
                    <td>
                        <button type="button" class="btn btn-success" onclick="addRow(this)">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="removeRow(this)">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            }

            // Attach openModal to the window object for global access
            window.openModal = function(type, element) {
                modalCallback = function(selectedValue) {
                    if (type === "Class") {
                        let classSpan = element.nextElementSibling;
                        classSpan.textContent = selectedValue;
                        classSpan.setAttribute("data-class", selectedValue);
                        classSpan.classList.add("selected-highlight");

                        // Reset subject when class is changed
                        let row = element.closest("tr");
                        let subjectButton = row.querySelector("td:nth-child(3) button");
                        let subjectSpan = row.querySelector("td:nth-child(3) .selected-subject");


                        if (subjectButton) {
                            subjectButton.textContent = "Select Subject";
                            subjectButton.removeAttribute("data-value");
                            subjectButton.classList.remove("selected-highlight");
                            subjectButton.disabled = false; // Enable subject button after class selection
                        }

                        if (subjectSpan) {
                            subjectSpan.textContent = "";
                            subjectSpan.removeAttribute("data-subject");
                            subjectSpan.classList.remove("selected-highlight");
                        }

                    } else if (type === "Subject") {

                        let subjectSpan = element.nextElementSibling;
                        subjectSpan.textContent = selectedValue;
                        subjectSpan.setAttribute("data-subject", selectedValue);
                        subjectSpan.classList.add("selected-highlight");

                        // Also update the button text for clarity
                        // element.textContent = selectedValue;
                        element.textContent = "Change Subject";
                        element.setAttribute("data-value", selectedValue);
                        element.classList.add("selected-highlight");
                    }
                };

                modalTitle.textContent = `Select ${type}`;
                modalOptions.innerHTML = getOptions(type, element);
                selectionModal.style.display = "block";

                // Close modal when clicking outside
                document.addEventListener("click", closeModalOnOutside);
            };


            // Generate Options for Class and Subject
            function getOptions(type, element) {
                if (type === "Class") {
                    return classList.length ?
                        classList.map(cls => `<button type ="button" onclick ="selectOption('${cls}', '${type}', event)" >${cls}</button>`).join("")
                        : "<p>No classes available</p>";
                        }
                    else if (type === "Subject") {
                        let row = element.closest("tr");
                        let classSpan = row.querySelector(".selected-class");

                        if (!classSpan || !classSpan.getAttribute("data-class")) {
                            return "<p>Please select a class first.</p>";
                        }

                        let className = classSpan.getAttribute("data-class");

                        // Check if subjects are available for the selected class
                        if (!subjectsList[className] || Object.keys(subjectsList[className]).length === 0) {
                            return "<p>No subjects available.</p>";
                        }


                        return Object.keys(subjectsList[className])
                        .map(sub => `<button type ="button" onclick ="selectOption('${sub}', '${type}', event)" >${sub}</button>`).join("");
                        }
                    }

                    // Handle Option Selection
                    window.selectOption = function(selectedValue, type, event) {
                        if (modalCallback) modalCallback(selectedValue);
                        selectionModal.style.display = "none";
                        event.stopPropagation();
                        document.removeEventListener("click", closeModalOnOutside);
                    };



                    function closeModalOnOutside(event) {
                        if (!selectionModal.contains(event.target) && !event.target.closest(".btn-primary")) {
                            window.closeModal();
                        }
                    }


                    // Close Modal on Close Button Click
                    window.closeModal = function() {
                        selectionModal.style.display = "none";
                        modalCallback = null;

                        document.removeEventListener("click", closeModalOnOutside);
                    };




                    textarea.addEventListener("input", function() {
                        // If the user starts typing and it's the first character, add a bullet point
                        if (textarea.value.length === 1 && textarea.value !== "•") {
                            textarea.value = "• " + textarea.value;
                        }
                    });

                    textarea.addEventListener("keydown", function(event) {
                        if (event.key === "Enter") {
                            event.preventDefault(); // Prevents new line default behavior

                            // Get current text
                            let cursorPos = textarea.selectionStart;
                            let textBeforeCursor = textarea.value.substring(0, cursorPos);
                            let textAfterCursor = textarea.value.substring(cursorPos);

                            // Insert a new bullet point at the next line
                            textarea.value = textBeforeCursor + "\n• " + textAfterCursor;

                            // Move cursor to correct position
                            textarea.selectionStart = textarea.selectionEnd = cursorPos + 3;
                        }
                    });


                    window.addRow = function(button) {
                        const tableBody = button.closest("tbody");
                        const row = generateRow(button.closest("tr").querySelector("td").textContent);
                        tableBody.insertAdjacentHTML("beforeend", row);
                    };

                    window.removeRow = function(button) {
                        const row = button.closest("tr");
                        row.parentElement.removeChild(row);
                    };
                });





            $(document).ready(function() {
                $("#add-exam-form").on("submit", function(e) {
                    e.preventDefault(); // Prevent default form submission

                    const scheduleData = [];

                    // Iterate over each table row to collect schedule data
                    $(".schedule-table tbody tr").each(function() {
                        const row = $(this);

                        const date = row.find("td:first").text().trim();
                        const className = row.find(".selected-class").attr("data-class");
                        const subject = row.find(".selected-subject").attr("data-subject");
                        const startTime = row.find(".start-time-input").val();
                        const endTime = row.find(".end-time-input").val();
                        const totalMarks = row.find(".marks-input").val();

                        // Trim the values and validate
                        if (!className.trim() || !subject.trim() || !startTime.trim() || !
                            endTime.trim() || !totalMarks.trim()) {
                            alert("Please fill in all schedule fields correctly.");
                            return false; // Exit the loop if any field is missing
                        }


                        // Append data to the schedule array
                        scheduleData.push({
                            date: date,
                            className: className,
                            subject: subject,
                            time: `${startTime} - ${endTime}`,
                            totalMarks: totalMarks
                        });
                    });

                    // If no schedule data, show alert and stop submission
                    if (scheduleData.length === 0) {
                        alert("Please add at least one schedule.");
                        return;
                    }

                    // Add the JSON string to the hidden input
                    $("#examScheduleInput").val(JSON.stringify(scheduleData));

                    // Submit the form via AJAX
                    $.ajax({
                        url: $(this).attr("action"),
                        method: $(this).attr("method"),
                        data: $(this).serialize(),
                        dataType: "json",
                        success: function(response) {
                            if (response.status === "success") {
                                alert("Exam saved successfully!");
                                location.reload();
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", error);
                            alert("An error occurred. Please try again.");
                        }
                    });
                });
            });
</script>



<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
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
    margin-top: 20px;
    font-weight: bold;
    text-align: center;
    font-size: 24px;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.add-exam {
    max-width: 1020px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: white;
}

.date-section {
    margin-bottom: 20px;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
}

.schedule-table th,
.schedule-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.schedule-table th {
    background-color: #f1f1f1;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 300px;
    background: rgba(255, 255, 255, 1);
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 20px;
    border-radius: 5px;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 339px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 20px;
    cursor: pointer;
}

input,
select,
textarea {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.selected-highlight {
    font-weight: bold;

    border-bottom: 2px solid #007bff;
    padding: 6px;
}

button {
    cursor: pointer;
}

#preview {
    background-color: #6c757d;
}

h3 {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 18px;
}

.time-input {
    width: 45%;
    padding: 5px;
    margin: 5px 2%;
    box-sizing: border-box;
}

/* Time Modal */
#timeModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

#timeModal .modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 300px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

#timeModal .btn {
    margin-top: 10px;
}

/* Form Sections */
.form-section {
    max-width: 910px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-grid div {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 8px;
}

input,
select {
    padding: 8px;
    font-size: 16px;
}

/* Modal Adjustments */
.modal {
    width: 90%;
    max-width: 400px;
    max-height: 70vh;
    overflow-y: auto;
}

#modalOptions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    max-height: 50vh;
    overflow-y: auto;
}

#modalOptions button {
    width: 45%;
    padding: 10px;
    border: 1px solid #ccc;
    background: #f8f9fa;
    border-radius: 5px;
    cursor: pointer;
}

#modalOptions button:hover {
    background: #007bff;
    color: white;
}
</style>