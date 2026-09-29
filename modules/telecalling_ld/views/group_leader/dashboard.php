<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <!-- ══ GROUP HEADER ════════════════════════════════════════════ -->
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box" style="background:<?=$group->color?>; color:#fff;"><i class="fa fa-users"></i></div>
          <div>
            <h1 class="tc-h-main"><?=htmlspecialchars($group->name)?> — Group Dashboard</h1>
            <p class="tc-h-sub">Team Performance Overview &bull; <?=$group->member_count ?? 0?> active agents</p>
          </div>
        </div>
        <div class="tc-actions">
           <form method="get" id="grp-filter-form" style="display:inline-flex; gap:10px; align-items:center;">
              <select name="period" onchange="this.form.submit()" class="form-control selectpicker" style="width:140px;">
                <?php foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month'] as $v=>$l): ?>
                <option value="<?=$v?>" <?=$period==$v?'selected':''?>><?=$l?></option>
                <?php endforeach; ?>
              </select>
           </form>
           <span class="tc-pill tc-pill-primary" style="padding: 10px 15px;"><?=date('d M Y')?></span>
        </div>
      </div>

      <!-- ══ TEAM KPI CARDS ══════════════════════════════════════════ -->
      <div class="tc-kpi-grid">
        <div class="tc-kpi teal">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-phone"></i></div>
          </div>
          <div class="tc-kpi-lbl">Team Calls</div>
          <div class="tc-kpi-val"><?=number_format($grp_calls)?></div>
          <div class="tc-kpi-sub">Total this <?=$period?></div>
        </div>

        <div class="tc-kpi green">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-calendar-check-o"></i></div>
          </div>
          <div class="tc-kpi-lbl">Appointments</div>
          <div class="tc-kpi-val"><?=number_format($grp_appts)?></div>
          <?php $appt_rate = $grp_calls>0?round($grp_appts/$grp_calls*100):0; ?>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-green" style="width:<?=$appt_rate?>%"></div></div>
          <div class="tc-kpi-sub">Rate: <?=$appt_rate?>%</div>
        </div>

        <div class="tc-kpi orange">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-star"></i></div>
          </div>
          <div class="tc-kpi-lbl">Conversions</div>
          <div class="tc-kpi-val"><?=number_format($grp_convs)?></div>
          <?php $conv_rate = $grp_calls>0?round($grp_convs/$grp_calls*100):0; ?>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-amber" style="width:<?=$conv_rate?>%"></div></div>
          <div class="tc-kpi-sub">Rate: <?=$conv_rate?>%</div>
        </div>
      </div>

      <!-- ══ CHARTS ══════════════════════════════════════════════════ -->
      <div class="row">
        <div class="col-md-7">
          <div class="tc-card">
            <div class="tc-card-title">Group Activity <small>Last 7 Days</small></div>
            <canvas id="chart-daily" height="250"></canvas>
          </div>
        </div>
        <div class="col-md-5">
          <div class="tc-card">
            <div class="tc-card-title">Agent Comparison <small>Calls by agent</small></div>
            <canvas id="chart-group-agents" height="250"></canvas>
          </div>
        </div>
      </div>

      <!-- ══ AGENT PERFORMANCE ═══════════════════════════════════════ -->
      <div class="tc-card">
        <div class="tc-card-title">Agent Performance Breakdown <small>Team ranking for this <?=$period?></small></div>
        <div class="tc-table-responsive">
          <table class="tc-table">
            <thead>
              <tr>
                <th width="60">Rank</th>
                <th>Agent</th>
                <th>Calls</th>
                <th>Appts</th>
                <th>Conversions</th>
                <th>Activity</th>
                <th width="100">Target</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $max_calls = max(1, max(array_column((array)$agents,'calls')));
              $rank = 1;
              foreach($agents as $a):
                if(isset($a->group_name) && $a->group_name !== $group->name && !empty($a->group_name)) continue;
                $tgt   = tc_target_status($a->staffid,'calls','weekly');
                $pct   = $tgt ? $tgt['pct'] : 0;
                $pill  = $pct>=100?'tc-pill-success':($pct>=60?'tc-pill-warning':'tc-pill-danger');
                $bar_w = round((int)$a->calls / $max_calls * 100);
                $ranking_icon = '';
                if($rank == 1) $ranking_icon = '<i class="fa fa-trophy text-warning"></i> ';
                if($rank == 2) $ranking_icon = '<i class="fa fa-trophy" style="color:#C0C0C0"></i> ';
                if($rank == 3) $ranking_icon = '<i class="fa fa-trophy" style="color:#CD7F32"></i> ';
              ?>
              <tr>
                <td style="font-weight:800; font-size:16px; color:var(--tc-text-muted);"><?=$ranking_icon?><?=$rank?></td>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div class="tc-avatar-circle" style="background:<?=tc_avatar_color($a->staffid)?>; width:34px; height:34px; font-size:12px;">
                      <?=strtoupper(substr($a->firstname,0,1).substr($a->lastname,0,1))?>
                    </div>
                    <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($a->firstname.' '.$a->lastname)?></div>
                  </div>
                </td>
                <td style="font-weight:700; color:var(--tc-primary);"><?=(int)$a->calls?></td>
                <td style="font-weight:600;"><?=(int)$a->appointments?></td>
                <td style="font-weight:600; color:var(--tc-success);"><?=(int)$a->conversions?></td>
                <td>
                  <div class="tc-prog-bg" style="width:100px; height:8px; margin-bottom:4px;"><div class="tc-prog-fill tc-btn-primary" style="width:<?=$bar_w?>%;"></div></div>
                  <div style="font-size:10px; color:var(--tc-text-muted);"><?=$bar_w?>% relative volume</div>
                </td>
                <td><span class="tc-pill <?=$pill?>"><?=$pct?>%</span></td>
              </tr>
              <?php $rank++; endforeach; ?>
            </tbody>
          </table>
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
var TC_AGENTS = <?=json_encode($agents)?>;
document.addEventListener('DOMContentLoaded', function() {
  tcInitGroupDashboard(TC_DAILY, TC_AGENTS);
});
</script>
