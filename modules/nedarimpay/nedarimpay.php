<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: NedarimPay
Description: Full integration between Perfex CRM and the Nedarim Plus payment
             gateway. Self-contained: payment iFrame route, webhook callback,
             dual-series receipts (Students / Donations), outbound charges,
             email delivery, and a client invoice "Pay with Nedarim Plus"
             button — all driven from this module. No core file edits needed.
Version: 1.1.0
Requires at least: 2.3.*
Author: Senior Module — NedarimPay
*/

define('NEDARIMPAY_MODULE_NAME', 'nedarimpay');
define('NEDARIMPAY_MODULE_PATH', __DIR__);
define('NEDARIMPAY_MODULE_URL',  module_dir_url(NEDARIMPAY_MODULE_NAME, ''));

// Load gateway class so register_payment_gateway() receives an object instance
require_once NEDARIMPAY_MODULE_PATH . '/libraries/Nedarimpay_gateway.php';

// ─── Receipt Type Constants ──────────────────────────────────────────────────
define('NEDARIMPAY_TYPE_STUDENT',  'student');
define('NEDARIMPAY_TYPE_DONATION', 'donation');

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook(NEDARIMPAY_MODULE_NAME, 'nedarimpay_activation_hook');
register_deactivation_hook(NEDARIMPAY_MODULE_NAME, 'nedarimpay_deactivation_hook');

function nedarimpay_activation_hook()
{
    require_once NEDARIMPAY_MODULE_PATH . '/install.php';

    // Self-heal: if a previous version of this module dropped a controller in
    // application/controllers/gateways/, remove it so the module is once
    // again the only source of truth.
    nedarimpay_purge_stale_core_files();

    log_activity('NedarimPay module activated');
}

function nedarimpay_deactivation_hook()
{
    // Preserve data — no table drops.
    log_activity('NedarimPay module deactivated');
}

/**
 * Best-effort delete of stale Nedarim-related files in core directories.
 * Idempotent: every path is checked before unlinking. Logs each removal.
 */
function nedarimpay_purge_stale_core_files()
{
    $stale_paths = [
        APPPATH . 'controllers/gateways/Nedarimpay.php',
        APPPATH . 'libraries/gateways/Nedarimpay_gateway.php',
    ];

    foreach ($stale_paths as $path) {
        if (file_exists($path) && is_writable($path)) {
            @unlink($path);
            if (!file_exists($path)) {
                log_activity('NedarimPay: removed stale core file ' . $path);
            }
        }
    }
}

// ─── Schema migrations (run on every request — guarded by column check) ──────
$_ndp_CI = &get_instance();
if ($_ndp_CI->db->table_exists(db_prefix() . 'nedarimpay_manual_charges')) {
    $_ndp_cols = array_column(
        $_ndp_CI->db->field_data(db_prefix() . 'nedarimpay_manual_charges'),
        'name'
    );
    if (!in_array('receipt_number', $_ndp_cols)) {
        $_ndp_CI->db->query(
            'ALTER TABLE `' . db_prefix() . 'nedarimpay_manual_charges`
             ADD COLUMN `receipt_number` VARCHAR(50) DEFAULT NULL AFTER `client_id_nedarim`'
        );
    }
}
unset($_ndp_CI, $_ndp_cols);

// ─── Language Files ──────────────────────────────────────────────────────────
register_language_files(NEDARIMPAY_MODULE_NAME, [NEDARIMPAY_MODULE_NAME]);

// ─── Module view path (so module controllers + hook callbacks can render) ───
hooks()->add_filter('app_view_paths', 'nedarimpay_add_view_path');
function nedarimpay_add_view_path($paths)
{
    $paths[] = NEDARIMPAY_MODULE_PATH . '/views/';
    return $paths;
}

// ─── Register Payment Gateway ────────────────────────────────────────────────
register_payment_gateway(new Nedarimpay_gateway(), NEDARIMPAY_MODULE_NAME);

// ─── Hooks: menu, action links, invoice button ───────────────────────────────
hooks()->add_action('admin_init',                       'nedarimpay_init_menu_items');
hooks()->add_filter('module_nedarimpay_action_links',   'nedarimpay_action_links');

