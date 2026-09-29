<?php

// License
$lang['wmm_zegaware_license']              = 'WMM License';
$lang['wmm_zegaware_license_title']        = 'Website Management Module License';
$lang['invalid_license']                   = 'Invalid License';
$lang['invalid_license_module']            = 'Invalid License';
$lang['license_activated']                 = 'License Was Activated';
$lang['license_expired']                   = 'License Expired';
$lang['invalid_license_domain']            = 'Invalid License Domain';
$lang['invalid_license_email']             = 'Invalid License Email';
$lang['activation_error']                  = 'License Activation Error';
$lang['require_license']                   = 'Require License';
$lang['zegaware_license_key']              = 'License Key';
$lang['zegaware_license_activate']         = 'Activate';
$lang['zegaware_your_license']             = 'Your License Key';
$lang['zegaware_your_name']                = 'Your Name';
$lang['zegaware_your_email']               = 'Your Email';
$lang['zegaware_activated_at']             = 'Activated At';
$lang['zegaware_remove_license']           = 'Remove License';
$lang['zegaware_activated_success']        = 'License Activated';
$lang['zegaware_removed_success']          = 'License Removed';
$lang['zegaware_require_license']          = 'Require License';
$lang['zegaware_customer_name']            = 'Name';
$lang['zegaware_customer_email']           = 'Email';
$lang['zegaware_customer_envato_username'] = 'Envato Username';

# General
$lang['wmm_website_maintenance'] = 'Website Maintenance Management';
$lang['wmm_maintenance_tasks']   = 'Maintenance Tasks';
$lang['wmm_manage_websites']     = 'Manage Websites';
$lang['wmm_perform_maintenance'] = 'Perform Maintenance';
$lang['wmm_maintenance_logs']    = 'Maintenance Logs';
$lang['wmm_log_maintenance']     = 'Log Maintenance';
$lang['wmm_problem_updating']    = 'Problem updating %s';

# Tasks
$lang['wmm_maintenance_task'] = 'Maintenance Task';
$lang['wmm_add_new_task']     = 'Add New Task';
$lang['wmm_task_name']        = 'Task Name';
$lang['wmm_category']         = 'Category';
$lang['wmm_description']      = 'Description';
$lang['wmm_status']           = 'Status';
$lang['wmm_is_active']        = 'Is Active';
$lang['wmm_active']           = 'Active';
$lang['wmm_inactive']         = 'Inactive';
$lang['wmm_created_at']       = 'Created At';
$lang['wmm_task_has_logs']    = 'This task cannot be deleted because it has been used in maintenance logs.';

# Categories
$lang['wmm_category_plugin'] = 'Plugin Update';
$lang['wmm_category_theme']  = 'Theme Update';
$lang['wmm_category_core']   = 'Core Update';
$lang['wmm_category_other']  = 'Other';

# Websites
$lang['wmm_website']                    = 'Website';
$lang['wmm_add_website_to_maintenance'] = 'Add Website to Maintenance';
$lang['wmm_websites_under_maintenance'] = 'Websites Under Maintenance';
$lang['wmm_select_customer']            = 'Select Customer';
$lang['wmm_select_project']             = 'Select Project';
$lang['wmm_select_project_first']       = 'Please select a customer first';
$lang['wmm_website_url']                = 'Website URL';
$lang['wmm_add_to_maintenance']         = 'Add to Maintenance';
$lang['wmm_website_added_successfully'] = 'Website added to maintenance successfully';
$lang['wmm_website_add_failed']         = 'Failed to add website to maintenance';
$lang['wmm_project_already_added']      = 'This project has already been added to maintenance';
$lang['wmm_website_has_logs']           = 'This website cannot be deleted because it has maintenance logs.';
$lang['wmm_customer']                   = 'Customer';
$lang['wmm_project']                    = 'Project';
$lang['wmm_date_added']                 = 'Date Added';

