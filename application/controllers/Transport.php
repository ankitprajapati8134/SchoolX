<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Transport Management Controller
 *
 * Sub-modules: Vehicles, Routes & Stops, Student Assignments, Fee Tracking
 *
 * Firebase paths:
 *   Schools/{school}/Operations/Transport/Vehicles/{VH0001}
 *   Schools/{school}/Operations/Transport/Routes/{RT0001}
 *   Schools/{school}/Operations/Transport/Stops/{STP0001}
 *   Schools/{school}/Operations/Transport/Assignments/{student_id}
 *   Schools/{school}/Operations/Transport/Counters/{type}
 *
 * Integration: Student module (assignments), Staff (drivers), Fees (transport fee)
 */
class Transport extends MY_Controller
{
    const OPS_ADMIN_ROLES  = ['Super Admin', 'Principal', 'Vice Principal'];
    const TRN_MANAGE_ROLES = ['Super Admin', 'Principal', 'Vice Principal', 'Operations Manager', 'Transport Manager'];
    const TRN_VIEW_ROLES   = ['Super Admin', 'Principal', 'Vice Principal', 'Operations Manager', 'Transport Manager', 'Accountant', 'Teacher'];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('operations_accounting');
        $this->operations_accounting->init(
            $this->firebase, $this->school_name, $this->session_year, $this->admin_id, $this
        );
    }

    private function _require_manage()
    {
        if (!in_array($this->admin_role, self::TRN_MANAGE_ROLES, true))
            $this->json_error('Access denied.', 403);
    }
    private function _require_view()
    {
        if (!in_array($this->admin_role, self::TRN_VIEW_ROLES, true))
            $this->json_error('Access denied.', 403);
    }

    // ── Path Helpers ────────────────────────────────────────────────────
    private function _trn(string $sub = ''): string
    {
        $b = "Schools/{$this->school_name}/Operations/Transport";
        return $sub !== '' ? "{$b}/{$sub}" : $b;
    }
    private function _vehicles(string $id = ''): string
    {
        return $id !== '' ? $this->_trn("Vehicles/{$id}") : $this->_trn('Vehicles');
    }
    private function _routes(string $id = ''): string
    {
        return $id !== '' ? $this->_trn("Routes/{$id}") : $this->_trn('Routes');
    }
    private function _stops(string $id = ''): string
    {
        return $id !== '' ? $this->_trn("Stops/{$id}") : $this->_trn('Stops');
    }
    private function _assignments(string $id = ''): string
    {
        return $id !== '' ? $this->_trn("Assignments/{$id}") : $this->_trn('Assignments');
    }
    private function _counters(string $type): string
    {
        return $this->_trn("Counters/{$type}");
    }

    // ====================================================================
    //  PAGE LOAD
    // ====================================================================

    public function index()
    {
        $tab = $this->uri->segment(2, 'vehicles');
        $data = ['active_tab' => $tab];
        $this->load->view('include/header', $data);
        $this->load->view('transport/index', $data);
        $this->load->view('include/footer');
    }

    // ====================================================================
    //  VEHICLES
    // ====================================================================

    public function get_vehicles()
    {
        $this->_require_view();
        $vehicles = $this->firebase->get($this->_vehicles());
        $list = [];
        if (is_array($vehicles)) {
            foreach ($vehicles as $id => $v) { $v['id'] = $id; $list[] = $v; }
        }
        $this->json_success(['vehicles' => $list]);
    }

    public function save_vehicle()
    {
        $this->_require_manage();
        $id          = trim($this->input->post('id') ?? '');
        $number      = trim($this->input->post('number') ?? '');
        $type        = trim($this->input->post('type') ?? 'Bus');
        $capacity    = max(1, (int) ($this->input->post('capacity') ?? 40));
        $driverName  = trim($this->input->post('driver_name') ?? '');
        $driverPhone = trim($this->input->post('driver_phone') ?? '');
        $staffId     = trim($this->input->post('staff_id') ?? '');
        $insuranceNo = trim($this->input->post('insurance_no') ?? '');
        $insuranceExp = trim($this->input->post('insurance_expiry') ?? '');
        $fitnessExp  = trim($this->input->post('fitness_expiry') ?? '');
        $gpsEnabled  = ($this->input->post('gps_enabled') ?? '0') === '1';

        if ($number === '') $this->json_error('Vehicle number is required.');

        $isNew = ($id === '');
        if ($isNew) {
            $id = $this->operations_accounting->next_id($this->_counters('Vehicle'), 'VH');
        } else {
            $id = $this->safe_path_segment($id, 'vehicle_id');
        }

        $data = [
            'number'           => $number,
            'type'             => $type,
            'capacity'         => $capacity,
            'driver_name'      => $driverName,
            'driver_phone'     => $driverPhone,
            'staff_id'         => $staffId,
            'insurance_no'     => $insuranceNo,
            'insurance_expiry' => $insuranceExp,
            'fitness_expiry'   => $fitnessExp,
            'gps_enabled'      => $gpsEnabled,
            'status'           => 'Active',
            'updated_at'       => date('c'),
        ];
        if ($isNew) $data['created_at'] = date('c');

        $this->firebase->set($this->_vehicles($id), $data);
        $this->json_success(['id' => $id, 'message' => 'Vehicle saved.']);
    }

    public function delete_vehicle()
    {
        $this->_require_manage();
        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'vehicle_id');

        // Check if routes use this vehicle
        $routes = $this->firebase->get($this->_routes());
        if (is_array($routes)) {
            foreach ($routes as $r) {
                if (($r['vehicle_id'] ?? '') === $id && ($r['status'] ?? '') === 'Active') {
                    $this->json_error('Cannot delete: vehicle is assigned to an active route.');
                }
            }
        }
        $this->firebase->delete($this->_vehicles(), $id);
        $this->json_success(['message' => 'Vehicle deleted.']);
    }

    // ====================================================================
    //  ROUTES
    // ====================================================================

    public function get_routes()
    {
        $this->_require_view();
        $routes = $this->firebase->get($this->_routes());
        $list = [];
        if (is_array($routes)) {
            foreach ($routes as $id => $r) { $r['id'] = $id; $list[] = $r; }
        }
        $this->json_success(['routes' => $list]);
    }

    public function save_route()
    {
        $this->_require_manage();
        $id         = trim($this->input->post('id') ?? '');
        $name       = trim($this->input->post('name') ?? '');
        $vehicleId  = trim($this->input->post('vehicle_id') ?? '');
        $startPoint = trim($this->input->post('start_point') ?? '');
        $endPoint   = trim($this->input->post('end_point') ?? '');
        $distanceKm = (float) ($this->input->post('distance_km') ?? 0);
        $monthlyFee = (float) ($this->input->post('monthly_fee') ?? 0);

        if ($name === '') $this->json_error('Route name is required.');

        $isNew = ($id === '');
        if ($isNew) {
            $id = $this->operations_accounting->next_id($this->_counters('Route'), 'RT');
        } else {
            $id = $this->safe_path_segment($id, 'route_id');
        }

        $data = [
            'name'        => $name,
            'vehicle_id'  => $vehicleId,
            'start_point' => $startPoint,
            'end_point'   => $endPoint,
            'distance_km' => $distanceKm,
            'monthly_fee' => $monthlyFee,
            'status'      => 'Active',
            'updated_at'  => date('c'),
        ];
        if ($isNew) $data['created_at'] = date('c');

        $this->firebase->set($this->_routes($id), $data);
        $this->json_success(['id' => $id, 'message' => 'Route saved.']);
    }

    public function delete_route()
    {
        $this->_require_manage();
        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'route_id');

        // Check student assignments
        $assignments = $this->firebase->get($this->_assignments());
        if (is_array($assignments)) {
            foreach ($assignments as $a) {
                if (($a['route_id'] ?? '') === $id) {
                    $this->json_error('Cannot delete: students are assigned to this route.');
                }
            }
        }
        // Delete associated stops
        $stops = $this->firebase->get($this->_stops());
        if (is_array($stops)) {
            foreach ($stops as $sid => $s) {
                if (($s['route_id'] ?? '') === $id) {
                    $this->firebase->delete($this->_stops(), $sid);
                }
            }
        }
        $this->firebase->delete($this->_routes(), $id);
        $this->json_success(['message' => 'Route and associated stops deleted.']);
    }

    // ====================================================================
    //  STOPS
    // ====================================================================

    /** GET — Stops for a route. ?route_id=RT0001 */
    public function get_stops()
    {
        $this->_require_view();
        $routeId = trim($this->input->get('route_id') ?? '');

        $stops = $this->firebase->get($this->_stops());
        $list = [];
        if (is_array($stops)) {
            foreach ($stops as $id => $s) {
                if ($routeId !== '' && ($s['route_id'] ?? '') !== $routeId) continue;
                $s['id'] = $id;
                $list[] = $s;
            }
        }
        usort($list, function ($a, $b) {
            return ((int) ($a['order'] ?? 0)) - ((int) ($b['order'] ?? 0));
        });
        $this->json_success(['stops' => $list]);
    }

    public function save_stop()
    {
        $this->_require_manage();
        $id         = trim($this->input->post('id') ?? '');
        $routeId    = $this->safe_path_segment(trim($this->input->post('route_id') ?? ''), 'route_id');
        $name       = trim($this->input->post('name') ?? '');
        $pickupTime = trim($this->input->post('pickup_time') ?? '');
        $dropTime   = trim($this->input->post('drop_time') ?? '');
        $order      = max(0, (int) ($this->input->post('order') ?? 0));

        if ($name === '') $this->json_error('Stop name is required.');

        $isNew = ($id === '');
        if ($isNew) {
            $id = $this->operations_accounting->next_id($this->_counters('Stop'), 'STP');
        } else {
            $id = $this->safe_path_segment($id, 'stop_id');
        }

        $data = [
            'route_id'    => $routeId,
            'name'        => $name,
            'pickup_time' => $pickupTime,
            'drop_time'   => $dropTime,
            'order'       => $order,
            'status'      => 'Active',
            'updated_at'  => date('c'),
        ];
        if ($isNew) $data['created_at'] = date('c');

        $this->firebase->set($this->_stops($id), $data);
        $this->json_success(['id' => $id, 'message' => 'Stop saved.']);
    }

    public function delete_stop()
    {
        $this->_require_manage();
        $id = $this->safe_path_segment(trim($this->input->post('id') ?? ''), 'stop_id');
        $this->firebase->delete($this->_stops(), $id);
        $this->json_success(['message' => 'Stop deleted.']);
    }

    // ====================================================================
    //  STUDENT ASSIGNMENTS
    // ====================================================================

    public function get_assignments()
    {
        $this->_require_view();
        $assignments = $this->firebase->get($this->_assignments());
        $list = [];
        if (is_array($assignments)) {
            foreach ($assignments as $sid => $a) { $a['student_id'] = $sid; $list[] = $a; }
        }
        $this->json_success(['assignments' => $list]);
    }

    public function save_assignment()
    {
        $this->_require_manage();
        $studentId = $this->safe_path_segment(trim($this->input->post('student_id') ?? ''), 'student_id');
        $routeId   = $this->safe_path_segment(trim($this->input->post('route_id') ?? ''), 'route_id');
        $stopId    = trim($this->input->post('stop_id') ?? '');
        $type      = trim($this->input->post('type') ?? 'both');

        if (!in_array($type, ['pickup', 'drop', 'both'], true)) $type = 'both';

        // Verify student
        $student = $this->firebase->get("Users/Parents/{$this->school_name}/{$studentId}");
        if (!is_array($student)) $this->json_error('Student not found.');

        // Verify route
        $route = $this->firebase->get($this->_routes($routeId));
        if (!is_array($route)) $this->json_error('Route not found.');

        $data = [
            'route_id'      => $routeId,
            'route_name'    => $route['name'] ?? '',
            'stop_id'       => $stopId,
            'type'          => $type,
            'student_name'  => $student['Name'] ?? $studentId,
            'student_class' => ($student['Class'] ?? '') . ' ' . ($student['Section'] ?? ''),
            'monthly_fee'   => (float) ($route['monthly_fee'] ?? 0),
            'assigned_date' => date('Y-m-d'),
            'assigned_by'   => $this->admin_name,
            'status'        => 'Active',
            'updated_at'    => date('c'),
        ];

        $this->firebase->set($this->_assignments($studentId), $data);

        // Write transport fee component for fee collection integration
        $feePath = "Schools/{$this->school_name}/Fees/Student_Fee_Items/{$studentId}/Transport";
        $this->firebase->set($feePath, [
            'route_id'       => $routeId,
            'route_name'     => $route['name'] ?? '',
            'monthly_fee'    => (float) ($route['monthly_fee'] ?? 0),
            'effective_from' => date('Y-m-d'),
            'status'         => 'active',
            'updated_at'     => date('c'),
        ]);

        $this->json_success(['message' => "Student assigned to route {$route['name']}."]);
    }

    public function delete_assignment()
    {
        $this->_require_manage();
        $studentId = $this->safe_path_segment(trim($this->input->post('student_id') ?? ''), 'student_id');

        // Disable transport fee component
        $feePath = "Schools/{$this->school_name}/Fees/Student_Fee_Items/{$studentId}/Transport";
        $existing = $this->firebase->get($feePath);
        if (is_array($existing)) {
            $this->firebase->update($feePath, [
                'status'      => 'inactive',
                'removed_at'  => date('c'),
                'updated_at'  => date('c'),
            ]);
        }

        $this->firebase->delete($this->_assignments(), $studentId);
        $this->json_success(['message' => 'Assignment removed.']);
    }

    /** GET — Search students for assignment. ?q=name */
    public function search_students()
    {
        $this->_require_view();
        $results = $this->operations_accounting->search_students(
            $this->input->get('q') ?? ''
        );
        // Merge class+section for Transport's expected format
        foreach ($results as &$r) {
            $r['class'] = trim(($r['class'] ?? '') . ' ' . ($r['section'] ?? ''));
            unset($r['section'], $r['user_id']);
        }
        unset($r);
        $this->json_success(['students' => $results]);
    }
}
