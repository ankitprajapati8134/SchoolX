<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * student_fees.php
 *
 * BUGS FIXED vs old version:
 *
 * 1. fetch_fee_receipts 403 Forbidden
 *    Old: sent raw JSON body → CI CSRF filter can't read it → 403
 *    Fix: use FormData via postForm() so CSRF token is in $_POST field,
 *         matching $this->input->post('userId') in the controller.
 *
 * 2. "Please enter a valid numeric User ID" error for STU0006
 *    Old: validated userId with isNaN(parseInt(userId)) — rejected "STU0006"
 *    Fix: accept any non-empty string; controller does its own validation.
 *
 * 3. "Error fetching data: SyntaxError: Unexpected token '<'"
 *    Root cause was the 403 returning an HTML error page.
 *    Resolved by fixing #1 above.
 */
?>

<div class="content-wrapper">
    <div class="sf-wrap">

        <!-- TOP BAR -->
        <div class="sf-topbar">
            <div>
                <h1 class="sf-page-title"><i class="fa fa-history"></i> Student Fee Receipts</h1>
                <ol class="sf-breadcrumb">
                    <li><a href="<?= base_url() ?>"><i class="fa fa-home"></i> Dashboard</a></li>
                    <li><a href="<?= site_url('fees/fees_records') ?>">Fees</a></li>
                    <li>Student Fee Receipts</li>
                </ol>
            </div>
        </div>

        <!-- SEARCH CARD -->
        <div class="sf-card">
            <div class="sf-card-head">
                <i class="fa fa-search"></i>
                <h3>Look Up Student</h3>
            </div>
            <div class="sf-card-body">
                <div class="sf-search-row">
                    <div class="sf-field">
                        <label class="sf-label">Student ID <span style="color:var(--sf-red)">*</span></label>
                        <!-- BUG FIX #2: Accept any non-empty string (e.g. STU0006), not just numeric -->
                        <input type="text" id="sfUserId" class="sf-input" placeholder="e.g. STU0006, STU0007…"
                            autocomplete="off">
                    </div>
                    <button class="sf-btn sf-btn-primary" onclick="loadReceipts()">
                        <i class="fa fa-search"></i> Fetch Receipts
                    </button>
                    <button class="sf-btn sf-btn-ghost" onclick="clearSearch()">
                        <i class="fa fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- STUDENT INFO STRIP (shown after search) -->
        <div class="sf-student-strip" id="sfStudentStrip">
            <div class="sf-sinfo">
                <span class="sf-sinfo-label">Student ID</span>
                <span class="sf-sinfo-val" id="sfDispId">—</span>
            </div>
            <div class="sf-sinfo-divider"></div>
            <div class="sf-sinfo">
                <span class="sf-sinfo-label">Name</span>
                <span class="sf-sinfo-val" id="sfDispName">—</span>
            </div>
            <div class="sf-sinfo-divider"></div>
            <div class="sf-sinfo">
                <span class="sf-sinfo-label">Class / Section</span>
                <span class="sf-sinfo-val" id="sfDispClass">—</span>
            </div>
            <div class="sf-sinfo-divider"></div>
            <div class="sf-sinfo">
                <span class="sf-sinfo-label">Total Receipts</span>
                <span class="sf-sinfo-val" id="sfDispCount">0</span>
            </div>
            <div class="sf-sinfo-divider"></div>
            <div class="sf-sinfo">
                <span class="sf-sinfo-label">Total Paid</span>
                <span class="sf-sinfo-val sf-amt-positive" id="sfDispTotal">₹ 0.00</span>
            </div>
        </div>

        <!-- RECEIPTS TABLE CARD -->
        <div class="sf-card" id="sfReceiptsCard" style="display:none;">
            <div class="sf-card-head">
                <i class="fa fa-file-text-o"></i>
                <h3>Payment History</h3>
            </div>
            <div class="sf-card-body" style="padding:0;">
                <div class="sf-table-wrap">
                    <table class="sf-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Student / Father</th>
                                <th>Class</th>
                                <th>Amount Paid</th>
                                <th>Fine</th>
                                <th>Discount</th>
                                <th>Mode</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody id="sfReceiptsTbody">
                            <tr>
                                <td colspan="10" class="sf-empty">
                                    <i class="fa fa-search"></i>
                                    Search for a student to view receipts.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="sfReceiptsFoot" style="display:none;">
                            <tr>
                                <td colspan="5" style="text-align:right;">TOTALS</td>
                                <td class="sf-amt-positive" id="sfFootAmt">₹ 0.00</td>
                                <td class="sf-amt-fine" id="sfFootFin">₹ 0.00</td>
                                <td class="sf-amt-discount" id="sfFootDis">₹ 0.00</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="sf-toast-wrap" id="sfToastWrap"></div>

