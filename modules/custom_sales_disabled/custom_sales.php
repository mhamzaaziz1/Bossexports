<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Deposit Invoice and Sales Item Customization
Description: Make split deposit invoice from an estimate and customize sales items: invoices, estimates e.t.c
Version: 1.0.6
Requires at least: 3.1.*
Author: ulutfa
Author URI: https://codecanyon.net/user/ulutfa
*/

defined('CUSTOM_SALES_MODULE_NAME') or define('CUSTOM_SALES_MODULE_NAME', 'custom_sales');

$CI = &get_instance();

/**
 * Load the helpers
 */
$CI->load->helper(CUSTOM_SALES_MODULE_NAME . '/' . CUSTOM_SALES_MODULE_NAME);


/**
 * Load the models
 */
$CI->load->model(CUSTOM_SALES_MODULE_NAME . '/' . CUSTOM_SALES_MODULE_NAME . '_model');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(CUSTOM_SALES_MODULE_NAME, [CUSTOM_SALES_MODULE_NAME]);

/**
 * Register activation module hook
 */
register_activation_hook(CUSTOM_SALES_MODULE_NAME, function () {
    require(__DIR__ . '/install.php');
});

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
hooks()->add_action('admin_init', function () use ($CI) {
    // Add settings to settings group.
    $CI->app_tabs->add_settings_tab(CUSTOM_SALES_MODULE_NAME, [
        'name'     => _l('settings_group_' . CUSTOM_SALES_MODULE_NAME),
        'view'     => CUSTOM_SALES_MODULE_NAME . '/admin/settings',
        'position' => 29,
        'icon'     => 'fa fa-receipt',
    ]);
}, PHP_INT_MAX);


// JSON encode neccessary settings before saving.
hooks()->add_filter('before_settings_updated', 'custom_sales_before_settings_updated_hook');
function custom_sales_before_settings_updated_hook($data)
{
    $array_fields = [
        'custom_sales_item_title_styles',
        'custom_sales_depoist_invoice_allowed_payment_modes',
        'custom_sales_disable_invoice_update_status',
        'custom_sales_disable_invoice_delete_status'
    ];
    $items_types = custom_sales_get_item_types(true);
    foreach ($items_types as $type) {
        $array_fields[] = 'custom_sales_client_items_hidden_field_for_' . $type;
    }

    foreach ($array_fields as $field) {
        if (isset($data['settings'][$field])) {
            $data['settings'][$field] = json_encode($data['settings'][$field]);
        }
    }

    // Upload pdf background
    custom_sales_handle_file_upload('custom_sales_pdf_background_image');

    return $data;
}


/**
 * Register admin footer hook
 */
hooks()->add_action('app_admin_footer', function () {
    //load common admin asset
    $CI = &get_instance();
    $CI->load->view(CUSTOM_SALES_MODULE_NAME . '/admin/scripts/common');
});


/**
 * Use custom class for rendering items table
 */
hooks()->add_filter('items_table_class', function ($class, $transaction, $type, $for, $admin_preview) {
    $hidden_fields = custom_sales_client_hidden_fields($type);

    $titles = get_instance()->custom_sales_model->get_items_titles($transaction->id, $type);

    if (!empty($hidden_fields) || !empty($titles) || !empty($transaction->deposit_from_estimate_id)) { // Control if title or field have been set for the sales type only
        require_once(APP_MODULES_PATH . CUSTOM_SALES_MODULE_NAME . '/libraries/Custom_sales_items_table.php');
        return new Custom_sales_items_table($class, $transaction, $type, $for, $admin_preview);
    }
    return $class;
}, 10, 5);

/**
 * Use custom class for rendering items table
 */
hooks()->add_filter('items_table_class', function ($class, $transaction, $type, $for, $admin_preview) {
    if (!empty($transaction->deposit_from_estimate_id)) { // Control if is deposit invoice
        require_once(APP_MODULES_PATH . CUSTOM_SALES_MODULE_NAME . '/libraries/Custom_sales_deposit_items_table.php');
        return new Custom_sales_deposit_items_table($class, $transaction, $type, $for, $admin_preview);
    }
    return $class;
}, 10, 5);


/**
 * Prevent updating of split deposit invoice
 */
