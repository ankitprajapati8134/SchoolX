<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">

            <!-- PAGE TITLE -->
            <div class="card card-warning shadow-sm mb-4">
                <div class="card-header text-center bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fa fa-bar-chart"></i> Fee Management Panel
                    </h4>
                </div>
            </div>

            <!-- FILTER SECTION -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form class="row align-items-end">

                        <div class="col-md-4">
                            <label class="font-weight-bold">Select Class</label>
                            <select class="form-control form-control-lg" id="selectClass">
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= htmlspecialchars($class) ?>">
                                        <?= htmlspecialchars($class) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="font-weight-bold">Select Section</label>
                            <select class="form-control form-control-lg" id="selectSection" disabled>
                                <option value="">Select Section</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary btn-lg btn-block" id="searchFees">
                                <i class="fa fa-search"></i> Load Fees
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- MONTHLY FEES (UNCHANGED TABLE STRUCTURE) -->
            <div class="box box-success">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Monthly Fees Chart</h3>
                </div>

                <div class="box-body table-responsive table-container" id="monthlyFees">
                    <table id="feesTable" class="table table-bordered table-hover text-center">
                        <thead>
                            <tr class="bg-green">
                                <th>Fee Title</th>
                                <th>April</th>
                                <th>May</th>
                                <th>June</th>
                                <th>July</th>
                                <th>August</th>
                                <th>September</th>
                                <th>October</th>
                                <th>November</th>
                                <th>December</th>
                                <th>January</th>
                                <th>February</th>
                                <th>March</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="bg-gray text-center">
                                <td><strong>Monthly Total</strong></td>
                                <?php for ($i = 0; $i < 12; $i++): ?>
                                    <td class="monthly-total"><strong>0.00</strong></td>
                                <?php endfor; ?>
                                <td class="overall-total"><strong>0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- COPY BUTTON -->
            <div class="text-right">
                <button class="btn btn-info" id="copycolfees">
                    <i class="fa fa-copy"></i> Copy April to All Months
                </button>
            </div>

            <!-- YEARLY FEES (UNCHANGED STRUCTURE) -->
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Yearly Fees Chart</h3>
                </div>

                <div class="box-body table-responsive table-container" id="yearlyFees">
                    <table id="feesTable2" class="table table-bordered table-hover text-center">
                        <thead>
                            <tr class="bg-aqua">
                                <th>SNO.</th>
                                <th>Fees Title</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td class="text-center"><strong>Total</strong></td>
                                <td id="totalFeesValue">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- OVERALL TOTAL -->
            <div class="box box-default">
                <div class="box-body text-right">
                    <h3><strong>OVERALL TOTAL:
                            <span id="totalFeesCell">0.00</span></strong>
                    </h3>

                    <button type="button"
                        id="saveButton"
                        onclick="saveUpdatedFees()"
                        class="btn btn-success"
                        disabled>
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </div>

        </div>
    </section>
</div>







