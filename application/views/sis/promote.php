<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
html { font-size: 16px !important; }
.promo-wrap { max-width:960px; margin:0 auto; padding:24px 20px; }
.promo-card { background:var(--bg2); border:1px solid var(--border); border-radius:12px; padding:28px; margin-bottom:20px; }
.promo-card h2 { margin:0 0 6px; font-size:1.2rem; color:var(--t1); font-family:var(--font-b); display:flex;align-items:center;gap:10px; }
.promo-card h2 i { color:var(--gold); }
.promo-card p.sub { color:var(--t3); font-size:.85rem; margin:0 0 22px; }

.promo-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px 24px; }
@media(max-width:600px){ .promo-grid{grid-template-columns:1fr;} }
.promo-grid .full { grid-column:1/-1; }

.fg { display:flex; flex-direction:column; gap:5px; }
.fg label { font-size:.86rem; color:var(--t3); font-family:var(--font-m); }
.fg select, .fg input { padding:9px 12px; border:1px solid var(--border); border-radius:6px;
    background:var(--bg3); color:var(--t1); font-size:.88rem; }

.arrow-div { display:flex; align-items:center; justify-content:center; font-size:1.8rem;
    color:var(--gold); padding:8px; }

.btn-primary { padding:10px 24px; background:var(--gold); color:#fff; border:none;
    border-radius:7px; cursor:pointer; font-size:.9rem; font-family:var(--font-m); }
.btn-primary:hover { background:var(--gold2); }
.btn-secondary { padding:10px 20px; background:var(--bg3); color:var(--t2);
    border:1px solid var(--border); border-radius:7px; cursor:pointer; font-size:.9rem; }

/* Preview panel */
.preview-panel { display:none; }
.preview-panel.show { display:block; }
.preview-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.preview-hdr h3 { margin:0; font-size:1rem; color:var(--t1); font-family:var(--font-b); }

.promo-table { width:100%; border-collapse:collapse; font-size:.86rem; }
.promo-table th { background:var(--bg3); color:var(--t2); padding:8px 12px; text-align:left;
    border-bottom:1px solid var(--border); font-family:var(--font-m); }
.promo-table td { padding:8px 12px; border-bottom:1px solid var(--border); color:var(--t1); }
.promo-table tr:last-child td { border-bottom:none; }

.alert { padding:12px 16px; border-radius:7px; font-size:.88rem; display:none; margin-top:14px; }
.alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
</style>

<div class="content-wrapper">
<div class="promo-wrap">

    <div style="margin-bottom:14px;">
        <a href="<?= base_url('sis') ?>" style="color:var(--t3);font-size:.85rem;text-decoration:none;">
            <i class="fa fa-arrow-left"></i> SIS Dashboard
        </a>
    </div>

    <div class="promo-card">
        <h2><i class="fa fa-level-up"></i> Student Promotion</h2>
        <p class="sub">Select source class and destination class. Preview students before confirming.</p>

        <div class="promo-grid">
            <div>
                <div style="font-size:.86rem;color:var(--gold);font-family:var(--font-m);margin-bottom:10px;text-transform:uppercase;">FROM</div>
                <div class="fg" style="margin-bottom:12px;">
                    <label>Class</label>
                    <select id="fromClass">
                        <option value="">Select Class...</option>
                        <?php foreach ($class_map as $ord => $secs): ?>
                        <option value="<?= htmlspecialchars($ord) ?>">Class <?= htmlspecialchars($ord) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg">
                    <label>Section (or All)</label>
                    <select id="fromSection">
                        <option value="all">All Sections</option>
                    </select>
                </div>
            </div>

            <div class="arrow-div">&rarr;</div>

            <div>
                <div style="font-size:.86rem;color:var(--gold);font-family:var(--font-m);margin-bottom:10px;text-transform:uppercase;">TO</div>
                <div class="fg" style="margin-bottom:12px;">
                    <label>Class</label>
                    <select id="toClass">
                        <option value="">Select Class...</option>
                        <?php foreach ($class_map as $ord => $secs): ?>
                        <option value="<?= htmlspecialchars($ord) ?>">Class <?= htmlspecialchars($ord) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg" style="margin-bottom:12px;">
                    <label>Section</label>
                    <select id="toSection">
                        <option value="">Select Section...</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Session Year (default: current)</label>
                    <input type="text" id="toSession" value="<?= htmlspecialchars($session_year) ?>" placeholder="e.g. 2026-27">
                </div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:12px;">
            <button class="btn-primary" onclick="previewPromotion()"><i class="fa fa-eye"></i> Preview Students</button>
        </div>
    </div>

    <!-- Preview -->
    <div class="promo-card preview-panel" id="previewPanel">
        <div class="preview-hdr">
            <h3 id="previewTitle">Students to Promote</h3>
            <button class="btn-secondary" onclick="document.getElementById('previewPanel').classList.remove('show')">Cancel</button>
        </div>
        <div id="alertBox" class="alert"></div>
        <div style="overflow-x:auto;">
            <table class="promo-table">
                <thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Class</th><th>Section</th></tr></thead>
                <tbody id="previewTbody"></tbody>
            </table>
        </div>
        <div style="margin-top:16px;display:flex;gap:12px;">
            <button class="btn-primary" onclick="executePromotion()"><i class="fa fa-check"></i> Confirm &amp; Promote</button>
        </div>
    </div>

