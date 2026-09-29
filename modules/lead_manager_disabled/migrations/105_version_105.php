<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if (!$CI->db->field_exists('sms_date', db_prefix() . 'lead_manager_conversation')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "lead_manager_conversation` ADD `sms_date` DATETIME NULL DEFAULT NULL AFTER `is_read`");
        }
        if ($CI->db->field_exists('template_id', db_prefix() . 'lead_manager_whatsapp_templates')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "lead_manager_whatsapp_templates` CHANGE `template_id` `template_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'id from api'");
        }
        if (!$CI->db->field_exists('sms_date', db_prefix() . 'lead_manager_whatsapp')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "lead_manager_whatsapp` ADD `sms_date` DATETIME NULL DEFAULT NULL AFTER `is_files`");
        }
    }
}