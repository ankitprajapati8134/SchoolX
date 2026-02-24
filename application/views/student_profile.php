<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/* ══════════════════════════════════════════════════════════════
   SAFE HELPERS — works for BOTH student formats:
   NEW:    Doc[label] = ['url' => '...', 'thumbnail' => '...']
   LEGACY: Doc[label] = 'https://...'  (flat string)
   Very old: Doc['PhotoUrl'] = 'https://...'
══════════════════════════════════════════════════════════════ */
function sp_doc_url($entry)
{
    if (is_array($entry))  return $entry['url'] ?? '';
    if (is_string($entry)) return $entry;
    return '';
}

/* Resolve profile photo — check 3 possible locations */
$profilePhoto = '';
if (!empty($student['Profile Pic']) && is_string($student['Profile Pic'])) {
    $profilePhoto = $student['Profile Pic'];
} elseif (!empty($student['Doc']['Photo'])) {
    $profilePhoto = sp_doc_url($student['Doc']['Photo']);
} elseif (!empty($student['Doc']['PhotoUrl']) && is_string($student['Doc']['PhotoUrl'])) {
    $profilePhoto = $student['Doc']['PhotoUrl'];
}
$fallbackPhoto = base_url('tools/image/default-school.jpeg');

/* Build safe doc list — skip internal photo keys */
$skipKeys   = ['Photo', 'PhotoUrl'];
$docDisplay = [];
if (!empty($student['Doc']) && is_array($student['Doc'])) {
    foreach ($student['Doc'] as $label => $entry) {
        if (in_array($label, $skipKeys, true)) continue;
        $url = sp_doc_url($entry); // SAFE — never passes array to htmlspecialchars
        if ($url !== '') $docDisplay[$label] = $url;
    }
}
?>
<div class="content-wrapper">
    <div class="sp-wrap">

        <div class="sp-heading"><i class="fa fa-id-card-o"></i> Student Profile</div>

        <!-- HERO -->
        <div class="sp-hero">
            <img class="sp-avatar" src="<?= htmlspecialchars($profilePhoto ?: $fallbackPhoto) ?>" alt="Photo"
                onerror="this.src='<?= $fallbackPhoto ?>'">

            <div class="sp-hero-info">
                <h1 class="sp-hero-name"><?= htmlspecialchars($student['Name'] ?? 'Unknown') ?></h1>
                <p class="sp-hero-sub">
                    Class <?= htmlspecialchars($class ?? 'N/A') ?> &bull;
                    Section <?= htmlspecialchars($section ?? 'N/A') ?>
                </p>
                <div class="sp-badges">
                    <span class="sp-badge sp-badge-gold"><?= htmlspecialchars($student['User Id'] ?? '') ?></span>
                    <span class="sp-badge"><i
                            class="fa fa-calendar"></i>&nbsp;<?= htmlspecialchars($student['Admission Date'] ?? 'N/A') ?></span>
                    <span class="sp-badge"><i
                            class="fa fa-tint"></i>&nbsp;<?= htmlspecialchars($student['Blood Group'] ?? 'N/A') ?></span>
                    <span class="sp-badge"><i
                            class="fa fa-venus-mars"></i>&nbsp;<?= htmlspecialchars($student['Gender'] ?? 'N/A') ?></span>
                </div>
            </div>

            <div class="sp-hero-btns">
                <button class="sp-btn sp-btn-blue" id="viewFeesBtn"
                    data-user-id="<?= htmlspecialchars($student['User Id'] ?? '') ?>">
                    <i class="fa fa-rupee"></i> Submitted Fees
                </button>
                <a class="sp-btn sp-btn-ghost"
                    href="<?= base_url('student/edit_student/' . urlencode($student['User Id'] ?? '')) ?>">
                    <i class="fa fa-pencil"></i> Edit Student
                </a>
            </div>
        </div>

        <!-- TABS -->
        <div class="sp-tabs">
            <button class="sp-tab is-active" data-tab="personal"><i class="fa fa-user"></i> Personal</button>
            <button class="sp-tab" data-tab="academic"><i class="fa fa-book"></i> Academic</button>
            <button class="sp-tab" data-tab="guardian"><i class="fa fa-users"></i> Guardian</button>
            <button class="sp-tab" data-tab="address"><i class="fa fa-map-marker"></i> Address</button>
            <button class="sp-tab" data-tab="previous"><i class="fa fa-university"></i> Prev. School</button>
            <button class="sp-tab" data-tab="fees"><i class="fa fa-money"></i> Fees</button>
            <button class="sp-tab" data-tab="documents"><i class="fa fa-file-text-o"></i> Documents</button>
        </div>

        <!-- PERSONAL -->
        <div class="sp-panel is-active" id="tab-personal">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-user"></i>
                    <h3>Personal Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid">
                        <?php foreach (
                            [
                                'Full Name'     => $student['Name']        ?? 'N/A',
                                'Date of Birth' => $student['DOB']         ?? 'N/A',
                                'Gender'        => $student['Gender']       ?? 'N/A',
                                'Blood Group'   => $student['Blood Group']  ?? 'N/A',
                                'Category'      => $student['Category']     ?? 'N/A',
                                'Religion'      => $student['Religion']     ?? 'N/A',
                                'Nationality'   => $student['Nationality']  ?? 'N/A',
                                'Email'         => $student['Email']        ?? 'N/A',
                                'Phone Number'  => $student['Phone Number'] ?? 'N/A',
                            ] as $lbl => $val
                        ): ?>
                        <div>
                            <div class="sp-field-label"><?= $lbl ?></div>
                            <div class="sp-field-value"><?= htmlspecialchars((string)$val) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACADEMIC -->
        <div class="sp-panel" id="tab-academic">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-book"></i>
                    <h3>Academic Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid" style="margin-bottom:18px;">
                        <div>
                            <div class="sp-field-label">Class</div>
                            <div class="sp-field-value"><?= htmlspecialchars($class ?? 'N/A') ?></div>
                        </div>
                        <div>
                            <div class="sp-field-label">Section</div>
                            <div class="sp-field-value"><?= htmlspecialchars($section ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="sp-field-label" style="margin-bottom:8px;">Subjects</div>
                    <div class="sp-chips">
                        <?php if (!empty($subjects)):
                            foreach ($subjects as $sub): ?>
                        <span class="sp-chip"><?= htmlspecialchars($sub) ?></span>
                        <?php endforeach;
                        else: ?>
                        <span style="color:var(--sp-muted);font-size:13px;">No subjects found.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- GUARDIAN -->
        <div class="sp-panel" id="tab-guardian">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-users"></i>
                    <h3>Guardian Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid">
                        <?php foreach (
                            [
                                "Father's Name"       => $student['Father Name']      ?? 'N/A',
                                "Father's Occupation" => $student['Father Occupation'] ?? 'N/A',
                                "Mother's Name"       => $student['Mother Name']       ?? 'N/A',
                                "Mother's Occupation" => $student['Mother Occupation'] ?? 'N/A',
                                'Guardian Relation'   => $student['Guard Relation']    ?? 'N/A',
                                'Guardian Contact'    => $student['Guard Contact']     ?? 'N/A',
                            ] as $lbl => $val
                        ): ?>
                        <div>
                            <div class="sp-field-label"><?= $lbl ?></div>
                            <div class="sp-field-value"><?= htmlspecialchars((string)$val) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADDRESS -->
        <div class="sp-panel" id="tab-address">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-map-marker"></i>
                    <h3>Address Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid">
                        <?php foreach (
                            [
                                'Street'      => $student['Address']['Street']     ?? 'N/A',
                                'City'        => $student['Address']['City']       ?? 'N/A',
                                'State'       => $student['Address']['State']      ?? 'N/A',
                                'Postal Code' => $student['Address']['PostalCode'] ?? 'N/A',
                            ] as $lbl => $val
                        ): ?>
                        <div>
                            <div class="sp-field-label"><?= $lbl ?></div>
                            <div class="sp-field-value"><?= htmlspecialchars((string)$val) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PREVIOUS SCHOOL -->
        <div class="sp-panel" id="tab-previous">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-university"></i>
                    <h3>Previous School Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid">
                        <?php foreach (
                            [
                                'Previous School' => $student['Pre School'] ?? 'N/A',
                                'Class Completed' => $student['Pre Class']  ?? 'N/A',
                                'Marks Obtained'  => $student['Pre Marks']  ?? 'N/A',
                            ] as $lbl => $val
                        ): ?>
                        <div>
                            <div class="sp-field-label"><?= $lbl ?></div>
                            <div class="sp-field-value"><?= htmlspecialchars((string)$val) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FEES -->
        <div class="sp-panel" id="tab-fees">
            <?php if (!empty($fees)):
                $yearlyTotal = 0;
                $mGrand = 0;

                // Collect monthly fee titles
                $mTitles = [];
                foreach ($fees as $mon => $fd) {
                    if ($mon === 'Yearly Fees' || !is_array($fd)) continue;
                    foreach ($fd as $ft => $a) $mTitles[$ft] = true;
                }
                $mTitles = array_keys($mTitles);
            ?>

            <!-- Yearly fees card -->
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-calendar-o"></i>
                    <h3>Yearly Fees</h3>
                </div>
                <div class="sp-card-body">
                    <?php if (!empty($fees['Yearly Fees'])): ?>
                    <div class="sp-tbl-wrap">
                        <table class="sp-tbl">
                            <thead>
                                <tr>
                                    <th>Fee Title</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees['Yearly Fees'] as $title => $amt): $yearlyTotal += (float)$amt; ?>
                                <tr>
                                    <td><?= htmlspecialchars($title) ?></td>
                                    <td>₹<?= number_format((float)$amt, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong>₹<?= number_format($yearlyTotal, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--sp-muted);text-align:center;padding:20px;">No yearly fees configured.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Monthly summary card -->
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-table"></i>
                    <h3>Monthly Fees Summary</h3>
                </div>
                <div class="sp-card-body">
                    <?php if (!empty($mTitles)): ?>
                    <div class="sp-tbl-wrap">
                        <table class="sp-tbl">
                            <thead>
                                <tr>
                                    <th>Fee Title</th>
                                    <th>Annual Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mTitles as $ft):
                                            $rt = 0;
                                            foreach ($fees as $mon => $fd) {
                                                if (!is_array($fd)) continue;
                                                $rt += (float)($fd[$ft] ?? 0);
                                            }
                                            $mGrand += $rt;
                                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($ft) ?></td>
                                    <td>₹<?= number_format($rt, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Grand Total</strong></td>
                                    <td><strong>₹<?= number_format($mGrand, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div style="text-align:right;margin-top:12px;">
                        <button class="sp-btn sp-btn-blue" id="openFeeModal">
                            <i class="fa fa-expand"></i> Month-wise Breakdown
                        </button>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--sp-muted);text-align:center;padding:20px;">No monthly fees configured.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Discount card -->
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-tag"></i>
                    <h3>Discount</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-grid" style="margin-bottom:18px;">
                        <div>
                            <div class="sp-field-label">Current Discount</div>
                            <div class="sp-field-value">
                                ₹<?= isset($currentdiscount) && $currentdiscount !== '' ? number_format((float)$currentdiscount, 2) : '0.00' ?>
                            </div>
                        </div>
                        <div>
                            <div class="sp-field-label">Total Discount Given</div>
                            <div class="sp-field-value">
                                ₹<?= isset($totaldiscount) && $totaldiscount !== '' ? number_format((float)$totaldiscount, 2) : '0.00' ?>
                            </div>
                        </div>
                    </div>
                    <div class="sp-field-label" style="margin-bottom:7px;">Add On-Demand Discount</div>
                    <form id="onDemandDiscountForm">
                        <div class="sp-disc-row">
                            <input type="number" id="onDemandDiscount" name="onDemandDiscount"
                                placeholder="Enter amount in ₹" min="0" required>
                            <button type="submit" class="sp-btn sp-btn-green" id="submitDiscountButton">
                                <i class="fa fa-check"></i> Apply Discount
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Overall total -->
            <?php $overallTot = $yearlyTotal + $mGrand - (float)($totaldiscount ?? 0); ?>
            <div class="sp-total">
                <div class="lbl">Yearly + Monthly &minus; Discount</div>
                <div class="amt">₹<?= number_format($overallTot, 2) ?></div>
            </div>

            <?php else: ?>
            <div class="sp-card">
                <div class="sp-card-body" style="text-align:center;padding:40px;color:var(--sp-muted);">
                    <i class="fa fa-info-circle" style="font-size:30px;display:block;margin-bottom:10px;"></i>
                    No fee data found for this student's class.
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- DOCUMENTS — THE CRASH FIX IS HERE -->
        <!-- sp_doc_url() always returns a string, never an array -->
        <!-- so htmlspecialchars() never receives an array = no fatal error -->
        <div class="sp-panel" id="tab-documents">
            <div class="sp-card">
                <div class="sp-card-head"><i class="fa fa-file-text-o"></i>
                    <h3>Documents</h3>
                </div>
                <div class="sp-card-body">
                    <?php if (!empty($docDisplay)): ?>
                    <div class="sp-doc-grid">
                        <?php foreach ($docDisplay as $label => $url):
                                $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                                $icon = ($ext === 'pdf') ? 'fa-file-pdf-o' : 'fa-file-image-o';
                            ?>
                        <div class="sp-doc-card">
                            <div class="sp-doc-icon"><i class="fa <?= $icon ?>"></i></div>
                            <div class="sp-doc-name"><?= htmlspecialchars($label) ?></div>
                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="sp-doc-link">
                                <i class="fa fa-eye"></i> View
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align:center;padding:36px;color:var(--sp-muted);">
                        <i class="fa fa-folder-open-o" style="font-size:30px;display:block;margin-bottom:10px;"></i>
                        No documents uploaded.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /.sp-wrap -->
</div><!-- /.content-wrapper -->


<!-- FEE MODAL -->
<div class="sp-overlay" id="feeOverlay">
    <div class="sp-modal">
        <div class="sp-modal-head">
            <h4><i class="fa fa-table" style="color:var(--sp-blue);margin-right:7px;"></i>Month-wise Fee Breakdown</h4>
            <button class="sp-modal-close" id="closeFeeModal">&times;</button>
        </div>
        <div class="sp-modal-body">
            <?php if (!empty($fees)):
                $monthOrder   = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
                $sortedMonths = array_values(array_intersect($monthOrder, array_keys($fees)));
                $mColTitles   = [];
                foreach ($fees as $mon => $fd) {
                    if ($mon === 'Yearly Fees' || !is_array($fd)) continue;
                    foreach ($fd as $ft => $a) $mColTitles[$ft] = true;
                }
                $mColTitles = array_keys($mColTitles);
                $colTotals  = array_fill_keys($sortedMonths, 0);
                $modalGrand = 0;
            ?>
            <div class="sp-tbl-wrap">
                <table class="sp-tbl">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <?php foreach ($sortedMonths as $mon): ?><th><?= $mon ?></th><?php endforeach; ?>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mColTitles as $ft): $rowT = 0; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ft) ?></strong></td>
                            <?php foreach ($sortedMonths as $mon):
                                        $amt = (float)($fees[$mon][$ft] ?? 0);
                                        $colTotals[$mon] += $amt;
                                        $rowT += $amt;
                                    ?>
                            <td>₹<?= number_format($amt, 2) ?></td>
                            <?php endforeach; ?>
                            <td><strong>₹<?= number_format($rowT, 2) ?></strong></td>
                        </tr>
                        <?php $modalGrand += $rowT;
                            endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <?php foreach ($sortedMonths as $mon): ?>
                            <td><strong>₹<?= number_format($colTotals[$mon], 2) ?></strong></td>
                            <?php endforeach; ?>
                            <td><strong>₹<?= number_format($modalGrand, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- ── Exempted Fees (restored from original code) ── -->
            <?php
                /* $exempted_fees is passed from student_profile() controller.
               It is an associative array like ['Bus Fees' => '', 'Tuition Fee' => '']
               array_keys() extracts the fee names for display. */
                $exemptedList = [];
                if (!empty($exempted_fees) && is_array($exempted_fees)) {
                    $exemptedList = array_keys($exempted_fees);
                }
                ?>
            <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--sp-border);">
                <h5
                    style="font-family:'Lora',serif;font-size:16px;font-weight:700;color:var(--sp-navy);margin-bottom:12px;text-align:center;">
                    <i class="fa fa-ban" style="color:#dc2626;margin-right:6px;"></i>
                    Exempted Fees For This Student
                </h5>
                <?php if (!empty($exemptedList)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;">
                    <?php foreach ($exemptedList as $fee): ?>
                    <span style="
                                background:#fee2e2;
                                color:#991b1b;
                                border:1px solid #fca5a5;
                                border-radius:20px;
                                padding:5px 16px;
                                font-size:13px;
                                font-weight:600;
                                font-family:'Plus Jakarta Sans',sans-serif;
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                            ">
                        <i class="fa fa-check-circle"></i>
                        <?= htmlspecialchars($fee) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align:center;color:var(--sp-muted);font-size:13px;">
                    <i class="fa fa-info-circle"></i>&nbsp;No fees exempted for this student.
                </p>
                <?php endif; ?>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';

    /* Tab switching */
    document.querySelectorAll('.sp-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.sp-tab').forEach(function (t)   { t.classList.remove('is-active'); });
            document.querySelectorAll('.sp-panel').forEach(function (p) { p.classList.remove('is-active'); });
            this.classList.add('is-active');
            var panel = document.getElementById('tab-' + this.dataset.tab);
            if (panel) panel.classList.add('is-active');
        });
    });

    /* Fee modal */
    var overlay  = document.getElementById('feeOverlay');
    var openBtn  = document.getElementById('openFeeModal');
    var closeBtn = document.getElementById('closeFeeModal');
    if (openBtn)  openBtn.addEventListener('click',  function () { overlay.classList.add('open'); });
    if (closeBtn) closeBtn.addEventListener('click', function () { overlay.classList.remove('open'); });
    if (overlay)  overlay.addEventListener('click',  function (e) { if (e.target === overlay) overlay.classList.remove('open'); });

    /* View submitted fees */
    var feesBtn = document.getElementById('viewFeesBtn');
    if (feesBtn) {
        feesBtn.addEventListener('click', function () {
            var uid = this.dataset.userId;
            if (uid) window.location.href = '<?= base_url("fees/student_fees?userId=") ?>' + encodeURIComponent(uid);
        });
    }

    /* On-demand discount */
    var discForm = document.getElementById('onDemandDiscountForm');
    var discBtn  = document.getElementById('submitDiscountButton');
    if (discForm && discBtn) {
        discForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var val = document.getElementById('onDemandDiscount').value.trim();
            if (!val) { alert('Please enter a discount amount.'); return; }

            discBtn.disabled = true;
            discBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Applying...';

            var payload = new URLSearchParams();
            payload.append('userId',  '<?= htmlspecialchars($student['User Id'] ?? '', ENT_QUOTES) ?>');
            payload.append('class',   '<?= htmlspecialchars($class ?? '', ENT_QUOTES) ?>');
            payload.append('section', '<?= htmlspecialchars($section ?? '', ENT_QUOTES) ?>');
            payload.append('discount', val);

            fetch('<?= base_url("fees/submit_discount") ?>', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    payload
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) { alert('Discount applied!'); window.location.reload(); }
                else { alert('Error: ' + data.message); }
            })
            .catch(function () { alert('Failed to apply discount.'); })
            .finally(function () {
                discBtn.disabled = false;
                discBtn.innerHTML = '<i class="fa fa-check"></i> Apply Discount';
            });
        });
    }

})();
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Lora:wght@500;600;700&display=swap');

