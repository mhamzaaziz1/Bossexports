<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * tc_whatsapp.php — WhatsApp Cloud API integration
 * ─────────────────────────────────────────────────
 * tc_wa_send($lead_id, $message, $agent_id)   Send message to lead
 * tc_wa_send_template($lead_id, $tpl_name)    Send approved template
 * tc_wa_webhook_verify()                      Verify webhook (GET)
 * tc_wa_webhook_process($payload)             Process incoming (POST)
 */

function tc_wa_send($lead_id, $message, $agent_id = null)
{
    $CI      =& get_instance(); $pfx = $CI->db->dbprefix;
    $lead    = $CI->db->get_where('tblleads',['id'=>$lead_id])->row();
    if (!$lead) return false;
    $phone   = preg_replace('/[^0-9]/','',$lead->phonenumber ?? '');
    if (!$phone) return false;

    $token   = tc_setting('tc_wa_token');
    $phone_id = tc_setting('tc_wa_phone_id');
    if (!$token || !$phone_id) return false;

    $payload = json_encode([
        'messaging_product'=>'whatsapp','recipient_type'=>'individual',
        'to'=>$phone,'type'=>'text','text'=>['body'=>$message],
    ]);
    $ch = curl_init("https://graph.facebook.com/v18.0/{$phone_id}/messages");
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>["Content-Type: application/json","Authorization: Bearer $token"],
        CURLOPT_TIMEOUT=>10,
    ]);
    $resp = json_decode(curl_exec($ch),true);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);

    $wa_id = $resp['messages'][0]['id'] ?? null;
    $CI->db->insert($pfx.'tc_whatsapp_log',[
        'lead_id'=>$lead_id,'agent_id'=>$agent_id??get_staff_user_id(),
        'message'=>$message,'status'=>($code==200?'sent':'failed'),'wa_msg_id'=>$wa_id,
    ]);
    return ($code === 200);
}

function tc_wa_send_template($lead_id, $template_name, $lang = 'en_US')
{
    $CI      =& get_instance();
    $lead    = $CI->db->get_where('tblleads',['id'=>$lead_id])->row();
    if (!$lead) return false;
    $phone   = preg_replace('/[^0-9]/','',$lead->phonenumber ?? '');
    $token   = tc_setting('tc_wa_token');
    $phone_id = tc_setting('tc_wa_phone_id');
    if (!$token || !$phone_id || !$phone) return false;
    $payload = json_encode([
        'messaging_product'=>'whatsapp','to'=>$phone,'type'=>'template',
        'template'=>['name'=>$template_name,'language'=>['code'=>$lang]],
    ]);
    $ch = curl_init("https://graph.facebook.com/v18.0/{$phone_id}/messages");
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>["Content-Type: application/json","Authorization: Bearer $token"],CURLOPT_TIMEOUT=>10]);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code===200);
}
