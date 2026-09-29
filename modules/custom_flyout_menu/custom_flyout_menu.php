<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Custom Flyout Menu
Description: Advanced flyout sidebar menu with group management, drag-drop customization, and SaaS multi-tenant support
Version: 1.0.0
Author: Custom Development
Requires at least: 2.3.*
*/

define('CFM_MODULE_NAME', 'custom_flyout_menu');
define('CFM_REVISION', 100);

// Register hooks
hooks()->add_action('app_admin_head', 'cfm_load_admin_css');
hooks()->add_action('app_admin_footer', 'cfm_load_admin_js');
hooks()->add_action('app_customers_head', 'cfm_load_client_css');
hooks()->add_action('app_customers_footer', 'cfm_load_client_js');
hooks()->add_action('admin_init', 'cfm_register_admin_menu');
hooks()->add_action('admin_init', 'cfm_register_profile_sidebar_menu', 1);
hooks()->add_action('admin_init', 'cfm_permissions');

// Register activation hook
register_activation_hook(CFM_MODULE_NAME, 'cfm_activation_hook');

// Register deactivation hook
register_deactivation_hook(CFM_MODULE_NAME, 'cfm_deactivation_hook');

// Get CI instance
$CI = &get_instance();

// Load helper file
if (file_exists(__DIR__ . '/helpers/custom_flyout_menu_helper.php')) {
    require_once(__DIR__ . '/helpers/custom_flyout_menu_helper.php');
}

// Ensure database is initialized on first load
if (!function_exists('_cfm_ensure_db_initialized')) {
    function _cfm_ensure_db_initialized()
    {
        static $initialized = false;
        
        if ($initialized) {
            return;
        }
        
        $initialized = true;
        
        $CI = &get_instance();
        
        try {
            if ($CI->db && !$CI->db->table_exists(db_prefix() . 'cfm_settings')) {
                // Database tables don't exist - run install
                require_once(__DIR__ . '/install.php');
            }
        } catch (Exception $e) {
            // Silently fail - database might not be ready
        }
    }
    
    _cfm_ensure_db_initialized();
}

/**
 * Register language files
 */
register_language_files(CFM_MODULE_NAME, [CFM_MODULE_NAME]);

/**
 * Module activation hook
 */
