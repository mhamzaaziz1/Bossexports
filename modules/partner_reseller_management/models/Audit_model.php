<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Audit_model extends App_Model
{
    private $table;
    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'prm_audit_log';
    }

    public function log($actor_type, $actor_id, $action, $entity_type = null, $entity_id = null, $before = null, $after = null, $severity = 'info')
    {
        $this->db->insert($this->table, [
            'actor_type'   => $actor_type,
            'actor_id'     => $actor_id,
            'action'       => $action,
            'entity_type'  => $entity_type,
            'entity_id'    => $entity_id,
            'before_state' => $before ? json_encode($before) : null,
            'after_state'  => $after  ? json_encode($after)  : null,
            'ip_address'   => $this->input->ip_address(),
            'user_agent'   => substr((string)$this->input->user_agent(), 0, 255),
            'severity'     => $severity,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function recent($limit = 100, array $where = [])
    {
        if ($where) $this->db->where($where);
        return $this->db->order_by('id', 'desc')->limit($limit)->get($this->table)->result();
    }
}
