<!-- Content Wrapper -->
<div class="content-wrapper db-root" id="dbRoot">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

        /* ══════════════════════════════════════════════
           BRAND CONSTANTS
        ══════════════════════════════════════════════ */
        .db-root {
            --brand:       #0f766e;
            --brand2:      #0d6b63;
            --brand3:      #14b8a6;
            --brand-light: #6ee7e7;
            --brand-dim:   rgba(15, 118, 110, 0.10);
            --brand-glow:  rgba(15, 118, 110, 0.22);
            --brand-ring:  rgba(15, 118, 110, 0.18);
            --blue:   #4AB5E3;
            --green:  #15803d;
            --rose:   #E05C6F;
            --amber:  #d97706;
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
        [data-theme="night"] .db-root {
            --bg:    #070f1c;
            --bg2:   #0c1e38;
            --bg3:   #0f2545;
            --bg4:   #1a3555;
            --card:  rgba(12, 30, 56, 0.96);
            --border:  rgba(15, 118, 110, 0.10);
            --border2: rgba(15, 118, 110, 0.22);
            --text:    #e6f4f1;
            --text2:   #94c9c3;
            --muted:   #5a9e98;
            --muted2:  #2e6b65;
            --heading: #FFFFFF;
            --shadow:      0 4px 28px rgba(0, 0, 0, 0.55);
            --shadow-card: 0 2px 16px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(15, 118, 110, 0.10);
            --grid-line:   rgba(15, 118, 110, 0.05);
            --stat-hover:  radial-gradient(ellipse at 50% 0%, rgba(15, 118, 110, .08), transparent 65%);
            --chart-grid:  rgba(15, 118, 110, 0.08);
            --chart-tick:  #5a9e98;
            --hero-grad:   linear-gradient(135deg, rgba(15, 118, 110, 0.08) 0%, transparent 55%);
        }

        /* ══════════════════════════════════════════════
           LIGHT THEME
        ══════════════════════════════════════════════ */
        [data-theme="day"] .db-root {
            --bg:    #f0f7f5;
            --bg2:   #ffffff;
            --bg3:   #e6f4f1;
            --bg4:   #cce9e4;
            --card:  rgba(255, 255, 255, 0.98);
            --border:  rgba(15, 118, 110, 0.15);
            --border2: rgba(15, 118, 110, 0.28);
            --text:    #0c1e38;
            --text2:   #1a5c56;
            --muted:   #5a9e98;
            --muted2:  #94c9c3;
            --heading: #0c1e38;
            --shadow:      0 2px 12px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 2px 16px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(15, 118, 110, 0.12);
            --grid-line:   rgba(15, 118, 110, 0.06);
            --stat-hover:  radial-gradient(ellipse at 50% 0%, rgba(15, 118, 110, .07), transparent 65%);
            --chart-grid:  rgba(15, 118, 110, 0.08);
            --chart-tick:  #5a9e98;
            --hero-grad:   linear-gradient(135deg, rgba(15, 118, 110, 0.06) 0%, transparent 55%);
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
        .db-root.t-ready canvas { transition: none; }

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
           HERO
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
        .db-hero::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--brand2) 25%, var(--brand) 50%, var(--brand3) 75%, transparent 100%);
        }
        .db-hero::after {
            content: ''; position: absolute;
            top: -80px; left: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(15, 118, 110, 0.06) 0%, transparent 68%);
            pointer-events: none;
        }
        .db-hero-left { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }
        .db-school-icon {
            width: 50px; height: 50px; border-radius: 14px;
            background: var(--brand-dim); border: 1px solid var(--brand-ring);
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
        .db-hero-text p { font-size: 12px; color: var(--muted); margin-top: 4px; letter-spacing: .2px; }
        .db-hero-text p span { color: var(--text2); }
        .db-hero-right { display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; }
        .db-date-pill {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg3); border: 1px solid var(--border2);
            border-radius: 50px; padding: 7px 16px;
            font-size: 12px; color: var(--text2);
        }
        .db-date-pill .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--brand); box-shadow: 0 0 8px var(--brand);
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
        .stat-card::after {
            content: ''; position: absolute; inset: 0;
            border-radius: var(--r); opacity: 0; transition: opacity .25s;
            background: var(--stat-hover);
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card:hover::after { opacity: 1; }
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
        .stat-icon.c-brand { background: var(--brand-dim); color: var(--brand); border: 1px solid var(--brand-ring); }
        .stat-icon.c-blue  { background: rgba(74,181,227,.09); color: var(--blue); border: 1px solid rgba(74,181,227,.18); }
        .stat-icon.c-rose  { background: rgba(224,92,111,.09); color: var(--rose); border: 1px solid rgba(224,92,111,.18); }
        .stat-icon.c-amber { background: rgba(201,168,76,.09); color: var(--amber); border: 1px solid rgba(201,168,76,.18); }

        .stat-sub {
            font-family: var(--font-mono); font-size: 10px;
            padding: 3px 8px; border-radius: 50px;
            background: var(--brand-dim); color: var(--muted);
        }

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

        .db-fee-chart  { grid-column: 1/9; }
        .db-events     { grid-column: 9/-1; }
        .db-class-dist { grid-column: 1/5; }
        .db-gender     { grid-column: 5/9; }
        .db-quick      { grid-column: 9/-1; }
        .db-calendar   { grid-column: 1/5; }
        .db-gallery    { grid-column: 5/-1; }

        @media(max-width:1100px) {
            .db-fee-chart, .db-events { grid-column: 1/-1; }
        }
        @media(max-width:900px) {
            .db-class-dist, .db-gender, .db-quick, .db-calendar, .db-gallery { grid-column: 1/-1; }
        }
        @media(max-width:700px) {
            .db-body { padding: 16px 16px 40px; gap: 14px; }
            .db-hero { padding: 20px 16px 18px; }
        }

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
        .card-badge.rose  { background: rgba(224,92,111,.10); color: var(--rose); border-color: rgba(224,92,111,.20); }
        .card-badge.green { background: rgba(61,214,140,.10); color: var(--green); border-color: rgba(61,214,140,.20); }

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

        /* ── Events panel ── */
        .evt-list { display: flex; flex-direction: column; gap: 8px; }
        .evt-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            background: var(--brand-dim);
            border: 1px solid var(--border);
            border-radius: 10px;
            border-left: 3px solid var(--brand);
            transition: border-color .18s, background .18s;
        }
        .evt-item:hover { border-color: var(--brand-ring); }
        .evt-item.ongoing { border-left-color: var(--green); background: rgba(61,214,140,.06); }
        .evt-item.completed { border-left-color: var(--muted); background: rgba(90,158,152,.04); }
        .evt-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; background: var(--brand-dim); color: var(--brand);
        }
        .evt-info { flex: 1; min-width: 0; }
        .evt-name { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .evt-date { font-size: 10.5px; color: var(--muted); margin-top: 1px; font-family: var(--font-mono); }
        .evt-badge {
            font-size: 10px; padding: 3px 9px; border-radius: 50px; white-space: nowrap; flex-shrink: 0;
        }
        .evt-badge.upcoming { background: var(--brand-dim); color: var(--brand); border: 1px solid var(--brand-ring); }
        .evt-badge.ongoing  { background: rgba(61,214,140,.10); color: var(--green); border: 1px solid rgba(61,214,140,.22); }
        .evt-empty { text-align: center; padding: 20px; color: var(--muted); font-size: 12px; }
        .evt-footer { margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); display: flex; gap: 10px; }
        .evt-footer a {
            font-size: 11px; color: var(--brand); text-decoration: none;
            display: flex; align-items: center; gap: 4px;
        }
        .evt-footer a:hover { text-decoration: underline; }

        /* ── Quick actions ── */
        .quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .quick-btn {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 8px;
            padding: 18px 12px; border-radius: 12px;
            text-decoration: none; font-size: 10.5px; font-weight: 600;
            letter-spacing: .3px; text-transform: uppercase;
            color: var(--text); border: 1px solid var(--border);
            transition: transform .18s var(--ease), box-shadow .18s var(--ease), border-color .18s;
            position: relative; overflow: hidden;
        }
        .quick-btn:hover { transform: translateY(-3px); text-decoration: none; color: var(--text); }
        .quick-btn i { font-size: 22px; }
        .quick-btn.qb-brand  { background: var(--brand-dim); border-color: var(--brand-ring); }
        .quick-btn.qb-amber  { background: rgba(201,168,76,.08); border-color: rgba(201,168,76,.18); }
        .quick-btn.qb-blue   { background: rgba(74,181,227,.08); border-color: rgba(74,181,227,.18); }
        .quick-btn.qb-rose   { background: rgba(224,92,111,.08); border-color: rgba(224,92,111,.18); }
        .quick-btn.qb-green  { background: rgba(21,128,61,.08); border-color: rgba(21,128,61,.18); }
        .quick-btn.qb-purple { background: rgba(124,58,237,.08); border-color: rgba(124,58,237,.18); }
        .quick-btn.qb-brand:hover  { border-color: var(--brand); box-shadow: 0 4px 20px rgba(15,118,110,.14); }
        .quick-btn.qb-amber:hover  { border-color: var(--amber); box-shadow: 0 4px 20px rgba(201,168,76,.14); }
        .quick-btn.qb-blue:hover   { border-color: var(--blue);  box-shadow: 0 4px 20px rgba(74,181,227,.14); }
        .quick-btn.qb-rose:hover   { border-color: var(--rose);  box-shadow: 0 4px 20px rgba(224,92,111,.14); }
        .quick-btn.qb-green:hover  { border-color: var(--green); box-shadow: 0 4px 20px rgba(21,128,61,.14); }
        .quick-btn.qb-purple:hover { border-color: #7c3aed;      box-shadow: 0 4px 20px rgba(124,58,237,.14); }
        .quick-btn.qb-brand  i { color: var(--brand); }
        .quick-btn.qb-amber  i { color: var(--amber); }
        .quick-btn.qb-blue   i { color: var(--blue); }
        .quick-btn.qb-rose   i { color: var(--rose); }
        .quick-btn.qb-green  i { color: var(--green); }
        .quick-btn.qb-purple i { color: #7c3aed; }

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
        .mini-cal-nav button:hover { background: var(--bg3); color: var(--brand); border-color: var(--brand-ring); }
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
        .cal-day:hover { background: rgba(15,118,110,.10); color: var(--brand); }
        .cal-day.other { color: var(--muted2); }
        .cal-day.today {
            background: var(--brand); color: #ffffff;
            font-weight: 700; box-shadow: 0 0 12px rgba(15, 118, 110, .42);
        }
        .cal-day.has-event { position: relative; }
        .cal-day.has-event::after {
            content: ''; position: absolute; bottom: 3px; left: 50%;
            transform: translateX(-50%); width: 4px; height: 4px;
            border-radius: 50%; background: var(--rose);
        }

        /* ── Gallery panel ── */
        .gallery-strip { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; }
        .gallery-thumb {
            width: 100px; height: 80px; border-radius: 10px; overflow: hidden;
            flex-shrink: 0; border: 1px solid var(--border); cursor: pointer;
            transition: transform .18s, border-color .18s;
        }
        .gallery-thumb:hover { transform: scale(1.05); border-color: var(--brand); }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .gallery-thumb.vid { position: relative; }
        .gallery-thumb.vid::after {
            content: '\f04b'; font-family: 'FontAwesome';
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,.35); color: #fff; font-size: 18px;
        }

        /* ── Loading skeleton ── */
        .db-skel {
            background: linear-gradient(90deg, var(--bg3) 25%, var(--bg4) 50%, var(--bg3) 75%);
            background-size: 200% 100%;
            animation: dbSkelShimmer 1.5s infinite;
            border-radius: 8px;
        }
        @keyframes dbSkelShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .db-skel-line { height: 16px; margin-bottom: 8px; }
        .db-skel-val  { height: 42px; width: 120px; margin-bottom: 6px; }

        /* ── Role-based hide ── */
        .db-finance-only { /* shown by default, hidden via JS for Teacher role */ }

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
        .db-events     { animation: dbFadeUp .45s .29s ease both; }
        .db-class-dist { animation: dbFadeUp .45s .34s ease both; }
        .db-gender     { animation: dbFadeUp .45s .39s ease both; }
        .db-quick      { animation: dbFadeUp .45s .44s ease both; }
        .db-calendar   { animation: dbFadeUp .45s .49s ease both; }
        .db-gallery    { animation: dbFadeUp .45s .54s ease both; }
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
                    <span class="stat-sub" id="sectionCount">--</span>
                </div>
                <div class="stat-value" id="valStudents">--</div>
                <div class="stat-label">Total Students</div>
                <div class="stat-footer">
                    <a href="<?= base_url('student/all_student') ?>">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="stat-card c-blue">
                <div class="stat-card-top">
                    <div class="stat-icon c-blue"><i class="fas fa-chalkboard-teacher"></i></div>
                    <span class="stat-sub">Session Staff</span>
                </div>
                <div class="stat-value" id="valTeachers">--</div>
                <div class="stat-label">Total Teachers</div>
                <div class="stat-footer">
                    <a href="<?= base_url('staff/all_staff') ?>">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="stat-card c-rose">
                <div class="stat-card-top">
                    <div class="stat-icon c-rose"><i class="fas fa-school"></i></div>
                    <span class="stat-sub" id="classCount">--</span>
                </div>
                <div class="stat-value" id="valClasses">--</div>
                <div class="stat-label">Classes &amp; Sections</div>
                <div class="stat-footer">
                    <a href="<?= base_url('classes/manage_classes') ?>">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="stat-card c-amber db-finance-only">
                <div class="stat-card-top">
                    <div class="stat-icon c-amber"><i class="fas fa-rupee-sign"></i></div>
                    <span class="stat-sub">This Session</span>
                </div>
                <div class="stat-value" id="valFees">--</div>
                <div class="stat-label">Fees Collected</div>
                <div class="stat-footer">
                    <a href="<?= base_url('fees/fees_records') ?>">View Records <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- ── FEE COLLECTION CHART ── -->
        <div class="db-panel db-fee-chart db-finance-only">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Fee Collection</div>
                    <div class="card-subtitle">Monthly overview &middot; <?= htmlspecialchars($session_year, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <span class="card-badge">Live</span>
            </div>
            <div class="fee-totals">
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--brand)" id="feeTotalCollected">&#8377;0</span>
                    <span class="fee-total-lbl">Collected</span>
                </div>
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--muted)" id="feeTotalReceipts">0</span>
                    <span class="fee-total-lbl">Receipts</span>
                </div>
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--green)" id="feeTotalMonths">0</span>
                    <span class="fee-total-lbl">Active Months</span>
                </div>
            </div>
            <canvas id="feeChart" height="200"></canvas>
        </div>

        <!-- ── EVENTS ── -->
        <div class="db-panel db-events">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Events</div>
                    <div class="card-subtitle">Upcoming &amp; ongoing</div>
                </div>
                <span class="card-badge" id="evtBadge">--</span>
            </div>
            <div class="evt-list" id="evtList">
                <div class="evt-empty">Loading events...</div>
            </div>
            <div class="evt-footer">
                <a href="<?= base_url('events') ?>"><i class="fas fa-calendar"></i> View Calendar</a>
                <a href="<?= base_url('events/list') ?>"><i class="fas fa-plus"></i> All Events</a>
            </div>
        </div>

        <!-- ── STUDENT DISTRIBUTION ── -->
        <div class="db-panel db-class-dist">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Students by Class</div>
                    <div class="card-subtitle">Current session enrollment</div>
                </div>
            </div>
            <canvas id="classChart" height="220"></canvas>
        </div>

        <!-- ── GENDER RATIO ── -->
        <div class="db-panel db-gender">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Gender Distribution</div>
                    <div class="card-subtitle">All students</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:20px;">
                <div style="position:relative;flex-shrink:0;">
                    <canvas id="genderChart" width="140" height="140"></canvas>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                        <span style="font-family:var(--font-display);font-size:24px;font-weight:700;color:var(--heading);" id="genderTotal">--</span>
                        <span style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Total</span>
                    </div>
                </div>
                <div id="genderLegend" style="flex:1;"></div>
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
                <a href="<?= base_url('student/studentAdmission') ?>" class="quick-btn qb-brand">
                    <i class="fas fa-user-plus"></i>Add Student
                </a>
                <a href="<?= base_url('fees/fees_counter') ?>" class="quick-btn qb-amber db-finance-only">
                    <i class="fas fa-money-bill-wave"></i>Collect Fees
                </a>
                <a href="<?= base_url('events/list') ?>" class="quick-btn qb-rose">
                    <i class="fas fa-calendar-plus"></i>Create Event
                </a>
                <a href="<?= base_url('attendance/student') ?>" class="quick-btn qb-blue">
                    <i class="fas fa-calendar-check"></i>Attendance
                </a>
                <a href="<?= base_url('result/marks_entry') ?>" class="quick-btn qb-green">
                    <i class="fas fa-clipboard-list"></i>Marks Entry
                </a>
                <a href="<?= base_url('communication/notices') ?>" class="quick-btn qb-purple">
                    <i class="fas fa-bullhorn"></i>Notice
                </a>
            </div>
        </div>

        <!-- ── CALENDAR ── -->
        <div class="db-panel db-calendar">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Calendar</div>
                    <div class="card-subtitle" id="todayDate">&mdash;</div>
                </div>
            </div>
            <div class="mini-cal" id="miniCal"></div>
            <div class="event-list" id="calEventList" style="margin-top:14px;display:flex;flex-direction:column;gap:7px;"></div>
        </div>

        <!-- ── GALLERY ACTIVITY ── -->
        <div class="db-panel db-gallery">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Gallery</div>
                    <div class="card-subtitle">Event albums</div>
                </div>
                <a href="<?= base_url('schools/schoolgallery') ?>" style="font-size:11px;color:var(--brand);text-decoration:none;">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div id="galleryStrip" class="gallery-strip">
                <div class="evt-empty" style="width:100%">Gallery loads from Event Albums</div>
            </div>
        </div>

    </div><!-- /db-body -->
