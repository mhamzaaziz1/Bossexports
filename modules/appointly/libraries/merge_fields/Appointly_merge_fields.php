<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_merge_fields extends App_merge_fields
{

    public function build()
    {
        return [
            [
                'name'      => 'Appointment Subject',
                'key'       => '{appointment_subject}',
                'available' => [
                    'appointly',
                ],
            ],
            [
                'name'      => 'Appointment Description',
                'key'       => '{appointment_description}',
                'available' => [
                    'appointly',
                ],
            ],
            [
                'name'      => 'Appointment Client Name',
                'key'       => '{appointment_client_name}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Client Email',
                'key'       => '{appointment_client_email}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Google Meet Link',
                'key'       => '{appointment_google_meet_link}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Client Phone',
                'key'       => '{appointment_client_phone}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Date',
                'key'       => '{appointment_date}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Location',
                'key'       => '{appointment_location}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Public Url',
                'key'       => '{appointment_public_url}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Appointment Admin Url',
                'key'       => '{appointment_admin_url}',
                'available' => [],
                'templates' => [
                    'appointment-approved-to-staff-attendees',
                    'appointment-cron-reminder-to-staff',
                    'appointment-notification-cancelled-to-staff',
                    'appointment-cancellation-request-to-staff',
                ]
            ],
            [
                'name'      => 'Appointment Cancel Notes',
                'key'       => '{appointment_cancel_notes}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Reschedule Requested Date',
                'key'       => '{reschedule_requested_date}',
                'available' => [],
                'templates' => [
                    'appointment-reschedule-request-confirmation-to-client',
                ]
            ],
            [
                'name'      => 'Reschedule Requested Time',
                'key'       => '{reschedule_requested_time}',
                'available' => [],
                'templates' => [
                    'appointment-reschedule-request-confirmation-to-client',
                ]
            ],
            [
                'name'      => 'Reschedule Reason',
                'key'       => '{reschedule_reason}',
                'available' => [],
                'templates' => [
                    'appointment-reschedule-request-confirmation-to-client',
                ]
            ],
            [
                'name'      => 'Cancellation Reason',
                'key'       => '{appointment_cancellation_reason}',
                'available' => [],
                'templates' => [
                    'appointment-cancellation-request-to-staff',
                    'appointment-cancellation-request-confirmation-to-client',
                ]
            ],
            [
                'name'      => 'Appointment Provider Name',
                'key'       => '{appointment_provider_name}',
                'available' => ['appointly'],
            ],
            [
                'name'      => 'Reschedule Denial Reason',
                'key'       => '{reschedule_denial_reason}',
                'available' => [],
                'templates' => [
                    'appointment-reschedule-denied-to-contact',
                ]
            ]
        ];
    }

    /**
     * Merge field for appointments
     *
     * @param mixed $appointment_id
     *
     * @return array
     */
    public function format($appointment_id)
    {
        $fields = [];

        $this->ci->db->where('id', $appointment_id);

        $appointment = $this->ci->db->get(db_prefix() . 'appointly_appointments')->row();

        if (! $appointment) return $fields;

        $fields['{appointment_subject}'] = e($appointment->subject);
        $fields['{appointment_description}'] = $appointment->description;
        $fields['{appointment_client_name}'] = e($appointment->name);
        $fields['{appointment_client_email}'] = e($appointment->email);
        $fields['{appointment_client_phone}'] = e($appointment->phone);
        $fields['{appointment_google_meet_link}'] = 'N/A';
        if ($appointment->google_meet_link) $fields['{appointment_google_meet_link}'] = $appointment->google_meet_link;
        // Format datetime following Perfex pattern: use _dt() for datetime and e() for escaping
        // Strip any time component from the date field before concatenating with start_hour
        $date_only = substr($appointment->date, 0, 10); // Extract YYYY-MM-DD only
        $datetime_string = $date_only . ' ' . $appointment->start_hour . ':00';
        $fields['{appointment_date}'] = e(_dt($datetime_string));
        $fields['{appointment_location}'] = e($appointment->address);
        $fields['{appointment_admin_url}'] = admin_url('appointly/appointments/view?appointment_id=' . $appointment->id);
        $fields['{appointment_public_url}'] = appointly_get_appointment_url($appointment->hash);
        $fields['{appointment_cancel_notes}'] = e($appointment->cancel_notes ?? '');

        // These fields are set dynamically in the notification functions for reschedule templates
        $fields['{reschedule_requested_date}'] = '';
        $fields['{reschedule_requested_time}'] = '';
        $fields['{reschedule_reason}'] = '';

        // This field is set dynamically in the notification functions for cancellation templates
        $fields['{appointment_cancellation_reason}'] = '';

        // Get provider information if available
        if (isset($appointment->provider_id) && $appointment->provider_id) {
            $this->ci->db->where('staffid', $appointment->provider_id);
            $provider = $this->ci->db->get(db_prefix() . 'staff')->row();
            $fields['{appointment_provider_name}'] = $provider ? e($provider->firstname . ' ' . $provider->lastname) : 'N/A';
        } else {
            $fields['{appointment_provider_name}'] = 'N/A';
        }

        // NOTE: {email_signature} is automatically provided by Perfex CRM core via Other_merge_fields

        // Reschedule denial reason - set dynamically in reschedule denial notifications
        $fields['{reschedule_denial_reason}'] = '';

        return $fields;
    }
}
