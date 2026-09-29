<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">

    <h4 class="no-mtop"><?php echo _l('bdm_campaigns'); ?> · 5-Channel Marketing</h4>
    <p class="text-muted small">Direct · Telecalling · Email · Social · SEO — track recipients, opens, clicks, leads, deals and revenue per channel.</p>
    <hr class="hr-panel-heading" />

    <!-- Channel summary cards -->
    <div class="row">
      <?php
      $channels = bdm_marketing_channels();
      $colors   = ['Direct' => '#1565c0', 'Telecalling' => '#00897b', 'Email' => '#6a1b9a', 'Social' => '#ef6c00', 'SEO' => '#558b2f'];
      $by_channel = [];
      foreach ($summary as $row) { $by_channel[$row['channel']] = $row; }
      foreach ($channels as $ckey => $clbl):
          $row = $by_channel[$ckey] ?? ['campaigns' => 0, 'leads' => 0, 'deals' => 0, 'revenue' => 0];
      ?>
      <div class="col-md-2 col-sm-4">
        <div class="panel_s" style="border-top:4px solid <?php echo $colors[$ckey] ?? '#999'; ?>; border-radius:6px; margin-bottom:10px;">
          <div class="panel-body" style="padding:12px;">
            <div style="font-size:11px; color:#666; text-transform:uppercase;"><?php echo $clbl; ?></div>
            <div style="font-size:18px; font-weight:700;"><?php echo (int) $row['campaigns']; ?> campaigns</div>
            <div style="font-size:12px; color:#444;">Leads: <strong><?php echo (int) ($row['leads'] ?? 0); ?></strong></div>
            <div style="font-size:12px; color:#444;">Deals: <strong><?php echo (int) ($row['deals'] ?? 0); ?></strong></div>
            <div style="font-size:12px; color:#1b5e20;">AED <?php echo number_format((float) ($row['revenue'] ?? 0), 0); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="panel_s">
      <div class="panel-body">
        <div class="_buttons">
          <?php if (has_permission('bdm_operations', '', 'create')): ?>
          <a href="#" class="btn btn-info pull-left display-block" onclick="new_campaign(); return false;">
            <i class="fa fa-plus"></i> <?php echo _l('new_campaign'); ?>
          </a>
          <?php endif; ?>
        </div>
        <div class="clearfix"></div>
        <hr class="hr-panel-heading" />
        <table class="table dt-table" data-order-col="0" data-order-type="desc">
          <thead>
            <tr>
              <th>ID</th>
              <th><?php echo _l('bdm_campaign_name'); ?></th>
              <th><?php echo _l('bdm_campaign_channel'); ?></th>
              <th><?php echo _l('status'); ?></th>
              <th>Period</th>
              <th><?php echo _l('bdm_campaign_recipients'); ?></th>
              <th><?php echo _l('bdm_campaign_opens'); ?></th>
              <th>Open %</th>
              <th><?php echo _l('bdm_campaign_leads'); ?></th>
              <th><?php echo _l('bdm_campaign_deals'); ?></th>
              <th><?php echo _l('bdm_campaign_revenue'); ?></th>
              <th><?php echo _l('options'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($campaigns as $c):
                $delivered = max(1, (int) $c['delivered']);
                $openpct = $delivered > 0 ? round(((int) $c['opens'] / $delivered) * 100, 1) : 0;
            ?>
            <tr>
              <td><?php echo $c['id']; ?></td>
              <td><strong><?php echo htmlentities($c['name']); ?></strong></td>
              <td><span class="label label-info"><?php echo htmlentities($c['channel']); ?></span></td>
              <td><span class="label label-default"><?php echo htmlentities($c['status']); ?></span></td>
              <td><small><?php echo $c['start_date'] ? _d($c['start_date']) : '—'; ?> → <?php echo $c['end_date'] ? _d($c['end_date']) : '—'; ?></small></td>
              <td><?php echo (int) $c['recipients']; ?></td>
              <td><?php echo (int) $c['opens']; ?></td>
              <td><?php echo $openpct; ?>%</td>
              <td><?php echo (int) $c['leads_generated']; ?></td>
              <td><?php echo (int) $c['deals_closed']; ?></td>
              <td>AED <?php echo number_format((float) $c['revenue_generated'], 2); ?></td>
              <td>
                <a href="#" class="btn btn-default btn-icon" onclick="edit_campaign(<?php echo $c['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                <a href="<?php echo admin_url('bdm_operations/delete_campaign/' . $c['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="campaign_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('bdm_operations/campaigns'), ['id' => 'campaign_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('new_campaign'); ?></span>
          <span class="edit-title"><?php echo _l('edit_campaign'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('name', 'bdm_campaign_name'); ?>
            <?php $cha = []; foreach (bdm_marketing_channels() as $k => $v) $cha[] = ['id' => $k, 'name' => $v];
                  echo render_select('channel', $cha, ['id', 'name'], 'bdm_campaign_channel'); ?>
            <?php echo render_select('status', [
                ['id' => 'Planned',   'name' => 'Planned'],
                ['id' => 'Live',      'name' => 'Live'],
                ['id' => 'Completed', 'name' => 'Completed'],
                ['id' => 'Paused',    'name' => 'Paused'],
            ], ['id', 'name'], 'status', 'Planned'); ?>
            <?php echo render_date_input('start_date', 'Start Date'); ?>
            <?php echo render_date_input('end_date', 'End Date'); ?>
            <?php echo render_input('budget',      'bdm_campaign_budget', 0, 'number', ['step' => '0.01']); ?>
            <?php echo render_input('cost_actual', 'bdm_campaign_cost',   0, 'number', ['step' => '0.01']); ?>
            <?php echo render_textarea('target_audience', 'bdm_campaign_audience'); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_input('recipients',        'bdm_campaign_recipients', 0, 'number'); ?>
            <?php echo render_input('delivered',         'Delivered',                0, 'number'); ?>
            <?php echo render_input('opens',             'bdm_campaign_opens',       0, 'number'); ?>
            <?php echo render_input('clicks',            'bdm_campaign_clicks',      0, 'number'); ?>
            <?php echo render_input('replies',           'Replies',                  0, 'number'); ?>
            <?php echo render_input('leads_generated',   'bdm_campaign_leads',       0, 'number'); ?>
            <?php echo render_input('meetings_booked',   'bdm_campaign_meetings',    0, 'number'); ?>
            <?php echo render_input('deals_closed',      'bdm_campaign_deals',       0, 'number'); ?>
            <?php echo render_input('revenue_generated', 'bdm_campaign_revenue',     0, 'number', ['step' => '0.01']); ?>
            <?php echo render_textarea('notes', 'bdm_notes'); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>

<?php init_tail(); ?>
<script>
function new_campaign() {
    $('#campaign_form')[0].reset();
    $('#campaign_form').attr('action', '<?php echo admin_url('bdm_operations/campaigns'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#campaign_form select').selectpicker('refresh');
    $('#campaign_modal').modal('show');
}
function edit_campaign(id) {
    $.get('<?php echo admin_url('bdm_operations/get_campaign_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#campaign_form').attr('action', '<?php echo admin_url('bdm_operations/campaigns/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#campaign_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#campaign_modal').modal('show');
    });
}
</script>
