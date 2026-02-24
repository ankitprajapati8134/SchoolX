<div class="content-wrapper">
    <div class="page_container">
        <div class="card" id="studentProfileContainer">
            <!-- Header Section -->
            <div class="header text-center bg-primary text-white">
                <h2><i class="fa fa-user"></i> Student Profile</h2>
            </div>

            <!-- <div class="search-section my-3" style="display: none;">
                <div class="row align-content-center">
                   
                    <div class="col-md-5"></div>
                    <div class="col-md-2 justify-content-end align-items-end">
                        <button type="button" class="btn btn-primary" id="searchdetails" data-toggle="modal"
                            data-target="#searchModal">
                            <i class="fa fa-search" style="font-size: 16px;"></i>
                            <br>
                            <span>Search Student</span>
                        </button>
                    </div>
                    <div class="col-md-5"></div>
                </div>
            </div>

            <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" style="display: none;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-center">
                            <h3 class="modal-title" id="studentModalLabel">Search Student By <br> User ID / Student Name/
                                Father Name</h3>
                        </div>
                        <div class="modal-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <form method="post" id="searchForm" action="#" class="d-flex flex-column">
                                        <input type="text" class="form-control mb-3" id="search_name" name="search_name"
                                            placeholder="Search Student">
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
                                            <th>User Id</th>
                                            <th>Student Name</th>
                                            <th>Father Name</th>
                                            <th>Class</th>
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
                            <a class="nav-link" id="academic-details-tab" data-bs-toggle="tab" href="#academic-details"
                                role="tab" aria-controls="academic-details" aria-selected="false">Academic Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="guardian-details-tab" data-bs-toggle="tab" href="#guardian-details"
                                role="tab" aria-controls="guardian-details" aria-selected="false">Guardian Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="address-details-tab" data-bs-toggle="tab" href="#address-details"
                                role="tab" aria-controls="address-details" aria-selected="false">Address Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="previous-school-tab" data-bs-toggle="tab" href="#previous-school"
                                role="tab" aria-controls="previous-school" aria-selected="false">Previous School
                                Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="fee-details-tab" data-bs-toggle="tab" href="#fee-details" role="tab"
                                aria-controls="fee-details" aria-selected="false">Fee Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="document-details-tab" data-bs-toggle="tab" href="#document-details"
                                role="tab" aria-controls="document-details" aria-selected="false">Document Details</a>
                        </li>

                    </ul>
                </div>



                <div class="row" style="margin-right: -15px; margin-left: 0px;">
                    <div class="profile col-md-4 text-center mb-3 mt-3">
                        <!-- <img src="<?= $student['Doc']['Photo']['document'] ?? base_url('tools/image/default-school.jpeg') ?>"
                            alt="ProfilePic"
                            onerror="this.src='<?= base_url('tools/image/default-school.jpeg') ?>';"
                            class="profile-picture"> -->
                        <img src="<?= $student['Doc']['PhotoUrl'] ?? base_url('tools/image/default-school.jpeg') ?>"
                            alt="ProfilePic"
                            onerror="this.src='<?= base_url('tools/image/default-school.jpeg') ?>';"
                            class="profile-picture">



                        <h1><?= isset($student['Name']) ? $student['Name'] : 'Unknown'; ?></h1>
                        <h3>
                            Class <?= $class ?? 'N/A'; ?> - Section <?= $section ?? 'N/A'; ?>
                        </h3>

                        <br>
                        <p><strong>Student Id:</strong> <?= $student['User Id'] ?? 'N/A'; ?></p>

                        <p><strong>Admission Date:</strong> <?= $student['Admission Date'] ?? 'N/A'; ?></p>
                        <button id="viewFeesBtn" class="btn btn-success"
                            data-user-id="<?= htmlspecialchars($student['User Id']); ?>">
                            View Submitted Fees Details
                        </button>
                    </div>
                    <!-- <p>

                            Hi! I’m <?= isset($student['Name']) ? $student['Name'] : 'Unknown'; ?>, a student at
                            <strong><?= $student['School Name'] ?? 'N/A'; ?></strong>, Grade <?= $class ?? 'N/A'; ?> ,
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


                    <div class="col-md-8">
                        <div class="details-container">
                            <div class="tab-content" id="profileTabsContent">
                                <div class="tab-pane show active" id="personal-details" role="tabpanel"
                                    aria-labelledby="personal-details-tab">
                                    <h4 class="text-center">Personal Details</h4><br>
                                    <p><strong>Name:</strong> <?= $student['Name'] ?? 'N/A'; ?></p>
                                    <p><strong>Gender:</strong> <?= $student['Gender'] ?? 'N/A'; ?></p>
                                    <p><strong>Date of Birth:</strong> <?= $student['DOB'] ?? 'N/A'; ?></p>
                                    <p><strong>Email:</strong> <?= $student['Email'] ?? 'N/A'; ?></p>
                                    <p><strong>Phone Number:</strong> <?= $student['Phone Number'] ?? 'N/A'; ?></p>

                                    <p><strong>Category:</strong> <?= $student['Category'] ?? 'N/A'; ?></p>
                                    <p><strong>Religion:</strong> <?= $student['Religion'] ?? 'N/A'; ?></p>
                                    <p><strong>Nationality:</strong> <?= $student['Nationality'] ?? 'N/A'; ?></p>

                                    <p><strong>Blood Group:</strong> <?= $student['Blood Group'] ?? 'N/A'; ?></p>

                                    </p>

                                </div>

                                <div class="tab-pane" id="academic-details" role="tabpanel"
                                    aria-labelledby="guardian-details-tab">
                                    <h4 class="text-center">Academic Details</h4><br>

                                    <p><strong>Class:</strong> <?= $student['Class'] ?? 'N/A'; ?></p>

                                    <p>
                                        <strong>Subjects:</strong>
                                        <span class="d-inline-block">
                                            <ul class="list-inline mb-0">
                                                <?php if (!empty($subjects)): ?>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <li class="list-inline-item"><?= htmlspecialchars($subject); ?></li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li class="list-inline-item">No subjects available</li>
                                                <?php endif; ?>
                                            </ul>
                                        </span>
                                    </p>
                                </div>



                                <div class="tab-pane " id="guardian-details" role="tabpanel"
                                    aria-labelledby="guardian-details-tab">
                                    <h4 class="text-center">Guardian Details</h4><br>
                                    <p><strong>Father’s Name:</strong> <?= $student['Father Name'] ?? 'N/A'; ?></p>
                                    <p><strong>Father’s Occupation:</strong>
                                        <?= $student['Father Occupation'] ?? 'N/A'; ?>
                                    </p>
                                    <p><strong>Mother’s Name:</strong> <?= $student['Mother Name'] ?? 'N/A'; ?></p>
                                    <p><strong>Mother’s Occupation:</strong>
                                        <?= $student['Mother Occupation'] ?? 'N/A'; ?>
                                    </p>

                                    <p><strong>Guardian Relation:</strong> <?= $student['Guard Relation'] ?? 'N/A'; ?>
                                    </p>
                                    <p><strong>Guardian Contact:</strong> <?= $student['Guard Contact'] ?? 'N/A'; ?></p>
                                </div>

                                <div class="tab-pane" id="address-details" role="tabpanel"
                                    aria-labelledby="address-details-tab">
                                    <h4 class="text-center">Address Details</h4><br>

                                    <p><strong>Street:</strong> <?= $student['Address']['Street'] ?? 'N/A'; ?></p>
                                    <p><strong>City:</strong> <?= $student['Address']['City'] ?? 'N/A'; ?></p>
                                    <p><strong>State:</strong> <?= $student['Address']['State'] ?? 'N/A'; ?></p>
                                    <p><strong>Postal Code:</strong> <?= $student['Address']['PostalCode'] ?? 'N/A'; ?>
                                    </p>


                                </div>

                                <div class="tab-pane" id="previous-school" role="tabpanel"
                                    aria-labelledby="previous-school-tab">
                                    <h4 class="text-center">Previous School Details</h4><br>

                                    <p><strong>Previous School:</strong> <?= $student['Pre School'] ?? 'N/A'; ?></p>
                                    <p><strong>Class Completed:</strong> <?= $student['Pre Class'] ?? 'N/A'; ?></p>
                                    <p><strong>Marks Obtained:</strong> <?= $student['Pre Marks'] ?? 'N/A'; ?></p>
                                </div>
                                <div class="tab-pane" id="fee-details" role="tabpanel"
                                    aria-labelledby="fee-details-tab">
                                    <h4 class="text-center mb-4">Fee Details</h4>

                                    <!-- Yearly Fees Table -->
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header text-center">
                                            <h4><u>Yearly Fees</u></h4>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-striped text-center">
                                                <thead class="table-dark text-center">
                                                    <tr>
                                                        <th>Fee Title</th>
                                                        <th>Total Amount (₹)</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    $yearlyTotal = 0;
                                                    if (!empty($fees['Yearly Fees'])) :
                                                        foreach ($fees['Yearly Fees'] as $feeTitle => $feeValue) :
                                                            $yearlyTotal += $feeValue;
                                                    ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($feeTitle); ?></td>
                                                                <td>₹<?= number_format((float) $feeValue, 2, '.', ','); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center">No yearly fees
                                                                available.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-warning font-weight-bold">
                                                        <td><strong>Total</strong></td>
                                                        <td><strong>₹<?= number_format((float) $yearlyTotal, 2, '.', ','); ?></strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            </table>
                                        </div>
                                    </div>

                                    <!-- Monthly Fees Table -->
                                    <div class="card shadow-sm">
                                        <div class="card-header text-center">
                                            <h4><u>Monthly Fees</u></h4>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-striped text-center">
                                                <thead class="table-secondary text-center">
                                                    <tr>
                                                        <th>Fee Title</th>
                                                        <th><strong>Total Amount (₹)</strong></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Extract unique fee titles
                                                    $feeTitles = [];
                                                    foreach ($fees as $month => $feeDetails) {
                                                        if ($month === 'Yearly Fees') continue;
                                                        foreach ($feeDetails as $feeTitle => $amount) {
                                                            $feeTitles[$feeTitle] = true;
                                                        }
                                                    }
                                                    $feeTitles = array_keys($feeTitles); // Get unique fee titles

                                                    // Initialize row totals
                                                    $rowTotals = array_fill_keys($feeTitles, 0);
                                                    $grandTotal = 0;

                                                    foreach ($feeTitles as $feeTitle) :
                                                        $rowTotal = 0;
                                                        foreach ($fees as $month => $feeDetails) {
                                                            $rowTotal += isset($feeDetails[$feeTitle]) ? (float) $feeDetails[$feeTitle] : 0;
                                                        }
                                                        $rowTotals[$feeTitle] = $rowTotal;
                                                        $grandTotal += $rowTotal;
                                                    ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($feeTitle); ?></strong></td>
                                                            <td><strong>₹<?= number_format($rowTotal, 2, '.', ','); ?></strong>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-warning font-weight-bold">
                                                        <td><strong>Total</strong></td>
                                                        <td><strong>₹<?= number_format($grandTotal, 2, '.', ','); ?></strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <!-- Expand Button -->
                                            <div class="text-center mt-3">
                                                <button class="btn btn-primary" id="openFeeModal">Expand</button>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Modal for Full Table -->
                                    <div id="feeDetailsModal" class="modal"
                                        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
                                        <div class="modal-dialog modal-xl"
                                            style="background: #fff; margin: 10% auto; padding: 20px; border-radius: 5px; width: 90%;">
                                            <div class="modal-header">
                                                <h4 class="modal-title text-center">Fee Details</h4>
                                                <button type="button" id="closeFeeModal" class="btn-close"
                                                    style="position: absolute; top: 10px; right: 15px; font-size: 20px; background: none; border: none; cursor: pointer;">&times;</button>

                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-striped text-center">
                                                    <thead class="table-secondary text-center">
                                                        <tr>
                                                            <th>Fee Type</th>
                                                            <?php
                                                            // Define month order
                                                            $monthOrder = [
                                                                "April",
                                                                "May",
                                                                "June",
                                                                "July",
                                                                "August",
                                                                "September",
                                                                "October",
                                                                "November",
                                                                "December",
                                                                "January",
                                                                "February",
                                                                "March"
                                                            ];
                                                            // Extract months from $fees in sorted order
                                                            $sortedMonths = array_intersect($monthOrder, array_keys($fees));

                                                            // Display months as table headers
                                                            foreach ($sortedMonths as $month) : ?>
                                                                <th><?= htmlspecialchars($month); ?></th>
                                                            <?php endforeach; ?>
                                                            <th><strong>Total</strong></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Extract unique fee titles
                                                        $feeTitles = [];
                                                        foreach ($fees as $month => $feeDetails) {
                                                            if ($month === 'Yearly Fees') continue;
                                                            foreach ($feeDetails as $feeTitle => $amount) {
                                                                $feeTitles[$feeTitle] = true;
                                                            }
                                                        }
                                                        $feeTitles = array_keys($feeTitles); // Get unique fee titles

                                                        // Initialize row totals & column totals
                                                        $columnTotals = array_fill_keys($sortedMonths, 0);
                                                        $grandTotal = 0;

                                                        foreach ($feeTitles as $feeTitle) :
                                                            $rowTotal = 0;
                                                        ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($feeTitle); ?></strong>
                                                                </td>
                                                                <?php foreach ($sortedMonths as $month) :
                                                                    $amount = isset($fees[$month][$feeTitle]) ? (float) $fees[$month][$feeTitle] : 0;
                                                                    $columnTotals[$month] += $amount;
                                                                    $rowTotal += $amount;
                                                                ?>
                                                                    <td>₹<?= number_format($amount, 2, '.', ','); ?></td>
                                                                <?php endforeach; ?>
                                                                <td><strong>₹<?= number_format($rowTotal, 2, '.', ','); ?></strong>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                            $grandTotal += $rowTotal;
                                                        endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-warning font-weight-bold">
                                                            <td><strong>Total</strong></td>
                                                            <?php foreach ($sortedMonths as $month) : ?>
                                                                <td><strong>₹<?= number_format($columnTotals[$month], 2, '.', ','); ?></strong>
                                                                </td>
                                                            <?php endforeach; ?>
                                                            <td><strong>₹<?= number_format($grandTotal, 2, '.', ','); ?></strong>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                                <!-- Exempted Fees Section -->
                                                <div class="mt-4 text-center">
                                                    <h4>Exempted Fees For Student</h4>
                                                    <?php if (!empty($exempted_fees)) : ?>
                                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                                            <?php foreach ($exempted_fees as $fee) : ?>
                                                                <span
                                                                    class="badge bg-danger px-3 py-2 fs-6"><?= htmlspecialchars($fee); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <p class="text-muted">No exempted fees</p>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>



                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header text-center">

                                            <h4><u>Total Discount Applied</u></h4>
                                            <p><strong>Current Discount Provided:</strong>
                                                ₹<?= isset($currentdiscount) && $currentdiscount !== '' ? number_format((float)$currentdiscount, 2, '.', ',') : 'No value found'; ?>
                                            </p>

                                            <p><strong>Total Discount (Given Till Now):</strong>
                                                ₹<?= isset($totaldiscount) && $totaldiscount !== '' ? number_format((float)$totaldiscount, 2, '.', ',') : 'No value found'; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Overall Total -->
                                    <div class="alert alert-info text-center mt-4">
                                        <h3><strong>Overall Total (Yearly + Monthly - Discount) = </strong>
                                            ₹<?php
                                                if (
                                                    isset($yearlyTotal, $grandTotal, $totaldiscount) &&
                                                    $yearlyTotal !== '' &&
                                                    $grandTotal !== '' &&
                                                    $totaldiscount !== ''
                                                ) {
                                                    echo number_format((float)($yearlyTotal + $grandTotal - $totaldiscount), 2, '.', ',');
                                                } else {
                                                    echo 'No value found';
                                                }
                                                ?>
                                        </h3>
                                    </div>
                                    <form id="onDemandDiscountForm">
                                        <div class="row align-items-center g-2">
                                            <div class="card-header text-center">
                                                <h4><u>Add On Demand Discount</u></h4>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="number" id="onDemandDiscount" name="onDemandDiscount"
                                                    class="form-control" placeholder="Enter discount amount in ₹"
                                                    required>
                                            </div>
                                            <div class="col-md-6 text-md-start text-start">
                                                <button type="submit" id="submitDiscountButton"
                                                    class="btn btn-primary">Submit Discount</button>
                                            </div>
                                        </div>
                                    </form>


                                </div>


                                <div class="tab-pane" id="document-details" role="tabpanel">
                                    <ul>
                                        <h4 class="text-center">Documents</h4><br>

                                        <?php if (!empty($student['Doc'])): ?>
                                            <?php foreach ($student['Doc'] as $docName => $docUrl): ?>

                                                <?php if (!empty($docUrl)): ?>
                                                    <p>
                                                        <strong><?= htmlspecialchars($docName) ?>:</strong>

                                                        <a href="<?= htmlspecialchars($docUrl) ?>"
                                                            target="_blank"
                                                            class="btn btn-sm btn-success">
                                                            View
                                                        </a>
                                                    </p>
                                                <?php endif; ?>

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




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // function forceDownload(url, filename) {
    //     fetch(url, { mode: 'no-cors' }) // Avoid CORS issue
    //     .then(response => response.blob())
    //     .then(blob => {
    //         const link = document.createElement('a');
    //         link.href = window.URL.createObjectURL(blob);
    //         link.download = filename + '.jpg';  // Adjust file extension if needed
    //         document.body.appendChild(link);
    //         link.click();
    //         document.body.removeChild(link);
    //     })
    //     .catch(error => console.error('Download error:', error));
    // }
    document.getElementById('openFeeModal').addEventListener('click', function() {
        document.getElementById('feeDetailsModal').style.display = "block";
    });

    document.getElementById('closeFeeModal').addEventListener('click', function() {
        document.getElementById('feeDetailsModal').style.display = "none";
    });

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('feeDetailsModal');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
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
    document.getElementById("viewFeesBtn").addEventListener("click", function() {
        const userId = this.dataset.userId;
        if (userId) {
            // window.location.href = `/Grader/school/fees/student_fees?userId=${encodeURIComponent(userId)}`;
            window.location.href = "<?= base_url('fees/student_fees?userId=') ?>" + encodeURIComponent(userId);

        }
    });
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("onDemandDiscountForm");
        const submitButton = document.getElementById("submitDiscountButton"); // Ensure button is available

        if (!form || !submitButton) {
            console.error("Form or Submit Button not found!");
            return;
        }

        form.addEventListener("submit", function(event) {
            event.preventDefault();

            const discountValue = document.getElementById("onDemandDiscount").value.trim();
            if (discountValue === "") {
                alert("Please enter a discount amount.");
                return;
            }

            // Disable button and update text
            submitButton.disabled = true;
            submitButton.innerHTML = "Submitting...";

            const userId = "<?= htmlspecialchars($student['User Id'], ENT_QUOTES, 'UTF-8'); ?>";
            const studentClass = "<?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>";
            const section = "<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>";

            const payload = new URLSearchParams();
            payload.append("userId", userId);
            payload.append("class", studentClass);
            payload.append("section", section);
            payload.append("discount", discountValue);

            fetch("<?= base_url('fees/submit_discount') ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: payload
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Discount submitted successfully!");
                        form.reset();
                        window.location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Failed to submit discount.");
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = "Submit Discount";
                });
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

        text-align: center;
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

        text-align: center;
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

    .profile-picture {
        width: 180px;
        /* Equal width & height */
        height: 180px;
        object-fit: cover;
        /* Fill circle properly */
        border-radius: 50%;
        /* Makes it circular */
        border: 4px solid #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 15px;
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

    tfoot td {
        font-weight: bold;
        background-color: #585652;
        color: white;
        text-align: center;
        padding: 8px;
    }
</style>