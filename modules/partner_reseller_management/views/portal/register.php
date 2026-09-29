<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<div class="prm-auth-box wide">
  <h2><?= _l('prm_partner_register') ?></h2>
  <?= form_open() ?>
    <div class="row">
      <div class="col-md-6"><label>Company name *</label>
        <input type="text" name="company_name" class="prm-input" required></div>
      <div class="col-md-6"><label>Partner type</label>
        <select name="partner_type" class="prm-input">
          <?php foreach (['reseller','referral','contractor','corporate','distributor','affiliate','msp','vad'] as $t): ?>
            <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label>Email *</label>
        <input type="email" name="email" class="prm-input" required></div>
      <div class="col-md-6"><label>Phone</label>
        <input type="text" name="phone" class="prm-input"></div>
      <div class="col-md-6"><label>Country</label>
        <input type="text" name="country" class="prm-input"></div>
      <div class="col-md-6"><label>Website</label>
        <input type="text" name="website" class="prm-input"></div>
      <div class="col-md-6"><label>Username *</label>
        <input type="text" name="username" class="prm-input" required></div>
      <div class="col-md-6"><label>Password *</label>
        <input type="password" name="password" class="prm-input" required></div>
    </div>
    <button type="submit" class="prm-btn primary block"><?= _l('prm_submit') ?></button>
  <?= form_close() ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