</div><!-- /db-root -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {

    var root  = document.getElementById('dbRoot');
    var grtEl = document.getElementById('dbGreeting');
    var dlEl  = document.getElementById('dbLiveDate');
    var tdEl  = document.getElementById('todayDate');
    var BASE  = '<?= rtrim(base_url(), '/') ?>';
    var ROLE  = '<?= htmlspecialchars($admin_role, ENT_QUOTES, 'UTF-8') ?>';

    var feeChartInst = null;
    var classChartInst = null;
    var genderChartInst = null;

    /* calendar event dates (populated by AJAX) */
    var calEventDates = {};

    /* ── Helpers ── */
    function getGreeting() {
        var h = new Date().getHours();
        if (h >= 5 && h < 12) return 'Morning';
        if (h >= 12 && h < 17) return 'Afternoon';
        if (h >= 17 && h < 21) return 'Evening';
        return 'Night';
    }
    function fmtDate(d) {
        return d.toLocaleDateString('en-IN', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    function fmtINR(n) {
        return '\u20B9' + Number(n).toLocaleString('en-IN');
    }
    function tick() {
        var n = new Date();
        if (dlEl) dlEl.textContent = fmtDate(n);
        if (tdEl) tdEl.textContent = fmtDate(n);
        if (grtEl) grtEl.textContent = getGreeting();
    }
    tick();
    setInterval(tick, 60000);

    /* Enable transitions after first paint */
    requestAnimationFrame(function() { setTimeout(function() { root.classList.add('t-ready'); }, 60); });

    /* ── Role-based visibility ── */
    if (ROLE === 'Teacher') {
        document.querySelectorAll('.db-finance-only').forEach(function(el) { el.style.display = 'none'; });
    }

    /* ── Counter animation ── */
    function animateValue(el, target) {
        var start = null, dur = 1200;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var ease = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(ease * target).toLocaleString('en-IN');
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function animateINR(el, target) {
        var start = null, dur = 1200;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var ease = 1 - Math.pow(1 - p, 3);
            el.textContent = fmtINR(Math.round(ease * target));
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* ── Chart color helper ── */
    function getC() {
        return {
            grid: 'rgba(15,118,110,0.08)',
            tick: '#5a9e98',
            legend: '#5a9e98'
        };
    }

    /* ══════════════════════════════════════════
       LOAD DASHBOARD DATA
    ══════════════════════════════════════════ */
    fetch(BASE + '/admin/get_dashboard_data')
        .then(function(r) { return r.json(); })
        .then(function(D) {
            populateStats(D.stats);
            populateEvents(D.events);
            buildFeeChart(D.monthly_fees, D.stats.fees_collected);
            buildClassChart(D.students_by_class);
            buildGenderChart(D.gender, D.stats.students);
            storeCalendarEvents(D.calendar_events);
            renderCalendar(window._dbCalY, window._dbCalM);
        })
        .catch(function(e) {
            console.error('Dashboard load failed:', e);
        });

    /* ── Populate stat cards ── */
    function populateStats(s) {
        animateValue(document.getElementById('valStudents'), s.students);
        animateValue(document.getElementById('valTeachers'), s.teachers);

        var classEl = document.getElementById('valClasses');
        classEl.textContent = s.classes;
        document.getElementById('classCount').textContent = s.classes + ' classes';
        document.getElementById('sectionCount').textContent = s.sections + ' sections';

        if (ROLE !== 'Teacher') {
            animateINR(document.getElementById('valFees'), s.fees_collected);
        }
    }

    /* ── Populate events ── */
    function populateEvents(evts) {
        var list = document.getElementById('evtList');
        var badge = document.getElementById('evtBadge');
        var items = [];

        var catIcons = { event: 'fas fa-star', cultural: 'fas fa-music', sports: 'fas fa-trophy' };

        (evts.ongoing || []).forEach(function(e) {
            items.push({ data: e, cls: 'ongoing', badgeCls: 'ongoing', badgeTxt: 'Ongoing' });
        });
        (evts.upcoming || []).forEach(function(e) {
            items.push({ data: e, cls: '', badgeCls: 'upcoming', badgeTxt: fmtShortDate(e.start) });
        });

        var total = (evts.ongoing || []).length + (evts.upcoming || []).length;
        badge.textContent = total + ' Active';
        if (!total) badge.classList.add('rose');

        if (!items.length) {
            list.innerHTML = '<div class="evt-empty"><i class="fas fa-calendar-times" style="font-size:20px;margin-bottom:8px;display:block;opacity:.4;"></i>No upcoming events</div>';
            return;
        }

        list.innerHTML = items.slice(0, 5).map(function(item) {
            var e = item.data;
            var icon = catIcons[e.category] || 'fas fa-star';
            return '<div class="evt-item ' + item.cls + '">'
                + '<div class="evt-icon"><i class="' + icon + '"></i></div>'
                + '<div class="evt-info"><div class="evt-name">' + escHtml(e.title) + '</div>'
                + '<div class="evt-date">' + (e.location ? e.location + ' &middot; ' : '') + e.start + '</div></div>'
                + '<span class="evt-badge ' + item.badgeCls + '">' + item.badgeTxt + '</span>'
                + '</div>';
        }).join('');
    }

    function fmtShortDate(d) {
        if (!d) return '';
        var p = d.split('-');
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return (p[2] ? parseInt(p[2]) : '') + ' ' + (months[parseInt(p[1]) - 1] || '');
    }

    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /* ── Fee Chart ── */
    function buildFeeChart(monthly, totalCollected) {
        var months = ['Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar'];
        var labels = [];
        var values = [];
        var totalReceipts = 0;

        // Map monthly_fees (keyed by YYYY-MM) to our April–March order
        var keys = Object.keys(monthly);
        if (keys.length) {
            // Use actual data
            keys.forEach(function(k) {
                var parts = k.split('-');
                var mIdx = parseInt(parts[1]) - 1;
                var mName = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][mIdx];
                labels.push(mName);
                values.push(monthly[k]);
            });
        } else {
            labels = months;
            values = months.map(function() { return 0; });
        }

        document.getElementById('feeTotalCollected').textContent = fmtINR(totalCollected);
        document.getElementById('feeTotalReceipts').textContent = keys.length ? Object.values(monthly).reduce(function(a, b) { return a + b; }, 0).toLocaleString('en-IN') : '0';
        document.getElementById('feeTotalMonths').textContent = keys.length;

        var c = getC();
        var ctx = document.getElementById('feeChart');
        if (!ctx) return;

        feeChartInst = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Collected',
                    data: values,
                    backgroundColor: 'rgba(15,118,110,0.72)',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: c.legend, font: { size: 11, family: 'DM Sans' }, boxWidth: 10, padding: 18 } },
                    tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.dataset.label + ': ' + fmtINR(ctx.raw); } } }
                },
                scales: {
                    x: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 11 } } },
                    y: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 11 }, callback: function(v) { return fmtINR(v); } } }
                }
            }
        });
    }

    /* ── Class Distribution Chart ── */
    function buildClassChart(classDist) {
        var labels = Object.keys(classDist);
        var values = Object.values(classDist);

        var ctx = document.getElementById('classChart');
        if (!ctx || !labels.length) return;

        var colors = labels.map(function(_, i) {
            var hue = 170 + (i * 25) % 360;
            return 'hsla(' + hue + ', 55%, 45%, 0.75)';
        });

        var c = getC();
        classChartInst = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Students',
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.raw + ' students'; } } }
                },
                scales: {
                    x: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 10 } } },
                    y: { grid: { display: false }, ticks: { color: c.tick, font: { size: 11 } } }
                }
            }
        });
    }

    /* ── Gender Distribution Chart ── */
    function buildGenderChart(gender, total) {
        document.getElementById('genderTotal').textContent = total.toLocaleString('en-IN');

        var labels = ['Male', 'Female', 'Other'];
        var values = [gender.Male || 0, gender.Female || 0, gender.Other || 0];
        var colors = ['#0f766e', '#E05C6F', '#d97706'];

        // Legend
        var legendHtml = '';
        labels.forEach(function(l, i) {
            if (!values[i]) return;
            var pct = total ? Math.round(values[i] / total * 100) : 0;
            legendHtml += '<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">'
                + '<div style="width:9px;height:9px;border-radius:3px;flex-shrink:0;background:' + colors[i] + '"></div>'
                + '<span style="font-size:12px;color:var(--muted);flex:1;">' + l + '</span>'
                + '<span style="font-family:var(--font-mono);font-size:12px;color:var(--text);font-weight:500;">' + values[i].toLocaleString('en-IN') + ' (' + pct + '%)</span>'
                + '</div>';
        });
        document.getElementById('genderLegend').innerHTML = legendHtml;

        var ctx = document.getElementById('genderChart');
        if (!ctx) return;

        genderChartInst = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverOffset: 4 }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c) { return ' ' + c.label + ': ' + c.raw; } } }
                },
                animation: { animateRotate: true, duration: 1000 }
            }
        });
    }

    /* ── Calendar ── */
    function storeCalendarEvents(events) {
        calEventDates = {};
        (events || []).forEach(function(e) {
            if (!e.date) return;
            if (!calEventDates[e.date]) calEventDates[e.date] = [];
            calEventDates[e.date].push(e.title);
        });
    }

    (function() {
        var today = new Date();
        window._dbCalY = today.getFullYear();
        window._dbCalM = today.getMonth();

        window._dbCalPrev = function() {
            window._dbCalM--;
            if (window._dbCalM < 0) { window._dbCalM = 11; window._dbCalY--; }
            renderCalendar(window._dbCalY, window._dbCalM);
        };
        window._dbCalNext = function() {
            window._dbCalM++;
            if (window._dbCalM > 11) { window._dbCalM = 0; window._dbCalY++; }
            renderCalendar(window._dbCalY, window._dbCalM);
        };

        renderCalendar(window._dbCalY, window._dbCalM);
    })();

    function renderCalendar(y, m) {
        var el = document.getElementById('miniCal');
        if (!el) return;
        var today = new Date();
        var days = ['Su','Mo','Tu','We','Th','Fr','Sa'];
        var mN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var first = new Date(y, m, 1).getDay();
        var total = new Date(y, m + 1, 0).getDate();
        var prevT = new Date(y, m, 0).getDate();
        var tDay = (y === today.getFullYear() && m === today.getMonth()) ? today.getDate() : -1;

        var h = '<div class="mini-cal-header"><span class="mini-cal-month">' + mN[m] + ' ' + y + '</span>';
        h += '<div class="mini-cal-nav">';
        h += '<button onclick="window._dbCalPrev()"><i class="fas fa-chevron-left" style="font-size:10px"></i></button>';
        h += '<button onclick="window._dbCalNext()"><i class="fas fa-chevron-right" style="font-size:10px"></i></button>';
        h += '</div></div><div class="cal-grid">';
        days.forEach(function(d) { h += '<div class="cal-day-name">' + d + '</div>'; });
        for (var i = first - 1; i >= 0; i--) h += '<div class="cal-day other">' + (prevT - i) + '</div>';
        for (var d = 1; d <= total; d++) {
            var dateStr = y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            var cls = 'cal-day';
            if (d === tDay) cls += ' today';
            else if (calEventDates[dateStr]) cls += ' has-event';
            h += '<div class="' + cls + '">' + d + '</div>';
        }
        var rem = (first + total) % 7;
        if (rem) rem = 7 - rem;
        for (var n = 1; n <= rem; n++) h += '<div class="cal-day other">' + n + '</div>';
        h += '</div>';
        el.innerHTML = h;

        // Show upcoming events for this month
        var evtListEl = document.getElementById('calEventList');
        if (!evtListEl) return;
        var prefix = y + '-' + String(m + 1).padStart(2, '0');
        var monthEvents = [];
        Object.keys(calEventDates).forEach(function(date) {
            if (date.indexOf(prefix) === 0) {
                calEventDates[date].forEach(function(title) {
                    monthEvents.push({ date: date, title: title });
                });
            }
        });
        monthEvents.sort(function(a, b) { return a.date.localeCompare(b.date); });

        if (!monthEvents.length) {
            evtListEl.innerHTML = '<div style="text-align:center;font-size:11px;color:var(--muted);padding:8px;">No events this month</div>';
            return;
        }

        var colors = ['', 'blue', 'rose'];
        evtListEl.innerHTML = monthEvents.slice(0, 4).map(function(e, i) {
            var parts = e.date.split('-');
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            var label = parseInt(parts[2]) + ' ' + months[parseInt(parts[1]) - 1];
            var colorCls = colors[i % 3];
            return '<div style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;border-left:3px solid var(--' + (colorCls || 'brand') + ');background:' + (colorCls === 'blue' ? 'rgba(74,181,227,.06)' : colorCls === 'rose' ? 'rgba(224,92,111,.06)' : 'var(--brand-dim)') + ';">'
                + '<span style="font-family:var(--font-mono);font-size:10px;color:var(--muted);min-width:44px;">' + label + '</span>'
                + '<span style="font-size:11.5px;color:var(--text);">' + escHtml(e.title) + '</span>'
                + '</div>';
        }).join('');
    }

    /* ── Theme observer ── */
    if (window.MutationObserver) {
        new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'data-theme') {
                    var c = getC();
                    [feeChartInst, classChartInst].forEach(function(chart) {
                        if (!chart) return;
                        if (chart.options.scales.x) { chart.options.scales.x.grid.color = c.grid; chart.options.scales.x.ticks.color = c.tick; }
                        if (chart.options.scales.y) { chart.options.scales.y.grid.color = c.grid; chart.options.scales.y.ticks.color = c.tick; }
                        if (chart.options.plugins.legend && chart.options.plugins.legend.labels) chart.options.plugins.legend.labels.color = c.legend;
                        chart.update();
                    });
                }
            }
        }).observe(root, { attributes: true });
    }

})();
</script>
