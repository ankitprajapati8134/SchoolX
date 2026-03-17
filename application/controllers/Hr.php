<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Hr Controller — Human Resources & Staff Management
 *
 * Modules:
 *   - Dashboard (overview stats)
 *   - Departments (CRUD)
 *   - Recruitment (jobs + applicants)
 *   - Leave Management (types, balances, requests, approval)
 *   - Salary Structures & Payroll (generate, finalize, pay + accounting)
 *   - Appraisals (CRUD + submit/review)
 *   - Utility (staff list for dropdowns)
 *
 * Firebase schema under Schools/{school}/HR/ (year-independent)
 * and Schools/{school}/{session}/HR/Payroll/ (year-scoped).
 *
 * Extends MY_Controller which provides:
 *   $this->school_name, $this->school_id, $this->session_year,
 *   $this->admin_id, $this->admin_name, $this->admin_role,
 *   $this->firebase, safe_path_segment(), json_success(), json_error()
 */
class Hr extends MY_Controller
{
    /** Roles for payroll and salary management */
    private const ADMIN_ROLES = ['Admin', 'Principal'];

    /** Roles for HR operations (departments, recruitment, leave mgmt) */
    private const HR_ROLES    = ['Admin', 'Principal'];

    /** Roles that may view HR data */
    private const VIEW_ROLES  = ['Admin', 'Principal', 'Teacher'];

    public function __construct()
    {
        parent::__construct();
        require_permission('HR');
    }

    // ====================================================================
    //  PATH HELPERS
    // ====================================================================

    private function _hr()
    {
        return "Schools/{$this->school_name}/HR";
    }

    private function _dept($id = '')
    {
        return $this->_hr() . '/Departments' . ($id ? "/{$id}" : '');
    }

    private function _jobs($id = '')
    {
        return $this->_hr() . '/Recruitment/Jobs' . ($id ? "/{$id}" : '');
    }

    private function _applicants($id = '')
    {
        return $this->_hr() . '/Recruitment/Applicants' . ($id ? "/{$id}" : '');
    }

    private function _leave_types($id = '')
    {
        return $this->_hr() . '/Leaves/Types' . ($id ? "/{$id}" : '');
    }

    private function _leave_bal($year = '', $staff = '', $type = '')
    {
        $p = $this->_hr() . '/Leaves/Balances';
        if ($year)  $p .= "/{$year}";
        if ($staff) $p .= "/{$staff}";
        if ($type)  $p .= "/{$type}";
        return $p;
    }

    private function _leave_req($id = '')
    {
        return $this->_hr() . '/Leaves/Requests' . ($id ? "/{$id}" : '');
    }

    private function _salary($staff = '')
    {
        return $this->_hr() . '/Salary_Structures' . ($staff ? "/{$staff}" : '');
    }

    private function _appraisals($id = '')
    {
        return $this->_hr() . '/Appraisals' . ($id ? "/{$id}" : '');
    }

    private function _payroll_runs($id = '')
    {
        return "Schools/{$this->school_name}/{$this->session_year}/HR/Payroll/Runs" . ($id ? "/{$id}" : '');
    }

    private function _payroll_slips($runId = '', $staff = '')
    {
        $p = "Schools/{$this->school_name}/{$this->session_year}/HR/Payroll/Slips";
        if ($runId) $p .= "/{$runId}";
        if ($staff) $p .= "/{$staff}";
        return $p;
    }

    private function _counters($type = '')
    {
        return $this->_hr() . '/Counters' . ($type ? "/{$type}" : '');
    }

    // ====================================================================
    //  ID GENERATOR
    // ====================================================================

    /**
     * Generate next sequential ID.
     *
     * @param string $prefix  e.g. 'DEPT', 'JOB', 'APP', 'LT', 'LR', 'PR', 'APR'
     * @param string $type    Counter key in Firebase
     * @param int    $pad     Pad length for numeric portion (default 4)
     * @return string         e.g. 'DEPT0001'
     */
    private function _next_id(string $prefix, string $type, int $pad = 4): string
    {
        $counterPath = $this->_counters($type);
        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $current = $this->firebase->get($counterPath);
            $next = is_numeric($current) ? (int) $current + 1 : 1;
            $this->firebase->set($counterPath, $next);

            // Verify-after-write: re-read to confirm we own this value
            $verify = $this->firebase->get($counterPath);
            if ((int) $verify === $next) {
                return $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
            }
            // Another writer overwrote — retry with their higher value
            usleep(50000 * $attempt); // 50ms, 100ms, 150ms backoff
        }
        // Fallback: use timestamp-based unique suffix to guarantee uniqueness
        $fallback = (int) ($this->firebase->get($counterPath) ?? 0) + 1;
        $this->firebase->set($counterPath, $fallback);
        return $prefix . str_pad($fallback, $pad, '0', STR_PAD_LEFT) . '_' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    // ====================================================================
    //  PAYROLL AUDIT LOG
    // ====================================================================

    /**
     * Write a payroll audit log entry to System/Logs/Payroll/.
     *
     * @param string $action  e.g. 'generated', 'finalized', 'paid', 'deleted'
     * @param string $runId   Payroll run ID (e.g. PR0001)
     * @param array  $extra   Optional additional data to store
     */
    private function _log_payroll(string $action, string $runId, array $extra = []): void
    {
        $logData = array_merge([
            'school_id'      => $this->school_name,
            'admin_id'       => $this->admin_id ?? '',
            'admin_name'     => $this->admin_name ?? '',
            'action'         => $action,
            'payroll_run_id' => $runId,
            'timestamp'      => date('c'),
            'ip'             => $this->input->ip_address(),
        ], $extra);

        $this->firebase->push('System/Logs/Payroll', $logData);
    }

    // ====================================================================
    //  ACCOUNTING INTEGRATION HELPERS
    //  Matches Accounting.php path structure exactly:
    //    Accounts:  Schools/{school}/Accounts/ChartOfAccounts/{code}
    //    Ledger:    Schools/{school}/{year}/Accounts/Ledger/{entryId}
    //    Index:     Schools/{school}/{year}/Accounts/Ledger_index/by_date|by_account
    //    Balances:  Schools/{school}/{year}/Accounts/Closing_balances/{code}
    //    Counter:   Schools/{school}/{year}/Accounts/Voucher_counters/{type}
    // ====================================================================

    /**
     * Validate that accounting accounts exist and are active.
     * Sends json_error and exits if any are missing.
     */
    private function _validate_accounts(array $codes): void
    {
        $coaBase = "Schools/{$this->school_name}/Accounts/ChartOfAccounts";
        $coa = $this->firebase->get($coaBase);
        if (!is_array($coa)) $coa = [];

        $missing = [];
        foreach ($codes as $code) {
            $acct = $coa[$code] ?? null;
            if (!is_array($acct) || ($acct['status'] ?? '') !== 'active') {
                $missing[] = $code;
            }
        }
        if (!empty($missing)) {
            $this->json_error(
                'Missing or inactive accounting accounts: ' . implode(', ', $missing)
                . '. Please set them up in Accounting first.'
            );
        }
    }

    /**
     * Create a journal entry compatible with the Accounting module.
     *
     * @param string $narration  Human-readable description
     * @param array  $lines      Array of [account_code, dr, cr] entries
     * @param string $sourceRef  Reference ID (e.g. payroll run ID)
     * @return string            The generated entry ID
     */
    private function _create_acct_journal(string $narration, array $lines, string $sourceRef = ''): string
    {
        $bp = "Schools/{$this->school_name}/{$this->session_year}";

        // Generate voucher number via the Accounting counter
        $counterPath = "{$bp}/Accounts/Voucher_counters/Journal";
        $maxAttempts = 3;
        $newSeq = 0;
        $resolved = false;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $currentSeq = (int) ($this->firebase->get($counterPath) ?? 0);
            $newSeq     = $currentSeq + 1;
            $this->firebase->set($counterPath, $newSeq);

            // Verify-after-write: re-read to confirm we own this value
            $verify = $this->firebase->get($counterPath);
            if ((int) $verify === $newSeq) {
                $resolved = true;
                break;
            }
            usleep(50000 * $attempt);
        }
        if (!$resolved) {
            $newSeq = (int) ($this->firebase->get($counterPath) ?? 0) + 1;
            $this->firebase->set($counterPath, $newSeq);
        }
        $voucherNo = 'JV-' . str_pad($newSeq, 6, '0', STR_PAD_LEFT);

        // Generate entry ID matching Accounting format
        $entryId = 'JE_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

        // Calculate totals and affected accounts
        $totalDr = 0;
        $totalCr = 0;
        $affected = [];
        foreach ($lines as &$line) {
            $dr = round((float) ($line['dr'] ?? 0), 2);
            $cr = round((float) ($line['cr'] ?? 0), 2);
            $line['dr'] = $dr;
            $line['cr'] = $cr;
            $totalDr += $dr;
            $totalCr += $cr;
            $ac = $line['account_code'] ?? '';
            if ($ac !== '') {
                $affected[$ac] = [
                    'dr' => ($affected[$ac]['dr'] ?? 0) + $dr,
                    'cr' => ($affected[$ac]['cr'] ?? 0) + $cr,
                ];
            }
        }
        unset($line);

        $entry = [
            'date'         => date('Y-m-d'),
            'voucher_no'   => $voucherNo,
            'voucher_type' => 'Journal',
            'narration'    => $narration,
            'lines'        => array_values($lines),
            'total_dr'     => round($totalDr, 2),
            'total_cr'     => round($totalCr, 2),
            'source'       => 'HR_Payroll',
            'source_ref'   => $sourceRef ?: null,
            'is_finalized' => false,
            'status'       => 'active',
            'created_by'   => $this->admin_id ?? '',
            'created_at'   => date('c'),
        ];

        // Write ledger entry
        $this->firebase->set("{$bp}/Accounts/Ledger/{$entryId}", $entry);

        // Write indices (by_date and by_account)
        $safeDate = date('Y-m-d');
        $this->firebase->set("{$bp}/Accounts/Ledger_index/by_date/{$safeDate}/{$entryId}", true);
        foreach (array_keys($affected) as $acCode) {
            $this->firebase->set("{$bp}/Accounts/Ledger_index/by_account/{$acCode}/{$entryId}", true);
        }

        // Update closing balances cache
        foreach ($affected as $code => $amounts) {
            $balPath = "{$bp}/Accounts/Closing_balances/{$code}";
            $current = $this->firebase->get($balPath);
            $pDr = (float) ($current['period_dr'] ?? 0) + $amounts['dr'];
            $pCr = (float) ($current['period_cr'] ?? 0) + $amounts['cr'];
            $this->firebase->set($balPath, [
                'period_dr'     => round($pDr, 2),
                'period_cr'     => round($pCr, 2),
                'last_computed' => date('c'),
            ]);
        }

