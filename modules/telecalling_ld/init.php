<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * init.php — Registers all Perfex CRM hooks for this module.
 * ─────────────────────────────────────────────────────────────
 * HOW TO ADD A NEW HOOK:
 *   hooks()->add_action('hook_name', 'tc_your_function');
 *   function tc_your_function($param) { ... }
 *
 * Available Perfex hooks:
 *   admin_init, app_cron, lead_updated, leads_query_where,
 *   after_lead_profile_tab, notification_settings, etc.
 * ─────────────────────────────────────────────────────────────
 */

hooks()->add_action('lead_updated',           'tc_on_lead_updated');
hooks()->add_action('after_lead_profile_tab', 'tc_inject_lead_tabs');
hooks()->add_filter('leads_query_where',      'tc_filter_agent_leads');
hooks()->add_action('app_cron',               'tc_run_cron');
hooks()->add_action('notification_settings',  'tc_register_notifications');

/* ── Called every time a lead is saved ─────────────────────── */
function tc_on_lead_updated($lead_id)
{
    $CI      =& get_instance();
    $status  = $CI->input->post('tc_call_status');
    if (!$status) return;

    require_once module_dir_path('telecalling_ld').'helpers/tc_distribution.php';
    require_once module_dir_path('telecalling_ld').'helpers/tc_email.php';

    $scores = [
        'not_answered'=>0,'callback_later'=>5,'not_interested'=>10,
        'interested'=>25,'appointment_scheduled'=>60,'converted'=>80,
    ];
    $score    = $scores[$status] ?? 0;
    $agent_id = get_staff_user_id();
    $pfx      = $CI->db->dbprefix;

    $CI->db->insert($pfx.'tc_call_log', [
        'lead_id'     => $lead_id,
        'agent_id'    => $agent_id,
        'call_status' => $status,
        'notes'       => $CI->input->post('tc_notes'),
        'lead_score'  => $score,
        'ai_score'    => (tc_setting('tc_ai_enabled')=='1' && $CI->input->post('tc_notes')) ? -1 : 0,
        'called_at'   => date('Y-m-d H:i:s'),
    ]);

    // Mark assignment completed on terminal statuses
    if (in_array($status, ['not_interested','appointment_scheduled','converted'])) {
        $CI->db->where(['lead_id'=>$lead_id,'agent_id'=>$agent_id])
               ->update($pfx.'tc_assignments', [
                   'completed'=>1,'status'=>$status,'completed_at'=>date('Y-m-d H:i:s'),
               ]);
        tc_auto_assign_next($agent_id);
    }

    // Trigger email campaign if score threshold met
    tc_check_score_trigger($lead_id, $score);
    tc_check_target_achieved($agent_id);
}

/* ── Inject Call Log, Email, WhatsApp tabs on lead profile ── */
function tc_inject_lead_tabs()
{
    $CI      =& get_instance();
    $lead_id = (int)$CI->uri->segment(4);
    if (!$lead_id) return;

    require_once module_dir_path('telecalling_ld').'helpers/tc_distribution.php';

    // Load each partial — these echo tab <li> + tab pane content
    $CI->load->view('partials/lead_tab',    ['lead_id'=>$lead_id]);
    $CI->load->view('partials/email_tab',   ['lead_id'=>$lead_id]);
    $CI->load->view('partials/whatsapp_tab',['lead_id'=>$lead_id]);
}

/* ── Restrict agents to only see their assigned leads ─────── */
function tc_filter_agent_leads($where)
{
    if (is_admin()) return $where;

    require_once module_dir_path('telecalling_ld').'helpers/tc_distribution.php';

    $CI    =& get_instance();
    $staff = get_staff_user_id();
    $pfx   = $CI->db->dbprefix;

    if (tc_is_manager()) {
        $group = $CI->db->where('manager_id',$staff)->get($pfx.'tc_groups')->row();
        if (!$group) return $where;
        $members = $CI->db->where('group_id',$group->id)->get($pfx.'tc_group_members')->result_array();
        $ids = implode(',', array_column($members,'agent_id') ?: [0]);
        return $where." AND tblleads.id IN (SELECT lead_id FROM {$pfx}tc_assignments WHERE agent_id IN ($ids))";
    }

    return $where." AND tblleads.id IN (SELECT lead_id FROM {$pfx}tc_assignments WHERE agent_id=$staff AND completed=0)";
}

/* ── Cron: email queue processing + AI scoring ─────────────── */
function tc_run_cron()
{
    require_once module_dir_path('telecalling_ld').'helpers/tc_email.php';
    require_once module_dir_path('telecalling_ld').'helpers/tc_ai_score.php';
    tc_process_email_queue();
    tc_process_ai_queue();
}

/* ── Register notification types ───────────────────────────── */
function tc_register_notifications()
{
    add_notification_type('tc_leads_assigned',         'TC: Leads assigned to you');
    add_notification_type('tc_lead_request_submitted', 'TC: Agent requested more leads');
    add_notification_type('tc_target_achieved',        'TC: Agent achieved their target');
    add_notification_type('tc_request_approved',       'TC: Your lead request was approved');
    add_notification_type('tc_request_rejected',       'TC: Your lead request was rejected');
}
