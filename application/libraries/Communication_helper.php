<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Communication_helper — Shared library for firing automated events
 *
 * Other modules (Attendance, Fees, Examination) call fire_event() to
 * queue notifications based on configured triggers and templates.
 *
 * Usage:
 *   $this->load->library('communication_helper');
 *   $this->communication_helper->init($this->firebase, $this->school_name, $this->session_year);
 *   $this->communication_helper->fire_event('student_absent', [
 *       'student_id'   => 'STU0001',
 *       'student_name' => 'Rahul Sharma',
 *       'class'        => 'Class 9th',
 *       'section'      => 'Section A',
 *       'date'         => '2026-03-12',
 *       'parent_name'  => 'Mr. Sharma',
 *   ]);
 */
class Communication_helper
{
    private $firebase;
    private $school_name;
    private $session_year;

    const ALLOWED_EVENTS = [
        'student_absent', 'student_late', 'low_attendance',
        'fee_due', 'fee_overdue', 'fee_received',
        'exam_result', 'exam_schedule',
        'admission_approved', 'admission_rejected',
        'salary_processed', 'leave_approved',
        'event_created', 'event_updated',
    ];

    const MAX_QUEUE_PER_EVENT = 200;

    /**
     * Initialize with controller context.
     */
    public function init($firebase, string $school_name, string $session_year): void
    {
        $this->firebase     = $firebase;
        $this->school_name  = $school_name;
        $this->session_year = $session_year;
    }

    // ====================================================================
    //  FIRE EVENT — called by external modules
    // ====================================================================

    /**
     * Fire an event to trigger queued notifications.
     *
     * @param string $eventType  e.g. 'student_absent', 'fee_due', 'exam_result'
     * @param array  $data       Context data with template variable values
     * @return int               Number of messages queued
     */
    public function fire_event(string $eventType, array $data): int
    {
        // Validate event type
        if (!in_array($eventType, self::ALLOWED_EVENTS, true)) {
            log_message('error', "Communication_helper: invalid event type '{$eventType}'");
            return 0;
        }

        // Validate school_name is initialized
        if (empty($this->school_name) || empty($this->firebase)) {
            log_message('error', 'Communication_helper: not initialized. Call init() first.');
            return 0;
        }

        // Sanitize all data values — strip HTML/script content
        $data = $this->_sanitize_data($data);

        $base = "Schools/{$this->school_name}/Communication";

        // Load all triggers
        $triggers = $this->firebase->get("{$base}/Triggers");
        if (!is_array($triggers)) return 0;

        $queued = 0;

        foreach ($triggers as $trgId => $trg) {
            if (!is_array($trg)) continue;
            if ($trgId === 'Counter') continue;
            if (($trg['event_type'] ?? '') !== $eventType) continue;
            if (empty($trg['enabled'])) continue;
            if ($queued >= self::MAX_QUEUE_PER_EVENT) break;

            // Check conditions
            $conditions = $trg['conditions'] ?? [];
            if (!is_array($conditions)) $conditions = [];
            if (!$this->_check_conditions($conditions, $data)) continue;

            // Load template
            $tplId = $trg['template_id'] ?? '';
            if ($tplId === '' || !preg_match('/^TPL\d+$/', $tplId)) continue;
            $tpl = $this->firebase->get("{$base}/Templates/{$tplId}");
            if (!is_array($tpl)) continue;

            // Resolve message
            $title = $this->_replace_vars($tpl['subject'] ?? ($tpl['name'] ?? ''), $data);
            $body  = $this->_replace_vars($tpl['body'] ?? '', $data);

            // Determine recipient
            $recipientType = $trg['recipient_type'] ?? 'parent';
            if (!in_array($recipientType, ['parent', 'student', 'teacher', 'staff', 'broadcast'], true)) continue;
            $recipient = $this->_resolve_recipient($recipientType, $data);
            if (empty($recipient)) continue;

            // Queue the message
            $queueCounter = (int) ($this->firebase->get("{$base}/Counters/Queue") ?? 0) + 1;
            $this->firebase->set("{$base}/Counters/Queue", $queueCounter);
            $queueId = 'QUE' . str_pad($queueCounter, 5, '0', STR_PAD_LEFT);

            $channel = $trg['channel'] ?? 'push';
            if (!in_array($channel, ['push', 'sms', 'email', 'in_app'], true)) $channel = 'push';

            $this->firebase->set("{$base}/Queue/{$queueId}", [
                'trigger_id'        => $trgId,
                'template_id'       => $tplId,
                'channel'           => $channel,
                'recipient_type'    => $recipientType,
                'recipient_id'      => $recipient['id'] ?? '',
                'recipient_name'    => $recipient['name'] ?? '',
                'recipient_contact' => $recipient['contact'] ?? '',
                'title'             => $title,
                'message_body'      => $body,
                'status'            => 'pending',
                'priority'          => $trg['priority'] ?? 'normal',
                'attempts'          => 0,
                'max_attempts'      => 3,
                'error_message'     => '',
                'created_at'        => date('c'),
                'scheduled_at'      => date('c'),
                'sent_at'           => '',
                'source'            => 'trigger',
                'event_type'        => $eventType,
            ]);

            $queued++;
        }

        return $queued;
    }

