<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Fast Load & Cache Builder
Description: Enhances Perfex CRM performance by minifying HTML output, enabling database query cache and optimizing app load.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('FAST_LOAD_MODULE_NAME', 'fast_load');

hooks()->add_action('admin_init', 'fast_load_init_menu_items');
hooks()->add_action('app_init', 'fast_load_register_ci_hook');

function fast_load_register_ci_hook()
{
    // Fast Load logic - Send Cache Headers if enabled
    if (get_option('fast_load_browser_cache') == 1) {
        $CI = &get_instance();
        if (!is_admin() && !$CI->input->post()) {
             $CI->output->set_header("Cache-Control: private, max-age=3600, must-revalidate");
        }
    }
    
    // Check if we should enable db query cache
    if (get_option('fast_load_enable_db_cache') == 1) {
        $CI = &get_instance();
        // Only cache GET requests outside admin
        if (!$CI->input->post() && !is_admin()) {
             $CI->db->cache_on();
        }
    }
}

/**
 * Register activation module hook
 */
register_activation_hook(FAST_LOAD_MODULE_NAME, 'fast_load_activation_hook');

function fast_load_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Init menu items
 */
function fast_load_init_menu_items()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('fast_load', [
            'collapse' => true,
            'name'     => 'Fast Load Settings',
            'position' => 35,
        ]);

        $CI->app_menu->add_setup_children_item('fast_load', [
            'slug'     => 'fast-load-options',
            'name'     => 'Configuration',
            'href'     => admin_url('fast_load/settings'),
            'position' => 1,
        ]);
    }
}

