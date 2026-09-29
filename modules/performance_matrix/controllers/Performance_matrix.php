<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Performance_matrix extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('performance_matrix_model');
    }

    public function index()
    {
        if (!has_permission('performance_matrix', '', 'view')) {
            access_denied('performance_matrix');
        }
        
        // Load items - using simple query for speed
        $this->db->select('id, description');
        $data['items'] = $this->db->get(db_prefix() . 'items')->result_array();
        
        // Load customers - using simple query to avoid potential subquery issues
        $this->db->select('userid, company');
        $data['customers'] = $this->db->get(db_prefix() . 'clients')->result_array();
        
        // Load vendors if purchase module exists
        $data['vendors'] = [];
        if ($this->db->table_exists(db_prefix() . 'pur_vendors')) {
            $this->db->select('userid, company');
            $data['vendors'] = $this->db->get(db_prefix() . 'pur_vendors')->result_array();
        }

        $data['title'] = 'Performance Matrix';
        $data['tab'] = $this->input->get('tab') ?: 'item';
        $this->load->view('manage', $data);
    }

    // --- CUSTOMER PERFORMANCE ---

    public function customer($id = '')
    {
        if (!has_permission('performance_matrix', '', 'view')) {
            access_denied('performance_matrix');
        }

        if ($this->input->is_ajax_request()) {
            $client_id = $this->input->post('client_id');
            $data['overview'] = $this->performance_matrix_model->get_detailed_customer_stats($client_id);
            $data['last_transactions'] = $this->performance_matrix_model->get_customer_last_transactions($client_id);
            $data['client_id'] = $client_id;
            
            $this->load->view('customer_stats_modal_content', $data);
        } else {
            redirect(admin_url('performance_matrix/index?tab=customer'));
        }
    }

    public function get_customer_item_prices()
    {
        if ($this->input->is_ajax_request()) {
            $client_id = $this->input->post('client_id');
            $item_id = $this->input->post('item_id');
            $prices = $this->performance_matrix_model->get_customer_item_last_prices($client_id, $item_id);

            if (empty($prices)) {
                echo '<p class="text-muted">No historical prices found for this item and customer.</p>';
            } else {
                echo '<table class="table table-bordered table-condensed">';
                echo '<thead><tr><th>Date</th><th>Source</th><th>Price</th><th>Qty</th></tr></thead><tbody>';
                foreach ($prices as $price) {
                    echo '<tr><td>' . _d($price['date']) . '</td><td>' . $price['source'] . '</td><td>' . app_format_money($price['rate'], '') . '</td><td>' . $price['qty'] . '</td></tr>';
                }
                echo '</tbody></table>';
            }
        }
    }

    // --- VENDOR PERFORMANCE ---

    public function vendor($id = '')
    {
        if (!has_permission('performance_matrix', '', 'view')) {
            access_denied('performance_matrix');
        }

        if ($this->input->is_ajax_request()) {
            $vendor_id = $this->input->post('vendor_id');
            $period = $this->input->post('period') ?: 'all_time';
            $data['stats'] = $this->performance_matrix_model->get_vendor_performance_stats($vendor_id, $period);
            $data['vendor_id'] = $vendor_id;
            $data['period'] = $period;
            
            $this->load->view('vendor_stats_modal_content', $data);
        } else {
            redirect(admin_url('performance_matrix/index?tab=vendor'));
        }
    }

    // --- ITEM PERFORMANCE ---

    public function item($id = '')
    {
        if (!has_permission('performance_matrix', '', 'view')) {
            access_denied('performance_matrix');
        }

        if ($this->input->is_ajax_request()) {
             $item_id = $this->input->post('item_id');
             $data['item'] = $this->db->get_where(db_prefix() . 'items', ['id' => $item_id])->row();
             $data['item_id'] = $item_id;
             $this->load->view('item_stats_modal_content', $data);
        } else {
            redirect(admin_url('performance_matrix/index?tab=item'));
        }
    }

    public function get_item_performance_data()
    {
        if ($this->input->is_ajax_request()) {
            $item_id = $this->input->post('item_id');
            $type = $this->input->post('transaction_type');
            $year = $this->input->post('year') ?: date('Y');
            $data = $this->performance_matrix_model->get_item_performance_data($item_id, $type, $year);
            echo json_encode($data);
        }
    }
}
