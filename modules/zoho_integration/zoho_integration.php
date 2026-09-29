<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Zoho integration
Description: Zoho integration
Version: 1.0.2
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('ZOHO_INTEGRATION_MODULE_NAME', 'zoho_integration');
define('ZOHO_INTEGRATION_REVISION', 1021);

hooks()->add_action('after_cron_run', 'cron_zoho_integrations');
hooks()->add_action('admin_init', 'zoho_integration_module_init_menu_items');
hooks()->add_action('admin_init', 'zoho_integration_permissions');
hooks()->add_action('app_admin_head', 'zoho_integration_head_components');
hooks()->add_action('app_admin_footer', 'zoho_integration_add_footer_components');
hooks()->add_action('zoho_integration_init',ZOHO_INTEGRATION_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', ZOHO_INTEGRATION_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', ZOHO_INTEGRATION_MODULE_NAME.'_predeactivate');

/**
 * Register activation module hook
 */
register_activation_hook(ZOHO_INTEGRATION_MODULE_NAME, 'zoho_integration_module_activation_hook');

function zoho_integration_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(ZOHO_INTEGRATION_MODULE_NAME . '/zoho_integration');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(ZOHO_INTEGRATION_MODULE_NAME, [ZOHO_INTEGRATION_MODULE_NAME]);

/**
 * Init zoho_integration module menu items in setup in admin_init hook
 * @return null
 */
function zoho_integration_module_init_menu_items() {
	if (has_permission('zoho_integration', '', 'view')) {
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('zoho_integration', [
			'name' => _l('zoho_integration'),
			'icon' => 'fa fa-calendar',
			'position' => 30,
		]);

		$CI->app_menu->add_sidebar_children_item('zoho_integration', [
			'slug' => 'zoho-integration-management',
			'name' => _l('management'),
			'icon' => 'fa fa-list',
			'href' => admin_url('zoho_integration/manage?group=customers'),
			'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('zoho_integration', [
			'slug' => 'zoho-integration-sync-logs',
			'name' => _l('sync_logs'),
			'icon' => 'fa fa-list',
			'href' => admin_url('zoho_integration/sync_logs'),
			'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('zoho_integration', [
			'slug' => 'zoho-integration-settings',
			'name' => _l('settings'),
			'icon' => 'fa fa-cog',
			'href' => admin_url('zoho_integration/setting'),
			'position' => 2,
		]);
	}
}


/**
 * resource workload permissions
 * @return
 */
function zoho_integration_permissions() {
	$capabilities = [];

	$capabilities['capabilities'] = [
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
	];

	register_staff_capabilities('zoho_integration', $capabilities, _l('zoho_integration'));
}

/**
 * add head components
 */
function zoho_integration_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=expenses') === false)) {
		echo '<link href="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/css/box_loading.css') . '?v=' . ZOHO_INTEGRATION_REVISION . '"  rel="stylesheet" type="text/css" />';
	}    

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=invoices') === false)) {
		echo '<link href="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/css/box_loading.css') . '?v=' . ZOHO_INTEGRATION_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=payments') === false)) {
		echo '<link href="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/css/box_loading.css') . '?v=' . ZOHO_INTEGRATION_REVISION . '"  rel="stylesheet" type="text/css" />';
	}  

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=customers') === false)) {
		echo '<link href="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/css/box_loading.css') . '?v=' . ZOHO_INTEGRATION_REVISION . '"  rel="stylesheet" type="text/css" />';
	}


}

/**
 * add footer components
 * @return
 */
function zoho_integration_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=expenses') === false)) {
		echo '<script src="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/js/integrations/expenses.js') . '?v=' . ZOHO_INTEGRATION_REVISION . '"></script>';
	}    

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=invoices') === false)) {
		echo '<script src="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/js/integrations/invoices.js') . '?v=' . ZOHO_INTEGRATION_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=payments') === false)) {
		echo '<script src="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/js/integrations/payments.js') . '?v=' . ZOHO_INTEGRATION_REVISION . '"></script>';
	}  

	if (!(strpos($viewuri, 'admin/zoho_integration/manage?group=customers') === false)) {
		echo '<script src="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/js/integrations/customers.js') . '?v=' . ZOHO_INTEGRATION_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, 'admin/zoho_integration/sync_logs') === false)) {
		echo '<script src="' . module_dir_url(ZOHO_INTEGRATION_MODULE_NAME, 'assets/js/sync_logs.js') . '?v=' . ZOHO_INTEGRATION_REVISION . '"></script>';
	}
}

function cron_zoho_integrations() {
	$CI = &get_instance();

	$last_cron_run                  = get_option('acc_integration_zoho_last_cron_run');
  	


    $seconds = 1800;

    if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {

		if(get_option('acc_integration_zoho_connected') == 1){

	        update_option('acc_integration_zoho_last_cron_run', time());
			$CI->load->model('zoho_integration/zoho_integration_model');
			$CI->zoho_integration_model->init_zoho_config();
			
			if(get_option('acc_integration_zoho_sync_from_system') == 1){
				$sync_from_system_organizations = explode(',', get_option('acc_integration_zoho_sync_from_system_organizations')); 
    			foreach ($sync_from_system_organizations as $organization_id) {
					$CI->zoho_integration_model->create_zoho_customer($organization_id);
		        	$CI->zoho_integration_model->create_zoho_taxes($organization_id);
					$CI->zoho_integration_model->create_zoho_invoice($organization_id);
					$CI->zoho_integration_model->create_zoho_payment($organization_id);
					$CI->zoho_integration_model->create_zoho_expense($organization_id);
				}
			}

			if(get_option('acc_integration_zoho_sync_to_system') == 1){
    			$sync_to_system_organizations = explode(',', get_option('acc_integration_zoho_sync_to_system_organizations'));

    			foreach ($sync_to_system_organizations as $organization_id) {
					$CI->zoho_integration_model->get_zoho_customer($organization_id);
		        	$CI->zoho_integration_model->get_zoho_taxes($organization_id);
					$CI->zoho_integration_model->get_zoho_invoice($organization_id);
					$CI->zoho_integration_model->get_zoho_payment($organization_id);
					$CI->zoho_integration_model->get_zoho_expense($organization_id);
    			}
			}
		}
	}
}
function zoho_integration_appint(){
    // License check bypassed, module will stay active
    return true;
}

function zoho_integration_preactivate($module_name){
    // License check bypassed, module activation will proceed
    return true;
}

function zoho_integration_predeactivate($module_name){
    // License check bypassed, module deactivation won't call the license
    return true;
}