    // ====================================================================
    //  FIRE EVENT FOR BULK — multiple recipients
    // ====================================================================

    /**
     * Fire event for a list of recipients (e.g. exam results for a class).
     *
     * @param string $eventType
     * @param array  $recipientDataList  Array of data arrays, each with recipient context
     * @return int
     */
    public function fire_event_bulk(string $eventType, array $recipientDataList): int
    {
        $total = 0;
        $cap   = self::MAX_QUEUE_PER_EVENT;
        foreach ($recipientDataList as $data) {
            if ($total >= $cap) break;
            $total += $this->fire_event($eventType, $data);
        }
        return $total;
    }

    // ====================================================================
    //  EVENT NOTICE — creates Communication/Notices + legacy fallback
    // ====================================================================

    /**
     * Write an announcement notice for a school event.
     * Creates a Communication/Notices entry (primary) and a legacy
     * All Notices entry (fallback for mobile apps).
     *
     * @param string $eventId  e.g. 'EVT0001'
     * @param array  $data     Event data (title, category, start_date, etc.)
     * @param string $adminId  Admin who created the event
     * @return string          The notice ID created
     */
    public function write_event_notice(string $eventId, array $data, string $adminId): string
    {
        if (empty($this->school_name) || empty($this->firebase)) {
            log_message('error', 'Communication_helper: not initialized for write_event_notice');
            return '';
        }

        $base    = "Schools/{$this->school_name}/Communication";
        $session = $this->session_year;
        $school  = $this->school_name;

        $categoryLabels = [
            'event'    => 'School Event',
            'cultural' => 'Cultural Program',
            'sports'   => 'Sports Competition',
        ];
        $catLabel = $categoryLabels[$data['category'] ?? 'event'] ?? 'Event';

        $title = "[{$catLabel}] " . ($data['title'] ?? '');
        $descParts = [];
        if (!empty($data['description'])) $descParts[] = $data['description'];
        if (!empty($data['start_date']))  $descParts[] = "Date: " . $data['start_date']
            . (!empty($data['end_date']) && $data['end_date'] !== $data['start_date'] ? " to " . $data['end_date'] : '');
        if (!empty($data['location']))    $descParts[] = "Venue: " . $data['location'];
        if (!empty($data['organizer']))   $descParts[] = "Organizer: " . $data['organizer'];
        $description = implode("\n", $descParts);

        // ── 1. Communication/Notices (primary) ──
        $counter  = (int) ($this->firebase->get("{$base}/Counters/Notice") ?? 0) + 1;
        $this->firebase->set("{$base}/Counters/Notice", $counter);
        $noticeId = 'NOT' . str_pad($counter, 5, '0', STR_PAD_LEFT);

        $this->firebase->set("{$base}/Notices/{$noticeId}", [
            'title'        => $title,
            'description'  => $description,
            'priority'     => 'Normal',
            'category'     => 'Event',
            'target_group' => 'All School',
            'status'       => 'published',
            'author_id'    => $adminId,
            'author_type'  => 'Admin',
            'event_ref'    => $eventId,
            'created_at'   => date('c'),
            'published_at' => date('c'),
        ]);

        // ── 2. Legacy All Notices path (fallback for mobile apps) ──
        $legacyBase  = "Schools/{$school}/{$session}/All Notices";
        $legacyCount = (int) ($this->firebase->get("{$legacyBase}/Count") ?? 0);
        $legacyNext  = $legacyCount + 1;
        $this->firebase->set("{$legacyBase}/Count", $legacyNext);
        $legacyId = 'NOT' . str_pad($legacyNext, 4, '0', STR_PAD_LEFT);

        $ts = round(microtime(true) * 1000);
        $this->firebase->set("{$legacyBase}/{$legacyId}", [
            'Title'       => $title,
            'Description' => $description,
            'From Id'     => $adminId,
            'From Type'   => 'Admin',
            'Priority'    => 'Normal',
            'Category'    => 'Event',
            'Timestamp'   => $ts,
            'To Id'       => ['All School' => ''],
        ]);

        $this->firebase->set(
            "Schools/{$school}/{$session}/Announcements/All School/{$legacyId}",
            $ts
        );

        return $noticeId;
    }

