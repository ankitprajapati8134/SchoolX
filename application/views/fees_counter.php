<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
 * fees_counter.php  — VIEW
 * ══════════════════════════════════════════════════════════════
 *  UX BUGS FIXED IN THIS VERSION:
 *
 *  1. STUDENT SEARCH — Two separate UX flows:
 *     a) Type User ID → press Enter or click Find → inline lookup,
 *        fields auto-fill WITHOUT opening any modal.
 *     b) Click "Search Students" button → opens full search modal
 *        where you can search by name / ID / father name / class.
 *
 *  2. MONTH TILES — appear automatically after student is found.
 *     fetch_months() is called right after lookup resolves.
 *
 *  3. FETCH FEE DETAILS — calls dedicated /fees/fetch_fee_details
 *     endpoint that returns pure JSON (no HTML parsing).
 *
 *  4. SERVER DATE — get_server_date() called on load; falls back
 *     to today's date, never shows "Timestamp Not Found".
 *
 *  5. ALREADY PAID stat — shows real totalSubmittedAmount only after
 *     a student is loaded (0.00 otherwise).
 * ══════════════════════════════════════════════════════════════
 */

// PHP safety defaults
$receiptNo         = $receiptNo         ?? '1';
$serverDate        = $serverDate        ?? date('d-m-Y');
$accounts          = $accounts          ?? [];
?>

<div class="content-wrapper">
<div class="fc-wrap">

<!-- ══ TOP BAR ══ -->
<div class="fc-topbar">
    <div>
        <h1 class="fc-page-title"><i class="fa fa-rupee"></i> Fee Counter</h1>
        <ol class="fc-breadcrumb">
            <li><a href="<?= base_url() ?>"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="<?= site_url('fees/fees_records') ?>">Fees</a></li>
            <li>Fee Counter</li>
        </ol>
    </div>
    <div class="fc-receipt-badge">
        <span class="fc-receipt-label">Receipt No.</span>
        <span class="fc-receipt-num" id="topReceiptNo"><?= htmlspecialchars($receiptNo) ?></span>
    </div>
</div>

<!-- ══ STAT STRIP ══ -->
<div class="fc-stat-strip">
    <div class="fc-stat fc-stat-blue">
        <div class="fc-stat-icon"><i class="fa fa-calendar-check-o"></i></div>
        <div class="fc-stat-body">
            <div class="fc-stat-label">Total Fee</div>
            <div class="fc-stat-val" id="statTotalFee">₹ 0.00</div>
        </div>
    </div>
    <div class="fc-stat fc-stat-green">
        <div class="fc-stat-icon"><i class="fa fa-check-circle"></i></div>
        <div class="fc-stat-body">
            <div class="fc-stat-label">Already Paid</div>
            <div class="fc-stat-val" id="statAlreadyPaid">₹ 0.00</div>
        </div>
    </div>
    <div class="fc-stat fc-stat-amber">
        <div class="fc-stat-icon"><i class="fa fa-gift"></i></div>
        <div class="fc-stat-body">
            <div class="fc-stat-label">Discount</div>
            <div class="fc-stat-val" id="statDiscount">₹ 0.00</div>
        </div>
    </div>
    <div class="fc-stat fc-stat-red">
        <div class="fc-stat-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="fc-stat-body">
            <div class="fc-stat-label">Due Amount</div>
            <div class="fc-stat-val" id="statDue">₹ 0.00</div>
        </div>
    </div>
</div>

<!-- Alert placeholder -->
<div id="fcAlertBox" style="display:none;"></div>

