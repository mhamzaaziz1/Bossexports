<?php

defined('BASEPATH') or exit('No direct script access allowed');

function cfm_seed_default_menus()
{
    $CI = &get_instance();
    $CI->load->model('custom_flyout_menu/custom_flyout_menu_model');
    
    // Check if SaaS module is active
    $saas_active = false;
    $is_tenant = false;
    
    if (function_exists('perfex_saas_is_tenant')) {
        $is_tenant = perfex_saas_is_tenant();
    }
    
    if (isset($CI->app_modules) && method_exists($CI->app_modules, 'is_active')) {
        $saas_active = $CI->app_modules->is_active('perfex_saas');
    }
    
    // ========== ADMIN MENU ==========
    
    // Admin Groups
    $admin_groups = [
        ['area' => 'admin', 'name' => 'SALES', 'slug' => 'sales', 'sort_order' => 10, 'active' => 1],
        ['area' => 'admin', 'name' => 'SUPPORT', 'slug' => 'support', 'sort_order' => 20, 'active' => 1],
        ['area' => 'admin', 'name' => 'MANAGEMENT', 'slug' => 'management', 'sort_order' => 30, 'active' => 1],
        ['area' => 'admin', 'name' => 'REPORTS', 'slug' => 'reports', 'sort_order' => 40, 'active' => 1],
    ];
    
    if ($saas_active && !$is_tenant) {
        $admin_groups[] = ['area' => 'admin', 'name' => 'SAAS', 'slug' => 'saas', 'sort_order' => 5, 'active' => 1];
    }
    
    $group_ids = [];
    foreach ($admin_groups as $group) {
        $group['created_at'] = date('Y-m-d H:i:s');
        $group['updated_at'] = date('Y-m-d H:i:s');
        $group_ids[$group['slug']] = $CI->custom_flyout_menu_model->save_group($group);
    }
    
    // Admin Menu Items
    $admin_items = [
        // Sales Group
        ['area' => 'admin', 'group_id' => $group_ids['sales'], 'label' => 'Customers', 'icon' => 'ph-users', 'url' => 'clients', 'sort_order' => 10, 'permission' => 'customers'],
        ['area' => 'admin', 'group_id' => $group_ids['sales'], 'label' => 'Invoices', 'icon' => 'ph-file-text', 'url' => 'invoices', 'sort_order' => 20, 'permission' => 'invoices'],
        ['area' => 'admin', 'group_id' => $group_ids['sales'], 'label' => 'Estimates', 'icon' => 'ph-receipt', 'url' => 'estimates', 'sort_order' => 30, 'permission' => 'estimates'],
        ['area' => 'admin', 'group_id' => $group_ids['sales'], 'label' => 'Proposals', 'icon' => 'ph-presentation', 'url' => 'proposals', 'sort_order' => 40, 'permission' => 'proposals'],
        ['area' => 'admin', 'group_id' => $group_ids['sales'], 'label' => 'Payments', 'icon' => 'ph-credit-card', 'url' => 'payments', 'sort_order' => 50, 'permission' => 'payments'],
        
        // Support Group
        ['area' => 'admin', 'group_id' => $group_ids['support'], 'label' => 'Tickets', 'icon' => 'ph-ticket', 'url' => 'tickets', 'sort_order' => 10, 'permission' => 'support'],
        ['area' => 'admin', 'group_id' => $group_ids['support'], 'label' => 'Tasks', 'icon' => 'ph-check-square', 'url' => 'tasks', 'sort_order' => 20, 'permission' => 'tasks'],
        ['area' => 'admin', 'group_id' => $group_ids['support'], 'label' => 'Projects', 'icon' => 'ph-folder-open', 'url' => 'projects', 'sort_order' => 30, 'permission' => 'projects'],
        ['area' => 'admin', 'group_id' => $group_ids['support'], 'label' => 'Leads', 'icon' => 'ph-chart-line-up', 'url' => 'leads', 'sort_order' => 40, 'permission' => 'leads'],
        
        // Management Group
        ['area' => 'admin', 'group_id' => $group_ids['management'], 'label' => 'Staff', 'icon' => 'ph-user-circle', 'url' => 'staff', 'sort_order' => 10, 'permission' => 'staff'],
        ['area' => 'admin', 'group_id' => $group_ids['management'], 'label' => 'Contracts', 'icon' => 'ph-file-text', 'url' => 'contracts', 'sort_order' => 20, 'permission' => 'contracts'],
        ['area' => 'admin', 'group_id' => $group_ids['management'], 'label' => 'Subscriptions', 'icon' => 'ph-repeat', 'url' => 'subscriptions', 'sort_order' => 30, 'permission' => 'subscriptions'],
        ['area' => 'admin', 'group_id' => $group_ids['management'], 'label' => 'Expenses', 'icon' => 'ph-wallet', 'url' => 'expenses', 'sort_order' => 40, 'permission' => 'expenses'],
        
        // Reports Group
        ['area' => 'admin', 'group_id' => $group_ids['reports'], 'label' => 'Sales Report', 'icon' => 'ph-chart-bar', 'url' => 'reports/sales', 'sort_order' => 10, 'permission' => 'reports'],
        ['area' => 'admin', 'group_id' => $group_ids['reports'], 'label' => 'Expenses Report', 'icon' => 'ph-chart-pie', 'url' => 'reports/expenses', 'sort_order' => 20, 'permission' => 'reports'],
        ['area' => 'admin', 'group_id' => $group_ids['reports'], 'label' => 'Leads Report', 'icon' => 'ph-chart-line', 'url' => 'reports/leads', 'sort_order' => 30, 'permission' => 'reports'],
    ];
    
    // Add SaaS items if active
    if ($saas_active && !$is_tenant && isset($group_ids['saas'])) {
        $admin_items[] = ['area' => 'admin', 'group_id' => $group_ids['saas'], 'label' => 'Companies', 'icon' => 'ph-buildings', 'url' => 'perfex_saas/companies', 'sort_order' => 10, 'permission' => 'perfex_saas'];
        $admin_items[] = ['area' => 'admin', 'group_id' => $group_ids['saas'], 'label' => 'Packages', 'icon' => 'ph-package', 'url' => 'perfex_saas/packages', 'sort_order' => 20, 'permission' => 'perfex_saas'];
        $admin_items[] = ['area' => 'admin', 'group_id' => $group_ids['saas'], 'label' => 'Subscriptions', 'icon' => 'ph-credit-card', 'url' => 'perfex_saas/subscriptions', 'sort_order' => 30, 'permission' => 'perfex_saas'];
    }
    
    foreach ($admin_items as $item) {
        $item['created_at'] = date('Y-m-d H:i:s');
        $item['updated_at'] = date('Y-m-d H:i:s');
        $item['active'] = 1;
        $item['has_children'] = 0;
        $item['parent_id'] = null;
        $CI->custom_flyout_menu_model->save_item($item);
    }
    
    // ========== CLIENT MENU ==========
    
    // Client Groups
    $client_groups = [
        ['area' => 'client', 'name' => 'BILLING', 'slug' => 'billing', 'sort_order' => 10, 'active' => 1],
        ['area' => 'client', 'name' => 'PROJECTS', 'slug' => 'projects', 'sort_order' => 20, 'active' => 1],
        ['area' => 'client', 'name' => 'SUPPORT', 'slug' => 'support', 'sort_order' => 30, 'active' => 1],
    ];
    
    $client_group_ids = [];
    foreach ($client_groups as $group) {
        $group['created_at'] = date('Y-m-d H:i:s');
        $group['updated_at'] = date('Y-m-d H:i:s');
        $client_group_ids[$group['slug']] = $CI->custom_flyout_menu_model->save_group($group);
    }
    
    // Client Menu Items
    $client_items = [
        // Billing Group
        ['area' => 'client', 'group_id' => $client_group_ids['billing'], 'label' => 'Invoices', 'icon' => 'ph-file-text', 'url' => 'invoices', 'sort_order' => 10],
        ['area' => 'client', 'group_id' => $client_group_ids['billing'], 'label' => 'Estimates', 'icon' => 'ph-receipt', 'url' => 'estimates', 'sort_order' => 20],
        ['area' => 'client', 'group_id' => $client_group_ids['billing'], 'label' => 'Payments', 'icon' => 'ph-credit-card', 'url' => 'payments', 'sort_order' => 30],
        ['area' => 'client', 'group_id' => $client_group_ids['billing'], 'label' => 'Subscriptions', 'icon' => 'ph-repeat', 'url' => 'subscriptions', 'sort_order' => 40],
        
        // Projects Group
        ['area' => 'client', 'group_id' => $client_group_ids['projects'], 'label' => 'Projects', 'icon' => 'ph-folder-open', 'url' => 'projects', 'sort_order' => 10],
        ['area' => 'client', 'group_id' => $client_group_ids['projects'], 'label' => 'Tasks', 'icon' => 'ph-check-square', 'url' => 'tasks', 'sort_order' => 20],
        ['area' => 'client', 'group_id' => $client_group_ids['projects'], 'label' => 'Proposals', 'icon' => 'ph-presentation', 'url' => 'proposals', 'sort_order' => 30],
        
        // Support Group
        ['area' => 'client', 'group_id' => $client_group_ids['support'], 'label' => 'Tickets', 'icon' => 'ph-ticket', 'url' => 'tickets', 'sort_order' => 10],
        ['area' => 'client', 'group_id' => $client_group_ids['support'], 'label' => 'Contracts', 'icon' => 'ph-file-text', 'url' => 'contracts', 'sort_order' => 20],
    ];
    
    foreach ($client_items as $item) {
        $item['created_at'] = date('Y-m-d H:i:s');
        $item['updated_at'] = date('Y-m-d H:i:s');
        $item['active'] = 1;
        $item['has_children'] = 0;
        $item['parent_id'] = null;
        $item['permission'] = null;
        $CI->custom_flyout_menu_model->save_item($item);
    }
}
