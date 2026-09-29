<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partners_model
 * Master CRUD + hierarchy + KYC + tier evaluation.
 */
class Partners_model extends App_Model
{
    private $table;
    private $hier;
    private $tiers;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'prm_partners';
        $this->hier  = db_prefix() . 'prm_partner_hierarchy';
        $this->tiers = db_prefix() . 'prm_partner_tier_levels';
    }

    /* ------------------------------------------------------------------ */
    public function get($id = null, array $where = [])
    {
        if ($id) {
            return $this->db->where('id', $id)->get($this->table)->row();
        }
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->get($this->table)->result();
    }

    public function get_by_email($email)
    {
        return $this->db->where('email', $email)->get($this->table)->row();
    }

    public function get_by_username($username)
    {
        return $this->db->where('username', $username)->get($this->table)->row();
    }

    public function search($q)
    {
        return $this->db
            ->group_start()
                ->like('company_name', $q)
                ->or_like('email', $q)
                ->or_like('partner_code', $q)
                ->or_like('phone', $q)
            ->group_end()
            ->get($this->table)->result();
    }

    /* ------------------------------------------------------------------ */
    public function add(array $data)
    {
        $data['partner_code']   = $data['partner_code']   ?? $this->generate_code();
        $data['password_hash']  = !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null;
        unset($data['password']);
        $data['created_at']     = date('Y-m-d H:i:s');
        $data['created_by']     = function_exists('get_staff_user_id') ? get_staff_user_id() : null;

        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        if ($id) {
            $this->update_hierarchy($id, (int) ($data['parent_partner_id'] ?? 0));
            hooks()->do_action('prm_partner_created', $id);
            audit_log('partner', 'create', $id);
        }
        return $id;
    }

    public function update($id, array $data)
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update($this->table, $data);

        if (isset($data['parent_partner_id'])) {
            $this->update_hierarchy($id, (int) $data['parent_partner_id']);
        }
        hooks()->do_action('prm_partner_updated', $id);
        audit_log('partner', 'update', $id);
        return $this->db->affected_rows() > 0;
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->delete($this->table);
        $this->db->where('descendant_id', $id)->or_where('ancestor_id', $id)->delete($this->hier);
        hooks()->do_action('prm_partner_deleted', $id);
        audit_log('partner', 'delete', $id);
        return $this->db->affected_rows() > 0;
    }

    /* ------------------------------------------------------------------ */
    public function generate_code()
    {
        $prefix = strtoupper(substr(get_option('companyname') ?: 'PRT', 0, 3));
        $next   = ($this->db->count_all($this->table) + 1);
        return sprintf('%s-%05d', $prefix, $next);
    }

    /* =================================================================
     *  HIERARCHY (closure table)
     * =============================================================== */
    private function update_hierarchy($descendant_id, $parent_id)
    {
        // Wipe existing entries for this descendant
        $this->db->where('descendant_id', $descendant_id)->delete($this->hier);
        // self-reference depth 0
        $this->db->insert($this->hier, [
            'ancestor_id'   => $descendant_id,
            'descendant_id' => $descendant_id,
            'depth'         => 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        if ($parent_id <= 0) return;

        // Inherit ancestors of parent
        $rows = $this->db->where('descendant_id', $parent_id)->get($this->hier)->result();
        foreach ($rows as $r) {
            $this->db->insert($this->hier, [
                'ancestor_id'   => $r->ancestor_id,
                'descendant_id' => $descendant_id,
                'depth'         => $r->depth + 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function get_descendants($partner_id, $depth = null)
    {
        $this->db->where('ancestor_id', $partner_id)->where('descendant_id !=', $partner_id);
        if ($depth !== null) {
            $this->db->where('depth <=', $depth);
        }
        return $this->db->get($this->hier)->result();
    }

    public function get_ancestors($partner_id)
    {
        return $this->db
            ->where('descendant_id', $partner_id)
            ->where('ancestor_id !=', $partner_id)
            ->order_by('depth', 'asc')
            ->get($this->hier)->result();
    }

    /* =================================================================
     *  TIER EVALUATION — auto-promote based on revenue/deals
     * =============================================================== */
    public function recalculate_tier($partner_id)
    {
        $partner = $this->get($partner_id);
        if (!$partner) return false;

        $tier = $this->db
            ->where('min_revenue <=', $partner->total_revenue)
            ->where('min_deals <=',   $partner->total_deals)
            ->order_by('priority', 'desc')
            ->limit(1)
            ->get($this->tiers)->row();

        if ($tier && (int)$tier->id !== (int)$partner->tier_level_id) {
            $this->db->where('id', $partner_id)->update($this->table, [
                'tier_level_id' => $tier->id,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            hooks()->do_action('prm_partner_tier_changed', $partner_id, $tier->id);
            audit_log('partner', 'tier_change', $partner_id, ['tier_id' => $tier->id]);
            return $tier;
        }
        return false;
    }

    /* =================================================================
     *  AUTH
     * =============================================================== */
    public function authenticate($username, $password)
    {
        $partner = $this->db
            ->group_start()
                ->where('username', $username)
                ->or_where('email', $username)
            ->group_end()
            ->get($this->table)->row();

        if (!$partner)                                return ['ok'=>false, 'reason'=>'not_found'];
        if ($partner->status !== 'active')            return ['ok'=>false, 'reason'=>'inactive'];
        if (!$partner->portal_access)                 return ['ok'=>false, 'reason'=>'no_portal'];
        if (!empty($partner->locked_until) && strtotime($partner->locked_until) > time()) {
            return ['ok'=>false, 'reason'=>'locked', 'until'=>$partner->locked_until];
        }
        if (!password_verify($password, (string) $partner->password_hash)) {
            $this->register_failed_login($partner->id);
            return ['ok'=>false, 'reason'=>'bad_password'];
        }

        $this->db->where('id', $partner->id)->update($this->table, [
            'last_login'            => date('Y-m-d H:i:s'),
            'last_login_ip'         => $this->input->ip_address(),
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);
        return ['ok'=>true, 'partner'=>$partner];
    }

    private function register_failed_login($partner_id)
    {
        $max     = (int) get_prm_option('prm_max_login_attempts', 5);
        $minutes = (int) get_prm_option('prm_login_lockout_minutes', 30);

        $this->db->set('failed_login_attempts', 'failed_login_attempts + 1', false)
                 ->where('id', $partner_id)->update($this->table);

        $row = $this->get($partner_id);
        if ($row && $row->failed_login_attempts >= $max) {
            $this->db->where('id', $partner_id)->update($this->table, [
                'locked_until' => date('Y-m-d H:i:s', strtotime("+{$minutes} minutes")),
            ]);
        }
    }

    /* =================================================================
     *  STATS REFRESH
     * =============================================================== */
    public function refresh_stats($partner_id)
    {
        $deals = $this->db->select("COUNT(*) total, SUM(actual_value) revenue, SUM(IF(pipeline_stage='won',1,0)) wins")
            ->from(db_prefix() . 'prm_deals')
            ->where('partner_id', $partner_id)->get()->row();
        $commissions = $this->db->select("SUM(commission_amount) total")
            ->from(db_prefix() . 'prm_commissions')
            ->where('partner_id', $partner_id)->where_in('status', ['approved','paid'])->get()->row();

        $this->db->where('id', $partner_id)->update($this->table, [
            'total_deals'      => (int)($deals->wins ?: 0),
            'total_revenue'    => (float)($deals->revenue ?: 0),
            'total_commission' => (float)($commissions->total ?: 0),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->recalculate_tier($partner_id);
    }
}
