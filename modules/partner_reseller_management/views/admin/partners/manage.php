<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= prm_safe($title) ?></h4><hr/>
  <?= form_open('', ['class' => 'partner-form']) ?>
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default"><div class="panel-body">
        <h4><?= _l('prm_company_info'); ?></h4>
        <div class="row">
          <div class="form-group col-md-6"><label><?= _l('prm_company') ?> *</label>
            <input type="text" name="company_name" class="form-control" required value="<?= prm_safe($partner->company_name ?? '') ?>"></div>
          <div class="form-group col-md-6"><label><?= _l('prm_partner_type') ?></label>
            <select name="partner_type" class="form-control selectpicker" data-width="100%">
              <?php foreach (['reseller','referral','contractor','corporate','distributor','affiliate','msp','vad'] as $t): ?>
                <option value="<?= $t ?>" <?= ($partner->partner_type ?? '')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group col-md-6"><label><?= _l('prm_email') ?> *</label>
            <input type="email" name="email" class="form-control" required value="<?= prm_safe($partner->email ?? '') ?>"></div>
          <div class="form-group col-md-6"><label><?= _l('prm_phone') ?></label>
            <input type="text" name="phone" class="form-control" value="<?= prm_safe($partner->phone ?? '') ?>"></div>
          <div class="form-group col-md-6"><label><?= _l('prm_website') ?></label>
            <input type="text" name="website" class="form-control" value="<?= prm_safe($partner->website ?? '') ?>"></div>
          <div class="form-group col-md-6"><label><?= _l('prm_country') ?></label>
            <input type="text" name="country" class="form-control" value="<?= prm_safe($partner->country ?? '') ?>"></div>
          <div class="form-group col-md-12"><label><?= _l('prm_address') ?></label>
            <textarea name="address" class="form-control"><?= prm_safe($partner->address ?? '') ?></textarea></div>
        </div>
      </div></div>

      <div class="panel panel-default"><div class="panel-body">
        <h4><?= _l('prm_commission'); ?></h4>
        <div class="row">
          <div class="form-group col-md-4"><label><?= _l('prm_commission_type') ?></label>
            <select name="commission_type" class="form-control selectpicker" data-width="100%">
              <?php foreach (['fixed','percentage','tiered','hybrid','product_based'] as $t): ?>
                <option value="<?= $t ?>" <?= ($partner->commission_type ?? '')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group col-md-4"><label><?= _l('prm_commission_value') ?></label>
            <input type="number" step="0.0001" name="commission_value" class="form-control" value="<?= prm_safe($partner->commission_value ?? 0) ?>"></div>
          <div class="form-group col-md-4"><label><?= _l('prm_override_rate') ?> (%)</label>
            <input type="number" step="0.01" name="override_rate" class="form-control" value="<?= prm_safe($partner->override_rate ?? 0) ?>"></div>
          <div class="form-group col-md-12"><label><?= _l('prm_commission_config_json') ?></label>
            <textarea name="commission_config" class="form-control" rows="3" placeholder='[{"min":0,"max":10000,"rate":5}]'><?= prm_safe($partner->commission_config ?? '') ?></textarea>
            <small class="text-muted"><?= _l('prm_commission_config_help') ?></small>
          </div>
        </div>
      </div></div>
    </div>

    <div class="col-md-4">
      <div class="panel panel-default"><div class="panel-body">
        <h4><?= _l('prm_settings_panel') ?></h4>
        <div class="form-group"><label><?= _l('prm_status') ?></label>
          <select name="status" class="form-control selectpicker" data-width="100%">
            <?php foreach (['draft','pending','active','suspended','terminated'] as $s): ?>
              <option value="<?= $s ?>" <?= ($partner->status ?? '')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-group"><label><?= _l('prm_tier') ?></label>
          <select name="tier_level_id" class="form-control selectpicker" data-width="100%">
            <option value=""><?= _l('prm_auto_tier') ?></option>
            <?php foreach ($tiers as $t): ?>
              <option value="<?= $t->id ?>" <?= ($partner->tier_level_id ?? '')==$t->id?'selected':'' ?>>
                <?= prm_safe($t->name) ?>
              </option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-group"><label><?= _l('prm_parent_partner') ?></label>
          <select name="parent_partner_id" class="form-control selectpicker" data-live-search="true" data-width="100%">
            <option value=""><?= _l('prm_none') ?></option>
            <?php foreach ($parents as $par): ?>
              <option value="<?= $par->id ?>" <?= ($partner->parent_partner_id ?? '')==$par->id?'selected':'' ?>><?= prm_safe($par->company_name) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-group"><label><?= _l('prm_currency') ?></label>
          <input type="text" name="currency" class="form-control" value="<?= prm_safe($partner->currency ?? 'USD') ?>"></div>
        <div class="form-group"><label><?= _l('prm_username') ?></label>
          <input type="text" name="username" class="form-control" value="<?= prm_safe($partner->username ?? '') ?>"></div>
        <div class="form-group"><label><?= _l('prm_password') ?></label>
          <input type="password" name="password" class="form-control" placeholder="<?= $partner ? _l('prm_leave_blank_keep') : '' ?>"></div>
        <div class="checkbox checkbox-primary">
          <input id="portal_access" type="checkbox" name="portal_access" value="1" <?= ($partner->portal_access ?? 1)?'checked':'' ?>>
          <label for="portal_access"><?= _l('prm_enable_portal_access') ?></label>
        </div>
        <div class="checkbox checkbox-primary">
          <input id="2fa_enabled" type="checkbox" name="2fa_enabled" value="1" <?= ($partner->{'2fa_enabled'} ?? 0)?'checked':'' ?>>
          <label for="2fa_enabled"><?= _l('prm_enable_2fa') ?></label>
        </div>
      </div></div>

      <button type="submit" class="btn btn-info btn-block"><?= _l('submit') ?></button>
    </div>
  </div>
  <?= form_close() ?>
</div></div>
<?php init_tail(); ?>