:root {
    --sp-navy: #0d1f3c;
    --sp-blue: #1e56d9;
    --sp-sky: #ebf1fd;
    --sp-gold: #f5a623;
    --sp-green: #16a34a;
    --sp-red: #dc2626;
    --sp-text: #1a2535;
    --sp-muted: #607080;
    --sp-border: #dde5f0;
    --sp-white: #ffffff;
    --sp-bg: #f2f5fb;
    --sp-shadow: 0 2px 18px rgba(13, 31, 60, .08);
    --sp-radius: 14px;
}

.sp-wrap {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--sp-bg);
    color: var(--sp-text);
    padding: 24px 20px 52px;
    min-height: 100vh;
}

.sp-heading {
    font-family: 'Lora', serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--sp-navy);
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 22px;
}

.sp-heading i {
    color: var(--sp-blue);
}

/* Hero */
.sp-hero {
    background: linear-gradient(130deg, var(--sp-navy) 0%, #1a3a70 100%);
    border-radius: var(--sp-radius);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    gap: 26px;
    margin-bottom: 20px;
    box-shadow: var(--sp-shadow);
    position: relative;
    overflow: hidden;
    flex-wrap: wrap;
}

.sp-hero::after {
    content: '';
    position: absolute;
    right: -50px;
    top: -50px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .05);
    pointer-events: none;
}

