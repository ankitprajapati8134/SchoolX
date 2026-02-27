<div class="content-wrapper">
    <div class="page_container">
        <div class="header text-center bg-primary text-white">
            <i class="fas fa-dollar-sign"></i>
            <h1>Fee Counter</h1>
        </div>

        <!-- Row for Receipt and Account Details -->
        <form method="post" action="<?php echo site_url('fees/fees_counter'); ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
            <fieldset>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="accountSelect">Mode of Payment</label>
                                <select id="accountSelect" name="paymentMode" class="form-control" required>
                                    <option disabled selected>Select Payment Mode</option>
                                    <?php if (!empty($accounts)): ?>
                                        <?php foreach ($accounts as $accountName => $under): ?>
                                            <option value="<?= htmlspecialchars($accountName); ?>">
                                                <?= htmlspecialchars($accountName) . ' (' . htmlspecialchars($under) . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No accounts available</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="receiptNo">Receipt No.</label>
                                <input type="text" id="receiptNo" name="receiptNo" class="form-control"
                                    value="<?php echo isset($receiptNo) ? $receiptNo : ''; ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="date">Date</label>
                                <input type="text" id="date" class="form-control" value="<?php echo date('d-m-Y'); ?>"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="sname">Student Name</label>
                                <input type="text" name="Name" id="sname" class="form-control"
                                    value="<?php echo isset($studentData['Name']) ? $studentData['Name'] : ''; ?>"
                                    placeholder="Student Name" readonly>
                            </div>

                        </div>


                        <!-- Row for Student Details -->

                        <div class="row mb-3">

                            <div class="col-md-2">
                                <label for="user_id">Student Id</label>
                                <input type="text" name="user_id" id="user_id" class="form-control"
                                    value="<?php echo isset($studentData['User Id']) ? $studentData['User Id'] : ''; ?>">
                            </div>

                            <div class="col-md-1">
                                <label>Search</label>
                                <button type="button" class="btn btn-warning" id="searchdetails" data-toggle="modal"
                                    data-target="#searchModal">User
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                            <div class="col-md-3">
                                <label for="fname">Father Name</label>
                                <input type="text" id="fname" class="form-control"
                                    value="<?php echo isset($studentData['Father Name']) ? $studentData['Father Name'] : ''; ?>"
                                    placeholder="Father Name" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="class">Class</label>
                                <input type="text" name="class" id="class" class="form-control"
                                    value="<?php echo htmlspecialchars($classOnly ?? ''); ?>" placeholder="Select Class"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="discountAmount">Discount To Be Applied</label>
                                <input type="text" id="discountAmount" class="form-control"
                                    value="<?php echo isset($discountAmount) ? $discountAmount : '00.00'; ?>" readonly>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <label for="months">Select Months</label>
                                <div class="dropdown">
                                    <button class="btn btn-light w-100" type="button" id="monthDropdownButton">
                                        Select Months <span class="ms-2">&#9662;</span>
                                    </button>
                                    <ul class="dropdown-menu" id="monthList">
                                        <!-- month dropdown will show here -->
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-1" style="align-items: right;">
                                <label>Fetch</label>

                                <button type="submit" class="btn btn-success w-200" id="fetchDetailsButton"
                                    disabled>Fetch
                                </button>
                            </div>

                            <div class="col-md-3">
                                <label for="totalAmount">Total Amount</label>
                                <input type="text" id="totalAmount" class="form-control"
                                    value="<?php echo isset($grandTotal) ? number_format($grandTotal, 2) : '00.00'; ?>"
                                    readonly>
                            </div>

                            <div class="col-md-3">
                                <label for="submitAmount">Submitted Amount</label>
                                <input type="text" id="submitAmount" class="form-control"
                                    value="<?php echo isset($oversubmittedFees) && $oversubmittedFees !== '' ? $oversubmittedFees : '0.00'; ?>"
                                    readonly>

                            </div>

                            <div class="col-md-3">
                                <label for="dueAmount"> Total Due Amount</label>
                                <input type="text" id="dueAmount" value="<?php echo $dueAmount; ?>" class="form-control"
                                    style="color: red; font-weight: bold;" readonly>
                            </div>
                        </div>


                        <?php if (isset($error)) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>

            </fieldset>

            <h2 id="selectedMonthHeading" style="display: none; text-align: center;"><?php echo $message; ?></h2>

            <!-- Tables for Fee Titles and Prevfieldsetments -->
            <div class="row">
                <div class="col-md-12">
                    <!-- Fee Title Table -->
                    <div class="table_wrapper">
                        <table class="table table-bordered">
                            <thead class="bg-danger text-white text-left">
                                <tr>
                                    <th class="text-left">Fee Title</th>
                                    <th class="text-left">Total</th>
                                    <!-- <th>Action</th> -->
                                </tr>
                            </thead>
                            <tbody class="fee-title-table">
                                <?php
                                $grandTotal = 0; // Initialize total

                                if (!empty($feesRecord)):
                                    foreach ($feesRecord as $fee):
                                        $grandTotal += $fee['total']; // Sum the total column values
                                ?>
                                        <tr>
                                            <td class="text-left"><?php echo htmlspecialchars($fee['title']); ?></td>
                                            <td class="text-left"><?php echo number_format($fee['total'], 2); ?></td>
                                        </tr>
                                    <?php
                                    endforeach;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="2" class="text-left">No Fees Data available, Please Enter the User ID.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td class="text-left"><strong>Grand Total</strong></td>
                                    <td class="text-left">
                                        <strong><?php echo number_format($grandTotal, 2); ?></strong>
                                        <button type="button" class="btn btn-primary action-arrow"
                                            id="expandTotalButton" style="margin-left: 25px;" data-toggle="modal"
                                            data-target="#expandTotalModal"> Expand
                                            &#x25B6;
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>

            <!-- Input Fields for Additional Fees -->
            <fieldset>
                <div class="row mb-3 mt-3">
                    <div class="col-md-6">
                        <label for="submitSchoolFees">Enter School Fees</label>
                        <input type="number" class="form-control" id="submitSchoolFees" placeholder="School Fee">
                    </div>
                    <!-- <div class="col-md-6">
                        <label for="conveyanceFees">Conveyance Fees</label>
                        <input type="number" class="form-control" id="conveyanceFees" placeholder="Conveyance Fee">
                    </div> -->
                </div>


                <div class="row mb-3 mt-3">
                    <div class="col-md-6">
                        <label for="fineAmount">Fine</label>
                        <input type="number" class="form-control" id="fineAmount" placeholder="Fine">
                    </div>
                    <div class="col-md-6">
                        <label for="reference">Give Reference</label>
                        <input type="text" class="form-control" id="reference" placeholder="Reference">
                    </div>
                </div>
            </fieldset>
        </form>


        <!-- Buttons for Actions -->
        <div class="row mb-3 mt-3">
            <div class="col-md-12 d-flex justify-content-end">
                <button id="submitfees" class="btn bg-maroon">Submit Fees</button>
                <button class="btn btn-primary me-3" onclick="location.href = location.href.split('?')[0] + '?t=' + new Date().getTime();">New Receipt</button>



                <!-- Fees Record Button -->
                <button id="feesRecordBtn" type="button" class="btn btn-info" data-toggle="modal"
                    data-target="#feesModal" disabled>
                    Fees Record
                </button>

            </div>
        </div>
    </div>