// Client-area invoice page: hidden marker + JS that injects the pay button
hooks()->add_action('after_right_panel_invoicehtml',    'nedarimpay_render_invoice_marker');
hooks()->add_action('app_customers_footer',             'nedarimpay_inject_invoice_pay_button');

// Admin-area invoice preview: "Copy Nedarim Payment Link" menu item + JS
hooks()->add_action('after_invoice_view_as_client_link','nedarimpay_render_admin_pay_link');
hooks()->add_action('app_admin_footer',                 'nedarimpay_inject_admin_pay_link_js');

// Mirror every Nedarim-mode payment Perfex records into our transactions log
hooks()->add_action('after_payment_added',              'nedarimpay_mirror_payment_to_transactions');

// =============================================================================
// HOOK: render an invisible marker on the client invoice page
// =============================================================================
/**
 * The customer invoice template (themes/perfex/views/invoicehtml.php) emits
 * `do_action('after_right_panel_invoicehtml', $invoice)`. We piggy-back on
 * that to stamp a small hidden marker tag carrying the invoice id, hash, and
 * remaining balance. Our JS picks those up and renders the pay button.
 *
 * Doing it this way means no template edits and no DOM scraping.
 *
 * @param object $invoice  The invoice object passed by the template hook
 */
function nedarimpay_render_invoice_marker($invoice)
{
    if (!$invoice || empty($invoice->id) || empty($invoice->hash)) {
        return;
    }

    // Only show on unpaid / partially paid invoices
    $balance = function_exists('get_invoice_total_left_to_pay')
        ? get_invoice_total_left_to_pay($invoice->id, $invoice->total)
        : (float) $invoice->total;
    if ($balance <= 0) {
        return;
    }

    // Only show when the gateway is actually active (settings flag is "1")
    if ((string) get_option('paymentmethod_nedarimpay_active') !== '1') {
        return;
    }

    // i18n-aware button label
    $label = function_exists('_l') ? _l('nedarimpay_pay_with_nedarim') : 'Pay with Nedarim Plus';
    if (stripos($label, 'nedarimpay_pay_with_nedarim') !== false) {
        $label = 'Pay with Nedarim Plus'; // language file fallback
    }

    $pay_url = site_url('nedarimpay/gateway/pay');

    echo '<span'
       . ' id="nedarimpay-invoice-marker"'
       . ' style="display:none"'
       . ' data-invoice-id="'   . (int) $invoice->id . '"'
       . ' data-invoice-hash="' . htmlspecialchars($invoice->hash, ENT_QUOTES) . '"'
       . ' data-pay-url="'      . htmlspecialchars($pay_url, ENT_QUOTES) . '"'
       . ' data-balance-due="'  . number_format((float) $balance, 2, '.', '') . '"'
       . ' data-label-pay="'    . htmlspecialchars($label, ENT_QUOTES) . '"'
       . '></span>';
}

// =============================================================================
// HOOK: enqueue the pay-button JS on customer pages (invoice view inherits)
// =============================================================================
/**
 * The customer area emits `do_action('app_customers_footer')` just before
 * </body>. We append our module-served script tag there. The script is a
 * no-op on any page that doesn't have the marker tag — safe to load
 * globally on the customer area.
 */
function nedarimpay_inject_invoice_pay_button()
{
    $script_url = NEDARIMPAY_MODULE_URL . 'assets/js/invoice_pay_button.js'
                . '?v=' . filemtime(NEDARIMPAY_MODULE_PATH . '/assets/js/invoice_pay_button.js');

    echo '<script src="' . htmlspecialchars($script_url, ENT_QUOTES) . '" defer></script>';
}

// =============================================================================
// HOOK: mirror Perfex payments (paymentmode = nedarimpay) into our log
// =============================================================================
/**
 * Fires after Perfex inserts a row into tblinvoicepaymentrecords. If the
 * payment was recorded via this gateway (paymentmode = "nedarimpay") and we
 * don't already have a transactions row for it (from the webhook), insert a
 * minimal row so the staff sees it in /admin/nedarimpay/transactions.
 *
 * Idempotent — keyed by (perfex_payment_id, transaction_id) so the webhook
 * handler and this callback never produce duplicates.
 *
 * @param int $payment_id  The newly-inserted tblinvoicepaymentrecords.id
 */