function cfm_activation_hook()
{
    $CI = &get_instance();
    
    // Run installation script to ensure database tables exist
    require_once(__DIR__ . '/install.php');
    
    // Verify settings table exists and has default settings
    try {
        if ($CI->db->table_exists(db_prefix() . 'cfm_settings')) {
            // Check if default settings exist
            $existing = $CI->db->select('setting_key')
            ->from(db_prefix() . 'cfm_settings')
            ->where('setting_key', 'flyout_trigger_mode')
            ->get()
            ->num_rows();
            
            if ($existing == 0) {
                // Insert default settings
                $defaults = [
                    ['setting_key' => 'flyout_trigger_mode', 'setting_value' => 'hover', 'updated_at' => date('Y-m-d H:i:s')],
                    ['setting_key' => 'admin_menu_enabled', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
                    ['setting_key' => 'client_menu_enabled', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
                    ['setting_key' => 'exclude_super_admin', 'setting_value' => '1', 'updated_at' => date('Y-m-d H:i:s')],
                ];
                $CI->db->insert_batch(db_prefix() . 'cfm_settings', $defaults);
            }
        }
    } catch (Exception $e) {
        // Silently fail - database might not be ready yet
    }
    
    // Check if reinstalling or first install
    $already_installed = get_option('custom_flyout_menu_installed');
    
    if (!$already_installed) {
        // First-time installation

        // Seed default menu items
        if (class_exists('Custom_flyout_menu_model')) {
            $CI->load->model('custom_flyout_menu/custom_flyout_menu_model');
        }
        
        if (file_exists(__DIR__ . '/includes/default_menu_data.php')) {
            require_once(__DIR__ . '/includes/default_menu_data.php');
            if (function_exists('cfm_seed_default_menus')) {
                cfm_seed_default_menus();
            }
        }
        
        // Mark as installed
        add_option('custom_flyout_menu_installed', date('Y-m-d H:i:s'));
    }
    
    // Set active flag
    update_option('custom_flyout_menu_active', '1');
    
    // Clear any cached menu data
    delete_option('cfm_menu_cache_admin');
    delete_option('cfm_menu_cache_client');
    
    // Log activation
    if (function_exists('log_activity')) {
        log_activity('Custom Flyout Menu activated');
    }
}

/**
 * Module deactivation hook
 */
function cfm_deactivation_hook()
{
    // Set inactive flag
    update_option('custom_flyout_menu_active', '0');
    
    // Clear cached data
    delete_option('cfm_menu_cache_admin');
    delete_option('cfm_menu_cache_client');
    
    // Log deactivation
    log_activity('Custom Flyout Menu deactivated');
}

/**
 * Load admin CSS assets
 */
function cfm_load_admin_css()
{
    // Check if module_dir_url is available
    if (!function_exists('module_dir_url')) {
        return;
    }
    
    // Get admin menu setting - default to enabled (true)
    $admin_menu_enabled = function_exists('cfm_get_setting') 
    ? cfm_get_setting('admin_menu_enabled', '1') 
    : '1';
    
    // Only load if explicitly enabled (value = '1')
    // Default behavior: if not found, enable the menu
    if ($admin_menu_enabled !== '1') {
        return;
    }
    
    echo '<link href="' . module_dir_url(CFM_MODULE_NAME, 'assets/css/admin_menu.css') . '?v=' . CFM_REVISION . '" rel="stylesheet" type="text/css" />';
    echo '<link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet" type="text/css" />';
}

/**
 * Load admin JS assets
 */
function cfm_load_admin_js()
{
    // Check if module_dir_url is available
    if (!function_exists('module_dir_url')) {
        return;
    }
    
    // Get admin menu setting - default to enabled (true)
    $admin_menu_enabled = function_exists('cfm_get_setting') 
    ? cfm_get_setting('admin_menu_enabled', '1') 
    : '1';
    
    // Only load if explicitly enabled (value = '1')
    // Default behavior: if not found, enable the menu
    if ($admin_menu_enabled !== '1') {
        return;
    }
    
    // Get trigger mode setting - default to 'hover' mode
    $trigger_mode = function_exists('cfm_get_setting') 
    ? cfm_get_setting('flyout_trigger_mode', 'hover') 
    : 'hover';
    
    // Ensure valid trigger mode (must be 'hover' or 'click')
    if ($trigger_mode !== 'hover' && $trigger_mode !== 'click') {
        $trigger_mode = 'hover';
    }
    
    echo '<script>';
    echo 'window.cfmSettings = window.cfmSettings || {};';
    echo 'window.cfmSettings.triggerMode = "' . ($trigger_mode) . '";';
    echo 'window.cfmSettings.adminMenuEnabled = true;';
    echo '</script>';
    echo '<script src="' . module_dir_url(CFM_MODULE_NAME, 'assets/js/admin_menu.js') . '?v=' . uniqid() . '"></script>';
}

/**
 * Load client CSS assets
 */
function cfm_load_client_css()
{
    // Check if client is logged in
    if (!function_exists('is_client_logged_in') || !is_client_logged_in()) {
        return;
    }
    
    // Check if module_dir_url is available
    if (!function_exists('module_dir_url')) {
        return;
    }
    
    // Get client menu setting - default to enabled (true)
    $client_menu_enabled = function_exists('cfm_get_setting') 
    ? cfm_get_setting('client_menu_enabled', '1') 
    : '1';
    
    // Only load if explicitly enabled (value = '1')
    // Default behavior: if not found, enable the menu
    if ($client_menu_enabled !== '1') {
        return;
    }
    
    echo '<link href="' . module_dir_url(CFM_MODULE_NAME, 'assets/css/client_menu.css') . '?v=' . uniqid() . '" rel="stylesheet" type="text/css" />';
    echo '<link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet" type="text/css" />';
}

/**
 * Load client JS assets
 */
function cfm_load_client_js()
{
    // Check if client is logged in
    if (!function_exists('is_client_logged_in') || !is_client_logged_in()) {
        return;
    }
    
    // Check if module_dir_url is available
    if (!function_exists('module_dir_url')) {
        return;
    }
    
    // Get client menu setting - default to enabled (true)
    $client_menu_enabled = function_exists('cfm_get_setting') 
    ? cfm_get_setting('client_menu_enabled', '1') 
    : '1';
    
    // Only load if explicitly enabled (value = '1')
    // Default behavior: if not found, enable the menu
    if ($client_menu_enabled !== '1') {
        return;
    }
    
    // Get trigger mode setting - default to 'hover' mode
    $trigger_mode = function_exists('cfm_get_setting') 
    ? cfm_get_setting('flyout_trigger_mode', 'hover') 
    : 'hover';
    
    // Ensure valid trigger mode (must be 'hover' or 'click')
    if ($trigger_mode !== 'hover' && $trigger_mode !== 'click') {
        $trigger_mode = 'hover';
    }
    
    echo '<script>';
    echo 'window.cfmSettings = window.cfmSettings || {};';
    echo 'window.cfmSettings.triggerMode = "' . ($trigger_mode) . '";';
    echo 'window.cfmSettings.clientMenuEnabled = true;';
    echo '</script>';
    echo '<script src="' . module_dir_url(CFM_MODULE_NAME, 'assets/js/client_menu.js') . '?v=' . uniqid() . '"></script>';
}

/**
 * Register permissions
 */
function cfm_permissions()
{
    // Load permission registration
    if (file_exists(__DIR__ . '/includes/permissions.php')) {
        require_once(__DIR__ . '/includes/permissions.php');
    }
}

/**
 * Register admin menu items
 */
function cfm_register_admin_menu()
{
    $CI = &get_instance();
    
    // Add to Setup menu
    $CI->app_menu->add_setup_menu_item('custom-flyout-menu', [
        'collapse' => true,
        'name'     => _l('cfm_menu_setup'),
        'position' => 65,
    ]);
    
    $CI->app_menu->add_setup_children_item('custom-flyout-menu', [
        'slug'     => 'cfm-admin-menu',
        'name'     => _l('cfm_admin_menu_builder'),
        'href'     => admin_url('custom_flyout_menu/admin_menu'),
        'position' => 1,
    ]);
    
    $CI->app_menu->add_setup_children_item('custom-flyout-menu', [
        'slug'     => 'cfm-client-menu',
        'name'     => _l('cfm_client_menu_builder'),
        'href'     => admin_url('custom_flyout_menu/client_menu'),
        'position' => 2,
    ]);
    
    $CI->app_menu->add_setup_children_item('custom-flyout-menu', [
        'slug'     => 'cfm-settings',
        'name'     => _l('settings'),
        'href'     => admin_url('custom_flyout_menu/settings'),
        'position' => 3,
    ]);

    $CI->app_menu->add_setup_menu_item('custom-flyout-menu', [
        'collapse' => true,
        'name'     => _l('cfm_menu_setup'),
        'position' => 65,
    ]);
}

function cfm_register_profile_sidebar_menu()
{
    if (function_exists('cfm_should_apply_menu') && !cfm_should_apply_menu()) {
        return;
    }

    if (!function_exists('is_staff_member') || !is_staff_member()) {
        return;
    }

    if (!function_exists('get_staff_full_name')) {
        return;
    }
    

    $CI = &get_instance();

    /*
    =====================================================
    MAIN STAFF MENU (PARENT)
    =====================================================
    */

    $CI->app_menu->add_sidebar_menu_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-settings',
        'name'     => get_staff_full_name(),
        'href'     => '#',
        'icon'     => 'fa-regular fa-user',
        'position' => 0,
        'collapse' => true,
        'li_attributes' => [
            'data-hide-title-if-grouped' => '1',
        ],
    ]);

    /*
    =====================================================
    GROUP 1 — STAFF NAME GROUP LABEL
    =====================================================
    */

    // Staff items
    $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-profile',
        'name'     => _l('nav_my_profile'),
        'href'     => admin_url('profile'),
        'position' => 2,
        'icon'     => '',
        'li_attributes' => [
            'data-group-id'          => 'staff-main',
            'data-group-name'        => get_staff_full_name(),
            'data-is-nested-grouped' => '1',
        ],
    ]);

    $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-timesheets',
        'name'     => _l('my_timesheets'),
        'href'     => admin_url('staff/timesheets'),
        'position' => 3,
        'icon'     => '',
        'li_attributes' => [
            'data-group-id'          => 'staff-main',
            'data-group-name'        => get_staff_full_name(),
            'data-is-nested-grouped' => '1',
        ],
    ]);

    $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-edit-profile',
        'name'     => _l('nav_edit_profile'),
        'href'     => admin_url('staff/edit_profile'),
        'position' => 4,
        'icon'     => '',
        'li_attributes' => [
            'data-group-id'          => 'staff-main',
            'data-group-name'        => get_staff_full_name(),
            'data-is-nested-grouped' => '1',
        ],
    ]);


    $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-logout',
        'name'     => _l('nav_logout'),
        'href'     => admin_url('authentication/logout'),
        'position' => 5,
        'icon'     => '',
        'li_attributes' => [
            'data-group-id'          => 'staff-main',
            'data-group-name'        => get_staff_full_name(),
            'data-is-nested-grouped' => '1',
        ],
    ]);

    /*
    =====================================================
    GROUP 2 — LANGUAGE GROUP LABEL
    =====================================================
    */
    if (is_language_disabled()) {
        return;
    }

    // System Default
    $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
        'slug'     => 'cfm-item-staff-language-default',
        'name'     => _l('system_default_string'),
        'href'     => admin_url('staff/change_language'),
        'position' => 7,
        'icon'     => '',
        'li_attributes' => [
            'data-group-id'          => 'staff-languages',
            'data-group-name'        => _l('language'),
            'data-is-nested-grouped' => '1',
        ],
    ]);

    // Dynamic Languages
    $languages = $CI->app->get_available_languages();
    $i = 8;

    foreach ($languages as $lang) {
        $CI->app_menu->add_sidebar_children_item('cfm-item-staff-settings', [
            'slug'     => 'cfm-item-staff-language-' . $lang,
            'name'     => ucfirst($lang),
            'href'     => admin_url('staff/change_language/' . $lang),
            'position' => $i++,
            'icon'     => '',
            'li_attributes' => [
                'data-group-id'          => 'staff-languages',
                'data-group-name'        => _l('language'),
                'data-is-nested-grouped' => '1',
            ],
        ]);
    }
}


