<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Bulk PDF Export
Description: Export invoices, estimates, and quotes for a given date range into a single printable PDF.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('BULK_PDF_EXPORT_MODULE_NAME', 'bulk_pdf_export');

hooks()->add_action('admin_init', 'bulk_pdf_export_module_init_menu_items');

/**
* Register activation module hook
*/
register_activation_hook(BULK_PDF_EXPORT_MODULE_NAME, 'bulk_pdf_export_module_activation_hook');

function bulk_pdf_export_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(BULK_PDF_EXPORT_MODULE_NAME, [BULK_PDF_EXPORT_MODULE_NAME]);

/**
 * Init bulk pdf export module menu items in setup in admin_init hook
 * @return null
 */
function bulk_pdf_export_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('reports', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'bulk-pdf-export-report',
            'name'     => _l('bulk_pdf_export_report'),
            'href'     => admin_url('bulk_pdf_export/report'),
            'position' => 99,
        ]);
    }
}
