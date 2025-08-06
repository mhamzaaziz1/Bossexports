<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get report by ID or get all reports
     * @param  integer $id Optional report ID
     * @return mixed      Object if ID is provided, array of objects if ID is not provided
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'smart_reports')->row();
        }

        return $this->db->get(db_prefix() . 'smart_reports')->result_array();
    }

    /**
     * Add new report
     * @param array $data Report data
     */
    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Handle filter_by as JSON
        if (isset($data['filter_by']) && is_array($data['filter_by'])) {
            $data['filter_by'] = json_encode($data['filter_by']);
        }
        
        // Convert date ranges to SQL format
        if (isset($data['date_range_start']) && $data['date_range_start']) {
            $data['date_range_start'] = to_sql_date($data['date_range_start']);
        }
        
        if (isset($data['date_range_end']) && $data['date_range_end']) {
            $data['date_range_end'] = to_sql_date($data['date_range_end']);
        }
        
        $this->db->insert(db_prefix() . 'smart_reports', $data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            log_activity('New Smart Report Created [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        
        return false;
    }

    /**
     * Update report
     * @param  array $data Report data
     * @param  integer $id   Report ID
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Handle filter_by as JSON
        if (isset($data['filter_by']) && is_array($data['filter_by'])) {
            $data['filter_by'] = json_encode($data['filter_by']);
        }
        
        // Convert date ranges to SQL format
        if (isset($data['date_range_start']) && $data['date_range_start']) {
            $data['date_range_start'] = to_sql_date($data['date_range_start']);
        }
        
        if (isset($data['date_range_end']) && $data['date_range_end']) {
            $data['date_range_end'] = to_sql_date($data['date_range_end']);
        }
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'smart_reports', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Smart Report Updated [ID: ' . $id . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Delete report
     * @param  integer $id Report ID
     * @return boolean
     */
    public function delete($id)
    {
        // Delete the report
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'smart_reports');
        
        if ($this->db->affected_rows() > 0) {
            // Delete any saved versions of this report
            $this->db->where('report_id', $id);
            $this->db->delete(db_prefix() . 'smart_reports_saved');
            
            // Delete any AI logs for this report
            $this->db->where('report_id', $id);
            $this->db->delete(db_prefix() . 'smart_reports_ai_logs');
            
            log_activity('Smart Report Deleted [ID: ' . $id . ']');
            return true;
        }
        
        return false;
    }

    /**
     * Generate report based on form input
     * @param  array $data Form data
     * @return array       Report data
     */
    public function generate_report($data)
    {
        $report_type = $data['report_type'];
        $date_range_start = isset($data['date_range_start']) ? to_sql_date($data['date_range_start']) : null;
        $date_range_end = isset($data['date_range_end']) ? to_sql_date($data['date_range_end']) : null;
        $group_by = isset($data['group_by']) ? $data['group_by'] : null;
        $metric = isset($data['metric']) ? $data['metric'] : null;
        $filter_by = isset($data['filter_by']) ? $data['filter_by'] : null;
        $sort_by = isset($data['sort_by']) ? $data['sort_by'] : null;
        $limit_results = isset($data['limit_results']) ? (int)$data['limit_results'] : 10;
        
        // If this is an AI-generated query, use the SQL from the AI helper
        if (isset($data['ai_query']) && !empty($data['ai_query'])) {
            $this->load->helper('smart_reports/smart_report_ai_helper');
            $ai_result = process_nlp_query($data['ai_query']);
            
            if (isset($ai_result['sql'])) {
                $sql = $ai_result['sql'];
                $result = $this->db->query($sql)->result_array();
                
                return [
                    'data' => $result,
                    'sql' => $sql,
                    'columns' => $this->get_columns_from_result($result)
                ];
            }
        }
        
        // Otherwise, build the query based on the form inputs
        $sql = $this->build_report_query(
            $report_type,
            $date_range_start,
            $date_range_end,
            $group_by,
            $metric,
            $filter_by,
            $sort_by,
            $limit_results
        );
        
        $result = $this->db->query($sql)->result_array();
        
        return [
            'data' => $result,
            'sql' => $sql,
            'columns' => $this->get_columns_from_result($result)
        ];
    }

    /**
     * Build SQL query for report
     * @param  string $report_type     Report type
     * @param  string $date_range_start Start date
     * @param  string $date_range_end   End date
     * @param  string $group_by        Group by field
     * @param  string $metric          Metric to calculate
     * @param  array  $filter_by       Filters
     * @param  string $sort_by         Sort by field
     * @param  integer $limit_results   Limit results
     * @return string                  SQL query
     */
    private function build_report_query($report_type, $date_range_start, $date_range_end, $group_by, $metric, $filter_by, $sort_by, $limit_results)
    {
        // Initialize query components
        $select = [];
        $from = '';
        $joins = [];
        $where = [];
        $group_by_sql = '';
        $order_by = '';
        $limit = '';
        
        // Add date range conditions if provided
        if ($date_range_start) {
            $where[] = "date_field >= '$date_range_start'";
        }
        
        if ($date_range_end) {
            $where[] = "date_field <= '$date_range_end'";
        }
        
        // Add filters if provided
        if ($filter_by && is_array($filter_by)) {
            foreach ($filter_by as $field => $value) {
                if ($value) {
                    $where[] = "$field = '$value'";
                }
            }
        }
        
        // Build query based on report type
        switch ($report_type) {
            case 'sales':
                $this->build_sales_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'purchases':
                $this->build_purchases_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'inventory':
                $this->build_inventory_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'payments':
                $this->build_payments_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'leads':
                $this->build_leads_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'tasks':
                $this->build_tasks_report_query($select, $from, $joins, $where, $group_by, $metric, $sort_by);
                break;
                
            case 'custom_query':
                // For custom queries, we'll just return a simple query
                return "SELECT * FROM " . db_prefix() . "clients LIMIT $limit_results";
                
            default:
                // Default to a simple query
                return "SELECT * FROM " . db_prefix() . "clients LIMIT $limit_results";
        }
        
        // Add group by if provided
        if ($group_by) {
            $group_by_sql = "GROUP BY $group_by";
        }
        
        // Add order by if provided
        if ($sort_by) {
            $order_by = "ORDER BY $sort_by DESC";
        }
        
        // Add limit
        $limit = "LIMIT $limit_results";
        
        // Combine all parts into a single SQL query
        $sql = "SELECT " . implode(', ', $select) . " FROM $from";
        
        if (!empty($joins)) {
            $sql .= ' ' . implode(' ', $joins);
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($group_by_sql) {
            $sql .= " $group_by_sql";
        }
        
        if ($order_by) {
            $sql .= " $order_by";
        }
        
        $sql .= " $limit";
        
        return $sql;
    }

    /**
     * Build sales report query components
     */
    private function build_sales_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        $from = db_prefix() . 'invoices';
        $date_field = 'date';
        
        // Replace the generic date_field in where clauses
        foreach ($where as $key => $clause) {
            $where[$key] = str_replace('date_field', $date_field, $clause);
        }
        
        if ($group_by == 'customer') {
            $select[] = db_prefix() . 'clients.company as customer';
            $select[] = "COUNT(" . db_prefix() . "invoices.id) as invoice_count";
            $select[] = "SUM(" . db_prefix() . "invoices.total) as total_amount";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "clients ON " . db_prefix() . "clients.userid = " . db_prefix() . "invoices.clientid";
            
            $group_by = db_prefix() . 'clients.company';
        } elseif ($group_by == 'staff') {
            $select[] = db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname';
            $select[] = "COUNT(" . db_prefix() . "invoices.id) as invoice_count";
            $select[] = "SUM(" . db_prefix() . "invoices.total) as total_amount";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "staff ON " . db_prefix() . "staff.staffid = " . db_prefix() . "invoices.addedfrom";
            
            $group_by = db_prefix() . 'staff.staffid';
        } else {
            // Default selections
            $select[] = db_prefix() . 'invoices.id';
            $select[] = db_prefix() . 'invoices.clientid';
            $select[] = db_prefix() . 'invoices.total';
            $select[] = db_prefix() . 'invoices.date';
            $select[] = db_prefix() . 'clients.company as customer';
            
            $joins[] = "LEFT JOIN " . db_prefix() . "clients ON " . db_prefix() . "clients.userid = " . db_prefix() . "invoices.clientid";
        }
    }

    /**
     * Build purchases report query components
     */
    private function build_purchases_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        // Similar structure to build_sales_report_query but for purchases
        $from = db_prefix() . 'purchase_orders';
        $date_field = 'order_date';
        
        // Replace the generic date_field in where clauses
        foreach ($where as $key => $clause) {
            $where[$key] = str_replace('date_field', $date_field, $clause);
        }
        
        if ($group_by == 'vendor') {
            $select[] = db_prefix() . 'vendors.company as vendor';
            $select[] = "COUNT(" . db_prefix() . "purchase_orders.id) as order_count";
            $select[] = "SUM(" . db_prefix() . "purchase_orders.total) as total_amount";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "vendors ON " . db_prefix() . "vendors.id = " . db_prefix() . "purchase_orders.vendor_id";
            
            $group_by = db_prefix() . 'vendors.company';
        } else {
            // Default selections
            $select[] = db_prefix() . 'purchase_orders.id';
            $select[] = db_prefix() . 'purchase_orders.vendor_id';
            $select[] = db_prefix() . 'purchase_orders.total';
            $select[] = db_prefix() . 'purchase_orders.order_date';
            $select[] = db_prefix() . 'vendors.company as vendor';
            
            $joins[] = "LEFT JOIN " . db_prefix() . "vendors ON " . db_prefix() . "vendors.id = " . db_prefix() . "purchase_orders.vendor_id";
        }
    }

    /**
     * Build inventory report query components
     */
    private function build_inventory_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        // Implementation for inventory reports
        $from = db_prefix() . 'items';
        
        $select[] = db_prefix() . 'items.id';
        $select[] = db_prefix() . 'items.description';
        $select[] = db_prefix() . 'items.rate';
        $select[] = db_prefix() . 'items.quantity_number as quantity';
        
        if ($group_by == 'item_group') {
            $select[] = db_prefix() . 'items_groups.name as group_name';
            $select[] = "COUNT(" . db_prefix() . "items.id) as item_count";
            $select[] = "SUM(" . db_prefix() . "items.quantity_number) as total_quantity";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "items_groups ON " . db_prefix() . "items_groups.id = " . db_prefix() . "items.group_id";
            
            $group_by = db_prefix() . 'items_groups.id';
        }
    }

    /**
     * Build payments report query components
     */
    private function build_payments_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        // Implementation for payments reports
        $from = db_prefix() . 'invoicepaymentrecords';
        $date_field = 'date';
        
        // Replace the generic date_field in where clauses
        foreach ($where as $key => $clause) {
            $where[$key] = str_replace('date_field', $date_field, $clause);
        }
        
        if ($group_by == 'payment_mode') {
            $select[] = db_prefix() . 'payment_modes.name as payment_mode';
            $select[] = "COUNT(" . db_prefix() . "invoicepaymentrecords.id) as payment_count";
            $select[] = "SUM(" . db_prefix() . "invoicepaymentrecords.amount) as total_amount";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "payment_modes ON " . db_prefix() . "payment_modes.id = " . db_prefix() . "invoicepaymentrecords.paymentmode";
            
            $group_by = db_prefix() . 'payment_modes.id';
        } else {
            // Default selections
            $select[] = db_prefix() . 'invoicepaymentrecords.id';
            $select[] = db_prefix() . 'invoicepaymentrecords.invoiceid';
            $select[] = db_prefix() . 'invoicepaymentrecords.amount';
            $select[] = db_prefix() . 'invoicepaymentrecords.date';
            $select[] = db_prefix() . 'payment_modes.name as payment_mode';
            
            $joins[] = "LEFT JOIN " . db_prefix() . "payment_modes ON " . db_prefix() . "payment_modes.id = " . db_prefix() . "invoicepaymentrecords.paymentmode";
        }
    }

    /**
     * Build leads report query components
     */
    private function build_leads_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        // Implementation for leads reports
        $from = db_prefix() . 'leads';
        $date_field = 'dateadded';
        
        // Replace the generic date_field in where clauses
        foreach ($where as $key => $clause) {
            $where[$key] = str_replace('date_field', $date_field, $clause);
        }
        
        if ($group_by == 'status') {
            $select[] = db_prefix() . 'leads_status.name as status';
            $select[] = "COUNT(" . db_prefix() . "leads.id) as lead_count";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "leads_status ON " . db_prefix() . "leads_status.id = " . db_prefix() . "leads.status";
            
            $group_by = db_prefix() . 'leads_status.id';
        } elseif ($group_by == 'source') {
            $select[] = db_prefix() . 'leads_sources.name as source';
            $select[] = "COUNT(" . db_prefix() . "leads.id) as lead_count";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "leads_sources ON " . db_prefix() . "leads_sources.id = " . db_prefix() . "leads.source";
            
            $group_by = db_prefix() . 'leads_sources.id';
        } else {
            // Default selections
            $select[] = db_prefix() . 'leads.id';
            $select[] = db_prefix() . 'leads.name';
            $select[] = db_prefix() . 'leads.email';
            $select[] = db_prefix() . 'leads.phonenumber';
            $select[] = db_prefix() . 'leads.dateadded';
            $select[] = db_prefix() . 'leads_status.name as status';
            
            $joins[] = "LEFT JOIN " . db_prefix() . "leads_status ON " . db_prefix() . "leads_status.id = " . db_prefix() . "leads.status";
        }
    }

    /**
     * Build tasks report query components
     */
    private function build_tasks_report_query(&$select, &$from, &$joins, &$where, $group_by, $metric, $sort_by)
    {
        // Implementation for tasks reports
        $from = db_prefix() . 'tasks';
        $date_field = 'dateadded';
        
        // Replace the generic date_field in where clauses
        foreach ($where as $key => $clause) {
            $where[$key] = str_replace('date_field', $date_field, $clause);
        }
        
        if ($group_by == 'status') {
            $select[] = db_prefix() . 'tasks.status';
            $select[] = "COUNT(" . db_prefix() . "tasks.id) as task_count";
            
            $group_by = db_prefix() . 'tasks.status';
        } elseif ($group_by == 'assignee') {
            $select[] = db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname';
            $select[] = "COUNT(" . db_prefix() . "tasks.id) as task_count";
            
            $joins[] = "LEFT JOIN " . db_prefix() . "task_assigned ON " . db_prefix() . "task_assigned.taskid = " . db_prefix() . "tasks.id";
            $joins[] = "LEFT JOIN " . db_prefix() . "staff ON " . db_prefix() . "staff.staffid = " . db_prefix() . "task_assigned.staffid";
            
            $group_by = db_prefix() . 'staff.staffid';
        } else {
            // Default selections
            $select[] = db_prefix() . 'tasks.id';
            $select[] = db_prefix() . 'tasks.name';
            $select[] = db_prefix() . 'tasks.status';
            $select[] = db_prefix() . 'tasks.startdate';
            $select[] = db_prefix() . 'tasks.duedate';
            $select[] = db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname';
            
            $joins[] = "LEFT JOIN " . db_prefix() . "task_assigned ON " . db_prefix() . "task_assigned.taskid = " . db_prefix() . "tasks.id";
            $joins[] = "LEFT JOIN " . db_prefix() . "staff ON " . db_prefix() . "staff.staffid = " . db_prefix() . "task_assigned.staffid";
        }
    }

    /**
     * Get columns from result array
     * @param  array $result Result array
     * @return array         Column names
     */
    private function get_columns_from_result($result)
    {
        if (empty($result)) {
            return [];
        }
        
        // Get the keys from the first row
        return array_keys($result[0]);
    }

    /**
     * Log AI query
     * @param  string  $query_text   The NLP query text
     * @param  string  $generated_sql The generated SQL
     * @param  integer $report_id    Optional report ID
     * @return integer               Log ID
     */
    public function log_ai_query($query_text, $generated_sql, $report_id = null)
    {
        $data = [
            'report_id' => $report_id,
            'query_text' => $query_text,
            'generated_sql' => $generated_sql,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert(db_prefix() . 'smart_reports_ai_logs', $data);
        return $this->db->insert_id();
    }

    /**
     * Get AI logs
     * @param  integer $limit Optional limit
     * @return array         AI logs
     */
    public function get_ai_logs($limit = null)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'smart_reports_ai_logs');
        $this->db->order_by('created_at', 'desc');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get()->result_array();
    }

    /**
     * Save report for future use
     * @param  array $data Report data
     * @return integer     Saved report ID
     */
    public function save_report($data)
    {
        $save_data = [
            'report_id' => $data['report_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_favorite' => isset($data['is_favorite']) ? 1 : 0,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert(db_prefix() . 'smart_reports_saved', $save_data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            log_activity('Smart Report Saved [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        
        return false;
    }

    /**
     * Get saved reports
     * @param  integer $user_id Optional user ID to filter by
     * @return array           Saved reports
     */
    public function get_saved_reports($user_id = null)
    {
        $this->db->select(db_prefix() . 'smart_reports_saved.*, ' . db_prefix() . 'smart_reports.title as report_title');
        $this->db->from(db_prefix() . 'smart_reports_saved');
        $this->db->join(db_prefix() . 'smart_reports', db_prefix() . 'smart_reports.id = ' . db_prefix() . 'smart_reports_saved.report_id');
        
        if ($user_id) {
            $this->db->where(db_prefix() . 'smart_reports_saved.created_by', $user_id);
        }
        
        $this->db->order_by('is_favorite', 'desc');
        $this->db->order_by('created_at', 'desc');
        
        return $this->db->get()->result_array();
    }
}