<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?=module_dir_url('telecalling_ld','assets/css/tc_style.css')?>">

<div id="wrapper">
  <div class="content tc-content-wrapper">
    <div class="tc-wrap animate-fadeIn">

      <!-- ══ PREMIUM HEADER ══════════════════════════════════════════ -->
      <div class="tc-header">
        <div class="tc-title-area">
          <div class="tc-icon-box" style="background:<?=tc_avatar_color($staff->staffid)?>; color:#fff; font-weight:800; font-size:14px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
             <?=strtoupper(substr($staff->firstname,0,1).substr($staff->lastname,0,1))?>
          </div>
          <div>
            <h1 class="tc-h-main">Welcome back, <?=htmlspecialchars($staff->firstname)?></h1>
            <p class="tc-h-sub">Your personal performance & assigned leads for <?=date('l, d M Y')?></p>
          </div>
        </div>
        <div class="tc-actions">
           <?php if($pending_requests>0): ?>
             <span class="tc-pill tc-pill-warning"><?=$pending_requests?> request pending</span>
           <?php endif; ?>
           <span class="tc-pill tc-pill-primary" style="padding: 10px 15px;"><?=date('d M Y')?></span>
        </div>
      </div>

      <!-- ══ KPI CARDS ═══════════════════════════════════════════════ -->
      <div class="tc-kpi-grid">
        <div class="tc-kpi blue">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-phone"></i></div>
          </div>
          <div class="tc-kpi-lbl">Calls Today</div>
          <div class="tc-kpi-val"><?=$calls_today?></div>
          <div class="tc-kpi-sub">Week: <?=$calls_week?> &bull; Month: <?=$calls_month?></div>
        </div>

        <div class="tc-kpi teal">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-users"></i></div>
          </div>
          <div class="tc-kpi-lbl">Pending Leads</div>
          <div class="tc-kpi-val"><?=count($my_leads)?></div>
          <div class="tc-kpi-sub">Awaiting Action</div>
        </div>

        <div class="tc-kpi green">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-calendar-check-o"></i></div>
          </div>
          <div class="tc-kpi-lbl">Appt Rate (Week)</div>
          <?php $rate = $calls_week>0?round(array_sum(array_map(function($s){ return $s->call_status=='appointment_scheduled'?$s->cnt:0; },(array)$status_breakdown))/$calls_week*100):0; ?>
          <div class="tc-kpi-val"><?=$rate?>%</div>
          <div class="tc-progress-bar"><div class="tc-progress-fill tc-fill-green" style="width:<?=$rate?>%"></div></div>
          <div class="tc-kpi-sub">Targeted Perf</div>
        </div>

        <div class="tc-kpi orange">
          <div class="tc-kpi-accent"></div>
          <div class="tc-kpi-top">
            <div class="tc-kpi-icon"><i class="fa fa-star"></i></div>
          </div>
          <div class="tc-kpi-lbl">Conversions</div>
          <?php $convs = array_sum(array_map(function($s){ return $s->call_status=='converted'?$s->cnt:0; },(array)$status_breakdown)); ?>
          <div class="tc-kpi-val"><?=$convs?></div>
          <div class="tc-kpi-sub">This week</div>
        </div>
      </div>

      <!-- ══ TARGET PROGRESS ══════════════════════════════════════════ -->
      <div class="row">
        <div class="col-md-12">
          <div class="tc-card">
            <div class="tc-card-title">Daily Performance Targets</div>
            <div class="row">
              <?php $targets_data = ['Daily Calls'=>$target_calls,'Daily Appointments'=>$target_appts,'Conversions'=>$target_convs]; 
                foreach($targets_data as $lbl => $t):
                  $pct = $t ? min($t['pct'],100) : 0;
                  $pill_cls = $pct>=100?'tc-pill-success':($pct>=60?'tc-pill-warning':'tc-pill-danger');
              ?>
              <div class="col-md-4">
                <div style="background:var(--tc-bg); padding:20px; border-radius:12px; margin-bottom:10px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <span style="font-weight:700; color:var(--tc-text-main);"><?=$lbl?></span>
                    <span class="tc-pill <?=$pill_cls?>"><?=$pct?>%</span>
                  </div>
                  <?php if($t): ?>
                    <div class="tc-prog-bg" style="height:12px; margin-bottom:10px;"><div class="tc-prog-fill tc-btn-primary" style="width:<?=$pct?>%;"></div></div>
                    <div style="font-size:12px; color:var(--tc-text-muted);">Reached <strong><?=$t['actual']?></strong> out of <strong><?=$t['target']?></strong></div>
                  <?php else: ?>
                    <div style="font-size:12px; color:var(--tc-text-muted); font-style:italic;">No target set by admin</div>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ CHARTS ══════════════════════════════════════════════════ -->
      <div class="row">
        <div class="col-md-8">
          <div class="tc-card">
            <div class="tc-card-title">My Activity <small>Last 7 Days</small></div>
            <canvas id="chart-daily" height="250"></canvas>
          </div>
        </div>
        <div class="col-md-4">
          <div class="tc-card">
            <div class="tc-card-title">Call Outcomes</div>
            <canvas id="chart-status" height="250"></canvas>
          </div>
        </div>
      </div>

      <!-- ══ MY LEADS ════════════════════════════════════════════════ -->
      <div class="tc-card">
        <div class="tc-card-title">My Recent Leads <small><?=count($my_leads)?> active leads</small></div>
        <div class="tc-table-responsive">
          <table class="tc-table">
            <thead>
              <tr>
                <th>Lead</th>
                <th>Company/Phone</th>
                <th>Email</th>
                <th>Last Status</th>
                <th>Last Called</th>
                <th width="120">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach(array_slice((array)$my_leads,0,10) as $l): ?>
              <tr>
                <td>
                  <div style="font-weight:700; color:var(--tc-text-main);"><?=htmlspecialchars($l->name)?></div>
                  <div style="font-size:11px; color:var(--tc-text-muted);">ID: #<?=$l->id?> &bull; Assigned: <?=$l->assigned_at?date('d M',strtotime($l->assigned_at)):'—'?></div>
                </td>
                <td>
                  <div style="font-weight:600;"><?=htmlspecialchars($l->company??'—')?></div>
                  <div style="font-size:11px; color:var(--tc-text-muted);"><?=htmlspecialchars($l->phonenumber??'—')?></div>
                </td>
                <td style="font-size:12px; color:var(--tc-text-muted);"><?=htmlspecialchars($l->email)?></td>
                <td>
                  <?php if($l->last_status): ?>
                    <?php
                    $st_map = ['converted'=>'st-converted','appointment_scheduled'=>'st-appointed','interested'=>'st-interested','not_answered'=>'st-no-answer','callback_later'=>'st-callback','not_interested'=>'st-rejected'];
                    $st_cls = $st_map[$l->last_status] ?? 'st-no-answer';
                    ?>
                    <span class="tc-status <?=$st_cls?>"><?=ucwords(str_replace('_',' ',$l->last_status))?></span>
                  <?php else: ?>
                    <span style="color:var(--tc-text-muted); font-size:12px;">Not called yet</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:11px; color:var(--tc-text-muted);"><?=$l->last_called?date('d M H:i',strtotime($l->last_called)):'Never'?></td>
                <td>
                  <div style="display:flex; gap:5px;">
                    <button class="tc-btn tc-btn-primary btn-xs tc-log-btn" data-lead="<?=$l->id?>" data-name="<?=htmlspecialchars($l->name)?>" style="padding:5px 8px;"><i class="fa fa-phone"></i> Log</button>
                    <a href="<?=admin_url('leads/index/'.$l->id)?>" class="tc-btn tc-btn-outline btn-xs" style="padding:5px 8px;"><i class="fa fa-eye"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($my_leads)): ?>
                <tr><td colspan="6" class="text-center" style="padding:50px; color:var(--tc-text-muted);">No leads assigned to you.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <?php if(count($my_leads)>10): ?>
            <div class="text-center" style="padding:15px; border-top:1px solid var(--tc-border);">
              <a href="<?=admin_url('telecalling_ld/my_leads')?>" style="font-weight:700; color:var(--tc-primary);">View All Leads (<?=count($my_leads)?>) &rarr;</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Log Call Modal -->
