<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_action('admin_init', 'add_custom_reports_menu_items');
hooks()->add_action('after_invoice_added', 'auto_create_invoice_task');
hooks()->add_action('app_init', 'log_controller_activity');
hooks()->add_action('model_init', 'log_model_activity');

function log_controller_activity() {
    // Singleton check: Ensure we only log the main controller action once per request
    static $logged = false;
    if ($logged) {
        return;
    }

    $CI = & get_instance();
    if(!isset($CI->router)) {
        return;
    }
    $class  = $CI->router->fetch_class();
    $method = $CI->router->fetch_method();
    
    // Avoid recursion or logging logger itself if needed
    if($class == 'activity_logger') return;
    
    // --- Smart Description Generator ---
    $description = "Controller Initialized: $class::$method";
    
    // Common ID extraction (usually segment 3 for admin/module/action/id or admin/module/view/id)
    // admin url is segment 1: admin, 2: module, 3: id or action
    $uri_id = $CI->uri->segment(3);
    if (!is_numeric($uri_id)) {
        $uri_id = $CI->uri->segment(4); // Try next segment
    }

    switch ($class) {
        case 'proposals':
            $entity = 'Sales Quotation'; // User requested "Sales Quote"
            if ($method == 'proposal' || $method == 'index') {
                if (is_numeric($uri_id)) {
                     // If accessing specific proposal
                    $description = "Accessed $entity $uri_id"; 
                } else if ($CI->input->post()) {
                    $description = "Attempted to Create/Update $entity";
                } else {
                    $description = "Accessing $entity List";
                }
            }
            break;

        case 'invoices':
            $entity = 'Invoice';
            if ($method == 'invoice' || $method == 'list_invoices') {
                if (is_numeric($uri_id)) {
                    $description = "Accessed $entity $uri_id";
                } else if ($CI->input->post()) {
                    $description = "Attempted to Create/Update $entity";
                } else {
                    $description = "Accessing $entity List";
                }
            }
            break;

        case 'estimates':
            $entity = 'Sales Order'; // User requested "Sales Order" (prev conversation context)
            if ($method == 'estimate' || $method == 'list_estimates') {
                 if (is_numeric($uri_id)) {
                    $description = "Accessed $entity $uri_id";
                } else if ($CI->input->post()) {
                    $description = "Attempted to Create/Update $entity";
                } else {
                    $description = "Accessing $entity List";
                }
            }
            break;
            
        case 'clients':
             $entity = 'Customer';
             if (is_numeric($uri_id)) {
                $description = "Accessed Profile of $entity $uri_id";
             } else {
                 $description = "Accessing $entity List or Action: $method";
             }
             break;
             
        default:
             // Generic fallback: Try to make it readable
             if (is_numeric($uri_id)) {
                 $description = "Accessed $class ($method) - ID: $uri_id";
             } else {
                 $description = "Accessed $class ($method)";
             }
             break;
    }
    
    // Capture POST data for detailed audit (sanitized) if needed, 
    // but for description we keep it short as requested.
    
    \app\services\ActivityLogger::log($description, null, 'controller', $class, $method);
    
    $logged = true;
}

function log_model_activity($model) {
    if(isset($model) && is_object($model)) {
        $class = get_class($model);
        \app\services\ActivityLogger::log("Model Initialized: $class", null, 'model', $class, 'init');
    }
}

