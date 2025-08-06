<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Beatroute Integration
Description: Two-way integration between Perfex CRM and Beatroute for SKUs, Customers, Invoices, and Payments
Version: 1.0.0
Requires at least: 2.3.*
Author: System
*/

// Include module constants
require_once(__DIR__ . '/module_constants.php');

// Register hooks
hooks()->add_action('admin_init', 'beatroute_integration_module_init_menu_items');
hooks()->add_action('admin_init', 'beatroute_integration_add_settings_tab');
hooks()->add_action('admin_init', 'beatroute_integration_exclude_uri');

// Exclude webhook URLs from CSRF protection
function beatroute_integration_exclude_uri() {
    $CI = &get_instance();
    $CI->load->config('migration');
    $update_info = $CI->config->item('migration_version');

    if(!get_option('current_perfex_version')) {
        update_option('current_perfex_version', $update_info);
    }

    if(!get_option('excluded_uri_for_beatroute_integration_once') || get_option('current_perfex_version') != $update_info) {
        $myfile = fopen(APPPATH."config/config.php", "a") or die("Unable to open file!");
        $txt = "if(!isset(\$config['csrf_exclude_uris'])) {
            \$config['csrf_exclude_uris']=[];
        }";
        fwrite($myfile, "\n". $txt);
        $txt = "\$config['csrf_exclude_uris'] = array_merge(\$config['csrf_exclude_uris'],array('beatroute_integration/webhook'));";
        fwrite($myfile, "\n". $txt);
        fclose($myfile);
        update_option('current_perfex_version', $update_info);
        update_option('excluded_uri_for_beatroute_integration_once', 1);
    }
}

// Add settings tab
function beatroute_integration_add_settings_tab() {
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('beatroute_integration', [
        'name'     => _l('beatroute_integration'),
        'view'     => BEATROUTE_INTEGRATION_MODULE_NAME . '/settings',
        'position' => 101,
    ]);
}

// Register activation hook
register_activation_hook(BEATROUTE_INTEGRATION_MODULE_NAME, 'beatroute_integration_module_activation_hook');

function beatroute_integration_module_activation_hook() {
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

// Register language files
register_language_files(BEATROUTE_INTEGRATION_MODULE_NAME, [BEATROUTE_INTEGRATION_MODULE_NAME]);

// Initialize menu items
function beatroute_integration_module_init_menu_items() {
    $CI = &get_instance();

    if (has_permission('beatroute_integration', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('beatroute-menu', [
            'name'     => 'Beatroute Integration',
            'collapse' => true,
            'position' => 11,
            'icon'     => 'fa fa-exchange',
        ]);

        $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
            'slug'     => 'beatroute-skus',
            'name'     => _l('skus'),
            'href'     => admin_url('beatroute_integration/skus'),
            'position' => 11,
        ]);

        $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
            'slug'     => 'beatroute-live-skus',
            'name'     => _l('beatroute_live_skus'),
            'href'     => admin_url('beatroute_integration/live_skus'),
            'position' => 12,
        ]);

        $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
            'slug'     => 'beatroute-customers',
            'name'     => _l('customers'),
            'href'     => admin_url('beatroute_integration/customers'),
            'position' => 13,
        ]);

        $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
            'slug'     => 'beatroute-invoices',
            'name'     => _l('invoices'),
            'href'     => admin_url('beatroute_integration/invoices'),
            'position' => 14,
        ]);

        $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
            'slug'     => 'beatroute-payments',
            'name'     => _l('payments'),
            'href'     => admin_url('beatroute_integration/payments'),
            'position' => 15,
        ]);

        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('beatroute-menu', [
                'slug'     => 'beatroute-settings',
                'name'     => _l('settings'),
                'href'     => admin_url('beatroute_integration/settings'),
                'position' => 16,
            ]);
        }
    }

    // Add quick actions link
    $CI->app->add_quick_actions_link([
        'name'       => _l('beatroute_integration'),
        'permission' => 'beatroute_integration',
        'url'        => 'beatroute_integration',
        'position'   => 70,
    ]);
}

// Register permissions
hooks()->add_action('admin_init', 'beatroute_integration_register_permissions');

function beatroute_integration_register_permissions() {
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('beatroute_integration', $capabilities, _l('beatroute_integration'));
}

// Register cron task for synchronization
hooks()->add_action('after_cron_run', 'beatroute_integration_register_cron');

function beatroute_integration_register_cron() {
    $CI = &get_instance();
    $CI->load->helper(BEATROUTE_INTEGRATION_MODULE_NAME . '/beatroute');
    register_cron_task('beatroute_integration_cron');
}
