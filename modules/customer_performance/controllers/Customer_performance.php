<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customer_performance extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_performance_model');
    }

    public function get_stats($client_id)
    {
        if (!is_numeric($client_id) || !has_permission('customers', '', 'view')) {
            echo '<div class="alert alert-danger">Access Denied</div>';
            return;
        }

        $stats = $this->customer_performance_model->get_matrix_stats($client_id);
        $transactions = $this->customer_performance_model->get_last_transactions($client_id, 5);
        $client = $this->clients_model->get($client_id);

        $data['stats'] = $stats;
        $data['transactions'] = $transactions;
        $data['client'] = $client;

        $this->load->view('stats_modal_content', $data);
    }

    public function get_item_prices()
    {
        $client_id = $this->input->post('client_id');
        $item_id = $this->input->post('item_id');

        if (!is_numeric($client_id) || !is_numeric($item_id) || !has_permission('customers', '', 'view')) {
            echo '';
            return;
        }

        $prices = $this->customer_performance_model->get_item_last_prices($client_id, $item_id, 5);
        $client = $this->clients_model->get($client_id);

        if (count($prices) > 0) {
            $html = '<div class="mtop10 text-muted"><small><b><i class="fa fa-history"></i> Last 5 Prices for ' . ($client ? $client->company : 'Customer') . ':</b><br>';
            foreach ($prices as $p) {
                $link = '#';
                $number = '';
                
                if ($p['source'] === 'Invoice') {
                    $link = admin_url('invoices/list_invoices/' . $p['rel_id']);
                    $number = format_invoice_number($p['rel_id']);
                } else if ($p['source'] === 'Estimate') {
                    $link = admin_url('estimates/list_estimates/' . $p['rel_id']);
                    $number = format_estimate_number($p['rel_id']);
                } else if ($p['source'] === 'Proposal') {
                    $link = admin_url('proposals/list_proposals/' . $p['rel_id']);
                    $number = format_proposal_number($p['rel_id']);
                }

                $html .= '- ' . app_format_money($p['rate'], $client->default_currency) . ' on ' . _d($p['date']) . ' (<a href="' . $link . '" target="_blank">' . $p['source'] . ' ' . $number . '</a>)<br>';
            }
            $html .= '</small></div>';
            echo $html;
        } else {
            echo '<div class="mtop10 text-muted"><small><i>No previous price history for this item.</i></small></div>';
        }
    }
}
