<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
    Module Name: N8N Connector
    Description: Send real-time Perfex CRM events to N8n via webhooks and automate everything without any subscription fees.
    Version: 1.0.0
    Requires at least: 3.0.*
    Module URI: http://codecanyon.net/item/perfex-crm-to-n8n-webhooks-realtime-automation-module/61450519
    Author: <a href="https://codecanyon.net/user/corbitaltech" target="_blank">Corbital Technologies<a/>
*/

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
*/
define('N8N_CONNECTOR_MODULE', 'n8n_connector');
define('N8N_CONNECTOR_VERSION', '1.0.0');

/*
 * Register activation module hook
 */
register_activation_hook(N8N_CONNECTOR_MODULE, 'n8n_connector_module_activate_hook');
function n8n_connector_module_activate_hook() {
    require_once __DIR__.'/install.php';
}

/*
 * Register deactivation module hook
 */
register_deactivation_hook(N8N_CONNECTOR_MODULE, 'n8n_connector_module_deactivate_hook');
function n8n_connector_module_deactivate_hook() {
    update_option('n8n_connector_enabled', 0);
}
/*
 * Register language files, must be registered if the module is using languages
 */
register_language_files(N8N_CONNECTOR_MODULE, [N8N_CONNECTOR_MODULE]);

require_once __DIR__ . '/vendor/autoload.php';
/*
 * Load module helper file
 */
get_instance()->load->helper(N8N_CONNECTOR_MODULE.'/n8n_connector');

/*
 * Load module Library files
 */
get_instance()->load->library(N8N_CONNECTOR_MODULE.'/N8n_payload_builder');
get_instance()->load->library(N8N_CONNECTOR_MODULE.'/N8n_http_client');
get_instance()->load->library(N8N_CONNECTOR_MODULE.'/N8n_signature_generator');

require_once __DIR__ . '/install.php';
get_instance()->config->load(N8N_CONNECTOR_MODULE . '/config');


require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';


/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
hooks()->add_filter('module_n8n_connector_action_links', function ($actions) {
    $actions[] = '<a href="https://help.corbitaltech.dev/hc/categories/12/perfex-crm-to-n8n" class="text-danger" target="_blank">' . _l('help') . '</a>';

    get_instance()->load->library(N8N_CONNECTOR_MODULE . '/n8n_connector_aeiou');
    $update = get_instance()->n8n_connector_aeiou->checkUpdateStatus(N8N_CONNECTOR_MODULE);
    if ($update > 0) {
        $actions[] = '<a href="' . admin_url('n8n_connector/env_ver/check_update') . '" class="text-warning">' . _l('check_update') . '</a>';
    }
    return $actions;
});


/**
 * Register settings section
 */
hooks()->add_action('admin_init', 'n8n_connector_init_settings');
function n8n_connector_init_settings()
{
    $CI = &get_instance();
    $CI->app->add_settings_section_child('other', 'n8n_connector', [
        'name'     => _l('n8n_connector'),
        'view'     => 'n8n_connector/settings',
        'position' => 50,
        'icon'     => 'fa fa-rocket',
    ]);
}

/* Lead webhooks : Start */
// Add new lead
hooks()->add_action('lead_created', 'n8n_lead_created_hook');
function n8n_lead_created_hook($leadID)
{
    //if lead created from web to lead form then leadid will be array
    if (is_array($leadID)) {
        $leadID = $leadID['lead_id'];
    }
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $tableData = $CI->leads_model->get($leadID);
    n8n_trigger_event('lead_created', $tableData);
}

hooks()->add_action('after_cron_run', 'run_n8n_connector_validator');
function run_n8n_connector_validator() {
    $CI = &get_instance();
    $CI->load->library(N8N_CONNECTOR_MODULE . '/n8n_connector_aeiou');
    $CI->n8n_connector_aeiou->checkLicenseStatus(N8N_CONNECTOR_MODULE);
}

// Update lead
hooks()->add_action('after_lead_updated', 'n8n_lead_updated_hook');
function n8n_lead_updated_hook($leadID)
{
    //if lead created from web to lead form then leadid will be array
    if (is_array($leadID)) {
        $leadID = $leadID['lead_id'];
    }
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $tableData = $CI->leads_model->get($leadID);
    n8n_trigger_event('lead_updated', $tableData);
}

