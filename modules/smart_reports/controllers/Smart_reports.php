<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_reports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_reports/smart_reports_model');
    }

    /**
     * Index method - displays the main Smart Reports page
     */
    public function index()
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('smart_reports', 'table'));
        }

        $data['title'] = _l('smart_reports');
        $this->load->view('smart_reports/manage', $data);
    }

    /**
     * Report form - create or edit a report
     * @param  string $id Optional report id for editing
     */
    public function report($id = '')
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('smart_reports', '', 'create')) {
                    access_denied('smart_reports');
                }
                $id = $this->smart_reports_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('report')));
                    redirect(admin_url('smart_reports/report/' . $id));
                }
            } else {
                if (!has_permission('smart_reports', '', 'edit')) {
                    access_denied('smart_reports');
                }
                $success = $this->smart_reports_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('report')));
                }
                redirect(admin_url('smart_reports/report/' . $id));
            }
        }

        // Prepare data for the form
        if ($id == '') {
            $title = _l('add_new', _l('report'));
            $data['report'] = null;
        } else {
            $data['report'] = $this->smart_reports_model->get($id);
            $title = _l('edit', _l('report'));
        }

        // Get report types
        $data['report_types'] = [
            'sales' => _l('sales'),
            'purchases' => _l('purchases'),
            'inventory' => _l('inventory'),
            'payments' => _l('payments'),
            'leads' => _l('leads'),
            'tasks' => _l('tasks'),
            'custom_query' => _l('custom_query')
        ];

        $data['title'] = $title;
        $this->load->view('smart_reports/report_form', $data);
    }

    /**
     * Generate report based on form input
     */
    public function generate()
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        if ($this->input->post()) {
            $report_data = $this->smart_reports_model->generate_report($this->input->post());
            
            $data = [
                'success' => true,
                'report_data' => $report_data,
                'title' => $this->input->post('title')
            ];

            // If this is an AI-generated report, log it
            if ($this->input->post('ai_query')) {
                $this->smart_reports_model->log_ai_query(
                    $this->input->post('ai_query'),
                    $report_data['sql'],
                    $this->input->post('report_id') ?? null
                );
            }

            echo json_encode($data);
            die;
        }
    }

    /**
     * Process AI/NLP query
     */
    public function process_ai_query()
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        if ($this->input->post('query')) {
            $query = $this->input->post('query');
            
            // Process the query using the AI helper
            $this->load->helper('smart_reports/smart_report_ai_helper');
            $result = process_nlp_query($query);
            
            echo json_encode($result);
            die;
        }
    }

    /**
     * Save a report for future use
     */
    public function save_report()
    {
        if (!has_permission('smart_reports', '', 'create')) {
            access_denied('smart_reports');
        }

        if ($this->input->post()) {
            $id = $this->smart_reports_model->save_report($this->input->post());
            if ($id) {
                set_alert('success', _l('report_saved_successfully'));
            }
            redirect(admin_url('smart_reports'));
        }
    }

    /**
     * Delete a report
     * @param  integer $id Report id
     */
    public function delete($id)
    {
        if (!has_permission('smart_reports', '', 'delete')) {
            access_denied('smart_reports');
        }

        if (!$id) {
            redirect(admin_url('smart_reports'));
        }

        $response = $this->smart_reports_model->delete($id);
        if ($response) {
            set_alert('success', _l('deleted', _l('report')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('report')));
        }
        
        redirect(admin_url('smart_reports'));
    }

    /**
     * View saved reports
     */
    public function saved_reports()
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        $data['saved_reports'] = $this->smart_reports_model->get_saved_reports();
        $data['title'] = _l('saved_reports');
        
        $this->load->view('smart_reports/saved_reports', $data);
    }

    /**
     * View AI query logs
     */
    public function ai_logs()
    {
        if (!has_permission('smart_reports', '', 'view')) {
            access_denied('smart_reports');
        }

        $data['ai_logs'] = $this->smart_reports_model->get_ai_logs();
        $data['title'] = _l('ai_query_logs');
        
        $this->load->view('smart_reports/ai_logs', $data);
    }
}