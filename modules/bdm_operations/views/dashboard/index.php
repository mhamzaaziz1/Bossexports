<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .bdm-purple { background-color:#6a1b9a !important; color:#fff !important; }
    .bdm-teal   { background-color:#009688 !important; color:#fff !important; }
    .bdm-orange { background-color:#ff7043 !important; color:#fff !important; }
    .bdm-blue   { background-color:#1565c0 !important; color:#fff !important; }
    .widget-title  { font-weight:600; font-size:12px; margin-bottom:4px; color:#666; text-transform:uppercase; letter-spacing:.5px; }
    .widget-val    { font-size:22px; font-weight:700; line-height:1.2; }
    .widget-target { font-size:11px; color:#888; margin-top:3px; }
    .card-bdm      { border-radius:8px; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,.08); border:1px solid #ececec; transition:transform .15s ease, box-shadow .15s ease; background:#fff; }
    .card-bdm:hover{ transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
    .kpi-progress  { height:6px; border-radius:3px; margin-top:8px; }
    .split-view-panel { height:380px; overflow-y:auto; }
    .funnel-bar      { height:32px; background:#1565c0; color:#fff; border-radius:4px; padding:6px 12px; margin-bottom:6px; font-size:13px; display:flex; align-items:center; justify-content:space-between; }
    .funnel-bar small { opacity:.85; }
    .channel-pill    { display:inline-block; padding:3px 8px; border-radius:12px; font-size:11px; color:#fff; font-weight:600; }
    .channel-pill-Direct      { background:#1565c0; }
    .channel-pill-Telecalling { background:#00897b; }
    .channel-pill-Email       { background:#6a1b9a; }
    .channel-pill-Social      { background:#ef6c00; }
    .channel-pill-SEO         { background:#558b2f; }
</style>
<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-9">
        <h4 class="no-mtop"><i class="fa fa-handshake-o"></i> <?php echo _l('bdm_command_center'); ?></h4>
        <p class="text-muted no-mbot">IT &amp; Fiber Optic Infrastructure · UAE · 4 Models · 5 Channels · 10-Step Pipeline</p>
      </div>
      <div class="col-md-3 text-right">
        <a href="<?php echo admin_url('bdm_operations/pipeline'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-columns"></i> <?php echo _l('bdm_pipeline'); ?></a>
        <a href="<?php echo admin_url('bdm_operations/kpi_targets'); ?>" class="btn btn-default btn-sm"><i class="fa fa-bullseye"></i> <?php echo _l('bdm_kpi_targets'); ?></a>
      </div>
    </div>
    <hr class="hr-panel-heading" />

    <!-- ============== KPI STRIP (12 + Pipeline Value) ============== -->
    <div class="row">
      <?php
      $kpis = [
        ['k' => 'leads',           'l' => _l('bdm_kpi_leads'),            'val' => $stats['kpi_leads'],          'color' => 'text-primary'],
        ['k' => 'visits',          'l' => _l('bdm_kpi_visits'),           'val' => $stats['kpi_visits'],         'color' => 'text-info'],
        ['k' => 'calls',           'l' => _l('bdm_kpi_calls'),            'val' => $stats['kpi_calls'],          'color' => 'text-success'],
        ['k' => 'proposals',       'l' => _l('bdm_kpi_proposals'),        'val' => $stats['kpi_proposals'],      'color' => 'text-warning'],
        ['k' => 'deals',           'l' => _l('bdm_kpi_deals'),            'val' => $stats['kpi_deals'],          'color' => 'text-danger'],
        ['k' => 'revenue_aed',     'l' => _l('bdm_kpi_revenue'),          'val' => 'AED ' . $stats['kpi_revenue_fmt'], 'color' => 'text-success'],
        ['k' => 'partner_regs',    'l' => _l('bdm_kpi_partners'),         'val' => $stats['kpi_partners'],       'color' => 'text-primary'],
        ['k' => 'client_approvals','l' => _l('bdm_kpi_client_approvals'), 'val' => $stats['approved_registrations'], 'color' => 'text-info'],
        ['k' => 'conversion_rate', 'l' => _l('bdm_kpi_conversion'),       'val' => $stats['conversion_rate'] . '%', 'color' => 'text-success'],
        ['k' => 'email_open_rate', 'l' => _l('bdm_kpi_email_open'),       'val' => $stats['kpi_email_open_rate'] . '%', 'color' => 'text-warning'],
        ['k' => 'linkedin_growth', 'l' => _l('bdm_kpi_linkedin_growth'),  'val' => $stats['kpi_linkedin_growth'], 'color' => 'text-info'],
        ['k' => 'pipeline_value',  'l' => _l('bdm_kpi_pipeline_value'),   'val' => 'AED ' . $stats['pipeline_value_fmt'], 'color' => 'text-primary'],
      ];
      foreach ($kpis as $kpi):
          $progress = isset($kpi_progress[$kpi['k']]) ? $kpi_progress[$kpi['k']] : null;
      ?>
      <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="panel_s card-bdm">
          <div class="panel-body" style="padding:14px 14px 10px;">
            <div class="widget-title"><?php echo $kpi['l']; ?></div>
            <div class="widget-val <?php echo $kpi['color']; ?>"><?php echo $kpi['val']; ?></div>
            <?php if ($progress): ?>
            <div class="widget-target">
              Target: <?php echo $kpi['k'] === 'revenue_aed'
                  ? 'AED ' . number_format($progress['target'])
                  : number_format($progress['target']); ?>
            </div>
            <div class="progress kpi-progress">
              <div class="progress-bar progress-bar-<?php echo $progress['percent'] >= 100 ? 'success' : ($progress['percent'] >= 60 ? 'info' : 'warning'); ?>"
                   role="progressbar" style="width: <?php echo $progress['percent']; ?>%"></div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ============== CHARTS ROW ============== -->
    <div class="row">
      <div class="col-md-4">
        <div class="panel_s card-bdm">
          <div class="panel-body">
            <h4 class="no-mtop"><?php echo _l('bdm_pipeline_status'); ?></h4>
            <div style="height:240px; position:relative;">
              <canvas id="pipeline_donut"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel_s card-bdm">
          <div class="panel-body">
            <h4 class="no-mtop"><?php echo _l('bdm_pipeline_step_dist'); ?></h4>
            <div style="height:240px; position:relative;">
              <canvas id="pipeline_steps_bar"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel_s card-bdm">
          <div class="panel-body">
            <h4 class="no-mtop"><?php echo _l('bdm_segment_dist'); ?></h4>
            <div style="height:240px; position:relative;">
              <canvas id="segment_pie"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="panel_s card-bdm">
          <div class="panel-body">
            <h4 class="no-mtop"><?php echo _l('bdm_revenue_trend'); ?></h4>
            <div style="height:240px; position:relative;">
              <canvas id="revenue_trend"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel_s card-bdm">
          <div class="panel-body" style="height:280px; overflow-y:auto;">
            <h4 class="no-mtop"><?php echo _l('bdm_channel_performance'); ?></h4>
            <?php if (empty($channel_summary)): ?>
              <p class="text-muted small">No campaigns yet. <a href="<?php echo admin_url('bdm_operations/campaigns'); ?>">Plan one →</a></p>
            <?php else: ?>
              <table class="table table-condensed">
                <thead>
                  <tr><th>Channel</th><th class="text-right">Leads</th><th class="text-right">Deals</th><th class="text-right">Rev (AED)</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($channel_summary as $row):
                    $ch = $row['channel'];
                    $cls = 'channel-pill channel-pill-' . preg_replace('/[^A-Za-z]/', '', $ch); ?>
                  <tr>
                    <td><span class="<?php echo $cls; ?>"><?php echo htmlentities($ch); ?></span></td>
                    <td class="text-right"><?php echo (int) $row['leads']; ?></td>
                    <td class="text-right"><?php echo (int) $row['deals']; ?></td>
                    <td class="text-right"><?php echo number_format((float) $row['revenue'], 0); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ============== PERFORMANCE LEADERBOARD ============== -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s card-bdm">
          <div class="panel-body" style="max-height:340px; overflow-y:auto;">
            <h4 class="no-mtop"><?php echo _l('bdm_performance_leaderboard'); ?></h4>
            <table class="table dt-table" data-order-col="6" data-order-type="desc">
              <thead>
                <tr>
                  <th><?php echo _l('bdm_agent'); ?></th>
                  <th>Visits</th>
                  <th>Calls</th>
                  <th><?php echo _l('vr_registrations'); ?></th>
                  <th><?php echo _l('bdm_approved_vrs'); ?></th>
                  <th><?php echo _l('distributors'); ?></th>
                  <th>Won (AED)</th>
                  <th><?php echo _l('bdm_performance'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($performance as $perf):
                    $score = ($perf['approved'] * 5) + ($perf['registrations'] * 2) + ($perf['distributors'] * 1) + (intval($perf['won_value']) > 0 ? 10 : 0);
                ?>
                <tr>
                  <td><strong><?php echo htmlentities($perf['firstname'] . ' ' . $perf['lastname']); ?></strong></td>
                  <td><span class="label label-info"><?php echo $perf['visits']; ?></span></td>
                  <td><span class="label label-default"><?php echo $perf['calls']; ?></span></td>
                  <td><span class="label label-primary"><?php echo $perf['registrations']; ?></span></td>
                  <td><span class="label label-success"><?php echo $perf['approved']; ?></span></td>
                  <td><span class="label label-default"><?php echo $perf['distributors']; ?></span></td>
                  <td><strong><?php echo number_format((float) $perf['won_value'], 0); ?></strong></td>
                  <td>
                    <div class="progress no-margin" style="height:14px;">
                      <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo min(100, $score * 2); ?>%"><?php echo $score; ?></div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ============== WORKSPACE SPLIT ============== -->
    <h4 class="mtop20"><?php echo _l('bdm_workspace'); ?></h4>
    <div class="row">
      <div class="col-md-6">
        <div class="panel_s card-bdm">
          <div class="panel-heading bdm-purple"><h4 class="panel-title"><i class="fa fa-search"></i> <?php echo _l('bdm_my_brand_research'); ?></h4></div>
          <div class="panel-body split-view-panel">
            <?php if (empty($brands)): ?>
              <p class="text-muted"><?php echo _l('no_records_found'); ?></p>
            <?php endif; ?>
            <div class="list-group">
              <?php foreach ($brands as $brand):
                  $country = $brand['country_id'] ? get_country($brand['country_id']) : null; ?>
              <div class="list-group-item" style="border-left:4px solid #6a1b9a;">
                <div class="row">
                  <div class="col-md-8">
                    <a href="<?php echo admin_url('bdm_operations/brands'); ?>" class="bold"><?php echo htmlentities($brand['name']); ?></a>
                    <br><small class="text-muted"><?php echo $country ? $country->short_name : '-'; ?> · <?php echo htmlentities($brand['category'] ?? ''); ?></small>
                  </div>
                  <div class="col-md-4 text-right"><span class="text-muted small"><?php echo _d($brand['created_at']); ?></span></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel_s card-bdm">
          <div class="panel-heading bdm-teal"><h4 class="panel-title"><i class="fa fa-rocket"></i> <?php echo _l('bdm_my_active_vrs'); ?></h4></div>
          <div class="panel-body split-view-panel">
            <?php if (empty($registrations)): ?>
              <p class="text-muted"><?php echo _l('no_records_found'); ?></p>
            <?php endif; ?>
            <div class="list-group">
              <?php foreach ($registrations as $reg):
                  $c   = $this->bdm_operations_model->get_master_companies($reg['company_id']);
                  $cls = bdm_status_label_class($reg['status']);
              ?>
              <div class="list-group-item" style="border-left:4px solid <?php echo bdm_pipeline_color($reg['status']); ?>;">
                <div class="row">
                  <div class="col-md-7">
                    <a href="<?php echo admin_url('bdm_operations/vr_registrations'); ?>" class="bold"><?php echo $c ? htmlentities($c->company_name) : 'N/A'; ?></a>
                    <br><small class="text-muted">Step <?php echo (int) $reg['pipeline_step']; ?> · <?php echo htmlentities($reg['segment'] ?? '—'); ?> · <?php echo get_staff_full_name($reg['assigned_bdm']); ?></small>
                  </div>
                  <div class="col-md-5 text-right">
                    <span class="label label-<?php echo $cls; ?>"><?php echo isset($pipeline_stages[$reg['status']]) ? $pipeline_stages[$reg['status']] : $reg['status']; ?></span>
                    <br><small class="text-muted">AED <?php echo number_format((float) ($reg['expected_value'] ?? 0), 0); ?></small>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php init_tail(); ?>

<script>
$(function () {
    /* Pipeline Donut (statuses) */
    var ctx = document.getElementById('pipeline_donut').getContext('2d');
    var pipelineData = <?php echo json_encode($pipeline); ?>;
    var stageMapping = <?php echo json_encode($pipeline_stages); ?>;
    var pipeColors   = ['#2196f3','#03a9f4','#00bcd4','#9c27b0','#673ab7','#ff9800','#ff5722','#4caf50','#f44336'];
    var labels1 = [], data1 = [];
    pipelineData.forEach(function(item){
        labels1.push(stageMapping[item.status] || item.status);
        data1.push(item.total);
    });
    new Chart(ctx, {
        type: 'doughnut',
        data: { labels: labels1, datasets: [{ data: data1, backgroundColor: pipeColors }] },
        options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right', labels: { fontSize: 10 } } }
    });

    /* 10-Step Pipeline Bar */
    var ctx2 = document.getElementById('pipeline_steps_bar').getContext('2d');
    var stepData = <?php echo json_encode($pipeline_steps); ?>;
    var stepMap  = <?php echo json_encode($pipeline_step_map); ?>;
    var labels2  = Object.keys(stepMap).map(function(k){ return 'S' + k; });
    var data2    = Object.keys(stepMap).map(function(k){
        var found = stepData.find(function(r){ return parseInt(r.pipeline_step) === parseInt(k); });
        return found ? parseInt(found.total) : 0;
    });
    new Chart(ctx2, {
        type: 'bar',
        data: { labels: labels2, datasets: [{ label: 'Registrations', data: data2, backgroundColor: '#1565c0' }] },
        options: { responsive: true, maintainAspectRatio: false, legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
    });

    /* Segment Pie */
    var ctx3 = document.getElementById('segment_pie').getContext('2d');
    var segData = <?php echo json_encode($segments); ?>;
    var segLabels = segData.map(function(r){ return r.segment || 'Unspecified'; });
    var segValues = segData.map(function(r){ return parseInt(r.total); });
    if (segLabels.length === 0) { segLabels = ['No segments']; segValues = [1]; }
    new Chart(ctx3, {
        type: 'pie',
        data: { labels: segLabels, datasets: [{ data: segValues, backgroundColor: ['#1565c0','#00897b','#6a1b9a','#ef6c00','#558b2f','#c62828','#607d8b'] }] },
        options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right', labels: { fontSize: 10 } } }
    });

    /* Revenue Trend Line */
    var ctx4 = document.getElementById('revenue_trend').getContext('2d');
    var trend = <?php echo json_encode($revenue_trend); ?>;
    new Chart(ctx4, {
        type: 'line',
        data: {
            labels: trend.map(function(r){ return r.period; }),
            datasets: [{
                label: 'Revenue (AED)',
                data: trend.map(function(r){ return parseFloat(r.revenue); }),
                fill: true,
                backgroundColor: 'rgba(76,175,80,0.15)',
                borderColor: '#4caf50',
                borderWidth: 2,
                pointRadius: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, legend: { display: false } }
    });
});
</script>