# Perform Maintenance
$lang['wmm_no_websites_available']           = 'No websites available for maintenance. Please add websites first.';
$lang['wmm_add_websites']                    = 'Add Websites';
$lang['wmm_select_website']                  = 'Select Website';
$lang['wmm_select_completed_tasks']          = 'Select Completed Tasks';
$lang['wmm_select_tasks_description']        = 'Please check all tasks that were completed during this maintenance session.';
$lang['wmm_loading_tasks']                   = 'Loading tasks...';
$lang['wmm_no_tasks_available']              = 'No active tasks available. Please add tasks first.';
$lang['wmm_notes']                           = 'Notes';
$lang['wmm_notes_placeholder']               = 'Add any additional notes about this maintenance session (optional)';
$lang['wmm_maintenance_completed']           = 'Maintenance Completed';
$lang['wmm_please_select_tasks']             = 'Please select at least one task that was completed.';
$lang['wmm_confirm_maintenance_complete']    = 'Are you sure you want to mark this maintenance as completed? An email will be sent to the customer.';
$lang['wmm_maintenance_logged_successfully'] = 'Maintenance logged successfully and notification email sent to customer.';
$lang['wmm_maintenance_log_failed']          = 'Failed to log maintenance. Please try again.';
$lang['wmm_select_tasks']                    = 'Select Tasks';

# Logs
$lang['wmm_maintenance_log']         = 'Maintenance Log';
$lang['wmm_maintenance_log_details'] = 'Maintenance Log Details';
$lang['wmm_performed_by']            = 'Performed By';
$lang['wmm_performed_at']            = 'Performed At';
$lang['wmm_maintenance_date']        = 'Maintenance Date';
$lang['wmm_email_status']            = 'Email Status';
$lang['wmm_email_sent']              = 'Email Sent';
$lang['wmm_email_not_sent']          = 'Email Not Sent';
$lang['wmm_resend_email']            = 'Resend Email';
$lang['wmm_send_email']              = 'Send Email';
$lang['wmm_confirm_resend_email']    = 'Are you sure you want to resend the notification email?';
$lang['wmm_confirm_send_email']      = 'Are you sure you want to send the notification email?';
$lang['wmm_email_sent_successfully'] = 'Email sent successfully';
$lang['wmm_email_send_failed']       = 'Failed to send email. Please check the email configuration.';
$lang['wmm_tasks_completed']         = 'Tasks Completed';
$lang['wmm_no_tasks_completed']      = 'No tasks were completed in this maintenance session.';
$lang['wmm_log_not_found']           = 'Maintenance log not found.';

# Email Template
$lang['wmm_email_subject']  = 'Website Maintenance Completed';
$lang['wmm_email_greeting'] = 'Dear {client_name},';
$lang['wmm_email_body']     = 'This is to inform you that maintenance work has been performed on your website.';
$lang['wmm_email_closing']  = 'If you have any questions or concerns, please feel free to contact us.';

# Attachments
$lang['wmm_attachments']                 = 'Attachments';
$lang['wmm_attachment']                  = 'Attachment';
$lang['wmm_no_attachments']              = 'No attachments';
$lang['wmm_download_all']                = 'Download All';
$lang['wmm_drop_files_here']             = 'Drop files here to upload';
$lang['wmm_or_click_to_browse']          = 'or click to browse';
$lang['wmm_upload_files']                = 'Upload Files';
$lang['wmm_files_uploaded_successfully'] = 'Files uploaded successfully';
$lang['wmm_upload_failed']               = 'Upload failed. Please try again.';

# Quick Actions
$lang['wmm_copy_link']   = 'Copy Link';
$lang['wmm_link_copied'] = 'Link copied to clipboard';

# Additional
$lang['wmm_performed_by'] = 'Performed by %s';
$lang['id']               = 'ID';
$lang['view']             = 'View';
$lang['back']             = 'Back';
$lang['download']         = 'Download';

# Version 1.0.2 - New Features

# Priorities
$lang['wmm_priority']        = 'Priority';
$lang['wmm_priority_low']    = 'Low';
$lang['wmm_priority_medium'] = 'Medium';
$lang['wmm_priority_high']   = 'High';
$lang['wmm_priority_urgent'] = 'Urgent';

# Task Statuses
$lang['wmm_status_not_started']       = 'Not Started';
$lang['wmm_status_in_progress']       = 'In Progress';
$lang['wmm_status_testing']           = 'Testing';
$lang['wmm_status_awaiting_feedback'] = 'Awaiting Feedback';
$lang['wmm_status_complete']          = 'Complete';

