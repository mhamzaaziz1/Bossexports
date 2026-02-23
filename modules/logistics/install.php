<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Add columns to tblitems (Master Data)
if (!$CI->db->field_exists('weight', db_prefix() . 'items')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "items` ADD `weight` DECIMAL(15,2) DEFAULT '0.00' AFTER `rate`;");
}

if (!$CI->db->field_exists('volume', db_prefix() . 'items')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "items` ADD `volume` DECIMAL(15,2) DEFAULT '0.00' AFTER `weight`;");
}

// Add columns to tblitemable (Transaction Lines)
if (!$CI->db->field_exists('weight', db_prefix() . 'itemable')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "itemable` ADD `weight` DECIMAL(15,2) DEFAULT '0.00' AFTER `qty`;");
}

if (!$CI->db->field_exists('volume', db_prefix() . 'itemable')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "itemable` ADD `volume` DECIMAL(15,2) DEFAULT '0.00' AFTER `weight`;");
}
