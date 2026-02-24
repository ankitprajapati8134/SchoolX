<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Account Controller — fully audited & fixed
 *
 * FIXES APPLIED vs original:
 *  account_book()           — Content-Type header, (array) cast, removed dead comment
 *  populateTable()          — Opening balance now works for mid-year accounts, Content-Type, variable rename
 *  create_account()         — Returns JSON (no redirect after AJAX), opening cast to float, removed duplicate assignment
 *  update_account()         — Content-Type, skip unnecessary write when name unchanged, Firebase char sanitize
 *  delete_account()         — Content-Type, server-side Default Account guard
 *  save_voucher()           — Voucher key uses 'V' prefix to prevent numeric-array bug, date validation,
 *                             Journal now updates account book, Content-Type
 *  view_accounts()          — Date comparison fixed (timestamp not string), Y-m-d input handled,
 *                             All voucher types now checked (Fees Received, Receipt, Contra, Journal)
 *  cash_book_month()        — (array) casts, string key '1' fix for Opening
 *  cash_book_dates()        — Month-to-number map replaces fragile strtotime(), ₹ stripped from opening
 *  cash_book_details()      — Reads correct voucher keys (Fees Received, Receipt, Payment)
 *  calculate_current_balances() — Uses $this->firebase->get() consistently
 *  get_receipt_no()         — Added (was missing, referenced by fees_counter frontend)
 */