<!-- ══ MAIN LAYOUT ══ -->
<div class="fc-layout">

    <!-- ── LEFT ── -->
    <div class="fc-left">

        <!-- STEP 1: Student & Receipt Details -->
        <div class="fc-card">
            <div class="fc-card-head">
                <span class="fc-step">1</span>
                <i class="fa fa-user-circle"></i>
                <h3>Student &amp; Receipt Details</h3>
            </div>
            <div class="fc-card-body">

                <!-- Row 1: Receipt / Date (Mode of Payment is in Step 4) -->
                <div class="fc-grid-2 fc-mb">
                    <div class="fc-field">
                        <label class="fc-label">Receipt No.</label>
                        <input type="text" id="receiptNo" class="fc-input" value="<?= htmlspecialchars($receiptNo) ?>" readonly>
                    </div>
                    <div class="fc-field">
                        <label class="fc-label">Date</label>
                        <input type="text" id="fcDate" class="fc-input" value="<?= htmlspecialchars($serverDate) ?>" readonly>
                    </div>
                </div>

                <!-- Row 2: Student ID lookup -->
                <!--
                    UX FIX: The Student ID row now works in TWO ways:
                    a) Type an exact ID → press Enter or click Find → inline AJAX lookup
                       → fields fill automatically, NO modal opens.
                    b) Click "Search Students" → opens the search modal for
                       browsing by name / father name / class.
                    This matches how real ERP systems work.
                -->
                <div class="fc-mb">
                    <label class="fc-label">Student ID <span class="fc-req">*</span></label>
                    <div class="fc-student-row">
                        <div class="fc-id-wrap">
                            <input type="text" id="user_id" class="fc-input"
                                placeholder="Type ID &amp; press Enter or click Find…"
                                autocomplete="off" spellcheck="false">
                            <!-- Spinner that shows while looking up -->
                            <span class="fc-id-spinner" id="idSpinner" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i>
                            </span>
                        </div>
                        <button type="button" class="fc-btn fc-btn-amber" id="findBtn" title="Find student by ID">
                            <i class="fa fa-search"></i> Find
                        </button>
                        <button type="button" class="fc-btn fc-btn-ghost" id="openSearchBtn" title="Browse &amp; search students">
                            <i class="fa fa-list-ul"></i> Browse
                        </button>
                    </div>
                    <!-- Inline error/success message for ID lookup -->
                    <div id="idFeedback" class="fc-id-feedback" style="display:none;"></div>
                </div>

                <!-- Row 3: Auto-filled student info -->
                <div class="fc-grid-2 fc-mb">
                    <div class="fc-field">
                        <label class="fc-label">Student Name</label>
                        <input type="text" id="sname" class="fc-input" placeholder="Auto-filled after search" readonly>
                    </div>
                    <div class="fc-field">
                        <label class="fc-label">Father's Name</label>
                        <input type="text" id="fname" class="fc-input" placeholder="Auto-filled" readonly>
                    </div>
                </div>

                <div class="fc-grid-2">
                    <div class="fc-field">
                        <label class="fc-label">Class / Section</label>
                        <input type="text" id="fcClass" class="fc-input" placeholder="Auto-filled" readonly>
                    </div>
                    <div class="fc-field">
                        <label class="fc-label">Discount Applied</label>
                        <input type="text" id="discountDisplay" class="fc-input fc-input-green"
                            value="₹ 0.00" readonly>
                    </div>
                </div>

            </div>
        </div>

        <!-- STEP 2: Month Selection -->
        <!--
            UX FIX: Months appear as visual tiles (green = paid, teal = selected, grey = unpaid).
            They load automatically after a student is found via fetch_months().
            "Select All Unpaid" and "Clear" buttons added.
            "Fetch Fee Details" button activates only when ≥1 month is selected.
        -->
        <div class="fc-card">
            <div class="fc-card-head">
                <span class="fc-step">2</span>
                <i class="fa fa-calendar"></i>
                <h3>Select Months</h3>
                <span class="fc-head-hint" id="monthsHint">Find a student first to see months</span>
            </div>
            <div class="fc-card-body">

                <!-- Month tile grid — populated by buildMonthTiles() -->
                <div class="fc-month-grid" id="monthGrid">
                    <div class="fc-months-placeholder" id="monthsPlaceholder">
                        <i class="fa fa-hand-o-up"></i>
                        <p>Search and select a student above to load their payment months.</p>
                    </div>
                </div>

                <!-- Action buttons — shown after months load -->
                <div class="fc-month-actions" id="monthActions" style="display:none;">
                    <button type="button" class="fc-btn fc-btn-ghost fc-btn-sm" id="selectAllBtn">
                        <i class="fa fa-check-square-o"></i> Select All Unpaid
                    </button>
                    <button type="button" class="fc-btn fc-btn-ghost fc-btn-sm" id="clearAllBtn">
                        <i class="fa fa-square-o"></i> Clear
                    </button>
                    <button type="button" class="fc-btn fc-btn-primary" id="fetchDetailsBtn" disabled>
                        <i class="fa fa-refresh"></i> Fetch Fee Details
                    </button>
                </div>

            </div>
        </div>

        <!-- STEP 3: Fee Breakdown (shown after Fetch Details) -->
        <div class="fc-card" id="breakdownCard" style="display:none;">
            <div class="fc-card-head">
                <span class="fc-step">3</span>
                <i class="fa fa-table"></i>
                <h3>Fee Breakdown</h3>
                <span class="fc-head-hint" id="breakdownHeading"></span>
                <button type="button" class="fc-btn fc-btn-ghost fc-btn-xs"
                    id="expandBreakdownBtn" style="margin-left:auto;">
                    <i class="fa fa-expand"></i> Expand
                </button>
            </div>
            <div class="fc-card-body" style="padding:0;">
                <div class="fc-table-wrap">
                    <table class="fc-table" id="breakdownTable">
                        <thead>
                            <tr>
                                <th>Fee Title</th>
                                <th style="text-align:right;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody id="breakdownTbody"></tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td style="text-align:right;"><strong id="breakdownGrandTotal">0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 4: Submit Payment (shown after Fetch Details) -->
        <div class="fc-card" id="paymentCard" style="display:none;">
            <div class="fc-card-head">
                <span class="fc-step">4</span>
                <i class="fa fa-credit-card"></i>
                <h3>Submit Payment</h3>
            </div>
            <div class="fc-card-body">

                <!-- Mode of Payment — placed here so operator fills it right before submitting -->
                <div class="fc-field fc-mb" id="paymentModeField">
                    <label class="fc-label" style="font-size:12px;color:var(--fc-teal);font-weight:800;">
                        <i class="fa fa-credit-card"></i>&nbsp; Mode of Payment <span class="fc-req">*</span>
                    </label>
                    <div class="fc-select-wrap">
                        <select id="accountSelect" class="fc-select fc-select-highlighted" required>
                            <option value="" disabled selected>— Select payment mode —</option>
                            <?php foreach ($accounts as $aName => $under): ?>
                                <option value="<?= htmlspecialchars($aName) ?>">
                                    <?= htmlspecialchars($aName) ?> (<?= htmlspecialchars($under) ?>)
                                </option>
                            <?php endforeach; ?>
                            <?php if (empty($accounts)): ?>
                                <option disabled>No accounts configured</option>
                            <?php endif; ?>
                        </select>
                        <i class="fa fa-chevron-down fc-select-arrow"></i>
                    </div>
                </div>

                <div class="fc-grid-3 fc-mb">
                    <div class="fc-field">
                        <label class="fc-label">School Fees to Submit (₹) <span class="fc-req">*</span></label>
                        <input type="number" id="submitSchoolFees" class="fc-input fc-input-primary"
                            placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="fc-field">
                        <label class="fc-label">Fine Amount (₹)</label>
                        <input type="number" id="fineAmount" class="fc-input" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="fc-field">
                        <label class="fc-label">Reference / Remark</label>
                        <input type="text" id="reference" class="fc-input" placeholder="e.g. Cash payment">
                    </div>
                </div>

                <!-- Live due calculation bar -->
                <div class="fc-due-bar">
                    <div class="fc-due-item">
                        <span class="fc-due-label">Total Fee</span>
                        <span class="fc-due-val" id="barTotalFee">₹ 0.00</span>
                    </div>
                    <span class="fc-due-sep">−</span>
                    <div class="fc-due-item">
                        <span class="fc-due-label">Discount</span>
                        <span class="fc-due-val fc-green" id="barDiscount">₹ 0.00</span>
                    </div>
                    <span class="fc-due-sep">−</span>
                    <div class="fc-due-item">
                        <span class="fc-due-label">Overpaid</span>
                        <span class="fc-due-val fc-green" id="barOverpaid">₹ 0.00</span>
                    </div>
                    <span class="fc-due-sep">=</span>
                    <div class="fc-due-item fc-due-item-big">
                        <span class="fc-due-label">Due Amount</span>
                        <span class="fc-due-val fc-red" id="barDueAmount">₹ 0.00</span>
                    </div>
                </div>

                <div class="fc-action-bar">
                    <button type="button" class="fc-btn fc-btn-ghost"
                        onclick="location.href='<?= site_url('fees/fees_counter') ?>'">
                        <i class="fa fa-file-o"></i> New Receipt
                    </button>
                    <button type="button" id="submitFeesBtn" class="fc-btn fc-btn-submit">
                        <i class="fa fa-paper-plane"></i> Submit Fees
                    </button>
                </div>

            </div>
        </div>

    </div><!-- /.fc-left -->

    <!-- ── RIGHT: Sticky Payment Summary ── -->
    <div class="fc-right">
        <div class="fc-summary-card">
            <div class="fc-summary-head">
                <i class="fa fa-file-text-o"></i> Payment Summary
            </div>
            <div class="fc-summary-body">
                <div class="fc-summary-row">
                    <span>Student</span>
                    <strong id="sumName">—</strong>
                </div>
                <div class="fc-summary-row">
                    <span>Class</span>
                    <strong id="sumClass">—</strong>
                </div>
                <div class="fc-summary-row">
                    <span>Receipt No.</span>
                    <strong id="sumReceiptNo"><?= htmlspecialchars($receiptNo) ?></strong>
                </div>
                <div class="fc-summary-row">
                    <span>Payment Mode</span>
                    <strong id="sumPaymentMode">—</strong>
                </div>
                <div class="fc-summary-row">
                    <span>Date</span>
                    <strong id="sumDate"><?= htmlspecialchars($serverDate) ?></strong>
                </div>
                <div class="fc-summary-divider"></div>
                <div class="fc-summary-row">
                    <span>Months Selected</span>
                    <strong id="sumMonths">—</strong>
                </div>
                <div class="fc-summary-row">
                    <span>Total Fee</span>
                    <strong id="sumTotal">₹ 0.00</strong>
                </div>
                <div class="fc-summary-row fc-green">
                    <span>Discount</span>
                    <strong id="sumDiscountRow">₹ 0.00</strong>
                </div>
                <div class="fc-summary-row fc-green">
                    <span>Overpaid (carry-fwd)</span>
                    <strong id="sumOverpaid">₹ 0.00</strong>
                </div>
                <div class="fc-summary-divider"></div>
                <div class="fc-summary-row fc-summary-due">
                    <span>DUE AMOUNT</span>
                    <strong id="sumDue">₹ 0.00</strong>
                </div>
                <div class="fc-summary-divider"></div>
                <div class="fc-summary-row">
                    <span>Fine</span>
                    <strong id="sumFine">₹ 0.00</strong>
                </div>
                <div class="fc-summary-row fc-summary-payable">
                    <span>Submitting Now</span>
                    <strong id="sumPayable">₹ 0.00</strong>
                </div>
            </div>

            <!-- Payment History button — always visible after student is selected -->
            <div class="fc-summary-history" id="historyBtnWrap" style="display:none;">
                <button type="button" class="fc-btn fc-btn-history-full" id="feesRecordBtn">
                    <i class="fa fa-history"></i> View Payment History
                </button>
            </div>

        </div>
    </div><!-- /.fc-right -->

