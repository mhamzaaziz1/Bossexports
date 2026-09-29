<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Aging extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aging_model');
    }

    public function items()
    {
        if (!has_permission('aging', '', 'view_items')) {
            access_denied('aging');
        }
        $data['title'] = _l('item_aging');
        $data['items'] = $this->aging_model->get_item_aging_data();
        $this->load->view('items', $data);
    }

    public function customers()
    {
        if (!has_permission('aging', '', 'view_customers')) {
            access_denied('aging');
        }
        $data['title'] = _l('customer_aging');
        $data['customers'] = $this->aging_model->get_customer_aging_data();
        $this->load->view('customers', $data);
    }

    public function vendors()
    {
        if (!has_permission('aging', '', 'view_vendors')) {
            access_denied('aging');
        }
        $data['title'] = _l('vendor_aging');
        $data['vendors'] = $this->aging_model->get_vendor_aging_data();
        $this->load->view('vendors', $data);
    }

    public function pdf($type = '')
    {
        $data = ['type' => $type];
        if ($type == 'items' && has_permission('aging', '', 'view_items')) {
            $data['items'] = $this->aging_model->get_item_aging_data();
        } elseif ($type == 'customers' && has_permission('aging', '', 'view_customers')) {
            $data['customers'] = $this->aging_model->get_customer_aging_data();
        } elseif ($type == 'vendors' && has_permission('aging', '', 'view_vendors')) {
            $data['vendors'] = $this->aging_model->get_vendor_aging_data();
        } else {
            access_denied('aging');
        }
        
        $this->load->library(AGING_MODULE_NAME . '/aging_pdf');
        $pdf = aging_pdf($data);
        $pdf->Output('Aging_Report_' . ucfirst($type) . '_' . date('Y-m-d') . '.pdf', 'D');
    }
}
