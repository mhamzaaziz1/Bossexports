<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Register a new deal</h1>
<p class="prm-muted">Lock window: <?= (int)get_prm_option('prm_deal_lock_days', 30) ?> days. Duplicate detection is active.</p>
<?= form_open() ?>
<div class="row">
  <div class="col-md-6"><label>Client name *</label>
    <input type="text" name="client_name" class="prm-input" required></div>
  <div class="col-md-6"><label>Client company *</label>
    <input type="text" name="company_name" class="prm-input" required></div>
  <div class="col-md-6"><label>Email *</label>
    <input type="email" name="email" class="prm-input" required></div>
  <div class="col-md-6"><label>Phone *</label>
    <input type="text" name="phone" class="prm-input" required></div>
  <div class="col-md-6"><label>Service / Product</label>
    <input type="text" name="service_type" class="prm-input"></div>
  <div class="col-md-6"><label>Estimated value *</label>
    <input type="number" step="0.01" name="estimated_value" class="prm-input" required></div>
  <div class="col-md-6"><label>Currency</label>
    <input type="text" name="currency" class="prm-input" value="<?= prm_safe(prm_current_partner()->currency) ?>"></div>
  <div class="col-md-6"><label>Expected close date</label>
    <input type="date" name="expected_close_date" class="prm-input"></div>
  <div class="col-md-12"><label>Description</label>
    <textarea name="description" class="prm-input" rows="4"></textarea></div>
  <div class="col-md-12"><label>Requirements</label>
    <textarea name="requirements" class="prm-input" rows="3"></textarea></div>
</div>
<button type="submit" class="prm-btn primary"><i class="fa fa-paper-plane"></i> Submit deal</button>
<?= form_close() ?>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