</div><!-- /.fc-layout -->
</div><!-- /.fc-wrap -->
</div><!-- /.content-wrapper -->


<!-- ══ MODAL: Search / Browse Students ══ -->
<div class="fc-overlay" id="searchModal">
    <div class="fc-modal">
        <div class="fc-modal-head">
            <h4><i class="fa fa-search"></i> Browse Students</h4>
            <button class="fc-modal-close" onclick="closeModal('searchModal')">&times;</button>
        </div>
        <div class="fc-modal-body">
            <div class="fc-search-box">
                <input type="text" class="fc-input" id="searchInput"
                    placeholder="Search by name, ID, father name or class…"
                    autocomplete="off">
                <button type="button" class="fc-btn fc-btn-primary" id="doSearchBtn">
                    <i class="fa fa-search"></i> Search
                </button>
            </div>
            <div class="fc-table-wrap" style="margin-top:12px;">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Father Name</th>
                            <th>Class</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="searchResults">
                        <tr><td colspan="6" class="fc-empty-cell">Enter a term and click Search.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL: Expanded Breakdown ══ -->
<div class="fc-overlay" id="expandModal">
    <div class="fc-modal fc-modal-wide">
        <div class="fc-modal-head">
            <h4><i class="fa fa-table"></i> Detailed Fee Breakdown</h4>
            <button class="fc-modal-close" onclick="closeModal('expandModal')">&times;</button>
        </div>
        <div class="fc-modal-body" id="expandModalBody">
            <p style="text-align:center;color:var(--fc-muted);padding:30px 0;">
                Fetch fee details first.
            </p>
        </div>
    </div>
</div>

<!-- ══ MODAL: Payment History ══ -->
<div class="fc-overlay" id="historyModal">
    <div class="fc-modal">
        <div class="fc-modal-head">
            <h4><i class="fa fa-history"></i> Payment History</h4>
            <button class="fc-modal-close" onclick="closeModal('historyModal')">&times;</button>
        </div>
        <div class="fc-modal-body">
            <div class="fc-table-wrap">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th>Receipt</th><th>Date</th>
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;">Fine</th>
                            <th style="text-align:right;">Discount</th>
                            <th>Mode</th>
                        </tr>
                    </thead>
                    <tbody id="historyTbody">
                        <tr><td colspan="6" class="fc-empty-cell">No history loaded yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="fcToastWrap" class="fc-toast-wrap"></div>



<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT — complete, no HTML parsing, clean endpoints
══════════════════════════════════════════════════════════ -->
<script>
/* ── State Object ── */
var FC = {
    userId:      '',
    studentName: '',
    fatherName:  '',
    className:   '',   // raw class string from Firebase e.g. "8th A"
    discountAmt: 0,
    overpaidAmt: 0,
    grandTotal:  0,
    selectedMonths: [],
    monthFeeMap: {},   // { month: totalFee } populated after fetchFeeDetails
    alreadyPaid: 0
};

/* PHP constants */
var SITE_URL  = '<?= rtrim(site_url(), '/') ?>';
var RECEIPT_NO = '<?= htmlspecialchars($receiptNo) ?>';

