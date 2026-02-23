<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Decimal Settings
Description: Allow user to select decimal places from 2 to 4 for the whole system calculation
Version: 1.0.0
Requires at least: 2.3.*
*/

define('DECIMAL_SETTINGS_MODULE_NAME', 'decimal_settings');

hooks()->add_action('admin_init', 'decimal_settings_add_settings_tab');
hooks()->add_filter('app_decimal_places', 'decimal_settings_apply_decimal_places');

register_activation_hook(DECIMAL_SETTINGS_MODULE_NAME, 'decimal_settings_module_activation_hook');

function decimal_settings_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function decimal_settings_add_settings_tab()
{
    $CI = &get_instance();
    $CI->app->add_settings_section('decimal_settings', [
        'name'     => 'Decimal Settings',
        'view'     => DECIMAL_SETTINGS_MODULE_NAME . '/decimal_settings_view',
        'position' => 100,
    ]);
}

function decimal_settings_apply_decimal_places($current_places)
{
    $places = get_option('cfg_decimal_places');
    if ($places) {
        return (int)$places;
    }
    return $current_places;
}
