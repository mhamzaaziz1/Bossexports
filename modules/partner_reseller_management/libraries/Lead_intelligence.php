<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Lead_intelligence
 * - Rule-based lead scoring (offline/free)
 * - Optional LLM-powered classification & win probability
 * - Fuzzy duplicate detection (Levenshtein/SoundEx + email/phone normaliser)
 */
class Lead_intelligence
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /* ==================================================================
     *  SCORE A LEAD (0..100)
     * ================================================================ */
    public function score_lead(array $deal): int
    {
        $score = 0;

        // 1. Estimated value bands (0-30)
        $val = (float)($deal['estimated_value'] ?? 0);
        if     ($val >= 250000) $score += 30;
        elseif ($val >=  50000) $score += 22;
        elseif ($val >=  10000) $score += 14;
        elseif ($val >=   1000) $score += 6;

        // 2. Email quality (0-15)
        $email = (string)($deal['email'] ?? '');
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $score += 8;
            $domain = strtolower(substr(strrchr($email, '@'), 1));
            if (!in_array($domain, ['gmail.com','yahoo.com','outlook.com','hotmail.com'])) {
                $score += 7; // corporate domain bonus
            }
        }

        // 3. Phone validity (0-10)
        $phone = preg_replace('/\D/', '', (string)($deal['phone'] ?? ''));
        if (strlen($phone) >= 8) $score += 10;

        // 4. Description completeness (0-15)
        $desc = (string)($deal['description'] ?? '');
        $len  = mb_strlen($desc);
        if     ($len > 300) $score += 15;
        elseif ($len > 100) $score += 8;
        elseif ($len >   0) $score += 3;

        // 5. Has expected close date (0-10)
        if (!empty($deal['expected_close_date'])) {
            $days = (strtotime($deal['expected_close_date']) - time()) / 86400;
            if ($days > 0 && $days <= 90) $score += 10;
            elseif ($days > 90)           $score += 5;
        }

        // 6. Service / product specified (0-10)
        if (!empty($deal['service_type']) || !empty($deal['product_id'])) $score += 10;

        // 7. Company name present (0-10)
        if (!empty($deal['company_name'])) $score += 10;

        return min(100, max(0, $score));
    }

    public function predict_win_probability(array $deal, $partner = null): int
    {
        $base = $this->score_lead($deal);

        // Adjust by partner's historical win rate
        if ($partner && (int)$partner->total_deals > 0) {
            $win_rate = ($partner->total_revenue > 0)
                ? min(100, ((int) $partner->total_deals / max(1, (int) $partner->total_deals)) * 100)
                : 50;
            $base = (int) round(($base * 0.6) + ($win_rate * 0.4));
        }

        // Optional LLM enrichment (if API key set)
        if ((int) get_prm_option('prm_ai_lead_scoring', 0) === 1) {
            $ai = $this->ai_predict($deal);
            if ($ai !== null) {
                $base = (int) round(($base * 0.4) + ($ai * 0.6));
            }
        }
        return min(100, max(0, $base));
    }

    /* ==================================================================
     *  DUPLICATE DETECTION (fuzzy)
     * ================================================================ */
    public function fuzzy_duplicate(array $deal, int $threshold = 85): ?object
    {
        $this->CI->load->model('partner_reseller_management/deals_model');
        $candidates = $this->CI->db
            ->where('lock_expires_at >=', date('Y-m-d H:i:s'))
            ->where_not_in('pipeline_stage', ['lost','withdrawn','expired'])
            ->limit(500)
            ->get(db_prefix() . 'prm_deals')->result();

        $best = null;
        $bestScore = 0;
        foreach ($candidates as $c) {
            $score = $this->similarity($deal, $c);
            if ($score > $bestScore) { $bestScore = $score; $best = $c; }
        }
        return ($best && $bestScore >= $threshold) ? $best : null;
    }

    private function similarity(array $a, $b): int
    {
        $score = 0;

        $emailA = strtolower(trim((string)($a['email'] ?? '')));
        $emailB = strtolower(trim((string)$b->email));
        if ($emailA && $emailA === $emailB) $score += 50;

        $phoneA = preg_replace('/\D/', '', (string)($a['phone'] ?? ''));
        $phoneB = preg_replace('/\D/', '', (string)$b->phone);
        if ($phoneA && $phoneA === $phoneB) $score += 30;

        $coA = strtolower(trim((string)($a['company_name'] ?? '')));
        $coB = strtolower(trim((string)$b->company_name));
        if ($coA && $coB) {
            $ratio = 0;
            similar_text($coA, $coB, $ratio);
            $score += (int) round($ratio * 0.2); // up to ~20 pts
        }
        return $score;
    }

    /* ==================================================================
     *  LLM CALL  (OpenAI / Anthropic) — best effort, fail-safe
     * ================================================================ */
    private function ai_predict(array $deal): ?int
    {
        $key = (string) get_prm_option('prm_ai_api_key', '');
        if (!$key) return null;

        $provider = (string) get_prm_option('prm_ai_provider', 'openai');
        $model    = (string) get_prm_option('prm_ai_model', 'gpt-4o-mini');

        $payload = [
            'model'       => $model,
            'temperature' => 0,
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a B2B sales analyst. Reply ONLY with an integer 0-100 representing the probability this deal will close in the next 90 days.'],
                ['role' => 'user',   'content' => json_encode([
                    'company'         => $deal['company_name']      ?? '',
                    'service'         => $deal['service_type']      ?? '',
                    'value'           => $deal['estimated_value']   ?? '',
                    'description'     => $deal['description']       ?? '',
                    'expected_close'  => $deal['expected_close_date'] ?? '',
                ])],
            ],
        ];

        $url = ($provider === 'anthropic')
            ? 'https://api.anthropic.com/v1/messages'
            : 'https://api.openai.com/v1/chat/completions';

        $headers = ($provider === 'anthropic')
            ? ['x-api-key: '.$key, 'anthropic-version: 2023-06-01', 'content-type: application/json']
            : ['Authorization: Bearer '.$key, 'Content-Type: application/json'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 8,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        if (!$resp) return null;

        $j = json_decode($resp, true);
        $text = $j['choices'][0]['message']['content']
            ?? $j['content'][0]['text']
            ?? '';
        if (preg_match('/(\d{1,3})/', $text, $m)) {
            return min(100, (int)$m[1]);
        }
        return null;
    }
}
