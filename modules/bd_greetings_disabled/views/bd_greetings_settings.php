<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation"  class="active">
		<a href="#set_greetings_tab" aria-controls="set_greetings_tab" role="tab" data-toggle="tab"><?php echo _l('bd_greetings_settings_tab1'); ?></a>
	</li>
</ul>
<div class="tab-content mtop30">
	<div role="tabpanel" class="tab-pane  active" id="set_greetings_tab">
		<div class="row">
			<div class="col-md-12">
				<label><?php echo _l('bd_greetings_settings_contact_cust_field_label',"<a href='".admin_url('custom_fields')."'>"._l('custom_fields')."</a>"); ?></label>
			</div>
			<div class="col-md-6">
			<?php 
			$custom_fields = get_custom_fields('contacts');
			echo render_select('settings['.BD_GREETINGS_MODULE_NAME.'_contact_cust_field_bday]',$custom_fields,array('id','name'),'bd_greetings_settings_contact_cust_field_bday',get_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_bday'),array('data-none-selected-text'=>_l('dropdown_non_selected_tex')),array(),'no-margin');
			?>
			</div>
			<div class="col-md-6">
			<?php echo render_select('settings['.BD_GREETINGS_MODULE_NAME.'_contact_cust_field_anniversary]',$custom_fields,array('id','name'),'bd_greetings_settings_contact_cust_field_anniversary',get_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_anniversary'),array('data-none-selected-text'=>_l('dropdown_non_selected_tex')),array(),'no-margin'); ?>
			</div>
		</div>
		<hr/>
		<div class="row">
			<div class="col-md-6">
				<?php render_yes_no_option(BD_GREETINGS_MODULE_NAME.'_send_sms','bd_greetings_settings_send_sms'); ?>
			</div>
			<div class="col-md-6">
				<?php render_yes_no_option(BD_GREETINGS_MODULE_NAME.'_send_email','bd_greetings_settings_send_email'); ?>
			</div>
		</div>
		<hr/>
		<div class="row">
			<div class="col-md-12">
   				 <?php echo render_input('settings['.BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_hour]','bd_greetings_settings_trigger_auto_operations_hour',get_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_hour'),'number',array('data-toggle'=>'tooltip','data-title'=>_l('hour_of_day_perform_auto_operations_format'),'max'=>23)); ?>
			</div>
		</div>	
		<hr/>
		<div class="row">
			<div class="col-md-6">
				<?php $email_attachment = get_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_bday'); ?>
				<?php if($email_attachment != ''){ ?>
					<div class="row">
						<div class="col-md-9">
							<label for="bd_greetings_email_attachment_bday" class="control-label"><?php echo _l('bd_greetings_settings_email_attachment_bday'); ?></label>
							<img src="<?php echo base_url('modules/bd_greetings/uploads/'.$email_attachment); ?>" class="img img-responsive">
						</div>
						<?php if(has_permission('settings','','delete')){ ?>
							<div class="col-md-3 text-right">
								<a href="<?php echo admin_url('bd_greetings/remove_email_attachment/bday'); ?>" data-toggle="tooltip" title="<?php echo _l('delete')." "._l('bd_greetings_settings_email_attachment_bday'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
							</div>
						<?php } ?>
					</div>
					<div class="clearfix"></div>
				<?php } else { ?>
					<div class="form-group">
						<label for="bd_greetings_email_attachment_bday" class="control-label"><?php echo _l('bd_greetings_settings_email_attachment_bday'); ?></label>
						<input type="file" name="bd_greetings_email_attachment_bday" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('bd_greetings_settings_email_attachment_bday'); ?>">
					</div>
				<?php } ?>
			</div>
			<div class="col-md-6">
				<?php $email_attachment = get_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_anniversary'); ?>
				<?php if($email_attachment != ''){ ?>
					<div class="row">
						<div class="col-md-9">
							<label for="bd_greetings_email_attachment_anniversary" class="control-label"><?php echo _l('bd_greetings_settings_email_attachment_anniversary'); ?></label>
							<img src="<?php echo base_url('modules/bd_greetings/uploads/'.$email_attachment); ?>" class="img img-responsive">
						</div>
						<?php if(has_permission('settings','','delete')){ ?>
							<div class="col-md-3 text-right">
								<a href="<?php echo admin_url('bd_greetings/remove_email_attachment/anniversary'); ?>" data-toggle="tooltip" title="<?php echo _l('delete')." "._l('bd_greetings_settings_email_attachment_anniversary'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
							</div>
						<?php } ?>
					</div>
					<div class="clearfix"></div>
				<?php } else { ?>
					<div class="form-group">
						<label for="bd_greetings_email_attachment_anniversary" class="control-label"><?php echo _l('bd_greetings_settings_email_attachment_anniversary'); ?></label>
						<input type="file" name="bd_greetings_email_attachment_anniversary" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('bd_greetings_settings_email_attachment_anniversary'); ?>">
					</div>
				<?php } ?>
			</div>
		</div>
		<hr />
		<hr/>
		<div class="row">
			<div class="col-md-6">
				<h4 class="mbot15">
					<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php $trigger_type = ' SMS '; echo _l('edit').$trigger_type._l('triggers'); ?>"></i>
					<a href="<?php echo admin_url('settings?group=sms');?>"><?php echo _l('edit').$trigger_type._l('triggers'); ?></a>
				</h4>
			</div>
			<div class="col-md-6">
				<h4 class="mbot15">
					<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('edit').' '._l('email_templates'); ?>"></i>
					<a href="<?php echo admin_url('emails');?>"><?php echo _l('edit').' '._l('email_templates'); ?></a>
				</h4>
			</div>
		</div>
		
	</div>
</div>