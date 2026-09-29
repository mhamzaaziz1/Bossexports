<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Wallet</h1>
<div class="prm-grid">
  <div class="prm-card"><div class="prm-card-label">Available</div>
    <div class="prm-card-value"><?= prm_format_money($balance['balance'] ?? 0) ?></div></div>
  <div class="prm-card"><div class="prm-card-label">On hold</div>
    <div class="prm-card-value"><?= prm_format_money($balance['pending'] ?? 0) ?></div></div>
</div>

<h3>Request payout</h3>
<?= form_open() ?>
  <input type="hidden" name="action" value="request_payout">
  <div class="prm-checklist">
    <?php foreach ($eligible_commissions as $c): ?>
      <label class="prm-row-check">
        <input type="checkbox" name="commission_ids[]" value="<?= (int)$c->id ?>">
        <span><?= prm_safe($c->commission_number) ?> · <?= prm_format_money($c->commission_amount, $c->currency) ?></span>
      </label>
    <?php endforeach; ?>
  </div>
  <select name="payment_method" class="prm-input">
    <option value="bank_transfer">Bank transfer</option>
    <option value="paypal">PayPal</option>
    <option value="wise">Wise</option>
    <option value="stripe">Stripe</option>
    <option value="other">Other</option>
  </select>
  <textarea name="notes" class="prm-input" placeholder="Notes (optional)"></textarea>
  <button class="prm-btn primary" type="submit">Request payout</button>
<?= form_close() ?>

<h3>Recent payouts</h3>
<table class="prm-table">
  <thead><tr><th>#</th><th>Amount</th><th>Method</th><th>Status</th><th>Requested</th></tr></thead>
  <tbody>
    <?php foreach ($payouts as $po): ?>
      <tr>
        <td><?= prm_safe($po->payout_number) ?></td>
        <td><?= prm_format_money($po->total_amount, $po->currency) ?></td>
        <td><?= prm_safe($po->payment_method) ?></td>
        <td><?= prm_safe($po->status) ?></td>
        <td><?= prm_safe($po->requested_at) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Ledger</h3>
<table class="prm-table">
  <thead><tr><th>When</th><th>Type</th><th>Amount</th><th>Balance</th><th>Description</th></tr></thead>
  <tbody>
    <?php foreach ($ledger as $l): ?>
      <tr>
        <td><?= prm_safe($l->created_at) ?></td>
        <td><span class="prm-pill"><?= prm_safe($l->entry_type) ?></span></td>
        <td><?= prm_format_money($l->amount, $l->currency) ?></td>
        <td><?= prm_format_money($l->balance_after, $l->currency) ?></td>
        <td><?= prm_safe($l->description) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