<script>
/* ═══════════════════════════════════════════════════════════════════
   student_fees.php — JavaScript
   
   CRITICAL BUG FIXES:
   
   1. 403 on fetch_fee_receipts:
      Old code sent JSON body with Content-Type: application/json.
      CI's CSRF filter reads only $_POST, so it couldn't find the
      token in the JSON body → 403 BEFORE controller runs.
      Fix: send FormData via postForm() so CI reads CSRF from $_POST.
      Controller reads $this->input->post('userId') — NOT php://input.
   
   2. "Please enter a valid numeric User ID" for STU0006:
      Old validation: if (isNaN(parseInt(userId))) { alert(...); return; }
      Fix: validate only that the field is non-empty — the format
      STU0006 is perfectly valid. Controller validates on its end.
   
   3. "SyntaxError: Unexpected token '<'" — was caused by the 403
      returning an HTML error page. Fixed by fixing #1.
═══════════════════════════════════════════════════════════════════ */

/* ── CSRF tokens from meta tags (set by include/header.php) ── */
var CSRF_NAME = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
var CSRF_HASH = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

var SITE_URL = '<?= rtrim(site_url(), '/') ?>';

/* ── Utilities ── */
function showToast(msg, type) {
    var wrap = document.getElementById('sfToastWrap');
    var el = document.createElement('div');
    el.className = 'sf-toast sf-toast-' + (type || 'success');
    var icons = {
        success: 'check-circle',
        error: 'times-circle',
        warning: 'exclamation-triangle'
    };
    el.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
    wrap.appendChild(el);
    setTimeout(function() {
        el.classList.add('sf-toast-hide');
        setTimeout(function() {
            el.remove();
        }, 350);
    }, 3500);
}

function fmtRs(n) {
    n = parseFloat(String(n || 0).replace(/,/g, '')) || 0;
    return '₹ ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/*
 * postForm — sends FormData with CSRF token in BOTH:
 *   1. FormData body field → CI built-in csrf_protection filter (reads $_POST)
 *   2. X-CSRF-Token header → MY_Controller secondary check
 * Controller reads $this->input->post('userId') — so FormData is required.
 */
function postForm(url, params) {
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH); /* layer 1: CI filter */
    if (params) {
        Object.keys(params).forEach(function(k) {
            fd.append(k, params[k]);
        });
    }
    return fetch(url, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-Token': CSRF_HASH,
                /* layer 2: MY_Controller */
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) {
            if (!r.ok) {
                /* Capture non-OK responses for better error messages */
                return r.text().then(function(t) {
                    throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 120));
                });
            }
            return r.json();
        });
}

