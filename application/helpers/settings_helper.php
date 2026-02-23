<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Add option
 *
 * @since  Version 1.0.1
 *
 * @param string $name     Option name (required|unique)
 * @param string $value    Option value
 * @param int    $autoload Whether to autoload this option
 */
function add_option($name, $value = '', $autoload = 1)
{
    if (! option_exists($name)) {
        $CI = &get_instance();

        $newData = [
            'name'  => $name,
            'value' => $value,
        ];

        if ($CI->db->field_exists('autoload', db_prefix() . 'options')) {
            $newData['autoload'] = $autoload;
        }

        $CI->db->insert(db_prefix() . 'options', $newData);

        $insert_id = $CI->db->insert_id();

        return (bool) ($insert_id);
    }

    return false;
}

/**
 * Get option value
 *
 * @param string $name Option name
 *
 * @return mixed
 */
function get_option($name)
{
    $CI = &get_instance();

    if (! class_exists('app', false)) {
        $CI->load->library('app');
    }

    return $CI->app->get_option($name);
}

/**
 * Updates option by name
 *
 * @param string $name     Option name
 * @param string $value    Option Value
 * @param mixed  $autoload Whether to update the autoload
 *
 * @return bool
 */
function update_option($name, $value, $autoload = null)
{
    /**
     * Create the option if not exists
     *
     * @since  2.3.3
     */
    if (! option_exists($name)) {
        return add_option($name, $value, $autoload === null ? 1 : 0);
    }

    $CI = &get_instance();

    $CI->db->where('name', $name);
    $data = ['value' => $value];

    if ($autoload) {
        $data['autoload'] = $autoload;
    }

    $CI->db->update(db_prefix() . 'options', $data);

    return (bool) ($CI->db->affected_rows() > 0);
}

/**
 * Delete option
 *
 * @since  Version 1.0.4
 *
 * @param mixed $name option name
 *
 * @return bool
 */
function delete_option($name)
{
    $CI = &get_instance();
    $CI->db->where('name', $name);
    $CI->db->delete(db_prefix() . 'options');

    return (bool) $CI->db->affected_rows();
}

/**
 * @since  2.3.3
 * Check whether an option exists
 *
 * @param string $name option name
 *
 * @return bool
 */
function option_exists($name)
{
    return total_rows(db_prefix() . 'options', [
        'name' => $name,
    ]) > 0;
}

