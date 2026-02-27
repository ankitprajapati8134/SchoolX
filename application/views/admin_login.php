<div class="content-wrapper">
    <div class="page">
        <div class="card">

            <!-- ══ LEFT PANEL ══ -->
            <div class="panel-l">

                <div class="brand-mark">
                    <div class="brand-icon">🏫</div>
                    <div>
                        <div class="brand-name">SchoolXAdmin</div>
                        <div class="brand-sub">School Management System</div>
                    </div>
                </div>

                <div class="copy">
                    <div class="copy-label">Admin Portal</div>
                    <h1>Manage your<br><em>school smarter.</em></h1>
                    <p>Students, staff, attendance, fees — everything in one place.</p>
                </div>

                <div class="stats">
                    <div class="stat">
                        <div class="stat-num">3,654</div>
                        <div class="stat-lbl">Students</div>
                    </div>
                    <div class="stat">
                        <div class="stat-num">284</div>
                        <div class="stat-lbl">Teachers</div>
                    </div>
                    <div class="stat">
                        <div class="stat-num">162</div>
                        <div class="stat-lbl">Classes</div>
                    </div>
                </div>

                <div class="deco">
                    <i class="fas fa-graduation-cap"></i>
                    <i class="fas fa-book-open"></i>
                    <i class="fas fa-chalkboard-teacher"></i>
                    <i class="fas fa-school"></i>
                    <i class="fas fa-calendar-check"></i>
                    <i class="fas fa-chart-bar"></i>
                    <i class="fas fa-money-bill-wave"></i>
                    <i class="fas fa-users"></i>
                </div>

            </div>
            <!-- /LEFT PANEL -->

            <!-- ══ RIGHT PANEL ══ -->
            <div class="panel-r">

                <div class="form-head">
                    <div class="form-kicker">Secure Access</div>
                    <h2>Welcome back 👋</h2>
                    <p>Sign in to your admin account to continue.</p>
                    <div class="mode-pill">
                        <span class="mode-dot"></span>
                        <span id="modePillText">Day mode — auto</span>
                    </div>
                </div>

                <!-- Flash error -->
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span><?= htmlspecialchars($this->session->flashdata('error')) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Flash success (e.g. logout) -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-circle-check"></i>
                        <span><?= htmlspecialchars($this->session->flashdata('success')) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="post" action="<?= base_url('admin_login/check_credentials') ?>" class="login-form"
                    id="loginForm">
                    <!-- ADD THIS LINE RIGHT HERE -->
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">


                    <div class="fgroup">
                        <div class="flabel"><i class="fas fa-id-badge"></i> Admin ID</div>
                        <div class="finput-wrap">
                            <input type="text" name="admin_id" id="admin_id" placeholder="Enter your Admin ID" required
                                autocomplete="username">
                            <i class="ficon fas fa-user"></i>
                        </div>
                    </div>

                    <div class="fgroup">
                        <div class="flabel"><i class="fas fa-school"></i> School ID</div>
                        <div class="finput-wrap">
                            <input type="text" name="school_id" id="school_id" placeholder="Enter your School ID"
                                required autocomplete="organization">
                            <i class="ficon fas fa-building"></i>
                        </div>
                    </div>

                    <div class="fdivider"></div>

                    <div class="fgroup">
                        <div class="flabel"><i class="fas fa-lock"></i> Password</div>
                        <div class="finput-wrap">
                            <input type="password" name="password" id="password" placeholder="Enter your password"
                                required autocomplete="current-password">
                            <i class="ficon fas fa-eye pw-toggle" id="pwToggle"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <div class="btn-inner">
                            <div class="spin"></div>
                            <span class="btn-text">
                                <i class="fas fa-arrow-right-to-bracket"></i>
                                Sign In to Dashboard
                            </span>
                        </div>
                    </button>

                </form>

                <div class="form-foot">
                    <div class="secure">
                        <i class="fas fa-shield-halved"></i>
                        256-bit encrypted
                    </div>
                    <a href="<?= base_url('admin_login/forgot_password') ?>" class="forgot-link">
                        Forgot password?
                    </a>
                </div>

            </div>
            <!-- /RIGHT PANEL -->

        </div>
    </div>
    <!-- ── THEME TOGGLE ── -->
    <button class="theme-btn" id="themeBtn" title="Toggle theme">
        <i class="fas fa-sun  ico-sun"></i>
        <i class="fas fa-moon ico-moon"></i>
        <span id="themeLabel">DAY</span>
        <span class="auto-tag" id="autoTag">AUTO</span>
    </button>
