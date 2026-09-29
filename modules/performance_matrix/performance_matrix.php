<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Performance Matrix
Description: A comprehensive performance matrix module for Items, Customers, and Vendors.
Version: 1.0.0
Requires at least: 2.3.2
*/

define('PERFORMANCE_MATRIX_MODULE_NAME', 'performance_matrix');

hooks()->add_action('admin_init', 'performance_matrix_module_init_menu_items');

/**
 * Register the main menu item
 */
function performance_matrix_module_init_menu_items()
{
    if (has_permission('performance_matrix', '', 'view')) {
        get_instance()->app_menu->add_sidebar_menu_item('performance_matrix', [
            'name'     => 'Performance Matrix',
            'href'     => admin_url('performance_matrix'),
            'position' => 30,
            'icon'     => 'fa fa-bar-chart',
        ]);
        
        get_instance()->app_menu->add_sidebar_children_item('performance_matrix', [
            'slug'     => 'item_performance_matrix',
            'name'     => 'Item Performance',
            'href'     => admin_url('performance_matrix/item'),
            'position' => 5,
        ]);
        
        get_instance()->app_menu->add_sidebar_children_item('performance_matrix', [
            'slug'     => 'customer_performance_matrix',
            'name'     => 'Customer Performance',
            'href'     => admin_url('performance_matrix/customer'),
            'position' => 10,
        ]);
        
        get_instance()->app_menu->add_sidebar_children_item('performance_matrix', [
            'slug'     => 'vendor_performance_matrix',
            'name'     => 'Vendor Performance',
            'href'     => admin_url('performance_matrix/vendor'),
            'position' => 15,
        ]);
    }
}

/**
 * Register Customer Profile Tab
 */
hooks()->add_filter('client_tabs', 'performance_matrix_client_tabs');

function performance_matrix_client_tabs($tabs) {
    if (has_permission('performance_matrix', '', 'view')) {
        $tabs['performance_matrix'] = [
            'name'     => 'Performance Matrix',
            'view'     => PERFORMANCE_MATRIX_MODULE_NAME . '/customer_profile_tab',
            'position' => 50,
            'icon'     => 'fa fa-bar-chart',
        ];
    }
    return $tabs;
}

/**
 * Register permissions
 */
hooks()->add_action('admin_init', 'performance_matrix_permissions');

function performance_matrix_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        ],
    ];

    register_staff_capabilities('performance_matrix', $capabilities, 'Performance Matrix');
}

/**
 * Load the module helper if needed
 */
// get_instance()->load->helper(PERFORMANCE_MATRIX_MODULE_NAME . '/performance_matrix');

/**
 * Re-register the customer performance hooks here instead of the old module
 */
hooks()->add_action('app_admin_footer', 'performance_matrix_load_js');
hooks()->add_action('admin_head', 'performance_matrix_load_css');

function performance_matrix_load_js() {
    echo '<script src="' . module_dir_url('performance_matrix', 'assets/js/performance_matrix.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
}

function performance_matrix_load_css() {
    // Add any necessary CSS here
}

/**
 * Customer Performance Matrix Hooks
 */
hooks()->add_filter('invoices_table_row_data', 'performance_matrix_add_customer_stats_link', 10, 2);
hooks()->add_filter('estimates_table_row_data', 'performance_matrix_add_customer_stats_link', 10, 2);
hooks()->add_filter('proposals_table_row_data', 'performance_matrix_add_proposal_stats_link', 10, 2);

function performance_matrix_add_customer_stats_link($row, $aRow) {
    if (isset($aRow['clientid']) && !empty($aRow['clientid'])) {
        $stats_link = ' | <a href="#" class="view-customer-matrix-stats" data-clientid="' . $aRow['clientid'] . '" data-toggle="tooltip" data-title="View Customer Performance Stats"><i class="fa fa-bar-chart"></i> Stats</a>';
        if (isset($row[0]) && strpos($row[0], '</div>') !== false) {
            $row[0] = str_replace('</div>', $stats_link . '</div>', $row[0]);
        }
    }
    return $row;
}

function performance_matrix_add_proposal_stats_link($row, $aRow) {
    if (isset($aRow['rel_type']) && $aRow['rel_type'] == 'customer' && isset($aRow['rel_id']) && !empty($aRow['rel_id'])) {
        $stats_link = ' | <a href="#" class="view-customer-matrix-stats" data-clientid="' . $aRow['rel_id'] . '" data-toggle="tooltip" data-title="View Customer Performance Stats"><i class="fa fa-bar-chart"></i> Stats</a>';
        if (isset($row[0]) && strpos($row[0], '</div>') !== false) {
            $row[0] = str_replace('</div>', $stats_link . '</div>', $row[0]);
        }
    }
    return $row;
}
