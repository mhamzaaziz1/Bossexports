<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Beatroute_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('beatroute_integration/beatroute');
    }

    /**
     * Get all SKUs from Beatroute
     *
     * @return array
     */
    public function get_beatroute_skus()
    {
        log_activity('Starting get_beatroute_skus()');

        // Get the current API configuration
        $config = get_beatroute_api_config();
        $original_url = $config->api_url;

        log_activity('Original API URL: ' . $original_url);

        // Temporarily set the API URL to int/v2 endpoint
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => 'https://api.beatroute.io/int/v2']);

        log_activity('Temporarily set API URL to: https://api.beatroute.io/int/v2');

        // Make the API request to the sku endpoint with empty POST fields
        // This matches the Postman request structure
        $data = []; // Add any required POST fields here if needed
        $result = beatroute_api_request('sku', 'POST', $data);

        log_activity('API request made to endpoint: sku with method: POST');
        log_activity('API response: ' . json_encode($result));

        // Restore the original API URL
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => $original_url]);

        log_activity('Restored API URL to: ' . $original_url);

        // Validate the API response structure
        if (!$result) {
            log_activity('Beatroute API request failed: Empty response');
            return [];
        }

        // Check if the response has the expected structure
        if (!isset($result['data']) || !is_array($result['data'])) {
            log_activity('Beatroute API response has unexpected structure: ' . json_encode($result));
            return [];
        }

        log_activity('API response has expected structure with ' . count($result['data']) . ' SKUs');

        // Return the data array from the response
        return $result['data'];
    }

    /**
     * Get SKUs from Beatroute v2 API with pagination and search
     *
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $search Search term
     * @return array
     */
    public function get_beatroute_skus_v2($page = "", $limit = 100)
    {
        $data = [
            'page' => $page,
            'limit' => $limit
        ];

        // Override the API URL to use v2 endpoint
        $config = get_beatroute_api_config();
        $original_url = $config->api_url;

        // Temporarily set the API URL to v2
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => 'https://api.beatroute.io/int/v2']);

        if($page == ""){
            // If no page is specified, fetch all pages and combine results
            $combinedResults = [
                'success' => true,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 0,
                    'currentPage' => 1,
                    'perPage' => $limit
                ]
            ];

            for ($currentPage = 1; $currentPage <= 100; $currentPage++) {
                $pageData = [
                    'page' => $currentPage,
                    'limit' => $limit
                ];

                $pageResult = beatroute_api_request('sku?page=' . $currentPage . '&limit=' . $limit, 'post', $pageData);

                if (empty($pageResult) || !isset($pageResult['data']) || empty($pageResult['data'])) {
                    break; // Stop if no results are returned or invalid response
                }

                // Combine data from this page with previous results
                $combinedResults['data'] = array_merge($combinedResults['data'], $pageResult['data']);

                // Update pagination info
                if (isset($pageResult['pagination'])) {
                    $combinedResults['pagination'] = $pageResult['pagination'];

                    // If we've reached the last page, stop
                    if ($currentPage >= $pageResult['pagination']['pageCount']) {
                        break;
                    }
                }
            }

            $result = $combinedResults;
        } else {
            // Make the API request for a specific page
            $result = beatroute_api_request('sku?page='.$page.'&limit='.$limit, 'post', $data);
        }

        // Restore the original API URL
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => $original_url]);

        // Validate the API response structure
        if (!$result) {
            log_activity('Beatroute API request failed: Empty response');
            return [
                'success' => false,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 1,
                    'currentPage' => 1,
                    'perPage' => 25
                ]
            ];
        }

        // Check if the response has the expected structure
        if (!isset($result['data']) || !is_array($result['data'])) {
            log_activity('Beatroute API response has unexpected structure: ' . json_encode($result));

            // If we have a success key but data is not in the expected format
            if (isset($result['success']) && $result['success'] === true && isset($result['data'])) {
                // Try to adapt the response to the expected format
                return [
                    'success' => true,
                    'data' => is_array($result['data']) ? $result['data'] : [$result['data']],
                    'pagination' => isset($result['pagination']) ? $result['pagination'] : [
                        'totalCount' => count(is_array($result['data']) ? $result['data'] : [$result['data']]),
                        'pageCount' => 1,
                        'currentPage' => $page,
                        'perPage' => 25
                    ]
                ];
            }

            // Return a default structure
            return [
                'success' => false,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 1,
                    'currentPage' => 1,
                    'perPage' => 25
                ]
            ];
        }

        return $result;
    }

    /**
     * Get all customers from Beatroute
     *
     * @return array
     */
    public function get_beatroute_customers()
    {
        $result = beatroute_api_request('customers');
        return $result ? $result : [];
    }

    /**
     * Get customers from Beatroute v2 API with pagination and search
     *
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $search Search term
     * @return array
     */
    public function get_beatroute_customers_v2($page = "", $limit = 100)
    {
        $data = [
            'page' => $page,
            'limit' => $limit
        ];

        // Override the API URL to use v2 endpoint
        $config = get_beatroute_api_config();
        $original_url = $config->api_url;

        // Temporarily set the API URL to v2
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => 'https://api.beatroute.io/int/v2']);

        if($page == ""){
            // If no page is specified, fetch all pages and combine results
            $combinedResults = [
                'success' => true,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 0,
                    'currentPage' => 1,
                    'perPage' => $limit
                ]
            ];

            for ($currentPage = 1; $currentPage <= 100; $currentPage++) {
                $pageData = [
                    'page' => $currentPage,
                    'limit' => $limit
                ];

                $pageResult = beatroute_api_request('customer/index?page=' . $currentPage . '&limit=' . $limit, 'post', $pageData);

                if (empty($pageResult) || !isset($pageResult['data']) || empty($pageResult['data'])) {
                    break; // Stop if no results are returned or invalid response
                }

                // Combine data from this page with previous results
                $combinedResults['data'] = array_merge($combinedResults['data'], $pageResult['data']);

                // Update pagination info
                if (isset($pageResult['pagination'])) {
                    $combinedResults['pagination'] = $pageResult['pagination'];

                    // If we've reached the last page, stop
                    if ($currentPage >= $pageResult['pagination']['pageCount']) {
                        break;
                    }
                }
            }

            $result = $combinedResults;
        } else {
            // Make the API request for a specific page
            $result = beatroute_api_request('customer/index?page='.$page.'&limit='.$limit, 'post', $data);
        }

        // Restore the original API URL
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => $original_url]);

        // Validate the API response structure
        if (!$result) {
            log_activity('Beatroute API request failed: Empty response');
            return [
                'success' => false,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 1,
                    'currentPage' => 1,
                    'perPage' => 25
                ]
            ];
        }

        // Check if the response has the expected structure
        if (!isset($result['data']) || !is_array($result['data'])) {
            log_activity('Beatroute API response has unexpected structure: ' . json_encode($result));

            // If we have a success key but data is not in the expected format
            if (isset($result['success']) && $result['success'] === true && isset($result['data'])) {
                // Try to adapt the response to the expected format
                return [
                    'success' => true,
                    'data' => is_array($result['data']) ? $result['data'] : [$result['data']],
                    'pagination' => isset($result['pagination']) ? $result['pagination'] : [
                        'totalCount' => count(is_array($result['data']) ? $result['data'] : [$result['data']]),
                        'pageCount' => 1,
                        'currentPage' => $page,
                        'perPage' => 25
                    ]
                ];
            }

            // Return a default structure
            return [
                'success' => false,
                'data' => [],
                'pagination' => [
                    'totalCount' => 0,
                    'pageCount' => 1,
                    'currentPage' => 1,
                    'perPage' => 25
                ]
            ];
        }

        return $result;
    }

    /**
     * Get all invoices from Beatroute
     *
     * @return array
     */
    public function get_beatroute_invoices()
    {
        $result = beatroute_api_request('invoices');
        return $result ? $result : [];
    }

    /**
     * Get all payments from Beatroute
     *
     * @return array
     */
    public function get_beatroute_payments()
    {
        $result = beatroute_api_request('payments');
        return $result ? $result : [];
    }

    /**
     * Check if SKU exists in local database
     *
     * @param string $beatroute_id
     * @return bool
     */
    public function is_sku_exists($beatroute_id)
    {
        $count = total_rows(BEATROUTE_SKUS_TABLE, ['sku_external_id' => $beatroute_id]);
        return $count > 0;
    }

    /**
     * Check if customer exists in local database
     *
     * @param string $beatroute_id
     * @return bool
     */
    public function is_customer_exists($beatroute_id)
    {
        return (bool) total_rows(BEATROUTE_CUSTOMERS_TABLE, ['external_id' => $beatroute_id]) > 0;
    }

    /**
     * Check if invoice exists in local database
     *
     * @param string $beatroute_id
     * @return bool
     */
    public function is_invoice_exists($beatroute_id)
    {
        return (bool) total_rows(BEATROUTE_INVOICES_TABLE, ['beatroute_id' => $beatroute_id]) > 0;
    }

    /**
     * Check if payment exists in local database
     *
     * @param string $beatroute_id
     * @return bool
     */
    public function is_payment_exists($beatroute_id)
    {
        return (bool) total_rows(BEATROUTE_PAYMENTS_TABLE, ['beatroute_id' => $beatroute_id]) > 0;
    }

    /**
     * Add SKU to local database
     *
     * @param array $data
     * @return int|bool
     */
    public function add_sku($data)
    {
        // Ensure sku_external_id is set
        if (!isset($data['sku_external_id']) || empty($data['sku_external_id'])) {
            log_activity('Failed to add SKU: sku_external_id is required and cannot be null');
            return false;
        }

        // Check if SKU already exists
        if ($this->is_sku_exists($data['sku_external_id'])) {
            log_activity('Failed to add SKU: SKU with sku_external_id ' . $data['sku_external_id'] . ' already exists');
            return false;
        }

        // Set date fields
        $data['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(BEATROUTE_SKUS_TABLE, $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            $insert_id = $this->db->insert_id();
            log_beatroute_sync('sku', $data['sku_external_id'], 'create', 'success');
            return $insert_id;
        }

        log_activity('SKU insert failed for sku_external_id: ' . $data['sku_external_id'] . '. No rows affected.');
        return false;
    }

    /**
     * Update SKU in local database
     *
     * @param string $beatroute_id
     * @param array $data
     * @return bool
     */
    public function update_sku($beatroute_id, $data)
    {
        // Check if the SKU exists before updating
        $existing_sku = $this->db->where('sku_external_id', $beatroute_id)->get(BEATROUTE_SKUS_TABLE)->row();
        if (!$existing_sku) {
            log_activity('Failed to update SKU: No SKU found with sku_external_id ' . $beatroute_id);
            return false;
        }

        $this->db->where('sku_external_id', $beatroute_id);
        $this->db->update(BEATROUTE_SKUS_TABLE, $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            log_beatroute_sync('sku', $beatroute_id, 'update', 'success');
            return true;
        }

        log_activity('SKU update failed for sku_external_id: ' . $beatroute_id . '. No rows affected.');
        return false;
    }

    /**
     * Add customer to local database
     *
     * @param array $data
     * @return int|bool
     */
    public function add_customer($data)
    {
        $address_parts = [];
        if (isset($data['locality']) && !empty($data['locality'])) $address_parts[] = $data['locality'];
        if (isset($data['street']) && !empty($data['street'])) $address_parts[] = $data['street'];
        if (isset($data['district']) && !empty($data['district'])) $address_parts[] = $data['district'];

        $combined_address = implode(', ', $address_parts);
        // Map Beatroute customer data to clients table fields
        $client_data = [
            'company' => 'Beatroute_' . $data['external_id'],
            'datecreated' => date('Y-m-d H:i:s'),
            'active' => 1,
            'phonenumber' => isset($data['mobile']) ? $data['mobile'] : '',
            'address' => $combined_address,
            'city' => isset($data['city']) ? $data['city'] : '',
            'state' => isset($data['state']) ? $data['state'] : '',
            'zip' => isset($data['pincode']) ? $data['pincode'] : '',
            'country' => isset($data['country']) ? $data['country'] : '',
            'last_status_change' => date('Y-m-d H:i:s')
        ];

        // Insert client record
        $this->db->insert(BEATROUTE_CUSTOMERS_TABLE, $client_data);

        if ($this->db->affected_rows() > 0) {
            $client_id = $this->db->insert_id();

            // Add contact with email if provided
            if (isset($data['email']) && !empty($data['email'])) {
                $contact_data = [
                    'userid' => $client_id,
                    'is_primary' => 1,
                    'firstname' => $data['first_name'] ?? '',
                    'lastname' => $data['last_name'] ?? '',
                    'email' => $data['email'],
                    'phonenumber' => isset($data['mobile']) ? $data['mobile'] : '',
                    'datecreated' => date('Y-m-d H:i:s')
                ];

                $this->db->insert(db_prefix() . 'contacts', $contact_data);
            }

            log_beatroute_sync('customer', $data['external_id'], 'create', 'success');
            return $client_id;
        }

        return false;
    }

    /**
     * Update customer in local database
     *
     * @param string $beatroute_id
     * @param array $data
     * @return bool
     */
    public function update_customer($beatroute_id, $data)
    {
        // Map Beatroute customer data to clients table fields
        $client_data = [
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'zip' => $data['zip'] ?? '',
            'country' => $data['country'] ?? '',
            'phonenumber' => isset($data['mobile']) ? $data['mobile'] : '',
            'last_status_change' => date('Y-m-d H:i:s')
        ];

        // Update client record
        $this->db->where('company', 'Beatroute_' . $beatroute_id);
        $this->db->update(BEATROUTE_CUSTOMERS_TABLE, $client_data);

        $affected_rows = $this->db->affected_rows();

        // Get the client ID
        $this->db->where('company', 'Beatroute_' . $beatroute_id);
        $client = $this->db->get(BEATROUTE_CUSTOMERS_TABLE)->row();

        if ($client) {
            // Update or create contact with email if provided
            if (isset($data['email']) && !empty($data['email'])) {
                // Check if contact exists
                $this->db->where('userid', $client->userid);
                $this->db->where('is_primary', 1);
                $contact = $this->db->get(db_prefix() . 'contacts')->row();

                $contact_data = [
                    'firstname' => $data['first_name'] ?? '',
                    'lastname' => $data['last_name'] ?? '',
                    'email' => $data['email'],
                    'phonenumber' => isset($data['mobile']) ? $data['mobile'] : ''
                ];

                if ($contact) {
                    // Update existing contact
                    $this->db->where('id', $contact->id);
                    $this->db->update(db_prefix() . 'contacts', $contact_data);
                    if ($this->db->affected_rows() > 0) {
                        $affected_rows++;
                    }
                } else {
                    // Create new contact
                    $contact_data['userid'] = $client->userid;
                    $contact_data['is_primary'] = 1;
                    $contact_data['datecreated'] = date('Y-m-d H:i:s');

                    $this->db->insert(db_prefix() . 'contacts', $contact_data);
                    if ($this->db->affected_rows() > 0) {
                        $affected_rows++;
                    }
                }
            }

            if ($affected_rows > 0) {
                log_beatroute_sync('customer', $beatroute_id, 'update', 'success');
                return true;
            }
        }

        return false;
    }

    /**
     * Add invoice to local database
     *
     * @param array $data
     * @return int|bool
     */
    public function add_invoice($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['last_sync'] = date('Y-m-d H:i:s');

        $this->db->insert(BEATROUTE_INVOICES_TABLE, $data);

        if ($this->db->affected_rows() > 0) {
            $insert_id = $this->db->insert_id();
            log_beatroute_sync('invoice', $data['beatroute_id'], 'create', 'success');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update invoice in local database
     *
     * @param string $beatroute_id
     * @param array $data
     * @return bool
     */
    public function update_invoice($beatroute_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['last_sync'] = date('Y-m-d H:i:s');

        $this->db->where('beatroute_id', $beatroute_id);
        $this->db->update(BEATROUTE_INVOICES_TABLE, $data);

        if ($this->db->affected_rows() > 0) {
            log_beatroute_sync('invoice', $beatroute_id, 'update', 'success');
            return true;
        }

        return false;
    }

    /**
     * Add invoice item to local database
     *
     * @param array $data
     * @return int|bool
     */
    public function add_invoice_item($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert(BEATROUTE_INVOICE_ITEMS_TABLE, $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Add payment to local database
     *
     * @param array $data
     * @return int|bool
     */
    public function add_payment($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['last_sync'] = date('Y-m-d H:i:s');

        $this->db->insert(BEATROUTE_PAYMENTS_TABLE, $data);

        if ($this->db->affected_rows() > 0) {
            $insert_id = $this->db->insert_id();
            log_beatroute_sync('payment', $data['beatroute_id'], 'create', 'success');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update payment in local database
     *
     * @param string $beatroute_id
     * @param array $data
     * @return bool
     */
    public function update_payment($beatroute_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['last_sync'] = date('Y-m-d H:i:s');

        $this->db->where('beatroute_id', $beatroute_id);
        $this->db->update(BEATROUTE_PAYMENTS_TABLE, $data);

        if ($this->db->affected_rows() > 0) {
            log_beatroute_sync('payment', $beatroute_id, 'update', 'success');
            return true;
        }

        return false;
    }

    /**
     * Get SKU by Beatroute ID
     *
     * @param string $beatroute_id
     * @return object
     */
    public function get_sku_by_beatroute_id($beatroute_id)
    {
        $this->db->where('sku_external_id', $beatroute_id);
        return $this->db->get(BEATROUTE_SKUS_TABLE)->row();
    }

    /**
     * Get SKU by ID
     *
     * @param int $id
     * @return object
     */
    public function get_sku_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(BEATROUTE_SKUS_TABLE)->row();
    }

    /**
     * Get customer by Beatroute ID
     *
     * @param string $beatroute_id
     * @return object
     */
    public function get_customer_by_beatroute_id($beatroute_id)
    {
        $this->db->where('company', 'Beatroute_' . $beatroute_id);
        $client = $this->db->get(BEATROUTE_CUSTOMERS_TABLE)->row();

        if ($client) {
            // Add beatroute_id property for backward compatibility
            $client->beatroute_id = str_replace('Beatroute_', '', $client->company);

            // Get primary contact for email
            $this->db->where('userid', $client->userid);
            $this->db->where('is_primary', 1);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            if ($contact) {
                $client->email = $contact->email;
                $client->first_name = $contact->firstname;
                $client->last_name = $contact->lastname;
                $client->phone = $contact->phonenumber;
            }
        }

        return $client;
    }

    /**
     * Get customer by ID
     *
     * @param int $id
     * @return object
     */
    public function get_customer_by_id($id)
    {
        $this->db->where('userid', $id);
        $client = $this->db->get(BEATROUTE_CUSTOMERS_TABLE)->row();

        if ($client) {
            // Add beatroute_id property for backward compatibility
            $client->beatroute_id = str_replace('Beatroute_', '', $client->company);

            // Get primary contact for email
            $this->db->where('userid', $client->userid);
            $this->db->where('is_primary', 1);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            if ($contact) {
                $client->email = $contact->email;
                $client->first_name = $contact->firstname;
                $client->last_name = $contact->lastname;
                $client->phone = $contact->phonenumber;
            }
        }

        return $client;
    }

    /**
     * Get invoice by Beatroute ID
     *
     * @param string $beatroute_id
     * @return object
     */
    public function get_invoice_by_beatroute_id($beatroute_id)
    {
        $this->db->where('beatroute_id', $beatroute_id);
        return $this->db->get(BEATROUTE_INVOICES_TABLE)->row();
    }

    /**
     * Get invoice by ID
     *
     * @param int $id
     * @return object
     */
    public function get_invoice_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(BEATROUTE_INVOICES_TABLE)->row();
    }

    /**
     * Get payment by Beatroute ID
     *
     * @param string $beatroute_id
     * @return object
     */
    public function get_payment_by_beatroute_id($beatroute_id)
    {
        $this->db->where('beatroute_id', $beatroute_id);
        return $this->db->get(BEATROUTE_PAYMENTS_TABLE)->row();
    }

    /**
     * Get payment by ID
     *
     * @param int $id
     * @return object
     */
    public function get_payment_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(BEATROUTE_PAYMENTS_TABLE)->row();
    }

    /**
     * Get all SKUs from local database
     *
     * @return array
     */
    public function get_skus()
    {
        $this->db->select('id, sku_code, description as name, rate as price, isactive, updated_at as last_sync');
        $skus = $this->db->get(BEATROUTE_SKUS_TABLE)->result_array();

        // Add missing fields needed for the view
        foreach ($skus as &$sku) {
            // Map isactive to status
            $sku['status'] = $sku['isactive'] ? 'active' : 'inactive';

            // Add sync_status field (default to 'synced' if it exists)
            $sku['sync_status'] = 'synced';

            // Format the price field if needed
            if (isset($sku['price'])) {
                $sku['price'] = floatval($sku['price']);
            }
        }

        return $skus;
    }

    /**
     * Get all customers from local database
     *
     * @return array
     */
    public function get_customers()
    {
        // Select clients that are from Beatroute (company starts with 'Beatroute_')
        $this->db->select('clients.userid as id, clients.company, clients.address, clients.city, clients.state, clients.zip, clients.country, clients.phonenumber, clients.active, clients.datecreated');
        $this->db->from(BEATROUTE_CUSTOMERS_TABLE . ' as clients');
        $this->db->like('clients.company', 'Beatroute_', 'after');

        // Join with contacts table to get email and name
        $this->db->join(db_prefix() . 'contacts as contacts', 'contacts.userid = clients.userid AND contacts.is_primary = 1', 'left');
        $this->db->select('contacts.firstname as first_name, contacts.lastname as last_name, contacts.email, contacts.phonenumber as phone');

        $clients = $this->db->get()->result_array();

        $customers = [];
        foreach ($clients as $client) {
            // Extract beatroute_id from company name
            $beatroute_id = str_replace('Beatroute_', '', $client['company']);

            $customer = [
                'id' => $client['id'],
                'beatroute_id' => $beatroute_id,
                'first_name' => $client['first_name'] ?? '',
                'last_name' => $client['last_name'] ?? '',
                'email' => $client['email'] ?? '',
                'phone' => $client['phone'] ?? $client['phonenumber'] ?? '',
                'address' => $client['address'] ?? '',
                'city' => $client['city'] ?? '',
                'state' => $client['state'] ?? '',
                'zip' => $client['zip'] ?? '',
                'country' => $client['country'] ?? '',
                'status' => $client['active'] ? 'active' : 'inactive',
                'last_sync' => $client['datecreated'] ?? date('Y-m-d H:i:s'),
                'sync_status' => 'synced'
            ];

            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * Get all invoices from local database
     *
     * @return array
     */
    public function get_invoices()
    {
        return $this->db->get(db_prefix() . 'beatroute_invoices')->result_array();
    }

    /**
     * Get all payments from local database
     *
     * @return array
     */
    public function get_payments()
    {
        return $this->db->get(db_prefix() . 'beatroute_payments')->result_array();
    }

    /**
     * Sync SKUs with Beatroute
     *
     * @return bool
     */
    public function sync_skus()
    {
        log_activity('Starting SKUs sync');

        // Use get_beatroute_skus_v2 with empty page parameter to fetch all pages
        $result = $this->get_beatroute_skus_v2();
        $beatroute_skus = $result['data'];

        if (empty($beatroute_skus)) {
            log_activity('Beatroute SKU sync failed: No SKUs found in Beatroute');
            return false;
        }

        // Only log the total count, not each individual SKU
        log_activity('Found ' . count($beatroute_skus) . ' SKUs to sync');

        $success_count = 0;
        $failure_count = 0;

        foreach ($beatroute_skus as $sku) {
            $sku['id']=$sku['sku_external_id'];
            // Validate that required fields are present
            if (!isset($sku['sku_external_id']) || empty($sku['sku_external_id'])) {
                log_activity('Beatroute SKU sync failed: Missing or empty ID in SKU data');
                $failure_count++;
                continue; // Skip this SKU and continue with the next one
            }

            $sku_data = [
                'id'                  => $sku['sku_external_id'], // Custom field to store BeatRoute SKU ID
                'description'         => $sku['description'] ?? '',
                'long_description'    => $sku['detail_description'] ?? '',
                'rate'                => isset($sku['price']) ? $sku['price'] : 0, // if price is available in another API call
                'group_id'            => $sku['group_id'] ?? 0,
                'commodity_code'      => $sku['sku_external_id'] ?? '',
                'commodity_barcode'   => '', // Not provided in API
                'unit'                => $sku['uom'] ?? '',
                'sku_code'            => $sku['sku_external_id'] ?? '', // Or another unique code if available
                'sku_name'            => $sku['description'] ?? '',
                'purchase_price'      => 0, // not in API
                'commodity_type'      => 0, // not in API
                'warehouse_id'        => 0, // not in API
                'origin'              => '', // not in API
                'color_id'            => 0,
                'style_id'            => 0,
                'model_id'            => 0,
                'size_id'             => 0,
                'sub_group'           => '', // not in API
                'commodity_name'      => $sku['description'] ?? '',
                'color'               => '', // not in API
                'rate_currency_1'     => 0,  // not in API
                'guarantee'           => '', // not in API
                'profif_ratio'        => '', // not in API
                'active'              => 1,
                'long_descriptions'   => $sku['detail_description'] ?? '',
                'without_checking_warehouse' => 0,
                'series_id'           => '', // not in API
                'parent_id'           => 0,
                'attributes'          => json_encode($sku['customFields'] ?? []),
                'parent_attributes'   => '',
                'isactive'            => intval($sku['is_available'] ?? 1),
                'sku_erp_id'          => $sku['sku_external_id'] ?? '',
                'sku_external_id'     => $sku['sku_external_id'] ?? '',
                'brand'               => $sku['brand_name'] ?? '',
                'subbrand'            => '', // not in API
                'updated_at'          => $sku['updated_date'] ?? date('Y-m-d H:i:s'),
            ];

            $operation_success = false;
            if ($this->is_sku_exists($sku['sku_external_id'])) {
                $operation_success = $this->update_sku($sku['sku_external_id'], $sku_data);
            } else {
                $operation_success = $this->add_sku($sku_data);
            }

            if ($operation_success) {
                $success_count++;
            } else {
                $failure_count++;
                // Only log failures, not successes
                log_activity('SKU sync operation failed for: ' . $sku['id']);
            }
        }

        // Log a summary at the end
        log_activity('SKU sync completed. Success: ' . $success_count . ', Failures: ' . $failure_count);

        // Return true only if at least one SKU was successfully synced
        return $success_count > 0;
    }

    /**
     * Sync individual SKU with Beatroute
     *
     * @param string $sku_id The Beatroute SKU ID
     * @return bool
     */
    public function sync_individual_sku($sku_id)
    {
        log_activity('Starting individual SKU sync for ID: ' . $sku_id);

        // Get the SKU data from Beatroute API
        $config = get_beatroute_api_config();
        $original_url = $config->api_url;

        // Temporarily set the API URL to int/v2 endpoint
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => 'https://api.beatroute.io/int/v2']);

        // Make the API request to get the specific SKU
        $result = beatroute_api_request('sku/' . $sku_id, 'POST');

        // Restore the original API URL
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => $original_url]);

        // Validate the API response
        if (!$result || !isset($result['data'])) {
            log_activity('Beatroute individual SKU sync failed: No SKU found with ID ' . $sku_id);
            return false;
        }

        $sku = $result['data'];

        // Validate that required fields are present
        if (!isset($sku['id']) || empty($sku['id'])) {
            log_activity('Beatroute individual SKU sync failed: Missing or empty ID in SKU data');
            return false;
        }

        // Prepare the SKU data for local database
        $sku_data = [
            'beatroute_id'        => $sku['sku_external_id'], // Custom field to store BeatRoute SKU ID
            'description'         => $sku['description'] ?? '',
            'long_description'    => $sku['detail_description'] ?? '',
            'rate'                => isset($sku['price']) ? $sku['price'] : 0, // if price is available in another API call
            'group_id'            => $sku['group_id'] ?? 0,
            'commodity_code'      => $sku['sku_external_id'] ?? '',
            'commodity_barcode'   => '', // Not provided in API
            'unit'                => $sku['uom'] ?? '',
            'sku_code'            => $sku['sku_external_id'] ?? '', // Or another unique code if available
            'sku_name'            => $sku['description'] ?? '',
            'purchase_price'      => 0, // not in API
            'commodity_type'      => 0, // not in API
            'warehouse_id'        => 0, // not in API
            'origin'              => '', // not in API
            'color_id'            => 0,
            'style_id'            => 0,
            'model_id'            => 0,
            'size_id'             => 0,
            'sub_group'           => '', // not in API
            'commodity_name'      => $sku['description'] ?? '',
            'color'               => '', // not in API
            'rate_currency_1'     => 0,  // not in API
            'guarantee'           => '', // not in API
            'profif_ratio'        => '', // not in API
            'active'              => 1,
            'long_descriptions'   => $sku['detail_description'] ?? '',
            'without_checking_warehouse' => 0,
            'series_id'           => '', // not in API
            'parent_id'           => 0,
            'attributes'          => json_encode($sku['customFields'] ?? []),
            'parent_attributes'   => '',
            'isactive'            => intval($sku['is_available'] ?? 1),
            'sku_erp_id'          => $sku['sku_external_id'] ?? '',
            'sku_external_id'     => $sku['sku_external_id'] ?? '',
            'brand'               => $sku['brand_name'] ?? '',
            'subbrand'            => '', // not in API
            'updated_at'          => $sku['updated_date'] ?? date('Y-m-d H:i:s'),
        ];

        // Update or add the SKU to the local database
        if ($this->is_sku_exists($sku['sku_external_id'])) {
            $success = $this->update_sku($sku['sku_external_id'], $sku_data);
        } else {
            $success = $this->add_sku($sku_data);
        }

        if (!$success) {
            log_activity('SKU sync operation failed for: ' . $sku_id);
        }

        return $success;
    }

    /**
     * Sync customers with Beatroute
     *
     * @return bool
     */
    public function sync_customers()
    {
        log_activity('Starting customers sync');

        // Use get_beatroute_customers_v2 with empty page parameter to fetch all pages
        $result = $this->get_beatroute_customers_v2();
        $beatroute_customers = $result['data'];

        if (empty($beatroute_customers)) {
            log_activity('Beatroute customer sync failed: No customers found in Beatroute');
            return false;
        }

        // Only log the total count, not each individual customer
        log_activity('Found ' . count($beatroute_customers) . ' customers to sync');

        $success_count = 0;
        $failure_count = 0;

        foreach ($beatroute_customers as $customer) {
            // Validate that required fields are present
            if (!isset($customer['external_id']) || empty($customer['external_id'])) {
//                var_dump($customer);die;
                log_activity('Beatroute customer sync failed: Missing or empty external_id in customer data'.$customer);
                $failure_count++;
                continue; // Skip this customer and continue with the next one
            }

            // Combine address fields
            $address_parts = [];
            if (isset($customer['locality']) && !empty($customer['locality'])) $address_parts[] = $customer['locality'];
            if (isset($customer['street']) && !empty($customer['street'])) $address_parts[] = $customer['street'];
            if (isset($customer['district']) && !empty($customer['district'])) $address_parts[] = $customer['district'];

            $combined_address = implode(', ', $address_parts);

            $customer_data = [
                'beatroute_id' => $customer['external_id'],
                'external_id'=>$customer['external_id'],
                'company' => $customer['name'],
                'email' => isset($customer['email']) ? $customer['email'] : '',
                'phone' => isset($customer['mobile']) ? $customer['mobile'] : '',
                'address' => $combined_address,
                'city' => isset($customer['city']) ? $customer['city'] : '',
                'state' => isset($customer['state']) ? $customer['state'] : '',
                'zip' => isset($customer['pincode']) ? $customer['pincode'] : '',
                'country' => isset($customer['country']) ? $customer['country'] : '',
                'status' => isset($customer['status']) ? $customer['status'] : 'active',
                'sync_status' => 'synced',
                'last_sync' => date('Y-m-d H:i:s')
            ];

            // Split name into first_name and last_name if available
            if (isset($customer['name']) && !empty($customer['name'])) {
                $name_parts = explode(' ', $customer['name'], 2);
                $customer_data['first_name'] = $name_parts[0];
                $customer_data['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
            }

            $operation_success = false;
            if ($this->is_customer_exists($customer['external_id'])) {
                $operation_success = $this->update_customer($customer['external_id'], $customer_data);
            } else {
//                var_dump($this->db->last_query());
                $operation_success = $this->add_customer($customer_data);
            }

            if ($operation_success) {
                $success_count++;
            } else {
//                var_dump($this->db->last_query()); die;
                $failure_count++;
                // Only log failures, not successes
                log_activity('Customer sync operation failed for: ' . $customer['external_id']);
            }
        }

        // Log a summary at the end
        log_activity('Customer sync completed. Success: ' . $success_count . ', Failures: ' . $failure_count);

        // Return true only if at least one customer was successfully synced
        return $success_count > 0;
    }

    /**
     * Sync individual customer with Beatroute
     *
     * @param string $customer_id The Beatroute Customer ID
     * @return bool
     */
    public function sync_individual_customer($customer_id)
    {
        log_activity('Starting individual customer sync for ID: ' . $customer_id);

        // Get the customer data from Beatroute API
        $config = get_beatroute_api_config();
        $original_url = $config->api_url;

        // Temporarily set the API URL to int/v2 endpoint
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => 'https://api.beatroute.io/int/v2']);

        // Make the API request to get the specific customer
        $result = beatroute_api_request('customer/' . $customer_id, 'POST');

        // Restore the original API URL
        $this->db->where('id', $config->id);
        $this->db->update(BEATROUTE_API_CONFIG_TABLE, ['api_url' => $original_url]);

        // Validate the API response
        if (!$result || !isset($result['data'])) {
            log_activity('Beatroute individual customer sync failed: No customer found with ID ' . $customer_id);
            return false;
        }

        $customer = $result['data'];

        // Validate that required fields are present
        if (!isset($customer['external_id']) || empty($customer['external_id'])) {
            log_activity('Beatroute individual customer sync failed: Missing or empty external_id in customer data');
            return false;
        }

        // Combine address fields
        $address_parts = [];
        if (isset($customer['locality']) && !empty($customer['locality'])) $address_parts[] = $customer['locality'];
        if (isset($customer['street']) && !empty($customer['street'])) $address_parts[] = $customer['street'];
        if (isset($customer['district']) && !empty($customer['district'])) $address_parts[] = $customer['district'];

        $combined_address = implode(', ', $address_parts);

        // Prepare the customer data for local database
        $customer_data = [
            'beatroute_id' => $customer['external_id'],
            'first_name' => '', // Will be populated from name
            'last_name' => '', // Will be populated from name
            'email' => isset($customer['email']) ? $customer['email'] : '',
            'phone' => isset($customer['mobile']) ? $customer['mobile'] : '',
            'address' => $combined_address,
            'city' => isset($customer['city']) ? $customer['city'] : '',
            'state' => isset($customer['state']) ? $customer['state'] : '',
            'zip' => isset($customer['pincode']) ? $customer['pincode'] : '',
            'country' => isset($customer['country']) ? $customer['country'] : '',
            'status' => isset($customer['status']) ? $customer['status'] : 'active',
            'sync_status' => 'synced',
            'last_sync' => date('Y-m-d H:i:s')
        ];

        // Split name into first_name and last_name if available
        if (isset($customer['name']) && !empty($customer['name'])) {
            $name_parts = explode(' ', $customer['name'], 2);
            $customer_data['first_name'] = $name_parts[0];
            $customer_data['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
        }

        // Update or add the customer to the local database
        if ($this->is_customer_exists($customer['external_id'])) {
            $success = $this->update_customer($customer['external_id'], $customer_data);
        } else {
            $success = $this->add_customer($customer_data);
        }

        if (!$success) {
            log_activity('Customer sync operation failed for: ' . $customer_id);
        }

        return $success;
    }

    /**
     * Sync invoices with Beatroute
     *
     * @return bool
     */
    public function sync_invoices()
    {
        $beatroute_invoices = $this->get_beatroute_invoices();

        if (empty($beatroute_invoices)) {
            log_activity('Beatroute invoice sync failed: No invoices found in Beatroute');
            return false;
        }

        foreach ($beatroute_invoices as $invoice) {
            // Validate that required fields are present
            if (!isset($invoice['id']) || empty($invoice['id'])) {
                log_activity('Beatroute invoice sync failed: Missing or empty ID in invoice data');
                continue; // Skip this invoice and continue with the next one
            }

            if (!isset($invoice['customer_id']) || empty($invoice['customer_id'])) {
                log_activity('Beatroute invoice sync failed: Missing or empty customer_id in invoice data');
                continue; // Skip this invoice and continue with the next one
            }

            $invoice_data = [
                'beatroute_id' => $invoice['id'],
                'beatroute_customer_id' => $invoice['customer_id'],
                'invoice_number' => isset($invoice['invoice_number']) ? $invoice['invoice_number'] : '',
                'date' => isset($invoice['date']) ? $invoice['date'] : date('Y-m-d'),
                'due_date' => isset($invoice['due_date']) ? $invoice['due_date'] : date('Y-m-d'),
                'currency' => isset($invoice['currency']) ? $invoice['currency'] : '',
                'subtotal' => isset($invoice['subtotal']) ? $invoice['subtotal'] : 0,
                'total' => isset($invoice['total']) ? $invoice['total'] : 0,
                'status' => isset($invoice['status']) ? $invoice['status'] : 'draft',
                'sync_status' => 'synced'
            ];

            if ($this->is_invoice_exists($invoice['id'])) {
                $this->update_invoice($invoice['id'], $invoice_data);
            } else {
                $invoice_id = $this->add_invoice($invoice_data);

                // Add invoice items
                if ($invoice_id && isset($invoice['items']) && is_array($invoice['items'])) {
                    foreach ($invoice['items'] as $item) {
                        // Validate item data
                        if (!isset($item['sku_id']) || empty($item['sku_id'])) {
                            log_activity('Beatroute invoice item sync failed: Missing or empty sku_id in item data');
                            continue; // Skip this item and continue with the next one
                        }

                        $item_data = [
                            'beatroute_invoice_id' => $invoice['id'],
                            'beatroute_sku_id' => $item['sku_id'],
                            'description' => isset($item['description']) ? $item['description'] : '',
                            'qty' => isset($item['quantity']) ? $item['quantity'] : 1,
                            'rate' => isset($item['rate']) ? $item['rate'] : 0
                        ];

                        $this->add_invoice_item($item_data);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sync payments with Beatroute
     *
     * @return bool
     */
    public function sync_payments()
    {
        $beatroute_payments = $this->get_beatroute_payments();

        if (empty($beatroute_payments)) {
            log_activity('Beatroute payment sync failed: No payments found in Beatroute');
            return false;
        }

        foreach ($beatroute_payments as $payment) {
            // Validate that required fields are present
            if (!isset($payment['id']) || empty($payment['id'])) {
                log_activity('Beatroute payment sync failed: Missing or empty ID in payment data');
                continue; // Skip this payment and continue with the next one
            }

            if (!isset($payment['invoice_id']) || empty($payment['invoice_id'])) {
                log_activity('Beatroute payment sync failed: Missing or empty invoice_id in payment data');
                continue; // Skip this payment and continue with the next one
            }

            $payment_data = [
                'beatroute_id' => $payment['id'],
                'beatroute_invoice_id' => $payment['invoice_id'],
                'amount' => isset($payment['amount']) ? $payment['amount'] : 0,
                'payment_mode' => isset($payment['payment_mode']) ? $payment['payment_mode'] : '',
                'payment_date' => isset($payment['payment_date']) ? $payment['payment_date'] : date('Y-m-d'),
                'transaction_id' => isset($payment['transaction_id']) ? $payment['transaction_id'] : '',
                'status' => isset($payment['status']) ? $payment['status'] : 'pending',
                'sync_status' => 'synced'
            ];

            if ($this->is_payment_exists($payment['id'])) {
                $this->update_payment($payment['id'], $payment_data);
            } else {
                $this->add_payment($payment_data);
            }
        }

        return true;
    }

    /**
     * Push SKU to Beatroute
     *
     * @param array $data
     * @return array|bool
     */
    public function push_sku_to_beatroute($data)
    {
        $result = beatroute_api_request('skus', 'POST', $data);

        if ($result) {
            log_beatroute_sync('sku', $result['id'], 'push', 'success');
            return $result;
        }

        log_beatroute_sync('sku', isset($data['id']) ? $data['id'] : 'unknown', 'push', 'error', 'Failed to push SKU to Beatroute');
        return false;
    }

    /**
     * Push customer to Beatroute
     *
     * @param array $data
     * @return array|bool
     */
    public function push_customer_to_beatroute($data)
    {
        $result = beatroute_api_request('customers', 'POST', $data);

        if ($result) {
            log_beatroute_sync('customer', $result['id'], 'push', 'success');
            return $result;
        }

        log_beatroute_sync('customer', isset($data['id']) ? $data['id'] : 'unknown', 'push', 'error', 'Failed to push customer to Beatroute');
        return false;
    }

    /**
     * Push invoice to Beatroute
     *
     * @param array $data
     * @return array|bool
     */
    public function push_invoice_to_beatroute($data)
    {
        $result = beatroute_api_request('invoices', 'POST', $data);

        if ($result) {
            log_beatroute_sync('invoice', $result['id'], 'push', 'success');
            return $result;
        }

        log_beatroute_sync('invoice', isset($data['id']) ? $data['id'] : 'unknown', 'push', 'error', 'Failed to push invoice to Beatroute');
        return false;
    }

    /**
     * Push payment to Beatroute
     *
     * @param array $data
     * @return array|bool
     */
    public function push_payment_to_beatroute($data)
    {
        $result = beatroute_api_request('payments', 'POST', $data);

        if ($result) {
            log_beatroute_sync('payment', $result['id'], 'push', 'success');
            return $result;
        }

        log_beatroute_sync('payment', isset($data['id']) ? $data['id'] : 'unknown', 'push', 'error', 'Failed to push payment to Beatroute');
        return false;
    }
}
