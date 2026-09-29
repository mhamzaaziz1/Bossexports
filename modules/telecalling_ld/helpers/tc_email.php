<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * tc_email.php — Email sending, queue processing, tracking
 * ─────────────────────────────────────────────────────────
 * tc_send_email($lead_id, $template_id)   Send one template to lead now
 * tc_queue_campaign($lead_id, $campaign)  Add full campaign to queue
 * tc_process_email_queue()                Send due items (called by cron)
 * tc_check_score_trigger($lead_id, $score) Trigger campaign if score met
 * tc_handle_unsubscribe($token)           Process unsubscribe click
 * tc_handle_bounce($email, $reason)       Remove bounced email
 */

function tc_send_email($lead_id, $template_id, $agent_id = null)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $tpl = $CI->db->get_where($pfx.'tc_templates',['id'=>$template_id])->row();
    if (!$tpl) return false;
    $lead = $CI->db->get_where('tblleads',['id'=>$lead_id])->row();
    if (!$lead) return false;

    // Check unsubscribe
    if ($CI->db->where('email',$lead->email)->count_all_results($pfx.'tc_unsubscribes')>0) return false;

    $token   = bin2hex(random_bytes(16));
    $subject = _tc_merge($tpl->subject,$lead);
    $body    = _tc_merge($tpl->body,$lead);

    // Append tracking pixel + unsubscribe link
    $unsub_url  = base_url('telecalling_ld/unsubscribe/'.$token);
    $track_url  = base_url('telecalling_ld/track_open/'.$token);
    $body .= '<br><br><small><a href="'.$unsub_url.'">Unsubscribe</a></small>';
    $body .= '<img src="'.$track_url.'" width="1" height="1" />';

    // Log before send (get ID for tracking)
    $CI->db->insert($pfx.'tc_email_log',[
        'lead_id'=>$lead_id,'agent_id'=>$agent_id??get_staff_user_id(),
        'template_id'=>$template_id,'to_email'=>$lead->email,
        'subject'=>$subject,'track_token'=>$token,'sent_at'=>date('Y-m-d H:i:s'),
    ]);

    // Send via configured method
    $result = _tc_do_send($lead->email,$subject,$body);
    if (!$result) {
        $CI->db->where('track_token',$token)->update($pfx.'tc_email_log',['bounced'=>1,'bounce_reason'=>'send_failed']);
    }
    return $result;
}

function tc_queue_campaign($lead_id, $campaign_id)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $templates = $CI->db->where('campaign_id',$campaign_id)->order_by('sort_order','ASC')->get($pfx.'tc_templates')->result();
    foreach ($templates as $tpl) {
        $send_after = date('Y-m-d H:i:s', strtotime("+{$tpl->delay_days} days"));
        $CI->db->insert($pfx.'tc_email_queue',[
            'lead_id'=>$lead_id,'template_id'=>$tpl->id,
            'campaign_id'=>$campaign_id,'send_after'=>$send_after,
        ]);
    }
}

function tc_process_email_queue()
{
    $CI   =& get_instance(); $pfx = $CI->db->dbprefix;
    $now  = date('Y-m-d H:i:s');
    $jobs = $CI->db->where('status','pending')->where("send_after <= '$now'")->limit(50)->get($pfx.'tc_email_queue')->result();
    foreach ($jobs as $j) {
        $CI->db->where('id',$j->id)->update($pfx.'tc_email_queue',['status'=>'sent','attempts'=>$j->attempts+1]);
        $ok = tc_send_email($j->lead_id,$j->template_id);
        if (!$ok) $CI->db->where('id',$j->id)->update($pfx.'tc_email_queue',['status'=>'failed']);
    }
}

function tc_check_score_trigger($lead_id, $score)
{
    $CI       =& get_instance(); $pfx = $CI->db->dbprefix;
    $campaigns = $CI->db->where('status','active')->where("trigger_score > 0")->where("trigger_score <=",  $score)->get($pfx.'tc_campaigns')->result();
    foreach ($campaigns as $c) {
        // Only queue once per lead per campaign
        if (!$CI->db->where(['lead_id'=>$lead_id,'campaign_id'=>$c->id])->count_all_results($pfx.'tc_email_queue'))
            tc_queue_campaign($lead_id,$c->id);
    }
}

function tc_handle_unsubscribe($token)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $log = $CI->db->where('track_token',$token)->get($pfx.'tc_email_log')->row();
    if (!$log) return false;
    $CI->db->where('track_token',$token)->update($pfx.'tc_email_log',['unsubscribed'=>1]);
    $lead = $CI->db->get_where('tblleads',['id'=>$log->lead_id])->row();
    if ($lead) $CI->db->replace($pfx.'tc_unsubscribes',['email'=>$lead->email,'lead_id'=>$lead->id,'reason'=>'user_request']);
    // Cancel pending queue items for this lead
    $CI->db->where('lead_id',$log->lead_id)->where('status','pending')->update($pfx.'tc_email_queue',['status'=>'skipped']);
    return true;
}

