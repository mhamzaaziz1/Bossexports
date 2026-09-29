<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
     public function up()
     {
          $CI = &get_instance();     
          add_option('acc_integration_zoho_sync_from_system_organizations', '');
          add_option('acc_integration_zoho_sync_to_system_organizations', '');

          if (!$CI->db->field_exists('organization_id', db_prefix() . 'acc_integration_logs')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_integration_logs`
                  ADD COLUMN `organization_id` TEXT NULL');
          }

          if (!$CI->db->field_exists('organization_id', db_prefix() . 'acc_integration_error_logs')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_integration_error_logs`
                  ADD COLUMN `organization_id` TEXT NULL');
          }

          if (!$CI->db->field_exists('organization_id', db_prefix() . 'acc_integration_sync_logs')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_integration_sync_logs`
                  ADD COLUMN `organization_id` TEXT NULL');
          }
     }
}