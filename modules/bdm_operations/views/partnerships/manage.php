<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons">
              <?php if (has_permission('bdm_operations', '', 'create')): ?>
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_partner(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('bdm_new_partner'); ?>
              </a>
              <?php endif; ?>
              <a href="<?php echo admin_url('bdm_operations/commissions'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-money"></i> <?php echo _l('bdm_commissions'); ?></a>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <table class="table dt-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th><?php echo _l('company_name'); ?></th>
                  <th><?php echo _l('bdm_partner_type'); ?></th>
                  <th><?php echo _l('status'); ?></th>
                  <th><?php echo _l('contact_name'); ?></th>
                  <th><?php echo _l('email'); ?></th>
                  <th><?php echo _l('bdm_commission'); ?></th>
                  <th>Total Deals</th>
                  <th>Revenue (AED)</th>
                  <th>Commission (AED)</th>
                  <th><?php echo _l('assigned_bdm'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($partners as $partner):
                    $cls = ['Active' => 'success', 'Pending' => 'warning', 'Suspended' => 'danger'];
                    $stCls = $cls[$partner['status']] ?? 'default';
                ?>
                <tr>
                  <td><?php echo $partner['id']; ?></td>
                  <td><strong><?php echo htmlentities($partner['company_name']); ?></strong>
                    <?php if (!empty($partner['emirate'])): ?>
                      <br><small class="text-muted"><?php echo htmlentities($partner['emirate']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="label label-<?php echo strpos($partner['partner_type'], 'Inbound') !== false ? 'primary' : 'info'; ?>">
                      <?php echo htmlentities($partner['partner_type']); ?>
                    </span>
                  </td>
                  <td><span class="label label-<?php echo $stCls; ?>"><?php echo htmlentities($partner['status']); ?></span></td>
                  <td><?php echo htmlentities($partner['contact_person'] ?? ''); ?></td>
                  <td><?php echo htmlentities($partner['email'] ?? ''); ?></td>
                  <td><?php echo $partner['commission_pct']; ?>%</td>
                  <td><?php echo (int) ($partner['total_deals'] ?? 0); ?></td>
                  <td><?php echo number_format((float) ($partner['total_revenue'] ?? 0), 2); ?></td>
                  <td><?php echo number_format((float) ($partner['total_commission'] ?? 0), 2); ?></td>
                  <td><?php echo get_staff_full_name($partner['assigned_bdm']); ?></td>
                  <td>
                    <a href="<?php echo admin_url('bdm_operations/commissions/' . $partner['id']); ?>" class="btn btn-success btn-icon" title="Commissions"><i class="fa fa-money"></i></a>
                    <a href="#" class="btn btn-default btn-icon" onclick="edit_partner(<?php echo $partner['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <a href="<?php echo admin_url('bdm_operations/delete_partner/' . $partner['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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
</div>

<div class="modal fade" id="partner_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('bdm_operations/partnerships'), ['id' => 'partner_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('bdm_new_partner'); ?></span>
          <span class="edit-title"><?php echo _l('edit_partner'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('company_name', 'company_name'); ?>
            <?php echo render_select('partner_type', [
                ['id' => 'Inbound (Channel Sales)',           'name' => _l('bdm_inbound_partner')],
                ['id' => 'Outbound (Reseller / Distributor)', 'name' => _l('bdm_outbound_partner')],
            ], ['id', 'name'], 'bdm_partner_type'); ?>
            <?php echo render_select('status', [
                ['id' => 'Active',    'name' => 'Active'],
                ['id' => 'Pending',   'name' => 'Pending'],
                ['id' => 'Suspended', 'name' => 'Suspended'],
                ['id' => 'Closed',    'name' => 'Closed'],
            ], ['id', 'name'], 'status', 'Pending'); ?>
            <?php echo render_input('contact_person', 'contact_name'); ?>
            <?php echo render_input('email', 'email'); ?>
            <?php echo render_input('phone', 'phone'); ?>
            <?php echo render_input('whatsapp', 'whatsapp'); ?>
          </div>
          <div class="col-md-6">
            <?php $emrs = []; foreach (bdm_emirates() as $k => $v) $emrs[] = ['id' => $k, 'name' => $v];
                  echo render_select('emirate', $emrs, ['id', 'name'], 'emirate'); ?>
            <?php echo render_input('territory', 'bdm_territory'); ?>
            <?php echo render_date_input('agreement_date',   'bdm_agreement_date'); ?>
            <?php echo render_date_input('agreement_expiry', 'bdm_agreement_expiry'); ?>
            <?php echo render_input('commission_pct', 'bdm_commission', '10', 'number', ['step' => '0.01']); ?>
            <?php echo render_input('margin_pct',     'bdm_margin',     '0',  'number', ['step' => '0.01']); ?>
            <?php echo render_select('assigned_bdm', $staff, ['staffid', ['firstname', 'lastname']], 'assigned_bdm', get_staff_user_id()); ?>
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
function new_partner() {
    $('#partner_form')[0].reset();
    $('#partner_form').attr('action', '<?php echo admin_url('bdm_operations/partnerships'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#partner_form select').selectpicker('refresh');
    $('#partner_modal').modal('show');
}
function edit_partner(id) {
    $.get('<?php echo admin_url('bdm_operations/get_partner_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#partner_form').attr('action', '<?php echo admin_url('bdm_operations/partnerships/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#partner_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#partner_modal').modal('show');
    });
}
</script>
