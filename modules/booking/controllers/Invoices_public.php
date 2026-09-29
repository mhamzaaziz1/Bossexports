<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoices_public extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
    }

    public function index($status = false)
    {
        if (!has_contact_permission('invoices')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $where = [
            'clientid' => get_client_user_id(),
        ];

        if (is_numeric($status)) {
            $where['status'] = $status;
        }

        if (isset($where['status'])) {
            if ($where['status'] == Invoices_model::STATUS_DRAFT
                && get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
                unset($where['status']);
                $where['status !='] = Invoices_model::STATUS_DRAFT;
            }
        } else {
            if (get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
                $where['status !='] = Invoices_model::STATUS_DRAFT;
            }
        }

        $data['invoices'] = $this->invoices_model->get('', $where);
        $data['title']    = _l('clients_my_invoices');
        $this->data($data);
        $this->view('clients/invoices/invoices');
        $this->layout();
    }
}
