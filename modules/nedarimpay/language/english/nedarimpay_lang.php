<?php defined('BASEPATH') or exit('No direct script access allowed');

// ── Module & Menu ────────────────────────────────────────────────────────────
$lang['nedarimpay_menu_title']          = 'NedarimPay';
$lang['nedarimpay_dashboard']           = 'NedarimPay Dashboard';
$lang['nedarimpay_transactions']        = 'Transactions';
$lang['nedarimpay_manual_charge']       = 'Manual Charge';
$lang['nedarimpay_settings']            = 'NedarimPay Settings';

// ── Dashboard KPIs ────────────────────────────────────────────────────────────
$lang['nedarimpay_total_transactions']  = 'Total Processed';
$lang['nedarimpay_student_volume']      = 'Student Payments';
$lang['nedarimpay_donation_volume']     = 'Donation Volume';
$lang['nedarimpay_standing_orders']     = 'Standing Orders (HK)';
$lang['nedarimpay_pending_alert']       = '%d transactions are pending processing.';
$lang['nedarimpay_failed_alert']        = '%d transactions failed.';
$lang['nedarimpay_view_failed']         = 'View Failed';
$lang['nedarimpay_quick_actions']       = 'Quick Actions';
$lang['nedarimpay_recent_transactions'] = 'Recent Transactions';
$lang['nedarimpay_view_all_transactions'] = 'View All Transactions';
$lang['nedarimpay_no_transactions_yet'] = 'No transactions received yet.';

// ── Receipt Types ─────────────────────────────────────────────────────────────
$lang['nedarimpay_type_student']        = 'Student';
$lang['nedarimpay_type_donation']       = 'Donation';
$lang['nedarimpay_receipt_series']      = 'Receipt Number Series';
$lang['nedarimpay_series_student_title']= 'Student Series';
$lang['nedarimpay_series_student_desc'] = 'Used for monthly tuition, travel fees, and all other student payments.';
$lang['nedarimpay_series_donation_title']= 'Donation Series';
$lang['nedarimpay_series_donation_desc']= 'Completely separate number series for donors. Different email template.';
$lang['nedarimpay_prefix']              = 'Prefix';
$lang['nedarimpay_series_warning']      = 'Warning: Changing prefixes after production use will break number continuity.';
$lang['nedarimpay_student_payment']     = 'Student Payment';
$lang['nedarimpay_donation_payment']    = 'Donation';

// ── Transaction Table Columns ─────────────────────────────────────────────────
$lang['nedarimpay_receipt_number']      = 'Receipt #';
$lang['nedarimpay_client_name']         = 'Client Name';
$lang['nedarimpay_type']                = 'Type';
$lang['nedarimpay_amount']              = 'Amount';
$lang['nedarimpay_email']               = 'Email';
$lang['nedarimpay_phone']               = 'Phone';
$lang['nedarimpay_email_sent']          = 'Email Sent';
$lang['nedarimpay_date']                = 'Date';
$lang['nedarimpay_status_col']          = 'Status';
$lang['nedarimpay_transaction_type']    = 'TX Type';
$lang['nedarimpay_tashloumim']          = 'Installments';
$lang['nedarimpay_groupe']              = 'Groupe';
$lang['nedarimpay_comments']            = 'Comments';
$lang['nedarimpay_card_last_4']         = 'Card Last 4';
$lang['nedarimpay_confirmation']        = 'Confirmation #';
$lang['nedarimpay_shovar']              = 'Shovar';
$lang['nedarimpay_transaction_time']    = 'Transaction Time';
$lang['nedarimpay_raw_payload']         = 'Raw Payload (Debug)';
$lang['nedarimpay_transaction_info']    = 'Transaction Details';
$lang['nedarimpay_transaction_id_label']= 'Nedarim Transaction ID';
$lang['nedarimpay_transaction_detail']  = 'Transaction Detail';
$lang['nedarimpay_transaction_not_found']= 'Transaction not found.';

// ── Status Labels ─────────────────────────────────────────────────────────────
$lang['nedarimpay_status_processed']    = 'Processed';
$lang['nedarimpay_status_pending']      = 'Pending';
$lang['nedarimpay_status_failed']       = 'Failed';
$lang['nedarimpay_status_duplicate']    = 'Duplicate';