function tc_handle_bounce($email, $reason = 'hard_bounce')
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $CI->db->where('email',$email)->update($pfx.'tc_email_log',['bounced'=>1,'bounce_reason'=>$reason]);
    $CI->db->replace($pfx.'tc_unsubscribes',['email'=>$email,'reason'=>$reason]);
    $lead = $CI->db->where('email',$email)->limit(1)->get('tblleads')->row();
    if ($lead) $CI->db->where('lead_id',$lead->id)->where('status','pending')->update($pfx.'tc_email_queue',['status'=>'skipped']);
}

/* ── Internal: merge tags {{lead_name}} etc ── */
function _tc_merge($text, $lead)
{
    $map = [
        '{{lead_name}}'    => $lead->firstname.' '.$lead->lastname,
        '{{first_name}}'   => $lead->firstname,
        '{{company}}'      => $lead->company ?? '',
        '{{email}}'        => $lead->email,
        '{{phone}}'        => $lead->phonenumber ?? '',
        '{{source}}'       => $lead->source ?? '',
        '{{date}}'         => date('d M Y'),
    ];
    return str_replace(array_keys($map), array_values($map), $text);
}

/* ── Internal: actual send via SMTP or SES ── */
function _tc_do_send($to, $subject, $body)
{
    $method = tc_setting('tc_email_method') ?: 'smtp';
    if ($method === 'ses') {
        // AWS SES via curl
        $key    = tc_setting('tc_ses_key');
        $secret = tc_setting('tc_ses_secret');
        $region = tc_setting('tc_ses_region') ?: 'us-east-1';
        $from   = tc_setting('tc_email_from') ?: 'noreply@example.com';
        // Use Perfex built-in send_email as fallback if SES not configured
        if (!$key || !$secret) return _tc_smtp_send($to,$subject,$body);
        return _tc_ses_send($to,$subject,$body,$from,$key,$secret,$region);
    }
    return _tc_smtp_send($to,$subject,$body);
}

function _tc_smtp_send($to,$subject,$body)
{
    // Use Perfex CRM's built-in email sending
    $CI =& get_instance();
    $CI->load->library('email');
    $CI->email->initialize([
        'protocol'  => 'smtp',
        'smtp_host' => tc_setting('tc_smtp_host') ?: get_option('smtp_host'),
        'smtp_port' => tc_setting('tc_smtp_port') ?: get_option('smtp_port'),
        'smtp_user' => tc_setting('tc_smtp_user') ?: get_option('smtp_username'),
        'smtp_pass' => tc_setting('tc_smtp_pass') ?: get_option('smtp_password'),
        'mailtype'  => 'html',
    ]);
    $from = tc_setting('tc_email_from') ?: get_option('smtp_email');
    $CI->email->from($from)->to($to)->subject($subject)->message($body);
    return $CI->email->send(false);
}

function _tc_ses_send($to,$subject,$body,$from,$key,$secret,$region)
{
    // Minimal AWS SES v2 send via curl (no SDK needed)
    $endpoint = "https://email.{$region}.amazonaws.com/v2/email/outbound-emails";
    $payload  = json_encode([
        'FromEmailAddress' => $from,
        'Destination'      => ['ToAddresses'=>[$to]],
        'Content'          => ['Simple'=>['Subject'=>['Data'=>$subject],'Body'=>['Html'=>['Data'=>$body]]]],
    ]);
    $date     = gmdate('Ymd');
    $datetime = gmdate('Ymd\THis\Z');
    $amz_date = $datetime;
    // Sign request (AWS Signature V4)
    $headers  = "content-type:application/json\nhost:email.{$region}.amazonaws.com\nx-amz-date:{$amz_date}\n";
    $signed   = "content-type;host;x-amz-date";
    $hash     = hash('sha256',$payload);
    $cr       = "POST\n/v2/email/outbound-emails\n\n{$headers}\n{$signed}\n{$hash}";
    $scope    = "{$date}/{$region}/ses/aws4_request";
    $sts      = "AWS4-HMAC-SHA256\n{$amz_date}\n{$scope}\n".hash('sha256',$cr);
    $sk   = hash_hmac('sha256',$date,'AWS4'.$secret,true);
    $sr   = hash_hmac('sha256',$region,$sk,true);
    $ss   = hash_hmac('sha256','ses',$sr,true);
    $sig  = hash_hmac('sha256','aws4_request',$ss,true);
    $auth = "AWS4-HMAC-SHA256 Credential={$key}/{$scope},SignedHeaders={$signed},Signature=".hash_hmac('sha256',$sts,$sig);
    $ch   = curl_init($endpoint);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>["Content-Type: application/json","X-Amz-Date: $amz_date","Authorization: $auth"],
        CURLOPT_TIMEOUT=>10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300);
}
