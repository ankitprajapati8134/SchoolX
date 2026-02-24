<!DOCTYPE html>
<html lang="en">

<head>
    <script>
    var BASE_URL = "<?php echo base_url(); ?>";
    </script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>GraderIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/bower_components/morris.js/morris.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>tools/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>tools/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>tools/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    /* ═══════════════════════════════════════════════════════════
   GRADERIQ GLOBAL THEME  v3
   Brand: #F5AF00  ·  Night default  ·  Day on demand
   ═══════════════════════════════════════════════════════════ */

    /* ── CSS TOKENS ── */
    :root {
        --gold: #F5AF00;
        --gold2: #D49700;
        --gold3: #FFC93C;
        --gold-dim: rgba(245, 175, 0, .10);
        --gold-ring: rgba(245, 175, 0, .22);
        --gold-glow: rgba(245, 175, 0, .18);
        --green: #3DD68C;
        --blue: #4AB5E3;
        --rose: #E05C6F;
        --amber: #C9A84C;
        --sw: 248px;
        /* sidebar width */
        --hh: 58px;
        /* header height */
        --r: 12px;
        --r-sm: 8px;
        --ease: .22s cubic-bezier(.4, 0, .2, 1);
        --font-d: 'Syne', sans-serif;
        --font-b: 'Plus Jakarta Sans', sans-serif;
        --font-m: 'JetBrains Mono', monospace;
    }

    /* ── NIGHT (default) ── */
    :root,
    [data-theme="night"],
    body[data-theme="night"] {
        --bg: #12100A;
        --bg2: #1A1710;
        --bg3: #221E14;
        --bg4: #2C2718;
        --card: rgba(30, 25, 14, .92);
        --border: rgba(245, 175, 0, .09);
        --brd2: rgba(245, 175, 0, .18);
        --t1: #F0E8D5;
        --t2: #C8B98A;
        --t3: #7A6E54;
        --t4: #5A5040;
        --sh: 0 4px 32px rgba(0, 0, 0, .50);
    }

    /* ── DAY ── */
    [data-theme="day"],
    body[data-theme="day"] {
        --bg: #FBF7EE;
        --bg2: #FFFFFF;
        --bg3: #F0EAD6;
        --bg4: #E5DCC4;
        --card: #FFFFFF;
        --border: rgba(180, 140, 0, .13);
        --brd2: rgba(180, 140, 0, .24);
        --t1: #1A1400;
        --t2: #6B5320;
        --t3: #9A8050;
        --t4: #BBA060;
        --sh: 0 2px 16px rgba(0, 0, 0, .08);
    }

    /* ── TRANSITION ── */
    html,
    body,
    .main-header,
    .main-sidebar,
    .content-wrapper,
    .main-footer,
    .box,
    .modal-content,
    .form-control,
    .btn {
        transition: background-color var(--ease), background var(--ease),
            border-color var(--ease), color var(--ease),
            box-shadow var(--ease) !important;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-b) !important;
        background: var(--bg) !important;
        color: var(--t1) !important;
    }

    body.hold-transition {
        visibility: visible !important;
    }

    a {
        text-decoration: none !important;
    }

    /* ═══════════════════════════════════════════════
   HEADER
   ═══════════════════════════════════════════════ */
    .main-header {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        height: var(--hh) !important;
        background: var(--bg2) !important;
        border-bottom: 1px solid var(--border) !important;
        box-shadow: var(--sh) !important;
        z-index: 1040 !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Gold underline on header */
    .main-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--gold) 0%, rgba(245, 175, 0, .25) 60%, transparent 100%);
    }

    /* ── Logo ── */
    .main-header .logo {
        width: var(--sw) !important;
        height: var(--hh) !important;
        background: var(--bg2) !important;
        border-right: 1px solid var(--border) !important;
        border-bottom: none !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 16px !important;
        gap: 10px;
        flex-shrink: 0;
        transition: width var(--ease) !important;
    }

    .g-mark {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-d);
        font-size: 16px;
        font-weight: 800;
        color: #0f0d06;
        flex-shrink: 0;
        box-shadow: 0 0 16px rgba(245, 175, 0, .4);
    }

    .g-logotext {
        line-height: 1;
        overflow: hidden;
    }

    .g-logoname {
        font-family: var(--font-d);
        font-size: 15px;
        font-weight: 800;
        color: var(--t1);
        letter-spacing: -.3px;
    }

    .g-logoname b {
        color: var(--gold);
    }

    .g-logosub {
        font-family: var(--font-m);
        font-size: 9px;
        color: var(--t3);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    .main-header .logo-mini {
        display: none !important;
    }

    /* ── Navbar ── */
    .main-header .navbar {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        min-height: var(--hh) !important;
        margin-left: var(--sw) !important;
        padding: 0 18px 0 18px !important;
        display: flex !important;
        align-items: center !important;
        flex: 1 !important;
        transition: margin-left var(--ease) !important;
    }

    .sidebar-toggle {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        background: transparent !important;
        border: 1px solid var(--border) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: var(--t3) !important;
        font-size: 13px !important;
        margin-right: 16px !important;
        flex-shrink: 0;
        transition: all var(--ease) !important;
    }

    .sidebar-toggle:hover {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
        border-color: var(--gold-ring) !important;
    }

    /* Global search */
    .g-search {
        flex: 1;
        max-width: 380px;
        display: flex;
        align-items: center;
        gap: 0;
        background: var(--bg3) !important;
        border: 1px solid var(--brd2) !important;
        border-radius: 10px !important;
        padding: 0 12px !important;
        height: 36px;
        transition: all var(--ease);
    }

    .g-search:focus-within {
        border-color: rgba(245, 175, 0, .45) !important;
        box-shadow: 0 0 0 3px var(--gold-glow) !important;
    }

    .g-search i {
        color: var(--t3);
        font-size: 12px;
        margin-right: 8px;
        flex-shrink: 0;
    }

    .g-search input {
        background: none !important;
        border: none !important;
        outline: none !important;
        color: var(--t1) !important;
        font-family: var(--font-b) !important;
        font-size: 13px !important;
        flex: 1;
        min-width: 0;
    }

    .g-search input::placeholder {
        color: var(--t4) !important;
    }

    /* Right actions */
    .navbar-custom-menu {
        margin-left: auto !important;
    }

    .g-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Icon button */
    .g-ibtn {
        position: relative;
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: transparent;
        border: 1px solid var(--border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--t3);
        font-size: 15px;
        cursor: pointer;
        transition: all var(--ease);
    }

    .g-ibtn:hover {
        background: var(--gold-dim);
        color: var(--gold);
        border-color: var(--gold-ring);
    }

    .g-ibtn .g-dot {
        position: absolute;
        top: -3px;
        right: -3px;
        min-width: 16px;
        height: 16px;
        border-radius: 8px;
        background: var(--rose);
        color: #fff;
        font-size: 8.5px;
        font-weight: 700;
        font-family: var(--font-m);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 2px solid var(--bg2);
        line-height: 1;
    }

    .g-ibtn .g-dot[data-n="0"] {
        display: none;
    }

    /* Theme toggle pill */
    .g-theme-pill {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px 6px 9px;
        background: var(--bg3);
        border: 1px solid var(--brd2);
        border-radius: 9px;
        color: var(--t2);
        font-size: 12px;
        font-weight: 600;
        font-family: var(--font-b);
        cursor: pointer;
        transition: all var(--ease);
        white-space: nowrap;
        user-select: none;
    }

    .g-theme-pill:hover {
        border-color: var(--gold);
        color: var(--gold);
    }

    /* Toggle track */
    .g-track {
        width: 34px;
        height: 18px;
        border-radius: 20px;
        background: var(--bg4);
        border: 1px solid var(--brd2);
        position: relative;
        flex-shrink: 0;
    }

    .g-knob {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--gold);
        box-shadow: 0 1px 5px rgba(0, 0, 0, .25);
        transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
    }

    [data-theme="day"] .g-knob {
        transform: translateX(16px);
    }

    [data-theme="night"] .g-knob {
        transform: translateX(0);
    }

    /* Bell dropdown */
    .g-bell-wrap {
        position: relative;
    }

    .g-bell-panel {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 320px;
        background: var(--bg2);
        border: 1px solid var(--brd2);
        border-radius: 14px;
        box-shadow: var(--sh);
        z-index: 9999;
        overflow: hidden;
        animation: panelIn .18s ease;
    }

    .g-bell-panel.open {
        display: block;
    }

    @keyframes panelIn {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(.96)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .g-bell-hd {
        padding: 13px 16px 11px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .g-bell-title {
        font-family: var(--font-d);
        font-size: 14px;
        font-weight: 700;
        color: var(--t1);
    }

    .g-bell-mark-btn {
        font-size: 10px;
        font-family: var(--font-m);
        color: var(--t3);
        background: none;
        border: none;
        cursor: pointer;
        padding: 3px 7px;
        border-radius: 4px;
        transition: all .14s;
    }

    .g-bell-mark-btn:hover {
        background: var(--gold-dim);
        color: var(--gold);
    }

    .g-bell-list {
        max-height: 290px;
        overflow-y: auto;
    }

    .g-bell-list::-webkit-scrollbar {
        width: 3px;
    }

    .g-bell-list::-webkit-scrollbar-thumb {
        background: var(--bg4);
        border-radius: 3px;
    }

    .g-bell-item {
        padding: 11px 15px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 10px;
        align-items: flex-start;
        cursor: pointer;
        transition: background .12s;
        text-decoration: none !important;
    }

    .g-bell-item:last-child {
        border-bottom: none;
    }

    .g-bell-item:hover {
        background: var(--bg3);
    }

    .g-bell-item.unread {
        background: rgba(245, 175, 0, .04);
    }

    .g-bld {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .g-bld.new {
        background: var(--gold);
        box-shadow: 0 0 6px rgba(245, 175, 0, .55);
    }

    .g-bld.old {
        background: var(--bg4);
    }

    .g-bell-nt {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--t1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 238px;
    }

    .g-bell-nd {
        font-size: 11.5px;
        color: var(--t2);
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        max-width: 238px;
        margin-top: 2px;
    }

    .g-bell-nt2 {
        font-size: 10px;
        color: var(--t3);
        font-family: var(--font-m);
        margin-top: 3px;
    }

    .g-bell-empty {
        padding: 26px 16px;
        text-align: center;
        color: var(--t3);
        font-size: 12.5px;
    }

    .g-bell-empty i {
        font-size: 22px;
        display: block;
        margin-bottom: 8px;
        opacity: .35;
    }

    .g-bell-ft {
        border-top: 1px solid var(--border);
    }

    .g-bell-ft a {
        display: block;
        text-align: center;
        padding: 9px;
        font-size: 12px;
        font-weight: 600;
        color: var(--gold) !important;
        transition: background .13s;
    }

    .g-bell-ft a:hover {
        background: var(--gold-dim);
    }

    /* User dropdown */
    .user-menu>a {
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 0 10px !important;
        height: var(--hh) !important;
        color: var(--t1) !important;
    }

    .user-menu>a .user-image {
        width: 28px !important;
        height: 28px !important;
        border-radius: 7px !important;
        border: 1.5px solid var(--gold) !important;
        margin: 0 !important;
    }

    .user-menu>a span {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: var(--t1) !important;
    }

    .navbar-nav .open>a {
        background: transparent !important;
    }

    .navbar-nav .dropdown-menu {
        background: var(--bg2) !important;
        border: 1px solid var(--brd2) !important;
        border-radius: 12px !important;
        box-shadow: var(--sh) !important;
        padding: 6px !important;
        min-width: 180px;
        top: calc(100% + 4px) !important;
    }

    .navbar-nav .dropdown-menu>li>a {
        color: var(--t2) !important;
        border-radius: 7px !important;
        padding: 8px 12px !important;
        font-size: 12.5px !important;
        display: block !important;
        transition: all .13s !important;
    }

    .navbar-nav .dropdown-menu>li>a:hover {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
    }

    .user-header {
        background: var(--bg3) !important;
        padding: 14px !important;
        border-radius: 8px 8px 0 0 !important;
    }

    .user-header img {
        border-radius: 8px !important;
        border: 2px solid var(--gold) !important;
    }

    .user-header p {
        color: var(--t1) !important;
        font-size: 13px !important;
        margin: 0 !important;
    }

    .user-header p small {
        color: var(--gold) !important;
        display: block;
        margin-top: 2px;
    }

    .user-footer {
        background: var(--bg3) !important;
        padding: 8px 10px !important;
        border-top: 1px solid var(--border) !important;
        border-radius: 0 0 9px 9px !important;
        display: flex !important;
        justify-content: space-between !important;
    }

    .user-footer .btn {
        background: var(--gold-dim) !important;
        border: 1px solid var(--gold-ring) !important;
        color: var(--gold) !important;
        border-radius: 7px !important;
        font-size: 12px !important;
        padding: 5px 11px !important;
    }

    .user-footer .btn:hover {
        background: var(--gold-ring) !important;
    }

    /* ═══════════════════════════════════════════════
   SIDEBAR
   ═══════════════════════════════════════════════ */
    .main-sidebar {
        position: fixed !important;
        top: var(--hh) !important;
        left: 0 !important;
        bottom: 0 !important;
        width: var(--sw) !important;
        background: var(--bg2) !important;
        border-right: 1px solid var(--border) !important;
        z-index: 1038 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        transition: width var(--ease), background var(--ease) !important;
    }

    /* Ambient gold strip */
    .main-sidebar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--gold), rgba(245, 175, 0, .2), transparent);
    }

    /* Dot-grid texture */
    .main-sidebar::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background-image: radial-gradient(circle, rgba(245, 175, 0, .035) 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .sidebar {
        flex: 1 !important;
        overflow-y: auto !important;
        padding: 8px 0 4px !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .sidebar::-webkit-scrollbar {
        width: 2px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(245, 175, 0, .12);
        border-radius: 3px;
    }

    /* Hide old AdminLTE user panel */
    .user-panel {
        display: none !important;
    }

    /* ── Section labels ── */
    .sidebar-menu {
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .sidebar-menu .g-sec {
        padding: 14px 16px 5px !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        color: var(--t4) !important;
        text-transform: uppercase !important;
        letter-spacing: 1.1px !important;
        font-family: var(--font-m) !important;
        pointer-events: none !important;
        cursor: default !important;
    }

    .sidebar-menu .g-sec:first-child {
        padding-top: 6px !important;
    }

    /* ── Nav items — GOLD hover/active ── */
    .sidebar-menu>li {
        margin: 1px 10px !important;
        position: relative !important;
    }

    .sidebar-menu>li>a {
        font-family: var(--font-b) !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        color: var(--t2) !important;
        padding: 9px 12px !important;
        border-radius: 9px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        transition: all var(--ease) !important;
        background: transparent !important;
    }

    /* ★ GOLD hover — replaces the default grey ★ */
    .sidebar-menu>li>a:hover {
        color: var(--gold) !important;
        background: var(--gold-dim) !important;
        border-color: var(--gold-ring) !important;
    }

    /* ★ Active pill ★ */
    .sidebar-menu>li.active>a,
    .sidebar-menu>li.active>a:hover {
        color: var(--t1) !important;
        background: var(--gold-dim) !important;
        font-weight: 600 !important;
    }

    .sidebar-menu>li.active>a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 22px;
        border-radius: 0 3px 3px 0;
        background: var(--gold);
        box-shadow: 0 0 8px rgba(245, 175, 0, .6);
    }

    /* Icon */
    .sidebar-menu>li>a>.fa,
    .sidebar-menu>li>a>i {
        width: 18px !important;
        font-size: 14px !important;
        text-align: center !important;
        flex-shrink: 0 !important;
        color: var(--t3) !important;
        transition: color var(--ease) !important;
    }

    .sidebar-menu>li>a:hover>i,
    .sidebar-menu>li>a:hover>.fa,
    .sidebar-menu>li.active>a>i,
    .sidebar-menu>li.active>a>.fa {
        color: var(--gold) !important;
    }

    /* Span text */
    .sidebar-menu>li>a>span:not(.pull-right-container):not(.g-nb) {
        flex: 1;
    }

    /* Nav badge */
    .g-nb {
        background: var(--rose);
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        font-family: var(--font-m);
        padding: 1px 5px;
        border-radius: 5px;
        flex-shrink: 0;
    }

    .g-nb.gold {
        background: var(--gold);
        color: #0f0d06;
    }

    /* Caret */
    .sidebar-menu>li>a>.pull-right-container {
        margin-left: auto !important;
        float: none !important;
    }

    .sidebar-menu .fa-angle-left {
        font-size: 11px !important;
        color: var(--t4) !important;
        transition: transform .2s !important;
    }

    .sidebar-menu li.menu-open>a>.pull-right-container .fa-angle-left {
        transform: rotate(-90deg) !important;
    }

    /* ── Submenu ── */
    .treeview-menu {
        background: transparent !important;
        padding: 3px 0 5px 15px !important;
        margin: 0 !important;
        list-style: none !important;
        border-left: 1.5px solid rgba(245, 175, 0, .17) !important;
        margin-left: 22px !important;
    }

    .treeview-menu>li {
        margin: 1px 0 !important;
    }

    .treeview-menu>li>a {
        font-family: var(--font-b) !important;
        font-size: 12.5px !important;
        color: var(--t3) !important;
        padding: 6px 10px !important;
        border-radius: 7px !important;
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        transition: all .14s !important;
    }

    .treeview-menu>li>a:hover {
        /* ★ Gold hover on sub-items too ★ */
        color: var(--gold) !important;
        background: var(--gold-dim) !important;
    }

    .treeview-menu>li.active>a {
        color: var(--gold) !important;
        font-weight: 600 !important;
    }

    .treeview-menu .fa-circle-o {
        font-size: 5px !important;
        opacity: .45 !important;
        flex-shrink: 0 !important;
    }

    .treeview-menu>li>a:hover .fa-circle-o,
    .treeview-menu>li.active>a .fa-circle-o {
        color: var(--gold) !important;
        opacity: 1 !important;
    }

    /* ── Bottom avatar ── */
    .g-sb-foot {
        position: relative;
        z-index: 1;
        border-top: 1px solid var(--border);
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg2);
        transition: background var(--ease);
    }

    .g-av {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        flex-shrink: 0;
        background: var(--gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-d);
        font-size: 13px;
        font-weight: 800;
        color: #0f0d06;
        box-shadow: 0 0 10px rgba(245, 175, 0, .3);
    }

    .g-av-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--t1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
    }

    .g-av-role {
        font-size: 10px;
        color: var(--t3);
        font-family: var(--font-m);
    }

    .g-av-out {
        margin-left: auto;
        width: 29px;
        height: 29px;
        border-radius: 7px;
        background: transparent;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--t3);
        font-size: 13px;
        cursor: pointer;
        transition: all var(--ease);
        flex-shrink: 0;
    }

    .g-av-out:hover {
        background: rgba(224, 92, 111, .1);
        color: var(--rose);
        border-color: rgba(224, 92, 111, .25);
    }

    /* ═══════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════ */
    .content-wrapper {
        background: var(--bg) !important;
        margin-left: var(--sw) !important;
        margin-top: var(--hh) !important;
        min-height: calc(100vh - var(--hh)) !important;
        color: var(--t1) !important;
        font-family: var(--font-b) !important;
        transition: margin-left var(--ease), background var(--ease) !important;
    }

    .main-footer {
        background: var(--bg2) !important;
        border-top: 1px solid var(--border) !important;
        color: var(--t3) !important;
        font-size: 12px !important;
        margin-left: var(--sw) !important;
        padding: 11px 24px !important;
        transition: background var(--ease), margin-left var(--ease) !important;
    }

    /* Collapsed */
    .sidebar-collapse .main-sidebar {
        width: 56px !important;
    }

    .sidebar-collapse .content-wrapper,
    .sidebar-collapse .main-footer {
        margin-left: 56px !important;
    }

    .sidebar-collapse .main-header .navbar {
        margin-left: 56px !important;
    }

    .sidebar-collapse .main-header .logo {
        width: 56px !important;
        padding: 0 !important;
        justify-content: center !important;
    }

    .sidebar-collapse .g-logotext,
    .sidebar-collapse .g-sec,
    .sidebar-collapse .sidebar-menu>li>a>span:not(.pull-right-container),
    .sidebar-collapse .sidebar-menu>li>a>.pull-right-container,
    .sidebar-collapse .treeview-menu,
    .sidebar-collapse .g-sb-foot .g-av-name,
    .sidebar-collapse .g-sb-foot .g-av-role,
    .sidebar-collapse .g-sb-foot .g-av-out {
        display: none !important;
    }

    .sidebar-collapse .sidebar-menu>li>a {
        justify-content: center !important;
        padding: 10px !important;
    }

    .sidebar-collapse .sidebar-menu>li {
        margin: 2px 5px !important;
    }

    .sidebar-collapse .g-sb-foot {
        justify-content: center !important;
    }

    /* ═══════════════════════════════════════════════
   COMPONENTS (global resets for all pages)
   ═══════════════════════════════════════════════ */
    .box {
        background: var(--bg2) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--r) !important;
        box-shadow: var(--sh) !important;
        color: var(--t1) !important;
    }

    .box-header {
        background: transparent !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 13px 18px !important;
    }

    .box-title {
        font-family: var(--font-d) !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: var(--t1) !important;
    }

    .box-body {
        padding: 18px !important;
    }

    .box-footer {
        background: var(--bg3) !important;
        border-top: 1px solid var(--border) !important;
        border-radius: 0 0 var(--r) var(--r) !important;
        padding: 11px 18px !important;
    }

    .box-primary {
        border-top: 3px solid var(--gold) !important;
    }

    .box-success {
        border-top: 3px solid var(--green) !important;
    }

    .box-danger {
        border-top: 3px solid var(--rose) !important;
    }

    .box-info {
        border-top: 3px solid var(--blue) !important;
    }

    .form-control {
        background: var(--bg3) !important;
        border: 1px solid var(--brd2) !important;
        color: var(--t1) !important;
        border-radius: var(--r-sm) !important;
        height: 38px !important;
        font-size: 13px !important;
        font-family: var(--font-b) !important;
    }

    .form-control:focus {
        border-color: rgba(245, 175, 0, .5) !important;
        box-shadow: 0 0 0 3px var(--gold-glow) !important;
        background: var(--bg3) !important;
        color: var(--t1) !important;
    }

    .form-control::placeholder {
        color: var(--t4) !important;
    }

    textarea.form-control {
        height: auto !important;
    }

    select.form-control option {
        background: var(--bg3);
        color: var(--t1);
    }

    label,
    .control-label {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: var(--t2) !important;
        text-transform: uppercase !important;
        letter-spacing: .5px !important;
    }

    .btn {
        border-radius: var(--r-sm) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        font-family: var(--font-b) !important;
        padding: 7px 16px !important;
    }

    .btn-primary {
        background: var(--gold) !important;
        color: #0f0d06 !important;
        border: none !important;
    }

    .btn-primary:hover {
        background: var(--gold2) !important;
        box-shadow: 0 4px 14px rgba(245, 175, 0, .4) !important;
    }

    .btn-success {
        background: rgba(61, 214, 140, .12) !important;
        color: var(--green) !important;
        border: 1px solid rgba(61, 214, 140, .25) !important;
    }

    .btn-danger {
        background: rgba(224, 92, 111, .12) !important;
        color: var(--rose) !important;
        border: 1px solid rgba(224, 92, 111, .25) !important;
    }

    .btn-default {
        background: var(--bg3) !important;
        color: var(--t2) !important;
        border: 1px solid var(--border) !important;
    }

    .btn-default:hover {
        color: var(--gold) !important;
        border-color: var(--gold-ring) !important;
        background: var(--gold-dim) !important;
    }

    .btn-info {
        background: rgba(74, 181, 227, .12) !important;
        color: var(--blue) !important;
        border: 1px solid rgba(74, 181, 227, .25) !important;
    }

    .btn-warning {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
        border: 1px solid var(--gold-ring) !important;
    }

    .table {
        color: var(--t1) !important;
        font-size: 13px !important;
    }

    .table>thead>tr>th {
        background: var(--bg3) !important;
        color: var(--t3) !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .6px !important;
        border-bottom: 1px solid var(--border) !important;
    }

    .table>tbody>tr>td {
        border-color: var(--border) !important;
        vertical-align: middle !important;
    }

    .table-striped>tbody>tr:nth-of-type(odd) {
        background: rgba(245, 175, 0, .025) !important;
    }

    .table-hover>tbody>tr:hover {
        background: var(--gold-dim) !important;
    }

    .modal-content {
        background: var(--bg2) !important;
        border: 1px solid var(--brd2) !important;
        border-radius: 14px !important;
        color: var(--t1) !important;
    }

    .modal-header {
        border-bottom: 1px solid var(--border) !important;
    }

    .modal-title {
        font-family: var(--font-d) !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        color: var(--t1) !important;
    }

    .modal-footer {
        border-top: 1px solid var(--border) !important;
    }

    .modal-backdrop {
        background: rgba(5, 4, 1, .8) !important;
    }

    .close {
        color: var(--t2) !important;
        opacity: 1 !important;
        text-shadow: none !important;
    }

    .close:hover {
        color: var(--rose) !important;
    }

    /* Badges + labels */
    .label,
    .badge {
        border-radius: 5px !important;
        font-size: 9.5px !important;
        font-family: var(--font-m) !important;
        padding: 2px 7px !important;
        font-weight: 700 !important;
    }

    .label-primary,
    .badge-primary {
        background: var(--gold) !important;
        color: #0f0d06 !important;
    }

    .label-success,
    .badge-success {
        background: rgba(61, 214, 140, .13) !important;
        color: var(--green) !important;
        border: 1px solid rgba(61, 214, 140, .25) !important;
    }

    .label-danger,
    .badge-danger {
        background: rgba(224, 92, 111, .13) !important;
        color: var(--rose) !important;
        border: 1px solid rgba(224, 92, 111, .25) !important;
    }

    .label-warning,
    .badge-warning {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
        border: 1px solid var(--gold-ring) !important;
    }

    .label-info,
    .badge-info {
        background: rgba(74, 181, 227, .13) !important;
        color: var(--blue) !important;
        border: 1px solid rgba(74, 181, 227, .25) !important;
    }

    .alert {
        border-radius: 10px !important;
        border: none !important;
        font-size: 13px !important;
    }

    .alert-success {
        background: rgba(61, 214, 140, .1) !important;
        color: var(--green) !important;
        border-left: 3px solid var(--green) !important;
    }

    .alert-danger {
        background: rgba(224, 92, 111, .1) !important;
        color: var(--rose) !important;
        border-left: 3px solid var(--rose) !important;
    }

    .alert-warning {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
        border-left: 3px solid var(--gold) !important;
    }

    .alert-info {
        background: rgba(74, 181, 227, .1) !important;
        color: var(--blue) !important;
        border-left: 3px solid var(--blue) !important;
    }

    /* DataTables */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        background: var(--bg3) !important;
        border: 1px solid var(--brd2) !important;
        color: var(--t1) !important;
        border-radius: 7px !important;
        padding: 4px 10px !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        color: var(--t2) !important;
        font-size: 12px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 7px !important;
        color: var(--t2) !important;
        font-size: 12px !important;
        border: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--gold) !important;
        color: #0f0d06 !important;
        border-color: var(--gold) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--gold-dim) !important;
        color: var(--gold) !important;
    }

    /* Content header */
    .content-header {
        padding: 24px 24px 0 !important;
    }

    .content-header h1 {
        font-family: var(--font-d) !important;
        font-size: 22px !important;
        font-weight: 800 !important;
        color: var(--t1) !important;
    }

    .content-header .breadcrumb {
        background: transparent !important;
        padding: 0 !important;
    }

    .content-header .breadcrumb>li+li::before {
        color: var(--t4) !important;
    }

    .content-header .breadcrumb>li>a {
        color: var(--gold) !important;
    }

    .content-header .breadcrumb>li.active {
        color: var(--t3) !important;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg);
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(245, 175, 0, .15);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(245, 175, 0, .35);
    }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <!-- ═════════════════════ HEADER ═════════════════════ -->
        <header class="main-header">

            <a href="<?= base_url('admin') ?>" class="logo">
                <div class="g-mark">G</div>
                <div class="g-logotext">
                    <div class="g-logoname"><b>Grader</b>IQ</div>
                    <div class="g-logosub">
                        <?= isset($school_name) ? strtoupper($school_name) : 'SCHOOL ERP' ?>
                        <?= isset($session_year) ? ' · ' . $session_year : '' ?>
                    </div>
                </div>
                <span class="logo-mini"><b>G</b></span>
            </a>

            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <i class="fa fa-bars"></i>
                </a>

                <div class="g-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="globalSearch" placeholder="Search anything…" autocomplete="off">
                </div>

                <div class="navbar-custom-menu">
                    <div class="g-actions">

                        <!-- Theme toggle -->
                        <button class="g-theme-pill" id="themeToggle" title="Toggle theme">
                            <div class="g-track">
                                <div class="g-knob"></div>
                            </div>
                            <span id="themeIcon">🌙</span>
                            <span id="themeLabel">Night</span>
                        </button>

                        <!-- Bell -->
                        <div class="g-bell-wrap" id="gBellWrap">
                            <button class="g-ibtn" id="gBellBtn" title="Notices">
                                <i class="fa fa-bell-o"></i>
                                <span class="g-dot" id="gBadge" data-n="0">0</span>
                            </button>
                            <div class="g-bell-panel" id="gBellPanel">
                                <div class="g-bell-hd">
                                    <span class="g-bell-title">Notices</span>
                                    <button class="g-bell-mark-btn" onclick="gMarkAllRead()">✓ Mark all read</button>
                                </div>
                                <div class="g-bell-list" id="gBellList">
                                    <div class="g-bell-empty"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
                                </div>
                                <div class="g-bell-ft">
                                    <a href="<?= base_url('NoticeAnnouncement') ?>">
                                        <i class="fa fa-list-ul" style="margin-right:5px"></i>View All Notices
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- User -->
                        <ul class="nav navbar-nav" style="list-style:none;margin:0;padding:0">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?= base_url() ?>tools/dist/img/user2-160x160.jpg" class="user-image"
                                        alt="">
                                    <span class="hidden-xs"><?= $admin_name ?? 'Admin' ?></span>
                                    <i class="fa fa-angle-down" style="font-size:10px;opacity:.4;margin-left:3px"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li class="user-header">
                                        <img src="<?= base_url() ?>tools/dist/img/user2-160x160.jpg" class="img-circle"
                                            style="width:52px;height:52px" alt="">
                                        <p><?= $admin_name ?? 'Admin' ?><small><?= $admin_role ?? '' ?></small></p>
                                    </li>
                                    <li class="user-footer">
                                        <div><a href="<?= base_url('admin/manage_admin') ?>" class="btn btn-flat"><i
                                                    class="fa fa-user" style="margin-right:5px"></i>Profile</a></div>
                                        <div><a href="<?= base_url('admin_login/logout') ?>" class="btn btn-flat"><i
                                                    class="fa fa-sign-out" style="margin-right:5px"></i>Logout</a></div>
                                    </li>
                                </ul>
                            </li>
                        </ul>

                    </div>
                </div>
            </nav>
        </header>

        <!-- ═════════════════════ SIDEBAR ═════════════════════ -->
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu" data-widget="tree">

                    <!-- OVERVIEW -->
                    <li class="g-sec">Overview</li>
                    <li class="sidebar-single">
                        <a href="<?= base_url('admin') ?>">
                            <i class="fa fa-th-large"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <!-- <li class="sidebar-single" id="gNavNotif">
                        <a href="<?= base_url('NoticeAnnouncement/create_notice') ?>">
                            <i class="fa fa-bell-o"></i>
                            <span>Notifications</span>
                            <span class="g-nb" id="gNavBadge" style="display:none">0</span>
                        </a>
                    </li> -->

                    <!-- ACADEMICS -->
                    <?php if (isset($school_features) && (in_array('Student Management', $school_features) || in_array('Staff Management', $school_features) || in_array('Class Management', $school_features) || in_array('Subject Management', $school_features) || in_array('Exam Management', $school_features))): ?>
                    <li class="g-sec">Academics</li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Student Management', $school_features)): ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-users"></i><span>Students</span>
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                        <ul class="treeview-menu">
                            <li><a href="<?= base_url('student/all_student') ?>"><i class="fa fa-circle-o"></i>All
                                    Students</a></li>
                            <li><a href="<?= base_url('student/studentAdmission') ?>"><i
                                        class="fa fa-circle-o"></i>Admission</a></li>
                            <li><a href="<?= base_url('student/attendance') ?>"><i
                                        class="fa fa-circle-o"></i>Attendance</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Staff Management', $school_features)): ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-user-o"></i><span>Teachers</span>
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                        <ul class="treeview-menu">
                            <li><a href="<?= base_url('staff/all_staff') ?>"><i class="fa fa-circle-o"></i>All Staff</a>
                            </li>
                            <li><a href="<?= base_url('staff/new_staff') ?>"><i class="fa fa-circle-o"></i>New Staff</a>
                            </li>
                            <li><a href="<?= base_url('staff/teacher_duty') ?>"><i class="fa fa-circle-o"></i>Teacher
                                    Duty</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Class Management', $school_features)): ?>
                    <li class="sidebar-single">
                        <a href="<?= base_url('classes/manage_classes') ?>"><i
                                class="fa fa-calendar"></i><span>Classes</span></a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Subject Management', $school_features)): ?>
                    <li class="sidebar-single">
                        <a href="<?= base_url('subjects/manage_subjects') ?>"><i
                                class="fa fa-book"></i><span>Subjects</span></a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Exam Management', $school_features)): ?>
                    <li class="sidebar-single">
                        <a href="<?= base_url('exam/manage_exam') ?>"><i
                                class="fa fa-pencil-square-o"></i><span>Exams</span></a>
                    </li>
                    <?php endif; ?>

                    <!-- ADMINISTRATION -->
                    <?php if (isset($school_features) && (in_array('Fees Management', $school_features) || in_array('Account Management', $school_features) || in_array('Notice and Announcement', $school_features) || in_array('School Management', $school_features))): ?>
                    <li class="g-sec">Administration</li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Fees Management', $school_features)): ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-inr"></i><span>Fees</span>
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                        <ul class="treeview-menu">
                            <li><a href="<?= base_url('fees/fees_structure') ?>"><i
                                        class="fa fa-circle-o"></i>Structure</a></li>
                            <li><a href="<?= base_url('fees/fees_chart') ?>"><i class="fa fa-circle-o"></i>Chart</a>
                            </li>
                            <li><a href="<?= base_url('fees/fees_counter') ?>"><i class="fa fa-circle-o"></i>Counter</a>
                            </li>
                            <li><a href="<?= base_url('fees/fees_records') ?>"><i class="fa fa-circle-o"></i>Records</a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Account Management', $school_features)): ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-briefcase"></i><span>Accounts</span>
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                        <ul class="treeview-menu">
                            <li><a href="<?= base_url('account/account_book') ?>"><i class="fa fa-circle-o"></i>Account
                                    Book</a></li>
                            <li><a href="<?= base_url('account/view_accounts') ?>"><i class="fa fa-circle-o"></i>View
                                    Accounts</a></li>
                            <li><a href="<?= base_url('account/vouchers') ?>"><i class="fa fa-circle-o"></i>Create
                                    Vouchers</a></li>
                            <li><a href="<?= base_url('account/view_voucher') ?>"><i class="fa fa-circle-o"></i>View
                                    Vouchers</a></li>
                            <li><a href="<?= base_url('account/day_book') ?>"><i class="fa fa-circle-o"></i>Day Book</a>
                            </li>
                            <li><a href="<?= base_url('account/cash_book') ?>"><i class="fa fa-circle-o"></i>Cash
                                    Book</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('Notice and Announcement', $school_features)): ?>
                    <li class="sidebar-single">
                        <a href="<?= base_url('NoticeAnnouncement/create_notice') ?>"><i
                                class="fa fa-bullhorn"></i><span>Notices</span></a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($school_features) && in_array('School Management', $school_features)): ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-building-o"></i><span>School</span>
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                        <ul class="treeview-menu">
                            <li><a href="<?= base_url('schools/manage_school') ?>"><i class="fa fa-circle-o"></i>Manage
                                    School</a></li>
                            <li><a href="<?= base_url('schools/schoolProfile') ?>"><i
                                        class="fa fa-circle-o"></i>Profile</a></li>
                            <li><a href="<?= base_url('schools/schoolGallery') ?>"><i
                                        class="fa fa-circle-o"></i>Gallery</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (isset($admin_role) && $admin_role === 'Super Admin'): ?>
                    <li class="g-sec">System</li>
                    <li class="sidebar-single">
                        <a href="<?= base_url('admin/manage_admin') ?>"><i
                                class="fa fa-user-circle-o"></i><span>Admin</span></a>
                    </li>
                    <?php endif; ?>

                </ul>
            </section>

            <!-- Pinned avatar footer -->
            <div class="g-sb-foot">
                <?php
        $ini = 'AD';
        if (!empty($admin_name)) {
          $p = explode(' ', trim($admin_name));
          $ini = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
        }
        ?>
                <div class="g-av"><?= $ini ?></div>
                <div style="flex:1;min-width:0">
                    <div class="g-av-name"><?= $admin_name ?? 'Admin' ?></div>
                    <div class="g-av-role"><?= $admin_role ?? 'Administrator' ?></div>
                </div>
                <a href="<?= base_url('admin_login/logout') ?>" class="g-av-out" title="Logout">
                    <i class="fa fa-sign-out"></i>
                </a>
            </div>
        </aside>

        <!-- ═════════════════ THEME + NAV + BELL ═════════════════ -->
        <script>
        (function() {
            var html = document.documentElement;
            var body = document.body;
            var btn = document.getElementById('themeToggle');
            var tIcon = document.getElementById('themeIcon');
            var tLbl = document.getElementById('themeLabel');

            /* ── 1. THEME ── */
            var saved = localStorage.getItem('graderiq_theme') || 'night';

            function applyTheme(t) {
                html.setAttribute('data-theme', t);
                body.setAttribute('data-theme', t);
                // Also sync dashboard root if present
                var dbRoot = document.getElementById('dbRoot');
                if (dbRoot) dbRoot.setAttribute('data-theme', t === 'night' ? 'dark' : 'light');

                if (t === 'day') {
                    tIcon && (tIcon.textContent = '☀️');
                    tLbl && (tLbl.textContent = 'Day');
                } else {
                    tIcon && (tIcon.textContent = '🌙');
                    tLbl && (tLbl.textContent = 'Night');
                }
                localStorage.setItem('graderiq_theme', t);
            }

            applyTheme(saved);

            if (btn) btn.addEventListener('click', function() {
                applyTheme(html.getAttribute('data-theme') === 'night' ? 'day' : 'night');
            });

            /* ── 2. ACTIVE LINK ── */
            var curPath = window.location.pathname.replace(/\/$/, '');
            var menu = document.querySelector('.sidebar-menu');
            if (menu) {
                menu.querySelectorAll('li.active').forEach(function(e) {
                    e.classList.remove('active');
                });
                var best = null,
                    bestLen = 0;
                menu.querySelectorAll('a[href]').forEach(function(a) {
                    var base = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/$/, '') : '';
                    var rel = (a.getAttribute('href') || '').replace(base, '').replace(/\/$/, '') || '/';
                    if (curPath === rel || curPath.indexOf(rel + '/') === 0) {
                        if (rel.length > bestLen) {
                            bestLen = rel.length;
                            best = a;
                        }
                    }
                });
                if (best) {
                    var li = best.closest('li');
                    if (li) {
                        li.classList.add('active');
                        var pUl = li.closest('.treeview-menu');
                        if (pUl) {
                            var pLi = pUl.closest('.treeview');
                            if (pLi) pLi.classList.add('active', 'menu-open');
                        }
                    }
                }
            }

            /* ── 3. BELL ── */
            var SITE = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/$/, '') : '';
            var RK = 'gbell_<?= md5(($school_name ?? '') . ($session_year ?? '')) ?>';
            var readIds = JSON.parse(localStorage.getItem(RK) || '[]');
            var bData = [];

            var $btn = document.getElementById('gBellBtn');
            var $panel = document.getElementById('gBellPanel');
            var $list = document.getElementById('gBellList');
            var $badge = document.getElementById('gBadge');
            var $nbadge = document.getElementById('gNavBadge');

            if ($btn) {
                $btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    $panel.classList.toggle('open');
                });
            }
            document.addEventListener('click', function(e) {
                if ($panel && !document.getElementById('gBellWrap').contains(e.target))
                    $panel.classList.remove('open');
            });

            function fetchBell() {
                fetch(SITE + '/NoticeAnnouncement/fetch_recent_notices', {
                        cache: 'no-store'
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(d) {
                        bData = Array.isArray(d) ? d : [];
                        renderBell();
                        updateBadge();
                    })
                    .catch(function() {
                        if ($list) $list.innerHTML =
                            '<div class="g-bell-empty"><i class="fa fa-exclamation-circle"></i> Could not load</div>';
                    });
            }

            function renderBell() {
                if (!$list) return;
                if (!bData.length) {
                    $list.innerHTML =
                        '<div class="g-bell-empty"><i class="fa fa-bell-slash-o"></i> No notices yet</div>';
                    return;
                }
                var h = '';
                bData.forEach(function(n) {
                    var isNew = readIds.indexOf(n.id) === -1;
                    var ts = n.Time_Stamp || n.Timestamp || 0;
                    var ago = timeAgo(ts ? new Date(ts) : new Date());
                    var desc = (n.Description || '').substring(0, 60);
                    h += '<a class="g-bell-item' + (isNew ? ' unread' : '') + '" href="' + SITE +
                        '/NoticeAnnouncement" data-id="' + n.id + '">' +
                        '<span class="g-bld ' + (isNew ? 'new' : 'old') + '"></span>' +
                        '<div style="flex:1;min-width:0">' +
                        '<div class="g-bell-nt">' + esc(n.Title || 'Untitled') + '</div>' +
                        '<div class="g-bell-nd">' + esc(desc) + '</div>' +
                        '<div class="g-bell-nt2"><i class="fa fa-clock-o" style="margin-right:3px"></i>' +
                        ago + '</div>' +
                        '</div></a>';
                });
                $list.innerHTML = h;
                $list.querySelectorAll('a[data-id]').forEach(function(a) {
                    a.addEventListener('click', function() {
                        var id = a.getAttribute('data-id');
                        if (id && readIds.indexOf(id) === -1) {
                            readIds.push(id);
                            localStorage.setItem(RK, JSON.stringify(readIds));
                            updateBadge();
                        }
                    });
                });
            }

            function updateBadge() {
                var u = bData.filter(function(n) {
                    return readIds.indexOf(n.id) === -1;
                }).length;
                if ($badge) {
                    $badge.textContent = u > 9 ? '9+' : String(u);
                    $badge.setAttribute('data-n', u);
                    $badge.style.display = u ? 'flex' : 'none';
                }
                if ($nbadge) {
                    $nbadge.textContent = u > 9 ? '9+' : String(u);
                    $nbadge.style.display = u ? 'inline-block' : 'none';
                }
            }

            window.gMarkAllRead = function() {
                bData.forEach(function(n) {
                    if (readIds.indexOf(n.id) === -1) readIds.push(n.id);
                });
                localStorage.setItem(RK, JSON.stringify(readIds));
                updateBadge();
                renderBell();
                $panel && $panel.classList.remove('open');
            };

            function esc(s) {
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function timeAgo(d) {
                var diff = Math.floor((Date.now() - d.getTime()) / 1000);
                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
                return d.toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short'
                });
            }

            fetchBell();
            setInterval(fetchBell, 90000);
        })();
        </script>