// ── Filters / Search ─────────────────────────────────────────────────────────
$lang['nedarimpay_filter_type']         = 'Type';
$lang['nedarimpay_filter_status']       = 'Status';
$lang['nedarimpay_date_from']           = 'From';
$lang['nedarimpay_date_to']             = 'To';
$lang['nedarimpay_all_types']           = 'All Types';
$lang['nedarimpay_all_statuses']        = 'All Statuses';
$lang['nedarimpay_search_placeholder']  = 'Name / Email / Receipt #';
$lang['nedarimpay_no_transactions_found'] = 'No transactions match your filters.';

// ── Settings ──────────────────────────────────────────────────────────────────
$lang['nedarimpay_credentials']         = 'Nedarim Plus Credentials';
$lang['nedarimpay_mosad_number']        = 'Institution Number (Mosad)';
$lang['nedarimpay_mosad_help']          = '7-digit institution number from Nedarim Plus.';
$lang['nedarimpay_api_valid']           = 'API Valid Token';
$lang['nedarimpay_api_key']             = 'Full API Key / URL';
$lang['nedarimpay_api_key_help']        = 'Full API key URL from the Nedarim Plus office (from the shared link).';
$lang['nedarimpay_payment_mode']        = 'Payment Mode in Perfex';
$lang['nedarimpay_select_payment_mode'] = '— Select —';
$lang['nedarimpay_client_match_field']  = 'Auto-Match Client By';
$lang['nedarimpay_match_field_help']    = 'Field used to find the Perfex client from incoming webhook data.';
$lang['nedarimpay_groupe_filter']       = 'Groupe Value';
$lang['nedarimpay_groupe_filter_help']  = 'Nedarim Groupe value that identifies this type. Leave empty for auto-detection.';

// ── Webhook ───────────────────────────────────────────────────────────────────
$lang['nedarimpay_webhook_url_title']   = 'Webhook URL';
$lang['nedarimpay_webhook_note']        = 'Send this URL to Nedarim Plus support:';
$lang['nedarimpay_webhook_instruction'] = 'Ask them to configure it for both regular transactions AND standing orders (הוראת קבע).';
$lang['nedarimpay_webhook_both_types']  = 'This single endpoint handles both regular payments and standing-order setups.';
$lang['nedarimpay_copy']                = 'Copy';
$lang['nedarimpay_copied']              = 'Copied to clipboard!';

// ── Email Templates ───────────────────────────────────────────────────────────
$lang['nedarimpay_email_templates']     = 'Email Templates';
$lang['nedarimpay_email_subject']       = 'Subject';
$lang['nedarimpay_email_body']          = 'Body (HTML allowed)';
$lang['nedarimpay_email_placeholders']  = 'Available placeholders';
$lang['nedarimpay_view_receipt']        = 'View Your Receipt';

// ── Email Actions ─────────────────────────────────────────────────────────────
$lang['nedarimpay_email_status']        = 'Email Status';
$lang['nedarimpay_email_sent_ok']       = 'Email sent successfully';
$lang['nedarimpay_email_not_sent']      = 'Email not sent';
$lang['nedarimpay_resend_email']        = 'Resend Email';
$lang['nedarimpay_email_resent']        = 'Receipt email resent successfully.';
$lang['nedarimpay_email_resend_failed'] = 'Failed to resend email.';

// ── Invoice / Actions ─────────────────────────────────────────────────────────
$lang['nedarimpay_view_invoice']        = 'View Invoice';
$lang['nedarimpay_view_detail']         = 'View Detail';
$lang['nedarimpay_actions']             = 'Actions';
$lang['nedarimpay_error']               = 'Error';

