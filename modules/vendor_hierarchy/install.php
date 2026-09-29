<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add parent_vendor_id column to tblpur_vendor if it doesn't exist
if (!$CI->db->field_exists('parent_vendor_id', db_prefix() . 'pur_vendor')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'pur_vendor`
        ADD COLUMN `parent_vendor_id` INT(11) NULL DEFAULT NULL AFTER `addedfrom`,
        ADD INDEX `idx_parent_vendor_id` (`parent_vendor_id`)
    ;');
}

// Create hierarchy settings table
if (!$CI->db->table_exists(db_prefix() . 'vendor_hierarchy_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'vendor_hierarchy_settings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL,
        `setting_value` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Insert default level labels
    $CI->db->query('INSERT INTO `' . db_prefix() . 'vendor_hierarchy_settings` (`setting_key`, `setting_value`) VALUES
        ("level_1_label", "Group"),
        ("level_2_label", "Company"),
        ("level_3_label", "Regional"),
        ("level_4_label", "Branch")
    ;');
}
