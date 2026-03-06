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
    <div class="sfr-page">

        <!-- ════════════════════════════════════
         PAGE HEADER
         ════════════════════════════════════ -->
        <div class="sfr-page-hd">
            <h1 class="sfr-page-title">
                <i class="fa fa-history"></i>
                Student Fee Receipts
            </h1>
            <ol class="sfr-breadcrumb">
                <li><a href="<?= base_url() ?>"><i class="fa fa-home"></i> Dashboard</a></li>
                <li><a href="<?= site_url('fees/fees_records') ?>">Fees</a></li>
                <li>Student Fee Receipts</li>
            </ol>
        </div>

        <!-- ════════════════════════════════════
         STAT CARDS — hidden until data loads
         ════════════════════════════════════ -->
        <div class="sfr-stats" id="sfrStats">
            <div class="sfr-stat">
                <div class="sfr-stat-ico ico-teal"><i class="fa fa-list-ol"></i></div>
                <div>
                    <div class="sfr-stat-label">Total Receipts</div>
                    <div class="sfr-stat-val" id="sfrStCount">0</div>
                </div>
            </div>
            <div class="sfr-stat">
                <div class="sfr-stat-ico ico-green"><i class="fa fa-inr"></i></div>
                <div>
                    <div class="sfr-stat-label">Total Paid</div>
                    <div class="sfr-stat-val" style="color:#16a34a;" id="sfrStPaid">₹ 0</div>
                </div>
            </div>
            <div class="sfr-stat">
                <div class="sfr-stat-ico ico-red"><i class="fa fa-exclamation-circle"></i></div>
                <div>
                    <div class="sfr-stat-label">Total Fine</div>
                    <div class="sfr-stat-val" style="color:#dc2626;" id="sfrStFine">₹ 0</div>
                </div>
            </div>
            <div class="sfr-stat">
                <div class="sfr-stat-ico ico-amber"><i class="fa fa-tag"></i></div>
                <div>
                    <div class="sfr-stat-label">Total Discount</div>
                    <div class="sfr-stat-val" style="color:#d97706;" id="sfrStDisc">₹ 0</div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
         SEARCH CARD
         ════════════════════════════════════ -->
        <div class="sfr-card">
            <div class="sfr-card-hd">
                <div class="sfr-card-hd-left">
                    <i class="fa fa-search"></i>
                    <h3>Look Up Student</h3>
                </div>
            </div>
            <div class="sfr-card-body">
                <div class="sfr-search-row">
                    <div class="sfr-field">
                        <label class="sfr-field-lbl">Student ID <span>*</span></label>
                        <div class="sfr-input-wr">
                            <i class="fa fa-id-card-o sfr-input-ico"></i>
                            <!-- accepts STU0006, STU0007 — not numeric-only -->
                            <input type="text" id="sfUserId" class="sfr-input" placeholder="e.g. STU0006, STU0007…"
                                autocomplete="off">
                        </div>
                    </div>
                    <button class="sfr-btn sfr-btn-primary" onclick="loadReceipts()">
                        <i class="fa fa-search"></i> Fetch Receipts
                    </button>
                    <button class="sfr-btn sfr-btn-ghost" onclick="clearSearch()">
                        <i class="fa fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
         STUDENT BANNER — dark navy gradient
         Replaces the broken border-left strip
         ════════════════════════════════════ -->
        <div class="sfr-banner" id="sfStudentStrip">
            <div class="sfr-banner-inner">
                <div class="sfr-avatar" id="sfAvatar">??</div>
                <div class="sfr-banner-fields">
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Student ID</div>
                        <div class="sfr-bf-val teal" id="sfDispId">—</div>
                    </div>
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Name</div>
                        <div class="sfr-bf-val" id="sfDispName">—</div>
                    </div>
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Father's Name</div>
                        <div class="sfr-bf-val" id="sfDispFather">—</div>
                    </div>
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Class &amp; Section</div>
                        <div class="sfr-bf-val" id="sfDispClass">—</div>
                    </div>
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Receipts</div>
                        <div class="sfr-bf-val amber" id="sfDispCount">0</div>
                    </div>
                    <div class="sfr-bf">
                        <div class="sfr-bf-lbl">Total Paid</div>
                        <div class="sfr-bf-val green" id="sfDispTotal">₹ 0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════
         PAYMENT HISTORY CARD
         ════════════════════════════════════ -->
        <div class="sfr-card" id="sfReceiptsCard">
            <div class="sfr-card-hd">
                <div class="sfr-card-hd-left">
                    <i class="fa fa-file-text-o"></i>
                    <h3>Payment History</h3>
                </div>
                <span class="sfr-count" id="sfRowCount"></span>
            </div>

            <!-- Inline filter — appears only when > 5 records -->
            <div class="sfr-filter-bar" id="sfFilterBar">
                <div class="sfr-filter-wr">
                    <i class="fa fa-search sfr-filter-ico"></i>
                    <input type="text" class="sfr-filter-inp" id="sfTableSearch"
                        placeholder="Filter by date, mode, reference…" autocomplete="off">
                </div>
                <span class="sfr-count" id="sfFilterCount"></span>
            </div>

            <div class="sfr-tbl-wr">
                <table class="sfr-tbl">
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th>Receipt No.</th>
                            <th>Date</th>
                            <th>Student &amp; Father</th>
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
                            <td colspan="10" class="sfr-state">
                                <i class="fa fa-search sfr-state-ico"></i>
                                <p class="sfr-state-ttl">No student selected</p>
                                <p class="sfr-state-sub">Enter a student ID above and click Fetch Receipts.</p>
                            </td>
                        </tr>
                    </tbody>
                    <!-- Footer: solid dark navy, teal top border, coloured totals -->
                    <tfoot id="sfReceiptsFoot">
                        <tr>
                            <td class="sfr-tfoot-lbl" colspan="5">TOTALS</td>
                            <td class="sfr-tfoot-paid" id="sfFootAmt">₹ 0.00</td>
                            <td class="sfr-tfoot-fine" id="sfFootFin">₹ 0.00</td>
                            <td class="sfr-tfoot-disc" id="sfFootDis">₹ 0.00</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div><!-- /.sfr-page -->