<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('selectClass');
        const sectionSelect = document.getElementById('selectSection');
        const searchButton = document.getElementById('searchFees');
        const totalFeesCell = document.getElementById('totalFeesCell');
        const saveButton = document.getElementById('saveButton'); // Ensure the save button is defined
        const sections = <?php echo json_encode($sections); ?>;

        // Initially disable the save button
        saveButton.disabled = true;

        classSelect.addEventListener('change', function() {
            const selectedClass = this.value;
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


        searchButton.addEventListener('click', function() {
            const selectedClass = classSelect.value;
            const selectedSection = sectionSelect.value;

            if (selectedClass && selectedSection) {
                fetch(
                        `<?php echo site_url('fees/fees_chart'); ?>?class=${encodeURIComponent(selectedClass)}&section=${encodeURIComponent(selectedSection)}`
                    )
                    .then(response => response.json())
                    .then(data => {
                        const monthlyTbody = document.querySelector('#monthlyFees tbody');
                        const yearlyTbody = document.querySelector('#yearlyFees tbody');

                        // Clear previous data
                        monthlyTbody.innerHTML = '';
                        yearlyTbody.innerHTML = '';

                        const feesData = data.fees;
                        const allMonths = ['April', 'May', 'June', 'July', 'August', 'September',
                            'October', 'November', 'December', 'January', 'February', 'March'
                        ];

                        // Populate monthly fees table
                        if (feesData && Object.keys(feesData).length > 0) {
                            const feeTitlesSet = new Set();

                            allMonths.forEach(month => {
                                const fees = feesData[month];
                                if (typeof fees === 'object') {
                                    Object.keys(fees).forEach(feeTitle => feeTitlesSet.add(
                                        feeTitle));
                                }
                            });

                            feeTitlesSet.forEach(feeTitle => {
                                const row = document.createElement('tr');
                                const titleCell = document.createElement('td');
                                titleCell.textContent = feeTitle;
                                row.appendChild(titleCell);

                                allMonths.forEach(month => {
                                    const cell = document.createElement('td');
                                    const value = feesData[month] && feesData[month][
                                        feeTitle
                                    ] !== undefined ? feesData[month][feeTitle] : 0;
                                    const input = document.createElement('input');
                                    input.type = 'text';
                                    input.className = 'numeric-input';
                                    input.value = value;

                                    // Enable save button when input changes
                                    input.addEventListener('input', function() {
                                        saveButton.disabled = false;
                                    });

                                    cell.appendChild(input);
                                    row.appendChild(cell);
                                });

                                const totalCell = document.createElement('td');
                                totalCell.className = 'total-cell';
                                row.appendChild(totalCell);

                                monthlyTbody.appendChild(row);
                            });

                            updateTotalFees();
                            document.querySelectorAll('.numeric-input').forEach(input =>
                                input.addEventListener('input', updateTotalFees)
                            );
                        } else {
                            monthlyTbody.innerHTML =
                                '<tr><td colspan="14">No fee data available.</td></tr>';
                        }

                        // Populate yearly fees table
                        if (feesData && feesData['Yearly Fees'] && Object.keys(feesData['Yearly Fees'])
                            .length > 0) {
                            let srNo = 1;
                            Object.entries(feesData['Yearly Fees']).forEach(([feeTitle, feeValue]) => {
                                const sanitizedTitle = feeTitle.replace(/[^a-zA-Z0-9]/g,
                                    ''); // Sanitize title for IDs

                                const row = document.createElement('tr');

                                const srNoCell = document.createElement('td');
                                srNoCell.textContent = srNo++;
                                row.appendChild(srNoCell);

                                const titleCell = document.createElement('td');
                                titleCell.textContent = feeTitle;
                                row.appendChild(titleCell);

                                const valueCell = document.createElement('td');
                                const input = document.createElement('input');
                                input.type = 'text';
                                input.id = `yearly-${sanitizedTitle}`;
                                input.value = feeValue;
                                input.className = 'numeric-input yearly-fee-input';

                                // Enable save button when input changes
                                input.addEventListener('input', function() {
                                    saveButton.disabled = false;
                                });

                                valueCell.appendChild(input);
                                row.appendChild(valueCell);

                                yearlyTbody.appendChild(row);

                            });
                            updateTotalFees();
                            document.querySelectorAll('.numeric-input').forEach(input =>
                                input.addEventListener('input', updateTotalFees)
                            );
                        } else {
                            yearlyTbody.innerHTML =
                                '<tr><td colspan="3">No yearly fee titles available.</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        const monthlyTbody = document.querySelector('#monthlyFees tbody');
                        const yearlyTbody = document.querySelector('#yearlyFees tbody');

                        monthlyTbody.innerHTML = '<tr><td colspan="14">Error fetching data.</td></tr>';
                        yearlyTbody.innerHTML = '<tr><td colspan="3">Error fetching data.</td></tr>';
                    });
            }
        });



        // Add the function for copying values from April to all other months
        document.getElementById('copycolfees').addEventListener('click', function() {
            const tbody = document.querySelector('#monthlyFees tbody');
            const months = ['April', 'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December', 'January', 'February', 'March'
            ];

            const aprilIndex = months.indexOf('April'); // Index of April

            if (aprilIndex === -1) {
                alert('April column not found!');
                return;
            }

            tbody.querySelectorAll('tr').forEach(row => {
                const inputs = row.querySelectorAll('.numeric-input');
                const aprilValue = parseFloat(inputs[aprilIndex]?.value) || 0; // Get April's value

                // Fill all other months with April's value
                inputs.forEach((input, index) => {
                    if (index !== aprilIndex) {
                        input.value = aprilValue;
                    }
                });
            });
            // Trigger the total calculation after copying values
            updateTotalFees();

            alert('Values copied from April to all other months!');
        });

    });

    function updateTotalFees() {
        // const tbody = document.querySelector('tbody');
        const tbody = document.querySelector('#monthlyFees tbody');

        const allMonths = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December',
            'January', 'February', 'March'
        ];
        let overallTotal = 0;
        const monthlyTotals = Array(allMonths.length).fill(0);

        // Calculate total from the main table
        tbody.querySelectorAll('tr').forEach((row, rowIndex) => {
            const inputs = row.querySelectorAll('.numeric-input');
            let rowTotal = 0;

            inputs.forEach((input, columnIndex) => {
                const value = parseFloat(input.value) || 0;
                rowTotal += value;
                if (rowIndex < inputs.length) {
                    monthlyTotals[columnIndex] += value;
                }
            });

            const totalCell = row.querySelector('.total-cell');
            if (totalCell) totalCell.textContent = numberFormat(rowTotal);

            overallTotal += rowTotal;
        });

        // Update monthly totals in the <tfoot>
        const monthlyTotalCells = document.querySelectorAll('tfoot .monthly-total');
        monthlyTotals.forEach((total, index) => {
            if (monthlyTotalCells[index]) {
                monthlyTotalCells[index].textContent = numberFormat(total);
            }
        });

        // Update overall total in the last cell of the <tfoot>
        const overallTotalCell = document.querySelector('tfoot .overall-total');
        if (overallTotalCell) {
            overallTotalCell.textContent = numberFormat(overallTotal);
        }

        // ✅ Calculate total fees from feesTable2
        let yearlyTotal = 0;
        document.querySelectorAll('#feesTable2 tbody tr').forEach(row => {
            const inputField = row.querySelector('td:nth-child(3) input'); // Get input inside the 3rd column
            if (inputField) {
                let value = parseFloat(inputField.value); // Get input value
                if (!isNaN(value)) {
                    yearlyTotal += value; // Accumulate total
                }
            }
        });

        console.log("Yearly Total:", yearlyTotal); // Debugging log

        // ✅ Update the <th id="totalFeesValue"> with yearlyTotal
        const totalFeesValue = document.getElementById('totalFeesValue');
        if (totalFeesValue) {
            totalFeesValue.textContent = numberFormat(yearlyTotal);
        }

        // ✅ Update totalFeesCell with the sum of overallTotal and yearlyTotal
        const totalFeesCell = document.getElementById('totalFeesCell');
        if (totalFeesCell) {
            totalFeesCell.textContent = numberFormat(overallTotal + yearlyTotal);
        }
    }



    function saveUpdatedFees() {
        const selectedClass = document.getElementById('selectClass').value;
        const selectedSection = document.getElementById('selectSection').value;

        if (!selectedClass || !selectedSection) {
            alert('Please select a class and section.');
            return;
        }

        // Prepare the key for class and section (e.g., "Class 8th 'A'")
        // const classWithSectionKey = `${selectedClass} '${selectedSection}'`;
// const classWithSectionKey = `${selectedClass}/${selectedSection}`;
const classWithSectionKey = selectedClass;

        // Initialize an empty object to store the updated fees data
        const updatedFees = {};

        // Iterate over each row in the table to get fee data for each month
        const tbody = document.querySelector('tbody');
        tbody.querySelectorAll('tr').forEach(row => {
            const feeTitle = row.cells[0].textContent; // Get the fee title (e.g., Tuition Fees)
            const inputs = row.querySelectorAll('.numeric-input');

            inputs.forEach((input, index) => {
                const month = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November',
                    'December', 'January', 'February', 'March'
                ][index];

                // Initialize the month if it doesn't exist in updatedFees
                if (!updatedFees[month]) {
                    updatedFees[month] = {};
                }

                // Add the fee for the month
                updatedFees[month][feeTitle] = parseFloat(input.value) || 0;
            });
        });

        // Collect Yearly Fees data
        const yearlyFeesInputs = document.querySelectorAll('.yearly-fee-input');
        const yearlyFees = {};

        yearlyFeesInputs.forEach(input => {
            const feeTitle = input.id.replace('yearly-', ''); // Extract fee title from ID
            yearlyFees[feeTitle] = parseFloat(input.value) || 0; // Convert value to number, default to 0
        });

        // Include Yearly Fees in the updatedFees object
        updatedFees['Yearly Fees'] = yearlyFees;

        // Log the data to check if it looks correct
        console.log('Updated fees:', updatedFees);

        // Send the updated fees data to the backend as a JSON payload
        fetch('<?php echo site_url('fees/save_updated_fees'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                // body: JSON.stringify({
                //     [classWithSectionKey]: updatedFees // Wrap the class and section key into its own object
                // })
                body: JSON.stringify({
    class: selectedClass,
    section: selectedSection,
    fees: updatedFees
})

            })
            .then(response => {
                if (response.ok) {
                    alert('Fees updated successfully');
                    // saveButton.disabled = true; // Disable the save button after successful update
                    document.getElementById('saveButton').disabled = true;
                } else {
                    alert('Failed to update fees');
                }
            })
            .catch(error => console.error('Save error:', error));
    }
</script>




<style>
    .card {
        border-radius: 12px;
    }

    .numeric-input {
        width: 80px;
        text-align: center;
        border-radius: 6px;
    }

    .table thead th {
        background: #2c3e50;
        color: #fff;
    }

    tfoot {
        background: #f4f6f9;
        font-weight: bold;
    }

    #saveButton {
        position: sticky;
        bottom: 10px;
        right: 20px;
    }

    /* Fee module header */
    .fee-title-box>.box-header {
        background: #F5AF00;
        color: #fff;
    }

    .fee-title-box .box-title,
    .fee-title-box i {
        color: #fff;
    }

    /* Inputs */
    .numeric-input {
        width: 60px;
        text-align: center;
    }

    /* Sticky footer */
    .table tfoot {
        position: sticky;
        bottom: 0;
        z-index: 5;
    }
</style>