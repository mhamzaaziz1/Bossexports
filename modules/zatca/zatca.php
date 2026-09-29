<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: ZATCA E-Invoicing
Description: ZATCA Phase 2 E-Invoicing Integration for Saudi Arabia (Fatoora).
Version: 1.0.0
Requires at least: 2.3.*
*/

define('ZATCA_MODULE_NAME', 'zatca');

hooks()->add_action('admin_init', 'zatca_module_init_menu_items');
hooks()->add_action('after_invoice_added', 'zatca_invoice_created');
hooks()->add_action('after_invoice_updated', 'zatca_invoice_updated');
hooks()->add_filter('module_zatca_action_links', 'module_zatca_action_links');

/**
* Register activation module hook
*/
register_activation_hook(ZATCA_MODULE_NAME, 'zatca_module_activation_hook');

function zatca_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(ZATCA_MODULE_NAME, [ZATCA_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function zatca_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('zatca', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('zatca', [
            'name'     => _l('zatca_compliance'),
            'icon'     => 'fa-solid fa-qrcode',
            'position' => 30,
        ]);

        $CI->app_menu->add_sidebar_children_item('zatca', [
            'slug'     => 'zatca-settings',
            'name'     => _l('settings'),
            'href'     => admin_url('zatca/settings'),
            'position' => 5,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('zatca', [
            'slug'     => 'zatca-history',
            'name'     => _l('zatca_history'),
            'href'     => admin_url('zatca/history'),
            'position' => 10,
        ]);
    }
}

/**
 * Add Settings Link in Module List
 */
function module_zatca_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('zatca/settings') . '">' . _l('settings') . '</a>';
    return $actions;
}

/**
 * Invoice Created Hook
 */
function zatca_invoice_created($invoice_id)
{
    // Logic to be implemented: Check if auto-report is enabled, then process
}

/**
 * Invoice Updated Hook
 */
function zatca_invoice_updated($invoice_id)
{
    // Logic to be implemented: Handle updates if not yet reported
}
