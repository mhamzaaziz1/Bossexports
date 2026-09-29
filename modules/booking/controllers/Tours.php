<?php

use app\services\utilities\Date;

defined('BASEPATH') or exit('No direct script access allowed');

class Tours extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('tours_model');

        $currentUser = $this->staff_model->get(get_staff_user_id());

        // Deleted or inactive but have session
        if (!$currentUser || $currentUser->active == 0) {
            $this->authentication_model->logout();
            redirect(admin_url('authentication'));
        }

        $GLOBALS['current_user'] = $currentUser;

        init_admin_assets();

        hooks()->do_action('admin_init');
        
        $vars = [
            'current_user'    => $currentUser,
            'current_version' => $this->current_db_version,
            'tour_statuses'   => $this->tours_model->get_statuses(),
        ];

        if (!$this->input->is_ajax_request()) {
            $vars['sidebar_menu'] = $this->app_menu->get_sidebar_menu_items();
            $vars['setup_menu']   = $this->app_menu->get_setup_menu_items();
        }

        /**
         * Autoloaded view variables
         * @var array
         */
        $vars = hooks()->apply_filters('admin_area_auto_loaded_vars', $vars);
        $this->load->vars($vars);
    }

    /* Open also all taks if user access this /tours url */
    public function index($id = '')
    {
        $this->list_tours($id);
    }

    /* List all tours */
    public function list_tours($id = '')
    {
        close_setup_menu();
        // If passed from url
        $data['custom_view'] = $this->input->get('custom_view') ? $this->input->get('custom_view') : '';
        $data['tourid']      = $id;

        if ($this->input->get('kanban')) {
            $this->switch_kanban(0, true);
        }

        $data['switch_kanban'] = false;
        $data['bodyclass']     = 'tours-page';

        if ($this->session->userdata('tours_kanban_view') == 'true') {
            $data['switch_kanban'] = true;
            $data['bodyclass']     = 'tours-page kan-ban-body';
        }

        $data['title'] = _l('tours');
        $this->load->view('admin/tours/manage', $data);
    }

    public function table()
    {
        $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/tours'));
    }

    public function kanban()
    {
        echo $this->load->view('admin/tours/kan_ban', [], true);
    }

    public function ajax_search_assign_tour_to_timer()
    {
        if ($this->input->is_ajax_request()) {
            $q = $this->input->post('q');
            $q = trim($q);
            $this->db->select('name, id,' . tours_rel_name_select_query() . ' as subtext');
            $this->db->from(db_prefix() . 'tours');
            $this->db->where('' . db_prefix() . 'tours.id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid = ' . get_staff_user_id() . ')');
            //   $this->db->where('id NOT IN (SELECT tour_id FROM '.db_prefix().'tourstimers WHERE staff_id = ' . get_staff_user_id() . ' AND end_time IS NULL)');
            $this->db->where('status != ', 5);
            $this->db->where('billed', 0);
            $this->db->group_start();
            $this->db->like('name', $q);
            $this->db->or_like(tours_rel_name_select_query(), $q);
            $this->db->group_end();
            echo json_encode($this->db->get()->result_array());
        }
    }

    public function tours_kanban_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $where = [];
        if ($this->input->get('booking_id')) {
            $where['rel_id']   = $this->input->get('booking_id');
            $where['rel_type'] = 'booking';
        }

        $tours = $this->tours_model->do_kanban_query($status, $this->input->get('search'), $page, false, $where);

        foreach ($tours as $tour) {
            $this->load->view('admin/tours/_kan_ban_card', [
                'tour'   => $tour,
                'status' => $status,
            ]);
        }
    }

    public function update_order()
    {
        $this->tours_model->update_order($this->input->post());
    }

    public function switch_kanban($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'false';
        } else {
            $set = 'true';
        }

        $this->session->set_userdata([
            'tours_kanban_view' => $set,
        ]);
        if ($manual == false) {
            // clicked on VIEW KANBAN from bookings area and will redirect again to the same view
            if (strpos($_SERVER['HTTP_REFERER'], 'booking_id') !== false) {
                redirect(admin_url('tours'));
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    // Used in invoice add/edit
    public function get_billable_tours_by_booking($booking_id)
    {
        if ($this->input->is_ajax_request() && (has_permission('invoices', '', 'edit') || has_permission('invoices', '', 'create'))) {
            $customer_id = get_client_id_by_booking_id($booking_id);
            echo json_encode($this->tours_model->get_billable_tours($customer_id, $booking_id));
        }
    }

    // Used in invoice add/edit
    public function get_billable_tours_by_customer_id($customer_id)
    {
        if ($this->input->is_ajax_request() && (has_permission('invoices', '', 'edit') || has_permission('invoices', '', 'create'))) {
            echo json_encode($this->tours_model->get_billable_tours($customer_id));
        }
    }

    public function update_tour_description($id)
    {
        if (has_permission('tours', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tours', [
                'description' => html_purify($this->input->post('description', false)),
            ]);
        }
    }

    public function detailed_overview()
    {
        $overview = [];

        $has_permission_create = has_permission('tours', '', 'create');
        $has_permission_view   = has_permission('tours', '', 'view');

        if (!$has_permission_view) {
            $staff_id = get_staff_user_id();
        } elseif ($this->input->post('member')) {
            $staff_id = $this->input->post('member');
        } else {
            $staff_id = '';
        }

        $month = ($this->input->post('month') ? $this->input->post('month') : date('m'));
        if ($this->input->post() && $this->input->post('month') == '') {
            $month = '';
        }

        $status = $this->input->post('status');

        $fetch_month_from = 'startdate';

        $year       = ($this->input->post('year') ? $this->input->post('year') : date('Y'));
        $booking_id = $this->input->get('booking_id');

        for ($m = 1; $m <= 12; $m++) {
            if ($month != '' && $month != $m) {
                continue;
            }

            // Tour rel_name
            $sqlToursSelect = '*,' . tours_rel_name_select_query() . ' as rel_name';

            // Tour logged time
            $selectLoggedTime = get_sql_calc_tour_logged_time('tmp-tour-id');
            // Replace tmp-tour-id to be the same like tours.id
            $selectLoggedTime = str_replace('tmp-tour-id', db_prefix() . 'tours.id', $selectLoggedTime);

            if (is_numeric($staff_id)) {
                $selectLoggedTime .= ' AND staff_id=' . $this->db->escape_str($staff_id);
                $sqlToursSelect .= ',(' . $selectLoggedTime . ')';
            } else {
                $sqlToursSelect .= ',(' . $selectLoggedTime . ')';
            }

            $sqlToursSelect .= ' as total_logged_time';

            // Tour checklist items
            $sqlToursSelect .= ',' . get_sql_select_tour_total_checklist_items();

            if (is_numeric($staff_id)) {
                $sqlToursSelect .= ',(SELECT COUNT(id) FROM ' . db_prefix() . 'tour_checklist_items WHERE tourid=' . db_prefix() . 'tours.id AND finished=1 AND finished_from=' . $staff_id . ') as total_finished_checklist_items';
            } else {
                $sqlToursSelect .= ',' . get_sql_select_tour_total_finished_checklist_items();
            }

            // Tour total comment and total files
            $selectTotalComments = ',(SELECT COUNT(id) FROM ' . db_prefix() . 'tour_comments WHERE tourid=' . db_prefix() . 'tours.id';
            $selectTotalFiles    = ',(SELECT COUNT(id) FROM ' . db_prefix() . 'files WHERE rel_id=' . db_prefix() . 'tours.id AND rel_type="tour"';

            if (is_numeric($staff_id)) {
                $sqlToursSelect .= $selectTotalComments . ' AND staffid=' . $staff_id . ') as total_comments_staff';
                $sqlToursSelect .= $selectTotalFiles . ' AND staffid=' . $staff_id . ') as total_files_staff';
            }

            $sqlToursSelect .= $selectTotalComments . ') as total_comments';
            $sqlToursSelect .= $selectTotalFiles . ') as total_files';

            // Tour assignees
            $sqlToursSelect .= ',' . get_sql_select_tour_asignees_full_names() . ' as assignees' . ',' . get_sql_select_tour_assignees_ids() . ' as assignees_ids';

            $this->db->select($sqlToursSelect);

            $this->db->where('MONTH(' . $fetch_month_from . ')', $m);
            $this->db->where('YEAR(' . $fetch_month_from . ')', $year);

            if ($booking_id && $booking_id != '') {
                $this->db->where('rel_id', $booking_id);
                $this->db->where('rel_type', 'booking');
            }

            if (!$has_permission_view) {
                $sqlWhereStaff = '(id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . $staff_id . ')';

                // User dont have permission for view but have for create
                // Only show tours createad by this user.
                if ($has_permission_create) {
                    $sqlWhereStaff .= ' OR addedfrom=' . get_staff_user_id();
                }

                $sqlWhereStaff .= ')';
                $this->db->where($sqlWhereStaff);
            } elseif ($has_permission_view) {
                if (is_numeric($staff_id)) {
                    $this->db->where('(id IN (SELECT tourid FROM ' . db_prefix() . 'tour_assigned WHERE staffid=' . $staff_id . '))');
                }
            }

            if ($status) {
                $this->db->where('status', $status);
            }

            $this->db->order_by($fetch_month_from, 'ASC');
            array_push($overview, $m);
            $overview[$m] = $this->db->get(db_prefix() . 'tours')->result_array();
        }

        unset($overview[0]);

        $overview = [
            'staff_id' => $staff_id,
            'detailed' => $overview,
        ];

        $data['members']  = $this->staff_model->get();
        $data['overview'] = $overview['detailed'];
        $data['years']    = $this->tours_model->get_distinct_tours_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));
        $data['staff_id'] = $overview['staff_id'];
        $data['title']    = _l('detailed_overview');
        $this->load->view('admin/tours/detailed_overview', $data);
    }

    public function init_relation_tours($rel_id, $rel_type)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/tours_relations'), [
                'rel_id'   => $rel_id,
                'rel_type' => $rel_type,
            ]);
        }
    }

    /* Add new tour or update existing */
    public function tour($id = '')
    {
        if (!has_permission('tours', '', 'edit') && !has_permission('tours', '', 'create')) {
            ajax_access_denied();
        }

        $data = [];
        // FOr new tour add directly from the bookings milestones
        if ($this->input->get('milestone_id')) {
            $this->db->where('id', $this->input->get('milestone_id'));
            $milestone = $this->db->get(db_prefix() . 'milestones')->row();
            if ($milestone) {
                $data['_milestone_selected_data'] = [
                    'id'       => $milestone->id,
                    'due_date' => _d($milestone->due_date),
                ];
            }
        }
        if ($this->input->get('start_date')) {
            $data['start_date'] = $this->input->get('start_date');
        }
        if ($this->input->post()) {
            $data                = $this->input->post();
            $data['description'] = html_purify($this->input->post('description', false));
            if ($id == '') {
                if (!has_permission('tours', '', 'create')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $id      = $this->tours_model->add($data);
                $_id     = false;
                $success = false;
                $message = '';
                if ($id) {
                    $success       = true;
                    $_id           = $id;
                    $message       = _l('added_successfully', _l('tour'));
                    $uploadedFiles = handle_tour_attachments_array($id);
                    if ($uploadedFiles && is_array($uploadedFiles)) {
                        foreach ($uploadedFiles as $file) {
                            $this->misc_model->add_attachment_to_database($id, 'tour', [$file]);
                        }
                    }
                }
                echo json_encode([
                    'success' => $success,
                    'id'      => $_id,
                    'message' => $message,
                ]);
            } else {
                if (!has_permission('tours', '', 'edit')) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                        'success' => false,
                        'message' => _l('access_denied'),
                    ]);
                    die;
                }
                $success = $this->tours_model->update($data, $id);
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('tour'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id'      => $id,
                ]);
            }
            die;
        }

        $data['milestones']         = [];
        $data['checklistTemplates'] = $this->tours_model->get_checklist_templates();
        if ($id == '') {
            $title = _l('add_new', _l('tour_lowercase'));
        } else {
            $data['tour'] = $this->tours_model->get($id);
            if ($data['tour']->rel_type == 'booking') {
                $data['milestones'] = $this->bookings_model->get_milestones($data['tour']->rel_id);
            }
            $title = _l('edit', _l('tour_lowercase')) . ' ' . $data['tour']->name;
        }

        $data['booking_end_date_attrs'] = [];
        if ($this->input->get('rel_type') == 'booking' && $this->input->get('rel_id') || ($id !== '' && $data['tour']->rel_type == 'booking')) {
            $booking = $this->bookings_model->get($id === '' ? $this->input->get('rel_id') : $data['tour']->rel_id);

            if ($booking->deadline) {
                $data['booking_end_date_attrs'] = [
                    'data-date-end-date' => $booking->deadline,
                ];
            }
        }

        $data['id']    = $id;
        $data['title'] = $title;
        $this->load->view('admin/tours/tour', $data);
    }

    public function copy()
    {
        if (has_permission('tours', '', 'create')) {
            $new_tour_id = $this->tours_model->copy($this->input->post());
            $response    = [
                'new_tour_id' => '',
                'alert_type'  => 'warning',
                'message'     => _l('failed_to_copy_tour'),
                'success'     => false,
            ];
            if ($new_tour_id) {
                $response['message']     = _l('tour_copied_successfully');
                $response['new_tour_id'] = $new_tour_id;
                $response['success']     = true;
                $response['alert_type']  = 'success';
            }
            echo json_encode($response);
        }
    }

    public function get_billable_tour_data($tour_id)
    {
        $tour              = $this->tours_model->get_billable_tour_data($tour_id);
        $tour->description = seconds_to_time_format($tour->total_seconds) . ' ' . _l('hours');
        echo json_encode($tour);
    }

    /**
     * Tour ajax request modal
     * @param  mixed $tourid
     * @return mixed
     */
    public function get_tour_data($tourid, $return = false)
    {
        $tours_where = [];

        if (!has_permission('tours', '', 'view')) {
            $tours_where = get_tours_where_string(false);
        }

        $tour = $this->tours_model->get($tourid, $tours_where);

        if (!$tour) {
            header('HTTP/1.0 404 Not Found');
            echo 'Tour not found';
            die();
        }

        $data['checklistTemplates'] = $this->tours_model->get_checklist_templates();
        $data['tour']               = $tour;
        $data['id']                 = $tour->id;
        $data['staff']              = $this->staff_model->get('', ['active' => 1]);
        $data['reminders']          = $this->tours_model->get_reminders($tourid);

        $data['staff_reminders'] = $this->tours_model->get_staff_members_that_can_access_tour($tourid);

        $data['booking_deadline'] = null;
        if ($tour->rel_type == 'booking') {
            $data['booking_deadline'] = get_booking_deadline($tour->rel_id);
        }

        if ($return == false) {
            $this->load->view('admin/tours/view_tour_template', $data);
        } else {
            return $this->load->view('admin/tours/view_tour_template', $data, true);
        }
    }

    public function add_reminder($tour_id)
    {
        $message    = '';
        $alert_type = 'warning';
        if ($this->input->post()) {
            $success = $this->misc_model->add_reminder($this->input->post(), $tour_id);
            if ($success) {
                $alert_type = 'success';
                $message    = _l('reminder_added_successfully');
            }
        }
        echo json_encode([
            'tourHtml'   => $this->get_tour_data($tour_id, true),
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function edit_reminder($id)
    {
        $reminder = $this->misc_model->get_reminders($id);
        if ($reminder && ($reminder->creator == get_staff_user_id() || is_admin()) && $reminder->isnotified == 0) {
            $success = $this->misc_model->edit_reminder($this->input->post(), $id);
            echo json_encode([
                'tourHtml'   => $this->get_tour_data($reminder->rel_id, true),
                'alert_type' => 'success',
                'message'    => ($success ? _l('updated_successfully', _l('reminder')) : ''),
            ]);
        }
    }

    public function delete_reminder($rel_id, $id)
    {
        $success    = $this->misc_model->delete_reminder($id);
        $alert_type = 'warning';
        $message    = _l('reminder_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('reminder_deleted');
        }
        echo json_encode([
            'tourHtml'   => $this->get_tour_data($rel_id, true),
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function get_staff_started_timers($return = false)
    {
        $data['startedTimers'] = $this->misc_model->get_staff_started_timers();
        $_data['html']         = $this->load->view('admin/tours/started_timers', $data, true);
        $_data['total_timers'] = count($data['startedTimers']);

        $timers = json_encode($_data);
        if ($return) {
            return $timers;
        }

        echo $timers;
    }

    public function save_checklist_item_template()
    {
        if (has_permission('checklist_templates', '', 'create')) {
            $id = $this->tours_model->add_checklist_template($this->input->post('description'));
            echo json_encode(['id' => $id]);
        }
    }

    public function remove_checklist_item_template($id)
    {
        if (has_permission('checklist_templates', '', 'delete')) {
            $success = $this->tours_model->remove_checklist_item_template($id);
            echo json_encode(['success' => $success]);
        }
    }

    public function init_checklist_items()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $post_data          = $this->input->post();
                $data['tour_id']    = $post_data['tourid'];
                $data['checklists'] = $this->tours_model->get_checklist_items($post_data['tourid']);
                $this->load->view('admin/tours/checklist_items_template', $data);
            }
        }
    }

    public function tour_tracking_stats($tour_id)
    {
        $data['stats'] = json_encode($this->tours_model->tour_tracking_stats($tour_id));
        $this->load->view('admin/tours/tracking_stats', $data);
    }

    public function checkbox_action($listid, $value)
    {
        $this->db->where('id', $listid);
        $this->db->update(db_prefix() . 'tour_checklist_items', [
            'finished' => $value,
        ]);

        if ($this->db->affected_rows() > 0) {
            if ($value == 1) {
                $this->db->where('id', $listid);
                $this->db->update(db_prefix() . 'tour_checklist_items', [
                    'finished_from' => get_staff_user_id(),
                ]);
                hooks()->do_action('tour_checklist_item_finished', $listid);
            }
        }
    }

    public function add_checklist_item()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                echo json_encode([
                    'success' => $this->tours_model->add_checklist_item($this->input->post()),
                ]);
            }
        }
    }

    public function update_checklist_order()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $this->tours_model->update_checklist_order($this->input->post());
            }
        }
    }

    public function delete_checklist_item($id)
    {
        $list = $this->tours_model->get_checklist_item($id);
        if (has_permission('tours', '', 'delete') || $list->addedfrom == get_staff_user_id()) {
            if ($this->input->is_ajax_request()) {
                echo json_encode([
                    'success' => $this->tours_model->delete_checklist_item($id),
                ]);
            }
        }
    }

    public function update_checklist_item()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $desc = $this->input->post('description');
                $desc = trim($desc);
                $this->tours_model->update_checklist_item($this->input->post('listid'), $desc);
                echo json_encode(['can_be_template' => (total_rows(db_prefix() . 'tours_checklist_templates', ['description' => $desc]) == 0)]);
            }
        }
    }

    public function make_public($tour_id)
    {
        if (!has_permission('tours', '', 'edit')) {
            json_encode([
                'success' => false,
            ]);
            die;
        }
        echo json_encode([
            'success'  => $this->tours_model->make_public($tour_id),
            'tourHtml' => $this->get_tour_data($tour_id, true),
        ]);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->tours_model->add_attachment_to_database($this->input->post('tour_id'), $this->input->post('files'), $this->input->post('external'));
        }
    }

    /* Add new tour comment / ajax */
    public function add_tour_comment()
    {
        $data            = $this->input->post();
        $data['content'] = html_purify($this->input->post('content', false));
        if ($this->input->post('no_editor')) {
            $data['content'] = nl2br($this->input->post('content'));
        }
        $comment_id = false;
        if (
            $data['content'] != ''
            || (isset($_FILES['file']['name']) && is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)
        ) {
            $comment_id = $this->tours_model->add_tour_comment($data);
            if ($comment_id) {
                $commentAttachments = handle_tour_attachments_array($data['tourid'], 'file');
                if ($commentAttachments && is_array($commentAttachments)) {
                    foreach ($commentAttachments as $file) {
                        $file['tour_comment_id'] = $comment_id;
                        $this->misc_model->add_attachment_to_database($data['tourid'], 'tour', [$file]);
                    }

                    if (count($commentAttachments) > 0) {
                        $this->db->query('UPDATE ' . db_prefix() . "tour_comments SET content = CONCAT(content, '[tour_attachment]')
                            WHERE id = " . $this->db->escape_str($comment_id));
                    }
                }
            }
        }
        echo json_encode([
            'success'  => $comment_id ? true : false,
            'tourHtml' => $this->get_tour_data($data['tourid'], true),
        ]);
    }

    public function download_files($tour_id, $comment_id = null)
    {
        $tourWhere = 'external IS NULL';

        if ($comment_id) {
            $tourWhere .= ' AND tour_comment_id=' . $this->db->escape_str($comment_id);
        }

        if (!has_permission('tours', '', 'view')) {
            $tourWhere .= ' AND ' . get_tours_where_string(false);
        }

        $files = $this->tours_model->get_tour_attachments($tour_id, $tourWhere);

        if (count($files) == 0) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        $path = get_upload_path_by_type('tour') . $tour_id;

        $this->load->library('zip');

        foreach ($files as $file) {
            $this->zip->read_file($path . '/' . $file['file_name']);
        }

        $this->zip->download('files.zip');
        $this->zip->clear_data();
    }

    /* Add new tour follower / ajax */
    public function add_tour_followers()
    {
        if (has_permission('tours', '', 'edit') || has_permission('tours', '', 'create')) {
            echo json_encode([
                'success'  => $this->tours_model->add_tour_followers($this->input->post()),
                'tourHtml' => $this->get_tour_data($this->input->post('tourid'), true),
            ]);
        }
    }

    /* Add tour assignees / ajax */
    public function add_tour_assignees()
    {
        if (has_permission('tours', '', 'edit') || has_permission('tours', '', 'create')) {
            echo json_encode([
                'success'  => $this->tours_model->add_tour_assignees($this->input->post()),
                'tourHtml' => $this->get_tour_data($this->input->post('tourid'), true),
            ]);
        }
    }

    public function edit_comment()
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['content'] = html_purify($this->input->post('content', false));
            if ($this->input->post('no_editor')) {
                $data['content'] = nl2br(clear_textarea_breaks($this->input->post('content')));
            }
            $success = $this->tours_model->edit_comment($data);
            $message = '';
            if ($success) {
                $message = _l('tour_comment_updated');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'tourHtml' => $this->get_tour_data($data['tour_id'], true),
            ]);
        }
    }

    /* Remove tour comment / ajax */
    public function remove_comment($id)
    {
        echo json_encode([
            'success' => $this->tours_model->remove_comment($id),
        ]);
    }

    /* Remove assignee / ajax */
    public function remove_assignee($id, $tourid)
    {
        if (has_permission('tours', '', 'edit') && has_permission('tours', '', 'create')) {
            $success = $this->tours_model->remove_assignee($id, $tourid);
            $message = '';
            if ($success) {
                $message = _l('tour_assignee_removed');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'tourHtml' => $this->get_tour_data($tourid, true),
            ]);
        }
    }

    /* Remove tour follower / ajax */
    public function remove_follower($id, $tourid)
    {
        if (has_permission('tours', '', 'edit') && has_permission('tours', '', 'create')) {
            $success = $this->tours_model->remove_follower($id, $tourid);
            $message = '';
            if ($success) {
                $message = _l('tour_follower_removed');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'tourHtml' => $this->get_tour_data($tourid, true),
            ]);
        }
    }

    /* Unmark tour as complete / ajax*/
    public function unmark_complete($id)
    {
        if (
            $this->tours_model->is_tour_assignee(get_staff_user_id(), $id)
            || $this->tours_model->is_tour_creator(get_staff_user_id(), $id)
            || has_permission('tours', '', 'edit')
        ) {
            $success = $this->tours_model->unmark_complete($id);

            // Don't do this query if the action is not performed via tour single
            $tourHtml = $this->input->get('single_tour') === 'true' ? $this->get_tour_data($id, true) : '';

            $message = '';
            if ($success) {
                $message = _l('tour_unmarked_as_complete');
            }
            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'tourHtml' => $tourHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'message'  => '',
                'tourHtml' => '',
            ]);
        }
    }

    public function mark_as($status, $id)
    {
        if (
            $this->tours_model->is_tour_assignee(get_staff_user_id(), $id)
            || $this->tours_model->is_tour_creator(get_staff_user_id(), $id)
            || has_permission('tours', '', 'edit')
        ) {
            $success = $this->tours_model->mark_as($status, $id);

            // Don't do this query if the action is not performed via tour single
            $tourHtml = $this->input->get('single_tour') === 'true' ? $this->get_tour_data($id, true) : '';

            $message = '';

            if ($success) {
                $message = _l('tour_marked_as_success', format_tour_status($status, true, true));
            }

            echo json_encode([
                'success'  => $success,
                'message'  => $message,
                'tourHtml' => $tourHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'message'  => '',
                'tourHtml' => '',
            ]);
        }
    }

    public function change_priority($priority_id, $id)
    {
        if (has_permission('tours', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tours', ['priority' => $priority_id]);

            $success = $this->db->affected_rows() > 0 ? true : false;

            // Don't do this query if the action is not performed via tour single
            $tourHtml = $this->input->get('single_tour') === 'true' ? $this->get_tour_data($id, true) : '';
            echo json_encode([
                'success'  => $success,
                'tourHtml' => $tourHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'tourHtml' => $tourHtml,
            ]);
        }
    }

    public function change_milestone($milestone_id, $id)
    {
        if (has_permission('tours', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'tours', ['milestone' => $milestone_id]);

            $success = $this->db->affected_rows() > 0 ? true : false;
            // Don't do this query if the action is not performed via tour single
            $tourHtml = $this->input->get('single_tour') === 'true' ? $this->get_tour_data($id, true) : '';
            echo json_encode([
                'success'  => $success,
                'tourHtml' => $tourHtml,
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'tourHtml' => $tourHtml,
            ]);
        }
    }

    public function tour_single_inline_update($tour_id)
    {
        if (has_permission('tours', '', 'edit')) {
            $post_data = $this->input->post();
            foreach ($post_data as $key => $val) {
                $this->db->where('id', $tour_id);
                $this->db->update(db_prefix() . 'tours', [$key => to_sql_date($val)]);
            }
        }
    }

    /* Delete tour from database */
    public function delete_tour($id)
    {
        if (!has_permission('tours', '', 'delete')) {
            access_denied('tours');
        }
        $success = $this->tours_model->delete_tour($id);
        $message = _l('problem_deleting', _l('tour_lowercase'));
        if ($success) {
            $message = _l('deleted', _l('tour'));
            set_alert('success', $message);
        } else {
            set_alert('warning', $message);
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'tours/index') !== false || strpos($_SERVER['HTTP_REFERER'], 'tours/view') !== false) {
            redirect(admin_url('tours'));
        } elseif (preg_match("/bookings\/view\/[1-9]+/", $_SERVER['HTTP_REFERER'])) {
            $booking_url = explode('?', $_SERVER['HTTP_REFERER']);
            redirect($booking_url[0] . '?group=booking_tours');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * Remove tour attachment
     * @since  Version 1.0.1
     * @param  mixed $id attachment it
     * @return json
     */
    public function remove_tour_attachment($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->tours_model->remove_tour_attachment($id));
        }
    }

    /**
     * Upload tour attachment
     * @since  Version 1.0.1
     */
    public function upload_file()
    {
        if ($this->input->post()) {
            $tourid  = $this->input->post('tourid');
            $files   = handle_tour_attachments_array($tourid, 'file');
            $success = false;

            if ($files) {
                $i   = 0;
                $len = count($files);
                foreach ($files as $file) {
                    $success = $this->tours_model->add_attachment_to_database($tourid, [$file], false, ($i == $len - 1 ? true : false));
                    $i++;
                }
            }

            echo json_encode([
                'success'  => $success,
                'tourHtml' => $this->get_tour_data($tourid, true),
            ]);
        }
    }

    public function timer_tracking()
    {
        $tour_id   = $this->input->post('tour_id');
        $adminStop = $this->input->get('admin_stop') && is_admin() ? true : false;

        if ($adminStop) {
            $this->session->set_flashdata('tour_single_timesheets_open', true);
        }

        echo json_encode([
            'success' => $this->tours_model->timer_tracking(
                $tour_id,
                $this->input->post('timer_id'),
                nl2br($this->input->post('note')),
                $adminStop
            ),
            'tourHtml' => $this->input->get('single_tour') === 'true' ? $this->get_tour_data($tour_id, true) : '',
            'timers'   => $this->get_staff_started_timers(true),
        ]);
    }

    public function delete_user_unfinished_timesheet($id)
    {
        $this->db->where('id', $id);
        $timesheet = $this->db->get(db_prefix() . 'tourstimers')->row();
        if ($timesheet && $timesheet->end_time == null && $timesheet->staff_id == get_staff_user_id()) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'tourstimers');
        }
        echo json_encode(['timers' => $this->get_staff_started_timers(true)]);
    }

    public function delete_timesheet($id)
    {
        if (has_permission('tours', '', 'delete') || has_permission('bookings', '', 'delete') || total_rows(db_prefix() . 'tourstimers', ['staff_id' => get_staff_user_id(), 'id' => $id]) > 0) {
            $alert_type = 'warning';
            $success    = $this->tours_model->delete_timesheet($id);
            if ($success) {
                $this->session->set_flashdata('tour_single_timesheets_open', true);
                $message = _l('deleted', _l('booking_timesheet'));
                set_alert('success', $message);
            }
            if (!$this->input->is_ajax_request()) {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function log_time()
    {
        $success = $this->tours_model->timesheet($this->input->post());
        if ($success === true) {
            $this->session->set_flashdata('tour_single_timesheets_open', true);
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

    public function update_tags()
    {
        if (has_permission('tours', '', 'create') || has_permission('tours', '', 'edit')) {
            handle_tags_save($this->input->post('tags'), $this->input->post('tour_id'), 'tour');
        }
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_tours');
        $total_deleted = 0;
        if ($this->input->post()) {
            $status    = $this->input->post('status');
            $ids       = $this->input->post('ids');
            $tags      = $this->input->post('tags');
            $assignees = $this->input->post('assignees');
            $milestone = $this->input->post('milestone');
            $priority  = $this->input->post('priority');
            $is_admin  = is_admin();
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if (has_permission('tours', '', 'delete')) {
                            if ($this->tours_model->delete_tour($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status) {
                            if (
                                $this->tours_model->is_tour_creator(get_staff_user_id(), $id)
                                || $is_admin
                                || $this->tours_model->is_tour_assignee(get_staff_user_id(), $id)
                            ) {
                                $this->tours_model->mark_as($status, $id);
                            }
                        }
                        if ($priority || $milestone) {
                            $update = [];
                            if ($priority) {
                                $update['priority'] = $priority;
                            }
                            if ($milestone) {
                                $update['milestone'] = $milestone;
                            }
                            $this->db->where('id', $id);
                            $this->db->update(db_prefix() . 'tours', $update);
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'tour');
                        }
                        if ($assignees) {
                            $notifiedUsers = [];
                            foreach ($assignees as $user_id) {
                                if (!$this->tours_model->is_tour_assignee($user_id, $id)) {
                                    $this->db->select('rel_type,rel_id');
                                    $this->db->where('id', $id);
                                    $tour = $this->db->get(db_prefix() . 'tours')->row();
                                    if ($tour->rel_type == 'booking') {
                                        // User is we are trying to assign the tour is not booking member
                                        if (total_rows(db_prefix() . 'booking_members', ['booking_id' => $tour->rel_id, 'staff_id' => $user_id]) == 0) {
                                            $this->db->insert(db_prefix() . 'booking_members', ['booking_id' => $tour->rel_id, 'staff_id' => $user_id]);
                                        }
                                    }
                                    $this->db->insert(db_prefix() . 'tour_assigned', [
                                        'staffid'       => $user_id,
                                        'tourid'        => $id,
                                        'assigned_from' => get_staff_user_id(),
                                    ]);
                                    if ($user_id != get_staff_user_id()) {
                                        $notification_data = [
                                            'description' => 'not_tour_assigned_to_you',
                                            'touserid'    => $user_id,
                                            'link'        => '#tourid=' . $id,
                                        ];

                                        $notification_data['additional_data'] = serialize([
                                            get_tour_subject_by_id($id),
                                        ]);
                                        if (add_notification($notification_data)) {
                                            array_push($notifiedUsers, $user_id);
                                        }
                                    }
                                }
                            }
                            pusher_trigger_notification($notifiedUsers);
                        }
                    }
                }
            }
            if ($this->input->post('mass_delete')) {
                set_alert('success', _l('total_tours_deleted', $total_deleted));
            }
        }
    }

    public function gantt_date_update($tour_id)
    {
        if (staff_can('edit', 'tours')) {
            $post_data = $this->input->post();
            foreach ($post_data as $key => $val) {
                $this->db->where('id', $tour_id);
                $this->db->update(db_prefix() . 'tours', [$key => $val]);
            }
        }
    }

    public function get_tour_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $tours_where = [];
            if (!staff_can('view', 'tours')) {
                $tours_where = get_tours_where_string(false);
            }
            $tour = $this->tours_model->get($id, $tours_where);
            if (!$tour) {
                header('HTTP/1.0 404 Not Found');
                echo 'Tour not found';
                die();
            }
            echo json_encode($tour);
        }
    }
}
