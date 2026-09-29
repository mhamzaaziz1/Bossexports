<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * tc_ai_score.php — AI-powered call scoring via Anthropic / OpenAI
 * ─────────────────────────────────────────────────────────────────
 * tc_process_ai_queue()        Called by cron — scores pending calls
 * tc_ai_score_call($log_row)   Score one call log row, return array
 */

function tc_process_ai_queue()
{
    if (tc_setting('tc_ai_enabled') != '1') return;
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $pending = $CI->db->where('ai_score',-1)->where('notes !=','')->limit(10)->get($pfx.'tc_call_log')->result();
    foreach ($pending as $row) {
        $result = tc_ai_score_call($row);
        $CI->db->where('id',$row->id)->update($pfx.'tc_call_log',[
            'ai_score'      => $result['score'] ?? 0,
            'ai_suggestion' => $result['suggestion'] ?? '',
        ]);
    }
}

function tc_ai_score_call($log_row)
{
    $provider = tc_setting('tc_ai_provider') ?: 'anthropic';
    $api_key  = tc_setting('tc_ai_api_key');
    if (!$api_key) return ['score'=>0,'suggestion'=>''];

    $prompt = "You are a telemarketing call analyst. Analyse this call note and respond ONLY in JSON with keys: score (0-100 integer based on call quality and lead interest) and suggestion (one sentence recommended next action).\n\nCall status: {$log_row->call_status}\nNotes: {$log_row->notes}";

    if ($provider === 'anthropic') {
        $payload = json_encode(['model'=>'claude-haiku-4-5-20251001','max_tokens'=>200,
            'messages'=>[['role'=>'user','content'=>$prompt]]]);
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
            CURLOPT_HTTPHEADER=>["Content-Type: application/json","x-api-key: $api_key","anthropic-version: 2023-06-01"],
            CURLOPT_TIMEOUT=>15]);
        $resp = json_decode(curl_exec($ch),true); curl_close($ch);
        $text = $resp['content'][0]['text'] ?? '{}';
    } else {
        $payload = json_encode(['model'=>'gpt-3.5-turbo','max_tokens'=>200,
            'messages'=>[['role'=>'user','content'=>$prompt]]]);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
            CURLOPT_HTTPHEADER=>["Content-Type: application/json","Authorization: Bearer $api_key"],CURLOPT_TIMEOUT=>15]);
        $resp = json_decode(curl_exec($ch),true); curl_close($ch);
        $text = $resp['choices'][0]['message']['content'] ?? '{}';
    }
    $clean = preg_replace('/```json|```/','',$text);
    $data  = json_decode(trim($clean),true);
    return ['score'=>(int)($data['score']??0),'suggestion'=>substr($data['suggestion']??'',0,255)];
}
