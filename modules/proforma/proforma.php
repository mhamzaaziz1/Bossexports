<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Proforma Invoice
Description: Proforma Invoice Module for Perfex CRM
Version: 1.0.0
Requires at least: 2.3.*
Author: Hamza Aziz
Author URI: https://help.perfexcrm.com
*/

define('PROFORMA_MODULE_NAME', 'proforma');

$CI = &get_instance();
$CI->load->helper(PROFORMA_MODULE_NAME . '/proforma');

hooks()->add_filter('items_table_class', 'proforma_items_table_class', 10, 4);

function proforma_items_table_class($class, $transaction, $type, $for)
{
    if ($type == 'proforma') {
        if ($class instanceof App_items_table_template) {
            return $class;
        }

        $CI = &get_instance();
        $CI->load->library('proforma/proforma_items_table', [
            'transaction' => $transaction,
            'type'        => $type,
            'for'         => $for,
        ]);

        return $CI->proforma_items_table;
    }

    return $class;
}



hooks()->add_action('admin_init', 'proforma_module_init_menu_items');
hooks()->add_action('admin_init', 'proforma_permissions');
hooks()->add_action('admin_init', 'proforma_settings_tab_init');
hooks()->add_action('app_admin_head', 'proforma_head_component');

/**
* Register activation module hook
*/
register_activation_hook(PROFORMA_MODULE_NAME, 'proforma_module_activation_hook');

function proforma_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(PROFORMA_MODULE_NAME, [PROFORMA_MODULE_NAME]);

/**
 * Init proforma module menu items in setup in admin_init hook
 * @return null
 */
function proforma_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('sales', [
        'collapse' => true,
        'name'     => _l('sales'),
        'position' => 10,
        'icon'     => 'fa-solid fa-receipt',
    ]);

    if (has_permission('proforma', '', 'view') || has_permission('proforma', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'proforma',
            'name'     => _l('proforma_invoices'), // We will add this to language file
            'href'     => admin_url('proforma'),
            'position' => 15, // After Estimates (usually 10 or 15)
        ]);
    }
}

function proforma_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'     => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    register_staff_capabilities('proforma', $capabilities, _l('proforma_invoice'));
}

function proforma_head_component()
{
    // Add any necessary CSS/JS here if needed
}

function proforma_settings_tab_init()
{
    $CI = &get_instance();
    $CI->app->add_settings_section('proforma', [
        'name'     => _l('proforma_invoice'),
        'view'     => 'proforma/settings',
        'position' => 60,
    ]);
}

hooks()->add_action('app_init', 'proforma_load_merge_fields');

function proforma_load_merge_fields(){
    $CI = &get_instance();
    $CI->app_merge_fields->register('proforma/merge_fields/proforma_merge_fields'); // Register path, not object
}

hooks()->add_action('admin_init', 'proforma_check_email_template');

function proforma_check_email_template() {
    $CI = &get_instance();
    if (!$CI->db->where('slug', 'proforma_invoice_send_to_client')->get(db_prefix() . 'emailtemplates')->row()) {
        $CI->db->insert(db_prefix() . 'emailtemplates', [
            'type'      => 'proforma',
            'slug'      => 'proforma_invoice_send_to_client',
            'language'  => 'english',
            'name'      => 'Proforma Invoice Send to Client',
            'subject'   => 'Proforma Invoice - {proforma_number}',
            'message'   => 'Dear {contact_firstname} {contact_lastname},<br /><br />Please find attached the Proforma Invoice {proforma_number}.<br /><br />Best Regards,<br />{email_signature}',
            'fromname'  => '{companyname} | CRM',
            'active'    => 1,
        ]);
    }
}
