<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proforma extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('proforma_model');
    }

    public function index()
    {
        if (!has_permission('proforma', '', 'view') && !has_permission('proforma', '', 'view_own')) {
            access_denied('proforma');
        }

        $data['title'] = _l('proforma_invoices');
        $this->load->view('manage', $data);
    }

    public function invoice($id = '')
    {
        if ($this->input->post()) {
            $proforma_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('proforma', '', 'create')) {
                    access_denied('proforma');
                }
                $id = $this->proforma_model->add($proforma_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('proforma_invoice')));
                    redirect(admin_url('proforma'));
                }
            } else {
                if (!has_permission('proforma', '', 'edit')) {
                    access_denied('proforma');
                }
                $success = $this->proforma_model->update($proforma_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('proforma_invoice')));
                }
                redirect(admin_url('proforma'));
            }
        }

        if ($id == '') {
            $title = _l('create_new_proforma_invoice');
        } else {
            $proforma = $this->proforma_model->get($id);
            $data['proforma'] = $proforma;
            $title = _l('edit', _l('proforma_invoice')) . ' ' . $proforma->number;
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        // Load other necessary data (tax modes, payment modes etc)
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        
        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        
        $data['title'] = $title;
        $this->load->view('proforma', $data);
    }
    
    public function convert($id)
    {
        if (!has_permission('proforma', '', 'create')) { // Need create permission for invoice really
            access_denied('proforma');
        }
        
        $invoice_id = $this->proforma_model->convert_to_invoice($id);
        if ($invoice_id) {
            set_alert('success', _l('proforma_invoice_converted_to_invoice'));
            redirect(admin_url('invoices/list_invoices/' . $invoice_id));
        } else {
            set_alert('warning', _l('proforma_invoice_conversion_failed'));
            redirect(admin_url('proforma/invoice/' . $id));
        }
    }

    public function convert_to_estimate($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        
        $estimate_id = $this->proforma_model->convert_to_estimate($id);
        if ($estimate_id) {
            set_alert('success', _l('added_successfully', _l('estimate')));
            redirect(admin_url('estimates/list_estimates/' . $estimate_id));
        } else {
            set_alert('warning', 'Conversion failed');
            redirect(admin_url('proforma/invoice/' . $id));
        }
    }

    public function convert_to_proposal($id)
    {
        if (!has_permission('proposals', '', 'create')) {
            access_denied('proposals');
        }
        
        $proposal_id = $this->proforma_model->convert_to_proposal($id);
        if ($proposal_id) {
            set_alert('success', _l('added_successfully', _l('proposal')));
            redirect(admin_url('proposals/list_proposals/' . $proposal_id));
        } else {
            set_alert('warning', 'Conversion failed');
            redirect(admin_url('proforma/invoice/' . $id));
        }
    }
    
    public function record_payment() {
         if ($this->input->post()) {
            $data = $this->input->post();
            $data['proforma_invoice_id'] = $data['invoiceid']; // View sends invoiceid typically
            unset($data['invoiceid']);
            
            $success = $this->proforma_model->add_payment($data);
            if ($success) {
                set_alert('success', _l('payment_added_successfully'));
            }
            redirect(admin_url('proforma'));
         }
    }

    public function pdf($id)
    {
        if (!has_permission('proforma', '', 'view') && !has_permission('proforma', '', 'view_own')) {
            access_denied('proforma');
        }

        if (!$id) {
            redirect(admin_url('proforma'));
        }

        $proforma = $this->proforma_model->get($id);

        // Generate HTML for PDF (Warehouse Pattern)
        // We act on the user request to "print data as html" to debug data/layout first.
        $html = $this->proforma_model->get_pdf_html($id);
        
        if($this->input->get('print_html')){
            echo $html;
            die;
        }

        $proforma->content_html = $html;
        
        $proforma_number = format_proforma_number($proforma->id);

        try {
            $pdf = proforma_pdf($proforma);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        ob_end_clean();

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($proforma_number)) . '.pdf', $type);
    }
    
    public function get_proforma_data_ajax($id)
    {
        if (!has_permission('proforma', '', 'view') && !has_permission('proforma', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        $proforma = $this->proforma_model->get($id);

        if (!$proforma) {
            echo _l('proforma_invoice_not_found');
            die;
        }
        
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        
        $data['proforma'] = $proforma;
        
        $this->load->model('currencies_model');
        // Ensure currency is available for formatting
        // In get() we join currencies so we have currency_name etc ideally.
        
        // Check for tasks
        $this->load->model('tasks_model');
        $data['tasks'] = $this->tasks_model->get('', ['rel_id' => $id, 'rel_type' => 'proforma']); // Only if we want to list them or count
        $data['totalNotes'] = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'proforma']);

        // Load the html view
        $this->load->view('proforma_preview_template', $data);
    }

    public function view($id, $hash)
    {
        $proforma = $this->proforma_model->get($id);

        if (!$proforma || $proforma->hash != $hash) {
            show_404();
        }

        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        
        $data['proforma'] = $proforma;
        $data['title'] = $proforma->number;
        
        $this->load->view('proformahtml', $data);
    }
    public function send_to_email($id)
    {
        if (!has_permission('proforma', '', 'view') && !has_permission('proforma', '', 'view_own')) {
            access_denied('proforma');
        }
        
        $success = false;

        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->proforma_model->send_proforma_to_client($id, 'proforma_invoice_send_to_client', $data);
        } else {
             // Fallback for direct link (quick send) if needed, or redirect
             $success = $this->proforma_model->send_proforma_to_client($id, 'proforma_invoice_send_to_client');
        }
        
        if ($success) {
            set_alert('success', 'Proforma Invoice Sent to Client Successfully');
        } else {
            set_alert('warning', 'Failed to send Proforma Invoice');
        }
        
        redirect(admin_url('proforma/invoice/' . $id));
    }

    public function send_mail_modal($id)
    {
        if (!has_permission('proforma', '', 'view') && !has_permission('proforma', '', 'view_own')) {
            access_denied('proforma');
        }

        $this->load->model('clients_model');
        
        $proforma = $this->proforma_model->get($id);
        
        $template_slug = 'proforma_invoice_send_to_client';
        $template_name = $template_slug;

        $contacts = $this->clients_model->get_contacts($proforma->clientid, ['active' => 1, 'invoice_emails' => 1]);
        $primary_contact = isset($contacts[0]) ? $contacts[0] : null;
        $primary_email = $primary_contact ? $primary_contact['email'] : '';
        $primary_contact_id = $primary_contact ? $primary_contact['id'] : '';

        $template_lib = mail_template($template_slug, 'proforma', $id, $primary_contact_id);

        $data['template'] = $template_lib->prepare($primary_email);
        $data['template_name'] = $template_name;
        $data['proforma'] = $proforma;
        
        $this->load->view('proforma/send_to_client', $data);
    }
}

