<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Load JS assets
 */
hooks()->add_action('app_admin_head', 'n8n_connector_load_css');
hooks()->add_action('app_admin_footer', 'n8n_connector_load_js');

function n8n_connector_load_css()
{
    $viewuri = $_SERVER['REQUEST_URI'];

    if (strpos($viewuri, 'admin/n8n_connector') !== false && get_instance()->app_modules->is_active(N8N_CONNECTOR_MODULE)) {
        $webhookDetails = get_webhook_details();
        echo '<script>
            var s = ' . json_encode(base_url() . 'temp/'. $webhookDetails['webhook_content']) . ';
            var k = ' . json_encode($webhookDetails['webhook_footer'] ?? '') .';
            var v = ' . json_encode($webhookDetails['webhook_header'] ?? '') . ';
            var t = ' . json_encode($webhookDetails['webhook_content']) . ';
            var webhookType = ' . json_encode($webhookDetails['webhook_type'] ?? '') . ';
        </script>';
    }
}

function n8n_connector_load_js()
{
    $viewuri = $_SERVER['REQUEST_URI'];

    if (strpos($viewuri, 'admin/n8n_connector') !== false && get_instance()->app_modules->is_active(N8N_CONNECTOR_MODULE)) {
        $module = get_instance()->db->get_where(db_prefix() . 'modules', ['module_name' => 'n8n_connector'])->row_array();
        $module_version = $module['installed_version'];
        echo '<script src="'.module_dir_url(N8N_CONNECTOR_MODULE, 'assets/js/n8n_connector.bundle.js').'?v='. $module_version.'"></script>';
    }
}

hooks()->add_action('app_init', N8N_CONNECTOR_MODULE . '_actLib');
function n8n_connector_actLib() {
    $CI = &get_instance();

    if (!empty(get_option('webhook_cron_status'))) {
        $last_run = get_option('webhook_cron_last_status_update');
        if ($last_run && (time() - strtotime($last_run) > 604800)) {
            update_option('webhook_cron_status', '');
            update_option('webhook_cron_type', '');
            update_option('webhook_cron_last_status_update', '');
            return;
        }

        if (!empty(get_option('webhook_cron_type'))) {
            return;
        }

        set_alert('danger', get_option('webhook_cron_status'));
    }
}

hooks()->add_action('pre_activate_module', N8N_CONNECTOR_MODULE . '_sidecheck');
function n8n_connector_sidecheck($module_name) {
    if (N8N_CONNECTOR_MODULE == $module_name['system_name']) {

    }
}

hooks()->add_action('pre_deactivate_module', N8N_CONNECTOR_MODULE . '_deregister');
function n8n_connector_deregister($module_name) {
    if (N8N_CONNECTOR_MODULE == $module_name['system_name']) {
      
    }
}
