<?php defined('BASEPATH') OR exit('No direct script access allowed');
    $at = isset($active_tab) ? $active_tab : 'dashboard';
    $tab_map = [
        'dashboard'   => ['panel'=>'panelDash',      'icon'=>'fa-tachometer',        'label'=>'Dashboard'],
        'departments' => ['panel'=>'panelDept',       'icon'=>'fa-building-o',        'label'=>'Departments'],
        'recruitment' => ['panel'=>'panelRecruit',    'icon'=>'fa-briefcase',         'label'=>'Recruitment'],
        'leaves'      => ['panel'=>'panelLeaves',     'icon'=>'fa-calendar-minus-o',  'label'=>'Leave Mgmt'],
        'payroll'     => ['panel'=>'panelPayroll',    'icon'=>'fa-money',             'label'=>'Payroll'],
        'appraisals'  => ['panel'=>'panelAppraisal',  'icon'=>'fa-star',              'label'=>'Appraisals'],
    ];
?>

<div class="content-wrapper">
<section class="content">
<div class="hr-wrap">

  <!-- Header -->
  <div class="hr-header">
    <div>
      <div class="hr-header-icon"><i class="fa fa-users"></i> HR &amp; Staff Management</div>
      <ol class="hr-breadcrumb">
        <li><a href="<?= base_url('admin') ?>">Dashboard</a></li>
        <li>HR Module</li>
      </ol>
    </div>
  </div>

  <!-- Tabs -->
  <nav class="hr-tabs">
    <?php foreach ($tab_map as $slug => $t): ?>
    <a class="hr-tab<?= $at === $slug ? ' active' : '' ?>" href="<?= base_url('hr/' . $slug) ?>">
      <i class="fa <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- ================================================================
       1. DASHBOARD
  ================================================================= -->
  <div class="hr-panel<?= $at === 'dashboard' ? ' active' : '' ?>" id="panelDash">

    <div class="hr-stats" id="hrDashStats">
      <div class="hr-stat">
        <div class="hr-stat-value" id="statTotalStaff">--</div>
        <div class="hr-stat-label"><i class="fa fa-users"></i> Total Staff</div>
      </div>
      <div class="hr-stat">
        <div class="hr-stat-value" id="statDepts">--</div>
        <div class="hr-stat-label"><i class="fa fa-building-o"></i> Departments</div>
      </div>
      <div class="hr-stat">
        <div class="hr-stat-value" id="statOpenJobs">--</div>
        <div class="hr-stat-label"><i class="fa fa-briefcase"></i> Open Jobs</div>
      </div>
      <div class="hr-stat">
        <div class="hr-stat-value" id="statPendingLeaves">--</div>
        <div class="hr-stat-label"><i class="fa fa-calendar-minus-o"></i> Pending Leaves</div>
      </div>
      <div class="hr-stat">
        <div class="hr-stat-value" id="statPayrollStatus">--</div>
        <div class="hr-stat-label"><i class="fa fa-money"></i> Payroll Status</div>
      </div>
    </div>

    <div class="hr-card">
      <div class="hr-card-title"><i class="fa fa-bolt"></i> Quick Actions</div>
      <div class="hr-quick-actions">
        <button class="hr-btn hr-btn-primary" onclick="HR.openJobModal()"><i class="fa fa-plus"></i> New Job</button>
        <button class="hr-btn hr-btn-primary" onclick="HR.openLeaveTypeModal()"><i class="fa fa-plus"></i> New Leave Type</button>
        <button class="hr-btn hr-btn-primary" onclick="HR.openGeneratePayroll()"><i class="fa fa-play"></i> Run Payroll</button>
        <button class="hr-btn hr-btn-primary" onclick="HR.openAppraisalModal()"><i class="fa fa-plus"></i> New Appraisal</button>
      </div>
    </div>

    <div class="hr-dash-grid">
      <div class="hr-card">
        <div class="hr-card-title"><i class="fa fa-calendar-minus-o"></i> Recent Leave Requests</div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblDashLeaves">
            <thead><tr><th>Staff</th><th>Type</th><th>From</th><th>To</th><th>Status</th></tr></thead>
            <tbody><tr><td colspan="5" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
      <div class="hr-card">
        <div class="hr-card-title"><i class="fa fa-user-plus"></i> Recent Hires</div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblDashHires">
            <thead><tr><th>Name</th><th>Position</th><th>Department</th><th>Date</th><th>Status</th></tr></thead>
            <tbody><tr><td colspan="5" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================
       2. DEPARTMENTS
  ================================================================= -->
  <div class="hr-panel<?= $at === 'departments' ? ' active' : '' ?>" id="panelDept">
    <div class="hr-card">
      <div class="hr-card-title">
        <span><i class="fa fa-building-o"></i> Departments</span>
        <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openDeptModal()"><i class="fa fa-plus"></i> Add Department</button>
      </div>
      <div class="hr-table-wrap">
        <table class="hr-table" id="tblDepts">
          <thead><tr><th>#</th><th>Name</th><th>Head</th><th>Staff Count</th><th>Description</th><th>Actions</th></tr></thead>
          <tbody><tr><td colspan="6" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ================================================================
       3. RECRUITMENT
  ================================================================= -->
  <div class="hr-panel<?= $at === 'recruitment' ? ' active' : '' ?>" id="panelRecruit">
    <div class="hr-card">
      <div class="hr-card-title">
        <span><i class="fa fa-briefcase"></i> Job Postings</span>
        <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openJobModal()"><i class="fa fa-plus"></i> New Job Posting</button>
      </div>
      <div class="hr-toolbar">
        <div class="hr-fg">
          <label>Status</label>
          <select id="filterJobStatus" onchange="HR.loadJobs()">
            <option value="">All</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div class="hr-fg">
          <label>Department</label>
          <select id="filterJobDept" onchange="HR.loadJobs()"><option value="">All</option></select>
        </div>
      </div>
      <div class="hr-table-wrap">
        <table class="hr-table" id="tblJobs">
          <thead><tr><th>#</th><th>Title</th><th>Department</th><th>Positions</th><th>Applicants</th><th>Status</th><th>Deadline</th><th>Actions</th></tr></thead>
          <tbody><tr><td colspan="8" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
        </table>
      </div>
      <div id="applicantsContainer" style="display:none;">
        <div class="hr-card-title" style="margin-top:18px;">
          <span><i class="fa fa-users"></i> Applicants for: <strong id="applicantsJobTitle"></strong></span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openApplicantModal()"><i class="fa fa-plus"></i> Add Applicant</button>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblApplicants">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Interview</th><th>Rating</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================
       4. LEAVE MANAGEMENT
  ================================================================= -->
  <div class="hr-panel<?= $at === 'leaves' ? ' active' : '' ?>" id="panelLeaves">

    <div class="hr-sub-tabs">
      <button class="hr-sub-tab active" data-sub="leaveTypes">Leave Types</button>
      <button class="hr-sub-tab" data-sub="leaveRequests">Leave Requests</button>
      <button class="hr-sub-tab" data-sub="leaveBalances">Leave Balances</button>
    </div>

    <!-- Leave Types -->
    <div class="hr-sub-panel active" id="subLeaveTypes">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-list"></i> Leave Types</span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openLeaveTypeModal()"><i class="fa fa-plus"></i> Add Type</button>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblLeaveTypes">
            <thead><tr><th>#</th><th>Name</th><th>Code</th><th>Days/Year</th><th>Carry Forward</th><th>Max Carry</th><th>Actions</th></tr></thead>
            <tbody><tr><td colspan="7" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Leave Requests -->
    <div class="hr-sub-panel" id="subLeaveRequests">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-calendar-minus-o"></i> Leave Requests</span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openLeaveRequestModal()"><i class="fa fa-plus"></i> New Leave Request</button>
        </div>
        <div class="hr-toolbar">
          <div class="hr-fg">
            <label>Status</label>
            <select id="filterLeaveStatus" onchange="HR.loadLeaveRequests()">
              <option value="">All</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="hr-fg">
            <label>Staff</label>
            <select id="filterLeaveStaff" onchange="HR.loadLeaveRequests()"><option value="">All</option></select>
          </div>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblLeaveRequests">
            <thead><tr><th>#</th><th>Staff Name</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody><tr><td colspan="9" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Leave Balances -->
    <div class="hr-sub-panel" id="subLeaveBalances">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-balance-scale"></i> Leave Balances</span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.initBalances()"><i class="fa fa-refresh"></i> Initialize Balances</button>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblLeaveBalances">
            <thead id="theadBalances"><tr><th>Staff Name</th></tr></thead>
            <tbody><tr><td class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================
       5. PAYROLL
  ================================================================= -->
  <div class="hr-panel<?= $at === 'payroll' ? ' active' : '' ?>" id="panelPayroll">

    <div class="hr-sub-tabs">
      <button class="hr-sub-tab active" data-sub="salaryStructures">Salary Structures</button>
      <button class="hr-sub-tab" data-sub="payrollRuns">Payroll Runs</button>
      <button class="hr-sub-tab" data-sub="payslips">Payslips</button>
    </div>

    <!-- Salary Structures -->
    <div class="hr-sub-panel active" id="subSalaryStructures">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-sitemap"></i> Salary Structures</span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openSalaryModal()"><i class="fa fa-plus"></i> Add Structure</button>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblSalary">
            <thead><tr><th>#</th><th>Staff</th><th>Basic</th><th>HRA</th><th>DA</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Actions</th></tr></thead>
            <tbody><tr><td colspan="9" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Payroll Runs -->
    <div class="hr-sub-panel" id="subPayrollRuns">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-play-circle"></i> Payroll Runs</span>
          <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openGeneratePayroll()"><i class="fa fa-play"></i> Generate Payroll</button>
        </div>
        <div class="hr-table-wrap">
          <table class="hr-table" id="tblPayrollRuns">
            <thead><tr><th>#</th><th>Run ID</th><th>Month</th><th>Year</th><th>Staff Count</th><th>Total Gross</th><th>Total Net</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody><tr><td colspan="9" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Payslips -->
    <div class="hr-sub-panel" id="subPayslips">
      <div class="hr-card">
        <div class="hr-card-title">
          <span><i class="fa fa-file-text-o"></i> Payslips</span>
          <span id="payslipRunInfo" class="hr-badge hr-badge-draft" style="display:none;"></span>
        </div>
        <div id="payslipSelectMsg" class="hr-empty" style="padding:30px;text-align:center;">
          <i class="fa fa-info-circle"></i> Select a payroll run from the Payroll Runs tab to view payslips.
        </div>
        <div class="hr-table-wrap" id="payslipTableWrap" style="display:none;">
          <table class="hr-table" id="tblPayslips">
            <thead><tr><th>#</th><th>Staff</th><th>Basic</th><th>Allowances</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Days Worked</th><th>Absent</th><th>LWP</th><th></th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================================================================
       6. APPRAISALS
  ================================================================= -->
  <div class="hr-panel<?= $at === 'appraisals' ? ' active' : '' ?>" id="panelAppraisal">
    <div class="hr-card">
      <div class="hr-card-title">
        <span><i class="fa fa-star"></i> Performance Appraisals</span>
        <button class="hr-btn hr-btn-primary hr-btn-sm" onclick="HR.openAppraisalModal()"><i class="fa fa-plus"></i> New Appraisal</button>
      </div>
      <div class="hr-toolbar">
        <div class="hr-fg">
          <label>Period</label>
          <select id="filterAppraisalPeriod" onchange="HR.loadAppraisals()"><option value="">All</option></select>
        </div>
        <div class="hr-fg">
          <label>Staff</label>
          <select id="filterAppraisalStaff" onchange="HR.loadAppraisals()"><option value="">All</option></select>
        </div>
      </div>
      <div class="hr-table-wrap">
        <table class="hr-table" id="tblAppraisals">
          <thead><tr><th>#</th><th>Staff Name</th><th>Period</th><th>Reviewer</th><th>Overall Rating</th><th>Recommendation</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody><tr><td colspan="8" class="hr-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

