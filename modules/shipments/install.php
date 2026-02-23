<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'shipment_headers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shipment_headers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shipment_number` varchar(100) NOT NULL,
      `carrier` varchar(200) DEFAULT NULL,
      `etd` date DEFAULT NULL,
      `eta` date DEFAULT NULL,
      `currency_base` varchar(10) NOT NULL DEFAULT 'USD',
      `exchange_rate_fixed` decimal(15,4) NOT NULL DEFAULT '1.0000',
      `status` varchar(50) NOT NULL DEFAULT 'Draft',
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'shipment_lines')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shipment_lines` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shipment_id` int(11) NOT NULL,
      `item_id` int(11) NOT NULL,
      `po_ref_id` int(11) DEFAULT NULL,
      `qty_shipped` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `unit_fob_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `net_weight_kg` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `volume_cbm` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `duty_percent` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `landed_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
      PRIMARY KEY (`id`),
      KEY `shipment_id` (`shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'cost_definitions')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "cost_definitions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(200) NOT NULL,
      `allocation_default` varchar(50) NOT NULL DEFAULT 'value',
      `layer_level` int(11) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    // Seed default costs
    $CI->db->query("INSERT INTO `" . db_prefix() . "cost_definitions` (`name`, `allocation_default`, `layer_level`) VALUES 
        ('Ocean Freight', 'volume', 2),
        ('Marine Insurance', 'value', 2),
        ('Port Handling', 'volume', 3),
        ('Customs Duty', 'value', 3),
        ('Local Trucking', 'weight', 4)
    ");
}

if (!$CI->db->table_exists(db_prefix() . 'shipment_cost_allocations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shipment_cost_allocations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shipment_id` int(11) NOT NULL,
      `cost_def_id` int(11) NOT NULL,
      `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `currency` varchar(10) NOT NULL DEFAULT 'USD',
      `exchange_rate` decimal(15,4) NOT NULL DEFAULT '1.0000',
      `allocation_method` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `shipment_id` (`shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
