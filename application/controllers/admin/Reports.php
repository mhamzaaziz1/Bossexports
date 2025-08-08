<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends AdminController
{
    /**
     * Codeigniter Instance
     * Expenses detailed report filters use $ci
     * @var object
     */
    // private $ci;

    public function __construct()
    {
        parent::__construct();
        if (!has_permission('reports', '', 'view')) {
            access_denied('reports');
        }
        // $this->ci = &get_instance();
        $this->load->model('reports_model');
    }

    /* No access on this url */
    public function index()
    {
        redirect(admin_url());
    }

    /* See knowledge base article reports*/
    public function knowledge_base_articles()
    {
        $this->load->model('knowledge_base_model');

        // Get time period filter
        $report_months = $this->input->get('report_months');
        $report_from = $this->input->get('report_from');
        $report_to = $this->input->get('report_to');

        // Store selected values for the view
        $data['report_months'] = $report_months;
        if ($report_months == 'custom') {
            $data['report_from'] = $report_from;
            $data['report_to'] = $report_to;
        }

        // Get date filter SQL
        $date_filter = '';
        if ($report_months) {
            $date_filter = $this->get_where_report_period('dateadded');
        }

        // Get selected group
        $group = $this->input->get('group');

        // Get all KB groups with articles filtered by date
        $data['groups'] = $this->knowledge_base_model->get_kbg($group, '', $date_filter);
        $data['title']  = _l('kb_reports');
        $this->load->view('admin/reports/knowledge_base_articles', $data);
    }

    /*
        public function tax_summary(){
           $this->load->model('taxes_model');
           $this->load->model('payments_model');
           $this->load->model('invoices_model');
           $data['taxes'] = $this->db->query("SELECT DISTINCT taxname,taxrate FROM ".db_prefix()."item_tax WHERE rel_type='invoice'")->result_array();
            $this->load->view('admin/reports/tax_summary',$data);
        }*/
    /* Repoert leads conversions */
    public function leads()
    {
        $type = 'leads';
        if ($this->input->get('type')) {
            $type                       = $type . '_' . $this->input->get('type');
            $data['leads_staff_report'] = json_encode($this->reports_model->leads_staff_report());
        }
        $this->load->model('leads_model');
        $data['statuses']               = $this->leads_model->get_status();
        $data['leads_this_week_report'] = json_encode($this->reports_model->leads_this_week_report());
        $data['leads_sources_report']   = json_encode($this->reports_model->leads_sources_report());
        $this->load->view('admin/reports/' . $type, $data);
    }

    /* Sales reportts */
    public function sales()
    {
        $data['mysqlVersion'] = $this->db->query('SELECT VERSION() as version')->row();
        $data['sqlMode']      = $this->db->query('SELECT @@sql_mode as mode')->row();

        if (is_using_multiple_currencies() || is_using_multiple_currencies(db_prefix() . 'creditnotes') || is_using_multiple_currencies(db_prefix() . 'estimates') || is_using_multiple_currencies(db_prefix() . 'proposals')) {
            $this->load->model('currencies_model');
            $data['currencies'] = $this->currencies_model->get();
        }
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('credit_notes_model');

        $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
        $data['invoice_statuses']      = $this->invoices_model->get_statuses();
        $data['estimate_statuses']     = $this->estimates_model->get_statuses();
        $data['payments_years']        = $this->reports_model->get_distinct_payments_years();
        $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();

        $data['invoices_sale_agents'] = $this->db->query('SELECT * FROM tblclients')->result_array();
        $data['invoices_sale_product'] = $this->db->query('SELECT * FROM tblitems')->result_array();

        $data['proposals_sale_agents'] = $this->proposals_model->get_sale_agents();
        $data['proposals_statuses']    = $this->proposals_model->get_statuses();

        $data['invoice_taxes']     = $this->distinct_taxes('invoice');
        $data['estimate_taxes']    = $this->distinct_taxes('estimate');
        $data['proposal_taxes']    = $this->distinct_taxes('proposal');
        $data['credit_note_taxes'] = $this->distinct_taxes('credit_note');


        $data['title'] = _l('sales_reports');
        $this->load->view('admin/reports/sales', $data);
    }

    /* Customer report */
    public function customers_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $select = [
                get_sql_select_client_company(),
                '(SELECT COUNT(clientid) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
                '(SELECT SUM(subtotal) - SUM(discount_total) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
                '(SELECT SUM(total) FROM ' . db_prefix() . 'invoices WHERE ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid AND status != 5)',
            ];

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' ' . $custom_date_select . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
            }
            $by_currency = $this->input->post('report_currency');
            $currency    = $this->currencies_model->get_base_currency();
            if ($by_currency) {
                $i = 0;
                foreach ($select as $_select) {
                    if ($i !== 0) {
                        $_temp = substr($_select, 0, -1);
                        $_temp .= ' AND currency =' . $this->db->escape_str($by_currency) . ')';
                        $select[$i] = $_temp;
                    }
                    $i++;
                }
                $currency = $this->currencies_model->get($by_currency);
            }
            $aColumns     = $select;
            $sIndexColumn = 'userid';
            $sTable       = db_prefix() . 'clients';
            $where        = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
                'userid',
            ]);
            $output  = $result['output'];
            $rResult = $result['rResult'];
            $x       = 0;
            foreach ($rResult as $aRow) {
                $row = [];
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = isset($aRow[$aColumns[$i]]) ? $aRow[$aColumns[$i]] : 0;
                    }
                    if ($i == 0) {
                        $_data = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                    } elseif ($aColumns[$i] == $select[2] || $aColumns[$i] == $select[3]) {
                        if ($_data == null) {
                            $_data = 0;
                        }
                        $_data = app_format_money($_data, $currency->name);
                    }
                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
                $x++;
            }
            echo json_encode($output);
            die();
        }
    }

    public function payments_received()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('payment_modes_model');
            $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
            $select           = [
                db_prefix() . 'invoicepaymentrecords.id',
                db_prefix() . 'invoicepaymentrecords.date',
                'invoiceid',
                get_sql_select_client_company(),
                'paymentmode',
                'transactionid',
                'note',
                db_prefix() . 'invoicepaymentrecords.amount as amount',
            ];
            $where = [
                // 'AND status != 5',
            ];

            $custom_date_select = $this->get_where_report_period(db_prefix() . 'invoicepaymentrecords.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'invoicepaymentrecords';
            $join         = [
                // 'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid',
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoicepaymentrecords.client_id',
                'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                // 'number',
                'client_id',
                db_prefix() . 'payment_modes.name',
                db_prefix() . 'payment_modes.id as paymentmodeid',
                'paymentmethod',
                // 'deleted_customer_name',
            ]);
            // var_dump($this->db->last_query());

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data['total_amount'] = 0;

            foreach ($rResult as $aRow) {
                $row = [];
                for ($i = 0; $i < count($aColumns); $i++) {
                    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                        $_data = $aRow[strafter($aColumns[$i], 'as ')];
                    } else {
                        $_data = isset($aRow[$aColumns[$i]]) ? $aRow[$aColumns[$i]] : 0;
                    }
                    if ($aColumns[$i] == 'paymentmode') {
                        $_data = $aRow['name'];
                        if (is_null($aRow['paymentmodeid'])) {
                            foreach ($payment_gateways as $gateway) {
                                if ($aRow['paymentmode'] == $gateway['id']) {
                                    $_data = $gateway['name'];
                                }
                            }
                        }
                        if (!empty($aRow['paymentmethod'])) {
                            $_data .= ' - ' . $aRow['paymentmethod'];
                        }
                    } elseif ($aColumns[$i] == db_prefix() . 'invoicepaymentrecords.id') {
                        $_data = '<a href="' . admin_url('payments/payment/' . $_data) . '" target="_blank">' . $_data . '</a>';
                    } elseif ($aColumns[$i] == db_prefix() . 'invoicepaymentrecords.date') {
                        $_data = _d($_data);
                    } elseif ($aColumns[$i] == 'invoiceid') {
                        $_data = '<a href="' . admin_url('invoices/list_invoices/' . $aRow[$aColumns[$i]]) . '" target="_blank">' . format_invoice_number($aRow['invoiceid']) . '</a>';
                    } elseif ($i == 3) {
                        if (empty($aRow['deleted_customer_name'])) {
                            $_data = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                        } else {
                            $row[] = $aRow['deleted_customer_name'];
                        }
                    } elseif ($aColumns[$i] == 'amount') {
                        $footer_data['total_amount'] += $_data;
                        $_data = app_format_money($_data, $currency->name);
                    }

                    $row[] = $_data;
                }
                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = app_format_money($footer_data['total_amount'], $currency->name);
            $output['sums']              = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function proposals_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('proposals_model');

            $proposalsTaxes    = $this->distinct_taxes('proposal');
            $totalTaxesColumns = count($proposalsTaxes);

            $select = [
                'id',
                'subject',
                'proposal_to',
                'date',
                'open_till',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'status',
            ];

            $proposalsTaxesSelect = array_reverse($proposalsTaxes);

            foreach ($proposalsTaxesSelect as $key => $tax) {
                array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="proposal" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'proposals.id) as total_tax_single_' . $key);
            }

            $where              = [];
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('proposal_status')) {
                $statuses  = $this->input->post('proposal_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('proposals_sale_agents')) {
                $agents  = $this->input->post('proposals_sale_agents');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND assigned IN (' . implode(', ', $_agents) . ')');
                }
            }


            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'proposals';
            $join         = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'rel_id',
                'rel_type',
                'discount_percent',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'          => 0,
                'subtotal'       => 0,
                'total_tax'      => 0,
                'discount_total' => 0,
                'adjustment'     => 0,
            ];

            foreach ($proposalsTaxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['id']) . '" target="_blank">' . format_proposal_number($aRow['id']) . '</a>';

                $row[] = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['id']) . '" target="_blank">' . $aRow['subject'] . '</a>';

                if ($aRow['rel_type'] == 'lead') {
                    $row[] = '<a href="#" onclick="init_lead(' . $aRow['rel_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="' . _l('lead') . '">' . $aRow['proposal_to'] . '</a>' . '<span class="hide">' . _l('lead') . '</span>';
                } elseif ($aRow['rel_type'] == 'customer') {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['rel_id']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . $aRow['proposal_to'] . '</a>' . '<span class="hide">' . _l('client') . '</span>';
                } else {
                    $row[] = '';
                }

                $row[] = _d($aRow['date']);

                $row[] = _d($aRow['open_till']);

                $row[] = app_format_money($aRow['subtotal'], $currency->name);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = app_format_money($aRow['total'], $currency->name);
                $footer_data['total'] += $aRow['total'];

                $row[] = app_format_money($aRow['total_tax'], $currency->name);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($proposalsTaxes as $tax) {
                    $row[] = app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name);
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = app_format_money($aRow['discount_total'], $currency->name);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = app_format_money($aRow['adjustment'], $currency->name);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[]              = format_proposal_status($aRow['status']);
                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function estimates_report()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('estimates_model');

            $estimateTaxes     = $this->distinct_taxes('estimate');
            $totalTaxesColumns = count($estimateTaxes);

            $select = [
                'number',
                get_sql_select_client_company(),
                'invoiceid',
                'YEAR(date) as year',
                'date',
                'expirydate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                'reference_no',
                'status',
            ];

            $estimatesTaxesSelect = array_reverse($estimateTaxes);

            foreach ($estimatesTaxesSelect as $key => $tax) {
                array_splice($select, 9, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="estimate" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'estimates.id) as total_tax_single_' . $key);
            }

            $where              = [];
            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('estimate_status')) {
                $statuses  = $this->input->post('estimate_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            if ($this->input->post('sale_agent_estimates')) {
                $agents  = $this->input->post('sale_agent_estimates');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'estimates';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'estimates.clientid',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'estimates.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'          => 0,
                'subtotal'       => 0,
                'total_tax'      => 0,
                'discount_total' => 0,
                'adjustment'     => 0,
            ];

            foreach ($estimateTaxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" target="_blank">' . format_estimate_number($aRow['id']) . '</a>';

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                } else {
                    $row[] = $aRow['deleted_customer_name'];
                }

                if ($aRow['invoiceid'] === null) {
                    $row[] = '';
                } else {
                    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '" target="_blank">' . format_invoice_number($aRow['invoiceid']) . '</a>';
                }

                $row[] = $aRow['year'];

                $row[] = _d($aRow['date']);


                $this->db->select('id');
                $this->db->from('tblproposals');
                $this->db->where('estimate_id', $aRow['id']);
                $query = $this->db->get();
                $proposal = $query->result();

                $row[] = $proposal[0]->id;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = app_format_money($aRow['total'], $currency->name);
                $footer_data['total'] += $aRow['total'];

                $row[] = app_format_money($aRow['total_tax'], $currency->name);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($estimateTaxes as $tax) {
                    $row[] = app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name);
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = app_format_money($aRow['discount_total'], $currency->name);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = app_format_money($aRow['adjustment'], $currency->name);
                $footer_data['adjustment'] += $aRow['adjustment'];


                $row[] = $aRow['reference_no'];

                $row[] = format_estimate_status($aRow['status']);

                $output['aaData'][] = $row;
            }
            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }
            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    private function get_where_report_period($field = 'date')
    {
        $months_report      = $this->input->post('report_months');
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = ' AND (' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = ' AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = ' AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = ' and (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = $this->input->post('report_from');
                $to_date = $this->input->post('report_to');

                // Convert dates to SQL format
                $from_date_sql = to_sql_date($from_date);
                $to_date_sql = to_sql_date($to_date);

                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $this->db->escape_str($from_date_sql) . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $this->db->escape_str($from_date_sql) . '" AND "' . $this->db->escape_str($to_date_sql) . '")';
                }
            }
        }

        return $custom_date_select;
    }

    public function items()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $v = $this->db->query('SELECT VERSION() as version')->row();
            // 5.6 mysql version don't have the ANY_VALUE function implemented.

            if ($v && strpos($v->version, '5.7') !== false) {
                $aColumns = [
                    'ANY_VALUE(description) as description',
                    'ANY_VALUE((SUM(' . db_prefix() . 'itemable.qty))) as quantity_sold',
                    'ANY_VALUE(SUM(rate*qty)) as rate',
                    'ANY_VALUE(AVG(rate*qty)) as avg_price',
                ];
            } else {
                $aColumns = [
                    'tblinvoices.id as IID',
                    'date',
                    'tblitemable.description as description',
                    'tblinvoices.clientid as customerID',
                    'concat(prefix,number) as invoiceID',
                    '(SUM(' . db_prefix() . 'itemable.qty)) as quantity_sold',
                    'SUM(tblitemable.rate*qty) as rate',
                    'tblitemable.rate as avg_price',
                ];
            }

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'itemable';

            // Optimize join by using LEFT JOIN for items to ensure all records are included
            $join = [
                'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id',
                'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description'
            ];

            $where = ['AND rel_type="invoice"'];

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('sale_agent_items')) {
                $agents  = $this->input->post('sale_agent_items');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND tblinvoices.clientid IN (' . implode(', ', $_agents) . ')');
                }
            }
            if ($this->input->post('sale_product_items')) {
                $agents  = $this->input->post('sale_product_items');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND tblitems.id IN (' . implode(', ', $_agents) . ')');
                }
            }

            // Optimize by adding index hint if available
            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblinvoices.clientid',
