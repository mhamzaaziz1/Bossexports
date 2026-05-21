<?php
defined('BASEPATH') or exit('No direct script access allowed');
$_lang     = $this->config->item('language');
$_is_rtl   = in_array($_lang, ['hebrew', 'arabic', 'persian', 'urdu']);
$_dir      = $_is_rtl ? 'rtl' : 'ltr';
$_lang_tag = $_is_rtl ? 'he' : $_lang;
?>
<!DOCTYPE html>
<html lang="<?php echo $_lang_tag; ?>" dir="<?php echo $_dir; ?>">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title><?php echo _l('nedarimpay_pay_title', format_invoice_number($invoiceid)); ?> — <?php echo get_option('companyname'); ?></title>
   <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }

      body {
         font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
         background: #f0f2f5;
         color: #333;
         min-height: 100vh;
         display: flex;
         flex-direction: column;
      }

      /* ── Header ─────────────────────────────────────────────────────── */
      .np-header {
         background: #fff;
         border-bottom: 1px solid #e4e7ea;
         padding: 14px 24px;
         display: flex;
         align-items: center;
         justify-content: space-between;
      }
      .np-header .logo {
         font-size: 18px;
         font-weight: 700;
         color: #1a73e8;
      }
      .np-header .invoice-badge {
         background: #f0f2f5;
         border: 1px solid #d0d5dd;
         border-radius: 20px;
         padding: 4px 14px;
         font-size: 13px;
         color: #555;
      }

      /* ── Layout ─────────────────────────────────────────────────────── */
      .np-main {
         flex: 1;
         display: flex;
         align-items: flex-start;
         justify-content: center;
         gap: 24px;
         padding: 32px 24px;
         max-width: 1100px;
         margin: 0 auto;
         width: 100%;
      }

      /* ── Summary Card ────────────────────────────────────────────────── */
      .np-summary {
         width: 280px;
         flex-shrink: 0;
      }
      .np-card {
         background: #fff;
         border: 1px solid #e4e7ea;
         border-radius: 10px;
         padding: 22px;
      }
      .np-card h3 {
         font-size: 13px;
         text-transform: uppercase;
         letter-spacing: .5px;
         color: #888;
         margin-bottom: 12px;
      }
      .np-amount-box {
         border-radius: 8px;
         background: #f0f7ff;
         border: 1px solid #cce1ff;
         padding: 16px;
         text-align: center;
         margin-bottom: 18px;
      }
      .np-amount-box .label {
         font-size: 12px;
         color: #666;
         margin-bottom: 4px;
      }
      .np-amount-box .value {
         font-size: 28px;
         font-weight: 700;
         color: #1a73e8;
      }
      .np-detail-row {
         display: flex;
         justify-content: space-between;
         font-size: 13px;
         padding: 6px 0;
         border-bottom: 1px solid #f0f0f0;
         color: #555;
      }
      .np-detail-row:last-child { border-bottom: none; }
      .np-detail-row span:last-child { font-weight: 600; color: #333; }

      .np-secure-note {
         margin-top: 16px;
         font-size: 12px;
         color: #888;
         text-align: center;
         line-height: 1.5;
      }
      .np-secure-note i { color: #4caf50; }

      /* ── iFrame Container ────────────────────────────────────────────── */
      .np-iframe-wrap {
         flex: 1;
         min-width: 0;
      }
      .np-iframe-card {
         background: #fff;
         border: 1px solid #e4e7ea;
         border-radius: 10px;
         overflow: hidden;
         min-height: 520px;
         position: relative;
      }
      .np-iframe-header {
         background: #1a73e8;
         padding: 14px 20px;
         color: #fff;
         font-size: 15px;
         font-weight: 600;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      .np-iframe-header svg {
         width: 18px; height: 18px; fill: #fff;
      }
      .np-loader {
         position: absolute;
         inset: 60px 0 0 0;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         gap: 12px;
         font-size: 14px;
         color: #888;
      }
      .np-spinner {
         width: 36px; height: 36px;
         border: 3px solid #e0e0e0;
         border-top-color: #1a73e8;
         border-radius: 50%;
         animation: spin .8s linear infinite;
      }
      @keyframes spin { to { transform: rotate(360deg); } }

      #nedarim-iframe {
         width: 100%;
         min-height: 520px;
         border: none;
         display: none; /* shown after load */
      }

      /* ── Cancel Link ─────────────────────────────────────────────────── */
      .np-cancel {
         text-align: center;
         margin-top: 12px;
         font-size: 13px;
      }
      .np-cancel a {
         color: #888;
         text-decoration: none;
      }
      .np-cancel a:hover { color: #333; text-decoration: underline; }

      /* ── Footer ──────────────────────────────────────────────────────── */
      .np-footer {
         text-align: center;
         padding: 16px;
         font-size: 12px;
         color: #aaa;
         border-top: 1px solid #e9ecef;
         background: #fff;
      }

      /* ── Responsive ──────────────────────────────────────────────────── */
      @media (max-width: 700px) {
         .np-main { flex-direction: column; padding: 16px; }
         .np-summary { width: 100%; }
      }

      /* ── RTL Support (Hebrew / Arabic) ──────────────────────────────── */
      [dir="rtl"] body { font-family: "Segoe UI", "Arial Hebrew", Arial, sans-serif; }
      [dir="rtl"] .np-detail-row { flex-direction: row-reverse; }
      [dir="rtl"] .np-detail-row span:last-child { text-align: left; }
      [dir="rtl"] .np-cancel a { direction: rtl; }
      [dir="rtl"] .np-main { flex-direction: row-reverse; }
      @media (max-width: 700px) {
         [dir="rtl"] .np-main { flex-direction: column; }
      }
   </style>
</head>
<body>

   <!-- Header -->
   <div class="np-header">
      <div class="logo">
         <?php echo get_option('companyname'); ?>
      </div>
      <div class="invoice-badge">
         <?php echo _l('invoice'); ?> <?php echo format_invoice_number($invoiceid); ?>
      </div>
   </div>

   <!-- Main -->
   <div class="np-main">

      <!-- Summary -->
      <div class="np-summary">
         <div class="np-card">
            <h3><?php echo _l('nedarimpay_order_summary'); ?></h3>

            <div class="np-amount-box">
               <div class="label"><?php echo _l('nedarimpay_amount_due'); ?></div>
               <div class="value"><?php echo $currency_sym; ?><?php echo number_format($amount_due, 2); ?></div>
            </div>

            <div class="np-detail-row">
               <span><?php echo _l('invoice'); ?></span>
               <span><?php echo format_invoice_number($invoiceid); ?></span>
            </div>
            <div class="np-detail-row">
               <span><?php echo _l('date'); ?></span>
               <span><?php echo _d($invoice->date); ?></span>
            </div>
            <?php if ($invoice->duedate) : ?>
            <div class="np-detail-row">
               <span><?php echo _l('invoice_due_date'); ?></span>
               <span><?php echo _d($invoice->duedate); ?></span>
            </div>
            <?php endif; ?>

            <div class="np-secure-note">
               &#x1F512; <?php echo _l('nedarimpay_secure_payment_note'); ?>
            </div>
         </div>

         <div class="np-cancel">
            <a href="<?php echo site_url('invoice/' . $invoiceid . '/' . $hash); ?>">
               &larr; <?php echo _l('nedarimpay_back_to_invoice'); ?>
            </a>
         </div>
      </div>

      <!-- iFrame -->
      <div class="np-iframe-wrap">
         <div class="np-iframe-card">
            <div class="np-iframe-header">
               <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
               <?php echo _l('nedarimpay_enter_payment_details'); ?>
            </div>

            <div class="np-loader" id="np-loader">
               <div class="np-spinner"></div>
               <span><?php echo _l('nedarimpay_loading_payment_form'); ?></span>
            </div>

            <iframe
               id="nedarim-iframe"
               src="<?php echo $iframe_src; ?>"
               title="<?php echo _l('nedarimpay_payment_form'); ?>"
               onload="iframeLoaded()"
               scrolling="auto"
               allow="payment"
            ></iframe>
         </div>
      </div>

   </div>

   <!-- Footer -->
   <div class="np-footer">
      <?php echo _l('nedarimpay_powered_by'); ?> &nbsp;
      <strong>Nedarim Plus</strong> &mdash; <?php echo get_option('companyname'); ?>
   </div>

   <script>
   function iframeLoaded() {
      document.getElementById('np-loader').style.display = 'none';
      var iframe = document.getElementById('nedarim-iframe');
      iframe.style.display = 'block';
      // Auto-adjust height
      try {
         var h = iframe.contentWindow.document.body.scrollHeight;
         if (h > 200) iframe.style.height = h + 'px';
      } catch(e) {
         // Cross-origin: use a fixed generous height
         iframe.style.height = '580px';
      }
   }
   </script>

</body>
</html>
