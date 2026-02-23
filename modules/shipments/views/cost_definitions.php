<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('shipments', '', 'create')) { ?>
                            <a href="#" onclick="new_cost_definition(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('new_cost_definition'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('cost_name'); ?></th>
                                    <th><?php echo _l('allocation_default'); ?></th>
                                    <th><?php echo _l('layer_level'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cost_definitions as $cost) { ?>
                                <tr>
                                    <td><a href="#" onclick="edit_cost_definition(this); return false;" data-id="<?php echo $cost['id']; ?>" data-name="<?php echo $cost['name']; ?>" data-allocation="<?php echo $cost['allocation_default']; ?>" data-layer="<?php echo $cost['layer_level']; ?>"><?php echo $cost['name']; ?></a></td>
                                    <td><?php echo ucfirst($cost['allocation_default']); ?></td>
                                    <td><?php echo $cost['layer_level']; ?></td>
                                    <td>
                                        <a href="#" onclick="edit_cost_definition(this); return false;" data-id="<?php echo $cost['id']; ?>" data-name="<?php echo $cost['name']; ?>" data-allocation="<?php echo $cost['allocation_default']; ?>" data-layer="<?php echo $cost['layer_level']; ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i><?php echo _l('edit'); ?></a>
                                        <?php if (has_permission('shipments', '', 'delete')) { ?>
                                        <a href="<?php echo admin_url('shipments/delete_cost_definition/' . $cost['id']); ?>" class="btn btn-danger btn-icon _delete"><?php echo _l('delete'); ?></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cost_definition_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('shipments/cost_definitions')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('new_cost_definition'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id">
                <?php echo render_input('name', 'cost_name'); ?>
                
                <div class="form-group">
                    <label class="control-label"><?php echo _l('allocation_default'); ?></label>
                    <select name="allocation_default" class="form-control selectpicker">
                        <option value="value"><?php echo _l('allocation_value'); ?></option>
                        <option value="weight"><?php echo _l('allocation_weight'); ?></option>
                        <option value="volume"><?php echo _l('allocation_volume'); ?></option>
                        <option value="quantity"><?php echo _l('allocation_quantity'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="control-label"><?php echo _l('layer_level'); ?></label>
                    <select name="layer_level" class="form-control selectpicker">
                        <option value="1"><?php echo _l('cost_layer_1'); ?></option>
                        <option value="2"><?php echo _l('cost_layer_2'); ?></option>
                        <option value="3"><?php echo _l('cost_layer_3'); ?></option>
                        <option value="4"><?php echo _l('cost_layer_4'); ?></option>
                    </select>
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

<script>
    function new_cost_definition(){
        $('#cost_definition_modal').modal('show');
        $('.edit-title').addClass('hide');
        $('.add-title').removeClass('hide');
        $('#cost_definition_modal input[name="id"]').val('');
        $('#cost_definition_modal input[name="name"]').val('');
        $('#cost_definition_modal select[name="allocation_default"]').selectpicker('val', 'value');
        $('#cost_definition_modal select[name="layer_level"]').selectpicker('val', '1');
    }

    function edit_cost_definition(invoker){
        var id = $(invoker).data('id');
        var name = $(invoker).data('name');
        var allocation = $(invoker).data('allocation');
        var layer = $(invoker).data('layer');
        
        $('#cost_definition_modal').modal('show');
        $('.edit-title').removeClass('hide');
        $('.add-title').addClass('hide');
        $('#cost_definition_modal input[name="id"]').val(id);
        $('#cost_definition_modal input[name="name"]').val(name);
        $('#cost_definition_modal select[name="allocation_default"]').selectpicker('val', allocation);
        $('#cost_definition_modal select[name="layer_level"]').selectpicker('val', layer);
    }
</script>

<?php init_tail(); ?>
