<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">
      
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box"><i class="fa fa-line-chart"></i></div>
          <div>
            <h1 class="tc-h-main">Reports &amp; Analytics</h1>
            <p class="tc-h-sub">Deep dive into call performance, lead conversion, and agent activity</p>
          </div>
        </div>
        <div class="tc-actions">
           <form method="get" class="tc-flex-row">
              <div class="tc-select-small">
                  <select name="agent_id" class="form-control selectpicker tc-select-small" data-live-search="true" data-none-selected-text="All Agents">
                      <option value="">All Agents</option>
                      <?php foreach($agents as $s): ?>
                      <option value="<?=$s->staffid?>" <?=(isset($filters['agent_id']) && $filters['agent_id']==$s->staffid)?'selected':''?>><?=htmlspecialchars($s->firstname.' '.$s->lastname)?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="input-group date tc-date-group">
                  <input type="text" name="from" class="form-control datepicker" value="<?=$from?>" placeholder="From">
                  <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
              </div>
              <div class="input-group date tc-date-group">
                  <input type="text" name="to" class="form-control datepicker" value="<?=$to?>" placeholder="To">
                  <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
              </div>
              <button type="submit" class="tc-btn tc-btn-primary tc-btn-filter"><i class="fa fa-filter"></i> Filter</button>
           </form>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <div class="tc-kpi teal">
             <div class="tc-kpi-accent"></div>
             <div class="tc-kpi-lbl">Open Rate</div>
             <div class="tc-kpi-val"><?=round($email_stats->open_rate ?? 0, 1)?>%</div>
             <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-green" style="width:<?=$email_stats->open_rate ?? 0?>%"></div></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="tc-kpi blue">
             <div class="tc-kpi-accent"></div>
             <div class="tc-kpi-lbl">Click Rate</div>
             <div class="tc-kpi-val"><?=round($email_stats->click_rate ?? 0, 1)?>%</div>
             <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-blue" style="width:<?=$email_stats->click_rate ?? 0?>%"></div></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="tc-kpi orange">
             <div class="tc-kpi-accent"></div>
             <div class="tc-kpi-lbl">Bounce Rate</div>
             <div class="tc-kpi-val"><?=round($email_stats->bounce_rate ?? 0, 1)?>%</div>
             <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-amber" style="width:<?=$email_stats->bounce_rate ?? 0?>%"></div></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="tc-kpi purple">
             <div class="tc-kpi-accent"></div>
             <div class="tc-kpi-lbl">Reply Rate</div>
             <div class="tc-kpi-val"><?=round($email_stats->reply_rate ?? 0, 1)?>%</div>
             <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-purple" style="width:<?=$email_stats->reply_rate ?? 0?>%"></div></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-7">
          <div class="tc-card">
            <div class="tc-card-title">Agent Comparison <small>Performance metrics by staff</small></div>
            <canvas id="chart-agents-performance" height="300"></canvas>
          </div>
        </div>
        <div class="col-md-5">
           <div class="tc-card">
              <div class="tc-card-title">Call Outcome Funnel</div>
              <canvas id="chart-funnel" height="300"></canvas>
           </div>
        </div>
      </div>

      <div class="tc-card">
        <div class="tc-card-title">Detailed Call Log <small>Historical activity</small></div>
        <div class="tc-table-responsive">
          <table class="tc-table">
            <thead><tr><th>Date</th><th>Agent</th><th>Lead</th><th>Outcome</th><th>Notes</th></tr></thead>
            <tbody>
              <?php foreach($logs as $log): ?>
              <tr>
                <td style="font-size:11px; color:var(--tc-text-muted);"><?=date('d M Y H:i', strtotime($log->created_at))?></td>
                <td style="font-weight:600;"><?=htmlspecialchars($log->agent_name)?></td>
                <td style="font-weight:600; color:var(--tc-primary);"><?=htmlspecialchars($log->lead_name)?></td>
                <td>
                   <?php
                   $map = ['converted'=>'st-converted','appointment_scheduled'=>'st-appointed','interested'=>'st-interested','not_answered'=>'st-no-answer','callback_later'=>'st-callback','not_interested'=>'st-rejected'];
                   $cls = $map[$log->call_status] ?? 'st-no-answer';
                   ?>
                   <span class="tc-status <?=$cls?>"><?=ucwords(str_replace('_',' ',$log->call_status))?></span>
                </td>
                <td style="font-size:12px; color:var(--tc-text-muted); max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?=htmlspecialchars($log->notes)?>">
                   <?=htmlspecialchars($log->notes)?>
                </td>
              </tr>
              <?php endforeach; ?>
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
var TC_AGENTS = <?=json_encode($agent_stats)?>;
var TC_FUNNEL = <?=json_encode($funnel)?>;
document.addEventListener('DOMContentLoaded',function(){ 
  tcInitReports(TC_AGENTS, TC_FUNNEL); 
});
</script>
