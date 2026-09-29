<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner Portal Controller
 * Handles partner-facing portal functionality
 */
class Partner_portal extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('partner_reseller/partner_model');
        $this->load->model('partner_reseller/partner_deal_model');
        $this->load->model('partner_reseller/partner_commission_model');
        
        // Check if partner is logged in
        if (!is_partner_logged_in() && !in_array($this->router->method, ['login', 'register', 'forgot_password'])) {
            redirect('partner/login');
        }
    }
    
    /**
     * Partner Login
     */
    public function login()
    {
        if (is_partner_logged_in()) {
            redirect('partner/dashboard');
        }
        
        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            
            $partner = $this->partner_model->get_by_username($username);
            
            if ($partner && password_verify($password, $partner->password)) {
                if ($partner->status != 'active') {
                    set_alert('warning', _l('partner_account_not_active'));
                    redirect('partner/login');
                }
                
                // Set session
                $this->session->set_userdata([
                    'partner_logged_in' => true,
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->company_name
                ]);
                
                // Update last login
                $this->partner_model->update($partner->id, [
                    'last_login' => date('Y-m-d H:i:s')
                ]);
                
                redirect('partner/dashboard');
            } else {
                set_alert('danger', _l('invalid_credentials'));
            }
        }
        
        $data['title'] = _l('partner_login');
        $this->load->view('partner_reseller/portal/login', $data);
    }
    
    /**
     * Partner Registration
     */
    public function register()
    {
        if (get_option('partner_self_signup') != '1') {
            show_404();
        }
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('company_name', _l('company_name'), 'required');
            $this->form_validation->set_rules('email', _l('email'), 'required|valid_email|is_unique[tblpartner_resellers.email]');
            $this->form_validation->set_rules('phone', _l('phone'), 'required');
            $this->form_validation->set_rules('password', _l('password'), 'required|min_length[8]');
            $this->form_validation->set_rules('password_confirm', _l('password_confirm'), 'required|matches[password]');
            
            if ($this->form_validation->run() !== false) {
                $data = $this->input->post();
                $data['username'] = $this->generate_username($data['email']);
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $data['status'] = get_option('partner_auto_approve') == '1' ? 'active' : 'pending';
                $data['portal_access'] = 1;
                $data['created_at'] = date('Y-m-d H:i:s');
                
                unset($data['password_confirm']);
                
                $partner_id = $this->partner_model->add($data);
                
                if ($partner_id) {
                    // Send welcome email
                    $this->send_welcome_email($data['email'], $data['username']);
                    
                    // If auto-approved, log them in
                    if ($data['status'] == 'active') {
                        $this->session->set_userdata([
                            'partner_logged_in' => true,
                            'partner_id' => $partner_id,
                            'partner_name' => $data['company_name']
                        ]);
                        
                        redirect('partner/dashboard');
                    } else {
                        set_alert('success', _l('registration_successful_pending_approval'));
                        redirect('partner/login');
                    }
                }
            }
        }
        
        $data['title'] = _l('partner_registration');
        $this->load->view('partner_reseller/portal/register', $data);
    }
    
    /**
     * Partner Dashboard
     */
    public function dashboard()
    {
        $partner_id = get_partner_id();
        
        $data['title'] = _l('dashboard');
        $data['partner'] = $this->partner_model->get($partner_id);
        
        // Statistics
        $data['stats'] = [
            'total_deals' => $this->partner_deal_model->count_by_partner($partner_id),
            'active_deals' => $this->partner_deal_model->count_by_partner($partner_id, ['status !=' => ['won', 'lost']]),
            'won_deals' => $this->partner_deal_model->count_by_partner($partner_id, ['status' => 'won']),
            'total_revenue' => $this->partner_deal_model->get_total_revenue_by_partner($partner_id),
            'total_commission_earned' => $this->partner_commission_model->get_total_by_partner($partner_id, ['status' => 'paid']),
            'pending_commission' => $this->partner_commission_model->get_total_by_partner($partner_id, ['status' => 'approved']),
            'conversion_rate' => $this->calculate_conversion_rate($partner_id)
        ];
        
        // Recent deals
        $data['recent_deals'] = $this->partner_deal_model->get_recent_by_partner($partner_id, 10);
        
        // Notifications
        $data['notifications'] = $this->partner_model->get_unread_notifications($partner_id, 5);
        
        // Deal status chart
        $data['deal_status_chart'] = $this->partner_deal_model->get_status_distribution($partner_id);
        
        // Monthly performance
        $data['monthly_performance'] = $this->partner_deal_model->get_monthly_stats($partner_id, 6);
        
        $this->load->view('partner_reseller/portal/dashboard', $data);
    }
    
    /**
     * Submit New Deal
     */
    public function submit_deal()
    {
        $partner_id = get_partner_id();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('client_name', _l('client_name'), 'required');
            $this->form_validation->set_rules('company_name', _l('company_name'), 'required');
            $this->form_validation->set_rules('email', _l('email'), 'required|valid_email');
            $this->form_validation->set_rules('phone', _l('phone'), 'required');
            $this->form_validation->set_rules('service_type', _l('service_type'), 'required');
            $this->form_validation->set_rules('estimated_value', _l('estimated_value'), 'required|numeric');
            
            if ($this->form_validation->run() !== false) {
                $data = $this->input->post();
                $data['partner_id'] = $partner_id;
                $data['deal_number'] = $this->generate_deal_number();
                $data['submitted_at'] = date('Y-m-d H:i:s');
                $data['status'] = 'pending';
                $data['approval_status'] = 'pending';
                
                // Check for duplicates
                $duplicates = check_duplicate_lead(
                    $data['company_name'],
                    $data['email'],
                    $data['phone']
                );
                
                if (!empty($duplicates)) {
                    $data['duplicate_flag'] = 1;
                    $data['duplicate_of'] = $duplicates[0]['id'];
                }
                
                $deal_id = $this->partner_deal_model->add($data);
                
                if ($deal_id) {
                    // Log activity
                    $this->log_activity($partner_id, 'deal_submitted', 'Deal #' . $data['deal_number'] . ' submitted');
                    
                    // Send notification to admin
                    $this->notify_admin_new_deal($deal_id);
                    
                    set_alert('success', _l('deal_submitted_successfully'));
                    redirect('partner/deals');
                }
            }
        }
        
        $data['title'] = _l('submit_deal');
        $data['service_types'] = ['CCTV', 'Fiber', 'IT Services', 'AMC', 'Other'];
        
        $this->load->view('partner_reseller/portal/submit_deal', $data);
    }
    
    /**
     * View Deals
     */
    public function deals()
    {
        $partner_id = get_partner_id();
        
        $data['title'] = _l('my_deals');
        $data['deals'] = $this->partner_deal_model->get_by_partner($partner_id);
        
        $this->load->view('partner_reseller/portal/deals', $data);
    }
    
    /**
     * View Single Deal
     */
    public function deal($id)
    {
        $partner_id = get_partner_id();
        $deal = $this->partner_deal_model->get($id);
        
        if (!$deal || $deal->partner_id != $partner_id) {
            show_404();
        }
        
        $data['title'] = _l('deal_details');
        $data['deal'] = $deal;
        
        // Get commission if deal is won
        if ($deal->status == 'won') {
            $data['commission'] = $this->partner_commission_model->get_by_deal($id);
        }
        
        $this->load->view('partner_reseller/portal/deal_view', $data);
    }
    
    /**
     * View Commissions
     */
    public function commissions()
    {
        $partner_id = get_partner_id();
        
        $data['title'] = _l('my_commissions');
        $data['commissions'] = $this->partner_commission_model->get_by_partner($partner_id);
        
        // Summary
        $data['summary'] = [
            'total_earned' => $this->partner_commission_model->get_total_by_partner($partner_id, ['status' => 'paid']),
            'pending_approval' => $this->partner_commission_model->get_total_by_partner($partner_id, ['status' => 'calculated']),
            'approved_unpaid' => $this->partner_commission_model->get_total_by_partner($partner_id, ['status' => 'approved'])
        ];
        
        $this->load->view('partner_reseller/portal/commissions', $data);
    }
    
    /**
     * Download Commission Invoice
     */
    public function download_invoice($commission_id)
    {
        $partner_id = get_partner_id();
        $commission = $this->partner_commission_model->get($commission_id);
        
        if (!$commission || $commission->partner_id != $partner_id) {
            show_404();
        }
        
        $this->load->library('pdf');
        
        $pdf_data = [
            'commission' => $commission,
            'partner' => $this->partner_model->get($partner_id),
            'deal' => $this->partner_deal_model->get($commission->deal_id)
        ];
        
        $html = $this->load->view('partner_reseller/portal/invoice_pdf', $pdf_data, true);
        
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream('commission_invoice_' . $commission->commission_number . '.pdf');
    }
    
    /**
     * Profile Management
     */
    public function profile()
    {
        $partner_id = get_partner_id();
        
        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Don't allow changing certain fields
            unset($data['status'], $data['commission_type'], $data['commission_value']);
            
            $success = $this->partner_model->update($partner_id, $data);
            
            if ($success) {
                set_alert('success', _l('profile_updated_successfully'));
            }
        }
        
        $data['title'] = _l('my_profile');
        $data['partner'] = $this->partner_model->get($partner_id);
        $data['contacts'] = $this->partner_model->get_contacts($partner_id);
        $data['documents'] = $this->partner_model->get_documents($partner_id);
        
        $this->load->view('partner_reseller/portal/profile', $data);
    }
    
    /**
     * Notifications
     */
    public function notifications()
    {
        $partner_id = get_partner_id();
        
        $data['title'] = _l('notifications');
        $data['notifications'] = $this->partner_model->get_notifications($partner_id);
        
        // Mark all as read
        $this->partner_model->mark_notifications_read($partner_id);
        
        $this->load->view('partner_reseller/portal/notifications', $data);
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $this->session->unset_userdata(['partner_logged_in', 'partner_id', 'partner_name']);
        redirect('partner/login');
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
    
    private function generate_deal_number()
    {
        $prefix = 'DEAL-' . date('Y') . '-';
        $last_number = $this->partner_deal_model->get_last_number();
        
        return $prefix . str_pad($last_number + 1, 6, '0', STR_PAD_LEFT);
    }
    
    private function calculate_conversion_rate($partner_id)
    {
        $total = $this->partner_deal_model->count_by_partner($partner_id);
        $won = $this->partner_deal_model->count_by_partner($partner_id, ['status' => 'won']);
        
        return $total > 0 ? round(($won / $total) * 100, 2) : 0;
    }
    
    private function log_activity($partner_id, $type, $description)
    {
        $this->db->insert('tblpartner_activities', [
            'partner_id' => $partner_id,
            'activity_type' => $type,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function notify_admin_new_deal($deal_id)
    {
        $deal = $this->partner_deal_model->get($deal_id);
        
        // Send email to admins
        $admins = $this->db->where('admin', 1)->get('tblstaff')->result();
        
        foreach ($admins as $admin) {
            $subject = _l('new_deal_registration');
            $message = _l('new_deal_registered_by_partner', [
                'deal_number' => $deal->deal_number,
                'company' => $deal->company_name,
                'value' => partner_format_money($deal->estimated_value)
            ]);
            
            send_mail_template('new_partner_deal', $admin->email, '', $deal_id);
        }
    }
    
    private function send_welcome_email($email, $username)
    {
        $subject = _l('welcome_to_partner_portal');
        $message = _l('partner_welcome_email', [
            'username' => $username,
            'portal_url' => site_url('partner/login')
        ]);
        
        return partner_send_email($email, $subject, $message);
    }
}
