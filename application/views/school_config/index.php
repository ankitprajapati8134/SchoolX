<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ── School Config Page ───────────────────────────────────── */
.sc-wrap { padding: 20px 22px 40px; }

.sc-head {
    display:flex; align-items:center; gap:14px;
    padding: 18px 22px; margin-bottom: 22px;
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--sh);
}
.sc-head-icon {
    width:44px; height:44px; border-radius:10px;
    background:var(--gold); display:flex; align-items:center;
    justify-content:center; flex-shrink:0;
    box-shadow:0 0 18px var(--gold-glow);
}
.sc-head-icon i { color:#fff; font-size:20px; }
.sc-head-title { font-size:18px; font-weight:700; color:var(--t1); font-family:var(--font-d); }
.sc-head-sub   { font-size:12px; color:var(--t3); margin-top:2px; }

/* ── Nav Tabs ─────────────────────────────────────────────── */
.sc-tabs { display:flex; gap:4px; flex-wrap:wrap; margin-bottom:20px; }
.sc-tab {
    display:flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:8px; border:1px solid var(--border);
    background:var(--bg2); color:var(--t2); font-size:12.5px; font-weight:600;
    cursor:pointer; transition:all var(--ease); white-space:nowrap;
}
.sc-tab:hover { border-color:var(--gold); color:var(--gold); }
.sc-tab.active {
    background:var(--gold); color:#fff; border-color:var(--gold);
    box-shadow:0 0 14px var(--gold-glow);
}
.sc-tab i { font-size:13px; }

/* ── Tab Panes ────────────────────────────────────────────── */
.sc-pane { display:none; }
.sc-pane.active { display:block; }

/* ── Card ─────────────────────────────────────────────────── */
.sc-card {
    background:var(--bg2); border:1px solid var(--border);
    border-radius:var(--r); padding:22px; margin-bottom:18px;
    box-shadow:var(--sh);
}
.sc-card-title {
    font-size:14px; font-weight:700; color:var(--t1);
    margin-bottom:16px; display:flex; align-items:center; gap:8px;
}
.sc-card-title i { color:var(--gold); font-size:15px; }

/* ── Form Grid ────────────────────────────────────────────── */
.sc-grid { display:grid; gap:14px; }
.sc-grid-1 { grid-template-columns:1fr; }
.sc-grid-2 { grid-template-columns:1fr 1fr; }
.sc-grid-3 { grid-template-columns:1fr 1fr 1fr; }
@media(max-width:640px){ .sc-grid-2,.sc-grid-3{ grid-template-columns:1fr; } }

.sc-field label {
    display:block; font-size:11.5px; font-weight:600;
    color:var(--t2); margin-bottom:5px; text-transform:uppercase; letter-spacing:.4px;
}
.sc-field input, .sc-field select, .sc-field textarea {
    width:100%; padding:9px 12px; border-radius:8px;
    border:1px solid var(--border); background:var(--bg3);
    color:var(--t1); font-size:13px; font-family:var(--font-b);
    transition:border-color var(--ease), box-shadow var(--ease);
    outline:none;
}
.sc-field input:focus, .sc-field select:focus, .sc-field textarea:focus {
    border-color:var(--gold); box-shadow:0 0 0 3px var(--gold-ring);
}
.sc-field select option { background:var(--bg3); }

/* ── Buttons ──────────────────────────────────────────────── */
.sc-btn {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 18px; border-radius:8px; border:none;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:all var(--ease); font-family:var(--font-b);
}
.sc-btn-primary {
    background:var(--gold); color:#fff;
    box-shadow:0 2px 10px var(--gold-ring);
}
.sc-btn-primary:hover { background:var(--gold2); }
.sc-btn-ghost {
    background:transparent; color:var(--t2);
    border:1px solid var(--border);
}
.sc-btn-ghost:hover { border-color:var(--gold); color:var(--gold); }
.sc-btn-danger { background:#c0392b; color:#fff; }
.sc-btn-danger:hover { background:#a93226; }
.sc-btn-warning { background:#d97706; color:#fff; }
.sc-btn-warning:hover { background:#b45309; }
.sc-btn-sm { padding:5px 12px; font-size:11.5px; }

/* ── Radio Group ──────────────────────────────────────────── */
.sc-radio-group { display:flex; gap:10px; flex-wrap:wrap; }
.sc-radio-opt {
    display:flex; align-items:center; gap:7px;
    padding:8px 14px; border-radius:8px; border:1px solid var(--border);
    background:var(--bg3); cursor:pointer; transition:all var(--ease);
    font-size:13px; color:var(--t2); font-weight:600;
}
.sc-radio-opt.sc-checked {
    border-color:var(--gold); background:var(--gold-dim); color:var(--gold);
}
.sc-radio-opt input { display:none; }

/* ── Table ────────────────────────────────────────────────── */
.sc-table { width:100%; border-collapse:collapse; }
.sc-table th {
    text-align:left; padding:8px 12px; font-size:11px;
    font-weight:700; color:var(--t3); text-transform:uppercase;
    letter-spacing:.5px; border-bottom:1px solid var(--border);
}
.sc-table td {
    padding:9px 12px; font-size:13px; color:var(--t1);
    border-bottom:1px solid var(--border);
}
.sc-table tr:last-child td { border-bottom:none; }
.sc-table tr:hover td { background:var(--gold-dim); }

/* ── Tag / Badge ──────────────────────────────────────────── */
.sc-tag {
    display:inline-block; padding:2px 9px; border-radius:20px;
    font-size:10.5px; font-weight:700; font-family:var(--font-m);
}
.sc-tag-teal   { background:var(--gold-dim); color:var(--gold); border:1px solid var(--gold-ring); }
.sc-tag-amber  { background:rgba(217,119,6,.12); color:#d97706; border:1px solid rgba(217,119,6,.25); }
.sc-tag-green  { background:rgba(21,128,61,.12); color:#15803d; border:1px solid rgba(21,128,61,.25); }
.sc-tag-red    { background:rgba(192,57,43,.12); color:#c0392b; border:1px solid rgba(192,57,43,.25); }
.sc-tag-gray   { background:var(--bg3); color:var(--t3); border:1px solid var(--border); }

/* ── Session List ─────────────────────────────────────────── */
.sc-sess-list { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; }
.sc-sess-pill {
    display:flex; align-items:center; gap:8px;
    padding:8px 14px; border-radius:24px; border:1px solid var(--border);
    background:var(--bg3); font-size:13px; font-weight:600;
    color:var(--t2); transition:all var(--ease);
}
.sc-sess-pill.active-sess {
    border-color:var(--gold); background:var(--gold-dim); color:var(--gold);
}
.sc-sess-pill .sc-sess-badge {
    font-size:9px; font-weight:800; font-family:var(--font-m);
    background:var(--gold); color:#fff; padding:1px 6px;
    border-radius:10px;
}
.sc-sess-pill .sc-sess-set {
    font-size:10.5px; color:var(--t3); cursor:pointer;
    text-decoration:underline;
}
.sc-sess-pill .sc-sess-set:hover { color:var(--gold); }

/* ── Grade Scale Table ────────────────────────────────────── */
.sc-gs-row { display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px; }
.sc-gs-row input { padding:7px 10px; }

/* ── Classes List ─────────────────────────────────────────── */
.sc-class-list { display:flex; flex-direction:column; gap:6px; }
.sc-class-row {
    display:grid; grid-template-columns:auto 1fr auto auto auto auto;
    gap:10px; align-items:center; padding:8px 12px;
    background:var(--bg3); border-radius:8px; border:1px solid var(--border);
}
.sc-class-row.deleted-row {
    opacity:0.5; border-style:dashed;
}
.sc-class-row input[type=text] {
    padding:6px 10px; font-size:13px; font-family:var(--font-b);
    background:var(--bg2); color:var(--t1);
    border:1px solid var(--border); border-radius:6px; outline:none;
}
.sc-class-row input[type=text]:focus {
    border-color:var(--gold); box-shadow:0 0 0 2px var(--gold-ring);
}

/* ── Logo Preview ─────────────────────────────────────────── */
.sc-logo-wrap { display:flex; align-items:center; gap:16px; }
.sc-logo-img {
    width:80px; height:80px; border-radius:12px;
    object-fit:contain; border:2px solid var(--border); background:var(--bg3);
}
.sc-logo-placeholder {
    width:80px; height:80px; border-radius:12px;
    border:2px dashed var(--border); background:var(--bg3);
    display:flex; align-items:center; justify-content:center;
    color:var(--t3); font-size:24px;
}

/* ── Empty state ──────────────────────────────────────────── */
.sc-empty {
    text-align:center; padding:30px 20px; color:var(--t3);
    font-size:13px;
}
.sc-empty i { font-size:36px; display:block; margin-bottom:8px; }

/* ── Toast ────────────────────────────────────────────────── */
#scToast {
    position:fixed; bottom:26px; right:26px; z-index:9999;
    min-width:240px; max-width:360px; padding:12px 18px;
    border-radius:10px; font-size:13px; font-weight:600;
    display:flex; align-items:center; gap:10px;
    box-shadow:0 8px 32px rgba(0,0,0,.3);
    transform:translateY(80px); opacity:0;
    transition:transform .3s cubic-bezier(.4,0,.2,1), opacity .3s;
    pointer-events:none;
}
#scToast.show { transform:translateY(0); opacity:1; pointer-events:auto; }
#scToast.ok   { background:var(--gold); color:#fff; }
#scToast.err  { background:#c0392b; color:#fff; }

/* ── Loading spinner ──────────────────────────────────────── */
.sc-spin { display:inline-block; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>

<div class="content-wrapper">
<div class="sc-wrap">

    <!-- Page Header -->
    <div class="sc-head">
        <div class="sc-head-icon"><i class="fa fa-cogs"></i></div>
        <div>
            <div class="sc-head-title">School Configuration</div>
            <div class="sc-head-sub">Profile · Sessions · Board · Classes · Sections · Subjects · Streams</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="sc-tabs" id="scTabs">
        <button class="sc-tab active" data-tab="profile">
            <i class="fa fa-building-o"></i> Profile
        </button>
        <button class="sc-tab" data-tab="sessions">
            <i class="fa fa-calendar"></i> Sessions
        </button>
        <button class="sc-tab" data-tab="board">
            <i class="fa fa-graduation-cap"></i> Board
        </button>
        <button class="sc-tab" data-tab="classes">
            <i class="fa fa-th-large"></i> Classes
        </button>
        <button class="sc-tab" data-tab="sections">
            <i class="fa fa-columns"></i> Sections
        </button>
        <button class="sc-tab" data-tab="subjects">
            <i class="fa fa-book"></i> Subjects
        </button>
        <button class="sc-tab" data-tab="streams">
            <i class="fa fa-code-fork"></i> Streams
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Profile                                            -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane active" id="tab-profile">
        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-image"></i> School Logo</div>
            <div class="sc-logo-wrap">
                <div id="logoPreviewWrap">
                    <div class="sc-logo-placeholder" id="logoPlaceholder"><i class="fa fa-picture-o"></i></div>
                    <img id="logoImg" class="sc-logo-img" style="display:none;" alt="School Logo">
                </div>
                <div>
                    <input type="file" id="logoFile" accept="image/*" style="display:none;">
                    <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="document.getElementById('logoFile').click()">
                        <i class="fa fa-upload"></i> Upload Logo
                    </button>
                    <div style="font-size:11px;color:var(--t3);margin-top:5px;">JPG, PNG, GIF, WebP · Max 2 MB</div>
                    <div id="logoMsg" style="font-size:11.5px;margin-top:4px;"></div>
                </div>
            </div>
        </div>

        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-info-circle"></i> Basic Information</div>
            <div class="sc-grid sc-grid-2">
                <div class="sc-field">
                    <label>School Display Name</label>
                    <input type="text" id="pf_display_name" maxlength="120" placeholder="Full school name for display">
                </div>
                <div class="sc-field">
                    <label>Principal / Head</label>
                    <input type="text" id="pf_principal_name" maxlength="80" placeholder="Principal name">
                </div>
                <div class="sc-field">
                    <label>Established Year</label>
                    <input type="number" id="pf_established_year" min="1800" max="<?= date('Y') ?>" placeholder="e.g. 1995">
                </div>
                <div class="sc-field">
                    <label>Affiliation Board</label>
                    <input type="text" id="pf_affiliation_board" maxlength="80" placeholder="e.g. CBSE">
                </div>
                <div class="sc-field">
                    <label>Affiliation / DISE No.</label>
                    <input type="text" id="pf_affiliation_no" maxlength="60" placeholder="Affiliation or registration number">
                </div>
            </div>
        </div>

        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-map-marker"></i> Address &amp; Contact</div>
            <div class="sc-grid sc-grid-1" style="margin-bottom:14px;">
                <div class="sc-field">
                    <label>Street Address</label>
                    <input type="text" id="pf_address" maxlength="200" placeholder="Street / area / locality">
                </div>
            </div>
            <div class="sc-grid sc-grid-3">
                <div class="sc-field">
                    <label>City</label>
                    <input type="text" id="pf_city" maxlength="60" placeholder="City">
                </div>
                <div class="sc-field">
                    <label>State</label>
                    <input type="text" id="pf_state" maxlength="60" placeholder="State">
                </div>
                <div class="sc-field">
                    <label>Pincode</label>
                    <input type="text" id="pf_pincode" maxlength="10" placeholder="Pincode">
                </div>
                <div class="sc-field">
                    <label>Phone</label>
                    <input type="tel" id="pf_phone" maxlength="20" placeholder="+91 00000 00000">
                </div>
                <div class="sc-field">
                    <label>Email</label>
                    <input type="email" id="pf_email" maxlength="100" placeholder="contact@school.edu">
                </div>
                <div class="sc-field">
                    <label>Website</label>
                    <input type="text" id="pf_website" maxlength="100" placeholder="https://school.edu">
                </div>
            </div>
        </div>

        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-file-text-o"></i> School Documents</div>
            <div class="sc-grid sc-grid-2">
                <div class="sc-field">
                    <label>Holidays Calendar</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="file" id="docHolidays" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx" style="display:none;">
                        <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="document.getElementById('docHolidays').click()">
                            <i class="fa fa-upload"></i> Upload
                        </button>
                        <a id="docHolidaysLink" href="#" target="_blank" style="display:none;font-size:.84rem;color:var(--gold);">
                            <i class="fa fa-download"></i> View Current
                        </a>
                        <span id="docHolidaysMsg" style="font-size:.82rem;color:var(--t3);"></span>
                    </div>
                    <div style="font-size:11px;color:var(--t3);margin-top:4px;">PDF, Image, or Document · Max 5 MB</div>
                </div>
                <div class="sc-field">
                    <label>Academic Calendar</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="file" id="docAcademic" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx" style="display:none;">
                        <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="document.getElementById('docAcademic').click()">
                            <i class="fa fa-upload"></i> Upload
                        </button>
                        <a id="docAcademicLink" href="#" target="_blank" style="display:none;font-size:.84rem;color:var(--gold);">
                            <i class="fa fa-download"></i> View Current
                        </a>
                        <span id="docAcademicMsg" style="font-size:.82rem;color:var(--t3);"></span>
                    </div>
                    <div style="font-size:11px;color:var(--t3);margin-top:4px;">PDF, Image, or Document · Max 5 MB</div>
                </div>
            </div>
        </div>

        <button class="sc-btn sc-btn-primary" onclick="saveProfile()">
            <i class="fa fa-save"></i> Save Profile
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Sessions                                           -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-sessions">
        <div class="sc-card">
            <div class="sc-card-title" style="justify-content:space-between;">
                <span><i class="fa fa-calendar-check-o"></i> Academic Sessions</span>
                <button class="sc-btn sc-btn-ghost sc-btn-sm" id="syncSessBtn" onclick="syncSessions()" title="Fetch latest list directly from Firebase">
                    <i class="fa fa-refresh"></i> Sync from Firebase
                </button>
            </div>
            <div id="sessList" class="sc-sess-list"></div>
            <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <div class="sc-field" style="width:180px;">
                    <label>New Session Year</label>
                    <input type="text" id="newSessInput" placeholder="e.g. 2026-27" maxlength="7">
                </div>
                <button class="sc-btn sc-btn-primary" onclick="addSession()" style="margin-bottom:1px;">
                    <i class="fa fa-plus"></i> Add Session
                </button>
            </div>
            <div style="font-size:11px;color:var(--t3);margin-top:8px;">
                Format: <code>YYYY-YY</code> &nbsp;·&nbsp;
                Click <b>Set Active</b> to make a session the default across all modules &nbsp;·&nbsp;
                Use <b>Sync from Firebase</b> if you edited sessions directly in Firebase Console.
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Board                                              -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-board">
        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-university"></i> Board Type</div>
            <div class="sc-radio-group" id="boardTypeGroup">
                <?php foreach (['CBSE','ICSE','State','IB','Custom'] as $bt): ?>
                <label class="sc-radio-opt">
                    <input type="radio" name="board_type" value="<?= $bt ?>">
                    <?= $bt ?>
                </label>
                <?php endforeach; ?>
            </div>
            <div id="customBoardRow" style="margin-top:14px;display:none;">
                <div class="sc-field" style="max-width:360px;">
                    <label>Board Name</label>
                    <input type="text" id="customBoardName" placeholder="e.g. MP Board, Cambridge IGCSE" maxlength="80">
                </div>
            </div>
        </div>

        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-bar-chart"></i> Grading Pattern</div>
            <div class="sc-radio-group" id="gradingGroup">
                <label class="sc-radio-opt">
                    <input type="radio" name="grading_pattern" value="marks"> Marks (out of 100)
                </label>
                <label class="sc-radio-opt">
                    <input type="radio" name="grading_pattern" value="grades"> Letter Grades (A+, A, B...)
                </label>
                <label class="sc-radio-opt">
                    <input type="radio" name="grading_pattern" value="cgpa"> CGPA / GPA
                </label>
            </div>

            <div class="sc-field" style="max-width:220px;margin-top:14px;">
                <label>Minimum Passing Marks (%)</label>
                <input type="number" id="passingMarks" min="0" max="100" value="33" placeholder="33">
            </div>
        </div>

        <div class="sc-card" id="gradeScaleCard" style="display:none;">
            <div class="sc-card-title"><i class="fa fa-list-ol"></i> Grade Scale</div>
            <div id="gradeScaleRows"></div>
            <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="addGradeRow()" style="margin-top:8px;">
                <i class="fa fa-plus"></i> Add Grade
            </button>
        </div>

        <button class="sc-btn sc-btn-primary" onclick="saveBoard()">
            <i class="fa fa-save"></i> Save Board Config
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Classes                                            -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-classes">
        <div class="sc-card">
            <div class="sc-card-title" style="justify-content:space-between;">
                <span><i class="fa fa-th-large"></i> Master Class List</span>
                <label style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--t3);cursor:pointer;">
                    <input type="checkbox" id="showDeletedToggle" onchange="toggleDeletedClasses()"> Show Deleted
                </label>
            </div>
            <div style="font-size:12px;color:var(--t3);margin-bottom:12px;">
                Define all classes your school runs. The list is used for sections, subjects, and results.
                Toggle <b>Streams</b> for classes that have Science/Commerce/Arts streams (typically 11 &amp; 12).
            </div>
            <div class="sc-class-list" id="classList"></div>
            <div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;">
                <button class="sc-btn sc-btn-primary sc-btn-sm" onclick="addClassRow()">
                    <i class="fa fa-plus"></i> Add New Class
                </button>
                <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="seedDefaultClasses()">
                    <i class="fa fa-magic"></i> Seed Standard Classes (1-12 + Foundational)
                </button>
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="sc-btn sc-btn-primary" onclick="saveClasses()">
                <i class="fa fa-save"></i> Save Class List
            </button>
            <button class="sc-btn sc-btn-warning" onclick="activateClassesInSession()">
                <i class="fa fa-bolt"></i> Activate Classes in Session
            </button>
        </div>
        <div style="font-size:11px;color:var(--t3);margin-top:6px;">
            <b>Save</b> stores the master list. <b>Activate</b> creates class nodes in the active session so they appear in Manage Classes and other modules.
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Sections                                           -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-sections">
        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-columns"></i> Section Setup</div>
            <div class="sc-grid sc-grid-2" style="max-width:480px;margin-bottom:14px;">
                <div class="sc-field">
                    <label>Class</label>
                    <select id="secClassSel"></select>
                </div>
                <div class="sc-field">
                    <label>Session</label>
                    <select id="secSessSel"></select>
                </div>
            </div>
            <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="loadSections()">
                <i class="fa fa-refresh"></i> Load Sections
            </button>
        </div>

        <div class="sc-card" id="sectionsCard" style="display:none;">
            <div class="sc-card-title"><i class="fa fa-list"></i> Sections in <span id="sectionsClassLabel"></span></div>
            <div id="sectionsList" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;"></div>
            <div style="display:flex;gap:10px;align-items:center;">
                <div class="sc-field" style="width:100px;">
                    <label>New Section</label>
                    <input type="text" id="newSectionInput" maxlength="1"
                           placeholder="A"
                           style="text-transform:uppercase;text-align:center;font-weight:700;">
                </div>
                <button class="sc-btn sc-btn-primary sc-btn-sm" onclick="addSection()" style="margin-top:17px;">
                    <i class="fa fa-plus"></i> Add
                </button>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Subjects                                           -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-subjects">
        <div class="sc-card">
            <div class="sc-card-title"><i class="fa fa-book"></i> Subject Assignment</div>
            <div class="sc-grid sc-grid-2" style="max-width:380px;margin-bottom:14px;">
                <div class="sc-field">
                    <label>Class</label>
                    <select id="subClassSel"></select>
                </div>
            </div>
            <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="loadSubjects()">
                <i class="fa fa-refresh"></i> Load Subjects
            </button>
        </div>

        <div class="sc-card" id="subjectsCard" style="display:none;">
            <div class="sc-card-title">
                <i class="fa fa-list"></i> Subjects — <span id="subjectsClassLabel"></span>
            </div>
            <table class="sc-table" id="subjectsTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stream</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="subjectsTbody"></tbody>
            </table>

            <!-- Add Subject Form -->
            <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);">
                <div class="sc-card-title" style="margin-bottom:12px;"><i class="fa fa-plus-circle"></i> Add Subject</div>
                <div class="sc-grid sc-grid-3">
                    <div class="sc-field">
                        <label>Name *</label>
                        <input type="text" id="newSubName" placeholder="e.g. Mathematics" maxlength="80">
                    </div>
                    <div class="sc-field">
                        <label>Category</label>
                        <select id="newSubCat">
                            <option value="Core">Core</option>
                            <option value="Elective">Elective</option>
                            <option value="Additional">Additional</option>
                            <option value="Language">Language</option>
                            <option value="Vocational">Vocational</option>
                            <option value="Assessment">Assessment</option>
                        </select>
                    </div>
                    <div class="sc-field">
                        <label>Stream (optional)</label>
                        <select id="newSubStream">
                            <option value="common">Common</option>
                        </select>
                    </div>
                    <div class="sc-field">
                        <label>Code (auto-generated if blank)</label>
                        <input type="text" id="newSubCode" placeholder="e.g. 901" maxlength="20">
                    </div>
                </div>
                <button class="sc-btn sc-btn-primary sc-btn-sm" onclick="addSubject()" style="margin-top:6px;">
                    <i class="fa fa-plus"></i> Add Subject
                </button>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════ -->
    <!-- TAB: Streams                                            -->
    <!-- ════════════════════════════════════════════════════════ -->
    <div class="sc-pane" id="tab-streams">
        <div class="sc-card">
            <div class="sc-card-title" style="justify-content:space-between;">
                <span><i class="fa fa-code-fork"></i> Stream Configuration</span>
                <button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="seedStandardStreams()">
                    <i class="fa fa-magic"></i> Seed Standard Streams
                </button>
            </div>
            <div style="font-size:12px;color:var(--t3);margin-bottom:14px;">
                Streams apply to Classes 11 &amp; 12 (or any class with "Streams Enabled").
                Enable or disable streams to control which options appear in marks entry.
            </div>
            <div id="streamsList" style="margin-bottom:16px;"></div>

            <!-- Add Stream Form -->
            <div style="padding-top:14px;border-top:1px solid var(--border);">
                <div class="sc-card-title" style="margin-bottom:12px;font-size:13px;">
                    <i class="fa fa-plus-circle"></i> Add / Update Stream
                </div>
                <div class="sc-grid sc-grid-2" style="max-width:460px;">
                    <div class="sc-field">
                        <label>Stream Key (no spaces)</label>
                        <input type="text" id="newStreamKey" placeholder="e.g. Science" maxlength="30">
                    </div>
                    <div class="sc-field">
                        <label>Display Label</label>
                        <input type="text" id="newStreamLabel" placeholder="e.g. Science" maxlength="60">
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                    <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;color:var(--t2);">
                        <input type="checkbox" id="newStreamEnabled" checked>
                        Enabled
                    </label>
                    <button class="sc-btn sc-btn-primary sc-btn-sm" onclick="saveStream()">
                        <i class="fa fa-save"></i> Save Stream
                    </button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.sc-wrap -->
</div><!-- /.content-wrapper -->

<div id="scToast"></div>

<script>
(function () {
'use strict';

var BASE = '<?= base_url() ?>';
var CSRFN = '<?= $this->security->get_csrf_token_name() ?>';
var CSRFT = '<?= $this->security->get_csrf_hash() ?>';
var currentSession = '<?= htmlspecialchars($session_year ?? '', ENT_QUOTES, 'UTF-8') ?>';

/* ── Toast ─────────────────────────────────────────────────── */
var toastTimer;
function toast(msg, ok) {
    var el = document.getElementById('scToast');
    el.textContent = msg;
    el.className   = 'show ' + (ok === false ? 'err' : 'ok');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.className = ''; }, 3500);
}

/* ── Tab switching ──────────────────────────────────────────── */
document.getElementById('scTabs').addEventListener('click', function(e) {
    var btn = e.target.closest('.sc-tab');
    if (!btn) return;
    document.querySelectorAll('.sc-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.sc-pane').forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');
    var pane = document.getElementById('tab-' + btn.dataset.tab);
    if (pane) pane.classList.add('active');

    if (btn.dataset.tab === 'sessions') {
        syncSessions();
    }
});

/* ── POST helper ────────────────────────────────────────────── */
function post(url, data, cb) {
    data[CSRFN] = CSRFT;
    var body = Object.keys(data).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
    }).join('&');

    fetch(BASE + url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': CSRFT,
        },
        body: body,
    })
    .then(function(r) { return r.json(); })
    .then(function(d) { cb(d); })
    .catch(function(e) { cb({ status: 'error', message: 'Network error.' }); });
}

/* ── Load all config on init ────────────────────────────────── */
var CFG = {};
function loadConfig() {
    post('school_config/get_config', {}, function(d) {
        if (d.status !== 'success') { toast('Failed to load config.', false); return; }
        CFG = d;
        CSRFT = d.csrf_token || CSRFT;
        renderProfile(d.profile || {});
        renderSessions(d.sessions || [], d.active_session || '');
        renderBoard(d.board || {});
        renderClasses(d.classes || []);
        renderClassSelects(d.classes || [], d.sessions || [], d.active_session || '');
        renderStreams(d.streams || {});
        populateStreamDropdown(d.streams || {});
    });
}

/* ══════════ PROFILE ══════════ */
function renderProfile(p) {
    var fields = ['display_name','principal_name','established_year','affiliation_board',
                  'affiliation_no','address','city','state','pincode','phone','email','website'];
    fields.forEach(function(f) {
        var el = document.getElementById('pf_' + f);
        if (el) el.value = p[f] || '';
    });
    if (p.logo_url) {
        document.getElementById('logoImg').src = p.logo_url;
        document.getElementById('logoImg').style.display = 'block';
        document.getElementById('logoPlaceholder').style.display = 'none';
    }
    // Document links
    var hLink = document.getElementById('docHolidaysLink');
    var aLink = document.getElementById('docAcademicLink');
    if (p.holidays_calendar) { hLink.href = p.holidays_calendar; hLink.style.display = 'inline'; }
    else { hLink.style.display = 'none'; }
    if (p.academic_calendar) { aLink.href = p.academic_calendar; aLink.style.display = 'inline'; }
    else { aLink.style.display = 'none'; }
}

function saveProfile() {
    var data = {};
    var fields = ['display_name','principal_name','established_year','affiliation_board',
                  'affiliation_no','address','city','state','pincode','phone','email','website'];
    fields.forEach(function(f) {
        var el = document.getElementById('pf_' + f);
        if (el && el.value.trim()) data[f] = el.value.trim();
    });
    post('school_config/save_profile', data, function(d) {
        toast(d.message || (d.status === 'success' ? 'Saved!' : d.message), d.status === 'success');
    });
}

/* Logo upload */
document.getElementById('logoFile').addEventListener('change', function() {
    if (!this.files.length) return;
    var fd = new FormData();
    fd.append('logo', this.files[0]);
    fd.append(CSRFN, CSRFT);
    document.getElementById('logoMsg').textContent = 'Uploading...';
    fetch(BASE + 'school_config/upload_logo', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRFT },
        body: fd,
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.status === 'success') {
            document.getElementById('logoImg').src = d.logo_url;
            document.getElementById('logoImg').style.display = 'block';
            document.getElementById('logoPlaceholder').style.display = 'none';
            document.getElementById('logoMsg').textContent = '';
            toast('Logo uploaded!');
        } else {
            document.getElementById('logoMsg').textContent = d.message;
            document.getElementById('logoMsg').style.color = '#c0392b';
            toast(d.message, false);
        }
    })
    .catch(function() { toast('Upload failed.', false); });
});

