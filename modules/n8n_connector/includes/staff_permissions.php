<?php
defined('BASEPATH') || exit('No direct script access allowed');

hooks()->add_filter('staff_permissions', 'n8n_module_permissions_for_staff');
function n8n_module_permissions_for_staff($permissions)
{
    $allPermissionsArray = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $permissions['n8n_connector'] = [
        'name'         => _l('n8n_connector_webhook'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['n8n_webhook_log'] = [
        'name'         => _l('n8n_connector_webhook_logs'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $permissions['n8n_queue'] = [
        'name'         => _l('n8n_connector_queue'),
        'capabilities' => [
            'view'   => _l('permission_view'),
            'delete' => _l('permission_delete'),
        ],
    ];

    return $permissions;
}
