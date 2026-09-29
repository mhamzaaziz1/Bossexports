<?php
/**
 * Partner / Reseller Management Module
 * Enterprise-level B2B Partner Portal with Commission Management
 * 
 * @package     Partner_Reseller_Management
 * @author      Your Company
 * @copyright   2025
 * @license     Commercial
 * @version     1.0.0
 */

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Partner & Reseller Management
Description: Complete B2B partner portal with deal registration, commission tracking, and advanced analytics
Version: 1.0.0
Requires at least: 2.3.0
Author: Your Company
Author URI: https://yourcompany.com
*/

define('PARTNER_MODULE_NAME', 'partner_reseller');
define('PARTNER_MODULE_VERSION', '1.0.0');
define('PARTNER_MODULE_PATH', dirname(__FILE__) . '/');

/**
 * Register activation hook
 */
register_activation_hook(PARTNER_MODULE_NAME, 'partner_reseller_activation_hook');

function partner_reseller_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install/install.php');
    $install = new Partner_Reseller_Install();
    $install->run();
}

/**
 * Register deactivation hook
 */
register_deactivation_hook(PARTNER_MODULE_NAME, 'partner_reseller_deactivation_hook');

function partner_reseller_deactivation_hook()
{
    // Cleanup if needed
}

/**
 * Register language files
 */
register_language_files(PARTNER_MODULE_NAME, [PARTNER_MODULE_NAME]);

/**
 * Module Menu Items
 */
hooks()->add_action('admin_init', 'partner_reseller_module_init_menu_items');

function partner_reseller_module_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission('partner_reseller', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('partner-reseller', [
            'collapse' => true,
            'name'     => _l('partner_reseller_module'),
            'icon'     => 'fa fa-handshake-o',
            'position' => 15,
        ]);
        
        // Dashboard
        $CI->app_menu->add_sidebar_children_item('partner-reseller', [
            'slug'     => 'partner-reseller-dashboard',
            'name'     => _l('dashboard'),
            'icon'     => 'fa fa-tachometer',
            'href'     => admin_url('partner_reseller/dashboard'),
            'position' => 1,
        ]);
        
        // Partners
        $CI->app_menu->add_sidebar_children_item('partner-reseller', [
            'slug'     => 'partner-reseller-partners',
            'name'     => _l('partners'),
            'icon'     => 'fa fa-users',
            'href'     => admin_url('partner_reseller/partners'),
            'position' => 2,
        ]);
        
        // Deal Registration
        $CI->app_menu->add_sidebar_children_item('partner-reseller', [
            'slug'     => 'partner-reseller-deals',
            'name'     => _l('deal_registration'),
            'icon'     => 'fa fa-briefcase',
            'href'     => admin_url('partner_reseller/deals'),
            'position' => 3,
        ]);
        
        // Commissions
        $CI->app_menu->add_sidebar_children_item('partner-reseller', [
            'slug'     => 'partner-reseller-commissions',
            'name'     => _l('commissions'),
            'icon'     => 'fa fa-money',
            'href'     => admin_url('partner_reseller/commissions'),
            'position' => 4,
        ]);
        
        // Reports & Analytics
        $CI->app_menu->add_sidebar_children_item('partner-reseller', [
            'slug'     => 'partner-reseller-reports',
            'name'     => _l('reports_analytics'),
            'icon'     => 'fa fa-bar-chart',
            'href'     => admin_url('partner_reseller/reports'),
            'position' => 5,
        ]);
        
        // Settings
        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('partner-reseller', [
                'slug'     => 'partner-reseller-settings',
                'name'     => _l('settings'),
                'icon'     => 'fa fa-cog',
                'href'     => admin_url('partner_reseller/settings'),
                'position' => 10,
            ]);
        }
    }
}

/**
 * Register module permissions
 */
hooks()->add_filter('staff_permissions', 'partner_reseller_module_permissions');

