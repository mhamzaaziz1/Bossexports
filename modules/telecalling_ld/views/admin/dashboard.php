<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <!-- ══ PREMIUM HEADER ══════════════════════════════════════════ -->
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-dashboard"></i></div>
          <div>
            <h1 class="tc-h-main">Telecalling Distribution <small>Performance Dashboard</small></h1>
            <p class="tc-h-sub">Global overview of lead allocation, agent activity, and conversion metrics</p>
          </div>
        </div>
        <div class="tc-actions">
           <form method="get" id="filter-form" style="display:inline-flex; gap:10px; align-items:center;">
              <select name="period" onchange="this.form.submit()" class="form-control selectpicker" style="width:140px;">
                <?php foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','custom'=>'Custom Range'] as $v=>$l): ?>
                <option value="<?=$v?>" <?=$period==$v?'selected':''?>><?=$l?></option>
                <?php endforeach; ?>
              </select>
           </form>
           <a href="<?=admin_url('telecalling_ld/assign')?>" class="tc-btn tc-btn-primary"><i class="fa fa-plus"></i> New Assignment</a>
        </div>
      </div>

      <!-- ══ KPI GRID ════════════════════════════════════════════════ -->
      <div class="tc-kpi-grid">
        <div class="tc-kpi blue">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-phone"></i></div>
            <div class="tc-kpi-trend up"><i class="fa fa-caret-up"></i> +<?=number_format($total_calls_today)?> today</div>
          </div>
          <div class="tc-kpi-lbl">Total Calls</div>
          <div class="tc-kpi-val"><?=number_format($total_calls)?></div>
          <div class="tc-kpi-sub">Period Volume</div>
        </div>

        <div class="tc-kpi green">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-calendar-check-o"></i></div>
          </div>
          <div class="tc-kpi-lbl">Appointments</div>
          <div class="tc-kpi-val"><?=number_format($total_appointments)?></div>
          <?php $appt_rate = $total_calls > 0 ? round($total_appointments / $total_calls * 100) : 0; ?>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-green" style="width:<?=$appt_rate?>%"></div></div>
          <div class="tc-kpi-sub">Rate: <?=$appt_rate?>%</div>
        </div>

        <div class="tc-kpi orange">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-star"></i></div>
          </div>
          <div class="tc-kpi-lbl">Conversions</div>
          <div class="tc-kpi-val"><?=number_format($total_conversions)?></div>
          <?php $conv_rate = $total_calls > 0 ? round($total_conversions / $total_calls * 100) : 0; ?>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-amber" style="width:<?=$conv_rate?>%"></div></div>
          <div class="tc-kpi-sub">Rate: <?=$conv_rate?>%</div>
        </div>

        <div class="tc-kpi purple">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-envelope-o"></i></div>
          </div>
          <div class="tc-kpi-lbl">Emails Sent</div>
          <div class="tc-kpi-val"><?=number_format($email_stats->total_sent ?? 0)?></div>
          <?php $open_rate = round($email_stats->open_rate ?? 0, 1); ?>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-purple" style="width:<?=$open_rate?>%"></div></div>
          <div class="tc-kpi-sub">Open Rate: <?=$open_rate?>%</div>
        </div>
      </div>

      <!-- ══ CHARTS ROW ══════════════════════════════════════════════ -->
      <div class="row">
        <div class="col-md-8">
          <div class="tc-card">
            <div class="tc-card-title">Call Performance History <small>Daily volume tracking</small></div>
            <canvas id="chart-daily" height="250"></canvas>
          </div>
        </div>
        <div class="col-md-4">
          <div class="tc-card">
            <div class="tc-card-title">Status Distribution</div>
            <canvas id="chart-status-donut" height="250"></canvas>
          </div>
        </div>
      </div>

      <!-- ══ AGENT PERFORMANCE ═══════════════════════════════════════ -->
      <div class="tc-card">
        <div class="tc-card-title">Agent Leaderboard <small>Performance ranking for current period</small></div>
        <div class="tc-table-responsive">
          <table class="tc-table">
            <thead>
              <tr>
                <th width="50">Rank</th>
                <th>Agent</th>
                <th>Group</th>
                <th>Calls</th>
                <th>Appts</th>
                <th>Convs</th>
                <th>Activity</th>
                <th width="100">Target</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $_calls_col = array_column((array)$agent_stats, 'calls');
              $max_calls  = max(1, !empty($_calls_col) ? max($_calls_col) : 0);
              foreach($agent_stats as $i=>$a):
                $tgt     = tc_target_status($a->staffid,'calls','daily');
                $pct     = $tgt ? $tgt['pct'] : 0;
                $pill_cls= $pct>=100?'tc-pill-success':($pct>=60?'tc-pill-warning':'tc-pill-danger');
                $bar_w   = round(((int)$a->calls / $max_calls)*100);
                $ranking_icon = '';
                if($i == 0) $ranking_icon = '<i class="fa fa-trophy text-warning"></i> ';
              ?>
              <tr>
                <td style="font-weight:800; color:var(--tc-text-muted);"><?=$ranking_icon?><?=$i+1?></td>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div class="tc-avatar-circle" style="background:<?=tc_avatar_color($a->staffid)?>; width:34px; height:34px; font-size:12px;">
                       <?=strtoupper(substr($a->firstname,0,1).substr($a->lastname,0,1))?>
                    </div>
                    <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($a->firstname.' '.$a->lastname)?></div>
                  </div>
                </td>
                <td><span class="tc-pill tc-pill-primary" style="font-size:10px;"><?=htmlspecialchars($a->group_name??'Unassigned')?></span></td>
                <td style="font-weight:700; color:var(--tc-primary);"><?=(int)$a->calls?></td>
                <td style="font-weight:600;"><?=(int)$a->appointments?></td>
                <td style="font-weight:700; color:var(--tc-success);"><?=(int)$a->conversions?></td>
                <td>
                   <div class="tc-prog-bg" style="width:100px; height:8px; margin-bottom:4px;"><div class="tc-prog-fill tc-btn-primary" style="width:<?=$bar_w?>%;"></div></div>
                   <div style="font-size:10px; color:var(--tc-text-muted);"><?=$bar_w?>% relative volume</div>
                </td>
                <td><span class="tc-pill <?=$pill_cls?>"><?=$pct?>%</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ QUICK ACTIONS ══════════════════════════════════════════ -->
      <div class="tc-card" style="padding: 15px;">
        <div style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;">
           <a href="<?=admin_url('telecalling_ld/reports')?>" class="tc-btn tc-btn-outline"><i class="fa fa-line-chart"></i> View Detailed Reports</a>
           <a href="<?=admin_url('telecalling_ld/requests')?>" class="tc-btn tc-btn-outline"><i class="fa fa-hand-paper-o"></i> Lead Requests</a>
           <a href="<?=admin_url('telecalling_ld/settings')?>" class="tc-btn tc-btn-outline"><i class="fa fa-cog"></i> Module Settings</a>
        </div>
      </div>

    </div>
  </div>
</div>

<?php init_tail(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?=module_dir_url('telecalling_ld','assets/js/tc_main.js')?>"></script>
<script>
var TC_DAILY  = <?=json_encode($daily_chart)?>;
var TC_FUNNEL = <?=json_encode($funnel)?>;
var TC_AGENTS = <?=json_encode($agent_stats)?>;
var TC_EMAIL  = <?=json_encode($email_stats)?>;
document.addEventListener('DOMContentLoaded', function() {
  tcInitAdminDashboard(TC_DAILY, TC_FUNNEL, TC_AGENTS, TC_EMAIL);
});
</script>
