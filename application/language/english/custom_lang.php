<?php

# Sales Terminology Toggle Setting
$lang['settings_show_adjustment_as_retainer']     = 'Enable Retainer & Sales Terminology Overrides';
$lang['settings_show_adjustment_as_retainer_help'] = 'If enabled, terminology will change as follows: <br>• <b>Proposal</b> ➔ <b>Sales Quotation</b><br>• <b>Estimate</b> ➔ <b>Sales Order</b><br>• <b>Adjustment</b> ➔ <b>Retainer</b><br>This applies to the entire sales section, including PDFs and customer portal.';

# Extra settings and helpers (Always needed)
$lang['settings_sales_company_info_note'] = 'This information will be displayed on invoices/sales orders/payments and other PDF documents where company info is required';
$lang['show_invoice_estimate_status_on_pdf'] = 'Show invoice/sales order status on PDF';
$lang['settings_estimate_auto_convert_to_invoice_on_client_accept'] = 'Auto convert the sales order to invoice after client accept';
$lang['settings_exclude_estimate_from_client_area_with_draft_status'] = 'Exclude sales orders with draft status from customers area';

# Shipping & Other Expenses Toggle
$lang['settings_show_shipping_on_sales']      = 'Show Shipping & Other Expenses on Sales Documents';
$lang['settings_show_shipping_on_sales_help'] = 'If enabled, Shipping Expense and Other Expenses fields will be visible when creating/editing Estimates (Sales Orders) and Proposals (Sales Quotations).';

# Proposal Table Headings
$lang['proposal_table_item_heading']      = 'Item';
$lang['proposal_table_quantity_heading']  = 'Qty';
$lang['proposal_table_rate_heading']      = 'Rate';
$lang['proposal_table_tax_heading']       = 'Tax';
$lang['proposal_table_amount_heading']    = 'Amount';
$lang['Discount']                         = 'Discount';
$lang['Tax Amount']                       = 'Tax Amount';

# Navigation & General
$lang['clients_nav_proposals']                    = 'Sales Quotations';
$lang['customer_permission_proposal']             = 'Sales Quotations';
$lang['bulk_export_pdf_proposals']                = 'Sales Quotations';

# Proposals / Sales Quotations General
$lang['proposal']                                 = 'Sales Quotation';
$lang['proposal_lowercase']                       = 'sales quotation';
$lang['proposals']                                = 'Sales Quotations';
$lang['proposals_lowercase']                      = 'sales quotations';
$lang['new_proposal']                             = 'New Sales Quotation';
$lang['proposal_view']                            = 'View Sales Quotation';
$lang['proposal_copy']                            = 'Copy Sales Quotation';
$lang['proposal_edit']                            = 'Edit Sales Quotation';
$lang['proposal_delete']                          = 'Delete Sales Quotation';
$lang['proposal_send_to_email']                   = 'Send to Email';
$lang['proposal_send_to_email_title']             = 'Send Sales Quotation to Email';
$lang['proposal_sent_to_email_success']           = 'Sales Quotation sent to email successfully';
$lang['proposal_sent_to_email_fail']              = 'Failed to send Sales Quotation to email';
$lang['proposal_copy_fail']                       = 'Failed to copy Sales Quotation';
$lang['proposal_copy_success']                    = 'Sales Quotation copied successfully';
$lang['proposal_status_changed_success']          = 'Sales Quotation status changed successfully';
$lang['proposal_status_changed_fail']             = 'Failed to change Sales Quotation status';
$lang['proposal_convert']                         = 'Convert';
$lang['proposal_convert_to_estimate']             = 'Convert to Sales Order';
$lang['proposal_convert_to_invoice']              = 'Convert to Invoice';
$lang['proposal_convert_not_related_help']        = 'The Sales Quotation needs to be related to customer in order to convert to %s';
$lang['proposal_converted_to_estimate_success']   = 'Sales Quotation converted to Sales Order successfully';
$lang['proposal_converted_to_invoice_success']    = 'Sales Quotation converted to invoice successfully';
$lang['proposal_converted_to_estimate_fail']      = 'Failed to convert Sales Quotation to Sales Order';
$lang['proposal_converted_to_invoice_fail']       = 'Failed to convert Sales Quotation to invoice';
$lang['proposal_save']                            = 'Save Sales Quotation';
$lang['proposal_information']                     = 'Sales Quotation Information';

