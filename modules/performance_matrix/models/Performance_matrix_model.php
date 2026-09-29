<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Performance_matrix_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // --- CUSTOMER PERFORMANCE ---

    public function get_customer_matrix_stats($client_id)
    {
        $stats = [
            'total_invoiced' => 0,
            'total_paid' => 0,
            'outstanding_balance' => 0,
            'open_invoices' => 0,
            'open_estimates' => 0,
        ];

        // Total Invoiced & Outstanding
        $this->db->select('SUM(total) as total_invoiced');
        $this->db->where('clientid', $client_id);
        $this->db->where('status !=', 5); // 5 = Cancelled (Invoices_model::STATUS_CANCELLED)
        $invoices = $this->db->get(db_prefix() . 'invoices')->row();

        if ($invoices && $invoices->total_invoiced) {
            $stats['total_invoiced'] = $invoices->total_invoiced;
        }

        // Total Paid
        $this->db->select('SUM(amount) as total_paid');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where(db_prefix() . 'invoices.clientid', $client_id);
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();

        if ($payments && $payments->total_paid) {
            $stats['total_paid'] = $payments->total_paid;
        }

        $stats['outstanding_balance'] = $stats['total_invoiced'] - $stats['total_paid'];

        // Open Invoices
        $this->db->where('clientid', $client_id);
        $this->db->where_not_in('status', [2, 5]); // 2=Paid, 5=Cancelled
        $stats['open_invoices'] = $this->db->count_all_results(db_prefix() . 'invoices');

        // Open Estimates
        $this->db->where('clientid', $client_id);
        $this->db->where_not_in('status', [3, 4]); // 3=Declined, 4=Accepted
        $stats['open_estimates'] = $this->db->count_all_results(db_prefix() . 'estimates');

        return $stats;
    }

    public function get_customer_last_transactions($client_id, $limit = 5)
    {
        $this->db->where('clientid', $client_id);
        $this->db->limit($limit);
        $this->db->order_by('datecreated', 'desc');
        return $this->db->get(db_prefix() . 'invoices')->result_array();
    }

    public function get_customer_item_last_prices($client_id, $item_id, $limit = 5)
    {
        $item_description = null;
        if (!empty($item_id) && is_numeric($item_id)) {
            $item = $this->db->get_where(db_prefix() . 'items', ['id' => $item_id])->row();
            if ($item) {
                $item_description = $item->description;
            }
        }

        if (!$item_description) {
            return [];
        }

        $desc_escaped = $this->db->escape($item_description);
        $client_id_esc = (int)$client_id;
        $limit_esc = (int)$limit;

        $sql = "
            (SELECT rate, qty, date, id as rel_id, 'Invoice' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "invoices ON " . db_prefix() . "invoices.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'invoice' AND clientid = $client_id_esc AND description = $desc_escaped)
            UNION
            (SELECT rate, qty, date, id as rel_id, 'Estimate' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "estimates ON " . db_prefix() . "estimates.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'estimate' AND clientid = $client_id_esc AND description = $desc_escaped)
            UNION
            (SELECT rate, qty, date, id as rel_id, 'Proposal' as source
             FROM " . db_prefix() . "itemable
             JOIN " . db_prefix() . "proposals ON " . db_prefix() . "proposals.id = " . db_prefix() . "itemable.rel_id
             WHERE rel_type = 'proposal' AND rel_id = $client_id_esc AND " . db_prefix() . "proposals.rel_type = 'customer' AND description = $desc_escaped)
            ORDER BY date DESC
            LIMIT $limit_esc
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_detailed_customer_stats($client_id)
    {
        $data = [];
        $client_id = (int)$client_id;

        // 1. Sales Performance Breakdown (Counts & Totals)
        $this->db->select('COUNT(*) as count, SUM(total) as total, AVG(total) as avg');
        $this->db->where('clientid', $client_id);
        $this->db->where('status !=', 5);
        $invoices = $this->db->get(db_prefix() . 'invoices')->row();

        $this->db->select('COUNT(*) as count, SUM(total) as total, AVG(total) as avg');
        $this->db->where('clientid', $client_id);
        $estimates = $this->db->get(db_prefix() . 'estimates')->row();

        $this->db->select('COUNT(*) as count, SUM(total) as total, AVG(total) as avg');
        $this->db->where('rel_id', $client_id);
        $this->db->where('rel_type', 'customer');
        $proposals = $this->db->get(db_prefix() . 'proposals')->row();

        $this->db->select('COUNT(*) as count, SUM(total) as total');
        $this->db->where('clientid', $client_id);
        $credit_notes = $this->db->get(db_prefix() . 'creditnotes')->row();

        $this->db->select('COUNT(*) as count, SUM(amount) as total');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where(db_prefix() . 'invoices.clientid', $client_id);
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();

        $data['breakdown'] = [
            'proposals' => $proposals,
            'estimates' => $estimates,
            'invoices'  => $invoices,
            'credit_notes' => $credit_notes,
            'payments'  => $payments
        ];

        // 2. Net Metrics
        $gross_sales = $invoices->total ?: 0;
        $credit_value = $credit_notes->total ?: 0;
        $net_sales = $gross_sales - $credit_value;
        $total_paid = $payments->total ?: 0;

        $data['net_metrics'] = [
            'gross_sales' => $gross_sales,
            'credit_value' => $credit_value,
            'net_sales' => $net_sales,
            'collection_rate' => $net_sales > 0 ? ($total_paid / $net_sales) * 100 : 0,
            'credit_impact' => $gross_sales > 0 ? ($credit_value / $gross_sales) * 100 : 0
        ];

        // 3. Profitability (Approximate based on item rate vs purchase_price)
        $this->db->select('SUM(it.qty * (it.rate - i.purchase_price)) as gross_profit');
        $this->db->from(db_prefix() . 'itemable as it');
        $this->db->join(db_prefix() . 'items as i', 'i.description = it.description');
        $this->db->join(db_prefix() . 'invoices as inv', 'inv.id = it.rel_id');
        $this->db->where('it.rel_type', 'invoice');
        $this->db->where('inv.clientid', $client_id);
        $this->db->where('inv.status !=', 5);
        $profit_row = $this->db->get()->row();
        
        $gross_profit = $profit_row->gross_profit ?: 0;
        $net_profit = $gross_profit - $credit_value;

        $data['profitability'] = [
            'gross_profit' => $gross_profit,
            'credit_loss' => $credit_value, // Assuming credit notes are loss for simplicity
            'net_profit' => $net_profit,
            'margin_percent' => $net_sales > 0 ? ($net_profit / $net_sales) * 100 : 0
        ];

        // 4. Customer 360
        $this->db->select('MIN(date) as first_date, MAX(date) as last_date');
        $this->db->where('clientid', $client_id);
        $dates = $this->db->get(db_prefix() . 'invoices')->row();
        
        $tenure_days = 0;
        $recency_days = 0;
        if ($dates && $dates->first_date) {
            $tenure_days = floor((time() - strtotime($dates->first_date)) / (60 * 60 * 24));
            $recency_days = floor((time() - strtotime($dates->last_date)) / (60 * 60 * 24));
        }

        $this->db->where('userid', $client_id);
        $interaction_count = $this->db->count_all_results(db_prefix() . 'tickets');

        $data['customer_360'] = [
            'clv' => $net_sales,
            'avg_order_value' => ($invoices->count > 0) ? ($net_sales / $invoices->count) : 0,
            'tenure' => $tenure_days,
            'recency' => $recency_days,
            'interaction_score' => $interaction_count
        ];

        // 5. Project Performance
        $this->db->where('clientid', $client_id);
        $data['projects'] = [
            'total' => $this->db->count_all_results(db_prefix() . 'projects'),
            'completed' => $this->db->where(['clientid' => $client_id, 'status' => 4])->count_all_results(db_prefix() . 'projects')
        ];

        // 6. Item Stats
        $this->db->select('COUNT(*) as total_items, COUNT(DISTINCT description) as unique_items');
        $this->db->from(db_prefix() . 'itemable as it');
        $this->db->join(db_prefix() . 'invoices as inv', 'inv.id = it.rel_id');
        $this->db->where('it.rel_type', 'invoice');
        $this->db->where('inv.clientid', $client_id);
        $item_stats = $this->db->get()->row();
        
        $data['item_stats'] = $item_stats;

        // 7. Scores (Heuristic)
        $sales_health = 50 + ($data['net_metrics']['collection_rate'] / 2) - ($data['net_metrics']['credit_impact']);
        $cust_perf = 40 + (min(60, $data['customer_360']['clv'] / 1000) * 10);
        
        $data['scores'] = [
            'sales_health' => max(0, min(100, $sales_health)),
            'customer_perf' => max(0, min(100, $cust_perf)),
            'product_perf' => max(0, min(100, $sales_health * 0.8)) // Dummy relation
        ];

        return $data;
    }

    // --- VENDOR PERFORMANCE ---

    public function get_vendor_performance_stats($vendor_id, $period = 'all_time')
    {
        $this->load->model('expenses_model');
        
        $apply_filter = function($db, $date_col) use ($period) {
            switch ($period) {
                case 'this_month':
                    $db->where('MONTH('.$date_col.')', date('m'));
                    $db->where('YEAR('.$date_col.')', date('Y'));
                    break;
                case 'last_month':
                    $db->where('MONTH('.$date_col.')', date('m', strtotime('last month')));
                    $db->where('YEAR('.$date_col.')', date('Y', strtotime('last month')));
                    break;
                case 'this_year':
                    $db->where('YEAR('.$date_col.')', date('Y'));
                    break;
                case 'last_year':
                    $db->where('YEAR('.$date_col.')', date('Y', strtotime('-1 year')));
                    break;
                case 'last_3_months':
                    $db->where($date_col.' >=', date('Y-m-d', strtotime('-3 months')));
                    break;
                case 'last_6_months':
                    $db->where($date_col.' >=', date('Y-m-d', strtotime('-6 months')));
                    break;
                case 'last_12_months':
                    $db->where($date_col.' >=', date('Y-m-d', strtotime('-12 months')));
                    break;
            }
        };

        $has_returns_col = $this->db->field_exists('returns', db_prefix() . 'pur_orders');
        
        $this->db->select_sum('total');
        $this->db->where('vendor', $vendor_id);
        $this->db->where('approve_status', 2);
        if($has_returns_col) $this->db->where('returns', 0);
        $apply_filter($this->db, 'order_date');
        $gross_spend = $this->db->get(db_prefix() . 'pur_orders')->row()->total ?: 0;

        $returns_value = 0;
        if($has_returns_col){
            $this->db->select_sum('total');
            $this->db->where('vendor', $vendor_id);
            $this->db->where('approve_status', 2);
            $this->db->where('returns', 1);
            $apply_filter($this->db, 'order_date');
            $returns_value = $this->db->get(db_prefix() . 'pur_orders')->row()->total ?: 0;
        }

        $net_spend = $gross_spend - $returns_value;

        // Spend Growth (Current vs Last Year)
        $this->db->select_sum('total');
        $this->db->where('vendor', $vendor_id);
        $this->db->where('approve_status', 2);
        $this->db->where('YEAR(order_date)', date('Y'));
        if($has_returns_col) $this->db->where('returns', 0);
        $gross_this_year = $this->db->get(db_prefix() . 'pur_orders')->row()->total ?: 0;
        
        $this->db->select_sum('total');
        $this->db->where('vendor', $vendor_id);
        $this->db->where('approve_status', 2);
        $this->db->where('YEAR(order_date)', date('Y') - 1);
        if($has_returns_col) $this->db->where('returns', 0);
        $gross_last_year = $this->db->get(db_prefix() . 'pur_orders')->row()->total ?: 0;
        
        $spend_growth = ($gross_last_year > 0) ? (($gross_this_year - $gross_last_year) / $gross_last_year) * 100 : 0;

        $this->db->select_sum('amount');
        $this->db->from(db_prefix() . 'pur_order_payment');
        $this->db->join(db_prefix() . 'pur_orders', db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order');
        $this->db->where(db_prefix() . 'pur_orders.vendor', $vendor_id);
        $apply_filter($this->db, db_prefix() . 'pur_order_payment.date');
        $total_paid = $this->db->get()->row()->amount ?: 0;
        
        $balance = $net_spend - $total_paid;
        $payment_rate = ($net_spend > 0) ? ($total_paid / $net_spend) * 100 : 0;

        // --- 3. PIPELINE & CONTRACTS ---
        $this->db->where('vendor', $vendor_id);
        $apply_filter($this->db, 'date');
        $total_estimates = $this->db->count_all_results(db_prefix().'pur_estimates');
        
        $this->db->select_sum('total');
        $this->db->where('vendor', $vendor_id);
        $apply_filter($this->db, 'date');
        $val_estimates = $this->db->get(db_prefix().'pur_estimates')->row()->total ?: 0;
        
        $this->db->where('vendor', $vendor_id);
        $this->db->where('estimate >', 0);
        $this->db->where('approve_status', 2);
        $apply_filter($this->db, 'order_date');
        $converted_orders = $this->db->count_all_results(db_prefix().'pur_orders');
        
        $conversion_rate = ($total_estimates > 0) ? ($converted_orders / $total_estimates) * 100 : 0;

        $this->db->where('vendor', $vendor_id);
        $apply_filter($this->db, 'start_date');
        $total_contracts = $this->db->count_all_results(db_prefix() . 'pur_contracts');

        $this->db->select_sum('amount');
        $this->db->where('vendor', $vendor_id);
        $apply_filter($this->db, 'date');
        $total_expenses = $this->db->get(db_prefix() . 'expenses')->row()->amount ?: 0;

        // --- 4. ITEM STATS ---
        $this->db->select('item_code, description, SUM(quantity) as total_qty, SUM('.db_prefix().'pur_order_detail.total) as total_val');
        $this->db->from(db_prefix() . 'pur_order_detail');
        $this->db->join(db_prefix() . 'pur_orders', db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_detail.pur_order');
        $this->db->where(db_prefix() . 'pur_orders.vendor', $vendor_id);
        $this->db->where(db_prefix() . 'pur_orders.approve_status', 2); 
        $apply_filter($this->db, db_prefix() . 'pur_orders.order_date');
        $this->db->group_by('item_code, description'); 
        $this->db->order_by('total_val', 'DESC');
        $this->db->limit(10);
        $top_items = $this->db->get()->result_array();
        
        // --- 5. COMPOSITE SCORES ---
        $return_rate = ($gross_spend > 0) ? ($returns_value / $gross_spend) * 100 : 0;
        $quality_score = max(0, 100 - ($return_rate * 5));
        $reliability_score = min(100, $payment_rate > 0 ? 80 + ($payment_rate/5) : 80); 
        $purchasing_score = ($quality_score + $reliability_score) / 2;

        return [
            'scores' => [
                'health' => $quality_score,
                'performance' => $purchasing_score,
                'reliability' => $reliability_score
            ],
            'spend' => [
                'gross' => $gross_spend,
                'returns' => $returns_value,
                'net' => $net_spend,
                'growth' => $spend_growth,
                'payment_rate' => $payment_rate,
                'balance' => $balance
            ],
            'pipeline' => [
                'estimates_count' => $total_estimates,
                'estimates_val' => $val_estimates,
                'converted_orders' => $converted_orders,
                'conversion_rate' => $conversion_rate,
                'contracts' => $total_contracts
            ],
            'expenses' => $total_expenses,
            'top_items' => $top_items
        ];
    }

    // --- ITEM PERFORMANCE ---

    public function get_item_performance_data($commodity_id, $type, $year)
    {
        $item = $this->db->get_where(db_prefix() . 'items', ['id' => $commodity_id])->row();
        if (!$item) {
            return ['amounts' => array_fill(0, 12, 0), 'trend' => array_fill(0, 12, 0)];
        }

        $amounts = array_fill(0, 12, 0);

        if ($type == 'sales') {
            $this->db->select('MONTH(i.date) as month, SUM(it.qty * it.rate) as total_amount');
            $this->db->from(db_prefix() . 'itemable as it');
            $this->db->join(db_prefix() . 'invoices as i', 'i.id = it.rel_id');
            $this->db->where('it.rel_type', 'invoice');
            $this->db->where('i.status !=', 5);
            $this->db->where('it.description', $item->description);
            $this->db->where('YEAR(i.date)', $year);
            $this->db->group_by('MONTH(i.date)');
            $result = $this->db->get()->result_array();

            foreach ($result as $row) {
                $amounts[$row['month'] - 1] = (float)$row['total_amount'];
            }
        } elseif ($type == 'purchases') {
            if ($this->db->table_exists(db_prefix() . 'pur_orders')) {
                $this->db->select('MONTH(po.order_date) as month, SUM(it.qty * it.rate) as total_amount');
                $this->db->from(db_prefix() . 'itemable as it');
                $this->db->join(db_prefix() . 'pur_orders as po', 'po.id = it.rel_id');
                $this->db->where('it.rel_type', 'pur_order');
                $this->db->where('po.approve_status', 2);
                $this->db->where('it.description', $item->description);
                $this->db->where('YEAR(po.order_date)', $year);
                $this->db->group_by('MONTH(po.order_date)');
                $result = $this->db->get()->result_array();

                foreach ($result as $row) {
                    $amounts[$row['month'] - 1] = (float)$row['total_amount'];
                }
            }
        }

        // Trend calculation
        $n = 12;
        $x = range(1, 12);
        $y = $amounts;
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        $sum_xy = 0; $sum_xx = 0;
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += $x[$i] * $y[$i];
            $sum_xx += $x[$i] * $x[$i];
        }
        $denominator = ($n * $sum_xx) - ($sum_x * $sum_x);
        $trend = array_fill(0, 12, 0);
        if ($denominator != 0) {
            $m = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
            $b = ($sum_y - ($m * $sum_x)) / $n;
            for ($i = 1; $i <= 12; $i++) {
                $trend[$i-1] = max(0, ($m * $i) + $b);
            }
        }

        return ['amounts' => $amounts, 'trend' => $trend];
    }
}
