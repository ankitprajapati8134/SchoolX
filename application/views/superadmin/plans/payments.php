<?php
$schools = $schools ?? [];
$plans   = $plans   ?? [];
?>

<section class="content-header">
    <h1><i class="fa fa-money" style="color:var(--sa3);margin-right:10px;font-size:20px;"></i>Payment Records</h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('superadmin/dashboard') ?>">Dashboard</a></li>
        <li><a href="<?= base_url('superadmin/plans') ?>">Plans</a></li>
        <li class="active">Payments</li>
    </ol>
</section>

<section class="content" style="padding:20px 24px;">

    <!-- Quick-nav -->
    <div style="display:flex;gap:8px;margin-bottom:20px;align-items:center;flex-wrap:wrap;">
        <a href="<?= base_url('superadmin/plans') ?>" class="btn btn-default btn-sm"><i class="fa fa-tags"></i> Plan Catalogue</a>
        <a href="<?= base_url('superadmin/plans/subscriptions') ?>" class="btn btn-default btn-sm"><i class="fa fa-calendar-check-o"></i> Subscriptions</a>
        <a href="<?= base_url('superadmin/plans/payments') ?>" class="btn btn-primary btn-sm"><i class="fa fa-money"></i> Payments</a>
        <div style="margin-left:auto;">
            <button class="btn btn-success btn-sm" id="addPaymentBtn">
                <i class="fa fa-plus"></i> Record Payment
            </button>
        </div>
    </div>

    <!-- KPI cards -->
    <div class="row" style="margin-bottom:20px;">
        <?php
        $kpis = [
            ['id'=>'kpi_total',   'label'=>'Total Records', 'icon'=>'fa-list',          'color'=>'var(--sa3)'],
            ['id'=>'kpi_paid',    'label'=>'Paid',          'icon'=>'fa-check-circle',  'color'=>'#22c55e'],
            ['id'=>'kpi_pending', 'label'=>'Pending',       'icon'=>'fa-clock-o',       'color'=>'#f97316'],
            ['id'=>'kpi_overdue', 'label'=>'Overdue',       'icon'=>'fa-exclamation-triangle','color'=>'#ef4444'],
            ['id'=>'kpi_revenue', 'label'=>'Total Revenue', 'icon'=>'fa-inr',           'color'=>'#8b5cf6'],
        ];
        foreach($kpis as $k): ?>
        <div class="col-xs-6 col-sm-4 col-md-2" style="margin-bottom:12px;">
            <div style="background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px 12px;text-align:center;">
                <i class="fa <?= $k['icon'] ?>" style="font-size:20px;color:<?= $k['color'] ?>;margin-bottom:6px;display:block;"></i>
                <div id="<?= $k['id'] ?>" style="font-size:22px;font-weight:800;color:var(--t1);font-family:var(--font-d);">—</div>
                <div style="font-size:11px;color:var(--t3);font-family:var(--font-m);"><?= $k['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;align-items:center;">
        <button class="btn btn-default btn-sm pay-filter active" data-filter="all">All</button>
        <button class="btn btn-default btn-sm pay-filter" data-filter="paid"    style="border-color:#22c55e;color:#22c55e;">Paid</button>
        <button class="btn btn-default btn-sm pay-filter" data-filter="pending" style="border-color:#f97316;color:#f97316;">Pending</button>
        <button class="btn btn-default btn-sm pay-filter" data-filter="overdue" style="border-color:#ef4444;color:#ef4444;">Overdue</button>
        <button class="btn btn-default btn-sm pay-filter" data-filter="failed"  style="border-color:#6b7280;color:#6b7280;">Failed</button>
        <div style="margin-left:auto;">
            <input type="text" id="paySearch" class="form-control input-sm" placeholder="Search school..." style="width:200px;">
        </div>
    </div>

    <!-- Table -->
    <div class="box">
        <div class="box-body" style="padding:0;overflow-x:auto;">
            <table class="table table-hover" style="margin:0;min-width:900px;">
                <thead>
                    <tr style="background:var(--bg3);">
                        <?php foreach(['PAYMENT ID','SCHOOL','PLAN','AMOUNT','STATUS','INVOICE','DUE DATE','PAID DATE','ACTIONS'] as $h): ?>
                        <th style="padding:12px 14px;font-size:11px;font-family:var(--font-m);color:var(--t3);border-bottom:1px solid var(--border);white-space:nowrap;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="payTableBody">
                    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--t3);">
                        <i class="fa fa-spinner fa-spin" style="font-size:20px;"></i><br>Loading...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

