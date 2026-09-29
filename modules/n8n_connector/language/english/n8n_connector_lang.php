<?php
# English language file for n8n Connector module

// Module name
$lang['n8n_connector'] = 'N8N Connector';
$lang['n8n_webhooks'] = 'Webhooks';
$lang['n8n_webhook'] = 'Webhook';
$lang['n8n_logs'] = 'Webhook Logs';
$lang['n8n_dashboard'] = 'Dashboard';
$lang['n8n_test_webhook'] = 'Test Webhook';
$lang['n8n_workflow_templates'] = 'Workflow Templates';
$lang['n8n_event_config'] = 'Event Configuration';

// General
$lang['n8n_description'] = 'Professional automation integration with n8n-ready webhooks';
$lang['n8n_getting_started'] = 'Getting Started';
$lang['n8n_documentation'] = 'Documentation';

// Webhook fields
$lang['n8n_webhook_name'] = 'Webhook Name';
$lang['n8n_webhook_url'] = 'N8N Webhook URL';
$lang['n8n_webhook_description'] = 'Description';
$lang['n8n_secret_key'] = 'Secret Key';
$lang['n8n_generate_secret'] = 'Generate Random Secret';
$lang['n8n_use_default_secret'] = 'Use Default';
$lang['n8n_enable_signatures'] = 'Enable Webhook Signatures';
$lang['n8n_include_relationships'] = 'Include Relationship Data';
$lang['n8n_track_changes'] = 'Track Changes (Previous/Current Values)';
$lang['n8n_enabled_events'] = 'Enabled Events';
$lang['n8n_custom_headers'] = 'Custom Headers';
$lang['n8n_header_name'] = 'Header Name';
$lang['n8n_header_value'] = 'Header Value';

// Webhook settings
$lang['n8n_retry_enabled'] = 'Enable Automatic Retries';
$lang['n8n_max_retries'] = 'Maximum Retry Attempts';
$lang['n8n_retry_delay'] = 'Retry Delay (seconds)';
$lang['n8n_timeout'] = 'Request Timeout (minutes)';
$lang['n8n_is_active'] = 'Active';
$lang['n8n_is_inactive'] = 'Inactive';

// Event categories
$lang['n8n_events_leads'] = 'Leads';
$lang['n8n_events_customers'] = 'Customers';
$lang['n8n_events_contacts'] = 'Contacts';
$lang['n8n_events_invoices'] = 'Invoices';
$lang['n8n_events_payments'] = 'Payments';
$lang['n8n_events_estimates'] = 'Estimates';
$lang['n8n_events_projects'] = 'Projects';
$lang['n8n_events_tasks'] = 'Tasks';
$lang['n8n_events_tickets'] = 'Tickets';
$lang['n8n_events_proposals'] = 'Proposals';
$lang['n8n_events_contracts'] = 'Contracts';
$lang['n8n_events_expenses'] = 'Expenses';
$lang['n8n_events_staff'] = 'Staff';
$lang['n8n_events_calendar'] = 'Calendar Events';

// Actions
$lang['n8n_add_webhook'] = 'Add New Webhook';
$lang['n8n_edit_webhook'] = 'Edit Webhook';
$lang['n8n_delete_webhook'] = 'Delete Webhook';
$lang['n8n_duplicate_webhook'] = 'Duplicate Webhook';
$lang['n8n_test_connection'] = 'Test Connection';
$lang['n8n_view_logs'] = 'View Logs';
$lang['n8n_clear_logs'] = 'Clear Logs';
$lang['n8n_export_logs'] = 'Export Logs';

// Messages
$lang['n8n_webhook_created'] = 'Webhook created successfully';
$lang['n8n_webhook_updated'] = 'Webhook updated successfully';
$lang['n8n_webhook_deleted'] = 'Webhook deleted successfully';
$lang['n8n_webhook_duplicated'] = 'Webhook duplicated successfully';
$lang['n8n_test_sent'] = 'Test webhook sent successfully';
$lang['n8n_test_failed'] = 'Test webhook failed: %s';
$lang['n8n_no_webhooks'] = 'No webhooks configured yet. Create your first webhook to get started.';
$lang['n8n_no_logs'] = 'No webhook logs found.';
$lang['n8n_logs_deleted'] = 'All webhook logs deleted successfully';
$lang['n8n_invalid_url'] = 'Please enter a valid webhook URL';
$lang['n8n_connection_successful'] = 'Connection test successful! (HTTP %s)';
$lang['n8n_connection_failed'] = 'Connection test failed: %s';