/* ── Utility ── */
function fmtRs(n) {
    return '₹ ' + parseFloat(n || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}
function fmtNum(n) {
    return parseFloat(n || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

/* Close modal on backdrop click */
document.querySelectorAll('.fc-overlay').forEach(function(ov) {
    ov.addEventListener('click', function(e) { if (e.target === ov) ov.classList.remove('open'); });
});

function showToast(msg, type) {
    var wrap = document.getElementById('fcToastWrap');
    var el   = document.createElement('div');
    el.className = 'fc-toast fc-toast-' + (type || 'info');
    var icons = { success:'check-circle', error:'times-circle', warning:'exclamation-triangle', info:'info-circle' };
    el.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
    wrap.appendChild(el);
    setTimeout(function() { el.classList.add('fc-toast-hide'); setTimeout(function() { el.remove(); }, 350); }, 3500);
}

function showAlert(msg, type) {
    var box = document.getElementById('fcAlertBox');
    box.className = 'fc-alert fc-alert-' + (type || 'info');
    box.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + msg;
    box.style.display = 'flex';
    setTimeout(function() { box.style.display = 'none'; }, 6000);
}

/* ── Live Recalc ── */
function recalc() {
    var fine      = parseFloat(document.getElementById('fineAmount').value)       || 0;
    var schoolFee = parseFloat(document.getElementById('submitSchoolFees').value) || 0;
    var due       = Math.max(0, FC.grandTotal - FC.discountAmt - FC.overpaidAmt);

    // Stat strip
    document.getElementById('statTotalFee').textContent   = fmtRs(FC.grandTotal);
    document.getElementById('statAlreadyPaid').textContent = fmtRs(FC.alreadyPaid);
    document.getElementById('statDiscount').textContent   = fmtRs(FC.discountAmt);
    document.getElementById('statDue').textContent        = fmtRs(due);

    // Due bar
    document.getElementById('barTotalFee').textContent  = fmtRs(FC.grandTotal);
    document.getElementById('barDiscount').textContent  = fmtRs(FC.discountAmt);
    document.getElementById('barOverpaid').textContent  = fmtRs(FC.overpaidAmt);
    document.getElementById('barDueAmount').textContent = fmtRs(due);

    // Summary panel
    document.getElementById('sumName').textContent        = FC.studentName || '—';
    document.getElementById('sumClass').textContent       = FC.className   || '—';
    document.getElementById('sumMonths').textContent      = FC.selectedMonths.length ? FC.selectedMonths.join(', ') : '—';
    document.getElementById('sumTotal').textContent       = fmtRs(FC.grandTotal);
    document.getElementById('sumDiscountRow').textContent = fmtRs(FC.discountAmt);
    document.getElementById('sumOverpaid').textContent    = fmtRs(FC.overpaidAmt);
    document.getElementById('sumDue').textContent         = fmtRs(due);
    document.getElementById('sumFine').textContent        = fmtRs(fine);
    document.getElementById('sumPayable').textContent     = fmtRs(schoolFee + fine);

    // Discount display field
    document.getElementById('discountDisplay').value = '₹ ' + fmtNum(FC.discountAmt);
}

/* ── Build Month Tiles ──
   monthFees = { April:0, May:1, ... }  1=paid 0=unpaid
   BUG FIX: tiles now appear correctly after student is found
*/
function buildMonthTiles(monthFees) {
    var grid        = document.getElementById('monthGrid');
    var placeholder = document.getElementById('monthsPlaceholder');
    var actions     = document.getElementById('monthActions');
    var hint        = document.getElementById('monthsHint');

    grid.innerHTML = '';
    if (placeholder) placeholder.style.display = 'none';

    var months = [
        'April','May','June','July','August','September',
        'October','November','December','January','February','March','Yearly Fees'
    ];

    var unpaidCount = 0;
    months.forEach(function(m) {
        var paid    = monthFees[m] === 1;
        if (!paid) unpaidCount++;

        var tile = document.createElement('div');
        tile.className  = 'fc-month-tile' + (paid ? ' paid' : '');
        tile.dataset.month = m;
        tile.dataset.paid  = paid ? '1' : '0';

        tile.innerHTML =
            '<div class="fc-month-name">' + m + '</div>' +
            '<div class="fc-month-status">' +
            (paid
                ? '<i class="fa fa-check-circle" style="color:#16a34a"></i> Paid'
                : '<i class="fa fa-circle-o"></i> Unpaid')
            + '</div>';

        if (!paid) {
            tile.addEventListener('click', function() {
                tile.classList.toggle('selected');
                updateFromTiles();
            });
        }
        grid.appendChild(tile);
    });

    if (actions) actions.style.display = 'flex';
    if (hint) hint.textContent = unpaidCount + ' unpaid month(s)';
    updateFromTiles();
}

function updateFromTiles() {
    var selected = Array.from(document.querySelectorAll('.fc-month-tile.selected'))
        .map(function(t) { return t.dataset.month; });
    FC.selectedMonths = selected;

    var btn = document.getElementById('fetchDetailsBtn');
    if (btn) btn.disabled = selected.length === 0;

    document.getElementById('sumMonths').textContent = selected.length ? selected.join(', ') : '—';
}

/* ── Fetch Months (after student selected) ──
   Calls /fees/fetch_months → JSON of { April:0|1, ... }
   BUG FIX: now calls dedicated JSON endpoint, not full page POST
*/
function fetchMonths(userId) {
    if (!userId) return;

    var body = 'user_id=' + encodeURIComponent(userId);
    fetch(SITE_URL + '/fees/fetch_months', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            showAlert('Month load failed: ' + data.error, 'warning');
            console.error('fetch_months error:', data.error);
            return;
        }
        buildMonthTiles(data);
        showToast('Months loaded for student', 'info');
    })
    .catch(function(e) {
        console.error('fetchMonths network error', e);
        showAlert('Could not load months — check console.', 'error');
    });
}

/* ── Inline Student Lookup (Find button / Enter key) ──
   BUG FIX: No modal opened. Directly calls /fees/lookup_student
   with the typed ID and fills fields inline.
*/
function lookupStudentById() {
    var uid = document.getElementById('user_id').value.trim();
    if (!uid) {
        showAlert('Please enter a Student ID first.', 'warning');
        return;
    }

    // Show spinner in input
    document.getElementById('idSpinner').style.display = 'inline';
    document.getElementById('findBtn').disabled = true;

    var feedback = document.getElementById('idFeedback');
    feedback.style.display = 'none';
    feedback.className = 'fc-id-feedback';

    fetch(SITE_URL + '/fees/lookup_student', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + encodeURIComponent(uid)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('idSpinner').style.display = 'none';
        document.getElementById('findBtn').disabled = false;

        if (data.error) {
            feedback.className  = 'fc-id-feedback fc-id-feedback-error';
            feedback.textContent = '✗ ' + data.error;
            feedback.style.display = 'block';
            return;
        }

        selectStudent(data);
        feedback.className  = 'fc-id-feedback fc-id-feedback-success';
        feedback.textContent = '✓ Student found: ' + data.name;
        feedback.style.display = 'block';
    })
    .catch(function(e) {
        document.getElementById('idSpinner').style.display = 'none';
        document.getElementById('findBtn').disabled = false;
        console.error('lookupStudentById error', e);
        showAlert('Network error during student lookup.', 'error');
    });
}

/* ── Select Student (shared by both lookup and modal select) ── */
function selectStudent(s) {
    FC.userId      = s.user_id     || '';
    FC.studentName = s.name        || '';
    FC.fatherName  = s.father_name || '';
    FC.className   = s.class       || '';

    document.getElementById('user_id').value = FC.userId;
    document.getElementById('sname').value   = FC.studentName;
    document.getElementById('fname').value   = FC.fatherName;
    document.getElementById('fcClass').value = FC.className;
    document.getElementById('sumName').textContent  = FC.studentName;
    document.getElementById('sumClass').textContent = FC.className;

    // Reset any previous fee details
    FC.grandTotal   = 0;
    FC.discountAmt  = 0;
    FC.overpaidAmt  = 0;
    FC.alreadyPaid  = 0;
    FC.monthFeeMap  = {};
    document.getElementById('breakdownCard').style.display = 'none';
    document.getElementById('paymentCard').style.display   = 'none';

    // Show Payment History button the moment a student is selected.
    // Previously it was buried inside Step 4 (paymentCard) which is
    // display:none until fee details are fetched — so it was invisible
    // after submit. Now it lives permanently in the summary panel.
    var hw = document.getElementById('historyBtnWrap');
    if (hw) hw.style.display = '';

    recalc();

    // Load months automatically
    fetchMonths(FC.userId);
}

/* ── Fetch Fee Details (dedicated JSON endpoint) ──
   BUG FIX: Calls /fees/fetch_fee_details → pure JSON, no HTML parsing.
*/
function fetchFeeDetails() {
    if (!FC.userId) {
        showAlert('Please select a student first.', 'error');
        return;
    }
    if (FC.selectedMonths.length === 0) {
        showAlert('Please select at least one month.', 'warning');
        return;
    }

    var btn = document.getElementById('fetchDetailsBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fetching…';

    var body = 'user_id=' + encodeURIComponent(FC.userId);
    FC.selectedMonths.forEach(function(m) {
        body += '&months[]=' + encodeURIComponent(m);
    });

    fetch(SITE_URL + '/fees/fetch_fee_details', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh"></i> Fetch Fee Details';

        if (d.error) {
            showAlert('Fee detail error: ' + d.error, 'error');
            return;
        }
        applyFetchedData(d);
    })
    .catch(function(e) {
        console.error('fetchFeeDetails error', e);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh"></i> Fetch Fee Details';
        showAlert('Network error. Please try again.', 'error');
    });
}