</div><!-- /.content-wrapper -->

<div class="sfr-toasts" id="sfToastWrap"></div>


<script>
/* ═══════════════════════════════════════════════════════════
   All existing bug fixes retained:
   ① postForm() — CSRF token in FormData $_POST → no 403
   ② No isNaN check — STU0006 accepted
   ③ SyntaxError resolved because ① stops the HTML 403 response
   ═══════════════════════════════════════════════════════════ */

var CSRF_NAME = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
var CSRF_HASH = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var SITE_URL = '<?= rtrim(site_url(), '/') ?>';

/* ── Helpers ── */
function fmtRs(n) {
    n = parseFloat(String(n || 0).replace(/,/g, '')) || 0;
    return '₹ ' + n.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function initials(s) {
    var p = String(s || '').trim().split(/\s+/);
    return p.length >= 2 ? (p[0][0] + p[1][0]).toUpperCase() : (p[0] || '?').slice(0, 2).toUpperCase();
}

function toast(msg, type) {
    type = type || 'success';
    var ico = {
        success: 'check-circle',
        error: 'times-circle',
        warning: 'exclamation-triangle'
    } [type];
    var el = document.createElement('div');
    el.className = 'sfr-toast t-' + type;
    el.innerHTML = '<i class="fa fa-' + ico + '"></i> ' + msg;
    document.getElementById('sfToastWrap').appendChild(el);
    setTimeout(function() {
        el.classList.add('sfr-toast-hide');
        setTimeout(function() {
            el.remove();
        }, 320);
    }, 3500);
}

/* CSRF in FormData body (layer 1: CI filter reads $_POST)
   + X-CSRF-Token header (layer 2: MY_Controller check) */
function postForm(url, params) {
    var fd = new FormData();
    fd.append(CSRF_NAME, CSRF_HASH);
    if (params) Object.keys(params).forEach(function(k) {
        fd.append(k, params[k]);
    });
    return fetch(url, {
        method: 'POST',
        body: fd,
        headers: {
            'X-CSRF-Token': CSRF_HASH,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function(r) {
        if (!r.ok) return r.text().then(function(t) {
            throw new Error('HTTP ' + r.status + ' — ' + t.slice(0, 120));
        });
        return r.json();
    });
}

/* Inline table filter */
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('sfTableSearch').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        var rows = document.querySelectorAll('#sfReceiptsTbody tr[data-s]');
        var n = 0;
        rows.forEach(function(r) {
            var show = !q || r.dataset.s.includes(q);
            r.style.display = show ? '' : 'none';
            if (show) n++;
        });
        document.getElementById('sfFilterCount').textContent = n + ' record(s)';
    });
});

/* ════════════════
   loadReceipts()
   ════════════════ */