/* Document upload (Holidays / Academic Calendar) */
function uploadDoc(inputId, docType, msgId, linkId) {
    var input = document.getElementById(inputId);
    if (!input.files.length) return;
    var fd = new FormData();
    fd.append('document', input.files[0]);
    fd.append('doc_type', docType);
    fd.append(CSRFN, CSRFT);
    var msg = document.getElementById(msgId);
    msg.textContent = 'Uploading...';
    msg.style.color = 'var(--t3)';
    fetch(BASE + 'school_config/upload_document', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRFT },
        body: fd,
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.csrf_token) CSRFT = d.csrf_token;
        if (d.status === 'success') {
            msg.textContent = '';
            var link = document.getElementById(linkId);
            link.href = d.url;
            link.style.display = 'inline';
            toast(d.message);
        } else {
            msg.textContent = d.message;
            msg.style.color = '#c0392b';
            toast(d.message, false);
        }
    })
    .catch(function() { msg.textContent = 'Upload failed.'; toast('Upload failed.', false); });
}
document.getElementById('docHolidays').addEventListener('change', function() {
    uploadDoc('docHolidays', 'holidays_calendar', 'docHolidaysMsg', 'docHolidaysLink');
});
document.getElementById('docAcademic').addEventListener('change', function() {
    uploadDoc('docAcademic', 'academic_calendar', 'docAcademicMsg', 'docAcademicLink');
});

