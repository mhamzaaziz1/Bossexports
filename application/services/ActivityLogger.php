<?php

namespace app\services;

defined('BASEPATH') or exit('No direct script access allowed');

class ActivityLogger
{
    public static function log($description, $staffid = null, $type = 'general', $module = null, $method = null)
    {
        $CI  = & get_instance();
        
        // Get metadata
        $ip = self::get_client_ip();
        $device = self::get_device_info();
        $location = self::get_location_info($ip);

        $log = [
            'description' => $description,
            'date'        => date('Y-m-d H:i:s'),
            'ip'          => $ip,
            'device'      => $device,
            'location'    => $location,
            'type'        => $type,
            'module'      => $module,
            'method'      => $method,
        ];

        if (!DEFINED('CRON')) {
             // Check if crucial libraries are loaded before attempting to access staff/client info
             if (isset($CI->app_object_cache) && isset($CI->db)) {
                if ($staffid != null && is_numeric($staffid)) {
                    $log['staffid'] = get_staff_full_name($staffid);
                } else {
                    if (!is_client_logged_in()) {
                        if (is_staff_logged_in()) {
                            $log['staffid'] = get_staff_full_name(get_staff_user_id());
                        } else {
                            $log['staffid'] = null;
                        }
                    } else {
                        $log['staffid'] = get_contact_full_name(get_contact_user_id());
                    }
                }
             }
        } else {
            // manually invoked cron
            if (function_exists('is_staff_logged_in') && is_staff_logged_in()) {
                $log['staffid'] = get_staff_full_name(get_staff_user_id());
            } else {
                $log['staffid'] = '[CRON]';
            }
        }

        $CI->db->insert(db_prefix() . 'activity_log', $log);
    }

    public static function getLast()
    {
        $CI = &get_instance();
        $CI->db->select('id');
        $CI->db->order_by('id', 'desc');
        $CI->db->limit(1);

        return $CI->db->get(db_prefix() . 'activity_log')->row();
    }

    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if(isset($_SERVER['REMOTE_ADDR'])) {
                return $_SERVER['REMOTE_ADDR'];
            }
            return '';
        }
    }

    private static function get_device_info() {
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return 'Unknown';
    }

    private static function get_location_info($ip) {
        if(empty($ip) || $ip == '127.0.0.1' || $ip == '::1') {
            return 'Localhost';
        }
        // Minimal timeout to avoid performance impact
        $ch = curl_init("http://ip-api.com/json/" . $ip);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // 1 second timeout
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if(isset($data['status']) && $data['status'] == 'success') {
                return $data['city'] . ', ' . $data['country'];
            }
        }
        return 'Unknown';
    }
}