.sp-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--sp-gold);
    box-shadow: 0 4px 18px rgba(0, 0, 0, .3);
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}

.sp-hero-info {
    position: relative;
    z-index: 1;
    flex: 1;
}

.sp-hero-name {
    font-family: 'Lora', serif;
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 4px;
}

.sp-hero-sub {
    color: rgba(255, 255, 255, .65);
    font-size: 14px;
    margin: 0 0 12px;
}

.sp-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.sp-badge {
    padding: 3px 13px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    background: rgba(255, 255, 255, .12);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .18);
}

.sp-badge-gold {
    background: var(--sp-gold);
    color: var(--sp-navy);
    border-color: var(--sp-gold);
    font-weight: 700;
}

.sp-hero-btns {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}

/* Buttons */
.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity .15s, transform .12s;
    white-space: nowrap;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.sp-btn:hover {
    opacity: .85;
    transform: translateY(-1px);
    text-decoration: none;
}

.sp-btn-blue {
    background: var(--sp-blue);
    color: #fff;
}

.sp-btn-green {
    background: var(--sp-green);
    color: #fff;
}

.sp-btn-ghost {
    background: rgba(255, 255, 255, .12);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .3);
}

/* Tabs */
.sp-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    background: var(--sp-white);
    border-radius: var(--sp-radius);
    padding: 7px;
    box-shadow: var(--sp-shadow);
    margin-bottom: 18px;
}

