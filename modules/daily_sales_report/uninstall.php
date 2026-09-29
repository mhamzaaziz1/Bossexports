<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if ($CI->db->table_exists(db_prefix() . 'daily_sales_reports')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'daily_sales_reports`');
}

if ($CI->db->table_exists(db_prefix() . 'daily_sales_report_logs')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'daily_sales_report_logs`');
}

delete_option('daily_sales_report_emails');
delete_option('daily_sales_report_items');
delete_option('daily_sales_report_last_run');