# Timer & Time Tracking
$lang['wmm_start_timer']           = 'Start Timer';
$lang['wmm_stop_timer']            = 'Stop Timer';
$lang['wmm_timer_already_running'] = 'Timer is already running for this task';
$lang['wmm_no_active_timer']       = 'No active timer found';
$lang['wmm_time_logged']           = 'Time Logged';
$lang['wmm_total_time']            = 'Total Time';
$lang['wmm_billable']              = 'Billable';
$lang['wmm_hourly_rate']           = 'Hourly Rate';
$lang['wmm_time_h']                = '%s Hours';
$lang['wmm_time_m']                = '%s Minutes';
$lang['wmm_no_time_logged']        = 'No time logged yet';
$lang['wmm_add_time_entry']        = 'Add Time Entry';
$lang['wmm_edit_time_entry']       = 'Edit Time Entry';
$lang['wmm_delete_time_entry']     = 'Delete Time Entry';
$lang['wmm_confirm_delete_time']   = 'Are you sure you want to delete this time entry?';

# Assignees
$lang['wmm_assignees']       = 'Assignees';
$lang['wmm_assign_to']       = 'Assign To';
$lang['wmm_assigned_to']     = 'Assigned to %s';
$lang['wmm_no_assignees']    = 'No assignees yet';
$lang['wmm_add_assignees']   = 'Add Assignees';
$lang['wmm_remove_assignee'] = 'Remove Assignee';

# Checklist
$lang['wmm_checklist']          = 'Checklist';
$lang['wmm_add_checklist_item'] = 'Add Checklist Item';
$lang['wmm_checklist_item']     = 'Checklist Item';
$lang['wmm_checklist_items']    = 'Checklist Items';
$lang['wmm_no_checklist_items'] = 'No checklist items';
$lang['wmm_checklist_finished'] = '%s of %s completed';

# Comments & Activity
$lang['wmm_comments']               = 'Comments';
$lang['wmm_add_comment']            = 'Add Comment';
$lang['wmm_write_comment']          = 'Write a comment...';
$lang['wmm_edit_comment']           = 'Edit Comment';
$lang['wmm_delete_comment']         = 'Delete Comment';
$lang['wmm_confirm_delete_comment'] = 'Are you sure you want to delete this comment?';
$lang['wmm_no_comments']            = 'No comments yet';
$lang['wmm_activity']               = 'Activity';
$lang['wmm_task_activity']          = 'Task Activity';

# Recurring Tasks
$lang['wmm_recurring']             = 'Recurring';
$lang['wmm_is_recurring_task']     = 'This is a recurring task';
$lang['wmm_recurring_every']       = 'Repeat Every';
$lang['wmm_recurring_interval']    = 'Interval';
$lang['wmm_recurring_type']        = 'Repeat Type';
$lang['wmm_recurring_type_day']    = 'Day(s)';
$lang['wmm_recurring_type_week']   = 'Week(s)';
$lang['wmm_recurring_type_month']  = 'Month(s)';
$lang['wmm_recurring_type_year']   = 'Year(s)';
$lang['wmm_recurring_type_custom'] = 'Custom';
$lang['wmm_last_recurring_date']   = 'Last Recurring Date';

# Dates
$lang['wmm_due_date']   = 'Due Date';
$lang['wmm_start_date'] = 'Start Date';
$lang['wmm_overdue']    = 'Overdue';
$lang['wmm_due_today']  = 'Due Today';
$lang['wmm_due_in']     = 'Due in %s days';

# Tags
$lang['wmm_tags']       = 'Tags';
$lang['wmm_add_tag']    = 'Add Tag';
$lang['wmm_enter_tags'] = 'Enter tags...';

# Task Details
$lang['wmm_task_details']        = 'Task Details';
$lang['wmm_task_overview']       = 'Overview';
$lang['wmm_task_information']    = 'Task Information';
$lang['wmm_view_task']           = 'View Task';
$lang['wmm_edit_task']           = 'Edit Task';
$lang['wmm_created_by']          = 'Created By';
$lang['wmm_last_updated']        = 'Last Updated';
$lang['wmm_updated_by']          = 'Updated By';
$lang['wmm_completed_on']        = 'Completed On';
$lang['wmm_visible_to_customer'] = 'Visible to Customer';

