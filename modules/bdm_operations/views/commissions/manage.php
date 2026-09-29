<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">

    <div class="row">
      <div class="col-md-9">
        <h4 class="no-mtop"><?php echo _l('bdm_commissions'); ?></h4>
        <p class="text-muted small">Partner commission ledger — accruals on invoice payment, paid-out tracking, and per-partner totals.</p>
      </div>
      <div class="col-md-3 text-right">
        <a href="<?php echo admin_url('bdm_operations/partnerships'); ?>" class="btn btn-default btn-sm"><i class="fa fa-handshake-o"></i> <?php echo _l('bdm_partnerships'); ?></a>
      </div>
    </div>
    <hr class="hr-panel-heading" />

    <div class="panel_s">
      <div class="panel-body">
        <div class="_buttons">
          <?php if (has_permission('bdm_operations', '', 'create')): ?>
          <a href="#" class="btn btn-info pull-left display-block" onclick="new_commission(); return false;">
            <i class="fa fa-plus"></i> Record Commission
          </a>
          <?php endif; ?>
        </div>
        <div class="clearfix"></div>
        <hr class="hr-panel-heading" />

        <table class="table dt-table" data-order-col="0" data-order-type="desc">
          <thead>
            <tr>
              <th>ID</th>
              <th>Partner</th>
              <th><?php echo _l('bdm_deal_reference'); ?></th>
              <th><?php echo _l('bdm_deal_value'); ?></th>
              <th><?php echo _l('bdm_commission'); ?></th>
              <th><?php echo _l('bdm_commission_amount'); ?></th>
              <th>Currency</th>
              <th><?php echo _l('bdm_commission_status'); ?></th>
              <th>Accrued</th>
              <th>Paid</th>
              <th><?php echo _l('options'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $partner_index = [];
            foreach ($partners as $p) { $partner_index[$p['id']] = $p; }
            foreach ($commissions as $c):
                $p = $partner_index[$c['partner_id']] ?? null;
                $cls = ['Accrued' => 'warning', 'Paid' => 'success', 'Cancelled' => 'default'];
                $stCls = $cls[$c['status']] ?? 'default';
            ?>
            <tr>
              <td><?php echo $c['id']; ?></td>
              <td><?php echo $p ? htmlentities($p['company_name']) : '#' . $c['partner_id']; ?></td>
              <td><?php echo htmlentities($c['deal_reference'] ?? '—'); ?></td>
              <td><?php echo number_format((float) $c['deal_value'], 2); ?></td>
              <td><?php echo (float) $c['commission_pct']; ?>%</td>
              <td><strong><?php echo number_format((float) $c['commission_amount'], 2); ?></strong></td>
              <td><?php echo htmlentities($c['currency']); ?></td>
              <td><span class="label label-<?php echo $stCls; ?>"><?php echo $c['status']; ?></span></td>
              <td><?php echo $c['accrued_at'] ? _dt($c['accrued_at']) : '—'; ?></td>
              <td><?php echo $c['paid_at'] ? _dt($c['paid_at']) : '—'; ?></td>
              <td>
                <?php if ($c['status'] === 'Accrued'): ?>
                <a href="<?php echo admin_url('bdm_operations/pay_commission/' . $c['id']); ?>" class="btn btn-success btn-icon" title="Mark Paid"><i class="fa fa-check"></i></a>
                <?php endif; ?>
                <a href="<?php echo admin_url('bdm_operations/delete_commission/' . $c['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="commission_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php echo form_open(admin_url('bdm_operations/commissions' . ($partner_id ? '/' . $partner_id : '')), ['id' => 'commission_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Record Commission</h4>
      </div>
      <div class="modal-body">
        <?php
        $partner_opts = [];
        foreach ($partners as $p) {
            $partner_opts[] = ['id' => $p['id'], 'name' => $p['company_name'] . ' (' . $p['commission_pct'] . '%)'];
        }
        echo render_select('partner_id', $partner_opts, ['id', 'name'], 'Partner', $partner_id);
        ?>
        <?php echo render_input('deal_reference', 'bdm_deal_reference'); ?>
        <?php echo render_input('deal_value',     'bdm_deal_value', 0, 'number', ['step' => '0.01']); ?>
        <?php echo render_input('commission_pct', 'bdm_commission', 10, 'number', ['step' => '0.01']); ?>
        <?php echo render_input('currency',       'currency', 'AED'); ?>
        <?php echo render_select('status', [
            ['id' => 'Accrued', 'name' => 'Accrued'],
            ['id' => 'Paid',    'name' => 'Paid'],
        ], ['id', 'name'], 'status', 'Accrued'); ?>
        <?php echo render_textarea('notes', 'bdm_notes'); ?>
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
function new_commission() {
    $('#commission_form')[0].reset();
    $('#commission_form select').selectpicker('refresh');
    $('#commission_modal').modal('show');
}
</script>