.sp-tab {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--sp-muted);
    cursor: pointer;
    border: none;
    background: transparent;
    transition: all .16s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.sp-tab:hover {
    background: var(--sp-sky);
    color: var(--sp-blue);
}

.sp-tab.is-active {
    background: var(--sp-blue);
    color: #fff;
    box-shadow: 0 2px 8px rgba(30, 86, 217, .28);
}

/* Panels */
.sp-panel {
    display: none;
}

.sp-panel.is-active {
    display: block;
}

/* Card */
.sp-card {
    background: var(--sp-white);
    border-radius: var(--sp-radius);
    box-shadow: var(--sp-shadow);
    overflow: hidden;
    margin-bottom: 18px;
}

.sp-card-head {
    padding: 13px 22px;
    border-bottom: 1px solid var(--sp-border);
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--sp-sky);
}

.sp-card-head h3 {
    margin: 0;
    font-family: 'Lora', serif;
    font-size: 17px;
    font-weight: 600;
    color: var(--sp-navy);
}

.sp-card-head i {
    color: var(--sp-blue);
}

.sp-card-body {
    padding: 20px 22px;
}

/* Info grid */
.sp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}

.sp-field-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--sp-muted);
    margin-bottom: 3px;
}

.sp-field-value {
    font-size: 14.5px;
    font-weight: 500;
    color: var(--sp-text);
}

