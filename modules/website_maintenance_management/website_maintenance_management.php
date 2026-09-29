<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Website Maintenance Management
Description: Manage website maintenance tasks and track maintenance activities for client projects
Version: 1.0.3
Requires at least: 3.4.*
Author: Zegaware
Author URI: https://zegaware.com
*/
const WEBSITE_MAINTENANCE_MODULE_NAME = 'website_maintenance_management';

$CI = &get_instance();

$CI->load->helper(WEBSITE_MAINTENANCE_MODULE_NAME.'/website_maintenance_management');
$CI->load->helper(WEBSITE_MAINTENANCE_MODULE_NAME.'/migration_log');

/**
 * Register activation hook
 */

function website_maintenance_management_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__.'/install.php');
}

register_activation_hook(WEBSITE_MAINTENANCE_MODULE_NAME, 'website_maintenance_management_activation_hook');

function app_init_wmm_required_services()
{
	require_once APP_MODULES_PATH.WEBSITE_MAINTENANCE_MODULE_NAME.'/libraries/Zegaware_license.php';
	require_once APP_MODULES_PATH.WEBSITE_MAINTENANCE_MODULE_NAME.'/libraries/Zegaware_license_validate.php';
}

hooks()->add_action('app_init', 'app_init_wmm_required_services');

/**
 * Register language files
 */
register_language_files(WEBSITE_MAINTENANCE_MODULE_NAME, [WEBSITE_MAINTENANCE_MODULE_NAME]);

function wmm_add_license_link_to_module_list(array $action_links)
{
	$action_links[] = '<a href="'.admin_url(WEBSITE_MAINTENANCE_MODULE_NAME.'/license').'">'._l('wmm_zegaware_license').'</a>';

	return $action_links;
}

hooks()->add_filter('module_website_maintenance_management_action_links', 'wmm_add_license_link_to_module_list');

/**
 * Init module menu items and other admin hooks
 */

/**
 * Initialize menu items
 */
function website_maintenance_management_init_menu_items()
{
	$CI = &get_instance();

	$validated = Zegaware_license::is_activated(WEBSITE_MAINTENANCE_MODULE_NAME);
	if ( ! $validated)
	{
		return;
	}

	// Add quick action link
	if (staff_can('view', 'website_maintenance_logs'))
	{
		$CI->app->add_quick_actions_link([
			'name'       => _l('wmm_log_maintenance'),
			'url'        => 'website_maintenance_management/maintenance_logs',
			'permission' => 'website_maintenance_logs',
			'position'   => 57,
			'icon'       => 'fa-solid fa-wrench',
		]);
	}

	// Check if user has any permission to show menu
	$has_any_permission = staff_can('view', 'website_maintenance_tasks')
	                      || staff_can('view', 'website_maintenance_categories')
	                      || staff_can('view', 'website_maintenance_websites')
	                      || staff_can('view', 'website_maintenance_logs')
	                      || staff_can('view', 'website_maintenance_reports')
	                      || staff_can('view', 'website_maintenance_packages');

	if ($has_any_permission)
	{
		// Add main menu item
		$CI->app_menu->add_sidebar_menu_item('website_maintenance_management', [
			'name'     => _l('wmm_website_maintenance'),
			'icon'     => 'fa-solid fa-wrench',
			'position' => 25,
		]);

		// Add submenu items based on permissions
		if (staff_can('view', 'website_maintenance_reports'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-dashboard',
				'name'     => _l('wmm_dashboard'),
				'href'     => admin_url('website_maintenance_management/dashboard'),
				'position' => 1,
			]);
		}

		if (staff_can('view', 'website_maintenance_tasks'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-tasks',
				'name'     => _l('wmm_maintenance_tasks'),
				'href'     => admin_url('website_maintenance_management/maintenance_tasks'),
				'position' => 2,
			]);
		}

		if (staff_can('view', 'website_maintenance_categories'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-categories',
				'name'     => _l('wmm_categories'),
				'href'     => admin_url('website_maintenance_management/categories'),
				'position' => 3,
			]);
		}

		if (staff_can('view', 'website_maintenance_websites'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-websites',
				'name'     => _l('wmm_websites'),
				'href'     => admin_url('website_maintenance_management/websites'),
				'position' => 4,
			]);
		}

		if (staff_can('view', 'website_maintenance_logs'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-logs',
				'name'     => _l('wmm_maintenance_logs'),
				'href'     => admin_url('website_maintenance_management/maintenance_logs'),
				'position' => 5,
			]);
		}

		if (staff_can('view', 'website_maintenance_logs') || staff_can('view', 'website_maintenance_tasks'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-calendar',
				'name'     => _l('wmm_calendar'),
				'href'     => admin_url('website_maintenance_management/calendar'),
				'position' => 6,
			]);
		}

		if (staff_can('view', 'website_maintenance_packages'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-packages',
				'name'     => _l('wmm_support_packages'),
				'href'     => admin_url('website_maintenance_management/support_packages'),
				'position' => 7,
			]);
		}

		if (staff_can('view', 'website_maintenance_packages'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-package-usage',
				'name'     => _l('wmm_package_usage_history'),
				'href'     => admin_url('website_maintenance_management/package_usage'),
				'position' => 8,
			]);
		}

		if (staff_can('view', 'website_maintenance_reports'))
		{
			$CI->app_menu->add_sidebar_children_item('website_maintenance_management', [
				'slug'     => 'wmm-reports',
				'name'     => _l('wmm_reports_analytics'),
				'href'     => admin_url('website_maintenance_management/reports'),
				'position' => 9,
			]);
		}
	}
}

