<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Create smart_reports table if it doesn't exist
if (!$CI->db->table_exists(db_prefix() . 'smart_reports')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "smart_reports` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(191) NOT NULL,
      `report_type` varchar(50) NOT NULL,
      `date_range_start` date DEFAULT NULL,
      `date_range_end` date DEFAULT NULL,
      `group_by` varchar(50) DEFAULT NULL,
      `metric` varchar(50) DEFAULT NULL,
      `filter_by` text DEFAULT NULL,
      `sort_by` varchar(50) DEFAULT NULL,
      `limit_results` int(11) DEFAULT 10,
      `output_format` varchar(20) DEFAULT 'table',
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create smart_reports_ai_logs table if it doesn't exist
if (!$CI->db->table_exists(db_prefix() . 'smart_reports_ai_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "smart_reports_ai_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_id` int(11) DEFAULT NULL,
      `query_text` text NOT NULL,
      `generated_sql` text NOT NULL,
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `report_id` (`report_id`),
      KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create smart_reports_saved table if it doesn't exist
if (!$CI->db->table_exists(db_prefix() . 'smart_reports_saved')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "smart_reports_saved` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_id` int(11) NOT NULL,
      `name` varchar(191) NOT NULL,
      `description` text DEFAULT NULL,
      `is_favorite` tinyint(1) DEFAULT 0,
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `report_id` (`report_id`),
      KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