// Statistics
$lang['n8n_total_webhooks'] = 'Total Webhooks';
$lang['n8n_active_webhooks'] = 'Active Webhooks';
$lang['n8n_total_triggers'] = 'Total Triggers';
$lang['n8n_successful_deliveries'] = 'Successful Deliveries';
$lang['n8n_failed_deliveries'] = 'Failed Deliveries';
$lang['n8n_success_rate'] = 'Success Rate';
$lang['n8n_avg_response_time'] = 'Avg Response Time';
$lang['n8n_last_triggered'] = 'Last Triggered';
$lang['n8n_never_triggered'] = 'Never Triggered';

// Log details
$lang['n8n_log_event_id'] = 'Event ID';
$lang['n8n_log_event_type'] = 'Event Type';
$lang['n8n_log_resource'] = 'Resource';
$lang['n8n_log_status'] = 'Status';
$lang['n8n_log_attempt'] = 'Attempt';
$lang['n8n_log_response_code'] = 'Response Code';
$lang['n8n_log_response_time'] = 'Response Time';
$lang['n8n_response_code'] = 'Response Code';
$lang['n8n_event'] = 'Event';

// Additional translations
$lang['n8n_connector_dashboard'] = 'N8N Connector Dashboard';
$lang['n8n_event_type'] = 'Event Type';
$lang['n8n_event_types'] = 'Event Types';
$lang['n8n_url'] = 'URL';
$lang['n8n_status'] = 'Status';
$lang['n8n_duration'] = 'Duration';
$lang['n8n_attempts'] = 'Attempts';
$lang['n8n_active'] = 'Active';
$lang['n8n_inactive'] = 'Inactive';
$lang['n8n_recent_webhooks'] = 'Recent Webhooks';
$lang['n8n_recent_logs'] = 'Recent Logs';
$lang['n8n_event_statistics'] = 'Event Statistics';
$lang['n8n_no_webhooks_yet'] = 'No webhooks configured yet';
$lang['n8n_no_logs_yet'] = 'No logs available yet';
$lang['n8n_webhook_status_updated'] = 'Webhook status updated';
$lang['n8n_webhook_status_update_failed'] = 'Failed to update webhook status';
$lang['n8n_test_webhook_success'] = 'Test webhook sent successfully';
$lang['n8n_test_webhook_failed'] = 'Test webhook failed';
$lang['n8n_webhook_create_failed'] = 'Failed to create webhook';
$lang['n8n_webhook_update_failed'] = 'Failed to update webhook';
$lang['n8n_webhook_delete_failed'] = 'Failed to delete webhook';
$lang['n8n_webhook_not_found'] = 'Webhook not found';
$lang['n8n_logs_cleared'] = 'Old logs cleared successfully';
$lang['n8n_logs_clear_failed'] = 'Failed to clear logs';
$lang['n8n_clear_all_logs'] = 'Clear All';
$lang['n8n_log_details'] = 'Log Details';
$lang['n8n_log_id'] = 'Log ID';
$lang['n8n_error_message'] = 'Error';
$lang['n8n_payload'] = 'Payload Sent';
$lang['n8n_response'] = 'Response Received';
$lang['n8n_settings'] = 'Settings';
$lang['n8n_settings_updated'] = 'Settings updated successfully';
$lang['n8n_module_enabled'] = 'Enable Module';
$lang['n8n_module_enabled_help'] = 'Turn on/off the n8n Connector module globally';
$lang['n8n_enable_logging'] = 'Enable Logging';
$lang['n8n_enable_logging_help'] = 'Log all webhook requests and responses';
$lang['n8n_log_retention_days'] = 'Log Retention Days';
$lang['n8n_log_retention_days_help'] = 'Number of days to keep logs before automatic cleanup';
$lang['n8n_request_timeout'] = 'Request Timeout (minutes)';
$lang['n8n_request_timeout_help'] = 'Maximum time to wait for webhook response (cron-dependent, 1-60 minutes)';
$lang['n8n_max_retries_help'] = 'Number of retry attempts on failure (0-10)';
$lang['n8n_retry_delay_help'] = 'Seconds to wait before retry attempt (10-3600)';
$lang['n8n_system_information'] = 'System Information';

