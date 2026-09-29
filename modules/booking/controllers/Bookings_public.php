<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bookings_public extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('bookings_model');
        $this->load->model('tours_model');
    }
    
    public function index($status = '')
    {
        // if (!has_contact_permission('bookings')) {
        //     set_alert('warning', _l('access_denied'));
        //     redirect(site_url());
        // }
        if (!get_client_user_id()) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
        $data['booking_statuses'] = $this->bookings_model->get_booking_statuses();

        $where = 'clientid=' . get_client_user_id();

        if (is_numeric($status)) {
            $where .= ' AND status=' . $this->db->escape_str($status);
        } else {
            $listStatusesIds = [];
            $where .= ' AND status IN (';
            foreach ($data['booking_statuses'] as $bookingStatus) {
                if (isset($bookingStatus['filter_default']) && $bookingStatus['filter_default'] == true) {
                    $listStatusesIds[] = $bookingStatus['id'];
                    $where .= $this->db->escape_str($bookingStatus['id']) . ',';
                }
            }
            $where = rtrim($where, ',');
            $where .= ')';
        }

        $data['list_statuses'] = is_numeric($status) ? [$status] : $listStatusesIds;
        $data['bookings']      = $this->bookings_model->get('', $where);
        $data['title']         = _l('clients_my_bookings');
        
        $handle = fopen("asdfasdf_bookings.txt", "w");
        fwrite($handle, print_r($data, true));
        fclose($handle);
        
        $this->data($data);
        $this->view('clients/bookings/bookings');
        $this->layout();
    }
    public function booking($id)
    {
        // if (!has_contact_permission('bookings')) {
        //     set_alert('warning', _l('access_denied'));
        //     redirect(site_url());
        // }
        
        if (!get_client_user_id()) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }

        $booking = $this->bookings_model->get($id, [
            'clientid' => get_client_user_id(),
        ]);

        if (!$booking) {
            show_404();
        }

        $data['booking']                               = $booking;
        $data['booking']->settings->available_features = unserialize($data['booking']->settings->available_features);

        $data['title'] = $data['booking']->name;
        if ($this->input->post('action')) {
            $action = $this->input->post('action');

            switch ($action) {
                  case 'new_tour':
                  case 'edit_tour':

                    $data    = $this->input->post();
                    $tour_id = false;
                    if (isset($data['tour_id'])) {
                        $tour_id = $data['tour_id'];
                        unset($data['tour_id']);
                    }

                    $data['rel_type']    = 'booking';
                    $data['rel_id']      = $booking->id;
                    $data['description'] = nl2br($data['description']);

                    $assignees = isset($data['assignees']) ? $data['assignees'] : [];
                    if (isset($data['assignees'])) {
                        unset($data['assignees']);
                    }
                    unset($data['action']);

                    if (!$tour_id) {
                        $tour_id = $this->tours_model->add($data, true);
                        if ($tour_id) {
                            foreach ($assignees as $assignee) {
                                $this->tours_model->add_tour_assignees(['tourid' => $tour_id, 'assignee' => $assignee], false, true);
                            }
                            $uploadedFiles = handle_tour_attachments_array($tour_id);
                            if ($uploadedFiles && is_array($uploadedFiles)) {
                                foreach ($uploadedFiles as $file) {
                                    $file['contact_id'] = get_contact_user_id();
                                    $this->misc_model->add_attachment_to_database($tour_id, 'tour', [$file]);
                                }
                            }
                            set_alert('success', _l('added_successfully', _l('tour')));
                            redirect(site_url('booking/bookings_public/booking/' . $booking->id . '?group=booking_tours&tourid=' . $tour_id));
                        }
                    } else {
                        if ($booking->settings->edit_tours == 1
                            && total_rows(db_prefix() . 'tours', ['is_added_from_contact' => 1, 'addedfrom' => get_contact_user_id(), 'billed' => 0]) > 0) {
                            $affectedRows = 0;
                            $updated      = $this->tours_model->update($data, $tour_id, true);
                            if ($updated) {
                                $affectedRows++;
                            }

                            $currentAssignees    = $this->tours_model->get_tour_assignees($tour_id);
                            $currentAssigneesIds = [];
                            foreach ($currentAssignees as $assigned) {
                                array_push($currentAssigneesIds, $assigned['assigneeid']);
                            }

                            $totalAssignees = count($assignees);

                            /**
                             * In case when contact created the tour and then was able to view team members
                             * Now in this case he still can view team members and can edit them
                             */
                            if ($totalAssignees == 0 && $booking->settings->view_team_members == 1) {
                                $this->db->where('tourid', $tour_id);
                                $this->db->delete(db_prefix() . 'tour_assigned');
                            } elseif ($totalAssignees > 0 && $booking->settings->view_team_members == 1) {
                                foreach ($currentAssignees as $assigned) {
                                    if (!in_array($assigned['assigneeid'], $assignees)) {
                                        if ($this->tours_model->remove_assignee($assigned['id'], $tour_id)) {
                                            $affectedRows++;
                                        }
                                    }
                                }
                                foreach ($assignees as $assignee) {
                                    if (!$this->tours_model->is_tour_assignee($assignee, $tour_id)) {
                                        if ($this->tours_model->add_tour_assignees(['tourid' => $tour_id, 'assignee' => $assignee], false, true)) {
                                            $affectedRows++;
                                        }
                                    }
                                }
                            }
                            if ($affectedRows > 0) {
                                set_alert('success', _l('updated_successfully', _l('tour')));
                            }
                            redirect(site_url('booking/bookings_public/booking/' . $booking->id . '?group=booking_tours&tourid=' . $tour_id));
                        }
                    }

                    redirect(site_url('booking/bookings_public/booking/' . $booking->id . '?group=booking_tours'));

                    break;
                case 'discussion_comments':
                    echo json_encode($this->bookings_model->get_discussion_comments($this->input->post('discussion_id'), $this->input->post('discussion_type')));
                    die;
                case 'new_discussion_comment':
                    echo json_encode($this->bookings_model->add_discussion_comment($this->input->post(), $this->input->post('discussion_id'), $this->input->post('discussion_type')));
                    die;

                    break;
                case 'update_discussion_comment':
                    echo json_encode($this->bookings_model->update_discussion_comment($this->input->post(), $this->input->post('discussion_id')));
                    die;

                    break;
                case 'delete_discussion_comment':
                    echo json_encode($this->bookings_model->delete_discussion_comment($this->input->post('id')));
                    die;

                    break;
                case 'new_discussion':
                    $discussion_data = $this->input->post();
                    unset($discussion_data['action']);
                    $success = $this->bookings_model->add_discussion($discussion_data);
                    if ($success) {
                        set_alert('success', _l('added_successfully', _l('booking_discussion')));
                    }
                    redirect(site_url('booking/bookings_public/booking/' . $id . '?group=booking_discussions'));

                    break;
                case 'upload_file':
                    handle_booking_file_uploads($id);
                    die;

                    break;
                case 'booking_file_dropbox': // deprecated
                case 'booking_external_file':
                        $data                        = [];
                        $data['booking_id']          = $id;
                        $data['files']               = $this->input->post('files');
                        $data['external']            = $this->input->post('external');
                        $data['visible_to_customer'] = 1;
                        $data['contact_id']          = get_contact_user_id();
                        $this->bookings_model->add_external_file($data);
                die;

                break;
                case 'get_file':
                    $file_data['discussion_user_profile_image_url'] = contact_profile_image_url(get_contact_user_id());
                    $file_data['current_user_is_admin']             = false;
                    $file_data['file']                              = $this->bookings_model->get_file($this->input->post('id'), $this->input->post('booking_id'));

                    if (!$file_data['file']) {
                        header('HTTP/1.0 404 Not Found');
                        die;
                    }
                    echo get_template_part('bookings/file', $file_data, true);
                    die;

                    break;
                case 'update_file_data':
                    $file_data = $this->input->post();
                    unset($file_data['action']);
                    $this->bookings_model->update_file_data($file_data);

                    break;
                case 'upload_tour_file':
                    $tourid = $this->input->post('tour_id');
                    $files  = handle_tour_attachments_array($tourid, 'file');
                    if ($files) {
                        $i   = 0;
                        $len = count($files);
                        foreach ($files as $file) {
                            $file['contact_id'] = get_contact_user_id();
                            $file['staffid']    = 0;
                            $this->tours_model->add_attachment_to_database($tourid, [$file], false, ($i == $len - 1 ? true : false));
                            $i++;
                        }
                    }
                    die;

                    break;
                case 'add_tour_external_file':
                    $tourid                = $this->input->post('tour_id');
                    $file                  = $this->input->post('files');
                    $file[0]['contact_id'] = get_contact_user_id();
                    $file[0]['staffid']    = 0;
                    $this->tours_model->add_attachment_to_database($this->input->post('tour_id'), $file, $this->input->post('external'));
                    die;

                    break;
                case 'new_tour_comment':
                    $comment_data            = $this->input->post();
                    $comment_data['content'] = nl2br($comment_data['content']);
                    $comment_id              = $this->tours_model->add_tour_comment($comment_data);
                    $url                     = site_url('booking/bookings_public/booking/' . $id . '?group=booking_tours&tourid=' . $comment_data['tourid']);

                    if ($comment_id) {
                        set_alert('success', _l('tour_comment_added'));
                        $url .= '#comment_' . $comment_id;
                    }

                    redirect($url);

                    break;
                default:
                    redirect(site_url('booking/bookings_public/booking/' . $id));

                    break;
            }
        }
        if (!$this->input->get('group')) {
            $group = 'booking_overview';
        } else {
            $group = $this->input->get('group');
        }
        $data['booking_status'] = get_booking_status_by_id($data['booking']->status);
        if ($group != 'edit_tour') {
            if ($group == 'booking_overview') {
                $percent          = $this->bookings_model->calc_progress($id);
                @$data['percent'] = $percent / 100;
                $this->load->helper('date');
                $data['booking_total_days']        = round((human_to_unix($data['booking']->deadline . ' 00:00') - human_to_unix($data['booking']->start_date . ' 00:00')) / 3600 / 24);
                $data['booking_days_left']         = $data['booking_total_days'];
                $data['booking_time_left_percent'] = 100;
                if ($data['booking']->deadline) {
                    if (human_to_unix($data['booking']->start_date . ' 00:00') < time() && human_to_unix($data['booking']->deadline . ' 00:00') > time()) {
                        $data['booking_days_left'] = round((human_to_unix($data['booking']->deadline . ' 00:00') - time()) / 3600 / 24);

                        $data['booking_time_left_percent'] = $data['booking_days_left'] / $data['booking_total_days'] * 100;
                        $data['booking_time_left_percent'] = round($data['booking_time_left_percent'], 2);
                    }
                    if (human_to_unix($data['booking']->deadline . ' 00:00') < time()) {
                        $data['booking_days_left']         = 0;
                        $data['booking_time_left_percent'] = 0;
                    }
                }
                $total_tours = total_rows(db_prefix() . 'tours', [
                    'rel_id'            => $id,
                    'rel_type'          => 'booking',
                    'visible_to_client' => 1,
                ]);
                $total_tours = hooks()->apply_filters('client_booking_total_tours', $total_tours, $id);

                $data['tours_not_completed'] = total_rows(db_prefix() . 'tours', [
                'status !='         => 5,
                'rel_id'            => $id,
                'rel_type'          => 'booking',
                'visible_to_client' => 1,
            ]);

                $data['tours_not_completed'] = hooks()->apply_filters('client_booking_tours_not_completed', $data['tours_not_completed'], $id);

                $data['tours_completed'] = total_rows(db_prefix() . 'tours', [
                'status'            => 5,
                'rel_id'            => $id,
                'rel_type'          => 'booking',
                'visible_to_client' => 1,
            ]);
                $data['tours_completed'] = hooks()->apply_filters('client_booking_tours_completed', $data['tours_completed'], $id);

                $data['total_tours']                  = $total_tours;
                $data['tours_not_completed_progress'] = ($total_tours > 0 ? number_format(($data['tours_completed'] * 100) / $total_tours, 2) : 0);
                $data['tours_not_completed_progress'] = round($data['tours_not_completed_progress'], 2);
            } elseif ($group == 'new_tour') {
                if ($booking->settings->create_tours == 0) {
                    redirect(site_url('booking/bookings_public/booking/' . $booking->id));
                }
                $data['milestones'] = $this->bookings_model->get_milestones($id);
            } elseif ($group == 'booking_gantt') {
                $data['gantt_data'] = $this->bookings_model->get_gantt_data($id);
            } elseif ($group == 'booking_discussions') {
                if ($this->input->get('discussion_id')) {
                    $data['discussion_user_profile_image_url'] = contact_profile_image_url(get_contact_user_id());
                    $data['discussion']                        = $this->bookings_model->get_discussion($this->input->get('discussion_id'), $id);
                    $data['current_user_is_admin']             = false;
                }
                $data['discussions'] = $this->bookings_model->get_discussions($id);
            } elseif ($group == 'booking_files') {
                $data['files'] = $this->bookings_model->get_files($id);
            } elseif ($group == 'booking_tours') {
                $data['tours_statuses'] = $this->tours_model->get_statuses();
                $data['booking_tours']  = $this->bookings_model->get_tours($id);
            } elseif ($group == 'booking_contracts') {
                $data['contracts'] = [];
                if (has_contact_permission('contracts')) {
                    $data['contracts'] = $this->contracts_model->get('', [
                            'client'     => get_client_user_id(),
                            'booking_id' => $id,
                        ]);
                }
            } elseif ($group == 'booking_activity') {
                $data['activity'] = $this->bookings_model->get_activity($id);
            } elseif ($group == 'booking_milestones') {
                $data['milestones'] = $this->bookings_model->get_milestones($id);
            } elseif ($group == 'booking_invoices') {
                $data['invoices'] = [];
                if (has_contact_permission('invoices')) {
                    $whereInvoices = [
                            'clientid'   => get_client_user_id(),
                            'booking_id' => $id,
                        ];
                    if (get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
                        $whereInvoices['status !='] = 6;
                    }
                    $data['invoices'] = $this->invoices_model->get('', $whereInvoices);
                }
            } elseif ($group == 'booking_tickets') {
                $data['tickets'] = [];
                if (has_contact_permission('support')) {
                    $where_tickets = [
                        db_prefix() . 'tickets.userid' => get_client_user_id(),
                        'booking_id'                   => $id,
                    ];

                    if (!!can_logged_in_contact_view_all_tickets()) {
                        $where_tickets[db_prefix() . 'tickets.contactid'] = get_contact_user_id();
                    }

                    $data['tickets']                 = $this->tickets_model->get('', $where_tickets);
                    $data['show_submitter_on_table'] = show_ticket_submitter_on_clients_area_table();
                }
            } elseif ($group == 'booking_estimates') {
                $data['estimates'] = [];
                if (has_contact_permission('estimates')) {
                    $data['estimates'] = $this->estimates_model->get('', [
                            'clientid'   => get_client_user_id(),
                            'booking_id' => $id,
                        ]);
                }
            } elseif ($group == 'booking_timesheets') {
                $data['timesheets'] = $this->bookings_model->get_timesheets($id);
            }

            if ($this->input->get('tourid')) {
                $data['view_tour'] = $this->tours_model->get($this->input->get('tourid'), [
                    'rel_id'   => $booking->id,
                    'rel_type' => 'booking',
                ]);

                $data['title'] = $data['view_tour']->name;
            }
        } elseif ($group == 'edit_tour') {
            $data['milestones'] = $this->bookings_model->get_milestones($id);
            $data['tour']       = $this->tours_model->get($this->input->get('tourid'), [
                    'rel_id'                => $booking->id,
                    'rel_type'              => 'booking',
                    'addedfrom'             => get_contact_user_id(),
                    'is_added_from_contact' => 1,
                ]);
        }

        $data['group']    = $group;
        $data['currency'] = $this->bookings_model->get_currency($id);
        $data['members']  = $this->bookings_model->get_booking_members($id);

        $this->data($data);
        $this->view('clients/bookings/booking');
        $this->layout();
    }
}