/* Chips */
.sp-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.sp-chip {
    background: var(--sp-sky);
    color: var(--sp-blue);
    border: 1px solid var(--sp-border);
    border-radius: 20px;
    padding: 4px 13px;
    font-size: 12.5px;
    font-weight: 500;
}

/* Doc grid */
.sp-doc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
}

.sp-doc-card {
    border: 1px solid var(--sp-border);
    border-radius: 12px;
    padding: 18px 14px;
    text-align: center;
    background: #fafbff;
    transition: box-shadow .16s, transform .16s;
}

.sp-doc-card:hover {
    box-shadow: 0 4px 14px rgba(30, 86, 217, .12);
    transform: translateY(-2px);
}

.sp-doc-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: var(--sp-sky);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 20px;
    color: var(--sp-blue);
}

.sp-doc-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--sp-text);
    margin-bottom: 12px;
}

.sp-doc-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 7px;
    background: var(--sp-blue);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: opacity .14s;
}

.sp-doc-link:hover {
    opacity: .82;
    color: #fff;
    text-decoration: none;
}

/* Tables */
.sp-tbl-wrap {
    overflow-x: auto;
}

.sp-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
}

.sp-tbl th {
    background: var(--sp-navy);
    color: #fff;
    padding: 10px 13px;
    text-align: left;
    font-weight: 500;
    white-space: nowrap;
}

