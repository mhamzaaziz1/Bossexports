<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Advance_analytics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_invoice_status_stats()
    {
        $this->load->model('invoices_model');
        $statuses = $this->invoices_model->get_statuses();
        $colors = get_system_favourite_colors();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];

        // Optimized query
        $this->db->select('status, count(id) as total');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->group_by('status');
        $results = $this->db->get()->result_array();
        
        // Key results by status for easy lookup
        $totals_by_status = [];
        foreach($results as $row){
            $totals_by_status[$row['status']] = $row['total'];
        }

        foreach ($statuses as $status) {
            $total = isset($totals_by_status[$status]) ? $totals_by_status[$status] : 0;
            if ($total > 0) {
                $labels[] = format_invoice_status($status, '', false);
                $data[] = $total;
                $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#c53da9';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_proposal_status_stats()
    {
        $this->load->model('proposals_model');
        $statuses = $this->proposals_model->get_statuses();
        $colors = get_system_favourite_colors();

        $labels = [];
        $data = [];
        $backgroundColor = [];

        // Optimized Query
        $this->db->select('status, count(id) as total');
        $this->db->from(db_prefix() . 'proposals');
        $this->db->group_by('status');
        $results = $this->db->get()->result_array();

        $totals_by_status = [];
        foreach($results as $row){
             $totals_by_status[$row['status']] = $row['total'];
        }

        foreach ($statuses as $status) {
            $total = isset($totals_by_status[$status]) ? $totals_by_status[$status] : 0;
            if($total > 0){
                $labels[] = format_proposal_status($status, '', false);
                $data[] = $total;
                 $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#84c529';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }
    
    public function get_estimate_status_stats()
    {
        $this->load->model('estimates_model');
        $statuses = $this->estimates_model->get_statuses();
        $colors = get_system_favourite_colors();

        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        // Optimized Query
        $this->db->select('status, count(id) as total');
        $this->db->from(db_prefix().'estimates');
        $this->db->group_by('status');
        $results = $this->db->get()->result_array();
        
        $totals_by_status = [];
        foreach($results as $row){
             $totals_by_status[$row['status']] = $row['total'];
        }

        foreach($statuses as $status){
            $total = isset($totals_by_status[$status]) ? $totals_by_status[$status] : 0;
            if($total > 0){
                $labels[] = format_estimate_status($status, '', false);
                $data[] = $total;
                $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#03a9f4';
            }
        }
        
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_monthly_sales_chart($year)
    {
        $this->load->model('reports_model');
        return $this->reports_model->total_income_report();
    }

    public function get_finance_overview()
    {
        $this->load->model('reports_model');
        return $this->reports_model->get_expenses_vs_income_report();
    }

    public function get_payment_mode_stats($year)
    {
         $this->load->model('reports_model');
         $this->load->model('payment_modes_model');
         $modes = $this->payment_modes_model->get('', [], true);
         
         $labels = [];
         $data = [];
         $backgroundColor = [];
         $colors = get_system_favourite_colors();

         // Optimized Query
         $this->db->select('paymentmode, SUM(amount) as total');
         $this->db->from(db_prefix() . 'invoicepaymentrecords');
         $this->db->where('YEAR(date)', $year);
         $this->db->group_by('paymentmode');
         $results = $this->db->get()->result_array();
         
         $totals_by_mode = [];
         foreach($results as $row){
             $totals_by_mode[$row['paymentmode']] = $row['total'];
         }

         $i = 0;
         foreach($modes as $mode) {
             $total = isset($totals_by_mode[$mode['id']]) ? $totals_by_mode[$mode['id']] : 0;
             if($total > 0){
                 $labels[] = $mode['name'];
                 $data[] = $total;
                 $color_index = $i % count($colors);
                 $backgroundColor[] = isset($colors[$color_index]) ? $colors[$color_index] : '#333';
             }
             $i++;
         }
         
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_lead_source_stats()
    {
        $this->load->model('leads_model');
        $sources = $this->leads_model->get_source();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        // Optimized Query
        $this->db->select('source, count(id) as total');
        $this->db->from(db_prefix().'leads');
        $this->db->group_by('source');
        $results = $this->db->get()->result_array();

        $totals_by_source = [];
        foreach($results as $row){
             $totals_by_source[$row['source']] = $row['total'];
        }
        
        foreach($sources as $source){
            $total = isset($totals_by_source[$source['id']]) ? $totals_by_source[$source['id']] : 0;
            if($total > 0){
                $labels[] = $source['name'];
                $data[] = $total;
                $backgroundColor[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT); 
            }
        }
        
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }
    
    public function get_customer_group_stats()
    {
        $this->load->model('clients_model');
        $groups = $this->clients_model->get_groups();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        // Optimized Query
        $this->db->select('groupid, count(id) as total');
        $this->db->from(db_prefix().'customer_groups');
        $this->db->group_by('groupid');
        $results = $this->db->get()->result_array();

        $totals_by_group = [];
        foreach($results as $row){
             $totals_by_group[$row['groupid']] = $row['total'];
        }

        foreach($groups as $group){
             $total = isset($totals_by_group[$group['id']]) ? $totals_by_group[$group['id']] : 0;
             if($total > 0){
                 $labels[] = $group['name'];
                 $data[] = $total;
                 $backgroundColor[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
             }
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_project_status_stats()
    {
        $this->load->model('projects_model');
        $statuses = $this->projects_model->get_project_statuses();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        // Optimized Query
        $this->db->select('status, count(id) as total');
        $this->db->from(db_prefix().'projects');
        $this->db->group_by('status');
        $results = $this->db->get()->result_array();

        $totals_by_status = [];
        foreach($results as $row){
             $totals_by_status[$row['status']] = $row['total'];
        }
        
        foreach($statuses as $status){
            $total = isset($totals_by_status[$status['id']]) ? $totals_by_status[$status['id']] : 0;
            if($total > 0){
                $labels[] = $status['name'];
                $data[] = $total;
                $backgroundColor[] = $status['color'];
            }
        }
        
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_msg_logged_hours_by_staff()
    {
        // This can be heavy, let's just get top 5 staff by logged hours on tasks
        $this->db->select('staff_id, SUM(CASE WHEN end_time IS NULL THEN ' . time() . ' - start_time ELSE end_time - start_time END) as total_seconds');
        $this->db->from(db_prefix().'taskstimers');
        $this->db->group_by('staff_id');
        $this->db->order_by('total_seconds', 'DESC');
        $this->db->limit(10);
        $res = $this->db->get()->result_array();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach($res as $row){
            $staff = $this->staff_model->get($row['staff_id']);
            if($staff){
                $labels[] = $staff->firstname . ' ' . $staff->lastname;
                $data[] = round($row['total_seconds'] / 3600, 2); // Convert to hours
                $backgroundColor[] = '#03a9f4';
            }
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('task_total_logged_time') . ' (Hours)',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_win_rate_trend($year)
    {
        $months = [];
        $win_rate = [];

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Total Proposals Sent (Status != Draft)
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status !=', 1); // Not Draft
            $total_sent = $this->db->count_all_results(db_prefix().'proposals');
            
            // Accepted
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status', 3); // Accepted
            $accepted = $this->db->count_all_results(db_prefix().'proposals');
            
            $rate = ($total_sent > 0) ? ($accepted / $total_sent) * 100 : 0;
            $win_rate[] = round($rate, 1);
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Win Rate (%)',
                    'data' => $win_rate,
                    'borderColor' => '#10b981', // Green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }
    
    public function get_avg_sales_cycle_length($year)
    {
        $months = [];
        $cycle_days = [];
        
        for ($m = 1; $m <= 12; $m++) {
            $months[] = _l(date('F', mktime(0, 0, 0, $m, 1)));
            
            // Join proposals and invoices to find time diff
            // Assuming invoice_id in proposal links to invoice
            
            $sql = "SELECT AVG(DATEDIFF(i.date, p.date)) as avg_days 
                    FROM ".db_prefix()."proposals p 
                    JOIN ".db_prefix()."invoices i ON p.invoice_id = i.id 
                    WHERE MONTH(p.date) = $m AND YEAR(p.date) = $year 
                    AND p.invoice_id IS NOT NULL AND p.invoice_id != 0";
            
            $res = $this->db->query($sql)->row();
            $cycle_days[] = $res && $res->avg_days ? round($res->avg_days) : 0;
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Avg Days to Close',
                    'data' => $cycle_days,
                    'borderColor' => '#6366f1', // Indigo
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    public function get_payment_collection_trend($year)
    {
        $months = [];
        $efficiency = [];
        
        for ($m = 1; $m <= 12; $m++) {
            $months[] = _l(date('F', mktime(0, 0, 0, $m, 1)));
            
            // Invoiced Amount
            $this->db->select_sum('total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status !=', 6); // Not Draft
            $res_inv = $this->db->get(db_prefix().'invoices')->row();
            $invoiced = $res_inv->total ?? 0;
            
            // Payments Received
            $this->db->select_sum('amount');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res_pay = $this->db->get(db_prefix().'invoicepaymentrecords')->row();
            $collected = $res_pay->amount ?? 0;
            
            // Efficiency %
            $eff = ($invoiced > 0) ? ($collected / $invoiced) * 100 : 0;
            $efficiency[] = round($eff, 1);
        }
        
        return [
            'labels' => $months,
            'datasets' => [[
                'label' => 'Collection Efficiency (%)',
                'data' => $efficiency,
                'borderColor' => '#0ea5e9', // Sky Blue
                'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                'fill' => true,
                'tension' => 0.4
            ]]
        ];
    }
    
    public function get_cost_vs_revenue_trend($year)
    {
        $months = [];
        $revenue = [];
        $expenses = [];
        $profit = [];
        
        for ($m = 1; $m <= 12; $m++) {
            $months[] = _l(date('F', mktime(0, 0, 0, $m, 1)));
            
            // Revenue (Invoices)
            $this->db->select_sum('total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status !=', 5); // Not Cancelled
            $this->db->where('status !=', 6); // Not Draft
            $res_inv = $this->db->get(db_prefix().'invoices')->row();
            $rev = $res_inv->total ?? 0;
            
            // Expenses (Costs)
            $this->db->select_sum('amount');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res_exp = $this->db->get(db_prefix().'expenses')->row();
            $exp = $res_exp->amount ?? 0;
            
            $revenue[] = $rev;
            $expenses[] = $exp;
            $profit[] = $rev - $exp;
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue,
                    'borderColor' => '#10b981', // Green
                    'borderWidth' => 2,
                    'fill' => false
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenses,
                    'borderColor' => '#ef4444', // Red
                    'borderWidth' => 2,
                     'fill' => false
                ],
                [
                    'label' => 'Net Profit',
                    'type' => 'bar',
                    'data' => $profit,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => '#6366f1',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    public function get_revenue_concentration($year)
    {
        // Top 5 Customers vs Others
        $this->db->select('clientid, SUM(total) as revenue');
        $this->db->from(db_prefix().'invoices');
        $this->db->where('YEAR(date)', $year);
        $this->db->group_by('clientid');
        $this->db->order_by('revenue', 'DESC');
        $all_clients = $this->db->get()->result_array();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        $total_revenue = array_sum(array_column($all_clients, 'revenue'));
        $top_rev = 0;
        
        $i = 0;
        foreach($all_clients as $c){
             if($i < 5){
                 $client_name = get_company_name($c['clientid']);
                 $labels[] = $client_name ? $client_name : 'Unknown';
                 $data[] = $c['revenue'];
                 $top_rev += $c['revenue'];
                 // Generate Color
                 $u = md5($c['clientid']);
                 $backgroundColor[] = '#'.substr($u, 0, 6);
                 $i++;
             }
        }
        
        // Others
        $others = $total_revenue - $top_rev;
        if($others > 0){
            $labels[] = 'Others';
            $data[] = $others;
            $backgroundColor[] = '#cbd5e1';
        }
        
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ]]
        ];
    }

    public function get_customer_loyalty_metrics($year)
    {
        // New vs Recurring Revenue per month
        $months = [];
        $new_rev = [];
        $recurring_rev = [];
        
        for ($m = 1; $m <= 12; $m++) {
             $months[] = _l(date('F', mktime(0, 0, 0, $m, 1)));
             
             // Get invoices for this month
             $this->db->select('clientid, total');
             $this->db->where('MONTH(date)', $m);
             $this->db->where('YEAR(date)', $year);
             $invoices = $this->db->get(db_prefix().'invoices')->result_array();
             
             $month_new = 0;
             $month_rec = 0;
             
             foreach($invoices as $inv){
                 // Check if client had invoice BEFORE this year OR before this month in this year
                 // Actually simple definition: New Client = First invoice ever is in this month.
                 
                 // Performance: This query inside loop is heavy but accurate. Optimize if slow.
                 $this->db->where('clientid', $inv['clientid']);
                 $this->db->where("date < '{$year}-".sprintf('%02d',$m)."-01'"); 
                 $prev = $this->db->count_all_results(db_prefix().'invoices');
                 
                 if($prev == 0){
                     $month_new += $inv['total'];
                 } else {
                     $month_rec += $inv['total'];
                 }
             }
             
             $new_rev[] = $month_new;
             $recurring_rev[] = $month_rec;
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'New Business',
                    'data' => $new_rev,
                    'backgroundColor' => '#3b82f6', // Blue
                ],
                [
                    'label' => 'Recurring',
                    'data' => $recurring_rev,
                    'backgroundColor' => '#a855f7', // Purple
                ]
            ]
        ];
    }
    public function get_revenue_trend($year)
    {
        // Monthly revenue for selected year vs previous year
        $months = [];
        $current_year_data = [];
        $prev_year_data = [];
        $prev_year = $year - 1;

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Current Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status !=', 5); // Exclude cancelled
            $res = $this->db->get(db_prefix().'invoices')->row();
            $current_year_data[] = $res ? $res->total : 0;

            // Previous Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $prev_year);
            $this->db->where('status !=', 5);
            $res_prev = $this->db->get(db_prefix().'invoices')->row();
            $prev_year_data[] = $res_prev ? $res_prev->total : 0;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => $year,
                    'data' => $current_year_data,
                    'borderColor' => '#84c529',
                    'backgroundColor' => 'rgba(132, 197, 41, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $prev_year,
                    'data' => $prev_year_data,
                    'borderColor' => '#c53da9',
                    'backgroundColor' => 'rgba(197, 61, 169, 0.1)',
                    'fill' => true,
                    'borderDash' => [5, 5],
                ]
            ]
        ];
    }

    public function get_top_performing_items($limit = 10)
    {
        $this->db->select('description, SUM(qty) as total_qty, SUM(qty * rate) as total_revenue');
        $this->db->from(db_prefix() . 'itemable');
        $this->db->where('rel_type', 'invoice');
        $this->db->group_by('description');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit($limit);
        $items = $this->db->get()->result_array();

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach($items as $item){
            $labels[] = $item['description'];
            $data[] = $item['total_revenue'];
            $backgroundColor[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('revenue'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_top_sales_staff($year, $limit = 10)
    {
        $this->db->select('sale_agent, SUM(total) as total_sold');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('YEAR(date)', $year);
        $this->db->where('status !=', 5);
        $this->db->where('sale_agent !=', 0);
        $this->db->group_by('sale_agent');
        $this->db->order_by('total_sold', 'DESC');
        $this->db->limit($limit);
        $agents = $this->db->get()->result_array();

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach($agents as $agent){
            $staff = $this->staff_model->get($agent['sale_agent']);
            if($staff){
                $labels[] = $staff->firstname . ' ' . $staff->lastname;
                $data[] = $agent['total_sold'];
                $backgroundColor[] = '#2563eb'; // Uniform color for leaderboard
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('total_sales'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }
    
    public function get_proposal_conversion_funnel()
    {
        // Total Proposals
        $total = $this->db->count_all_results(db_prefix().'proposals');
        
        // Viewed (assuming there is a field or logic, usually open status implies viewed or sent)
        $this->db->where('status !=', 1); // 1 is Draft usually, so Sent/Open
        $sent = $this->db->count_all_results(db_prefix().'proposals');

        // Accepted/Converted
        $this->db->where('status', 3); // 3 is Accepted usually
        $accepted = $this->db->count_all_results(db_prefix().'proposals');
        
        // Converted to Invoice
        $this->db->where('invoice_id !=', NULL);
        $this->db->where('invoice_id !=', 0);
        $invoiced = $this->db->count_all_results(db_prefix().'proposals');

        return [
            'labels' => ['Draft', 'Sent/Active', 'Accepted', 'Converted to Invoice'],
            'datasets' => [
                [
                    'data' => [$total, $sent, $accepted, $invoiced],
                    'backgroundColor' => ['#9ca3af', '#60a5fa', '#34d399', '#f59e0b'],
                ]
            ]
        ];
    }
    public function get_estimate_conversion_funnel()
    {
        // Total Estimates (Active)
        $this->db->where('status !=', 5); // 5 is Cancelled
        $total = $this->db->count_all_results(db_prefix().'estimates');
        
        // Sent
        $this->db->where('status !=', 1); // 1 is Draft
        $this->db->where('status !=', 5);
        $sent = $this->db->count_all_results(db_prefix().'estimates');

        // Accepted
        $this->db->where('status', 4); // 4 is Accepted
        $accepted = $this->db->count_all_results(db_prefix().'estimates');
        
        // Converted to Invoice
        $this->db->where('invoiceid !=', NULL);
        $this->db->where('invoiceid !=', 0);
        $invoiced = $this->db->count_all_results(db_prefix().'estimates');

        return [
            'labels' => ['Active', 'Sent', 'Accepted', 'Invoiced'],
            'datasets' => [
                [
                    'data' => [$total, $sent, $accepted, $invoiced],
                    'backgroundColor' => ['#9ca3af', '#60a5fa', '#34d399', '#f59e0b'],
                ]
            ]
        ];
    }

    public function get_invoice_payment_funnel()
    {
         // Generated / Active (Excluding Cancelled and Draft)
        $this->db->where('status !=', 5); // Cancelled
        $this->db->where('status !=', 6); // Draft
        $generated = $this->db->count_all_results(db_prefix().'invoices');
        
        // Sent (Not Draft, Not Cancelled - assuming sent if status is not Draft)
        // status 6 is draft. 
        $sent = $generated; // In Perfex, if it's not draft, it's considered active/sent usually.

        // Partially Paid (Status 3)
        $this->db->where('status', 3);
        $partially_paid = $this->db->count_all_results(db_prefix().'invoices');
        
        // Fully Paid (Status 2)
        $this->db->where('status', 2);
        $fully_paid = $this->db->count_all_results(db_prefix().'invoices');

        return [
            'labels' => ['Active', 'Partially Paid', 'Fully Paid'],
            'datasets' => [
                [
                    'data' => [$generated, $partially_paid, $fully_paid],
                    'backgroundColor' => ['#60a5fa', '#f59e0b', '#34d399'],
                ]
            ]
        ];
    }
    public function get_invoice_value_stats()
    {
        $this->load->model('invoices_model');
        $statuses = $this->invoices_model->get_statuses();
        $colors = get_system_favourite_colors();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($statuses as $status) {
            $this->db->select('SUM(total) as total_value');
            $this->db->where('status', $status);
            $res = $this->db->get(db_prefix() . 'invoices')->row();
            
            if ($res && $res->total_value > 0) {
                $labels[] = format_invoice_status($status, '', false);
                $data[] = $res->total_value;
                $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#c53da9';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('total_sales'), // Or 'Amount'
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_proposal_value_stats()
    {
        $this->load->model('proposals_model');
        $statuses = $this->proposals_model->get_statuses();
        $colors = get_system_favourite_colors(); 

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($statuses as $status) {
            $this->db->select('SUM(total) as total_value');
            $this->db->where('status', $status);
            $res = $this->db->get(db_prefix() . 'proposals')->row();
            
            if($res && $res->total_value > 0){
                $labels[] = format_proposal_status($status, '', false);
                $data[] = $res->total_value;
                $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#84c529';
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('total_amount'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_estimate_value_stats()
    {
        $this->load->model('estimates_model');
        $statuses = $this->estimates_model->get_statuses();
        $colors = get_system_favourite_colors();

        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach($statuses as $status){
            $this->db->select('SUM(total) as total_value');
            $this->db->where('status', $status);
            $res = $this->db->get(db_prefix() . 'estimates')->row();
             
            if($res && $res->total_value > 0){
                $labels[] = format_estimate_status($status, '', false);
                $data[] = $res->total_value;
                $backgroundColor[] = isset($colors[$status]) ? $colors[$status] : '#03a9f4';
            }
        }
        
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('total_amount'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }
    public function get_proposal_trend($year)
    {
        $months = [];
        $current_year_data = [];
        $prev_year_data = [];
        $prev_year = $year - 1;

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Current Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $this->db->where('status !=', 0); // Exclude if needed, usually 0 is not valid status ID but check db
            $res = $this->db->get(db_prefix().'proposals')->row();
            $current_year_data[] = $res ? $res->total : 0;

            // Previous Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $prev_year);
            $res_prev = $this->db->get(db_prefix().'proposals')->row();
            $prev_year_data[] = $res_prev ? $res_prev->total : 0;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => $year,
                    'data' => $current_year_data,
                    'borderColor' => '#84c529',
                    'backgroundColor' => 'rgba(132, 197, 41, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $prev_year,
                    'data' => $prev_year_data,
                    'borderColor' => '#c53da9',
                    'backgroundColor' => 'rgba(197, 61, 169, 0.1)',
                    'fill' => true,
                    'borderDash' => [5, 5],
                ]
            ]
        ];
    }

    public function get_estimate_trend($year)
    {
        $months = [];
        $current_year_data = [];
        $prev_year_data = [];
        $prev_year = $year - 1;

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Current Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res = $this->db->get(db_prefix().'estimates')->row();
            $current_year_data[] = $res ? $res->total : 0;

            // Previous Year
            $this->db->select('SUM(total) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $prev_year);
            $res_prev = $this->db->get(db_prefix().'estimates')->row();
            $prev_year_data[] = $res_prev ? $res_prev->total : 0;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => $year,
                    'data' => $current_year_data,
                    'borderColor' => '#03a9f4',
                    'backgroundColor' => 'rgba(3, 169, 244, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $prev_year,
                    'data' => $prev_year_data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'borderDash' => [5, 5],
                ]
            ]
        ];
    }
    public function get_activity_volume_trend($year)
    {
        $months = [];
        $proposals_data = [];
        $estimates_data = [];
        $invoices_data = [];

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Proposals Count
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $proposals_data[] = $this->db->count_all_results(db_prefix().'proposals');

            // Estimates Count
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $estimates_data[] = $this->db->count_all_results(db_prefix().'estimates');

             // Invoices Count
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $invoices_data[] = $this->db->count_all_results(db_prefix().'invoices');
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => _l('proposals'),
                    'data' => $proposals_data,
                    'borderColor' => '#84c529',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                ],
                [
                    'label' => _l('estimates'),
                    'data' => $estimates_data,
                    'borderColor' => '#f59e0b', // Yellow/Orange
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                ],
                [
                    'label' => _l('invoices'),
                    'data' => $invoices_data,
                    'borderColor' => '#ef4444', // Red
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                ]
            ]
        ];
    }

    public function get_average_deal_size_trend($year)
    {
        $months = [];
        $avg_proposals = [];
        $avg_estimates = [];

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Avg Proposal
            $this->db->select('AVG(total) as avg_val');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res_prop = $this->db->get(db_prefix().'proposals')->row();
            $avg_proposals[] = $res_prop && $res_prop->avg_val ? round($res_prop->avg_val, 2) : 0;

            // Avg Estimate
            $this->db->select('AVG(total) as avg_val');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res_est = $this->db->get(db_prefix().'estimates')->row();
            $avg_estimates[] = $res_est && $res_est->avg_val ? round($res_est->avg_val, 2) : 0;
        }

         return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => _l('proposals'),
                    'data' => $avg_proposals,
                    'borderColor' => '#84c529',
                    'backgroundColor' => 'rgba(132, 197, 41, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => _l('estimates'),
                    'data' => $avg_estimates,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ]
            ]
        ];
    }
    public function get_payment_receipts_trend($year)
    {
        $months = [];
        $current_year_data = [];
        $prev_year_data = [];
        $prev_year = $year - 1;

        for ($m = 1; $m <= 12; $m++) {
            $month_name = _l(date('F', mktime(0, 0, 0, $m, 1)));
            $months[] = $month_name;
            
            // Current Year Payments
            $this->db->select('SUM(amount) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $year);
            $res = $this->db->get(db_prefix().'invoicepaymentrecords')->row();
            $current_year_data[] = $res && $res->total ? $res->total : 0;

            // Previous Year Payments
            $this->db->select('SUM(amount) as total');
            $this->db->where('MONTH(date)', $m);
            $this->db->where('YEAR(date)', $prev_year);
            $res_prev = $this->db->get(db_prefix().'invoicepaymentrecords')->row();
            $prev_year_data[] = $res_prev && $res_prev->total ? $res_prev->total : 0;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => $year,
                    'data' => $current_year_data,
                    'borderColor' => '#2563eb', // Blue
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $prev_year,
                    'data' => $prev_year_data,
                    'borderColor' => '#9ca3af', // Grey
                    'backgroundColor' => 'rgba(156, 163, 175, 0.1)',
                    'fill' => true,
                    'borderDash' => [5, 5],
                ]
            ]
        ];
    }

    public function get_top_paying_customers($limit = 10)
    {
        // Sum payments by client
        // Join invoices to get clientid
        $this->db->select('c.userid, c.company, SUM(p.amount) as total_paid');
        $this->db->from(db_prefix() . 'invoicepaymentrecords p');
        $this->db->join(db_prefix() . 'invoices i', 'i.id = p.invoiceid', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = i.clientid', 'left');
        $this->db->where('c.userid IS NOT NULL');
        $this->db->group_by('c.userid');
        $this->db->order_by('total_paid', 'DESC');
        $this->db->limit($limit);
        
        $clients = $this->db->get()->result_array();

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach($clients as $client){
            $labels[] = $client['company'];
            $data[] = $client['total_paid'];
            $backgroundColor[] = '#10b981'; // Emerald Green
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('payments_received'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }
    public function get_client_analytics_data($client_id)
    {
        $this->load->model('clients_model');
        $this->load->model('currencies_model');
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('credit_notes_model');

        $client = $this->clients_model->get($client_id);
        if (!$client) {
            return false;
        }

        $data = [];
        $data['title'] = _l('advanced_analytics') . ' - ' . $client->company;
        $data['client'] = $client;
        $data['client_id'] = $client_id;

        // Get last 30 days purchase frequency
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        $data['invoices_30_days'] = $this->invoices_model->get_invoices_total_by_client($client_id, $thirty_days_ago);
        $data['estimates_30_days'] = $this->estimates_model->get_estimates_total_by_client($client_id, $thirty_days_ago);
        $data['proposals_30_days'] = $this->proposals_model->get_proposals_total_by_client($client_id, $thirty_days_ago);

        // Get purchase history
        $data['total_invoiced'] = sum_from_table(db_prefix() . 'invoices', ['field' => 'total', 'where' => ['clientid' => $client_id, 'status !=' => 5]]);
        $data['total_paid'] = sum_from_table(db_prefix() . 'invoicepaymentrecords', ['field' => 'amount', 'where' => ['invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE clientid=' . $client_id . ')']]);

        // Get credit notes
        $data['total_credits'] = sum_from_table(db_prefix() . 'creditnotes', ['field' => 'total', 'where' => ['clientid' => $client_id]]);

        // Calculate client score/categorization
        $data['client_score'] = $this->calculate_client_score($client_id);

        // Get most purchased items
        $this->db->select('description, SUM(qty) as total_quantity, SUM(qty * rate) as total_amount');
        $this->db->from(db_prefix() . 'itemable');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id');
        $this->db->where('rel_type', 'invoice');
        $this->db->where('clientid', $client_id);
        $this->db->group_by('description');
        $this->db->order_by('total_quantity', 'DESC');
        $this->db->limit(5);
        $data['top_purchased_items'] = $this->db->get()->result_array();

        // Get payment history
        $this->db->select('amount, '.db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where('clientid', $client_id);
        $this->db->order_by('date', 'DESC');
        $this->db->limit(10);
        $data['recent_payments'] = $this->db->get()->result_array();

        // Get currencies
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        return $data;
    }

    /**
     * Calculate client score based on various factors
     * Higher score means better customer
     *
     * @param integer $client_id The client ID
     * @return array Score details with total score and category
     */
    public function calculate_client_score($client_id)
    {
        $score = [
            'payment_promptness' => 0,
            'purchase_frequency' => 0,
            'purchase_value' => 0,
            'loyalty' => 0,
            'total' => 0,
            'category' => ''
        ];

        // Payment promptness (how quickly they pay invoices)
        $this->db->select('DATEDIFF(' . db_prefix() . 'invoicepaymentrecords.date, '.db_prefix() . 'invoices.duedate) as days_diff');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where('clientid', $client_id);
        $payment_days = $this->db->get()->result_array();

        $total_days = 0;
        $payment_count = count($payment_days);

        if ($payment_count > 0) {
            foreach ($payment_days as $payment) {
                $total_days += $payment['days_diff'];
            }
            $avg_days = $total_days / $payment_count;

            // Score based on average payment time
            if ($avg_days <= 0) {
                $score['payment_promptness'] = 25; // Paid before due date
            } elseif ($avg_days <= 7) {
                $score['payment_promptness'] = 20; // Paid within a week after due date
            } elseif ($avg_days <= 14) {
                $score['payment_promptness'] = 15; // Paid within two weeks after due date
            } elseif ($avg_days <= 30) {
                $score['payment_promptness'] = 10; // Paid within a month after due date
            } else {
                $score['payment_promptness'] = 5; // Paid more than a month after due date
            }
        }

        // Purchase frequency (how often they make purchases)
        $this->db->select('COUNT(*) as invoice_count');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('clientid', $client_id);
        $this->db->where('status !=', 5); // Exclude cancelled invoices
        $invoice_count = $this->db->get()->row()->invoice_count;

        // Get client creation date
        $this->db->select('datecreated');
        $this->db->from(db_prefix() . 'clients');
        $this->db->where('userid', $client_id);
        $client_created = $this->db->get()->row()->datecreated;

        $days_as_client = max(1, ceil((time() - strtotime($client_created)) / (60 * 60 * 24)));
        $months_as_client = $days_as_client / 30;

        $invoices_per_month = $invoice_count / max(1, $months_as_client);

        // Score based on invoices per month
        if ($invoices_per_month >= 4) {
            $score['purchase_frequency'] = 25; // More than weekly purchases
        } elseif ($invoices_per_month >= 2) {
            $score['purchase_frequency'] = 20; // Bi-weekly purchases
        } elseif ($invoices_per_month >= 1) {
            $score['purchase_frequency'] = 15; // Monthly purchases
        } elseif ($invoices_per_month >= 0.5) {
            $score['purchase_frequency'] = 10; // Bi-monthly purchases
        } else {
            $score['purchase_frequency'] = 5; // Less frequent purchases
        }

        // Purchase value (how much they spend)
        $this->db->select('SUM(total) as total_spent');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('clientid', $client_id);
        $this->db->where('status !=', 5); // Exclude cancelled invoices
        $total_spent = $this->db->get()->row()->total_spent;

        $avg_invoice_value = $invoice_count > 0 ? $total_spent / $invoice_count : 0;

        // Score based on average invoice value
        if ($avg_invoice_value >= 5000) {
            $score['purchase_value'] = 25; // High value customer
        } elseif ($avg_invoice_value >= 1000) {
            $score['purchase_value'] = 20;
        } elseif ($avg_invoice_value >= 500) {
            $score['purchase_value'] = 15;
        } elseif ($avg_invoice_value >= 100) {
            $score['purchase_value'] = 10;
        } else {
            $score['purchase_value'] = 5; // Low value customer
        }

        // Loyalty (how long they've been a customer)
        $years_as_client = $days_as_client / 365;

        // Score based on years as client
        if ($years_as_client >= 5) {
            $score['loyalty'] = 25; // Long-term customer (5+ years)
        } elseif ($years_as_client >= 3) {
            $score['loyalty'] = 20; // 3-5 years
        } elseif ($years_as_client >= 2) {
            $score['loyalty'] = 15; // 2-3 years
        } elseif ($years_as_client >= 1) {
            $score['loyalty'] = 10; // 1-2 years
        } else {
            $score['loyalty'] = 5; // Less than 1 year
        }

        // Calculate total score
        $score['total'] = $score['payment_promptness'] + $score['purchase_frequency'] + $score['purchase_value'] + $score['loyalty'];

        // Determine category based on total score
        if ($score['total'] >= 80) {
            $score['category'] = 'excellent';
        } elseif ($score['total'] >= 60) {
            $score['category'] = 'good';
        } elseif ($score['total'] >= 40) {
            $score['category'] = 'average';
        } elseif ($score['total'] >= 20) {
            $score['category'] = 'below_average';
        } else {
            $score['category'] = 'poor';
        }

        return $score;
    }


    /**
     * Get Proposal Analytics with Forecast
     */
    public function get_proposal_analytics($period = 'all_time') 
    {
        $this->load->model('proposals_model');
        $statuses = $this->proposals_model->get_statuses();
        
        $date_where = $this->get_date_filter($period, 'date');
        // Filter by user if needed
        $where_own = [];
        if (!has_permission('proposals', '', 'view')) {
            $where_own['addedfrom'] = get_staff_user_id();
        }
        $final_where = array_merge($where_own, $date_where);

        $total_proposals = total_rows(db_prefix() . 'proposals', $final_where);
        $status_financials = [];
        $status_counts = [];

        foreach ($statuses as $status) {
            $where = array_merge(['status' => $status], $final_where);
            $count = total_rows(db_prefix() . 'proposals', $where);
            $status_counts[$status] = $count;
            
            $this->db->select_sum('total');
            $this->db->where($where);
            $sum_query = $this->db->get(db_prefix() . 'proposals')->row();
            $status_financials[$status] = $sum_query->total ?? 0;
        }

        // Regression Data
        $this->db->select('date');
        if (!empty($final_where)) { $this->db->where($final_where); }
        $this->db->order_by('date', 'ASC');
        $rows = $this->db->get(db_prefix().'proposals')->result_array();
        
        $chart_data = $this->prepare_forecast_chart_data($rows, $period, 'Proposals');

        return [
            'total' => $total_proposals,
            'status_counts' => $status_counts,
            'status_financials' => $status_financials,
            'chart_data' => $chart_data,
            'statuses' => $statuses
        ];
    }

    /**
     * Get Estimate Analytics with Forecast
     */
    public function get_estimate_analytics($period = 'all_time') 
    {
        $this->load->model('estimates_model');
        $statuses = $this->estimates_model->get_statuses();
        
        $date_where = $this->get_date_filter($period, 'date');
        $where_own = [];
        if (!has_permission('estimates', '', 'view')) {
            $where_own['addedfrom'] = get_staff_user_id();
        }
        $final_where = array_merge($where_own, $date_where);

        $total_estimates = total_rows(db_prefix() . 'estimates', $final_where);
        $status_financials = [];
        $status_counts = [];

        foreach ($statuses as $status) {
            $where = array_merge(['status' => $status], $final_where);
            $count = total_rows(db_prefix() . 'estimates', $where);
            $status_counts[$status] = $count;

            $this->db->select_sum('total');
            $this->db->where($where);
            $sum_query = $this->db->get(db_prefix() . 'estimates')->row();
            $status_financials[$status] = $sum_query->total ?? 0;
        }

        // Retained Funds
        // 1. Available Retained (Held)
        // Check if 'retained' column exists first to be safe, though users old version has it
        // Assuming 'retained' column exists on estimates table based on old file
        $retained_held = 0;
        $retained_released = 0;
        
        if ($this->db->field_exists('retained', db_prefix() . 'estimates')) {
            $where_held = array_merge($final_where, ['retained' => "0"]);
            $this->db->select('(SUM(total)*0.15) as retained_amount'); // 15% rule from old code
            $this->db->where($where_held);
            $q_held = $this->db->get(db_prefix().'estimates')->row();
            $retained_held = $q_held->retained_amount ?? 0;

            $where_released = array_merge($final_where, ['retained' => "1"]);
            $this->db->select('(SUM(total)*0.15) as retained_amount');
            $this->db->where($where_released);
            $q_released = $this->db->get(db_prefix().'estimates')->row();
            $retained_released = $q_released->retained_amount ?? 0;
        }

        // Regression Data
        $this->db->select('date');
        if (!empty($final_where)) { $this->db->where($final_where); }
        $this->db->order_by('date', 'ASC');
        $rows = $this->db->get(db_prefix().'estimates')->result_array();

        $chart_data = $this->prepare_forecast_chart_data($rows, $period, 'Estimates');

        return [
            'total' => $total_estimates,
            'status_counts' => $status_counts,
            'status_financials' => $status_financials,
            'retained_held' => $retained_held,
            'retained_released' => $retained_released,
            'chart_data' => $chart_data,
            'statuses' => $statuses
        ];
    }

    /**
     * Get Invoice Analytics with Forecast
     */
    public function get_invoice_analytics($period = 'all_time') 
    {
        $this->load->model('invoices_model');
        $statuses = [1,2,3,4,5,6]; // Standard invoice statuses
        
        $date_where = $this->get_date_filter($period, 'date');
        $where_own = [];
        if (!has_permission('invoices', '', 'view')) {
            $where_own['addedfrom'] = get_staff_user_id();
        }
        $final_where = array_merge($where_own, $date_where);

        $total_invoices = total_rows(db_prefix() . 'invoices', $final_where);
        $status_financials = [];
        $status_counts = [];

        foreach ($statuses as $status) {
            $where = array_merge(['status' => $status], $final_where);
            $count = total_rows(db_prefix() . 'invoices', $where);
            $status_counts[$status] = $count;

            $this->db->select_sum('total');
            $this->db->where($where);
            $sum_query = $this->db->get(db_prefix() . 'invoices')->row();
            $status_financials[$status] = $sum_query->total ?? 0;
        }

        // Regression Data
        $this->db->select('date');
        if (!empty($final_where)) { $this->db->where($final_where); }
        $this->db->order_by('date', 'ASC');
        $rows = $this->db->get(db_prefix().'invoices')->result_array();

        $chart_data = $this->prepare_forecast_chart_data($rows, $period, 'Invoices');

        return [
            'total' => $total_invoices,
            'status_counts' => $status_counts,
            'status_financials' => $status_financials,
            'chart_data' => $chart_data,
            'statuses' => $statuses
        ];
    }

    // --- Helpers ---

    private function get_date_filter($period, $column = 'date')
    {
        $date_where = [];
        if($period == 'this_month'){
            $date_where[$column . ' >='] = date('Y-m-01');
            $date_where[$column . ' <='] = date('Y-m-t');
        } elseif($period == 'last_month'){
            $date_where[$column . ' >='] = date('Y-m-01', strtotime('last month'));
            $date_where[$column . ' <='] = date('Y-m-t', strtotime('last month'));
        } elseif($period == 'this_year'){
            $date_where[$column . ' >='] = date('Y-01-01');
            $date_where[$column . ' <='] = date('Y-12-31');
        } elseif($period == 'last_year'){
            $date_where[$column . ' >='] = date('Y-01-01', strtotime('last year'));
            $date_where[$column . ' <='] = date('Y-12-31', strtotime('last year'));
        } elseif($period == 'last_6_months'){
            $date_where[$column . ' >='] = date('Y-m-d', strtotime('-6 months'));
            $date_where[$column . ' <='] = date('Y-m-d');
        }
        return $date_where;
    }

    private function prepare_forecast_chart_data($rows, $period, $label)
    {
        $temp_chart_data = [];
        foreach($rows as $row){
            if(!$row['date']) continue;
            // Grouping
            if($period == 'this_month' || $period == 'last_month') {
                $key = date('d M', strtotime($row['date']));
            } else {
                $key = date('Y-m', strtotime($row['date']));
            }
            if(!isset($temp_chart_data[$key])) $temp_chart_data[$key] = 0;
            $temp_chart_data[$key]++;
        }
        
        $hist_labels = array_keys($temp_chart_data);
        $hist_values = array_values($temp_chart_data);
        $n = count($hist_values);

        // Linear Regression
        $forecast_values = [];
        $upper_bound = [];
        $lower_bound = [];
        $future_labels = [];
        
        $enable_forecast = ($n > 1 && $period != 'this_month' && $period != 'last_month');
        
        if ($enable_forecast) {
            $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
            for ($i = 0; $i < $n; $i++) {
                $x = $i; $y = $hist_values[$i];
                $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumXX += ($x * $x);
            }
            
            // Avoid division by zero
             $denominator = (($n * $sumXX) - ($sumX * $sumX));
             if ($denominator == 0) {
                 $m = 0;
                 $b = ($n > 0) ? $sumY / $n : 0;
             } else {
                 $m = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
                 $b = ($sumY - ($m * $sumX)) / $n;
             }

            $forecast_months = 6;
            // Padding historical data with nulls for forecast part
            // But we normally return 2 separate datasets or one merged. 
            // Let's match the structure needed for Chart.js
            // We need labels for the whole range (history + future)
            
            for ($i = 0; $i < ($n + $forecast_months); $i++) {
                if($i < $n) {
                    $future_labels[] = $hist_labels[$i];
                    $forecast_values[] = null;
                    $upper_bound[] = null;
                    $lower_bound[] = null;
                } else {
                     $future_labels[] = "Forecast +" . ($i - $n + 1);
                     $predicted_y = max(0, ($m * $i) + $b);
                     $forecast_values[] = $predicted_y;
                     $upper_bound[] = $predicted_y * 1.2;
                     $lower_bound[] = $predicted_y * 0.8;
                }
            }
        } else {
            $future_labels = $hist_labels;
        }

        return [
            'labels' => $future_labels,
            'history' => $hist_values, // Note: this array length is $n, shorter than labels if forecast enabled
            'forecast' => $forecast_values,
            'upper' => $upper_bound,
            'lower' => $lower_bound,
            'enable_forecast' => $enable_forecast
        ];
    }
    public function get_sales_targets()
    {
        $this->load->model('goals/goals_model');
        $goals = $this->goals_model->get();
        
        $sales_goals = [];
        foreach ($goals as $goal) {
            // Filter for sales related goals if possible, or just return all and filter in view
            // Goal types: 1=Achieve Total Income, 2=Convert X Leads, 3=Increase Customer Number, 4=Increase Customer Number (Type)
            // We are interested in Type 1 (Income) mostly for "Sales Targets"
            if ($goal['goal_type'] == 1) {
                $achievement = $this->goals_model->calculate_goal_achievement($goal['id']);
                $goal['achievement'] = $achievement;
                $sales_goals[] = $goal;
            }
        }
        return $sales_goals;
    }

    public function get_expenses_by_category($year)
    {
        $this->load->model('expenses_model');
        $categories = $this->expenses_model->get_category();
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach($categories as $category){
            $this->db->where('category', $category['id']);
            $this->db->where('YEAR(date)', $year);
            $this->db->select_sum('amount');
            $this->db->select_sum('tax');
            $res = $this->db->get(db_prefix().'expenses')->row();
            
            $total = $res->amount ?? 0;
            
            if($total > 0){
                $labels[] = $category['name'];
                $data[] = $total;
                $backgroundColor[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
            }
        }
        
         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => _l('expenses'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]
            ]
        ];
    }

    public function get_financial_health_stats($year)
    {
         // Total Income
         $this->db->select_sum('amount');
         $this->db->where('YEAR(date)', $year);
         $income_res = $this->db->get(db_prefix().'invoicepaymentrecords')->row();
         $total_income = $income_res->amount ?? 0;
         
         // Total Expenses
         $this->db->select_sum('amount');
         $this->db->where('YEAR(date)', $year);
         $expense_res = $this->db->get(db_prefix().'expenses')->row();
         $total_expenses = $expense_res->amount ?? 0;
         
         $net_profit = $total_income - $total_expenses;
         $profit_margin = 0;
         if($total_income > 0){
             $profit_margin = ($net_profit / $total_income) * 100;
         }
         
         return [
             'total_income' => $total_income,
             'total_expenses' => $total_expenses,
             'net_profit' => $net_profit,
             'profit_margin' => $profit_margin
         ];
    }
    public function get_new_vs_returning_customers($year)
    {
        $months = [];
        $new_customers = [];
        // Ideally "Returning" means they bought something again, but for this context, 
        // we might just track "Total Active" vs "New" or similar.
        // Let's stick to "New Customer Registrations" vs "Recurring Invoices" count maybe?
        // Or simply: New Customers (Registered this month)
        
        for ($m = 1; $m <= 12; $m++) {
            $months[] = _l(date('F', mktime(0, 0, 0, $m, 1)));
            
            $this->db->where('MONTH(datecreated)', $m);
            $this->db->where('YEAR(datecreated)', $year);
            $new_customers[] = $this->db->count_all_results(db_prefix().'clients');
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $new_customers,
                    'borderColor' => '#84c529',
                    'backgroundColor' => 'rgba(132, 197, 41, 0.1)',
                    'fill' => true,
                ]
            ]
        ];
    }

    public function get_customer_geography()
    {
        $this->db->select('country, COUNT(*) as total');
        $this->db->group_by('country');
        $this->db->where('country !=', 0); // Exclude unknown
        $this->db->order_by('total', 'DESC');
        $this->db->limit(10);
        $rows = $this->db->get(db_prefix().'clients')->result_array();
        
        $labels = [];
        $data = [];
        $colors = [];
        
        
        // Assuming we need to fetch country name from ID
        $all_countries = get_all_countries(); 
        $country_map = [];
        foreach($all_countries as $c){
            $country_map[$c['country_id']] = $c['short_name'];
        }

        foreach($rows as $row){
            $name = isset($country_map[$row['country']]) ? $country_map[$row['country']] : 'Unknown';
            $labels[] = $name;
            $data[] = $row['total'];
            $colors[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

         return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ]
            ]
        ];
    }

    public function get_customer_retention_kpis()
    {
        // Churn Rate: Inactive / Total
        $total_clients = $this->db->count_all_results(db_prefix().'clients');
        $this->db->where('active', 0);
        $inactive_clients = $this->db->count_all_results(db_prefix().'clients');
        
        $churn_rate = 0;
        $retention_rate = 100;
        
        if($total_clients > 0){
            $churn_rate = ($inactive_clients / $total_clients) * 100;
            $retention_rate = 100 - $churn_rate;
        }

        // New Clients this Month
        $this->db->where('MONTH(datecreated)', date('m'));
        $this->db->where('YEAR(datecreated)', date('Y'));
        $new_this_month = $this->db->count_all_results(db_prefix().'clients');

        return [
            'total_customers' => $total_clients,
            'churn_rate' => $churn_rate,
            'retention_rate' => $retention_rate,
            'new_this_month' => $new_this_month
        ];
    }
    public function get_sales_velocity()
    {
        // 1. Opportunities (Active Proposals)
        $this->db->where('status !=', 3); // Not Accepted (still open/draft/sent)
        $this->db->where('status !=', 2); // Not Declined
        $num_opportunities = $this->db->count_all_results(db_prefix().'proposals');
        
        // 2. Avg Deal Value
        $this->db->select_avg('total');
        $this->db->where('status !=', 3);
        $this->db->where('status !=', 2);
        $query = $this->db->get(db_prefix().'proposals')->row();
        $avg_deal_value = $query ? $query->total : 0;
        
        // 3. Win Rate % (Accepted / (Accepted + Declined))
        $this->db->where('status', 3); 
        $won = $this->db->count_all_results(db_prefix().'proposals');
        
        $this->db->where('status', 2);
        $lost = $this->db->count_all_results(db_prefix().'proposals');
        
        $win_rate = ($won + $lost > 0) ? $won / ($won + $lost) : 0;
        
        // 4. Sales Cycle Length (Days)
        $sql = "SELECT AVG(DATEDIFF(i.date, p.date)) as avg_days 
                FROM ".db_prefix()."proposals p 
                JOIN ".db_prefix()."invoices i ON p.invoice_id = i.id 
                WHERE p.invoice_id IS NOT NULL AND p.invoice_id != 0";
        $length_query = $this->db->query($sql)->row();
        $length_sales_cycle = ($length_query && $length_query->avg_days) ? $length_query->avg_days : 30; // Default to 30 if no data
        
        if($length_sales_cycle <= 0) $length_sales_cycle = 1;
        
        // Velocity = (Ops * Deal Value * Win Rate) / Length
        $velocity = ($num_opportunities * $avg_deal_value * $win_rate) / $length_sales_cycle;
        
        return [
            'velocity' => $velocity,
            'opportunities' => $num_opportunities,
            'avg_deal_value' => $avg_deal_value,
            'win_rate' => $win_rate * 100,
            'length_sales_cycle' => $length_sales_cycle
        ];
    }

    public function get_win_rate_analysis()
    {
        $year = date('Y');
        
        $this->db->where('YEAR(date)', $year);
        $this->db->where('status', 3); // Accepted
        $won = $this->db->count_all_results(db_prefix().'proposals');
        
        $this->db->where('YEAR(date)', $year);
        $total = $this->db->count_all_results(db_prefix().'proposals');
        
        $rate = ($total > 0) ? ($won / $total) * 100 : 0;
        
        return [
            'win_rate' => $rate,
            'won' => $won,
            'total' => $total
        ];
    }

    public function get_arpa()
    {
        $year = date('Y');
        
        $this->db->select_sum('total');
        $this->db->where('YEAR(date)', $year);
        $this->db->where('status !=', 5); // Exclude Cancelled
        $query = $this->db->get(db_prefix().'invoices')->row();
        $revenue = $query ? $query->total : 0;
        
        // Active customers (those who have invoices this year)
        $this->db->distinct();
        $this->db->select('clientid');
        $this->db->where('YEAR(date)', $year);
        $customers = $this->db->count_all_results(db_prefix().'invoices');
        
        return ($customers > 0) ? $revenue / $customers : 0;
    }

    public function get_pipeline_analysis()
    {
        return $this->get_proposal_conversion_funnel();
    }

    public function get_sales_cycle_analysis()
    {
        $sql = "SELECT AVG(DATEDIFF(i.date, p.date)) as avg_days 
                FROM ".db_prefix()."proposals p 
                JOIN ".db_prefix()."invoices i ON p.invoice_id = i.id 
                WHERE p.invoice_id IS NOT NULL AND p.invoice_id != 0";
        $query = $this->db->query($sql)->row();
        $avg_days = ($query && $query->avg_days) ? round($query->avg_days, 1) : 0;

        return ['avg_days' => $avg_days];
    }

    public function get_customer_overview_data()
    {
        // 1. Retention KPIs
        $kpis = $this->get_customer_retention_kpis();
        
        // 2. CLV Stats
        $this->load->model('advance_analytics/customer_analytics_model');
        $clv = $this->customer_analytics_model->get_clv_distribution();
        
        return [
            'retention_kpis' => $kpis,
            'clv_stats' => $clv
        ];
    }
}
