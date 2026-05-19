<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarim_api Library
 *
 * Handles all communication between Perfex CRM and the Nedarim Plus
 * payment platform — both inbound (webhook parsing) and outbound
 * (push occasional charges to a customer card).
 *
 * Nedarim Plus API docs: https://matara.pro/nedarimplus/ApiDocumentation.html
 * Invoice4U API docs:    https://invoice4uapi.docs.apiary.io/
 */
class Nedarim_api
{
    /** @var object CodeIgniter instance */
    private $CI;

    /** @var string Nedarim Plus base API URL */
    private $base_url = 'https://www.matara.pro/nedarimplus/';

    /** @var string Institution number */
    private $mosad;

    /** @var string API validation token */
    private $api_valid;

    /** @var string Full API key */
    private $api_key;

    public function __construct()
    {
        $this->CI        = &get_instance();
        $this->mosad     = get_option('nedarimpay_mosad_number');
        $this->api_valid = get_option('nedarimpay_api_valid');
        $this->api_key   = get_option('nedarimpay_api_key');

        log_message('debug', 'Nedarim_api library loaded');
    }

    // =========================================================================
    // SECTION 1 — INBOUND: WEBHOOK / CALLBACK PARSING
    // =========================================================================

    /**
     * Parse and validate an inbound webhook POST from Nedarim Plus.
     *
     * Nedarim sends application/json via POST.
     * We support two event types:
     *   - "transaction"  (regular payment / installments)
     *   - "keva"         (new standing order setup)
     *
     * @param  string $raw_body  Raw php://input body
     * @return array|false       Parsed, normalised payload or false on failure
     */
    public function parse_webhook($raw_body)
    {
        if (empty($raw_body)) {
            return false;
        }

        $data = json_decode($raw_body, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            log_message('error', 'NedarimPay webhook: invalid JSON — ' . $raw_body);
            return false;
        }

        // Determine event type
        if (isset($data['TransactionId'])) {
            $data['_event_type'] = 'transaction';
        } elseif (isset($data['KevaId']) && !isset($data['TransactionId'])) {
            $data['_event_type'] = 'keva';
        } else {
            // Could be a combined payload — treat as transaction
            $data['_event_type'] = 'transaction';
        }

        return $data;
    }

    /**
     * Determine receipt type (student vs donation) from webhook payload.
     *
     * Logic:
     *  1. Match Groupe field against configured student/donation groupe values.
     *  2. If unmatched, fall back to "student" (safer default — staff can correct).
     *
     * @param  array  $payload  Parsed webhook data
     * @return string           NEDARIMPAY_TYPE_STUDENT | NEDARIMPAY_TYPE_DONATION
     */
    public function determine_receipt_type(array $payload)
    {
        $groupe           = strtolower(trim($payload['Groupe'] ?? ''));
        $student_groupe   = strtolower(trim(get_option('nedarimpay_student_groupe')));
        $donation_groupe  = strtolower(trim(get_option('nedarimpay_donation_groupe')));

        // Explicit donation groupe match
        if (!empty($donation_groupe) && $groupe === $donation_groupe) {
            return NEDARIMPAY_TYPE_DONATION;
        }

        // Explicit student groupe match OR no configuration (default)
        if (!empty($student_groupe) && $groupe === $student_groupe) {
            return NEDARIMPAY_TYPE_STUDENT;
        }

        // If donation groupe not configured, use keywords in comments/groupe
        $donation_keywords = ['donat', 'תרומ', 'תורמ', 'charity'];
        foreach ($donation_keywords as $kw) {
            if (stripos($groupe, $kw) !== false) {
                return NEDARIMPAY_TYPE_DONATION;
            }
            $comments = strtolower($payload['Comments'] ?? '');
            if (stripos($comments, $kw) !== false) {
                return NEDARIMPAY_TYPE_DONATION;
            }
        }

        return NEDARIMPAY_TYPE_STUDENT; // safe default
    }

    /**
     * Attempt to find (or create) a Perfex client from the webhook data.
     *
     * Match order (configurable via nedarimpay_match_field):
     *   email → phone → zeout → nedarim ClientId
     *
     * If no match found AND email is present, a new client is created.
     *
     * @param  array $payload  Parsed Nedarim payload
     * @return int|false       Perfex client userid or false
     */
    public function resolve_perfex_client(array $payload)
    {
        $this->CI->load->model('clients_model');

        $email  = trim($payload['Mail']        ?? '');
        $phone  = preg_replace('/\D/', '', $payload['Phone']      ?? '');
        $zeout  = trim($payload['Zeout']       ?? '');
        $name   = trim($payload['ClientName']  ?? '');
        $match  = get_option('nedarimpay_match_field') ?: 'email';

        $client_id = false;

        // 1. Match by email
        if ($match === 'email' && !empty($email)) {
            $this->CI->db->where('email', $email);
            $row = $this->CI->db->get(db_prefix() . 'clients')->row();
            if ($row) {
                $client_id = $row->userid;
            }
        }

        // 2. Match by phone (contacts table)
        if (!$client_id && !empty($phone)) {
            $this->CI->db->where('phonenumber', $phone);
            $row = $this->CI->db->get(db_prefix() . 'contacts')->row();
            if ($row) {
                $client_id = $row->userid;
            }
        }

        // 3. Match by ID number (zeout) stored in custom field or vat
        if (!$client_id && !empty($zeout)) {
            $this->CI->db->where('vat', $zeout);
            $row = $this->CI->db->get(db_prefix() . 'clients')->row();
            if ($row) {
                $client_id = $row->userid;
            }
        }

        // 4. Match by Nedarim ClientId stored in custom field
        if (!$client_id && !empty($payload['ClientId'])) {
            $this->CI->db->where('fieldto', 'customers');
            $this->CI->db->where('fieldname', 'nedarim_client_id');
            $cf = $this->CI->db->get(db_prefix() . 'customfields')->row();
            if ($cf) {
                $this->CI->db->where('fieldid', $cf->id);
                $this->CI->db->where('value', $payload['ClientId']);
                $cfv = $this->CI->db->get(db_prefix() . 'customfieldsvalues')->row();
                if ($cfv) {
                    $client_id = $cfv->relid;
                }
            }
        }

        // 5. Auto-create client if we have enough data
        if (!$client_id && (!empty($email) || !empty($name))) {
            $parts     = explode(' ', $name, 2);
            $firstname = $parts[0] ?? $name;
            $lastname  = $parts[1] ?? '';

            $insert = [
                'company'     => $name,
                'vat'         => $zeout,
                'active'      => 1,
                'datecreated' => date('Y-m-d H:i:s'),
                'addedfrom'   => 0,
            ];
            $this->CI->db->insert(db_prefix() . 'clients', $insert);
            $client_id = $this->CI->db->insert_id();

            if ($client_id && !empty($email)) {
                // Insert primary contact
                $contact = [
                    'userid'      => $client_id,
                    'email'       => $email,
                    'firstname'   => $firstname,
                    'lastname'    => $lastname,
                    'phonenumber' => $phone,
                    'is_primary'  => 1,
                    'active'      => 1,
                    'datecreated' => date('Y-m-d H:i:s'),
                ];
                $this->CI->db->insert(db_prefix() . 'contacts', $contact);
            }

            log_message('info', 'NedarimPay: auto-created Perfex client #' . $client_id . ' for ' . $name);
        }

        return $client_id;
    }