// Form help texts
$lang['n8n_webhook_url_help'] = 'Your n8n webhook URL (e.g., https://your-n8n.com/webhook/unique-id)';
$lang['n8n_event_types_help'] = 'Select which events should trigger this webhook';
$lang['n8n_secret_key_help'] = 'Optional secret key for webhook signature verification';
$lang['n8n_secret_key_description'] = 'Used to generate HMAC signature in X-N8N-Signature header';
$lang['n8n_custom_headers_help'] = 'Additional HTTP headers to include in webhook requests';
$lang['n8n_custom_headers_description'] = 'Enter valid JSON object with header name-value pairs';
$lang['n8n_enable_signatures_help'] = 'Add X-N8N-Signature header with HMAC-SHA256 signature';
$lang['n8n_include_relationships_help'] = 'Include related data (contacts, items, etc.) in webhook payload';
$lang['n8n_track_changes_help'] = 'Include previous and current values for updated fields';
$lang['n8n_retry_configuration'] = 'Retry Configuration';
$lang['n8n_advanced_settings'] = 'Advanced Settings';
$lang['n8n_generate'] = 'Generate';
$lang['n8n_optional'] = 'Optional';
$lang['n8n_webhook_url_required'] = 'Webhook URL is required';
$lang['n8n_testing'] = 'Testing';
$lang['n8n_selected_events_info'] = 'Selected Events Information';
$lang['n8n_select_events_to_see_info'] = 'Select events to see their descriptions';
$lang['n8n_module_version'] = 'Module Version';
$lang['n8n_perfex_version'] = 'Perfex CRM Version';
$lang['n8n_php_version'] = 'PHP Version';
$lang['n8n_curl_enabled'] = 'cURL Enabled';
$lang['n8n_total_logs'] = 'Total Logs';
$lang['n8n_webhook_url_help'] = 'Your n8n webhook URL (starts with https://)';
$lang['n8n_event_types_help'] = 'Select which Perfex CRM events will trigger this webhook';
$lang['n8n_secret_key_help'] = 'Optional secret for HMAC signature verification';
$lang['n8n_secret_key_description'] = 'Used to generate X-n8n-Signature header for secure verification';
$lang['n8n_optional'] = 'Optional';
$lang['n8n_generate'] = 'Generate';
$lang['n8n_selected_events_info'] = 'Selected Events Information';
$lang['n8n_select_events_to_see_info'] = 'Select events to see their information';
$lang['n8n_webhook_url_required'] = 'Webhook URL is required';
$lang['n8n_testing'] = 'Testing';
$lang['n8n_event'] = 'Event';
$lang['n8n_time'] = 'Time';
$lang['n8n_successful'] = 'Successful';
$lang['n8n_failed'] = 'Failed';
$lang['n8n_status_success'] = 'Success';
$lang['n8n_status_failed'] = 'Failed';
$lang['n8n_status_retry'] = 'Retry';
$lang['n8n_status_pending'] = 'Pending';
$lang['n8n_events'] = 'Events';
$lang['n8n_total_triggers'] = 'Total Triggers';

// Permissions
$lang['n8n_connector_view'] = 'View n8n Connector';
$lang['n8n_connector_create'] = 'Create Webhooks';
$lang['n8n_connector_edit'] = 'Edit Webhooks';
$lang['n8n_connector_delete'] = 'Delete Webhooks';

$lang['n8n_log_payload'] = 'Payload';
$lang['n8n_log_request_headers'] = 'Request Headers';
$lang['n8n_log_response_body'] = 'Response Body';
$lang['n8n_log_error_message'] = 'Error Message';
$lang['n8n_log_triggered_at'] = 'Triggered At';
$lang['n8n_log_completed_at'] = 'Completed At';

// Settings
$lang['n8n_settings_general'] = 'General Settings';
$lang['n8n_settings_security'] = 'Security Settings';
$lang['n8n_settings_retry'] = 'Retry Policy';
$lang['n8n_settings_logging'] = 'Logging Settings';
$lang['n8n_enable_module'] = 'Enable n8n Connector';
$lang['n8n_debug_mode'] = 'Debug Mode';
$lang['n8n_log_retention'] = 'Log Retention (days)';
$lang['n8n_default_timeout'] = 'Default Timeout (minutes)';
$lang['n8n_validate_ssl'] = 'Validate SSL Certificates';
$lang['n8n_settings_saved'] = 'Settings saved successfully';

// Help text
$lang['n8n_help_webhook_url'] = 'Copy the webhook URL from your n8n workflow and paste it here';
$lang['n8n_help_secret_key'] = 'Secret key used to sign webhook payloads for security. Keep this confidential.';
$lang['n8n_help_signatures'] = 'When enabled, webhooks will include an X-Webhook-Signature header for verification';
$lang['n8n_help_relationships'] = 'Include related data like customer info, invoice items, etc. in the payload';
$lang['n8n_help_track_changes'] = 'For update events, include previous and current values to see what changed';
$lang['n8n_help_retries'] = 'Automatically retry failed webhooks with exponential backoff';
$lang['n8n_help_debug'] = 'Log all webhook attempts. May increase database size.';

// Additional settings
$lang['n8n_enable_signatures_help'] = 'Add HMAC-SHA256 signature to webhook requests for security verification';
$lang['n8n_validate_ssl_help'] = 'Verify SSL certificates when sending webhooks (recommended for production)';
$lang['n8n_retry_enabled_help'] = 'Automatically retry failed webhook deliveries';
$lang['n8n_retry_delay_help'] = 'Seconds to wait between retry attempts';
$lang['n8n_retry_settings'] = 'Retry Settings';
$lang['n8n_statistics'] = 'Statistics';
$lang['n8n_available_events'] = 'Available Events';
$lang['view_documentation'] = 'View Documentation';

