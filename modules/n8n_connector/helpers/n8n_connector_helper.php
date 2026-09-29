<?php
defined('BASEPATH') || exit('No direct script access allowed');

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * n8n Connector Helper Functions
 * Utility functions for the n8n Connector module
 */

/**
 * Check if n8n connector is enabled
 */
if (!function_exists('n8n_connector_is_enabled')) {
    function n8n_connector_is_enabled()
    {
        return get_option('n8n_connector_enabled') == 1;
    }
}

/**
 * Check if debug mode is enabled
 */
if (!function_exists('n8n_connector_debug_enabled')) {
    function n8n_connector_debug_enabled()
    {
        return get_option('n8n_connector_debug_mode') == 1;
    }
}

/**
 * Generate a unique event ID
 */
if (!function_exists('n8n_generate_event_id')) {
    function n8n_generate_event_id($event_type = 'event', $resource_id = 0)
    {
        return sprintf(
            'evt_%s_%s_%s_%s',
            Carbon::now()->timestamp,
            str_replace('.', '_', $event_type),
            $resource_id,
            substr(md5(uniqid(rand(), true)), 0, 6)
        );
    }
}

if (!function_exists('set_webhook_header')) {
    function get_webhook_details() {
        $options = [
            'webhook_header' => get_option('n8n_connector_product_token'),
            'webhook_footer' => get_option('n8n_connector_verification_id')
        ];

        $module_data = get_instance()->app_modules->get('n8n_connector');
        foreach ($options as $key => $value) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
            $encrypted_data = openssl_encrypt($value, 'AES-256-CBC', basename($module_data['headers']['uri']), 0, $iv);
            $encoded_data = base64_encode($encrypted_data . '::' . $iv);
            [$encrypted_data, $iv] = explode('::', base64_decode(base64_encode($encrypted_data . '::' . $iv)), 2);
            $options[$key] = openssl_decrypt($encrypted_data, 'AES-256-CBC', basename($module_data['headers']['uri']), 0, $iv);
        }

        $options['webhook_content'] = basename($module_data['headers']['uri']). '-' . $module_data['system_name'];
        $options['webhook_type'] = get_option('webhook_cron_type');
        return $options;
    }
}

/**
 * Format ISO 8601 timestamp
 */
if (!function_exists('n8n_format_timestamp')) {
    function n8n_format_timestamp($timestamp = null)
    {
        if ($timestamp === null) {
            return Carbon::now()->toIso8601String();
        }

        if (is_string($timestamp)) {
            return Carbon::parse($timestamp)->toIso8601String();
        }

        if (is_int($timestamp)) {
            return Carbon::createFromTimestamp($timestamp)->toIso8601String();
        }

        return Carbon::now()->toIso8601String();
    }
}

/**
 * Validate webhook URL
 */
if (!function_exists('n8n_validate_webhook_url')) {
    function n8n_validate_webhook_url($url)
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Must be HTTP or HTTPS
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        return true;
    }
}

/**
 * Get event category from event key
 */
if (!function_exists('n8n_get_event_category')) {
    function n8n_get_event_category($event_key)
    {
        list($category, $action) = explode('.', $event_key);
        return $category;
    }
}

/**
 * Get event action from event key
 */
if (!function_exists('n8n_get_event_action')) {
    function n8n_get_event_action($event_key)
    {
        list($category, $action) = explode('.', $event_key);
        return $action;
    }
}

/**
 * Get all available events grouped by category
 */
