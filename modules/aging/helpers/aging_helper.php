<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get aging module option
 * @param  string $name
 * @return mixed
 */
function aging_get_option($name)
{
    $CI = &get_instance();
    $CI->db->where('name', $name);
    $option = $CI->db->get(db_prefix() . 'aging_settings')->row();
    if ($option) {
        return $option->value;
    }
    return '';
}

/**
 * Update aging module option
 * @param  string $name
 * @param  mixed $value
 * @return boolean
 */
function aging_update_option($name, $value)
{
    $CI = &get_instance();
    $CI->db->where('name', $name);
    $CI->db->update(db_prefix() . 'aging_settings', ['value' => $value]);
    return true;
}

/**
 * Generate Aging PDF
 * @param  array $data
 * @return object
 */
function aging_pdf($data)
{
    return app_pdf('aging', module_dir_path(AGING_MODULE_NAME, 'libraries/Aging_pdf.php'), $data);
}
