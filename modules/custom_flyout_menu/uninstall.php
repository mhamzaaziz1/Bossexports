<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Drop tables
$tables = [
    db_prefix() . 'cfm_menu_items',
    db_prefix() . 'cfm_menu_groups',
    db_prefix() . 'cfm_settings',
];

foreach ($tables as $table) {
    if ($CI->db->table_exists($table)) {
        $CI->db->query('DROP TABLE `' . $table . '`');
    }
}

// Remove options
$options = [
    'custom_flyout_menu_version',
    'custom_flyout_menu_installed',
    'custom_flyout_menu_active',
    'cfm_menu_cache_admin',
    'cfm_menu_cache_client',
];

foreach ($options as $option) {
    delete_option($option);
}

// Log
log_activity('Custom Flyout Menu uninstalled (data deleted)');
