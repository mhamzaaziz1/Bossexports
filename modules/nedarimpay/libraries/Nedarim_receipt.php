<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarim_receipt Library
 *
 * Dual-series receipt engine for NedarimPay.
 *
 * Series A — Students  (prefix T-, e.g. T-00001)
 *   • Monthly tuition
 *   • Occasional fees (trips, materials, etc.)
 *
 * Series B — Donations (prefix D-, e.g. D-00001)
 *   • Donor payments — completely separate numbering + email template
 *
 * Flow per incoming payment:
 *   1. Resolve or create Perfex client
 *   2. Create Perfex Invoice (status=Paid) with correct series prefix
 *   3. Record payment against that invoice
 *   4. Store transaction in nedarimpay_transactions table
 *   5. Send branded receipt email
 */
class Nedarim_receipt
{
    /** @var object */
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('invoices_model');
        $this->CI->load->model('clients_model');
        $this->CI->load->model('payments_model');
        log_message('debug', 'Nedarim_receipt library loaded');
    }

    // =========================================================================
    // PUBLIC ENTRY POINT
    // =========================================================================

    /**
     * Process a fully-parsed Nedarim Plus webhook (transaction type).
     *
     * @param  array  $payload       Parsed webhook data from Nedarim_api::parse_webhook()
     * @param  int    $client_id     Perfex client userid (already resolved)
     * @param  string $receipt_type  NEDARIMPAY_TYPE_STUDENT | NEDARIMPAY_TYPE_DONATION
     * @return array  {success, transaction_row_id, invoice_id, payment_id, receipt_number, error}
     */
    public function process_transaction(array $payload, int $client_id, string $receipt_type): array
    {
        try {
            // ── 1. Generate next receipt number ──────────────────────────────
            $receipt_number = $this->_next_receipt_number($receipt_type);

            // ── 2. Create Perfex Invoice ──────────────────────────────────────
            $invoice_id = $this->_create_invoice($payload, $client_id, $receipt_type, $receipt_number);
            if (!$invoice_id) {
                return $this->_error('Failed to create Perfex invoice');
            }

            // ── 3. Record payment ─────────────────────────────────────────────
            $payment_id = $this->_record_payment($payload, $invoice_id);
            if (!$payment_id) {
                return $this->_error('Failed to record payment for invoice #' . $invoice_id);
            }

            // ── 4. Persist to nedarimpay_transactions ─────────────────────────
            $row_id = $this->_insert_transaction_row(
                $payload, $client_id, $invoice_id, $payment_id, $receipt_type, $receipt_number
            );

            // ── 5. Send receipt email ─────────────────────────────────────────
            $email_sent = false;
            $email      = trim($payload['Mail'] ?? '');
            if (!empty($email)) {
                $email_sent = $this->_send_receipt_email(
                    $payload, $client_id, $invoice_id, $receipt_type, $receipt_number
                );
                // Update email status
                $this->CI->db->where('id', $row_id)->update(
                    db_prefix() . 'nedarimpay_transactions',
                    ['email_sent' => $email_sent ? 1 : 0, 'email_sent_at' => date('Y-m-d H:i:s')]
                );
            }

            // ── 6. Mark transaction as processed ─────────────────────────────
            $this->CI->db->where('id', $row_id)->update(
                db_prefix() . 'nedarimpay_transactions',
                ['status' => 'processed', 'perfex_invoice_id' => $invoice_id, 'perfex_payment_id' => $payment_id]
            );

            return [
                'success'        => true,
                'transaction_id' => $row_id,
                'invoice_id'     => $invoice_id,
                'payment_id'     => $payment_id,
                'receipt_number' => $receipt_number,
                'email_sent'     => $email_sent,
                'error'          => null,
            ];

        } catch (Exception $e) {
            log_message('error', 'Nedarim_receipt::process_transaction exception: ' . $e->getMessage());
            return $this->_error($e->getMessage());
        }
    }

    /**
     * Process a standing-order (HK) setup notification.
     *
     * @param  array  $payload
     * @param  int    $client_id
     * @param  string $receipt_type
     * @return array
     */
    public function process_keva(array $payload, int $client_id, string $receipt_type): array
    {
        $data = [
            'keva_id'           => $payload['KevaId']       ?? '',
            'client_id_nedarim' => $payload['ClientId']     ?? '',
            'perfex_client_id'  => $client_id,
            'zeout'             => $payload['Zeout']        ?? '',
            'client_name'       => $payload['ClientName']   ?? '',
            'email'             => $payload['Mail']         ?? '',
            'phone'             => $payload['Phone']        ?? '',
            'address'           => $payload['Adresse']      ?? '',
            'amount'            => $payload['Amount']       ?? 0,
            'currency'          => $payload['Currency']     ?? 1,
            'next_date'         => $payload['NextDate']     ?? null,
            'last_num'          => $payload['LastNum']      ?? '',
            'tokef'             => $payload['Tokef']        ?? '',
            'groupe'            => $payload['Groupe']       ?? '',
            'comments'          => $payload['Comments']     ?? '',
            'tashloumim'        => $payload['Tashloumim']   ?? null,
            'masof_id'          => $payload['MasofId']      ?? '',
            'debit_iframe'      => $payload['DebitIframe']  ?? 0,
            'receipt_type'      => $receipt_type,
            'raw_payload'       => json_encode($payload),
        ];

        $this->CI->db->insert(db_prefix() . 'nedarimpay_keva', $data);
        $row_id = $this->CI->db->insert_id();

        return [
            'success' => (bool)$row_id,
            'row_id'  => $row_id,
            'error'   => $row_id ? null : 'DB insert failed for keva',
        ];
    }

    // =========================================================================
    // PRIVATE — RECEIPT NUMBER GENERATION (Thread-safe via DB lock)
    // =========================================================================

    /**
     * Atomically generate the next receipt number for a given series.
     *
     * Uses DB-level increment to prevent race conditions under concurrent
     * webhook delivery.
     *
     * @param  string $receipt_type
     * @return string  e.g. "T-00042" or "D-00007"
     */
    private function _next_receipt_number(string $receipt_type): string
    {
        if ($receipt_type === NEDARIMPAY_TYPE_DONATION) {
            $prefix_key = 'nedarimpay_donation_prefix';
            $num_key    = 'nedarimpay_donation_next_number';
        } else {
            $prefix_key = 'nedarimpay_student_prefix';
            $num_key    = 'nedarimpay_student_next_number';
        }

        // Atomic increment — read then immediately update
        $this->CI->db->query(
            "UPDATE `" . db_prefix() . "options` SET `value` = LAST_INSERT_ID(`value` + 1) WHERE `name` = ?",
            [$num_key]
        );
        $next_num = $this->CI->db->query('SELECT LAST_INSERT_ID() as n')->row()->n;

        // Fallback: if LAST_INSERT_ID trick didn't work (shouldn't happen)
        if (!$next_num) {
            $next_num = (int) get_option($num_key);
            update_option($num_key, $next_num + 1);
        }

        $prefix = get_option($prefix_key) ?: ($receipt_type === NEDARIMPAY_TYPE_DONATION ? 'D' : 'T');
        return $prefix . '-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // PRIVATE — INVOICE CREATION
    // =========================================================================

    private function _create_invoice(array $payload, int $client_id, string $receipt_type, string $receipt_number): int
    {
        $amount    = (float)($payload['Amount'] ?? 0);
        $currency  = (int)($payload['Currency'] ?? 1);
        $date      = date('Y-m-d', strtotime($payload['TransactionTime'] ?? 'now'));

        // Build description
        $description = $this->_build_line_description($payload, $receipt_type);

        $invoice_data = [
            'clientid'          => $client_id,
            'date'              => $date,
            'duedate'           => $date,
            'number'            => $this->_extract_number($receipt_number),
            'prefix'            => $this->_extract_prefix($receipt_number),
            'currency'          => $currency === 2 ? $this->_get_usd_currency_id() : $this->_get_ils_currency_id(),
            'status'            => 2, // STATUS_PAID
            'subtotal'          => $amount,
            'total'             => $amount,
            'discount_percent'  => 0,
            'discount_total'    => 0,
            'discount_type'     => '',
            'adjustment'        => 0,
            'addedfrom'         => 0,
            'hash'              => app_generate_hash(),
            'datecreated'       => date('Y-m-d H:i:s'),
            'adminnote'         => 'Auto-created by NedarimPay | TxID: ' . ($payload['TransactionId'] ?? ''),
        ];

        $this->CI->db->insert(db_prefix() . 'invoices', $invoice_data);
        $invoice_id = $this->CI->db->insert_id();

        if (!$invoice_id) {
            return 0;
        }

        // Insert invoice item line
        $this->CI->db->insert(db_prefix() . 'itemable', [
            'rel_id'      => $invoice_id,
            'rel_type'    => 'invoice',
            'description' => $description,
            'long_description' => $payload['Comments'] ?? '',
            'qty'         => 1,
            'rate'        => $amount,
            'unit'        => '',
            'item_order'  => 1,
            'taxname'     => null,
            'taxrate'     => 0,
            'taxid'       => null,
            'taxrate_2'   => 0,
            'taxname_2'   => null,
            'taxid_2'     => null,
        ]);

        return $invoice_id;
    }

    // =========================================================================
    // PRIVATE — PAYMENT RECORDING
    // =========================================================================

    private function _record_payment(array $payload, int $invoice_id): int
    {
        $amount    = (float)($payload['Amount'] ?? 0);
        $mode_id   = get_option('nedarimpay_payment_mode_id');
        $date      = date('Y-m-d', strtotime($payload['TransactionTime'] ?? 'now'));

        // Fallback to first available payment mode if not configured
        if (empty($mode_id)) {
            $mode = $this->CI->db->order_by('id', 'asc')->limit(1)
                        ->get(db_prefix() . 'payment_modes')->row();
            $mode_id = $mode ? $mode->id : 1;
        }

        $note = implode(' | ', array_filter([
            'NedarimPay',
            $payload['TransactionId'] ?? '',
            $payload['LastNum']       ? '****' . $payload['LastNum'] : '',
            $payload['Confirmation']  ?? '',
        ]));

        $payment_data = [
            'invoiceid'    => $invoice_id,
            'amount'       => $amount,
            'paymentmode'  => $mode_id,
            'date'         => $date,
            'daterecorded' => date('Y-m-d H:i:s'),
            'note'         => $note,
        ];

        $this->CI->db->insert(db_prefix() . 'invoicepaymentrecords', $payment_data);
        return $this->CI->db->insert_id();
    }

    // =========================================================================
    // PRIVATE — PERSIST TRANSACTION ROW
    // =========================================================================

    private function _insert_transaction_row(
        array $payload, int $client_id, int $invoice_id, int $payment_id,
        string $receipt_type, string $receipt_number
    ): int {
        $data = [
            'transaction_id'      => $payload['TransactionId']    ?? '',
            'keva_id'             => $payload['KevaId']           ?? null,
            'client_id_nedarim'   => $payload['ClientId']         ?? null,
            'perfex_client_id'    => $client_id,
            'perfex_invoice_id'   => $invoice_id,
            'perfex_payment_id'   => $payment_id,
            'receipt_type'        => $receipt_type,
            'receipt_number'      => $receipt_number,
            'zeout'               => $payload['Zeout']            ?? '',
            'client_name'         => $payload['ClientName']       ?? '',
            'email'               => $payload['Mail']             ?? '',
            'phone'               => $payload['Phone']            ?? '',
            'address'             => $payload['Adresse']          ?? '',
            'amount'              => $payload['Amount']           ?? 0,
            'currency'            => $payload['Currency']         ?? 1,
            'transaction_type'    => $payload['TransactionType']  ?? '',
            'groupe'              => $payload['Groupe']           ?? '',
            'comments'            => $payload['Comments']         ?? '',
            'tashloumim'          => $payload['Tashloumim']       ?? null,
            'first_tashloum'      => $payload['FirstTashloum']    ?? null,
            'last_num'            => $payload['LastNum']          ?? '',
            'tokef'               => $payload['Tokef']            ?? '',
            'confirmation'        => $payload['Confirmation']     ?? '',
            'shovar'              => $payload['Shovar']           ?? '',
            'makor'               => $payload['Makor']            ?? '',
            'masof_id'            => $payload['MasofId']          ?? '',
            'debit_iframe'        => $payload['DebitIframe']      ?? 0,
            'transaction_time'    => !empty($payload['TransactionTime'])
                                        ? date('Y-m-d H:i:s', strtotime($payload['TransactionTime']))
                                        : date('Y-m-d H:i:s'),
            'raw_payload'         => json_encode($payload),
            'status'              => 'pending',
        ];

        $this->CI->db->insert(db_prefix() . 'nedarimpay_transactions', $data);
        return $this->CI->db->insert_id();
    }

    // =========================================================================
    // PRIVATE — EMAIL
    // =========================================================================

    private function _send_receipt_email(
        array $payload, int $client_id, int $invoice_id,
        string $receipt_type, string $receipt_number
    ): bool {
        $email = trim($payload['Mail'] ?? '');
        if (empty($email)) {
            return false;
        }

        if ($receipt_type === NEDARIMPAY_TYPE_DONATION) {
            $subject_tpl = get_option('nedarimpay_donation_email_subject');
            $body_tpl    = get_option('nedarimpay_donation_email_body');
        } else {
            $subject_tpl = get_option('nedarimpay_student_email_subject');
            $body_tpl    = get_option('nedarimpay_student_email_body');
        }

        $replacements = [
            '{client_name}'    => $payload['ClientName']  ?? '',
            '{amount}'         => number_format((float)($payload['Amount'] ?? 0), 2) . ' ' . ($payload['Currency'] == 2 ? 'USD' : 'ILS'),
            '{receipt_number}' => $receipt_number,
            '{date}'           => date('d/m/Y', strtotime($payload['TransactionTime'] ?? 'now')),
            '{transaction_id}' => $payload['TransactionId'] ?? '',
            '{invoice_number}' => $receipt_number,
        ];

        $subject = str_replace(array_keys($replacements), array_values($replacements), $subject_tpl);
        $body    = str_replace(array_keys($replacements), array_values($replacements), $body_tpl);

        // Use Perfex's built-in email sending
        $this->CI->load->library('email_template');

        try {
            // Build the receipt PDF link
            $invoice_hash = $this->CI->db->select('hash')->where('id', $invoice_id)
                                ->get(db_prefix() . 'invoices')->row();
            $pdf_url = site_url('invoice/' . $invoice_id . '/' . ($invoice_hash->hash ?? '') . '/pdf');

            $this->CI->load->library('app_mail');

            $mail = app_mail_instance()
                ->add_recipient($email)
                ->set_from(get_option('smtp_email'), get_option('companyname'))
                ->set_subject($subject)
                ->set_message($body . '<br><br><a href="' . $pdf_url . '">' . _l('nedarimpay_view_receipt') . '</a>');

            // Attach PDF if possible
            if (method_exists($mail, 'attach_invoice_pdf')) {
                $mail->attach_invoice_pdf($invoice_id);
            }

            return $mail->send();
        } catch (Exception $e) {
            log_message('error', 'NedarimPay email send failed: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // PRIVATE — HELPERS
    // =========================================================================

    private function _build_line_description(array $payload, string $receipt_type): string
    {
        $type_label  = $receipt_type === NEDARIMPAY_TYPE_DONATION
            ? _l('nedarimpay_donation_payment')
            : _l('nedarimpay_student_payment');

        $groupe  = $payload['Groupe']   ?? '';
        $comment = $payload['Comments'] ?? '';

        $parts = [$type_label];
        if (!empty($groupe))  { $parts[] = $groupe; }
        if (!empty($comment)) { $parts[] = $comment; }

        return implode(' — ', $parts);
    }

    private function _extract_number(string $receipt_number): int
    {
        // "T-00042" → 42
        $parts = explode('-', $receipt_number);
        return (int)ltrim(end($parts), '0') ?: 1;
    }

    private function _extract_prefix(string $receipt_number): string
    {
        // "T-00042" → "T-"
        $parts = explode('-', $receipt_number);
        return count($parts) > 1 ? $parts[0] . '-' : '';
    }

    private function _get_ils_currency_id(): int
    {
        $row = $this->CI->db->where('symbol', '₪')->orwhere('name', 'Israeli New Shekel')
                   ->orwhere('iso_code', 'ILS')->limit(1)
                   ->get(db_prefix() . 'currencies')->row();
        return $row ? (int)$row->id : 1;
    }

    private function _get_usd_currency_id(): int
    {
        $row = $this->CI->db->where('iso_code', 'USD')->limit(1)
                   ->get(db_prefix() . 'currencies')->row();
        return $row ? (int)$row->id : 2;
    }

    /**
     * Public wrapper — generate the next receipt number for a given type.
     * Exposes the private _next_receipt_number() for use from controllers.
     */
    public function generate_receipt_number(string $receipt_type): string
    {
        return $this->_next_receipt_number($receipt_type);
    }

    /**
     * Public wrapper for resend_email action in controller.
     */
    public function _send_receipt_email_public(
        array $payload, int $client_id, int $invoice_id,
        string $receipt_type, string $receipt_number
    ): bool {
        return $this->_send_receipt_email($payload, $client_id, $invoice_id, $receipt_type, $receipt_number);
    }

    private function _error(string $msg): array
    {
        log_message('error', 'Nedarim_receipt: ' . $msg);
        return [
            'success'        => false,
            'transaction_id' => null,
            'invoice_id'     => null,
            'payment_id'     => null,
            'receipt_number' => null,
            'email_sent'     => false,
            'error'          => $msg,
        ];
    }
}
