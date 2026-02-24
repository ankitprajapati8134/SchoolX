<div class="content-wrapper">
    <div class="page_container">
        <!-- Header Section -->
        <div class="header">
            School Profile
        </div>


        <!-- Profile Container -->
        <div class="profile-container">
            <!-- Profile Image and Basic Details -->
            <div class="profile-image">
                <img src="<?= $schoolData['Logo'] ?>" alt="School Logo"
                    onerror="this.src='http://localhost/Grader/school/tools/image/default-school.jpeg';">

                <div class="profile-header"><?= $schoolData['School Name'] ?? 'Default School Name' ?></div>
                <ul class="profile-description"
                    style="text-align: left; font-size: 14px; color: #585652; margin: 0; padding: 0; list-style: none; line-height: 1.6;">
                    Welcome to <strong><?= $schoolData['School Name'] ?? 'Default School Name' ?>.</strong><br>
                    <strong>Affiliated To:</strong> <?= $schoolData['Affiliated To'] ?? 'N/A' ?>.<br>
                    <strong>Our School is located near :</strong> <?= $schoolData['Address'] ?? 'N/A' ?><br>
                    <strong>Commitment:</strong> Committed to excellence in education.
                </ul>

            </div>

            <!-- Profile Specifications -->
            <div class="profile-specs">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th>School Id :</th>
                            <td><?= $schoolData['School Id'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Affiliation Number :</th>
                            <td><?= $schoolData['Affiliation Number'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Affiliated To :</th>
                            <td><?= $schoolData['Affiliated To'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Address :</th>
                            <td><?= $schoolData['Address'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Email :</th>
                            <td><?= $schoolData['Email'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Mobile Number :</th>
                            <td><?= $schoolData['Mobile Number'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number :</th>
                            <td><?= $schoolData['Phone Number'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Website :</th>
                            <td>
                                <a href="<?= $schoolData['Website'] ?? '#' ?>" target="_blank">
                                    <?= $schoolData['Website'] ?? 'N/A' ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Subscription :</th>
                            <td>
                                <?= $schoolData['subscription']['status'] ?? 'Inactive' ?>
                                <span style="color: red; font-weight: bold;">(<?= $daysLeft ?> days left)</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subscription Details Section -->
        <div class="subscription-wrapper">
            <h3 class="section-title">Subscription Details</h3>
            <div class="subscription-content">
                <table class="subscription-table">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Features</th>
                            <th>Payment Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?= $schoolData['subscription']['planName'] ?? 'N/A' ?></strong></td>
                            <td><strong>
                                    <?= isset($schoolData['subscription']['duration']['startDate']) ? 
                            date("d/m/Y", strtotime($schoolData['subscription']['duration']['startDate'])) : 'N/A'; ?>
                                </strong></td>
                            <td><strong>
                                    <?= isset($schoolData['subscription']['duration']['endDate']) ? 
                            date("d/m/Y", strtotime($schoolData['subscription']['duration']['endDate'])) : 'N/A'; ?>
                                </strong></td>
                            <td><strong><?= $schoolData['subscription']['status'] ?? 'Inactive' ?></strong></td>
                            <td>
                                <ul>
                                    <?php foreach ($schoolData['subscription']['features'] ?? [] as $feature): ?>
                                    <li><?= $feature ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <strong>Last Payment:</strong>
                                <?= $schoolData['paymentDetails']['lastPaymentAmount'] ?? 'N/A' ?><br>
                                <strong>Date:</strong>
                                <?= isset($schoolData['paymentDetails']['lastPaymentDate']) ? 
                            date("d/m/Y", strtotime($schoolData['paymentDetails']['lastPaymentDate'])) : 'N/A'; ?>
                                <br>
                                <strong>Total Amount :</strong>
                                <?= $schoolData['subscription']['amount']['totalAmount'] ?? 'N/A' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>


<!-- Search Modal -->
<!-- <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Search Student</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="searchName">Student Name</label>
                        <input type="text" class="form-control" id="searchName" placeholder="Enter student name">
                    </div>
                    <div class="form-group">
                        <label for="searchClass">Class</label>
                        <input type="text" class="form-control" id="searchClass" placeholder="Enter class">
                    </div>
                    <div class="form-group">
                        <label for="searchSection">Section</label>
                        <input type="text" class="form-control" id="searchSection" placeholder="Enter section">
                    </div>
                </form>
            </div>

        </div>
    </div>
</div> -->


<!-- <img src="http://localhost/Grader/school/tools/image/school.jpeg" alt="School Logo"> -->
<style>
.page_container {
    padding-top: 1px;
    background-color: #f8f9fa;
}

body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;

    }

.header {
    background-color: #007bff;
    color: white;
    text-align: center;
    padding: 15px;
    font-size: 24px;
    margin: 15px 10px 10px 10px;
    font-weight: bold;
}


/* .search-section {
        margin-top: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 13px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
    } */

.profile-container {
    margin: 20px auto;
    max-width: 900px;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
}

.profile-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background-color: #f7f1f1;
    border-radius: 8px;
    padding: 20px;
    overflow: hidden;
}

.profile-image img {
    width: 250px;
    /* Adjust width */
    height: 250px;
    /* Adjust height */
    object-fit: contain;
    /* Maintain aspect ratio */
    border-radius: 15px;
    /* Adjust corner radius */
    margin-bottom: 10px;
}


.profile-header {
    font-weight: bold;
    font-size: 24px;
    margin: 10px 0;
}

.profile-description {
    font-size: 14px;
    color: #585652;
    text-align: left;
}


.profile-specs {
    display: flex;
    font-size: 20px;

    flex-direction: column;
}

.profile-specs table {
    width: 100%;
    margin-bottom: 20px;
}

.profile-specs th {
    text-align: left;
    padding-right: 10px;
    margin-bottom: 5px auto;
    color: #495057;
}

.profile-actions .btn {
    margin-right: 10px;
}


/* Updated Styling for Subscription Section */
.subscription-wrapper {
    margin: 20px auto;
    max-width: 900px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 15px;
    color: #495057;
    text-align: center;
}

.subscription-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.subscription-table th,
.subscription-table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

.subscription-table th {
    background-color: #006400;
    color: white;
    font-weight: bold;
}

.subscription-table td {
    background-color: #f8f9fa;
    color: #495057;
}

.subscription-table ul {
    margin: 0;
    padding-left: 20px;
    list-style-type: disc;
}

.subscription-content {
    overflow-x: auto;
    /* Add horizontal scroll for smaller screens */
}

.subscription-wrapper table {
    font-size: 1.8rem;
    margin: 0 auto;
}

.subscription-wrapper td a {
    color: #007bff;
    text-decoration: none;
}

.subscription-wrapper td a:hover {
    text-decoration: underline;
}
</style>