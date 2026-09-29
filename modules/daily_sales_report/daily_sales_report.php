<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Daily Sales Report
Description: Sends a selected products sale report as a CSV attachment to specific emails at the end of the day.
Version: 2.0.0
Requires at least: 2.3.2
*/

define('DAILY_SALES_REPORT_MODULE_NAME', 'daily_sales_report');

hooks()->add_action('admin_init', 'daily_sales_report_init_menu_items');

register_activation_hook(DAILY_SALES_REPORT_MODULE_NAME, 'daily_sales_report_activation_hook');

function daily_sales_report_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(DAILY_SALES_REPORT_MODULE_NAME, [DAILY_SALES_REPORT_MODULE_NAME]);

/**
 * Init daily sales report module menu items in setup in admin_init hook
 * @return null
 */
function daily_sales_report_init_menu_items()
{
    $CI = &get_instance();
    if (is_admin()) {
        
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'daily-sales-report-settings',
            'name'     => _l('daily_sales_report'),
            'href'     => admin_url('daily_sales_report'),
            'position' => 30,
        ]);
        
        $CI->app_menu->add_setup_menu_item('daily-sales-report-api-settings', [
            'name'     => 'Optimetriks API Settings',
            'href'     => admin_url('daily_sales_report/api_settings'),
            'position' => 31,
        ]);

        $CI->app_menu->add_setup_menu_item('daily-sales-report-global-settings', [
            'name'     => _l('daily_sales_report_global_settings'),
            'href'     => admin_url('daily_sales_report/global_settings'),
            'position' => 30,
        ]);
    }
}

