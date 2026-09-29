<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Table 1: Groups
if (!$CI->db->table_exists(db_prefix() . 'cfm_menu_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'cfm_menu_groups` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `area` ENUM(\'admin\',\'client\') NOT NULL DEFAULT \'admin\',
      `name` VARCHAR(100) NOT NULL,
      `slug` VARCHAR(100) NOT NULL,
      `sort_order` INT(11) NOT NULL DEFAULT 0,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_area_slug` (`area`, `slug`),
      KEY `idx_area_active` (`area`, `active`),
      KEY `idx_sort_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table 2: Items
if (!$CI->db->table_exists(db_prefix() . 'cfm_menu_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'cfm_menu_items` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `area` ENUM(\'admin\',\'client\') NOT NULL DEFAULT \'admin\',
      `group_id` INT(11) UNSIGNED NULL,
      `parent_id` INT(11) UNSIGNED NULL,
      `label` VARCHAR(150) NOT NULL,
      `icon` VARCHAR(100) NULL,
      `url` VARCHAR(500) NOT NULL,
      `has_children` TINYINT(1) NOT NULL DEFAULT 0,
      `badge_text` VARCHAR(30) NULL,
      `badge_color_class` VARCHAR(50) NULL,
      `sort_order` INT(11) NOT NULL DEFAULT 0,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `permission` VARCHAR(100) NULL,
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      KEY `idx_area_active` (`area`, `active`),
      KEY `idx_group_id` (`group_id`),
      KEY `idx_parent_id` (`parent_id`),
      KEY `idx_sort_order` (`sort_order`),
      CONSTRAINT `fk_cfm_items_group` 
        FOREIGN KEY (`group_id`) 
        REFERENCES `' . db_prefix() . 'cfm_menu_groups` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
      CONSTRAINT `fk_cfm_items_parent` 
        FOREIGN KEY (`parent_id`) 
        REFERENCES `' . db_prefix() . 'cfm_menu_items` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Table 3: Settings
if (!$CI->db->table_exists(db_prefix() . 'cfm_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'cfm_settings` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `setting_key` VARCHAR(100) NOT NULL,
      `setting_value` TEXT NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    
    // Insert default settings
    $defaults = [
        ['setting_key' => 'flyout_trigger_mode', 'setting_value' => 'hover', 'updated_at' => date('Y-m-d H:i:s')],
        ['setting_key' => 'admin_menu_enabled', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
        ['setting_key' => 'client_menu_enabled', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
        ['setting_key' => 'exclude_super_admin', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
    ];
    $CI->db->insert_batch(db_prefix() . 'cfm_settings', $defaults);
}

// Version tracking
if (!get_option('custom_flyout_menu_version')) {
    add_option('custom_flyout_menu_version', '1.0.0');
}
if (!get_option('custom_flyout_menu_installed')) {
    add_option('custom_flyout_menu_installed', date('Y-m-d H:i:s'));
}
