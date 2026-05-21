<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: NedarimPay
Description: Full integration between Perfex CRM and Nedarim Plus payment gateway.
             Handles webhook callbacks, automatic receipt generation (dual series:
             Students/Tuition vs Donations), outbound occasional charges, and
             email delivery — all from within Perfex CRM.
Version: 1.0.0
Requires at least: 2.3.*
Author: Senior Module — NedarimPay
*/

define('NEDARIMPAY_MODULE_NAME', 'nedarimpay');
define('NEDARIMPAY_MODULE_PATH', __DIR__);

// Load gateway class so register_payment_gateway() receives an object instance
require_once NEDARIMPAY_MODULE_PATH . '/libraries/Nedarimpay_gateway.php';

// ─── Receipt Type Constants ──────────────────────────────────────────────────
define('NEDARIMPAY_TYPE_STUDENT',  'student');   // Monthly tuition + occasional student fees
define('NEDARIMPAY_TYPE_DONATION', 'donation');  // Donor receipts — separate number series

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook(NEDARIMPAY_MODULE_NAME, 'nedarimpay_activation_hook');
register_deactivation_hook(NEDARIMPAY_MODULE_NAME, 'nedarimpay_deactivation_hook');

function nedarimpay_activation_hook()
{
    require_once NEDARIMPAY_MODULE_PATH . '/install.php';
}

function nedarimpay_deactivation_hook()
{
    // Preserve data on deactivation — no table drops
}

// ─── Schema migrations (run once, check column existence first) ───────────────
$_ndp_CI = &get_instance();
if ($_ndp_CI->db->table_exists(db_prefix() . 'nedarimpay_manual_charges')) {
    $_ndp_cols = array_column(
        $_ndp_CI->db->field_data(db_prefix() . 'nedarimpay_manual_charges'),
        'name'
    );
    if (!in_array('receipt_number', $_ndp_cols)) {
        $_ndp_CI->db->query(
            'ALTER TABLE `' . db_prefix() . 'nedarimpay_manual_charges`
             ADD COLUMN `receipt_number` VARCHAR(50) DEFAULT NULL AFTER `client_id_nedarim`'
        );
    }
}
unset($_ndp_CI, $_ndp_cols);

// ─── Language Files ──────────────────────────────────────────────────────────
register_language_files(NEDARIMPAY_MODULE_NAME, [NEDARIMPAY_MODULE_NAME]);

// ─── Add module views path so gateway controller can load views ──────────────
hooks()->add_filter('app_view_paths', 'nedarimpay_add_view_path');
function nedarimpay_add_view_path($paths)
{
    $paths[] = NEDARIMPAY_MODULE_PATH . '/views/';
    return $paths;
}

// ─── Register Payment Gateway ────────────────────────────────────────────────
// Must happen on every request so Perfex always knows about this gateway.
register_payment_gateway(new Nedarimpay_gateway(), NEDARIMPAY_MODULE_NAME);

// ─── Hooks ───────────────────────────────────────────────────────────────────
hooks()->add_action('admin_init',             'nedarimpay_init_menu_items');
hooks()->add_filter('module_nedarimpay_action_links', 'nedarimpay_action_links');

// ─── Menu ────────────────────────────────────────────────────────────────────
function nedarimpay_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('nedarimpay', '', 'view')) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('nedarimpay', [
        'name'     => _l('nedarimpay_menu_title'),
        'icon'     => 'fa fa-credit-card',
        'position' => 25,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-dashboard',
        'name'     => _l('nedarimpay_dashboard'),
        'href'     => admin_url('nedarimpay/dashboard'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-transactions',
        'name'     => _l('nedarimpay_transactions'),
        'href'     => admin_url('nedarimpay/transactions'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-manual-charge',
        'name'     => _l('nedarimpay_manual_charge'),
        'href'     => admin_url('nedarimpay/manual_charge'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-settings',
        'name'     => _l('settings'),
        'href'     => admin_url('nedarimpay/settings'),
        'position' => 4,
    ]);
}

// ─── Module Action Links in Modules List ─────────────────────────────────────
function nedarimpay_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('nedarimpay/settings') . '">' . _l('settings') . '</a>';
    return $actions;
}
