<?php
defined('BASEPATH') || exit('No direct script access allowed');

use Carbon\Carbon;

class N8n_logs_model extends App_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'n8n_webhook_logs';
    }

    public function get($id = null, $where = [], $limit = 50, $offset = 0)
    {
        if ($id) {
            $this->db->where('id', $id);
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('triggered_at', 'DESC');
        $this->db->limit($limit, $offset);

        $result = $this->db->get($this->table);

        if ($id) {
            $log = $result->row();
            if ($log) {
                $log->payload = $log->payload ? json_decode($log->payload, true) : null;
                $log->request_headers = $log->request_headers ? json_decode($log->request_headers, true) : null;
            }
            return $log;
        }

        $logs = $result->result_array();
        foreach ($logs as &$log) {
            $log->payload = $log->payload ? json_decode($log->payload, true) : null;
            $log->request_headers = $log->request_headers ? json_decode($log->request_headers, true) : null;
        }

        return $logs;
    }

    public function add($data)
    {
        if (isset($data['payload']) && is_array($data['payload'])) {
            $data['payload'] = json_encode($data['payload']);
        }
        if (isset($data['request_headers']) && is_array($data['request_headers'])) {
            $data['request_headers'] = json_encode($data['request_headers']);
        }

        $data['triggered_at'] = Carbon::now()->toDateTimeString();

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function update($id, $data)
    {
        if (isset($data['payload']) && is_array($data['payload'])) {
            $data['payload'] = json_encode($data['payload']);
        }
        if (isset($data['request_headers']) && is_array($data['request_headers'])) {
            $data['request_headers'] = json_encode($data['request_headers']);
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
        }

        return $this->db->delete($this->table);
    }

    public function clean_old_logs($days = null)
    {
        if ($days === null) {
            $days = get_option('n8n_connector_log_retention_days');
        }

        if ($days > 0) {
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $this->db->where('triggered_at <', $cutoff_date);
            $this->db->delete($this->table);

            return $this->db->affected_rows();
        }

        return 0;
    }

    public function get_recent_errors($limit = 10)
    {
        $this->db->where('status', 'failed');
        $this->db->order_by('triggered_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get($this->table)->result();
    }

    public function count_logs($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Count all logs
     */
    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Get recent logs
     */
    public function get_recent($limit = 10)
    {
        $this->db->order_by('triggered_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Get success rate percentage
     */
    public function get_success_rate()
    {
        $total = $this->count_all();

        if ($total == 0) {
            return 0;
        }

        $this->db->where('status', 'success');
        $success = $this->db->count_all_results($this->table);

        return ($success / $total) * 100;
    }

    /**
     * Get event statistics
     */
    public function get_event_statistics()
    {
        $this->db->select('event_type,
                          COUNT(*) as total,
                          SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                          SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed');
        $this->db->group_by('event_type');
        $this->db->order_by('total', 'DESC');
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Clear old logs
     */
    public function clear_old_logs($days = 30)
    {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->where('triggered_at <', $cutoff_date);
        return $this->db->delete($this->table);
    }
}

