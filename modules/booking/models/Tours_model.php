<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tours_model extends App_Model
{
    const STATUS_NOT_STARTED = 1;

    const STATUS_AWAITING_FEEDBACK = 2;

    const STATUS_TESTING = 3;

    const STATUS_IN_PROGRESS = 4;

    const STATUS_COMPLETE = 5;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('staff_model');
    }

    // Not used?
    public function get_user_tours_assigned()
    {
        $this->db->where('id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid = ' . get_staff_user_id() . ')');
        $this->db->where('status !=', 5);
        $this->db->order_by('duedate', 'asc');

        return $this->db->get(db_prefix() . 'tours')->result_array();
    }

    public function get_statuses()
    {
        $statuses = hooks()->apply_filters('before_get_tour_statuses', [
            [
                'id'             => self::STATUS_NOT_STARTED,
                'color'          => '#989898',
                'name'           => _l('tour_status_1'),
                'order'          => 1,
                'filter_default' => true,
                ],
             [
                'id'             => self::STATUS_IN_PROGRESS,
                'color'          => '#03A9F4',
                'name'           => _l('tour_status_4'),
                'order'          => 2,
                'filter_default' => true,
                ],
             [
                'id'             => self::STATUS_TESTING,
                'color'          => '#2d2d2d',
                'name'           => _l('tour_status_3'),
                'order'          => 3,
                'filter_default' => true,
                ],
              [
                'id'             => self::STATUS_AWAITING_FEEDBACK,
                'color'          => '#adca65',
                'name'           => _l('tour_status_2'),
                'order'          => 4,
                'filter_default' => true,
                ],
            [
                'id'             => self::STATUS_COMPLETE,
                'color'          => '#84c529',
                'name'           => _l('tour_status_5'),
                'order'          => 100,
                'filter_default' => false,
                ],
            ]);

        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $statuses;
    }

    /**
     * Get tour by id
     * @param  mixed $id tour id
     * @return object
     */
    public function get($id, $where = [])
    {
        $is_admin = is_admin();
        $this->db->where('id', $id);
        $this->db->where($where);
        $tour = $this->db->get(db_prefix() . 'tours')->row();
        if ($tour) {
            $tour->comments      = $this->get_tour_comments($id);
            $tour->assignees     = $this->get_tour_assignees($id);
            $tour->assignees_ids = [];

            foreach ($tour->assignees as $follower) {
                array_push($tour->assignees_ids, $follower['assigneeid']);
            }

            $tour->followers     = $this->get_tour_followers($id);
            $tour->followers_ids = [];
            foreach ($tour->followers as $follower) {
                array_push($tour->followers_ids, $follower['followerid']);
            }

            $tour->attachments     = $this->get_tour_attachments($id);
            $tour->timesheets      = $this->get_timesheeets($id);
            $tour->checklist_items = $this->get_checklist_items($id);

            if (is_staff_logged_in()) {
                $tour->current_user_is_assigned = $this->is_tour_assignee(get_staff_user_id(), $id);
                $tour->current_user_is_creator  = $this->is_tour_creator(get_staff_user_id(), $id);
            }

            $tour->milestone_name = '';

            if ($tour->rel_type == 'booking') {
                $tour->booking_data = $this->bookings_model->get($tour->rel_id);
                if ($tour->milestone != 0) {
                    $milestone = $this->get_milestone($tour->milestone);
                    if ($milestone) {
                        $tour->milestone_name = $milestone->name;
                    }
                }
            }
        }

        return hooks()->apply_filters('get_tour', $tour);
    }

    public function get_milestone($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'milestones')->row();
    }

    public function do_kanban_query($status, $search = '', $page = 1, $count = false, $where = [])
    {
        $tours_where = '';
        if (!has_permission('tours', '', 'view')) {
            $tours_where = get_tours_where_string(false);
        }

        $this->db->select(tours_rel_name_select_query() . ' as rel_name,rel_type,rel_id,id,priority,name,duedate,startdate,status,' . get_sql_select_tour_total_checklist_items() . ',' . get_sql_select_tour_total_finished_checklist_items() . ',(SELECT COUNT(id) FROM ' . db_prefix() . 'tour_comments WHERE tourid=' . db_prefix() . 'tours.id) as total_comments,(SELECT COUNT(id) FROM ' . db_prefix() . 'files WHERE rel_id=' . db_prefix() . 'tours.id AND rel_type="tour") as total_files,' . get_sql_select_tour_asignees_full_names() . ' as assignees' . ',' . get_sql_select_tour_assignees_ids() . ' as assignees_ids,(SELECT staffid FROM ' . db_prefix() . 'tour_assigned WHERE tourid=' . db_prefix() . 'tours.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned, (SELECT CASE WHEN addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0 THEN 1 ELSE 0 END) as current_user_is_creator');

        $this->db->from(db_prefix() . 'tours');
        $this->db->where('status', $status);

        $this->db->where($where);

        if ($tours_where != '') {
            $this->db->where($tours_where);
        }

        if ($search != '') {
            if (!startsWith($search, '#')) {
                $this->db->where('(' . db_prefix() . 'tours.name LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\'  OR ' . db_prefix() . 'tours.description LIKE "%' . $this->db->escape_like_str($search) . '%" ESCAPE \'!\')');
            } else {
                $this->db->where(db_prefix() . 'tours.id IN
                (SELECT rel_id FROM ' . db_prefix() . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . 'tags WHERE name="' . $this->db->escape_str(strafter($search, '#')) . '")
                AND ' . db_prefix() . 'taggables.rel_type=\'tour\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
        }

        $this->db->order_by('kanban_order', 'asc');

        if ($count == false) {
            if ($page > 1) {
                $page--;
                $position = ($page * get_option('tours_kanban_limit'));
                $this->db->limit(get_option('tours_kanban_limit'), $position);
            } else {
                $this->db->limit(get_option('tours_kanban_limit'));
            }
        }

        if ($count == false) {
            return $this->db->get()->result_array();
        }

        return $this->db->count_all_results();
    }

    public function update_order($data)
    {
        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . 'tours', [
                'kanban_order' => $order[1],
            ]);
        }
    }

    public function get_distinct_tours_years($get_from)
    {
        return $this->db->query('SELECT DISTINCT(YEAR(' . $this->db->escape_str($get_from) . ')) as year FROM ' . db_prefix() . 'tours WHERE ' . $this->db->escape_str($get_from) . ' IS NOT NULL ORDER BY year DESC')->result_array();
    }

    public function is_tour_billed($id)
    {
        return (total_rows(db_prefix() . 'tours', [
            'id'     => $id,
            'billed' => 1,
        ]) > 0 ? true : false);
    }

    public function copy($data, $overwrites = [])
    {
        $tour           = $this->get($data['copy_from']);
        $fields_tours   = $this->db->list_fields(db_prefix() . 'tours');
        $_new_tour_data = [];

        foreach ($fields_tours as $field) {
            if (isset($tour->$field)) {
                $_new_tour_data[$field] = $tour->$field;
            }
        }

        unset($_new_tour_data['id']);

        if (isset($data['copy_tour_status']) && is_numeric($data['copy_tour_status'])) {
            $_new_tour_data['status'] = $data['copy_tour_status'];
        } else {
            // fallback in case no status is provided
            $_new_tour_data['status'] = 1;
        }

        $_new_tour_data['dateadded']         = date('Y-m-d H:i:s');
        $_new_tour_data['startdate']         = date('Y-m-d');
        $_new_tour_data['deadline_notified'] = 0;
        $_new_tour_data['billed']            = 0;
        $_new_tour_data['invoice_id']        = 0;
        $_new_tour_data['total_cycles']      = 0;
        $_new_tour_data['is_recurring_from'] = null;

        if (!empty($tour->duedate)) {
            $dStart                    = new DateTime($tour->startdate);
            $dEnd                      = new DateTime($tour->duedate);
            $dDiff                     = $dStart->diff($dEnd);
            $_new_tour_data['duedate'] = date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY'))));
        }

        // Overwrite data options
        if (count($overwrites) > 0) {
            foreach ($overwrites as $key => $val) {
                $_new_tour_data[$key] = $val;
            }
        }

        unset($_new_tour_data['datefinished']);

        $_new_tour_data = hooks()->apply_filters('before_add_tour', $_new_tour_data);

        $this->db->insert(db_prefix() . 'tours', $_new_tour_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $tags = get_tags_in($data['copy_from'], 'tour');
            handle_tags_save($tags, $insert_id, 'tour');

            if (isset($data['copy_tour_assignees']) && $data['copy_tour_assignees'] == 'true') {
                $this->copy_tour_assignees($data['copy_from'], $insert_id);
            }

            if (isset($data['copy_tour_followers']) && $data['copy_tour_followers'] == 'true') {
                $this->copy_tour_followers($data['copy_from'], $insert_id);
            }

            if (isset($data['copy_tour_checklist_items']) && $data['copy_tour_checklist_items'] == 'true') {
                $this->copy_tour_checklist_items($data['copy_from'], $insert_id);
            }

            if (isset($data['copy_tour_attachments']) && $data['copy_tour_attachments'] == 'true') {
                $attachments = $this->get_tour_attachments($data['copy_from']);

                if (is_dir(get_upload_path_by_type('tour') . $data['copy_from'])) {
                    xcopy(get_upload_path_by_type('tour') . $data['copy_from'], get_upload_path_by_type('tour') . $insert_id);
                }

                foreach ($attachments as $at) {
                    $_at      = [];
                    $_at[]    = $at;
                    $external = false;
                    if (!empty($at['external'])) {
                        $external       = $at['external'];
                        $_at[0]['name'] = $at['file_name'];
                        $_at[0]['link'] = $at['external_link'];
                        if (!empty($at['thumbnail_link'])) {
                            $_at[0]['thumbnailLink'] = $at['thumbnail_link'];
                        }
                    }
                    $this->add_attachment_to_database($insert_id, $_at, $external, false);
                }
            }

            $this->copy_tour_custom_fields($data['copy_from'], $insert_id);

            hooks()->do_action('after_add_tour', $insert_id);

            return $insert_id;
        }

        return false;
    }

    public function copy_tour_followers($from_tour, $to_tour)
    {
        $followers = $this->get_tour_followers($from_tour);
        foreach ($followers as $follower) {
            $this->db->insert(db_prefix() . 'tour_followers', [
                'tourid'  => $to_tour,
                'staffid' => $follower['followerid'],
            ]);
        }
    }

    public function copy_tour_assignees($from_tour, $to_tour)
    {
        $assignees = $this->get_tour_assignees($from_tour);
        foreach ($assignees as $assignee) {
            $this->db->insert(db_prefix() . 'tour_assigned', [
                'tourid'        => $to_tour,
                'staffid'       => $assignee['assigneeid'],
                'assigned_from' => get_staff_user_id(),
            ]);
        }
    }

    public function copy_tour_checklist_items($from_tour, $to_tour)
    {
        $checklists = $this->get_checklist_items($from_tour);
        foreach ($checklists as $list) {
            $this->db->insert(db_prefix() . 'tour_checklist_items', [
                'tourid'      => $to_tour,
                'finished'    => 0,
                'description' => $list['description'],
                'dateadded'   => date('Y-m-d H:i:s'),
                'addedfrom'   => $list['addedfrom'],
                'list_order'  => $list['list_order'],
            ]);
        }
    }

    public function copy_tour_custom_fields($from_tour, $to_tour)
    {
        $custom_fields = get_custom_fields('tours');
        foreach ($custom_fields as $field) {
            $value = get_custom_field_value($from_tour, $field['id'], 'tours', false);
            if ($value != '') {
                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $to_tour,
                    'fieldid' => $field['id'],
                    'fieldto' => 'tours',
                    'value'   => $value,
                ]);
            }
        }
    }

    public function get_billable_tours($customer_id = false, $booking_id = '')
    {
        $has_permission_view = has_permission('tours', '', 'view');
        $noPermissionsQuery  = get_tours_where_string(false);

        $this->db->where('billable', 1);
        $this->db->where('billed', 0);

        if ($booking_id == '') {
            $this->db->where('rel_type != "booking"');
        } else {
            $this->db->where('rel_type', 'booking');
            $this->db->where('rel_id', $booking_id);
        }

        if ($customer_id != false && $booking_id == '') {
            $this->db->where(
                '
                (
                (rel_id IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE clientid=' . $this->db->escape_str($customer_id) . ') AND rel_type="invoice")
                OR
                (rel_id IN (SELECT id FROM ' . db_prefix() . 'estimates WHERE clientid=' . $this->db->escape_str($customer_id) . ') AND rel_type="estimate")
                OR
                (rel_id IN (SELECT id FROM ' . db_prefix() . 'contracts WHERE client=' . $this->db->escape_str($customer_id) . ') AND rel_type="contract")
                OR
                ( rel_id IN (SELECT ticketid FROM ' . db_prefix() . 'tickets WHERE userid=' . $this->db->escape_str($customer_id) . ') AND rel_type="ticket")
                OR
                (rel_id IN (SELECT id FROM ' . db_prefix() . 'expenses WHERE clientid=' . $this->db->escape_str($customer_id) . ') AND rel_type="expense")
                OR
                (rel_id IN (SELECT id FROM ' . db_prefix() . 'quotes WHERE rel_id=' . $this->db->escape_str($customer_id) . ' AND rel_type="customer") AND rel_type="quote")
                OR
                (rel_id IN (SELECT userid FROM ' . db_prefix() . 'clients WHERE userid=' . $this->db->escape_str($customer_id) . ') AND rel_type="customer")
                )'
                );
        }

        if (!$has_permission_view) {
            $this->db->where($noPermissionsQuery);
        }

        $tours = $this->db->get(db_prefix() . 'tours')->result_array();

        $i = 0;
        foreach ($tours as $tour) {
            $tour_rel_data         = get_relation_data($tour['rel_type'], $tour['rel_id']);
            $tour_rel_value        = get_relation_values($tour_rel_data, $tour['rel_type']);
            $tours[$i]['rel_name'] = $tour_rel_value['name'];
            if (total_rows(db_prefix() . 'tourstimers', [
                'tour_id' => $tour['id'],
                'end_time' => null,
            ]) > 0) {
                $tours[$i]['started_timers'] = true;
            } else {
                $tours[$i]['started_timers'] = false;
            }
            $i++;
        }

        return $tours;
    }

    public function get_billable_amount($tourId)
    {
        $data = $this->get_billable_tour_data($tourId);

        return app_format_number($data->total_hours * $data->hourly_rate);
    }

    public function get_billable_tour_data($tour_id)
    {
        $this->db->where('id', $tour_id);
        $data = $this->db->get(db_prefix() . 'tours')->row();
        if ($data->rel_type == 'booking') {
            $this->db->select('billing_type,booking_rate_per_hour,name');
            $this->db->where('id', $data->rel_id);
            $booking      = $this->db->get(db_prefix() . 'bookings')->row();
            $billing_type = get_booking_billing_type($data->rel_id);

            if ($booking->billing_type == 2) {
                $data->hourly_rate = $booking->booking_rate_per_hour;
            }

            $data->name = $booking->name . ' - ' . $data->name;
        }
        $total_seconds       = $this->calc_tour_total_time($tour_id);
        $data->total_hours   = sec2qty($total_seconds);
        $data->total_seconds = $total_seconds;

        return $data;
    }

    public function get_tours_by_staff_id($id, $where = [])
    {
        $this->db->where($where);
        $this->db->where('(id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . $this->db->escape_str($id) . '))');

        return $this->db->get(db_prefix() . 'tours')->result_array();
    }

    /**
     * Add new staff tour
     * @param array $data tour $_POST data
     * @return mixed
     */
    public function add($data, $clientRequest = false)
    {
        $ticket_to_tour = false;

        if (isset($data['ticket_to_tour'])) {
            $ticket_to_tour = true;
            unset($data['ticket_to_tour']);
        }

        $data['startdate']             = to_sql_date($data['startdate']);
        $data['duedate']               = to_sql_date($data['duedate']);
        $data['dateadded']             = date('Y-m-d H:i:s');
        $data['addedfrom']             = $clientRequest == false ? get_staff_user_id() : get_contact_user_id();
        $data['is_added_from_contact'] = $clientRequest == false ? 0 : 1;

        $checklistItems = [];
        if (isset($data['checklist_items']) && count($data['checklist_items']) > 0) {
            $checklistItems = $data['checklist_items'];
            unset($data['checklist_items']);
        }

        if ($clientRequest == false) {
            $defaultStatus = get_option('default_tour_status');
            if ($defaultStatus == 'auto') {
                if (date('Y-m-d') >= $data['startdate']) {
                    $data['status'] = 4;
                } else {
                    $data['status'] = 1;
                }
            } else {
                $data['status'] = $defaultStatus;
            }
        } else {
            // When client create tour the default status is NOT STARTED
            // After staff will get the tour will change the status
            $data['status'] = 1;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['is_public'])) {
            $data['is_public'] = 1;
        } else {
            $data['is_public'] = 0;
        }

        if (isset($data['repeat_every']) && $data['repeat_every'] != '') {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every']     = $data['repeat_every_custom'];
                $data['recurring_type']   = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                $_temp                    = explode('-', $data['repeat_every']);
                $data['recurring_type']   = $_temp[1];
                $data['repeat_every']     = $_temp[0];
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring']    = 0;
            $data['repeat_every'] = null;
        }

        if (isset($data['repeat_type_custom']) && isset($data['repeat_every_custom'])) {
            unset($data['repeat_type_custom']);
            unset($data['repeat_every_custom']);
        }

        if (is_client_logged_in() || $clientRequest) {
            $data['visible_to_client'] = 1;
        } else {
            if (isset($data['visible_to_client'])) {
                $data['visible_to_client'] = 1;
            } else {
                $data['visible_to_client'] = 0;
            }
        }

        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }

        if ((!isset($data['milestone']) || $data['milestone'] == '') || (isset($data['milestone']) && $data['milestone'] == '')) {
            $data['milestone'] = 0;
        } else {
            if ($data['rel_type'] != 'booking') {
                $data['milestone'] = 0;
            }
        }
        if (empty($data['rel_type'])) {
            unset($data['rel_type']);
            unset($data['rel_id']);
        } else {
            if (empty($data['rel_id'])) {
                unset($data['rel_type']);
                unset($data['rel_id']);
            }
        }

        $data = hooks()->apply_filters('before_add_tour', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        $this->db->insert(db_prefix() . 'tours', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            foreach ($checklistItems as $key => $chkID) {
                if ($chkID != '') {
                    $itemTemplate = $this->get_checklist_template($chkID);
                    $this->db->insert(db_prefix() . 'tour_checklist_items', [
                        'description' => $itemTemplate->description,
                        'tourid'      => $insert_id,
                        'dateadded'   => date('Y-m-d H:i:s'),
                        'addedfrom'   => get_staff_user_id(),
                        'list_order'  => $key,
                        ]);
                }
            }
            handle_tags_save($tags, $insert_id, 'tour');
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            if (isset($data['rel_type']) && $data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['rel_id'], 'not_activity_new_tour_created', false, serialize([
                    '<a href="' . admin_url('tours/view/' . $insert_id) . '" onclick="init_tour_modal(' . $insert_id . ');return false;">' . $data['name'] . '</a>',
                    ]));
            }

            if ($clientRequest == false) {
                $new_tour_auto_assign_creator = (get_option('new_tour_auto_assign_current_member') == '1' ? true : false);

                if (isset($data['rel_type']) && $data['rel_type'] == 'booking' && !$this->bookings_model->is_member($data['rel_id'])) {
                    $new_tour_auto_assign_creator = false;
                }
                if ($new_tour_auto_assign_creator == true) {
                    $this->db->insert(db_prefix() . 'tour_assigned', [
                        'tourid'        => $insert_id,
                        'staffid'       => get_staff_user_id(),
                        'assigned_from' => get_staff_user_id(),
                    ]);
                }
                if (get_option('new_tour_auto_follower_current_member') == '1') {
                    $this->db->insert(db_prefix() . 'tour_followers', [
                        'tourid'  => $insert_id,
                        'staffid' => get_staff_user_id(),
                    ]);
                }

                if ($ticket_to_tour && isset($data['rel_type']) && $data['rel_type'] == 'ticket') {
                    $ticket_attachments = $this->db->query('SELECT * FROM ' . db_prefix() . 'ticket_attachments WHERE ticketid=' . $this->db->escape_str($data['rel_id']) . ' OR (ticketid=' . $this->db->escape_str($data['rel_id']) . ' AND replyid IN (SELECT id FROM ' . db_prefix() . 'ticket_replies WHERE ticketid=' . $this->db->escape_str($data['rel_id']) . '))')->result_array();

                    if (count($ticket_attachments) > 0) {
                        $tour_path = get_upload_path_by_type('tour') . $insert_id . '/';
                        _maybe_create_upload_path($tour_path);

                        foreach ($ticket_attachments as $ticket_attachment) {
                            $path = get_upload_path_by_type('ticket') . $data['rel_id'] . '/' . $ticket_attachment['file_name'];
                            if (file_exists($path)) {
                                $f = fopen($path, FOPEN_READ);
                                if ($f) {
                                    $filename = unique_filename($tour_path, $ticket_attachment['file_name']);
                                    $fpt      = fopen($tour_path . $filename, 'w');
                                    if ($fpt && fwrite($fpt, stream_get_contents($f))) {
                                        $this->db->insert(db_prefix() . 'files', [
                                                            'rel_id'         => $insert_id,
                                                            'rel_type'       => 'tour',
                                                            'file_name'      => $filename,
                                                            'filetype'       => $ticket_attachment['filetype'],
                                                            'staffid'        => get_staff_user_id(),
                                                            'dateadded'      => date('Y-m-d H:i:s'),
                                                            'attachment_key' => app_generate_hash(),
                                                        ]);
                                    }
                                    if ($fpt) {
                                        fclose($fpt);
                                    }
                                    fclose($f);
                                }
                            }
                        }
                    }
                }
            }

            log_activity('New Tour Added [ID:' . $insert_id . ', Name: ' . $data['name'] . ']');
            hooks()->do_action('after_add_tour', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update tour data
     * @param  array $data tour data $_POST
     * @param  mixed $id   tour id
     * @return boolean
     */
    public function update($data, $id, $clientRequest = false)
    {
        $affectedRows      = 0;
        $data['startdate'] = to_sql_date($data['startdate']);
        $data['duedate']   = to_sql_date($data['duedate']);

        $checklistItems = [];
        if (isset($data['checklist_items']) && count($data['checklist_items']) > 0) {
            $checklistItems = $data['checklist_items'];
            unset($data['checklist_items']);
        }

        if (isset($data['datefinished'])) {
            $data['datefinished'] = to_sql_date($data['datefinished'], true);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if ($clientRequest == false) {
            $data['cycles'] = !isset($data['cycles']) ? 0 : $data['cycles'];

            $original_tour = $this->get($id);

            // Recurring tour set to NO, Cancelled
            if ($original_tour->repeat_every != '' && $data['repeat_every'] == '') {
                $data['cycles']              = 0;
                $data['total_cycles']        = 0;
                $data['last_recurring_date'] = null;
            }

            if ($data['repeat_every'] != '') {
                $data['recurring'] = 1;
                if ($data['repeat_every'] == 'custom') {
                    $data['repeat_every']     = $data['repeat_every_custom'];
                    $data['recurring_type']   = $data['repeat_type_custom'];
                    $data['custom_recurring'] = 1;
                } else {
                    $_temp                    = explode('-', $data['repeat_every']);
                    $data['recurring_type']   = $_temp[1];
                    $data['repeat_every']     = $_temp[0];
                    $data['custom_recurring'] = 0;
                }
            } else {
                $data['recurring'] = 0;
            }

            if (isset($data['repeat_type_custom']) && isset($data['repeat_every_custom'])) {
                unset($data['repeat_type_custom']);
                unset($data['repeat_every_custom']);
            }

            if (isset($data['is_public'])) {
                $data['is_public'] = 1;
            } else {
                $data['is_public'] = 0;
            }
            if (isset($data['billable'])) {
                $data['billable'] = 1;
            } else {
                $data['billable'] = 0;
            }

            if (isset($data['visible_to_client'])) {
                $data['visible_to_client'] = 1;
            } else {
                $data['visible_to_client'] = 0;
            }
        }

        if ((!isset($data['milestone']) || $data['milestone'] == '') || (isset($data['milestone']) && $data['milestone'] == '')) {
            $data['milestone'] = 0;
        } else {
            if ($data['rel_type'] != 'booking') {
                $data['milestone'] = 0;
            }
        }


        if (empty($data['rel_type'])) {
            $data['rel_id']   = null;
            $data['rel_type'] = null;
        } else {
            if (empty($data['rel_id'])) {
                $data['rel_id']   = null;
                $data['rel_type'] = null;
            }
        }

        $data = hooks()->apply_filters('before_update_tour', $data, $id);

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'tour')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }

        foreach ($checklistItems as $key => $chkID) {
            $itemTemplate = $this->get_checklist_template($chkID);
            $this->db->insert(db_prefix() . 'tour_checklist_items', [
                    'description' => $itemTemplate->description,
                    'tourid'      => $id,
                    'dateadded'   => date('Y-m-d H:i:s'),
                    'addedfrom'   => get_staff_user_id(),
                    'list_order'  => $key,
                    ]);
            $affectedRows++;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'tours', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            hooks()->do_action('after_update_tour', $id);
            log_activity('Tour Updated [ID:' . $id . ', Name: ' . $data['name'] . ']');
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function get_checklist_item($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'tour_checklist_items')->row();
    }

    public function get_checklist_items($tourid)
    {
        $this->db->where('tourid', $tourid);
        $this->db->order_by('list_order', 'asc');

        return $this->db->get(db_prefix() . 'tour_checklist_items')->result_array();
    }

    public function add_checklist_template($description)
    {
        $this->db->insert(db_prefix() . 'tours_checklist_templates', [
            'description' => $description,
            ]);

        return $this->db->insert_id();
    }

    public function remove_checklist_item_template($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tours_checklist_templates');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function get_checklist_templates()
    {
        $this->db->order_by('description', 'asc');

        return $this->db->get(db_prefix() . 'tours_checklist_templates')->result_array();
    }

    public function get_checklist_template($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'tours_checklist_templates')->row();
    }

    /**
     * Add tour new blank check list item
     * @param mixed $data $_POST data with taxid
     */
    public function add_checklist_item($data)
    {
        $this->db->insert(db_prefix() . 'tour_checklist_items', [
            'tourid'      => $data['tourid'],
            'description' => $data['description'],
            'dateadded'   => date('Y-m-d H:i:s'),
            'addedfrom'   => get_staff_user_id(),
            'list_order'  => 0,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            hooks()->do_action('tour_checklist_item_created', ['tour_id' => $data['tourid'], 'checklist_id' => $insert_id]);

            return true;
        }

        return false;
    }

    public function delete_checklist_item($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tour_checklist_items');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function update_checklist_order($data)
    {
        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . 'tour_checklist_items', [
                'list_order' => $order[1],
            ]);
        }
    }

    /**
     * Update checklist item
     * @param  mixed $id          check list id
     * @param  mixed $description checklist description
     * @return void
     */
    public function update_checklist_item($id, $description)
    {
        $description = strip_tags($description, '<br>,<br/>');
        if ($description === '') {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'tour_checklist_items');
        } else {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tour_checklist_items', [
                'description' => nl2br($description),
            ]);
        }
    }

    /**
     * Make tour public
     * @param  mixed $tour_id tour id
     * @return boolean
     */
    public function make_public($tour_id)
    {
        $this->db->where('id', $tour_id);
        $this->db->update(db_prefix() . 'tours', [
            'is_public' => 1,
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get tour creator id
     * @param  mixed $tourid tour id
     * @return mixed
     */
    public function get_tour_creator_id($tourid)
    {
        $this->db->select('addedfrom');
        $this->db->where('id', $tourid);

        return $this->db->get(db_prefix() . 'tours')->row()->addedfrom;
    }

    /**
     * Add new tour comment
     * @param array $data comment $_POST data
     * @return boolean
     */
    public function add_tour_comment($data)
    {
        if (is_client_logged_in()) {
            $data['staffid']    = 0;
            $data['contact_id'] = get_contact_user_id();
        } else {
            $data['staffid']    = get_staff_user_id();
            $data['contact_id'] = 0;
        }

        $this->db->insert(db_prefix() . 'tour_comments', [
            'tourid'     => $data['tourid'],
            'content'    => is_client_logged_in() ? _strip_tags($data['content']) : $data['content'],
            'staffid'    => $data['staffid'],
            'contact_id' => $data['contact_id'],
            'dateadded'  => date('Y-m-d H:i:s'),
        ]);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->db->select('rel_type,rel_id,name,visible_to_client');
            $this->db->where('id', $data['tourid']);
            $tour = $this->db->get(db_prefix() . 'tours')->row();

            $description     = 'not_tour_new_comment';
            $additional_data = serialize([
                $tour->name,
            ]);

            if ($tour->rel_type == 'booking') {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_new_tour_comment', $tour->name, $tour->visible_to_client);
            }

            $this->_send_tour_responsible_users_notification($description, $data['tourid'], false, 'tour_new_comment_to_staff', $additional_data, $insert_id);

            $this->db->where('booking_id', $tour->rel_id);
            $this->db->where('name', 'view_tour_comments');
            $booking_settings = $this->db->get(db_prefix() . 'booking_settings')->row();

            if ($booking_settings && $booking_settings->value == 1) {
                $this->_send_customer_contacts_notification($data['tourid'], 'tour_new_comment_to_customer');
            }

            hooks()->do_action('tour_comment_added', ['tour_id' => $data['tourid'], 'comment_id' => $insert_id]);

            return $insert_id;
        }

        return false;
    }

    /**
     * Add tour followers
     * @param array $data followers $_POST data
     * @return boolean
     */
    public function add_tour_followers($data)
    {
        $this->db->insert(db_prefix() . 'tour_followers', [
            'tourid'  => $data['tourid'],
            'staffid' => $data['follower'],
        ]);
        if ($this->db->affected_rows() > 0) {
            $tourName = get_tour_subject_by_id($data['tourid']);

            if (get_staff_user_id() != $data['follower']) {
                $notified = add_notification([
                    'description'     => 'not_tour_added_you_as_follower',
                    'touserid'        => $data['follower'],
                    'link'            => '#tourid=' . $data['tourid'],
                    'additional_data' => serialize([
                        $tourName,
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$data['follower']]);
                }

                $member = $this->staff_model->get($data['follower']);

                send_mail_template('tour_added_as_follower_to_staff', BOOKING_MODULE_NAME, $member->email, $data['follower'], $data['tourid']);
            }

            $description = 'not_tour_added_someone_as_follower';

            $additional_notification_data = serialize([
                get_staff_full_name($data['follower']),
                $tourName,
            ]);

            if ($data['follower'] == get_staff_user_id()) {
                $additional_notification_data = serialize([
                    $tourName,
                ]);
                $description = 'not_tour_added_himself_as_follower';
            }

            $this->_send_tour_responsible_users_notification($description, $data['tourid'], $data['follower'], '', $additional_notification_data);

            return true;
        }

        return false;
    }

    /**
     * Assign tour to staff
     * @param array $data tour assignee $_POST data
     * @return boolean
     */
    public function add_tour_assignees($data, $cronOrIntegration = false, $clientRequest = false)
    {
        $assignData = [
            'tourid'  => $data['tourid'],
            'staffid' => $data['assignee'],
        ];
        if ($cronOrIntegration) {
            $assignData['assigned_from'] = $data['assignee'];
        } elseif ($clientRequest) {
            $assignData['is_assigned_from_contact'] = 1;
            $assignData['assigned_from']            = get_contact_user_id();
        } else {
            $assignData['assigned_from'] = get_staff_user_id();
        }
        $this->db->insert(db_prefix() . 'tour_assigned', $assignData);

        $assigneeId = $this->db->insert_id();

        if ($assigneeId) {
            $this->db->select('name,visible_to_client,rel_id,rel_type');
            $this->db->where('id', $data['tourid']);
            $tour = $this->db->get(db_prefix() . 'tours')->row();

            if (get_staff_user_id() != $data['assignee'] || $clientRequest) {
                $notification_data = [
                    'description' => ($cronOrIntegration == false ? 'not_tour_assigned_to_you' : 'new_tour_assigned_non_user'),
                    'touserid'    => $data['assignee'],
                    'link'        => '#tourid=' . $data['tourid'],
                ];

                $notification_data['additional_data'] = serialize([
                    $tour->name,
                ]);

                if ($cronOrIntegration) {
                    $notification_data['fromcompany'] = 1;
                }

                if ($clientRequest) {
                    $notification_data['fromclientid'] = get_contact_user_id();
                }

                if (add_notification($notification_data)) {
                    pusher_trigger_notification([$data['assignee']]);
                }

                $member = $this->staff_model->get($data['assignee']);

                send_mail_template('tour_assigned_to_staff', BOOKING_MODULE_NAME, $member->email, $data['assignee'], $data['tourid']);
            }

            $description                  = 'not_tour_assigned_someone';
            $additional_notification_data = serialize([
                get_staff_full_name($data['assignee']),
                $tour->name,
            ]);
            if ($data['assignee'] == get_staff_user_id()) {
                $description                  = 'not_tour_will_do_user';
                $additional_notification_data = serialize([
                    $tour->name,
                ]);
            }

            if ($tour->rel_type == 'booking') {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_new_tour_assignee', $tour->name . ' - ' . get_staff_full_name($data['assignee']), $tour->visible_to_client);
            }

            $this->_send_tour_responsible_users_notification($description, $data['tourid'], $data['assignee'], '', $additional_notification_data);

            return $assigneeId;
        }

        return false;
    }

    /**
     * Get all tour attachments
     * @param  mixed $tourid tourid
     * @return array
     */
    public function get_tour_attachments($tourid, $where = [])
    {
        $this->db->select(implode(', ', prefixed_table_fields_array(db_prefix() . 'files')) . ', ' . db_prefix() . 'tour_comments.id as comment_file_id');
        $this->db->where(db_prefix() . 'files.rel_id', $tourid);
        $this->db->where(db_prefix() . 'files.rel_type', 'tour');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        $this->db->join(db_prefix() . 'tour_comments', db_prefix() . 'tour_comments.file_id = ' . db_prefix() . 'files.id', 'left');
        $this->db->join(db_prefix() . 'tours', db_prefix() . 'tours.id = ' . db_prefix() . 'files.rel_id');
        $this->db->order_by(db_prefix() . 'files.dateadded', 'desc');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }

    /**
     * Remove tour attachment from server and database
     * @param  mixed $id attachmentid
     * @return boolean
     */
    public function remove_tour_attachment($id)
    {
        $comment_removed = false;
        $deleted         = false;
        // Get the attachment
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'files')->row();

        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath  = get_upload_path_by_type('tour') . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Tour Attachment Deleted [TourID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('tour') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('tour') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('tour') . $attachment->rel_id);
                }
            }
        }

        if ($deleted) {
            if ($attachment->tour_comment_id != 0) {
                $total_comment_files = total_rows(db_prefix() . 'files', ['tour_comment_id' => $attachment->tour_comment_id]);
                if ($total_comment_files == 0) {
                    $this->db->where('id', $attachment->tour_comment_id);
                    $comment = $this->db->get(db_prefix() . 'tour_comments')->row();

                    if ($comment) {
                        // Comment is empty and uploaded only with attachments
                        // Now all attachments are deleted, we need to delete the comment too
                        if (empty($comment->content) || $comment->content === '[tour_attachment]') {
                            $this->db->where('id', $attachment->tour_comment_id);
                            $this->db->delete(db_prefix() . 'tour_comments');
                            $comment_removed = $comment->id;
                        } else {
                            $this->db->query('UPDATE ' . db_prefix() . "tour_comments
                            SET content = REPLACE(content, '[tour_attachment]', '')
                            WHERE id = " . $attachment->tour_comment_id);
                        }
                    }
                }
            }

            $this->db->where('file_id', $id);
            $comment_attachment = $this->db->get(db_prefix() . 'tour_comments')->row();

            if ($comment_attachment) {
                $this->remove_comment($comment_attachment->id);
            }
        }

        return ['success' => $deleted, 'comment_removed' => $comment_removed];
    }

    /**
     * Add uploaded attachments to database
     * @since  Version 1.0.1
     * @param mixed $tourid     tour id
     * @param array $attachment attachment data
     */
    public function add_attachment_to_database($rel_id, $attachment, $external = false, $notification = true)
    {
        $file_id = $this->misc_model->add_attachment_to_database($rel_id, 'tour', $attachment, $external);
        if ($file_id) {
            $this->db->select('rel_type,rel_id,name,visible_to_client');
            $this->db->where('id', $rel_id);
            $tour = $this->db->get(db_prefix() . 'tours')->row();

            if ($tour->rel_type == 'booking') {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_new_tour_attachment', $tour->name, $tour->visible_to_client);
            }

            if ($notification == true) {
                $description = 'not_tour_new_attachment';
                $this->_send_tour_responsible_users_notification($description, $rel_id, false, 'tour_new_attachment_to_staff');
                $this->_send_customer_contacts_notification($rel_id, 'tour_new_attachment_to_customer');
            }

            $tour_attachment_as_comment = hooks()->apply_filters('add_tour_attachment_as_comment', 'true');

            if ($tour_attachment_as_comment == 'true') {
                $file = $this->misc_model->get_file($file_id);
                $this->db->insert(db_prefix() . 'tour_comments', [
                    'content'    => '[tour_attachment]',
                    'tourid'     => $rel_id,
                    'staffid'    => $file->staffid,
                    'contact_id' => $file->contact_id,
                    'file_id'    => $file_id,
                    'dateadded'  => date('Y-m-d H:i:s'),
                    ]);
            }

            return true;
        }

        return false;
    }

    /**
     * Get all tour followers
     * @param  mixed $id tour id
     * @return array
     */
    public function get_tour_followers($id)
    {
        $this->db->select('id,' . db_prefix() . 'tour_followers.staffid as followerid, CONCAT(firstname, " ", lastname) as full_name');
        $this->db->from(db_prefix() . 'tour_followers');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tour_followers.staffid');
        $this->db->where('tourid', $id);

        return $this->db->get()->result_array();
    }

    /**
     * Get all tour assigneed
     * @param  mixed $id tour id
     * @return array
     */
    public function get_tour_assignees($id)
    {
        $this->db->select('id,' . db_prefix() . 'tour_assigned.staffid as assigneeid,assigned_from,firstname,lastname,CONCAT(firstname, " ", lastname) as full_name,is_assigned_from_contact');
        $this->db->from(db_prefix() . 'tour_assigned');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tour_assigned.staffid');
        $this->db->where('tourid', $id);
        $this->db->order_by('firstname', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Get tour comment
     * @param  mixed $id tour id
     * @return array
     */
    public function get_tour_comments($id)
    {
        $tour_comments_order = hooks()->apply_filters('tour_comments_order', 'DESC');

        $this->db->select('id,dateadded,content,' . db_prefix() . 'staff.firstname,' . db_prefix() . 'staff.lastname,' . db_prefix() . 'tour_comments.staffid,' . db_prefix() . 'tour_comments.contact_id as contact_id,file_id,CONCAT(firstname, " ", lastname) as staff_full_name');
        $this->db->from(db_prefix() . 'tour_comments');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'tour_comments.staffid', 'left');
        $this->db->where('tourid', $id);
        $this->db->order_by('dateadded', $tour_comments_order);

        $comments = $this->db->get()->result_array();

        $ids = [];
        foreach ($comments as $key => $comment) {
            array_push($ids, $comment['id']);
            $comments[$key]['attachments'] = [];
        }

        if (count($ids) > 0) {
            $allAttachments = $this->get_tour_attachments($id, 'tour_comment_id IN (' . implode(',', $ids) . ')');
            foreach ($comments as $key => $comment) {
                foreach ($allAttachments as $attachment) {
                    if ($comment['id'] == $attachment['tour_comment_id']) {
                        $comments[$key]['attachments'][] = $attachment;
                    }
                }
            }
        }

        return $comments;
    }

    public function edit_comment($data)
    {
        // Check if user really creator
        $this->db->where('id', $data['id']);
        $comment = $this->db->get(db_prefix() . 'tour_comments')->row();
        if ($comment->staffid == get_staff_user_id() || has_permission('tours', '', 'edit') || $comment->contact_id == get_contact_user_id()) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour  = strtotime('-1 hours');
            if (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()) {
                if (total_rows(db_prefix() . 'files', ['tour_comment_id' => $comment->id]) > 0) {
                    $data['content'] .= '[tour_attachment]';
                }

                $this->db->where('id', $data['id']);
                $this->db->update(db_prefix() . 'tour_comments', [
                    'content' => $data['content'],
                ]);
                if ($this->db->affected_rows() > 0) {
                    hooks()->do_action('tour_comment_updated', [
                        'comment_id' => $comment->id,
                        'tour_id'    => $comment->tourid,
                    ]);

                    return true;
                }
            } else {
                return false;
            }

            return false;
        }
    }

    /**
     * Remove tour comment from database
     * @param  mixed $id tour id
     * @return boolean
     */
    public function remove_comment($id, $force = false)
    {
        // Check if user really creator
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'tour_comments')->row();

        if (!$comment) {
            return true;
        }

        if ($comment->staffid == get_staff_user_id() || has_permission('tours', '', 'delete') || $comment->contact_id == get_contact_user_id() || $force === true) {
            $comment_added = strtotime($comment->dateadded);
            $minus_1_hour  = strtotime('-1 hours');
            if (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 1 && $comment_added >= $minus_1_hour)
                || (is_admin() || $force === true)) {
                $this->db->where('id', $id);
                $this->db->delete(db_prefix() . 'tour_comments');
                if ($this->db->affected_rows() > 0) {
                    if ($comment->file_id != 0) {
                        $this->remove_tour_attachment($comment->file_id);
                    }

                    $commentAttachments = $this->get_tour_attachments($comment->tourid, 'tour_comment_id=' . $id);
                    foreach ($commentAttachments as $attachment) {
                        $this->remove_tour_attachment($attachment['id']);
                    }

                    hooks()->do_action('tour_comment_deleted', [ 'tour_id' => $comment->tourid, 'comment_id' => $id ]);

                    return true;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Remove tour assignee from database
     * @param  mixed $id     assignee id
     * @param  mixed $tourid tour id
     * @return boolean
     */
    public function remove_assignee($id, $tourid)
    {
        $this->db->select('rel_type,rel_id,name,visible_to_client');
        $this->db->where('id', $tourid);
        $tour = $this->db->get(db_prefix() . 'tours')->row();

        $this->db->where('id', $id);
        $assignee_data = $this->db->get(db_prefix() . 'tour_assigned')->row();

        // Delete timers
        //   $this->db->where('tour_id', $tourid);
        ////   $this->db->where('staff_id', $assignee_data->staffid);
        ///   $this->db->delete(db_prefix().'tourstimers');

        // Stop all timers
        $this->db->where('tour_id', $tourid);
        $this->db->where('staff_id', $assignee_data->staffid);
        $this->db->where('end_time IS NULL');
        $this->db->update(db_prefix() . 'tourstimers', ['end_time' => time()]);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tour_assigned');
        if ($this->db->affected_rows() > 0) {
            if ($tour->rel_type == 'booking') {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_tour_assignee_removed', $tour->name . ' - ' . get_staff_full_name($assignee_data->staffid), $tour->visible_to_client);
            }

            return true;
        }

        return false;
    }

    /**
     * Remove tour follower from database
     * @param  mixed $id     followerid
     * @param  mixed $tourid tour id
     * @return boolean
     */
    public function remove_follower($id, $tourid)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tour_followers');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Change tour status
     * @param  mixed $status  tour status
     * @param  mixed $tour_id tour id
     * @return boolean
     */
    public function mark_as($status, $tour_id)
    {
        $this->db->select('rel_type,rel_id,name,visible_to_client,status');
        $this->db->where('id', $tour_id);
        $tour = $this->db->get(db_prefix() . 'tours')->row();

        if ($tour->status == self::STATUS_COMPLETE) {
            return $this->unmark_complete($tour_id, $status);
        }

        $update = ['status' => $status];

        if ($status == self::STATUS_COMPLETE) {
            $update['datefinished'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $tour_id);
        $this->db->update(db_prefix() . 'tours', $update);
        if ($this->db->affected_rows() > 0) {
            $description = 'not_tour_status_changed';

            $not_data = [
                $tour->name,
                format_tour_status($status, false, true),
            ];

            if ($status == self::STATUS_COMPLETE) {
                $description = 'not_tour_marked_as_complete';
                unset($not_data[1]);

                $this->db->where('end_time IS NULL');
                $this->db->where('tour_id', $tour_id);
                $this->db->update(db_prefix() . 'tourstimers', [
                    'end_time' => time(),
                ]);
            }

            if ($tour->rel_type == 'booking') {
                $booking_activity_log = $status == self::STATUS_COMPLETE ? 'booking_activity_tour_marked_complete' : 'not_booking_activity_tour_status_changed';

                $booking_activity_desc = $tour->name;

                if ($status != self::STATUS_COMPLETE) {
                    $booking_activity_desc .= ' - ' . format_tour_status($status);
                }

                $this->bookings_model->log_activity($tour->rel_id, $booking_activity_log, $booking_activity_desc, $tour->visible_to_client);
            }

            $this->_send_tour_responsible_users_notification($description, $tour_id, false, 'tour_status_changed_to_staff', serialize($not_data));

            $this->_send_customer_contacts_notification($tour_id, 'tour_status_changed_to_customer');
            hooks()->do_action('tour_status_changed', ['status' => $status, 'tour_id' => $tour_id]);

            return true;
        }

        return false;
    }

    /**
     * Unmark tour as complete
     * @param  mixed $id tour id
     * @return boolean
     */
    public function unmark_complete($id, $force_to_status = false)
    {
        if ($force_to_status != false) {
            $status = $force_to_status;
        } else {
            $status = 1;
            $this->db->select('startdate');
            $this->db->where('id', $id);
            $_tour = $this->db->get(db_prefix() . 'tours')->row();
            if (date('Y-m-d') > date('Y-m-d', strtotime($_tour->startdate))) {
                $status = 4;
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'tours', [
            'datefinished' => null,
            'status'       => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            $this->db->select('rel_type,rel_id,name,visible_to_client');
            $this->db->where('id', $id);
            $tour = $this->db->get(db_prefix() . 'tours')->row();

            if ($tour->rel_type == 'booking') {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_tour_unmarked_complete', $tour->name, $tour->visible_to_client);
            }

            $description = 'not_tour_unmarked_as_complete';

            $this->_send_tour_responsible_users_notification('not_tour_unmarked_as_complete', $id, false, 'tour_status_changed_to_staff', serialize([
                $tour->name,
            ]));

            hooks()->do_action('tour_status_changed', ['status' => $status, 'tour_id' => $id]);

            return true;
        }

        return false;
    }

    /**
     * Delete tour and all connections
     * @param  mixed $id tourid
     * @return boolean
     */
    public function delete_tour($id, $log_activity = true)
    {
        $this->db->select('rel_type,rel_id,name,visible_to_client');
        $this->db->where('id', $id);
        $tour = $this->db->get(db_prefix() . 'tours')->row();

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tours');
        if ($this->db->affected_rows() > 0) {

            // Log activity only if tour is deleted indivudual not when deleting all bookings
            if ($tour->rel_type == 'booking' && $log_activity == true) {
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_tour_deleted', $tour->name, $tour->visible_to_client);
            }

            $this->db->where('tourid', $id);
            $this->db->delete(db_prefix() . 'tour_followers');

            $this->db->where('tourid', $id);
            $this->db->delete(db_prefix() . 'tour_assigned');

            $this->db->where('tourid', $id);
            $comments = $this->db->get(db_prefix() . 'tour_comments')->result_array();
            foreach ($comments as $comment) {
                $this->remove_comment($comment['id'], true);
            }

            $this->db->where('tourid', $id);
            $this->db->delete(db_prefix() . 'tour_checklist_items');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'tours');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('tour_id', $id);
            $this->db->delete(db_prefix() . 'tourstimers');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tour');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'tour');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tour');
            $attachments = $this->db->get(db_prefix() . 'files')->result_array();
            foreach ($attachments as $at) {
                $this->remove_tour_attachment($at['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tour');
            $this->db->delete(db_prefix() . 'related_items');

            if (is_dir(get_upload_path_by_type('tour') . $id)) {
                delete_dir(get_upload_path_by_type('tour') . $id);
            }

            hooks()->do_action('tour_deleted', $id);

            return true;
        }

        return false;
    }

    /**
     * Send notification on tour activity to creator,follower/s,assignee/s
     * @param  string  $description notification description
     * @param  mixed  $tourid      tour id
     * @param  boolean $excludeid   excluded staff id to not send the notifications
     * @return boolean
     */
    private function _send_tour_responsible_users_notification($description, $tourid, $excludeid = false, $email_template = '', $additional_notification_data = '', $comment_id = false)
    {
        $this->load->model('staff_model');

        $staff = $this->staff_model->get('', ['active' => 1]);

        $notifiedUsers = [];
        foreach ($staff as $member) {
            if (is_numeric($excludeid)) {
                if ($excludeid == $member['staffid']) {
                    continue;
                }
            }
            if (!is_client_logged_in()) {
                if ($member['staffid'] == get_staff_user_id()) {
                    continue;
                }
            }

            if ($this->should_staff_receive_notification($member['staffid'], $tourid)) {
                $link = '#tourid=' . $tourid;

                if ($comment_id) {
                    $link .= '#comment_' . $comment_id;
                }

                $notified = add_notification([
                    'description'     => $description,
                    'touserid'        => $member['staffid'],
                    'link'            => $link,
                    'additional_data' => $additional_notification_data,
                ]);

                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }

                if ($email_template != '') {
                    send_mail_template($email_template, BOOKING_MODULE_NAME, $member['email'], $member['staffid'], $tourid);
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function _send_customer_contacts_notification($tourid, $template_name)
    {
        $this->db->select('rel_id,visible_to_client,rel_type');
        $this->db->from(db_prefix() . 'tours');
        $this->db->where('id', $tourid);
        $tour = $this->db->get()->row();

        if ($tour->rel_type == 'booking') {
            $this->db->where('booking_id', $tour->rel_id);
            $this->db->where('name', 'view_tours');
            $booking_settings = $this->db->get(db_prefix() . 'booking_settings')->row();
            if ($booking_settings) {
                if ($booking_settings->value == 1 && $tour->visible_to_client == 1) {
                    $this->db->select('clientid');
                    $this->db->from(db_prefix() . 'bookings');
                    $this->db->where('id', $booking_settings->booking_id);
                    $booking  = $this->db->get()->row();
                    $contacts = $this->clients_model->get_contacts($booking->clientid, ['active' => 1, 'tour_emails' => 1]);
                    foreach ($contacts as $contact) {
                        if (is_client_logged_in() && get_contact_user_id() == $contact['id']) {
                            continue;
                        }

                        send_mail_template($template_name, BOOKING_MODULE_NAME, $contact['email'], $booking->clientid, $contact['id'], $tourid);
                    }
                }
            }
        }
    }

    /**
     * Check if user has commented on tour
     * @param  mixed $userid staff id to check
     * @param  mixed $tourid tour id
     * @return boolean
     */
    public function staff_has_commented_on_tour($userid, $tourid)
    {
        return total_rows(db_prefix() . 'tour_comments', ['staffid' => $userid, 'tourid' => $tourid]) > 0 ? true : false;
    }

    /**
     * Check is user is tour follower
     * @param  mixed  $userid staff id
     * @param  mixed  $tourid tourid
     * @return boolean
     */
    public function is_tour_follower($userid, $tourid)
    {
        if (total_rows(db_prefix() . 'tour_followers', [
            'staffid' => $userid,
            'tourid' => $tourid,
        ]) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Check is user is tour assignee
     * @param  mixed  $userid staff id
     * @param  mixed  $tourid tourid
     * @return boolean
     */
    public function is_tour_assignee($userid, $tourid)
    {
        if (total_rows(db_prefix() . 'tour_assigned', [
            'staffid' => $userid,
            'tourid' => $tourid,
        ]) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Check is user is tour creator
     * @param  mixed  $userid staff id
     * @param  mixed  $tourid tourid
     * @return boolean
     */
    public function is_tour_creator($userid, $tourid)
    {
        if (total_rows(db_prefix() . 'tours', [
            'addedfrom' => $userid,
            'id' => $tourid,
            'is_added_from_contact' => 0,
        ]) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Timer action, START/STOP Timer
     * @param  mixed  $tour_id   tour id
     * @param  mixed  $timer_id  timer_id to stop the timer
     * @param  string  $note      note for timer
     * @param  boolean $adminStop is admin want to stop timer from another staff member
     * @return boolean
     */
    public function timer_tracking($tour_id = '', $timer_id = '', $note = '', $adminStop = false)
    {
        if ($tour_id == '' && $timer_id == '') {
            return false;
        }

        if ($tour_id !== '0' && $adminStop == false) {
            if (!$this->is_tour_assignee(get_staff_user_id(), $tour_id)) {
                return false;
            } elseif ($this->is_tour_billed($tour_id)) {
                return false;
            }
        }

        $timer = $this->get_tour_timer([
            'id' => $timer_id,
        ]);

        $newTimer = false;

        if ($timer == null) {
            $newTimer = true;
        }

        if ($newTimer) {
            $this->db->select('hourly_rate');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', get_staff_user_id());
            $hourly_rate = $this->db->get()->row()->hourly_rate;

            $this->db->insert(db_prefix() . 'tourstimers', [
                'start_time'  => time(),
                'staff_id'    => get_staff_user_id(),
                'tour_id'     => $tour_id,
                'hourly_rate' => $hourly_rate,
                'note'        => ($note != '' ? $note : null),
            ]);

            $_new_timer_id = $this->db->insert_id();

            if (get_option('auto_stop_tours_timers_on_new_timer') == 1) {
                $this->db->where('id !=', $_new_timer_id);
                $this->db->where('end_time IS NULL');
                $this->db->where('tour_id !=', '0');
                $this->db->where('staff_id', get_staff_user_id());
                $this->db->update(db_prefix() . 'tourstimers', [
                    'end_time' => time(),
                    'note'     => ($note != '' ? $note : null),
                ]);
            }

            if ($tour_id != '0'
                && get_option('timer_started_change_status_in_progress') == '1'
                && total_rows(db_prefix() . 'tours', ['id' => $tour_id, 'status' => 1]) > 0) {
                $this->mark_as(4, $tour_id);
            }

            hooks()->do_action('tour_timer_started', ['tour_id' => $tour_id, 'timer_id' => $_new_timer_id]);

            return true;
        }

        if ($timer) {
            // time already ended
            if ($timer->end_time != null) {
                return false;
            }
            $this->db->where('id', $timer_id);
            $this->db->update(db_prefix() . 'tourstimers', [
                    'end_time' => time(),
                    'tour_id'  => $tour_id,
                    'note'     => ($note != '' ? $note : null),
                ]);
        }

        return true;
    }

    public function timesheet($data)
    {
        if (isset($data['timesheet_duration']) && $data['timesheet_duration'] != '') {
            $duration_array = explode(':', $data['timesheet_duration']);
            $hour           = $duration_array[0];
            $minutes        = $duration_array[1];
            $end_time       = time();
            $start_time     = strtotime('-' . $hour . ' hour -' . $minutes . ' minutes');
        } else {
            $start_time = to_sql_date($data['start_time'], true);
            $end_time   = to_sql_date($data['end_time'], true);
            $start_time = strtotime($start_time);
            $end_time   = strtotime($end_time);
        }

        if ($end_time < $start_time) {
            return [
                'end_time_smaller' => true,
            ];
        }

        $timesheet_staff_id = get_staff_user_id();
        if (isset($data['timesheet_staff_id']) && $data['timesheet_staff_id'] != '') {
            $timesheet_staff_id = $data['timesheet_staff_id'];
        }

        if (!isset($data['timer_id']) || (isset($data['timer_id']) && $data['timer_id'] == '')) {

            // Stop all other timesheets when adding new timesheet
            $this->db->where('tour_id', $data['timesheet_tour_id']);
            $this->db->where('staff_id', $timesheet_staff_id);
            $this->db->where('end_time IS NULL');
            $this->db->update(db_prefix() . 'tourstimers', [
                'end_time' => time(),
            ]);

            $this->db->select('hourly_rate');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('staffid', $timesheet_staff_id);
            $hourly_rate = $this->db->get()->row()->hourly_rate;

            $this->db->insert(db_prefix() . 'tourstimers', [
                'start_time'  => $start_time,
                'end_time'    => $end_time,
                'staff_id'    => $timesheet_staff_id,
                'tour_id'     => $data['timesheet_tour_id'],
                'hourly_rate' => $hourly_rate,
                'note'        => (isset($data['note']) && $data['note'] != '' ? nl2br($data['note']) : null),
            ]);

            $insert_id = $this->db->insert_id();
            $tags      = '';

            if (isset($data['tags'])) {
                $tags = $data['tags'];
            }

            handle_tags_save($tags, $insert_id, 'timesheet');

            if ($insert_id) {
                $this->db->select('rel_type,rel_id,name,visible_to_client');
                $this->db->where('id', $data['timesheet_tour_id']);
                $tour = $this->db->get(db_prefix() . 'tours')->row();

                if ($tour->rel_type == 'booking') {
                    $total      = $end_time - $start_time;
                    $additional = '<seconds>' . $total . '</seconds>';
                    $additional .= '<br />';
                    $additional .= '<lang>booking_activity_tour_name</lang> ' . $tour->name;
                    $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_recorded_timesheet', $additional, $tour->visible_to_client);
                }

                return true;
            }

            return false;
        }
        $affectedRows = 0;
        $this->db->where('id', $data['timer_id']);
        $this->db->update(db_prefix() . 'tourstimers', [
                'start_time' => $start_time,
                'end_time'   => $end_time,
                'staff_id'   => $timesheet_staff_id,
                'tour_id'    => $data['timesheet_tour_id'],
                'note'       => (isset($data['note']) && $data['note'] != '' ? nl2br($data['note']) : null),
            ]);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $data['timer_id'], 'timesheet')) {
                $affectedRows++;
            }
        }

        return ($affectedRows > 0 ? true : false);
    }

    public function get_timers($tour_id, $where = [])
    {
        $this->db->where($where);
        $this->db->where('tour_id', $tour_id);
        $this->db->order_by('start_time', 'DESC');

        return $this->db->get(db_prefix() . 'tourstimers')->result_array();
    }

    public function get_tour_timer($where)
    {
        $this->db->where($where);

        return $this->db->get(db_prefix() . 'tourstimers')->row();
    }

    public function is_timer_started($tour_id, $staff_id = '')
    {
        if ($staff_id == '') {
            $staff_id = get_staff_user_id();
        }

        $timer = $this->get_last_timer($tour_id, $staff_id);

        if (!$timer) {
            return false;
        }

        if ($timer->end_time != null) {
            return false;
        }

        return $timer;
    }

    public function is_timer_started_for_tour($id, $where = [])
    {
        $this->db->where('tour_id', $id);
        $this->db->where('end_time IS NULL');
        $this->db->where($where);
        $results = $this->db->count_all_results(db_prefix() . 'tourstimers');

        return $results > 0 ? true : false;
    }

    public function get_last_timer($tour_id, $staff_id = '')
    {
        if ($staff_id == '') {
            $staff_id = get_staff_user_id();
        }
        $this->db->where('staff_id', $staff_id);
        $this->db->where('tour_id', $tour_id);
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        $timer = $this->db->get(db_prefix() . 'tourstimers')->row();

        return $timer;
    }

    public function tour_tracking_stats($id)
    {
        $loggers    = $this->db->query('SELECT DISTINCT(staff_id) FROM ' . db_prefix() . 'tourstimers WHERE tour_id=' . $this->db->escape_str($id))->result_array();
        $labels     = [];
        $labels_ids = [];
        foreach ($loggers as $assignee) {
            array_push($labels, get_staff_full_name($assignee['staff_id']));
            array_push($labels_ids, $assignee['staff_id']);
        }
        $chart = [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label' => _l('tour_stats_logged_hours'),
                    'data'  => [],
                ],
            ],
        ];
        $i = 0;
        foreach ($labels_ids as $staffid) {
            $chart['datasets'][0]['data'][$i] = sec2qty($this->calc_tour_total_time($id, ' AND staff_id=' . $staffid));
            $i++;
        }

        return $chart;
    }

    public function get_timesheeets($tour_id)
    {
        $tour_id = $this->db->escape_str($tour_id);

        return $this->db->query("SELECT id,note,start_time,end_time,tour_id,staff_id, CONCAT(firstname, ' ', lastname) as full_name,
        end_time - start_time time_spent FROM " . db_prefix() . 'tourstimers JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid=' . db_prefix() . "tourstimers.staff_id WHERE tour_id = '$tour_id' ORDER BY start_time DESC")->result_array();
    }

    public function get_time_spent($seconds)
    {
        $minutes = $seconds / 60;
        $hours   = $minutes / 60;
        if ($minutes >= 60) {
            return round($hours, 2);
        } elseif ($seconds > 60) {
            return round($minutes, 2);
        }

        return $seconds;
    }

    public function calc_tour_total_time($tour_id, $where = '')
    {
        $sql = get_sql_calc_tour_logged_time($tour_id) . $where;

        $result = $this->db->query($sql)->row();

        if ($result) {
            return $result->total_logged_time;
        }

        return 0;
    }

    public function get_unique_member_logged_tour_ids($staff_id, $where = '')
    {
        $sql = 'SELECT DISTINCT(tour_id)
        FROM ' . db_prefix() . 'tourstimers WHERE staff_id =' . $staff_id . $where;

        return $this->db->query($sql)->result();
    }

    /**
     * @deprecated
     */
    private function _cal_total_logged_array_from_timers($timers)
    {
        $total = [];
        foreach ($timers as $key => $timer) {
            $_tspent = 0;
            if (is_null($timer->end_time)) {
                $_tspent = time() - $timer->start_time;
            } else {
                $_tspent = $timer->end_time - $timer->start_time;
            }
            $total[] = $_tspent;
        }

        return array_sum($total);
    }

    public function delete_timesheet($id)
    {
        $this->db->where('id', $id);
        $timesheet = $this->db->get(db_prefix() . 'tourstimers')->row();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tourstimers');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'timesheet');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->select('rel_type,rel_id,name,visible_to_client');
            $this->db->where('id', $timesheet->tour_id);
            $tour = $this->db->get(db_prefix() . 'tours')->row();

            if ($tour->rel_type == 'booking') {
                $additional_data = $tour->name;
                $total           = $timesheet->end_time - $timesheet->start_time;
                $additional_data .= '<br /><seconds>' . $total . '</seconds>';
                $this->bookings_model->log_activity($tour->rel_id, 'booking_activity_tour_timesheet_deleted', $additional_data, $tour->visible_to_client);
            }

            hooks()->do_action('tour_timer_deleted', $timesheet);

            log_activity('Timesheet Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_reminders($tour_id)
    {
        $this->db->where('rel_id', $tour_id);
        $this->db->where('rel_type', 'tour');
        $this->db->order_by('isnotified,date', 'ASC');

        return $this->db->get(db_prefix() . 'reminders')->result_array();
    }

    /**
     * Check whether the given staff can access the given tour
     *
     * @param  int $staff_id
     * @param  int $tour_id
     *
     * @return boolean
     */
    public function can_staff_access_tour($staff_id, $tour_id)
    {
        $retVal              = false;
        $staffCanAccessTours = $this->get_staff_members_that_can_access_tour($tour_id);
        foreach ($staffCanAccessTours as $staff) {
            if ($staff['staffid'] == $staff_id) {
                $retVal = true;

                break;
            }
        }

        return $retVal;
    }

    /**
     * Get the staff members that can view the given tour
     *
     * @param  int $tour_id
     *
     * @return array
     */
    public function get_staff_members_that_can_access_tour($tour_id)
    {
        $tour_id = $this->db->escape_str($tour_id);

        return $this->db->query('SELECT * FROM ' . db_prefix() . 'staff
            WHERE (
                    admin=1
                    OR staffid IN (SELECT staffid FROM ' . db_prefix() . "tour_assigned WHERE tourid='.$tour_id.')
                    OR staffid IN (SELECT staffid FROM " . db_prefix() . "tour_followers WHERE tourid='.$tour_id.')
                    OR staffid IN (SELECT addedfrom FROM " . db_prefix() . "tours WHERE id='.$tour_id.' AND is_added_from_contact=0)
                    OR staffid IN(SELECT staff_id FROM " . db_prefix() . 'staff_permissions WHERE feature = "tours" AND capability="view")
                )
            AND active=1')->result_array();
    }

    /**
     * Check whether the given staff should receive notification for
     * the given tour
     *
     * @param  int $staffid
     * @param  int $tourid  [description]
     *
     * @return boolean
     */
    private function should_staff_receive_notification($staffid, $tourid)
    {
        if (!$this->can_staff_access_tour($staffid, $tourid)) {
            return false;
        }

        return hooks()->apply_filters('should_staff_receive_tour_notification', ($this->is_tour_assignee($staffid, $tourid)
                || $this->is_tour_follower($staffid, $tourid)
                || $this->is_tour_creator($staffid, $tourid)
                || $this->staff_has_commented_on_tour($staffid, $tourid)), ['staff_id' => $staffid, 'tour_id' => $tourid]);
    }
}
