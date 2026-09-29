<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Fix Inventory
Description: Reconcile and fix item inventory levels based on approved vouchers.
Version: 1.0.0
Author: Antigravity
*/

define('FIX_INVENTORY_MODULE_NAME', 'fix_inventory');

hooks()->add_action('admin_init', 'fix_inventory_module_init_menu_items');

/**
 * Register menu items
 */
function fix_inventory_module_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();
        // Add to the reports children menu
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'fix-inventory-module',
            'name'     => 'Reconciliation and Fix',
            'icon'     => 'fa-solid fa-wrench',
            'href'     => admin_url('fix_inventory'),
            'position' => 99,
        ]);
    }
}
