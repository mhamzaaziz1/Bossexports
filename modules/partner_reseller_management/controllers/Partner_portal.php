<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner_portal — Partner-facing self-service controller.
 * Routes mounted at /partner-portal/* via app_routes hook.
 */
class Partner_portal extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('partner_reseller_management/partner_reseller_management');
        $this->load->model('partner_reseller_management/partners_model');
        $this->load->model('partner_reseller_management/deals_model');
        $this->load->model('partner_reseller_management/commissions_model');
        $this->load->model('partner_reseller_management/wallet_model');
        $this->load->model('partner_reseller_management/training_model');
    }

    /* ============================================================
     *  AUTH GUARD
     * ========================================================== */
    private function require_auth()
    {
        if (!is_partner_authenticated()) {
            redirect(site_url('partner-portal/login'));
        }
    }

    /* ============================================================
     *  PUBLIC PAGES
     * ========================================================== */
    public function index()
    {
        if (is_partner_authenticated()) redirect(site_url('partner-portal/dashboard'));
        redirect(site_url('partner-portal/login'));
    }

    public function login()
    {
        if ($this->input->post()) {
            $r = $this->partners_model->authenticate(
                $this->input->post('username'),
                $this->input->post('password')
            );
            if ($r['ok']) {
                prm_login_partner($r['partner']);
                redirect(site_url('partner-portal/dashboard'));
            }
            $this->session->set_flashdata('error', _l('prm_login_failed_'.$r['reason']));
        }
        $this->load->view('partner_reseller_management/portal/login');
    }

    public function logout()
    {
        prm_logout_partner();
        redirect(site_url('partner-portal/login'));
    }

    public function register()
    {
        if ((int) get_prm_option('prm_self_signup', 1) !== 1) {
            show_404();
        }
        if ($this->input->post()) {
            $auto = (int) get_prm_option('prm_auto_approve_partner', 0) === 1;
            $payload = $this->input->post(null, true);
            $payload['status']     = $auto ? 'active' : 'pending';
            $payload['kyc_status'] = 'submitted';
            $id = $this->partners_model->add($payload);
            $this->session->set_flashdata('success', $auto ? _l('prm_register_active') : _l('prm_register_pending'));
            redirect(site_url('partner-portal/login'));
        }
        $this->load->view('partner_reseller_management/portal/register');
    }

    /* ============================================================
     *  DASHBOARD
     * ========================================================== */
    public function dashboard()
    {
        $this->require_auth();
        $pid = prm_current_partner_id();
        $data['partner']    = prm_current_partner();
        $data['stats']      = [
            'pipeline'       => $this->deals_model->pipeline_summary($pid),
            'commission_total'=>(float)($this->db->select_sum('commission_amount')->where('partner_id',$pid)->where_in('status',['approved','paid'])->get(db_prefix().'prm_commissions')->row()->commission_amount ?? 0),
            'wallet'          => $this->wallet_model->balance($pid),
        ];
        $data['recent_deals'] = $this->deals_model->get_by_partner($pid);
        $data['title'] = _l('prm_dashboard');
        $this->load->view('partner_reseller_management/portal/dashboard', $data);
    }

    /* ============================================================
     *  DEALS
     * ========================================================== */
    public function deals()
    {
        $this->require_auth();
        $data['deals'] = $this->deals_model->get_by_partner(prm_current_partner_id());
        $data['title'] = _l('prm_deals');
        $this->load->view('partner_reseller_management/portal/deals_list', $data);
    }

    public function deal_new()
    {
        $this->require_auth();

        if ($this->input->post()) {
            $payload = $this->input->post(null, true);
            $payload['partner_id'] = prm_current_partner_id();
            $payload['pipeline_stage']   = 'submitted';
            $payload['approval_status']  = 'pending';

            // AI scoring + duplicate detection
            $this->load->library('partner_reseller_management/Lead_intelligence', null, 'lead_intel');
            $payload['lead_score']      = $this->lead_intel->score_lead($payload);
            $payload['win_probability'] = $this->lead_intel->predict_win_probability($payload, prm_current_partner());

            $id = $this->deals_model->add($payload);

            // Notify admin
            $this->load->library('partner_reseller_management/Notification_hub', null, 'notification_hub');
            $this->notification_hub->send_to_partner(prm_current_partner_id(), 'deal_submitted', [
                'deal_number' => $payload['deal_number'] ?? '',
                'client_name' => $payload['client_name'] ?? '',
            ]);

            $this->session->set_flashdata('success', _l('prm_deal_submitted'));
            redirect(site_url('partner-portal/deals/' . $id));
        }
        $data['title'] = _l('prm_register_deal');
        $this->load->view('partner_reseller_management/portal/deal_new', $data);
    }

    public function deal_view($id)
    {
        $this->require_auth();
        $deal = $this->deals_model->get($id);
        if (!$deal || $deal->partner_id !== prm_current_partner_id()) show_404();
        $data['deal']    = $deal;
        $data['history'] = $this->deals_model->get_history($id);
        $data['title']   = $deal->deal_number;
        $this->load->view('partner_reseller_management/portal/deal_view', $data);
    }

    /* ============================================================
     *  COMMISSIONS
     * ========================================================== */
    public function commissions()
    {
        $this->require_auth();
        $data['commissions'] = $this->commissions_model->by_partner(prm_current_partner_id());
        $data['title']       = _l('prm_commissions');
        $this->load->view('partner_reseller_management/portal/commissions', $data);
    }

    /* ============================================================
     *  WALLET / PAYOUTS
     * ========================================================== */
    public function wallet()
    {
        $this->require_auth();
        $pid = prm_current_partner_id();
        if ($this->input->post('action') === 'request_payout') {
            $cids = (array) $this->input->post('commission_ids');
            $r = $this->wallet_model->request_payout($pid, $cids, $this->input->post('payment_method'), $this->input->post('notes'));
            $this->session->set_flashdata($r['ok']?'success':'error', $r['ok']?_l('prm_payout_requested'):_l('prm_payout_below_min'));
            redirect(site_url('partner-portal/wallet'));
        }
        $data['balance'] = $this->wallet_model->balance($pid);
        $data['ledger']  = $this->wallet_model->ledger($pid, 50);
        $data['payouts'] = $this->wallet_model->list_payouts($pid);
        $data['eligible_commissions'] = $this->commissions_model->by_partner($pid, ['status' => 'approved']);
        $data['title']   = _l('prm_wallet');
        $this->load->view('partner_reseller_management/portal/wallet', $data);
    }

    /* ============================================================
     *  TRAINING
     * ========================================================== */
    public function training()
    {
        $this->require_auth();
        $data['courses']        = $this->training_model->list_courses();
        $data['progress']       = $this->training_model->partner_progress(prm_current_partner_id());
        $data['certifications'] = $this->training_model->partner_certifications(prm_current_partner_id());
        $data['title']          = _l('prm_training');
        $this->load->view('partner_reseller_management/portal/training', $data);
    }

    /* ============================================================
     *  PROFILE
     * ========================================================== */
    public function profile()
    {
        $this->require_auth();
        $pid = prm_current_partner_id();
        if ($this->input->post()) {
            $allowed = ['phone','website','address','city','state','country','zip_code','iban','swift','bank_name','timezone','language'];
            $payload = array_intersect_key($this->input->post(null, true), array_flip($allowed));
            $this->partners_model->update($pid, $payload);
            $this->session->set_flashdata('success', _l('prm_profile_updated'));
            redirect(site_url('partner-portal/profile'));
        }
        $data['partner'] = prm_current_partner();
        $data['title']   = _l('prm_profile');
        $this->load->view('partner_reseller_management/portal/profile', $data);
    }
}
