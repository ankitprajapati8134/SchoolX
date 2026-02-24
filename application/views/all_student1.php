<div class="content-wrapper">

    <section class="content-header custom-header">

        <div class="header-top clearfix">

            <div class="pull-left">
                <h1 class="page-title">
                    <i class="fa fa-users text-primary"></i> Student List
                </h1>
            </div>

            <div class="pull-right header-buttons">
                <a href="<?= base_url('student/master_student') ?>" class="btn btn-warning">
                    <i class="fa fa-upload"></i> Import Student
                </a>


                <a href="<?= base_url('student/studentAdmission') ?>" class="btn btn-success">
                    <i class="fa fa-plus"></i> Add New Student
                </a>
            </div>

        </div>

        <ol class="breadcrumb custom-breadcrumb">
            <li>
                <a href="<?= base_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
            </li>
            <li class="active">Student List</li>
        </ol>

    </section>

    <section class="content">
        <!-- TABLE -->
        <div class="box box-primary">
            <div class="box-body table-responsive">

                <table class="table table-bordered table-hover example">
                    <thead>
                        <tr class="bg-green">
                            <th style="width:40px;">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>SNo.</th>
                            <th style="width:70px;">Avatar</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Class</th>
                            <th>Section</th>

                            <th>Parent</th>
                            <th>Admission Date</th>
                            <th>DOB</th>
                            <th>Guardian Contact</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1;

                        usort($students, function ($a, $b) {
                            return strcmp($a['User Id'], $b['User Id']);
                        });
                        foreach ($students as $student): ?>

                            <tr>
                                <td>
                                    <input type="checkbox" class="row-checkbox">
                                </td>
                                <td><?= $i++ ?></td>
                                <td>
                                    <?php

                                    //we have to solve the error of this avtar code
                                    $profilePic =
                                        !empty($student['Profile Pic'])
                                        ? $student['Profile Pic']
                                        : (!empty($student['Doc']['PhotoUrl'])
                                            ? $student['Doc']['PhotoUrl']
                                            : base_url('tools/dist/img/user2-160x160.jpg'));
                                    ?>
                                    <img src="<?= htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8') ?>"
                                        class="student-avatar"
                                        alt="Student Avatar"
                                        onerror="this.src='<?= base_url('tools/dist/img/user2-160x160.jpg') ?>'">

                                </td>
                                <td><?= $student['User Id'] ?? 'N/A' ?></td>
                                <td class="text-left">
                                    <strong><?= $student['Name'] ?? 'N/A' ?></strong>
                                </td>
                                <td><?= $student['Gender'] ?? 'N/A' ?></td>
                                <td><?= $student['Class'] ?? 'N/A' ?></td>
                                <td><?= $student['Section'] ?? 'N/A' ?></td>

                                <td><?= $student['Father Name'] ?? 'N/A' ?></td>
                                <td><?= $student['Admission Date'] ?? 'N/A' ?></td>
                                <td><?= $student['DOB'] ?? 'N/A' ?></td>
                                <td><?= $student['Guard Contact'] ?? 'N/A' ?></td>
                                <td>
                                    <a href="<?= base_url('student/student_profile/' . ($student['User Id'] ?? '')) ?>"
                                        class="btn btn-success btn-xs" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <a href="<?= base_url('student/edit_student/' . ($student['User Id'] ?? '')) ?>"
                                        class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="<?= base_url('student/delete_student/' . $student['User Id']) ?>"
                                        class="btn btn-danger btn-xs"
                                        onclick="return confirm('Are you sure you want to delete this student?')"
                                        title="Delete">
                                        <i class="fa fa-trash"></i>
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

        // Select All
        $(document).on('change', '#selectAll', function() {
            const checked = this.checked;
            $('.example tbody .row-checkbox').prop('checked', checked);
        });

        // Individual row checkbox
        $(document).on('change', '.row-checkbox', function() {
            const total = $('.example tbody .row-checkbox').length;
            const checked = $('.example tbody .row-checkbox:checked').length;

            $('#selectAll').prop('checked', total === checked);
        });

    });
</script>


<style>
    /* ===== PAGE BACKGROUND ===== */
    .content-wrapper {
        background: #f4f6f9;
        padding: 20px;
    }

    /* ===== HEADER ===== */
    .custom-header {
        margin-bottom: 18px;
    }

    .page-title {
        font-size: 26px;
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }

    /* ===============================
   ENHANCED BREADCRUMB
================================= */

    .custom-breadcrumb {
        float: none !important;
        position: static !important;
        margin-top: 8px;
        padding: 0;
        background: transparent;
        font-size: 16px;
        /* Base size */
    }

    /* Breadcrumb list items */
    .custom-breadcrumb>li {
        font-size: 16px;
    }

    /* Breadcrumb links */
    .custom-breadcrumb>li>a {
        color: #3c8dbc;
        font-weight: 500;
        font-size: 16px;
    }

    /* Active item */
    .custom-breadcrumb>.active {
        color: #666;
        font-weight: 500;
        font-size: 16px;
    }

    /* Separator ( > ) size */
    .custom-breadcrumb>li+li:before {
        font-size: 16px;
        padding: 0 8px;
        color: #999;
    }


    /* ===== ACTION BAR ===== */
    .action-bar {
        margin-bottom: 15px;
        padding: 12px 15px;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
    }

    .page-subtitle {
        margin: 6px 0;
        font-size: 15px;
        font-weight: 500;
        color: #555;
    }

    .action-buttons .btn {
        margin-left: 8px;
        padding: 7px 16px;
        font-size: 14px;
        border-radius: 6px;
    }

    /* ===== TABLE ===== */
    .table>thead>tr>th {
        vertical-align: middle;
        font-size: 14px;
        background: #3c8dbc;
        color: #fff;
    }

    .table>tbody>tr>td {
        font-size: 14px;
        vertical-align: middle;
    }

    /* Action buttons inside table */
    .table .btn-xs {
        padding: 4px 8px;
        font-size: 12px;
    }

    /* Align name better */
    .table td.text-left {
        padding-left: 12px;
    }

    /* Avatar */
    .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Checkbox */
    .table input[type="checkbox"] {
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .action-buttons {
            margin-top: 10px;
            text-align: left !important;
        }
    }
</style>