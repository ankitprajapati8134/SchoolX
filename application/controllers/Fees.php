<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fees extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ══════════════════════════════════════════════════════════════════
    //  PRIVATE PATH HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Firebase path for a class+section fee chart.
     * Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th/Section A
     */
    private function feesPath($class, $section)
    {
        $sn = $this->school_name;
        $sy = $this->session_year;
        return "Schools/$sn/$sy/Accounts/Fees/Classes Fees/$class/$section";
    }

    /**
     * Firebase path for students in a class+section.
     * Schools/{sn}/{sy}/Class 8th/Section A/Students[/{uid}]
     */
    private function studentPath($class, $section, $userId = '')
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        $section = preg_replace('/^Section\s+/i', '', trim($section));
        $section = 'Section ' . strtoupper($section);

        $base = "Schools/$sn/$sy/$class/$section/Students";
        return $userId ? "$base/$userId" : $base;
    }

    // ══════════════════════════════════════════════════════════════════
    //  CLASS + SECTION PARSERS
    // ══════════════════════════════════════════════════════════════════

    private function parseClassSection($classString)
    {
        $classString = trim((string)$classString);
        if ($classString === '') return ['', ''];

        $stripped = preg_replace('/^Class\s+/i', '', $classString);

        // "8th Section A" or "8th Section B"
        if (preg_match('/^(.+?)\s+Section\s+([A-Z0-9]+)\s*$/i', $stripped, $m)) {
            return ['Class ' . trim($m[1]), 'Section ' . strtoupper(trim($m[2]))];
        }

        // "8th B"
        $parts    = preg_split('/\s+/', $stripped, 2);
        $classNum = trim($parts[0] ?? '');
        $rawSec   = trim($parts[1] ?? '', " \t'\"");

        if ($rawSec !== '') {
            $rawSec  = preg_replace('/^Section\s+/i', '', $rawSec);
            $section = 'Section ' . strtoupper($rawSec);
        } else {
            $section = '';
        }

        return [
            $classNum !== '' ? "Class $classNum" : '',
            $section,
        ];
    }

    /**
     * Resolve class and section from a student profile array.
     * Handles both:
     *   Format A: Class="8th",   Section="B"  (separate fields)
     *   Format B: Class="8th B", Section=""   (merged in Class field)
     * Returns: ["Class 8th", "Section B"]
     */
    private function _resolveClassSection(array $student)
    {
        $classRaw = trim($student['Class'] ?? '');

        list($class, $section) = $this->parseClassSection($classRaw);

        // If section not found in Class field, try dedicated Section field
        if ($section === '') {
            $rawSec = trim($student['Section'] ?? '');
            if ($rawSec !== '') {
                $rawSec  = preg_replace('/^Section\s+/i', '', $rawSec);
                $section = 'Section ' . strtoupper($rawSec);
            }
        }

        // Rebuild class prefix if still empty
        if ($class === '' && $classRaw !== '') {
            $stripped  = preg_replace('/^Class\s+/i', '', $classRaw);
            $firstPart = trim(explode(' ', $stripped)[0]);
            $class     = 'Class ' . $firstPart;
        }

        return [$class, $section];
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEES STRUCTURE
    // ══════════════════════════════════════════════════════════════════

    public function fees_structure()
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        if ($this->input->method() == 'post') {
            $feeTitle = trim(ucwords(strtolower($this->input->post('fee_title'))));
            $feeType  = $this->input->post('fee_type');
            if (!empty($feeTitle) && !empty($feeType)) {
                $result = $this->CM->addKey_pair_data(
                    "Schools/$sn/$sy/Accounts/Fees/Fees Structure/$feeType",
                    [$feeTitle => '']
                );
                echo $result ? '1' : '0';
            } else {
                echo '0';
            }
            return;
        }

        $feesStructure         = $this->CM->get_data("Schools/$sn/$sy/Accounts/Fees/Fees Structure");
        $data['feesStructure'] = $feesStructure;
        $this->load->view('include/header');
        $this->load->view('fees_structure', $data);
        $this->load->view('include/footer');
    }

    public function delete_fees_structure($feeTitle, $feeType)
    {
        $sn = $this->school_name;
        $sy = $this->session_year;
        $this->CM->delete_data(
            "Schools/$sn/$sy/Accounts/Fees/Fees Structure/" . urldecode($feeType),
            urldecode($feeTitle)
        );
        redirect(base_url() . 'fees/fees_structure');
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEES CHART
    // ══════════════════════════════════════════════════════════════════

    public function fees_chart()
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        // AJAX GET — return fees JSON for selected class + section
        if ($this->input->get('class') && $this->input->get('section')) {

            $selClass   = urldecode(trim($this->input->get('class')));
            $selSection = urldecode(trim($this->input->get('section')));

            if (stripos($selSection, 'Section ') !== 0) {
                $selSection = 'Section ' . $selSection;
            }

            $feesJson = $this->_getFees($selClass, $selSection);
            $feesData = json_decode($feesJson, true);

            if (empty($feesData['fees'])) {
                $default  = $this->_createDefaultFees($selClass, $selSection);
                $feesData = ['fees' => $default];
            }

            header('Content-Type: application/json');
            echo json_encode(
                isset($feesData['fees'])
                    ? ['fees' => $feesData['fees']]
                    : ['error' => 'No fees data found']
            );
            return;
        }

        // Page load — build class + section lists from year root
        $yearRoot = $this->CM->get_data("Schools/$sn/$sy");
        $yearRoot = is_array($yearRoot) ? $yearRoot : [];

        $classList  = [];
        $sectionMap = [];

        foreach ($yearRoot as $key => $value) {
            if (stripos($key, 'Class ') !== 0) continue;
            $classList[]      = $key;
            $sectionMap[$key] = [];

            if (!is_array($value)) continue;

            foreach (array_keys($value) as $secKey) {
                $secKey = (string)$secKey;
                if (stripos($secKey, 'Section ') === 0) {
                    $sectionMap[$key][] = $secKey;
                } elseif (strlen($secKey) <= 3 && ctype_alpha($secKey)) {
                    $sectionMap[$key][] = 'Section ' . strtoupper($secKey);
                }
            }
            sort($sectionMap[$key]);
        }

        usort($classList, function ($a, $b) {
            preg_match('/(\d+)/', $a, $ma);
            preg_match('/(\d+)/', $b, $mb);
            return ((int)($ma[1] ?? 0)) <=> ((int)($mb[1] ?? 0));
        });

        $data['classes']  = $classList;
        $data['sections'] = $sectionMap;

        $this->load->view('include/header');
        $this->load->view('fees_chart', $data);
        $this->load->view('include/footer');
    }

    private function _createDefaultFees($class, $section)
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        $structure = $this->CM->get_data("Schools/$sn/$sy/Accounts/Fees/Fees Structure");
        $feesPath  = $this->feesPath($class, $section);
        $existing  = $this->CM->get_data($feesPath);

        if (!empty($existing)) return $existing;
        if (empty($structure))  return [];

        $months  = [
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
        $default = [];

        foreach ($months as $month) {
            $default[$month] = [];
            if (isset($structure['Monthly']) && is_array($structure['Monthly'])) {
                foreach ($structure['Monthly'] as $t => $v) $default[$month][$t] = 0;
            }
        }

        $default['Yearly Fees'] = [];
        if (isset($structure['Yearly']) && is_array($structure['Yearly'])) {
            foreach ($structure['Yearly'] as $t => $v) $default['Yearly Fees'][$t] = 0;
        }

        $this->CM->addKey_pair_data($feesPath, $default);
        return $default;
    }

    private function _getFees($class, $section)
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        $feesPath = $this->feesPath($class, $section);
        $feesData = $this->CM->get_data($feesPath);

        if (empty($feesData) || !is_array($feesData)) {
            return json_encode(['fees' => [], 'monthlyTotals' => []]);
        }

        // Ensure Yearly Fees node exists
        if (!isset($feesData['Yearly Fees']) || !is_array($feesData['Yearly Fees'])) {
            $ys = $this->CM->get_data("Schools/$sn/$sy/Accounts/Fees/Fees Structure/Yearly");
            if ($ys && is_array($ys)) {
                $yearly = array_fill_keys(array_keys($ys), 0);
                $feesData['Yearly Fees'] = $yearly;
                $this->CM->addKey_pair_data($feesPath, ['Yearly Fees' => $yearly]);
            }
        }

        $formatted = [];
        $totals    = [];
        foreach ($feesData as $month => $fees) {
            if (is_array($fees)) {
                $formatted[$month] = $fees;
                $totals[$month]    = array_sum($fees);
            }
        }

        return json_encode([
            'fees'          => $formatted,
            'monthlyTotals' => $totals,
            'overallTotal'  => array_sum($totals),
        ]);
    }

    public function save_updated_fees()
    {
        header('Content-Type: application/json');

        // ── MY_Controller already validated CSRF in __construct() ──────
        // Token arrived via FormData field (CI built-in filter) AND
        // X-CSRF-Token header (MY_Controller check). Both layers passed
        // or we would never reach this line. No bypass. No exclusions.
        // ──────────────────────────────────────────────────────────────

        // Only accept AJAX POST requests
        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Direct access not allowed.']);
            return;
        }

        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
            return;
        }

        // Read fields from $_POST (FormData)
        $class   = trim($this->input->post('class')   ?? '');
        $section = trim($this->input->post('section') ?? '');
        $feesRaw = trim($this->input->post('fees')    ?? '');

        if (!$class || !$section || !$feesRaw) {
            echo json_encode(['status' => 'error', 'message' => 'Missing class, section, or fees.']);
            return;
        }

        // Validate class format — must match "Class Nth" stored in Firebase
        if (!preg_match('/^Class\s+\S+$/i', $class)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid class format.']);
            return;
        }

        // Validate section format — must match "Section X"
        if (!preg_match('/^Section\s+[A-Z0-9]+$/i', $section)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid section format.']);
            return;
        }

        // Decode fees JSON string
        $fees = json_decode($feesRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($fees)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid fees data.']);
            return;
        }

        // Validate all fee amounts are non-negative numbers
        $allowedMonths = [
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
            'March',
            'Yearly Fees'
        ];

        foreach ($fees as $month => $entries) {
            if (!in_array($month, $allowedMonths)) {
                echo json_encode(['status' => 'error', 'message' => "Invalid month key: $month"]);
                return;
            }
            if (!is_array($entries)) {
                echo json_encode(['status' => 'error', 'message' => "Invalid fee entries for $month"]);
                return;
            }
            foreach ($entries as $title => $amount) {
                if (!is_numeric($amount) || (float)$amount < 0) {
                    echo json_encode(['status' => 'error', 'message' => "Invalid amount for $title in $month"]);
                    return;
                }
            }
        }

        // Verify this class+section actually exists in Firebase
        // (prevents writing to arbitrary paths)
        $sn = $this->school_name;
        $sy = $this->session_year;
        $classExists = $this->CM->get_data("Schools/$sn/$sy/$class/$section");
        if (empty($classExists)) {
            echo json_encode(['status' => 'error', 'message' => 'Class/section not found. Please reload the page.']);
            return;
        }

        $feesPath = $this->feesPath($class, $section);

        // Save Yearly Fees separately, then monthly fees
        if (isset($fees['Yearly Fees']) && is_array($fees['Yearly Fees'])) {
            $this->CM->addKey_pair_data("$feesPath/Yearly Fees", $fees['Yearly Fees']);
            unset($fees['Yearly Fees']);
        }

        if (!empty($fees)) {
            $this->CM->addKey_pair_data($feesPath, $fees);
        }

        echo json_encode(['status' => 'success', 'message' => 'Fees updated successfully.']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  DISCOUNT
    // ══════════════════════════════════════════════════════════════════

    public function submit_discount()
    {
        header('Content-Type: application/json');

        $userId   = trim($this->input->post('userId'));
        $class    = trim($this->input->post('class'));
        $section  = trim($this->input->post('section'));
        $discount = $this->input->post('discount');

        if (empty($userId) || empty($class) || empty($section) || $discount === false || $discount === '') {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            return;
        }

        if (!is_numeric($discount) || (int)$discount < 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid discount value.']);
            return;
        }

        $base = $this->studentPath($class, $section, $userId);

        try {
            $this->firebase->set("$base/Discount/OnDemandDiscount", (int)$discount);
            $cur = (int)($this->firebase->get("$base/Discount/totalDiscount") ?? 0);
            $new = $cur + (int)$discount;
            $this->firebase->set("$base/Discount/totalDiscount", $new);
            echo json_encode(['success' => true, 'newTotalDiscount' => $new]);
        } catch (Exception $e) {
            log_message('error', 'submit_discount: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Internal server error.']);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  SEARCH
    // ══════════════════════════════════════════════════════════════════

    public function student_fees()
    {
        $this->load->view('include/header');
        $this->load->view('student_fees');
        $this->load->view('include/footer');
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEE RECEIPTS
    // ══════════════════════════════════════════════════════════════════

    public function fetch_fee_receipts()
    {
        $this->output->set_content_type('application/json');

        $school_id = $this->school_id;

        // FIX: Read userId from $_POST (FormData) instead of php://input JSON.
        // This means CSRF token arrives in the body field normally — no 403.
        $userId = trim($this->input->post('userId') ?? '');

        if (!$userId) {
            $this->output->set_output(json_encode([]));
            return;
        }

        $userInfo = $this->firebase->get("Users/Parents/$school_id/$userId");
        if (empty($userInfo)) {
            $this->output->set_output(json_encode([]));
            return;
        }
        $userInfo = (array)$userInfo;

        $name   = $userInfo['Name']        ?? 'N/A';
        $father = $userInfo['Father Name'] ?? 'N/A';

        list($class, $section) = $this->_resolveClassSection($userInfo);

        if ($class === '' || $section === '') {
            $this->output->set_output(json_encode([]));
            return;
        }

        $studentBase = $this->studentPath($class, $section, $userId);
        $recs        = $this->firebase->get("$studentBase/Fees Record");

        $response = [];
        if (is_array($recs)) {
            foreach ($recs as $key => $rec) {
                $rec        = (array)$rec;
                $response[] = [
                    'receiptNo' => str_replace('F', '', $key),
                    'date'      => $rec['Date']     ?? '',
                    'student'   => "$name / $father",
                    'class'     => "$class $section",
                    'amount'    => $rec['Amount']   ?? '0.00',
                    'fine'      => $rec['Fine']     ?? '0.00',
                    'discount'  => $rec['Discount'] ?? '0.00',
                    'account'   => $rec['Mode']     ?? 'N/A',
                    'reference' => $rec['Refer']    ?? '',
                    'Id'        => $userId,
                ];
            }

            usort($response, function ($a, $b) {
                return (int)$b['receiptNo'] - (int)$a['receiptNo'];
            });
        }

        $this->output->set_output(json_encode($response));
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEES COUNTER (page load only — data fetched via AJAX)
    // ══════════════════════════════════════════════════════════════════

    public function fees_counter()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $receiptPath       = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
        $data['receiptNo'] = $this->CM->get_data($receiptPath) ?: '1';

        $accountsData     = $this->CM->get_data("Schools/$school_name/$session_year/Accounts/Account_book");
        $filteredAccounts = [];
        if (!empty($accountsData) && is_array($accountsData)) {
            foreach ($accountsData as $aName => $aDetails) {
                if (isset($aDetails['Under']) && in_array($aDetails['Under'], ['BANK ACCOUNT', 'CASH'])) {
                    $filteredAccounts[$aName] = $aDetails['Under'];
                }
            }
        }
        $data['accounts'] = $filteredAccounts;

        $ts = $this->CM->get_data("Schools/$school_name/$session_year/ServerTimestamp");
        $data['serverDate'] = (!empty($ts) && is_numeric($ts))
            ? date('d-m-Y', $ts / 1000)
            : date('d-m-Y');

        $this->load->view('include/header');
        $this->load->view('fees_counter', $data);
        $this->load->view('include/footer');
    }

    // ══════════════════════════════════════════════════════════════════
    //  STUDENT LOOKUP
    // ══════════════════════════════════════════════════════════════════

    public function lookup_student()
    {
        header('Content-Type: application/json');

        $userId = trim($this->input->post('user_id') ?? '');
        if ($userId === '') {
            echo json_encode(['error' => 'No user ID provided']);
            return;
        }

        $student = $this->CM->get_data("Users/Parents/{$this->school_id}/$userId");
        if (empty($student)) {
            echo json_encode(['error' => "Student '$userId' not found"]);
            return;
        }

        $student = (array)$student;

        // ── FIX: Use _resolveClassSection to get normalized values ──
        // This handles all formats: "8th", "8th B", "Class 8th", etc.
        list($class, $section) = $this->_resolveClassSection($student);

        echo json_encode([
            'user_id'     => $student['User Id'] ?? $userId,
            'name'        => $student['Name']        ?? '',
            'father_name' => $student['Father Name'] ?? '',
            'class'       => $class,    // e.g. "Class 8th"
            'section'     => $section,  // e.g. "Section B"
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  FETCH MONTHS
    // ══════════════════════════════════════════════════════════════════

    public function fetch_months()
    {
        header('Content-Type: application/json');

        $school_id = $this->school_id;
        $userId    = trim($this->input->post('user_id') ?? '');

        if ($userId === '') {
            echo json_encode(['error' => 'No user ID provided']);
            return;
        }

        $student = $this->CM->get_data("Users/Parents/$school_id/$userId");
        if (empty($student)) {
            echo json_encode(['error' => "Student '$userId' not found"]);
            return;
        }
        $student = (array)$student;

        list($class, $section) = $this->_resolveClassSection($student);

        if ($class === '' || $section === '') {
            echo json_encode([
                'error'         => "Cannot resolve class/section for '$userId'",
                'class_field'   => $student['Class']   ?? '',
                'section_field' => $student['Section'] ?? '',
            ]);
            return;
        }

        $studentBase   = $this->studentPath($class, $section, $userId);
        $monthFeesData = $this->CM->get_data("$studentBase/Month Fee");
        $monthFeesData = is_array($monthFeesData) ? $monthFeesData : [];

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
            'March',
            'Yearly Fees'
        ];

        $result = [];
        foreach ($months as $m) {
            $result[$m] = isset($monthFeesData[$m]) ? (int)$monthFeesData[$m] : 0;
        }

        echo json_encode($result);
    }

    // ══════════════════════════════════════════════════════════════════
    //  FETCH FEE DETAILS
    // ══════════════════════════════════════════════════════════════════

    public function fetch_fee_details()
    {
        ob_start();
        header('Content-Type: application/json');

        $school_id = $this->school_id;
        $userId    = trim($this->input->post('user_id') ?? '');
        $selectedMonths = $this->input->post('months') ?? [];

        if ($userId === '' || empty($selectedMonths)) {
            ob_end_clean();
            echo json_encode(['error' => 'Missing user_id or months']);
            return;
        }

        $student = $this->CM->get_data("Users/Parents/$school_id/$userId");
        if (empty($student)) {
            ob_end_clean();
            echo json_encode(['error' => "Student '$userId' not found"]);
            return;
        }
        $student = (array)$student;

        list($class, $section) = $this->_resolveClassSection($student);

        if ($class === '' || $section === '') {
            ob_end_clean();
            echo json_encode([
                'error'         => "Cannot resolve class/section for '$userId'",
                'class_field'   => $student['Class']   ?? '',
                'section_field' => $student['Section'] ?? '',
            ]);
            return;
        }

        $studentBase = $this->studentPath($class, $section, $userId);
        $fp          = $this->feesPath($class, $section);

        $exemptedFees = $this->CM->get_data("$studentBase/Exempted Fees");
        $exemptedFees = is_array($exemptedFees) ? $exemptedFees : [];

        $feesRecord = $this->getFeesForSelectedMonths(
            $this->school_name,
            $class,
            $section,
            $selectedMonths
        );

        if (!is_array($feesRecord)) $feesRecord = [];

        $feeRecord     = [];
        $feesRecordArr = [];
        $monthTotals   = array_fill_keys($selectedMonths, 0);
        $grandTotal    = 0;

        $allFeeTitles = [];
        foreach ($selectedMonths as $month) {
            if (!is_array($feesRecord[$month] ?? null)) continue;
            foreach (array_keys($feesRecord[$month]) as $t) {
                if (!in_array($t, $allFeeTitles)) $allFeeTitles[] = $t;
            }
        }

        foreach ($allFeeTitles as $feename) {
            $cleanName = str_replace(' (Yearly)', '', $feename);
            if (array_key_exists($cleanName, $exemptedFees)) continue;

            $feeRecord[$feename] = ['title' => $feename, 'total' => 0];

            foreach ($selectedMonths as $month) {
                $val = (float)($feesRecord[$month][$feename] ?? 0);
                $feeRecord[$feename][$month]      = $val;
                $monthTotals[$month]             += $val;
                $feeRecord[$feename]['total']     += $val;
            }

            $grandTotal     += $feeRecord[$feename]['total'];
            $feesRecordArr[] = [
                'title' => $feename,
                'total' => $feeRecord[$feename]['total'],
            ];
        }

        $discountData   = $this->CM->get_data("$studentBase/Discount");
        $discountAmount = (is_array($discountData) && isset($discountData['OnDemandDiscount']))
            ? (float)$discountData['OnDemandDiscount']
            : 0;

        $overRaw       = $this->CM->get_data("$studentBase/Oversubmittedfees");
        $oversubmitted = is_numeric($overRaw) ? (float)$overRaw : 0;

        $last  = end($selectedMonths);
        $label = count($selectedMonths) > 1
            ? implode(', ', array_slice($selectedMonths, 0, -1)) . ' and ' . $last
            : $last;

        ob_end_clean();
        echo json_encode([
            'grandTotal'     => $grandTotal,
            'discountAmount' => $discountAmount,
            'overpaidFees'   => $oversubmitted,
            'message'        => "Fee Details for: $label",
            'feesRecord'     => $feesRecordArr,
            'feeRecord'      => $feeRecord,
            'selectedMonths' => $selectedMonths,
            'monthTotals'    => $monthTotals,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  GET SERVER DATE
    // ══════════════════════════════════════════════════════════════════

    public function get_server_date()
    {
        header('Content-Type: application/json');
        $sn = $this->school_name;
        $sy = $this->session_year;
        $ts = $this->CM->get_data("Schools/$sn/$sy/ServerTimestamp");
        $date = (!empty($ts) && is_numeric($ts))
            ? date('d-m-Y', $ts / 1000)
            : date('d-m-Y');
        echo json_encode(['date' => $date]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  SEARCH STUDENT
    // ══════════════════════════════════════════════════════════════════

    public function search_student()
    {
        header('Content-Type: application/json');
        $results = $this->input->post('search_name')
            ? $this->_searchByName($this->input->post('search_name'))
            : [];
        echo json_encode($results);
        exit;
    }

    private function _searchByName($entry)
    {
        $students = $this->CM->get_data('Users/Parents/' . $this->school_id);
        $results  = [];
        if (!is_array($students)) return $results;

        foreach ($students as $uid => $s) {
            $s = is_array($s) ? $s : [];
            $name   = $s['Name']        ?? '';
            $sid    = $s['User Id']     ?? '';
            $father = $s['Father Name'] ?? '';

            // Normalize class & section using the same resolver used everywhere
            list($class, $section) = $this->_resolveClassSection($s);

            if (
                stripos($name,    $entry) !== false ||
                stripos($sid,     $entry) !== false ||
                stripos($father,  $entry) !== false ||
                stripos($class,   $entry) !== false ||
                stripos($section, $entry) !== false    // ← NEW: search by section too
            ) {
                $results[] = [
                    'user_id'     => $sid,
                    'name'        => $name,
                    'father_name' => $father,
                    'class'       => $class,       // e.g. "Class 8th"
                    'section'     => $section,     // e.g. "Section B"
                ];
            }
        }
        return $results;
    }

    // ══════════════════════════════════════════════════════════════════
    //  SUBMIT FEES
    // ══════════════════════════════════════════════════════════════════

    public function submit_fees()
    {
        $this->output->set_content_type('application/json');
        $this->load->library('firebase');

        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        $receiptNo      = $this->input->post('receiptNo');
        $paymentMode    = $this->input->post('paymentMode') ?: 'N/A';
        $userId         = trim($this->input->post('userId') ?? '');
        $schoolFees     = floatval(str_replace(',', '', $this->input->post('schoolFees')     ?? '0'));
        $discountFees   = floatval(str_replace(',', '', $this->input->post('discountAmount') ?? '0'));
        $fineAmount     = floatval(str_replace(',', '', $this->input->post('fineAmount')     ?? '0'));
        $submitAmount   = floatval(str_replace(',', '', $this->input->post('submitAmount')   ?? '0'));
        $reference      = $this->input->post('reference') ?: 'Fees Submitted';

        $selectedMonths = $this->input->post('selectedMonths') ?? [];
        if (!is_array($selectedMonths)) $selectedMonths = explode(',', $selectedMonths);

        $MonthTotal       = $this->input->post('monthTotals') ?? [];
        $monthTotalsArray = [];
        foreach ((array)$MonthTotal as $md) {
            if (isset($md['month'], $md['total'])) {
                $monthTotalsArray[trim($md['month'])] = floatval(str_replace(',', '', $md['total']));
            }
        }

        if ($userId === '') {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Missing student ID']));
            return;
        }
        if (empty($selectedMonths)) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'No months selected']));
            return;
        }
        if ($schoolFees <= 0) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Fee amount must be greater than 0']));
            return;
        }

        // Resolve class + section from Firebase (authoritative source)
        $student = $this->CM->get_data("Users/Parents/$school_id/$userId");
        if (empty($student)) {
            $this->output->set_output(json_encode([
                'status'  => 'error',
                'message' => "Student '$userId' not found in Firebase",
            ]));
            return;
        }
        $student = (array)$student;

        list($class, $section) = $this->_resolveClassSection($student);

        if ($class === '' || $section === '') {
            $this->output->set_output(json_encode([
                'status'  => 'error',
                'message' => "Cannot resolve class/section for '$userId' "
                    . "(Class='" . ($student['Class']   ?? '') . "', "
                    . "Section='" . ($student['Section'] ?? '') . "')",
            ]));
            return;
        }

        $date     = date('d-m-Y');
        $date_obj = DateTime::createFromFormat('d-m-Y', $date);
        $month    = $date_obj ? $date_obj->format('F') : date('F');
        $day      = $date_obj ? $date_obj->format('d') : date('d');

        $receiptKey  = 'F' . $receiptNo;
        $studentBase = $this->studentPath($class, $section, $userId);

        // 1. Reset OnDemandDiscount, accumulate totalDiscount
        $discPath1 = "$studentBase/Discount/OnDemandDiscount";
        $discPath2 = "$studentBase/Discount/totalDiscount";
        try {
            $this->firebase->set($discPath1, 0);
            $cur = $this->firebase->get($discPath2);
            $cur = is_numeric($cur) ? (int)$cur : 0;
            $this->firebase->set($discPath2, $cur + (int)$discountFees);
        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'status'  => 'error',
                'message' => 'Discount update failed: ' . $e->getMessage(),
            ]));
            return;
        }

        // 2. Fees Record
        try {
            $this->firebase->update("$studentBase/Fees Record", [
                $receiptKey => [
                    'Amount'   => number_format($schoolFees,   2, '.', ','),
                    'Discount' => number_format($discountFees, 2, '.', ','),
                    'Date'     => $date,
                    'Fine'     => number_format($fineAmount,   2, '.', ','),
                    'Mode'     => $paymentMode,
                    'Refer'    => $reference,
                ]
            ]);
        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'status'  => 'error',
                'message' => 'Fees Record write failed: ' . $e->getMessage(),
            ]));
            return;
        }

        // 3. Vouchers
        try {
            $this->firebase->update(
                "Schools/$school_name/$session_year/Accounts/Vouchers/$date",
                [
                    $receiptKey => [
                        'Acc'           => 'Fees',
                        'Fees Received' => number_format($schoolFees, 2),
                        'Id'            => $userId,
                        'Mode'          => $paymentMode,
                    ]
                ]
            );
        } catch (Exception $e) {
            log_message('error', 'submit_fees voucher write failed: ' . $e->getMessage());
        }

        // 4. Account book ledger
        $ab      = "Schools/$school_name/$session_year/Accounts/Account_book";
        $updateR = function ($path, $amount) {
            if ($amount <= 0) return;
            $cur = floatval($this->firebase->get("$path/R") ?? 0);
            $this->firebase->set("$path/R", $cur + $amount);
        };
        try {
            $updateR("$ab/Discount/$month/$day", $discountFees);
            $updateR("$ab/Fees/$month/$day",     $schoolFees);
            $updateR("$ab/Fine/$month/$day",     $fineAmount);
        } catch (Exception $e) {
            log_message('error', 'submit_fees account book failed: ' . $e->getMessage());
        }

        // 5. Receipt counter
        try {
            $rcPath = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
            $this->firebase->set($rcPath, ((int)($this->firebase->get($rcPath) ?? 0)) + 1);
        } catch (Exception $e) {
            log_message('error', 'submit_fees receipt counter failed: ' . $e->getMessage());
        }

        // 6. Mark months as paid
        $monthOrder = [
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
            'March',
            'Yearly Fees'
        ];
        usort(
            $selectedMonths,
            fn($a, $b) =>
            array_search($a, $monthOrder) - array_search($b, $monthOrder)
        );

        $totalSubmitted = $schoolFees + $submitAmount;
        foreach ($selectedMonths as $m) {
            $mFee = $monthTotalsArray[$m] ?? 0;
            if ($mFee > 0 && $totalSubmitted >= $mFee) {
                $this->firebase->set("$studentBase/Month Fee/$m", 1);
                $totalSubmitted -= $mFee;
            }
        }

        // 7. Carry-forward overpaid amount
        if ($totalSubmitted > 0.005) {
            $this->firebase->set("$studentBase/Oversubmittedfees", round($totalSubmitted, 2));
        }

        $this->output->set_output(json_encode([
            'status'  => 'success',
            'message' => 'Fees submitted successfully!',
        ]));
    }

    // ══════════════════════════════════════════════════════════════════
    //  GET FEES FOR SELECTED MONTHS
    // ══════════════════════════════════════════════════════════════════

    public function getFeesForSelectedMonths($school_name, $class, $section, $selectedMonths)
    {
        $fp       = $this->feesPath($class, $section);
        $feesData = [];

        foreach ($selectedMonths as $month) {
            $monthFees = $this->CM->get_data("$fp/$month");

            if (is_array($monthFees)) {
                $feesData[$month] = $monthFees;
            } elseif (is_string($monthFees) && $monthFees !== '') {
                $decoded          = json_decode($monthFees, true);
                $feesData[$month] = is_array($decoded) ? $decoded : [];
            } else {
                $feesData[$month] = [];
            }
        }

        return $feesData;
    }

    public function calculateTotalFees($feesRecord, $selectedMonths, $exemptedFees)
    {
        $totals = [];
        foreach ($selectedMonths as $month) {
            if (!isset($feesRecord[$month]) || !is_array($feesRecord[$month])) continue;
            foreach ($feesRecord[$month] as $feeTitle => $feeAmount) {
                $clean   = str_replace(' (Yearly)', '', $feeTitle);
                if (array_key_exists($clean, $exemptedFees)) continue;
                $display = ($month === 'Yearly Fees') ? "$clean (Yearly)" : $clean;
                $totals[$display] = ($totals[$display] ?? 0) + floatval($feeAmount);
            }
        }
        return $totals;
    }

    // ══════════════════════════════════════════════════════════════════
    //  CLASS FEES
    // ══════════════════════════════════════════════════════════════════

    public function class_fees()
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        $yearRoot = $this->CM->get_data("Schools/$sn/$sy");
        $yearRoot = is_array($yearRoot) ? $yearRoot : [];

        $classList  = [];
        $sectionMap = [];

        foreach ($yearRoot as $key => $value) {
            if (stripos($key, 'Class ') !== 0 || !is_array($value)) continue;
            $classList[] = $key;
            $sectionMap[$key] = [];
            foreach (array_keys($value) as $sk) {
                $sk = (string)$sk;
                if (stripos($sk, 'Section ') === 0) {
                    $sectionMap[$key][] = $sk;
                } elseif (strlen($sk) <= 3 && ctype_alpha($sk)) {
                    $sectionMap[$key][] = 'Section ' . strtoupper($sk);
                }
            }
            sort($sectionMap[$key]);
        }

        usort($classList, function ($a, $b) {
            preg_match('/(\d+)/', $a, $ma);
            preg_match('/(\d+)/', $b, $mb);
            return ((int)($ma[1] ?? 0)) <=> ((int)($mb[1] ?? 0));
        });

        $data['classes']  = $classList;
        $data['sections'] = $sectionMap;
        // $data['class']    = $this->input->get('class')   ?? '';
        // $data['section']  = $this->input->get('section') ?? '';
        $rawClass = urldecode($this->input->get('class') ?? '');
        if ($rawClass !== '') {
            $data['class'] = (stripos($rawClass, 'Class ') === 0)
                ? $rawClass
                : 'Class ' . $rawClass;
        } else {
            $data['class'] = '';
        }

        $rawSection = urldecode($this->input->get('section') ?? '');
        if ($rawSection !== '') {
            $rawSection = preg_replace('/^Section\s+/i', '', $rawSection);
            $data['section'] = 'Section ' . strtoupper($rawSection);
        } else {
            $data['section'] = '';
        }

        $this->load->view('include/header');
        $this->load->view('class_fees', $data);
        $this->load->view('include/footer');
    }

    // ══════════════════════════════════════════════════════════════════
    //  DUE FEES TABLE
    // ══════════════════════════════════════════════════════════════════

    public function due_fees_table()
    {
        $this->output->set_content_type('application/json');

        $school_id = $this->school_id;
        $sn        = $this->school_name;
        $sy        = $this->session_year;

        $class   = trim($this->input->post('class')   ?? '');
        $section = trim($this->input->post('section') ?? '');

        if (!$class || !$section) {
            $this->output->set_output(json_encode([[
                'userId' => null,
                'name' => 'Missing class or section parameter',
                'totalFee' => null,
                'receivedFee' => null,
                'dueFee' => null,
            ]]));
            return;
        }

        $studentsPath = $this->studentPath($class, $section) . '/List';
        $feesPath     = $this->feesPath($class, $section);

        $student_ids = $this->firebase->get($studentsPath);
        if (empty($student_ids)) {
            $this->output->set_output(json_encode([[
                'userId' => null,
                'name' => "No students in $class $section",
                'totalFee' => null,
                'receivedFee' => null,
                'dueFee' => null,
            ]]));
            return;
        }

        $class_fees = $this->firebase->get($feesPath);
        if (empty($class_fees)) {
            $this->output->set_output(json_encode([[
                'userId' => null,
                'name' => "No fee structure defined for $class $section",
                'totalFee' => null,
                'receivedFee' => null,
                'dueFee' => null,
            ]]));
            return;
        }

        $annual_fee = 0;
        foreach ($class_fees as $month => $fees) {
            if (is_array($fees)) {
                foreach ($fees as $title => $amt) {
                    $annual_fee += (float)str_replace(',', '', $amt ?? 0);
                }
            }
        }

        $allProfiles = $this->CM->get_data("Users/Parents/$school_id");
        $allProfiles = is_array($allProfiles) ? $allProfiles : [];

        $response = [];
        foreach ($student_ids as $uid => $v) {
            $uid   = (string)$uid;
            $prof  = isset($allProfiles[$uid]) ? (array)$allProfiles[$uid] : [];
            $name  = $prof['Name']        ?? 'N/A';
            $fname = $prof['Father Name'] ?? 'N/A';

            $recs = $this->firebase->get($this->studentPath($class, $section, $uid) . '/Fees Record');
            $paid = 0;
            if (is_array($recs)) {
                foreach ($recs as $r) {
                    if (is_array($r)) {
                        $paid += (float)str_replace(',', '', $r['Amount'] ?? 0);
                    }
                }
            }

            $discNode = $this->firebase->get($this->studentPath($class, $section, $uid) . '/Discount');
            $discount = 0;
            if (is_array($discNode)) {
                $discount += (float)($discNode['totalDiscount'] ?? 0);
            }

            $response[] = [
                'userId'      => $uid,
                'name'        => "$name / $fname",
                'totalFee'    => $annual_fee,
                'receivedFee' => $paid,
                'discount'    => $discount,
                'dueFee'      => max(0, $annual_fee - $paid - $discount),
            ];
        }

        usort($response, fn($a, $b) => $b['dueFee'] <=> $a['dueFee']);

        $this->output->set_output(json_encode($response));
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEES RECORDS
    // ══════════════════════════════════════════════════════════════════

    public function fees_records()
    {
        $school_id = $this->school_id;
        $sn        = $this->school_name;
        $sy        = $this->session_year;

        $yearRoot = $this->CM->get_data("Schools/$sn/$sy");
        $yearRoot = is_array($yearRoot) ? $yearRoot : [];

        $classList  = [];
        $feesMatrix = [];

        foreach ($yearRoot as $key => $value) {
            if (stripos($key, 'Class ') !== 0 || !is_array($value)) continue;
            foreach (array_keys($value) as $sk) {
                $sk = (string)$sk;
                if (stripos($sk, 'Section ') === 0) {
                    $normSec = $sk;
                } elseif (strlen($sk) <= 3 && ctype_alpha($sk)) {
                    $normSec = 'Section ' . strtoupper($sk);
                } else {
                    continue;
                }
                $matKey              = "$key|$normSec";
                $classList[$matKey]  = "$key $normSec";
                $feesMatrix[$matKey] = array_fill(0, 12, 0);
            }
        }

        uksort($classList, function ($a, $b) {
            preg_match('/(\d+)/', $a, $ma);
            preg_match('/(\d+)/', $b, $mb);
            return ((int)($ma[1] ?? 0)) <=> ((int)($mb[1] ?? 0));
        });

        // Bulk-load ALL student records once (avoids N+1 Firebase calls)
        $allStudents       = $this->CM->get_data("Users/Parents/$school_id");
        $studentClassCache = [];
        if (is_array($allStudents)) {
            foreach ($allStudents as $uid => $stu) {
                $stu = is_array($stu) ? $stu : [];
                list($cls, $sec) = $this->_resolveClassSection($stu);
                if ($cls !== '' && $sec !== '') {
                    $studentClassCache[(string)$uid] = "$cls|$sec";
                }
            }
        }

        $vouchers = $this->CM->get_data("Schools/$sn/$sy/Accounts/Vouchers");
        $vouchers = is_array($vouchers) ? $vouchers : [];

        foreach ($vouchers as $date => $vList) {
            if ($date === 'VoucherCount' || !is_array($vList)) continue;
            $dObj = DateTime::createFromFormat('d-m-Y', $date);
            if (!$dObj) continue;

            $calMonth = (int)$dObj->format('n');
            $mi       = ($calMonth >= 4) ? ($calMonth - 4) : ($calMonth + 8);

            foreach ($vList as $vk => $v) {
                if (!is_array($v) || strpos((string)$vk, 'F') !== 0) continue;
                $received = (float)str_replace(',', '', $v['Fees Received'] ?? 0);
                if ($received <= 0) continue;
                $sid = trim((string)($v['Id'] ?? ''));
                if ($sid === '') continue;

                $matKey = $studentClassCache[$sid] ?? null;
                if ($matKey && isset($feesMatrix[$matKey])) {
                    $feesMatrix[$matKey][$mi] += $received;
                }
            }
        }

        $matrix = [];
        foreach ($classList as $k => $label) {
            $amounts  = $feesMatrix[$k] ?? array_fill(0, 12, 0);
            $matrix[] = [
                'class'   => $label,
                'key'     => $k,
                'amounts' => $amounts,
                'total'   => array_sum($amounts),
            ];
        }

        $data['fees_record_matrix'] = $matrix;
        $this->load->view('include/header');
        $this->load->view('fees_records', $data);
        $this->load->view('include/footer');
    }
}