function add_custom_reports_menu_items()
{
    $CI = &get_instance();
    if (has_permission('reports', '', 'view')) {
        // Cashbook Report
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'cashbook-report',
            'name'     => 'Cashbook Report',
            'href'     => admin_url('reports/cashbook'),
            'position' => 30, 
        ]);
        
        // Cashbook Advance
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'cashbook-advance',
            'name'     => 'Cashbook Advance',
            'href'     => admin_url('reports/cashbook2'),
            'position' => 31,
        ]);
        // avg_purchase_aging
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'avg-purchase-aging',
            'name'     => 'avg_purchase_aging',
            'href'     => admin_url('reports/avg_purchase_aging'), 
            'position' => 32,
        ]);
        // Stock full view report
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'stock-full-view-report',
            'name'     => 'Stock full view report',
            'href'     => admin_url('reports/stock_full_view_report'),
            'position' => 33,
        ]);
        // Container-wise Purchase Reconciliation
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'container-wise-purchase-reconciliation',
            'name'     => 'Container-wise Purchase Reconciliation',
            'href'     => admin_url('reports/container_report'),
            'position' => 34,
        ]);
         // Sales Quotes Detail
         $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'sales-quotes-detail',
            'name'     => 'Sales Quotes Detail',
            'href'     => admin_url('reports/sales_quotes_report'),
            'position' => 35,
        ]);
        // Aging List
        if (has_permission('accounting_dashboard', '', 'view')) {
             $CI->app_menu->add_sidebar_children_item('reports', [
                'slug'     => 'aging-list',
                'name'     => 'Aging List',
                'href'     => admin_url('clients/aging'),
                'position' => 36,
            ]);
        }
        
        // Customer Balance Report
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'customer-balance-report',
            'name'     => 'Customer Balance Report',
            'href'     => admin_url('reports/customer_balances'),
            'position' => 37,
        ]);

        // Vendor Balance Report
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'vendor-balance-report',
            'name'     => 'Vendor Balance Report',
            'href'     => admin_url('purchase/vendor_balances'),
            'position' => 38,
        ]);
    }
}
function auto_create_invoice_task($invoice_id)
{
    $CI = &get_instance();
    
    // Log start of process
    \app\services\ActivityLogger::log("Auto Task Process Started for Invoice ID: $invoice_id", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');

    if (get_option('invoice_auto_create_task') != '1') {
        \app\services\ActivityLogger::log("Auto Task Process Aborted: Feature disabled", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
        return; 
    }

    $tasks_json = get_option('invoice_auto_tasks_list');
    
    if (!$tasks_json) {
         \app\services\ActivityLogger::log("Auto Task Process Aborted: No tasks JSON found", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
         return;
    }

    $tasks = json_decode($tasks_json, true);

    if (empty($tasks) || !is_array($tasks)) {
        \app\services\ActivityLogger::log("Auto Task Process Aborted: No tasks configured or invalid JSON", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
        return;
    }

    $CI->load->model('tasks_model');
    $CI->load->model('invoices_model');

    $invoice = $CI->invoices_model->get($invoice_id);
    if (!$invoice) {
        \app\services\ActivityLogger::log("Auto Task Process Failed: Invoice $invoice_id not found", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
        return;
    }

    foreach ($tasks as $task_def) {
        $start_date = date('Y-m-d');
        $due_days   = isset($task_def['due_days']) ? (int)$task_def['due_days'] : 0;
        $due_date   = date('Y-m-d', strtotime('+' . $due_days . ' days'));

        $assignee_option = isset($task_def['assignee']) ? $task_def['assignee'] : 'creator';
        $assignees = [];
        if ($assignee_option == 'creator' && $invoice->addedfrom != 0) {
            $assignees[] = $invoice->addedfrom;
        }

        $task_data = [
            'name'         => $task_def['subject'] ?? 'Auto Task',
            'description'  => $task_def['description'] ?? '',
            'priority'     => $task_def['priority'] ?? 1,
            'startdate'    => $start_date,
            'duedate'      => $due_date,
            'rel_type'     => 'invoice',
            'rel_id'       => $invoice_id,
            'status'       => 1,
            'billable'     => 0,
        ];

        $task_id = $CI->tasks_model->add($task_data);

        if ($task_id) {
             \app\services\ActivityLogger::log("Auto Task Created: ID $task_id for Invoice $invoice_id", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
             
            if (!empty($assignees)) {
                foreach ($assignees as $staff_id) {
                    $CI->tasks_model->add_task_assignees(['taskid' => $task_id, 'assignee' => $staff_id]);
                    \app\services\ActivityLogger::log("Auto Task Assigned: Task $task_id assigned to Staff $staff_id", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
                }
            }
        } else {
             \app\services\ActivityLogger::log("Auto Task Creation Failed for Invoice $invoice_id", null, 'auto_task', 'Invoice', 'auto_create_invoice_task');
        }
    }
}