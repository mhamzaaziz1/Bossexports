<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ai_query_builder_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get module settings
     */
    public function get_settings()
    {
        $this->db->limit(1);
        $query = $this->db->get(db_prefix() . 'ai_query_builder_settings');

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        // If no settings found, return default settings
        $default = new stdClass();
        $default->openai_api_key = '';
        $default->model = 'gpt-3.5-turbo';
        $default->max_rows = 100;

        return $default;
    }

    /**
     * Update module settings
     */
    public function update_settings($data)
    {
        $this->db->limit(1);
        $query = $this->db->get(db_prefix() . 'ai_query_builder_settings');

        if ($query->num_rows() > 0) {
            $this->db->where('id', $query->row()->id);
            $this->db->update(db_prefix() . 'ai_query_builder_settings', [
                'openai_api_key' => $data['openai_api_key'],
                'model' => $data['model'],
                'max_rows' => $data['max_rows']
            ]);
        } else {
            $this->db->insert(db_prefix() . 'ai_query_builder_settings', [
                'openai_api_key' => $data['openai_api_key'],
                'model' => $data['model'],
                'max_rows' => $data['max_rows']
            ]);
        }

        return $this->db->affected_rows() > 0;
    }

    /**
     * Log a query execution
     */
    public function log_query($data)
    {
        // Set default values for new fields if not provided
        if (!isset($data['name'])) {
            $data['name'] = null;
        }

        if (!isset($data['is_saved'])) {
            $data['is_saved'] = 0;
        }

        $this->db->insert(db_prefix() . 'ai_query_builder_logs', $data);
        return $this->db->insert_id();
    }

    /**
     * Save a query with a name
     */
    public function save_query($id, $name)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ai_query_builder_logs', [
            'name' => $name,
            'is_saved' => 1
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get query logs
     */
    public function get_logs($limit = 10, $offset = 0, $saved_only = false)
    {
        if ($saved_only) {
            $this->db->where('is_saved', 1);
        }

        $this->db->order_by('date_created', 'desc');
        $this->db->limit($limit, $offset);
        $query = $this->db->get(db_prefix() . 'ai_query_builder_logs');

        return $query->result_array();
    }

    /**
     * Get saved queries
     */
    public function get_saved_queries()
    {
        return $this->get_logs(100, 0, true);
    }

    /**
     * Get total number of logs
     */
    public function get_total_logs()
    {
        return $this->db->count_all(db_prefix() . 'ai_query_builder_logs');
    }

    /**
     * Delete a log entry
     */
    public function delete_log($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ai_query_builder_logs');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Clear all logs
     */
    public function clear_logs()
    {
        $this->db->truncate(db_prefix() . 'ai_query_builder_logs');
        return true;
    }
}
