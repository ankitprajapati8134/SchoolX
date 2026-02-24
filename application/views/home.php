<!-- Content Wrapper -->
<div class="content-wrapper db-root" id="dbRoot">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

        /* ═══════════════════════════════════════════════
        BRAND CONSTANTS  (never change with theme)
        ═══════════════════════════════════════════════ */
        .db-root {
            --brand: #F5AF00;
            --brand2: #D49700;
            --brand3: #FFC93C;
            --brand-dim: rgba(245, 175, 0, 0.11);
            --brand-ring: rgba(245, 175, 0, 0.22);
            --blue: #4AB5E3;
            --green: #3DD68C;
            --rose: #E05C6F;
            --amber: #C9A84C;
            --r: 16px;
            --r-sm: 10px;
            --font-display: 'Fraunces', serif;
            --font-body: 'DM Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        /* ═══════════════════════════════════════════════
   DARK THEME  (default / night)
═══════════════════════════════════════════════ */
        .db-root,
        .db-root[data-theme="dark"] {
            --bg: #12100A;
            --bg2: #1A1710;
            --bg3: #221E14;
            --bg4: #2C2718;
            --card: rgba(30, 25, 14, 0.90);
            --border: rgba(245, 175, 0, 0.09);
            --border2: rgba(245, 175, 0, 0.17);
            --text: #F0E8D5;
            --text2: #C8B98A;
            --muted: #7A6E54;
            --muted2: #5A5040;
            --heading: #FFFFFF;
            --shadow: 0 4px 32px rgba(0, 0, 0, 0.50);
            --grid-line: rgba(245, 175, 0, 0.025);
            --stat-hover: radial-gradient(circle at 50% 0%, rgba(245, 175, 0, .06), transparent 70%);
            --leave-hover: rgba(245, 175, 0, 0.04);
            --cal-hover: rgba(245, 175, 0, 0.09);
            --chart-grid: rgba(245, 175, 0, 0.05);
            --chart-tick: #7A6E54;
            --toggle-bg: rgba(30, 25, 14, 0.95);
            --toggle-bd: rgba(245, 175, 0, 0.25);
        }

        /* ═══════════════════════════════════════════════
   LIGHT THEME  (day)
═══════════════════════════════════════════════ */
        .db-root[data-theme="light"] {
            --bg: #FBF7EE;
            --bg2: #F5EDD8;
            --bg3: #EEE4C8;
            --bg4: #E8D9B0;
            --card: #FFFFFF;
            --border: rgba(180, 140, 0, 0.14);
            --border2: rgba(180, 140, 0, 0.24);
            --text: #2C2206;
            --text2: #6B5320;
            --muted: #9A8050;
            --muted2: #BBA060;
            --heading: #1A1400;
            --shadow: 0 2px 16px rgba(0, 0, 0, 0.09), 0 1px 4px rgba(0, 0, 0, 0.06);
            --grid-line: rgba(180, 140, 0, 0.06);
            --stat-hover: radial-gradient(circle at 50% 0%, rgba(245, 175, 0, .07), transparent 70%);
            --leave-hover: rgba(245, 175, 0, 0.05);
            --cal-hover: rgba(245, 175, 0, 0.10);
            --chart-grid: rgba(180, 140, 0, 0.08);
            --chart-tick: #9A8050;
            --toggle-bg: #FFFFFF;
            --toggle-bd: rgba(180, 140, 0, 0.30);
        }

        /* ═══════════════════════════════════════════════
   SMOOTH TRANSITIONS
═══════════════════════════════════════════════ */
        .db-root.t-ready,
        .db-root.t-ready * {
            transition:
                background-color .32s ease,
                background .32s ease,
                border-color .32s ease,
                color .32s ease,
                box-shadow .32s ease;
        }

        /* Don't transition canvas / animations */
        .db-root.t-ready canvas,
        .db-root.t-ready .stat-card::before,
        .db-root.t-ready .db-root::before {
            transition: none;
        }

        /* ═══════════════════════════════════════════════
   RESET / ROOT
═══════════════════════════════════════════════ */
        .db-root *,
        .db-root *::before,
        .db-root *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .db-root {
            font-family: var(--font-body);
            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
            padding: 0;
            position: relative;
        }

        /* Grid overlay */
        .db-root::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 0;
        }

        /* ═══════════════════════════════════════════════
   THEME TOGGLE BUTTON  (inside header-right)
═══════════════════════════════════════════════ */
        .db-theme-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-bd);
            border-radius: 8px;
            padding: 7px 13px 7px 10px;
            cursor: pointer;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 500;
            color: var(--text2);
            letter-spacing: .5px;
            box-shadow: var(--shadow);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .db-theme-btn:hover {
            border-color: var(--brand);
            color: var(--text);
        }

        .db-theme-btn .t-ico-sun,
        .db-theme-btn .t-ico-moon {
            font-size: 13px;
        }

        .db-root[data-theme="dark"] .t-ico-sun {
            display: none;
        }

        .db-root[data-theme="dark"] .t-ico-moon {
            display: block;
            color: #A5B4FC;
        }

        .db-root[data-theme="light"] .t-ico-moon {
            display: none;
        }

        .db-root[data-theme="light"] .t-ico-sun {
            display: block;
            color: var(--brand);
        }

        /* Toggle track + knob */
        .t-track {
            width: 36px;
            height: 20px;
            border-radius: 20px;
            background: var(--bg3);
            border: 1px solid var(--border2);
            position: relative;
            flex-shrink: 0;
        }

        .t-knob {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .db-root[data-theme="dark"] .t-knob {
            transform: translateX(0);
        }

        .db-root[data-theme="light"] .t-knob {
            transform: translateX(16px);
        }

        .t-auto-tag {
            font-size: 9px;
            background: var(--brand-dim);
            color: var(--brand);
            border: 1px solid var(--brand-ring);
            padding: 1px 6px;
            border-radius: 4px;
            letter-spacing: .3px;
        }

        /* ═══════════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════════ */
        .db-header {
            position: relative;
            z-index: 2;
            padding: 0 32px 22px;
            padding-top: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1px solid var(--border);
        }

        .db-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--brand);
        }

        .db-header-left h1 {
            font-family: var(--font-display);
            font-size: clamp(22px, 3vw, 30px);
            font-weight: 700;
            letter-spacing: -.5px;
            line-height: 1.1;
            color: var(--heading);
        }

        .db-header-left h1 em {
            font-style: italic;
            color: var(--brand);
        }

        .db-header-left p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 4px;
            letter-spacing: .3px;
        }

        .db-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .db-date-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg3);
            border: 1px solid var(--border2);
            border-radius: 50px;
            padding: 8px 16px;
            font-size: 12.5px;
            color: var(--text2);
        }

        .db-date-pill .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 0 8px var(--brand);
            animation: dbPulse 2s ease infinite;
        }

        @keyframes dbPulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .35
            }
        }

        /* ═══════════════════════════════════════════════
   MAIN GRID
═══════════════════════════════════════════════ */
        .db-body {
            position: relative;
            z-index: 1;
            padding: 24px 32px 40px;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }

        /* ═══════════════════════════════════════════════
   STAT CARDS
═══════════════════════════════════════════════ */
        .db-stats {
            grid-column: 1/-1;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media(max-width:900px) {
            .db-stats {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(max-width:560px) {
            .db-stats {
                grid-template-columns: 1fr
            }
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 22px 22px 18px;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            cursor: default;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--r);
            opacity: 0;
            transition: opacity .25s;
            background: var(--stat-hover);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .stat-card:hover::after {
            opacity: 1;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 3px;
            border-radius: 4px;
        }

        .stat-card.c-brand::before {
            background: var(--brand);
            box-shadow: 0 0 12px var(--brand);
        }

        .stat-card.c-blue::before {
            background: var(--blue);
            box-shadow: 0 0 12px var(--blue);
        }

        .stat-card.c-rose::before {
            background: var(--rose);
            box-shadow: 0 0 12px var(--rose);
        }

        .stat-card.c-amber::before {
            background: var(--amber);
            box-shadow: 0 0 12px var(--amber);
        }

        .stat-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-icon.c-brand {
            background: var(--brand-dim);
            color: var(--brand);
        }

        .stat-icon.c-blue {
            background: rgba(74, 181, 227, .12);
            color: var(--blue);
        }

        .stat-icon.c-rose {
            background: rgba(224, 92, 111, .12);
            color: var(--rose);
        }

        .stat-icon.c-amber {
            background: rgba(201, 168, 76, .12);
            color: var(--amber);
        }

        .stat-trend {
            font-family: var(--font-mono);
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-trend.up {
            background: rgba(61, 214, 140, .1);
            color: var(--green);
        }

        .stat-trend.down {
            background: rgba(224, 92, 111, .1);
            color: var(--rose);
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            color: var(--heading);
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .stat-footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-footer a {
            font-size: 11px;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color .2s;
        }

        .stat-footer a:hover {
            color: var(--brand);
        }

        .stat-bar {
            height: 3px;
            border-radius: 2px;
            background: var(--border);
            width: 80px;
            overflow: hidden;
        }

        .stat-bar-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 1s ease;
        }

        .c-brand .stat-bar-fill {
            background: var(--brand);
        }

        .c-blue .stat-bar-fill {
            background: var(--blue);
        }

        .c-rose .stat-bar-fill {
            background: var(--rose);
        }

        .c-amber .stat-bar-fill {
            background: var(--amber);
        }

        /* ═══════════════════════════════════════════════
   PANEL CARDS  (shared shell)
═══════════════════════════════════════════════ */
        .db-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 24px;
            backdrop-filter: blur(12px);
        }

        .db-fee-chart {
            grid-column: 1/9;
        }

        .db-leave {
            grid-column: 9/-1;
        }

        .db-attendance {
            grid-column: 1/5;
        }

        .db-quick {
            grid-column: 5/9;
        }

        .db-calendar {
            grid-column: 9/-1;
        }

        @media(max-width:1100px) {

            .db-fee-chart,
            .db-leave {
                grid-column: 1/-1;
            }
        }

        @media(max-width:900px) {

            .db-attendance,
            .db-quick,
            .db-calendar {
                grid-column: 1/-1;
            }
        }

        /* ─── Card heading ─── */
        .card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title-txt {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 600;
            color: var(--heading);
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        .card-badge {
            font-size: 10px;
            font-family: var(--font-mono);
            padding: 3px 10px;
            border-radius: 50px;
            background: var(--brand-dim);
            color: var(--brand);
            border: 1px solid var(--brand-ring);
        }

        .card-badge.rose {
            background: rgba(224, 92, 111, .1);
            color: var(--rose);
            border-color: rgba(224, 92, 111, .2);
        }

        /* ─── Fee totals ─── */
        .fee-totals {
            display: flex;
            gap: 24px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .fee-total-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .fee-total-num {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--heading);
        }

        .fee-total-lbl {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ─── Leave list ─── */
        .leave-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .leave-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            background: var(--leave-hover);
            border: 1px solid var(--border);
            border-radius: 10px;
            transition: border-color .2s;
        }

        .leave-item:hover {
            border-color: var(--brand-ring);
        }

        .leave-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
        }

        .leave-info {
            flex: 1;
        }

        .leave-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .leave-dates {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
            font-family: var(--font-mono);
        }

        .leave-badge {
            font-size: 10px;
            padding: 3px 9px;
            border-radius: 50px;
            white-space: nowrap;
        }

        .leave-badge.pending {
            background: var(--brand-dim);
            color: var(--brand);
            border: 1px solid var(--brand-ring);
        }

        .leave-badge.approved {
            background: rgba(61, 214, 140, .1);
            color: var(--green);
            border: 1px solid rgba(61, 214, 140, .2);
        }

        /* ─── Attendance donut ─── */
        .attendance-wrap {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .attendance-canvas-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .attendance-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .attendance-pct {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 700;
            color: var(--heading);
        }

        .attendance-pct-sub {
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .attendance-legend {
            flex: 1;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .legend-item:last-child {
            border-bottom: none;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .legend-lbl {
            font-size: 12px;
            color: var(--muted);
            flex: 1;
        }

        .legend-val {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text);
            font-weight: 500;
        }

        /* ─── Quick buttons ─── */
        .quick-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .quick-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 18px 12px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .3px;
            text-transform: uppercase;
            color: var(--text);
            border: 1px solid var(--border);
            transition: all .2s;
            position: relative;
            overflow: hidden;
        }

        .quick-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity .2s;
            background: rgba(255, 255, 255, .04);
        }

        .quick-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: var(--text);
            text-decoration: none;
        }

        .quick-btn:hover::before {
            opacity: 1;
        }

        .quick-btn i {
            font-size: 22px;
            margin-bottom: 2px;
        }

        .quick-btn.qb-brand {
            background: var(--brand-dim);
            border-color: var(--brand-ring);
        }

        .quick-btn.qb-brand2 {
            background: rgba(201, 168, 76, .10);
            border-color: rgba(201, 168, 76, .22);
        }

        .quick-btn.qb-blue {
            background: rgba(74, 181, 227, .10);
            border-color: rgba(74, 181, 227, .22);
        }

        .quick-btn.qb-rose {
            background: rgba(224, 92, 111, .10);
            border-color: rgba(224, 92, 111, .22);
        }

        .quick-btn.qb-brand i {
            color: var(--brand);
        }

        .quick-btn.qb-brand2 i {
            color: var(--amber);
        }

        .quick-btn.qb-blue i {
            color: var(--blue);
        }

        .quick-btn.qb-rose i {
            color: var(--rose);
        }

        /* ─── Calendar ─── */
        .mini-cal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .mini-cal-nav {
            display: flex;
            gap: 4px;
        }

        .mini-cal-nav button {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .15s;
        }

        .mini-cal-nav button:hover {
            background: var(--bg3);
            color: var(--brand);
            border-color: var(--brand-ring);
        }

        .mini-cal-month {
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 600;
            color: var(--heading);
        }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .cal-day-name {
            text-align: center;
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 4px 0 8px;
            font-family: var(--font-mono);
        }

        .cal-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all .15s;
            color: var(--text);
        }

        .cal-day:hover {
            background: var(--cal-hover);
            color: var(--brand);
        }

        .cal-day.other {
            color: var(--muted2);
        }

        .cal-day.today {
            background: var(--brand);
            color: #12100A;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(245, 175, 0, .45);
        }

        .cal-day.has-event {
            position: relative;
        }

        .cal-day.has-event::after {
            content: '';
            position: absolute;
            bottom: 3px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--rose);
        }

        .event-list {
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .event-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            border-left: 3px solid var(--brand);
            background: var(--brand-dim);
        }

        .event-item.blue {
            border-color: var(--blue);
            background: rgba(74, 181, 227, .06);
        }

        .event-item.rose {
            border-color: var(--rose);
            background: rgba(224, 92, 111, .06);
        }

        .event-date {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--muted);
            min-width: 32px;
        }

        .event-name {
            font-size: 12px;
            color: var(--text);
        }

        /* ═══════════════════════════════════════════════
   ANIMATIONS
═══════════════════════════════════════════════ */
        @keyframes dbFadeUp {
            from {
                opacity: 0;
                transform: translateY(16px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .stat-card {
            animation: dbFadeUp .5s ease both;
        }

        .stat-card:nth-child(1) {
            animation-delay: .05s;
        }

        .stat-card:nth-child(2) {
            animation-delay: .10s;
        }

        .stat-card:nth-child(3) {
            animation-delay: .15s;
        }

        .stat-card:nth-child(4) {
            animation-delay: .20s;
        }

        .db-fee-chart {
            animation: dbFadeUp .5s .25s ease both;
        }

        .db-leave {
            animation: dbFadeUp .5s .30s ease both;
        }

        .db-attendance {
            animation: dbFadeUp .5s .35s ease both;
        }

        .db-quick {
            animation: dbFadeUp .5s .40s ease both;
        }

        .db-calendar {
            animation: dbFadeUp .5s .45s ease both;
        }
    </style>

    <!-- ─── PAGE HEADER ─── -->
    <div class="db-header">
        <div class="db-header-left">
            <h1>Good <span id="dbGreeting">Morning</span>, <em><?= $admin_name ?></em></h1>
            <p><?= $school_name ?> &nbsp;·&nbsp; Session <?= $session_year ?> &nbsp;·&nbsp; Admin Dashboard</p>
        </div>
         <div class="db-header-right">
            
           <div class="db-date-pill">
                <span class="dot"></span>
                <span id="dbLiveDate"></span>
            </div>
            
            <button class="db-theme-btn" id="dbThemeBtn" title="Toggle theme (dbl-click = auto)">
                <div class="t-track">
                    <div class="t-knob"></div>
                </div>
                <i class="fas fa-sun  t-ico-sun"></i>
                <i class="fas fa-moon t-ico-moon"></i>
                <span id="dbThemeLabel">NIGHT</span>
                <span class="t-auto-tag" id="dbAutoTag">AUTO</span>
            </button>
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
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:72%"></div>
                    </div>
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
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:55%"></div>
                    </div>
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
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:85%"></div>
                    </div>
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
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:40%;background:var(--amber)"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── FEE CHART ── -->
        <div class="db-panel db-fee-chart">
            <div class="card-heading">
                <div>
                    <div class="card-title-txt">Fee Collection</div>
                    <div class="card-subtitle">Monthly overview · Current session</div>
                </div>
                <span class="card-badge">Live</span>
            </div>
            <div class="fee-totals">
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--brand)">₹92,000</span>
                    <span class="fee-total-lbl">Collected</span>
                </div>
                <div style="width:1px;background:var(--border);align-self:stretch"></div>
                <div class="fee-total-item">
                    <span class="fee-total-num" style="color:var(--rose)">₹28,400</span>
                    <span class="fee-total-lbl">Pending</span>
                </div>
                <div style="width:1px;background:var(--border);align-self:stretch"></div>
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
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#D49700,#C9A84C);color:#12100A">R</div>
                    <div class="leave-info">
                        <div class="leave-name">Rahul Sharma</div>
                        <div class="leave-dates">12 Jan – 14 Jan</div>
                    </div>
                    <span class="leave-badge pending">Pending</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#C9A84C,#E05C6F);color:#fff">P</div>
                    <div class="leave-info">
                        <div class="leave-name">Priya Singh</div>
                        <div class="leave-dates">15 Jan – 16 Jan</div>
                    </div>
                    <span class="leave-badge approved">Approved</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#F5AF00,#D49700);color:#12100A">A</div>
                    <div class="leave-info">
                        <div class="leave-name">Amit Verma</div>
                        <div class="leave-dates">18 Jan – 19 Jan</div>
                    </div>
                    <span class="leave-badge pending">Pending</span>
                </div>
                <div class="leave-item">
                    <div class="leave-avatar" style="background:linear-gradient(135deg,#4AB5E3,#D49700);color:#fff">S</div>
                    <div class="leave-info">
                        <div class="leave-name">Sunita Rawat</div>
                        <div class="leave-dates">20 Jan – 21 Jan</div>
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
                    <div class="card-subtitle" id="todayDate">—</div>
                </div>
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

        <!-- ── QUICK LINKS ── -->
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
                    <span class="event-name">PTM – All Classes</span>
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
        var themeBtn = document.getElementById('dbThemeBtn');
        var tLabel = document.getElementById('dbThemeLabel');
        var tAuto = document.getElementById('dbAutoTag');
        var grtEl = document.getElementById('dbGreeting');
        var dlEl = document.getElementById('dbLiveDate');
        var tdEl = document.getElementById('todayDate');

        var manualOverride = false;
        var feeChartInst = null;
        var attendChartInst = null;

        /* ── helpers ── */
        function getHour() {
            return new Date().getHours();
        }

        function getTimeTheme() {
            return (getHour() >= 6 && getHour() < 18) ? 'light' : 'dark';
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

        /* ── apply theme ── */
        function applyTheme(theme, isManual) {
            root.setAttribute('data-theme', theme);
            var isDark = theme === 'dark';
            tLabel.textContent = isDark ? 'NIGHT' : 'DAY';
            tAuto.textContent = isManual ? 'MANUAL' : 'AUTO';
            tAuto.style.opacity = isManual ? '0.5' : '1';

            if (isManual) {
                localStorage.setItem('graderadmin_db_theme', theme);
                localStorage.setItem('graderadmin_db_manual', '1');
            }

            /* Update chart colors to match theme */
            updateChartColors(theme);
        }

        /* ── init ── */
        var savedTheme = localStorage.getItem('graderadmin_db_theme');
        var savedManual = localStorage.getItem('graderadmin_db_manual') === '1';

        if (savedManual && savedTheme) {
            manualOverride = true;
            applyTheme(savedTheme, true);
        } else {
            applyTheme(getTimeTheme(), false);
        }

        tick();

        /* Enable transitions after first paint */
        requestAnimationFrame(function() {
            setTimeout(function() {
                root.classList.add('t-ready');
            }, 60);
        });

        /* ── manual click ── */
        themeBtn.addEventListener('click', function() {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            manualOverride = true;
            applyTheme(next, true);
        });

        /* ── double-click resets to AUTO ── */
        themeBtn.addEventListener('dblclick', function() {
            manualOverride = false;
            localStorage.removeItem('graderadmin_db_theme');
            localStorage.removeItem('graderadmin_db_manual');
            tAuto.textContent = 'AUTO';
            tAuto.style.opacity = '1';
            applyTheme(getTimeTheme(), false);
        });

        /* ── auto re-check every 60s ── */
        setInterval(function() {
            if (!manualOverride) applyTheme(getTimeTheme(), false);
            tick();
        }, 60000);

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
                                    return ' ' + ctx.dataset.label + ': ₹' + ctx.raw.toLocaleString('en-IN');
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
                                    return '₹' + v.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }

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