# Maintenance History
$lang['wmm_maintenance_history'] = 'Maintenance History';
$lang['wmm_performed_in_logs']   = 'Performed in Logs';
$lang['wmm_times_performed']     = 'Times Performed';
$lang['wmm_last_performed']      = 'Last Performed';
$lang['wmm_never_performed']     = 'Never Performed';
$lang['wmm_view_log']            = 'View Log';

# Notifications
$lang['wmm_notify_assignees']           = 'Notify Assignees';
$lang['wmm_task_assigned_notification'] = 'You have been assigned to maintenance task: %s';
$lang['wmm_task_due_notification']      = 'Maintenance task "%s" is due %s';
$lang['wmm_task_overdue_notification']  = 'Maintenance task "%s" is overdue!';

# Stats & Summary
$lang['wmm_total_tasks']      = 'Total Tasks';
$lang['wmm_active_tasks']     = 'Active Tasks';
$lang['wmm_completed_tasks']  = 'Completed Tasks';
$lang['wmm_overdue_tasks']    = 'Overdue Tasks';
$lang['wmm_my_tasks']         = 'My Tasks';
$lang['wmm_unassigned_tasks'] = 'Unassigned Tasks';

# Actions
$lang['wmm_mark_complete']       = 'Mark as Complete';
$lang['wmm_mark_incomplete']     = 'Mark as Incomplete';
$lang['wmm_change_status']       = 'Change Status';
$lang['wmm_change_priority']     = 'Change Priority';
$lang['wmm_copy_task']           = 'Copy Task';
$lang['wmm_convert_to_template'] = 'Convert to Template';

# Filters
$lang['wmm_filter_by_status']   = 'Filter by Status';
$lang['wmm_filter_by_priority'] = 'Filter by Priority';
$lang['wmm_filter_by_assignee'] = 'Filter by Assignee';
$lang['wmm_filter_by_category'] = 'Filter by Category';
$lang['wmm_filter_by_tags']     = 'Filter by Tags';
$lang['wmm_show_completed']     = 'Show Completed';
$lang['wmm_show_active']        = 'Show Active Only';

# Validation Messages
$lang['wmm_task_name_required']          = 'Task name is required';
$lang['wmm_category_required']           = 'Category is required';
$lang['wmm_at_least_one_assignee']       = 'Please assign at least one staff member';
$lang['wmm_invalid_date_range']          = 'Start date must be before due date';
$lang['wmm_recurring_interval_required'] = 'Recurring interval is required for recurring tasks';

# Calendar
$lang['wmm_calendar']   = 'Calendar';
$lang['wmm_all_staff']  = 'All Staff';
$lang['calendar_month'] = 'Month';
$lang['calendar_week']  = 'Week';
$lang['calendar_day']   = 'Day';
$lang['today']          = 'Today';

# Reports & Analytics
$lang['wmm_maintenance_reports_analytics'] = 'Maintenance Reports & Analytics';
$lang['wmm_reports_analytics']             = 'Reports & Analytics';
$lang['wmm_reports']                       = 'Reports';
$lang['wmm_analytics']                     = 'Analytics';
$lang['wmm_date_from']                     = 'Date From';
$lang['wmm_date_to']                       = 'Date To';
$lang['apply_filter']                      = 'Apply Filter';
$lang['export_excel']                      = 'Export to Excel';
$lang['wmm_completion_trend']              = 'Task Completion Trend';
$lang['wmm_time_logged_trend']             = 'Time Logged Trend';
$lang['wmm_tasks_by_category']             = 'Tasks by Category';
$lang['wmm_tasks_by_priority']             = 'Tasks by Priority';
$lang['wmm_staff_productivity']            = 'Staff Productivity';
$lang['wmm_tasks_worked_on']               = 'Tasks Worked On';
$lang['wmm_total_hours']                   = 'Total Hours';
$lang['wmm_billable_amount']               = 'Billable Amount';
$lang['wmm_from_time_tracking']            = 'From Time Tracking';
$lang['wmm_top_performers']                = 'Top Performers';
$lang['wmm_most_maintained_websites']      = 'Most Maintained Websites';
$lang['wmm_maintenance_count']             = 'Maintenance Count';
$lang['wmm_time_entries']                  = 'Time Entries';
$lang['wmm_hours']                         = 'Hours';
$lang['wmm_completed']                     = 'Completed';
$lang['days_overdue']                      = 'days overdue';

