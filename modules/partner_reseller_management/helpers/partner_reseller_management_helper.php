<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Partner & Reseller Management — Helper functions
 *  - Settings access
 *  - Auth helpers (partner session)
 *  - Audit logger shorthand
 *  - Currency formatter w/ multi-currency support
 *  - Permission shorthand
 */

if (!function_exists('get_prm_option')) {
    function get_prm_option($key, $default = null)
    {
        $CI = &get_instance();
        $row = $CI->db->where('setting_key', $key)->get(db_prefix() . 'prm_settings')->row();
        return $row ? $row->setting_value : $default;
    }
}

if (!function_exists('set_prm_option')) {
    function set_prm_option($key, $value)
    {
        $CI = &get_instance();
        $existing = $CI->db->where('setting_key', $key)->get(db_prefix() . 'prm_settings')->row();
        if ($existing) {
            $CI->db->where('setting_key', $key)->update(db_prefix() . 'prm_settings', [
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $CI->db->insert(db_prefix() . 'prm_settings', [
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }
}

if (!function_exists('audit_log')) {
    function audit_log($entity_type, $action, $entity_id = null, $after = null, $severity = 'info')
    {
        $CI = &get_instance();
        $actor_type = is_partner_authenticated() ? 'partner' : (function_exists('is_logged_in') && is_logged_in() ? 'staff' : 'system');
        $actor_id   = $actor_type === 'partner'
            ? prm_current_partner_id()
            : (function_exists('get_staff_user_id') ? get_staff_user_id() : null);

        $CI->db->insert(db_prefix() . 'prm_audit_log', [
            'actor_type'   => $actor_type,
            'actor_id'     => $actor_id,
            'action'       => $action,
            'entity_type'  => $entity_type,
            'entity_id'    => $entity_id,
            'after_state'  => $after ? json_encode($after) : null,
            'ip_address'   => $CI->input->ip_address(),
            'user_agent'   => substr((string)$CI->input->user_agent(), 0, 255),
            'severity'     => $severity,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}

if (!function_exists('is_partner_authenticated')) {
    function is_partner_authenticated()
    {
        $CI = &get_instance();
        return (bool) $CI->session->userdata('prm_partner_id');
    }
}

if (!function_exists('prm_current_partner_id')) {
    function prm_current_partner_id()
    {
        $CI = &get_instance();
        return (int) $CI->session->userdata('prm_partner_id');
    }
}

if (!function_exists('prm_login_partner')) {
    function prm_login_partner($partner)
    {
        $CI = &get_instance();
        $CI->session->set_userdata([
            'prm_partner_id'   => $partner->id,
            'prm_partner_code' => $partner->partner_code,
            'prm_partner_name' => $partner->company_name,
            'prm_logged_in_at' => time(),
        ]);
        // create session row
        $CI->db->insert(db_prefix() . 'prm_partner_sessions', [
            'partner_id'    => $partner->id,
            'session_token' => bin2hex(random_bytes(32)),
            'ip_address'    => $CI->input->ip_address(),
            'user_agent'    => substr((string)$CI->input->user_agent(), 0, 255),
            'expires_at'    => date('Y-m-d H:i:s', strtotime('+'.(int)get_prm_option('prm_session_timeout', 120).' minutes')),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}

if (!function_exists('prm_logout_partner')) {
    function prm_logout_partner()
    {
        $CI = &get_instance();
        $pid = prm_current_partner_id();
        if ($pid) {
            $CI->db->where('partner_id', $pid)->update(db_prefix() . 'prm_partner_sessions', ['revoked' => 1]);
        }
        $CI->session->unset_userdata(['prm_partner_id','prm_partner_code','prm_partner_name','prm_logged_in_at']);
    }
}

if (!function_exists('prm_current_partner')) {
    function prm_current_partner()
    {
        $CI = &get_instance();
        $id = prm_current_partner_id();
        if (!$id) return null;
        $CI->load->model('partner_reseller_management/partners_model');
        return $CI->partners_model->get($id);
    }
}

if (!function_exists('prm_format_money')) {
    function prm_format_money($amount, $currency = null)
    {
        $cur = $currency ?: get_prm_option('prm_default_currency', 'USD');
        return $cur . ' ' . number_format((float)$amount, 2);
    }
}

if (!function_exists('prm_can')) {
    function prm_can($capability)
    {
        return is_admin() || (function_exists('staff_can') && staff_can($capability, 'partner_reseller_management'));
    }
}

if (!function_exists('prm_safe')) {
    /** Safe HTML output for views */
    function prm_safe($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('prm_status_label')) {
    function prm_status_label($status)
    {
        $map = [
            'pending'      => '<span class="label label-warning">Pending</span>',
            'approved'     => '<span class="label label-success">Approved</span>',
            'rejected'     => '<span class="label label-danger">Rejected</span>',
            'active'       => '<span class="label label-success">Active</span>',
            'suspended'    => '<span class="label label-warning">Suspended</span>',
            'terminated'   => '<span class="label label-danger">Terminated</span>',
            'submitted'    => '<span class="label label-info">Submitted</span>',
            'won'          => '<span class="label label-success">Won</span>',
            'lost'         => '<span class="label label-danger">Lost</span>',
            'paid'         => '<span class="label label-success">Paid</span>',
            'calculated'   => '<span class="label label-info">Calculated</span>',
            'on_hold'      => '<span class="label label-default">On hold</span>',
            'clawed_back'  => '<span class="label label-danger">Clawed back</span>',
        ];
        return $map[$status] ?? '<span class="label label-default">'.prm_safe($status).'</span>';
    }
}
