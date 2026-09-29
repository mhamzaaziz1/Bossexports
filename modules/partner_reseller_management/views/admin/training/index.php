<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_training') ?></h4><hr/>
  <div class="row">
    <div class="col-md-5">
      <div class="panel panel-default"><div class="panel-body">
        <h4><?= _l('prm_new_course') ?></h4>
        <?= form_open(admin_url('partner_reseller_management/training/add_course')) ?>
          <div class="form-group"><label><?= _l('prm_title') ?></label>
            <input type="text" name="title" class="form-control" required></div>
          <div class="form-group"><label><?= _l('prm_difficulty') ?></label>
            <select name="difficulty" class="form-control selectpicker" data-width="100%">
              <?php foreach (['beginner','intermediate','advanced','expert'] as $d): ?>
                <option value="<?= $d ?>"><?= ucfirst($d) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label><?= _l('prm_duration_min') ?></label>
            <input type="number" name="duration_min" class="form-control" value="30"></div>
          <div class="form-group"><label><?= _l('prm_pass_score') ?></label>
            <input type="number" name="pass_score" class="form-control" value="70"></div>
          <div class="form-group"><label><?= _l('prm_video_url') ?></label>
            <input type="text" name="video_url" class="form-control"></div>
          <div class="form-group"><label><?= _l('prm_description') ?></label>
            <textarea name="description" class="form-control" rows="3"></textarea></div>
          <div class="checkbox checkbox-primary">
            <input id="mandatory" type="checkbox" name="mandatory" value="1">
            <label for="mandatory"><?= _l('prm_mandatory') ?></label>
          </div>
          <button class="btn btn-info btn-block" type="submit"><?= _l('submit') ?></button>
        <?= form_close() ?>
      </div></div>
    </div>

    <div class="col-md-7">
      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_courses') ?></h4></div>
        <table class="table">
          <thead><tr><th><?= _l('prm_title') ?></th><th><?= _l('prm_difficulty') ?></th><th><?= _l('prm_duration_min') ?></th><th><?= _l('prm_pass_score') ?></th><th></th></tr></thead>
          <tbody>
            <?php foreach ($courses as $c): ?>
              <tr>
                <td><strong><?= prm_safe($c->title) ?></strong>
                  <?php if ($c->mandatory): ?> <span class="label label-warning"><?= _l('prm_mandatory') ?></span><?php endif; ?>
                </td>
                <td><?= prm_safe(ucfirst($c->difficulty)) ?></td>
                <td><?= (int)$c->duration_min ?></td>
                <td><?= (int)$c->pass_score ?>%</td>
                <td><span class="label <?= $c->active?'label-success':'label-default' ?>"><?= $c->active?'Active':'Disabled' ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>
