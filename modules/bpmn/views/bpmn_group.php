<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="bpmn-group-modal" tabindex="-1" role="dialog" aria-labelledby="bpmn-group-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('bpmn/group'),array('id'=>'bpmn-group-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_bpmn_group'); ?></span>
                    <span class="add-title"><?php echo _l('new_bpmn_group'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name','bpmn_group_add_edit_name'); ?>
                        <?php echo render_textarea('description','bpmn_group_add_edit_description'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript" src="<?php echo base_url();?>modules/bpmn/assets/js/group.js"></script>
