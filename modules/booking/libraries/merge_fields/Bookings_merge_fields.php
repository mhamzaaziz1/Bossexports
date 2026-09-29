<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bookings_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Booking Name',
                    'key'       => '{booking_name}',
                    'available' => [
                        'booking',
                    ],
                ],
                [
                    'name'      => 'Booking Description',
                    'key'       => '{booking_description}',
                    'available' => [
                        'booking',
                    ],
                ],
                [
                    'name'      => 'Booking Start Date',
                    'key'       => '{booking_start_date}',
                    'available' => [
                        'booking',
                    ],
                ],
                [
                    'name'      => 'Booking Deadline',
                    'key'       => '{booking_deadline}',
                    'available' => [
                        'booking',
                    ],
                ],
                [
                    'name'      => 'Booking Link',
                    'key'       => '{booking_link}',
                    'available' => [
                        'booking',
                    ],
                ],
                    [
                    'name'      => 'File Creator',
                    'key'       => '{file_creator}',
                    'available' => [
                    ],
                    'templates' => [
                        'new-booking-file-uploaded-to-customer',
                        'new-booking-file-uploaded-to-staff',
                    ],
                ],
                [
                    'name'      => 'Comment Creator',
                    'key'       => '{comment_creator}',
                    'available' => [
                    ],
                    'templates' => [
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                    ],
                ],
                [
                    'name'      => 'Discussion Link',
                    'key'       => '{discussion_link}',
                    'available' => [
                    ],
                    'templates' => [
                        'new-booking-discussion-created-to-staff',
                        'new-booking-discussion-created-to-customer',
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                        'new-booking-file-uploaded-to-staff',
                        'new-booking-file-uploaded-to-customer',
                    ],
                ],
                [
                    'name'      => 'Discussion Subject',
                    'key'       => '{discussion_subject}',
                    'available' => [
                    ],
                     'templates' => [
                        'new-booking-discussion-created-to-staff',
                        'new-booking-discussion-created-to-customer',
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                        'new-booking-file-uploaded-to-staff',
                        'new-booking-file-uploaded-to-customer',
                    ],
                ],
                [
                    'name'      => 'Discussion Description',
                    'key'       => '{discussion_description}',
                    'available' => [
                    ],
                     'templates' => [
                        'new-booking-discussion-created-to-staff',
                        'new-booking-discussion-created-to-customer',
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                    ],
                ],
                [
                    'name'      => 'Discussion Creator',
                    'key'       => '{discussion_creator}',
                    'available' => [
                    ],
                    'templates' => [
                        'new-booking-discussion-created-to-staff',
                        'new-booking-discussion-created-to-customer',
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                    ],
                ],
                [
                    'name'      => 'Discussion Comment',
                    'key'       => '{discussion_comment}',
                    'available' => [
                    ],
                    'templates' => [
                        'new-booking-discussion-comment-to-customer',
                        'new-booking-discussion-comment-to-staff',
                    ],
                ],
            ];
    }

    /**
     * Booking merge fields
     * @param  mixed $booking_id      booking id
     * @param  array  $additional_data option to pass additional data for the templates eq is staff template or customer template
     * This field is also used for the booking discussion files and regular discussions
     * @return array
     */
    public function format($booking_id, $additional_data = [])
    {
        $fields = [];

        $fields['{booking_name}']           = '';
        $fields['{booking_deadline}']       = '';
        $fields['{booking_start_date}']     = '';
        $fields['{booking_description}']    = '';
        $fields['{booking_link}']           = '';
        $fields['{discussion_link}']        = '';
        $fields['{discussion_creator}']     = '';
        $fields['{comment_creator}']        = '';
        $fields['{file_creator}']           = '';
        $fields['{discussion_subject}']     = '';
        $fields['{discussion_description}'] = '';
        $fields['{discussion_comment}']     = '';


        $this->ci->db->where('id', $booking_id);
        $booking = $this->ci->db->get(db_prefix().'bookings')->row();

        $fields['{booking_name}']        = $booking->name;
        $fields['{booking_deadline}']    = _d($booking->deadline);
        $fields['{booking_start_date}']  = _d($booking->start_date);
        $fields['{booking_description}'] = $booking->description;

        $custom_fields = get_custom_fields('bookings');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($booking_id, $field['id'], 'bookings');
        }

        if (is_client_logged_in()) {
            $cf = get_contact_full_name(get_contact_user_id());
        } else {
            $cf = get_staff_full_name(get_staff_user_id());
        }

        $fields['{file_creator}']       = $cf;
        $fields['{discussion_creator}'] = $cf;
        $fields['{comment_creator}']    = $cf;

        if (isset($additional_data['discussion_id'])) {
            $this->ci->db->where('id', $additional_data['discussion_id']);

            if (isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular') {
                $table = db_prefix().'bookingdiscussions';
            } else {
                // is file
                $table = db_prefix().'booking_files';
            }

            $discussion = $this->ci->db->get($table)->row();

            $fields['{discussion_subject}']     = $discussion->subject;
            $fields['{discussion_description}'] = $discussion->description;

            if (isset($additional_data['discussion_comment_id'])) {
                $this->ci->db->where('id', $additional_data['discussion_comment_id']);
                $discussion_comment             = $this->ci->db->get(db_prefix().'bookingdiscussioncomments')->row();
                $fields['{discussion_comment}'] = $discussion_comment->content;
            }
        }
        if (isset($additional_data['customer_template'])) {
            $fields['{booking_link}'] = site_url('clients/booking/' . $booking_id);

            if (isset($additional_data['discussion_id']) && isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular') {
                $fields['{discussion_link}'] = site_url('clients/booking/' . $booking_id . '?group=booking_discussions&discussion_id=' . $additional_data['discussion_id']);
            } elseif (isset($additional_data['discussion_id']) && isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'file') {
                // is file
                $fields['{discussion_link}'] = site_url('clients/booking/' . $booking_id . '?group=booking_files&file_id=' . $additional_data['discussion_id']);
            }
        } else {
            $fields['{booking_link}'] = admin_url('booking/bookings/view/' . $booking_id);
            if (isset($additional_data['discussion_type']) && $additional_data['discussion_type'] == 'regular' && isset($additional_data['discussion_id'])) {
                $fields['{discussion_link}'] = admin_url('booking/bookings/view/' . $booking_id . '?group=booking_discussions&discussion_id=' . $additional_data['discussion_id']);
            } else {
                if (isset($additional_data['discussion_id'])) {
                    // is file
                    $fields['{discussion_link}'] = admin_url('booking/bookings/view/' . $booking_id . '?group=booking_files&file_id=' . $additional_data['discussion_id']);
                }
            }
        }

        $custom_fields = get_custom_fields('bookings');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($booking_id, $field['id'], 'bookings');
        }

        return hooks()->apply_filters('booking_merge_fields', $fields, [
        'id'              => $booking_id,
        'booking'         => $booking,
        'additional_data' => $additional_data,
     ]);
    }
}
