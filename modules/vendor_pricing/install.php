<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'vendor_pricing_po_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'vendor_pricing_po_details` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `pur_order_id` int(11) NOT NULL,
        `item_code` varchar(200) NOT NULL,
        `vendor_price` decimal(15,2) NOT NULL,
        `status` varchar(50) NOT NULL DEFAULT "pending",
        `date_submitted` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$CI->load->helper('perfex_emails');

create_email_template(
    'Vendor Pricing Request', 
    'Hi {vendor_name}! <br /><br />Please click the link below to submit your prices for Purchase Order <b>{pur_order_number}</b>:<br /><br /><a href="{vendor_pricing_link}">{vendor_pricing_link}</a><br /><br />Thank you.', 
    'vendor_pricing', 
    'Vendor Pricing Request (Sent to Vendor)', 
    'vendor-pricing-request'
);