// Load menu renderer
if (file_exists(__DIR__ . '/includes/menu_renderer.php')) {
    require_once(__DIR__ . '/includes/menu_renderer.php');
}

if (function_exists('cfm_should_apply_menu') && cfm_should_apply_menu()) {
    hooks()->add_action('customers_navigation_start', 'cfm_remove_default_customer_navigation');

    function cfm_remove_default_customer_navigation()
    {
        ob_start(function () {
            return '';
        });
    }

    hooks()->add_action('customers_navigation_end', 'cfm_stop_buffer_customer_navigation');

    function cfm_stop_buffer_customer_navigation()
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    if(is_client_logged_in())
        hooks()->add_action('customers_content_container_start', 'cfm_customers_content_container_start');

    /**
     * Map default menu slugs to their appropriate Phosphor icons
     */
    function cfm_get_default_menu_icon($slug)
    {
        $icon_map = [
            'knowledge-base' => 'ph-book',
            'register' => 'ph-user-plus',
            'login' => 'ph-user',
            'projects' => 'ph-folder-open',
            'invoices' => 'ph-receipt',
            'contracts' => 'ph-file-text',
            'estimates' => 'ph-currency-dollar',
            'proposals' => 'ph-handshake',
            'subscriptions' => 'ph-calendar',
            'support' => 'ph-headset',
        ];
        
        return isset($icon_map[$slug]) ? $icon_map[$slug] : 'ph-circle';
    }

    function cfm_customers_content_container_start()
    {
        $CI = &get_instance();
        $sidebar_menu = $CI->load->get_var('menu');

        if (empty($sidebar_menu)) {
            return;
        }

        // Add icons to default menus if they don't have them
        foreach ($sidebar_menu as $slug => &$item) {
            // Check if this is a default menu item (no 'collapse' property)
            if (!isset($item['collapse']) || !$item['collapse']) {
                // Check if the item doesn't have an icon
                if (empty($item['icon'])) {
                    // Try to add the default icon based on slug
                    $default_icon = cfm_get_default_menu_icon($slug);
                    $sidebar_menu[$slug]['icon'] = 'ph '.$default_icon;
                }
            }
        }
        unset($item);

        $cfm_aside_path = __DIR__ . '/views/client/aside.php';
        
        if (file_exists($cfm_aside_path)) {
            require_once($cfm_aside_path);
        }
    }
}
