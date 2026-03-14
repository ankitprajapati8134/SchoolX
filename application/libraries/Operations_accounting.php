<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Operations_accounting — Shared accounting & ID generation helpers
 *
 * Provides:
 *   - validate_accounts()      — verify CoA accounts exist and are active
 *   - create_journal()         — create a double-entry journal with indices + balances
 *   - next_id()                — sequential ID generator via Firebase counters
 *   - search_students()        — cached student search (file cache, 5-min TTL)
 *
 * Used by: Library, Inventory, Assets, Transport, Hostel controllers.
 * Eliminates code duplication across Operations sub-modules.
 *
 * Matches the journal format from Hr.php and Accounting.php:
 *   Accounts:  Schools/{school}/Accounts/ChartOfAccounts/{code}
 *   Ledger:    Schools/{school}/{year}/Accounts/Ledger/{entryId}
 *   Index:     Schools/{school}/{year}/Accounts/Ledger_index/by_date|by_account
 *   Balances:  Schools/{school}/{year}/Accounts/Closing_balances/{code}
 *   Counter:   Schools/{school}/{year}/Accounts/Voucher_counters/{type}
 */
class Operations_accounting
{
    /** @var object Firebase library instance */
    private $firebase;

    /** @var string School key (SCH_XXXXXX) */
    private $school_name;

    /** @var string Key for Users/Parents/ path — school_code for legacy, school_id for SCH_ schools */
    private $parent_db_key;

    /** @var string Session year (YYYY-YY) */
    private $session_year;

    /** @var string Admin ID */
    private $admin_id;

    /** @var object CI controller instance (for json_error) */
    private $CI;

    /**
     * Initialize with controller context.
     *
     * @param object $firebase       Firebase library instance
     * @param string $school_name    School key (SCH_XXXXXX)
     * @param string $session_year   Session year (e.g. 2025-26)
     * @param string $admin_id       Current admin ID
     * @param object $CI             Controller instance (must have json_error())
     * @param string $parent_db_key  Key for Users/Parents/ path (defaults to school_name)
     */
    public function init($firebase, string $school_name, string $session_year, string $admin_id, $CI, string $parent_db_key = ''): void
    {
        $this->firebase       = $firebase;
        $this->school_name    = $school_name;
        $this->parent_db_key  = $parent_db_key !== '' ? $parent_db_key : $school_name;
        $this->session_year   = $session_year;
        $this->admin_id       = $admin_id;
        $this->CI             = $CI;
    }

    // ====================================================================
    //  SEQUENTIAL ID GENERATION
    // ====================================================================

    /**
     * Generate a sequential ID from a Firebase counter.
     *
     * @param string $counterPath Full Firebase path to the counter node
     * @param string $prefix      ID prefix (e.g. 'BK', 'ISS', 'VH')
     * @param int    $pad         Zero-padding width (default 4 → BK0001)
     * @return string             Generated ID (e.g. BK0001)
     */
    public function next_id(string $counterPath, string $prefix, int $pad = 4): string
    {
        $cur  = (int) ($this->firebase->get($counterPath) ?? 0);
        $next = $cur + 1;
        $this->firebase->set($counterPath, $next);
        return $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
    }

    // ====================================================================
    //  CACHED STUDENT SEARCH
    // ====================================================================

