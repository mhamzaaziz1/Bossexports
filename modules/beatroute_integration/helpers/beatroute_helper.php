<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Initialize footer if function doesn't exist
 * This is a fallback for the core Perfex CRM function
 */
if (!function_exists('init_footer')) {
    function init_footer() {
        // Get instance of CI
        $CI = &get_instance();

        // Load necessary views for footer
        $CI->load->view('admin/includes/scripts');

        // Check if there are any footer hooks
        hooks()->do_action('admin_footer');

        // Close body and html tags
        echo '</body></html>';
    }
}

/**
 * Get Beatroute API configuration
 *
 * @return object|null
 */
function get_beatroute_api_config()
{
    $CI = &get_instance();
    $CI->db->where('active', 1);
    return $CI->db->get(db_prefix() . 'beatroute_api_config')->row();
}

/**
 * Initialize Beatroute API client
 *
 * @return object|false
 */
function init_beatroute_api()
{
    $config = get_beatroute_api_config();

    if (!$config) {
        log_activity('Beatroute API initialization failed: No active configuration found');
        return false;
    }

    // Create a new instance of the Beatroute API client
    $client = new stdClass();
    $client->api_key = $config->api_key;
    $client->api_secret = $config->api_secret;
    $client->api_url = $config->api_url;

    return $client;
}

/**
 * Make a request to the Beatroute API
 *
 * @param string $endpoint API endpoint
 * @param string $method HTTP method (GET, POST, PUT, DELETE)
 * @param array $data Request data
 * @return array|false Response data or false on failure
 */
function beatroute_api_request($endpoint, $method = 'GET', $data = [])
{
    $client = init_beatroute_api();

    if (!$client) {
        return false;
    }

    $url = rtrim($client->api_url, '/') . '/' . ltrim($endpoint, '/');

    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $client->api_key,
        // Add cookie header if needed for authentication
        // 'Cookie: _csrf=SkqxB_PDKxOuVDsCQhHJqxBdQq5AhDxK'
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } elseif ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
    }

    // Log the request URL for debugging
    log_activity('Beatroute API request to: ' . $url . ' with method: ' . $method);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        log_activity('Beatroute API request failed: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    // Log the response for debugging
    log_activity('Beatroute API response code: ' . $http_code);

    $result = json_decode($response, true);

    if ($http_code >= 400) {
        $error_message = isset($result['message']) ? $result['message'] : 'Unknown error';
        log_activity('Beatroute API request failed with status ' . $http_code . ': ' . $error_message);
        // Log the raw response for debugging
        log_activity('Beatroute API raw response: ' . $response);
        return false;
    }

    // Log success for debugging
    log_activity('Beatroute API request successful');

    return $result;
}

/**
 * Log synchronization activity
 *
 * @param string $entity_type Type of entity (sku, customer, invoice, payment)
 * @param string $entity_id Entity ID
 * @param string $action Action performed (create, update, delete)
 * @param string $status Status of the action (success, error)
 * @param string $message Additional message
 * @return int|bool The insert ID or false on failure
 */
function log_beatroute_sync($entity_type, $entity_id, $action, $status, $message = '')
{
    $CI = &get_instance();

    $data = [
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'action' => $action,
        'status' => $status,
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $CI->db->insert(db_prefix() . 'beatroute_sync_logs', $data);

    if ($CI->db->affected_rows() > 0) {
        return $CI->db->insert_id();
    }

    return false;
}

/**
 * Check if Beatroute integration is enabled
 *
 * @return bool
 */
function is_beatroute_integration_enabled()
{
    return get_option('beatroute_integration_enabled') == 1;
}

/**
 * Check if Beatroute auto sync is enabled
 *
 * @return bool
 */
function is_beatroute_auto_sync_enabled()
{
    return get_option('beatroute_auto_sync_enabled') == 1;
}

/**
 * Check if Beatroute webhook is enabled
 *
 * @return bool
 */
function is_beatroute_webhook_enabled()
{
    return get_option('beatroute_webhook_enabled') == 1;
}

/**
 * Get Beatroute sync interval
 *
 * @return string
 */
function get_beatroute_sync_interval()
{
    return get_option('beatroute_sync_interval');
}

/**
 * Update last sync time
 *
 * @return bool
 */
function update_beatroute_last_sync_time()
{
    $CI = &get_instance();
    $CI->db->where('active', 1);
    $CI->db->update(db_prefix() . 'beatroute_api_config', ['last_sync_time' => date('Y-m-d H:i:s')]);

    return $CI->db->affected_rows() > 0;
}

/**
 * Cron job function for Beatroute synchronization
 *
 * @return void
 */
function beatroute_integration_cron()
{
    if (!is_beatroute_integration_enabled() || !is_beatroute_auto_sync_enabled()) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('beatroute_integration/beatroute_model');

    // Sync SKUs
    $CI->beatroute_model->sync_skus();

    // Sync customers
    $CI->beatroute_model->sync_customers();

    // Sync invoices
    $CI->beatroute_model->sync_invoices();

    // Sync payments
    $CI->beatroute_model->sync_payments();

    // Update last sync time
    update_beatroute_last_sync_time();

    log_activity('Beatroute integration cron job completed');
}

/**
 * Verify Beatroute webhook signature
 *
 * @param string $payload Request payload
 * @param string $signature Request signature
 * @return bool
 */
function verify_beatroute_webhook_signature($payload, $signature)
{
    $config = get_beatroute_api_config();

    if (!$config || empty($config->webhook_secret)) {
        return false;
    }

    $expected_signature = hash_hmac('sha256', $payload, $config->webhook_secret);

    return hash_equals($expected_signature, $signature);
}
