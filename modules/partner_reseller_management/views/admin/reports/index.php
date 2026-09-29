<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_reports') ?></h4><hr/>

  <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_revenue_by_month') ?></h4></div>
    <table class="table">
      <thead><tr><th><?= _l('prm_month') ?></th><th><?= _l('prm_revenue') ?></th><th><?= _l('prm_deals_won') ?></th></tr></thead>
      <tbody>
        <?php foreach ($by_month as $r): ?>
          <tr>
            <td><?= prm_safe($r->m) ?></td>
            <td><?= prm_format_money($r->revenue) ?></td>
            <td><?= (int)$r->deals ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_revenue_by_partner') ?></h4></div>
    <table class="table dt-table">
      <thead><tr>
        <th><?= _l('prm_code') ?></th><th><?= _l('prm_partner') ?></th>
        <th><?= _l('prm_revenue') ?></th><th><?= _l('prm_deals') ?></th><th><?= _l('prm_commission') ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($by_partner as $r): ?>
          <tr>
            <td><?= prm_safe($r->partner_code) ?></td>
            <td><?= prm_safe($r->company_name) ?></td>
            <td><?= prm_format_money($r->revenue ?? 0) ?></td>
            <td><?= (int)$r->deals ?></td>
            <td><?= prm_format_money($r->commission ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php init_tail(); ?>
