<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bulk_pdf_export extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function report()
    {
        if (!has_permission('reports', '', 'view')) {
            access_denied('reports');
        }

        $data['title'] = _l('bulk_pdf_export_report');
        $this->load->view('bulk_pdf_export/report', $data);
    }

    public function generate()
    {
        if (!has_permission('reports', '', 'view')) {
            access_denied('reports');
        }

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $include_invoices = $this->input->post('invoices');
        $include_estimates = $this->input->post('estimates');
        $include_quotes = $this->input->post('quotes');

        if (!$from_date || !$to_date) {
            set_alert('warning', _l('bulk_pdf_export_select_date_range'));
            redirect(admin_url('bulk_pdf_export/report'));
        }

        $from = to_sql_date($from_date);
        $to = to_sql_date($to_date);

        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');

        $items = [];

        if ($include_invoices) {
            $this->db->where('date >=', $from);
            $this->db->where('date <=', $to);
            $this->db->order_by('date', 'desc');
            $invoices = $this->db->get(db_prefix() . 'invoices')->result_array();
            foreach ($invoices as $inv) {
                if (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own')) {
                    $item = $this->invoices_model->get($inv['id']);
                    if ($item) {
                        $items[] = ['type' => 'invoice', 'date' => $item->date, 'data' => $item];
                    }
                }
            }
        }

        if ($include_estimates) {
            $this->db->where('date >=', $from);
            $this->db->where('date <=', $to);
            $this->db->order_by('date', 'desc');
            $estimates = $this->db->get(db_prefix() . 'estimates')->result_array();
            foreach ($estimates as $est) {
                if (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own')) {
                    $item = $this->estimates_model->get($est['id']);
                    if ($item) {
                       $items[] = ['type' => 'estimate', 'date' => $item->date, 'data' => $item];
                    }
                }
            }
        }

        if ($include_quotes) {
            $this->db->where('date >=', $from);
            $this->db->where('date <=', $to);
            $this->db->order_by('date', 'desc');
            $proposals = $this->db->get(db_prefix() . 'proposals')->result_array();
            foreach ($proposals as $prop) {
                if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own')) {
                    $item = $this->proposals_model->get($prop['id']);
                    if ($item) {
                       $items[] = ['type' => 'proposal', 'date' => $item->date, 'data' => $item];
                    }
                }
            }
        }

        // Collect Credit Notes
        $include_credit_notes = $this->input->post('credit_notes');
        if ($include_credit_notes) {
            $this->load->model('credit_notes_model');
            $this->db->where('date >=', $from);
            $this->db->where('date <=', $to);
            $this->db->order_by('date', 'desc');
            $credit_notes = $this->db->get(db_prefix() . 'creditnotes')->result_array();
            foreach ($credit_notes as $cn) {
                if (has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own')) {
                    $item = $this->credit_notes_model->get($cn['id']);
                    if ($item) {
                        $items[] = ['type' => 'credit_note', 'date' => $item->date, 'data' => $item];
                    }
                }
            }
        }
        
        // Collect Purchase Module entities
        // Check if purchase module is active
        $this->load->library('app_modules');
        $purchase_active = $this->app_modules->is_active('purchase');
        if ($purchase_active) {
            $this->load->model('purchase/purchase_model');
            
            // Purchase Requests
            if ($this->input->post('pur_requests')) {
                $this->db->where('request_date >=', $from);
                $this->db->where('request_date <=', $to);
                $this->db->order_by('request_date', 'desc');
                $requests = $this->db->get(db_prefix() . 'pur_request')->result_array();
                foreach ($requests as $req) {
                    $item = $this->purchase_model->get_purchase_request($req['id']);
                    if ($item) {
                        $items[] = ['type' => 'pur_request', 'date' => $item->request_date, 'data' => $item];
                    }
                }
            }
            
            // Purchase Quotations (Estimates)
            if ($this->input->post('pur_quotations')) {
                $this->db->where('date >=', $from);
                $this->db->where('date <=', $to);
                $this->db->order_by('date', 'desc');
                $quotes = $this->db->get(db_prefix() . 'pur_estimates')->result_array();
                foreach ($quotes as $quote) {
                    $item = $this->purchase_model->get_estimate($quote['id']);
                    if ($item) {
                        $items[] = ['type' => 'pur_quotation', 'date' => $item->date, 'data' => $item];
                    }
                }
            }
            
            // Purchase Orders and Returns
            $include_pur_orders = $this->input->post('pur_orders');
            $include_pur_returns = $this->input->post('pur_returns');
            
            if ($include_pur_orders || $include_pur_returns) {
                $this->db->where('order_date >=', $from);
                $this->db->where('order_date <=', $to);
                $this->db->order_by('order_date', 'desc');
                $orders = $this->db->get(db_prefix() . 'pur_orders')->result_array();
                foreach ($orders as $order) {
                    $is_return = isset($order['return_order']) && $order['return_order'] == 1;
                    if (($is_return && $include_pur_returns) || (!$is_return && $include_pur_orders)) {
                        $item = $this->purchase_model->get_pur_order($order['id']);
                        if ($item) {
                            $type = $is_return ? 'pur_return' : 'pur_order';
                            $items[] = ['type' => $type, 'date' => $item->order_date, 'data' => $item];
                        }
                    }
                }
            }
        }

        if (empty($items)) {
            set_alert('warning', _l('bulk_pdf_export_no_records_found'));
            redirect(admin_url('bulk_pdf_export/report'));
        }

        usort($items, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        require_once(module_dir_path('bulk_pdf_export', 'libraries/Bulk_pdf.php'));

        try {
            $pdf = new Bulk_pdf($items);
            $pdf->prepare();
            $pdf->Output('bulk_export_' . $from_date . '_to_' . $to_date . '.pdf', 'D');
        } catch (Exception $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('bulk_pdf_export/report'));
        }
    }
}