.sp-tbl td {
    padding: 9px 13px;
    border-bottom: 1px solid var(--sp-border);
}

.sp-tbl tr:hover td {
    background: var(--sp-sky);
}

.sp-tbl tfoot td {
    background: var(--sp-gold);
    color: var(--sp-navy);
    font-weight: 700;
    border: none;
}

/* Total */
.sp-total {
    background: linear-gradient(135deg, var(--sp-navy), #1a3a70);
    border-radius: 12px;
    padding: 18px 22px;
    text-align: center;
    color: #fff;
    margin-top: 18px;
}

.sp-total .lbl {
    font-size: 12px;
    opacity: .65;
    margin-bottom: 5px;
}

.sp-total .amt {
    font-family: 'Lora', serif;
    font-size: 30px;
    font-weight: 700;
    color: var(--sp-gold);
}

/* Discount */
.sp-disc-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 10px;
}

.sp-disc-row input {
    flex: 1;
    min-width: 180px;
    padding: 8px 13px;
    border: 1px solid var(--sp-border);
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    transition: border-color .14s;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.sp-disc-row input:focus {
    border-color: var(--sp-blue);
}

/* Modal */
.sp-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .52);
    z-index: 9100;
    align-items: center;
    justify-content: center;
}

.sp-overlay.open {
    display: flex;
}

.sp-modal {
    background: var(--sp-white);
    border-radius: var(--sp-radius);
    width: 96%;
    max-width: 1080px;
    max-height: 86vh;
    overflow-y: auto;
    box-shadow: 0 8px 40px rgba(0, 0, 0, .22);
}

.sp-modal-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--sp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: var(--sp-white);
    z-index: 1;
}

.sp-modal-head h4 {
    margin: 0;
    font-family: 'Lora', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--sp-navy);
}

.sp-modal-close {
    background: none;
    border: none;
    font-size: 22px;
    line-height: 1;
    color: var(--sp-muted);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background .14s;
}

.sp-modal-close:hover {
    background: #fee2e2;
    color: var(--sp-red);
}

.sp-modal-body {
    padding: 22px;
}

@media (max-width: 720px) {
    .sp-hero {
        flex-direction: column;
        text-align: center;
    }

    .sp-hero-btns {
        align-items: center;
    }

    .sp-badges {
        justify-content: center;
    }

    .sp-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .sp-grid {
        grid-template-columns: 1fr;
    }

    .sp-tab {
        padding: 7px 10px;
        font-size: 12px;
    }
}
</style>