// Lead status changed
hooks()->add_action('lead_status_changed', 'n8n_lead_status_changed_hook');
function n8n_lead_status_changed_hook($lead)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $tableData = $CI->leads_model->get($lead['lead_id']);
    n8n_trigger_event('lead_status_changed', $tableData);
}

// Lead converted to customer
hooks()->add_action('lead_converted_to_customer', 'n8n_lead_converted_to_customer');
function n8n_lead_converted_to_customer($data)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $tableData = $CI->leads_model->get($data['lead_id']);
    n8n_trigger_event('lead_converted_to_customer', $tableData);
}

// Delete lead
hooks()->add_action('before_lead_deleted', 'n8n_lead_deleted_hook');
function n8n_lead_deleted_hook($leadID)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $tableData = $CI->leads_model->get($leadID);
    n8n_trigger_event('lead_deleted', $tableData);
}
/* Lead webhooks : End */

/* Client webhooks : Start */
// Add new customer
hooks()->add_action('after_client_created', 'n8n_customer_created_hook');
function n8n_customer_created_hook($data)
{
    $CI = &get_instance();
    $clientData = $CI->clients_model->get($data['id']);
    n8n_trigger_event('customer_created', $clientData);
}

// Customer updated
hooks()->add_action('client_updated', 'n8n_customer_update_hook');
function n8n_customer_update_hook($data)
{
    $CI = &get_instance();
    $clientData = $CI->clients_model->get($data['id']);
    n8n_trigger_event('customer_updated', $clientData);
}

// Customer deleted
hooks()->add_action('before_client_deleted', 'n8n_customer_delete_hook');
function n8n_customer_delete_hook($id)
{
    $CI = &get_instance();
    $clientData = $CI->clients_model->get($id);
    n8n_trigger_event('customer_deleted', $clientData);
}

// Add new contact
hooks()->add_action('contact_created', 'n8n_contact_created_hook');
function n8n_contact_created_hook($contactID)
{
    $CI = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);
    n8n_trigger_event('contact_created', $tableData);
}

// Update contact
hooks()->add_action('contact_updated', 'n8n_contact_updated_hook');
function n8n_contact_updated_hook($contactID)
{
    $CI = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);
    n8n_trigger_event('contact_updated', $tableData);
}

// Delete contact
hooks()->add_action('before_delete_contact', 'n8n_contact_deleted_hook');
function n8n_contact_deleted_hook($contactID)
{
    $CI = &get_instance();
    $tableData = new stdClass();
    $tableData->ContactData = $CI->clients_model->get_contact($contactID);
    $tableData->ClientData = $CI->clients_model->get($tableData->ContactData->userid);
    if (empty($tableData->ClientData)) {
        return;
    }
    n8n_trigger_event('contact_deleted', $tableData);
}
/* Client webhooks : End */

/* Invoice webhooks : Start */
// Add new invoice
hooks()->add_action('after_invoice_added', 'n8n_invoice_created_hook');
function n8n_invoice_created_hook($id)
{
    $CI = &get_instance();
    $invoiceData = $CI->invoices_model->get($id);
    n8n_trigger_event('invoice_created', $invoiceData);
}

// Invoice updated
hooks()->add_action('invoice_updated', 'n8n_invoice_updated_hook');
function n8n_invoice_updated_hook($id)
{
    $CI = &get_instance();
    $invoiceData = $CI->invoices_model->get($id);
    n8n_trigger_event('invoice_updated', $invoiceData);
}

// Invoice status change
hooks()->add_action('invoice_status_changed', 'n8n_invoice_status_changed_hook');
function n8n_invoice_status_changed_hook($data)
{
    $CI = &get_instance();
    $invoiceData = $CI->invoices_model->get($data['invoice_id']);
    n8n_trigger_event('invoice_status_changed', $invoiceData);
}

// Invoice sent to customer
hooks()->add_action('invoice_sent', 'n8n_invoice_sent_to_customer');
function n8n_invoice_sent_to_customer($id)
{
    $CI = &get_instance();
    $invoiceData = $CI->invoices_model->get($id);
    n8n_trigger_event('invoice_sent', $invoiceData);
}