hooks()->add_filter('before_update_invoice', function ($hookData, $id) {

    $CI = &get_instance();

    if (!empty($CI->input->post('items')) && custom_sales_disable_updating_deposit_invoice()) {
        $original_invoice = $CI->invoices_model->get($id);
        if (!empty($original_invoice->deposit_from_estimate_id)) {
            set_alert('danger', _l('custom_sales_deposit_invoice_cannot_be_updated'));
            redirect(admin_url('invoices/list_invoices/' . $id));
        }
    }
    return custom_sales_remove_title_fields($hookData, $id);
}, 10, 2);

hooks()->add_action('before_render_invoice_template', function ($invoice = null) {
    if (custom_sales_disable_updating_deposit_invoice() && !empty($invoice->deposit_from_estimate_id)) {
        set_alert('danger', _l('custom_sales_deposit_invoice_cannot_be_updated'));
        redirect(admin_url('invoices/list_invoices/' . $invoice->id));
    }
});

hooks()->add_action('after_invoice_preview_template_rendered', function ($invoice) {
    if (custom_sales_disable_updating_deposit_invoice() && !empty($invoice->deposit_from_estimate_id)) {
        echo '<script>var DEPOSIT_INVOICE_PREVIEW=' . $invoice->id . ';</script>';
    }
});



hooks()->add_action('admin_init', function () {

    // Custom mode of detecting copying of estimate through flashdata. No copy event emitted for estimate
    $CI = &get_instance();
    $controller = $CI->router->fetch_class();
    $method = $CI->router->fetch_method();
    // map into plural form to fit with the controller
    $resources = array_map('custom_sales_get_resources_plural', custom_sales_get_item_types());
    if (in_array($controller, $resources) && $method === 'copy') {
        $segments = $CI->uri->segments;
        $id = last($segments);
        if (!empty((int)$id))
            $CI->session->set_userdata('custom_sales_copying_from', $id);
    }

    // Add neccessary hooks for each supported type
    foreach (custom_sales_get_item_types() as $type) {
        // Before update clean up form data
        hooks()->add_filter('before_' . $type . '_added', 'custom_sales_remove_title_fields');

        // Proposal model does not have before_proposal_added hook but before_create_proposal
        if ($type == 'proposal') {
            hooks()->add_filter('before_create_proposal', 'custom_sales_remove_title_fields');
            hooks()->add_action('proposal_created', function ($id) use ($type) {

                $CI = &get_instance();
                // Detect if copying as previously set for those type that does not have copied hooks.
                if ($copy_from = $CI->session->userdata('custom_sales_copying_from')) {
                    $CI->session->unset_userdata('custom_sales_copying_from');
                    // Emit custom hooks
                    hooks()->do_action('proposal_copied', ['copy_from' => $copy_from, 'copy_id' => $id]);
                }

                custom_sales_save_items_titles($type, $id);
            });
        }

        // Before updating clean up form data
        hooks()->add_filter('before_update_' . $type, function ($hookData, $id) use ($type) {
            // Always update titles as invoice_updated hook is only triggered when item or invoice details is modified
            custom_sales_save_items_titles($type, $id);

            // Remove title field and return data
            return custom_sales_remove_title_fields($hookData, $id);
        }, 10, 2);

        if ($type !== 'invoice') { // skip for invoice - deprecated for invoice i.e before_invoice_updated
            hooks()->add_filter('before_' . $type . '_updated', function ($hookData, $id)  use ($type) {

                // Always update titles as after_estimate_updated hook is only triggered when items or estimate details is modified
                custom_sales_save_items_titles($type, $id);

                // Remove title field and return data
                return custom_sales_remove_title_fields($hookData, $id);
            }, 10, 2);
        }

        // After removing resources remove titles
        hooks()->add_action('after_' . $type . '_deleted', function ($id) use ($type) {
            get_instance()->custom_sales_model->save_items_titles($id, $type);
        });

        // After adding a resources
        hooks()->add_action('after_' . $type . '_added', function ($id) use ($type) {

            $CI = &get_instance();
            // Detect if copying as previously set for those type that does not have copied hooks.
            if ($copy_from = $CI->session->userdata('custom_sales_copying_from')) {
                $CI->session->unset_userdata('custom_sales_copying_from');
                // Emit custom hooks
                hooks()->do_action($type . '_copied', ['copy_from' => $copy_from, 'copy_id' => $id]);
            }

            custom_sales_save_items_titles($type, $id);
        });


        // when copying, copy source titles into the new item
        hooks()->add_action($type . '_copied', function ($data) use ($type) {
            $copy_from = $data['copy_from'];
            $invoice_id = $data['copy_id'];
            custom_sales_save_items_titles($type, $invoice_id, $copy_from);
        });
    }
});

