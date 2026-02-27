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
                        <i class="fa fa-upload"></i> Bulk Student Import
                    </h3>
                </div>

                <div class="card-body">

                    <form action="<?= base_url('student/import_students') ?>" 
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
                                <li>File must follow given header format.</li>
                                <li>Class format must be: <b>Class 8</b></li>
                                <li>Section must be: <b>A / B / C</b></li>
                                <li>Photo upload not required.</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Upload & Import
                        </button>

                        <a href="<?= base_url('student/all_student') ?>" 
                           class="btn btn-secondary">
                            Cancel
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </section>
</div>
