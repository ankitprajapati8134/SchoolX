<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
/* ── Accounting Module — Production Styles ─────────────────────────── */
.ac-wrap {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 1440px; margin: 0 auto; padding: 24px 20px;
    color: var(--t1, #1a2e2a); line-height: 1.5; font-size: 14px;
    min-height: calc(100vh - 120px);
}
.ac-wrap *, .ac-wrap *::before, .ac-wrap *::after { box-sizing: border-box; }
.ac-mono { font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; }

/* ── Theme vars with solid fallbacks ── */
.ac-wrap {
    --ac-primary: var(--gold, #0f766e);
    --ac-bg: var(--bg, #f0f7f5);
    --ac-bg2: var(--bg2, #ffffff);
    --ac-bg3: var(--bg3, #e6f4f1);
    --ac-border: var(--border, #d1ddd8);
    --ac-text: var(--t1, #1a2e2a);
    --ac-text2: var(--t2, #4a6a60);
    --ac-text3: var(--t3, #7a9a8e);
    --ac-card: var(--card, #ffffff);
    --ac-shadow: var(--sh, 0 2px 8px rgba(0,0,0,.06));
    --ac-r: 10px;
    --ac-green: #16a34a;
    --ac-red: #dc2626;
    --ac-blue: #2563eb;
    --ac-amber: #d97706;
}

/* ── Page Header ── */
.ac-header {
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 24px; padding-bottom: 18px;
    border-bottom: 1px solid var(--ac-border);
}
.ac-header-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: linear-gradient(135deg, var(--ac-primary), #14b8a6);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 22px; flex-shrink: 0;
}
.ac-header h2 {
    font-size: 24px; font-weight: 800; color: var(--ac-text);
    margin: 0; letter-spacing: -.3px; line-height: 1.2;
}
.ac-header p { font-size: 14px; color: var(--ac-text3); margin: 2px 0 0; }

/* ── Tab Navigation ── */
.ac-tabs {
    display: flex; gap: 2px; border-bottom: 2px solid var(--ac-border);
    margin-bottom: 24px; overflow-x: auto; padding: 0 2px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.ac-tabs::-webkit-scrollbar { display: none; }
.ac-tab {
    padding: 12px 20px; font-size: 14px; font-weight: 600;
    color: var(--ac-text3); cursor: pointer;
    border-bottom: 3px solid transparent; margin-bottom: -2px;
    white-space: nowrap; transition: all .2s ease;
    display: flex; align-items: center; gap: 7px;
    border-radius: 6px 6px 0 0;
}
.ac-tab:hover { color: var(--ac-text); background: rgba(15,118,110,.04); }
.ac-tab.active {
    color: var(--ac-primary); border-bottom-color: var(--ac-primary);
    background: rgba(15,118,110,.06);
}
.ac-tab i { font-size: 14px; }
a.ac-tab { text-decoration: none; color: inherit; }
a.ac-tab:hover { text-decoration: none; color: var(--ac-text); }
a.ac-tab.active { color: var(--ac-primary); }

.ac-panel { display: none; }
.ac-panel.active { display: block; animation: acFadeIn .25s ease; }
@keyframes acFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* ── Cards ── */
.ac-card {
    background: var(--ac-card); border: 1px solid var(--ac-border);
    border-radius: var(--ac-r); padding: 22px; margin-bottom: 18px;
    box-shadow: var(--ac-shadow);
}
.ac-card-title {
    font-size: 16px; font-weight: 700; color: var(--ac-text);
    margin-bottom: 16px; display: flex; align-items: center;
    gap: 10px; flex-wrap: wrap;
}
.ac-card-title i { color: var(--ac-primary); }

/* ── Buttons ── */
.ac-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px; font-family: inherit; font-size: 13px; font-weight: 600;
    border-radius: 8px; border: none; cursor: pointer; transition: all .2s ease;
    text-decoration: none; line-height: 1.4;
}
.ac-btn-primary { background: var(--ac-primary); color: #fff; }
.ac-btn-primary:hover { background: #0d6b63; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(15,118,110,.3); }
.ac-btn-danger { background: var(--ac-red); color: #fff; }
.ac-btn-danger:hover { background: #b91c1c; }
.ac-btn-ghost { background: transparent; color: var(--ac-primary); border: 1.5px solid var(--ac-primary); }
.ac-btn-ghost:hover { background: var(--ac-primary); color: #fff; }
.ac-btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; }
.ac-btn[disabled] { opacity: .45; cursor: not-allowed; pointer-events: none; }

/* ── Toolbar / Filters ── */
.ac-toolbar {
    display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
    padding: 16px 18px; background: var(--ac-bg3); border-radius: var(--ac-r);
    margin-bottom: 18px; border: 1px solid var(--ac-border);
}
.ac-fg { display: flex; flex-direction: column; gap: 4px; }
.ac-fg label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--ac-text3);
}
.ac-fg input, .ac-fg select, .ac-fg textarea {
    font-family: inherit; font-size: 14px; padding: 8px 12px;
    border: 1.5px solid var(--ac-border); border-radius: 8px;
    background: var(--ac-bg2); color: var(--ac-text);
    transition: all .2s ease; min-height: 38px;
}
.ac-fg input:focus, .ac-fg select:focus, .ac-fg textarea:focus {
    outline: none; border-color: var(--ac-primary);
    box-shadow: 0 0 0 3px rgba(15,118,110,.1);
}

/* ── Tables ── */
.ac-table-wrap { overflow-x: auto; border-radius: var(--ac-r); }
.ac-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.ac-table th {
    text-align: left; padding: 12px 14px; font-weight: 700; font-size: 12px;
    text-transform: uppercase; letter-spacing: .5px; color: var(--ac-text3);
    border-bottom: 2px solid var(--ac-border); background: var(--ac-bg3);
    white-space: nowrap;
}
.ac-table td {
    padding: 11px 14px; border-bottom: 1px solid var(--ac-border);
    color: var(--ac-text); vertical-align: middle;
}
.ac-table tbody tr:hover td { background: rgba(15,118,110,.04); }
.ac-table .ac-num { font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 13px; }
.ac-table .ac-dr { color: var(--ac-green); font-weight: 600; }
.ac-table .ac-cr { color: var(--ac-red); font-weight: 600; }
.ac-table tfoot td { font-weight: 700; border-top: 2px solid var(--ac-border); background: var(--ac-bg3); }
.ac-table code { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ac-text2); }

/* ── Badges ── */
.ac-badge {
    display: inline-block; padding: 3px 10px; border-radius: 12px;
    font-size: 11px; font-weight: 700; letter-spacing: .3px;
}
.ac-badge-asset { background: #dbeafe; color: #1e40af; }
.ac-badge-liability { background: #fce7f3; color: #9d174d; }
.ac-badge-equity { background: #ede9fe; color: #6d28d9; }
.ac-badge-income { background: #dcfce7; color: #166534; }
.ac-badge-expense { background: #fef3c7; color: #92400e; }
.ac-badge-finalized { background: #dcfce7; color: #166534; }
.ac-badge-draft { background: #fef3c7; color: #92400e; }
.ac-badge-matched { background: #dcfce7; color: #166534; }
.ac-badge-unmatched { background: #fee2e2; color: #991b1b; }

/* ── Modal ── */
.ac-modal-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.5); z-index: 9000;
    align-items: center; justify-content: center;
    backdrop-filter: blur(2px);
}
.ac-modal-overlay.show { display: flex; }
.ac-modal {
    background: var(--ac-bg2); border-radius: 14px; width: 95%; max-width: 720px;
    max-height: 90vh; overflow-y: auto; padding: 28px;
    box-shadow: 0 12px 40px rgba(0,0,0,.25);
}
.ac-modal-title {
    font-size: 18px; font-weight: 800; margin-bottom: 20px;
    color: var(--ac-text); letter-spacing: -.2px;
}
.ac-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--ac-border); }

/* ── Stats Row ── */
.ac-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
.ac-stat {
    background: var(--ac-card); border: 1px solid var(--ac-border);
    border-radius: var(--ac-r); padding: 18px; text-align: center;
}
.ac-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--ac-text3); }
.ac-stat-value { font-size: 24px; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: var(--ac-text); margin-top: 6px; }

/* ── Toast ── */
.ac-toast {
    position: fixed; bottom: 28px; right: 28px; padding: 14px 22px;
    border-radius: 10px; font-size: 14px; font-weight: 600; color: #fff;
    z-index: 9999; opacity: 0; transform: translateY(10px);
    transition: opacity .3s, transform .3s;
    box-shadow: 0 4px 16px rgba(0,0,0,.2);
}
.ac-toast.show { opacity: 1; transform: translateY(0); }
.ac-toast.success { background: var(--ac-green); }
.ac-toast.error { background: var(--ac-red); }

/* ── Responsive ── */
@media (max-width: 768px) {
    .ac-wrap { padding: 16px 12px; }
    .ac-header h2 { font-size: 20px; }
    .ac-tab { padding: 10px 14px; font-size: 13px; }
    .ac-toolbar { flex-direction: column; }
    .ac-stats { grid-template-columns: 1fr 1fr; }
    .ac-modal { padding: 20px; }
}

/* ── CoA hierarchy indents ── */
.ac-indent-1 { padding-left: 32px !important; }
.ac-indent-2 { padding-left: 52px !important; }
.ac-group-row td { font-weight: 700; background: var(--ac-bg3) !important; }

/* ── Empty state ── */
.ac-empty { text-align: center; color: var(--ac-text3); padding: 40px 20px; font-size: 14px; }
</style>

<div class="content-wrapper">
<section class="content">
<div class="ac-wrap">
    <div class="ac-header">
        <div class="ac-header-icon"><i class="fa fa-calculator"></i></div>
        <div>
            <h2>Accounting System</h2>
            <p>Double-entry accounting, reports & financial management</p>
        </div>
    </div>

    <?php
        $at = isset($active_tab) ? $active_tab : 'chart';
        $tab_map = [
            'chart'          => ['panel'=>'panelCoa',      'icon'=>'fa-sitemap',   'label'=>'Chart of Accounts'],
            'ledger'         => ['panel'=>'panelLedger',   'icon'=>'fa-book',      'label'=>'Journal Entries'],
            'income-expense' => ['panel'=>'panelIe',       'icon'=>'fa-exchange',  'label'=>'Income & Expense'],
            'cash-book'      => ['panel'=>'panelCashbook', 'icon'=>'fa-money',     'label'=>'Cash Book'],
            'bank-recon'     => ['panel'=>'panelBankrecon', 'icon'=>'fa-bank',     'label'=>'Bank Recon'],
            'reports'        => ['panel'=>'panelReports',  'icon'=>'fa-bar-chart', 'label'=>'Reports'],
            'settings'       => ['panel'=>'panelSettings', 'icon'=>'fa-cog',       'label'=>'Settings'],
        ];
    ?>
    <!-- Tabs -->
    <div class="ac-tabs" id="acTabs">
        <?php foreach ($tab_map as $slug => $t): ?>
        <a class="ac-tab<?= $slug === $at ? ' active' : '' ?>" data-tab="<?= $slug ?>" href="<?= base_url('accounting/' . $slug) ?>">
            <i class="fa <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ═══════════ TAB 1: CHART OF ACCOUNTS ═══════════ -->
    <div class="ac-panel<?= $at === 'chart' ? ' active' : '' ?>" id="panelCoa">
        <div class="ac-card">
            <div class="ac-card-title">
                <span>Chart of Accounts</span>
                <span style="flex:1"></span>
                <button class="ac-btn ac-btn-primary ac-btn-sm" onclick="AC.showAccountModal()"><i class="fa fa-plus"></i> Add Account</button>
                <button class="ac-btn ac-btn-ghost ac-btn-sm ac-role-admin" onclick="AC.seedChart()" style="display:none"><i class="fa fa-magic"></i> Seed Defaults</button>
            </div>
            <div class="ac-table-wrap">
                <table class="ac-table" id="coaTable">
                    <thead><tr>
                        <th>Code</th><th>Account Name</th><th>Category</th><th>Type</th><th>Opening Bal</th><th>Actions</th>
                    </tr></thead>
                    <tbody id="coaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 2: JOURNAL ENTRIES ═══════════ -->
    <div class="ac-panel<?= $at === 'ledger' ? ' active' : '' ?>" id="panelLedger">
        <div class="ac-toolbar">
            <div class="ac-fg"><label>From</label><input type="date" id="ledgerFrom"></div>
            <div class="ac-fg"><label>To</label><input type="date" id="ledgerTo"></div>
            <div class="ac-fg"><label>Account</label><select id="ledgerAcct"><option value="">All</option></select></div>
            <div class="ac-fg"><label>Type</label>
                <select id="ledgerVType">
                    <option value="">All</option>
                    <option>Journal</option><option>Receipt</option><option>Payment</option><option>Contra</option><option>Fee</option>
                </select>
            </div>
            <button class="ac-btn ac-btn-primary" onclick="AC.loadLedger()"><i class="fa fa-search"></i> Load</button>
            <button class="ac-btn ac-btn-ghost" onclick="AC.showJournalModal()"><i class="fa fa-plus"></i> New Entry</button>
        </div>
        <div class="ac-card">
            <div class="ac-table-wrap">
                <table class="ac-table" id="ledgerTable">
                    <thead><tr>
                        <th>Date</th><th>Voucher #</th><th>Type</th><th>Narration</th><th class="ac-num">Debit</th><th class="ac-num">Credit</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody id="ledgerBody"></tbody>
                </table>
            </div>
            <div id="ledgerPagination" style="text-align:center;margin-top:12px;display:none;">
                <span id="ledgerCount" style="font-size:13px;color:var(--ac-text2);margin-right:12px;"></span>
                <button class="ac-btn ac-btn-ghost ac-btn-sm" id="ledgerLoadMore" onclick="AC.loadMoreLedger()"><i class="fa fa-arrow-down"></i> Load More</button>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 3: INCOME & EXPENSE ═══════════ -->
    <div class="ac-panel<?= $at === 'income-expense' ? ' active' : '' ?>" id="panelIe">
        <div class="ac-stats" id="ieSummary"></div>
        <div class="ac-toolbar">
            <div class="ac-fg"><label>Type</label>
                <select id="ieType"><option value="">All</option><option value="income">Income</option><option value="expense">Expense</option></select>
            </div>
            <div class="ac-fg"><label>From</label><input type="date" id="ieFrom"></div>
            <div class="ac-fg"><label>To</label><input type="date" id="ieTo"></div>
            <button class="ac-btn ac-btn-primary" onclick="AC.loadIE()"><i class="fa fa-search"></i> Load</button>
            <button class="ac-btn ac-btn-ghost" onclick="AC.showIEModal('income')"><i class="fa fa-plus"></i> Income</button>
            <button class="ac-btn ac-btn-ghost" onclick="AC.showIEModal('expense')"><i class="fa fa-plus"></i> Expense</button>
        </div>
        <div class="ac-card">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>Date</th><th>Type</th><th>Account</th><th>Description</th><th>Mode</th><th class="ac-num">Amount</th><th>Actions</th></tr></thead>
                    <tbody id="ieBody"></tbody>
                </table>
            </div>
            <div id="iePagination" style="text-align:center;margin-top:12px;display:none;">
                <span id="ieCount" style="font-size:13px;color:var(--ac-text2);margin-right:12px;"></span>
                <button class="ac-btn ac-btn-ghost ac-btn-sm" id="ieLoadMore" onclick="AC.loadMoreIE()"><i class="fa fa-arrow-down"></i> Load More</button>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 4: CASH BOOK ═══════════ -->
    <div class="ac-panel<?= $at === 'cash-book' ? ' active' : '' ?>" id="panelCashbook">
        <div class="ac-toolbar">
            <div class="ac-fg"><label>Account</label><select id="cbAccount"></select></div>
            <div class="ac-fg"><label>From</label><input type="date" id="cbFrom"></div>
            <div class="ac-fg"><label>To</label><input type="date" id="cbTo"></div>
            <button class="ac-btn ac-btn-primary" onclick="AC.loadCashBook()"><i class="fa fa-search"></i> Load</button>
        </div>
        <div class="ac-stats" id="cbStats"></div>
        <div class="ac-card">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>Date</th><th>Voucher #</th><th>Narration</th><th class="ac-num">Received (Dr)</th><th class="ac-num">Paid (Cr)</th><th class="ac-num">Balance</th></tr></thead>
                    <tbody id="cbBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 5: BANK RECONCILIATION ═══════════ -->
    <div class="ac-panel<?= $at === 'bank-recon' ? ' active' : '' ?>" id="panelBankrecon">
        <div class="ac-toolbar">
            <div class="ac-fg"><label>Bank Account</label><select id="brAccount"></select></div>
            <div class="ac-fg"><label>From</label><input type="date" id="brFrom"></div>
            <div class="ac-fg"><label>To</label><input type="date" id="brTo"></div>
            <button class="ac-btn ac-btn-primary" onclick="AC.loadBankRecon()"><i class="fa fa-search"></i> Load</button>
            <button class="ac-btn ac-btn-ghost" onclick="AC.showImportCSV()"><i class="fa fa-upload"></i> Import CSV</button>
        </div>
        <div class="ac-stats" id="brStats"></div>
        <div class="ac-card">
            <div class="ac-card-title">Bank Statement Entries</div>
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>Date</th><th>Description</th><th>Reference</th><th class="ac-num">Debit</th><th class="ac-num">Credit</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody id="brBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════ TAB 6: REPORTS ═══════════ -->
    <div class="ac-panel<?= $at === 'reports' ? ' active' : '' ?>" id="panelReports">
        <div class="ac-toolbar">
            <div class="ac-fg"><label>Report</label>
                <select id="rptType">
                    <option value="trial_balance">Trial Balance</option>
                    <option value="profit_loss">Profit & Loss</option>
                    <option value="balance_sheet">Balance Sheet</option>
                    <option value="cash_flow">Cash Flow</option>
                </select>
            </div>
            <div class="ac-fg"><label>As of / From</label><input type="date" id="rptFrom"></div>
            <div class="ac-fg"><label>To</label><input type="date" id="rptTo"></div>
            <button class="ac-btn ac-btn-primary" onclick="AC.generateReport()"><i class="fa fa-file-text-o"></i> Generate</button>
        </div>
        <div class="ac-card" id="rptOutput"></div>
    </div>

    <!-- ═══════════ TAB 7: SETTINGS ═══════════ -->
    <div class="ac-panel<?= $at === 'settings' ? ' active' : '' ?>" id="panelSettings">
        <div class="ac-card">
            <div class="ac-card-title"><i class="fa fa-lock"></i> Period Lock</div>
            <p style="font-size:13px;color:var(--ac-text2);margin-bottom:12px;">
                Locking a period finalizes all journal entries on or before the selected date. This cannot be undone.
            </p>
            <div class="ac-toolbar" style="margin-bottom:0">
                <div class="ac-fg"><label>Current Lock</label><input type="text" id="settLockCurrent" readonly style="width:160px"></div>
                <div class="ac-fg"><label>Lock Until</label><input type="date" id="settLockDate"></div>
                <button class="ac-btn ac-btn-danger ac-role-admin" onclick="AC.lockPeriod()" style="display:none"><i class="fa fa-lock"></i> Lock Period</button>
            </div>
        </div>
        <div class="ac-card">
            <div class="ac-card-title"><i class="fa fa-database"></i> Migration</div>
            <p style="font-size:13px;color:var(--ac-text2);margin-bottom:12px;">
                Import existing Account Book entries into the Chart of Accounts.
            </p>
            <div id="migrationStatus" style="margin-bottom:12px;font-size:13px;"></div>
            <button class="ac-btn ac-btn-ghost ac-role-admin" onclick="AC.migrateAccounts()" style="display:none"><i class="fa fa-download"></i> Migrate Existing Accounts</button>
            <button class="ac-btn ac-btn-ghost ac-role-admin" onclick="AC.recomputeBalances()" style="margin-left:8px;display:none"><i class="fa fa-refresh"></i> Recompute Balances</button>
            <button class="ac-btn ac-btn-ghost ac-role-admin" onclick="AC.carryForward()" style="margin-left:8px;display:none"><i class="fa fa-forward"></i> Carry Forward Balances</button>
        </div>
        <div class="ac-card">
            <div class="ac-card-title"><i class="fa fa-hashtag"></i> Voucher Counters</div>
            <div id="settCounters" style="font-size:13px;color:var(--ac-text2);"></div>
        </div>
        <div class="ac-card">
            <div class="ac-card-title"><i class="fa fa-history"></i> Audit Trail</div>
            <p style="font-size:13px;color:var(--ac-text2);margin-bottom:12px;">
                Recent changes to accounting data.
            </p>
            <button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.loadAuditLog()" style="margin-bottom:12px"><i class="fa fa-refresh"></i> Refresh</button>
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
                    <tbody id="auditBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</section>
</div>

<!-- ══════ MODALS ══════ -->

<!-- Account Modal -->
<div class="ac-modal-overlay" id="accountModal">
    <div class="ac-modal">
        <div class="ac-modal-title" id="accountModalTitle">Add Account</div>
        <input type="hidden" id="amIsEdit" value="false">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="ac-fg"><label>Code *</label><input type="text" id="amCode" placeholder="e.g. 1021"></div>
            <div class="ac-fg"><label>Name *</label><input type="text" id="amName" placeholder="Account name"></div>
            <div class="ac-fg"><label>Category *</label>
                <select id="amCategory">
                    <option value="">Select</option>
                    <option>Asset</option><option>Liability</option><option>Equity</option>
                    <option>Income</option><option>Expense</option>
                </select>
            </div>
            <div class="ac-fg"><label>Sub-Category</label><input type="text" id="amSubCat" placeholder="e.g. Current Assets"></div>
            <div class="ac-fg"><label>Parent Code</label><input type="text" id="amParent" placeholder="e.g. 1000"></div>
            <div class="ac-fg"><label>Opening Balance</label><input type="number" id="amOpenBal" step="0.01" value="0"></div>
            <div class="ac-fg" style="grid-column:1/-1"><label>Description</label><input type="text" id="amDesc"></div>
            <div class="ac-fg"><label><input type="checkbox" id="amIsGroup"> Group Account (not postable)</label></div>
            <div class="ac-fg"><label><input type="checkbox" id="amIsBank"> Bank Account</label></div>
        </div>
        <div id="amBankFields" style="display:none;margin-top:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="ac-fg"><label>Bank Name</label><input type="text" id="amBankName"></div>
                <div class="ac-fg"><label>Branch</label><input type="text" id="amBranch"></div>
                <div class="ac-fg"><label>Account No</label><input type="text" id="amAccNo"></div>
                <div class="ac-fg"><label>IFSC</label><input type="text" id="amIfsc"></div>
            </div>
        </div>
        <div class="ac-modal-actions">
            <button class="ac-btn ac-btn-ghost" onclick="AC.closeModal('accountModal')">Cancel</button>
            <button class="ac-btn ac-btn-primary" onclick="AC.saveAccount()"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
</div>

<!-- Journal Entry Modal -->
<div class="ac-modal-overlay" id="journalModal">
    <div class="ac-modal" style="max-width:860px;">
        <div class="ac-modal-title">New Journal Entry</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
            <div class="ac-fg"><label>Date *</label><input type="date" id="jeDate"></div>
            <div class="ac-fg"><label>Voucher Type</label>
                <select id="jeVType"><option>Journal</option><option>Receipt</option><option>Payment</option><option>Contra</option></select>
            </div>
            <div class="ac-fg"><label>Voucher #</label><input type="text" id="jeVNo" readonly></div>
        </div>
        <div class="ac-fg" style="margin-bottom:14px;"><label>Narration</label><input type="text" id="jeNarration" placeholder="Description of the entry" style="width:100%"></div>

        <div style="font-size:12px;font-weight:600;text-transform:uppercase;color:var(--ac-text3);margin-bottom:6px;">Line Items</div>
        <table class="ac-table" style="margin-bottom:8px;">
            <thead><tr><th>Account</th><th style="width:140px">Debit</th><th style="width:140px">Credit</th><th style="width:40px"></th></tr></thead>
            <tbody id="jeLines"></tbody>
            <tfoot>
                <tr>
                    <td style="text-align:right;font-weight:700;">Totals:</td>
                    <td class="ac-num" id="jeTotalDr" style="font-weight:700;">0.00</td>
                    <td class="ac-num" id="jeTotalCr" style="font-weight:700;">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.addJELine()"><i class="fa fa-plus"></i> Add Line</button>
        <div id="jeError" style="color:var(--ac-red);font-size:13px;margin-top:8px;display:none;"></div>

        <div class="ac-modal-actions">
            <button class="ac-btn ac-btn-ghost" onclick="AC.closeModal('journalModal')">Cancel</button>
            <button class="ac-btn ac-btn-primary" onclick="AC.saveJournalEntry()"><i class="fa fa-check"></i> Save Entry</button>
        </div>
    </div>
</div>

<!-- Income/Expense Modal -->
<div class="ac-modal-overlay" id="ieModal">
    <div class="ac-modal">
        <div class="ac-modal-title" id="ieModalTitle">Record Income</div>
        <input type="hidden" id="ieFormType" value="income">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="ac-fg"><label>Date *</label><input type="date" id="ieFormDate"></div>
            <div class="ac-fg"><label>Amount *</label><input type="number" id="ieFormAmt" step="0.01" min="0"></div>
            <div class="ac-fg"><label>Account *</label><select id="ieFormAcct"></select></div>
            <div class="ac-fg"><label>Payment Mode</label>
                <select id="ieFormMode"><option>Cash</option><option>Bank</option><option>UPI</option><option>Cheque</option></select>
            </div>
            <div class="ac-fg"><label>Category</label><input type="text" id="ieFormCat" placeholder="e.g. Staff Salary"></div>
            <div class="ac-fg"><label>Receipt/Ref No</label><input type="text" id="ieFormRef"></div>
            <div class="ac-fg" style="grid-column:1/-1"><label>Description</label><input type="text" id="ieFormDesc" style="width:100%"></div>
            <div class="ac-fg"><label>Vendor</label><input type="text" id="ieFormVendor" placeholder="Vendor/Payee name"></div>
            <div class="ac-fg"><label>Pay via Bank Account</label><select id="ieFormBank"><option value="">Cash (1010)</option></select></div>
        </div>
        <div class="ac-modal-actions">
            <button class="ac-btn ac-btn-ghost" onclick="AC.closeModal('ieModal')">Cancel</button>
            <button class="ac-btn ac-btn-primary" onclick="AC.saveIE()"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
</div>

<!-- Bank Match Modal -->
<div class="ac-modal-overlay" id="matchModal">
    <div class="ac-modal" style="max-width:700px;">
        <div class="ac-modal-title">Match Transaction</div>
        <div style="margin-bottom:14px;padding:12px;background:var(--ac-bg3);border-radius:var(--ac-r);font-size:13px;">
            <div id="matchStmtInfo"></div>
        </div>
        <div style="font-size:12px;font-weight:600;text-transform:uppercase;color:var(--ac-text3);margin-bottom:6px;">Suggested Matches</div>
        <div class="ac-table-wrap">
            <table class="ac-table">
                <thead><tr><th>Date</th><th>Voucher</th><th>Narration</th><th class="ac-num">Dr</th><th class="ac-num">Cr</th><th>Score</th><th></th></tr></thead>
                <tbody id="matchSuggestions"></tbody>
            </table>
        </div>
        <div style="margin-top:12px;">
            <div class="ac-fg"><label>Or enter Ledger Entry ID manually</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="matchManualId" placeholder="e.g. JE_20260312..." style="flex:1;">
                    <button class="ac-btn ac-btn-primary ac-btn-sm" onclick="AC.doMatch(document.getElementById('matchManualId').value)">Match</button>
                </div>
            </div>
        </div>
        <div class="ac-modal-actions">
            <button class="ac-btn ac-btn-ghost" onclick="AC.closeModal('matchModal')">Cancel</button>
        </div>
    </div>
</div>

<div class="ac-toast" id="acToast"></div>

<script>
(function(){
    var BASE = '<?= base_url() ?>';
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
    var ADMIN_ROLE = '<?= $admin_role ?>';
    var IS_ADMIN = ['Admin','Super Admin','Our Panel'].indexOf(ADMIN_ROLE) >= 0;
    var IS_FINANCE = IS_ADMIN || ['Accountant','Finance'].indexOf(ADMIN_ROLE) >= 0;

    var coaCache = {}; // code → account object

    // ── Helpers ──
    function post(url, data) {
        var fd = new FormData();
        fd.append(CSRF_NAME, CSRF_HASH);
        if (data) Object.keys(data).forEach(function(k){ fd.append(k, data[k]); });
        return fetch(BASE + url, { method: 'POST', body: fd })
            .then(function(r){
                if (!r.ok) return { status: 'error', message: 'Server error (' + r.status + ')' };
                return r.json();
            })
            .then(function(j){ if (j && j.csrf_hash) CSRF_HASH = j.csrf_hash; return j; })
            .catch(function(e){ toast('Network error: ' + e.message, 'error'); return { status: 'error', message: e.message }; });
    }

    function getJSON(url) {
        return fetch(BASE + url)
            .then(function(r){
                if (!r.ok) return { status: 'error', message: 'Server error (' + r.status + ')' };
                return r.json();
            })
            .then(function(j){ if (j && j.csrf_hash) CSRF_HASH = j.csrf_hash; return j; })
            .catch(function(e){ toast('Network error: ' + e.message, 'error'); return { status: 'error', message: e.message }; });
    }

    function toast(msg, type) {
        var el = document.getElementById('acToast');
        el.textContent = msg; el.className = 'ac-toast ' + (type || 'success');
        setTimeout(function(){ el.classList.add('show'); }, 10);
        setTimeout(function(){ el.classList.remove('show'); }, 3000);
    }

    function fmt(n) { return Number(n || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    function catBadge(cat) {
        return '<span class="ac-badge ac-badge-' + (cat || '').toLowerCase() + '">' + esc(cat) + '</span>';
    }

    // ── Role-based visibility ──
    function applyRoleVisibility() {
        var adminEls = document.querySelectorAll('.ac-role-admin');
        for (var i = 0; i < adminEls.length; i++) {
            adminEls[i].style.display = IS_ADMIN ? '' : 'none';
        }
    }

    // Tabs use clean URLs (<a> links) — no JS switching needed.
    // Each tab click triggers a full page reload with the correct active_tab from PHP.

    // ══════════════════════════════════════════════
    //  CHART OF ACCOUNTS
    // ══════════════════════════════════════════════
    function loadCoA() {
        getJSON('accounting/get_chart').then(function(r) {
            if (r.status !== 'success') return toast(r.message, 'error');
            coaCache = r.accounts || {};
            renderCoA();
            populateAccountDropdowns();
        });
    }

    function renderCoA() {
        var body = document.getElementById('coaBody');
        var accounts = coaCache;
        var codes = Object.keys(accounts).sort(function(a,b){ return a.localeCompare(b, undefined, {numeric:true}); });

        if (!codes.length) {
            body.innerHTML = '<tr><td colspan="6" class="ac-empty">No accounts yet. Click "Seed Defaults" to create a standard chart.</td></tr>';
            return;
        }

        var html = '';
        codes.forEach(function(code) {
            var a = accounts[code];
            var indent = a.parent_code ? (accounts[a.parent_code] && accounts[a.parent_code].parent_code ? 'ac-indent-2' : 'ac-indent-1') : '';
            var rowCls = a.is_group ? 'ac-group-row' : '';

            html += '<tr class="' + rowCls + '">'
                + '<td><code>' + esc(code) + '</code></td>'
                + '<td class="' + indent + '">' + (a.is_group ? '<i class="fa fa-folder-o" style="margin-right:6px;color:var(--ac-text3)"></i>' : '') + esc(a.name) + (a.is_bank ? ' <i class="fa fa-bank" style="color:var(--ac-blue);font-size:12px;margin-left:4px;" title="Bank Account"></i>' : '') + '</td>'
                + '<td>' + catBadge(a.category) + '</td>'
                + '<td style="font-size:12px;color:var(--ac-text2);">' + esc(a.sub_category || '') + '</td>'
                + '<td class="ac-num">' + fmt(a.opening_balance) + '</td>'
                + '<td>'
                + (a.is_system ? '<span style="font-size:11px;color:var(--ac-text3)">System</span>'
                    : '<button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.editAccount(\'' + code + '\')"><i class="fa fa-pencil"></i></button> '
                    + (IS_ADMIN ? '<button class="ac-btn ac-btn-danger ac-btn-sm" onclick="AC.deleteAccount(\'' + code + '\')"><i class="fa fa-trash"></i></button>' : ''))
                + '</td></tr>';
        });
        body.innerHTML = html;
    }

    function populateAccountDropdowns() {
        var codes = Object.keys(coaCache).sort(function(a,b){ return a.localeCompare(b, undefined, {numeric:true}); });

        // Ledger filter
        var ledgerAcct = document.getElementById('ledgerAcct');
        var prevVal = ledgerAcct.value;
        ledgerAcct.innerHTML = '<option value="">All</option>';
        codes.forEach(function(c){
            var a = coaCache[c];
            if (a.is_group) return;
            ledgerAcct.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
        });
        ledgerAcct.value = prevVal;

        // IE form accounts
        var ieFormAcct = document.getElementById('ieFormAcct');
        ieFormAcct.innerHTML = '<option value="">Select Account</option>';
        codes.forEach(function(c){
            var a = coaCache[c];
            if (a.is_group) return;
            ieFormAcct.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
        });

        // IE bank dropdown
        var ieFormBank = document.getElementById('ieFormBank');
        ieFormBank.innerHTML = '<option value="">Cash (1010)</option>';
        codes.forEach(function(c){
            var a = coaCache[c];
            if (a.is_bank) ieFormBank.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
        });
    }

    function showAccountModal(code) {
        var isEdit = !!code;
        document.getElementById('amIsEdit').value = isEdit ? 'true' : 'false';
        document.getElementById('accountModalTitle').textContent = isEdit ? 'Edit Account' : 'Add Account';

        if (isEdit && coaCache[code]) {
            var a = coaCache[code];
            document.getElementById('amCode').value = a.code; document.getElementById('amCode').readOnly = true;
            document.getElementById('amName').value = a.name;
            document.getElementById('amCategory').value = a.category;
            document.getElementById('amSubCat').value = a.sub_category || '';
            document.getElementById('amParent').value = a.parent_code || '';
            document.getElementById('amOpenBal').value = a.opening_balance || 0;
            document.getElementById('amDesc').value = a.description || '';
            document.getElementById('amIsGroup').checked = !!a.is_group;
            document.getElementById('amIsBank').checked = !!a.is_bank;
            if (a.bank_details) {
                document.getElementById('amBankName').value = a.bank_details.bank_name || '';
                document.getElementById('amBranch').value = a.bank_details.branch || '';
                document.getElementById('amAccNo').value = a.bank_details.account_no || '';
                document.getElementById('amIfsc').value = a.bank_details.ifsc || '';
            }
        } else {
            ['amCode','amName','amSubCat','amParent','amDesc','amBankName','amBranch','amAccNo','amIfsc'].forEach(function(id){ document.getElementById(id).value = ''; });
            document.getElementById('amCode').readOnly = false;
            document.getElementById('amCategory').value = '';
            document.getElementById('amOpenBal').value = '0';
            document.getElementById('amIsGroup').checked = false;
            document.getElementById('amIsBank').checked = false;
        }
        document.getElementById('amBankFields').style.display = document.getElementById('amIsBank').checked ? 'block' : 'none';
        document.getElementById('accountModal').classList.add('show');
    }

    document.getElementById('amIsBank').addEventListener('change', function(){
        document.getElementById('amBankFields').style.display = this.checked ? 'block' : 'none';
    });

    function saveAccount() {
        post('accounting/save_account', {
            code: document.getElementById('amCode').value,
            name: document.getElementById('amName').value,
            category: document.getElementById('amCategory').value,
            sub_category: document.getElementById('amSubCat').value,
            parent_code: document.getElementById('amParent').value,
            opening_balance: document.getElementById('amOpenBal').value,
            description: document.getElementById('amDesc').value,
            is_group: document.getElementById('amIsGroup').checked ? 'true' : 'false',
            is_bank: document.getElementById('amIsBank').checked ? 'true' : 'false',
            is_edit: document.getElementById('amIsEdit').value,
            bank_name: document.getElementById('amBankName').value,
            branch: document.getElementById('amBranch').value,
            account_no: document.getElementById('amAccNo').value,
            ifsc: document.getElementById('amIfsc').value,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); closeModal('accountModal'); loadCoA();
        });
    }

    function editAccount(code) { showAccountModal(code); }

    function deleteAccount(code) {
        if (!confirm('Delete account ' + code + '?')) return;
        post('accounting/delete_account', { code: code }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadCoA();
        });
    }

    function seedChart() {
        if (!confirm('Seed default Indian school chart of accounts?')) return;
        post('accounting/seed_default_chart').then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadCoA();
        });
    }

    // ══════════════════════════════════════════════
    //  JOURNAL ENTRIES
    // ══════════════════════════════════════════════
    var ledgerOffset = 0, ledgerLimit = 100, ledgerHasMore = false;

    function loadLedger(append) {
        if (!append) ledgerOffset = 0;
        post('accounting/get_ledger_entries', {
            date_from: document.getElementById('ledgerFrom').value,
            date_to: document.getElementById('ledgerTo').value,
            account_code: document.getElementById('ledgerAcct').value,
            voucher_type: document.getElementById('ledgerVType').value,
            limit: ledgerLimit,
            offset: ledgerOffset,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            var body = document.getElementById('ledgerBody');
            if (!append) body.innerHTML = '';

            if (!r.entries.length && !append) {
                body.innerHTML = '<tr><td colspan="8" class="ac-empty">No entries found.</td></tr>';
                document.getElementById('ledgerPagination').style.display = 'none';
                return;
            }

            var html = '';
            r.entries.forEach(function(e){
                var st = e.is_finalized ? '<span class="ac-badge ac-badge-finalized">Finalized</span>' : '<span class="ac-badge ac-badge-draft">Draft</span>';
                html += '<tr>'
                    + '<td>' + esc(e.date) + '</td>'
                    + '<td><code>' + esc(e.voucher_no) + '</code></td>'
                    + '<td>' + esc(e.voucher_type) + '</td>'
                    + '<td>' + esc(e.narration) + '</td>'
                    + '<td class="ac-num ac-dr">' + fmt(e.total_dr) + '</td>'
                    + '<td class="ac-num ac-cr">' + fmt(e.total_cr) + '</td>'
                    + '<td>' + st + '</td>'
                    + '<td>'
                    + (!e.is_finalized ? '<button class="ac-btn ac-btn-danger ac-btn-sm" onclick="AC.deleteJE(\'' + e.id + '\')"><i class="fa fa-trash"></i></button> ' : '')
                    + (!e.is_finalized ? '<button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.finalizeJE(\'' + e.id + '\')"><i class="fa fa-lock"></i></button>' : '')
                    + '</td></tr>';

                // Show line items
                (e.lines || []).forEach(function(l){
                    html += '<tr style="background:var(--ac-bg);font-size:12px;"><td></td><td colspan="2" style="padding-left:24px;">'
                        + '<code>' + esc(l.account_code) + '</code> ' + esc(l.account_name) + '</td><td>' + esc(l.narration || '') + '</td>'
                        + '<td class="ac-num">' + (l.dr > 0 ? fmt(l.dr) : '') + '</td>'
                        + '<td class="ac-num">' + (l.cr > 0 ? fmt(l.cr) : '') + '</td><td></td><td></td></tr>';
                });
            });
            body.innerHTML += html;

            ledgerHasMore = r.has_more || false;
            ledgerOffset += r.entries.length;

            var total = r.total || r.entries.length;
            var pag = document.getElementById('ledgerPagination');
            pag.style.display = (total > 0) ? 'block' : 'none';
            document.getElementById('ledgerCount').textContent = 'Showing ' + Math.min(ledgerOffset, total) + ' of ' + total;
            document.getElementById('ledgerLoadMore').style.display = ledgerHasMore ? 'inline-flex' : 'none';
        });
    }

    function loadMoreLedger() { loadLedger(true); }

    function showJournalModal() {
        document.getElementById('jeDate').value = new Date().toISOString().slice(0, 10);
        document.getElementById('jeVType').value = 'Journal';
        document.getElementById('jeNarration').value = '';
        document.getElementById('jeLines').innerHTML = '';
        document.getElementById('jeError').style.display = 'none';
        addJELine(); addJELine();

        getJSON('accounting/get_next_voucher_no?type=Journal').then(function(r){
            document.getElementById('jeVNo').value = (r && r.voucher_no) || '';
        });

        document.getElementById('journalModal').classList.add('show');
    }

    // Update voucher number when type changes
    document.getElementById('jeVType').addEventListener('change', function(){
        getJSON('accounting/get_next_voucher_no?type=' + this.value).then(function(r){
            document.getElementById('jeVNo').value = (r && r.voucher_no) || '';
        });
    });

    function addJELine() {
        var codes = Object.keys(coaCache).sort(function(a,b){ return a.localeCompare(b, undefined, {numeric:true}); });
        var opts = '<option value="">Select Account</option>';
        codes.forEach(function(c){
            var a = coaCache[c]; if (a.is_group) return;
            opts += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
        });

        var tr = document.createElement('tr');
        tr.innerHTML = '<td><select class="je-acct" style="width:100%;padding:6px;font-size:13px;border:1px solid var(--ac-border);border-radius:4px;">' + opts + '</select></td>'
            + '<td><input type="number" class="je-dr" step="0.01" min="0" value="" style="width:100%;padding:6px;font-size:13px;border:1px solid var(--ac-border);border-radius:4px;text-align:right;"></td>'
            + '<td><input type="number" class="je-cr" step="0.01" min="0" value="" style="width:100%;padding:6px;font-size:13px;border:1px solid var(--ac-border);border-radius:4px;text-align:right;"></td>'
            + '<td><button class="ac-btn ac-btn-danger ac-btn-sm" onclick="this.closest(\'tr\').remove();AC.updateJETotals();"><i class="fa fa-times"></i></button></td>';
        document.getElementById('jeLines').appendChild(tr);

        // Auto-clear other field on input
        tr.querySelector('.je-dr').addEventListener('input', function(){ if (this.value) tr.querySelector('.je-cr').value = ''; updateJETotals(); });
        tr.querySelector('.je-cr').addEventListener('input', function(){ if (this.value) tr.querySelector('.je-dr').value = ''; updateJETotals(); });
    }

    function updateJETotals() {
        var dr = 0, cr = 0;
        document.querySelectorAll('#jeLines tr').forEach(function(tr){
            dr += parseFloat(tr.querySelector('.je-dr').value || 0);
            cr += parseFloat(tr.querySelector('.je-cr').value || 0);
        });
        document.getElementById('jeTotalDr').textContent = fmt(dr);
        document.getElementById('jeTotalCr').textContent = fmt(cr);
        var errEl = document.getElementById('jeError');
        if (Math.abs(dr - cr) > 0.01 && dr > 0 && cr > 0) {
            errEl.textContent = 'Debit (' + fmt(dr) + ') does not equal Credit (' + fmt(cr) + ')';
            errEl.style.display = 'block';
        } else {
            errEl.style.display = 'none';
        }
    }

    function saveJournalEntry() {
        var lines = [];
        document.querySelectorAll('#jeLines tr').forEach(function(tr){
            var code = tr.querySelector('.je-acct').value;
            if (!code) return;
            var acctName = coaCache[code] ? coaCache[code].name : code;
            lines.push({
                account_code: code,
                account_name: acctName,
                dr: parseFloat(tr.querySelector('.je-dr').value || 0),
                cr: parseFloat(tr.querySelector('.je-cr').value || 0),
            });
        });

        post('accounting/save_journal_entry', {
            date: document.getElementById('jeDate').value,
            voucher_type: document.getElementById('jeVType').value,
            narration: document.getElementById('jeNarration').value,
            lines: JSON.stringify(lines),
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); closeModal('journalModal'); loadLedger();
        });
    }

    function deleteJE(id) {
        if (!confirm('Delete this journal entry?')) return;
        post('accounting/delete_journal_entry', { entry_id: id }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadLedger();
        });
    }

    function finalizeJE(id) {
        if (!confirm('Finalize this entry? It cannot be edited or deleted after.')) return;
        post('accounting/finalize_entry', { entry_id: id }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadLedger();
        });
    }

    // ══════════════════════════════════════════════
    //  INCOME & EXPENSE
    // ══════════════════════════════════════════════
    var ieOffset = 0, ieLimit = 100, ieHasMore = false;

    function loadIE(append) {
        if (!append) ieOffset = 0;
        post('accounting/get_income_expenses', {
            type: document.getElementById('ieType').value,
            date_from: document.getElementById('ieFrom').value,
            date_to: document.getElementById('ieTo').value,
            limit: ieLimit,
            offset: ieOffset,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            var body = document.getElementById('ieBody');
            if (!append) body.innerHTML = '';
            var totalInc = 0, totalExp = 0;

            if (!r.records.length && !append) {
                body.innerHTML = '<tr><td colspan="7" class="ac-empty">No records found.</td></tr>';
                document.getElementById('iePagination').style.display = 'none';
            }
            else {
                var html = '';
                r.records.forEach(function(rec){
                    var acctName = coaCache[rec.account_code] ? coaCache[rec.account_code].name : rec.account_code;
                    var typeBadge = rec.type === 'income'
                        ? '<span class="ac-badge ac-badge-income">Income</span>'
                        : '<span class="ac-badge ac-badge-expense">Expense</span>';
                    if (rec.type === 'income') totalInc += rec.amount; else totalExp += rec.amount;

                    html += '<tr>'
                        + '<td>' + esc(rec.date) + '</td>'
                        + '<td>' + typeBadge + '</td>'
                        + '<td>' + esc(rec.account_code) + ' - ' + esc(acctName) + '</td>'
                        + '<td>' + esc(rec.description) + '</td>'
                        + '<td>' + esc(rec.payment_mode || '') + '</td>'
                        + '<td class="ac-num">' + fmt(rec.amount) + '</td>'
                        + '<td><button class="ac-btn ac-btn-danger ac-btn-sm" onclick="AC.deleteIE(\'' + rec.id + '\')"><i class="fa fa-trash"></i></button></td>'
                        + '</tr>';
                });
                body.innerHTML += html;

                ieHasMore = r.has_more || false;
                ieOffset += r.records.length;

                var total = r.total || r.records.length;
                var pag = document.getElementById('iePagination');
                pag.style.display = (total > 0) ? 'block' : 'none';
                document.getElementById('ieCount').textContent = 'Showing ' + Math.min(ieOffset, total) + ' of ' + total;
                document.getElementById('ieLoadMore').style.display = ieHasMore ? 'inline-flex' : 'none';
            }

            if (!append) {
                // Load full summary from dedicated endpoint
                post('accounting/get_income_expense_summary').then(function(sr){
                    if (sr.status !== 'success') return;
                    var sumInc = 0, sumExp = 0;
                    var months = sr.summary || {};
                    Object.keys(months).forEach(function(m){
                        sumInc += months[m].income || 0;
                        sumExp += months[m].expense || 0;
                    });
                    document.getElementById('ieSummary').innerHTML =
                        '<div class="ac-stat"><div class="ac-stat-label">Total Income</div><div class="ac-stat-value" style="color:var(--ac-green)">' + fmt(sumInc) + '</div></div>'
                        + '<div class="ac-stat"><div class="ac-stat-label">Total Expenses</div><div class="ac-stat-value" style="color:var(--ac-red)">' + fmt(sumExp) + '</div></div>'
                        + '<div class="ac-stat"><div class="ac-stat-label">Net</div><div class="ac-stat-value">' + fmt(sumInc - sumExp) + '</div></div>';
                });
            }
        });
    }

    function loadMoreIE() { loadIE(true); }

    function showIEModal(type) {
        document.getElementById('ieFormType').value = type;
        document.getElementById('ieModalTitle').textContent = type === 'income' ? 'Record Income' : 'Record Expense';
        document.getElementById('ieFormDate').value = new Date().toISOString().slice(0, 10);
        document.getElementById('ieFormMode').value = 'Cash';
        ['ieFormAmt','ieFormCat','ieFormRef','ieFormDesc','ieFormVendor'].forEach(function(id){ document.getElementById(id).value = ''; });

        var codes = Object.keys(coaCache).sort(function(a,b){ return a.localeCompare(b, undefined, {numeric:true}); });

        // Filter accounts by type
        var sel = document.getElementById('ieFormAcct');
        sel.innerHTML = '<option value="">Select Account</option>';
        var filterCat = type === 'income' ? 'Income' : 'Expense';
        codes.forEach(function(c){
            var a = coaCache[c]; if (a.is_group) return;
            if (a.category === filterCat) {
                sel.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
            }
        });

        // Refresh bank account dropdown
        var bankSel = document.getElementById('ieFormBank');
        bankSel.innerHTML = '<option value="">Cash (1010)</option>';
        codes.forEach(function(c){
            var a = coaCache[c];
            if (a.is_bank) bankSel.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
        });

        document.getElementById('ieModal').classList.add('show');
    }

    function saveIE() {
        post('accounting/save_income_expense', {
            type: document.getElementById('ieFormType').value,
            date: document.getElementById('ieFormDate').value,
            amount: document.getElementById('ieFormAmt').value,
            account_code: document.getElementById('ieFormAcct').value,
            payment_mode: document.getElementById('ieFormMode').value,
            bank_account_code: document.getElementById('ieFormBank').value,
            category: document.getElementById('ieFormCat').value,
            receipt_no: document.getElementById('ieFormRef').value,
            description: document.getElementById('ieFormDesc').value,
            vendor: document.getElementById('ieFormVendor').value,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); closeModal('ieModal'); loadIE();
        });
    }

    function deleteIE(id) {
        if (!confirm('Delete this record and its journal entry?')) return;
        post('accounting/delete_income_expense', { id: id }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadIE();
        });
    }

    // ══════════════════════════════════════════════
    //  CASH BOOK
    // ══════════════════════════════════════════════
    function populateCashBookAccounts() {
        if (!Object.keys(coaCache).length) {
            getJSON('accounting/get_chart').then(function(r){
                if (r.status === 'success') { coaCache = r.accounts || {}; populateAccountDropdowns(); _fillCBDropdown(); }
            });
        } else { _fillCBDropdown(); }
    }

    function _fillCBDropdown() {
        var sel = document.getElementById('cbAccount');
        sel.innerHTML = '';
        Object.keys(coaCache).sort().forEach(function(c){
            var a = coaCache[c];
            if (a.is_group) return;
            if (c === '1010' || a.is_bank || (a.sub_category||'').toLowerCase().indexOf('cash') >= 0) {
                sel.innerHTML += '<option value="' + c + '">' + c + ' - ' + esc(a.name) + '</option>';
            }
        });
    }

    function loadCashBook() {
        post('accounting/get_cash_book', {
            account_code: document.getElementById('cbAccount').value,
            date_from: document.getElementById('cbFrom').value,
            date_to: document.getElementById('cbTo').value,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');

            document.getElementById('cbStats').innerHTML =
                '<div class="ac-stat"><div class="ac-stat-label">Opening Balance</div><div class="ac-stat-value">' + fmt(r.opening_balance) + '</div></div>'
                + '<div class="ac-stat"><div class="ac-stat-label">Closing Balance</div><div class="ac-stat-value">' + fmt(r.closing_balance) + '</div></div>';

            var body = document.getElementById('cbBody');
            if (!r.transactions.length) { body.innerHTML = '<tr><td colspan="6" class="ac-empty">No transactions.</td></tr>'; return; }

            var html = '';
            r.transactions.forEach(function(t){
                html += '<tr>'
                    + '<td>' + esc(t.date) + '</td>'
                    + '<td><code>' + esc(t.voucher_no) + '</code></td>'
                    + '<td>' + esc(t.narration) + '</td>'
                    + '<td class="ac-num ac-dr">' + (t.dr > 0 ? fmt(t.dr) : '') + '</td>'
                    + '<td class="ac-num ac-cr">' + (t.cr > 0 ? fmt(t.cr) : '') + '</td>'
                    + '<td class="ac-num">' + fmt(t.balance) + '</td>'
                    + '</tr>';
            });
            body.innerHTML = html;
        });
    }

    // ══════════════════════════════════════════════
    //  BANK RECONCILIATION
    // ══════════════════════════════════════════════
    function loadBankAccounts() {
        getJSON('accounting/get_bank_accounts').then(function(r){
            if (r.status !== 'success') return;
            var sel = document.getElementById('brAccount');
            sel.innerHTML = '';
            (r.banks || []).forEach(function(b){
                sel.innerHTML += '<option value="' + b.code + '">' + esc(b.name) + '</option>';
            });
        });
    }

    function loadBankRecon() {
        var code = document.getElementById('brAccount').value;
        if (!code) return toast('Select a bank account.', 'error');

        // Load statement + summary in parallel
        post('accounting/get_bank_statement', {
            account_code: code,
            date_from: document.getElementById('brFrom').value,
            date_to: document.getElementById('brTo').value,
        }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            var body = document.getElementById('brBody');
            if (!r.items.length) { body.innerHTML = '<tr><td colspan="7" class="ac-empty">No statement entries. Import a CSV.</td></tr>'; return; }

            var html = '';
            r.items.forEach(function(item){
                var statusBadge = item.status === 'matched'
                    ? '<span class="ac-badge ac-badge-matched">Matched</span>'
                    : '<span class="ac-badge ac-badge-unmatched">Unmatched</span>';
                html += '<tr>'
                    + '<td>' + esc(item.statement_date) + '</td>'
                    + '<td>' + esc(item.description) + '</td>'
                    + '<td>' + esc(item.reference || '') + '</td>'
                    + '<td class="ac-num">' + (item.debit > 0 ? fmt(item.debit) : '') + '</td>'
                    + '<td class="ac-num">' + (item.credit > 0 ? fmt(item.credit) : '') + '</td>'
                    + '<td>' + statusBadge + '</td>'
                    + '<td>'
                    + (item.status === 'unmatched'
                        ? '<button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.matchPrompt(\'' + code + '\',\'' + item.id + '\')"><i class="fa fa-link"></i> Match</button>'
                        : '<button class="ac-btn ac-btn-ghost ac-btn-sm" onclick="AC.unmatchTxn(\'' + code + '\',\'' + item.id + '\')"><i class="fa fa-chain-broken"></i></button>')
                    + '</td>'
                    + '</tr>';
            });
            body.innerHTML = html;
        });

        post('accounting/get_recon_summary', { account_code: code }).then(function(r){
            if (r.status !== 'success') return;
            document.getElementById('brStats').innerHTML =
                '<div class="ac-stat"><div class="ac-stat-label">Bank Balance</div><div class="ac-stat-value">' + fmt(r.bank_balance) + '</div></div>'
                + '<div class="ac-stat"><div class="ac-stat-label">Book Balance</div><div class="ac-stat-value">' + fmt(r.book_balance) + '</div></div>'
                + '<div class="ac-stat"><div class="ac-stat-label">Difference</div><div class="ac-stat-value" style="color:' + (Math.abs(r.difference) < 0.01 ? 'var(--ac-green)' : 'var(--ac-red)') + '">' + fmt(r.difference) + '</div></div>'
                + '<div class="ac-stat"><div class="ac-stat-label">Unmatched</div><div class="ac-stat-value">' + r.unmatched + '</div></div>';
        });
    }

    function showImportCSV() {
        var code = document.getElementById('brAccount').value;
        if (!code) return toast('Select a bank account first.', 'error');

        var input = document.createElement('input');
        input.type = 'file'; input.accept = '.csv';
        input.onchange = function(){
            var fd = new FormData();
            fd.append(CSRF_NAME, CSRF_HASH);
            fd.append('account_code', code);
            fd.append('csv_file', input.files[0]);
            fetch(BASE + 'accounting/import_bank_statement', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(r){
                    if (r && r.csrf_hash) CSRF_HASH = r.csrf_hash;
                    if (r.status !== 'success') return toast(r.message, 'error');
                    toast(r.message); loadBankRecon();
                });
        };
        input.click();
    }

    // ── Bank Match with suggestions ──
    var _matchCode = '', _matchReconId = '';

    function matchPrompt(code, reconId) {
        _matchCode = code;
        _matchReconId = reconId;

        document.getElementById('matchStmtInfo').textContent = 'Loading...';
        document.getElementById('matchSuggestions').innerHTML = '';
        document.getElementById('matchManualId').value = '';

        post('accounting/suggest_matches', { account_code: code, recon_id: reconId }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');

            document.getElementById('matchStmtInfo').textContent = 'Select a matching ledger entry for statement item';

            var body = document.getElementById('matchSuggestions');
            if (!(r.suggestions || []).length) {
                body.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--ac-text3);padding:12px;">No matching entries found. Enter ID manually below.</td></tr>';
            } else {
                var html = '';
                r.suggestions.forEach(function(s){
                    var scoreBadge = s.score >= 100 ? 'ac-badge-income' : (s.score >= 50 ? 'ac-badge-expense' : 'ac-badge-draft');
                    html += '<tr>'
                        + '<td>' + esc(s.date) + '</td>'
                        + '<td><code>' + esc(s.voucher_no) + '</code></td>'
                        + '<td>' + esc(s.narration) + '</td>'
                        + '<td class="ac-num">' + (s.dr > 0 ? fmt(s.dr) : '') + '</td>'
                        + '<td class="ac-num">' + (s.cr > 0 ? fmt(s.cr) : '') + '</td>'
                        + '<td><span class="ac-badge ' + scoreBadge + '">' + s.score + '%</span></td>'
                        + '<td><button class="ac-btn ac-btn-primary ac-btn-sm" onclick="AC.doMatch(\'' + esc(s.entry_id) + '\')"><i class="fa fa-check"></i></button></td>'
                        + '</tr>';
                });
                body.innerHTML = html;
            }
            document.getElementById('matchModal').classList.add('show');
        });
    }

    function doMatch(ledgerId) {
        if (!ledgerId) return toast('Enter a ledger entry ID.', 'error');
        post('accounting/match_transaction', { account_code: _matchCode, recon_id: _matchReconId, ledger_id: ledgerId }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); closeModal('matchModal'); loadBankRecon();
        });
    }

    function unmatchTxn(code, reconId) {
        if (!confirm('Unmatch this transaction?')) return;
        post('accounting/unmatch_transaction', { account_code: code, recon_id: reconId }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadBankRecon();
        });
    }

    // ══════════════════════════════════════════════
    //  REPORTS
    // ══════════════════════════════════════════════
    function generateReport() {
        var type = document.getElementById('rptType').value;
        var container = document.getElementById('rptOutput');
        container.innerHTML = '<p class="ac-empty">Generating...</p>';

        post('accounting/' + type, {
            as_of_date: document.getElementById('rptFrom').value,
            date_from: document.getElementById('rptFrom').value,
            date_to: document.getElementById('rptTo').value,
        }).then(function(r){
            if (r.status !== 'success') return container.innerHTML = '<p style="color:var(--ac-red);">' + esc(r.message) + '</p>';

            if (type === 'trial_balance') renderTrialBalance(r, container);
            else if (type === 'profit_loss') renderProfitLoss(r, container);
            else if (type === 'balance_sheet') renderBalanceSheet(r, container);
            else if (type === 'cash_flow') renderCashFlow(r, container);
        });
    }

    function renderTrialBalance(r, el) {
        var html = '<h3 style="margin:0 0 14px;">Trial Balance</h3><table class="ac-table"><thead><tr><th>Code</th><th>Account</th><th>Category</th><th class="ac-num">Debit</th><th class="ac-num">Credit</th></tr></thead><tbody>';
        (r.rows || []).forEach(function(row){
            html += '<tr><td><code>' + esc(row.code) + '</code></td><td>' + esc(row.name) + '</td><td>' + catBadge(row.category) + '</td>'
                + '<td class="ac-num ac-dr">' + (row.dr > 0 ? fmt(row.dr) : '') + '</td>'
                + '<td class="ac-num ac-cr">' + (row.cr > 0 ? fmt(row.cr) : '') + '</td></tr>';
        });
        html += '</tbody><tfoot><tr><td colspan="3" style="text-align:right;font-weight:700;">Totals</td>'
            + '<td class="ac-num ac-dr">' + fmt(r.totals.dr) + '</td><td class="ac-num ac-cr">' + fmt(r.totals.cr) + '</td></tr></tfoot></table>';
        var diff = Math.abs(r.totals.dr - r.totals.cr);
        if (diff > 0.01) html += '<p style="color:var(--ac-red);margin-top:8px;">Difference: ' + fmt(diff) + ' (out of balance)</p>';
        else html += '<p style="color:var(--ac-green);margin-top:8px;">Balanced</p>';
        el.innerHTML = html;
    }

    function renderProfitLoss(r, el) {
        var html = '<h3 style="margin:0 0 14px;">Profit & Loss Statement</h3>';

        html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';
        // Income
        html += '<div><h4 style="color:var(--ac-green);margin-bottom:8px;">Income</h4><table class="ac-table"><thead><tr><th>Account</th><th class="ac-num">Amount</th></tr></thead><tbody>';
        (r.income || []).forEach(function(row){ html += '<tr><td>' + esc(row.name) + '</td><td class="ac-num">' + fmt(row.amount) + '</td></tr>'; });
        html += '</tbody><tfoot><tr><td style="font-weight:700;">Total Income</td><td class="ac-num" style="font-weight:700;color:var(--ac-green)">' + fmt(r.total_income) + '</td></tr></tfoot></table></div>';

        // Expenses
        html += '<div><h4 style="color:var(--ac-red);margin-bottom:8px;">Expenses</h4><table class="ac-table"><thead><tr><th>Account</th><th class="ac-num">Amount</th></tr></thead><tbody>';
        (r.expenses || []).forEach(function(row){ html += '<tr><td>' + esc(row.name) + '</td><td class="ac-num">' + fmt(row.amount) + '</td></tr>'; });
        html += '</tbody><tfoot><tr><td style="font-weight:700;">Total Expenses</td><td class="ac-num" style="font-weight:700;color:var(--ac-red)">' + fmt(r.total_expense) + '</td></tr></tfoot></table></div>';
        html += '</div>';

        var netColor = r.net_profit >= 0 ? 'var(--ac-green)' : 'var(--ac-red)';
        html += '<div style="margin-top:16px;padding:16px;background:var(--ac-bg3);border-radius:var(--ac-r);text-align:center;">'
            + '<span style="font-size:18px;font-weight:700;color:' + netColor + ';">' + (r.net_profit >= 0 ? 'Net Profit' : 'Net Loss') + ': ' + fmt(Math.abs(r.net_profit)) + '</span></div>';
        el.innerHTML = html;
    }

    function renderBalanceSheet(r, el) {
        var html = '<h3 style="margin:0 0 14px;">Balance Sheet</h3>';
        html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';

        // Assets
        html += '<div><h4 style="margin-bottom:8px;">Assets</h4><table class="ac-table"><thead><tr><th>Account</th><th class="ac-num">Amount</th></tr></thead><tbody>';
        (r.assets || []).forEach(function(row){ html += '<tr><td>' + esc(row.name) + '</td><td class="ac-num">' + fmt(row.amount) + '</td></tr>'; });
        html += '</tbody><tfoot><tr><td style="font-weight:700;">Total Assets</td><td class="ac-num" style="font-weight:700;">' + fmt(r.totals.assets) + '</td></tr></tfoot></table></div>';

        // Liabilities + Equity
        html += '<div><h4 style="margin-bottom:8px;">Liabilities & Equity</h4><table class="ac-table"><thead><tr><th>Account</th><th class="ac-num">Amount</th></tr></thead><tbody>';
        (r.liabilities || []).forEach(function(row){ html += '<tr><td>' + esc(row.name) + '</td><td class="ac-num">' + fmt(row.amount) + '</td></tr>'; });
        (r.equity || []).forEach(function(row){ html += '<tr><td><em>' + esc(row.name) + '</em></td><td class="ac-num">' + fmt(row.amount) + '</td></tr>'; });
        html += '</tbody><tfoot><tr><td style="font-weight:700;">Total Liab. + Equity</td><td class="ac-num" style="font-weight:700;">' + fmt(r.totals.liabilities_equity) + '</td></tr></tfoot></table></div>';
        html += '</div>';

        var diff = Math.abs(r.totals.assets - r.totals.liabilities_equity);
        if (diff > 0.01) html += '<p style="color:var(--ac-red);margin-top:8px;">Difference: ' + fmt(diff) + ' (not balanced)</p>';
        else html += '<p style="color:var(--ac-green);margin-top:8px;">Balanced (Assets = Liabilities + Equity)</p>';
        el.innerHTML = html;
    }

    function renderCashFlow(r, el) {
        el.innerHTML = '<h3 style="margin:0 0 14px;">Cash Flow Statement</h3>'
            + '<div class="ac-stats">'
            + '<div class="ac-stat"><div class="ac-stat-label">Operating</div><div class="ac-stat-value">' + fmt(r.operating) + '</div></div>'
            + '<div class="ac-stat"><div class="ac-stat-label">Investing</div><div class="ac-stat-value">' + fmt(r.investing) + '</div></div>'
            + '<div class="ac-stat"><div class="ac-stat-label">Financing</div><div class="ac-stat-value">' + fmt(r.financing) + '</div></div>'
            + '<div class="ac-stat"><div class="ac-stat-label">Net Change</div><div class="ac-stat-value" style="color:' + (r.net_change >= 0 ? 'var(--ac-green)' : 'var(--ac-red)') + '">' + fmt(r.net_change) + '</div></div>'
            + '</div>';
    }

    // ══════════════════════════════════════════════
    //  SETTINGS
    // ══════════════════════════════════════════════
    function loadSettings() {
        getJSON('accounting/get_settings').then(function(r){
            if (r.status !== 'success') return;
            document.getElementById('settLockCurrent').value = (r.period_lock && r.period_lock.locked_until) || 'None';
            var countersHtml = '';
            var counters = r.counters || {};
            Object.keys(counters).forEach(function(k){ countersHtml += '<p><strong>' + esc(k) + ':</strong> ' + counters[k] + '</p>'; });
            document.getElementById('settCounters').innerHTML = countersHtml || '<p>No counters yet.</p>';
        });

        getJSON('accounting/get_migration_status').then(function(r){
            if (r.status !== 'success') return;
            document.getElementById('migrationStatus').innerHTML =
                '<p>Chart of Accounts: <strong>' + r.coa_count + '</strong> accounts</p>'
                + '<p>Old Account Book: ' + (r.has_old_book ? '<strong>Yes</strong> (can migrate)' : 'None') + '</p>';
        });
    }

    function lockPeriod() {
        var d = document.getElementById('settLockDate').value;
        if (!d) return toast('Select a date.', 'error');
        if (!confirm('Lock all entries on or before ' + d + '? This cannot be undone.')) return;
        post('accounting/lock_period', { locked_until: d }).then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadSettings();
        });
    }

    function migrateAccounts() {
        if (!confirm('Migrate existing Account Book entries to Chart of Accounts?')) return;
        post('accounting/migrate_existing_accounts').then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadCoA(); loadSettings();
        });
    }

    function recomputeBalances() {
        post('accounting/recompute_balances').then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message);
        });
    }

    function carryForward() {
        if (!confirm('Carry forward closing balances as next year opening balances? This updates the Chart of Accounts.')) return;
        post('accounting/carry_forward_balances').then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            toast(r.message); loadCoA();
        });
    }

    // ══════════════════════════════════════════════
    //  AUDIT LOG
    // ══════════════════════════════════════════════
    function loadAuditLog() {
        getJSON('accounting/get_audit_log?limit=50').then(function(r){
            if (r.status !== 'success') return toast(r.message, 'error');
            var body = document.getElementById('auditBody');
            if (!(r.logs || []).length) { body.innerHTML = '<tr><td colspan="5" class="ac-empty">No audit entries yet.</td></tr>'; return; }
            var html = '';
            r.logs.forEach(function(log){
                var ts = log.timestamp ? new Date(log.timestamp).toLocaleString('en-IN') : '';
                html += '<tr>'
                    + '<td style="font-size:12px;white-space:nowrap;">' + esc(ts) + '</td>'
                    + '<td>' + esc(log.admin_name || log.admin_id) + '</td>'
                    + '<td><span class="ac-badge" style="background:var(--ac-bg3);color:var(--ac-text);">' + esc(log.action) + '</span></td>'
                    + '<td>' + esc(log.entity_type) + ' <code>' + esc(log.entity_id || '') + '</code></td>'
                    + '<td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;">' + esc(JSON.stringify(log.new_value || log.old_value || '').substring(0, 80)) + '</td>'
                    + '</tr>';
            });
            body.innerHTML = html;
        });
    }

    // ── Public API ──
    window.AC = {
        loadCoA: loadCoA, showAccountModal: showAccountModal, saveAccount: saveAccount,
        editAccount: editAccount, deleteAccount: deleteAccount, seedChart: seedChart,
        loadLedger: loadLedger, loadMoreLedger: loadMoreLedger,
        showJournalModal: showJournalModal, addJELine: addJELine,
        updateJETotals: updateJETotals, saveJournalEntry: saveJournalEntry,
        deleteJE: deleteJE, finalizeJE: finalizeJE,
        loadIE: loadIE, loadMoreIE: loadMoreIE,
        showIEModal: showIEModal, saveIE: saveIE, deleteIE: deleteIE,
        loadCashBook: loadCashBook, populateCashBookAccounts: populateCashBookAccounts,
        loadBankAccounts: loadBankAccounts, loadBankRecon: loadBankRecon,
        showImportCSV: showImportCSV, matchPrompt: matchPrompt, doMatch: doMatch,
        unmatchTxn: unmatchTxn,
        generateReport: generateReport, loadSettings: loadSettings,
        lockPeriod: lockPeriod, migrateAccounts: migrateAccounts,
        recomputeBalances: recomputeBalances, carryForward: carryForward,
        loadAuditLog: loadAuditLog, closeModal: closeModal,
    };

    // ── Apply role visibility & auto-load data for active tab ──
    applyRoleVisibility();
    var activeTab = '<?= $at ?>';
    loadCoA(); // always load CoA (needed for account dropdowns)
    if (activeTab === 'settings') { loadSettings(); loadAuditLog(); }
    else if (activeTab === 'bank-recon') { loadBankAccounts(); }
    else if (activeTab === 'cash-book') { populateCashBookAccounts(); }
})();
</script>
