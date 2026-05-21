<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarimpay Controller
 *
 * Handles:
 *  - /nedarimpay/webhook        — public endpoint for Nedarim Plus callbacks (no auth)
 *  - /nedarimpay/dashboard      — admin overview
 *  - /nedarimpay/transactions   — full transaction log with filters
 *  - /nedarimpay/manual_charge  — push occasional charge to Nedarim
 *  - /nedarimpay/settings       — module configuration
 */
class Nedarimpay extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('nedarimpay/nedarim_api');
        $this->load->library('nedarimpay/nedarim_receipt');
    }

    // =========================================================================
    // WEBHOOK — Public endpoint (no session/admin auth required)
    // =========================================================================

    /**
     * POST /nedarimpay/webhook
     *
     * Nedarim Plus calls this URL after every successful payment or new
     * standing-order setup. The data arrives as application/json POST body.
     *
     * Security:
     *  - We do NOT require admin login (this is server-to-server).
     *  - Duplicate transaction IDs are silently accepted (200 OK) to prevent
     *    Nedarim from retrying endlessly.
     *  - All raw payloads are logged before any processing.
     */
    public function webhook()
    {
        // Bypass the admin auth layer — this is a server callback
        // We respond with 200 in all non-fatal cases to stop Nedarim retries.
        $this->output->set_content_type('application/json');

        $raw_body = file_get_contents('php://input');
        $ip       = $this->input->ip_address();

        // ── Log raw payload immediately ───────────────────────────────────────
        $log_id = $this->_log_webhook($raw_body, $ip);

        // ── Parse ─────────────────────────────────────────────────────────────
        $payload = $this->nedarim_api->parse_webhook($raw_body);
        if (!$payload) {
            $this->_update_webhook_log($log_id, 'error', 'Invalid JSON or empty body');
            $this->_json_response(false, 'Invalid payload');
            return;
        }

        $event_type   = $payload['_event_type'];
        $receipt_type = $this->nedarim_api->determine_receipt_type($payload);

        // ── TRANSACTION event ─────────────────────────────────────────────────
        if ($event_type === 'transaction') {
            $transaction_id = $payload['TransactionId'] ?? '';

            // Duplicate guard
            if ($this->nedarim_api->is_duplicate($transaction_id)) {
                $this->_update_webhook_log($log_id, 'duplicate', 'TxID already processed: ' . $transaction_id);
                $this->_json_response(true, 'Duplicate — already processed');
                return;
            }

            // Resolve client
            $client_id = $this->nedarim_api->resolve_perfex_client($payload);
            if (!$client_id) {
                $this->_update_webhook_log($log_id, 'error', 'Could not resolve or create client');
                $this->_json_response(false, 'Client resolution failed');
                return;
            }

            // Process: create invoice + payment + send email
            $result = $this->nedarim_receipt->process_transaction($payload, $client_id, $receipt_type);

            if ($result['success']) {
                $this->_update_webhook_log($log_id, 'processed',
                    'Invoice #' . $result['invoice_id'] . ' | Receipt: ' . $result['receipt_number']
                );
                $this->_json_response(true, 'OK', [
                    'receipt_number' => $result['receipt_number'],
                    'invoice_id'     => $result['invoice_id'],
                ]);
            } else {
                $this->_update_webhook_log($log_id, 'error', $result['error']);
                $this->_json_response(false, $result['error']);
            }
            return;
        }

        // ── KEVA (standing order) event ───────────────────────────────────────
        if ($event_type === 'keva') {
            $keva_id = $payload['KevaId'] ?? '';

            if ($this->nedarim_api->is_duplicate_keva($keva_id)) {
                $this->_update_webhook_log($log_id, 'duplicate', 'KevaId already recorded: ' . $keva_id);
                $this->_json_response(true, 'Duplicate keva — already recorded');
                return;
            }

            $client_id = $this->nedarim_api->resolve_perfex_client($payload);
            $result    = $this->nedarim_receipt->process_keva($payload, (int)$client_id, $receipt_type);

            $this->_update_webhook_log(
                $log_id,
                $result['success'] ? 'processed' : 'error',
                $result['error'] ?? 'Keva row #' . $result['row_id']
            );
            $this->_json_response($result['success'], $result['success'] ? 'OK' : $result['error']);
            return;
        }

        $this->_json_response(false, 'Unknown event type');
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================

    public function dashboard()
    {
        if (!has_permission('nedarimpay', '', 'view')) {
            access_denied('NedarimPay');
        }

        // Summary stats
        $data['total_transactions'] = $this->db
            ->where('status', 'processed')
            ->count_all_results(db_prefix() . 'nedarimpay_transactions');

        $data['total_student_amount'] = (float)$this->db
            ->select_sum('amount')
            ->where('status', 'processed')
            ->where('receipt_type', 'student')
            ->get(db_prefix() . 'nedarimpay_transactions')->row()->amount;

        $data['total_donation_amount'] = (float)$this->db
            ->select_sum('amount')
            ->where('status', 'processed')
            ->where('receipt_type', 'donation')
            ->get(db_prefix() . 'nedarimpay_transactions')->row()->amount;

        $data['pending_count'] = $this->db
            ->where('status', 'pending')
            ->count_all_results(db_prefix() . 'nedarimpay_transactions');

        $data['failed_count'] = $this->db
            ->where('status', 'failed')
            ->count_all_results(db_prefix() . 'nedarimpay_transactions');

        // Last 10 transactions
        $data['recent_transactions'] = $this->db
            ->order_by('created_at', 'desc')
            ->limit(10)
            ->get(db_prefix() . 'nedarimpay_transactions')->result_array();

        // Active standing orders
        $data['active_keva'] = $this->db
            ->count_all(db_prefix() . 'nedarimpay_keva');

        $data['title'] = _l('nedarimpay_dashboard');
        $this->load->view('nedarimpay/dashboard', $data);
    }

    // =========================================================================
    // TRANSACTIONS LOG
    // =========================================================================

    public function transactions()
    {
        if (!has_permission('nedarimpay', '', 'view')) {
            access_denied('NedarimPay');
        }

        $filters = [
            'receipt_type' => $this->input->get('receipt_type'),
            'status'       => $this->input->get('status'),
            'date_from'    => $this->input->get('date_from'),
            'date_to'      => $this->input->get('date_to'),
            'search'       => $this->input->get('search'),
        ];

        $pfx  = db_prefix();
        $tx_w = [];
        $mc_w = [];

        if (!empty($filters['receipt_type'])) {
            $v      = $this->db->escape($filters['receipt_type']);
            $tx_w[] = "t.receipt_type = $v";
            $mc_w[] = "m.receipt_type = $v";
        }
        if (!empty($filters['status'])) {
            $tx_w[] = 't.status = ' . $this->db->escape($filters['status']);
            // nedarimpay_manual_charges uses sent/confirmed/failed/pending
            switch ($filters['status']) {
                case 'processed': $mc_w[] = "m.status IN ('sent','confirmed')"; break;
                case 'pending':   $mc_w[] = "m.status = 'pending'"; break;
                case 'failed':    $mc_w[] = "m.status = 'failed'"; break;
                default:          $mc_w[] = '1=0'; // 'duplicate' not in manual_charges
            }
        }
        if (!empty($filters['date_from'])) {
            $v      = $this->db->escape($filters['date_from']);
            $tx_w[] = "DATE(t.created_at) >= $v";
            $mc_w[] = "DATE(m.created_at) >= $v";
        }
        if (!empty($filters['date_to'])) {
            $v      = $this->db->escape($filters['date_to']);
            $tx_w[] = "DATE(t.created_at) <= $v";
            $mc_w[] = "DATE(m.created_at) <= $v";
        }
        if (!empty($filters['search'])) {
            $s      = $this->db->escape('%' . $this->db->escape_like_str($filters['search']) . '%');
            $tx_w[] = "(t.client_name LIKE $s OR t.email LIKE $s OR t.transaction_id LIKE $s OR t.receipt_number LIKE $s)";
            $mc_w[] = "(c.company LIKE $s OR m.description LIKE $s)";
        }

        $tx_where = $tx_w ? 'WHERE ' . implode(' AND ', $tx_w) : '';
        $mc_where = $mc_w ? 'WHERE ' . implode(' AND ', $mc_w) : '';

        $sql = "
            SELECT
                t.id, t.transaction_id, t.receipt_number, t.receipt_type,
                t.perfex_client_id, t.perfex_invoice_id,
                t.client_name, t.email, t.amount, t.currency,
                t.transaction_type, t.email_sent, t.email_sent_at,
                t.created_at, t.status, 'webhook' AS makor
            FROM `{$pfx}nedarimpay_transactions` t
            $tx_where
            UNION ALL
            SELECT
                m.id,
                NULL                                                        AS transaction_id,
                COALESCE(m.receipt_number, CONCAT('MC-', LPAD(m.id, 5, '0'))) AS receipt_number,
                m.receipt_type,
                m.perfex_client_id,
                NULL               AS perfex_invoice_id,
                c.company          AS client_name,
                NULL               AS email,
                m.amount,
                m.currency,
                m.charge_type      AS transaction_type,
                0                  AS email_sent,
                NULL               AS email_sent_at,
                m.created_at,
                CASE m.status
                    WHEN 'sent'      THEN 'processed'
                    WHEN 'confirmed' THEN 'processed'
                    WHEN 'failed'    THEN 'failed'
                    ELSE 'pending'
                END                AS status,
                'manual_charge'    AS makor
            FROM `{$pfx}nedarimpay_manual_charges` m
            LEFT JOIN `{$pfx}clients` c ON c.userid = m.perfex_client_id
            $mc_where
            ORDER BY created_at DESC
        ";

        $data['transactions'] = $this->db->query($sql)->result_array();
        $data['filters']      = $filters;
        $data['title']        = _l('nedarimpay_transactions');
        $this->load->view('nedarimpay/transactions', $data);
    }

    // =========================================================================
    // TRANSACTION DETAIL
    // =========================================================================

    public function transaction_detail($id)
    {
        if (!has_permission('nedarimpay', '', 'view')) {
            access_denied('NedarimPay');
        }

        $id  = (int)$id;
        $src = $this->input->get('src');

        if ($src === 'mc') {
            // Manual charge from nedarimpay_manual_charges
            $row = $this->db->where('id', $id)
                       ->get(db_prefix() . 'nedarimpay_manual_charges')->row_array();

            if (!$row) {
                show_404();
            }

            // Fetch client name from clients table
            $client_name = '';
            if (!empty($row['perfex_client_id'])) {
                $c = $this->db->select('company')->where('userid', $row['perfex_client_id'])
                        ->get(db_prefix() . 'clients')->row();
                $client_name = $c ? $c->company : '';
            }

            // Map manual charge fields to the transaction view structure
            $status_map = ['sent' => 'processed', 'confirmed' => 'processed',
                           'failed' => 'failed', 'pending' => 'pending'];

            $tx = [
                'id'                => $row['id'],
                'transaction_id'    => null,
                'keva_id'           => null,
                'client_id_nedarim' => $row['client_id_nedarim'],
                'perfex_client_id'  => $row['perfex_client_id'],
                'perfex_invoice_id' => null,
                'perfex_payment_id' => null,
                'receipt_type'      => $row['receipt_type'],
                'receipt_number'    => $row['receipt_number'] ?: ('MC-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT)),
                'zeout'             => null,
                'client_name'       => $client_name,
                'email'             => null,
                'phone'             => null,
                'address'           => null,
                'amount'            => $row['amount'],
                'currency'          => $row['currency'],
                'transaction_type'  => $row['charge_type'],
                'groupe'            => $row['groupe'],
                'comments'          => $row['description'],
                'tashloumim'        => null,
                'last_num'          => null,
                'tokef'             => null,
                'confirmation'      => null,
                'shovar'            => null,
                'makor'             => 'manual_charge',
                'masof_id'          => null,
                'debit_iframe'      => 0,
                'transaction_time'  => $row['created_at'],
                'raw_payload'       => $row['nedarim_response'],
                'email_sent'        => 0,
                'email_sent_at'     => null,
                'status'            => $status_map[$row['status']] ?? 'pending',
                'error_message'     => null,
                'created_at'        => $row['created_at'],
            ];
        } else {
            $tx = $this->db->where('id', $id)
                      ->get(db_prefix() . 'nedarimpay_transactions')->row_array();

            if (!$tx) {
                show_404();
            }
        }

        $data['transaction'] = $tx;
        $data['title']       = _l('nedarimpay_transaction_detail');
        $this->load->view('nedarimpay/transaction_detail', $data);
    }

    // =========================================================================
    // MANUAL CHARGE
    // =========================================================================

    /**
     * GET  /nedarimpay/manual_charge   — show form
     * POST /nedarimpay/manual_charge   — submit charge to Nedarim
     */
    public function manual_charge()
    {
        if (!has_permission('nedarimpay', '', 'create')) {
            access_denied('NedarimPay Manual Charge');
        }

        if ($this->input->post()) {
            $charge_data = [
                'client_id_nedarim' => $this->input->post('client_id_nedarim', true),
                'amount'            => (float)$this->input->post('amount'),
                'currency'          => (int)$this->input->post('currency'),
                'description'       => $this->input->post('description', true),
                'groupe'            => $this->input->post('groupe', true),
                'tashlumim'         => (int)$this->input->post('tashlumim') ?: 1,
                'receipt_type'      => $this->input->post('receipt_type', true),
                'charge_type'       => $this->input->post('charge_type', true),
                'perfex_client_id'  => (int)$this->input->post('perfex_client_id'),
            ];

            // Basic validation
            if (empty($charge_data['client_id_nedarim']) || $charge_data['amount'] <= 0) {
                set_alert('danger', _l('nedarimpay_charge_validation_error'));
                redirect(admin_url('nedarimpay/manual_charge'));
                return;
            }

            $result         = $this->nedarim_api->push_charge($charge_data);
            $receipt_number = $this->nedarim_receipt->generate_receipt_number($charge_data['receipt_type']);

            // Log the charge attempt
            $this->db->insert(db_prefix() . 'nedarimpay_manual_charges', [
                'perfex_client_id'  => $charge_data['perfex_client_id'],
                'client_id_nedarim' => $charge_data['client_id_nedarim'],
                'receipt_number'    => $receipt_number,
                'amount'            => $charge_data['amount'],
                'currency'          => $charge_data['currency'],
                'description'       => $charge_data['description'],
                'groupe'            => $charge_data['groupe'],
                'receipt_type'      => $charge_data['receipt_type'],
                'charge_type'       => $charge_data['charge_type'],
                'nedarim_response'  => json_encode($result['response'] ?? ''),
                'status'            => $result['success'] ? 'sent' : 'failed',
                'staff_id'          => get_staff_user_id(),
            ]);

            if ($result['success']) {
                set_alert('success', _l('nedarimpay_charge_sent_success'));
            } else {
                set_alert('danger', _l('nedarimpay_charge_sent_error') . ': ' . $result['error']);
            }

            redirect(admin_url('nedarimpay/manual_charge'));
            return;
        }

        // Load clients for dropdown
        $data['clients'] = $this->db
            ->select('userid, company')
            ->order_by('company', 'asc')
            ->get(db_prefix() . 'clients')->result_array();

        $data['title'] = _l('nedarimpay_manual_charge');
        $this->load->view('nedarimpay/manual_charge', $data);
    }

    // =========================================================================
    // SETTINGS
    // =========================================================================

    public function settings()
    {
        if (!has_permission('nedarimpay', '', 'edit')) {
            access_denied('NedarimPay Settings');
        }

        if ($this->input->post()) {
            $fields = [
                'nedarimpay_mosad_number',
                'nedarimpay_api_valid',
                'nedarimpay_api_key',
                'nedarimpay_webhook_secret',
                'nedarimpay_student_prefix',
                'nedarimpay_donation_prefix',
                'nedarimpay_student_email_subject',
                'nedarimpay_student_email_body',
                'nedarimpay_donation_email_subject',
                'nedarimpay_donation_email_body',
                'nedarimpay_student_groupe',
                'nedarimpay_donation_groupe',
                'nedarimpay_match_field',
                'nedarimpay_payment_mode_id',
            ];

            foreach ($fields as $field) {
                $value = $this->input->post($field);
                if ($value !== false && $value !== null) {
                    update_option($field, $value);
                }
            }

            set_alert('success', _l('updated_successfully', _l('settings')));
            redirect(admin_url('nedarimpay/settings'));
            return;
        }

        $data['payment_modes'] = $this->db
            ->get(db_prefix() . 'payment_modes')->result_array();

        $data['webhook_url'] = site_url('nedarimpay/webhook');
        $data['title']       = _l('nedarimpay_settings');
        $this->load->view('nedarimpay/settings', $data);
    }

    // =========================================================================
    // RECORD INVOICE PAYMENT (AJAX — from invoice preview button)
    // =========================================================================

    /**
     * POST /nedarimpay/record_invoice_payment
     *
     * Records an admin-initiated Nedarim payment against a Perfex invoice.
     * Responds with JSON {success, message}.
     */
    public function record_invoice_payment()
    {
        if (!has_permission('payments', '', 'create')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            die();
        }

        // Config guard
        $mosad         = get_option('nedarimpay_mosad_number');
        $api_valid     = get_option('nedarimpay_api_valid');
        $payment_mode  = (int)get_option('nedarimpay_payment_mode_id');

        if (empty($mosad) || empty($api_valid) || !$payment_mode) {
            echo json_encode(['success' => false, 'message' => _l('nedarimpay_not_configured_title')]);
            die();
        }

        $invoice_id     = (int)$this->input->post('invoice_id');
        $amount         = (float)$this->input->post('amount');
        $date           = $this->input->post('date', true);
        $receipt_type   = $this->input->post('receipt_type', true) ?: NEDARIMPAY_TYPE_STUDENT;
        $transaction_id = $this->input->post('transaction_id', true);
        $note           = $this->input->post('note', true);

        if (!$invoice_id || $amount <= 0 || empty($date)) {
            echo json_encode(['success' => false, 'message' => _l('nedarimpay_charge_validation_error')]);
            die();
        }

        $this->load->model('payments_model');

        // Generate receipt number before recording payment
        $receipt_number = $this->nedarim_receipt->generate_receipt_number($receipt_type);

        $payment_data = [
            'invoiceid'   => $invoice_id,
            'amount'      => $amount,
            'paymentmode' => $payment_mode,
            'date'        => to_sql_date($date),
            'transactionid' => $transaction_id,
            'note'        => $note,
        ];

        $payment_id = $this->payments_model->add($payment_data);

        if (!$payment_id) {
            echo json_encode(['success' => false, 'message' => _l('nedarimpay_record_payment_fail')]);
            die();
        }

        // Resolve client from the invoice
        $invoice_row = $this->db->select('clientid')->where('id', $invoice_id)
                        ->get(db_prefix() . 'invoices')->row();
        $client_id   = $invoice_row ? (int)$invoice_row->clientid : 0;

        // Unique manual transaction ID
        $manual_tx_id = 'MANUAL-' . $invoice_id . '-' . time();
        if (!empty($transaction_id)) {
            // Prefix to avoid collision with real Nedarim IDs
            $manual_tx_id = 'M-' . $transaction_id;
        }

        $this->db->insert(db_prefix() . 'nedarimpay_transactions', [
            'transaction_id'    => $manual_tx_id,
            'perfex_client_id'  => $client_id,
            'perfex_invoice_id' => $invoice_id,
            'perfex_payment_id' => $payment_id,
            'receipt_type'      => $receipt_type,
            'receipt_number'    => $receipt_number,
            'amount'            => $amount,
            'comments'          => $note,
            'status'            => 'processed',
            'makor'             => 'manual_admin',
            'raw_payload'       => json_encode($payment_data),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['success' => true, 'message' => _l('nedarimpay_record_payment_success')]);
        die();
    }

    // =========================================================================
    // RESEND RECEIPT EMAIL
    // =========================================================================

    public function resend_email($transaction_row_id)
    {
        if (!has_permission('nedarimpay', '', 'edit')) {
            access_denied('NedarimPay');
        }

        $tx = $this->db->where('id', (int)$transaction_row_id)
                ->get(db_prefix() . 'nedarimpay_transactions')->row_array();

        if (!$tx) {
            set_alert('danger', _l('nedarimpay_transaction_not_found'));
            redirect(admin_url('nedarimpay/transactions'));
            return;
        }

        $payload      = json_decode($tx['raw_payload'], true) ?? [];
        $receipt_type = $tx['receipt_type'];

        $result = $this->nedarim_receipt->_send_receipt_email_public(
            $payload, $tx['perfex_client_id'], $tx['perfex_invoice_id'],
            $receipt_type, $tx['receipt_number']
        );

        if ($result) {
            $this->db->where('id', $transaction_row_id)->update(
                db_prefix() . 'nedarimpay_transactions',
                ['email_sent' => 1, 'email_sent_at' => date('Y-m-d H:i:s')]
            );
            set_alert('success', _l('nedarimpay_email_resent'));
        } else {
            set_alert('danger', _l('nedarimpay_email_resend_failed'));
        }

        redirect(admin_url('nedarimpay/transaction_detail/' . $transaction_row_id));
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function _log_webhook($raw_body, $ip): int
    {
        $this->db->insert(db_prefix() . 'nedarimpay_webhook_log', [
            'received_at' => date('Y-m-d H:i:s'),
            'ip'          => $ip,
            'payload'     => $raw_body,
        ]);
        return $this->db->insert_id();
    }

    private function _update_webhook_log(int $log_id, string $result, string $notes): void
    {
        $payload = json_decode($this->db->where('id', $log_id)
                    ->get(db_prefix() . 'nedarimpay_webhook_log')->row()->payload ?? '{}', true);
        $type = isset($payload['TransactionId']) ? 'transaction' : (isset($payload['KevaId']) ? 'keva' : 'unknown');

        $this->db->where('id', $log_id)->update(db_prefix() . 'nedarimpay_webhook_log', [
            'type'   => $type,
            'result' => $result,
            'notes'  => $notes,
        ]);
    }

    private function _json_response(bool $success, string $message, array $extra = []): void
    {
        $response = array_merge(['success' => $success, 'message' => $message], $extra);
        $this->output
            ->set_status_header($success ? 200 : 400)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
