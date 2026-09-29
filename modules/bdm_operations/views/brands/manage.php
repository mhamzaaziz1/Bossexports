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
              <a href="#" class="btn btn-info pull-left display-block" onclick="new_brand(); return false;">
                <i class="fa fa-plus"></i> <?php echo _l('new_brand'); ?>
              </a>
              <?php endif; ?>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <table class="table dt-table" data-order-col="0" data-order-type="desc">
              <thead>
                <tr>
                  <th>ID</th>
                  <th><?php echo _l('brand_name'); ?></th>
                  <th><?php echo _l('brand_category'); ?></th>
                  <th><?php echo _l('country'); ?></th>
                  <th><?php echo _l('brand_website'); ?></th>
                  <th><?php echo _l('brand_description'); ?></th>
                  <th><?php echo _l('created_by'); ?></th>
                  <th><?php echo _l('date'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($brands as $brand): ?>
                <tr>
                  <td><?php echo $brand['id']; ?></td>
                  <td><strong><?php echo htmlentities($brand['name']); ?></strong></td>
                  <td><?php echo htmlentities($brand['category'] ?? '-'); ?></td>
                  <td><?php echo $brand['country_id'] ? get_country_short_name($brand['country_id']) : '-'; ?></td>
                  <td>
                    <?php if (!empty($brand['website'])): ?>
                      <a href="<?php echo $brand['website']; ?>" target="_blank"><i class="fa fa-external-link"></i></a>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td><small><?php echo htmlentities($brand['description'] ?? ''); ?></small></td>
                  <td><?php echo get_staff_full_name($brand['created_by']); ?></td>
                  <td><?php echo _dt($brand['created_at']); ?></td>
                  <td>
                    <?php if (has_permission('bdm_operations', '', 'edit')): ?>
                    <a href="#" class="btn btn-default btn-icon" onclick="edit_brand(<?php echo $brand['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <?php endif; ?>
                    <?php if (has_permission('bdm_operations', '', 'delete')): ?>
                    <a href="<?php echo admin_url('bdm_operations/delete_brand/' . $brand['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="brand_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php echo form_open(admin_url('bdm_operations/save_brand'), ['id' => 'brand_form']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('new_brand'); ?></span>
          <span class="edit-title"><?php echo _l('edit_brand'); ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <?php echo render_input('name', 'brand_name'); ?>
        <?php echo render_input('category', 'brand_category'); ?>
        <?php echo render_select('country_id', $countries, ['country_id', ['short_name']], 'country'); ?>
        <?php echo render_input('website', 'brand_website'); ?>
        <?php echo render_textarea('description', 'brand_description'); ?>
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
function new_brand() {
    $('#brand_form')[0].reset();
    $('#brand_form').attr('action', '<?php echo admin_url('bdm_operations/save_brand'); ?>');
    $('.add-title').show(); $('.edit-title').hide();
    $('#brand_form select').selectpicker('refresh');
    $('#brand_modal').modal('show');
}
function edit_brand(id) {
    $.get('<?php echo admin_url('bdm_operations/get_brand_json/'); ?>' + id, function (resp) {
        try { resp = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ return; }
        if (!resp) return;
        $('#brand_form').attr('action', '<?php echo admin_url('bdm_operations/save_brand/'); ?>' + id);
        $('.add-title').hide(); $('.edit-title').show();
        for (var key in resp) {
            var $el = $('#brand_form [name="' + key + '"]');
            if (!$el.length) continue;
            if ($el.is('select')) { $el.selectpicker('val', resp[key]); }
            else { $el.val(resp[key]); }
        }
        $('#brand_modal').modal('show');
    });
}
</script>
