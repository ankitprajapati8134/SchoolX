<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Fee_management Controller
 *
 * Advanced fee management module: categories, discount policies, scholarships,
 * refunds, fee reminders, payment gateway config, and online payments.
 *
 * Extends MY_Controller which provides:
 *   $this->school_name, $this->school_id, $this->session_year,
 *   $this->firebase, $this->CM, json_success(), json_error()
 */
class Fee_management extends MY_Controller
{
    /** Roles for admin-level config (gateway, refund approval) */
    private const ADMIN_ROLES   = ['Admin', 'Principal'];

    /** Roles for financial operations (categories, discounts, scholarships) */
    private const FINANCE_ROLES = ['Admin', 'Principal', 'Accountant'];

    /** Roles that may view fee management data */
    private const VIEW_ROLES    = ['Admin', 'Principal', 'Accountant', 'Teacher'];

    /** @var string Base Firebase path for fees */
    private $feesBase;

    /** @var string Session root path */
    private $sessionRoot;

    public function __construct()
    {
        parent::__construct();
        require_permission('Fees');
        $sn = $this->school_name;
        $sy = $this->session_year;
        $this->feesBase    = "Schools/$sn/$sy/Accounts/Fees";
        $this->sessionRoot = "Schools/$sn/$sy";
    }

    // ══════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Get existing fee structure (Monthly + Yearly titles).
     */
    private function _getFeesStructure()
    {
        $raw = $this->firebase->get("{$this->feesBase}/Fees Structure");
        return is_array($raw) ? $raw : [];
    }

    /**
     * Get all fee title names as a flat array.
     */
    private function _getAllFeeTitles()
    {
        $structure = $this->_getFeesStructure();
        $titles = [];
        foreach ($structure as $type => $fees) {
            if (is_array($fees)) {
                foreach (array_keys($fees) as $title) {
                    $titles[] = $title;
                }
            }
        }
        return $titles;
    }

    /**
     * Parse class string into [classNode, sectionNode].
     * Input: "Class 9th" + "Section A" or "9th" + "A"
     */
    private function _normalizeClassSection($class, $section)
    {
        $class = trim($class);
        if (stripos($class, 'Class ') !== 0) {
            $class = 'Class ' . $class;
        }

        $section = trim($section);
        if (stripos($section, 'Section ') !== 0) {
            $section = 'Section ' . strtoupper($section);
        }

        // Reject path-injection characters (., /, #, $, [, ])
        if (preg_match('/[.\/\#\$\[\]]/', $class . $section)) {
            $this->json_error('Invalid class/section value.');
        }

        return [$class, $section];
    }

    /**
     * Get all classes and sections from session root using shallow_get.
     * Returns: [['class' => 'Class 9th', 'section' => 'Section A'], ...]
     */
    private function _getAllClassSections()
    {
        $result = [];
        $classKeys = $this->firebase->shallow_get($this->sessionRoot);
        if (!is_array($classKeys)) {
            return $result;
        }

        foreach ($classKeys as $classKey) {
            $classKey = (string)$classKey;
            if (strpos($classKey, 'Class ') !== 0) {
                continue;
            }

            $sectionKeys = $this->firebase->shallow_get("{$this->sessionRoot}/{$classKey}");
            if (!is_array($sectionKeys)) {
                continue;
            }

            foreach ($sectionKeys as $sectionKey) {
                $sectionKey = (string)$sectionKey;
                if (strpos($sectionKey, 'Section ') !== 0) {
                    continue;
                }
                $result[] = [
                    'class'   => $classKey,
                    'section' => $sectionKey,
                ];
            }
        }

        return $result;
    }

    /**
     * Atomically increment the receipt counter with retry logic.
     * Returns the new receipt number and key string.
     */
    private function _nextReceiptNo()
    {
        $path = "{$this->feesBase}/Receipt No";
        $maxRetries = 5;
        for ($i = 0; $i < $maxRetries; $i++) {
            $current = $this->firebase->get($path);
            $current = !empty($current) ? (int)$current : 0;
            $next = $current + 1;
            $this->firebase->set($path, $next);
            // Verify it was set correctly (optimistic check)
            $verify = $this->firebase->get($path);
            if ((int)$verify === $next) {
                return [
                    'number' => $next,
                    'key'    => 'F' . str_pad($next, 6, '0', STR_PAD_LEFT),
                ];
            }
            // Another process changed it, retry with new value
            usleep(50000 * ($i + 1)); // 50ms, 100ms, 150ms...
        }
        // Fallback: use timestamp-based unique key
        $fallback = (int)(microtime(true) * 1000) % 999999;
        $this->firebase->set($path, $fallback);
        return [
            'number' => $fallback,
            'key'    => 'F' . str_pad($fallback, 6, '0', STR_PAD_LEFT),
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    //  PAGE LOADERS (GET)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Fee Categories page.
     */
    public function categories()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $data['feesStructure'] = $this->_getFeesStructure();
        $data['page_title']    = 'Fee Categories';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/categories', $data);
        $this->load->view('include/footer');
    }

    /**
     * Discount Policies page.
     */
    public function discounts()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $cats = $this->firebase->get("{$this->feesBase}/Categories");
        $data['categories']    = is_array($cats) ? $cats : [];
        $data['feesStructure'] = $this->_getFeesStructure();
        $data['page_title']    = 'Discount Policies';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/discounts', $data);
        $this->load->view('include/footer');
    }

