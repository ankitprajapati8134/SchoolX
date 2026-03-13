<?php defined('BASEPATH') or exit('No direct script access allowed');

$userId  = $student['User Id'] ?? '';
$name    = $student['Name'] ?? 'Unknown';
$class   = $student['Class'] ?? '';
$section = $student['Section'] ?? '';

// Profile photo resolution
$photo = '';
if (!empty($student['Profile Pic']) && is_string($student['Profile Pic'])) {
    $photo = $student['Profile Pic'];
} elseif (!empty($student['Doc']['Photo'])) {
    $e = $student['Doc']['Photo'];
    $photo = is_array($e) ? ($e['url'] ?? '') : $e;
}
$fallback = base_url('tools/image/default-school.jpeg');
?>

<style>
html { font-size: 16px !important; }
.sp-wrap2 { max-width:1000px; margin:0 auto; padding:24px 20px; }
.sp-hero2 { display:flex; gap:20px; align-items:flex-start; background:var(--bg2);
    border:1px solid var(--border); border-radius:12px; padding:24px; margin-bottom:20px; }
.sp-avatar2 { width:96px; height:96px; border-radius:50%; object-fit:cover;
    border:3px solid var(--gold); flex-shrink:0; }
.sp-meta { flex:1; }
.sp-name2 { font-size:1.4rem; font-weight:700; color:var(--t1); font-family:var(--font-b); margin:0 0 4px; }
.sp-sub2  { color:var(--t3); font-size:.9rem; margin:0 0 10px; }
.sp-badges2 { display:flex; flex-wrap:wrap; gap:8px; }
.sp-badge2 { padding:4px 12px; border-radius:20px; font-size:.84rem;
    background:var(--bg3); color:var(--t2); border:1px solid var(--border); }
.sp-badge2.gold { background:var(--gold-dim); color:var(--gold); border-color:var(--gold-ring); }

.sp-tabs { display:flex; gap:4px; margin-bottom:16px; border-bottom:1px solid var(--border); }
.sp-tab { padding:8px 16px; border:none; background:none; color:var(--t3);
    cursor:pointer; font-size:.88rem; border-bottom:2px solid transparent; transition:all .2s; }
.sp-tab.active { color:var(--gold); border-bottom-color:var(--gold); font-weight:600; }

.sp-pane { display:none; }
.sp-pane.active { display:block; }

.info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; }
.info-item label { font-size:.84rem; color:var(--t3); font-family:var(--font-m); display:block; margin-bottom:3px; }
.info-item .val  { font-size:.9rem; color:var(--t1); }

.edit-panel { background:var(--bg2); border:1px solid var(--border); border-radius:10px; padding:20px; }
.form-grid3 { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; }
@media(max-width:600px){ .form-grid3{grid-template-columns:1fr;} }
.fg3 { display:flex; flex-direction:column; gap:4px; }
.fg3 label { font-size:.84rem; color:var(--t3); }
.fg3 input  { padding:8px 10px; border:1px solid var(--border); border-radius:6px;
    background:var(--bg3); color:var(--t1); font-size:.86rem; }
.btn-save { padding:9px 22px; background:var(--gold); color:#fff; border:none;
    border-radius:6px; cursor:pointer; font-size:.88rem; margin-top:14px; }
.btn-save:hover { background:var(--gold2); }

.hist-list { list-style:none; padding:0; margin:0; }
.hist-item { display:flex; gap:14px; padding:10px 0; border-bottom:1px solid var(--border); }
.hist-item:last-child { border-bottom:none; }
.hist-dot { width:10px; height:10px; border-radius:50%; background:var(--gold);
    margin-top:5px; flex-shrink:0; }
.hist-action { font-size:.82rem; font-weight:700; color:var(--gold); font-family:var(--font-m); text-transform:uppercase; }
.hist-desc   { font-size:.9rem; color:var(--t1); }
.hist-meta   { font-size:.82rem; color:var(--t3); margin-top:2px; }

