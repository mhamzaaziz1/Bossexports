<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<h4 class="pull-left"><?php echo _l('si_sms')." - "._l('si_sms_templates'); ?></h4>
						<a href="#si_sms_template_modal" data-toggle="modal" class=" pull-right btn btn-info mleft4"><?php echo _l('si_sms_add'); ?></a>
						<div class="clearfix"></div>
						<hr />
						<table class="table dt-table scroll-responsive">
							<thead>
								<tr>
									<th width="5%">#</th>
									<th><?php echo _l('si_sms_template_name'); ?></th>
									<th><?php echo _l('si_sms_text'); ?></th>
									<th><?php echo _l('actions'); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							if(!empty($filter_templates)){
								$i=1;
								foreach($filter_templates as $row){?>
								<tr class="has-row-options">
									<td><?php echo ($i++);?></td>
									<td data-order="<?php echo ($row['template_name']); ?>">
										<span><?php echo  ($row['template_name']); ?></span>
									</td>
									<td><?php echo ($row['content']);?></td>
									<td>
										<a href="#si_sms_template_modal" class="si_template_edit" data-toggle="modal" data-id="<?php echo ($row['id']);?>" title="<?php echo _l('edit');?>"><i class="fa-regular fa-pen-to-square"></i></a>  <a href="del_sms_template/<?php echo ($row['id']);?>" class="confirm text-danger _delete" title="<?php echo _l('delete');?>"><i class="fa fa-trash"></i></a>
									</td>
								</tr>
								<?php } }?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="si_sms_template_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title hide"><?php echo _l('edit').' '._l('si_sms_templates'); ?></span>
					<span class="add-title hide"><?php echo _l('si_sms_add').' '._l('si_sms_templates'); ?></span>
				</h4>
			</div>
			<?php echo form_open('admin/si_sms/save_template',array('id'=>'si_sms_add_new_template')); ?>
			<div class="modal-body">
				<div class="row">
					<?php echo form_hidden('id',''); ?>
					<div class="col-md-12">
						<?php echo render_input('template_name','si_sms_template_name',''); ?>
					</div>
					<div class="col-md-12">
						<?php echo render_textarea('content','si_sms_text','');?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
<?php init_tail(); ?>
</body>
</html>
<script>
	var si_sms_alphanumeric_validation = '<?php echo _l('si_sms_alphanumeric_validation'); ?>';
</script>	
<script src="<?php echo module_dir_url('si_sms','assets/js/si_sms_templates.js'); ?>"></script>