// ── Manual Charge ─────────────────────────────────────────────────────────────
$lang['nedarimpay_charge_form_title']   = 'Push Charge to Nedarim Plus';
$lang['nedarimpay_charge_info']         = 'This charge will be transmitted to the customer\'s card in Nedarim Plus and bundled into their next monthly billing line.';
$lang['nedarimpay_perfex_client']       = 'Perfex Client (optional)';
$lang['nedarimpay_select_client']       = '— Select Perfex Client —';
$lang['nedarimpay_nedarim_client_id']   = 'Nedarim Client ID';
$lang['nedarimpay_nedarim_client_id_placeholder'] = 'Nedarim Plus ClientId';
$lang['nedarimpay_nedarim_client_id_help'] = 'The ClientId from the customer\'s card in Nedarim Plus.';
$lang['nedarimpay_currency']            = 'Currency';
$lang['nedarimpay_tashlumim']           = 'Installments';
$lang['nedarimpay_tashlumim_help']      = 'Number of installments (1 for single charge).';
$lang['nedarimpay_charge_type']         = 'Charge Type';
$lang['nedarimpay_charge_tuition']      = 'Monthly Tuition';
$lang['nedarimpay_charge_travel']       = 'Travel / Trip Fees';
$lang['nedarimpay_charge_other']        = 'Other Student Fee';
$lang['nedarimpay_charge_donation']     = 'Donation';
$lang['nedarimpay_receipt_type']        = 'Receipt Series';
$lang['nedarimpay_groupe_placeholder']  = 'e.g. tuition';
$lang['nedarimpay_description']         = 'Description';
$lang['nedarimpay_description_placeholder'] = 'e.g. Trip to Jerusalem - May 2026';
$lang['nedarimpay_send_charge']         = 'Send Charge';
$lang['nedarimpay_confirm_charge']      = 'Are you sure you want to push this charge to Nedarim Plus?';
$lang['nedarimpay_charge_sent_success'] = 'Charge sent to Nedarim Plus successfully.';
$lang['nedarimpay_charge_sent_error']   = 'Failed to send charge to Nedarim Plus';
$lang['nedarimpay_charge_validation_error'] = 'Please fill all required fields with valid values.';

// ── Payment Gateway (client invoice pay) ──────────────────────────────────────
$lang['nedarimpay_gw_mosad_number']              = 'Institution Number (Mosad)';
$lang['nedarimpay_gw_api_valid']                 = 'API Valid Token';
$lang['nedarimpay_gw_iframe_url']                = 'iFrame Base URL';
$lang['nedarimpay_gw_default_receipt_type']      = 'Default Receipt Series for Invoice Payments';

// Pay page (client-facing iFrame)
$lang['nedarimpay_pay_title']                    = 'Pay Invoice %s';
$lang['nedarimpay_pay_invoice']                  = 'Pay Invoice %s';
$lang['nedarimpay_order_summary']                = 'Order Summary';
$lang['nedarimpay_amount_due']                   = 'Amount Due';
$lang['nedarimpay_enter_payment_details']        = 'Enter Payment Details';
$lang['nedarimpay_loading_payment_form']         = 'Loading secure payment form…';
$lang['nedarimpay_payment_form']                 = 'NedarimPay Payment Form';
$lang['nedarimpay_secure_payment_note']          = 'Secured by Nedarim Plus. Your card details are never stored on our servers.';
$lang['nedarimpay_back_to_invoice']              = 'Back to Invoice';
$lang['nedarimpay_powered_by']                   = 'Powered by';

// Verify / result messages
$lang['nedarimpay_payment_processing']           = 'Your payment is being processed. You will receive a receipt by email shortly.';
$lang['nedarimpay_payment_failed_or_cancelled']  = 'The payment was not completed. Please try again or contact us.';
$lang['invoice_already_paid']                    = 'This invoice is already paid.';

// Manual charge how-to sidebar
$lang['nedarimpay_charge_howto_title']           = 'How It Works';
$lang['nedarimpay_charge_step1']                 = 'Enter the Nedarim Plus ClientId of the customer.';
$lang['nedarimpay_charge_step2']                 = 'Set the amount, currency, and charge type.';
$lang['nedarimpay_charge_step3']                 = 'Click Send — the charge is transmitted to Nedarim Plus.';
$lang['nedarimpay_charge_step4']                 = 'Nedarim Plus adds it to the customer\'s next monthly billing cycle.';
$lang['nedarimpay_receipt_series_reminder']      = 'Receipt Series';
$lang['nedarimpay_receipt_type_auto_help']       = 'Auto-set by Charge Type. Can be overridden manually.';

// Settings extra
$lang['nedarimpay_config_status']                = 'Configuration Status';
$lang['nedarimpay_api_docs']                     = 'API Documentation';
$lang['nedarimpay_preview']                      = 'Preview';
$lang['nedarimpay_toggle']                       = 'Toggle';
$lang['nedarimpay_ids_panel']                    = 'Nedarim / System IDs';
$lang['nedarimpay_confirm_resend']               = 'Resend the receipt email to this client?';

// ── Setup Guide ───────────────────────────────────────────────────────────────
$lang['nedarimpay_setup_guide_title']            = 'Setup Guide';
$lang['nedarimpay_setup_guide_intro']            = 'Follow these steps once to connect this system to your Nedarim Plus account. The whole process takes about 5 minutes.';

