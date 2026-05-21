<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarimpay Gateway Controller
 *
 * Public-facing controller (no admin auth required).
 * Handles the client payment flow via Nedarim Plus iFrame:
 *
 *  GET  /gateways/nedarimpay/pay      — render iFrame payment page
 *  GET  /gateways/nedarimpay/verify   — client return URL after iFrame payment
 *
 * The actual payment recording happens via the existing NedarimPay webhook
 * at /nedarimpay/webhook — Nedarim Plus calls that server-to-server.
 * This controller only handles the browser-side redirect flow.
 */
class Nedarimpay extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('payments_model');
        $this->load->model('clients_model');
    }

    // =========================================================================
    // PAY — Render the Nedarim Plus iFrame
    // =========================================================================

    public function pay()
    {
        $invoiceid = (int) $this->input->get('invoiceid');
        $hash      = $this->input->get('hash');

        check_invoice_restrictions($invoiceid, $hash);

        $invoice = $this->db->where('id', $invoiceid)
                            ->get(db_prefix() . 'invoices')->row();

        if (!$invoice) {
            show_404();
        }

        $amount_due = get_invoice_total_left_to_pay($invoiceid, $invoice->total);
        if ($amount_due <= 0) {
            set_alert('success', _l('invoice_already_paid'));
            redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
            return;
        }

        // Read credentials — gateway-specific option first, then module option
        $mosad     = trim(get_option('paymentmethod_nedarimpay_mosad_number')
                     ?: get_option('nedarimpay_mosad_number'));
        $api_valid = trim(get_option('paymentmethod_nedarimpay_api_valid')
                     ?: get_option('nedarimpay_api_valid'));

        // Decrypt gateway-stored api_valid if it was saved encrypted
        if (!empty(get_option('paymentmethod_nedarimpay_api_valid'))) {
            try {
                $decrypted = $this->encryption->decrypt(get_option('paymentmethod_nedarimpay_api_valid'));
                if (!empty($decrypted)) {
                    $api_valid = trim($decrypted);
                }
            } catch (Exception $e) {
                // Not encrypted — use raw value already set above
            }
        }

        $iframe_base = rtrim(
            get_option('paymentmethod_nedarimpay_iframe_url')
            ?: 'https://www.matara.pro/nedarimplus/online/',
            '/'
        ) . '/';

        // Currency: Perfex currency name → Nedarim code (1=ILS, 2=USD)
        $currency_name    = strtoupper($invoice->currency_name ?? 'ILS');
        $nedarim_currency = ($currency_name === 'USD') ? 2 : 1;

        $good_url  = site_url('gateways/nedarimpay/verify?invoiceid=' . $invoiceid . '&hash=' . $hash . '&status=success');
        $error_url = site_url('gateways/nedarimpay/verify?invoiceid=' . $invoiceid . '&hash=' . $hash . '&status=error');

        $description = 'Payment for Invoice ' . format_invoice_number($invoiceid);
        $custom_desc = get_option('paymentmethod_nedarimpay_description');
        if (!empty($custom_desc)) {
            $description = str_replace('{invoice_number}', format_invoice_number($invoiceid), $custom_desc);
        }

        $iframe_params = http_build_query([
            'Mosad'       => $mosad,
            'ApiValid'    => $api_valid,
            'PaymentType' => 'Ragil',
            'Amount'      => number_format($amount_due, 2, '.', ''),
            'Currency'    => $nedarim_currency,
            'Comment'     => $description,
            'GoodUrl'     => $good_url,
            'ErrorUrl'    => $error_url,
        ]);

        $data = [
            'invoice'      => $invoice,
            'invoiceid'    => $invoiceid,
            'hash'         => $hash,
            'amount_due'   => $amount_due,
            'currency_sym' => $nedarim_currency == 2 ? '$' : '₪',
            'iframe_src'   => $iframe_base . '?' . $iframe_params,
            'good_url'     => $good_url,
            'error_url'    => $error_url,
            'title'        => _l('nedarimpay_pay_invoice', format_invoice_number($invoiceid)),
        ];

        // Add module view path so CI can locate the view in modules/nedarimpay/views/
        $this->load->add_package_path(NEDARIMPAY_MODULE_PATH . '/');
        $this->load->view('gateway/pay', $data);
        $this->load->remove_package_path(NEDARIMPAY_MODULE_PATH . '/');
    }

    // =========================================================================
    // VERIFY — Client return URL after iFrame payment
    // =========================================================================

    public function verify()
    {
        $invoiceid = (int) $this->input->get('invoiceid');
        $hash      = $this->input->get('hash');
        $status    = $this->input->get('status');

        check_invoice_restrictions($invoiceid, $hash);

        if ($status === 'success') {
            $paid = false;
            for ($i = 0; $i < 5; $i++) {
                $inv = $this->db->select('status')
                               ->where('id', $invoiceid)
                               ->get(db_prefix() . 'invoices')->row();
                if ($inv && (int) $inv->status === 2) {
                    $paid = true;
                    break;
                }
                sleep(1);
            }

            if ($paid) {
                set_alert('success', _l('online_payment_recorded_success'));
            } else {
                set_alert('info', _l('nedarimpay_payment_processing'));
            }
        } else {
            set_alert('danger', _l('nedarimpay_payment_failed_or_cancelled'));
        }

        redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
    }
}