function applyFetchedData(d) {
    FC.grandTotal   = parseFloat(d.grandTotal)     || 0;
    FC.discountAmt  = parseFloat(d.discountAmount) || 0;
    FC.overpaidAmt  = parseFloat(d.overpaidFees)   || 0;
    FC.monthFeeMap  = d.monthTotals || {};

    var feesRecord = d.feesRecord || [];

    /* Breakdown table */
    var tbody = document.getElementById('breakdownTbody');
    tbody.innerHTML = '';
    if (feesRecord.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" class="fc-empty-cell">No fee titles found for selected months.</td></tr>';
    } else {
        feesRecord.forEach(function(row) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + row.title + '</td>' +
                '<td style="text-align:right;font-weight:600;">' + fmtNum(row.total) + '</td>';
            tbody.appendChild(tr);
        });
    }
    document.getElementById('breakdownGrandTotal').textContent = fmtNum(FC.grandTotal);

    var h = document.getElementById('breakdownHeading');
    if (h) h.textContent = d.message || '';

    /* Show step cards */
    document.getElementById('breakdownCard').style.display = '';
    document.getElementById('paymentCard').style.display   = '';

    /* Pre-fill school fees with due amount */
    var due = Math.max(0, FC.grandTotal - FC.discountAmt - FC.overpaidAmt);
    document.getElementById('submitSchoolFees').value = due.toFixed(2);

    /* Expand modal */
    buildExpandModal(d.feeRecord, d.selectedMonths, d.monthTotals, d.grandTotal);

    recalc();
    showToast('Fee details loaded!', 'success');
    document.getElementById('breakdownCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function buildExpandModal(feeRecord, months, mTotals, grand) {
    var body = document.getElementById('expandModalBody');
    if (!feeRecord || !months || months.length === 0) {
        body.innerHTML = '<p style="text-align:center;color:var(--fc-muted);padding:30px 0;">No breakdown data.</p>';
        return;
    }
    var html = '<div class="fc-table-wrap"><table class="fc-table"><thead><tr><th>Fee Title</th>';
    months.forEach(function(m) { html += '<th style="text-align:center;">' + m + '</th>'; });
    html += '<th style="text-align:right;">Total</th></tr></thead><tbody>';

    Object.values(feeRecord).forEach(function(row) {
        html += '<tr><td>' + row.title + '</td>';
        months.forEach(function(m) { html += '<td style="text-align:center;">' + fmtNum(row[m] || 0) + '</td>'; });
        html += '<td style="text-align:right;font-weight:700;">' + fmtNum(row.total) + '</td></tr>';
    });

    html += '</tbody><tfoot><tr><td><strong>Total</strong></td>';
    months.forEach(function(m) {
        html += '<td style="text-align:center;"><strong>' + fmtNum((mTotals||{})[m]||0) + '</strong></td>';
    });
    html += '<td style="text-align:right;"><strong>' + fmtNum(grand) + '</strong></td></tr></tfoot></table></div>';
    body.innerHTML = html;
}

/* ── Modal search (Browse button) ── */
function doSearch() {
    var q = document.getElementById('searchInput').value.trim();
    if (!q) return;
    var tbody = document.getElementById('searchResults');
    tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell"><i class="fa fa-spinner fa-spin"></i> Searching…</td></tr>';

    fetch(SITE_URL + '/fees/search_student', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'search_name=' + encodeURIComponent(q)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell">No students found.</td></tr>';
            return;
        }
        data.forEach(function(s, i) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (i+1) + '</td>' +
                '<td><span class="fc-id-pill">' + (s.user_id||'—') + '</span></td>' +
                '<td><strong>' + (s.name||'—') + '</strong></td>' +
                '<td>' + (s.father_name||'—') + '</td>' +
                '<td>' + (s.class||'—') + '</td>' +
                '<td><button type="button" class="fc-btn fc-btn-primary fc-btn-xs">Select</button></td>';
            tr.querySelector('button').addEventListener('click', function() {
                closeModal('searchModal');
                document.getElementById('searchInput').value = '';
                document.getElementById('searchResults').innerHTML =
                    '<tr><td colspan="6" class="fc-empty-cell">Enter a term and click Search.</td></tr>';
                // Clear feedback since we selected from modal
                document.getElementById('idFeedback').style.display = 'none';
                selectStudent(s);
                showToast('Student selected: ' + s.name, 'success');
            });
            tbody.appendChild(tr);
        });
    })
    .catch(function() {
        tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell" style="color:var(--fc-red);">Search failed.</td></tr>';
    });
}

/* ── Submit Fees ── */
function submitFees() {
    if (!FC.userId) { showAlert('Please select a student.', 'error'); return; }
    if (FC.selectedMonths.length === 0) { showAlert('Please select at least one month.', 'error'); return; }

    var paymentMode = document.getElementById('accountSelect').value;
    if (!paymentMode) { showAlert('Please select a payment mode.', 'error'); return; }

    var schoolFees = parseFloat(document.getElementById('submitSchoolFees').value) || 0;
    if (schoolFees <= 0) { showAlert('Please enter the fee amount.', 'error'); return; }

    var fineAmt   = parseFloat(document.getElementById('fineAmount').value) || 0;
    var reference = document.getElementById('reference').value.trim() || 'Fees Submitted';

    var monthTotals = FC.selectedMonths.map(function(m) {
        return { month: m, total: FC.monthFeeMap[m] || 0 };
    });

    var parts = [];
    parts.push('receiptNo='      + encodeURIComponent(RECEIPT_NO));
    parts.push('paymentMode='    + encodeURIComponent(paymentMode));
    parts.push('class='          + encodeURIComponent(FC.className));
    parts.push('userId='         + encodeURIComponent(FC.userId));
    parts.push('submitAmount='   + encodeURIComponent(FC.overpaidAmt));
    parts.push('schoolFees='     + encodeURIComponent(schoolFees.toFixed(2)));
    parts.push('discountAmount=' + encodeURIComponent(FC.discountAmt.toFixed(2)));
    parts.push('fineAmount='     + encodeURIComponent(fineAmt.toFixed(2)));
    parts.push('reference='      + encodeURIComponent(reference));
    FC.selectedMonths.forEach(function(m) { parts.push('selectedMonths[]=' + encodeURIComponent(m)); });
    monthTotals.forEach(function(o, i) {
        parts.push('monthTotals['+i+'][month]=' + encodeURIComponent(o.month));
        parts.push('monthTotals['+i+'][total]=' + encodeURIComponent(o.total));
    });

    var btn = document.getElementById('submitFeesBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting…';

    fetch(SITE_URL + '/fees/submit_fees', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: parts.join('&')
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
        if (resp.status === 'success') {
            showToast('Fees submitted successfully! 🎉', 'success');

            // 1. Hide Step 3 & 4
            document.getElementById('breakdownCard').style.display = 'none';
            document.getElementById('paymentCard').style.display   = 'none';

            // 2. Clear months, reset state
            document.querySelectorAll('.fc-month-tile').forEach(function(t) { t.classList.remove('selected'); });
            FC.selectedMonths = [];
            FC.monthFeeMap    = {};
            FC.grandTotal     = 0;
            FC.discountAmt    = 0;
            FC.overpaidAmt    = 0;
            recalc();

            // 3. FIX: Refresh Receipt Number — old one is now used, get the next one
            fetch(SITE_URL + '/fees/get_receipt_no')
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (d.receiptNo) {
                        RECEIPT_NO = d.receiptNo;
                        document.getElementById('receiptNo').value            = d.receiptNo;
                        document.getElementById('topReceiptNo').textContent   = d.receiptNo;
                        var sumRN = document.getElementById('sumReceiptNo');
                        if (sumRN) sumRN.textContent = d.receiptNo;
                    }
                }).catch(function(){});

            // 4. Also reset Payment Mode highlight
            var acct = document.getElementById('accountSelect');
            if (acct) acct.value = '';
            var spm = document.getElementById('sumPaymentMode');
            if (spm) spm.textContent = '—';

            // 5. Re-fetch months (newly-paid now green)
            fetchMonths(FC.userId);

            // 6. Auto-open Payment History for immediate verification
            setTimeout(function() { loadHistory(); }, 800);
        } else {
            showAlert('Error: ' + (resp.message || 'Unknown error'), 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Fees';
        }
    })
    .catch(function() {
        showAlert('Network error during submission.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Fees';
    });
}

