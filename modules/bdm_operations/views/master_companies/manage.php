<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons">
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_company(); return false;">
                <i class="fa fa-plus"></i> New Company
              </a>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <table class="table dt-table" data-order-col="0" data-order-type="desc">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Company</th>
                  <th>Reg Number</th>
                  <th>Industry</th>
                  <th><?php echo _l('emirate'); ?></th>
                  <th>Country</th>
                  <th>Website</th>
                  <th>Phone</th>
                  <th>Created</th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                  <td><?php echo $company['id']; ?></td>
                  <td><strong><?php echo htmlentities($company['company_name']); ?></strong></td>
                  <td><?php echo htmlentities($company['registration_number'] ?? '-'); ?></td>
                  <td><?php echo htmlentities($company['industry'] ?? '-'); ?></td>
                  <td><?php echo htmlentities($company['emirate'] ?? '-'); ?></td>
                  <td><?php echo $company['country_id'] ? get_country_short_name($company['country_id']) : '-'; ?></td>
                  <td><?php echo htmlentities($company['website'] ?? '-'); ?></td>
                  <td><?php echo htmlentities($company['phone'] ?? '-'); ?></td>
                  <td><?php echo _dt($company['created_at']); ?></td>
                  <td>
                    <a href="#" class="btn btn-default btn-icon" onclick="edit_company(<?php echo $company['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <a href="<?php echo admin_url('bdm_operations/delete_company/' . $company['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="company_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php echo form_open(admin_url('bdm_operations/save_company'), ['id' => 'company_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><span class="add-title">New Company</span><span class="edit-title">Edit Company</span></h4>
      </div>
      <div class="modal-body">
        <?php echo render_input('company_name', 'company_name'); ?>
        <?php echo render_input('registration_number', 'Registration Number'); ?>
        <?php echo render_input('industry', 'Industry'); ?>
        <?php $emrs = []; foreach (bdm_emirates() as $k => $v) $emrs[] = ['id' => $k, 'name' => $v];
              echo render_select('emirate', $emrs, ['id', 'name'], 'emirate'); ?>
        <?php echo render_select('country_id', $countries, ['country_id', 'short_name'], 'country'); ?>
        <?php echo render_input('website', 'Website'); ?>
        <?php echo render_input('phone', 'phone'); ?>
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
function new_company() {
    $('#company_form')[0].reset();
    $('#company_form').attr('action', '<?php echo admin_url('bdm_operations/save_company'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#company_form select').selectpicker('refresh');
    $('#company_modal').modal('show');
}
function edit_company(id) {
    $.get('<?php echo admin_url('bdm_operations/get_company_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#company_form').attr('action', '<?php echo admin_url('bdm_operations/save_company/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#company_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#company_modal').modal('show');
    });
}
</script>
