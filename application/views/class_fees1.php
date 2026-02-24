<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <!-- Title Bar -->
            <div class="title-bar">
                <i class="fas fa-money-bill-alt"></i>Class Fees: <?php echo htmlspecialchars($class ?? ''); ?>

            </div>

            <!-- Enlarged Search Section with Search Button -->
            <div class="search-section py-3" style="display: none;">
                <div class="form-row justify-content-center">
                    <div class="col-md-6">
                        <label for="selectClass" class="font-weight-bold">Select Class</label>
                        <select id="selectClass" class="form-control">
                            <option disabled selected>Class</option>
                            <?php foreach ($classes as $class) : ?>
                                <option value="<?php echo htmlspecialchars($class); ?>">
                                    <?php echo htmlspecialchars($class); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="selectSection" class="font-weight-bold">Select Section</label>
                        <select id="selectSection" class="form-control" disabled>
                            <option disabled selected>Section</option>
                        </select>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button id="searchBtn" class="btn btn-primary" disabled><i class="fa fa-search"></i> Search</button>
                </div>
            </div>



            <!-- Table Section -->
            <div class="table-responsive mt-3" id="print-section">
                <div class="table-scroll">
                    <table class="table" id="table">
                        <thead class="table-header">
                            <tr>
                                <th>Sr.no</th>
                                <th>User Id</th>
                                <th>Student Name/Guardian Name</th>
                                <th>Total Fee</th>
                                <th>Received Fee</th>
                                <th>Discount Applied</th>


                                <th>Due Fee</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Placeholder row -->
                            <tr>
                                <td colspan="6" class="text-center">Please select class and section first</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="footer-row">
                                <td colspan="3">Total</td>
                                <td id="totalFee">0</td>
                                <td id="receivedFee">0</td>
                                <td id="dueFee">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>




            <div class="buttons d-flex justify-content-between mt-4">
                <div class="col-md-3">
                    <button onclick="toggleSearchSection()" class="btn btn-warning border">Search Another Class</button>
                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-3">
                    <button id="viewDetailsBtn" class="btn btn-success" disabled>View Details</button>
                    <!-- <button class="btn btn-primary" onclick="window.print()">Print</button> -->
                    <button class="btn btn-primary" onclick="printTable()">Print</button>
                    <button class="btn btn-info" onclick="history.back();">Back</button>

                </div>
            </div>
        </div>

        <!-- Receipt Container - Hidden initially -->
        <div id="receipt-container" style="display: none;">
            <div class="container">
                <div class="header">DEMO SCHOOL OF SCIENCE</div>
                <div class="sub-header">RAMLEELA GROUND ETWAH</div>
                <div class="sub-header">
                    <span>Email -</span> demo@gmail.com<br>
                    <span>Phone.No</span> 0562-1001<br>
                    <span>Mobile</span> 1234567890
                </div>

                <div class="title-bar">Pending Fee ( Class Wise )</div>

                <!-- Info bar for Class and Session -->
                <div class="info-bar">
                    <div class="info-box">Class: 6TH - A</div>
                    <div class="info-box">Session: 2024 - 2025</div>
                </div>

                <!-- Table starts here -->
                <table class="table">
                    <thead class="table-header">
                        <tr>
                            <th>St Sr.no</th>
                            <th>Student Name</th>
                            <th>Total Fee</th>
                            <th>Received</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2</td>
                            <td>ANKIT / ANKIT</td>
                            <td>10.00</td>
                            <td>1.00</td>
                            <td>9.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="footer-row">
                            <td colspan="2">Total Student</td>
                            <td>1</td>
                            <td>Total Pending Fee</td>
                            <td>9.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>