// Invoice deleted
hooks()->add_action('before_invoice_deleted', 'n8n_invoice_deleted_hook');
function n8n_invoice_deleted_hook($id)
{
    $CI = &get_instance();
    $invoiceData = $CI->invoices_model->get($id);
    n8n_trigger_event('invoice_deleted', $invoiceData);
}
/* Invoice webhooks : End */

/* Payment webhooks : Start */
// Payment created
hooks()->add_action('after_payment_added', 'n8n_payment_created_hook');
function n8n_payment_created_hook($id)
{
    $CI = &get_instance();
    $invoiceData = $CI->payments_model->get($id);
    n8n_trigger_event('payment_created', $invoiceData);
}
// Payment deleted
hooks()->add_action('before_payment_deleted', 'n8n_payment_deleted_hook');
function n8n_payment_deleted_hook($data)
{
    $CI = &get_instance();
    $invoiceData = $CI->payments_model->get($data['paymentid']);
    n8n_trigger_event('payment_deleted', $invoiceData);
}
/* Payment webhooks : End */

/* Estimates webhooks : Start */
// Estimate Create
hooks()->add_action('after_estimate_added', 'n8n_estimate_created_hook');
function n8n_estimate_created_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_created', $estimateData);
}

// Estimate update
hooks()->add_action('after_estimate_updated', 'n8n_estimate_updated_hook');
function n8n_estimate_updated_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_updated', $estimateData);
}

// Estimate delete
hooks()->add_action('before_estimate_deleted', 'n8n_estimate_deleted_hook');
function n8n_estimate_deleted_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_deleted', $estimateData);
}

// Estimate sent to customer
hooks()->add_action('estimate_sent', 'n8n_estimate_sent_hook');
function n8n_estimate_sent_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_sent', $estimateData);
}

// Estimate Accepted
hooks()->add_action('estimate_accepted', 'n8n_estimate_accepted_hook');
function n8n_estimate_accepted_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_accepted', $estimateData);
}

// Estimate Declined
hooks()->add_action('estimate_declined', 'n8n_estimate_declined_hook');
function n8n_estimate_declined_hook($id)
{
    $CI = &get_instance();
    $estimateData = $CI->estimates_model->get($id);
    n8n_trigger_event('estimate_declined', $estimateData);
}
/* Estimates webhooks : Start */

/* Proposals webhooks : Start */
// Proposal Create
hooks()->add_action('proposal_created', 'n8n_proposal_created_hook');
function n8n_proposal_created_hook($id)
{
    $CI = &get_instance();
    $proposalData = $CI->proposals_model->get($id);
    n8n_trigger_event('proposal_created', $proposalData);
}

// Proposal Update
hooks()->add_action('after_proposal_updated', 'n8n_proposal_updated_hook');
function n8n_proposal_updated_hook($id)
{
    $CI = &get_instance();
    $proposalData = $CI->proposals_model->get($id);
    n8n_trigger_event('proposal_updated', $proposalData);
}

// Proposal Delete
hooks()->add_action('before_proposal_deleted', 'n8n_proposal_deleted_hook');
function n8n_proposal_deleted_hook($id)
{
    $CI = &get_instance();
    $proposalData = $CI->proposals_model->get($id);
    n8n_trigger_event('proposal_deleted', $proposalData);
}

// Proposal Accepted
hooks()->add_action('proposal_accepted', 'n8n_proposal_accepted_hook');
function n8n_proposal_accepted_hook($id)
{
    $CI = &get_instance();
    $proposalData = $CI->proposals_model->get($id);
    n8n_trigger_event('proposal_accepted', $proposalData);
}

// Proposal Declined
hooks()->add_action('proposal_declined', 'n8n_proposal_declined_hook');
function n8n_proposal_declined_hook($id)
{
    $CI = &get_instance();
    $proposalData = $CI->proposals_model->get($id);
    n8n_trigger_event('proposal_declined', $proposalData);
}
/* Proposals webhooks : End */

/* Projects webhooks : Start */
// Project Create
hooks()->add_action('after_add_project', 'n8n_project_created_hook');
function n8n_project_created_hook($id)
{
    $CI = &get_instance();
    $projectData = $CI->projects_model->get($id);
    n8n_trigger_event('project_created', $projectData);
}