<div id="tc-call-modal" class="tc-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
  <div class="tc-card" style="width:100%; max-width:500px; margin:20px; position:relative;">
    <button style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:24px; cursor:pointer;" onclick="document.getElementById('tc-call-modal').style.display='none'">&times;</button>
    <div class="tc-card-title">Log Call — <span id="tc-modal-name" style="color:var(--tc-primary)"></span></div>
    <form method="post" action="<?=admin_url('telecalling_ld/log_call')?>">
      <?=form_hidden($this->security->get_csrf_token_name(),$this->security->get_csrf_hash())?>
      <input type="hidden" name="lead_id" id="tc-modal-lead-id">
      <div class="form-group">
        <label>Call Outcome *</label>
        <select name="tc_call_status" class="form-control selectpicker" required>
          <option value="not_answered">Not Answered</option>
          <option value="callback_later">Callback Later</option>
          <option value="interested">Interested</option>
          <option value="not_interested">Not Interested</option>
          <option value="appointment_scheduled">Appointment Scheduled</option>
          <option value="converted">Converted</option>
        </select>
      </div>
      <div class="form-group">
        <label>Notes</label>
        <textarea name="tc_notes" class="form-control" rows="4" placeholder="Briefly describe the call conversation..."></textarea>
      </div>
      <div style="margin-top: 20px; display:flex; gap:10px;">
        <button type="submit" class="tc-btn tc-btn-primary" style="flex:1; justify-content:center;"><i class="fa fa-save"></i> Save Log</button>
        <button type="button" class="tc-btn tc-btn-outline" onclick="document.getElementById('tc-call-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php init_tail(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?=module_dir_url('telecalling_ld','assets/js/tc_main.js')?>"></script>
<script>
var TC_DAILY  = <?=json_encode($daily_chart)?>;
var TC_STATUS = <?=json_encode($status_breakdown)?>;
document.addEventListener('DOMContentLoaded', function() {
  tcInitAgentDashboard(TC_DAILY, TC_STATUS);
});
document.querySelectorAll('.tc-log-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.getElementById('tc-modal-lead-id').value = this.dataset.lead;
    document.getElementById('tc-modal-name').textContent = this.dataset.name;
    document.getElementById('tc-call-modal').style.display = 'flex';
  });
});
</script>
