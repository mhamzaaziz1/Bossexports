<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Commissions_model extends App_Model
{
    private $table;
    private $recurring;
    private $clawbacks;

    public function __construct()
    {
        parent::__construct();
        $this->table     = db_prefix() . 'prm_commissions';
        $this->recurring = db_prefix() . 'prm_recurring_commissions';
        $this->clawbacks = db_prefix() . 'prm_clawbacks';
    }

    public function get($id = null, array $where = [])
    {
        if ($id) return $this->db->where('id', $id)->get($this->table)->row();
        if ($where) $this->db->where($where);
        return $this->db->get($this->table)->result();
    }

    public function by_partner($partner_id, array $where = [])
    {
        $this->db->where('partner_id', $partner_id);
        if ($where) $this->db->where($where);
        return $this->db->order_by('id', 'desc')->get($this->table)->result();
    }

    public function add(array $data)
    {
        $data['commission_number'] = $data['commission_number'] ?? $this->generate_number();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        if ($id) {
            hooks()->do_action('prm_commission_created', $id);
            audit_log('commission', 'create', $id);
        }
        return $id;
    }

    public function update($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update($this->table, $data);
        audit_log('commission', 'update', $id);
        return $this->db->affected_rows() > 0;
    }

    public function approve($id, $staff_id = 0)
    {
        $this->update($id, [
            'status'      => 'approved',
            'approved_by' => $staff_id,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        hooks()->do_action('prm_commission_approved', $id);
    }

    public function mark_paid($id, $payout_id, $paid_amount, $paid_date = null)
    {
        $this->update($id, [
            'status'      => 'paid',
            'paid_amount' => $paid_amount,
            'paid_date'   => $paid_date ?: date('Y-m-d'),
            'payout_id'   => $payout_id,
        ]);
        hooks()->do_action('prm_commission_paid', $id);
    }

    public function generate_number()
    {
        $y    = date('Y');
        $next = ($this->db->where("YEAR(created_at)", $y)->count_all_results($this->table) + 1);
        return sprintf('COM-%s-%05d', $y, $next);
    }

    /* =================================================================
     *  CLAWBACKS
     * =============================================================== */
    public function clawback($commission_id, $reason, $reason_code = 'other', $amount = null)
    {
        $com = $this->get($commission_id);
        if (!$com) return false;
        $amt = $amount ?? $com->commission_amount;
        $this->db->insert($this->clawbacks, [
            'commission_id' => $commission_id,
            'partner_id'    => $com->partner_id,
            'amount'        => $amt,
            'reason'        => $reason,
            'reason_code'   => $reason_code,
            'created_by'    => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->update($commission_id, ['status' => 'clawed_back']);
        hooks()->do_action('prm_commission_clawed_back', $commission_id);
        return $this->db->insert_id();
    }

    /* =================================================================
     *  RECURRING
     * =============================================================== */
    public function add_recurring(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->recurring, $data);
        return $this->db->insert_id();
    }

    public function due_recurring()
    {
        return $this->db
            ->where('status', 'active')
            ->where('next_run_date <=', date('Y-m-d'))
            ->get($this->recurring)->result();
    }

    public function increment_recurring_cycle($recurring_id)
    {
        $row = $this->db->where('id', $recurring_id)->get($this->recurring)->row();
        if (!$row) return;

        $next = null;
        switch ($row->period) {
            case 'monthly':   $next = date('Y-m-d', strtotime('+1 month',   strtotime($row->next_run_date))); break;
            case 'quarterly': $next = date('Y-m-d', strtotime('+3 months',  strtotime($row->next_run_date))); break;
            case 'yearly':    $next = date('Y-m-d', strtotime('+1 year',    strtotime($row->next_run_date))); break;
        }
        $cycles_paid = $row->cycles_paid + 1;
        $update = [
            'cycles_paid'   => $cycles_paid,
            'next_run_date' => $next,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        if ($row->cycles_total > 0 && $cycles_paid >= $row->cycles_total) {
            $update['status'] = 'ended';
        }
        $this->db->where('id', $recurring_id)->update($this->recurring, $update);
    }
}
