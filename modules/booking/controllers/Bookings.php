<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bookings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('tours_model');
        $this->load->model('currencies_model');
        $this->load->helper('date');
    }

    public function index()
    {
        close_setup_menu();
        $data['statuses'] = $this->bookings_model->get_booking_statuses();
        $data['title']    = _l('bookings');
        $this->load->view('admin/bookings/manage', $data);
    }

    public function table($clientid = '')
    {
        $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/bookings'), [
            'clientid' => $clientid,
        ]);
    }

    public function staff_bookings()
    {
        $this->app->get_table_data('staff_bookings');
    }

    public function expenses($id)
    {
        $this->load->model('expenses_model');
        $this->app->get_table_data('booking_expenses', [
            'booking_id' => $id,
        ]);
    }

    public function add_expense()
    {
        if ($this->input->post()) {
            $this->load->model('expenses_model');
            $id = $this->expenses_model->add($this->input->post());
            if ($id) {
                set_alert('success', _l('added_successfully', _l('expense')));
                echo json_encode([
                    'url'       => admin_url('booking/bookings/view/' . $this->input->post('booking_id') . '/?group=booking_expenses'),
                    'expenseid' => $id,
                ]);
                die;
            }
            echo json_encode([
                'url' => admin_url('booking/bookings/view/' . $this->input->post('booking_id') . '/?group=booking_expenses'),
            ]);
            die;
        }
    }

    public function booking($id = '')
    {
        if (!has_permission('bookings', '', 'edit') && !has_permission('bookings', '', 'create')) {
            access_denied('Bookings');
        }

        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));
            if ($id == '') {
                if (!has_permission('bookings', '', 'create')) {
                    access_denied('Bookings');
                }
                $id = $this->bookings_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('booking')));
                    redirect(admin_url('booking/bookings/view/' . $id));
                }
            } else {
                if (!has_permission('bookings', '', 'edit')) {
                    access_denied('Bookings');
                }
                $success = $this->bookings_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('booking')));
                }
                redirect(admin_url('booking/bookings/view/' . $id));
            }
        }
        if ($id == '') {
            $title                            = _l('add_new', _l('booking_lowercase'));
            $data['auto_select_billing_type'] = $this->bookings_model->get_most_used_billing_type();
        } else {
            $data['booking']                               = $this->bookings_model->get($id);
            $data['booking']->settings->available_features = unserialize($data['booking']->settings->available_features);

            $data['booking_members'] = $this->bookings_model->get_booking_members($id);
            $title                   = _l('edit', _l('booking_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['last_booking_settings'] = $this->bookings_model->get_last_booking_settings();

        if (count($data['last_booking_settings'])) {
            $key                                          = array_search('available_features', array_column($data['last_booking_settings'], 'name'));
            $data['last_booking_settings'][$key]['value'] = unserialize($data['last_booking_settings'][$key]['value']);
        }

        $data['settings'] = $this->bookings_model->get_settings();
        $data['statuses'] = $this->bookings_model->get_booking_statuses();
        $data['staff']    = $this->staff_model->get('', ['active' => 1]);

        $data['title'] = $title;
        $this->load->view('admin/bookings/booking', $data);
    }

    public function gantt()
    {
        $data['title'] = _l('booking_gant');

        $selected_statuses = [];
        $selectedMember    = null;
        $data['statuses']  = $this->bookings_model->get_booking_statuses();

        $appliedStatuses = $this->input->get('status');
        $appliedMember   = $this->input->get('member');

        $allStatusesIds = [];
        foreach ($data['statuses'] as $status) {
            if (!isset($status['filter_default'])
                || (isset($status['filter_default']) && $status['filter_default'])
                && !$appliedStatuses) {
                $selected_statuses[] = $status['id'];
            } elseif ($appliedStatuses) {
                if (in_array($status['id'], $appliedStatuses)) {
                    $selected_statuses[] = $status['id'];
                }
            } else {
                // All statuses
                $allStatusesIds[] = $status['id'];
            }
        }

        if (count($selected_statuses) == 0) {
            $selected_statuses = $allStatusesIds;
        }


        $data['selected_statuses'] = $selected_statuses;

        if (has_permission('bookings', '', 'view')) {
            $selectedMember          = $appliedMember;
            $data['selectedMember']  = $selectedMember;
            $data['booking_members'] = $this->bookings_model->get_distinct_bookings_members();
        }

        $data['gantt_data'] = $this->bookings_model->get_all_bookings_gantt_data([
            'status' => $selected_statuses,
            'member' => $selectedMember,
        ]);

        $this->load->view('bookings/gantt', $data);
    }

    public function view($id)
    {
        if (has_permission('bookings', '', 'view') || $this->bookings_model->is_member($id)) {
            close_setup_menu();
            $booking = $this->bookings_model->get($id);
            
            if (!$booking) {
                blank_page(_l('booking_not_found'));
            }

            if(isset($booking->settings->available_features)) $booking->settings->available_features = unserialize($booking->settings->available_features);
            $data['statuses']                      = $this->bookings_model->get_booking_statuses();

            $group = !$this->input->get('group') ? 'booking_overview' : $this->input->get('group');

            // Unable to load the requested file: bookings/booking_tours#.php - FIX
            if (strpos($group, '#') !== false) {
                $group = str_replace('#', '', $group);
            }

            $data['tabs'] = get_booking_tabs_admin();
            $data['tab']  = $this->app_tabs->filter_tab($data['tabs'], $group);

            if (!$data['tab']) {
                show_404();
            }

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

            $data['booking']  = $booking;
            $data['currency'] = $this->bookings_model->get_currency($id);

            $data['booking_total_logged_time'] = $this->bookings_model->total_logged_time($id);

            $data['staff']     = $this->staff_model->get('', ['active' => 1]);
            $percent           = $this->bookings_model->calc_progress($id);
            $data['bodyclass'] = '';

            $this->app_scripts->add(
                'bookings-js',
                base_url($this->app_scripts->core_file('assets/js', 'bookings.js')) . '?v=' . $this->app_scripts->core_version(),
                'admin',
                ['app-js', 'jquery-comments-js', 'frappe-gantt-js', 'circle-progress-js']
            );

            if ($group == 'booking_overview') {
                $data['members'] = $this->bookings_model->get_booking_members($id);
                foreach ($data['members'] as $key => $member) {
                    $data['members'][$key]['total_logged_time'] = 0;
                    $member_timesheets                          = $this->tours_model->get_unique_member_logged_tour_ids($member['staff_id'], ' AND tour_id IN (SELECT id FROM ' . db_prefix() . 'tours WHERE rel_type="booking" AND rel_id="' . $this->db->escape_str($id) . '")');

                    foreach ($member_timesheets as $member_tour) {
                        $data['members'][$key]['total_logged_time'] += $this->tours_model->calc_tour_total_time($member_tour->tour_id, ' AND staff_id=' . $member['staff_id']);
                    }
                }

                $data['booking_total_days']        = round((human_to_unix($data['booking']->deadline . ' 00:00') - human_to_unix($data['booking']->start_date . ' 00:00')) / 3600 / 24);
                $data['booking_days_left']         = $data['booking_total_days'];
                $data['booking_time_left_percent'] = 100;
                if ($data['booking']->deadline) {
                    if (human_to_unix($data['booking']->start_date . ' 00:00') < time() && human_to_unix($data['booking']->deadline . ' 00:00') > time()) {
                        $data['booking_days_left']         = round((human_to_unix($data['booking']->deadline . ' 00:00') - time()) / 3600 / 24);
                        $data['booking_time_left_percent'] = $data['booking_days_left'] / $data['booking_total_days'] * 100;
                        $data['booking_time_left_percent'] = round($data['booking_time_left_percent'], 2);
                    }
                    if (human_to_unix($data['booking']->deadline . ' 00:00') < time()) {
                        $data['booking_days_left']         = 0;
                        $data['booking_time_left_percent'] = 0;
                    }
                }

                $__total_where_tours = 'rel_type = "booking" AND rel_id=' . $this->db->escape_str($id);
                if (!has_permission('tours', '', 'view')) {
                    $__total_where_tours .= ' AND ' . db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid = ' . get_staff_user_id() . ')';

                    if (get_option('show_all_tours_for_booking_member') == 1) {
                        $__total_where_tours .= ' AND (rel_type="booking" AND rel_id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . '))';
                    }
                }

                $__total_where_tours = hooks()->apply_filters('admin_total_booking_tours_where', $__total_where_tours, $id);

                $where = ($__total_where_tours == '' ? '' : $__total_where_tours . ' AND ') . 'status != ' . Tours_model::STATUS_COMPLETE;

                $data['tours_not_completed'] = total_rows(db_prefix() . 'tours', $where);
                $total_tours                 = total_rows(db_prefix() . 'tours', $__total_where_tours);
                $data['total_tours']         = $total_tours;

                $where = ($__total_where_tours == '' ? '' : $__total_where_tours . ' AND ') . 'status = ' . Tours_model::STATUS_COMPLETE . ' AND rel_type="booking" AND rel_id="' . $id . '"';

                $data['tours_completed'] = total_rows(db_prefix() . 'tours', $where);

                $data['tours_not_completed_progress'] = ($total_tours > 0 ? number_format(($data['tours_completed'] * 100) / $total_tours, 2) : 0);
                $data['tours_not_completed_progress'] = round($data['tours_not_completed_progress'], 2);

                @$percent_circle        = $percent / 100;
                $data['percent_circle'] = $percent_circle;


                $data['booking_overview_chart'] = $this->bookings_model->get_booking_overview_weekly_chart_data($id, ($this->input->get('overview_chart') ? $this->input->get('overview_chart'):'this_week'));
            } elseif ($group == 'booking_invoices') {
                $this->load->model('invoices_model');

                $data['invoiceid']   = '';
                $data['status']      = '';
                $data['custom_view'] = '';

                $data['invoices_years']       = $this->invoices_model->get_invoices_years();
                $data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();
                $data['invoices_statuses']    = $this->invoices_model->get_statuses();
            } elseif ($group == 'booking_gantt') {
                $gantt_type         = (!$this->input->get('gantt_type') ? 'milestones' : $this->input->get('gantt_type'));
                $tourStatus         = (!$this->input->get('gantt_tour_status') ? null : $this->input->get('gantt_tour_status'));
                $data['gantt_data'] = $this->bookings_model->get_gantt_data($id, $gantt_type, $tourStatus);
            } elseif ($group == 'booking_milestones') {
                $data['bodyclass'] .= 'booking-milestones ';
                $data['milestones_exclude_completed_tours'] = $this->input->get('exclude_completed') && $this->input->get('exclude_completed') == 'yes' || !$this->input->get('exclude_completed');

                $data['total_milestones'] = total_rows(db_prefix() . 'milestones', ['booking_id' => $id]);
                $data['milestones_found'] = $data['total_milestones'] > 0 || (!$data['total_milestones'] && total_rows(db_prefix() . 'tours', ['rel_id' => $id, 'rel_type' => 'booking', 'milestone' => 0]) > 0);
            } elseif ($group == 'booking_files') {
                $data['files'] = $this->bookings_model->get_files($id);
            } elseif ($group == 'booking_expenses') {
                $this->load->model('taxes_model');
                $this->load->model('expenses_model');
                $data['taxes']              = $this->taxes_model->get();
                $data['expense_categories'] = $this->expenses_model->get_category();
                $data['currencies']         = $this->currencies_model->get();
            } elseif ($group == 'booking_activity') {
                $data['activity'] = $this->bookings_model->get_activity($id);
            } elseif ($group == 'booking_notes') {
                $data['staff_notes'] = $this->bookings_model->get_staff_notes($id);
            } elseif ($group == 'booking_contracts') {
                $this->load->model('contracts_model');
                $data['contract_types'] = $this->contracts_model->get_contract_types();
                $data['years']          = $this->contracts_model->get_contracts_years();
            } elseif ($group == 'booking_estimates') {
                $this->load->model('estimates_model');
                $data['estimates_years']       = $this->estimates_model->get_estimates_years();
                $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();
                $data['estimate_statuses']     = $this->estimates_model->get_statuses();
                $data['estimateid']            = '';
                $data['switch_pipeline']       = '';
            } elseif ($group == 'booking_tickets') {
                $data['chosen_ticket_status'] = '';
                $this->load->model('tickets_model');
                $data['ticket_assignees'] = $this->tickets_model->get_tickets_assignes_disctinct();

                $this->load->model('departments_model');
                $data['staff_deparments_ids']          = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $data['default_tickets_list_statuses'] = hooks()->apply_filters('default_tickets_list_statuses', [1, 2, 4]);
            } elseif ($group == 'booking_timesheets') {
                // Tours are used in the timesheet dropdown
                // Completed tours are excluded from this list because you can't add timesheet on completed tour.
                $data['tours']                = $this->bookings_model->get_tours($id, 'status != ' . Tours_model::STATUS_COMPLETE . ' AND billed=0');
                $data['timesheets_staff_ids'] = $this->bookings_model->get_distinct_tours_timesheets_staff($id);
            }

            // Discussions
            if ($this->input->get('discussion_id')) {
                $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
                $data['discussion']                        = $this->bookings_model->get_discussion($this->input->get('discussion_id'), $id);
                $data['current_user_is_admin']             = is_admin();
            }

            $data['percent'] = $percent;

            $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');

            $other_bookings       = [];
            $other_bookings_where = 'id != ' . $id;

            $statuses = $this->bookings_model->get_booking_statuses();

            $other_bookings_where .= ' AND (';
            foreach ($statuses as $status) {
                if (isset($status['filter_default']) && $status['filter_default']) {
                    $other_bookings_where .= 'status = ' . $status['id'] . ' OR ';
                }
            }

            $other_bookings_where = rtrim($other_bookings_where, ' OR ');

            $other_bookings_where .= ')';

            if (!has_permission('bookings', '', 'view')) {
                $other_bookings_where .= ' AND ' . db_prefix() . 'bookings.id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . ')';
            }

            $data['other_bookings'] = $this->bookings_model->get('', $other_bookings_where);
            $data['title']          = $data['booking']->name;
            $data['bodyclass'] .= 'booking invoices-total-manual estimates-total-manual';
            $data['booking_status'] = get_booking_status_by_id($booking->status);

            $this->load->view('admin/bookings/view', $data);
        } else {
            access_denied('Booking View');
        }
    }

    public function mark_as()
    {
        $success = false;
        $message = '';
        if ($this->input->is_ajax_request()) {
            if (has_permission('bookings', '', 'create') || has_permission('bookings', '', 'edit')) {
                $status = get_booking_status_by_id($this->input->post('status_id'));

                $message = _l('booking_marked_as_failed', $status['name']);
                $success = $this->bookings_model->mark_as($this->input->post());

                if ($success) {
                    $message = _l('booking_marked_as_success', $status['name']);
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function file($id, $booking_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();

        $data['file'] = $this->bookings_model->get_file($id, $booking_id);

        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('bookings/_file', $data);
    }

    public function update_file_data()
    {
        if ($this->input->post()) {
            $this->bookings_model->update_file_data($this->input->post());
        }
    }

    public function add_external_file()
    {
        if ($this->input->post()) {
            $data                        = [];
            $data['booking_id']          = $this->input->post('booking_id');
            $data['files']               = $this->input->post('files');
            $data['external']            = $this->input->post('external');
            $data['visible_to_customer'] = ($this->input->post('visible_to_customer') == 'true' ? 1 : 0);
            $data['staffid']             = get_staff_user_id();
            $this->bookings_model->add_external_file($data);
        }
    }

    public function download_all_files($id)
    {
        if ($this->bookings_model->is_member($id) || has_permission('bookings', '', 'view')) {
            $files = $this->bookings_model->get_files($id);
            if (count($files) == 0) {
                set_alert('warning', _l('no_files_found'));
                redirect(admin_url('booking/bookings/view/' . $id . '?group=booking_files'));
            }
            $path = get_upload_path_by_type('booking') . $id;
            $this->load->library('zip');
            foreach ($files as $file) {
                $this->zip->read_file($path . '/' . $file['file_name']);
            }
            $this->zip->download(slug_it(get_booking_name_by_id($id)) . '-files.zip');
            $this->zip->clear_data();
        }
    }

    public function export_booking_data($id)
    {
        if (has_permission('bookings', '', 'create')) {
            app_pdf('booking-data', module_libs_path('booking') . 'pdf/Booking_data_pdf', $id);
        }
    }

    public function update_tour_milestone()
    {
        if ($this->input->post()) {
            $this->bookings_model->update_tour_milestone($this->input->post());
        }
    }

    public function update_milestones_order()
    {
        if ($post_data = $this->input->post()) {
            $this->bookings_model->update_milestones_order($post_data);
        }
    }

    public function pin_action($booking_id)
    {
        $this->bookings_model->pin_action($booking_id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function add_edit_members($booking_id)
    {
        if (has_permission('bookings', '', 'edit')) {
            $this->bookings_model->add_edit_members($this->input->post(), $booking_id);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function discussions($booking_id)
    {
        if ($this->bookings_model->is_member($booking_id) || has_permission('bookings', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('booking_discussions', [
                    'booking_id' => $booking_id,
                ]);
            }
        }
    }

    public function discussion($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->bookings_model->add_discussion($this->input->post());
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('booking_discussion'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->bookings_model->edit_discussion($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('booking_discussion'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function get_discussion_comments($id, $type)
    {
        echo json_encode($this->bookings_model->get_discussion_comments($id, $type));
    }

    public function add_discussion_comment($discussion_id, $type)
    {
        echo json_encode($this->bookings_model->add_discussion_comment(
            $this->input->post(null, false),
            $discussion_id,
            $type
        ));
    }

    public function update_discussion_comment()
    {
        echo json_encode($this->bookings_model->update_discussion_comment($this->input->post(null, false)));
    }

    public function delete_discussion_comment($id)
    {
        echo json_encode($this->bookings_model->delete_discussion_comment($id));
    }

    public function delete_discussion($id)
    {
        $success = false;
        if (has_permission('bookings', '', 'delete')) {
            $success = $this->bookings_model->delete_discussion($id);
        }
        $alert_type = 'warning';
        $message    = _l('booking_discussion_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('booking_discussion_deleted');
        }
        echo json_encode([
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function change_milestone_color()
    {
        if ($this->input->post()) {
            $this->bookings_model->update_milestone_color($this->input->post());
        }
    }

    public function upload_file($booking_id)
    {
        handle_booking_file_uploads($booking_id);
    }

    public function change_file_visibility($id, $visible)
    {
        if ($this->input->is_ajax_request()) {
            $this->bookings_model->change_file_visibility($id, $visible);
        }
    }

    public function change_activity_visibility($id, $visible)
    {
        if (has_permission('bookings', '', 'create')) {
            if ($this->input->is_ajax_request()) {
                $this->bookings_model->change_activity_visibility($id, $visible);
            }
        }
    }

    public function remove_file($booking_id, $id)
    {
        $this->bookings_model->remove_file($id);
        redirect(admin_url('booking/bookings/view/' . $booking_id . '?group=booking_files'));
    }

    public function milestones_kanban()
    {
        $data['milestones_exclude_completed_tours'] = $this->input->get('exclude_completed_tours') && $this->input->get('exclude_completed_tours') == 'yes';

        $data['booking_id'] = $this->input->get('booking_id');
        $data['milestones'] = [];

        $data['milestones'][] = [
          'name'              => _l('milestones_uncategorized'),
          'id'                => 0,
          'total_logged_time' => $this->bookings_model->calc_milestone_logged_time($data['booking_id'], 0),
          'color'             => null,
          ];

        $_milestones = $this->bookings_model->get_milestones($data['booking_id']);

        foreach ($_milestones as $m) {
            $data['milestones'][] = $m;
        }

        echo $this->load->view('bookings/milestones_kan_ban', $data, true);
    }

    public function milestones_kanban_load_more()
    {
        $milestones_exclude_completed_tours = $this->input->get('exclude_completed_tours') && $this->input->get('exclude_completed_tours') == 'yes';

        $status     = $this->input->get('status');
        $page       = $this->input->get('page');
        $booking_id = $this->input->get('booking_id');
        $where      = [];
        if ($milestones_exclude_completed_tours) {
            $where['status !='] = Tours_model::STATUS_COMPLETE;
        }
        $tours = $this->bookings_model->do_milestones_kanban_query($status, $booking_id, $page, $where);
        foreach ($tours as $tour) {
            $this->load->view('bookings/_milestone_kanban_card', ['tour' => $tour, 'milestone' => $status]);
        }
    }

    public function milestones($booking_id)
    {
        if ($this->bookings_model->is_member($booking_id) || has_permission('bookings', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('milestones', [
                    'booking_id' => $booking_id,
                ]);
            }
        }
    }

    public function milestone($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->bookings_model->add_milestone($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('booking_milestone')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->bookings_model->update_milestone($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('booking_milestone')));
                }
            }
        }

        redirect(admin_url('booking/bookings/view/' . $this->input->post('booking_id') . '?group=booking_milestones'));
    }

    public function delete_milestone($booking_id, $id)
    {
        if (has_permission('bookings', '', 'delete')) {
            if ($this->bookings_model->delete_milestone($id)) {
                set_alert('deleted', 'booking_milestone');
            }
        }
        redirect(admin_url('booking/bookings/view/' . $booking_id . '?group=booking_milestones'));
    }

    public function bulk_action_files()
    {
        hooks()->do_action('before_do_bulk_action_for_booking_files');
        $total_deleted       = 0;
        $hasPermissionDelete = has_permission('bookings', '', 'delete');
        // bulk action for bookings currently only have delete button
        if ($this->input->post()) {
            $fVisibility = $this->input->post('visible_to_customer') == 'true' ? 1 : 0;
            $ids         = $this->input->post('ids');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($hasPermissionDelete && $this->input->post('mass_delete') && $this->bookings_model->remove_file($id)) {
                        $total_deleted++;
                    } else {
                        $this->bookings_model->change_file_visibility($id, $fVisibility);
                    }
                }
            }
        }
        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_files_deleted', $total_deleted));
        }
    }

    public function timesheets($booking_id)
    {
        if ($this->bookings_model->is_member($booking_id) || has_permission('bookings', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('timesheets', [
                    'booking_id' => $booking_id,
                ]);
            }
        }
    }

    public function timesheet()
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            $success = $this->tours_model->timesheet($this->input->post());
            if ($success === true) {
                $message = _l('added_successfully', _l('booking_timesheet'));
            } elseif (is_array($success) && isset($success['end_time_smaller'])) {
                $message = _l('failed_to_add_booking_timesheet_end_time_smaller');
            } else {
                $message = _l('booking_timesheet_not_updated');
            }
            echo json_encode([
                'success' => $success,
                'message' => $message,
            ]);
            die;
        }
    }

    public function timesheet_tour_assignees($tour_id, $booking_id, $staff_id = 'undefined')
    {
        $assignees             = $this->tours_model->get_tour_assignees($tour_id);
        $data                  = '';
        $has_permission_edit   = has_permission('bookings', '', 'edit');
        $has_permission_create = has_permission('bookings', '', 'edit');
        // The second condition if staff member edit their own timesheet
        if ($staff_id == 'undefined' || $staff_id != 'undefined' && (!$has_permission_edit || !$has_permission_create)) {
            $staff_id     = get_staff_user_id();
            $current_user = true;
        }
        foreach ($assignees as $staff) {
            $selected = '';
            // maybe is admin and not booking member
            if ($staff['assigneeid'] == $staff_id && $this->bookings_model->is_member($booking_id, $staff_id)) {
                $selected = ' selected';
            }
            if ((!$has_permission_edit || !$has_permission_create) && isset($current_user)) {
                if ($staff['assigneeid'] != $staff_id) {
                    continue;
                }
            }
            $data .= '<option value="' . $staff['assigneeid'] . '"' . $selected . '>' . get_staff_full_name($staff['assigneeid']) . '</option>';
        }
        echo $data;
    }

    public function remove_team_member($booking_id, $staff_id)
    {
        if (has_permission('bookings', '', 'edit')) {
            if ($this->bookings_model->remove_team_member($booking_id, $staff_id)) {
                set_alert('success', _l('booking_member_removed'));
            }
        }

        redirect(admin_url('booking/bookings/view/' . $booking_id));
    }

    public function save_note($booking_id)
    {
        if ($this->input->post()) {
            $success = $this->bookings_model->save_note($this->input->post(null, false), $booking_id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('booking_note')));
            }
            redirect(admin_url('booking/bookings/view/' . $booking_id . '?group=booking_notes'));
        }
    }

    public function delete($booking_id)
    {
        if (has_permission('bookings', '', 'delete')) {
            $booking = $this->bookings_model->get($booking_id);
            $success = $this->bookings_model->delete($booking_id);
            if ($success) {
                set_alert('success', _l('deleted', _l('booking')));
                if (strpos($_SERVER['HTTP_REFERER'], 'clients/') !== false) {
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    redirect(admin_url('booking/bookings'));
                }
            } else {
                set_alert('warning', _l('problem_deleting', _l('booking_lowercase')));
                redirect(admin_url('booking/bookings/view/' . $booking_id));
            }
        }
    }

    public function copy($booking_id)
    {
        if (has_permission('bookings', '', 'create')) {
            $id = $this->bookings_model->copy($booking_id, $this->input->post());
            if ($id) {
                set_alert('success', _l('booking_copied_successfully'));
                redirect(admin_url('booking/bookings/view/' . $id));
            } else {
                set_alert('danger', _l('failed_to_copy_booking'));
                redirect(admin_url('booking/bookings/view/' . $booking_id));
            }
        }
    }

    public function mass_stop_timers($booking_id, $billable = 'false')
    {
        if (has_permission('invoices', '', 'create')) {
            $where = [
                'billed'       => 0,
                'startdate <=' => date('Y-m-d'),
            ];
            if ($billable == 'true') {
                $where['billable'] = true;
            }
            $tours                = $this->bookings_model->get_tours($booking_id, $where);
            $total_timers_stopped = 0;
            foreach ($tours as $tour) {
                $this->db->where('tour_id', $tour['id']);
                $this->db->where('end_time IS NULL');
                $this->db->update(db_prefix() . 'tourstimers', [
                    'end_time' => time(),
                ]);
                $total_timers_stopped += $this->db->affected_rows();
            }
            $message = _l('booking_tours_total_timers_stopped', $total_timers_stopped);
            $type    = 'success';
            if ($total_timers_stopped == 0) {
                $type = 'warning';
            }
            echo json_encode([
                'type'    => $type,
                'message' => $message,
            ]);
        }
    }

    public function get_pre_invoice_booking_info($booking_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $data['billable_tours'] = $this->bookings_model->get_tours($booking_id, [
                'billable'     => 1,
                'billed'       => 0,
                'startdate <=' => date('Y-m-d'),
            ]);

            $data['not_billable_tours'] = $this->bookings_model->get_tours($booking_id, [
                'billable'    => 1,
                'billed'      => 0,
                'startdate >' => date('Y-m-d'),
            ]);

            $data['booking_id']   = $booking_id;
            $data['billing_type'] = get_booking_billing_type($booking_id);

            $this->load->model('expenses_model');
            $this->db->where('invoiceid IS NULL');
            $data['expenses'] = $this->expenses_model->get('', [
                'booking_id' => $booking_id,
                'billable'   => 1,
            ]);

            $this->load->view('bookings/booking_pre_invoice_settings', $data);
        }
    }

    public function get_invoice_booking_data()
    {
        if (has_permission('invoices', '', 'create')) {
            $type       = $this->input->post('type');
            $booking_id = $this->input->post('booking_id');
            // Check for all cases
            if ($type == '') {
                $type == 'single_line';
            }
            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [
                'expenses_only !=' => 1,
            ]);
            $this->load->model('taxes_model');
            $data['taxes']         = $this->taxes_model->get();
            $data['currencies']    = $this->currencies_model->get();
            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $this->load->model('invoice_items_model');

            $data['ajaxItems'] = false;
            if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
                $data['items'] = $this->invoice_items_model->get_grouped();
            } else {
                $data['items']     = [];
                $data['ajaxItems'] = true;
            }

            $data['items_groups'] = $this->invoice_items_model->get_groups();
            $data['staff']        = $this->staff_model->get('', ['active' => 1]);
            $booking              = $this->bookings_model->get($booking_id);
            $data['booking']      = $booking;
            $items                = [];

            $booking    = $this->bookings_model->get($booking_id);
            $item['id'] = 0;

            $default_tax     = unserialize(get_option('default_tax'));
            $item['taxname'] = $default_tax;

            $tours = $this->input->post('tours');
            if ($tours) {
                $item['long_description'] = '';
                $item['qty']              = 0;
                $item['tour_id']          = [];
                if ($type == 'single_line') {
                    $item['description'] = $booking->name;
                    foreach ($tours as $tour_id) {
                        $tour = $this->tours_model->get($tour_id);
                        $sec  = $this->tours_model->calc_tour_total_time($tour_id);
                        $item['long_description'] .= $tour->name . ' - ' . seconds_to_time_format($sec) . ' ' . _l('hours') . "\r\n";
                        $item['tour_id'][] = $tour_id;
                        if ($booking->billing_type == 2) {
                            if ($sec < 60) {
                                $sec = 0;
                            }
                            $item['qty'] += sec2qty($sec);
                        }
                    }
                    if ($booking->billing_type == 1) {
                        $item['qty']  = 1;
                        $item['rate'] = $booking->booking_cost;
                    } elseif ($booking->billing_type == 2) {
                        $item['rate'] = $booking->booking_rate_per_hour;
                    }
                    $item['unit'] = '';
                    $items[]      = $item;
                } elseif ($type == 'tour_per_item') {
                    foreach ($tours as $tour_id) {
                        $tour                     = $this->tours_model->get($tour_id);
                        $sec                      = $this->tours_model->calc_tour_total_time($tour_id);
                        $item['description']      = $booking->name . ' - ' . $tour->name;
                        $item['qty']              = floatVal(sec2qty($sec));
                        $item['long_description'] = seconds_to_time_format($sec) . ' ' . _l('hours');
                        if ($booking->billing_type == 2) {
                            $item['rate'] = $booking->booking_rate_per_hour;
                        } elseif ($booking->billing_type == 3) {
                            $item['rate'] = $tour->hourly_rate;
                        }
                        $item['tour_id'] = $tour_id;
                        $item['unit']    = '';
                        $items[]         = $item;
                    }
                } elseif ($type == 'timesheets_individualy') {
                    $timesheets     = $this->bookings_model->get_timesheets($booking_id, $tours);
                    $added_tour_ids = [];
                    foreach ($timesheets as $timesheet) {
                        if ($timesheet['tour_data']->billed == 0 && $timesheet['tour_data']->billable == 1) {
                            $item['description'] = $booking->name . ' - ' . $timesheet['tour_data']->name;
                            if (!in_array($timesheet['tour_id'], $added_tour_ids)) {
                                $item['tour_id'] = $timesheet['tour_id'];
                            }

                            array_push($added_tour_ids, $timesheet['tour_id']);

                            $item['qty']              = floatVal(sec2qty($timesheet['total_spent']));
                            $item['long_description'] = _l('booking_invoice_timesheet_start_time', _dt($timesheet['start_time'], true)) . "\r\n" . _l('booking_invoice_timesheet_end_time', _dt($timesheet['end_time'], true)) . "\r\n" . _l('booking_invoice_timesheet_total_logged_time', seconds_to_time_format($timesheet['total_spent'])) . ' ' . _l('hours');

                            if ($this->input->post('timesheets_include_notes') && $timesheet['note']) {
                                $item['long_description'] .= "\r\n\r\n" . _l('note') . ': ' . $timesheet['note'];
                            }

                            if ($booking->billing_type == 2) {
                                $item['rate'] = $booking->booking_rate_per_hour;
                            } elseif ($booking->billing_type == 3) {
                                $item['rate'] = $timesheet['tour_data']->hourly_rate;
                            }
                            $item['unit'] = '';
                            $items[]      = $item;
                        }
                    }
                }
            }
            if ($booking->billing_type != 1) {
                $data['hours_quantity'] = true;
            }
            if ($this->input->post('expenses')) {
                if (isset($data['hours_quantity'])) {
                    unset($data['hours_quantity']);
                }
                if (count($tours) > 0) {
                    $data['qty_hrs_quantity'] = true;
                }
                $expenses       = $this->input->post('expenses');
                $addExpenseNote = $this->input->post('expenses_add_note');
                $addExpenseName = $this->input->post('expenses_add_name');

                if (!$addExpenseNote) {
                    $addExpenseNote = [];
                }

                if (!$addExpenseName) {
                    $addExpenseName = [];
                }

                $this->load->model('expenses_model');
                foreach ($expenses as $expense_id) {
                    // reset item array
                    $item                     = [];
                    $item['id']               = 0;
                    $expense                  = $this->expenses_model->get($expense_id);
                    $item['expense_id']       = $expense->expenseid;
                    $item['description']      = _l('item_as_expense') . ' ' . $expense->name;
                    $item['long_description'] = $expense->description;

                    if (in_array($expense_id, $addExpenseNote) && !empty($expense->note)) {
                        $item['long_description'] .= PHP_EOL . $expense->note;
                    }

                    if (in_array($expense_id, $addExpenseName) && !empty($expense->expense_name)) {
                        $item['long_description'] .= PHP_EOL . $expense->expense_name;
                    }

                    $item['qty'] = 1;

                    $item['taxname'] = [];
                    if ($expense->tax != 0) {
                        array_push($item['taxname'], $expense->tax_name . '|' . $expense->taxrate);
                    }
                    if ($expense->tax2 != 0) {
                        array_push($item['taxname'], $expense->tax_name2 . '|' . $expense->taxrate2);
                    }
                    $item['rate']  = $expense->amount;
                    $item['order'] = 1;
                    $item['unit']  = '';
                    $items[]       = $item;
                }
            }
            $data['customer_id']          = $booking->clientid;
            $data['invoice_from_booking'] = true;
            $data['add_items']            = $items;
            $this->load->view('bookings/invoice_booking', $data);
        }
    }

    public function get_rel_booking_data($id, $tour_id = '')
    {
        if ($this->input->is_ajax_request()) {
            $selected_milestone = '';
            if ($tour_id != '' && $tour_id != 'undefined') {
                $tour               = $this->tours_model->get($tour_id);
                $selected_milestone = $tour->milestone;
            }

            $allow_to_view_tours = 0;
            $this->db->where('booking_id', $id);
            $this->db->where('name', 'view_tours');
            $booking_settings = $this->db->get(db_prefix() . 'booking_settings')->row();
            if ($booking_settings) {
                $allow_to_view_tours = $booking_settings->value;
            }

            $deadline = get_booking_deadline($id);

            echo json_encode([
                'deadline'            => $deadline,
                'deadline_formatted'  => $deadline ? _d($deadline) : null,
                'allow_to_view_tours' => $allow_to_view_tours,
                'billing_type'        => get_booking_billing_type($id),
                'milestones'          => render_select('milestone', $this->bookings_model->get_milestones($id), [
                    'id',
                    'name',
                ], 'tour_milestone', $selected_milestone),
            ]);
        }
    }

    public function invoice_booking($booking_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $this->load->model('invoices_model');
            $data               = $this->input->post();
            $data['booking_id'] = $booking_id;
            $invoice_id         = $this->invoices_model->add($data);
            if ($invoice_id) {
                $this->bookings_model->log_activity($booking_id, 'booking_activity_invoiced_booking', format_invoice_number($invoice_id));
                set_alert('success', _l('booking_invoiced_successfully'));
            }
            redirect(admin_url('booking/bookings/view/' . $booking_id . '?group=booking_invoices'));
        }
    }

    public function view_booking_as_client($id, $clientid)
    {
        if (is_admin()) {
            login_as_client($clientid);
            redirect(site_url('clients/booking/' . $id));
        }
    }
}
