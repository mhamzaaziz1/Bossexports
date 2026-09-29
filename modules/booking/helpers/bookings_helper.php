<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('app_admin_assets', '_maybe_init_admin_booking_assets', 5);

function _maybe_init_admin_booking_assets()
{
    $CI = &get_instance();
    if (strpos($_SERVER['REQUEST_URI'], get_admin_uri() . '/bookings/view') !== false
        || strpos($_SERVER['REQUEST_URI'], get_admin_uri() . '/bookings/gantt') !== false) {
        $CI = &get_instance();

        $CI->app_scripts->add('jquery-comments-js', 'assets/plugins/jquery-comments/js/jquery-comments.min.js', 'admin', ['vendor-js']);
        $CI->app_scripts->add('frappe-gantt-js','assets/plugins/frappe/frappe-gantt-es2015.js', 'admin', ['vendor-js']);

        $CI->app_css->add('frappe-gantt-js', 'assets/plugins//frappe/frappe-gantt.css', 'admin', ['vendor-css']);
        $CI->app_css->add('jquery-comments-css', 'assets/plugins/jquery-comments/css/jquery-comments.css', 'admin', ['reset-css']);
    }
}

/**
 * Default booking tabs
 * @return array
 */

function get_booking_tabs_admin()
{
    return get_instance()->app_tabs->get('booking');
}

/**
 * Filter only visible tabs selected from booking settings
 * @param  array $tabs available tabs
 * @param  array $applied_settings current applied booking visible tabs
 * @return array
 */
function filter_booking_visible_tabs($tabs, $applied_settings)
{
    $newTabs = [];
    foreach ($tabs as $key => $tab) {
        $dropdown = isset($tab['collapse']) ? true : false;

        if ($dropdown) {
            $totalChildTabsHidden = 0;
            $newChild             = [];

            foreach ($tab['children'] as $d) {
                if ((isset($applied_settings[$d['slug']]) && $applied_settings[$d['slug']] == 0)) {
                    $totalChildTabsHidden++;
                } else {
                    $newChild[] = $d;
                }
            }

            if ($totalChildTabsHidden == count($tab['children'])) {
                continue;
            }

            if (count($newChild) > 0) {
                $tab['children'] = $newChild;
            }

            $newTabs[$tab['slug']] = $tab;
        } else {
            if (isset($applied_settings[$key]) && $applied_settings[$key] == 0) {
                continue;
            }

            $newTabs[$tab['slug']] = $tab;
        }
    }

    return hooks()->apply_filters('booking_filtered_visible_tabs', $newTabs);
}

/**
 * Get booking by ID or current queried booking
 * @param  mixed $id booking id
 * @return mixed
 */
function get_booking($id = null)
{
    if (empty($id) && isset($GLOBALS['booking'])) {
        return $GLOBALS['booking'];
    }

    // Client global object not set
    if (empty($id)) {
        return null;
    }

    if (!class_exists('bookings_model', false)) {
        get_instance()->load->model('bookings_model');
    }

    $booking = get_instance()->bookings_model->get($id);

    return $booking;
}

/**
 * Get booking status by passed booking id
 * @param  mixed $id booking id
 * @return array
 */
function get_booking_status_by_id($id)
{
    $CI = &get_instance();
    if (!class_exists('bookings_model')) {
        $CI->load->model('bookings_model');
    }

    $statuses = $CI->bookings_model->get_booking_statuses();

    $status = [
          'id'    => 0,
          'color' => '#333',
          'name'  => '[Status Not Found]',
          'order' => 1,
      ];

    foreach ($statuses as $s) {
        if ($s['id'] == $id) {
            $status = $s;

            break;
        }
    }

    return $status;
}

/**
 * Return logged in user pinned bookings
 * @return array
 */
function get_user_pinned_bookings()
{
    $CI = &get_instance();
    $CI->db->select(db_prefix() . 'bookings.id, ' . db_prefix() . 'bookings.name, ' . db_prefix() . 'bookings.clientid, ' . get_sql_select_client_company());
    $CI->db->join(db_prefix() . 'bookings', db_prefix() . 'bookings.id=' . db_prefix() . 'pinned_bookings.booking_id');
    $CI->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'bookings.clientid');
    $CI->db->where(db_prefix() . 'pinned_bookings.staff_id', get_staff_user_id());
    $bookings = $CI->db->get(db_prefix() . 'pinned_bookings')->result_array();
    $CI->load->model('bookings_model');

    foreach ($bookings as $key => $booking) {
        $bookings[$key]['progress'] = $CI->bookings_model->calc_progress($booking['id']);
    }

    return $bookings;
}


