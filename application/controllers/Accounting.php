<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Accounting System Controller
 *
 * Complete double-entry accounting for multi-school SaaS:
 *   Tab 1: Chart of Accounts (hierarchical, coded, 5 categories)
 *   Tab 2: Journal Entries / Ledger (double-entry, immutable after lock)
 *   Tab 3: Income & Expense Tracker
 *   Tab 4: Cash Book (enhanced)
 *   Tab 5: Bank Reconciliation
 *   Tab 6: Financial Reports (Trial Balance, P&L, Balance Sheet, Cash Flow)
 *   Tab 7: Settings & Period Lock
 *
 * Firebase paths:
 *   Schools/{school}/Accounts/ChartOfAccounts/{code}        (year-independent)
 *   Schools/{school}/{year}/Accounts/Ledger/{entry_id}
 *   Schools/{school}/{year}/Accounts/Ledger_index/by_date/{date}/{id}
 *   Schools/{school}/{year}/Accounts/Ledger_index/by_account/{code}/{id}
 *   Schools/{school}/{year}/Accounts/Income_expense/{id}
 *   Schools/{school}/{year}/Accounts/Bank_recon/{code}/{id}
 *   Schools/{school}/{year}/Accounts/Closing_balances/{code}
 *   Schools/{school}/{year}/Accounts/Voucher_counters/{type}
 *   Schools/{school}/{year}/Accounts/Period_lock
 *   Schools/{school}/{year}/Accounts/Audit_log/{log_id}
 */
class Accounting extends MY_Controller
{
    // =========================================================================
    //  ROLE CONSTANTS
    // =========================================================================

    private const ADMIN_ROLES   = ['Admin', 'Super Admin', 'Our Panel'];
    private const FINANCE_ROLES = ['Admin', 'Super Admin', 'Our Panel', 'Accountant', 'Finance'];

    // =========================================================================
    //  PATH HELPERS
    // =========================================================================

    /** Year-scoped base: Schools/{school}/{year} */
    private function _bp(): string
    {
        return "Schools/{$this->school_name}/{$this->session_year}";
    }

    /** Chart of Accounts (year-independent) */
    private function _coa(): string
    {
        return "Schools/{$this->school_name}/Accounts/ChartOfAccounts";
    }

    /** Ledger path */
    private function _ledger(): string
    {
        return $this->_bp() . '/Accounts/Ledger';
    }

    /** Ledger index path */
    private function _idx(): string
    {
        return $this->_bp() . '/Accounts/Ledger_index';
    }

    /** Closing balances cache */
    private function _bal(): string
    {
        return $this->_bp() . '/Accounts/Closing_balances';
    }

    /** Income/Expense path */
    private function _ie_path(): string
    {
        return $this->_bp() . '/Accounts/Income_expense';
    }

    // =========================================================================
    //  PRIVATE SECURITY & AUDIT HELPERS
    // =========================================================================

    /**
     * Require the current admin to have one of the allowed roles.
     * Sends a 403 JSON error and exits if not.
     */
    private function _require_role(array $allowed): void
    {
        if (!in_array($this->admin_role, $allowed, true)) {
            $this->json_error('Insufficient permissions for this operation.', 403);
        }
    }

