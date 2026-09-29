<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet_model extends App_Model
{
    private $ledger;
    private $payouts;
    private $partners;

    public function __construct()
    {
        parent::__construct();
        $this->ledger   = db_prefix() . 'prm_wallet_ledger';
        $this->payouts  = db_prefix() . 'prm_payouts';
        $this->partners = db_prefix() . 'prm_partners';
    }

    /* ------------------------------------------------------------------ */
    public function balance($partner_id)
    {
        $row = $this->db->select('wallet_balance, wallet_pending')
            ->where('id', $partner_id)->get($this->partners)->row();
        return $row ? ['balance' => (float)$row->wallet_balance, 'pending' => (float)$row->wallet_pending] : null;
    }

    public function ledger($partner_id, $limit = 100)
    {
        return $this->db->where('partner_id', $partner_id)
            ->order_by('id', 'desc')->limit($limit)
            ->get($this->ledger)->result();
    }

    public function entry($partner_id, $type, $amount, $reference_type = null, $reference_id = null, $description = '')
    {
        // Recalculate balance
        $bal = $this->balance($partner_id) ?: ['balance' => 0, 'pending' => 0];
        $balance_after = $bal['balance'];
        $pending_after = $bal['pending'];

        switch ($type) {
            case 'credit':  $balance_after += $amount; break;
            case 'debit':   $balance_after -= $amount; break;
            case 'hold':    $pending_after += $amount; break;
            case 'release': $pending_after -= $amount; $balance_after += $amount; break;
        }

        $this->db->insert($this->ledger, [
            'partner_id'    => $partner_id,
            'entry_type'    => $type,
            'amount'        => $amount,
            'balance_after' => $balance_after,
            'reference_type'=> $reference_type,
            'reference_id'  => $reference_id,
            'description'   => $description,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $partner_id)->update($this->partners, [
            'wallet_balance' => $balance_after,
            'wallet_pending' => $pending_after,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    /* ------------------------------------------------------------------ */
    public function request_payout($partner_id, array $commission_ids, $payment_method, $notes = '')
    {
        $total = 0;
        foreach ($commission_ids as $cid) {
            $c = $this->db->where('id', $cid)->where('partner_id', $partner_id)->where('status', 'approved')
                          ->get(db_prefix() . 'prm_commissions')->row();
            if ($c) $total += (float) $c->commission_amount;
        }

        $min = (float) get_prm_option('prm_payout_min_amount', 100);
        if ($total < $min) {
            return ['ok' => false, 'reason' => 'below_min', 'min' => $min];
        }

        $this->db->insert($this->payouts, [
            'partner_id'     => $partner_id,
            'payout_number'  => 'PO-' . date('Y') . '-' . str_pad((string)($this->db->count_all($this->payouts)+1), 5, '0', STR_PAD_LEFT),
            'commission_ids' => json_encode($commission_ids),
            'total_amount'   => $total,
            'payment_method' => $payment_method,
            'status'         => 'requested',
            'requested_at'   => date('Y-m-d H:i:s'),
            'notes'          => $notes,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $payout_id = $this->db->insert_id();
        hooks()->do_action('prm_payout_requested', $payout_id);
        audit_log('payout', 'request', $payout_id);
        return ['ok' => true, 'id' => $payout_id, 'amount' => $total];
    }

    public function approve_payout($payout_id, $staff_id = 0)
    {
        $this->db->where('id', $payout_id)->update($this->payouts, [
            'status'      => 'approved',
            'approved_by' => $staff_id,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        audit_log('payout', 'approve', $payout_id);
    }

    public function mark_payout_paid($payout_id, $reference = '')
    {
        $payout = $this->db->where('id', $payout_id)->get($this->payouts)->row();
        if (!$payout) return false;

        $this->db->where('id', $payout_id)->update($this->payouts, [
            'status'    => 'paid',
            'reference' => $reference,
            'paid_at'   => date('Y-m-d H:i:s'),
        ]);

        $cids = json_decode($payout->commission_ids, true) ?: [];
        $this->load->model('partner_reseller_management/commissions_model');
        foreach ($cids as $cid) {
            $c = $this->commissions_model->get($cid);
            if ($c) $this->commissions_model->mark_paid($cid, $payout_id, (float)$c->commission_amount);
        }
        $this->entry($payout->partner_id, 'debit', $payout->total_amount, 'payout', $payout_id, 'Payout #' . $payout->payout_number);
        audit_log('payout', 'paid', $payout_id);
        return true;
    }

    public function list_payouts($partner_id = null, array $where = [])
    {
        if ($partner_id) $this->db->where('partner_id', $partner_id);
        if ($where)      $this->db->where($where);
        return $this->db->order_by('id', 'desc')->get($this->payouts)->result();
    }
}