function loadReceipts() {
    var userId = document.getElementById('sfUserId').value.trim();
    if (!userId) {
        toast('Please enter a student ID (e.g. STU0006).', 'warning');
        document.getElementById('sfUserId').focus();
        return;
    }

    var tbody = document.getElementById('sfReceiptsTbody');
    tbody.innerHTML =
        '<tr><td colspan="10" class="sfr-state">' +
        '<i class="fa fa-spinner fa-spin sfr-state-ico" style="opacity:.6;"></i>' +
        '<p class="sfr-state-ttl">Loading…</p>' +
        '<p class="sfr-state-sub">Fetching records for <strong>' + userId + '</strong></p>' +
        '</td></tr>';

    document.getElementById('sfReceiptsFoot').style.display = 'none';
    document.getElementById('sfFilterBar').style.display = 'none';
    document.getElementById('sfRowCount').textContent = '';
    document.getElementById('sfStudentStrip').classList.remove('visible');
    document.getElementById('sfrStats').style.display = 'none';

    postForm(SITE_URL + '/fees/fetch_fee_receipts', {
            userId: userId
        })
        .then(function(data) {
            tbody.innerHTML = '';

            if (!Array.isArray(data) || !data.length) {
                tbody.innerHTML =
                    '<tr><td colspan="10" class="sfr-state">' +
                    '<i class="fa fa-inbox sfr-state-ico"></i>' +
                    '<p class="sfr-state-ttl">No payment records found</p>' +
                    '<p class="sfr-state-sub">Student <strong>' + userId +
                    '</strong> has no fee receipts yet.</p>' +
                    '</td></tr>';
                return;
            }

            /* ── Student banner ── */
            var first = data[0];
            var parts = (first.student || '').split('/');
            var name = (parts[0] || '').trim();
            var father = (parts[1] || '').trim();

            document.getElementById('sfAvatar').textContent = initials(name);
            document.getElementById('sfDispId').textContent = first.Id || userId;
            document.getElementById('sfDispName').textContent = name || '—';
            document.getElementById('sfDispFather').textContent = father || '—';
            document.getElementById('sfDispClass').textContent = first.class || '—';
            document.getElementById('sfDispCount').textContent = data.length;
            document.getElementById('sfStudentStrip').classList.add('visible');

            /* ── Rows ── */
            var tAmt = 0,
                tFin = 0,
                tDis = 0;

            data.forEach(function(rec, i) {
                var amt = parseFloat(String(rec.amount || 0).replace(/,/g, '')) || 0;
                var fin = parseFloat(String(rec.fine || 0).replace(/,/g, '')) || 0;
                var dis = parseFloat(String(rec.discount || 0).replace(/,/g, '')) || 0;
                tAmt += amt;
                tFin += fin;
                tDis += dis;

                var searchKey = [rec.receiptNo, rec.date, first.student, rec.class,
                    rec.account, rec.reference
                ].join(' ').toLowerCase();

                var tr = document.createElement('tr');
                tr.setAttribute('data-s', searchKey);
                tr.innerHTML =
                    /* # */
                    '<td style="color:#9ca3af;font-weight:600;font-size:12px;">' + (i + 1) + '</td>' +
                    /* Receipt No */
                    '<td><span class="sfr-pill"><i class="fa fa-hashtag"></i>' + (rec.receiptNo || '—') +
                    '</span></td>' +
                    /* Date */
                    '<td style="white-space:nowrap;color:#374151;">' +
                    '<i class="fa fa-calendar-o" style="color:#9ca3af;margin-right:5px;font-size:11px;"></i>' +
                    (rec.date || '—') +
                    '</td>' +
                    /* Student + father — avatar + stacked text */
                    '<td>' +
                    '<div style="display:flex;align-items:center;">' +
                    '<span class="sfr-ico-cell">' + initials(name) + '</span>' +
                    '<div class="sfr-sname">' + name +
                    (father ? '<span>S/o ' + father + '</span>' : '') +
                    '</div>' +
                    '</div>' +
                    '</td>' +
                    /* Class */
                    '<td style="font-size:12px;color:#374151;white-space:nowrap;">' + (rec.class || '—') +
                    '</td>' +
                    /* Amount Paid */
                    '<td><span class="c-paid">' + fmtRs(amt) + '</span></td>' +
                    /* Fine — dash if zero */
                    '<td>' + (fin > 0 ? '<span class="c-fine">' + fmtRs(fin) + '</span>' :
                        '<span class="c-mute">—</span>') + '</td>' +
                    /* Discount — dash if zero */
                    '<td>' + (dis > 0 ? '<span class="c-disc">' + fmtRs(dis) + '</span>' :
                        '<span class="c-mute">—</span>') + '</td>' +
                    /* Mode */
                    '<td><span class="sfr-mode">' + (rec.account || 'N/A') + '</span></td>' +
                    /* Reference */
                    '<td style="color:#6b7280;font-size:12px;max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                    (rec.reference || '—') +
                    '</td>';

                tbody.appendChild(tr);
            });

            /* ── Footer totals ── */
            document.getElementById('sfFootAmt').textContent = fmtRs(tAmt);
            document.getElementById('sfFootFin').textContent = fmtRs(tFin);
            document.getElementById('sfFootDis').textContent = fmtRs(tDis);
            document.getElementById('sfReceiptsFoot').style.display = ''; /* reveal navy footer */

            /* ── Banner total ── */
            document.getElementById('sfDispTotal').textContent = fmtRs(tAmt);

            /* ── Stat cards ── */
            document.getElementById('sfrStCount').textContent = data.length;
            document.getElementById('sfrStPaid').textContent = fmtRs(tAmt);
            document.getElementById('sfrStFine').textContent = fmtRs(tFin);
            document.getElementById('sfrStDisc').textContent = fmtRs(tDis);
            document.getElementById('sfrStats').style.display = 'grid';

            /* ── Row count + filter ── */
            document.getElementById('sfRowCount').textContent = data.length + ' record(s)';
            document.getElementById('sfFilterCount').textContent = data.length + ' record(s)';
            if (data.length > 5) document.getElementById('sfFilterBar').style.display = 'flex';
        })
        .catch(function(err) {
            console.error('fetch_fee_receipts:', err);
            tbody.innerHTML =
                '<tr><td colspan="10" class="sfr-state">' +
                '<i class="fa fa-exclamation-circle sfr-state-ico" style="color:#dc2626;opacity:.7;"></i>' +
                '<p class="sfr-state-ttl" style="color:#dc2626;">Failed to load receipts</p>' +
                '<p class="sfr-state-sub">' + (err.message || 'Please try again.') + '</p>' +
                '</td></tr>';
            toast('Failed to load receipts.', 'error');
        });
}

