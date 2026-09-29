<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*Credits*/
/*----------------------------------------
Xero for the xero-php-oauth2
------------------------------------------- */

//Xero Config
add_option('xero_client_id', '');
add_option('xero_client_secret', '');
add_option('xero_active', '0');
add_option('xero_auto_operations_hour', '21');
add_option('xero_default_asset_account', '');
add_option('xero_default_income_account', '');
add_option('xero_default_payment_account', '');
add_option('xero_manual_cron_sync_enabled', '0');
add_option('xero_auto_cron_sync_clients_enabled', '1');
add_option('xero_auto_cron_sync_items_enabled', '1');
add_option('xero_auto_cron_sync_taxes_enabled', '1');
add_option('xero_auto_cron_sync_invoices_enabled', '1');
add_option('xero_auto_cron_sync_payments_enabled', '1');
add_option('xero_auto_cron_sync_expenses_enabled', '1');
add_option('xero_sync_payments_down_enabled', '0');
add_option('xero_sync_invoice_ref_with_project_details', '0');

//Xero Access Params
add_option('xero_access_token', '');
add_option('xero_refresh_token', '');
add_option('xero_expiration_date', '');
add_option('xero_is_expired', '');
add_option('xero_realmid', '');
add_option('xero_refresh_token_expires_in', '');
add_option('xero_tenant_id', '');
add_option('xero_tenant_name', '');

$CI = &get_instance();

// Xero Chart Of Accounts
if (!$CI->db->table_exists(db_prefix() . 'xero_chart_accounts')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "xero_chart_accounts` (
        `chart_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	`Code` varchar(100) NOT NULL,
		`Name` varchar(100) NOT NULL,
		`AccountID` varchar(100) NOT NULL,
		`Type` varchar(100) NOT NULL,
		`BankAccountNumber` varchar(100) NOT NULL,
		`Status` varchar(100) NOT NULL,
		`BankAccountType` varchar(100) NOT NULL,
		`CurrencyCode` varchar(100) NOT NULL,
		`TaxType` varchar(100) NOT NULL,
		`EnablePaymentsToAccount` bit DEFAULT 0,
		`ShowInExpenseClaims` bit DEFAULT 0,
		`Class` varchar(100) NOT NULL,
		`ReportingCode` varchar(100) NOT NULL,
		`ReportingCodeName` varchar(100) NOT NULL,
		`HasAttachments` bit DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Xero Items
if (!$CI->db->table_exists(db_prefix() . 'xero_items')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "xero_items` (
		  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  `item_id` int(11) NOT NULL UNIQUE KEY,
		  `xero_Id` varchar(100) NOT NULL UNIQUE KEY
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists("xero_incomeAccountRef", db_prefix() . 'items')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'items' . " ADD  xero_incomeAccountRef varchar(100) DEFAULT NULL");
}

// Xero Client Groups
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'customers_groups')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'customers_groups' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

// Xero Clients
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'clients')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'clients' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

// Xero Invoices
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'invoices')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'invoices' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

// Xero Payment Methods
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'payment_modes')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}
if (!$CI->db->field_exists("xero_accountNumber", db_prefix() . 'payment_modes')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " ADD xero_accountNumber varchar(100) DEFAULT NULL");
	$CI->db->query("ALTER TABLE " . db_prefix() . 'payment_modes' . " ADD xero_hasSortCode TINYINT(1) DEFAULT 0");
}

// Xero Payment Record
if (!$CI->db->field_exists("xero_payment_Id", db_prefix() . 'invoicepaymentrecords')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'invoicepaymentrecords' . " ADD xero_payment_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

// Xero Expenses
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'expenses')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

if (!$CI->db->field_exists("xero_paymentAccountRef", db_prefix() . 'expenses')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses' . " ADD xero_paymentAccountRef varchar(100) DEFAULT NULL");
}

// Xero Expense Categories
if (!$CI->db->field_exists("xero_Id", db_prefix() . 'expenses_categories')) {
	$CI->db->query("ALTER TABLE " . db_prefix() . 'expenses_categories' . " ADD xero_Id varchar(100) NOT NULL DEFAULT '" . XERO_DEFAULT_ID . "'");
}

if (!$CI->db->table_exists(db_prefix() . 'xero_taxes')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "xero_taxes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  `tax_id` int(11) NOT NULL UNIQUE KEY,
		  `xero_TaxType` varchar(100) DEFAULT NULL,
		  `xero_TaxName` varchar(100) DEFAULT NULL,
		  `sync_xero` bit NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

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
$searchF1 = "'selected_by_default' => \$data['selected_by_default'],";
$replaceW1 = "'selected_by_default' => \$data['selected_by_default'],\n'xero_accountNumber'  => \$data['xero_accountNumber'],\n'xero_hasSortCode'    => \$data['xero_hasSortCode'],";
xero_modFile($fname1, $searchF1, $replaceW1);