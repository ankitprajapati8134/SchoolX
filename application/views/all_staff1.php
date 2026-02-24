<div class="content-wrapper">
    <section class="content">

        <!-- ================= PAGE HEADER ================= -->
        <div class="page-header-custom">

            <div class="header-left">
                <h2>
                    <i class="fa fa-users text-primary"></i>
                    All Staff
                </h2>

                <!-- Breadcrumb -->
                <div class="breadcrumb-custom">
                    <a href="<?= base_url('dashboard'); ?>">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                    <span>/</span>
                    <span class="active">All Staff</span>
                </div>
            </div>

            <div class="header-right">
                <a href="<?= base_url('staff/new_staff'); ?>" class="btn btn-primary btn-xl">
                    <i class="fa fa-plus"></i> Add New Staff
                </a>

                <a href="<?= base_url('staff/master_staff'); ?>" class="btn btn-warning btn-xl">
                    <i class="fa fa-user-plus"></i> Master Staff
                </a>
            </div>

        </div>


        <!-- ================= TABLE CARD ================= -->
        <div class="box box-primary staff-table-card">
            <div class="box-body table-responsive">

                <table class="table table-hover table-bordered text-center example">
                    <thead>
                        <tr class="table-header-row">
                            <th style="width:40px;">
                                <input type="checkbox" id="selectAllStaff">
                            </th>
                            <th style="width:60px;">SNo.</th>
                            <th style="width:70px;">Avatar</th>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Position</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($staff as $s1):
                            if (!is_array($s1)) continue;

                            $profilePic =
                                !empty($s1['ProfilePic'])
                                ? $s1['ProfilePic']
                                : (!empty($s1['Doc']['ProfilePic'])
                                    ? $s1['Doc']['ProfilePic']
                                    : '');
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="row-checkbox-staff">
                            </td>

                            <td><?= $i++ ?></td>

                            <td>
                                <?php if ($profilePic): ?>
                                <img src="<?= htmlspecialchars($profilePic) ?>" class="staff-avatar"
                                    onerror="this.src='<?= base_url('tools/dist/img/user2-160x160.jpg') ?>'">
                                <?php else: ?>
                                <img src="<?= base_url('tools/dist/img/user2-160x160.jpg') ?>" class="staff-avatar">
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($s1['User ID'] ?? 'N/A') ?></td>

                            <td class="text-left">
                                <strong><?= htmlspecialchars($s1['Name'] ?? 'N/A') ?></strong>
                            </td>

                            <td><?= htmlspecialchars($s1['Gender'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($s1['Position'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($s1['Phone Number'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($s1['Email'] ?? 'N/A') ?></td>

                            <td>
                                <a href="<?= base_url('staff/teacher_profile/' . ($s1['User ID'] ?? '')) ?>"
                                    class="btn btn-success btn-xs">
                                    <i class="fa fa-eye"></i>
                                </a>

                                <a href="<?= base_url('staff/edit_staff/' . ($s1['User ID'] ?? '')) ?>"
                                    class="btn btn-warning btn-xs">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

            </div>
        </div>

    </section>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

    /* STOP DATATABLE SORT FROM CHECKBOX CLICKS */
    $(document).on('click', '#selectAllStaff, .row-checkbox-staff', function(e) {
        e.stopPropagation();
    });

    /* SELECT ALL */
    $(document).on('change', '#selectAllStaff', function() {
        $('.example tbody .row-checkbox-staff').prop('checked', this.checked);
    });

    /* INDIVIDUAL CHECKBOX */
    $(document).on('change', '.row-checkbox-staff', function() {
        const total = $('.example tbody .row-checkbox-staff').length;
        const checked = $('.example tbody .row-checkbox-staff:checked').length;
        $('#selectAllStaff').prop('checked', total === checked);
    });

});
</script>

<style>
.content {
    background: #f4f6f9;
    padding: 40px;
}

/* ================= HEADER ================= */

.page-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.page-header-custom h2 {
    margin: 0;
    font-weight: 600;
}

.badge-primary {
    background: #1e88e5;
    padding: 6px 10px;
    font-size: 13px;
    border-radius: 20px;
    margin-left: 8px;
}

.header-right .btn {
    margin-left: 8px;
}

/* ================= BREADCRUMB ================= */

.breadcrumb-custom {
    margin-top: 6px;
    font-size: 13px;
    color: #6c757d;
}

.breadcrumb-custom a {
    text-decoration: none;
    color: #6c757d;
    transition: 0.2s;
}

.breadcrumb-custom a:hover {
    color: #1e88e5;
}

.breadcrumb-custom span {
    margin: 0 6px;
}

.breadcrumb-custom .active {
    color: #adb5bd;
    font-weight: 500;
}

/* ================= TABLE CARD ================= */

.staff-table-card {
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

/* Table Header */
.table-header-row {
    background: #1e88e5;
    color: #fff;
}

/* Avatar */
.staff-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
}

/* Table Text */
.table>thead>tr>th,
.table>tbody>tr>td {
    font-size: 13px;
    vertical-align: middle;
}

/* Name Alignment */
.table td.text-left {
    text-align: left !important;
    padding-left: 14px;
}

/* Buttons */
.table .btn-xs {
    padding: 4px 8px;
    border-radius: 4px;
}

/* Checkbox */
.table input[type="checkbox"] {
    cursor: pointer;
}

/* Header */
.box.box-warning .box-header {
    background: #F5AF00;
    color: #fff;
}

.box.box-warning .box-title,
.box.box-warning i {
    color: #fff;
}

/* Avatar */
.staff-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

/* Table text */
.table>thead>tr>th,
.table>tbody>tr>td {
    font-size: 13px;
    vertical-align: middle;
}

/* Name alignment */
.table td.text-left {
    text-align: left !important;
    padding-left: 12px;
}

/* Buttons */
.table .btn-xs {
    padding: 4px 7px;
}

/* Checkbox */
.table input[type="checkbox"] {
    cursor: pointer;
}
</style>