/* ══════════ SESSIONS ══════════ */
function renderSessions(sessions, active) {
    var el = document.getElementById('sessList');
    if (!sessions.length) { el.innerHTML = '<div class="sc-empty"><i class="fa fa-calendar-o"></i>No sessions yet.</div>'; return; }
    el.innerHTML = sessions.map(function(s) {
        var isActive = s === active;
        return '<div class="sc-sess-pill' + (isActive ? ' active-sess' : '') + '">'
            + '<i class="fa fa-calendar-o" style="font-size:12px;"></i>'
            + '<span>' + esc(s) + '</span>'
            + (isActive ? '<span class="sc-sess-badge">ACTIVE</span>' : '')
            + (!isActive ? '<span class="sc-sess-set" onclick="setActive(\'' + esc(s) + '\')">Set Active</span>' : '')
            + '</div>';
    }).join('');
}

window.syncSessions = function() {
    var btn = document.getElementById('syncSessBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-refresh sc-spin"></i> Syncing...'; }

    post('school_config/sync_sessions', {}, function(d) {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Sync from Firebase'; }

        if (d.status === 'success') {
            CFG.sessions = d.sessions || [];
            renderSessions(d.sessions || [], CFG.active_session || '');
            renderClassSelects(CFG.classes || [], d.sessions || [], CFG.active_session || '');
            toast(d.message || 'Sessions refreshed from Firebase.');
        } else {
            toast(d.message || 'Sync failed.', false);
        }
    });
};

