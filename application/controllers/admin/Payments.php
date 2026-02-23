<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payments_model');
    }

    /* In case if user go only on /payments */
    public function index()
    {
        $this->all_payment();
    }
    
    public function all_payment()
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('payments');
        }

        $data['title'] = _l('All payments');
        $this->load->view('admin/payments/manage_all', $data);
    }
    public function get_invoice_unpaid()
    {
        $vid = $this->input->post('vid');
        $this->load->model('invoices_model');
        $result=$this->invoices_model->get_unpaid_invoices($vid);
    echo json_encode($result) ;
    }

    public function list_payments()
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('payments');
        }

        $data['title'] = _l('payments');
        $this->load->view('admin/payments/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            ajax_access_denied();
        }

        $this->app->get_table_data('payments', [
            'clientid' => $clientid,
        ]);
    }
    
    public function table_all($clientid = '')
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            ajax_access_denied();
        }

        $this->app->get_table_data('payments_all', [
            'clientid' => $clientid,
        ]);
        
        
    }

    /* Update payment data */
    public function payment($id = '')
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('payments');
        }
        

        if (!$id) {
            redirect(admin_url('payments'));
        }

        if ($this->input->post()) {
            if (!has_permission('payments', '', 'edit')) {
                access_denied('Update Payment');
            }
            if($id == '-1'){
                $data["client_id"]=$this->input->post("client_id");
                $data["amount"]=$this->input->post('amount');
                $data["date"]=to_sql_date($this->input->post('date'));
                $data["paymentmode"]=$this->input->post('paymentmode');
                $data["paymentmethod"]=$this->input->post('paymentmethod');
                $data["transactionid"]=$this->input->post('transactionid');
                $data["note"]=$this->input->post('note');
                $amount=$data["amount"];
                // var_dump($data);die;
                if(!empty($this->input->post('pur_order'))){
                // var_dump($this->input->post());
                foreach($this->input->post('pur_order')  as $inv){
                $pinv=$inv;
                $data["date"]=$this->input->post('date');
                $data['invoiceid']=$inv;
                if($amount>0 && get_invoice_total_left_to_pay($inv) !=0 ){
                    if(get_invoice_total_left_to_pay($inv) <= $amount){
                        $data['amount']=get_invoice_total_left_to_pay($inv);
                        $amount-=$data['amount'];
                        $data["client_id"]="";
                        $this->load->model('payments_model');
                        $pid = $this->payments_model->process_payment($data, '');
                    }
                    else{
                        $data['amount']=$amount;
                        $amount-=$data['amount'];
                        $data["client_id"]="";
                        $this->load->model('payments_model');
                        $pid = $this->payments_model->process_payment($data, '');
                    }
                }
                }
                if($amount>0){
                    $data['amount']=$amount;
                    $data["client_id"]=$this->input->post("client_id");
                        $data['invoiceid']=0;
                        $data["date"]=date('Y-m-d', strtotime($this->input->post('date')));
                        $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
                        $pid = $this->db->insert_id();
                }
                }else{
                        $data['amount']=$amount;
                        $data["date"]=date('Y-m-d', strtotime($this->input->post('date')));
                        $data['invoiceid']=0;
                        $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
                        $pid = $this->db->insert_id();
                    }
            if ($pid) {
                set_alert('success', _l('invoice_payment_recorded'));
                redirect(admin_url('payments/payment/' . $pid));
            } else {
                set_alert('danger', _l('invoice_payment_record_failed'));
            }
            }
            else{
                $success = $this->payments_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('payment')));
                }
                redirect(admin_url('payments/payment/' . $id));
            }
        
        }
        
        $payment = $this->payments_model->get($id);
        $this->load->model('invoices_model');
        //var_dump("a");die;
        //$payment->invoice = $this->invoices_model->get($payment->invoiceid);
        $template_name    = 'invoice_payment_recorded_to_customer';
        
        $data = prepare_mail_preview_data($template_name, $payment->invoice->clientid);

        $data['payment'] = $payment;
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true, true);

        $i = 0;
        foreach ($data['payment_modes'] as $mode) {
            if ($mode['active'] == 0 && $data['payment']->paymentmode != $mode['id']) {
                unset($data['payment_modes'][$i]);
            }
            $i++;
        }
        

        $data['title'] = _l('payment_receipt') . ' - ' . format_invoice_number($data['payment']->invoiceid);
        $this->load->view('admin/payments/payment', $data);
    }
    
    

    /**
     * Generate payment pdf
     * @since  Version 1.0.1
     * @param  mixed $id Payment id
     */
    public function pdf($id)
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('View Payment');
        }

        $payment = $this->payments_model->get($id);

        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && !user_can_view_invoice($payment->invoiceid)) {
            access_denied('View Payment');
        }

        $this->load->model('invoices_model');
        $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);

        try {
            $paymentpdf = payment_pdf($payment);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid)) . '.pdf', $type);
    }

    /**
     * Send payment manually to customer contacts
     * @since  2.3.2
     * @param  mixed $id payment id
     * @return mixed
     */
    public function send_to_email($id)
    {
        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Send Payment');
        }

        $payment = $this->payments_model->get($id);

        if (!has_permission('payments', '', 'view')
            && !has_permission('invoices', '', 'view_own')
            && !user_can_view_invoice($payment->invoiceid)) {
            access_denied('Send Payment');
        }

        $this->load->model('invoices_model');
        $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
        set_mailing_constant();

        $paymentpdf = payment_pdf($payment);
        $filename   = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';

        $attach = $paymentpdf->Output($filename, 'S');

        $sent    = false;
        $sent_to = $this->input->post('sent_to');

        if (is_array($sent_to) && count($sent_to) > 0) {
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    $contact = $this->clients_model->get_contact($contact_id);

                    $template = mail_template('invoice_payment_recorded_to_customer', (array) $contact, $payment->invoice_data, false, $payment->paymentid);

                    $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $filename,
                            'type'       => 'application/pdf',
                        ]);

                        
                    if (get_option('attach_invoice_to_payment_receipt_email') == 1) {
                        $invoice_number = format_invoice_number($payment->invoiceid);
                        set_mailing_constant();
                        $pdfInvoice           = invoice_pdf($payment->invoice_data);
                        $pdfInvoiceAttachment = $pdfInvoice->Output($invoice_number . '.pdf', 'S');
                        
                        $template->add_attachment([
                            'attachment' => $pdfInvoiceAttachment,
                            'filename'   => str_replace('/', '-', $invoice_number) . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        $sent = true;
                    }
                }
            }
        }

        // In case client use another language
        load_admin_language();
        set_alert($sent ? 'success' : 'danger', _l($sent ? 'payment_sent_successfully' : 'payment_sent_failed'));

        redirect(admin_url('payments/payment/' . $id));
    }

    /* Delete payment */
    public function delete($id)
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('Delete Payment');
        }
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        redirect(admin_url('payments'));
    }
    /* Attach invoice to payment */
    public function attach_invoice($id)
    {
        if (!has_permission('payments', '', 'edit')) {
            ajax_access_denied();
        }
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => _l('payment_not_found')]);
            die;
        }

        $invoiceid = $this->input->post('invoiceid');
        if (!$invoiceid) {
             echo json_encode(['success' => false, 'message' => _l('invoice_not_found')]);
             die;
        }

        // Verify invoice exists and is unpaid/not cancelled is done via UI mostly but good to verify
        // For now, trust the UI selection filtering, just update the relationship.
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'invoicepaymentrecords', [
            'invoiceid' => $invoiceid
        ]);

        if ($this->db->affected_rows() > 0) {
             echo json_encode(['success' => true, 'message' => _l('updated_successfully', _l('payment'))]);
        } else {
             echo json_encode(['success' => false, 'message' => _l('payment_updated_nothing_changed')]);
        }
    }

    public function get_grouped_payments()
    {
        if (!has_permission('payments', '', 'view') 
            && !has_permission('invoices', '', 'view_own') 
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            ajax_access_denied();
        }

        $transactionid = $this->input->post('transactionid');
        $date = $this->input->post('date');

        if (!$transactionid) {
             echo json_encode([]);
             die;
        }

        $this->db->select(db_prefix() . 'invoicepaymentrecords.*, ' . db_prefix() . 'invoices.number as invoice_number, ' . db_prefix() . 'payment_modes.name as mode_name');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid', 'left');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->where('transactionid', $transactionid);
        if ($date) {
            $this->db->where('DATE(' . db_prefix() . 'invoicepaymentrecords.date)', $date);
        }
        
        $payments = $this->db->get()->result_array();
        
        foreach ($payments as &$payment) {
            $payment['amount_formatted'] = app_format_money($payment['amount'], get_base_currency());
            $payment['date'] = _d($payment['date']);
            $payment['invoice_link'] = admin_url('invoices/list_invoices/' . $payment['invoiceid']);
            $payment['payment_link'] = admin_url('payments/payment/' . $payment['id']);
        }

        echo json_encode($payments);
    }

    public function get_payment_receipt_html($id)
    {
        if (!has_permission('payments', '', 'view') 
            && !has_permission('invoices', '', 'view_own') 
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            ajax_access_denied();
        }
        
        $payment = $this->payments_model->get($id);
        if (!$payment) {
            echo 'Payment not found';
            return;
        }

        $this->load->model('invoices_model');
        if ($payment->invoiceid != 0) {
            $payment->invoice = $this->invoices_model->get($payment->invoiceid);
        } else {
            $payment->invoice = null;
            // Fetch client details if no invoice
            if ($payment->client_id) {
                $this->load->model('clients_model');
                $payment->client = $this->clients_model->get($payment->client_id);
            }
        }
        
        $data['payment'] = $payment;
        $this->load->view('admin/payments/payment_receipt_partial', $data);
    }

    public function get_payment_edit_html($id)
    {
        if (!has_permission('payments', '', 'edit')) {
            access_denied('Edit Payment');
        }

        $payment = $this->payments_model->get($id);
        if (!$payment) {
            echo 'Payment not found';
            return;
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $data['payment'] = $payment;
        
        $this->load->view('admin/payments/payment_edit_partial', $data);
    }

    public function update_payment_ajax($id)
    {
        if (!has_permission('payments', '', 'edit')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }

        if ($this->input->post()) {
            $this->load->model('payments_model');
            $success = $this->payments_model->update($this->input->post(), $id);
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => _l('updated_successfully', _l('payment')),
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => _l('could_not_update', _l('payment')),
                ]);
            }
            die;
        }
    }

    public function get_payment_add_html()
    {
        if (!has_permission('payments', '', 'create')) {
            access_denied('Create Payment');
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);

        $this->load->view('admin/payments/payment_add_partial', $data);
    }

    public function add_payment_ajax()
    {
        if (!has_permission('payments', '', 'create')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            // Handle date format if necessary, though to_sql_date is usually used in Perfex
            if(isset($data['date'])) {
                $data["date"] = to_sql_date($data['date']);
            }
            $amount = isset($data["amount"]) ? $data["amount"] : 0;

            if (!empty($this->input->post('pur_order'))) {
                foreach ($this->input->post('pur_order') as $inv) {
                    $invoice_amount_left = get_invoice_total_left_to_pay($inv);
                    if ($amount > 0 && $invoice_amount_left != 0) {
                        $payment_data = [
                            'invoiceid'     => $inv,
                            'amount'        => ($invoice_amount_left <= $amount) ? $invoice_amount_left : $amount,
                            'date'          => $data['date'],
                            'paymentmode'   => $data['paymentmode'],
                            'paymentmethod' => $data['paymentmethod'],
                            'transactionid' => $data['transactionid'],
                            'note'          => $data['note'],
                            'client_id'     => '',
                        ];
                        $amount -= $payment_data['amount'];
                        $this->load->model('payments_model');
                        $this->payments_model->process_payment($payment_data, '');
                    }
                }
                // If there's remaining amount, record it as a payment without invoice
                if ($amount > 0) {
                    $extra_data = [
                        'amount'        => $amount,
                        'client_id'     => $data['client_id'],
                        'invoiceid'     => 0,
                        'date'          => $data['date'],
                        'paymentmode'   => $data['paymentmode'],
                        'paymentmethod' => $data['paymentmethod'],
                        'transactionid' => $data['transactionid'],
                        'note'          => $data['note'],
                    ];
                    $this->db->insert(db_prefix() . 'invoicepaymentrecords', $extra_data);
                }
                echo json_encode([
                    'success' => true,
                    'message' => _l('added_successfully', _l('payment')),
                ]);
            } else {
                $payment_data = [
                    'amount'        => $amount,
                    'client_id'     => $data['client_id'],
                    'invoiceid'     => 0,
                    'date'          => $data['date'],
                    'paymentmode'   => $data['paymentmode'],
                    'paymentmethod' => $data['paymentmethod'],
                    'transactionid' => $data['transactionid'],
                    'note'          => $data['note'],
                ];
                $this->db->insert(db_prefix() . 'invoicepaymentrecords', $payment_data);
                echo json_encode([
                    'success' => true,
                    'message' => _l('added_successfully', _l('payment')),
                ]);
            }
            die;
        }
    }

    public function get_send_to_client_modal($id)
    {
        if (!has_permission('payments', '', 'view')) {
            ajax_access_denied();
        }

        $payment = $this->payments_model->get($id);
        if (!$payment) {
            echo 'Payment not found';
            return;
        }

        $this->load->model('invoices_model');
        $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
        
        $data['payment'] = $payment;
        $this->load->view('admin/payments/send_to_client', $data);
    }
}
