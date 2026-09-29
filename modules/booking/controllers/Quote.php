<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('invoices_model');
        $this->load->model('leads_model');
    }
    
    public function index($id, $hash)
    {
        check_quote_restrictions($id, $hash);
        $quote = $this->quotes_model->get($id);

        if ($quote->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($quote->rel_id);
        } else if($quote->rel_type == 'lead') {
            load_lead_language($quote->rel_id);
        }

        $identity_confirmation_enabled = get_option('quote_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'quote_pdf':

                    $quote_number = format_quote_number($id);
                    $companyname     = get_option('invoice_company_name');
                    if ($companyname != '') {
                        $quote_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
                    }

                    try {
                        $pdf = quote_pdf($quote);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        die;
                    }

                    $pdf->Output($quote_number . '.pdf', 'D');

                    break;
                case 'quote_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['quoteid'] = $id;
                    $this->quotes_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
                case 'accept_quote':
                    if(!get_client_user_id()){
                        redirect_after_login_to_current_url();
                        redirect(site_url('authentication/login'));
                    }
                    $success = $this->quotes_model->mark_action_status(3, $id, true);
                    if ($success) {
                        $original_quote = $this->quotes_model->get($id);
                        $items = $original_quote->items;
                        $description = $items[1]['description'];
                        $long_description = $items[1]['long_description'];
                        $quote_to = $original_quote->quote_to;
                        
                        $sum = 0;
                        for($j = 1 ; $j < count($items) ; $j++){
                            $sum += $items[$j]['rate'];
                        }
                        
                        if($original_quote->rel_type == 'lead'){
                            $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            $this->db->where('relid', $original_quote->rel_id);
                            $this->db->where('fieldid', LEADS_TOUR_DATE_TIME_ID);
                            $this->db->where('fieldto', 'leads');
                            $tour_date = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            $tour_date1 = date("d/m/Y H:i", strtotime($tour_date));
                            $phone = $original_quote->phone;
                            $booking_data = array();
                            $booking_data['name'] = $description . ' ' . $quote_to . ' ' . $tour_date1 . ' ' . $phone;
                            $booking_data['clientid'] = get_client_user_id();
                            $booking_data['progress_from_tours'] = 'on';
                            $booking_data['progress'] = 0;
                            $booking_data['billing_type'] = 1;
                            $booking_data['status'] = 1;
                            $booking_data['booking_cost'] = $sum;
                            $booking_data['start_date'] = date('d/m/Y', strtotime($tour_date));
                            $booking_data['deadline'] = '';
                            $booking_data['description'] = 'Job Value $' . $sum;
                            $booking_data['tags'] = '';
                            $booking_data['booking_members'] = array();
                            $booking_data['booking_rate_per_hour'] = '';
                            $booking_data['estimated_hours'] = '';
                            $booking_data['settings'] = array(
                                'available_features' => ['booking_overview', 'booking_tours', 'booking_timesheets', 'booking_milestones', 'booking_files', 'booking_discussions', 'booking_gantt', 'booking_tickets', 'booking_contracts', 'booking_invoices', 'booking_estimates', 'booking_expenses', 'booking_credit_notes', 'booking_subscriptions', 'booking_notes', 'booking_activity'],
                                'view_tours' => 'on',
                                'create_tours' => 'on',
                                'edit_tours' => 'on',
                                'comment_on_tours' => 'on',
                                'view_tour_comments' => 'on',
                                'view_tour_attachments' => 'on',
                                'view_tour_checklist_items' => 'on',
                                'upload_on_tours' => 'on',
                                'view_tour_total_logged_time' => 'on',
                                'view_finance_overview' => 'on',
                                'upload_files' => 'on',
                                'open_discussions' => 'on',
                                'view_milestones' => 'on',
                                'view_gantt' => 'on',
                                'view_timesheets' => 'on',
                                'view_activity_log' => 'on',
                                'view_team_members' => 'on',
                            );
                            $booking_id = $this->bookings_model->add($booking_data);
                            $lead = $this->leads_model->get($original_quote->rel_id);
                            
                            $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            $this->db->where('relid', $original_quote->rel_id);
                            $this->db->where('fieldid', LEADS_PICKUP_ADDRESS_ID);
                            $this->db->where('fieldto', 'leads');
                            $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid'   => $booking_id,
                                'fieldid' => BOOKINGS_PICKUP_ADDRESS_ID,
                                'fieldto' => 'bookings',
                                'value'   => $value,
                            ]);
                            
                            $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            $this->db->where('relid', $original_quote->rel_id);
                            $this->db->where('fieldid', LEADS_DROPOFF_ADDRESS_ID);
                            $this->db->where('fieldto', 'leads');
                            $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid'   => $booking_id,
                                'fieldid' => BOOKINGS_DROPOFF_ADDRESS_ID,
                                'fieldto' => 'bookings',
                                'value'   => $value,
                            ]);
                            
                            $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            $this->db->where('relid', $original_quote->rel_id);
                            $this->db->where('fieldid', LEADS_HOW_MANY_GUESTS_ID);
                            $this->db->where('fieldto', 'leads');
                            $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid'   => $booking_id,
                                'fieldid' => BOOKINGS_NUMBER_OF_GUESTS_ID,
                                'fieldto' => 'bookings',
                                'value'   => $value,
                            ]);
                            
                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid'   => $booking_id,
                                'fieldid' => BOOKINGS_AMOUNT_TO_COLLECT_ID,
                                'fieldto' => 'bookings',
                                'value'   => $sum,
                            ]);
                            
                            $invoice_data = array();
                            $invoice_data['clientid'] = get_client_user_id();
                            $invoice_data['booking_id'] = $booking_id;
                            $invoice_data['billing_street'] = $original_quote->address;
                            $invoice_data['billing_city'] = $original_quote->city;
                            $invoice_data['billing_state'] = $original_quote->state;
                            $invoice_data['billing_zip'] = $original_quote->zip;
                            $invoice_data['billing_country'] = $original_quote->country;
                            $invoice_data['show_shipping_on_invoice'] = 'on';
                            $invoice_data['shipping_street'] = '';
                            $invoice_data['shipping_city'] = '';
                            $invoice_data['shipping_state'] = '';
                            $invoice_data['shipping_zip'] = '';
                            $this->db->select('MAX(id) as num');
                            $num = $this->db->get(db_prefix() . 'invoices')->row()->num;
                            $invoice_data['number'] = $num+1;
                            $invoice_data['date'] = date('d/m/Y');
                            $invoice_data['duedate'] = date('d/m/Y', mktime(0, 0, 0, date('n'), date('j') + 3, date('Y')));
                            $invoice_data['tags'] = '';
                            $this->db->select('id');
                            $payments = $this->db->get(db_prefix() . 'payment_modes')->result_array();
                            $payment_modes = array();
                            foreach($payments as $payment) $payment_modes[] = $payment['id'];
                            $invoice_data['allowed_payment_modes'] = $payment_modes;
                            $invoice_data['currency'] = 3;
                            $invoice_data['sale_agent'] = '';
                            $invoice_data['discount_type'] = '';
                            $invoice_data['repeat_every_custom'] = 1;
                            $invoice_data['repeat_type_custom'] = 'day';
                            $invoice_data['adminnote'] = '';
                            $invoice_data['item_select'] = '';
                            $invoice_data['show_quantity_as'] = 1;
                            $invoice_data['description'] = '';
                            $invoice_data['long_description'] = '';
                            $invoice_data['quantity'] = 1;
                            $invoice_data['unit'] = '';
                            $invoice_data['rate'] = '';
                            $invoice_data['newitems'] = array();
                            $invoice_data['newitems'][] = array(
                                'itemid' => 0,
                                'order' => 1,
                                'description' => $items[0]['description'],
                                'long_description' => $items[0]['long_description'],
                                'qty' => $items[0]['qty'],
                                'unit' => $items[0]['unit'],
                                'rate' => $items[0]['rate']
                            );
                            $rate = $items[0]['rate'];
                            if(isset($items[2])){
                                $invoice_data['newitems'][] = array(
                                    'itemid' => 0,
                                    'order' => 1,
                                    'description' => $items[2]['description'],
                                    'long_description' => $items[2]['long_description'],
                                    'qty' => $items[2]['qty'],
                                    'unit' => $items[2]['unit'],
                                    'rate' => $items[2]['rate']
                                );
                                $rate += $items[2]['rate'];
                            }
                            $invoice_data['subtotal'] = $rate;
                            $invoice_data['discount_percent'] = '0.00';
                            $invoice_data['discount_total'] = '0.00';
                            $invoice_data['adjustment'] = '0.00';
                            $invoice_data['total'] = $rate;
                            $invoice_data['task_id'] = '';
                            $invoice_data['expense_id'] = '';
                            $invoice_data['clientnote'] = '';
                            $invoice_data['terms'] = '';
                            $bond_invoice_id = $this->invoices_model->add($invoice_data);
                            
                            $invoice_data['number'] = $num+2;
                            $invoice_data['newitems'] = array();
                            $invoice_data['newitems'][] = array(
                                'itemid' => 0,
                                'order' => 1,
                                'description' => $items[1]['description'],
                                'long_description' => $items[1]['long_description'],
                                'qty' => $items[1]['qty'],
                                'unit' => $items[1]['unit'],
                                'rate' => $items[1]['rate']
                            );
                            $invoice_data['subtotal'] = $items[1]['rate'];
                            $invoice_data['total'] = $items[1]['rate'];
                            $tour_invoice_id = $this->invoices_model->add($invoice_data);
                        }
                        if($original_quote->rel_type == 'customer'){
                            $tmp = explode("/", $original_quote->subject);
                            $tour_date = substr($tmp[0],-2, 2)."-".$tmp[1]."-".$tmp[2];
                            $tour_date1 = date("d/m/Y H:i", strtotime($tour_date));
                            $phone = $original_quote->phone;
                            $booking_data = array();
                            $booking_data['name'] = $description . ' ' . $quote_to . ' ' . $tour_date1 . ' ' . $phone;
                            $booking_data['clientid'] = get_client_user_id();
                            $booking_data['progress_from_tours'] = 'on';
                            $booking_data['progress'] = 0;
                            $booking_data['billing_type'] = 1;
                            $booking_data['status'] = 1;
                            $booking_data['booking_cost'] = $sum;
                            $booking_data['start_date'] = date('d/m/Y', strtotime($tour_date));
                            $booking_data['deadline'] = '';
                            $booking_data['description'] = 'Job Value $' . $sum;
                            $booking_data['tags'] = '';
                            $booking_data['booking_members'] = array();
                            $booking_data['booking_rate_per_hour'] = '';
                            $booking_data['estimated_hours'] = '';
                            $booking_data['settings'] = array(
                                'available_features' => ['booking_overview', 'booking_tours', 'booking_timesheets', 'booking_milestones', 'booking_files', 'booking_discussions', 'booking_gantt', 'booking_tickets', 'booking_contracts', 'booking_invoices', 'booking_estimates', 'booking_expenses', 'booking_credit_notes', 'booking_subscriptions', 'booking_notes', 'booking_activity'],
                                'view_tours' => 'on',
                                'create_tours' => 'on',
                                'edit_tours' => 'on',
                                'comment_on_tours' => 'on',
                                'view_tour_comments' => 'on',
                                'view_tour_attachments' => 'on',
                                'view_tour_checklist_items' => 'on',
                                'upload_on_tours' => 'on',
                                'view_tour_total_logged_time' => 'on',
                                'view_finance_overview' => 'on',
                                'upload_files' => 'on',
                                'open_discussions' => 'on',
                                'view_milestones' => 'on',
                                'view_gantt' => 'on',
                                'view_timesheets' => 'on',
                                'view_activity_log' => 'on',
                                'view_team_members' => 'on',
                            );
                            $booking_id = $this->bookings_model->add($booking_data);
                            
                            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            // $this->db->where('relid', $original_quote->rel_id);
                            // $this->db->where('fieldid', LEADS_PICKUP_ADDRESS_ID);
                            // $this->db->where('fieldto', 'leads');
                            // $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
                            //     'relid'   => $booking_id,
                            //     'fieldid' => BOOKINGS_PICKUP_ADDRESS_ID,
                            //     'fieldto' => 'bookings',
                            //     'value'   => $value,
                            // ]);
                            
                            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            // $this->db->where('relid', $original_quote->rel_id);
                            // $this->db->where('fieldid', LEADS_DROPOFF_ADDRESS_ID);
                            // $this->db->where('fieldto', 'leads');
                            // $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
                            //     'relid'   => $booking_id,
                            //     'fieldid' => BOOKINGS_DROPOFF_ADDRESS_ID,
                            //     'fieldto' => 'bookings',
                            //     'value'   => $value,
                            // ]);
                            
                            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
                            // $this->db->where('relid', $original_quote->rel_id);
                            // $this->db->where('fieldid', LEADS_HOW_MANY_GUESTS_ID);
                            // $this->db->where('fieldto', 'leads');
                            // $value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
                            //     'relid'   => $booking_id,
                            //     'fieldid' => BOOKINGS_NUMBER_OF_GUESTS_ID,
                            //     'fieldto' => 'bookings',
                            //     'value'   => $value,
                            // ]);
                            
                            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
                            //     'relid'   => $booking_id,
                            //     'fieldid' => BOOKINGS_AMOUNT_TO_COLLECT_ID,
                            //     'fieldto' => 'bookings',
                            //     'value'   => $sum,
                            // ]);
                            
                            $invoice_data = array();
                            $invoice_data['clientid'] = get_client_user_id();
                            $invoice_data['booking_id'] = $booking_id;
                            $invoice_data['billing_street'] = $original_quote->address;
                            $invoice_data['billing_city'] = $original_quote->city;
                            $invoice_data['billing_state'] = $original_quote->state;
                            $invoice_data['billing_zip'] = $original_quote->zip;
                            $invoice_data['billing_country'] = $original_quote->country;
                            $invoice_data['show_shipping_on_invoice'] = 'on';
                            $invoice_data['shipping_street'] = '';
                            $invoice_data['shipping_city'] = '';
                            $invoice_data['shipping_state'] = '';
                            $invoice_data['shipping_zip'] = '';
                            $this->db->select('MAX(id) as num');
                            $num = $this->db->get(db_prefix() . 'invoices')->row()->num;
                            $invoice_data['number'] = $num+1;
                            $invoice_data['date'] = date('d/m/Y');
                            $invoice_data['duedate'] = date('d/m/Y', mktime(0, 0, 0, date('n'), date('j') + 3, date('Y')));
                            $invoice_data['tags'] = '';
                            $this->db->select('id');
                            $payments = $this->db->get(db_prefix() . 'payment_modes')->result_array();
                            $payment_modes = array();
                            foreach($payments as $payment) $payment_modes[] = $payment['id'];
                            $invoice_data['allowed_payment_modes'] = $payment_modes;
                            $invoice_data['currency'] = 3;
                            $invoice_data['sale_agent'] = '';
                            $invoice_data['discount_type'] = '';
                            $invoice_data['repeat_every_custom'] = 1;
                            $invoice_data['repeat_type_custom'] = 'day';
                            $invoice_data['adminnote'] = '';
                            $invoice_data['item_select'] = '';
                            $invoice_data['show_quantity_as'] = 1;
                            $invoice_data['description'] = '';
                            $invoice_data['long_description'] = '';
                            $invoice_data['quantity'] = 1;
                            $invoice_data['unit'] = '';
                            $invoice_data['rate'] = '';
                            $invoice_data['newitems'] = array();
                            $invoice_data['newitems'][] = array(
                                'itemid' => 0,
                                'order' => 1,
                                'description' => $items[0]['description'],
                                'long_description' => $items[0]['long_description'],
                                'qty' => $items[0]['qty'],
                                'unit' => $items[0]['unit'],
                                'rate' => $items[0]['rate']
                            );
                            $rate = $items[0]['rate'];
                            if(isset($items[2])){
                                $invoice_data['newitems'][] = array(
                                    'itemid' => 0,
                                    'order' => 1,
                                    'description' => $items[2]['description'],
                                    'long_description' => $items[2]['long_description'],
                                    'qty' => $items[2]['qty'],
                                    'unit' => $items[2]['unit'],
                                    'rate' => $items[2]['rate']
                                );
                                $rate += $items[2]['rate'];
                            }
                            $invoice_data['subtotal'] = $rate;
                            $invoice_data['discount_percent'] = '0.00';
                            $invoice_data['discount_total'] = '0.00';
                            $invoice_data['adjustment'] = '0.00';
                            $invoice_data['total'] = $rate;
                            $invoice_data['task_id'] = '';
                            $invoice_data['expense_id'] = '';
                            $invoice_data['clientnote'] = '';
                            $invoice_data['terms'] = '';
                            $bond_invoice_id = $this->invoices_model->add($invoice_data);
                            
                            $invoice_data['number'] = $num+2;
                            $invoice_data['newitems'] = array();
                            $invoice_data['newitems'][] = array(
                                'itemid' => 0,
                                'order' => 1,
                                'description' => $items[1]['description'],
                                'long_description' => $items[1]['long_description'],
                                'qty' => $items[1]['qty'],
                                'unit' => $items[1]['unit'],
                                'rate' => $items[1]['rate']
                            );
                            $invoice_data['subtotal'] = $items[1]['rate'];
                            $invoice_data['total'] = $items[1]['rate'];
                            $tour_invoice_id = $this->invoices_model->add($invoice_data);
                        }

                        process_digital_signature_image($this->input->post('signature', false), QUOTE_ATTACHMENTS_FOLDER . $id);

                        $this->db->where('id', $id);
                        $this->db->update(db_prefix().'quotes', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
                case 'decline_quote':
                    $success = $this->quotes_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
            }
        }

        $number_word_lang_rel_id = 'unknown';
        if ($quote->rel_type == 'customer') {
            $number_word_lang_rel_id = $quote->rel_id;
        }
        $this->load->library('app_number_to_word', [
            'clientid' => $number_word_lang_rel_id,
        ],'numberword');

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $quote->subject;
        $data['quote']  = hooks()->apply_filters('quote_html_pdf_data', $quote);
        $data['bodyclass'] = 'quote quote-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $this->app_scripts->theme('sticky-js','assets/plugins/sticky/sticky.js');

        $data['comments'] = $this->quotes_model->get_comments($id);
        add_views_tracking('quote', $id);
        hooks()->do_action('quote_html_viewed', $id);
        $this->app_css->remove('reset-css','customers-area-default');
        $data                      = hooks()->apply_filters('quote_customers_area_view_data', $data);
        no_index_customers_area();
        $this->data($data);
        $this->view('clients/quotes/viewquote');
        $this->layout();
    }
}
