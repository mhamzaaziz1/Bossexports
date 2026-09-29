<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<div class="prm-flex-between">
  <h1><?= prm_safe($deal->deal_number) ?></h1>
  <span class="prm-pill"><?= prm_safe($deal->pipeline_stage) ?></span>
</div>
<table class="prm-table">
  <tr><th>Client</th><td><?= prm_safe($deal->client_name) ?></td></tr>
  <tr><th>Company</th><td><?= prm_safe($deal->company_name) ?></td></tr>
  <tr><th>Email</th><td><?= prm_safe($deal->email) ?></td></tr>
  <tr><th>Phone</th><td><?= prm_safe($deal->phone) ?></td></tr>
  <tr><th>Estimated</th><td><?= prm_format_money($deal->estimated_value, $deal->currency) ?></td></tr>
  <tr><th>Lead score</th><td><?= (int)$deal->lead_score ?>/100 (<?= (int)$deal->win_probability ?>% win prob)</td></tr>
  <tr><th>Lock until</th><td><?= prm_safe($deal->lock_expires_at) ?></td></tr>
  <tr><th>Description</th><td><?= nl2br(prm_safe($deal->description)) ?></td></tr>
</table>

<h3>History</h3>
<ul class="prm-history">
  <?php foreach ($history as $h): ?>
    <li><strong><?= prm_safe($h->event) ?></strong>
      <span class="prm-muted"><?= prm_safe($h->from_stage) ?> → <?= prm_safe($h->to_stage) ?></span>
      <span class="prm-muted pull-right"><?= prm_safe($h->created_at) ?></span>
      <?php if (!empty($h->note)): ?><div class="prm-muted"><?= prm_safe($h->note) ?></div><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
