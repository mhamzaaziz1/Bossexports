<div class="col-md-6 new_form_col hidden">
	<?php echo form_open_multipart(admin_url('call_logs/call/'.$id),array('id'=>'call-form-new','data-form'=>'new')); ?>
	<div class="row">
		<div class="col-md-12">
			<h4 class="text-center title_heading">New Call</h4>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<div class="radio radio-primary radio-inline">
					<input id="new_call_type" type="radio" value="current" name="type" checked>
					<label for="new_call_type"><?php echo _l('current_call'); ?></label>
				</div>
			</div>
			<div class="form-group select-placeholder">
            	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_caller_new" value="caller_name"><label for="_caller_new">Caller</label></div>
            	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_driver_new" checked value="driver_id"><label for="_driver_new">Driver</label></div>
            	<div class="radio radio-primary radio-inline"><input type="radio" name="caller_type" id="_customer_new" value="clientid"><label for="_customer_new"><?php echo _l('invoice_select_customer'); ?></label></div>
            	<div class="hidden caller_type">
            		<input type="text" class="form-control" name="caller_name" disabled placeholder="Caller Name">
            	</div>
            	<div class="caller_type">
            		<select id="driver_id" name="driver_id" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                   		<option value=""></option>
                    </select>
            	</div>
            	<div class="caller_type hidden">
            		<select id="clientid" name="clientid" disabled data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
						<option value=""></option>
					</select>
            	</div>
			</div>
			<div class="form-group">
				<label for="direction" class="control-label"><?php echo _l('call_direction'); ?></label>    
				<select name="direction" id="direction" class="form-control">
					<option value="inbound">Inbound</option>
					<option value="outbound">Outbound</option>
				</select>
			</div>
			<div class="form-group">
				<label for="purpose_of_call" class="control-label"><?php echo _l('purpose'); ?></label>    
				<input type="text" name="purpose_of_call" value="" class="form-control" id="">               
			</div>
            <div class="form-group">
                <label for="new_invoice" class="control-label">Invoice #</label>    
                <input type="text" name="invoice_id" value="" class="form-control" id="new_invoice">               
            </div>
			<div class="form-group">
				<label for="call_summary" class="control-label"><?php echo _l('summary'); ?></label>    
				<textarea name="call_summary" id="" class="form-control" cols="30" rows="5"></textarea>
			</div>
			<div class="form-group">
				<div for="followup"><?php echo _l('followp_required'); ?></div>
				<div class="radio radio-primary radio-inline">
					<input type="radio" value="yes" name="followup">
					<label for=""><?php echo _l('yes'); ?></label>
				</div>
				<div class="radio radio-primary radio-inline">
					<input type="radio" checked value="no" id="" name="followup">
					<label for=""><?php echo _l('no'); ?></label>
				</div>
				<div class="radio radio-primary radio-inline">
					<input type="radio" value="done" id="" name="followup">
					<label for=""><?php echo _l('done'); ?></label>
				</div>
			</div>
			<div class="followup">
				<div class="form-group">
					<label for="followup_time" class="control-label"><?php echo _l('followup_time'); ?></label>    
					<input type="text" name="followup_time" class="form-control datetimepicker" autocomplete="no" id="">               
				</div>
				<div class="form-group">
					<label for="new_followup_notes" class="control-label"><?php echo _l('notes'); ?></label>    
					<textarea name="followup_notes" id="" class="form-control" cols="30" rows="5"></textarea>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="checkbox checkbox-primary no-mtop checkbox-inline current_call">
				<input type="checkbox" id="new_end_call" name="" data-name="end_call">
				<label for="new_end_call" data-toggle="tooltip" data-placement="bottom" title=""><?php echo _l('end_call'); ?></label>
			</div>
			<div class="form-group">
				<label for="start_time" class="control-label"><?php echo _l('call_start_time'); ?></label>    
				<input type="text" data-date-format="DD.MM.YYYY HH:MM:s" name="start_time" value="" autocomplete="no" class="form-control datetimepicker" id="">               
			</div>
			<div class="form-group">
				<label for="end_time" class="control-label"><?php echo _l('call_end_time'); ?></label>    
				<input type="text" readonly data-date-format="DD.MM.YYYY HH:MM:s" name="end_time" class="form-control" autocomplete="no" id="new_end_time" value="">               
			</div>
			<div class="form-group">
				<label for="duration" class="control-label"><?php echo _l('call_duration'); ?></label>    
				<input type="text" name="duration" readonly value="00:00:00" class="form-control" id="">                
			</div>
			<div class="form-group">
				<label for="owner" class="control-label"><?php echo _l('call_owner'); ?></label>         
				<select name="call_owner" id="" class="form-control">
					<option value="<?php echo get_staff_user_id() ?>"><?php echo get_staff_full_name() ?></option>
				</select>      
			</div>
			<div class="form-group">
				<label for="call_for" class="control-label"><?php echo _l('select_staff'); ?></label>
				<select id="call_for" name="call_for" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="pull-right">
				<input type="hidden" name="new_call_check" value="1">
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
				<button class="btn btn-danger btn_cancel" type="button">Cancel</button>
			</div>
		</div>
	</div>
	<?php echo form_close();?>
</div>