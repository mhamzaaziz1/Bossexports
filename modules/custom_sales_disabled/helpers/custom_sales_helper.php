<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get titles and save/update accordingly
 *
 * @param string $resources
 * @param mixed $id
 * @param mixed $source_id
 * @param $source_resources Optional. Will use $resources if not provided i.e assume copying.
 * @return bool
 */
function custom_sales_save_items_titles($resources, $id, $source_id = null, $source_resources = '')
{
    $CI = &get_instance();

    $data = [];

    $source_resources = empty($source_resources) ? $resources : $source_resources;

    // When copying, we want to copy source titles to the new one.
    if ($source_id) {
        $rows = $CI->custom_sales_model->get_items_titles($source_id, $source_resources);
        foreach ($rows as $row) {
            $data[] = [
                'title' => $row->title,
                'child_item_order' => $row->child_item_order
            ];
        }

        return $CI->custom_sales_model->save_items_titles($id, $resources, $data);
    } else if ($CI->input->method() === 'post') {

        $data = $CI->input->post('custom_sales_items_titles', true);
        return $CI->custom_sales_model->save_items_titles($id, $resources, $data);
    }

    return false;
}

/**
 * Remove custom input field from the resource form before submisison
 *
 * @param array $hookData
 * @param mixed $id
 * @return array
 */
function custom_sales_remove_title_fields($hookData, $id = NULL)
{
    if (isset($hookData['custom_sales_items_titles'])) {
        unset($hookData['custom_sales_items_titles']);
    }
    if (isset($hookData['data']['custom_sales_items_titles'])) {
        unset($hookData['data']['custom_sales_items_titles']);
    }
    return $hookData;
}

/**
 * Generate the plural form of a resource type.
 *
 * @param string $resources_type
 * @return string
 */
function custom_sales_get_resources_plural($resources_type)
{
    if (str_ends_with($resources_type, 's')) return $resources_type;
    return $resources_type . 's';
}

/**
 * Get hidden fields for a type
 *
 * @param string $type
 * @return array
 */
function custom_sales_client_hidden_fields($type)
{
    $option = get_option('custom_sales_client_items_hidden_field_for_' . $type);
    $hidden_fields = empty($option) ? [] : json_decode($option, true);
    $hidden_fields = empty($hidden_fields) ? $hidden_fields : array_filter($hidden_fields); // Remove empty values
    $hidden_fields = hooks()->apply_filters('custom_sales_resources_type_hidden_fields', ['hidden_fields' => $hidden_fields, 'type' => $type]);
    return $hidden_fields['hidden_fields'] ?? [];
}

/**
 * Determine if a field can be shown on item
 *
 * @param string $field
 * @param object $transaction
 * @return bool
 */
function custom_sales_can_show_column($field, $transaction, $type)
{
    $can_show = true;
    $type_option_key = $type == 'invoice' && !empty($transaction->deposit_from_estimate_id) ? 'deposit_invoice' : $type;
    $hidden_fields = custom_sales_client_hidden_fields($type_option_key);
    if ($type == 'invoice' && !empty($transaction->deposit_from_estimate_id) && (int)get_option('custom_sales_hide_pricing_on_deposit_invoice')) {
        $can_show = !in_array($field, $hidden_fields);
    } else {
        $viewing_as_customer = get_instance() instanceof ClientsController;
        if (is_staff_logged_in() && !$viewing_as_customer) {
            $can_show = true;
        } else {

            $can_show = !in_array($field, $hidden_fields);
        }
    }

    return hooks()->apply_filters('custom_sales_can_show_column', ['hidden_fields' => $hidden_fields, 'field' => $field, 'type' => $type, 'transaction' => $transaction, 'can_show' => $can_show])['can_show'] ?? true;
}

/**
 * Check if editing deposit invoice is allowed or not
 *
 * @return bool
 */
function custom_sales_disable_updating_deposit_invoice()
{
    return (int)get_option('custom_sales_disable_updating_deposit_invoice');
}

