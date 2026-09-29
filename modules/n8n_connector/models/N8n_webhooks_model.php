<?php
defined('BASEPATH') || exit('No direct script access allowed');

use Carbon\Carbon;

class N8n_webhooks_model extends App_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'n8n_webhooks';
    }

    public function get($id = null, $where = [])
    {
        if ($id) {
            $this->db->where('id', $id);
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        $result = $this->db->get($this->table);

        if ($id) {
            $webhook = $result->row();
            if ($webhook) {
                // Decode JSON fields
                $webhook->enabled_events = $webhook->enabled_events ? json_decode($webhook->enabled_events, true) : [];
                $webhook->custom_headers = $webhook->custom_headers ? json_decode($webhook->custom_headers, true) : [];
            }
            return $webhook;
        }

        $webhooks = $result->result();
        foreach ($webhooks as &$webhook) {
            $webhook->enabled_events = $webhook->enabled_events ? json_decode($webhook->enabled_events, true) : [];
            $webhook->custom_headers = $webhook->custom_headers ? json_decode($webhook->custom_headers, true) : [];
        }

        return $webhooks;
    }

    public function get_webhooks_for_event($event_key)
    {
        $this->db->where('is_active', 1);
        $webhooks = $this->db->get($this->table)->result();

        $filtered = [];
        foreach ($webhooks as $webhook) {
            $enabled_events = $webhook->enabled_events ? json_decode($webhook->enabled_events, true) : [];
            if (is_array($enabled_events) && in_array($event_key, $enabled_events)) {
                $webhook->enabled_events = $enabled_events;
                $webhook->custom_headers = $webhook->custom_headers ? json_decode($webhook->custom_headers, true) : [];
                $filtered[] = $webhook;
            }
        }

        return $filtered;
    }

    public function add($data)
    {
        if (empty($data['name']) || empty($data['webhook_url'])) {
            return false;
        } 

        // Set timestamps and user
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['created_by'] = get_staff_user_id();

        // Generate secret key if signatures enabled and no key provided
        if (empty($data['secret_key']) && get_option('n8n_connector_enable_signatures') == 1) {
            $data['secret_key'] = bin2hex(random_bytes(32));
        }

        // Remove null/empty values for optional fields
        $optional_fields = ['secret_key', 'custom_headers'];
        foreach ($optional_fields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
                unset($data[$field]);
            }
        }

        // Encode JSON fields
        if (isset($data['enabled_events'])) {
            if (is_array($data['enabled_events'])) {
                $data['enabled_events'] = json_encode($data['enabled_events']);
            } elseif (empty($data['enabled_events'])) {
                unset($data['enabled_events']);
            }
        }
        
        if (isset($data['custom_headers'])) {
            if (is_array($data['custom_headers'])) {
                $data['custom_headers'] = json_encode($data['custom_headers']);
            } elseif (empty($data['custom_headers'])) {
                unset($data['custom_headers']);
            }
        }

        // Ensure boolean fields have proper values
        $boolean_fields = ['include_relationships'];
        foreach ($boolean_fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field] ? 1 : 0;
            }
        }

        if ($this->db->insert($this->table, $data)) {
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }

        return false;
    }

    public function update($id, $data)
    {
        // Check if webhook exists
        $existing = $this->get($id);
        if (!$existing) {
            return false;
        }

        $data['updated_at'] = Carbon::now()->toDateTimeString();

        // Remove null/empty values for optional fields
        $optional_fields = ['secret_key', 'custom_headers'];
        foreach ($optional_fields as $field) {
            if (isset($data[$field]) && ($data[$field] === '' || $data[$field] === null)) {
                unset($data[$field]);
            }
        }

        // Encode JSON fields
        if (isset($data['enabled_events'])) {
            if (is_array($data['enabled_events'])) {
                $data['enabled_events'] = json_encode($data['enabled_events']);
            } elseif (empty($data['enabled_events'])) {
                unset($data['enabled_events']);
            }
        }
        
        if (isset($data['custom_headers'])) {
            if (is_array($data['custom_headers'])) {
                $data['custom_headers'] = json_encode($data['custom_headers']);
            } elseif (empty($data['custom_headers'])) {
                unset($data['custom_headers']);
            }
        }

        // Ensure boolean fields have proper values
        $boolean_fields = ['include_relationships'];
        foreach ($boolean_fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field] ? 1 : 0;
            }
        }

        $this->db->where('id', $id);
        if ($this->db->update($this->table, $data)) {
            return true;
        }

        return false;
    }

    public function delete($id)
    {
        // Check if webhook exists
        $webhook = $this->get($id);
        if (!$webhook) {
            return false;
        }

        // Delete related logs
        $this->db->where('webhook_id', $id);
        $this->db->delete(db_prefix() . 'n8n_webhook_logs');

        // Delete webhook
        $this->db->where('id', $id);
        if ($this->db->delete($this->table)) {
            return true;
        }

        return false;
    }

    public function increment_triggers($id, $failed = false)
    {
        $this->db->set('total_triggers', 'total_triggers + 1', FALSE);
        $this->db->set('last_triggered_at', Carbon::now()->toDateTimeString());

        if ($failed) {
            $this->db->set('total_failures', 'total_failures + 1', FALSE);
        }

        $this->db->where('id', $id);
        $this->db->update($this->table);
    }

    public function get_statistics($webhook_id = null, $days = 30)
    {
        $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
            AVG(response_time_ms) as avg_response_time
        ');

        if ($webhook_id) {
            $this->db->where('webhook_id', $webhook_id);
        }

        $this->db->where('triggered_at >=', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        $this->db->from(db_prefix() . 'n8n_webhook_logs');

        return $this->db->get()->row();
    }

    /**
     * Count all webhooks
     */
    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Count active webhooks
     */
    public function count_active()
    {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get all webhooks with optional filters
     */
    public function get_all($filters = [])
    {
        if (isset($filters['limit'])) {
            $this->db->limit($filters['limit']);
            unset($filters['limit']);
        }

        if (!empty($filters)) {
            $this->db->where($filters);
        }

        $this->db->order_by('created_at', 'DESC');
        $webhooks = $this->db->get($this->table)->result_array();

        // Decode JSON fields
        foreach ($webhooks as &$webhook) {
            if (isset($webhook['enabled_events']) && $webhook['enabled_events']) {
                $webhook['enabled_events_decoded'] = json_decode($webhook['enabled_events'], true) ?: [];
            }
        }

        return $webhooks;
    }

    /**
     * Get active webhooks for a specific event
     */
    public function get_active_webhooks_by_event($event_key)
    {
        $this->db->where('is_active', 1);
        $webhooks = $this->db->get($this->table)->result_array();

        $filtered = [];
        foreach ($webhooks as $webhook) {
            $events = !empty($webhook['enabled_events']) ? json_decode($webhook['enabled_events'], true) : [];
            if (is_array($events) && in_array($event_key, $events)) {
                $filtered[] = $webhook;
            }
        }

        return $filtered;
    }
}

