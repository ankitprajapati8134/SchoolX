<div class="content-wrapper">
    <div class="page_container">
        <div class="card" id="teacherProfileContainer">
            <!-- Header Section -->
            <div class="header text-center bg-primary text-white">
                <h2><i class="fa fa-user"></i> Teacher Profile</h2>
            </div>

            <!-- <div class="search-section my-3">
                <div class="row align-content-center">
                    <div class="col-md-5"></div>
                    <div class="col-md-2 justify-content-end align-items-end">
                        <button type="button" class="btn btn-primary" id="searchdetails" data-toggle="modal"
                            data-target="#searchModal">
                            <i class="fa fa-search" style="font-size: 16px;"></i>
                            <br>
                            <span>Search teacher</span>
                        </button>
                    </div>
                    <div class="col-md-5"></div>
                </div>
            </div>

            <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-center">
                            <h3 class="modal-title" id="studentModalLabel">Search teacher By <br> Teacher ID / Teacher
                                Name
                            </h3>
                        </div>
                        <div class="modal-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <form method="post" id="searchForm" action="#" class="d-flex flex-column">
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
                                        <input type="text" class="form-control mb-3" id="search_name" name="search_name"
                                            placeholder="Search Teacher">
                                        <button class="btn btn-primary" id="name" type="submit">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="searchTable">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Sr. No</th>
                                            <th>Teacher Id</th>
                                            <th>Teacher Name</th>
                                            <th>Father Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="searchResultsTable">
                                        <tr>
                                            <td colspan="6" class="text-center">No results found.</td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div> -->


            <div id="showinfo">
                <div>
                    <ul class="nav nav-tabs details-container" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="personal-details-tab" data-bs-toggle="tab"
                                href="#personal-details" role="tab" aria-controls="personal-details"
                                aria-selected="true">Personal Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="professional-details-tab" data-bs-toggle="tab"
                                href="#professional-details" role="tab" aria-controls="guardian-details"
                                aria-selected="false">Professional Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="salary-details-tab" data-bs-toggle="tab" href="#salary-details"
                                role="tab" aria-controls="address-details" aria-selected="false">Salary Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="emergency-datails-tab" data-bs-toggle="tab" href="#emergency-school"
                                role="tab" aria-controls="previous-school" aria-selected="false">Emergency Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="bank-details-tab" data-bs-toggle="tab" href="#bank-details"
                                role="tab" aria-controls="bank-details" aria-selected="false">Bank Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="qualification and experience-details-tab" data-bs-toggle="tab"
                                href="#qualification-details" role="tab" aria-controls="document-details"
                                aria-selected="false">Qualification Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="doc-details-tab" data-bs-toggle="tab"
                                href="#doc-details" role="tab" aria-controls="doc-details"
                                aria-selected="false">Documents</a>
                        </li>
                    </ul>
                </div>



                <div class="row" style="margin-right: -15px; margin-left: 0px;">
                    <div class="profile col-md-4 text-center mb-3 mt-3">
                        <img src="<?= $teacher['Doc']['ProfilePic'] ?>" alt="ProfilePic"
                            onerror="this.src='http://localhost/Grader/school/tools/image/default-school.jpeg';"
                            style="width: 250px; height: 250px; object-fit: contain; border-radius: 10%;">

                        <h3><strong>Name: </strong><?= isset($teacher['Name']) ? $teacher['Name'] : 'Unknown'; ?></h3>
                        <h3><strong>Teacher Id: </strong> <?= $teacher['User ID'] ?? 'N/A'; ?></h3>
                        <br>

                        <!-- <p>

                            Hi! I’m <?= isset($teacher['Name']) ? $teacher['Name'] : 'Unknown'; ?>, a teacher at
                            <strong><?= $teacher['School Name'] ?? 'N/A'; ?></strong>, Grade <?= $class ?? 'N/A'; ?> ,
                            Section <?= $section ?? 'N/A'; ?>.
                            I’m passionate about <strong>math and science</strong> and enjoy participating in
                            <strong>science fairs</strong>.
                            My hobbies include <strong>painting, reading novels, and coding small projects</strong>.

                        </p> -->
                        <!-- <div>
                            <div class="about-me-header">
                                <div class="buttons">
                                    <button class="btn btn-info" onclick="window.print()">
                                        <i class="fa fa-print"></i>
                                    </button>
                                    <button class="btn btn-success" onclick="downloadProfile()">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <div class="col-md-8">
                        <div class="details-container">
                            <div class="tab-content" id="profileTabsContent">
                                <div class="tab-pane show active" id="personal-details" role="tabpanel"
                                    aria-labelledby="personal-details-tab">
                                    <h4 class="text-center">Personal Details</h4><br>
                                    <p><strong>Name:</strong> <?= $teacher['Name'] ?? 'N/A'; ?></p>
                                    <p><strong>Father's Name:</strong> <?= $teacher['Father Name'] ?? 'N/A'; ?></p>
                                    <p><strong>Gender:</strong> <?= $teacher['Gender'] ?? 'N/A'; ?></p>
                                    <p><strong>Date of Birth:</strong> <?= $teacher['DOB'] ?? 'N/A'; ?></p>
                                    <p><strong>Email:</strong> <?= $teacher['Email'] ?? 'N/A'; ?></p>
                                    <p><strong>Phone Number:</strong> <?= $teacher['Phone Number'] ?? 'N/A'; ?></p>
                                    <p><strong>Category:</strong> <?= $teacher['Category'] ?? 'N/A'; ?></p>


                                </div>

                                <div class="tab-pane " id="professional-details" role="tabpanel"
                                    aria-labelledby="professional-details-tab">
                                    <h4 class="text-center">Professional Details</h4><br>
                                    <p><strong>Position:</strong> <?= $teacher['Position'] ?? 'N/A'; ?></p>
                                    <p><strong>Department:</strong> <?= $teacher['Department'] ?? 'N/A'; ?></p>
                                    <p><strong>Date Of Joining:</strong> <?= $teacher['Date Of Joining'] ?? 'N/A'; ?>
                                    </p>
                                    <p><strong>Employement Type:</strong> <?= $teacher['Employment Type'] ?? 'N/A'; ?>
                                    </p>
                                </div>

                                <div class="tab-pane" id="salary-details" role="tabpanel"
                                    aria-labelledby="salary-details-tab">
                                    <h4 class="text-center">Salary Details</h4><br>

                                    <p><strong>Basic Salary:</strong>
                                        ₹ <?= isset($teacher['salaryDetails']['basicSalary']) 
                                                ? number_format($teacher['salaryDetails']['basicSalary'], 2, '.', ',') 
                                                : 'N/A'; ?>
                                    </p>

                                    <p><strong>Allowances:</strong>
                                        ₹ <?= isset($teacher['salaryDetails']['Allowances']) 
                                                ? number_format($teacher['salaryDetails']['Allowances'], 2, '.', ',') 
                                                : 'N/A'; ?>
                                    </p>

                                    <!-- Deduction Calculation
                                        <p><strong>Deduction:</strong>
                                            <?php 
                                            $pf = $teacher['salaryDetails']['deductions']['PF'] ?? 0;
                                            $professionalTax = $teacher['salaryDetails']['deductions']['ProfessionalTax'] ?? 0;
                                            $deductionsTotal = $pf + $professionalTax;
                                            echo $deductionsTotal > 0 ? '₹' . number_format($deductionsTotal, 2, '.', ',') : 'N/A';
                                            ?>
                                        </p> -->

                                    <p><strong>Net Salary:</strong>
                                        ₹ <?= isset($teacher['salaryDetails']['Net Salary']) 
                                                ? number_format($teacher['salaryDetails']['Net Salary'], 2, '.', ',') 
                                                : 'N/A'; ?>
                                    </p>
                                </div>



                                <div class="tab-pane" id="emergency-school" role="tabpanel"
                                    aria-labelledby="emergency-datails-tab">
                                    <h4 class="text-center">Emergency Details</h4><br>

                                    <p><strong>Name:</strong> <?= $teacher['emergencyContact']['name'] ?? 'N/A'; ?></p>
                                    <p><strong>Phone:</strong>
                                        <?= $teacher['emergencyContact']['phoneNumber'] ?? 'N/A'; ?></p>
                                </div>
                                <div class="tab-pane" id="bank-details" role="tabpanel"
                                    aria-labelledby="bank-details-tab">
                                    <h4 class="text-center">Bank Details</h4><br>
                                    <p><strong>Bank Name:</strong> <?= $teacher['bankDetails']['bankName'] ?? 'N/A'; ?>
                                    </p>
                                    <p><strong>IFSC Code:</strong> <?= $teacher['bankDetails']['ifscCode'] ?? 'N/A'; ?>
                                    </p>

                                    <p><strong>Bank Account Number:</strong>
                                        <?= $teacher['bankDetails']['accountNumber'] ?? 'N/A'; ?></p>

                                    <p><strong>Account Holder Name:</strong>
                                        <?= $teacher['bankDetails']['accountHolderName'] ?? 'N/A'; ?></p>

                                </div>


                                <div class="tab-pane" id="qualification-details" role="tabpanel"
                                    aria-labelledby="qualification and experience-details">

                                    <h4 class="text-center">Qualification Details</h4><br>

                                    <p><strong>Highest Qualification:</strong>
                                        <?= $teacher['qualificationDetails']['highestQualification'] ?? 'N/A'; ?></p>
                                    <p><strong>University:</strong>
                                        <?= $teacher['qualificationDetails']['university'] ?? 'N/A'; ?></p>
                                    <p><strong>Year Of Passing:</strong>
                                        <?= $teacher['qualificationDetails']['yearOfPassing'] ?? 'N/A'; ?></p>
                                    <p><strong>Experience:</strong>
                                        <?= $teacher['qualificationDetails']['experience']?? 'N/A'; ?></p>

                                </div>

                                <div class="tab-pane" id="medical-history" role="tabpanel"
                                    aria-labelledby="medical-history-tab">
                                    <h4 class="text-center">Medical Details</h4><br>

                                    <p><strong>Medical History:</strong> <?= $student['Med Hist'] ?? 'N/A'; ?></p>
                                </div>
                                <div class="tab-pane" id="doc-details" role="tabpanel"
                                    aria-labelledby="doc-details-tab">
                                    <ul>
                                        <h4 class="text-center">Documents</h4><br>

                                        <?php if (!empty($teacher['Doc'])): ?>
                                        <?php foreach ($teacher['Doc'] as $docName => $docUrl): ?>
                                        <p>
                                            <strong><?= htmlspecialchars($docName) ?>:</strong>
                                           
                                        <a href="<?= base_url('student/download_document?file=') . urlencode($docUrl) ?>" class="btn btn-sm btn-success">Download</a>

                                        </p>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <p>No documents available.</p>
                                        <?php endif; ?>

                                    </ul>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>