</div><!-- /.hr-wrap -->
</section>
</div><!-- /.content-wrapper -->


<!-- ================================================================
     MODALS
================================================================= -->

<!-- Department Modal -->
<div class="hr-modal-overlay" id="modalDept">
  <div class="hr-modal">
    <div class="hr-modal-title"><i class="fa fa-building-o"></i> <span id="modalDeptTitle">Add Department</span></div>
    <input type="hidden" id="deptId">
    <div class="hr-fg">
      <label>Department Name <span class="req">*</span></label>
      <input type="text" id="deptName" placeholder="e.g. Mathematics" maxlength="100">
    </div>
    <div class="hr-fg">
      <label>Department Head</label>
      <select id="deptHead"><option value="">-- Select Staff --</option></select>
    </div>
    <div class="hr-fg">
      <label>Description</label>
      <textarea id="deptDesc" rows="3" placeholder="Brief description..."></textarea>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalDept')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveDept()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Job Posting Modal -->
<div class="hr-modal-overlay" id="modalJob">
  <div class="hr-modal" style="max-width:620px;">
    <div class="hr-modal-title"><i class="fa fa-briefcase"></i> <span id="modalJobTitle">New Job Posting</span></div>
    <input type="hidden" id="jobId">
    <div class="hr-modal-grid">
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Job Title <span class="req">*</span></label>
        <input type="text" id="jobTitle" placeholder="e.g. Mathematics Teacher" maxlength="150">
      </div>
      <div class="hr-fg">
        <label>Department <span class="req">*</span></label>
        <select id="jobDept"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg">
        <label>Positions <span class="req">*</span></label>
        <input type="number" id="jobPositions" min="1" value="1">
      </div>
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Description</label>
        <textarea id="jobDescription" rows="3" placeholder="Role description..."></textarea>
      </div>
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Requirements</label>
        <textarea id="jobRequirements" rows="3" placeholder="Qualifications, experience, etc."></textarea>
      </div>
      <div class="hr-fg">
        <label>Salary Range Min</label>
        <input type="number" id="jobSalaryMin" min="0" step="100" placeholder="0">
      </div>
      <div class="hr-fg">
        <label>Salary Range Max</label>
        <input type="number" id="jobSalaryMax" min="0" step="100" placeholder="0">
      </div>
      <div class="hr-fg">
        <label>Deadline</label>
        <input type="date" id="jobDeadline">
      </div>
      <div class="hr-fg">
        <label>Status</label>
        <select id="jobStatus">
          <option value="open">Open</option>
          <option value="closed">Closed</option>
        </select>
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalJob')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveJob()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Applicant Modal -->
<div class="hr-modal-overlay" id="modalApplicant">
  <div class="hr-modal" style="max-width:580px;">
    <div class="hr-modal-title"><i class="fa fa-user-plus"></i> <span id="modalApplicantTitle">Add Applicant</span></div>
    <input type="hidden" id="applicantId">
    <input type="hidden" id="applicantJobId">
    <div class="hr-modal-grid">
      <div class="hr-fg">
        <label>Full Name <span class="req">*</span></label>
        <input type="text" id="applicantName" placeholder="Full name" maxlength="100">
      </div>
      <div class="hr-fg">
        <label>Email</label>
        <input type="email" id="applicantEmail" placeholder="email@example.com">
      </div>
      <div class="hr-fg">
        <label>Phone</label>
        <input type="text" id="applicantPhone" placeholder="Phone number" maxlength="15">
      </div>
      <div class="hr-fg">
        <label>Status</label>
        <select id="applicantStatus">
          <option value="applied">Applied</option>
          <option value="shortlisted">Shortlisted</option>
          <option value="interviewed">Interviewed</option>
          <option value="selected">Selected</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
      <div class="hr-fg">
        <label>Interview Date</label>
        <input type="date" id="applicantInterview">
      </div>
      <div class="hr-fg">
        <label>Rating</label>
        <div class="hr-stars" id="applicantRatingStars">
          <i class="fa fa-star-o" data-v="1"></i>
          <i class="fa fa-star-o" data-v="2"></i>
          <i class="fa fa-star-o" data-v="3"></i>
          <i class="fa fa-star-o" data-v="4"></i>
          <i class="fa fa-star-o" data-v="5"></i>
        </div>
        <input type="hidden" id="applicantRating" value="0">
      </div>
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Interview Notes</label>
        <textarea id="applicantNotes" rows="3" placeholder="Notes from interview..."></textarea>
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalApplicant')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveApplicant()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Leave Type Modal -->
<div class="hr-modal-overlay" id="modalLeaveType">
  <div class="hr-modal">
    <div class="hr-modal-title"><i class="fa fa-list"></i> <span id="modalLeaveTypeTitle">Add Leave Type</span></div>
    <input type="hidden" id="leaveTypeId">
    <div class="hr-modal-grid">
      <div class="hr-fg">
        <label>Name <span class="req">*</span></label>
        <input type="text" id="ltName" placeholder="e.g. Casual Leave" maxlength="60">
      </div>
      <div class="hr-fg">
        <label>Code <span class="req">*</span></label>
        <input type="text" id="ltCode" placeholder="e.g. CL" maxlength="10" style="text-transform:uppercase;">
      </div>
      <div class="hr-fg">
        <label>Days / Year <span class="req">*</span></label>
        <input type="number" id="ltDays" min="0" value="12">
      </div>
      <div class="hr-fg">
        <label>Carry Forward</label>
        <select id="ltCarry">
          <option value="no">No</option>
          <option value="yes">Yes</option>
        </select>
      </div>
      <div class="hr-fg">
        <label>Max Carry Days</label>
        <input type="number" id="ltMaxCarry" min="0" value="0">
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalLeaveType')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveLeaveType()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Leave Request Modal -->
<div class="hr-modal-overlay" id="modalLeaveRequest">
  <div class="hr-modal">
    <div class="hr-modal-title"><i class="fa fa-calendar-minus-o"></i> New Leave Request</div>
    <input type="hidden" id="leaveReqId">
    <div class="hr-modal-grid">
      <div class="hr-fg">
        <label>Staff <span class="req">*</span></label>
        <select id="lrStaff"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg">
        <label>Leave Type <span class="req">*</span></label>
        <select id="lrType"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg">
        <label>Start Date <span class="req">*</span></label>
        <input type="date" id="lrStart">
      </div>
      <div class="hr-fg">
        <label>End Date <span class="req">*</span></label>
        <input type="date" id="lrEnd">
      </div>
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Reason</label>
        <textarea id="lrReason" rows="2" placeholder="Reason for leave..."></textarea>
      </div>
      <div class="hr-fg">
        <label><input type="checkbox" id="lrHalfDay"> Half Day</label>
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalLeaveRequest')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveLeaveRequest()"><i class="fa fa-check"></i> Submit</button>
    </div>
  </div>
</div>

<!-- Approve / Reject Leave Modal -->
<div class="hr-modal-overlay" id="modalLeaveAction">
  <div class="hr-modal" style="max-width:400px;">
    <div class="hr-modal-title"><i class="fa fa-gavel"></i> <span id="leaveActionTitle">Approve Leave</span></div>
    <input type="hidden" id="leaveActionId">
    <input type="hidden" id="leaveActionType">
    <div class="hr-fg">
      <label>Remarks</label>
      <textarea id="leaveActionRemarks" rows="3" placeholder="Optional remarks..."></textarea>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalLeaveAction')">Cancel</button>
      <button class="hr-btn hr-btn-primary" id="btnLeaveAction" onclick="HR.confirmLeaveAction()"><i class="fa fa-check"></i> Confirm</button>
    </div>
  </div>
</div>

<!-- Salary Structure Modal -->
<div class="hr-modal-overlay" id="modalSalary">
  <div class="hr-modal" style="max-width:680px;">
    <div class="hr-modal-title"><i class="fa fa-sitemap"></i> <span id="modalSalaryTitle">Add Salary Structure</span></div>
    <input type="hidden" id="salaryId">
    <div class="hr-modal-grid hr-salary-grid">
      <div class="hr-fg" style="grid-column:1/-1;">
        <label>Staff <span class="req">*</span></label>
        <select id="salStaff"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg-group">
        <div class="hr-fg-group-title">Earnings</div>
        <div class="hr-modal-grid">
          <div class="hr-fg"><label>Basic</label><input type="number" id="salBasic" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>HRA</label><input type="number" id="salHRA" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>DA</label><input type="number" id="salDA" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>TA</label><input type="number" id="salTA" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>Medical</label><input type="number" id="salMedical" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>Special Allowance</label><input type="number" id="salSpecial" min="0" step="100" value="0" oninput="HR.calcSalary()"></div>
        </div>
      </div>
      <div class="hr-fg-group">
        <div class="hr-fg-group-title">Deductions</div>
        <div class="hr-modal-grid">
          <div class="hr-fg"><label>PF Employee %</label><input type="number" id="salPFEmp" min="0" max="100" step="0.1" value="12" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>PF Employer %</label><input type="number" id="salPFEr" min="0" max="100" step="0.1" value="12" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>ESI Employee %</label><input type="number" id="salESIEmp" min="0" max="100" step="0.1" value="0.75" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>ESI Employer %</label><input type="number" id="salESIEr" min="0" max="100" step="0.1" value="3.25" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>Professional Tax</label><input type="number" id="salPT" min="0" step="50" value="200" oninput="HR.calcSalary()"></div>
          <div class="hr-fg"><label>TDS %</label><input type="number" id="salTDS" min="0" max="100" step="0.1" value="0" oninput="HR.calcSalary()"></div>
        </div>
      </div>
      <div class="hr-salary-summary">
        <div><span>Gross:</span> <strong id="salGrossDisplay">0.00</strong></div>
        <div><span>Deductions:</span> <strong id="salDeductDisplay">0.00</strong></div>
        <div class="hr-salary-net"><span>Net Pay:</span> <strong id="salNetDisplay">0.00</strong></div>
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalSalary')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveSalary()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Generate Payroll Modal -->
<div class="hr-modal-overlay" id="modalGenPayroll">
  <div class="hr-modal" style="max-width:380px;">
    <div class="hr-modal-title"><i class="fa fa-play-circle"></i> Generate Payroll</div>
    <div class="hr-modal-grid">
      <div class="hr-fg">
        <label>Month <span class="req">*</span></label>
        <select id="genMonth">
          <option value="January">January</option><option value="February">February</option><option value="March">March</option>
          <option value="April">April</option><option value="May">May</option><option value="June">June</option>
          <option value="July">July</option><option value="August">August</option><option value="September">September</option>
          <option value="October">October</option><option value="November">November</option><option value="December">December</option>
        </select>
      </div>
      <div class="hr-fg">
        <label>Year <span class="req">*</span></label>
        <input type="number" id="genYear" min="2020" max="2050" value="<?= date('Y') ?>">
      </div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalGenPayroll')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.generatePayroll()"><i class="fa fa-play"></i> Generate</button>
    </div>
  </div>
