<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_mdf') ?></h4>
  <p class="text-muted"><?= _l('prm_mdf_subtitle') ?></p><hr/>

  <div class="row">
    <div class="col-md-5">
      <div class="panel panel-default"><div class="panel-body">
        <h4><?= _l('prm_allocate_funds') ?></h4>
        <?= form_open(admin_url('partner_reseller_management/mdf/allocate')) ?>
          <div class="form-group"><label><?= _l('prm_partner') ?></label>
            <select name="partner_id" class="form-control selectpicker" data-live-search="true" data-width="100%" required>
              <?php foreach ($partners as $p): ?>
                <option value="<?= $p->id ?>"><?= prm_safe($p->company_name) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label><?= _l('prm_fiscal_year') ?></label>
            <input type="number" name="fiscal_year" class="form-control" value="<?= date('Y') ?>" required></div>
          <div class="form-group"><label><?= _l('prm_amount') ?></label>
            <input type="number" step="0.01" name="amount" class="form-control" required></div>
          <div class="form-group"><label><?= _l('prm_expires') ?></label>
            <input type="date" name="expires_at" class="form-control"></div>
          <button type="submit" class="btn btn-info btn-block"><?= _l('prm_allocate') ?></button>
        <?= form_close() ?>
      </div></div>
    </div>

    <div class="col-md-7">
      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_allocations') ?></h4></div>
        <table class="table">
          <thead><tr>
            <th><?= _l('prm_partner') ?></th><th><?= _l('prm_fiscal_year') ?></th>
            <th><?= _l('prm_allocated') ?></th><th><?= _l('prm_used') ?></th><th><?= _l('prm_remaining') ?></th>
          </tr></thead>
          <tbody>
            <?php foreach ($funds as $f): ?>
              <tr>
                <td><?= prm_safe($f->partner_company ?? '—') ?></td>
                <td><?= (int)$f->fiscal_year ?></td>
                <td><?= prm_format_money($f->allocated, $f->currency) ?></td>
                <td><?= prm_format_money($f->used, $f->currency) ?></td>
                <td><strong><?= prm_format_money($f->remaining, $f->currency) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_campaigns') ?></h4></div>
        <table class="table">
          <thead><tr>
            <th><?= _l('prm_partner') ?></th><th><?= _l('prm_campaign') ?></th>
            <th><?= _l('prm_budget') ?></th><th><?= _l('prm_status') ?></th><th></th>
          </tr></thead>
          <tbody>
            <?php foreach ($campaigns as $c): ?>
              <tr>
                <td><?= prm_safe($c->partner_company ?? '—') ?></td>
                <td><?= prm_safe($c->name) ?> <small class="text-muted">(<?= prm_safe($c->campaign_type) ?>)</small></td>
                <td><?= prm_format_money($c->budget) ?></td>
                <td><?= prm_status_label($c->status) ?></td>
                <td>
                  <?php if ($c->status === 'submitted'): ?>
                    <a class="btn btn-success btn-xs" href="<?= admin_url('partner_reseller_management/mdf/approve_campaign/'.$c->id) ?>"><?= _l('approve') ?></a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>
