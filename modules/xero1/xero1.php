<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Xero Integration Module
Description: Complete Xero integration, to sync all the payments and invoices with Xero
Version: 1.0
Requires at least: 2.3.*
*/

define('XERO_MODULE_NAME', 'xero');
$CI = &get_instance();

/**
* Register activation module hook
*/
register_activation_hook(XERO_MODULE_NAME, 'xero_module_activation_hook');

function xero_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    require_once(__DIR__ . '/libraries/XeroAPIWrapper.php');
}

hooks()->add_action('admin_init', 'xero_module_init_menu_items');

hooks()->add_action('after_invoice_added','sync_invoice_data_with_xero', 10);

hooks()->add_action('before_payment_recorded','create_and_update_payment', 10);//

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(XERO_MODULE_NAME, [XERO_MODULE_NAME]);


function xero_module_init_menu_items()
{

	if(has_permission('xero','','view')){
		$CI = &get_instance();

		$CI->app_menu->add_sidebar_menu_item('xero', [
			'name'     => _l('xero'), // The name if the item
			'collapse' => true, // Indicates that this item will have submitems
			'position' => 11, // The menu position
			'icon'     => 'fa fa-dollar', // Font awesome icon
		]);
		
		$CI->app_menu->add_sidebar_children_item('xero', [
			'slug'     => 'xero-config', // Required ID/slug UNIQUE for the child menu
			'name'     => _l('xero_configuration'), // The name if the item
			'href'     => admin_url('xero'),
			'position' => 5,
		   
		   
		]);

    }
}

function sync_invoice_data_with_xero($id){
    $CI = &get_instance();
    $CI->load->library(XERO_MODULE_NAME . '/' . 'xeroAPIWrapper');
    $xeroWrapper = new XeroAPIWrapper();
    $val = $xeroWrapper->createInvoice($id);
    last_insert_invoice_id($val);
    return true;
}

function last_insert_invoice_id($val) {
    $CI = &get_instance();
    $CI->load->library(XERO_MODULE_NAME . '/' . 'xeroAPIWrapper');
    $xeroWrapper = new XeroAPIWrapper();
    $invoice_array = $val;
    $xeroWrapper->updateInvoiceWithId($invoice_array['perfex_invoice_id'], $invoice_array['xero_invoice_id'], $invoice_array['data']);
    return true;
}



function create_and_update_payment($data) {
    $data_start = $data;
    $CI = &get_instance();
    $CI->load->library(XERO_MODULE_NAME . '/' . 'xeroAPIWrapper');
    $xeroWrapper = new XeroAPIWrapper();
    $xeroWrapper->createXeroAccountForTransfer($data);
    return $data_start;

}