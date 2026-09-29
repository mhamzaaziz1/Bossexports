<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if (!$CI->db->table_exists(db_prefix() . 'reminder_activity')) {
  $CI->db->query('DROP TABLE `' . db_prefix() . 'reminder_activity`');
}
if (!$CI->db->field_exists('notify_by_email_client', db_prefix() . 'reminders')) {
  $CI->db->query("ALTER TABLE `".db_prefix()."leads` DROP COLUMN notify_by_email_client");
}
