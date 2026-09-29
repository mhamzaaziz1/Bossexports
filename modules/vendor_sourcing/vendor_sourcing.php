<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Vendor Sourcing
Description: Establish a many-to-many data relationship between Items and Vendors, enabling advanced search capabilities and automated data ingestion from the Purchase module.
Version: 1.1.0
Requires at least: 2.3.*
*/

define('VENDOR_SOURCING_MODULE_NAME', 'vendor_sourcing');

hooks()->add_action('admin_init', 'vendor_sourcing_module_init_menu_items');
hooks()->add_action('app_admin_footer', 'vendor_sourcing_inject_item_tab');
hooks()->add_action('item_created', 'vendor_sourcing_item_created');
hooks()->add_action('item_updated', 'vendor_sourcing_item_updated');

// Hook into the Purchase module's goods receipt filter (fires when PO status changes)
// The Purchase model fires: apply_filters('create_goods_receipt', ['status'=>$status, 'id'=>$id])
// We use add_filter to intercept it and run our pipeline.
hooks()->add_filter('create_goods_receipt', 'vendor_sourcing_purchase_pipeline');

function vendor_sourcing_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('vendor_sourcing', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('vendor_sourcing', [
            'name'     => 'Vendor Sourcing',
            'href'     => admin_url('vendor_sourcing/locator'),
            'position' => 35,
            'icon'     => 'fa fa-cubes',
        ]);

        $CI->app_menu->add_sidebar_children_item('vendor_sourcing', [
            'slug'     => 'vendor_sourcing_locator',
            'name'     => 'Sourcing Locator',
            'href'     => admin_url('vendor_sourcing/locator'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('vendor_sourcing', [
            'slug'     => 'vendor_sourcing_inquiry_log',
            'name'     => 'Inquiry Log',
            'href'     => admin_url('vendor_sourcing/inquiry_log'),
            'position' => 2,
        ]);

        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('vendor_sourcing', [
                'slug'     => 'vendor_sourcing_inquiry_settings',
                'name'     => 'Inquiry Settings',
                'href'     => admin_url('vendor_sourcing/inquiry_settings'),
                'position' => 3,
            ]);
        }
    }
}

function vendor_sourcing_inject_item_tab()
{
    $CI = &get_instance();
    $segment2 = $CI->uri->segment(2);

    // Sales/Invoice Items — inject modal injection script
    if ($segment2 == 'invoice_items' || $segment2 == 'items') {
        echo '<script src="' . base_url('modules/vendor_sourcing/assets/js/item_integration.js') . '?v=' . VENDOR_SOURCING_MODULE_NAME . '"></script>';
    }

    // Warehouse — inject tab injection script on all warehouse pages
    // (the JS itself checks the URL for view_item_details pattern)
    if ($segment2 == 'warehouse') {
        echo '<script src="' . base_url('modules/vendor_sourcing/assets/js/warehouse_item_integration.js') . '?v=' . VENDOR_SOURCING_MODULE_NAME . '"></script>';
    }
}

function vendor_sourcing_item_created($item_id)
{
    $CI = &get_instance();
    $CI->load->model('vendor_sourcing/vendor_sourcing_model');
    $vendors = $CI->input->post('vendors');
    if (is_array($vendors)) {
        $CI->vendor_sourcing_model->save_item_vendors($item_id, $vendors);
    }
}

function vendor_sourcing_item_updated($item_id)
{
    $CI = &get_instance();
    $CI->load->model('vendor_sourcing/vendor_sourcing_model');
    $vendors = $CI->input->post('vendors');
    $CI->vendor_sourcing_model->save_item_vendors($item_id, is_array($vendors) ? $vendors : []);
}

/**
 * Intercepts the create_goods_receipt filter from the Purchase module.
 * Fires every time a PO status is changed; we only act on status = 2 (approved).
 *
 * @param  array $data  ['status' => int, 'id' => int]
 * @return array $data  (must return the value for apply_filters to work)
 */
function vendor_sourcing_purchase_pipeline($data)
{
    // Only process on approved status
    if (!isset($data['id']) || !isset($data['status']) || (int) $data['status'] !== 2) {
        return $data;
    }

    $CI = &get_instance();
    $CI->load->model('vendor_sourcing/vendor_sourcing_model');
    $CI->vendor_sourcing_model->upsert_from_purchase_order((int) $data['id']);

    return $data;
}

// Language files
register_language_files(VENDOR_SOURCING_MODULE_NAME, [VENDOR_SOURCING_MODULE_NAME]);
