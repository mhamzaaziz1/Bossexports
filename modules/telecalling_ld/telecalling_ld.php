<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Telecalling Lead Distribution
Description: Advanced telemarketing management with lead distribution, email automation, WhatsApp, AI scoring and graphical dashboards.
Version: 1.0.0
Author: SoloSoft Solutions LLC
Author URI: https://solosoftsolutions.com
Requires at least: 3.0.*
*/

define('TC_MODULE',  'telecalling_ld');
define('TC_VERSION', '1.0.0');

register_activation_hook(TC_MODULE,   'tc_on_activate');
register_deactivation_hook(TC_MODULE, 'tc_on_deactivate');
register_uninstall_hook(TC_MODULE,    'tc_on_uninstall');

function tc_on_activate()   { include module_dir_path(TC_MODULE).'install.php'; }
function tc_on_deactivate() {}
function tc_on_uninstall()  { include module_dir_path(TC_MODULE).'uninstall.php'; }

hooks()->add_action('admin_init', 'tc_register_menus');

function tc_register_menus()
{
    $CI =& get_instance();
    $CI->app_menu->add_sidebar_menu_item('tc-main', [
        'name'     => 'Telecalling',
        'href'     => admin_url('telecalling_ld'),
        'position' => 11,
        'icon'     => 'fa fa-phone',
    ]);

    $items = (is_admin() || tc_is_manager())
        ? [
            ['tc-dash',      'Dashboard',    'telecalling_ld',           1],
            ['tc-assign',    'Assign Leads', 'telecalling_ld/assign',    2],
            ['tc-groups',    'Groups',       'telecalling_ld/groups',    3],
            ['tc-targets',   'Targets',      'telecalling_ld/targets',   4],
            ['tc-campaigns', 'Campaigns',    'telecalling_ld/campaigns', 5],
            ['tc-requests',  'Requests',     'telecalling_ld/requests',  7],
          ]
        : [
            ['tc-dash',    'Dashboard', 'telecalling_ld',          1],
            ['tc-myleads', 'My Leads',  'telecalling_ld/my_leads', 2],
          ];

    foreach ($items as [$slug, $name, $url, $pos]) {
        $CI->app_menu->add_sidebar_children_item('tc-main', [
            'slug'     => $slug,
            'name'     => $name,
            'href'     => admin_url($url),
            'position' => $pos,
        ]);
    }

    if (is_admin() || tc_is_manager()) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'tc-reports',
            'name'     => 'Telecalling',
            'href'     => admin_url('telecalling_ld/reports'),
            'position' => 25,
        ]);

        $CI->app_menu->add_setup_menu_item('tc-settings', [
            'name'     => 'Telecalling',
            'href'     => admin_url('telecalling_ld/settings'),
            'position' => 25,
        ]);
    }
}
