<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Webhook Signature Generator
 * Generates HMAC-SHA256 signatures for webhook security
 */
class N8n_signature_generator
{
    /**
     * Generate HMAC-SHA256 signature for payload
     */
    public static function generate($payload, $secret_key)
    {
        if (empty($secret_key)) {
            return null;
        }

        $payload_json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $payload_json, $secret_key);

        return 'sha256=' . $signature;
    }

    /**
     * Add signature to headers
     */
    public static function add_to_headers($headers, $signature, $timestamp)
    {
        if ($signature) {
            $headers['X-Webhook-Signature'] = $signature;
            $headers['X-Webhook-Signature-256'] = $signature;
            $headers['X-Webhook-Timestamp'] = $timestamp;
        }

        return $headers;
    }

    /**
     * Verify signature (for incoming webhooks if needed)
     */
    public static function verify($payload, $signature, $secret_key)
    {
        $expected = self::generate($payload, $secret_key);
        
        $signature = str_replace('sha256=', '', $signature);
        $expected = str_replace('sha256=', '', $expected);

        return hash_equals($expected, $signature);
    }

    /**
     * Generate random secret key
     */
    public static function generate_secret_key($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}
