<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  Leads conversions monthly report
     * @param   mixed $month  which month / chart
     * @return  array          chart data
     */
    public function leads_monthly_report($month)
    {
        $result      = $this->db->query('select last_status_change from ' . db_prefix() . 'leads where MONTH(last_status_change) = ' . $month . ' AND status = 1 and lost = 0')->result_array();
        $month_dates = [];
        $data        = [];
        for ($d = 1; $d <= 31; $d++) {
            $time = mktime(12, 0, 0, $month, $d, date('Y'));
            if (date('m', $time) == $month) {
                $month_dates[] = _d(date('Y-m-d', $time));
                $data[]        = 0;
            }
        }
        $chart = [
            'labels'   => $month_dates,
            'datasets' => [
                [
                    'label'           => _l('leads'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $data,
                ],
            ],
        ];
        foreach ($result as $lead) {
            $i = 0;
            foreach ($chart['labels'] as $date) {
                if (_d(date('Y-m-d', strtotime($lead['last_status_change']))) == $date) {
                    $chart['datasets'][0]['data'][$i]++;
                }
                $i++;
            }
        }

        return $chart;
    }

    public function get_stats_chart_data($label, $where, $dataset_options, $year)
    {
        $chart = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'       => $label,
                    'borderWidth' => 1,
                    'tension'     => false,
                    'data'        => [],
                ],
            ],
        ];

        foreach ($dataset_options as $key => $val) {
            $chart['datasets'][0][$key] = $val;
        }
        $this->load->model('expenses_model');
        $categories = $this->expenses_model->get_category();
        foreach ($categories as $category) {
            $_where['category']   = $category['id'];
            $_where['YEAR(date)'] = $year;
            if (count($where) > 0) {
                foreach ($where as $key => $val) {
                    $_where[$key] = $this->db->escape_str($val);
                }
            }
            array_push($chart['labels'], $category['name']);
            array_push($chart['datasets'][0]['data'], total_rows(db_prefix() . 'expenses', $_where));
        }

        return $chart;
    }

    public function get_expenses_vs_income_report($year = '')
    {
        $this->load->model('expenses_model');

        $months_labels  = [];
        $total_expenses = [];
        $total_income   = [];
        $i              = 0;
        if (!is_numeric($year)) {
            $year = date('Y');
        }
        for ($m = 1; $m <= 12; $m++) {
            array_push($months_labels, _l(date('F', mktime(0, 0, 0, $m, 1))));
            $this->db->select('id')->from(db_prefix() . 'expenses')->where('MONTH(date)', $m)->where('YEAR(date)', $year);
            $expenses = $this->db->get()->result_array();
            if (!isset($total_expenses[$i])) {
                $total_expenses[$i] = [];
            }
            if (count($expenses) > 0) {
                foreach ($expenses as $expense) {
                    $expense = $this->expenses_model->get($expense['id']);
                    $total   = $expense->amount;
                    // Check if tax is applied
                    if ($expense->tax != 0) {
                        $total += ($total / 100 * $expense->taxrate);
                    }
                    if ($expense->tax2 != 0) {
                        $total += ($expense->amount / 100 * $expense->taxrate2);
                    }
                    $total_expenses[$i][] = $total;
                }
            } else {
                $total_expenses[$i][] = 0;
            }
            $total_expenses[$i] = array_sum($total_expenses[$i]);
            // Calculate the income
            $this->db->select('amount');
            $this->db->from(db_prefix() . 'invoicepaymentrecords');
            $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
            $this->db->where('MONTH(' . db_prefix() . 'invoicepaymentrecords.date)', $m);
            $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
            $payments = $this->db->get()->result_array();
            if (!isset($total_income[$m])) {
                $total_income[$i] = [];
            }
            if (count($payments) > 0) {
                foreach ($payments as $payment) {
                    $total_income[$i][] = $payment['amount'];
                }
            } else {
                $total_income[$i][] = 0;
            }
            $total_income[$i] = array_sum($total_income[$i]);
            $i++;
        }
        $chart = [
            'labels'   => $months_labels,
            'datasets' => [
                [
                    'label'           => _l('report_sales_type_income'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_income,
                ],
                [
                    'label'           => _l('expenses'),
                    'backgroundColor' => 'rgba(252,45,66,0.4)',
                    'borderColor'     => '#fc2d42',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_expenses,
                ],
            ],
        ];

        return $chart;
    }

    /**
     * Chart leads weeekly report
     * @return array  chart data
     */
    public function leads_this_week_report()
    {
        $this->db->where('CAST(last_status_change as DATE) >= "' . date('Y-m-d', strtotime('monday this week')) . '" AND CAST(last_status_change as DATE) <= "' . date('Y-m-d', strtotime('sunday this week')) . '" AND status = 1 and lost = 0');
        $weekly = $this->db->get(db_prefix() . 'leads')->result_array();
        $colors = get_system_favourite_colors();
        $chart  = [
            'labels' => [
                _l('wd_monday'),
                _l('wd_tuesday'),
                _l('wd_wednesday'),
                _l('wd_thursday'),
                _l('wd_friday'),
                _l('wd_saturday'),
                _l('wd_sunday'),
            ],
            'datasets' => [
                [
                    'data' => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                    'backgroundColor' => [
                        $colors[0],
                        $colors[1],
                        $colors[2],
                        $colors[3],
                        $colors[4],
                        $colors[5],
                        $colors[6],
                    ],
                    'hoverBackgroundColor' => [
                        adjust_color_brightness($colors[0], -20),
                        adjust_color_brightness($colors[1], -20),
                        adjust_color_brightness($colors[2], -20),
                        adjust_color_brightness($colors[3], -20),
                        adjust_color_brightness($colors[4], -20),
                        adjust_color_brightness($colors[5], -20),
                        adjust_color_brightness($colors[6], -20),
                    ],
                ],
            ],
        ];
        foreach ($weekly as $weekly) {
            $lead_status_day = _l(mb_strtolower('wd_' . date('l', strtotime($weekly['last_status_change']))));
            $i               = 0;
            foreach ($chart['labels'] as $dat) {
                if ($lead_status_day == $dat) {
                    $chart['datasets'][0]['data'][$i]++;
                }
                $i++;
            }
        }

        return $chart;
    }

    public function leads_staff_report()
    {
        $this->load->model('staff_model');
        $staff = $this->staff_model->get();
        if ($this->input->post()) {
            $from_date = to_sql_date($this->input->post('staff_report_from_date'));
            $to_date   = to_sql_date($this->input->post('staff_report_to_date'));
        }
        $chart = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => _l('leads_staff_report_created'),
                    'backgroundColor' => 'rgba(3,169,244,0.2)',
                    'borderColor'     => '#03a9f4',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
                [
                    'label'           => _l('leads_staff_report_lost'),
                    'backgroundColor' => 'rgba(252,45,66,0.4)',
                    'borderColor'     => '#fc2d42',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
                [
                    'label'           => _l('leads_staff_report_converted'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
            ],
        ];

        foreach ($staff as $member) {
            array_push($chart['labels'], $member['firstname'] . ' ' . $member['lastname']);

            if (!isset($to_date) && !isset($from_date)) {
                $this->db->where('CASE WHEN assigned=0 THEN addedfrom=' . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END
                    AND status=1', '', false);
                $total_rows_converted = $this->db->count_all_results(db_prefix() . 'leads');

                $total_rows_created = total_rows(db_prefix() . 'leads', [
                    'addedfrom' => $member['staffid'],
                ]);

                $this->db->where('CASE WHEN assigned=0 THEN addedfrom=' . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END
                    AND lost=1', '', false);
                $total_rows_lost = $this->db->count_all_results(db_prefix() . 'leads');
            } else {
                $sql                  = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(last_status_change) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND status = 1 AND CASE WHEN assigned=0 THEN addedfrom=" . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END';
                $total_rows_converted = $this->db->query($sql)->row()->total;

                $sql                = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(dateadded) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND addedfrom=" . $member['staffid'] . '';
                $total_rows_created = $this->db->query($sql)->row()->total;

                $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(last_status_change) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND lost = 1 AND CASE WHEN assigned=0 THEN addedfrom=" . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END';

                $total_rows_lost = $this->db->query($sql)->row()->total;
            }

            array_push($chart['datasets'][0]['data'], $total_rows_created);
            array_push($chart['datasets'][1]['data'], $total_rows_lost);
            array_push($chart['datasets'][2]['data'], $total_rows_converted);
        }

        return $chart;
    }

    /**
     * Lead conversion by sources report / chart
     * @return arrray chart data
     */
    public function leads_sources_report()
    {
        $this->load->model('leads_model');
        $sources = $this->leads_model->get_source();
        $chart   = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => _l('report_leads_sources_conversions'),
                    'backgroundColor' => 'rgba(124, 179, 66, 0.5)',
                    'borderColor'     => '#7cb342',
                    'data'            => [],
                ],
            ],
        ];
        foreach ($sources as $source) {
            array_push($chart['labels'], $source['name']);
            array_push($chart['datasets'][0]['data'], total_rows(db_prefix() . 'leads', [
                'source' => $source['id'],
                'status' => 1,
                'lost'   => 0,
            ]));
        }

        return $chart;
    }

    public function report_by_customer_groups()
    {
        $months_report = $this->input->post('months_report');
        $groups        = $this->clients_model->get_groups();
        if ($months_report != '') {
            $custom_date_select = '';
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

                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
                    date('Y-m-d', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = db_prefix() . 'invoicepaymentrecords.date ="' . $from_date . '"';
                } else {
                    $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
            $this->db->where($custom_date_select);
        }
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date,' . db_prefix() . 'invoices.clientid,(SELECT GROUP_CONCAT(name) FROM ' . db_prefix() . 'customers_groups LEFT JOIN ' . db_prefix() . 'customer_groups ON ' . db_prefix() . 'customer_groups.groupid = ' . db_prefix() . 'customers_groups.id WHERE customer_id = ' . db_prefix() . 'invoices.clientid) as customerGroups');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where(db_prefix() . 'invoices.clientid IN (select customer_id FROM ' . db_prefix() . 'customer_groups)');
        $this->db->where(db_prefix() . 'invoices.status !=', 5);
        $by_currency = $this->input->post('report_currency');
        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }
        $payments       = $this->db->get()->result_array();
        $data           = [];
        $data['temp']   = [];
        $data['total']  = [];
        $data['labels'] = [];
        foreach ($groups as $group) {
            if (!isset($data['groups'][$group['name']])) {
                $data['groups'][$group['name']] = $group['name'];
            }
        }

        // If any groups found
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                foreach ($payments as $payment) {
                    $p_groups = explode(',', $payment['customerGroups']);
                    foreach ($p_groups as $p_group) {
                        if ($p_group == $group) {
                            $data['temp'][$group][] = $payment['amount'];
                        }
                    }
                }
                array_push($data['labels'], $group);
                if (isset($data['temp'][$group])) {
                    $data['total'][] = array_sum($data['temp'][$group]);
                } else {
                    $data['total'][] = 0;
                }
            }
        }

        $chart = [
            'labels'   => $data['labels'],
            'datasets' => [
                [
                    'label'           => _l('total_amount'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.2)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $data['total'],
                ],
            ],
        ];

        return $chart;
    }

    public function report_by_payment_modes()
    {
        $this->load->model('payment_modes_model');
        $modes  = $this->payment_modes_model->get('', [], true, true);
        $year   = $this->input->post('year');
        $colors = get_system_favourite_colors();
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $by_currency = $this->input->post('report_currency');
        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }
        $all_payments = $this->db->get()->result_array();
        $chart        = [
            'labels'   => [],
            'datasets' => [],
        ];
        $data           = [];
        $data['months'] = [];
        foreach ($all_payments as $payment) {
            $month   = date('m', strtotime($payment['date']));
            $dateObj = DateTime::createFromFormat('!m', $month);
            $month   = $dateObj->format('F');
            if (!isset($data['months'][$month])) {
                $data['months'][$month] = $month;
            }
        }
        usort($data['months'], function ($a, $b) {
            $month1 = date_parse($a);
            $month2 = date_parse($b);

            return $month1['month'] - $month2['month'];
        });

        foreach ($data['months'] as $month) {
            array_push($chart['labels'], _l($month) . ' - ' . $year);
        }
        $i = 0;
        foreach ($modes as $mode) {
            if (total_rows(db_prefix() . 'invoicepaymentrecords', [
                    'paymentmode' => $mode['id'],
                ]) == 0) {
                continue;
            }
            $color = '#4B5158';
            if (isset($colors[$i])) {
                $color = $colors[$i];
            }
            $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
            $this->db->from(db_prefix() . 'invoicepaymentrecords');
            $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
            $this->db->where(db_prefix() . 'invoicepaymentrecords.paymentmode', $mode['id']);
            $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $this->db->where('currency', $by_currency);
            }
            $payments = $this->db->get()->result_array();

            $datasets_data          = [];
            $datasets_data['total'] = [];
            foreach ($data['months'] as $month) {
                $total_payments = [];
                if (!isset($datasets_data['temp'][$month])) {
                    $datasets_data['temp'][$month] = [];
                }
                foreach ($payments as $payment) {
                    $_month  = date('m', strtotime($payment['date']));
                    $dateObj = DateTime::createFromFormat('!m', $_month);
                    $_month  = $dateObj->format('F');
                    if ($month == $_month) {
                        $total_payments[] = $payment['amount'];
                    }
                }
                $datasets_data['total'][] = array_sum($total_payments);
            }
            $chart['datasets'][] = [
                'label'           => $mode['name'],
                'backgroundColor' => $color,
                'borderColor'     => adjust_color_brightness($color, -20),
                'tension'         => false,
                'borderWidth'     => 1,
                'data'            => $datasets_data['total'],
            ];
            $i++;
        }

        return $chart;
    }

    /**
     * Total income report / chart
     * @return array chart data
     */
    public function total_income_report()
    {
        $year = $this->input->post('year');
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $by_currency = $this->input->post('report_currency');
        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }
        $payments       = $this->db->get()->result_array();
        $data           = [];
        $data['months'] = [];
        $data['temp']   = [];
        $data['total']  = [];
        $data['labels'] = [];
        foreach ($payments as $payment) {
            $month   = date('m', strtotime($payment['date']));
            $dateObj = DateTime::createFromFormat('!m', $month);
            $month   = $dateObj->format('F');
            if (!isset($data['months'][$month])) {
                $data['months'][$month] = $month;
            }
        }
        usort($data['months'], function ($a, $b) {
            $month1 = date_parse($a);
            $month2 = date_parse($b);

            return $month1['month'] - $month2['month'];
        });
        foreach ($data['months'] as $month) {
            foreach ($payments as $payment) {
                $_month  = date('m', strtotime($payment['date']));
                $dateObj = DateTime::createFromFormat('!m', $_month);
                $_month  = $dateObj->format('F');
                if ($month == $_month) {
                    $data['temp'][$month][] = $payment['amount'];
                }
            }
            array_push($data['labels'], _l($month) . ' - ' . $year);
            $data['total'][] = array_sum($data['temp'][$month]);
        }
        $chart = [
            'labels'   => $data['labels'],
            'datasets' => [
                [
                    'label'           => _l('report_sales_type_income'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'tension'         => false,
                    'borderWidth'     => 1,
                    'data'            => $data['total'],
                ],
            ],
        ];

        return $chart;
    }

    public function get_distinct_payments_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'invoicepaymentrecords')->result_array();
    }

    public function get_distinct_customer_invoices_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'invoices WHERE clientid=' . get_client_user_id())->result_array();
    }

    /**
     * Get top customers/vendors report data
     * @param  integer $limit           number of results to return
     * @param  string  $transaction_type sales, purchases, or both
     * @param  string  $metric          quantity or amount
     * @param  string  $date_filter     SQL date filter string
     * @return array
     */
    public function get_top_customers_vendors($limit = 10, $transaction_type = 'both', $metric = 'amount', $date_filter = '')
    {
        $result = [];

        // Get sales data from invoices if transaction_type is 'sales' or 'both'
        if ($transaction_type == 'sales' || $transaction_type == 'both') {
            $this->db->select('clientid, SUM(total) as total_amount, COUNT(id) as total_quantity');
            $this->db->from(db_prefix() . 'invoices');
            $this->db->where('status !=', 5); // Exclude cancelled invoices

            // Apply date filter if provided
            if (!empty($date_filter)) {
                $this->db->where($date_filter, null, false);
            }

            $this->db->group_by('clientid');
            $this->db->order_by($metric == 'amount' ? 'total_amount' : 'total_quantity', 'DESC');

            $sales_data = $this->db->get()->result_array();

            // Add type and format data
            foreach ($sales_data as &$row) {
                $row['type'] = 'customer';
                $row['name'] = get_client($row['clientid'])->company;
                $row['contact_id'] = $row['clientid'];
            }

            $result = $sales_data;
        }

        // Get purchase data from purchase orders if transaction_type is 'purchases' or 'both'
        if ($transaction_type == 'purchases' || $transaction_type == 'both') {
            // Check if purchase module is installed
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in get_top_customers_vendors: ' . $e->getMessage());
            }

            if ($purchase_model_loaded) {
                $this->db->select('vendor, SUM(total) as total_amount, COUNT(id) as total_quantity');
                $this->db->from(db_prefix() . 'pur_orders');
                $this->db->where('approve_status', 2); // Only approved purchase orders

                // Apply date filter if provided
                if (!empty($date_filter)) {

                    $this->db->where(str_replace('date', 'order_date', $date_filter), null, false);
                }

                $this->db->group_by('vendor');
                $this->db->order_by($metric == 'amount' ? 'total_amount' : 'total_quantity', 'DESC');

                $purchase_data = $this->db->get()->result_array();

                // Add type and format data
                foreach ($purchase_data as &$row) {
                    $row['type'] = 'vendor';
                    // Get vendor name from purchase model
                    $vendor = $this->purchase_model->get_vendor($row['vendor']);
                    $row['name'] = $vendor ? $vendor->company : 'Unknown Vendor';
                    $row['contact_id'] = $row['vendor'];
                }

                // Combine data if transaction_type is 'both'
                if ($transaction_type == 'both') {
                    $result = array_merge($result, $purchase_data);
                } else {
                    $result = $purchase_data;
                }
            }
        }

        // Sort combined results if transaction_type is 'both'
        if ($transaction_type == 'both') {
            // Sort by the selected metric
            usort($result, function($a, $b) use ($metric) {
                if ($metric == 'amount') {
                    return $b['total_amount'] - $a['total_amount'];
                } else {
                    return $b['total_quantity'] - $a['total_quantity'];
                }
            });
        }

        // Limit results
        return array_slice($result, 0, $limit);
    }

    /**
     * Get buyers by item report data
     * @param  integer $item_id         item id to filter by
     * @param  string  $transaction_type sales, purchases, or both
     * @param  string  $ranking         top or least
     * @param  integer $limit           number of results to return
     * @param  string  $metric          quantity or amount
     * @param  string  $date_filter     SQL date filter string
     * @return array
     */
    public function get_buyers_by_item($item_id, $transaction_type = 'both', $ranking = 'top', $limit = 10, $metric = 'quantity', $date_filter = '')
    {
        $result = [];
        $item = $this->db->get_where(db_prefix() . 'items', ['id' => $item_id])->row();

        if (!$item) {
            return [];
        }

        // Get sales data from invoices if transaction_type is 'sales' or 'both'
        if ($transaction_type == 'sales' || $transaction_type == 'both') {
            $this->db->select('i.clientid, SUM(it.qty) as total_quantity, SUM(it.qty * it.rate) as total_amount');
            $this->db->from(db_prefix() . 'itemable as it');
            $this->db->join(db_prefix() . 'invoices as i', 'i.id = it.rel_id');
            $this->db->where('it.rel_type', 'invoice');
            $this->db->where('i.status !=', 5); // Exclude cancelled invoices
            $this->db->where('it.description', $item->description); // Filter by item description

            // Apply date filter if provided
            if (!empty($date_filter)) {
                $this->db->where($date_filter, null, false);
            }

            $this->db->group_by('i.clientid');

            $sales_data = $this->db->get()->result_array();

            // Add type and format data
            foreach ($sales_data as &$row) {
                $row['type'] = 'customer';
                $client = get_client($row['clientid']);
                $row['name'] = $client ? $client->company : 'Unknown Customer';
                $row['contact_id'] = $row['clientid'];
            }

            $result = $sales_data;
        }

        // Get purchase data from purchase orders if transaction_type is 'purchases' or 'both'
        if ($transaction_type == 'purchases' || $transaction_type == 'both') {
            // Check if purchase module is installed
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model in get_buyers_by_item: ' . $e->getMessage());
            }

            if ($purchase_model_loaded) {
                $this->db->select('po.vendor, SUM(it.qty) as total_quantity, SUM(it.qty * it.rate) as total_amount');
                $this->db->from(db_prefix() . 'itemable as it');
                $this->db->join(db_prefix() . 'pur_orders as po', 'po.id = it.rel_id');
                $this->db->where('it.rel_type', 'pur_order');
                $this->db->where('po.approve_status', 2); // Only approved purchase orders
                $this->db->where('it.description', $item->description); // Filter by item description

                // Apply date filter if provided
                if (!empty($date_filter)) {
                    $this->db->where($date_filter, null, false);
                }

                $this->db->group_by('po.vendor');

                $purchase_data = $this->db->get()->result_array();

                // Add type and format data
                foreach ($purchase_data as &$row) {
                    $row['type'] = 'vendor';
                    // Get vendor name from purchase model
                    $vendor = $this->purchase_model->get_vendor($row['vendor']);
                    $row['name'] = $vendor ? $vendor->company : 'Unknown Vendor';
                    $row['contact_id'] = $row['vendor'];
                }

                // Combine data if transaction_type is 'both'
                if ($transaction_type == 'both') {
                    $result = array_merge($result, $purchase_data);
                } else {
                    $result = $purchase_data;
                }
            }
        }

        // Sort combined results
        if (count($result) > 0) {
            // Sort by the selected metric and ranking
            usort($result, function($a, $b) use ($metric, $ranking) {
                if ($metric == 'amount') {
                    return $ranking == 'top'
                        ? $b['total_amount'] - $a['total_amount']
                        : $a['total_amount'] - $b['total_amount'];
                } else {
                    return $ranking == 'top'
                        ? $b['total_quantity'] - $a['total_quantity']
                        : $a['total_quantity'] - $b['total_quantity'];
                }
            });
        }

        // Limit results
        return array_slice($result, 0, $limit);
    }
    /**
     * Get items bought by a specific contact (customer or vendor)
     * @param  integer $contact_id     contact id to filter by
     * @param  string  $contact_type   customer or vendor
     * @param  string  $transaction_type sales, purchases, or both
     * @param  string  $ranking         most or least
     * @param  integer $limit           number of results to return
     * @param  string  $metric          quantity or amount
     * @param  string  $date_filter     SQL date filter string
     * @return array
     */
    public function get_items_by_contact($contact_id, $contact_type = 'customer', $transaction_type = 'both', $ranking = 'most', $limit = 10, $metric = 'quantity', $date_filter = '')
    {
        $result = [];

        // Get sales data from invoices if transaction_type is 'sales' or 'both' and contact is a customer
        if (($transaction_type == 'sales' || $transaction_type == 'both') && $contact_type == 'customer') {
            // Optimize query by joining with items table directly and applying sorting and limit in SQL
            $this->db->select('it.description, it.long_description, SUM(it.qty) as total_quantity, SUM(it.qty * it.rate) as total_amount, items.id as item_id, items.description as code, items.group_id, groups.name as group_name');
            $this->db->from(db_prefix() . 'itemable as it');
            $this->db->join(db_prefix() . 'invoices as i', 'i.id = it.rel_id');
            $this->db->join(db_prefix() . 'items as items', 'items.description = it.description', 'left'); // Left join to include items without matching records
            $this->db->join(db_prefix() . 'items_groups as groups', 'groups.id = items.group_id', 'left'); // Left join to include group information
            $this->db->where('it.rel_type', 'invoice');
            $this->db->where('i.status !=', 5); // Exclude cancelled invoices
            $this->db->where('i.clientid', $contact_id);

            // Apply date filter if provided
            if (!empty($date_filter)) {
                $this->db->where($date_filter, null, false);
            }

            $this->db->group_by('it.description');

            // Apply sorting in SQL query
            if ($metric == 'amount') {
                $this->db->order_by('total_amount', ($ranking == 'most' ? 'DESC' : 'ASC'));
            } else {
                $this->db->order_by('total_quantity', ($ranking == 'most' ? 'DESC' : 'ASC'));
            }

            // Apply limit in SQL query
            $this->db->limit($limit);

            $sales_data = $this->db->get()->result_array();

            // Add type to each row
            foreach ($sales_data as &$row) {
                $row['type'] = 'sales';
                // If code is NULL (no matching item found), use description as fallback
                if (is_null($row['code'])) {
                    $row['code'] = $row['description'];
                }
            }

            $result = $sales_data;
        }

        // Get purchase data from purchase orders if transaction_type is 'purchases' or 'both' and contact is a vendor
        if (($transaction_type == 'purchases' || $transaction_type == 'both') && $contact_type == 'vendor') {
            // Check if purchase module is installed
            $purchase_model_loaded = false;
            try {
                $this->load->model('purchase/purchase_model');
                $purchase_model_loaded = true;
            } catch (Exception $e) {
                log_activity('Failed to load purchase model: ' . $e->getMessage());
            }

            if ($purchase_model_loaded) {
                // Optimize query by joining with items table directly and applying sorting and limit in SQL
                $this->db->select('it.description, items.long_description, SUM(it.quantity) as total_quantity, SUM(it.quantity * it.unit_price) as total_amount, items.id as item_id, items.description as code, items.group_id, groups.name as group_name');
                $this->db->from(db_prefix() . 'pur_order_detail as it');
                $this->db->join(db_prefix() . 'pur_orders as po', 'po.id = it.pur_order');
                $this->db->join(db_prefix() . 'items as items', 'items.id = it.item_code', 'left'); // Left join to include items without matching records
                $this->db->join(db_prefix() . 'items_groups as groups', 'groups.id = items.group_id', 'left'); // Left join to include group information
                $this->db->where('po.approve_status', 2); // Only approved purchase orders
                $this->db->where('po.vendor', $contact_id);

                // Apply date filter if provided
                if (!empty($date_filter)) {
                    $this->db->where($date_filter, null, false);
                }

                $this->db->group_by('it.description');

                // Apply sorting in SQL query
                if ($metric == 'amount') {
                    $this->db->order_by('total_amount', ($ranking == 'most' ? 'DESC' : 'ASC'));
                } else {
                    $this->db->order_by('total_quantity', ($ranking == 'most' ? 'DESC' : 'ASC'));
                }

                // Apply limit in SQL query
                $this->db->limit($limit);

                $purchase_data = $this->db->get()->result_array();

                // Add type to each row
                foreach ($purchase_data as &$row) {
                    $row['type'] = 'purchases';
                    // If code is NULL (no matching item found), use description as fallback
                    if (is_null($row['code'])) {
                        $row['code'] = $row['description'];
                    }
                }

                // Combine data if transaction_type is 'both'
                if ($transaction_type == 'both') {
                    // If we need both types, we need to merge, sort and limit again
                    $result = array_merge($result, $purchase_data);

                    // Sort combined results
                    if (count($result) > 0) {
                        // Sort by the selected metric and ranking
                        usort($result, function($a, $b) use ($metric, $ranking) {
                            if ($metric == 'amount') {
                                return $ranking == 'most'
                                    ? $b['total_amount'] - $a['total_amount']
                                    : $a['total_amount'] - $b['total_amount'];
                            } else {
                                return $ranking == 'most'
                                    ? $b['total_quantity'] - $a['total_quantity']
                                    : $a['total_quantity'] - $b['total_quantity'];
                            }
                        });
                    }

                    // Limit combined results
                    $result = array_slice($result, 0, $limit);
                } else {
                    $result = $purchase_data;
                }
            }
        }

        return $result;
    }
}
