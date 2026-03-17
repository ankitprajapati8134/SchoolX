<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/MY_Superadmin_Controller.php';

/**
 * Superadmin_plans
 * Subscription plan management: create, update, delete, assign modules per plan.
 */
class Superadmin_plans extends MY_Superadmin_Controller
{
    // All available modules the SA can toggle per plan
    const AVAILABLE_MODULES = [
        'student_management' => 'Student Management',
        'staff_management'   => 'Staff Management',
        'fees'               => 'Fees Collection',
        'accounts'           => 'Accounts & Ledger',
        'exams'              => 'Exam Management',
        'results'            => 'Result Management',
        'attendance'         => 'Attendance',
        'homework'           => 'Homework',
        'notices'            => 'Notices & Announcements',
        'gallery'            => 'School Gallery',
        'timetable'          => 'Timetable',
        'id_cards'           => 'ID Cards',
        'sms_alerts'         => 'SMS Alerts',
        'parent_app'         => 'Parent App Access',
        'teacher_app'        => 'Teacher App Access',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/plans
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $plans = [];
        try {
            $raw     = $this->firebase->get('System/Plans') ?? [];
            $schools = $this->firebase->get('System/Schools') ?? [];

            foreach ($raw as $pid => $p) {
                // Count schools on this plan (check System/Schools subscription)
                $school_count = 0;
                foreach ($schools as $s) {
                    if (!is_array($s)) continue;
                    $sub = is_array($s['subscription'] ?? null) ? $s['subscription'] : [];
                    if (($sub['plan_id'] ?? '') === $pid) $school_count++;
                }
                $plans[] = array_merge(['plan_id' => $pid, 'school_count' => $school_count], $p);
            }
            usort($plans, fn($a, $b) => ($a['sort_order'] ?? 99) - ($b['sort_order'] ?? 99));
        } catch (Exception $e) {
            log_message('error', 'SA plans/index: ' . $e->getMessage());
        }

        $data = [
            'page_title'        => 'Subscription Plans',
            'plans'             => $plans,
            'available_modules' => self::AVAILABLE_MODULES,
        ];

        $this->load->view('superadmin/include/sa_header', $data);
        $this->load->view('superadmin/plans/index',       $data);
        $this->load->view('superadmin/include/sa_footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/create
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $name        = trim($this->input->post('name',         TRUE) ?? '');
        $price       = (float)($this->input->post('price')         ?? 0);
        $billing     = trim($this->input->post('billing_cycle', TRUE) ?? 'monthly');
        $max_students= (int)($this->input->post('max_students')     ?? 500);
        $max_staff   = (int)($this->input->post('max_staff')        ?? 50);
        $grace_days  = (int)($this->input->post('grace_days')       ?? 7);
        $sort_order  = (int)($this->input->post('sort_order')       ?? 10);
        $modules_raw = $this->input->post('modules') ?? [];

        if (empty($name)) { $this->json_error('Plan name is required.'); return; }
        if (!in_array($billing, ['monthly', 'quarterly', 'annual'])) {
            $this->json_error('Invalid billing cycle.'); return;
        }

        // Build modules map: only keys from AVAILABLE_MODULES that were submitted
        $modules = [];
        foreach (array_keys(self::AVAILABLE_MODULES) as $mod) {
            $modules[$mod] = in_array($mod, (array)$modules_raw);
        }

        $plan_id = 'PLAN_' . strtoupper(substr(md5(uniqid($name, true)), 0, 6));

        try {
            $this->firebase->set("System/Plans/{$plan_id}", [
                'name'         => $name,
                'price'        => $price,
                'billing_cycle'=> $billing,
                'max_students' => $max_students,
                'max_staff'    => $max_staff,
                'grace_days'   => $grace_days,
                'sort_order'   => $sort_order,
                'modules'      => $modules,
                'created_at'   => date('Y-m-d H:i:s'),
                'created_by'   => $this->sa_id,
            ]);

            $this->sa_log('plan_created', '', ['plan_id' => $plan_id, 'name' => $name]);
            $this->json_success(['plan_id' => $plan_id, 'message' => "Plan '{$name}' created."]);
        } catch (Exception $e) {
            log_message('error', 'SA plans/create: ' . $e->getMessage());
            $this->json_error('Failed to create plan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/update
    // ─────────────────────────────────────────────────────────────────────────

    public function update()
    {
        $plan_id      = trim($this->input->post('plan_id',       TRUE) ?? '');
        $name         = trim($this->input->post('name',          TRUE) ?? '');
        $price        = (float)($this->input->post('price')            ?? 0);
        $billing      = trim($this->input->post('billing_cycle', TRUE) ?? '');
        $max_students = $this->input->post('max_students');
        $max_staff    = $this->input->post('max_staff');
        $grace_days   = (int)($this->input->post('grace_days')        ?? 7);
        $sort_order   = $this->input->post('sort_order');
        $modules_raw  = $this->input->post('modules') ?? [];

        if (empty($plan_id) || empty($name)) {
            $this->json_error('Plan ID and name are required.');
            return;
        }
        if (!preg_match('/^PLAN_[A-Z0-9]+$/', $plan_id)) {
            $this->json_error('Invalid plan ID format.'); return;
        }

        $modules = [];
        foreach (array_keys(self::AVAILABLE_MODULES) as $mod) {
            $modules[$mod] = in_array($mod, (array)$modules_raw);
        }

        $update = [
            'name'        => $name,
            'price'       => $price,
            'grace_days'  => $grace_days,
            'modules'     => $modules,
            'updated_at'  => date('Y-m-d H:i:s'),
            'updated_by'  => $this->sa_id,
        ];
        if ($billing !== '' && in_array($billing, ['monthly', 'quarterly', 'annual'])) {
            $update['billing_cycle'] = $billing;
        }
        if ($max_students !== null) $update['max_students'] = (int)$max_students;
        if ($max_staff    !== null) $update['max_staff']    = (int)$max_staff;
        if ($sort_order   !== null) $update['sort_order']   = (int)$sort_order;

        try {
            $this->firebase->update("System/Plans/{$plan_id}", $update);
            $this->sa_log('plan_updated', '', ['plan_id' => $plan_id]);
            $this->json_success(['message' => "Plan '{$name}' updated."]);
        } catch (Exception $e) {
            $this->json_error('Failed to update plan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/delete
    // ─────────────────────────────────────────────────────────────────────────

    public function delete_plan()
    {
        $plan_id = trim($this->input->post('plan_id', TRUE) ?? '');
        if (empty($plan_id)) { $this->json_error('Plan ID required.'); return; }
        if (!preg_match('/^PLAN_[A-Z0-9]+$/', $plan_id)) {
            $this->json_error('Invalid plan ID format.'); return;
        }

        // Safety: refuse if schools are on this plan
        try {
            $schools = $this->firebase->get('System/Schools') ?? [];
            foreach ($schools as $s) {
                if (!is_array($s)) continue;
                $sub = is_array($s['subscription'] ?? null) ? $s['subscription'] : [];
                if (($sub['plan_id'] ?? '') === $plan_id) {
                    $this->json_error('Cannot delete: one or more schools are on this plan. Reassign them first.');
                    return;
                }
            }
            $this->firebase->delete("System/Plans", $plan_id);
            $this->sa_log('plan_deleted', '', ['plan_id' => $plan_id]);
            $this->json_success(['message' => 'Plan deleted.']);
        } catch (Exception $e) {
            $this->json_error('Failed to delete plan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/fetch
    // Returns single plan data for edit modal
    // ─────────────────────────────────────────────────────────────────────────

    public function fetch()
    {
        $plan_id = trim($this->input->post('plan_id', TRUE) ?? '');

        try {
            if ($plan_id !== '') {
                if (!preg_match('/^PLAN_[A-Z0-9]+$/', $plan_id)) {
                    $this->json_error('Invalid plan ID format.'); return;
                }
                // Fetch a single plan
                $plan = $this->firebase->get("System/Plans/{$plan_id}") ?? [];
                $this->json_success(['plan' => $plan, 'plans' => [$plan_id => $plan]]);
            } else {
                // No plan_id — return all plans
                $raw   = $this->firebase->get('System/Plans') ?? [];
                $plans = [];
                foreach ($raw as $pid => $p) {
                    if (is_array($p)) $plans[$pid] = $p;
                }
                $this->json_success(['plans' => $plans, 'total' => count($plans)]);
            }
        } catch (Exception $e) {
            $this->json_error('Failed to fetch plans.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/seed_defaults
    // Seeds Basic / Standard / Premium plans if they do not already exist.
    // ─────────────────────────────────────────────────────────────────────────

    public function seed_defaults()
    {
        $defaults = [
            'Basic' => [
                'price' => 5000, 'billing_cycle' => 'annual',
                'max_students' => 300, 'max_staff' => 20, 'grace_days' => 7, 'sort_order' => 1,
                'description' => 'Essential modules for small schools.',
                'modules' => [
                    'student_management' => true,  'staff_management'  => true,
                    'fees'               => true,  'attendance'        => true,
                    'notices'            => true,  'timetable'         => true,
                    'accounts'           => false, 'exams'             => false,
                    'results'            => false, 'homework'          => false,
                    'gallery'            => false, 'id_cards'          => false,
                    'sms_alerts'         => false, 'parent_app'        => false,
                    'teacher_app'        => false,
                ],
            ],
            'Standard' => [
                'price' => 12000, 'billing_cycle' => 'annual',
                'max_students' => 1000, 'max_staff' => 60, 'grace_days' => 10, 'sort_order' => 2,
                'description' => 'Full academic suite for medium-sized schools.',
                'modules' => [
                    'student_management' => true,  'staff_management'  => true,
                    'fees'               => true,  'attendance'        => true,
                    'notices'            => true,  'timetable'         => true,
                    'accounts'           => true,  'exams'             => true,
                    'results'            => true,  'homework'          => true,
                    'gallery'            => true,  'id_cards'          => true,
                    'sms_alerts'         => false, 'parent_app'        => false,
                    'teacher_app'        => false,
                ],
            ],
            'Premium' => [
                'price' => 25000, 'billing_cycle' => 'annual',
                'max_students' => 5000, 'max_staff' => 200, 'grace_days' => 15, 'sort_order' => 3,
                'description' => 'All modules including apps & SMS for large institutions.',
                'modules' => [
                    'student_management' => true, 'staff_management' => true,
                    'fees'               => true, 'attendance'       => true,
                    'notices'            => true, 'timetable'        => true,
                    'accounts'           => true, 'exams'            => true,
                    'results'            => true, 'homework'         => true,
                    'gallery'            => true, 'id_cards'         => true,
                    'sms_alerts'         => true, 'parent_app'       => true,
                    'teacher_app'        => true,
                ],
            ],
        ];

        $now    = date('Y-m-d H:i:s');
        $seeded = [];

        try {
            $existing      = $this->firebase->get('System/Plans') ?? [];
            $existingNames = array_map(fn($p) => strtolower($p['name'] ?? ''), array_filter((array)$existing, 'is_array'));

            foreach ($defaults as $planName => $config) {
                if (in_array(strtolower($planName), $existingNames)) continue;

                $plan_id = 'PLAN_' . strtoupper(substr(md5(uniqid($planName, true)), 0, 6));
                $this->firebase->set("System/Plans/{$plan_id}", array_merge($config, [
                    'name'       => $planName,
                    'plan_id'    => $plan_id,
                    'created_at' => $now,
                    'created_by' => $this->sa_id,
                ]));
                $seeded[] = $planName;
            }

            if (empty($seeded)) {
                $this->json_success(['message' => 'Default plans already exist — no changes made.', 'seeded' => []]);
            } else {
                $this->sa_log('plans_seeded', '', ['plans' => $seeded]);
                $this->json_success([
                    'message' => 'Created: ' . implode(', ', $seeded) . '.',
                    'seeded'  => $seeded,
                ]);
            }
        } catch (Exception $e) {
            $this->json_error('Failed to seed plans: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/plans/subscriptions
    // Subscription expiry tracking dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function subscriptions()
    {
        $data = ['page_title' => 'Subscription Tracking'];
        $this->load->view('superadmin/include/sa_header', $data);
        $this->load->view('superadmin/plans/subscriptions', $data);
        $this->load->view('superadmin/include/sa_footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/fetch_subscriptions
    // Returns all school subscriptions with computed status + days remaining
    // ─────────────────────────────────────────────────────────────────────────

    public function fetch_subscriptions()
    {
        try {
            $schools = $this->firebase->get('System/Schools') ?? [];
            $today   = date('Y-m-d');
            $rows    = [];

            foreach ($schools as $name => $school) {
                if (!is_array($school)) continue;

                $sub      = is_array($school['subscription'] ?? null) ? $school['subscription'] : [];
                $saP      = is_array($school['profile']       ?? null) ? $school['profile']      : [];
                $expiry   = $sub['expiry_date'] ?? ($sub['duration']['endDate'] ?? '');
                $grace_end= $sub['grace_end']   ?? '';
                $status      = $sub['status'] ?? 'Inactive';
                $statusLower = strtolower($status);

                // Compute display classification
                if ($statusLower === 'suspended') {
                    $display = 'suspended';
                } elseif ($statusLower === 'grace_period') {
                    $display = 'grace';
                } elseif (empty($expiry)) {
                    $display = 'inactive';
                } elseif ($expiry < $today) {
                    $display = (!empty($grace_end) && $grace_end >= $today) ? 'grace' : 'expired';
                } elseif ((int)ceil((strtotime($expiry) - time()) / 86400) <= 30) {
                    $display = 'expiring_soon';
                } else {
                    $display = 'active';
                }

                $rows[] = [
                    'uid'          => $name,
                    'name'         => $saP['name']      ?? $name,
                    'school_code'  => $saP['school_code'] ?? '',
                    'plan_name'    => $sub['plan_name']  ?? '—',
                    'expiry_date'  => $expiry,
                    'grace_end'    => $grace_end,
                    'sub_status'   => $status,
                    'display'      => $display,
                    'days_left'    => $expiry ? (int)ceil((strtotime($expiry) - time()) / 86400) : null,
                    'grace_left'   => $grace_end ? (int)ceil((strtotime($grace_end) - time()) / 86400) : null,
                    'last_payment' => $sub['last_payment_date'] ?? '',
                ];
            }

            // Sort: soonest expiry first; null (inactive) at end
            usort($rows, function ($a, $b) {
                if ($a['days_left'] === null) return 1;
                if ($b['days_left'] === null) return -1;
                return $a['days_left'] - $b['days_left'];
            });

            // Build bucketed counts for dashboard/tests
            $buckets = ['active' => 0, 'expiring_soon' => 0, 'grace' => 0, 'expired' => 0, 'suspended' => 0, 'inactive' => 0];
            foreach ($rows as $r) {
                $d = $r['display'] ?? 'inactive';
                $buckets[$d] = ($buckets[$d] ?? 0) + 1;
            }

            $this->json_success(array_merge(['rows' => $rows], $buckets));
        } catch (Exception $e) {
            $this->json_error('Failed to load subscriptions.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/expire_check
    // Scan all schools; move past-expiry → Grace_Period or Suspended.
    // Safe to call repeatedly — idempotent per school.
    // ─────────────────────────────────────────────────────────────────────────

    public function expire_check()
    {
        try {
            $schools   = $this->firebase->get('System/Schools') ?? [];
            $today     = date('Y-m-d');
            $suspended = [];
            $graced    = [];

            foreach ($schools as $name => $school) {
                if (!is_array($school)) continue;
                if (!preg_match("/^[A-Za-z0-9 ',_\-]+$/u", $name)) continue;

                $sub      = is_array($school['subscription'] ?? null) ? $school['subscription'] : [];
                $status   = $sub['status'] ?? 'Inactive';
                $expiry   = $sub['expiry_date']  ?? ($sub['duration']['endDate'] ?? '');
                $grace_end= $sub['grace_end']    ?? '';

                if (empty($expiry)) continue;

                // Normalise legacy lowercase statuses for comparison
                $statusLower = strtolower($status);

                if ($statusLower === 'active' && $expiry < $today) {
                    if (!empty($grace_end) && $grace_end >= $today) {
                        // Move to grace period — reduce access but not yet suspended
                        $this->firebase->update("System/Schools/{$name}/subscription", ['status' => 'Grace_Period']);
                        $this->firebase->update("System/Schools/{$name}/profile",      ['status' => 'grace_period']);
                        $graced[]    = $name;
                        $this->sa_log('auto_grace', $name);
                    } else {
                        // Fully suspend — top-level status gates all SA reads
                        $this->firebase->update("System/Schools/{$name}",             ['status' => 'suspended']);
                        $this->firebase->update("System/Schools/{$name}/subscription", ['status' => 'Suspended']);
                        $this->firebase->update("System/Schools/{$name}/profile",      ['status' => 'suspended']);
                        $suspended[] = $name;
                        $this->sa_log('auto_suspended', $name);
                    }
                } elseif ($statusLower === 'grace_period' && !empty($grace_end) && $grace_end < $today) {
                    // Grace period ended — fully suspend
                    $this->firebase->update("System/Schools/{$name}",             ['status' => 'suspended']);
                    $this->firebase->update("System/Schools/{$name}/subscription", ['status' => 'Suspended']);
                    $this->firebase->update("System/Schools/{$name}/profile",      ['status' => 'suspended']);
                    $suspended[] = $name;
                    $this->sa_log('auto_suspended', $name);
                }
            }

            $this->json_success([
                'suspended'       => $suspended,
                'suspended_count' => count($suspended),
                'graced'          => $graced,
                'graced_count'    => count($graced),
                'message'         => sprintf(
                    'Check complete. %d suspended, %d moved to grace period.',
                    count($suspended), count($graced)
                ),
            ]);
        } catch (Exception $e) {
            $this->json_error('Expire check failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET  /superadmin/plans/payments
    // Payment records management
    // ─────────────────────────────────────────────────────────────────────────

    public function payments()
    {
        $schools = [];
        $plans   = [];
        try {
            $raw  = $this->firebase->get('System/Schools') ?? [];
            foreach ($raw as $name => $school) {
                if (!is_array($school)) continue;
                $saP            = is_array($school['profile']      ?? null) ? $school['profile']      : [];
                $sub            = is_array($school['subscription'] ?? null) ? $school['subscription'] : [];
                $schools[$name] = [
                    'name'        => $saP['name']      ?? $name,
                    'plan_name'   => $sub['plan_name'] ?? '—',
                    'school_code' => $saP['school_code'] ?? '',
                ];
            }
            $rawPlans = $this->firebase->get('System/Plans') ?? [];
            foreach ($rawPlans as $pid => $p) {
                $plans[$pid] = [
                    'name'  => $p['name']  ?? $pid,
                    'price' => (float)($p['price'] ?? 0),
                ];
            }
        } catch (Exception $e) {}

        $data = [
            'page_title' => 'Payment Records',
            'schools'    => $schools,
            'plans'      => $plans,
        ];
        $this->load->view('superadmin/include/sa_header', $data);
        $this->load->view('superadmin/plans/payments',    $data);
        $this->load->view('superadmin/include/sa_footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/fetch_payments
    // ─────────────────────────────────────────────────────────────────────────

    public function fetch_payments()
    {
        try {
            $raw  = $this->firebase->get('System/Payments') ?? [];
            $rows = [];
            foreach ($raw as $pid => $p) {
                if (!is_array($p)) continue;
                $rows[] = array_merge(['payment_id' => $pid], $p);
            }
            usort($rows, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
            $this->json_success(['rows' => $rows]);
        } catch (Exception $e) {
            $this->json_error('Failed to load payments.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/add_payment
    // ─────────────────────────────────────────────────────────────────────────

    public function add_payment()
    {
        // Accept school_uid or school_name (test compatibility)
        $school_uid   = trim($this->input->post('school_uid',   TRUE)
                     ?? $this->input->post('school_name', TRUE) ?? '');
        $amount       = (float)($this->input->post('amount',    TRUE) ?? 0);
        // Accept plan_id or plan_name (test compatibility)
        $plan_id      = trim($this->input->post('plan_id',      TRUE)
                     ?? $this->input->post('plan_name', TRUE) ?? '');
        $status       = trim($this->input->post('status',       TRUE) ?? 'pending');
        $invoice_date = trim($this->input->post('invoice_date', TRUE) ?? date('Y-m-d'));
        $due_date     = trim($this->input->post('due_date',     TRUE) ?? '');
        $paid_date    = trim($this->input->post('paid_date',    TRUE) ?? '');
        $period_start = trim($this->input->post('period_start', TRUE) ?? '');
        $period_end   = trim($this->input->post('period_end',   TRUE) ?? '');
        $notes        = trim($this->input->post('notes',        TRUE) ?? '');

        if (empty($school_uid) || $amount <= 0 || empty($plan_id)) {
            $this->json_error('School, amount and plan are required.'); return;
        }
        if (!preg_match("/^[A-Za-z0-9 ',_\-]+$/u", $school_uid)) {
            $this->json_error('Invalid school identifier.'); return;
        }
        if (!in_array($status, ['paid', 'pending', 'overdue', 'failed'])) {
            $this->json_error('Invalid payment status.'); return;
        }
        if (!preg_match('/^PLAN_[A-Z0-9]+$/', $plan_id)) {
            $this->json_error('Invalid plan ID format.'); return;
        }

        $plan_data = [];
        try { $plan_data = $this->firebase->get("System/Plans/{$plan_id}") ?? []; } catch (Exception $e) {}

        $now        = date('Y-m-d H:i:s');
        $payment_id = 'PAY_' . strtoupper(substr(md5(uniqid($school_uid, true)), 0, 8));

        try {
            $this->firebase->set("System/Payments/{$payment_id}", [
                'school_uid'    => $school_uid,
                'amount'        => $amount,
                'plan_id'       => $plan_id,
                'plan_name'     => $plan_data['name']          ?? $plan_id,
                'billing_cycle' => $plan_data['billing_cycle'] ?? 'annual',
                'status'        => $status,
                'invoice_date'  => $invoice_date,
                'due_date'      => $due_date,
                'paid_date'     => $paid_date,
                'period_start'  => $period_start,
                'period_end'    => $period_end,
                'notes'         => $notes,
                'created_by'    => $this->sa_id,
                'created_at'    => $now,
            ]);

            // If paid, sync last_payment_date onto school subscription
            if ($status === 'paid' && !empty($paid_date)) {
                $this->firebase->update("System/Schools/{$school_uid}/subscription", [
                    'last_payment_date'   => $paid_date,
                    'last_payment_amount' => $amount,
                ]);
            }

            $this->sa_log('payment_added', $school_uid, ['payment_id' => $payment_id, 'amount' => $amount]);
            $this->json_success(['payment_id' => $payment_id, 'message' => 'Payment recorded.']);
        } catch (Exception $e) {
            $this->json_error('Failed to save payment.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/update_payment
    // ─────────────────────────────────────────────────────────────────────────

    public function update_payment()
    {
        $payment_id = trim($this->input->post('payment_id', TRUE) ?? '');
        $status     = trim($this->input->post('status',     TRUE) ?? '');
        $paid_date  = trim($this->input->post('paid_date',  TRUE) ?? '');
        $notes      = trim($this->input->post('notes',      TRUE) ?? '');

        if (empty($payment_id) || !preg_match('/^PAY_[A-Z0-9]+$/', $payment_id)) {
            $this->json_error('Invalid payment ID.'); return;
        }
        if (!empty($status) && !in_array($status, ['paid', 'pending', 'overdue', 'failed'])) {
            $this->json_error('Invalid payment status.'); return;
        }

        try {
            $existing = $this->firebase->get("System/Payments/{$payment_id}");
            if (empty($existing)) { $this->json_error('Payment record not found.'); return; }

            $update = ['updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $this->sa_id];
            if ($status)    $update['status']    = $status;
            if ($paid_date) $update['paid_date'] = $paid_date;
            if ($notes !== '') $update['notes']  = $notes;

            $this->firebase->update("System/Payments/{$payment_id}", $update);

            // Sync last_payment_date on the school if marked paid
            if ($status === 'paid' && !empty($paid_date)) {
                $uid = $existing['school_uid'] ?? '';
                if ($uid && preg_match("/^[A-Za-z0-9 ',_\-]+$/u", $uid)) {
                    $this->firebase->update("System/Schools/{$uid}/subscription", [
                        'last_payment_date'   => $paid_date,
                        'last_payment_amount' => $existing['amount'] ?? 0,
                    ]);
                }
            }

            $this->sa_log('payment_updated', $existing['school_uid'] ?? '', ['payment_id' => $payment_id]);
            $this->json_success(['message' => 'Payment updated.']);
        } catch (Exception $e) {
            $this->json_error('Failed to update payment.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST  /superadmin/plans/delete_payment
    // ─────────────────────────────────────────────────────────────────────────

    public function delete_payment()
    {
        $payment_id = trim($this->input->post('payment_id', TRUE) ?? '');
        if (empty($payment_id) || !preg_match('/^PAY_[A-Z0-9]+$/', $payment_id)) {
            $this->json_error('Invalid payment ID.'); return;
        }

        try {
            $existing = $this->firebase->get("System/Payments/{$payment_id}");
            if (empty($existing)) { $this->json_error('Payment not found.'); return; }

            $this->firebase->delete('System/Payments', $payment_id);
            $this->sa_log('payment_deleted', $existing['school_uid'] ?? '', ['payment_id' => $payment_id]);
            $this->json_success(['message' => 'Payment deleted.']);
        } catch (Exception $e) {
            $this->json_error('Failed to delete payment.');
        }
    }
}
