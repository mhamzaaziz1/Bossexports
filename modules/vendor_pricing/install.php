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
