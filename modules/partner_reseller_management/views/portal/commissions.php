<?php
defined('BASEPATH') or exit('No direct script access allowed');
ob_start(); ?>
<h1>Commissions</h1>
<table class="prm-table">
  <thead><tr><th>#</th><th>Kind</th><th>Deal value</th><th>Rate</th><th>Amount</th><th>Status</th><th>Paid</th></tr></thead>
  <tbody>
    <?php foreach ($commissions as $c): ?>
      <tr>
        <td><?= prm_safe($c->commission_number) ?></td>
        <td><span class="prm-pill"><?= prm_safe($c->kind) ?></span></td>
        <td><?= prm_format_money($c->deal_value, $c->currency) ?></td>
        <td><?= prm_safe($c->commission_rate) ?><?= $c->commission_type==='fixed'?'':'%' ?></td>
        <td><strong><?= prm_format_money($c->commission_amount, $c->currency) ?></strong></td>
        <td><?= prm_safe($c->status) ?></td>
        <td><?= prm_safe($c->paid_date ?: '—') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
