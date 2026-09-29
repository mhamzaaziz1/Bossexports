<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mdf_model extends App_Model
{
    private $funds;
    private $campaigns;

    public function __construct()
    {
        parent::__construct();
        $this->funds     = db_prefix() . 'prm_mdf_funds';
        $this->campaigns = db_prefix() . 'prm_mdf_campaigns';
    }

    /* FUNDS ----------------------------------------------------------- */
    public function allocate($partner_id, $year, $amount, $expires_at = null)
    {
        $existing = $this->db->where(['partner_id' => $partner_id, 'fiscal_year' => $year])->get($this->funds)->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update($this->funds, [
                'allocated'  => $existing->allocated + $amount,
                'remaining'  => $existing->remaining + $amount,
                'expires_at' => $expires_at,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return $existing->id;
        }
        $this->db->insert($this->funds, [
            'partner_id'  => $partner_id,
            'fiscal_year' => $year,
            'allocated'   => $amount,
            'used'        => 0,
            'remaining'   => $amount,
            'expires_at'  => $expires_at,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function get_funds($partner_id = null)
    {
        $this->db->select(db_prefix() . 'prm_mdf_funds.*, ' . db_prefix() . 'prm_partners.company_name as partner_company, ' . db_prefix() . 'prm_partners.currency');
        $this->db->join(db_prefix() . 'prm_partners', db_prefix() . 'prm_partners.id = ' . db_prefix() . 'prm_mdf_funds.partner_id', 'left');
        if ($partner_id) $this->db->where(db_prefix() . 'prm_mdf_funds.partner_id', $partner_id);
        return $this->db->order_by('fiscal_year', 'desc')->get($this->funds)->result();
    }

    /* CAMPAIGNS ------------------------------------------------------- */
    public function create_campaign(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->campaigns, $data);
        return $this->db->insert_id();
    }

    public function update_campaign($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update($this->campaigns, $data);
    }

    public function approve_campaign($id, $staff_id = 0)
    {
        $camp = $this->db->where('id', $id)->get($this->campaigns)->row();
        if (!$camp) return false;
        $fund = $this->db->where(['partner_id' => $camp->partner_id, 'fiscal_year' => date('Y')])->get($this->funds)->row();
        if (!$fund || $fund->remaining < $camp->budget) {
            return ['ok' => false, 'reason' => 'insufficient_fund'];
        }
        $this->db->where('id', $fund->id)->update($this->funds, [
            'used'      => $fund->used + $camp->budget,
            'remaining' => $fund->remaining - $camp->budget,
            'updated_at'=> date('Y-m-d H:i:s'),
        ]);
        $this->update_campaign($id, ['status' => 'approved', 'fund_id' => $fund->id]);
        audit_log('mdf_campaign', 'approve', $id);
        return ['ok' => true];
    }

    public function list_campaigns($partner_id = null, array $where = [])
    {
        $this->db->select(db_prefix() . 'prm_mdf_campaigns.*, ' . db_prefix() . 'prm_partners.company_name as partner_company');
        $this->db->join(db_prefix() . 'prm_partners', db_prefix() . 'prm_partners.id = ' . db_prefix() . 'prm_mdf_campaigns.partner_id', 'left');
        if ($partner_id) $this->db->where(db_prefix() . 'prm_mdf_campaigns.partner_id', $partner_id);
        if ($where)      $this->db->where($where);
        return $this->db->order_by('id', 'desc')->get($this->campaigns)->result();
    }
}
