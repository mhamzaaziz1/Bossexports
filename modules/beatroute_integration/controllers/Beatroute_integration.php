<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Beatroute_integration extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        $this->load->model('beatroute_integration/beatroute_model');
    }

    /**
     * Default method - redirect to SKUs page
     */
    public function index()
    {
        redirect(admin_url('beatroute_integration/skus'));
    }

    /**
     * Display SKUs page
     */
    public function skus()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        $data['title'] = _l('skus');
        $data['skus'] = $this->beatroute_model->get_skus();

        // Get search parameters
        $search = $this->input->get('search');
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 25;

        // Get live data from Beatroute using v2 API with pagination
        $data['live_skus'] = $this->beatroute_model->get_beatroute_skus_v2($page, $limit);

        // Pass search parameters to the view
        $data['search'] = $search;
        $data['page'] = $page;
        $data['limit'] = $limit;

        // Calculate pagination from the API response
        $data['total_pages'] = isset($data['live_skus']['pagination']['pageCount']) ? $data['live_skus']['pagination']['pageCount'] : 1;
        $data['total_items'] = isset($data['live_skus']['pagination']['totalCount']) ? $data['live_skus']['pagination']['totalCount'] : 0;

        $this->load->view('beatroute_integration/skus', $data);
    }

    /**
     * Display Beatroute Live SKUs page
     */
    public function live_skus()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        // Load the beatroute helper to ensure init_footer() is available
        $this->load->helper('beatroute_integration/beatroute');

        $data['title'] = _l('beatroute_live_skus');

        // Get search parameters
        $search = $this->input->get('search');
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 25;

        // Get live data from Beatroute v2 API
        $data['live_skus'] = $this->beatroute_model->get_beatroute_skus_v2($page, $limit);

        // Pass search parameters to the view
        $data['search'] = $search;
        $data['page'] = $page;
        $data['limit'] = $limit;

        // Calculate pagination from the API response
        $data['total_pages'] = isset($data['live_skus']['pagination']['pageCount']) ? $data['live_skus']['pagination']['pageCount'] : 1;
        $data['total_items'] = isset($data['live_skus']['pagination']['totalCount']) ? $data['live_skus']['pagination']['totalCount'] : 0;

        $this->load->view('beatroute_integration/live_skus', $data);
    }

    /**
     * Display customers page
     */
    public function customers()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        $data['title'] = _l('customers');
        $data['customers'] = $this->beatroute_model->get_customers();

        // Get search parameters
        $search = $this->input->get('search');
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 25;

        // Get live data from Beatroute using v2 API with pagination
        $data['live_customers'] = $this->beatroute_model->get_beatroute_customers_v2($page, $limit);

        // Pass search parameters to the view
        $data['search'] = $search;
        $data['page'] = $page;
        $data['limit'] = $limit;

        // Calculate pagination from the API response
        $data['total_pages'] = isset($data['live_customers']['pagination']['pageCount']) ? $data['live_customers']['pagination']['pageCount'] : 1;
        $data['total_items'] = isset($data['live_customers']['pagination']['totalCount']) ? $data['live_customers']['pagination']['totalCount'] : 0;

        $this->load->view('beatroute_integration/customers', $data);
    }

    /**
     * Display Beatroute Live Customers page
     */
    public function live_customers()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        // Load the beatroute helper to ensure init_footer() is available
        $this->load->helper('beatroute_integration/beatroute');

        $data['title'] = _l('beatroute_live_customers');

        // Get search parameters
        $search = $this->input->get('search');
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 25;

        // Get live data from Beatroute v2 API
        $data['live_customers'] = $this->beatroute_model->get_beatroute_customers_v2($page, $limit);

        // Pass search parameters to the view
        $data['search'] = $search;
        $data['page'] = $page;
        $data['limit'] = $limit;

        // Calculate pagination from the API response
        $data['total_pages'] = isset($data['live_customers']['pagination']['pageCount']) ? $data['live_customers']['pagination']['pageCount'] : 1;
        $data['total_items'] = isset($data['live_customers']['pagination']['totalCount']) ? $data['live_customers']['pagination']['totalCount'] : 0;

        $this->load->view('beatroute_integration/live_customers', $data);
    }

    /**
     * Display invoices page
     */
    public function invoices()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        $data['title'] = _l('invoices');
        $data['invoices'] = $this->beatroute_model->get_invoices();

        // Get live data from Beatroute
        $data['live_invoices'] = $this->beatroute_model->get_beatroute_invoices();

        $this->load->view('beatroute_integration/invoices', $data);
    }

    /**
     * Display payments page
     */
    public function payments()
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            access_denied('beatroute_integration');
        }

        $data['title'] = _l('payments');
        $data['payments'] = $this->beatroute_model->get_payments();

        // Get live data from Beatroute
        $data['live_payments'] = $this->beatroute_model->get_beatroute_payments();

        $this->load->view('beatroute_integration/payments', $data);
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        if (!is_admin()) {
            access_denied('beatroute_integration');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            update_option('beatroute_integration_enabled', $data['beatroute_integration_enabled']);
            update_option('beatroute_auto_sync_enabled', $data['beatroute_auto_sync_enabled']);
            update_option('beatroute_webhook_enabled', $data['beatroute_webhook_enabled']);
            update_option('beatroute_sync_interval', $data['beatroute_sync_interval']);

            // Update API configuration
            $api_config = [
                'api_key' => $data['api_key'],
                'api_secret' => '', // No longer used, but kept for backward compatibility
                'api_url' => $data['api_url'],
                'webhook_secret' => $data['webhook_secret'],
                'active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('active', 1);
            $existing_config = $this->db->get(db_prefix() . 'beatroute_api_config')->row();

            if ($existing_config) {
                $this->db->where('id', $existing_config->id);
                $this->db->update(db_prefix() . 'beatroute_api_config', $api_config);
            } else {
                $api_config['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert(db_prefix() . 'beatroute_api_config', $api_config);
            }

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('beatroute_integration/settings'));
        }

        $this->db->where('active', 1);
        $data['api_config'] = $this->db->get(db_prefix() . 'beatroute_api_config')->row();

        $data['title'] = _l('settings');

        $this->load->view('beatroute_integration/settings', $data);
    }

    /**
     * Test connection to Beatroute API
     */
    public function test_connection()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $api_key = $this->input->post('api_key');
        $api_url = $this->input->post('api_url');

        if (empty($api_key) || empty($api_url)) {
            echo json_encode([
                'success' => false,
                'message' => _l('api_credentials_missing')
            ]);
            return;
        }

        // Create a temporary client for testing
        $client = new stdClass();
        $client->api_key = $api_key;
        $client->api_url = $api_url;

        // Test connection by making a simple request
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $client->api_key,
        ];

        $url = rtrim($client->api_url, '/') . '/ping';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo json_encode([
                'success' => false,
                'message' => 'cURL Error: ' . curl_error($ch)
            ]);
            curl_close($ch);
            return;
        }

        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            echo json_encode([
                'success' => true,
                'message' => _l('connection_successful')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('connection_failed') . ' (HTTP ' . $http_code . ')'
            ]);
        }
    }

    /**
     * Get SKU details for modal
     */
    public function get_sku_details($id)
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            ajax_access_denied();
        }

        $sku = $this->beatroute_model->get_sku_by_id($id);

        if (!$sku) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'SKU not found']);
            die();
        }

        echo json_encode($sku);
    }

    /**
     * Get customer details for modal
     */
    public function get_customer_details($id)
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            ajax_access_denied();
        }

        $customer = $this->beatroute_model->get_customer_by_id($id);

        if (!$customer) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Customer not found']);
            die();
        }

        echo json_encode($customer);
    }

    /**
     * Get invoice details for modal
     */
    public function get_invoice_details($id)
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            ajax_access_denied();
        }

        $invoice = $this->beatroute_model->get_invoice_by_id($id);

        if (!$invoice) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Invoice not found']);
            die();
        }

        $customer = $this->beatroute_model->get_customer_by_beatroute_id($invoice->beatroute_customer_id);
        $customer_name = $customer ? $customer->first_name . ' ' . $customer->last_name : $invoice->beatroute_customer_id;

        $items = $this->db->where('beatroute_invoice_id', $invoice->beatroute_id)
                          ->get(db_prefix() . 'beatroute_invoice_items')
                          ->result_array();

        echo json_encode([
            'invoice' => $invoice,
            'items' => $items,
            'customer_name' => $customer_name
        ]);
    }

    /**
     * Get payment details for modal
     */
    public function get_payment_details($id)
    {
        if (!has_permission('beatroute_integration', '', 'view')) {
            ajax_access_denied();
        }

        $payment = $this->beatroute_model->get_payment_by_id($id);

        if (!$payment) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Payment not found']);
            die();
        }

        $invoice = $this->beatroute_model->get_invoice_by_beatroute_id($payment->beatroute_invoice_id);
        $invoice_number = $invoice ? $invoice->invoice_number : $payment->beatroute_invoice_id;
        $currency = $invoice ? $invoice->currency : '';

        echo json_encode([
            'payment' => $payment,
            'invoice_number' => $invoice_number,
            'currency' => $currency
        ]);
    }

    /**
     * Sync data with Beatroute
     */
    public function sync()
    {
        if (!has_permission('beatroute_integration', '', 'edit')) {
            access_denied('beatroute_integration');
        }

        $type = $this->input->get('type');
        $id = $this->input->get('id'); // Get the ID parameter from the URL
        $success = false;

        switch ($type) {
            case 'skus':
                if ($id) {
                    // Sync individual SKU
                    $success = $this->beatroute_model->sync_individual_sku($id);
                } else {
                    // Sync all SKUs
                    $success = $this->beatroute_model->sync_skus();
                }
                break;
            case 'customers':
                if ($id) {
                    // Sync individual customer
                    $success = $this->beatroute_model->sync_individual_customer($id);
                } else {
                    // Sync all customers
                    $success = $this->beatroute_model->sync_customers();
                }
                break;
            case 'invoices':
                $success = $this->beatroute_model->sync_invoices();
                break;
            case 'payments':
                $success = $this->beatroute_model->sync_payments();
                break;
            case 'all':
                $success = $this->beatroute_model->sync_skus() &&
                           $this->beatroute_model->sync_customers() &&
                           $this->beatroute_model->sync_invoices() &&
                           $this->beatroute_model->sync_payments();
                break;
        }

        if ($success) {
            update_beatroute_last_sync_time();
            set_alert('success', _l('sync_successful'));
        } else {
            set_alert('warning', _l('sync_failed'));
        }

        redirect(admin_url('beatroute_integration/' . ($type != 'all' ? $type : 'skus')));
    }

    /**
     * Push data to Beatroute
     */
    public function push()
    {
        if (!has_permission('beatroute_integration', '', 'edit')) {
            access_denied('beatroute_integration');
        }

        $type = $this->input->get('type');
        $id = $this->input->get('id');
        $success = false;

        switch ($type) {
            case 'sku':
                $sku = $this->beatroute_model->get_sku_by_beatroute_id($id);
                if ($sku) {
                    $data = [
                        'name' => $sku->name,
                        'description' => $sku->description,
                        'price' => $sku->price,
                        'sku_code' => $sku->sku_code
                    ];
                    $success = $this->beatroute_model->push_sku_to_beatroute($data);
                }
                break;
            case 'customer':
                $customer = $this->beatroute_model->get_customer_by_beatroute_id($id);
                if ($customer) {
                    $data = [
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                        'city' => $customer->city,
                        'state' => $customer->state,
                        'zip' => $customer->zip,
                        'country' => $customer->country
                    ];
                    $success = $this->beatroute_model->push_customer_to_beatroute($data);
                }
                break;
            case 'invoice':
                $invoice = $this->beatroute_model->get_invoice_by_beatroute_id($id);
                if ($invoice) {
                    $data = [
                        'customer_id' => $invoice->beatroute_customer_id,
                        'invoice_number' => $invoice->invoice_number,
                        'date' => $invoice->date,
                        'due_date' => $invoice->due_date,
                        'currency' => $invoice->currency,
                        'subtotal' => $invoice->subtotal,
                        'total' => $invoice->total
                    ];
                    $success = $this->beatroute_model->push_invoice_to_beatroute($data);
                }
                break;
            case 'payment':
                $payment = $this->beatroute_model->get_payment_by_beatroute_id($id);
                if ($payment) {
                    $data = [
                        'invoice_id' => $payment->beatroute_invoice_id,
                        'amount' => $payment->amount,
                        'payment_mode' => $payment->payment_mode,
                        'payment_date' => $payment->payment_date,
                        'transaction_id' => $payment->transaction_id
                    ];
                    $success = $this->beatroute_model->push_payment_to_beatroute($data);
                }
                break;
        }

        if ($success) {
            set_alert('success', _l('push_successful'));
        } else {
            set_alert('warning', _l('push_failed'));
        }

        redirect(admin_url('beatroute_integration/' . $type . 's'));
    }

    /**
     * Handle webhook requests from Beatroute
     */
    public function webhook()
    {
        if (!is_beatroute_webhook_enabled()) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Webhooks are disabled']);
            die();
        }

        $payload = file_get_contents('php://input');
        $signature = $this->input->get_request_header('X-Beatroute-Signature');

        if (!verify_beatroute_webhook_signature($payload, $signature)) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Invalid signature']);
            die();
        }

        $data = json_decode($payload, true);

        if (!isset($data['event']) || !isset($data['data'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Invalid payload']);
            die();
        }

        $event = $data['event'];
        $entity_data = $data['data'];

        switch ($event) {
            case 'sku.created':
            case 'sku.updated':
                // Validate that required fields are present
                if (!isset($entity_data['id']) || empty($entity_data['id'])) {
                    log_activity('Webhook SKU sync failed: Missing or empty ID in webhook data');
                    break;
                }

                $sku_data = [
                    'beatroute_id' => $entity_data['id'],
                    'name' => isset($entity_data['name']) ? $entity_data['name'] : '',
                    'description' => isset($entity_data['description']) ? $entity_data['description'] : '',
                    'price' => isset($entity_data['price']) ? $entity_data['price'] : 0,
                    'sku_code' => isset($entity_data['sku_code']) ? $entity_data['sku_code'] : '',
                    'status' => isset($entity_data['status']) ? $entity_data['status'] : 'active',
                    'sync_status' => 'synced'
                ];

                if ($this->beatroute_model->is_sku_exists($entity_data['id'])) {
                    $this->beatroute_model->update_sku($entity_data['id'], $sku_data);
                } else {
                    $this->beatroute_model->add_sku($sku_data);
                }
                break;

            case 'customer.created':
            case 'customer.updated':
                // Validate that required fields are present
                if (!isset($entity_data['id']) || empty($entity_data['id'])) {
                    log_activity('Webhook customer sync failed: Missing or empty ID in webhook data');
                    break;
                }

                $customer_data = [
                    'beatroute_id' => $entity_data['id'],
                    'first_name' => isset($entity_data['first_name']) ? $entity_data['first_name'] : '',
                    'last_name' => isset($entity_data['last_name']) ? $entity_data['last_name'] : '',
                    'email' => isset($entity_data['email']) ? $entity_data['email'] : '',
                    'phone' => isset($entity_data['phone']) ? $entity_data['phone'] : '',
                    'address' => isset($entity_data['address']) ? $entity_data['address'] : '',
                    'city' => isset($entity_data['city']) ? $entity_data['city'] : '',
                    'state' => isset($entity_data['state']) ? $entity_data['state'] : '',
                    'zip' => isset($entity_data['zip']) ? $entity_data['zip'] : '',
                    'country' => isset($entity_data['country']) ? $entity_data['country'] : '',
                    'status' => isset($entity_data['status']) ? $entity_data['status'] : 'active',
                    'sync_status' => 'synced'
                ];

                if ($this->beatroute_model->is_customer_exists($entity_data['id'])) {
                    $this->beatroute_model->update_customer($entity_data['id'], $customer_data);
                } else {
                    $this->beatroute_model->add_customer($customer_data);
                }
                break;

            case 'invoice.created':
            case 'invoice.updated':
                // Validate that required fields are present
                if (!isset($entity_data['id']) || empty($entity_data['id'])) {
                    log_activity('Webhook invoice sync failed: Missing or empty ID in webhook data');
                    break;
                }

                if (!isset($entity_data['customer_id']) || empty($entity_data['customer_id'])) {
                    log_activity('Webhook invoice sync failed: Missing or empty customer_id in webhook data');
                    break;
                }

                $invoice_data = [
                    'beatroute_id' => $entity_data['id'],
                    'beatroute_customer_id' => $entity_data['customer_id'],
                    'invoice_number' => isset($entity_data['invoice_number']) ? $entity_data['invoice_number'] : '',
                    'date' => isset($entity_data['date']) ? $entity_data['date'] : date('Y-m-d'),
                    'due_date' => isset($entity_data['due_date']) ? $entity_data['due_date'] : date('Y-m-d'),
                    'currency' => isset($entity_data['currency']) ? $entity_data['currency'] : '',
                    'subtotal' => isset($entity_data['subtotal']) ? $entity_data['subtotal'] : 0,
                    'total' => isset($entity_data['total']) ? $entity_data['total'] : 0,
                    'status' => isset($entity_data['status']) ? $entity_data['status'] : 'draft',
                    'sync_status' => 'synced'
                ];

                if ($this->beatroute_model->is_invoice_exists($entity_data['id'])) {
                    $this->beatroute_model->update_invoice($entity_data['id'], $invoice_data);
                } else {
                    $invoice_id = $this->beatroute_model->add_invoice($invoice_data);

                    // Add invoice items
                    if ($invoice_id && isset($entity_data['items']) && is_array($entity_data['items'])) {
                        foreach ($entity_data['items'] as $item) {
                            // Validate item data
                            if (!isset($item['sku_id']) || empty($item['sku_id'])) {
                                log_activity('Webhook invoice item sync failed: Missing or empty sku_id in item data');
                                continue; // Skip this item and continue with the next one
                            }

                            $item_data = [
                                'beatroute_invoice_id' => $entity_data['id'],
                                'beatroute_sku_id' => $item['sku_id'],
                                'description' => isset($item['description']) ? $item['description'] : '',
                                'qty' => isset($item['quantity']) ? $item['quantity'] : 1,
                                'rate' => isset($item['rate']) ? $item['rate'] : 0
                            ];

                            $this->beatroute_model->add_invoice_item($item_data);
                        }
                    }
                }
                break;

            case 'payment.created':
            case 'payment.updated':
                // Validate that required fields are present
                if (!isset($entity_data['id']) || empty($entity_data['id'])) {
                    log_activity('Webhook payment sync failed: Missing or empty ID in webhook data');
                    break;
                }

                if (!isset($entity_data['invoice_id']) || empty($entity_data['invoice_id'])) {
                    log_activity('Webhook payment sync failed: Missing or empty invoice_id in webhook data');
                    break;
                }

                $payment_data = [
                    'beatroute_id' => $entity_data['id'],
                    'beatroute_invoice_id' => $entity_data['invoice_id'],
                    'amount' => isset($entity_data['amount']) ? $entity_data['amount'] : 0,
                    'payment_mode' => isset($entity_data['payment_mode']) ? $entity_data['payment_mode'] : '',
                    'payment_date' => isset($entity_data['payment_date']) ? $entity_data['payment_date'] : date('Y-m-d'),
                    'transaction_id' => isset($entity_data['transaction_id']) ? $entity_data['transaction_id'] : '',
                    'status' => isset($entity_data['status']) ? $entity_data['status'] : 'pending',
                    'sync_status' => 'synced'
                ];

                if ($this->beatroute_model->is_payment_exists($entity_data['id'])) {
                    $this->beatroute_model->update_payment($entity_data['id'], $payment_data);
                } else {
                    $this->beatroute_model->add_payment($payment_data);
                }
                break;
        }

        echo json_encode(['success' => true]);
        die();
    }
}