/* ── Load receipts ── */
function loadReceipts() {
    /* BUG FIX #2: Accept any non-empty string — no numeric-only check */
    var userId = document.getElementById('sfUserId').value.trim();
    if (!userId) {
        showToast('Please enter a student ID (e.g. STU0006).', 'warning');
        return;
    }

    var tbody = document.getElementById('sfReceiptsTbody');
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:24px;">' +
        '<i class="fa fa-spinner fa-spin"></i> Loading receipts…</td></tr>';

    document.getElementById('sfReceiptsCard').style.display = 'block';
    document.getElementById('sfReceiptsFoot').style.display = 'none';

    /*
     * BUG FIX #1: Use postForm() NOT postJSON() / raw JSON fetch.
     * Controller: $this->input->post('userId') reads from $_POST.
     * Old bug: JSON body → CI CSRF filter can't find token → 403 HTML page
     *   → JS tried to JSON.parse(HTML) → "Unexpected token '<'"
     */
    postForm(SITE_URL + '/fees/fetch_fee_receipts', {
            userId: userId
        })
        .then(function(data) {
            tbody.innerHTML = '';

            if (!Array.isArray(data) || !data.length) {
                tbody.innerHTML = '<tr><td colspan="10"><div class="sf-empty">' +
                    '<i class="fa fa-inbox"></i>' +
                    '<p>No payment records found for <strong>' + userId + '</strong>.</p>' +
                    '</div></td></tr>';
                /* Hide student strip totals */
                document.getElementById('sfStudentStrip').classList.remove('visible');
                return;
            }

            /* Populate student info strip */
            var firstRec = data[0];
            var parts = (firstRec.student || '').split('/');
            document.getElementById('sfDispId').textContent = firstRec.Id || userId;
            document.getElementById('sfDispName').textContent = (parts[0] || '').trim();
            document.getElementById('sfDispClass').textContent = firstRec.class || '—';
            document.getElementById('sfDispCount').textContent = data.length;
            document.getElementById('sfStudentStrip').classList.add('visible');

            var tAmt = 0,
                tFin = 0,
                tDis = 0;
            data.forEach(function(rec, i) {
                var amt = parseFloat(String(rec.amount || 0).replace(/,/g, ''));
                var fin = parseFloat(String(rec.fine || 0).replace(/,/g, ''));
                var dis = parseFloat(String(rec.discount || 0).replace(/,/g, ''));
                tAmt += amt;
                tFin += fin;
                tDis += dis;

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + (i + 1) + '</td>' +
                    '<td><span class="sf-receipt-pill">#' + (rec.receiptNo || '—') + '</span></td>' +
                    '<td>' + (rec.date || '—') + '</td>' +
                    '<td>' + (rec.student || '—') + '</td>' +
                    '<td>' + (rec.class || '—') + '</td>' +
                    '<td class="sf-amt-positive">' + fmtRs(amt) + '</td>' +
                    '<td class="sf-amt-fine">' + fmtRs(fin) + '</td>' +
                    '<td class="sf-amt-discount">' + fmtRs(dis) + '</td>' +
                    '<td>' + (rec.account || 'N/A') + '</td>' +
                    '<td>' + (rec.reference || '—') + '</td>';
                tbody.appendChild(tr);
            });

            /* Update totals row */
            document.getElementById('sfDispTotal').textContent = fmtRs(tAmt);
            document.getElementById('sfFootAmt').textContent = fmtRs(tAmt);
            document.getElementById('sfFootFin').textContent = fmtRs(tFin);
            document.getElementById('sfFootDis').textContent = fmtRs(tDis);
            document.getElementById('sfReceiptsFoot').style.display = '';
        })
        .catch(function(err) {
            console.error('fetch_fee_receipts error:', err);
            tbody.innerHTML = '<tr><td colspan="10"><div class="sf-empty" style="color:var(--sf-red);">' +
                '<i class="fa fa-exclamation-circle"></i>' +
                '<p>Error loading receipts. Please try again.</p>' +
                '<p style="font-size:11px;opacity:.7;">' + err.message + '</p>' +
                '</div></td></tr>';
            showToast('Failed to load receipts. Check console for details.', 'error');
        });
}

function clearSearch() {
    document.getElementById('sfUserId').value = '';
    document.getElementById('sfReceiptsCard').style.display = 'none';
    document.getElementById('sfStudentStrip').classList.remove('visible');
    document.getElementById('sfReceiptsTbody').innerHTML = '';
    document.getElementById('sfReceiptsFoot').style.display = 'none';
}

/* ── Auto-load if userId passed in URL ── */
(function checkUrlParams() {
    var params = new URLSearchParams(window.location.search);
    var uid = params.get('userId') || '';
    if (uid) {
        document.getElementById('sfUserId').value = uid;
        /* Small delay to ensure CSRF meta tags are parsed */
        setTimeout(function() {
            loadReceipts();
        }, 120);
    }
})();

/* ── Enter key ── */
document.getElementById('sfUserId').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') loadReceipts();
});
</script>

<style>
/* ── Student Fees — matches ERP theme ── */
:root {
    --sf-navy: #1a2332;
    --sf-teal: #0d9488;
    --sf-teal-lt: #ccfbf1;
    --sf-amber: #d97706;
    --sf-red: #dc2626;
    --sf-green: #16a34a;
    --sf-muted: #6b7280;
    --sf-border: #e5e7eb;
    --sf-bg: #f4f6f9;
    --sf-white: #ffffff;
    --sf-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    --sf-radius: 10px;
}

.sf-wrap {
    padding: 20px 24px;
    background: var(--sf-bg);
    min-height: 100vh;
}

.sf-topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 12px;
}

