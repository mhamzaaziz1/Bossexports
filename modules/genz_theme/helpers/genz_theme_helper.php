<?php
/*
Module Name: GenZ Theme
Description: Modern and interactive theme with Gen Z aesthetics
Version: 1.0.0
Author: AI Developer
Requires at least: 2.3.2
*/
defined('BASEPATH') or exit('No direct script access allowed');

// Add hooks for admin area
hooks()->add_action('app_admin_head', 'genz_theme_head_component');
hooks()->add_action('app_admin_footer', 'genz_theme_footer_js_component');
hooks()->add_action('admin_init', 'genz_theme_settings_tab');
hooks()->add_action('app_admin_authentication_head', 'genz_theme_staff_login');

// Check if customers theme is enabled
if (get_option('genz_theme_customers') == '1') {
    hooks()->add_action('app_customers_head', 'genz_app_client_head_includes');
    hooks()->add_action('app_customers_footer', 'genz_theme_customers_footer_js_component');
}

/**
 * Theme staff login includes
 * @return stylesheet / script
 */
function genz_theme_staff_login()
{
    echo '<link href="' . base_url('modules/genz_theme/assets/css/staff_login_styles.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<script src="' . module_dir_url('genz_theme', 'assets/js/login.js') . '"></script>';
}

/**
 * Theme clients head includes
 * @return stylesheet / script
 */
function genz_app_client_head_includes()
{
    echo '<link href="' . module_dir_url('genz_theme', 'assets/css/clients/clients.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<link href="' . module_dir_url('genz_theme', 'assets/css/animations.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<script src="' . module_dir_url('genz_theme', 'assets/js/third-party/gsap.min.js') . '"></script>';
}

/**
 * Add theme settings tab in setup->settings
 * @return void
 */
function genz_theme_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('genz-theme-settings', [
        'name'     => 'GenZ Theme Settings',
        'view'     => 'genz_theme/genz_theme_settings',
        'position' => 50,
    ]);
}

/**
 * Injects theme CSS
 * @return null
 */
function genz_theme_head_component()
{
    // Only apply theme if enabled for staff
    if (get_option('genz_theme_staff') != '1') {
        return;
    }
    
    // Main theme styles
    echo '<link href="' . base_url('modules/genz_theme/assets/css/genz_styles.css') . '"  rel="stylesheet" type="text/css" >';
    
    // Animations if enabled
    if (get_option('genz_theme_animations') == '1') {
        echo '<link href="' . base_url('modules/genz_theme/assets/css/animations.css') . '"  rel="stylesheet" type="text/css" >';
    }
    
    // Dark mode if enabled
    if (get_option('genz_theme_dark_mode') == '1') {
        echo '<link href="' . base_url('modules/genz_theme/assets/css/dark_mode.css') . '"  rel="stylesheet" type="text/css" >';
    }
    
    // Custom colors
    echo '<style>
        :root {
            --genz-accent-color: ' . get_option('genz_theme_accent_color') . ';
            --genz-secondary-color: ' . get_option('genz_theme_secondary_color') . ';
        }
    </style>';
    
    // Third-party libraries
    echo '<script src="' . module_dir_url('genz_theme', 'assets/js/third-party/gsap.min.js') . '"></script>';
}

/**
 * Injects theme js components in footer
 * @return null
 */
function genz_theme_footer_js_component()
{
    // Only apply theme if enabled for staff
    if (get_option('genz_theme_staff') != '1') {
        return;
    }
    
    echo '<script src="' . module_dir_url('genz_theme', 'assets/js/main.js') . '"></script>';
}

/**
 * Injects customers theme js components in footer
 * @return null
 */
function genz_theme_customers_footer_js_component()
{
    echo '<script src="' . module_dir_url('genz_theme', 'assets/js/clients.js') . '"></script>';
}