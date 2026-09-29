<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner Reseller Controller
 * Handles all admin-side partner management operations
 */
class Partner_reseller extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('partner_reseller/partner_model');
        $this->load->model('partner_reseller/partner_deal_model');
        $this->load->model('partner_reseller/partner_commission_model');
        $this->load->model('partner_reseller/partner_analytics_model');
        
        // Check permission
        if (!has_permission('partner_reseller', '', 'view')) {
            access_denied('partner_reseller');
        }
    }
    
    /**
     * Dashboard - Main overview page
     */
    public function dashboard()
    {
        // Check permission
        if (!has_permission('partner_reseller', '', 'view')) {
            access_denied('partner_reseller');
        }
        
        $data['title'] = _l('partner_reseller_dashboard');
        
        // Get dashboard statistics
        $data['stats'] = [
            'total_partners'        => $this->partner_model->count_all(['status' => 'active']),
            'pending_partners'      => $this->partner_model->count_all(['status' => 'pending']),
            'total_deals'           => $this->partner_deal_model->count_all(),
            'pending_deals'         => $this->partner_deal_model->count_all(['approval_status' => 'pending']),
            'active_deals'          => $this->partner_deal_model->count_all_active(),
            'won_deals'             => $this->partner_deal_model->count_all(['status' => 'won']),
            'total_revenue'         => $this->partner_deal_model->get_total_revenue(),
            'total_commissions'     => $this->partner_commission_model->get_total_commissions(),
            'pending_commissions'   => $this->partner_commission_model->get_pending_commissions(),
            'paid_commissions'      => $this->partner_commission_model->get_paid_commissions()
        ];
        
        // Recent deals
        $data['recent_deals'] = $this->partner_deal_model->get_recent(10);
        
        // Top performers
        $data['top_partners'] = $this->partner_analytics_model->get_top_partners(5);
        
        // Monthly revenue chart data
        $data['monthly_revenue'] = $this->partner_analytics_model->get_monthly_revenue(12);
        
        // Deal pipeline
        $data['deal_pipeline'] = $this->partner_deal_model->get_pipeline_stats();
        
        // Conversion funnel
        $data['conversion_funnel'] = $this->partner_analytics_model->get_conversion_funnel();
        
        $this->load->view('partner_reseller/dashboard', $data);
    }
    
    /**
     * Partners list
     */
    public function partners()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(PARTNER_MODULE_NAME, 'tables/partners'));
        }
        
        $data['title'] = _l('partners');
        $data['partner_types'] = ['reseller', 'referral', 'contractor', 'corporate'];
        $data['statuses'] = ['pending', 'active', 'suspended', 'inactive'];
        
        $this->load->view('partner_reseller/partners/manage', $data);
    }
    
    /**
     * View single partner
     */
    public function partner($id = '')
    {
        if (!$id) {
            redirect(admin_url('partner_reseller/partners'));
        }
        
        $partner = $this->partner_model->get($id);
        
        if (!$partner) {
            set_alert('danger', _l('partner_not_found'));
            redirect(admin_url('partner_reseller/partners'));
        }
        
        $data['partner'] = $partner;
        $data['title'] = $partner->company_name;
        
        // Get related data
        $data['contacts'] = $this->partner_model->get_contacts($id);
        $data['documents'] = $this->partner_model->get_documents($id);
        $data['deals'] = $this->partner_deal_model->get_by_partner($id);
        $data['commissions'] = $this->partner_commission_model->get_by_partner($id);
        $data['activities'] = $this->partner_model->get_activities($id);
        
        // Performance metrics
        $data['metrics'] = $this->partner_analytics_model->get_partner_metrics($id);
        
        $this->load->view('partner_reseller/partners/view', $data);
    }
    
    /**
     * Create/Edit partner
     */
    public function partner_form($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Validate required fields
            $this->form_validation->set_rules('company_name', _l('company_name'), 'required');
            $this->form_validation->set_rules('email', _l('email'), 'required|valid_email');
            $this->form_validation->set_rules('partner_type', _l('partner_type'), 'required');
            
            if ($this->form_validation->run() !== false) {
                if ($id) {
                    // Update
                    $success = $this->partner_model->update($id, $data);
                    if ($success) {
                        set_alert('success', _l('updated_successfully', _l('partner')));
                        redirect(admin_url('partner_reseller/partner/' . $id));
                    }
                } else {
                    // Create
                    $data['created_by'] = get_staff_user_id();
                    $data['created_at'] = date('Y-m-d H:i:s');
                    
                    // Generate username and password if portal access enabled
                    if ($data['portal_access'] == 1) {
                        $data['username'] = $this->generate_username($data['email']);
                        $data['password'] = password_hash($this->generate_password(), PASSWORD_DEFAULT);
                        
                        // Send credentials email
                        $this->send_partner_credentials_email($data['email'], $data['username']);
                    }
                    
                    $partner_id = $this->partner_model->add($data);
                    
                    if ($partner_id) {
                        set_alert('success', _l('added_successfully', _l('partner')));
                        redirect(admin_url('partner_reseller/partner/' . $partner_id));
                    }
                }
            }
        }
        
        if ($id) {
            $data['partner'] = $this->partner_model->get($id);
            if (!$data['partner']) {
                redirect(admin_url('partner_reseller/partners'));
            }
        }
        
        $data['title'] = $id ? _l('edit_partner') : _l('new_partner');
        $data['staff_members'] = $this->staff_model->get();
        
        $this->load->view('partner_reseller/partners/form', $data);
    }
    
    /**
     * Delete partner
     */
    public function delete_partner($id)
    {
        if (!has_permission('partner_reseller', '', 'delete')) {
            access_denied('partner_reseller');
        }
        
        // Check if partner has active deals
        $active_deals = $this->partner_deal_model->count_all([
            'partner_id' => $id,
            'status !=' => 'lost'
        ]);
        
        if ($active_deals > 0) {
            set_alert('warning', _l('cannot_delete_partner_with_active_deals'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        $response = $this->partner_model->delete($id);
        
        if ($response) {
            set_alert('success', _l('deleted', _l('partner')));
        } else {
            set_alert('danger', _l('problem_deleting', _l('partner')));
        }
        
        redirect(admin_url('partner_reseller/partners'));
    }
    
    /**
     * Deals Management
     */
    public function deals()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(PARTNER_MODULE_NAME, 'tables/deals'));
        }
        
        $data['title'] = _l('deal_registration');
        $data['partners'] = $this->partner_model->get_all(['status' => 'active']);
        $data['service_types'] = ['CCTV', 'Fiber', 'IT Services', 'AMC', 'Other'];
        
        $this->load->view('partner_reseller/deals/manage', $data);
    }
    
    /**
     * View single deal
     */
    public function deal($id = '')
    {
        if (!$id) {
            redirect(admin_url('partner_reseller/deals'));
        }
        
        $deal = $this->partner_deal_model->get($id);
        
        if (!$deal) {
            set_alert('danger', _l('deal_not_found'));
            redirect(admin_url('partner_reseller/deals'));
        }
        
        $data['deal'] = $deal;
        $data['partner'] = $this->partner_model->get($deal->partner_id);
        $data['title'] = $deal->deal_number . ' - ' . $deal->company_name;
        
        // Check for duplicates
        $data['possible_duplicates'] = $this->partner_deal_model->find_duplicates(
            $deal->company_name,
            $deal->email,
            $deal->phone
        );
        
        // Get commission details if calculated
        if ($deal->commission_status != 'pending') {
            $data['commission'] = $this->partner_commission_model->get_by_deal($id);
        }
        
        $this->load->view('partner_reseller/deals/view', $data);
    }
    
    /**
     * Approve/Reject deal
     */
    public function deal_action($id, $action)
    {
        if (!has_permission('partner_reseller', '', 'edit')) {
            ajax_access_denied();
        }
        
        $deal = $this->partner_deal_model->get($id);
        
        if (!$deal) {
            echo json_encode(['success' => false, 'message' => _l('deal_not_found')]);
            return;
        }
        
        $update_data = [
            'approval_status' => $action,
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($action == 'approved') {
            $update_data['status'] = 'new';
            
            // Create lead in CRM if auto-create is enabled
            if (get_option('deal_auto_create_lead') == '1') {
                $lead_id = $this->create_crm_lead_from_deal($deal);
                $update_data['lead_id'] = $lead_id;
            }
            
            // Send notification to partner
            partner_send_notification(
                $deal->partner_id,
                _l('deal_approved'),
                _l('your_deal_has_been_approved', $deal->deal_number),
                'partner/deals/' . $id
            );
            
            // Send email
            $this->send_deal_approval_email($deal);
            
        } elseif ($action == 'rejected') {
            $update_data['rejection_reason'] = $this->input->post('reason');
            
            // Send rejection notification
            partner_send_notification(
                $deal->partner_id,
                _l('deal_rejected'),
                _l('your_deal_has_been_rejected', $deal->deal_number),
                'partner/deals/' . $id
            );
        }
        
        $success = $this->partner_deal_model->update($id, $update_data);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('deal_' . $action) : _l('error_occurred')
        ]);
    }
    
    /**
     * Update deal status
     */
    public function update_deal_status($id)
    {
        if (!has_permission('partner_reseller', '', 'edit')) {
            ajax_access_denied();
        }
        
        $status = $this->input->post('status');
        $actual_value = $this->input->post('actual_value');
        
        $update_data = ['status' => $status];
        
        if ($status == 'won' && $actual_value) {
            $update_data['actual_value'] = $actual_value;
            $update_data['closed_at'] = date('Y-m-d H:i:s');
            
            // Calculate and create commission
            $this->calculate_deal_commission($id);
        }
        
        if ($status == 'lost') {
            $update_data['closed_at'] = date('Y-m-d H:i:s');
        }
        
        $success = $this->partner_deal_model->update($id, $update_data);
        
        // Send notification to partner
        $deal = $this->partner_deal_model->get($id);
        partner_send_notification(
            $deal->partner_id,
            _l('deal_status_updated'),
            _l('deal_status_changed_to', $status),
            'partner/deals/' . $id
        );
        
        echo json_encode(['success' => $success]);
    }
    
    /**
     * Commissions Management
     */
    public function commissions()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(PARTNER_MODULE_NAME, 'tables/commissions'));
        }
        
        $data['title'] = _l('commissions');
        $data['partners'] = $this->partner_model->get_all(['status' => 'active']);
        
        $this->load->view('partner_reseller/commissions/manage', $data);
    }
    
    /**
     * Approve commission
     */
    public function approve_commission($id)
    {
        if (!has_permission('partner_reseller', '', 'edit')) {
            access_denied('partner_reseller');
        }
        
        $data = [
            'status' => 'approved',
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->partner_commission_model->update($id, $data);
        
        if ($success) {
            // Generate invoice
            $this->generate_commission_invoice($id);
            
            // Send notification
            $commission = $this->partner_commission_model->get($id);
            partner_send_notification(
                $commission->partner_id,
                _l('commission_approved'),
                _l('commission_approved_message', partner_format_money($commission->commission_amount)),
                'partner/commissions/' . $id
            );
            
            set_alert('success', _l('commission_approved_successfully'));
        }
        
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    /**
     * Process commission payment
     */
    public function process_payment($id)
    {
        if (!has_permission('partner_reseller', '', 'edit')) {
            access_denied('partner_reseller');
        }
        
        if ($this->input->post()) {
            $payment_data = [
                'status' => 'paid',
                'paid_amount' => $this->input->post('amount'),
                'paid_date' => $this->input->post('payment_date'),
                'payment_method' => $this->input->post('payment_method'),
                'payment_reference' => $this->input->post('reference'),
                'paid_by' => get_staff_user_id()
            ];
            
            $success = $this->partner_commission_model->update($id, $payment_data);
            
            if ($success) {
                // Send notification
                $commission = $this->partner_commission_model->get($id);
                partner_send_notification(
                    $commission->partner_id,
                    _l('commission_paid'),
                    _l('commission_paid_message', partner_format_money($payment_data['paid_amount'])),
                    'partner/commissions/' . $id
                );
                
                set_alert('success', _l('payment_processed_successfully'));
            }
            
            redirect(admin_url('partner_reseller/commissions'));
        }
        
        $data['commission'] = $this->partner_commission_model->get($id);
        $this->load->view('partner_reseller/commissions/payment_form', $data);
    }
    
    /**
     * Reports & Analytics
     */
    public function reports()
    {
        $data['title'] = _l('reports_analytics');
        
        // Date range filter
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        
        // Get analytics data
        $data['partner_performance'] = $this->partner_analytics_model->get_partner_performance($start_date, $end_date);
        $data['revenue_by_service'] = $this->partner_analytics_model->get_revenue_by_service($start_date, $end_date);
        $data['conversion_metrics'] = $this->partner_analytics_model->get_conversion_metrics($start_date, $end_date);
        $data['commission_summary'] = $this->partner_analytics_model->get_commission_summary($start_date, $end_date);
        
        $this->load->view('partner_reseller/reports/analytics', $data);
    }
    
    /**
     * Export report
     */
    public function export_report($type = 'partners')
    {
        $this->load->library('excel');
        
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        
        switch ($type) {
            case 'partners':
                $data = $this->partner_analytics_model->get_partners_export_data();
                $filename = 'partners_report_' . date('Y-m-d');
                break;
            case 'deals':
                $data = $this->partner_analytics_model->get_deals_export_data($start_date, $end_date);
                $filename = 'deals_report_' . date('Y-m-d');
                break;
            case 'commissions':
                $data = $this->partner_analytics_model->get_commissions_export_data($start_date, $end_date);
                $filename = 'commissions_report_' . date('Y-m-d');
                break;
            default:
                redirect(admin_url('partner_reseller/reports'));
        }
        
        $this->excel->export($data, $filename);
    }
    
    /**
     * Settings
     */
    public function settings()
    {
        if (!is_admin()) {
            access_denied('Settings');
        }
        
        if ($this->input->post()) {
            $settings = $this->input->post();
            
            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }
            
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('partner_reseller/settings'));
        }
        
        $data['title'] = _l('settings');
        $data['settings'] = $this->db->get('tblpartner_settings')->result();
        
        $this->load->view('partner_reseller/settings/manage', $data);
    }
    
    // Helper Methods
    
    private function generate_username($email)
    {
        $username = strstr($email, '@', true);
        $count = 1;
        $original_username = $username;
        
        while ($this->partner_model->username_exists($username)) {
            $username = $original_username . $count;
            $count++;
        }
        
        return $username;
    }
    
    private function generate_password($length = 12)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    private function create_crm_lead_from_deal($deal)
    {
        $this->load->model('leads_model');
        
        $lead_data = [
            'name' => $deal->client_name,
            'company' => $deal->company_name,
            'email' => $deal->email,
            'phonenumber' => $deal->phone,
            'address' => $deal->location,
            'description' => $deal->description,
            'source' => 'Partner Referral',
            'status' => 1, // Default status
            'assigned' => $deal->assigned_to,
            'dateadded' => date('Y-m-d H:i:s')
        ];
        
        return $this->leads_model->add($lead_data);
    }
    
    private function calculate_deal_commission($deal_id)
    {
        $deal = $this->partner_deal_model->get($deal_id);
        $partner = $this->partner_model->get($deal->partner_id);
        
        $commission_amount = calculate_commission(
            $deal->actual_value ?: $deal->estimated_value,
            $partner->commission_type,
            $partner->commission_value,
            json_decode($partner->commission_config, true)
        );
        
        $commission_data = [
            'partner_id' => $deal->partner_id,
            'deal_id' => $deal_id,
            'commission_number' => 'COM-' . date('Y') . '-' . str_pad($deal_id, 6, '0', STR_PAD_LEFT),
            'deal_value' => $deal->actual_value ?: $deal->estimated_value,
            'commission_type' => $partner->commission_type,
            'commission_rate' => $partner->commission_value,
            'commission_amount' => $commission_amount,
            'status' => 'calculated',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $commission_id = $this->partner_commission_model->add($commission_data);
        
        // Update deal
        $this->partner_deal_model->update($deal_id, [
            'commission_amount' => $commission_amount,
            'commission_status' => 'calculated'
        ]);
        
        return $commission_id;
    }
    
    private function generate_commission_invoice($commission_id)
    {
        $commission = $this->partner_commission_model->get($commission_id);
        
        $invoice_data = [
            'invoice_number' => 'INV-COM-' . date('Y') . '-' . str_pad($commission_id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => date('Y-m-d')
        ];
        
        $this->partner_commission_model->update($commission_id, [
            'invoice_generated' => 1,
            'invoice_number' => $invoice_data['invoice_number'],
            'invoice_date' => $invoice_data['invoice_date']
        ]);
        
        return $invoice_data['invoice_number'];
    }
    
    private function send_partner_credentials_email($email, $username)
    {
        $subject = _l('partner_portal_access');
        $message = _l('partner_credentials_email_message', [
            'username' => $username,
            'portal_url' => site_url('partner/login')
        ]);
        
        return partner_send_email($email, $subject, $message);
    }
    
    private function send_deal_approval_email($deal)
    {
        $partner = $this->partner_model->get($deal->partner_id);
        
        $subject = _l('deal_approved_email_subject');
        $message = _l('deal_approved_email_message', [
            'deal_number' => $deal->deal_number,
            'company' => $deal->company_name
        ]);
        
        return partner_send_email($partner->email, $subject, $message);
    }
}