# Dashboard Widgets
$lang['wmm_dashboard']             = 'Dashboard';
$lang['wmm_my_active_tasks']       = 'My Active Tasks';
$lang['wmm_my_overdue']            = 'My Overdue';
$lang['wmm_hours_this_week']       = 'Hours This Week';
$lang['wmm_maintenance_summary']   = 'Maintenance Summary';
$lang['wmm_time_logged_this_week'] = 'Time Logged This Week';
$lang['wmm_timer_running']         = 'Timer Running';
$lang['wmm_view_detailed_report']  = 'View Detailed Report';
$lang['wmm_no_tasks_assigned']     = 'No tasks assigned to you yet';
$lang['wmm_no_overdue_tasks']      = 'No overdue tasks! Great job!';
$lang['view_all']                  = 'View All';
$lang['wmm_websites']              = 'Websites';
$lang['wmm_maintenance_websites']  = 'Maintenance Websites';

# Categories Management (v1.0.3)
$lang['wmm_maintenance_categories'] = 'Maintenance Categories';
$lang['wmm_categories']             = 'Categories';
$lang['wmm_category']               = 'Category';
$lang['wmm_add_new_category']       = 'Add New Category';
$lang['wmm_category_name']          = 'Category Name';
$lang['wmm_category_slug']          = 'Slug';
$lang['wmm_category_icon']          = 'Icon';
$lang['wmm_category_color']         = 'Color';
$lang['wmm_display_order']          = 'Display Order';
$lang['wmm_auto_generated']         = 'Auto-generated from name';
$lang['wmm_slug_help']              = 'Leave empty to auto-generate from name. Only lowercase letters, numbers and hyphens allowed.';
$lang['wmm_browse_icons']           = 'Browse FontAwesome Icons';
$lang['wmm_category_slug_exists']   = 'This slug already exists. Please choose a different one.';
$lang['wmm_category_has_tasks']     = 'Cannot delete this category. It is being used by %s task(s).';

# Quick Links & Dashboard
$lang['wmm_quick_links']             = 'Quick Links';
$lang['wmm_my_recent_tasks']         = 'My Recent Tasks';
$lang['wmm_recent_maintenance_logs'] = 'Recent Maintenance Logs';
$lang['wmm_no_recent_logs']          = 'No recent logs';
$lang['wmm_view_report']             = 'View Report';

# Timer & Time Tracking
$lang['wmm_time_tracker'] = 'Time Tracker';
$lang['wmm_time_spent']   = 'Time Spent';

# Recurrence
$lang['wmm_daily']               = 'Daily';
$lang['wmm_weekly']              = 'Weekly';
$lang['wmm_monthly']             = 'Monthly';
$lang['wmm_yearly']              = 'Yearly';
$lang['wmm_recurrence_type']     = 'Recurrence Type';
$lang['wmm_recurrence_interval'] = 'Recurrence Interval';
$lang['error_occurred']          = 'An error occurred';

# Maintenance Type & Timer (v1.0.4)
$lang['wmm_maintenance_type']               = 'Maintenance Type';
$lang['wmm_start_new_maintenance']          = 'Start New Maintenance';
$lang['wmm_start_new_maintenance_help']     = 'Start a new maintenance session. The timer will begin automatically.';
$lang['wmm_log_completed_maintenance']      = 'Log Completed Maintenance';
$lang['wmm_log_completed_maintenance_help'] = 'Log maintenance that has already been completed. You need to enter the start and end time.';
$lang['wmm_start_time']                     = 'Start Time';
$lang['wmm_end_time']                       = 'End Time';
$lang['wmm_please_enter_start_end_time']    = 'Please enter both start time and end time';
$lang['wmm_end_time_must_be_after_start']   = 'End time must be after start time';
$lang['wmm_maintenance_in_progress']        = 'Maintenance In Progress';
$lang['wmm_started_at']                     = 'Started at';
$lang['wmm_elapsed_time']                   = 'Elapsed Time';
$lang['wmm_confirm_stop_timer']             = 'Are you sure you want to stop the timer and mark this maintenance as completed?';
$lang['wmm_stopping']                       = 'Stopping...';
$lang['wmm_timer_stopped_successfully']     = 'Timer stopped successfully. Maintenance marked as completed.';
$lang['wmm_timer_stop_failed']              = 'Failed to stop timer. Please try again.';
$lang['wmm_from']                           = 'From';
$lang['wmm_to']                             = 'To';
$lang['wmm_send_email_notification']        = 'Send Email Notification';
$lang['wmm_in_progress']                    = 'In Progress';
$lang['wmm_completed_logs']                 = 'Completed Logs';
$lang['wmm_avg_time_per_log']               = 'Avg. Time per Log';
$lang['wmm_my_logs_this_month']             = 'My Logs This Month';
$lang['wmm_my_in_progress']                 = 'My In Progress';
$lang['wmm_uncategorized']                  = 'Uncategorized';