// Datatables
$lang['actions'] = 'Actions';
$lang['view_all'] = 'View All';
$lang['back'] = 'Back';

// Background queue settings
$lang['n8n_run_in_background'] = 'Run as Background Job (Cron)';
$lang['n8n_run_in_background_help'] = 'When enabled, this webhook will be queued and processed by a cron job instead of executing immediately. Recommended for high-volume webhooks or when the external service has slow response times. Provides automatic retry on failure.';
$lang['n8n_queue_processing'] = 'Background Queue Processing';
$lang['n8n_queue_stats'] = 'Queue Statistics';
$lang['n8n_queue'] = 'Webhook Queue';
$lang['n8n_queue_id'] = 'Queue ID';
$lang['n8n_queue_pending'] = 'Pending';
$lang['n8n_queue_retry'] = 'Retry';
$lang['n8n_queue_completed'] = 'Completed';
$lang['n8n_queue_failed'] = 'Failed';
$lang['n8n_queue_retrying'] = 'Retrying';
$lang['n8n_oldest_pending'] = 'Oldest Pending';
$lang['n8n_process_queue_manually'] = 'Process Queue Manually';
$lang['n8n_queue_total_today'] = 'Total Today';
$lang['n8n_retry_count'] = 'Retry Count';
$lang['n8n_next_retry'] = 'Next Retry';
$lang['n8n_created_at'] = 'Created At';
$lang['n8n_view_log'] = 'View Log';

// Queue statuses
$lang['n8n_queue_status_pending'] = 'Pending';
$lang['n8n_queue_status_processing'] = 'Processing';
$lang['n8n_queue_status_completed'] = 'Completed';
$lang['n8n_queue_status_failed'] = 'Failed';
$lang['n8n_queue_status_retrying'] = 'Retrying';

// Cron-based features
$lang['n8n_auto_remove_logs'] = 'Auto Remove Old Logs (Cron)';
$lang['n8n_auto_remove_logs_help'] = 'Automatically remove logs older than the specified retention days when cron runs';
$lang['copy_url'] = 'Copy URL';
$lang['url_copied'] = 'Webhook URL copied successfully';
$lang['n8n_secret_key'] = 'Secret Key';
$lang['n8n_webhook_logs'] = 'Webhook Logs';
$lang['n8n_connector_webhook_logs'] = 'N8N Webhook Logs';
$lang['n8n_connector_queue'] = 'N8N Connector Queue';
$lang['webhook_response'] = 'Webhook Response';
$lang['n8n_connector_webhook'] = 'N8N Connector Webhooks';

// Additional event labels
$lang['proposal_accepted'] = 'Proposal Accepted';
$lang['proposal_declined'] = 'Proposal Declined';
$lang['estimate_accepted'] = 'Estimate Accepted';
$lang['estimate_declined'] = 'Estimate Declined';
$lang['webhook_enabled'] = 'Webhook enabled successfully';
$lang['webhook_disabled'] = 'Webhook disabled successfully';

// Update language line : Start
$lang['update_version'] = 'Update Version';
$lang['update_warning'] = 'Before performing an update, it is <b>strongly recommended to create a full backup</b> of your current installation <b>(files and database)</b> and review the changelog.';
$lang['upgrade_function'] = 'Upgrade Function';
$lang['download_files'] = 'Download files';
$lang['fix_errors'] = 'Please fix the errors listed below.';
$lang['module_update'] = 'Module Update';
$lang['username'] = 'Username';
$lang['changelog'] = 'Change Log';
$lang['check_update'] = 'Check Update';
$lang['database_upgrade_required'] = 'Database upgrade is required!';
$lang['update_content_1'] = 'You need to perform a database upgrade before proceeding. Your ';
$lang['update_content_2'] = '<strong>files version</strong> is ';
$lang['update_content_3'] = ' and <strong>database version</strong> is ';
$lang['update_content_4'] = 'Make sure that you have a backup of your database before performing an upgrade.';
$lang['update_content_5'] = 'This message may show if you uploaded files from a newer version downloaded from CodeCanyon to your existing installation or you used an auto-upgrade tool.';
$lang['upgrade_now'] = 'UPGRADE NOW';
$lang['module_updated_successfully'] = 'Module Updated Successfully';
$lang['create_support_ticket'] = 'Create Support Ticket';
$lang['support_ticket_content'] = 'Do you want custom services? Visit here and create your ticket';
// Update language line: Over
$lang['queue_job_deleted'] = 'Queued job deleted successfully';
$lang['log_deleted'] = 'Webhook log deleted successfully';
$lang['help'] = 'Help';
