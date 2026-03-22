<?php
defined('BASEPATH') or exit('No direct script access allowed');
$esc     = function($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); };
$enabled = !empty($config['enabled']);
$amount  = (float) ($config['amount'] ?? 0);
$currency= $config['currency'] ?? 'INR';
$label   = $config['label'] ?? 'Admission Fee';
?>

<style>
.apc-wrap { max-width:720px; margin:0 auto; padding:24px 20px; }
.apc-hdr { margin-bottom:24px; }
.apc-hdr h1 { font-size:1.2rem; color:var(--t1); font-family:var(--font-b); margin:0 0 4px; }
.apc-hdr p { font-size:13px; color:var(--t3); }

.apc-card {
    background:var(--bg2); border:1px solid var(--border); border-radius:10px;
    padding:24px; margin-bottom:20px;
}
.apc-card h2 {
    font-size:14px; font-weight:700; color:var(--t1); margin-bottom:16px;
    padding-bottom:10px; border-bottom:2px solid var(--gold);
    display:flex; align-items:center; gap:8px;
}
.apc-card h2 i { color:var(--gold); }

.apc-row { display:flex; align-items:center; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
.apc-field { display:flex; flex-direction:column; gap:4px; flex:1; min-width:200px; }
.apc-field label {
    font-size:11px; font-weight:600; color:var(--t3); text-transform:uppercase; letter-spacing:.3px;
}
.apc-field input, .apc-field select {
    padding:10px 12px; border:1px solid var(--border); border-radius:8px;
    background:#fff; color:var(--t1); font-size:14px; font-family:var(--font-b);
    outline:none; transition:border-color .2s;
}
.apc-field input:focus, .apc-field select:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(15,118,110,.15); }
.apc-field input:disabled { background:var(--bg3); color:var(--t3); }

/* Toggle switch */
.apc-toggle-row { display:flex; align-items:center; gap:14px; margin-bottom:20px; }
.apc-toggle {
    position:relative; width:48px; height:26px; cursor:pointer;
}
.apc-toggle input { display:none; }
.apc-toggle-track {
    position:absolute; top:0; left:0; width:100%; height:100%;
    background:var(--border); border-radius:13px; transition:background .25s;
}
.apc-toggle input:checked + .apc-toggle-track { background:var(--gold); }
.apc-toggle-thumb {
    position:absolute; top:3px; left:3px; width:20px; height:20px;
    background:#fff; border-radius:50%; transition:left .25s; box-shadow:0 1px 4px rgba(0,0,0,.2);
}
.apc-toggle input:checked ~ .apc-toggle-thumb { left:25px; }
.apc-toggle-label { font-size:14px; font-weight:600; color:var(--t1); }
.apc-toggle-sub { font-size:12px; color:var(--t3); }

.apc-btn {
    padding:10px 24px; background:var(--gold); color:#fff; border:none; border-radius:8px;
    font-size:13px; font-weight:700; cursor:pointer; font-family:var(--font-b);
    transition:background .2s; display:inline-flex; align-items:center; gap:6px;
}
.apc-btn:hover { background:var(--gold2); }
.apc-btn:disabled { opacity:.6; cursor:not-allowed; }

