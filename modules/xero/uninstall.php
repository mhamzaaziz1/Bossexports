<?php

defined('BASEPATH') or exit('No direct script access allowed');

delete_option('xero_client_id');
delete_option('xero_client_secret');
delete_option('xero_active');
delete_option('xero_auto_operations_hour');
delete_option('xero_default_asset_account');
delete_option('xero_default_income_account');
delete_option('xero_default_payment_account');
delete_option('xero_manual_cron_sync_enabled');
delete_option('xero_auto_cron_sync_clients_enabled');
delete_option('xero_auto_cron_sync_items_enabled');
delete_option('xero_auto_cron_sync_taxes_enabled');
delete_option('xero_auto_cron_sync_invoices_enabled');
delete_option('xero_auto_cron_sync_payments_enabled');
delete_option('xero_auto_cron_sync_expenses_enabled');
delete_option('xero_sync_payments_down_enabled');
delete_option('xero_sync_invoice_ref_with_project_details');

//Xero Access Params
delete_option('xero_access_token');
delete_option('xero_refresh_token');
delete_option('xero_expiration_date');
delete_option('xero_is_expired');
delete_option('xero_realmid');
delete_option('xero_refresh_token_expires_in');
delete_option('xero_tenant_id');
delete_option('xero_tenant_name');

$CI = &get_instance();

if ($CI->db->table_exists(db_prefix() . 'xero_chart_accounts')) {
	$CI->db->query('DROP TABLE `' . db_prefix() . 'xero_chart_accounts`;');
}

// Xero Items
if ($CI->db->table_exists(db_prefix() . 'xero_items')) {
	$CI->db->query('DROP TABLE `' . db_prefix() . 'xero_items`;');
}

if ($CI->db->field_exists("xero_incomeAccountRef", db_prefix() . 'items')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'items' . " DROP COLUMN xero_incomeAccountRef");
}

// Xero Client Groups
if ($CI->db->field_exists("xero_Id", db_prefix() . 'customers_groups')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'customers_groups' . " DROP COLUMN xero_Id");
}

// Xero Clients
if ($CI->db->field_exists("xero_Id", db_prefix() . 'clients')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'clients' . " DROP COLUMN xero_Id");
}

// Xero Invoices
if ($CI->db->field_exists("xero_Id", db_prefix() . 'invoices')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'invoices' . " DROP COLUMN xero_Id");
}

// Xero Payment Methods
if ($CI->db->field_exists("xero_Id", db_prefix() . 'payment_modes')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " DROP COLUMN xero_Id");
}

if ($CI->db->field_exists("xero_accountNumber", db_prefix() . 'payment_modes')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " DROP COLUMN  xero_accountNumber");
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " DROP COLUMN  xero_hasSortCode");
}

// Xero Payment Record
if ($CI->db->field_exists("xero_payment_Id", db_prefix() . 'invoicepaymentrecords')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'invoicepaymentrecords' . " DROP COLUMN xero_payment_Id");
}

// Xero Expenses
if ($CI->db->field_exists("xero_Id", db_prefix() . 'expenses')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses' . " DROP COLUMN xero_Id");
}

if (!$CI->db->field_exists("xero_paymentAccountRef", db_prefix() . 'expenses')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses' . " DROP COLUMN xero_paymentAccountRef");
}

// Xero Expense Categories
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'expenses_categories')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses_categories' . " DROP COLUMN xero_Id");
}

// Xero Taxes
if ($CI->db->table_exists(db_prefix() . 'xero_taxes')) {
	$CI->db->query('DROP TABLE `' . db_prefix() . 'xero_taxes`;');
}

/**
 * Modify core files
 * @return [type] [description]
 */
function xero_modFile($fname, $searchF, $replaceW) {
	$fhandle = fopen($fname, "r");
	$content = fread($fhandle, filesize($fname));
	if (strstr($content, $searchF)) {
		$content = str_replace($searchF, $replaceW, $content);
		$fhandle = fopen($fname, "w");
		fwrite($fhandle, $content);
	}
	fclose($fhandle);
	return true;
}

$fname1 = APPPATH . "models/Payment_modes_model.php";
$replaceF1 = "'selected_by_default' => \$data['selected_by_default'],\n'xero_accountNumber'  => \$data['xero_accountNumber'],\n'xero_hasSortCode'    => \$data['xero_hasSortCode'],";
$searchW1 = "'selected_by_default' => \$data['selected_by_default'],";
xero_modFile($fname1, $searchF1, $replaceW1);