.alert { padding:10px 14px; border-radius:6px; font-size:.85rem; display:none; margin-bottom:14px; }
.alert-success { background:#dcfce7; color:#166534; }
.alert-error   { background:#fee2e2; color:#991b1b; }
</style>

<div class="content-wrapper">
<div class="sp-wrap2">

    <div style="margin-bottom:14px;">
        <a href="<?= base_url('sis/students') ?>" style="color:var(--t3);font-size:.85rem;text-decoration:none;">
            <i class="fa fa-arrow-left"></i> Student List
        </a>
    </div>

    <div class="sp-hero2">
        <img class="sp-avatar2" src="<?= htmlspecialchars($photo ?: $fallback) ?>"
            onerror="this.src='<?= $fallback ?>'" alt="Photo">
        <div class="sp-meta">
            <h1 class="sp-name2"><?= htmlspecialchars($name) ?></h1>
            <p class="sp-sub2">Class <?= htmlspecialchars($class) ?> &bull; Section <?= htmlspecialchars($section) ?> &bull; <?= htmlspecialchars($session_year) ?></p>
            <div class="sp-badges2">
                <span class="sp-badge2 gold"><?= htmlspecialchars($userId) ?></span>
                <span class="sp-badge2"><?= htmlspecialchars($student['Status'] ?? 'Active') ?></span>
                <?php if (!empty($student['Admission Date'])): ?>
                <span class="sp-badge2"><i class="fa fa-calendar"></i> <?= htmlspecialchars($student['Admission Date']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;">
            <a href="<?= base_url('student/student_profile/' . urlencode($userId)) ?>" class="btn-save" style="font-size:.82rem;padding:7px 14px;text-decoration:none;">
                <i class="fa fa-user"></i> Full Profile
            </a>
            <a href="<?= base_url('sis/documents/' . urlencode($userId)) ?>" class="btn-save" style="font-size:.82rem;padding:7px 14px;text-decoration:none;background:var(--bg3);color:var(--t2);border:1px solid var(--border);">
                <i class="fa fa-folder-open-o"></i> Documents
            </a>
            <a href="<?= base_url('sis/history/' . urlencode($userId)) ?>" class="btn-save" style="font-size:.82rem;padding:7px 14px;text-decoration:none;background:var(--bg3);color:var(--t2);border:1px solid var(--border);">
                <i class="fa fa-history"></i> History
            </a>
            <?php if (($student['Status'] ?? '') !== 'TC'): ?>
            <?php if (($student['Status'] ?? 'Active') === 'Active'): ?>
            <button onclick="withdrawStudent()" class="btn-save" style="font-size:.82rem;padding:7px 14px;background:#dc2626;border:none;cursor:pointer;">
                <i class="fa fa-sign-out"></i> Withdraw
            </button>
            <?php else: ?>
            <button onclick="reactivateStudent()" class="btn-save" style="font-size:.82rem;padding:7px 14px;background:#16a34a;border:none;cursor:pointer;">
                <i class="fa fa-check-circle"></i> Re-activate
            </button>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="sp-tabs">
        <button class="sp-tab active" onclick="showTab('info',this)">Info</button>
        <button class="sp-tab" onclick="showTab('edit',this)">Edit Profile</button>
        <button class="sp-tab" onclick="showTab('hist',this)">Recent Activity</button>
        <?php if (!empty($student['TC'])): ?>
        <button class="sp-tab" onclick="showTab('tc',this)">Transfer Cert.</button>
        <?php endif; ?>
    </div>

    <!-- Info Tab -->
    <div id="pane-info" class="sp-pane active">
        <div class="info-grid">
            <?php
            $infoFields = [
                'Name'             => 'Name',
                'Father Name'      => 'Father',
                'Father Occupation'=> 'Father Occupation',
                'Mother Name'      => 'Mother',
                'Mother Occupation'=> 'Mother Occupation',
                'Guard Contact'    => 'Guardian Contact',
                'Guard Relation'   => 'Guardian Relation',
                'DOB'              => 'Date of Birth',
                'Gender'           => 'Gender',
                'Blood Group'      => 'Blood Group',
                'Category'         => 'Category',
                'Religion'         => 'Religion',
                'Nationality'      => 'Nationality',
                'Phone Number'     => 'Phone',
                'Email'            => 'Email',
                'Roll No'          => 'Roll No',
                'Admission Date'   => 'Admission Date',
                'Session'          => 'Session',
            ];
            foreach ($infoFields as $key => $label):
                $val = $student[$key] ?? '';
                if (empty($val)) continue;
            ?>
            <div class="info-item">
                <label><?= htmlspecialchars($label) ?></label>
                <div class="val"><?= htmlspecialchars($val) ?></div>
            </div>
            <?php endforeach; ?>
            <?php
            // Address is a nested object {Street, City, State, PostalCode}
            $addr = $student['Address'] ?? '';
            if (is_array($addr)) {
                $addrStr = implode(', ', array_filter([
                    $addr['Street'] ?? '', $addr['City'] ?? '',
                    $addr['State'] ?? '', $addr['PostalCode'] ?? '',
                ]));
            } else {
                $addrStr = (string)$addr;
            }
            if (!empty($addrStr)):
            ?>
            <div class="info-item" style="grid-column:1/-1;">
                <label>Address</label>
                <div class="val"><?= htmlspecialchars($addrStr) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Tab -->
    <div id="pane-edit" class="sp-pane">
        <div class="edit-panel">
            <div id="editAlert" class="alert"></div>
            <form id="editForm">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
                <div class="form-grid3">
                    <?php
                    $editFields = [
                        'Name'              => 'Full Name',
                        'Father Name'       => 'Father Name',
                        'Father Occupation' => 'Father Occupation',
                        'Mother Name'       => 'Mother Name',
                        'Mother Occupation' => 'Mother Occupation',
                        'Guard Contact'     => 'Guardian Contact',
                        'Guard Relation'    => 'Guardian Relation',
                        'DOB'               => 'Date of Birth',
                        'Gender'            => 'Gender',
                        'Blood Group'       => 'Blood Group',
                        'Category'          => 'Category',
                        'Religion'          => 'Religion',
                        'Nationality'       => 'Nationality',
                        'Phone Number'      => 'Phone',
                        'Email'             => 'Email',
                        'Roll No'           => 'Roll No',
                        'Pre School'        => 'Previous School',
                        'Pre Class'         => 'Previous Class',
                        'Pre Marks'         => 'Previous Marks %',
                    ];
                    foreach ($editFields as $key => $label): ?>
                    <div class="fg3">
                        <label><?= htmlspecialchars($label) ?></label>
                        <input type="text" name="<?= htmlspecialchars($key) ?>"
                            value="<?= htmlspecialchars($student[$key] ?? '') ?>">
                    </div>
                    <?php endforeach; ?>
                    <?php
                    // Address is a nested object — expose sub-fields
                    $addr = $student['Address'] ?? [];
                    if (!is_array($addr)) { $addr = ['Street' => (string)$addr]; }
                    $addrSubFields = [
                        'Street'     => 'Street / Locality',
                        'City'       => 'City',
                        'State'      => 'State',
                        'PostalCode' => 'Postal Code',
                    ];
                    foreach ($addrSubFields as $sub => $lbl): ?>
                    <div class="fg3">
                        <label><?= htmlspecialchars($lbl) ?></label>
                        <input type="text" name="Address[<?= htmlspecialchars($sub) ?>]"
                            value="<?= htmlspecialchars($addr[$sub] ?? '') ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn-save"><i class="fa fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <!-- History Tab (recent 10) -->
    <div id="pane-hist" class="sp-pane">
        <?php if (empty($history)): ?>
        <p style="color:var(--t3);">No activity recorded yet.</p>
        <?php else: ?>
        <ul class="hist-list">
            <?php $shown = 0; foreach ($history as $h): if (!is_array($h) || $shown >= 10) continue; $shown++; ?>
            <li class="hist-item">
                <div class="hist-dot"></div>
                <div>
                    <div class="hist-action"><?= htmlspecialchars($h['action'] ?? '') ?></div>
                    <div class="hist-desc"><?= htmlspecialchars($h['description'] ?? '') ?></div>
                    <div class="hist-meta"><?= htmlspecialchars($h['changed_at'] ?? '') ?> &bull; by <?= htmlspecialchars($h['changed_by'] ?? 'System') ?></div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div style="margin-top:10px;">
            <a href="<?= base_url('sis/history/' . urlencode($userId)) ?>" style="color:var(--gold);font-size:.85rem;">
                View full history &rarr;
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- TC Tab -->
    <?php if (!empty($student['TC'])): ?>
    <div id="pane-tc" class="sp-pane">
        <?php foreach ($student['TC'] as $tcKey => $tc): if (!is_array($tc)) continue; ?>
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:8px;padding:16px;margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:1rem;font-weight:700;color:var(--gold);"><?= htmlspecialchars($tc['tc_no'] ?? '') ?></div>
                    <div style="color:var(--t2);font-size:.88rem;margin-top:4px;">Issued: <?= htmlspecialchars($tc['issued_date'] ?? '') ?></div>
                    <div style="color:var(--t3);font-size:.82rem;">Reason: <?= htmlspecialchars($tc['reason'] ?? '') ?></div>
                    <div style="color:var(--t3);font-size:.82rem;">Destination: <?= htmlspecialchars($tc['destination'] ?? '') ?></div>
                </div>
                <div style="display:flex;gap:8px;flex-direction:column;">
                    <span style="padding:3px 10px;border-radius:20px;font-size:.82rem;background:<?= ($tc['status']??'')=='active'?'#dcfce7':'var(--bg3)' ?>;color:<?= ($tc['status']??'')=='active'?'#166534':'var(--t3)' ?>;">
                        <?= htmlspecialchars($tc['status'] ?? '') ?>
                    </span>
                    <?php if (($tc['status'] ?? '') === 'active'): ?>
                    <a href="<?= base_url("sis/print_tc/{$userId}/{$tcKey}") ?>" target="_blank"
                        style="padding:6px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:6px;color:var(--t2);text-decoration:none;font-size:.85rem;">
                        <i class="fa fa-print"></i> Print
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
var csrfName  = document.querySelector('meta[name="csrf-name"]').content;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showTab(name, btn) {
    document.querySelectorAll('.sp-pane').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.sp-tab').forEach(function(b) { b.classList.remove('active'); });
    var pane = document.getElementById('pane-' + name);
    if (pane) pane.classList.add('active');
    btn.classList.add('active');
}

var editForm = document.getElementById('editForm');
if (editForm) {
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Save changes to this student profile?')) return;
        var btn   = this.querySelector('button[type=submit]');
        var alertEl = document.getElementById('editAlert');
        btn.disabled = true; btn.textContent = 'Saving...';
        alertEl.style.display = 'none';

        var fd = new FormData(this);
        fd.append(csrfName, csrfToken);
        fetch('<?= base_url('sis/update_profile') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
            alertEl.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-error');
            alertEl.textContent = data.message;
            alertEl.style.display = 'block';
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
            alertEl.className = 'alert alert-error';
            alertEl.textContent = 'Network error.';
            alertEl.style.display = 'block';
        });
    });
}

function withdrawStudent() {
    var reason = prompt('Reason for withdrawal (leave blank for "Withdrawn"):');
    if (reason === null) return;
    var body = new URLSearchParams({
        user_id: '<?= htmlspecialchars($userId, ENT_QUOTES, 'UTF-8') ?>',
        reason:  reason || 'Withdrawn',
    });
    body.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/withdraw') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { alert(data.message); if (data.status === 'success') location.reload(); });
}

function reactivateStudent() {
    if (!confirm('Re-activate this student?')) return;
    var body = new URLSearchParams({
        user_id: '<?= htmlspecialchars($userId, ENT_QUOTES, 'UTF-8') ?>',
        status:  'Active',
    });
    body.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/change_status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { alert(data.message); if (data.status === 'success') location.reload(); });
}
</script>
