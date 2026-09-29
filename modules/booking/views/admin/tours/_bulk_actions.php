<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade bulk_actions" id="tours_bulk_actions" tabindex="-1" role="dialog" data-table="<?php echo (isset($table) ? $table : '.table-tours'); ?>">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
         </div>
         <div class="modal-body">

            <?php if(has_permission('tours','','delete')){ ?>
               <div class="checkbox checkbox-danger">
                  <input type="checkbox" name="mass_delete" id="mass_delete">
                  <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
               </div>
               <hr class="mass_delete_separator" />
            <?php } ?>
            <div id="bulk_change">
               <div class="form-group">
                  <label for="move_to_status_tours_bulk_action"><?php echo _l('tour_status'); ?></label>
                  <select name="move_to_status_tours_bulk_action" id="move_to_status_tours_bulk_action" data-width="100%" class="selectpicker" data-none-selected-text="<?php echo _l('tour_status'); ?>">
                     <option value=""></option>
                     <?php foreach($tour_statuses as $status){ ?>
                        <option value="<?php echo $status['id']; ?>"><?php echo $status['name']; ?></option>
                     <?php } ?>
                  </select>
               </div>
               <?php if(has_permission('tours','','edit')){ ?>

                  <div class="form-group">
                     <label for="tour_bulk_priority" class="control-label"><?php echo _l('tour_add_edit_priority'); ?></label>
                     <select name="tour_bulk_priority" class="selectpicker" id="tour_bulk_priority" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""></option>
                        <option value="1"><?php echo _l('tour_priority_low'); ?></option>
                        <option value="2"><?php echo _l('tour_priority_medium'); ?></option>
                        <option value="3"><?php echo _l('tour_priority_high'); ?></option>
                        <option value="4"><?php echo _l('tour_priority_urgent'); ?></option>
                     </select>
                  </div>
                  <?php
                  echo '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="'._l('tours_bull_actions_assign_notice').'"></i>';
                  $staff_bulk_assigned = $this->staff_model->get('', ['active'=>1]);
                  echo render_select('tour_bulk_assignees',$staff_bulk_assigned,array('staffid',array('firstname','lastname')),'tour_assigned','',array('multiple'=>true));
                  if(isset($booking)){
                    echo render_select('tour_bulk_milestone', $this->bookings_model->get_milestones($booking->id), array(
                      'id',
                      'name'
                   ), 'tour_milestone');
                } ?>
                <div class="form-group">
                  <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                  <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
               </div>

            <?php } ?>
         </div>
      </div>
      <div class="modal-footer">
         <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
         <a href="#" class="btn btn-info" onclick="tours_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
      </div>
   </div>
   <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
