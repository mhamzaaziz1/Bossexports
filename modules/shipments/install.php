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
      `country_of_origin` varchar(10) DEFAULT NULL,
      `port_of_loading` varchar(200) DEFAULT NULL,
      `port_of_discharge` varchar(200) DEFAULT NULL,
      `incoterm` varchar(50) DEFAULT NULL,
      `transport_mode` varchar(50) DEFAULT NULL,
      `bl_awb_number` varchar(100) DEFAULT NULL,
      `vessel_flight` varchar(150) DEFAULT NULL,
      `compliance_status` varchar(50) NOT NULL DEFAULT 'Pending',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Add missing fields for existing installations
    $fields = [
        'country_of_origin' => 'VARCHAR(10) DEFAULT NULL',
        'port_of_loading' => 'VARCHAR(200) DEFAULT NULL',
        'port_of_discharge' => 'VARCHAR(200) DEFAULT NULL',
        'incoterm' => 'VARCHAR(50) DEFAULT NULL',
        'transport_mode' => 'VARCHAR(50) DEFAULT NULL',
        'bl_awb_number' => 'VARCHAR(100) DEFAULT NULL',
        'vessel_flight' => 'VARCHAR(150) DEFAULT NULL',
        'compliance_status' => "VARCHAR(50) NOT NULL DEFAULT 'Pending'"
    ];
    foreach ($fields as $field => $def) {
        if (!$CI->db->field_exists($field, db_prefix() . 'shipment_headers')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "shipment_headers` ADD `$field` $def;");
        }
    }
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
      `hs_code` varchar(50) DEFAULT NULL,
      `customs_value` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `specific_duty_rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
      `country_of_origin` varchar(10) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `shipment_id` (`shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Add missing fields
    $fields = [
        'hs_code' => 'VARCHAR(50) DEFAULT NULL',
        'customs_value' => "DECIMAL(15,4) NOT NULL DEFAULT '0.0000'",
        'specific_duty_rate' => "DECIMAL(15,4) NOT NULL DEFAULT '0.0000'",
        'country_of_origin' => 'VARCHAR(10) DEFAULT NULL'
    ];
    foreach ($fields as $field => $def) {
        if (!$CI->db->field_exists($field, db_prefix() . 'shipment_lines')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "shipment_lines` ADD `$field` $def;");
        }
    }
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

// New Tables for Advanced Logistics
if (!$CI->db->table_exists(db_prefix() . 'country_import_rules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "country_import_rules` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `country_code` varchar(10) NOT NULL,
      `rule_type` varchar(100) NOT NULL,
      `rule_value` varchar(255) NOT NULL,
      `description` text,
      PRIMARY KEY (`id`),
      KEY `country_code` (`country_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'shipment_milestones')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shipment_milestones` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shipment_id` int(11) NOT NULL,
      `milestone_name` varchar(150) NOT NULL,
      `estimated_date` date DEFAULT NULL,
      `actual_date` date DEFAULT NULL,
      `status` varchar(50) NOT NULL DEFAULT 'Pending',
      PRIMARY KEY (`id`),
      KEY `shipment_id` (`shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'shipment_documents')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shipment_documents` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shipment_id` int(11) NOT NULL,
      `document_type` varchar(150) NOT NULL,
      `is_required` tinyint(1) NOT NULL DEFAULT '0',
      `file_name` varchar(255) DEFAULT NULL,
      `file_path` varchar(255) DEFAULT NULL,
      `verification_status` varchar(50) NOT NULL DEFAULT 'Pending',
      PRIMARY KEY (`id`),
      KEY `shipment_id` (`shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