window.addSession = function() {
    var val = document.getElementById('newSessInput').value.trim();
    if (!val) { toast('Enter a session year.', false); return; }
    post('school_config/add_session', { session: val }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            document.getElementById('newSessInput').value = '';
            renderSessions(d.sessions, CFG.active_session || '');
            CFG.sessions = d.sessions;
            renderClassSelects(CFG.classes || [], d.sessions, CFG.active_session || '');
        }
    });
};

window.setActive = function(sess) {
    if (!confirm('Set "' + sess + '" as the active session? This will switch the entire application to this session.')) return;
    post('school_config/set_active_session', { session: sess }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            CFG.active_session = sess;
            currentSession = sess;
            renderSessions(CFG.sessions || [], sess);
            renderClassSelects(CFG.classes || [], CFG.sessions || [], sess);
        }
    });
};

/* ══════════ BOARD ══════════ */
function renderBoard(b) {
    if (b.type) {
        document.querySelectorAll('input[name="board_type"]').forEach(function(r) {
            r.checked = (r.value === b.type);
        });
        syncRadioGroup('board_type');
        // Show custom board name input for State, IB, Custom
        if (b.type === 'State' || b.type === 'IB' || b.type === 'Custom') {
            document.getElementById('customBoardRow').style.display = 'block';
        }
    }
    // Support both old field name (state_board_name) and new (custom_board_name)
    if (b.custom_board_name || b.state_board_name) {
        document.getElementById('customBoardName').value = b.custom_board_name || b.state_board_name || '';
    }
    if (b.grading_pattern) {
        document.querySelectorAll('input[name="grading_pattern"]').forEach(function(r) {
            r.checked = (r.value === b.grading_pattern);
        });
        syncRadioGroup('grading_pattern');
        if (b.grading_pattern !== 'marks') {
            document.getElementById('gradeScaleCard').style.display = 'block';
        }
    }
    if (b.passing_marks != null) document.getElementById('passingMarks').value = b.passing_marks;
    if (b.grade_scale && Array.isArray(b.grade_scale)) {
        b.grade_scale.forEach(function(row) { addGradeRow(row.grade, row.min_pct, row.max_pct); });
    }
}

