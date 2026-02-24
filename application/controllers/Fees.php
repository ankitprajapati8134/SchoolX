<?php

class Fees extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS (reference — ensure these exist in your controller)
    // ══════════════════════════════════════════════════════════════════

    private function feesPath($class, $section)
    {
        // Output: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th/Section A
        $sn = $this->school_name;
        $sy = $this->session_year;
        return "Schools/$sn/$sy/Accounts/Fees/Classes Fees/$class/$section";
    }

    private function studentPath($class, $section, $userId = '')
    {
        // Output: Schools/{sn}/{sy}/Class 8th/Section A/Students[/{uid}]
        $sn = $this->school_name;
        $sy = $this->session_year;

        $section = preg_replace('/^Section\s+/i', '', trim($section));
        $section = 'Section ' . strtoupper($section);

        $base = "Schools/$sn/$sy/$class/$section/Students";
        return $userId ? "$base/$userId" : $base;
    }

    private function parseClassSection($classString)
    {
        $classString = trim((string)$classString);
        if ($classString === '') return ['', ''];

        $stripped = preg_replace('/^Class\s+/i', '', $classString);

        // "8th Section A" or "8th Section B"
        if (preg_match('/^(.+?)\s+Section\s+([A-Z0-9]+)\s*$/i', $stripped, $m)) {
            return ['Class ' . trim($m[1]), 'Section ' . strtoupper(trim($m[2]))];
        }

        // "8th B" or "8th 'B'"
        $parts    = preg_split('/\s+/', $stripped, 2);
        $classNum = trim($parts[0] ?? '');
        $rawSec   = trim($parts[1] ?? '', " \t'\"");

        if ($rawSec !== '') {
            $rawSec  = preg_replace('/^Section\s+/i', '', $rawSec);
            $section = 'Section ' . strtoupper($rawSec);
        } else {
            $section = ''; // Caller must read student['Section'] field
        }

        return [
            $classNum !== '' ? "Class $classNum" : '',
            $section,
        ];
    }


    // ══════════════════════════════════════════════════════════════
    //  FEES STRUCTURE
    // ══════════════════════════════════════════════════════════════

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

    // ══════════════════════════════════════════════════════════════
    //  FEES CHART
    // ══════════════════════════════════════════════════════════════

    public function fees_chart()
    {
        $sn = $this->school_name;
        $sy = $this->session_year;

        // AJAX GET — return fees JSON for selected class + section
        if ($this->input->get('class') && $this->input->get('section')) {

            $selClass   = urldecode(trim($this->input->get('class')));
            $selSection = urldecode(trim($this->input->get('section')));

            // Ensure section always has "Section " prefix
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
            $classList[]            = $key;
            $sectionMap[$key]       = [];

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

        // Natural sort: Class 1st < Class 4th < Class 10th
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
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
            "January",
            "February",
            "March"
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
            return json_encode(["fees" => [], "monthlyTotals" => []]);
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
            "fees"          => $formatted,
            "monthlyTotals" => $totals,
            "overallTotal"  => array_sum($totals),
        ]);
    }


    public function save_updated_fees()
    {
        $raw = json_decode(file_get_contents("php://input"), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'JSON decode error: ' . json_last_error_msg()]);
            return;
        }
        // if (empty($raw)) {
        //     echo json_encode(['status' => 'error', 'message' => 'No data received.']);
        //     return;
        // }

        $class   = trim($raw['class']   ?? '');
        $section = trim($raw['section'] ?? '');
        $fees    = $raw['fees'] ?? [];

        if (!$class || !$section || empty($fees)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing class, section, or fees.']);
            return;
        }

        $feesPath = $this->feesPath($class, $section);

        if (isset($fees['Yearly Fees']) && is_array($fees['Yearly Fees'])) {
            $this->firebase->set("$feesPath/Yearly Fees", $fees['Yearly Fees']);
            unset($fees['Yearly Fees']);
        }

        if (!empty($fees)) {
            $this->firebase->update($feesPath, $fees);
        }

        echo json_encode(['status' => 'success', 'message' => 'Fees updated successfully.']);
    }

    // ══════════════════════════════════════════════════════════════
    //  DISCOUNT
    // ══════════════════════════════════════════════════════════════

    public function submit_discount()
    {
        if (!isset($this->firebase)) {
            echo json_encode(["success" => false, "message" => "Firebase library not loaded."]);
            return;
        }

        $userId   = trim($this->input->post('userId'));
        $class    = trim($this->input->post('class'));
        $section  = trim($this->input->post('section'));
        $discount = trim($this->input->post('discount'));

        if (empty($userId) || empty($class) || empty($section) || empty($discount)) {
            echo json_encode(["success" => false, "message" => "Missing required fields."]);
            return;
        }

        $base = $this->studentPath($class, $section, $userId);
        try {
            $this->firebase->set("$base/Discount/OnDemandDiscount", (int)$discount);
            $cur = (int)($this->firebase->get("$base/Discount/totalDiscount") ?? 0);
            $new = $cur + (int)$discount;
            $this->firebase->set("$base/Discount/totalDiscount", $new);
            echo json_encode(["success" => true, "newTotalDiscount" => $new]);
        } catch (Exception $e) {
            log_message('error', "submit_discount: " . $e->getMessage());
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }




    // ══════════════════════════════════════════════════════════════
    //  SEARCH
    // ══════════════════════════════════════════════════════════════

    public function student_fees()
    {
        $this->load->view('include/header');
        $this->load->view('student_fees');
        $this->load->view('include/footer');
    }



    // ══════════════════════════════════════════════════════════════
    //  FEE RECEIPTS
    // ══════════════════════════════════════════════════════════════

    public function fetch_fee_receipts()
    {
        $this->output->set_content_type('application/json');

        $school_id = $this->school_id;
        $input     = json_decode(file_get_contents('php://input'), true);
        $userId    = trim($input['userId'] ?? '');

        if (!$userId) {
            $this->output->set_output(json_encode([]));
            return;
        }

        // Get full student record
        $userInfo = $this->firebase->get("Users/Parents/$school_id/$userId");
        if (empty($userInfo)) {
            $this->output->set_output(json_encode([]));
            return;
        }
        $userInfo = (array)$userInfo;

        $name   = $userInfo['Name']        ?? 'N/A';
        $father = $userInfo['Father Name'] ?? 'N/A';

        // BUG FIX: use _resolveClassSection() not bare parseClassSection()
        // _resolveClassSection checks both the Class field AND the separate
        // Section field, so "Class=8th, Section=A" is handled correctly.
        list($class, $section) = $this->_resolveClassSection($userInfo);

        log_message('debug', "fetch_fee_receipts: userId=$userId class=$class section=$section");

        if ($class === '' || $section === '') {
            log_message('error', "fetch_fee_receipts: cannot resolve class/section for $userId");
            $this->output->set_output(json_encode([]));
            return;
        }

        $studentBase = $this->studentPath($class, $section, $userId);
        $recs        = $this->firebase->get("$studentBase/Fees Record");

        log_message('debug', "fetch_fee_receipts: path=$studentBase/Fees Record records=" . json_encode($recs));

        $response = [];
        if (is_array($recs)) {
            foreach ($recs as $key => $rec) {
                $rec = (array)$rec;
                $response[] = [
                    'receiptNo' => str_replace('F', '', $key),
                    'date'      => $rec['Date']     ?? '',
                    'student'   => "$name / $father",
                    'class'     => "$class $section",
                    'amount'    => $rec['Amount']   ?? '0.00',   // e.g. "1,200.00"
                    'fine'      => $rec['Fine']     ?? '0.00',
                    'discount'  => $rec['Discount'] ?? '0.00',   // BUG FIX: was missing
                    'account'   => $rec['Mode']     ?? 'N/A',
                    'reference' => $rec['Refer']    ?? '',
                    'Id'        => $userId,
                ];
            }

            // Sort by receipt number descending (newest first)
            usort($response, function ($a, $b) {
                return (int)$b['receiptNo'] - (int)$a['receiptNo'];
            });
        }

        $this->output->set_output(json_encode($response));
    }

    // ══════════════════════════════════════════════════════════════
    //  FEES COUNTER
    // ══════════════════════════════════════════════════════════════

    // public function fees_counter()
    // {
    //     $school_id    = $this->school_id;
    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     // ── Defaults ──────────────────────────────────────────────
    //     $studentData          = [];
    //     $data['receiptNo']    = null;
    //     $totalAmount          = 0;
    //     $totalSubmittedAmount = 0;
    //     $dueAmount            = 0;
    //     $feeDetails           = [];
    //     $class                = '';   // "Class 8th"
    //     $section              = '';   // "Section A"
    //     $data['message']      = '';

    //     // ── Receipt number ─────────────────────────────────────────
    //     $receiptPath       = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
    //     $data['receiptNo'] = $this->CM->get_data($receiptPath) ?: '1';

    //     // ── Accounts (BANK ACCOUNT + CASH only) ───────────────────
    //     $accountsData     = $this->CM->get_data("Schools/$school_name/$session_year/Accounts/Account_book");
    //     $filteredAccounts = [];
    //     if (!empty($accountsData) && is_array($accountsData)) {
    //         foreach ($accountsData as $accountName => $accountDetails) {
    //             if (isset($accountDetails['Under']) && in_array($accountDetails['Under'], ['BANK ACCOUNT', 'CASH'])) {
    //                 $filteredAccounts[$accountName] = $accountDetails['Under'];
    //             }
    //         }
    //     }

    //     // ── Server timestamp ───────────────────────────────────────
    //     $serverTimestamp = $this->CM->get_data("Schools/$school_name/$session_year/ServerTimestamp");
    //     $data['serverDate'] = !empty($serverTimestamp)
    //         ? date('d-m-Y', $serverTimestamp / 1000)
    //         : 'Timestamp Not Found';

    //     // ── Selected months ────────────────────────────────────────
    //     $selectedMonths = $this->input->post('months');
    //     if (is_array($selectedMonths) && !empty($selectedMonths)) {
    //         $lastMonth       = end($selectedMonths);
    //         $formattedMonths = count($selectedMonths) > 1
    //             ? implode(', ', array_slice($selectedMonths, 0, -1)) . ' and ' . $lastMonth
    //             : $lastMonth;
    //     } else {
    //         $formattedMonths = 'No Months Selected';
    //     }

    //     // ══════════════════════════════════════════════════════════
    //     //  STUDENT LOOKUP
    //     // ══════════════════════════════════════════════════════════
    //     if ($this->input->post('user_id')) {

    //         $userId      = trim($this->input->post('user_id'));
    //         $studentData = $this->CM->get_data("Users/Parents/$school_id/$userId");

    //         if (!empty($studentData)) {
    //             $studentData      = (array)$studentData;
    //             $classWithSection = $studentData['Class'] ?? '';

    //             // ── Parse class + section ─────────────────────────
    //             // OLD: explode(' ', "8th A") → classOnly="8th"  section="A"
    //             //      then built "Class 8th 'A'" as one merged key
    //             //
    //             // NEW: parseClassSection("8th A") → ["Class 8th", "Section A"]
    //             //      two separate Firebase node keys
    //             list($class, $section) = $this->parseClassSection($classWithSection);

    //             log_message('debug', "fees_counter: userId=$userId rawClass='$classWithSection' → class='$class' section='$section'");

    //             // Fallback: check dedicated 'Section' field if section still empty
    //             if ($section === '') {
    //                 $rawSection = trim($studentData['Section'] ?? '');
    //                 if ($rawSection !== '') {
    //                     $section = (stripos($rawSection, 'Section ') === 0)
    //                         ? $rawSection
    //                         : 'Section ' . strtoupper($rawSection);
    //                     log_message('debug', "fees_counter: section from fallback='$section'");
    //                 }
    //             }

    //             if ($class !== '' && $section !== '') {

    //                 // ── Student base path (NEW: two separate keys) ─
    //                 // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}
    //                 // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}
    //                 $studentBase = $this->studentPath($class, $section, $userId);
    //                 log_message('debug', "fees_counter: studentBase='$studentBase'");

    //                 // ── Fees Record (payment history) ──────────────
    //                 $receiptrecordData = $this->CM->get_data("$studentBase/Fees Record");
    //                 $data['fee_records'] = !empty($receiptrecordData) ? $receiptrecordData : 'Not Found';

    //                 if (!empty($data['fee_records']) && is_array($data['fee_records'])) {
    //                     foreach ($data['fee_records'] as $receiptno => $record) {
    //                         $record     = (array)$record;
    //                         $feeDate    = $record['Date'] ?? 'N/A';
    //                         $feeDetails[] = [
    //                             'receiptno' => $receiptno,
    //                             'Amount'    => number_format(floatval(str_replace(',', '', $record['Amount']   ?? 0)), 2),
    //                             'Discount'  => number_format(floatval(str_replace(',', '', $record['Discount'] ?? 0)), 2),
    //                             'Date'      => $feeDate,
    //                             'Fine'      => number_format(floatval(str_replace(',', '', $record['Fine']     ?? 0)), 2),
    //                             'Mode'      => $record['Mode'] ?? 'N/A',
    //                         ];
    //                     }
    //                 }

    //                 // ── Total submitted ────────────────────────────
    //                 foreach ($feeDetails as $fd) {
    //                     $totalSubmittedAmount += floatval(str_replace(',', '', $fd['Amount']));
    //                 }

    //                 // ── Oversubmitted fees ─────────────────────────
    //                 // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Oversubmittedfees
    //                 // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Oversubmittedfees
    //                 $oversubmittedFees = (float)($this->CM->get_data("$studentBase/Oversubmittedfees") ?? 0);

    //                 $data['totalSubmittedAmount'] = number_format($totalSubmittedAmount, 2, '.', ',');
    //                 $data['oversubmittedFees']    = number_format($oversubmittedFees,    2, '.', ',');
    //                 $data['showMonthDropdown']    = true;
    //                 $data['message']              = '';

    //                 // Display value for the class input box
    //                 // Show as "Class 8th - Section A" or whatever your view expects
    //                 $data['classOnly'] = $class . " - " . $section;
    //                 $data['section']   = $section;

    //                 // ── Monthly fee breakdown ──────────────────────
    //                 if (!empty($selectedMonths) && is_array($selectedMonths)) {

    //                     // getFeesForSelectedMonths now receives $class + $section separately
    //                     // OLD: getFeesForSelectedMonths($school_name, "Class 8th 'A'", $months)
    //                     // NEW: getFeesForSelectedMonths($school_name, $class, $section, $months)
    //                     $feesRecord = $this->getFeesForSelectedMonths($school_name, $class, $section, $selectedMonths);

    //                     // Exempted fees
    //                     // OLD path: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/exempted_fees
    //                     // NEW path: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Exempted Fees
    //                     $exemptedFees = $this->CM->get_data("$studentBase/Exempted Fees");
    //                     $exemptedFees = !empty($exemptedFees) ? (array)$exemptedFees : [];

    //                     $totals      = $this->calculateTotalFees($feesRecord, $selectedMonths, $exemptedFees);
    //                     $feesRecord  = [];
    //                     $discount    = 0;
    //                     $totalAmount = 0;

    //                     foreach ($totals as $feeTitle => $feeTotal) {
    //                         if (strtolower($feeTitle) === 'discount') {
    //                             $discount = $feeTotal;
    //                         } else {
    //                             $totalAmount += $feeTotal;
    //                         }
    //                         $feesRecord[] = ['title' => $feeTitle, 'total' => $feeTotal];
    //                     }
    //                     $totalAmount += $discount;

    //                     // On-demand discount
    //                     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Discount
    //                     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Discount
    //                     $DiscountData   = $this->CM->get_data("$studentBase/Discount");
    //                     $discountAmount = isset($DiscountData['OnDemandDiscount']) ? (float)$DiscountData['OnDemandDiscount'] : 0;

    //                     $data['feesRecord']     = $feesRecord;
    //                     $data['discountAmount'] = number_format($discountAmount, 2);
    //                     $data['totalAmount']    = number_format($totalAmount, 2);
    //                     $data['message']        = "Fee Details for :- " . $formattedMonths;
    //                     $data['selectedMonths'] = $selectedMonths;

    //                     // ── Detailed per-month table ───────────────
    //                     $feeRecord   = [];
    //                     $monthTotals = array_fill_keys($selectedMonths, 0);
    //                     $grandTotal  = 0;

    //                     // Fee-chart path (already uses new structure via feesPath())
    //                     // OLD: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th 'A'/April/Bus Fees
    //                     // NEW: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th/Section A/April/Bus Fees
    //                     $fp = $this->feesPath($class, $section);
    //                     log_message('debug', "fees_counter: feesPath='$fp'");

    //                     $feeTitlesArray = [];
    //                     foreach ($totals as $feetitle => $feeTotal) {
    //                         $feeTitlesArray[] = str_replace(' (Yearly)', '', $feetitle);
    //                     }

    //                     foreach ($feeTitlesArray as $feename) {
    //                         $feeRecord[$feename] = ['title' => $feename, 'total' => 0];

    //                         foreach ($selectedMonths as $m) {
    //                             $feeValue = (float)($this->CM->get_data("$fp/$m/$feename") ?? 0);
    //                             $feeRecord[$feename][$m]      = $feeValue;
    //                             $monthTotals[$m]             += $feeValue;
    //                             $feeRecord[$feename]['total'] += $feeValue;
    //                         }
    //                         $grandTotal += $feeRecord[$feename]['total'];
    //                     }

    //                     $data['feeRecord']   = $feeRecord;
    //                     $data['monthTotals'] = $monthTotals;
    //                     $data['grandTotal']  = $grandTotal;
    //                 }

    //                 // ── Due amount ─────────────────────────────────
    //                 $dueAmount         = $totalAmount - $oversubmittedFees - ($discountAmount ?? 0);
    //                 $data['dueAmount'] = number_format(max(0, $dueAmount), 2);
    //             } else {
    //                 log_message('error', "fees_counter: Could not parse class/section for userId=$userId rawClass='$classWithSection'");
    //                 $data['error'] = "Could not determine class/section for student '$userId'. Raw class field: '$classWithSection'.";
    //             }
    //         } else {
    //             $studentData   = ['Name' => 'Not Found', 'Father Name' => 'Not Found', 'Class' => 'Not Found'];
    //             $data['error'] = 'Student not found. Please check the User ID.';
    //         }
    //     }

    //     $data['accounts']    = $filteredAccounts;
    //     $data['studentData'] = $studentData;
    //     $data['feeDetails']  = $feeDetails;
    //     $data['classOnly']   = isset($data['classOnly']) ? $data['classOnly'] : (isset($studentData['Class']) ? $studentData['Class'] : 'Not Found');
    //     $data['section']     = $section;

    //     if (!isset($data['totalSubmittedAmount'])) $data['totalSubmittedAmount'] = '00.00';
    //     if (!isset($data['dueAmount']))            $data['dueAmount']            = '00.00';

    //     $this->load->view('include/header');
    //     $this->load->view('fees_counter', $data);
    //     $this->load->view('include/footer');
    // }

    public function fees_counter()
    {
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        // Receipt number
        $receiptPath       = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
        $data['receiptNo'] = $this->CM->get_data($receiptPath) ?: '1';

        // Accounts dropdown
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

        // Server date — try Firebase timestamp, fallback to PHP date
        $ts = $this->CM->get_data("Schools/$school_name/$session_year/ServerTimestamp");
        $data['serverDate'] = (!empty($ts) && is_numeric($ts))
            ? date('d-m-Y', $ts / 1000)
            : date('d-m-Y');   // BUG FIX: fallback to PHP date, not "Timestamp Not Found"

        $this->load->view('include/header');
        $this->load->view('fees_counter', $data);
        $this->load->view('include/footer');
    }

    // ── NEW: Lookup single student by User ID (for inline Find button) ────
    // BUG FIX: Previously only had a search modal. Now the Find button on the
    // main form calls this endpoint directly with the typed ID and gets
    // student data back as JSON — no modal needed for simple ID lookup.

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

        // ✅ Read directly from Firebase fields
        $class   = trim($student['Class'] ?? '');
        $section = trim($student['Section'] ?? '');

        echo json_encode([
            'user_id'     => $student['User Id'] ?? $userId,
            'name'        => $student['Name'] ?? '',
            'father_name' => $student['Father Name'] ?? '',
            'class'       => $class,
            'section'     => $section
        ]);
    }


    // ══════════════════════════════════════════════════════════════
    //  FETCH MONTHS
    //
    //  KEY CHANGES:
    //  OLD path: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Month Fee
    //  NEW path: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Month Fee
    // ══════════════════════════════════════════════════════════════

    // public function fetch_months()
    // {
    //     $school_id    = $this->school_id;
    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     if ($this->input->post('user_id')) {

    //         $userId      = trim($this->input->post('user_id'));
    //         $studentData = $this->CM->get_data("Users/Parents/$school_id/$userId");

    //         if (!empty($studentData)) {
    //             $studentData      = (array)$studentData;
    //             $classWithSection = trim($studentData['Class'] ?? '');

    //             if (!empty($classWithSection)) {

    //                 // Parse "8th A" → ["Class 8th", "Section A"]
    //                 list($class, $section) = $this->parseClassSection($classWithSection);

    //                 if ($class && $section) {
    //                     // NEW path: two separate keys
    //                     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Month Fee
    //                     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Month Fee
    //                     $studentBase   = $this->studentPath($class, $section, $userId);
    //                     $monthFeesData = $this->CM->get_data("$studentBase/Month Fee");

    //                     $months = [
    //                         'January',
    //                         'February',
    //                         'March',
    //                         'April',
    //                         'May',
    //                         'June',
    //                         'July',
    //                         'August',
    //                         'September',
    //                         'October',
    //                         'November',
    //                         'December',
    //                         'Yearly Fees',
    //                     ];
    //                     $monthFees = [];
    //                     foreach ($months as $month) {
    //                         $monthFees[$month] = $monthFeesData[$month] ?? 0;
    //                     }

    //                     echo json_encode($monthFees);
    //                     return;
    //                 }
    //             }
    //         }
    //     }

    //     echo json_encode(['error' => 'Invalid request or student not found']);
    // }

    public function fetch_months()
    {
        header('Content-Type: application/json');

        $school_id = $this->school_id;

        $userId = trim($this->input->post('user_id') ?? '');
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

        // Resolve class + section
        list($class, $section) = $this->_resolveClassSection($student);

        if ($class === '' || $section === '') {
            echo json_encode([
                'error'   => "Cannot resolve class/section for '$userId'",
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

        log_message('debug', "fetch_months: userId=$userId class=$class section=$section path=$studentBase/Month Fee");
        echo json_encode($result);
    }



    // ══════════════════════════════════════════════════════════════════
    //  METHOD 2: fetch_fee_details()
    //  Fee path: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th/Section A/{month}
    // ══════════════════════════════════════════════════════════════════

    public function fetch_fee_details()
    {
        ob_start();
        header('Content-Type: application/json');

        $school_id = $this->school_id;

        $userId         = trim($this->input->post('user_id') ?? '');
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

        // Resolve class + section using shared helper
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

        log_message('debug', "fetch_fee_details: class=$class section=$section");
        log_message('debug', "fetch_fee_details: feesPath=$fp");
        log_message('debug', "fetch_fee_details: selectedMonths=" . json_encode($selectedMonths));

        // Sanity check — verify fee path has real data
        $testData = $this->CM->get_data("$fp/{$selectedMonths[0]}");
        log_message('debug', "fetch_fee_details: test read $fp/{$selectedMonths[0]} => " . json_encode($testData));

        if (empty($testData)) {
            ob_end_clean();
            echo json_encode([
                'error'        => "No fee data found — path may be wrong",
                'feesPath'     => "$fp/{$selectedMonths[0]}",
                'school_name'  => $this->school_name,
                'session_year' => $this->session_year,
                'class'        => $class,
                'section'      => $section,
            ]);
            return;
        }

        // Exempted fees
        $exemptedFees = $this->CM->get_data("$studentBase/Exempted Fees");
        $exemptedFees = is_array($exemptedFees) ? $exemptedFees : [];

        // ── Read fee titles & amounts directly from Firebase ─────────
        // Using getFeesForSelectedMonths() with CORRECT 4-arg signature.
        // This now calls feesPath() internally so the path is correct.
        $prevLevel  = error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        $feesRecord = $this->getFeesForSelectedMonths(
            $this->school_name,   // arg 1 — kept for compat, ignored internally
            $class,               // arg 2 — "Class 8th"
            $section,             // arg 3 — "Section A"
            $selectedMonths       // arg 4 — ["April", ...]
        );
        error_reporting($prevLevel);

        if (!is_array($feesRecord)) $feesRecord = [];

        // ── Build breakdown ───────────────────────────────────────────
        $feeRecord     = [];
        $feesRecordArr = [];
        $monthTotals   = array_fill_keys($selectedMonths, 0);
        $grandTotal    = 0;

        // Get all unique fee titles from all selected months
        $allFeeTitles = [];
        foreach ($selectedMonths as $month) {
            if (!is_array($feesRecord[$month] ?? null)) continue;
            foreach (array_keys($feesRecord[$month]) as $t) {
                if (!in_array($t, $allFeeTitles)) $allFeeTitles[] = $t;
            }
        }

        foreach ($allFeeTitles as $feename) {
            // Skip exempted
            $cleanName = str_replace(' (Yearly)', '', $feename);
            if (array_key_exists($cleanName, $exemptedFees)) continue;

            $feeRecord[$feename] = ['title' => $feename, 'total' => 0];

            foreach ($selectedMonths as $month) {
                $val = (float)($feesRecord[$month][$feename] ?? 0);
                $feeRecord[$feename][$month]      = $val;
                $monthTotals[$month]             += $val;
                $feeRecord[$feename]['total']     += $val;
            }

            $grandTotal += $feeRecord[$feename]['total'];
            $feesRecordArr[] = [
                'title' => $feename,
                'total' => $feeRecord[$feename]['total'],
            ];
        }

        // On-demand discount
        $discountData   = $this->CM->get_data("$studentBase/Discount");
        $discountAmount = (is_array($discountData) && isset($discountData['OnDemandDiscount']))
            ? (float)$discountData['OnDemandDiscount']
            : 0;

        // Oversubmitted carry-forward
        $overRaw       = $this->CM->get_data("$studentBase/Oversubmittedfees");
        $oversubmitted = is_numeric($overRaw) ? (float)$overRaw : 0;

        // Label
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
    //  PRIVATE HELPER: _resolveClassSection()
    //  Shared by fetch_months() and fetch_fee_details() to avoid
    //  duplicating the same class/section resolution logic.
    //
    //  Student Firebase record has TWO possible formats:
    //    Format A: Class="8th",   Section="B"  (separate fields)
    //    Format B: Class="8th B", Section=""   (merged in Class field)
    //
    //  Returns: ["Class 8th", "Section B"]
    // ══════════════════════════════════════════════════════════════════

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

        // If class still empty (e.g. Class field was just "8th"),
        // rebuild it with the "Class " prefix
        if ($class === '' && $classRaw !== '') {
            // Take only the first token to avoid grabbing a section that
            // parseClassSection already missed
            $stripped = preg_replace('/^Class\s+/i', '', $classRaw);
            $firstPart = trim(explode(' ', $stripped)[0]);
            $class = 'Class ' . $firstPart;
        }

        return [$class, $section];
    }



    public function get_server_date()
    {
        header('Content-Type: application/json');
        $ts = $this->CM->get_data(
            "Schools/{$this->school_name}/{$this->session_year}/ServerTimestamp"
        );
        $date = (!empty($ts) && is_numeric($ts))
            ? date('d-m-Y', $ts / 1000)
            : date('d-m-Y');
        echo json_encode(['date' => $date]);
    }


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
            $name   = $s['Name']        ?? '';
            $sid    = $s['User Id']     ?? '';
            $father = $s['Father Name'] ?? '';
            $class  = $s['Class']       ?? '';
            if (
                stripos($name,   $entry) !== false ||
                stripos($sid,    $entry) !== false ||
                stripos($father, $entry) !== false ||
                stripos($class,  $entry) !== false
            ) {
                $results[] = ['user_id' => $sid, 'name' => $name, 'father_name' => $father, 'class' => $class];
            }
        }
        return $results;
    }


    // public function submit_fees()
    // {
    //     $this->load->library('firebase');

    //     $school_name  = $this->school_name;
    //     $session_year = $this->session_year;

    //     // ── Collect POST data ──────────────────────────────────────
    //     $receiptNo    = $this->input->post('receiptNo');
    //     $studentName  = $this->input->post('studentName');
    //     $paymentMode  = $this->input->post('paymentMode') ?: 'N/A';
    //     $fatherName   = $this->input->post('fatherName');
    //     $classRaw     = $this->input->post('class');   // whatever the view sends, e.g. "Class 8th - Section A" or "8th A"
    //     $userId       = $this->input->post('userId');

    //     $totalAmount  = floatval(str_replace(',', '', $this->input->post('totalAmount')    ?? '0'));
    //     $submitAmount = floatval(str_replace(',', '', $this->input->post('submitAmount')   ?? '0'));
    //     $dueAmount    = floatval(str_replace(',', '', $this->input->post('dueAmount')      ?? '0'));
    //     $schoolFees   = floatval(str_replace(',', '', $this->input->post('schoolFees')     ?? '0'));
    //     $discountFees = floatval(str_replace(',', '', $this->input->post('discountAmount') ?? '0'));
    //     $fineAmount   = floatval(str_replace(',', '', $this->input->post('fineAmount')     ?? '0'));
    //     $reference    = $this->input->post('reference') ?: 'Fees Submitted';

    //     $selectedMonths = $this->input->post('selectedMonths') ?? [];
    //     $MonthTotal     = $this->input->post('monthTotals')    ?? [];

    //     if (!is_array($selectedMonths)) {
    //         $selectedMonths = explode(',', $selectedMonths);
    //     }

    //     $monthTotalsArray = [];
    //     foreach ($MonthTotal as $md) {
    //         if (isset($md['month'], $md['total'])) {
    //             $monthTotalsArray[trim($md['month'])] = floatval(str_replace(',', '', $md['total']));
    //         }
    //     }

    //     $date     = date('d-m-Y');
    //     $date_obj = DateTime::createFromFormat('d-m-Y', $date);
    //     $month    = ($date_obj !== false) ? $date_obj->format('F') : date('F');
    //     $day      = ($date_obj !== false) ? $date_obj->format('d') : date('d');

    //     // ── Parse class + section from whatever the view sent ─────
    //     // The view may send "Class 8th - Section A" (our display format)
    //     // or "8th A" (raw from student record) — parseClassSection handles both.
    //     //
    //     // Special case: view sends "Class 8th - Section A" (dash format)
    //     // Strip dash variant before parsing
    //     $classRawClean = str_replace(' - Section ', ' ', $classRaw); // "Class 8th - Section A" → "Class 8th A"
    //     $classRawClean = str_replace(' - ', ' ', $classRawClean);     // fallback for other dash formats

    //     list($class, $section) = $this->parseClassSection($classRawClean);

    //     log_message('debug', "submit_fees: classRaw='$classRaw' → class='$class' section='$section'");

    //     $receiptKey  = 'F' . $receiptNo;
    //     $studentBase = $this->studentPath($class, $section, $userId);

    //     // ── 1. Reset OnDemandDiscount, update totalDiscount ───────
    //     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Discount/...
    //     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Discount/...
    //     $discPath1 = "$studentBase/Discount/OnDemandDiscount";
    //     $discPath2 = "$studentBase/Discount/totalDiscount";

    //     try {
    //         log_message('info', "submit_fees: resetting OnDemandDiscount at $discPath1");
    //         $this->firebase->set($discPath1, 0);

    //         $currentTotal = $this->firebase->get($discPath2);
    //         $currentTotal = is_numeric($currentTotal) ? (int)$currentTotal : 0;
    //         $newTotal     = $currentTotal + (int)$discountFees;
    //         $this->firebase->set($discPath2, $newTotal);
    //         log_message('info', "submit_fees: totalDiscount updated to $newTotal");
    //     } catch (Exception $e) {
    //         log_message('error', "submit_fees discount error: " . $e->getMessage());
    //         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    //         return;
    //     }

    //     // ── 2. Fees Record ─────────────────────────────────────────
    //     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Fees Record
    //     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Fees Record
    //     $this->firebase->update("$studentBase/Fees Record", [
    //         $receiptKey => [
    //             'Amount'   => number_format($schoolFees,   2, '.', ','),
    //             'Discount' => number_format($discountFees, 2, '.', ','),
    //             'Date'     => $date,
    //             'Fine'     => number_format($fineAmount,   2, '.', ','),
    //             'Mode'     => $paymentMode,
    //             'Refer'    => $reference,
    //         ]
    //     ]);

    //     // ── 3. Vouchers ────────────────────────────────────────────
    //     // Path unchanged — no class segment here
    //     $this->firebase->update("Schools/$school_name/$session_year/Accounts/Vouchers/$date", [
    //         $receiptKey => [
    //             'Acc'           => 'Fees',
    //             'Fees Received' => number_format($schoolFees, 2),
    //             'Id'            => $userId,
    //             'Mode'          => $paymentMode,
    //         ]
    //     ]);

    //     // ── 4. Account book ────────────────────────────────────────
    //     // Path unchanged — no class segment here
    //     $ab = "Schools/$school_name/$session_year/Accounts/Account_book";
    //     $updateReceived = function ($path, $amount) {
    //         $rp  = "$path/R";
    //         $cur = floatval($this->firebase->get($rp) ?? '0');
    //         $this->firebase->set($rp, $cur + $amount);
    //     };

    //     $updateReceived("$ab/Discount/$month/$day", $discountFees);
    //     $updateReceived("$ab/Fees/$month/$day",     $schoolFees);
    //     $updateReceived("$ab/Fine/$month/$day",     $fineAmount);

    //     // ── 5. Receipt counter ─────────────────────────────────────
    //     $rcPath = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
    //     $this->firebase->set($rcPath, ((int)($this->firebase->get($rcPath) ?? 0)) + 1);

    //     // ── 6. Mark paid months ────────────────────────────────────
    //     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Month Fee/{month}
    //     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Month Fee/{month}
    //     $monthOrder = [
    //         'April',
    //         'May',
    //         'June',
    //         'July',
    //         'August',
    //         'September',
    //         'October',
    //         'November',
    //         'December',
    //         'January',
    //         'February',
    //         'March',
    //         'Yearly Fees',
    //     ];
    //     usort($selectedMonths, fn($a, $b) => array_search($a, $monthOrder) - array_search($b, $monthOrder));

    //     $totalSubmitted = $schoolFees + $submitAmount;
    //     foreach ($selectedMonths as $m) {
    //         $monthFee = $monthTotalsArray[$m] ?? 0;
    //         if ($monthFee > 0 && $totalSubmitted >= $monthFee) {
    //             $this->firebase->set("$studentBase/Month Fee/$m", 1);
    //             $totalSubmitted -= $monthFee;
    //         }
    //     }

    //     // ── 7. Oversubmitted carry-forward ─────────────────────────
    //     // OLD: Schools/{sn}/{sy}/Class 8th 'A'/Students/{uid}/Oversubmittedfees
    //     // NEW: Schools/{sn}/{sy}/Class 8th/Section A/Students/{uid}/Oversubmittedfees
    //     if ($totalSubmitted > 0) {
    //         $this->firebase->set("$studentBase/Oversubmittedfees", $totalSubmitted);
    //     }

    //     echo json_encode(['status' => 'success', 'message' => 'Fees submitted successfully!']);
    // }


    // ══════════════════════════════════════════════════════════════
    //  GET FEES FOR SELECTED MONTHS
    //
    //  KEY CHANGES:
    //  OLD signature: getFeesForSelectedMonths($school_name, $classSection, $selectedMonths)
    //                 $classSection was "Class 8th 'A'" (merged)
    //                 path: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th 'A'/{month}
    //
    //  NEW signature: getFeesForSelectedMonths($school_name, $class, $section, $selectedMonths)
    //                 $class   = "Class 8th"
    //                 $section = "Section A"
    //                 path: Schools/{sn}/{sy}/Accounts/Fees/Classes Fees/Class 8th/Section A/{month}
    //                 (built via feesPath() helper)
    // ══════════════════════════════════════════════════════════════


    // ── SUBMIT FEES ───────────────────────────────────────────────────────



    public function submit_fees()
    {
        // ── Must be FIRST: set JSON header before any output ──────────
        // Using CI's output class avoids the "headers already sent" issue
        // that ob_start/ob_end_clean was trying to work around.
        $this->output->set_content_type('application/json');

        $this->load->library('firebase');

        $school_id    = $this->school_id;
        $school_name  = $this->school_name;
        $session_year = $this->session_year;

        /* ── Collect POST data ── */
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

        /* ── Basic validation ── */
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

        /* ── Resolve class + section from Firebase (not from POST) ── */
        $student = $this->CM->get_data("Users/Parents/$school_id/$userId");
        if (empty($student)) {
            $this->output->set_output(json_encode([
                'status'  => 'error',
                'message' => "Student '$userId' not found in Firebase"
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
                    . "Section='" . ($student['Section'] ?? '') . "')"
            ]));
            return;
        }

        /* ── Date helpers ── */
        $date     = date('d-m-Y');
        $date_obj = DateTime::createFromFormat('d-m-Y', $date);
        $month    = $date_obj ? $date_obj->format('F') : date('F');
        $day      = $date_obj ? $date_obj->format('d') : date('d');

        $receiptKey  = 'F' . $receiptNo;
        $studentBase = $this->studentPath($class, $section, $userId);

        log_message('debug', "submit_fees: userId=$userId class=$class section=$section");
        log_message('debug', "submit_fees: studentBase=$studentBase");
        log_message('debug', "submit_fees: schoolFees=$schoolFees months=" . json_encode($selectedMonths));
        log_message('debug', "submit_fees: monthTotals=" . json_encode($monthTotalsArray));

        /* ── 1. Reset OnDemandDiscount, accumulate totalDiscount ── */
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
                'message' => 'Discount update failed: ' . $e->getMessage()
            ]));
            return;
        }

        /* ── 2. Fees Record ── */
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
                'message' => 'Fees Record write failed: ' . $e->getMessage()
            ]));
            return;
        }

        /* ── 3. Vouchers ── */
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
            // Non-fatal — continue
        }

        /* ── 4. Account book ledger ── */
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
            log_message('error', 'submit_fees account book write failed: ' . $e->getMessage());
            // Non-fatal — continue
        }

        /* ── 5. Increment receipt counter ── */
        try {
            $rcPath = "Schools/$school_name/$session_year/Accounts/Fees/Receipt No";
            $this->firebase->set($rcPath, ((int)($this->firebase->get($rcPath) ?? 0)) + 1);
        } catch (Exception $e) {
            log_message('error', 'submit_fees receipt counter failed: ' . $e->getMessage());
        }

        /* ── 6. Mark months as paid ── */
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
                log_message('debug', "submit_fees: marked '$m' paid (fee=$mFee remaining=$totalSubmitted)");
            }
        }

        /* ── 7. Carry-forward overpaid amount ── */
        if ($totalSubmitted > 0.005) {   // ignore float rounding noise
            $this->firebase->set("$studentBase/Oversubmittedfees", round($totalSubmitted, 2));
            log_message('debug', "submit_fees: carry-forward=" . round($totalSubmitted, 2));
        }

        $this->output->set_output(json_encode([
            'status'  => 'success',
            'message' => 'Fees submitted successfully!'
        ]));
    }


    // ══════════════════════════════════════════════════════════════════
    //  METHOD 3: getFeesForSelectedMonths()
    //  FIXED: Now takes 4 args and uses feesPath() helper
    //  OLD: ($school_name, $classSection, $selectedMonths)             — 3 args
    //  NEW: ($school_name, $class, $section, $selectedMonths)          — 4 args
    // ══════════════════════════════════════════════════════════════════

    public function getFeesForSelectedMonths($school_name, $class, $section, $selectedMonths)
    {
        // Build the correct new-format path: .../Classes Fees/Class 8th/Section A
        $fp       = $this->feesPath($class, $section);
        $feesData = [];

        foreach ($selectedMonths as $month) {
            $prevLevel = error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
            $monthFees = $this->CM->get_data("$fp/$month");
            error_reporting($prevLevel);

            if (is_array($monthFees)) {
                $feesData[$month] = $monthFees;
            } elseif (is_string($monthFees) && $monthFees !== '') {
                $decoded = json_decode($monthFees, true);
                $feesData[$month] = is_array($decoded) ? $decoded : [];
            } else {
                $feesData[$month] = [];
            }

            log_message('debug', "getFeesForSelectedMonths: $fp/$month => " . json_encode($feesData[$month]));
        }

        return $feesData;
    }

    public function calculateTotalFees($feesRecord, $selectedMonths, $exemptedFees)
    {
        $totals = [];
        foreach ($selectedMonths as $month) {
            if (!isset($feesRecord[$month]) || !is_array($feesRecord[$month])) continue;
            foreach ($feesRecord[$month] as $feeTitle => $feeAmount) {
                $clean = str_replace(' (Yearly)', '', $feeTitle);
                if (array_key_exists($clean, $exemptedFees)) continue;
                $display = ($month === 'Yearly Fees') ? "$clean (Yearly)" : $clean;
                $totals[$display] = ($totals[$display] ?? 0) + floatval($feeAmount);
            }
        }
        return $totals;
    }



    // ──────────────────────────────────────────────────────────────────
    //  class_fees()  — Class selector page (links from fees_records)
    // ──────────────────────────────────────────────────────────────────
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
        $data['class']    = $this->input->get('class')   ?? '';
        $data['section']  = $this->input->get('section') ?? '';

        $this->load->view('include/header');
        $this->load->view('class_fees', $data);
        $this->load->view('include/footer');
    }


    // ──────────────────────────────────────────────────────────────────
    //  due_fees_table()  — AJAX endpoint for class_fees page
    // ──────────────────────────────────────────────────────────────────
    public function due_fees_table()
    {
        // BUG FIX: Set JSON header via CI output class (avoids ob_start issues)
        $this->output->set_content_type('application/json');

        $school_id = $this->school_id;
        $sn        = $this->school_name;
        $sy        = $this->session_year;

        $class   = trim($this->input->post('class')   ?? '');
        $section = trim($this->input->post('section') ?? '');

        if (!$class || !$section) {
            $this->output->set_output(json_encode([
                [
                    'userId' => null,
                    'name' => 'Missing class or section parameter',
                    'totalFee' => null,
                    'receivedFee' => null,
                    'dueFee' => null
                ]
            ]));
            return;
        }

        $studentsPath = $this->studentPath($class, $section) . '/List';
        $feesPath     = $this->feesPath($class, $section);

        // Student list
        $student_ids = $this->firebase->get($studentsPath);
        if (empty($student_ids)) {
            $this->output->set_output(json_encode([
                [
                    'userId' => null,
                    'name' => "No students in $class $section",
                    'totalFee' => null,
                    'receivedFee' => null,
                    'dueFee' => null
                ]
            ]));
            return;
        }

        // Fee structure for the class
        $class_fees = $this->firebase->get($feesPath);
        if (empty($class_fees)) {
            $this->output->set_output(json_encode([
                [
                    'userId' => null,
                    'name' => "No fee structure defined for $class $section",
                    'totalFee' => null,
                    'receivedFee' => null,
                    'dueFee' => null
                ]
            ]));
            return;
        }

        // Compute annual total from fee structure
        $annual_fee = 0;
        foreach ($class_fees as $month => $fees) {
            if (is_array($fees)) {
                foreach ($fees as $title => $amt) {
                    $annual_fee += (float)str_replace(',', '', $amt ?? 0);
                }
            }
        }

        // BUG FIX: Bulk-fetch all student profiles for this school once
        $allProfiles = $this->CM->get_data("Users/Parents/$school_id");
        $allProfiles = is_array($allProfiles) ? $allProfiles : [];

        $response = [];
        foreach ($student_ids as $uid => $v) {
            $uid    = (string)$uid;
            $prof   = isset($allProfiles[$uid]) ? (array)$allProfiles[$uid] : [];
            $name   = $prof['Name']        ?? 'N/A';
            $fname  = $prof['Father Name'] ?? 'N/A';

            // Sum all fee receipts for this student
            $recs = $this->firebase->get($this->studentPath($class, $section, $uid) . '/Fees Record');
            $paid = 0;
            if (is_array($recs)) {
                foreach ($recs as $r) {
                    if (is_array($r)) {
                        $paid += (float)str_replace(',', '', $r['Amount'] ?? 0);
                    }
                }
            }

            // Read discount applied to this student
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

        // Sort: most due first
        usort($response, fn($a, $b) => $b['dueFee'] <=> $a['dueFee']);

        $this->output->set_output(json_encode($response));
    }
   
    public function fees_records()
    {
        $sn        = $this->school_name;
        $sy        = $this->session_year;
        $school_id = $this->school_id;

        $yearRoot = $this->CM->get_data("Schools/$sn/$sy");
        $yearRoot = is_array($yearRoot) ? $yearRoot : [];

        // ── 1. Build classList + feesMatrix keys from Firebase structure ──
        $classList  = [];   // "Class 8th|Section A" => "Class 8th Section A"
        $feesMatrix = [];   // "Class 8th|Section A" => [0,0,0,...0]  (12 months)

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
                $matKey = "$key|$normSec";
                $classList[$matKey]  = "$key $normSec";
                $feesMatrix[$matKey] = array_fill(0, 12, 0);
            }
        }

        // Sort keys by class number
        uksort($classList, function ($a, $b) {
            preg_match('/(\d+)/', $a, $ma);
            preg_match('/(\d+)/', $b, $mb);
            return ((int)($ma[1] ?? 0)) <=> ((int)($mb[1] ?? 0));
        });

        // ── 2. BUG FIX: Bulk-load ALL student records once ────────────
        //    Old code: called firebase->get(Users/Parents/{id}/Class) per
        //    voucher → 300+ HTTP calls → timeout.
        //    New: one call, local cache map.
        $allStudents = $this->CM->get_data("Users/Parents/$school_id");
        $studentClassCache = [];  // uid => "Class 8th|Section A"
        if (is_array($allStudents)) {
            foreach ($allStudents as $uid => $stu) {
                $stu = is_array($stu) ? $stu : [];
                list($cls, $sec) = $this->_resolveClassSection($stu);
                if ($cls !== '' && $sec !== '') {
                    $studentClassCache[(string)$uid] = "$cls|$sec";
                }
            }
        }

        // ── 3. Walk vouchers, accumulate into matrix ───────────────────
        $vouchers = $this->CM->get_data("Schools/$sn/$sy/Accounts/Vouchers");
        $vouchers = is_array($vouchers) ? $vouchers : [];

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
            'March'
        ];

        foreach ($vouchers as $date => $vList) {
            if ($date === 'VoucherCount' || !is_array($vList)) continue;
            $dObj = DateTime::createFromFormat('d-m-Y', $date);
            if (!$dObj) continue;

            // Academic month index: April=0 … March=11
            $calMonth = (int)$dObj->format('n'); // 1-12
            $mi = ($calMonth >= 4) ? ($calMonth - 4) : ($calMonth + 8);

            foreach ($vList as $vk => $v) {
                if (!is_array($v) || strpos((string)$vk, 'F') !== 0) continue;
                $received = (float)str_replace(',', '', $v['Fees Received'] ?? 0);
                if ($received <= 0) continue;
                $sid = trim((string)($v['Id'] ?? ''));
                if ($sid === '') continue;

                // BUG FIX: Use local cache instead of Firebase HTTP call
                $matKey = $studentClassCache[$sid] ?? null;
                if ($matKey && isset($feesMatrix[$matKey])) {
                    $feesMatrix[$matKey][$mi] += $received;
                }
            }
        }

        // ── 4. Build matrix array for view ────────────────────────────
        $matrix = [];
        foreach ($classList as $k => $label) {
            $amounts = $feesMatrix[$k] ?? array_fill(0, 12, 0);
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

