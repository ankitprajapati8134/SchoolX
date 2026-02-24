<div class="content-wrapper">
    <div class="page_container">

        <div class="container mt-4">
            <div class="title-bar">
                Student Fee Receipts: <?php echo htmlspecialchars($_GET['userId'] ?? ''); ?>
            </div>


            <div class="col-md-6">
            </div>
            <div class="search-section my-3">
                <div class="row align-content-center">
                    <!-- Input and Fetch Button aligned to the left -->

                    <div class="col-md-4 justify-content-end align-items-end">
                        <div class="d-flex">
                            <input type="text" class="form-control me-2" id="UserId" placeholder="Enter User Id">
                            <button class="btn btn-primary" id="fetch" onclick="populateTable()">Fetch Details</button>
                        </div>
                    </div>
                    <!-- Search Student Button aligned to the right -->
                    <div class="col-md-2 justify-content-end align-items-end">
                        <button type="button" class="btn btn-primary" id="searchdetails" data-toggle="modal"
                            data-target="#searchModal">
                            <i class="fa fa-search" style="font-size: 16px;"></i>
                            <br>
                            <span>Search Student</span>
                        </button>
                    </div>
                </div>
            </div>



            <!-- Table to display the fee details -->
            <div class="table-container" id="print-section">
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Student Name / Father Name</th>
                            <th>Class</th>
                            <th>Fee Amount</th>
                            
                            <th>Fine</th>
                            <th>Mode</th>
                            <th>User Id</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">

                        <td colspan="8" class="table-message text-center">Enter the user ID to populate the table</td>
                        </tr>
                    </tbody>
                    <tfoot class="footer-row">
                        <tr>
                            <td colspan="4">TOTAL</td>
                            <td id="totalAmount"></td>
                            <!-- <td id="totalConveyance"></td> -->
                            <td id="totalFine"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer buttons -->
            <div class="buttons">
                <!-- <button class="btn btn-success" onclick="window.print()">Print</button> -->
                <button class="btn btn-success" onclick="printTable()">Print</button>

                <button class="btn btn-warning" onclick="window.location.reload()">Refresh</button>
                <!-- <button class="btn btn-info" id="backBtn" onclick="location.href='due_fees'">Back</button> -->
                <button class="btn btn-info" id="backBtn" onclick="history.back();">Back</button>


            </div>
        </div>

        <!-- Modal for Student Search -->
        <!-- <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentModalLabel">Search Student By Sr.No / Name</h5>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control" placeholder="Search Student By Name / Roll No">
                        <input type="text" class="form-control mt-3" placeholder="Search Student By Enrollment No">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel">
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
        </div>

    </div>
</div>

<script>
function printTable() {
    const feeDetailsSection = document.getElementById("print-section").innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = `
        <html>
        <head>
            <title>Print Fee Details</title>
            <style>
                @media print {
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    .header {
                        text-align: center;
                        font-size: 24px;
                        margin-bottom: 10px;
                        font-weight: bold;
                    }
                    .sub-header {
                        text-align: center;
                        font-size: 18px;
                        margin-bottom: 20px;
                    }
                    .table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                        border-top: 1px solid #dee2e6; /* Add top border */
                    }
                    .table th, .table td {
                        border: 1px solid #dee2e6;
                        padding: 10px;
                        text-align: center;
                        word-wrap: break-word;
                    }
                    .table th {
                        background-color: #28a745;
                        color: white;
                    }
                    .footer-row td {
                        background-color: #d1ecf1;
                        font-weight: bold;
                    }
                    .table-message {
                        color: #ff0000;
                        font-size: 16px;
                        padding: 15px;
                    }
                    .table-container {
                        width: 100%;
                        overflow-x: auto;
                    }
                    /* Prevent content from breaking off the page */
                    html, body {
                        height: auto;
                    }
                }
            </style>
        </head>
        <body>
            <h1 class="header">Fee Details</h1>
            <div class="sub-header">Summary of Student Fees</div>
            <div class="table-container">
                ${feeDetailsSection}
            </div>
        </body>
        </html>
    `;

    window.print();
    // Restore the original content after printing
    document.body.innerHTML = originalContent;
    location.reload();
}