/* ════════════════
   clearSearch()
   ════════════════ */
function clearSearch() {
    document.getElementById('sfUserId').value = '';
    document.getElementById('sfTableSearch').value = '';
    document.getElementById('sfReceiptsFoot').style.display = 'none';
    document.getElementById('sfFilterBar').style.display = 'none';
    document.getElementById('sfrStats').style.display = 'none';
    document.getElementById('sfRowCount').textContent = '';
    document.getElementById('sfStudentStrip').classList.remove('visible');
    document.getElementById('sfReceiptsTbody').innerHTML =
        '<tr><td colspan="10" class="sfr-state">' +
        '<i class="fa fa-search sfr-state-ico"></i>' +
        '<p class="sfr-state-ttl">No student selected</p>' +
        '<p class="sfr-state-sub">Enter a student ID above and click Fetch Receipts.</p>' +
        '</td></tr>';
}

/* Auto-load from ?userId=STU0007 */
(function() {
    var uid = new URLSearchParams(window.location.search).get('userId') || '';
    if (uid) {
        document.getElementById('sfUserId').value = uid;
        setTimeout(loadReceipts, 120);
    }
})();

/* Enter key */
document.getElementById('sfUserId').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') loadReceipts();
});
</script>


<style>
/*
 * student_fees.php — All CSS variables removed and replaced with
 * hardcoded values taken directly from the app screenshots.
 *
 * Every var(--gold), var(--bg), var(--t1), var(--font-d) etc.
 * was resolving to nothing → broken layout, wrong colours.
 *
 * Exact palette from screenshots:
 *   Page bg        #f4f6f9
 *   Card bg        #ffffff
 *   Card head bg   #f8fafc
 *   Table header   #1a2332  (solid dark navy)
 *   Table footer   #1a2332  (same navy — "Class Total" row style)
 *   Teal/primary   #0d9488
 *   Body text      #1a2332
 *   Secondary text #6b7280
 *   Border         #e5e7eb
 *   Row separator  #f1f5f9
 *   Row hover      #f0fdfa
 *   Green amounts  #16a34a
 *   Red amounts    #dc2626
 *   Amber amounts  #d97706
 */

/* ── Page ── */
.sfr-page {
    padding: 24px 28px;
    background: #f4f6f9;
    min-height: 100vh;
}

/* ════════════════════════════
   PAGE HEADER
   Exact match: "₹ Fee Counter" / "Cash Book" title style
   Navy bold title, teal icon, small grey breadcrumb below
   ════════════════════════════ */
.sfr-page-hd { margin-bottom: 26px; }

.sfr-page-title {
    font-size: 23px;
    font-weight: 800;
    color: #1a2332;          /* was: var(--t1) — undefined */
    margin: 0 0 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -.3px;
    line-height: 1.2;
}
.sfr-page-title i {
    color: #0d9488;          /* was: var(--gold) — undefined */
    font-size: 21px;
}

