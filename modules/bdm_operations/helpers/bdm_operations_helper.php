<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * BDM Operations — Helper Library
 * Senior Dev Build (UAE Edition)
 */

/* ============================================================
 *  ROLE / PERMISSION HELPERS
 * ============================================================ */

if (!function_exists('is_purchase_manager')) {
    /**
     * Detect whether the staff member is in a "Purchase Manager" role.
     */
    function is_purchase_manager($staff_id = '')
    {
        if (!is_numeric($staff_id)) {
            $staff_id = get_staff_user_id();
        }
        $CI = &get_instance();
        $CI->load->model('staff_model');
        $staff = $CI->staff_model->get($staff_id);
        if ($staff && !empty($staff->role) && $staff->role != 0) {
            $CI->load->model('roles_model');
            $role = $CI->roles_model->get($staff->role);
            if ($role && stripos($role->name, 'purchase manager') !== false) {
                return true;
            }
        }
        return false;
    }
}

/* ============================================================
 *  DOMAIN ENUMS
 * ============================================================ */

if (!function_exists('bdm_pipeline_stages')) {
    /**
     * Legacy 9-stage status map (still used in the kanban + dropdowns).
     */
    function bdm_pipeline_stages()
    {
        return [
            'New Referral'          => _l('stage_new_referral'),
            'Contact Initiated'     => _l('stage_contact_initiated'),
            'Information Requested' => _l('stage_info_requested'),
            'Under Review'          => _l('stage_under_review'),
            'Qualified'             => _l('stage_qualified'),
            'Verification'          => _l('stage_verification'),
            'Final Approval'        => _l('stage_final_approval'),
            'Registered'            => _l('stage_registered'),
            'Rejected'              => _l('stage_rejected'),
        ];
    }
}

if (!function_exists('bdm_pipeline_steps')) {
    /**
     * Canonical 10-step BDM workflow (matches the Advanced Planning Document).
     */
    function bdm_pipeline_steps()
    {
        return [
            1  => _l('bdm_step_1_lead_id'),
            2  => _l('bdm_step_2_classify'),
            3  => _l('bdm_step_3_register'),
            4  => _l('bdm_step_4_relationship'),
            5  => _l('bdm_step_5_requirement'),
            6  => _l('bdm_step_6_quote'),
            7  => _l('bdm_step_7_deal'),
            8  => _l('bdm_step_8_execution'),
            9  => _l('bdm_step_9_payment'),
            10 => _l('bdm_step_10_long_term'),
        ];
    }
}

if (!function_exists('bdm_segments')) {
    function bdm_segments()
    {
        return [
            'Construction'         => _l('segment_construction'),
            'Facility Management'  => _l('segment_fm'),
            'Government'           => _l('segment_government'),
            'IT Companies'         => _l('segment_it_companies'),
            'Telecom'              => _l('segment_telecom'),
            'Hospitality'          => _l('segment_hospitality'),
            'Other'                => _l('segment_other'),
        ];
    }
}

if (!function_exists('bdm_emirates')) {
    function bdm_emirates()
    {
        return [
            'Abu Dhabi'      => 'Abu Dhabi',
            'Dubai'          => 'Dubai',
            'Sharjah'        => 'Sharjah',
            'Ajman'          => 'Ajman',
            'Ras Al Khaimah' => 'Ras Al Khaimah',
            'Fujairah'       => 'Fujairah',
            'Umm Al Quwain'  => 'Umm Al Quwain',
        ];
    }
}

if (!function_exists('bdm_priorities')) {
    function bdm_priorities()
    {
        return [
            'High'   => _l('priority_high'),
            'Medium' => _l('priority_medium'),
            'Low'    => _l('priority_low'),
        ];
    }
}

if (!function_exists('bdm_marketing_channels')) {
    function bdm_marketing_channels()
    {
        return [
            'Direct'      => _l('channel_direct'),
            'Telecalling' => _l('channel_telecalling'),
            'Email'       => _l('channel_email'),
            'Social'      => _l('channel_social'),
            'SEO'         => _l('channel_seo'),
        ];
    }
}

if (!function_exists('bdm_activity_types')) {
    function bdm_activity_types()
    {
        return [
            'Visit'    => _l('bdm_visit'),
            'Call'     => _l('bdm_call'),
            'Email'    => _l('bdm_email'),
            'WhatsApp' => _l('bdm_whatsapp'),
            'LinkedIn' => _l('bdm_linkedin'),
            'Meeting'  => _l('bdm_meeting'),
        ];
    }
}

if (!function_exists('bdm_kpi_metrics_definition')) {
    /**
     * Display label + monthly default target for KPI metrics.
     */
    function bdm_kpi_metrics_definition()
    {
        return [
            'leads'            => ['label' => _l('bdm_kpi_leads'),            'default' => 50],
            'visits'           => ['label' => _l('bdm_kpi_visits'),           'default' => 100],
            'calls'            => ['label' => _l('bdm_kpi_calls'),            'default' => 600],
            'vendor_regs'      => ['label' => _l('bdm_kpi_vendor_regs'),      'default' => 10],
            'client_approvals' => ['label' => _l('bdm_kpi_client_approvals'), 'default' => 3],
            'proposals'        => ['label' => _l('bdm_kpi_proposals'),        'default' => 20],
            'deals'            => ['label' => _l('bdm_kpi_deals'),            'default' => 4],
            'revenue_aed'      => ['label' => _l('bdm_kpi_revenue'),          'default' => 200000],
            'partner_regs'     => ['label' => _l('bdm_kpi_partners'),         'default' => 2],
            'email_open_rate'  => ['label' => _l('bdm_kpi_email_open'),       'default' => 28],
            'linkedin_growth'  => ['label' => _l('bdm_kpi_linkedin_growth'),  'default' => 200],
            'conversion_rate'  => ['label' => _l('bdm_kpi_conversion'),       'default' => 8],
        ];
    }
}

/* ============================================================
 *  FORMATTING HELPERS
 * ============================================================ */

if (!function_exists('bdm_aed')) {
    /**
     * Format an amount as AED with 2 decimals.
     */
    function bdm_aed($amount, $with_symbol = true)
    {
        $val = number_format((float) $amount, 2);
        return $with_symbol ? 'AED ' . $val : $val;
    }
}

if (!function_exists('bdm_pipeline_color')) {
    function bdm_pipeline_color($status)
    {
        $map = [
            'New Referral'          => '#2196f3',
            'Contact Initiated'     => '#03a9f4',
            'Information Requested' => '#00bcd4',
            'Under Review'          => '#9c27b0',
            'Qualified'             => '#673ab7',
            'Verification'          => '#ff9800',
            'Final Approval'        => '#ff5722',
            'Registered'            => '#4caf50',
            'Rejected'              => '#f44336',
        ];
        return $map[$status] ?? '#607d8b';
    }
}

if (!function_exists('bdm_status_label_class')) {
    function bdm_status_label_class($status)
    {
        switch ($status) {
            case 'Registered':       return 'success';
            case 'Rejected':         return 'danger';
            case 'New Referral':     return 'info';
            case 'Final Approval':
            case 'Verification':     return 'warning';
            default:                 return 'default';
        }
    }
}