/* ── Load payment history into modal ── */
function loadHistory() {
    if (!FC.userId) { showToast('Select a student first.', 'warning'); return; }

    var tbody = document.getElementById('historyTbody');
    tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell"><i class="fa fa-spinner fa-spin"></i> Loading…</td></tr>';
    openModal('historyModal');

    fetch(SITE_URL + '/fees/fetch_fee_receipts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId: FC.userId })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell">No payment records found.</td></tr>';
            return;
        }
        var tAmt=0, tFin=0, tDis=0;
        data.forEach(function(rec) {
            var amt = parseFloat(String(rec.amount   || rec.Amount  || 0).replace(/,/g,''));
            var fin = parseFloat(String(rec.fine     || rec.Fine    || 0).replace(/,/g,''));
            var dis = parseFloat(String(rec.discount || rec.Discount|| 0).replace(/,/g,''));
            tAmt+=amt; tFin+=fin; tDis+=dis;
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><span class="fc-receipt-pill">F' + rec.receiptNo + '</span></td>' +
                '<td>' + (rec.date||'') + '</td>' +
                '<td style="text-align:right;font-weight:600;">₹ ' + fmtNum(amt) + '</td>' +
                '<td style="text-align:right;">₹ ' + fmtNum(fin) + '</td>' +
                '<td style="text-align:right;color:var(--fc-green);">₹ ' + fmtNum(dis) + '</td>' +
                '<td><span class="fc-mode-pill">' + (rec.account||'N/A') + '</span></td>';
            tbody.appendChild(tr);
        });
        var tot = document.createElement('tr');
        tot.className = 'fc-history-total';
        tot.innerHTML =
            '<td colspan="2"><strong>TOTAL</strong></td>' +
            '<td style="text-align:right;"><strong>₹ ' + fmtNum(tAmt) + '</strong></td>' +
            '<td style="text-align:right;"><strong>₹ ' + fmtNum(tFin) + '</strong></td>' +
            '<td style="text-align:right;"><strong>₹ ' + fmtNum(tDis) + '</strong></td>' +
            '<td></td>';
        tbody.appendChild(tot);

        /* Update already-paid stat */
        FC.alreadyPaid = tAmt;
        recalc();
    })
    .catch(function() {
        tbody.innerHTML = '<tr><td colspan="6" class="fc-empty-cell" style="color:var(--fc-red);">Failed to load history.</td></tr>';
    });
}

/* ── DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', function() {

    /* ── Auto-load student from ?uid= URL param (Collect button in class_fees) ── */
    var urlUid = new URLSearchParams(window.location.search).get('uid');
    if (urlUid) {
        // Pre-fill the ID input immediately so user sees what's happening
        var uidInput  = document.getElementById('user_id');
        var spinner   = document.getElementById('idSpinner');
        var findBtnEl = document.getElementById('findBtn');
        var feedback  = document.getElementById('idFeedback');

        uidInput.value        = urlUid;
        spinner.style.display = 'inline';
        findBtnEl.disabled    = true;

        feedback.className   = 'fc-id-feedback fc-id-feedback-info';
        feedback.innerHTML   = '<i class="fa fa-spinner fa-spin"></i> Auto-loading student <strong>' + urlUid + '</strong>…';
        feedback.style.display = 'block';

        fetch(SITE_URL + '/fees/lookup_student', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'user_id=' + encodeURIComponent(urlUid)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            spinner.style.display = 'none';
            findBtnEl.disabled    = false;

            if (data.error) {
                feedback.className = 'fc-id-feedback fc-id-feedback-error';
                feedback.textContent = '✗ Student not found: ' + data.error;
                showToast('Student ' + urlUid + ' not found.', 'error');
                return;
            }

            selectStudent(data);

            feedback.className = 'fc-id-feedback fc-id-feedback-success';
            feedback.textContent = '✓ Auto-loaded: ' + data.name;
            showToast('Loaded: ' + data.name, 'success');
        })
        .catch(function(e) {
            spinner.style.display = 'none';
            findBtnEl.disabled    = false;
            feedback.className   = 'fc-id-feedback fc-id-feedback-error';
            feedback.textContent = '✗ Network error during auto-load. Enter ID manually.';
            showToast('Auto-load failed — enter student ID manually.', 'error');
            console.error('uid auto-load error:', e);
        });
    }

    /* BUG FIX: Get real server date on page load */
    fetch(SITE_URL + '/fees/get_server_date')
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.date) {
                document.getElementById('fcDate').value = d.date;
                document.getElementById('sumDate').textContent = d.date;
            }
        }).catch(function(){});

    /* Find button → inline lookup (no modal) */
    document.getElementById('findBtn').addEventListener('click', lookupStudentById);

    /* Enter key in ID field → same as Find button */
    document.getElementById('user_id').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); lookupStudentById(); }
    });

    /* Browse button → search modal */
    document.getElementById('openSearchBtn').addEventListener('click', function() {
        openModal('searchModal');
    });
    document.getElementById('doSearchBtn').addEventListener('click', doSearch);
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') doSearch();
    });

    /* Month actions */
    document.getElementById('fetchDetailsBtn').addEventListener('click', fetchFeeDetails);
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        document.querySelectorAll('.fc-month-tile:not(.paid)').forEach(function(t) {
            t.classList.add('selected');
        });
        updateFromTiles();
    });
    document.getElementById('clearAllBtn').addEventListener('click', function() {
        document.querySelectorAll('.fc-month-tile').forEach(function(t) { t.classList.remove('selected'); });
        updateFromTiles();
    });

    /* Breakdown / payment */
    document.getElementById('expandBreakdownBtn').addEventListener('click', function() { openModal('expandModal'); });
    document.getElementById('feesRecordBtn').addEventListener('click', loadHistory);
    document.getElementById('submitFeesBtn').addEventListener('click', submitFees);

    /* Live recalc */
    ['submitSchoolFees','fineAmount'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', recalc);
    });

    /* Payment Mode → live update summary panel */
    var acctEl = document.getElementById('accountSelect');
    if (acctEl) {
        acctEl.addEventListener('change', function() {
            var spm = document.getElementById('sumPaymentMode');
            if (spm) spm.textContent = acctEl.options[acctEl.selectedIndex].text || '—';
        });
    }

    recalc();
});
</script>