    /**
     * Search students with file-cache backed lookup.
     *
     * Loads the full student list from Firebase once, caches a lightweight
     * index (id, name, class, section, user_id) for 5 minutes, and filters
     * in PHP. All Operations controllers share this single cache entry,
     * eliminating repeated full-tree downloads on every typeahead keystroke.
     *
     * @param string $query   Search term (min 2 chars, matched against name and id)
     * @param int    $limit   Max results to return (default 20)
     * @return array          Array of matching student records
     */
    public function search_students(string $query, int $limit = 20): array
    {
        $q = strtolower(trim($query));
        if (strlen($q) < 2) {
            $this->CI->json_error('Enter at least 2 characters.');
        }

        $dbKey = $this->parent_db_key;
        $cacheKey = 'ops_students_' . md5($dbKey);

        // Try file cache first (5-minute TTL)
        $CI =& get_instance();
        $CI->load->driver('cache', ['adapter' => 'file']);
        $index = $CI->cache->get($cacheKey);

        if ($index === false) {
            // Cache miss — load from Firebase and build lightweight index
            $students = $this->firebase->get("Users/Parents/{$dbKey}");
            $index = [];
            if (is_array($students)) {
                foreach ($students as $sid => $s) {
                    if (!is_array($s)) continue;
                    $index[] = [
                        'id'      => $sid,
                        'name'    => $s['Name'] ?? $sid,
                        'class'   => $s['Class'] ?? '',
                        'section' => $s['Section'] ?? '',
                        'user_id' => $s['User Id'] ?? $sid,
                    ];
                }
            }
            // Cache for 5 minutes (300 seconds)
            $CI->cache->save($cacheKey, $index, 300);
        }

        // Filter cached index
        $results = [];
        foreach ($index as $s) {
            $nameMatch = strpos(strtolower($s['name']), $q) !== false;
            $idMatch   = strpos(strtolower($s['id']), $q) !== false;
            $uidMatch  = strpos(strtolower($s['user_id'] ?? ''), $q) !== false;
            if ($nameMatch || $idMatch || $uidMatch) {
                $results[] = $s;
                if (count($results) >= $limit) break;
            }
        }

        return $results;
    }

    // ====================================================================
    //  PAGINATION HELPER
    // ====================================================================

    /**
     * Apply pagination to an array and return paginated result with metadata.
     *
     * Backward-compatible: if no page param is provided, returns all data
     * with page=1 and total=count. Existing UIs that ignore pagination
     * fields continue to work unchanged.
     *
     * @param array  $list      Full list of records
     * @param string $dataKey   Response key name (e.g. 'books', 'items', 'assets')
     * @param int|null $page    Page number (null = return all)
     * @param int    $limit     Records per page (default 50, max 200)
     * @return array            ['dataKey' => [...], 'page' => int, 'limit' => int, 'total' => int]
     */
    public function paginate(array $list, string $dataKey, $page = null, int $limit = 50): array
    {
        $total = count($list);
        $limit = max(1, min(200, $limit));

        if ($page !== null) {
            $page = max(1, (int) $page);
            $list = array_slice($list, ($page - 1) * $limit, $limit);
        } else {
            $page = 1;
            $limit = $total;
        }

        return [
            $dataKey => array_values($list),
            'page'   => $page,
            'limit'  => $limit,
            'total'  => $total,
        ];
    }

    // ====================================================================
    //  ACCOUNT VALIDATION
    // ====================================================================