function nedarimpay_mirror_payment_to_transactions($payment_id)
{
    $CI       = &get_instance();
    $payment  = $CI->db->where('id', (int) $payment_id)
                       ->get(db_prefix() . 'invoicepaymentrecords')->row();
    if (!$payment || strtolower((string) $payment->paymentmode) !== 'nedarimpay') {
        return; // not ours
    }

    $tx_table = db_prefix() . 'nedarimpay_transactions';
    if (!$CI->db->table_exists($tx_table)) {
        return; // schema not present — install hook will handle it next time
    }

    // De-dup: skip if a row already exists for this transactionid OR for this
    // perfex_payment_id (webhook may have inserted before us).
    $existing = $CI->db
        ->where('perfex_payment_id', (int) $payment->id)
        ->or_where('transaction_id', (string) $payment->transactionid)
        ->get($tx_table)->row();
    if ($existing) {
        // Make sure existing row points at this Perfex payment + invoice
        $CI->db->where('id', $existing->id)->update($tx_table, [
            'perfex_payment_id' => (int) $payment->id,
            'perfex_invoice_id' => (int) $payment->invoiceid,
            'status'            => 'processed',
        ]);
        return;
    }

    // Pull invoice context for nice client_name / email in the log
    $invoice = $CI->db->select('clientid, currency, total')
                      ->where('id', (int) $payment->invoiceid)
                      ->get(db_prefix() . 'invoices')->row();
    $client_name = '';
    $email       = '';
    $client_id   = $invoice ? (int) $invoice->clientid : 0;
    if ($client_id) {
        $client = $CI->db->select('company, phonenumber')
                         ->where('userid', $client_id)
                         ->get(db_prefix() . 'clients')->row();
        if ($client) {
            $client_name = (string) $client->company;
        }
        $primary = $CI->db->select('email')
                          ->where('userid', $client_id)
                          ->where('is_primary', 1)
                          ->get(db_prefix() . 'contacts')->row();
        if ($primary) {
            $email = (string) $primary->email;
        }
    }

    // Currency code mapping (Perfex stores currency id; map to Nedarim 1/2)
    $currency_code = 1; // ILS default
    $currency_name = '';
    if ($invoice && !empty($invoice->currency)) {
        $cur = $CI->db->select('name')
                      ->where('id', (int) $invoice->currency)
                      ->get(db_prefix() . 'currencies')->row();
        if ($cur) {
            $currency_name = strtoupper((string) $cur->name);
            $currency_code = $currency_name === 'USD' ? 2 : 1;
        }
    }

    // Receipt type defaults from gateway settings; webhook will refine later
    $receipt_type = (string) (get_option('paymentmethod_nedarimpay_receipt_type') ?: 'student');

    $CI->db->insert($tx_table, [
        'transaction_id'    => (string) ($payment->transactionid ?: 'PFX-PAY-' . $payment->id),
        'perfex_client_id'  => $client_id ?: null,
        'perfex_invoice_id' => (int) $payment->invoiceid,
        'perfex_payment_id' => (int) $payment->id,
        'receipt_type'      => in_array($receipt_type, ['student', 'donation'], true) ? $receipt_type : 'student',
        'client_name'       => $client_name,
        'email'             => $email,
        'amount'            => (float) $payment->amount,
        'currency'          => $currency_code,
        'transaction_type'  => 'gateway',
        'makor'             => 'gateway',
        'transaction_time'  => !empty($payment->date) ? $payment->date : date('Y-m-d H:i:s'),
        'status'            => 'processed',
        'created_at'        => date('Y-m-d H:i:s'),
    ]);
}

// =============================================================================
// HOOK: render the "Copy Nedarim Payment Link" menu item on admin invoice
// =============================================================================
/**
 * Fires inside the More-dropdown on the admin invoice preview, right under
 * "View as customer". Emits an <a> tag carrying the public payment URL as
 * a data attribute so our JS can copy it to the clipboard.
 *
 * Visibility rules:
 *  - Hidden when the invoice is fully paid or cancelled (no point sharing).
 *  - Hidden when the gateway is not active (avoids dead links).
 *
 * @param object $invoice  The invoice passed by the template hook
 */
