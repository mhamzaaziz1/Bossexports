<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Daily_sales_report_model extends App_Model
{
    /**
     * Last Optimetriks auth/config error from send_to_optimetriks()
     */
    public $optimetriks_error = '';

    public function __construct()
    {
        parent::__construct();
        
        if (!$this->db->table_exists(db_prefix() . 'daily_sales_report_logs')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'daily_sales_report_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `date_sent` datetime NOT NULL,
              `email_to` varchar(255) NOT NULL,
              `records_count` int(11) NOT NULL DEFAULT 0,
              `csv_content` longtext,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }

        if ($this->db->table_exists(db_prefix() . 'daily_sales_reports') && !$this->db->field_exists('supplier_ids', db_prefix() . 'daily_sales_reports')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'daily_sales_reports` ADD `supplier_ids` LONGTEXT NULL AFTER `items`');
        }
    }

    /**
     * Item ID => supplier ID map saved on the report
     */
    public function get_report_supplier_ids($report)
    {
        if (empty($report['supplier_ids'])) {
            return [];
        }
        $map = json_decode($report['supplier_ids'], true);
        return is_array($map) ? $map : [];
    }

    /**
     * SKU ID used in reports: the supplier ID when set, otherwise the default BOSS-<item id>
     */
    public function resolve_sku_id($item_id, $supplier_ids = [])
    {
        if (isset($supplier_ids[$item_id]) && trim($supplier_ids[$item_id]) !== '') {
            return trim($supplier_ids[$item_id]);
        }
        return 'BOSS-' . $item_id;
    }

    public function get_reports($where = [])
    {
        $this->db->where($where);
        return $this->db->get(db_prefix() . 'daily_sales_reports')->result_array();
    }

    public function get_report($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'daily_sales_reports')->row_array();
    }

    public function add_report($data)
    {
        $this->db->insert(db_prefix() . 'daily_sales_reports', $data);
        return $this->db->insert_id();
    }

    public function update_report($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'daily_sales_reports', $data);
        return $this->db->affected_rows() > 0;
    }

    public function delete_report($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'daily_sales_reports');
        return $this->db->affected_rows() > 0;
    }


    public function send_manual_report($report_id, $start_date = '', $end_date = '')
    {
        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }
        if ($end_date == '') {
            $end_date = $start_date;
        }
        return $this->process_and_send_report($report_id, $start_date, $end_date, false);
    }

    public function send_cron_report($report_id)
    {
        $today = date('Y-m-d');
        $report = $this->get_report($report_id);
        if (!$report) return false;
        
        $frequency = isset($report['frequency']) ? $report['frequency'] : 'daily';
        $last_run = $report['last_run'];
        
        if ($last_run) {
            $days_since = (strtotime($today) - strtotime($last_run)) / (60 * 60 * 24);
            if ($frequency == 'daily' && $days_since < 1) {
                return false;
            }
            if ($frequency == 'weekly' && $days_since < 7) {
                return false;
            }
            if ($frequency == 'monthly' && $days_since < 30) {
                return false;
            }
        }

        $end_date = $today;
        if ($frequency == 'weekly') {
            $start_date = date('Y-m-d', strtotime('-6 days', strtotime($end_date)));
        } elseif ($frequency == 'monthly') {
            $start_date = date('Y-m-d', strtotime('-29 days', strtotime($end_date)));
        } else {
            $start_date = $end_date;
        }
        
        return $this->process_and_send_report($report_id, $start_date, $end_date, true);
    }

    private function process_and_send_report($report_id, $start_date, $end_date, $is_cron = false)
    {
        $report = $this->get_report($report_id);
        if (!$report) return false;
        
        $emails_option = $report['emails'];
        $items_option = $report['items'];

        if (empty($emails_option) || empty($items_option) || $items_option == '[]') {
            return false;
        }

        $emails = explode(',', $emails_option);
        $selected_items = json_decode($items_option, true);
        $supplier_ids = $this->get_report_supplier_ids($report);

        $attachments = [];
        $total_records = 0;
        $all_csv_content = "";
        $opti_results = [];
        $opti_errors = [];

        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $curr_date = date('Y-m-d', $current);
            $aggregated = $this->get_today_sales_data($selected_items, $curr_date, $curr_date, $supplier_ids);
            
            if (count($aggregated) > 0) {
                $csv_data = _l('daily_sales_report_date') . "," . _l('daily_sales_report_sku') . "," . _l('daily_sales_report_product_name') . "," . _l('daily_sales_report_format') . "," . _l('daily_sales_report_quantity_sold') . "," . _l('daily_sales_report_unit_price') . "," . _l('daily_sales_report_total_amount') . "," . _l('daily_sales_report_visit_id') . "," . _l('daily_sales_report_customer_id') . "," . _l('daily_sales_report_customer') . "," . _l('daily_sales_report_phone') . "," . _l('daily_sales_report_channel') . "," . _l('daily_sales_report_shop_class') . "," . _l('daily_sales_report_region') . "," . _l('daily_sales_report_town') . "," . _l('daily_sales_report_creator') . "\r\n";
                
                $customers = [];
                $products = [];

                foreach ($aggregated as $row) {
                    $csv_data .= '"' . $row['date'] . '","' . str_replace('"', '""', $row['sku']) . '","' . str_replace('"', '""', $row['name']) . '","' . str_replace('"', '""', $row['format']) . '",' . $row['qty'] . ',' . number_format($row['unit_price'], 2, '.', '') . ',' . number_format($row['total'], 2, '.', '') . ',"' . str_replace('"', '""', $row['invoice_no']) . '","' . $row['customer_id'] . '","' . str_replace('"', '""', $row['customer']) . '","' . str_replace('"', '""', $row['phone']) . '","' . str_replace('"', '""', $row['channel']) . '","' . str_replace('"', '""', $row['shop_class']) . '","' . str_replace('"', '""', $row['region']) . '","' . str_replace('"', '""', $row['town']) . '","' . str_replace('"', '""', $row['creator']) . '"' . "\r\n";
                    
                    if (!empty($row['customer_id'])) {
                        $customers[$row['customer_id']] = $row;
                    }
                    if (!empty($row['sku']) || !empty($row['name'])) {
                        $products[$row['sku'] . '|' . $row['name']] = $row;
                    }
                }
                $attachments[] = [
                    'content' => $csv_data,
                    'filename' => 'sales_report_' . $curr_date . '.csv',
                    'is_zip' => false
                ];

                // Customers CSV
                $cust_csv = "Customer ID,Customer Name,Phone,Shop Class,Region,Town\r\n";
                foreach ($customers as $c) {
                    $cust_csv .= '"' . $c['customer_id'] . '","' . str_replace('"', '""', $c['customer']) . '","' . str_replace('"', '""', $c['phone']) . '","' . str_replace('"', '""', $c['shop_class']) . '","' . str_replace('"', '""', $c['region']) . '","' . str_replace('"', '""', $c['town']) . "\"\r\n";
                }
                $attachments[] = [
                    'content' => $cust_csv,
                    'filename' => 'customers_' . $curr_date . '.csv',
                    'is_zip' => false
                ];

                // Products CSV
                $prod_csv = "SKU,Product Name,Format,Unit Price\r\n";
                foreach ($products as $p) {
                    $prod_csv .= '"' . str_replace('"', '""', $p['sku']) . '","' . str_replace('"', '""', $p['name']) . '","' . str_replace('"', '""', $p['format']) . '",' . number_format($p['unit_price'], 2, '.', '') . "\r\n";
                }
                $attachments[] = [
                    'content' => $prod_csv,
                    'filename' => 'products_' . $curr_date . '.csv',
                    'is_zip' => false
                ];

                $total_records += count($aggregated);
                $all_csv_content .= $csv_data . "\n";
            }

            // Send to Optimetriks API for this date
            $opti_zips = $this->send_to_optimetriks($curr_date, $selected_items, $supplier_ids);
            $opti_results = array_merge($opti_results, $opti_zips);
            if ($this->optimetriks_error) {
                $opti_errors[] = $curr_date . ': ' . $this->optimetriks_error;
            }
            foreach ($opti_zips as $z) {
                if (empty($z['path'])) {
                    continue;
                }
                $attachments[] = [
                    'content' => file_get_contents($z['path']),
                    'filename' => $z['filename'],
                    'is_zip' => true
                ];
                // Clean up temp file
                $this->cleanup_optimetriks_zip($z['path']);
            }

            $current = strtotime('+1 day', $current);
        }

        // Log what went to Optimetriks regardless of whether the email goes out
        $this->log_optimetriks_send($report, $start_date, $end_date, $opti_results, $opti_errors);

        if (empty($attachments) || $total_records == 0) {
            if ($is_cron) {
                $this->update_report($report_id, ['last_run' => $end_date]);
            }
            return false;
        }

        $this->load->config('email');
        $this->load->library('email');

        $date_label = $start_date == $end_date ? $start_date : $start_date . ' to ' . $end_date;
        $email_subject = sprintf(_l('daily_sales_report_email_subject'), $date_label);
        $email_body = sprintf(_l('daily_sales_report_email_body'), $date_label);

        $sent = false;
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email)) {
                $this->email->clear(true);
                $this->email->from(get_option('smtp_email'), get_option('companyname'));
                $this->email->to($email);
                $this->email->subject($email_subject);
                $this->email->message($email_body);
                foreach ($attachments as $att) {
                    if (isset($att['is_zip']) && $att['is_zip']) {
                        $this->email->attach($att['content'], 'attachment', $att['filename'], 'application/zip');
                    } else {
                        $this->email->attach($att['content'], 'attachment', $att['filename'], 'text/csv');
                    }
                }
                if($this->email->send()) {
                    $sent = true;
                }
            }
        }

        if ($sent || true) {
            $this->db->insert(db_prefix() . 'daily_sales_report_logs', [
                'report_id' => $report_id,
                'report_name' => $report['name'],
                'date_sent' => date('Y-m-d H:i:s'),
                'email_to' => $emails_option,
                'records_count' => $total_records,
                'csv_content' => $all_csv_content
            ]);

            if ($is_cron) {
                $this->update_report($report_id, ['last_run' => $end_date]);
            }
            return true;
        }

        return false;
    }

    public function get_today_sales_data($selected_item_ids = [], $start_date = '', $end_date = '', $supplier_ids = [])
    {
        if (empty($selected_item_ids)) {
            return [];
        }

        if ($start_date == '') {
            $start_date = date('Y-m-d');
        }
        if ($end_date == '') {
            $end_date = $start_date;
        }

        // Fetch the item descriptions and SKU to match against itemable
        $this->db->select('id, description, commodity_code');
        $this->db->from(db_prefix() . 'items');
        $this->db->where_in('id', $selected_item_ids);
        $items = $this->db->get()->result_array();
        
        if(empty($items)) {
            return [];
        }
        
        $item_descriptions = [];
        $items_sku_map = [];
        
        // Populate tracking list
        foreach($items as $item) {
            $desc = $item['description'];
            if(!empty($desc)) {
                $item_descriptions[] = $desc;
                $has_supplier_id = isset($supplier_ids[$item['id']]) && trim($supplier_ids[$item['id']]) !== '';
                $items_sku_map[$desc] = $has_supplier_id ? trim($supplier_ids[$item['id']]) : $item['commodity_code'];
            }
        }

        $final_results = [];
        $sold_products = [];

        if(!empty($item_descriptions)) {
            $this->db->select(
                db_prefix() . 'invoices.id as invoice_id, ' . 
                db_prefix() . 'invoices.date, ' . 
                db_prefix() . 'clients.company as customer_name, ' . 
                db_prefix() . 'clients.userid as customer_id, ' .
                db_prefix() . 'clients.phonenumber, ' .
                db_prefix() . 'clients.pgroup as shop_class, ' .
                db_prefix() . 'clients.state, ' .
                db_prefix() . 'clients.city, ' .
                db_prefix() . 'clients.latitude, ' .
                db_prefix() . 'clients.longitude, ' .
                db_prefix() . 'countries.short_name as region, ' .
                db_prefix() . 'itemable.description, ' . 
                db_prefix() . 'itemable.unit, ' .
                db_prefix() . 'itemable.qty, ' . 
                db_prefix() . 'itemable.rate, ' . 
                db_prefix() . 'staff.firstname, ' . 
                db_prefix() . 'staff.lastname'
            );
            $this->db->from(db_prefix() . 'itemable');
            $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id');
            $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');
            $this->db->join(db_prefix() . 'countries', db_prefix() . 'countries.country_id = ' . db_prefix() . 'clients.country', 'left');
            $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'invoices.addedfrom', 'left');
            
            $this->db->where_in(db_prefix() . 'itemable.description', $item_descriptions);
            $this->db->where(db_prefix() . 'itemable.rel_type', 'invoice');
            $this->db->where('DATE(' . db_prefix() . 'invoices.date) >=', $start_date);
            $this->db->where('DATE(' . db_prefix() . 'invoices.date) <=', $end_date);
            $this->db->where(db_prefix() . 'invoices.status !=', 5);
            $this->db->where(db_prefix() . 'invoices.status !=', 6);
            
            $sales = $this->db->get()->result_array();
    
            $grouped = [];
            foreach ($sales as $sale) {
                $inv_no = format_invoice_number($sale['invoice_id']);
                $customer = $sale['customer_name'] ? $sale['customer_name'] : 'Unknown';
                $desc = $sale['description'];
                $creator = $sale['firstname'] ? trim($sale['firstname'] . ' ' . $sale['lastname']) : 'System';
                
                $sku = isset($items_sku_map[$desc]) ? $items_sku_map[$desc] : '';
                $town = trim($sale['state'] . ' ' . $sale['city']);
                
                $key = $inv_no . '|' . $desc;
                if(!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'date' => $sale['date'],
                        'invoice_no' => $inv_no,
                        'customer' => $customer,
                        'name' => $desc,
                        'sku' => $sku,
                        'creator' => $creator,
                        'unit_price' => $sale['rate'],
                        'qty' => 0,
                        'total' => 0,
                        'format' => $sale['unit'],
                        'customer_id' => $sale['customer_id'],
                        'phone' => $sale['phonenumber'],
                        'channel' => 'Sales',
                        'shop_class' => $sale['shop_class'],
                        'region' => $sale['region'],
                        'town' => $town,
                        'latitude' => $sale['latitude'],
                        'longitude' => $sale['longitude']
                    ];
                }
                $grouped[$key]['qty'] += $sale['qty'];
                $grouped[$key]['total'] += ($sale['qty'] * $sale['rate']);
                
                $sold_products[] = $desc;
            }
            $final_results = array_values($grouped);
        }
        
        $date_label = $start_date == $end_date ? $start_date : $start_date . ' to ' . $end_date;
        // Find unsold products
        $sold_products = array_unique($sold_products);
        $unsold_results = [];
        foreach($item_descriptions as $desc) {
            if(!in_array($desc, $sold_products)) {
                $sku = isset($items_sku_map[$desc]) ? $items_sku_map[$desc] : '';
                $unsold_results[] = [
                    'date' => $date_label,
                    'invoice_no' => '',
                    'customer' => '',
                    'name' => $desc,
                    'sku' => $sku,
                    'creator' => '',
                    'unit_price' => 0,
                    'qty' => 0,
                    'total' => 0,
                    'format' => '',
                    'customer_id' => '',
                    'phone' => '',
                    'channel' => '',
                    'shop_class' => '',
                    'region' => '',
                    'town' => '',
                    'latitude' => '',
                    'longitude' => ''
                ];
            }
        }
        
        
        // Sort final results by invoice_no, then append unsold
        usort($final_results, function($a, $b) {
            return strcmp($a['invoice_no'], $b['invoice_no']);
        });
        
        usort($unsold_results, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return array_merge($final_results, $unsold_results);
    }

    public function get_logs()
    {
        $this->db->order_by('date_sent', 'desc');
        return $this->db->get(db_prefix() . 'daily_sales_report_logs')->result_array();
    }

    public function get_log($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'daily_sales_report_logs')->row_array();
    }

    public function get_warehouses()
    {
        if($this->db->table_exists(db_prefix() . 'warehouse')) {
            $this->db->order_by('warehouse_name', 'asc');
            return $this->db->get(db_prefix() . 'warehouse')->result_array();
        }
        return [];
    }

    public function get_commodity_types()
    {
        if($this->db->table_exists(db_prefix() . 'ware_commodity_type')) {
            $this->db->order_by('commondity_name', 'asc');
            return $this->db->get(db_prefix() . 'ware_commodity_type')->result_array();
        }
        return [];
    }

    public function get_commodity_groups()
    {
        if($this->db->table_exists(db_prefix() . 'items_groups')) {
            $this->db->order_by('name', 'asc');
            return $this->db->get(db_prefix() . 'items_groups')->result_array();
        }
        return [];
    }

    public function get_sub_groups($group_id = '')
    {
        if($this->db->table_exists(db_prefix() . 'wh_sub_group')) {
            if ($group_id != '') {
                $this->db->where('group_id', $group_id);
            }
            $this->db->order_by('sub_group_name', 'asc');
            return $this->db->get(db_prefix() . 'wh_sub_group')->result_array();
        }
        return [];
    }

    public function get_colors()
    {
        if($this->db->table_exists(db_prefix() . 'ware_color')) {
            $this->db->order_by('color_name', 'asc');
            return $this->db->get(db_prefix() . 'ware_color')->result_array();
        }
        return [];
    }
    
    public function get_sizes()
    {
        if($this->db->table_exists(db_prefix() . 'ware_size_type')) {
            $this->db->order_by('size_name', 'asc');
            return $this->db->get(db_prefix() . 'ware_size_type')->result_array();
        }
        return [];
    }
    
    public function get_styles()
    {
        if($this->db->table_exists(db_prefix() . 'ware_style_type')) {
            $this->db->order_by('style_name', 'asc');
            return $this->db->get(db_prefix() . 'ware_style_type')->result_array();
        }
        return [];
    }
    
    public function get_models()
    {
        if($this->db->table_exists(db_prefix() . 'wh_model')) {
            $this->db->order_by('name', 'asc');
            return $this->db->get(db_prefix() . 'wh_model')->result_array();
        }
        return [];
    }

    public function get_items_by_filters($warehouse_id = '', $commodity_type_id = '', $group_id = '', $sub_group_id = '', $color_id = '', $size_id = '', $style_id = '', $model_id = '')
    {
        $this->db->select('id, description, commodity_code');
        if (!empty($warehouse_id)) {
            if(is_array($warehouse_id)) { $this->db->where_in('warehouse_id', $warehouse_id); }
            else { $this->db->where('warehouse_id', $warehouse_id); }
        }
        if (!empty($commodity_type_id)) {
            if(is_array($commodity_type_id)) { $this->db->where_in('commodity_type', $commodity_type_id); }
            else { $this->db->where('commodity_type', $commodity_type_id); }
        }
        if (!empty($group_id)) {
            if(is_array($group_id)) { $this->db->where_in('group_id', $group_id); }
            else { $this->db->where('group_id', $group_id); }
        }
        if (!empty($sub_group_id)) {
            if(is_array($sub_group_id)) { $this->db->where_in('sub_group', $sub_group_id); }
            else { $this->db->where('sub_group', $sub_group_id); }
        }
        if (!empty($color_id)) {
            if(is_array($color_id)) { $this->db->where_in('color_id', $color_id); }
            else { $this->db->where('color_id', $color_id); }
        }
        if (!empty($size_id)) {
            if(is_array($size_id)) { $this->db->where_in('size_id', $size_id); }
            else { $this->db->where('size_id', $size_id); }
        }
        if (!empty($style_id)) {
            if(is_array($style_id)) { $this->db->where_in('style_id', $style_id); }
            else { $this->db->where('style_id', $style_id); }
        }
        if (!empty($model_id)) {
            if(is_array($model_id)) { $this->db->where_in('model_id', $model_id); }
            else { $this->db->where('model_id', $model_id); }
        }
        
        $this->db->order_by('description', 'asc');
        return $this->db->get(db_prefix() . 'items')->result_array();
    }
    
    public function get_item_details($item_ids)
    {
        if (empty($item_ids)) return [];
        $this->db->select('id, description, commodity_code');
        $this->db->where_in('id', $item_ids);
        return $this->db->get(db_prefix() . 'items')->result_array();
    }

    public function get_optimetriks_stock_level($selected_item_ids, $supplier_ids = [], $date = '')
    {
        if (empty($selected_item_ids)) return [];
        $warehouse_id = get_option('optimetriks_stock_warehouse_id');

        // One row per product (per warehouse when no stock warehouse is configured), lots summed together
        $this->db->select("COALESCE(NULLIF(" . db_prefix() . "items.unit, ''), 'pack') as uom, SUM(" . db_prefix() . 'inventory_manage.inventory_number) as quantity, ' . db_prefix() . 'items.id as id, ' . db_prefix() . 'warehouse.warehouse_name as warehouse_attached');
        $this->db->from(db_prefix() . 'items');
        $this->db->join(db_prefix() . 'inventory_manage', db_prefix() . 'inventory_manage.commodity_id = ' . db_prefix() . 'items.id');
        $this->db->join(db_prefix() . 'warehouse', db_prefix() . 'warehouse.warehouse_id = ' . db_prefix() . 'inventory_manage.warehouse_id', 'left');
        $this->db->where_in(db_prefix() . 'items.id', $selected_item_ids);
        if (!empty($warehouse_id)) {
            $this->db->where(db_prefix() . 'inventory_manage.warehouse_id', $warehouse_id);
        }
        $this->db->group_by([db_prefix() . 'items.id', db_prefix() . 'inventory_manage.warehouse_id']);
        // Skip products with no stock
        $this->db->having('quantity >', 0);
        $rows = $this->db->get()->result_array();
        foreach ($rows as &$row) {
            $row['sku_id'] = $this->resolve_sku_id($row['id'], $supplier_ids);
            // Stock snapshot id: digits only, <Ymd><item id>, e.g. 20260929 + 15 = 2026092915
            $row['id'] = preg_replace('/\D/', '', ($date ? $date : date('Y-m-d')) . $row['id']);
        }
        unset($row);
        return $rows;
    }

    public function get_optimetriks_sell_out($date, $selected_item_ids)
    {
        if (empty($selected_item_ids)) return [];
        // We first need the invoices that contain these items
        $this->db->select('rel_id');
        $this->db->from(db_prefix() . 'itemable');
        $this->db->where('rel_type', 'invoice');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description');
        $this->db->where_in(db_prefix() . 'items.id', $selected_item_ids);
        $invoice_ids = array_column($this->db->get()->result_array(), 'rel_id');
        
        if (empty($invoice_ids)) return [];

        $this->db->select('id as raw_invoice_id, date as date, clientid as raw_client_id, addedfrom as sales_rep_id, total as amount');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('DATE(date)', $date);
        $this->db->where_in('id', $invoice_ids);
        $this->db->where('status !=', 5);
        $this->db->where('status !=', 6);
        $rows = $this->db->get()->result_array();
        
        $final_rows = [];
        foreach ($rows as $r) {
            $final_rows[] = [
                'visit_id' => 'BOSS-' . format_invoice_number($r['raw_invoice_id']),
                'date' => $r['date'],
                'cust_id' => 'BOSS-' . $r['raw_client_id'],
                'sales_rep_id' => $r['sales_rep_id'],
                'amount' => $r['amount']
            ];
        }
        return $final_rows;
    }

    public function get_optimetriks_sell_out_lines($date, $selected_item_ids, $supplier_ids = [])
    {
        if (empty($selected_item_ids)) return [];
        $this->db->select(db_prefix() . "itemable.id as raw_line_id, " . db_prefix() . "invoices.id as raw_invoice_id, " . db_prefix() . "items.id as item_id,COALESCE(NULLIF(" . db_prefix() . "itemable.unit, ''), 'pack') as format, " . db_prefix() . "itemable.qty as quantity, " . db_prefix() . "itemable.rate as format_price, (" . db_prefix() . "itemable.qty * " . db_prefix() . "itemable.rate) as total_price");
        $this->db->from(db_prefix() . 'itemable');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'itemable.rel_id');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description');
        $this->db->where(db_prefix() . 'itemable.rel_type', 'invoice');
        $this->db->where('DATE(' . db_prefix() . 'invoices.date)', $date);
        $this->db->where_in(db_prefix() . 'items.id', $selected_item_ids);
        $this->db->where(db_prefix() . 'invoices.status !=', 5);
        $this->db->where(db_prefix() . 'invoices.status !=', 6);
        $rows = $this->db->get()->result_array();
        
        $final_rows = [];
        foreach ($rows as $r) {
            $formatted_invoice = format_invoice_number($r['raw_invoice_id']);
            $final_rows[] = [
                'id' => 'BOSS-' . $formatted_invoice . '-' . $r['raw_line_id'],
                'visit_id' => 'BOSS-' . $formatted_invoice,
                'sku_id' => $this->resolve_sku_id($r['item_id'], $supplier_ids),
                'format' => $r['format'],
                'quantity' => $r['quantity'],
                'format_price' => $r['format_price'],
                'total_price' => $r['total_price']
            ];
        }
        return $final_rows;
    }

    public function get_optimetriks_customers($date, $selected_item_ids)
    {
        if (empty($selected_item_ids)) return [];
        // Get invoices
        $this->db->select('rel_id');
        $this->db->from(db_prefix() . 'itemable');
        $this->db->where('rel_type', 'invoice');
        $this->db->join(db_prefix() . 'items', db_prefix() . 'items.description = ' . db_prefix() . 'itemable.description');
        $this->db->where_in(db_prefix() . 'items.id', $selected_item_ids);
        $invoice_ids = array_column($this->db->get()->result_array(), 'rel_id');
        
        if (empty($invoice_ids)) return [];

        $select_fields = "CONCAT('BOSS-', " . db_prefix() . "clients.userid) as cust_id, " . 
            db_prefix() . 'clients.company as customer_name, ' . 
            db_prefix() . 'clients.phonenumber as phone, ' .
            db_prefix() . 'clients.pgroup as customer_type, ' .
            db_prefix() . 'clients.state, ' .
            db_prefix() . 'clients.city, ' .
            db_prefix() . 'clients.latitude as latitude, ' .
            db_prefix() . 'clients.longitude as longitude, ' .
            db_prefix() . 'countries.short_name as region';
            
        $has_warehouse = $this->db->field_exists('warehouse_id', db_prefix() . 'invoices');
        if ($has_warehouse) {
            $select_fields .= ", MAX(" . db_prefix() . "warehouse.warehouse_name) as warehouse_name";
        }
        
        $this->db->select($select_fields);
        $this->db->from(db_prefix() . 'clients');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid');
        
        if ($has_warehouse) {
            $this->db->join(db_prefix() . 'warehouse', db_prefix() . 'warehouse.warehouse_id = ' . db_prefix() . 'invoices.warehouse_id', 'left');
        }
        
        $this->db->join(db_prefix() . 'countries', db_prefix() . 'countries.country_id = ' . db_prefix() . 'clients.country', 'left');
        $this->db->where('DATE(' . db_prefix() . 'invoices.date)', $date);
        $this->db->where_in(db_prefix() . 'invoices.id', $invoice_ids);
        $this->db->group_by(db_prefix() . 'clients.userid');
        $clients = $this->db->get()->result_array();

        $customers = [];
        foreach ($clients as $client) {
            $customers[] = [
                'cust_id' => $client['cust_id'],
                'customer_name' => $client['customer_name'] ? $client['customer_name'] : 'Unknown',
                'phone' => $client['phone'],
                'channel' => 'Sales',
                'customer_type' => $client['customer_type'],
                'region' => $client['region'],
                'town' => trim($client['state'] . ' ' . $client['city']),
                'latitude' => $client['latitude'] ? $client['latitude'] : 0,
                'longitude' => $client['longitude'] ? $client['longitude'] : 0,
                'warehouse_attached' => (isset($client['warehouse_name']) && !empty($client['warehouse_name'])) ? $client['warehouse_name'] : 'Warehouse 01'
            ];
        }
        return $customers;
    }

    public function get_optimetriks_products($date, $selected_item_ids, $supplier_ids = [])
    {
        if (empty($selected_item_ids)) return [];
        
        $this->db->select(
            db_prefix() . 'items.id as sku_id, ' .
            db_prefix() . 'items.commodity_code as sku_code, ' .
            db_prefix() . 'items.description as sku_description, ' .
            db_prefix() . 'items.unit as format, ' .
            db_prefix() . 'items.rate as price'
        );
        $this->db->from(db_prefix() . 'items');
        $this->db->where_in('id', $selected_item_ids);
        $items = $this->db->get()->result_array();

        $products = [];
        foreach ($items as $item) {
            $products[] = [
                'sku_id' => $this->resolve_sku_id($item['sku_id'], $supplier_ids),
                'sku_code' => $item['sku_code'],
                'sku_description' => $item['sku_description'],
                'format' => $item['format'] ? $item['format'] : 'unit',
                'nbr_units' => 1,
                'price' => $item['price'] ? $item['price'] : 0
            ];
        }
        return $products;
    }

    public function send_to_optimetriks($date, $selected_item_ids, $supplier_ids = [])
    {
        $this->optimetriks_error = '';
        // Missing/invalid date (e.g. 1970-01-01 from an empty value) falls back to today
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) || $date < '2000-01-01') {
            $date = date('Y-m-d');
        }
        $client_id = get_option('optimetriks_client_id');
        $email = get_option('optimetriks_email');
        $password = get_option('optimetriks_password');

        if (empty($client_id) || empty($email) || empty($password)) {
            $this->optimetriks_error = 'Optimetriks API credentials are not configured.';
            return [];
        }

        // 1. Authenticate
        $login_url = 'https://v3-0.fieldproapp.com/web/web/users/login';
        $payload = json_encode(['email' => $email, 'password' => $password]);
        
        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'client-id: ' . $client_id
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $auth_token = '';
        if ($http_code == 200) {
            $resp_data = json_decode($response, true);
            if (isset($resp_data['data']['data']['token'])) {
                $auth_token = $resp_data['data']['data']['token'];
            } elseif (isset($resp_data['data']['token'])) {
                $auth_token = $resp_data['data']['token'];
            } elseif (isset($resp_data['auth_token'])) {
                $auth_token = $resp_data['auth_token'];
            }
        }

        if (empty($auth_token)) {
            $this->optimetriks_error = 'Optimetriks login failed (HTTP ' . $http_code . '): ' . substr(strip_tags((string) $response), 0, 300);
            return [];
        }

        $headers = [
            'client-id: ' . $client_id,
            'auth: ' . $auth_token
        ];

        $generated_zips = [];

        // 2. Generate and send Stock Level
        $stock_data = $this->get_optimetriks_stock_level($selected_item_ids, $supplier_ids, $date);
        if (!empty($stock_data)) {
            $csv = "sku_id;uom;quantity;id;warehouse_attached\n";
            foreach ($stock_data as $row) {
                $csv .= '"' . str_replace('"', '""', $row['sku_id']) . '";"' . str_replace('"', '""', $row['uom']) . '";' . (float)$row['quantity'] . ';"' . str_replace('"', '""', $row['id']) . '";"' . str_replace('"', '""', $row['warehouse_attached']) . "\"\n";
            }
            $generated_zips[] = $this->upload_optimetriks_stream('distributors_stock_level', $csv, $date, $headers);
        } else {
            $generated_zips[] = $this->skipped_optimetriks_stream('distributors_stock_level', $date, 'No products with stock in the selected stock warehouse');
        }

        // 3. Generate and send Sell Out
        $sell_out_data = $this->get_optimetriks_sell_out($date, $selected_item_ids);
        if (!empty($sell_out_data)) {
            $csv = "visit_id;date;cust_id;sales_rep_id;amount\n";
            foreach ($sell_out_data as $row) {
                $csv .= '"' . str_replace('"', '""', $row['visit_id']) . '";"' . $row['date'] . '";"' . str_replace('"', '""', $row['cust_id']) . '";"' . str_replace('"', '""', $row['sales_rep_id']) . '";' . (float)$row['amount'] . "\n";
            }
            $generated_zips[] = $this->upload_optimetriks_stream('distributors_sell_out', $csv, $date, $headers);
        } else {
            $generated_zips[] = $this->skipped_optimetriks_stream('distributors_sell_out', $date, 'No invoices with the tracked products on this date');
        }

        // 4. Generate and send Sell Out Lines
        $lines_data = $this->get_optimetriks_sell_out_lines($date, $selected_item_ids, $supplier_ids);
        if (!empty($lines_data)) {
            $csv = "id;visit_id;sku_id;format;quantity;format_price;total_price\n";
            foreach ($lines_data as $row) {
                $csv .= '"' . str_replace('"', '""', $row['id']) . '";"' . str_replace('"', '""', $row['visit_id']) . '";"' . str_replace('"', '""', $row['sku_id']) . '";"' . str_replace('"', '""', $row['format']) . '";' . (float)$row['quantity'] . ';' . (float)$row['format_price'] . ';' . (float)$row['total_price'] . "\n";
            }
            $generated_zips[] = $this->upload_optimetriks_stream('distributors_sell_out_lines', $csv, $date, $headers);
        } else {
            $generated_zips[] = $this->skipped_optimetriks_stream('distributors_sell_out_lines', $date, 'No invoice lines with the tracked products on this date');
        }

        // 5. Generate and send Customers
        $customers_data = $this->get_optimetriks_customers($date, $selected_item_ids);
        if (!empty($customers_data)) {
            $csv = "cust_id;customer_name;phone;channel;customer_type;region;town;latitude;longitude;warehouse_attached\n";
            foreach ($customers_data as $row) {
                $csv .= '"' . str_replace('"', '""', $row['cust_id']) . '";"' . str_replace('"', '""', $row['customer_name']) . '";"' . str_replace('"', '""', $row['phone']) . '";"' . str_replace('"', '""', $row['channel']) . '";"' . str_replace('"', '""', $row['customer_type']) . '";"' . str_replace('"', '""', $row['region']) . '";"' . str_replace('"', '""', $row['town']) . '";' . (float)$row['latitude'] . ';' . (float)$row['longitude'] . ';"' . str_replace('"', '""', $row['warehouse_attached']) . "\"\n";
            }
            $generated_zips[] = $this->upload_optimetriks_stream('distributors_customers', $csv, $date, $headers);
        } else {
            $generated_zips[] = $this->skipped_optimetriks_stream('distributors_customers', $date, 'No customers invoiced for the tracked products on this date');
        }

        return $generated_zips;
    }

    /**
     * Entry for a stream that had no data for the date, so it appears in the log instead of silently missing
     */
    private function skipped_optimetriks_stream($stream, $date, $reason)
    {
        return [
            'stream' => $stream,
            'date' => $date,
            'csv' => '',
            'filename' => $stream . '_' . $date . '.zip',
            'path' => false,
            'skipped' => true,
            'success' => false,
            'http_code' => 0,
            'response' => $reason,
        ];
    }

    /**
     * Zip the CSV as <stream>.csv inside <stream>.zip and upload it to the FieldPro stream endpoint.
     * Returns the zip info plus the API result so callers can report real success/failure.
     */
    private function upload_optimetriks_stream($stream, $csv, $date, $headers)
    {
        $result = [
            'stream' => $stream,
            'date' => $date,
            'csv' => $csv,
            'filename' => $stream . '_' . $date . '.zip',
            'path' => false,
            'success' => false,
            'http_code' => 0,
            'response' => '',
        ];

        $zip_path = $this->create_zip_for_optimetriks($stream, $csv);
        if (!$zip_path) {
            $result['response'] = 'Could not create zip file';
            return $result;
        }
        $result['path'] = $zip_path;

        $upload = $this->post_zip_to_optimetriks('https://v3-0.fieldproapp.com/web/stream/' . $stream . '/run', $zip_path, $headers);
        $result['http_code'] = $upload['http_code'];
        $result['response'] = $upload['response'];
        $result['success'] = $upload['http_code'] >= 200 && $upload['http_code'] < 300;

        return $result;
    }

    /**
     * Write one log entry with the status and the exact CSV data of every Optimetriks upload.
     * Returns the number of files Optimetriks accepted.
     */
    public function log_optimetriks_send($report, $start_date, $end_date, $results, $errors = [])
    {
        if (empty($results) && empty($errors)) {
            return 0;
        }

        $accepted = 0;
        $summary = [];
        $data_sections = [];
        foreach ($results as $z) {
            if (!empty($z['skipped'])) {
                $summary[] = $z['date'] . ' | ' . $z['stream'] . ' | SKIPPED | ' . $z['response'];
                continue;
            }
            if ($z['success']) {
                $accepted++;
            }
            $summary[] = $z['date'] . ' | ' . $z['stream'] . ' | ' . ($z['success'] ? 'OK' : 'FAILED') . ' | HTTP ' . $z['http_code'] . ' | ' . substr(strip_tags($z['response']), 0, 500);
            $data_sections[] = '===== ' . $z['stream'] . '.csv (' . $z['date'] . ') =====' . "\n" . $z['csv'];
        }

        $content = 'Optimetriks API upload for ' . $start_date . ' to ' . $end_date . "\n\n"
            . "----- STATUS -----\n" . implode("\n", array_merge($errors, $summary)) . "\n\n"
            . "----- DATA SENT -----\n" . implode("\n", $data_sections);

        $this->db->insert(db_prefix() . 'daily_sales_report_logs', [
            'report_id' => $report['id'],
            'report_name' => $report['name'],
            'date_sent' => date('Y-m-d H:i:s'),
            'email_to' => 'FieldPro API (Optimetriks)',
            'records_count' => $accepted,
            'csv_content' => $content
        ]);

        return $accepted;
    }

    /**
     * Remove a zip created by create_zip_for_optimetriks along with its temp folder
     */
    public function cleanup_optimetriks_zip($zip_path)
    {
        if (!$zip_path) {
            return;
        }
        @unlink($zip_path);
        @rmdir(dirname($zip_path));
    }

    private function create_zip_for_optimetriks($stream, $csv_content)
    {
        // Unique folder per upload so the zip itself can carry the stream name (e.g. distributors_stock_level.zip)
        $tmp_dir = rtrim(get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . uniqid('opti_');
        if (!@mkdir($tmp_dir, 0755, true) && !is_dir($tmp_dir)) {
            return false;
        }
        $zip_path = $tmp_dir . DIRECTORY_SEPARATOR . $stream . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString($stream . '.csv', $csv_content);
            $zip->close();
            return $zip_path;
        }
        return false;
    }

    private function post_zip_to_optimetriks($url, $zip_path, $headers)
    {
        $ch = curl_init($url);
        $cfile = new CURLFile($zip_path, 'application/zip', basename($zip_path));
        
        $post = ['file' => $cfile];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $response = 'cURL error: ' . curl_error($ch);
        }
        curl_close($ch);

        return ['http_code' => $http_code, 'response' => (string) $response];
    }
}