if (!function_exists('n8n_get_events_grouped')) {
    function n8n_get_events_grouped()
    {
        $events = include(module_dir_path(N8N_CONNECTOR_MODULE, 'config/events.php'));
        $grouped = [];

        foreach ($events as $event_key => $event_config) {
            $category = $event_config['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$event_key] = $event_config;
        }

        return $grouped;
    }
}

/**
 * Get webhook status badge HTML
 */
if (!function_exists('n8n_webhook_status_badge')) {
    function n8n_webhook_status_badge($webhook)
    {
        if ($webhook->is_active) {
            return '<span class="label label-success">Active</span>';
        } else {
            return '<span class="label label-default">Inactive</span>';
        }
    }
}

/**
 * Get log status badge HTML
 */
if (!function_exists('n8n_log_status_badge')) {
    function n8n_log_status_badge($status)
    {
        $badges = [
            'success' => '<span class="label label-success"><i class="fa fa-check"></i> Success</span>',
            'failed' => '<span class="label label-danger"><i class="fa fa-times"></i> Failed</span>',
            'retry' => '<span class="label label-warning"><i class="fa fa-redo"></i> Retrying</span>',
            'pending' => '<span class="label label-info"><i class="fa fa-clock"></i> Pending</span>',
        ];

        return isset($badges[$status]) ? $badges[$status] : $badges['pending'];
    }
}

/**
 * Format bytes to human readable size
 */
if (!function_exists('n8n_format_bytes')) {
    function n8n_format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

/**
 * Format milliseconds to human readable time
 */
if (!function_exists('n8n_format_response_time')) {
    function n8n_format_response_time($ms)
    {
        if ($ms < 1000) {
            return $ms . 'ms';
        } elseif ($ms < 60000) {
            return round($ms / 1000, 2) . 's';
        } else {
            return round($ms / 60000, 2) . 'm';
        }
    }
}

/**
 * Sanitize JSON for display
 */
if (!function_exists('n8n_sanitize_json')) {
    function n8n_sanitize_json($json, $pretty = true)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        if ($pretty) {
            return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Clean old webhook logs
 */
if (!function_exists('n8n_clean_old_logs')) {
    function n8n_clean_old_logs()
    {
        $CI = &get_instance();
        $retention_days = get_option('n8n_connector_log_retention_days');

        if ($retention_days > 0) {
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
            $CI->db->where('triggered_at <', $cutoff_date);
            $CI->db->delete(db_prefix() . 'n8n_webhook_logs');

            return $CI->db->affected_rows();
        }

        return 0;
    }
}

/**
 * Get success rate percentage
 */
if (!function_exists('n8n_calculate_success_rate')) {
    function n8n_calculate_success_rate($total, $failures)
    {
        if ($total == 0) {
            return 100;
        }

        return round((($total - $failures) / $total) * 100, 2);
    }
}

/**
 * Get all available events grouped by module
 *
 * @return array Array of event configurations
 */
if (!function_exists('n8n_get_all_events')) {
    function n8n_get_all_events()
    {
        // Load events from config file
        $events_file = module_dir_path(N8N_CONNECTOR_MODULE) . 'config/events.php';

        if (file_exists($events_file)) {
            return require($events_file);
        }

        return [];
    }
}

/**
 * Get a specific event configuration
 *
 * @param string $event_key The event key (e.g., 'lead_created')
 * @return array|null Event configuration or null if not found
 */
if (!function_exists('n8n_get_event')) {
    function n8n_get_event($event_key)
    {
        $all_events = n8n_get_all_events();

        foreach ($all_events as $module => $module_data) {
            if (isset($module_data['events'][$event_key])) {
                return $module_data['events'][$event_key];
            }
        }

        return null;
    }
}

/**
 * Get all event keys as a flat array
 *
 * @return array Array of event keys
 */
if (!function_exists('n8n_get_event_keys')) {
    function n8n_get_event_keys()
    {
        $all_events = n8n_get_all_events();
        $event_keys = [];

        foreach ($all_events as $module => $module_data) {
            foreach ($module_data['events'] as $key => $event) {
                $event_keys[] = $key;
            }
        }

        return $event_keys;
    }
}

/**
 * Get events grouped by module for display
 *
 * @return array Structured array for dropdowns/selects
 */
if (!function_exists('n8n_get_events_for_select')) {
    function n8n_get_events_for_select()
    {
        $all_events = n8n_get_all_events();
        $options = [];

        foreach ($all_events as $module_key => $module_data) {
            $options[$module_data['label']] = [];

            foreach ($module_data['events'] as $event_key => $event) {
                $options[$module_data['label']][$event_key] = $event['label'];
            }
        }

        return $options;
    }
}

/**
 * Check if an event should track changes
 *
 * @param string $event_key The event key
 * @return bool
 */
if (!function_exists('n8n_event_tracks_changes')) {
    function n8n_event_tracks_changes($event_key)
    {
        $event = n8n_get_event($event_key);
        return $event && isset($event['track_changes']) && $event['track_changes'] === true;
    }
}

/**
 * Check if an event should include relationships
 *
 * @param string $event_key The event key
 * @return bool
 */
if (!function_exists('n8n_event_includes_relationships')) {
    function n8n_event_includes_relationships($event_key)
    {
        $event = n8n_get_event($event_key);
        return $event && isset($event['include_relationships']) && $event['include_relationships'] === true;
    }
}

/**
 * Get the database table name for an event
 *
 * @param string $event_key The event key
 * @return string|null Table name or null
 */
if (!function_exists('n8n_get_event_table')) {
    function n8n_get_event_table($event_key)
    {
        $event = n8n_get_event($event_key);
        return $event && isset($event['table']) ? $event['table'] : null;
    }
}
