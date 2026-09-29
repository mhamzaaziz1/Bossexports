<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Bdm_operations
 *
 * Senior-grade controller — UAE BDM Module (IT & Fiber Optic Infrastructure).
 * Implements every endpoint required by the Advanced Planning Document:
 * brands · distributors · VR registrations (10-step pipeline) · activities
 * · partnerships + commissions · tenders · marketing campaigns · KPI targets
 * · reports + CSV export · AJAX JSON · lead-to-BDM referral.
 */
class Bdm_operations extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bdm_operations_model');

        if (!has_permission('bdm_operations', '', 'view') &&
            !has_permission('bdm_operations', '', 'view_own')) {
            access_denied('bdm_operations');
        }
    }

    /* ======================================================================= */
    /* DASHBOARD                                                                 */
    /* ======================================================================= */

    public function index()
    {
        $has_bdm_core_access = is_admin() || !is_purchase_manager();
        if (!$has_bdm_core_access) {
            redirect(admin_url('bdm_operations/distributors'));
        }

        $data['title']             = _l('bdm_operations');
        $data['stats']              = $this->bdm_operations_model->get_dashboard_stats();
        $data['kpi_progress']       = $this->bdm_operations_model->get_kpi_progress();
        $data['performance']        = $this->bdm_operations_model->get_bdm_performance();
        $data['pipeline']           = $this->bdm_operations_model->get_pipeline_distribution();
        $data['pipeline_steps']     = $this->bdm_operations_model->get_pipeline_step_distribution();
        $data['segments']           = $this->bdm_operations_model->get_segment_distribution();
        $data['revenue_trend']      = $this->bdm_operations_model->get_revenue_trend(6);
        $data['channel_summary']    = $this->bdm_operations_model->get_campaign_channel_summary();

        $data['brands']             = $this->bdm_operations_model->get_brands();
        $data['registrations']      = $this->bdm_operations_model->get_vr_registrations();

        $data['pipeline_stages']    = bdm_pipeline_stages();
        $data['pipeline_step_map']  = bdm_pipeline_steps();

        $this->load->view('dashboard/index', $data);
    }

    /* ======================================================================= */
    /* BRANDS                                                                    */
    /* ======================================================================= */

    public function brands()
    {
        $data['title']     = _l('brands');
        $data['brands']    = $this->bdm_operations_model->get_brands();
        $data['countries'] = get_all_countries();
        $this->load->view('brands/manage', $data);
    }

    public function save_brand($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
                $new_id = $this->bdm_operations_model->add_brand($this->input->post());
                if ($new_id) set_alert('success', _l('added_successfully', _l('brand')));
            } else {
                if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
                $ok = $this->bdm_operations_model->update_brand($this->input->post(), $id);
                if ($ok) set_alert('success', _l('updated_successfully', _l('brand')));
            }
            redirect(admin_url('bdm_operations/brands'));
        }
    }

    public function delete_brand($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->delete_brand($id)) {
            set_alert('success', _l('deleted', _l('brand')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('brand')));
        }
        redirect(admin_url('bdm_operations/brands'));
    }

    public function get_brand_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_brands($id));
        die;
    }

    /* ======================================================================= */
    /* DISTRIBUTORS / VENDORS                                                    */
    /* ======================================================================= */

    public function distributors()
    {
        $data['title']        = _l('distributors');
        $data['distributors'] = $this->bdm_operations_model->get_distributors();
        $data['brands']       = $this->bdm_operations_model->get_brands();
        $this->load->model('staff_model');
        $data['staff']        = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('distributors/manage', $data);
    }

    public function save_distributor($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
                $new_id = $this->bdm_operations_model->add_distributor($this->input->post());
                if ($new_id) set_alert('success', _l('added_successfully', _l('distributor')));
            } else {
                if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
                $ok = $this->bdm_operations_model->update_distributor($this->input->post(), $id);
                if ($ok) set_alert('success', _l('updated_successfully', _l('distributor')));
            }
            redirect(admin_url('bdm_operations/distributors'));
        }
    }

    public function delete_distributor($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->delete_distributor($id)) {
            set_alert('success', _l('deleted', _l('distributor')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('distributor')));
        }
        redirect(admin_url('bdm_operations/distributors'));
    }

    public function get_distributor_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_distributors($id));
        die;
    }

    public function push_to_purchase($id)
    {
        if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
        $vendor_id = $this->bdm_operations_model->push_to_purchase_module($id);
        if ($vendor_id) {
            set_alert('success', _l('bdm_pushed_to_purchase'));
        } else {
            set_alert('danger', _l('bdm_push_to_purchase_failed'));
        }
        redirect(admin_url('bdm_operations/distributors'));
    }

    public function distributor_search()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $q = $this->input->get('q');
        $this->db->group_start()
                 ->like('name', $q)
                 ->or_like('contact_name', $q)
                 ->or_like('email', $q)
                 ->or_like('emirate', $q)
                 ->or_like('vendor_role', $q)
                 ->group_end();

        if (!is_admin() && !has_permission('bdm_operations', '', 'view')) {
            $this->db->where('assigned_bdm', get_staff_user_id());
        }
        $rows = $this->db->get(db_prefix() . 'distributors')->result_array();
        echo json_encode($rows);
        die;
    }

    /* ======================================================================= */
    /* VR REGISTRATIONS                                                          */
    /* ======================================================================= */

    public function vr_registrations()
    {
        $data['title']            = _l('vr_registrations');
        $data['registrations']    = $this->bdm_operations_model->get_vr_registrations();
        $data['pipeline_stages']  = bdm_pipeline_stages();
        $data['pipeline_step_map']= bdm_pipeline_steps();
        $data['companies']        = $this->bdm_operations_model->get_master_companies();
        $this->load->model('staff_model');
        $data['staff']            = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('vr_registrations/manage', $data);
    }

    public function save_vr($id = '')
    {
        if (!$this->input->post()) {
            redirect(admin_url('bdm_operations/vr_registrations'));
        }
        $post = $this->input->post();

        // Resolve / create master company on the fly from form input
        if (empty($post['company_id']) && !empty($post['company_name'])) {
            $post['company_id'] = $this->bdm_operations_model->add_master_company([
                'company_name' => $post['company_name'],
                'industry'     => $post['segment']  ?? null,
                'emirate'      => $post['emirate']  ?? null,
            ]);
        }

        if ($id == '') {
            if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
            $new_id = $this->bdm_operations_model->add_vr_registration($post);
            if ($new_id) set_alert('success', _l('added_successfully', _l('vr_registrations')));
        } else {
            if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
            $ok = $this->bdm_operations_model->update_vr_registration($post, $id);
            if ($ok) set_alert('success', _l('updated_successfully', _l('vr_registrations')));
        }
        redirect(admin_url('bdm_operations/vr_registrations'));
    }

    public function update_vr_status($id)
    {
        if (!$this->input->post()) {
            redirect(admin_url('bdm_operations/vr_registrations'));
        }
        $status = $this->input->post('status');
        if ($this->bdm_operations_model->update_vr_status($id, $status)) {
            set_alert('success', _l('updated_successfully', _l('status')));
        }
        redirect(admin_url('bdm_operations/vr_registrations'));
    }

    public function update_vr_step()
    {
        if (!$this->input->is_ajax_request()) show_404();
        $id   = (int) $this->input->post('id');
        $step = (int) $this->input->post('step');
        $ok   = $this->bdm_operations_model->update_vr_pipeline_step($id, $step);
        echo json_encode(['success' => (bool) $ok]);
        die;
    }

    public function get_vr_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_vr_registrations($id));
        die;
    }

    public function get_vr_activity_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_vr_activity_logs($id));
        die;
    }

    public function delete_vr($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->delete_vr_registration($id)) {
            set_alert('success', _l('deleted', _l('vr_registrations')));
        }
        redirect(admin_url('bdm_operations/vr_registrations'));
    }

    /* ======================================================================= */
    /* PIPELINE — KANBAN                                                         */
    /* ======================================================================= */

    public function pipeline()
    {
        $data['title']             = _l('bdm_pipeline');
        $data['registrations']     = $this->bdm_operations_model->get_vr_registrations();
        $data['pipeline_step_map'] = bdm_pipeline_steps();
        $data['companies_index']   = [];
        foreach ($this->bdm_operations_model->get_master_companies() as $c) {
            $data['companies_index'][$c['id']] = $c;
        }
        $this->load->view('pipeline/board', $data);
    }

    /* ======================================================================= */
    /* MASTER COMPANIES                                                          */
    /* ======================================================================= */

    public function master_companies()
    {
        if (!is_admin()) access_denied('bdm_operations');
        $data['title']     = _l('master_companies');
        $data['companies'] = $this->bdm_operations_model->get_master_companies();
        $data['countries'] = get_all_countries();
        $this->load->view('master_companies/manage', $data);
    }

    public function save_company($id = '')
    {
        if (!is_admin()) access_denied('bdm_operations');
        if ($this->input->post()) {
            if ($id == '') {
                $this->bdm_operations_model->add_master_company($this->input->post());
                set_alert('success', _l('added_successfully', _l('master_companies')));
            } else {
                $this->bdm_operations_model->update_master_company($this->input->post(), $id);
                set_alert('success', _l('updated_successfully', _l('master_companies')));
            }
            redirect(admin_url('bdm_operations/master_companies'));
        }
    }

    public function delete_company($id)
    {
        if (!is_admin()) access_denied('bdm_operations');
        $this->bdm_operations_model->delete_master_company($id);
        set_alert('success', _l('deleted', _l('master_companies')));
        redirect(admin_url('bdm_operations/master_companies'));
    }

    public function get_company_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_master_companies($id));
        die;
    }

    /* ======================================================================= */
    /* ACTIVITIES                                                                */
    /* ======================================================================= */

    public function activities()
    {
        if ($this->input->post()) {
            $id = $this->bdm_operations_model->add_activity($this->input->post());
            if ($id) set_alert('success', _l('added_successfully', _l('bdm_activities')));
            redirect(admin_url('bdm_operations/activities'));
        }
        $data['title']      = _l('bdm_activities');
        $data['activities'] = $this->bdm_operations_model->get_activities();
        $this->load->view('activities/manage', $data);
    }

    public function delete_activity($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->delete_activity($id)) {
            set_alert('success', _l('deleted', _l('bdm_activities')));
        }
        redirect(admin_url('bdm_operations/activities'));
    }

    public function get_activity_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_activities($id));
        die;
    }

    /* ======================================================================= */
    /* PARTNERSHIPS + COMMISSIONS                                                */
    /* ======================================================================= */

    public function partnerships($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
                $new_id = $this->bdm_operations_model->add_partner($this->input->post());
                if ($new_id) set_alert('success', _l('added_successfully', _l('bdm_partnerships')));
            } else {
                if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
                $ok = $this->bdm_operations_model->update_partner($this->input->post(), $id);
                if ($ok) set_alert('success', _l('updated_successfully', _l('bdm_partnerships')));
            }
            redirect(admin_url('bdm_operations/partnerships'));
        }

        $data['title']    = _l('bdm_partnerships');
        $data['partners'] = $this->bdm_operations_model->get_partners();
        $this->load->model('staff_model');
        $data['staff']    = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('partnerships/manage', $data);
    }

    public function delete_partner($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->delete_partner($id)) {
            set_alert('success', _l('deleted', _l('bdm_partnerships')));
        }
        redirect(admin_url('bdm_operations/partnerships'));
    }

    public function get_partner_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_partners($id));
        die;
    }

    public function commissions($partner_id = null)
    {
        if ($this->input->post()) {
            if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
            $cid = $this->bdm_operations_model->add_commission($this->input->post());
            if ($cid) set_alert('success', _l('bdm_commission_recorded'));
            redirect(admin_url('bdm_operations/commissions' . ($partner_id ? '/' . $partner_id : '')));
        }

        $data['title']        = _l('bdm_commissions');
        $data['commissions']  = $this->bdm_operations_model->get_commissions('', $partner_id);
        $data['partners']     = $this->bdm_operations_model->get_partners();
        $data['partner_id']   = $partner_id;
        $this->load->view('commissions/manage', $data);
    }

    public function pay_commission($id)
    {
        if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
        if ($this->bdm_operations_model->mark_commission_paid($id)) {
            set_alert('success', _l('bdm_commission_paid'));
        }
        redirect(admin_url('bdm_operations/commissions'));
    }

    public function delete_commission($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        $this->bdm_operations_model->delete_commission($id);
        set_alert('success', _l('deleted', _l('bdm_commissions')));
        redirect(admin_url('bdm_operations/commissions'));
    }

    /* ======================================================================= */
    /* TENDERS                                                                   */
    /* ======================================================================= */

    public function tenders($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
                $this->bdm_operations_model->add_tender($this->input->post());
                set_alert('success', _l('added_successfully', _l('bdm_tenders')));
            } else {
                if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
                $this->bdm_operations_model->update_tender($this->input->post(), $id);
                set_alert('success', _l('updated_successfully', _l('bdm_tenders')));
            }
            redirect(admin_url('bdm_operations/tenders'));
        }
        $data['title']   = _l('bdm_tenders');
        $data['tenders'] = $this->bdm_operations_model->get_tenders();
        $this->load->model('staff_model');
        $data['staff']   = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('tenders/manage', $data);
    }

    public function delete_tender($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        $this->bdm_operations_model->delete_tender($id);
        set_alert('success', _l('deleted', _l('bdm_tenders')));
        redirect(admin_url('bdm_operations/tenders'));
    }

    public function get_tender_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_tenders($id));
        die;
    }

    /* ======================================================================= */
    /* MARKETING CAMPAIGNS                                                       */
    /* ======================================================================= */

    public function campaigns($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bdm_operations', '', 'create')) access_denied('bdm_operations');
                $this->bdm_operations_model->add_campaign($this->input->post());
                set_alert('success', _l('added_successfully', _l('bdm_campaigns')));
            } else {
                if (!has_permission('bdm_operations', '', 'edit')) access_denied('bdm_operations');
                $this->bdm_operations_model->update_campaign($this->input->post(), $id);
                set_alert('success', _l('updated_successfully', _l('bdm_campaigns')));
            }
            redirect(admin_url('bdm_operations/campaigns'));
        }
        $data['title']     = _l('bdm_campaigns');
        $data['campaigns'] = $this->bdm_operations_model->get_campaigns();
        $data['summary']   = $this->bdm_operations_model->get_campaign_channel_summary();
        $this->load->view('campaigns/manage', $data);
    }

    public function delete_campaign($id)
    {
        if (!has_permission('bdm_operations', '', 'delete')) access_denied('bdm_operations');
        $this->bdm_operations_model->delete_campaign($id);
        set_alert('success', _l('deleted', _l('bdm_campaigns')));
        redirect(admin_url('bdm_operations/campaigns'));
    }

    public function get_campaign_json($id)
    {
        echo json_encode($this->bdm_operations_model->get_campaigns($id));
        die;
    }

    /* ======================================================================= */
    /* KPI TARGETS                                                               */
    /* ======================================================================= */

    public function kpi_targets()
    {
        if (!is_admin()) access_denied('bdm_operations');

        if ($this->input->post()) {
            $year  = (int) $this->input->post('year');
            $month = (int) $this->input->post('month');
            foreach ($this->input->post('targets') ?: [] as $key => $value) {
                $this->bdm_operations_model->set_kpi_target($key, (float) $value, $year, $month);
            }
            set_alert('success', _l('bdm_targets_saved'));
            redirect(admin_url('bdm_operations/kpi_targets'));
        }

        $data['title']    = _l('bdm_kpi_targets');
        $data['year']     = (int) ($this->input->get('year')  ?: date('Y'));
        $data['month']    = (int) ($this->input->get('month') ?: date('m'));
        $data['targets']  = $this->bdm_operations_model->get_kpi_targets($data['year'], $data['month']);
        $data['progress'] = $this->bdm_operations_model->get_kpi_progress();
        $this->load->view('kpi_targets/manage', $data);
    }

    /* ======================================================================= */
    /* LEAD REFERRAL                                                             */
    /* ======================================================================= */

    public function refer_lead($id)
    {
        $this->load->model('leads_model');
        $data['lead'] = $this->leads_model->get($id);
        if (!$data['lead']) show_404();

        if ($this->input->post()) {
            $post      = $this->input->post();
            $duplicate = $this->bdm_operations_model->check_duplicate_company($post['company']);
            if ($duplicate) {
                set_alert('warning', _l('company_already_exists') . ' (Reg ID: ' . $duplicate->id . ')');
            } else {
                $rid = $this->bdm_operations_model->refer_lead_to_bdm($id, $post);
                if ($rid) set_alert('success', _l('bdm_referral') . ' #' . $rid);
            }
            redirect(admin_url('leads/index/' . $id));
        }

        $this->load->model('staff_model');
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('referral_modal', $data);
    }

    /* ======================================================================= */
    /* REPORTS                                                                   */
    /* ======================================================================= */

    public function reports()
    {
        if (!has_permission('bdm_operations', '', 'view')) access_denied('bdm_operations');
        $data['title'] = _l('bdm_report_engine');
        $data['stats'] = $this->bdm_operations_model->get_dashboard_stats();
        $this->load->view('reports/manage', $data);
    }

    public function export($type)
    {
        if (!has_permission('bdm_operations', '', 'view')) access_denied('bdm_operations');

        $filename = 'bdm_report_' . $type . '_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');

        switch ($type) {
            case 'brands':
                fputcsv($output, ['ID', 'Name', 'Description', 'Country', 'Created']);
                foreach ($this->bdm_operations_model->get_brands() as $b) {
                    $country = $b['country_id'] ? get_country($b['country_id']) : null;
                    fputcsv($output, [$b['id'], $b['name'], $b['description'], $country ? $country->short_name : '-', $b['created_at']]);
                }
                break;

            case 'distributors':
            case 'national_dists':
            case 'intl_dists':
                fputcsv($output, ['ID', 'Name', 'Classification', 'Segment', 'Emirate', 'Contact', 'Email', 'Phone', 'Trade License', 'VAT TRN', 'BDM', 'Created']);
                foreach ($this->bdm_operations_model->get_distributors() as $d) {
                    if ($type == 'national_dists' && $d['classification'] !== 'National') continue;
                    if ($type == 'intl_dists' && $d['classification'] !== 'International') continue;
                    fputcsv($output, [
                        $d['id'], $d['name'], $d['classification'], $d['segment'] ?? '',
                        $d['emirate'] ?? '', $d['contact_name'], $d['email'], $d['phone'],
                        $d['trade_license_no'] ?? '', $d['vat_trn'] ?? '',
                        get_staff_full_name($d['assigned_bdm']), $d['created_at'],
                    ]);
                }
                break;

            case 'registrations':
            case 'approved_vrs':
            case 'rejected_vrs':
                fputcsv($output, ['ID', 'Company', 'Type', 'Status', 'Step', 'Segment', 'Expected (AED)', 'Won (AED)', 'BDM', 'Reg Date', 'Updated']);
                foreach ($this->bdm_operations_model->get_vr_registrations() as $v) {
                    if ($type == 'approved_vrs' && $v['status'] !== 'Registered') continue;
                    if ($type == 'rejected_vrs' && $v['status'] !== 'Rejected')   continue;
                    $c = $this->bdm_operations_model->get_master_companies($v['company_id']);
                    fputcsv($output, [
                        $v['id'], $c ? $c->company_name : 'N/A',
                        $v['registration_type'], $v['status'], $v['pipeline_step'],
                        $v['segment'] ?? '',
                        number_format((float) $v['expected_value'], 2),
                        number_format((float) $v['won_value'], 2),
                        get_staff_full_name($v['assigned_bdm']),
                        $v['registration_date'], $v['updated_at'],
                    ]);
                }
                break;

            case 'overdue':
                fputcsv($output, ['Source', 'ID', 'Name', 'Follow-up', 'BDM']);
                foreach ($this->bdm_operations_model->get_overdue_followups_list() as $o) {
                    fputcsv($output, [
                        $o['source'] ?? 'N/A',
                        $o['id'],
                        $o['name'] ?? '',
                        $o['follow_up_date'] ?? '',
                        get_staff_full_name($o['assigned_bdm'] ?? 0),
                    ]);
                }
                break;

            case 'partners':
                fputcsv($output, ['ID', 'Company', 'Type', 'Status', 'Commission %', 'Margin %', 'Total Deals', 'Total Revenue', 'Total Commission', 'BDM', 'Created']);
                foreach ($this->bdm_operations_model->get_partners() as $p) {
                    fputcsv($output, [
                        $p['id'], $p['company_name'], $p['partner_type'], $p['status'],
                        $p['commission_pct'], $p['margin_pct'] ?? 0,
                        $p['total_deals'] ?? 0,
                        number_format((float) ($p['total_revenue'] ?? 0), 2),
                        number_format((float) ($p['total_commission'] ?? 0), 2),
                        get_staff_full_name($p['assigned_bdm']), $p['created_at'],
                    ]);
                }
                break;

            case 'commissions':
                fputcsv($output, ['ID', 'Partner', 'Deal Ref', 'Deal Value', 'Commission %', 'Commission Amount', 'Currency', 'Status', 'Accrued', 'Paid']);
                foreach ($this->bdm_operations_model->get_commissions() as $c) {
                    $partner = $this->bdm_operations_model->get_partners($c['partner_id']);
                    fputcsv($output, [
                        $c['id'], $partner ? $partner->company_name : 'N/A',
                        $c['deal_reference'], number_format((float) $c['deal_value'], 2),
                        $c['commission_pct'], number_format((float) $c['commission_amount'], 2),
                        $c['currency'], $c['status'], $c['accrued_at'], $c['paid_at'],
                    ]);
                }
                break;

            case 'tenders':
                fputcsv($output, ['ID', 'Ref', 'Title', 'Client', 'Segment', 'Emirate', 'Value', 'Currency', 'Deadline', 'Status', 'Outcome', 'BDM']);
                foreach ($this->bdm_operations_model->get_tenders() as $t) {
                    fputcsv($output, [
                        $t['id'], $t['tender_ref'], $t['title'], $t['client_company'],
                        $t['segment'], $t['emirate'],
                        number_format((float) $t['value'], 2), $t['currency'],
                        $t['submission_deadline'], $t['status'], $t['outcome'],
                        get_staff_full_name($t['assigned_bdm']),
                    ]);
                }
                break;

            case 'campaigns':
                fputcsv($output, ['ID', 'Name', 'Channel', 'Status', 'Start', 'End', 'Recipients', 'Opens', 'Clicks', 'Leads', 'Deals', 'Revenue', 'Budget', 'Cost']);
                foreach ($this->bdm_operations_model->get_campaigns() as $c) {
                    fputcsv($output, [
                        $c['id'], $c['name'], $c['channel'], $c['status'],
                        $c['start_date'], $c['end_date'],
                        $c['recipients'], $c['opens'], $c['clicks'],
                        $c['leads_generated'], $c['deals_closed'],
                        number_format((float) $c['revenue_generated'], 2),
                        number_format((float) $c['budget'], 2),
                        number_format((float) $c['cost_actual'], 2),
                    ]);
                }
                break;

            case 'activity':
                fputcsv($output, ['ID', 'Registration', 'Action', 'Staff', 'Date']);
                $logs = $this->db->get(db_prefix() . 'vr_activity_logs')->result_array();
                foreach ($logs as $l) {
                    fputcsv($output, [$l['id'], $l['registration_id'], $l['action_description'], get_staff_full_name($l['staff_id']), $l['date_created']]);
                }
                break;

            case 'master_companies':
                fputcsv($output, ['ID', 'Company', 'Reg Number', 'Industry', 'Emirate', 'Created']);
                foreach ($this->bdm_operations_model->get_master_companies() as $c) {
                    fputcsv($output, [$c['id'], $c['company_name'], $c['registration_number'] ?? '', $c['industry'] ?? '', $c['emirate'] ?? '', $c['created_at']]);
                }
                break;
        }

        fclose($output);
        die;
    }

    public function get_report_data($type)
    {
        if (!has_permission('bdm_operations', '', 'view')) access_denied('bdm_operations');

        $data = ['headers' => [], 'rows' => []];

        switch ($type) {
            case 'brands':
                $data['headers'] = ['ID', 'Name', 'Description', 'Country', 'Created'];
                foreach ($this->bdm_operations_model->get_brands() as $b) {
                    $data['rows'][] = [$b['id'], $b['name'], $b['description'], get_country_short_name($b['country_id']), $b['created_at']];
                }
                break;
            case 'distributors':
            case 'national_dists':
            case 'intl_dists':
                $data['headers'] = ['ID', 'Name', 'Classification', 'Segment', 'Emirate', 'Contact', 'Email', 'Phone', 'BDM'];
                foreach ($this->bdm_operations_model->get_distributors() as $d) {
                    if ($type == 'national_dists' && $d['classification'] !== 'National') continue;
                    if ($type == 'intl_dists' && $d['classification'] !== 'International') continue;
                    $data['rows'][] = [
                        $d['id'], $d['name'], $d['classification'], $d['segment'] ?? '',
                        $d['emirate'] ?? '', $d['contact_name'], $d['email'], $d['phone'],
                        get_staff_full_name($d['assigned_bdm']),
                    ];
                }
                break;
            case 'registrations':
            case 'approved_vrs':
            case 'rejected_vrs':
                $data['headers'] = ['ID', 'Company', 'Type', 'Status', 'Step', 'Expected (AED)', 'BDM'];
                foreach ($this->bdm_operations_model->get_vr_registrations() as $v) {
                    if ($type == 'approved_vrs' && $v['status'] !== 'Registered') continue;
                    if ($type == 'rejected_vrs' && $v['status'] !== 'Rejected')   continue;
                    $c = $this->bdm_operations_model->get_master_companies($v['company_id']);
                    $data['rows'][] = [
                        $v['id'], $c ? $c->company_name : 'N/A',
                        $v['registration_type'], $v['status'], $v['pipeline_step'],
                        number_format((float) $v['expected_value'], 2),
                        get_staff_full_name($v['assigned_bdm']),
                    ];
                }
                break;
            case 'partners':
                $data['headers'] = ['ID', 'Company', 'Type', 'Status', 'Commission %', 'Total Revenue (AED)', 'BDM'];
                foreach ($this->bdm_operations_model->get_partners() as $p) {
                    $data['rows'][] = [
                        $p['id'], $p['company_name'], $p['partner_type'], $p['status'],
                        $p['commission_pct'],
                        number_format((float) ($p['total_revenue'] ?? 0), 2),
                        get_staff_full_name($p['assigned_bdm']),
                    ];
                }
                break;
            case 'tenders':
                $data['headers'] = ['ID', 'Ref', 'Title', 'Client', 'Value (AED)', 'Deadline', 'Status'];
                foreach ($this->bdm_operations_model->get_tenders() as $t) {
                    $data['rows'][] = [
                        $t['id'], $t['tender_ref'], $t['title'], $t['client_company'],
                        number_format((float) $t['value'], 2),
                        $t['submission_deadline'], $t['status'],
                    ];
                }
                break;
            case 'campaigns':
                $data['headers'] = ['ID', 'Name', 'Channel', 'Recipients', 'Opens', 'Leads', 'Deals', 'Revenue (AED)'];
                foreach ($this->bdm_operations_model->get_campaigns() as $c) {
                    $data['rows'][] = [
                        $c['id'], $c['name'], $c['channel'], $c['recipients'], $c['opens'],
                        $c['leads_generated'], $c['deals_closed'],
                        number_format((float) $c['revenue_generated'], 2),
                    ];
                }
                break;
        }

        $this->load->view('reports/table_partial', $data);
    }
}
