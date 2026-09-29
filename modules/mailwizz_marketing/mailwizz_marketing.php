<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: MailWizz - Email Marketing Application
Author: UHScripts
Author URI: http://www.uhscripts.com
Description: Properly handle email marketing for your own purposes
Version: 1.0.0
Requires at least: 2.3.*
 */
define('MAILWIZZ_MODULE_NAME', 'mailwizz_marketing');

require __DIR__ . '/vendor/autoload.php';

$CI = &get_instance();

hooks()->add_action('admin_init', 'mailwizz_marketing_module_init_menu_items');
hooks()->add_action('before_lead_added', 'add_lead_to_mailwizz_marketing', 10); //

/**
 * Register activation module hook
 */
register_activation_hook(MAILWIZZ_MODULE_NAME, 'mailwizz_marketing_module_activation_hook');

/**
 * Loads the module function helper
 */
$CI->load->helper(MAILWIZZ_MODULE_NAME . '/mailwizz_marketing');

function mailwizz_marketing_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MAILWIZZ_MODULE_NAME, [MAILWIZZ_MODULE_NAME]);

/**
 * Init backup module menu items in setup in admin_init hook
 * @return null
 */
function mailwizz_marketing_module_init_menu_items()
{
    /**
     * If the logged in user is administrator, add custom menu in Setup
     */

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('mailwizz_marketing-menu', [
        'slug' => 'mailwizz_marketing',
        'name' => _l('mailwizz_marketing_menu'),
        'collapse' => true,
        'icon' => 'fa fa-envelope',
        'position' => 5,
    ]);

    $CI->app_menu->add_sidebar_children_item('mailwizz_marketing-menu', [

        'slug' => 'mailwizz_marketing-settings', // Required ID/slug UNIQUE for the child menu

        'name' => _l('mailwizz_marketing_settings'), // The name if the item

        'href' => admin_url('mailwizz_marketing/settings'),

    ]);

    $CI->app_menu->add_sidebar_children_item('mailwizz_marketing-menu', [

        'slug' => 'mailwizz_marketing-campaign-list', // Required ID/slug UNIQUE for the child menu

        'name' => _l('mailwizz_marketing_campaign_list'), // The name if the item

        'href' => admin_url('mailwizz_marketing/campaign/campaign_list'),

    ]);

    $CI->app_menu->add_sidebar_children_item('mailwizz_marketing-menu', [

        'slug' => 'mailwizz_marketing-add-campaign', // Required ID/slug UNIQUE for the child menu

        'name' => _l('mailwizz_marketing_create_campaign'), // The name if the item

        'href' => admin_url('mailwizz_marketing/campaign/mailwizz_marketing_create_campaign'),

    ]);

    function add_lead_to_mailwizz_marketing($data)
    {

        get_lead($data);

        return $data;
    }

}
