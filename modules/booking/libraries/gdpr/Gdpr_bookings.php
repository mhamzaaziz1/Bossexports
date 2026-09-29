<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gdpr_bookings
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function export($customer_id, $contact_id)
    {
        if (!class_exists('bookings_model')) {
            $this->ci->load->model('bookings_model');
        }

        $valAllowed = get_option('gdpr_contact_data_portability_allowed');
        if (empty($valAllowed)) {
            $valAllowed = [];
        } else {
            $valAllowed = unserialize($valAllowed);
        }

        $this->ci->db->where('clientid', $customer_id);
        $bookings = $this->ci->db->get(db_prefix() . 'bookings')->result_array();

        $this->ci->db->where('show_on_client_portal', 1);
        $this->ci->db->where('fieldto', 'bookings');
        $this->ci->db->order_by('field_order', 'asc');
        $custom_fields = $this->ci->db->get(db_prefix() . 'customfields')->result_array();

        foreach ($bookings as $bookingsKey => $booking) {
            if (in_array('related_tours', $valAllowed)) {
                $sql = 'SELECT * FROM ' . db_prefix() . 'tours WHERE (rel_id="' . $booking['id'] . '" AND rel_type="booking"';
                $sql .= ' AND addedfrom=' . $this->ci->db->escape_str($contact_id) . ' AND is_added_from_contact=1) OR (id IN (SELECT(tourid) FROM ' . db_prefix() . 'tour_comments WHERE contact_id=' . $this->ci->db->escape_str($contact_id) . '))';
                $tours = $this->ci->db->query($sql)->result_array();

                foreach ($tours as $tourKey => $tour) {
                    $this->ci->db->where('tourid', $tour['id']);
                    $this->ci->db->where('contact_id', $contact_id);
                    $tours[$tourKey]['comments'] = $this->ci->db->get(db_prefix() . 'tour_comments')->result_array();
                }
                $bookings[$bookingsKey]['tours'] = $tours;
            }

            if (in_array('related_discussions', $valAllowed)) {
                $sql = 'SELECT * FROM ' . db_prefix() . 'bookingdiscussions WHERE (booking_id="' . $booking['id'] . '"';
                $sql .= ' AND contact_id=' . $this->ci->db->escape_str($contact_id) . ') OR (id IN (SELECT(discussion_id) FROM ' . db_prefix() . 'bookingdiscussioncomments WHERE contact_id=' . $this->ci->db->escape_str($contact_id) . ' AND discussion_type="regular"))';

                $discussions = $this->ci->db->query($sql)->result_array();

                foreach ($discussions as $discussionKey => $discussion) {
                    $this->ci->db->where('discussion_id', $discussion['id']);
                    $this->ci->db->where('discussion_type', 'regular');
                    $this->ci->db->where('contact_id', $contact_id);
                    $discussions[$discussionKey]['comments'] = $this->ci->db->get(db_prefix() . 'bookingdiscussioncomments')->result_array();
                }

                $bookings[$bookingsKey]['discussions'] = $discussions;
            }

            if (in_array('bookings_activity_log', $valAllowed)) {
                $this->ci->db->where('booking_id', $booking['id']);
                $this->ci->db->where('contact_id', $contact_id);
                $bookings[$bookingsKey]['activity'] = $this->ci->db->get(db_prefix() . 'booking_activity')->result_array();
            }

            $bookings[$bookingsKey]['additional_fields'] = [];
            foreach ($custom_fields as $cf) {
                $bookings[$bookingsKey]['additional_fields'][] = [
                    'name'  => $cf['name'],
                    'value' => get_custom_field_value($booking['id'], $cf['id'], 'bookings'),
                ];
            }
        }

        return $bookings;
    }
}