function syncRadioGroup(name) {
    document.querySelectorAll('input[name="' + name + '"]').forEach(function(r) {
        r.closest('.sc-radio-opt').classList.toggle('sc-checked', r.checked);
    });
}

document.querySelectorAll('.sc-radio-group input[type=radio]').forEach(function(r) {
    r.addEventListener('change', function() {
        syncRadioGroup(this.name);
        if (this.name === 'board_type') {
            // Show custom name for State, IB, Custom
            var showCustom = (this.value === 'State' || this.value === 'IB' || this.value === 'Custom');
            document.getElementById('customBoardRow').style.display = showCustom ? 'block' : 'none';
        }
        if (this.name === 'grading_pattern') {
            document.getElementById('gradeScaleCard').style.display =
                this.value !== 'marks' ? 'block' : 'none';
        }
    });
});

window.addGradeRow = function(grade, min, max) {
    var wrap = document.getElementById('gradeScaleRows');
    var row  = document.createElement('div');
    row.className = 'sc-gs-row';
    row.innerHTML =
        '<div class="sc-field"><label>Grade</label>'
        + '<input type="text" class="gs-grade" value="' + (grade || '') + '" placeholder="A+" maxlength="5"></div>'
        + '<div class="sc-field"><label>Min %</label>'
        + '<input type="number" class="gs-min" value="' + (min != null ? min : '') + '" placeholder="90" min="0" max="100"></div>'
        + '<div class="sc-field"><label>Max %</label>'
        + '<input type="number" class="gs-max" value="' + (max != null ? max : '') + '" placeholder="100" min="0" max="100"></div>'
        + '<button class="sc-btn sc-btn-danger sc-btn-sm" style="margin-top:17px;" onclick="this.parentElement.remove()">'
        + '<i class="fa fa-trash"></i></button>';
    wrap.appendChild(row);
};

