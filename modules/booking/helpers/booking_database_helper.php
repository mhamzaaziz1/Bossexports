<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('init_booking_database_tables')) {

    /**
     * Init installation tables creation in database
     */
    function init_booking_database_tables()
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
        add_option('default_tour_priority', 2);
        add_option('show_tours_on_calendar', 1);
        add_option('client_staff_add_edit_delete_tour_comments_first_hour', 0);
        add_option('tours_reminder_notification_before', 2);
        add_option('show_all_tours_for_booking_member', 1);
        add_option('auto_stop_tours_timers_on_new_timer', 1);
        add_option('new_tour_auto_assign_current_member', 1);
        add_option('tours_kanban_limit', 50);
        add_option('tour_modal_class', 'modal-lg');
        add_option('default_tour_status', 'auto');
        add_option('new_tour_auto_follower_current_member', 0);
        add_option('tour_biillable_checked_on_creation', 1);
        add_option('calendar_only_assigned_tours', 0);
        add_option('show_tour_reminders_on_calendar', 1);
        add_option('tours_reminder_notification_hour', 21);
        add_option('show_all_tours_for_booking_member', 1);

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
        
        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "pinned_bookings (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `booking_id` int(11) NOT NULL,
                `staff_id` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `booking_id` (`booking_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tour_assigned (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `staffid` int(11) NOT NULL,
                `tourid` int(11) NOT NULL,
                `assigned_from` int(11) NOT NULL DEFAULT 0,
                `is_assigned_from_contact` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `tourid` (`tourid`),
                KEY `staffid` (`staffid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tour_checklist_items (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `tourid` int(11) NOT NULL,
                `description` text NOT NULL,
                `finished` int(11) NOT NULL DEFAULT 0,
                `dateadded` datetime NOT NULL,
                `addedfrom` int(11) NOT NULL,
                `finished_from` int(11) DEFAULT 0,
                `list_order` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `tourid` (`tourid`)                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tour_comments (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `content` text NOT NULL,
                `tourid` int(11) NOT NULL,
                `staffid` int(11) NOT NULL,
                `contact_id` int(11) NOT NULL DEFAULT 0,
                `file_id` int(11) NOT NULL DEFAULT 0,
                `dateadded` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `file_id` (`file_id`),
                KEY `tourid` (`tourid`)                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tour_followers (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `staffid` int(11) NOT NULL,
                `tourid` int(11) NOT NULL,
                PRIMARY KEY (`id`)                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tours (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` mediumtext DEFAULT NULL,
                `description` text DEFAULT NULL,
                `priority` int(11) DEFAULT NULL,
                `dateadded` datetime NOT NULL,
                `startdate` date NOT NULL,
                `duedate` date DEFAULT NULL,
                `datefinished` datetime DEFAULT NULL,
                `addedfrom` int(11) NOT NULL,
                `is_added_from_contact` tinyint(1) NOT NULL DEFAULT 0,
                `status` int(11) NOT NULL DEFAULT 0,
                `recurring_type` varchar(10) DEFAULT NULL,
                `repeat_every` int(11) DEFAULT NULL,
                `recurring` int(11) NOT NULL DEFAULT 0,
                `is_recurring_from` int(11) DEFAULT NULL,
                `cycles` int(11) NOT NULL DEFAULT 0,
                `total_cycles` int(11) NOT NULL DEFAULT 0,
                `custom_recurring` tinyint(1) NOT NULL DEFAULT 0,
                `last_recurring_date` date DEFAULT NULL,
                `rel_id` int(11) DEFAULT NULL,
                `rel_type` varchar(30) DEFAULT NULL,
                `is_public` tinyint(1) NOT NULL DEFAULT 0,
                `billable` tinyint(1) NOT NULL DEFAULT 0,
                `billed` tinyint(1) NOT NULL DEFAULT 0,
                `invoice_id` int(11) NOT NULL DEFAULT 0,
                `hourly_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
                `milestone` int(11) DEFAULT 0,
                `kanban_order` int(11) NOT NULL DEFAULT 0,
                `milestone_order` int(11) NOT NULL DEFAULT 0,
                `visible_to_client` tinyint(1) NOT NULL DEFAULT 0,
                `deadline_notified` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `rel_id` (`rel_id`),
                KEY `rel_type` (`rel_type`),
                KEY `milestone` (`milestone`),
                KEY `kanban_order` (`kanban_order`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tours_checklist_templates (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `description` text DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS " . db_prefix() . "tourstimers (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `tour_id` int(11) NOT NULL,
                `start_time` varchar(64) NOT NULL,
                `end_time` varchar(64) DEFAULT NULL,
                `staff_id` int(11) NOT NULL,
                `hourly_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
                `note` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `tour_id` (`tour_id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "invoices' AND table_schema = '" . $CI->db->database . "' AND column_name = 'booking_id'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "invoices ADD COLUMN `booking_id` INT(11) DEFAULT 0 NULL AFTER `project_id`");
        };
        
        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "vault' AND table_schema = '" . $CI->db->database . "' AND column_name = 'share_in_bookings'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "vault ADD COLUMN `share_in_bookings` TINYINT(1) DEFAULT 0 NOT NULL AFTER `share_in_projects`");
        };
        
        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "expenses' AND table_schema = '" . $CI->db->database . "' AND column_name = 'booking_id'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "expenses ADD COLUMN `booking_id` INT(11) DEFAULT 0 NOT NULL AFTER `project_id`");
        };
        
        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "milestones' AND table_schema = '" . $CI->db->database . "' AND column_name = 'booking_id'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "milestones ADD COLUMN `booking_id` INT(11) DEFAULT 0 NOT NULL AFTER `project_id`");
        };
        
        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "estimates' AND table_schema = '" . $CI->db->database . "' AND column_name = 'booking_id'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "estimates ADD COLUMN `booking_id` INT(11) DEFAULT 0 NOT NULL AFTER `project_id`");
        };
        
        if(!$CI->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . db_prefix() . "tickets' AND table_schema = '" . $CI->db->database . "' AND column_name = 'booking_id'")->num_rows()){
            $CI->db->query("ALTER TABLE " . db_prefix() . "tickets ADD COLUMN `booking_id` INT(11) DEFAULT 0 NOT NULL AFTER `project_id`");
        };

        checkForModuleReinstallation();
    }
}


if (!function_exists('init_booking_template_tables')) {
    /**
     * Insert email templates into database
     */
    function init_booking_template_tables()
    {
        create_email_template('Customer Accepted Quote', '<div>Hi<br /> <br />Client <strong>{quote_quote_to}</strong> accepted the following quote:<br /> <br /><strong>Number:</strong> {quote_number}<br /><strong>Subject</strong>: {quote_subject}<br /><strong>Total</strong>: {quote_total}<br /> <br />View the quote on the following link: <a href="{quote_link}">{quote_number}</a><br /> <br />Kind Regards,<br />{email_signature}</div><div>&nbsp;</div><div>&nbsp;</div><div>&nbsp;</div>', 'booking', 'Customer Action - Accepted (Sent to Staff)', 'quote-client-accepted');

        create_email_template('Quote With Number {quote_number} Created', 'Dear {quote_quote_to}<br /><br />Please find our attached quote.<br /><br />This quote is valid until: {quote_open_till}<br />You can view the quote on the following link: <a href="{quote_link}">{quote_number}</a><br /><br />Please don\'t hesitate to comment online if you have any questions.<br /><br />We look forward to your communication.<br /><br />Kind Regards,<br />{email_signature}', 'booking', 'Send Quote to Customer', 'quote-send-to-customer');

        create_email_template('Client Declined Quote', 'Hi<br /> <br />Customer <strong>{quote_quote_to}</strong> declined the quote <strong>{quote_subject}</strong><br /> <br />View the quote on the following link <a href="{quote_link}">{quote_number}</a>&nbsp;or from the admin area.<br /> <br />Kind Regards,<br />{email_signature}', 'booking', 'Customer Action - Declined (Sent to Staff)', 'quote-client-declined');

        create_email_template('Thank for you accepting quote', 'Dear {quote_quote_to}<br /> <br />Thank for for accepting the quote.<br /> <br />We look forward to doing business with you.<br /> <br />We will contact you as soon as possible<br /> <br />Kind Regards,<br />{email_signature}', 'booking', 'Thank You Email (Sent to Customer After Accept)', 'quote-client-thank-you');

        create_email_template('New Quote Comment', 'Dear {quote_quote_to}<br /> <br />A new comment has been made on the following quote: <strong>{quote_number}</strong><br /> <br />You can view and reply to the comment on the following link: <a href="{quote_link}">{quote_number}</a><br /> <br />Kind Regards,<br />{email_signature}', 'booking', 'New Comment Â (Sent to Customer/Lead)', 'quote-comment-to-client');

        create_email_template('New Quote Comment', 'Hi<br /> <br />A new comment has been made to the quote <strong>{quote_subject}</strong><br /> <br />You can view and reply to the comment on the following link: <a href="{quote_link}">{quote_number}</a>&nbsp;or from the admin area.<br /> <br />{email_signature}', 'booking', 'New Comment (Sent to Staff) ', 'quote-comment-to-admin');

        create_email_template('Quote Expiration Reminder', '<p>Hello {quote_quote_to}<br /><br />The quote {quote_number}&nbsp;will expire on <strong>{quote_open_till}</strong><br /><br />You can view the quote on the following link: <a href="{quote_link}">{quote_number}</a><br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'Quote Expiration Reminder', 'quote-expiry-reminder');

        create_email_template('New Booking Discussion Created - {booking_name}', '<p>Hi {staff_firstname}<br /><br />New booking discussion created from <strong>{discussion_creator}</strong><br /><br /><strong>Subject:</strong> {discussion_subject}<br /><strong>Description:</strong> {discussion_description}<br /><br />You can view the discussion on the following link: <a href="{discussion_link}">{discussion_subject}</a><br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'New Booking Discussion (Sent to Booking Members)', 'new-booking-discussion-created-to-staff');

        create_email_template('New Booking Discussion Created - {booking_name}', '<p>Hello {contact_firstname} {contact_lastname}<br /><br />New booking discussion created from <strong>{discussion_creator}</strong><br /><br /><strong>Subject:</strong> {discussion_subject}<br /><strong>Description:</strong> {discussion_description}<br /><br />You can view the discussion on the following link: <a href="{discussion_link}">{discussion_subject}</a><br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'New Booking Discussion (Sent to Customer Contacts)', 'new-booking-discussion-created-to-customer');

        create_email_template('New Booking File(s) Uploaded - {booking_name}', '<p>Hello {contact_firstname} {contact_lastname}<br /><br />New booking file is uploaded on <strong>{booking_name}</strong> from <strong>{file_creator}</strong><br /><br />You can view the booking on the following link: <a href="{booking_link}">{booking_name}</a><br /><br />To view the file in our CRM you can click on the following link: <a href="{discussion_link}">{discussion_subject}</a><br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'New Booking File(s) Uploaded (Sent to Customer Contacts)', 'new-booking-file-uploaded-to-customer');

        create_email_template('New Booking File(s) Uploaded - {booking_name}', '<p>Hello&nbsp;{staff_firstname}</p><p>New booking&nbsp;file is uploaded on&nbsp;<strong>{booking_name}</strong> from&nbsp;<strong>{file_creator}</strong></p><p>You can view the booking on the following link: <a href="{booking_link}">{booking_name}<br /></a><br />To view&nbsp;the file you can click on the following link: <a href="{discussion_link}">{discussion_subject}</a></p><p>Kind Regards,<br />{email_signature}</p>', 'booking', 'New Booking File(s) Uploaded (Sent to Booking Members)', 'new-booking-file-uploaded-to-staff');

        create_email_template('New Discussion Comment', '<p><span style="font-size: 12pt;">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style="font-size: 12pt;">New discussion comment has been made on <strong>{discussion_subject}</strong> from <strong>{comment_creator}</strong></span><br /><br /><span style="font-size: 12pt;"><strong>Discussion subject:</strong> {discussion_subject}</span><br /><span style="font-size: 12pt;"><strong>Comment</strong>: {discussion_comment}</span><br /><br /><span style="font-size: 12pt;">You can view the discussion on the following link: <a href="{discussion_link}">{discussion_subject}</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span></p>', 'booking', 'New Discussion Comment  (Sent to Customer Contacts)', 'new-booking-discussion-comment-to-customer');

        create_email_template('New Discussion Comment', '<p>Hi {staff_firstname}<br /><br />New discussion comment has been made on <strong>{discussion_subject}</strong> from <strong>{comment_creator}</strong><br /><br /><strong>Discussion subject:</strong> {discussion_subject}<br /><strong>Comment:</strong> {discussion_comment}<br /><br />You can view the discussion on the following link: <a href="{discussion_link}">{discussion_subject}</a><br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'New Discussion Comment (Sent to Booking Members)', 'new-booking-discussion-comment-to-staff');

        create_email_template('New booking assigned to you', '<p>Hi {staff_firstname}<br /><br />New booking has been assigned to you.<br /><br />You can view the booking on the following link <a href="{booking_link}">{booking_name}</a><br /><br />{email_signature}</p>', 'booking', 'Staff Added as Booking Member', 'staff-added-as-booking-member');

        create_email_template('New Booking Created', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p><p>New booking is assigned to your company.<br /><br /><strong>Booking Name:</strong>&nbsp;{booking_name}<br /><strong>Booking Start Date:</strong>&nbsp;{booking_start_date}</p><p>You can view the booking on the following link:&nbsp;<a href="{booking_link}">{booking_name}</a></p><p>We are looking forward hearing from you.<br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'New Booking Created (Sent to Customer Contacts)', 'assigned-to-booking');

        create_email_template('Booking Marked as Finished', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p><p>You are receiving this email because booking&nbsp;<strong>{booking_name}</strong> has been marked as finished. This booking is assigned under your company and we just wanted to keep you up to date.<br /><br />You can view the booking on the following link:&nbsp;<a href="{booking_link}">{booking_name}</a></p><p>If you have any questions don\'t hesitate to contact us.<br /><br />Kind Regards,<br />{email_signature}</p>', 'booking', 'Booking Marked as Finished (Sent to Customer Contacts)', 'booking-finished-to-customer');

        create_email_template('New Tour Assigned to You - {tour_name}', '<span style="font-size: 12pt;">Dear {staff_firstname}</span><br /><br /><span style="font-size: 12pt;">You have been assigned to a new tour:</span><br /><br /><span style="font-size: 12pt;"><strong>Name:</strong> {tour_name}<br /></span><strong>Start Date:</strong> {tour_startdate}<br /><span style="font-size: 12pt;"><strong>Due date:</strong> {tour_duedate}</span><br /><span style="font-size: 12pt;"><strong>Priority:</strong> {tour_priority}<br /><br /></span><span style="font-size: 12pt;"><span>You can view the tour on the following link</span>: <a href="{tour_link}">{tour_name}</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span>', 'booking', 'New Tour Assigned (Sent to Staff)', 'tour-assigned');

        create_email_template('You are added as follower on tour - {tour_name}', '<span style="font-size: 12pt;">Hi {staff_firstname}<br /></span><br /><span style="font-size: 12pt;">You have been added as follower on the following tour:</span><br /><br /><span style="font-size: 12pt;"><strong>Name:</strong> {tour_name}</span><br /><span style="font-size: 12pt;"><strong>Start date:</strong> {tour_startdate}</span><br /><br /><span>You can view the tour on the following link</span><span>: </span><a href="{tour_link}">{tour_name}</a><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span>', 'booking', 'Staff Member Added as Follower on Tour (Sent to Staff)', 'tour-added-as-follower');

        create_email_template('New Comment on Tour - {tour_name}', 'Dear {staff_firstname}<br /><br />A comment has been made on the following tour:<br /><br /><strong>Name:</strong> {tour_name}<br /><strong>Comment:</strong> {tour_comment}<br /><br />You can view the tour on the following link: <a href="{tour_link}">{tour_name}</a><br /><br />Kind Regards,<br />{email_signature}', 'booking', 'New Comment on Tour (Sent to Staff)', 'tour-commented');

        create_email_template('New Attachment on Tour - {tour_name}', 'Hi {staff_firstname}<br /><br /><strong>{tour_user_take_action}</strong> added an attachment on the following tour:<br /><br /><strong>Name:</strong> {tour_name}<br /><br />You can view the tour on the following link: <a href="{tour_link}">{tour_name}</a><br /><br />Kind Regards,<br />{email_signature}', 'booking', 'New Attachment(s) on Tour (Sent to Staff)', 'tour-added-attachment');

        create_email_template('Tour Deadline Reminder', 'Hi {staff_firstname}&nbsp;{staff_lastname}<br /><br />This is an automated email from {companyname}.<br /><br />The tour <strong>{tour_name}</strong> deadline is on <strong>{tour_duedate}</strong>. <br />This tour is still not finished.<br /><br />You can view the tour on the following link: <a href="{tour_link}">{tour_name}</a><br /><br />Kind Regards,<br />{email_signature}', 'booking', 'Tour Deadline Reminder - Sent to Assigned Members', 'tour-deadline-notification');

        create_email_template('New Attachment on Tour - {tour_name}', '<span>Hi {contact_firstname} {contact_lastname}</span><br /><br /><strong>{tour_user_take_action}</strong><span> added an attachment on the following tour:</span><br /><br /><strong>Name:</strong><span> {tour_name}</span><br /><br /><span>You can view the tour on the following link: </span><a href="{tour_link}">{tour_name}</a><br /><br /><span>Kind Regards,</span><br /><span>{email_signature}</span>', 'booking', 'New Attachment(s) on Tour (Sent to Customer Contacts)', 'tour-added-attachment-to-contacts');

        create_email_template('New Comment on Tour - {tour_name}', '<span>Dear {contact_firstname} {contact_lastname}</span><br /><br /><span>A comment has been made on the following tour:</span><br /><br /><strong>Name:</strong><span> {tour_name}</span><br /><strong>Comment:</strong><span> {tour_comment}</span><br /><br /><span>You can view the tour on the following link: </span><a href="{tour_link}">{tour_name}</a><br /><br /><span>Kind Regards,</span><br /><span>{email_signature}</span>', 'booking', 'New Comment on Tour (Sent to Customer Contacts)', 'tour-commented-to-contacts');

        create_email_template('Tour Status Changed', '<span style="font-size: 12pt;">Hi {staff_firstname}</span><br /><br /><span style="font-size: 12pt;"><strong>{tour_user_take_action}</strong> marked tour as <strong>{tour_status}</strong></span><br /><br /><span style="font-size: 12pt;"><strong>Name:</strong> {tour_name}</span><br /><span style="font-size: 12pt;"><strong>Due date:</strong> {tour_duedate}</span><br /><br /><span style="font-size: 12pt;">You can view the tour on the following link: <a href="{tour_link}">{tour_name}</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span>', 'booking', 'Tour Status Changed (Sent to Staff)', 'tour-status-change-to-staff');
        
        create_email_template('Tour Status Changed', '<span style="font-size: 12pt;">Hi {contact_firstname} {contact_lastname}</span><br /><br /><span style="font-size: 12pt;"><strong>{tour_user_take_action}</strong> marked tour as <strong>{tour_status}</strong></span><br /><br /><span style="font-size: 12pt;"><strong>Name:</strong> {tour_name}</span><br /><span style="font-size: 12pt;"><strong>Due date:</strong> {tour_duedate}</span><br /><br /><span style="font-size: 12pt;">You can view the tour on the following link: <a href="{tour_link}">{tour_name}</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span>', 'booking', 'Tour Status Changed (Sent to Customer Contacts)', 'tour-status-change-to-contacts');
    }
}


if (!function_exists('init_booking_install_sequence')) {
    /**
     * Initialize tables content example data for email templates in database
     */
    function init_booking_install_sequence()
    {
        init_booking_database_tables();
        init_booking_template_tables();
    }
}

if (!function_exists('checkForModuleReinstallation')) {
    /** 
     * Percusion database checks
     */
    function checkForModuleReinstallation()
    {
        $CI = &get_instance();

        if (!$CI->db->field_exists('contact_id', db_prefix() . 'booking_activity')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "booking_activity ADD `contact_id` INT(11) NOT NULL DEFAULT '0' AFTER `staff_id`;");
        }

        // if (!$CI->db->field_exists('type_id', db_prefix() . 'appointly_appointments')) {
        //     $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD `type_id` INT(11) NOT NULL DEFAULT '0' AFTER `source`;");
        // }

        // if (!$CI->db->field_exists('google_event_id', db_prefix() . 'appointly_appointments')) {
        //     $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD `google_event_id` VARCHAR(191) NULL DEFAULT NULL AFTER `id`;");
        // }

        // if (!$CI->db->field_exists('google_calendar_link', db_prefix() . 'appointly_appointments')) {
        //     $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD `google_calendar_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_event_id`;");
        // }

        // if (!$CI->db->field_exists('google_added_by_id', db_prefix() . 'appointly_appointments')) {
        //     $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD `google_added_by_id` int(11) NULL DEFAULT NULL AFTER `google_calendar_link`;");
        // }
    }
}

