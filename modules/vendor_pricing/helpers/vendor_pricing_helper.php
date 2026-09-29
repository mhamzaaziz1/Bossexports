<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check purchase order restrictions for Vendor Pricing specifically
 *
 * @param  int $id
 * @param  string $hash
 */
function check_vendor_pricing_po_restrictions($id, $hash)
{
    $CI = & get_instance();
    $CI->load->model('purchase/purchase_model');

    if (!$hash || !$id) {
        show_404();
    }

    $pur_order = $CI->purchase_model->get_pur_order($id);
    if (!$pur_order || ($pur_order->hash != $hash)) {
        show_404();
    }
}