    /**
     * Write an audit trail entry for every write operation.
     */
    private function _audit(string $action, string $entityType, string $entityId, $oldValue = null, $newValue = null): void
    {
        $logId = 'AL_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        $this->firebase->set($this->_bp() . "/Accounts/Audit_log/{$logId}", [
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'admin_id'    => $this->admin_id,
            'admin_name'  => $this->admin_name,
            'timestamp'   => date('c'),
            'ip'          => $this->input->ip_address(),
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
        ]);
    }

    /**
     * Check if a date falls within a locked period. Sends JSON error if locked.
     */
    private function _check_period_lock(string $date): void
    {
        $lock = $this->firebase->get($this->_bp() . '/Accounts/Period_lock');
        if (is_array($lock) && !empty($lock['locked_until']) && $date <= $lock['locked_until']) {
            $this->json_error("Period locked until {$lock['locked_until']}. Cannot modify entries on or before that date.");
        }
    }

    /**
     * Generate a unique entry ID with microsecond component.
     */
    private function _generate_entry_id(string $prefix = 'JE'): string
    {
        return $prefix . '_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    // =========================================================================
    //  PAGE LOAD
    // =========================================================================

    public function index()
    {
        $seg = $this->uri->segment(2);
        $validTabs = ['chart','ledger','income-expense','cash-book','bank-recon','reports','settings'];
        $data = [
            'active_tab' => in_array($seg, $validTabs, true) ? $seg : 'chart',
        ];
        $this->load->view('include/header', $data);
        $this->load->view('accounting/index', $data);
        $this->load->view('include/footer');
    }

    // =========================================================================
    //  TAB 1: CHART OF ACCOUNTS
    // =========================================================================

    /** GET: Fetch full Chart of Accounts */
    public function get_chart()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $all = $this->firebase->get($this->_coa());
        if (!is_array($all)) $all = [];

        // Sort by code
        uksort($all, 'strnatcmp');

        $this->json_success(['accounts' => $all]);
    }

    /** POST: Create or update an account */
    public function save_account()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code        = $this->safe_path_segment(trim((string) $this->input->post('code')), 'code');
        $name        = trim((string) $this->input->post('name'));
        $category    = trim((string) $this->input->post('category'));
        $subCategory = trim((string) $this->input->post('sub_category'));
        $parentCode  = trim((string) $this->input->post('parent_code'));
        $isGroup     = $this->input->post('is_group') === 'true';
        $isBank      = $this->input->post('is_bank') === 'true';
        $description = trim((string) $this->input->post('description'));
        $openBal     = (float) $this->input->post('opening_balance');
        $isEdit      = $this->input->post('is_edit') === 'true';

        if (!$code || !$name || !$category) {
            return $this->json_error('Code, name, and category are required.');
        }

        // Validate code format: 4-6 digits
        if (!preg_match('/^\d{4,6}$/', $code)) {
            return $this->json_error('Account code must be 4-6 digits.');
        }

        $validCats = ['Asset', 'Liability', 'Equity', 'Income', 'Expense'];
        if (!in_array($category, $validCats, true)) {
            return $this->json_error('Invalid category.');
        }

        // Check code uniqueness on create
        if (!$isEdit) {
            $existing = $this->firebase->get($this->_coa() . "/{$code}");
            if ($existing) {
                return $this->json_error("Account code {$code} already exists.");
            }
        }

        // Normal side: Assets & Expenses = Dr, others = Cr
        $normalSide = in_array($category, ['Asset', 'Expense']) ? 'Dr' : 'Cr';

        $data = [
            'code'            => $code,
            'name'            => $name,
            'category'        => $category,
            'sub_category'    => $subCategory ?: $category,
            'parent_code'     => $parentCode ?: null,
            'is_group'        => $isGroup,
            'is_bank'         => $isBank,
            'normal_side'     => $normalSide,
            'description'     => $description,
            'opening_balance' => $openBal,
            'status'          => 'active',
            'is_system'       => false,
            'sort_order'      => (int) $code,
            'updated_at'      => date('c'),
        ];

        if ($isBank) {
            $data['bank_details'] = [
                'bank_name'  => trim((string) $this->input->post('bank_name')),
                'branch'     => trim((string) $this->input->post('branch')),
                'account_no' => trim((string) $this->input->post('account_no')),
                'ifsc'       => trim((string) $this->input->post('ifsc')),
            ];
        }

        if (!$isEdit) {
            $data['created_at'] = date('c');
        }

        $oldData = $isEdit ? $this->firebase->get($this->_coa() . "/{$code}") : null;
        $this->firebase->set($this->_coa() . "/{$code}", $data);
        $this->_audit($isEdit ? 'update_account' : 'create_account', 'chart_of_accounts', $code, $oldData, $data);
        $this->json_success(['message' => $isEdit ? 'Account updated.' : 'Account created.']);
    }

    /** POST: Delete (deactivate) an account */
    public function delete_account()
    {
        $this->_require_role(self::ADMIN_ROLES);

        $code = $this->safe_path_segment(trim((string) $this->input->post('code')), 'code');

        $acct = $this->firebase->get($this->_coa() . "/{$code}");
        if (!$acct) {
            return $this->json_error('Account not found.');
        }
        if (!empty($acct['is_system'])) {
            return $this->json_error('Cannot delete system accounts.');
        }

        // Check if account has ledger entries
        $idx = $this->firebase->shallow_get($this->_idx() . "/by_account/{$code}");
        if (!empty($idx)) {
            return $this->json_error('Cannot delete — account has ledger entries. Deactivate instead.');
        }

        $this->firebase->delete($this->_coa(), $code);
        $this->_audit('delete_account', 'chart_of_accounts', $code, $acct, null);
        $this->json_success(['message' => 'Account deleted.']);
    }

    /** POST: Seed default chart of accounts for Indian schools */
    public function seed_default_chart()
    {
        $this->_require_role(self::ADMIN_ROLES);

        $existing = $this->firebase->shallow_get($this->_coa());
        if (!empty($existing)) {
            return $this->json_error('Chart already has accounts. Clear first or add individually.');
        }

        $ts = date('c');
        $accounts = $this->_default_coa_template($ts);

        $this->firebase->set($this->_coa(), $accounts);
        $this->_audit('seed_default_chart', 'chart_of_accounts', 'all', null, ['count' => count($accounts)]);
        $this->json_success(['message' => 'Default chart seeded with ' . count($accounts) . ' accounts.']);
    }

    /** POST: Migrate existing Account_book entries to ChartOfAccounts */
    public function migrate_existing_accounts()
    {
        $this->_require_role(self::ADMIN_ROLES);

        $bookPath = $this->_bp() . '/Accounts/Account_book';
        $book = $this->firebase->get($bookPath);
        if (!is_array($book)) {
            return $this->json_error('No existing Account_book entries found.');
        }

        $coaPath = $this->_coa();
        $existing = $this->firebase->shallow_get($coaPath);
        $ts = date('c');
        $migrated = 0;
        $nextCode = 6000; // start migrated accounts at 6000

        // Map sub-groups to categories
        $catMap = [
            'CASH' => 'Asset', 'BANK ACCOUNT' => 'Asset', 'CURRENT ASSETS' => 'Asset',
            'MOVABLE ASSETS' => 'Asset', 'STOCK IN HAND' => 'Asset', 'SUNDRY DEBTORS' => 'Asset',
            'FIXED ASSETS' => 'Asset', 'FURNITURE ACCOUNT' => 'Asset', 'OFFICE EQUIPMENT' => 'Asset',
            'PLANT & MACHINERY ACCOUNT' => 'Asset', 'VEHICLES' => 'Asset', 'MOVEABLE ASSETS' => 'Asset',
            'CURRENT LIABILITIES' => 'Liability', 'SECURED LOAN' => 'Liability',
            'SUNDRY CREDITORS' => 'Liability', 'UNSECURED LOAN' => 'Liability',
            'DUTY & TAXES' => 'Liability',
            'REVENUE ACCOUNT' => 'Income', 'INCOME FROM OTHER SOURCES' => 'Income',
            'SALE ACCOUNT' => 'Income',
            'PERSONAL EXP.' => 'Expense', 'DIRECT EXPENSES' => 'Expense', 'DIRECT EXP.' => 'Expense',
            'INDIRECT EXPENSES' => 'Expense', 'ADMINISTRATION EXP.' => 'Expense',
            'ADVERTISEMENT & PUBLICITY EXP.' => 'Expense', 'FINANCIAL EXP.' => 'Expense',
            'PURCHASE ACCOUNT' => 'Expense',
        ];

        foreach ($book as $acctName => $acctData) {
            if (!is_array($acctData)) continue;

            $code = (string) $nextCode;
            if (in_array($code, $existing ?: [], true)) {
                $nextCode++;
                $code = (string) $nextCode;
            }

            $under = strtoupper(trim($acctData['Under'] ?? ''));
            $category = $catMap[$under] ?? 'Expense';
            $isBank = ($under === 'BANK ACCOUNT');

            $entry = [
                'code'            => $code,
                'name'            => $acctName,
                'category'        => $category,
                'sub_category'    => $under ?: $category,
                'parent_code'     => null,
                'is_group'        => false,
                'is_bank'         => $isBank,
                'normal_side'     => in_array($category, ['Asset', 'Expense']) ? 'Dr' : 'Cr',
                'description'     => "Migrated from Account Book",
                'opening_balance' => 0,
                'status'          => 'active',
                'is_system'       => false,
                'sort_order'      => (int) $code,
                'created_at'      => $ts,
                'updated_at'      => $ts,
                'migrated_from'   => $acctName,
            ];

            if ($isBank) {
                $entry['bank_details'] = [
                    'bank_name'  => $acctData['branchName'] ?? '',
                    'branch'     => '',
                    'account_no' => $acctData['accountNumber'] ?? '',
                    'ifsc'       => $acctData['ifscCode'] ?? '',
                ];
            }

            $this->firebase->set("{$coaPath}/{$code}", $entry);
            $nextCode++;
            $migrated++;
        }

        $this->_audit('migrate_existing_accounts', 'chart_of_accounts', 'migration', null, ['migrated' => $migrated]);
        $this->json_success(['message' => "Migrated {$migrated} accounts.", 'migrated' => $migrated]);
    }

    // =========================================================================
    //  TAB 2: LEDGER / JOURNAL ENTRIES
    // =========================================================================

    /** POST: Fetch ledger entries with filters and pagination.
     *
     * Optimization: when date range is provided without account filter,
     * uses by_date index to fetch only relevant entry IDs instead of
     * downloading the entire Ledger node. Falls back to full scan only
     * when no filters are provided.
     */
    public function get_ledger_entries()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $dateFrom    = trim((string) $this->input->post('date_from'));
        $dateTo      = trim((string) $this->input->post('date_to'));
        $accountCode = trim((string) $this->input->post('account_code'));
        $vType       = trim((string) $this->input->post('voucher_type'));
        $page        = (int) ($this->input->post('page') ?: 1);
        $limit       = min(100, max(1, (int) ($this->input->post('limit') ?: 50)));
        // Backward compat: if 'offset' is sent (old UI), compute page from it
        $rawOffset   = $this->input->post('offset');
        if ($rawOffset !== null && $rawOffset !== '' && $page <= 1) {
            $page = (int) floor((int) $rawOffset / $limit) + 1;
        }

        $entries = [];

        if ($accountCode) {
            // Strategy A: use account index → fetch only matching IDs
            $safeCode = $this->safe_path_segment($accountCode, 'account_code');
            $ids = $this->firebase->shallow_get($this->_idx() . "/by_account/{$safeCode}");
            if (is_array($ids)) {
                $allLedger = $this->firebase->get($this->_ledger());
                if (!is_array($allLedger)) $allLedger = [];
                foreach ($ids as $id) {
                    $entry = $allLedger[$id] ?? null;
                    if (!is_array($entry)) continue;
                    if (($entry['status'] ?? '') === 'deleted') continue;
                    if ($dateFrom && ($entry['date'] ?? '') < $dateFrom) continue;
                    if ($dateTo && ($entry['date'] ?? '') > $dateTo) continue;
                    if ($vType && ($entry['voucher_type'] ?? '') !== $vType) continue;
                    $entry['id'] = $id;
                    $entries[] = $entry;
                }
            }
        } elseif ($dateFrom || $dateTo) {
            // Strategy B: use date index to narrow the fetch window
            $dateIndex = $this->firebase->get($this->_idx() . '/by_date');
            if (!is_array($dateIndex)) $dateIndex = [];

            // Collect entry IDs from matching date range
            $targetIds = [];
            foreach ($dateIndex as $idxDate => $ids) {
                if ($dateFrom && $idxDate < $dateFrom) continue;
                if ($dateTo && $idxDate > $dateTo) continue;
                if (is_array($ids)) {
                    foreach (array_keys($ids) as $id) {
                        $targetIds[$id] = true;
                    }
                }
            }

            if (!empty($targetIds)) {
                $allLedger = $this->firebase->get($this->_ledger());
                if (!is_array($allLedger)) $allLedger = [];
                foreach ($targetIds as $id => $_) {
                    $entry = $allLedger[$id] ?? null;
                    if (!is_array($entry)) continue;
                    if (($entry['status'] ?? '') === 'deleted') continue;
                    if ($vType && ($entry['voucher_type'] ?? '') !== $vType) continue;
                    $entry['id'] = $id;
                    $entries[] = $entry;
                }
            }
        } else {
            // Strategy C: no filters — full scan (fallback)
            $all = $this->firebase->get($this->_ledger());
            if (is_array($all)) {
                foreach ($all as $id => $entry) {
                    if (!is_array($entry)) continue;
                    if (($entry['status'] ?? '') === 'deleted') continue;
                    if ($vType && ($entry['voucher_type'] ?? '') !== $vType) continue;
                    $entry['id'] = $id;
                    $entries[] = $entry;
                }
            }
        }

        // Sort by date desc
        usort($entries, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        // Pagination
        $total  = count($entries);
        $offset = ($page - 1) * $limit;
        $entries = array_slice($entries, $offset, $limit);

        $this->json_success([
            'entries'  => $entries,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    /** GET: Get next voucher number for a given type */
    public function get_next_voucher_no()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $type = trim((string) $this->input->get('type'));
        if (!$type) $type = 'Journal';

        $safeType = $this->safe_path_segment($type, 'type');
        $prefix = $this->_voucher_prefix($type);
        $counterPath = $this->_bp() . "/Accounts/Voucher_counters/{$safeType}";
        $current = (int) $this->firebase->get($counterPath);

        $next = $current + 1;
        $voucherNo = $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);

        $this->json_success(['voucher_no' => $voucherNo, 'seq' => $next]);
    }

    /** POST: Create a journal entry (double-entry) */
    public function save_journal_entry()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $date      = trim((string) $this->input->post('date'));
        $vType     = trim((string) $this->input->post('voucher_type'));
        $narration = trim((string) $this->input->post('narration'));
        $linesJson = $this->input->post('lines');
        $source    = trim((string) $this->input->post('source')) ?: 'manual';
        $sourceRef = trim((string) $this->input->post('source_ref'));

        if (!$date || !$vType || !$linesJson) {
            return $this->json_error('Date, voucher type, and line items are required.');
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->json_error('Invalid date format. Use YYYY-MM-DD.');
        }

        // Check period lock
        $this->_check_period_lock($date);

        $lines = is_string($linesJson) ? json_decode($linesJson, true) : $linesJson;
        if (!is_array($lines) || count($lines) < 2) {
            return $this->json_error('At least 2 line items required for double-entry.');
        }

        // Validate and sum
        $totalDr = 0;
        $totalCr = 0;
        $cleanLines = [];
        $affectedAccounts = [];

        foreach ($lines as $line) {
            $acCode = trim((string) ($line['account_code'] ?? ''));
            $acName = trim((string) ($line['account_name'] ?? ''));
            $dr     = round((float) ($line['dr'] ?? 0), 2);
            $cr     = round((float) ($line['cr'] ?? 0), 2);

            if (!$acCode) continue;
            if ($dr == 0 && $cr == 0) continue;
            if ($dr > 0 && $cr > 0) {
                return $this->json_error("Line for {$acCode}: cannot have both debit and credit.");
            }

            $totalDr += $dr;
            $totalCr += $cr;
            $cleanLines[] = [
                'account_code' => $acCode,
                'account_name' => $acName,
                'dr'           => $dr,
                'cr'           => $cr,
                'narration'    => trim((string) ($line['narration'] ?? '')),
            ];

            $affectedAccounts[$acCode] = [
                'dr' => ($affectedAccounts[$acCode]['dr'] ?? 0) + $dr,
                'cr' => ($affectedAccounts[$acCode]['cr'] ?? 0) + $cr,
            ];
        }

        if (empty($cleanLines)) {
            return $this->json_error('No valid line items provided.');
        }

        // Double-entry check: total debit must equal total credit
        if (abs($totalDr - $totalCr) > 0.01) {
            return $this->json_error("Debit ({$totalDr}) does not equal Credit ({$totalCr}).");
        }

        // Validate each account exists and is active in CoA
        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) $coa = [];
        foreach ($cleanLines as $line) {
            $ac = $line['account_code'];
            if (!isset($coa[$ac]) || ($coa[$ac]['status'] ?? '') !== 'active') {
                return $this->json_error("Account {$ac} does not exist or is inactive.");
            }
            if (!empty($coa[$ac]['is_group'])) {
                return $this->json_error("Account {$ac} is a group account — cannot post directly.");
            }
        }

        // Generate voucher number
        $safeVType = $this->safe_path_segment($vType, 'voucher_type');
        $prefix = $this->_voucher_prefix($vType);
        $counterPath = $this->_bp() . "/Accounts/Voucher_counters/{$safeVType}";
        $currentSeq = (int) $this->firebase->get($counterPath);
        $newSeq = $currentSeq + 1;
        $voucherNo = $prefix . str_pad($newSeq, 6, '0', STR_PAD_LEFT);
        // Write counter immediately to minimise race window
        $this->firebase->set($counterPath, $newSeq);

        // Build entry
        $entryId = $this->_generate_entry_id('JE');
        $entry = [
            'date'         => $date,
            'voucher_no'   => $voucherNo,
            'voucher_type' => $vType,
            'narration'    => $narration,
            'lines'        => $cleanLines,
            'total_dr'     => round($totalDr, 2),
            'total_cr'     => round($totalCr, 2),
            'source'       => $source,
            'source_ref'   => $sourceRef ?: null,
            'is_finalized' => false,
            'status'       => 'active',
            'created_by'   => $this->admin_id,
            'created_at'   => date('c'),
        ];

        // Write entry
        $this->firebase->set($this->_ledger() . "/{$entryId}", $entry);

        // Write index entries
        $safeDateSeg = $this->safe_path_segment($date, 'date');
        $this->firebase->set($this->_idx() . "/by_date/{$safeDateSeg}/{$entryId}", true);
        foreach (array_keys($affectedAccounts) as $acCode) {
            $safeAc = $this->safe_path_segment($acCode, 'account_code');
            $this->firebase->set($this->_idx() . "/by_account/{$safeAc}/{$entryId}", true);
        }

        // Update closing balances cache
        $this->_update_balances($affectedAccounts, 'add');

        $this->_audit('create', 'journal_entry', $entryId, null, $entry);

        $this->json_success([
            'message'    => 'Journal entry saved.',
            'entry_id'   => $entryId,
            'voucher_no' => $voucherNo,
        ]);
    }

    /** POST: Soft-delete a non-finalized journal entry */
    public function delete_journal_entry()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $entryId = trim((string) $this->input->post('entry_id'));
        if (!$entryId) return $this->json_error('Entry ID required.');

        $entry = $this->firebase->get($this->_ledger() . "/{$entryId}");
        if (!is_array($entry)) return $this->json_error('Entry not found.');
        if (($entry['status'] ?? '') === 'deleted') return $this->json_error('Entry already deleted.');

        if (!empty($entry['is_finalized'])) {
            return $this->json_error('Cannot delete finalized entries.');
        }

        // Check period lock
        $this->_check_period_lock($entry['date'] ?? '');

        // Reverse closing balances
        $affectedAccounts = [];
        foreach ($entry['lines'] ?? [] as $line) {
            $ac = $line['account_code'] ?? '';
            if (!$ac) continue;
            $affectedAccounts[$ac] = [
                'dr' => ($affectedAccounts[$ac]['dr'] ?? 0) + ($line['dr'] ?? 0),
                'cr' => ($affectedAccounts[$ac]['cr'] ?? 0) + ($line['cr'] ?? 0),
            ];
        }
        $this->_update_balances($affectedAccounts, 'subtract');

        // Remove indices
        $date = $entry['date'] ?? '';
        if ($date) {
            $safeDateSeg = $this->safe_path_segment($date, 'date');
            $this->firebase->delete($this->_idx() . "/by_date/{$safeDateSeg}", $entryId);
        }
        foreach (array_keys($affectedAccounts) as $acCode) {
            $safeAc = $this->safe_path_segment($acCode, 'account_code');
            $this->firebase->delete($this->_idx() . "/by_account/{$safeAc}", $entryId);
        }

        // Soft-delete: mark as deleted instead of removing
        $this->firebase->update($this->_ledger() . "/{$entryId}", [
            'status'     => 'deleted',
            'deleted_by' => $this->admin_id,
            'deleted_at' => date('c'),
        ]);

        $this->_audit('delete', 'journal_entry', $entryId, $entry, null);
        $this->json_success(['message' => 'Entry deleted.']);
    }

    /** POST: Finalize an entry (make immutable) */
    public function finalize_entry()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $entryId = trim((string) $this->input->post('entry_id'));
        if (!$entryId) return $this->json_error('Entry ID required.');

        $path = $this->_ledger() . "/{$entryId}";
        $entry = $this->firebase->get($path);
        if (!is_array($entry)) return $this->json_error('Entry not found.');
        if (($entry['status'] ?? '') === 'deleted') return $this->json_error('Cannot finalize a deleted entry.');

        $this->firebase->update($path, [
            'is_finalized' => true,
            'finalized_at' => date('c'),
        ]);

        $this->_audit('finalize', 'journal_entry', $entryId, null, ['finalized_at' => date('c')]);
        $this->json_success(['message' => 'Entry finalized.']);
    }

    // =========================================================================
    //  TAB 3: INCOME & EXPENSE
    // =========================================================================

    /** POST: Fetch income/expense records with pagination */
    public function get_income_expenses()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $type     = trim((string) $this->input->post('type')); // income|expense|''
        $dateFrom = trim((string) $this->input->post('date_from'));
        $dateTo   = trim((string) $this->input->post('date_to'));
        $limit    = (int) ($this->input->post('limit') ?: 100);
        $offset   = (int) ($this->input->post('offset') ?: 0);

        $all = $this->firebase->get($this->_ie_path());
        if (!is_array($all)) $all = [];

        $records = [];
        foreach ($all as $id => $rec) {
            if (!is_array($rec)) continue;
            if (($rec['status'] ?? '') === 'deleted') continue;
            if ($type && ($rec['type'] ?? '') !== $type) continue;
            if ($dateFrom && ($rec['date'] ?? '') < $dateFrom) continue;
            if ($dateTo && ($rec['date'] ?? '') > $dateTo) continue;
            $rec['id'] = $id;
            $records[] = $rec;
        }

        usort($records, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        // Pagination
        $total = count($records);
        $records = array_slice($records, $offset, $limit);

        $this->json_success([
            'records'  => $records,
            'total'    => $total,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    /** POST: Create income or expense record + auto-create ledger entry */
    public function save_income_expense()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $type        = trim((string) $this->input->post('type'));
        $date        = trim((string) $this->input->post('date'));
        $accountCode = trim((string) $this->input->post('account_code'));
        $amount      = round((float) $this->input->post('amount'), 2);
        $payMode     = trim((string) $this->input->post('payment_mode'));
        $bankCode    = trim((string) $this->input->post('bank_account_code'));
        $description = trim((string) $this->input->post('description'));
        $category    = trim((string) $this->input->post('category'));
        $vendor      = trim((string) $this->input->post('vendor'));
        $receiptNo   = trim((string) $this->input->post('receipt_no'));

        if (!$type || !in_array($type, ['income', 'expense'])) {
            return $this->json_error('Type must be income or expense.');
        }
        if (!$date || !$accountCode || $amount <= 0) {
            return $this->json_error('Date, account, and amount are required.');
        }

        // Check period lock
        $this->_check_period_lock($date);

        // Determine cash/bank account for the other side of the entry
        $cashBankCode = $bankCode ?: '1010'; // default to Cash in Hand

        // Validate both accounts exist in CoA (single fetch)
        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) $coa = [];

        if (!isset($coa[$accountCode]) || ($coa[$accountCode]['status'] ?? '') !== 'active') {
            return $this->json_error("Account {$accountCode} does not exist or is inactive.");
        }
        if (!isset($coa[$cashBankCode]) || ($coa[$cashBankCode]['status'] ?? '') !== 'active') {
            return $this->json_error("Cash/Bank account {$cashBankCode} does not exist or is inactive.");
        }

        // Build ledger lines
        if ($type === 'income') {
            // Dr Cash/Bank, Cr Income Account
            $lines = [
                ['account_code' => $cashBankCode, 'account_name' => '', 'dr' => $amount, 'cr' => 0, 'narration' => $description],
                ['account_code' => $accountCode, 'account_name' => '', 'dr' => 0, 'cr' => $amount, 'narration' => $description],
            ];
            $vType = 'Receipt';
        } else {
            // Dr Expense Account, Cr Cash/Bank
            $lines = [
                ['account_code' => $accountCode, 'account_name' => '', 'dr' => $amount, 'cr' => 0, 'narration' => $description],
                ['account_code' => $cashBankCode, 'account_name' => '', 'dr' => 0, 'cr' => $amount, 'narration' => $description],
            ];
            $vType = 'Payment';
        }

        // Resolve account names from the already-fetched CoA
        foreach ($lines as &$line) {
            $line['account_name'] = $coa[$line['account_code']]['name'] ?? $line['account_code'];
        }
        unset($line);

        // Create ledger entry
        $safeVType = $this->safe_path_segment($vType, 'voucher_type');
        $prefix = $this->_voucher_prefix($vType);
        $counterPath = $this->_bp() . "/Accounts/Voucher_counters/{$safeVType}";
        $seq = (int) $this->firebase->get($counterPath) + 1;
        $voucherNo = $prefix . str_pad($seq, 6, '0', STR_PAD_LEFT);
        // Write counter immediately to minimise race window
        $this->firebase->set($counterPath, $seq);

        $entryId = $this->_generate_entry_id('IE');
        $ledgerEntry = [
            'date'         => $date,
            'voucher_no'   => $voucherNo,
            'voucher_type' => $vType,
            'narration'    => $description,
            'lines'        => $lines,
            'total_dr'     => $amount,
            'total_cr'     => $amount,
            'source'       => $type,
            'source_ref'   => null,
            'is_finalized' => false,
            'status'       => 'active',
            'created_by'   => $this->admin_id,
            'created_at'   => date('c'),
        ];

        $this->firebase->set($this->_ledger() . "/{$entryId}", $ledgerEntry);

        // Indices
        $safeDateSeg = $this->safe_path_segment($date, 'date');
        $this->firebase->set($this->_idx() . "/by_date/{$safeDateSeg}/{$entryId}", true);
        foreach ($lines as $line) {
            $safeAc = $this->safe_path_segment($line['account_code'], 'account_code');
            $this->firebase->set($this->_idx() . "/by_account/{$safeAc}/{$entryId}", true);
        }

        // Update balances
        $affected = [];
        foreach ($lines as $line) {
            $ac = $line['account_code'];
            $affected[$ac] = [
                'dr' => ($affected[$ac]['dr'] ?? 0) + $line['dr'],
                'cr' => ($affected[$ac]['cr'] ?? 0) + $line['cr'],
            ];
        }
        $this->_update_balances($affected, 'add');

        // Save income/expense record
        $recordId = $entryId;
        $record = [
            'type'              => $type,
            'date'              => $date,
            'account_code'      => $accountCode,
            'amount'            => $amount,
            'payment_mode'      => $payMode,
            'bank_account_code' => $bankCode ?: null,
            'description'       => $description,
            'category'          => $category,
            'vendor'            => $vendor,
            'receipt_no'        => $receiptNo,
            'ledger_entry_id'   => $entryId,
            'status'            => 'active',
            'created_by'        => $this->admin_id,
            'created_at'        => date('c'),
        ];

        $this->firebase->set($this->_ie_path() . "/{$recordId}", $record);

        $this->_audit('create', 'income_expense', $recordId, null, $record);

        $this->json_success([
            'message'    => ucfirst($type) . ' recorded.',
            'record_id'  => $recordId,
            'voucher_no' => $voucherNo,
        ]);
    }

    /** POST: Soft-delete income/expense + its ledger entry */
    public function delete_income_expense()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $id = trim((string) $this->input->post('id'));
        if (!$id) return $this->json_error('Record ID required.');

        $recPath = $this->_ie_path() . "/{$id}";
        $rec = $this->firebase->get($recPath);
        if (!is_array($rec)) return $this->json_error('Record not found.');
        if (($rec['status'] ?? '') === 'deleted') return $this->json_error('Record already deleted.');

        // Check period lock
        $this->_check_period_lock($rec['date'] ?? '');

        // Soft-delete linked ledger entry
        $ledgerId = $rec['ledger_entry_id'] ?? '';
        if ($ledgerId) {
            $entry = $this->firebase->get($this->_ledger() . "/{$ledgerId}");
            if (is_array($entry)) {
                if (!empty($entry['is_finalized'])) {
                    return $this->json_error('Linked journal entry is finalized.');
                }

                $affected = [];
                foreach ($entry['lines'] ?? [] as $line) {
                    $ac = $line['account_code'] ?? '';
                    if (!$ac) continue;
                    $affected[$ac] = [
                        'dr' => ($affected[$ac]['dr'] ?? 0) + ($line['dr'] ?? 0),
                        'cr' => ($affected[$ac]['cr'] ?? 0) + ($line['cr'] ?? 0),
                    ];
                }
                $this->_update_balances($affected, 'subtract');

                $date = $entry['date'] ?? '';
                if ($date) {
                    $safeDateSeg = $this->safe_path_segment($date, 'date');
                    $this->firebase->delete($this->_idx() . "/by_date/{$safeDateSeg}", $ledgerId);
                }
                foreach (array_keys($affected) as $acCode) {
                    $safeAc = $this->safe_path_segment($acCode, 'account_code');
                    $this->firebase->delete($this->_idx() . "/by_account/{$safeAc}", $ledgerId);
                }

                // Soft-delete the ledger entry
                $this->firebase->update($this->_ledger() . "/{$ledgerId}", [
                    'status'     => 'deleted',
                    'deleted_by' => $this->admin_id,
                    'deleted_at' => date('c'),
                ]);
            }
        }

        // Soft-delete the income/expense record
        $this->firebase->update($recPath, [
            'status'     => 'deleted',
            'deleted_by' => $this->admin_id,
            'deleted_at' => date('c'),
        ]);

        $this->_audit('delete', 'income_expense', $id, $rec, null);
        $this->json_success(['message' => 'Record deleted.']);
    }

    /** POST: Income/Expense summary by month */
    public function get_income_expense_summary()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $all = $this->firebase->get($this->_ie_path());
        if (!is_array($all)) $all = [];

        $months = [];
        foreach ($all as $rec) {
            if (!is_array($rec)) continue;
            if (($rec['status'] ?? '') === 'deleted') continue;
            $m = substr($rec['date'] ?? '', 0, 7); // YYYY-MM
            $type = $rec['type'] ?? 'expense';
            $amt = (float) ($rec['amount'] ?? 0);

            if (!isset($months[$m])) $months[$m] = ['income' => 0, 'expense' => 0];
            $months[$m][$type] += $amt;
        }

        ksort($months);
        $this->json_success(['summary' => $months]);
    }

    // =========================================================================
    //  TAB 4: CASH BOOK
    // =========================================================================

    /** POST: Get cash book for a cash/bank account */
    public function get_cash_book()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $accountCode = trim((string) $this->input->post('account_code'));
        $dateFrom    = trim((string) $this->input->post('date_from'));
        $dateTo      = trim((string) $this->input->post('date_to'));

        if (!$accountCode) return $this->json_error('Account code required.');

        $this->_render_cash_book($accountCode, $dateFrom, $dateTo);
    }

    /**
     * Shared cash book / ledger report renderer.
     * Fetches all ledger entries at once (no N+1), filters by account+date, computes running balance.
     */
    private function _render_cash_book(string $accountCode, string $dateFrom, string $dateTo): void
    {
        $safeCode = $this->safe_path_segment($accountCode, 'account_code');

        // Get account details and opening balance from CoA
        $acct = $this->firebase->get($this->_coa() . "/{$safeCode}");
        $openBal = (float) ($acct['opening_balance'] ?? 0);
        $normalSide = $acct['normal_side'] ?? 'Dr';

        // Get all entry IDs for this account
        $ids = $this->firebase->shallow_get($this->_idx() . "/by_account/{$safeCode}");
        if (!is_array($ids)) $ids = [];

        // Fetch FULL ledger once (fix N+1)
        $allLedger = $this->firebase->get($this->_ledger());
        if (!is_array($allLedger)) $allLedger = [];

        // First pass: collect all entries, splitting pre-filter vs in-range
        $allTxns = [];
        foreach ($ids as $id) {
            $entry = $allLedger[$id] ?? null;
            if (!is_array($entry)) continue;
            if (($entry['status'] ?? '') === 'deleted') continue;

            $entryDate = $entry['date'] ?? '';
            if ($dateTo && $entryDate > $dateTo) continue;

            // Find this account's dr/cr in the entry lines
            $dr = 0;
            $cr = 0;
            foreach ($entry['lines'] ?? [] as $line) {
                if (($line['account_code'] ?? '') === $accountCode) {
                    $dr += (float) ($line['dr'] ?? 0);
                    $cr += (float) ($line['cr'] ?? 0);
                }
            }

            $allTxns[] = [
                'date'       => $entryDate,
                'voucher_no' => $entry['voucher_no'] ?? '',
                'narration'  => $entry['narration'] ?? '',
                'dr'         => $dr,
                'cr'         => $cr,
                'entry_id'   => $id,
            ];
        }

        // Sort all by date
        usort($allTxns, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Accumulate pre-filter transactions into opening balance
        $runningBal = $openBal;
        $transactions = [];
        foreach ($allTxns as $txn) {
            if ($dateFrom && $txn['date'] < $dateFrom) {
                // Pre-filter: accumulate into opening balance
                if ($normalSide === 'Dr') {
                    $runningBal += $txn['dr'] - $txn['cr'];
                } else {
                    $runningBal += $txn['cr'] - $txn['dr'];
                }
                continue;
            }
            $transactions[] = $txn;
        }

        // Effective opening balance includes pre-filter movements
        $effectiveOpenBal = round($runningBal, 2);

        // Compute running balance respecting account normal side
        foreach ($transactions as &$txn) {
            if ($normalSide === 'Dr') {
                $runningBal += $txn['dr'] - $txn['cr'];
            } else {
                $runningBal += $txn['cr'] - $txn['dr'];
            }
            $txn['balance'] = round($runningBal, 2);
        }
        unset($txn);

        $this->json_success([
            'account'         => $acct,
            'opening_balance' => $effectiveOpenBal,
            'transactions'    => $transactions,
            'closing_balance' => round($runningBal, 2),
        ]);
    }

    // =========================================================================
    //  TAB 5: BANK RECONCILIATION
    // =========================================================================

    /** GET: Get bank accounts from CoA */
    public function get_bank_accounts()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $all = $this->firebase->get($this->_coa());
        if (!is_array($all)) $all = [];

        $banks = [];
        foreach ($all as $code => $acct) {
            if (!is_array($acct)) continue;
            if (!empty($acct['is_bank']) && ($acct['status'] ?? '') === 'active') {
                $banks[] = ['code' => $code, 'name' => $acct['name'] ?? $code];
            }
        }

        $this->json_success(['banks' => $banks]);
    }

    /** POST: Get bank recon entries */
    public function get_bank_statement()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code     = trim((string) $this->input->post('account_code'));
        $dateFrom = trim((string) $this->input->post('date_from'));
        $dateTo   = trim((string) $this->input->post('date_to'));

        if (!$code) return $this->json_error('Account code required.');

        $safeCode = $this->safe_path_segment($code, 'account_code');
        $path = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}";
        $all = $this->firebase->get($path);
        if (!is_array($all)) $all = [];

        $items = [];
        foreach ($all as $id => $item) {
            if (!is_array($item)) continue;
            if ($dateFrom && ($item['statement_date'] ?? '') < $dateFrom) continue;
            if ($dateTo && ($item['statement_date'] ?? '') > $dateTo) continue;
            $item['id'] = $id;
            $items[] = $item;
        }

        usort($items, fn($a, $b) => strcmp($a['statement_date'] ?? '', $b['statement_date'] ?? ''));
        $this->json_success(['items' => $items]);
    }

    /** POST: Import bank statement (CSV) with duplicate detection */
    public function import_bank_statement()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code = trim((string) $this->input->post('account_code'));
        if (!$code) return $this->json_error('Account code required.');

        $safeCode = $this->safe_path_segment($code, 'account_code');

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json_error('CSV file upload failed.');
        }

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$file) return $this->json_error('Cannot read file.');

        $header = fgetcsv($file); // Skip header row
        $basePath = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}";
        $ts = date('c');

        // Load existing entries for duplicate detection
        $existingEntries = $this->firebase->get($basePath);
        if (!is_array($existingEntries)) $existingEntries = [];
        $existingHashes = [];
        foreach ($existingEntries as $existItem) {
            if (!is_array($existItem)) continue;
            $hash = md5(($existItem['statement_date'] ?? '') . '|' . ($existItem['description'] ?? '') . '|' . ($existItem['debit'] ?? 0) . '|' . ($existItem['credit'] ?? 0));
            $existingHashes[$hash] = true;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 4) continue;

            $stDate  = trim($row[0]);
            $desc    = trim($row[1]);
            $debit   = (float) str_replace(',', '', $row[2] ?? '0');
            $credit  = (float) str_replace(',', '', $row[3] ?? '0');
            $ref     = trim($row[4] ?? '');

            // Normalize date to YYYY-MM-DD
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $stDate)) {
                $stDate = date('Y-m-d', strtotime(str_replace('/', '-', $stDate)));
            }

            // Check for duplicates
            $hash = md5($stDate . '|' . $desc . '|' . $debit . '|' . $credit);
            if (isset($existingHashes[$hash])) {
                $skipped++;
                continue;
            }
            $existingHashes[$hash] = true;

            $itemId = $this->_generate_entry_id('BK');
            $item = [
                'statement_date'    => $stDate,
                'description'       => $desc,
                'reference'         => $ref,
                'debit'             => $debit,
                'credit'            => $credit,
                'matched_ledger_id' => null,
                'status'            => 'unmatched',
                'imported_at'       => $ts,
            ];

            $this->firebase->set("{$basePath}/{$itemId}", $item);
            $imported++;
        }

        fclose($file);
        $this->_audit('import_bank_statement', 'bank_recon', $safeCode, null, ['imported' => $imported, 'skipped' => $skipped]);
        $this->json_success([
            'message'  => "Imported {$imported} statement entries." . ($skipped ? " {$skipped} duplicates skipped." : ''),
            'imported' => $imported,
            'skipped'  => $skipped,
        ]);
    }

    /** POST: Match a bank statement item to a ledger entry */
    public function match_transaction()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code     = trim((string) $this->input->post('account_code'));
        $reconId  = trim((string) $this->input->post('recon_id'));
        $ledgerId = trim((string) $this->input->post('ledger_id'));

        if (!$code || !$reconId || !$ledgerId) {
            return $this->json_error('Account code, recon ID, and ledger ID are required.');
        }

        $safeCode = $this->safe_path_segment($code, 'account_code');
        $reconPath = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}/{$reconId}";
        $recon = $this->firebase->get($reconPath);
        if (!is_array($recon)) return $this->json_error('Statement entry not found.');

        // Validate ledger entry exists and is not deleted
        $entry = $this->firebase->get($this->_ledger() . "/{$ledgerId}");
        if (!is_array($entry) || ($entry['status'] ?? '') === 'deleted') {
            return $this->json_error('Ledger entry not found.');
        }

        $this->firebase->update($reconPath, [
            'matched_ledger_id' => $ledgerId,
            'status'            => 'matched',
            'matched_at'        => date('c'),
        ]);

        $this->_audit('match', 'bank_recon', $reconId, null, ['ledger_id' => $ledgerId]);
        $this->json_success(['message' => 'Transaction matched.']);
    }

    /** POST: Unmatch a previously matched bank statement item */
    public function unmatch_transaction()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code    = trim((string) $this->input->post('account_code'));
        $reconId = trim((string) $this->input->post('recon_id'));
        if (!$code || !$reconId) return $this->json_error('Account code and recon ID required.');

        $safeCode = $this->safe_path_segment($code, 'account_code');
        $reconPath = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}/{$reconId}";
        $recon = $this->firebase->get($reconPath);
        if (!is_array($recon)) return $this->json_error('Statement entry not found.');

        $oldLedgerId = $recon['matched_ledger_id'] ?? null;
        $this->firebase->update($reconPath, [
            'matched_ledger_id' => null,
            'status'            => 'unmatched',
            'matched_at'        => null,
        ]);

        $this->_audit('unmatch', 'bank_recon', $reconId, ['ledger_id' => $oldLedgerId], null);
        $this->json_success(['message' => 'Transaction unmatched.']);
    }

    /** POST: Suggest matching ledger entries for a bank statement item */
    public function suggest_matches()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code    = trim((string) $this->input->post('account_code'));
        $reconId = trim((string) $this->input->post('recon_id'));
        if (!$code || !$reconId) return $this->json_error('Required fields missing.');

        $safeCode = $this->safe_path_segment($code, 'account_code');
        $reconPath = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}/{$reconId}";
        $recon = $this->firebase->get($reconPath);
        if (!is_array($recon)) return $this->json_error('Statement entry not found.');

        $stmtAmount = max((float)($recon['debit'] ?? 0), (float)($recon['credit'] ?? 0));
        $stmtDate   = $recon['statement_date'] ?? '';

        // Get ledger entries for this account
        $ids = $this->firebase->shallow_get($this->_idx() . "/by_account/{$safeCode}");
        if (!is_array($ids)) return $this->json_success(['suggestions' => []]);

        $allLedger = $this->firebase->get($this->_ledger());
        if (!is_array($allLedger)) $allLedger = [];

        // Already matched ledger IDs
        $allRecon = $this->firebase->get($this->_bp() . "/Accounts/Bank_recon/{$safeCode}");
        $matchedIds = [];
        if (is_array($allRecon)) {
            foreach ($allRecon as $r) {
                if (is_array($r) && ($r['status'] ?? '') === 'matched' && !empty($r['matched_ledger_id'])) {
                    $matchedIds[$r['matched_ledger_id']] = true;
                }
            }
        }

        $suggestions = [];
        foreach ($ids as $id) {
            if (isset($matchedIds[$id])) continue;
            $entry = $allLedger[$id] ?? null;
            if (!is_array($entry) || ($entry['status'] ?? '') === 'deleted') continue;

            // Find this account's dr/cr
            $dr = 0;
            $cr = 0;
            foreach ($entry['lines'] ?? [] as $line) {
                if (($line['account_code'] ?? '') === $code) {
                    $dr += (float)($line['dr'] ?? 0);
                    $cr += (float)($line['cr'] ?? 0);
                }
            }
            $entryAmount = max($dr, $cr);

            // Score: exact amount match = 100, close amount = 50, date match = 30
            $score = 0;
            if (abs($entryAmount - $stmtAmount) < 0.01) {
                $score += 100;
            } elseif ($stmtAmount > 0 && abs($entryAmount - $stmtAmount) / $stmtAmount < 0.05) {
                $score += 50;
            }

            if ($stmtDate && ($entry['date'] ?? '') === $stmtDate) {
                $score += 30;
            } elseif ($stmtDate && abs(strtotime($entry['date'] ?? '') - strtotime($stmtDate)) <= 259200) {
                $score += 15; // within 3 days
            }

            if ($score >= 15) {
                $suggestions[] = [
                    'entry_id'   => $id,
                    'date'       => $entry['date'] ?? '',
                    'voucher_no' => $entry['voucher_no'] ?? '',
                    'narration'  => $entry['narration'] ?? '',
                    'dr'         => $dr,
                    'cr'         => $cr,
                    'score'      => $score,
                ];
            }
        }

        usort($suggestions, fn($a, $b) => $b['score'] - $a['score']);
        $suggestions = array_slice($suggestions, 0, 10);

        $this->json_success(['suggestions' => $suggestions]);
    }

    /** POST: Get reconciliation summary */
    public function get_recon_summary()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code = trim((string) $this->input->post('account_code'));
        if (!$code) return $this->json_error('Account code required.');

        $safeCode = $this->safe_path_segment($code, 'account_code');

        // Bank statement total
        $stmtPath = $this->_bp() . "/Accounts/Bank_recon/{$safeCode}";
        $stmt = $this->firebase->get($stmtPath);
        $bankBal = 0;
        $unmatchedCount = 0;
        if (is_array($stmt)) {
            foreach ($stmt as $item) {
                if (!is_array($item)) continue;
                $bankBal += (float) ($item['credit'] ?? 0) - (float) ($item['debit'] ?? 0);
                if (($item['status'] ?? '') === 'unmatched') $unmatchedCount++;
            }
        }

        // Book balance from closing_balances
        $bal = $this->firebase->get($this->_bal() . "/{$safeCode}");
        $bookDr = (float) ($bal['period_dr'] ?? 0);
        $bookCr = (float) ($bal['period_cr'] ?? 0);

        $acct = $this->firebase->get($this->_coa() . "/{$safeCode}");
        $openBal = (float) ($acct['opening_balance'] ?? 0);
        $bookBal = $openBal + $bookDr - $bookCr;

        $this->json_success([
            'bank_balance'   => round($bankBal, 2),
            'book_balance'   => round($bookBal, 2),
            'difference'     => round($bankBal - $bookBal, 2),
            'unmatched'      => $unmatchedCount,
        ]);
    }

    // =========================================================================
    //  TAB 6: FINANCIAL REPORTS
    // =========================================================================

    /** POST: Trial Balance.
     *
     * When as_of_date is provided, recomputes period movements from ledger
     * entries up to that date AND includes each account's opening_balance,
     * so the trial balance reflects true account balances at a point in time.
     */
    public function trial_balance()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $asOf = trim((string) $this->input->post('as_of_date'));

        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) return $this->json_success(['rows' => [], 'totals' => ['dr' => 0, 'cr' => 0]]);

        // When as_of_date is provided, compute balances from ledger entries up to that date
        if ($asOf) {
            $allLedger = $this->firebase->get($this->_ledger());
            $balances = [];
            if (is_array($allLedger)) {
                foreach ($allLedger as $entry) {
                    if (!is_array($entry) || ($entry['status'] ?? '') === 'deleted') continue;
                    if (($entry['date'] ?? '') > $asOf) continue;
                    foreach ($entry['lines'] ?? [] as $line) {
                        $ac = $line['account_code'] ?? '';
                        if (!$ac) continue;
                        if (!isset($balances[$ac])) $balances[$ac] = ['period_dr' => 0, 'period_cr' => 0];
                        $balances[$ac]['period_dr'] += (float) ($line['dr'] ?? 0);
                        $balances[$ac]['period_cr'] += (float) ($line['cr'] ?? 0);
                    }
                }
            }
        } else {
            $balances = $this->firebase->get($this->_bal());
            if (!is_array($balances)) $balances = [];
        }

        $rows = [];
        $totalDr = 0;
        $totalCr = 0;

        foreach ($coa as $code => $acct) {
            if (!is_array($acct)) continue;
            if (($acct['status'] ?? '') !== 'active') continue;
            if (!empty($acct['is_group'])) continue;

            // Opening balance is always included (both cached and as_of_date modes)
            $openBal  = (float) ($acct['opening_balance'] ?? 0);
            $periodDr = (float) ($balances[$code]['period_dr'] ?? 0);
            $periodCr = (float) ($balances[$code]['period_cr'] ?? 0);

            $normalSide = $acct['normal_side'] ?? 'Dr';
            if ($normalSide === 'Dr') {
                $closingBal = $openBal + $periodDr - $periodCr;
            } else {
                $closingBal = $openBal + $periodCr - $periodDr;
            }

            if (abs($closingBal) < 0.01) continue;

            $dr = $normalSide === 'Dr' ? max($closingBal, 0) : max(-$closingBal, 0);
            $cr = $normalSide === 'Cr' ? max($closingBal, 0) : max(-$closingBal, 0);

            $totalDr += $dr;
            $totalCr += $cr;

            $rows[] = [
                'code'            => $code,
                'name'            => $acct['name'] ?? '',
                'category'        => $acct['category'] ?? '',
                'opening_balance' => round($openBal, 2),
                'period_dr'       => round($periodDr, 2),
                'period_cr'       => round($periodCr, 2),
                'dr'              => round($dr, 2),
                'cr'              => round($cr, 2),
            ];
        }

        usort($rows, fn($a, $b) => strnatcmp($a['code'], $b['code']));

        $this->json_success([
            'rows'   => $rows,
            'totals' => ['dr' => round($totalDr, 2), 'cr' => round($totalCr, 2)],
        ]);
    }

    /** POST: Profit & Loss Statement */
    public function profit_loss()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) return $this->json_success(['income' => [], 'expenses' => [], 'net' => 0]);

        $balances = $this->firebase->get($this->_bal());
        if (!is_array($balances)) $balances = [];

        $income = [];
        $expenses = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($coa as $code => $acct) {
            if (!is_array($acct) || ($acct['status'] ?? '') !== 'active') continue;
            if (!empty($acct['is_group'])) continue;

            $cat = $acct['category'] ?? '';
            $periodDr = (float) ($balances[$code]['period_dr'] ?? 0);
            $periodCr = (float) ($balances[$code]['period_cr'] ?? 0);

            if ($cat === 'Income') {
                $amt = $periodCr - $periodDr;
                if (abs($amt) < 0.01) continue;
                $totalIncome += $amt;
                $income[] = ['code' => $code, 'name' => $acct['name'] ?? '', 'amount' => round($amt, 2)];
            } elseif ($cat === 'Expense') {
                $amt = $periodDr - $periodCr;
                if (abs($amt) < 0.01) continue;
                $totalExpense += $amt;
                $expenses[] = ['code' => $code, 'name' => $acct['name'] ?? '', 'amount' => round($amt, 2)];
            }
        }

        $this->json_success([
            'income'        => $income,
            'expenses'      => $expenses,
            'total_income'  => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'net_profit'    => round($totalIncome - $totalExpense, 2),
        ]);
    }

    /** POST: Balance Sheet */
    public function balance_sheet()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) return $this->json_success(['assets' => [], 'liabilities' => [], 'equity' => []]);

        $balances = $this->firebase->get($this->_bal());
        if (!is_array($balances)) $balances = [];

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totals = ['assets' => 0, 'liabilities' => 0, 'equity' => 0];

        // Also compute net P&L for retained earnings
        $netPL = 0;

        foreach ($coa as $code => $acct) {
            if (!is_array($acct) || ($acct['status'] ?? '') !== 'active') continue;
            if (!empty($acct['is_group'])) continue;

            $cat = $acct['category'] ?? '';
            $openBal  = (float) ($acct['opening_balance'] ?? 0);
            $periodDr = (float) ($balances[$code]['period_dr'] ?? 0);
            $periodCr = (float) ($balances[$code]['period_cr'] ?? 0);

            $row = ['code' => $code, 'name' => $acct['name'] ?? ''];

            switch ($cat) {
                case 'Asset':
                    $bal = $openBal + $periodDr - $periodCr;
                    if (abs($bal) < 0.01) continue 2;
                    $row['amount'] = round($bal, 2);
                    $totals['assets'] += $bal;
                    $assets[] = $row;
                    break;
                case 'Liability':
                    $bal = $openBal + $periodCr - $periodDr;
                    if (abs($bal) < 0.01) continue 2;
                    $row['amount'] = round($bal, 2);
                    $totals['liabilities'] += $bal;
                    $liabilities[] = $row;
                    break;
                case 'Equity':
                    $bal = $openBal + $periodCr - $periodDr;
                    if (abs($bal) < 0.01) continue 2;
                    $row['amount'] = round($bal, 2);
                    $totals['equity'] += $bal;
                    $equity[] = $row;
                    break;
                case 'Income':
                    $netPL += ($periodCr - $periodDr);
                    break;
                case 'Expense':
                    $netPL -= ($periodDr - $periodCr);
                    break;
            }
        }

        // Add net P&L as retained surplus
        if (abs($netPL) > 0.01) {
            $equity[] = ['code' => '-', 'name' => 'Current Year Surplus/Deficit', 'amount' => round($netPL, 2)];
            $totals['equity'] += $netPL;
        }

        $totalLiabilitiesEquity = round($totals['liabilities'] + $totals['equity'], 2);

        $this->json_success([
            'assets'                   => $assets,
            'liabilities'              => $liabilities,
            'equity'                   => $equity,
            'totals'                   => [
                'assets'               => round($totals['assets'], 2),
                'liabilities'          => round($totals['liabilities'], 2),
                'equity'               => round($totals['equity'], 2),
                'liabilities_equity'   => $totalLiabilitiesEquity,
            ],
            'total_liabilities_equity' => $totalLiabilitiesEquity,
        ]);
    }

    /** POST: Cash Flow Report (N+1 fixed, contra double-counting fixed) */
    public function cash_flow()
    {
        $this->_require_role(self::FINANCE_ROLES);

        // Get all cash/bank accounts
        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) return $this->json_success(['flows' => []]);

        $cashCodes = [];
        foreach ($coa as $code => $acct) {
            if (!is_array($acct)) continue;
            $sub = strtolower($acct['sub_category'] ?? '');
            if (in_array($code, ['1010', '1020']) || !empty($acct['is_bank']) || $sub === 'cash') {
                $cashCodes[$code] = $acct['name'] ?? $code;
            }
        }

        // Fetch FULL ledger once (fix N+1)
        $allLedger = $this->firebase->get($this->_ledger());
        if (!is_array($allLedger)) $allLedger = [];

        $totals = ['operating' => 0, 'investing' => 0, 'financing' => 0];

        // Track processed entry IDs to fix contra double-counting
        $processedIds = [];

        foreach (array_keys($cashCodes) as $cashCode) {
            $safeCode = $this->safe_path_segment($cashCode, 'account_code');
            $ids = $this->firebase->shallow_get($this->_idx() . "/by_account/{$safeCode}");
            if (!is_array($ids)) continue;

            foreach ($ids as $id) {
                if (isset($processedIds[$id])) continue;
                $processedIds[$id] = true;

                $entry = $allLedger[$id] ?? null;
                if (!is_array($entry)) continue;
                if (($entry['status'] ?? '') === 'deleted') continue;

                foreach ($entry['lines'] ?? [] as $line) {
                    $lineCode = $line['account_code'] ?? '';
                    if (!isset($cashCodes[$lineCode])) continue;

                    $inflow = (float) ($line['dr'] ?? 0);
                    $outflow = (float) ($line['cr'] ?? 0);
                    $net = $inflow - $outflow;
                    $flowType = 'operating'; // default

                    // Classify based on the OTHER account in the entry
                    foreach ($entry['lines'] ?? [] as $otherLine) {
                        if (isset($cashCodes[$otherLine['account_code'] ?? ''])) continue;
                        $otherCode = $otherLine['account_code'] ?? '';
                        $otherAcct = $coa[$otherCode] ?? [];
                        $otherCat = $otherAcct['category'] ?? '';
                        $otherSub = strtolower($otherAcct['sub_category'] ?? '');

                        if (strpos($otherSub, 'fixed') !== false) {
                            $flowType = 'investing';
                        } elseif (strpos($otherSub, 'loan') !== false || $otherCat === 'Equity') {
                            $flowType = 'financing';
                        }
                        break;
                    }

                    $totals[$flowType] += $net;
                }
            }
        }

        $this->json_success([
            'operating'  => round($totals['operating'], 2),
            'investing'  => round($totals['investing'], 2),
            'financing'  => round($totals['financing'], 2),
            'net_change' => round($totals['operating'] + $totals['investing'] + $totals['financing'], 2),
        ]);
    }

    /** POST: Ledger report for a single account (no $_POST mutation) */
    public function ledger_report()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $code     = trim((string) $this->input->post('account_code'));
        $dateFrom = trim((string) $this->input->post('date_from'));
        $dateTo   = trim((string) $this->input->post('date_to'));

        if (!$code) return $this->json_error('Account code required.');

        $this->_render_cash_book($code, $dateFrom, $dateTo);
    }

    /** POST: Recompute all closing balances from scratch, with integrity report */
    public function recompute_balances()
    {
        $this->_require_role(self::ADMIN_ROLES);

        // Fetch existing cache for comparison
        $oldBalances = $this->firebase->get($this->_bal());
        if (!is_array($oldBalances)) $oldBalances = [];

        $allEntries = $this->firebase->get($this->_ledger());
        if (!is_array($allEntries)) $allEntries = [];

        $balances = [];
        foreach ($allEntries as $entry) {
            if (!is_array($entry)) continue;
            if (($entry['status'] ?? '') === 'deleted') continue;
            foreach ($entry['lines'] ?? [] as $line) {
                $ac = $line['account_code'] ?? '';
                if (!$ac) continue;
                if (!isset($balances[$ac])) {
                    $balances[$ac] = ['period_dr' => 0, 'period_cr' => 0];
                }
                $balances[$ac]['period_dr'] += (float) ($line['dr'] ?? 0);
                $balances[$ac]['period_cr'] += (float) ($line['cr'] ?? 0);
            }
        }

        // Round and add timestamp
        foreach ($balances as &$b) {
            $b['period_dr'] = round($b['period_dr'], 2);
            $b['period_cr'] = round($b['period_cr'], 2);
            $b['last_computed'] = date('c');
        }
        unset($b);

        // Compute discrepancies
        $discrepancies = [];
        $allCodes = array_unique(array_merge(array_keys($oldBalances), array_keys($balances)));
        foreach ($allCodes as $code) {
            $oldDr = (float) ($oldBalances[$code]['period_dr'] ?? 0);
            $oldCr = (float) ($oldBalances[$code]['period_cr'] ?? 0);
            $newDr = (float) ($balances[$code]['period_dr'] ?? 0);
            $newCr = (float) ($balances[$code]['period_cr'] ?? 0);
            if (abs($oldDr - $newDr) > 0.01 || abs($oldCr - $newCr) > 0.01) {
                $discrepancies[] = [
                    'code'   => $code,
                    'old_dr' => $oldDr, 'old_cr' => $oldCr,
                    'new_dr' => $newDr, 'new_cr' => $newCr,
                ];
            }
        }

        $this->firebase->set($this->_bal(), $balances);
        $this->_audit('recompute_balances', 'closing_balances', 'all', null, [
            'accounts'      => count($balances),
            'discrepancies' => count($discrepancies),
        ]);
        $this->json_success([
            'message'       => 'Balances recomputed.',
            'accounts'      => count($balances),
            'discrepancies' => $discrepancies,
        ]);
    }

    // =========================================================================
    //  TAB 7: SETTINGS
    // =========================================================================

    /** GET: Get accounting settings */
    public function get_settings()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $lock = $this->firebase->get($this->_bp() . '/Accounts/Period_lock');
        $counters = $this->firebase->get($this->_bp() . '/Accounts/Voucher_counters');

        $this->json_success([
            'period_lock' => is_array($lock) ? $lock : ['locked_until' => null],
            'counters'    => is_array($counters) ? $counters : [],
        ]);
    }

    /** POST: Lock accounting period (multi-path update for finalization) */
    public function lock_period()
    {
        $this->_require_role(self::ADMIN_ROLES);

        $date = trim((string) $this->input->post('locked_until'));
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->json_error('Valid date required (YYYY-MM-DD).');
        }

        $this->firebase->set($this->_bp() . '/Accounts/Period_lock', [
            'locked_until' => $date,
            'locked_by'    => $this->admin_id,
            'locked_at'    => date('c'),
        ]);

        // Finalize all entries on or before this date using multi-path update
        $dateIdx = $this->firebase->get($this->_idx() . '/by_date');
        $updates = [];
        if (is_array($dateIdx)) {
            foreach ($dateIdx as $entryDate => $ids) {
                if ($entryDate > $date) continue;
                if (!is_array($ids)) continue;
                foreach (array_keys($ids) as $id) {
                    $updates["Ledger/{$id}/is_finalized"] = true;
                    $updates["Ledger/{$id}/finalized_at"] = date('c');
                }
            }
        }

        $finalized = 0;
        if (!empty($updates)) {
            $this->firebase->update($this->_bp() . '/Accounts', $updates);
            $finalized = (int) (count($updates) / 2);
        }

        $this->_audit('lock_period', 'period_lock', $date, null, ['finalized' => $finalized]);
        $this->json_success(['message' => "Period locked until {$date}. {$finalized} entries finalized."]);
    }

    /** GET: Check if migration has been done */
    public function get_migration_status()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $coa = $this->firebase->shallow_get($this->_coa());
        $hasBook = !empty($this->firebase->shallow_get($this->_bp() . '/Accounts/Account_book'));

        $this->json_success([
            'coa_count'    => count($coa ?: []),
            'has_old_book' => $hasBook,
        ]);
    }

    /** POST: Carry forward closing balances as opening balances for next period */
    public function carry_forward_balances()
    {
        $this->_require_role(self::ADMIN_ROLES);

        $coa = $this->firebase->get($this->_coa());
        if (!is_array($coa)) return $this->json_error('No chart of accounts found.');

        $balances = $this->firebase->get($this->_bal());
        if (!is_array($balances)) $balances = [];

        $updated = 0;
        foreach ($coa as $code => $acct) {
            if (!is_array($acct) || ($acct['status'] ?? '') !== 'active') continue;
            if (!empty($acct['is_group'])) continue;

            $openBal  = (float) ($acct['opening_balance'] ?? 0);
            $periodDr = (float) ($balances[$code]['period_dr'] ?? 0);
            $periodCr = (float) ($balances[$code]['period_cr'] ?? 0);
            $normalSide = $acct['normal_side'] ?? 'Dr';

            if ($normalSide === 'Dr') {
                $closingBal = $openBal + $periodDr - $periodCr;
            } else {
                $closingBal = $openBal + $periodCr - $periodDr;
            }

            // Only update if closing balance differs from opening
            if (abs($closingBal - $openBal) > 0.01) {
                $safeCode = $this->safe_path_segment($code, 'code');
                $this->firebase->update($this->_coa() . "/{$safeCode}", [
                    'opening_balance'      => round($closingBal, 2),
                    'balance_carried_from' => $this->session_year,
                    'updated_at'           => date('c'),
                ]);
                $updated++;
            }
        }

        $this->_audit('carry_forward', 'balances', $this->session_year, null, ['accounts_updated' => $updated]);
        $this->json_success(['message' => "Carried forward {$updated} account balances.", 'updated' => $updated]);
    }

    /** GET: Fetch audit log entries */
    public function get_audit_log()
    {
        $this->_require_role(self::FINANCE_ROLES);

        $limit = (int) ($this->input->get('limit') ?: 50);
        $all = $this->firebase->get($this->_bp() . '/Accounts/Audit_log');
        if (!is_array($all)) $all = [];

        $logs = [];
        foreach ($all as $id => $log) {
            if (!is_array($log)) continue;
            $log['id'] = $id;
            $logs[] = $log;
        }

        // Sort by timestamp desc
        usort($logs, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));
        $logs = array_slice($logs, 0, $limit);

        $this->json_success(['logs' => $logs]);
    }


    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    /** Incrementally update closing balances cache */
    private function _update_balances(array $affectedAccounts, string $op): void
    {
        foreach ($affectedAccounts as $code => $amounts) {
            $safeCode = $this->safe_path_segment($code, 'code');
            $path = $this->_bal() . "/{$safeCode}";
            $current = $this->firebase->get($path);

            $pDr = (float) ($current['period_dr'] ?? 0);
            $pCr = (float) ($current['period_cr'] ?? 0);

            if ($op === 'add') {
                $pDr += $amounts['dr'];
                $pCr += $amounts['cr'];
            } else {
                $pDr -= $amounts['dr'];
                $pCr -= $amounts['cr'];
            }

            $this->firebase->set($path, [
                'period_dr'     => round($pDr, 2),
                'period_cr'     => round($pCr, 2),
                'last_computed' => date('c'),
            ]);
        }
    }

    /** Get voucher number prefix for a type */
    private function _voucher_prefix(string $type): string
    {
        $map = [
            'Journal' => 'JV-', 'Receipt' => 'RV-', 'Payment' => 'PV-',
            'Contra'  => 'CV-', 'Fee'     => 'FV-',
        ];
        return $map[$type] ?? 'GV-';
    }

    /** Default Chart of Accounts template for Indian schools */
    private function _default_coa_template(string $ts): array
    {
        $a = [];
        $add = function ($code, $name, $cat, $sub, $parent, $group = false, $bank = false) use (&$a, $ts) {
            $a[$code] = [
                'code' => $code, 'name' => $name, 'category' => $cat,
                'sub_category' => $sub, 'parent_code' => $parent,
                'is_group' => $group, 'is_bank' => $bank,
                'normal_side' => in_array($cat, ['Asset', 'Expense']) ? 'Dr' : 'Cr',
                'description' => '', 'opening_balance' => 0,
                'status' => 'active', 'is_system' => true,
                'sort_order' => (int) $code,
                'created_at' => $ts, 'updated_at' => $ts,
            ];
        };

        // Assets (1000-1999)
        $add('1000', 'Current Assets',        'Asset', 'Current Assets',  null, true);
        $add('1010', 'Cash in Hand',           'Asset', 'Current Assets',  '1000');
        $add('1020', 'Bank Accounts',          'Asset', 'Current Assets',  '1000', true);
        $add('1030', 'Accounts Receivable',    'Asset', 'Current Assets',  '1000');
        $add('1040', 'Advance to Staff',       'Asset', 'Current Assets',  '1000');
        $add('1050', 'Deposits & Prepayments', 'Asset', 'Current Assets',  '1000');
        $add('1100', 'Fixed Assets',           'Asset', 'Fixed Assets',    null, true);
        $add('1110', 'Furniture & Fixtures',   'Asset', 'Fixed Assets',    '1100');
        $add('1120', 'Computer & Equipment',   'Asset', 'Fixed Assets',    '1100');
        $add('1130', 'Vehicles',               'Asset', 'Fixed Assets',    '1100');
        $add('1140', 'Building',               'Asset', 'Fixed Assets',    '1100');

        // Liabilities (2000-2999)
        $add('2000', 'Current Liabilities',       'Liability', 'Current Liabilities', null, true);
        $add('2010', 'Accounts Payable',           'Liability', 'Current Liabilities', '2000');
        $add('2020', 'Salary Payable',             'Liability', 'Current Liabilities', '2000');
        $add('2030', 'Tax Payable (TDS/GST)',      'Liability', 'Current Liabilities', '2000');
        $add('2040', 'Security Deposits Received', 'Liability', 'Current Liabilities', '2000');
        $add('2050', 'Advance Fees Received',      'Liability', 'Current Liabilities', '2000');
        $add('2100', 'Long-term Liabilities',      'Liability', 'Long-term Liabilities', null, true);
        $add('2110', 'Loans Payable',              'Liability', 'Long-term Liabilities', '2100');

        // Equity (3000-3999)
        $add('3000', 'Equity',           'Equity', 'Equity', null, true);
        $add('3010', 'Trust Fund/Capital','Equity', 'Equity', '3000');
        $add('3020', 'Retained Surplus',  'Equity', 'Equity', '3000');

        // Income (4000-4999)
        $add('4000', 'Fee Income',         'Income', 'Fee Income',    null, true);
        $add('4010', 'Tuition Fees',       'Income', 'Fee Income',    '4000');
        $add('4020', 'Admission Fees',     'Income', 'Fee Income',    '4000');
        $add('4030', 'Examination Fees',   'Income', 'Fee Income',    '4000');
        $add('4040', 'Transport Fees',     'Income', 'Fee Income',    '4000');
        $add('4050', 'Hostel Fees',        'Income', 'Fee Income',    '4000');
        $add('4060', 'Late Fees/Penalty',  'Income', 'Fee Income',    '4000');
        $add('4100', 'Other Income',       'Income', 'Other Income',  null, true);
        $add('4110', 'Interest Income',    'Income', 'Other Income',  '4100');
        $add('4120', 'Donation Received',  'Income', 'Other Income',  '4100');
        $add('4130', 'Rent Income',        'Income', 'Other Income',  '4100');
        $add('4140', 'Miscellaneous Income','Income','Other Income',  '4100');

        // Expenses (5000-5999)
        $add('5000', 'Staff Expenses',           'Expense', 'Staff Expenses',  null, true);
        $add('5010', 'Teaching Staff Salary',    'Expense', 'Staff Expenses',  '5000');
        $add('5020', 'Non-Teaching Staff Salary','Expense', 'Staff Expenses',  '5000');
        $add('5030', 'PF/ESI Contribution',      'Expense', 'Staff Expenses',  '5000');
        $add('5100', 'Administrative Expenses',  'Expense', 'Admin Expenses',  null, true);
        $add('5110', 'Office Supplies',          'Expense', 'Admin Expenses',  '5100');
        $add('5120', 'Printing & Stationery',   'Expense', 'Admin Expenses',  '5100');
        $add('5130', 'Communication',            'Expense', 'Admin Expenses',  '5100');
        $add('5140', 'Travel & Conveyance',     'Expense', 'Admin Expenses',  '5100');
        $add('5150', 'Repairs & Maintenance',   'Expense', 'Admin Expenses',  '5100');
        $add('5160', 'Insurance',                'Expense', 'Admin Expenses',  '5100');
        $add('5170', 'Legal & Professional',    'Expense', 'Admin Expenses',  '5100');
        $add('5180', 'Bank Charges',             'Expense', 'Admin Expenses',  '5100');
        $add('5200', 'Educational Expenses',     'Expense', 'Educational',     null, true);
        $add('5210', 'Books & Library',          'Expense', 'Educational',     '5200');
        $add('5220', 'Laboratory Expenses',      'Expense', 'Educational',     '5200');
        $add('5230', 'Sports & Games',           'Expense', 'Educational',     '5200');
        $add('5240', 'Cultural Activities',      'Expense', 'Educational',     '5200');
        $add('5300', 'Utilities',                'Expense', 'Utilities',       null, true);
        $add('5310', 'Electricity',              'Expense', 'Utilities',       '5300');
        $add('5320', 'Water',                    'Expense', 'Utilities',       '5300');
        $add('5330', 'Generator/Fuel',           'Expense', 'Utilities',       '5300');

        return $a;
    }
}
