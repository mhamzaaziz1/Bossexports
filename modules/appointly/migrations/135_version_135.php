<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
{
    public function up()
    {
        if (!get_option('appointly_show_staff_phone')) {
            add_option('appointly_show_staff_phone', 0);
        }
        // Client cancellation request - Staff notification
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
            '<span style="font-size: 12pt;">Hello {staff_firstname} {staff_lastname},</span><br /><br /><span style="font-size: 12pt;">A client has requested to reschedule their appointment. Please review and take appropriate action.</span><br /><br /><span style="font-size: 12pt;"><strong>Current Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Client:</strong> {appointment_client_name}</span><br /><span style="font-size: 12pt;"><strong>Current Date & Time:</strong> {appointment_date}</span><br /><br /><span style="font-size: 12pt;"><strong>Requested New Date & Time:</strong> {reschedule_requested_date} {reschedule_requested_time}</span><br /><span style="font-size: 12pt;"><strong>Reason for Reschedule:</strong> {reschedule_reason}</span><br /><br /><span style="font-size: 12pt;"><strong>Action Required:</strong></span><br /><span style="font-size: 12pt;">Please review this reschedule request and approve or deny it accordingly. You can view the appointment details and process the request at: <a href="{appointment_admin_url}">View Appointment</a></span><br /><br /><span style="font-size: 12pt;">Best regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Client Reschedule Request (Sent to Staff)',
            'appointment-reschedule-request-to-staff'
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
            'Your appointment has been approved!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Your appointment has been approved!</span><br /><br /><span style="font-size: 12pt;"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Appointment Description:</strong> {appointment_description}</span><br /><span style="font-size: 12pt;"><strong>Appointment scheduled date to start:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>You can keep track of your appointment at the following link:</strong> <a href="{appointment_public_url}">Your appointment URL</a></span><br /><br /><span style="font-size: 12pt;">If you have any questions Please contact us for more information.</span><br /><br /><span style="font-size: 12pt;">Kind Regards,<br />{companyname}<br />{email_signature}</span>',
            'appointly',
            'Appointment approved (Sent to Contact)',
            'appointment-approved-to-contact'
        );

        // Reschedule response notifications
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

        // Add missing notification cancelled templates
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

        // Add database indexes to improve query performance and fix N+1 problems
        $CI = &get_instance();

        // Helper function to safely add index
        $addIndexSafely = function ($table, $indexName, $columns) use ($CI) {
            try {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD INDEX `" . $indexName . "` (" . $columns . ")");
            } catch (Exception $e) {
                // Index might already exist, continue silently
            }
        };

        // Index for appointments table - most critical for performance
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_date_status", "`date`, `status`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_contact_id", "`contact_id`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_service_id", "`service_id`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_provider_id", "`provider_id`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_created_by", "`created_by`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_google_event_id", "`google_event_id`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_date_start_hour", "`date`, `start_hour`");
        $addIndexSafely(db_prefix() . "appointly_appointments", "idx_approved_status", "`approved`, `status`");

        // Index for services table
        $addIndexSafely(db_prefix() . "appointly_services", "idx_active", "`active`");

        // Index for attendees table
        $addIndexSafely(db_prefix() . "appointly_attendees", "idx_appointment_id", "`appointment_id`");
        $addIndexSafely(db_prefix() . "appointly_attendees", "idx_staff_id", "`staff_id`");
        $addIndexSafely(db_prefix() . "appointly_attendees", "idx_appointment_staff", "`appointment_id`, `staff_id`");

        // Index for service_staff table - for provider lookups
        $addIndexSafely(db_prefix() . "appointly_service_staff", "idx_service_staff", "`service_id`, `staff_id`");
        $addIndexSafely(db_prefix() . "appointly_service_staff", "idx_staff_provider", "`staff_id`, `is_provider`");

        // Index for reschedule_requests table
        $addIndexSafely(db_prefix() . "appointly_reschedule_requests", "idx_status_requested", "`status`, `requested_at`");

        // Index for staff_working_hours table
        $addIndexSafely(db_prefix() . "appointly_staff_working_hours", "idx_staff_weekday", "`staff_id`, `weekday`");

        // Index for appointment_services table
        $addIndexSafely(db_prefix() . "appointly_appointment_services", "idx_service_appointment", "`service_id`, `appointment_id`");

        // Add new setting for auto-adding to Google Calendar on approval
        add_option('appointly_auto_add_to_google_on_approval', 0);
        add_option('appointments_external_form_show_language_dropdown', 0);

        // Fix: Increase access_token and refresh_token column sizes for Google OAuth
        // Modern OAuth tokens can exceed 191 characters, causing "Data too long" errors
        // Note: The Googlecalendar model also has auto-migration for multi-tenant systems
        if ($CI->db->table_exists(db_prefix() . 'appointly_google')) {
            // Check if columns need to be altered
            $fields = $CI->db->field_data(db_prefix() . 'appointly_google');
            $needs_update = false;

            foreach ($fields as $field) {
                if ($field->name === 'access_token' && $field->type === 'varchar') {
                    $needs_update = true;
                    break;
                }
            }

            if ($needs_update) {
                // Alter columns to LONGTEXT to support longer OAuth tokens (up to 4GB)
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_google` 
                    MODIFY COLUMN `access_token` LONGTEXT,
                    MODIFY COLUMN `refresh_token` LONGTEXT");
            }
        }
    }
}
