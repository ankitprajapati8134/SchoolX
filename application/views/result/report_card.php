<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Report Card — ~70% styled like Mount Litera Zee School official marksheet.
 * Standalone HTML — no CI header/footer chrome.
 */

$computed  = $computed ?? [];
$templates = $templates ?? [];
$marks     = $marks ?? [];

// ── Student info ──────────────────────────────────────────────────────
$studentName = $profile['Name']        ?? 'Unknown';
$fatherName  = $profile['Father Name'] ?? '';
$motherName  = $profile['Mother Name'] ?? '';
$dob         = $profile['DOB']         ?? '';
$gender      = $profile['Gender']      ?? '';
$rollNo      = $profile['User Id']     ?? '';

$addrObj = $profile['Address'] ?? [];

if (is_object($addrObj)) {
  $addrObj = (array)$addrObj;
}

$street = $addrObj['Street'] ?? '';
$city   = $addrObj['City'] ?? '';
$state  = $addrObj['State'] ?? '';
$postal = $addrObj['PostalCode'] ?? '';

$addressParts = [];

if ($street) $addressParts[] = $street;
if ($city)   $addressParts[] = $city;
if ($state)  $addressParts[] = $state;
if ($postal) $addressParts[] = $postal;

$address = implode(', ', $addressParts);


$photoUrl    = $profile['Profile Pic'] ?? '';

// ── Class info ────────────────────────────────────────────────────────
$classNameRaw  = ltrim(trim(str_ireplace('Class', '', $classKey)));   // "9th"
$sectionLetter = str_replace('Section ', '', $sectionKey);            // "A"
$gradeLabel    = $classNameRaw . ($sectionLetter ? ' - ' . $sectionLetter : '');

// ── Exam info ─────────────────────────────────────────────────────────
$examName     = $exam['Name']              ?? 'Exam';
$examType     = $exam['Type']              ?? '';
$startDate    = $exam['StartDate']         ?? '';
$endDate      = $exam['EndDate']           ?? '';
$gradingScale = $exam['GradingScale']      ?? 'Percentage';
$passingPct   = (int)($exam['PassingPercent'] ?? 33);

// ── School info ───────────────────────────────────────────────────────
$schoolDisplayName = $schoolInfo['Name']    ?? $schoolName;
$schoolCity        = $schoolInfo['City']    ?? '';
$schoolAddress     = $schoolInfo['Address'] ?? '';
$schoolAffNo       = $schoolInfo['AffNo']   ?? $schoolInfo['affiliation_no'] ?? '';
$schoolBoard       = $schoolInfo['Board']   ?? '';
$schoolCode        = $schoolInfo['Code']    ?? '';
$schoolLogoUrl     = $schoolInfo['Logo']    ?? '';

// ── Build subject rows ────────────────────────────────────────────────
$subjectRows = [];
$allCompDefs = [];

if (!empty($computed['Subjects'])) {
  foreach ($computed['Subjects'] as $subj => $sd) {
    $tmpl     = $templates[$subj] ?? [];
    $comps    = $tmpl['Components'] ?? [];
    ksort($comps);
    $stuMarks = $marks[$subj] ?? [];

    $row = [
      'subject'  => $subj,
      'comps'    => [],
      'total'    => $sd['Total']      ?? 0,
      'maxMarks' => $sd['MaxMarks']   ?? 0,
      'pct'      => $sd['Percentage'] ?? 0,
      'grade'    => $sd['Grade']      ?? '',
      'passFail' => $sd['PassFail']   ?? '',
      'absent'   => $sd['Absent']     ?? false,
    ];

    foreach ($comps as $ci => $comp) {
      $cn = $comp['Name'];
      $mx = (int)$comp['MaxMarks'];
      $row['comps'][$cn] = $stuMarks[$cn] ?? ($sd['Absent'] ? 0 : '—');
      $allCompDefs[$cn]  = $mx;
    }
    $subjectRows[] = $row;
  }
}

