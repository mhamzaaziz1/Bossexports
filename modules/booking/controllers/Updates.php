<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Updates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('invoices_model');
        $this->load->model('leads_model');
        $this->load->model('quotes_model');
    }

    public function index()
    {
        $this->db->where('from_form_id', 1);
        $this->db->where('id > 5198');
        $leads = $this->db->get(db_prefix(). 'leads')->result_array();
        foreach($leads as $row){
            $pickup_address = $row['address']?$row['address']:'';
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 51,
                'fieldto' => 'leads',
                'value'   => $pickup_address,
            ]);
            
            $suburb = $row['city']?$row['city']:'';
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 52,
                'fieldto' => 'leads',
                'value'   => $suburb,
            ]);
            
            $this->db->select(db_prefix() . 'customfieldsvalues.value');
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 11);
            $this->db->where('fieldto', 'leads');
            $dropoff_address = '';
            if($this->db->get(db_prefix() . 'customfieldsvalues')->row()){
                $dropoff_address = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            }
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 59,
                'fieldto' => 'leads',
                'value'   => $dropoff_address,
            ]);
            
            $this->db->select(db_prefix() . 'customfieldsvalues.value');
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 44);
            $this->db->where('fieldto', 'leads');
            $howmanyguests = '';
            if ($this->db->get(db_prefix() . 'customfieldsvalues')->row()){
                $howmanyguests = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            }
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 58,
                'fieldto' => 'leads',
                'value'   => $howmanyguests,
            ]);
            
            $this->db->select(db_prefix() . 'customfieldsvalues.value');
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 33);
            $this->db->where('fieldto', 'leads');
            $winetour = '';
            if ($this->db->get(db_prefix() . 'customfieldsvalues')->row()){
                $winetour = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            }
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 54,
                'fieldto' => 'leads',
                'value'   => $winetour,
            ]);
            
            $this->db->select(db_prefix() . 'customfieldsvalues.value');
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 14);
            $this->db->where('fieldto', 'leads');
            $accept = '';
            if($this->db->get(db_prefix() . 'customfieldsvalues')->row()){
                $accept = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            }
            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid'   => $row['id'],
                'fieldid' => 55,
                'fieldto' => 'leads',
                'value'   => $accept,
            ]);
            
        }
    }
    public function bookings_update(){
        // $projects = $this->db->get(db_prefix(). 'projects')->result_array();
        $projects = $this->db->get(db_prefix(). 'projects')->result_array();
        foreach($projects as $row){
            // if($row['id'] > 1383) break;
            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
            // $this->db->where('relid', $row['id']);
            // $this->db->where('fieldid', 35);
            // $this->db->where('fieldto', 'projects');
            // $amount_to_collect ='';
            // if($this->db->get(db_prefix() . 'customfieldsvalues')->row()) $amount_to_collect = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
            //     'relid'   => $row['id'],
            //     'fieldid' => 47,
            //     'fieldto' => 'bookings',
            //     'value'   => $amount_to_collect,
            // ]);
            
            $this->db->select(db_prefix() . 'customfieldsvalues.value');
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 31);
            $this->db->where('fieldto', 'projects');
            $number_of_guests = '';
            if($this->db->get(db_prefix() . 'customfieldsvalues')->row()) $number_of_guests = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 48);
            $this->db->where('fieldto', 'bookings');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $number_of_guests,
            ]);
            
            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
            // $this->db->where('relid', $row['id']);
            // $this->db->where('fieldid', 37);
            // $this->db->where('fieldto', 'projects');
            // $dropoff = '';
            // if($this->db->get(db_prefix() . 'customfieldsvalues')->row()) $dropoff = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
            //     'relid'   => $row['id'],
            //     'fieldid' => 49,
            //     'fieldto' => 'bookings',
            //     'value'   => $dropoff,
            // ]);
            
            // $this->db->select(db_prefix() . 'customfieldsvalues.value');
            // $this->db->where('relid', $row['id']);
            // $this->db->where('fieldid', 36);
            // $this->db->where('fieldto', 'projects');
            // $pickup = '';
            // if($this->db->get(db_prefix() . 'customfieldsvalues')->row()) $pickup = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            // $this->db->insert(db_prefix() . 'customfieldsvalues', [
            //     'relid'   => $row['id'],
            //     'fieldid' => 50,
            //     'fieldto' => 'bookings',
            //     'value'   => $pickup,
            // ]);
        }
    }
    public function bookings_update_fix_wrong(){
        $projects = $this->db->get(db_prefix(). 'projects')->result_array();
        foreach($projects as $row){
            if($row['id'] > 1383) break;
            echo $row['id'] . "<br>";
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 35);
            $this->db->where('fieldto', 'projects');
            $amount_to_collect ='';
            $row1 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            if($row1) $amount_to_collect = $row1->value;
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 47);
            $this->db->where('fieldto', 'bookings');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $amount_to_collect,
            ]);
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 31);
            $this->db->where('fieldto', 'projects');
            $num ='';
            $row2 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            if($row2) $num = $row2->value;
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 48);
            $this->db->where('fieldto', 'bookings');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $num,
            ]);
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 36);
            $this->db->where('fieldto', 'projects');
            $pick ='';
            $row3 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            if($row3) $pick = $row3->value;
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 50);
            $this->db->where('fieldto', 'bookings');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $pick,
            ]);
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 37);
            $this->db->where('fieldto', 'projects');
            $drop ='';
            $row4 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            if($row4) $drop = $row4->value;
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 49);
            $this->db->where('fieldto', 'bookings');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $drop,
            ]);
        }
    }
    public function fix_missing_booking($id){
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
            $this->db->where('fieldid', 1);
            $this->db->where('fieldto', 'leads');
            $tour_date = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
            $phone = $original_quote->phone;
            $booking_data = array();
            $booking_data['name'] = $description . ' ' . $quote_to . ' ' . $original_quote->rel_id . ' ' . $phone;
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
            
            // $invoice_data = array();
            // $invoice_data['clientid'] = get_client_user_id();
            // $invoice_data['booking_id'] = $booking_id;
            // $invoice_data['billing_street'] = $original_quote->address;
            // $invoice_data['billing_city'] = $original_quote->city;
            // $invoice_data['billing_state'] = $original_quote->state;
            // $invoice_data['billing_zip'] = $original_quote->zip;
            // $invoice_data['billing_country'] = $original_quote->country;
            // $invoice_data['show_shipping_on_invoice'] = 'on';
            // $invoice_data['shipping_street'] = '';
            // $invoice_data['shipping_city'] = '';
            // $invoice_data['shipping_state'] = '';
            // $invoice_data['shipping_zip'] = '';
            // $this->db->select('MAX(id) as num');
            // $num = $this->db->get(db_prefix() . 'invoices')->row()->num;
            // $invoice_data['number'] = $num+1;
            // $invoice_data['date'] = date('d/m/Y');
            // $invoice_data['duedate'] = date('d/m/Y', mktime(0, 0, 0, date('n'), date('j') + 3, date('Y')));
            // $invoice_data['tags'] = '';
            // $this->db->select('id');
            // $payments = $this->db->get(db_prefix() . 'payment_modes')->result_array();
            // $payment_modes = array();
            // foreach($payments as $payment) $payment_modes[] = $payment['id'];
            // $invoice_data['allowed_payment_modes'] = $payment_modes;
            // $invoice_data['currency'] = 3;
            // $invoice_data['sale_agent'] = '';
            // $invoice_data['discount_type'] = '';
            // $invoice_data['repeat_every_custom'] = 1;
            // $invoice_data['repeat_type_custom'] = 'day';
            // $invoice_data['adminnote'] = '';
            // $invoice_data['item_select'] = '';
            // $invoice_data['show_quantity_as'] = 1;
            // $invoice_data['description'] = '';
            // $invoice_data['long_description'] = '';
            // $invoice_data['quantity'] = 1;
            // $invoice_data['unit'] = '';
            // $invoice_data['rate'] = '';
            // $invoice_data['newitems'] = array();
            // $invoice_data['newitems'][] = array(
            //     'itemid' => 0,
            //     'order' => 1,
            //     'description' => $items[0]['description'],
            //     'long_description' => $items[0]['long_description'],
            //     'qty' => $items[0]['qty'],
            //     'unit' => $items[0]['unit'],
            //     'rate' => $items[0]['rate']
            // );
            // $rate = $items[0]['rate'];
            // if(isset($items[2])){
            //     $invoice_data['newitems'][] = array(
            //         'itemid' => 0,
            //         'order' => 1,
            //         'description' => $items[2]['description'],
            //         'long_description' => $items[2]['long_description'],
            //         'qty' => $items[2]['qty'],
            //         'unit' => $items[2]['unit'],
            //         'rate' => $items[2]['rate']
            //     );
            //     $rate += $items[2]['rate'];
            // }
            // $invoice_data['subtotal'] = $rate;
            // $invoice_data['discount_percent'] = '0.00';
            // $invoice_data['discount_total'] = '0.00';
            // $invoice_data['adjustment'] = '0.00';
            // $invoice_data['total'] = $rate;
            // $invoice_data['task_id'] = '';
            // $invoice_data['expense_id'] = '';
            // $invoice_data['clientnote'] = '';
            // $invoice_data['terms'] = '';
            // $bond_invoice_id = $this->invoices_model->add($invoice_data);
            
            // $invoice_data['number'] = $num+2;
            // $invoice_data['newitems'] = array();
            // $invoice_data['newitems'][] = array(
            //     'itemid' => 0,
            //     'order' => 1,
            //     'description' => $items[1]['description'],
            //     'long_description' => $items[1]['long_description'],
            //     'qty' => $items[1]['qty'],
            //     'unit' => $items[1]['unit'],
            //     'rate' => $items[1]['rate']
            // );
            // $invoice_data['subtotal'] = $items[1]['rate'];
            // $invoice_data['total'] = $items[1]['rate'];
            // $tour_invoice_id = $this->invoices_model->add($invoice_data);
        }
    }
    public function update_missing_leads_value()
    {
        $this->db->where('from_form_id', 1);
        $this->db->where('id > 5198');
        $leads = $this->db->get(db_prefix(). 'leads')->result_array();
        foreach($leads as $row){
            echo $row['id'];
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 3);
            $this->db->where('fieldto', 'leads');
            $num = '';
            $row1 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            if ($row1){
                $num = $row1->value;
            }
            else{
                $this->db->where('relid', $row['id']);
                $this->db->where('fieldid', 43);
                $this->db->where('fieldto', 'leads');
                $row2 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
                if ($row2){
                    $num = $row2->value;
                }
                else{
                    $this->db->where('relid', $row['id']);
                    $this->db->where('fieldid', 44);
                    $this->db->where('fieldto', 'leads');
                    $row3 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
                    if ($row3){
                        $num = $row3->value;
                    }
                }
            }
            if ($num != ''){
                $this->db->where('relid', $row['id']);
                $this->db->where('fieldid', 58);
                $this->db->where('fieldto', 'leads');
                $this->db->update(db_prefix() . 'customfieldsvalues', [
                    'value'   => $num,
                ]);
            }
            
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 33);
            $this->db->where('fieldto', 'leads');
            $row4 = $this->db->get(db_prefix() . 'customfieldsvalues')->row();
            $winetour = '';
            if ($row4){
                $winetour = $row4->value;
            }
            $this->db->where('relid', $row['id']);
            $this->db->where('fieldid', 54);
            $this->db->where('fieldto', 'leads');
            $this->db->update(db_prefix() . 'customfieldsvalues', [
                'value'   => $winetour,
            ]);
        }
    }
}