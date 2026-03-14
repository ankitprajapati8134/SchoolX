<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Batch Report Cards — renders report_card.php for each student in a section
 * with CSS page-break-before between students. Standalone (no header/footer).
 *
 * Expects: $students[] — each item has the same keys as report_card.php expects
 *          (userId, examId, exam, profile, classKey, sectionKey, computed,
 *           templates, marks, schoolInfo, schoolName, sessionYear)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Batch Report Cards — <?= htmlspecialchars($exam['Name'] ?? 'Exam') ?> — <?= htmlspecialchars($classKey ?? '') ?> <?= htmlspecialchars($sectionKey ?? '') ?></title>
<style>
  /* ── Batch toolbar (hidden on print) ─────────────────────────────── */
  .batch-toolbar {
    position: sticky; top: 0; z-index: 100;
    display: flex; align-items: center; gap: 14px;
    padding: 10px 20px; background: #0f766e; color: #fff;
    font-family: 'Segoe UI', sans-serif;
  }
  .batch-toolbar button {
    padding: 7px 18px; background: #fff; color: #0f766e;
    border: none; border-radius: 6px; font-weight: 700;
    font-size: .9rem; cursor: pointer;
  }
  .batch-toolbar button:hover { background: #e0f2f1; }
  .batch-toolbar .batch-info { font-size: .9rem; opacity: .9; }

  /* Page break between report cards */
  .batch-page-break { page-break-before: always; }

  @media print {
    .batch-toolbar { display: none !important; }
    .batch-page-break { page-break-before: always; }
  }
</style>
</head>
<body style="margin:0; padding:0; background:#f5f5f5;">

<!-- ── Toolbar ────────────────────────────────────────────────────── -->
<div class="batch-toolbar">
  <button onclick="window.print()"><i class="fa fa-print"></i> Print All (<?= count($students) ?>)</button>
  <button onclick="window.history.back()">Back</button>
  <span class="batch-info">
    <?= htmlspecialchars($exam['Name'] ?? '') ?> &mdash;
    <?= htmlspecialchars($classKey ?? '') ?> / <?= htmlspecialchars($sectionKey ?? '') ?>
    &mdash; <?= count($students) ?> student(s)
  </span>
</div>

<?php foreach ($students as $idx => $stu):
  // Set variables expected by report_card.php
  $userId      = $stu['userId'];
  $examId      = $stu['examId'];
  $exam        = $stu['exam'];
  $profile     = $stu['profile'];
  $classKey    = $stu['classKey'];
  $sectionKey  = $stu['sectionKey'];
  $computed    = $stu['computed'];
  $templates   = $stu['templates'];
  $marks       = $stu['marks'];
  $schoolInfo  = $stu['schoolInfo'];
  $schoolName  = $stu['schoolName'];
  $sessionYear = $stu['sessionYear'];
?>

<?php if ($idx > 0): ?><div class="batch-page-break"></div><?php endif; ?>

<?php $batch_mode = true; $this->load->view('result/report_card'); ?>

<?php endforeach; ?>

</body>
</html>
