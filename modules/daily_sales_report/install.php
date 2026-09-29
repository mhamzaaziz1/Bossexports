<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'daily_sales_reports')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "daily_sales_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `emails` text NOT NULL,
  `items` longtext NOT NULL,
  `supplier_ids` longtext NULL,
  `frequency` varchar(50) NOT NULL DEFAULT 'daily',
  `last_run` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Add columns if they don't exist for users upgrading from an early multi-report version
    if (!$CI->db->field_exists('frequency', db_prefix() . 'daily_sales_reports')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_reports` ADD `frequency` VARCHAR(50) NOT NULL DEFAULT "daily" AFTER `items`');
    }
    if (!$CI->db->field_exists('last_run', db_prefix() . 'daily_sales_reports')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_reports` ADD `last_run` DATE NULL DEFAULT NULL AFTER `frequency`');
    }
    if (!$CI->db->field_exists('active', db_prefix() . 'daily_sales_reports')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_reports` ADD `active` TINYINT(1) NOT NULL DEFAULT "1" AFTER `last_run`');
    }
    if (!$CI->db->field_exists('supplier_ids', db_prefix() . 'daily_sales_reports')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_reports` ADD `supplier_ids` LONGTEXT NULL AFTER `items`');
    }
}

// Migrate data from v1.x (tbloptions) if table is empty
if ($CI->db->count_all(db_prefix() . 'daily_sales_reports') == 0) {
    $emails = get_option('daily_sales_report_emails');
    $items = get_option('daily_sales_report_items');
    $last_run = get_option('daily_sales_report_last_run');
    
    if ($emails !== '' || ($items !== '' && $items !== '[]')) {
        $CI->db->insert(db_prefix() . 'daily_sales_reports', [
            'name' => 'Default Report',
            'emails' => $emails,
            'items' => $items ? $items : '[]',
            'last_run' => $last_run ? $last_run : null,
            'active' => 1
        ]);
        
        // Clean up old options
        delete_option('daily_sales_report_emails');
        delete_option('daily_sales_report_items');
        delete_option('daily_sales_report_last_run');
    }
}

if (!$CI->db->table_exists(db_prefix() . 'daily_sales_report_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "daily_sales_report_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NULL DEFAULT NULL,
  `report_name` varchar(255) NULL DEFAULT NULL,
  `date_sent` datetime NOT NULL,
  `email_to` varchar(255) NOT NULL,
  `records_count` int(11) NOT NULL DEFAULT '0',
  `csv_content` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Add columns if they don't exist
    if (!$CI->db->field_exists('report_id', db_prefix() . 'daily_sales_report_logs')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_report_logs` ADD `report_id` INT(11) NULL DEFAULT NULL AFTER `id`');
    }
    if (!$CI->db->field_exists('report_name', db_prefix() . 'daily_sales_report_logs')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_report_logs` ADD `report_name` VARCHAR(255) NULL DEFAULT NULL AFTER `report_id`');
    }
}
