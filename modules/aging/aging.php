<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Aging Report
Description: Calculate aging reports for items, customers and vendor.
Version: 1.0.0
Requires at least: 2.3.2
*/

define('AGING_MODULE_NAME', 'aging');

hooks()->add_action('admin_init', 'aging_module_init_menu_items');
hooks()->add_action('admin_init', 'aging_permissions');

/**
* Load the module helper
*/
$CI = &get_instance();
$CI->load->helper(AGING_MODULE_NAME . '/aging');

/**
* Register activation hook
*/
register_activation_hook(AGING_MODULE_NAME, 'aging_module_activation_hook');

function aging_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files
*/
register_language_files(AGING_MODULE_NAME, [AGING_MODULE_NAME]);

/**
 * Init aging module menu items in setup in admin_init hook
 * @return null
 */
function aging_module_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission('aging', '', 'view_items')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'aging-report-items',
            'name'     => _l('item_aging'),
            'href'     => admin_url('aging/items'),
            'position' => 10,
        ]);
    }
    
    if (has_permission('aging', '', 'view_customers')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'aging-report-customers',
            'name'     => _l('customer_aging'),
            'href'     => admin_url('aging/customers'),
            'position' => 11,
        ]);
    }
    
    if (has_permission('aging', '', 'view_vendors')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'aging-report-vendors',
            'name'     => _l('vendor_aging'),
            'href'     => admin_url('aging/vendors'),
            'position' => 12,
        ]);
    }
}

hooks()->add_action('admin_init', 'aging_settings_tab');
function aging_settings_tab()
{
    $CI = &get_instance();
    $CI->app->add_settings_section('aging', [
        'name'     => _l('aging_report'),
        'view'     => 'aging/settings',
        'position' => 50,
    ]);
}

/**
 * [aging_permissions]
 * @return [type] [description]
 */
function aging_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view_items'   => _l('item_aging'),
        'view_customers' => _l('customer_aging'),
        'view_vendors' => _l('vendor_aging'),
    ];

    register_staff_capabilities('aging', $capabilities, _l('aging_report'));
}

/**
 * Register cron hook
 */
hooks()->add_action('after_cron_run', 'aging_send_periodic_emails');

function aging_send_periodic_emails()
{
    $CI = &get_instance();
    $CI->load->model(AGING_MODULE_NAME . '/aging_model');
    $CI->aging_model->send_reports_by_cron();
}