function partner_reseller_module_permissions($permissions)
{
    $permissions['partner_reseller'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    
    return $permissions;
}

/**
 * Register custom fields
 */
hooks()->add_action('app_admin_head', 'partner_reseller_custom_fields');

function partner_reseller_custom_fields()
{
    echo '<link href="' . module_dir_url(PARTNER_MODULE_NAME, 'assets/css/partner_reseller.css') . '" rel="stylesheet">';
    echo '<script src="' . module_dir_url(PARTNER_MODULE_NAME, 'assets/js/partner_reseller.js') . '"></script>';
}

/**
 * Partner Portal Routes
 */
hooks()->add_action('before_client_render', 'partner_portal_routes');

function partner_portal_routes()
{
    $CI = &get_instance();
    if (is_partner_logged_in()) {
        echo '<link href="' . module_dir_url(PARTNER_MODULE_NAME, 'assets/css/partner_portal.css') . '" rel="stylesheet">';
        echo '<script src="' . module_dir_url(PARTNER_MODULE_NAME, 'assets/js/partner_portal.js') . '"></script>';
    }
}

/**
 * Check if partner is logged in
 */
function is_partner_logged_in()
{
    $CI = &get_instance();
    return isset($CI->session->userdata['partner_logged_in']) && $CI->session->userdata['partner_logged_in'] === true;
}

/**
 * Get current partner ID
 */
function get_partner_id()
{
    $CI = &get_instance();
    return $CI->session->userdata['partner_id'] ?? 0;
}

/**
 * Format currency for partners
 */
function partner_format_money($amount, $currency = '')
{
    if (empty($currency)) {
        $currency = get_base_currency()->symbol;
    }
    return app_format_money($amount, $currency);
}

/**
 * Notification Helper
 */
function partner_send_notification($partner_id, $title, $message, $link = '')
{
    $CI = &get_instance();
    $CI->load->model('partner_reseller/partner_notification_model');
    
    return $CI->partner_notification_model->add([
        'partner_id' => $partner_id,
        'title'      => $title,
        'message'    => $message,
        'link'       => $link,
        'date_sent'  => date('Y-m-d H:i:s'),
        'is_read'    => 0
    ]);
}

/**
 * Email Helper
 */
function partner_send_email($to, $subject, $message, $cc = '')
{
    $CI = &get_instance();
    $CI->load->library('email');
    
    $CI->email->clear();
    $CI->email->from(get_option('smtp_email'), get_option('companyname'));
    $CI->email->to($to);
    if (!empty($cc)) {
        $CI->email->cc($cc);
    }
    $CI->email->subject($subject);
    $CI->email->message($message);
    
    return $CI->email->send();
}

/**
 * WhatsApp Notification (Integration placeholder)
 */
function partner_send_whatsapp($phone, $message)
{
    // Integration with WhatsApp API
    // This is a placeholder - implement your WhatsApp service
    
    $CI = &get_instance();
    $api_key = get_option('partner_whatsapp_api_key');
    $api_url = get_option('partner_whatsapp_api_url');
    
    if (empty($api_key) || empty($api_url)) {
        return false;
    }
    
    $data = [
        'phone'   => $phone,
        'message' => $message,
        'api_key' => $api_key
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

/**
 * Duplicate Lead Detection
 */
function check_duplicate_lead($company, $email, $phone)
{
    $CI = &get_instance();
    $CI->load->model('partner_reseller/partner_deal_model');
    
    return $CI->partner_deal_model->check_duplicate($company, $email, $phone);
}

/**
 * Commission Calculation
 */
function calculate_commission($deal_value, $commission_type, $commission_value, $tier_config = [])
{
    if ($commission_type == 'fixed') {
        return $commission_value;
    } elseif ($commission_type == 'percentage') {
        return ($deal_value * $commission_value) / 100;
    } elseif ($commission_type == 'tiered' && !empty($tier_config)) {
        foreach ($tier_config as $tier) {
            if ($deal_value >= $tier['min'] && $deal_value <= $tier['max']) {
                return ($deal_value * $tier['rate']) / 100;
            }
        }
    }
    
    return 0;
}
