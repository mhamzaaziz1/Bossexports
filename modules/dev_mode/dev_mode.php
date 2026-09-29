<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Dev Mode
Description: Toggle development and production environment from the header bar.
Version: 1.0.0
Author: Hamza
*/

define('DEV_MODE_MODULE_NAME', 'dev_mode');

hooks()->add_action('admin_init', 'dev_mode_init_permissions');
hooks()->add_action('admin_navbar_start', 'dev_mode_render_navbar_toggle');

/**
 * Initialize permissions
 */
function dev_mode_init_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => 'View & Toggle Dev Mode',
        ],
    ];
    register_staff_capabilities('dev_mode', $capabilities, 'Dev Mode');
}

/**
 * Render the toggle button in the navbar
 */
function dev_mode_render_navbar_toggle()
{
    if (has_permission('dev_mode', '', 'view')) {
        $is_dev = ENVIRONMENT == 'development';
        $url = admin_url('dev_mode/switch/' . ($is_dev ? 'production' : 'development'));
        $label = $is_dev ? 'Dev Mode: ON' : 'Dev Mode: OFF';
        
        // Vibrant colors for the toggle
        $color = $is_dev ? '#ff2d42' : '#848484';
        $icon = $is_dev ? 'fa-solid fa-toggle-on' : 'fa-solid fa-toggle-off';
        
        echo '<li class="icon header-dev-mode" data-toggle="tooltip" title="Switch to ' . ($is_dev ? 'Production' : 'Development') . ' Mode" data-placement="bottom">
                <a href="' . $url . '" style="color: ' . $color . ' !important; display: flex; align-items: center;">
                    <i class="' . $icon . ' fa-lg"></i>
                    <span class="tw-ml-2 tw-font-medium visible-lg">' . $label . '</span>
                </a>
              </li>';
    }
}
