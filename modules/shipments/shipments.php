<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Shipments / Landed Cost
Description: Track shipments, calculate landed costs (Base -> FOB -> CIF -> DDP), and commit to stock.
Version: 1.0.0
Requires at least: 2.3.*
Author: Antigravity
*/

define('SHIPMENTS_MODULE_NAME', 'shipments');

hooks()->add_action('admin_init', 'shipments_module_init_menu_items');
hooks()->add_action('admin_init', 'shipments_permissions');
hooks()->add_action('app_admin_footer', 'shipments_load_js');

/**
 * Load module JS
 */
function shipments_load_js()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    if (!(strpos($viewuri, 'admin/shipments/shipment') === false)) {
        echo '<script src="' . module_dir_url(SHIPMENTS_MODULE_NAME, 'assets/js/shipments.js') . '"></script>';
    }
}

/**
* Register activation module hook
*/
register_activation_hook(SHIPMENTS_MODULE_NAME, 'shipments_module_activation_hook');

function shipments_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SHIPMENTS_MODULE_NAME, [SHIPMENTS_MODULE_NAME]);

/**
 * Init shipments module menu items in setup in admin_init hook
 * @return null
 */
function shipments_module_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission('shipments', '', 'view') || has_permission('shipments', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('shipments', [
            'name'     => _l('shipments'),
            'icon'     => 'fa fa-ship', 
            'position' => 30,
        ]);

        $CI->app_menu->add_sidebar_children_item('shipments', [
            'slug'     => 'shipments_list',
            'name'     => _l('shipments_list'),
            'href'     => admin_url('shipments'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('shipments', [
            'slug'     => 'cost_definitions',
            'name'     => _l('cost_definitions'),
            'href'     => admin_url('shipments/cost_definitions'),
            'position' => 5,
        ]);
    }
}

/**
 * shipments permissions
 */
function shipments_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('shipments', $capabilities, _l('shipments'));
}