</div>

<!-- Appraisal Modal -->
<div class="hr-modal-overlay" id="modalAppraisal">
  <div class="hr-modal" style="max-width:660px;">
    <div class="hr-modal-title"><i class="fa fa-star"></i> <span id="modalAppraisalTitle">New Appraisal</span></div>
    <input type="hidden" id="appraisalId">
    <div class="hr-modal-grid">
      <div class="hr-fg">
        <label>Staff <span class="req">*</span></label>
        <select id="apStaff"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg">
        <label>Period <span class="req">*</span></label>
        <input type="text" id="apPeriod" placeholder="e.g. 2025-26 Q1" maxlength="30">
      </div>
      <div class="hr-fg">
        <label>Reviewer</label>
        <select id="apReviewer"><option value="">-- Select --</option></select>
      </div>
      <div class="hr-fg">
        <label>Recommendation</label>
        <select id="apRecommendation">
          <option value="none">None</option>
          <option value="increment">Increment</option>
          <option value="promotion">Promotion</option>
          <option value="warning">Warning</option>
          <option value="termination">Termination</option>
        </select>
      </div>
    </div>
    <div class="hr-fg-group" style="margin-top:8px;">
      <div class="hr-fg-group-title">Performance Scores (1-10)</div>
      <div class="hr-modal-grid hr-scores-grid">
        <div class="hr-fg"><label>Teaching</label><input type="number" id="apTeaching" min="1" max="10" value="5" oninput="HR.calcAppraisal()"></div>
        <div class="hr-fg"><label>Punctuality</label><input type="number" id="apPunctuality" min="1" max="10" value="5" oninput="HR.calcAppraisal()"></div>
        <div class="hr-fg"><label>Behavior</label><input type="number" id="apBehavior" min="1" max="10" value="5" oninput="HR.calcAppraisal()"></div>
        <div class="hr-fg"><label>Innovation</label><input type="number" id="apInnovation" min="1" max="10" value="5" oninput="HR.calcAppraisal()"></div>
        <div class="hr-fg"><label>Teamwork</label><input type="number" id="apTeamwork" min="1" max="10" value="5" oninput="HR.calcAppraisal()"></div>
        <div class="hr-fg hr-overall-box"><label>Overall</label><div class="hr-overall-val" id="apOverall">5.0</div></div>
      </div>
    </div>
    <div class="hr-modal-grid" style="margin-top:8px;">
      <div class="hr-fg" style="grid-column:1/-1;"><label>Strengths</label><textarea id="apStrengths" rows="2" placeholder="Key strengths..."></textarea></div>
      <div class="hr-fg" style="grid-column:1/-1;"><label>Areas of Improvement</label><textarea id="apImprovement" rows="2" placeholder="Areas to work on..."></textarea></div>
      <div class="hr-fg" style="grid-column:1/-1;"><label>Goals</label><textarea id="apGoals" rows="2" placeholder="Goals for next period..."></textarea></div>
    </div>
    <div class="hr-modal-actions">
      <button class="hr-btn hr-btn-ghost" onclick="HR.closeModal('modalAppraisal')">Cancel</button>
      <button class="hr-btn hr-btn-primary" onclick="HR.saveAppraisal()"><i class="fa fa-check"></i> Save</button>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div id="hrToastContainer"></div>


<!-- ================================================================
     STYLES
================================================================= -->
<style>
/* ================================================================
   HR Module - .hr-* prefix
   Teal/Navy theme using global CSS variables
================================================================ */
.hr-wrap{max-width:1180px;margin:0 auto;padding:20px 16px 48px}

/* Header */
.hr-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px}
.hr-header-icon{font-size:1.3rem;font-weight:700;color:var(--t1);display:flex;align-items:center;gap:8px;margin-bottom:3px;font-family:var(--font-b)}
.hr-header-icon i{color:var(--gold);font-size:1.1rem}
.hr-breadcrumb{list-style:none;margin:0;padding:0;display:flex;gap:6px;font-size:12px;color:var(--t3);font-family:var(--font-b)}
.hr-breadcrumb li+li::before{content:'/';margin-right:6px;color:var(--t3)}
.hr-breadcrumb a{color:var(--gold);text-decoration:none}
.hr-breadcrumb a:hover{text-decoration:underline}

/* Tabs */
.hr-tabs{display:flex;gap:4px;border-bottom:2px solid var(--border);margin-bottom:22px;flex-wrap:wrap}
.hr-tab{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:600;color:var(--t2);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s var(--ease);font-family:var(--font-b);border-radius:6px 6px 0 0}
.hr-tab:hover{color:var(--gold);background:var(--gold-dim)}
.hr-tab.active{color:var(--gold);border-bottom-color:var(--gold);background:var(--gold-dim)}
.hr-tab i{font-size:14px}

/* Panels */
.hr-panel{display:none}
.hr-panel.active{display:block}

/* Cards */
.hr-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:18px 20px;margin-bottom:16px;box-shadow:var(--sh)}
.hr-card-title{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:14px;font-weight:700;color:var(--t1);margin-bottom:14px;font-family:var(--font-b)}
.hr-card-title i{color:var(--gold)}
.hr-card-title span{display:flex;align-items:center;gap:8px}

/* Stats */
.hr-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:18px}
.hr-stat{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:18px 16px;text-align:center;box-shadow:var(--sh);transition:transform .2s var(--ease)}
.hr-stat:hover{transform:translateY(-2px)}
.hr-stat-value{font-size:1.6rem;font-weight:800;color:var(--gold);font-family:var(--font-m)}
.hr-stat-label{font-size:12px;color:var(--t3);margin-top:4px;font-family:var(--font-b)}
.hr-stat-label i{margin-right:3px}

