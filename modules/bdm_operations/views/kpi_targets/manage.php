<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <h4 class="no-mtop"><?php echo _l('bdm_kpi_targets'); ?> · <?php echo _l('bdm_target_vs_actual'); ?></h4>
    <p class="text-muted small">Define monthly KPI targets for the BDM team. Defaults match the Advanced Planning Document.</p>
    <hr class="hr-panel-heading" />

    <!-- Period switcher -->
    <form method="get" class="form-inline" style="margin-bottom:14px;">
      <label><?php echo _l('bdm_year'); ?></label>
      <select name="year" class="form-control" onchange="this.form.submit()">
        <?php for ($y = (int) date('Y') - 2; $y <= (int) date('Y') + 1; $y++): ?>
          <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>
      <label class="mleft10"><?php echo _l('bdm_month'); ?></label>
      <select name="month" class="form-control" onchange="this.form.submit()">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
        <?php endfor; ?>
      </select>
    </form>

    <?php echo form_open(admin_url('bdm_operations/kpi_targets')); ?>
      <input type="hidden" name="year"  value="<?php echo $year; ?>">
      <input type="hidden" name="month" value="<?php echo $month; ?>">

      <div class="panel_s">
        <div class="panel-body">
          <table class="table">
            <thead>
              <tr>
                <th>Metric</th>
                <th><?php echo _l('bdm_target'); ?></th>
                <th><?php echo _l('bdm_actual'); ?></th>
                <th>Progress</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $defs = bdm_kpi_metrics_definition();
              $existing = [];
              foreach ($targets as $t) { $existing[$t['metric_key']] = $t; }
              foreach ($defs as $key => $meta):
                  $target_val = $existing[$key]['target_value'] ?? $meta['default'];
                  $actual_val = $progress[$key]['actual'] ?? 0;
                  $pct        = $progress[$key]['percent'] ?? 0;
              ?>
              <tr>
                <td><strong><?php echo $meta['label']; ?></strong></td>
                <td>
                  <input type="number" step="0.01" name="targets[<?php echo $key; ?>]" value="<?php echo $target_val; ?>" class="form-control" style="max-width:160px;">
                </td>
                <td><?php echo is_numeric($actual_val) ? number_format((float) $actual_val, $key === 'revenue_aed' ? 2 : 0) : $actual_val; ?></td>
                <td style="min-width:200px;">
                  <div class="progress" style="margin:0; height:18px;">
                    <div class="progress-bar progress-bar-<?php echo $pct >= 100 ? 'success' : ($pct >= 60 ? 'info' : 'warning'); ?>"
                         role="progressbar" style="width: <?php echo $pct; ?>%"><?php echo $pct; ?>%</div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="text-right">
            <button type="submit" class="btn btn-info"><i class="fa fa-save"></i> Save Targets</button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>
  </div>
</div>
<?php init_tail(); ?>
