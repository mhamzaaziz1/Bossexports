<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarimpay_gateway
 *
 * Registers NedarimPay as a first-class Perfex payment gateway so that
 * clients can pay invoices directly through the Nedarim Plus iFrame.
 *
 * Flow:
 *  1. Client opens an invoice and clicks "Pay Now → NedarimPay"
 *  2. process_payment() redirects them to nedarimpay/gateway/pay?invoiceid=X&hash=Y
 *     (HMVC route — fully owned by this module, no core controller involved)
 *  3. The Gateway module controller renders the Nedarim Plus iFrame
 *  4. After payment Nedarim calls our webhook (/nedarimpay/webhook) — the
 *     existing handler records the transaction, creates the receipt, sends the email
 *  5. Nedarim also redirects the client browser to our return URL
 *  6. nedarimpay/gateway/verify marks the invoice paid and shows the confirmation page
 *
 * No card data ever touches our server — Nedarim Plus handles PCI compliance.
 */
class Nedarimpay_gateway extends App_gateway
{
    public function __construct()
    {
        parent::__construct();

        // ── Required: unique gateway ID (alphanumeric, no spaces) ────────────
        $this->setId('nedarimpay');

        // ── Required: display name shown on invoice payment screen ───────────
        $this->setName('NedarimPay (נדרים פלוס)');

        // ── Settings that appear in Setup → Payment Gateways ─────────────────
        $this->setSettings([
            [
                'name'          => 'mosad_number',
                'label'         => 'nedarimpay_gw_mosad_number',
                'default_value' => '',
            ],
            [
                'name'          => 'api_valid',
                'label'         => 'nedarimpay_gw_api_valid',
                'default_value' => '',
                'encrypted'     => true,
            ],
            [
                'name'          => 'iframe_url',
                'label'         => 'nedarimpay_gw_iframe_url',
                'default_value' => 'https://www.matara.pro/nedarimplus/online/',
            ],
            [
                'name'          => 'currencies',
                'label'         => 'settings_paymentmethod_currencies',
                'default_value' => 'ILS,USD',
            ],
            [
                'name'          => 'description',
                'label'         => 'settings_paymentmethod_description',
                'type'          => 'textarea',
                'default_value' => 'Payment for Invoice {invoice_number}',
            ],
            [
                'name'          => 'receipt_type',
                'label'         => 'nedarimpay_gw_default_receipt_type',
                'type'          => 'select',
                'default_value' => 'student',
                'options'       => [
                    'student'  => 'nedarimpay_type_student',
                    'donation' => 'nedarimpay_type_donation',
                ],
            ],
        ]);
    }

    /**
     * Called by Perfex when a client selects NedarimPay and clicks "Pay".
     *
     * We redirect to our own MODULE controller (HMVC routing) which renders
     * the iFrame. No core controller is involved — the route
     * `nedarimpay/gateway/pay` resolves directly to
     * `modules/nedarimpay/controllers/Gateway.php::pay()`.
     *
     * @param  array $data  Contains: invoice, invoiceid, amount, paymentmode, etc.
     */
    public function process_payment($data)
    {
        $return_url = site_url('nedarimpay/gateway/verify')
                    . '?invoiceid=' . $data['invoiceid']
                    . '&hash='      . $data['invoice']->hash;

        $pay_url = site_url('nedarimpay/gateway/pay')
                 . '?invoiceid=' . $data['invoiceid']
                 . '&hash='      . $data['invoice']->hash
                 . '&amount='    . number_format((float) $data['amount'], 2, '.', '')
                 . '&return='    . urlencode($return_url);

        redirect($pay_url);
    }
}