/* Buttons */
.hr-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:13px;font-weight:600;border:none;border-radius:7px;cursor:pointer;transition:all .2s var(--ease);font-family:var(--font-b);text-decoration:none}
.hr-btn-primary{background:var(--gold);color:#fff}
.hr-btn-primary:hover{background:var(--gold2);transform:translateY(-1px)}
.hr-btn-danger{background:#dc2626;color:#fff}
.hr-btn-danger:hover{background:#b91c1c}
.hr-btn-ghost{background:transparent;color:var(--t2);border:1px solid var(--border)}
.hr-btn-ghost:hover{background:var(--gold-dim);color:var(--gold)}
.hr-btn-sm{padding:6px 12px;font-size:12px}

/* Toolbar / Filter Group */
.hr-toolbar{display:flex;gap:12px;margin-bottom:14px;flex-wrap:wrap;align-items:flex-end}
.hr-fg{display:flex;flex-direction:column;gap:4px}
.hr-fg label{font-size:11px;font-weight:600;color:var(--t3);font-family:var(--font-b);text-transform:uppercase;letter-spacing:.3px}
.hr-fg input,.hr-fg select,.hr-fg textarea{padding:8px 10px;font-size:13px;border:1px solid var(--border);border-radius:6px;background:var(--bg);color:var(--t1);font-family:var(--font-b);outline:none;transition:border-color .2s}
.hr-fg input:focus,.hr-fg select:focus,.hr-fg textarea:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-ring)}
.hr-fg textarea{resize:vertical}
.hr-fg .req{color:#dc2626}

/* Tables */
.hr-table-wrap{overflow-x:auto}
.hr-table{width:100%;border-collapse:collapse;font-size:13px;font-family:var(--font-b)}
.hr-table thead{background:var(--bg3)}
.hr-table th{padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid var(--border);white-space:nowrap}
.hr-table td{padding:10px 12px;border-bottom:1px solid var(--border);color:var(--t1);vertical-align:middle}
.hr-table tbody tr:hover{background:var(--gold-dim)}
.hr-table .hr-num{font-family:var(--font-m);font-size:12px;color:var(--t2)}
.hr-empty{text-align:center;padding:28px 12px;color:var(--t3);font-size:13px}
.hr-empty i{margin-right:6px}

/* Badges */
.hr-badge{display:inline-block;padding:3px 10px;font-size:11px;font-weight:700;border-radius:20px;text-transform:capitalize;font-family:var(--font-b);letter-spacing:.3px}
.hr-badge-pending{background:#fef3c7;color:#92400e}
.hr-badge-approved,.hr-badge-open{background:#d1fae5;color:#065f46}
.hr-badge-rejected{background:#fee2e2;color:#991b1b}
.hr-badge-cancelled{background:#e5e7eb;color:#4b5563}
.hr-badge-closed{background:#e5e7eb;color:#4b5563}
.hr-badge-draft{background:#dbeafe;color:#1e40af}
.hr-badge-finalized{background:#fef3c7;color:#92400e}
.hr-badge-paid{background:#d1fae5;color:#065f46}
.hr-badge-selected{background:#d1fae5;color:#065f46}
.hr-badge-applied{background:#dbeafe;color:#1e40af}
.hr-badge-shortlisted{background:#ede9fe;color:#5b21b6}
.hr-badge-interviewed{background:#fef3c7;color:#92400e}

/* Stars */
.hr-stars{display:inline-flex;gap:3px;cursor:pointer;font-size:18px;color:var(--gold)}
.hr-stars i{transition:transform .15s}
.hr-stars i:hover{transform:scale(1.2)}
.hr-stars-display{display:inline-flex;gap:2px;color:var(--gold);font-size:14px}
.hr-stars-display .empty{color:var(--border)}

/* Sub-tabs */
.hr-sub-tabs{display:flex;gap:4px;margin-bottom:16px;flex-wrap:wrap}
.hr-sub-tab{padding:8px 14px;font-size:12px;font-weight:600;border:1px solid var(--border);border-radius:7px;background:var(--bg);color:var(--t2);cursor:pointer;transition:all .2s var(--ease);font-family:var(--font-b)}
.hr-sub-tab:hover{background:var(--gold-dim);color:var(--gold)}
.hr-sub-tab.active{background:var(--gold);color:#fff;border-color:var(--gold)}
.hr-sub-panel{display:none}
.hr-sub-panel.active{display:block}

/* Modal */
.hr-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;animation:hrFadeIn .2s}
.hr-modal-overlay.open{display:flex}
.hr-modal{background:var(--bg2);border-radius:12px;padding:24px;max-width:500px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:hrSlideUp .25s var(--ease)}
.hr-modal-title{font-size:16px;font-weight:700;color:var(--t1);margin-bottom:18px;display:flex;align-items:center;gap:8px;font-family:var(--font-b)}
.hr-modal-title i{color:var(--gold)}
.hr-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)}
.hr-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* Salary Structure */
.hr-fg-group{grid-column:1/-1;border:1px solid var(--border);border-radius:8px;padding:14px;margin-top:4px}
.hr-fg-group-title{font-size:12px;font-weight:700;color:var(--gold);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;font-family:var(--font-b)}
.hr-salary-summary{grid-column:1/-1;display:flex;gap:20px;align-items:center;flex-wrap:wrap;padding:12px 14px;background:var(--bg3);border-radius:8px;margin-top:4px;font-family:var(--font-m);font-size:13px;color:var(--t2)}
.hr-salary-summary strong{color:var(--t1);font-size:14px}
.hr-salary-net strong{color:var(--gold);font-size:16px}
.hr-salary-grid{display:flex;flex-direction:column;gap:12px}

/* Appraisal scores */
.hr-scores-grid{grid-template-columns:repeat(3,1fr)}
.hr-overall-box{display:flex;flex-direction:column;align-items:center;justify-content:center}
.hr-overall-val{font-size:1.5rem;font-weight:800;color:var(--gold);font-family:var(--font-m)}

/* Quick Actions */
.hr-quick-actions{display:flex;gap:10px;flex-wrap:wrap}
.hr-dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* Action buttons in tables */
.hr-act-btn{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:var(--gold-dim);color:var(--gold);border:none;cursor:pointer;transition:all .2s;font-size:13px;text-decoration:none}
.hr-act-btn:hover{background:var(--gold);color:#fff}
.hr-act-btn.danger{background:#fee2e2;color:#dc2626}
.hr-act-btn.danger:hover{background:#dc2626;color:#fff}

/* Payslip expand */
.hr-payslip-detail{display:none;background:var(--bg3);padding:12px 16px}
.hr-payslip-detail.open{display:table-row}
.hr-payslip-breakdown{display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;font-size:12px;padding:10px}
.hr-payslip-breakdown dt{color:var(--t3);font-weight:600}
.hr-payslip-breakdown dd{color:var(--t1);font-family:var(--font-m);text-align:right;margin:0}

/* Toast */
#hrToastContainer{position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px}
.hr-toast{padding:12px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:var(--font-b);color:#fff;animation:hrSlideIn .3s var(--ease);min-width:240px;box-shadow:0 8px 24px rgba(0,0,0,.2)}
.hr-toast.success{background:#059669}
.hr-toast.error{background:#dc2626}
.hr-toast.info{background:#2563eb}

/* Animations */
@keyframes hrFadeIn{from{opacity:0}to{opacity:1}}
@keyframes hrSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes hrSlideIn{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}

/* Responsive */
@media(max-width:768px){
  .hr-tabs{gap:2px}
  .hr-tab{padding:8px 10px;font-size:11px}
  .hr-tab i{display:none}
  .hr-dash-grid{grid-template-columns:1fr}
  .hr-modal-grid{grid-template-columns:1fr}
  .hr-scores-grid{grid-template-columns:1fr 1fr}
  .hr-stats{grid-template-columns:repeat(2,1fr)}
  .hr-salary-summary{flex-direction:column;gap:6px}
  .hr-toolbar{flex-direction:column}
}
@media(max-width:480px){
  .hr-stats{grid-template-columns:1fr}
  .hr-scores-grid{grid-template-columns:1fr}
  .hr-wrap{padding:12px 8px 36px}
}
</style>


<!-- ================================================================
     JAVASCRIPT
================================================================= -->
<script>
/* Save PHP config before jQuery loads */
var _HR_CFG = {
  BASE:      '<?= base_url() ?>',
  CSRF_NAME: '<?= $this->security->get_csrf_token_name() ?>',
  CSRF_HASH: '<?= $this->security->get_csrf_hash() ?>',
  activeTab: '<?= $at ?>'
};
/* Defer until jQuery is available (loaded in footer) */
document.addEventListener('DOMContentLoaded', function(){
(function(){
  'use strict';

  /* ── Config ─────────────────────────────────────────────── */
  var BASE       = _HR_CFG.BASE;
  var CSRF_NAME  = _HR_CFG.CSRF_NAME;
  var CSRF_HASH  = _HR_CFG.CSRF_HASH;
  var activeTab  = _HR_CFG.activeTab;

  /* ── Caches ─────────────────────────────────────────────── */
  var deptCache      = {};
  var staffCache     = {};
  var leaveTypeCache = {};
  var _jobsCache     = {};
  var _appraisalCache= {};
  var _salaryCache   = {};
  var currentJobId   = null;
  var currentRunId   = null;

  /* ── Helpers ────────────────────────────────────────────── */
  function csrfData(){ var o={}; o[CSRF_NAME]=CSRF_HASH; return o; }
  function refreshCsrf(h){ if(h) CSRF_HASH=h; }

  function post(url, data){
    data = $.extend({}, csrfData(), data||{});
    return $.ajax({url:BASE+url, type:'POST', data:data, dataType:'json'}).then(function(r){
      if(r && r.csrf_hash) refreshCsrf(r.csrf_hash);
      return r;
    }).catch(function(xhr){
      var msg='Request failed';
      try{ var j=JSON.parse(xhr.responseText); if(j.csrf_hash) refreshCsrf(j.csrf_hash); msg=j.message||msg; }catch(e){}
      toast(msg,'error');
      return $.Deferred().reject(msg);
    });
  }
  function getJSON(url){
    return $.ajax({url:BASE+url, type:'GET', dataType:'json'}).catch(function(){
      toast('Failed to load data','error');
      return $.Deferred().reject();
    });
  }

  function toast(msg, type){
    type = type||'info';
    var $t = $('<div class="hr-toast '+type+'">'+esc(msg)+'</div>');
    $('#hrToastContainer').append($t);
    setTimeout(function(){ $t.fadeOut(300,function(){ $t.remove(); }); }, 3500);
  }

  function fmt(n){
    n = parseFloat(n)||0;
    return n.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  }

  // Convert array [{id:'X',...},...] to object {X:{...},...}
  function toMap(arr){
    if(!Array.isArray(arr)) return arr||{};
    var m={};
    $.each(arr,function(i,o){ if(o&&o.id) m[o.id]=o; else m[i]=o; });
    return m;
  }

  function esc(s){
    if(!s) return '';
    var d=document.createElement('div'); d.textContent=s; return d.innerHTML;
  }

  function closeModal(id){
    $('#'+id).removeClass('open');
  }
  function openModal(id){
    $('#'+id).addClass('open');
  }

  function starsHtml(rating, max){
    max = max||5;
    var h='';
    for(var i=1;i<=max;i++){
      h += '<i class="fa '+(i<=rating?'fa-star':'fa-star-o')+' '+(i>rating?'empty':'')+'"></i>';
    }
    return '<span class="hr-stars-display">'+h+'</span>';
  }

  function badgeHtml(status){
    var cls = 'hr-badge hr-badge-'+(status||'').toLowerCase().replace(/\s+/g,'-');
    return '<span class="'+cls+'">'+esc(status)+'</span>';
  }

  function staffName(id){
    if(staffCache[id]) return staffCache[id].Name || staffCache[id].name || id;
    return id;
  }
  function deptName(id){
    if(deptCache[id]) return deptCache[id].name || id;
    return id;
  }

  function fillStaffSelect(selId, selectedVal){
    var $s = $(selId);
    var first = $s.find('option:first').prop('outerHTML');
    $s.html(first);
    $.each(staffCache, function(k,v){
      var n = v.Name||v.name||k;
      $s.append('<option value="'+esc(k)+'"'+(k===selectedVal?' selected':'')+'>'+esc(n)+'</option>');
    });
  }
  function fillDeptSelect(selId, selectedVal){
    var $s = $(selId);
    var first = $s.find('option:first').prop('outerHTML');
    $s.html(first);
    $.each(deptCache, function(k,v){
      $s.append('<option value="'+esc(k)+'"'+(k===selectedVal?' selected':'')+'>'+esc(v.name)+'</option>');
    });
  }
  function fillLeaveTypeSelect(selId, selectedVal){
    var $s = $(selId);
    var first = $s.find('option:first').prop('outerHTML');
    $s.html(first);
    $.each(leaveTypeCache, function(k,v){
      $s.append('<option value="'+esc(k)+'"'+(k===selectedVal?' selected':'')+'>'+esc(v.name)+' ('+esc(v.code)+')</option>');
    });
  }

  /* ── Init ───────────────────────────────────────────────── */
  function init(){
    // Load staff + departments first, then tab-specific data
    $.when(loadStaffCache(), loadDeptCache()).then(function(){
      loadLeaveTypesCacheQuiet();
      populateFilterDropdowns();
      loadTabData();
    });

    // Sub-tab switching
    $(document).on('click', '.hr-sub-tab', function(){
      var sub = $(this).data('sub');
      var $parent = $(this).closest('.hr-panel');
      $parent.find('.hr-sub-tab').removeClass('active');
      $(this).addClass('active');
      $parent.find('.hr-sub-panel').removeClass('active');
      $parent.find('#sub' + sub.charAt(0).toUpperCase() + sub.slice(1)).addClass('active');
    });

    // Star rating click
    $(document).on('click', '.hr-stars i', function(){
      var val = $(this).data('v');
      var $parent = $(this).closest('.hr-stars');
      $parent.find('i').each(function(){
        var v = $(this).data('v');
        $(this).toggleClass('fa-star', v<=val).toggleClass('fa-star-o', v>val);
      });
      $parent.next('input[type=hidden]').val(val);
    });

    // Click outside modal to close
    $(document).on('click', '.hr-modal-overlay', function(e){
      if(e.target === this) $(this).removeClass('open');
    });

    // Set current month in payroll generator
    $('#genMonth').val(['January','February','March','April','May','June','July','August','September','October','November','December'][new Date().getMonth()]);
  }

  function loadStaffCache(){
    return getJSON('hr/get_staff_list').then(function(r){
      staffCache = toMap((r && r.staff) ? r.staff : (r && r.data) ? r.data : {});
    });
  }
  function loadDeptCache(){
    return getJSON('hr/get_departments').then(function(r){
      deptCache = toMap((r && r.departments) ? r.departments : (r && r.data) ? r.data : {});
    });
  }
  function loadLeaveTypesCacheQuiet(){
    return getJSON('hr/get_leave_types').then(function(r){
      leaveTypeCache = toMap((r && r.leave_types) ? r.leave_types : (r && r.data) ? r.data : {});
    });
  }

  function populateFilterDropdowns(){
    fillStaffSelect('#filterLeaveStaff');
    fillStaffSelect('#filterAppraisalStaff');
    fillDeptSelect('#filterJobDept');
  }

  function loadTabData(){
    switch(activeTab){
      case 'dashboard':    loadDashboard(); break;
      case 'departments':  loadDepartments(); break;
      case 'recruitment':  loadJobs(); break;
      case 'leaves':       loadLeaveTypes(); loadLeaveRequests(); loadLeaveBalances(); break;
      case 'payroll':      loadSalaryStructures(); loadPayrollRuns(); break;
      case 'appraisals':   loadAppraisals(); break;
    }
  }

  /* ================================================================
     DASHBOARD
  ================================================================ */
  function loadDashboard(){
    getJSON('hr/get_dashboard').then(function(r){
      if(!r) return;
      $('#statTotalStaff').text(r.staff_count||0);
      $('#statDepts').text(r.dept_count||0);
      $('#statOpenJobs').text(r.open_jobs||0);
      $('#statPendingLeaves').text(r.pending_leaves||0);
      var lp = r.last_payroll;
      $('#statPayrollStatus').text(lp ? (lp.month+' '+lp.year+' - '+lp.status) : '--');

      // Recent leaves
      var $lb = $('#tblDashLeaves tbody');
      if(r.recent_leaves && r.recent_leaves.length){
        var h='';
        $.each(r.recent_leaves.slice(0,5), function(i,l){
          h+='<tr><td>'+esc(l.staff_name||staffName(l.staff_id))+'</td><td>'+esc(l.type_name||l.type||'')+'</td><td>'+esc(l.from_date||l.start_date||'')+'</td><td>'+esc(l.to_date||l.end_date||'')+'</td><td>'+badgeHtml(l.status)+'</td></tr>';
        });
        $lb.html(h);
      } else {
        $lb.html('<tr><td colspan="5" class="hr-empty"><i class="fa fa-inbox"></i> No recent leave requests</td></tr>');
      }

      // Recent hires — from applicants with 'Joined' status (not in dashboard response, show placeholder)
      var $hb = $('#tblDashHires tbody');
      $hb.html('<tr><td colspan="5" class="hr-empty"><i class="fa fa-inbox"></i> Check Recruitment tab for applicant status</td></tr>');
    });
  }

  /* ================================================================
     DEPARTMENTS
  ================================================================ */
  function loadDepartments(){
    getJSON('hr/get_departments').then(function(r){
      deptCache = toMap((r&&r.departments)?r.departments:(r&&r.data)?r.data:{});
      var $tb=$('#tblDepts tbody');
      var keys=Object.keys(deptCache);
      if(!keys.length){ $tb.html('<tr><td colspan="6" class="hr-empty"><i class="fa fa-inbox"></i> No departments found. Add one to get started.</td></tr>'); return; }
      var h='', i=0;
      $.each(deptCache, function(k,d){
        i++;
        h+='<tr><td class="hr-num">'+i+'</td><td><strong>'+esc(d.name)+'</strong></td><td>'+esc(staffName(d.head_staff_id))+'</td><td class="hr-num">'+(d.staff_count||0)+'</td><td>'+esc(d.description||'-')+'</td>';
        h+='<td><button class="hr-act-btn" onclick="HR.editDept(\''+esc(k)+'\')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="HR.deleteDept(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);
    });
  }

  function openDeptModal(id){
    $('#deptId').val('');
    $('#deptName').val('');
    $('#deptDesc').val('');
    fillStaffSelect('#deptHead');
    $('#modalDeptTitle').text('Add Department');
    if(id && deptCache[id]){
      var d=deptCache[id];
      $('#deptId').val(id);
      $('#deptName').val(d.name);
      $('#deptDesc').val(d.description);
      fillStaffSelect('#deptHead', d.head_staff_id);
      $('#modalDeptTitle').text('Edit Department');
    }
    openModal('modalDept');
  }

  function saveDept(){
    var name=$('#deptName').val().trim();
    if(!name){ toast('Department name is required','error'); return; }
    var data={
      id: $('#deptId').val(),
      name: name,
      head_staff_id: $('#deptHead').val(),
      description: $('#deptDesc').val().trim()
    };
    post('hr/save_department', data).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalDept'); loadDepartments(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteDept(id){
    if(!confirm('Delete this department? This cannot be undone.')) return;
    post('hr/delete_department', {id:id}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadDepartments(); }
      else toast(r.message||'Failed','error');
    });
  }

  /* ================================================================
     RECRUITMENT
  ================================================================ */
  function loadJobs(){
    var params = '?status='+($('#filterJobStatus').val()||'')+'&department='+($('#filterJobDept').val()||'');
    getJSON('hr/get_jobs'+params).then(function(r){
      var jobs = toMap((r&&r.jobs)?r.jobs:(r&&r.data)?r.data:{});
      _jobsCache = jobs;
      var $tb=$('#tblJobs tbody');
      var keys=Object.keys(jobs);
      if(!keys.length){ $tb.html('<tr><td colspan="8" class="hr-empty"><i class="fa fa-inbox"></i> No job postings found.</td></tr>'); return; }
      var h='', i=0;
      $.each(jobs, function(k,j){
        i++;
        h+='<tr class="hr-job-row" data-id="'+esc(k)+'" style="cursor:pointer">';
        h+='<td class="hr-num">'+i+'</td><td><strong>'+esc(j.title)+'</strong></td><td>'+esc(deptName(j.department))+'</td>';
        h+='<td class="hr-num">'+(j.positions||0)+'</td><td class="hr-num">'+(j.applicant_count||0)+'</td>';
        h+='<td>'+badgeHtml(j.status)+'</td><td>'+esc(j.deadline||'-')+'</td>';
        h+='<td><button class="hr-act-btn" onclick="event.stopPropagation();HR.editJob(\''+esc(k)+'\')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="event.stopPropagation();HR.deleteJob(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);

      // Row click => show applicants
      $tb.find('.hr-job-row').on('click', function(){
        var jid=$(this).data('id');
        loadApplicants(jid, jobs[jid]?jobs[jid].title:'');
      });
    });
  }

  function loadApplicants(jobId, jobTitle){
    currentJobId = jobId;
    $('#applicantsJobTitle').text(jobTitle||jobId);
    $('#applicantsContainer').show();
    getJSON('hr/get_applicants?job_id='+encodeURIComponent(jobId)).then(function(r){
      var apps = toMap((r&&r.applicants)?r.applicants:(r&&r.data)?r.data:{});
      var $tb=$('#tblApplicants tbody');
      var keys=Object.keys(apps);
      if(!keys.length){ $tb.html('<tr><td colspan="8" class="hr-empty"><i class="fa fa-inbox"></i> No applicants yet.</td></tr>'); return; }
      var h='', i=0;
      $.each(apps, function(k,a){
        i++;
        h+='<tr><td class="hr-num">'+i+'</td><td>'+esc(a.name)+'</td><td>'+esc(a.email||'-')+'</td><td>'+esc(a.phone||'-')+'</td>';
        h+='<td>'+badgeHtml(a.status)+'</td><td>'+esc(a.interview_date||'-')+'</td>';
        h+='<td>'+starsHtml(a.rating||0)+'</td>';
        h+='<td><button class="hr-act-btn" onclick="HR.editApplicant(\''+esc(k)+'\','+JSON.stringify(a).replace(/'/g,"\\'")+')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="HR.deleteApplicant(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);
    });
  }

  function openJobModal(id){
    $('#jobId').val('');
    $('#jobTitle,#jobDescription,#jobRequirements').val('');
    $('#jobPositions').val(1);
    $('#jobSalaryMin,#jobSalaryMax').val('');
    $('#jobDeadline').val('');
    $('#jobStatus').val('open');
    fillDeptSelect('#jobDept');
    $('#modalJobTitle').text('New Job Posting');
    openModal('modalJob');
  }

  function editJob(id){
    // Use cached jobs data instead of a separate API call
    if(!_jobsCache[id]){ toast('Job not found in cache — refreshing','warning'); loadJobs(); return; }
    var j=_jobsCache[id];
    $('#jobId').val(id);
    $('#jobTitle').val(j.title||'');
    fillDeptSelect('#jobDept', j.department);
    $('#jobDescription').val(j.description||'');
    $('#jobRequirements').val(j.requirements||j.qualifications||'');
    $('#jobPositions').val(j.positions||1);
    $('#jobSalaryMin').val(j.salary_range_min||'');
    $('#jobSalaryMax').val(j.salary_range_max||'');
    $('#jobDeadline').val(j.deadline||'');
    $('#jobStatus').val(j.status||'Open');
    $('#modalJobTitle').text('Edit Job Posting');
    openModal('modalJob');
  }

  function saveJob(){
    var title=$('#jobTitle').val().trim();
    var dept=$('#jobDept').val();
    if(!title||!dept){ toast('Title and department are required','error'); return; }
    post('hr/save_job', {
      id:$('#jobId').val(), title:title, department:dept,
      description:$('#jobDescription').val().trim(), requirements:$('#jobRequirements').val().trim(),
      positions:$('#jobPositions').val(), salary_range_min:$('#jobSalaryMin').val(),
      salary_range_max:$('#jobSalaryMax').val(), deadline:$('#jobDeadline').val(), status:$('#jobStatus').val()
    }).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalJob'); loadJobs(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteJob(id){
    if(!confirm('Delete this job posting and all its applicants?')) return;
    post('hr/delete_job', {id:id}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadJobs(); $('#applicantsContainer').hide(); }
      else toast(r.message||'Failed','error');
    });
  }

  function openApplicantModal(){
    if(!currentJobId){ toast('Select a job first','error'); return; }
    $('#applicantId').val('');
    $('#applicantJobId').val(currentJobId);
    $('#applicantName,#applicantEmail,#applicantPhone,#applicantNotes').val('');
    $('#applicantStatus').val('applied');
    $('#applicantInterview').val('');
    $('#applicantRating').val(0);
    $('#applicantRatingStars i').removeClass('fa-star').addClass('fa-star-o');
    $('#modalApplicantTitle').text('Add Applicant');
    openModal('modalApplicant');
  }

  function editApplicant(id, data){
    $('#applicantId').val(id);
    $('#applicantJobId').val(currentJobId);
    $('#applicantName').val(data.name);
    $('#applicantEmail').val(data.email);
    $('#applicantPhone').val(data.phone);
    $('#applicantStatus').val(data.status);
    $('#applicantInterview').val(data.interview_date);
    $('#applicantNotes').val(data.interview_notes);
    var rating=parseInt(data.rating)||0;
    $('#applicantRating').val(rating);
    $('#applicantRatingStars i').each(function(){ var v=$(this).data('v'); $(this).toggleClass('fa-star',v<=rating).toggleClass('fa-star-o',v>rating); });
    $('#modalApplicantTitle').text('Edit Applicant');
    openModal('modalApplicant');
  }

  function saveApplicant(){
    var name=$('#applicantName').val().trim();
    if(!name){ toast('Name is required','error'); return; }
    post('hr/save_applicant', {
      id:$('#applicantId').val(), job_id:$('#applicantJobId').val(),
      name:name, email:$('#applicantEmail').val().trim(), phone:$('#applicantPhone').val().trim(),
      status:$('#applicantStatus').val(), interview_date:$('#applicantInterview').val(),
      interview_notes:$('#applicantNotes').val().trim(), rating:$('#applicantRating').val()
    }).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalApplicant'); loadApplicants(currentJobId,$('#applicantsJobTitle').text()); loadJobs(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteApplicant(id){
    if(!confirm('Delete this applicant?')) return;
    post('hr/delete_applicant', {id:id, job_id:currentJobId}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadApplicants(currentJobId,$('#applicantsJobTitle').text()); }
      else toast(r.message||'Failed','error');
    });
  }

  /* ================================================================
     LEAVE MANAGEMENT
  ================================================================ */
  function loadLeaveTypes(){
    getJSON('hr/get_leave_types').then(function(r){
      leaveTypeCache = toMap((r&&r.leave_types)?r.leave_types:(r&&r.data)?r.data:{});
      var $tb=$('#tblLeaveTypes tbody');
      var keys=Object.keys(leaveTypeCache);
      if(!keys.length){ $tb.html('<tr><td colspan="7" class="hr-empty"><i class="fa fa-inbox"></i> No leave types defined.</td></tr>'); return; }
      var h='', i=0;
      $.each(leaveTypeCache, function(k,t){
        i++;
        h+='<tr><td class="hr-num">'+i+'</td><td><strong>'+esc(t.name)+'</strong></td><td class="hr-num">'+esc(t.code)+'</td>';
        h+='<td class="hr-num">'+(t.days_per_year||0)+'</td><td>'+(t.carry_forward==='yes'?'<i class="fa fa-check" style="color:var(--gold)"></i>':'<i class="fa fa-times" style="color:var(--t3)"></i>')+'</td>';
        h+='<td class="hr-num">'+(t.max_carry||0)+'</td>';
        h+='<td><button class="hr-act-btn" onclick="HR.editLeaveType(\''+esc(k)+'\')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="HR.deleteLeaveType(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);
    });
  }

  function openLeaveTypeModal(id){
    $('#leaveTypeId').val('');
    $('#ltName,#ltCode').val('');
    $('#ltDays').val(12);
    $('#ltCarry').val('no');
    $('#ltMaxCarry').val(0);
    $('#modalLeaveTypeTitle').text('Add Leave Type');
    if(id && leaveTypeCache[id]){
      var t=leaveTypeCache[id];
      $('#leaveTypeId').val(id);
      $('#ltName').val(t.name);
      $('#ltCode').val(t.code);
      $('#ltDays').val(t.days_per_year);
      $('#ltCarry').val(t.carry_forward||'no');
      $('#ltMaxCarry').val(t.max_carry||0);
      $('#modalLeaveTypeTitle').text('Edit Leave Type');
    }
    openModal('modalLeaveType');
  }

  function saveLeaveType(){
    var name=$('#ltName').val().trim(), code=$('#ltCode').val().trim().toUpperCase();
    if(!name||!code){ toast('Name and code are required','error'); return; }
    post('hr/save_leave_type', {
      id:$('#leaveTypeId').val(), name:name, code:code,
      days_per_year:$('#ltDays').val(), carry_forward:$('#ltCarry').val(), max_carry:$('#ltMaxCarry').val()
    }).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalLeaveType'); loadLeaveTypes(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteLeaveType(id){
    if(!confirm('Delete this leave type?')) return;
    post('hr/delete_leave_type', {id:id}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadLeaveTypes(); }
      else toast(r.message||'Failed','error');
    });
  }

  function loadLeaveRequests(){
    var params='?status='+($('#filterLeaveStatus').val()||'')+'&staff_id='+($('#filterLeaveStaff').val()||'');
    getJSON('hr/get_leave_requests'+params).then(function(r){
      var reqs=toMap((r&&r.leave_requests)?r.leave_requests:(r&&r.data)?r.data:{});
      var $tb=$('#tblLeaveRequests tbody');
      var keys=Object.keys(reqs);
      if(!keys.length){ $tb.html('<tr><td colspan="9" class="hr-empty"><i class="fa fa-inbox"></i> No leave requests found.</td></tr>'); return; }
      var h='', i=0;
      $.each(reqs, function(k,l){
        i++;
        var lwpTag = (parseInt(l.lwp_days||0)>0) ? ' <span class="hr-badge" style="background:var(--amber);color:#fff;font-size:10px">LWP:'+l.lwp_days+'</span>' : '';
        h+='<tr><td class="hr-num">'+i+'</td><td>'+esc(staffName(l.staff_id))+'</td><td>'+esc(l.type_name||l.leave_type)+'</td>';
        h+='<td>'+esc(l.from_date||l.start_date)+'</td><td>'+esc(l.to_date||l.end_date)+'</td><td class="hr-num">'+(l.days||'-')+lwpTag+'</td>';
        h+='<td>'+esc(l.reason||'-')+'</td><td>'+badgeHtml(l.status)+'</td>';
        h+='<td>';
        if((l.status||'').toLowerCase()==='pending'){
          h+='<button class="hr-act-btn" onclick="HR.approveLeave(\''+esc(k)+'\')" title="Approve"><i class="fa fa-check"></i></button> ';
          h+='<button class="hr-act-btn danger" onclick="HR.rejectLeave(\''+esc(k)+'\')" title="Reject"><i class="fa fa-times"></i></button>';
        } else { h+='-'; }
        h+='</td></tr>';
      });
      $tb.html(h);
    });
  }

  function openLeaveRequestModal(){
    $('#leaveReqId').val('');
    $('#lrReason').val('');
    $('#lrStart,#lrEnd').val('');
    $('#lrHalfDay').prop('checked',false);
    fillStaffSelect('#lrStaff');
    fillLeaveTypeSelect('#lrType');
    openModal('modalLeaveRequest');
  }

  function saveLeaveRequest(){
    var staff=$('#lrStaff').val(), type=$('#lrType').val(), start=$('#lrStart').val(), end=$('#lrEnd').val();
    if(!staff||!type||!start||!end){ toast('Staff, type, start and end dates are required','error'); return; }
    post('hr/apply_leave', {
      staff_id:staff, type_id:type,
      from_date:start, to_date:end, reason:$('#lrReason').val().trim()
    }).then(function(r){
      if(r&&r.status){
        var msg = r.message||'Submitted';
        if(r.lwp_warning){ toast(msg,'warning'); } else { toast(msg,'success'); }
        closeModal('modalLeaveRequest'); loadLeaveRequests();
      } else toast(r.message||'Failed','error');
    });
  }

  function approveLeave(id){
    $('#leaveActionId').val(id);
    $('#leaveActionType').val('approved');
    $('#leaveActionTitle').text('Approve Leave');
    $('#leaveActionRemarks').val('');
    $('#btnLeaveAction').removeClass('hr-btn-danger').addClass('hr-btn-primary').html('<i class="fa fa-check"></i> Approve');
    openModal('modalLeaveAction');
  }
  function rejectLeave(id){
    $('#leaveActionId').val(id);
    $('#leaveActionType').val('rejected');
    $('#leaveActionTitle').text('Reject Leave');
    $('#leaveActionRemarks').val('');
    $('#btnLeaveAction').removeClass('hr-btn-primary').addClass('hr-btn-danger').html('<i class="fa fa-times"></i> Reject');
    openModal('modalLeaveAction');
  }
  function confirmLeaveAction(){
    var id=$('#leaveActionId').val(), action=$('#leaveActionType').val();
    // Controller expects 'decision' param with capitalized value
    var decision = action === 'approved' ? 'Approved' : 'Rejected';
    post('hr/decide_leave', {id:id, decision:decision, remarks:$('#leaveActionRemarks').val().trim()}).then(function(r){
      if(r&&r.status){
        var msg = r.message||'Updated';
        if(r.lwp_days && parseInt(r.lwp_days)>0){ toast(msg,'warning'); } else { toast(msg,'success'); }
        closeModal('modalLeaveAction'); loadLeaveRequests();
      } else toast(r.message||'Failed','error');
    });
  }

  function loadLeaveBalances(){
    getJSON('hr/get_leave_balances').then(function(r){
      var data=(r&&r.balances)?r.balances:(r&&r.data)?r.data:{};
      var types=(r&&r.types)?r.types:{};
      var $thead=$('#theadBalances tr');
      var $tb=$('#tblLeaveBalances tbody');

      // Build header with leave type columns
      var thH='<th>Staff Name</th>';
      var typeKeys=Object.keys(types);
      $.each(typeKeys, function(i,tk){
        thH+='<th class="hr-num">'+esc(types[tk])+'</th>';
      });
      $thead.html(thH);

      var staffKeys=Object.keys(data);
      if(!staffKeys.length){ $tb.html('<tr><td colspan="'+(typeKeys.length+1)+'" class="hr-empty"><i class="fa fa-inbox"></i> No balance data. Click Initialize Balances to set up.</td></tr>'); return; }
      var h='';
      $.each(data, function(sid,balances){
        h+='<tr><td>'+esc(staffName(sid))+'</td>';
        $.each(typeKeys, function(i,tk){
          h+='<td class="hr-num">'+(balances[tk]!=null?balances[tk]:'-')+'</td>';
        });
        h+='</tr>';
      });
      $tb.html(h);
    });
  }

  function initBalances(){
    if(!confirm('Initialize leave balances for all staff based on leave type definitions? Existing balances will not be overwritten.')) return;
    post('hr/initialize_balances').then(function(r){
      if(r&&r.status){ toast(r.message||'Initialized','success'); loadLeaveBalances(); }
      else toast(r.message||'Failed','error');
    });
  }

  /* ================================================================
     PAYROLL
  ================================================================ */
  function loadSalaryStructures(){
    getJSON('hr/get_salary_structures').then(function(r){
      var structs=toMap((r&&r.salary_structures)?r.salary_structures:(r&&r.data)?r.data:{});
      _salaryCache = structs;
      var $tb=$('#tblSalary tbody');
      var keys=Object.keys(structs);
      if(!keys.length){ $tb.html('<tr><td colspan="9" class="hr-empty"><i class="fa fa-inbox"></i> No salary structures defined.</td></tr>'); return; }
      var h='', i=0;
      $.each(structs, function(k,s){
        i++;
        var gross=calcGrossFromObj(s), deductions=calcDeductionsFromObj(s,gross), net=gross-deductions;
        h+='<tr><td class="hr-num">'+i+'</td><td>'+esc(staffName(s.staff_id))+'</td>';
        h+='<td class="hr-num">'+fmt(s.basic)+'</td><td class="hr-num">'+fmt(s.hra)+'</td><td class="hr-num">'+fmt(s.da)+'</td>';
        h+='<td class="hr-num"><strong>'+fmt(gross)+'</strong></td><td class="hr-num">'+fmt(deductions)+'</td>';
        h+='<td class="hr-num"><strong style="color:var(--gold)">'+fmt(net)+'</strong></td>';
        h+='<td><button class="hr-act-btn" onclick="HR.editSalary(\''+esc(k)+'\')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="HR.deleteSalary(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);
    });
  }

  function calcGrossFromObj(s){
    return (parseFloat(s.basic)||0)+(parseFloat(s.hra)||0)+(parseFloat(s.da)||0)+(parseFloat(s.ta)||0)+(parseFloat(s.medical)||0)+(parseFloat(s.special_allowance)||0);
  }
  function calcDeductionsFromObj(s,gross){
    var basic=parseFloat(s.basic)||0;
    var pfEmp=basic*((parseFloat(s.pf_employee)||0)/100);
    var esiEmp=gross*((parseFloat(s.esi_employee)||0)/100);
    var pt=parseFloat(s.professional_tax)||0;
    var tds=gross*((parseFloat(s.tds)||0)/100);
    return pfEmp+esiEmp+pt+tds;
  }

  function calcSalary(){
    var basic=parseFloat($('#salBasic').val())||0;
    var hra=parseFloat($('#salHRA').val())||0;
    var da=parseFloat($('#salDA').val())||0;
    var ta=parseFloat($('#salTA').val())||0;
    var med=parseFloat($('#salMedical').val())||0;
    var sp=parseFloat($('#salSpecial').val())||0;
    var gross=basic+hra+da+ta+med+sp;

    var pfEmp=basic*((parseFloat($('#salPFEmp').val())||0)/100);
    var esiEmp=gross*((parseFloat($('#salESIEmp').val())||0)/100);
    var pt=parseFloat($('#salPT').val())||0;
    var tds=gross*((parseFloat($('#salTDS').val())||0)/100);
    var deductions=pfEmp+esiEmp+pt+tds;
    var net=gross-deductions;

    $('#salGrossDisplay').text(fmt(gross));
    $('#salDeductDisplay').text(fmt(deductions));
    $('#salNetDisplay').text(fmt(net));
  }

  function openSalaryModal(id){
    $('#salaryId').val('');
    fillStaffSelect('#salStaff');
    $('#salBasic,#salHRA,#salDA,#salTA,#salMedical,#salSpecial').val(0);
    $('#salPFEmp').val(12); $('#salPFEr').val(12);
    $('#salESIEmp').val(0.75); $('#salESIEr').val(3.25);
    $('#salPT').val(200); $('#salTDS').val(0);
    $('#modalSalaryTitle').text('Add Salary Structure');
    calcSalary();
    openModal('modalSalary');
  }

  function editSalary(id){
    if(!_salaryCache[id]){ toast('Salary not found in cache — refreshing','warning'); loadSalaryStructures(); return; }
    var s=_salaryCache[id];
    $('#salaryId').val(id);
    fillStaffSelect('#salStaff', s.staff_id);
    $('#salBasic').val(s.basic); $('#salHRA').val(s.hra); $('#salDA').val(s.da);
    $('#salTA').val(s.ta); $('#salMedical').val(s.medical); $('#salSpecial').val(s.special_allowance||s.other_allowances||'');
    $('#salPFEmp').val(s.pf_employee); $('#salPFEr').val(s.pf_employer);
    $('#salESIEmp').val(s.esi_employee); $('#salESIEr').val(s.esi_employer);
    $('#salPT').val(s.professional_tax); $('#salTDS').val(s.tds);
    $('#modalSalaryTitle').text('Edit Salary Structure');
    calcSalary();
    openModal('modalSalary');
  }

  function saveSalary(){
    var staff=$('#salStaff').val();
    if(!staff){ toast('Staff is required','error'); return; }
    post('hr/save_salary_structure', {
      id:$('#salaryId').val(), staff_id:staff,
      basic:$('#salBasic').val(), hra:$('#salHRA').val(), da:$('#salDA').val(),
      ta:$('#salTA').val(), medical:$('#salMedical').val(), special_allowance:$('#salSpecial').val(),
      pf_employee:$('#salPFEmp').val(), pf_employer:$('#salPFEr').val(),
      esi_employee:$('#salESIEmp').val(), esi_employer:$('#salESIEr').val(),
      professional_tax:$('#salPT').val(), tds:$('#salTDS').val()
    }).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalSalary'); loadSalaryStructures(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteSalary(id){
    if(!confirm('Delete this salary structure?')) return;
    post('hr/delete_salary_structure', {id:id}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadSalaryStructures(); }
      else toast(r.message||'Failed','error');
    });
  }

  function loadPayrollRuns(){
    getJSON('hr/get_payroll_runs').then(function(r){
      var runs=(r&&r.payroll_runs)?r.payroll_runs:(r&&r.data)?r.data:{};
      var $tb=$('#tblPayrollRuns tbody');
      var keys=Object.keys(runs);
      if(!keys.length){ $tb.html('<tr><td colspan="9" class="hr-empty"><i class="fa fa-inbox"></i> No payroll runs yet.</td></tr>'); return; }
      var h='', i=0;
      var months=['','January','February','March','April','May','June','July','August','September','October','November','December'];
      $.each(runs, function(k,pr){
        i++;
        var rid = pr.id || k;
        var st=(pr.status||'draft').toLowerCase();
        h+='<tr><td class="hr-num">'+i+'</td><td class="hr-num">'+esc(rid)+'</td>';
        h+='<td>'+(pr.month||'')+'</td><td class="hr-num">'+(pr.year||'')+'</td>';
        h+='<td class="hr-num">'+(pr.staff_count||0)+'</td>';
        h+='<td class="hr-num">'+fmt(pr.total_gross)+'</td><td class="hr-num"><strong style="color:var(--gold)">'+fmt(pr.total_net)+'</strong></td>';
        h+='<td>'+badgeHtml(pr.status)+'</td>';
        h+='<td>';
        h+='<button class="hr-act-btn" onclick="HR.viewPayslips(\''+esc(rid)+'\')" title="View Payslips"><i class="fa fa-file-text-o"></i></button> ';
        if(st==='draft') h+='<button class="hr-act-btn" onclick="HR.finalizeRun(\''+esc(rid)+'\')" title="Finalize"><i class="fa fa-lock"></i></button> ';
        if(st==='finalized') h+='<button class="hr-act-btn" onclick="HR.markPaid(\''+esc(rid)+'\')" title="Mark Paid"><i class="fa fa-check-circle"></i></button> ';
        h+='</td></tr>';
      });
      $tb.html(h);
    });
  }

  function openGeneratePayroll(){
    $('#genMonth').val(['January','February','March','April','May','June','July','August','September','October','November','December'][new Date().getMonth()]);
    $('#genYear').val(new Date().getFullYear());
    openModal('modalGenPayroll');
  }

  function generatePayroll(){
    var m=$('#genMonth').val(), y=$('#genYear').val();
    if(!m||!y){ toast('Month and year are required','error'); return; }
    // Pre-flight check before generating
    getJSON('hr/preflight_payroll?month='+encodeURIComponent(m)+'&year='+encodeURIComponent(y)).then(function(r){
      if(!r||!r.status){ toast(r.message||'Pre-flight check failed','error'); return; }
      var warnings = r.warnings || [];
      if(warnings.length > 0){
        var msg = 'Payroll pre-flight warnings:\n\n';
        for(var i=0;i<warnings.length;i++) msg += '• ' + warnings[i] + '\n';
        msg += '\nStaff covered: ' + (r.staff_covered||0) + '/' + (r.staff_total||0);
        msg += '\n\nProceed with payroll generation?';
        if(!confirm(msg)) return;
      }
      post('hr/generate_payroll', {month:m, year:y}).then(function(r2){
        if(r2&&r2.status){ toast(r2.message||'Generated','success'); closeModal('modalGenPayroll'); loadPayrollRuns(); }
        else toast(r2.message||'Failed','error');
      });
    });
  }

  function finalizeRun(id){
    if(!confirm('Finalize this payroll run? Draft payslips will be locked.')) return;
    post('hr/finalize_payroll', {run_id:id}).then(function(r){
      if(r&&r.status){ toast('Finalized','success'); loadPayrollRuns(); }
      else toast(r.message||'Failed','error');
    });
  }
  function markPaid(id){
    if(!confirm('Mark this payroll run as paid? This will create accounting journal entries.')) return;
    post('hr/mark_payroll_paid', {run_id:id, payment_mode:'Bank'}).then(function(r){
      if(r&&r.status){ toast('Marked as paid','success'); loadPayrollRuns(); }
      else toast(r.message||'Failed','error');
    });
  }

  function viewPayslips(runId){
    currentRunId = runId;
    // Switch to payslips sub-tab
    var $panel=$('#panelPayroll');
    $panel.find('.hr-sub-tab').removeClass('active').filter('[data-sub=payslips]').addClass('active');
    $panel.find('.hr-sub-panel').removeClass('active');
    $('#subPayslips').addClass('active');

    $('#payslipRunInfo').text('Run: '+runId).show();
    $('#payslipSelectMsg').hide();
    $('#payslipTableWrap').show();

    getJSON('hr/get_payroll_slips?run_id='+encodeURIComponent(runId)).then(function(r){
      var slips=(r&&r.slips)?r.slips:(r&&r.data)?r.data:{};
      var $tb=$('#tblPayslips tbody');
      var keys=Object.keys(slips);
      if(!keys.length){ $tb.html('<tr><td colspan="11" class="hr-empty"><i class="fa fa-inbox"></i> No payslips in this run.</td></tr>'); return; }
      var h='', i=0;
      $.each(slips, function(k,p){
        i++;
        var allowances=(parseFloat(p.hra)||0)+(parseFloat(p.da)||0)+(parseFloat(p.ta)||0)+(parseFloat(p.medical)||0)+(parseFloat(p.other_allowances)||0);
        h+='<tr class="hr-payslip-row" data-id="'+esc(k)+'" style="cursor:pointer">';
        h+='<td class="hr-num">'+i+'</td><td>'+esc(p.staff_name||staffName(p.staff_id||k))+'</td>';
        h+='<td class="hr-num">'+fmt(p.basic)+'</td><td class="hr-num">'+fmt(allowances)+'</td>';
        h+='<td class="hr-num"><strong>'+fmt(p.gross)+'</strong></td><td class="hr-num">'+fmt(p.total_deductions)+'</td>';
        h+='<td class="hr-num"><strong style="color:var(--gold)">'+fmt(p.net_pay)+'</strong></td>';
        h+='<td class="hr-num">'+(p.days_worked||'-')+'</td><td class="hr-num">'+(p.days_absent||0)+'</td>';
        h+='<td class="hr-num">'+(p.lwp_days||0)+'</td>';
        h+='<td><i class="fa fa-chevron-down" style="color:var(--t3)"></i></td></tr>';
        // Detail row
        h+='<tr class="hr-payslip-detail" id="detail_'+esc(k)+'">';
        h+='<td colspan="11"><div class="hr-payslip-breakdown"><dl style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px 16px;width:100%;margin:0">';
        h+='<div><dt>Basic</dt><dd>'+fmt(p.basic)+'</dd></div>';
        h+='<div><dt>HRA</dt><dd>'+fmt(p.hra)+'</dd></div>';
        h+='<div><dt>DA</dt><dd>'+fmt(p.da)+'</dd></div>';
        h+='<div><dt>TA</dt><dd>'+fmt(p.ta)+'</dd></div>';
        h+='<div><dt>Medical</dt><dd>'+fmt(p.medical)+'</dd></div>';
        h+='<div><dt>Other Allowances</dt><dd>'+fmt(p.other_allowances)+'</dd></div>';
        h+='<div><dt>PF (Employee)</dt><dd>'+fmt(p.pf_employee)+'</dd></div>';
        h+='<div><dt>ESI (Employee)</dt><dd>'+fmt(p.esi_employee)+'</dd></div>';
        h+='<div><dt>Prof. Tax</dt><dd>'+fmt(p.professional_tax)+'</dd></div>';
        h+='<div><dt>TDS</dt><dd>'+fmt(p.tds)+'</dd></div>';
        if(parseFloat(p.lwp_deduction)>0){
          h+='<div style="grid-column:span 3;border-top:1px dashed var(--border);padding-top:6px;margin-top:4px">';
          h+='<dt style="color:var(--amber)">LWP Deduction ('+parseInt(p.lwp_days||0)+' days)</dt>';
          h+='<dd style="color:var(--amber);font-weight:700">-'+fmt(p.lwp_deduction)+'</dd></div>';
        }
        h+='</dl></div></td></tr>';
      });
      $tb.html(h);

      // Toggle detail rows
      $tb.find('.hr-payslip-row').on('click', function(){
        var did='detail_'+$(this).data('id');
        var $d=$('#'+did);
        $d.toggleClass('open');
        $(this).find('.fa-chevron-down,.fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
      });
    });
  }

  /* ================================================================
     APPRAISALS
  ================================================================ */
  function loadAppraisals(){
    var params='?period='+encodeURIComponent($('#filterAppraisalPeriod').val()||'')+'&staff_id='+($('#filterAppraisalStaff').val()||'');
    getJSON('hr/get_appraisals'+params).then(function(r){
      var appraisals=toMap((r&&r.appraisals)?r.appraisals:(r&&r.data)?r.data:{});
      _appraisalCache = appraisals;
      var $tb=$('#tblAppraisals tbody');
      var keys=Object.keys(appraisals);

      // Populate period filter
      var periods={};
      $.each(appraisals, function(k,a){ if(a.period) periods[a.period]=1; });
      var $pf=$('#filterAppraisalPeriod');
      var curVal=$pf.val();
      $pf.html('<option value="">All</option>');
      $.each(Object.keys(periods).sort(), function(i,p){ $pf.append('<option value="'+esc(p)+'">'+esc(p)+'</option>'); });
      if(curVal) $pf.val(curVal);

      if(!keys.length){ $tb.html('<tr><td colspan="8" class="hr-empty"><i class="fa fa-inbox"></i> No appraisals found.</td></tr>'); return; }
      var h='', i=0;
      $.each(appraisals, function(k,a){
        i++;
        h+='<tr><td class="hr-num">'+i+'</td><td>'+esc(staffName(a.staff_id))+'</td><td>'+esc(a.period)+'</td>';
        h+='<td>'+esc(staffName(a.reviewer_id))+'</td>';
        h+='<td>'+starsHtml(Math.round(parseFloat(a.overall_rating)||0))+'<span class="hr-num" style="margin-left:6px">'+parseFloat(a.overall_rating||0).toFixed(1)+'</span></td>';
        h+='<td>'+badgeHtml(a.recommendation||'none')+'</td><td>'+esc(a.date||'-')+'</td>';
        h+='<td><button class="hr-act-btn" onclick="HR.editAppraisal(\''+esc(k)+'\')"><i class="fa fa-pencil"></i></button> ';
        h+='<button class="hr-act-btn danger" onclick="HR.deleteAppraisal(\''+esc(k)+'\')"><i class="fa fa-trash"></i></button></td></tr>';
      });
      $tb.html(h);
    });
  }

  function calcAppraisal(){
    var t=parseFloat($('#apTeaching').val())||0;
    var p=parseFloat($('#apPunctuality').val())||0;
    var b=parseFloat($('#apBehavior').val())||0;
    var n=parseFloat($('#apInnovation').val())||0;
    var w=parseFloat($('#apTeamwork').val())||0;
    var avg=((t+p+b+n+w)/5).toFixed(1);
    $('#apOverall').text(avg);
  }

  function openAppraisalModal(id){
    $('#appraisalId').val('');
    fillStaffSelect('#apStaff');
    fillStaffSelect('#apReviewer');
    $('#apPeriod').val('');
    $('#apTeaching,#apPunctuality,#apBehavior,#apInnovation,#apTeamwork').val(5);
    $('#apRecommendation').val('none');
    $('#apStrengths,#apImprovement,#apGoals').val('');
    calcAppraisal();
    $('#modalAppraisalTitle').text('New Appraisal');
    openModal('modalAppraisal');
  }

  function editAppraisal(id){
    if(!_appraisalCache[id]){ toast('Appraisal not found in cache — refreshing','warning'); loadAppraisals(); return; }
    var a=_appraisalCache[id];
    $('#appraisalId').val(id);
    fillStaffSelect('#apStaff', a.staff_id);
    fillStaffSelect('#apReviewer', a.reviewer_id);
    $('#apPeriod').val(a.period);
    $('#apTeaching').val(a.teaching||a.teaching_quality||5);
    $('#apPunctuality').val(a.punctuality||5);
    $('#apBehavior').val(a.behavior||a.student_feedback||5);
    $('#apInnovation').val(a.innovation||a.initiative||5);
    $('#apTeamwork').val(a.teamwork||5);
    $('#apRecommendation').val(a.recommendation||'none');
    $('#apStrengths').val(a.strengths||a.comments||'');
    $('#apImprovement').val(a.areas_of_improvement||'');
    $('#apGoals').val(a.goals||'');
    calcAppraisal();
    $('#modalAppraisalTitle').text('Edit Appraisal');
    openModal('modalAppraisal');
  }

  function saveAppraisal(){
    var staff=$('#apStaff').val(), period=$('#apPeriod').val().trim();
    if(!staff||!period){ toast('Staff and period are required','error'); return; }
    post('hr/save_appraisal', {
      id:$('#appraisalId').val(), staff_id:staff, period:period,
      reviewer_id:$('#apReviewer').val(),
      teaching:$('#apTeaching').val(), punctuality:$('#apPunctuality').val(),
      behavior:$('#apBehavior').val(), innovation:$('#apInnovation').val(), teamwork:$('#apTeamwork').val(),
      overall_rating:$('#apOverall').text(),
      strengths:$('#apStrengths').val().trim(), areas_of_improvement:$('#apImprovement').val().trim(),
      goals:$('#apGoals').val().trim(), recommendation:$('#apRecommendation').val()
    }).then(function(r){
      if(r&&r.status){ toast(r.message||'Saved','success'); closeModal('modalAppraisal'); loadAppraisals(); }
      else toast(r.message||'Failed','error');
    });
  }

  function deleteAppraisal(id){
    if(!confirm('Delete this appraisal?')) return;
    post('hr/delete_appraisal', {id:id}).then(function(r){
      if(r&&r.status){ toast('Deleted','success'); loadAppraisals(); }
      else toast(r.message||'Failed','error');
    });
  }

  /* ── Public API ─────────────────────────────────────────── */
  window.HR = {
    closeModal:       closeModal,
    openDeptModal:    openDeptModal,
    editDept:         function(id){ openDeptModal(id); },
    saveDept:         saveDept,
    deleteDept:       deleteDept,
    openJobModal:     openJobModal,
    editJob:          editJob,
    saveJob:          saveJob,
    deleteJob:        deleteJob,
    openApplicantModal: openApplicantModal,
    editApplicant:    editApplicant,
    saveApplicant:    saveApplicant,
    deleteApplicant:  deleteApplicant,
    loadJobs:         loadJobs,
    openLeaveTypeModal: openLeaveTypeModal,
    editLeaveType:    function(id){ openLeaveTypeModal(id); },
    saveLeaveType:    saveLeaveType,
    deleteLeaveType:  deleteLeaveType,
    openLeaveRequestModal: openLeaveRequestModal,
    saveLeaveRequest: saveLeaveRequest,
    loadLeaveRequests: loadLeaveRequests,
    approveLeave:     approveLeave,
    rejectLeave:      rejectLeave,
    confirmLeaveAction: confirmLeaveAction,
    initBalances:     initBalances,
    openSalaryModal:  openSalaryModal,
    editSalary:       editSalary,
    saveSalary:       saveSalary,
    deleteSalary:     deleteSalary,
    calcSalary:       calcSalary,
    openGeneratePayroll: openGeneratePayroll,
    generatePayroll:  generatePayroll,
    finalizeRun:      finalizeRun,
    markPaid:         markPaid,
    viewPayslips:     viewPayslips,
    openAppraisalModal: function(id){ openAppraisalModal(id); },
    editAppraisal:    editAppraisal,
    saveAppraisal:    saveAppraisal,
    deleteAppraisal:  deleteAppraisal,
    calcAppraisal:    calcAppraisal,
    loadAppraisals:   loadAppraisals
  };

  /* ── Boot ───────────────────────────────────────────────── */
  init();

})();
}); /* end DOMContentLoaded */
</script>
