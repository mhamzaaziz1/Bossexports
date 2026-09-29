<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'daraz_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "daraz_categories` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `commission_percentage` decimal(15,2) DEFAULT '0.00',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'daraz_profit_calculations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "daraz_profit_calculations` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `item_id` int(11) NOT NULL,
      `sale_price` decimal(15,2) DEFAULT '0.00',
      `product_cost` decimal(15,2) DEFAULT '0.00',
      `packaging_cost` decimal(15,2) DEFAULT '0.00',
      `shipping_fee_customer` decimal(15,2) DEFAULT '0.00',
      `actual_shipping_cost` decimal(15,2) DEFAULT '0.00',
      `commission_amount` decimal(15,2) DEFAULT '0.00',
      `payment_fee_amount` decimal(15,2) DEFAULT '0.00',
      `vat_on_fees` decimal(15,2) DEFAULT '0.00',
      `marketing_cost` decimal(15,2) DEFAULT '0.00',
      `net_profit` decimal(15,2) DEFAULT '0.00',
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'daraz_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "daraz_settings` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `value` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    // Seed default settings
    $CI->db->insert(db_prefix() . 'daraz_settings', ['name' => 'daraz_vat_percentage', 'value' => '16.00']);
    $CI->db->insert(db_prefix() . 'daraz_settings', ['name' => 'daraz_payment_fee_percentage', 'value' => '1.25']);
}

// Add daraz_category_id to items table if not exists
if (!$CI->db->field_exists('daraz_category_id', db_prefix() . 'items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'items` ADD `daraz_category_id` INT NULL AFTER `group_id`;');
}