const tableBody = document.getElementById('tableBody');
const totalAmount = document.getElementById('totalAmount');
// const totalConveyance = document.getElementById('totalConveyance');
const totalFine = document.getElementById('totalFine');

function populateTable() {
    const userIdInput = document.getElementById('UserId').value;
    const fetchButton = document.querySelector('.btn-primary');

    if (!userIdInput || isNaN(userIdInput)) {
        alert('Please enter a valid numeric User ID.');
        return;
    }

    fetchButton.textContent = 'Fetching...';
    fetchButton.disabled = true;

    fetch('<?= site_url("fees/fetch_fee_receipts"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: userIdInput
            })
        })
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = '';
            let totalf = 0,
                // totalc = 0,
                totalfi = 0;

            data.forEach(record => {
                // Convert to numbers for correct calculation
                const amount = Number(record.amount);
                // const convey = Number(record.convey);
                const fine = Number(record.fine);

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${record.receiptNo}</td>
                <td>${record.date}</td>
                <td>${record.student}</td>
                <td>${record.class}</td>
                <td>${numberFormat(amount)}</td>
               
                <td>${numberFormat(fine)}</td>
                <td>${record.account || ''}</td>
                <td>${record.Id}</td>
            `;
                tableBody.appendChild(tr);
                totalf += amount;
                // totalc += convey;
                totalfi += fine;
            });

            // Update totals with proper Indian number format
            totalAmount.textContent = numberFormat(totalf);
            // totalConveyance.textContent = numberFormat(totalc);
            totalFine.textContent = numberFormat(totalfi);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
    
            alert('Failed to fetch data, please try again.');
        })
        .finally(() => {
            fetchButton.textContent = 'Fetch Details';
            fetchButton.disabled = false;
        });
}


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
                                <td>${index + 1}</td>
                                <td>${student.user_id}</td>
                                <td>${student.name}</td>
                                <td>${student.father_name}</td>
                                <td >${student.class}</td>
                                <td class="text-center">
                                    <button class="btn btn-white p-0 select-btn" style="background-color: #006400; color: white; border: 1px solid #ccc;">
                                        Fill Out ➡
                                    </button>
                                </td>
                                
                            `;

                    // Add click event listener to the button in the row
                    row.querySelector('.select-btn').addEventListener('click', function() {
                        // Highlight the selected row
                        document.querySelectorAll('.table-row').forEach(tr => tr.classList
                            .remove('table-active'));
                        row.classList.add('table-active');


                        document.getElementById('UserId').value = student.user_id;
                        // Close the modal by removing Bootstrap classes and hiding the backdrop
                        document.getElementById('searchModal').classList.remove('show');
                        document.getElementById('searchModal').style.display = 'none';
                        document.body.classList.remove('modal-open');
                        document.querySelector('.modal-backdrop').remove();

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

function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const idParam = urlParams.get('userId');

    if (idParam) {
        // Log the class and section values
        console.log('id:', idParam);
        document.getElementById('UserId').value = idParam;
        setTimeout(() => {
            document.getElementById('fetch').click();
        }, 500);
    } else {
        console.log('No id found in the URL.');
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
    border: 1px solid #ccc;
    padding-bottom: 20px;
}

/* Title Bar Styling */
.title-bar {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-align: center;
    font-size: 24px;
    padding: 10px;
    border-radius: 5px;
    margin-top: 20px;
}



/* Search Section */
.search-section {
    margin-top: 20px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.d-flex {
    display: flex;
    align-items: center;
}


/* Optional: You can adjust the spacing between input and button */
input.form-control {
    margin-right: 10px;
}

/* Table Container */
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

/* Footer Buttons */
.buttons {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

#searchTable {
    margin-top: 20px;
    /* Adjust as needed */
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }

    .table-container,
    .table-container * {
        visibility: visible;
    }

    .table-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>