window.saveBoard = function() {
    var type = (document.querySelector('input[name="board_type"]:checked') || {}).value || '';
    var gp   = (document.querySelector('input[name="grading_pattern"]:checked') || {}).value || '';
    if (!type || !gp) { toast('Select board type and grading pattern.', false); return; }

    var gradeScale = [];
    document.querySelectorAll('#gradeScaleRows .sc-gs-row').forEach(function(row) {
        gradeScale.push({
            grade:   row.querySelector('.gs-grade').value.trim(),
            min_pct: parseFloat(row.querySelector('.gs-min').value) || 0,
            max_pct: parseFloat(row.querySelector('.gs-max').value) || 100,
        });
    });

    post('school_config/save_board', {
        type:              type,
        custom_board_name: document.getElementById('customBoardName').value.trim(),
        grading_pattern:   gp,
        passing_marks:     document.getElementById('passingMarks').value,
        grade_scale:       JSON.stringify(gradeScale),
    }, function(d) {
        toast(d.message || (d.status === 'success' ? 'Saved!' : d.message), d.status === 'success');
    });
};

/* ══════════ CLASSES ══════════ */
var showDeleted = false;

function renderClasses(classes) {
    var wrap = document.getElementById('classList');
    var visible = classes.filter(function(c) { return showDeleted || !c.deleted; });

    if (!visible.length) {
        wrap.innerHTML = '<div class="sc-empty"><i class="fa fa-th-large"></i>No classes defined. Click "Seed Standard Classes" to get started.</div>';
        return;
    }
    wrap.innerHTML = visible.map(function(cls, i) {
        var isDeleted = cls.deleted;
        return '<div class="sc-class-row' + (isDeleted ? ' deleted-row' : '') + '" data-key="' + esc(cls.key) + '">'
            + '<span class="sc-tag ' + (isDeleted ? 'sc-tag-red' : 'sc-tag-teal') + '" style="font-size:10px;padding:2px 6px;" title="' + (isDeleted ? 'DELETED' : 'Order') + '">' + (isDeleted ? 'DEL' : (i + 1)) + '</span>'
            + '<input type="text" class="cls-label" value="' + esc(cls.label) + '" placeholder="Class label"' + (isDeleted ? ' disabled' : '') + '>'
            + '<input type="hidden" class="cls-key" value="' + esc(cls.key) + '">'
            + '<select class="cls-type" style="padding:6px 8px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--t1);font-size:12px;"' + (isDeleted ? ' disabled' : '') + '>'
            + ['foundational','primary','middle','secondary','senior'].map(function(t) {
                return '<option value="' + t + '"' + (cls.type === t ? ' selected' : '') + '>' + t + '</option>';
              }).join('')
            + '</select>'
            + '<label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--t2);cursor:pointer;" title="Enable streams (for Cl. 11-12)">'
            + '<input type="checkbox" class="cls-streams"' + (cls.streams_enabled ? ' checked' : '') + (isDeleted ? ' disabled' : '') + '> Streams'
            + '</label>'
            + (isDeleted
                ? '<button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="restoreClass(\'' + esc(cls.key) + '\')"><i class="fa fa-undo"></i> Restore</button>'
                : '<button class="sc-btn sc-btn-danger sc-btn-sm" onclick="softDeleteClass(\'' + esc(cls.key) + '\')"><i class="fa fa-trash"></i></button>')
            + '</div>';
    }).join('');
}

window.toggleDeletedClasses = function() {
    showDeleted = document.getElementById('showDeletedToggle').checked;
    renderClasses(CFG.classes || []);
};

window.addClassRow = function() {
    var wrap = document.getElementById('classList');
    var empty = wrap.querySelector('.sc-empty');
    if (empty) empty.remove();

    var row = document.createElement('div');
    row.className = 'sc-class-row';
    row.innerHTML =
        '<span class="sc-tag sc-tag-teal" style="font-size:10px;padding:2px 6px;">NEW</span>'
        + '<input type="text" class="cls-label" placeholder="e.g. Class 1st" onblur="autoFillClassKey(this)">'
        + '<input type="hidden" class="cls-key" value="">'
        + '<select class="cls-type" style="padding:6px 8px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--t1);font-size:12px;">'
        + ['foundational','primary','middle','secondary','senior'].map(function(t) {
            return '<option value="' + t + '">' + t + '</option>';
          }).join('')
        + '</select>'
        + '<label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--t2);cursor:pointer;">'
        + '<input type="checkbox" class="cls-streams"> Streams</label>'
        + '<button class="sc-btn sc-btn-danger sc-btn-sm" onclick="this.closest(\'.sc-class-row\').remove()">'
        + '<i class="fa fa-trash"></i></button>';
    wrap.appendChild(row);
    row.querySelector('.cls-label').focus();
};

/* Auto-derive key from label for new class rows */
window.autoFillClassKey = function(input) {
    var row = input.closest('.sc-class-row');
    var keyInput = row.querySelector('.cls-key');
    if (keyInput.value) return; // already has a key

    var label = input.value.trim();
    if (!label) return;

    // Try to extract number: "Class 9th" → "9", "Nursery" → "Nursery"
    var numMatch = label.match(/(\d+)/);
    if (numMatch) {
        keyInput.value = numMatch[1];
    } else {
        // Foundational: use name directly
        var clean = label.replace(/^class\s*/i, '').trim();
        keyInput.value = clean.replace(/[^A-Za-z0-9_]/g, '');
    }
};

