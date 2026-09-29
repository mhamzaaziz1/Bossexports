<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Function that format tour status for the final user
 * @param  string  $id    status id
 * @param  boolean $text
 * @param  boolean $clean
 * @return string
 */
function format_tour_status($status, $text = false, $clean = false)
{
    if (!is_array($status)) {
        $status = get_tour_status_by_id($status);
    }

    $status_name = $status['name'];

    $status_name = hooks()->apply_filters('tour_status_name', $status_name, $status);

    if ($clean == true) {
        return $status_name;
    }

    $style = '';
    $class = '';
    if ($text == false) {
        $style = 'border: 1px solid ' . $status['color'] . ';color:' . $status['color'] . ';';
        $class = 'label';
    } else {
        $style = 'color:' . $status['color'] . ';';
    }

    return '<span class="' . $class . '" style="' . $style . '">' . $status_name . '</span>';
}

/**
 * Return predefined tours priorities
 * @return array
 */
function get_tours_priorities()
{
    return hooks()->apply_filters('tours_priorities', [
        [
            'id'    => 1,
            'name'  => _l('tour_priority_low'),
            'color' => '#777',

        ],
        [
            'id'    => 2,
            'name'  => _l('tour_priority_medium'),
            'color' => '#03a9f4',

        ],
        [
            'id'    => 3,
            'name'  => _l('tour_priority_high'),
            'color' => '#ff6f00',
        ],
        [
            'id'    => 4,
            'name'  => _l('tour_priority_urgent'),
            'color' => '#fc2d42',
        ],
    ]);
}

/**
 * Get booking name by passed id
 * @param  mixed $id
 * @return string
 */
function get_tour_subject_by_id($id)
{
    $CI = &get_instance();
    $CI->db->select('name');
    $CI->db->where('id', $id);
    $tour = $CI->db->get(db_prefix() . 'tours')->row();
    if ($tour) {
        return $tour->name;
    }

    return '';
}

/**
 * Get tour status by passed tour id
 * @param  mixed $id tour id
 * @return array
 */
