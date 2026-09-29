<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Profile</h1>
<?= form_open() ?>
<div class="row">
  <div class="col-md-6"><label>Phone</label><input type="text" name="phone" class="prm-input" value="<?= prm_safe($partner->phone) ?>"></div>
  <div class="col-md-6"><label>Website</label><input type="text" name="website" class="prm-input" value="<?= prm_safe($partner->website) ?>"></div>
  <div class="col-md-12"><label>Address</label><textarea name="address" class="prm-input" rows="3"><?= prm_safe($partner->address) ?></textarea></div>
  <div class="col-md-4"><label>City</label><input type="text" name="city" class="prm-input" value="<?= prm_safe($partner->city) ?>"></div>
  <div class="col-md-4"><label>State</label><input type="text" name="state" class="prm-input" value="<?= prm_safe($partner->state) ?>"></div>
  <div class="col-md-4"><label>Country</label><input type="text" name="country" class="prm-input" value="<?= prm_safe($partner->country) ?>"></div>
  <div class="col-md-6"><label>IBAN</label><input type="text" name="iban" class="prm-input" value="<?= prm_safe($partner->iban) ?>"></div>
  <div class="col-md-3"><label>SWIFT</label><input type="text" name="swift" class="prm-input" value="<?= prm_safe($partner->swift) ?>"></div>
  <div class="col-md-3"><label>Bank name</label><input type="text" name="bank_name" class="prm-input" value="<?= prm_safe($partner->bank_name) ?>"></div>
</div>
<button class="prm-btn primary" type="submit">Save changes</button>
<?= form_close() ?>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