$lang['nedarimpay_setup_step1_title']            = 'Get Your Credentials from Nedarim Plus';
$lang['nedarimpay_setup_step1_body']             = 'Contact Nedarim Plus support and ask for: (1) your <strong>Institution Number (Mosad)</strong> — a 7-digit number, (2) your <strong>ApiValid Token</strong>, and (3) the <strong>full API Key URL</strong> they send as a shared link. Keep these ready before continuing.';

$lang['nedarimpay_setup_step2_title']            = 'Enter Credentials';
$lang['nedarimpay_setup_step2_body']             = 'Paste the Institution Number, ApiValid Token, and API Key URL into the <em>Nedarim Plus Credentials</em> panel on the left. All three fields must be filled for payments to work.';

$lang['nedarimpay_setup_step3_title']            = 'Send the Webhook URL to Nedarim Plus';
$lang['nedarimpay_setup_step3_body']             = 'Copy the URL shown in the <em>Webhook URL</em> box at the top-left and send it to Nedarim Plus support. Ask them to register it for <strong>both regular transactions AND standing orders (הוראת קבע)</strong>. Without this step no payments will arrive in the system.';

$lang['nedarimpay_setup_step4_title']            = 'Select a Payment Mode';
$lang['nedarimpay_setup_step4_body']             = 'Choose which Perfex payment mode (e.g. "Credit Card" or "Nedarim Plus") incoming payments should be recorded under. If you have not created one yet, go to <strong>Settings → Payment Modes</strong> first, then return here.';

$lang['nedarimpay_setup_step5_title']            = 'Set Receipt Number Prefixes';
$lang['nedarimpay_setup_step5_body']             = 'Set a short prefix for each receipt series — <strong>T</strong> for student payments and <strong>D</strong> for donations is a common choice. Receipts will be numbered <code>T-00001</code>, <code>T-00002</code>, etc. <em>Do not change these after you have started receiving real payments.</em>';

$lang['nedarimpay_setup_step6_title']            = 'Save & You Are Done';
$lang['nedarimpay_setup_step6_body']             = 'Click the <strong>Save</strong> button. The configuration status panel will turn green for each field that is correctly filled. When Institution Number, ApiValid Token, and Payment Mode all show a green tick the module is ready to receive payments.';

$lang['nedarimpay_setup_tip_title']              = 'Helpful Tips';
$lang['nedarimpay_setup_tip_email']              = '<strong>Email templates</strong> are optional — the module works without them, but configuring them lets clients receive a branded receipt email after each payment.';
$lang['nedarimpay_setup_tip_groupe']             = '<strong>Groupe value</strong> is optional. Leave it empty for automatic detection. Only fill it in if Nedarim Plus sends a specific group code that identifies student vs. donation payments.';
$lang['nedarimpay_setup_tip_match']              = '<strong>Auto-Match Client By</strong> controls how an incoming webhook is linked to a Perfex client record. <em>Email</em> is the most reliable option if your clients have email addresses on file.';
$lang['nedarimpay_setup_need_help']              = 'Need help? Contact your system administrator or the Nedarim Plus support team.';

// ── Invoice Payment Button & Modal ────────────────────────────────────────────
$lang['nedarimpay_record_payment_btn']           = 'Nedarim Pay';
$lang['nedarimpay_record_payment_title']         = 'Record Nedarim Payment';
$lang['nedarimpay_record_payment_invoice']       = 'Invoice';
$lang['nedarimpay_record_payment_amount']        = 'Amount';
$lang['nedarimpay_record_payment_date']          = 'Payment Date';
$lang['nedarimpay_record_payment_receipt_type']  = 'Receipt Series';
$lang['nedarimpay_record_payment_transaction_id']= 'Nedarim Transaction ID';
$lang['nedarimpay_record_payment_transaction_id_help'] = 'Optional — paste the Transaction ID from Nedarim Plus if available.';
$lang['nedarimpay_record_payment_note']          = 'Internal Note';
$lang['nedarimpay_record_payment_submit']        = 'Record Payment';
$lang['nedarimpay_record_payment_success']       = 'Payment recorded successfully via Nedarim Pay.';
$lang['nedarimpay_record_payment_fail']          = 'Failed to record payment. Please try again.';
$lang['nedarimpay_not_configured_title']         = 'Nedarim Pay is Not Configured';
$lang['nedarimpay_not_configured_body']          = 'Before you can record a Nedarim payment, you must complete the module setup: fill in the Institution Number, API Token, and select a Payment Mode.';
$lang['nedarimpay_not_configured_link']          = 'Open Nedarim Pay Settings';