hooks()->add_action('admin_init', 'website_maintenance_management_init_menu_items');

/**
 * Register permissions
 */
function website_maintenance_management_permissions()
{
	$capabilities = [];

	$capabilities['capabilities'] = [
		'view'   => _l('permission_view').'('._l('permission_global').')',
		'create' => _l('permission_create'),
		'edit'   => _l('permission_edit'),
		'delete' => _l('permission_delete'),
	];

	// Tasks permissions
	register_staff_capabilities('website_maintenance_tasks', $capabilities, _l('wmm_maintenance_tasks'));

	// Categories permissions
	register_staff_capabilities('website_maintenance_categories', $capabilities, _l('wmm_maintenance_categories'));

	// Websites permissions
	register_staff_capabilities('website_maintenance_websites', $capabilities, _l('wmm_maintenance_websites'));

	// Logs permissions
	register_staff_capabilities('website_maintenance_logs', $capabilities, _l('wmm_maintenance_logs'));

	// Packages permissions
	register_staff_capabilities('website_maintenance_packages', $capabilities, _l('wmm_support_packages'));

	// Reports permissions (view only)
	$reports_capabilities                 = [];
	$reports_capabilities['capabilities'] = [
		'view' => _l('permission_view').'('._l('permission_global').')',
	];
	register_staff_capabilities('website_maintenance_reports', $reports_capabilities, _l('wmm_reports_analytics'));
}

hooks()->add_action('admin_init', 'website_maintenance_management_permissions');

/**
 * Handle project deletion
 */
function website_maintenance_management_project_deleted($data)
{
	$CI = &get_instance();
	$CI->db->where('project_id', $data['id']);
	$CI->db->delete(db_prefix().'wmm_websites');
}

hooks()->add_action('project_deleted', 'website_maintenance_management_project_deleted');

/**
 * Handle client deletion
 */
function website_maintenance_management_client_deleted($data)
{
	$CI = &get_instance();
	$CI->db->where('client_id', $data['id']);
	$CI->db->delete(db_prefix().'wmm_websites');
}

hooks()->add_action('client_deleted', 'website_maintenance_management_client_deleted');

/**
 * Add CSS in admin head
 */
