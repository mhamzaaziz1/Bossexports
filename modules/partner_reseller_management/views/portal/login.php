<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<div class="prm-auth-box">
  <h2><?= _l('prm_partner_login') ?></h2>
  <?= form_open() ?>
    <label><?= _l('prm_username_or_email') ?></label>
    <input type="text" name="username" class="prm-input" required autofocus>
    <label><?= _l('prm_password') ?></label>
    <input type="password" name="password" class="prm-input" required>
    <button type="submit" class="prm-btn primary block"><?= _l('prm_login') ?></button>
  <?= form_close() ?>
  <?php if ((int)get_prm_option('prm_self_signup',1)===1): ?>
    <p class="prm-muted">Don't have an account? <a href="<?= site_url('partner-portal/register') ?>"><?= _l('prm_register') ?></a></p>
  <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
