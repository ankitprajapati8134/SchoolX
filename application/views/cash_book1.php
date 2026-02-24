<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <!-- Header Section -->
            <div class="title-bar">
                <h2><i class="fa fa-book"></i> Cash Book</h2>
            </div>
            <h3 id="accountname" class="text-center"></h3>

            <div id="cashbook" class="table-responsive">
                <?php
                // Function for Indian Number Formatting
                function formatIndianNumber($num)
                {
                    $num = str_replace(',', '', $num); // Remove existing commas
                    $num = (float) $num; // Convert to float

                    // Check if the number is negative
                    $isNegative = $num < 0;
                    $num = abs($num); // Work with absolute value

                    // Format with 2 decimal places
                    $decimalPart = number_format($num, 2, '.', '');

                    // Extract the integer part (before the decimal)
                    $parts = explode('.', $decimalPart);
                    $integerPart = $parts[0];
                    $decimalPart = isset($parts[1]) ? '.' . $parts[1] : '';

                    // Apply Indian formatting to the integer part
                    $length = strlen($integerPart);
                    if ($length > 3) {
                        $lastThree = substr($integerPart, -3);
                        $remaining = substr($integerPart, 0, $length - 3);
                        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
                        $formattedNumber = $remaining . ',' . $lastThree . $decimalPart;
                    } else {
                        $formattedNumber = $integerPart . $decimalPart;
                    }

                    // Add back the negative sign if applicable
                    return $isNegative ? "−" . $formattedNumber : $formattedNumber;
                }

                ?>

                <table id="cashBookTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Account Name</th>
                            <th>Opening Balance</th>
                            <th>Total Received</th>
                            <th>Total Payment</th>
                            <th>Current Balance</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)) : ?>
                        <?php $srNo = 1; ?>
                        <?php foreach ($accounts as $account) : ?>
                        <tr>
                            <td><?= $srNo++; ?></td>
                            <td><?= htmlspecialchars($account['Account Name']); ?></td>
                            <td>₹ <?= formatIndianNumber($account['Opening Balance']); ?></td>
                            <td>₹ <?= formatIndianNumber($account['Total Received']); ?></td>
                            <td>₹ <?= formatIndianNumber($account['Total Payment']); ?></td>
                            <td><strong>₹ <?= formatIndianNumber($account['Current Balance']); ?></strong></td>
                            <!-- <td>
                <button class="btn btn-info btn-sm view-btn"
                    data-account="<?= htmlspecialchars($account['Account Name']); ?>"
                    data-balance="<?= floatval(str_replace(',', '', $account['Current Balance'] ?? 0)); ?>">
                    <i class="fa fa-eye"></i> View
                </button>
            </td> -->
                        </tr>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center">No accounts found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Month-wise Table -->
            <div id="monthtable" style="display:none;">
                <div style="background-color: #006400; margin-top: 2px;">
                    <h2 class="text-center text-white" style="color: white; ">Month Wise</h2>
                </div>


                <div class="table-container table_wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Opening</th>
                                <th>Received</th>
                                <th>Payment</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody id="monthCashBookTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td></td>
                                <td id="monthCashBookTableBodytotalReceived"></td>
                                <td id="monthCashBookTableBodytotalPayments"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Date-wise Table -->
            <div id="datetable" style="display:none;">
                <div style="background-color: #006400; margin-top: 0;">
                    <h2 class="text-center text-white" style="background-color: #006400; color: white;">Date Wise</h2>
                </div>

                <div class="table-container table_wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Opening</th>
                                <th>Received</th>
                                <th>Payment</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody id="DateCashBookTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td></td>
                                <td id="DateCashBookTableBodytotalReceived"></td>
                                <td id="DateCashBookTableBodytotalPayments"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Day-wise Table -->
            <div id="detailtable" style="display:none;">
                <div style="background-color: #006400; margin-top: 0;">
                    <h2 class="text-center text-white" style="background-color: #006400; color: white;">Day Wise</h2>
                </div>

                <div class="table-container table_wrapper">
                    <table id="detailCashBookTable" class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Received</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody id="detailCashBookTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td></td>
                                <td id="detailCashBookTableBodytotalReceived"></td>
                                <td id="detailCashBookTableBodytotalPayments"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="padding-top: 10px;">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="reference">Reference:</label>
                            <input id="reference" type="text" class="form-control" placeholder="Reference">
                        </div>

                    </div>
                </div>
            </div>

            <!-- Buttons Section -->
            <div class="row mb-3 buttons">
                <div class="col-md-12 text-center" id="showmonths">
                    <button class="btn btn-warning" disabled>Show</button>
                </div>
                <div class="col-md-12 text-center" id="buttons" style="display:none;">
                    <button id="viewbtn" class="btn btn-success" disabled>View</button>
                    <!-- <button class="btn btn-info" onclick="printTable()">Print</button> -->

                    <button id="backBtnMonth" style="display:none;" class="btn btn-primary">Back</button>
                    <button id="backBtndate" style="display:none;" class="btn btn-primary">Back</button>
                    <button id="backBtndetail" style="display:none;" class="btn btn-primary">Back</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    let selectedAccount = null; // Variable to store selected account name

    // Handle row selection in cashBookTable
    $('#cashBookTable tbody').on('click', 'tr', function() {
        // Remove "selected" class from all rows and add it only to the clicked row
        $('#cashBookTable tbody tr').removeClass('selected');
        $(this).addClass('selected');

        // Get the account name from the 2nd column (index 1, since index starts from 0)
        let selectedAccount = $(this).find('td:eq(1)').text().trim();

        // Enable the Show button only if a row is selected
        if (selectedAccount) {
            $('#showmonths button').prop('disabled', false).data('account', selectedAccount);
        } else {
            $('#showmonths button').prop('disabled', true).removeData('account');
        }
    });


    // Handle double-click to trigger show function
    $('#cashBookTable tbody').on('dblclick', 'tr', function() {
        $('#showmonths button').click(); // Trigger the show button
    });

    // Handle Show button click event
    $('#showmonths').on('click', function() {
        let selectedAccount = $(this).find('button').data('account'); // Get the stored account name

        console.log("Selected Account:", selectedAccount); // Debugging log

        if (!selectedAccount || selectedAccount.trim() === "") {
            alert('Please select an account first.');
            return;
        }

        $.ajax({
            url: 'cash_book_month',
            type: 'GET',
            data: {
                account_name: selectedAccount
            }, // Send account name in AJAX request
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {

                    $('#accountname').text("Account: " + selectedAccount);
                    const data = result.data;
                    let tableBody = '';

                    data.forEach(row => {
                        tableBody += `
                    <tr>
                        <td><strong>${row.month}</strong></td>
                        <td>${numberFormat(row.opening)}</td>
                        <td>${numberFormat(row.received)}</td>
                        <td>${numberFormat(row.payments)}</td>
                        <td>${numberFormat(row.balance)}</td>
                    </tr>`;
                    });

                    $('#monthCashBookTableBody').html(tableBody);
                    $("#monthtable").show();
                    $("#buttons").show();
                    $("#showmonths").hide();
                    $("#cashbook").hide();
                    $("#backBtnMonth").show();



                    // Calculate totals after loading table data
                    calculateTotals('monthCashBookTableBody');

                } else {
                    alert(result.message || 'Error fetching data');
                }
            },
            error: function() {
                alert('An error occurred while processing your request.');
            }
        });
    });

    // Row selection and AJAX functionality for the month table
    $("#monthCashBookTableBody").on("click", "tr", function() {
        $(this).addClass("selected").siblings().removeClass("selected");
        $(".btn-success").prop("disabled", false); // Enable "View" button
    });

    $("#monthCashBookTableBody").on("dblclick", "tr", function() {
        const selectedAccount = $('#accountname').text();
        sendAjaxToCashBookDates($(this), selectedAccount);
    });

    $(".btn-success").click(function() {
        const selectedRow = $("#monthCashBookTableBody tr.selected");
        const selectedAccount = $('#accountname').text();
        if (selectedRow.length > 0) {
            sendAjaxToCashBookDates(selectedRow, selectedAccount);
        }
    });
    $("#backBtnMonth").click(function() {
        $("#cashbook").show();
        $("#monthtable").hide();
        $("#backBtnMonth").hide();
        $("#buttons").hide();
        $("#showmonths").show();
        $('#accountname').text('');




    });
    $("#backBtndate").click(function() {
        $("#monthtable").show();
        $("#datetable").hide();
        $("#backBtndate").hide();
        $("#backBtnMonth").show();


    });
    $("#backBtndetail").click(function() {
        $("#backBtndate").show();
        $("#backBtndetail").hide();


        $("#detailtable").hide();
        $("#datetable").show();
    });

    // Row selection and AJAX functionality for the date table
    $("#DateCashBookTableBody").on("click", "tr", function() {
        $(this).addClass("selected").siblings().removeClass("selected");
        $(".btn-success").prop("disabled", false); // Enable "View" button
    });

    $("#DateCashBookTableBody").on("dblclick", "tr", function() {
        sendAjaxToCashBookDetails($(this));
    });

    $(".btn-success").click(function() {
        const selectedRow = $("#DateCashBookTableBody tr.selected");
        if (selectedRow.length > 0) {
            sendAjaxToCashBookDetails(selectedRow);
        }
    });



    // Initialize DataTable
    let accountTable = $('#cashBookTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        responsive: true, // ✅ Ensures responsiveness
        pageLength: 15, // ✅ Fixed to 15 rows per page
        autoWidth: false,
        dom: '<"top"Bf>rt<"bottom"ip>', // ✅ Export buttons placed properly
        buttons: [{
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'LEGAL'
            },
            'copy', 'csv', 'excel', 'print'
        ],
        language: {
            emptyTable: "No data available in the table"
        },
        order: []
    });



    function sendAjaxToCashBookDates(row, account_name) {
        const selectedMonth = row.find("td:first").text().trim();
        const openingValue = row.find("td:nth-child(2)").text().trim();

        console.log("Selected Month:", selectedMonth);
        console.log("Opening Value:", openingValue);
        console.log("Account Name:", account_name); // Log account name to debug

        $.ajax({
            url: "cash_book_dates",
            type: "POST",
            data: {
                month: selectedMonth,
                opening: openingValue,
                account_name: account_name.trim() // Ensure account_name is properly sent
            },
            success: function(response) {
                const result = JSON.parse(response);

                // Check if response status is success and data exists
                if (result.status === "success" && Array.isArray(result.data)) {
                    const data = result.data;
                    const tbody = $("#DateCashBookTableBody").empty();

                    data.forEach((item) => {
                        tbody.append(`
                <tr>
                    <td>${item.date}</td>
                    <td>${numberFormat(item.opening)}</td>
                    <td>${numberFormat(item.received)}</td>
                    <td>${numberFormat(item.payments)}</td>
                    <td>${numberFormat(item.balance)}</td>
                </tr>`);
                    });

                    $("#monthtable").hide();
                    $("#datetable").show();
                    $('#showmonths').hide();
                    $('#backBtnMonth').hide();
                    $(".btn-success").prop("disabled", true);
                    $("#backBtndate").show();

                    // Calculate totals for date table
                    calculateTotals('DateCashBookTableBody');
                } else {
                    console.error("Unexpected response format:", result);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching date data:", error);
            }

        });
    }

    function sendAjaxToCashBookDetails(row) {
        const selectedDate = row.find("td:first").text();

        $.ajax({
            url: "cash_book_details",
            type: "POST",
            data: {
                date: selectedDate
            },
            success: function(response) {
                const data = JSON.parse(response);
                const tbody = $("#detailCashBookTableBody").empty();

                data.forEach((item) => {
                    const tr = $(`<tr data-reference="${item.reference}">
                    <td>${item.date}</td>
                    <td>${item.account}</td>
                    <td>${numberFormat(item.received)}</td>
                    <td>${numberFormat(item.payment)}</td>
                </tr>`);

                    tr.on("click", function() {
                        $(this).addClass("selected").siblings().removeClass(
                            "selected");
                        $("#reference").val($(this).data("reference"));
                    });

                    tbody.append(tr);
                });

                $("#datetable").hide();
                $("#detailtable").show();
                $(".btn-success").hide();
                $("#backBtndate").hide();
                $("#backBtndetail").show();

                // ✅ Destroy old DataTable if already initialized
                if ($.fn.DataTable.isDataTable("#detailCashBookTable")) {
                    $("#detailCashBookTable").DataTable().destroy();
                }

                // ✅ Reinitialize DataTable with new data
                $("#detailCashBookTable").DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    responsive: true,
                    pageLength: 10, // Set to 10 rows per page
                    autoWidth: false,
                    dom: '<"top"Bf>rt<"bottom"ip>',
                    buttons: [{
                            extend: "pdfHtml5",
                            orientation: "landscape",
                            pageSize: "LEGAL"
                        },
                        "copy", "csv", "excel", "print"
                    ],
                    language: {
                        emptyTable: "No data available"
                    },
                    order: [] // Disable initial sorting
                });

                // ✅ Calculate totals after table update
                calculateTotals("detailCashBookTableBody");
            },
            error: function(error) {
                console.error("Error fetching cash book details:", error);
            }
        });
    }


    function calculateTotals(tableBodyId) {
        let totalReceived = 0,
            totalPayments = 0;

        // Select rows from the passed tableBodyId
        const rows = document.querySelectorAll(`#${tableBodyId} tr`);
        rows.forEach(row => {
            const received = parseFloat(row.cells[2]?.innerText.replace(/,/g, '')) || 0;
            const payments = parseFloat(row.cells[3]?.innerText.replace(/,/g, '')) || 0;

            totalReceived += received;
            totalPayments += payments;
        });

        // Format totals using Indian number formatting
        document.getElementById(`${tableBodyId}totalReceived`).innerText = numberFormat(totalReceived);
        document.getElementById(`${tableBodyId}totalPayments`).innerText = numberFormat(totalPayments);
    }



});
</script>



<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;

}

.container {
    width: 90%;
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
    margin-top: 15px;
    font-weight: bold;
    text-align: center;
    padding: 2px;
    border-radius: 5px;
    margin-bottom: 15px;
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
    border: 1px solid #ccc;
}

table th {
    background-color: #006400;
    color: white;
    padding: 8px;
}

table tbody tr {
    /* border-bottom: 1px solid #ddd; */
    cursor: pointer;
}



table tbody tr:hover {
    background-color: #b0b0b0 !important;
}

/* .footer-row {
    font-weight: bold;
    background-color: #585652;
    color: white;
} */

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

tfoot {
    position: sticky;
    bottom: 0;
    background-color: #f8f9fa;
    /* Same as the header for consistency */
    z-index: 10;
    font-weight: bold;
    background-color: #585652;
    color: white;
}

.table-header {
    color: white;
    padding: 10px;
}

tbody tr.selected {
    background-color: #237BFC !important;
    color: white;
}
</style>