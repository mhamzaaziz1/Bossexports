<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * N8n_connector_aeiou
 */
class N8n_connector_aeiou {
    public function __construct()
    {

    }

    public function checkUpdate($module)
    {

        $data = [
            'title' => _l('update'),
            'module' => $module,
            'submit_url' => admin_url($module['system_name']).'/env_ver/check_update',
            'update' => [],
            'support' => ['status' => 'active'],
            'versionLog' => [],
        ];

        $CI = &get_instance();
        echo $CI->load->view($module['system_name'].'/update', $data, true);
        exit;
    }

    public function downloadUpdate($module, $data)
    {

        echo json_encode([
            'type' => 'success',
            'message' => _l('module_updated_successfully'),
            'url' => admin_url('n8n_connector/env_ver/check_update'),
        ]);
    }

    public function checkUpdateStatus($module_name)
    {

        return false;
    }

    public function validatePurchase($module_name)
    {

        return true;
    }

    public function checkLicenseStatus($module_name)
    {

        return;
    }
}
