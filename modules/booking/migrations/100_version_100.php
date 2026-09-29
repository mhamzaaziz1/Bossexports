<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
     public function up()
     {
          $CI = &get_instance();

          add_option('quote_expiry_reminder_enabled', 1);
          add_option('send_quote_expiry_reminder_before', 4);
          add_option('show_quotes_on_calendar', 1);
          add_option('calendar_quote_color', '#84c529');
          add_option('show_quote_reminders_on_calendar', 1);
          add_option('quote_due_after', 7);
          add_option('default_quotes_pipeline_sort', 'pipeline_order');
          add_option('default_quotes_pipeline_sort_type', 'acs');
          add_option('pdf_format_quote', 'A4-PORTRAIT');
          add_option('quotes_pipeline_limit', 50);
          add_option('quote_number_prefix', 'Q-');
          add_option('exclude_quote_from_client_area_with_draft_status', 1);
          add_option('allow_staff_view_quotes_assigned', 1);
          add_option('quote_info_format', '{quote_to}<br />{address}<br />{city} {state}<br />{country_code} {zip_code}<br />{phone}<br />{email}');
          add_option('quote_accept_identity_confirmation', 1);
          add_option('quotes_auto_operations_hour', 21);
          add_option('show_bookings_on_calendar', 1);
          add_option('show_all_tours_for_booking_member', 1);
          add_option('calendar_booking_color', '#B72974');
          add_option('show_booking_on_credit_note', 1);
          add_option('show_booking_on_invoice', 1);
          add_option('show_booking_on_estimate', 1);

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "quotes (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `subject` varchar(191) DEFAULT NULL,
                    `content` longtext DEFAULT NULL,
                    `addedfrom` int(11) NOT NULL,
                    `datecreated` datetime NOT NULL,
                    `total` decimal(15,2) DEFAULT NULL,
                    `subtotal` decimal(15,2) NOT NULL,
                    `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
                    `adjustment` decimal(15,2) DEFAULT NULL,
                    `discount_percent` decimal(15,2) NOT NULL,
                    `discount_total` decimal(15,2) NOT NULL,
                    `discount_type` varchar(30) DEFAULT NULL,
                    `show_quantity_as` int(11) NOT NULL DEFAULT 1,
                    `currency` int(11) NOT NULL,
                    `open_till` date DEFAULT NULL,
                    `date` date NOT NULL,
                    `rel_id` int(11) DEFAULT NULL,
                    `rel_type` varchar(40) DEFAULT NULL,
                    `assigned` int(11) DEFAULT NULL,
                    `hash` varchar(32) NOT NULL,
                    `quote_to` varchar(191) DEFAULT NULL,
                    `country` int(11) NOT NULL DEFAULT 0,
                    `zip` varchar(50) DEFAULT NULL,
                    `state` varchar(100) DEFAULT NULL,
                    `city` varchar(100) DEFAULT NULL,
                    `address` varchar(200) DEFAULT NULL,
                    `email` varchar(150) DEFAULT NULL,
                    `phone` varchar(50) DEFAULT NULL,
                    `allow_comments` tinyint(1) NOT NULL DEFAULT 1,
                    `status` int(11) NOT NULL,
                    `estimate_id` int(11) DEFAULT NULL,
                    `invoice_id` int(11) DEFAULT NULL,
                    `date_converted` datetime DEFAULT NULL,
                    `pipeline_order` int(11) NOT NULL DEFAULT 0,
                    `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
                    `acceptance_firstname` varchar(50) DEFAULT NULL,
                    `acceptance_lastname` varchar(50) DEFAULT NULL,
                    `acceptance_email` varchar(100) DEFAULT NULL,
                    `acceptance_date` datetime DEFAULT NULL,
                    `acceptance_ip` varchar(40) DEFAULT NULL,
                    `signature` varchar(40) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `status` (`status`)
               ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "quote_comments (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `content` mediumtext DEFAULT NULL,
                    `quoteid` int(11) NOT NULL,
                    `staffid` int(11) NOT NULL,
                    `dateadded` datetime NOT NULL,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "booking_activity (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `booking_id` int(11) NOT NULL,
                    `staff_id` int(11) NOT NULL DEFAULT 0,
                    `contact_id` int(11) NOT NULL DEFAULT 0,
                    `fullname` varchar(100) DEFAULT NULL,
                    `visible_to_customer` int(11) NOT NULL DEFAULT 0,
                    `description_key` varchar(191) NOT NULL COMMENT 'Language file key',
                    `additional_data` text DEFAULT NULL,
                    `dateadded` datetime NOT NULL,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "booking_files (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `file_name` varchar(191) NOT NULL,
                    `subject` varchar(191) DEFAULT NULL,
                    `description` text DEFAULT NULL,
                    `filetype` varchar(50) DEFAULT NULL,
                    `dateadded` datetime NOT NULL,
                    `last_activity` datetime DEFAULT NULL,
                    `booking_id` int(11) NOT NULL,
                    `visible_to_customer` tinyint(1) DEFAULT 0,
                    `staffid` int(11) NOT NULL,
                    `contact_id` int(11) NOT NULL DEFAULT 0,
                    `external` varchar(40) DEFAULT NULL,
                    `external_link` text DEFAULT NULL,
                    `thumbnail_link` text DEFAULT NULL,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "booking_members (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `booking_id` int(11) NOT NULL,
                    `staff_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `booking_id` (`booking_id`),
                    KEY `staff_id` (`staff_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "booking_notes (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `booking_id` int(11) NOT NULL,
                    `content` text NOT NULL,
                    `staff_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "booking_settings (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `booking_id` int(11) NOT NULL,
                    `name` varchar(100) NOT NULL,
                    `value` text DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `booking_id` (`booking_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "bookingdiscussioncomments (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `discussion_id` int(11) NOT NULL,
                    `discussion_type` varchar(10) NOT NULL,
                    `parent` int(11) DEFAULT NULL,
                    `created` datetime NOT NULL,
                    `modified` datetime DEFAULT NULL,
                    `content` text NOT NULL,
                    `staff_id` int(11) NOT NULL,
                    `contact_id` int(11) DEFAULT 0,
                    `fullname` varchar(191) DEFAULT NULL,
                    `file_name` varchar(191) DEFAULT NULL,
                    `file_mime_type` varchar(70) DEFAULT NULL,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "bookingdiscussions (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `booking_id` int(11) NOT NULL,
                    `subject` varchar(191) NOT NULL,
                    `description` text NOT NULL,
                    `show_to_customer` tinyint(1) NOT NULL DEFAULT 0,
                    `datecreated` datetime NOT NULL,
                    `last_activity` datetime DEFAULT NULL,
                    `staff_id` int(11) NOT NULL DEFAULT 0,
                    `contact_id` int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );

          $CI->db->query(
               "CREATE TABLE IF NOT EXISTS " . db_prefix() . "bookings (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `description` text DEFAULT NULL,
                    `status` int(11) NOT NULL DEFAULT 0,
                    `clientid` int(11) NOT NULL,
                    `billing_type` int(11) NOT NULL,
                    `start_date` date NOT NULL,
                    `deadline` date DEFAULT NULL,
                    `booking_created` date NOT NULL,
                    `date_finished` datetime DEFAULT NULL,
                    `progress` int(11) DEFAULT 0,
                    `progress_from_tours` int(11) NOT NULL DEFAULT 1,
                    `booking_cost` decimal(15,2) DEFAULT NULL,
                    `booking_rate_per_hour` decimal(15,2) DEFAULT NULL,
                    `estimated_hours` decimal(15,2) DEFAULT NULL,
                    `addedfrom` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `clientid` (`clientid`),
                    KEY `name` (`name`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
          );
     }
}