//                'tblestimates.id',
//                'tblestimates.number'
            ], 'GROUP by description,tblinvoices.id,date, tblinvoices.clientid');

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total_amount' => 0,
                'total_qty'    => 0,
            ];

            // Optimize by pre-fetching all estimate data in a single query
            $invoice_ids = [];
            foreach ($rResult as $row) {
                $invoice_ids[] = $row['IID'];
            }

            $estimates_data = [];
            if (!empty($invoice_ids)) {
                $this->db->select('id, number, invoiceid');
                $this->db->from('tblestimates');
                $this->db->where_in('invoiceid', $invoice_ids);
                $estimates = $this->db->get()->result_array();

                foreach ($estimates as $estimate) {
                    $estimates_data[$estimate['invoiceid']] = $estimate;
                }
            }

            // Optimize by pre-fetching all client data in a single query
            $client_ids = [];
            foreach ($rResult as $row) {
                $client_ids[] = $row['customerID'];
            }

            $clients_data = [];
            if (!empty($client_ids)) {
                $this->db->select('userid, company');
                $this->db->from('tblclients');
                $this->db->where_in('userid', array_unique($client_ids));
                $clients = $this->db->get()->result_array();

                foreach ($clients as $client) {
                    $clients_data[$client['userid']] = $client;
                }
            }

            foreach ($rResult as $aRow) {
                $row = [];
                $invoice = $aRow['IID'];

                $row[] = $aRow['description'];
                $row[] = $aRow['date'];

                // Use pre-fetched client data
                $row[] = isset($clients_data[$aRow['customerID']]) ? $clients_data[$aRow['customerID']]['company'] : '';

                // Use pre-fetched estimate data
                $estimate_number = isset($estimates_data[$aRow['IID']]) ? $estimates_data[$aRow['IID']]['number'] : '0';
                $row[] = $aRow['invoiceID'] . '(SO-000' . $estimate_number . ')';

                $row[] = $aRow['quantity_sold'];
                $row[] = app_format_money($aRow['rate'], $currency->name);
                $row[] = app_format_money($aRow['avg_price'], $currency->name);
                $footer_data['total_amount'] += $aRow['rate'];
                $footer_data['total_qty'] += $aRow['quantity_sold'];
                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = app_format_money($footer_data['total_amount'], $currency->name);

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function credit_notes()
    {
        if ($this->input->is_ajax_request()) {
            $credit_note_taxes = $this->distinct_taxes('credit_note');
            $totalTaxesColumns = count($credit_note_taxes);

            $this->load->model('currencies_model');

            $select = [
                'number',
                'date',
                get_sql_select_client_company(),
                'reference_no',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT ' . db_prefix() . 'creditnotes.total - (
                  (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.credit_id=' . db_prefix() . 'creditnotes.id)
                  +
                  (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'creditnote_refunds WHERE ' . db_prefix() . 'creditnote_refunds.credit_note_id=' . db_prefix() . 'creditnotes.id)
                  )
                ) as remaining_amount',
                'status',
            ];

            $where = [];

            $credit_note_taxes_select = array_reverse($credit_note_taxes);

            foreach ($credit_note_taxes_select as $key => $tax) {
                array_splice($select, 5, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="credit_note" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'creditnotes.id) as total_tax_single_' . $key);
            }

            $custom_date_select = $this->get_where_report_period();

            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');

            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('credit_note_status')) {
                $statuses  = $this->input->post('credit_note_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'creditnotes';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'creditnotes.clientid',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'creditnotes.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'            => 0,
                'subtotal'         => 0,
                'total_tax'        => 0,
                'discount_total'   => 0,
                'adjustment'       => 0,
                'remaining_amount' => 0,
            ];

            foreach ($credit_note_taxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }
            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('credit_notes/list_credit_notes/' . $aRow['id']) . '" target="_blank">' . format_credit_note_number($aRow['id']) . '</a>';

                $row[] = _d($aRow['date']);

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
                } else {
                    $row[] = $aRow['deleted_customer_name'];
                }

                $row[] = $aRow['reference_no'];

                $row[] = app_format_money($aRow['subtotal'], $currency->name);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = app_format_money($aRow['total'], $currency->name);
                $footer_data['total'] += $aRow['total'];

                $row[] = app_format_money($aRow['total_tax'], $currency->name);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($credit_note_taxes as $tax) {
                    $row[] = app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name);
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = app_format_money($aRow['discount_total'], $currency->name);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = app_format_money($aRow['adjustment'], $currency->name);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = app_format_money($aRow['remaining_amount'], $currency->name);
                $footer_data['remaining_amount'] += $aRow['remaining_amount'];

                $row[] = format_credit_note_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    public function invoices_report()
    {
        if ($this->input->is_ajax_request()) {
            $invoice_taxes     = $this->distinct_taxes('invoice');
            $totalTaxesColumns = count($invoice_taxes);

            $this->load->model('currencies_model');
            $this->load->model('invoices_model');

            $select = [
                'number',
                get_sql_select_client_company(),
                'YEAR(date) as year',
                'date',
                'duedate',
                'subtotal',
                'total',
                'total_tax',
                'discount_total',
                'adjustment',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id) as credits_applied',
                '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id))',
                'status',
            ];

            $where = [
                'AND status != 5',
            ];

            $invoiceTaxesSelect = array_reverse($invoice_taxes);

            foreach ($invoiceTaxesSelect as $key => $tax) {
                array_splice($select, 8, 0, '(
                    SELECT CASE
                    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
                    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
                    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
                    END
                    FROM ' . db_prefix() . 'itemable
                    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
                    WHERE ' . db_prefix() . 'itemable.rel_type="invoice" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'invoices.id) as total_tax_single_' . $key);
            }

            $custom_date_select = $this->get_where_report_period();
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            if ($this->input->post('sale_agent_invoices')) {
                $agents  = $this->input->post('sale_agent_invoices');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            $by_currency              = $this->input->post('report_currency');
            $totalPaymentsColumnIndex = (12 + $totalTaxesColumns - 1);

            if ($by_currency) {
                $_temp = substr($select[$totalPaymentsColumnIndex], 0, -2);
                $_temp .= ' AND currency =' . $by_currency . ')) as amount_open';
                $select[$totalPaymentsColumnIndex] = $_temp;

                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency                          = $this->currencies_model->get_base_currency();
                $select[$totalPaymentsColumnIndex] = $select[$totalPaymentsColumnIndex] .= ' as amount_open';
            }

            if ($this->input->post('invoice_status')) {
                $statuses  = $this->input->post('invoice_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'invoices';
            $join         = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'userid',
                'clientid',
                db_prefix() . 'invoices.id',
                'discount_percent',
                'deleted_customer_name',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
                'subtotal'        => 0,
                'total_tax'       => 0,
                'discount_total'  => 0,
                'adjustment'      => 0,
                'applied_credits' => 0,
                'amount_open'     => 0,
            ];

            foreach ($invoice_taxes as $key => $tax) {
                $footer_data['total_tax_single_' . $key] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';

                if (empty($aRow['deleted_customer_name'])) {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '" target="_blank">' . $aRow['company'] . '</a>';
                } else {
                    $row[] = $aRow['deleted_customer_name'];
                }

                $row[] = $aRow['year'];

                $row[] = _d($aRow['date']);
                $this->db->select('id , number');
                $this->db->from('tblestimates');
                $this->db->where('invoiceid', $aRow['id']);
                $query = $this->db->get();
                $estimate = $query->result();


                $row[] =  'SO-'.$estimate[0]->number;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);
                $footer_data['subtotal'] += $aRow['subtotal'];

                $row[] = app_format_money($aRow['total'], $currency->name);
                $footer_data['total'] += $aRow['total'];

                $row[] = app_format_money($aRow['total_tax'], $currency->name);
                $footer_data['total_tax'] += $aRow['total_tax'];

                $t = $totalTaxesColumns - 1;
                $i = 0;
                foreach ($invoice_taxes as $tax) {
                    $row[] = app_format_money(($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]), $currency->name);
                    $footer_data['total_tax_single_' . $i] += ($aRow['total_tax_single_' . $t] == null ? 0 : $aRow['total_tax_single_' . $t]);
                    $t--;
                    $i++;
                }

                $row[] = app_format_money($aRow['discount_total'], $currency->name);
                $footer_data['discount_total'] += $aRow['discount_total'];

                $row[] = app_format_money($aRow['adjustment'], $currency->name);
                $footer_data['adjustment'] += $aRow['adjustment'];

                $row[] = app_format_money($aRow['credits_applied'], $currency->name);
                $footer_data['applied_credits'] += $aRow['credits_applied'];

                $amountOpen = $aRow['amount_open'];
                $row[]      = app_format_money($amountOpen, $currency->name);
                $footer_data['amount_open'] += $amountOpen;

                $row[] = format_invoice_status($aRow['status']);

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }




    public function get_sum_payments_today($customer_id,$date)
    {
        // Get payments for this customer on this date, grouped by transaction ID
        $this->db->select('transactionid, amount');
        $this->db->from('tblinvoicepaymentrecords'); // Use your actual table name
        $this->db->where('client_id', $customer_id); // Filter by customer ID
        // Filter for today's date using DATE() function for the 'date' column
        $this->db->where('DATE(date)', $date);
        $this->db->group_by('transactionid'); // Group by transaction ID to avoid summing all payments
        $query = $this->db->get();

        // Check if any rows were returned
        if ($query->num_rows() > 0) {
            $total = 0;
            foreach ($query->result() as $row) {
                $total += floatval($row->amount);
            }
            return $total;
        }
        return 0; // Return 0 if no payments found
    }



    public function directors_report(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('invoices_model');
            $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);

            // Get base currency
            $currency = $this->currencies_model->get_base_currency();

            // Initialize where clause array
            $where = [];

            // Custom date filter
            $custom_date_select = ltrim($this->get_where_report_period(), 'AND ');
            if ($custom_date_select != '') {
                // Note: $custom_date_select should be a full conditional SQL fragment, e.g. "tblinvoices.date >= '2023-01-01'"
                array_push($where, $custom_date_select);
            }

            // Sale agent filter
            if ($this->input->post('sale_agent_invoices')) {
                $agents  = $this->input->post('sale_agent_invoices');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    // Note: This adds an AND condition, so ensure proper placement when concatenating
                    array_push($where, 'AND sale_agent IN (' . implode(', ', $_agents) . ')');
                }
            }

            // Prepare WHERE clause for invoices and payments separately
            // Because the columns/tables differ, applying filters exactly might need adjustment.
            // For simplicity, we'll apply filters to both with possible adaptations.
            $where_invoices = '';
            $where_payments = '';

            if (!empty($where)) {
                // Join filters with spaces, replacing leading AND appropriately
                // Remove starting AND for first condition in invoices and payments separately

                $filters = array_map(function ($filter) {
                    // Trim 'AND' from start if present
                    return preg_replace('/^AND\s+/i', '', $filter);
                }, $where);

                $where_invoices = ' WHERE ' . implode(' AND ', $filters);
                $where_payments = ' WHERE ' . implode(' AND ', $filters);
            }

            // Pagination parameters from DataTables request
            $limit = intval($this->input->post('length')) > 0 ? intval($this->input->post('length')) : 10000;
            $offset = intval($this->input->post('start')) >= 0 ? intval($this->input->post('start')) : 0;

            // Build main SQL query with UNION
            $sql = "SELECT COALESCE(tblinvoices.id, '' ) as ID,
                           COALESCE(tblinvoices.clientid, '' ) as customerID,
                           CONCAT(tblinvoices.prefix, tblinvoices.number) as invoice,
                           ' ' as PaymentID,
                           ' ' as TRANSACTIONID,
                           ' ' as PaymentMode,
                           COALESCE(tblinvoices.total, 0) as amount,
                           COALESCE(tblinvoices.date, '' ) as date,
                           COALESCE(tblinvoices.status, '' ) as status
                    FROM tblinvoices
                    $where_invoices
                    UNION
                    SELECT COALESCE(tblinvoicepaymentrecords.id, ' ') as ID,
                           COALESCE(tblinvoicepaymentrecords.client_id, ' ') as customerID,
                           ' ' as invoice,
                           tblinvoicepaymentrecords.id as PaymentID,
                           transactionid as TRANSACTIONID,
                           paymentmode as PaymentMode,
                           COALESCE(SUM(amount), 0) as amount,
                           COALESCE(tblinvoicepaymentrecords.date, NULL) as date,
                           COALESCE('', '' ) as status
                    FROM tblinvoicepaymentrecords
                    $where_payments
                    GROUP BY transactionid
                    ORDER BY date DESC, customerID DESC
                    LIMIT $offset, $limit";

            // Run main query for data rows
            $query = $this->db->query($sql);
            $results = $query->result_array();

            // Fetch total records count without filtering (for pagination UI)
            $total_invoices = $this->db->count_all('tblinvoices');
            $total_payments = $this->db->count_all('tblinvoicepaymentrecords');

            // Approximate total records without filter as sum of both
            $recordsTotal = $total_invoices + $total_payments;

            // Because filtering is applied via SQL and involves UNION, the filtered count needs an additional count query:
            // We'll count filtered invoices
            $count_invoices_sql = "SELECT COUNT(*) as cnt FROM tblinvoices $where_invoices";
            $count_invoices_query = $this->db->query($count_invoices_sql);
            $count_invoices = $count_invoices_query->row()->cnt ?? 0;

            // Count filtered payments grouped by transactionid
            $count_payments_sql = "SELECT COUNT(DISTINCT transactionid) as cnt FROM tblinvoicepaymentrecords $where_payments";
            $count_payments_query = $this->db->query($count_payments_sql);
            $count_payments = $count_payments_query->row()->cnt ?? 0;

            $recordsFiltered = $count_invoices + $count_payments;

            // Prepare DataTables response
            $output = [
                'draw' => intval($this->input->post('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'aaData' => []
            ];

            foreach ($results as $aRow) {
                $row = [];

                // Prepare invoice link
                $invoiceLink = ($aRow['ID'] && $aRow['invoice'] !== '' && $aRow['invoice'] !== 'NULL')
                    ? '<a href="' . admin_url('invoices/list_invoices/' . $aRow['ID']) . '" target="_blank">' . $aRow['invoice'] . '</a>'
                    : '—';

                // Prepare client link, check if customerID is not null/empty and if get_client result exists
                // $clientName = '—';
                if (!empty($aRow['customerID']) && $aRow['customerID'] !== 'NULL') {
                    $clientLink = $aRow['customerID'] !== 'NULL'
                        ? '<a href="' . admin_url('clients/client/' . $aRow['customerID']) . '" target="_blank">' . get_client($aRow['customerID'])->company . '</a>'
                        : "";
                }

                $row[] = $aRow['ID'];
                $row[] = $clientLink;
                $this->db->select('id , number');
                $this->db->from('tblestimates');
                $this->db->where('invoiceid', $aRow['ID']);
                $query = $this->db->get();
                $estimate = $query->result();
                // var_dump($estimate);die;

                $row[] = $invoiceLink.' - SO-000' .  $estimate[0]->number;
                $row[] = $aRow['PaymentID'];
                $row[] = $aRow['TRANSACTIONID'];

                // Map PaymentMode codes to string labels
                if ($aRow['PaymentMode'] == 3) {
                    $data = 'FNB';
                } elseif ($aRow['PaymentMode'] == 4) {
                    $data = 'NEDBANK';
                } elseif (is_numeric($aRow['PaymentMode']) && $aRow['PaymentMode'] > 4) {
                    $data = 'OTHERS';
                } else {
                    $data = ' ';
                }
                $row[] = $data;

                $row[] = $aRow['amount'];
                $row[] = _d($aRow['date']);
                $row[] = app_format_money($aRow['amount'], $currency->name);

                if (empty($aRow['status'])){
                    $row[]="";
                }
                else if (!empty($aRow['status']) && $aRow['status']==2) {
                    $row[]=format_invoice_status(2);
                }else{
                    $row[]=format_invoice_status(1);
                }
                // $row[] = $totalLeftToPay;
                $this->db->select("value as value");
                $this->db->from('tblcustomfieldsvalues');
                $this->db->where('tblcustomfieldsvalues.relid',$aRow['ID']);
                $this->db->where('tblcustomfieldsvalues.fieldid',7);
                $this->db->where('tblcustomfieldsvalues.fieldto',"invoice");
                $query = $this->db->get()->result();
                // var_dump($query);die;
                if (empty($query)){
                    $row[] = '';
                }else{
                    $row[] = $query->value;
                }



                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            exit;
        }
    }



    public function expenses($type = 'simple_report')
    {
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['currencies']    = $this->currencies_model->get();

        $data['title'] = _l('expenses_report');
        if ($type != 'simple_report') {
            $this->load->model('expenses_model');
            $data['categories'] = $this->expenses_model->get_category();
            $data['years']      = $this->expenses_model->get_expenses_years();

            $this->load->model('payment_modes_model');
            $data['payment_modes']  = $this->payment_modes_model->get('', [], true);

            if ($this->input->is_ajax_request()) {
                $aColumns = [
                    db_prefix().'expenses.category',
                    db_prefix().'expenses.amount as amount',
                    'expense_name',
                    'tax',
                    'tax2',
                    '(SELECT taxrate FROM ' . db_prefix() . 'taxes WHERE id=' . db_prefix() . 'expenses.tax)',
                    db_prefix().'expenses.amount as amount_with_tax',
                    'billable',
                    'date',
                    get_sql_select_client_company(),
                    'invoiceid',
                    'reference_no',
                    'paymentmode',
                ];
                $join = [
                    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'expenses.clientid',
                    'LEFT JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category',
                ];
                $where  = [];
                $filter = [];
                include_once(APPPATH . 'views/admin/tables/includes/expenses_filter.php');
                if (count($filter) > 0) {
                    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
                }

                $by_currency = $this->input->post('currency');
                if ($by_currency) {
                    $currency = $this->currencies_model->get($by_currency);
                    array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
                } else {
                    $currency = $this->currencies_model->get_base_currency();
                }

                $sIndexColumn = 'id';
                $sTable       = db_prefix() . 'expenses';
                $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                    db_prefix() . 'expenses_categories.name as category_name',
                    db_prefix() . 'expenses.id',
                    db_prefix() . 'expenses.clientid',
                    'currency',
                ]);
                $output  = $result['output'];
                $rResult = $result['rResult'];
                $this->load->model('currencies_model');
                $this->load->model('payment_modes_model');

                $footer_data = [
                    'tax_1'           => 0,
                    'tax_2'           => 0,
                    'amount'          => 0,
                    'total_tax'       => 0,
                    'amount_with_tax' => 0,
                ];

                foreach ($rResult as $aRow) {
                    $row = [];
                    for ($i = 0; $i < count($aColumns); $i++) {
                        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                            $_data = $aRow[strafter($aColumns[$i], 'as ')];
                        } else {
                            $_data = isset($aRow[$aColumns[$i]]) ? $aRow[$aColumns[$i]] : 0;
                        }
                        if ($aRow['tax'] != 0) {
                            $_tax = get_tax_by_id($aRow['tax']);
                        }
                        if ($aRow['tax2'] != 0) {
                            $_tax2 = get_tax_by_id($aRow['tax2']);
                        }
                        if ($aColumns[$i] == 'category') {
                            $_data = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" target="_blank">' . $aRow['category_name'] . '</a>';
                        } elseif ($aColumns[$i] == 'expense_name') {
                            $_data = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" target="_blank">' . $aRow['expense_name'] . '</a>';
                        } elseif ($aColumns[$i] == 'amount' || $i == 6) {
                            $total = $_data;
                            if ($i != 6) {
                                $footer_data['amount'] += $total;
                            } else {
                                if ($aRow['tax'] != 0 && $i == 6) {
                                    $total += ($total / 100 * $_tax->taxrate);
                                }
                                if ($aRow['tax2'] != 0 && $i == 6) {
                                    $total += ($aRow['amount'] / 100 * $_tax2->taxrate);
                                }
                                $footer_data['amount_with_tax'] += $total;
                            }

                            $_data = app_format_money($total, $currency->name);
                        } elseif ($i == 9) {
                            $_data = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
                        } elseif ($aColumns[$i] == 'paymentmode') {
                            $_data = '';
                            if ($aRow['paymentmode'] != '0' && !empty($aRow['paymentmode'])) {
                                $payment_mode = $this->payment_modes_model->get($aRow['paymentmode'], [], false, true);
                                if ($payment_mode) {
                                    $_data = $payment_mode->name;
                                }
                            }
                        } elseif ($aColumns[$i] == 'date') {
                            $_data = _d($_data);
                        } elseif ($aColumns[$i] == 'tax') {
                            if ($aRow['tax'] != 0) {
                                $_data = $_tax->name . ' - ' . app_format_number($_tax->taxrate) . '%';
                            } else {
                                $_data = '';
                            }
                        } elseif ($aColumns[$i] == 'tax2') {
                            if ($aRow['tax2'] != 0) {
                                $_data = $_tax2->name . ' - ' . app_format_number($_tax2->taxrate) . '%';
                            } else {
                                $_data = '';
                            }
                        } elseif ($i == 5) {
                            if ($aRow['tax'] != 0 || $aRow['tax2'] != 0) {
                                if ($aRow['tax'] != 0) {
                                    $total = ($total / 100 * $_tax->taxrate);
                                    $footer_data['tax_1'] += $total;
                                }
                                if ($aRow['tax2'] != 0) {
                                    $total += ($aRow['amount'] / 100 * $_tax2->taxrate);
                                    $footer_data['tax_2'] += $total;
                                }
                                $_data = app_format_money($total, $currency->name);
                                $footer_data['total_tax'] += $total;
                            } else {
                                $_data = app_format_number(0);
                            }
                        } elseif ($aColumns[$i] == 'billable') {
                            if ($aRow['billable'] == 1) {
                                $_data = _l('expenses_list_billable');
                            } else {
                                $_data = _l('expense_not_billable');
                            }
                        } elseif ($aColumns[$i] == 'invoiceid') {
                            if ($_data) {
                                $_data = '<a href="' . admin_url('invoices/list_invoices/' . $_data) . '">' . format_invoice_number($_data) . '</a>';
                            } else {
                                $_data = '';
                            }
                        }
                        $row[] = $_data;
                    }
                    $output['aaData'][] = $row;
                }

                foreach ($footer_data as $key => $total) {
                    $footer_data[$key] = app_format_money($total, $currency->name);
                }

                $output['sums'] = $footer_data;
                echo json_encode($output);
                die;
            }
            $this->load->view('admin/reports/expenses_detailed', $data);
        } else {
            if (!$this->input->get('year')) {
                $data['current_year'] = date('Y');
            } else {
                $data['current_year'] = $this->input->get('year');
            }


            $data['export_not_supported'] = ($this->agent->browser() == 'Internet Explorer' || $this->agent->browser() == 'Spartan');

            $this->load->model('expenses_model');

            $data['chart_not_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('not_billable_expenses_by_categories'), [
                'billable' => 0,
            ], [
                'backgroundColor' => 'rgba(252,45,66,0.4)',
                'borderColor'     => '#fc2d42',
            ], $data['current_year']));

            $data['chart_billable'] = json_encode($this->reports_model->get_stats_chart_data(_l('billable_expenses_by_categories'), [
                'billable' => 1,
            ], [
                'backgroundColor' => 'rgba(37,155,35,0.2)',
                'borderColor'     => '#84c529',
            ], $data['current_year']));

            $data['expense_years'] = $this->expenses_model->get_expenses_years();

            if (count($data['expense_years']) > 0) {
                // Perhaps no expenses in new year?
                if (!in_array_multidimensional($data['expense_years'], 'year', date('Y'))) {
                    array_unshift($data['expense_years'], ['year' => date('Y')]);
                }
            }

            $data['categories'] = $this->expenses_model->get_category();

            $this->load->view('admin/reports/expenses', $data);
        }
    }

    public function expenses_vs_income($year = '')
    {
        $_expenses_years = [];
        $_years          = [];
        $this->load->model('expenses_model');
        $expenses_years = $this->expenses_model->get_expenses_years();
        $payments_years = $this->reports_model->get_distinct_payments_years();

        foreach ($expenses_years as $y) {
            array_push($_years, $y['year']);
        }
        foreach ($payments_years as $y) {
            array_push($_years, $y['year']);
        }

        $_years = array_map('unserialize', array_unique(array_map('serialize', $_years)));

        if (!in_array(date('Y'), $_years)) {
            $_years[] = date('Y');
        }

        rsort($_years, SORT_NUMERIC);
        $data['report_year'] = $year == '' ? date('Y') : $year;

        $data['years']                           = $_years;
        $data['chart_expenses_vs_income_values'] = json_encode($this->reports_model->get_expenses_vs_income_report($year));
        $data['base_currency']                   = get_base_currency();
        $data['title']                           = _l('als_expenses_vs_income');
        $this->load->view('admin/reports/expenses_vs_income', $data);
    }

    /* Total income report / ajax chart*/
    public function total_income_report()
    {
        echo json_encode($this->reports_model->total_income_report());
    }

    public function report_by_payment_modes()
    {
        echo json_encode($this->reports_model->report_by_payment_modes());
    }

    public function report_by_customer_groups()
    {
        echo json_encode($this->reports_model->report_by_customer_groups());
    }

    public function cashbook_report()
    {
        // Cashbook report function

        // Always process the request, regardless of whether it's AJAX or not
        // This ensures we always return valid JSON
        try {
            $this->load->model('currencies_model');
            $this->load->model('invoices_model');
            $this->load->model('payments_model');
            $this->load->model('credit_notes_model');
            $this->load->model('payment_modes_model');

            // Load purchase model for vendor payments
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in cashbook_report: ' . $e->getMessage());
            }

            // Get all payment modes
            $all_payment_modes = $this->payment_modes_model->get();

            // Filter payment modes to cash, bank, and others
            $payment_modes = [];
            $cash_mode = null;
            $bank_modes = [];
            $other_modes = [];

            foreach ($all_payment_modes as $mode) {
                if ($mode['id'] == 2) { // Cash payment mode
                    $cash_mode = $mode;
                    $payment_modes[] = $mode;
                } elseif (stripos($mode['name'], 'bank') !== false) { // Bank payment modes
                    $bank_modes[] = $mode;
                    $payment_modes[] = $mode;
                } else { // Other payment modes
                    $other_modes[] = $mode;
                }
            }

            // Add a combined "Others" payment mode if there are any other modes
            if (!empty($other_modes)) {
                $payment_modes[] = [
                    'id' => 'others',
                    'name' => 'Others'
                ];
            }

            // Generate payment mode columns
            $payment_mode_columns = [];
            foreach ($payment_modes as $mode) {
                if ($mode['id'] === 'others') {
                    // For "Others" category, include all payment modes that are not cash or bank
                    $other_mode_ids = array_map(function($m) { return $m['id']; }, $other_modes);
                    if (!empty($other_mode_ids)) {
                        $payment_mode_columns['payment_mode_others'] = '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode IN (' . implode(',', $other_mode_ids) . ')) as payment_mode_others';
                    } else {
                        $payment_mode_columns['payment_mode_others'] = '0 as payment_mode_others';
                    }
                } else {
                    $payment_mode_columns['payment_mode_' . $mode['id']] = '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = ' . $mode['id'] . ') as payment_mode_' . $mode['id'];
                }
            }

            // Define the columns to select directly from source tables
            $select = [
                db_prefix() . 'invoices.number',
                get_sql_select_client_company(),
                db_prefix() . 'invoices.status',
                'YEAR(' . db_prefix() . 'invoices.date) as year',
                db_prefix() . 'invoices.date',
                db_prefix() . 'invoices.duedate',
                db_prefix() . 'invoices.total as invoice_amount',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash_paid',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as cash_paid_out',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date)) as today_amount_due',
                '(' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as total_invoice_due',
                '(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "' . db_prefix() . 'acc_accounts"), (SELECT COALESCE(balance,0) FROM ' . db_prefix() . 'acc_accounts WHERE key_name LIKE "%Zim%"  LIMIT 1), 0)) as zim_account',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE DATE(' . db_prefix() . 'creditnotes.date) = DATE(' . db_prefix() . 'invoices.date) AND ' . db_prefix() . 'creditnotes.clientid = ' . db_prefix() . 'invoices.clientid) as credit_note',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode != 2) as bank',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE ' . db_prefix() . 'creditnotes.clientid=' . db_prefix() . 'invoices.clientid AND ' . db_prefix() . 'creditnotes.date < ' . db_prefix() . 'invoices.date) as credit_bf',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE ' . db_prefix() . 'creditnotes.clientid=' . db_prefix() . 'invoices.clientid AND ' . db_prefix() . 'creditnotes.date <= ' . db_prefix() . 'invoices.date) as credit_cf',
                '(' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE DATE(' . db_prefix() . 'creditnotes.date) = DATE(' . db_prefix() . 'invoices.date) AND ' . db_prefix() . 'creditnotes.clientid = ' . db_prefix() . 'invoices.clientid)) as total_balance',
                db_prefix() . 'invoices.adminnote as director_note',
                // Add payment date column
                '(SELECT GROUP_CONCAT(date ORDER BY date SEPARATOR ", ") FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as payment_dates',
            ];

            // Add payment mode columns to select array
            foreach ($payment_mode_columns as $column) {
                $select[] = $column;
            }

            // Define where conditions
            $where = [
                'AND ' . db_prefix() . 'invoices.status != 5', // Not cancelled
            ];

            // Exclude cancelled invoices

            // Remove debug queries for better performance

            // Enable date filtering
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'invoices.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            // Apply date filtering

            // Enable customer filtering
            if ($this->input->post('customer_id')) {
                $customers = $this->input->post('customer_id');
                $_customers = [];
                if (is_array($customers)) {
                    foreach ($customers as $customer) {
                        if ($customer != '') {
                            array_push($_customers, $this->db->escape_str($customer));
                        }
                    }
                }
                if (count($_customers) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid IN (' . implode(', ', $_customers) . ')');
                }
            }

            // Apply customer filtering

            // Enable status filtering
            if ($this->input->post('invoice_status')) {
                $statuses = $this->input->post('invoice_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            // Apply status filtering

            // Always use base currency
            $currency = $this->currencies_model->get_base_currency();

            // Define columns for data tables
            $aColumns = [
                db_prefix() . 'invoices.date',
                db_prefix() . 'invoices.status',
                db_prefix() . 'invoices.number',
                'company',
                db_prefix() . 'invoices.total as invoice_amount',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash_paid',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as cash_paid_out',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date)) as today_amount_due',
                '(' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as total_invoice_due',
                '(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "' . db_prefix() . 'acc_accounts"), (SELECT COALESCE(balance,0) FROM ' . db_prefix() . 'acc_accounts WHERE key_name LIKE "%Zim%"  LIMIT 1), 0)) as zim_account',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE DATE(' . db_prefix() . 'creditnotes.date) = DATE(' . db_prefix() . 'invoices.date) AND ' . db_prefix() . 'creditnotes.clientid = ' . db_prefix() . 'invoices.clientid) as credit_note',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode != 2) as bank',
                '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE ' . db_prefix() . 'creditnotes.clientid=' . db_prefix() . 'invoices.clientid AND ' . db_prefix() . 'creditnotes.date < ' . db_prefix() . 'invoices.date) as credit_bf',
                '(SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE ' . db_prefix() . 'creditnotes.clientid=' . db_prefix() . 'invoices.clientid AND ' . db_prefix() . 'creditnotes.date <= ' . db_prefix() . 'invoices.date) as credit_cf',
                '(' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(total),0) FROM ' . db_prefix() . 'creditnotes WHERE DATE(' . db_prefix() . 'creditnotes.date) = DATE(' . db_prefix() . 'invoices.date) AND ' . db_prefix() . 'creditnotes.clientid = ' . db_prefix() . 'invoices.clientid)) as total_balance',
                db_prefix() . 'invoices.adminnote as director_note',

                // Add payment mode columns to aColumns array
                db_prefix() . 'invoices.id',
                db_prefix() . 'invoices.clientid'
            ];

            // Add payment mode columns to aColumns array
            foreach ($payment_mode_columns as $key => $column) {
                // Insert before the id and clientid
                array_splice($aColumns, count($aColumns) - 2, 0, [$column]);
            }

            $sIndexColumn = db_prefix() . 'invoices.id';
            $sTable = db_prefix() . 'invoices';
            $join = [
                'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
            ];

            // Try a direct query instead of using data_tables_init

            // Build a query with all required columns
            $this->db->select('
                ' . db_prefix() . 'invoices.id,
                ' . db_prefix() . 'invoices.clientid,
                ' . db_prefix() . 'invoices.date,
                ' . db_prefix() . 'invoices.status,
                ' . db_prefix() . 'invoices.number,
                ' . db_prefix() . 'clients.company,
                ' . db_prefix() . 'invoices.total as invoice_amount,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash_paid,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as cash_paid_out,
                (SELECT COALESCE(SUM(t.amount),0) FROM (SELECT amount, transactionid FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date) GROUP BY transactionid) as t) as today_amount_due,
                (' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as total_invoice_due,
                ' . db_prefix() . 'invoices.adminnote as director_note
            ');
            $this->db->from(db_prefix() . 'invoices');
            $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

            // Apply WHERE conditions from the $where array
            if (!empty($where)) {
                foreach ($where as $condition) {
                    // Remove the leading 'AND ' if present
                    $condition = preg_replace('/^AND /', '', $condition);
                    $this->db->where($condition, null, false);
                }
            }
            $this->db->order_by('tblinvoices.date', 'DESC');

            // Get pagination parameters from DataTables
            $limit = $this->input->post('length') ? (int)$this->input->post('length') : 25;
            $start = $this->input->post('start') ? (int)$this->input->post('start') : 0;

            // Apply pagination only if limit is not -1 (which means show all records)
            if ($limit > 0) {
                $this->db->limit($limit, $start);
            }

            // Execute the query
            $query = $this->db->get();

            // Check if the query returned any results
            if ($query->num_rows() > 0) {
                $direct_result = $query->result_array();
                // var_dump($this->db->last_query());die;

                // Get total count of all records (without filters)
                $total_count = $this->db->count_all(db_prefix() . 'invoices');

                // Get count of filtered records
                $this->db->select('COUNT(*) as filtered_count');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

                // Apply WHERE conditions for filtering
                if (!empty($where)) {
                    foreach ($where as $condition) {
                        $condition = preg_replace('/^AND /', '', $condition);
                        $this->db->where($condition, null, false);
                    }
                }

                $filtered_count = $this->db->get()->row()->filtered_count;

                // Use the direct query result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => $total_count,
                        'recordsFiltered' => $filtered_count,
                        'aaData' => []
                    ],
                    'rResult' => $direct_result
                ];
            } else {

                // Get total count of all records (without filters)
                $total_count = $this->db->count_all(db_prefix() . 'invoices');

                // For filtered count, we already know it's 0 since the query returned no results
                $filtered_count = 0;

                // Create an empty result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => $total_count,
                        'recordsFiltered' => $filtered_count,
                        'aaData' => []
                    ],
                    'rResult' => []
                ];
            }

            $output = $result['output'];
            $rResult = $result['rResult'];
