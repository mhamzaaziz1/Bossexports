<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Reports
Description: Dynamic report generation module with AI/NLP capabilities
Version: 1.0.0
Requires at least: 2.3.*
*/

define('SMART_REPORTS_MODULE_NAME', 'smart_reports');

// Add hooks for module initialization
hooks()->add_action('admin_init', 'smart_reports_module_init_menu_items');
hooks()->add_action('admin_init', 'smart_reports_permissions');
hooks()->add_action('admin_init', 'smart_reports_register_settings');
hooks()->add_filter('before_settings_updated', 'smart_reports_settings_validate');

// Register activation hook
register_activation_hook(SMART_REPORTS_MODULE_NAME, 'smart_reports_module_activation_hook');

// Register language files
register_language_files(SMART_REPORTS_MODULE_NAME, [SMART_REPORTS_MODULE_NAME]);

/**
 * Register activation module hook
 */
function smart_reports_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Initialize module menu items in admin_init hook
 * @return null
 */
function smart_reports_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('smart_reports', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-reports', [
            'slug'     => 'smart-reports',
            'name'     => _l('smart_reports'),
            'icon'     => 'fa fa-bar-chart',
            'href'     => admin_url('smart_reports'),
            'position' => 30,
        ]);
    }
}

/**
 * Register module permissions
 */
function smart_reports_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('smart_reports', $capabilities, _l('smart_reports'));
}

/**
 * Register module settings
 */
function smart_reports_register_settings()
{
    $CI = &get_instance();

    // Add tab in settings
    $CI->app_tabs->add_settings_tab('smart-reports', [
        'name'     => _l('smart_reports'),
        'view'     => 'smart_reports/settings',
        'position' => 36,
    ]);

    // Add OpenAI API key setting if it doesn't exist
    if (!option_exists('openai_api_key')) {
        add_option('openai_api_key', '', 1);
    }
}

/**
 * Validate settings before update
 * @param  array $data Settings data
 * @return array       Modified settings data
 */
function smart_reports_settings_validate($data)
{
    if (isset($data['settings']['openai_api_key'])) {
        // Trim the API key to remove any whitespace
        $data['settings']['openai_api_key'] = trim($data['settings']['openai_api_key']);
    }

    return $data;
}
