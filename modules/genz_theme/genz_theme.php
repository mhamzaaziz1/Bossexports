<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: GenZ Theme
Description: Modern and interactive theme with Gen Z aesthetics
Version: 1.0.0
Author: AI Developer
Requires at least: 2.3.2
*/

define('GENZ_THEME_MODULE_NAME', 'genz_theme');
define('GENZ_THEME_CSS', module_dir_path(GENZ_THEME_MODULE_NAME, 'assets/css/genz_styles.css'));

$CI = &get_instance();

/**
 * Register the activation hook
 */
register_activation_hook(GENZ_THEME_MODULE_NAME, 'genz_theme_activation_hook');

/**
 * The activation function
 */
function genz_theme_activation_hook()
{
    require(__DIR__ . '/install.php');
}

/**
 * Register language files
 */
register_language_files(GENZ_THEME_MODULE_NAME, ['genz_theme']);

/**
 * Load the theme helper
 */
$CI->load->helper(GENZ_THEME_MODULE_NAME . '/genz_theme_helper');