function zegaware_cmm_admin_head()
{
	$CI = &get_instance();

	if (strpos($_SERVER['REQUEST_URI'], 'website_maintenance_management') !== FALSE)
	{
		echo '<link href="'.module_dir_url(WEBSITE_MAINTENANCE_MODULE_NAME, 'assets/css/website-maintenance.css').'" rel="stylesheet">';
	}
}

hooks()->add_action('app_admin_head', 'zegaware_cmm_admin_head', 20);

function wmm_add_maintenance_logs_to_get_upload_path_by_type($path, $type)
{
	if ($type === 'maintenance_logs')
	{
		return FCPATH.'uploads/maintenance_logs/';
	}

	return $path;
}

hooks()->add_filter('get_upload_path_by_type', 'wmm_add_maintenance_logs_to_get_upload_path_by_type', 10, 2);

/**
 * Add email templates to email templates list
 */
function wmm_add_email_templates()
{
	$CI = &get_instance();
	$CI->load->model('emails_model');

	$website_maintenance = $CI->emails_model->get([
		'type'     => 'website_maintenance',
		'language' => 'english',
	]);

	$path = module_dir_path(WEBSITE_MAINTENANCE_MODULE_NAME).'views/emails/email_templates_list.php';

	ob_start();
	require_once $path;

	echo ob_get_clean();
}

hooks()->add_action('before_staff_email_templates', 'wmm_add_email_templates');

/**
 * Register merge fields for email templates
 */
function wmm_register_merge_fields()
{
	$CI = &get_instance();

	// Merge fields for maintenance templates
	$merge_fields = [
		[
			'name'      => 'Client Name',
			'key'       => '{client_name}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started', 'wmm-maintenance-completed', 'wmm-package-low-balance'],
		],
		[
			'name'      => 'Project Name',
			'key'       => '{project_name}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started', 'wmm-maintenance-completed'],
		],
		[
			'name'      => 'Website URL',
			'key'       => '{website_url}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started', 'wmm-maintenance-completed'],
		],
		[
			'name'      => 'Company Name',
			'key'       => '{company_name}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started', 'wmm-maintenance-completed', 'wmm-package-low-balance'],
		],
		[
			'name'      => 'Staff Name',
			'key'       => '{staff_name}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started', 'wmm-maintenance-completed'],
		],
		// Maintenance started specific
		[
			'name'      => 'Maintenance Start Time',
			'key'       => '{maintenance_start_time}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-started'],
		],
		// Maintenance completed specific
		[
			'name'      => 'Maintenance Date',
			'key'       => '{maintenance_date}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-completed'],
		],
		[
			'name'      => 'Time Spent',
			'key'       => '{time_spent}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-completed'],
		],
		[
			'name'      => 'Tasks Completed',
			'key'       => '{tasks_completed}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-completed'],
		],
		[
			'name'      => 'Notes',
			'key'       => '{notes}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-maintenance-completed'],
		],
		// Package low balance specific
		[
			'name'      => 'Package Name',
			'key'       => '{package_name}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Hours Remaining',
			'key'       => '{hours_remaining}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Total Hours',
			'key'       => '{total_hours}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Hours Used',
			'key'       => '{hours_used}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Website Info',
			'key'       => '{website_info}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Threshold Hours',
			'key'       => '{threshold_hours}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Expiry Date',
			'key'       => '{expiry_date}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
		[
			'name'      => 'Package View Link',
			'key'       => '{package_view_link}',
			'available' => [
				'staff',
			],
			'templates' => ['wmm-package-low-balance'],
		],
	];

	return $merge_fields;
}

hooks()->add_filter('available_merge_fields', function ($merge_fields) {
	$wmm_fields     = wmm_register_merge_fields();
	$merge_fields[] = [
		'website_maintenance' => $wmm_fields,
	];

	//	echo '<pre>';
	//	print_r($merge_fields);
	//	die();

	return $merge_fields;
});
