<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: BecomeDigital Expense Approval Module
Description: This Addtional Expense Module will allow admin or respective person to approve expenses. Create permissions of Manage Expenses instead of Expenses for Staff. 
Author: <a href="https://becomedigital.in" target="_blank">Become Digital (An Initiative of DSU INFOTECH&reg;)</a>
Version: 1.0.0
Requires at least: 1.0.*
*/

define('BD_EXPENSES_MODULE_NAME', 'bd_expenses');
define('BD_EXPENSE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/bd_expenses' . '/');

$CI = &get_instance();

hooks()->add_action('admin_init', 'bd_expenses_hook_admin_init');

/**
* Load the module helper
*/
$CI->load->helper(BD_EXPENSES_MODULE_NAME . '/bd_expenses');

/**
* Load the module model
*/
$CI->load->model(BD_EXPENSES_MODULE_NAME . '/bd_expenses_model');

/**
* Register activation module hook
*/
register_activation_hook(BD_EXPENSES_MODULE_NAME, 'bd_expenses_activation_hook');

function bd_expenses_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
	
	if(!is_dir(BD_EXPENSE_ATTACHMENTS_FOLDER))
		_maybe_create_upload_path(BD_EXPENSE_ATTACHMENTS_FOLDER);
	
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(BD_EXPENSES_MODULE_NAME, [BD_EXPENSES_MODULE_NAME]);

/**
*	Admin Init Hook for module
*/
function bd_expenses_hook_admin_init()
{
	/*Add customer permissions */
	$capabilities = [];
	$capabilities['capabilities'] = [
		'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
		'view_own'   => _l('permission_view_own'),
		'create'   => _l('permission_create'),
		'edit'   => _l('permission_edit'),
		'delete'   => _l('permission_delete'),
		'approve'   => _l('bd_expenses_approve'),
	];
	register_staff_capabilities('bd_expenses', $capabilities, _l('bd_expenses'));
	
	$CI = &get_instance();
	/** Add Menu for staff and admin**/
	if (is_admin() || has_permission('bd_expenses', '', 'view') || has_permission('bd_expenses', '', 'view_own')) {
		$CI->app_menu->add_sidebar_menu_item('bd_expenses_menu', [
			'slug'     => 'si-expenses-menu',
			'icon'     => 'fa fa-file-text-o',
			'name'     => _l('bd_expenses_menu'),
			'href'     => admin_url('bd_expenses'),
			'position' => 35,
		]);
	}
}