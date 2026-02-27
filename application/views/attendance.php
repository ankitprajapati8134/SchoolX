<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <div class="at-wrap">

        <div class="at-page-title">
            <i class="fa fa-calendar-check-o"></i> Student Attendance
        </div>

        <!-- ── Filter card ── -->
        <div class="at-filter-card">
            <div class="at-filter-card-title">
                <i class="fa fa-filter"></i> Select Class, Section &amp; Month
            </div>

            <div class="at-filter-row" id="filterRow">

                <div class="at-field">
                    <label for="atClass">Class <span style="color:var(--at-red)">*</span></label>
                    <!-- BUG FIX 1: PHP block removed — JS fetch is the single source -->
                    <select id="atClass" class="at-select" required>
                        <option value="" disabled selected>Loading…</option>
                    </select>
                </div>

                <div class="at-field">
                    <label for="atSection">Section <span style="color:var(--at-red)">*</span></label>
                    <select id="atSection" class="at-select" required disabled>
                        <option value="" disabled selected>Select Class first</option>
                    </select>
                </div>

                <div class="at-field">
                    <label for="atMonth">Month <span style="color:var(--at-red)">*</span></label>
                    <select id="atMonth" class="at-select" required>
                        <option value="" disabled selected>Select Month</option>
                        <?php foreach (['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'] as $m): ?>
                            <option value="<?= $m ?>"><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="at-field">
                    <label>&nbsp;</label>
                    <button id="atSearchBtn" class="at-btn-search" type="button">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>

            </div>
        </div>

        <!-- ── Default / empty state ── -->
        <div class="at-state" id="atStateDefault">
            <i class="fa fa-calendar-o"></i>
            <p>Select class, section and month above to view attendance.</p>
        </div>

        <!-- ── Loading state ── -->
        <div class="at-state hidden" id="atStateLoading">
            <i class="fa fa-spinner fa-spin" style="color:var(--at-blue);"></i>
            <p>Loading attendance data…</p>
        </div>

        <!-- ── Error state ── -->
        <div class="at-state hidden" id="atStateError">
            <i class="fa fa-exclamation-circle" style="color:var(--at-red);"></i>
            <p id="atErrorMsg">Something went wrong.</p>
        </div>

        <!-- ── Result card ── -->
        <div class="at-result-card hidden" id="atResultCard">

            <div class="at-result-header">
                <div class="at-result-header-title">
                    <i class="fa fa-table"></i>
                    <span id="atResultTitle">Attendance</span>
                </div>
                <span class="at-result-hint">
                    <i class="fa fa-info-circle"></i> Double-click a row for summary
                </span>
            </div>

            <div class="at-tbl-scroll">
                <table class="at-tbl">
                    <thead id="atThead"></thead>
                    <tbody id="atTbody"></tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="at-legend">
                <div class="at-leg-item">
                    <div class="at-leg-dot leg-P"></div> P – Present
                </div>
                <div class="at-leg-item">
                    <div class="at-leg-dot leg-A"></div> A – Absent
                </div>
                <div class="at-leg-item">
                    <div class="at-leg-dot leg-L"></div> L – Leave
                </div>
                <div class="at-leg-item">
                    <div class="at-leg-dot leg-V"></div> V – Vacant / Not Marked
                </div>
            </div>

        </div>

    </div><!-- /.at-wrap -->
</div><!-- /.content-wrapper -->


<!-- ── Summary modal ── -->
<div class="at-overlay" id="atOverlay">
    <div class="at-modal">
        <div class="at-modal-head">
            <h4><i class="fa fa-bar-chart" style="margin-right:7px;"></i> Attendance Summary</h4>
            <button class="at-modal-x" id="atModalX">&times;</button>
        </div>
        <div class="at-modal-body">
            <div class="at-modal-name" id="atModalName"></div>
            <div class="at-stat-grid" id="atStatGrid"></div>
        </div>
    </div>
</div>




