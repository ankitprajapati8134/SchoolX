<div class="content-wrapper">
    <section class="content">

        <div class="container-fluid">

            <!-- TITLE BAR -->
            <div class="box box-warning fee-title-box">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">
                        <i class="fa fa-tag"></i> Fee Title
                    </h3>
                </div>
            </div>


            <!-- FORM SECTION -->
            <div class="box box-default">
                <div class="box-body">

                    <form id="add_fees_title" method="post">

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Fee Title</label>
                                    <input type="text" name="fee_title" id="fee_title" class="form-control" placeholder="Fee Title" required>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Fee Type</label>
                                    <select name="fee_type"
                                        id="fee_type"
                                        class="form-control"
                                        required>
                                        <option value="">Select Fee Type</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Yearly">Yearly</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group text-right">
                                    <!-- Empty label to align with inputs -->
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="box box-success">
                <div class="box-body table-responsive">

                    <table class="table table-bordered table-hover example text-left">
                        <thead>
                            <tr class="bg-yellow text-center">
                                <th style="width:60px;">SNO.</th>
                                <th>Fees Title</th>
                                <th>Fees Type</th>
                                <th style="width:80px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($feesStructure)) : ?>
                                <?php $sno = 1; ?>
                                <?php foreach ($feesStructure as $feeType => $feeTitles) : ?>
                                    <?php foreach ($feeTitles as $feeTitle => $value) : ?>
                                        <tr>
                                            <td><?= $sno++ ?></td>
                                            <td><?= $feeTitle ?></td>
                                            <td><?= $feeType ?></td>
                                            <td>
                                                <a href="<?= base_url('fees/delete_fees_structure/' . $feeTitle . '/' . urlencode($feeType)) ?>"
                                                    class="btn btn-danger btn-xs"
                                                    onclick="return confirm('Delete this fee title?')">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No fees structure found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>

                </div>
            </div>

        </div>

    </section>
</div>

<!-- TOAST -->
<div id="toast"></div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    $(document).ready(function() {

        $('#add_fees_title').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "<?= base_url('fees/fees_structure') ?>",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.trim() === '1') {
                        showToast('Fee title saved successfully', 'success');

                        setTimeout(function() {
                            window.location.href = "<?= base_url('fees/fees_structure') ?>";
                        }, 800);
                    } else {
                        showToast('Failed to save fee title', 'error');
                    }
                },
                error: function() {
                    showToast('Server error occurred', 'error');
                }
            });

            return false;
        });

        function showToast(msg, type) {
            const toast = $('#toast');
            toast.removeClass().addClass(type).text(msg).fadeIn();
            setTimeout(() => toast.fadeOut(), 3000);
        }

    });
</script>


<style>
    /* Toast */
    #toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 18px;
        border-radius: 4px;
        color: #fff;
        display: none;
        z-index: 9999;
    }

    #toast.success {
        background: #00a65a;
    }

    #toast.error {
        background: #dd4b39;
    }

    /* Custom page title header color */
    .fee-title-box>.box-header {
        background-color: #F5AF00;
        color: #fff;
    }

    .fee-title-box>.box-header .box-title,
    .fee-title-box>.box-header .box-title i {
        color: #fff;
    }
</style>