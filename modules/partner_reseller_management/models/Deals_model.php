<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deals_model extends App_Model
{
    private $table;
    private $history;

    public function __construct()
    {
        parent::__construct();
        $this->table   = db_prefix() . 'prm_deals';
        $this->history = db_prefix() . 'prm_deal_history';
    }

    public function get($id = null, array $where = [])
    {
        if ($id) return $this->db->where('id', $id)->get($this->table)->row();
        if ($where) $this->db->where($where);
        return $this->db->get($this->table)->result();
    }

    public function get_by_partner($partner_id, array $where = [])
    {
        $this->db->where('partner_id', $partner_id);
        if ($where) $this->db->where($where);
        return $this->db->order_by('id', 'desc')->get($this->table)->result();
    }

    public function pending_approval()
    {
        return $this->db
            ->where('approval_status', 'pending')
            ->order_by('submitted_at', 'asc')
            ->get($this->table)->result();
    }

    public function add(array $data)
    {
        $data['deal_number']   = $data['deal_number'] ?? $this->generate_number();
        $data['fingerprint']   = $this->fingerprint($data);
        $data['submitted_at']  = date('Y-m-d H:i:s');
        $data['lock_expires_at'] = date('Y-m-d H:i:s', strtotime('+' . (int) get_prm_option('prm_deal_lock_days', 30) . ' days'));
        $data['created_at']    = date('Y-m-d H:i:s');

        // Duplicate detection
        if ((int) get_prm_option('prm_duplicate_detection', 1) === 1) {
            $dup = $this->find_duplicate($data);
            if ($dup) {
                $data['duplicate_flag'] = 1;
                $data['duplicate_of']   = $dup->id;
            }
        }

        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        if ($id) {
            $this->log_event($id, 'submitted', null, $data['pipeline_stage'] ?? 'submitted', 'Deal submitted by partner', 'partner', $data['partner_id']);
            hooks()->do_action('prm_deal_submitted', $id);
            audit_log('deal', 'create', $id);
        }
        return $id;
    }

    public function update($id, array $data)
    {
        $before = $this->get($id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update($this->table, $data);

        if ($before && isset($data['pipeline_stage']) && $data['pipeline_stage'] !== $before->pipeline_stage) {
            $this->log_event($id, 'stage_change', $before->pipeline_stage, $data['pipeline_stage'], 'Stage updated');
            hooks()->do_action('prm_deal_stage_changed', $id, $before->pipeline_stage, $data['pipeline_stage']);
        }
        audit_log('deal', 'update', $id, ['changed' => array_keys($data)]);
        return $this->db->affected_rows() > 0;
    }

    public function approve($id, $staff_id = 0, $notes = '')
    {
        $this->update($id, [
            'approval_status' => 'approved',
            'pipeline_stage'  => 'approved',
            'approved_at'     => date('Y-m-d H:i:s'),
            'approved_by'     => $staff_id,
            'admin_notes'     => $notes,
        ]);
        $this->log_event($id, 'approved', null, 'approved', $notes, 'staff', $staff_id);
        hooks()->do_action('prm_deal_approved', $id);
        return true;
    }

    public function reject($id, $staff_id = 0, $reason = '')
    {
        $this->update($id, [
            'approval_status'   => 'rejected',
            'pipeline_stage'    => 'lost',
            'approved_at'       => date('Y-m-d H:i:s'),
            'approved_by'       => $staff_id,
            'rejection_reason'  => $reason,
        ]);
        $this->log_event($id, 'rejected', null, 'lost', $reason, 'staff', $staff_id);
        hooks()->do_action('prm_deal_rejected', $id, $reason);
        return true;
    }

    public function close_won($id, $actual_value)
    {
        $this->update($id, [
            'pipeline_stage'  => 'won',
            'actual_value'    => $actual_value,
            'closed_at'       => date('Y-m-d H:i:s'),
        ]);
        hooks()->do_action('prm_deal_won', $id, $actual_value);
    }

    public function close_lost($id, $reason = '')
    {
        $this->update($id, [
            'pipeline_stage'   => 'lost',
            'rejection_reason' => $reason,
            'closed_at'        => date('Y-m-d H:i:s'),
        ]);
        hooks()->do_action('prm_deal_lost', $id);
    }

    /* =================================================================
     *  DUPLICATE / FINGERPRINT
     * =============================================================== */
    public function fingerprint(array $d)
    {
        $key = strtolower(trim(($d['email'] ?? '') . '|' . ($d['phone'] ?? '') . '|' . ($d['company_name'] ?? '')));
        return hash('sha256', preg_replace('/\s+/', '', $key));
    }

    public function find_duplicate(array $d)
    {
        $fingerprint = $this->fingerprint($d);

        // Exact fingerprint match
        $exact = $this->db
            ->where('fingerprint', $fingerprint)
            ->where('lock_expires_at >=', date('Y-m-d H:i:s'))
            ->where_not_in('pipeline_stage', ['lost','withdrawn','expired'])
            ->limit(1)->get($this->table)->row();
        if ($exact) return $exact;

        // Fuzzy match — same email or phone or company name within lock window
        $row = $this->db
            ->group_start()
                ->where('email', $d['email'] ?? '')
                ->or_where('phone', $d['phone'] ?? '')
                ->or_like('company_name', $d['company_name'] ?? '')
            ->group_end()
            ->where('lock_expires_at >=', date('Y-m-d H:i:s'))
            ->where_not_in('pipeline_stage', ['lost','withdrawn','expired'])
            ->limit(1)->get($this->table)->row();

        return $row ?: null;
    }

    public function generate_number()
    {
        $y    = date('Y');
        $next = ($this->db->where("YEAR(created_at)", $y)->count_all_results($this->table) + 1);
        return sprintf('DEAL-%s-%05d', $y, $next);
    }

    public function log_event($deal_id, $event, $from = null, $to = null, $note = '', $actor_type = 'system', $actor_id = null)
    {
        $this->db->insert($this->history, [
            'deal_id'     => $deal_id,
            'event'       => $event,
            'from_stage'  => $from,
            'to_stage'    => $to,
            'note'        => $note,
            'actor_type'  => $actor_type,
            'actor_id'    => $actor_id,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_history($deal_id)
    {
        return $this->db->where('deal_id', $deal_id)->order_by('id', 'desc')->get($this->history)->result();
    }

    public function pipeline_summary($partner_id = null)
    {
        if ($partner_id) $this->db->where('partner_id', $partner_id);
        $rows = $this->db->select('pipeline_stage, COUNT(*) c, SUM(estimated_value) v')
            ->group_by('pipeline_stage')->get($this->table)->result();
        $out = [];
        foreach ($rows as $r) $out[$r->pipeline_stage] = ['count' => (int)$r->c, 'value' => (float)$r->v];
        return $out;
    }
}
