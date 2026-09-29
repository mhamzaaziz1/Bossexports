<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('quotes_model');
        $this->load->model('currencies_model');
        $this->load->model('tours_model');
    }

    public function index($quote_id = '')
    {
        $this->list_quotes($quote_id);
    }

    public function list_quotes($quote_id = '')
    {
        close_setup_menu();

        if (!has_permission('quotes', '', 'view') && !has_permission('quotes', '', 'view_own') && get_option('allow_staff_view_estimates_assigned') == 0) {
            access_denied('quotes');
        }

        $isPipeline = $this->session->userdata('quotes_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('quotes_pipeline');
            $data['bodyclass']       = 'quotes-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($quote_id)) {
                $data['quoteid'] = $quote_id;
            } else {
                $data['quoteid'] = $this->session->flashdata('quoteid');
            }

            $this->load->view('admin/quotes/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['quote_id']           = $quote_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('quotes');
            $data['statuses']              = $this->quotes_model->get_statuses();
            $data['quotes_sale_agents'] = $this->quotes_model->get_sale_agents();
            $data['years']                 = $this->quotes_model->get_quotes_years();
            $this->load->view('admin/quotes/manage', $data);
        }
    }

    public function table()
    {
        if (!has_permission('quotes', '', 'view')
            && !has_permission('quotes', '', 'view_own')
            && get_option('allow_staff_view_quotes_assigned') == 0) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/quotes'));
    }

    public function quote_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data(module_views_path(BOOKING_MODULE_NAME, 'admin/tables/quotes_relations'), [
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type,
        ]);
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->quotes_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('quotes', '', 'delete')) {
            $this->quotes_model->clear_signature($id);
        }

        redirect(admin_url('booking/quotes/list_quotes/' . $id));
    }

    public function sync_data()
    {
        if (has_permission('quotes', '', 'create') || has_permission('quotes', '', 'edit')) {
            $has_permission_view = has_permission('quotes', '', 'view');

            $this->db->where('rel_id', $this->input->post('rel_id'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'quotes', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('sync_quotes_up_to_date'),
                ]);
            }
        }
    }

    public function quote($id = '')
    {
        if ($this->input->post()) {
            $quote_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('quotes', '', 'create')) {
                    access_denied('quotes');
                }
                $id = $this->quotes_model->add($quote_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('quote')));
                    if ($this->set_quote_pipeline_autoload($id)) {
                        redirect(admin_url('booking/quotes'));
                    } else {
                        redirect(admin_url('booking/quotes/list_quotes/' . $id));
                    }
                }
            } else {
                if (!has_permission('quotes', '', 'edit')) {
                    access_denied('quotes');
                }
                $success = $this->quotes_model->update($quote_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('quote')));
                }
                if ($this->set_quote_pipeline_autoload($id)) {
                    redirect(admin_url('booking/quotes'));
                } else {
                    redirect(admin_url('booking/quotes/list_quotes/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('quote_lowercase'));
        } else {
            $data['quote'] = $this->quotes_model->get($id);

            if (!$data['quote'] || !user_can_view_quote($id)) {
                blank_page(_l('quote_not_found'));
            }

            $data['estimate']    = $data['quote'];
            $data['is_quote'] = true;
            $title               = _l('edit', _l('quote_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->quotes_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/quotes/quote', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/quotes/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_quote($id);
        if (!$canView) {
            access_denied('quotes');
        } else {
            if (!has_permission('quotes', '', 'view') && !has_permission('quotes', '', 'view_own') && $canView == false) {
                access_denied('quotes');
            }
        }

        $success = $this->quotes_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_quote_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('booking/quotes/list_quotes/' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'quotes', get_acceptance_info_array(true));
        }

        redirect(admin_url('booking/quotes/list_quotes/' . $id));
    }

    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('booking/quotes'));
        }

        $canView = user_can_view_quote($id);
        if (!$canView) {
            access_denied('quotes');
        } else {
            if (!has_permission('quotes', '', 'view') && !has_permission('quotes', '', 'view_own') && $canView == false) {
                access_denied('quotes');
            }
        }

        $quote = $this->quotes_model->get($id);

        try {
            $pdf = quote_pdf($quote);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $quote_number = format_quote_number($id);
        $pdf->Output($quote_number . '.pdf', $type);
    }

    public function get_quote_data_ajax($id, $to_return = false)
    {
        if (!has_permission('quotes', '', 'view') && !has_permission('quotes', '', 'view_own') && get_option('allow_staff_view_quotes_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $quote = $this->quotes_model->get($id, [], true);

        if (!$quote || !user_can_view_quote($id)) {
            echo _l('quote_not_found');
            die;
        }

        $this->app_mail_template->set_rel_id($quote->id);
        $params = array();
        $params[] = BOOKING_MODULE_NAME ;
        $data = prepare_mail_preview_data('quote_send_to_customer', $quote->email, $params);

        $merge_fields = [];

        $merge_fields[] = [
            [
                'name' => 'Items Table',
                'key'  => '{quote_items}',
            ],
        ];

        $merge_fields = array_merge($merge_fields, $this->app_merge_fields->get_flat('quotes', 'other', '{email_signature}'));

        $data['quote_statuses']     = $this->quotes_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['quote_merge_fields'] = $merge_fields;
        $data['quote']              = $quote;
        $data['totalNotes']            = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'quote']);
        if ($to_return == false) {
            $this->load->view('admin/quotes/quotes_preview_template', $data);
        } else {
            return $this->load->view('admin/quotes/quotes_preview_template', $data, true);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_quote($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'quote', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_quote($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'quote');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function convert_to_estimate($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        if ($this->input->post()) {
            $this->load->model('estimates_model');
            $estimate_id = $this->estimates_model->add($this->input->post());
            if ($estimate_id) {
                set_alert('success', _l('quote_converted_to_estimate_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'quotes', [
                    'estimate_id' => $estimate_id,
                    'status'      => 3,
                ]);
                log_activity('Quote Converted to Estimate [EstimateID: ' . $estimate_id . ', QuoteID: ' . $id . ']');

                hooks()->do_action('quote_converted_to_estimate', ['quote_id' => $id, 'estimate_id' => $estimate_id]);

                redirect(admin_url('estimates/estimate/' . $estimate_id));
            } else {
                set_alert('danger', _l('quote_converted_to_estimate_fail'));
            }
            if ($this->set_quote_pipeline_autoload($id)) {
                redirect(admin_url('booking/quotes'));
            } else {
                redirect(admin_url('booking/quotes/list_quotes/' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('quote_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'quotes', [
                    'invoice_id' => $invoice_id,
                    'status'     => 3,
                ]);
                log_activity('Quote Converted to Invoice [InvoiceID: ' . $invoice_id . ', QuoteID: ' . $id . ']');
                hooks()->do_action('quote_converted_to_invoice', ['quote_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('quote_converted_to_invoice_fail'));
            }
            if ($this->set_quote_pipeline_autoload($id)) {
                redirect(admin_url('booking/quotes'));
            } else {
                redirect(admin_url('booking/quotes/list_quotes/' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
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

        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['quote']       = $this->quotes_model->get($id);
        $data['billable_tours'] = [];
        $data['add_items']      = $this->_parse_items($data['quote']);

        if ($data['quote']->rel_type == 'lead') {
            $this->db->where('leadid', $data['quote']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['quote']->rel_id;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'quote',
            'rel_id'     => $id,
        ];
        $this->load->view('admin/quotes/invoice_convert_template', $data);
    }

    public function get_estimate_convert_data($id)
    {
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

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['quote']  = $this->quotes_model->get($id);
        $data['add_items'] = $this->_parse_items($data['quote']);

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();
        if ($data['quote']->rel_type == 'lead') {
            $this->db->where('leadid', $data['quote']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['quote']->rel_id;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'quote',
            'rel_id'     => $id,
        ];

        $this->load->view('admin/quotes/estimate_convert_template', $data);
    }

    private function _parse_items($quote)
    {
        $items = [];
        foreach ($quote->items as $item) {
            $taxnames = [];
            $taxes    = get_quote_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname']        = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id']             = 0;
            $items[]                = $item;
        }

        return $items;
    }

    /* Send quote to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_quote($id);
        if (!$canView) {
            access_denied('quotes');
        } else {
            if (!has_permission('quotes', '', 'view') && !has_permission('quotes', '', 'view_own') && $canView == false) {
                access_denied('quotes');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->quotes_model->send_quote_to_email(
                    $id,
                    $this->input->post('attach_pdf'),
                    $this->input->post('cc')
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('quote_sent_to_email_success'));
            } else {
                set_alert('danger', _l('quote_sent_to_email_fail'));
            }

            if ($this->set_quote_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('booking/quotes/list_quotes/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('quotes', '', 'create')) {
            access_denied('quotes');
        }
        $new_id = $this->quotes_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('quote_copy_success'));
            $this->set_quote_pipeline_autoload($new_id);
            redirect(admin_url('booking/quotes/quote/' . $new_id));
        } else {
            set_alert('success', _l('quote_copy_fail'));
        }
        if ($this->set_quote_pipeline_autoload($id)) {
            redirect(admin_url('booking/quotes'));
        } else {
            redirect(admin_url('booking/quotes/list_quotes/' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('quotes', '', 'edit')) {
            access_denied('quotes');
        }
        $success = $this->quotes_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('quote_status_changed_success'));
        } else {
            set_alert('danger', _l('quote_status_changed_fail'));
        }
        if ($this->set_quote_pipeline_autoload($id)) {
            redirect(admin_url('booking/quotes'));
        } else {
            redirect(admin_url('booking/quotes/list_quotes/' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('quotes', '', 'delete')) {
            access_denied('quotes');
        }
        $response = $this->quotes_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('quote')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('quote_lowercase')));
        }
        redirect(admin_url('booking/quotes'));
    }

    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->quotes_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_quote_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->quotes_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->quotes_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_quote_comments($id)
    {
        $data['comments'] = $this->quotes_model->get_comments($id);
        $this->load->view('admin/quotes/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'quote_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->quotes_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function save_quote_data()
    {
        if (!has_permission('quotes', '', 'edit') && !has_permission('quotes', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('quote_id'));
        $this->db->update(db_prefix() . 'quotes', [
            'content' => html_purify($this->input->post('content', false)),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('quote'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'quotes_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('booking/quotes'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('quotes', '', 'view') || has_permission('quotes', '', 'view_own') || get_option('allow_staff_view_quotes_assigned') == 1) {
            $data['quote']      = $this->get_quote_data_ajax($id, true);
            $data['quote_data'] = $this->quotes_model->get($id);
            $this->load->view('admin/quotes/pipeline/quote', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('quotes', '', 'edit')) {
            $this->quotes_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('quotes', '', 'view') || has_permission('quotes', '', 'view_own') || get_option('allow_staff_view_quotes_assigned') == 1) {
            $data['statuses'] = $this->quotes_model->get_statuses();
            $this->load->view('admin/quotes/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $quotes = $this->quotes_model->do_kanban_query($status, $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort'    => $this->input->get('sort'),
        ]);

        foreach ($quotes as $quote) {
            $this->load->view('admin/quotes/pipeline/_kanban_card', [
                'quote' => $quote,
                'status'   => $status,
            ]);
        }
    }

    public function set_quote_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('quotes_pipeline') && $this->session->userdata('quotes_pipeline') == 'true') {
            $this->session->set_flashdata('quoteid', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('quote_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('quote_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }
}