// Listen to conversion events
hooks()->add_action('estimate_converted_to_invoice', 'custom_sales_copy_converted_items_titles');

hooks()->add_action('estimate_converted_to_purchase_order', 'custom_sales_copy_converted_items_titles');
hooks()->add_action('purchase_order_converted_to_invoice', 'custom_sales_copy_converted_items_titles');
hooks()->add_action('purchase_order_converted_to_delivery_note', 'custom_sales_copy_converted_items_titles');
hooks()->add_action('delivery_note_converted_to_invoice', 'custom_sales_copy_converted_items_titles');
hooks()->add_action('invoice_converted_to_delivery_note', 'custom_sales_copy_converted_items_titles');
hooks()->add_action('estimate_converted_to_delivery_note', 'custom_sales_copy_converted_items_titles');


/** Helpers to estimate id on invoice view */
hooks()->add_action('after_admin_invoice_preview_template_tab_content_last_item', function ($invoice) {
    $CI = &get_instance();
    $CI->load->view(CUSTOM_SALES_MODULE_NAME . '/admin/scripts/show_invoice_estimate', ['invoice' => $invoice]);
});


hooks()->add_action('pdf_header', function ($data) {

    $image = get_option('custom_sales_pdf_background_image');
    if (!empty($image)) {
        $img_file = get_upload_path_by_type('company') . '/' . $image;

        $pdf = &$data['pdf_instance'];
        $type = $data['type'];

        // get the current page break margin
        $bMargin = $pdf->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf->GetAutoPageBreak();
        // disable auto-page-break
        $pdf->SetAutoPageBreak(false, 0);
        // set bacground image
        $pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf->setPageMark();
    }
});


hooks()->add_filter('item_preview_amount_with_currency', function (
    $formatted_qty,
    $item,
    $transaction,
    $excluded_currency
) {
    if ((int)get_option('custom_sales_add_tax_to_item_table_amounts')) {

        $amount = ($item['qty'] * $item['rate']);
        $total_tax = 0;

        if (count($item['taxes']) > 0) {
            foreach ($item['taxes'] as $tax) {
                $item_tax = (float)app_format_number($tax['taxrate']);
                $total_tax += ($item_tax / 100) * $amount;
            }
        }
        $amount_with_tax = $amount + $total_tax;
        return app_format_money($amount_with_tax, $transaction->currency_name, $excluded_currency);
    }
    return $formatted_qty;
}, 10, 4);




/**
 * Prevent updating of certain invoice with particular attribute
 */
hooks()->add_filter('before_update_invoice', function ($hookData, $id) {

    $CI = &get_instance();

    $statuses = custom_sales_disable_invoice_update_status();
    if (!empty($CI->input->post()) && !empty($statuses)) {
        $invoice = $CI->invoices_model->get($id);
        if (in_array($invoice->status, $statuses)) {
            $status_text = custom_sales_invoice_status_label_map()[$invoice->status]['label'] ?? '';
            set_alert('danger', _l('custom_sales_invoice_cannot_be_updated', $status_text));
            redirect(admin_url('invoices/list_invoices/' . $id));
        }
    }
    return $hookData;
}, 10, 2);

hooks()->add_action('after_invoice_preview_template_rendered', function ($invoice) {
    if (!empty($statuses = custom_sales_disable_invoice_update_status()) && in_array($invoice->status, $statuses)) {
        echo '<script>var DEPOSIT_INVOICE_PREVIEW=' . $invoice->id . ';</script>';
    }
});

hooks()->add_action('after_render_invoice_template', function ($invoice) {

    if (!$invoice) return;

    if (!empty($statuses = custom_sales_disable_invoice_update_status()) && in_array($invoice->status, $statuses)) {
        echo "
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('.invoice-form').find('input, select, textarea, button, a').prop('disabled', true).addClass('disabled');
            });
            </script>
        ";
    }
});

/**
 * Prevent deleting of certain invoice with particular attribute
 */
hooks()->add_action('before_invoice_deleted', function ($id) {

    $CI = &get_instance();
    $statuses = custom_sales_disable_invoice_delete_status();
    if (!empty($statuses)) {
        $invoice = $CI->invoices_model->get($id);
        if (in_array($invoice->status, $statuses)) {
            $status_text = custom_sales_invoice_status_label_map()[$invoice->status]['label'] ?? '';
            set_alert('danger', _l('custom_sales_invoice_cannot_be_deleted', $status_text));
            redirect(admin_url('invoices/list_invoices/' . $id));
        }
    }
});
