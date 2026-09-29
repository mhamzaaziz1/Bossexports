<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <h4 class="no-margin"><?= _l('prm_gamification') ?></h4><hr/>
  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_badges') ?></h4></div>
        <ul class="list-group">
          <?php foreach ($badges as $b): ?>
            <li class="list-group-item">
              <i class="fa <?= prm_safe($b->icon) ?>" style="color: <?= prm_safe($b->color) ?>"></i>
              <strong class="mleft5"><?= prm_safe($b->name) ?></strong>
              <span class="badge pull-right"><?= (int)$b->points ?> <?= _l('prm_pts') ?></span>
              <div class="text-muted"><?= prm_safe($b->description) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><?= _l('prm_leaderboard') ?></h4></div>
        <table class="table">
          <thead><tr><th>#</th><th><?= _l('prm_partner') ?></th><th><?= _l('prm_score') ?></th><th><?= _l('prm_revenue') ?></th></tr></thead>
          <tbody>
            <?php foreach ($leaderboard as $i => $p): ?>
              <tr>
                <td><strong><?= $i + 1 ?></strong></td>
                <td><?= prm_safe($p->company_name) ?> <small class="text-muted"><?= prm_safe($p->partner_code) ?></small></td>
                <td><span class="badge"><?= (int)$p->lifetime_score ?></span></td>
                <td><?= prm_format_money($p->total_revenue) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>
