<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: AI Query Builder
Description: Convert natural language to SQL queries using AI
Version: 1.0.0
Requires at least: 2.3.*
Author: AI Developer
*/

define('AI_QUERY_BUILDER_MODULE_NAME', 'ai_query_builder');

// Add menu item in admin area
hooks()->add_action('admin_init', 'ai_query_builder_module_init_menu_items');
hooks()->add_action('admin_init', 'ai_query_builder_add_settings_tab');

/**
 * Register activation module hook
 */
register_activation_hook(AI_QUERY_BUILDER_MODULE_NAME, 'ai_query_builder_module_activation_hook');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(AI_QUERY_BUILDER_MODULE_NAME, [AI_QUERY_BUILDER_MODULE_NAME]);

/**
 * Module activation hook
 */
function ai_query_builder_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Add settings tab
 */
function ai_query_builder_add_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('ai_query_builder', [
        'name'     => _l('ai_query_builder'),
        'view'     => AI_QUERY_BUILDER_MODULE_NAME . '/settings',
        'position' => 101,
    ]);
}

/**
 * Init AI Query Builder module menu items in setup in admin_init hook
 * @return null
 */
function ai_query_builder_module_init_menu_items()
{
    $CI = &get_instance();

    // Add main menu item
    $CI->app_menu->add_sidebar_menu_item('ai-query-builder', [
        'name'     => _l('ai_query_builder'),
        'href'     => admin_url('ai_query_builder'),
        'icon'     => 'fa fa-search',
        'position' => 60,
    ]);

    // Add quick actions link
    $CI->app->add_quick_actions_link([
        'name'       => _l('ai_query_builder'),
        'permission' => 'ai_query_builder',
        'url'        => 'ai_query_builder',
        'position'   => 70,
    ]);
}