// ── Grand totals ──────────────────────────────────────────────────────
$grandTotal = $computed['TotalMarks']  ?? 0;
$grandMax   = $computed['MaxMarks']    ?? 0;
$grandPct   = $computed['Percentage']  ?? 0;
$grandGrade = $computed['Grade']       ?? '';
$grandPass  = $computed['PassFail']    ?? '';
$rank       = $computed['Rank']        ?? '';

// ── Grade legend ──────────────────────────────────────────────────────
$scaleLegendMap = [
  'Percentage' => 'A1=(91-100), A2=(81-90), B1=(71-80), B2=(61-70), C1=(51-60), C2=(41-50), D=(33-40), E=(32 &amp; Below - Needs Improvement)',
  'A-F Grades' => 'A=(90-100), B=(80-89), C=(70-79), D=(60-69), E=(50-59), F=(&lt;50)',
  'O-E Grades' => 'O=(91-100), E1=(81-90), E2=(71-80), B1=(61-70), B2=(51-60), C1=(41-50), C2=(33-40), D=(&lt;33)',
  '10-Point'   => '10=(91-100), 9=(81-90), 8=(71-80), 7=(61-70), 6=(51-60), 5=(41-50), 4=(33-40), F=(&lt;33)',
  'Pass/Fail'  => 'Pass=(&ge;' . $passingPct . '%), Fail=(&lt;' . $passingPct . '%)',
];
$gradeLegend = $scaleLegendMap[$gradingScale] ?? '';

// ── Promotion text ────────────────────────────────────────────────────
$nextClass = '';
if (preg_match('/\d+/', $classNameRaw, $m)) {
  $nextNum   = (int)$m[0] + 1;
  $nextClass = ' TO GRADE ' . $nextNum;
}
$resultText = ($grandPass === 'Pass')
  ? 'RESULT : PROMOTED' . $nextClass
  : 'RESULT : NOT PROMOTED — FURTHER IMPROVEMENT NEEDED';
?>
<!-- ══ Screen toolbar ══════════════════════════════════════════════════ -->
<div class="rc-toolbar">
  <a href="javascript:history.back()" class="rc-btn-back">&#8592; Back</a>
  <button class="rc-btn-print" onclick="window.print()">&#128438; Print Report Card</button>
</div>

