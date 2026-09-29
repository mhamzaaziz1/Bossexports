<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade<?php if(isset($task)){echo ' edit';} ?> call-modal" id="_call_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
	    	<div class="modal-header">
	        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        	<h4 class="modal-title" id="myModalLabel">
	            	<?php echo $title; ?>
	        	</h4>
	    	</div>
	    	<?php #print_r($call); ?>
	    	<div class="modal-body">
		      	 <div class="row">
		      	 	<div class="col-md-12 first_form_col">
						<?php echo form_open_multipart(admin_url('call_logs/call/'.$id),array('id'=>'call-form')); ?>
		      	 		<div class="row">
							<div class="col-md-12">
								<h4 class="text-center title_heading">On Going...</h4>
							</div>
						</div>
		      	 		<div class="row">
		      	 			<?php #print_r($call);exit; ?>
				            <div class="col-md-6">
				            	<div class="form-group">
					                <div class="radio radio-primary radio-inline">
					                    <input id="call_type" type="radio" value="current" name="type" <?php if(isset($call) && $call->type == 'current'){echo 'checked';} elseif(!isset($call)) {echo 'checked';}?>>
					                    <label for="call_type"><?php echo _l('current_call'); ?></label>
					                </div>
					                <div class="radio radio-primary radio-inline">
					                    <input id="call_type2" type="radio" value="completed" <?= (isset($call) && $call->type == 'completed') ? 'checked' : '' ?> id="completed_call" name="type">
					                    <label for="call_type2"><?php echo _l('completed_call'); ?></label>
					                </div>
				            		
				            	</div>
				                <!-- <div class="form-group">
				                    <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
				                    <input type="text" name="clientid" value="<?= (isset($call)) ? $call->clientid : '' ?>" class="form-control" id="">          
				                </div> -->
				                <div class="form-group select-placeholder">
				                	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_caller" value="caller_name" <?= (isset($call) && $call->caller_type == 'caller') ? ' checked' : ''; ?>><label for="_caller">Caller</label></div>
				                	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_driver" value="driver_id" <?= (isset($call) && $call->caller_type == 'driver') ? ' checked' : ''; ?> <?= !isset($call) ? 'checked' : ''; ?>><label for="_driver">Driver</label></div>
				                	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_customer" value="clientid" <?= (isset($call) && $call->caller_type == 'customer') ? ' checked' : ''; ?>><label for="_customer"><?php echo _l('invoice_select_customer'); ?></label></div>
				                	<div class="caller_type<?= (isset($call) && $call->caller_type != 'caller') ? ' hidden' : ''; ?> <?php echo !isset($call) ? 'hidden' : ''; ?>">
				                		<input type="text" name="caller_name" class="form-control" <?= (isset($call) && $call->caller_type != 'caller') ? ' disabled' : '';// <-- for edit call log ?> <?= !isset($call) ? 'disabled' : '';// <-- for new call log ?> placeholder="Caller Name" value="<?= (isset($call) && $call->caller_type == 'caller') ? $call->caller_name : '' ?>">
				                	</div>
				                	<div class="caller_type<?= (isset($call) && $call->caller_type != 'driver') ? ' hidden' : ''; ?>">
				                		<select id="driver_id" name="driver_id" data-live-search="true" <?= (isset($call) && $call->caller_type != 'driver') ? ' disabled' : '';// <-- for edit call log ?> data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
					                   		<option value="<?= (isset($call) && !empty($call->driver_id)) ? $call->driver_id : '' ?>"><?= (isset($call) && !empty($call->driver_id)) ? get_driver_name_by_id($call->driver_id) : '' ?></option>
					                    </select>
				                	</div>
				                    <!-- <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label> -->
				                    <div class="caller_type<?= (isset($call) && $call->caller_type != 'customer') ? ' hidden' : '' ?> <?php echo !isset($call) ? 'hidden' : ''; ?>">
				                    	<select id="clientid" name="clientid" data-live-search="true" <?= (isset($call) && $call->caller_type != 'customer') ? ' disabled' : '';// <-- for edit call log ?> <?= !isset($call) ? 'disabled' : '';// <-- for new call log ?> data-width="100%" class="ajax-search<?php if(isset($call) && empty($call->company)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
					                   		<option value="<?= (isset($call)) ? $call->clientid : '' ?>"><?= (isset($call)) ? $call->company : '' ?></option>
				                    	</select>
				                    </div>
				                </div>
				                <div class="form-group">
				                    <label for="direction" class="control-label"><?php echo _l('call_direction'); ?></label>    
				                    <select name="direction" id="" class="form-control">
				                        <option value="inbound" <?= (isset($call) && $call->direction == 'inbound') ? ' selected' : '' ?>><?php echo _l('inbound') ?></option>
				                        <option value="outbound" <?= (isset($call) && $call->direction == 'outbound') ? ' selected' : '' ?>><?php echo _l('outbound') ?></option>
				                    </select>
				                </div>
				                <div class="form-group">
				                    <label for="purpose_of_call" class="control-label"><?php echo _l('purpose'); ?></label>    
				                    <input type="text" name="purpose_of_call" value="<?= (isset($call)) ? $call->purpose_of_call : '' ?>" class="form-control" id="">               
				                </div>
				                <div class="form-group">
				                    <label for="invoice" class="control-label">Invoice #</label>    
				                    <input type="text" name="invoice_id" value="<?= (isset($call)) ? $call->invoice_id : '' ?>" class="form-control" id="invoice">               
				                </div>
				                <div class="form-group">
				                    <label for="call_summary" class="control-label"><?php echo _l('summary'); ?></label>    
				                    <textarea name="call_summary" id="" class="form-control" cols="30" rows="5"><?= (isset($call)) ? $call->call_summary : '' ?></textarea>
				                </div>
				                <div class="form-group">
					                <div for="followup"><?php echo _l('followp_required'); ?></div>
					                <div class="radio radio-primary radio-inline">
					                    <input type="radio" value="yes" name="followup" <?php if(isset($call)){echo ($call->followup == 'yes') ? ' checked' : '';}?>>
					                    <label for=""><?php echo _l('yes'); ?></label>
					                </div>
					                <div class="radio radio-primary radio-inline">
					                    <input type="radio" value="no" id="" name="followup" <?php if(isset($call)){echo ($call->followup == 'no') ? ' checked' : '';} elseif(!isset($call)) {echo 'checked';}?>>
					                    <label for=""><?php echo _l('no'); ?></label>
					                </div>
					                <div class="radio radio-primary radio-inline">
					                    <input type="radio" value="done" id="" name="followup" <?php if(isset($call)){echo ($call->followup == 'done') ? ' checked' : '';} ?>>
					                    <label for=""><?php echo _l('done'); ?></label>
					                </div>
				                </div>
				                <div class="followup" <?php if(isset($call)){echo ($call->followup == 'yes') ? 'style="display:block"' : '';}?>>
				                    <div class="form-group">
				                        <label for="followup_time" class="control-label"><?php echo _l('followup_time'); ?></label>    
				                        <input type="text" name="followup_time" class="form-control datetimepicker" autocomplete="no" id="" value="<?= (isset($call)) && $call->followup_time != '0000-00-00 00:00:00' ? date('Y-d-m H:i:s',strtotime($call->followup_time)) : '' ?>">               
				                    </div>
				                    <div class="form-group">
				                        <label for="followup_notes" class="control-label"><?php echo _l('notes'); ?></label>    
				                        <textarea name="followup_notes" id="" class="form-control" cols="30" rows="5"> <?= (isset($call)) ? $call->followup_notes : '' ?></textarea>
				                    </div>
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="checkbox checkbox-primary no-mtop checkbox-inline current_call <?php if(isset($call)) echo 'hidden'; ?>">
				                    <input type="checkbox" id="end_call" name="" data-name="end_call">
				                    <label for="end_call" data-toggle="tooltip" data-placement="bottom" title=""><?php echo _l('end_call'); ?></label>
				                </div>
				                <div class="form-group">
				                    <label for="start_time" class="control-label"><?php echo _l('call_start_time'); ?></label>    
				                    <input type="text" data-date-format="DD.MM.YYYY HH:MM:s" name="start_time" value="<?php echo (!empty($call)) ? date('Y-d-m H:i:s',strtotime($call->start_time)): date('Y-d-m H:i:s'); ?>" autocomplete="no" class="form-control datetimepicker" id="">               
				                </div>
				                <div class="form-group">
				                    <label for="end_time" class="control-label"><?php echo _l('call_end_time'); ?></label>    
				                    <input type="text" readonly data-date-format="DD.MM.YYYY HH:MM:s" name="end_time" class="form-control datetimepicker" autocomplete="no" id="" value="<?php echo (isset($call)) ? date('Y-d-m H:i:s',strtotime($call->end_time)) : ''; ?>">               
				                </div>
				                <div class="form-group">
				                    <label for="duration" class="control-label"><?php echo _l('call_duration'); ?></label>    
				                    <input type="text" name="duration" readonly value="<?= (isset($call)) ? explode('.',$call->call_duration)[0] : '00:00:00' ?>" class="form-control" id="">               
				                </div>
				                <div class="form-group">
				                    <label for="owner" class="control-label"><?php echo _l('call_owner'); ?></label>         
				                    <select name="call_owner" id="" class="form-control">
				                        <option value="<?php echo get_staff_user_id() ?>"><?php echo get_staff_full_name() ?></option>
				                    </select>      
				                </div>
				                <div class="form-group">
				                    <label for="call_for" class="control-label"><?php echo _l('select_staff'); ?></label>
				                    <select id="call_for" name="call_for" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($call) && empty($call->call_for['staffid'])){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
				                    <?php $selected = (isset($call) ? $call->call_for['staffid'] : '');
				                        if($selected == ''){
				                        $selected = (isset($customer_id) ? $customer_id: '');
				                        }
				                        if($selected != ''){
				                            $rel_data = get_relation_data('staff',$selected);
				                            $rel_val = get_relation_values($rel_data,'staff');
				                            echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
				                        } ?>
				                    </select>
				                </div>
				                <div class="form-group">
				                	<div class="checkbox checkbox-primary no-mtop checkbox-inline ">
					                    <input type="checkbox" id="record_issue" name="record_issue" data-name="record_issue" title="<?php echo _l('record_as_issue'); ?>" data-toggle="tooltip" <?php echo isset($issue_exists) ? 'disabled' : ''; ?>>
					                    <label for="record_issue"><?php echo _l('record_as_issue'); ?></label>
					                </div>
				                </div>
				                <?php if (isset($issue_exists)): ?>
				                	<div class="form-group">
				                		<a href="javascript:view_issue('<?= (isset($call)) ? $call->purpose_of_call : '' ?>', '<?php echo htmlspecialchars(json_encode($issue_exists)); ?>');"><?= (isset($call)) ? $call->purpose_of_call : '' ?></a>
				                	</div>
				                <?php endif ?>
				            </div>
		      	 		</div>
				        <div class="row">
				        	<div class="col-md-12 clearfix">
				        		<div class="pull-right">
					        		<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
					        		<?php $parent_id =  !empty($this->input->get('parent_id')) ? '<input type="hidden" name="parent_id" value="'.$this->input->get('parent_id').'">' : ''; ?>
									<?php echo $parent_id; ?>
							        <?php if(!isset($call)): ?>
							         	<button class="btn btn-info btn-hold" type="button">Hold</button>
							        <?php endif ?>
				        		</div>
				        	</div>
				        </div>
						<?php echo form_close();?>
		      	 	</div>
		      	 	<?php $this->load->view('modals/new_call_modal'); ?>
		        </div>
	   		</div>
	    	<div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
	    	</div>
	   </div>
	</div>
</div>

<script>
    // $('.current_call').hide();
    custom_fields_hyperlink();

    init_actions_on_form('#call-form');
    $('.btn-hold').click(function() {
    	if (!$(this).closest('.modal-dialog').hasClass('modal-xl')) {
    		$(this).closest('.modal-dialog').addClass('modal-xl');
    		$('.new_form_col').removeClass('hidden');
    		$('.first_form_col').removeClass('col-md-12').addClass('col-md-6');

    		$('.first_form_col').find('.title_heading').text('On Hold...');
			init_actions_on_form('#call-form-new');
    	}
    });
    $(document).on('click','.new_form_col .btn_cancel',function() {
    	$('#_call_modal').find('.modal-dialog').removeClass('modal-xl');
    	$('#_call_modal .new_form_col').addClass('hidden');
    	$('#_call_modal .first_form_col').removeClass('col-md-6').addClass('col-md-12');
		$('.first_form_col').find('.title_heading').text('On Going...');
    });
</script>
