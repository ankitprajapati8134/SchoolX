
<div class="content-wrapper">
    <div class="page_container">
        <div class="header text-center bg-primary text-white">

            <h2 class="text-center bg-primary text-white">
                <i class="fa fa-book"></i> Day Book:
                <?php echo "Selected Account Type Name"; ?>
            </h2>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="container">
        <!-- <div class="panel panel-default"> -->
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="accountType">Account Type</label>
                    <select id="accountType" class="form-control">
                        <option selected>Select Account Type</option>
                        <option value="All">All</option>
                        <?php if (!empty($accountTypes)) : ?>
                            <?php foreach ($accountTypes as $key) : ?>
                                <option value="<?php echo htmlspecialchars($key); ?>">
                                    <?php echo htmlspecialchars($key); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="">No account types available</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fromDate" class="form-label">From Date:</label>
                    <input type="date" id="fromDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="toDate" class="form-label">To Date:</label>
                    <input type="date" id="toDate" class="form-control">
                </div>
                <div class="col-md-3 ">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" id="showaccount" style="width: 46%;">
                        Show
                    </button>
                </div>
            </div>
            <!-- </div> -->
        </div>
    </div>

    <!-- Table Section -->
    <div class="table_wrapper">
        <table class="table-responsive table table-bordered">
            <thead class="text-center bg-primary text-white" style="background-color: #30b549;">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Particulars</th>
                    <th>Cr Amt</th>
                    <th>Dr Amt</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody id="vouchersTableBody">

            </tbody>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="container text-center flex-end" style="margin-left: 390px; padding:10px;">
        <button class="btn btn-warning">Print</button>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.onload = function() {
        var today = new Date();

        // Calculate financial year start based on the current date
        var financialYearStart;
        if (today.getMonth() + 1 >= 4) { // If today is in or after April
            financialYearStart = new Date(today.getFullYear(), 3, 1); // 1st April of the current year
        } else { // If today is before April
            financialYearStart = new Date(today.getFullYear() - 1, 3, 1); // 1st April of the previous year
        }

        // Manually format the dates as YYYY-MM-DD
        function formatDate(date) {
            var day = ('0' + date.getDate()).slice(-2);
            var month = ('0' + (date.getMonth() + 1)).slice(-2); // Months are 0-based
            var year = date.getFullYear();
            return year + '-' + month + '-' + day;
        }

        // Set the values in the 'fromDate' and 'toDate' input fields
        document.getElementById("fromDate").value = formatDate(financialYearStart);
        document.getElementById("toDate").value = formatDate(today);
    };


    $(document).on('click', '#showaccount', function() {
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();
        const accountType = $('#accountType').val();

        $.ajax({
            url: '<?php echo site_url("account/day_book"); ?>',
            type: 'POST',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                accountType: accountType
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Clear existing table rows
                    const tableBody = $('#vouchersTableBody');
                    tableBody.empty();

                    // Check if there are any vouchers
                    if (response.data.length > 0) {
                        response.data.forEach(function(voucher) {
                            const row = `
                            <tr>
                                <td>${voucher.Date}</td>
                                <td>${voucher.Type}</td>
                                <td>${voucher.Particulars}</td>
                                <td>${voucher['Cr Amt'] || '0'}</td>
                                <td>${voucher['Dr Amt'] || '0'}</td>
                                <td>${voucher.Mode ? voucher.Mode : 'NA'}</td>
                            </tr>
                        `;
                            tableBody.append(row);
                        });
                    } else {
                        // If no data, show a "No data available" row
                        tableBody.append(`
                        <tr>
                            <td colspan="6">No data available.</td>
                        </tr>
                    `);
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('An error occurred while fetching data.');
            }
        });
    });
</script>





<style>
    /* Center the content */
    .content-wrapper {

        max-width: 1140px;
        /* Matches Bootstrap container width */
        padding: 15px;
        /* Add padding for breathing room */
        background-color: white;
        /* Add a white background for content */

    }


    /* Style for page header */
    .page_container {
        padding-top: 10px;
        margin-bottom: 20px;
        background-color: #337ab7;
        /* Bootstrap primary color */
        color: white;
        border-radius: 4px;
        /* Rounded corners */
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #222;
        /* Black border */
        padding: 8px;
        text-align: center;
    }

    tbody tr:hover {
        background-color: #e6e6e6;
        /* Optional: Add a hover effect */
    }
</style>