var DEFAULT_CLASSES = [
    { key:'Playgroup', label:'Playgroup',  type:'foundational', streams_enabled:false, deleted:false },
    { key:'Nursery',   label:'Nursery',    type:'foundational', streams_enabled:false, deleted:false },
    { key:'LKG',       label:'LKG',        type:'foundational', streams_enabled:false, deleted:false },
    { key:'UKG',       label:'UKG',        type:'foundational', streams_enabled:false, deleted:false },
    { key:'1',  label:'Class 1st',  type:'primary',   streams_enabled:false, deleted:false },
    { key:'2',  label:'Class 2nd',  type:'primary',   streams_enabled:false, deleted:false },
    { key:'3',  label:'Class 3rd',  type:'primary',   streams_enabled:false, deleted:false },
    { key:'4',  label:'Class 4th',  type:'primary',   streams_enabled:false, deleted:false },
    { key:'5',  label:'Class 5th',  type:'primary',   streams_enabled:false, deleted:false },
    { key:'6',  label:'Class 6th',  type:'middle',    streams_enabled:false, deleted:false },
    { key:'7',  label:'Class 7th',  type:'middle',    streams_enabled:false, deleted:false },
    { key:'8',  label:'Class 8th',  type:'middle',    streams_enabled:false, deleted:false },
    { key:'9',  label:'Class 9th',  type:'secondary', streams_enabled:false, deleted:false },
    { key:'10', label:'Class 10th', type:'secondary', streams_enabled:false, deleted:false },
    { key:'11', label:'Class 11th', type:'senior',    streams_enabled:true,  deleted:false },
    { key:'12', label:'Class 12th', type:'senior',    streams_enabled:true,  deleted:false },
];

window.seedDefaultClasses = function() {
    if (!confirm('This will replace the current class list with the standard list (1-12 + Foundational). Continue?')) return;
    CFG.classes = DEFAULT_CLASSES.slice();
    renderClasses(CFG.classes);
    toast('Standard classes loaded. Click "Save Class List" to persist.');
};

window.saveClasses = function() {
    // Collect from DOM (non-deleted visible rows) + keep deleted from CFG
    var rows = document.querySelectorAll('#classList .sc-class-row');
    var classes = [];
    var seenKeys = {};

    rows.forEach(function(row, i) {
        var label = row.querySelector('.cls-label').value.trim();
        var key   = row.querySelector('.cls-key').value.trim() || label.replace(/\s+/g,'_').replace(/[^A-Za-z0-9_]/g,'');
        var type  = row.querySelector('.cls-type').value;
        var streams = row.querySelector('.cls-streams').checked;
        var isDeleted = row.classList.contains('deleted-row');
        if (label && key) {
            seenKeys[key] = true;
            classes.push({ key: key, label: label, type: type, order: i, streams_enabled: streams, deleted: isDeleted });
        }
    });

    // Re-add hidden deleted classes that aren't shown
    if (!showDeleted && CFG.classes) {
        CFG.classes.forEach(function(c) {
            if (c.deleted && !seenKeys[c.key]) {
                classes.push(c);
            }
        });
    }

    if (!classes.length) { toast('Add at least one class.', false); return; }

    post('school_config/save_classes', { classes: JSON.stringify(classes) }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            CFG.classes = classes;
            renderClassSelects(classes, CFG.sessions || [], CFG.active_session || '');
        }
    });
};

/* Issue 7: Soft delete via server endpoint */
window.softDeleteClass = function(key) {
    if (!confirm('Soft-delete this class? It will be hidden but can be restored later. Existing student data is NOT affected.')) return;
    post('school_config/soft_delete_class', { class_key: key }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            // Update local state
            (CFG.classes || []).forEach(function(c) {
                if (c.key === key) { c.deleted = true; c.deleted_at = new Date().toISOString(); }
            });
            renderClasses(CFG.classes || []);
            renderClassSelects(CFG.classes || [], CFG.sessions || [], CFG.active_session || '');
        }
    });
};

window.restoreClass = function(key) {
    if (!confirm('Restore this class?')) return;
    post('school_config/restore_class', { class_key: key }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            (CFG.classes || []).forEach(function(c) {
                if (c.key === key) { c.deleted = false; c.deleted_at = null; }
            });
            renderClasses(CFG.classes || []);
            renderClassSelects(CFG.classes || [], CFG.sessions || [], CFG.active_session || '');
        }
    });
};

/* Issue 5: Activate classes in the current session */
window.activateClassesInSession = function() {
    var sess = CFG.active_session || currentSession;
    if (!sess) { toast('No active session. Set one first.', false); return; }
    if (!confirm('Create class nodes in session "' + sess + '" for all saved (non-deleted) classes?\n\nExisting class data will NOT be overwritten.')) return;

    post('school_config/activate_classes', { session: sess }, function(d) {
        toast(d.message, d.status === 'success');
    });
};

/* ══════════ SELECTS (shared by Sections + Subjects tabs) ══════════ */
function renderClassSelects(classes, sessions, activeSess) {
    // Filter out deleted classes for selects
    var activeClasses = classes.filter(function(c) { return !c.deleted; });

    var secSel = document.getElementById('secClassSel');
    var subSel = document.getElementById('subClassSel');
    var opts   = activeClasses.map(function(c) { return '<option value="' + esc(c.key) + '">' + esc(c.label) + '</option>'; }).join('');
    if (secSel) secSel.innerHTML = opts || '<option value="">No classes</option>';
    if (subSel) subSel.innerHTML = opts || '<option value="">No classes</option>';

    var sessSel = document.getElementById('secSessSel');
    var sessOpts = sessions.map(function(s) {
        return '<option value="' + esc(s) + '"' + (s === activeSess ? ' selected' : '') + '>' + s + '</option>';
    }).join('');
    if (sessSel) sessSel.innerHTML = sessOpts || '<option value="">No sessions</option>';
}

/* ══════════ SECTIONS ══════════ */
window.loadSections = function() {
    var classKey = document.getElementById('secClassSel').value;
    var sess     = document.getElementById('secSessSel').value;
    if (!classKey || !sess) { toast('Select class and session.', false); return; }

    post('school_config/get_sections', { class_key: classKey, session: sess }, function(d) {
        if (d.status !== 'success') { toast(d.message, false); return; }
        var card = document.getElementById('sectionsCard');
        card.style.display = 'block';
        document.getElementById('sectionsClassLabel').textContent = d.class_node + ' (' + sess + ')';
        renderSectionPills(d.sections, classKey, sess);
    });
};

function renderSectionPills(sections, classKey, sess) {
    var el = document.getElementById('sectionsList');
    if (!sections.length) {
        el.innerHTML = '<div style="color:var(--t3);font-size:12px;">No sections yet.</div>';
        return;
    }
    el.innerHTML = sections.map(function(s) {
        var letter = s.replace('Section ', '');
        return '<div style="display:flex;align-items:center;gap:6px;padding:8px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:24px;">'
            + '<i class="fa fa-columns" style="color:var(--gold);font-size:12px;"></i>'
            + '<span style="font-weight:700;font-size:14px;">' + esc(letter) + '</span>'
            + '<button class="sc-btn sc-btn-danger sc-btn-sm" style="padding:3px 8px;" onclick="deleteSection(\'' + esc(classKey) + '\',\'' + esc(letter) + '\',\'' + esc(sess) + '\')">'
            + '<i class="fa fa-trash"></i></button>'
            + '</div>';
    }).join('');
}

