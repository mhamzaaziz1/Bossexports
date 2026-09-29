<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Xero® Online
Description: Module for handling Xero Cloud Accounting Integration.
Version: 1.0.2
Requires at least: 2.6.*
Author: Swivernet
 */

require __DIR__ . '/vendor/autoload.php';

define('XERO_MODULE_NAME', 'xero');
define('XERO_DEFAULT_ID', '00000000-0000-0000-0000-000000000000');
define('XERO_PAYMENT_METHOD_PREFIX', 'PM-');
define('XERO_EXPENSE_CATEGORY_PREFIX', 'EC-');
define('XERO_ITEM_CODE_PREFIX', 'PFX-');

$CI = &get_instance();

hooks()->add_action('settings_tab_footer', 'xero_connect_script');
hooks()->add_action('admin_init', 'add_xero_to_settings_tabs');
hooks()->add_action('admin_init', 'xero_permissions');
hooks()->add_filter('numbers_of_features_using_cron_job', 'xero_numbers_of_features_using_cron_job');
hooks()->add_filter('used_cron_features', 'xero_used_cron_features');
hooks()->add_filter('before_settings_updated', 'xero_monitor_settings_update');
hooks()->add_filter('not_importable_clients_fields', 'xero_not_importable_clients_fields');
hooks()->add_filter('module_xero_action_links', 'module_xero_action_links');

if ((bool) get_option('xero_active') && get_option('xero_access_token') && time() < get_option('xero_expiration_date')) {

	hooks()->add_action('admin_init', 'xero_module_init_menu_items');

	hooks()->add_action('before_js_scripts_render', 'xero_variable_script', 9);
	hooks()->add_action('before_js_scripts_render', 'xero_script', 10);

	//Items
	hooks()->add_action('item_created', 'add_item_to_xero'); //add to xero;
	hooks()->add_action('item_updated', 'update_item_in_xero'); //update in xero;
	hooks()->add_action('item_deleted', 'delete_item_in_xero'); //delete in xero;

	//Invoices
	hooks()->add_action('after_invoice_added', 'add_invoice_to_xero'); //add to xero;
	hooks()->add_action('after_invoice_updated', 'update_invoice_in_xero'); //update in xero;
	hooks()->add_action('before_invoice_deleted', 'delete_invoice_in_xero'); //delete in xero;

	//Payments
	hooks()->add_action('after_payment_added', 'add_payment_to_xero'); //add to xero;
	hooks()->add_filter('before_payment_updated', 'update_payment_in_xero', 10, 2); //update in xero;
	hooks()->add_action('before_payment_deleted', 'delete_payment_in_xero'); //delete in xero;

}

//REGISTERS

/**
 * Load the module helper
 */
$CI->load->helper(XERO_MODULE_NAME . '/xero');

/**
 * Load the module library
 */
$CI->load->library(XERO_MODULE_NAME . '/xero_module');

/**
 * Load the module model
 */
$CI->load->model(XERO_MODULE_NAME . '/xero_model');

/**
 * Register activation module hook
 */
register_activation_hook(XERO_MODULE_NAME, 'xero_activation_hook');

function xero_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register deactivation module hook
 */
register_uninstall_hook(XERO_MODULE_NAME, 'xero_uninstall_hook');

function xero_uninstall_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/uninstall.php';
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(XERO_MODULE_NAME, 'xero_deactivation_hook');

function xero_deactivation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/deactivate.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(XERO_MODULE_NAME, [XERO_MODULE_NAME]);

function xero_variable_script() {
	echo "<script> var xero_manual_cron_sync_enabled = '" . get_option('xero_manual_cron_sync_enabled') . "'; </script>";
}

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_xero_action_links($actions) {
	$actions[] = '<a href="' . admin_url('settings?group=xero') . '">' . _l('xero_settings') . '</a>';

	return $actions;
}

function xero_script() {
	$CI = &get_instance();
	return $CI->app_scripts->add('xero-js', module_dir_url('xero', 'assets/js/xero.js') . '?v=' . $CI->app_scripts->core_version(), 'admin', ['app-js']);
}

function xero_connect_script($tab) {
	$CI = &get_instance();
	if ($tab['slug'] == 'xero') {
		echo "<script>
	  $(function(){
    'use strict';

    $('#xero_connect_btn').click(function(e){
        e.preventDefault();

        var iMyWidth;
        var iMyHeight;
            iMyWidth = (window.screen.width / 2) - (400 + 10);
            iMyHeight = (window.screen.height / 2) - (250 + 50);
            var win2 = window.open( '" . site_url('xero/xero_auth') . "', 'Window2', 'status=no,height=500,width=800, resizable=yes,left=' + iMyWidth + ',top=' + iMyHeight + ',screenX=' + iMyWidth + ',screenY=' + iMyHeight + ',toolbar=no,menubar=no,scrollbars=no,location=no,directories=no');
            win2.focus();
        });
        });
      </script>";
	}

}

