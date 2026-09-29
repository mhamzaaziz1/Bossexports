<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal" data-target="#daraz_category_modal">New Category</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="table-responsive">
                            <table class="table dt-table" data-order-col="0" data-order-type="asc">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Commission Percentage (%)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['name']; ?></td>
                                        <td><?php echo $category['commission_percentage']; ?>%</td>
                                        <td>
                                            <a href="#" class="btn btn-default btn-icon" onclick="edit_category(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>', <?php echo $category['commission_percentage']; ?>)"><i class="fa fa-pencil"></i></a>
                                            <a href="<?php echo admin_url('daraz_profit_calculator/delete_category/' . $category['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="daraz_category_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('daraz_profit_calculator/categories')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Category Info</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="category_id">
                <?php echo render_input('name', 'Category Name'); ?>
                <?php echo render_input('commission_percentage', 'Commission (%)', '', 'number', ['step' => '0.01']); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-info">Save</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>

<script>
function edit_category(id, name, commission) {
    $('#category_id').val(id);
    $('input[name="name"]').val(name);
    $('input[name="commission_percentage"]').val(commission);
    $('#daraz_category_modal').modal('show');
}
</script>
