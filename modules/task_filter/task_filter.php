<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Advanced Task Filters
Description: Create custom rules for your tasks
Version: 1.0.0
Requires at least: 2.3.*
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('TASK_FILTER_MODULE_NAME', 'task_filter');

hooks()->add_action('admin_init', 'task_filter_permissions');
hooks()->add_action('admin_init', 'task_filter_module_init_menu_items');
hooks()->add_action('app_admin_head', 'task_filter_header_static_css_js');

/**
* Register activation module hook
*/

register_activation_hook(TASK_FILTER_MODULE_NAME, 'task_filter_module_activation_hook');

function task_filter_module_activation_hook() {
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}


/**
* Register language files, must be registered if the module is using languages
*/

register_language_files(TASK_FILTER_MODULE_NAME, [TASK_FILTER_MODULE_NAME]);
$CI = & get_instance();

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */

function task_filter_module_init_menu_items() {
    $CI = &get_instance();
    if (has_permission('task_filter', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('task_filter', [
                'name'     =>_l('task_filter'),
                'href'     => admin_url('task_filter'),
                'icon'     => 'fa fa-fa',
                'position' => 6,
            ]);
    }
}

function task_filter_header_static_css_js(){
    $CI = &get_instance();
	echo '<link href="' . base_url('modules/task_filter/assets/css/main.css') .'"  rel="stylesheet" type="text/css" />';

}

function task_filter_permissions() {
    $capabilities = [];
    $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
    ];

    register_staff_capabilities('task_filter', $capabilities, _l('task_filter'));
}