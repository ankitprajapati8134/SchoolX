<!-- Content Wrapper -->
<div class="content-wrapper db-root" id="dbRoot">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

        /* ══════════════════════════════════════════════
           BRAND CONSTANTS
        ══════════════════════════════════════════════ */
        .db-root {
            --brand:       #F5AF00;
            --brand2:      #D49700;
            --brand3:      #FFC93C;
            --brand-light: #FFD55A;
            --brand-dim:   rgba(245, 175, 0, 0.10);
            --brand-glow:  rgba(245, 175, 0, 0.22);
            --brand-ring:  rgba(245, 175, 0, 0.18);
            --blue:   #4AB5E3;
            --green:  #3DD68C;
            --rose:   #E05C6F;
            --amber:  #C9A84C;
            --r:      16px;
            --r-sm:   10px;
            --ease:   cubic-bezier(.4, 0, .2, 1);
            --font-display: 'Fraunces', serif;
            --font-body:    'DM Sans', sans-serif;
            --font-mono:    'JetBrains Mono', monospace;
        }

        /* ══════════════════════════════════════════════
           DARK THEME
        ══════════════════════════════════════════════ */
        .db-root,
        .db-root[data-theme="dark"] {
            --bg:    #0C0A06;
            --bg2:   #141109;
            --bg3:   #1C180E;
            --bg4:   #252013;
            --card:  rgba(20, 17, 9, 0.96);
            --border:  rgba(245, 175, 0, 0.07);
            --border2: rgba(245, 175, 0, 0.16);
            --text:    #EEE4C8;
            --text2:   #C2A870;
            --muted:   #7A6E50;
            --muted2:  #4E4432;
            --heading: #FFFFFF;
            --shadow:      0 4px 28px rgba(0, 0, 0, 0.55);
            --shadow-card: 0 2px 16px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(245, 175, 0, 0.055);
            --grid-line:   rgba(245, 175, 0, 0.020);
            --stat-hover:  radial-gradient(ellipse at 50% 0%, rgba(245, 175, 0, .07), transparent 65%);
            --leave-hover: rgba(245, 175, 0, 0.025);
            --cal-hover:   rgba(245, 175, 0, 0.09);
            --chart-grid:  rgba(245, 175, 0, 0.05);
            --chart-tick:  #7A6E54;
            --hero-grad:   linear-gradient(135deg, rgba(245, 175, 0, 0.055) 0%, transparent 55%);
        }

        /* ══════════════════════════════════════════════
           LIGHT THEME
        ══════════════════════════════════════════════ */
        .db-root[data-theme="light"] {
            --bg:    #F9F5EA;
            --bg2:   #F2E9CC;
            --bg3:   #ECDFB4;
            --bg4:   #E4D49A;
            --card:  rgba(255, 255, 255, 0.98);
            --border:  rgba(180, 140, 0, 0.12);
            --border2: rgba(180, 140, 0, 0.22);
            --text:    #2A2004;
            --text2:   #6B5018;
            --muted:   #9A7E44;
            --muted2:  #BFA86A;
            --heading: #140E00;
            --shadow:      0 2px 12px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 2px 16px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(180, 140, 0, 0.09);
            --grid-line:   rgba(180, 140, 0, 0.050);
            --stat-hover:  radial-gradient(ellipse at 50% 0%, rgba(245, 175, 0, .07), transparent 65%);
            --leave-hover: rgba(245, 175, 0, 0.035);
            --cal-hover:   rgba(245, 175, 0, 0.10);
            --chart-grid:  rgba(180, 140, 0, 0.08);
            --chart-tick:  #9A8050;
            --hero-grad:   linear-gradient(135deg, rgba(245, 175, 0, 0.06) 0%, transparent 55%);
        }

        /* ══════════════════════════════════════════════
           TRANSITIONS
        ══════════════════════════════════════════════ */
        .db-root.t-ready,
        .db-root.t-ready * {
            transition:
                background-color .30s var(--ease),
                background .30s var(--ease),
                border-color .30s var(--ease),
                color .30s var(--ease),
                box-shadow .30s var(--ease);
        }
        .db-root.t-ready canvas,
        .db-root.t-ready .stat-card::before,
        .db-root.t-ready .db-root::before { transition: none; }

        /* ══════════════════════════════════════════════
           RESET / BASE
        ══════════════════════════════════════════════ */
        .db-root *,
        .db-root *::before,
        .db-root *::after { box-sizing: border-box; margin: 0; padding: 0; }

        .db-root {
            font-family: var(--font-body);
            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
            position: relative;
        }

        /* Subtle grid overlay */
        .db-root::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none; z-index: 0;
        }

        /* ══════════════════════════════════════════════
           HERO / PAGE HEADER
        ══════════════════════════════════════════════ */
        .db-hero {
            position: relative; z-index: 2;
            padding: 26px 32px 22px;
            background: var(--hero-grad);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
            overflow: hidden;
        }

        /* Gold gradient top line */
        .db-hero::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--brand2) 25%, var(--brand) 50%, var(--brand3) 75%, transparent 100%);
        }

        /* Ambient gold corner glow */
        .db-hero::after {
            content: ''; position: absolute;
            top: -80px; left: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(245, 175, 0, 0.06) 0%, transparent 68%);
            pointer-events: none;
        }

        .db-hero-left { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }

        .db-school-icon {
            width: 50px; height: 50px; border-radius: 14px;
            background: var(--brand-dim);
            border: 1px solid var(--brand-ring);
            display: flex; align-items: center; justify-content: center;
            font-size: 21px; color: var(--brand); flex-shrink: 0;
        }

        .db-hero-text h1 {
            font-family: var(--font-display);
            font-size: clamp(20px, 2.5vw, 27px);
            font-weight: 700; letter-spacing: -.4px; line-height: 1.15;
            color: var(--heading);
        }
        .db-hero-text h1 em { font-style: italic; color: var(--brand); }
        .db-hero-text p {
            font-size: 12px; color: var(--muted); margin-top: 4px;
            letter-spacing: .2px;
        }
        .db-hero-text p span { color: var(--text2); }

        .db-hero-right {
            display: flex; align-items: center; gap: 10px; position: relative; z-index: 1;
        }

        .db-date-pill {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg3); border: 1px solid var(--border2);
            border-radius: 50px; padding: 7px 16px;
            font-size: 12px; color: var(--text2);
        }
        .db-date-pill .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 0 8px var(--brand);
            animation: dbPulse 2s ease infinite;
        }
        @keyframes dbPulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

        /* ══════════════════════════════════════════════
           MAIN GRID
        ══════════════════════════════════════════════ */
        .db-body {
            position: relative; z-index: 1;
            padding: 22px 32px 48px;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 18px;
        }

        /* ══════════════════════════════════════════════
           STAT CARDS
        ══════════════════════════════════════════════ */
        .db-stats {
            grid-column: 1/-1;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        @media(max-width:900px) { .db-stats { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width:540px) { .db-stats { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 22px 22px 18px;
            position: relative; overflow: hidden;
            transition: transform .22s var(--ease), box-shadow .22s var(--ease), border-color .22s;
            cursor: default;
            box-shadow: var(--shadow-card);
        }

        /* Gradient hover sheen */
        .stat-card::after {
            content: ''; position: absolute; inset: 0;
            border-radius: var(--r); opacity: 0; transition: opacity .25s;
            background: var(--stat-hover);
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card:hover::after { opacity: 1; }

        /* Bottom accent bar */
        .stat-card::before {
            content: ''; position: absolute;
            bottom: 0; left: 0; right: 0; height: 2px;
            border-radius: 0 0 var(--r) var(--r);
        }
        .stat-card.c-brand::before { background: linear-gradient(90deg, var(--brand2), var(--brand), var(--brand3)); }
        .stat-card.c-blue::before  { background: linear-gradient(90deg, var(--blue), #74C8E9); }
        .stat-card.c-rose::before  { background: linear-gradient(90deg, var(--rose), #F08095); }
        .stat-card.c-amber::before { background: linear-gradient(90deg, var(--amber), #E0C070); }

        .stat-card.c-brand:hover { border-color: var(--brand-ring); box-shadow: 0 8px 32px rgba(0,0,0,.30), 0 0 0 1px var(--brand-ring); }
        .stat-card.c-blue:hover  { border-color: rgba(74,181,227,.22); box-shadow: 0 8px 32px rgba(0,0,0,.30); }
        .stat-card.c-rose:hover  { border-color: rgba(224,92,111,.22); box-shadow: 0 8px 32px rgba(0,0,0,.30); }
        .stat-card.c-amber:hover { border-color: rgba(201,168,76,.22); box-shadow: 0 8px 32px rgba(0,0,0,.30); }

        .stat-card-top {
            display: flex; align-items: flex-start;
            justify-content: space-between; margin-bottom: 16px;
        }

        .stat-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .stat-icon.c-brand { background: var(--brand-dim);               color: var(--brand); border: 1px solid var(--brand-ring); }
        .stat-icon.c-blue  { background: rgba(74,181,227,.09);            color: var(--blue);  border: 1px solid rgba(74,181,227,.18); }
        .stat-icon.c-rose  { background: rgba(224,92,111,.09);            color: var(--rose);  border: 1px solid rgba(224,92,111,.18); }
        .stat-icon.c-amber { background: rgba(201,168,76,.09);            color: var(--amber); border: 1px solid rgba(201,168,76,.18); }

        .stat-trend {
            font-family: var(--font-mono); font-size: 10px;
            padding: 3px 8px; border-radius: 50px;
            display: flex; align-items: center; gap: 3px;
        }
        .stat-trend.up   { background: rgba(61,214,140,.10); color: var(--green); }
        .stat-trend.down { background: rgba(224,92,111,.10); color: var(--rose); }

        .stat-value {
            font-family: var(--font-display);
            font-size: 42px; font-weight: 700; line-height: 1;
            color: var(--heading); letter-spacing: -1.5px;
        }
        .stat-label {
            font-size: 11px; color: var(--muted); margin-top: 5px;
            text-transform: uppercase; letter-spacing: .8px;
        }

        .stat-footer {
            margin-top: 18px; padding-top: 13px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .stat-footer a {
            font-size: 11px; color: var(--muted); text-decoration: none;
            display: flex; align-items: center; gap: 4px; transition: color .2s;
        }
        .stat-footer a:hover { color: var(--brand); }

        .stat-bar { height: 3px; border-radius: 2px; background: var(--border); width: 72px; overflow: hidden; }
        .stat-bar-fill { height: 100%; border-radius: 2px; }
        .c-brand .stat-bar-fill { background: linear-gradient(90deg, var(--brand2), var(--brand3)); }
        .c-blue  .stat-bar-fill { background: var(--blue); }
        .c-rose  .stat-bar-fill { background: var(--rose); }
        .c-amber .stat-bar-fill { background: var(--amber); }

        /* ══════════════════════════════════════════════
           PANEL CARDS
        ══════════════════════════════════════════════ */
        .db-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 24px;
            box-shadow: var(--shadow-card);
        }

        /* Grid spans */
        .db-fee-chart  { grid-column: 1/9; }
        .db-leave      { grid-column: 9/-1; }
        .db-attendance { grid-column: 1/5; }
        .db-quick      { grid-column: 5/9; }
        .db-calendar   { grid-column: 9/-1; }

        @media(max-width:1100px) {
            .db-fee-chart, .db-leave { grid-column: 1/-1; }
        }
        @media(max-width:900px) {
            .db-attendance, .db-quick, .db-calendar { grid-column: 1/-1; }
        }
        @media(max-width:700px) {
            .db-body { padding: 16px 16px 40px; gap: 14px; }
            .db-hero { padding: 20px 16px 18px; }
        }

        /* ── Panel heading ── */
        .card-heading {
            display: flex; align-items: flex-start;
            justify-content: space-between; margin-bottom: 20px;
        }
        .card-title-txt {
            font-family: var(--font-display); font-size: 16px;
            font-weight: 600; color: var(--heading);
        }
        .card-subtitle { font-size: 11.5px; color: var(--muted); margin-top: 2px; }

        .card-badge {
            font-size: 10px; font-family: var(--font-mono);
            padding: 3px 10px; border-radius: 50px;
            background: var(--brand-dim); color: var(--brand);
            border: 1px solid var(--brand-ring);
            white-space: nowrap; flex-shrink: 0;
        }
        .card-badge.rose  { background: rgba(224,92,111,.10); color: var(--rose);  border-color: rgba(224,92,111,.20); }
        .card-badge.green { background: rgba(61,214,140,.10);  color: var(--green); border-color: rgba(61,214,140,.20); }

        /* ── Fee totals row ── */
        .fee-totals {
            display: flex; gap: 0; margin-bottom: 18px;
            background: var(--bg3); border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
        }
        .fee-total-item {
            display: flex; flex-direction: column; gap: 2px;
            padding: 14px 20px; flex: 1;
        }
        .fee-total-item + .fee-total-item { border-left: 1px solid var(--border); }
        .fee-total-num {
            font-family: var(--font-display); font-size: 21px;
            font-weight: 700; color: var(--heading);
        }
        .fee-total-lbl {
            font-size: 10px; color: var(--muted);
            text-transform: uppercase; letter-spacing: .5px;
        }

        /* ── Leave list ── */
        .leave-list { display: flex; flex-direction: column; gap: 8px; }
        .leave-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            background: var(--leave-hover);
            border: 1px solid var(--border);
            border-radius: 10px;
            transition: border-color .18s, background .18s;
        }
        .leave-item:hover {
            border-color: var(--brand-ring);
            background: var(--brand-dim);
        }
        .leave-avatar {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-family: var(--font-display); font-size: 14px; font-weight: 700;
        }
        .leave-info { flex: 1; min-width: 0; }
        .leave-name {
            font-size: 13px; font-weight: 600; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .leave-dates {
            font-size: 10.5px; color: var(--muted); margin-top: 1px;
            font-family: var(--font-mono);
        }
        .leave-badge {
            font-size: 10px; padding: 3px 9px; border-radius: 50px;
            white-space: nowrap; flex-shrink: 0;
        }
        .leave-badge.pending  { background: var(--brand-dim); color: var(--brand); border: 1px solid var(--brand-ring); }
        .leave-badge.approved { background: rgba(61,214,140,.10); color: var(--green); border: 1px solid rgba(61,214,140,.22); }

        /* ── Attendance donut ── */
        .attendance-wrap { display: flex; align-items: center; gap: 22px; }
        .attendance-canvas-wrap { position: relative; flex-shrink: 0; }
        .attendance-center {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; pointer-events: none;
        }
        .attendance-pct {
            font-family: var(--font-display); font-size: 28px;
            font-weight: 700; color: var(--heading);
        }
        .attendance-pct-sub {
            font-size: 9.5px; color: var(--muted);
            text-transform: uppercase; letter-spacing: .5px;
        }
        .attendance-legend { flex: 1; }
        .legend-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 0; border-bottom: 1px solid var(--border);
        }
        .legend-item:last-child { border-bottom: none; }
        .legend-dot { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0; }
        .legend-lbl { font-size: 12px; color: var(--muted); flex: 1; }
        .legend-val { font-family: var(--font-mono); font-size: 12px; color: var(--text); font-weight: 500; }

        /* ── Quick actions ── */
        .quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .quick-btn {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 8px;
            padding: 20px 12px; border-radius: 12px;
            text-decoration: none; font-size: 11.5px; font-weight: 600;
            letter-spacing: .3px; text-transform: uppercase;
            color: var(--text); border: 1px solid var(--border);
            transition: transform .18s var(--ease), box-shadow .18s var(--ease), border-color .18s;
            position: relative; overflow: hidden;
        }
        .quick-btn::before {
            content: ''; position: absolute; inset: 0;
            opacity: 0; transition: opacity .2s;
            background: rgba(255, 255, 255, .03);
        }
        .quick-btn:hover { transform: translateY(-3px); text-decoration: none; color: var(--text); }
        .quick-btn:hover::before { opacity: 1; }
        .quick-btn i { font-size: 24px; }

        .quick-btn.qb-brand  { background: var(--brand-dim);             border-color: var(--brand-ring); }
        .quick-btn.qb-brand2 { background: rgba(201,168,76,.08);          border-color: rgba(201,168,76,.18); }
        .quick-btn.qb-blue   { background: rgba(74,181,227,.08);           border-color: rgba(74,181,227,.18); }
        .quick-btn.qb-rose   { background: rgba(224,92,111,.08);           border-color: rgba(224,92,111,.18); }

        .quick-btn.qb-brand:hover  { border-color: var(--brand); box-shadow: 0 4px 20px rgba(245,175,0,.14); }
        .quick-btn.qb-brand2:hover { border-color: var(--amber); box-shadow: 0 4px 20px rgba(201,168,76,.14); }
        .quick-btn.qb-blue:hover   { border-color: var(--blue);  box-shadow: 0 4px 20px rgba(74,181,227,.14); }
        .quick-btn.qb-rose:hover   { border-color: var(--rose);  box-shadow: 0 4px 20px rgba(224,92,111,.14); }

        .quick-btn.qb-brand  i { color: var(--brand); }
        .quick-btn.qb-brand2 i { color: var(--amber); }
        .quick-btn.qb-blue   i { color: var(--blue); }
        .quick-btn.qb-rose   i { color: var(--rose); }

        /* ── Calendar ── */
        .mini-cal-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 14px;
        }
        .mini-cal-nav { display: flex; gap: 4px; }
        .mini-cal-nav button {
            width: 27px; height: 27px; border-radius: 6px;
            border: 1px solid var(--border); background: transparent;
            color: var(--muted); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
        }
        .mini-cal-nav button:hover {
            background: var(--bg3); color: var(--brand); border-color: var(--brand-ring);
        }
        .mini-cal-month {
            font-family: var(--font-display); font-size: 14px;
            font-weight: 600; color: var(--heading);
        }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
        .cal-day-name {
            text-align: center; font-size: 9.5px; color: var(--muted);
            text-transform: uppercase; letter-spacing: .4px;
            padding: 3px 0 7px; font-family: var(--font-mono);
        }
        .cal-day {
            aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
            font-size: 11.5px; border-radius: 8px; cursor: pointer;
            transition: all .15s; color: var(--text);
        }
        .cal-day:hover { background: var(--cal-hover); color: var(--brand); }
        .cal-day.other { color: var(--muted2); }
        .cal-day.today {
            background: var(--brand); color: #0C0A06;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(245, 175, 0, .42);
        }
        .cal-day.has-event { position: relative; }
        .cal-day.has-event::after {
            content: ''; position: absolute; bottom: 3px; left: 50%;
            transform: translateX(-50%); width: 4px; height: 4px;
            border-radius: 50%; background: var(--rose);
        }

        .event-list { margin-top: 14px; display: flex; flex-direction: column; gap: 7px; }
        .event-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 8px;
            border-left: 3px solid var(--brand); background: var(--brand-dim);
        }
        .event-item.blue { border-color: var(--blue);  background: rgba(74,181,227,.06); }
        .event-item.rose { border-color: var(--rose);  background: rgba(224,92,111,.06); }
        .event-date { font-family: var(--font-mono); font-size: 10px; color: var(--muted); min-width: 34px; }
        .event-name { font-size: 11.5px; color: var(--text); }

        /* ══════════════════════════════════════════════
           ANIMATIONS
        ══════════════════════════════════════════════ */
        @keyframes dbFadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .stat-card              { animation: dbFadeUp .45s ease both; }
        .stat-card:nth-child(1) { animation-delay: .04s; }
        .stat-card:nth-child(2) { animation-delay: .09s; }
        .stat-card:nth-child(3) { animation-delay: .14s; }
        .stat-card:nth-child(4) { animation-delay: .19s; }
        .db-fee-chart  { animation: dbFadeUp .45s .24s ease both; }
        .db-leave      { animation: dbFadeUp .45s .29s ease both; }
        .db-attendance { animation: dbFadeUp .45s .34s ease both; }
        .db-quick      { animation: dbFadeUp .45s .39s ease both; }
        .db-calendar   { animation: dbFadeUp .45s .44s ease both; }
    </style>

    <!-- ─── HERO HEADER ─── -->
    <div class="db-hero">
        <div class="db-hero-left">
            <div class="db-school-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="db-hero-text">
                <h1>Good <span id="dbGreeting">Morning</span>, <em><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></em></h1>
                <p>
                    <span><?= htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8') ?></span>
                    &nbsp;&middot;&nbsp;
                    <span>Session <?= htmlspecialchars($session_year, ENT_QUOTES, 'UTF-8') ?></span>
                    &nbsp;&middot;&nbsp; Admin Dashboard
                </p>
            </div>
        </div>
        <div class="db-hero-right">
            <div class="db-date-pill">
                <span class="dot"></span>
                <span id="dbLiveDate"></span>
            </div>
        </div>
    </div>

    <!-- ─── MAIN GRID ─── -->
    <div class="db-body">

        <!-- ── STAT CARDS ── -->
        <div class="db-stats">

            <div class="stat-card c-brand">
                <div class="stat-card-top">
                    <div class="stat-icon c-brand"><i class="fas fa-user-graduate"></i></div>
                    <span class="stat-trend up"><i class="fas fa-arrow-up"></i> 4.2%</span>
                </div>
                <div class="stat-value" data-target="3654">0</div>
                <div class="stat-label">Total Students</div>
                <div class="stat-footer">
                    <a href="<?= base_url('student/all_student') ?>">View All <i class="fas fa-arrow-right"></i></a>
                    <div class="stat-bar"><div class="stat-bar-fill" style="width:72%"></div></div>
                </div>
            </div>

            <div class="stat-card c-blue">
                <div class="stat-card-top">
                    <div class="stat-icon c-blue"><i class="fas fa-chalkboard-teacher"></i></div>
                    <span class="stat-trend up"><i class="fas fa-arrow-up"></i> 1.8%</span>
                </div>
                <div class="stat-value" data-target="284">0</div>
                <div class="stat-label">Total Teachers</div>
                <div class="stat-footer">
                    <a href="<?= base_url('staff/manage_staff') ?>">View All <i class="fas fa-arrow-right"></i></a>
                    <div class="stat-bar"><div class="stat-bar-fill" style="width:55%"></div></div>
                </div>
            </div>

            <div class="stat-card c-rose">
                <div class="stat-card-top">
                    <div class="stat-icon c-rose"><i class="fas fa-school"></i></div>
                    <span class="stat-trend up"><i class="fas fa-arrow-up"></i> 0.6%</span>
                </div>
                <div class="stat-value" data-target="162">0</div>
                <div class="stat-label">Total Classes</div>
                <div class="stat-footer">
                    <a href="<?= base_url('classes/manage_class') ?>">View All <i class="fas fa-arrow-right"></i></a>
                    <div class="stat-bar"><div class="stat-bar-fill" style="width:85%"></div></div>
                </div>
            </div>

            <div class="stat-card c-amber">
                <div class="stat-card-top">
                    <div class="stat-icon c-amber"><i class="fas fa-users"></i></div>
                    <span class="stat-trend down"><i class="fas fa-arrow-down"></i> 0.3%</span>
                </div>
                <div class="stat-value" data-target="82">0</div>
                <div class="stat-label">Non-Teaching Staff</div>
                <div class="stat-footer">
                    <a href="#">View All <i class="fas fa-arrow-right"></i></a>
                    <div class="stat-bar"><div class="stat-bar-fill" style="width:40%;background:var(--amber)"></div></div>
                </div>
            </div>

        </div>

        <!-- ── FEE CHART ── -->
        <div class="db-panel db-fee-chart">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Fee Collection</div>
                    <div class="card-subtitle">Monthly overview &middot; <?= htmlspecialchars($session_year, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <span class="card-badge">Live</span>
            </div>
            <div class="fee-totals">
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--brand)">&#8377;92,000</span>
                    <span class="fee-total-lbl">Collected</span>
                </div>
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--rose)">&#8377;28,400</span>
                    <span class="fee-total-lbl">Pending</span>
                </div>
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--green)">76%</span>
                    <span class="fee-total-lbl">Collection Rate</span>
                </div>
            </div>
            <canvas id="feeChart" height="200"></canvas>
        </div>

        <!-- ── LEAVE REQUESTS ── -->
        <div class="db-panel db-leave">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Leave Requests</div>
                    <div class="card-subtitle">Pending approval</div>
                </div>
                <span class="card-badge rose">5 New</span>
            </div>
            <div class="leave-list">
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#D49700,#C9A84C);color:#0C0A06">R</div>
                    <div class="leave-info">
                        <div class="leave-name">Rahul Sharma</div>
                        <div class="leave-dates">12 Jan &ndash; 14 Jan</div>
                    </div>
                    <span class="leave-badge pending">Pending</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#C9A84C,#E05C6F);color:#fff">P</div>
                    <div class="leave-info">
                        <div class="leave-name">Priya Singh</div>
                        <div class="leave-dates">15 Jan &ndash; 16 Jan</div>
                    </div>
                    <span class="leave-badge approved">Approved</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#F5AF00,#D49700);color:#0C0A06">A</div>
                    <div class="leave-info">
                        <div class="leave-name">Amit Verma</div>
                        <div class="leave-dates">18 Jan &ndash; 19 Jan</div>
                    </div>
                    <span class="leave-badge pending">Pending</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#4AB5E3,#D49700);color:#fff">S</div>
                    <div class="leave-info">
                        <div class="leave-name">Sunita Rawat</div>
                        <div class="leave-dates">20 Jan &ndash; 21 Jan</div>
                    </div>
                    <span class="leave-badge pending">Pending</span>
                </div>
            </div>
        </div>

        <!-- ── ATTENDANCE ── -->
        <div class="db-panel db-attendance">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Today's Attendance</div>
                    <div class="card-subtitle" id="todayDate">&mdash;</div>
                </div>
                <span class="card-badge green">85% Present</span>
            </div>
            <div class="attendance-wrap">
                <div class="attendance-canvas-wrap">
                    <canvas id="attendanceChart" width="140" height="140"></canvas>
                    <div class="attendance-center">
                        <span class="attendance-pct">85%</span>
                        <span class="attendance-pct-sub">Present</span>
                    </div>
                </div>
                <div class="attendance-legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--brand)"></div>
                        <span class="legend-lbl">Present</span>
                        <span class="legend-val">3,106</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--rose)"></div>
                        <span class="legend-lbl">Absent</span>
                        <span class="legend-val">548</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:var(--amber)"></div>
                        <span class="legend-lbl">On Leave</span>
                        <span class="legend-val">44</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── QUICK ACTIONS ── -->
        <div class="db-panel db-quick">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Quick Actions</div>
                    <div class="card-subtitle">Frequently used</div>
                </div>
            </div>
            <div class="quick-grid">
                <a href="<?= base_url('student/add_student') ?>" class="quick-btn qb-brand">
                    <i class="fas fa-user-plus"></i>Add Student
                </a>
                <a href="<?= base_url('staff/add_staff') ?>" class="quick-btn qb-brand2">
                    <i class="fas fa-user-tie"></i>Add Staff
                </a>
                <a href="<?= base_url('attendance') ?>" class="quick-btn qb-blue">
                    <i class="fas fa-calendar-check"></i>Attendance
                </a>
                <a href="<?= base_url('fees') ?>" class="quick-btn qb-rose">
                    <i class="fas fa-money-bill-wave"></i>Fees
                </a>
            </div>
        </div>

        <!-- ── CALENDAR ── -->
        <div class="db-panel db-calendar">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Calendar</div>
                    <div class="card-subtitle">School schedule</div>
                </div>
            </div>
            <div class="mini-cal" id="miniCal"></div>
            <div class="event-list">
                <div class="event-item">
                    <span class="event-date">Feb 25</span>
                    <span class="event-name">Annual Science Fair</span>
                </div>
                <div class="event-item blue">
                    <span class="event-date">Mar 01</span>
                    <span class="event-name">PTM &ndash; All Classes</span>
                </div>
                <div class="event-item rose">
                    <span class="event-date">Mar 10</span>
                    <span class="event-name">Board Exam Begins</span>
                </div>
            </div>
        </div>

    </div><!-- /db-body -->
</div><!-- /db-root -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {

        var root = document.getElementById('dbRoot');
        var grtEl = document.getElementById('dbGreeting');
        var dlEl = document.getElementById('dbLiveDate');
        var tdEl = document.getElementById('todayDate');

        var feeChartInst = null;
        var attendChartInst = null;

        /* ── helpers ── */
        function getHour() {
            return new Date().getHours();
        }

        function getGreeting() {
            var h = getHour();
            if (h >= 5 && h < 12) return 'Morning';
            if (h >= 12 && h < 17) return 'Afternoon';
            if (h >= 17 && h < 21) return 'Evening';
            return 'Night';
        }

        function fmtDate(d) {
            return d.toLocaleDateString('en-IN', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        function tick() {
            var n = new Date();
            if (dlEl) dlEl.textContent = fmtDate(n);
            if (tdEl) tdEl.textContent = fmtDate(n);
            if (grtEl) grtEl.textContent = getGreeting();
        }

        /* ── init ── */
        tick();

        /* Enable transitions after first paint */
        requestAnimationFrame(function() {
            setTimeout(function() { root.classList.add('t-ready'); }, 60);
        });

        /* Watch for theme changes triggered by the header toggle button */
        if (window.MutationObserver) {
            new MutationObserver(function(mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    if (mutations[i].attributeName === 'data-theme') {
                        updateChartColors(root.getAttribute('data-theme') || 'dark');
                    }
                }
            }).observe(root, { attributes: true });
        }

        /* Refresh greeting + date every minute */
        setInterval(tick, 60000);

        /* ── counter animation ── */
        root.querySelectorAll('.stat-value[data-target]').forEach(function(el) {
            var target = +el.dataset.target,
                start = null,
                dur = 1200;

            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                var ease = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(ease * target).toLocaleString('en-IN');
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        /* ══════════════════════════════════════════
           CHART COLORS — update on theme change
        ══════════════════════════════════════════ */
        function getChartColors() {
            var isDark = root.getAttribute('data-theme') === 'dark';
            return {
                grid: isDark ? 'rgba(245,175,0,0.05)' : 'rgba(180,140,0,0.08)',
                tick: isDark ? '#7A6E54' : '#9A8050',
                legend: isDark ? '#7A6E54' : '#9A8050'
            };
        }

        function updateChartColors(theme) {
            var c = getChartColors();
            if (feeChartInst) {
                feeChartInst.options.scales.x.grid.color = c.grid;
                feeChartInst.options.scales.y.grid.color = c.grid;
                feeChartInst.options.scales.x.ticks.color = c.tick;
                feeChartInst.options.scales.y.ticks.color = c.tick;
                feeChartInst.options.plugins.legend.labels.color = c.legend;
                feeChartInst.update();
            }
        }

        /* ── attendance donut ── */
        var aCtx = document.getElementById('attendanceChart');
        if (aCtx) {
            attendChartInst = new Chart(aCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Leave'],
                    datasets: [{
                        data: [85, 12, 3],
                        backgroundColor: ['#F5AF00', '#E05C6F', '#C9A84C'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '76%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(c) {
                                    return ' ' + c.label + ': ' + c.raw + '%'
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
        }

        /* ── fee bar chart ── */
        var fCtx = document.getElementById('feeChart');
        if (fCtx) {
            var c = getChartColors();
            feeChartInst = new Chart(fCtx, {
                type: 'bar',
                data: {
                    labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
                    datasets: [{
                            label: 'Collected',
                            data: [18000, 20000, 17000, 22000, 19500, 21000, 16000, 23000, 18500, 12000, 15000, 10000],
                            backgroundColor: 'rgba(245,175,0,0.72)',
                            borderRadius: 6,
                            borderSkipped: false
                        },
                        {
                            label: 'Pending',
                            data: [4000, 3200, 5000, 2800, 4100, 3000, 6000, 2200, 4500, 8000, 5000, 10000],
                            backgroundColor: 'rgba(224,92,111,0.52)',
                            borderRadius: 6,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: c.legend,
                                font: {
                                    size: 11,
                                    family: 'DM Sans'
                                },
                                boxWidth: 10,
                                padding: 18
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ' ' + ctx.dataset.label + ': \u20B9' + ctx.raw.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: c.grid
                            },
                            ticks: {
                                color: c.tick,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: c.grid
                            },
                            ticks: {
                                color: c.tick,
                                font: {
                                    size: 11
                                },
                                callback: function(v) {
                                    return '\u20B9' + v.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }

        /* Sync chart colors once both charts are built */
        updateChartColors(root.getAttribute('data-theme') || 'dark');

        /* ── mini calendar ── */
        (function() {
            var today = new Date(),
                yr = today.getFullYear(),
                mo = today.getMonth();
            var events = [25, 10];

            function render(y, m) {
                var el = document.getElementById('miniCal');
                if (!el) return;
                var days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                var mN = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                var first = new Date(y, m, 1).getDay();
                var total = new Date(y, m + 1, 0).getDate();
                var prevT = new Date(y, m, 0).getDate();
                var tDay = (y === today.getFullYear() && m === today.getMonth()) ? today.getDate() : -1;
                var h = '<div class="mini-cal-header"><span class="mini-cal-month">' + mN[m] + ' ' + y + '</span>';
                h += '<div class="mini-cal-nav">';
                h += '<button onclick="window._dbCalPrev()"><i class="fas fa-chevron-left" style="font-size:10px"></i></button>';
                h += '<button onclick="window._dbCalNext()"><i class="fas fa-chevron-right" style="font-size:10px"></i></button>';
                h += '</div></div><div class="cal-grid">';
                days.forEach(function(d) {
                    h += '<div class="cal-day-name">' + d + '</div>';
                });
                for (var i = first - 1; i >= 0; i--) h += '<div class="cal-day other">' + (prevT - i) + '</div>';
                for (var d = 1; d <= total; d++) {
                    var cls = 'cal-day';
                    if (d === tDay) cls += ' today';
                    else if (events.indexOf(d) > -1) cls += ' has-event';
                    h += '<div class="' + cls + '">' + d + '</div>';
                }
                var rem = (first + total) % 7;
                if (rem) rem = 7 - rem;
                for (var n = 1; n <= rem; n++) h += '<div class="cal-day other">' + n + '</div>';
                h += '</div>';
                el.innerHTML = h;
            }
            window._dbCalY = yr;
            window._dbCalM = mo;
            window._dbCalPrev = function() {
                window._dbCalM--;
                if (window._dbCalM < 0) {
                    window._dbCalM = 11;
                    window._dbCalY--;
                }
                render(window._dbCalY, window._dbCalM);
            };
            window._dbCalNext = function() {
                window._dbCalM++;
                if (window._dbCalM > 11) {
                    window._dbCalM = 0;
                    window._dbCalY++;
                }
                render(window._dbCalY, window._dbCalM);
            };
            render(yr, mo);
        })();

    })();
</script>