# Email Templates
$lang['wmm_email_templates']                     = 'Website Maintenance';
$lang['wmm_email_maintenance_started_subject']   = 'Website Maintenance Started - {project_name}';
$lang['wmm_email_maintenance_completed_subject'] = 'Website Maintenance Completed - {project_name}';

# Invoice & Billing
$lang['wmm_billing_options']               = 'Billing Options';
$lang['wmm_is_billable']                   = 'This maintenance is billable';
$lang['wmm_hourly_rate']                   = 'Hourly Rate';
$lang['wmm_hourly_rate_placeholder']       = 'Enter hourly rate';
$lang['wmm_hourly_rate_help']              = 'If specified, invoice will be calculated based on time spent × hourly rate';
$lang['wmm_billable_hours']                = 'Billable Hours';
$lang['wmm_create_invoice']                = 'Create Invoice';
$lang['wmm_confirm_create_invoice']        = 'Are you sure you want to create an invoice for this maintenance?';
$lang['wmm_invoice_created_successfully']  = 'Invoice created successfully';
$lang['wmm_invoice_creation_failed']       = 'Failed to create invoice';
$lang['wmm_cannot_create_invoice']         = 'Cannot create invoice. Maintenance must be completed.';
$lang['wmm_invoice_already_created']       = 'Invoice has already been created for this maintenance';
$lang['wmm_view_invoice_now']              = 'Do you want to view the invoice now?';
$lang['wmm_not_invoiced']                  = 'Not Invoiced';
$lang['wmm_maintenance_service']           = 'Maintenance Service';
$lang['wmm_unlink_invoice']                = 'Unlink Invoice';
$lang['wmm_confirm_unlink_invoice']        = 'Are you sure you want to unlink this invoice? The invoice will still exist but will no longer be linked to this maintenance log.';
$lang['wmm_invoice_unlinked_successfully'] = 'Invoice unlinked successfully';
$lang['wmm_invoice_unlink_failed']         = 'Failed to unlink invoice';
$lang['wmm_no_invoice_to_unlink']          = 'No invoice to unlink';
$lang['wmm_generating_invoice']            = 'Generating invoice';

