<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Notification_hub
 * Multi-channel notification dispatcher: email, SMS, WhatsApp, Telegram,
 * in-app, webhook.  Honours partner preferences and event templates.
 */
class Notification_hub
{
    private $CI;
    private $templates;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->templates = [
            'deal_submitted' => [
                'subject' => 'Deal #{deal_number} submitted',
                'body'    => 'Hi {partner_name}, your deal #{deal_number} for {client_name} has been received. We will review it shortly.',
            ],
            'deal_approved' => [
                'subject' => 'Deal #{deal_number} approved',
                'body'    => 'Good news {partner_name}! Your deal #{deal_number} for {client_name} has been approved.',
            ],
            'deal_rejected' => [
                'subject' => 'Deal #{deal_number} requires attention',
                'body'    => 'Hello {partner_name}, your deal #{deal_number} could not be approved. Reason: {reason}.',
            ],
            'commission_paid' => [
                'subject' => 'Commission payout confirmed',
                'body'    => 'Hi {partner_name}, a commission of {amount} has been paid to your wallet.',
            ],
            'tier_promoted' => [
                'subject' => 'You have been promoted to {tier}!',
                'body'    => 'Congrats {partner_name} — you reached {tier} tier. Enjoy your new perks!',
            ],
            'badge_earned' => [
                'subject' => 'New badge unlocked: {badge}',
                'body'    => 'You earned the "{badge}" badge — keep going, {partner_name}!',
            ],
        ];
    }

    public function send_to_partner($partner_id, $event, array $vars = [])
    {
        $this->CI->load->model('partner_reseller_management/partners_model');
        $partner = $this->CI->partners_model->get($partner_id);
        if (!$partner) return false;

        $vars = array_merge($vars, [
            'partner_name' => $partner->company_name,
            'partner_code' => $partner->partner_code,
        ]);

        $tpl = $this->templates[$event] ?? null;
        if (!$tpl) return false;

        $subject = $this->render($tpl['subject'], $vars);
        $body    = $this->render($tpl['body'],    $vars);

        // In-app
        $this->in_app($partner_id, $subject, $body);

        // Email
        if ((int)get_prm_option('prm_notify_email', 1) === 1 && !empty($partner->email)) {
            $this->email($partner->email, $subject, $body);
        }
        // WhatsApp
        if ((int)get_prm_option('prm_notify_whatsapp', 0) === 1 && !empty($partner->phone)) {
            $this->whatsapp($partner->phone, $body);
        }
        // Telegram
        if ((int)get_prm_option('prm_notify_telegram', 0) === 1 && !empty($partner->phone)) {
            $this->telegram($partner->phone, $body);
        }
        // Webhook
        $this->webhook($event, ['partner_id' => $partner_id, 'vars' => $vars]);

        return true;
    }

    public function in_app($partner_id, $title, $message, $link = '')
    {
        $this->CI->db->insert(db_prefix().'prm_audit_log', [
            'actor_type' => 'system',
            'actor_id'   => 0,
            'action'     => 'notify',
            'entity_type'=> 'partner',
            'entity_id'  => $partner_id,
            'after_state'=> json_encode(['title'=>$title,'message'=>$message,'link'=>$link]),
            'severity'   => 'info',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function email($to, $subject, $message)
    {
        $this->CI->load->library('email');
        $this->CI->email->clear();
        $this->CI->email->from(get_option('smtp_email') ?: 'no-reply@example.com', get_option('companyname'));
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message(nl2br($message));
        return @$this->CI->email->send();
    }

    public function whatsapp($phone, $message)
    {
        $token = (string)get_prm_option('prm_whatsapp_token', '');
        $phid  = (string)get_prm_option('prm_whatsapp_phone_id', '');
        if (!$token || !$phid) return false;

        $url = "https://graph.facebook.com/v18.0/{$phid}/messages";
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => preg_replace('/\D/', '', $phone),
            'type'              => 'text',
            'text'              => ['body' => $message],
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$token, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 6,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return $r !== false;
    }

    public function telegram($chat_id, $message)
    {
        $token = (string)get_prm_option('prm_telegram_bot_token', '');
        if (!$token) return false;
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['chat_id'=>$chat_id,'text'=>$message]),
            CURLOPT_TIMEOUT => 6,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return $r !== false;
    }

    public function webhook($event, array $payload)
    {
        $url = (string)get_prm_option('prm_webhook_url', '');
        if (!$url) return false;
        $secret = (string)get_prm_option('prm_webhook_secret', '');
        $body   = json_encode(['event' => $event, 'payload' => $payload, 'ts' => time()]);
        $sig    = hash_hmac('sha256', $body, $secret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-PRM-Signature: '.$sig],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 6,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return $r !== false;
    }

    private function render($tpl, array $vars)
    {
        foreach ($vars as $k => $v) {
            $tpl = str_replace('{'.$k.'}', (string)$v, $tpl);
        }
        return $tpl;
    }
}
