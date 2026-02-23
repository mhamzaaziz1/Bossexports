<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Documentation Hub
Description: A centralized, rich-content documentation system inside Perfex CRM.
Version: 1.0.0
Requires at least: 2.3.*
Author: Anti-Gravity
*/

define('SMART_DOCUMENTATION_MODULE_NAME', 'smart_documentation');

hooks()->add_action('admin_init', 'smart_documentation_init_menu_items');
hooks()->add_action('app_admin_head', 'smart_documentation_add_head_components');

/**
* Register activation module hook
*/
register_activation_hook(SMART_DOCUMENTATION_MODULE_NAME, 'smart_documentation_module_activation_hook');

function smart_documentation_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SMART_DOCUMENTATION_MODULE_NAME, [SMART_DOCUMENTATION_MODULE_NAME]);

/**
* Init module menu items in setup in admin_init hook
* @return null
*/
function smart_documentation_init_menu_items()
{
    $CI = &get_instance();
    
    // Auto-migration check to ensure DB is up to date
    if (!$CI->db->field_exists('status', db_prefix() . 'smart_docs_articles')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `status` varchar(50) DEFAULT 'draft'");
    }
    if (!$CI->db->field_exists('related_module', db_prefix() . 'smart_docs_articles')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `related_module` varchar(150) DEFAULT NULL");
    }
    if (!$CI->db->field_exists('language', db_prefix() . 'smart_docs_articles')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "smart_docs_articles` ADD COLUMN `language` varchar(50) DEFAULT 'english'");
    }

    if (has_permission('smart_documentation', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart_documentation', [
            'name'     => _l('smart_documentation'),
            'href'     => admin_url('smart_documentation'),
            'icon'     => 'fa fa-book',
            'position' => 30,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('smart_documentation', [
            'slug'     => 'smart_documentation_dashboard',
            'name'     => _l('sd_dashboard'),
            'href'     => admin_url('smart_documentation'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('smart_documentation', [
            'slug'     => 'smart_documentation_manage',
            'name'     => _l('sd_manage_docs'),
            'href'     => admin_url('smart_documentation/manage'),
            'position' => 5,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('smart_documentation', [
            'slug'     => 'smart_documentation_settings',
            'name'     => _l('sd_settings'),
            'href'     => admin_url('smart_documentation/settings'),
            'position' => 10,
        ]);
    }
}

/**
* Add head components
*/
function smart_documentation_add_head_components()
{
    // Add any necessary CSS or JS here
}