# Support Hour Packages (v1.0.3)
$lang['wmm_support_packages']              = 'Support Hour Packages';
$lang['wmm_support_package']               = 'Support Hour Package';
$lang['wmm_add_new_package']               = 'Add New Package';
$lang['wmm_edit_package']                  = 'Edit Package';
$lang['wmm_package_details']               = 'Package Details';
$lang['wmm_package_name']                  = 'Package Name';
$lang['wmm_package_scope']                 = 'Package Scope';
$lang['wmm_specific_website']              = 'Specific Website';
$lang['wmm_all_client_websites']           = 'All Client Websites';
$lang['wmm_total_hours']                   = 'Total Hours';
$lang['wmm_hours_used']                    = 'Hours Used';
$lang['wmm_hours_remaining']               = 'Hours Remaining';
$lang['wmm_package_price']                 = 'Package Price';
$lang['wmm_low_balance_threshold']         = 'Low Balance Threshold (hours)';
$lang['wmm_low_balance_notify']            = 'Low Balance Notification';
$lang['wmm_low_balance_threshold_help']    = 'Send notification when remaining hours fall below the threshold amount';
$lang['wmm_package_information']           = 'Package Information';
$lang['wmm_usage_history']                 = 'Usage History';
$lang['wmm_hours_consumed']                = 'Hours Consumed';
$lang['wmm_no_usage_history']              = 'No usage history yet';
$lang['wmm_package_added_successfully']    = 'Package added successfully';
$lang['wmm_package_add_failed']            = 'Failed to add package';
$lang['wmm_package_updated_successfully']  = 'Package updated successfully';
$lang['wmm_package_update_failed']         = 'Failed to update package';
$lang['wmm_package_deleted_successfully']  = 'Package deleted successfully';
$lang['wmm_package_delete_failed']         = 'Failed to delete package';
$lang['wmm_package_not_found']             = 'Package not found';
$lang['wmm_package_has_usage']             = 'Cannot delete this package because it has usage history';
$lang['wmm_insufficient_package_hours']    = 'Insufficient hours remaining in package';
$lang['wmm_hours_already_deducted']        = 'Hours have already been deducted from this package';
$lang['wmm_client_required']               = 'Client is required';
$lang['wmm_invalid_status']                = 'Invalid status';
$lang['wmm_package_status_updated']        = 'Package status updated';
$lang['wmm_active_packages']               = 'Active Packages';
$lang['wmm_exhausted_packages']            = 'Exhausted Packages';
$lang['wmm_low_balance_packages']          = 'Low Balance Packages';
$lang['wmm_total_hours_remaining']         = 'Total Hours Remaining';
$lang['wmm_usage_progress']                = 'Usage Progress';
$lang['wmm_total_usages']                  = 'Total Usages';
$lang['wmm_avg_hours_per_usage']           = 'Avg Hours per Usage';
$lang['wmm_log_id']                        = 'Maintenance Log ID';
$lang['wmm_no_expiry']                     = 'No Expiry';
$lang['wmm_days_remaining']                = 'days remaining';
$lang['wmm_statistics']                    = 'Statistics';
$lang['wmm_deduct_from_package']           = 'Deduct from Package';
$lang['wmm_select_package']                = 'Select Package';
$lang['wmm_package_balance']               = 'Package Balance';
$lang['wmm_package_usage']                 = 'Package Usage';
$lang['wmm_hours_h']                       = 'h';
$lang['wmm_package_will_be_exhausted']     = 'Package will be exhausted';
$lang['wmm_current_balance']               = 'Current Balance';
$lang['wmm_after_deduction']               = 'After Deduction';
$lang['wmm_deduct_hours']                  = 'Deduct Hours';
$lang['wmm_package_deducted_successfully'] = 'Hours deducted from package successfully';
$lang['wmm_package_deduction_failed']      = 'Failed to deduct hours from package';
$lang['wmm_no_active_packages']            = 'No active packages available';
$lang['wmm_package_exhausted']             = 'Package Exhausted';
$lang['wmm_package_expired']               = 'Package Expired';
$lang['wmm_package_cancelled']             = 'Package Cancelled';
$lang['wmm_low_balance']                   = 'Low Balance';
$lang['wmm_package_expiry_warning']        = 'Package expires on %s';
$lang['wmm_get_summary']                   = 'Get Summary';
$lang['wmm_packages_analytics']            = 'Packages Analytics';
$lang['wmm_client_packages']               = 'Client Packages';
$lang['wmm_package_renewal']               = 'Package Renewal';
$lang['wmm_renew_package']                 = 'Renew Package';
$lang['wmm_package_renewed_successfully']  = 'Package renewed successfully';
$lang['wmm_top_packages']                  = 'Top Packages by Usage';
$lang['wmm_packages_by_status']            = 'Packages by Status';
$lang['wmm_expiry_date']                   = 'Expiry Date';
$lang['wmm_hours_deducted']                = 'Hours Deducted';
$lang['wmm_not_deducted']                  = 'Not Deducted from Package';
$lang['wmm_package_usage_history']         = 'Package Usage History';
$lang['wmm_total_usage_records']           = 'Total Usage Records';
$lang['wmm_total_hours_consumed']          = 'Total Hours Consumed';
$lang['wmm_this_month_usage']              = 'This Month Usage';
$lang['wmm_this_month_hours']              = 'This Month Hours';
$lang['wmm_consumed_at']                   = 'Consumed At';
$lang['wmm_consumed_by']                   = 'Consumed By';
$lang['wmm_no_expiry']                     = 'No Expiry Date';
