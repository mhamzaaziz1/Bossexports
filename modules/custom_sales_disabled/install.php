<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();


$key = 'custom_sales_disable_updating_deposit_invoice';
add_option($key, '1');

$key = 'custom_sales_hide_pricing_on_deposit_invoice';
add_option($key, '1');

$key = 'custom_sales_client_items_hidden_field_for_deposit_invoice';
add_option($key, json_encode(['rate', 'amount']));

// Create tables
$table = db_prefix() . 'custom_sales_items_titles';
if (!$CI->db->table_exists($table)) {
    $CI->db->query(
        "CREATE TABLE IF NOT EXISTS `" . $table . "` (
            `id` int NOT NULL AUTO_INCREMENT,
            `title` varchar(250) NOT NULL,
            `resources` varchar(250) NOT NULL,
            `resources_id` int NOT NULL,
            `child_item_order` int NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
    );
}


$table = db_prefix() . 'invoices';
if (!$CI->db->field_exists('deposit_from_estimate_id', $table)) {
    $CI->db->query("ALTER TABLE `$table` ADD `deposit_from_estimate_id` int DEFAULT NULL");
}
