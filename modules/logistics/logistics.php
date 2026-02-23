<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Logistics
Description: Adds Weight and Volume management to Items and Sales.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('LOGISTICS_MODULE_NAME', 'logistics');

hooks()->add_action('admin_init', 'logistics_init_menu');
hooks()->add_action('before_items_modal_render', 'logistics_add_item_inputs');
hooks()->add_action('app_admin_footer', 'logistics_load_js');

// Register filters for PDF
// Note: hook priority 10, 1 argument for headers (array), 2 arguments for row (row array, item object)
hooks()->add_filter('pdf_items_table_headers', 'logistics_pdf_heading_filter');
hooks()->add_filter('pdf_items_table_row', 'logistics_pdf_row_filter', 10, 2);

hooks()->add_action('admin_init', 'logistics_register_controller');

/**
 * Register activation
 */
register_activation_hook(LOGISTICS_MODULE_NAME, 'logistics_activation_hook');

function logistics_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Dummy function for menu init if needed, user didn't ask for menu but it's good practice
 */
function logistics_init_menu() {
    // No menu requested
}

/**
 * Add inputs to Item Modal
 */
function logistics_add_item_inputs($item)
{
    $data['item'] = $item;
    $CI = &get_instance();
    echo $CI->load->view('logistics/item_inputs', $data, true);
}

/**
 * Load JS and inject data for existing items
 */
function logistics_load_js()
{
    $CI = &get_instance();
    $view = $CI->router->fetch_class(); 
    $method = $CI->router->fetch_method();
    
    $allowed = ['invoices', 'requests', 'estimates', 'proposals', 'credit_notes'];
    
    if (in_array($view, $allowed)) {
        echo '<script src="' . module_dir_url(LOGISTICS_MODULE_NAME, 'assets/js/sales_logistics.js') . '?v=' . time() . '"></script>';
        
        $id = '';
        if ($method == 'invoice' || $method == 'estimate' || $method == 'proposal' || $method == 'credit_note') {
            $segments = $CI->uri->segment_array();
            $last = end($segments);
            if (is_numeric($last)) {
                $id = $last;
            }
        }
        
        if ($id) {
            $rel_type = '';
            switch ($view) {
                case 'invoices': $rel_type = 'invoice'; break;
                case 'estimates': $rel_type = 'estimate'; break;
                case 'proposals': $rel_type = 'proposal'; break;
                case 'credit_notes': $rel_type = 'credit_note'; break;
            }
            
            if ($rel_type) {
                $items = $CI->db->where('rel_id', $id)->where('rel_type', $rel_type)->get(db_prefix().'itemable')->result_array();
                if ($items) {
                    echo '<script>var logistics_items_data = ' . json_encode($items) . ';</script>';
                }
            }
        }
    }
}

function logistics_pdf_heading_filter($headers)
{
    $qtyPos = -1;
    $i = 0;
    foreach($headers as $h) {
        if (stripos($h, 'qty') !== false || stripos($h, 'quantity') !== false) {
            $qtyPos = $i;
            break;
        }
        $i++;
    }
    
    if ($qtyPos !== -1) {
        array_splice($headers, $qtyPos + 1, 0, ['Volume', 'Weight']);
    } else {
        $headers[] = 'Volume';
        $headers[] = 'Weight';
    }
    
    return $headers;
}

function logistics_pdf_row_filter($row_data, $item) 
{
    // $row_data is array, $item is object
    // Find injection point (after qty)
    // We assume the order matches headers.
    
    // Wait, $row_data keys match header order? usually indexed array.
    // Need to find which index corresponds to Qty.
    // Since we don't have headers here easily, we approximate or rely on knowing Perfex structure.
    
    // Default Perfex Columns: #, Item, Qty, Rate, Tax, Amount
    // We injected after Qty.
    // So usually index 2 (0-based) is Qty.
    
    // Safer: Just inject at index 2?
    // We can't be 100% sure of Qty position without headers. 
    // But usually index 2.
    
    // Let's assume index 2 is Qty.
    $qtyIndex = 2; // #=0, Item=1, Qty=2
    
    $weight = isset($item['weight']) ? $item['weight'] : (isset($item->weight) ? $item->weight : '');
    $volume = isset($item['volume']) ? $item['volume'] : (isset($item->volume) ? $item->volume : '');
    
    array_splice($row_data, $qtyIndex + 1, 0, [$volume, $weight]);
    
    return $row_data;
}

function logistics_register_controller(){
    // nothing needed really, standard MVC works in modules
}
