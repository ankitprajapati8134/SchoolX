<?php if ($this->session->flashdata('import_result')): ?>
    <div class="alert alert-success">
        <?= $this->session->flashdata('import_result') ?>
    </div>
<?php endif; ?>



<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-upload"></i> Bulk Staff Import
                    </h3>
                </div>

                <div class="card-body">

                    <form action="<?= base_url('staff/import_staff') ?>"
                        method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                            value="<?= $this->security->get_csrf_hash() ?>">
                        <div class="form-group">
                            <label>Select Excel File (.xlsx / .csv)</label>
                            <input type="file"
                                name="excelFile"
                                class="form-control"
                                accept=".xlsx,.csv"
                                required>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Instructions:</strong>
                            <ul>
                                <li>Upload <b>.xlsx</b> or <b>.csv</b> file in the exact header format below.</li>
                                <li><b>Required column:</b> Name, Phone Number</li>
                                <li>All other columns are optional — leave blank if not available.</li>
                                <li>DOB &amp; Date Of Joining format: <b>30-06-1990</b> or <b>1990-06-30</b></li>
                                <li>Phone Number must be a valid 10-digit Indian mobile number.</li>
                                <li>Photo &amp; Documents can be uploaded later via Edit Staff.</li>
                            </ul>
                        </div>

                        <div class="alert alert-secondary mt-2">
                            <strong>Excel Headers (in order):</strong><br>
                            <code>Name | Phone Number | DOB | Email | Gender | Department | Position | Employment Type | Date Of Joining | Father Name | Religion | Category | Qualification | Experience | University | Year Of Passing | Account Holder Name | Account Number | Bank Name | IFSC Code | Emergency Contact Name | Emergency Contact Number | Street | City | State | Postal Code | Basic Salary | Allowances</code>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Upload & Import
                        </button>

                        <a href="<?= base_url('staff/all_staff') ?>"
                            class="btn btn-secondary">
                            Cancel
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </section>
</div>