<script>
// Function to handle tab switching
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#profileTabs .nav-link');
    const tabContents = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();

            // Remove 'active' class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add 'active' class to clicked tab
            tab.classList.add('active');

            // Remove 'active show' from all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active', 'show');
            });

            // Show the corresponding tab content
            const target = document.querySelector(tab.getAttribute('href'));
            if (target) {
                target.classList.add('active', 'show');
            }
        });
    });
});

//AJAX Request for Search Name/UserID/or any key
document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    // Get the search input value
    var searchValue = document.getElementById('search_name').value;

    // Perform fetch request
    fetch('<?php echo site_url("staff/search_teacher"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'search_name=' + encodeURIComponent(searchValue)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse the response as JSON
        })
        .then(data => {
            var tableBody = document.getElementById('searchResultsTable');
            tableBody.innerHTML = '';

            if (data.length > 0) {
                // Populate the table with search results
                data.forEach(function(student, index) {
                    // Create a row for each student
                    var row = document.createElement('tr');
                    row.classList.add('table-row'); // Add Bootstrap class
                    row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${student.user_id}</td>
                                <td>${student.name}</td>
                                <td>${student.father_name}</td>
                                
                                <td class="text-center">
                                    <button class="btn btn-white p-0 select-btn" style="background-color: #006400; color: white; border: 1px solid #ccc;">
                                        Find ➡
                                    </button>
                                </td>
                                
                            `;

                    row.querySelector('.select-btn').addEventListener('click', function() {
                        // Highlight the selected row
                        document.querySelectorAll('.table-row').forEach(tr => tr.classList
                            .remove('table-active'));
                        row.classList.add('table-active');

                        // Fetch the user ID of the selected student
                        const userId = student.user_id;

                        // Perform the AJAX request
                        fetch('<?php echo site_url("staff/teacher_profile"); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'userId=' + encodeURIComponent(userId)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response
                                    .text(); // Use text if the response contains an HTML view
                            })
                            .then(html => {
                                // window.location.href = '<?php echo current_url(); ?>'; // Redirects to the current URL
                                document.open(); // Clears the current document
                                document.write(html); // Writes the new HTML content
                                document.close();

                                document.getElementById('showinfo').style.display =
                                    'block';

                                // Close the modal by removing Bootstrap classes and hiding the backdrop
                                document.getElementById('searchModal').classList.remove(
                                    'show');
                                document.getElementById('searchModal').style.display =
                                    'none';
                                document.body.classList.remove('modal-open');
                                document.querySelector('.modal-backdrop').remove();
                            })
                            .catch(error => {
                                console.error(
                                    'There was a problem with the fetch operation:',
                                    error);
                            });

                    });

                    tableBody.appendChild(row);
                });
            } else {
                // No results found
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No results found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
});
</script>


<style>
#searchBtn {
    margin-top: 10px;
}

.search-section {
    /* margin-top: 10px; */
    /* display: flex;
    justify-content: space-between; */
    align-items: center;
    padding: 13px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
}

.table-container {
    margin-top: 20px;
    max-height: 360px;
    /* Adjust height to fit your needs */
    overflow-y: auto;
    border: 1px solid #ccc;
    position: relative;
}

/* Table Styling */
.table {
    border-collapse: collapse;
    width: 100%;

}

/* Table Header */
.table thead th {
    background-color: #006400;
    color: white;
    padding: 8px;
    position: sticky;
    top: 0;
    z-index: 2;
    /* Ensure header is above the content */

    text-align: left;
}

/* Table Body Rows */
.table tbody tr {

    cursor: pointer;
}

.table tbody td {
    padding: 8px;

}

.table tbody tr:hover {
    background-color: #b0b0b0 !important;
}

/* Table Footer */
.table tfoot td {
    background-color: #585652;
    color: white;
    font-weight: bold;
    padding: 8px;
    position: sticky;
    bottom: 0;
    z-index: 1;
    /* Ensure footer is above the content */

    text-align: left;
}

/* Highlight Selected Row */
.selected-row {
    background-color: #d1e7dd !important;
    transition: background-color 0.3s ease;
}


#searchTable {
    margin-top: 20px;
    /* Adjust as needed */
}

/* Content Wrapper Styling */
.content-wrapper {
    padding: 20px;
    background-color: #ecf0f5;
}

/* Header Styling */
.header {
    padding: 10px;
    margin-bottom: 30px;
    border-radius: 5px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

/* Profile Picture Styling */
.profile-pic {
    width: 150px;
    /* Set the width of the profile picture */
    height: 150px;
    /* Set the height of the profile picture */
    object-fit: cover;
    /* Ensures the image covers the entire space */
    border-radius: 50%;
    /* Makes the image round */
    border: 3px solid #ddd;
    /* Optional: adds a border around the profile picture */
}


.details-container h4 {
    color: #007bff;
    font-weight: bold;
}

.details-container p {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    color: #0c1116;
    margin-bottom: 15px;
    line-height: 1.6;
}

.details-container p strong {
    min-width: 150px;
    /* Ensures labels have a consistent width */
    text-align: left;
}

/* Details Container Styling */
.details-container {
    background-color: white;
    border-radius: 5px;
    padding-left: 80px;
    padding-right: 80px;
    padding-top: 20px;
    padding-bottom: 20px;

    box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
    margin-top: 15px;
}

.profile {
    background-color: white;
    border-radius: 5px;
    padding: 10px;

    box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
    margin-top: 15px;
}

.profile p {
    font-size: 16px;
    color: rgb(12, 17, 22);
    margin-bottom: 15px;
    line-height: 1.6;
}



.nav-tabs {
    display: flex;
    justify-content: center;
    /* Align items to the center horizontally */
    align-items: center;
    /* Align items to the center vertically (optional) */
    gap: 10px;
    /* Optional: Add spacing between list items */
    padding: 10px;
    background-color: #fff;
    /* Optional: Background color for better visibility */
    border-radius: 5px;
    box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
}




/* Navigation Bar Styling */
.nav-tabs .nav-link {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    margin-right: 10px;

    border: none;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-tabs .nav-link:hover {
    background: linear-gradient(135deg, #0056b3, #003f7f);
    color: white;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #0056b3, #003f7f);
    font-weight: bold;
    color: white;
}

/* Document Details Styling */
.document-button {
    margin-top: 5px;
}
</style>