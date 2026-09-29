<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= prm_safe($title) ?> <?= prm_status_label($deal->pipeline_stage) ?></h4>
  <p class="text-muted"><?= _l('prm_partner') ?>: <?= prm_safe($partner->company_name ?? '—') ?> · <?= _l('prm_submitted') ?>: <?= prm_safe($deal->submitted_at) ?></p>
  <hr/>

  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default"><div class="panel-body">
        <table class="table">
          <tr><th><?= _l('prm_client') ?></th><td><?= prm_safe($deal->client_name) ?></td></tr>
          <tr><th><?= _l('prm_company') ?></th><td><?= prm_safe($deal->company_name) ?></td></tr>
          <tr><th><?= _l('prm_email') ?></th><td><?= prm_safe($deal->email) ?></td></tr>
          <tr><th><?= _l('prm_phone') ?></th><td><?= prm_safe($deal->phone) ?></td></tr>
          <tr><th><?= _l('prm_service_type') ?></th><td><?= prm_safe($deal->service_type) ?></td></tr>
          <tr><th><?= _l('prm_estimated_value') ?></th><td><?= prm_format_money($deal->estimated_value, $deal->currency) ?></td></tr>
          <tr><th><?= _l('prm_actual_value') ?></th><td><?= $deal->actual_value ? prm_format_money($deal->actual_value, $deal->currency) : '—' ?></td></tr>
          <tr><th><?= _l('prm_description') ?></th><td><?= nl2br(prm_safe($deal->description)) ?></td></tr>
          <tr><th><?= _l('prm_lead_score') ?></th><td><?= (int)$deal->lead_score ?>/100 · <?= (int)$deal->win_probability ?>% <?= _l('prm_win_prob') ?></td></tr>
          <tr><th><?= _l('prm_lock_until') ?></th><td><?= prm_safe($deal->lock_expires_at) ?></td></tr>
          <?php if ($deal->duplicate_flag): ?>
          <tr class="warning"><th><?= _l('prm_duplicate_of') ?></th><td>#<?= (int)$deal->duplicate_of ?></td></tr>
          <?php endif; ?>
        </table>
      </div></div>

      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_history') ?></h4></div>
        <ul class="list-group">
          <?php foreach ($history as $h): ?>
            <li class="list-group-item">
              <strong><?= prm_safe($h->event) ?></strong>
              <?php if ($h->from_stage || $h->to_stage): ?>
                <small class="text-muted"><?= prm_safe($h->from_stage) ?> → <?= prm_safe($h->to_stage) ?></small>
              <?php endif; ?>
              <span class="pull-right text-muted"><?= prm_safe($h->created_at) ?></span>
              <?php if (!empty($h->note)): ?><div class="text-muted"><?= prm_safe($h->note) ?></div><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="col-md-4">
      <?php if ($deal->approval_status === 'pending' && (is_admin() || staff_can('approve_deals','partner_reseller_management'))): ?>
        <div class="panel panel-default"><div class="panel-body">
          <h4><?= _l('prm_approval') ?></h4>
          <?= form_open(admin_url('partner_reseller_management/deals/approve/'.$deal->id)) ?>
            <textarea name="admin_notes" class="form-control mbot10" placeholder="<?= _l('prm_admin_notes') ?>"></textarea>
            <button type="submit" class="btn btn-success btn-block"><?= _l('prm_approve_deal') ?></button>
          <?= form_close() ?>
          <hr/>
          <?= form_open(admin_url('partner_reseller_management/deals/reject/'.$deal->id)) ?>
            <textarea name="reason" class="form-control mbot10" placeholder="<?= _l('prm_reason') ?>" required></textarea>
            <button type="submit" class="btn btn-danger btn-block"><?= _l('prm_reject_deal') ?></button>
          <?= form_close() ?>
        </div></div>
      <?php endif; ?>

      <?php if ($deal->approval_status === 'approved' && $deal->pipeline_stage !== 'won'): ?>
        <div class="panel panel-default"><div class="panel-body">
          <?= form_open(admin_url('partner_reseller_management/deals/won/'.$deal->id)) ?>
            <h4><?= _l('prm_close_won') ?></h4>
            <input type="number" step="0.01" name="actual_value" class="form-control mbot10" placeholder="<?= _l('prm_actual_value') ?>" required>
            <button type="submit" class="btn btn-success btn-block"><?= _l('prm_mark_won_calc_commission') ?></button>
          <?= form_close() ?>
        </div></div>
      <?php endif; ?>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>
