<?php
/**
 * Ensures that the script can't be accessed directly.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Modern Sales UI
Description: State of the art UI/UX Redesign for Proposals, Estimates, Invoices, Payments, and Credit Notes.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('MODERN_SALES_UI_MODULE_NAME', 'modern_sales_ui');

hooks()->add_action('admin_init', 'modern_sales_ui_init_menu_items');
hooks()->add_action('admin_head', 'modern_sales_ui_inject_styles');

/**
 * Register activation hook
 */
register_activation_hook(MODERN_SALES_UI_MODULE_NAME, 'modern_sales_ui_activation_hook');

function modern_sales_ui_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MODERN_SALES_UI_MODULE_NAME, [MODERN_SALES_UI_MODULE_NAME]);

/**
 * Init menu items module
 * @return null
 */
function modern_sales_ui_init_menu_items()
{
    // No menu items needed for this UI-only module, but hook is kept for future extensibility.
}

/**
 * Inject the Modern Sales UI CSS into the admin head
 */
function modern_sales_ui_inject_styles()
{
    $CI = &get_instance();
    
    // Check if we are in the admin area
    if (!function_exists('is_admin') || !is_admin()) {
        return;
    }

    $view_name = $CI->uri->segment(2);
    
    // Target specific controllers/views
    $target_views = [
        'proposals',
        'estimates',
        'invoices',
        'payments',
        'credit_notes'
    ];

    // If we are on one of the target pages, inject the CSS
    if (in_array($view_name, $target_views)) {
        echo '<link href="' . base_url('modules/modern_sales_ui/assets/css/sales_ui.css') . '?v=' . time() . '" rel="stylesheet" type="text/css" />';
    }
}
