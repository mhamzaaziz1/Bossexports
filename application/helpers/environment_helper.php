<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Initialize environment mode setting
 * Adds the environment_mode option if it doesn't exist
 */
function init_environment_mode()
{
    if (!option_exists('environment_mode')) {
        add_option('environment_mode', 'production');
    }
}

/**
 * Get the current environment mode
 * @return string The current environment mode (development, testing, or production)
 */
function get_environment_mode()
{
    $mode = get_option('environment_mode');
    
    // Default to production if the option doesn't exist or is invalid
    if (!$mode || !in_array($mode, ['development', 'testing', 'production'])) {
        $mode = 'production';
    }
    
    return $mode;
}