</div>
</div>

<script>
var csrfName  = document.querySelector('meta[name="csrf-name"]').content;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var CLASS_MAP = <?= json_encode($class_map) ?>;
var previewStudents = [];

function populateSections(selectEl, classOrd, includeAll = false) {
    selectEl.innerHTML = '';
    if (includeAll) selectEl.innerHTML = '<option value="all">All Sections</option>';
    else selectEl.innerHTML = '<option value="">Select Section...</option>';
    if (classOrd && CLASS_MAP[classOrd]) {
        CLASS_MAP[classOrd].forEach(s => {
            selectEl.innerHTML += `<option value="${s}">Section ${s}</option>`;
        });
    }
}

document.getElementById('fromClass').addEventListener('change', function () {
    populateSections(document.getElementById('fromSection'), this.value, true);
});
document.getElementById('toClass').addEventListener('change', function () {
    populateSections(document.getElementById('toSection'), this.value, false);
});

function previewPromotion() {
    const fromClass   = document.getElementById('fromClass').value;
    const fromSection = document.getElementById('fromSection').value;
    if (!fromClass) { alert('Please select source class.'); return; }

    var body = new URLSearchParams({ from_class: fromClass, from_section: fromSection });
    body.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/promote_preview') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status !== 'success') { alert(data.message); return; }
        previewStudents = data.students;
        const tbody = document.getElementById('previewTbody');
        if (!previewStudents.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--t3);padding:20px;">No students found.</td></tr>';
        } else {
            tbody.innerHTML = previewStudents.map((s, i) => `
                <tr><td>${i+1}</td><td><code>${esc(s.user_id)}</code></td><td>${esc(s.name)}</td>
                <td>Class ${esc(s.class)}</td><td>${esc(s.section)}</td></tr>`).join('');
        }
        document.getElementById('previewTitle').textContent =
            `${previewStudents.length} student(s) — Class ${fromClass} / Section ${fromSection === 'all' ? 'All' : fromSection}`;
        document.getElementById('previewPanel').classList.add('show');
        document.getElementById('alertBox').style.display = 'none';
    })
    .catch(() => alert('Failed to load preview.'));
}

function executePromotion() {
    const toClass   = document.getElementById('toClass').value;
    const toSection = document.getElementById('toSection').value;
    if (!toClass || !toSection) { alert('Please select destination class and section.'); return; }
    if (!confirm(`Promote ${previewStudents.length} student(s) to Class ${toClass} / Section ${toSection}?`)) return;

    var body = new URLSearchParams({
        from_class:   document.getElementById('fromClass').value,
        from_section: document.getElementById('fromSection').value,
        to_class:     toClass,
        to_section:   toSection,
        to_session:   document.getElementById('toSession').value.trim(),
    });
    body.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/execute_promotion') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        const alert = document.getElementById('alertBox');
        alert.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-error');
        alert.textContent = data.message;
        alert.style.display = 'block';
    })
    .catch(() => {
        const alert = document.getElementById('alertBox');
        alert.className = 'alert alert-error';
        alert.textContent = 'Promotion failed. Please try again.';
        alert.style.display = 'block';
    });
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
