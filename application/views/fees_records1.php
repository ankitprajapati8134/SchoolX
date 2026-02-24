<div class="content-wrapper">
    <div class="page_container">
        <div class="container" style="border: 2px solid #dddddd; padding-right: 20px; margin-right: 20px;">
            <h1 style="background-color: #007bff; color: white; padding: 10px;">Fees Records (Class Wise)</h1>
            <?php if (!empty($fees_record_matrix)): ?>
                <div class="table-wrapper">
                <table id="feeTable">
                <thead>
                    <tr>
                        <th>Classes</th>
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
                        <th>Yearly Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fees_record_matrix as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['class']) ?></td>
                    <?php foreach ($row['amounts'] as $amount): ?>
                        <td><?= number_format($amount) ?></td>
                    <?php endforeach; ?>
                    <td><?= number_format($row['total']) ?></td>
                </tr>
            <?php endforeach; ?>
                    
                </tbody>
                <tfoot>
    <tr>
        <td>Total</td>
        <?php
        $columnTotals = array_fill(0, 12, 0);
        $grandTotal = 0;

        foreach ($fees_record_matrix as $row) {
            foreach ($row['amounts'] as $monthIndex => $amount) {
                $columnTotals[$monthIndex] += $amount;
            }
            $grandTotal += $row['total'];
        }

        foreach ($columnTotals as $total) {
            echo '<td>' . number_format($total) . '</td>';
        }
        ?>
        <td><?= number_format($grandTotal) ?></td>
    </tr>
</tfoot>

            </table>
    </div>
            <?php else: ?>
    <p>No fee records available to display.</p>
<?php endif; ?>
            <div class="btn-container">
            <button id="viewBtn" class="btn btn-success" disabled>View Details</button>
                <!-- <button id="closeBtn">Close</button> -->
                
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tableRows = document.querySelectorAll("#feeTable tbody tr");
    const viewBtn = document.getElementById("viewBtn");
    let selectedRow = null;

    tableRows.forEach(row => {
        row.addEventListener("click", function () {
            // Remove selection from previous row
            if (selectedRow) {
                selectedRow.classList.remove("selected");
            }

            // Select the clicked row
            row.classList.add("selected");
            selectedRow = row;

            // Show the View button
            // viewBtn.style.display = "inline-block";
            viewBtn.disabled=false;

        });

        row.addEventListener("dblclick", function () {
            goToDueFees();
        });
    });

    viewBtn.addEventListener("click", goToDueFees);

    function goToDueFees() {
    if (selectedRow) {
        const className = selectedRow.querySelector("td:first-child").textContent.trim();
        window.location.href = `class_fees?class=${encodeURIComponent(className)}`;
    }
}

});

</script>

<style>
body {
    font-family: Arial, sans-serif;
}

.container {
    width: 100%;
    border: 2px solid #ccc;
    padding-bottom: 20px;
}

h1 {
    text-align: center;
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-radius: 5px;
}
.table-wrapper {
    max-height: 400px; /* Set height for scrolling */
    overflow-y: auto; /* Enable vertical scrolling */
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #ccc;
    margin-top: 20px;
    table-layout: fixed;
    word-wrap: break-word; 
}


thead th, tfoot td {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
    text-align: center;
    padding: 2px;
    position: sticky;
    z-index: 2;
}
/* First column background and bold text */
thead th:first-child,
tbody td:first-child {
    text-align: center;
    background-color: #4CAF50; /* Green background for first column */
    color: white; /* White text */
    font-weight: bold; /* Bold text */
    padding: 8px; 
}

thead th {
    top: 0; /* Fix header to top */
}

tfoot td {
    bottom: 0; /* Fix footer to bottom */
}
tbody td {
    text-align: center;
    padding: 6px;
   
}
tbody tr td:first-child {
    text-align: center; /* Align first column to the left */
}

tbody tr:hover {
    background-color: #b0b0b0;
    cursor: pointer;
}

tbody tr.selected {
    background-color: #d1e7dd;
    font-weight: bold;
}

tfoot td {
    font-weight: bold;
    background-color: #585652;
    color: white;
    text-align: center;
    padding: 8px; 
}

tfoot td:empty {
    text-align: center; /* Align empty cells in footer */
}
/* Apply transparency to the last column */
tbody td:last-child {
    background-color: rgba(100, 149, 237, 0.2); /* Light blue with transparency */
}

.btn-container {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;

}

.btn-container button {
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    margin-left: 10px;
}



/* Responsive Design */
@media (max-width: 768px) {
    table {
        font-size: 12px;
    }
    
    h1 {
        font-size: 24px;
    }

    .btn-container button {
        padding: 8px 12px;
        font-size: 12px;
    }
    thead th, tfoot td, tbody td {
        padding: 8px; /* Reduce padding on smaller screens */
    }
}
.table-wrapper {
    overflow-x: auto; /* Enable horizontal scrolling */
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on touch devices */
}
</style>