    // =========================================================================
    // SECTION 2 — OUTBOUND: PUSH OCCASIONAL CHARGE TO NEDARIM PLUS
    // =========================================================================

    /**
     * Push an occasional (one-time) charge to a customer card stored in
     * Nedarim Plus, so it will be bundled into their next monthly billing line.
     *
     * This uses the Nedarim Plus iframe postMessage approach or direct API —
     * the exact endpoint depends on the API key provided.
     *
     * @param  array $charge_data  {
     *   client_id_nedarim: string,  // Nedarim ClientId
     *   amount:            float,
     *   currency:          int,     // 1=ILS, 2=USD
     *   description:       string,
     *   groupe:            string,
     *   tashlumim:         int,     // 1 for single charge
     * }
     * @return array {success: bool, response: mixed, error: string|null}
     */
    public function push_charge(array $charge_data)
    {
        if (empty($this->mosad) || empty($this->api_valid)) {
            return [
                'success'  => false,
                'response' => null,
                'error'    => 'Nedarim Plus credentials not configured (Mosad / ApiValid)',
            ];
        }

        $post_fields = [
            'Mosad'       => $this->mosad,
            'ApiValid'    => $this->api_valid,
            'ClientId'    => $charge_data['client_id_nedarim'] ?? '',
            'Amount'      => number_format((float)($charge_data['amount'] ?? 0), 2, '.', ''),
            'Currency'    => $charge_data['currency']    ?? 1,
            'Tashlumim'   => $charge_data['tashlumim']   ?? 1,
            'Groupe'      => $charge_data['groupe']      ?? '',
            'Comment'     => $charge_data['description'] ?? '',
            'PaymentType' => 'Ragil',
        ];

        $result = $this->_post_to_nedarim('api/charge', $post_fields);
        return $result;
    }

    /**
     * Internal HTTP POST to Nedarim Plus API endpoint.
     *
     * @param  string $endpoint   Relative endpoint
     * @param  array  $fields     POST fields
     * @return array  {success, response, http_code, error}
     */
    private function _post_to_nedarim($endpoint, array $fields)
    {
        // Always use the Nedarim Plus base URL for API calls.
        // The api_key field is the iFrame payment URL — it must NOT override
        // server-to-server API endpoints, as that causes 406/404 responses.
        $url = rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            // Do NOT send Accept: application/json — Nedarim Plus returns plain
            // text or XML for some endpoints and will reply 406 if JSON-only is
            // declared. Accept anything and parse the body ourselves.
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: */*',
            ],
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            log_message('error', 'NedarimPay push_charge cURL error: ' . $curl_err);
            return ['success' => false, 'response' => null, 'http_code' => 0, 'error' => $curl_err];
        }

        // Try JSON first; fall back to raw string (Nedarim may return plain text)
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = trim($response);
        }

        $success = ($http_code >= 200 && $http_code < 300);

        log_message('info', 'NedarimPay push_charge HTTP ' . $http_code . ' → ' . $response);

        return [
            'success'   => $success,
            'response'  => $decoded,
            'http_code' => $http_code,
            'error'     => $success ? null : ('HTTP ' . $http_code . ': ' . $response),
        ];
    }

    // =========================================================================
    // SECTION 3 — DUPLICATE DETECTION
    // =========================================================================

    /**
     * Check if a TransactionId has already been processed.
     *
     * @param  string $transaction_id
     * @return bool
     */
    public function is_duplicate(string $transaction_id): bool
    {
        $this->CI->db->where('transaction_id', $transaction_id);
        $this->CI->db->where_in('status', ['processed', 'duplicate']);
        return $this->CI->db->count_all_results(db_prefix() . 'nedarimpay_transactions') > 0;
    }

    /**
     * Check if a KevaId has already been recorded.
     *
     * @param  string $keva_id
     * @return bool
     */
    public function is_duplicate_keva(string $keva_id): bool
    {
        $this->CI->db->where('keva_id', $keva_id);
        return $this->CI->db->count_all_results(db_prefix() . 'nedarimpay_keva') > 0;
    }
}
