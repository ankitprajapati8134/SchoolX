<div class="content-wrapper">
    <div class="page_container">
        <div class="box">
            <h3>Schools(<?php echo sizeof($Schools) ?>)<a href="javascript:;" class="btn btn-success pull-right"
                    data-toggle="modal" data-target="#myModal">Add New school</a></h3>
            <div style="padding-top:20px; padding-left: 10px; padding-right: 20px;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered example" style="width:100%">
                        <thead>
                            <tr>
                                <th>SNO</th>
                                <th>School Id</th>
                                <th>School Logo</th>
                                <th>School Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sno = 1;
                            foreach ($Schools as $school) {
                            ?>
                            <tr>
                                <td><?php echo $sno ?></td>
                                <td><?php echo $school['school_id']; ?></td>
                                <td>
                                    <!-- <?php if (isset($school['Logo']) && !empty($school['Logo'])): ?>
                                    <img src="<?php echo $school['Logo']; ?>" alt="School Logo" class="circular-image">
                                <?php else: ?>
                                    <p>No Logo</p>
                                <?php endif; ?> -->


                                    <?php if (isset($school['Logo']) && filter_var($school['Logo'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?php echo $school['Logo']; ?>" alt="School Logo" class="circular-image">

                                    <?php else: ?>
                                    <div class="no-logo">
                                        <span>No Logo</span>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $school['school_name']; ?></td>
                                <td>
                                    <a href="<?php echo base_url() . 'index.php/schools/delete_school/'. $school['school_id'] ?>"
                                        class="btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                    <a href="<?php echo base_url() . 'index.php/schools/edit_school/'. $school['school_id'] ?>"
                                        class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                            <?php
                                $sno++;
                                }
                            ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th>SNO</th>
                                <th>School Id</th>
                                <th>School Logo</th>
                                <th>School Name</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-center">Add New School</h4>
            </div>
            <div class="modal-body">

                <form action="<?php echo base_url() . 'index.php/schools/manage_school' ?>" id="add_school"
                    method="post" enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" 
           value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="form-group input-group mb-3">
                        <label>Enter School Id</label>
                        <input type="text" name="school_id" required="required" value="<?php echo isset($currentSchoolCount) ? $currentSchoolCount : ''; ?>"  id="school_id" class="form-control"
                            placeholder="Enter School Id" aria-label="Sizing example input"
                            aria-describedby="inputGroup-sizing-default" readonly>
                    </div>
                    
                    <div class="form-group input-group mb-3">
                        <label>Enter School Name</label>
                        <input type="text" name="school_name" required="required" id="school_name" class="form-control"
                            placeholder="Enter School Name" aria-label="Sizing example input"
                            aria-describedby="inputGroup-sizing-default">
                    </div>

                    <div class="form-group input-group mb-3">
                        <label>Upload School Logo</label>
                        <input type="file" name="school_logo" required="required" id="school_logo" class="form-control"
                            aria-label="Upload School Logo" aria-describedby="inputGroup-sizing-default">
                    </div>

                    <div class="form-group input-group mb-3">
                        <label>Upload Holiday Calendar</label>
                        <input type="file" name="Holidays" id="Holidays" class="form-control"
                            aria-label="Upload Holiday Calendar" aria-describedby="inputGroup-sizing-default">
                    </div>
                    <div class="form-group input-group mb-3">
                        <label>Upload Academic Calendar</label>
                        <input type="file" name="Academic" id="Academic" class="form-control"
                            aria-label="Upload Academic Calendar" aria-describedby="inputGroup-sizing-default">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<style>
.circular-image {
    width: 50px;
    /* Adjust width as needed */
    height: 50px;
    /* Adjust height as needed */
    border-radius: 50%;
    /* Makes the image circular */
    object-fit: cover;
    /* Ensures the image covers the circular area */
}

.no-logo {
    width: 50px;
    /* Same dimensions as the circular image */
    height: 50px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.1);
    /* Light gray background */
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(0, 0, 0, 0.4);
    /* Slightly darker text */
    font-size: 12px;
    text-align: center;
    line-height: 1.2;
    /* Adjust line height */
}

.no-logo span {
    display: inline-block;
}
</style>