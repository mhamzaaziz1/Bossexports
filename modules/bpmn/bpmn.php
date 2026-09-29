<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*

Module Name: Bpmn

Description: Module to draw Bpmn

Version: 1.0.6

Author: Weeb Digital

Author URI: https://weebdigital.com/

Requires at least: 2.3.*

*/

$CI = &get_instance();

define('BPMN_MODULE_NAME', 'bpmn');
define('BPMN_DISCUSSION_ATTACHMENT_FOLDER', FCPATH . 'uploads/bpmn' . '/');


hooks()->add_action('admin_init', 'bpmn_module_init_menu_items');

hooks()->add_action('admin_init', 'bpmn_permissions');



hooks()->add_filter('global_search_result_query', 'bpmn_global_search_result_query', 10, 3);

hooks()->add_filter('global_search_result_output', 'bpmn_global_search_result_output', 10, 2);

hooks()->add_filter('migration_tables_to_replace_old_links', 'bpmn_migration_tables_to_replace_old_links');
$CI->load->helper(BPMN_MODULE_NAME . '/bpmn');




function bpmn_global_search_result_output($output, $data)

{

    if ($data['type'] == 'bpmn') {

        $output = '<a href="' . admin_url('bpmn/preview/' . $data['result']['id']) . '">' . $data['result']['title'] . '</a>';

    }



    return $output;

}



function bpmn_global_search_result_query($result, $q, $limit)

{

    $CI = &get_instance();

    if (has_permission('bpmn', '', 'view')) {

        $CI->db->select()->from(db_prefix() . 'bpmn')->like('description', $q)->or_like('title', $q)->limit($limit);



        $CI->db->order_by('title', 'ASC');



        $result[] = [

                'result'         => $CI->db->get()->result_array(),

                'type'           => 'bpmn',

                'search_heading' => _l('bpmn'),

            ];

    }



    return $result;

}



function bpmn_migration_tables_to_replace_old_links($tables)

{

    $tables[] = [

                'table' => db_prefix() . 'bpmn',

            ];



    return $tables;

}



function bpmn_permissions()

{

    $capabilities = [];



    $capabilities['capabilities'] = [

            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',

            'create' => _l('permission_create'),

            'edit'   => _l('permission_edit'),

            'delete' => _l('permission_delete'),

    ];



    register_staff_capabilities('bpmn', $capabilities, _l('bpmn'));

}



/**

* Register activation module hook

*/

register_activation_hook(BPMN_MODULE_NAME, 'bpmn_module_activation_hook');



function bpmn_module_activation_hook()

{

    $CI = &get_instance();

    require_once(__DIR__ . '/install.php');

}





/**

 * Register uninstall module hook

 */

register_uninstall_hook(BPMN_MODULE_NAME, 'bpmn_module_uninstall_hook');



function bpmn_module_uninstall_hook()

{

    $CI = &get_instance();

    require_once(__DIR__ . '/uninstall.php');

}





/**

* Register language files, must be registered if the module is using languages

*/

register_language_files(BPMN_MODULE_NAME, [BPMN_MODULE_NAME]);



/**

 * Init module menu items in setup in admin_init hook

 * @return null

 */

function bpmn_module_init_menu_items()

{

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('bpmn_menu', [

        'name' => 'BPMN', // The name if the item

        'href' => admin_url('bpmn'), // URL of the item

        'position' => 10, // The menu position, see below for default positions.

        'icon' => 'fa fa-line-chart ', // Font awesome icon

    ]);



    if (staff_can('view', 'settings')) {

        $CI = &get_instance();

        $CI->app_tabs->add_settings_tab('bpmn', [

            'name'     => '' . _l('bpmn_settings_name') . '',

            'view'     => 'bpmn/admin/settings',

            'position' => 36,

        ]);

    }





    if (is_admin()) {

        $CI->app_menu->add_setup_menu_item('bpmn', [

            'collapse' => true,

            'name' => _l('bpmn'),

            'position' => 10,

        ]);



        $CI->app_menu->add_setup_children_item('bpmn', [

            'slug' => 'bpmn-groups',

            'name' => _l('bpmn_groups'),

            'href' => admin_url('bpmn/groups'),

            'position' => 5,

        ]);

    }
    $CI->app_tabs->add_project_tab('bpmn', [
        'name'                      => _l('bpmn'),
        'icon'                      => 'fa fa-line-chart',
        'view'                      => 'bpmn/admin/project_bpmn',
        'position'                  => 56,
    ]);
}