class Account extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ══════════════════════════════════════════════════════════
       HELPERS
    ══════════════════════════════════════════════════════════ */

    /** Set JSON content type and output in one call */
    private function jsonOut($data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * Cast a Firebase value to a plain PHP associative array recursively.
     * Firebase PHP libs sometimes return stdClass; (array) flattens one level only.
     */
    private function toArray($val)
    {
        return json_decode(json_encode($val), true);
    }

    /**
     * Sanitize an account name for use as a Firebase path segment.
     * Firebase forbids: . # $ [ ] /
     */
    private function sanitizeKey($name)
    {
        return str_replace(['/', '.', '#', '$', '[', ']'], '-', trim($name));
    }

    /**
     * FIX cash_book_dates: strtotime('April') is unreliable.
     * Use a hard-coded month map instead.
     */
    private static $monthMap = [
        'January'   => '01',
        'February'  => '02',
        'March'     => '03',
        'April'     => '04',
        'May'        => '05',
        'June'      => '06',
        'July'      => '07',
        'August'     => '08',
        'September' => '09',
        'October'   => '10',
        'November'   => '11',
        'December'  => '12',
    ];

    /* ══════════════════════════════════════════════════════════
       ACCOUNT BOOK
    ══════════════════════════════════════════════════════════ */

    public function account_book()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accounts = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book")
        );

        $currentYear    = date('Y');
        $currentMonth   = date('m');
        $current_session = ($currentMonth < 4)
            ? ($currentYear - 1) . '-' . $currentYear
            : $currentYear . '-' . ($currentYear + 1);

        if ($this->input->is_ajax_request()) {
            $selectedAccountName = $this->input->post('selectedAccountName');

            if (isset($accounts[$selectedAccountName])) {
                $this->jsonOut([
                    'selectedAccount' => $accounts[$selectedAccountName],
                    'current_session' => $current_session,
                ]);
            } else {
                log_message('error', 'Account not found: ' . $selectedAccountName);
                $this->jsonOut(['error' => 'Account not found']);
            }
            return;
        }

        $data['accounts']        = $accounts;
        $data['current_session'] = $current_session;
        $data['session_year']    = $session_year;

        $this->load->view('include/header');
        $this->load->view('account_book', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════
       POPULATE TABLE (month-wise ledger for account_book view)
    ══════════════════════════════════════════════════════════ */

    public function populateTable()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accountName = $this->input->post('selectedAccountName');
        $accountData = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book/$accountName")
        );

        $months = [
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
            'January',
            'February',
            'March'
        ];

        $matrix              = array_fill(0, 12, ['Opening' => 0.0, 'Received' => 0.0, 'Payments' => 0.0, 'Balance' => 0.0]);
        $previousMonthBalance = 0.0;

        /*
         * FIX: Find the opening balance from ANY month, not just April.
         * Walk all months until we find a day entry that has an 'Opening' key.
         * Once found, use it as the starting balance for that month.
         */
        $openingFoundAtIndex = -1;
        $openingValue        = 0.0;

        foreach ($months as $index => $month) {
            if (!isset($accountData[$month])) continue;
            foreach ((array)$accountData[$month] as $dayData) {
                $dayData = (array)$dayData;
                if (isset($dayData['Opening'])) {
                    $openingValue        = (float)$dayData['Opening'];
                    $openingFoundAtIndex = $index;
                    break 2;
                }
            }
        }

        foreach ($months as $index => $month) {
            $monthData     = isset($accountData[$month]) ? (array)$accountData[$month] : [];
            $totalReceived = 0.0;
            $totalPayments = 0.0;

            foreach ($monthData as $dayData) {
                $dayData = (array)$dayData;
                if (isset($dayData['R'])) $totalReceived += (float)$dayData['R'];
                if (isset($dayData['P'])) $totalPayments += (float)$dayData['P'];
            }

            $matrix[$index]['Received'] = $totalReceived;
            $matrix[$index]['Payments'] = $totalPayments;

            // Set opening: either the found opening value or carry-forward from previous month
            if ($index === $openingFoundAtIndex) {
                $matrix[$index]['Opening'] = $openingValue;
                $previousMonthBalance      = $openingValue;
            } elseif ($index < $openingFoundAtIndex) {
                // Months before the account was created — zero everything
                $matrix[$index]['Opening']  = 0.0;
                $matrix[$index]['Received'] = 0.0;
                $matrix[$index]['Payments'] = 0.0;
            } else {
                $matrix[$index]['Opening'] = $previousMonthBalance;
            }

            $matrix[$index]['Balance'] = $matrix[$index]['Opening']
                + $matrix[$index]['Received']
                - $matrix[$index]['Payments'];

            $previousMonthBalance = $matrix[$index]['Balance'];
        }

        $this->jsonOut([
            'matrix'        => $matrix,
            'totalReceived' => array_sum(array_column($matrix, 'Received')),
            'totalPayments' => array_sum(array_column($matrix, 'Payments')),
        ]);
    }

    /* ══════════════════════════════════════════════════════════
       CREATE ACCOUNT
    ══════════════════════════════════════════════════════════ */

    public function create_account()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accountName   = $this->sanitizeKey($this->input->post('accountName'));
        $subGroup      = $this->input->post('subGroup');
        // FIX: cast opening to float, strip commas
        $openingAmount = (float)str_replace(',', '', $this->input->post('openingAmount') ?? '0');

        if (empty($accountName) || empty($subGroup)) {
            $this->jsonOut(['success' => false, 'message' => 'Account name and sub-group are required']);
            return;
        }

        $createdOn    = date('d/m/Y');
        $createdMonth = date('F');
        $createdDay   = date('j');

        // FIX: build $accountData cleanly — no duplicate key assignment
        $accountData = [
            'Created On' => $createdOn,
            'Under'      => $subGroup,
            'April' => [
                '1' => ['Opening' => $openingAmount, 'P' => 0, 'R' => 0],
            ],
            'May'       => ['1' => ['P' => 0, 'R' => 0]],
            'June'      => ['1' => ['P' => 0, 'R' => 0]],
            'July'      => ['1' => ['P' => 0, 'R' => 0]],
            'August'    => ['1' => ['P' => 0, 'R' => 0]],
            'September' => ['1' => ['P' => 0, 'R' => 0]],
            'October'   => ['1' => ['P' => 0, 'R' => 0]],
            'November'  => ['1' => ['P' => 0, 'R' => 0]],
            'December'  => ['1' => ['P' => 0, 'R' => 0]],
            'January'   => ['1' => ['P' => 0, 'R' => 0]],
            'February'  => ['1' => ['P' => 0, 'R' => 0]],
            'March'     => ['1' => ['P' => 0, 'R' => 0]],
        ];

        // Add or overwrite current month's current day entry (non-April creation)
        if ($createdMonth !== 'April') {
            $accountData[$createdMonth][(string)$createdDay] = ['P' => 0, 'R' => 0];
        }

        // Optional bank fields
        foreach (['branchName', 'accountHolder', 'accountNumber', 'ifscCode'] as $field) {
            $val = $this->input->post($field);
            if (!empty($val)) $accountData[$field] = $val;
        }

        $firebasePath = "Schools/$school_name/$session_year/Accounts/Account_book/$accountName";
        $this->firebase->set($firebasePath, $accountData);  // set() not update() — clean write

        // FIX: return JSON so AJAX success handler receives it (not redirect)
        $this->jsonOut(['success' => true, 'message' => 'Account created successfully', 'accountName' => $accountName]);
    }

    /* ══════════════════════════════════════════════════════════
       CHECK ACCOUNT
    ══════════════════════════════════════════════════════════ */

    public function check_account()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accountName = trim($this->input->post('accountName') ?? '');
        if ($accountName === '') {
            $this->jsonOut(['exists' => false]);
            return;
        }

        $existingAccounts = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book")
        );

        $exists = false;
        if (is_array($existingAccounts)) {
            foreach (array_keys($existingAccounts) as $key) {
                if (strcasecmp($key, $accountName) === 0) {
                    $exists = true;
                    break;
                }
            }
        }

        $this->jsonOut(['exists' => $exists]);
    }

    /* ══════════════════════════════════════════════════════════
       UPDATE ACCOUNT
    ══════════════════════════════════════════════════════════ */

    public function update_account()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accountId      = $this->input->post('accountId');
        // FIX: sanitize new name for Firebase special chars
        $newAccountName = $this->sanitizeKey($this->input->post('accountName'));
        $subGroup       = $this->input->post('subGroup');

        if (empty($accountId) || empty($newAccountName) || empty($subGroup)) {
            $this->jsonOut(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $firebasePath = "Schools/$school_name/$session_year/Accounts/Account_book/$accountId";
        $accountData  = $this->toArray($this->firebase->get($firebasePath));

        if (!$accountData) {
            $this->jsonOut(['success' => false, 'message' => 'Account not found']);
            return;
        }

        $accountData['Under'] = $subGroup;
        foreach (['branchName', 'accountHolder', 'accountNumber', 'ifscCode'] as $field) {
            $accountData[$field] = $this->input->post($field) ?? '';
        }

        $newPath = "Schools/$school_name/$session_year/Accounts/Account_book/$newAccountName";

        if ($accountId !== $newAccountName) {
            // Rename: write to new path, delete old
            $this->firebase->set($newPath, $accountData);
            $this->firebase->delete($firebasePath);
        } else {
            // FIX: same name — use update() to avoid rewriting all month data
            $this->firebase->update($firebasePath, [
                'Under'         => $accountData['Under'],
                'branchName'    => $accountData['branchName'],
                'accountHolder' => $accountData['accountHolder'],
                'accountNumber' => $accountData['accountNumber'],
                'ifscCode'      => $accountData['ifscCode'],
            ]);
        }

        $this->jsonOut(['success' => true, 'message' => 'Account updated successfully']);
    }

    /* ══════════════════════════════════════════════════════════
       DELETE ACCOUNT
    ══════════════════════════════════════════════════════════ */

    public function delete_account()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accountName = trim($this->input->post('accountName') ?? '');

        if (empty($accountName)) {
            $this->jsonOut(['status' => 'error', 'message' => 'No account name provided']);
            return;
        }

        // FIX: server-side guard — frontend-only protection is not enough
        if (strtolower($accountName) === 'default account') {
            $this->jsonOut(['status' => 'error', 'message' => 'Default Account cannot be deleted']);
            return;
        }

        try {
            $this->firebase->delete("Schools/$school_name/$session_year/Accounts/Account_book/$accountName");
            $this->jsonOut(['status' => 'success', 'message' => 'Account deleted']);
        } catch (Exception $e) {
            $this->jsonOut(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /* ══════════════════════════════════════════════════════════
       SAVE VOUCHER
    ══════════════════════════════════════════════════════════ */

    public function save_voucher()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $date    = $this->input->post('date');
        $voucher = $this->input->post('voucher');

        // FIX: validate date before calling ->format() — prevents fatal error
        $date_obj = DateTime::createFromFormat('d-m-Y', $date);
        if (!$date_obj) {
            $this->jsonOut(['status' => 'error', 'message' => 'Invalid date format. Expected d-m-Y']);
            return;
        }

        $month = $date_obj->format('F');   // e.g. "April"
        $day   = $date_obj->format('d');   // e.g. "01"

        // Fetch current VoucherCount
        $count_path    = "Schools/{$school_name}/{$session_year}/Accounts/Vouchers/VoucherCount";
        $voucher_count = (int)($this->firebase->get($count_path) ?? 0);

        /*
         * FIX (CRITICAL): VoucherCount was used as the key directly (e.g. key = "0", "1", "2").
         * Firebase treats purely numeric keys as array indices and returns a JSON array on GET.
         * That causes the indexed-null bug in show_vouchers.
         * FIX: prefix with 'V' so key is always a string: "V0", "V1", "V2"...
         */
        $voucher_key  = 'V' . $voucher_count;
        $voucher_path = "Schools/{$school_name}/{$session_year}/Accounts/Vouchers/{$date}/{$voucher_key}";

        $this->firebase->set($voucher_path, $voucher);

        // Increment count AFTER successful write
        $this->firebase->set($count_path, $voucher_count + 1);

        $account_name = $voucher['Acc'];
        $mode         = $voucher['Mode'];

        $account_path = "Schools/{$school_name}/{$session_year}/Accounts/Account_book/{$account_name}/{$month}/{$day}";
        $mode_path    = "Schools/{$school_name}/{$session_year}/Accounts/Account_book/{$mode}/{$month}/{$day}";

        $updateR = function ($path, $amount) {
            $cur = (float)($this->firebase->get("$path/R") ?? 0);
            $this->firebase->set("$path/R", $cur + $amount);
        };
        $updateP = function ($path, $amount) {
            $cur = (float)($this->firebase->get("$path/P") ?? 0);
            $this->firebase->set("$path/P", $cur + $amount);
        };

        if (isset($voucher['Receipt'])) {
            $amt = (float)$voucher['Receipt'];
            $updateR($account_path, $amt);
            $updateR($mode_path, $amt);
        }

        if (isset($voucher['Payment'])) {
            $amt = (float)$voucher['Payment'];
            $updateP($account_path, $amt);
            $updateP($mode_path, $amt);
        }

        if (isset($voucher['Contra'])) {
            // Contra: debit account, credit mode (cash withdrawn = payment from bank, receipt for cash)
            $amt = (float)$voucher['Contra'];
            $updateP($account_path, $amt);
            $updateR($mode_path, $amt);
        }

        // FIX: Journal now actually updates the account book (was fully commented out)
        if (isset($voucher['Journal'])) {
            $amt = (float)$voucher['Journal'];
            // Journal = debit entry on the account
            $updateP($account_path, $amt);
        }

        $this->jsonOut(['status' => 'success', 'message' => 'Voucher saved']);
    }

    /* ══════════════════════════════════════════════════════════
       VIEW VOUCHER  (page loader only)
    ══════════════════════════════════════════════════════════ */

    public function view_voucher()
    {
        $this->load->view('include/header');
        $this->load->view('view_voucher');
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════
       SHOW VOUCHERS  (already fully fixed — unchanged)
    ══════════════════════════════════════════════════════════ */

    public function show_vouchers()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $from_date    = $this->input->post('from_date');
        $to_date      = $this->input->post('to_date');
        $voucher_type = $this->input->post('voucher_type');

        $from_ts = strtotime($from_date);
        $to_ts   = strtotime($to_date);

        $vouchers_data = $this->toArray(
            $this->firebase->get("Schools/{$school_name}/{$session_year}/Accounts/Vouchers/")
        );

        if (empty($vouchers_data)) {
            $this->jsonOut([]);
            return;
        }

        $type_map = [
            'Payment'       => 'Dr. Amt',
            'Journal'       => 'Dr. Amt',
            'Receipt'       => 'Cr. Amt',
            'Contra'        => 'Cr. Amt',
            'Fees Received' => 'Cr. Amt',
        ];

        $prepared_vouchers = [];

        foreach ($vouchers_data as $date => $vouchers_on_date) {
            if (!is_array($vouchers_on_date) && !is_object($vouchers_on_date)) continue;

            $date_ts = strtotime(str_replace('-', '/', $date));
            if ($date_ts === false || $date_ts < $from_ts || $date_ts > $to_ts) continue;

            foreach ((array)$vouchers_on_date as $voucher_details) {
                if (empty($voucher_details)) continue;
                $vd = (array)$voucher_details;

                $particular   = $vd['Acc']   ?? '';
                $payment_mode = $vd['Mode']  ?? '';
                $refer        = $vd['Refer'] ?? '';
                $id           = $vd['Id']    ?? null;

                foreach ($type_map as $type_key => $amt_col) {
                    if (!isset($vd[$type_key])) continue;
                    if ($voucher_type !== 'All' && $voucher_type !== $type_key) continue;

                    $entry = [
                        'Date'         => $date,
                        'Type'         => $type_key,
                        'Particular'   => $particular,
                        'Payment Mode' => $payment_mode,
                        'Dr. Amt'      => null,
                        'Cr. Amt'      => null,
                        'Refer'        => $refer,
                    ];
                    $entry[$amt_col] = $vd[$type_key];
                    if ($id) $entry['Id'] = $id;
                    $prepared_vouchers[] = $entry;
                }
            }
        }

        usort($prepared_vouchers, function ($a, $b) {
            return strtotime(str_replace('-', '/', $b['Date']))
                - strtotime(str_replace('-', '/', $a['Date']));
        });

        $this->jsonOut($prepared_vouchers);
    }

    /* ══════════════════════════════════════════════════════════
       VIEW ACCOUNTS  (ledger with date-range filter)
    ══════════════════════════════════════════════════════════ */

    public function view_accounts()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        if ($this->input->post()) {

            // FIX: HTML date inputs send Y-m-d, NOT d-m-Y
            // Use strtotime() directly — no createFromFormat('d-m-Y') on Y-m-d input
            $fromDate    = $this->input->post('fromDate');   // "2026-02-20"
            $toDate      = $this->input->post('toDate');     // "2026-02-23"
            $accountType = $this->input->post('accountType');

            $from_ts = strtotime($fromDate);
            $to_ts   = strtotime($toDate);

            if (!$from_ts || !$to_ts) {
                $this->jsonOut(['status' => 'error', 'message' => 'Invalid date format']);
                return;
            }

            $vouchersData = $this->toArray(
                $this->firebase->get("Schools/$school_name/$session_year/Accounts/Vouchers")
            );

            if (empty($vouchersData)) {
                $this->jsonOut(['status' => 'success', 'data' => []]);
                return;
            }

            /*
             * FIX: map ALL voucher types including Fees Received, Receipt, Contra, Journal
             * Original only checked ['Payment','Received'] — fees vouchers were invisible
             */
            $type_map = [
                'Payment'       => ['col' => 'Dr Amt', 'label' => 'Payment'],
                'Journal'       => ['col' => 'Dr Amt', 'label' => 'Journal'],
                'Receipt'       => ['col' => 'Cr Amt', 'label' => 'Receipt'],
                'Contra'        => ['col' => 'Cr Amt', 'label' => 'Contra'],
                'Fees Received' => ['col' => 'Cr Amt', 'label' => 'Fees Received'],
            ];

            $filteredVouchers = [];

            foreach ($vouchersData as $voucherDate => $voucherDetails) {
                if (!is_array($voucherDetails) && !is_object($voucherDetails)) continue;

                // FIX: timestamp comparison — string compare on d-m-Y is wrong
                $date_ts = strtotime(str_replace('-', '/', $voucherDate));
                if ($date_ts === false || $date_ts < $from_ts || $date_ts > $to_ts) continue;

                foreach ((array)$voucherDetails as $details) {
                    if (!is_array($details) && !is_object($details)) continue;
                    $details = (array)$details;

                    $acc = $details['Acc'] ?? '';
                    if ($accountType !== 'All' && $acc !== $accountType) continue;

                    foreach ($type_map as $typeKey => $cfg) {
                        if (!isset($details[$typeKey])) continue;

                        $amount = (float)str_replace(',', '', $details[$typeKey]);
                        $entry  = [
                            'Date'        => $voucherDate,
                            'Type'        => $cfg['label'],
                            'Particulars' => $acc,
                            'Cr Amt'      => ($cfg['col'] === 'Cr Amt') ? $amount : 0,
                            'Dr Amt'      => ($cfg['col'] === 'Dr Amt') ? $amount : 0,
                            'Mode'        => $details['Mode'] ?? '',
                        ];
                        if (isset($details['Id'])) $entry['Id'] = $details['Id'];
                        $filteredVouchers[] = $entry;
                    }
                }
            }

            // Sort descending by date
            usort($filteredVouchers, function ($a, $b) {
                return strtotime(str_replace('-', '/', $b['Date']))
                    - strtotime(str_replace('-', '/', $a['Date']));
            });

            $this->jsonOut(['status' => 'success', 'data' => $filteredVouchers]);
        } else {
            // Page load — fetch account type list
            $accountKeys   = $this->toArray(
                $this->CM->get_data("Schools/$school_name/$session_year/Accounts/Account_book")
            );
            $data['accountTypes'] = !empty($accountKeys) ? array_keys($accountKeys) : [];
            $data['session_year'] = $session_year;

            $this->load->view('include/header');
            $this->load->view('view_accounts', $data);
            $this->load->view('include/footer');
        }
    }

    /* ══════════════════════════════════════════════════════════
       DAY BOOK  (page loader only)
    ══════════════════════════════════════════════════════════ */

    public function day_book()
    {
        $this->load->view('include/header');
        $this->load->view('day_book');
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════
       CASH BOOK  (page loader + balance calc)
    ══════════════════════════════════════════════════════════ */

    public function cash_book()
    {
        $this->load->library('firebase');
        $accountBalances    = $this->calculate_current_balances();
        $data['accounts']   = $accountBalances;
        $data['session_year'] = $this->session_year;

        $this->load->view('include/header');
        $this->load->view('cash_book', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════
       GET SERVER DATE
    ══════════════════════════════════════════════════════════ */

    public function get_server_date()
    {
        $this->jsonOut(['date' => date('d-m-Y')]);
    }

    /* ══════════════════════════════════════════════════════════
       GET RECEIPT NO  (FIX: was missing — referenced by fees_counter frontend)
    ══════════════════════════════════════════════════════════ */

    public function get_receipt_no()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $count = $this->firebase->get("Schools/$school_name/$session_year/Accounts/Fees/Receipt No");
        $next  = is_numeric($count) ? ((int)$count + 1) : 1;

        $this->jsonOut(['receiptNo' => (string)$next]);
    }

    /* ══════════════════════════════════════════════════════════
       CALCULATE CURRENT BALANCES  (private, used by cash_book())
    ══════════════════════════════════════════════════════════ */

    private function calculate_current_balances()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $todayParts    = explode('-', date('d-m-Y'));
        $currentDay    = (int)$todayParts[0];
        $currentMonth  = (int)$todayParts[1];
        $currentYear   = (int)$todayParts[2];

        $fyStart = ($currentMonth >= 4) ? $currentYear : ($currentYear - 1);

        // FIX: use $this->firebase->get() consistently — not getDatabase()->getReference()->getValue()
        // which requires a different Firebase library init pattern
        $accountsData = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book")
        );

        if (empty($accountsData)) return [];

        $filteredAccounts = [];

        foreach ($accountsData as $accountName => $accountDetails) {
            if (!isset($accountDetails['Under'])) continue;

            $accountType = $accountDetails['Under'];
            if ($accountType !== 'CASH' && $accountType !== 'BANK ACCOUNT') continue;

            $openingBalance = 0.0;
            $totalReceived  = 0.0;
            $totalPayment   = 0.0;

            // Months in financial year order: Apr-Dec of fyStart, then Jan-Mar of fyStart+1
            $monthsToProcess = [];
            for ($m = 4; $m <= 12; $m++) {
                $monthsToProcess[] = ['month' => $m, 'year' => $fyStart];
            }
            // Only include Jan-Mar if current month is in that range or we've passed December
            for ($m = 1; $m <= 3; $m++) {
                $monthsToProcess[] = ['month' => $m, 'year' => $fyStart + 1];
            }

            $openingSet = false;

            foreach ($monthsToProcess as $entry) {
                $m         = $entry['month'];
                $y         = $entry['year'];
                $monthName = date('F', mktime(0, 0, 0, $m, 1, $y));

                if (!isset($accountDetails[$monthName])) continue;

                foreach ((array)$accountDetails[$monthName] as $day => $transaction) {
                    $transaction = (array)$transaction;

                    // Find opening balance (first occurrence)
                    if (!$openingSet && isset($transaction['Opening'])) {
                        $openingBalance = (float)$transaction['Opening'];
                        $openingSet     = true;
                    }

                    // Only include transactions up to today
                    $txInPast = ($y < $currentYear)
                        || ($y === $currentYear && $m < $currentMonth)
                        || ($y === $currentYear && $m === $currentMonth && (int)$day <= $currentDay);

                    if ($txInPast) {
                        $totalReceived += isset($transaction['R']) ? (float)$transaction['R'] : 0;
                        $totalPayment  += isset($transaction['P']) ? (float)$transaction['P'] : 0;
                    }
                }
            }

            $currentBalance    = $openingBalance + $totalReceived - $totalPayment;
            $filteredAccounts[] = [
                'Account Name'    => $accountName,
                'Opening Balance' => number_format($openingBalance, 2),
                'Total Received'  => number_format($totalReceived,  2),
                'Total Payment'   => number_format($totalPayment,   2),
                'Current Balance' => number_format($currentBalance, 2),
            ];
        }

        return $filteredAccounts;
    }

    /* ══════════════════════════════════════════════════════════
       CASH BOOK — MONTH WISE
    ══════════════════════════════════════════════════════════ */

    public function cash_book_month()
    {
        $school_name    = $this->school_name;
        $session_year   = $this->session_year;
        $selectedAccount = $this->input->get_post('account_name');

        if (empty($selectedAccount)) {
            $this->jsonOut(['status' => 'error', 'message' => 'No account selected']);
            return;
        }

        $accountData = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book/$selectedAccount")
        );

        if (empty($accountData)) {
            $this->jsonOut(['status' => 'error', 'message' => 'No data found for selected account']);
            return;
        }

        $months          = [
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
            'January',
            'February',
            'March'
        ];
        $cashBookData    = [];
        $previousBalance = 0.0;
        $openingSet      = false;

        foreach ($months as $month) {
            if (!isset($accountData[$month])) continue;

            $monthlyData   = (array)$accountData[$month];
            $monthPayments = 0.0;
            $monthReceived = 0.0;

            foreach ($monthlyData as $dayData) {
                $dayData = (array)$dayData;
                $monthPayments += (float)($dayData['P'] ?? 0);
                $monthReceived += (float)($dayData['R'] ?? 0);
            }

            // FIX: use string key '1' — Firebase returns string keys, not integer keys
            if (!$openingSet && isset($monthlyData['1']['Opening'])) {
                $previousBalance += (float)$monthlyData['1']['Opening'];
                $openingSet       = true;
            }

            $balance = $previousBalance + $monthReceived - $monthPayments;

            $cashBookData[] = [
                'month'    => $month,
                'opening'  => $previousBalance,
                'received' => $monthReceived,
                'payments' => $monthPayments,
                'balance'  => $balance,
            ];

            $previousBalance = $balance;
        }

        $this->jsonOut(['status' => 'success', 'data' => $cashBookData]);
    }

    /* ══════════════════════════════════════════════════════════
       CASH BOOK — DATE WISE
    ══════════════════════════════════════════════════════════ */

    public function cash_book_dates()
    {
        $school_name    = $this->school_name;
        $session_year   = $this->session_year;

        [$startYear, $endYear] = explode('-', $session_year);

        $selectedAccount = $this->input->get_post('account_name');
        $selectedMonth   = $this->input->post('month');

        // FIX: strip both commas AND ₹ symbol before casting
        $rawOpening = $this->input->post('opening') ?? '0';
        $opening    = (float)preg_replace('/[^0-9.\-]/', '', $rawOpening);

        if (empty($selectedAccount) || empty($selectedMonth)) {
            $this->jsonOut(['status' => 'error', 'message' => 'Invalid account or month']);
            return;
        }

        // FIX: use hard-coded month map instead of fragile strtotime('April')
        $monthNum = self::$monthMap[$selectedMonth] ?? null;
        if (!$monthNum) {
            $this->jsonOut(['status' => 'error', 'message' => 'Unknown month: ' . $selectedMonth]);
            return;
        }

        $monthsAfterApril = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $currentYear      = in_array($selectedMonth, $monthsAfterApril) ? $startYear : $endYear;

        $accountPath = "Schools/$school_name/$session_year/Accounts/Account_book/$selectedAccount/$selectedMonth";
        $monthData   = $this->toArray($this->firebase->get($accountPath));

        if (empty($monthData)) {
            $this->jsonOut(['status' => 'error', 'message' => 'No data found for this account in the selected month']);
            return;
        }

        $dateRecords = [];
        foreach ($monthData as $key => $dayData) {
            $dayData  = (array)$dayData;
            $payments = (float)($dayData['P'] ?? 0);
            $received = (float)($dayData['R'] ?? 0);
            // FIX: use $monthNum from map — not strtotime-derived value
            $date     = str_pad((string)$key, 2, '0', STR_PAD_LEFT) . '-' . $monthNum . '-' . $currentYear;

            $dateRecords[$date] = [
                'date'     => $date,
                'opening'  => 0.0,
                'payments' => $payments,
                'received' => $received,
                'balance'  => 0.0,
            ];
        }

        ksort($dateRecords);

        $output  = [];
        $balance = $opening;
        foreach ($dateRecords as &$data) {
            $data['opening'] = $balance;
            $data['balance'] = $balance + $data['received'] - $data['payments'];
            $balance         = $data['balance'];
            $output[]        = $data;
        }

        $this->jsonOut(['status' => 'success', 'data' => $output]);
    }

    /* ══════════════════════════════════════════════════════════
       CASH BOOK — DAY DETAIL
    ══════════════════════════════════════════════════════════ */

    public function cash_book_details()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $selectedDate = $this->input->post('date');

        $vouchers = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Vouchers/$selectedDate")
        );

        $result = [];

        if (!empty($vouchers) && is_array($vouchers)) {
            foreach ($vouchers as $key => $voucher) {
                if (!is_array($voucher)) continue;

                $account = $voucher['Acc'] ?? 'N/A';

                /*
                 * FIX (HIGH): original read $voucher['Received'] — a key that NEVER exists.
                 * submit_fees writes 'Fees Received' (with space).
                 * save_voucher writes 'Receipt'.
                 * Check all possible received-type keys in priority order.
                 */
                $received = 0.0;
                foreach (['Receipt', 'Fees Received', 'Contra', 'Received'] as $rKey) {
                    if (isset($voucher[$rKey])) {
                        $received = (float)str_replace(',', '', $voucher[$rKey]);
                        break;
                    }
                }

                /*
                 * FIX (HIGH): original read $voucher['Payment'] — save_voucher writes 'Payment'
                 * but submit_fees writes nothing for payment (fees are credits only).
                 * Also check 'Journal' as a debit entry.
                 */
                $payment = 0.0;
                foreach (['Payment', 'Journal'] as $pKey) {
                    if (isset($voucher[$pKey])) {
                        $payment = (float)str_replace(',', '', $voucher[$pKey]);
                        break;
                    }
                }

                if ($account === 'N/A' && $received == 0 && $payment == 0) continue;

                $result[] = [
                    'date'      => $selectedDate,
                    'account'   => $account,
                    'received'  => $received,
                    'payment'   => $payment,
                    'reference' => $voucher['Refer'] ?? '',
                    'id'        => $voucher['Id']    ?? '',
                    'mode'      => $voucher['Mode']  ?? '',
                ];
            }
        }

        $this->jsonOut($result);
    }

    /* ══════════════════════════════════════════════════════════
       VOUCHERS PAGE  (page loader only)
    ══════════════════════════════════════════════════════════ */

    public function vouchers()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $accounts = $this->toArray(
            $this->firebase->get("Schools/$school_name/$session_year/Accounts/Account_book")
        );

        $accountsList            = [];
        $accountsUnderBankAccount = [];

        if (is_array($accounts) && !empty($accounts)) {
            foreach ($accounts as $accountName => $accountDetails) {
                if (
                    isset($accountDetails['Under']) &&
                    ($accountDetails['Under'] === 'BANK ACCOUNT' || $accountDetails['Under'] === 'CASH')
                ) {
                    $accountsUnderBankAccount[] = $accountName;
                }
            }
            $accountsList = array_keys($accounts);
        }

        $data['accounts']   = $accountsList;
        $data['accounts_2'] = $accountsUnderBankAccount;
        $data['session_year'] = $session_year;

        $this->load->view('include/header');
        $this->load->view('manage_voucher', $data);
        $this->load->view('include/footer');
    }

    /* ══════════════════════════════════════════════════════════
       TEST  (dev only — remove before production)
    ══════════════════════════════════════════════════════════ */

    public function test()
    {
        $this->load->view('include/header');
        $this->load->view('test');
        $this->load->view('include/footer');
    }
}
