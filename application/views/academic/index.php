<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
html{font-size:16px !important}

/* ── Layout ── */
.ac-wrap{padding:24px 22px 52px;min-height:100vh}

/* ── Header ── */
.ac-head{display:flex;align-items:center;gap:14px;padding:18px 22px;margin-bottom:22px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--r,10px);box-shadow:var(--sh)}
.ac-head-icon{width:44px;height:44px;border-radius:10px;background:var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 18px var(--gold-glow)}
.ac-head-icon i{color:#fff;font-size:18px}
.ac-head-info{flex:1}
.ac-head-title{font-size:18px;font-weight:700;color:var(--t1);font-family:var(--font-d)}
.ac-head-sub{font-size:12px;color:var(--t3);margin-top:2px}

/* ── Tabs ── */
.ac-tabs{display:flex;gap:4px;margin-bottom:22px;background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:6px;box-shadow:var(--sh);flex-wrap:wrap}
.ac-tab{padding:9px 18px;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;color:var(--t2);transition:all .15s;display:flex;align-items:center;gap:7px;font-family:var(--font-b);user-select:none}
.ac-tab:hover{background:var(--bg3);color:var(--t1)}
.ac-tab.active{background:var(--gold);color:#fff}
.ac-tab i{font-size:13px}

/* ── Panes ── */
.ac-pane{display:none}
.ac-pane.active{display:block}

/* ── Cards / Panels ── */
.ac-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;box-shadow:var(--sh);margin-bottom:18px;overflow:hidden}
.ac-card-hd{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ac-card-hd h3{margin:0;font-size:14px;font-weight:700;color:var(--t1);font-family:var(--font-b)}
.ac-card-body{padding:18px}

/* ── Form Groups ── */
.ac-fg{display:flex;flex-direction:column;gap:4px;margin-bottom:12px}
.ac-fg label{font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;font-family:var(--font-m)}
.ac-fg input,.ac-fg select,.ac-fg textarea{padding:8px 12px;border:1px solid var(--border);border-radius:6px;background:var(--bg3);color:var(--t1);font-size:13px;font-family:var(--font-b);outline:none;transition:border-color .15s}
.ac-fg input:focus,.ac-fg select:focus,.ac-fg textarea:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-ring)}
.ac-fg textarea{resize:vertical;min-height:50px}
.ac-row{display:flex;gap:12px;flex-wrap:wrap}
.ac-row .ac-fg{flex:1;min-width:140px}

