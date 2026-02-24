<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$students = $students ?? [];
function sl_avatar($student, $fallback)
{
    if (!empty($student['Profile Pic']) && is_string($student['Profile Pic']))
        return $student['Profile Pic'];
    if (!empty($student['Doc']['Photo']['url']))
        return $student['Doc']['Photo']['url'];
    if (!empty($student['Doc']['PhotoUrl']) && is_string($student['Doc']['PhotoUrl']))
        return $student['Doc']['PhotoUrl'];
    return $fallback;
}

$fallbackAvatar = base_url('tools/dist/img/user2-160x160.jpg');

/* Sort students by User Id */
usort($students, function ($a, $b) {
    return strcmp($a['User Id'] ?? '', $b['User Id'] ?? '');
});

/* Collect unique classes & sections for filter dropdowns */
$uniqueClasses  = [];
$uniqueSections = [];
foreach ($students as $s) {
    if (!empty($s['Class']))   $uniqueClasses[$s['Class']]   = true;
    if (!empty($s['Section'])) $uniqueSections[$s['Section']] = true;
}
ksort($uniqueClasses);
ksort($uniqueSections);
?>


<div class="content-wrapper">
    <div class="sl-wrap">

        <!-- ── Top bar ── -->
        <div class="sl-topbar">
            <div class="sl-topbar-left">
                <h1 class="sl-page-title">
                    <i class="fa fa-users"></i> Student List
                </h1>
                <ol class="sl-breadcrumb">
                    <li><a href="<?= base_url('dashboard') ?>"><i class="fa fa-home"></i> Dashboard</a></li>
                    <li>Students</li>
                </ol>
            </div>
            <div class="sl-topbar-right">
                <a href="<?= base_url('student/master_student') ?>" class="sl-btn sl-btn-warning">
                    <i class="fa fa-upload"></i> Import Students
                </a>
                <a href="<?= base_url('student/studentAdmission') ?>" class="sl-btn sl-btn-success">
                    <i class="fa fa-plus"></i> Add New Student
                </a>
            </div>
        </div>

        <!-- ── Stats row ── -->
        <!-- <?php
        $totalStudents = count($students);
        $classCount    = count($uniqueClasses);
        ?>
        <div class="sl-stats">
            <div class="sl-stat-card blue">
                <div class="sl-stat-icon"><i class="fa fa-users"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $totalStudents ?></div>
                    <div class="sl-stat-lbl">Total Students</div>
                </div>
            </div>
            <div class="sl-stat-card red">
                <div class="sl-stat-icon"><i class="fa fa-graduation-cap"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $classCount ?></div>
                    <div class="sl-stat-lbl">Active Classes</div>
                </div>
            </div>
        </div> -->

        <!-- ── Main card ── -->
        <div class="sl-card">

            <!-- Toolbar -->
            <div class="sl-toolbar">
                <div class="sl-search-wrap">
                    <i class="fa fa-search"></i>
                    <input type="text" class="sl-search-input" id="slSearch"
                        placeholder="Search by name, ID, parent…">
                </div>

                <select class="sl-filter-select" id="slFilterClass">
                    <option value="">All Classes</option>
                    <?php foreach ($uniqueClasses as $cn => $_): ?>
                        <option value="<?= htmlspecialchars($cn) ?>"><?= htmlspecialchars($cn) ?></option>
                    <?php endforeach; ?>
                </select>

                <select class="sl-filter-select" id="slFilterSection">
                    <option value="">All Sections</option>
                    <?php foreach ($uniqueSections as $sn => $_): ?>
                        <option value="<?= htmlspecialchars($sn) ?>"><?= htmlspecialchars($sn) ?></option>
                    <?php endforeach; ?>
                </select>

                <select class="sl-filter-select" id="slFilterGender">
                    <option value="">All Genders</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>

                <div class="sl-toolbar-right">
                    <span class="sl-count-badge" id="slCountBadge"><?= $totalStudents ?> students</span>
                    <button class="sl-btn sl-btn-outline sl-btn-sm" id="slClearFilters" style="display:none;">
                        <i class="fa fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Bulk action bar -->
            <div class="sl-bulk-bar" id="slBulkBar">
                <span class="sl-bulk-info"><span id="slBulkCount">0</span> students selected</span>
                <button class="sl-btn sl-btn-danger sl-btn-sm" id="slBulkDelete">
                    <i class="fa fa-trash"></i> Delete Selected
                </button>
                <button class="sl-btn sl-btn-outline sl-btn-sm" id="slBulkDeselect">
                    <i class="fa fa-times"></i> Deselect All
                </button>
            </div>

            <!-- Table -->
            <div class="sl-tbl-wrap">
                <table class="sl-tbl" id="slTable">
                    <thead>
                        <tr>
                            <th style="width:42px;text-align:center;">
                                <input type="checkbox" id="slSelectAll" style="cursor:pointer;accent-color:#1a56db;">
                            </th>
                            <th style="width:36px;">#</th>
                            <th style="width:52px;text-align:center;">Photo</th>
                            <th>Student</th>
                            <th>Gender</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Parent</th>
                            <th>Admission Date</th>
                            <th>DOB</th>
                            <th>Contact</th>
                            <th style="width:110px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slTbody">
                        <?php foreach ($students as $idx => $student):
                            $avatarUrl = sl_avatar($student, $fallbackAvatar);
                            $gender    = strtolower($student['Gender'] ?? '');
                            $gClass    = in_array($gender, ['male', 'female']) ? $gender : 'other';
                            $gLabel    = $student['Gender'] ?? 'N/A';
                        ?>
                            <tr data-name="<?= htmlspecialchars(strtolower($student['Name'] ?? '')) ?>"
                                data-uid="<?= htmlspecialchars(strtolower($student['User Id'] ?? '')) ?>"
                                data-parent="<?= htmlspecialchars(strtolower($student['Father Name'] ?? '')) ?>"
                                data-class="<?= htmlspecialchars($student['Class'] ?? '') ?>"
                                data-section="<?= htmlspecialchars($student['Section'] ?? '') ?>"
                                data-gender="<?= htmlspecialchars($gender) ?>"
                                style="animation-delay: <?= min($idx * 0.03, 0.6) ?>s">

                                <td class="center">
                                    <input type="checkbox" class="sl-row-cb"
                                        data-uid="<?= htmlspecialchars($student['User Id'] ?? '') ?>"
                                        style="cursor:pointer;accent-color:#1a56db;">
                                </td>
                                <td style="color:var(--sl-muted);font-size:12px;"><?= $idx + 1 ?></td>
                                <td class="center">
                                    <!-- BUG FIX 1 & 2: safe avatar helper, never passes array -->
                                    <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        class="sl-avatar"
                                        alt="<?= htmlspecialchars($student['Name'] ?? '') ?>"
                                        onerror="this.src='<?= $fallbackAvatar ?>'">
                                </td>
                                <td>
                                    <div class="sl-name"><?= htmlspecialchars($student['Name'] ?? 'N/A') ?></div>
                                    <div class="sl-uid"><?= htmlspecialchars($student['User Id'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="sl-gender <?= $gClass ?>"><?= htmlspecialchars($gLabel) ?></span>
                                </td>
                                <td>
                                    <span class="sl-class-pill">
                                        <?= htmlspecialchars($student['Class'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="sl-section-pill">
                                        <?= htmlspecialchars($student['Section'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($student['Father Name'] ?? 'N/A') ?></td>
                                <td style="font-size:13px;"><?= htmlspecialchars($student['Admission Date'] ?? 'N/A') ?></td>
                                <td style="font-size:13px;"><?= htmlspecialchars($student['DOB'] ?? 'N/A') ?></td>
                                <td style="font-size:13px;"><?= htmlspecialchars($student['Guard Contact'] ?? 'N/A') ?></td>
                                <td class="center">
                                    <div class="sl-actions" style="justify-content:center;">
                                        <a href="<?= base_url('student/student_profile/' . urlencode($student['User Id'] ?? '')) ?>"
                                            class="sl-act-btn sl-act-view" title="View Profile">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('student/edit_student/' . urlencode($student['User Id'] ?? '')) ?>"
                                            class="sl-act-btn sl-act-edit" title="Edit Student">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?= base_url('student/delete_student/' . urlencode($student['User Id'] ?? '')) ?>"
                                            class="sl-act-btn sl-act-delete" title="Delete Student"
                                            onclick="return confirm('Delete <?= htmlspecialchars(addslashes($student['Name'] ?? 'this student'), ENT_QUOTES) ?>? This cannot be undone.')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Empty state (shown by JS when no rows match) -->
                <div class="sl-empty" id="slEmpty" style="display:none;">
                    <i class="fa fa-search"></i>
                    <p>No students match your filters.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="sl-pager" id="slPager">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span>Rows per page:</span>
                    <select class="sl-rows-select" id="slRowsPerPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="9999">All</option>
                    </select>
                </div>
                <span id="slPagerInfo">Showing 1–10 of <?= $totalStudents ?></span>
                <div class="sl-pager-btns" id="slPagerBtns"></div>
            </div>

        </div>

    </div>
</div>


<script>
 (function() {
        'use strict';

        /* ── refs ── */
        var tbody = document.getElementById('slTbody');
        var searchInput = document.getElementById('slSearch');
        var filterClass = document.getElementById('slFilterClass');
        var filterSec = document.getElementById('slFilterSection');
        var filterGend = document.getElementById('slFilterGender');
        var selectAll = document.getElementById('slSelectAll');
        var bulkBar = document.getElementById('slBulkBar');
        var bulkCount = document.getElementById('slBulkCount');
        var bulkDelete = document.getElementById('slBulkDelete');
        var bulkDesel = document.getElementById('slBulkDeselect');
        var countBadge = document.getElementById('slCountBadge');
        var clearBtn = document.getElementById('slClearFilters');
        var emptyEl = document.getElementById('slEmpty');
        var pagerInfo = document.getElementById('slPagerInfo');
        var pagerBtns = document.getElementById('slPagerBtns');
        var rowsSelect = document.getElementById('slRowsPerPage');
        var tblWrap = document.querySelector('.sl-tbl-wrap');

        var allRows = Array.from(tbody.querySelectorAll('tr'));
        var filteredRows = allRows.slice(); // current filter result
        var currentPage = 1;
        var rowsPerPage = 10;

        /* ────────────────────────────────────────────
           FILTERING
        ──────────────────────────────────────────── */
        function applyFilters() {
            var q = searchInput.value.trim().toLowerCase();
            var cls = filterClass.value;
            var sec = filterSec.value;
            var gend = filterGend.value.toLowerCase();

            var hasFilter = q || cls || sec || gend;
            clearBtn.style.display = hasFilter ? '' : 'none';

            filteredRows = allRows.filter(function(row) {
                if (q) {
                    var name = row.dataset.name || '';
                    var uid = row.dataset.uid || '';
                    var parent = row.dataset.parent || '';
                    if (name.indexOf(q) === -1 && uid.indexOf(q) === -1 && parent.indexOf(q) === -1)
                        return false;
                }
                if (cls && row.dataset.class !== cls) return false;
                if (sec && row.dataset.section !== sec) return false;
                if (gend && row.dataset.gender !== gend) return false;
                return true;
            });

            /* renumber visible rows */
            filteredRows.forEach(function(row, i) {
                var numCell = row.cells[1];
                if (numCell) numCell.textContent = i + 1;
            });

            currentPage = 1;
            renderPage();
            updateSelectAllState();
        }

        /* ────────────────────────────────────────────
           PAGINATION
        ──────────────────────────────────────────── */
        function renderPage() {
            var total = filteredRows.length;
            var pages = rowsPerPage >= 9999 ? 1 : Math.ceil(total / rowsPerPage);
            if (currentPage > pages) currentPage = Math.max(1, pages);

            var start = (currentPage - 1) * rowsPerPage;
            var end = rowsPerPage >= 9999 ? total : Math.min(start + rowsPerPage, total);

            /* hide ALL rows first */
            allRows.forEach(function(row) {
                row.classList.add('sl-hidden');
            });

            /* show only current page of filtered rows */
            filteredRows.forEach(function(row, i) {
                if (i >= start && i < end) row.classList.remove('sl-hidden');
            });

            /* empty state */
            emptyEl.style.display = total === 0 ? 'block' : 'none';
            document.querySelector('.sl-tbl').style.display = total === 0 ? 'none' : '';

            /* count badge */
            countBadge.textContent = total + ' student' + (total !== 1 ? 's' : '');

            /* pager info */
            if (total === 0) {
                pagerInfo.textContent = 'No results';
            } else {
                pagerInfo.textContent = 'Showing ' + (start + 1) + '–' + end + ' of ' + total;
            }

            /* page buttons */
            pagerBtns.innerHTML = '';
            if (pages <= 1) return;

            function makeBtn(label, page, disabled) {
                var btn = document.createElement('button');
                btn.className = 'sl-pager-btn' + (page === currentPage ? ' active' : '');
                btn.textContent = label;
                btn.disabled = disabled;
                if (!disabled) {
                    btn.addEventListener('click', function() {
                        currentPage = page;
                        renderPage();
                        updateSelectAllState();
                    });
                }
                return btn;
            }

            pagerBtns.appendChild(makeBtn('‹', currentPage - 1, currentPage === 1));

            /* show up to 7 page buttons with ellipsis */
            var range = [];
            for (var p = 1; p <= pages; p++) {
                if (p === 1 || p === pages || (p >= currentPage - 2 && p <= currentPage + 2)) {
                    range.push(p);
                }
            }
            var prev = null;
            range.forEach(function(p) {
                if (prev !== null && p - prev > 1) {
                    var ellipsis = document.createElement('button');
                    ellipsis.className = 'sl-pager-btn';
                    ellipsis.textContent = '…';
                    ellipsis.disabled = true;
                    pagerBtns.appendChild(ellipsis);
                }
                pagerBtns.appendChild(makeBtn(p, p, false));
                prev = p;
            });

            pagerBtns.appendChild(makeBtn('›', currentPage + 1, currentPage === pages));
        }

        /* ────────────────────────────────────────────
           CHECKBOXES — BUG FIX 4: works across pages
        ──────────────────────────────────────────── */
        function getVisibleCheckboxes() {
            return Array.from(tbody.querySelectorAll('tr:not(.sl-hidden) .sl-row-cb'));
        }

        function getAllCheckboxes() {
            return Array.from(tbody.querySelectorAll('.sl-row-cb'));
        }

        function getCheckedCheckboxes() {
            return getAllCheckboxes().filter(function(cb) {
                return cb.checked;
            });
        }

        function updateSelectAllState() {
            var vis = getVisibleCheckboxes();
            var checked = vis.filter(function(cb) {
                return cb.checked;
            });
            selectAll.indeterminate = checked.length > 0 && checked.length < vis.length;
            selectAll.checked = vis.length > 0 && checked.length === vis.length;
            updateBulkBar();
        }

        function updateBulkBar() {
            var n = getCheckedCheckboxes().length;
            bulkCount.textContent = n;
            bulkBar.classList.toggle('show', n > 0);
        }

        /* Select all VISIBLE (current filtered page) rows */
        selectAll.addEventListener('change', function() {
            getVisibleCheckboxes().forEach(function(cb) {
                cb.checked = selectAll.checked;
                cb.closest('tr').classList.toggle('sl-checked', selectAll.checked);
            });
            updateBulkBar();
        });

        /* Individual row checkbox */
        tbody.addEventListener('change', function(e) {
            if (!e.target.classList.contains('sl-row-cb')) return;
            e.target.closest('tr').classList.toggle('sl-checked', e.target.checked);
            updateSelectAllState();
        });

        /* Bulk deselect */
        bulkDesel.addEventListener('click', function() {
            getAllCheckboxes().forEach(function(cb) {
                cb.checked = false;
                cb.closest('tr').classList.remove('sl-checked');
            });
            selectAll.checked = false;
            selectAll.indeterminate = false;
            updateBulkBar();
        });

        /* Bulk delete confirmation */
        bulkDelete.addEventListener('click', function() {
            var checked = getCheckedCheckboxes();
            if (checked.length === 0) return;
            var names = checked.map(function(cb) {
                return cb.dataset.uid;
            }).join(', ');
            if (!confirm('Delete ' + checked.length + ' selected students?\n\nIDs: ' + names + '\n\nThis cannot be undone.')) return;
            /* Navigate to bulk delete endpoint or handle individually */
            checked.forEach(function(cb) {
                var uid = cb.dataset.uid;
                if (uid) window.location.href = '<?= base_url("student/delete_student/") ?>' + encodeURIComponent(uid);
            });
        });

        /* ────────────────────────────────────────────
           ROWS PER PAGE
        ──────────────────────────────────────────── */
        rowsSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value) || 10;
            currentPage = 1;
            renderPage();
        });

        /* ────────────────────────────────────────────
           FILTER EVENTS
        ──────────────────────────────────────────── */
        searchInput.addEventListener('input', applyFilters);
        filterClass.addEventListener('change', applyFilters);
        filterSec.addEventListener('change', applyFilters);
        filterGend.addEventListener('change', applyFilters);
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterClass.value = '';
            filterSec.value = '';
            filterGend.value = '';
            applyFilters();
        });

        /* ── initial render ── */
        renderPage();

    })();
</script>


<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    :root {
        --sl-navy: #0d1f3c;
        --sl-blue: #1a56db;
        --sl-sky: #ebf2ff;
        --sl-green: #16a34a;
        --sl-amber: #d97706;
        --sl-red: #dc2626;
        --sl-text: #1a2535;
        --sl-muted: #607080;
        --sl-border: #dde5f0;
        --sl-white: #ffffff;
        --sl-bg: #f0f4fa;
        --sl-shadow: 0 2px 16px rgba(13, 31, 60, .08);
        --sl-radius: 14px;
    }

    /* ── wrapper ── */
    .sl-wrap {
        font-family: 'DM Sans', sans-serif;
        background: var(--sl-bg);
        color: var(--sl-text);
        padding: 24px 22px 52px;
        min-height: 100vh;
    }

    /* ── top bar ── */
    .sl-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }

    .sl-topbar-left {}

    .sl-page-title {
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--sl-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 6px;
    }

    .sl-page-title i {
        color: var(--sl-blue);
        font-size: 22px;
    }

    .sl-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--sl-muted);
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sl-breadcrumb a {
        color: var(--sl-blue);
        text-decoration: none;
        font-weight: 500;
    }

    .sl-breadcrumb a:hover {
        text-decoration: underline;
    }

    .sl-breadcrumb li::before {
        content: '/';
        margin-right: 6px;
        color: var(--sl-border);
    }

    .sl-breadcrumb li:first-child::before {
        display: none;
    }

    .sl-topbar-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    /* ── buttons ── */
    .sl-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: opacity .14s, transform .1s, box-shadow .14s;
        font-family: 'DM Sans', sans-serif;
        white-space: nowrap;
    }

    .sl-btn:hover {
        opacity: .87;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .sl-btn-primary {
        background: var(--sl-blue);
        color: #fff;
        box-shadow: 0 2px 8px rgba(26, 86, 219, .25);
    }

    .sl-btn-success {
        background: var(--sl-green);
        color: #fff;
        box-shadow: 0 2px 8px rgba(22, 163, 74, .25);
    }

    .sl-btn-warning {
        background: var(--sl-amber);
        color: #fff;
        box-shadow: 0 2px 8px rgba(217, 119, 6, .2);
    }

    .sl-btn-danger {
        background: var(--sl-red);
        color: #fff;
    }

    .sl-btn-outline {
        background: var(--sl-white);
        color: var(--sl-blue);
        border: 1.5px solid var(--sl-blue);
        box-shadow: none;
    }

    .sl-btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 7px;
    }

    .sl-btn-icon {
        padding: 7px 10px;
    }

    /* ── stats row ── */
    .sl-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .sl-stat-card {
        background: var(--sl-white);
        border-radius: var(--sl-radius);
        box-shadow: var(--sl-shadow);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-left: 4px solid transparent;
        transition: transform .14s, box-shadow .14s;
    }

    .sl-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 31, 60, .12);
    }

    .sl-stat-card.blue {
        border-color: var(--sl-blue);
    }

    .sl-stat-card.green {
        border-color: var(--sl-green);
    }

    .sl-stat-card.amber {
        border-color: var(--sl-amber);
    }

    .sl-stat-card.red {
        border-color: var(--sl-red);
    }

    .sl-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .blue .sl-stat-icon {
        background: #dbeafe;
        color: var(--sl-blue);
    }

    .green .sl-stat-icon {
        background: #dcfce7;
        color: var(--sl-green);
    }

    .amber .sl-stat-icon {
        background: #fef3c7;
        color: var(--sl-amber);
    }

    .red .sl-stat-icon {
        background: #fee2e2;
        color: var(--sl-red);
    }

    .sl-stat-num {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--sl-navy);
        line-height: 1;
    }

    .sl-stat-lbl {
        font-size: 12px;
        color: var(--sl-muted);
        margin-top: 2px;
        font-weight: 500;
    }

    /* ── main card ── */
    .sl-card {
        background: var(--sl-white);
        border-radius: var(--sl-radius);
        box-shadow: var(--sl-shadow);
        overflow: hidden;
    }

    /* ── toolbar ── */
    .sl-toolbar {
        padding: 16px 20px;
        border-bottom: 1px solid var(--sl-border);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--sl-white);
    }

    .sl-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }

    .sl-search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--sl-muted);
        font-size: 13px;
        pointer-events: none;
    }

    .sl-search-input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        border: 1.5px solid var(--sl-border);
        border-radius: 8px;
        font-size: 13.5px;
        color: var(--sl-text);
        outline: none;
        transition: border-color .14s;
        font-family: 'DM Sans', sans-serif;
        background: #fafbff;
    }

    .sl-search-input:focus {
        border-color: var(--sl-blue);
    }

    .sl-filter-select {
        padding: 8px 30px 8px 11px;
        border: 1.5px solid var(--sl-border);
        border-radius: 8px;
        font-size: 13px;
        color: var(--sl-text);
        outline: none;
        background: #fafbff;
        transition: border-color .14s;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%23607080' d='M5 7L0 2h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-color: #fafbff;
    }

    .sl-filter-select:focus {
        border-color: var(--sl-blue);
    }

    .sl-toolbar-right {
        margin-left: auto;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .sl-count-badge {
        background: var(--sl-sky);
        color: var(--sl-blue);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12.5px;
        font-weight: 600;
    }

    /* ── bulk action bar ── */
    .sl-bulk-bar {
        display: none;
        padding: 10px 20px;
        background: #fff7ed;
        border-bottom: 1px solid #fed7aa;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sl-bulk-bar.show {
        display: flex;
    }

    .sl-bulk-info {
        font-size: 13px;
        font-weight: 600;
        color: #92400e;
    }

    /* ── table ── */
    .sl-tbl-wrap {
        overflow-x: auto;
    }

    .sl-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .sl-tbl thead th {
        background: var(--sl-navy);
        color: #fff;
        padding: 11px 13px;
        text-align: left;
        font-weight: 500;
        white-space: nowrap;
        border-right: 1px solid rgba(255, 255, 255, .07);
        font-size: 12px;
        letter-spacing: .3px;
    }

    .sl-tbl thead th:first-child {
        border-radius: 0;
    }

    .sl-tbl thead th.center {
        text-align: center;
    }

    .sl-tbl tbody tr {
        border-bottom: 1px solid var(--sl-border);
        transition: background .11s;
        animation: sl-row-in .25s ease both;
    }

    @keyframes sl-row-in {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sl-tbl tbody tr:hover {
        background: var(--sl-sky);
    }

    .sl-tbl tbody tr.sl-checked {
        background: #dbeafe;
    }

    .sl-tbl tbody tr.sl-hidden {
        display: none;
    }

    .sl-tbl td {
        padding: 10px 13px;
        vertical-align: middle;
        color: var(--sl-text);
    }

    .sl-tbl td.center {
        text-align: center;
    }

    /* Avatar */
    .sl-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--sl-border);
        transition: transform .14s, border-color .14s;
    }

    .sl-tbl tbody tr:hover .sl-avatar {
        transform: scale(1.08);
        border-color: var(--sl-blue);
    }

    /* Student name */
    .sl-name {
        font-weight: 600;
        color: var(--sl-navy);
    }

    .sl-uid {
        font-size: 11.5px;
        color: var(--sl-muted);
        margin-top: 1px;
    }

    /* Gender — plain, no colour */
    .sl-gender {
        font-size: 13px;
        color: var(--sl-text);
        font-weight: 400;
    }

    /* Class — plain, no colour */
    .sl-class-pill {
        font-size: 13px;
        color: var(--sl-text);
        font-weight: 500;
    }

    /* Section — plain, no colour */
    .sl-section-pill {
        font-size: 13px;
        color: var(--sl-text);
        font-weight: 500;
    }

    /* Action buttons in table */
    .sl-actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .sl-act-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: opacity .13s, transform .1s;
    }

    .sl-act-btn:hover {
        opacity: .85;
        transform: translateY(-1px);
    }

    .sl-act-view {
        background: #dcfce7;
        color: #15803d;
    }

    .sl-act-edit {
        background: #fef9c3;
        color: #854d0e;
    }

    .sl-act-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ── empty state ── */
    .sl-empty {
        padding: 60px 24px;
        text-align: center;
        color: var(--sl-muted);
    }

    .sl-empty i {
        font-size: 44px;
        margin-bottom: 14px;
        display: block;
        color: var(--sl-border);
    }

    .sl-empty p {
        font-size: 15px;
        margin: 0;
    }

    /* ── pagination ── */
    .sl-pager {
        padding: 14px 20px;
        border-top: 1px solid var(--sl-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 13px;
        color: var(--sl-muted);
    }

    .sl-pager-btns {
        display: flex;
        gap: 5px;
    }

    .sl-pager-btn {
        padding: 5px 12px;
        border-radius: 7px;
        background: var(--sl-white);
        border: 1.5px solid var(--sl-border);
        color: var(--sl-text);
        font-size: 12.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all .13s;
        font-family: 'DM Sans', sans-serif;
    }

    .sl-pager-btn:hover:not(:disabled) {
        border-color: var(--sl-blue);
        color: var(--sl-blue);
    }

    .sl-pager-btn.active {
        background: var(--sl-blue);
        border-color: var(--sl-blue);
        color: #fff;
    }

    .sl-pager-btn:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    .sl-rows-select {
        padding: 5px 8px;
        border: 1.5px solid var(--sl-border);
        border-radius: 7px;
        font-size: 12.5px;
        outline: none;
        font-family: 'DM Sans', sans-serif;
        background: #fafbff;
    }

    .sl-rows-select:focus {
        border-color: var(--sl-blue);
    }

    @media (max-width: 768px) {
        .sl-topbar {
            flex-direction: column;
        }

        .sl-topbar-right {
            width: 100%;
        }

        .sl-btn {
            flex: 1;
            justify-content: center;
        }

        .sl-stats {
            grid-template-columns: 1fr 1fr;
        }

        .sl-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .sl-search-wrap {
            max-width: 100%;
        }

        .sl-toolbar-right {
            margin-left: 0;
        }
    }
</style>