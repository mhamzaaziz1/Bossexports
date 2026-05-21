<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Nedarim Plus Gateway Controller (PUBLIC — no admin auth required)
 *
 * Lives inside the module and is reached via HMVC routing:
 *
 *   GET /nedarimpay/gateway/pay     — render the Nedarim Plus iFrame checkout
 *   GET /nedarimpay/gateway/verify  — return URL after the iFrame payment
 *
 * NOTE: This controller intentionally does NOT extend AdminController, so
 * customers can reach it without a staff session. The invoice + hash
 * combination is the security guard (see check_invoice_restrictions).
 *
 * This is the ONLY file the user needs to drop in to expose the
 * `gateways/nedarimpay/pay`-equivalent route. Nothing in application/
 * needs editing — installing the module is enough.
 */
class Gateway extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Module bootstrap should already have loaded the language file via
        // register_language_files(), but reload defensively in case the
        // controller is hit before the bootstrap fires (e.g. a stale opcache).
        $this->lang->load('nedarimpay/nedarimpay');

        // Models we always need
        $this->load->model('invoices_model');
    }

    // =========================================================================
    // PAY — Render the Nedarim Plus iFrame
    // =========================================================================

    public function pay()
    {
        $invoiceid = (int) $this->input->get('invoiceid');
        $hash      = (string) $this->input->get('hash');

        // Friendly landing page when the route is opened without an invoice
        // context (e.g. while smoke-testing or after the user pastes the URL
        // from somewhere). We return 200 OK so it's visibly different from
        // a real 404.
        if (!$invoiceid || !$hash) {
            $this->_render_endpoint_info();
            return;
        }

        check_invoice_restrictions($invoiceid, $hash);

        $invoice = $this->invoices_model->get($invoiceid);
        if (!$invoice) {
            show_404();
        }
        load_client_language($invoice->clientid);

        $amount_due = get_invoice_total_left_to_pay($invoiceid, $invoice->total);
        if ($amount_due <= 0) {
            set_alert('success', _l('invoice_already_paid'));
            redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
            return;
        }

        // Allow ?amount=X override (used when paying a partial balance)
        $override = $this->input->get('amount');
        if ($override !== null && is_numeric($override) && (float) $override > 0
            && (float) $override <= (float) $amount_due) {
            $amount_due = (float) $override;
        }

        // ── Read credentials ──────────────────────────────────────────────────
        // Prefer the per-gateway encrypted settings, fall back to module-level
        // options so a fresh install with only module settings still works.
        $mosad     = trim((string) (get_option('paymentmethod_nedarimpay_mosad_number')
                       ?: get_option('nedarimpay_mosad_number')));
        $api_valid = trim((string) (get_option('paymentmethod_nedarimpay_api_valid')
                       ?: get_option('nedarimpay_api_valid')));

        // The gateway library marks api_valid as encrypted — decrypt if needed
        if (!empty(get_option('paymentmethod_nedarimpay_api_valid'))) {
            try {
                $decrypted = $this->encryption->decrypt(get_option('paymentmethod_nedarimpay_api_valid'));
                if (!empty($decrypted)) {
                    $api_valid = trim($decrypted);
                }
            } catch (Exception $e) {
                // Already plaintext — keep current value
            }
        }

        if ($mosad === '' || $api_valid === '') {
            show_error(
                _l('nedarimpay_not_configured'),
                500,
                'Nedarim Plus configuration missing'
            );
            return;
        }

        $iframe_base = rtrim(
            (string) (get_option('paymentmethod_nedarimpay_iframe_url')
                ?: 'https://www.matara.pro/nedarimplus/online/'),
            '/'
        ) . '/';

        // Currency mapping (Nedarim: 1 = ILS, 2 = USD)
        $currency_name    = strtoupper((string) ($invoice->currency_name ?? 'ILS'));
        $nedarim_currency = ($currency_name === 'USD') ? 2 : 1;

        $good_url  = site_url('nedarimpay/gateway/verify')
                   . '?invoiceid=' . $invoiceid . '&hash=' . $hash . '&status=success';
        $error_url = site_url('nedarimpay/gateway/verify')
                   . '?invoiceid=' . $invoiceid . '&hash=' . $hash . '&status=error';

        $description = 'Payment for Invoice ' . format_invoice_number($invoiceid);
        $custom_desc = get_option('paymentmethod_nedarimpay_description');
        if (!empty($custom_desc)) {
            $description = str_replace(
                '{invoice_number}',
                format_invoice_number($invoiceid),
                $custom_desc
            );
        }

        $iframe_params = http_build_query([
            'Mosad'       => $mosad,
            'ApiValid'    => $api_valid,
            'PaymentType' => 'Ragil',
            'Amount'      => number_format((float) $amount_due, 2, '.', ''),
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

        $this->_render_module_view('gateway/pay', $data);
    }

    // =========================================================================
    // VERIFY — Return URL after the Nedarim Plus iFrame redirects back
    // =========================================================================

    public function verify()
    {
        $invoiceid = (int) $this->input->get('invoiceid');
        $hash      = (string) $this->input->get('hash');
        $status    = (string) $this->input->get('status');

        check_invoice_restrictions($invoiceid, $hash);

        if ($status === 'success') {
            // The webhook lands asynchronously — poll briefly so the customer
            // sees the paid state immediately when possible.
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

            set_alert(
                $paid ? 'success' : 'info',
                _l($paid ? 'online_payment_recorded_success' : 'nedarimpay_payment_processing')
            );
        } else {
            set_alert('danger', _l('nedarimpay_payment_failed_or_cancelled'));
        }

        redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Load a view that physically lives inside this module folder, without
     * depending on a global view-paths filter. Self-contained.
     */
    private function _render_module_view($view, $data)
    {
        if (defined('NEDARIMPAY_MODULE_PATH')) {
            $this->load->add_package_path(NEDARIMPAY_MODULE_PATH . '/');
            $this->load->view($view, $data);
            $this->load->remove_package_path(NEDARIMPAY_MODULE_PATH . '/');
            return;
        }

        // Fallback when the module bootstrap hasn't run (extremely rare):
        // include the view file directly. We're inside the module, so paths
        // are deterministic.
        $abs = __DIR__ . '/../views/' . ltrim($view, '/') . '.php';
        if (file_exists($abs)) {
            extract($data, EXTR_SKIP);
            include $abs;
            return;
        }

        show_error('Nedarim Plus view not found: ' . $view, 500);
    }

    /**
     * Standalone landing page that confirms the route is wired up when the
     * URL is hit without invoiceid/hash. Important: keeps the 404 from
     * masking a misinstalled module.
     */
    private function _render_endpoint_info()
    {
        $this->output
             ->set_status_header(200)
             ->set_content_type('text/html; charset=utf-8');

        $module_example = site_url('nedarimpay/gateway/pay')
                        . '?invoiceid=<INVOICE_ID>&hash=<INVOICE_HASH>';

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
           . '<title>Nedarim Plus Payment</title>'
           . '<meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<style>'
           . 'body{font-family:-apple-system,Segoe UI,Roboto,sans-serif;'
           . 'background:#f4f6f9;color:#333;margin:0;padding:40px 16px;}'
           . '.card{max-width:640px;margin:0 auto;background:#fff;padding:32px;'
           . 'border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.08);}'
           . 'h1{margin:0 0 8px;font-size:22px;color:#1a73e8;}'
           . 'p{line-height:1.55;}'
           . 'code{background:#f1f3f6;padding:2px 6px;border-radius:4px;'
           . 'font-size:13px;word-break:break-all;display:inline-block;}'
           . '.ok{display:inline-block;background:#e7f5ee;color:#1f7a3a;'
           . 'padding:4px 10px;border-radius:12px;font-size:12px;'
           . 'font-weight:600;margin-bottom:12px;}'
           . '.muted{color:#888;font-size:13px;margin-top:18px;}'
           . '</style></head><body><div class="card">'
           . '<span class="ok">Endpoint OK · module-served</span>'
           . '<h1>Nedarim Plus Payment Gateway</h1>'
           . '<p>This route is reachable and served entirely from the '
           . '<code>nedarimpay</code> module. No core Perfex file is required.</p>'
           . '<p>To start a payment, append the invoice context:</p>'
           . '<p><code>' . htmlspecialchars($module_example, ENT_QUOTES) . '</code></p>'
           . '<p class="muted">Required: <code>invoiceid</code>, <code>hash</code>. '
           . 'Optional: <code>amount</code> (partial payment).</p>'
           . '</div></body></html>';
    }
}