        return $entryId;
    }

    /**
     * Soft-delete a journal entry, reverse balances, and clean indices.
     * Matches Accounting.php delete_journal_entry() behavior.
     */
    private function _delete_acct_journal(string $entryId): void
    {
        $bp    = "Schools/{$this->school_name}/{$this->session_year}";
        $entry = $this->firebase->get("{$bp}/Accounts/Ledger/{$entryId}");
        if (!is_array($entry) || ($entry['status'] ?? '') === 'deleted') {
            return;
        }

        // Collect affected accounts from lines
        $affected = [];
        foreach ($entry['lines'] ?? [] as $line) {
            $ac = $line['account_code'] ?? '';
            if ($ac === '') continue;
            $affected[$ac] = [
                'dr' => ($affected[$ac]['dr'] ?? 0) + (float) ($line['dr'] ?? 0),
                'cr' => ($affected[$ac]['cr'] ?? 0) + (float) ($line['cr'] ?? 0),
            ];
        }

        // Reverse closing balances
        foreach ($affected as $code => $amounts) {
            $balPath = "{$bp}/Accounts/Closing_balances/{$code}";
            $current = $this->firebase->get($balPath);
            $pDr = (float) ($current['period_dr'] ?? 0) - $amounts['dr'];
            $pCr = (float) ($current['period_cr'] ?? 0) - $amounts['cr'];
            $this->firebase->set($balPath, [
                'period_dr'     => round($pDr, 2),
                'period_cr'     => round($pCr, 2),
                'last_computed' => date('c'),
            ]);
        }

        // Remove indices
        $date = $entry['date'] ?? '';
        if ($date !== '') {
            $this->firebase->delete("{$bp}/Accounts/Ledger_index/by_date/{$date}", $entryId);
        }
        foreach (array_keys($affected) as $acCode) {
            $this->firebase->delete("{$bp}/Accounts/Ledger_index/by_account/{$acCode}", $entryId);
        }

        // Soft-delete the entry
        $this->firebase->update("{$bp}/Accounts/Ledger/{$entryId}", [
            'status'     => 'deleted',
            'deleted_by' => $this->admin_id ?? '',
            'deleted_at' => date('c'),
        ]);
    }

    // ====================================================================
    //  PAGE ROUTE
    // ====================================================================

    /**
     * Single page entry point — loads view with active tab.
     */
    public function index()
    {
        $this->_require_role(self::VIEW_ROLES, 'hr_view');

        $tab = $this->uri->segment(2, 'dashboard');
        $allowed = ['dashboard', 'departments', 'recruitment', 'leaves', 'payroll', 'appraisals'];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'dashboard';
        }

        $data['active_tab'] = $tab;
        $this->load->view('include/header');
        $this->load->view('hr/index', $data);
        $this->load->view('include/footer');
    }

    // ====================================================================
    //  DASHBOARD
    // ====================================================================

    /**
     * GET — Returns dashboard statistics.
     */
    public function get_dashboard()
    {
        $this->_require_role(self::VIEW_ROLES, 'hr_dashboard');

        // Staff count from session roster
        $roster = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        $staffCount = is_array($roster) ? count($roster) : 0;

        // Department count
        $depts = $this->firebase->get($this->_dept());
        $deptCount = is_array($depts) ? count($depts) : 0;

        // Open jobs & applicant counts
        $jobs = $this->firebase->get($this->_jobs());
        $openJobs = 0;
        if (is_array($jobs)) {
            foreach ($jobs as $j) {
                if (isset($j['status']) && $j['status'] === 'Open') {
                    $openJobs++;
                }
            }
        }

        $applicants = $this->firebase->get($this->_applicants());
        $totalApplicants = is_array($applicants) ? count($applicants) : 0;

        // Pending leave requests
        $leaveReqs = $this->firebase->get($this->_leave_req());
        $pendingLeaves = 0;
        if (is_array($leaveReqs)) {
            foreach ($leaveReqs as $lr) {
                if (isset($lr['status']) && $lr['status'] === 'Pending') {
                    $pendingLeaves++;
                }
            }
        }

        // Payroll runs this session
        $runs = $this->firebase->get($this->_payroll_runs());
        $payrollRuns = is_array($runs) ? count($runs) : 0;
        $lastPayroll = null;
        if (is_array($runs)) {
            $latest = null;
            foreach ($runs as $rid => $r) {
                if (!$latest || (isset($r['created_at']) && $r['created_at'] > ($latest['created_at'] ?? ''))) {
                    $latest = $r;
                    $latest['id'] = $rid;
                }
            }
            if ($latest) {
                $lastPayroll = [
                    'month'  => $latest['month'] ?? '',
                    'year'   => $latest['year'] ?? '',
                    'status' => $latest['status'] ?? '',
                ];
            }
        }

        // Recent leave requests (last 5)
        $recentLeaves = [];
        if (is_array($leaveReqs)) {
            uasort($leaveReqs, function ($a, $b) {
                return strcmp($b['applied_on'] ?? '', $a['applied_on'] ?? '');
            });
            $i = 0;
            foreach ($leaveReqs as $id => $lr) {
                if ($i >= 5) break;
                $recentLeaves[] = [
                    'id'         => $id,
                    'staff_name' => $lr['staff_name'] ?? '',
                    'type_name'  => $lr['type_name'] ?? '',
                    'status'     => $lr['status'] ?? '',
                    'from_date'  => $lr['from_date'] ?? '',
                    'to_date'    => $lr['to_date'] ?? '',
                ];
                $i++;
            }
        }

        // Appraisal count
        $appraisals = $this->firebase->get($this->_appraisals());
        $appraisalCount = is_array($appraisals) ? count($appraisals) : 0;

        $this->json_success([
            'staff_count'      => $staffCount,
            'dept_count'       => $deptCount,
            'open_jobs'        => $openJobs,
            'total_applicants' => $totalApplicants,
            'pending_leaves'   => $pendingLeaves,
            'payroll_runs'     => $payrollRuns,
            'last_payroll'     => $lastPayroll,
            'appraisal_count'  => $appraisalCount,
            'recent_leaves'    => $recentLeaves,
        ]);
    }

    // ====================================================================
    //  DEPARTMENTS
    // ====================================================================

    /**
     * GET — List all departments.
     */
    public function get_departments()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_departments');

        $depts = $this->firebase->get($this->_dept());
        $list = [];
        if (is_array($depts)) {
            foreach ($depts as $id => $d) {
                $d['id'] = $id;
                $list[] = $d;
            }
        }

        $this->json_success(['departments' => $list]);
    }

    /**
     * POST — Create or update a department.
     * Params: id (optional for update), name, head_staff_id, description, status
     */
    public function save_department()
    {
        $this->_require_role(self::HR_ROLES, 'save_department');

        $id          = trim($this->input->post('id') ?? '');
        $name        = trim($this->input->post('name') ?? '');
        $headStaffId = trim($this->input->post('head_staff_id') ?? '');
        $description = trim($this->input->post('description') ?? '');
        $status      = trim($this->input->post('status') ?? 'Active');

        if ($name === '') {
            $this->json_error('Department name is required.');
        }
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $status = 'Active';
        }

        $now = date('c');
        $isNew = ($id === '');

        if ($isNew) {
            $id = $this->_next_id('DEPT', 'Department');
        }

        // Check for duplicate name (excluding self when editing)
        $existing = $this->firebase->get($this->_dept());
        if (is_array($existing)) {
            foreach ($existing as $eid => $ed) {
                if ($eid !== $id && isset($ed['name']) && strtolower($ed['name']) === strtolower($name)) {
                    $this->json_error('A department with this name already exists.');
                }
            }
        }

        $data = [
            'name'          => $name,
            'head_staff_id' => $headStaffId,
            'description'   => $description,
            'status'        => $status,
            'updated_at'    => $now,
        ];
        if ($isNew) {
            $data['created_at'] = $now;
        }

        $this->firebase->set($this->_dept($id), $data);
        $this->json_success(['id' => $id, 'message' => $isNew ? 'Department created.' : 'Department updated.']);
    }

    /**
     * POST — Delete a department.
     * Params: id
     */
    public function delete_department()
    {
        $this->_require_role(self::HR_ROLES, 'delete_department');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        // Check if department exists
        $dept = $this->firebase->get($this->_dept($id));
        if (!is_array($dept)) {
            $this->json_error('Department not found.');
        }
        $deptName = $dept['name'] ?? '';

        // Check if staff are assigned to this department
        $staffProfiles = $this->firebase->get("Users/Teachers/{$this->school_name}");
        if (is_array($staffProfiles)) {
            foreach ($staffProfiles as $sid => $sp) {
                if (isset($sp['Department']) && $sp['Department'] === $deptName) {
                    $this->json_error('Cannot delete: staff members are assigned to this department. Reassign them first.');
                }
            }
        }

        // Check if any open jobs reference this department
        $jobs = $this->firebase->get($this->_jobs());
        if (is_array($jobs)) {
            foreach ($jobs as $jid => $j) {
                if (isset($j['department']) && $j['department'] === $deptName && isset($j['status']) && $j['status'] === 'Open') {
                    $this->json_error('Cannot delete: there are open job postings in this department.');
                }
            }
        }

        $this->firebase->delete($this->_dept($id));
        $this->json_success(['message' => 'Department deleted.']);
    }

    // ====================================================================
    //  RECRUITMENT — JOBS
    // ====================================================================

    /**
     * GET — List all job postings. Optional filter: ?status=Open
     */
    public function get_jobs()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_jobs');

        $filterStatus = trim($this->input->get('status') ?? '');
        $jobs = $this->firebase->get($this->_jobs());
        $list = [];
        if (is_array($jobs)) {
            foreach ($jobs as $id => $j) {
                if ($filterStatus !== '' && isset($j['status']) && $j['status'] !== $filterStatus) {
                    continue;
                }
                $j['id'] = $id;
                $j['applicant_count'] = 0;
                $list[] = $j;
            }
        }

        // Count applicants per job
        $applicants = $this->firebase->get($this->_applicants());
        if (is_array($applicants)) {
            $countsByJob = [];
            foreach ($applicants as $a) {
                $jid = $a['job_id'] ?? '';
                if ($jid !== '') {
                    $countsByJob[$jid] = ($countsByJob[$jid] ?? 0) + 1;
                }
            }
            foreach ($list as &$j) {
                $j['applicant_count'] = $countsByJob[$j['id']] ?? 0;
            }
            unset($j);
        }

        $this->json_success(['jobs' => $list]);
    }

    /**
     * POST — Create or update a job posting.
     */
    public function save_job()
    {
        $this->_require_role(self::HR_ROLES, 'save_job');

        $id                  = trim($this->input->post('id') ?? '');
        $title               = trim($this->input->post('title') ?? '');
        $department          = trim($this->input->post('department') ?? '');
        $positions           = (int) ($this->input->post('positions') ?? 1);
        $qualifications      = trim($this->input->post('qualifications') ?? '');
        $experienceRequired  = trim($this->input->post('experience_required') ?? '');
        $salaryRangeMin      = (float) ($this->input->post('salary_range_min') ?? 0);
        $salaryRangeMax      = (float) ($this->input->post('salary_range_max') ?? 0);
        $status              = trim($this->input->post('status') ?? 'Open');
        $deadline            = trim($this->input->post('deadline') ?? '');
        $description         = trim($this->input->post('description') ?? '');

        if ($title === '') {
            $this->json_error('Job title is required.');
        }
        if ($department === '') {
            $this->json_error('Department is required.');
        }
        if ($positions < 1) {
            $this->json_error('Number of positions must be at least 1.');
        }
        if (!in_array($status, ['Open', 'Closed', 'On_Hold'], true)) {
            $status = 'Open';
        }
        if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            $this->json_error('Deadline must be in YYYY-MM-DD format.');
        }

        $now   = date('c');
        $isNew = ($id === '');

        if ($isNew) {
            $id = $this->_next_id('JOB', 'Job');
        }

        $data = [
            'title'               => $title,
            'department'          => $department,
            'positions'           => $positions,
            'qualifications'      => $qualifications,
            'experience_required' => $experienceRequired,
            'salary_range_min'    => $salaryRangeMin,
            'salary_range_max'    => $salaryRangeMax,
            'status'              => $status,
            'deadline'            => $deadline,
            'description'         => $description,
            'updated_at'          => $now,
        ];
        if ($isNew) {
            $data['created_at']  = $now;
            $data['created_by']  = $this->admin_name;
        }

        $this->firebase->set($this->_jobs($id), $data);
        $this->json_success(['id' => $id, 'message' => $isNew ? 'Job posting created.' : 'Job posting updated.']);
    }

    /**
     * POST — Delete a job posting.
     * Params: id
     */
    public function delete_job()
    {
        $this->_require_role(self::HR_ROLES, 'delete_job');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        // Verify job exists
        $existing = $this->firebase->get($this->_jobs($id));
        if (!is_array($existing)) {
            $this->json_error('Job posting not found.');
        }

        // Check if applicants exist for this job
        $applicants = $this->firebase->get($this->_applicants());
        if (is_array($applicants)) {
            foreach ($applicants as $a) {
                if (isset($a['job_id']) && $a['job_id'] === $id) {
                    $this->json_error('Cannot delete: applicants are linked to this job posting. Delete or reassign applicants first.');
                }
            }
        }

        $this->firebase->delete($this->_jobs($id));
        $this->json_success(['message' => 'Job posting deleted.']);
    }

    // ====================================================================
    //  RECRUITMENT — APPLICANTS
    // ====================================================================

    /**
     * GET — List applicants. Optional filters: ?job_id=JOB0001&status=Applied
     */
    public function get_applicants()
    {
        $this->_require_role(self::HR_ROLES, 'get_applicants');

        $filterJob    = trim($this->input->get('job_id') ?? '');
        $filterStatus = trim($this->input->get('status') ?? '');

        $applicants = $this->firebase->get($this->_applicants());
        $list = [];
        if (is_array($applicants)) {
            foreach ($applicants as $id => $a) {
                if ($filterJob !== '' && isset($a['job_id']) && $a['job_id'] !== $filterJob) {
                    continue;
                }
                if ($filterStatus !== '' && isset($a['status']) && $a['status'] !== $filterStatus) {
                    continue;
                }
                $a['id'] = $id;
                $list[] = $a;
            }
        }

        // Sort by applied date descending
        usort($list, function ($a, $b) {
            return strcmp($b['applied_date'] ?? '', $a['applied_date'] ?? '');
        });

        $this->json_success(['applicants' => $list]);
    }

    /**
     * POST — Create or update an applicant.
     */
    public function save_applicant()
    {
        $this->_require_role(self::HR_ROLES, 'save_applicant');

        $id            = trim($this->input->post('id') ?? '');
        $jobId         = trim($this->input->post('job_id') ?? '');
        if ($id !== '') {
            $id = $this->safe_path_segment($id, 'id');
        }
        $jobId         = $this->safe_path_segment($jobId, 'job_id');
        $name          = trim($this->input->post('name') ?? '');
        $email         = trim($this->input->post('email') ?? '');
        $phone         = trim($this->input->post('phone') ?? '');
        $qualification = trim($this->input->post('qualification') ?? '');
        $experience    = trim($this->input->post('experience') ?? '');
        $resumeNotes   = trim($this->input->post('resume_notes') ?? '');
        $interviewDate = trim($this->input->post('interview_date') ?? '');
        $interviewNotes= trim($this->input->post('interview_notes') ?? '');
        $rating        = (int) ($this->input->post('rating') ?? 0);
        $status        = trim($this->input->post('status') ?? 'Applied');
        $notes         = trim($this->input->post('notes') ?? '');

        if ($name === '') {
            $this->json_error('Applicant name is required.');
        }
        if ($jobId === '') {
            $this->json_error('Job posting is required.');
        }

        // Normalise status to title-case
        $status = ucfirst(strtolower($status));
        if ($status === 'Interviewed') $status = 'Interview';
        $validStatuses = ['Applied', 'Shortlisted', 'Interview', 'Selected', 'Rejected', 'Joined'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'Applied';
        }

        // Verify job exists
        $job = $this->firebase->get($this->_jobs($jobId));
        if (!is_array($job)) {
            $this->json_error('Job posting not found.');
        }

        $now   = date('c');
        $isNew = ($id === '');

        if ($isNew) {
            $id = $this->_next_id('APP', 'Applicant');
        }

        // Preserve applied_date on edit
        $existingApplicant = null;
        if (!$isNew) {
            $existingApplicant = $this->firebase->get($this->_applicants($id));
        }

        $data = [
            'job_id'          => $jobId,
            'name'            => $name,
            'email'           => $email,
            'phone'           => $phone,
            'qualification'   => $qualification,
            'experience'      => $experience,
            'resume_notes'    => $resumeNotes,
            'interview_date'  => $interviewDate,
            'interview_notes' => $interviewNotes,
            'rating'          => $rating,
            'status'          => $status,
            'notes'           => $notes,
            'updated_at'      => $now,
            'updated_by'      => $this->admin_name,
        ];
        if ($isNew) {
            $data['applied_date'] = date('Y-m-d');
        } else {
            $data['applied_date'] = $existingApplicant['applied_date'] ?? date('Y-m-d');
        }

        $this->firebase->set($this->_applicants($id), $data);
        $this->json_success(['id' => $id, 'message' => $isNew ? 'Applicant added.' : 'Applicant updated.']);
    }

    /**
     * POST — Update only the status of an applicant (quick action).
     * Params: id, status, notes (optional remark)
     */
    public function update_applicant_status()
    {
        $this->_require_role(self::HR_ROLES, 'update_applicant_status');

        $id     = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');
        $status = trim($this->input->post('status') ?? '');
        $notes  = trim($this->input->post('notes') ?? '');

        $validStatuses = ['Applied', 'Shortlisted', 'Interview', 'Selected', 'Rejected', 'Joined'];
        if (!in_array($status, $validStatuses, true)) {
            $this->json_error('Invalid status.');
        }

        $existing = $this->firebase->get($this->_applicants($id));
        if (!is_array($existing)) {
            $this->json_error('Applicant not found.');
        }

        $update = [
            'status'     => $status,
            'updated_at' => date('c'),
            'updated_by' => $this->admin_name,
        ];
        if ($notes !== '') {
            $update['notes'] = $notes;
        }

        $this->firebase->update($this->_applicants($id), $update);
        $this->json_success(['message' => "Applicant status updated to {$status}."]);
    }

    /**
     * POST — Delete an applicant.
     * Params: id
     */
    public function delete_applicant()
    {
        $this->_require_role(self::HR_ROLES, 'delete_applicant');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        $existing = $this->firebase->get($this->_applicants($id));
        if (!is_array($existing)) {
            $this->json_error('Applicant not found.');
        }

        $this->firebase->delete($this->_applicants($id));
        $this->json_success(['message' => 'Applicant deleted.']);
    }

    // ====================================================================
    //  LEAVE MANAGEMENT — TYPES
    // ====================================================================

    /**
     * GET — List all leave types.
     */
    public function get_leave_types()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_leave_types');

        $types = $this->firebase->get($this->_leave_types());
        $list = [];
        if (is_array($types)) {
            foreach ($types as $id => $t) {
                $t['id'] = $id;
                $list[] = $t;
            }
        }

        $this->json_success(['leave_types' => $list]);
    }

    /**
     * POST — Create or update a leave type.
     */
    public function save_leave_type()
    {
        $this->_require_role(self::HR_ROLES, 'save_leave_type');

        $id           = trim($this->input->post('id') ?? '');
        $name         = trim($this->input->post('name') ?? '');
        $code         = strtoupper(trim($this->input->post('code') ?? ''));
        $daysPerYear  = (int) ($this->input->post('days_per_year') ?? 0);
        $carryForward = ($this->input->post('carry_forward') === 'true' || $this->input->post('carry_forward') === '1' || $this->input->post('carry_forward') === 'yes');
        $maxCarry     = (int) ($this->input->post('max_carry') ?? 0);
        $paid         = ($this->input->post('paid') === 'true' || $this->input->post('paid') === '1');
        $description  = trim($this->input->post('description') ?? '');
        $status       = trim($this->input->post('status') ?? 'Active');

        if ($name === '') {
            $this->json_error('Leave type name is required.');
        }
        if ($daysPerYear < 0) {
            $this->json_error('Days per year must be a non-negative number.');
        }
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $status = 'Active';
        }

        $now   = date('c');
        $isNew = ($id === '');

        if ($isNew) {
            $id = $this->_next_id('LT', 'LeaveType');
        }

        // Check duplicate name (excluding self)
        $existing = $this->firebase->get($this->_leave_types());
        if (is_array($existing)) {
            foreach ($existing as $eid => $et) {
                if ($eid !== $id && isset($et['name']) && strtolower($et['name']) === strtolower($name)) {
                    $this->json_error('A leave type with this name already exists.');
                }
            }
        }

        $data = [
            'name'          => $name,
            'code'          => $code,
            'days_per_year' => $daysPerYear,
            'carry_forward' => $carryForward,
            'max_carry'     => $maxCarry,
            'paid'          => $paid,
            'description'   => $description,
            'status'        => $status,
            'updated_at'    => $now,
        ];
        if ($isNew) {
            $data['created_at'] = $now;
        }

        $this->firebase->set($this->_leave_types($id), $data);
        $this->json_success(['id' => $id, 'message' => $isNew ? 'Leave type created.' : 'Leave type updated.']);
    }

    /**
     * POST — Delete a leave type.
     * Params: id
     */
    public function delete_leave_type()
    {
        $this->_require_role(self::HR_ROLES, 'delete_leave_type');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        $existing = $this->firebase->get($this->_leave_types($id));
        if (!is_array($existing)) {
            $this->json_error('Leave type not found.');
        }

        // Check if any pending/approved leave requests use this type
        $requests = $this->firebase->get($this->_leave_req());
        if (is_array($requests)) {
            foreach ($requests as $lr) {
                if (
                    isset($lr['type_id']) && $lr['type_id'] === $id &&
                    isset($lr['status']) && in_array($lr['status'], ['Pending', 'Approved'], true)
                ) {
                    $this->json_error('Cannot delete: active leave requests exist for this type.');
                }
            }
        }

        $this->firebase->delete($this->_leave_types($id));
        $this->json_success(['message' => 'Leave type deleted.']);
    }

    // ====================================================================
    //  LEAVE MANAGEMENT — BALANCES
    // ====================================================================

    /**
     * GET — Get leave balances for a year (defaults to current calendar year).
     * Optional: ?year=2026&staff_id=STAFF001
     */
    public function get_leave_balances()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_leave_balances');

        $year    = trim($this->input->get('year') ?? date('Y'));
        $staffId = trim($this->input->get('staff_id') ?? '');

        if (!preg_match('/^\d{4}$/', $year)) {
            $this->json_error('Invalid year format.');
        }

        if ($staffId !== '') {
            $staffId = $this->safe_path_segment($staffId, 'staff_id');
            // Single staff balances
            $balances = $this->firebase->get($this->_leave_bal($year, $staffId));
            $this->json_success([
                'balances' => is_array($balances) ? $balances : [],
                'staff_id' => $staffId,
                'year'     => $year,
            ]);
            return;
        }

        // All staff balances for the year
        $allBal = $this->firebase->get($this->_leave_bal($year));
        $result = [];
        if (is_array($allBal)) {
            foreach ($allBal as $sid => $types) {
                $result[$sid] = is_array($types) ? $types : [];
            }
        }

        $this->json_success(['balances' => $result, 'year' => $year]);
    }

    /**
     * POST — Allocate leave balances for all staff in the session roster.
     * Creates/updates balance records for each active leave type.
     * Params: year (calendar year e.g. '2026')
     */
    public function allocate_leave_balances()
    {
        $this->_require_role(self::HR_ROLES, 'allocate_leave');

        $year = trim($this->input->post('year') ?? date('Y'));
        if (!preg_match('/^\d{4}$/', $year)) {
            $this->json_error('Invalid year format.');
        }

        // Get all active leave types
        $leaveTypes = $this->firebase->get($this->_leave_types());
        $activeTypes = [];
        if (is_array($leaveTypes)) {
            foreach ($leaveTypes as $tid => $lt) {
                if (isset($lt['status']) && $lt['status'] === 'Active') {
                    $activeTypes[$tid] = $lt;
                }
            }
        }

        if (empty($activeTypes)) {
            $this->json_error('No active leave types found. Please create leave types first.');
        }

        // Get staff from session roster
        $roster = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        if (!is_array($roster) || empty($roster)) {
            $this->json_error('No staff found in the current session roster.');
        }

        // Determine previous year for carry forward
        $prevYear = (string) ((int) $year - 1);
        $prevBalances = $this->firebase->get($this->_leave_bal($prevYear));

        $staffCount = 0;

        foreach ($roster as $staffId => $rosterData) {
            foreach ($activeTypes as $typeId => $lt) {
                $allocated = (int) ($lt['days_per_year'] ?? 0);
                $carried = 0;

                // Carry forward from previous year if enabled
                if (!empty($lt['carry_forward'])) {
                    $prevBal = 0;
                    if (is_array($prevBalances) && isset($prevBalances[$staffId][$typeId]['balance'])) {
                        $prevBal = (int) $prevBalances[$staffId][$typeId]['balance'];
                    }
                    $maxCarry = (int) ($lt['max_carry'] ?? 0);
                    $carried = ($maxCarry > 0) ? min($prevBal, $maxCarry) : $prevBal;
                    if ($carried < 0) $carried = 0;
                }

                // Check if balance already exists — preserve used count
                $existingBal = $this->firebase->get($this->_leave_bal($year, $staffId, $typeId));
                $used = 0;
                if (is_array($existingBal) && isset($existingBal['used'])) {
                    $used = (int) $existingBal['used'];
                }

                $balance = $allocated + $carried - $used;
                if ($balance < 0) $balance = 0;

                $balData = [
                    'allocated' => $allocated,
                    'used'      => $used,
                    'carried'   => $carried,
                    'balance'   => $balance,
                ];

                $this->firebase->set($this->_leave_bal($year, $staffId, $typeId), $balData);
            }
            $staffCount++;
        }

        $this->json_success([
            'message'     => "Leave balances allocated for {$staffCount} staff members across " . count($activeTypes) . " leave types.",
            'staff_count' => $staffCount,
            'type_count'  => count($activeTypes),
            'year'        => $year,
        ]);
    }

    // ====================================================================
    //  LEAVE MANAGEMENT — REQUESTS
    // ====================================================================

    /**
     * GET — List leave requests. Optional filters: ?status=Pending&staff_id=XXX
     */
    public function get_leave_requests()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_leave_requests');

        $filterStatus  = trim($this->input->get('status') ?? '');
        $filterStaffId = trim($this->input->get('staff_id') ?? '');

        $requests = $this->firebase->get($this->_leave_req());
        $list = [];
        if (is_array($requests)) {
            foreach ($requests as $id => $r) {
                if ($filterStatus !== '' && isset($r['status']) && $r['status'] !== $filterStatus) {
                    continue;
                }
                if ($filterStaffId !== '' && isset($r['staff_id']) && $r['staff_id'] !== $filterStaffId) {
                    continue;
                }
                $r['id'] = $id;
                $list[] = $r;
            }
        }

        // Sort by applied_on descending
        usort($list, function ($a, $b) {
            return strcmp($b['applied_on'] ?? '', $a['applied_on'] ?? '');
        });

        $this->json_success(['leave_requests' => $list]);
    }

    /**
     * POST — Apply for leave (by or on behalf of a staff member).
     * Params: staff_id, type_id, from_date, to_date, reason
     */
    public function apply_leave()
    {
        $this->_require_role(self::VIEW_ROLES, 'apply_leave');

        $staffId  = $this->safe_path_segment(trim($this->input->post('staff_id') ?? ''), 'staff_id');
        $typeId   = $this->safe_path_segment(trim($this->input->post('type_id') ?? ''), 'type_id');
        $fromDate = trim($this->input->post('from_date') ?? '');
        $toDate   = trim($this->input->post('to_date') ?? '');
        $reason   = trim($this->input->post('reason') ?? '');

        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $this->json_error('From date must be in YYYY-MM-DD format.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $this->json_error('To date must be in YYYY-MM-DD format.');
        }
        if ($toDate < $fromDate) {
            $this->json_error('To date cannot be before from date.');
        }

        // Calculate days
        $from = new DateTime($fromDate);
        $to   = new DateTime($toDate);
        $days = (int) $from->diff($to)->days + 1;

        // Verify leave type exists and is active
        $leaveType = $this->firebase->get($this->_leave_types($typeId));
        if (!is_array($leaveType)) {
            $this->json_error('Leave type not found.');
        }
        if (isset($leaveType['status']) && $leaveType['status'] !== 'Active') {
            $this->json_error('This leave type is inactive.');
        }

        // Verify staff exists in roster
        $staffProfile = $this->firebase->get("Users/Teachers/{$this->school_name}/{$staffId}");
        if (!is_array($staffProfile)) {
            $this->json_error('Staff member not found.');
        }
        $staffName = $staffProfile['Name'] ?? $staffId;

        // Check leave balance — allow submission even if insufficient (excess becomes LWP)
        $year = date('Y', strtotime($fromDate));
        $balance = $this->firebase->get($this->_leave_bal($year, $staffId, $typeId));
        $currentBalance = 0;
        if (is_array($balance) && isset($balance['balance'])) {
            $currentBalance = max(0, (int) $balance['balance']); // clamp: legacy records may be negative
        }
        $paidDays = min($currentBalance, $days);
        $lwpDays  = $days - $paidDays;
        $lwpWarning = '';

        // Check for overlapping leave requests (Pending or Approved)
        $existingReqs = $this->firebase->get($this->_leave_req());
        if (is_array($existingReqs)) {
            foreach ($existingReqs as $rid => $er) {
                if (
                    isset($er['staff_id']) && $er['staff_id'] === $staffId &&
                    isset($er['status']) && in_array($er['status'], ['Pending', 'Approved'], true)
                ) {
                    $erFrom = $er['from_date'] ?? '';
                    $erTo   = $er['to_date'] ?? '';
                    // Check overlap: two date ranges overlap when start1 <= end2 AND start2 <= end1
                    if ($fromDate <= $erTo && $toDate >= $erFrom) {
                        $this->json_error("Overlapping leave request exists ({$erFrom} to {$erTo}).");
                    }
                }
            }
        }

        $reqId = $this->_next_id('LR', 'LeaveRequest');

        if ($lwpDays > 0) {
            $lwpWarning = "Warning: {$lwpDays} day(s) will be treated as Leave Without Pay (balance: {$currentBalance}).";
        }

        $data = [
            'staff_id'   => $staffId,
            'staff_name' => $staffName,
            'type_id'    => $typeId,
            'type_name'  => $leaveType['name'] ?? '',
            'from_date'  => $fromDate,
            'to_date'    => $toDate,
            'days'       => $days,
            'paid_days'  => $paidDays,
            'lwp_days'   => $lwpDays,
            'reason'     => $reason,
            'status'     => 'Pending',
            'applied_on' => date('c'),
            'decided_by' => '',
            'decided_on' => '',
            'remarks'    => '',
        ];

        $this->firebase->set($this->_leave_req($reqId), $data);

        $msg = "Leave request submitted for {$days} day(s).";
        if ($lwpWarning !== '') {
            $msg .= ' ' . $lwpWarning;
        }
        $this->json_success(['id' => $reqId, 'message' => $msg, 'lwp_days' => $lwpDays, 'lwp_warning' => $lwpWarning]);
    }

    /**
     * POST — Approve or reject a leave request.
     * Params: id, decision (Approved|Rejected), remarks (optional)
     */
    public function decide_leave()
    {
        $this->_require_role(self::HR_ROLES, 'decide_leave');

        $id       = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');
        $decision = trim($this->input->post('decision') ?? '');
        $remarks  = trim($this->input->post('remarks') ?? '');

        if (!in_array($decision, ['Approved', 'Rejected'], true)) {
            $this->json_error('Decision must be Approved or Rejected.');
        }

        $request = $this->firebase->get($this->_leave_req($id));
        if (!is_array($request)) {
            $this->json_error('Leave request not found.');
        }
        if (($request['status'] ?? '') !== 'Pending') {
            $this->json_error('Only pending requests can be decided.');
        }

        // M-04 FIX: Atomically mark request as "Processing" to prevent concurrent approvals
        // on the same leave request (optimistic lock via status transition).
        $this->firebase->update($this->_leave_req($id), ['status' => 'Processing']);

        // Re-read to verify we won the race (another thread may have set it too)
        $recheck = $this->firebase->get($this->_leave_req($id) . '/status');
        if ($recheck !== 'Processing') {
            $this->json_error('This request is being processed by another user. Please refresh.');
        }

        $staffId  = $request['staff_id'] ?? '';
        $typeId   = $request['type_id'] ?? '';
        $days     = (int) ($request['days'] ?? 0);
        $fromDate = $request['from_date'] ?? '';
        $year     = date('Y', strtotime($fromDate));

        // If approving, calculate paid/LWP split and deduct from balance
        $paidDays = $days;
        $lwpDays  = 0;
        if ($decision === 'Approved') {
            $balance = $this->firebase->get($this->_leave_bal($year, $staffId, $typeId));
            if (is_array($balance)) {
                $currentBalance = (int) ($balance['balance'] ?? 0);
                $currentUsed    = (int) ($balance['used'] ?? 0);

                // Split into paid and LWP portions (clamp balance: legacy records may be negative)
                $paidDays = min(max(0, $currentBalance), $days);
                $lwpDays  = $days - $paidDays;

                // Only deduct the paid portion from balance
                if ($paidDays > 0) {
                    $this->firebase->update($this->_leave_bal($year, $staffId, $typeId), [
                        'used'    => $currentUsed + $paidDays,
                        'balance' => $currentBalance - $paidDays,
                    ]);

                    // M-04 FIX: Post-write verification — re-read balance to detect concurrent deduction
                    $verifyBal = $this->firebase->get($this->_leave_bal($year, $staffId, $typeId));
                    if (is_array($verifyBal) && (int) ($verifyBal['balance'] ?? 0) < 0) {
                        // Race detected: balance went negative — rollback deduction
                        $this->firebase->update($this->_leave_bal($year, $staffId, $typeId), [
                            'used'    => $currentUsed,
                            'balance' => $currentBalance,
                        ]);
                        $this->firebase->update($this->_leave_req($id), ['status' => 'Pending']);
                        $this->json_error('Leave balance was modified concurrently. Please try again.');
                    }
                }
            } else {
                // No balance record — all days are LWP
                $paidDays = 0;
                $lwpDays  = $days;
                $this->firebase->set($this->_leave_bal($year, $staffId, $typeId), [
                    'allocated' => 0,
                    'used'      => 0,
                    'carried'   => 0,
                    'balance'   => 0,
                ]);
            }
        }

        $updateData = [
            'status'     => $decision,
            'decided_by' => $this->admin_name,
            'decided_on' => date('c'),
            'remarks'    => $remarks,
            'paid_days'  => $paidDays,
            'lwp_days'   => $lwpDays,
        ];

        $this->firebase->update($this->_leave_req($id), $updateData);

        $msg = "Leave request {$decision}.";
        if ($decision === 'Approved' && $lwpDays > 0) {
            $msg .= " ({$paidDays} paid, {$lwpDays} LWP)";
        }
        $this->json_success(['message' => $msg, 'paid_days' => $paidDays, 'lwp_days' => $lwpDays]);
    }

    /**
     * POST — Cancel a leave request.
     * Params: id
     * If the request was Approved, restores the balance.
     */
    public function cancel_leave()
    {
        $this->_require_role(self::HR_ROLES, 'cancel_leave');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        $request = $this->firebase->get($this->_leave_req($id));
        if (!is_array($request)) {
            $this->json_error('Leave request not found.');
        }

        $currentStatus = $request['status'] ?? '';
        if (!in_array($currentStatus, ['Pending', 'Approved'], true)) {
            $this->json_error('Only Pending or Approved requests can be cancelled.');
        }

        // M-04 FIX: Lock the request by transitioning to Cancelling status
        $this->firebase->update($this->_leave_req($id), ['status' => 'Cancelling']);

        $staffId  = $request['staff_id'] ?? '';
        $typeId   = $request['type_id'] ?? '';
        $days     = (int) ($request['days'] ?? 0);
        $fromDate = $request['from_date'] ?? '';
        $year     = date('Y', strtotime($fromDate));

        // If was Approved, restore only the paid portion (not LWP days) to balance
        $paidDays = (int) ($request['paid_days'] ?? $days); // fallback to full days for legacy requests
        if ($currentStatus === 'Approved' && $staffId !== '' && $typeId !== '' && $paidDays > 0) {
            $balance = $this->firebase->get($this->_leave_bal($year, $staffId, $typeId));
            if (is_array($balance)) {
                $currentUsed    = (int) ($balance['used'] ?? 0);
                $currentBalance = (int) ($balance['balance'] ?? 0);

                $newUsed    = max(0, $currentUsed - $paidDays);
                $newBalance = $currentBalance + $paidDays;

                $this->firebase->update($this->_leave_bal($year, $staffId, $typeId), [
                    'used'    => $newUsed,
                    'balance' => $newBalance,
                ]);
            }
        }

        $this->firebase->update($this->_leave_req($id), [
            'status'     => 'Cancelled',
            'decided_by' => $this->admin_name,
            'decided_on' => date('c'),
            'remarks'    => 'Cancelled' . ($currentStatus === 'Approved' ? ' (balance restored)' : ''),
        ]);

        $this->json_success([
            'message' => 'Leave request cancelled.' . ($currentStatus === 'Approved' ? ' Balance restored.' : ''),
        ]);
    }

    // ====================================================================
    //  SALARY STRUCTURES
    // ====================================================================

    /**
     * GET — Get all salary structures or a single one (?staff_id=XXX).
     */
    public function get_salary_structures()
    {
        $this->_require_role(self::ADMIN_ROLES, 'get_salary_structures');

        $staffId = trim($this->input->get('staff_id') ?? '');

        if ($staffId !== '') {
            $staffId = $this->safe_path_segment($staffId, 'staff_id');
            $structure = $this->firebase->get($this->_salary($staffId));
            $this->json_success([
                'salary_structure' => is_array($structure) ? $structure : null,
                'staff_id'         => $staffId,
            ]);
            return;
        }

        $all = $this->firebase->get($this->_salary());
        $list = [];
        if (is_array($all)) {
            foreach ($all as $sid => $s) {
                $s['staff_id'] = $sid;
                $list[] = $s;
            }
        }

        $this->json_success(['salary_structures' => $list]);
    }

    /**
     * POST — Save salary structure for a staff member.
     */
    public function save_salary_structure()
    {
        $this->_require_role(self::ADMIN_ROLES, 'save_salary_structure');

        $staffId = $this->safe_path_segment(trim($this->input->post('staff_id') ?? ''), 'staff_id');

        // Verify staff exists
        $staffProfile = $this->firebase->get("Users/Teachers/{$this->school_name}/{$staffId}");
        if (!is_array($staffProfile)) {
            $this->json_error('Staff member not found.');
        }

        $basic            = (float) ($this->input->post('basic') ?? 0);
        $hra              = (float) ($this->input->post('hra') ?? 0);
        $da               = (float) ($this->input->post('da') ?? 0);
        $ta               = (float) ($this->input->post('ta') ?? 0);
        $medical          = (float) ($this->input->post('medical') ?? 0);
        $otherAllowances  = (float) ($this->input->post('other_allowances') ?? $this->input->post('special_allowance') ?? 0);
        $pfEmployee       = (float) ($this->input->post('pf_employee') ?? 0);
        $pfEmployer       = (float) ($this->input->post('pf_employer') ?? 0);
        $esiEmployee      = (float) ($this->input->post('esi_employee') ?? 0);
        $esiEmployer      = (float) ($this->input->post('esi_employer') ?? 0);
        $professionalTax  = (float) ($this->input->post('professional_tax') ?? 0);
        $tds              = (float) ($this->input->post('tds') ?? 0);
        $otherDeductions  = (float) ($this->input->post('other_deductions') ?? 0);

        if ($basic <= 0) {
            $this->json_error('Basic salary must be greater than zero.');
        }

        $data = [
            'basic'            => $basic,
            'hra'              => $hra,
            'da'               => $da,
            'ta'               => $ta,
            'medical'          => $medical,
            'other_allowances' => $otherAllowances,
            'pf_employee'      => $pfEmployee,
            'pf_employer'      => $pfEmployer,
            'esi_employee'     => $esiEmployee,
            'esi_employer'     => $esiEmployer,
            'professional_tax' => $professionalTax,
            'tds'              => $tds,
            'other_deductions' => $otherDeductions,
            'updated_at'       => date('c'),
            'updated_by'       => $this->admin_name,
        ];

        $this->firebase->set($this->_salary($staffId), $data);

        $gross      = $basic + $hra + $da + $ta + $medical + $otherAllowances;
        $deductions = $pfEmployee + $esiEmployee + $professionalTax + $tds + $otherDeductions;
        $net        = $gross - $deductions;

        $this->json_success([
            'message' => 'Salary structure saved.',
            'summary' => [
                'gross'      => round($gross, 2),
                'deductions' => round($deductions, 2),
                'net'        => round($net, 2),
            ],
        ]);
    }

    /**
     * POST — Delete a salary structure.
     * Params: id (staff_id key)
     */
    public function delete_salary_structure()
    {
        $this->_require_role(self::ADMIN_ROLES, 'delete_salary_structure');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'salary_id');

        $existing = $this->firebase->get($this->_salary($id));
        if (!is_array($existing)) {
            $this->json_error('Salary structure not found.');
        }

        $this->firebase->delete($this->_salary(), $id);

        $this->json_success(['message' => 'Salary structure deleted.']);
    }

    // ====================================================================
    //  PAYROLL
    // ====================================================================

    /**
     * GET — List payroll runs for this session.
     */
    public function get_payroll_runs()
    {
        $this->_require_role(self::ADMIN_ROLES, 'get_payroll_runs');

        $runs = $this->firebase->get($this->_payroll_runs());
        $list = [];
        if (is_array($runs)) {
            foreach ($runs as $id => $r) {
                $r['id'] = $id;
                $list[] = $r;
            }
        }

        // Sort by created_at descending
        usort($list, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $this->json_success(['payroll_runs' => $list]);
    }

    /**
     * GET — Pre-flight check before payroll generation.
     * Params: month (e.g. 'January'), year (e.g. '2026')
     *
     * Returns warnings and readiness status without creating any data.
     */
    public function preflight_payroll()
    {
        $this->_require_role(self::ADMIN_ROLES, 'preflight_payroll');

        $month = trim($this->input->get('month') ?? '');
        $year  = trim($this->input->get('year') ?? '');

        $validMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        if (!in_array($month, $validMonths, true) || !preg_match('/^\d{4}$/', $year)) {
            $this->json_error('Invalid month or year.');
        }

        $warnings = [];

        // Check for existing run
        $existingRuns = $this->firebase->get($this->_payroll_runs());
        if (is_array($existingRuns)) {
            foreach ($existingRuns as $rid => $er) {
                if (($er['month'] ?? '') === $month && ($er['year'] ?? '') === $year) {
                    $this->json_error("A payroll run already exists for {$month} {$year} (ID: {$rid}). Delete or use it.");
                }
            }
        }

        // Check roster
        $roster = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        if (!is_array($roster) || empty($roster)) {
            $this->json_error('No staff found in the current session roster.');
        }

        // Check salary structures coverage
        $salaryStructures = $this->firebase->get($this->_salary());
        $staffWithSalary = is_array($salaryStructures) ? array_keys($salaryStructures) : [];
        $rosterIds = array_keys($roster);
        $missingStructures = array_diff($rosterIds, $staffWithSalary);
        if (!empty($staffWithSalary)) {
            $coveredCount = count(array_intersect($rosterIds, $staffWithSalary));
        } else {
            $coveredCount = 0;
        }

        if (empty($staffWithSalary)) {
            $this->json_error('No salary structures found. Please set up salary structures first.');
        }
        if (!empty($missingStructures)) {
            $warnings[] = count($missingStructures) . ' staff member(s) in the roster have no salary structure and will be skipped.';
        }

        // Check accounting accounts (uses correct ChartOfAccounts path)
        $this->_validate_accounts(['5010', '5020', '2020']);

        // Check attendance data
        $monthYear  = "{$month} {$year}";
        $attendance = $this->firebase->get(
            "Schools/{$this->school_name}/{$this->session_year}/Staff_Attendance/{$monthYear}"
        );
        if (!is_array($attendance) || empty($attendance)) {
            $warnings[] = "No attendance data found for {$month} {$year}. All staff will be treated as fully present.";
        } else {
            $attendanceCount = count($attendance);
            if ($attendanceCount < $coveredCount) {
                $warnings[] = "Attendance data exists for {$attendanceCount} of {$coveredCount} staff. Missing staff will be treated as fully present.";
            }
        }

        // Check pending leave requests for this month
        $allLeaveReqs = $this->firebase->get($this->_leave_req());
        $pendingCount = 0;
        if (is_array($allLeaveReqs)) {
            $monthNum   = array_search($month, $validMonths) + 1;
            $monthStart = sprintf('%04d-%02d-01', (int) $year, $monthNum);
            $monthEnd   = date('Y-m-t', strtotime($monthStart));
            foreach ($allLeaveReqs as $rid => $lr) {
                if (($lr['status'] ?? '') !== 'Pending') continue;
                $lrFrom = $lr['from_date'] ?? '';
                $lrTo   = $lr['to_date'] ?? '';
                if ($lrFrom <= $monthEnd && $lrTo >= $monthStart) {
                    $pendingCount++;
                }
            }
        }
        if ($pendingCount > 0) {
            $warnings[] = "{$pendingCount} pending leave request(s) overlap with {$month} {$year}. Consider approving/rejecting them before generating payroll.";
        }

        $this->json_success([
            'ready'          => true,
            'staff_covered'  => $coveredCount,
            'staff_total'    => count($rosterIds),
            'pending_leaves' => $pendingCount,
            'warnings'       => $warnings,
        ]);
    }

    /**
     * POST — Generate payroll for a given month/year.
     * Params: month (e.g. 'January'), year (e.g. '2026')
     *
     * Creates a Draft run with individual slips for all staff with salary structures.
     * Factors in attendance (absent days reduce basic proportionally).
     * Also creates expense journal entries (Debit salary expense, Credit salary payable).
     */
    public function generate_payroll()
    {
        $this->_require_role(self::ADMIN_ROLES, 'generate_payroll');

        $month = trim($this->input->post('month') ?? '');
        $year  = trim($this->input->post('year') ?? '');

        $validMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        if (!in_array($month, $validMonths, true)) {
            $this->json_error('Invalid month.');
        }
        if (!preg_match('/^\d{4}$/', $year)) {
            $this->json_error('Invalid year.');
        }

        // Check for existing run for the same month/year
        $existingRuns = $this->firebase->get($this->_payroll_runs());
        if (is_array($existingRuns)) {
            foreach ($existingRuns as $rid => $er) {
                if (
                    isset($er['month']) && $er['month'] === $month &&
                    isset($er['year']) && $er['year'] === $year
                ) {
                    $this->json_error("A payroll run already exists for {$month} {$year} (ID: {$rid}). Delete or use it.");
                }
            }
        }

        // Get staff from session roster
        $roster = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        if (!is_array($roster) || empty($roster)) {
            $this->json_error('No staff found in the current session roster.');
        }

        // Get all salary structures
        $salaryStructures = $this->firebase->get($this->_salary());
        if (!is_array($salaryStructures) || empty($salaryStructures)) {
            $this->json_error('No salary structures found. Please set up salary structures first.');
        }

        // Get staff profiles for names/departments
        $staffProfiles = $this->firebase->get("Users/Teachers/{$this->school_name}");

        // Validate accounting accounts exist before proceeding (H3)
        // 2030=PF Payable, 2031=ESI Payable, 2032=TDS Payable,
        // 2033=Professional Tax Payable, 2034=Other Deductions Payable
        $this->_validate_accounts(['5010', '5020', '2020', '2030', '2031', '2032', '2033', '2034']);

        // Get approved leave requests for this month to calculate LWP days per staff
        $allLeaveReqs = $this->firebase->get($this->_leave_req());
        $lwpByStaff     = []; // staffId => total LWP days in this month
        $lwpDaysByStaff = []; // staffId => array of day-of-month numbers that are LWP
        if (is_array($allLeaveReqs)) {
            $monthStart = sprintf('%04d-%02d-01', (int) $year, array_search($month, $validMonths) + 1);
            $monthEnd   = date('Y-m-t', strtotime($monthStart));
            foreach ($allLeaveReqs as $rid => $lr) {
                if (($lr['status'] ?? '') !== 'Approved') continue;
                $lwp = (int) ($lr['lwp_days'] ?? 0);
                if ($lwp <= 0) continue;
                $sid = $lr['staff_id'] ?? '';
                if ($sid === '') continue;
                // LWP days are at the END of the leave period (paid days consumed first)
                $lrTo = $lr['to_date'] ?? '';
                // Calculate the start date of the LWP-only portion
                $lwpStart = date('Y-m-d', strtotime("{$lrTo} -" . ($lwp - 1) . " days"));
                // Check if LWP date range overlaps with this payroll month
                if ($lwpStart <= $monthEnd && $lrTo >= $monthStart) {
                    $overlapStart = max($lwpStart, $monthStart);
                    $overlapEnd   = min($lrTo, $monthEnd);
                    $lwpInMonth   = (int) (new DateTime($overlapStart))->diff(new DateTime($overlapEnd))->days + 1;
                    if (!isset($lwpByStaff[$sid])) $lwpByStaff[$sid] = 0;
                    $lwpByStaff[$sid] += $lwpInMonth;

                    // Track which day-of-month numbers are LWP days (1-based)
                    if (!isset($lwpDaysByStaff[$sid])) $lwpDaysByStaff[$sid] = [];
                    $cursor = new DateTime($overlapStart);
                    $end    = new DateTime($overlapEnd);
                    while ($cursor <= $end) {
                        $lwpDaysByStaff[$sid][(int) $cursor->format('j')] = true;
                        $cursor->modify('+1 day');
                    }
                }
            }
        }

        // Get attendance for the month
        $monthYear  = "{$month} {$year}";
        $attendance = $this->firebase->get(
            "Schools/{$this->school_name}/{$this->session_year}/Staff_Attendance/{$monthYear}"
        );

        // HR-5 FIX: Exclude Sundays, 2nd & 4th Saturdays, and school holidays
        $monthNum    = array_search($month, $validMonths) + 1;
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $monthNum, 1, (int) $year));

        // Load school holidays for this month (HR-11 FIX: merge both Events and Attendance holiday sources)
        $holidays = [];
        try {
            // Source 1: Events module — array of [{date: "YYYY-MM-DD", ...}]
            $holidayData = $this->firebase->get("Schools/{$this->school_name}/Events/Holidays/{$year}");
            if (is_array($holidayData)) {
                foreach ($holidayData as $h) {
                    $hDate = $h['date'] ?? '';
                    if ($hDate && (int) date('n', strtotime($hDate)) === $monthNum) {
                        $holidays[date('j', strtotime($hDate))] = true;
                    }
                }
            }
            // Source 2: Attendance settings — {"YYYY-MM-DD": "Holiday Name", ...}
            $attHolidays = $this->firebase->get("Schools/{$this->school_name}/Config/Attendance/holidays");
            if (is_array($attHolidays)) {
                foreach ($attHolidays as $date => $name) {
                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
                        if ((int) $m[1] === (int) $year && (int) $m[2] === $monthNum) {
                            $holidays[(int) $m[3]] = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Non-fatal: proceed without holidays
        }

        $nonWorkingDays = 0;
        $satCount = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dow = (int) date('w', mktime(0, 0, 0, $monthNum, $d, (int) $year));
            if ($dow === 0) {
                $nonWorkingDays++;
            } elseif ($dow === 6) {
                $satCount++;
                if ($satCount === 2 || $satCount === 4) {
                    $nonWorkingDays++;
                }
            } elseif (isset($holidays[$d])) {
                $nonWorkingDays++;
            }
        }
        $workingDays = $daysInMonth - $nonWorkingDays;

        // Generate run ID
        $runId = $this->_next_id('PR', 'PayrollRun');

        $totalGross          = 0;
        $totalDeductions     = 0;
        $totalNet            = 0;
        $totalPF             = 0;
        $totalESI            = 0;
        $totalTDS            = 0;
        $totalProfTax        = 0;
        $totalOtherDed       = 0;
        $staffCount          = 0;
        $totalTeachingExp    = 0;
        $totalNonTeachingExp = 0;
        $slips               = [];

        foreach ($roster as $staffId => $rosterData) {
            // Skip if no salary structure
            if (!isset($salaryStructures[$staffId])) {
                continue;
            }

            $sal     = $salaryStructures[$staffId];
            $profile = (isset($staffProfiles[$staffId]) && is_array($staffProfiles[$staffId]))
                ? $staffProfiles[$staffId]
                : [];

            $staffName  = $profile['Name'] ?? $staffId;
            $department = $profile['Department'] ?? '';
            $position   = $profile['Position'] ?? '';

            // Determine absent days from attendance string
            // NOTE: Skip days already covered by approved LWP leave to avoid double-counting.
            // LWP days are deducted separately via $staffLwpDays below.
            $daysAbsent   = 0;
            $leaveDays    = 0;
            $staffLwpSet  = isset($lwpDaysByStaff[$staffId]) ? $lwpDaysByStaff[$staffId] : [];
            if (is_array($attendance) && isset($attendance[$staffId])) {
                $attStr = (string) $attendance[$staffId];
                $len    = strlen($attStr);
                for ($i = 0; $i < $len; $i++) {
                    $dayNum = $i + 1; // attendance string is 1-indexed by day of month
                    $ch = strtoupper($attStr[$i]);
                    if ($ch === 'A') {
                        // Only count as absent if this day is NOT an LWP day
                        if (!isset($staffLwpSet[$dayNum])) {
                            $daysAbsent++;
                        }
                    } elseif ($ch === 'L') {
                        $leaveDays++;
                    }
                }
            }

            // LWP days for this staff from approved leave requests
            $staffLwpDays = isset($lwpByStaff[$staffId]) ? (int) $lwpByStaff[$staffId] : 0;

            $daysWorked = $workingDays - $daysAbsent - $leaveDays;
            if ($daysWorked < 0) $daysWorked = 0;

            // Calculate pay — basic reduced proportionally for absent + LWP days
            $basic = (float) ($sal['basic'] ?? 0);
            $deductionDays  = $daysAbsent + $staffLwpDays;
            $absentFraction = ($workingDays > 0) ? ($deductionDays / $workingDays) : 0;
            $effectiveBasic = round($basic * (1 - $absentFraction), 2);

            // Calculate LWP deduction amount separately for payslip display
            $dailySalary   = ($workingDays > 0) ? round($basic / $workingDays, 2) : 0;
            $lwpDeduction  = round($dailySalary * $staffLwpDays, 2);

            // HR-6 FIX: Pro-rate allowances by the same absent fraction as basic
            $hra             = round((float) ($sal['hra'] ?? 0) * (1 - $absentFraction), 2);
            $da              = round((float) ($sal['da'] ?? 0) * (1 - $absentFraction), 2);
            $ta              = round((float) ($sal['ta'] ?? 0) * (1 - $absentFraction), 2);
            $medical         = round((float) ($sal['medical'] ?? 0) * (1 - $absentFraction), 2);
            $otherAllowances = round((float) ($sal['other_allowances'] ?? 0) * (1 - $absentFraction), 2);

            $gross = round($effectiveBasic + $hra + $da + $ta + $medical + $otherAllowances, 2);

            $pfEmployee      = (float) ($sal['pf_employee'] ?? 0);
            $pfEmployer      = (float) ($sal['pf_employer'] ?? 0);
            $esiEmployee     = (float) ($sal['esi_employee'] ?? 0);
            $esiEmployer     = (float) ($sal['esi_employer'] ?? 0);
            $professionalTax = (float) ($sal['professional_tax'] ?? 0);
            $tds             = (float) ($sal['tds'] ?? 0);
            $otherDeductions = (float) ($sal['other_deductions'] ?? 0);

            $employeeDeductions = round(
                $pfEmployee + $esiEmployee + $professionalTax + $tds + $otherDeductions, 2
            );
            $netPay = round($gross - $employeeDeductions, 2);
            if ($netPay < 0) $netPay = 0;

            // Classify as Teaching or Non-Teaching for expense accounts
            $isTeaching = (
                stripos($position, 'teacher') !== false ||
                stripos($position, 'lecturer') !== false ||
                stripos($department, 'teaching') !== false
            );
            if ($isTeaching) {
                $totalTeachingExp += $gross;
            } else {
                $totalNonTeachingExp += $gross;
            }

            $slips[$staffId] = [
                'staff_id'         => $staffId,
                'staff_name'       => $staffName,
                'department'       => $department,
                'basic'            => $effectiveBasic,
                'hra'              => $hra,
                'da'               => $da,
                'ta'               => $ta,
                'medical'          => $medical,
                'other_allowances' => $otherAllowances,
                'gross'            => $gross,
                'pf_employee'      => $pfEmployee,
                'pf_employer'      => $pfEmployer,
                'esi_employee'     => $esiEmployee,
                'esi_employer'     => $esiEmployer,
                'professional_tax' => $professionalTax,
                'tds'              => $tds,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $employeeDeductions,
                'net_pay'          => $netPay,
                'days_worked'      => $daysWorked,
                'days_absent'      => $daysAbsent,
                'leave_days'       => $leaveDays,
                'lwp_days'         => $staffLwpDays,
                'lwp_deduction'    => $lwpDeduction,
                'status'           => 'Draft',
            ];

            $totalGross      += $gross;
            $totalDeductions += $employeeDeductions;
            $totalNet        += $netPay;
            $totalPF         += $pfEmployee;
            $totalESI        += $esiEmployee;
            $totalTDS        += $tds;
            $totalProfTax    += $professionalTax;
            $totalOtherDed   += $otherDeductions;
            $staffCount++;
        }

        if ($staffCount === 0) {
            $this->json_error('No staff with salary structures found in the roster.');
        }

        $totalGross          = round($totalGross, 2);
        $totalDeductions     = round($totalDeductions, 2);
        $totalNet            = round($totalNet, 2);
        $totalPF             = round($totalPF, 2);
        $totalESI            = round($totalESI, 2);
        $totalTDS            = round($totalTDS, 2);
        $totalProfTax        = round($totalProfTax, 2);
        $totalOtherDed       = round($totalOtherDed, 2);
        $totalTeachingExp    = round($totalTeachingExp, 2);
        $totalNonTeachingExp = round($totalNonTeachingExp, 2);

        // Create payroll run record
        $runData = [
            'month'            => $month,
            'year'             => $year,
            'status'           => 'Draft',
            'total_gross'      => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'        => $totalNet,
            'staff_count'      => $staffCount,
            'working_days'     => $workingDays,
            'created_at'       => date('c'),
            'created_by'       => $this->admin_name,
            'finalized_at'     => '',
            'finalized_by'     => '',
            'paid_at'          => '',
            'paid_by'          => '',
            'payment_mode'     => '',
            'journal_id'       => '',
        ];

        $this->firebase->set($this->_payroll_runs($runId), $runData);

        // Create individual slips (single batch write instead of N sequential calls)
        $this->firebase->set($this->_payroll_slips($runId), $slips);

        // ── Expense journal entry (Debit salary expenses, Credit payable accounts) ──
        // DR side: gross salary expense (teaching + non-teaching)
        // CR side: net salary payable (2020) + statutory deductions to separate liability accounts
        //   2030 = PF Payable, 2031 = ESI Payable, 2032 = TDS Payable
        // This ensures 2020 only holds the net amount cleared on payment day,
        // while statutory liabilities sit in their own accounts until remitted to government.
        $narration = "Salary accrual - {$month} {$year}";
        $journalLines = [];
        if ($totalTeachingExp > 0) {
            $journalLines[] = ['account_code' => '5010', 'dr' => $totalTeachingExp, 'cr' => 0];
        }
        if ($totalNonTeachingExp > 0) {
            $journalLines[] = ['account_code' => '5020', 'dr' => $totalNonTeachingExp, 'cr' => 0];
        }
        $journalLines[] = ['account_code' => '2020', 'dr' => 0, 'cr' => $totalNet];
        if ($totalPF > 0) {
            $journalLines[] = ['account_code' => '2030', 'dr' => 0, 'cr' => $totalPF];
        }
        if ($totalESI > 0) {
            $journalLines[] = ['account_code' => '2031', 'dr' => 0, 'cr' => $totalESI];
        }
        if ($totalTDS > 0) {
            $journalLines[] = ['account_code' => '2032', 'dr' => 0, 'cr' => $totalTDS];
        }
        if ($totalProfTax > 0) {
            $journalLines[] = ['account_code' => '2033', 'dr' => 0, 'cr' => $totalProfTax];
        }
        if ($totalOtherDed > 0) {
            $journalLines[] = ['account_code' => '2034', 'dr' => 0, 'cr' => $totalOtherDed];
        }

        $journalId = $this->_create_acct_journal($narration, $journalLines, $runId);

        // Store expense journal ID in run
        $this->firebase->update($this->_payroll_runs($runId), [
            'expense_journal_id' => $journalId,
        ]);

        $this->_log_payroll('generated', $runId, [
            'month' => $month, 'year' => $year,
            'staff_count' => $staffCount, 'total_net' => $totalNet,
            'journal_id' => $journalId,
        ]);

        $this->json_success([
            'id'      => $runId,
            'message' => "Payroll generated for {$month} {$year}: {$staffCount} staff, Net: {$totalNet}.",
            'summary' => [
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net'        => $totalNet,
                'staff_count'      => $staffCount,
                'working_days'     => $workingDays,
                'journal_id'       => $journalId,
            ],
        ]);
    }

    /**
     * GET — Get payroll slips for a run. ?run_id=PR0001
     */
    public function get_payroll_slips()
    {
        $this->_require_role(self::ADMIN_ROLES, 'get_payroll_slips');

        $runId = trim($this->input->get('run_id') ?? '');
        if ($runId === '') {
            $this->json_error('Payroll run ID is required.');
        }
        $runId = $this->safe_path_segment($runId, 'run_id');

        // Verify run exists
        $run = $this->firebase->get($this->_payroll_runs($runId));
        if (!is_array($run)) {
            $this->json_error('Payroll run not found.');
        }

        $slips = $this->firebase->get($this->_payroll_slips($runId));
        $list = [];
        if (is_array($slips)) {
            foreach ($slips as $staffId => $s) {
                $s['staff_id'] = $staffId;
                $list[] = $s;
            }
        }

        // Sort by staff name
        usort($list, function ($a, $b) {
            return strcmp($a['staff_name'] ?? '', $b['staff_name'] ?? '');
        });

        $this->json_success([
            'run'   => array_merge($run, ['id' => $runId]),
            'slips' => $list,
        ]);
    }

    /**
     * POST — Finalize a payroll run (lock it from edits).
     * Params: run_id
     */
    public function finalize_payroll()
    {
        $this->_require_role(self::ADMIN_ROLES, 'finalize_payroll');

        $runId = $this->safe_path_segment(trim($this->input->post('run_id') ?? ''), 'run_id');

        $run = $this->firebase->get($this->_payroll_runs($runId));
        if (!is_array($run)) {
            $this->json_error('Payroll run not found.');
        }
        if (($run['status'] ?? '') !== 'Draft') {
            $this->json_error('Only Draft payroll runs can be finalized.');
        }

        $this->firebase->update($this->_payroll_runs($runId), [
            'status'       => 'Finalized',
            'finalized_at' => date('c'),
            'finalized_by' => $this->admin_name,
        ]);

        // Batch-update all slip statuses in a single call
        $slips = $this->firebase->get($this->_payroll_slips($runId));
        if (is_array($slips)) {
            $statusPatch = [];
            foreach ($slips as $staffId => $s) {
                $statusPatch["{$staffId}/status"] = 'Finalized';
            }
            $this->firebase->update($this->_payroll_slips($runId), $statusPatch);
        }

        $this->_log_payroll('finalized', $runId);

        $this->json_success(['message' => 'Payroll run finalized.']);
    }

    /**
     * POST — Mark payroll as paid and create payment journal entry.
     * Params: run_id, payment_mode (Cash|Bank)
     *
     * Creates accounting entries:
     *   Debit  Salary Payable (2020) — reduces liability (net salary only)
     *   Credit Cash (1010) or Bank (1020) — payment out
     *
     * NOTE: Statutory deductions (PF/ESI/TDS) were credited to separate liability
     * accounts (2030/2031/2032) during accrual in generate_payroll(). Those accounts
     * should be cleared separately when the statutory payments are actually remitted
     * to the government (e.g., DR 2030 PF Payable / CR 1020 Bank).
     */
    public function mark_payroll_paid()
    {
        $this->_require_role(self::ADMIN_ROLES, 'mark_payroll_paid');

        $runId       = $this->safe_path_segment(trim($this->input->post('run_id') ?? ''), 'run_id');
        $paymentMode = trim($this->input->post('payment_mode') ?? 'Bank');

        if (!in_array($paymentMode, ['Cash', 'Bank'], true)) {
            $paymentMode = 'Bank';
        }

        $run = $this->firebase->get($this->_payroll_runs($runId));
        if (!is_array($run)) {
            $this->json_error('Payroll run not found.');
        }
        if (($run['status'] ?? '') !== 'Finalized') {
            $this->json_error('Only Finalized payroll runs can be marked as paid.');
        }

        $totalNet = (float) ($run['total_net'] ?? 0);
        $month    = $run['month'] ?? '';
        $year     = $run['year'] ?? '';

        // ── Create payment journal entry ──
        $payAccount = ($paymentMode === 'Bank') ? '1020' : '1010';
        $this->_validate_accounts(['2020', $payAccount]);

        $narration = "Salary payment - {$month} {$year}";
        $journalId = $this->_create_acct_journal($narration, [
            ['account_code' => '2020',        'dr' => $totalNet, 'cr' => 0],
            ['account_code' => $payAccount,   'dr' => 0,         'cr' => $totalNet],
        ], $runId);

        // Update run status
        $this->firebase->update($this->_payroll_runs($runId), [
            'status'       => 'Paid',
            'paid_at'      => date('c'),
            'paid_by'      => $this->admin_name,
            'payment_mode' => $paymentMode,
            'journal_id'   => $journalId,
        ]);

        // Batch-update all slip statuses in a single call
        $slips = $this->firebase->get($this->_payroll_slips($runId));
        if (is_array($slips)) {
            $statusPatch = [];
            foreach ($slips as $staffId => $s) {
                $statusPatch["{$staffId}/status"] = 'Paid';
            }
            $this->firebase->update($this->_payroll_slips($runId), $statusPatch);
        }

        $this->_log_payroll('paid', $runId, [
            'payment_mode' => $paymentMode, 'total_net' => $totalNet,
            'journal_id' => $journalId,
        ]);

        $this->json_success([
            'message'    => "Payroll marked as paid via {$paymentMode}. Journal: {$journalId}.",
            'journal_id' => $journalId,
        ]);
    }

    /**
     * GET — Get a single payslip. ?run_id=PR0001&staff_id=XXX
     */
    public function get_payslip()
    {
        $this->_require_role(self::ADMIN_ROLES, 'get_payslip');

        $runId   = trim($this->input->get('run_id') ?? '');
        $staffId = trim($this->input->get('staff_id') ?? '');

        if ($runId === '' || $staffId === '') {
            $this->json_error('Both run_id and staff_id are required.');
        }
        $runId   = $this->safe_path_segment($runId, 'run_id');
        $staffId = $this->safe_path_segment($staffId, 'staff_id');

        // Get run info
        $run = $this->firebase->get($this->_payroll_runs($runId));
        if (!is_array($run)) {
            $this->json_error('Payroll run not found.');
        }

        // Get slip
        $slip = $this->firebase->get($this->_payroll_slips($runId, $staffId));
        if (!is_array($slip)) {
            $this->json_error('Payslip not found for this staff member.');
        }

        // Get staff profile for additional info
        $staffProfile = $this->firebase->get("Users/Teachers/{$this->school_name}/{$staffId}");

        $this->json_success([
            'run' => [
                'id'     => $runId,
                'month'  => $run['month'] ?? '',
                'year'   => $run['year'] ?? '',
                'status' => $run['status'] ?? '',
            ],
            'slip' => array_merge($slip, ['staff_id' => $staffId]),
            'staff_profile' => is_array($staffProfile) ? [
                'name'          => $staffProfile['Name'] ?? '',
                'email'         => $staffProfile['Email'] ?? '',
                'phone'         => $staffProfile['Phone'] ?? '',
                'department'    => $staffProfile['Department'] ?? '',
                'position'      => $staffProfile['Position'] ?? '',
                'qualification' => $staffProfile['Qualification'] ?? '',
            ] : null,
        ]);
    }

    /**
     * POST — Delete a payroll run (Draft only).
     * Params: run_id
     */
    public function delete_payroll_run()
    {
        $this->_require_role(self::ADMIN_ROLES, 'delete_payroll_run');

        $runId = $this->safe_path_segment(trim($this->input->post('run_id') ?? ''), 'run_id');

        $run = $this->firebase->get($this->_payroll_runs($runId));
        if (!is_array($run)) {
            $this->json_error('Payroll run not found.');
        }
        if (($run['status'] ?? '') !== 'Draft') {
            $this->json_error('Only Draft payroll runs can be deleted.');
        }

        // Audit log before deletion (run data will be gone after)
        $this->_log_payroll('deleted', $runId, [
            'month' => $run['month'] ?? '', 'year' => $run['year'] ?? '',
            'total_net' => $run['total_net'] ?? 0,
        ]);

        // Delete slips
        $this->firebase->delete($this->_payroll_slips($runId));

        // Reverse the expense journal entry if it exists (soft-delete + balance reversal)
        $expenseJournalId = $run['expense_journal_id'] ?? '';
        if ($expenseJournalId !== '') {
            $this->_delete_acct_journal($expenseJournalId);
        }

        // Delete run
        $this->firebase->delete($this->_payroll_runs($runId));

        $this->json_success(['message' => 'Payroll run and associated slips deleted.']);
    }

    // ====================================================================
    //  APPRAISALS
    // ====================================================================

    /**
     * GET — List appraisals. Optional filters: ?staff_id=XXX&status=Draft
     */
    public function get_appraisals()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_appraisals');

        $filterStaffId = trim($this->input->get('staff_id') ?? '');
        $filterStatus  = trim($this->input->get('status') ?? '');

        $appraisals = $this->firebase->get($this->_appraisals());
        $list = [];
        if (is_array($appraisals)) {
            foreach ($appraisals as $id => $a) {
                if ($filterStaffId !== '' && isset($a['staff_id']) && $a['staff_id'] !== $filterStaffId) {
                    continue;
                }
                if ($filterStatus !== '' && isset($a['status']) && $a['status'] !== $filterStatus) {
                    continue;
                }
                $a['id'] = $id;
                $list[] = $a;
            }
        }

        // Sort by created_at descending
        usort($list, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $this->json_success(['appraisals' => $list]);
    }

    /**
     * POST — Create or update an appraisal (Draft state only for updates).
     */
    public function save_appraisal()
    {
        $this->_require_role(self::HR_ROLES, 'save_appraisal');

        $id               = trim($this->input->post('id') ?? '');
        $staffId          = $this->safe_path_segment(trim($this->input->post('staff_id') ?? ''), 'staff_id');
        $period           = trim($this->input->post('period') ?? '');
        // Accept both field-name conventions (view sends teaching/behavior/innovation)
        $teachingQuality  = (float) ($this->input->post('teaching') ?? $this->input->post('teaching_quality') ?? 0);
        $punctuality      = (float) ($this->input->post('punctuality') ?? 0);
        $studentFeedback  = (float) ($this->input->post('behavior') ?? $this->input->post('student_feedback') ?? 0);
        $initiative       = (float) ($this->input->post('innovation') ?? $this->input->post('initiative') ?? 0);
        $teamwork         = (float) ($this->input->post('teamwork') ?? 0);
        $overallRating    = (float) ($this->input->post('overall_rating') ?? 0);
        $strengths        = trim($this->input->post('strengths') ?? '');
        $areasImprovement = trim($this->input->post('areas_of_improvement') ?? '');
        $recommendation   = trim($this->input->post('recommendation') ?? 'none');
        $comments         = trim($this->input->post('comments') ?? '');
        $goals            = trim($this->input->post('goals') ?? '');

        if ($period === '') {
            $this->json_error('Appraisal period is required (e.g., "2025-26 Term 1").');
        }

        // Validate ratings (0-10 scale)
        $ratings = [$teachingQuality, $punctuality, $studentFeedback, $initiative, $teamwork, $overallRating];
        foreach ($ratings as $r) {
            if ($r < 0 || $r > 10) {
                $this->json_error('Ratings must be between 0 and 10.');
            }
        }

        // Verify staff exists
        $staffProfile = $this->firebase->get("Users/Teachers/{$this->school_name}/{$staffId}");
        if (!is_array($staffProfile)) {
            $this->json_error('Staff member not found.');
        }
        $staffName = $staffProfile['Name'] ?? $staffId;

        $now   = date('c');
        $isNew = ($id === '');

        if ($isNew) {
            $id = $this->_next_id('APR', 'Appraisal');
        } else {
            // Only allow editing Draft appraisals
            $existing = $this->firebase->get($this->_appraisals($id));
            if (!is_array($existing)) {
                $this->json_error('Appraisal not found.');
            }
            if (($existing['status'] ?? '') !== 'Draft') {
                $this->json_error('Only Draft appraisals can be edited.');
            }
        }

        $data = [
            'staff_id'              => $staffId,
            'staff_name'            => $staffName,
            'period'                => $period,
            'reviewer_id'           => $this->input->post('reviewer_id') ? $this->safe_path_segment(trim($this->input->post('reviewer_id')), 'reviewer_id') : $this->admin_id,
            'reviewer_name'         => $this->admin_name,
            'teaching_quality'      => $teachingQuality,
            'punctuality'           => $punctuality,
            'student_feedback'      => $studentFeedback,
            'initiative'            => $initiative,
            'teamwork'              => $teamwork,
            'overall_rating'        => $overallRating,
            'strengths'             => $strengths,
            'areas_of_improvement'  => $areasImprovement,
            'recommendation'        => $recommendation,
            'comments'              => $comments,
            'goals'                 => $goals,
            'status'                => 'Draft',
            'updated_at'            => $now,
        ];
        if ($isNew) {
            $data['created_at'] = $now;
        }

        $this->firebase->set($this->_appraisals($id), $data);
        $this->json_success(['id' => $id, 'message' => $isNew ? 'Appraisal created.' : 'Appraisal updated.']);
    }

    /**
     * POST — Submit a draft appraisal for review.
     * Params: id
     */
    public function submit_appraisal()
    {
        $this->_require_role(self::HR_ROLES, 'submit_appraisal');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        $appraisal = $this->firebase->get($this->_appraisals($id));
        if (!is_array($appraisal)) {
            $this->json_error('Appraisal not found.');
        }
        if (($appraisal['status'] ?? '') !== 'Draft') {
            $this->json_error('Only Draft appraisals can be submitted.');
        }

        // Validate that key fields are filled
        $overallRating = (float) ($appraisal['overall_rating'] ?? 0);
        if ($overallRating <= 0) {
            $this->json_error('Overall rating must be set before submitting.');
        }

        $this->firebase->update($this->_appraisals($id), [
            'status'     => 'Submitted',
            'updated_at' => date('c'),
        ]);

        $this->json_success(['message' => 'Appraisal submitted for review.']);
    }

    /**
     * POST — Mark a submitted appraisal as reviewed (final).
     * Params: id, comments (optional additional reviewer comments)
     */
    public function review_appraisal()
    {
        $this->_require_role(self::HR_ROLES, 'review_appraisal');

        $id       = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');
        $comments = trim($this->input->post('comments') ?? '');

        $appraisal = $this->firebase->get($this->_appraisals($id));
        if (!is_array($appraisal)) {
            $this->json_error('Appraisal not found.');
        }
        if (($appraisal['status'] ?? '') !== 'Submitted') {
            $this->json_error('Only Submitted appraisals can be reviewed.');
        }

        $update = [
            'status'     => 'Reviewed',
            'updated_at' => date('c'),
        ];
        if ($comments !== '') {
            $existingComments = $appraisal['comments'] ?? '';
            $update['comments'] = $existingComments . "\n[Review: " . date('Y-m-d') . '] ' . $comments;
        }

        $this->firebase->update($this->_appraisals($id), $update);
        $this->json_success(['message' => 'Appraisal marked as reviewed.']);
    }

    /**
     * POST — Delete an appraisal (only Draft).
     * Params: id
     */
    public function delete_appraisal()
    {
        $this->_require_role(self::HR_ROLES, 'delete_appraisal');

        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'id');

        $appraisal = $this->firebase->get($this->_appraisals($id));
        if (!is_array($appraisal)) {
            $this->json_error('Appraisal not found.');
        }
        if (($appraisal['status'] ?? '') !== 'Draft') {
            $this->json_error('Only Draft appraisals can be deleted.');
        }

        $this->firebase->delete($this->_appraisals($id));
        $this->json_success(['message' => 'Appraisal deleted.']);
    }

    // ====================================================================
    //  UTILITY
    // ====================================================================

    /**
     * GET — Returns staff from session roster for dropdowns.
     * Returns: [{id, name, department, position, email, phone}]
     */
    public function get_staff_list()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_staff_list');

        // Get roster
        $roster = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        if (!is_array($roster) || empty($roster)) {
            $this->json_success(['staff' => []]);
            return;
        }

        // Get profiles
        $profiles = $this->firebase->get("Users/Teachers/{$this->school_name}");
        $list = [];

        foreach ($roster as $staffId => $rosterData) {
            $profile = (is_array($profiles) && isset($profiles[$staffId]) && is_array($profiles[$staffId]))
                ? $profiles[$staffId]
                : [];

            $list[] = [
                'id'         => $staffId,
                'name'       => $profile['Name'] ?? $staffId,
                'department' => $profile['Department'] ?? '',
                'position'   => $profile['Position'] ?? '',
                'email'      => $profile['Email'] ?? '',
                'phone'      => $profile['Phone'] ?? '',
            ];
        }

        // Sort alphabetically by name
        usort($list, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $this->json_success(['staff' => $list]);
    }

    // ====================================================================
    //  REPORTS
    // ====================================================================

    /**
     * GET — Get HR summary report for export/print.
     * ?type=staff|leaves|payroll|departments
     */
    public function get_report()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_report');

        $type = trim($this->input->get('type') ?? 'staff');

        switch ($type) {
            case 'staff':
                return $this->_report_staff();
            case 'leaves':
                return $this->_report_leaves();
            case 'payroll':
                return $this->_report_payroll();
            case 'departments':
                return $this->_report_departments();
            default:
                $this->json_error('Invalid report type. Use: staff, leaves, payroll, departments.');
        }
    }

    /**
     * Staff report: roster with departments and salary info.
     */
    private function _report_staff()
    {
        $roster   = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");
        $profiles = $this->firebase->get("Users/Teachers/{$this->school_name}");
        $salaries = $this->firebase->get($this->_salary());

        $list = [];
        if (is_array($roster)) {
            foreach ($roster as $staffId => $rd) {
                $p = (is_array($profiles) && isset($profiles[$staffId]) && is_array($profiles[$staffId]))
                    ? $profiles[$staffId]
                    : [];
                $s = (is_array($salaries) && isset($salaries[$staffId]) && is_array($salaries[$staffId]))
                    ? $salaries[$staffId]
                    : [];

                $gross = 0;
                if (!empty($s)) {
                    $gross = (float) ($s['basic'] ?? 0)
                        + (float) ($s['hra'] ?? 0)
                        + (float) ($s['da'] ?? 0)
                        + (float) ($s['ta'] ?? 0)
                        + (float) ($s['medical'] ?? 0)
                        + (float) ($s['other_allowances'] ?? 0);
                }

                $list[] = [
                    'staff_id'      => $staffId,
                    'name'          => $p['Name'] ?? $staffId,
                    'department'    => $p['Department'] ?? '',
                    'position'      => $p['Position'] ?? '',
                    'phone'         => $p['Phone'] ?? '',
                    'email'         => $p['Email'] ?? '',
                    'qualification' => $p['Qualification'] ?? '',
                    'gross_salary'  => round($gross, 2),
                    'has_salary'    => !empty($s),
                ];
            }
        }

        usort($list, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $this->json_success(['report' => 'staff', 'data' => $list, 'total' => count($list)]);
    }

    /**
     * Leave report: summary of all leave requests and balances.
     */
    private function _report_leaves()
    {
        $year     = trim($this->input->get('year') ?? date('Y'));
        $requests = $this->firebase->get($this->_leave_req());
        $balances = $this->firebase->get($this->_leave_bal($year));

        $summary = [
            'total_requests' => 0,
            'pending'        => 0,
            'approved'       => 0,
            'rejected'       => 0,
            'cancelled'      => 0,
        ];

        $byStaff = [];
        if (is_array($requests)) {
            foreach ($requests as $r) {
                $summary['total_requests']++;
                $status = strtolower($r['status'] ?? '');
                if (isset($summary[$status])) {
                    $summary[$status]++;
                }

                $sid = $r['staff_id'] ?? '';
                if ($sid !== '') {
                    if (!isset($byStaff[$sid])) {
                        $byStaff[$sid] = [
                            'staff_name'    => $r['staff_name'] ?? $sid,
                            'total_days'    => 0,
                            'approved_days' => 0,
                        ];
                    }
                    $days = (int) ($r['days'] ?? 0);
                    $byStaff[$sid]['total_days'] += $days;
                    if (($r['status'] ?? '') === 'Approved') {
                        $byStaff[$sid]['approved_days'] += $days;
                    }
                }
            }
        }

        $this->json_success([
            'report'   => 'leaves',
            'year'     => $year,
            'summary'  => $summary,
            'by_staff' => array_values($byStaff),
            'balances' => is_array($balances) ? $balances : [],
        ]);
    }

    /**
     * Payroll report: all runs for this session.
     */
    private function _report_payroll()
    {
        $runs = $this->firebase->get($this->_payroll_runs());
        $list = [];
        $totalPaid = 0;

        if (is_array($runs)) {
            foreach ($runs as $id => $r) {
                $r['id'] = $id;
                $list[] = $r;
                if (($r['status'] ?? '') === 'Paid') {
                    $totalPaid += (float) ($r['total_net'] ?? 0);
                }
            }
        }

        usort($list, function ($a, $b) {
            return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');
        });

        $this->json_success([
            'report'     => 'payroll',
            'session'    => $this->session_year,
            'runs'       => $list,
            'total_paid' => round($totalPaid, 2),
            'run_count'  => count($list),
        ]);
    }

    /**
     * Departments report: departments with staff counts.
     */
    private function _report_departments()
    {
        $depts    = $this->firebase->get($this->_dept());
        $profiles = $this->firebase->get("Users/Teachers/{$this->school_name}");
        $roster   = $this->firebase->get("Schools/{$this->school_name}/{$this->session_year}/Teachers");

        // Count staff per department
        $countByDept = [];
        if (is_array($roster) && is_array($profiles)) {
            foreach ($roster as $sid => $rd) {
                $dept = (isset($profiles[$sid]) && is_array($profiles[$sid]))
                    ? ($profiles[$sid]['Department'] ?? 'Unassigned')
                    : 'Unassigned';
                $countByDept[$dept] = ($countByDept[$dept] ?? 0) + 1;
            }
        }

        $list = [];
        $assignedDeptNames = [];
        if (is_array($depts)) {
            foreach ($depts as $id => $d) {
                $dName = $d['name'] ?? '';
                $d['id']          = $id;
                $d['staff_count'] = $countByDept[$dName] ?? 0;
                $list[] = $d;
                $assignedDeptNames[] = $dName;
            }
        }

        // Add "Unassigned" or any departments not in the formal list
        foreach ($countByDept as $dName => $cnt) {
            if (!in_array($dName, $assignedDeptNames, true)) {
                $list[] = [
                    'id'          => '',
                    'name'        => $dName,
                    'staff_count' => $cnt,
                    'status'      => 'N/A',
                ];
            }
        }

        $this->json_success(['report' => 'departments', 'data' => $list]);
    }
}