/**
 * Get list of available sales type
 *
 * @param bool $include_deposit_invoice
 * @return array
 */
function custom_sales_get_item_types($include_deposit_invoice = false)
{
    $resources = ['invoice', 'estimate', 'credit_note', 'proposal'];
    $resources = hooks()->apply_filters('custom_sales_resources_type_list', $resources);
    if ($include_deposit_invoice) $resources[] = 'deposit_invoice';
    return $resources;
}

/**
 * Copy converted sales type items titles
 *
 * @param array $data
 * @return void
 */
function custom_sales_copy_converted_items_titles($data)
{
    // Get the active filter
    $filter = hooks()->current_filter();

    $resources = explode('_converted_to_', $filter, 2);
    if (count($resources) === 2) {
        $source = $resources[0];
        $destination = $resources[1];

        if ($destination == $source) return false;

        $source_id = $data[$source . '_id'] ?? $data[$source . 'id'] ?? '';
        $destination_id = $data[$destination . '_id'] ?? $data[$destination . 'id'] ?? '';
        if (!empty($source_id) && !empty($destination_id)) {
            custom_sales_save_items_titles($destination, $destination_id, $source_id, $source);
        }
    }
}

/**
 * Check for company logo upload
 * @return boolean
 */
function custom_sales_handle_file_upload($index, $allowed_extensions = [], $update_option = true)
{
    $success   = false;
    if (isset($_FILES[$index]) && !empty($_FILES[$index]['name']) && _perfex_upload_error($_FILES[$index]['error'])) {
        set_alert('warning', _perfex_upload_error($_FILES[$index]['error']));

        return false;
    }

    if (isset($_FILES[$index]['name']) && $_FILES[$index]['name'] != '') {
        hooks()->do_action('before_upload_company_logo_attachment');
        $path = get_upload_path_by_type('company');
        // Get the temp file path
        $tmpFilePath = $_FILES[$index]['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension          = strtolower(pathinfo($_FILES[$index]['name'], PATHINFO_EXTENSION));

            if (empty($allowed_extensions))
                $allowed_extensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'svg',
                ];

            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', 'Image extension not allowed.');
                return $success;
            }

            // Setup our new file path
            $filename    = 'custom_sales_' . md5($index . time()) . '.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                if ($update_option)
                    update_option($index, $filename);
                $success = true;
            }
        }
    }
    return $success;
}

function custom_sales_input_label_with_hint($key, $param = '')
{
    return _l($key, $param) . ' <i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . _l($key . '_hint', $param) . '"></i>';
}

function custom_sales_disable_invoice_update_status()
{
    $key = 'custom_sales_disable_invoice_update_status';
    $value = get_option($key);
    return empty($value) ? [] : array_filter((array)json_decode($value));
}

function custom_sales_disable_invoice_delete_status()
{
    $key = 'custom_sales_disable_invoice_delete_status';
    $value = get_option($key);
    return empty($value) ? [] : array_filter((array)json_decode($value));
}

function custom_sales_invoice_status_label_map()
{
    return [
        Invoices_model::STATUS_PAID => ['key' => Invoices_model::STATUS_PAID, 'label' => _l('invoice_status_paid')],
        Invoices_model::STATUS_PARTIALLY => ['key' => Invoices_model::STATUS_PARTIALLY, 'label' => _l('invoice_status_not_paid_completely')],
        Invoices_model::STATUS_CANCELLED => ['key' => Invoices_model::STATUS_CANCELLED, 'label' => _l('invoice_status_cancelled')],
        Invoices_model::STATUS_OVERDUE => ['key' => Invoices_model::STATUS_OVERDUE, 'label' => _l('invoice_status_overdue')],
        Invoices_model::STATUS_UNPAID => ['key' => Invoices_model::STATUS_UNPAID, 'label' => _l('invoice_status_unpaid')],
        Invoices_model::STATUS_DRAFT => ['key' => Invoices_model::STATUS_DRAFT, 'label' => _l('invoice_status_draft')],
    ];
}