</section>

<!-- ── Add / Edit Payment Modal ────────────────────────────────────────────── -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="paymentModalTitle">Record Payment</h4>
            </div>
            <form id="paymentForm">
                <input type="hidden" id="paymentId" name="payment_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School <span style="color:var(--rose);">*</span></label>
                                <select class="form-control" name="school_uid" id="paySchool" required>
                                    <option value="">— Select School —</option>
                                    <?php foreach($schools as $uid => $s): ?>
                                    <option value="<?= htmlspecialchars($uid) ?>"><?= htmlspecialchars($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plan <span style="color:var(--rose);">*</span></label>
                                <select class="form-control" name="plan_id" id="payPlan" required>
                                    <option value="">— Select Plan —</option>
                                    <?php foreach($plans as $pid => $pdata): ?>
                                    <?php
                                        $pname  = is_array($pdata) ? ($pdata['name']  ?? $pid)   : $pdata;
                                        $pprice = is_array($pdata) ? ($pdata['price'] ?? 0)       : 0;
                                    ?>
                                    <option value="<?= htmlspecialchars($pid) ?>" data-price="<?= (float)$pprice ?>"><?= htmlspecialchars($pname) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount (₹) <span style="color:var(--rose);">*</span></label>
                                <input type="number" class="form-control" name="amount" id="payAmount" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status <span style="color:var(--rose);">*</span></label>
                                <select class="form-control" name="status" id="payStatus">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Invoice Date</label>
                                <input type="date" class="form-control" name="invoice_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                        </div>
                        <div class="col-md-4" id="paidDateGroup">
                            <div class="form-group">
                                <label>Paid Date</label>
                                <input type="date" class="form-control" name="paid_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Period Start</label>
                                <input type="date" class="form-control" name="period_start">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Period End</label>
                                <input type="date" class="form-control" name="period_end">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Notes</label>
                                <input type="text" class="form-control" name="notes" placeholder="Optional notes...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePayBtn">
                        <i class="fa fa-save"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var payData = [];

var payCfg = {
    paid:    { cls:'label-success', label:'Paid'    },
    pending: { cls:'label-warning', label:'Pending' },
    overdue: { cls:'label-danger',  label:'Overdue' },
    failed:  { cls:'label-default', label:'Failed'  },
};

function escHtml(s){
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtNum(n){ return '₹'+Number(n||0).toLocaleString('en-IN'); }

// ── Load payments ─────────────────────────────────────────────────────────────
function loadPayments(){
    $.post(BASE_URL+'superadmin/plans/fetch_payments', {}, function(r){
        if(r.status !== 'success'){ saToast('Failed to load payments.','error'); return; }
        payData = r.rows || [];
        renderKpi();
        renderTable($('.pay-filter.active').data('filter'), $('#paySearch').val());
    },'json');
}

function renderKpi(){
    var counts = {paid:0, pending:0, overdue:0, failed:0}, revenue = 0;
    payData.forEach(function(p){
        if(counts[p.status] !== undefined) counts[p.status]++;
        if(p.status === 'paid') revenue += parseFloat(p.amount || 0);
    });
    $('#kpi_total').text(payData.length);
    $('#kpi_paid').text(counts.paid);
    $('#kpi_pending').text(counts.pending);
    $('#kpi_overdue').text(counts.overdue);
    $('#kpi_revenue').text(fmtNum(revenue));
}

function renderTable(filter, search){
    filter = filter || 'all';
    search = (search||'').toLowerCase();

    var rows = payData.filter(function(p){
        if(filter !== 'all' && p.status !== filter) return false;
        if(search && (p.school_uid||'').toLowerCase().indexOf(search) < 0) return false;
        return true;
    });

    if(!rows.length){
        $('#payTableBody').html('<tr><td colspan="9" style="text-align:center;padding:32px;color:var(--t3);">No records match the current filter.</td></tr>');
        return;
    }

    var html = rows.map(function(p){
        var cfg = payCfg[p.status] || {cls:'label-default',label:p.status||'—'};
        return '<tr>'
            +'<td style="padding:10px 14px;"><code style="font-size:11px;">'+escHtml(p.payment_id)+'</code></td>'
            +'<td style="padding:10px 14px;">'+escHtml(p.school_uid)+'</td>'
            +'<td style="padding:10px 14px;">'+escHtml(p.plan_name||'—')+'</td>'
            +'<td style="padding:10px 14px;font-weight:600;color:var(--sa3);">'+fmtNum(p.amount)+'</td>'
            +'<td style="padding:10px 14px;"><span class="label '+cfg.cls+'">'+cfg.label+'</span></td>'
            +'<td style="padding:10px 14px;">'+escHtml(p.invoice_date||'—')+'</td>'
            +'<td style="padding:10px 14px;">'+escHtml(p.due_date||'—')+'</td>'
            +'<td style="padding:10px 14px;">'+escHtml(p.paid_date||'—')+'</td>'
            +'<td style="padding:10px 14px;white-space:nowrap;">'
            +'<button class="btn btn-default btn-xs edit-pay-btn" data-pid="'+escHtml(p.payment_id)+'" title="Edit"><i class="fa fa-edit"></i></button> '
            +'<button class="btn btn-danger btn-xs del-pay-btn" data-pid="'+escHtml(p.payment_id)+'" title="Delete"><i class="fa fa-trash"></i></button>'
            +'</td>'
            +'</tr>';
    }).join('');
    $('#payTableBody').html(html);
}

// ── Filters ───────────────────────────────────────────────────────────────────
$(document).on('click','.pay-filter',function(){
    $('.pay-filter').removeClass('active');
    $(this).addClass('active');
    renderTable($(this).data('filter'), $('#paySearch').val());
});
$('#paySearch').on('input',function(){
    renderTable($('.pay-filter.active').data('filter'), this.value);
});

// ── Show/hide paid date based on status ───────────────────────────────────────
$('#payStatus').on('change',function(){
    $('#paidDateGroup').toggle($(this).val() === 'paid');
}).trigger('change');

// ── Add payment modal ─────────────────────────────────────────────────────────
$('#addPaymentBtn').on('click', function(){
    $('#paymentId').val('');
    $('#paymentForm')[0].reset();
    $('#paymentModalTitle').text('Record Payment');
    $('[name=invoice_date]').val('<?= date('Y-m-d') ?>');
    $('#payStatus').trigger('change');
    $('#paymentModal').modal('show');
});

// ── Edit payment ──────────────────────────────────────────────────────────────
$(document).on('click','.edit-pay-btn', function(){
    var pid = $(this).data('pid');
    var p   = payData.find(function(x){ return x.payment_id === pid; });
    if(!p) return;
    $('#paymentId').val(p.payment_id);
    $('#paySchool').val(p.school_uid);
    $('#payPlan').val(p.plan_id);
    $('[name=amount]').val(p.amount);
    $('#payStatus').val(p.status).trigger('change');
    $('[name=invoice_date]').val(p.invoice_date||'');
    $('[name=due_date]').val(p.due_date||'');
    $('[name=paid_date]').val(p.paid_date||'');
    $('[name=period_start]').val(p.period_start||'');
    $('[name=period_end]').val(p.period_end||'');
    $('[name=notes]').val(p.notes||'');
    $('#paymentModalTitle').text('Edit Payment');
    $('#paymentModal').modal('show');
});

// ── Delete payment ────────────────────────────────────────────────────────────
$(document).on('click','.del-pay-btn', function(){
    var pid = $(this).data('pid');
    if(!confirm('Delete payment '+pid+'? This cannot be undone.')) return;
    $.post(BASE_URL+'superadmin/plans/delete_payment', {payment_id:pid}, function(r){
        saToast(r.message, r.status);
        if(r.status==='success') loadPayments();
    },'json');
});

// ── Auto-fill amount from plan price when plan is selected ────────────────────
$('#payPlan').on('change', function(){
    var price = parseFloat($('option:selected', this).data('price') || 0);
    var $amt  = $('#payAmount');
    // Only auto-fill if amount is currently empty (don't override existing edits)
    if (price > 0 && !$amt.val()) $amt.val(price);
});

// ── Form submit ───────────────────────────────────────────────────────────────
$('#paymentForm').on('submit', function(e){
    e.preventDefault();
    var pid  = $('#paymentId').val();
    var url  = pid ? BASE_URL+'superadmin/plans/update_payment' : BASE_URL+'superadmin/plans/add_payment';
    var $btn = $('#savePayBtn').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({ url:url, type:'POST', data:$(this).serialize(),
        success:function(r){
            saToast(r.message||(r.status==='success'?'Saved.':'Error.'), r.status);
            if(r.status==='success'){ $('#paymentModal').modal('hide'); loadPayments(); }
            $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Payment');
        },
        error:function(){ saToast('Server error.','error'); $btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Payment'); }
    });
});

$(function(){ loadPayments(); });
</script>