    /**
     * Validate that accounting accounts exist and are active.
     * Calls json_error() and exits if any are missing/inactive.
     *
     * Fetches ChartOfAccounts once and validates all codes from memory
     * instead of one Firebase read per code (N+1 fix).
     *
     * @param array $codes Array of account codes (e.g. ['1010', '4060'])
     */
    public function validate_accounts(array $codes): void
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
            $this->CI->json_error(
                'Missing or inactive accounts: ' . implode(', ', $missing)
                . '. Set them up in Accounting first.'
            );
        }
    }

    // ====================================================================
    //  JOURNAL CREATION
    // ====================================================================

    /**
     * Create a journal entry compatible with the Accounting module.
     *
     * Writes:
     *   - Ledger entry at {year}/Accounts/Ledger/{entryId}
     *   - Date index at {year}/Accounts/Ledger_index/by_date/{date}/{entryId}
     *   - Account index at {year}/Accounts/Ledger_index/by_account/{code}/{entryId}
     *   - Closing balances at {year}/Accounts/Closing_balances/{code}
     *
     * @param string $narration  Human-readable description
     * @param array  $lines      Array of ['account_code'=>..., 'dr'=>..., 'cr'=>...]
     * @param string $source     Source module name (e.g. 'Library', 'Inventory', 'Assets')
     * @param string $sourceRef  Reference ID (e.g. fine ID, purchase ID)
     * @return string            The generated entry ID
     */
    public function create_journal(string $narration, array $lines, string $source = '', string $sourceRef = ''): string
    {
        // Validate minimum 2 lines for double-entry
        if (count($lines) < 2) {
            $this->CI->json_error('Journal entry requires at least 2 line items.');
        }

        $bp = "Schools/{$this->school_name}/{$this->session_year}";

        // Fetch CoA once for account name resolution and group-account guard
        $coaBase  = "Schools/{$this->school_name}/Accounts/ChartOfAccounts";
        $coa      = $this->firebase->get($coaBase);
        if (!is_array($coa)) $coa = [];

        $totalDr  = 0;
        $totalCr  = 0;
        $affected = [];

        foreach ($lines as &$ln) {
            $dr = round((float) ($ln['dr'] ?? 0), 2);
            $cr = round((float) ($ln['cr'] ?? 0), 2);
            $ln['dr'] = $dr;
            $ln['cr'] = $cr;
            $totalDr += $dr;
            $totalCr += $cr;

            // Resolve account name from already-fetched CoA
            $acCode = $ln['account_code'] ?? '';
            $acct = $coa[$acCode] ?? null;
            $ln['account_name'] = is_array($acct) ? ($acct['name'] ?? $acCode) : $acCode;

            // Guard: reject group accounts
            if (is_array($acct) && !empty($acct['is_group'])) {
                $this->CI->json_error("Account {$acCode} is a group account — cannot post directly.");
            }

            // Aggregate by account code
            if ($acCode !== '') {
                $affected[$acCode] = [
                    'dr' => ($affected[$acCode]['dr'] ?? 0) + $dr,
                    'cr' => ($affected[$acCode]['cr'] ?? 0) + $cr,
                ];
            }
        }
        unset($ln);

        // Double-entry validation: total debit must equal total credit
        if (abs($totalDr - $totalCr) > 0.01) {
            $this->CI->json_error("Unbalanced journal: Debit ({$totalDr}) does not equal Credit ({$totalCr}).");
        }

        // Generate voucher number (after validation to avoid wasting sequence numbers)
        $counterPath = "{$bp}/Accounts/Voucher_counters/Journal";
        $seq = (int) ($this->firebase->get($counterPath) ?? 0) + 1;
        $this->firebase->set($counterPath, $seq);
        $voucherNo = 'JV-' . str_pad($seq, 6, '0', STR_PAD_LEFT);

        // Generate entry ID
        $entryId = 'JE_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));

        $entry = [
            'date'         => date('Y-m-d'),
            'voucher_no'   => $voucherNo,
            'voucher_type' => 'Journal',
            'narration'    => $narration,
            'lines'        => array_values($lines),
            'total_dr'     => round($totalDr, 2),
            'total_cr'     => round($totalCr, 2),
            'source'       => $source,
            'source_ref'   => $sourceRef ?: null,
            'is_finalized' => false,
            'status'       => 'active',
            'created_by'   => $this->admin_id,
            'created_at'   => date('c'),
        ];

        // Write ledger entry
        $this->firebase->set("{$bp}/Accounts/Ledger/{$entryId}", $entry);

        // Write indices
        $today = date('Y-m-d');
        $this->firebase->set("{$bp}/Accounts/Ledger_index/by_date/{$today}/{$entryId}", true);
        foreach (array_keys($affected) as $acCode) {
            $this->firebase->set("{$bp}/Accounts/Ledger_index/by_account/{$acCode}/{$entryId}", true);
        }

        // Update closing balances
        foreach ($affected as $code => $amounts) {
            $balPath = "{$bp}/Accounts/Closing_balances/{$code}";
            $current = $this->firebase->get($balPath);
            if (!is_array($current)) $current = ['period_dr' => 0, 'period_cr' => 0];
            $this->firebase->set($balPath, [
                'period_dr'     => round((float) ($current['period_dr'] ?? 0) + $amounts['dr'], 2),
                'period_cr'     => round((float) ($current['period_cr'] ?? 0) + $amounts['cr'], 2),
                'last_computed' => date('c'),
            ]);
        }

        return $entryId;
    }

    // ====================================================================
    //  FEE JOURNAL ENTRY
    // ====================================================================

    /**
     * Create a journal entry for a fee payment.
     *
     * Single source of truth for fee accounting — called by Fees.php.
     * Payment mode selects correct debit account (cash or bank).
     * Errors are logged but never block fee submission (returns null).
     *
     * @param array $params Keys: school_name, session_year, date, amount, payment_mode,
     *                       bank_code, receipt_no, student_name, student_id, class, admin_id
     * @return string|null  Entry ID or null on failure
     */
    public function create_fee_journal(array $params): ?string
    {
        $school   = $params['school_name'];
        $session  = $params['session_year'];
        $date     = $params['date'] ?? date('Y-m-d');
        $amount   = round((float) ($params['amount'] ?? 0), 2);
        $payMode  = strtolower(trim($params['payment_mode'] ?? 'cash'));
        $bankCode = trim($params['bank_code'] ?? '');
        $receipt  = $params['receipt_no'] ?? '';
        $student  = $params['student_name'] ?? '';
        $stuId    = $params['student_id'] ?? '';
        $class    = $params['class'] ?? '';
        $adminId  = $params['admin_id'] ?? $this->admin_id;

        if ($amount <= 0) return null;

        // Single fetch: full CoA
        $coaPath = "Schools/{$school}/Accounts/ChartOfAccounts";
        $coa = $this->firebase->get($coaPath);
        if (!is_array($coa) || empty($coa)) return null; // Accounting not set up

        // Select cash/bank account based on payment mode
        if ($bankCode && isset($coa[$bankCode])) {
            $cashBankCode = $bankCode;
        } elseif (in_array($payMode, ['bank', 'cheque', 'upi', 'neft', 'rtgs', 'online'])) {
            $cashBankCode = '1010'; // fallback
            foreach ($coa as $code => $acct) {
                if (!empty($acct['is_bank']) && ($acct['status'] ?? '') === 'active') {
                    $cashBankCode = $code;
                    break;
                }
            }
        } else {
            $cashBankCode = '1010'; // Cash in Hand
        }

        $feeIncomeCode = '4010'; // Tuition Fees

        // Validate both accounts from already-fetched CoA
        $cashAcct = $coa[$cashBankCode] ?? null;
        $feeAcct  = $coa[$feeIncomeCode] ?? null;
        if (!is_array($cashAcct) || ($cashAcct['status'] ?? '') !== 'active') {
            log_message('error', "Fee journal: cash/bank account {$cashBankCode} missing/inactive for {$school}");
            return null;
        }
        if (!is_array($feeAcct) || ($feeAcct['status'] ?? '') !== 'active') {
            log_message('error', "Fee journal: fee income account {$feeIncomeCode} missing/inactive for {$school}");
            return null;
        }

        $narration = "Fee payment: {$student} ({$stuId}) - {$class}" . ($receipt ? " Rcpt#{$receipt}" : '');
        $bp = "Schools/{$school}/{$session}";

        // Generate entry ID
        $entryId = 'FE_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

        // Voucher counter
        $counterPath = "{$bp}/Accounts/Voucher_counters/Fee";
        $seq = (int) ($this->firebase->get($counterPath) ?? 0) + 1;
        $voucherNo = 'FV-' . str_pad($seq, 6, '0', STR_PAD_LEFT);

        $lines = [
            ['account_code' => $cashBankCode,  'account_name' => $cashAcct['name'] ?? $cashBankCode,  'dr' => $amount, 'cr' => 0,       'narration' => $narration],
            ['account_code' => $feeIncomeCode, 'account_name' => $feeAcct['name'] ?? $feeIncomeCode, 'dr' => 0,       'cr' => $amount, 'narration' => $narration],
        ];

        $entry = [
            'date'         => $date,
            'voucher_no'   => $voucherNo,
            'voucher_type' => 'Fee',
            'narration'    => $narration,
            'lines'        => $lines,
            'total_dr'     => $amount,
            'total_cr'     => $amount,
            'source'       => 'fee_payment',
            'source_ref'   => $receipt,
            'is_finalized' => false,
            'status'       => 'active',
            'created_by'   => $adminId,
            'created_at'   => date('c'),
        ];

        // Write ledger entry + counter
        $this->firebase->set("{$bp}/Accounts/Ledger/{$entryId}", $entry);
        $this->firebase->set($counterPath, $seq);

        // Write indices
        $this->firebase->set("{$bp}/Accounts/Ledger_index/by_date/{$date}/{$entryId}", true);
        $this->firebase->set("{$bp}/Accounts/Ledger_index/by_account/{$cashBankCode}/{$entryId}", true);
        $this->firebase->set("{$bp}/Accounts/Ledger_index/by_account/{$feeIncomeCode}/{$entryId}", true);

        // Update closing balances
        $balPath = "{$bp}/Accounts/Closing_balances";
        foreach ([$cashBankCode, $feeIncomeCode] as $ac) {
            $cur = $this->firebase->get("{$balPath}/{$ac}");
            if (!is_array($cur)) $cur = ['period_dr' => 0, 'period_cr' => 0];
            $pDr = (float) ($cur['period_dr'] ?? 0);
            $pCr = (float) ($cur['period_cr'] ?? 0);
            if ($ac === $cashBankCode) {
                $pDr += $amount;
            } else {
                $pCr += $amount;
            }
            $this->firebase->set("{$balPath}/{$ac}", [
                'period_dr' => round($pDr, 2), 'period_cr' => round($pCr, 2), 'last_computed' => date('c'),
            ]);
        }

        return $entryId;
    }

    // ====================================================================
    //  REFUND JOURNAL ENTRY
    // ====================================================================

    /**
     * Create a journal entry for a fee refund.
     *
     * Reversal of fee payment: Dr Fee Income (4010), Cr Cash/Bank (1010).
     * Uses create_journal() for full validation (dr==cr, group guard, indices).
     *
     * @param array $params Keys: student_name, student_id, class, amount,
     *                       refund_mode, refund_id, receipt_no
     * @return string|null  Entry ID or null on failure
     */
    public function create_refund_journal(array $params): ?string
    {
        $amount     = round((float) ($params['amount'] ?? 0), 2);
        $refundMode = strtolower(trim($params['refund_mode'] ?? 'cash'));
        $student    = $params['student_name'] ?? '';
        $stuId      = $params['student_id'] ?? '';
        $class      = $params['class'] ?? '';
        $refId      = $params['refund_id'] ?? '';
        $origRcpt   = $params['receipt_no'] ?? '';

        if ($amount <= 0) return null;

        // Single fetch: full CoA
        $coaPath = "Schools/{$this->school_name}/Accounts/ChartOfAccounts";
        $coa = $this->firebase->get($coaPath);
        if (!is_array($coa) || empty($coa)) return null;

        // Select cash/bank account based on refund mode
        if (in_array($refundMode, ['bank_transfer', 'cheque', 'online', 'upi', 'neft'])) {
            $cashBankCode = '1010'; // fallback
            foreach ($coa as $code => $acct) {
                if (!empty($acct['is_bank']) && ($acct['status'] ?? '') === 'active') {
                    $cashBankCode = $code;
                    break;
                }
            }
        } else {
            $cashBankCode = '1010'; // Cash in Hand
        }

        $feeIncomeCode = '4010'; // Tuition Fees

        // Validate both accounts
        $cashAcct = $coa[$cashBankCode] ?? null;
        $feeAcct  = $coa[$feeIncomeCode] ?? null;
        if (!is_array($cashAcct) || ($cashAcct['status'] ?? '') !== 'active') {
            log_message('error', "Refund journal: cash/bank account {$cashBankCode} missing/inactive");
            return null;
        }
        if (!is_array($feeAcct) || ($feeAcct['status'] ?? '') !== 'active') {
            log_message('error', "Refund journal: fee income account {$feeIncomeCode} missing/inactive");
            return null;
        }

        $narration = "Fee refund: {$student} ({$stuId}) - {$class}"
            . ($origRcpt ? " OrigRcpt#{$origRcpt}" : '')
            . " Ref#{$refId}";

        $lines = [
            ['account_code' => $feeIncomeCode, 'dr' => $amount, 'cr' => 0],
            ['account_code' => $cashBankCode,  'dr' => 0,       'cr' => $amount],
        ];

        try {
            return $this->create_journal($narration, $lines, 'fee_refund', $refId);
        } catch (\Exception $e) {
            log_message('error', 'create_refund_journal failed: ' . $e->getMessage());
            return null;
        }
    }
}
