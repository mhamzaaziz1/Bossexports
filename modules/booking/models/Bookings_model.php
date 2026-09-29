<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bookings_model extends App_Model
{
    private $booking_settings;

    public function __construct()
    {
        parent::__construct();

        $booking_settings = [
            'available_features',
            'view_tours',
            'create_tours',
            'edit_tours',
            'comment_on_tours',
            'view_tour_comments',
            'view_tour_attachments',
            'view_tour_checklist_items',
            'upload_on_tours',
            'view_tour_total_logged_time',
            'view_finance_overview',
            'upload_files',
            'open_discussions',
            'view_milestones',
            'view_gantt',
            'view_timesheets',
            'view_activity_log',
            'view_team_members',
            'hide_tours_on_main_tours_table',
        ];

        $this->booking_settings = hooks()->apply_filters('booking_settings', $booking_settings);
    }

    public function get_booking_statuses()
    {
        $statuses = hooks()->apply_filters('before_get_booking_statuses', [
            [
                'id'             => 1,
                'color'          => '#989898',
                'name'           => _l('booking_status_1'),
                'order'          => 1,
                'filter_default' => true,
            ],
            [
                'id'             => 2,
                'color'          => '#03a9f4',
                'name'           => _l('booking_status_2'),
                'order'          => 2,
                'filter_default' => true,
            ],
            [
                'id'             => 3,
                'color'          => '#ff6f00',
                'name'           => _l('booking_status_3'),
                'order'          => 100,
                'filter_default' => true,
            ],
            [
                'id'             => 4,
                'color'          => '#84c529',
                'name'           => _l('booking_status_4'),
                'order'          => 3,
                'filter_default' => true,
            ],
            [
                'id'             => 6,
                'color'          => '#84c529',
                'name'           => _l('booking_status_6'),
                'order'          => 101,
                'filter_default' => false,
            ],
            [
                'id'             => 5,
                'color'          => '#989898',
                'name'           => _l('booking_status_5'),
                'order'          => 4,
                'filter_default' => false,
            ],
        ]);

        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $statuses;
    }

    public function get_distinct_tours_timesheets_staff($booking_id)
    {
        return $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . 'tourstimers LEFT JOIN ' . db_prefix() . 'tours ON ' . db_prefix() . 'tours.id = ' . db_prefix() . 'tourstimers.tour_id WHERE rel_type="booking" AND rel_id=' . $this->db->escape_str($booking_id))->result_array();
    }

    public function get_distinct_bookings_members()
    {
        return $this->db->query('SELECT staff_id, firstname, lastname FROM ' . db_prefix() . 'booking_members JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'booking_members.staff_id GROUP by staff_id order by firstname ASC')->result_array();
    }

    public function get_most_used_billing_type()
    {
        return $this->db->query('SELECT billing_type, COUNT(*) AS total_usage
                FROM ' . db_prefix() . 'bookings
                GROUP BY billing_type
                ORDER BY total_usage DESC
                LIMIT 1')->row();
    }

    public function timers_started_for_booking($booking_id, $where = [], $tour_timers_where = [])
    {
        $this->db->where($where);
        $this->db->where('end_time IS NULL');
        $this->db->where(db_prefix() . 'tours.rel_id', $booking_id);
        $this->db->where(db_prefix() . 'tours.rel_type', 'booking');
        $this->db->join(db_prefix() . 'tours', db_prefix() . 'tours.id=' . db_prefix() . 'tourstimers.tour_id');
        $total = $this->db->count_all_results(db_prefix() . 'tourstimers');

        return $total > 0 ? true : false;
    }

    public function pin_action($id)
    {
        if (total_rows(db_prefix() . 'pinned_bookings', [
            'staff_id' => get_staff_user_id(),
            'booking_id' => $id,
        ]) == 0) {
            $this->db->insert(db_prefix() . 'pinned_bookings', [
                'staff_id'   => get_staff_user_id(),
                'booking_id' => $id,
            ]);

            return true;
        }
        $this->db->where('booking_id', $id);
        $this->db->where('staff_id', get_staff_user_id());
        $this->db->delete(db_prefix() . 'pinned_bookings');

        return true;
    }

    public function get_currency($id)
    {
        $this->load->model('currencies_model');
        $customer_currency = $this->clients_model->get_customer_default_currency(get_client_id_by_booking_id($id));
        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency);
        } else {
            $currency = $this->currencies_model->get_base_currency();
        }

        return $currency;
    }

    public function calc_progress($id)
    {
        $this->db->select('progress_from_tours,progress,status');
        $this->db->where('id', $id);
        $booking = $this->db->get(db_prefix() . 'bookings')->row();

        if ($booking->status == 4) {
            return 100;
        }

        if ($booking->progress_from_tours == 1) {
            return $this->calc_progress_by_tours($id);
        }

        return $booking->progress;
    }

    public function calc_progress_by_tours($id)
    {
        $total_booking_tours = total_rows(db_prefix() . 'tours', [
            'rel_type' => 'booking',
            'rel_id'   => $id,
        ]);
        $total_finished_tours = total_rows(db_prefix() . 'tours', [
            'rel_type' => 'booking',
            'rel_id'   => $id,
            'status'   => 5,
        ]);
        $percent = 0;
        if ($total_finished_tours >= floatval($total_booking_tours)) {
            $percent = 100;
        } else {
            if ($total_booking_tours !== 0) {
                $percent = number_format(($total_finished_tours * 100) / $total_booking_tours, 2);
            }
        }

        return $percent;
    }

    public function get_last_booking_settings()
    {
        $this->db->select('id');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last_booking = $this->db->get(db_prefix() . 'bookings')->row();
        if ($last_booking) {
            return $this->get_booking_settings($last_booking->id);
        }

        return [];
    }

    public function get_settings()
    {
        return $this->booking_settings;
    }

    public function get($id = '', $where = [])
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $booking = $this->db->get(db_prefix() . 'bookings')->row();
            if ($booking) {
                $booking->shared_vault_entries = $this->clients_model->get_vault_entries($booking->clientid, ['share_in_bookings' => 1]);
                $settings                      = $this->get_booking_settings($id);

                // SYNC NEW TABS
                $tabs                        = get_booking_tabs_admin();
                $tabs_flatten                = [];
                $settings_available_features = [];

                $available_features_index = false;
                foreach ($settings as $key => $setting) {
                    if ($setting['name'] == 'available_features') {
                        $available_features_index = $key;
                        if(isset($setting['value']))$available_features       = unserialize($setting['value']);
                        if (is_array($available_features)) {
                            foreach ($available_features as $name => $avf) {
                                $settings_available_features[] = $name;
                            }
                        }
                    }
                }
                foreach ($tabs as $tab) {
                    if (isset($tab['collapse'])) {
                        foreach ($tab['children'] as $d) {
                            $tabs_flatten[] = $d['slug'];
                        }
                    } else {
                        $tabs_flatten[] = $tab['slug'];
                    }
                }
                if (count($settings_available_features) != $tabs_flatten) {
                    foreach ($tabs_flatten as $tab) {
                        if (!in_array($tab, $settings_available_features)) {
                            if ($available_features_index) {
                                $current_available_features_settings = $settings[$available_features_index];
                                $tmp                                 = unserialize($current_available_features_settings['value']);
                                $tmp[$tab]                           = 1;
                                $this->db->where('id', $current_available_features_settings['id']);
                                $this->db->update(db_prefix() . 'booking_settings', ['value' => serialize($tmp)]);
                            }
                        }
                    }
                }

                $booking->settings = new StdClass();

                foreach ($settings as $setting) {
                    $booking->settings->{$setting['name']} = $setting['value'];
                }

                $booking->client_data = new StdClass();
                $booking->client_data = $this->clients_model->get($booking->clientid);

                $booking            = hooks()->apply_filters('booking_get', $booking);
                $GLOBALS['booking'] = $booking;

                return $booking;
            }

            return null;
        }

        $this->db->select('*,' . get_sql_select_client_company());
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'bookings.clientid');
        $this->db->order_by('id', 'desc');

        return $this->db->get(db_prefix() . 'bookings')->result_array();
    }

    public function calculate_total_by_booking_hourly_rate($seconds, $hourly_rate)
    {
        $hours       = seconds_to_time_format($seconds);
        $decimal     = sec2qty($seconds);
        $total_money = 0;
        $total_money += ($decimal * $hourly_rate);

        return [
            'hours'       => $hours,
            'total_money' => $total_money,
        ];
    }

    public function calculate_total_by_tour_hourly_rate($tours)
    {
        $total_money    = 0;
        $_total_seconds = 0;

        foreach ($tours as $tour) {
            $seconds = $tour['total_logged_time'];
            $_total_seconds += $seconds;
            $total_money += sec2qty($seconds) * $tour['hourly_rate'];
        }

        return [
            'total_money'   => $total_money,
            'total_seconds' => $_total_seconds,
        ];
    }

    public function get_tours($id, $where = [], $apply_restrictions = false, $count = false, $callback = null)
    {
        $has_permission                    = has_permission('tours', '', 'view');
        $show_all_tours_for_booking_member = get_option('show_all_tours_for_booking_member');

        $select = implode(', ', prefixed_table_fields_array(db_prefix() . 'tours')) . ',' . db_prefix() . 'milestones.name as milestone_name,
        (SELECT SUM(CASE
            WHEN end_time is NULL THEN ' . time() . '-start_time
            ELSE end_time-start_time
            END) FROM ' . db_prefix() . 'tourstimers WHERE tour_id=' . db_prefix() . 'tours.id) as total_logged_time,
           ' . get_sql_select_tour_assignees_ids() . ' as assignees_ids
        ';

        if (!is_client_logged_in() && is_staff_logged_in()) {
            $select .= ',(SELECT staffid FROM ' . db_prefix() . 'tour_assigned WHERE tourid=' . db_prefix() . 'tours.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned';
        }

        if (is_client_logged_in()) {
            $this->db->where('visible_to_client', 1);
        }

        $this->db->select($select);

        $this->db->join(db_prefix() . 'milestones', db_prefix() . 'milestones.id = ' . db_prefix() . 'tours.milestone', 'left');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'booking');
        if ($apply_restrictions == true) {
            if (!is_client_logged_in() && !$has_permission && $show_all_tours_for_booking_member == 0) {
                $this->db->where('(
                    ' . db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . get_staff_user_id() . ')
                    OR ' . db_prefix() . 'tours.id IN(SELECT tourid FROM ' . db_prefix() . 'tour_followers WHERE staffid=' . get_staff_user_id() . ')
                    OR is_public = 1
                    OR (addedfrom =' . get_staff_user_id() . ' AND is_added_from_contact = 0)
                    )');
            }
        }

        $this->db->where($where);

        // Milestones kanban order
        // Request is admin/bookings/milestones_kanban
        if ($this->uri->segment(3) == 'milestones_kanban' | $this->uri->segment(3) == 'milestones_kanban_load_more') {
            $this->db->order_by('milestone_order', 'asc');
        } else {
            $orderByString = hooks()->apply_filters('booking_tours_array_default_order', 'FIELD(status, 5), duedate IS NULL ASC, duedate');
            $this->db->order_by($orderByString, '', false);
        }

        if ($callback) {
            $callback();
        }

        if ($count == false) {
            $tours = $this->db->get(db_prefix() . 'tours')->result_array();
        } else {
            $tours = $this->db->count_all_results(db_prefix() . 'tours');
        }

        return $tours;
    }

    public function cancel_recurring_tours($id)
    {
        $this->db->where('rel_type', 'booking');
        $this->db->where('rel_id', $id);
        $this->db->where('recurring', 1);
        $this->db->where('(cycles != total_cycles OR cycles=0)');

        $this->db->update(db_prefix() . 'tours', [
            'recurring_type'      => null,
            'repeat_every'        => 0,
            'cycles'              => 0,
            'recurring'           => 0,
            'custom_recurring'    => 0,
            'last_recurring_date' => null,
        ]);
    }

    public function do_milestones_kanban_query($milestone_id, $booking_id, $page = 1, $where = [], $count = false)
    {
        $where['milestone'] = $milestone_id;
        $limit              = get_option('tours_kanban_limit');
        $tours              = $this->get_tours($booking_id, $where, true, $count, function () use ($count, $page, $limit) {
            if ($count == false) {
                if ($page > 1) {
                    $position = (($page - 1) * $limit);
                    $this->db->limit($limit, $position);
                } else {
                    $this->db->limit($limit);
                }
            }
        });

        return $tours;
    }

    public function get_files($booking_id)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('booking_id', $booking_id);

        return $this->db->get(db_prefix() . 'booking_files')->result_array();
    }

    public function get_file($id, $booking_id = false)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'booking_files')->row();

        if ($file && $booking_id) {
            if ($file->booking_id != $booking_id) {
                return false;
            }
        }

        return $file;
    }

    public function update_file_data($data)
    {
        $this->db->where('id', $data['id']);
        unset($data['id']);
        $this->db->update(db_prefix() . 'booking_files', $data);
    }

    public function change_file_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'booking_files', [
            'visible_to_customer' => $visible,
        ]);
    }

    public function change_activity_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'booking_activity', [
            'visible_to_customer' => $visible,
        ]);
    }

    public function remove_file($id, $logActivity = true)
    {
        hooks()->do_action('before_remove_booking_file', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'booking_files')->row();
        if ($file) {
            if (empty($file->external)) {
                $path     = get_upload_path_by_type('booking') . $file->booking_id . '/';
                $fullPath = $path . $file->file_name;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path . $fname . '_thumb.' . $fext;

                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
            }

            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'booking_files');
            if ($logActivity) {
                $this->log_activity($file->booking_id, 'booking_activity_booking_file_removed', $file->file_name, $file->visible_to_customer);
            }

            // Delete discussion comments
            $this->_delete_discussion_comments($id, 'file');

            if (is_dir(get_upload_path_by_type('booking') . $file->booking_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('booking') . $file->booking_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('booking') . $file->booking_id);
                }
            }

            return true;
        }

        return false;
    }

    public function get_booking_overview_weekly_chart_data($id, $type = 'this_week')
    {
        $billing_type = get_booking_billing_type($id);
        $chart        = [];

        $has_permission_create = has_permission('bookings', '', 'create');
        // If don't have permission for bookings create show only bileld time
        if (!$has_permission_create) {
            $timesheets_type = 'total_logged_time_only';
        } else {
            if ($billing_type == 2 || $billing_type == 3) {
                $timesheets_type = 'billable_unbilled';
            } else {
                $timesheets_type = 'total_logged_time_only';
            }
        }

        $chart['data']             = [];
        $chart['data']['labels']   = [];
        $chart['data']['datasets'] = [];

        $chart['data']['datasets'][] = [
            'label'           => ($timesheets_type == 'billable_unbilled' ? str_replace(':', '', _l('booking_overview_billable_hours')) : str_replace(':', '', _l('booking_overview_logged_hours'))),
            'data'            => [],
            'backgroundColor' => [],
            'borderColor'     => [],
            'borderWidth'     => 1,
        ];

        if ($timesheets_type == 'billable_unbilled') {
            $chart['data']['datasets'][] = [
                'label'           => str_replace(':', '', _l('booking_overview_unbilled_hours')),
                'data'            => [],
                'backgroundColor' => [],
                'borderColor'     => [],
                'borderWidth'     => 1,
            ];
        }

        $temp_weekdays_data = [];
        $weeks              = [];
        $where_time         = '';

        if ($type == 'this_month') {
            $beginThisMonth = date('Y-m-01');
            $endThisMonth   = date('Y-m-t 23:59:59');

            $weeks_split_start = date('Y-m-d', strtotime($beginThisMonth));
            $weeks_split_end   = date('Y-m-d', strtotime($endThisMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginThisMonth) . ' AND ' . strtotime($endThisMonth);
        } elseif ($type == 'last_month') {
            $beginLastMonth = date('Y-m-01', strtotime('-1 MONTH'));
            $endLastMonth   = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));

            $weeks_split_start = date('Y-m-d', strtotime($beginLastMonth));
            $weeks_split_end   = date('Y-m-d', strtotime($endLastMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginLastMonth) . ' AND ' . strtotime($endLastMonth);
        } elseif ($type == 'last_week') {
            $beginLastWeek = date('Y-m-d', strtotime('monday last week'));
            $endLastWeek   = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            $where_time    = 'start_time BETWEEN ' . strtotime($beginLastWeek) . ' AND ' . strtotime($endLastWeek);
        } else {
            $beginThisWeek = date('Y-m-d', strtotime('monday this week'));
            $endThisWeek   = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            $where_time    = 'start_time BETWEEN ' . strtotime($beginThisWeek) . ' AND ' . strtotime($endThisWeek);
        }

        if ($type == 'this_week' || $type == 'last_week') {
            foreach (get_weekdays() as $day) {
                array_push($chart['data']['labels'], $day);
            }
            $weekDay = date('w', strtotime(date('Y-m-d H:i:s')));
            $i       = 0;
            foreach (get_weekdays_original() as $day) {
                if ($weekDay != '0') {
                    $chart['data']['labels'][$i] = date('d', strtotime($day . ' ' . str_replace('_', ' ', $type))) . ' - ' . $chart['data']['labels'][$i];
                } else {
                    if ($type == 'this_week') {
                        $strtotime = 'last ' . $day;
                        if ($day == 'Sunday') {
                            $strtotime = 'sunday this week';
                        }
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)) . ' - ' . $chart['data']['labels'][$i];
                    } else {
                        $strtotime                   = $day . ' last week';
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)) . ' - ' . $chart['data']['labels'][$i];
                    }
                }
                $i++;
            }
        } elseif ($type == 'this_month' || $type == 'last_month') {
            $weeks_split_start = new DateTime($weeks_split_start);
            $weeks_split_end   = new DateTime($weeks_split_end);
            $weeks             = get_weekdays_between_dates($weeks_split_start, $weeks_split_end);
            $total_weeks       = count($weeks);
            for ($i = 1; $i <= $total_weeks; $i++) {
                array_push($chart['data']['labels'], split_weeks_chart_label($weeks, $i));
            }
        }

        $loop_break = ($timesheets_type == 'billable_unbilled') ? 2 : 1;

        for ($i = 0; $i < $loop_break; $i++) {
            $temp_weekdays_data = [];
            // Store the weeks in new variable for each loop to prevent duplicating
            $tmp_weeks = $weeks;


            $color = '3, 169, 244';

            $where = 'tour_id IN (SELECT id FROM ' . db_prefix() . 'tours WHERE rel_type = "booking" AND rel_id = "' . $this->db->escape_str($id) . '"';

            if ($timesheets_type != 'total_logged_time_only') {
                $where .= ' AND billable=1';
                if ($i == 1) {
                    $color = '252, 45, 66';
                    $where .= ' AND billed = 0';
                }
            }

            $where .= ')';
            $this->db->where($where_time);
            $this->db->where($where);
            if (!$has_permission_create) {
                $this->db->where('staff_id', get_staff_user_id());
            }
            $timesheets = $this->db->get(db_prefix() . 'tourstimers')->result_array();

            foreach ($timesheets as $t) {
                $total_logged_time = 0;
                if ($t['end_time'] == null) {
                    $total_logged_time = time() - $t['start_time'];
                } else {
                    $total_logged_time = $t['end_time'] - $t['start_time'];
                }

                if ($type == 'this_week' || $type == 'last_week') {
                    $weekday = date('N', $t['start_time']);
                    if (!isset($temp_weekdays_data[$weekday])) {
                        $temp_weekdays_data[$weekday] = 0;
                    }
                    $temp_weekdays_data[$weekday] += $total_logged_time;
                } else {
                    // months - this and last
                    $w = 1;
                    foreach ($tmp_weeks as $week) {
                        $start_time_date = strftime('%Y-%m-%d', $t['start_time']);
                        if (!isset($tmp_weeks[$w]['total'])) {
                            $tmp_weeks[$w]['total'] = 0;
                        }
                        if (in_array($start_time_date, $week)) {
                            $tmp_weeks[$w]['total'] += $total_logged_time;
                        }
                        $w++;
                    }
                }
            }

            if ($type == 'this_week' || $type == 'last_week') {
                ksort($temp_weekdays_data);
                for ($w = 1; $w <= 7; $w++) {
                    $total_logged_time = 0;
                    if (isset($temp_weekdays_data[$w])) {
                        $total_logged_time = $temp_weekdays_data[$w];
                    }
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba(' . $color . ',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba(' . $color . ',1)');
                }
            } else {
                // loop over $tmp_weeks because the unbilled is shown twice because we auto increment twice
                // months - this and last
                foreach ($tmp_weeks as $week) {
                    $total = 0;
                    if (isset($week['total'])) {
                        $total = $week['total'];
                    }
                    $total_logged_time = $total;
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba(' . $color . ',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba(' . $color . ',1)');
                }
            }
        }

        return $chart;
    }

    public function get_gantt_data($booking_id, $type = 'milestones', $tourStatus = null)
    {
        $booking_data = $this->get($booking_id);
        $type_data    = [];
        if ($type == 'milestones') {
            $type_data[] = [
                'name'   => _l('milestones_uncategorized'),
                'dep_id' => 'milestone_0',
                'id'     => 0,
            ];
            $_milestones = $this->get_milestones($booking_id);
            foreach ($_milestones as $m) {
                $m['dep_id']       = 'milestone_' . $m['id'];
                $m['milestone_id'] = $m['id'];
                $type_data[]       = $m;
            }
        } elseif ($type == 'members') {
            $type_data[] = [
                'name'     => _l('tour_list_not_assigned'),
                'dep_id'   => 'member_0' ,
                'staff_id' => 0,
            ];
            $_members = $this->get_booking_members($booking_id);
            foreach ($_members as $m) {
                $m['dep_id'] = 'member_' . $m['staff_id'];
                $m['name']   = get_staff_full_name($m['staff_id']);
                $type_data[] = $m;
            }
        } else {
            if (!$tourStatus) {
                $statuses = $this->tours_model->get_statuses();
                foreach ($statuses as $status) {
                    $status['dep_id'] = 'status_' . $status['id'];
                    $status['name']   = format_tour_status($status['id'], false, true);
                    $type_data[]      = $status;
                }
            } else {
                $status['id']     = $tourStatus;
                $status['dep_id'] = 'status_' . $tourStatus;
                $status['name']   = format_tour_status($tourStatus, false, true);
                $type_data[]      = $status;
            }
        }

        $gantt_data     = [];
        $has_permission = has_permission('tours', '', 'view');
        foreach ($type_data as $data) {
            if ($type == 'milestones') {
                $tours = $this->get_tours($booking_id, 'milestone=' . $this->db->escape_str($data['id']) . ($tourStatus ? ' AND ' . db_prefix() . 'tours.status=' . $this->db->escape_str($tourStatus) : ''), true);
                if (isset($data['due_date'])) {
                    $data['end'] = $data['due_date'];
                }
                unset($data['description']);
            } elseif ($type == 'members') {
                if ($data['staff_id'] != 0) {
                    $tours = $this->get_tours($booking_id, db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . $data['staff_id'] . ')' . ($tourStatus ? ' AND ' . db_prefix() . 'tours.status=' . $tourStatus : ''), true);
                } else {
                    $tours = $this->get_tours($booking_id, db_prefix() . 'tours.id NOT IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned)' . ($tourStatus ? ' AND ' . db_prefix() . 'tours.status=' . $tourStatus : ''), true);
                }
            } else {
                $tours = $this->get_tours($booking_id, [
                    'status' => $data['id'],
                ], true);
            }

            if (count($tours) > 0) {
                $data['id']           = $data['dep_id'];
                $data['start']        = $booking_data->start_date;
                $data['end']          = (isset($data['end'])) ? $data['end'] : $booking_data->deadline;
                $data['custom_class'] = 'noDrag';
                unset($data['dep_id']);
                $gantt_data[] = $data;

                foreach ($tours as $tour) {
                    $gantt_data[] = get_tour_array_gantt_data($tour, $data['id']);
                }
            }
        }

        return $gantt_data;
    }

    public function get_all_bookings_gantt_data($filters = [])
    {
        $statuses   = $this->get_booking_statuses();
        $gantt_data = [];

        $statusesIds = [];
        foreach ($statuses as $status) {
            if (!in_array($status['id'], $filters['status'])) {
                continue;
            }

            if (!has_permission('bookings', '', 'view')) {
                $this->db->where(db_prefix() . 'bookings.id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . ')');
            }

            if ($filters['member']) {
                $this->db->where(db_prefix() . 'bookings.id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . $this->db->escape_str($filters['member']) . ')');
            }

            $this->db->where('status', $status['id']);
            $this->db->order_by('deadline IS NULL ASC, deadline', '', false);
            $bookings = $this->db->get(db_prefix() . 'bookings')->result_array();

            foreach ($bookings as $booking) {
                $tours = $this->get_tours($booking['id'], [], true);

                $data               = [];
                $data['id']         = 'proj_' . $booking['id'];
                $data['booking_id'] = $booking['id'];
                $data['name']       = $booking['name'];
                $data['progress']   = 0;
                $data['start']      = strftime('%Y-%m-%d', strtotime($booking['start_date']));

                if (!empty($booking['deadline'])) {
                    $data['end'] = strftime('%Y-%m-%d', strtotime($booking['deadline']));
                }

                $data['custom_class'] = 'noDrag';
                $gantt_data[]         = $data;

                if (count($tours) > 0) {
                    foreach ($tours as $tour) {
                        $tour_data                 = get_tour_array_gantt_data($tour, null, isset($data['end']) ? $data['end'] : null);
                        $tour_data['progress']     = 0;
                        $tour_data['dependencies'] = $data['id'];
                        $gantt_data[]              = $tour_data;
                    }
                }
            }
        }

        return $gantt_data;
    }

    public function calc_milestone_logged_time($booking_id, $id)
    {
        $total = [];
        $tours = $this->get_tours($booking_id, [
            'milestone' => $id,
        ]);

        foreach ($tours as $tour) {
            $total[] = $tour['total_logged_time'];
        }

        return array_sum($total);
    }

    public function total_logged_time($id)
    {
        $q = $this->db->query('
            SELECT SUM(CASE
                WHEN end_time is NULL THEN ' . time() . '-start_time
                ELSE end_time-start_time
                END) as total_logged_time
            FROM ' . db_prefix() . 'tourstimers
            WHERE tour_id IN (SELECT id FROM ' . db_prefix() . 'tours WHERE rel_type="booking" AND rel_id=' . $this->db->escape_str($id) . ')')
            ->row();

        return $q->total_logged_time;
    }

    public function get_milestones($booking_id)
    {
        $this->db->select('*, (SELECT COUNT(id) FROM ' . db_prefix() . 'tours WHERE rel_type="booking" AND rel_id=' . $this->db->escape_str($booking_id) . ' and milestone=' . db_prefix() . 'milestones.id) as total_tours, (SELECT COUNT(id) FROM ' . db_prefix() . 'tours WHERE rel_type="booking" AND rel_id=' . $this->db->escape_str($booking_id) . ' and milestone=' . db_prefix() . 'milestones.id AND status=5) as total_finished_tours');
        $this->db->where('booking_id', $booking_id);
        $this->db->order_by('milestone_order', 'ASC');
        $milestones = $this->db->get(db_prefix() . 'milestones')->result_array();
        $i          = 0;
        foreach ($milestones as $milestone) {
            $milestones[$i]['total_logged_time'] = $this->calc_milestone_logged_time($booking_id, $milestone['id']);
            $i++;
        }


        return $milestones;
    }

    public function add_milestone($data)
    {
        $data['due_date']    = to_sql_date($data['due_date']);
        $data['datecreated'] = date('Y-m-d');
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }
        $this->db->insert(db_prefix() . 'milestones', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $insert_id);
            $milestone = $this->db->get(db_prefix() . 'milestones')->row();
            $booking   = $this->get($milestone->booking_id);
            if ($booking->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->booking_id, 'booking_activity_created_milestone', $milestone->name, $show_to_customer);
            log_activity('Booking Milestone Created [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_milestone($data, $id)
    {
        $this->db->where('id', $id);
        $milestone           = $this->db->get(db_prefix() . 'milestones')->row();
        $data['due_date']    = to_sql_date($data['due_date']);
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'milestones', $data);
        if ($this->db->affected_rows() > 0) {
            $booking = $this->get($milestone->booking_id);
            if ($booking->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->booking_id, 'booking_activity_updated_milestone', $milestone->name, $show_to_customer);
            log_activity('Booking Milestone Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_tour_milestone($data)
    {
        $this->db->where('id', $data['tour_id']);
        $this->db->update(db_prefix() . 'tours', [
            'milestone' => $data['milestone_id'],
        ]);

        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update(db_prefix() . 'tours', [
                'milestone_order' => $order[1],
            ]);
        }
    }

    public function update_milestones_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update(db_prefix() . 'milestones', [
                'milestone_order' => $status[1],
            ]);
        }
    }

    public function update_milestone_color($data)
    {
        $this->db->where('id', $data['milestone_id']);
        $this->db->update(db_prefix() . 'milestones', [
            'color' => $data['color'],
        ]);
    }

    public function delete_milestone($id)
    {
        $this->db->where('id', $id);
        $milestone = $this->db->get(db_prefix() . 'milestones')->row();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'milestones');
        if ($this->db->affected_rows() > 0) {
            $booking = $this->get($milestone->booking_id);
            if ($booking->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->booking_id, 'booking_activity_deleted_milestone', $milestone->name, $show_to_customer);
            $this->db->where('milestone', $id);
            $this->db->update(db_prefix() . 'tours', [
                'milestone' => 0,
            ]);
            log_activity('Booking Milestone Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function add($data)
    {
        if (isset($data['notify_booking_members_status_change'])) {
            unset($data['notify_booking_members_status_change']);
        }
        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_booking_marked_as_finished_email_to_contacts = false;
        if (isset($data['booking_marked_as_finished_email_to_contacts'])) {
            unset($data['booking_marked_as_finished_email_to_contacts']);
            $send_booking_marked_as_finished_email_to_contacts = true;
        }

        if (isset($data['settings'])) {
            $booking_settings = $data['settings'];
            unset($data['settings']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['progress_from_tours'])) {
            $data['progress_from_tours'] = 1;
        } else {
            $data['progress_from_tours'] = 0;
        }


        $data['booking_cost']    = !empty($data['booking_cost']) ? $data['booking_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;

        $data['start_date'] = to_sql_date($data['start_date']);

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            unset($data['deadline']);
        }

        $data['booking_created'] = date('Y-m-d');
        if (isset($data['booking_members'])) {
            $booking_members = $data['booking_members'];
            unset($data['booking_members']);
        }
        if ($data['billing_type'] == 1) {
            $data['booking_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['booking_cost'] = 0;
        } else {
            $data['booking_rate_per_hour'] = 0;
            $data['booking_cost']          = 0;
        }

        $data['addedfrom'] = get_staff_user_id();

        $data = hooks()->apply_filters('before_add_booking', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        $this->db->insert(db_prefix() . 'bookings', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            handle_tags_save($tags, $insert_id, 'booking');

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            if (isset($booking_members)) {
                $_pm['booking_members'] = $booking_members;
                $this->add_edit_members($_pm, $insert_id);
            }

            $original_settings = $this->get_settings();
            if (isset($booking_settings)) {
                $_settings = [];
                $_values   = [];
                foreach ($booking_settings as $name => $val) {
                    array_push($_settings, $name);
                    $_values[$name] = $val;
                }
                foreach ($original_settings as $setting) {
                    if ($setting != 'available_features') {
                        if (in_array($setting, $_settings)) {
                            $value_setting = 1;
                        } else {
                            $value_setting = 0;
                        }
                    } else {
                        $tabs         = get_booking_tabs_admin();
                        $tab_settings = [];
                        foreach ($_values[$setting] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['collapse'])) {
                                if (!in_array($tab['slug'], $_values[$setting])) {
                                    $tab_settings[$tab['slug']] = 0;
                                }
                            } else {
                                foreach ($tab['children'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['slug'], $_values[$setting])) {
                                        $tab_settings[$tab_dropdown['slug']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                    $this->db->insert(db_prefix() . 'booking_settings', [
                        'booking_id' => $insert_id,
                        'name'       => $setting,
                        'value'      => $value_setting,
                    ]);
                }
            } else {
                foreach ($original_settings as $setting) {
                    $value_setting = 0;
                    $this->db->insert(db_prefix() . 'booking_settings', [
                        'booking_id' => $insert_id,
                        'name'       => $setting,
                        'value'      => $value_setting,
                    ]);
                }
            }

            $this->log_activity($insert_id, 'booking_activity_created');

            if ($send_created_email == true) {
                $this->send_booking_customer_email($insert_id, 'booking_created_to_customer');
            }

            if ($send_booking_marked_as_finished_email_to_contacts == true) {
                $this->send_booking_customer_email($insert_id, 'booking_marked_as_finished_to_customer');
            }

            hooks()->do_action('after_add_booking', $insert_id);

            log_activity('New Booking Created [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update($data, $id)
    {
        $this->db->select('status');
        $this->db->where('id', $id);
        $old_status = $this->db->get(db_prefix() . 'bookings')->row()->status;

        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_booking_marked_as_finished_email_to_contacts = false;
        if (isset($data['booking_marked_as_finished_email_to_contacts'])) {
            unset($data['booking_marked_as_finished_email_to_contacts']);
            $send_booking_marked_as_finished_email_to_contacts = true;
        }

        $original_booking = $this->get($id);

        if (isset($data['notify_booking_members_status_change'])) {
            $notify_booking_members_status_change = true;
            unset($data['notify_booking_members_status_change']);
        }
        $affectedRows = 0;
        if (!isset($data['settings'])) {
            $this->db->where('booking_id', $id);
            $this->db->update(db_prefix() . 'booking_settings', [
                'value' => 0,
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $_settings = [];
            $_values   = [];

            foreach ($data['settings'] as $name => $val) {
                array_push($_settings, $name);
                $_values[$name] = $val;
            }

            unset($data['settings']);
            $original_settings = $this->get_booking_settings($id);

            foreach ($original_settings as $setting) {
                if ($setting['name'] != 'available_features') {
                    if (in_array($setting['name'], $_settings)) {
                        $value_setting = 1;
                    } else {
                        $value_setting = 0;
                    }
                } else {
                    $tabs         = get_booking_tabs_admin();
                    $tab_settings = [];
                    if(is_array($_values[$setting['name']])){
                        foreach ($_values[$setting['name']] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['collapse'])) {
                                if (!in_array($tab['slug'], $_values[$setting['name']])) {
                                    $tab_settings[$tab['slug']] = 0;
                                }
                            } else {
                                foreach ($tab['children'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['slug'], $_values[$setting['name']])) {
                                        $tab_settings[$tab_dropdown['slug']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                }

                $this->db->where('booking_id', $id);
                $this->db->where('name', $setting['name']);
                if(isset($value_setting))$this->db->update(db_prefix() . 'booking_settings', [
                    'value' => $value_setting,
                ]);

                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        $data['booking_cost']    = !empty($data['booking_cost']) ? $data['booking_cost'] : null;
        $data['estimated_hours'] = !empty($data['estimated_hours']) ? $data['estimated_hours'] : null;

        if ($old_status == 4 && $data['status'] != 4) {
            $data['date_finished'] = null;
        } elseif (isset($data['date_finished'])) {
            $data['date_finished'] = to_sql_date($data['date_finished'], true);
        }

        if (isset($data['progress_from_tours'])) {
            $data['progress_from_tours'] = 1;
        } else {
            $data['progress_from_tours'] = 0;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $data['deadline'] = null;
        }

        $data['start_date'] = to_sql_date($data['start_date']);
        if ($data['billing_type'] == 1) {
            $data['booking_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['booking_cost'] = 0;
        } else {
            $data['booking_rate_per_hour'] = 0;
            $data['booking_cost']          = 0;
        }
        if (isset($data['booking_members'])) {
            $booking_members = $data['booking_members'];
            unset($data['booking_members']);
        }
        $_pm = [];
        if (isset($booking_members)) {
            $_pm['booking_members'] = $booking_members;
        }
        if ($this->add_edit_members($_pm, $id)) {
            $affectedRows++;
        }
        if (isset($data['mark_all_tours_as_completed'])) {
            $mark_all_tours_as_completed = true;
            unset($data['mark_all_tours_as_completed']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'booking')) {
                $tags = explode(",", $data['tags']);
                foreach($tags as $tag){
                    if($tag == TOUR_COMPLETED){
                        $data['status'] = 6;
                        $this->send_booking_customer_email($id, 'booking_marked_as_finished_to_customer');
                    }
                    if($tag == 'BOND RECEIVED') $data['status'] = 2;
                    if($tag == 'POSTPONED') $data['status'] = 3;
                    if($tag == 'PAID IN FULL') $data['status'] = 4;
                    if($tag == 'CANCELLED') $data['status'] = 5;
                }
                $affectedRows++;
            }
            unset($data['tags']);
        }

        if (isset($data['cancel_recurring_tours'])) {
            unset($data['cancel_recurring_tours']);
            $this->cancel_recurring_tours($id);
        }

        $data = hooks()->apply_filters('before_update_booking', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'bookings', $data);

        if ($this->db->affected_rows() > 0) {
            if (isset($mark_all_tours_as_completed)) {
                $this->_mark_all_booking_tours_as_completed($id);
            }
            $affectedRows++;
        }

        if ($send_created_email == true) {
            if ($this->send_booking_customer_email($id, 'booking_created_to_customer')) {
                $affectedRows++;
            }
        }

        if ($send_booking_marked_as_finished_email_to_contacts == true) {
            if ($this->send_booking_customer_email($id, 'booking_marked_as_finished_to_customer')) {
                $affectedRows++;
            }
        }
        if ($affectedRows > 0) {
            $this->log_activity($id, 'booking_activity_updated');
            log_activity('Booking Updated [ID: ' . $id . ']');

            if ($original_booking->status != $data['status']) {
                hooks()->do_action('booking_status_changed', [
                    'status'     => $data['status'],
                    'booking_id' => $id,
                ]);
                // Give space this log to be on top
                sleep(1);
                if ($data['status'] == 6) {
                    $this->log_activity($id, 'booking_marked_as_finished');
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'bookings', ['date_finished' => date('Y-m-d H:i:s')]);
                } else {
                    $this->log_activity($id, 'booking_status_updated', '<b><lang>booking_status_' . $data['status'] . '</lang></b>');
                }

                if (isset($notify_booking_members_status_change)) {
                    $this->_notify_booking_members_status_change($id, $original_booking->status, $data['status']);
                }
            }
            hooks()->do_action('after_update_booking', $id);

            return true;
        }

        return false;
    }

    /**
     * Simplified function to send non complicated email templates for booking contacts
     * @param  mixed $id booking id
     * @return boolean
     */
    public function send_booking_customer_email($id, $template)
    {
        $this->db->select('clientid');
        $this->db->where('id', $id);
        $clientid = $this->db->get(db_prefix() . 'bookings')->row()->clientid;

        $sent     = false;
        $contacts = $this->clients_model->get_contacts($clientid);
        foreach ($contacts as $contact) {
            if (send_mail_template($template, BOOKING_MODULE_NAME, $id, $clientid, $contact)) {
                $sent = true;
            }
        }

        return $sent;
    }

    public function mark_as($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['booking_id']);
        $old_status = $this->db->get(db_prefix() . 'bookings')->row()->status;

        $this->db->where('id', $data['booking_id']);
        $this->db->update(db_prefix() . 'bookings', [
            'status' => $data['status_id'],
        ]);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('booking_status_changed', [
                'status'     => $data['status_id'],
                'booking_id' => $data['booking_id'],
            ]);


            if ($data['status_id'] == 4) {
                $this->log_activity($data['booking_id'], 'booking_marked_as_finished');
                $this->db->where('id', $data['booking_id']);
                $this->db->update(db_prefix() . 'bookings', ['date_finished' => date('Y-m-d H:i:s')]);
            } else {
                $this->log_activity($data['booking_id'], 'booking_status_updated', '<b><lang>booking_status_' . $data['status_id'] . '</lang></b>');
                if ($old_status == 4) {
                    $this->db->update(db_prefix() . 'bookings', ['date_finished' => null]);
                }
            }

            if ($data['notify_booking_members_status_change'] == 1) {
                $this->_notify_booking_members_status_change($data['booking_id'], $old_status, $data['status_id']);
            }

            if ($data['mark_all_tours_as_completed'] == 1) {
                $this->_mark_all_booking_tours_as_completed($data['booking_id']);
            }

            if (isset($data['cancel_recurring_tours']) && $data['cancel_recurring_tours'] == 'true') {
                $this->cancel_recurring_tours($data['booking_id']);
            }

            if (
                isset($data['send_booking_marked_as_finished_email_to_contacts'])
                && $data['send_booking_marked_as_finished_email_to_contacts'] == 1
            ) {
                $this->send_booking_customer_email($data['booking_id'], 'booking_marked_as_finished_to_customer');
            }

            return true;
        }


        return false;
    }

    private function _notify_booking_members_status_change($id, $old_status, $new_status)
    {
        $members       = $this->get_booking_members($id);
        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] != get_staff_user_id()) {
                $notified = add_notification([
                    'fromuserid'      => get_staff_user_id(),
                    'description'     => 'not_booking_status_updated',
                    'link'            => 'booking/bookings/view/' . $id,
                    'touserid'        => $member['staff_id'],
                    'additional_data' => serialize([
                        '<lang>booking_status_' . $old_status . '</lang>',
                        '<lang>booking_status_' . $new_status . '</lang>',
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }

    private function _mark_all_booking_tours_as_completed($id)
    {
        $this->db->where('rel_type', 'booking');
        $this->db->where('rel_id', $id);
        $this->db->update(db_prefix() . 'tours', [
            'status'       => 5,
            'datefinished' => date('Y-m-d H:i:s'),
        ]);
        $tours = $this->get_tours($id);
        foreach ($tours as $tour) {
            $this->db->where('tour_id', $tour['id']);
            $this->db->where('end_time IS NULL');
            $this->db->update(db_prefix() . 'tourstimers', [
                'end_time' => time(),
            ]);
        }
        $this->log_activity($id, 'booking_activity_marked_all_tours_as_complete');
    }

    public function add_edit_members($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['booking_members'])) {
            $booking_members = $data['booking_members'];
        }

        $new_booking_members_to_receive_email = [];
        $this->db->select('name,clientid');
        $this->db->where('id', $id);
        $booking      = $this->db->get(db_prefix() . 'bookings')->row();
        $booking_name = $booking->name;
        $client_id    = $booking->clientid;

        $booking_members_in = $this->get_booking_members($id);
        if (sizeof($booking_members_in) > 0) {
            foreach ($booking_members_in as $booking_member) {
                if (isset($booking_members)) {
                    if (!in_array($booking_member['staff_id'], $booking_members)) {
                        $this->db->where('booking_id', $id);
                        $this->db->where('staff_id', $booking_member['staff_id']);
                        $this->db->delete(db_prefix() . 'booking_members');
                        if ($this->db->affected_rows() > 0) {
                            $this->db->where('staff_id', $booking_member['staff_id']);
                            $this->db->where('booking_id', $id);
                            $this->db->delete(db_prefix() . 'pinned_bookings');

                            $this->log_activity($id, 'booking_activity_removed_team_member', get_staff_full_name($booking_member['staff_id']));
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('booking_id', $id);
                    $this->db->delete(db_prefix() . 'booking_members');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($booking_members)) {
                $notifiedUsers = [];
                foreach ($booking_members as $staff_id) {
                    $this->db->where('booking_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get(db_prefix() . 'booking_members')->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert(db_prefix() . 'booking_members', [
                            'booking_id' => $id,
                            'staff_id'   => $staff_id,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            if ($staff_id != get_staff_user_id()) {
                                $notified = add_notification([
                                    'fromuserid'      => get_staff_user_id(),
                                    'description'     => 'not_staff_added_as_booking_member',
                                    'link'            => 'booking/bookings/view/' . $id,
                                    'touserid'        => $staff_id,
                                    'additional_data' => serialize([
                                        $booking_name,
                                    ]),
                                ]);
                                array_push($new_booking_members_to_receive_email, $staff_id);
                                if ($notified) {
                                    array_push($notifiedUsers, $staff_id);
                                }
                            }


                            $this->log_activity($id, 'booking_activity_added_team_member', get_staff_full_name($staff_id));
                            $affectedRows++;
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        } else {
            if (isset($booking_members)) {
                $notifiedUsers = [];
                foreach ($booking_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert(db_prefix() . 'booking_members', [
                        'booking_id' => $id,
                        'staff_id'   => $staff_id,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        if ($staff_id != get_staff_user_id()) {
                            $notified = add_notification([
                                'fromuserid'      => get_staff_user_id(),
                                'description'     => 'not_staff_added_as_booking_member',
                                'link'            => 'booking/bookings/view/' . $id,
                                'touserid'        => $staff_id,
                                'additional_data' => serialize([
                                    $booking_name,
                                ]),
                            ]);
                            array_push($new_booking_members_to_receive_email, $staff_id);
                            if ($notifiedUsers) {
                                array_push($notifiedUsers, $staff_id);
                            }
                        }
                        $this->log_activity($id, 'booking_activity_added_team_member', get_staff_full_name($staff_id));
                        $affectedRows++;
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        }

        if (count($new_booking_members_to_receive_email) > 0) {
            $all_members = $this->get_booking_members($id);
            foreach ($all_members as $data) {
                if (in_array($data['staff_id'], $new_booking_members_to_receive_email)) {
                    send_mail_template('booking_staff_added_as_member', BOOKING_MODULE_NAME, $data, $id, $client_id);
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function is_member($booking_id, $staff_id = '')
    {
        if (!is_numeric($staff_id)) {
            $staff_id = get_staff_user_id();
        }
        $member = total_rows(db_prefix() . 'booking_members', [
            'staff_id'   => $staff_id,
            'booking_id' => $booking_id,
        ]);
        if ($member > 0) {
            return true;
        }

        return false;
    }

    public function get_bookings_for_ticket($client_id)
    {
        return $this->get('', [
            'clientid' => $client_id,
        ]);
    }

    public function get_booking_settings($booking_id)
    {
        $this->db->where('booking_id', $booking_id);

        return $this->db->get(db_prefix() . 'booking_settings')->result_array();
    }

    public function get_booking_members($id)
    {
        $this->db->select('email,booking_id,staff_id');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'booking_members.staff_id');
        $this->db->where('booking_id', $id);

        return $this->db->get(db_prefix() . 'booking_members')->result_array();
    }

    public function remove_team_member($booking_id, $staff_id)
    {
        $this->db->where('booking_id', $booking_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete(db_prefix() . 'booking_members');
        if ($this->db->affected_rows() > 0) {

            // Remove member from tours where is assigned
            $this->db->where('staffid', $staff_id);
            $this->db->where('tourid IN (SELECT id FROM ' . db_prefix() . 'tours WHERE rel_type="booking" AND rel_id="' . $this->db->escape_str($booking_id) . '")');
            $this->db->delete(db_prefix() . 'tour_assigned');

            $this->log_activity($booking_id, 'booking_activity_removed_team_member', get_staff_full_name($staff_id));

            return true;
        }

        return false;
    }

    public function get_timesheets($booking_id, $tours_ids = [])
    {
        if (count($tours_ids) == 0) {
            $tours     = $this->get_tours($booking_id);
            $tours_ids = [];
            foreach ($tours as $tour) {
                array_push($tours_ids, $tour['id']);
            }
        }
        if (count($tours_ids) > 0) {
            $this->db->where('tour_id IN(' . implode(', ', $tours_ids) . ')');
            $timesheets = $this->db->get(db_prefix() . 'tourstimers')->result_array();
            $i          = 0;
            foreach ($timesheets as $t) {
                $tour                         = $this->tours_model->get($t['tour_id']);
                $timesheets[$i]['tour_data']  = $tour;
                $timesheets[$i]['staff_name'] = get_staff_full_name($t['staff_id']);
                if (!is_null($t['end_time'])) {
                    $timesheets[$i]['total_spent'] = $t['end_time'] - $t['start_time'];
                } else {
                    $timesheets[$i]['total_spent'] = time() - $t['start_time'];
                }
                $i++;
            }

            return $timesheets;
        }

        return [];
    }

    public function get_discussion($id, $booking_id = '')
    {
        if ($booking_id != '') {
            $this->db->where('booking_id', $booking_id);
        }
        $this->db->where('id', $id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
            $this->db->where('booking_id IN (SELECT id FROM ' . db_prefix() . 'bookings WHERE clientid=' . get_client_user_id() . ')');
        }
        $discussion = $this->db->get(db_prefix() . 'bookingdiscussions')->row();
        if ($discussion) {
            return $discussion;
        }

        return false;
    }

    public function get_discussion_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'bookingdiscussioncomments')->row();
        if ($comment->contact_id != 0) {
            if (is_client_logged_in()) {
                if ($comment->contact_id == get_contact_user_id()) {
                    $comment->created_by_current_user = true;
                } else {
                    $comment->created_by_current_user = false;
                }
            } else {
                $comment->created_by_current_user = false;
            }
            $comment->profile_picture_url = contact_profile_image_url($comment->contact_id);
        } else {
            if (is_client_logged_in()) {
                $comment->created_by_current_user = false;
            } else {
                if (is_staff_logged_in()) {
                    if ($comment->staff_id == get_staff_user_id()) {
                        $comment->created_by_current_user = true;
                    } else {
                        $comment->created_by_current_user = false;
                    }
                } else {
                    $comment->created_by_current_user = false;
                }
            }
            if (is_admin($comment->staff_id)) {
                $comment->created_by_admin = true;
            } else {
                $comment->created_by_admin = false;
            }
            $comment->profile_picture_url = staff_profile_image_url($comment->staff_id);
        }
        $comment->created = (strtotime($comment->created) * 1000);
        if (!empty($comment->modified)) {
            $comment->modified = (strtotime($comment->modified) * 1000);
        }
        if (!is_null($comment->file_name)) {
            $comment->file_url = site_url('uploads/discussions/' . $comment->discussion_id . '/' . $comment->file_name);
        }

        return $comment;
    }

    public function get_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments             = $this->db->get(db_prefix() . 'bookingdiscussioncomments')->result_array();
        $i                    = 0;
        $allCommentsIDS       = [];
        $allCommentsParentIDS = [];
        foreach ($comments as $comment) {
            $allCommentsIDS[] = $comment['id'];
            if (!empty($comment['parent'])) {
                $allCommentsParentIDS[] = $comment['parent'];
            }

            if ($comment['contact_id'] != 0) {
                if (is_client_logged_in()) {
                    if ($comment['contact_id'] == get_contact_user_id()) {
                        $comments[$i]['created_by_current_user'] = true;
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                } else {
                    $comments[$i]['created_by_current_user'] = false;
                }
                $comments[$i]['profile_picture_url'] = contact_profile_image_url($comment['contact_id']);
            } else {
                if (is_client_logged_in()) {
                    $comments[$i]['created_by_current_user'] = false;
                } else {
                    if (is_staff_logged_in()) {
                        if ($comment['staff_id'] == get_staff_user_id()) {
                            $comments[$i]['created_by_current_user'] = true;
                        } else {
                            $comments[$i]['created_by_current_user'] = false;
                        }
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                }
                if (is_admin($comment['staff_id'])) {
                    $comments[$i]['created_by_admin'] = true;
                } else {
                    $comments[$i]['created_by_admin'] = false;
                }
                $comments[$i]['profile_picture_url'] = staff_profile_image_url($comment['staff_id']);
            }
            if (!is_null($comment['file_name'])) {
                $comments[$i]['file_url'] = site_url('uploads/discussions/' . $id . '/' . $comment['file_name']);
            }
            $comments[$i]['created'] = (strtotime($comment['created']) * 1000);
            if (!empty($comment['modified'])) {
                $comments[$i]['modified'] = (strtotime($comment['modified']) * 1000);
            }
            $i++;
        }

        // Ticket #5471
        foreach ($allCommentsParentIDS as $parent_id) {
            if (!in_array($parent_id, $allCommentsIDS)) {
                foreach ($comments as $key => $comment) {
                    if ($comment['parent'] == $parent_id) {
                        $comments[$key]['parent'] = null;
                    }
                }
            }
        }

        return $comments;
    }

    public function get_discussions($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
        }
        $discussions = $this->db->get(db_prefix() . 'bookingdiscussions')->result_array();
        $i           = 0;
        foreach ($discussions as $discussion) {
            $discussions[$i]['total_comments'] = total_rows(db_prefix() . 'bookingdiscussioncomments', [
                'discussion_id'   => $discussion['id'],
                'discussion_type' => 'regular',
            ]);
            $i++;
        }

        return $discussions;
    }

    public function add_discussion_comment($data, $discussion_id, $type)
    {
        $discussion               = $this->get_discussion($discussion_id);
        $_data['discussion_id']   = $discussion_id;
        $_data['discussion_type'] = $type;
        if (isset($data['content'])) {
            $_data['content'] = $data['content'];
        }
        if (isset($data['parent']) && $data['parent'] != null) {
            $_data['parent'] = $data['parent'];
        }
        if (is_client_logged_in()) {
            $_data['contact_id'] = get_contact_user_id();
            $_data['fullname']   = get_contact_full_name($_data['contact_id']);
            $_data['staff_id']   = 0;
        } else {
            $_data['contact_id'] = 0;
            $_data['staff_id']   = get_staff_user_id();
            $_data['fullname']   = get_staff_full_name($_data['staff_id']);
        }
        $_data = handle_booking_discussion_comment_attachments($discussion_id, $data, $_data);

        $_data['created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bookingdiscussioncomments', $_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($type == 'regular') {
                $discussion = $this->get_discussion($discussion_id);
                $not_link   = 'booking/bookings/view/' . $discussion->booking_id . '?group=booking_discussions&discussion_id=' . $discussion_id;
            } else {
                $discussion                   = $this->get_file($discussion_id);
                $not_link                     = 'booking/bookings/view/' . $discussion->booking_id . '?group=booking_files&file_id=' . $discussion_id;
                $discussion->show_to_customer = $discussion->visible_to_customer;
            }

            $emailTemplateData = [
                'staff' => [
                    'discussion_id'         => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type'       => $type,
                ],
                'customers' => [
                    'customer_template'     => true,
                    'discussion_id'         => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type'       => $type,
                ],
            ];

            if (isset($_data['file_name'])) {
                $emailTemplateData['attachments'] = [
                    [
                        'attachment' => BOOKING_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id . '/' . $_data['file_name'],
                        'filename'   => $_data['file_name'],
                        'type'       => $_data['file_mime_type'],
                        'read'       => true,
                    ],
                ];
            }

            $this->send_booking_email_template($discussion->booking_id, 'booking_new_discussion_comment_to_staff', 'booking_new_discussion_comment_to_customer', $discussion->show_to_customer, $emailTemplateData);


            $this->log_activity($discussion->booking_id, 'booking_activity_commented_on_discussion', $discussion->subject, $discussion->show_to_customer);

            $notification_data = [
                'description' => 'not_commented_on_booking_discussion',
                'link'        => $not_link,
            ];

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $members       = $this->get_booking_members($discussion->booking_id);
            $notifiedUsers = [];
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);

            $this->_update_discussion_last_activity($discussion_id, $type);

            return $this->get_discussion_comment($insert_id);
        }

        return false;
    }

    public function update_discussion_comment($data)
    {
        $comment = $this->get_discussion_comment($data['id']);
        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix() . 'bookingdiscussioncomments', [
            'modified' => date('Y-m-d H:i:s'),
            'content'  => $data['content'],
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return $this->get_discussion_comment($data['id']);
    }

    public function delete_discussion_comment($id, $logActivity = true)
    {
        $comment = $this->get_discussion_comment($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'bookingdiscussioncomments');
        if ($this->db->affected_rows() > 0) {
            $this->delete_discussion_comment_attachment($comment->file_name, $comment->discussion_id);
            if ($logActivity) {
                $additional_data = '';
                if ($comment->discussion_type == 'regular') {
                    $discussion = $this->get_discussion($comment->discussion_id);
                    $not        = 'booking_activity_deleted_discussion_comment';
                    $additional_data .= $discussion->subject . '<br />' . $comment->content;
                } else {
                    $discussion = $this->get_file($comment->discussion_id);
                    $not        = 'booking_activity_deleted_file_discussion_comment';
                    $additional_data .= $discussion->subject . '<br />' . $comment->content;
                }

                if (!is_null($comment->file_name)) {
                    $additional_data .= $comment->file_name;
                }

                $this->log_activity($discussion->booking_id, $not, $additional_data);
            }
        }

        $this->db->where('parent', $id);
        $this->db->update(db_prefix() . 'bookingdiscussioncomments', [
            'parent' => null,
        ]);

        if ($this->db->affected_rows() > 0 && $logActivity) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return true;
    }

    public function delete_discussion_comment_attachment($file_name, $discussion_id)
    {
        $path = BOOKING_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id;
        if (!is_null($file_name)) {
            if (file_exists($path . '/' . $file_name)) {
                unlink($path . '/' . $file_name);
            }
        }
        if (is_dir($path)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files($path);
            if (count($other_attachments) == 0) {
                delete_dir($path);
            }
        }
    }

    public function add_discussion($data)
    {
        if (is_client_logged_in()) {
            $data['contact_id']       = get_contact_user_id();
            $data['staff_id']         = 0;
            $data['show_to_customer'] = 1;
        } else {
            $data['staff_id']   = get_staff_user_id();
            $data['contact_id'] = 0;
            if (isset($data['show_to_customer'])) {
                $data['show_to_customer'] = 1;
            } else {
                $data['show_to_customer'] = 0;
            }
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix() . 'bookingdiscussions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $members           = $this->get_booking_members($data['booking_id']);
            $notification_data = [
                'description' => 'not_created_new_booking_discussion',
                'link'        => 'booking/bookings/view/' . $data['booking_id'] . '?group=booking_discussions&discussion_id=' . $insert_id,
            ];

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $notifiedUsers = [];
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
            $this->send_booking_email_template($data['booking_id'], 'booking_discussion_created_to_staff', 'booking_discussion_created_to_customer', $data['show_to_customer'], [
                'staff' => [
                    'discussion_id'   => $insert_id,
                    'discussion_type' => 'regular',
                ],
                'customers' => [
                    'customer_template' => true,
                    'discussion_id'     => $insert_id,
                    'discussion_type'   => 'regular',
                ],
            ]);
            $this->log_activity($data['booking_id'], 'booking_activity_created_discussion', $data['subject'], $data['show_to_customer']);

            return $insert_id;
        }

        return false;
    }

    public function edit_discussion($data, $id)
    {
        $this->db->where('id', $id);
        if (isset($data['show_to_customer'])) {
            $data['show_to_customer'] = 1;
        } else {
            $data['show_to_customer'] = 0;
        }
        $data['description'] = nl2br($data['description']);
        $this->db->update(db_prefix() . 'bookingdiscussions', $data);
        if ($this->db->affected_rows() > 0) {
            $this->log_activity($data['booking_id'], 'booking_activity_updated_discussion', $data['subject'], $data['show_to_customer']);

            return true;
        }

        return false;
    }

    public function delete_discussion($id, $logActivity = true)
    {
        $discussion = $this->get_discussion($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'bookingdiscussions');
        if ($this->db->affected_rows() > 0) {
            if ($logActivity) {
                $this->log_activity($discussion->booking_id, 'booking_activity_deleted_discussion', $discussion->subject, $discussion->show_to_customer);
            }
            $this->_delete_discussion_comments($id, 'regular');

            return true;
        }

        return false;
    }

    public function copy($booking_id, $data)
    {
        $_new_data = [];
        $booking   = $this->get($booking_id);
        $settings  = $this->get_booking_settings($booking_id);
        $fields    = $this->db->list_fields(db_prefix() . 'bookings');

        foreach ($fields as $field) {
            if (isset($booking->$field)) {
                $_new_data[$field] = $booking->$field;
            }
        }

        unset($_new_data['id']);
        $_new_data['clientid'] = $data['clientid_copy_booking'];
        unset($_new_data['clientid_copy_booking']);

        $_new_data['start_date'] = to_sql_date($data['start_date']);

        if ($_new_data['start_date'] > date('Y-m-d')) {
            $_new_data['status'] = 1;
        } else {
            $_new_data['status'] = 2;
        }
        if ($data['deadline']) {
            $_new_data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $_new_data['deadline'] = null;
        }

        $_new_data['booking_created'] = date('Y-m-d H:i:s');
        $_new_data['addedfrom']       = get_staff_user_id();

        $_new_data['date_finished'] = null;

        $this->db->insert(db_prefix() . 'bookings', $_new_data);
        $id = $this->db->insert_id();
        if ($id) {
            $tags = get_tags_in($booking_id, 'booking');
            handle_tags_save($tags, $id, 'booking');

            foreach ($settings as $setting) {
                $this->db->insert(db_prefix() . 'booking_settings', [
                    'booking_id' => $id,
                    'name'       => $setting['name'],
                    'value'      => $setting['value'],
                ]);
            }

            $added_tours = [];
            $tours       = $this->get_tours($booking_id);

            if (isset($data['tours'])) {
                foreach ($tours as $tour) {
                    if (isset($data['tour_include_followers'])) {
                        $copy_tour_data['copy_tour_followers'] = 'true';
                    }

                    if (isset($data['tour_include_assignees'])) {
                        $copy_tour_data['copy_tour_assignees'] = 'true';
                    }

                    if (isset($data['tours_include_checklist_items'])) {
                        $copy_tour_data['copy_tour_checklist_items'] = 'true';
                    }

                    $copy_tour_data['copy_from'] = $tour['id'];

                    // For new tour start date, we will find the difference in days between
                    // the old booking start and and the old tour start date and then
                    // based on the new booking start date, we will add e.q. 15 days to be
                    // new tour start date to the tour
                    // e.q. old booking start date 2020-04-01, old tour start date 2020-04-15 and due date 2020-04-30
                    // copy booking and set start date 2020-06-01
                    // new tour start date will be 2020-06-15 and below due date 2020-06-30
                    $dStart    = new DateTime($booking->start_date);
                    $dEnd      = new DateTime($tour['startdate']);
                    $dDiff     = $dStart->diff($dEnd);
                    $startDate = new DateTime($_new_data['start_date']);
                    $startDate->modify('+' . $dDiff->days . ' DAY');
                    $newTourStartDate = $startDate->format('Y-m-d');

                    $merge = [
                        'rel_id'              => $id,
                        'rel_type'            => 'booking',
                        'last_recurring_date' => null,
                        'startdate'           => $newTourStartDate,
                        'status'              => $data['copy_booking_tour_status'],
                    ];

                    // Calculate the diff in days between the tour start and due date
                    // then add these days to the new tour start date to be used as this tour due date
                    if ($tour['duedate']) {
                        $dStart  = new DateTime($tour['startdate']);
                        $dEnd    = new DateTime($tour['duedate']);
                        $dDiff   = $dStart->diff($dEnd);
                        $dueDate = new DateTime($newTourStartDate);
                        $dueDate->modify('+' . $dDiff->days . ' DAY');
                        $merge['duedate'] = $dueDate->format('Y-m-d');
                    }

                    $tour_id = $this->tours_model->copy($copy_tour_data, $merge);

                    if ($tour_id) {
                        array_push($added_tours, $tour_id);
                    }
                }
            }

            if (isset($data['milestones'])) {
                $milestones        = $this->get_milestones($booking_id);
                $_added_milestones = [];
                foreach ($milestones as $milestone) {
                    $dCreated = new DateTime($milestone['datecreated']);
                    $dDuedate = new DateTime($milestone['due_date']);
                    $dDiff    = $dCreated->diff($dDuedate);
                    $due_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY'))));

                    $this->db->insert(db_prefix() . 'milestones', [
                        'name'                            => $milestone['name'],
                        'booking_id'                      => $id,
                        'milestone_order'                 => $milestone['milestone_order'],
                        'description_visible_to_customer' => $milestone['description_visible_to_customer'],
                        'description'                     => $milestone['description'],
                        'due_date'                        => $due_date,
                        'datecreated'                     => date('Y-m-d'),
                        'color'                           => $milestone['color'],
                    ]);

                    $milestone_id = $this->db->insert_id();

                    if ($milestone_id) {
                        $_added_milestone_data         = [];
                        $_added_milestone_data['id']   = $milestone_id;
                        $_added_milestone_data['name'] = $milestone['name'];
                        $_added_milestones[]           = $_added_milestone_data;
                    }
                }

                if (isset($data['tours'])) {
                    if (count($added_tours) > 0) {
                        // Original booking tours
                        foreach ($tours as $tour) {
                            if ($tour['milestone'] != 0) {
                                $this->db->where('id', $tour['milestone']);
                                $milestone = $this->db->get(db_prefix() . 'milestones')->row();

                                if ($milestone) {
                                    foreach ($_added_milestones as $added_milestone) {
                                        if ($milestone->name == $added_milestone['name']) {
                                            $this->db->where('id IN (' . implode(', ', $added_tours) . ')');
                                            $this->db->where('milestone', $tour['milestone']);
                                            $this->db->update(db_prefix() . 'tours', [
                                                'milestone' => $added_milestone['id'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // milestones not set
                if (count($added_tours)) {
                    foreach ($added_tours as $tour) {
                        $this->db->where('id', $tour['id']);
                        $this->db->update(db_prefix() . 'tours', [
                            'milestone' => 0,
                        ]);
                    }
                }
            }

            if (isset($data['members'])) {
                $members  = $this->get_booking_members($booking_id);
                $_members = [];

                foreach ($members as $member) {
                    array_push($_members, $member['staff_id']);
                }

                $this->add_edit_members([
                    'booking_members' => $_members,
                ], $id);
            }

            foreach (get_custom_fields('bookings') as $field) {
                $value = get_custom_field_value($booking_id, $field['id'], 'bookings', false);
                if ($value != '') {
                    $this->db->insert(db_prefix() . 'customfieldsvalues', [
                        'relid'   => $id,
                        'fieldid' => $field['id'],
                        'fieldto' => 'bookings',
                        'value'   => $value,
                    ]);
                }
            }

            $this->log_activity($id, 'booking_activity_created');
            log_activity('Booking Copied [ID: ' . $booking_id . ', NewID: ' . $id . ']');

            return $id;
        }

        return false;
    }

    public function get_staff_notes($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(db_prefix() . 'booking_notes')->row();
        if ($notes) {
            return $notes->content;
        }

        return '';
    }

    public function save_note($data, $booking_id)
    {
        // Check if the note exists for this booking;
        $this->db->where('booking_id', $booking_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get(db_prefix() . 'booking_notes')->row();
        if ($notes) {
            $this->db->where('id', $notes->id);
            $this->db->update(db_prefix() . 'booking_notes', [
                'content' => $data['content'],
            ]);
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        }
        $this->db->insert(db_prefix() . 'booking_notes', [
            'staff_id'   => get_staff_user_id(),
            'content'    => $data['content'],
            'booking_id' => $booking_id,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return true;
        }

        return false;


        return false;
    }

    public function delete($booking_id)
    {
        $booking_name = get_booking_name_by_id($booking_id);

        $this->db->where('id', $booking_id);
        $this->db->delete(db_prefix() . 'bookings');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'booking_members');

            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'booking_notes');

            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'milestones');

            // Delete the custom field values
            $this->db->where('relid', $booking_id);
            $this->db->where('fieldto', 'bookings');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('rel_id', $booking_id);
            $this->db->where('rel_type', 'booking');
            $this->db->delete(db_prefix() . 'taggables');


            $this->db->where('booking_id', $booking_id);
            $discussions = $this->db->get(db_prefix() . 'bookingdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $discussion_comments = $this->get_discussion_comments($discussion['id'], 'regular');
                foreach ($discussion_comments as $comment) {
                    $this->delete_discussion_comment_attachment($comment['file_name'], $discussion['id']);
                }
                $this->db->where('discussion_id', $discussion['id']);
                $this->db->delete(db_prefix() . 'bookingdiscussioncomments');
            }
            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'bookingdiscussions');

            $files = $this->get_files($booking_id);
            foreach ($files as $file) {
                $this->remove_file($file['id']);
            }

            $tours = $this->get_tours($booking_id);
            foreach ($tours as $tour) {
                $this->tours_model->delete_tour($tour['id'], false);
            }

            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'booking_settings');

            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'booking_activity');

            $this->db->where('booking_id', $booking_id);
            $this->db->update(db_prefix() . 'expenses', [
                'booking_id' => 0,
            ]);

            // $this->db->where('booking_id', $booking_id);
            // $this->db->update(db_prefix() . 'contracts', [
            //     'booking_id' => null,
            // ]);

            $this->db->where('booking_id', $booking_id);
            $this->db->update(db_prefix() . 'invoices', [
                'booking_id' => 0,
            ]);

            // $this->db->where('booking_id', $booking_id);
            // $this->db->update(db_prefix() . 'creditnotes', [
            //     'booking_id' => 0,
            // ]);

            // $this->db->where('booking_id', $booking_id);
            // $this->db->update(db_prefix() . 'estimates', [
            //     'booking_id' => 0,
            // ]);

            // $this->db->where('booking_id', $booking_id);
            // $this->db->update(db_prefix() . 'tickets', [
            //     'booking_id' => 0,
            // ]);

            $this->db->where('booking_id', $booking_id);
            $this->db->delete(db_prefix() . 'pinned_bookings');

            log_activity('Booking Deleted [ID: ' . $booking_id . ', Name: ' . $booking_name . ']');

            return true;
        }

        return false;
    }

    public function get_activity($id = '', $limit = '', $only_booking_members_activity = false)
    {
        if (!is_client_logged_in()) {
            $has_permission = has_permission('bookings', '', 'view');
            if (!$has_permission) {
                $this->db->where('booking_id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . ')');
            }
        }
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        if (is_numeric($id)) {
            $this->db->where('booking_id', $id);
        }
        if (is_numeric($limit)) {
            $this->db->limit($limit);
        }
        $this->db->order_by('dateadded', 'desc');
        $activities = $this->db->get(db_prefix() . 'booking_activity')->result_array();
        $i          = 0;
        foreach ($activities as $activity) {
            $seconds          = get_string_between($activity['additional_data'], '<seconds>', '</seconds>');
            $other_lang_keys  = get_string_between($activity['additional_data'], '<lang>', '</lang>');
            $_additional_data = $activity['additional_data'];

            if ($seconds != '') {
                $_additional_data = str_replace('<seconds>' . $seconds . '</seconds>', seconds_to_time_format($seconds), $_additional_data);
            }

            if ($other_lang_keys != '') {
                $_additional_data = str_replace('<lang>' . $other_lang_keys . '</lang>', _l($other_lang_keys), $_additional_data);
            }

            if (strpos($_additional_data, 'booking_status_') !== false) {
                $_additional_data = get_booking_status_by_id(strafter($_additional_data, 'booking_status_'));

                if (isset($_additional_data['name'])) {
                    $_additional_data = $_additional_data['name'];
                }
            }

            $activities[$i]['description']     = _l($activities[$i]['description_key']);
            $activities[$i]['additional_data'] = $_additional_data;
            $activities[$i]['booking_name']    = get_booking_name_by_id($activity['booking_id']);
            unset($activities[$i]['description_key']);
            $i++;
        }

        return $activities;
    }

    public function log_activity($booking_id, $description_key, $additional_data = '', $visible_to_customer = 1)
    {
        if (!DEFINED('CRON')) {
            if (is_client_logged_in()) {
                $data['contact_id'] = get_contact_user_id();
                $data['staff_id']   = 0;
                $data['fullname']   = get_contact_full_name(get_contact_user_id());
            } elseif (is_staff_logged_in()) {
                $data['contact_id'] = 0;
                $data['staff_id']   = get_staff_user_id();
                $data['fullname']   = get_staff_full_name(get_staff_user_id());
            }
        } else {
            $data['contact_id'] = 0;
            $data['staff_id']   = 0;
            $data['fullname']   = '[CRON]';
        }
        $data['description_key']     = $description_key;
        $data['additional_data']     = $additional_data;
        $data['visible_to_customer'] = $visible_to_customer;
        $data['booking_id']          = $booking_id;
        $data['dateadded']           = date('Y-m-d H:i:s');

        $data = hooks()->apply_filters('before_log_booking_activity', $data);

        $this->db->insert(db_prefix() . 'booking_activity', $data);
    }

    public function new_booking_file_notification($file_id, $booking_id)
    {
        $file = $this->get_file($file_id);

        $additional_data = $file->file_name;
        $this->log_activity($booking_id, 'booking_activity_uploaded_file', $additional_data, $file->visible_to_customer);

        $members           = $this->get_booking_members($booking_id);
        $notification_data = [
            'description' => 'not_booking_file_uploaded',
            'link'        => 'booking/bookings/view/' . $booking_id . '?group=booking_files&file_id=' . $file_id,
        ];

        if (is_client_logged_in()) {
            $notification_data['fromclientid'] = get_contact_user_id();
        } else {
            $notification_data['fromuserid'] = get_staff_user_id();
        }

        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                continue;
            }
            $notification_data['touserid'] = $member['staff_id'];
            if (add_notification($notification_data)) {
                array_push($notifiedUsers, $member['staff_id']);
            }
        }
        pusher_trigger_notification($notifiedUsers);

        $this->send_booking_email_template(
            $booking_id,
            'booking_file_to_staff',
            'booking_file_to_customer',
            $file->visible_to_customer,
            [
                'staff'     => ['discussion_id' => $file_id, 'discussion_type' => 'file'],
                'customers' => ['customer_template' => true, 'discussion_id' => $file_id, 'discussion_type' => 'file'],
            ]
        );
    }

    public function add_external_file($data)
    {
        $insert['dateadded']           = date('Y-m-d H:i:s');
        $insert['booking_id']          = $data['booking_id'];
        $insert['external']            = $data['external'];
        $insert['visible_to_customer'] = $data['visible_to_customer'];
        $insert['file_name']           = $data['files'][0]['name'];
        $insert['subject']             = $data['files'][0]['name'];
        $insert['external_link']       = $data['files'][0]['link'];

        $path_parts         = pathinfo($data['files'][0]['name']);
        $insert['filetype'] = get_mime_by_extension('.' . $path_parts['extension']);

        if (isset($data['files'][0]['thumbnailLink'])) {
            $insert['thumbnail_link'] = $data['files'][0]['thumbnailLink'];
        }

        if (isset($data['staffid'])) {
            $insert['staffid'] = $data['staffid'];
        } elseif (isset($data['contact_id'])) {
            $insert['contact_id'] = $data['contact_id'];
        }

        $this->db->insert(db_prefix() . 'booking_files', $insert);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->new_booking_file_notification($insert_id, $data['booking_id']);

            return $insert_id;
        }

        return false;
    }

    public function send_booking_email_template($booking_id, $staff_template, $customer_template, $action_visible_to_customer, $additional_data = [])
    {
        if (count($additional_data) == 0) {
            $additional_data['customers'] = [];
            $additional_data['staff']     = [];
        } elseif (count($additional_data) == 1) {
            if (!isset($additional_data['staff'])) {
                $additional_data['staff'] = [];
            } else {
                $additional_data['customers'] = [];
            }
        }

        $booking = $this->get($booking_id);
        $members = $this->get_booking_members($booking_id);

        foreach ($members as $member) {
            if (is_staff_logged_in() && $member['staff_id'] == get_staff_user_id()) {
                continue;
            }
            $mailTemplate = mail_template($staff_template, $booking, $member, $additional_data['staff']);
            if (isset($additional_data['attachments'])) {
                foreach ($additional_data['attachments'] as $attachment) {
                    $mailTemplate->add_attachment($attachment);
                }
            }
            $mailTemplate->send();
        }
        if ($action_visible_to_customer == 1) {
            $contacts = $this->clients_model->get_contacts($booking->clientid, ['active' => 1, 'booking_emails' => 1]);

            foreach ($contacts as $contact) {
                if (is_client_logged_in() && $contact['id'] == get_contact_user_id()) {
                    continue;
                }
                $mailTemplate = mail_template($customer_template, $booking, $contact, $additional_data['customers']);
                if (isset($additional_data['attachments'])) {
                    foreach ($additional_data['attachments'] as $attachment) {
                        $mailTemplate->add_attachment($attachment);
                    }
                }
                $mailTemplate->send();
            }
        }
    }

    private function _get_booking_billing_data($id)
    {
        $this->db->select('billing_type,booking_rate_per_hour');
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'bookings')->row();
    }

    public function total_logged_time_by_billing_type($id, $conditions = [])
    {
        $booking_data = $this->_get_booking_billing_data($id);
        $data         = [];
        if ($booking_data->billing_type == 2) {
            $seconds             = $this->total_logged_time($id);
            $data                = $this->bookings_model->calculate_total_by_booking_hourly_rate($seconds, $booking_data->booking_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        } elseif ($booking_data->billing_type == 3) {
            $data = $this->_get_data_total_logged_time($id);
        }

        return $data;
    }

    public function data_billable_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
        ]);
    }

    public function data_billed_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
            'billed'   => 1,
        ]);
    }

    public function data_unbilled_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
            'billed'   => 0,
        ]);
    }

    private function _delete_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get(db_prefix() . 'bookingdiscussioncomments')->result_array();
        foreach ($comments as $comment) {
            $this->delete_discussion_comment_attachment($comment['file_name'], $id);
        }
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $this->db->delete(db_prefix() . 'bookingdiscussioncomments');
    }

    private function _get_data_total_logged_time($id, $conditions = [])
    {
        $booking_data = $this->_get_booking_billing_data($id);
        $tours        = $this->get_tours($id, $conditions);

        if ($booking_data->billing_type == 3) {
            $data                = $this->calculate_total_by_tour_hourly_rate($tours);
            $data['logged_time'] = seconds_to_time_format($data['total_seconds']);
        } elseif ($booking_data->billing_type == 2) {
            $seconds = 0;
            foreach ($tours as $tour) {
                $seconds += $tour['total_logged_time'];
            }
            $data                = $this->calculate_total_by_booking_hourly_rate($seconds, $booking_data->booking_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        }

        return $data;
    }

    private function _update_discussion_last_activity($id, $type)
    {
        if ($type == 'file') {
            $table = db_prefix() . 'booking_files';
        } elseif ($type == 'regular') {
            $table = db_prefix() . 'bookingdiscussions';
        }
        $this->db->where('id', $id);
        $this->db->update($table, [
            'last_activity' => date('Y-m-d H:i:s'),
        ]);
    }
}
