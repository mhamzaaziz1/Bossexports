<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="_buttons">
          <a href="<?= admin_url('partner_reseller_management/partners/manage'); ?>" class="btn btn-info pull-left mright5">
            <i class="fa fa-plus"></i> <?= _l('prm_new_partner'); ?>
          </a>
          <a href="<?= admin_url('partner_reseller_management/deals'); ?>" class="btn btn-default pull-left">
            <i class="fa fa-briefcase"></i> <?= _l('prm_deals'); ?>
          </a>
        </div>
        <div class="clearfix"></div><hr/>
        <h4 class="no-margin"><?= _l('prm_dashboard'); ?></h4>
        <p class="text-muted"><?= _l('prm_dashboard_subtitle'); ?></p>
      </div>
    </div>

    <div class="row">
      <?php
        $cards = [
          ['fa-users','Partners',$stats['total_partners'],'info'],
          ['fa-check','Active',$stats['active_partners'],'success'],
          ['fa-clock-o','Pending',$stats['pending_partners'],'warning'],
          ['fa-briefcase','Total Deals',$stats['total_deals'],'primary'],
          ['fa-flag','Pending Approval',$stats['pending_deals'],'warning'],
          ['fa-trophy','Won Deals',$stats['won_deals'],'success'],
          ['fa-money','Pending Commission',prm_format_money($stats['pending_commission']),'info'],
          ['fa-credit-card','Paid Commission',prm_format_money($stats['paid_commission']),'success'],
        ];
        foreach ($cards as $c): ?>
        <div class="col-md-3 col-sm-6">
          <div class="panel panel-<?= $c[3] ?> prm-stat-card">
            <div class="panel-body">
              <i class="fa <?= $c[0] ?> fa-2x pull-right text-<?= $c[3] ?>"></i>
              <div class="prm-stat-label"><?= prm_safe($c[1]) ?></div>
              <div class="prm-stat-value"><?= prm_safe($c[2]) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="row">
      <div class="col-md-7">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= _l('prm_top_partners'); ?></h4></div>
          <div class="panel-body">
            <table class="table table-striped">
              <thead><tr>
                <th>#</th><th><?= _l('prm_partner'); ?></th><th><?= _l('prm_revenue'); ?></th>
                <th><?= _l('prm_deals'); ?></th><th><?= _l('prm_commission'); ?></th>
              </tr></thead>
              <tbody>
                <?php foreach ($top_partners as $i => $p): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><a href="<?= admin_url('partner_reseller_management/partners/manage/'.$p->id) ?>"><?= prm_safe($p->company_name); ?></a><br/><small class="text-muted"><?= prm_safe($p->partner_code) ?></small></td>
                  <td><?= prm_format_money($p->total_revenue, $p->currency) ?></td>
                  <td><?= (int)$p->total_deals ?></td>
                  <td><?= prm_format_money($p->total_commission, $p->currency) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= _l('prm_pipeline'); ?></h4></div>
          <div class="panel-body">
            <?php foreach ($pipeline as $stage => $row): ?>
              <div class="prm-pipeline-row">
                <div class="prm-stage"><?= prm_safe(ucfirst(str_replace('_',' ',$stage))) ?></div>
                <div class="prm-bar-wrap">
                  <div class="prm-bar" style="width:<?= min(100, $row['count']*5) ?>%;"></div>
                </div>
                <div class="prm-stage-meta"><?= (int)$row['count'] ?> · <?= prm_format_money($row['value']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><h4 class="panel-title"><?= _l('prm_recent_deals'); ?></h4></div>
          <div class="panel-body">
            <table class="table table-hover">
              <thead><tr>
                <th><?= _l('prm_deal_number'); ?></th><th><?= _l('prm_client'); ?></th>
                <th><?= _l('prm_value'); ?></th><th><?= _l('prm_stage'); ?></th><th><?= _l('prm_score'); ?></th><th></th>
              </tr></thead>
              <tbody>
                <?php foreach ($recent_deals as $d): ?>
                <tr>
                  <td><a href="<?= admin_url('partner_reseller_management/deals/view/'.$d->id) ?>"><?= prm_safe($d->deal_number) ?></a></td>
                  <td><?= prm_safe($d->client_name) ?><br/><small class="text-muted"><?= prm_safe($d->company_name) ?></small></td>
                  <td><?= prm_format_money($d->estimated_value, $d->currency) ?></td>
                  <td><?= prm_status_label($d->pipeline_stage); ?></td>
                  <td><?php if ($d->lead_score !== null): ?><span class="badge"><?= (int)$d->lead_score ?>/100</span><?php endif; ?></td>
                  <td><a href="<?= admin_url('partner_reseller_management/deals/view/'.$d->id) ?>" class="btn btn-default btn-xs"><?= _l('view'); ?></a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
