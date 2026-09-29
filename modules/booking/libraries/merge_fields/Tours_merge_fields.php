<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tours_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Staff/Contact who take action on tour',
                    'key'       => '{tour_user_take_action}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Link',
                    'key'       => '{tour_link}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Comment Link',
                    'key'       => '{comment_link}',
                    'available' => [
                    ],
                    'templates' => [
                        'tour-commented',
                        'tour-commented-to-contacts',
                    ],
                ],
                [
                    'name'      => 'Tour Name',
                    'key'       => '{tour_name}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Description',
                    'key'       => '{tour_description}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Status',
                    'key'       => '{tour_status}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Comment',
                    'key'       => '{tour_comment}',
                    'available' => [

                    ],
                    'templates' => [
                        'tour-commented',
                        'tour-commented-to-contacts',
                    ],
                ],
                [
                    'name'      => 'Tour Priority',
                    'key'       => '{tour_priority}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Start Date',
                    'key'       => '{tour_startdate}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Tour Due Date',
                    'key'       => '{tour_duedate}',
                    'available' => [
                        'tours',
                    ],
                ],
                [
                    'name'      => 'Related to',
                    'key'       => '{tour_related}',
                    'available' => [
                        'tours',
                    ],
                ],
            ];
    }

    /**
     * Merge fields for tours
     * @param  mixed  $tour_id         tour id
     * @param  boolean $client_template is client template or staff template
     * @return array
     */
    public function format($tour_id, $client_template = false)
    {
        $fields = [];

        $this->ci->db->where('id', $tour_id);
        $tour = $this->ci->db->get(db_prefix().'tours')->row();

        if (!$tour) {
            return $fields;
        }

        // Client templateonly passed when sending to tours related to booking and sending email template to contacts
        // Passed from tours_model  _send_tour_responsible_users_notification function
        if ($client_template == false) {
            $fields['{tour_link}'] = admin_url('tours/view/' . $tour_id);
        } else {
            $fields['{tour_link}'] = site_url('clients/booking/' . $tour->rel_id . '?group=booking_tours&tourid=' . $tour_id);
        }

        if (is_client_logged_in()) {
            $fields['{tour_user_take_action}'] = get_contact_full_name(get_contact_user_id());
        } else {
            $fields['{tour_user_take_action}'] = get_staff_full_name(get_staff_user_id());
        }

        $fields['{tour_comment}'] = '';
        $fields['{tour_related}'] = '';
        $fields['{booking_name}'] = '';

        if ($tour->rel_type == 'booking') {
            $this->ci->db->select('name, clientid');
            $this->ci->db->from(db_prefix().'bookings');
            $this->ci->db->where('id', $tour->rel_id);
            $booking = $this->ci->db->get()->row();
            if ($booking) {
                $fields['{booking_name}'] = $booking->name;
            }
        }

        if (!empty($tour->rel_id)) {
            $rel_data                 = get_relation_data($tour->rel_type, $tour->rel_id);
            $rel_values               = get_relation_values($rel_data, $tour->rel_type);
            $fields['{tour_related}'] = $rel_values['name'];
        }

        $fields['{tour_name}']        = $tour->name;
        $fields['{tour_description}'] = $tour->description;

        $languageChanged = false;

        // The tours status may not be translated if the client language is not loaded
        if (!is_client_logged_in()
        && $tour->rel_type == 'booking'
        && $booking
        && isset($GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS'])
        && !$GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS']->get_staff_id() // email to client
    ) {
            load_client_language($booking->clientid);
            $languageChanged = true;
        } else {
            if (isset($GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS'])) {
                $sending_to_staff_id = $GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS']->get_staff_id();
                if ($sending_to_staff_id) {
                    load_admin_language($sending_to_staff_id);
                    $languageChanged = true;
                }
            }
        }

        $fields['{tour_status}']   = format_tour_status($tour->status, false, true);
        $fields['{tour_priority}'] = tour_priority($tour->priority);

        $custom_fields = get_custom_fields('tours');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($tour_id, $field['id'], 'tours');
        }

        if (!is_client_logged_in() && $languageChanged) {
            load_admin_language();
        } elseif (is_client_logged_in() && $languageChanged) {
            load_client_language();
        }

        $fields['{tour_startdate}'] = _d($tour->startdate);
        $fields['{tour_duedate}']   = _d($tour->duedate);
        $fields['{comment_link}']   = '';

        $this->ci->db->where('tourid', $tour_id);
        $this->ci->db->limit(1);
        $this->ci->db->order_by('dateadded', 'desc');
        $comment = $this->ci->db->get(db_prefix().'tour_comments')->row();

        if ($comment) {
            $fields['{tour_comment}'] = $comment->content;
            $fields['{comment_link}'] = $fields['{tour_link}'] . '#comment_' . $comment->id;
        }

        return hooks()->apply_filters('tour_merge_fields', $fields, [
        'id'              => $tour_id,
        'tour'            => $tour,
        'client_template' => $client_template,
     ]);
    }
}
