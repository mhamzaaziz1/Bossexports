<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<div class="prm-flex-between">
  <h1>My deals</h1>
  <a href="<?= site_url('partner-portal/deals/new') ?>" class="prm-btn primary"><i class="fa fa-plus"></i> Register a deal</a>
</div>
<table class="prm-table">
  <thead><tr><th>#</th><th>Client</th><th>Value</th><th>Stage</th><th>Score</th><th>Submitted</th></tr></thead>
  <tbody>
    <?php foreach ($deals as $d): ?>
      <tr>
        <td><a href="<?= site_url('partner-portal/deals/'.$d->id) ?>"><?= prm_safe($d->deal_number) ?></a></td>
        <td><?= prm_safe($d->client_name) ?> <small><?= prm_safe($d->company_name) ?></small></td>
        <td><?= prm_format_money($d->actual_value ?: $d->estimated_value, $d->currency) ?></td>
        <td><span class="prm-pill"><?= prm_safe($d->pipeline_stage) ?></span></td>
        <td><?= (int)$d->lead_score ?>/<?= (int)$d->win_probability ?>%</td>
        <td><?= prm_safe(date('M j, Y', strtotime($d->submitted_at ?: 'now'))) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