window.addSection = function() {
    var classKey = document.getElementById('secClassSel').value;
    var sess     = document.getElementById('secSessSel').value;
    var letter   = document.getElementById('newSectionInput').value.trim().toUpperCase();
    if (!letter) { toast('Enter a section letter (A-Z).', false); return; }
    post('school_config/save_section', { class_key: classKey, section: letter, session: sess }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            document.getElementById('newSectionInput').value = '';
            loadSections();
        }
    });
};

window.deleteSection = function(classKey, letter, sess) {
    if (!confirm('Delete Section ' + letter + '? This cannot be undone.')) return;
    post('school_config/delete_section', { class_key: classKey, section: letter, session: sess }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') loadSections();
    });
};

/* ══════════ SUBJECTS ══════════ */
window.loadSubjects = function() {
    var classKey = document.getElementById('subClassSel').value;
    if (!classKey) { toast('Select a class.', false); return; }

    post('school_config/get_subjects', { class_key: classKey }, function(d) {
        if (d.status !== 'success') { toast(d.message, false); return; }
        var card = document.getElementById('subjectsCard');
        card.style.display = 'block';
        var sel = document.getElementById('subClassSel');
        var lbl = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : classKey;
        document.getElementById('subjectsClassLabel').textContent = lbl;
        renderSubjectsTable(d.subjects, classKey);
    });
};

function renderSubjectsTable(subjects, classKey) {
    var tbody = document.getElementById('subjectsTbody');
    if (!subjects.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="sc-empty"><i class="fa fa-book"></i> No subjects yet.</td></tr>';
        return;
    }
    tbody.innerHTML = subjects.map(function(s) {
        var streamTag = (s.stream && s.stream !== 'common')
            ? '<span class="sc-tag sc-tag-amber">' + esc(s.stream) + '</span>'
            : '<span style="color:var(--t3);font-size:12px;">--</span>';
        return '<tr>'
            + '<td><span class="sc-tag sc-tag-teal">' + esc(String(s.code)) + '</span></td>'
            + '<td>' + esc(s.name) + '</td>'
            + '<td><span class="sc-tag sc-tag-green">' + esc(s.category || 'Core') + '</span></td>'
            + '<td>' + streamTag + '</td>'
            + '<td><button class="sc-btn sc-btn-danger sc-btn-sm" onclick="deleteSubject(\'' + esc(classKey) + '\',\'' + esc(String(s.code)) + '\')">'
            + '<i class="fa fa-trash"></i></button></td>'
            + '</tr>';
    }).join('');
}

window.addSubject = function() {
    var classKey = document.getElementById('subClassSel').value;
    var name     = document.getElementById('newSubName').value.trim();
    if (!classKey || !name) { toast('Class and subject name are required.', false); return; }

    post('school_config/save_subject', {
        class_key: classKey,
        name:      name,
        category:  document.getElementById('newSubCat').value,
        stream:    document.getElementById('newSubStream').value,
        code:      document.getElementById('newSubCode').value.trim(),
    }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            document.getElementById('newSubName').value = '';
            document.getElementById('newSubCode').value = '';
            loadSubjects();
        }
    });
};

window.deleteSubject = function(classKey, code) {
    if (!confirm('Delete subject ' + code + '?')) return;
    post('school_config/delete_subject', { class_key: classKey, code: code }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') loadSubjects();
    });
};

/* ══════════ STREAMS ══════════ */
function renderStreams(streams) {
    var el = document.getElementById('streamsList');
    var items = [];
    if (typeof streams === 'object') {
        Object.keys(streams).forEach(function(k) {
            var s = streams[k];
            if (s) items.push(s);
        });
    }

    if (!items.length) {
        el.innerHTML = '<div class="sc-empty"><i class="fa fa-code-fork"></i> No streams configured. Click "Seed Standard Streams" or add manually below.</div>';
        return;
    }

    el.innerHTML = items.map(function(s) {
        var key = s.key || '';
        return '<div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;margin-bottom:8px;">'
            + '<span class="sc-tag sc-tag-teal" style="min-width:80px;text-align:center;">' + esc(key) + '</span>'
            + '<span style="font-size:13px;color:var(--t1);font-weight:600;flex:1;">' + esc(s.label || key) + '</span>'
            + '<span class="sc-tag ' + (s.enabled ? 'sc-tag-green' : 'sc-tag-red') + '">' + (s.enabled ? 'Enabled' : 'Disabled') + '</span>'
            + '<button class="sc-btn sc-btn-ghost sc-btn-sm" onclick="editStream(\'' + esc(key) + '\',\'' + esc(s.label) + '\',' + (s.enabled ? 'true' : 'false') + ')">'
            + '<i class="fa fa-pencil"></i></button>'
            + '<button class="sc-btn sc-btn-danger sc-btn-sm" onclick="deleteStream(\'' + esc(key) + '\')">'
            + '<i class="fa fa-trash"></i></button>'
            + '</div>';
    }).join('');
}

/* Populate the subject stream dropdown from configured streams */
function populateStreamDropdown(streams) {
    var sel = document.getElementById('newSubStream');
    if (!sel) return;
    sel.innerHTML = '<option value="common">Common</option>';
    if (typeof streams === 'object') {
        Object.keys(streams).forEach(function(k) {
            var s = streams[k];
            if (s && s.enabled) {
                sel.innerHTML += '<option value="' + esc(s.key) + '">' + esc(s.label || s.key) + '</option>';
            }
        });
    }
}

window.editStream = function(key, label, enabled) {
    document.getElementById('newStreamKey').value   = key;
    document.getElementById('newStreamLabel').value = label;
    document.getElementById('newStreamEnabled').checked = enabled;
};

window.saveStream = function() {
    var key   = document.getElementById('newStreamKey').value.trim();
    var label = document.getElementById('newStreamLabel').value.trim();
    var en    = document.getElementById('newStreamEnabled').checked ? '1' : '0';
    if (!key || !label) { toast('Stream key and label are required.', false); return; }
    post('school_config/save_stream', { stream_key: key, label: label, enabled: en }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            document.getElementById('newStreamKey').value = '';
            document.getElementById('newStreamLabel').value = '';
            CFG.streams = CFG.streams || {};
            CFG.streams[key] = { key: key, label: label, enabled: en === '1' };
            renderStreams(CFG.streams);
            populateStreamDropdown(CFG.streams);
        }
    });
};

window.deleteStream = function(key) {
    if (!confirm('Delete stream "' + key + '"?')) return;
    post('school_config/delete_stream', { stream_key: key }, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success') {
            if (CFG.streams) delete CFG.streams[key];
            renderStreams(CFG.streams || {});
            populateStreamDropdown(CFG.streams || {});
        }
    });
};

/* Issue 6: Seed standard streams */
window.seedStandardStreams = function() {
    if (!confirm('Seed standard streams (Science, Commerce, Arts, General)?\nExisting streams will NOT be overwritten.')) return;
    post('school_config/seed_streams', {}, function(d) {
        toast(d.message, d.status === 'success');
        if (d.status === 'success' && d.streams) {
            CFG.streams = d.streams;
            renderStreams(d.streams);
            populateStreamDropdown(d.streams);
        }
    });
};

/* ── Escape HTML ─────────────────────────────────────────────── */
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Init ────────────────────────────────────────────────────── */
loadConfig();

}());
</script>
