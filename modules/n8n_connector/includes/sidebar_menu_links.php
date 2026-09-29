<?php
defined('BASEPATH') || exit('No direct script access allowed');

hooks()->add_action('admin_init', function ()  {



    $CI = &get_instance();

    if (staff_can('view', 'n8n_connector')) {
        // Main menu item
        $CI->app_menu->add_sidebar_menu_item('n8n_connector', [
            'name'     => _l('n8n_connector'),
            'icon'     => 'fa fa-plug',
            'position' => 30,
        ]);

        // Dashboard
        $CI->app_menu->add_sidebar_children_item('n8n_connector', [
            'slug'     => 'n8n-connector-dashboard',
            'name'     => _l('n8n_dashboard'),
            'icon'     => 'fa fa-tachometer-alt',
            'href'     => admin_url('n8n_connector'),
            'position' => 1,
        ]);
        // Webhooks
        $CI->app_menu->add_sidebar_children_item('n8n_connector', [
            'slug'     => 'n8n-connector-webhooks',
            'name'     => _l('n8n_webhooks'),
            'icon'     => 'fa fa-plug',
            'href'     => admin_url('n8n_connector/webhooks'),
            'position' => 2,
        ]);

        if (staff_can('view', 'n8n_connector') && staff_can('view', 'n8n_webhook_log')) {
            // Logs
            $CI->app_menu->add_sidebar_children_item('n8n_connector', [
                'slug'     => 'n8n-connector-logs',
                'name'     => _l('n8n_logs'),
                'icon'     => 'fa fa-list-alt',
                'href'     => admin_url('n8n_connector/logs'),
                'position' => 3,
            ]);
        }

        if (staff_can('view', 'n8n_connector') && staff_can('view', 'n8n_queue')) {
            // Queue
            $CI->app_menu->add_sidebar_children_item('n8n_connector', [
                'slug'     => 'n8n-connector-queue',
                'name'     => _l('n8n_queue'),
                'icon'     => 'fa fa-tasks',
                'href'     => admin_url('n8n_connector/queue'),
                'position' => 4,
            ]);
        }

        // Settings
        $CI->app_menu->add_sidebar_children_item('n8n_connector', [
            'slug'     => 'n8n-connector-settings',
            'name'     => _l('settings'),
            'icon'     => 'fa fa-cog',
            'href'     => admin_url('n8n_connector/settings'),
            'position' => 5,
        ]);
    }
});

hooks()->add_action('module_deactivated', function ($module_name) {
    if (N8N_CONNECTOR_MODULE == $module_name['system_name']) {

    }
});

get_instance()->load->library(N8N_CONNECTOR_MODULE . '/n8n_connector_aeiou');
$update = get_instance()->n8n_connector_aeiou->checkUpdateStatus(N8N_CONNECTOR_MODULE);
if ($update > 0) {

}