/* ── Buttons ── */
.ac-btn{padding:8px 18px;border:none;border-radius:7px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:var(--font-b);transition:all .15s;display:inline-flex;align-items:center;gap:7px}
.ac-btn-p{background:var(--gold);color:#fff}
.ac-btn-p:hover{background:var(--gold2)}
.ac-btn-s{background:var(--bg3);color:var(--t2);border:1px solid var(--border)}
.ac-btn-s:hover{border-color:var(--gold);color:var(--gold)}
.ac-btn-d{background:transparent;color:#dc2626;border:1px solid #fca5a5}
.ac-btn-d:hover{background:#fee2e2}
.ac-btn:disabled{opacity:.5;cursor:not-allowed}
.ac-btn-sm{padding:5px 12px;font-size:11px}

/* ── Tables ── */
.ac-table{width:100%;border-collapse:collapse;font-size:13px}
.ac-table th{background:var(--bg3);color:var(--t3);font-family:var(--font-m);padding:9px 12px;text-align:left;border-bottom:1px solid var(--border);font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;position:sticky;top:0;z-index:1}
.ac-table td{padding:9px 12px;border-bottom:1px solid var(--border);color:var(--t1);vertical-align:middle}
.ac-table tr:last-child td{border-bottom:none}
.ac-table tr:hover td{background:var(--gold-dim)}

/* ── Badges ── */
.ac-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:10.5px;font-weight:700;letter-spacing:.2px}
.ac-badge-ns{background:var(--bg3);color:var(--t3)}
.ac-badge-ip{background:rgba(234,179,8,.12);color:#a16207}
.ac-badge-done{background:rgba(22,163,74,.12);color:#16a34a}
.ac-badge-hol{background:rgba(220,38,38,.12);color:#dc2626}
.ac-badge-exam{background:rgba(37,99,235,.12);color:#2563eb}
.ac-badge-meet{background:rgba(234,88,12,.12);color:#ea580c}
.ac-badge-event{background:rgba(15,118,110,.12);color:var(--gold)}
.ac-badge-act{background:rgba(139,92,246,.12);color:#8b5cf6}
.ac-badge-asgn{background:rgba(37,99,235,.12);color:#2563eb}
.ac-badge-comp{background:rgba(22,163,74,.12);color:#16a34a}
.ac-badge-canc{background:var(--bg3);color:var(--t3)}

/* ── Timetable Grid ── */
.ac-tt-grid{overflow-x:auto;margin-top:12px}
.ac-tt{width:100%;border-collapse:collapse;font-size:12px;min-width:700px}
.ac-tt th,.ac-tt td{border:1px solid var(--border);text-align:center;padding:6px 4px;min-width:80px}
.ac-tt th{background:var(--bg3);color:var(--t2);font-family:var(--font-m);font-size:10px;text-transform:uppercase;letter-spacing:.3px;position:sticky;top:0;z-index:2}
.ac-tt th:first-child{position:sticky;left:0;z-index:3;background:var(--bg3)}
.ac-tt th .ac-th-time{display:block;font-size:8.5px;font-weight:400;color:var(--t3);text-transform:none;letter-spacing:0;margin-top:1px}
.ac-tt td:first-child{position:sticky;left:0;z-index:1;background:var(--bg2);font-weight:700;font-size:11px;color:var(--t2);white-space:nowrap}
.ac-tt td{cursor:pointer;transition:background .12s;min-height:32px}
.ac-tt td:hover{background:var(--gold-dim)}
.ac-tt td.ac-recess{background:var(--bg3) !important;color:var(--t3);font-style:italic;cursor:default;font-size:10px}
.ac-tt td.ac-empty-cell{background:rgba(217,119,6,.04)}
.ac-tt-cell{font-size:11px;line-height:1.3;color:var(--t1);padding:2px 3px;border-radius:3px}
.ac-tt-cell small{display:block;font-size:9px;color:var(--t3)}
.ac-tt-cell.has-sub{border-left:3px solid var(--sub-color,var(--gold))}
.ac-tt tr.ac-row-incomplete td:first-child{border-left:3px solid #d97706}
/* Full-week view */
.ac-tt-week{width:100%;border-collapse:collapse;font-size:11px;min-width:500px}
.ac-tt-week th,.ac-tt-week td{border:1px solid var(--border);text-align:center;padding:5px 4px}
.ac-tt-week th{background:var(--bg3);color:var(--t2);font-family:var(--font-m);font-size:10px;text-transform:uppercase;position:sticky;top:0;z-index:2}
.ac-tt-week td{font-size:10.5px;min-width:55px}
.ac-tt-week td.ac-recess{background:var(--bg3) !important;color:var(--t3);font-size:9px;font-style:italic}
/* Print styles */
@media print{
    .ac-head,.ac-tabs,.ac-card-hd,.ac-day-tabs,.ac-pills,#ttViewToggle,#ttClassFilter,#ttFillRate,#ttLegend,button,.ac-toast,.content-header,.main-sidebar,.main-footer{display:none !important}
    .content-wrapper{margin-left:0 !important;padding:0 !important}
    .ac-wrap{padding:10px !important}
    .ac-card{border:none !important;box-shadow:none !important}
    .ac-tt,.ac-tt-week{font-size:10px !important}
    .ac-tt th,.ac-tt td,.ac-tt-week th,.ac-tt-week td{padding:3px 2px !important;border-color:#ccc !important}
    #ttPrintHeader{display:block !important;text-align:center;margin-bottom:12px}
    #ttPrintHeader h2{margin:0;font-size:16px}
    #ttPrintHeader p{margin:2px 0;font-size:11px;color:#666}
}

/* ── Calendar ── */
.ac-cal-nav{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.ac-cal-nav button{background:var(--bg3);border:1px solid var(--border);border-radius:6px;padding:6px 12px;cursor:pointer;color:var(--t2);font-size:13px}
.ac-cal-nav button:hover{border-color:var(--gold);color:var(--gold)}
.ac-cal-month{font-size:16px;font-weight:700;color:var(--t1);font-family:var(--font-d);min-width:180px;text-align:center}
.ac-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px}
.ac-cal-hd{padding:8px 4px;text-align:center;font-size:10px;font-weight:700;color:var(--t3);text-transform:uppercase;font-family:var(--font-m)}
.ac-cal-day{min-height:80px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;padding:4px 6px;cursor:pointer;transition:border-color .12s;position:relative}
.ac-cal-day:hover{border-color:var(--gold)}
.ac-cal-day.today{border-color:var(--gold);box-shadow:0 0 0 2px var(--gold-ring)}
.ac-cal-day.other{opacity:.35}
.ac-cal-day .num{font-size:11px;font-weight:700;color:var(--t2);margin-bottom:2px}
.ac-cal-dot{width:100%;padding:1px 3px;border-radius:3px;font-size:9px;font-weight:600;margin-bottom:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer}
.ac-cal-dot.holiday{background:rgba(220,38,38,.15);color:#dc2626}
.ac-cal-dot.exam{background:rgba(37,99,235,.15);color:#2563eb}
.ac-cal-dot.meeting{background:rgba(234,88,12,.15);color:#ea580c}
.ac-cal-dot.event{background:rgba(15,118,110,.15);color:var(--gold)}
.ac-cal-dot.activity{background:rgba(139,92,246,.15);color:#8b5cf6}

/* ── Progress Bar ── */
.ac-progress{height:6px;background:var(--bg3);border-radius:3px;overflow:hidden;margin:8px 0}
.ac-progress-bar{height:100%;background:var(--gold);border-radius:3px;transition:width .3s ease}

/* ── Inline Form (slide-down, no modal) ── */
.ac-inline-form{display:none;padding:16px 18px;border-top:1px solid var(--border);background:var(--bg3)}
.ac-inline-form.show{display:block}

/* ── Filter Pills ── */
.ac-pills{display:flex;gap:6px;flex-wrap:wrap}
.ac-pill{padding:4px 12px;border-radius:14px;font-size:11px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:transparent;color:var(--t2);font-family:var(--font-b)}
.ac-pill:hover{border-color:var(--gold);color:var(--gold)}
.ac-pill.active{background:var(--gold-dim);border-color:var(--gold);color:var(--gold)}

/* ── Empty State ── */
.ac-empty{text-align:center;padding:50px 20px;color:var(--t3)}
.ac-empty i{font-size:2.2rem;display:block;margin-bottom:10px;opacity:.4}

/* ── Day Tabs (Timetable) ── */
.ac-day-tabs{display:flex;gap:4px;margin-bottom:14px;flex-wrap:wrap}
.ac-day-tab{padding:6px 14px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;background:var(--bg3);color:var(--t2);border:1px solid var(--border);font-family:var(--font-m)}
.ac-day-tab:hover{border-color:var(--gold);color:var(--gold)}
.ac-day-tab.active{background:var(--gold);color:#fff;border-color:var(--gold)}

/* ── Toast ── */
.ac-toast{position:fixed;bottom:24px;right:24px;z-index:99999;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.2);transform:translateY(80px);opacity:0;transition:all .3s ease;pointer-events:none}
.ac-toast.show{transform:translateY(0);opacity:1}
.ac-toast.ok{background:#16a34a}
.ac-toast.err{background:#dc2626}

@media(max-width:767px){
    .ac-head{flex-wrap:wrap}
    .ac-tabs{gap:2px;padding:4px}
    .ac-tab{padding:7px 12px;font-size:11px}
    .ac-row{flex-direction:column}
    .ac-cal-grid{font-size:10px}
    .ac-cal-day{min-height:60px}
}
</style>

<div class="content-wrapper">
<div class="ac-wrap">

    <!-- Header -->
    <div class="ac-head">
        <div class="ac-head-icon"><i class="fa fa-university"></i></div>
        <div class="ac-head-info">
            <div class="ac-head-title">Academic Management</div>
            <div class="ac-head-sub"><?= htmlspecialchars($school_name ?? '') ?> — Session <?= htmlspecialchars($session_year ?? '') ?> — Curriculum, Calendar, Timetable & Substitutes</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="ac-tabs" id="acTabs">
        <div class="ac-tab active" data-tab="curriculum"><i class="fa fa-list-ol"></i> Curriculum</div>
        <div class="ac-tab" data-tab="calendar"><i class="fa fa-calendar"></i> Calendar</div>
        <div class="ac-tab" data-tab="timetable"><i class="fa fa-th"></i> Master Timetable</div>
        <div class="ac-tab" data-tab="substitutes"><i class="fa fa-exchange"></i> Substitutes</div>
    </div>

    <!-- ═══════════ TAB 1: CURRICULUM ═══════════ -->
    <div class="ac-pane active" id="pane-curriculum">
        <div class="ac-card">
            <div class="ac-card-hd">
                <h3><i class="fa fa-list-ol" style="color:var(--gold);margin-right:6px"></i>Curriculum Planning</h3>
                <div style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <select id="curClass" class="ac-fg" style="margin:0;padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg3);color:var(--t1);font-size:12px;min-width:160px">
                        <option value="">Select Class...</option>
                    </select>
                    <select id="curSubject" class="ac-fg" style="margin:0;padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg3);color:var(--t1);font-size:12px;min-width:140px">
                        <option value="">Select Subject...</option>
                    </select>
                    <button class="ac-btn ac-btn-p ac-btn-sm" onclick="AC.cur.load()"><i class="fa fa-refresh"></i> Load</button>
                </div>
            </div>
            <div class="ac-card-body">
                <!-- Progress -->
                <div id="curProgress" style="display:none">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                        <span style="font-size:12px;font-weight:600;color:var(--t2)">Syllabus Progress</span>
                        <span id="curProgressPct" style="font-size:12px;font-weight:700;color:var(--gold)">0%</span>
                    </div>
                    <div class="ac-progress"><div class="ac-progress-bar" id="curProgressBar" style="width:0%"></div></div>
                </div>

                <!-- Add Topic Form -->
                <div id="curAddForm" class="ac-inline-form" style="margin:12px -18px;border-top:none;border-bottom:1px solid var(--border)">
                    <div class="ac-row">
                        <div class="ac-fg"><label>Topic Title *</label><input type="text" id="curTopicTitle" placeholder="e.g. Quadratic Equations"></div>
                        <div class="ac-fg" style="max-width:120px"><label>Chapter</label><input type="text" id="curTopicChapter" placeholder="Ch. 4"></div>
                        <div class="ac-fg" style="max-width:100px"><label>Est. Periods</label><input type="number" id="curTopicPeriods" value="1" min="0"></div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px">
                        <button class="ac-btn ac-btn-p ac-btn-sm" onclick="AC.cur.addTopic()"><i class="fa fa-plus"></i> Add Topic</button>
                        <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.cur.toggleForm()">Cancel</button>
                    </div>
                </div>

                <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.cur.toggleForm()"><i class="fa fa-plus"></i> Add Topic</button>
                    <div class="ac-pills" id="curFilter" style="margin-left:auto">
                        <button class="ac-pill active" data-f="all">All</button>
                        <button class="ac-pill" data-f="not_started">Not Started</button>
                        <button class="ac-pill" data-f="in_progress">In Progress</button>
                        <button class="ac-pill" data-f="completed">Completed</button>
                    </div>
                </div>

                <div id="curTopics">
                    <div class="ac-empty"><i class="fa fa-list-ol"></i>Select a class and subject, then click Load</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 2: CALENDAR ═══════════ -->
    <div class="ac-pane" id="pane-calendar">
        <div class="ac-card">
            <div class="ac-card-hd">
                <h3><i class="fa fa-calendar" style="color:var(--gold);margin-right:6px"></i>Academic Calendar</h3>
                <button class="ac-btn ac-btn-p ac-btn-sm" style="margin-left:auto" onclick="AC.cal.showAddForm()"><i class="fa fa-plus"></i> Add Event</button>
            </div>

            <!-- Add/Edit Event Form -->
            <div id="calForm" class="ac-inline-form">
                <input type="hidden" id="calEditId" value="">
                <div class="ac-row">
                    <div class="ac-fg" style="flex:2"><label>Title *</label><input type="text" id="calTitle" placeholder="Event title"></div>
                    <div class="ac-fg"><label>Type</label>
                        <select id="calType">
                            <option value="holiday">Holiday</option>
                            <option value="exam">Exam</option>
                            <option value="meeting">Meeting</option>
                            <option value="event" selected>Event</option>
                            <option value="activity">Activity</option>
                        </select>
                    </div>
                </div>
                <div class="ac-row">
                    <div class="ac-fg"><label>Start Date *</label><input type="date" id="calStart"></div>
                    <div class="ac-fg"><label>End Date</label><input type="date" id="calEnd"></div>
                </div>
                <div class="ac-fg"><label>Description</label><textarea id="calDesc" rows="2" placeholder="Optional details..."></textarea></div>
                <div style="display:flex;gap:8px;margin-top:4px">
                    <button class="ac-btn ac-btn-p ac-btn-sm" onclick="AC.cal.saveEvent()"><i class="fa fa-check"></i> Save</button>
                    <button class="ac-btn ac-btn-d ac-btn-sm" id="calDeleteBtn" style="display:none" onclick="AC.cal.deleteEditingEvent()"><i class="fa fa-trash"></i> Delete</button>
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.cal.hideForm()">Cancel</button>
                </div>
            </div>

            <div class="ac-card-body">
                <!-- Legend -->
                <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;font-size:11px">
                    <span><span class="ac-cal-dot holiday" style="display:inline-block;width:10px;height:10px;border-radius:50%;padding:0"></span> Holiday</span>
                    <span><span class="ac-cal-dot exam" style="display:inline-block;width:10px;height:10px;border-radius:50%;padding:0"></span> Exam</span>
                    <span><span class="ac-cal-dot meeting" style="display:inline-block;width:10px;height:10px;border-radius:50%;padding:0"></span> Meeting</span>
                    <span><span class="ac-cal-dot event" style="display:inline-block;width:10px;height:10px;border-radius:50%;padding:0"></span> Event</span>
                    <span><span class="ac-cal-dot activity" style="display:inline-block;width:10px;height:10px;border-radius:50%;padding:0"></span> Activity</span>
                </div>

                <div class="ac-cal-nav">
                    <button onclick="AC.cal.prevMonth()"><i class="fa fa-chevron-left"></i></button>
                    <div class="ac-cal-month" id="calMonthLabel">March 2026</div>
                    <button onclick="AC.cal.nextMonth()"><i class="fa fa-chevron-right"></i></button>
                    <button class="ac-btn ac-btn-s ac-btn-sm" style="margin-left:auto" onclick="AC.cal.goToday()">Today</button>
                </div>

                <div class="ac-cal-grid" id="calGrid"></div>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 3: MASTER TIMETABLE ═══════════ -->
    <div class="ac-pane" id="pane-timetable">
        <div class="ac-card">
            <div class="ac-card-hd">
                <h3><i class="fa fa-th" style="color:var(--gold);margin-right:6px"></i>Master Timetable</h3>
                <div style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <!-- View Toggle -->
                    <div class="ac-pills" id="ttViewToggle">
                        <button class="ac-pill active" data-view="class"><i class="fa fa-th" style="margin-right:4px"></i>Class View</button>
                        <button class="ac-pill" data-view="teacher"><i class="fa fa-user" style="margin-right:4px"></i>Teacher View</button>
                    </div>
                    <!-- Class filter -->
                    <select id="ttClassFilter" style="padding:5px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg3);color:var(--t1);font-size:12px;min-width:120px">
                        <option value="">All Classes</option>
                    </select>
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.tt.copyDay()" title="Copy this day's timetable to another day"><i class="fa fa-copy"></i> Copy Day</button>
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.tt.printTT()" title="Print timetable"><i class="fa fa-print"></i> Print</button>
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.tt.load()"><i class="fa fa-refresh"></i> Refresh</button>
                </div>
            </div>
            <div class="ac-card-body">
                <!-- Day Tabs -->
                <div class="ac-day-tabs" id="ttDayTabs">
                    <div class="ac-day-tab active" data-day="Monday">Mon</div>
                    <div class="ac-day-tab" data-day="Tuesday">Tue</div>
                    <div class="ac-day-tab" data-day="Wednesday">Wed</div>
                    <div class="ac-day-tab" data-day="Thursday">Thu</div>
                    <div class="ac-day-tab" data-day="Friday">Fri</div>
                    <div class="ac-day-tab" data-day="Saturday">Sat</div>
                    <div class="ac-day-tab" data-day="_week" style="margin-left:8px;border-color:var(--gold);color:var(--gold)"><i class="fa fa-calendar"></i> Full Week</div>
                </div>

                <!-- Settings Summary + Fill Rate -->
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px">
                    <div id="ttSettingsSummary" style="font-size:11px;color:var(--t3)"></div>
                    <div id="ttFillRate" style="font-size:11px;font-weight:600;display:none">
                        <span id="ttFillPct" style="color:var(--gold)">0%</span>
                        <span style="color:var(--t3)">slots filled</span>
                        <span id="ttFillEmpty" style="color:#d97706;margin-left:6px"></span>
                    </div>
                </div>

                <!-- Subject Color Legend -->
                <div id="ttLegend" style="display:none;margin-bottom:12px;padding:8px 12px;background:var(--bg3);border-radius:6px;font-size:11px;display:flex;gap:10px;flex-wrap:wrap"></div>

                <!-- Print Header (visible only when printing) -->
                <div id="ttPrintHeader" style="display:none">
                    <h2><?= htmlspecialchars($school_name ?? '') ?> — Master Timetable</h2>
                    <p>Session: <?= htmlspecialchars($session_year ?? '') ?> | <span id="ttPrintDay"></span></p>
                </div>

                <!-- Grid -->
                <div class="ac-tt-grid" id="ttGridWrap">
                    <div class="ac-empty"><i class="fa fa-th"></i>Loading timetable...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 4: SUBSTITUTES ═══════════ -->
    <div class="ac-pane" id="pane-substitutes">
        <div class="ac-card">
            <div class="ac-card-hd">
                <h3><i class="fa fa-exchange" style="color:var(--gold);margin-right:6px"></i>Substitute Teachers</h3>
                <div style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <input type="date" id="subDateFilter" style="padding:5px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg3);color:var(--t1);font-size:12px">
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.sub.load()"><i class="fa fa-refresh"></i> Load</button>
                    <button class="ac-btn ac-btn-p ac-btn-sm" onclick="AC.sub.showForm()"><i class="fa fa-plus"></i> Assign Substitute</button>
                </div>
            </div>

            <!-- Add Form -->
            <div id="subForm" class="ac-inline-form">
                <input type="hidden" id="subEditId" value="">
                <div class="ac-row">
                    <div class="ac-fg"><label>Start Date *</label><input type="date" id="subDate"></div>
                    <div class="ac-fg"><label>End Date <span style="color:var(--t3);font-size:11px">(leave blank for single day)</span></label><input type="date" id="subDateEnd"></div>
                    <div class="ac-fg"><label>Absent Teacher *</label>
                        <select id="subAbsent"><option value="">Select teacher...</option></select>
                    </div>
                </div>
                <div class="ac-row">
                    <div class="ac-fg"><label>Substitute Teacher *</label>
                        <select id="subTeacher"><option value="">Select teacher...</option></select>
                    </div>
                    <div class="ac-fg"><label>Class / Section *</label>
                        <select id="subClass"><option value="">Select class...</option></select>
                    </div>
                    <div class="ac-fg"><label>Subject</label>
                        <select id="subSubject"><option value="">Select class first...</option></select>
                    </div>
                </div>
                <div class="ac-row">
                    <div class="ac-fg"><label>Periods (comma-separated) *</label><input type="text" id="subPeriods" placeholder="e.g. 3,4,5"></div>
                    <div class="ac-fg"><label>Reason</label><input type="text" id="subReason" placeholder="e.g. Medical leave"></div>
                </div>
                <!-- Teacher availability indicator -->
                <div id="subAvailability" style="display:none;padding:8px 12px;border-radius:6px;margin-bottom:8px;font-size:12px"></div>
                <div style="display:flex;gap:8px;margin-top:4px">
                    <button class="ac-btn ac-btn-p ac-btn-sm" onclick="AC.sub.save()"><i class="fa fa-check"></i> Save</button>
                    <button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.sub.hideForm()">Cancel</button>
                </div>
            </div>

            <div class="ac-card-body">
                <div id="subList">
                    <div class="ac-empty"><i class="fa fa-exchange"></i>Select a date and click Load, or view all</div>
                </div>
            </div>
        </div>
    </div>

</div><!-- .ac-wrap -->
</div><!-- .content-wrapper -->

<!-- Toast -->
<div class="ac-toast" id="acToast"></div>

<script>
var BASE = '<?= base_url() ?>';
var CSRF_NAME  = '<?= $this->security->get_csrf_token_name() ?>';
var CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';

/* ── Helpers ── */
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML}
function post(url,params){
    params = params || {};
    params[CSRF_NAME] = CSRF_TOKEN;
    return fetch(BASE+url,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body:new URLSearchParams(params).toString(),
        credentials:'include'
    }).then(function(r){return r.json()}).then(function(d){
        if(d.csrf_token) CSRF_TOKEN=d.csrf_token;
        return d;
    });
}
function toast(msg,ok){
    var t=document.getElementById('acToast');
    t.textContent=msg;t.className='ac-toast '+(ok?'ok':'err')+' show';
    setTimeout(function(){t.classList.remove('show')},2800);
}

/* ── Tab Switching ── */
document.getElementById('acTabs').addEventListener('click',function(e){
    var tab=e.target.closest('.ac-tab');
    if(!tab)return;
    document.querySelectorAll('.ac-tab').forEach(function(t){t.classList.remove('active')});
    document.querySelectorAll('.ac-pane').forEach(function(p){p.classList.remove('active')});
    tab.classList.add('active');
    document.getElementById('pane-'+tab.dataset.tab).classList.add('active');
    // Lazy-load on first visit
    var k=tab.dataset.tab;
    if(k==='calendar' && !AC.cal._loaded) AC.cal.init();
    if(k==='timetable' && !AC.tt._loaded) AC.tt.load();
    if(k==='substitutes' && !AC.sub._loaded) AC.sub.init();
});

/* ── Shared Data Cache ── */
var _classes=[], _subjects={}, _teachers=[];
function loadSharedData(cb){
    if(_classes.length>0) return cb();
    post('academic/get_classes_subjects').then(function(d){
        if(d.status==='success'){
            _classes=d.classes||[];
            _subjects=d.subjects||{};
        }
        cb();
    });
}

/* ══════════════════════════════════════════════════════════════
   CURRICULUM
══════════════════════════════════════════════════════════════ */
var AC = {};
AC.cur = {
    topics: [],
    filter: 'all',
    init: function(){
        loadSharedData(function(){
            var sel=document.getElementById('curClass');
            sel.innerHTML='<option value="">Select Class...</option>';
            _classes.forEach(function(c){
                sel.innerHTML+='<option value="'+esc(c.class_section)+'" data-key="'+esc(c.class_key)+'">'+esc(c.label)+'</option>';
            });
            sel.onchange=AC.cur.onClassChange;
        });
        // Filter pills
        document.getElementById('curFilter').addEventListener('click',function(e){
            var pill=e.target.closest('.ac-pill');
            if(!pill)return;
            document.querySelectorAll('#curFilter .ac-pill').forEach(function(p){p.classList.remove('active')});
            pill.classList.add('active');
            AC.cur.filter=pill.dataset.f;
            AC.cur.render();
        });
    },
    onClassChange: function(){
        var cs=document.getElementById('curClass');
        var opt=cs.options[cs.selectedIndex];
        var classKey=opt?opt.dataset.key:'';
        var subSel=document.getElementById('curSubject');
        subSel.innerHTML='<option value="">Select Subject...</option>';
        if(!classKey)return;
        // Extract class num
        var m=classKey.match(/(\d+)/);
        var num=m?m[1]:'';
        var subs=_subjects[num]||[];
        subs.forEach(function(s){
            var label=s.name+(s.code&&s.code!==s.name?' ('+s.code+')':'');
            subSel.innerHTML+='<option value="'+esc(s.name)+'">'+esc(label)+'</option>';
        });
        // Also try non-numeric keys (Nursery, LKG etc)
        var nameKey=classKey.replace('Class ','');
        var subs2=_subjects[nameKey]||[];
        subs2.forEach(function(s){
            if(subs.some(function(x){return x.name===s.name}))return;
            var label=s.name+(s.code&&s.code!==s.name?' ('+s.code+')':'');
            subSel.innerHTML+='<option value="'+esc(s.name)+'">'+esc(label)+'</option>';
        });
    },
    load: function(){
        var cs=document.getElementById('curClass').value;
        var sub=document.getElementById('curSubject').value;
        if(!cs||!sub){toast('Select class and subject first',false);return}
        post('academic/get_curriculum',{class_section:cs,subject:sub}).then(function(d){
            if(d.status==='success'){
                AC.cur.topics=d.topics||[];
                AC.cur.render();
                toast('Loaded '+AC.cur.topics.length+' topics',true);
            } else toast(d.message,false);
        });
    },
    render: function(){
        var t=AC.cur.topics;
        var f=AC.cur.filter;
        // Progress
        var total=t.length, done=t.filter(function(x){return x.status==='completed'}).length;
        var pct=total>0?Math.round(done/total*100):0;
        var pg=document.getElementById('curProgress');
        pg.style.display=total>0?'block':'none';
        document.getElementById('curProgressPct').textContent=pct+'%';
        document.getElementById('curProgressBar').style.width=pct+'%';

        if(total===0){
            document.getElementById('curTopics').innerHTML='<div class="ac-empty"><i class="fa fa-list-ol"></i>No topics yet. Click "Add Topic" to start planning.</div>';
            return;
        }
        var html='<table class="ac-table"><thead><tr><th>#</th><th>Topic</th><th>Chapter</th><th>Periods</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        t.forEach(function(topic,i){
            if(f!=='all' && topic.status!==f) return;
            var badge='ac-badge-ns',lbl='Not Started';
            if(topic.status==='in_progress'){badge='ac-badge-ip';lbl='In Progress'}
            if(topic.status==='completed'){badge='ac-badge-done';lbl='Completed'}
            html+='<tr>';
            html+='<td style="color:var(--t3);font-weight:700">'+(i+1)+'</td>';
            html+='<td style="font-weight:600">'+esc(topic.title)+'</td>';
            html+='<td>'+esc(topic.chapter)+'</td>';
            html+='<td>'+((topic.est_periods||0))+'</td>';
            html+='<td><span class="ac-badge '+badge+'">'+lbl+'</span></td>';
            html+='<td style="white-space:nowrap">';
            if(topic.status!=='completed'){
                var next=topic.status==='not_started'?'in_progress':'completed';
                html+='<button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.cur.setStatus('+i+',\''+next+'\')"><i class="fa fa-arrow-right"></i></button> ';
            }
            if(topic.status==='completed'){
                html+='<button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.cur.setStatus('+i+',\'in_progress\')"><i class="fa fa-undo"></i></button> ';
            }
            html+='<button class="ac-btn ac-btn-d ac-btn-sm" onclick="AC.cur.deleteTopic('+i+')"><i class="fa fa-trash"></i></button>';
            html+='</td></tr>';
        });
        html+='</tbody></table>';
        document.getElementById('curTopics').innerHTML=html;
    },
    toggleForm: function(){
        var f=document.getElementById('curAddForm');
        f.classList.toggle('show');
        if(f.classList.contains('show')) document.getElementById('curTopicTitle').focus();
    },
    addTopic: function(){
        var title=document.getElementById('curTopicTitle').value.trim();
        if(!title){toast('Topic title required',false);return}
        AC.cur.topics.push({
            title:title,
            chapter:document.getElementById('curTopicChapter').value.trim(),
            est_periods:parseInt(document.getElementById('curTopicPeriods').value)||0,
            status:'not_started',
            completed_date:'',
            sort_order:AC.cur.topics.length
        });
        AC.cur.saveFull();
        document.getElementById('curTopicTitle').value='';
        document.getElementById('curTopicChapter').value='';
        document.getElementById('curTopicPeriods').value='1';
    },
    setStatus: function(idx,status){
        var cs=document.getElementById('curClass').value;
        var sub=document.getElementById('curSubject').value;
        post('academic/update_topic_status',{class_section:cs,subject:sub,index:idx,status:status}).then(function(d){
            if(d.status==='success'){
                AC.cur.topics[idx].status=status;
                if(status==='completed') AC.cur.topics[idx].completed_date=new Date().toISOString().slice(0,10);
                AC.cur.render();
                toast('Status updated',true);
            } else toast(d.message,false);
        });
    },
    deleteTopic: function(idx){
        if(!confirm('Delete this topic?'))return;
        var cs=document.getElementById('curClass').value;
        var sub=document.getElementById('curSubject').value;
        post('academic/delete_topic',{class_section:cs,subject:sub,index:idx}).then(function(d){
            if(d.status==='success'){
                AC.cur.topics=d.topics||[];
                AC.cur.render();
                toast('Topic deleted',true);
            } else toast(d.message,false);
        });
    },
    saveFull: function(){
        var cs=document.getElementById('curClass').value;
        var sub=document.getElementById('curSubject').value;
        if(!cs||!sub) return;
        post('academic/save_curriculum',{class_section:cs,subject:sub,topics:JSON.stringify(AC.cur.topics)}).then(function(d){
            if(d.status==='success'){
                AC.cur.topics=d.topics||[];
                AC.cur.render();
                toast('Curriculum saved',true);
            } else toast(d.message,false);
        });
    }
};

/* ══════════════════════════════════════════════════════════════
   CALENDAR
══════════════════════════════════════════════════════════════ */
AC.cal = {
    _loaded:false,
    year:new Date().getFullYear(),
    month:new Date().getMonth(),
    events:[],
    init:function(){
        AC.cal._loaded=true;
        AC.cal.loadMonth();
    },
    loadMonth:function(){
        var mm=(AC.cal.month+1).toString().padStart(2,'0');
        var ym=AC.cal.year+'-'+mm;
        document.getElementById('calMonthLabel').textContent=new Date(AC.cal.year,AC.cal.month,1).toLocaleString('en',{month:'long',year:'numeric'});
        post('academic/get_calendar_events',{month:ym}).then(function(d){
            AC.cal.events=(d.status==='success')?(d.events||[]):[];
            AC.cal.renderGrid();
        });
    },
    prevMonth:function(){AC.cal.month--;if(AC.cal.month<0){AC.cal.month=11;AC.cal.year--}AC.cal.loadMonth()},
    nextMonth:function(){AC.cal.month++;if(AC.cal.month>11){AC.cal.month=0;AC.cal.year++}AC.cal.loadMonth()},
    goToday:function(){AC.cal.year=new Date().getFullYear();AC.cal.month=new Date().getMonth();AC.cal.loadMonth()},
    renderGrid:function(){
        var y=AC.cal.year,m=AC.cal.month;
        var first=new Date(y,m,1),last=new Date(y,m+1,0);
        var startDay=first.getDay()||7; // Mon=1
        var days=last.getDate();
        var today=new Date().toISOString().slice(0,10);

        var html='';
        ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].forEach(function(d){
            html+='<div class="ac-cal-hd">'+d+'</div>';
        });

        // Prev month padding
        var prevLast=new Date(y,m,0).getDate();
        for(var i=startDay-1;i>=1;i--){
            var d=prevLast-i+1;
            html+='<div class="ac-cal-day other"><div class="num">'+d+'</div></div>';
        }

        // Current month
        for(var d=1;d<=days;d++){
            var dateStr=y+'-'+(m+1).toString().padStart(2,'0')+'-'+d.toString().padStart(2,'0');
            var isToday=dateStr===today?' today':'';
            html+='<div class="ac-cal-day'+isToday+'" data-date="'+dateStr+'" onclick="AC.cal.onDayClick(\''+dateStr+'\')">';
            html+='<div class="num">'+d+'</div>';
            // Events for this date
            AC.cal.events.forEach(function(ev){
                if(ev.start_date<=dateStr && (ev.end_date||ev.start_date)>=dateStr){
                    html+='<div class="ac-cal-dot '+esc(ev.type)+'" onclick="event.stopPropagation();AC.cal.editEvent(\''+esc(ev.id)+'\')">'+esc(ev.title)+'</div>';
                }
            });
            html+='</div>';
        }

        // Next month padding
        var totalCells=startDay-1+days;
        var remaining=totalCells%7===0?0:7-totalCells%7;
        for(var i=1;i<=remaining;i++){
            html+='<div class="ac-cal-day other"><div class="num">'+i+'</div></div>';
        }

        document.getElementById('calGrid').innerHTML=html;
    },
    onDayClick:function(date){
        document.getElementById('calEditId').value='';
        document.getElementById('calTitle').value='';
        document.getElementById('calType').value='event';
        document.getElementById('calStart').value=date;
        document.getElementById('calEnd').value=date;
        document.getElementById('calDesc').value='';
        document.getElementById('calDeleteBtn').style.display='none';
        document.getElementById('calForm').classList.add('show');
        document.getElementById('calTitle').focus();
    },
    showAddForm:function(){
        var today=new Date().toISOString().slice(0,10);
        AC.cal.onDayClick(today);
    },
    hideForm:function(){document.getElementById('calForm').classList.remove('show')},
    editEvent:function(id){
        var ev=AC.cal.events.find(function(e){return e.id===id});
        if(!ev)return;
        document.getElementById('calEditId').value=id;
        document.getElementById('calTitle').value=ev.title||'';
        document.getElementById('calType').value=ev.type||'event';
        document.getElementById('calStart').value=ev.start_date||'';
        document.getElementById('calEnd').value=ev.end_date||'';
        document.getElementById('calDesc').value=ev.description||'';
        document.getElementById('calDeleteBtn').style.display='inline-flex';
        document.getElementById('calForm').classList.add('show');
    },
    deleteEditingEvent:function(){
        var id=document.getElementById('calEditId').value;
        if(!id){toast('No event selected',false);return}
        AC.cal.deleteEvent(id);
    },
    saveEvent:function(){
        var title=document.getElementById('calTitle').value.trim();
        var start=document.getElementById('calStart').value;
        if(!title||!start){toast('Title and date required',false);return}
        post('academic/save_event',{
            id:document.getElementById('calEditId').value,
            title:title,
            type:document.getElementById('calType').value,
            start_date:start,
            end_date:document.getElementById('calEnd').value||start,
            description:document.getElementById('calDesc').value.trim()
        }).then(function(d){
            if(d.status==='success'){
                AC.cal.hideForm();
                AC.cal.loadMonth();
                toast('Event saved',true);
            } else toast(d.message,false);
        });
    },
    deleteEvent:function(id){
        if(!confirm('Delete this event?'))return;
        post('academic/delete_event',{id:id}).then(function(d){
            if(d.status==='success'){AC.cal.loadMonth();toast('Event deleted',true)}
            else toast(d.message,false);
        });
    }
};

/* ══════════════════════════════════════════════════════════════
   MASTER TIMETABLE — Production Grade
══════════════════════════════════════════════════════════════ */
AC.tt = {
    _loaded:false,
    settings:null,
    timetables:{},
    classes:[],
    day:'Monday',
    view:'class', // 'class' or 'teacher'
    filter:'',    // class filter
    _subColors:{},
    _colorPalette:['#0f766e','#2563eb','#9333ea','#ea580c','#dc2626','#0891b2','#65a30d','#c026d3','#d97706','#4f46e5','#059669','#be185d','#6d28d9','#0284c7','#ca8a04'],

    init:function(){},

    load:function(){
        AC.tt._loaded=true;
        document.getElementById('ttGridWrap').innerHTML='<div class="ac-empty"><i class="fa fa-spinner fa-spin"></i> Loading timetable data...</div>';
        loadSharedData(function(){
            post('academic/get_master_timetable').then(function(d){
                if(d.status==='success'){
                    AC.tt.settings=d.settings;
                    AC.tt.timetables=d.timetables||{};
                    AC.tt.classes=d.classes||[];
                    AC.tt._buildSubjectColors();
                    AC.tt._populateClassFilter();
                    AC.tt.renderSettings();
                    AC.tt.render();
                } else {
                    document.getElementById('ttGridWrap').innerHTML='<div class="ac-empty"><i class="fa fa-exclamation-triangle"></i> '+esc(d.message)+'</div>';
                }
            });
        });
    },

    _buildSubjectColors:function(){
        // Collect all unique subjects across all timetables
        var allSubs={};
        var labels=Object.keys(AC.tt.timetables);
        labels.forEach(function(lbl){
            var tt=AC.tt.timetables[lbl]||{};
            Object.keys(tt).forEach(function(day){
                var periods=tt[day]||[];
                periods.forEach(function(s){if(s&&s!=='')allSubs[s]=true});
            });
        });
        var names=Object.keys(allSubs).sort();
        AC.tt._subColors={};
        names.forEach(function(name,i){
            AC.tt._subColors[name]=AC.tt._colorPalette[i%AC.tt._colorPalette.length];
        });
        // Render legend
        var leg=document.getElementById('ttLegend');
        if(names.length>0){
            var h='';
            names.forEach(function(name){
                var c=AC.tt._subColors[name];
                h+='<span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:'+c+';flex-shrink:0"></span>'+esc(name)+'</span>';
            });
            leg.innerHTML=h;
            leg.style.display='flex';
        } else {
            leg.style.display='none';
        }
    },

    _populateClassFilter:function(){
        var sel=document.getElementById('ttClassFilter');
        sel.innerHTML='<option value="">All Classes</option>';
        // Extract unique class keys
        var classKeys=[];
        AC.tt.classes.forEach(function(c){
            if(classKeys.indexOf(c.class_key)===-1) classKeys.push(c.class_key);
        });
        classKeys.forEach(function(k){
            sel.innerHTML+='<option value="'+esc(k)+'">'+esc(k)+'</option>';
        });
    },

    // Calculate period time ranges from settings
    _getPeriodTimes:function(){
        var s=AC.tt.settings;
        if(!s)return[];
        // Parse start time
        var startMin=AC.tt._parseTime(s.start_time);
        var periodLen=s.length_of_period||45;
        var recMap={};
        (s.recesses||[]).forEach(function(r){recMap[r.after_period]=r.duration});

        var times=[];
        var current=startMin;
        for(var p=1;p<=s.no_of_periods;p++){
            var endMin=current+periodLen;
            times.push({start:AC.tt._fmtTime(current),end:AC.tt._fmtTime(endMin)});
            current=endMin;
            if(recMap[p]) current+=recMap[p]; // Add recess duration
        }
        return times;
    },

    _parseTime:function(str){
        if(!str)return 540; // default 9:00
        str=str.toUpperCase().trim();
        var m=str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/);
        if(!m)return 540;
        var h=parseInt(m[1]),min=parseInt(m[2]),ampm=m[3]||'';
        if(ampm==='PM'&&h!==12) h+=12;
        if(ampm==='AM'&&h===12) h=0;
        return h*60+min;
    },

    _fmtTime:function(minutes){
        var h=Math.floor(minutes/60)%24;
        var m=minutes%60;
        var ampm=h>=12?'PM':'AM';
        var h12=h%12||12;
        return h12+':'+String(m).padStart(2,'0')+ampm;
    },

    renderSettings:function(){
        var s=AC.tt.settings;
        if(!s)return;
        var rStr='';
        if(s.recesses&&s.recesses.length>0){
            rStr=s.recesses.map(function(r){return 'after P'+r.after_period+' ('+r.duration+'min)'}).join(', ');
        }
        document.getElementById('ttSettingsSummary').innerHTML=
            '<i class="fa fa-clock-o"></i> '+esc(s.start_time)+' — '+esc(s.end_time)+
            ' &nbsp;|&nbsp; '+s.no_of_periods+' periods × '+s.length_of_period+'min'+
            (rStr?' &nbsp;|&nbsp; Recess: '+esc(rStr):'');
    },

    render:function(){
        if(AC.tt.view==='teacher') AC.tt.renderTeacherView();
        else if(AC.tt.day==='_week') AC.tt.renderWeekView();
        else AC.tt.renderGrid();
    },

    renderGrid:function(){
        var s=AC.tt.settings;
        if(!s||!s.no_of_periods){
            document.getElementById('ttGridWrap').innerHTML='<div class="ac-empty"><i class="fa fa-cog"></i>No timetable settings configured. Set up periods in Classes &gt; Timetable Settings.</div>';
            document.getElementById('ttFillRate').style.display='none';
            return;
        }

        var np=s.no_of_periods;
        var recMap={};
        (s.recesses||[]).forEach(function(r){recMap[r.after_period]=r.duration});
        var day=AC.tt.day;
        var times=AC.tt._getPeriodTimes();
        var filter=AC.tt.filter;

        // Build header with time ranges
        var html='<table class="ac-tt"><thead><tr><th style="min-width:140px">Class / Section</th>';
        for(var p=1;p<=np;p++){
            var timeStr=times[p-1]?('<span class="ac-th-time">'+times[p-1].start+' — '+times[p-1].end+'</span>'):'';
            html+='<th>P'+p+timeStr+'</th>';
            if(recMap[p]) html+='<th style="width:40px;font-size:9px;color:var(--t3)">Break</th>';
        }
        html+='</tr></thead><tbody>';

        // Rows
        var labels=Object.keys(AC.tt.timetables).sort();
        var totalSlots=0,filledSlots=0,emptyClasses=[];

        if(labels.length===0){
            html+='<tr><td colspan="'+(np+1+Object.keys(recMap).length)+'" style="text-align:center;padding:30px;color:var(--t3)">No timetable data found</td></tr>';
        }

        labels.forEach(function(label){
            // Apply class filter
            var cls=AC.tt.classes.find(function(c){return c.label===label});
            if(filter && cls && cls.class_key!==filter) return;

            var classKey=cls?cls.class_key:'';
            var section=cls?cls.section:'';
            var tt=AC.tt.timetables[label]||{};
            var periods=tt[day]||[];

            var rowEmpty=0;
            for(var p=0;p<np;p++){
                totalSlots++;
                if(periods[p]&&periods[p]!=='') filledSlots++;
                else rowEmpty++;
            }
            if(rowEmpty>0) emptyClasses.push(label+' ('+rowEmpty+')');

            var rowCls=rowEmpty>0?' class="ac-row-incomplete"':'';
            html+='<tr'+rowCls+'><td>'+esc(label);
            if(rowEmpty>0) html+=' <span style="font-size:9px;color:#d97706" title="'+rowEmpty+' empty slot(s)">'+rowEmpty+' empty</span>';
            html+='</td>';

            for(var p=0;p<np;p++){
                var sub=periods[p]||'';
                var color=AC.tt._subColors[sub]||'';
                var cellCls=sub?'':' ac-empty-cell';
                html+='<td class="'+cellCls+'" onclick="AC.tt.editCell(\''+esc(classKey)+'\',\''+esc(section)+'\',\''+esc(day)+'\','+p+',this)" title="Click to edit">';
                if(sub){
                    html+='<div class="ac-tt-cell has-sub" style="--sub-color:'+color+'">'+esc(sub)+'</div>';
                } else {
                    html+='<div class="ac-tt-cell" style="color:var(--t3);font-size:10px">—</div>';
                }
                html+='</td>';
                if(recMap[p+1]) html+='<td class="ac-recess">Break</td>';
            }
            html+='</tr>';
        });

        html+='</tbody></table>';
        document.getElementById('ttGridWrap').innerHTML=html;

        // Fill rate
        var fr=document.getElementById('ttFillRate');
        if(totalSlots>0){
            var pct=Math.round(filledSlots/totalSlots*100);
            document.getElementById('ttFillPct').textContent=pct+'%';
            var emptyCount=totalSlots-filledSlots;
            document.getElementById('ttFillEmpty').textContent=emptyCount>0?emptyCount+' empty slot(s)':'All filled!';
            document.getElementById('ttFillEmpty').style.color=emptyCount>0?'#d97706':'#16a34a';
            fr.style.display='block';
        } else {
            fr.style.display='none';
        }
    },

    renderWeekView:function(){
        var s=AC.tt.settings;
        if(!s||!s.no_of_periods){
            document.getElementById('ttGridWrap').innerHTML='<div class="ac-empty"><i class="fa fa-cog"></i>No timetable settings configured.</div>';
            return;
        }

        var filter=AC.tt.filter;
        var days=['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var np=s.no_of_periods;
        var times=AC.tt._getPeriodTimes();

        // Build one table per filtered class
        var labels=Object.keys(AC.tt.timetables).sort();
        var html='';

        labels.forEach(function(label){
            var cls=AC.tt.classes.find(function(c){return c.label===label});
            if(filter && cls && cls.class_key!==filter) return;

            html+='<div style="margin-bottom:18px"><h4 style="font-size:13px;font-weight:700;color:var(--t1);margin-bottom:6px;font-family:var(--font-b)"><i class="fa fa-graduation-cap" style="color:var(--gold);margin-right:6px"></i>'+esc(label)+'</h4>';
            html+='<table class="ac-tt-week"><thead><tr><th style="width:70px">Day</th>';
            for(var p=1;p<=np;p++){
                var timeStr=times[p-1]?('<span class="ac-th-time" style="display:block;font-size:8px;font-weight:400;color:var(--t3)">'+times[p-1].start+'</span>'):'';
                html+='<th>P'+p+timeStr+'</th>';
            }
            html+='</tr></thead><tbody>';

            var tt=AC.tt.timetables[label]||{};
            days.forEach(function(day){
                var periods=tt[day]||[];
                html+='<tr><td style="font-weight:700;font-size:10px;color:var(--t2)">'+day.substr(0,3)+'</td>';
                for(var p=0;p<np;p++){
                    var sub=periods[p]||'';
                    var color=AC.tt._subColors[sub]||'';
                    if(sub){
                        html+='<td><div class="ac-tt-cell has-sub" style="--sub-color:'+color+'">'+esc(sub)+'</div></td>';
                    } else {
                        html+='<td style="color:var(--t3);font-size:9px">—</td>';
                    }
                }
                html+='</tr>';
            });
            html+='</tbody></table></div>';
        });

        if(!html) html='<div class="ac-empty"><i class="fa fa-th"></i>No data for selected filter</div>';
        document.getElementById('ttGridWrap').innerHTML=html;
        document.getElementById('ttFillRate').style.display='none';
    },

    renderTeacherView:function(){
        var s=AC.tt.settings;
        if(!s||!s.no_of_periods){
            document.getElementById('ttGridWrap').innerHTML='<div class="ac-empty"><i class="fa fa-cog"></i>No timetable settings configured.</div>';
            return;
        }

        var np=s.no_of_periods;
        var day=AC.tt.day==='_week'?'Monday':AC.tt.day;
        var times=AC.tt._getPeriodTimes();
        var recMap={};
        (s.recesses||[]).forEach(function(r){recMap[r.after_period]=r.duration});

        // Build teacher → period map by scanning all timetables
        // We don't have teacher-to-subject mapping, so we show which classes a subject appears in
        // Group by subject: subject → [{period, class}]
        var subjectMap={}; // subject → period → [class labels]
        var labels=Object.keys(AC.tt.timetables).sort();
        labels.forEach(function(label){
            var tt=AC.tt.timetables[label]||{};
            var periods=tt[day]||[];
            for(var p=0;p<np;p++){
                var sub=periods[p]||'';
                if(!sub)continue;
                if(!subjectMap[sub])subjectMap[sub]={};
                if(!subjectMap[sub][p])subjectMap[sub][p]=[];
                subjectMap[sub][p].push(label);
            }
        });

        var subjects=Object.keys(subjectMap).sort();

        // Header
        var html='<table class="ac-tt"><thead><tr><th style="min-width:140px">Subject</th>';
        for(var p=1;p<=np;p++){
            var timeStr=times[p-1]?('<span class="ac-th-time">'+times[p-1].start+' — '+times[p-1].end+'</span>'):'';
            html+='<th>P'+p+timeStr+'</th>';
            if(recMap[p]) html+='<th style="width:40px;font-size:9px;color:var(--t3)">Break</th>';
        }
        html+='</tr></thead><tbody>';

        if(subjects.length===0){
            html+='<tr><td colspan="'+(np+1+Object.keys(recMap).length)+'" style="text-align:center;padding:30px;color:var(--t3)">No timetable data for '+esc(day)+'</td></tr>';
        }

        subjects.forEach(function(sub){
            var color=AC.tt._subColors[sub]||'var(--gold)';
            html+='<tr><td style="font-weight:700"><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:'+color+';margin-right:6px"></span>'+esc(sub)+'</td>';
            for(var p=0;p<np;p++){
                var classes=subjectMap[sub][p]||[];
                if(classes.length>0){
                    html+='<td style="font-size:10px;line-height:1.4;text-align:left;padding-left:6px">';
                    classes.forEach(function(c){html+='<div>'+esc(c.replace('Class ','').replace(' / Section ',' '))+'</div>'});
                    html+='</td>';
                } else {
                    html+='<td style="color:var(--t3);font-size:10px">—</td>';
                }
                if(recMap[p+1]) html+='<td class="ac-recess">Break</td>';
            }
            html+='</tr>';
        });

        html+='</tbody></table>';
        html+='<div style="margin-top:10px;font-size:11px;color:var(--t3);font-style:italic"><i class="fa fa-info-circle"></i> Teacher View shows which classes have each subject per period. Assign class teachers in Classes module for full teacher-level scheduling.</div>';
        document.getElementById('ttGridWrap').innerHTML=html;
        document.getElementById('ttFillRate').style.display='none';
    },

    editCell:function(classKey,section,day,periodIdx,td){
        if(td.querySelector('select'))return;

        var current=td.textContent.trim();
        if(current==='—')current='';

        var m=classKey.match(/(\d+)/);
        var num=m?m[1]:'';
        var nameKey=classKey.replace('Class ','');
        var subs=(_subjects[num]||[]).concat(_subjects[nameKey]||[]);
        var seen={};
        subs=subs.filter(function(s){if(seen[s.name])return false;seen[s.name]=true;return true});

        var sel=document.createElement('select');
        sel.style.cssText='width:100%;padding:3px;font-size:11px;border:1px solid var(--gold);border-radius:4px;background:var(--bg2);color:var(--t1)';
        sel.innerHTML='<option value="">— Empty —</option>';
        subs.forEach(function(s){
            var label=s.name+(s.code&&s.code!==s.name?' ('+s.code+')':'');
            sel.innerHTML+='<option value="'+esc(s.name)+'"'+(s.name===current?' selected':'')+'>'+esc(label)+'</option>';
        });
        if(current && !subs.some(function(s){return s.name===current})){
            sel.innerHTML+='<option value="'+esc(current)+'" selected>'+esc(current)+'</option>';
        }

        td.innerHTML='';
        td.appendChild(sel);
        sel.focus();

        function save(){
            var val=sel.value;
            post('academic/save_period',{class_key:classKey,section:section,day:day,period_index:periodIdx,subject:val}).then(function(d){
                if(d.status==='success'){
                    var label=AC.tt.classes.find(function(c){return c.class_key===classKey&&c.section===section});
                    if(label){
                        var lbl=label.label;
                        if(!AC.tt.timetables[lbl])AC.tt.timetables[lbl]={};
                        if(!AC.tt.timetables[lbl][day])AC.tt.timetables[lbl][day]=[];
                        while(AC.tt.timetables[lbl][day].length<=periodIdx)AC.tt.timetables[lbl][day].push('');
                        AC.tt.timetables[lbl][day][periodIdx]=val;
                    }
                    // Re-build colors if new subject added
                    if(val&&!AC.tt._subColors[val]) AC.tt._buildSubjectColors();
                    var color=AC.tt._subColors[val]||'';
                    td.innerHTML=val?'<div class="ac-tt-cell has-sub" style="--sub-color:'+color+'">'+esc(val)+'</div>':'<div class="ac-tt-cell" style="color:var(--t3);font-size:10px">—</div>';
                    toast('Saved',true);
                } else {
                    td.innerHTML=current?'<div class="ac-tt-cell">'+esc(current)+'</div>':'<div class="ac-tt-cell" style="color:var(--t3);font-size:10px">—</div>';
                    toast(d.message,false);
                }
            });
        }

        sel.addEventListener('change',save);
        sel.addEventListener('blur',function(){
            setTimeout(function(){
                if(td.querySelector('select')){
                    td.innerHTML=current?'<div class="ac-tt-cell">'+esc(current)+'</div>':'<div class="ac-tt-cell" style="color:var(--t3);font-size:10px">—</div>';
                }
            },200);
        });
    },

    copyDay:function(){
        var fromDay=AC.tt.day;
        if(fromDay==='_week'){toast('Switch to a specific day first',false);return}
        var days=['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var opts=days.filter(function(d){return d!==fromDay}).map(function(d){return d}).join(', ');
        var toDay=prompt('Copy '+fromDay+' timetable to which day?\n\nOptions: '+opts);
        if(!toDay)return;
        toDay=toDay.trim();
        // Normalize
        toDay=toDay.charAt(0).toUpperCase()+toDay.slice(1).toLowerCase();
        if(days.indexOf(toDay)===-1){toast('Invalid day: '+toDay,false);return}
        if(toDay===fromDay){toast('Cannot copy to same day',false);return}
        if(!confirm('Copy ALL class timetables from '+fromDay+' to '+toDay+'?\nThis will overwrite '+toDay+'\'s data.'))return;

        var labels=Object.keys(AC.tt.timetables);
        var promises=[];
        labels.forEach(function(label){
            var cls=AC.tt.classes.find(function(c){return c.label===label});
            if(!cls)return;
            var tt=AC.tt.timetables[label]||{};
            var periods=tt[fromDay]||[];
            if(periods.length===0)return;
            // Save each period
            periods.forEach(function(sub,p){
                promises.push(post('academic/save_period',{
                    class_key:cls.class_key,section:cls.section,day:toDay,period_index:p,subject:sub||''
                }));
            });
            // Update local cache
            if(!AC.tt.timetables[label])AC.tt.timetables[label]={};
            AC.tt.timetables[label][toDay]=periods.slice();
        });

        Promise.all(promises).then(function(){
            toast('Copied '+fromDay+' → '+toDay+' ('+promises.length+' cells)',true);
        });
    },

    printTT:function(){
        var dayLabel=AC.tt.day==='_week'?'Full Week':AC.tt.day;
        document.getElementById('ttPrintDay').textContent='Day: '+dayLabel;
        window.print();
    }
};

// Day tab switching
document.getElementById('ttDayTabs').addEventListener('click',function(e){
    var tab=e.target.closest('.ac-day-tab');
    if(!tab)return;
    document.querySelectorAll('.ac-day-tab').forEach(function(t){t.classList.remove('active')});
    tab.classList.add('active');
    AC.tt.day=tab.dataset.day;
    if(AC.tt._loaded) AC.tt.render();
});

// View toggle (Class vs Teacher)
document.getElementById('ttViewToggle').addEventListener('click',function(e){
    var pill=e.target.closest('.ac-pill');
    if(!pill)return;
    document.querySelectorAll('#ttViewToggle .ac-pill').forEach(function(p){p.classList.remove('active')});
    pill.classList.add('active');
    AC.tt.view=pill.dataset.view;
    if(AC.tt._loaded) AC.tt.render();
});

// Class filter
document.getElementById('ttClassFilter').addEventListener('change',function(){
    AC.tt.filter=this.value;
    if(AC.tt._loaded) AC.tt.render();
});

/* ══════════════════════════════════════════════════════════════
   SUBSTITUTES
══════════════════════════════════════════════════════════════ */
AC.sub = {
    _loaded:false,
    records:[],
    _busyPeriods:[],
    _maxPeriods:6,
    init:function(){
        AC.sub._loaded=true;
        loadSharedData(function(){
            // Populate class dropdown
            var cs=document.getElementById('subClass');
            cs.innerHTML='<option value="">Select class...</option>';
            _classes.forEach(function(c){
                cs.innerHTML+='<option value="'+esc(c.class_section)+'" data-key="'+esc(c.class_key)+'">'+esc(c.label)+'</option>';
            });
            // Class change → populate subject dropdown
            cs.addEventListener('change',AC.sub.onClassChange);
            // Load teachers
            post('academic/get_all_teachers').then(function(d){
                if(d.status==='success'){
                    _teachers=d.teachers||[];
                    var abs=document.getElementById('subAbsent');
                    var sub=document.getElementById('subTeacher');
                    abs.innerHTML='<option value="">Select teacher...</option>';
                    sub.innerHTML='<option value="">Select teacher...</option>';
                    _teachers.forEach(function(t){
                        abs.innerHTML+='<option value="'+esc(t.id)+'" data-name="'+esc(t.name)+'">'+esc(t.name)+' ('+esc(t.id)+')</option>';
                        sub.innerHTML+='<option value="'+esc(t.id)+'" data-name="'+esc(t.name)+'">'+esc(t.name)+' ('+esc(t.id)+')</option>';
                    });
                }
            });
            // Substitute teacher change → check availability
            document.getElementById('subTeacher').addEventListener('change',AC.sub.checkAvailability);
            document.getElementById('subDate').addEventListener('change',AC.sub.checkAvailability);
            AC.sub.load();
        });
    },
    onClassChange:function(){
        var cs=document.getElementById('subClass');
        var opt=cs.options[cs.selectedIndex];
        var classKey=opt?opt.dataset.key:'';
        var subSel=document.getElementById('subSubject');
        subSel.innerHTML='<option value="">Select subject...</option>';
        if(!classKey)return;
        var m=classKey.match(/(\d+)/);
        var num=m?m[1]:'';
        var subs=_subjects[num]||[];
        subs.forEach(function(s){
            var label=s.name+(s.code&&s.code!==s.name?' ('+s.code+')':'');
            subSel.innerHTML+='<option value="'+esc(s.name)+'">'+esc(label)+'</option>';
        });
        var nameKey=classKey.replace('Class ','');
        var subs2=_subjects[nameKey]||[];
        subs2.forEach(function(s){
            if(subs.some(function(x){return x.name===s.name}))return;
            var label=s.name+(s.code&&s.code!==s.name?' ('+s.code+')':'');
            subSel.innerHTML+='<option value="'+esc(s.name)+'">'+esc(label)+'</option>';
        });
    },
    checkAvailability:function(){
        var teacherId=document.getElementById('subTeacher').value;
        var date=document.getElementById('subDate').value;
        var box=document.getElementById('subAvailability');
        if(!teacherId||!date){box.style.display='none';AC.sub._busyPeriods=[];return}
        post('academic/get_teacher_schedule',{teacher_id:teacherId,date:date}).then(function(d){
            if(d.status!=='success'){box.style.display='none';return}
            AC.sub._busyPeriods=d.busy_periods||[];
            AC.sub._maxPeriods=d.max_periods||6;
            if(AC.sub._busyPeriods.length>0){
                box.style.display='block';
                box.style.background='rgba(217,119,6,.12)';
                box.style.color='var(--t1)';
                box.style.border='1px solid rgba(217,119,6,.3)';
                box.innerHTML='<i class="fa fa-exclamation-triangle" style="color:#d97706;margin-right:6px"></i><strong>Busy periods:</strong> P'+AC.sub._busyPeriods.join(', P')+' (already covering another class on '+esc(d.day||date)+')';
            } else {
                box.style.display='block';
                box.style.background='rgba(15,118,110,.08)';
                box.style.color='var(--t1)';
                box.style.border='1px solid rgba(15,118,110,.2)';
                box.innerHTML='<i class="fa fa-check-circle" style="color:var(--gold);margin-right:6px"></i>Teacher is available for all '+AC.sub._maxPeriods+' periods on '+esc(d.day||date);
            }
        });
    },
    load:function(){
        var date=document.getElementById('subDateFilter').value||'';
        post('academic/get_substitutes',{date:date}).then(function(d){
            AC.sub.records=(d.status==='success')?(d.substitutes||[]):[];
            AC.sub.render();
        });
    },
    render:function(){
        var recs=AC.sub.records;
        if(recs.length===0){
            document.getElementById('subList').innerHTML='<div class="ac-empty"><i class="fa fa-exchange"></i>No substitute records found</div>';
            return;
        }
        var html='<table class="ac-table"><thead><tr><th>Date</th><th>Absent Teacher</th><th>Substitute</th><th>Class</th><th>Periods</th><th>Subject</th><th>Reason</th><th>Status</th><th>By</th><th>Actions</th></tr></thead><tbody>';
        recs.forEach(function(r){
            var badgeCls='ac-badge-asgn',lbl='Assigned';
            if(r.status==='completed'){badgeCls='ac-badge-comp';lbl='Completed'}
            if(r.status==='cancelled'){badgeCls='ac-badge-canc';lbl='Cancelled'}
            var periods=Array.isArray(r.periods)?r.periods.join(', '):'';
            // Date display: show range if multi-day
            var dateStr=esc(r.date);
            if(r.date_end && r.date_end!==r.date) dateStr+=' → '+esc(r.date_end);
            html+='<tr>';
            html+='<td style="white-space:nowrap">'+dateStr+'</td>';
            html+='<td>'+esc(r.absent_teacher_name)+'</td>';
            html+='<td>'+esc(r.substitute_teacher_name)+'</td>';
            html+='<td>'+esc(r.class_section)+'</td>';
            html+='<td>P'+esc(periods)+'</td>';
            html+='<td>'+esc(r.subject||'—')+'</td>';
            html+='<td>'+esc(r.reason||'—')+'</td>';
            html+='<td><span class="ac-badge '+badgeCls+'">'+lbl+'</span></td>';
            html+='<td style="font-size:11px;color:var(--t3)" title="Created: '+(r.created_at||'')+' by '+(r.created_by||'')+'">'+esc(r.updated_by||r.created_by||'')+'</td>';
            html+='<td style="white-space:nowrap">';
            if(r.status==='assigned'){
                html+='<button class="ac-btn ac-btn-s ac-btn-sm" onclick="AC.sub.setStatus(\''+esc(r.id)+'\',\'completed\')" title="Mark completed"><i class="fa fa-check"></i></button> ';
                html+='<button class="ac-btn ac-btn-d ac-btn-sm" onclick="AC.sub.setStatus(\''+esc(r.id)+'\',\'cancelled\')" title="Cancel"><i class="fa fa-times"></i></button> ';
            }
            html+='<button class="ac-btn ac-btn-d ac-btn-sm" onclick="AC.sub.del(\''+esc(r.id)+'\')" title="Delete"><i class="fa fa-trash"></i></button>';
            html+='</td></tr>';
        });
        html+='</tbody></table>';
        document.getElementById('subList').innerHTML=html;
    },
    showForm:function(){
        document.getElementById('subEditId').value='';
        document.getElementById('subDate').value=new Date().toISOString().slice(0,10);
        document.getElementById('subDateEnd').value='';
        document.getElementById('subAbsent').value='';
        document.getElementById('subTeacher').value='';
        document.getElementById('subClass').value='';
        document.getElementById('subSubject').innerHTML='<option value="">Select class first...</option>';
        document.getElementById('subPeriods').value='';
        document.getElementById('subReason').value='';
        document.getElementById('subAvailability').style.display='none';
        AC.sub._busyPeriods=[];
        document.getElementById('subForm').classList.add('show');
    },
    hideForm:function(){document.getElementById('subForm').classList.remove('show')},
    save:function(){
        var absEl=document.getElementById('subAbsent');
        var subEl=document.getElementById('subTeacher');
        var absOpt=absEl.options[absEl.selectedIndex];
        var subOpt=subEl.options[subEl.selectedIndex];
        var date=document.getElementById('subDate').value;
        var dateEnd=document.getElementById('subDateEnd').value||'';
        var cs=document.getElementById('subClass').value;
        if(!date||!absEl.value||!subEl.value||!cs){toast('Fill all required fields',false);return}
        if(absEl.value===subEl.value){toast('Absent and substitute cannot be the same teacher',false);return}

        var periodsStr=document.getElementById('subPeriods').value.trim();
        var periods=periodsStr?periodsStr.split(',').map(function(p){return parseInt(p.trim())}).filter(function(n){return!isNaN(n)&&n>=1}):[];
        if(periods.length===0){toast('Enter at least one valid period number',false);return}

        // Warn if assigning to busy periods
        if(AC.sub._busyPeriods.length>0){
            var overlap=periods.filter(function(p){return AC.sub._busyPeriods.indexOf(p)!==-1});
            if(overlap.length>0 && !confirm('Warning: Substitute teacher is already busy during period(s) '+overlap.join(', ')+'. Continue anyway?')) return;
        }

        post('academic/save_substitute',{
            id:document.getElementById('subEditId').value,
            date:date,
            date_end:dateEnd,
            absent_teacher_id:absEl.value,
            absent_teacher_name:absOpt?absOpt.dataset.name:'',
            substitute_teacher_id:subEl.value,
            substitute_teacher_name:subOpt?subOpt.dataset.name:'',
            class_section:cs,
            periods:JSON.stringify(periods),
            subject:document.getElementById('subSubject').value,
            reason:document.getElementById('subReason').value.trim()
        }).then(function(d){
            if(d.status==='success'){
                AC.sub.hideForm();
                AC.sub.load();
                toast('Substitute assigned',true);
            } else toast(d.message,false);
        });
    },
    setStatus:function(id,status){
        post('academic/update_substitute',{id:id,status:status}).then(function(d){
            if(d.status==='success'){AC.sub.load();toast('Status updated',true)}
            else toast(d.message,false);
        });
    },
    del:function(id){
        if(!confirm('Delete this record?'))return;
        post('academic/delete_substitute',{id:id}).then(function(d){
            if(d.status==='success'){AC.sub.load();toast('Deleted',true)}
            else toast(d.message,false);
        });
    }
};

/* ── Init on page load ── */
AC.cur.init();
</script>
