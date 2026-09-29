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
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_tender(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('new_tender'); ?>
              </a>
              <?php endif; ?>
            </div>
            <div class="clearfix"></div>
            <h4 class="no-mtop"><?php echo _l('bdm_tenders'); ?> · ADNOC · DEWA · RTA · Government &amp; Corporate</h4>
            <hr class="hr-panel-heading" />
            <table class="table dt-table" data-order-col="0" data-order-type="desc">
              <thead>
                <tr>
                  <th>ID</th>
                  <th><?php echo _l('bdm_tender_ref'); ?></th>
                  <th><?php echo _l('bdm_tender_title'); ?></th>
                  <th><?php echo _l('bdm_tender_client'); ?></th>
                  <th><?php echo _l('bdm_segment'); ?></th>
                  <th><?php echo _l('emirate'); ?></th>
                  <th><?php echo _l('bdm_tender_value'); ?></th>
                  <th><?php echo _l('bdm_tender_deadline'); ?></th>
                  <th><?php echo _l('status'); ?></th>
                  <th><?php echo _l('assigned_bdm'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $st_cls = ['Identified' => 'default', 'In Preparation' => 'info', 'Submitted' => 'warning', 'Won' => 'success', 'Lost' => 'danger', 'Withdrawn' => 'default'];
                foreach ($tenders as $t):
                    $cls = $st_cls[$t['status']] ?? 'default';
                    $today = date('Y-m-d');
                    $overdue = !empty($t['submission_deadline']) && $t['submission_deadline'] < $today && $t['status'] !== 'Submitted' && $t['status'] !== 'Won' && $t['status'] !== 'Lost';
                ?>
                <tr <?php if ($overdue) echo 'style="background:#ffebee;"'; ?>>
                  <td><?php echo $t['id']; ?></td>
                  <td><?php echo htmlentities($t['tender_ref'] ?? '—'); ?></td>
                  <td><strong><?php echo htmlentities($t['title']); ?></strong></td>
                  <td><?php echo htmlentities($t['client_company'] ?? ''); ?></td>
                  <td><?php echo htmlentities($t['segment'] ?? '—'); ?></td>
                  <td><?php echo htmlentities($t['emirate'] ?? '—'); ?></td>
                  <td><?php echo htmlentities($t['currency'] ?? 'AED'); ?> <?php echo number_format((float) $t['value'], 2); ?></td>
                  <td><?php echo $t['submission_deadline'] ? _d($t['submission_deadline']) : '—'; ?>
                    <?php if ($overdue): ?> <span class="label label-danger">OVERDUE</span><?php endif; ?>
                  </td>
                  <td><span class="label label-<?php echo $cls; ?>"><?php echo htmlentities($t['status']); ?></span></td>
                  <td><?php echo get_staff_full_name($t['assigned_bdm']); ?></td>
                  <td>
                    <a href="#" class="btn btn-default btn-icon" onclick="edit_tender(<?php echo $t['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <a href="<?php echo admin_url('bdm_operations/delete_tender/' . $t['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="tender_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('bdm_operations/tenders'), ['id' => 'tender_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('new_tender'); ?></span>
          <span class="edit-title"><?php echo _l('edit_tender'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('tender_ref', 'bdm_tender_ref'); ?>
            <?php echo render_input('title', 'bdm_tender_title'); ?>
            <?php echo render_input('client_company', 'bdm_tender_client'); ?>
            <?php $segs = []; foreach (bdm_segments() as $k => $v) $segs[] = ['id' => $k, 'name' => $v];
                  echo render_select('segment', $segs, ['id', 'name'], 'bdm_segment'); ?>
            <?php $emrs = []; foreach (bdm_emirates() as $k => $v) $emrs[] = ['id' => $k, 'name' => $v];
                  echo render_select('emirate', $emrs, ['id', 'name'], 'emirate'); ?>
            <?php echo render_input('value', 'bdm_tender_value', 0, 'number', ['step' => '0.01']); ?>
            <?php echo render_input('currency', 'currency', 'AED'); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_date_input('submission_deadline', 'bdm_tender_deadline'); ?>
            <?php echo render_select('status', [
                ['id' => 'Identified',     'name' => 'Identified'],
                ['id' => 'In Preparation', 'name' => 'In Preparation'],
                ['id' => 'Submitted',      'name' => 'Submitted'],
                ['id' => 'Won',            'name' => 'Won'],
                ['id' => 'Lost',           'name' => 'Lost'],
                ['id' => 'Withdrawn',      'name' => 'Withdrawn'],
            ], ['id', 'name'], 'status', 'Identified'); ?>
            <?php echo render_select('assigned_bdm', $staff, ['staffid', ['firstname', 'lastname']], 'assigned_bdm', get_staff_user_id()); ?>
            <?php echo render_textarea('documents_required', 'bdm_tender_documents'); ?>
            <?php echo render_input('outcome', 'bdm_tender_outcome'); ?>
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
function new_tender() {
    $('#tender_form')[0].reset();
    $('#tender_form').attr('action', '<?php echo admin_url('bdm_operations/tenders'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#tender_form select').selectpicker('refresh');
    $('#tender_modal').modal('show');
}
function edit_tender(id) {
    $.get('<?php echo admin_url('bdm_operations/get_tender_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#tender_form').attr('action', '<?php echo admin_url('bdm_operations/tenders/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#tender_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#tender_modal').modal('show');
    });
}
</script>
