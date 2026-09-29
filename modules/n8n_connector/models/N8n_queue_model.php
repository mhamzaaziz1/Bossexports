<?php

defined('BASEPATH') || exit('No direct script access allowed');

use Carbon\Carbon;

/**
 * N8n Queue Model
 *
 * Manages background webhook queue using the existing n8n_webhook_logs table
 * with status='pending' for queued webhooks
 */
class N8n_queue_model extends App_Model
{
    protected $logs_table;
    protected $queue_table;

    public function __construct()
    {
        parent::__construct();
        $this->logs_table = db_prefix() . 'n8n_webhook_logs';
        $this->queue_table = db_prefix() . 'n8n_webhook_queue';
    }

    /**
     * Insert new webhook job into queue
     *
     * @param array $data Webhook job data
     * @return int|bool Queue ID on success, false on failure
     */
    public function insert_job($data)
    {
        // Generate unique event ID
        $event_id = uniqid('n8n_bg_', true);

        // Always initialize
        $log_id = null;

        // 1. Create log entry ONLY if logging enabled
        if (get_option('n8n_connector_enable_logging') == 1) {

            $log_data = [
                'webhook_id'      => $data['webhook_id'],
                'event_id'        => $event_id,
                'event_type'      => $data['event_type'],
                'resource_type'   => $data['resource_type'],
                'resource_id'     => $data['resource_id'],
                'payload'         => is_array($data['payload']) ? json_encode($data['payload']) : $data['payload'],
                'request_headers' => isset($data['request_headers']) ? json_encode($data['request_headers']) : null,
                'status'          => 'pending',
                'attempt_number'  => 1,
                'triggered_at'    => Carbon::now()->toDateTimeString(),
            ];

            $this->db->insert($this->logs_table, $log_data);
            $log_id = $this->db->insert_id();
        }

        // 2. Always insert into queue
        $queue_data = [
            'log_id'      => $log_id,
            'webhook_id'  => $data['webhook_id'],
            'event_id' => $event_id,
            'event_type' => $data['event_type'],
            'resource_type' => $data['resource_type'],
            'resource_id' => $data['resource_id'],
            'payload'         => is_array($data['payload']) ? json_encode($data['payload']) : $data['payload'],
            'request_headers' => isset($data['request_headers']) ? json_encode($data['request_headers']) : null,
            'status'      => 'pending',
            'retry_count' => 0,
            'created_at'  => Carbon::now()->toDateTimeString(),
        ];

        $this->db->insert($this->queue_table, $queue_data);
        $queue_id = $this->db->insert_id();

        if ($queue_id) {
            return $queue_id;
        }

        return false;
    }