# Dashboard & Reports
$lang['home_proposal_overview']                   = 'Sales Quotation overview';
$lang['proposals_report']                         = 'Sales Quotations Report';
$lang['proposals_pipeline']                       = 'Sales Quotations Pipeline';
$lang['proposals_sort_proposal_date']             = 'Sales Quotation Date';
$lang['customer_have_proposals_by']               = 'Contains Sales Quotations by status %s';
$lang['not_lead_activity_created_proposal']       = 'Created new Sales Quotation - %s';
$lang['not_customer_viewed_proposal']             = 'A Sales Quotation with number %s has been viewed';

# Calendar & Reminders
$lang['show_proposals_on_calendar']               = 'Sales Quotations';
$lang['show_proposal_reminders_on_calendar']      = 'Sales Quotation Reminders';
$lang['calendar_proposal_reminder']               = 'Sales Quotation Reminder';
$lang['proposal_reminders']                       = 'Reminders';
$lang['proposal_set_reminder_title']              = 'Set Sales Quotation Reminder';
$lang['proposal_due_after']                       = 'Sales Quotation Due After (days)';

# Emails & Notifications
$lang['email_template_proposals_fields_heading']  = 'Sales Quotation';
$lang['proposal_warning_email_change']            = 'Email changed for %s. This %s is linked to Sales Quotation/s. Do you want to update all Sales Quotation emails linked to %s?';
$lang['update_proposal_email_yes']                = 'Yes update all linked emails.';
$lang['proposals_emails_updated']                 = 'All Sales Quotation emails linked to this %s updated to %s';
$lang['not_proposal_assigned_to_you']             = 'Sales Quotation assigned to you - %s ...';
$lang['not_proposal_comment_from_client']         = 'New comment from customer on Sales Quotation %s ...';
$lang['not_proposal_proposal_accepted']           = 'Sales Quotation Accepted - %s';
$lang['not_proposal_proposal_declined']           = 'Sales Quotation Declined - %s';

# Settings & Misc
$lang['proposal_number_prefix']                   = 'Sales Quotation Number Prefix';
$lang['allow_staff_view_proposals_assigned']      = 'Allow staff members to view Sales Quotations where they are assigned to';
$lang['exclude_proposal_from_client_area_with_draft_status'] = 'Exclude Sales Quotations with draft status from customers area';
$lang['proposal_not_found']                       = 'Sales Quotation not found';
$lang['no_proposals_found']                       = 'No Sales Quotations Found';
$lang['sync_proposals_up_to_date']                = 'All Sales Quotations are up to date, nothing to sync';
$lang['proposal_sync_1_info']                     = 'All Sales Quotation data is stored separately for each Sales Quotation after creation. Updating the %s info won\'t affect previous created Sales Quotations for this %s.';
$lang['proposal_sync_2_info']                     = 'If you recently updated your %s info you can sync all new data to associated Sales Quotations. Here is a list of fields you can sync.';
$lang['proposal_files']                           = 'Sales Quotation Files';
$lang['proposal_info_format']                     = 'Sales Quotation Info Format (PDF and HTML)';
$lang['include_proposal_items_merge_field_help']  = 'Include Sales Quotation items with merge field anywhere in Sales Quotation content as %s';

# PDF / Table Headings
$lang['proposal_table_item_heading']              = 'Description';
$lang['proposal_table_quantity_heading']          = 'Qty';
$lang['proposal_table_rate_heading']              = 'Rate';
$lang['proposal_table_tax_heading']               = 'Tax';
$lang['proposal_table_amount_heading']            = 'Amount';
$lang['proposal_subtotal']                        = 'Sub-Total';

# Avg Purchase Aging Report
$lang['avg_purchase_aging']             = 'Average Purchase Aging';
$lang['avg_age_days']                   = 'Avg Age (Days)';
$lang['risk_level']                     = 'Risk Level';
$lang['inventory_turnover']             = 'Inventory Turnover';
$lang['aging_buckets']                  = 'Aging Buckets';
$lang['total_items']                    = 'Total Items';
$lang['total_value']                    = 'Total Value';
$lang['total_quantity']                 = 'Total Quantity';
$lang['risk_distribution']              = 'Risk Distribution';
$lang['aging_buckets_distribution']     = 'Aging Buckets Distribution';
$lang['risk_level_distribution']        = 'Risk Level Distribution';
$lang['aging_trend_analysis']           = 'Aging Trend Analysis';
$lang['detailed_aging_report']          = 'Detailed Aging Report';
$lang['avg_value_per_unit']             = 'Avg Value / Unit';
$lang['transaction_type']               = 'Transaction Type';
$lang['over']                           = 'Over';
$lang['days']                           = 'Days';
$lang['both_sales_and_purchases']       = 'Both Sales & Purchases';
$lang['no_data_found_for_selected_criteria'] = 'No data found for selected criteria';
$lang['generate_report_to_view_data']   = 'Please generate the report to view data';