.sf-page-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--sf-navy);
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sf-page-title i {
    color: var(--sf-teal);
}

.sf-breadcrumb {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--sf-muted);
}

.sf-breadcrumb li:not(:last-child)::after {
    content: '/';
    margin-left: 6px;
}

.sf-breadcrumb a {
    color: var(--sf-teal);
    text-decoration: none;
}

/* ── Search card ── */
.sf-card {
    background: var(--sf-white);
    border-radius: var(--sf-radius);
    box-shadow: var(--sf-shadow);
    margin-bottom: 20px;
    overflow: hidden;
}

.sf-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1.5px solid var(--sf-border);
}

.sf-card-head h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--sf-navy);
}

.sf-card-head i {
    color: var(--sf-teal);
}

.sf-card-body {
    padding: 20px;
}

.sf-search-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.sf-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 180px;
}

.sf-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--sf-navy);
}

.sf-input {
    padding: 9px 12px;
    border: 1.5px solid var(--sf-border);
    border-radius: 7px;
    font-size: 13px;
    outline: none;
    transition: border .18s;
}

.sf-input:focus {
    border-color: var(--sf-teal);
}

.sf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .18s;
    white-space: nowrap;
}

.sf-btn-primary {
    background: var(--sf-teal);
    color: #fff;
}

.sf-btn-primary:hover {
    background: #0f766e;
}

.sf-btn-ghost {
    background: #fff;
    color: var(--sf-navy);
    border: 1.5px solid var(--sf-border);
}

.sf-btn-ghost:hover {
    border-color: var(--sf-teal);
    color: var(--sf-teal);
}

/* ── Student info strip ── */
.sf-student-strip {
    display: none;
    background: var(--sf-navy);
    border-radius: var(--sf-radius);
    padding: 14px 20px;
    margin-bottom: 20px;
    color: #fff;
    display: none;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.sf-student-strip.visible {
    display: flex;
}

.sf-sinfo {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sf-sinfo-label {
    font-size: 10px;
    color: rgba(255, 255, 255, .6);
    text-transform: uppercase;
    letter-spacing: .5px;
}

.sf-sinfo-val {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
}

.sf-sinfo-divider {
    width: 1px;
    height: 36px;
    background: rgba(255, 255, 255, .15);
}

/* ── Table ── */
.sf-table-wrap {
    overflow-x: auto;
}

.sf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.sf-table thead tr {
    background: var(--sf-navy);
    color: #fff;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: .5px;
}

.sf-table thead th {
    padding: 12px 14px;
    font-weight: 600;
    white-space: nowrap;
}

.sf-table tbody tr {
    border-bottom: 1px solid var(--sf-border);
    transition: background .12s;
}

.sf-table tbody tr:hover {
    background: #f8fafc;
}

.sf-table td {
    padding: 11px 14px;
    vertical-align: middle;
    color: var(--sf-navy);
}

.sf-table tfoot td {
    padding: 11px 14px;
    background: #f1f5f9;
    font-weight: 700;
}

.sf-receipt-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--sf-teal-lt);
    color: var(--sf-teal);
    font-size: 11px;
    font-weight: 700;
}

.sf-amt-positive {
    color: var(--sf-green);
    font-weight: 700;
}

.sf-amt-discount {
    color: var(--sf-amber);
}

.sf-amt-fine {
    color: var(--sf-red);
}

/* ── Empty / loading ── */
.sf-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--sf-muted);
}

.sf-empty i {
    font-size: 40px;
    margin-bottom: 12px;
    opacity: .4;
    display: block;
}

/* ── Toast ── */
.sf-toast-wrap {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sf-toast {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
    animation: sfToastIn .25s ease;
    min-width: 240px;
}

@keyframes sfToastIn {
    from {
        transform: translateX(60px);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.sf-toast-success {
    background: #f0fdf4;
    color: var(--sf-green);
    border-left: 4px solid var(--sf-green);
}

.sf-toast-error {
    background: #fef2f2;
    color: var(--sf-red);
    border-left: 4px solid var(--sf-red);
}

.sf-toast-warning {
    background: #fffbeb;
    color: var(--sf-amber);
    border-left: 4px solid var(--sf-amber);
}

.sf-toast-hide {
    animation: sfToastOut .3s ease forwards;
}

@keyframes sfToastOut {
    to {
        transform: translateX(60px);
        opacity: 0;
    }
}
</style>