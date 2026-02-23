<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Advance Analytics
Description: Advanced Data Analytics and Decision Support System
Version: 1.0.0
Requires at least: 2.3.*
*/

define('ADVANCE_ANALYTICS_MODULE_NAME', 'advance_analytics');

hooks()->add_action('admin_init', 'advance_analytics_module_init_menu_items');
hooks()->add_action('admin_init', 'advance_analytics_permissions');

/**
* Register activation module hook
*/
register_activation_hook(ADVANCE_ANALYTICS_MODULE_NAME, 'advance_analytics_module_activation_hook');

function advance_analytics_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(ADVANCE_ANALYTICS_MODULE_NAME, [ADVANCE_ANALYTICS_MODULE_NAME]);

/**
 * Init advance analytics module menu items in setup in admin_init hook
 * @return null
 */
function advance_analytics_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('advance_analytics', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('advance_analytics', [
            'name'     => _l('advance_analytics'),
            'href'     => admin_url('advance_analytics'),
            'position' => 30,
            'icon'     => 'fa fa-chart-line',
        ]);

        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-full-report',
            'name'     => 'Full Report Analytics',
            'href'     => admin_url('advance_analytics/full_report'),
            'position' => 0, // Top priority
        ]);
        
        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-dashboard',
            'name'     => _l('analytics_dashboard'),
            'href'     => admin_url('advance_analytics'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-sales',
            'name'     => 'Deep Sales Analytics',
            'href'     => admin_url('advance_analytics/sales'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-finance',
            'name'     => _l('analytics_finance'),
            'href'     => admin_url('advance_analytics/finance'),
            'position' => 10,
        ]);



        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-projects',
            'name'     => _l('analytics_projects'),
            'href'     => admin_url('advance_analytics/projects'),
            'position' => 20,
        ]);
        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-dss',
            'name'     => 'Decision Support System',
            'href'     => admin_url('advance_analytics/dss'),
            'position' => 25,
        ]);

        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-customers-deep',
            'name'     => 'Deep Customer Analytics',
            'href'     => admin_url('advance_analytics/customers_deep_dive'),
            'position' => 30,
        ]);

        $CI->app_menu->add_sidebar_children_item('advance_analytics', [
            'slug'     => 'advance-analytics-items-deep',
            'name'     => 'Deep Items Analytics',
            'href'     => admin_url('advance_analytics/items_deep_dive'),
            'position' => 35,
        ]);
    }
}

function advance_analytics_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];

    register_staff_capabilities('advance_analytics', $capabilities, _l('advance_analytics'));
}

hooks()->add_filter('admin_client_profile_tabs', 'advance_analytics_client_tab');

function advance_analytics_client_tab($tabs)
{
    if (has_permission('advance_analytics', '', 'view')) {
        $tabs[] = [
            'key'      => 'advance_analytics',
            'name'     => _l('advanced_analytics'),
            'icon'     => 'fa fa-chart-line',
            'view'     => 'advance_analytics/advanced_analytics',
            'position' => 50,
        ];
    }

    return $tabs;
}