function get_tour_status_by_id($id)
{
    $CI       = &get_instance();
    $statuses = $CI->tours_model->get_statuses();

    $status = [
        'id'         => 0,
        'bg_color'   => '#333',
        'text_color' => '#333',
        'name'       => '[Status Not Found]',
        'order'      => 1,
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
 * Format tour priority based on passed priority id
 * @param  mixed $id
 * @return string
 */
function tour_priority($id)
{
    foreach (get_tours_priorities() as $priority) {
        if ($priority['id'] == $id) {
            return $priority['name'];
        }
    }

    // Not exists?
    return $id;
}

/**
 * Get and return tour priority color
 * @param  mixed $id priority id
 * @return string
 */
function tour_priority_color($id)
{
    foreach (get_tours_priorities() as $priority) {
        if ($priority['id'] == $id) {
            return $priority['color'];
        }
    }

    // Not exists?
    return '#333';
}

/**
 * Format tour relation name
 * @param  string $rel_name current rel name
 * @param  mixed $rel_id   relation id
 * @param  string $rel_type relation type
 * @return string
 */
function tour_rel_name($rel_name, $rel_id, $rel_type)
{
    if ($rel_type == 'invoice') {
        $rel_name = format_invoice_number($rel_id);
    } elseif ($rel_type == 'estimate') {
        $rel_name = format_estimate_number($rel_id);
    } elseif ($rel_type == 'quote') {
        $rel_name = format_quote_number($rel_id);
    }

    return $rel_name;
}

/**
 * Tour relation link
 * @param  mixed $rel_id   relation id
 * @param  string $rel_type relation type
 * @return string
 */
function tour_rel_link($rel_id, $rel_type)
{
    $link = '#';
    if ($rel_type == 'customer') {
        $link = admin_url('booking/clients/client/' . $rel_id);
    } elseif ($rel_type == 'invoice') {
        $link = admin_url('booking/invoices/list_invoices/' . $rel_id);
    } elseif ($rel_type == 'booking') {
        $link = admin_url('booking/bookings/view/' . $rel_id);
    } elseif ($rel_type == 'estimate') {
        $link = admin_url('estimates/list_estimates/' . $rel_id);
    } elseif ($rel_type == 'contract') {
        $link = admin_url('contracts/contract/' . $rel_id);
    } elseif ($rel_type == 'ticket') {
        $link = admin_url('tickets/ticket/' . $rel_id);
    } elseif ($rel_type == 'expense') {
        $link = admin_url('expenses/list_expenses/' . $rel_id);
    } elseif ($rel_type == 'lead') {
        $link = admin_url('booking/leads/index/' . $rel_id);
    } elseif ($rel_type == 'quote') {
        $link = admin_url('booking/quotes/list_quotes/' . $rel_id);
    }

    return $link;
}
/**
 * Prepares tour array gantt data to be used in the gantt chart
 * @param  array $tour tour array
 * @return array
 */
function get_tour_array_gantt_data($tour, $dep_id = null, $defaultEnd = null)
{
    $data = [];

    $data['id']   = $tour['id'];
    $data['desc'] = $tour['name'];

    $data['start'] = strftime('%Y-%m-%d', strtotime($tour['startdate']));

    if ($tour['duedate']) {
        $data['end'] = strftime('%Y-%m-%d', strtotime($tour['duedate']));
    } else {
        $data['end'] = $defaultEnd;
    }

    $data['desc']  = $tour['name'] . ' - ' . _l('tour_total_logged_time') . ' ' . seconds_to_time_format($tour['total_logged_time']);
    $data['label'] = $tour['name'];
    if ($tour['duedate'] && date('Y-m-d') > $tour['duedate'] && $tour['status'] != Tours_model::STATUS_COMPLETE) {
        $data['custom_class'] = 'ganttRed';
    } elseif ($tour['status'] == Tours_model::STATUS_COMPLETE) {
        $data['custom_class'] = 'ganttGreen';
    }

    $data['name']    = $tour['name'];
    $data['tour_id'] = $tour['id'];
    $data['progress'] = 0;

    //for tour in single booking gantt
    if ($dep_id) {
        $data['dependencies'] = $dep_id;
    }

    if (!staff_can('edit', 'tours') || is_client_logged_in()) {
        if (isset($data['custom_class'])) {
            $data['custom_class'] .= ' noDrag';
        } else {
            $data['custom_class'] = 'noDrag';
        }
    }

    return $data;
}
/**
 * Common function used to select tour relation name
 * @return string
 */
function tours_rel_name_select_query()
{
    return '(CASE rel_type
        WHEN "contract" THEN (SELECT subject FROM ' . db_prefix() . 'contracts WHERE ' . db_prefix() . 'contracts.id = ' . db_prefix() . 'tours.rel_id)
        WHEN "estimate" THEN (SELECT id FROM ' . db_prefix() . 'estimates WHERE ' . db_prefix() . 'estimates.id = ' . db_prefix() . 'tours.rel_id)
        WHEN "quote" THEN (SELECT id FROM ' . db_prefix() . 'quotes WHERE ' . db_prefix() . 'quotes.id = ' . db_prefix() . 'tours.rel_id)
        WHEN "invoice" THEN (SELECT id FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'tours.rel_id)
        WHEN "ticket" THEN (SELECT CONCAT(CONCAT("#",' . db_prefix() . 'tickets.ticketid), " - ", ' . db_prefix() . 'tickets.subject) FROM ' . db_prefix() . 'tickets WHERE ' . db_prefix() . 'tickets.ticketid=' . db_prefix() . 'tours.rel_id)
        WHEN "lead" THEN (SELECT CASE ' . db_prefix() . 'leads.email WHEN "" THEN ' . db_prefix() . 'leads.name ELSE CONCAT(' . db_prefix() . 'leads.name, " - ", ' . db_prefix() . 'leads.email) END FROM ' . db_prefix() . 'leads WHERE ' . db_prefix() . 'leads.id=' . db_prefix() . 'tours.rel_id)
        WHEN "customer" THEN (SELECT CASE company WHEN "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'contacts WHERE userid = ' . db_prefix() . 'clients.userid and is_primary = 1) ELSE company END FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.userid=' . db_prefix() . 'tours.rel_id)
        WHEN "booking" THEN (SELECT CONCAT(CONCAT(CONCAT("#",' . db_prefix() . 'bookings.id)," - ",' . db_prefix() . 'bookings.name), " - ", (SELECT CASE company WHEN "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'contacts WHERE userid = ' . db_prefix() . 'clients.userid and is_primary = 1) ELSE company END FROM ' . db_prefix() . 'clients WHERE userid=' . db_prefix() . 'bookings.clientid)) FROM ' . db_prefix() . 'bookings WHERE ' . db_prefix() . 'bookings.id=' . db_prefix() . 'tours.rel_id)
        WHEN "expense" THEN (SELECT CASE expense_name WHEN "" THEN ' . db_prefix() . 'expenses_categories.name ELSE
         CONCAT(' . db_prefix() . 'expenses_categories.name, \' (\',' . db_prefix() . 'expenses.expense_name,\')\') END FROM ' . db_prefix() . 'expenses JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category WHERE ' . db_prefix() . 'expenses.id=' . db_prefix() . 'tours.rel_id)
        ELSE NULL
        END)';
}


/**
 * Tours html table used all over the application for relation tours
 * This table is not used for the main tours table
 * @param  array  $table_attributes
 * @return string
 */
function init_relation_tours_table($table_attributes = [])
{
    $CI         = &get_instance();
    $CI->load->model("tours_model");
    $table_data = [
        _l('the_number_sign'),
        [
            'name'     => _l('tours_dt_name'),
            'th_attrs' => [
                'style' => 'width:200px',
            ],
        ],
        _l('tour_status'),
        [
            'name'     => _l('tours_dt_datestart'),
            'th_attrs' => [
                'style' => 'width:75px',
            ],
        ],
        [
            'name'     => _l('tour_duedate'),
            'th_attrs' => [
                'style' => 'width:75px',
                'class' => 'duedate',
            ],
        ],
        [
            'name'     => _l('tour_assigned'),
            'th_attrs' => [
                'style' => 'width:75px',
            ],
        ],
        _l('tags'),
        _l('tours_list_priority'),
    ];

    array_unshift($table_data, [
        'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="rel-tours"><label></label></div>',
        'th_attrs' => ['class' => ($table_attributes['data-new-rel-type'] !== 'booking' ? 'not_visible' : '')],
    ]);

    $custom_fields = get_custom_fields('tours', [
        'show_on_table' => 1,
    ]);

    foreach ($custom_fields as $field) {
        array_push($table_data, $field['name']);
    }

    $table_data = hooks()->apply_filters('tours_related_table_columns', $table_data);

    $name = 'rel-tours';
    if ($table_attributes['data-new-rel-type'] == 'lead') {
        $name = 'rel-tours-leads';
    }

    $table      = '';
    $table_name = '.table-' . $name;
    $CI->load->view('admin/tours/tours_filter_by', [
        'view_table_name' => $table_name,
    ]);
    if (has_permission('tours', '', 'create')) {
        $disabled   = '';
        $table_name = addslashes($table_name);
        if ($table_attributes['data-new-rel-type'] == 'customer' && is_numeric($table_attributes['data-new-rel-id'])) {
            if (total_rows(db_prefix() . 'clients', [
                'active' => 0,
                'userid' => $table_attributes['data-new-rel-id'],
            ]) > 0) {
                $disabled = ' disabled';
            }
        }
        // bookings have button on top
        if ($table_attributes['data-new-rel-type'] != 'booking') {
            echo "<a href='#' class='btn btn-info pull-left mbot25 mright5 new-tour-relation" . $disabled . "' onclick=\"new_tour_from_relation('$table_name'); return false;\" data-rel-id='" . $table_attributes['data-new-rel-id'] . "' data-rel-type='" . $table_attributes['data-new-rel-type'] . "'>" . _l('new_tour') . '</a>';
        }
    }

    if ($table_attributes['data-new-rel-type'] == 'booking') {
        echo "<a href='" . admin_url('tours/detailed_overview?booking_id=' . $table_attributes['data-new-rel-id']) . "' class='btn btn-success pull-right mbot25'>" . _l('detailed_overview') . '</a>';
        echo "<a href='" . admin_url('tours/list_tours?booking_id=' . $table_attributes['data-new-rel-id'] . '&kanban=true') . "' class='btn btn-default pull-right mbot25 mright5 hidden-xs'>" . _l('view_kanban') . '</a>';
        echo '<div class="clearfix"></div>';
        echo $CI->load->view('admin/tours/_bulk_actions', ['table' => '.table-rel-tours'], true);
        echo $CI->load->view('admin/tours/_summary', ['rel_id' => $table_attributes['data-new-rel-id'], 'rel_type' => 'booking', 'table' => $table_name], true);
        echo '<a href="#" data-toggle="modal" data-target="#tours_bulk_actions" class="hide bulk-actions-btn table-btn" data-table=".table-rel-tours">' . _l('bulk_actions') . '</a>';
    } elseif ($table_attributes['data-new-rel-type'] == 'customer') {
        echo '<div class="clearfix"></div>';
        echo '<div id="tours_related_filter">';
        echo '<p class="bold">' . _l('tour_related_to') . ': </p>';

        echo '<div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" checked value="customer" disabled id="ts_rel_to_customer" name="tours_related_to[]">
        <label for="ts_rel_to_customer">' . _l('client') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="booking" id="ts_rel_to_booking" name="tours_related_to[]">
        <label for="ts_rel_to_booking">' . _l('bookings') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="invoice" id="ts_rel_to_invoice" name="tours_related_to[]">
        <label for="ts_rel_to_invoice">' . _l('invoices') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="estimate" id="ts_rel_to_estimate" name="tours_related_to[]">
        <label for="ts_rel_to_estimate">' . _l('estimates') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="contract" id="ts_rel_to_contract" name="tours_related_to[]">
        <label for="ts_rel_to_contract">' . _l('contracts') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="ticket" id="ts_rel_to_ticket" name="tours_related_to[]">
        <label for="ts_rel_to_ticket">' . _l('tickets') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="expense" id="ts_rel_to_expense" name="tours_related_to[]">
        <label for="ts_rel_to_expense">' . _l('expenses') . '</label>
        </div>

        <div class="checkbox checkbox-inline mbot25">
        <input type="checkbox" value="quote" id="ts_rel_to_quote" name="tours_related_to[]">
        <label for="ts_rel_to_quote">' . _l('quotes') . '</label>
        </div>';

        echo '</div>';
    }
    echo "<div class='clearfix'></div>";

    // If new column is added on tours relations table this will not work fine
    // In this case we need to add new identifier eq tour-relation
    $table_attributes['data-last-order-identifier'] = 'tours';
    $table_attributes['data-default-order']         = get_table_last_order('tours');

    $table .= render_datatable($table_data, $name, [], $table_attributes);

    return $table;
}

/**
 * Return tours summary formated data
 * @param  string $where additional where to perform
 * @return array
 */
function tours_summary_data($rel_id = null, $rel_type = null)
{
    $CI            = &get_instance();
    $tours_summary = [];
    $statuses      = $CI->tours_model->get_statuses();
    foreach ($statuses as $status) {
        $tours_where = 'status = ' . $CI->db->escape_str($status['id']);
        if (!has_permission('tours', '', 'view')) {
            $tours_where .= ' ' . get_tours_where_string();
        }
        $tours_my_where = 'id IN(SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . get_staff_user_id() . ') AND status=' . $CI->db->escape_str($status['id']);
        if ($rel_id && $rel_type) {
            $tours_where .= ' AND rel_id=' . $CI->db->escape_str($rel_id) . ' AND rel_type="' . $CI->db->escape_str($rel_type) . '"';
            $tours_my_where .= ' AND rel_id=' . $CI->db->escape_str($rel_id) . ' AND rel_type="' . $CI->db->escape_str($rel_type) . '"';
        } else {
            $sqlBookingToursWhere = ' AND CASE
            WHEN rel_type="booking" AND rel_id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_settings WHERE booking_id=rel_id AND name="hide_tours_on_main_tours_table" AND value=1)
            THEN rel_type != "booking"
            ELSE 1=1
            END';
            $tours_where .= $sqlBookingToursWhere;
            $tours_my_where .= $sqlBookingToursWhere;
        }

        $summary                   = [];
        $summary['total_tours']    = total_rows(db_prefix() . 'tours', $tours_where);
        $summary['total_my_tours'] = total_rows(db_prefix() . 'tours', $tours_my_where);
        $summary['color']          = $status['color'];
        $summary['name']           = $status['name'];
        $summary['status_id']      = $status['id'];
        $tours_summary[]           = $summary;
    }

    return $tours_summary;
}


function get_sql_calc_tour_logged_time($tour_id)
{
    /**
     * Do not remove where tour_id=
     * Used in tours detailed_overview to overwrite the tourid
     */
    return 'SELECT SUM(CASE
            WHEN end_time is NULL THEN ' . time() . '-start_time
            ELSE end_time-start_time
            END) as total_logged_time FROM ' . db_prefix() . 'tourstimers WHERE tour_id =' . get_instance()->db->escape_str($tour_id);
}

function get_sql_select_tour_assignees_ids()
{
    return '(SELECT GROUP_CONCAT(staffid SEPARATOR ",") FROM ' . db_prefix() . 'tour_assigned WHERE tourid=' . db_prefix() . 'tours.id ORDER BY ' . db_prefix() . 'tour_assigned.staffid)';
}

function get_sql_select_tour_asignees_full_names()
{
    return '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM ' . db_prefix() . 'tour_assigned JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'tour_assigned.staffid WHERE tourid=' . db_prefix() . 'tours.id ORDER BY ' . db_prefix() . 'tour_assigned.staffid)';
}

function get_sql_select_tour_total_checklist_items()
{
    return '(SELECT COUNT(id) FROM ' . db_prefix() . 'tour_checklist_items WHERE tourid=' . db_prefix() . 'tours.id) as total_checklist_items';
}

function get_sql_select_tour_total_finished_checklist_items()
{
    return '(SELECT COUNT(id) FROM ' . db_prefix() . 'tour_checklist_items WHERE tourid=' . db_prefix() . 'tours.id AND finished=1) as total_finished_checklist_items';
}

/**
 * This text is used in WHERE statements for tours if the staff member don't have permission for tours VIEW
 * This query will shown only tours that are created from current user, public tours or where this user is added is tour follower.
 * Other statement will be included the tours to be visible for this user only if Show All Tours For Booking Members is set to YES
 * @return string
 */
function get_tours_where_string($table = true)
{
    $_tours_where = '(' . db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid = ' . get_staff_user_id() . ') OR ' . db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_followers WHERE staffid = ' . get_staff_user_id() . ') OR (addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0)';
    if (get_option('show_all_tours_for_booking_member') == 1) {
        $_tours_where .= ' OR (' . db_prefix() . 'tours.rel_type="booking" AND ' . db_prefix() . 'tours.rel_id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . '))';
    }
    $_tours_where .= ' OR is_public = 1)';
    if ($table == true) {
        $_tours_where = 'AND ' . $_tours_where;
    }

    return $_tours_where;
}
