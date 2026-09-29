<?php
defined('BASEPATH') or exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Carbon\Carbon;

/**
 * n8n HTTP Client Library
 *
 * Handles HTTP requests to n8n webhooks with:
 * - Guzzle HTTP client (modern, PSR-7 compliant)
 * - Retry logic
 * - Timeout handling
 * - Signature generation
 * - Error logging
 */
class N8n_http_client
{
    private $CI;
    private $client;
    private $timeout = 300; // Default timeout in seconds (5 minutes converted)
    private $max_retries = 3;
    private $retry_delay = 2; // Seconds between retries

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model(N8N_CONNECTOR_MODULE . '/N8n_logs_model', 'n8n_logs_model');
        $this->CI->load->library(N8N_CONNECTOR_MODULE . '/N8n_signature_generator');

        // Initialize Guzzle HTTP client
        $this->client = new Client([
            'timeout' => $this->timeout,
            'connect_timeout' => 10,
            'http_errors' => false,
            'verify' => true,
            'allow_redirects' => [
                'max' => 3,
                'strict' => true,
            ],
        ]);
    }

    /**
     * Send webhook to n8n
     *
     * @param string $url Webhook URL
     * @param array $payload Data payload
     * @param array $options Additional options (headers, secret, etc.)
     * @return array Response with success status and details
     */
    public function send($url, $payload, $options = [])
    {
        get_instance()->load->model('N8n_webhooks_model');
        $start_time = microtime(true);
        $webhook_id = isset($options['webhook_id']) ? $options['webhook_id'] : null;
        $secret = isset($options['secret']) ? $options['secret'] : null;
        $webhook = get_instance()->N8n_webhooks_model->get($webhook_id);

        // Validate URL
        if (!$this->validate_url($url)) {
            return $this->error_response('Invalid webhook URL', $webhook_id);
        }

        // Prepare headers
        $headers = $this->prepare_headers($payload, $secret, $options);

        // Convert payload to JSON
        $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json_payload === false) {
            return $this->error_response('Failed to encode payload to JSON', $webhook_id);
        }

        // Attempt to send with retries
        $attempt = 0;
        $last_error = null;

        while ($attempt < $this->max_retries) {
            $attempt++;

            try {
                $response = $this->make_request($url, $json_payload, $headers);

                if ($response['success']) {
                    $duration = round((microtime(true) - $start_time) * 1000, 2); // milliseconds

                    // Log success
                    if (isset($webhook->run_in_background) && $webhook->run_in_background == 0) {
                        $this->log_request($webhook_id, $url, $payload, $response, $duration, 'success');
                    }

                    return [
                        'success' => true,
                        'status_code' => $response['status_code'],
                        'response_body' => $response['body'],
                        'duration_ms' => $duration,
                        'attempts' => $attempt,
                    ];
                }

                $last_error = $response['error'];

                // Don't retry on client errors (4xx)
                if ($response['status_code'] >= 400 && $response['status_code'] < 500) {
                    break;
                }
            } catch (Exception $e) {
                $last_error = $e->getMessage();
            }

            // Wait before retry (except on last attempt)
            if ($attempt < $this->max_retries) {
                sleep($this->retry_delay);
            }
        }

        // All attempts failed
        $duration = round((microtime(true) - $start_time) * 1000, 2);

        $error_response = [
            'success' => false,
            'error' => $last_error,
            'attempts' => $attempt,
            'duration_ms' => $duration,
        ];

        // Log failure
        if (isset($webhook->run_in_background) && $webhook->run_in_background == 0) {
            $this->log_request($webhook_id, $url, $payload, $error_response, $duration, 'failed');
        }

        return $error_response;
    }

    /**
     * Make HTTP request using Guzzle
     *
     * @param string $url URL
     * @param string $json_payload JSON payload
     * @param array $headers Headers
     * @return array Response
     */
    private function make_request($url, $json_payload, $headers)
    {
        try {
            // Convert header array to Guzzle format
            $guzzle_headers = [];
            foreach ($headers as $header) {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $guzzle_headers[trim($parts[0])] = trim($parts[1]);
                }
            }

            // Make POST request with Guzzle
            $response = $this->client->post($url, [
                'headers' => $guzzle_headers,
                'body' => $json_payload,
            ]);

            $status_code = $response->getStatusCode();
            $response_body = (string) $response->getBody();

            // Check HTTP status code
            if ($status_code >= 200 && $status_code < 300) {
                return [
                    'success' => true,
                    'status_code' => $status_code,
                    'body' => $response_body,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'status_code' => $status_code,
                'body' => $response_body,
                'error' => 'HTTP Error: ' . $status_code . ' - ' . $this->get_status_message($status_code),
            ];
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Connection Error: ' . $e->getMessage(),
                'status_code' => 0,
                'body' => null,
            ];
        } catch (RequestException $e) {
            $status_code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : null;

            return [
                'success' => false,
                'status_code' => $status_code,
                'body' => $body,
                'error' => 'Request Error: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'status_code' => 0,
                'body' => null,
            ];
        }
    }

    /**
     * Prepare HTTP headers
     *
     * @param array $payload Payload
     * @param string $secret Secret key
     * @param array $options Options
     * @return array Headers
     */
    private function prepare_headers($payload, $secret = null, $options = [])
    {
        $headers = [
            'Content-Type: application/json',
            'User-Agent: Perfex-CRM-n8n-Connector/1.0',
            'X-Perfex-CRM-Version: ' . get_app_version(),
            'X-n8n-Connector-Version: 1.0.0',
        ];

        // Add signature if secret is provided
        if (!empty($secret) && get_option('n8n_connector_enable_signatures') == 1) {
            $signature = $this->CI->n8n_signature_generator->generate(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $secret
            );
            $headers[] = 'X-n8n-Signature: ' . $signature;
        }

        // Add custom headers from options
        if (isset($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
        }

        return $headers;
    }

    /**
     * Validate webhook URL
     *
     * @param string $url URL to validate
     * @return bool
     */
    private function validate_url($url)
    {
        // Basic URL validation
        if (empty($url) || !is_string($url)) {
            return false;
        }

        // Check if valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check scheme (must be http or https)
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        // Check if host is set
        if (!isset($parsed['host']) || empty($parsed['host'])) {
            return false;
        }

        // Prevent localhost in production (security)
        if (ENVIRONMENT === 'production') {
            $localhost_patterns = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
            foreach ($localhost_patterns as $pattern) {
                if (stripos($parsed['host'], $pattern) !== false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Log webhook request
     *
     * @param int $webhook_id Webhook ID
     * @param string $url URL
     * @param array $payload Payload
     * @param array $response Response
     * @param float $duration Duration in ms
     * @param string $status Status (success/failed)
     */
    private function log_request($webhook_id, $url, $payload, $response, $duration, $status)
    {
        // Check if logging is enabled
        if (!get_option('n8n_connector_enable_logging')) {
            return;
        }

        $log_data = [
            'webhook_id' => $webhook_id,
            'event_id' => isset($payload['metadata']['event_id']) ? $payload['metadata']['event_id'] : n8n_generate_event_id(),
            'event_type' => isset($payload['event']) ? $payload['event'] : 'unknown',
            'resource_type' => isset($payload['metadata']['table']) ? $payload['metadata']['table'] : 'unknown',
            'resource_id' => isset($payload['data']['id']) ? $payload['data']['id'] : 0,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'request_headers' => json_encode([]),
            'response_code' => isset($response['status_code']) ? $response['status_code'] : 0,
            'response_body' => isset($response['response_body']) ? $response['response_body'] : (isset($response['body']) ? $response['body'] : null),
            'response_time_ms' => $duration,
            'status' => $status,
            'attempt_number' => isset($response['attempts']) ? $response['attempts'] : 1,
            'error_message' => isset($response['error']) ? $response['error'] : null,
            'triggered_at' => Carbon::now()->toDateTimeString(),
            'completed_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            $this->CI->n8n_logs_model->add($log_data);
        } catch (Exception $e) {
            log_activity('n8n Connector: Failed to log webhook request - ' . $e->getMessage());
        }
    }

    /**
     * Create error response
     *
     * @param string $error Error message
     * @param int $webhook_id Webhook ID
     * @return array Error response
     */
    private function error_response($error, $webhook_id = null)
    {
        return [
            'success' => false,
            'error' => $error,
            'webhook_id' => $webhook_id,
            'attempts' => 0,
            'duration_ms' => 0,
        ];
    }

    /**
     * Get HTTP status message
     *
     * @param int $code Status code
     * @return string Status message
     */
    private function get_status_message($code)
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return isset($messages[$code]) ? $messages[$code] : 'Unknown Status';
    }

    /**
     * Set request timeout
     *
     * @param int $minutes Timeout in minutes (will be converted to seconds internally)
     */
    public function set_timeout($minutes)
    {
        // Convert minutes to seconds for Guzzle
        $this->timeout = (int) $minutes * 60;
    }

    /**
     * Set max retries
     *
     * @param int $retries Max retry attempts
     */
    public function set_max_retries($retries)
    {
        $this->max_retries = (int) $retries;
    }

    /**
     * Set retry delay
     *
     * @param int $seconds Delay in seconds
     */
    public function set_retry_delay($seconds)
    {
        $this->retry_delay = (int) $seconds;
    }

    /**
     * Test webhook URL (sends a test payload)
     *
     * @param string $url Webhook URL
     * @param string $secret Optional secret
     * @return array Response
     */
    public function test($url, $secret = null)
    {
        $test_payload = [
            'event' => 'test',
            'event_label' => 'Test Event',
            'event_description' => 'This is a test webhook from Perfex CRM n8n Connector',
            'triggered_at' => Carbon::now()->toDateTimeString(),
            'triggered_at_unix' => Carbon::now()->timestamp,
            'data' => [
                'test' => true,
                'message' => 'If you receive this, your webhook is configured correctly!',
            ],
            'metadata' => [
                'source' => 'perfex_crm',
                'source_url' => site_url(),
                'source_version' => get_app_version(),
                'module' => N8N_CONNECTOR_MODULE,
                'module_version' => '1.0.0',
                'event_id' => n8n_generate_event_id(),
                'is_test' => true,
            ],
        ];

        return $this->send($url, $test_payload, [
            'secret' => $secret,
            'webhook_id' => null,
        ]);
    }

    /**
     * Send webhook (wrapper for send method with different parameter format)
     *
     * @param string $url Webhook URL
     * @param array $payload Data payload
     * @param array $headers Custom headers array
     * @param string $secret Secret key
     * @param int $webhook_id Webhook ID
     * @return array Response with success status and details
     */
    public function send_webhook($url, $payload, $headers = [], $secret = null, $webhook_id = null)
    {
        $options = [
            'secret' => $secret,
            'headers' => $headers,
            'webhook_id' => $webhook_id,
        ];

        return $this->send($url, $payload, $options);
    }
}
