<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
    }

    /* This is admin dashboard view */
    public function index()
    {
        close_setup_menu();
        $this->load->model('departments_model');
        $this->load->model('todo_model');
        $data['departments'] = $this->departments_model->get();

        $data['todos'] = $this->todo_model->get_todo_items(0);
        // Only show last 5 finished todo items
        $this->todo_model->setTodosLimit(5);
        $data['todos_finished']            = $this->todo_model->get_todo_items(1);
        $data['upcoming_events_next_week'] = $this->dashboard_model->get_upcoming_events_next_week();
        $data['upcoming_events']           = $this->dashboard_model->get_upcoming_events();
        $data['title']                     = _l('dashboard_string');

        $this->load->model('contracts_model');
        $data['expiringContracts'] = $this->contracts_model->get_contracts_about_to_expire(get_staff_user_id());

        $this->load->model('currencies_model');
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['activity_log']  = $this->misc_model->get_activity_log();
        // Tickets charts
        $tickets_awaiting_reply_by_status     = $this->dashboard_model->tickets_awaiting_reply_by_status();
        $tickets_awaiting_reply_by_department = $this->dashboard_model->tickets_awaiting_reply_by_department();

        $data['tickets_reply_by_status']              = json_encode($tickets_awaiting_reply_by_status);
        $data['tickets_awaiting_reply_by_department'] = json_encode($tickets_awaiting_reply_by_department);

        $data['tickets_reply_by_status_no_json']              = $tickets_awaiting_reply_by_status;
        $data['tickets_awaiting_reply_by_department_no_json'] = $tickets_awaiting_reply_by_department;

        $data['projects_status_stats'] = json_encode($this->dashboard_model->projects_status_stats());
        $data['leads_status_stats']    = json_encode($this->dashboard_model->leads_status_stats());
        $data['google_ids_calendars']  = $this->misc_model->get_google_calendar_ids();
        $data['bodyclass']             = 'dashboard invoices-total-manual';
        $this->load->model('announcements_model');
        $data['staff_announcements']             = $this->announcements_model->get();
        $data['total_undismissed_announcements'] = $this->announcements_model->get_total_undismissed_announcements();

        $this->load->model('projects_model');
        $data['projects_activity'] = $this->projects_model->get_activity('', hooks()->apply_filters('projects_activity_dashboard_limit', 20));
        add_calendar_assets();
        $this->load->model('utilities_model');
        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $wps_currency = 'undefined';
        if (is_using_multiple_currencies()) {
            $wps_currency = $data['base_currency']->id;
        }
        $data['weekly_payment_stats'] = json_encode($this->dashboard_model->get_weekly_payments_statistics($wps_currency));

        $data['dashboard'] = true;

        $data['user_dashboard_visibility'] = get_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility');

        if (!$data['user_dashboard_visibility']) {
            $data['user_dashboard_visibility'] = [];
        } else {
            $data['user_dashboard_visibility'] = unserialize($data['user_dashboard_visibility']);
        }
        $data['user_dashboard_visibility'] = json_encode($data['user_dashboard_visibility']);

        $data = hooks()->apply_filters('before_dashboard_render', $data);
        $this->load->view('admin/dashboard/dashboard', $data);
    }


    /* Get recent activity data for dashboard widget */
    public function get_recent_activity_data()
    {
        if ($this->input->is_ajax_request()) {
            $type = $this->input->post('type');
            $html = '';
            
            $this->load->model('proposals_model');
            $this->load->model('estimates_model');
            $this->load->model('invoices_model');
            $this->load->model('credit_notes_model');
            
            $data = [];
            
            switch ($type) {
                case 'proposals':
                    $this->db->select('id, subject, date, status, total, currency');
                    $this->db->from(db_prefix() . 'proposals');
                    $this->db->order_by('datecreated', 'desc');
                    $this->db->limit(5);
                    $data = $this->db->get()->result_array();
                    break;
                case 'estimates':
                    $this->db->select('id, number, date, status, total, currency, clientid, project_id');
                    $this->db->from(db_prefix() . 'estimates');
                    $this->db->order_by('datecreated', 'desc');
                    $this->db->limit(5);
                    $data = $this->db->get()->result_array();
                    break;
                case 'invoices':
                    $this->db->select('id, number, date, status, total, currency, clientid, project_id');
                    $this->db->from(db_prefix() . 'invoices');
                    $this->db->order_by('datecreated', 'desc');
                    $this->db->limit(5);
                    $data = $this->db->get()->result_array();
                    break;
                case 'credit_notes':
                    $this->db->select('id, number, date, status, total, currency, clientid, project_id');
                    $this->db->from(db_prefix() . 'creditnotes');
                    $this->db->order_by('datecreated', 'desc');
                    $this->db->limit(5);
                    $data = $this->db->get()->result_array();
                    break;
            }
            log_message('error', 'Recent Activity Widget: Type=' . $type . ' Count=' . count($data));
            
            if (count($data) > 0) {
                foreach ($data as $item) {
                    $html .= '<div class="feed-item">';
                    $html .= '<div class="date"><span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($item['date']) . '">' . _d($item['date']) . '</span></div>';
                    $html .= '<div class="text">';
                    
                    if ($type == 'proposals') {
                        $html .= '<a href="' . admin_url('proposals/list_proposals/' . $item['id']) . '">' . format_proposal_number($item['id']) . '</a>';
                        $html .= ' - ' . $item['subject'];
                        $html .= '<br>';
                        $html .= '<span class="label" style="color:'.format_proposal_status($item['status'], '', false).';border:1px solid '.format_proposal_status($item['status'], '', false).'">' . format_proposal_status($item['status'], '', true) . '</span>';
                        $html .= ' <span class="text-muted">' . app_format_money($item['total'], get_currency($item['currency'])) . '</span>';
                    } elseif ($type == 'estimates') {
                        $html .= '<a href="' . admin_url('estimates/list_estimates/' . $item['id']) . '">' . format_estimate_number($item['id']) . '</a>';
                         if(get_option('estimate_prefix') != ""){
                            $html = str_replace(get_option('estimate_prefix'), "", $html);
                         }
                        $html .= '<br>';
                        $html .= '<span class="label" style="color:'.format_estimate_status($item['status'], '', false).';border:1px solid '.format_estimate_status($item['status'], '', false).'">' . format_estimate_status($item['status'], '', true) . '</span>';
                        $html .= ' <span class="text-muted">' . app_format_money($item['total'], get_currency($item['currency'])) . '</span>';
                    } elseif ($type == 'invoices') {
                        $html .= '<a href="' . admin_url('invoices/list_invoices/' . $item['id']) . '">' . format_invoice_number($item['id']) . '</a>';
                        $html .= '<br>';
                        $html .= '<span class="label" style="color:'.format_invoice_status($item['status'], '', false).';border:1px solid '.format_invoice_status($item['status'], '', false).'">' . format_invoice_status($item['status'], '', true) . '</span>';
                         $html .= ' <span class="text-muted">' . app_format_money($item['total'], get_currency($item['currency'])) . '</span>';
                    } elseif ($type == 'credit_notes') {
                        $html .= '<a href="' . admin_url('credit_notes/list_credit_notes/' . $item['id']) . '">' . format_credit_note_number($item['id']) . '</a>';
                        $html .= '<br>';
                        $html .= '<span class="label" style="color:'.format_credit_note_status($item['status'], '', false).';border:1px solid '.format_credit_note_status($item['status'], '', false).'">' . format_credit_note_status($item['status'], '', true) . '</span>';
                        $html .= ' <span class="text-muted">' . app_format_money($item['total'], get_currency($item['currency'])) . '</span>';
                    }
                    
                    $html .= '</div>';
                    $html .= '</div>';
                }
            } else {
                 $html .= '<p class="text-muted text-center">' . _l('no_activity_found') . '</p>';
            }
            
            echo $html;
            die();
        }
    }

    /* Chart weekly payments statistics on home page / ajax */
    public function weekly_payments_statistics($currency)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->dashboard_model->get_weekly_payments_statistics($currency));
            die();
        }
    }
}