// Project Update
hooks()->add_action('after_update_project', 'n8n_project_updated_hook');
function n8n_project_updated_hook($id)
{
    $CI = &get_instance();
    $projectData = $CI->projects_model->get($id);
    n8n_trigger_event('project_updated', $projectData);
}

// Project Delete
hooks()->add_action('before_project_deleted', 'n8n_project_deleted_hook');
function n8n_project_deleted_hook($id)
{
    $CI = &get_instance();
    $projectData = $CI->projects_model->get($id);
    n8n_trigger_event('project_deleted', $projectData);
}

// Project status changed
hooks()->add_action('project_status_changed', 'n8n_project_status_changed_hook');
function n8n_project_status_changed_hook($data)
{
    $CI = &get_instance();
    $projectData = $CI->projects_model->get($data['project_id']);
    n8n_trigger_event('project_status_changed', $projectData);
}
/* Projects webhooks : End */

/* Tasks webhooks : Start */
// Task Create
hooks()->add_action('after_add_task', 'n8n_task_created_hook');
function n8n_task_created_hook($id)
{
    $CI = &get_instance();
    $taskData = $CI->tasks_model->get($id);
    n8n_trigger_event('task_created', $taskData);
}

// Task Update
hooks()->add_action('after_update_task', 'n8n_task_updated_hook');
function n8n_task_updated_hook($id)
{
    $CI = &get_instance();
    $taskData = $CI->tasks_model->get($id);
    n8n_trigger_event('task_updated', $taskData);
}

// Task Delete
hooks()->add_action('task_deleted', 'n8n_task_deleted_hook');
function n8n_task_deleted_hook($id)
{
    $CI = &get_instance();
    $taskData = $CI->tasks_model->get($id);
    n8n_trigger_event('task_deleted', $taskData);
}

// Task Status Change
hooks()->add_action('task_status_changed', 'n8n_task_status_changed_hook');
function n8n_task_status_changed_hook($data)
{
    $CI = &get_instance();
    $taskData = $CI->tasks_model->get($data['task_id']);
    n8n_trigger_event('task_status_changed', $taskData);
}
/* Tasks webhooks : End */

/* Ticket webhooks : Start */
// Ticket Create
hooks()->add_action('ticket_created', 'n8n_ticket_created_hook');
function n8n_ticket_created_hook($id)
{
    $CI = &get_instance();
    $ticketData = $CI->tickets_model->get($id);
    n8n_trigger_event('ticket_created', $ticketData);
}

// Ticket Delete
hooks()->add_action('before_ticket_deleted', 'n8n_ticket_deleted_hook');
function n8n_ticket_deleted_hook($id)
{
    $CI = &get_instance();
    $ticketData = $CI->tickets_model->get($id);
    n8n_trigger_event('ticket_deleted', $ticketData);
}

// Ticket Status Changed
hooks()->add_action('after_ticket_status_changed', 'n8n_ticket_status_changed_hook');
function n8n_ticket_status_changed_hook($data)
{
    $CI = &get_instance();
    $ticketData = $CI->tickets_model->get($data['id']);
    n8n_trigger_event('ticket_status_changed', $ticketData);
}

// Ticket reply added
hooks()->add_action('after_ticket_reply_added', 'n8n_ticket_reply_added_hook');
function n8n_ticket_reply_added_hook($data)
{
    $CI = &get_instance();
    $ticketData = $CI->tickets_model->get($data['id']);
    n8n_trigger_event('ticket_reply_added', $ticketData);
}
/* Ticket webhooks : End */

/* Contracts webhooks : Start */
// Contract Create
hooks()->add_action('after_contract_added', 'n8n_contracts_created_hook');
function n8n_contracts_created_hook($id)
{
    $CI = &get_instance();
    $contractData = $CI->contracts_model->get($id);
    n8n_trigger_event('contract_created', $contractData);
}

// Contract Update
hooks()->add_action('after_contract_updated', 'n8n_contracts_updated_hook');
function n8n_contracts_updated_hook($id)
{
    $CI = &get_instance();
    $contractData = $CI->contracts_model->get($id);
    n8n_trigger_event('contract_updated', $contractData);
}

