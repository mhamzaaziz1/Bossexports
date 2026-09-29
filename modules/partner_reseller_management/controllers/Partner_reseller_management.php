<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner_reseller_management — Admin controller
 * Handles dashboard, partners, hierarchy, deals, commissions, payouts,
 * MDF, training, gamification, reports, audit, settings.
 */
class Partner_reseller_management extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('partner_reseller_management/partners_model');
        $this->load->model('partner_reseller_management/deals_model');
        $this->load->model('partner_reseller_management/commissions_model');
        $this->load->model('partner_reseller_management/wallet_model');
        $this->load->model('partner_reseller_management/mdf_model');
        $this->load->model('partner_reseller_management/training_model');
        $this->load->model('partner_reseller_management/audit_model');
        if (!is_admin() && !staff_can('view', 'partner_reseller_management')) {
            access_denied('partner_reseller_management');
        }
    }

    public function index()
    {
        redirect(admin_url('partner_reseller_management/dashboard'));
    }

    /* ============================================================
     *  AJAX TABLES
     * ========================================================== */
    public function table($type)
    {
        if (!is_admin() && !staff_can('view', 'partner_reseller_management')) {
            ajax_access_denied();
        }
        switch ($type) {
            case 'partners':
                $this->app->get_table_data(module_views_path('partner_reseller_management', 'admin/tables/partners'));
                break;
            case 'deals':
                $this->app->get_table_data(module_views_path('partner_reseller_management', 'admin/tables/deals'));
                break;
            case 'commissions':
                $this->app->get_table_data(module_views_path('partner_reseller_management', 'admin/tables/commissions'));
                break;
            case 'payouts':
                $this->app->get_table_data(module_views_path('partner_reseller_management', 'admin/tables/payouts'));
                break;
            case 'audit':
                $this->app->get_table_data(module_views_path('partner_reseller_management', 'admin/tables/audit'));
                break;
        }
    }


    /* ============================================================
     *  DASHBOARD
     * ========================================================== */
    public function dashboard()
    {
        $data['title'] = _l('prm_dashboard');
        $data['stats'] = [
            'total_partners'     => $this->db->count_all(db_prefix().'prm_partners'),
            'active_partners'    => $this->db->where('status','active')->count_all_results(db_prefix().'prm_partners'),
            'pending_partners'   => $this->db->where('status','pending')->count_all_results(db_prefix().'prm_partners'),
            'total_deals'        => $this->db->count_all(db_prefix().'prm_deals'),
            'pending_deals'      => $this->db->where('approval_status','pending')->count_all_results(db_prefix().'prm_deals'),
            'won_deals'          => $this->db->where('pipeline_stage','won')->count_all_results(db_prefix().'prm_deals'),
            'pending_commission' => (float) ($this->db->select_sum('commission_amount')->where('status','calculated')->get(db_prefix().'prm_commissions')->row()->commission_amount ?? 0),
            'paid_commission'    => (float) ($this->db->select_sum('commission_amount')->where('status','paid')->get(db_prefix().'prm_commissions')->row()->commission_amount ?? 0),
        ];
        $data['pipeline'] = $this->deals_model->pipeline_summary();
        $data['top_partners'] = $this->db->order_by('total_revenue', 'desc')->limit(10)->get(db_prefix().'prm_partners')->result();
        $data['recent_deals'] = $this->db->order_by('id','desc')->limit(10)->get(db_prefix().'prm_deals')->result();
        $this->load->view('partner_reseller_management/admin/dashboard/index', $data);
    }

    /* ============================================================
     *  PARTNERS
     * ========================================================== */
    public function partners($action = '', $id = 0)
    {
        if ($action === 'manage') {
            $partner = $id ? $this->partners_model->get($id) : null;
            if ($this->input->post()) {
                $payload = $this->input->post(null, true);
                if ($id) {
                    $this->partners_model->update($id, $payload);
                    set_alert('success', _l('prm_partner_updated'));
                } else {
                    $new_id = $this->partners_model->add($payload);
                    set_alert('success', _l('prm_partner_created'));
                    redirect(admin_url('partner_reseller_management/partners/manage/'.$new_id));
                }
                redirect(admin_url('partner_reseller_management/partners/manage/'.$id));
            }
            $data['partner']   = $partner;
            $data['tiers']     = $this->db->get(db_prefix().'prm_partner_tier_levels')->result();
            $data['parents']   = $this->db->where('id !=', (int)$id)->get(db_prefix().'prm_partners')->result();
            $data['title']     = $partner ? _l('prm_edit_partner') : _l('prm_new_partner');
            $this->load->view('partner_reseller_management/admin/partners/manage', $data);
            return;
        }

        if ($action === 'delete' && $id && (is_admin() || staff_can('delete','partner_reseller_management'))) {
            $this->partners_model->delete($id);
            set_alert('success', _l('prm_partner_deleted'));
            redirect(admin_url('partner_reseller_management/partners'));
        }

        $data['title']    = _l('prm_partners');
        $this->load->view('partner_reseller_management/admin/partners/list', $data);
    }

    /* ============================================================
     *  HIERARCHY (visual tree)
     * ========================================================== */
    public function hierarchy()
    {
        $data['title']    = _l('prm_hierarchy');
        $data['partners'] = $this->db->select('id, company_name, partner_code, parent_partner_id, tier_level_id')
            ->order_by('parent_partner_id','asc')->order_by('id','asc')
            ->get(db_prefix().'prm_partners')->result();
        $this->load->view('partner_reseller_management/admin/partners/hierarchy', $data);
    }

    /* ============================================================
     *  DEALS
     * ========================================================== */
    public function deals($action = '', $id = 0)
    {
        if ($action === 'view' && $id) {
            $data['deal']    = $this->deals_model->get($id);
            $data['history'] = $this->deals_model->get_history($id);
            $data['partner'] = $data['deal'] ? $this->partners_model->get($data['deal']->partner_id) : null;
            $data['title']   = _l('prm_deal') . ' ' . ($data['deal']->deal_number ?? '');
            $this->load->view('partner_reseller_management/admin/deals/view', $data);
            return;
        }

        if ($action === 'approve' && $id && (is_admin() || staff_can('approve_deals','partner_reseller_management'))) {
            $this->deals_model->approve($id, get_staff_user_id(), $this->input->post('admin_notes'));
            // Calculate commission
            $this->load->library('partner_reseller_management/Commission_engine', null, 'commission_engine');
            $this->commission_engine->calculate_for_deal($id);
            set_alert('success', _l('prm_deal_approved'));
            redirect(admin_url('partner_reseller_management/deals/view/'.$id));
        }

        if ($action === 'reject' && $id) {
            $this->deals_model->reject($id, get_staff_user_id(), $this->input->post('reason'));
            set_alert('success', _l('prm_deal_rejected'));
            redirect(admin_url('partner_reseller_management/deals/view/'.$id));
        }

        if ($action === 'won' && $id) {
            $this->deals_model->close_won($id, (float)$this->input->post('actual_value'));
            $this->load->library('partner_reseller_management/Commission_engine', null, 'commission_engine');
            $this->commission_engine->calculate_for_deal($id);
            set_alert('success', _l('prm_deal_marked_won'));
            redirect(admin_url('partner_reseller_management/deals/view/'.$id));
        }

        $data['title'] = _l('prm_deals');
        $this->load->view('partner_reseller_management/admin/deals/list', $data);
    }

    /* ============================================================
     *  COMMISSIONS
     * ========================================================== */
    public function commissions($action = '', $id = 0)
    {
        if ($action === 'approve' && $id && (is_admin() || staff_can('manage_commission','partner_reseller_management'))) {
            $this->commissions_model->approve($id, get_staff_user_id());
            set_alert('success', _l('prm_commission_approved'));
            redirect(admin_url('partner_reseller_management/commissions'));
        }
        if ($action === 'clawback' && $id) {
            $reason = $this->input->post('reason');
            $code   = $this->input->post('reason_code') ?: 'other';
            $this->commissions_model->clawback($id, $reason, $code);
            set_alert('success', _l('prm_commission_clawed_back'));
            redirect(admin_url('partner_reseller_management/commissions'));
        }
        $data['title']       = _l('prm_commissions');
        $this->load->view('partner_reseller_management/admin/commissions/list', $data);
    }

    /* ============================================================
     *  PAYOUTS
     * ========================================================== */
    public function payouts($action = '', $id = 0)
    {
        if ($action === 'approve' && $id && (is_admin() || staff_can('process_payouts','partner_reseller_management'))) {
            $this->wallet_model->approve_payout($id, get_staff_user_id());
            set_alert('success', _l('prm_payout_approved'));
            redirect(admin_url('partner_reseller_management/payouts'));
        }
        if ($action === 'pay' && $id) {
            $this->wallet_model->mark_payout_paid($id, $this->input->post('reference'));
            set_alert('success', _l('prm_payout_paid'));
            redirect(admin_url('partner_reseller_management/payouts'));
        }
        $data['title']   = _l('prm_payouts');
        $this->load->view('partner_reseller_management/admin/commissions/payouts', $data);
    }

    /* ============================================================
     *  MDF
     * ========================================================== */
    public function mdf($action = '', $id = 0)
    {
        if ($action === 'allocate' && $this->input->post()) {
            $this->mdf_model->allocate(
                (int)$this->input->post('partner_id'),
                (int)$this->input->post('fiscal_year'),
                (float)$this->input->post('amount'),
                $this->input->post('expires_at')
            );
            set_alert('success', _l('prm_mdf_allocated'));
            redirect(admin_url('partner_reseller_management/mdf'));
        }
        if ($action === 'approve_campaign' && $id) {
            $r = $this->mdf_model->approve_campaign($id, get_staff_user_id());
            set_alert($r['ok'] ? 'success' : 'danger', $r['ok'] ? _l('prm_campaign_approved') : _l('prm_insufficient_fund'));
            redirect(admin_url('partner_reseller_management/mdf'));
        }
        $data['title']     = _l('prm_mdf');
        $data['funds']     = $this->mdf_model->get_funds();
        $data['campaigns'] = $this->mdf_model->list_campaigns();
        $data['partners']  = $this->db->order_by('company_name','asc')->get(db_prefix().'prm_partners')->result();
        $this->load->view('partner_reseller_management/admin/mdf/index', $data);
    }

    /* ============================================================
     *  TRAINING
     * ========================================================== */
    public function training($action = '', $id = 0)
    {
        if ($action === 'add_course' && $this->input->post()) {
            $this->training_model->add_course($this->input->post(null, true));
            set_alert('success', _l('prm_course_created'));
            redirect(admin_url('partner_reseller_management/training'));
        }
        $data['title']   = _l('prm_training');
        $data['courses'] = $this->training_model->list_courses(false);
        $this->load->view('partner_reseller_management/admin/training/index', $data);
    }

    /* ============================================================
     *  GAMIFICATION
     * ========================================================== */
    public function gamification()
    {
        $data['title']  = _l('prm_gamification');
        $data['badges'] = $this->db->order_by('points','desc')->get(db_prefix().'prm_badges')->result();
        $data['leaderboard'] = $this->db
            ->select('id, company_name, partner_code, total_revenue, total_deals, lifetime_score, tier_level_id')
            ->order_by('lifetime_score','desc')
            ->limit(20)->get(db_prefix().'prm_partners')->result();
        $this->load->view('partner_reseller_management/admin/training/gamification', $data);
    }

    /* ============================================================
     *  REPORTS
     * ========================================================== */
    public function reports()
    {
        $data['title']    = _l('prm_reports');
        $data['by_month'] = $this->db->query("
            SELECT DATE_FORMAT(closed_at, '%Y-%m') AS m,
                   SUM(actual_value) AS revenue,
                   COUNT(*) AS deals
            FROM ".db_prefix()."prm_deals
            WHERE pipeline_stage='won' AND closed_at IS NOT NULL
            GROUP BY m ORDER BY m DESC LIMIT 24
        ")->result();
        $data['by_partner'] = $this->db->query("
            SELECT p.company_name, p.partner_code,
                   SUM(d.actual_value) revenue,
                   COUNT(d.id) deals,
                   SUM(c.commission_amount) commission
            FROM ".db_prefix()."prm_partners p
            LEFT JOIN ".db_prefix()."prm_deals       d ON d.partner_id=p.id AND d.pipeline_stage='won'
            LEFT JOIN ".db_prefix()."prm_commissions c ON c.partner_id=p.id AND c.status IN ('approved','paid')
            GROUP BY p.id ORDER BY revenue DESC LIMIT 50
        ")->result();
        $this->load->view('partner_reseller_management/admin/reports/index', $data);
    }

    /* ============================================================
     *  AUDIT
     * ========================================================== */
    public function audit()
    {
        if (!is_admin() && !staff_can('view_audit','partner_reseller_management')) access_denied();
        $data['title']  = _l('prm_audit_log');
        $this->load->view('partner_reseller_management/admin/audit/index', $data);
    }

    /* ============================================================
     *  SETTINGS
     * ========================================================== */
    public function settings()
    {
        if (!is_admin()) access_denied();
        if ($this->input->post()) {
            foreach ($this->input->post(null, true) as $k => $v) {
                if (strpos($k, 'prm_') === 0) {
                    set_prm_option($k, $v);
                }
            }
            set_alert('success', _l('prm_settings_saved'));
            redirect(admin_url('partner_reseller_management/settings'));
        }
        $data['title']    = _l('prm_settings');
        $data['settings'] = $this->db->order_by('setting_key','asc')->get(db_prefix().'prm_settings')->result();
        $this->load->view('partner_reseller_management/admin/settings/index', $data);
    }
}