/**
 * Get booking name by passed id
 * @param  mixed $id
 * @return string
 */
function get_booking_name_by_id($id)
{
    $CI      = & get_instance();
    $booking = $CI->app_object_cache->get('booking-name-data-' . $id);

    if (!$booking) {
        $CI->db->select('name');
        $CI->db->where('id', $id);
        $booking = $CI->db->get(db_prefix() . 'bookings')->row();
        $CI->app_object_cache->add('booking-name-data-' . $id, $booking);
    }

    if ($booking) {
        return $booking->name;
    }

    return '';
}

/**
 * Return booking milestones
 * @param  mixed $booking_id booking id
 * @return array
 */
function get_booking_milestones($booking_id)
{
    $CI = &get_instance();
    $CI->db->where('booking_id', $booking_id);
    $CI->db->order_by('milestone_order', 'ASC');

    return $CI->db->get(db_prefix() . 'milestones')->result_array();
}

/**
 * Get booking client id by passed booking id
 * @param  mixed $id booking id
 * @return mixed
 */
function get_client_id_by_booking_id($id)
{
    $CI = & get_instance();
    $CI->db->select('clientid');
    $CI->db->where('id', $id);
    $booking = $CI->db->get(db_prefix() . 'bookings')->row();
    if ($booking) {
        return $booking->clientid;
    }

    return false;
}

/**
 * Check if customer has booking assigned
 * @param  mixed $customer_id customer id to check
 * @return boolean
 */
function customer_has_bookings($customer_id)
{
    $totalCustomerBookings = total_rows(db_prefix() . 'bookings', 'clientid=' . get_instance()->db->escape_str($customer_id));

    return ($totalCustomerBookings > 0 ? true : false);
}

/**
 * Get booking billing type
 * @param  mixed $booking_id
 * @return mixed
 */
function get_booking_billing_type($booking_id)
{
    $CI = & get_instance();
    $CI->db->select('billing_type');
    $CI->db->where('id', $booking_id);
    $booking = $CI->db->get(db_prefix() . 'bookings')->row();
    if ($booking) {
        return $booking->billing_type;
    }

    return false;
}
/**
 * Get booking deadline
 * @param  mixed $booking_id
 * @return mixed
 */
function get_booking_deadline($booking_id)
{
    $CI = & get_instance();
    $CI->db->select('deadline');
    $CI->db->where('id', $booking_id);
    $booking = $CI->db->get(db_prefix() . 'bookings')->row();
    if ($booking) {
        return $booking->deadline;
    }

    return false;
}

/**
 * Translated jquery-comment language based on app languages
 * This feature is used on both admin and customer area
 * @return array
 */
function get_booking_discussions_language_array()
{
    $lang = [
        'discussion_add_comment'      => _l('discussion_add_comment'),
        'discussion_newest'           => _l('discussion_newest'),
        'discussion_oldest'           => _l('discussion_oldest'),
        'discussion_attachments'      => _l('discussion_attachments'),
        'discussion_send'             => _l('discussion_send'),
        'discussion_reply'            => _l('discussion_reply'),
        'discussion_edit'             => _l('discussion_edit'),
        'discussion_edited'           => _l('discussion_edited'),
        'discussion_you'              => _l('discussion_you'),
        'discussion_save'             => _l('discussion_save'),
        'discussion_delete'           => _l('discussion_delete'),
        'discussion_view_all_replies' => _l('discussion_view_all_replies'),
        'discussion_hide_replies'     => _l('discussion_hide_replies'),
        'discussion_no_comments'      => _l('discussion_no_comments'),
        'discussion_no_attachments'   => _l('discussion_no_attachments'),
        'discussion_attachments_drop' => _l('discussion_attachments_drop'),
    ];

    return $lang;
}

/**
 * Check if booking has recurring tours
 * @param  mixed $id booking id
 * @return boolean
 */
function booking_has_recurring_tours($id)
{
    return total_rows(db_prefix() . 'tours', 'recurring=1 AND rel_id="' . get_instance()->db->escape_str($id) . '" AND rel_type="booking"') > 0;
}

function total_booking_tours_by_milestone($milestone_id, $booking_id)
{
    return total_rows(db_prefix() . 'tours', [
              'rel_type'  => 'booking',
              'rel_id'    => $booking_id,
              'milestone' => $milestone_id,
             ]);
}

function total_booking_finished_tours_by_milestone($milestone_id, $booking_id)
{
    return total_rows(db_prefix() . 'tours', [
             'rel_type'  => 'booking',
             'rel_id'    => $booking_id,
             'status'    => 5,
             'milestone' => $milestone_id,
             ]);
}