function printTable() {
    const printSection = document.getElementById("print-section").innerHTML;
    const originalContent = document.body.innerHTML;

    // Temporarily replace the body content with the table content for printing
    document.body.innerHTML = `
        <html>
        <head>
            <title>Print Table</title>
            <style>
                @media print {
                    body {
                        font-family: Arial, sans-serif;
                    }
                    .table th {
                        background-color: #28a745;
                        color: white;
                    }
                    .table td, .table th {
                        border: 1px solid #dee2e6;
                        padding: 10px;
                        text-align: center;
                    }
                    .footer-row td {
                        background-color: #d1ecf1;
                    }
                }
            </style>
        </head>
        <body>
            <h1 class="header">Student Fee Details</h1>
            <div class="sub-header">Class & Section Summary</div>
            ${printSection}
        </body>
        </html>
    `;
    
    window.print();
    // Revert back to the original content
    document.body.innerHTML = originalContent;
    location.reload(); // Reload to restore JavaScript functionality
}

    // Step 1: Enable Section and Search Button After Selecting Class


    document.getElementById('selectClass').addEventListener('change', function() {
        // document.getElementById('selectSection').disabled = false;
        const sectionSelect = document.getElementById('selectSection');
        const selectedClass = this.value;
        const sections = <?php echo json_encode($sections); ?>;
        sectionSelect.innerHTML = '<option value="">Select Section</option>';

        if (selectedClass && sections[selectedClass]) {
            sectionSelect.disabled = false;
            sections[selectedClass].forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                sectionSelect.appendChild(option);
            });
        } else {
            sectionSelect.disabled = true;
        }
    });



    document.getElementById('selectSection').addEventListener('change', function() {
        document.getElementById('searchBtn').disabled = false;
    });


    document.getElementById('searchBtn').addEventListener('click', function() {
        const tableBody = document.getElementById('table-body');
        tableBody.innerHTML = ''; // Clear existing rows

        const selectClass = document.getElementById('selectClass').value;
        const selectSection = document.getElementById('selectSection').value;

        // AJAX request to fetch data from the server
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'due_fees_table', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Send the class and section to the server
        xhr.send(`class=${encodeURIComponent(selectClass)}&section=${encodeURIComponent(selectSection)}`);

        xhr.onload = function() {
            if (xhr.status === 200) {
                // Parse the server response as JSON
                const data = JSON.parse(xhr.responseText);

                let totalFee = 0,
                    receivedFee = 0,
                    dueFee = 0;

                data.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.userId}</td>
                    <td>${row.name}</td>
                    <td>${numberFormat(row.totalFee)}</td> <!-- Apply number formatting -->
                    <td>${numberFormat(row.receivedFee)}</td> <!-- Apply number formatting -->
                    <td>${numberFormat(row.dueFee)}</td> <!-- Apply number formatting -->
                `;

                    // Single-click functionality: highlight the row
                    tr.addEventListener('click', function() {
                        // Remove highlight from any previously selected row
                        const selectedRow = document.querySelector('.selected-row');
                        if (selectedRow) {
                            selectedRow.classList.remove('selected-row');
                        }
                        // Add highlight to clicked row
                        tr.classList.add('selected-row');
                        document.getElementById('viewDetailsBtn').disabled = false;
                        document.getElementById('viewDetailsBtn').dataset.userId = row.userId;
                    });

                    // Double-click functionality: redirect to another webpage via AJAX request
                    tr.addEventListener('dblclick', function() {
                        const selectedRow = document.querySelector('.selected-row');
                        const userId = row.userId;
                        if (selectedRow) {
                            const userId = row.userId;
                            window.location.href =
                                `student_fees?userId=${encodeURIComponent(userId)}`;
                        }
                        // if (userId) {
                        //     // Save the state of the entire page
                        //     const state = {
                        //         selectedClass: document.getElementById('selectClass').value,
                        //         selectedSection: document.getElementById('selectSection')
                        //             .value,
                        //         tableData: document.getElementById('table-body').innerHTML,
                        //         totalFee: document.getElementById('totalFee').textContent,
                        //         receivedFee: document.getElementById('receivedFee')
                        //             .textContent,
                        //         dueFee: document.getElementById('dueFee').textContent,
                        //         scrollPosition: window.scrollY,
                        //         searchSectionVisible: document.querySelector(
                        //             '.search-section').style.display !== 'none',
                        //     };

                        //     history.pushState(state, '', window.location.href);
                        //     window.location.href =
                        //         `student_fees?userId=${encodeURIComponent(userId)}`;
                        // }
                    });

                    tableBody.appendChild(tr);

                    // Sum totals
                    totalFee += row.totalFee;
                    receivedFee += row.receivedFee;
                    dueFee += row.dueFee;
                });

                // Update totals in the footer row
                document.getElementById('totalFee').textContent = numberFormat(
                    totalFee); // Apply number formatting
                document.getElementById('receivedFee').textContent = numberFormat(
                    receivedFee); // Apply number formatting
                document.getElementById('dueFee').textContent = numberFormat(dueFee); // Apply number formatting
            } else {
                console.error('Failed to fetch data from server.');
            }
        };
    });

    // Helper function for number formatting


    // window.addEventListener('popstate', function(event) {
    //     if (event.state) {
    //         // Restore the selected class and section
    //         document.getElementById('selectClass').value = event.state.selectedClass;
    //         document.getElementById('selectSection').value = event.state.selectedSection;

    //         // Restore table data
    //         document.getElementById('table-body').innerHTML = event.state.tableData;
    //         document.getElementById('totalFee').textContent = event.state.totalFee;
    //         document.getElementById('receivedFee').textContent = event.state.receivedFee;
    //         document.getElementById('dueFee').textContent = event.state.dueFee;

    //         // Restore scroll position
    //         window.scrollTo(0, event.state.scrollPosition);

    //         // Show or hide the search section
    //         document.querySelector('.search-section').style.display = event.state.searchSectionVisible ? 'block' :
    //             'none';
    //     }
    // });

    // Restore state on page reload
    document.addEventListener('DOMContentLoaded', function() {
        if (history.state) {
            document.dispatchEvent(new Event('popstate'));
        }
    });



    // Step 3: View Details Button - AJAX request
    document.getElementById('viewDetailsBtn').addEventListener('click', function() {
        const selectedRow = document.querySelector('.selected-row');
        const userId = row.userId;

        if (selectedRow) {
            const userId = this.dataset.userId;
            window.location.href = `student_fees?userId=${encodeURIComponent(userId)}`;
        }
        // if (userId) {
        //     // Save the state of the entire page
        //     const state = {
        //         selectedClass: document.getElementById('selectClass').value,
        //         selectedSection: document.getElementById('selectSection')
        //             .value,
        //         tableData: document.getElementById('table-body').innerHTML,
        //         totalFee: document.getElementById('totalFee').textContent,
        //         receivedFee: document.getElementById('receivedFee')
        //             .textContent,
        //         dueFee: document.getElementById('dueFee').textContent,
        //         scrollPosition: window.scrollY,
        //         searchSectionVisible: document.querySelector(
        //             '.search-section').style.display !== 'none',
        //     };

        //     history.pushState(state, '', window.location.href);
        //     window.location.href =
        //         `student_fees?userId=${encodeURIComponent(userId)}`;
        // }
    });

    // Step 4: Highlight row and show details
    function highlightRowAndShowDetails(rowElement, userId) {
        const selectedRow = document.querySelector('.selected-row');
        if (selectedRow) {
            selectedRow.classList.remove('selected-row');
        }
        rowElement.classList.add('selected-row');
        document.getElementById('viewDetailsBtn').disabled = false;
        document.getElementById('viewDetailsBtn').dataset.userId = userId;
    }

    // Step 5: Print Functionality
    // function openReceipt() {
    //     const receiptContainer = document.getElementById("receipt-container");

    //     // Inject the current page's styles into the receipt
    //     const styles = Array.from(document.styleSheets)
    //         .map(sheet => {
    //             try {
    //                 return Array.from(sheet.cssRules)
    //                     .map(rule => rule.cssText)
    //                     .join(' ');
    //             } catch (e) {
    //                 return '';
    //             }
    //         })
    //         .join(' ');

    //     // Create a <style> tag for the receipt and inject the styles
    //     const styleTag = document.createElement('style');
    //     styleTag.innerHTML = styles;
    //     receiptContainer.appendChild(styleTag);

    //     // Show the receipt and print it
    //     receiptContainer.style.display = "block"; // Show the receipt
    //     window.print();
    //     receiptContainer.style.display = "none"; // Hide after printing
    // }

    function toggleSearchSection() {
        const searchSection = document.querySelector('.search-section');
        // Toggle the display property
        if (searchSection.style.display === "none") {
            searchSection.style.display = "block";
        } else {
            searchSection.style.display = "none";
        }
    }

    function checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const classParam = urlParams.get('class');

        if (classParam) {
            let [className, section] = classParam.split("'").map(s => s.trim());

            // Prepend "Class " if it's missing
            if (!className.startsWith("Class")) {
                className = `Class ${className}`;
            }

            // Log the class and section values
            console.log('Class:', className);
            console.log('Section:', section);

            document.getElementById('selectClass').value = className;
            populateSections(className);

            setTimeout(() => {
                document.getElementById('selectSection').value = section;
                document.getElementById('searchBtn').disabled = false;

                document.getElementById('searchBtn').click();
            }, 500);
        } else {
            console.log('No class parameter found in the URL.');
        }
    }
    // Utility: Populate sections dynamically based on the selected class
    function populateSections(selectedClass) {
        const sectionSelect = document.getElementById('selectSection');
        const sections = <?php echo json_encode($sections); ?>;
        sectionSelect.innerHTML = '<option value="">Select Section</option>';

        if (selectedClass && sections[selectedClass]) {
            sectionSelect.disabled = false;
            sections[selectedClass].forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                sectionSelect.appendChild(option);
            });
        } else {
            sectionSelect.disabled = true;
        }
    }

    window.onload = checkUrlParams;
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
        /* Add right padding */
        border: 1px solid #ccc;
        border-radius: 5px;
        padding-bottom: 20px;
        font-size: 16px;
    }

    /* Main Styles */
    .title-bar {
        background-color: #007bff;
        color: white;
        margin-top: 20px;
        font-weight: bold;
        text-align: center;
        /* Centers the text horizontally */
        font-size: 24px;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        /* You can increase this for larger text size */
    }

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

    .buttons {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 20px;

    }

    .table-container {
        margin-top: 20px;

    }

    table th {
        background-color: #006400;
        color: white;
        padding: 8px;
    }

    table tbody tr {
        border-bottom: 1px solid #ddd;
        cursor: pointer;
    }

    .selected-row {
        background-color: #d1e7dd !important;
        transition: background-color 0.3s ease;
    }

    table tbody tr:hover {
        background-color: #b0b0b0 !important;
    }

    .footer-row {
        font-weight: bold;
        background-color: #585652;
        color: white;
    }

    .table-scroll {
        max-height: 300px;
        /* Adjust the height as per your requirement */
        overflow-y: auto;
        border: 1px solid #ddd;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 10;

    }

    .table tfoot {
        position: sticky;
        bottom: 0;
        background-color: #f8f9fa;
        /* Same as the header for consistency */
        z-index: 10;
    }

    .table-header {
        color: white;
        padding: 10px;
    }




    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }

        #table,
        #table * {
            visibility: visible;
        }

        #table {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Selected Row Highlight */


        /* Optional: Adjust hover effect */
        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }


        /* Table Styles for Print */
        .table,
        .table th,
        .table td {
            border: 1px solid #dee2e6;
        }

        .table th {
            background-color: #28a745;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .table td {
            padding: 10px;
            text-align: center;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .table tbody tr:nth-child(even) {
            background-color: #e9ecef;
        }

        .footer-row {
            font-weight: bold;
            background-color: #f1f1f1;
        }

        .footer-row td {
            text-align: center;
            background-color: #d1ecf1;
        }

        .header,
        .sub-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .sub-header {
            font-size: 18px;
            color: #333;
        }

        .info-bar {
            margin-top: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            font-size: 16px;
        }

        .info-box strong {
            color: #007bff;
        }
    }
   

</style>