</div>

<script>
    const html = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const themeLabel = document.getElementById('themeLabel');
    const autoTag = document.getElementById('autoTag');
    const pillText = document.getElementById('modePillText');

    let manualOverride = false; // becomes true when user clicks toggle

    function getTimeTheme() {
        const h = new Date().getHours();
        return (h >= 6 && h < 18) ? 'light' : 'dark';
    }

    function applyTheme(theme, isManual = false) {
        html.setAttribute('data-theme', theme);

        const isDark = theme === 'dark';
        themeLabel.textContent = isDark ? 'NIGHT' : 'DAY';
        autoTag.textContent = isManual ? 'MANUAL' : 'AUTO';
        autoTag.style.opacity = isManual ? '0.55' : '1';

        const timeStr = new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        pillText.textContent = isDark ?
            `Night mode · ${timeStr}` :
            `Day mode · ${timeStr}`;

        if (isManual) {
            localStorage.setItem('graderadmin_theme', theme);
            localStorage.setItem('graderadmin_manual', '1');
        }
    }

    // ── Init: check if user had a manual preference ──
    const savedTheme = localStorage.getItem('graderadmin_theme');
    const savedManual = localStorage.getItem('graderadmin_manual') === '1';

    if (savedManual && savedTheme) {
        manualOverride = true;
        applyTheme(savedTheme, true);
    } else {
        applyTheme(getTimeTheme(), false);
    }

    // Enable smooth transitions AFTER first paint (avoids flash)
    requestAnimationFrame(() => {
        setTimeout(() => document.body.classList.add('t-ready'), 50);
    });

    // ── Manual toggle ──
    themeBtn.addEventListener('click', () => {
        const curr = html.getAttribute('data-theme');
        const next = curr === 'dark' ? 'light' : 'dark';
        manualOverride = true;
        applyTheme(next, true);
    });

    // ── Auto re-check every minute (follows clock if no manual override) ──
    setInterval(() => {
        if (!manualOverride) {
            applyTheme(getTimeTheme(), false);
        }
        // Update the time display in the pill every minute regardless
        const isDark = html.getAttribute('data-theme') === 'dark';
        const timeStr = new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        pillText.textContent = isDark ? `Night mode · ${timeStr}` : `Day mode · ${timeStr}`;
    }, 60000);

    // ── Double-click toggle button resets to AUTO ──
    themeBtn.addEventListener('dblclick', () => {
        manualOverride = false;
        localStorage.removeItem('graderadmin_theme');
        localStorage.removeItem('graderadmin_manual');
        autoTag.textContent = 'AUTO';
        autoTag.style.opacity = '1';
        applyTheme(getTimeTheme(), false);
    });

    /* ══════════════════════════════════════════
       PASSWORD SHOW / HIDE
    ══════════════════════════════════════════ */
    const pwToggle = document.getElementById('pwToggle');
    const pwInput = document.getElementById('password');

    pwToggle.addEventListener('click', () => {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        pwToggle.classList.toggle('fa-eye', !show);
        pwToggle.classList.toggle('fa-eye-slash', show);
    });

    /* ══════════════════════════════════════════
       SUBMIT LOADING STATE
    ══════════════════════════════════════════ */
    document.getElementById('loginForm').addEventListener('submit', () => {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
        setTimeout(() => {
            btn.classList.remove('loading');
            btn.disabled = false;
        }, 6000);
    });
</script>

