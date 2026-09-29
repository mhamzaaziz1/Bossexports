<?php

$company_name = empty(get_option('companyname')) ? 'Perfex' : get_option('companyname');

$lang['xero'] = 'Xero® Online';
$lang['xero_short'] = 'Xero®';
$lang['xero_settings'] = 'Xero® Setup';
$lang['xero_active'] = 'Active';
$lang['xero_client_id'] = 'Client ID';
$lang['xero_client_secret'] = 'Client Secret';

$lang['xero_general'] = 'General';
$lang['xero_config'] = '<i class="fa fa-cogs"></i> Config';
$lang['xero_reset_sync'] = '<i class="fa fa-window-restore"></i> Reset';

$lang['xero_reset_sync_help'] = 'This will remove all evidence of any synchronization in Perfex CRM, giving you an opportunity to run a clean synchronization. It is recommended for use if you want to switch from Xero Demo Company to Xero Actual Company Account';

$lang['requires_connection'] = 'Requires Connection';

$lang['xero_invoice_synch'] = 'Invoice Synch';
$lang['xero_hour_of_day_perform_auto_operations_help'] = 'To help you synchronize everything "Chart of Accounts, Customers, Items, Taxes, Invoices, Payments" with xero at a given set time during the day, make sure that the cron job is properly configured in order to use this feature.';

$lang['xero_manual_cron_sync_enabled'] = 'Only Manual Cron Sync Enabled';
$lang['xero_manual_cron_sync_enabled_help'] = 'Due to the xero synchronization taking some time to complete. We recommend leaving this "Inactive" and setting up a CRON Job instance for your system. However, if you dont mind waiting till the synchronization is complete you may "Activate" it.';

$lang['xero_sync_payments_down_enabled'] = 'Sync Payments from ' . $lang['xero'] . ' to ' . $company_name;
$lang['xero_sync_payments_down_enabled_help'] = 'By activating this option, only payments made in ' . $lang['xero'] . ' will be synchronized.';

$lang['xero_sync_invoice_ref_with_project_details'] = 'Sync Project Invoices Ref as Project ID and Name <br>(<small class="text-warning">01-PROJECT-NAME</small>)';
$lang['xero_sync_invoice_ref_with_project_details_help'] = 'Project invoices will sync with Project ID and Name as Invoice Reference';

$lang['xero_auto_cron_sync_clients_enabled'] = 'Auto Sync Clients';
$lang['xero_auto_cron_sync_items_enabled'] = 'Auto Sync Items';
$lang['xero_auto_cron_sync_taxes_enabled'] = 'Auto Sync Taxes';
$lang['xero_auto_cron_sync_invoices_enabled'] = 'Auto Sync Invoices';
$lang['xero_auto_cron_sync_payments_enabled'] = 'Auto Sync Payments';
$lang['xero_auto_cron_sync_expenses_enabled'] = 'Auto Sync Expenses';

$lang['xero_default_income_acc'] = 'Default Income Account';
$lang['xero_default_asset_acc'] = 'Default Asset Account';
$lang['xero_default_payment_acc'] = 'Default Payment Account';

$lang['xero_default_income_acc_help'] = 'This will be set as the default income account for all Items. (IncomeAccountRef)';
$lang['xero_default_asset_acc_help'] = 'This will be the deposit account when a payment has been processed. (DepositToAccountRef)';
$lang['xero_default_payment_acc_help'] = 'This will be the default payment account used when an expense payment is processed.';

$lang['xero_upload_helper'] = $company_name . ' <i class="fa fa-arrow-right"></i> ' . $lang['xero'];
$lang['xero_download_helper'] = $lang['xero'] . ' <i class="fa fa-arrow-right"></i> ' . $company_name;
$lang['xero_upload_helper_2'] = $company_name . ' to ' . $lang['xero'];
$lang['xero_download_helper_2'] = $lang['xero'] . ' to ' . $company_name;
$lang['xero_sync_helper'] = 'Here we apply one-way sync from Perfex to ' . $lang['xero'] . ' or vice-versa. The following symbols will guide you as you run a manual sync.<br>
<strong><i class="text-info fa fa-upload"></i> for ' . $lang['xero_upload_helper'] . '</strong>	<br>
<strong><i class="text-warning fa fa-download"></i> for ' . $lang['xero_download_helper'] . '</strong>';

