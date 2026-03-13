<?php defined('BASEPATH') or exit('No direct script access allowed');

$schoolN    = $school_profile['school_name'] ?? $school_name ?? '';
$schoolAddr = $school_profile['address'] ?? '';
$schoolLogo = $school_profile['logo'] ?? base_url('tools/image/default-school.jpeg');
$fallback   = base_url('tools/image/default-school.jpeg');

function sis_photo($s) {
    global $fallback;
    if (!empty($s['Profile Pic']) && is_string($s['Profile Pic'])) return $s['Profile Pic'];
    if (!empty($s['Doc']['Photo'])) {
        $e = $s['Doc']['Photo'];
        return is_array($e) ? ($e['url'] ?? $fallback) : $e;
    }
    return $fallback;
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
html { font-size: 16px !important; }
.id-wrap { max-width:1200px; margin:0 auto; padding:24px 20px; }
.page-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.page-hdr h1 { margin:0; font-size:1.25rem; color:var(--t1); font-family:var(--font-b); }

.btn-print { padding:9px 18px; background:var(--gold); color:#fff; border:none;
    border-radius:6px; cursor:pointer; font-size:.88rem; }
.btn-print:hover { background:var(--gold2); }

.cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }

/* ID Card */
.id-card {
    width:280px; border-radius:12px; overflow:hidden;
    background:linear-gradient(135deg,#0f766e 60%,#14b8a6);
    box-shadow:0 4px 18px rgba(0,0,0,.18); font-family:sans-serif; page-break-inside:avoid;
}
.id-card-top { padding:14px 14px 10px; display:flex; align-items:center; gap:10px; }
.id-card-logo { width:42px; height:42px; border-radius:50%; object-fit:contain; background:#fff; padding:2px; }
.id-card-school { color:#fff; flex:1; }
.id-card-school h4 { margin:0; font-size:.82rem; font-weight:700; line-height:1.2; }
.id-card-school p  { margin:0; font-size:.68rem; opacity:.85; }

.id-card-body { background:#fff; margin:0 10px; border-radius:8px; padding:12px; display:flex; gap:12px; }
.id-card-photo { width:60px; height:70px; border-radius:5px; object-fit:cover;
    border:2px solid #0f766e; flex-shrink:0; }
.id-card-info { flex:1; overflow:hidden; }
.id-card-name { font-size:.88rem; font-weight:700; color:#1f2937; word-break:break-word; margin-bottom:2px; }
.id-card-detail { font-size:.72rem; color:#4b5563; line-height:1.7; }
.id-card-detail span { font-weight:600; color:#111827; }

.id-card-footer { padding:8px 14px; display:flex; align-items:center; justify-content:space-between; }
.id-card-id   { font-size:.72rem; color:#e0f2fe; }
.id-card-sess { font-size:.68rem; color:#ccfbf1; }
.id-card-qr   { width:36px; height:36px; flex-shrink:0; }
.id-card-qr canvas, .id-card-qr img { width:36px !important; height:36px !important; border-radius:3px; }

@media print {
    .no-print { display:none !important; }
    .cards-grid { grid-template-columns:repeat(3,280px); gap:14px; }
}
</style>

<div class="content-wrapper">
<div class="id-wrap">

    <div class="page-hdr">
        <h1><i class="fa fa-id-card-o" style="color:var(--gold);margin-right:8px;"></i>Student ID Cards</h1>
        <button class="btn-print no-print" onclick="window.print()"><i class="fa fa-print"></i> Print All</button>
    </div>

    <?php if (empty($students)): ?>
    <div style="text-align:center;padding:60px;color:var(--t3);background:var(--bg2);border:1px solid var(--border);border-radius:10px;">
        <i class="fa fa-id-card-o" style="font-size:3rem;display:block;margin-bottom:10px;"></i>
        No enrolled students found for <?= htmlspecialchars($session_year) ?>.
    </div>
    <?php else: ?>
    <div class="cards-grid">
        <?php foreach ($students as $s):
            $photo = sis_photo($s);
        ?>
        <div class="id-card">
            <div class="id-card-top">
                <img class="id-card-logo"
                    src="<?= htmlspecialchars($schoolLogo) ?>"
                    onerror="this.src='<?= $fallback ?>'"
                    alt="Logo">
                <div class="id-card-school">
                    <h4><?= htmlspecialchars($schoolN) ?></h4>
                    <?php if ($schoolAddr): ?>
                    <p><?= htmlspecialchars(mb_strimwidth($schoolAddr, 0, 50, '...')) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="id-card-body">
                <img class="id-card-photo"
                    src="<?= htmlspecialchars($photo) ?>"
                    onerror="this.src='<?= $fallback ?>'"
                    alt="Photo">
                <div class="id-card-info">
                    <div class="id-card-name"><?= htmlspecialchars($s['Name'] ?? '') ?></div>
                    <div class="id-card-detail">
                        <span>Father:</span> <?= htmlspecialchars(mb_strimwidth($s['Father Name'] ?? '', 0, 22, '...')) ?><br>
                        <span>Class:</span> <?= htmlspecialchars($s['Class'] ?? '') ?> &bull; <span>Sec:</span> <?= htmlspecialchars($s['Section'] ?? '') ?><br>
                        <?php if (!empty($s['Roll No'])): ?>
                        <span>Roll:</span> <?= htmlspecialchars($s['Roll No']) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($s['DOB'])): ?>
                        <span>DOB:</span> <?= htmlspecialchars($s['DOB']) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($s['Phone'])): ?>
                        <span>Ph:</span> <?= htmlspecialchars($s['Phone']) ?><br>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="id-card-footer">
                <div>
                    <div class="id-card-id">ID: <?= htmlspecialchars($s['User Id'] ?? '') ?></div>
                    <div class="id-card-sess"><?= htmlspecialchars($session_year) ?></div>
                </div>
                <div class="id-card-qr" id="qr-<?= htmlspecialchars($s['User Id'] ?? '') ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
// Generate QR codes for each student ID card
<?php foreach ($students as $s): $uid = $s['User Id'] ?? ''; if (empty($uid)) continue; ?>
(function() {
    const el = document.getElementById('qr-<?= htmlspecialchars($uid, ENT_QUOTES) ?>');
    if (el && typeof QRCode !== 'undefined') {
        new QRCode(el, {
            text: '<?= htmlspecialchars($uid, ENT_QUOTES) ?>',
            width: 36, height: 36,
            colorDark: '#0f766e', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
    }
})();
<?php endforeach; ?>
</script>
