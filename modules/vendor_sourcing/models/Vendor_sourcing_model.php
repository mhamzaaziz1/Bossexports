<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_sourcing_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Save (replace) all vendor mappings for a single item.
     */
    public function save_item_vendors($item_id, $vendors_data)
    {
        // Delete all existing mappings first
        $this->db->where('item_id', (int) $item_id);
        $this->db->delete(db_prefix() . 'item_vendors');

        if (!is_array($vendors_data) || count($vendors_data) === 0) {
            return;
        }

        foreach ($vendors_data as $vendor) {
            if (!isset($vendor['vendor_id']) || empty($vendor['vendor_id'])) {
                continue;
            }
            $data = [
                'item_id'             => (int) $item_id,
                'vendor_id'           => (int) $vendor['vendor_id'],
                'vendor_item_code'    => isset($vendor['vendor_item_code']) ? xss_clean($vendor['vendor_item_code']) : null,
                'last_purchase_price' => isset($vendor['last_purchase_price']) && is_numeric($vendor['last_purchase_price'])
                    ? $vendor['last_purchase_price'] : null,
                'currency'    => isset($vendor['currency']) && is_numeric($vendor['currency']) ? (int) $vendor['currency'] : null,
                'is_preferred'=> isset($vendor['is_preferred']) && $vendor['is_preferred'] == 1 ? 1 : 0,
            ];
            $this->db->insert(db_prefix() . 'item_vendors', $data);
        }
    }

    /**
     * Save vendor assignments from the Warehouse tab (vendor-select only).
     * Preserves existing last_purchase_price / currency on already-mapped vendors.
     * Newly added vendors get null price/currency (filled later by the purchase pipeline).
     * Removed vendors are deleted.
     *
     * @param int   $item_id
     * @param array $vendor_ids  flat array of vendor IDs to keep
     */
    public function save_warehouse_vendors($item_id, $vendor_ids)
    {
        $item_id    = (int) $item_id;
        $vendor_ids = array_filter(array_map('intval', (array) $vendor_ids));
        $vendor_ids = array_values(array_unique($vendor_ids));

        // 1. Fetch currently saved vendor IDs for this item
        $this->db->select('vendor_id');
        $this->db->where('item_id', $item_id);
        $existing = $this->db->get(db_prefix() . 'item_vendors')->result_array();
        $existing_ids = array_column($existing, 'vendor_id');
        $existing_ids = array_map('intval', $existing_ids);

        // 2. Remove vendors that were de-selected
        $to_remove = array_diff($existing_ids, $vendor_ids);
        if (!empty($to_remove)) {
            $this->db->where('item_id', $item_id);
            $this->db->where_in('vendor_id', $to_remove);
            $this->db->delete(db_prefix() . 'item_vendors');
        }

        // 3. Insert only newly added vendors (skip already-existing ones to preserve price/currency)
        $to_add = array_diff($vendor_ids, $existing_ids);
        foreach ($to_add as $vendor_id) {
            $this->db->insert(db_prefix() . 'item_vendors', [
                'item_id'             => $item_id,
                'vendor_id'           => $vendor_id,
                'vendor_item_code'    => null,
                'last_purchase_price' => null,
                'currency'            => null,
                'is_preferred'        => 0,
            ]);
        }
    }

    /**
     * Get all vendor mappings for an item, joined with vendor company name.
     */
    public function get_vendors_for_item($item_id)
    {
        $this->db->select('iv.*, pv.company as vendor_name');
        $this->db->from(db_prefix() . 'item_vendors iv');
        $this->db->join(db_prefix() . 'pur_vendor pv', 'pv.userid = iv.vendor_id', 'left');
        $this->db->where('iv.item_id', (int) $item_id);
        return $this->db->get()->result_array();
    }

    /**
     * Automated pipeline: read a PO and upsert vendor-item price data.
     * Uses INSERT ON DUPLICATE KEY UPDATE for atomic, race-condition-safe upserts.
     */
    public function upsert_from_purchase_order($pur_order_id)
    {
        // Get the PO header for vendor info
        $this->db->where('id', (int) $pur_order_id);
        $pur_order = $this->db->get(db_prefix() . 'pur_orders')->row();

        if (!$pur_order) {
            return false;
        }

        $vendor_id = (int) $pur_order->vendor;

        // Get all line items for this PO
        $this->db->where('pur_order', (int) $pur_order_id);
        $details = $this->db->get(db_prefix() . 'pur_order_detail')->result_array();

        if (empty($details)) {
            return false;
        }

        foreach ($details as $detail) {
            $item_id = (int) $detail['item_code'];
            $price   = (float) $detail['unit_price'];

            if ($item_id <= 0) {
                continue;
            }

            // Check if a mapping already exists for this item+vendor pair
            $this->db->where('item_id', $item_id);
            $this->db->where('vendor_id', $vendor_id);
            $existing = $this->db->get(db_prefix() . 'item_vendors')->row();

            if ($existing) {
                // UPDATE — only refresh the price, keep is_preferred and vendor_item_code as-is
                $this->db->where('id', (int) $existing->id);
                $this->db->update(db_prefix() . 'item_vendors', [
                    'last_purchase_price' => $price,
                ]);
            } else {
                // INSERT a new mapping
                $this->db->insert(db_prefix() . 'item_vendors', [
                    'item_id'             => $item_id,
                    'vendor_id'           => $vendor_id,
                    'vendor_item_code'    => null,
                    'last_purchase_price' => $price,
                    'currency'            => null,
                    'is_preferred'        => 0,
                ]);
            }
        }

        return true;
    }

    /**
     * Get the most recent purchase price and currency for a given item + vendor
     * from tblpur_order_detail JOIN tblpur_orders, ordered by order date DESC.
     *
     * @param  int   $item_id
     * @param  int   $vendor_id
     * @return array ['found'=>bool, 'price'=>float|null, 'currency_id'=>int|null]
     */
    public function get_last_purchase_price_from_history($item_id, $vendor_id)
    {
        $prefix = db_prefix();
        $sql = "
            SELECT
                pod.unit_price,
                po.currency AS currency_id
            FROM {$prefix}pur_order_detail pod
            JOIN {$prefix}pur_orders po ON po.id = pod.pur_order
            WHERE pod.item_code = " . (int) $item_id . "
              AND po.vendor     = " . (int) $vendor_id . "
              AND po.returns    = 0
            ORDER BY po.order_date DESC, po.id DESC
            LIMIT 1
        ";

        $row = $this->db->query($sql)->row();

        if (!$row) {
            return ['found' => false, 'price' => null, 'currency_id' => null];
        }

        return [
            'found'       => true,
            'price'       => (float) $row->unit_price,
            'currency_id' => $row->currency_id ? (int) $row->currency_id : null,
        ];
    }

    /**
     * Advanced search for the Sourcing Locator dashboard (server-side DataTables).
     *
     * @param array  $params  DataTables request params (start, length, search, order, columns)
     * @param array  $filters Named filters: item_name, sku, vendor_name, vendor_sku, category, price_min, price_max
     * @return array ['data' => rows, 'recordsTotal' => int, 'recordsFiltered' => int]
     */
    public function sourcing_locator_search($params, $filters = [])
    {
        $prefix = db_prefix();

        $select = "
            iv.id,
            i.description  AS item_name,
            i.commodity_code  AS sku,
            ig.name           AS category,
            pv.company        AS vendor_name,
            iv.vendor_item_code AS vendor_sku,
            iv.last_purchase_price,
            iv.is_preferred,
            iv.currency AS currency_id,
            cur.name    AS currency_name
        ";

        $from = "{$prefix}item_vendors iv";

        $joins = [
            "LEFT JOIN {$prefix}items i         ON i.id  = iv.item_id",
            "LEFT JOIN {$prefix}items_groups ig ON ig.id = i.group_id",
            "LEFT JOIN {$prefix}pur_vendor pv   ON pv.userid = iv.vendor_id",
            "LEFT JOIN {$prefix}currencies cur  ON cur.id = iv.currency",
        ];

        // Build dynamic WHERE conditions
        $where_parts = [];
        $bindings    = [];

        // Global DataTables search
        $search_value = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        if ($search_value !== '') {
            $like = $this->db->escape_like_str($search_value);
            $where_parts[] = "(i.description LIKE '%{$like}%' OR i.commodity_code LIKE '%{$like}%' OR pv.company LIKE '%{$like}%' OR iv.vendor_item_code LIKE '%{$like}%' OR ig.name LIKE '%{$like}%')";
        }

        // Named filter: Item Name
        if (!empty($filters['item_name'])) {
            $like = $this->db->escape_like_str($filters['item_name']);
            $where_parts[] = "i.description LIKE '%{$like}%'";
        }
        // Named filter: SKU
        if (!empty($filters['sku'])) {
            $like = $this->db->escape_like_str($filters['sku']);
            $where_parts[] = "i.commodity_code LIKE '%{$like}%'";
        }
        // Named filter: Vendor Name
        if (!empty($filters['vendor_name'])) {
            $like = $this->db->escape_like_str($filters['vendor_name']);
            $where_parts[] = "pv.company LIKE '%{$like}%'";
        }
        // Named filter: Vendor SKU
        if (!empty($filters['vendor_sku'])) {
            $like = $this->db->escape_like_str($filters['vendor_sku']);
            $where_parts[] = "iv.vendor_item_code LIKE '%{$like}%'";
        }
        // Named filter: Category
        if (!empty($filters['category'])) {
            $like = $this->db->escape_like_str($filters['category']);
            $where_parts[] = "ig.name LIKE '%{$like}%'";
        }
        // Named filter: Price range
        if (isset($filters['price_min']) && is_numeric($filters['price_min'])) {
            $where_parts[] = "iv.last_purchase_price >= " . (float) $filters['price_min'];
        }
        if (isset($filters['price_max']) && is_numeric($filters['price_max'])) {
            $where_parts[] = "iv.last_purchase_price <= " . (float) $filters['price_max'];
        }

        $where_clause = count($where_parts) > 0 ? 'WHERE ' . implode(' AND ', $where_parts) : '';

        $join_clause = implode(' ', $joins);

        // Count total (unfiltered)
        $total_result = $this->db->query("SELECT COUNT(*) AS cnt FROM {$from} {$join_clause}")->row();
        $records_total = $total_result ? (int) $total_result->cnt : 0;

        // Count filtered
        $filtered_result = $this->db->query("SELECT COUNT(*) AS cnt FROM {$from} {$join_clause} {$where_clause}")->row();
        $records_filtered = $filtered_result ? (int) $filtered_result->cnt : 0;

        // ORDER
        $order_col_index = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) && strtolower($params['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
        $col_map = [
            0 => 'i.description',
            1 => 'i.commodity_code',
            2 => 'ig.name',
            3 => 'pv.company',
            4 => 'iv.vendor_item_code',
            5 => 'iv.last_purchase_price',
            6 => 'iv.is_preferred',
        ];
        $order_col = isset($col_map[$order_col_index]) ? $col_map[$order_col_index] : 'i.description';
        $order_clause = "ORDER BY {$order_col} {$order_dir}";

        // LIMIT
        $start  = isset($params['start'])  ? (int) $params['start']  : 0;
        $length = isset($params['length']) ? (int) $params['length'] : 25;
        $limit_clause = "LIMIT {$start}, {$length}";

        $sql = "SELECT {$select} FROM {$from} {$join_clause} {$where_clause} {$order_clause} {$limit_clause}";
        $rows = $this->db->query($sql)->result_array();

        return [
            'data'            => $rows,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
        ];
    }

    // ========================================================================
    //  INQUIRY FEATURE METHODS
    // ========================================================================

    /**
     * Get the primary contact for a vendor (email + phone).
     *
     * @param  int $vendor_id
     * @return object|null  {email, phonenumber, firstname, lastname, company}
     */
    public function get_vendor_contact($vendor_id)
    {
        $prefix = db_prefix();
        return $this->db->select('pc.email, pc.phonenumber AS contact_phone, pc.firstname, pc.lastname, pv.company, pv.phonenumber AS vendor_phone')
            ->from($prefix . 'pur_contacts pc')
            ->join($prefix . 'pur_vendor pv', 'pv.userid = pc.userid', 'left')
            ->where('pc.userid', (int) $vendor_id)
            ->where('pc.is_primary', 1)
            ->limit(1)
            ->get()
            ->row();
    }

    /**
     * Collect all vendor IDs in the hierarchy starting from a root vendor.
     * Returns flat array of vendor IDs including the root.
     *
     * @param  int $root_vendor_id
     * @return array
     */
    public function get_hierarchy_vendor_ids($root_vendor_id)
    {
        $ids = [(int) $root_vendor_id];
        $this->_collect_hierarchy_ids((int) $root_vendor_id, $ids);
        return array_unique($ids);
    }

    /**
     * Recursive helper to collect descendant vendor IDs.
     */
    private function _collect_hierarchy_ids($parent_id, &$ids)
    {
        $children = $this->db->select('userid')
            ->where('parent_vendor_id', $parent_id)
            ->get(db_prefix() . 'pur_vendor')
            ->result_array();

        foreach ($children as $child) {
            $ids[] = (int) $child['userid'];
            $this->_collect_hierarchy_ids((int) $child['userid'], $ids);
        }
    }

    /**
     * Parse merge fields in a template string.
     *
     * @param  string $template
     * @param  array  $fields  ['item_name' => ..., 'item_sku' => ..., etc.]
     * @return string
     */
    public function parse_template_merge_fields($template, $fields)
    {
        $replacements = [
            '{item_name}'    => isset($fields['item_name'])    ? $fields['item_name']    : '',
            '{item_sku}'     => isset($fields['item_sku'])     ? $fields['item_sku']     : '',
            '{vendor_name}'  => isset($fields['vendor_name'])  ? $fields['vendor_name']  : '',
            '{vendor_code}'  => isset($fields['vendor_code'])  ? $fields['vendor_code']  : '',
            '{sender_name}'  => isset($fields['sender_name'])  ? $fields['sender_name']  : '',
            '{company_name}' => isset($fields['company_name']) ? $fields['company_name'] : get_option('companyname'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Send an inquiry to one or more vendors (email and/or SMS).
     *
     * @param  array $data  {
     *   item_id, send_email, send_sms, send_to_hierarchy,
     *   vendor_id, email_subject, email_body, sms_body
     * }
     * @return array  ['success' => bool, 'sent_count' => int, 'failed_count' => int, 'details' => [...]]
     */
    public function send_inquiry($data)
    {
        $item_id          = (int) ($data['item_id'] ?? 0);
        $vendor_id        = (int) ($data['vendor_id'] ?? 0);
        $send_email       = !empty($data['send_email']);
        $send_sms         = !empty($data['send_sms']);
        $send_hierarchy   = !empty($data['send_to_hierarchy']);
        $email_subject    = $data['email_subject'] ?? '';
        $email_body       = $data['email_body'] ?? '';
        $sms_body         = $data['sms_body'] ?? '';
        $staff_id         = get_staff_user_id();

        // Get item info
        $item = $this->db->select('id, description, commodity_code')
            ->where('id', $item_id)
            ->get(db_prefix() . 'items')
            ->row();

        $item_name = $item ? $item->description : 'Unknown Item';
        $item_sku  = $item ? $item->commodity_code : '';

        // Determine target vendors
        if ($send_hierarchy) {
            $vendor_ids = $this->get_hierarchy_vendor_ids($vendor_id);
        } else {
            $vendor_ids = [$vendor_id];
        }

        $sent_count   = 0;
        $failed_count = 0;
        $details      = [];

        // Get sender info
        $staff = $this->db->select('firstname, lastname')
            ->where('staffid', $staff_id)
            ->get(db_prefix() . 'staff')
            ->row();
        $sender_name = $staff ? trim($staff->firstname . ' ' . $staff->lastname) : 'Staff';

        foreach ($vendor_ids as $vid) {
            $contact = $this->get_vendor_contact($vid);
            $vendor_name  = $contact ? $contact->company : '';
            $vendor_email = $contact ? $contact->email : '';
            $vendor_phone = $contact ? ($contact->contact_phone ?: $contact->vendor_phone) : '';

            // Get vendor code from the item_vendors mapping
            $mapping = $this->db->select('vendor_item_code')
                ->where('item_id', $item_id)
                ->where('vendor_id', $vid)
                ->get(db_prefix() . 'item_vendors')
                ->row();
            $vendor_code = $mapping ? $mapping->vendor_item_code : '';

            // Merge field values
            $merge_fields = [
                'item_name'    => $item_name,
                'item_sku'     => $item_sku,
                'vendor_name'  => $vendor_name,
                'vendor_code'  => $vendor_code,
                'sender_name'  => $sender_name,
                'company_name' => get_option('companyname'),
            ];

            // == SEND EMAIL ==
            if ($send_email && !empty($vendor_email)) {
                $parsed_subject = $this->parse_template_merge_fields($email_subject, $merge_fields);
                $parsed_body    = $this->parse_template_merge_fields($email_body, $merge_fields);

                $CI = &get_instance();
                $CI->load->model('emails_model');

                $success = $CI->emails_model->send_simple_email($vendor_email, $parsed_subject, $parsed_body);

                $log = [
                    'item_id'            => $item_id,
                    'item_name'          => $item_name,
                    'vendor_id'          => $vid,
                    'vendor_name'        => $vendor_name,
                    'vendor_email'       => $vendor_email,
                    'vendor_phone'       => $vendor_phone,
                    'channel'            => 'email',
                    'subject'            => $parsed_subject,
                    'message_body'       => $parsed_body,
                    'sent_to_hierarchy'  => $send_hierarchy ? 1 : 0,
                    'hierarchy_vendor_id'=> $send_hierarchy ? $vendor_id : null,
                    'sent_by'            => $staff_id,
                    'sent_at'            => date('Y-m-d H:i:s'),
                    'status'             => $success ? 'sent' : 'failed',
                    'error_message'      => $success ? null : 'Email delivery failed',
                ];
                $this->log_inquiry($log);

                if ($success) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }

                $details[] = ['vendor' => $vendor_name, 'channel' => 'email', 'status' => $success ? 'sent' : 'failed'];
            }

            // == SEND SMS ==
            if ($send_sms && !empty($vendor_phone)) {
                $parsed_sms = $this->parse_template_merge_fields($sms_body, $merge_fields);

                $sms_result = $this->_send_sms($vendor_phone, $parsed_sms);

                $log = [
                    'item_id'            => $item_id,
                    'item_name'          => $item_name,
                    'vendor_id'          => $vid,
                    'vendor_name'        => $vendor_name,
                    'vendor_email'       => $vendor_email,
                    'vendor_phone'       => $vendor_phone,
                    'channel'            => 'sms',
                    'subject'            => null,
                    'message_body'       => $parsed_sms,
                    'sent_to_hierarchy'  => $send_hierarchy ? 1 : 0,
                    'hierarchy_vendor_id'=> $send_hierarchy ? $vendor_id : null,
                    'sent_by'            => $staff_id,
                    'sent_at'            => date('Y-m-d H:i:s'),
                    'status'             => $sms_result['success'] ? 'sent' : 'failed',
                    'error_message'      => $sms_result['success'] ? null : $sms_result['error'],
                ];
                $this->log_inquiry($log);

                if ($sms_result['success']) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }

                $details[] = ['vendor' => $vendor_name, 'channel' => 'sms', 'status' => $sms_result['success'] ? 'sent' : 'failed'];
            }
        }

        return [
            'success'      => ($sent_count > 0),
            'sent_count'   => $sent_count,
            'failed_count' => $failed_count,
            'details'      => $details,
        ];
    }

    /**
     * Send SMS via configured API gateway.
     *
     * @param  string $phone
     * @param  string $message
     * @return array  ['success' => bool, 'error' => string|null]
     */
    private function _send_sms($phone, $message)
    {
        $api_url   = get_option('vs_inquiry_sms_api_url');
        $api_key   = get_option('vs_inquiry_sms_api_key');
        $sender_id = get_option('vs_inquiry_sms_sender_id');

        if (empty($api_url)) {
            return ['success' => false, 'error' => 'SMS API URL not configured'];
        }

        // Generic SMS API call — adapt to your provider
        $post_data = [
            'api_key'   => $api_key,
            'sender_id' => $sender_id,
            'to'        => $phone,
            'message'   => $message,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => 'cURL error: ' . $err];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => 'HTTP ' . $httpCode . ': ' . substr($response, 0, 500)];
    }

    /**
     * Insert an inquiry log entry.
     *
     * @param  array $data
     * @return int   insert ID
     */
    public function log_inquiry($data)
    {
        $this->db->insert(db_prefix() . 'vendor_inquiry_log', $data);
        return $this->db->insert_id();
    }

    /**
     * Get inquiry logs (server-side DataTables).
     *
     * @param  array $params   DataTables request params
     * @param  array $filters  Named filters: channel, vendor_id, date_from, date_to, status
     * @return array
     */
    public function get_inquiry_logs($params, $filters = [])
    {
        $prefix = db_prefix();
        $table  = $prefix . 'vendor_inquiry_log il';

        $aColumns = [
            'il.sent_at',
            'il.item_name',
            'il.vendor_name',
            'il.channel',
            'il.subject',
            'il.status',
            'il.sent_to_hierarchy',
        ];

        $join = [
            'LEFT JOIN ' . $prefix . 'staff s ON s.staffid = il.sent_by',
        ];

        $where = [];

        if (!empty($filters['channel'])) {
            $where[] = "AND il.channel = " . $this->db->escape($filters['channel']);
        }
        if (!empty($filters['vendor_id'])) {
            $where[] = "AND il.vendor_id = " . (int) $filters['vendor_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "AND il.sent_at >= " . $this->db->escape($filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $where[] = "AND il.sent_at <= " . $this->db->escape($filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['status'])) {
            $where[] = "AND il.status = " . $this->db->escape($filters['status']);
        }

        $sIndexColumn = 'il.id';
        $extraSelect  = [
            'il.id',
            'il.vendor_email',
            'il.vendor_phone',
            'il.message_body',
            'il.error_message',
            'il.hierarchy_vendor_id',
            's.firstname as staff_firstname',
            's.lastname as staff_lastname',
        ];

        $result  = data_tables_init($aColumns, $sIndexColumn, $table, $join, $where, $extraSelect);
        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $row) {
            $channel_badge = $row['channel'] === 'email'
                ? '<span class="label label-info"><i class="fa fa-envelope"></i> Email</span>'
                : '<span class="label label-success"><i class="fa fa-comment"></i> SMS</span>';

            $status_badge = $row['status'] === 'sent'
                ? '<span class="label label-success">Sent</span>'
                : '<span class="label label-danger" title="' . htmlspecialchars($row['error_message'] ?: '') . '">Failed</span>';

            $hierarchy_badge = $row['sent_to_hierarchy']
                ? '<span class="label label-warning"><i class="fa fa-sitemap"></i> Hierarchy</span>'
                : '<span class="label label-default">Single</span>';

            $staff_name = trim(($row['staff_firstname'] ?? '') . ' ' . ($row['staff_lastname'] ?? ''));
            $sent_at = _dt($row['sent_at']);

            $subject_html = htmlspecialchars($row['subject'] ?: '—');

            // View details button
            $view_btn = '<a href="javascript:void(0);" class="btn btn-default btn-xs vs-log-detail-btn" '
                . 'data-log-id="' . $row['id'] . '" '
                . 'data-vendor="' . htmlspecialchars($row['vendor_name'] ?: '') . '" '
                . 'data-email="' . htmlspecialchars($row['vendor_email'] ?: '') . '" '
                . 'data-phone="' . htmlspecialchars($row['vendor_phone'] ?: '') . '" '
                . 'data-channel="' . htmlspecialchars($row['channel']) . '" '
                . 'data-subject="' . htmlspecialchars($row['subject'] ?: '') . '" '
                . 'data-body="' . htmlspecialchars($row['message_body'] ?: '') . '" '
                . 'data-status="' . htmlspecialchars($row['status']) . '" '
                . 'data-error="' . htmlspecialchars($row['error_message'] ?: '') . '" '
                . 'data-staff="' . htmlspecialchars($staff_name) . '" '
                . 'data-date="' . htmlspecialchars($row['sent_at']) . '" '
                . 'title="View Details"><i class="fa fa-eye"></i></a>';

            $output['aaData'][] = [
                $sent_at,
                htmlspecialchars($row['item_name'] ?: ''),
                htmlspecialchars($row['vendor_name'] ?: ''),
                $channel_badge,
                $subject_html,
                $status_badge . ' ' . $hierarchy_badge,
                $view_btn,
            ];
        }

        return $output;
    }

    /**
     * Get inquiry log statistics for a summary panel.
     *
     * @return array
     */
    public function get_inquiry_stats()
    {
        $prefix = db_prefix();
        $table  = $prefix . 'vendor_inquiry_log';

        $total   = (int) $this->db->count_all($table);
        $emails  = (int) $this->db->where('channel', 'email')->count_all_results($table);
        $sms     = (int) $this->db->where('channel', 'sms')->count_all_results($table);
        $sent    = (int) $this->db->where('status', 'sent')->count_all_results($table);
        $failed  = (int) $this->db->where('status', 'failed')->count_all_results($table);
        $today   = (int) $this->db->where('DATE(sent_at)', date('Y-m-d'))->count_all_results($table);

        return compact('total', 'emails', 'sms', 'sent', 'failed', 'today');
    }
}