// Contract Delete
hooks()->add_action('before_contract_deleted', 'n8n_contracts_deleted_hook');
function n8n_contracts_deleted_hook($id)
{
    $CI = &get_instance();
    $contractData = $CI->contracts_model->get($id);
    n8n_trigger_event('contract_deleted', $contractData);
}
/* Contracts webhooks : End */

/* Expense webhooks : Start */
// Expense Create
hooks()->add_action('after_expense_added', 'n8n_expense_created_hook');
function n8n_expense_created_hook($id)
{
    $CI = &get_instance();
    $expneseData = $CI->expenses_model->get($id);
    n8n_trigger_event('expense_created', $expneseData);
}

// Expense Update
hooks()->add_action('expense_updated', 'n8n_expense_updated_hook');
function n8n_expense_updated_hook($data)
{
    $CI = &get_instance();
    $expneseData = $CI->expenses_model->get($data['id']);
    n8n_trigger_event('expense_updated', $expneseData);
}
/* Expense webhooks : End */

/* Staff webhooks : Start */
// Staff create
hooks()->add_action('staff_member_created', 'n8n_staff_created_hook');
function n8n_staff_created_hook($id)
{
    $CI = &get_instance();
    $staffData = $CI->staff_model->get($id);
    n8n_trigger_event('staff_created', $staffData);
}

// Staff update
hooks()->add_action('staff_member_updated', 'n8n_staff_updated_hook');
function n8n_staff_updated_hook($id)
{
    $CI = &get_instance();
    $staffData = $CI->staff_model->get($id);
    n8n_trigger_event('staff_updated', $staffData);
}

// Staff delete
hooks()->add_action('before_delete_staff_member', 'n8n_staff_deleted_hook');
function n8n_staff_deleted_hook($data)
{
    $CI = &get_instance();
    $staffData = $CI->staff_model->get($data['id']);
    n8n_trigger_event('staff_deleted', $staffData);
}
/* Staff webhooks : End */

/* Credit notes webhooks : Start */
// Credit notes create
hooks()->add_action('after_create_credit_note', 'n8n_credit_note_created_hook');
function n8n_credit_note_created_hook($id)
{
    $CI = &get_instance();
    $creditNoteData = $CI->credit_notes_model->get($id);
    n8n_trigger_event('credit_note_created', $creditNoteData);
}

// Credit notes update
hooks()->add_action('after_update_credit_note', 'n8n_credit_note_updated_hook');
function n8n_credit_note_updated_hook($id)
{
    $CI = &get_instance();
    $creditNoteData = $CI->credit_notes_model->get($id);
    n8n_trigger_event('credit_note_updated', $creditNoteData);
}

// Credit notes delete
hooks()->add_action('before_credit_note_deleted', 'n8n_credit_note_deleted_hook');
function n8n_credit_note_deleted_hook($id)
{
    $CI = &get_instance();
    $creditNoteData = $CI->credit_notes_model->get($id);
    n8n_trigger_event('credit_note_deleted', $creditNoteData);
}
/* Credit notes webhooks : End */

/**
 * Module activated hook
 */
hooks()->add_action('module_activated', 'n8n_connector_module_activated');
function n8n_connector_module_activated($module_name)
{
    if ($module_name == N8N_CONNECTOR_MODULE) {
        // Set module as enabled
        update_option('n8n_connector_enabled', 1);
        log_activity('n8n Connector module activated');
    }
}

hooks()->add_action('module_deactivated', 'n8n_connector_module_deactivated');
function n8n_connector_module_deactivated() {
    // add your logic here

}


/**
 * Cron hook - Process queued webhooks and cleanup old logs
 */