//tax agency
$lang['xero_tax_agency'] = "Tax Agency";
$lang['xero_new_tax_agency'] = "New Tax Agency";
$lang['tax_agency_add_title'] = "Add Tax Agency";
$lang['tax_agency_edit_title'] = "Edit Tax Agency";

//Chart of Accounts
$lang['account_code'] = 'Account Number';
$lang['account_name'] = 'Account Name';
$lang['account_type'] = 'Type';
$lang['acc_ledger_notes'] = 'Notes';
$lang['o/p_balance'] = 'O/P Balance';
$lang['c/l_balance'] = 'C/L Balance';

//items
$lang['income_account'] = 'Income Account <small>(' . $lang['xero'] . ' Account)</small>';

//---------- Responses ---------------//
//Xero Connection
$lang['needs_reconnecting'] = $lang['xero'] . ' needs reconnecting';
$lang['xero_disconnection_success'] = 'Disconnected from ' . _l('xero') . ' successfully';
$lang['xero_disconnection_fail'] = 'Diconnection failed with ' . _l('xero');

//COA
$lang['chat_sync_success'] = 'Chart of Accounts synchronization with ' . $lang['xero'] . ' successful';
$lang['chat_sync_fail'] = 'Chart of Accounts synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly refresh page and try again.';

//Customer
$lang['customer_sync_success'] = 'Customers synchronization with ' . $lang['xero'] . ' successful';
$lang['customer_sync_fail'] = 'Customers synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly refresh page and try again.';
$lang['customer_sync_404'] = 'No customers to synchronize with ' . $lang['xero'];

//Customer Groups
$lang['customer_groups_sync_success'] = 'Customer Groups synchronization with ' . $lang['xero'] . ' successful';
$lang['customer_groups_sync_fail'] = 'Customers Groups synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly refresh page and try again.';

//Taxes
$lang['taxes_sync_success'] = 'Taxes synchronization with ' . $lang['xero'] . ' successful';
$lang['taxes_sync_fail'] = 'Tax Down-synchronization from ' . $lang['xero'] . ' unsuccessful. Kindly refresh page and try again.';
$lang['taxes_sync_404'] = 'No taxes to synchronize with ' . $lang['xero'];

//Items
$lang['items_sync_success'] = 'Items synchronization with ' . $lang['xero'] . ' successful';
$lang['items_sync_fail'] = 'Some items [%s] synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly check on these items and try again.';
$lang['items_sync_404'] = 'No items can synchronize with ' . $lang['xero'] . ', Kindly check whether you have set the "Default Income Account" in the config';

//Invoices
$lang['invoices_sync_success'] = 'Invoices synchronization with ' . $lang['xero'] . ' successful';
$lang['invoices_sync_fail'] = 'Some invoices [%s] synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly check on these Invoices and try again.';
$lang['invoices_sync_404'] = 'No invoices can synchronize with ' . $lang['xero'] . ', Kindly check whether you have set the right configurations.';

//Payments
$lang['payments_sync_success'] = 'Payments synchronization with ' . $lang['xero'] . ' successful';
$lang['payments_sync_fail'] = 'Some payments [%s] synchronization with ' . $lang['xero'] . ' unsuccessful. Kindly check on these payments and try again.';
$lang['payments_sync_404'] = 'No payments can synchronize with ' . $lang['xero'] . ', Kindly check whether you have set the right configurations.';

//Payment Method
$lang['xero_hasSortCode'] = "Allow Sort Code? <small>(" . $lang['xero'] . ")</small>";
$lang['bank_acc_no'] = "Bank Account <small>(" . $lang['xero'] . ")</small>";
$lang['bank_acc_no_with_sort'] = "Sort Code + Bank Account Number <small>(" . $lang['xero'] . ")</small>";
$lang['bank_acc_no_with_sort_help'] = "The Sort Code needs to be a six digit number, merge it with the account number i.e. 123456xxxxxxxxxx";

//Reset Xero
$lang['reset_success'] = 'Reset successful.';
