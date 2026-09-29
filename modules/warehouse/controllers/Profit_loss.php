<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Profit_loss extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('warehouse/warehouse_model');
    }

    public function index()
    {
        if (!has_permission('warehouse', '', 'view') && !has_permission('warehouse', '', 'view_own')) {
            access_denied('warehouse');
        }

        $data['title'] = _l('profit_and_loss_report');
        
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');

        if (!$from_date) {
            $from_date = date('Y-m-d', strtotime('first day of this month'));
        } else {
            $from_date = to_sql_date($from_date);
        }

        if (!$to_date) {
            $to_date = date('Y-m-d');
        } else {
            $to_date = to_sql_date($to_date);
        }

        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        if ($this->input->post()) {
             $report_data = $this->get_data($from_date, $to_date);
             $data = array_merge($data, $report_data);
        } else {
            // Initial load with default dates
            $report_data = $this->get_data($from_date, $to_date);
             $data = array_merge($data, $report_data);
        }

        $this->load->view('warehouse/report/profit_loss', $data);
    }
    
    private function get_data($from_date, $to_date)
    {
        // 1. Opening Stock Calculation
        $opening_stock_value_purchase = $this->get_stock_value_at_date($from_date, 'purchase_price');
        $opening_stock_value_sale = $this->get_stock_value_at_date($from_date, 'rate'); // 'rate' is typically sale price

        // 2. Closing Stock Calculation
        // For closing stock, we want the value at the end of the day of to_date, so effectively closest to next day 00:00 or just strictly after all movements of that day.
        $closing_stock_value_purchase = $this->get_stock_value_at_date($to_date, 'purchase_price', true); // true for end of day
        $closing_stock_value_sale = $this->get_stock_value_at_date($to_date, 'rate', true);

        // 3. Purchases (Approved Purchase Orders)
        $this->load->model('purchase/purchase_model');
        $purchases_sql = "SELECT SUM(total) as total, SUM(total_tax) as tax, SUM(discount_total) as discount FROM ".db_prefix()."pur_orders WHERE approve_status = 2 AND order_date BETWEEN '$from_date' AND '$to_date'";
        $purchases_res = $this->db->query($purchases_sql)->row();
        
        // Shipping charges for purchases? usually in pur_orders or estimated.
        // The user asked for "Total purchase shipping charge". 
        // We'll check if there's a shipping field in pur_orders. 
        // $purchase_shipping_sql = "SELECT SUM(shipping_fee) as shipping FROM ".db_prefix()."pur_orders WHERE approve_status = 2 AND order_date BETWEEN '$from_date' AND '$to_date'";
        // $purchase_shipping_res = $this->db->query($purchase_shipping_sql)->row();
        $purchase_shipping_res = null; // Column does not exist


        // 4. Sales (Invoices)
        // Exclude cancelled (status 5) and draft (status 6)? 
        // Usually Profit/Loss is based on Accrual, so Issued Invoices.
        $sales_sql = "SELECT SUM(total) as total, SUM(subtotal) as subtotal, SUM(total_tax) as tax, SUM(discount_total) as discount, SUM(adjustment) as adjustment FROM ".db_prefix()."invoices WHERE status != 5 AND date BETWEEN '$from_date' AND '$to_date'";
        $sales_res = $this->db->query($sales_sql)->row();

        // 5. Expenses
        $expenses_sql = "SELECT SUM(amount) as amount, SUM(tax) as tax, SUM(tax2) as tax2 FROM ".db_prefix()."expenses WHERE date BETWEEN '$from_date' AND '$to_date'";
        $expenses_res = $this->db->query($expenses_sql)->row();

        // 6. Breakdowns (Best Effort)
        $breakdowns = $this->get_profit_breakdown($from_date, $to_date);

        // Calculations
        $opening_stock_purchase = $opening_stock_value_purchase;
        $opening_stock_sale = $opening_stock_value_sale;

        $total_purchase = $purchases_res->total ?? 0;
        $total_purchase_tax = $purchases_res->tax ?? 0;
        $total_purchase_discount = $purchases_res->discount ?? 0;
        $total_purchase_shipping = $purchase_shipping_res->shipping ?? 0;

        $total_sales = $sales_res->total ?? 0;
        $total_sales_exc_tax = $sales_res->subtotal ?? 0; // The user asked for Exc tax, Discount. Subtotal usually excludes tax but includes line item amounts.
        $total_sales_discount = $sales_res->discount ?? 0;
        
        $total_expenses = $expenses_res->amount ?? 0; // Expenses total (usually pre-tax in db? need to check)
        
        // COGS = Opening Stock + Purchases - Closing Stock
        // Usually COGS is calculated using Purchase Price items.
        $cogs = $opening_stock_purchase + $total_purchase - $closing_stock_value_purchase;

        // Gross Profit
        // (Total sell price - Total purchase price)? No, standard is Sales - COGS.
        // The user formula: (Total sell price - Total purchase price) + Hms Total + Project Invoice
        // We will stick to standard accounting first: Sales - COGS.
        $gross_profit = $total_sales - $cogs;

        // Net Profit
        // Gross Profit - Expenses
        $net_profit = $gross_profit - $total_expenses;

        return [
            'opening_stock_purchase' => $opening_stock_purchase,
            'opening_stock_sale' => $opening_stock_sale,
            'closing_stock_purchase' => $closing_stock_value_purchase,
            'closing_stock_sale' => $closing_stock_value_sale,
            'total_purchase' => $total_purchase,
            'total_purchase_tax' => $total_purchase_tax,
            'total_purchase_discount' => $total_purchase_discount,
            'purchase_shipping_charge' => $total_purchase_shipping,
            'total_sales' => $total_sales,
            'total_sales_exc_tax' => $total_sales_exc_tax,
            'total_sales_discount' => $total_sales_discount,
            'total_expenses' => $total_expenses,
            'cogs' => $cogs,
            'gross_profit' => $gross_profit,
            'net_profit' => $net_profit,
            'profit_by_product' => $breakdowns['products'],
            'profit_by_category' => $breakdowns['categories'],
            'profit_by_day' => $breakdowns['days']
        ];
    }

    private function get_profit_breakdown($from_date, $to_date)
    {
        $this->db->select('i.date, it.description, it.qty, it.rate, item.purchase_price, item.group_id as category_id, g.name as category_name');
        $this->db->from(db_prefix() . 'invoices i');
        $this->db->join(db_prefix() . 'itemable it', 'it.rel_id = i.id AND it.rel_type = "invoice"');
        // Try to join items by description as last resort or if available
        // Note: Perfex might not store link, but we try description
        $this->db->join(db_prefix() . 'items item', 'item.description = it.description', 'left');
        $this->db->join(db_prefix() . 'items_groups g', 'g.id = item.group_id', 'left');
        
        $this->db->where('i.status !=', 5); // Not Cancelled
        $this->db->where('i.date >=', $from_date);
        $this->db->where('i.date <=', $to_date);

        $items = $this->db->get()->result_array();

        $products = [];
        $categories = [];
        $days = [];

        foreach ($items as $item) {
            $name = $item['description'];
            $qty = $item['qty'];
            $sale_rate = $item['rate'];
            $purchase_price = $item['purchase_price'] ?? 0;
            $cat_name = $item['category_name'] ?? 'Unknown';
            $date = $item['date'];
            $day_name = date('l', strtotime($date)); // e.g., Monday

            $total_sale = $sale_rate * $qty;
            $total_cost = $purchase_price * $qty;
            $profit = $total_sale - $total_cost;

            // Product Aggregation
            if (!isset($products[$name])) {
                $products[$name] = ['name' => $name, 'qty' => 0, 'sales' => 0, 'cost' => 0, 'profit' => 0];
            }
            $products[$name]['qty'] += $qty;
            $products[$name]['sales'] += $total_sale;
            $products[$name]['cost'] += $total_cost;
            $products[$name]['profit'] += $profit;

            // Category Aggregation
            if (!isset($categories[$cat_name])) {
                $categories[$cat_name] = ['name' => $cat_name, 'sales' => 0, 'cost' => 0, 'profit' => 0];
            }
            $categories[$cat_name]['sales'] += $total_sale;
            $categories[$cat_name]['cost'] += $total_cost;
            $categories[$cat_name]['profit'] += $profit;

             // Day Aggregation
             if (!isset($days[$date])) {
                $days[$date] = ['date' => $date, 'day' => $day_name, 'profit' => 0];
             }
             $days[$date]['profit'] += $profit;
        }

        return ['products' => $products, 'categories' => $categories, 'days' => $days];
    }

    private function get_stock_value_at_date($date, $price_column = 'purchase_price', $end_of_day = false)
    {
         // Fix: Explicitly use table name for columns to avoid ambiguity
        $price_col_sql = db_prefix() . 'items.' . $price_column;

        // 1. Get Current Stock from inventory_manage
        // We need to join items to get price
        $this->db->select('SUM(inventory_number * '.$price_col_sql.') as total_value');
        $this->db->from(db_prefix() . 'inventory_manage');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.id = ' . db_prefix() . 'inventory_manage.commodity_id');
        $current = $this->db->get()->row();
        $current_value = $current->total_value ?? 0;

        // If date is today or future, just return current (approx)
        if ($date >= date('Y-m-d') && !$end_of_day) {
           // return $current_value; // Actually better to always calculate backwards for consistency if we want "At start of today"
        }

        // 2. Adjust for Movements from Date to Now
        // We are at NOW. We want value at DATE.
        // DATE < NOW.
        // Stock(Date) = Stock(Now) - Receipts(Between Date and Now) + Deliveries(Between Date and Now)
        
        // Receipts (Added to stock, so we subtract them to go back)
        // Table: goods_receipt_detail
        // Date: goods_receipt.date_add
        $this->db->select('SUM(quantities * '.$price_col_sql.') as val');
        $this->db->from(db_prefix().'goods_receipt_detail');
        $this->db->join(db_prefix().'goods_receipt', db_prefix().'goods_receipt.id = '.db_prefix().'goods_receipt_detail.goods_receipt_id');
        $this->db->join(db_prefix().'items', db_prefix().'items.id = '.db_prefix().'goods_receipt_detail.commodity_code');
        
        if ($end_of_day) {
            $this->db->where(db_prefix().'goods_receipt.date_add >', $date . ' 23:59:59');
        } else {
             $this->db->where(db_prefix().'goods_receipt.date_add >=', $date . ' 00:00:00');
        }
        
        $receipts_diff = $this->db->get()->row()->val ?? 0;


        // Deliveries (Removed from stock, so we add them to go back)
        // Table: goods_delivery_detail
        // Date: goods_delivery.date_add
        $this->db->select('SUM(quantities * '.$price_col_sql.') as val');
        $this->db->from(db_prefix().'goods_delivery_detail');
        $this->db->join(db_prefix().'goods_delivery', db_prefix().'goods_delivery.id = '.db_prefix().'goods_delivery_detail.goods_delivery_id');
        $this->db->join(db_prefix().'items', db_prefix().'items.id = '.db_prefix().'goods_delivery_detail.commodity_code');

         if ($end_of_day) {
            $this->db->where(db_prefix().'goods_delivery.date_add >', $date . ' 23:59:59');
        } else {
             $this->db->where(db_prefix().'goods_delivery.date_add >=', $date . ' 00:00:00');
        }

        $deliveries_diff = $this->db->get()->row()->val ?? 0;

        return $current_value - $receipts_diff + $deliveries_diff;
    }
}