<div class="rc-wrapper">
  <div class="rc-page">

    <!-- ════════════════════════════════════════════════
         SCHOOL HEADER
    ═════════════════════════════════════════════════ -->
    <div class="rc-head">
      <div class="rc-head-inner">

        <!-- Left logo -->
        <div class="rc-logo">
          <?php if ($schoolLogoUrl): ?>
            <img src="<?= htmlspecialchars($schoolLogoUrl) ?>" alt="logo">
          <?php else: ?>
            <div class="rc-logo-ph"><?= htmlspecialchars(strtoupper(substr($schoolDisplayName, 0, 4))) ?></div>
          <?php endif; ?>
        </div>

        <!-- Center: name + city + address -->
        <div class="rc-head-center">
          <div class="rc-school-name"><?= htmlspecialchars(strtoupper($schoolDisplayName)) ?></div>
          <?php if ($schoolCity): ?>
            <div class="rc-city">&mdash; <?= htmlspecialchars(strtoupper($schoolCity)) ?> &mdash;</div>
          <?php endif; ?>
          <?php if ($schoolAddress || $schoolAffNo): ?>
            <div class="rc-school-addr">
              <?= htmlspecialchars($schoolAddress) ?><?= $schoolAffNo ? ',&nbsp; Aff. No&nbsp;: ' . htmlspecialchars($schoolAffNo) : '' ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right logo -->
        <div class="rc-logo">
          <?php if ($schoolLogoUrl): ?>
            <img src="<?= htmlspecialchars($schoolLogoUrl) ?>" alt="logo">
          <?php else: ?>
            <div class="rc-logo-ph"><?= htmlspecialchars(substr($schoolDisplayName, 0, 4)) ?></div>
          <?php endif; ?>
        </div>

      </div><!-- /.rc-head-inner -->

      <!-- Board / school-code strip -->
      <div class="rc-head-strip">
        <span><?= htmlspecialchars($schoolBoard) ?></span>
        <span><?= htmlspecialchars($schoolCode) ?></span>
      </div>
    </div><!-- /.rc-head -->


    <!-- ════════════════════════════════════════════════
         TITLE
    ═════════════════════════════════════════════════ -->
    <div class="rc-title-area">
      <div class="rc-title-box">
        <?= htmlspecialchars(strtoupper($examName)) ?><?= $examType ? ' &mdash; ' . htmlspecialchars(strtoupper($examType)) : '' ?> — REPORT CARD
      </div>
      <div class="rc-grade-sub">
        Class - <?= htmlspecialchars(strtoupper($classNameRaw)) ?>
        <?= $sectionLetter ? ' &nbsp;&nbsp;|&nbsp;&nbsp; SECTION : ' . htmlspecialchars(strtoupper($sectionLetter)) : '' ?>
      </div>
    </div>


    <!-- ════════════════════════════════════════════════
         STUDENT INFO
    ═════════════════════════════════════════════════ -->
    <div class="rc-stu-block">

      <!-- Photo -->
      <div class="rc-photo-cell">
        <?php if ($photoUrl): ?>
          <img src="<?= htmlspecialchars($photoUrl) ?>" alt="photo" class="rc-stu-photo">
        <?php else: ?>
          <div class="rc-photo-ph">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="#c0c0c0">
              <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
            </svg>
            <span>Photo</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info table -->
      <table class="rc-info-tbl">

        <tr>
          <td class="il">Name of Student</td>
          <td class="isep">:</td>
          <td class="iv"><strong><?= htmlspecialchars(strtoupper($studentName ?: '—')) ?></strong></td>

          <td class="igap"></td>

          <td class="il rl">Session</td>
          <td class="isep">:</td>
          <td class="iv"><?= htmlspecialchars($sessionYear ?: '—') ?></td>
        </tr>

        <tr>
          <td class="il">Father's Name</td>
          <td class="isep">:</td>
          <td class="iv"><?= htmlspecialchars(strtoupper($fatherName ?: '—')) ?></td>

          <td class="igap"></td>

          <td class="il rl">SR No</td>
          <td class="isep">:</td>
          <td class="iv"><?= htmlspecialchars($rollNo ?: '—') ?></td>
        </tr>

        <tr>
          <td class="il">Mother's Name</td>
          <td class="isep">:</td>
          <td class="iv"><?= htmlspecialchars(strtoupper($motherName ?: '—')) ?></td>

          <td class="igap"></td>

          <td class="il rl">DOB</td>
          <td class="isep">:</td>
          <td class="iv"><?= htmlspecialchars($dob ?: '—') ?></td>
        </tr>

        <tr>
          <td class="il" style="vertical-align:top">Address</td>
          <td class="isep" style="vertical-align:top">:</td>

          <td class="iv" colspan="2">
            <?= htmlspecialchars(strtoupper($address ?: '—')) ?>
          </td>
        </tr>



      </table>

    </div><!-- /.rc-stu-block -->


    <!-- ════════════════════════════════════════════════
         PART A — SCHOLASTIC AREA
    ═════════════════════════════════════════════════ -->
    <div class="rc-part-head">PART A : SCHOLASTIC AREA</div>

    <div class="rc-marks-wrap">
      <?php if (empty($subjectRows)): ?>
        <p class="rc-no-data">No result data found. Please enter marks and compute results first.</p>
      <?php else:
        $compKeys = array_keys($allCompDefs);
        $numComps = count($compKeys);
      ?>
        <table class="rc-tbl">
          <thead>
            <!-- Row 1 — exam group span -->
            <tr class="rth-grp">
              <th class="rth-subj" rowspan="3">SUBJECT</th>
              <?php if ($numComps > 0): ?>
                <th class="rth-exam" colspan="<?= $numComps ?>">
                  <?= htmlspecialchars(strtoupper($examName)) ?>
                  (<?= htmlspecialchars($grandMax) ?> Marks)
                  <?php if ($startDate): ?>
                    &nbsp;&mdash;&nbsp;<?= htmlspecialchars($startDate) ?><?= $endDate ? ' to ' . htmlspecialchars($endDate) : '' ?>
                  <?php endif; ?>
                </th>
              <?php endif; ?>
              <th class="rth-col">Marks<br>Obtained</th>
              <th class="rth-col">GRADE</th>
              <th class="rth-col">Pass /<br>Fail</th>
            </tr>
            <!-- Row 2 — component names -->
            <tr class="rth-names">
              <?php foreach ($compKeys as $cn): ?>
                <th><?= htmlspecialchars($cn) ?></th>
              <?php endforeach; ?>
              <th>(<?= htmlspecialchars($grandMax) ?>)</th>
              <th>(<?= htmlspecialchars($gradingScale) ?>)</th>
              <th>(&ge;<?= $passingPct ?>%)</th>
            </tr>
            <!-- Row 3 — max marks per component -->
            <tr class="rth-max">
              <?php foreach ($compKeys as $cn): ?>
                <th>(<?= $allCompDefs[$cn] ?>)</th>
              <?php endforeach; ?>
              <th>(100%)</th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subjectRows as $idx => $row): ?>
              <tr class="<?= $row['absent'] ? 'row-absent' : '' ?> <?= ($idx % 2 === 1) ? 'row-alt' : '' ?>">
                <td class="td-subj"><?= htmlspecialchars($row['subject']) ?></td>
                <?php foreach ($compKeys as $cn): ?>
                  <td class="td-num">
                    <?= $row['absent'] ? '<em class="ab">AB</em>' : htmlspecialchars($row['comps'][$cn] ?? '—') ?>
                  </td>
                <?php endforeach; ?>
                <td class="td-tot">
                  <?= htmlspecialchars($row['total']) ?><span class="sub-max">/<?= htmlspecialchars($row['maxMarks']) ?></span>
                </td>
                <td class="td-grade"><?= htmlspecialchars($row['grade'] ?: ($row['absent'] ? 'AB' : '—')) ?></td>
                <td class="td-pf pf-<?= strtolower($row['passFail']) ?>">
                  <?= htmlspecialchars($row['absent'] ? 'Absent' : ($row['passFail'] ?: '—')) ?>
                </td>
              </tr>
            <?php endforeach; ?>

            <!-- In Number / In % row -->
            <tr class="row-subtotal">
              <td class="td-subj" style="text-align:right;padding-right:8px;font-style:italic;color:#333;">
                In Number
              </td>
              <?php for ($i = 0; $i < $numComps; $i++): ?><td></td><?php endfor; ?>
              <td class="td-tot"><?= htmlspecialchars($grandTotal) ?> / <?= htmlspecialchars($grandMax) ?></td>
              <td colspan="2" style="padding-left:7px;font-size:11px;">
                In % = <strong><?= htmlspecialchars($grandPct) ?>%</strong>
              </td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>
    </div><!-- /.rc-marks-wrap -->

    <!-- Grade legend note -->
    <?php if ($gradeLegend): ?>
      <div class="rc-note">
        <strong>Note :</strong> <?= $gradeLegend ?>
      </div>
    <?php endif; ?>


    <!-- ════════════════════════════════════════════════
         OVERALL MARKS (salmon / peach background)
    ═════════════════════════════════════════════════ -->
    <div class="rc-overall">
      <div class="ov-cell ov-wide">
        <div class="ov-lbl">OVERALL MARKS</div>
        <div class="ov-val"><?= htmlspecialchars($grandTotal) ?> / <?= htmlspecialchars($grandMax) ?></div>
      </div>
      <div class="ov-cell ov-wide">
        <div class="ov-lbl">PERCENTAGE</div>
        <div class="ov-val"><?= htmlspecialchars($grandPct) ?> %</div>
      </div>
      <div class="ov-cell">
        <div class="ov-lbl">GRADE</div>
        <div class="ov-val"><?= htmlspecialchars($grandGrade ?: '—') ?></div>
      </div>
      <div class="ov-cell">
        <div class="ov-lbl">RANK</div>
        <div class="ov-val"><?= htmlspecialchars($rank ?: '—') ?></div>
      </div>
      <div class="ov-cell">
        <div class="ov-lbl">RESULT</div>
        <div class="ov-val pf-<?= strtolower($grandPass) ?>"><?= htmlspecialchars($grandPass ?: '—') ?></div>
      </div>
    </div>


    <!-- ════════════════════════════════════════════════
         RESULT STATEMENT
    ═════════════════════════════════════════════════ -->
    <div class="rc-result <?= $grandPass === 'Pass' ? 'rc-promoted' : 'rc-failed' ?>">
      <?= htmlspecialchars($resultText) ?>
    </div>


    <!-- ════════════════════════════════════════════════
         SIGNATURES
    ═════════════════════════════════════════════════ -->
    <div class="rc-sigs">
      <div class="sig-cell">
        <div class="sig-space"></div>
        <div class="sig-line"></div>
        <div class="sig-lbl">Class Teacher Signature</div>
      </div>
      <div class="sig-cell">
        <div class="sig-space"></div>
        <div class="sig-seal">SCHOOL<br>SEAL</div>
        <div class="sig-line"></div>
        <div class="sig-lbl">Principal</div>
      </div>
      <div class="sig-cell">
        <div class="sig-space"></div>
        <div class="sig-line"></div>
        <div class="sig-lbl">Principal Signature</div>
      </div>
    </div>

  </div><!-- /.rc-page -->