    /**
     * Get pending jobs for processing
     *
     * @param int $limit Maximum number of jobs to fetch
     * @return array Array of pending job objects (merged queue + log data)
     */
    public function get_pending_jobs($limit = 10)
    {
        $this->db->select([
            'q.id AS queue_id',
            'q.webhook_id',
            'q.retry_count',
            'q.created_at AS queued_at',
            'q.event_id',
            'q.event_type',
            'q.resource_type',
            'q.resource_id',
            'q.payload',
            'q.request_headers',
            'q.log_id'
        ]);

        $this->db->from($this->queue_table . ' q');
        $this->db->where('q.status', 'pending');
        $this->db->order_by('q.created_at', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }


    /**
     * Get jobs that need retry
     *
     * @param int $limit Maximum number of jobs to fetch
     * @return array Array of retry job objects (merged queue + log data)
     */
    public function get_retry_jobs($limit = 10)
    {
        $max_retries = get_option('n8n_connector_max_retries', 3);
        $now = Carbon::now()->toDateTimeString();

        $this->db->select([
            'q.id AS queue_id',
            'q.webhook_id',
            'q.retry_count',
            'q.next_retry_at',
            'q.created_at AS queued_at',
            'q.event_id',
            'q.event_type',
            'q.resource_type',
            'q.resource_id',
            'q.payload',
            'q.request_headers',
            'q.log_id'
        ]);

        $this->db->from($this->queue_table . ' q');
        $this->db->where('q.status', 'pending');
        $this->db->where('q.retry_count >', 0);
        $this->db->where('q.retry_count <', $max_retries);
        $this->db->where(
            "(q.next_retry_at IS NULL OR q.next_retry_at <= '{$now}')",
            null,
            false
        );
        $this->db->order_by('q.next_retry_at', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }


    /**
     * Mark job as processing
     *
     * @param int $queue_id Queue ID
     * @return bool Success status
     */
    public function mark_processing($queue_id)
    {
        $this->db->where('id', $queue_id);
        $this->db->update($this->queue_table, [
            'status' => 'processing',
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark job as completed
     *
     * @param int $queue_id Queue ID or Log ID (for backward compatibility)
     * @param int $response_code HTTP response code
     * @param string $response_body Response body
     * @param int $response_time Response time in milliseconds
     * @return bool Success status
     */
    public function mark_completed($queue_id, $response_code, $response_body = null, $response_time = 0)
    {
        // Get queue entry to find log_id
        $queue = $this->db->get_where($this->queue_table, ['id' => $queue_id])->row();

        if (!$queue) {
            return false;
        }

        // Update log entry
        if (!empty($queue->log_id)) {
            $this->db->where('id', $queue->log_id);
            $this->db->update($this->logs_table, [
                'status'           => 'success',
                'response_code'    => $response_code,
                'response_body'    => $response_body,
                'response_time_ms' => $response_time,
                'completed_at'     => Carbon::now()->toDateTimeString(),
                'error_message'    => null,
            ]);
        }
        // Remove from queue (job completed)
        $this->db->where('id', $queue->id);
        $this->db->update($this->queue_table, [
            'status'       => 'completed',
            'processed_at' => Carbon::now()->toDateTimeString(),
        ]);
        return true;
    }

    /**
     * Mark job as failed and increment retry attempts
     *
     * @param int $queue_id Queue ID or Log ID (for backward compatibility)
     * @param string $error_message Error message
     * @param int $response_code HTTP response code
     * @param string $response_body Response body
     * @return bool Success status
     */
    public function mark_failed($queue_id, $error_message, $response_code = null, $response_body = null)
    {
        // Get queue entry
        $queue = $this->db->get_where($this->queue_table, ['id' => $queue_id])->row();

        if (!$queue) {
            return false;
        }

        $max_retries = get_option('n8n_connector_max_retries', 3);
        $retry_delay = (int) get_option('n8n_connector_retry_delay', 60);

        $new_retry_count = $queue->retry_count + 1;
        $should_retry = $new_retry_count < $max_retries;

        $queue_status = $should_retry ? 'pending' : 'failed';
        $next_retry_at = $should_retry
            ? Carbon::now()->addSeconds($retry_delay)->toDateTimeString()
            : null;

        /* Update LOG (ONLY if exists) */
        if (!empty($queue->log_id)) {

            $log = $this->db
                ->get_where($this->logs_table, ['id' => $queue->log_id])
                ->row();

            if ($log) {
                $log_update = [
                    'status'         => $should_retry ? 'retry' : 'failed',
                    'attempt_number' => $log->attempt_number + 1,
                    'error_message'  => $error_message,
                    'response_code'  => $response_code,
                    'response_body'  => $response_body,
                ];

                if (!$should_retry) {
                    $log_update['completed_at'] = Carbon::now()->toDateTimeString();
                }

                $this->db->where('id', $log->id);
                $this->db->update($this->logs_table, $log_update);
            }
        }

        /* ALWAYS update QUEUE (authoritative) */
        $queue_update = [
            'status'        => $queue_status,
            'retry_count'   => $new_retry_count,
            'next_retry_at' => $next_retry_at,
        ];

        if (!$should_retry) {
            $queue_update['processed_at'] = Carbon::now()->toDateTimeString();
        }

        $this->db->where('id', $queue->id);
        $this->db->update($this->queue_table, $queue_update);

        return true;
    }


    /**
     * Get today's queue statistics
     *
     * @return array Today's statistics
     */
    public function get_today_stats()
    {
        $today_start = Carbon::today()->startOfDay()->toDateTimeString();
        $today_end = Carbon::today()->endOfDay()->toDateTimeString();

        // Total jobs created today
        $total = $this->db->where('created_at >=', $today_start)
            ->where('created_at <=', $today_end)
            ->count_all_results($this->queue_table);

        // Pending jobs
        $pending = $this->db->where('created_at >=', $today_start)
            ->where('created_at <=', $today_end)
            ->where('status', 'pending')
            ->count_all_results($this->queue_table);

        // Completed jobs today
        $completed = $this->db->where('processed_at >=', $today_start)
            ->where('processed_at <=', $today_end)
            ->where('status', 'completed')
            ->count_all_results($this->queue_table);

        // Failed jobs today
        $failed = $this->db->where('processed_at >=', $today_start)
            ->where('processed_at <=', $today_end)
            ->where('status', 'failed')
            ->count_all_results($this->queue_table);

        // Retrying jobs
        $retrying = $this->db->where('status', 'pending')
            ->where('retry_count >', 0)
            ->count_all_results($this->queue_table);

        return [
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'failed' => $failed,
            'retrying' => $retrying,
        ];
    }

    /**
     * Get queue statistics
     *
     * @return object Queue statistics
     */
    public function get_queue_stats()
    {
        $stats = new stdClass();

        // Count by status
        $this->db->select('status, COUNT(*) as count');
        $this->db->group_by('status');
        $result = $this->db->get($this->logs_table)->result();

        $stats->pending = 0;
        $stats->retry   = 0;
        $stats->success = 0;
        $stats->failed  = 0;

        foreach ($result as $row) {
            $stats->{$row->status} = (int) $row->count;
        }

        $stats->total = $stats->pending + $stats->retry + $stats->success + $stats->failed;

        // Get oldest pending job age
        $this->db->select('triggered_at');
        $this->db->where_in('status', ['pending', 'retry']);
        $this->db->order_by('triggered_at', 'ASC');
        $this->db->limit(1);
        $oldest = $this->db->get($this->logs_table)->row();

        $stats->oldest_pending_age = $oldest ? strtotime($oldest->triggered_at) : null;

        return $stats;
    }

    /**
     * Clean up old completed logs
     *
     * @param int $days_old Number of days to retain
     * @return int Number of logs deleted
     */
    public function cleanup_old_logs($days_old = 30)
    {
        $cutoff_date = Carbon::now()->subDays($days_old)->toDateTimeString();

        $this->db->where('status', 'success');
        $this->db->where('completed_at <', $cutoff_date);
        $this->db->delete($this->logs_table);

        $affected = $this->db->affected_rows();

        if ($affected > 0) {
            log_activity('n8n Background Queue: Cleaned up ' . $affected . ' old logs (older than ' . $days_old . ' days)');
        }

        return $affected;
    }

    /**
     * Manually retry a failed job
     *
     * @param int $log_id Log ID
     * @return bool Success status
     */
    public function retry_job($log_id)
    {
        $job = $this->db->get_where($this->logs_table, ['id' => $log_id])->row();

        if (!$job || !in_array($job->status, ['failed', 'retry'])) {
            return false;
        }

        $this->db->where('id', $log_id);
        $this->db->update($this->logs_table, [
            'status'         => 'pending',
            'attempt_number' => 1,
            'error_message'  => null,
            'completed_at'   => null,
        ]);

        log_activity('n8n Background Queue: Manually retrying job [ID: ' . $log_id . ']');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete queue job
     *
     * @param int $id 
     * @return array Success status
     */
    public function delete_queue($id)
    {
        $queue = $this->db
            ->select('log_id')
            ->from($this->queue_table)
            ->where('id', $id)
            ->get()
            ->row();

        if ($queue && !empty($queue->log_id)) {
            $this->db->where('id', $queue->log_id);
            $this->db->delete($this->logs_table);
        }

        $this->db->where('id', $id);
        $this->db->delete($this->queue_table);

        return [
            'type'=>'danger',
            'message' => $this->db->affected_rows() > 0 ? _l('queue_job_deleted') : _l('something_went_wrong')
        ];
    }
}