<style>
    :root {
        --brand: #F5AF00;
        --brand-dark: #D49700;
        --brand-light: #FFC93C;
        --brand-dim: rgba(245, 175, 0, 0.10);
        --brand-ring: rgba(245, 175, 0, 0.22);
        --font: 'Plus Jakarta Sans', sans-serif;
        --mono: 'JetBrains Mono', monospace;
    }

    /* LIGHT THEME */
    [data-theme="light"] {
        --bg: #F8F9FC;
        --surface: #FFFFFF;
        --surface2: #F2F4F8;
        --surface3: #E8EBF2;
        --border: #E2E5EE;
        --border2: #D0D5E2;
        --text: #111520;
        --text2: #3D4460;
        --muted: #8A92AA;
        --muted-light: #C5CAD8;
        --input-bg: #F2F4F8;
        --input-focus: #FFFFFF;
        --panel-bg: #111520;
        --panel-text: #FFFFFF;
        --panel-sub: rgba(255, 255, 255, 0.45);
        --panel-border: rgba(255, 255, 255, 0.08);
        --panel-card: rgba(255, 255, 255, 0.07);
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 32px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 2px 8px rgba(0, 0, 0, 0.06), 0 20px 60px rgba(0, 0, 0, 0.10);
        --red: #E5484D;
        --red-bg: rgba(229, 72, 77, 0.06);
        --red-border: rgba(229, 72, 77, 0.18);
        --green: #12A05C;
    }

    /* DARK THEME */
    [data-theme="dark"] {
        --bg: #0C0E14;
        --surface: #141720;
        --surface2: #1B1F2E;
        --surface3: #222740;
        --border: rgba(255, 255, 255, 0.07);
        --border2: rgba(255, 255, 255, 0.11);
        --text: #EDF0F8;
        --text2: #9BA3BF;
        --muted: #454E6A;
        --muted-light: #353D55;
        --input-bg: #1B1F2E;
        --input-focus: #222740;
        --panel-bg: #111520;
        --panel-text: #FFFFFF;
        --panel-sub: rgba(255, 255, 255, 0.4);
        --panel-border: rgba(255, 255, 255, 0.07);
        --panel-card: rgba(255, 255, 255, 0.06);
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.3), 0 8px 32px rgba(0, 0, 0, 0.4);
        --shadow-lg: 0 4px 16px rgba(0, 0, 0, 0.4), 0 24px 64px rgba(0, 0, 0, 0.5);
        --red: #F87171;
        --red-bg: rgba(248, 113, 113, 0.07);
        --red-border: rgba(248, 113, 113, 0.18);
        --green: #34D399;
    }

    /* ─────────────────────────────────────────
       SMOOTH THEME TRANSITION
    ───────────────────────────────────────── */
    *,
    *::before,
    *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .t-ready * {
        transition:
            background-color .3s ease,
            border-color .3s ease,
            color .3s ease,
            box-shadow .3s ease;
    }

    /* ─────────────────────────────────────────
       BASE
    ───────────────────────────────────────── */
    html,
    body {
        height: 100%;
        font-family: var(--font);
        background: var(--bg);
        color: var(--text);
        overflow: hidden;
    }

    /* ─────────────────────────────────────────
       SUBTLE BACKGROUND PATTERN
    ───────────────────────────────────────── */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: 0;
        background-image:
            radial-gradient(circle, var(--border) 1px, transparent 1px);
        background-size: 28px 28px;
        opacity: 0.6;
        pointer-events: none;
    }

    [data-theme="dark"] body::before {
        opacity: 0.35;
    }

    /* ─────────────────────────────────────────
       THEME TOGGLE  — top right
    ───────────────────────────────────────── */
    .theme-btn {
        position: fixed;
        top: 20px;
        right: 22px;
        z-index: 300;
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--surface);
        border: 1px solid var(--border2);
        border-radius: 8px;
        padding: 7px 13px 7px 10px;
        cursor: pointer;
        box-shadow: var(--shadow);
        font-family: var(--mono);
        font-size: 11px;
        font-weight: 500;
        color: var(--text2);
        letter-spacing: 0.5px;
        animation: fadeDown .5s .4s ease both;
    }

    .theme-btn:hover {
        border-color: var(--brand);
        color: var(--text);
    }

    /* Icon swap */
    .theme-btn .ico-sun,
    .theme-btn .ico-moon {
        font-size: 13px;
    }

    [data-theme="light"] .ico-moon {
        display: none;
    }

    [data-theme="light"] .ico-sun {
        display: block;
        color: var(--brand);
    }

    [data-theme="dark"] .ico-sun {
        display: none;
    }

    [data-theme="dark"] .ico-moon {
        display: block;
        color: #A5B4FC;
    }

    /* Auto tag */
    .auto-tag {
        font-size: 9px;
        background: var(--brand-dim);
        color: var(--brand);
        border: 1px solid var(--brand-ring);
        padding: 1px 6px;
        border-radius: 4px;
        font-family: var(--mono);
        letter-spacing: .3px;
    }

    /* ─────────────────────────────────────────
       PAGE LAYOUT
    ───────────────────────────────────────── */
    .page {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    /* ─────────────────────────────────────────
       CARD
    ───────────────────────────────────────── */
    .card {
        display: flex;
        width: 100%;
        max-width: 940px;
        min-height: 560px;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--border2);
        box-shadow: var(--shadow-lg);
        animation: riseIn .65s cubic-bezier(.16, 1, .3, 1) both;
    }

    @keyframes riseIn {
        from {
            opacity: 0;
            transform: translateY(24px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ─────────────────────────────────────────
       LEFT PANEL  — always dark, brand accent
    ───────────────────────────────────────── */
    .panel-l {
        width: 380px;
        flex-shrink: 0;
        background: var(--panel-bg);
        padding: 52px 44px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        border-right: 1px solid var(--panel-border);
    }

    /* Brand accent bar — top */
    .panel-l::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--brand);
    }

    /* Subtle corner glow */
    .panel-l::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: -60px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(245, 175, 0, 0.08) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Brand mark */
    .brand-mark {
        display: flex;
        align-items: center;
        gap: 13px;
        animation: slideR .5s .1s ease both;
    }

    .brand-icon {
        width: 44px;
        height: 44px;
        background: var(--brand);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(245, 175, 0, 0.3);
    }

    .brand-name {
        font-size: 17px;
        font-weight: 800;
        color: #FFFFFF;
        letter-spacing: -.4px;
    }

    .brand-sub {
        font-size: 11px;
        color: var(--panel-sub);
        font-family: var(--mono);
        margin-top: 2px;
    }

    /* Copy block */
    .copy {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 44px 0 36px;
        animation: slideR .5s .18s ease both;
    }

    .copy-label {
        font-size: 10.5px;
        font-family: var(--mono);
        color: var(--brand);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .copy-label::before {
        content: '';
        width: 20px;
        height: 1.5px;
        background: var(--brand);
        border-radius: 2px;
    }

    .copy h1 {
        font-size: 34px;
        font-weight: 800;
        letter-spacing: -1.2px;
        line-height: 1.12;
        color: #FFFFFF;
        margin-bottom: 14px;
    }

    .copy h1 em {
        font-style: normal;
        color: var(--brand);
    }

    .copy p {
        font-size: 13.5px;
        color: var(--panel-sub);
        line-height: 1.7;
    }

    /* Stats */
    .stats {
        display: flex;
        gap: 12px;
        animation: slideR .5s .26s ease both;
    }

    .stat {
        flex: 1;
        background: var(--panel-card);
        border: 1px solid var(--panel-border);
        border-radius: 10px;
        padding: 12px 14px;
    }

    .stat-num {
        font-size: 19px;
        font-weight: 700;
        font-family: var(--mono);
        color: var(--brand);
    }

    .stat-lbl {
        font-size: 10.5px;
        color: var(--panel-sub);
        margin-top: 3px;
    }

    /* Deco grid of icons */
    .deco {
        position: absolute;
        bottom: 18px;
        right: 18px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 7px;
        opacity: 0.04;
    }

    .deco i {
        font-size: 20px;
        color: #FFF;
    }

    /* ─────────────────────────────────────────
       RIGHT PANEL  — form
    ───────────────────────────────────────── */
    .panel-r {
        flex: 1;
        background: var(--surface);
        padding: 52px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    /* Top accent line in dark mode */
    [data-theme="dark"] .panel-r::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--brand);
        opacity: 0.6;
    }

    /* Form heading */
    .form-head {
        margin-bottom: 30px;
        animation: slideL .5s .15s ease both;
    }

    .form-kicker {
        font-size: 10.5px;
        font-family: var(--mono);
        color: var(--muted);
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .form-head h2 {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.5px;
        color: var(--text);
    }

    .form-head p {
        font-size: 13px;
        color: var(--muted);
        margin-top: 5px;
    }

    /* Time-mode indicator */
    .mode-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--muted);
    }

    .mode-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    [data-theme="light"] .mode-dot {
        background: var(--brand);
        box-shadow: 0 0 6px rgba(245, 175, 0, .5);
    }

    [data-theme="dark"] .mode-dot {
        background: #A5B4FC;
        box-shadow: 0 0 6px rgba(165, 180, 252, .5);
    }

    /* Error alert */
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--red-bg);
        border: 1px solid var(--red-border);
        border-left: 3px solid var(--red);
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 22px;
        font-size: 13px;
        color: var(--red);
        animation: shakeX .4s ease both;
    }

    @keyframes shakeX {

        0%,
        100% {
            transform: translateX(0);
        }

        20%,
        60% {
            transform: translateX(-5px);
        }

        40%,
        80% {
            transform: translateX(5px);
        }
    }

    /* Success alert */
    .alert-success {
        background: rgba(18, 160, 92, 0.07);
        border-color: rgba(18, 160, 92, 0.20);
        border-left-color: var(--green);
        color: var(--green);
    }

    /* Form fields */
    .login-form {
        animation: slideL .5s .22s ease both;
    }

    .fgroup {
        margin-bottom: 16px;
    }

    .flabel {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text2);
        letter-spacing: .4px;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .flabel i {
        font-size: 10.5px;
        color: var(--brand);
    }

    .finput-wrap {
        position: relative;
    }

    .finput-wrap input {
        width: 100%;
        background: var(--input-bg);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 12px 44px 12px 14px;
        font-size: 14px;
        font-family: var(--font);
        color: var(--text);
        outline: none;
        caret-color: var(--brand);
    }

    .finput-wrap input::placeholder {
        color: var(--muted-light);
    }

    .finput-wrap input:focus {
        border-color: var(--brand);
        background: var(--input-focus);
        box-shadow: 0 0 0 3px var(--brand-ring);
    }

    .ficon {
        position: absolute;
        right: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        font-size: 13.5px;
        pointer-events: none;
    }

    .finput-wrap:focus-within .ficon {
        color: var(--brand);
    }

    .pw-toggle {
        pointer-events: all;
        cursor: pointer;
    }

    .pw-toggle:hover {
        color: var(--text);
    }

    /* Divider between School ID and Password */
    .fdivider {
        height: 1px;
        background: var(--border);
        margin: 4px 0 16px;
    }

    /* Submit */
    .btn-submit {
        width: 100%;
        padding: 13.5px;
        background: var(--brand);
        border: none;
        border-radius: 10px;
        color: #111520;
        font-family: var(--font);
        font-size: 14px;
        font-weight: 800;
        letter-spacing: .2px;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(245, 175, 0, 0.28);
        margin-top: 4px;
        position: relative;
        overflow: hidden;
    }

    .btn-submit:hover {
        background: var(--brand-light);
        box-shadow: 0 6px 22px rgba(245, 175, 0, 0.38);
        transform: translateY(-1px);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-inner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        position: relative;
        z-index: 1;
    }

    /* Loading state */
    .btn-submit.loading .btn-text {
        display: none;
    }

    .spin {
        display: none;
        width: 17px;
        height: 17px;
        border: 2px solid rgba(17, 21, 32, 0.25);
        border-top-color: #111520;
        border-radius: 50%;
        animation: spin .65s linear infinite;
    }

    .btn-submit.loading .spin {
        display: block;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Footer row */
    .form-foot {
        margin-top: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideL .5s .30s ease both;
    }

    .secure {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--muted);
    }

    .secure i {
        color: var(--green);
        font-size: 10.5px;
    }

    .forgot-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--brand);
        text-decoration: none;
        transition: opacity .2s;
    }

    .forgot-link:hover {
        opacity: .75;
    }

    /* ─────────────────────────────────────────
       ANIMATIONS
    ───────────────────────────────────────── */
    @keyframes slideR {
        from {
            opacity: 0;
            transform: translateX(-18px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideL {
        from {
            opacity: 0;
            transform: translateX(18px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ─────────────────────────────────────────
       RESPONSIVE
    ───────────────────────────────────────── */
    @media (max-width: 780px) {
        .card {
            flex-direction: column;
            max-width: 440px;
        }

        .panel-l {
            width: 100%;
            padding: 34px 28px;
        }

        .stats {
            display: none;
        }

        .copy h1 {
            font-size: 26px;
        }

        .copy {
            padding: 28px 0 24px;
        }

        .panel-r {
            padding: 34px 28px;
        }

        html,
        body {
            overflow: auto;
        }
    }

    @media (max-width: 460px) {

        .panel-l,
        .panel-r {
            padding: 26px 22px;
        }

        .copy h1 {
            font-size: 22px;
        }
    }
</style>