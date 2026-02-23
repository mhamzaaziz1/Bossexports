<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_po extends App_Controller
{
    public function view($id, $hash)
    {
        check_pur_order_restrictions($id, $hash);

        $this->load->model('purchase/purchase_model');
        $this->load->model('vendor_pricing_model');

        if ($this->input->post()) {
            $prices = $this->input->post('vendor_price');
            if ($prices && is_array($prices)) {
                $success = $this->vendor_pricing_model->submit_vendor_pricing($id, $prices);
                if ($success) {
                    set_alert('success', _l('vendor_prices_submitted'));
                    redirect(site_url('vendor_pricing/vendor_po/view/' . $id . '/' . $hash));
                }
            }
        }

        $pur_order = $this->purchase_model->get_pur_order($id);
        if (!$pur_order) {
            show_404();
        }

        $data['pur_order'] = $pur_order;
        $data['pur_order_detail'] = $this->purchase_model->get_pur_order_detail($id);
        
        // Get existing submissions if any
        $submitted_prices = $this->vendor_pricing_model->get_vendor_pricing($id);
        $prices_map = [];
        foreach ($submitted_prices as $sp) {
            $prices_map[$sp['item_code']] = $sp['vendor_price'];
        }
        $data['vendor_prices'] = $prices_map;
        $data['status'] = (!empty($submitted_prices)) ? $submitted_prices[0]['status'] : '';

        $data['title'] = $pur_order->pur_order_name;
        
        $this->load->view('vendor_po_view', $data);
    }
}
