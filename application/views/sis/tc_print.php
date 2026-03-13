<?php defined('BASEPATH') or exit('No direct script access allowed');

$s   = $student;
$tc  = $tc;
$sp  = $school_profile;

$schoolName  = $sp['school_name'] ?? $school_name ?? 'School';
$schoolAddr  = $sp['address'] ?? '';
$schoolPhone = $sp['phone'] ?? '';
$schoolLogo  = $sp['logo'] ?? '';

$studentName = $s['Name'] ?? '';
$fatherName  = $s['Father Name'] ?? '';
$motherName  = $s['Mother Name'] ?? '';
$dob         = $s['DOB'] ?? '';
$classOrd    = $s['Class'] ?? '';
$section     = $s['Section'] ?? '';
$rollNo      = $s['Roll No'] ?? '';
$admDate     = $s['Admission Date'] ?? '';
$rawAddr     = $s['Address'] ?? '';
$address     = is_array($rawAddr)
    ? implode(', ', array_filter([
        $rawAddr['Street'] ?? '',
        $rawAddr['City'] ?? '',
        $rawAddr['State'] ?? '',
        $rawAddr['PostalCode'] ?? '',
    ]))
    : (string) $rawAddr;
$gender      = $s['Gender'] ?? '';

$tcNo      = $tc['tc_no'] ?? '';
$issuedDate= $tc['issued_date'] ?? date('Y-m-d');
$issuedBy  = $tc['issued_by'] ?? '';
$reason    = $tc['reason'] ?? '';
$dest      = $tc['destination'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transfer Certificate — <?= htmlspecialchars($tcNo) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Times New Roman', Times, serif; background:#fff; color:#000; font-size:13pt; }

.tc-page { width:210mm; min-height:297mm; margin:0 auto; padding:18mm 16mm; }
@media print {
    .no-print { display:none !important; }
    .tc-page { padding:12mm 10mm; }
    @page { size:A4; margin:0; }
}

/* School Header */
.school-hdr { display:flex; align-items:center; gap:16px; border-bottom:3px double #000; padding-bottom:10px; margin-bottom:12px; }
.school-logo { width:70px; height:70px; object-fit:contain; }
.school-info { flex:1; text-align:center; }
.school-info h1 { font-size:20pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
.school-info p  { font-size:9.5pt; color:#333; margin-top:2px; }

/* TC Title */
.tc-title { text-align:center; margin:14px 0; }
.tc-title h2 { font-size:16pt; text-decoration:underline; text-transform:uppercase; letter-spacing:2px; }
.tc-no-row { display:flex; justify-content:space-between; margin-bottom:10px; font-size:10.5pt; }

/* Fields */
.tc-body { margin-top:8px; }
.tc-field { display:flex; margin-bottom:8px; font-size:11pt; align-items:baseline; gap:6px; }
.tc-field .label { width:220px; flex-shrink:0; font-weight:bold; }
.tc-field .colon { flex-shrink:0; }
.tc-field .val   { flex:1; border-bottom:1px solid #555; min-width:100px; padding-bottom:1px; }

/* Certificate Text */
.cert-text { margin-top:20px; font-size:11pt; line-height:1.8; text-align:justify; }

/* Signatures */
.sig-row { display:flex; justify-content:space-between; margin-top:50px; }
.sig-box { text-align:center; width:150px; }
.sig-box .sig-line { border-top:1px solid #000; padding-top:6px; margin-top:40px; font-size:10pt; font-weight:bold; }

/* Print button */
.no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:99; }
.no-print button { padding:8px 18px; border:none; border-radius:5px; cursor:pointer;
    font-size:13px; font-family:sans-serif; }
.no-print .btn-print { background:#0f766e; color:#fff; }
.no-print .btn-close  { background:#eee; color:#333; }
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()"><i>&#9113;</i> Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<div class="tc-page">

    <!-- School Header -->
    <div class="school-hdr">
        <?php if ($schoolLogo): ?>
        <img class="school-logo" src="<?= htmlspecialchars($schoolLogo) ?>" alt="Logo">
        <?php endif; ?>
        <div class="school-info">
            <h1><?= htmlspecialchars($schoolName) ?></h1>
            <?php if ($schoolAddr): ?><p><?= htmlspecialchars($schoolAddr) ?></p><?php endif; ?>
            <?php if ($schoolPhone): ?><p>Phone: <?= htmlspecialchars($schoolPhone) ?></p><?php endif; ?>
        </div>
        <?php if ($schoolLogo): ?>
        <img class="school-logo" src="<?= htmlspecialchars($schoolLogo) ?>" alt="Logo">
        <?php endif; ?>
    </div>

    <!-- Title -->
    <div class="tc-title">
        <h2>Transfer Certificate</h2>
    </div>

    <div class="tc-no-row">
        <span><strong>TC No:</strong> <?= htmlspecialchars($tcNo) ?></span>
        <span><strong>Date of Issue:</strong> <?= htmlspecialchars($issuedDate) ?></span>
    </div>

    <!-- Student Details -->
    <div class="tc-body">
        <div class="tc-field">
            <div class="label">Student's Full Name</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($studentName) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Father's Name</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($fatherName) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Mother's Name</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($motherName) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Date of Birth</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($dob) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Gender</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($gender) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Address</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($address) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Class / Section</div>
            <div class="colon">:</div>
            <div class="val">Class <?= htmlspecialchars($classOrd) ?> / Section <?= htmlspecialchars($section) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Roll Number</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($rollNo) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Admission Date</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($admDate) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Reason for Leaving</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($reason) ?></div>
        </div>
        <div class="tc-field">
            <div class="label">Admitted To / Destination</div>
            <div class="colon">:</div>
            <div class="val"><?= htmlspecialchars($dest) ?></div>
        </div>
    </div>

    <!-- Certificate Text -->
    <div class="cert-text">
        <p>This is to certify that <strong><?= htmlspecialchars($studentName) ?></strong>, son/daughter of
        <strong><?= htmlspecialchars($fatherName) ?></strong>, was a bonafide student of this institution.
        He/She has been duly transferred from our school and his/her conduct and character were
        <strong>Good</strong> during his/her stay in this institution.</p>

        <p style="margin-top:12px;">We wish him/her all success in future endeavours.</p>
    </div>

    <!-- Signatures -->
    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-line">Class Teacher</div>
        </div>
        <div class="sig-box">
            <div class="sig-line">Prepared By<br><small><?= htmlspecialchars($issuedBy) ?></small></div>
        </div>
        <div class="sig-box">
            <div class="sig-line">Principal / Head</div>
        </div>
    </div>

</div>

</body>


</html>
