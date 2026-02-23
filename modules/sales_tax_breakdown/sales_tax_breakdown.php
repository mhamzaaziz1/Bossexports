<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Sales Tax Breakdown
Description: Show per item tax amount in sales documents
Version: 1.0.0
Author: Hamza
*/

hooks()->add_action('app_admin_footer', 'sales_tax_breakdown_inject_js');
hooks()->add_filter('items_table_class', 'sales_tax_breakdown_table_class', 10, 5);

function sales_tax_breakdown_inject_js()
{
    $CI = &get_instance();
    echo $CI->load->view('sales_tax_breakdown/inject_js', [], true);
}

function sales_tax_breakdown_table_class($class, $transaction, $type, $for, $admin_preview)
{
    if ($type == 'invoice' || $type == 'estimate' || $type == 'proposal' || $type == 'credit_note') {
        require_once(__DIR__ . '/libraries/Sales_tax_breakdown_items_table.php');
        return new Sales_tax_breakdown_items_table($transaction, $type, $for, $admin_preview);
    }

    return $class;
}