    /**
     * Scholarships page.
     */
    public function scholarships()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $data['page_title'] = 'Scholarships';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/scholarships', $data);
        $this->load->view('include/footer');
    }

    /**
     * Refunds page.
     */
    public function refunds()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $data['fee_titles']  = $this->_getAllFeeTitles();
        $data['page_title']  = 'Fee Refunds';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/refunds', $data);
        $this->load->view('include/footer');
    }

    /**
     * Fee Reminders page.
     */
    public function reminders()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $settings = $this->firebase->get("{$this->feesBase}/Reminder Settings");
        $data['settings']   = is_array($settings) ? $settings : [];
        $data['page_title'] = 'Fee Reminders';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/reminders', $data);
        $this->load->view('include/footer');
    }

    /**
     * Payment Gateway Configuration page.
     */
    public function gateway()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $config = $this->firebase->get("{$this->feesBase}/Gateway Config");
        if (is_array($config)) {
            // Mask secrets for display
            if (!empty($config['api_secret'])) {
                $config['api_secret_masked'] = str_repeat('*', max(0, strlen($config['api_secret']) - 4))
                    . substr($config['api_secret'], -4);
            }
            if (!empty($config['webhook_secret'])) {
                $config['webhook_secret_masked'] = str_repeat('*', max(0, strlen($config['webhook_secret']) - 4))
                    . substr($config['webhook_secret'], -4);
            }
        }
        $data['config']     = is_array($config) ? $config : [];
        $data['page_title'] = 'Payment Gateway';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/gateway', $data);
        $this->load->view('include/footer');
    }

    /**
     * Online Payments listing page.
     */
    public function online_payments()
    {
        $this->_require_role(self::VIEW_ROLES, 'fee_mgmt_view');
        $data = [];
        $config = $this->firebase->get("{$this->feesBase}/Gateway Config");
        $data['gateway_mode'] = is_array($config) && isset($config['mode']) ? $config['mode'] : '';
        $data['page_title']   = 'Online Payments';

        $this->load->view('include/header', $data);
        $this->load->view('fee_management/online_payments', $data);
        $this->load->view('include/footer');
    }

    // ══════════════════════════════════════════════════════════════════
    //  CATEGORY MANAGEMENT (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch all fee categories.
     */
    public function fetch_categories()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_categories');
        $cats = $this->firebase->get("{$this->feesBase}/Categories");
        $categories = [];
        if (is_array($cats)) {
            foreach ($cats as $id => $cat) {
                if (!is_array($cat)) {
                    continue;
                }
                $cat['id'] = $id;
                $categories[] = $cat;
            }
            // Sort by sort_order
            usort($categories, function ($a, $b) {
                return ((int)($a['sort_order'] ?? 999)) - ((int)($b['sort_order'] ?? 999));
            });
        }
        $this->json_success(['categories' => $categories]);
    }

    /**
     * POST — Create or update a fee category.
     * Params: id?, name, description, type, fee_titles (comma-sep), sort_order
     */
    public function save_category()
    {
        $this->_require_role(self::FINANCE_ROLES, 'save_category');
        $name        = trim($this->input->post('name'));
        $description = trim($this->input->post('description'));
        $type        = trim($this->input->post('type'));
        $feeTitles   = trim($this->input->post('fee_titles'));
        $sortOrder   = (int)$this->input->post('sort_order');
        $catId       = trim($this->input->post('id'));

        if ($name === '') {
            $this->json_error('Category name is required.');
        }

        $validTypes = ['academic', 'transport', 'extra', 'other'];
        if (!in_array($type, $validTypes, true)) {
            $this->json_error('Invalid category type. Must be one of: ' . implode(', ', $validTypes));
        }

        // Parse fee titles
        $titlesArray = [];
        if ($feeTitles !== '') {
            $titlesArray = array_map('trim', explode(',', $feeTitles));
            $titlesArray = array_filter($titlesArray, function ($t) {
                return $t !== '';
            });
            $titlesArray = array_values($titlesArray);
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            'name'        => $name,
            'description' => $description,
            'type'        => $type,
            'fee_titles'  => $titlesArray,
            'sort_order'  => $sortOrder,
            'active'      => true,
        ];

        if (!empty($catId)) {
            // Update existing
            $catId = $this->safe_path_segment($catId, 'category_id');
            $data['updated_at'] = $now;
            $this->firebase->update("{$this->feesBase}/Categories/{$catId}", $data);
            $this->json_success(['message' => 'Category updated successfully.', 'id' => $catId]);
        } else {
            // Create new
            $data['created_at'] = $now;
            $newId = uniqid('cat_');
            $this->firebase->set("{$this->feesBase}/Categories/{$newId}", $data);
            $this->json_success(['message' => 'Category created successfully.', 'id' => $newId]);
        }
    }

    /**
     * POST — Delete a fee category.
     * Params: category_id
     */
    public function delete_category()
    {
        $this->_require_role(self::FINANCE_ROLES, 'delete_category');
        $catId = $this->safe_path_segment(trim($this->input->post('category_id') ?? ''), 'category_id');

        // Verify it exists
        $existing = $this->firebase->get("{$this->feesBase}/Categories/{$catId}");
        if (empty($existing)) {
            $this->json_error('Category not found.');
        }

        $this->firebase->delete("{$this->feesBase}/Categories", $catId);
        $this->json_success(['message' => 'Category deleted successfully.']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  DISCOUNT POLICIES (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch all discount policies.
     */
    public function fetch_discounts()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_discounts');
        $raw = $this->firebase->get("{$this->feesBase}/Discount Policies");
        $discounts = [];
        if (is_array($raw)) {
            foreach ($raw as $id => $disc) {
                if (!is_array($disc)) {
                    continue;
                }
                $disc['id'] = $id;
                $discounts[] = $disc;
            }
        }
        $this->json_success(['discounts' => $discounts]);
    }

    /**
     * POST — Create or update a discount policy.
     * Params: id?, name, type, value, criteria, applicable_categories, applicable_titles, max_discount, active
     */
    public function save_discount()
    {
        $this->_require_role(self::FINANCE_ROLES, 'save_discount');
        $name       = trim($this->input->post('name'));
        $type       = trim($this->input->post('type'));
        $value      = floatval($this->input->post('value'));
        $criteria   = trim($this->input->post('criteria'));
        $maxDisc    = floatval($this->input->post('max_discount'));
        $active     = ($this->input->post('active') === 'false' || $this->input->post('active') === '0')
                      ? false : true;
        $discId     = trim($this->input->post('id'));

        if ($name === '') {
            $this->json_error('Discount policy name is required.');
        }
        if (!in_array($type, ['percentage', 'fixed'], true)) {
            $this->json_error('Discount type must be "percentage" or "fixed".');
        }
        if ($value <= 0) {
            $this->json_error('Discount value must be greater than zero.');
        }

        $validCriteria = ['sibling', 'early_bird', 'merit', 'staff_ward', 'custom'];
        if (!in_array($criteria, $validCriteria, true)) {
            $this->json_error('Invalid criteria. Must be one of: ' . implode(', ', $validCriteria));
        }

        // Parse applicable categories and titles (comma-separated)
        $appCats   = trim($this->input->post('applicable_categories'));
        $appTitles = trim($this->input->post('applicable_titles'));

        $catsArray = [];
        if ($appCats !== '') {
            $catsArray = array_values(array_filter(array_map('trim', explode(',', $appCats))));
        }
        $titlesArray = [];
        if ($appTitles !== '') {
            $titlesArray = array_values(array_filter(array_map('trim', explode(',', $appTitles))));
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            'name'                  => $name,
            'type'                  => $type,
            'value'                 => $value,
            'criteria'              => $criteria,
            'applicable_categories' => $catsArray,
            'applicable_titles'     => $titlesArray,
            'max_discount'          => $maxDisc,
            'active'                => $active,
        ];

        if (!empty($discId)) {
            $discId = $this->safe_path_segment($discId, 'discount_id');
            $data['updated_at'] = $now;
            $this->firebase->update("{$this->feesBase}/Discount Policies/{$discId}", $data);
            $this->json_success(['message' => 'Discount policy updated successfully.', 'id' => $discId]);
        } else {
            $data['created_at'] = $now;
            $newId = uniqid('disc_');
            $this->firebase->set("{$this->feesBase}/Discount Policies/{$newId}", $data);
            $this->json_success(['message' => 'Discount policy created successfully.', 'id' => $newId]);
        }
    }

    /**
     * POST — Delete a discount policy.
     * Params: discount_id
     */
    public function delete_discount()
    {
        $this->_require_role(self::FINANCE_ROLES, 'delete_discount');
        $discId = $this->safe_path_segment(trim($this->input->post('discount_id') ?? ''), 'discount_id');

        $existing = $this->firebase->get("{$this->feesBase}/Discount Policies/{$discId}");
        if (empty($existing)) {
            $this->json_error('Discount policy not found.');
        }

        $this->firebase->delete("{$this->feesBase}/Discount Policies", $discId);
        $this->json_success(['message' => 'Discount policy deleted successfully.']);
    }

    /**
     * POST — Fetch students eligible for a specific discount based on criteria.
     * Params: discount_id
     *
     * Criteria logic:
     *   sibling    — students sharing the same parent/guardian (same parent key in Users/Parents)
     *   early_bird — all students (manual selection expected)
     *   merit      — all students (manual selection expected)
     *   staff_ward — students whose parent is in the Teachers node
     *   custom     — all students (manual selection expected)
     */
    public function fetch_eligible_students()
    {
        $this->_require_role(self::FINANCE_ROLES, 'fetch_eligible');
        $discId = $this->safe_path_segment(trim($this->input->post('discount_id') ?? ''), 'discount_id');

        $policy = $this->firebase->get("{$this->feesBase}/Discount Policies/{$discId}");
        if (!is_array($policy)) {
            $this->json_error('Discount policy not found.');
        }

        $criteria = isset($policy['criteria']) ? $policy['criteria'] : 'custom';
        $students = [];
        $classSections = $this->_getAllClassSections();

        foreach ($classSections as $cs) {
            $classNode   = $cs['class'];
            $sectionNode = $cs['section'];
            $listPath    = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/List";
            $list        = $this->firebase->get($listPath);

            if (!is_array($list)) {
                continue;
            }

            foreach ($list as $userId => $name) {
                $students[] = [
                    'user_id' => $userId,
                    'name'    => is_string($name) ? $name : (string)$userId,
                    'class'   => $classNode,
                    'section' => $sectionNode,
                ];
            }
        }

        // For sibling criteria, group by parent and only include those with 2+ children
        if ($criteria === 'sibling') {
            $parentStudents = [];
            $schoolId = $this->school_id;
            $parentData = $this->firebase->get("Users/Parents/{$schoolId}");

            if (is_array($parentData)) {
                // Build parent -> children map using Father_name or parent key
                $fatherMap = [];
                foreach ($parentData as $uid => $profile) {
                    if (!is_array($profile)) {
                        continue;
                    }
                    $fatherName = isset($profile['Father_name']) ? trim($profile['Father_name']) : '';
                    if ($fatherName !== '') {
                        $fatherMap[$fatherName][] = $uid;
                    }
                }

                // Keep only siblings (2+ children with same father)
                $siblingIds = [];
                foreach ($fatherMap as $father => $uids) {
                    if (count($uids) >= 2) {
                        foreach ($uids as $uid) {
                            $siblingIds[$uid] = true;
                        }
                    }
                }

                $students = array_filter($students, function ($s) use ($siblingIds) {
                    return isset($siblingIds[$s['user_id']]);
                });
                $students = array_values($students);
            }
        } elseif ($criteria === 'staff_ward') {
            // Filter to students whose parent is a teacher
            $teacherPath = "{$this->sessionRoot}/Teachers";
            $teachers    = $this->firebase->get($teacherPath);
            $teacherIds  = is_array($teachers) ? array_keys($teachers) : [];

            if (!empty($teacherIds)) {
                $schoolId    = $this->school_id;
                $parentData  = $this->firebase->get("Users/Parents/{$schoolId}");
                $staffWardIds = [];

                if (is_array($parentData)) {
                    foreach ($parentData as $uid => $profile) {
                        if (!is_array($profile)) {
                            continue;
                        }
                        // Check if any parent field matches a teacher
                        $parentPhone = isset($profile['Father_phone']) ? trim($profile['Father_phone']) : '';
                        $motherPhone = isset($profile['Mother_phone']) ? trim($profile['Mother_phone']) : '';
                        foreach ($teacherIds as $tid) {
                            $teacher = is_array($teachers[$tid]) ? $teachers[$tid] : [];
                            $tPhone  = isset($teacher['Phone']) ? trim($teacher['Phone']) : '';
                            if ($tPhone !== '' && ($tPhone === $parentPhone || $tPhone === $motherPhone)) {
                                $staffWardIds[$uid] = true;
                            }
                        }
                    }
                }

                $students = array_filter($students, function ($s) use ($staffWardIds) {
                    return isset($staffWardIds[$s['user_id']]);
                });
                $students = array_values($students);
            } else {
                $students = [];
            }
        }
        // For early_bird, merit, custom — return all students for manual selection

        $this->json_success([
            'students' => $students,
            'criteria' => $criteria,
            'policy'   => [
                'name'  => isset($policy['name']) ? $policy['name'] : '',
                'type'  => isset($policy['type']) ? $policy['type'] : '',
                'value' => isset($policy['value']) ? $policy['value'] : 0,
            ],
        ]);
    }

    /**
     * POST — Apply a discount to selected students.
     * Params: discount_id, student_ids[] (array of user IDs with class/section info as JSON)
     *
     * Each student_ids[] element is JSON: {"user_id":"...","class":"Class 9th","section":"Section A"}
     */
    public function apply_discount()
    {
        $this->_require_role(self::FINANCE_ROLES, 'apply_discount');
        $discId     = $this->safe_path_segment(trim($this->input->post('discount_id') ?? ''), 'discount_id');
        $studentRaw = $this->input->post('student_ids');

        if (empty($studentRaw) || !is_array($studentRaw)) {
            $this->json_error('No students selected.');
        }

        $policy = $this->firebase->get("{$this->feesBase}/Discount Policies/{$discId}");
        if (!is_array($policy)) {
            $this->json_error('Discount policy not found.');
        }

        $discType  = isset($policy['type']) ? $policy['type'] : 'fixed';
        $discValue = isset($policy['value']) ? floatval($policy['value']) : 0;
        $discName  = isset($policy['name']) ? $policy['name'] : 'Discount';
        $maxDisc   = isset($policy['max_discount']) ? floatval($policy['max_discount']) : 0;
        $applied   = 0;
        $errors    = [];

        foreach ($studentRaw as $entry) {
            $student = is_string($entry) ? json_decode($entry, true) : $entry;
            if (!is_array($student) || empty($student['user_id'])) {
                continue;
            }

            $userId  = $student['user_id'];
            $class   = isset($student['class']) ? $student['class'] : '';
            $section = isset($student['section']) ? $student['section'] : '';

            if ($class === '' || $section === '') {
                $errors[] = "Missing class/section for student {$userId}";
                continue;
            }

            // Sanitize path segments from user-posted JSON
            list($class, $section) = $this->_normalizeClassSection($class, $section);
            $safeUserId = $this->safe_path_segment($userId, 'student_id');

            // Read existing discount data for this student
            $discPath    = "{$this->sessionRoot}/{$class}/{$section}/Students/{$safeUserId}/Discount";
            $existing    = $this->firebase->get($discPath);
            $existingAmt = 0;
            if (is_array($existing)) {
                $existingAmt = isset($existing['totalDiscount']) ? floatval($existing['totalDiscount']) : 0;
            }

            // Calculate discount amount
            $discountAmount = $discValue;
            if ($discType === 'percentage') {
                // For percentage, we need the student's total fee. Read from class fees.
                $feePath   = "{$this->feesBase}/Classes Fees/{$class}/{$section}";
                $classFees = $this->firebase->get($feePath);
                $totalFee  = 0;
                if (is_array($classFees)) {
                    foreach ($classFees as $month => $fees) {
                        if (is_array($fees)) {
                            foreach ($fees as $title => $amt) {
                                $totalFee += floatval($amt);
                            }
                        }
                    }
                }
                $discountAmount = round(($totalFee * $discValue) / 100, 2);
            }

            // Apply max_discount cap if set
            if ($maxDisc > 0 && $discountAmount > $maxDisc) {
                $discountAmount = $maxDisc;
            }

            $newTotal = $existingAmt + $discountAmount;

            // Write to student's Discount node - maintain history
            $historyKey = $discId . '_' . date('Ymd_His');
            $updateData = [
                'OnDemandDiscount' => $discountAmount,
                'totalDiscount'    => $newTotal,
                'last_policy_id'   => $discId,
                'last_policy_name' => $discName,
                'applied_at'       => date('Y-m-d H:i:s'),
            ];

            // Store in Applied sub-node for audit trail
            $appliedData = [
                'policy_id'   => $discId,
                'policy_name' => $discName,
                'amount'      => $discountAmount,
                'type'        => $discType,
                'applied_at'  => date('Y-m-d H:i:s'),
                'applied_by'  => $this->admin_name,
            ];

            $this->firebase->update($discPath, $updateData);
            $this->firebase->set("{$discPath}/Applied/{$historyKey}", $appliedData);
            $applied++;
        }

        $msg = "Discount applied to {$applied} student(s).";
        if (!empty($errors)) {
            $msg .= ' Errors: ' . implode('; ', $errors);
        }

        $this->json_success(['message' => $msg, 'applied' => $applied]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  SCHOLARSHIP MANAGEMENT (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch all scholarships.
     */
    public function fetch_scholarships()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_scholarships');
        $raw = $this->firebase->get("{$this->feesBase}/Scholarships");
        $allAwards = $this->firebase->get("{$this->feesBase}/Scholarship Awards");
        $scholarships = [];

        // Pre-compute award counts per scholarship
        $awardCounts = [];
        if (is_array($allAwards)) {
            foreach ($allAwards as $award) {
                if (is_array($award)
                    && isset($award['scholarship_id'])
                    && isset($award['status'])
                    && $award['status'] === 'active'
                ) {
                    $sid = $award['scholarship_id'];
                    if (!isset($awardCounts[$sid])) $awardCounts[$sid] = 0;
                    $awardCounts[$sid]++;
                }
            }
        }

        if (is_array($raw)) {
            foreach ($raw as $id => $schol) {
                if (!is_array($schol)) {
                    continue;
                }
                $schol['id'] = $id;
                $schol['current_awards'] = isset($awardCounts[$id]) ? $awardCounts[$id] : 0;
                $scholarships[] = $schol;
            }
        }
        $this->json_success(['scholarships' => $scholarships]);
    }

    /**
     * POST — Create or update a scholarship.
     * Params: id?, name, type, value, criteria, max_beneficiaries, active
     */
    public function save_scholarship()
    {
        $this->_require_role(self::FINANCE_ROLES, 'save_scholarship');
        $name    = trim($this->input->post('name'));
        $type    = trim($this->input->post('type'));
        $value   = floatval($this->input->post('value'));
        $criteria       = trim($this->input->post('criteria'));
        $maxBeneficiary = (int)$this->input->post('max_beneficiaries');
        $active  = ($this->input->post('active') === 'false' || $this->input->post('active') === '0')
                   ? false : true;
        $scholId = trim($this->input->post('id'));

        if ($name === '') {
            $this->json_error('Scholarship name is required.');
        }
        if (!in_array($type, ['percentage', 'fixed'], true)) {
            $this->json_error('Scholarship type must be "percentage" or "fixed".');
        }
        if ($value <= 0) {
            $this->json_error('Scholarship value must be greater than zero.');
        }

        $now = date('Y-m-d H:i:s');

        $data = [
            'name'              => $name,
            'type'              => $type,
            'value'             => $value,
            'criteria'          => $criteria,
            'max_beneficiaries' => $maxBeneficiary,
            'academic_year'     => $this->session_year,
            'active'            => $active,
        ];

        if (!empty($scholId)) {
            $scholId = $this->safe_path_segment($scholId, 'scholarship_id');
            $data['updated_at'] = $now;
            $this->firebase->update("{$this->feesBase}/Scholarships/{$scholId}", $data);
            $this->json_success(['message' => 'Scholarship updated successfully.', 'id' => $scholId]);
        } else {
            $data['created_at'] = $now;
            $newId = uniqid('schol_');
            $this->firebase->set("{$this->feesBase}/Scholarships/{$newId}", $data);
            $this->json_success(['message' => 'Scholarship created successfully.', 'id' => $newId]);
        }
    }

    /**
     * POST — Delete a scholarship.
     * Params: scholarship_id
     */
    public function delete_scholarship()
    {
        $this->_require_role(self::FINANCE_ROLES, 'delete_scholarship');
        $scholId = $this->safe_path_segment(trim($this->input->post('scholarship_id') ?? ''), 'scholarship_id');

        $existing = $this->firebase->get("{$this->feesBase}/Scholarships/{$scholId}");
        if (empty($existing)) {
            $this->json_error('Scholarship not found.');
        }

        // Check for active awards
        $awards = $this->firebase->get("{$this->feesBase}/Scholarship Awards");
        if (is_array($awards)) {
            foreach ($awards as $award) {
                if (is_array($award)
                    && isset($award['scholarship_id'])
                    && $award['scholarship_id'] === $scholId
                    && isset($award['status'])
                    && $award['status'] === 'active'
                ) {
                    $this->json_error('Cannot delete scholarship with active awards. Revoke all awards first.');
                }
            }
        }

        $this->firebase->delete("{$this->feesBase}/Scholarships", $scholId);
        $this->json_success(['message' => 'Scholarship deleted successfully.']);
    }

    /**
     * GET — Fetch scholarship awards, optionally filtered by scholarship_id.
     * Query param: scholarship_id (optional)
     */
    public function fetch_awards()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_awards');
        $scholId = trim($this->input->get('scholarship_id'));
        $raw     = $this->firebase->get("{$this->feesBase}/Scholarship Awards");
        $awards  = [];

        if (is_array($raw)) {
            foreach ($raw as $id => $award) {
                if (!is_array($award)) {
                    continue;
                }
                if ($scholId !== '' && (!isset($award['scholarship_id']) || $award['scholarship_id'] !== $scholId)) {
                    continue;
                }
                $award['id'] = $id;
                $awards[] = $award;
            }
        }

        $this->json_success(['awards' => $awards]);
    }

    /**
     * POST — Award a scholarship to a student.
     * Params: scholarship_id, student_id, student_name, class, section, amount
     */
    public function award_scholarship()
    {
        $this->_require_role(self::FINANCE_ROLES, 'award_scholarship');
        $scholId     = $this->safe_path_segment(trim($this->input->post('scholarship_id') ?? ''), 'scholarship_id');
        $studentId   = $this->safe_path_segment(trim($this->input->post('student_id') ?? ''), 'student_id');
        $studentName = trim($this->input->post('student_name'));
        $class       = trim($this->input->post('class'));
        $section     = trim($this->input->post('section'));
        $amount      = floatval($this->input->post('amount'));

        // Validate scholarship exists and is active
        $scholarship = $this->firebase->get("{$this->feesBase}/Scholarships/{$scholId}");
        if (!is_array($scholarship)) {
            $this->json_error('Scholarship not found.');
        }
        if (isset($scholarship['active']) && $scholarship['active'] === false) {
            $this->json_error('This scholarship is not active.');
        }

        // Check max beneficiaries
        $maxBen = isset($scholarship['max_beneficiaries']) ? (int)$scholarship['max_beneficiaries'] : 0;
        if ($maxBen > 0) {
            $existingAwards = $this->firebase->get("{$this->feesBase}/Scholarship Awards");
            $currentCount = 0;
            if (is_array($existingAwards)) {
                foreach ($existingAwards as $aw) {
                    if (is_array($aw)
                        && isset($aw['scholarship_id'])
                        && $aw['scholarship_id'] === $scholId
                        && isset($aw['status'])
                        && $aw['status'] === 'active'
                    ) {
                        $currentCount++;
                    }
                }
            }
            if ($currentCount >= $maxBen) {
                $this->json_error("Maximum beneficiaries ({$maxBen}) reached for this scholarship.");
            }
        }

        // Calculate amount if not provided (use scholarship value)
        if ($amount <= 0) {
            $scholType  = isset($scholarship['type']) ? $scholarship['type'] : 'fixed';
            $scholValue = isset($scholarship['value']) ? floatval($scholarship['value']) : 0;

            if ($scholType === 'percentage') {
                // Get total fees for the student's class
                list($classNode, $sectionNode) = $this->_normalizeClassSection($class, $section);
                $feePath   = "{$this->feesBase}/Classes Fees/{$classNode}/{$sectionNode}";
                $classFees = $this->firebase->get($feePath);
                $totalFee  = 0;
                if (is_array($classFees)) {
                    foreach ($classFees as $month => $fees) {
                        if (is_array($fees)) {
                            foreach ($fees as $title => $amt) {
                                $totalFee += floatval($amt);
                            }
                        }
                    }
                }
                $amount = round(($totalFee * $scholValue) / 100, 2);
            } else {
                $amount = $scholValue;
            }
        }

        $scholName = isset($scholarship['name']) ? $scholarship['name'] : 'Scholarship';
        $now       = date('Y-m-d H:i:s');

        // Create award record
        $awardData = [
            'scholarship_id'   => $scholId,
            'scholarship_name' => $scholName,
            'student_id'       => $studentId,
            'student_name'     => $studentName,
            'class'            => $class,
            'section'          => $section,
            'amount'           => $amount,
            'awarded_date'     => $now,
            'status'           => 'active',
            'awarded_by'       => $this->admin_name,
        ];

        $awardId = uniqid('award_');
        $this->firebase->set("{$this->feesBase}/Scholarship Awards/{$awardId}", $awardData);

        // Update student's Discount node with scholarship info
        list($classNode, $sectionNode) = $this->_normalizeClassSection($class, $section);
        $discPath = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$studentId}/Discount";

        $existing = $this->firebase->get($discPath);
        $existingTotal = 0;
        $existingSchol = 0;
        if (is_array($existing)) {
            $existingTotal = isset($existing['totalDiscount']) ? floatval($existing['totalDiscount']) : 0;
            $existingSchol = isset($existing['ScholarshipDiscount']) ? floatval($existing['ScholarshipDiscount']) : 0;
        }

        $scholarshipUpdate = [
            'ScholarshipDiscount' => $existingSchol + $amount,
            'totalDiscount'       => $existingTotal + $amount,
        ];
        $this->firebase->update($discPath, $scholarshipUpdate);

        // Store individual scholarship record under Applied
        $this->firebase->set("{$discPath}/Scholarships/{$awardId}", [
            'scholarship_id'   => $scholId,
            'scholarship_name' => $scholName,
            'amount'           => $amount,
            'awarded_date'     => $now,
        ]);

        $this->json_success([
            'message'  => "Scholarship awarded to {$studentName}.",
            'award_id' => $awardId,
            'amount'   => $amount,
        ]);
    }

    /**
     * POST — Revoke a scholarship award.
     * Params: award_id
     */
    public function revoke_scholarship()
    {
        $this->_require_role(self::FINANCE_ROLES, 'revoke_scholarship');
        $awardId = $this->safe_path_segment(trim($this->input->post('award_id') ?? ''), 'award_id');

        $award = $this->firebase->get("{$this->feesBase}/Scholarship Awards/{$awardId}");
        if (!is_array($award)) {
            $this->json_error('Award not found.');
        }
        if (isset($award['status']) && $award['status'] === 'revoked') {
            $this->json_error('This award has already been revoked.');
        }

        $now = date('Y-m-d H:i:s');

        // Update award status
        $this->firebase->update("{$this->feesBase}/Scholarship Awards/{$awardId}", [
            'status'      => 'revoked',
            'revoked_date' => $now,
            'revoked_by'   => $this->admin_name,
        ]);

        // Remove scholarship discount from student's Discount node
        $class   = isset($award['class']) ? $award['class'] : '';
        $section = isset($award['section']) ? $award['section'] : '';
        $userId  = isset($award['student_id']) ? $award['student_id'] : '';
        $amount  = isset($award['amount']) ? floatval($award['amount']) : 0;

        if ($class !== '' && $section !== '' && $userId !== '') {
            list($classNode, $sectionNode) = $this->_normalizeClassSection($class, $section);
            $discPath = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$userId}/Discount";
            $existing = $this->firebase->get($discPath);

            if (is_array($existing)) {
                $totalDisc = isset($existing['totalDiscount']) ? floatval($existing['totalDiscount']) : 0;
                $scholDisc = isset($existing['ScholarshipDiscount']) ? floatval($existing['ScholarshipDiscount']) : 0;

                $newScholDisc = max(0, $scholDisc - $amount);
                $newTotal     = max(0, $totalDisc - $amount);

                $this->firebase->update($discPath, [
                    'ScholarshipDiscount' => $newScholDisc,
                    'totalDiscount'       => $newTotal,
                ]);

                // Remove individual scholarship record
                $this->firebase->delete("{$discPath}/Scholarships", $awardId);
            }
        }

        $this->json_success(['message' => 'Scholarship award revoked successfully.']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  REFUND SYSTEM (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch refunds, optionally filtered by status.
     * Query param: status (optional — pending|approved|processed|rejected)
     */
    public function fetch_refunds()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_refunds');
        $filterStatus = trim($this->input->get('status'));
        $raw = $this->firebase->get("{$this->feesBase}/Refunds");
        $refunds = [];

        if (is_array($raw)) {
            foreach ($raw as $id => $ref) {
                if (!is_array($ref)) {
                    continue;
                }
                if ($filterStatus !== '' && (!isset($ref['status']) || $ref['status'] !== $filterStatus)) {
                    continue;
                }
                $ref['id'] = $id;
                $refunds[] = $ref;
            }
        }

        // Sort by requested_date descending
        usort($refunds, function ($a, $b) {
            $da = isset($a['requested_date']) ? $a['requested_date'] : '';
            $db = isset($b['requested_date']) ? $b['requested_date'] : '';
            return strcmp($db, $da);
        });

        // Compute stats
        $stats = ['total' => count($refunds), 'pending' => 0, 'approved' => 0, 'processed' => 0, 'rejected' => 0];
        foreach ($refunds as &$ref) {
            $s = isset($ref['status']) ? $ref['status'] : '';
            if (isset($stats[$s])) $stats[$s]++;
            // Add combined class_section and date alias for view compatibility
            $ref['class_section'] = trim((isset($ref['class']) ? $ref['class'] : '') . ' / ' . (isset($ref['section']) ? $ref['section'] : ''));
            $ref['date'] = isset($ref['requested_date']) ? $ref['requested_date'] : '';
        }
        unset($ref);

        $this->json_success(['refunds' => $refunds, 'stats' => $stats, 'success' => true]);
    }

    /**
     * POST — Create a refund request.
     * Params: student_id, student_name, class, section, amount, fee_title, receipt_no, reason
     */
    public function create_refund()
    {
        $this->_require_role(self::ADMIN_ROLES, 'create_refund');
        $studentId   = trim($this->input->post('student_id'));
        $studentName = trim($this->input->post('student_name'));
        $class       = trim($this->input->post('class'));
        $section     = trim($this->input->post('section'));
        $amount      = floatval($this->input->post('amount'));
        $feeTitle    = trim($this->input->post('fee_title'));
        $receiptNo   = trim($this->input->post('receipt_no'));
        $reason      = trim($this->input->post('reason'));

        if ($studentId === '' || $studentName === '') {
            $this->json_error('Student information is required.');
        }

        // Sanitize path segments
        $studentId = $this->safe_path_segment($studentId, 'student_id');
        if ($receiptNo !== '') {
            $receiptNo = $this->safe_path_segment($receiptNo, 'receipt_no');
        }
        if ($amount <= 0) {
            $this->json_error('Refund amount must be greater than zero.');
        }
        if ($feeTitle === '') {
            $this->json_error('Fee title is required.');
        }
        if ($reason === '') {
            $this->json_error('Refund reason is required.');
        }

        // Handle combined class_section field from view
        $classSection = trim($this->input->post('class_section'));
        if ($classSection !== '' && $class === '') {
            // Parse "Class 9th / Section A" or "Class 9th"
            $parts = preg_split('/[\/,]/', $classSection, 2);
            $class = trim($parts[0]);
            $section = isset($parts[1]) ? trim($parts[1]) : '';
        }

        // Verify the receipt exists if provided
        if ($receiptNo !== '') {
            // Receipt stored in student's Fees Record
            list($classNode, $sectionNode) = $this->_normalizeClassSection($class, $section);
            $recordPath = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$studentId}/Fees Record/{$receiptNo}";
            $record     = $this->firebase->get($recordPath);
            if (empty($record)) {
                $this->json_error("Receipt '{$receiptNo}' not found for this student.");
            }
            // Validate refund amount doesn't exceed original payment
            $originalAmount = 0;
            if (is_array($record)) {
                if (isset($record['Amount'])) {
                    $originalAmount = floatval(str_replace(',', '', $record['Amount']));
                } elseif (isset($record['amount'])) {
                    $originalAmount = floatval($record['amount']);
                }
            }
            if ($originalAmount > 0 && $amount > $originalAmount) {
                $this->json_error("Refund amount ({$amount}) exceeds original payment amount ({$originalAmount}).");
            }
        }

        $now = date('Y-m-d H:i:s');

        $refundData = [
            'student_id'     => $studentId,
            'student_name'   => $studentName,
            'class'          => $class,
            'section'        => $section,
            'amount'         => $amount,
            'fee_title'      => $feeTitle,
            'receipt_no'     => $receiptNo,
            'reason'         => $reason,
            'status'         => 'pending',
            'requested_date' => $now,
            'reviewed_date'  => '',
            'processed_date' => '',
            'reviewed_by'    => '',
            'processed_by'   => '',
            'refund_mode'    => '',
            'remarks'        => '',
        ];

        $refId = uniqid('ref_');
        $this->firebase->set("{$this->feesBase}/Refunds/{$refId}", $refundData);

        $this->json_success([
            'message'   => 'Refund request created successfully.',
            'refund_id' => $refId,
            'success'   => true,
        ]);
    }

    /**
     * POST — Update refund status (approve/reject).
     * Params: refund_id, status (approved|rejected), remarks?
     */
    public function update_refund_status()
    {
        $this->_require_role(self::ADMIN_ROLES, 'update_refund_status');
        $refId   = $this->safe_path_segment(trim($this->input->post('refund_id') ?? ''), 'refund_id');
        $status  = trim($this->input->post('status'));
        $remarks = trim($this->input->post('remarks'));

        $validStatuses = ['approved', 'rejected'];
        if (!in_array($status, $validStatuses, true)) {
            $this->json_error('Status must be "approved" or "rejected".');
        }

        $existing = $this->firebase->get("{$this->feesBase}/Refunds/{$refId}");
        if (!is_array($existing)) {
            $this->json_error('Refund not found.');
        }

        $currentStatus = isset($existing['status']) ? $existing['status'] : '';
        if ($currentStatus !== 'pending') {
            $this->json_error("Cannot change status. Current status is '{$currentStatus}'.");
        }

        $now = date('Y-m-d H:i:s');

        $updateData = [
            'status'       => $status,
            'reviewed_date' => $now,
            'reviewed_by'   => $this->admin_name,
        ];
        if ($remarks !== '') {
            $updateData['remarks'] = $remarks;
        }

        $this->firebase->update("{$this->feesBase}/Refunds/{$refId}", $updateData);

        $this->json_success(['message' => "Refund {$status} successfully.", 'success' => true]);
    }

    /**
     * POST — Approve a refund (convenience wrapper).
     */
    public function approve_refund()
    {
        $this->_require_role(self::ADMIN_ROLES, 'approve_refund');
        $_POST['status'] = 'approved';
        $this->update_refund_status();
    }

    /**
     * POST — Reject a refund (convenience wrapper).
     */
    public function reject_refund()
    {
        $this->_require_role(self::ADMIN_ROLES, 'reject_refund');
        $_POST['status'] = 'rejected';
        $this->update_refund_status();
    }

    /**
     * POST — Process an approved refund (mark as processed, create audit voucher).
     * Params: refund_id, refund_mode (cash|bank_transfer|cheque|online)
     */
    public function process_refund()
    {
        $this->_require_role(self::ADMIN_ROLES, 'process_refund');
        $refId      = $this->safe_path_segment(trim($this->input->post('refund_id') ?? ''), 'refund_id');
        $refundMode = trim($this->input->post('refund_mode'));

        $validModes = ['cash', 'bank_transfer', 'cheque', 'online'];
        if (!in_array($refundMode, $validModes, true)) {
            $this->json_error('Invalid refund mode. Must be one of: ' . implode(', ', $validModes));
        }

        $refund = $this->firebase->get("{$this->feesBase}/Refunds/{$refId}");
        if (!is_array($refund)) {
            $this->json_error('Refund not found.');
        }

        $currentStatus = isset($refund['status']) ? $refund['status'] : '';
        if ($currentStatus !== 'approved') {
            $this->json_error("Only approved refunds can be processed. Current status: '{$currentStatus}'.");
        }

        $now    = date('Y-m-d H:i:s');
        $today  = date('Y-m-d');
        $amount = isset($refund['amount']) ? floatval($refund['amount']) : 0;

        // Update refund status
        $this->firebase->update("{$this->feesBase}/Refunds/{$refId}", [
            'status'         => 'processed',
            'processed_date' => $now,
            'processed_by'   => $this->admin_name,
            'refund_mode'    => $refundMode,
        ]);

        // Create a negative voucher entry for audit trail
        $voucherPath = "{$this->sessionRoot}/Accounts/Vouchers/{$today}";
        $receiptKey  = 'REFUND_' . strtoupper(substr($refId, 4)); // Remove 'ref_' prefix

        $voucherData = [
            'type'        => 'refund',
            'student_id'  => isset($refund['student_id']) ? $refund['student_id'] : '',
            'student_name' => isset($refund['student_name']) ? $refund['student_name'] : '',
            'class'       => isset($refund['class']) ? $refund['class'] : '',
            'section'     => isset($refund['section']) ? $refund['section'] : '',
            'fee_title'   => isset($refund['fee_title']) ? $refund['fee_title'] : '',
            'amount'      => -$amount,
            'refund_mode' => $refundMode,
            'receipt_no'  => isset($refund['receipt_no']) ? $refund['receipt_no'] : '',
            'refund_id'   => $refId,
            'reason'      => isset($refund['reason']) ? $refund['reason'] : '',
            'processed_by' => $this->admin_name,
            'timestamp'    => $now,
        ];

        $this->firebase->set("{$voucherPath}/{$receiptKey}", $voucherData);

        // ── F-10: Reverse student paid flags for refunded months ──
        $studentId = isset($refund['student_id']) ? $refund['student_id'] : '';
        $origReceiptNo = isset($refund['receipt_no']) ? $refund['receipt_no'] : '';
        $refClass   = isset($refund['class']) ? $refund['class'] : '';
        $refSection = isset($refund['section']) ? $refund['section'] : '';

        if ($studentId !== '' && $origReceiptNo !== '' && $refClass !== '' && $refSection !== '') {
            try {
                list($classNode, $sectionNode) = $this->_normalizeClassSection($refClass, $refSection);
                $studentBase = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$studentId}";

                // Look up the original receipt to find which months were paid
                $origReceipt = $this->firebase->get("{$studentBase}/Fees Record/{$origReceiptNo}");
                if (is_array($origReceipt)) {
                    // Read current Month Fee flags
                    $monthFee = $this->firebase->get("{$studentBase}/Month Fee");
                    $monthFee = is_array($monthFee) ? $monthFee : [];

                    // The receipt Amount tells us total paid; reset all months that were marked paid
                    // by this receipt. We check which months are currently paid=1 and reset them.
                    // Since receipts don't store individual months, we reset months that equal the
                    // fee structure amount. For safety, mark all currently-paid months as candidates
                    // and reset up to the refund amount.
                    $refundAmt = $amount;
                    $feesPath = "{$this->sessionRoot}/Accounts/Fees/Classes Fees/{$classNode}/{$sectionNode}";
                    $classFees = $this->firebase->get($feesPath);
                    $classFees = is_array($classFees) ? $classFees : [];

                    // Build per-month cost from fee structure
                    $monthOrder = ['April','May','June','July','August','September',
                                   'October','November','December','January','February','March','Yearly Fees'];

                    // Reverse iterate paid months (latest first) and un-mark until refund is consumed
                    $reversedMonths = array_reverse($monthOrder);
                    foreach ($reversedMonths as $m) {
                        if ($refundAmt <= 0) break;
                        if (!isset($monthFee[$m]) || (int)$monthFee[$m] !== 1) continue;

                        // Calculate this month's fee total from structure
                        $mTotal = 0;
                        if (is_array($classFees)) {
                            foreach ($classFees as $title => $val) {
                                if (is_array($val) && isset($val[$m])) {
                                    $mTotal += floatval(str_replace(',', '', $val[$m]));
                                }
                            }
                        }
                        if ($mTotal <= 0) $mTotal = $refundAmt; // fallback: consume remaining

                        if ($refundAmt >= $mTotal) {
                            $this->firebase->set("{$studentBase}/Month Fee/{$m}", 0);
                            $refundAmt -= $mTotal;
                        }
                    }

                    // Reduce Oversubmittedfees if any remaining refund amount
                    if ($refundAmt > 0.005) {
                        $overSub = floatval($this->firebase->get("{$studentBase}/Oversubmittedfees") ?? 0);
                        $newOver = max(0, $overSub - $refundAmt);
                        $this->firebase->set("{$studentBase}/Oversubmittedfees", round($newOver, 2));
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Refund month-fee reversal failed: ' . $e->getMessage());
            }
        }

        // ── F-11: Reverse legacy Account_book entries ──
        try {
            $origDate = '';
            if ($studentId !== '' && $origReceiptNo !== '' && isset($studentBase)) {
                $origReceipt = $this->firebase->get("{$studentBase}/Fees Record/{$origReceiptNo}");
                if (is_array($origReceipt) && isset($origReceipt['Date'])) {
                    $origDate = $origReceipt['Date'];
                }
            }

            // Use original receipt date for reversal if available, otherwise use today
            if ($origDate !== '') {
                $dateObj = DateTime::createFromFormat('d-m-Y', $origDate);
            } else {
                $dateObj = new DateTime();
            }
            $abMonth = $dateObj ? $dateObj->format('F') : date('F');
            $abDay   = $dateObj ? $dateObj->format('d') : date('d');

            $ab = "{$this->sessionRoot}/Accounts/Account_book";

            // Subtract refunded amount from Fees ledger
            $feeLedgerPath = "{$ab}/Fees/{$abMonth}/{$abDay}/R";
            $curFees = floatval($this->firebase->get($feeLedgerPath) ?? 0);
            $this->firebase->set($feeLedgerPath, max(0, $curFees - $amount));

            // Add refund entry to Refunds ledger
            $refundLedgerPath = "{$ab}/Refunds/{$abMonth}/{$abDay}/R";
            $curRefunds = floatval($this->firebase->get($refundLedgerPath) ?? 0);
            $this->firebase->set($refundLedgerPath, $curRefunds + $amount);
        } catch (\Exception $e) {
            log_message('error', 'Refund Account_book reversal failed: ' . $e->getMessage());
        }

        // ── Accounting integration via Operations_accounting library
        try {
            $this->load->library('Operations_accounting', null, 'ops_acct');
            $this->ops_acct->init(
                $this->firebase, $this->school_name, $this->session_year, $this->admin_id, $this
            );
            $this->ops_acct->create_refund_journal([
                'student_name' => isset($refund['student_name']) ? $refund['student_name'] : '',
                'student_id'   => isset($refund['student_id']) ? $refund['student_id'] : '',
                'class'        => isset($refund['class']) ? $refund['class'] : '',
                'amount'       => $amount,
                'refund_mode'  => $refundMode,
                'refund_id'    => $refId,
                'receipt_no'   => isset($refund['receipt_no']) ? $refund['receipt_no'] : '',
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Accounting integration failed in process_refund: ' . $e->getMessage());
        }

        $this->json_success([
            'message'     => 'Refund processed successfully. Audit voucher created.',
            'voucher_key' => $receiptKey,
            'success'     => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEE REMINDERS (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch reminder settings.
     */
    public function get_reminder_settings()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_reminder_settings');
        $settings = $this->firebase->get("{$this->feesBase}/Reminder Settings");
        if (!is_array($settings)) {
            $settings = [
                'auto_remind'        => false,
                'days_before_due'    => [7, 3, 1],
                'reminder_message'   => 'Dear Parent, this is a reminder that fees for {month} amounting to Rs. {amount} are due. Please pay by {due_date}.',
                'due_day_of_month'   => 10,
                'late_fee_enabled'   => false,
                'late_fee_type'      => 'fixed',
                'late_fee_value'     => 0,
            ];
        }
        $this->json_success(['settings' => $settings]);
    }

    /**
     * POST — Save reminder settings.
     * Params: auto_remind, days_before_due, reminder_message, due_day_of_month,
     *         late_fee_enabled, late_fee_type, late_fee_value
     */
    public function save_reminder_settings()
    {
        $this->_require_role(self::FINANCE_ROLES, 'save_reminder_settings');
        $autoRemind     = ($this->input->post('auto_remind') === '1'
                          || $this->input->post('auto_remind') === 'true') ? true : false;
        $daysBeforeDue  = $this->input->post('days_before_due');
        $message        = trim($this->input->post('reminder_message'));
        $dueDay         = (int)$this->input->post('due_day_of_month');
        $lateFeeEnabled = ($this->input->post('late_fee_enabled') === '1'
                          || $this->input->post('late_fee_enabled') === 'true') ? true : false;
        $lateFeeType    = trim($this->input->post('late_fee_type'));
        $lateFeeValue   = floatval($this->input->post('late_fee_value'));

        if ($dueDay < 1 || $dueDay > 28) {
            $this->json_error('Due day of month must be between 1 and 28.');
        }

        if ($lateFeeEnabled && !in_array($lateFeeType, ['percentage', 'fixed'], true)) {
            $this->json_error('Late fee type must be "percentage" or "fixed".');
        }

        // Parse days_before_due
        $daysArray = [];
        if (is_array($daysBeforeDue)) {
            $daysArray = array_map('intval', $daysBeforeDue);
        } elseif (is_string($daysBeforeDue) && $daysBeforeDue !== '') {
            $daysArray = array_map('intval', array_filter(explode(',', $daysBeforeDue)));
        }
        $daysArray = array_values(array_filter($daysArray, function ($d) {
            return $d > 0;
        }));
        rsort($daysArray);

        $settings = [
            'auto_remind'      => $autoRemind,
            'days_before_due'  => $daysArray,
            'reminder_message' => $message,
            'due_day_of_month' => $dueDay,
            'late_fee_enabled' => $lateFeeEnabled,
            'late_fee_type'    => $lateFeeType,
            'late_fee_value'   => $lateFeeValue,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $this->firebase->set("{$this->feesBase}/Reminder Settings", $settings);

        $this->json_success(['message' => 'Reminder settings saved successfully.']);
    }

    /**
     * GET — Scan all classes to find students with unpaid fee months.
     * Returns list of students with due amounts.
     */
    public function fetch_due_students()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_due_students');
        $classSections = $this->_getAllClassSections();
        $dueStudents   = [];

        // Get current month names for reference
        $months = [
            'April', 'May', 'June', 'July', 'August', 'September',
            'October', 'November', 'December', 'January', 'February', 'March'
        ];

        // Determine months up to current month
        $currentMonth  = date('n'); // 1-12
        $monthIndex    = ($currentMonth >= 4) ? ($currentMonth - 4) : ($currentMonth + 8);
        $monthsToCheck = array_slice($months, 0, $monthIndex + 1);

        foreach ($classSections as $cs) {
            $classNode   = $cs['class'];
            $sectionNode = $cs['section'];

            // Read entire section Students node in ONE call
            $allStudentData = $this->firebase->get("{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students");
            if (!is_array($allStudentData)) continue;

            $list = isset($allStudentData['List']) && is_array($allStudentData['List'])
                ? $allStudentData['List'] : [];

            // Get class fees to calculate due amount
            $feePath   = "{$this->feesBase}/Classes Fees/{$classNode}/{$sectionNode}";
            $classFees = $this->firebase->get($feePath);

            foreach ($list as $userId => $studentName) {
                $studentData = isset($allStudentData[$userId]) && is_array($allStudentData[$userId])
                    ? $allStudentData[$userId] : [];
                $monthFee = isset($studentData['Month Fee']) && is_array($studentData['Month Fee'])
                    ? $studentData['Month Fee'] : [];

                $unpaidMonths = [];
                $totalDue     = 0;

                foreach ($monthsToCheck as $month) {
                    $paid = isset($monthFee[$month]) ? (int)$monthFee[$month] : 0;
                    if ($paid !== 1) {
                        $unpaidMonths[] = $month;

                        // Calculate due for this month
                        if (is_array($classFees) && isset($classFees[$month]) && is_array($classFees[$month])) {
                            foreach ($classFees[$month] as $title => $amt) {
                                $totalDue += floatval($amt);
                            }
                        }
                    }
                }

                if (!empty($unpaidMonths)) {
                    $dueStudents[] = [
                        'user_id'       => $userId,
                        'name'          => is_string($studentName) ? $studentName : (string)$userId,
                        'class'         => $classNode,
                        'section'       => $sectionNode,
                        'unpaid_months' => $unpaidMonths,
                        'total_due'     => $totalDue,
                    ];
                }
            }
        }

        // Sort by total_due descending
        usort($dueStudents, function ($a, $b) {
            return $b['total_due'] - $a['total_due'];
        });

        $this->json_success([
            'students'     => $dueStudents,
            'total_count'  => count($dueStudents),
            'months_checked' => $monthsToCheck,
        ]);
    }

    /**
     * POST — Send reminder to selected students.
     * Params: student_ids[] (JSON objects), month
     *
     * Since SMS/email gateway is not yet integrated, this logs the reminder
     * for future integration.
     */
    public function send_reminder()
    {
        $this->_require_role(self::FINANCE_ROLES, 'send_reminder');
        $studentRaw = $this->input->post('student_ids');
        $month      = trim($this->input->post('month'));

        if (empty($studentRaw) || !is_array($studentRaw)) {
            $this->json_error('No students selected.');
        }
        if ($month === '') {
            $this->json_error('Month is required.');
        }

        $now     = date('Y-m-d H:i:s');
        $logged  = 0;

        $batchData = [];
        foreach ($studentRaw as $entry) {
            $student = is_string($entry) ? json_decode($entry, true) : $entry;
            if (!is_array($student) || empty($student['user_id'])) {
                continue;
            }

            $logId = uniqid('rem_');
            $batchData[$logId] = [
                'student_id'   => $student['user_id'],
                'student_name' => isset($student['name']) ? $student['name'] : '',
                'class'        => isset($student['class']) ? $student['class'] : '',
                'section'      => isset($student['section']) ? $student['section'] : '',
                'month'        => $month,
                'amount_due'   => isset($student['total_due']) ? floatval($student['total_due']) : 0,
                'sent_date'    => $now,
                'type'         => 'manual',
                'status'       => 'sent',
            ];
            $logged++;
        }

        if (!empty($batchData)) {
            $this->firebase->update("{$this->feesBase}/Reminders Log", $batchData);
        }

        $this->json_success([
            'message' => "Reminder logged for {$logged} student(s). Actual SMS/email delivery will be available once the messaging gateway is integrated.",
            'logged'  => $logged,
        ]);
    }

    /**
     * GET — Fetch all reminder log entries.
     */
    public function fetch_reminder_log()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_reminder_log');
        $raw = $this->firebase->get("{$this->feesBase}/Reminders Log");
        $logs = [];

        if (is_array($raw)) {
            foreach ($raw as $id => $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $entry['id'] = $id;
                $logs[] = $entry;
            }
        }

        // Sort by sent_date descending
        usort($logs, function ($a, $b) {
            $da = isset($a['sent_date']) ? $a['sent_date'] : '';
            $db = isset($b['sent_date']) ? $b['sent_date'] : '';
            return strcmp($db, $da);
        });

        $this->json_success(['logs' => $logs, 'total' => count($logs)]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  PAYMENT GATEWAY (AJAX)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fetch gateway configuration (secrets masked).
     */
    public function get_gateway_config()
    {
        $this->_require_role(self::ADMIN_ROLES, 'get_gateway_config');
        $config = $this->firebase->get("{$this->feesBase}/Gateway Config");
        if (!is_array($config)) {
            $config = [
                'provider'       => '',
                'mode'           => 'test',
                'api_key'        => '',
                'api_secret'     => '',
                'active'         => false,
                'webhook_secret' => '',
            ];
        }

        // Mask secrets
        $masked = $config;
        if (!empty($masked['api_secret'])) {
            $len = strlen($masked['api_secret']);
            $masked['api_secret'] = $len > 4
                ? str_repeat('*', $len - 4) . substr($masked['api_secret'], -4)
                : str_repeat('*', $len);
        }
        if (!empty($masked['webhook_secret'])) {
            $len = strlen($masked['webhook_secret']);
            $masked['webhook_secret'] = $len > 4
                ? str_repeat('*', $len - 4) . substr($masked['webhook_secret'], -4)
                : str_repeat('*', $len);
        }

        $this->json_success(['config' => $masked]);
    }

    /**
     * POST — Save gateway configuration.
     * Params: provider, api_key, api_secret, mode, active, webhook_secret
     */
    public function save_gateway_config()
    {
        $this->_require_role(self::ADMIN_ROLES, 'save_gateway_config');
        $provider      = trim($this->input->post('provider'));
        $apiKey        = trim($this->input->post('api_key'));
        $apiSecret     = trim($this->input->post('api_secret'));
        $mode          = trim($this->input->post('mode'));
        $active        = ($this->input->post('active') === '1'
                         || $this->input->post('active') === 'true') ? true : false;
        $webhookSecret = trim($this->input->post('webhook_secret'));

        $validProviders = ['razorpay', 'stripe', 'paytm'];
        if ($provider !== '' && !in_array($provider, $validProviders, true)) {
            $this->json_error('Invalid provider. Must be one of: ' . implode(', ', $validProviders));
        }

        if (!in_array($mode, ['test', 'live'], true)) {
            $this->json_error('Mode must be "test" or "live".');
        }

        // If secret contains only asterisks, preserve existing value
        $existingConfig = $this->firebase->get("{$this->feesBase}/Gateway Config");
        if (preg_match('/^\*+/', $apiSecret) && is_array($existingConfig) && !empty($existingConfig['api_secret'])) {
            $apiSecret = $existingConfig['api_secret'];
        }
        if (preg_match('/^\*+/', $webhookSecret) && is_array($existingConfig) && !empty($existingConfig['webhook_secret'])) {
            $webhookSecret = $existingConfig['webhook_secret'];
        }

        $now = date('Y-m-d H:i:s');

        $configData = [
            'provider'       => $provider,
            'mode'           => $mode,
            'api_key'        => $apiKey,
            'api_secret'     => $apiSecret,
            'active'         => $active,
            'webhook_secret' => $webhookSecret,
            'updated_at'     => $now,
        ];

        // Preserve created_at if updating
        if (is_array($existingConfig) && !empty($existingConfig['created_at'])) {
            $configData['created_at'] = $existingConfig['created_at'];
        } else {
            $configData['created_at'] = $now;
        }

        $this->firebase->set("{$this->feesBase}/Gateway Config", $configData);

        $this->json_success(['message' => 'Gateway configuration saved successfully.']);
    }

    /**
     * GET — Fetch all online payment records.
     */
    public function fetch_online_payments()
    {
        $this->_require_role(self::VIEW_ROLES, 'fetch_online_payments');
        $raw = $this->firebase->get("{$this->feesBase}/Online Payments");
        $payments = [];

        if (is_array($raw)) {
            foreach ($raw as $id => $pay) {
                if (!is_array($pay)) {
                    continue;
                }
                $pay['id'] = $id;
                $payments[] = $pay;
            }
        }

        // Sort by created_at descending
        usort($payments, function ($a, $b) {
            $da = isset($a['created_at']) ? $a['created_at'] : '';
            $db = isset($b['created_at']) ? $b['created_at'] : '';
            return strcmp($db, $da);
        });

        $this->json_success(['payments' => $payments, 'total' => count($payments)]);
    }

    /**
     * POST — Create a payment order (gateway stub).
     * Params: student_id, student_name, class, section, amount, fee_months[]
     *
     * TODO: Integrate with Razorpay/Stripe SDK to create an actual order.
     * Currently creates a Firebase record to track the payment attempt.
     */
    public function create_payment_order()
    {
        $this->_require_role(self::FINANCE_ROLES, 'create_payment_order');
        $studentId   = trim($this->input->post('student_id'));
        $studentName = trim($this->input->post('student_name'));
        $class       = trim($this->input->post('class'));
        $section     = trim($this->input->post('section'));
        $amount      = floatval($this->input->post('amount'));
        $feeMonths   = $this->input->post('fee_months');

        if ($studentId === '') {
            $this->json_error('Student ID is required.');
        }
        if ($amount <= 0) {
            $this->json_error('Amount must be greater than zero.');
        }
        if (empty($feeMonths) || !is_array($feeMonths)) {
            $this->json_error('At least one fee month must be selected.');
        }

        // Check gateway is configured and active
        $gwConfig = $this->firebase->get("{$this->feesBase}/Gateway Config");
        if (!is_array($gwConfig) || empty($gwConfig['active']) || empty($gwConfig['provider'])) {
            $this->json_error('Payment gateway is not configured or not active.');
        }

        $now = date('Y-m-d H:i:s');

        // TODO: Replace with actual gateway API call
        // Example for Razorpay:
        //   $api = new Razorpay\Api($gwConfig['api_key'], $gwConfig['api_secret']);
        //   $order = $api->order->create([
        //       'amount'   => $amount * 100, // in paise
        //       'currency' => 'INR',
        //       'receipt'  => $payId,
        //   ]);
        //   $gatewayOrderId = $order->id;

        $gatewayOrderId = 'order_' . strtoupper(uniqid());

        $paymentData = [
            'student_id'         => $studentId,
            'student_name'       => $studentName,
            'class'              => $class,
            'section'            => $section,
            'amount'             => $amount,
            'gateway_order_id'   => $gatewayOrderId,
            'gateway_payment_id' => '',
            'status'             => 'created',
            'fee_months'         => $feeMonths,
            'provider'           => $gwConfig['provider'],
            'mode'               => isset($gwConfig['mode']) ? $gwConfig['mode'] : 'test',
            'created_at'         => $now,
            'paid_at'            => '',
        ];

        $payId = uniqid('pay_');
        $this->firebase->set("{$this->feesBase}/Online Payments/{$payId}", $paymentData);

        // Write lookup index for O(1) verification
        $this->firebase->set("{$this->feesBase}/Online Payments Index/{$gatewayOrderId}", $payId);

        $this->json_success([
            'message'          => 'Payment order created.',
            'payment_id'       => $payId,
            'gateway_order_id' => $gatewayOrderId,
            'amount'           => $amount,
            'provider'         => $gwConfig['provider'],
            'api_key'          => $gwConfig['api_key'], // Public key for client-side SDK
            'mode'             => isset($gwConfig['mode']) ? $gwConfig['mode'] : 'test',
        ]);
    }

    /**
     * POST — Verify a payment after gateway callback.
     * Params: gateway_order_id, gateway_payment_id, signature
     *
     * TODO: Integrate with actual gateway signature verification.
     * Currently marks the payment as paid in Firebase.
     */
    public function verify_payment()
    {
        $this->_require_role(self::FINANCE_ROLES, 'verify_payment');
        $gatewayOrderId   = $this->safe_path_segment(trim($this->input->post('gateway_order_id') ?? ''), 'gateway_order_id');
        $gatewayPaymentId = trim($this->input->post('gateway_payment_id'));
        $signature        = trim($this->input->post('signature'));

        if ($gatewayPaymentId === '') {
            $this->json_error('Gateway payment ID is required.');
        }

        // Look up payment by gateway order ID using index
        $payId = $this->firebase->get("{$this->feesBase}/Online Payments Index/{$gatewayOrderId}");
        if (empty($payId)) {
            $this->json_error('Payment record not found for this order.');
        }
        $payment = $this->firebase->get("{$this->feesBase}/Online Payments/{$payId}");
        if (!is_array($payment)) {
            $this->json_error('Payment record not found for this order.');
        }

        if (isset($payment['status']) && $payment['status'] === 'paid') {
            $this->json_error('This payment has already been verified.');
        }

        // TODO: Verify signature with gateway
        // Example for Razorpay:
        //   $expectedSignature = hash_hmac('sha256',
        //       $gatewayOrderId . '|' . $gatewayPaymentId,
        //       $gwConfig['api_secret']
        //   );
        //   if ($expectedSignature !== $signature) {
        //       // Mark as failed
        //       $this->firebase->update("{$this->feesBase}/Online Payments/{$payId}", [
        //           'status' => 'failed',
        //       ]);
        //       $this->json_error('Payment verification failed. Signature mismatch.');
        //   }

        $now = date('Y-m-d H:i:s');

        // Mark payment as paid
        $this->firebase->update("{$this->feesBase}/Online Payments/{$payId}", [
            'gateway_payment_id' => $gatewayPaymentId,
            'status'             => 'paid',
            'paid_at'            => $now,
            'signature'          => $signature,
        ]);

        // Atomically get next receipt number
        $receipt    = $this->_nextReceiptNo();
        $receiptKey = $receipt['key'];
        $today      = date('d-m-Y');

        $voucherData = [
            'type'               => 'online_payment',
            'student_id'         => isset($payment['student_id']) ? $payment['student_id'] : '',
            'student_name'       => isset($payment['student_name']) ? $payment['student_name'] : '',
            'class'              => isset($payment['class']) ? $payment['class'] : '',
            'section'            => isset($payment['section']) ? $payment['section'] : '',
            'Fees Received'      => isset($payment['amount']) ? number_format(floatval($payment['amount']), 2, '.', ',') : '0.00',
            'amount'             => isset($payment['amount']) ? $payment['amount'] : 0,
            'fee_months'         => isset($payment['fee_months']) ? $payment['fee_months'] : [],
            'payment_mode'       => 'Online - ' . (isset($payment['provider']) ? ucfirst($payment['provider']) : 'Gateway'),
            'gateway_payment_id' => $gatewayPaymentId,
            'receipt_no'         => $receiptKey,
            'timestamp'          => $now,
        ];

        $this->firebase->set("{$this->sessionRoot}/Accounts/Vouchers/{$today}/{$receiptKey}", $voucherData);

        // Update student's Month Fee for paid months
        $studentId = isset($payment['student_id']) ? $payment['student_id'] : '';
        $class     = isset($payment['class']) ? $payment['class'] : '';
        $section   = isset($payment['section']) ? $payment['section'] : '';
        $feeMonths = isset($payment['fee_months']) ? $payment['fee_months'] : [];

        if ($studentId !== '' && $class !== '' && $section !== '') {
            list($classNode, $sectionNode) = $this->_normalizeClassSection($class, $section);

            // Save fee record (matching Fees.php format)
            $recordPath = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$studentId}/Fees Record/{$receiptKey}";
            $this->firebase->set($recordPath, [
                'Amount'      => number_format(floatval($payment['amount']), 2, '.', ','),
                'Discount'    => '0.00',
                'Date'        => date('d-m-Y'),
                'Fine'        => '0.00',
                'Mode'        => 'Online - ' . (isset($payment['provider']) ? ucfirst($payment['provider']) : 'Gateway'),
                'Refer'       => 'Online Payment #' . $gatewayPaymentId,
            ]);

            // Update account book (matching Fees.php format)
            $dateObj = DateTime::createFromFormat('d-m-Y', date('d-m-Y'));
            $bookMonth = $dateObj ? $dateObj->format('F') : date('F');
            $bookDay = $dateObj ? $dateObj->format('d') : date('d');
            $amount = isset($payment['amount']) ? floatval($payment['amount']) : 0;
            $abPath = "{$this->sessionRoot}/Accounts/Account_book/Fees/{$bookMonth}/{$bookDay}/R";
            $curBook = $this->firebase->get($abPath);
            $curBook = is_numeric($curBook) ? floatval($curBook) : 0;
            $this->firebase->set($abPath, $curBook + $amount);

            // Update Month Fee for paid months
            $monthFeePath = "{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students/{$studentId}/Month Fee";
            $monthUpdate = [];
            foreach ($feeMonths as $m) {
                $monthUpdate[$m] = 1;
            }
            if (!empty($monthUpdate)) {
                $this->firebase->update($monthFeePath, $monthUpdate);
            }
        }

        // ── Accounting integration via Operations_accounting library
        try {
            $this->load->library('Operations_accounting', null, 'ops_acct');
            $this->ops_acct->init(
                $this->firebase, $this->school_name, $this->session_year, $this->admin_id, $this
            );
            $payAmount = isset($payment['amount']) ? floatval($payment['amount']) : 0;
            $payMode   = isset($payment['provider']) ? $payment['provider'] : 'online';
            $this->ops_acct->create_fee_journal([
                'school_name'  => $this->school_name,
                'session_year' => $this->session_year,
                'date'         => date('Y-m-d'),
                'amount'       => $payAmount,
                'payment_mode' => $payMode,
                'bank_code'    => '',
                'receipt_no'   => $receiptKey,
                'student_name' => isset($payment['student_name']) ? $payment['student_name'] : '',
                'student_id'   => $studentId,
                'class'        => $class,
                'admin_id'     => $this->admin_id,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Accounting integration failed in verify_payment: ' . $e->getMessage());
        }

        $this->json_success([
            'message'    => 'Payment verified and recorded successfully.',
            'receipt_no' => $receiptKey,
            'payment_id' => $payId,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  DASHBOARD / ANALYTICS HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET — Fee summary for dashboard stats.
     * Returns: total_collected, total_due, total_discounts, total_scholarships, total_refunds
     */
    public function get_fee_summary()
    {
        $this->_require_role(self::VIEW_ROLES, 'get_fee_summary');
        // Check for cached summary (valid for 5 minutes)
        $cachePath = "{$this->feesBase}/Summary Cache";
        $cached = $this->firebase->get($cachePath);
        if (is_array($cached) && !empty($cached['as_of'])) {
            $cacheAge = time() - strtotime($cached['as_of']);
            if ($cacheAge < 300) { // 5 minutes
                $this->json_success($cached);
            }
        }

        $totalCollected    = 0;
        $totalDue          = 0;
        $totalDiscounts    = 0;
        $totalScholarships = 0;
        $totalRefunds      = 0;

        // --- Total collected: sum all voucher amounts ---
        $vouchers = $this->firebase->get("{$this->sessionRoot}/Accounts/Vouchers");
        if (is_array($vouchers)) {
            foreach ($vouchers as $date => $dayVouchers) {
                if (!is_array($dayVouchers)) continue;
                foreach ($dayVouchers as $key => $voucher) {
                    if (!is_array($voucher)) continue;
                    // Handle both formats: 'Fees Received' (old) and 'amount' (new)
                    $amt = 0;
                    if (isset($voucher['Fees Received'])) {
                        $amt = floatval(str_replace(',', '', $voucher['Fees Received']));
                    } elseif (isset($voucher['amount'])) {
                        $amt = floatval($voucher['amount']);
                    }
                    if ($amt > 0) $totalCollected += $amt;
                }
            }
        }

        // --- Scan classes in bulk (read entire section data at once) ---
        $classSections = $this->_getAllClassSections();
        $months = ['April','May','June','July','August','September','October','November','December','January','February','March'];
        $currentMonth = date('n');
        $monthIndex = ($currentMonth >= 4) ? ($currentMonth - 4) : ($currentMonth + 8);
        $monthsToCheck = array_slice($months, 0, $monthIndex + 1);

        foreach ($classSections as $cs) {
            $classNode   = $cs['class'];
            $sectionNode = $cs['section'];

            // Read entire section Students node in ONE call (includes List, each student's data)
            $sectionData = $this->firebase->get("{$this->sessionRoot}/{$classNode}/{$sectionNode}/Students");
            $classFees   = $this->firebase->get("{$this->feesBase}/Classes Fees/{$classNode}/{$sectionNode}");

            if (!is_array($sectionData) || !is_array($classFees)) continue;

            $studentList = isset($sectionData['List']) && is_array($sectionData['List'])
                ? $sectionData['List'] : [];

            foreach ($studentList as $userId => $name) {
                // Student data is already in $sectionData
                $studentData = isset($sectionData[$userId]) && is_array($sectionData[$userId])
                    ? $sectionData[$userId] : [];
                $monthFee = isset($studentData['Month Fee']) && is_array($studentData['Month Fee'])
                    ? $studentData['Month Fee'] : [];
                $discount = isset($studentData['Discount']) && is_array($studentData['Discount'])
                    ? $studentData['Discount'] : [];

                // Due calculation
                foreach ($monthsToCheck as $month) {
                    $paid = isset($monthFee[$month]) ? (int)$monthFee[$month] : 0;
                    if ($paid !== 1 && isset($classFees[$month]) && is_array($classFees[$month])) {
                        foreach ($classFees[$month] as $title => $amt) {
                            $totalDue += floatval($amt);
                        }
                    }
                }

                // Discounts
                if (isset($discount['totalDiscount'])) {
                    $totalDiscounts += floatval($discount['totalDiscount']);
                }
            }
        }

        // --- Total scholarships ---
        $awards = $this->firebase->get("{$this->feesBase}/Scholarship Awards");
        if (is_array($awards)) {
            foreach ($awards as $award) {
                if (is_array($award) && isset($award['status']) && $award['status'] === 'active' && isset($award['amount'])) {
                    $totalScholarships += floatval($award['amount']);
                }
            }
        }

        // --- Total refunds ---
        $refunds = $this->firebase->get("{$this->feesBase}/Refunds");
        if (is_array($refunds)) {
            foreach ($refunds as $ref) {
                if (is_array($ref) && isset($ref['status']) && $ref['status'] === 'processed' && isset($ref['amount'])) {
                    $totalRefunds += floatval($ref['amount']);
                }
            }
        }

        $result = [
            'total_collected'    => round($totalCollected, 2),
            'total_due'          => round($totalDue, 2),
            'total_discounts'    => round($totalDiscounts, 2),
            'total_scholarships' => round($totalScholarships, 2),
            'total_refunds'      => round($totalRefunds, 2),
            'net_receivable'     => round($totalDue - $totalDiscounts - $totalScholarships, 2),
            'as_of'              => date('Y-m-d H:i:s'),
        ];

        // Cache the result
        $this->firebase->set($cachePath, $result);

        $this->json_success($result);
    }

    // ══════════════════════════════════════════════════════════════════
    //  FEE CARRY-FORWARD (F-15)
    // ══════════════════════════════════════════════════════════════════

    /**
     * POST — Carry forward unpaid fees from previous session.
     * Params: from_session (e.g. "2025-26"), to_session (e.g. "2026-27")
     */
    public function carry_forward_fees()
    {
        $this->_require_role(self::ADMIN_ROLES, 'carry_forward_fees');

        $fromSession = trim($this->input->post('from_session') ?? '');
        $toSession   = trim($this->input->post('to_session') ?? '');
        $sn          = $this->school_name;

        if (empty($fromSession) || empty($toSession)) {
            $this->json_error('Both from_session and to_session are required.');
        }
        if ($fromSession === $toSession) {
            $this->json_error('Source and target sessions must be different.');
        }

        // Read fee structure from old session
        $oldFeesBase = "Schools/{$sn}/{$fromSession}/Accounts/Fees";
        $classFees = $this->firebase->get("{$oldFeesBase}/Classes Fees");
        if (!is_array($classFees)) {
            $this->json_error('No fee structure found in the source session.');
        }

        // Read all class/sections in old session to find students with unpaid months
        $sessionRoot = "Schools/{$sn}/{$fromSession}";
        $classKeys = $this->firebase->shallow_get($sessionRoot);
        if (!is_array($classKeys)) $classKeys = [];

        $months = ['April','May','June','July','August','September','October','November','December','January','February','March'];
        $carriedForward = [];
        $totalStudents = 0;
        $totalAmount = 0;

        foreach ($classKeys as $classKey => $v) {
            if (strpos($classKey, 'Class ') !== 0) continue;

            $sections = $this->firebase->shallow_get("{$sessionRoot}/{$classKey}");
            if (!is_array($sections)) continue;

            foreach ($sections as $sectionKey => $sv) {
                if (strpos($sectionKey, 'Section ') !== 0) continue;

                $studentsPath = "{$sessionRoot}/{$classKey}/{$sectionKey}/Students";
                $studentList = $this->firebase->get("{$studentsPath}/List");
                if (!is_array($studentList)) continue;

                foreach ($studentList as $userId => $name) {
                    $monthFee = $this->firebase->get("{$studentsPath}/{$userId}/Month Fee");
                    if (!is_array($monthFee)) continue;

                    $unpaidMonths = [];
                    foreach ($months as $m) {
                        if (isset($monthFee[$m]) && (int)$monthFee[$m] === 0) {
                            $unpaidMonths[] = $m;
                        }
                    }

                    if (!empty($unpaidMonths)) {
                        // Calculate unpaid amount from fee structure
                        // Firebase stores fees nested: Classes Fees/Class 8th/Section A/{title}
                        $feeData = $classFees[$classKey][$sectionKey] ?? [];

                        $monthlyTotal = 0;
                        if (is_array($feeData)) {
                            foreach ($feeData as $title => $amt) {
                                if (!is_numeric($amt)) continue;
                                $monthlyTotal += (float) $amt;
                            }
                        }

                        $unpaidAmount = $monthlyTotal * count($unpaidMonths);
                        if ($unpaidAmount > 0) {
                            $carriedForward[$userId] = [
                                'student_name'  => $name,
                                'class'         => $classKey,
                                'section'       => $sectionKey,
                                'unpaid_months' => $unpaidMonths,
                                'amount'        => round($unpaidAmount, 2),
                                'from_session'  => $fromSession,
                            ];
                            $totalStudents++;
                            $totalAmount += $unpaidAmount;
                        }
                    }
                }
            }
        }

        if (empty($carriedForward)) {
            $this->json_success(['message' => 'No unpaid fees found to carry forward.', 'count' => 0]);
            return;
        }

        // Write carry-forward records to new session
        $cfPath = "Schools/{$sn}/{$toSession}/Accounts/Fees/Carried_Forward";
        $this->firebase->set($cfPath, [
            'from_session'   => $fromSession,
            'created_at'     => date('c'),
            'created_by'     => $this->session->userdata('admin_name') ?? 'Admin',
            'total_students' => $totalStudents,
            'total_amount'   => round($totalAmount, 2),
            'students'       => $carriedForward,
        ]);

        $this->json_success([
            'message' => "Carried forward unpaid fees for {$totalStudents} student(s). Total: Rs. " . number_format($totalAmount, 2),
            'count'   => $totalStudents,
            'total'   => round($totalAmount, 2),
        ]);
    }
}
