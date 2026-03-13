<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
html { font-size: 16px !important; }
.sis-students-wrap { max-width:1200px; margin:0 auto; padding:24px 20px; }
.page-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.page-hdr h1 { margin:0; font-size:1.35rem; color:var(--t1); font-family:var(--font-b); }

.filter-bar { background:var(--bg2); border:1px solid var(--border); border-radius:10px;
    padding:16px; margin-bottom:20px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
.filter-bar .fg { display:flex; flex-direction:column; gap:4px; }
.filter-bar label { font-size:.84rem; color:var(--t3); font-family:var(--font-m); }
.filter-bar select, .filter-bar input { padding:7px 10px; border:1px solid var(--border);
    border-radius:6px; background:var(--bg3); color:var(--t1); font-size:.88rem; min-width:140px; }
.btn-search { padding:8px 18px; background:var(--gold); color:#fff; border:none;
    border-radius:6px; cursor:pointer; font-size:.88rem; font-family:var(--font-m); }
.btn-search:hover { background:var(--gold2); }

.students-table-wrap { background:var(--bg2); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.students-table { width:100%; border-collapse:collapse; font-size:.9rem; }
.students-table th { background:var(--bg3); color:var(--t2); font-family:var(--font-m);
    padding:10px 14px; text-align:left; border-bottom:1px solid var(--border); }
.students-table td { padding:10px 14px; border-bottom:1px solid var(--border); color:var(--t1); }
.students-table tr:last-child td { border-bottom:none; }
.students-table tr:hover td { background:var(--gold-dim); }

.badge-active   { background:#dcfce7; color:#166534; padding:3px 10px; border-radius:20px; font-size:.82rem; }
.badge-tc       { background:#fee2e2; color:#991b1b; padding:3px 10px; border-radius:20px; font-size:.82rem; }
.badge-inactive { background:var(--bg3); color:var(--t3); padding:3px 10px; border-radius:20px; font-size:.82rem; }

.act-btn { padding:7px 14px; border-radius:6px; border:1px solid var(--border);
    background:var(--bg3); color:var(--t2); cursor:pointer; font-size:.85rem; text-decoration:none; }
.act-btn:hover { background:var(--gold-dim); color:var(--gold); border-color:var(--gold-ring); }
.act-btn.red:hover { background:#fee2e2; color:#991b1b; border-color:#fecaca; }

.pagination { display:flex; gap:8px; align-items:center; justify-content:center; padding:16px; }
.page-btn { padding:5px 12px; border:1px solid var(--border); border-radius:5px;
    background:var(--bg3); color:var(--t2); cursor:pointer; font-size:.82rem; }
.page-btn.active, .page-btn:hover { background:var(--gold); color:#fff; border-color:var(--gold); }

.tbl-empty { text-align:center; padding:40px; color:var(--t3); }
</style>

<div class="content-wrapper">
<div class="sis-students-wrap">

    <div class="page-hdr">
        <h1><i class="fa fa-users" style="color:var(--gold);margin-right:8px;"></i>Student List</h1>
        <a href="<?= base_url('sis/admission') ?>" class="btn-search" style="text-decoration:none;">
            <i class="fa fa-plus"></i> New Admission
        </a>
    </div>

    <div class="filter-bar">
        <div class="fg">
            <label>Search</label>
            <input id="searchQ" type="text" placeholder="Name / ID / Father name..." style="min-width:200px;">
        </div>
        <div class="fg">
            <label>Class</label>
            <select id="classFilter">
                <option value="">All Classes</option>
                <?php foreach ($class_map as $ord => $sections): ?>
                <option value="<?= htmlspecialchars($ord) ?>">Class <?= htmlspecialchars($ord) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Section</label>
            <select id="secFilter"><option value="">All Sections</option></select>
        </div>
        <button class="btn-search" onclick="loadStudents(1)"><i class="fa fa-search"></i> Search</button>
    </div>

    <div class="students-table-wrap">
        <table class="students-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="studentsTbody">
                <tr><td colspan="9" class="tbl-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
            </tbody>
        </table>
        <div class="pagination" id="paginationWrap"></div>
    </div>

</div>
</div>

<script>
var csrfName  = document.querySelector('meta[name="csrf-name"]').content;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var CLASS_MAP = <?= json_encode($class_map) ?>;
var currentPage = 1;

document.getElementById('classFilter').addEventListener('change', function () {
    const cls = this.value;
    const secSel = document.getElementById('secFilter');
    secSel.innerHTML = '<option value="">All Sections</option>';
    if (cls && CLASS_MAP[cls]) {
        CLASS_MAP[cls].forEach(s => {
            secSel.innerHTML += `<option value="${s}">Section ${s}</option>`;
        });
    }
    loadStudents(1);
});

document.getElementById('secFilter').addEventListener('change', () => loadStudents(1));
document.getElementById('searchQ').addEventListener('keydown', e => {
    if (e.key === 'Enter') loadStudents(1);
});

function loadStudents(page) {
    currentPage = page;
    const tbody = document.getElementById('studentsTbody');
    tbody.innerHTML = '<tr><td colspan="9" class="tbl-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

    var params = new URLSearchParams({
        query:   document.getElementById('searchQ').value.trim(),
        class:   document.getElementById('classFilter').value,
        section: document.getElementById('secFilter').value,
        page:    page,
    });
    params.append(csrfName, csrfToken);

    fetch('<?= base_url('sis/search_student') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                   'X-Requested-With': 'XMLHttpRequest' },
        body: params.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status !== 'success') {
            tbody.innerHTML = `<tr><td colspan="9" class="tbl-empty">${data.message || 'No results'}</td></tr>`;
            return;
        }
        const students = data.students;
        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="tbl-empty">No students found.</td></tr>';
            document.getElementById('paginationWrap').innerHTML = '';
            return;
        }
        const offset   = (page - 1) * data.per_page;
        const fallback = '<?= base_url('tools/image/default-school.jpeg') ?>';
        tbody.innerHTML = students.map((s, i) => {
            const photo = s.photo || fallback;
            const badgeCls = s.status === 'Active' ? 'active' : (s.status === 'TC' ? 'tc' : 'inactive');
            return `<tr>
                <td>${offset + i + 1}</td>
                <td><img src="${esc(photo)}" onerror="this.src='${fallback}'"
                    style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--gold-ring);"></td>
                <td><code>${esc(s.user_id)}</code></td>
                <td><strong>${esc(s.name)}</strong></td>
                <td>${esc(s.father_name)}</td>
                <td>Class ${esc(s.class)}</td>
                <td>${esc(s.section)}</td>
                <td>${esc(s.phone)}</td>
                <td><span class="badge-${badgeCls}">${esc(s.status)}</span></td>
                <td style="white-space:nowrap;">
                    <a href="<?= base_url('sis/profile/') ?>${encodeURIComponent(s.user_id)}" class="act-btn" title="SIS Profile"><i class="fa fa-eye"></i></a>
                    <a href="<?= base_url('student/student_profile/') ?>${encodeURIComponent(s.user_id)}" class="act-btn" title="Full Profile"><i class="fa fa-user"></i></a>
                    <a href="<?= base_url('sis/documents/') ?>${encodeURIComponent(s.user_id)}" class="act-btn" title="Documents"><i class="fa fa-folder-open-o"></i></a>
                    <a href="<?= base_url('sis/history/') ?>${encodeURIComponent(s.user_id)}" class="act-btn" title="History"><i class="fa fa-history"></i></a>
                    ${s.status !== 'Inactive' ? `<button class="act-btn red" title="Withdraw" onclick="withdrawStudent('${esc(s.user_id)}','${esc(s.name)}')"><i class="fa fa-sign-out"></i></button>` : ''}
                </td>
            </tr>`;
        }).join('');

        // Pagination
        const total   = data.total;
        const perPage = data.per_page;
        const pages   = Math.ceil(total / perPage);
        let paginHtml = `<span style="color:var(--t3);font-size:.82rem;">${total} student(s)</span>`;
        if (pages > 1) {
            if (page > 1) paginHtml += `<button class="page-btn" onclick="loadStudents(${page-1})">&laquo; Prev</button>`;
            paginHtml += `<span class="page-btn active">Page ${page} / ${pages}</span>`;
            if (page < pages) paginHtml += `<button class="page-btn" onclick="loadStudents(${page+1})">Next &raquo;</button>`;
        }
        document.getElementById('paginationWrap').innerHTML = paginHtml;
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="9" class="tbl-empty">Failed to load students.</td></tr>';
    });
}

function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function withdrawStudent(userId, name) {
    var reason = prompt('Withdraw "' + name + '"?\nEnter reason (or leave blank for "Withdrawn"):') ;
    if (reason === null) return; // cancelled
    var body = new URLSearchParams({ user_id: userId, reason: reason || 'Withdrawn' });
    body.append(csrfName, csrfToken);
    fetch('<?= base_url('sis/withdraw') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') loadStudents(currentPage);
    })
    .catch(() => alert('Request failed.'));
}

// Initial load — show all enrolled students on page open
loadStudents(1);
</script>
