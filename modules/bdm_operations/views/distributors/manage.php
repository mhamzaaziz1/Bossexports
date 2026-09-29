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
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_distributor(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('new_distributor'); ?>
              </a>
              <?php endif; ?>
              <div class="col-md-3 pull-right">
                <input type="text" id="distributor_search" class="form-control" placeholder="AJAX search vendors..." onkeyup="search_distributors(this.value);">
              </div>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />

            <div id="distributor_list_container">
              <table class="table dt-table" data-order-col="0" data-order-type="desc">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th><?php echo _l('distributor_name'); ?></th>
                    <th><?php echo _l('brand'); ?></th>
                    <th><?php echo _l('classification'); ?></th>
                    <th><?php echo _l('emirate'); ?></th>
                    <th><?php echo _l('contact_name'); ?></th>
                    <th><?php echo _l('email'); ?></th>
                    <th><?php echo _l('phone'); ?></th>
                    <th>VAT TRN</th>
                    <th><?php echo _l('assigned_bdm'); ?></th>
                    <th><?php echo _l('options'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($distributors as $distributor):
                      $b = $distributor['brand_id'] ? $this->bdm_operations_model->get_brands($distributor['brand_id']) : null;
                  ?>
                  <tr>
                    <td><?php echo $distributor['id']; ?></td>
                    <td><strong><?php echo htmlentities($distributor['name']); ?></strong>
                      <?php if (!empty($distributor['iso_certified'])): ?> <span class="label label-success">ISO</span><?php endif; ?>
                    </td>
                    <td><?php echo $b ? htmlentities($b->name) : '-'; ?></td>
                    <td><?php echo htmlentities($distributor['classification'] ?? '-'); ?></td>
                    <td><?php echo htmlentities($distributor['emirate'] ?? '-'); ?></td>
                    <td><?php echo htmlentities($distributor['contact_name'] ?? ''); ?></td>
                    <td><?php echo htmlentities($distributor['email'] ?? ''); ?></td>
                    <td><?php echo htmlentities($distributor['phone'] ?? ''); ?></td>
                    <td><?php echo htmlentities($distributor['vat_trn'] ?? '-'); ?></td>
                    <td><?php echo get_staff_full_name($distributor['assigned_bdm']); ?></td>
                    <td>
                      <?php if (has_permission('bdm_operations', '', 'edit')): ?>
                      <a href="#" class="btn btn-default btn-icon" onclick="edit_distributor(<?php echo $distributor['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                      <a href="<?php echo admin_url('bdm_operations/push_to_purchase/' . $distributor['id']); ?>" class="btn btn-success btn-icon" title="Push to Purchase Module"><i class="fa fa-share-square-o"></i></a>
                      <?php endif; ?>
                      <?php if (has_permission('bdm_operations', '', 'delete')): ?>
                      <a href="<?php echo admin_url('bdm_operations/delete_distributor/' . $distributor['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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
</div>

<div class="modal fade" id="distributor_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <?php echo form_open(admin_url('bdm_operations/save_distributor'), ['id' => 'distributor_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('new_distributor'); ?></span>
          <span class="edit-title"><?php echo _l('edit_distributor'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('name', 'distributor_name'); ?>
            <?php echo render_select('brand_id', $brands, ['id', 'name'], 'brand'); ?>
            <?php echo render_select('classification', [
                ['id' => 'National', 'name' => 'National'],
                ['id' => 'Regional', 'name' => 'Regional'],
                ['id' => 'Local', 'name' => 'Local'],
                ['id' => 'International', 'name' => 'International'],
            ], ['id', 'name'], 'classification'); ?>
            <?php $segs = []; foreach (bdm_segments() as $k => $v) $segs[] = ['id' => $k, 'name' => $v];
                  echo render_select('segment', $segs, ['id', 'name'], 'bdm_segment'); ?>
            <?php $emrs = []; foreach (bdm_emirates() as $k => $v) $emrs[] = ['id' => $k, 'name' => $v];
                  echo render_select('emirate', $emrs, ['id', 'name'], 'emirate'); ?>
            <?php echo render_select('vendor_role', [
                ['id' => 'Supplier',         'name' => _l('vendor_role_supplier')],
                ['id' => 'Service Provider', 'name' => _l('vendor_role_service')],
                ['id' => 'Subcontractor',    'name' => _l('vendor_role_subcon')],
            ], ['id', 'name'], 'vendor_role'); ?>
            <?php echo render_input('contact_name', 'contact_name'); ?>
            <?php echo render_input('email', 'email'); ?>
            <?php echo render_input('phone', 'phone'); ?>
            <?php echo render_input('whatsapp', 'whatsapp'); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_input('website', 'brand_website'); ?>
            <?php echo render_textarea('address', 'address'); ?>
            <?php echo render_input('trade_license_no', 'trade_license_no'); ?>
            <?php echo render_input('vat_trn', 'vat_trn'); ?>
            <?php echo render_input('cr_number', 'cr_number'); ?>
            <?php echo render_yes_no_option('iso_certified', 'iso_certified'); ?>
            <?php echo render_input('products_services', 'products_services'); ?>
            <?php echo render_input('price_range', 'price_range'); ?>
            <?php echo render_date_input('follow_up_date', 'follow_up_date'); ?>
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
function new_distributor() {
    $('#distributor_form')[0].reset();
    $('#distributor_form').attr('action', '<?php echo admin_url('bdm_operations/save_distributor'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#distributor_form select').selectpicker('refresh');
    $('#distributor_modal').modal('show');
}
function edit_distributor(id) {
    $.get('<?php echo admin_url('bdm_operations/get_distributor_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#distributor_form').attr('action', '<?php echo admin_url('bdm_operations/save_distributor/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#distributor_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#distributor_modal').modal('show');
    });
}
function search_distributors(q) {
    if (q.length === 1) return;
    $.get('<?php echo admin_url('bdm_operations/distributor_search'); ?>?q=' + encodeURIComponent(q), function (response) {
        try { response = (typeof response === 'string') ? JSON.parse(response) : response; } catch(e){ return; }
        var html = '<table class="table"><thead><tr><th>ID</th><th>Name</th><th>Classification</th><th>Emirate</th><th>Contact</th><th>Email</th><th>Phone</th><th>Options</th></tr></thead><tbody>';
        response.forEach(function (d) {
            html += '<tr>'
                +  '<td>' + d.id + '</td>'
                +  '<td>' + (d.name || '') + '</td>'
                +  '<td>' + (d.classification || '') + '</td>'
                +  '<td>' + (d.emirate || '') + '</td>'
                +  '<td>' + (d.contact_name || '') + '</td>'
                +  '<td>' + (d.email || '') + '</td>'
                +  '<td>' + (d.phone || '') + '</td>'
                +  '<td><a href="#" class="btn btn-default btn-icon" onclick="edit_distributor(' + d.id + '); return false;"><i class="fa fa-pencil-square-o"></i></a></td>'
                +  '</tr>';
        });
        html += '</tbody></table>';
        $('#distributor_list_container').html(html);
    });
}
</script>
