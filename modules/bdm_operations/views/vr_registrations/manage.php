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
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_vr(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('vr_registrations'); ?>
              </a>
              <?php endif; ?>
              <a href="<?php echo admin_url('bdm_operations/pipeline'); ?>" class="btn btn-default pull-left mleft5"><i class="fa fa-columns"></i> Kanban</a>
            </div>
            <div class="clearfix"></div>
            <h4 class="no-mtop"><?php echo _l('vr_registrations'); ?> · 10-Step Pipeline · UAE Compliance</h4>
            <hr class="hr-panel-heading" />
            <div class="clearfix"></div>
            <table class="table dt-table" data-order-col="0" data-order-type="desc">
              <thead>
                <tr>
                  <th>ID</th>
                  <th><?php echo _l('company_name'); ?></th>
                  <th><?php echo _l('bdm_registration_type'); ?></th>
                  <th><?php echo _l('bdm_segment'); ?></th>
                  <th>Step</th>
                  <th><?php echo _l('status'); ?></th>
                  <th><?php echo _l('bdm_expected_value'); ?></th>
                  <th>Compliance</th>
                  <th><?php echo _l('assigned_bdm'); ?></th>
                  <th><?php echo _l('last_updated'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($registrations as $reg):
                    $c   = $this->bdm_operations_model->get_master_companies($reg['company_id']);
                    $cls = bdm_status_label_class($reg['status']);
                ?>
                <tr>
                  <td><?php echo $reg['id']; ?></td>
                  <td><strong><?php echo $c ? htmlentities($c->company_name) : 'N/A'; ?></strong></td>
                  <td><?php echo htmlentities($reg['registration_type']); ?></td>
                  <td><?php echo htmlentities($reg['segment'] ?? '—'); ?></td>
                  <td>
                    <span class="label label-primary" title="<?php echo isset($pipeline_step_map[$reg['pipeline_step']]) ? htmlentities($pipeline_step_map[$reg['pipeline_step']]) : ''; ?>">
                      S<?php echo (int) $reg['pipeline_step']; ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-<?php echo $cls; ?> dropdown-toggle btn-xs" data-toggle="dropdown">
                        <?php echo isset($pipeline_stages[$reg['status']]) ? $pipeline_stages[$reg['status']] : $reg['status']; ?> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                        <?php foreach ($pipeline_stages as $stage_val => $stage_label): ?>
                        <li><a href="#" onclick="update_vr_status(<?php echo $reg['id']; ?>, '<?php echo $stage_val; ?>'); return false;"><?php echo $stage_label; ?></a></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </td>
                  <td>AED <?php echo number_format((float) ($reg['expected_value'] ?? 0), 0); ?></td>
                  <td>
                    <?php
                    $marks = [];
                    if (!empty($reg['compliance_iso']))           $marks[] = '<i class="fa fa-check-circle text-success" title="ISO"></i>';
                    if (!empty($reg['compliance_trade_license'])) $marks[] = '<i class="fa fa-check-circle text-success" title="Trade License"></i>';
                    if (!empty($reg['compliance_vat_trn']))       $marks[] = '<i class="fa fa-check-circle text-success" title="VAT TRN"></i>';
                    if (!empty($reg['compliance_cr']))            $marks[] = '<i class="fa fa-check-circle text-success" title="CR"></i>';
                    echo $marks ? implode(' ', $marks) : '<span class="text-muted small">—</span>';
                    ?>
                  </td>
                  <td><?php echo get_staff_full_name($reg['assigned_bdm']); ?></td>
                  <td><?php echo time_ago($reg['updated_at']); ?></td>
                  <td>
                    <a href="#" class="btn btn-default btn-icon" title="<?php echo _l('view_history'); ?>" onclick="view_vr_activity(<?php echo $reg['id']; ?>); return false;"><i class="fa fa-history"></i></a>
                    <?php if (has_permission('bdm_operations', '', 'edit')): ?>
                    <a href="#" class="btn btn-default btn-icon" title="Edit" onclick="edit_vr(<?php echo $reg['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <?php endif; ?>
                    <?php if (has_permission('bdm_operations', '', 'delete')): ?>
                    <a href="<?php echo admin_url('bdm_operations/delete_vr/' . $reg['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                    <?php endif; ?>
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

<!-- ============== VR EDIT MODAL ============== -->
<div class="modal fade" id="vr_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('bdm_operations/save_vr'), ['id' => 'vr_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('vr_registrations'); ?></span>
          <span class="edit-title">Edit Registration</span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php
              $companies_select = [];
              foreach ($companies as $c) {
                  $companies_select[] = ['id' => $c['id'], 'name' => $c['company_name']];
              }
              echo render_select('company_id', $companies_select, ['id', 'name'], 'company_name');
            ?>
            <?php echo render_input('company_name', 'company_name', '', 'text', ['placeholder' => 'Or type a new company...']); ?>
            <?php echo render_select('registration_type', [
                ['id' => 'Supplier Onboarding',     'name' => _l('bdm_supplier_onboarding')],
                ['id' => 'Client Vendor Approval', 'name' => _l('bdm_client_approval')],
            ], ['id', 'name'], 'bdm_registration_type'); ?>
            <?php
              $segs = [];
              foreach (bdm_segments() as $k => $v) $segs[] = ['id' => $k, 'name' => $v];
              echo render_select('segment', $segs, ['id', 'name'], 'bdm_segment');
            ?>
            <?php
              $prios = [];
              foreach (bdm_priorities() as $k => $v) $prios[] = ['id' => $k, 'name' => $v];
              echo render_select('priority', $prios, ['id', 'name'], 'bdm_priority', 'Medium');
            ?>
            <?php
              $emirates = [];
              foreach (bdm_emirates() as $k => $v) $emirates[] = ['id' => $k, 'name' => $v];
              echo render_select('emirate', $emirates, ['id', 'name'], 'emirate');
            ?>
            <?php
              $steps = [];
              foreach ($pipeline_step_map as $k => $v) $steps[] = ['id' => $k, 'name' => $v];
              echo render_select('pipeline_step', $steps, ['id', 'name'], 'Pipeline Step', '1');
            ?>
            <?php
              $statuses = [];
              foreach ($pipeline_stages as $k => $v) $statuses[] = ['id' => $k, 'name' => $v];
              echo render_select('status', $statuses, ['id', 'name'], 'status', 'New Referral');
            ?>
          </div>
          <div class="col-md-6">
            <?php echo render_input('decision_maker', 'bdm_decision_maker'); ?>
            <?php echo render_input('decision_maker_role', 'bdm_decision_role'); ?>
            <?php echo render_input('email', 'email'); ?>
            <?php echo render_input('phone', 'phone'); ?>
            <?php echo render_input('expected_value', 'bdm_expected_value', 0, 'number', ['step' => '0.01']); ?>
            <?php echo render_input('won_value',      'bdm_won_value',      0, 'number', ['step' => '0.01']); ?>
            <?php echo render_date_input('target_close_date', 'bdm_target_close_date'); ?>
            <?php echo render_date_input('follow_up_date', 'follow_up_date'); ?>
            <?php echo render_select('assigned_bdm', $staff, ['staffid', ['firstname', 'lastname']], 'assigned_bdm', get_staff_user_id()); ?>
          </div>
        </div>
        <hr/>
        <h5><strong><?php echo _l('bdm_compliance'); ?></strong></h5>
        <div class="row">
          <div class="col-md-3"><?php echo render_yes_no_option('compliance_iso',           _l('bdm_compliance_iso')); ?></div>
          <div class="col-md-3"><?php echo render_yes_no_option('compliance_trade_license', _l('bdm_compliance_tl')); ?></div>
          <div class="col-md-3"><?php echo render_yes_no_option('compliance_vat_trn',       _l('bdm_compliance_vat')); ?></div>
          <div class="col-md-3"><?php echo render_yes_no_option('compliance_cr',            _l('bdm_compliance_cr')); ?></div>
        </div>
        <?php echo render_textarea('documents_submitted', 'bdm_documents'); ?>
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

<!-- ============== ACTIVITY HISTORY MODAL ============== -->
<div class="modal fade" id="vr_activity_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('registration_activity'); ?></h4>
      </div>
      <div class="modal-body">
        <div id="vr_activity_log_container"></div>
      </div>
    </div>
  </div>
</div>

<?php echo form_open(admin_url('bdm_operations/update_vr_status/'), ['id' => 'status_update_form']); ?>
  <input type="hidden" name="status" id="new_status_val">
<?php echo form_close(); ?>

<?php init_tail(); ?>
<script>
function update_vr_status(id, status) {
    $('#status_update_form').attr('action', '<?php echo admin_url('bdm_operations/update_vr_status/'); ?>' + id);
    $('#new_status_val').val(status);
    $('#status_update_form').submit();
}
function view_vr_activity(id) {
    $.get('<?php echo admin_url('bdm_operations/get_vr_activity_json/'); ?>' + id, function (response) {
        try { response = (typeof response === 'string') ? JSON.parse(response) : response; } catch(e){ response = []; }
        var html = '<ul class="list-group">';
        if (!response.length) html += '<li class="list-group-item text-muted">No activity recorded yet.</li>';
        response.forEach(function (log) {
            html += '<li class="list-group-item"><strong>' + log.date_created + ':</strong> ' + log.action_description + '</li>';
        });
        html += '</ul>';
        $('#vr_activity_log_container').html(html);
        $('#vr_activity_modal').modal('show');
    });
}
function new_vr() {
    $('#vr_form')[0].reset();
    $('#vr_form').attr('action', '<?php echo admin_url('bdm_operations/save_vr'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#vr_form select').selectpicker('refresh');
    $('#vr_modal').modal('show');
}
function edit_vr(id) {
    $.get('<?php echo admin_url('bdm_operations/get_vr_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e) { return; }
        if (!resp) return;
        $('#vr_form').attr('action', '<?php echo admin_url('bdm_operations/save_vr/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#vr_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) {
                $el.selectpicker('val', resp[key]);
            } else {
                $el.val(resp[key]);
            }
        }
        $('#vr_modal').modal('show');
    });
}
</script>