hooks()->add_action('after_cron_run', 'n8n_connector_after_cron_run');
function n8n_connector_after_cron_run()
{
    $CI = &get_instance();

    // Auto remove old logs if enabled
    if (get_option('n8n_connector_auto_remove_logs') == 1) {
        $CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_logs_model', 'n8n_logs_model');
        $retention_days = get_option('n8n_connector_log_retention_days', 30);

        $deleted_count = $CI->n8n_logs_model->clear_old_logs($retention_days);

        if ($deleted_count > 0) {
            log_activity('n8n Connector: Auto-removed ' . $deleted_count . ' old logs (older than ' . $retention_days . ' days)');
        }
    }

    // Process queued webhooks (from webhooks with run_in_background enabled)
    $CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_queue_model', 'n8n_queue_model');
    $CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_webhooks_model', 'n8n_webhooks_model');
    $CI->load->library(N8N_CONNECTOR_MODULE . '/N8n_http_client');

    // Get retry settings from options
    $retry_enabled = get_option('n8n_connector_retry_enabled', 1);
    $max_retries = get_option('n8n_connector_max_retries', 3);
    $retry_delay = get_option('n8n_connector_retry_delay', 60);
    $timeout = get_option('n8n_connector_default_timeout', 5);

    // Configure HTTP client
    $CI->n8n_http_client->set_timeout($timeout);
    if ($retry_enabled) {
        $CI->n8n_http_client->set_max_retries($max_retries);
        $CI->n8n_http_client->set_retry_delay($retry_delay);
    } else {
        $CI->n8n_http_client->set_max_retries(1); // No retries
    }

    // Process pending webhooks (batch of 20)
    $pending_jobs = $CI->n8n_queue_model->get_pending_jobs(20);
    if (!empty($pending_jobs)) {
        foreach ($pending_jobs as $job) {
            n8n_process_queued_webhook($job);
        }
    }

    // Process retry webhooks (batch of 10)
    $retry_jobs = $CI->n8n_queue_model->get_retry_jobs(10);

    if (!empty($retry_jobs)) {
        foreach ($retry_jobs as $job) {
            n8n_process_queued_webhook($job);
        }
    }
}

/**
 * Process a single queued webhook job
 *
 * @param object $job Job object from queue
 */
function n8n_process_queued_webhook($job)
{
    $CI = &get_instance();

    try {
        // Get webhook configuration
        $webhook = $CI->n8n_webhooks_model->get($job->webhook_id);

        if (!$webhook || !$webhook->is_active) {
            $CI->n8n_queue_model->mark_failed(
                $job->id,
                'Webhook not found or inactive',
                null,
                null
            );
            return;
        }

        // Decode payload
        $payload = json_decode($job->payload, true);

        // Decode request headers
        $request_headers = $job->request_headers ? json_decode($job->request_headers, true) : [];

        // Merge with webhook custom headers
        if (!empty($webhook->custom_headers)) {
            $custom_headers = is_string($webhook->custom_headers) ? json_decode($webhook->custom_headers, true) : $webhook->custom_headers;
            if (is_array($custom_headers)) {
                $request_headers = array_merge($request_headers, $custom_headers);
            }
        }

        // Execute webhook using send_webhook method
        $start_time = microtime(true);

        $response = $CI->n8n_http_client->send_webhook(
            $webhook->webhook_url,
            $payload,
            $request_headers,
            $webhook->secret_key,
            $webhook->id
        );

        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000); // Convert to milliseconds

        // Check response
        if ($response['success']) {
            // Success
            $CI->n8n_queue_model->mark_completed(
                $job->queue_id,
                $response['status_code'],
                isset($response['response_body']) ? $response['response_body'] : '',
                $response_time
            );

            // Update webhook stats
            $CI->db->where('id', $webhook->id);
            $CI->db->set('total_triggers', 'total_triggers + 1', false);
            $CI->db->set('last_triggered_at', \Carbon\Carbon::now()->toDateTimeString());
            $CI->db->update(db_prefix() . 'n8n_webhooks');
        } else {
            // Failed
            $CI->n8n_queue_model->mark_failed(
                $job->queue_id,
                $response['error'],
                isset($response['status_code']) ? $response['status_code'] : null,
                isset($response['response_body']) ? $response['response_body'] : null
            );

            // Update webhook failure stats
            $CI->db->where('id', $webhook->id);
            $CI->db->set('total_failures', 'total_failures + 1', false);
            $CI->db->update(db_prefix() . 'n8n_webhooks');
        }
    } catch (Exception $e) {
        // Exception during execution
        $error_msg = 'Exception: ' . $e->getMessage();

        $CI->n8n_queue_model->mark_failed(
            $job->queue_id,
            $error_msg,
            null,
            null
        );
    }
}
