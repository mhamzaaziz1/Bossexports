<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'aging_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "aging_settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `value` mediumtext NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    
    // Default settings
    $CI->db->insert(db_prefix() . 'aging_settings', ['name' => 'aging_cron_period', 'value' => 'weekly']);
    $CI->db->insert(db_prefix() . 'aging_settings', ['name' => 'aging_target_employee', 'value' => '']);
    $CI->db->insert(db_prefix() . 'aging_settings', ['name' => 'aging_last_sent', 'value' => '0']);
}
