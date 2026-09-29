<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (! function_exists('init_appointly_database_tables')) {
    /**
     * Init installation tables creation in a database
     */
    function init_appointly_database_tables()
    {
        $CI = &get_instance();

        add_option('appointly_show_clients_schedule_button', 0); // Show clients schedule button
        add_option('appointly_tab_on_clients_page', 0); // Show tab on clients page
        add_option('appointly_also_delete_in_google_calendar', 1); // Also delete in google calendar
        add_option('appointly_google_client_secret'); // Google client secrets
        add_option('appointly_outlook_client_id'); // Outlook client id
        add_option('appointly_view_all_in_calendar', 0); // View all in calendar
        add_option('appointments_googlesync_show_in_table', 0); // Show in table
        add_option('appointments_googlesync_show_from', 'last_3_months'); // Show from
        add_option('appointments_enable_terms_conditions', 1); // Enable terms and conditions
        add_option('appointly_blocked_days'); // Blocked days
        add_option('appointly_appointments_recaptcha', '0'); // Appointments recaptcha
        add_option('external_form_heading', 'Schedule a Meeting'); // External form heading
        add_option('appointly_show_summary', '1'); // Show summary
        add_option('appointly_today_widget_enabled', '1'); // Today widget enabled
        add_option('appointly_upcoming_widget_enabled', '1'); // Upcoming widget enabled
        add_option('appointly_upcoming_widget_range', '7_days'); // Upcoming widget range
        add_option('appointments_booking_services_availability', json_encode([]));
        add_option('appointly_invoice_tax_type', 'none'); // none, custom, system
        add_option('appointly_invoice_system_tax', ''); // Selected tax ID for system taxes
        add_option('appointly_invoice_default_vat', '0'); // Custom tax percentage (backward compatibility)
        add_option('appointly_create_invoice_when_completed', 0); // Create invoice when appointment is completed
        add_option('appointly_auto_enable_google_meet', 0); // Auto enable google meet
        add_option('appointly_google_meet_recording', 0); // Google meet recording
        add_option('appointly_google_meet_waiting_room', 0); // Google meet waiting room
        add_option('appointly_google_meet_reminder_minutes', 30); // Google meet reminder minutes
        add_option('appointly_disable_google_meeting_emails', 0); // Disable google meeting emails
        add_option('appointly_show_staff_email', 0); // Show staff email in booking form
        add_option('appointly_show_staff_phone', 0); // Show staff phone in booking form
        add_option('appointly_staff_respect_availability', 0); // Staff appointments respect availability restrictions
        add_option('appointly_auto_add_to_google_on_approval', 0); // Add new setting for auto-adding to Google Calendar on approval
        add_option('appointments_external_form_show_language_dropdown', 0); // Show language dropdown in external form
        add_option(
            'external_form_description',
            'Complete the form below to arrange your session with our team'
        ); // External form description

        add_option(
            'appointly_default_feedbacks',
            '["0","1","2","3","4","5","6"]'
        ); // Default feedbacks

        // Create the company schedule table if it doesn't exist
        if (!$CI->db->table_exists(db_prefix() . "appointly_company_schedule")) {
            $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "appointly_company_schedule` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `weekday` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
                `start_time` TIME NOT NULL DEFAULT '09:00:00',
                `end_time` TIME NOT NULL DEFAULT '17:00:00',
                `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Populate with default data
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $companySchedule = [];

            foreach ($weekdays as $index => $day) {
                $isWeekend = ($index > 4); // Saturday and Sunday
                $companySchedule[] = [
                    'weekday' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'is_enabled' => $isWeekend ? 0 : 1
                ];
            }

            if (!empty($companySchedule)) {
                $CI->db->insert_batch(db_prefix() . 'appointly_company_schedule', $companySchedule);
            }
        }

        // Create the staff working hours table if it doesn't exist
        if (!$CI->db->table_exists(db_prefix() . "appointly_staff_working_hours")) {
            $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "appointly_staff_working_hours` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `staff_id` int(11) NOT NULL,
                `weekday` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
                `start_time` TIME NOT NULL,
                `end_time` TIME NOT NULL,
                `is_available` TINYINT(1) NOT NULL DEFAULT 1,
                `use_company_schedule` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `staff_id` (`staff_id`),
                INDEX `idx_staff_weekday` (`staff_id`, `weekday`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // Updated appointments table: added provider_id column after created_by.
        if (! $CI->db->table_exists(db_prefix() . "appointly_appointments")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_appointments (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `service_id` int(11) DEFAULT NULL,
                `provider_id` int(11) DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,     
                `source` varchar(191) DEFAULT NULL,
                `google_event_id` varchar(191) DEFAULT NULL,
                `google_calendar_link` varchar(191) DEFAULT NULL,
                `google_meet_link` varchar(191) DEFAULT NULL,
                `google_added_by_id` int(11) DEFAULT NULL,
                `outlook_event_id` VARCHAR(191) DEFAULT NULL,
                `outlook_calendar_link` VARCHAR(255) DEFAULT NULL,
                `outlook_added_by_id` INT(11) DEFAULT NULL,
                `subject` varchar(191) NOT NULL,
                `description` text,
                `email` varchar(191) DEFAULT NULL,
                `name` varchar(191) DEFAULT NULL,
                `phone` varchar(191) DEFAULT NULL,
                `address` varchar(191) DEFAULT NULL,
                `notes` longtext DEFAULT NULL,
                `contact_id` int(11) DEFAULT NULL,
                `hash` varchar(191) DEFAULT NULL,
                `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress',
                `notification_date` datetime DEFAULT NULL,
                `external_notification_date` datetime DEFAULT NULL,
                `date` date NOT NULL,
                `start_hour` varchar(191) NOT NULL,
                `duration` varchar(100) DEFAULT NULL,
                `end_hour` varchar(191) NOT NULL,
                `approved` tinyint(1) NOT NULL DEFAULT '0',                        
                `cancel_notes` text,
                `feedback` SMALLINT NULL DEFAULT NULL,
                `feedback_comment` TEXT NULL DEFAULT NULL,
                `reminder_before` int(11) DEFAULT NULL,
                `reminder_before_type` varchar(10) DEFAULT NULL,
                `by_sms` tinyint(1) DEFAULT NULL,
                `by_email` tinyint(1) DEFAULT NULL,
                `recurring` int NOT NULL DEFAULT '0',
                `recurring_type` varchar(10) DEFAULT NULL,
                `repeat_every` INT NULL DEFAULT NULL,
                `custom_recurring` tinyint NULL DEFAULT '0',
                `cycles` int NOT NULL DEFAULT '0',
                `total_cycles` int NOT NULL DEFAULT '0',
                `last_recurring_date` date DEFAULT NULL,
                `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `files` text DEFAULT NULL,
                `timezone` varchar(100) DEFAULT NULL,
                `invoice_id` int(11) NULL DEFAULT NULL,
                `invoice_date` datetime NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_date_status` (`date`, `status`),
                INDEX `idx_contact_id` (`contact_id`),
                INDEX `idx_service_id` (`service_id`),
                INDEX `idx_provider_id` (`provider_id`),
                INDEX `idx_created_by` (`created_by`),
                INDEX `idx_google_event_id` (`google_event_id`),
                INDEX `idx_date_start_hour` (`date`, `start_hour`),
                INDEX `idx_approved_status` (`approved`, `status`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );
        }

        if (! $CI->db->table_exists(db_prefix() . 'appointly_reschedule_requests')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_reschedule_requests` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `appointment_id` INT UNSIGNED NOT NULL,
                `requested_date` DATE NOT NULL,
                `requested_time` TIME NOT NULL,
                `reason` TEXT NULL,
                `status` ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
                `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `processed_by` INT NULL,
                `processed_at` DATETIME NULL,
                `denial_reason` TEXT NULL,
                INDEX `idx_appointment_id` (`appointment_id`),
                INDEX `idx_status` (`status`),
                FOREIGN KEY (`appointment_id`) REFERENCES `" . db_prefix() . "appointly_appointments`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        if (! $CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_service_staff` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `staff_id` int(11) NOT NULL,
                `is_provider` tinyint(1) DEFAULT 1,
                `is_primary` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `service_id` (`service_id`),
                KEY `staff_id` (`staff_id`),
                INDEX `idx_service_staff` (`service_id`, `staff_id`),
                INDEX `idx_staff_provider` (`staff_id`, `is_provider`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        if (! $CI->db->table_exists(db_prefix()
            . 'appointly_appointment_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_appointment_services` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `appointment_id` int unsigned NOT NULL,
                `service_id` int NOT NULL,
                PRIMARY KEY (`id`),
                KEY `appointment_id` (`appointment_id`),
                KEY `service_id` (`service_id`),
                INDEX `idx_service_appointment` (`service_id`, `appointment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Migrate existing appointments data
            $CI->db->query("INSERT INTO `" . db_prefix() . "appointly_appointment_services`
                (appointment_id, service_id)
                SELECT id, service_id
                FROM `" . db_prefix() . "appointly_appointments`
                WHERE service_id IS NOT NULL");
        }

        if (! $CI->db->table_exists(db_prefix() . "appointly_attendees")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_attendees (
                `staff_id` int(11) NOT NULL,
                `appointment_id` int(11) NOT NULL,
                INDEX `idx_appointment_id` (`appointment_id`),
                INDEX `idx_staff_id` (`staff_id`),
                INDEX `idx_appointment_staff` (`appointment_id`, `staff_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );
        }

        if (! $CI->db->table_exists(db_prefix() . "appointly_google")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_google (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `staff_id` int(11) NOT NULL,
               `access_token` varchar(191) NOT NULL,
               `refresh_token` varchar(191) NOT NULL,
               `expires_in` varchar(191) NOT NULL,
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );
        }
        // Create services table if it doesn't exist
        if (! $CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_services` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `description` text DEFAULT NULL,
            `duration` int(11) DEFAULT 60,
            `buffer_before` int(11) DEFAULT 0,
            `buffer_after` int(11) DEFAULT 0,
            `price` decimal(15,2) DEFAULT 0.00,
            `color` varchar(10) DEFAULT '#28B8DA',
            `active` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_active` (`active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // Check if services table exists before counting (safety check)
        $services_count = 0;
        if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $services_count = $CI->db->count_all(db_prefix() . 'appointly_services');
        }

        if ($services_count == 0) {

            $default_services = [
                [
                    'name' => 'Initial Consultation',
                    'description' => 'First meeting to discuss your needs and requirements',
                    'duration' => 30,
                    'price' => 0,
                    'color' => '#3B82F6', // Tailwind blue-500
                    'active' => 1,
                ],
                [
                    'name' => 'Business Strategy Session',
                    'description' => 'In-depth discussion about business strategy and planning',
                    'duration' => 60,
                    'price' => 150,
                    'color' => '#10B981', // Tailwind emerald-500
                    'active' => 1,
                ],
                [
                    'name' => 'Project Review Meeting',
                    'description' => 'Regular project status review and updates',
                    'duration' => 45,
                    'price' => 100,
                    'color' => '#8B5CF6', // Tailwind violet-500
                    'active' => 1,
                ]
            ];

            // Insert services and assign to default admin
            foreach ($default_services as $service) {
                $CI->db->insert(db_prefix() . 'appointly_services', $service);
                $service_id = $CI->db->insert_id();

                // Assign to default admin (staff_id = 1)
                $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                    'service_id' => $service_id,
                    'staff_id' => 1,
                    'is_provider' => 1,
                    'is_primary' => 1
                ]);
            }
            log_message('info', 'Appointly Module: Assigned default services to admin');
        }

        // Check if there are any service staff assignments
        $staff_assignments_count = 0;
        if ($CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $staff_assignments_count = $CI->db->count_all(db_prefix() . 'appointly_service_staff');
        }

        if ($staff_assignments_count == 0) {
            // Get all services - only if table exists
            if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
                $services = $CI->db->get(db_prefix() . 'appointly_services')->result_array();

                // Assign each service to admin
                foreach ($services as $service) {
                    $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                        'service_id' => $service['id'],
                        'staff_id' => 1,
                        'is_provider' => 1,
                        'is_primary' => 1
                    ]);
                }

                log_message('info', 'Appointly Module: Assigned default services to admin');
            }
        }

        // Check for missing columns after all tables are created
        checkForMissingColumns($CI);

        checkForModuleReinstallation();
    }

    /**
     * @param $CI
     *
     * @return void
     */
    function checkForMissingColumns($CI): void
    {
        // Check if end_hour exists, if not add it
        if (!$CI->db->field_exists('end_hour', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` ADD `end_hour` varchar(191) NOT NULL DEFAULT '' AFTER `start_hour`;");
        }

        // Check if duration exists, if not add it
        if (!$CI->db->field_exists('duration', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` ADD `duration` varchar(100) DEFAULT NULL AFTER `start_hour`;");
        }

        if (! $CI->db->field_exists(
            'service_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `service_id` int(11) DEFAULT NULL AFTER `id`");
        }

        if (! $CI->db->field_exists(
            'date_created',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `date_created` datetime DEFAULT CURRENT_TIMESTAMP AFTER `id`");
        }

        // Check for invoice integration columns
        if (! $CI->db->field_exists(
            'invoice_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `invoice_id` int(11) NULL DEFAULT NULL AFTER `feedback_comment`");
        }

        if (! $CI->db->field_exists(
            'invoice_date',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `invoice_date` datetime NULL DEFAULT NULL AFTER `invoice_id`");
        }

        // Check for provider_id in appointments table
        if (! $CI->db->field_exists('provider_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `created_by`;");
        }

        // Check for is_primary in service_staff table - only if table exists
        if (
            $CI->db->table_exists(db_prefix() . 'appointly_service_staff') &&
            !$CI->db->field_exists('is_primary', db_prefix() . 'appointly_service_staff')
        ) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_service_staff` 
                ADD COLUMN `is_primary` TINYINT(1) DEFAULT 0 AFTER `is_provider`;");

            // Mark the first provider for each service as primary
            $CI->db->query("
                UPDATE " . db_prefix() . "appointly_service_staff ss1
                JOIN (
                    SELECT service_id, MIN(id) as min_id
                    FROM " . db_prefix() . "appointly_service_staff
                    WHERE is_provider = 1
                    GROUP BY service_id
                ) ss2 ON ss1.service_id = ss2.service_id AND ss1.id = ss2.min_id
                SET ss1.is_primary = 1
            ");
        }

        // Check for status field in appointments table
        if (!$CI->db->field_exists('status', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` 
                ADD COLUMN `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress' AFTER `hash`;");

            // Migrate existing statuses to the new field
            $CI->db->query("UPDATE " . db_prefix() . "appointly_appointments 
                SET status = CASE 
                    WHEN finished = 1 THEN 'completed'
                    WHEN cancelled = 1 THEN 'cancelled'
                    WHEN approved = 1 THEN 'in-progress'
                    ELSE 'pending' 
                END");
        }

        // Check for buffer times in services table - CRITICAL for time slot generation
        if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
            if (!$CI->db->field_exists('buffer_before', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_before` int(11) DEFAULT 0 AFTER `duration`;");
            }

            if (!$CI->db->field_exists('buffer_after', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_after` int(11) DEFAULT 0 AFTER `buffer_before`;");
            }
        }
    }
}


if (! function_exists('init_appointly_template_tables')) {
    /**
     * Insert email templates into database
     */
    function init_appointly_template_tables()
    {
        create_email_template(
            'You have an upcoming appointment!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname} </span><br /><br /><span style=\"font-size: 12pt;\"> You have an upcoming appointment that is need to be held date {appointment_date} and location {appointment_location}</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_admin_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment reminder (Sent to Staff and Attendees)',
            'appointment-cron-reminder-to-staff'
        );

        create_email_template(
            'Your appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}.</span><br /><br /><span style=\"font-size: 12pt;\"> Your appointment has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_public_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment updated (Sent to Contact)',
            'appointment-updated-to-contact'
        );

        create_email_template(
            'Appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname}.</span><br /><br /><span style=\"font-size: 12pt;\"> An appointment that you are attending has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>Client:</strong> {appointment_client_name}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_admin_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment updated (Sent to Staff)',
            'appointment-updated-to-staff'
        );

        create_email_template(
            'Recurring appointment was re-created!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname} </span><br /><br /><span style=\"font-size: 12pt;\"> Your recurring appointment was recreated with date {appointment_date} and location {appointment_location}</span><br /><br /><span style=\"font-size: 12pt;\"><strong> Additional info for your appointment:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_admin_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment recurring (Sent to Staff and Attendees)',
            'appointment-recurring-recreated-to-staff'
        );

        create_email_template(
            'You have an upcoming appointment!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}. </span><br /><br /><span style=\"font-size: 12pt;\"> You have an upcoming appointment that is need to be held date {appointment_date} {appointment_location}.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_public_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment reminder (Sent to Contact)',
            'appointment-cron-reminder-to-contact'
        );

        create_email_template(
            'Recurring appointment was re-created!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}. </span><br /><br /><span style=\"font-size: 12pt;\"> Your recurring appointment was recreated with date {appointment_date} {appointment_location}.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_public_url}">Your appointment URL</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment recurring (Sent to Contact)',
            'appointment-recurring-recreated-to-contacts'
        );

        create_email_template(
            'Your appointment has been approved!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}.</span><br /><br /><span style=\"font-size: 12pt;\"> You appointment has been approved!</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style=\"font-size: 12pt;\"><strong>You can keep track of your appointment at the following link:</strong> <a href="{appointment_public_url}">Your appointment URL</a></span><br /><span style=\"font-size: 12pt;\"><br/>If you have any questions Please contact us for more information.</span><br /><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment approved (Sent to Contact)',
            'appointment-approved-to-contact'
        );

        create_email_template(
            'New appointment request via external form!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname}<br /><br />New appointment request submitted via external form</span>.<br /><br /><span 12pt=""><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><br /><span 12pt=""><strong>Appointment Description:</strong> {appointment_description}</span><br /><br /><span 12pt=""><strong>Appointment requested scheduled start date:</strong> {appointment_date}</span><br /><br /><span 12pt=""><strong>You can view this appointment request at the following link:</strong> <a href="{appointment_admin_url}">{appointment_admin_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'New appointment request (Sent to Staff)',
            'appointment-submitted-to-staff'
        );

        create_email_template(
            'Thank you for your appointment request!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment request! We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Your Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Requested Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;"><strong>What happens next?</strong></span><br /><span style="font-size: 12pt;">• Our staff will review your request within 24 hours</span><br /><span style="font-size: 12pt;">• You will receive a confirmation email once your appointment is approved</span><br /><span style="font-size: 12pt;">• If we need to suggest alternative times, we will contact you directly</span><br /><br /><span style="font-size: 12pt;">You can view your appointment status at any time using this link: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for choosing our services!</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment request confirmation (Sent to Contact)',
            'appointment-submitted-to-contact'
        );

        create_email_template(
            'Feedback request for appointment!',
            '<span 12pt=""><span 12pt="">Hello {appointment_client_name} <br /><br />A new feedback request has just been submitted, please leave your comments and thoughts about this past appointment, fast navigate to the appointment to add a feedback:</strong> <a href="{appointment_public_url}">{appointment_public_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'Request Appointment Feedback (Sent to Client)',
            'appointment-request-feedback'
        );

        create_email_template(
            'New appointment feedback rating received!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname} <br /><br />A new feedback rating has been received from client {appointment_client_name}. View the new feedback rating submitted at the following link:</strong> <a href="{appointment_admin_url}">{appointment_admin_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'New Feedback Received (Sent to Staff)',
            'appointment-feedback-received'
        );

        create_email_template(
            'Appointment feedback rating updated!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname} <br /><br />An existing feedback was just updated from client {appointment_client_name}. View the new rating submitted at the following link:</strong> <a href="{appointment_admin_url}">{appointment_admin_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'Feedback Updated (Sent to Staff)',
            'appointment-feedback-updated'
        );

        create_email_template(
            'Client requested appointment cancellation!',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">A client has requested to cancel their appointment. Please review and take appropriate action.</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Scheduled Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Cancellation Reason:</strong> {appointment_cancel_notes}</span><br /><br /><span style="font-size: 12pt;"><strong>Action Required:</strong></span><br /><span style="font-size: 12pt;">Please review this cancellation request and process it accordingly. You can view the appointment details at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Client Cancellation Request (Sent to Staff)',
            'appointment-cancellation-request-to-staff'
        );

        // Client cancellation request - Client confirmation
        create_email_template(
            'Your cancellation request has been received!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment cancellation request. We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Scheduled Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Your Cancellation Reason:</strong> {appointment_cancel_notes}</span><br /><br /><span style="font-size: 12pt;"><strong>What happens next?</strong></span><br /><span style="font-size: 12pt;">• Our staff will review your cancellation request</span><br /><span style="font-size: 12pt;">• You will receive a confirmation email once the cancellation is processed</span><br /><span style="font-size: 12pt;">• If you have any questions, please contact us directly</span><br /><br /><span style="font-size: 12pt;">You can view your appointment status at any time using this link: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for your understanding.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Cancellation Request Confirmation (Sent to Client)',
            'appointment-cancellation-request-confirmation-to-client'
        );

        // Provider change notifications
        create_email_template(
            'You have been removed as provider for an appointment',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">You have been removed as the provider for the following appointment:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">A different provider has been assigned to handle this appointment. You no longer need to prepare for or attend this appointment.</span><br /><br /><span style="font-size: 12pt;">If you have any questions about this change, please contact the appointment creator or administrator.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Provider Removed from Appointment (Sent to Old Provider)',
            'appointment-provider-removed'
        );

        create_email_template(
            'You have been assigned as provider for an appointment',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">You have been assigned as the provider for the following appointment:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;">Please prepare for this appointment and ensure you are available at the scheduled time.</span><br /><br /><span style="font-size: 12pt;">You can view the full appointment details at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Provider Assigned to Appointment (Sent to New Provider)',
            'appointment-provider-assigned'
        );

        // Client reschedule request - Client confirmation
        create_email_template(
            'Your reschedule request has been received!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment reschedule request. We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Current Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Current Date & Time:</strong> {appointment_date}</span><br /><br /><span style="font-size: 12pt;"><strong>Requested New Date & Time:</strong> {reschedule_requested_date} {reschedule_requested_time}</span><br /><span style="font-size: 12pt;"><strong>Reason for Reschedule:</strong> {reschedule_reason}</span><br /><br /><span style="font-size: 12pt;"><strong>What happens next?</strong></span><br /><span style="font-size: 12pt;">• Our staff will review your reschedule request</span><br /><span style="font-size: 12pt;">• You will receive a confirmation email once the request is approved or denied</span><br /><span style="font-size: 12pt;">• If we need to suggest alternative times, we will contact you directly</span><br /><br /><span style="font-size: 12pt;">You can view your appointment status at any time using this link: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for choosing our services!</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Reschedule Request Confirmation (Sent to Client)',
            'appointment-reschedule-request-confirmation-to-client'
        );

        create_email_template(
            'Client has requested to reschedule appointment',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">A client has requested to reschedule their appointment. Please review and take appropriate action.</span><br /><br /><span style="font-size: 12pt;"><strong>Current Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Current Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;"><strong>Requested New Date & Time:</strong> {reschedule_requested_date} {reschedule_requested_time}</span><br /><span style="font-size: 12pt;"><strong>Reason for Reschedule:</strong> {reschedule_reason}</span><br /><br /><span style="font-size: 12pt;"><strong>Action Required:</strong></span><br /><span style="font-size: 12pt;">Please review this reschedule request and approve or deny it accordingly. You can view the appointment details and process the request at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Client Reschedule Request (Sent to Staff)',
            'appointly-reschedule-request-to-staff'
        );

        // Attendee change notifications
        create_email_template(
            'You have been removed from an appointment',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">You have been removed from the following appointment:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">You are no longer required to attend this appointment. Please update your calendar accordingly.</span><br /><br /><span style="font-size: 12pt;">If you have any questions about this change, please contact the appointment creator or administrator.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Attendee Removed from Appointment (Sent to Removed Attendee)',
            'appointment-attendee-removed'
        );

        // Status change notifications - Completed
        create_email_template(
            'Appointment has been marked as completed',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">The following appointment has been marked as completed:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">This appointment has been successfully completed. Please ensure any follow-up actions are taken as needed.</span><br /><br /><span style="font-size: 12pt;">You can view the appointment details at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment Completed (Sent to Staff)',
            'appointment-completed-to-staff'
        );

        create_email_template(
            'Your appointment has been completed',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Your appointment has been successfully completed:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;">Thank you for attending your appointment. We hope you had a positive experience with our services.</span><br /><br /><span style="font-size: 12pt;">If you need any follow-up assistance or have feedback about your appointment, please don\'t hesitate to contact us.</span><br /><br /><span style="font-size: 12pt;">You can view your appointment details at: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for choosing our services!</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment Completed (Sent to Client)',
            'appointment-completed-to-contact'
        );

        // Status change notifications - No Show
        create_email_template(
            'Appointment has been marked as no-show',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">The following appointment has been marked as no-show:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">The client did not attend this appointment. Please review your no-show policy and take appropriate action if needed.</span><br /><br /><span style="font-size: 12pt;">You can view the appointment details at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment No-Show (Sent to Staff)',
            'appointment-no-show-to-staff'
        );

        create_email_template(
            'You missed your scheduled appointment',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">We noticed that you did not attend your scheduled appointment:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;">If you had an emergency or unexpected situation that prevented you from attending, we understand that these things happen.</span><br /><br /><span style="font-size: 12pt;">If you would like to reschedule your appointment, please contact us at your earliest convenience. We are here to help accommodate your scheduling needs.</span><br /><br /><span style="font-size: 12pt;">You can contact us directly or view your appointment details at: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for your understanding.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment No-Show (Sent to Client)',
            'appointment-no-show-to-contact'
        );

        create_email_template(
            'You are added as a appointment attendee!',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">You are added as a appointment attendee.</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style="font-size: 12pt;"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>You can view this appointment at the following link:</strong> <a href="{appointment_admin_url}">Your appointment URL</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment approved (Sent to Staff and Attendees)',
            'appointment-approved-to-staff-attendees'
        );

        create_email_template(
            'Your reschedule request has been approved!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Great news! Your appointment reschedule request has been approved.</span><br /><br /><span style="font-size: 12pt;"><strong>Updated Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>New Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">Your appointment has been successfully rescheduled. Please make note of the new date and time.</span><br /><br /><span style="font-size: 12pt;">You can view your updated appointment details at: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for choosing our services!</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Reschedule Request Approved (Sent to Client)',
            'appointment-reschedule-approved-to-contact'
        );

        create_email_template(
            'Your reschedule request has been denied',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">We have reviewed your appointment reschedule request, but unfortunately we are unable to accommodate the requested change at this time.</span><br /><br /><span style="font-size: 12pt;"><strong>Original Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Current Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;"><strong>Reason for denial:</strong> {reschedule_denial_reason}</span><br /><br /><span style="font-size: 12pt;">Your original appointment remains scheduled as planned. If you need to make alternative arrangements, please contact us directly and we will do our best to help find a suitable solution.</span><br /><br /><span style="font-size: 12pt;">You can view your appointment details at: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">We apologize for any inconvenience and appreciate your understanding.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Reschedule Request Denied (Sent to Client)',
            'appointment-reschedule-denied-to-contact'
        );

        // Add missing email templates that have mail classes but no database entries
        create_email_template(
            'Client has requested to reschedule appointment',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">A client has requested to reschedule their appointment. Please review and take appropriate action.</span><br /><br /><span style="font-size: 12pt;"><strong>Current Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Current Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;"><strong>Requested New Date & Time:</strong> {reschedule_requested_date} {reschedule_requested_time}</span><br /><span style="font-size: 12pt;"><strong>Reason for Reschedule:</strong> {reschedule_reason}</span><br /><br /><span style="font-size: 12pt;"><strong>Action Required:</strong></span><br /><span style="font-size: 12pt;">Please review this reschedule request and approve or deny it accordingly. You can view the appointment details and process the request at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Client Reschedule Request (Sent to Staff)',
            'appointment-reschedule-request-to-staff'
        );

        create_email_template(
            'Appointment has been cancelled!',
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">The following appointment has been cancelled:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;">Please update your calendar and make any necessary adjustments to your schedule.</span><br /><br /><span style="font-size: 12pt;">You can view the appointment details at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment Cancelled (Sent to Staff)',
            'appointment-notification-cancelled-to-staff'
        );

        create_email_template(
            'Your appointment has been cancelled',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">We regret to inform you that your appointment has been cancelled:</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Provider:</strong> {appointment_provider_name}</span><br /><span style="font-size: 12pt;"><strong>Location:</strong> {appointment_location}</span><br /><br /><span style="font-size: 12pt;">We apologize for any inconvenience this may cause. If you would like to reschedule, please contact us and we will be happy to help you find a new suitable time.</span><br /><br /><span style="font-size: 12pt;">You can contact us directly or view your appointment details at: <a href="{appointment_public_url}">View Your Appointment</a></span><br /><br /><span style="font-size: 12pt;">Thank you for your understanding.</span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment Cancelled (Sent to Client)',
            'appointment-notification-cancelled-to-contact'
        );
    }
}

if (! function_exists('init_appointly_install_sequence')) {
    /**
     * Initialize tables content example data for email templates and sms in database
     */
    function init_appointly_install_sequence()
    {
        init_appointly_database_tables();
        init_appointly_template_tables();

        log_message('info', 'Appointly: Installation sequence completed with browser cache clearing');
    }
}


if (! function_exists('checkForModuleReinstallation')) {
    /**
     * Percussion database checks
     */
    function checkForModuleReinstallation()
    {
        $CI = &get_instance();

        $table_name = db_prefix() . 'appointly_appointments';

        if ($CI->db->table_exists($table_name)) {
            $CI->db->query("DROP TABLE IF EXISTS " . db_prefix()
                . "appointly_appointment_types");

            if (! $CI->db->field_exists('notes', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `notes` LONGTEXT NULL AFTER `address`;"
                );
            }

            if (! $CI->db->field_exists('google_event_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_event_id` VARCHAR(191) NULL DEFAULT NULL AFTER `id`;"
                );
            }

            if (! $CI->db->field_exists('google_calendar_link', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_calendar_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_event_id`;"
                );
            }

            if (! $CI->db->field_exists('google_added_by_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_added_by_id` int(11) NULL DEFAULT NULL AFTER `google_calendar_link`;"
                );
            }
            if (! $CI->db->field_exists('google_meet_link', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_meet_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_calendar_link`;"
                );
            }
            // New check for provider_id
            if (! $CI->db->field_exists('provider_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `provider_id` INT(11) NULL AFTER `created_by`;"
                );
            }

            // Check for timezone field
            if (! $CI->db->field_exists('timezone', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `timezone` VARCHAR(100) DEFAULT NULL AFTER `date`;"
                );
            }
        }

        // If type_id exists in appointments table, remove it - only if table exists
        if ($CI->db->table_exists($table_name) && $CI->db->field_exists(
            'type_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments DROP COLUMN `type_id`");
        }

        // Check for working_hours field in service_staff table - only if table exists
        if (
            $CI->db->table_exists(db_prefix() . 'appointly_service_staff') &&
            !$CI->db->field_exists('working_hours', db_prefix() . 'appointly_service_staff')
        ) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_service_staff 
                ADD COLUMN `working_hours` text DEFAULT NULL AFTER `is_provider`");
        }
    }
}
