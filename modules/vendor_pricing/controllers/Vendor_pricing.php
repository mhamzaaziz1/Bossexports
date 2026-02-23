<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_pricing extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vendor_pricing_model');
        $this->load->model('purchase/purchase_model');
    }

    public function index()
    {
        if (!has_permission('vendor_pricing', '', 'view')) {
            access_denied('vendor_pricing');
        }

        $data['title'] = _l('vendor_pricing');
        $data['pos']   = $this->vendor_pricing_model->get_pos_with_pricing();
        $this->load->view('admin_manage', $data);
    }

    public function view($po_id)
    {
        if (!has_permission('vendor_pricing', '', 'view')) {
            access_denied('vendor_pricing');
        }

        $pur_order = $this->purchase_model->get_pur_order($po_id);
        if (!$pur_order) {
            show_404();
        }

        $data['pur_order'] = $pur_order;
        $data['pur_order_detail'] = $this->purchase_model->get_pur_order_detail($po_id);
        
        $submitted_prices = $this->vendor_pricing_model->get_vendor_pricing($po_id);
        $prices_map = [];
        foreach ($submitted_prices as $sp) {
            $prices_map[$sp['item_code']] = $sp['vendor_price'];
        }
        $data['vendor_prices'] = $prices_map;
        $data['status'] = (!empty($submitted_prices)) ? $submitted_prices[0]['status'] : '';

        $data['title'] = _l('vendor_pricing') . ' - ' . $pur_order->pur_order_number;
        $this->load->view('admin_review_po', $data);
    }

    public function accept($po_id)
    {
        if (!has_permission('vendor_pricing', '', 'edit')) {
            access_denied('vendor_pricing');
        }
        $success = $this->vendor_pricing_model->accept_vendor_pricing($po_id);
        if ($success) {
            set_alert('success', _l('vendor_prices_accepted'));
        }
        redirect(admin_url('vendor_pricing/view/' . $po_id));
    }

    public function reject($po_id)
    {
        if (!has_permission('vendor_pricing', '', 'edit')) {
            access_denied('vendor_pricing');
        }
        $success = $this->vendor_pricing_model->reject_vendor_pricing($po_id);
        if ($success) {
            set_alert('success', _l('rejected'));
        }
        redirect(admin_url('vendor_pricing/view/' . $po_id));
    }

    public function send_email($po_id, $hash)
    {
        if (!has_permission('vendor_pricing', '', 'view')) {
            access_denied('vendor_pricing');
        }

        $pur_order = $this->purchase_model->get_pur_order($po_id);
        if (!$pur_order || $pur_order->hash != $hash) {
            show_404();
        }

        $this->db->where('userid', $pur_order->vendor);
        $this->db->where('is_primary', 1);
        $contact = $this->db->get(db_prefix() . 'pur_contacts')->row();

        if (!$contact || empty($contact->email)) {
            set_alert('danger', 'Vendor Primary Contact Email not found.');
            redirect(admin_url('purchase/purchase_order/' . $po_id));
        }

        $data = new stdClass();
        $data->receiver = $contact->email;
        $data->vendor_name = get_vendor_company_name($pur_order->vendor);
        $data->pur_order_number = $pur_order->pur_order_number;
        $data->vendor_pricing_link = site_url('vendor_pricing/vendor_po/view/' . $po_id . '/' . $hash);

        try {
            $sent = mail_template('vendor_pricing_request', 'vendor_pricing', clone $data)->send();
            if ($sent) {
                set_alert('success', 'Vendor Pricing Request email sent successfully.');
            } else {
                set_alert('warning', 'Failed to send Vendor Pricing Request email.');
            }
        } catch (Exception $e) {
            set_alert('warning', 'Failed to send Custom Email: ' . $e->getMessage());
        }

        redirect(admin_url('purchase/purchase_order/' . $po_id));
    }
}