function app_init_settings_tabs()
{
    $CI = &get_instance();

    // General Settings Group - flattened
    $CI->app->add_settings_section('general', [
        'name'     => _l('settings_group_general'),
        'view'     => 'admin/settings/includes/general',
        'position' => 1,
        'icon'     => 'fa fa-cog',
    ]);

    $CI->app->add_settings_section('company', [
        'name'     => _l('company_information'),
        'view'     => 'admin/settings/includes/company',
        'position' => 2,
        'icon'     => 'fa-solid fa-bars-staggered',
    ]);

    $CI->app->add_settings_section('localization', [
        'name'     => _l('settings_group_localization'),
        'view'     => 'admin/settings/includes/localization',
        'position' => 3,
        'icon'     => 'fa-solid fa-globe',
    ]);

    $CI->app->add_settings_section('email', [
        'name'     => _l('settings_group_email'),
        'view'     => 'admin/settings/includes/email',
        'position' => 4,
        'icon'     => 'fa-regular fa-envelope',
    ]);

    // Finance/Sales Group - flattened
    $CI->app->add_settings_section('sales_general', [
        'name'     => _l('settings_sales_general'),
        'view'     => 'admin/settings/includes/sales_general',
        'position' => 5,
        'icon'     => 'fa fa-cog',
    ]);

    $CI->app->add_settings_section('invoices', [
        'name'     => _l('invoices'),
        'view'     => 'admin/settings/includes/invoices',
        'position' => 6,
        'icon'     => 'fa fa-file-invoice',
    ]);

    $CI->app->add_settings_section('proposals', [
        'name'     => _l('proposals'),
        'view'     => 'admin/settings/includes/proposals',
        'position' => 7,
        'icon'     => 'fa-regular fa-file-powerpoint',
    ]);

    $CI->app->add_settings_section('estimates', [
        'name'     => _l('estimates'),
        'view'     => 'admin/settings/includes/estimates',
        'position' => 8,
        'icon'     => 'fa-regular fa-file',
    ]);

    $CI->app->add_settings_section('credit_notes', [
        'name'     => _l('credit_notes'),
        'view'     => 'admin/settings/includes/credit_notes',
        'position' => 9,
        'icon'     => 'fa-regular fa-file-lines',
    ]);

    $CI->app->add_settings_section('subscriptions', [
        'name'     => _l('subscriptions'),
        'view'     => 'admin/settings/includes/subscriptions',
        'position' => 10,
        'icon'     => 'fa fa-repeat',
    ]);

    $CI->app->add_settings_section('payment_gateways', [
        'name'     => _l('settings_group_online_payment_modes'),
        'view'     => 'admin/settings/includes/payment_gateways',
        'position' => 11,
        'icon'     => 'fa-regular fa-credit-card',
    ]);

    // Configuration Group - flattened
    $CI->app->add_settings_section('clients', [
        'name'     => _l('settings_group_clients'),
        'view'     => 'admin/settings/includes/clients',
        'position' => 12,
        'icon'     => 'fa-regular fa-user',
    ]);

    $CI->app->add_settings_section('tasks', [
        'name'     => _l('tasks'),
        'view'     => 'admin/settings/includes/tasks',
        'position' => 13,
        'icon'     => 'fa-regular fa-circle-check',
    ]);

    $CI->app->add_settings_section('tickets', [
        'name'     => _l('support'),
        'view'     => 'admin/settings/includes/tickets',
        'position' => 14,
        'icon'     => 'fa-regular fa-life-ring',
    ]);

    $CI->app->add_settings_section('leads', [
        'name'     => _l('leads'),
        'view'     => 'admin/settings/includes/leads',
        'position' => 15,
        'icon'     => 'fa-solid fa-crosshairs',
    ]);

    // Integrations Group - flattened
    $CI->app->add_settings_section('google', [
        'name'     => 'Google',
        'view'     => 'admin/settings/includes/google',
        'position' => 16,
        'icon'     => 'fa-brands fa-google',
    ]);

    $CI->app->add_settings_section('pusher', [
        'name'     => 'Pusher.com',
        'view'     => 'admin/settings/includes/pusher',
        'position' => 17,
        'icon'     => 'fa-regular fa-bell',
    ]);

    // Other Group - flattened
    $CI->app->add_settings_section('calendar', [
        'name'     => _l('settings_calendar'),
        'view'     => 'admin/settings/includes/calendar',
        'position' => 18,
        'icon'     => 'fa-regular fa-calendar',
    ]);

    $CI->app->add_settings_section('pdf', [
        'name'     => _l('settings_pdf'),
        'view'     => 'admin/settings/includes/pdf',
        'position' => 19,
        'icon'     => 'fa-regular fa-file-pdf',
    ]);

    $CI->app->add_settings_section('e_sign', [
        'name'     => 'E-Sign',
        'view'     => 'admin/settings/includes/e_sign',
        'position' => 20,
        'icon'     => 'fa-solid fa-signature',
    ]);

    $CI->app->add_settings_section('tags', [
        'name'     => _l('tags'),
        'view'     => 'admin/settings/includes/tags',
        'position' => 21,
        'icon'     => 'fa-solid fa-tags',
    ]);

    // Login Settings
    $CI->app->add_settings_section('login_settings', [
        'name'     => 'Login Settings',
        'view'     => 'admin/settings/includes/login_settings',
        'position' => 22,
        'icon'     => 'fa-solid fa-right-to-bracket',
    ]);

    // Misc Group - flattened
    $CI->app->add_settings_section('cronjob', [
        'name'     => _l('settings_group_cronjob'),
        'view'     => 'admin/settings/includes/cronjob',
        'position' => 23,
        'icon'     => 'fa-solid fa-microchip',
    ]);

    $CI->app->add_settings_section('misc', [
        'name'     => _l('settings_group_misc'),
        'view'     => 'admin/settings/includes/misc',
        'position' => 24,
        'icon'     => 'fa-solid fa-gears',
    ]);

    // AI
    $CI->app->add_settings_section('ai', [
        'name'     => _l('settings_ai_general'),
        'view'     => 'admin/settings/includes/ai',
        'position' => 25,
        'icon'     => 'fa fa-cog',
    ]);

    // Dev Mode
    $CI->app->add_settings_section('dev_mode', [
        'name'     => 'Dev Mode',
        'view'     => 'admin/settings/includes/dev_mode',
        'position' => 26,
        'icon'     => 'fa fa-code',
    ]);
}