.sfr-breadcrumb {
    list-style: none;
    padding: 0; margin: 0;
    display: flex; align-items: center; gap: 6px;
    font-size: 12.5px;
    color: #9ca3af;          /* was: var(--t3) — undefined */
}
.sfr-breadcrumb li + li::before { content: '/'; color: #d1d5db; margin-right: 6px; }
.sfr-breadcrumb a {
    color: #0d9488;          /* was: var(--gold) — undefined */
    text-decoration: none; font-weight: 500;
}
.sfr-breadcrumb a:hover { text-decoration: underline; }

/* ════════════════════════════
   STAT CARDS
   Same as fee counter TOTAL FEE / ALREADY PAID / DISCOUNT / DUE AMOUNT cards
   White card, coloured left border, coloured icon box
   ════════════════════════════ */
.sfr-stats {
    display: none;           /* revealed via JS once data loads */
    grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.sfr-stat {
    background: #ffffff;     /* was: var(--bg2) — undefined */
    border-radius: 12px;
    padding: 18px 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.04);
    border: 1px solid #f1f5f9;
    transition: transform .18s, box-shadow .18s;
}
.sfr-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,.09);
}
.sfr-stat-ico {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0;
}
.ico-teal  { background: #f0fdfa; color: #0d9488; }
.ico-green { background: #f0fdf4; color: #16a34a; }
.ico-red   { background: #fef2f2; color: #dc2626; }
.ico-amber { background: #fffbeb; color: #d97706; }
.sfr-stat-label {
    font-size: 10.5px; font-weight: 700; color: #9ca3af;
    text-transform: uppercase; letter-spacing: .6px; margin-bottom: 4px;
}
.sfr-stat-val {
    font-size: 21px; font-weight: 800; color: #1a2332; line-height: 1;
}

/* ════════════════════════════
   CARDS
   White body, light-grey head (#f8fafc), 1px #f1f5f9 border
   ════════════════════════════ */
.sfr-card {
    background: #ffffff;     /* was: var(--bg2) — undefined */
    border-radius: 12px;
    border: 1px solid #f1f5f9; /* was: var(--border) — undefined */
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.04);
    margin-bottom: 20px;
    overflow: hidden;
}
.sfr-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px;
    background: #f8fafc;     /* was: var(--bg3) — undefined */
    border-bottom: 1.5px solid #f1f5f9; /* was: var(--border) — undefined */
    gap: 12px; flex-wrap: wrap;
}
.sfr-card-hd-left { display: flex; align-items: center; gap: 10px; }
.sfr-card-hd-left i { color: #0d9488; font-size: 15px; } /* was: var(--gold) — undefined */
.sfr-card-hd-left h3 {
    margin: 0; font-size: 14px; font-weight: 700;
    color: #1a2332;          /* was: var(--t2) — undefined */
    letter-spacing: -.1px;
}
.sfr-card-body { padding: 20px; }

/* ════════════════════════════
   SEARCH ROW
   ════════════════════════════ */
.sfr-search-row {
    display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
}
.sfr-field { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 220px; }
.sfr-field-lbl {
    font-size: 11px; font-weight: 700; color: #374151; /* was: var(--t2) — undefined */
    text-transform: uppercase; letter-spacing: .55px;
}
.sfr-field-lbl span { color: #dc2626; }
.sfr-input-wr { position: relative; }
.sfr-input-ico {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none;
}
.sfr-input {
    width: 100%; box-sizing: border-box;
    padding: 10px 14px 10px 36px;
    border: 1.5px solid #e5e7eb; /* was: var(--brd2) — undefined */
    border-radius: 8px;
    font-size: 13.5px; font-weight: 500;
    color: #1a2332;            /* was: var(--t1) — undefined */
    background: #ffffff;       /* was: var(--bg3) — undefined */
    outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.sfr-input:focus {
    border-color: #0d9488;     /* was: var(--gold) — undefined */
    box-shadow: 0 0 0 3px rgba(13,148,136,.13);
}
.sfr-input::placeholder { color: #b0b8c4; }

.sfr-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px; border-radius: 8px;
    font-size: 13.5px; font-weight: 700;
    cursor: pointer; border: none;
    transition: all .18s; white-space: nowrap; line-height: 1;
}
.sfr-btn-primary {
    background: #0d9488;       /* was: var(--gold) — undefined */
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(13,148,136,.28);
}
.sfr-btn-primary:hover {
    background: #0f766e;
    box-shadow: 0 4px 16px rgba(13,148,136,.38);
    transform: translateY(-1px);
}
.sfr-btn-ghost {
    background: #ffffff;       /* was: var(--bg3) — undefined */
    color: #374151;            /* was: var(--t2) — undefined */
    border: 1.5px solid #e5e7eb; /* was: var(--brd2) — undefined */
}
.sfr-btn-ghost:hover { border-color: #0d9488; color: #0d9488; background: #f0fdfa; }

/* ════════════════════════════
   STUDENT INFO BANNER
   Dark navy gradient — same feel as fee counter's PAYMENT SUMMARY panel
   Replaces the old border-left strip that used var() variables
   ════════════════════════════ */
.sfr-banner {
    display: none;
    background: linear-gradient(135deg, #1a2332 0%, #22304a 55%, #1e3a5a 100%);
    border-radius: 12px;
    padding: 20px 26px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(26,35,50,.3);
    position: relative; overflow: hidden;
}
/* decorative bg circles */
.sfr-banner::before {
    content: ''; position: absolute;
    right: -40px; top: -40px;
    width: 200px; height: 200px;
    background: rgba(13,148,136,.1); border-radius: 50%; pointer-events: none;
}
.sfr-banner::after {
    content: ''; position: absolute;
    right: 80px; bottom: -60px;
    width: 140px; height: 140px;
    background: rgba(13,148,136,.06); border-radius: 50%; pointer-events: none;
}
.sfr-banner.visible { display: block; }

.sfr-banner-inner {
    display: flex; align-items: center; flex-wrap: wrap; gap: 0; position: relative; z-index: 1;
}
/* initials circle — like "BA" "PR" teal circles in due fees screenshot */
.sfr-avatar {
    width: 52px; height: 52px; border-radius: 14px;
    background: #0d9488;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; font-weight: 800; color: #fff;
    flex-shrink: 0; margin-right: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,.22);
}
.sfr-banner-fields { display: flex; align-items: center; flex-wrap: wrap; gap: 0; flex: 1; }
.sfr-bf {
    padding: 0 22px;
    border-right: 1px solid rgba(255,255,255,.1);
}
.sfr-bf:first-child { padding-left: 0; }
.sfr-bf:last-child  { border-right: none; }
.sfr-bf-lbl {
    font-size: 10px; color: rgba(255,255,255,.5);
    text-transform: uppercase; letter-spacing: .7px; font-weight: 600; margin-bottom: 5px;
}
.sfr-bf-val { font-size: 14px; font-weight: 700; color: #fff; white-space: nowrap; }
.sfr-bf-val.teal  { color: #5eead4; }
.sfr-bf-val.green { color: #86efac; }
.sfr-bf-val.amber { color: #fcd34d; }

/* ════════════════════════════
   FILTER BAR (inside card, above table)
   Matches "Search student or father name…" bar in due fees screenshot
   ════════════════════════════ */
.sfr-filter-bar {
    display: none; align-items: center; justify-content: space-between;
    padding: 12px 20px; border-bottom: 1px solid #f1f5f9;
    background: #fafbfc; gap: 12px; flex-wrap: wrap;
}
.sfr-filter-wr { position: relative; }
.sfr-filter-ico {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none;
}
.sfr-filter-inp {
    padding: 8px 14px 8px 32px;
    border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 13px; color: #1a2332; background: #fff;
    outline: none; transition: border-color .18s; min-width: 240px;
}
.sfr-filter-inp:focus { border-color: #0d9488; }
.sfr-count { font-size: 12px; color: #6b7280; font-weight: 600; white-space: nowrap; }

/* ════════════════════════════
   TABLE
   Header  : solid #1a2332 navy (from both screenshots — NOT a gradient)
   Rows    : white, #f0fdfa on hover
   Footer  : solid #1a2332 navy with 3px teal top border
             This matches the "Class Total" row in the due fees screenshot exactly
   ════════════════════════════ */
.sfr-tbl-wr { overflow-x: auto; }
.sfr-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }

/* Header — solid dark navy, uppercase small caps */
.sfr-tbl thead tr { background: #1a2332; } /* was: linear-gradient(var(--gold)…) — undefined */
.sfr-tbl thead th {
    padding: 13px 16px;
    font-size: 11px; font-weight: 700; color: #fff;
    text-transform: uppercase; letter-spacing: .65px;
    white-space: nowrap; border: none; text-align: left;
}

/* Body rows */
.sfr-tbl tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .14s; }
.sfr-tbl tbody tr:last-child { border-bottom: none; }
.sfr-tbl tbody tr:hover { background: #f0fdfa; } /* was: var(--gold-dim) — undefined */
.sfr-tbl td { padding: 13px 16px; vertical-align: middle; color: #374151; font-size: 13px; }

/* Student name / father stacked in one cell */
.sfr-sname  { font-weight: 700; color: #1a2332; font-size: 13.5px; line-height: 1.3; }
.sfr-sname  span { display: block; font-size: 11.5px; color: #6b7280; font-weight: 400; margin-top: 2px; }

/* Table-row avatar — like "BA"/"PR" circles in due fees screenshot */
.sfr-ico-cell {
    width: 36px; height: 36px; border-radius: 10px;
    background: #0d9488; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800;
    flex-shrink: 0; margin-right: 10px; vertical-align: middle;
}

/* Receipt number pill */
.sfr-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px 3px 8px; border-radius: 20px;
    background: #ccfbf1; color: #0f766e;       /* was: var(--gold-dim)/var(--gold) — undefined */
    font-size: 11.5px; font-weight: 800;
    border: 1px solid rgba(13,148,136,.18); white-space: nowrap;
}
.sfr-pill i { font-size: 9px; }

/* Payment mode badge */
.sfr-mode {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    background: #f3f4f6; color: #374151;
    font-size: 11.5px; font-weight: 700; border: 1px solid #e5e7eb;
}

/* Amount colour classes */
.c-paid { font-weight: 700; color: #16a34a; }  /* was: var(--green) — undefined */
.c-fine { font-weight: 600; color: #dc2626; }  /* was: var(--rose) — undefined */
.c-disc { font-weight: 600; color: #d97706; }  /* was: var(--amber) — undefined */
.c-mute { color: #9ca3af; }

/* ── TABLE FOOTER ──
   Matches "Class Total" row from due fees screenshot exactly:
   - Same dark navy (#1a2332) background as the header
   - 3px teal top border as a visual separator
   - White text with coloured amount cells
   Previously was just var(--bg3) with a thin border — looked plain
*/
.sfr-tbl tfoot { display: none; }
.sfr-tbl tfoot tr {
    background: #1a2332;        /* was: var(--bg3) — resolved to nothing */
    border-top: 3px solid #0d9488; /* teal separator — same as teal accents throughout */
}
.sfr-tbl tfoot td {
    padding: 14px 16px; border: none;
    font-weight: 700; font-size: 13px; color: #fff;
}
.sfr-tfoot-lbl {
    font-size: 11px !important; font-weight: 600 !important;
    color: rgba(255,255,255,.55) !important;
    text-transform: uppercase; letter-spacing: .65px;
    text-align: right; padding-right: 20px !important;
}
.sfr-tfoot-paid { color: #86efac !important; font-size: 14px !important; }
.sfr-tfoot-fine { color: #fca5a5 !important; }
.sfr-tfoot-disc { color: #fcd34d !important; }

/* ── Empty / loading state ── */
.sfr-state {
    text-align: center !important;
    padding: 60px 20px !important;
    color: #9ca3af;
}
.sfr-state-ico {
    font-size: 46px; display: block;
    margin: 0 auto 16px; color: #0d9488; opacity: .25;
}
.sfr-state-ttl {
    font-size: 15px; font-weight: 700; color: #374151; margin: 0 0 6px;
}
.sfr-state-sub { font-size: 13px; color: #9ca3af; margin: 0; }

/* ── Toast ── */
.sfr-toasts {
    position: fixed; bottom: 24px; right: 24px;
    z-index: 9999; display: flex; flex-direction: column; gap: 8px;
}
.sfr-toast {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    background: #ffffff;       /* was: var(--bg2) — undefined */
    box-shadow: 0 4px 20px rgba(0,0,0,.13);
    animation: sfr-in .25s ease; min-width: 240px;
    border: 1px solid #f1f5f9; /* was: var(--border) — undefined */
}
.t-success { border-left: 4px solid #16a34a; color: #16a34a; }
.t-error   { border-left: 4px solid #dc2626; color: #dc2626; }
.t-warning { border-left: 4px solid #d97706; color: #d97706; }
.sfr-toast-hide { animation: sfr-out .3s ease forwards; }
@keyframes sfr-in  { from { transform: translateX(60px); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes sfr-out { to   { transform: translateX(60px); opacity: 0; } }
</style>


<!-- <script>
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
</script> -->

<!-- <style>
/* ── Student Fees — ERP Gold Theme (day/night aware) ── */
.sf-wrap {
    padding: 20px 24px;
    background: var(--bg);
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
    font-size: 20px;
    font-weight: 800;
    color: var(--t1);
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-d);
}

.sf-page-title i {
    color: var(--gold);
}

.sf-breadcrumb {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--t3);
    font-family: var(--font-b);
}

.sf-breadcrumb li:not(:last-child)::after {
    content: '/';
    margin-left: 6px;
    opacity: .5;
}

.sf-breadcrumb a {
    color: var(--gold);
}

/* ── Cards ── */
.sf-card {
    background: var(--bg2);
    border-radius: var(--r, 12px);
    border: 1px solid var(--border);
    box-shadow: var(--sh);
    margin-bottom: 20px;
    overflow: hidden;
}

.sf-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--bg3);
}

.sf-card-head h3 {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: var(--t2);
    font-family: var(--font-b);
    text-transform: uppercase;
    letter-spacing: .6px;
}

.sf-card-head i {
    color: var(--gold);
}

.sf-card-body {
    padding: 18px;
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
    font-size: 11px;
    font-weight: 700;
    color: var(--t2);
    font-family: var(--font-b);
    text-transform: uppercase;
    letter-spacing: .5px;
}

.sf-input {
    padding: 9px 12px;
    border: 1.5px solid var(--brd2);
    border-radius: var(--r-sm, 8px);
    font-size: 13px;
    outline: none;
    background: var(--bg3);
    color: var(--t1);
    font-family: var(--font-b);
    transition: border-color var(--ease), box-shadow var(--ease);
}

.sf-input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(15, 118, 110, .15);
}

.sf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 20px;
    border-radius: var(--r-sm, 8px);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all var(--ease);
    white-space: nowrap;
    font-family: var(--font-b);
}

.sf-btn-primary {
    background: var(--gold);
    color: #ffffff;
}

.sf-btn-primary:hover {
    background: var(--gold2, #0d6b63);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(15, 118, 110, .35);
}

.sf-btn-ghost {
    background: var(--bg3);
    color: var(--t2);
    border: 1.5px solid var(--brd2);
}

.sf-btn-ghost:hover {
    border-color: var(--gold);
    color: var(--gold);
    background: var(--gold-dim);
}

/* ── Student info strip ── */
.sf-student-strip {
    display: none;
    background: var(--bg2);
    border: 1px solid var(--border);
    border-left: 3px solid var(--gold);
    border-radius: var(--r, 12px);
    padding: 14px 20px;
    margin-bottom: 20px;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    box-shadow: var(--sh);
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
    color: var(--t3);
    text-transform: uppercase;
    letter-spacing: .6px;
    font-family: var(--font-m);
}

.sf-sinfo-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--t1);
    font-family: var(--font-b);
}

.sf-sinfo-divider {
    width: 1px;
    height: 36px;
    background: var(--border);
}

/* ── Table ── */
.sf-table-wrap {
    overflow-x: auto;
}

.sf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    font-family: var(--font-b);
}

.sf-table thead tr {
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold2, #0d6b63) 100%);
    color: #ffffff;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: .5px;
}

.sf-table thead th {
    padding: 11px 14px;
    font-weight: 700;
    white-space: nowrap;
}

.sf-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background var(--ease);
}

.sf-table tbody tr:hover {
    background: var(--gold-dim);
}

.sf-table td {
    padding: 10px 14px;
    vertical-align: middle;
    color: var(--t2);
}

.sf-table tfoot td {
    padding: 10px 14px;
    background: var(--bg3);
    font-weight: 700;
    color: var(--t1);
    border-top: 2px solid var(--border);
}

.sf-receipt-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--gold-dim);
    color: var(--gold);
    font-size: 11px;
    font-weight: 700;
    font-family: var(--font-m);
    border: 1px solid var(--gold-ring, rgba(15, 118, 110, .22));
}

.sf-amt-positive {
    color: var(--green, #3DD68C);
    font-weight: 700;
}

.sf-amt-discount {
    color: var(--amber, #C9A84C);
}

.sf-amt-fine {
    color: var(--rose, #E05C6F);
}

/* ── Empty / loading ── */
.sf-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--t3);
    font-family: var(--font-b);
}

.sf-empty i {
    font-size: 36px;
    margin-bottom: 12px;
    opacity: .35;
    display: block;
    color: var(--gold);
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
    border-radius: var(--r-sm, 8px);
    font-size: 13px;
    font-weight: 600;
    box-shadow: var(--sh);
    animation: sfToastIn .25s ease;
    min-width: 240px;
    background: var(--bg2);
    border: 1px solid var(--border);
    color: var(--t1);
    font-family: var(--font-b);
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
    border-left: 4px solid var(--green, #3DD68C);
}

.sf-toast-success i {
    color: var(--green, #3DD68C);
}

.sf-toast-error {
    border-left: 4px solid var(--rose, #E05C6F);
}

.sf-toast-error i {
    color: var(--rose, #E05C6F);
}

.sf-toast-warning {
    border-left: 4px solid var(--gold);
}

.sf-toast-warning i {
    color: var(--gold);
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
</style> -->