</div>


<!-- Expand button clicked and month Fees Record Button -->
<div id="expandTotalModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fees</h4>
            </div>
            <div class="modal-body">
                <div class="table_wrapper">

                    <table class="table table-bordered">
                        <thead class="bg-danger text-white text-center">
                            <tr>
                                <th class="text-left">Fee Title</th>
                                <?php foreach ($selectedMonths as $month): ?>
                                    <th class="text-center"><?php echo htmlspecialchars($month); ?></th>
                                <?php endforeach; ?>
                                <th class="text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feeRecord as $fee): ?>
                                <tr>
                                    <td class="text-left"><?php echo htmlspecialchars($fee['title']); ?></td>
                                    <?php foreach ($selectedMonths as $month): ?>
                                        <td class="text-center"><?php echo number_format($fee[$month] ?? 0, 2); ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-left"><?php echo number_format($fee['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td class="text-left"><strong>Total</strong></td>
                                <?php foreach ($selectedMonths as $month): ?>
                                    <td class="text-center month-total" data-month="<?php echo $month; ?>">
                                        <strong><?php echo number_format($monthTotals[$month], 2); ?></strong>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-left"><strong><?php echo number_format($grandTotal, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>




<!-- Bootstrap Modal For Fees Record -->
<div id="feesModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fees Record</h4>
            </div>
            <div class="modal-body">
                <div class="table_wrapper">
                    <table class="table table-bordered">
                        <thead class="bg-danger text-white text-center">
                            <tr>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Fine</th>
                                <th>Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($feeDetails)) : ?>
                                <?php foreach ($feeDetails as $fee) : ?>
                                    <tr class="text-center">
                                        <td><?php echo htmlspecialchars($fee['receiptno']); ?></td>
                                        <td><?php echo htmlspecialchars($fee['Date']); ?></td>
                                        <td><?php echo number_format((float) str_replace(',', '', $fee['Amount']), 2, '.', ','); ?></td>
                                        <td><?php echo number_format((float) str_replace(',', '', $fee['Fine']), 2); ?></td>
                                        <td><?php echo number_format((float) str_replace(',', '', $fee['Discount']), 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No Payments made yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <td></td>
                                <td class="text-cent    er"><strong>Total</strong></td>
                                <!-- Empty column for Date -->
                                <td class="text-center">
                                    <strong>
                                        <?php
                                        $totalAmount = array_sum(array_map(function ($fee) {
                                            return (float) str_replace(',', '', $fee['Amount']);
                                        }, $feeDetails));
                                        echo number_format($totalAmount, 2);
                                        ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <strong>
                                        <?php
                                        $totalFine = array_sum(array_map(function ($fee) {
                                            return (float) str_replace(',', '', $fee['Fine']);
                                        }, $feeDetails));
                                        echo number_format($totalFine, 2);
                                        ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <strong>
                                        <?php
                                        $totalDiscount = array_sum(array_map(function ($fee) {
                                            return (float) str_replace(',', '', $fee['Discount']);
                                        }, $feeDetails));
                                        echo number_format($totalDiscount, 2);
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>


                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal for Search Student Structure -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-center">
                <h2 class="modal-title" id="searchModalLabel">Search Student By User ID / Name</h2>
            </div>

            <div class="modal-body">
                <!-- Search Section -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <!-- Ensure the form action is correct -->
                            <form id="searchForm" method="post" action="#">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
                                <input type="text" class="form-control" id="search_name" name="search_name"
                                    placeholder="Search Student">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Student Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="text-center">
                            <tr class="bg-success text-center">
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
                                <td colspan="5" class="text-center">No results found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="cancelButton" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let selectedMonths = <?php echo isset($selectedMonths) && is_array($selectedMonths) ? json_encode($selectedMonths) : '[]'; ?>;
    console.log("Selected Months from PHP:", selectedMonths);

    document.addEventListener("DOMContentLoaded", function() {
        const userIdInput = document.getElementById("user_id");
        const fetchDetailsButton = document.getElementById("fetchDetailsButton");
        const dropdownButton = document.getElementById("monthDropdownButton");
        const monthList = document.getElementById("monthList");
        const feesRecordsButton = document.getElementById("feesRecordBtn");


        document.getElementById('selectedMonthHeading').style.display = 'block';

        feesRecordsButton.disabled = false;
        dropdownButton.disabled = true;
        fetchDetailsButton.disabled = true;
        monthList.style.display = "none";


        userIdInput.addEventListener("input", function() {
            let userId = this.value.trim();
            if (userId !== "") {
                setTimeout(() => fetchEnabledMonths(userId), 1000);
            } else {
                dropdownButton.disabled = true;
                monthList.innerHTML = "";
            }
        });

        function fetchEnabledMonths(userId) {
            $.ajax({
                url: 'fetch_months',
                type: 'POST',
                data: {
                    user_id: userId
                },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        console.error('Error fetching months:', data.error);
                        return;
                    }
                    updateMonthsDropdown(data);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        window.onload = function() {
            console.log("Window Loaded. Selected Months from PHP:", selectedMonths);
            let monthFees = {};
            updateMonthsDropdown(monthFees, selectedMonths);
        };

        function updateMonthsDropdown(monthFees, selectedMonths = []) {
            console.log("Before fetching details, selectedMonths:", selectedMonths);

            let dropdownButton = document.getElementById("monthDropdownButton");
            let monthList = document.getElementById("monthList");

            dropdownButton.disabled = false;
            monthList.innerHTML = "";

            // Add "Select All" checkbox at the top
            let selectAllItem = `<li>
                            <label class='dropdown-item'>
                                <input type='checkbox' id='selectAllCheckbox'>
                                <strong>Select All</strong>
                            </label>
                        </li>`;
            monthList.innerHTML += selectAllItem;

            const months = [
                "April", "May", "June", "July", "August", "September", "October",
                "November", "December", "January", "February", "March", "Yearly Fees"
            ];

            months.forEach(month => {
                let isDisabled = monthFees[month] === 1 ? "disabled" : "";
                let isChecked = selectedMonths.includes(month) ? "checked" : ""; // Ensure pre-selected months

                let listItem = `<li>
                            <label class='dropdown-item'>
                                <input type='checkbox' class='month-checkbox' name='months[]' value='${month}' ${isDisabled} ${isChecked}>
                                ${month}
                            </label>
                        </li>`;
                monthList.innerHTML += listItem;
            });

            console.log("After fetching details, selectedMonths:", selectedMonths);

            // Add event listener for "Select All" checkbox
            document.getElementById("selectAllCheckbox").addEventListener("change", function() {
                let allCheckboxes = document.querySelectorAll(".month-checkbox:not(:disabled)");
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateButtonText();
            });

            // Add event listener for individual checkboxes
            monthList.querySelectorAll(".month-checkbox").forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    updateButtonText();
                    updateSelectAllState();
                });
            });

            // Update "Select All" checkbox state based on pre-selected months
            updateSelectAllState();
        }



        dropdownButton.addEventListener("click", function(event) {
            event.stopPropagation();
            if (!dropdownButton.disabled) {
                monthList.style.display = (monthList.style.display === "block") ? "none" : "block";
            }
        });

        document.addEventListener("click", function(event) {
            if (!monthList.contains(event.target) && event.target !== dropdownButton) {
                monthList.style.display = "none";
            }
        });

        monthList.addEventListener("click", function(event) {
            event.stopPropagation();
        });

        function updateButtonText() {
            const selectedMonths = Array.from(document.querySelectorAll(".month-checkbox:checked"))
                .map(checkbox => checkbox.value);
            dropdownButton.textContent = selectedMonths.length > 0 ? `${selectedMonths.length} Month(s) Selected` :
                "Select Month";
            fetchDetailsButton.disabled = selectedMonths.length === 0;
        }

        function updateSelectAllState() {
            let allCheckboxes = document.querySelectorAll(".month-checkbox:not(:disabled)");
            let checkedCheckboxes = document.querySelectorAll(".month-checkbox:checked:not(:disabled)");
            document.getElementById("selectAllCheckbox").checked = allCheckboxes.length === checkedCheckboxes
                .length;
        }
    });


    //AJAX Request for Search Name/UserID/or any key
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form submission

        // Get the search input value
        var searchValue = document.getElementById('search_name').value;

        // Perform fetch request
        fetch('<?php echo site_url("fees/search_student"); ?>', {
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
                                    <td class="text-center">${index + 1}</td>
                                    <td class="text-center">${student.user_id}</td>
                                    <td class="text-center">${student.name}</td>
                                    <td class="text-center">${student.father_name}</td>
                                    <td class="text-center">${student.class}</td>
                                    <td class="text-center">
                                        <button class="btn btn-white p-0 select-btn" style="background-color: white; border: none;">
                                            ➡️
                                        </button>
                                    </td>
                                `;

                        // Add click event listener to the button in the row
                        row.querySelector('.select-btn').addEventListener('click',
                            function() {
                                // Highlight the selected row
                                document.querySelectorAll('.table-row').forEach(tr => tr
                                    .classList
                                    .remove('table-active'));
                                row.classList.add('table-active');

                                // Automatically fill the user_id input box
                                document.getElementById('user_id').value = student
                                    .user_id;

                                // Ensure the months dropdown appears after selecting a student
                                toggleMonthsDropdown
                                    (); // Trigger the months dropdown visibility check
                            });

                        tableBody.appendChild(row);
                    });
                } else {
                    // No results found
                    tableBody.innerHTML =
                        '<tr><td colspan="6" class="text-center">No results found.</td></tr>';
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    });

    // Add event listener for Cancel button
    document.getElementById('cancelButton').addEventListener('click', function() {
        // Reset the search input
        document.getElementById('search_name').value = '';

        // Clear the search results table
        var tableBody = document.getElementById('searchResultsTable');
        tableBody.innerHTML = ''; // Clear the table            

        // Clear the user_id input
        document.getElementById('search_name').value = '';

        // Reset the selected row (if any)
        document.querySelectorAll('.table-row').forEach(row => row.classList.remove('table-active'));
    });



  
    $(document).ready(function() {
        $('#submitfees').on('click', function(event) {
            event.preventDefault(); // Prevent the default form submission

            let selectedMonths = $('input[name="months[]"]:checked').map(function() {
                return $(this).val();
            }).get(); // Get selected months as an array

            let monthTotals = []; // Array to store month-wise totals

            // Fetch month totals from the table (assuming each month's total is in <td class="month-total" data-month="April">)
            $('.month-total').each(function() {
                let month = $(this).data('month'); // Get the month name from data attribute
                let total = parseFloat($(this).text().replace(/,/g, '')) || 0; // Convert to float, removing commas

                if (selectedMonths.includes(month)) {
                    monthTotals.push({
                        month: month,
                        total: total
                    }); // Push an object with month and total
                }
            });

            // Collect form data
            const formData = {
                receiptNo: $('#receiptNo').val(),
                date: $('#date').val(),
                studentName: $('#sname').val(),
                paymentMode: $('#accountSelect').val(),
                fatherName: $('#fname').val(),
                class: $('#class').val(),
                userId: $('#user_id').val(),
                totalAmount: $('#totalAmount').val(),
                submitAmount: $('#submitAmount').val(),
                dueAmount: $('#dueAmount').val(),
                schoolFees: $('#submitSchoolFees').val(),
                discountAmount: $('#discountAmount').val(),
                fineAmount: $('#fineAmount').val(),
                reference: $('#reference').val(),
                selectedMonths: selectedMonths, // Send selected months as an array
                monthTotals: monthTotals // Now an array of objects instead of an object
            };

            // console.log("Selected Months:", formData.selectedMonths); // Debugging
            // console.log("Month Totals:", formData.monthTotals); // Debugging
            // console.log("Final FormData being sent:", formData);

            // AJAX request
            $.ajax({
                url: 'submit_fees', // Replace with the actual server-side URL
                type: 'POST',
                data: formData,
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'json',
                beforeSend: function() {
                    $('#submitfees').text('Submitting...').attr('disabled', true);
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Fees submitted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                },
                complete: function() {
                    $('#submitfees').text('Submit Fees').attr('disabled', false);
                },
            });
        });
    });
</script>


<style>
    .content-wrapper {
        background-color: #f8f9fa
    }

    .page_container {
        padding-top: 10px;
        background-color: #f8f9fa;
    }

    .row {
        margin-left: 0;
        margin-right: 0;
        padding-top: 8px;
        padding-bottom: 10px;


        padding-left: 15px;
        /* Adds left padding */
        padding-right: 15px;
        /* Adds right padding */
    }

    .row .col-md-2,
    .row .col-md-3,
    .row .col-md-4,
    .row .col-md-6 {
        padding-left: 15px;
        /* Adds padding to each column */
        padding-right: 15px;
    }

    .col-md-12 {
        padding-left: 15px;
        padding-right: 15px;
    }

    .row.mb-3 {
        margin-bottom: 15px;
        /* Adjust row margins */
    }

    .table-wrapper {
        max-height: 40px;
        /* Set height for scrolling */
        overflow-y: auto;
        /* Enable vertical scrolling */
        overflow-x: hidden;
        /* Prevent horizontal scrolling */
    }

    thead th {
        top: 0;
        /* Fix header to top */
    }


    table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #ccc;
        table-layout: fixed;
        word-wrap: break-word;
    }


    thead th,
    tfoot td {
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 2px;
        position: sticky;
        z-index: 2;
    }

    #monthList {
        background-color: white !important;
        /* Set background color to white */
        border-radius: 5px;
        /* Optional: Add rounded corners */
    }

    #monthList .dropdown-item {
        color: black;
        /* Optional: Change text color */
    }

    #monthDropdownButton {
        background-color: #ffffff;
        /* Optional: Style the button */
        border-color: #6c757d;
        color: #555;
        /* Button text color */
        position: relative;
        /* Positioning needed for the dropdown arrow */
        padding-right: 20px;
        /* Adjust padding to make room for the arrow */

    }

    /* Style for the arrow */
    #monthDropdownButton span {
        font-size: 1.55rem;
        /* Adjust size of the arrow */
        color: black;
        /* Arrow color */
        font-weight: bold;
        /* Optional: Make the arrow bold */
    }

    @media (min-width: 992px) {
        .modal-lg {
            max-width: 98%;
            /* Increase modal width */
        }
    }

    /* Expand the modal height */
    .modal-body {
        max-height: 90vh;
        /* Make modal taller */
        overflow-y: auto;
        /* Allow vertical scrolling */
    }
</style>