.apc-status {
    display:none; padding:10px 16px; border-radius:8px; font-size:13px; margin-top:16px;
}
.apc-status.ok { display:block; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.apc-status.err { display:block; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

/* Public link card */
.apc-link-card {
    background:var(--bg3); border:1px solid var(--border); border-radius:8px;
    padding:14px 18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;
}
.apc-link-card code {
    font-size:12px; color:var(--gold); background:var(--bg2); padding:6px 12px;
    border-radius:6px; border:1px solid var(--border); flex:1; min-width:200px;
    word-break:break-all;
}
.apc-copy-btn {
    padding:6px 14px; background:var(--gold-dim); color:var(--gold); border:1px solid var(--border);
    border-radius:6px; cursor:pointer; font-size:12px; font-weight:600;
}
.apc-copy-btn:hover { background:var(--gold); color:#fff; }

.apc-preview {
    margin-top:12px; padding:12px 16px; background:var(--bg3); border-radius:8px;
    border:1px dashed var(--border); font-size:13px; color:var(--t2);
}
.apc-preview .amt { font-size:1.3rem; font-weight:800; color:var(--gold); }
</style>

<div class="apc-wrap">
    <div class="apc-hdr">
        <h1><i class="fa fa-credit-card" style="color:var(--gold);margin-right:8px;"></i>Admission Payment Settings</h1>
        <p>Configure registration/admission fee for the public admission form.</p>
    </div>

    <!-- Payment Settings Card -->
    <div class="apc-card">
        <h2><i class="fa fa-cog"></i> Payment Configuration</h2>

        <div class="apc-toggle-row">
            <label class="apc-toggle">
                <input type="checkbox" id="payEnabled" <?= $enabled ? 'checked' : '' ?>>
                <div class="apc-toggle-track"></div>
                <div class="apc-toggle-thumb"></div>
            </label>
            <div>
                <div class="apc-toggle-label" id="toggleLabel"><?= $enabled ? 'Payment Enabled' : 'Payment Disabled' ?></div>
                <div class="apc-toggle-sub">When enabled, parents see a "Pay Now" button after submitting the admission form.</div>
            </div>
        </div>

        <div id="feeFields" style="<?= $enabled ? '' : 'opacity:.5;pointer-events:none;' ?>">
            <div class="apc-row">
                <div class="apc-field">
                    <label>Fee Label</label>
                    <input type="text" id="feeLabel" value="<?= $esc($label) ?>" maxlength="100" placeholder="e.g. Admission Fee">
                </div>
            </div>

            <div class="apc-row">
                <div class="apc-field">
                    <label>Amount</label>
                    <input type="number" id="feeAmount" value="<?= $amount ?>" min="0" max="500000" step="1" placeholder="e.g. 500">
                </div>
                <div class="apc-field" style="max-width:160px;">
                    <label>Currency</label>
                    <select id="feeCurrency">
                        <option value="INR" <?= $currency==='INR'?'selected':'' ?>>INR (&#8377;)</option>
                        <option value="USD" <?= $currency==='USD'?'selected':'' ?>>USD ($)</option>
                        <option value="GBP" <?= $currency==='GBP'?'selected':'' ?>>GBP (&pound;)</option>
                        <option value="EUR" <?= $currency==='EUR'?'selected':'' ?>>EUR (&euro;)</option>
                    </select>
                </div>
            </div>

            <div class="apc-preview" id="preview">
                Parents will see: <span class="amt" id="previewAmt"><?= $currency === 'INR' ? '&#8377;' : $currency . ' ' ?><?= number_format($amount, 0) ?></span>
                <span id="previewLabel"><?= $esc($label) ?></span>
            </div>
        </div>

        <div style="margin-top:20px;">
            <button class="apc-btn" id="saveBtn" onclick="saveConfig()">
                <i class="fa fa-save"></i> Save Settings
            </button>
        </div>

        <div class="apc-status" id="statusMsg"></div>
    </div>

    <!-- Public Form Link -->
    <div class="apc-card">
        <h2><i class="fa fa-link"></i> Public Admission Form Link</h2>
        <p style="font-size:13px;color:var(--t3);margin-bottom:12px;">Share this link with parents. They can apply and pay directly.</p>
        <div class="apc-link-card">
            <code id="publicLink"><?= $esc($public_form_url) ?></code>
            <button class="apc-copy-btn" onclick="copyLink()"><i class="fa fa-copy"></i> Copy</button>
        </div>
    </div>
</div>

<script>
var csrfName  = document.querySelector('meta[name="csrf-name"]').content;
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

var toggle    = document.getElementById('payEnabled');
var fields    = document.getElementById('feeFields');
var lbl       = document.getElementById('toggleLabel');
var amtInput  = document.getElementById('feeAmount');
var curSelect = document.getElementById('feeCurrency');
var lblInput  = document.getElementById('feeLabel');

var symbols = { INR:'\u20B9', USD:'$', GBP:'\u00A3', EUR:'\u20AC' };

toggle.addEventListener('change', function() {
    var on = this.checked;
    fields.style.opacity = on ? '1' : '.5';
    fields.style.pointerEvents = on ? 'auto' : 'none';
    lbl.textContent = on ? 'Payment Enabled' : 'Payment Disabled';
});

function updatePreview() {
    var sym = symbols[curSelect.value] || curSelect.value + ' ';
    var amt = parseFloat(amtInput.value) || 0;
    document.getElementById('previewAmt').textContent = sym + amt.toLocaleString('en-IN');
    document.getElementById('previewLabel').textContent = lblInput.value || 'Admission Fee';
}
amtInput.addEventListener('input', updatePreview);
curSelect.addEventListener('change', updatePreview);
lblInput.addEventListener('input', updatePreview);

function showStatus(msg, ok) {
    var el = document.getElementById('statusMsg');
    el.textContent = msg;
    el.className = 'apc-status ' + (ok ? 'ok' : 'err');
    setTimeout(function() { el.className = 'apc-status'; }, 5000);
}

function saveConfig() {
    var btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    var fd = new URLSearchParams({
        enabled:  toggle.checked ? 'true' : 'false',
        amount:   amtInput.value,
        currency: curSelect.value,
        label:    lblInput.value,
    });
    fd.append(csrfName, csrfToken);

    fetch('<?= base_url("school_config/save_admission_payment_config") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd.toString()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            showStatus(data.message || 'Saved!', true);
        } else {
            showStatus(data.message || 'Save failed.', false);
        }
    })
    .catch(function() { showStatus('Network error. Please try again.', false); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save"></i> Save Settings';
    });
}

function copyLink() {
    var link = document.getElementById('publicLink').textContent;
    navigator.clipboard.writeText(link).then(function() {
        var btn = event.target.closest('button');
        btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
        setTimeout(function() { btn.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 2000);
    });
}
</script>