<script>
    /* ================================================================
   attendance.php  — all jQuery removed (BUG FIX 4), pure vanilla
================================================================ */
    (function() {
        'use strict';

        /* ── element refs ── */
        var selClass = document.getElementById('atClass');
        var selSection = document.getElementById('atSection');
        var selMonth = document.getElementById('atMonth');
        var btnSearch = document.getElementById('atSearchBtn');
        var thead = document.getElementById('atThead');
        var tbody = document.getElementById('atTbody');
        var overlay = document.getElementById('atOverlay');
        var modalName = document.getElementById('atModalName');
        var statGrid = document.getElementById('atStatGrid');

        var stDefault = document.getElementById('atStateDefault');
        var stLoading = document.getElementById('atStateLoading');
        var stError = document.getElementById('atStateError');
        var stResult = document.getElementById('atResultCard');
        var errMsg = document.getElementById('atErrorMsg');
        var resultTitle = document.getElementById('atResultTitle');

        /* ── state helpers ── */
        function showOnly(el) {
            [stDefault, stLoading, stError, stResult].forEach(function(e) {
                e.classList.add('hidden');
            });
            el.classList.remove('hidden');
        }

        function showError(msg) {
            errMsg.textContent = msg || 'An error occurred.';
            showOnly(stError);
        }

        /* ────────────────────────────────────────────────────────
           STEP 1 — Load classes on page load (JS only, BUG FIX 1)
        ──────────────────────────────────────────────────────── */
        fetch('<?= base_url("student/get_classes") ?>')
            .then(function(r) {
                return r.json();
            })
            .then(function(classes) {
                selClass.innerHTML = '<option value="" disabled selected>Select Class</option>';
                if (!Array.isArray(classes) || classes.length === 0) {
                    selClass.innerHTML = '<option value="" disabled selected>No classes found</option>';
                    return;
                }
                classes.forEach(function(cn) {
                    var o = document.createElement('option');
                    o.value = o.textContent = cn;
                    selClass.appendChild(o);
                });
            })
            .catch(function() {
                selClass.innerHTML = '<option value="" disabled selected>Error loading classes</option>';
            });

        /* ────────────────────────────────────────────────────────
           STEP 2 — Load sections when class changes
        ──────────────────────────────────────────────────────── */
        selClass.addEventListener('change', function() {
            selSection.innerHTML = '<option value="" disabled selected>Loading…</option>';
            selSection.disabled = true;

            fetch('<?= base_url("student/get_sections_by_class") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        class_name: this.value
                    })
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(sections) {
                    selSection.innerHTML = '<option value="" disabled selected>Select Section</option>';
                    if (!Array.isArray(sections) || sections.length === 0) {
                        selSection.innerHTML = '<option value="" disabled selected>No sections found</option>';
                        return;
                    }
                    sections.forEach(function(s) {
                        var o = document.createElement('option');
                        o.value = o.textContent = s;
                        selSection.appendChild(o);
                    });
                    selSection.disabled = false;
                })
                .catch(function() {
                    selSection.innerHTML = '<option value="" disabled selected>Error loading sections</option>';
                    selSection.disabled = false;
                });
        });

        /* ────────────────────────────────────────────────────────
           STEP 3 — Search button click
        ──────────────────────────────────────────────────────── */
        btnSearch.addEventListener('click', function() {
            var cls = selClass.value;
            var sec = selSection.value;
            var mon = selMonth.value;

            if (!cls || !sec || !mon) {
                alert('Please select Class, Section and Month.');
                return;
            }

            showOnly(stLoading);
            btnSearch.disabled = true;
            btnSearch.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Searching…';

            fetch('<?= base_url("student/fetchAttendance") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': csrfToken
                    },
                    body: 'class=' + encodeURIComponent(cls) +
                        '&section=' + encodeURIComponent(sec) +
                        '&month=' + encodeURIComponent(mon)
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    btnSearch.disabled = false;
                    btnSearch.innerHTML = '<i class="fa fa-search"></i> Search';

                    if (data.error) {
                        showError(data.error);
                        return;
                    }

                    buildTable(data, cls, sec, mon);
                    resultTitle.textContent = cls + ' – Section ' + sec + ' · ' + mon + ' ' + data.year;
                    showOnly(stResult);
                })
                .catch(function(err) {
                    console.error(err);
                    btnSearch.disabled = false;
                    btnSearch.innerHTML = '<i class="fa fa-search"></i> Search';
                    showError('Server error — please try again.');
                });
        });

        /* ────────────────────────────────────────────────────────
           Build attendance table
        ──────────────────────────────────────────────────────── */
        var DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        var MONTH_IDX = {
            January: 0,
            February: 1,
            March: 2,
            April: 3,
            May: 4,
            June: 5,
            July: 6,
            August: 7,
            September: 8,
            October: 9,
            November: 10,
            December: 11
        };

        function buildTable(data, cls, sec, mon) {
            var students = data.students;
            var days = data.daysInMonth;
            var sundays = data.sundays; // array of day numbers (1-based)
            var year = data.year; // correctly resolved year from controller
            var mIdx = MONTH_IDX[mon] !== undefined ? MONTH_IDX[mon] : 0;

            /* ── header row ── */
            var hRow = document.createElement('tr');

            ['User ID', 'Student Name'].forEach(function(t, i) {
                var th = document.createElement('th');
                th.textContent = t;
                th.style.textAlign = 'left';
                hRow.appendChild(th);
            });

            for (var d = 1; d <= days; d++) {
                var th = document.createElement('th');
                /* BUG FIX 5 — use data.year, not hardcoded 2020 */
                var dayName = DAY_NAMES[new Date(year, mIdx, d).getDay()];
                th.innerHTML = d + '<br><span style="font-size:10px;opacity:.65;">' + dayName + '</span>';
                if (sundays.indexOf(d) !== -1) th.classList.add('at-sunday');
                hRow.appendChild(th);
            }

            thead.innerHTML = '';
            thead.appendChild(hRow);

            /* ── body rows ── */
            tbody.innerHTML = '';

            students.forEach(function(student) {
                var tr = document.createElement('tr');

                /* BUG FIX 3 — store attendance in data attribute, not read from DOM */
                tr.dataset.attendance = JSON.stringify(student.attendance);
                tr.dataset.name = student.name;

                /* User ID cell */
                var tdId = document.createElement('td');
                tdId.textContent = student.userId;
                tr.appendChild(tdId);

                /* Name cell */
                var tdName = document.createElement('td');
                tdName.textContent = student.name;
                tr.appendChild(tdName);

                /* Day cells */
                for (var i = 0; i < days; i++) {
                    var td = document.createElement('td');
                    var status = (student.attendance[i] || 'V').toUpperCase();
                    td.textContent = status;
                    td.classList.add('at-' + status);
                    if (sundays.indexOf(i + 1) !== -1) td.classList.add('at-sunday');
                    tr.appendChild(td);
                }

                /* click = select row */
                tr.addEventListener('click', function() {
                    tbody.querySelectorAll('tr').forEach(function(r) {
                        r.classList.remove('at-row-selected');
                    });
                    tr.classList.add('at-row-selected');
                });

                /* double-click = summary modal */
                tr.addEventListener('dblclick', function() {
                    /* BUG FIX 3 — read from data-attribute, not DOM slice(2) */
                    var att = JSON.parse(tr.dataset.attendance || '[]');
                    var name = tr.dataset.name || 'Unknown';

                    var P = att.filter(function(a) {
                        return a === 'P';
                    }).length;
                    var A = att.filter(function(a) {
                        return a === 'A';
                    }).length;
                    var L = att.filter(function(a) {
                        return a === 'L';
                    }).length;
                    var V = att.filter(function(a) {
                        return a === 'V';
                    }).length;

                    modalName.innerHTML = '<i class="fa fa-user-circle-o"></i> ' + name;
                    statGrid.innerHTML =
                        '<div class="at-stat stat-P"><div class="num">' + P + '</div><div class="lbl">Present</div></div>' +
                        '<div class="at-stat stat-A"><div class="num">' + A + '</div><div class="lbl">Absent</div></div>' +
                        '<div class="at-stat stat-L"><div class="num">' + L + '</div><div class="lbl">Leave</div></div>' +
                        '<div class="at-stat stat-V"><div class="num">' + V + '</div><div class="lbl">Vacant</div></div>';

                    overlay.classList.add('open'); /* BUG FIX 4 — no jQuery fadeIn */
                });

                tbody.appendChild(tr);
            });
        }

        /* ── modal close — BUG FIX 4: pure vanilla, no jQuery ── */
        document.getElementById('atModalX').addEventListener('click', function() {
            overlay.classList.remove('open');
        });
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });

    })();
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Fraunces:ital,wght@0,500;0,600;0,700;1,500&display=swap');

    :root {
        --at-navy: #0b1f3a;
        --at-blue: #1a56db;
        --at-sky: #e8f0fe;
        --at-green: #15803d;
        --at-red: #dc2626;
        --at-amber: #d97706;
        --at-text: #1e2d3d;
        --at-muted: #5e7083;
        --at-border: #dce4f0;
        --at-white: #ffffff;
        --at-bg: #f0f4fa;
        --at-shadow: 0 2px 16px rgba(11, 31, 58, .08);
        --at-radius: 14px;
    }

    /* ── wrapper ── */
    .at-wrap {
        font-family: 'Sora', sans-serif;
        background: var(--at-bg);
        color: var(--at-text);
        padding: 24px 20px 52px;
        min-height: 100vh;
    }

    /* ── page title ── */
    .at-page-title {
        font-family: 'Fraunces', serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--at-navy);
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .at-page-title i {
        color: var(--at-blue);
    }

    /* ── filter card ── */
    .at-filter-card {
        background: var(--at-white);
        border-radius: var(--at-radius);
        box-shadow: var(--at-shadow);
        padding: 22px 26px;
        margin-bottom: 20px;
    }

    .at-filter-card-title {
        font-family: 'Fraunces', serif;
        font-size: 15px;
        font-weight: 600;
        color: var(--at-navy);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 18px;
    }

    .at-filter-card-title i {
        color: var(--at-blue);
    }

    .at-filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .at-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .55px;
        color: var(--at-muted);
        margin-bottom: 5px;
    }

    .at-select {
        width: 100%;
        padding: 9px 32px 9px 12px;
        border: 1.5px solid var(--at-border);
        border-radius: 8px;
        font-size: 13.5px;
        color: var(--at-text);
        background: #fafbff;
        outline: none;
        transition: border-color .14s, box-shadow .14s;
        font-family: 'Sora', sans-serif;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%235e7083' d='M5 7L0 2h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 11px center;
        cursor: pointer;
    }

    .at-select:focus {
        border-color: var(--at-blue);
        box-shadow: 0 0 0 3px rgba(26, 86, 219, .12);
    }

    .at-select:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .at-btn-search {
        width: 100%;
        padding: 10px 18px;
        background: var(--at-blue);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: opacity .14s, transform .1s;
        font-family: 'Sora', sans-serif;
    }

    .at-btn-search:hover:not(:disabled) {
        opacity: .88;
        transform: translateY(-1px);
    }

    .at-btn-search:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    /* ── state panels ── */
    .at-state {
        background: var(--at-white);
        border-radius: var(--at-radius);
        box-shadow: var(--at-shadow);
        padding: 50px 24px;
        text-align: center;
        margin-bottom: 20px;
    }

    .at-state i {
        font-size: 40px;
        color: #c9d6e8;
        display: block;
        margin-bottom: 12px;
    }

    .at-state p {
        font-size: 15px;
        color: var(--at-muted);
        margin: 0;
    }

    .at-state.hidden {
        display: none;
    }

    /* ── result card ── */
    .at-result-card {
        background: var(--at-white);
        border-radius: var(--at-radius);
        box-shadow: var(--at-shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .at-result-card.hidden {
        display: none;
    }

    .at-result-header {
        padding: 13px 22px;
        background: var(--at-sky);
        border-bottom: 1px solid var(--at-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .at-result-header-title {
        font-family: 'Fraunces', serif;
        font-size: 16px;
        font-weight: 600;
        color: var(--at-navy);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .at-result-header-title i {
        color: var(--at-blue);
    }

    .at-result-hint {
        font-size: 12px;
        color: var(--at-muted);
    }

    /* ── attendance table ── */
    .at-tbl-scroll {
        overflow-x: auto;
    }

    .at-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        min-width: 500px;
    }

    .at-tbl thead th {
        background: var(--at-navy);
        color: #fff;
        padding: 10px 7px;
        text-align: center;
        font-weight: 500;
        white-space: nowrap;
        border-right: 1px solid rgba(255, 255, 255, .08);
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .at-tbl thead th:first-child {
        text-align: left;
        padding-left: 14px;
        min-width: 90px;
    }

    .at-tbl thead th:nth-child(2) {
        text-align: left;
        padding-left: 12px;
        min-width: 130px;
    }

    .at-tbl thead th.at-sunday {
        background: #172d4e;
    }

    .at-tbl tbody tr {
        transition: background .11s;
        cursor: pointer;
    }

    .at-tbl tbody tr:hover {
        background: var(--at-sky);
    }

    .at-tbl tbody tr.at-row-selected {
        background: #dbeafe !important;
    }

    .at-tbl td {
        padding: 8px 7px;
        text-align: center;
        border-bottom: 1px solid var(--at-border);
        border-right: 1px solid var(--at-border);
    }

    .at-tbl td:first-child {
        text-align: left;
        padding-left: 14px;
        font-weight: 600;
        font-size: 11.5px;
        white-space: nowrap;
    }

    .at-tbl td:nth-child(2) {
        text-align: left;
        padding-left: 12px;
        white-space: nowrap;
    }

    .at-tbl td.at-sunday {
        background: #f5f8ff;
    }

    /* attendance status colours */
    .at-P {
        background: #dcfce7;
        color: #15803d;
        font-weight: 700;
        border-radius: 4px;
    }

    .at-A {
        background: #fee2e2;
        color: #dc2626;
        font-weight: 700;
        border-radius: 4px;
    }

    .at-L {
        background: #fef9c3;
        color: #a16207;
        font-weight: 700;
        border-radius: 4px;
    }

    .at-V {
        background: #fef3c7;
        color: #92400e;
        font-weight: 700;
        border-radius: 4px;
    }

    /* ── legend ── */
    .at-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        padding: 13px 22px;
        border-top: 1px solid var(--at-border);
        background: #fafbff;
    }

    .at-leg-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--at-muted);
    }

    .at-leg-dot {
        width: 13px;
        height: 13px;
        border-radius: 3px;
        border: 1px solid rgba(0, 0, 0, .1);
    }

    .leg-P {
        background: #dcfce7;
    }

    .leg-A {
        background: #fee2e2;
    }

    .leg-L {
        background: #fef9c3;
    }

    .leg-V {
        background: #fef3c7;
    }

    /* ── summary modal ── */
    .at-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .48);
        z-index: 9100;
        align-items: center;
        justify-content: center;
    }

    .at-overlay.open {
        display: flex;
    }

    /* BUG FIX 4 — pure CSS, no jQuery */

    .at-modal {
        background: var(--at-white);
        border-radius: var(--at-radius);
        width: 90%;
        max-width: 380px;
        box-shadow: 0 8px 36px rgba(0, 0, 0, .2);
        overflow: hidden;
        animation: at-modal-in .16s ease;
    }

    @keyframes at-modal-in {
        from {
            transform: scale(.94);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .at-modal-head {
        background: var(--at-navy);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .at-modal-head h4 {
        margin: 0;
        font-family: 'Fraunces', serif;
        font-size: 17px;
        font-weight: 700;
        color: #fff;
    }

    .at-modal-x {
        background: none;
        border: none;
        color: rgba(255, 255, 255, .65);
        font-size: 22px;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        transition: color .12s;
    }

    .at-modal-x:hover {
        color: #fff;
    }

    .at-modal-body {
        padding: 22px 20px;
    }

    .at-modal-name {
        font-size: 14.5px;
        font-weight: 600;
        color: var(--at-navy);
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 16px;
    }

    .at-modal-name i {
        color: var(--at-blue);
    }

    .at-stat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .at-stat {
        border-radius: 10px;
        padding: 14px 10px;
        text-align: center;
    }

    .at-stat .num {
        font-family: 'Fraunces', serif;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .at-stat .lbl {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 4px;
    }

    .stat-P {
        background: #dcfce7;
    }

    .stat-P .num {
        color: #15803d;
    }

    .stat-P .lbl {
        color: #166534;
    }

    .stat-A {
        background: #fee2e2;
    }

    .stat-A .num {
        color: #dc2626;
    }

    .stat-A .lbl {
        color: #991b1b;
    }

    .stat-L {
        background: #fef9c3;
    }

    .stat-L .num {
        color: #a16207;
    }

    .stat-L .lbl {
        color: #713f12;
    }

    .stat-V {
        background: #fef3c7;
    }

    .stat-V .num {
        color: #92400e;
    }

    .stat-V .lbl {
        color: #78350f;
    }

    /* ── responsive ── */
    @media (max-width: 600px) {
        .at-filter-row {
            grid-template-columns: 1fr;
        }
    }

    @media print {

        .at-filter-card,
        .at-legend {
            display: none;
        }

        .at-tbl-scroll {
            overflow: visible;
        }

        .at-tbl {
            font-size: 11px;
        }
    }
</style>