<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_sourcing extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vendor_sourcing/vendor_sourcing_model');
    }

    /**
     * Sourcing Locator Dashboard
     */
    public function locator()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            access_denied('vendor_sourcing');
        }

        $prefix = db_prefix();

        // All item names (not just mapped ones)
        $this->db->select('id, description as item_name');
        $this->db->from($prefix . 'items');
        $this->db->where('description !=', '');
        $this->db->order_by('description', 'asc');
        $data['filter_items'] = $this->db->get()->result_array();

        // All item SKUs
        $this->db->select('commodity_code as sku');
        $this->db->from($prefix . 'items');
        $this->db->where("commodity_code IS NOT NULL AND commodity_code != ''", NULL, FALSE);
        $this->db->group_by('commodity_code');
        $this->db->order_by('commodity_code', 'asc');
        $data['filter_skus'] = $this->db->get()->result_array();

        // All vendors
        $this->db->select('userid, company');
        $this->db->from($prefix . 'pur_vendor');
        $this->db->where('active', 1);
        $this->db->order_by('company', 'asc');
        $data['filter_vendors'] = $this->db->get()->result_array();

        // All vendor SKUs (only from mappings — only relevant values)
        $this->db->select('vendor_item_code');
        $this->db->from($prefix . 'item_vendors');
        $this->db->where("vendor_item_code IS NOT NULL AND vendor_item_code != ''", NULL, FALSE);
        $this->db->group_by('vendor_item_code');
        $this->db->order_by('vendor_item_code', 'asc');
        $data['filter_vendor_skus'] = $this->db->get()->result_array();

        // All item categories
        $this->db->select('id, name');
        $this->db->from($prefix . 'items_groups');
        $this->db->where("name IS NOT NULL AND name != ''", NULL, FALSE);
        $this->db->order_by('name', 'asc');
        $data['filter_categories'] = $this->db->get()->result_array();

        $data['title'] = 'Sourcing Locator';
        $this->load->view('vendor_sourcing/locator', $data);
    }

    /**
     * Render the vendor mappings UI for a Warehouse module item detail page.
     * This is a full standalone panel (not injected into a modal).
     */
    public function warehouse_item_vendors_ui($item_id = '')
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            echo '<div class="alert alert-danger">Access Denied</div>';
            return;
        }

        $item_id = (int) $item_id;

        try {
            $data['item_id']      = $item_id;
            $data['item_vendors'] = [];
            $data['vendors']      = [];

            if ($item_id > 0) {
                // Get existing vendor mappings (only vendor_id needed for simple display)
                // FALSE disables CI auto-escaping so DISTINCT works correctly with aliased columns
                $this->db->select('iv.vendor_id, pv.company as vendor_name', FALSE);
                $this->db->from(db_prefix() . 'item_vendors iv');
                $this->db->join(db_prefix() . 'pur_vendor pv', 'pv.userid = iv.vendor_id', 'left');
                $this->db->where('iv.item_id', $item_id);
                $this->db->group_by('iv.vendor_id');
                $data['item_vendors'] = $this->db->get()->result_array();
            }

            // Get all available vendors from Purchase module
            $this->db->select('userid, company');
            $this->db->from(db_prefix() . 'pur_vendor');
            $this->db->where('active', 1);
            $this->db->order_by('company', 'asc');
            $data['vendors'] = $this->db->get()->result_array();

        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Error loading vendor data: ' . htmlspecialchars($e->getMessage()) . '</div>';
            return;
        }

        $this->load->view('vendor_sourcing/warehouse_vendors_tab', $data);
    }

    /**
     * AJAX save endpoint for vendor-item mappings from the Warehouse item detail page.
     * item_id is passed as a URI segment: warehouse_save_vendors/{item_id}
     */
    public function warehouse_save_vendors($item_id = 0)
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $item_id = (int) $item_id;
        $vendors = $this->input->post('vendors');

        if ($item_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }

        // Extract vendor IDs only — the warehouse form only posts vendor_id per row
        $vendor_rows = $this->input->post('vendors');
        $vendor_ids = [];
        if (is_array($vendor_rows)) {
            foreach ($vendor_rows as $row) {
                if (!empty($row['vendor_id'])) {
                    $vendor_ids[] = (int) $row['vendor_id'];
                }
            }
        }

        $this->vendor_sourcing_model->save_warehouse_vendors($item_id, $vendor_ids);
        echo json_encode(['success' => true, 'message' => 'Vendor mappings saved successfully']);
    }

    /**
     * AJAX endpoint: Return the most recent purchase price and currency
     * for a given item + vendor pair from tblpur_order_detail + tblpur_orders.
     *
     * POST params: item_id, vendor_id
     * Returns: { found: bool, price: float|null, currency_id: int|null }
     */
    public function get_last_vendor_price()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            echo json_encode(['found' => false]);
            return;
        }

        $item_id   = (int) $this->input->post('item_id');
        $vendor_id = (int) $this->input->post('vendor_id');

        if ($item_id <= 0 || $vendor_id <= 0) {
            echo json_encode(['found' => false]);
            return;
        }

        $result = $this->vendor_sourcing_model->get_last_purchase_price_from_history($item_id, $vendor_id);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Server-side DataTables endpoint for Sourcing Locator
     */
    public function locator_data()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            header('HTTP/1.0 403 Forbidden');
            exit(json_encode(['error' => 'Access denied']));
        }

        $prefix = db_prefix();

        // Named filter values from POST
        $f_item_name   = $this->input->post('filter_item_name');
        $f_sku         = $this->input->post('filter_sku');
        $f_vendor_name = $this->input->post('filter_vendor_name');
        $f_vendor_sku  = $this->input->post('filter_vendor_sku');
        $f_category    = $this->input->post('filter_category');
        $f_price_min   = $this->input->post('filter_price_min');
        $f_price_max   = $this->input->post('filter_price_max');

        // Columns for data_tables_init — based on tblitems as the primary table
        $aColumns = [
            'i.description',
            'i.commodity_code',
            'ig.name',
            'pv.company',
            'iv.vendor_item_code',
            'iv.last_purchase_price',
            'iv.is_preferred',
        ];

        $sIndexColumn = 'i.id';
        $sTable       = $prefix . 'items i';

        $join = [
            'LEFT JOIN ' . $prefix . 'items_groups ig   ON ig.id = i.group_id',
            'LEFT JOIN ' . $prefix . 'item_vendors iv    ON iv.item_id = i.id',
            'LEFT JOIN ' . $prefix . 'pur_vendor pv      ON pv.userid = iv.vendor_id',
            'LEFT JOIN ' . $prefix . 'currencies cur     ON cur.id = iv.currency',
        ];

        // Only show items that can be inventory / are active
        $where = [];

        if (!empty($f_item_name)) {
            $safe = $this->db->escape_like_str($f_item_name);
            $where[] = "AND i.description LIKE '%{$safe}%'";
        }
        if (!empty($f_sku)) {
            $safe = $this->db->escape_like_str($f_sku);
            $where[] = "AND i.commodity_code LIKE '%{$safe}%'";
        }
        if (!empty($f_vendor_name)) {
            $safe = $this->db->escape_like_str($f_vendor_name);
            $where[] = "AND pv.company LIKE '%{$safe}%'";
        }
        if (!empty($f_vendor_sku)) {
            $safe = $this->db->escape_like_str($f_vendor_sku);
            $where[] = "AND iv.vendor_item_code LIKE '%{$safe}%'";
        }
        if (!empty($f_category)) {
            $safe = $this->db->escape_like_str($f_category);
            $where[] = "AND ig.name LIKE '%{$safe}%'";
        }
        if (is_numeric($f_price_min)) {
            $where[] = 'AND iv.last_purchase_price >= ' . (float) $f_price_min;
        }
        if (is_numeric($f_price_max)) {
            $where[] = 'AND iv.last_purchase_price <= ' . (float) $f_price_max;
        }

        // Extra columns to SELECT (beyond aColumns)
        $extraWhere = $where;
        $extraSelect = ['cur.name as currency_name', 'iv.id as mapping_id', 'pv.userid as vendor_id', 'i.id as item_id'];

        $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $extraWhere, $extraSelect);
        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $row) {
            $preferred_badge = $row['is_preferred']
                ? '<span class="label label-success">Yes</span>'
                : '<span class="label label-default">No</span>';

            $price = ($row['last_purchase_price'] !== null && $row['last_purchase_price'] !== '')
                ? number_format((float) $row['last_purchase_price'], 2) . ' ' . ($row['currency_name'] ?: '')
                : '<em class="text-muted">N/A</em>';

            // Vendor column with hierarchy button
            $vendorHtml = htmlspecialchars($row['company'] ?: '');
            if (!empty($row['vendor_id'])) {
                $vendorHtml .= ' <a href="javascript:void(0);" class="btn btn-default btn-xs vs-hierarchy-btn" data-vendor-id="' . $row['vendor_id'] . '" data-vendor-name="' . htmlspecialchars($row['company'] ?: '') . '" title="View Hierarchy"><i class="fa fa-sitemap"></i></a>';
            }

            // Inquiry button
            $inquiryBtn = '';
            if (!empty($row['vendor_id'])) {
                $inquiryBtn = '<a href="javascript:void(0);" class="btn btn-info btn-xs vs-inquiry-btn" '
                    . 'data-vendor-id="' . $row['vendor_id'] . '" '
                    . 'data-vendor-name="' . htmlspecialchars($row['company'] ?: '') . '" '
                    . 'data-item-id="' . ($row['item_id'] ?? '') . '" '
                    . 'data-item-name="' . htmlspecialchars($row['description'] ?: '') . '" '
                    . 'title="Send Inquiry"><i class="fa fa-paper-plane"></i></a>';
            }

            $output['aaData'][] = [
                htmlspecialchars($row['description']    ?: ''),
                htmlspecialchars($row['commodity_code'] ?: ''),
                htmlspecialchars($row['name']           ?: ''),
                $vendorHtml,
                htmlspecialchars($row['vendor_item_code'] ?: ''),
                $price,
                $preferred_badge,
                $inquiryBtn,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($output);
    }

    /**
     * Render the vendor mappings UI for the Item Add/Edit modal
     */
    public function get_item_vendors_ui($item_id = '')
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            echo '<p class="text-danger">Access Denied</p>';
            return;
        }

        $data['item_id']      = $item_id;
        $data['item_vendors'] = [];

        if (is_numeric($item_id) && $item_id > 0) {
            $data['item_vendors'] = $this->vendor_sourcing_model->get_vendors_for_item($item_id);
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $this->db->select('userid, company');
        $this->db->from(db_prefix() . 'pur_vendor');
        $this->db->order_by('company', 'asc');
        $data['vendors'] = $this->db->get()->result_array();

        $this->load->view('vendor_sourcing/item_vendors_ui', $data);
    }

    // ========================================================================
    //  INQUIRY FEATURE
    // ========================================================================

    /**
     * Inquiry Settings Page — Email/SMS templates
     */
    public function inquiry_settings()
    {
        if (!is_admin()) {
            access_denied('vendor_sourcing');
        }

        if ($this->input->post()) {
            $fields = [
                'vs_inquiry_email_subject',
                'vs_inquiry_email_template',
                'vs_inquiry_sms_template',
                'vs_inquiry_sms_api_url',
                'vs_inquiry_sms_api_key',
                'vs_inquiry_sms_sender_id',
            ];

            foreach ($fields as $field) {
                $val = $this->input->post($field, false); // false = don't XSS clean HTML templates
                update_option($field, $val);
            }

            set_alert('success', 'Inquiry settings saved successfully.');
            redirect(admin_url('vendor_sourcing/inquiry_settings'));
        }

        $data['title'] = 'Inquiry Settings';
        $data['email_subject']  = get_option('vs_inquiry_email_subject');
        $data['email_template'] = get_option('vs_inquiry_email_template');
        $data['sms_template']   = get_option('vs_inquiry_sms_template');
        $data['sms_api_url']    = get_option('vs_inquiry_sms_api_url');
        $data['sms_api_key']    = get_option('vs_inquiry_sms_api_key');
        $data['sms_sender_id']  = get_option('vs_inquiry_sms_sender_id');

        $this->load->view('vendor_sourcing/inquiry_settings', $data);
    }

    /**
     * Inquiry Log Page
     */
    public function inquiry_log()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            access_denied('vendor_sourcing');
        }

        // Get vendors for filter dropdown
        $this->db->select('userid, company');
        $this->db->from(db_prefix() . 'pur_vendor');
        $this->db->where('active', 1);
        $this->db->order_by('company', 'asc');
        $data['filter_vendors'] = $this->db->get()->result_array();

        $data['title'] = 'Inquiry Log';
        $data['stats'] = $this->vendor_sourcing_model->get_inquiry_stats();
        $this->load->view('vendor_sourcing/inquiry_log', $data);
    }

    /**
     * Server-side DataTables endpoint for Inquiry Log
     */
    public function inquiry_log_data()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            header('HTTP/1.0 403 Forbidden');
            exit(json_encode(['error' => 'Access denied']));
        }

        $filters = [
            'channel'   => $this->input->post('filter_channel'),
            'vendor_id' => $this->input->post('filter_vendor'),
            'date_from' => $this->input->post('filter_date_from'),
            'date_to'   => $this->input->post('filter_date_to'),
            'status'    => $this->input->post('filter_status'),
        ];

        $output = $this->vendor_sourcing_model->get_inquiry_logs($_POST, $filters);

        header('Content-Type: application/json');
        echo json_encode($output);
    }

    /**
     * AJAX: Send inquiry to vendor(s)
     */
    public function send_inquiry()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'create')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $data = [
            'item_id'           => $this->input->post('item_id'),
            'vendor_id'         => $this->input->post('vendor_id'),
            'send_email'        => $this->input->post('send_email'),
            'send_sms'          => $this->input->post('send_sms'),
            'send_to_hierarchy' => $this->input->post('send_to_hierarchy'),
            'email_subject'     => $this->input->post('email_subject'),
            'email_body'        => $this->input->post('email_body', false), // Don't XSS clean HTML
            'sms_body'          => $this->input->post('sms_body'),
        ];

        $result = $this->vendor_sourcing_model->send_inquiry($data);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * AJAX: Get vendor contact info for the inquiry modal
     */
    public function get_vendor_info()
    {
        if (!is_admin() && !has_permission('vendor_sourcing', '', 'view')) {
            echo json_encode(['success' => false]);
            return;
        }

        $vendor_id = (int) $this->input->post('vendor_id');
        $item_id   = (int) $this->input->post('item_id');

        $contact = $this->vendor_sourcing_model->get_vendor_contact($vendor_id);

        // Get item info
        $item = $this->db->select('description, commodity_code')
            ->where('id', $item_id)
            ->get(db_prefix() . 'items')
            ->row();

        // Get hierarchy children count
        $children_count = $this->db->where('parent_vendor_id', $vendor_id)
            ->count_all_results(db_prefix() . 'pur_vendor');

        $hierarchy_ids = $this->vendor_sourcing_model->get_hierarchy_vendor_ids($vendor_id);

        header('Content-Type: application/json');
        echo json_encode([
            'success'         => true,
            'vendor_name'     => $contact ? $contact->company : '',
            'vendor_email'    => $contact ? $contact->email : '',
            'vendor_phone'    => $contact ? ($contact->contact_phone ?: $contact->vendor_phone) : '',
            'contact_name'    => $contact ? trim($contact->firstname . ' ' . $contact->lastname) : '',
            'item_name'       => $item ? $item->description : '',
            'item_sku'        => $item ? $item->commodity_code : '',
            'children_count'  => $children_count,
            'hierarchy_total' => count($hierarchy_ids),
        ]);
    }
}

