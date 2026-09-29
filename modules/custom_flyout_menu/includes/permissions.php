<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('admin_init', 'cfm_register_permissions');

function cfm_register_permissions()
{
    $capabilities = [];
    
    $capabilities['capabilities'] = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    
    register_staff_capabilities('custom_flyout_menu', $capabilities, _l('cfm_module_name'));
}