<!-- ══ STYLES ══ -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    :root {
        --fc-navy: #0b1f3a;
        --fc-teal: #0e7490;
        --fc-sky: #e0f2fe;
        --fc-green: #16a34a;
        --fc-red: #dc2626;
        --fc-amber: #d97706;
        --fc-blue: #2563eb;
        --fc-text: #1e293b;
        --fc-muted: #64748b;
        --fc-border: #e2e8f0;
        --fc-white: #ffffff;
        --fc-bg: #f1f5f9;
        --fc-shadow: 0 1px 14px rgba(11, 31, 58, .08);
        --fc-radius: 12px;
    }

    * {
        box-sizing: border-box;
    }

    /* Shell */
    .fc-wrap {
        font-family: 'DM Sans', sans-serif;
        background: var(--fc-bg);
        color: var(--fc-text);
        padding: 24px 20px 60px;
        min-height: 100vh;
    }

    /* Top bar */
    .fc-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .fc-page-title {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--fc-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 5px;
    }

    .fc-page-title i {
        color: var(--fc-teal);
    }

    .fc-breadcrumb {
        display: flex;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 12.5px;
        color: var(--fc-muted);
    }

    .fc-breadcrumb a {
        color: var(--fc-teal);
        text-decoration: none;
        font-weight: 500;
    }

    .fc-breadcrumb li::before {
        content: '/';
        margin-right: 6px;
        color: #cbd5e1;
    }

    .fc-breadcrumb li:first-child::before {
        display: none;
    }

    .fc-receipt-badge {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
    }

    .fc-receipt-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: var(--fc-muted);
    }

    .fc-receipt-num {
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--fc-navy);
        line-height: 1;
    }

    /* Stat strip */
    .fc-stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    @media(max-width:768px) {
        .fc-stat-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .fc-stat {
        background: var(--fc-white);
        border-radius: var(--fc-radius);
        box-shadow: var(--fc-shadow);
        border: 1px solid var(--fc-border);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: transform .15s;
    }

    .fc-stat:hover {
        transform: translateY(-2px);
    }

    .fc-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .fc-stat-blue .fc-stat-icon {
        background: #dbeafe;
        color: var(--fc-blue);
    }

    .fc-stat-green .fc-stat-icon {
        background: #dcfce7;
        color: var(--fc-green);
    }

    .fc-stat-amber .fc-stat-icon {
        background: #fef3c7;
        color: var(--fc-amber);
    }

    .fc-stat-red .fc-stat-icon {
        background: #fee2e2;
        color: var(--fc-red);
    }

    .fc-stat-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: var(--fc-muted);
        margin-bottom: 3px;
    }

    .fc-stat-val {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--fc-navy);
    }

    /* Layout */
    .fc-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 18px;
        align-items: start;
    }

    @media(max-width:960px) {
        .fc-layout {
            grid-template-columns: 1fr;
        }

        .fc-right {
            order: -1;
        }
    }

    /* Card */
    .fc-card {
        background: var(--fc-white);
        border-radius: var(--fc-radius);
        box-shadow: var(--fc-shadow);
        border: 1px solid var(--fc-border);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .fc-card-head {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px 18px;
        border-bottom: 1px solid var(--fc-border);
        background: linear-gradient(90deg, var(--fc-sky) 0%, var(--fc-white) 100%);
    }

    .fc-step {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--fc-teal);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .fc-card-head i {
        color: var(--fc-teal);
        flex-shrink: 0;
    }

    .fc-card-head h3 {
        font-family: 'Playfair Display', serif;
        font-size: 14.5px;
        font-weight: 700;
        color: var(--fc-navy);
        margin: 0;
    }

    .fc-head-hint {
        font-size: 11.5px;
        color: var(--fc-muted);
        font-weight: 400;
        margin-left: 4px;
    }

    .fc-card-body {
        padding: 18px;
    }

    /* Grid */
    .fc-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .fc-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .fc-mb {
        margin-bottom: 12px;
    }

    @media(max-width:600px) {

        .fc-grid-2,
        .fc-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    /* Fields */
    .fc-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .fc-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: var(--fc-muted);
    }

    .fc-req {
        color: var(--fc-red);
    }

    .fc-input,
    .fc-select {
        height: 38px;
        padding: 0 10px;
        border: 1.5px solid var(--fc-border);
        border-radius: 8px;
        font-size: 13.5px;
        color: var(--fc-text);
        background: #fafcff;
        font-family: 'DM Sans', sans-serif;
        outline: none;
        width: 100%;
        transition: border-color .13s, box-shadow .13s;
    }

    .fc-input:focus,
    .fc-select:focus {
        border-color: var(--fc-teal);
        box-shadow: 0 0 0 3px rgba(14, 116, 144, .1);
        background: #fff;
    }

    .fc-input[readonly] {
        background: #f1f5f9;
        color: var(--fc-muted);
        cursor: default;
    }

    .fc-input-green {
        color: var(--fc-green) !important;
        font-weight: 600;
    }

    .fc-input-primary {
        border-color: var(--fc-teal);
        font-weight: 600;
    }

    .fc-select-wrap {
        position: relative;
    }

    .fc-select {
        appearance: none;
        padding-right: 28px;
        cursor: pointer;
    }

    .fc-select-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--fc-muted);
        font-size: 10px;
        pointer-events: none;
    }

    /* Student row — Find + Browse side by side */
    .fc-student-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .fc-id-wrap {
        position: relative;
        flex: 1;
    }

    .fc-id-wrap .fc-input {
        width: 100%;
    }

    .fc-id-spinner {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--fc-teal);
        font-size: 14px;
    }

    .fc-id-feedback {
        font-size: 12.5px;
        font-weight: 500;
        margin-top: 5px;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .fc-id-feedback-success {
        color: var(--fc-green);
        background: #f0fdf4;
        border: 1px solid #86efac;
    }

    .fc-id-feedback-error {
        color: var(--fc-red);
        background: #fef2f2;
        border: 1px solid #fca5a5;
    }

    /* Month tiles */
    .fc-month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
        margin-bottom: 14px;
        min-height: 60px;
    }

    .fc-month-tile {
        border: 2px solid var(--fc-border);
        border-radius: 10px;
        padding: 10px 8px;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: border-color .15s, background .15s, transform .1s;
        background: var(--fc-white);
        user-select: none;
    }

    .fc-month-tile:hover:not(.paid) {
        border-color: var(--fc-teal);
        background: var(--fc-sky);
        transform: translateY(-1px);
    }

    .fc-month-tile.selected {
        border-color: var(--fc-teal);
        background: var(--fc-sky);
    }

    .fc-month-tile.selected .fc-month-name {
        color: var(--fc-teal);
        font-weight: 700;
    }

    .fc-month-tile.paid {
        background: #f0fdf4;
        border-color: #86efac;
        cursor: not-allowed;
        opacity: .75;
    }

    .fc-month-name {
        font-size: 12px;
        font-weight: 600;
        color: var(--fc-navy);
        margin-bottom: 4px;
    }

    .fc-month-status {
        font-size: 10px;
        color: var(--fc-muted);
    }

    .fc-month-tile.paid .fc-month-status {
        color: var(--fc-green);
    }

    .fc-months-placeholder {
        text-align: center;
        color: var(--fc-muted);
        padding: 30px 20px;
        grid-column: 1/-1;
    }

    .fc-months-placeholder i {
        font-size: 28px;
        opacity: .35;
        display: block;
        margin-bottom: 8px;
    }

    .fc-months-placeholder p {
        margin: 0;
        font-size: 13.5px;
    }

    .fc-month-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        padding-top: 10px;
        border-top: 1px solid var(--fc-border);
    }

    /* Due bar */
    .fc-due-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        background: var(--fc-bg);
        border: 1px solid var(--fc-border);
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 18px;
    }

    .fc-due-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .fc-due-item-big .fc-due-label {
        color: var(--fc-red);
    }

    .fc-due-sep {
        font-size: 18px;
        font-weight: 700;
        color: var(--fc-muted);
        padding: 0 4px;
    }

    .fc-due-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: var(--fc-muted);
    }

    .fc-due-val {
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        font-weight: 700;
        color: var(--fc-navy);
    }

    .fc-due-val.fc-green {
        color: var(--fc-green);
    }

    .fc-due-val.fc-red {
        color: var(--fc-red);
        font-size: 20px;
    }

    /* Table */
    .fc-table-wrap {
        overflow-x: auto;
    }

    .fc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .fc-table thead tr {
        background: var(--fc-navy);
    }

    .fc-table thead th {
        padding: 9px 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .85);
        white-space: nowrap;
    }

    .fc-table td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--fc-border);
        vertical-align: middle;
    }

    .fc-table tbody tr:hover {
        background: var(--fc-sky);
    }

    .fc-table tfoot td {
        background: #e0f2fe;
        font-weight: 700;
        border-top: 2px solid var(--fc-teal);
        padding: 10px 12px;
    }

    .fc-empty-cell {
        text-align: center;
        color: var(--fc-muted);
        padding: 28px !important;
    }

    .fc-history-total td {
        background: #f0fdf4;
        border-top: 2px solid var(--fc-green);
    }

    /* Pills */
    .fc-receipt-pill {
        background: var(--fc-sky);
        color: var(--fc-teal);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .fc-mode-pill {
        background: #f1f5f9;
        color: var(--fc-muted);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 12px;
    }

    .fc-id-pill {
        background: #dbeafe;
        color: var(--fc-blue);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    /* Action bar */
    .fc-action-bar {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    /* Buttons */
    .fc-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 8px;
        border: none;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: opacity .13s, transform .1s;
    }

    .fc-btn:hover:not(:disabled) {
        opacity: .85;
        transform: translateY(-1px);
    }

    .fc-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
        transform: none;
    }

    .fc-btn-sm {
        padding: 7px 14px;
        font-size: 12.5px;
    }

    .fc-btn-xs {
        padding: 5px 10px;
        font-size: 12px;
    }

    .fc-btn-primary {
        background: var(--fc-teal);
        color: #fff;
        box-shadow: 0 2px 8px rgba(14, 116, 144, .28);
    }

    .fc-btn-amber {
        background: var(--fc-amber);
        color: #fff;
    }

    .fc-btn-ghost {
        background: var(--fc-white);
        color: var(--fc-text);
        border: 1.5px solid var(--fc-border);
    }

    .fc-btn-ghost:hover {
        border-color: var(--fc-teal);
        color: var(--fc-teal);
        opacity: 1;
    }

    .fc-btn-info {
        background: var(--fc-blue);
        color: #fff;
    }

    .fc-btn-submit {
        background: var(--fc-navy);
        color: #fff;
        box-shadow: 0 2px 10px rgba(11, 31, 58, .3);
        font-size: 14px;
        padding: 10px 24px;
    }

    .fc-btn-submit:hover {
        background: #162d50;
        opacity: 1;
    }

    /* Alert */
    .fc-alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 14px;
        font-size: 13.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fc-alert-danger {
        background: #fee2e2;
        color: var(--fc-red);
        border: 1px solid #fca5a5;
    }

    .fc-alert-warning {
        background: #fef3c7;
        color: var(--fc-amber);
        border: 1px solid #fde68a;
    }

    .fc-alert-info {
        background: #e0f2fe;
        color: var(--fc-teal);
        border: 1px solid #bae6fd;
    }

    .fc-alert-success {
        background: #f0fdf4;
        color: var(--fc-green);
        border: 1px solid #86efac;
    }

    /* Summary card (right) */
    .fc-right {
        position: sticky;
        top: 16px;
    }

    .fc-summary-card {
        background: var(--fc-navy);
        border-radius: var(--fc-radius);
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(11, 31, 58, .2);
    }

    .fc-summary-head {
        padding: 14px 18px;
        color: rgba(255, 255, 255, .7);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, .1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fc-summary-body {
        padding: 16px 18px;
    }

    .fc-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        font-size: 13px;
        color: rgba(255, 255, 255, .65);
    }

    .fc-summary-row strong {
        color: rgba(255, 255, 255, .9);
        font-size: 13.5px;
    }

    .fc-summary-row.fc-green strong {
        color: #4ade80;
    }

    .fc-summary-divider {
        border-bottom: 1px solid rgba(255, 255, 255, .15);
        margin: 6px 0;
    }

    .fc-summary-due {
        color: rgba(255, 255, 255, .8);
        background: rgba(220, 38, 38, .18);
        margin: 0 -18px;
        padding: 10px 18px !important;
        border: none !important;
    }

    .fc-summary-due strong {
        color: #f87171 !important;
        font-size: 17px !important;
    }

    .fc-summary-payable {
        background: rgba(14, 116, 144, .25);
        margin: 0 -18px;
        padding: 10px 18px !important;
        border: none !important;
    }

    .fc-summary-payable strong {
        color: #7dd3fc !important;
        font-size: 16px !important;
    }

    /* Modal */
    .fc-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11, 31, 58, .55);
        z-index: 9000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .fc-overlay.open {
        display: flex;
    }

    .fc-modal {
        background: var(--fc-white);
        border-radius: var(--fc-radius);
        box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        width: 100%;
        max-width: 620px;
        max-height: 88vh;
        overflow-y: auto;
        animation: fc-modal-in .2s ease;
    }

    .fc-modal-wide {
        max-width: 980px;
    }

    .fc-modal-head {
        background: var(--fc-navy);
        color: #fff;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 2;
        border-radius: var(--fc-radius) var(--fc-radius) 0 0;
    }

    .fc-modal-head h4 {
        font-family: 'Playfair Display', serif;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fc-modal-close {
        background: none;
        border: none;
        color: rgba(255, 255, 255, .65);
        font-size: 22px;
        cursor: pointer;
        transition: color .13s;
        line-height: 1;
    }

    .fc-modal-close:hover {
        color: #fff;
    }

    .fc-modal-body {
        padding: 20px;
    }

    @keyframes fc-modal-in {
        from {
            transform: translateY(16px);
            opacity: 0
        }

        to {
            transform: translateY(0);
            opacity: 1
        }
    }

    .fc-search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
    }

    .fc-search-box .fc-input {
        flex: 1;
    }

    /* Toast */
    .fc-toast-wrap {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 8px;
        pointer-events: none;
    }

    .fc-toast {
        padding: 12px 18px;
        border-radius: 10px;
        color: #fff;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .2);
        display: flex;
        align-items: center;
        gap: 9px;
        animation: fc-toast-in .22s ease;
        max-width: 320px;
        pointer-events: auto;
        transition: opacity .3s;
    }

    .fc-toast-hide {
        opacity: 0;
    }

    .fc-toast-success {
        background: var(--fc-green);
    }

    .fc-toast-error {
        background: var(--fc-red);
    }

    .fc-toast-warning {
        background: var(--fc-amber);
    }

    .fc-toast-info {
        background: var(--fc-teal);
    }

    @keyframes fc-toast-in {
        from {
            transform: translateX(20px);
            opacity: 0
        }

        to {
            transform: translateX(0);
            opacity: 1
        }
    }

    /* Summary panel — Payment History button */
    .fc-summary-history {
        padding: 14px 18px;
        border-top: 1px solid rgba(255, 255, 255, .1);
    }

    .fc-btn-history-full {
        width: 100%;
        justify-content: center;
        background: rgba(14, 116, 144, .35);
        color: #7dd3fc;
        border: 1px solid rgba(14, 116, 144, .5);
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        transition: background .15s;
    }

    .fc-btn-history-full:hover {
        background: rgba(14, 116, 144, .55);
        color: #fff;
        opacity: 1;
    }

    /* Utilities */
    .fc-green {
        color: var(--fc-green);
    }

    .fc-red {
        color: var(--fc-red);
    }
</style>