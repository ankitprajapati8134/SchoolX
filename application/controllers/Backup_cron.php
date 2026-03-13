<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Backup_cron — Lightweight public cron endpoint for automated scheduled backups.
 *
 * Does NOT require an SA session — validated by a stored cron_key instead.
 * Designed to be called by a server cron job (Linux crontab or Windows Task Scheduler).
 *
 * Usage:
 *   GET  /backup_cron/{cron_key}
 *
 * Example crontab (daily at 02:00):
 *   0 2 * * * curl -s "https://yourserver/school/backup_cron/YOUR_CRON_KEY" >> /var/log/grader_backup.log
 *
 * The cron_key is generated when the schedule is first saved via
 * /superadmin/backups/save_schedule and is stored in System/BackupSchedule/cron_key.
 */
class Backup_cron extends CI_Controller
{
    const BACKUP_DIR = 'application/backups/';

    public function __construct()
    {
        parent::__construct();
        // Output JSON always
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /backup_cron/{cron_key}
    public function run($cron_key = '')
    {
        if (empty(trim($cron_key))) {
            $this->_out('error', 'Cron key is required.', 400); return;
        }

        try {
            $this->load->library('firebase');

            $schedule = $this->firebase->get('System/BackupSchedule') ?? [];

            // Validate cron key FIRST (before checking enabled status)
            $stored_key = $schedule['cron_key'] ?? '';
            if (empty($stored_key) || !hash_equals((string)$stored_key, (string)$cron_key)) {
                $this->_out('error', 'Invalid or expired cron key.', 403); return;
            }

            if (empty($schedule['enabled'])) {
                $this->_out('skipped', 'Scheduled backups are disabled.'); return;
            }

            // Check if it's time to run (basic frequency check)
            $frequency   = $schedule['frequency']   ?? 'daily';
            $day_of_week = (int)($schedule['day_of_week'] ?? 0);
            $backup_time = $schedule['backup_time']  ?? '02:00';
            $last_run    = $schedule['last_run']     ?? '';

            if ($frequency === 'weekly' && (int)date('w') !== $day_of_week) {
                $this->_out('skipped', 'Not the scheduled day of week (' . date('l') . ').'); return;
            }

            // Prevent double-run within same hour
            if ($last_run && substr($last_run, 0, 13) === date('Y-m-d H')) {
                $this->_out('skipped', 'Already ran this hour.'); return;
            }

            set_time_limit(300);
            ini_set('memory_limit', '256M');

            $backup_type = $schedule['backup_type'] ?? 'firebase';
            $retention   = max(1, (int)($schedule['retention'] ?? 7));

            $raw     = $this->firebase->get('System/Schools') ?? [];
            $schools = array_keys(array_filter($raw, 'is_array'));

            if (empty($schools)) {
                $this->_out('error', 'No schools found in Firebase.'); return;
            }

            $succeeded = 0; $failed = 0; $results = [];

            foreach ($schools as $school_uid) {
                try {
                    $r = $this->_do_backup($school_uid, $backup_type);
                    $this->_apply_retention($school_uid, $retention);
                    $succeeded++;
                    $results[] = ['school' => $school_uid, 'status' => 'ok', 'backup_id' => $r['backup_id'], 'size' => $r['size_human']];
                } catch (Exception $e) {
                    $failed++;
                    $results[] = ['school' => $school_uid, 'status' => 'error', 'message' => $e->getMessage()];
                    log_message('error', "Cron backup failed for {$school_uid}: " . $e->getMessage());
                }
            }

            $this->firebase->update('System/BackupSchedule', [
                'last_run'       => date('Y-m-d H:i:s'),
                'last_run_count' => $succeeded,
                'last_run_by'    => 'cron',
            ]);

            // Log to Firebase activity
            try {
                $key = 'cron_' . substr(md5(uniqid('', true)), 0, 8);
                $this->firebase->update('System/Logs/Activity/' . date('Y-m-d'), [
                    $key => [
                        'action'     => 'cron_backup_run',
                        'school_uid' => '',
                        'sa_name'    => 'cron',
                        'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                        'meta'       => ['succeeded' => $succeeded, 'failed' => $failed],
                        'timestamp'  => date('Y-m-d H:i:s'),
                    ],
                ]);
            } catch (Exception $e) {}

            echo json_encode([
                'status'    => 'success',
                'ran_at'    => date('Y-m-d H:i:s'),
                'succeeded' => $succeeded,
                'failed'    => $failed,
                'results'   => $results,
                'message'   => "{$succeeded} school(s) backed up" . ($failed ? ", {$failed} failed." : '.'),
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            log_message('error', 'Backup_cron: ' . $e->getMessage());
            $this->_out('error', $e->getMessage());
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function _do_backup(string $school_uid, string $backup_type = 'firebase'): array
    {
        $backup_data = $this->firebase->get("Schools/{$school_uid}");
        if (empty($backup_data)) throw new Exception("No data found for school '{$school_uid}'.");

        $export = [
            'backup_format' => '1.2',
            'backup_type'   => $backup_type,
            'school_name'   => $school_uid,
            'firebase_key'  => $school_uid,
            'backed_up_at'  => date('Y-m-d H:i:s'),
            'backed_up_by'  => 'cron',
            'Schools'       => $backup_data,
        ];

        if ($backup_type === 'full') {
            $export['SystemConfig'] = [
                'php_version' => PHP_VERSION,
                'ci_version'  => CI_VERSION,
                'server_time' => date('Y-m-d H:i:s'),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            ];
        }

        $backup_id  = 'BKP_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6);
        $safe_uid   = preg_replace('/[^A-Za-z0-9\-]/', '_', trim($school_uid));
        $school_dir = FCPATH . self::BACKUP_DIR . $safe_uid . '/';

        if (!is_dir($school_dir)) mkdir($school_dir, 0750, true);

        $filename   = $backup_id . '.json';
        $filepath   = $school_dir . $filename;
        $json       = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $size_bytes = strlen($json);

        if (file_put_contents($filepath, $json) === false) {
            throw new Exception("Failed to write backup file for '{$school_uid}'.");
        }

        $this->firebase->set("System/Backups/{$safe_uid}/{$backup_id}", [
            'backup_id'   => $backup_id,
            'school_name' => $school_uid,
            'filename'    => $filename,
            'backup_type' => $backup_type,
            'format'      => 'json',
            'size_bytes'  => $size_bytes,
            'size_human'  => $this->_human_size($size_bytes),
            'type'        => 'scheduled',
            'status'      => 'completed',
            'created_at'  => date('Y-m-d H:i:s'),
            'created_by'  => 'cron',
        ]);

        return ['backup_id' => $backup_id, 'size_human' => $this->_human_size($size_bytes)];
    }

    private function _apply_retention(string $school_uid, int $keep): void
    {
        $safe_uid = preg_replace('/[^A-Za-z0-9\-]/', '_', trim($school_uid));
        $raw      = $this->firebase->get("System/Backups/{$safe_uid}") ?? [];
        if (!is_array($raw) || count($raw) <= $keep) return;

        $entries = [];
        foreach ($raw as $bid => $b) {
            if (!is_array($b) || strpos((string)$bid, 'SAFETY') !== false) continue;
            $entries[$bid] = $b['created_at'] ?? '';
        }
        asort($entries);

        $excess = count($entries) - $keep;
        foreach (array_slice(array_keys($entries), 0, $excess) as $bid) {
            $filename = basename($raw[$bid]['filename'] ?? '');
            if ($filename && preg_match('/^[A-Za-z0-9_\-\.]+$/', $filename)) {
                $fp = FCPATH . self::BACKUP_DIR . $safe_uid . '/' . $filename;
                if (file_exists($fp)) @unlink($fp);
            }
            $this->firebase->delete("System/Backups/{$safe_uid}", $bid);
        }
    }

    private function _human_size(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024,    1) . ' KB';
        return $bytes . ' B';
    }

    private function _out(string $status, string $message, int $http_code = 200): void
    {
        if ($http_code !== 200) http_response_code($http_code);
        echo json_encode(['status' => $status, 'message' => $message, 'ran_at' => date('Y-m-d H:i:s')]);
        exit;
    }
}
