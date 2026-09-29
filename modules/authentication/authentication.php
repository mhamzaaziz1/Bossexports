<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Authentication 
Description: Authentication For Perfex CRM
Version: 1.0.0
Requires at least: 2.3.*
*/


define('AUTH_MODULE', 'authentication');


/**
 * Register activation module hook
 */
register_activation_hook(AUTH_MODULE, 'authentication_activation_hook');

function authentication_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(AUTH_MODULE, [AUTH_MODULE]);

// hooks()->add_action('app_admin_head', function () {
//     echo '<link rel="stylesheet" href="src/output.css">';
// });
// hooks()->add_action('app_customers_head', function () {
//     echo '<link rel="stylesheet" href="src/output.css">';
// });