//            var_dump($this->db->last_query()); die;

            // Get vendor payments if purchase model is loaded
            $vendor_payments = [];
            if ($purchase_model_loaded) {
                // Get vendor payment date filters
                $vendor_payment_from = $this->input->post('vendor_payment_from');
                $vendor_payment_to = $this->input->post('vendor_payment_to');

                // Get search term if provided
                $search_term = $this->input->post('search');
                $search_value = isset($search_term['value']) ? $search_term['value'] : '';

                if ($vendor_payment_from && $vendor_payment_to) {
                    // Convert to SQL date format
                    $vendor_payment_from = to_sql_date($vendor_payment_from);
                    $vendor_payment_to = to_sql_date($vendor_payment_to);

                    // Get all vendors
                    $vendors = $this->purchase_model->get_vendor();

                    // For each vendor, get their payments and filter by date
                    foreach ($vendors as $vendor) {
                        $payments = $this->purchase_model->get_payment_by_vendor($vendor['userid']);

                        foreach ($payments as $payment) {
                            // Check if payment date is within the filter range
                            if ($payment['date'] >= $vendor_payment_from && $payment['date'] <= $vendor_payment_to) {
                                // If search term is provided, check if it matches any of the payment fields
                                if (!empty($search_value)) {
                                    // Convert payment data to lowercase for case-insensitive search
                                    $payment_date = strtolower(date('Y-m-d', strtotime($payment['date'])));
                                    $payment_amount = strtolower((string)$payment['amount']);
                                    $payment_note = strtolower($payment['note'] ?? '');
                                    $payment_pur_order = strtolower($payment['pur_order_name'] ?? '');

                                    // Get vendor name
                                    $vendor_info = $this->purchase_model->get_vendor($payment['pur_order']);
                                    $vendor_name = strtolower($vendor_info->company ?? 'Unknown Vendor');

                                    // Get payment mode
                                    $payment_mode = '';
                                    if (isset($payment['paymentmode'])) {
                                        $this->db->select('name');
                                        $this->db->from(db_prefix() . 'payment_modes');
                                        $this->db->where('id', $payment['paymentmode']);
                                        $mode = $this->db->get()->row();
                                        if ($mode) {
                                            $payment_mode = strtolower($mode->name);
                                        }
                                    }

                                    // Convert search term to lowercase for case-insensitive search
                                    $search_value = strtolower($search_value);

                                    // Check if search term matches any of the payment fields
                                    if (strpos($payment_date, $search_value) !== false ||
                                        strpos($payment_amount, $search_value) !== false ||
                                        strpos($payment_note, $search_value) !== false ||
                                        strpos($payment_pur_order, $search_value) !== false ||
                                        strpos($vendor_name, $search_value) !== false ||
                                        strpos($payment_mode, $search_value) !== false) {
                                        // Add to vendor payments array if search term matches
                                        $vendor_payments[] = $payment;
                                    }
                                } else {
                                    // No search term, add all payments within date range
                                    $vendor_payments[] = $payment;
                                }
                            }
                        }
                    }
                }
            }



            $footer_data = [
                'invoice_amount' => 0,
                'cash_paid' => 0,
                'total_amount_paid' => 0,
                'today_amount_due' => 0,
                'total_invoice_due' => 0,
                'zim_account' => 0,
                'credit_note' => 0,
                'bank' => 0,
                'cash' => 0,
                'credit_bf' => 0,
                'credit_cf' => 0,
                'total_balance' => 0,
            ];

            // Add payment mode columns to footer data
            foreach ($payment_modes as $mode) {
                $footer_data['payment_mode_' . $mode['id']] = 0;
            }

            foreach ($rResult as $aRow) {
                $row = [];

                // Simplified columns for debugging
                $row[] = _d(isset($aRow['date']) ? $aRow['date'] : ''); // Date
                $row[] = format_invoice_status(isset($aRow['status']) ? $aRow['status'] : 0); // Status
                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>'; // Invoice Number

                // Account Name
                if (isset($aRow['deleted_customer_name']) && !empty($aRow['deleted_customer_name'])) {
                    $row[] = $aRow['deleted_customer_name'];
                } else {
                    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . (isset($aRow['company']) ? $aRow['company'] : 'Unknown') . '</a>';
                }

                $invoice_amount = isset($aRow['invoice_amount']) ? $aRow['invoice_amount'] : 0;
                $row[] = app_format_money($invoice_amount, $currency->name); // Total Invoice Amount
                $footer_data['invoice_amount'] += $invoice_amount;

                // Cash paid
                $cash_paid = isset($aRow['cash_paid']) ? $aRow['cash_paid'] : 0;
                $row[] = app_format_money($cash_paid, $currency->name);
                $footer_data['cash_paid'] += $cash_paid;

                // Total amount paid
                $cash_paid_out = isset($aRow['cash_paid_out']) ? $aRow['cash_paid_out'] : 0;
                $row[] = app_format_money($cash_paid_out, $currency->name);
                $footer_data['total_amount_paid'] += $cash_paid_out;

                // Today Amount Due
                $today_amount_due = isset($aRow['today_amount_due']) ? $aRow['today_amount_due'] : 0;
                $row[] = app_format_money($today_amount_due, $currency->name);
                $footer_data['today_amount_due'] += $today_amount_due;

                // Total Invoice Due
                $total_invoice_due = isset($aRow['total_invoice_due']) ? $aRow['total_invoice_due'] : 0;
                $row[] = app_format_money($total_invoice_due, $currency->name);
                $footer_data['total_invoice_due'] += $total_invoice_due;

                // Sales Order
                // Find the estimate (sales order) where invoiceid matches the current invoice id
                $this->db->select('id, number, prefix');
                $this->db->from(db_prefix() . 'estimates');
                $this->db->where('invoiceid', $aRow['id']);
                $sales_order = $this->db->get()->row();

                if ($sales_order) {
                    $sales_order_display = $sales_order->prefix . $sales_order->number;
                    $row[] = '<a href="' . admin_url('estimates/list_estimates/' . $sales_order->id) . '" target="_blank">' . $sales_order_display . '</a>';
                } else {
                    $row[] = '';
                }

                // Zim Account
                $zim_account = isset($aRow['zim_account']) ? $aRow['zim_account'] : 0;
                $row[] = app_format_money($zim_account, $currency->name);
                $footer_data['zim_account'] += $zim_account;

                // Credit Note
                $credit_note = isset($aRow['credit_note']) ? $aRow['credit_note'] : 0;
                $row[] = app_format_money($credit_note, $currency->name);
                $footer_data['credit_note'] += $credit_note;

                // Bank
                $bank = isset($aRow['bank']) ? $aRow['bank'] : 0;
                $row[] = app_format_money($bank, $currency->name);
                $footer_data['bank'] += $bank;

                // Cash
                $cash = isset($aRow['cash']) ? $aRow['cash'] : 0;
                $row[] = app_format_money($cash, $currency->name);
                $footer_data['cash'] += $cash;

                // Payment Modes
                foreach ($payment_modes as $mode) {
                    $payment_mode_key = 'payment_mode_' . $mode['id'];
                    $payment_mode_amount = isset($aRow[$payment_mode_key]) ? $aRow[$payment_mode_key] : 0;
                    $row[] = app_format_money($payment_mode_amount, $currency->name);
                    $footer_data[$payment_mode_key] += $payment_mode_amount;
                }

                // Credit BF
                $credit_bf = isset($aRow['credit_bf']) ? $aRow['credit_bf'] : 0;
                $row[] = app_format_money($credit_bf, $currency->name);
                $footer_data['credit_bf'] += $credit_bf;

                // Credit CF
                $credit_cf = isset($aRow['credit_cf']) ? $aRow['credit_cf'] : 0;
                $row[] = app_format_money($credit_cf, $currency->name);
                $footer_data['credit_cf'] += $credit_cf;

                // Total Balance
                $total_balance = isset($aRow['total_balance']) ? $aRow['total_balance'] : 0;
                $row[] = app_format_money($total_balance, $currency->name);
                $footer_data['total_balance'] += $total_balance;

                // Director Note - Get from custom field like in directors_report
                // Query the custom field value for this invoice
                $this->db->select("value");
                $this->db->from('tblcustomfieldsvalues');
                $this->db->where('tblcustomfieldsvalues.relid', $aRow['id']);
                $this->db->where('tblcustomfieldsvalues.fieldid', 7);
                $this->db->where('tblcustomfieldsvalues.fieldto', "invoice");
                $query = $this->db->get();

                if ($query && $query->num_rows() > 0) {
                    $row[] = $query->row()->value;
                } else {
                    // Fallback to adminnote if custom field is not found
                    $row[] = isset($aRow['director_note']) ? $aRow['director_note'] : '';
                }

                $output['aaData'][] = $row;

            }

            // Process vendor payments
            if ($purchase_model_loaded && !empty($vendor_payments)) {
                foreach ($vendor_payments as $payment) {
                    $row = [];

                    // Get vendor information
                    $vendor = $this->purchase_model->get_vendor($payment['pur_order']);
                    $vendor_name = isset($vendor->company) ? $vendor->company : 'Unknown Vendor';

                    // Format date
                    $row[] = _d($payment['date']); // Date

                    // Status - use a special status for vendor payments
                    $row[] = '<span class="label label-info">Vendor Payment</span>'; // Status

                    // Purchase Order Number
                    $row[] = '<a href="' . admin_url('purchase/purchase_order/' . $payment['pur_order']) . '" target="_blank">' . $payment['pur_order_name'] . '</a>'; // PO Number

                    // Vendor Name
                    $row[] = '<a href="' . admin_url('purchase/vendor/' . $vendor->userid) . '" target="_blank">' . $vendor_name . '</a>';

                    // Invoice Amount - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Cash Paid - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Total Amount Paid - Use payment amount as total amount paid
                    $row[] = app_format_money($payment['amount'], $currency->name);
                    $footer_data['total_amount_paid'] += $payment['amount'];

                    // VAT Refunded - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Amount Due - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Sales Order - N/A for vendor payments
                    $row[] = '';

                    // Zim Account - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Credit Note - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Bank - If payment mode is not cash, add to bank
                    if ($payment['paymentmode'] != 2) {
                        $row[] = app_format_money($payment['amount'], $currency->name);
                        $footer_data['bank'] += $payment['amount'];
                    } else {
                        $row[] = app_format_money(0, $currency->name);
                    }

                    // Cash - If payment mode is cash, add to cash
                    if ($payment['paymentmode'] == 2) {
                        $row[] = app_format_money($payment['amount'], $currency->name);
                        $footer_data['cash'] += $payment['amount'];
                    } else {
                        $row[] = app_format_money(0, $currency->name);
                    }

                    // Credit BF - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Credit CF - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Total Balance - N/A for vendor payments
                    $row[] = app_format_money(0, $currency->name);

                    // Director Note - Use payment note if available
                    $row[] = isset($payment['note']) ? $payment['note'] : '';

                    $output['aaData'][] = $row;
                }
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        } catch (Exception $e) {
            // Log the error

            // Return a valid JSON response with the error message
            echo json_encode([
                'error' => true,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage()
            ]);
            die();
        }
    }

    /* Leads conversion monthly report / ajax chart*/
    public function leads_monthly_report($month)
    {
        echo json_encode($this->reports_model->leads_monthly_report($month));
    }

    public function cashbook()
    {
        $data['title'] = _l('cashbook_report');
        $this->load->view('admin/reports/cashbook', $data);
    }

    /**
     * Combined cashbook report - shows both date-based and invoice-based payment information
     * This endpoint is used for the combined cashbook report
     */
    public function cashbook_combined_report()
    {
        try {
            $this->load->model('currencies_model');
            $this->load->model('invoices_model');
            $this->load->model('payments_model');
            $this->load->model('payment_modes_model');
            $this->load->model('credit_notes_model');

            // Get all payment modes
            $all_payment_modes = $this->payment_modes_model->get();

            // Filter payment modes to cash, bank, and others
            $cash_mode = null;
            $bank_modes = [];
            $other_modes = [];

            foreach ($all_payment_modes as $mode) {
                if ($mode['id'] == 2) { // Cash payment mode
                    $cash_mode = $mode;
                } elseif (stripos($mode['name'], 'bank') !== false) { // Bank payment modes
                    $bank_modes[] = $mode;
                } else { // Other payment modes
                    $other_modes[] = $mode;
                }
            }

            // Get other mode IDs
            $other_mode_ids = array_map(function($m) { return $m['id']; }, $other_modes);

            // Define where conditions
            $where = [
                'AND ' . db_prefix() . 'invoices.status != 5', // Not cancelled
            ];

            // Enable date filtering
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'invoices.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            // Enable customer filtering
            if ($this->input->post('customer_id')) {
                $customers = $this->input->post('customer_id');
                $_customers = [];
                if (is_array($customers)) {
                    foreach ($customers as $customer) {
                        if ($customer != '') {
                            array_push($_customers, $this->db->escape_str($customer));
                        }
                    }
                }
                if (count($_customers) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid IN (' . implode(', ', $_customers) . ')');
                }
            }

            // Enable status filtering
            if ($this->input->post('invoice_status')) {
                $statuses = $this->input->post('invoice_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            // Always use base currency
            $currency = $this->currencies_model->get_base_currency();

            // Build a query to get invoices with payment information
            $this->db->select('
                ' . db_prefix() . 'invoices.id,
                ' . db_prefix() . 'invoices.clientid,
                ' . db_prefix() . 'invoices.date,
                ' . db_prefix() . 'invoices.status,
                ' . db_prefix() . 'invoices.number,
                ' . db_prefix() . 'clients.company,
                ' . db_prefix() . 'invoices.total as invoice_amount,
                (SELECT GROUP_CONCAT(date ORDER BY date SEPARATOR ", ") FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as payment_dates,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode != 2 AND ' . 
                (!empty($bank_modes) ? 'paymentmode IN (' . implode(',', array_map(function($m) { return $m['id']; }, $bank_modes)) . ')' : 'FALSE') . ') as bank,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND ' . 
                (!empty($other_mode_ids) ? 'paymentmode IN (' . implode(',', $other_mode_ids) . ')' : 'FALSE') . ') as payment_mode_others,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as total_amount_paid,
                (' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as total_invoice_due,
                ' . db_prefix() . 'invoices.adminnote as director_note,

                /* New columns for payments on invoice date */
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date)) as total_paid_on_invoice_date,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date) AND paymentmode = 2) as cash_paid_on_invoice_date,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date) AND paymentmode != 2 AND ' . 
                (!empty($bank_modes) ? 'paymentmode IN (' . implode(',', array_map(function($m) { return $m['id']; }, $bank_modes)) . ')' : 'FALSE') . ') as bank_paid_on_invoice_date,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE DATE(' . db_prefix() . 'invoicepaymentrecords.date) = DATE(' . db_prefix() . 'invoices.date) AND ' . 
                (!empty($other_mode_ids) ? 'paymentmode IN (' . implode(',', $other_mode_ids) . ')' : 'FALSE') . ') as others_paid_on_invoice_date,

                /* Sales Order data for searching */
                (SELECT CONCAT(prefix, number) FROM ' . db_prefix() . 'estimates WHERE invoiceid = ' . db_prefix() . 'invoices.id) as sales_order_number
            ');

            $this->db->from(db_prefix() . 'invoices');
            $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

            // Apply WHERE conditions from the $where array
            if (!empty($where)) {
                foreach ($where as $condition) {
                    // Remove the leading 'AND ' if present
                    $condition = preg_replace('/^AND /', '', $condition);
                    $this->db->where($condition, null, false);
                }
            }

            $this->db->order_by('tblinvoices.date', 'DESC');

            // Get pagination parameters from DataTables
            $limit = $this->input->post('length') ? (int)$this->input->post('length') : 25;
            $start = $this->input->post('start') ? (int)$this->input->post('start') : 0;

            // Apply pagination only if limit is not -1 (which means show all records)
            if ($limit > 0) {
                $this->db->limit($limit, $start);
            }

            // Execute the query
            $query = $this->db->get();

            // Check if the query returned any results
            if ($query->num_rows() > 0) {
                $direct_result = $query->result_array();

                // Get total count of all records (without filters)
                $this->db->select('COUNT(*) as filtered_count');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

                // Apply WHERE conditions for filtering
                if (!empty($where)) {
                    foreach ($where as $condition) {
                        $condition = preg_replace('/^AND /', '', $condition);
                        $this->db->where($condition, null, false);
                    }
                }

                $filtered_count = $this->db->get()->row()->filtered_count;

                // Get total count without filters
                $this->db->select('COUNT(*) as total_count');
                $this->db->from(db_prefix() . 'invoices');
                $total_count = $this->db->get()->row()->total_count;

                // Use the direct query result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => $total_count,
                        'recordsFiltered' => $filtered_count,
                        'aaData' => []
                    ],
                    'rResult' => $direct_result
                ];
            } else {
                // Create an empty result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'aaData' => []
                    ],
                    'rResult' => []
                ];
            }

            $output = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'invoice_amount' => 0,
                'cash' => 0,
                'bank' => 0,
                'payment_mode_others' => 0,
                'total_amount_paid' => 0,
                'total_invoice_due' => 0,
                'total_paid_on_invoice_date' => 0,
                'cash_paid_on_invoice_date' => 0,
                'bank_paid_on_invoice_date' => 0,
                'others_paid_on_invoice_date' => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                // Date
                $row[] = _d(isset($aRow['date']) ? $aRow['date'] : '');

                // Status
                $row[] = format_invoice_status(isset($aRow['status']) ? $aRow['status'] : 0);

                // Invoice Number
                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';

                // Customer Name
                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . (isset($aRow['company']) ? $aRow['company'] : 'Unknown') . '</a>';

                // Invoice Amount
                $invoice_amount = isset($aRow['invoice_amount']) ? $aRow['invoice_amount'] : 0;
                $row[] = app_format_money($invoice_amount, $currency->name);
                $footer_data['invoice_amount'] += $invoice_amount;

                // Payment Dates
                $row[] = isset($aRow['payment_dates']) ? $aRow['payment_dates'] : '';

                // Cash
                $cash = isset($aRow['cash']) ? $aRow['cash'] : 0;
                $row[] = app_format_money($cash, $currency->name);
                $footer_data['cash'] += $cash;

                // Bank
                $bank = isset($aRow['bank']) ? $aRow['bank'] : 0;
                $row[] = app_format_money($bank, $currency->name);
                $footer_data['bank'] += $bank;

                // Others
                $others = isset($aRow['payment_mode_others']) ? $aRow['payment_mode_others'] : 0;
                $row[] = app_format_money($others, $currency->name);
                $footer_data['payment_mode_others'] += $others;

                // Total Amount Paid
                $total_amount_paid = isset($aRow['total_amount_paid']) ? $aRow['total_amount_paid'] : 0;
                $row[] = app_format_money($total_amount_paid, $currency->name);
                $footer_data['total_amount_paid'] += $total_amount_paid;

                // Total Invoice Due
                $total_invoice_due = isset($aRow['total_invoice_due']) ? $aRow['total_invoice_due'] : 0;
                $row[] = app_format_money($total_invoice_due, $currency->name);
                $footer_data['total_invoice_due'] += $total_invoice_due;

                // Sales Order
                if (!empty($aRow['sales_order_number'])) {
                    // Find the estimate (sales order) ID for the link
                    $this->db->select('id');
                    $this->db->from(db_prefix() . 'estimates');
                    $this->db->where('invoiceid', $aRow['id']);
                    $sales_order = $this->db->get()->row();

                    if ($sales_order) {
                        $row[] = '<a href="' . admin_url('estimates/list_estimates/' . $sales_order->id) . '" target="_blank">' . $aRow['sales_order_number'] . '</a>';
                    } else {
                        $row[] = $aRow['sales_order_number'];
                    }
                } else {
                    $row[] = '';
                }

                // Add new columns for payments on invoice date
                // Total paid on invoice date
                $total_paid_on_invoice_date = isset($aRow['total_paid_on_invoice_date']) ? $aRow['total_paid_on_invoice_date'] : 0;
                $row[] = app_format_money($total_paid_on_invoice_date, $currency->name);
                $footer_data['total_paid_on_invoice_date'] += $total_paid_on_invoice_date;

                // Cash paid on invoice date
                $cash_paid_on_invoice_date = isset($aRow['cash_paid_on_invoice_date']) ? $aRow['cash_paid_on_invoice_date'] : 0;
                $row[] = app_format_money($cash_paid_on_invoice_date, $currency->name);
                $footer_data['cash_paid_on_invoice_date'] += $cash_paid_on_invoice_date;

                // Bank paid on invoice date
                $bank_paid_on_invoice_date = isset($aRow['bank_paid_on_invoice_date']) ? $aRow['bank_paid_on_invoice_date'] : 0;
                $row[] = app_format_money($bank_paid_on_invoice_date, $currency->name);
                $footer_data['bank_paid_on_invoice_date'] += $bank_paid_on_invoice_date;

                // Others paid on invoice date
                $others_paid_on_invoice_date = isset($aRow['others_paid_on_invoice_date']) ? $aRow['others_paid_on_invoice_date'] : 0;
                $row[] = app_format_money($others_paid_on_invoice_date, $currency->name);
                $footer_data['others_paid_on_invoice_date'] += $others_paid_on_invoice_date;

                // Director Note
                $row[] = isset($aRow['director_note']) ? $aRow['director_note'] : '';

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        } catch (Exception $e) {
            // Return a valid JSON response with the error message
            echo json_encode([
                'error' => true,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage()
            ]);
            die();
        }
    }

    /**
     * Cashbook report by invoice - shows payments grouped by invoice
     * This endpoint is used for the second tab in the cashbook report
     */
    public function cashbook_report_by_invoice()
    {
        try {
            $this->load->model('currencies_model');
            $this->load->model('invoices_model');
            $this->load->model('payments_model');
            $this->load->model('payment_modes_model');

            // Get all payment modes
            $all_payment_modes = $this->payment_modes_model->get();

            // Filter payment modes to cash, bank, and others
            $cash_mode = null;
            $bank_modes = [];
            $other_modes = [];

            foreach ($all_payment_modes as $mode) {
                if ($mode['id'] == 2) { // Cash payment mode
                    $cash_mode = $mode;
                } elseif (stripos($mode['name'], 'bank') !== false) { // Bank payment modes
                    $bank_modes[] = $mode;
                } else { // Other payment modes
                    $other_modes[] = $mode;
                }
            }

            // Get other mode IDs
            $other_mode_ids = array_map(function($m) { return $m['id']; }, $other_modes);

            // Define where conditions
            $where = [
                'AND ' . db_prefix() . 'invoices.status != 5', // Not cancelled
            ];

            // Enable date filtering
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'invoices.date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            // Enable customer filtering
            if ($this->input->post('customer_id')) {
                $customers = $this->input->post('customer_id');
                $_customers = [];
                if (is_array($customers)) {
                    foreach ($customers as $customer) {
                        if ($customer != '') {
                            array_push($_customers, $this->db->escape_str($customer));
                        }
                    }
                }
                if (count($_customers) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid IN (' . implode(', ', $_customers) . ')');
                }
            }

            // Enable status filtering
            if ($this->input->post('invoice_status')) {
                $statuses = $this->input->post('invoice_status');
                $_statuses = [];
                if (is_array($statuses)) {
                    foreach ($statuses as $status) {
                        if ($status != '') {
                            array_push($_statuses, $this->db->escape_str($status));
                        }
                    }
                }
                if (count($_statuses) > 0) {
                    array_push($where, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(', ', $_statuses) . ')');
                }
            }

            // Always use base currency
            $currency = $this->currencies_model->get_base_currency();

            // Build a query to get invoices with payment information
            $this->db->select('
                ' . db_prefix() . 'invoices.id,
                ' . db_prefix() . 'invoices.clientid,
                ' . db_prefix() . 'invoices.number,
                ' . db_prefix() . 'clients.company,
                ' . db_prefix() . 'invoices.total as invoice_amount,
                (SELECT GROUP_CONCAT(date ORDER BY date SEPARATOR ", ") FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as payment_dates,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode = 2) as cash,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND paymentmode != 2 AND ' . 
                (!empty($bank_modes) ? 'paymentmode IN (' . implode(',', array_map(function($m) { return $m['id']; }, $bank_modes)) . ')' : 'FALSE') . ') as bank,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND ' . 
                (!empty($other_mode_ids) ? 'paymentmode IN (' . implode(',', $other_mode_ids) . ')' : 'FALSE') . ') as payment_mode_others,
                (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) as total_amount_paid,
                (' . db_prefix() . 'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id)) as total_invoice_due
            ');

            $this->db->from(db_prefix() . 'invoices');
            $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

            // Apply WHERE conditions from the $where array
            if (!empty($where)) {
                foreach ($where as $condition) {
                    // Remove the leading 'AND ' if present
                    $condition = preg_replace('/^AND /', '', $condition);
                    $this->db->where($condition, null, false);
                }
            }

            // Only include invoices that have payments
            $this->db->having('total_amount_paid > 0');

            $this->db->order_by('tblinvoices.number', 'ASC');

            // Get pagination parameters from DataTables
            $limit = $this->input->post('length') ? (int)$this->input->post('length') : 25;
            $start = $this->input->post('start') ? (int)$this->input->post('start') : 0;

            // Apply pagination only if limit is not -1 (which means show all records)
            if ($limit > 0) {
                $this->db->limit($limit, $start);
            }

            // Execute the query
            $query = $this->db->get();

            // Check if the query returned any results
            if ($query->num_rows() > 0) {
                $direct_result = $query->result_array();

                // Get total count of all records (without filters)
                $this->db->select('COUNT(*) as filtered_count');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');
                $this->db->where('(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) > 0', null, false);

                // Apply WHERE conditions for filtering
                if (!empty($where)) {
                    foreach ($where as $condition) {
                        $condition = preg_replace('/^AND /', '', $condition);
                        $this->db->where($condition, null, false);
                    }
                }

                $filtered_count = $this->db->get()->row()->filtered_count;

                // Get total count without filters but only including invoices with payments
                $this->db->select('COUNT(*) as total_count');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->where('(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) > 0', null, false);
                $total_count = $this->db->get()->row()->total_count;

                // Use the direct query result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => $total_count,
                        'recordsFiltered' => $filtered_count,
                        'aaData' => []
                    ],
                    'rResult' => $direct_result
                ];
            } else {
                // Create an empty result with proper counts
                $result = [
                    'output' => [
                        'draw' => $this->input->post('draw') ? $this->input->post('draw') : 1,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'aaData' => []
                    ],
                    'rResult' => []
                ];
            }

            $output = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'invoice_amount' => 0,
                'cash' => 0,
                'bank' => 0,
                'payment_mode_others' => 0,
                'total_amount_paid' => 0,
                'total_invoice_due' => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                // Invoice Number
                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';

                // Customer Name
                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . (isset($aRow['company']) ? $aRow['company'] : 'Unknown') . '</a>';

                // Invoice Amount
                $invoice_amount = isset($aRow['invoice_amount']) ? $aRow['invoice_amount'] : 0;
                $row[] = app_format_money($invoice_amount, $currency->name);
                $footer_data['invoice_amount'] += $invoice_amount;

                // Payment Dates
                $row[] = isset($aRow['payment_dates']) ? $aRow['payment_dates'] : '';

                // Cash
                $cash = isset($aRow['cash']) ? $aRow['cash'] : 0;
                $row[] = app_format_money($cash, $currency->name);
                $footer_data['cash'] += $cash;

                // Bank
                $bank = isset($aRow['bank']) ? $aRow['bank'] : 0;
                $row[] = app_format_money($bank, $currency->name);
                $footer_data['bank'] += $bank;

                // Others
                $others = isset($aRow['payment_mode_others']) ? $aRow['payment_mode_others'] : 0;
                $row[] = app_format_money($others, $currency->name);
                $footer_data['payment_mode_others'] += $others;

                // Total Amount Paid
                $total_amount_paid = isset($aRow['total_amount_paid']) ? $aRow['total_amount_paid'] : 0;
                $row[] = app_format_money($total_amount_paid, $currency->name);
                $footer_data['total_amount_paid'] += $total_amount_paid;

                // Total Invoice Due
                $total_invoice_due = isset($aRow['total_invoice_due']) ? $aRow['total_invoice_due'] : 0;
                $row[] = app_format_money($total_invoice_due, $currency->name);
                $footer_data['total_invoice_due'] += $total_invoice_due;

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        } catch (Exception $e) {
            // Return a valid JSON response with the error message
            echo json_encode([
                'error' => true,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage()
            ]);
            die();
        }
    }


    /**
     * This function has been removed as we now fetch data directly from the database tables
     * instead of creating a temporary table. The cashbook_report() function handles this.
     */

    private function distinct_taxes($rel_type)
    {
        return $this->db->query('SELECT DISTINCT taxname,taxrate FROM ' . db_prefix() . "item_tax WHERE rel_type='" . $rel_type . "' ORDER BY taxname ASC")->result_array();
    }

    public function top_customers_vendors()
    {
        $data = [];

        if ($this->input->post()) {
            $this->load->model('currencies_model');

            // Try to load the purchase model
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in top_customers_vendors: ' . $e->getMessage());
                set_alert('warning', _l('purchase_module_not_available'));
            }

            $limit = $this->input->post('limit') ? $this->input->post('limit') : 10;
            $transaction_type = $this->input->post('transaction_type') ? $this->input->post('transaction_type') : 'both';
            $metric = $this->input->post('metric') ? $this->input->post('metric') : 'amount';

            // Get time period filter
            $report_months = $this->input->post('report_months');
            $report_from = $this->input->post('report_from');
            $report_to = $this->input->post('report_to');

            // Store selected values for the view
            $data['report_months'] = $report_months;
            if ($report_months == 'custom') {
                $data['report_from'] = $report_from;
                $data['report_to'] = $report_to;
            }

            // Get date filter SQL
            $date_filter = '';
            if ($report_months) {
                $date_filter = $this->get_where_report_period('date');
            }

            $data['report_data'] = $this->reports_model->get_top_customers_vendors($limit, $transaction_type, $metric, $date_filter);
        }

        $data['title'] = _l('top_customers_vendors');

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/reports/top_customers_vendors', $data);
        } else {
            $this->load->view('admin/reports/top_customers_vendors_wrapper', $data);
        }
    }

    /**
     * Top/Least Buyers per Item report
     * Shows which customers or vendors have purchased a selected product the most or least
     */
    public function top_buyers_per_item()
    {
        $data = [];

        // Load required models
        $this->load->model('currencies_model');

        // Try to load the purchase model
        $purchase_model_loaded = false;
        try {
            $this->load->model('purchase/purchase_model');
            $purchase_model_loaded = true;
        } catch (Exception $e) {
            log_activity('Failed to load purchase model in top_buyers_per_item: ' . $e->getMessage());
            set_alert('warning', _l('purchase_module_not_available'));
        }

        $this->load->model('Invoice_items_model', 'items_model');

        // Get all items for the dropdown
        $data['items'] = $this->items_model->get();

        if ($this->input->post()) {
            $item_id = $this->input->post('item_id');
            $transaction_type = $this->input->post('transaction_type') ? $this->input->post('transaction_type') : 'both';
            $ranking = $this->input->post('ranking') ? $this->input->post('ranking') : 'top';
            $limit = $this->input->post('limit') ? $this->input->post('limit') : 10;
            $metric = $this->input->post('metric') ? $this->input->post('metric') : 'quantity';

            // Get time period filter
            $report_months = $this->input->post('report_months');
            $report_from = $this->input->post('report_from');
            $report_to = $this->input->post('report_to');

            // Store selected values for the view
            $data['report_months'] = $report_months;
            if ($report_months == 'custom') {
                $data['report_from'] = $report_from;
                $data['report_to'] = $report_to;
            }

            // Get date filter SQL
            $date_filter = '';
            if ($report_months) {
                $date_filter = $this->get_where_report_period('date');
            }

            $data['report_data'] = $this->reports_model->get_buyers_by_item(
                $item_id,
                $transaction_type,
                $ranking,
                $limit,
                $metric,
                $date_filter
            );

            // Store the selected parameters for the view
            $data['selected_item'] = $item_id;
            $data['selected_transaction_type'] = $transaction_type;
            $data['selected_ranking'] = $ranking;
            $data['selected_limit'] = $limit;
            $data['selected_metric'] = $metric;
        }

        $data['title'] = _l('top_buyers_per_item');

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/reports/top_buyers_per_item', $data);
        } else {
            $this->load->view('admin/reports/top_buyers_per_item_wrapper', $data);
        }
    }

    /**
     * Most/Least Items Bought by Contact report
     * Shows which products were bought the most or least by a specific contact (customer or vendor)
     *
     * @param  string  $contact_type   customer or vendor
     * @param  integer $contact_id     contact id
     * @return view
     */
    /**
     * AVG purchase aging report
     * Shows the average age of items from the time they were purchased
     */
    public function avg_purchase_aging()
    {
        $data = [];

        // Load required models
        $this->load->model('currencies_model');
        $this->load->model('Invoice_items_model', 'items_model');

        // Get all items for the dropdown
        $data['items'] = $this->items_model->get();

        if ($this->input->post()) {
            $transaction_type = $this->input->post('transaction_type') ? $this->input->post('transaction_type') : 'both';

            // Get time period filter
            $report_months = $this->input->post('report_months');
            $report_from = $this->input->post('report_from');
            $report_to = $this->input->post('report_to');

            // Store selected values for the view
            $data['report_months'] = $report_months;
            if ($report_months == 'custom') {
                $data['report_from'] = $report_from;
                $data['report_to'] = $report_to;
            }

            // Get date filter SQL
            $date_filter = '';
            if ($report_months) {
                $date_filter = $this->get_where_report_period('date');
            }

            $data['report_data'] = $this->reports_model->get_avg_purchase_aging(
                $transaction_type,
                $date_filter
            );

            // Store the selected parameters for the view
            $data['selected_transaction_type'] = $transaction_type;
        }

        $data['title'] = _l('avg_purchase_aging');

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/reports/avg_purchase_aging', $data);
        } else {
            $this->load->view('admin/reports/avg_purchase_aging_wrapper', $data);
        }
    }

    public function contact_items_report($contact_type = '', $contact_id = '')
    {
        $data = [];

        // Load required models
        $this->load->model('currencies_model');

        // Set default values
        $data['contact_type'] = $contact_type;
        $data['contact_id'] = $contact_id;

        // Get contact details
        if ($contact_type == 'customer') {
            $this->load->model('clients_model');
            $contact = $this->clients_model->get($contact_id);
            $data['contact_name'] = $contact ? $contact->company : '';
        } elseif ($contact_type == 'vendor') {
            // Try to load the purchase model
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in contact_items_report: ' . $e->getMessage());
            }

            if ($purchase_model_loaded) {
                $contact = $this->purchase_model->get_vendor($contact_id);
                $data['contact_name'] = $contact ? $contact->company : '';
            } else {
                $data['contact_name'] = '';
                set_alert('warning', _l('purchase_module_not_available'));
            }
        }

        if ($this->input->post()) {
            $transaction_type = $this->input->post('transaction_type') ? $this->input->post('transaction_type') : 'both';
            $ranking = $this->input->post('ranking') ? $this->input->post('ranking') : 'most';
            $limit = $this->input->post('limit') ? $this->input->post('limit') : 10;
            $metric = $this->input->post('metric') ? $this->input->post('metric') : 'quantity';

            // Get time period filter
            $report_months = $this->input->post('report_months');
            $report_from = $this->input->post('report_from');
            $report_to = $this->input->post('report_to');

            // Store selected values for the view
            $data['report_months'] = $report_months;
            if ($report_months == 'custom') {
                $data['report_from'] = $report_from;
                $data['report_to'] = $report_to;
            }

            // Get date filter SQL
            $date_filter = '';
            if ($report_months) {
                $date_filter = $report_months ? $this->get_where_report_period($contact_type === 'vendor' ? 'order_date' : 'date'): '';
            }

            $data['report_data'] = $this->reports_model->get_items_by_contact(
                $contact_id,
                $contact_type,
                $transaction_type,
                $ranking,
                $limit,
                $metric,
                preg_replace('/^\s*AND\s+/i', '', $date_filter)
            );

            // Store the selected parameters for the view
            $data['selected_transaction_type'] = $transaction_type;
            $data['selected_ranking'] = $ranking;
            $data['selected_limit'] = $limit;
            $data['selected_metric'] = $metric;
        }

        $data['title'] = _l('contact_items_report');

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/reports/contact_items_report', $data);
        } else {
            $this->load->view('admin/reports/contact_items_report_wrapper', $data);
        }
    }
    /**
     * Sales aging report based on items sold
     * Shows aging of sales based on items sold
     *
     * @return view
     */
    public function sales_aging()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $aColumns = [
                'tblitems.description as description',
                'tblitemable.rel_id as invoice_id',
                'tblinvoices.date as invoice_date',
                'tblinvoices.duedate as due_date',
                'DATEDIFF(NOW(), tblinvoices.duedate) as days_overdue',
                'tblitemable.qty as quantity',
                'tblitemable.rate as rate',
                'tblitemable.qty * tblitemable.rate as total_amount',
                'tblinvoices.status as invoice_status',
                'tblclients.company as customer_name'
            ];

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'itemable';

            $join = [
                'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id',
                'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
                'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description'
            ];

            $where = ['AND rel_type="invoice"'];

            $custom_date_select = $this->get_where_report_period('date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('sale_agent_items')) {
                $agents  = $this->input->post('sale_agent_items');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND tblinvoices.clientid IN (' . implode(', ', $_agents) . ')');
                }
            }

            if ($this->input->post('sale_product_items')) {
                $products  = $this->input->post('sale_product_items');
                $_products = [];
                if (is_array($products)) {
                    foreach ($products as $product) {
                        if ($product != '') {
                            array_push($_products, $this->db->escape_str($product));
                        }
                    }
                }
                if (count($_products) > 0) {
                    array_push($where, 'AND tblitems.id IN (' . implode(', ', $_products) . ')');
                }
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblitemable.id',
                'tblinvoices.clientid',
                'tblinvoices.number',
                'tblinvoices.prefix'
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total_amount' => 0,
                'total_qty'    => 0,
                'aging_30'     => 0,
                'aging_60'     => 0,
                'aging_90'     => 0,
                'aging_120'    => 0,
                'aging_older'  => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                // Item description
                $row[] = $aRow['description'];

                // Invoice number
                $this->db->select('prefix, number');
                $this->db->from(db_prefix() . 'invoices');
                $this->db->where('id', $aRow['invoice_id']);
                $invoice = $this->db->get()->row();
                $invoice_number = $invoice ? $invoice->prefix . $invoice->number : '';
                $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoice_id']) . '" target="_blank">' . $invoice_number . '</a>';

                // Customer name
                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank">' . $aRow['customer_name'] . '</a>';

                // Invoice date
                $row[] = _d($aRow['invoice_date']);

                // Due date
                $row[] = _d($aRow['due_date']);

                // Days overdue
                $days_overdue = $aRow['days_overdue'];
                $row[] = $days_overdue > 0 ? $days_overdue : 0;

                // Aging category
                $aging_category = '';
                if ($days_overdue <= 0) {
                    $aging_category = _l('current');
                } elseif ($days_overdue <= 30) {
                    $aging_category = '1-30 ' . _l('days');
                    $footer_data['aging_30'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 60) {
                    $aging_category = '31-60 ' . _l('days');
                    $footer_data['aging_60'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 90) {
                    $aging_category = '61-90 ' . _l('days');
                    $footer_data['aging_90'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 120) {
                    $aging_category = '91-120 ' . _l('days');
                    $footer_data['aging_120'] += $aRow['total_amount'];
                } else {
                    $aging_category = '120+ ' . _l('days');
                    $footer_data['aging_older'] += $aRow['total_amount'];
                }
                $row[] = $aging_category;

                // Quantity
                $row[] = $aRow['quantity'];
                $footer_data['total_qty'] += $aRow['quantity'];

                // Rate
                $row[] = app_format_money($aRow['rate'], $currency->name);

                // Total amount
                $row[] = app_format_money($aRow['total_amount'], $currency->name);
                $footer_data['total_amount'] += $aRow['total_amount'];

                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = app_format_money($footer_data['total_amount'], $currency->name);
            $footer_data['aging_30'] = app_format_money($footer_data['aging_30'], $currency->name);
            $footer_data['aging_60'] = app_format_money($footer_data['aging_60'], $currency->name);
            $footer_data['aging_90'] = app_format_money($footer_data['aging_90'], $currency->name);
            $footer_data['aging_120'] = app_format_money($footer_data['aging_120'], $currency->name);
            $footer_data['aging_older'] = app_format_money($footer_data['aging_older'], $currency->name);

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }
    /**
     * Average sale aging report based on items sold
     * Shows average aging of sales based on items sold
     *
     * @return view
     */
    public function avg_sale_aging()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $aColumns = [
                'tblitems.description as description',
                'COUNT(tblitemable.rel_id) as invoice_count',
                'AVG(DATEDIFF(NOW(), tblinvoices.duedate)) as avg_days_overdue',
                'SUM(tblitemable.qty) as total_quantity',
                'SUM(tblitemable.qty * tblitemable.rate) as total_amount',
                'AVG(tblitemable.rate) as avg_rate'
            ];

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'itemable';

            $join = [
                'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id',
                'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
                'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description'
            ];

            $where = ['AND rel_type="invoice"'];

            $custom_date_select = $this->get_where_report_period('date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('sale_agent_items')) {
                $agents  = $this->input->post('sale_agent_items');
                $_agents = [];
                if (is_array($agents)) {
                    foreach ($agents as $agent) {
                        if ($agent != '') {
                            array_push($_agents, $this->db->escape_str($agent));
                        }
                    }
                }
                if (count($_agents) > 0) {
                    array_push($where, 'AND tblinvoices.clientid IN (' . implode(', ', $_agents) . ')');
                }
            }

            if ($this->input->post('sale_product_items')) {
                $products  = $this->input->post('sale_product_items');
                $_products = [];
                if (is_array($products)) {
                    foreach ($products as $product) {
                        if ($product != '') {
                            array_push($_products, $this->db->escape_str($product));
                        }
                    }
                }
                if (count($_products) > 0) {
                    array_push($where, 'AND tblitems.id IN (' . implode(', ', $_products) . ')');
                }
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblitemable.id',
                'tblinvoices.clientid'
            ], 'GROUP BY tblitemable.description');

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total_amount' => 0,
                'total_qty'    => 0,
                'avg_days'     => 0,
                'total_items'  => 0
            ];

            $total_days = 0;
            $item_count = 0;

            foreach ($rResult as $aRow) {
                $row = [];

                // Item description
                $row[] = $aRow['description'];

                // Invoice count
                $row[] = $aRow['invoice_count'];

                // Average days overdue
                $avg_days = round($aRow['avg_days_overdue']);
                $row[] = $avg_days > 0 ? $avg_days : 0;
                $total_days += $avg_days > 0 ? $avg_days : 0;
                $item_count++;

                // Aging category
                $aging_category = '';
                if ($avg_days <= 0) {
                    $aging_category = _l('current');
                } elseif ($avg_days <= 30) {
                    $aging_category = '1-30 ' . _l('days');
                } elseif ($avg_days <= 60) {
                    $aging_category = '31-60 ' . _l('days');
                } elseif ($avg_days <= 90) {
                    $aging_category = '61-90 ' . _l('days');
                } elseif ($avg_days <= 120) {
                    $aging_category = '91-120 ' . _l('days');
                } else {
                    $aging_category = '120+ ' . _l('days');
                }
                $row[] = $aging_category;

                // Total quantity
                $row[] = $aRow['total_quantity'];
                $footer_data['total_qty'] += $aRow['total_quantity'];

                // Average rate
                $row[] = app_format_money($aRow['avg_rate'], $currency->name);

                // Total amount
                $row[] = app_format_money($aRow['total_amount'], $currency->name);
                $footer_data['total_amount'] += $aRow['total_amount'];

                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = app_format_money($footer_data['total_amount'], $currency->name);
            $footer_data['avg_days'] = $item_count > 0 ? round($total_days / $item_count) : 0;
            $footer_data['total_items'] = $item_count;

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }
    }

    /**
     * Purchase aging report based on items purchased
     * Shows aging of purchases based on items purchased
     *
     * @return view
     */
    public function purchase_aging()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $aColumns = [
                'tblitems.description as description',
                'tblitemable.rel_id as purchase_id',
                'tblpur_orders.order_date as purchase_date',
                'tblpur_orders.delivery_date as delivery_date',
                'DATEDIFF(NOW(), tblpur_orders.delivery_date) as days_overdue',
                'tblitemable.qty as quantity',
                'tblitemable.rate as rate',
                'tblitemable.qty * tblitemable.rate as total_amount',
                'tblpur_orders.status as purchase_status',
                'tblpur_vendor.company as vendor_name'
            ];

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'itemable';

            $join = [
                'JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'itemable.rel_id',
                'JOIN ' . db_prefix() . 'pur_vendor ON ' . db_prefix() . 'pur_vendor.userid = ' . db_prefix() . 'pur_orders.vendor',
                'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description'
            ];

            $where = ['AND rel_type="pur_order"'];

            $custom_date_select = $this->get_where_report_period('order_date');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }

            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $currency = $this->currencies_model->get($by_currency);
                array_push($where, 'AND currency=' . $this->db->escape_str($by_currency));
            } else {
                $currency = $this->currencies_model->get_base_currency();
            }

            if ($this->input->post('vendor_items')) {
                $vendors  = $this->input->post('vendor_items');
                $_vendors = [];
                if (is_array($vendors)) {
                    foreach ($vendors as $vendor) {
                        if ($vendor != '') {
                            array_push($_vendors, $this->db->escape_str($vendor));
                        }
                    }
                }
                if (count($_vendors) > 0) {
                    array_push($where, 'AND tblpur_orders.vendor IN (' . implode(', ', $_vendors) . ')');
                }
            }

            if ($this->input->post('purchase_product_items')) {
                $products  = $this->input->post('purchase_product_items');
                $_products = [];
                if (is_array($products)) {
                    foreach ($products as $product) {
                        if ($product != '') {
                            array_push($_products, $this->db->escape_str($product));
                        }
                    }
                }
                if (count($_products) > 0) {
                    array_push($where, 'AND tblitems.id IN (' . implode(', ', $_products) . ')');
                }
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblitemable.id',
                'tblpur_orders.vendor',
                'tblpur_orders.po_number',
                'tblpur_orders.prefix'
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total_amount' => 0,
                'total_qty'    => 0,
                'aging_30'     => 0,
                'aging_60'     => 0,
                'aging_90'     => 0,
                'aging_120'    => 0,
                'aging_older'  => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                // Item description
                $row[] = $aRow['description'];

                // Purchase order number
                $this->db->select('prefix, po_number');
                $this->db->from(db_prefix() . 'pur_orders');
                $this->db->where('id', $aRow['purchase_id']);
                $purchase = $this->db->get()->row();
                $purchase_number = $purchase ? $purchase->prefix . $purchase->po_number : '';
                $row[] = '<a href="' . admin_url('purchase/purchase_order/' . $aRow['purchase_id']) . '" target="_blank">' . $purchase_number . '</a>';

                // Vendor name
                $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" target="_blank">' . $aRow['vendor_name'] . '</a>';

                // Purchase date
                $row[] = _d($aRow['purchase_date']);

                // Delivery date
                $row[] = _d($aRow['delivery_date']);

                // Days overdue
                $days_overdue = $aRow['days_overdue'];
                $row[] = $days_overdue > 0 ? $days_overdue : 0;

                // Aging category
                $aging_category = '';
                if ($days_overdue <= 0) {
                    $aging_category = _l('current');
                } elseif ($days_overdue <= 30) {
                    $aging_category = '1-30 ' . _l('days');
                    $footer_data['aging_30'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 60) {
                    $aging_category = '31-60 ' . _l('days');
                    $footer_data['aging_60'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 90) {
                    $aging_category = '61-90 ' . _l('days');
                    $footer_data['aging_90'] += $aRow['total_amount'];
                } elseif ($days_overdue <= 120) {
                    $aging_category = '91-120 ' . _l('days');
                    $footer_data['aging_120'] += $aRow['total_amount'];
                } else {
                    $aging_category = '120+ ' . _l('days');
                    $footer_data['aging_older'] += $aRow['total_amount'];
                }
                $row[] = $aging_category;

                // Quantity
                $row[] = $aRow['quantity'];
                $footer_data['total_qty'] += $aRow['quantity'];

                // Rate
                $row[] = app_format_money($aRow['rate'], $currency->name);

                // Total amount
                $row[] = app_format_money($aRow['total_amount'], $currency->name);
                $footer_data['total_amount'] += $aRow['total_amount'];

                $output['aaData'][] = $row;
            }

            $footer_data['total_amount'] = app_format_money($footer_data['total_amount'], $currency->name);
            $footer_data['aging_30'] = app_format_money($footer_data['aging_30'], $currency->name);
            $footer_data['aging_60'] = app_format_money($footer_data['aging_60'], $currency->name);
            $footer_data['aging_90'] = app_format_money($footer_data['aging_90'], $currency->name);
            $footer_data['aging_120'] = app_format_money($footer_data['aging_120'], $currency->name);
            $footer_data['aging_older'] = app_format_money($footer_data['aging_older'], $currency->name);

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();
        }

        // Load required models
        $this->load->model('currencies_model');
        $this->load->model('Invoice_items_model', 'items_model');

        // Try to load the purchase model
        $purchase_model_loaded = false;
        try {
            $this->load->model('purchase/purchase_model');
            $purchase_model_loaded = true;
        } catch (Exception $e) {
            log_activity('Failed to load purchase model in purchase_aging: ' . $e->getMessage());
        }

        if ($purchase_model_loaded) {
            $data = [];
            $data['title'] = _l('purchase_aging');
            $data['vendors'] = $this->purchase_model->get_vendor();
            $data['items'] = $this->items_model->get();
            $data['currencies'] = $this->currencies_model->get();
            $data['base_currency'] = $this->currencies_model->get_base_currency();

            $this->load->view('admin/reports/purchase_aging', $data);
        } else {
            set_alert('warning', _l('purchase_module_not_available'));
            redirect(admin_url('reports'));
        }
    }
    /**
     * Purchase & Sales Aging Report
     * Shows aging of purchases and sales based on selected product
     *
     * @return view
     */
    public function purchase_sales_aging()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');
            $this->load->model('Invoice_items_model', 'items_model');

            // Get filter parameters
            $product_id = $this->input->post('product_id');
            $report_from = $this->input->post('report_from');
            $report_to = $this->input->post('report_to');
            $report_months = $this->input->post('report_months');

            // Prepare date filters
            $custom_date_select = '';
            if ($report_months) {
                if ($report_months == 'this_month') {
                    $custom_date_select = 'AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())';
                } elseif ($report_months == 'this_year') {
                    $custom_date_select = 'AND YEAR(date) = YEAR(CURDATE())';
                } elseif ($report_months == 'last_year') {
                    $custom_date_select = 'AND YEAR(date) = YEAR(CURDATE()) - 1';
                } elseif ($report_months == '3') {
                    $custom_date_select = 'AND date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)';
                } elseif ($report_months == '6') {
                    $custom_date_select = 'AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
                } elseif ($report_months == '12') {
                    $custom_date_select = 'AND date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)';
                } elseif (is_numeric($report_months)) {
                    $custom_date_select = 'AND date >= DATE_SUB(CURDATE(), INTERVAL ' . $report_months . ' MONTH)';
                }
            }

            if ($report_from && $report_to) {
                $custom_date_select = 'AND date BETWEEN "' . $report_from . '" AND "' . $report_to . '"';
            }

            // Initialize response data
            $response = [
                'purchase_summary' => [
                    'total_amount' => 0,
                    'transaction_count' => 0,
                    'average_amount' => 0,
                    'time_intervals' => [
                        '1_week' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '2_weeks' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '1_month' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '2_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '3_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '6_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '12_months' => ['total' => 0, 'count' => 0, 'average' => 0]
                    ]
                ],
                'sales_summary' => [
                    'total_amount' => 0,
                    'transaction_count' => 0,
                    'average_amount' => 0,
                    'time_intervals' => [
                        '1_week' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '2_weeks' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '1_month' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '2_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '3_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '6_months' => ['total' => 0, 'count' => 0, 'average' => 0],
                        '12_months' => ['total' => 0, 'count' => 0, 'average' => 0]
                    ]
                ]
            ];

            // Get purchase data
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in purchase_sales_aging: ' . $e->getMessage());
            }

            if ($purchase_model_loaded) {
                // Query for purchase data
                $this->db->select('tblpur_orders.id, tblpur_orders.order_date, tblpur_order_detail.quantity, tblpur_order_detail.unit_price, tblpur_order_detail.total');
                $this->db->from(db_prefix() . 'pur_order_detail');
                $this->db->join(db_prefix() . 'pur_orders', db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_detail.pur_order');
                $this->db->where('tblpur_order_detail.item_code', $product_id);

                if ($custom_date_select != '') {
                    $this->db->where($custom_date_select);
                }

                $purchases = $this->db->get()->result_array();

                // Process purchase data
                foreach ($purchases as $purchase) {
                    $purchase_date = new DateTime($purchase['order_date']);
                    $now = new DateTime();
                    $interval = $purchase_date->diff($now);
                    $days_ago = $interval->days;

                    // Add to total
                    $response['purchase_summary']['total_amount'] += $purchase['total'];
                    $response['purchase_summary']['transaction_count']++;

                    // Add to time intervals
                    if ($days_ago <= 7) {
                        $response['purchase_summary']['time_intervals']['1_week']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['1_week']['count']++;
                    }
                    if ($days_ago <= 14) {
                        $response['purchase_summary']['time_intervals']['2_weeks']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['2_weeks']['count']++;
                    }
                    if ($days_ago <= 30) {
                        $response['purchase_summary']['time_intervals']['1_month']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['1_month']['count']++;
                    }
                    if ($days_ago <= 60) {
                        $response['purchase_summary']['time_intervals']['2_months']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['2_months']['count']++;
                    }
                    if ($days_ago <= 90) {
                        $response['purchase_summary']['time_intervals']['3_months']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['3_months']['count']++;
                    }
                    if ($days_ago <= 180) {
                        $response['purchase_summary']['time_intervals']['6_months']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['6_months']['count']++;
                    }
                    if ($days_ago <= 365) {
                        $response['purchase_summary']['time_intervals']['12_months']['total'] += $purchase['total'];
                        $response['purchase_summary']['time_intervals']['12_months']['count']++;
                    }
                }

                // Calculate averages
                if ($response['purchase_summary']['transaction_count'] > 0) {
                    $response['purchase_summary']['average_amount'] = $response['purchase_summary']['total_amount'] / $response['purchase_summary']['transaction_count'];
                }

                foreach ($response['purchase_summary']['time_intervals'] as $interval => $data) {
                    if ($data['count'] > 0) {
                        $response['purchase_summary']['time_intervals'][$interval]['average'] = $data['total'] / $data['count'];
                    }
                }
            }

            // Get sales data
            $this->db->select('tblinvoices.id, tblinvoices.date, tblitems_in.qty, tblitems_in.rate, (tblitems_in.qty * tblitems_in.rate) as total');
            $this->db->from(db_prefix() . 'items_in');
            $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'items_in.rel_id');
            $this->db->where('tblitems_in.rel_type', 'invoice');
            $this->db->where('tblitems_in.itemid', $product_id);

            if ($custom_date_select != '') {
                $this->db->where($custom_date_select);
            }

            $sales = $this->db->get()->result_array();

            // Process sales data
            foreach ($sales as $sale) {
                $sale_date = new DateTime($sale['date']);
                $now = new DateTime();
                $interval = $sale_date->diff($now);
                $days_ago = $interval->days;

                // Add to total
                $response['sales_summary']['total_amount'] += $sale['total'];
                $response['sales_summary']['transaction_count']++;

                // Add to time intervals
                if ($days_ago <= 7) {
                    $response['sales_summary']['time_intervals']['1_week']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['1_week']['count']++;
                }
                if ($days_ago <= 14) {
                    $response['sales_summary']['time_intervals']['2_weeks']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['2_weeks']['count']++;
                }
                if ($days_ago <= 30) {
                    $response['sales_summary']['time_intervals']['1_month']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['1_month']['count']++;
                }
                if ($days_ago <= 60) {
                    $response['sales_summary']['time_intervals']['2_months']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['2_months']['count']++;
                }
                if ($days_ago <= 90) {
                    $response['sales_summary']['time_intervals']['3_months']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['3_months']['count']++;
                }
                if ($days_ago <= 180) {
                    $response['sales_summary']['time_intervals']['6_months']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['6_months']['count']++;
                }
                if ($days_ago <= 365) {
                    $response['sales_summary']['time_intervals']['12_months']['total'] += $sale['total'];
                    $response['sales_summary']['time_intervals']['12_months']['count']++;
                }
            }

            // Calculate averages
            if ($response['sales_summary']['transaction_count'] > 0) {
                $response['sales_summary']['average_amount'] = $response['sales_summary']['total_amount'] / $response['sales_summary']['transaction_count'];
            }

            foreach ($response['sales_summary']['time_intervals'] as $interval => $data) {
                if ($data['count'] > 0) {
                    $response['sales_summary']['time_intervals'][$interval]['average'] = $data['total'] / $data['count'];
                }
            }

            // Format currency values
            $currency = $this->currencies_model->get_base_currency();

            $response['purchase_summary']['total_amount'] = app_format_money($response['purchase_summary']['total_amount'], $currency->name);
            $response['purchase_summary']['average_amount'] = app_format_money($response['purchase_summary']['average_amount'], $currency->name);

            $response['sales_summary']['total_amount'] = app_format_money($response['sales_summary']['total_amount'], $currency->name);
            $response['sales_summary']['average_amount'] = app_format_money($response['sales_summary']['average_amount'], $currency->name);

            foreach ($response['purchase_summary']['time_intervals'] as $interval => $data) {
                $response['purchase_summary']['time_intervals'][$interval]['total'] = app_format_money($data['total'], $currency->name);
                $response['purchase_summary']['time_intervals'][$interval]['average'] = app_format_money($data['average'], $currency->name);
            }

            foreach ($response['sales_summary']['time_intervals'] as $interval => $data) {
                $response['sales_summary']['time_intervals'][$interval]['total'] = app_format_money($data['total'], $currency->name);
                $response['sales_summary']['time_intervals'][$interval]['average'] = app_format_money($data['average'], $currency->name);
            }

            echo json_encode($response);
            die();
        }

        // Load required models
        $this->load->model('currencies_model');
        $this->load->model('Invoice_items_model', 'items_model');

        // Try to load the purchase model
        $purchase_model_loaded = false;
        try {
            $this->load->model('purchase/purchase_model');
            $purchase_model_loaded = true;
        } catch (Exception $e) {
            log_activity('Failed to load purchase model in purchase_sales_aging: ' . $e->getMessage());
        }

        $data = [];
        $data['title'] = _l('purchase_sales_aging');
        $data['items'] = $this->items_model->get();
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['purchase_model_loaded'] = $purchase_model_loaded;

        $this->load->view('admin/reports/purchase_sales_aging', $data);
    }
}