function add_xero_to_settings_tabs() {
	$CI = &get_instance();
	$CI->app_tabs->add_settings_tab('xero', [
		'name' => _l('xero'),
		'view' => 'xero/settings/xero',
		'position' => 84,
	]);
}

function xero_numbers_of_features_using_cron_job($number) {
	$feature = get_option('xero_active');
	$number += $feature;

	return $number;
}

function xero_used_cron_features($features) {
	$feature = get_option('xero_active');
	if ($feature > 0) {
		array_push($features, 'Auto xero connection');
	}

	return $features;
}

/**
 * Register Xero cron job
 */
register_cron_task('xero_cron_perform');
function xero_cron_perform($manually) {
	$CI = &get_instance();
	$CI->xero_model->half_hourly_scheduled_synch($manually);
	//$CI->xero_model->daily_scheduled_synch($manually);
}

/**
 * Monitor Xero Settings Changes
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function xero_monitor_settings_update($data) {
	$_posted = $data['settings'];
	if (isset($data['settings']['xero_active'])) {
		$_stored = array(
			"xero_active" => get_option("xero_active"),
			"xero_client_id" => get_option("xero_client_id"),
			"xero_client_secret" => get_option("xero_client_secret"),
			"xero_test_mode_enabled" => get_option("xero_test_mode_enabled"),
		);

		$_posted_check = array(
			"xero_active" => $_posted['xero_active'],
			"xero_client_id" => $_posted['xero_client_id'],
			"xero_client_secret" => $_posted['xero_client_secret'],
			"xero_test_mode_enabled" => $_posted['xero_test_mode_enabled'],
		);

		$diff_result = array_diff($_posted_check, $_stored);

		if (count($diff_result) > 0) {
			update_option('xero_access_token', '');
			update_option('xero_refresh_token', '');
			update_option('xero_expiration_date', '');
			update_option('xero_is_expired', '');
			update_option('xero_realmid', '');
			update_option('xero_refresh_token_expires_in', '');
			update_option('xero_tenant_id', '');
		}
	}

	return $data;
}

/**
 * Add xero_id to non_importable_clients_fields
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function xero_not_importable_clients_fields($data) {
	array_push($data, 'xero_id');
	return $data;
}

/**
 * Init xero module menu items in setup in admin_init hook
 * @return null
 */
function xero_module_init_menu_items() {
	$CI = &get_instance();

	$CI->app->add_quick_actions_link([
		'name' => _l('xero_short'),
		'permission' => 'xero',
		'url' => 'xero',
		'position' => 2,
	]);

	if (has_permission('xero', '', 'view')) {

		$CI->app_menu->add_sidebar_menu_item('xero', [
			'name' => _l('xero_short'),
			'href' => admin_url('xero'),
			'icon' => 'fa fa-cloud',
			'position' => 21,
		]);

		$CI->app_menu->add_setup_children_item('finance', [
			'slug' => 'xero',
			'name' => _l('xero'),
			'href' => admin_url('xero'),
			'position' => 2,
		]);
	}
}
/**
 * Xero Permissions
 * @return [type] [description]
 */
function xero_permissions() {
	$capabilities = [];

	$capabilities['capabilities'] = [
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('permission_edit'),
		'delete' => _l('permission_delete'),
	];

	register_staff_capabilities(XERO_MODULE_NAME, $capabilities, _l('xero'));
}

/******************** ITEM **********************/
/**
 * Add Item
 */
function add_item_to_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_item($id);

}

/**
 * Update Item
 * @return [type] [description]
 */
function update_item_in_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_item($id);
}

/**
 * Delete Item
 * @return [type] [description]
 */
function delete_item_in_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_item($id, 'delete');
}

/******************** PAYMENT **********************/
/**
 * Add Payment
 */
function add_payment_to_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_payment($id);
}

/**
 * Update Payment
 * @return [type] [description]
 */
function update_payment_in_xero($data, $id) {
	$CI = &get_instance();
	$CI->xero_model->xero_payment($id, 'add_edit', $data);
	return $data;
}

/**
 * Delete Payment
 * @return [type] [description]
 */
function delete_payment_in_xero($data = array()) {
	$CI = &get_instance();
	$CI->xero_model->xero_payment($data['paymentid'], 'delete');
}

/******************** INVOICE **********************/
/**
 * Add Invoice
 */
function add_invoice_to_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_invoice($id);
}

/**
 * Update Invoice
 * @return [type] [description]
 */
function update_invoice_in_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_invoice($id);
}

/**
 * Delete Invoice
 * @return [type] [description]
 */
function delete_invoice_in_xero($id) {
	$CI = &get_instance();
	$CI->xero_model->xero_invoice($id, 'delete');
	log_message('error', "Invoice deleted xero"); //Incomplete
}