    // ====================================================================
    //  INTERNALS
    // ====================================================================

    /**
     * Sanitize all string values in data array — prevent XSS in templates.
     */
    private function _sanitize_data(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || !preg_match('/^\w+$/', $key)) continue;
            if (is_string($value)) {
                $clean[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } elseif (is_numeric($value)) {
                $clean[$key] = $value;
            }
            // Skip arrays/objects — templates only use flat values
        }
        return $clean;
    }

    /**
     * Replace {{variable}} placeholders in a string.
     * Only allows word-character keys (\w+) to prevent injection.
     */
    private function _replace_vars(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($m) use ($data) {
            return $data[$m[1]] ?? $m[0];
        }, $template);
    }

    /**
     * Check trigger conditions against event data.
     * Only supports flat key→numeric threshold comparisons.
     */
    private function _check_conditions(array $conditions, array $data): bool
    {
        if (empty($conditions)) return true;

        foreach ($conditions as $key => $threshold) {
            // Only allow alphanumeric keys
            if (!is_string($key) || !preg_match('/^\w+$/', $key)) continue;
            // Skip non-scalar thresholds
            if (is_array($threshold) || is_object($threshold)) continue;

            $actual = $data[$key] ?? null;
            if ($actual === null) continue;
            // Numeric comparison: actual must meet or exceed threshold
            if (is_numeric($threshold) && is_numeric($actual)) {
                if ((float) $actual < (float) $threshold) return false;
            }
        }
        return true;
    }

    /**
     * Resolve recipient from event data.
     */
    private function _resolve_recipient(string $type, array $data): array
    {
        switch ($type) {
            case 'parent':
                $studentId = $data['student_id'] ?? '';
                if ($studentId === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $studentId)) return [];
                $student = $this->firebase->get("Users/Parents/{$this->school_name}/{$studentId}");
                if (!is_array($student)) return [];
                return [
                    'id'      => $studentId,
                    'name'    => $data['parent_name'] ?? ($student['Father Name'] ?? ($student['Name'] ?? '')),
                    'contact' => $student['Phone'] ?? ($student['Father Phone'] ?? ''),
                ];

            case 'student':
                $sid = $data['student_id'] ?? '';
                if ($sid !== '' && !preg_match('/^[A-Za-z0-9_\-]+$/', $sid)) return [];
                return [
                    'id'      => $sid,
                    'name'    => $data['student_name'] ?? '',
                    'contact' => $data['student_phone'] ?? '',
                ];

            case 'teacher':
                $tid = $data['teacher_id'] ?? '';
                if ($tid !== '' && !preg_match('/^[A-Za-z0-9_\-]+$/', $tid)) return [];
                return [
                    'id'      => $tid,
                    'name'    => $data['teacher_name'] ?? '',
                    'contact' => $data['teacher_phone'] ?? '',
                ];

            case 'staff':
                $sid = $data['staff_id'] ?? '';
                if ($sid !== '' && !preg_match('/^[A-Za-z0-9_\-]+$/', $sid)) return [];
                return [
                    'id'      => $sid,
                    'name'    => $data['staff_name'] ?? '',
                    'contact' => $data['staff_phone'] ?? '',
                ];

            case 'broadcast':
                return [
                    'id'      => 'all_school',
                    'name'    => 'All School',
                    'contact' => '',
                ];

            default:
                return [];
        }
    }
}
