<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Create table for logging queries
if (!$CI->db->table_exists(db_prefix() . 'ai_query_builder_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'ai_query_builder_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `query` text NOT NULL,
        `sql` text NOT NULL,
        `execution_time` decimal(10,4) NOT NULL DEFAULT 0,
        `rows_returned` int(11) NOT NULL DEFAULT 0,
        `staff_id` int(11) NOT NULL,
        `date_created` datetime NOT NULL,
        `name` varchar(255) NULL DEFAULT NULL,
        `is_saved` tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Create table for API settings
if (!$CI->db->table_exists(db_prefix() . 'ai_query_builder_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'ai_query_builder_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `openai_api_key` varchar(255) NOT NULL,
        `model` varchar(50) NOT NULL DEFAULT "gpt-3.5-turbo",
        `max_rows` int(11) NOT NULL DEFAULT 100,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    // Insert default settings
    $CI->db->query('INSERT INTO `' . db_prefix() . 'ai_query_builder_settings` 
        (`openai_api_key`, `model`, `max_rows`) VALUES 
        ("", "gpt-3.5-turbo", 100)');
}

// Add permissions for AI Query Builder
$CI->load->model('roles_model');

// Get all roles
$roles = $CI->roles_model->get();

// Add permission to each role
foreach ($roles as $role) {
    // Get the role permissions
    $role_permissions = $CI->roles_model->get($role['roleid']);

    // Add the new permission
    if (!isset($role_permissions->permissions['ai_query_builder'])) {
        $role_permissions->permissions['ai_query_builder'] = [
            'view' => 1
        ];
    }

    // Update the role with the new permissions
    $CI->roles_model->update([
        'name' => $role_permissions->name,
        'permissions' => $role_permissions->permissions
    ], $role['roleid']);
}