function nedarimpay_render_admin_pay_link($invoice)
{
    if (!$invoice || empty($invoice->id) || empty($invoice->hash)) {
        return;
    }

    // Don't render for paid / cancelled invoices
    $skip_statuses = [];
    if (class_exists('Invoices_model')) {
        $skip_statuses = [
            Invoices_model::STATUS_PAID,
            Invoices_model::STATUS_CANCELLED,
        ];
    } else {
        // Magic number fallback: 2 = paid, 5 = cancelled in stock Perfex
        $skip_statuses = [2, 5];
    }
    if (in_array((int) $invoice->status, $skip_statuses, true)) {
        return;
    }

    // Hide when the gateway hasn't been activated
    if ((string) get_option('paymentmethod_nedarimpay_active') !== '1') {
        return;
    }

    $pay_url = site_url('nedarimpay/gateway/pay')
             . '?invoiceid=' . (int) $invoice->id
             . '&hash='      . rawurlencode($invoice->hash);

    // The dropdown <li> wrapper is already in the template. We emit ONLY a
    // hidden marker — the JS then renders a real Pay button + a Copy button
    // into the action toolbar (next to "Send to client" / "Record Payment").
    //
    // We intentionally do NOT render a visible <a> inside the dropdown <li>
    // because Perfex's dropdown CSS expects exactly one <a> per <li>; a
    // second link disrupts the menu layout and doesn't inherit hover styles.
    echo '<span'
       . ' class="nedarimpay-admin-bigbtn-marker"'
       . ' style="display:none"'
       . ' data-pay-url="'    . htmlspecialchars($pay_url, ENT_QUOTES)                                . '"'
       . ' data-label-pay="'  . htmlspecialchars(_l('nedarimpay_pay_with_nedarim'),  ENT_QUOTES)      . '"'
       . ' data-label-copy="' . htmlspecialchars(_l('nedarimpay_copy_payment_link'), ENT_QUOTES)      . '"'
       . ' data-msg-copied="' . htmlspecialchars(_l('nedarimpay_link_copied'),       ENT_QUOTES)      . '"'
       . ' data-msg-fail="'   . htmlspecialchars(_l('nedarimpay_link_copy_failed'),  ENT_QUOTES)      . '"'
       . '></span>';
}

// =============================================================================
// HOOK: enqueue the admin-side click handler JS
// =============================================================================
/**
 * Loads admin_invoice_pay_link.js on every admin page. The script self-checks
 * for our marker class and is a cheap no-op anywhere else.
 */
function nedarimpay_inject_admin_pay_link_js()
{
    $path = NEDARIMPAY_MODULE_PATH . '/assets/js/admin_invoice_pay_link.js';
    if (!file_exists($path)) {
        return;
    }
    $script_url = NEDARIMPAY_MODULE_URL . 'assets/js/admin_invoice_pay_link.js'
                . '?v=' . filemtime($path);
    echo '<script src="' . htmlspecialchars($script_url, ENT_QUOTES) . '" defer></script>';
}

// ─── Menu ────────────────────────────────────────────────────────────────────
function nedarimpay_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('nedarimpay', '', 'view')) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('nedarimpay', [
        'name'     => _l('nedarimpay_menu_title'),
        'icon'     => 'fa fa-credit-card',
        'position' => 25,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-dashboard',
        'name'     => _l('nedarimpay_dashboard'),
        'href'     => admin_url('nedarimpay/dashboard'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-transactions',
        'name'     => _l('nedarimpay_transactions'),
        'href'     => admin_url('nedarimpay/transactions'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-manual-charge',
        'name'     => _l('nedarimpay_manual_charge'),
        'href'     => admin_url('nedarimpay/manual_charge'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('nedarimpay', [
        'slug'     => 'nedarimpay-settings',
        'name'     => _l('settings'),
        'href'     => admin_url('nedarimpay/settings'),
        'position' => 4,
    ]);
}

// ─── Module Action Links in Modules List ─────────────────────────────────────
function nedarimpay_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('nedarimpay/settings') . '">' . _l('settings') . '</a>';
    return $actions;
}