</div><!-- /.rc-wrapper -->



<style>
  /* ══════════════════════════════════════════════════
   BASE RESET
══════════════════════════════════════════════════ */
  *,
  *::before,
  *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #000;
    background: #cbcbcb;
  }

  /* ══════════════════════════════════════════════════
   SCREEN TOOLBAR
══════════════════════════════════════════════════ */
  .rc-toolbar {
    max-width: 830px;
    margin: 14px auto 10px;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 0 4px;
  }

  .rc-btn-back,
  .rc-btn-print {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 22px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
  }

  .rc-btn-back {
    background: #fff;
    color: #1e7a6e;
    border: 1.5px solid #1e7a6e !important;
  }

  .rc-btn-back:hover {
    background: #f0faf9;
  }

  .rc-btn-print {
    background: #1e7a6e;
    color: #fff;
  }

  .rc-btn-print:hover {
    background: #155e54;
  }

  /* ══════════════════════════════════════════════════
   PAGE SHELL
══════════════════════════════════════════════════ */
  .rc-wrapper {
    padding: 0 0 36px;
  }

  .rc-page {
    background: #fff;
    max-width: 830px;
    margin: 0 auto;
    border: 2.5px solid #000;
    font-size: 11.5px;
  }

  /* ══════════════════════════════════════════════════
   HEADER
══════════════════════════════════════════════════ */
  .rc-head {
    border-bottom: 1.5px solid #000;
    padding: 10px 16px 0;
  }

  .rc-head-inner {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  /* Logo circles */
  .rc-logo {
    width: 72px;
    height: 72px;
    border: 2.5px solid #1e7a6e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    background: #f0faf9;
  }

  .rc-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .rc-logo-ph {
    font-size: 9px;
    font-weight: 700;
    color: #1e7a6e;
    text-align: center;
    line-height: 1.3;
    padding: 6px;
  }

  /* Center text block */
  .rc-head-center {
    flex: 1;
    text-align: center;
  }

  .rc-school-name {
    font-size: 32px;
    font-weight: 900;
    letter-spacing: 1px;
    color: #000;
    line-height: 1.1;
    font-family: Arial, sans-serif;
  }

  .rc-city {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 5px;
    color: #333;
    margin: 3px 0 1px;
  }

  .rc-school-addr {
    font-size: 10px;
    color: #555;
    margin-top: 2px;
  }

  /* Board / code strip */
  .rc-head-strip {
    display: flex;
    justify-content: space-between;
    margin-top: 7px;
    padding: 4px 0 5px;
    border-top: 1px solid #ccc;
    font-size: 10px;
    color: #333;
    font-style: italic;
  }

  /* ══════════════════════════════════════════════════
   TITLE
══════════════════════════════════════════════════ */
  .rc-title-area {
    text-align: center;
    padding: 8px 10px 6px;
    border-bottom: 1.5px solid #000;
  }

  .rc-title-box {
    display: inline-block;
    border: 2px solid #000;
    padding: 3px 28px;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .8px;
    text-transform: uppercase;
  }

  .rc-grade-sub {
    font-size: 12px;
    font-weight: 700;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: .3px;
  }

  /* ══════════════════════════════════════════════════
   STUDENT INFO BLOCK
══════════════════════════════════════════════════ */
  .rc-stu-block {
    display: flex;
    align-items: stretch;
    border-bottom: 1.5px solid #000;
  }

  /* Photo cell */


  .rc-photo-cell {
    width: 110px;
    min-height: 120px;
    border-right: 1px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f6f6f6;
    overflow: hidden;
  }

  .rc-stu-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .rc-photo-ph {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #bbb;
    font-size: 9.5px;
    text-align: center;
    padding: 8px;
  }

  /* Info table */
  .rc-info-tbl {
    flex: 1;
    border-collapse: collapse;
    width: 100%;
  }

  .rc-info-tbl tr:first-child td {
    padding-top: 10px;
  }

  .rc-info-tbl tr:last-child td {
    padding-bottom: 10px;
  }

  .rc-info-tbl td {
    padding: 3.5px 4px;
    vertical-align: middle;
    font-size: 11px;
    color: #000;
  }

  /* label / sep / value / gap / right-label */
  .il {
    font-weight: 700;
    min-width: 110px;
    padding-left: 12px;
    white-space: nowrap;
  }

  .isep {
    font-weight: 700;
    padding: 3px 5px;
    color: #000;
    white-space: nowrap;
  }

  .iv {
    min-width: 110px;
    padding-right: 6px;
  }

  .igap {
    width: 14px;
    border-left: 1px solid #ddd;
  }

  .rl {
    padding-left: 12px;
  }

  /* ══════════════════════════════════════════════════
   PART A HEADER
══════════════════════════════════════════════════ */
  .rc-part-head {
    background: #000;
    color: #fff;
    font-weight: 900;
    font-size: 11px;
    padding: 4px 10px;
    letter-spacing: .5px;
    text-transform: uppercase;
  }

 /* ══════════════════════════════════════════════════
   MARKS TABLE — PROFESSIONAL ERP STYLE
══════════════════════════════════════════════════ */

.rc-marks-wrap{
overflow-x:auto;
}

.rc-tbl{
width:100%;
border-collapse:collapse;
table-layout:fixed;
font-size:11px;
}

/* column widths */

.rc-tbl th.rth-subj,
.rc-tbl td.td-subj{
width:160px;
min-width:160px;
text-align:left;
padding-left:10px;
font-weight:700;
}

.rc-tbl th.rth-col{
width:85px;
}

.rc-tbl td{
text-align:center;
padding:6px 4px;
border:1px solid #cfcfcf;
}

/* HEADER ROW 1 */

.rc-tbl thead tr.rth-grp th{
background:#14532d;
color:#fff;
font-weight:700;
font-size:11px;
border:1px solid #12422a;
padding:6px 4px;
}

/* SUBJECT HEADER */

.rc-tbl thead tr.rth-grp th.rth-subj{
background:#0b3d24;
}

/* HEADER ROW 2 */

.rc-tbl thead tr.rth-names th{
background:#2f855a;
color:#fff;
font-size:11px;
font-weight:600;
border:1px solid #1f6640;
}

/* HEADER ROW 3 */

.rc-tbl thead tr.rth-max th{
background:#e6f4ea;
font-size:10px;
font-style:italic;
color:#333;
}

/* SUBJECT CELL */

.td-subj{
text-align:left;
font-weight:700;
padding-left:10px;
background:#fff;
}

/* MARKS */

.td-num{
font-size:11px;
}

/* TOTAL */

.td-tot{
font-weight:700;
}

.sub-max{
display:block;
font-size:9px;
color:#777;
}

/* GRADE */

.td-grade{
font-weight:700;
color:#0f5132;
}

/* PASS FAIL */

.td-pf{
font-weight:700;
}

.pf-pass{
color:#15803d;
}

.pf-fail{
color:#dc2626;
}

/* ROW STRIPES */

.row-alt td{
background:#f5fbf7;
}

/* TOTAL ROW */

.row-subtotal td{
background:#e8f6ee;
font-weight:700;
}

/* ABSENT */

.row-absent td{
background:#fff5f5;
}

.ab{
color:#999;
font-style:italic;
}


/* Row striping */
  .row-alt td {
    background: #f3faf6;
  }

  .row-alt .td-subj {
    background: #f3faf6;
  }

  .row-absent td {
    background: #fff6f6 !important;
  }

  /* Sub-total row */
  .row-subtotal td {
    background: #e5f5ec !important;
    font-weight: 700;
    font-size: 11px;
  }

  /* ══════════════════════════════════════════════════
   GRADE LEGEND
══════════════════════════════════════════════════ */
  .rc-note {
    font-size: 9.5px;
    color: #333;
    padding: 4px 10px;
    background: #fffde8;
    border-top: 1px solid #e0d860;
    border-bottom: 1px solid #e0d860;
    font-style: italic;
  }

  .rc-note strong {
    color: #000;
    font-style: normal;
  }

  /* ══════════════════════════════════════════════════
   OVERALL MARKS — salmon / peach
══════════════════════════════════════════════════ */
  .rc-overall {
    display: flex;
    border-top: 1.5px solid #000;
    border-bottom: 1.5px solid #000;
    background: #fdebd0;
  }

  .ov-cell {
    flex: 1;
    text-align: center;
    padding: 8px 6px;
    border-right: 1px solid #c8a06a;
  }

  .ov-cell:last-child {
    border-right: none;
  }

  .ov-wide {
    flex: 2;
  }

  .ov-lbl {
    font-size: 9.5px;
    font-weight: 700;
    color: #5a3a1a;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: .3px;
  }

  .ov-val {
    font-size: 15px;
    font-weight: 900;
    color: #000;
  }

  .ov-val.pf-pass {
    color: #15803d;
  }

  .ov-val.pf-fail {
    color: #dc2626;
  }

  /* ══════════════════════════════════════════════════
   RESULT STATEMENT
══════════════════════════════════════════════════ */
  .rc-result {
    text-align: center;
    padding: 10px;
    font-size: 14px;
    font-weight: 900;
    letter-spacing: .5px;
    border-bottom: 1.5px solid #000;
    text-transform: uppercase;
  }

  .rc-promoted {
    color: #0d3b22;
  }

  .rc-failed {
    color: #b91c1c;
  }

  /* ══════════════════════════════════════════════════
   SIGNATURES
══════════════════════════════════════════════════ */
  .rc-sigs {
    display: flex;
    min-height: 96px;
  }

  .sig-cell {
    flex: 1;
    text-align: center;
    padding: 14px 12px 10px;
    border-right: 1px solid #000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
  }

  .sig-cell:last-child {
    border-right: none;
  }

  .sig-space {
    flex: 1;
  }

  .sig-seal {
    width: 64px;
    height: 64px;
    border: 2px dashed #bbb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8.5px;
    color: #bbb;
    text-align: center;
    line-height: 1.5;
    margin-bottom: 4px;
  }

  .sig-line {
    width: 64%;
    border-top: 1.5px solid #444;
    margin-bottom: 5px;
  }

  .sig-lbl {
    font-size: 10.5px;
    font-weight: 700;
    color: #000;
  }

  /* ══════════════════════════════════════════════════
   PRINT
══════════════════════════════════════════════════ */
  @media print {
    body {
      background: #fff;
    }

    .rc-toolbar {
      display: none !important;
    }

    .rc-wrapper {
      padding: 0;
    }

    .rc-page {
      max-width: 100%;
      margin: 0;
      border: 1.5px solid #000;
      box-shadow: none;
    }

    .rc-school-name {
      font-size: 26px;
    }

    .rc-tbl {
      font-size: 10px;
    }

    @page {
      size: A4 portrait;
      margin: 8mm;
    }
  }
</style>