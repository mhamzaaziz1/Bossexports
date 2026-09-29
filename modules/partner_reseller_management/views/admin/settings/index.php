<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_settings') ?></h4><hr/>
  <?= form_open(admin_url('partner_reseller_management/settings')) ?>
    <div class="panel panel-default"><div class="panel-body">
      <?php foreach ($settings as $s): ?>
        <div class="form-group row">
          <label class="col-md-4 control-label"><?= prm_safe($s->setting_key) ?></label>
          <div class="col-md-8">
            <?php if ($s->setting_type === 'boolean'): ?>
              <div class="checkbox checkbox-primary">
                <input type="hidden" name="<?= prm_safe($s->setting_key) ?>" value="0">
                <input id="s-<?= prm_safe($s->setting_key) ?>" type="checkbox" name="<?= prm_safe($s->setting_key) ?>" value="1" <?= $s->setting_value=='1'?'checked':'' ?>>
                <label for="s-<?= prm_safe($s->setting_key) ?>"><?= prm_safe($s->description) ?></label>
              </div>
            <?php elseif ($s->setting_type === 'password'): ?>
              <input type="password" class="form-control" name="<?= prm_safe($s->setting_key) ?>" value="<?= prm_safe($s->setting_value) ?>" autocomplete="new-password">
              <small class="text-muted"><?= prm_safe($s->description) ?></small>
            <?php else: ?>
              <input type="text" class="form-control" name="<?= prm_safe($s->setting_key) ?>" value="<?= prm_safe($s->setting_value) ?>">
              <small class="text-muted"><?= prm_safe($s->description) ?></small>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-info pull-right"><?= _l('save') ?></button>
      <div class="clearfix"></div>
    </div></div>
  <?= form_close() ?>
</div></div>
<?php init_tail(); ?>
