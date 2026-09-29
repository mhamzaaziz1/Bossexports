<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade task-modal-single" id="_call_modal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header task-single-header" data-task-single-id="<?php echo $call->id; ?>">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo $call->purpose_of_call; ?></h4>
			</div>
			<div class="modal-body">
				<?php #print_r($call); ?>
				<div class="row">
					<div class="col-md-6 call-single-col-left">
						<div class="clearfix"></div>
						<div class="row">
							<div class="panel_s">
								<div class="panel-heading">
									<h4 class="panel-title">Initial Call</h4>
								</div>
								<div class="panel-body">
									<h5><?php echo _l('summary'); ?></h5>
									<?php if(!empty($call->call_summary)){
										echo '<div class="tc-content"><div id="call_view_description">' .check_for_links($call->call_summary) .'</div></div>';
										} else {
										echo '<div class="no-margin tc-content task-no-description" id="call_view_description"><span class="text-muted">' . _l('call_no_summary') . '</span></div>';
									} ?>
									<hr class="mtop10 mbot10">
									<div class="clearfix"></div>
									<h5><?php echo _l('purpose_of_call'); ?></h5>
									<div class="clearfix"></div>
									<?php if(!empty($call->purpose_of_call)){
										echo '<div class="tc-content"><div id="call_view_purpose">' .check_for_links($call->purpose_of_call) .'</div></div>';
										} else {
										echo '<div class="no-margin tc-content task-no-description" id="call_view_purpose"><span class="text-muted">' . _l('call_no_purpose') . '</span></div>';
									} ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="panel-group" id="accordion" data-followups="">
							<?php echo get_call_log_followups($call->id); ?>
							</div>
						</div>
					</div>
					<div class="col-md-6 task-single-col-right">
						<div class="pull-right mbot10 task-single-menu task-menu-options">
							<div class="content-menu hide">
								<ul>
									<li>
										<a href="#" onclick="edit_task(<?php echo $call->id; ?>); return false;">
										<?php echo _l('task_single_edit'); ?>
										</a>
									</li>
									<li><a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover" data-content="" data-html="true"><?php echo _l('task_copy'); ?></span></a></li>
									<li>
										<a href="<?php echo admin_url('tasks/delete_task/'.$call->id); ?>" class="_delete task-delete">
										<?php echo _l('task_single_delete'); ?>
										</a>
									</li>
								</ul>
							</div>
							<?php if(has_permission('tasks','','delete') || has_permission('tasks','','edit') || has_permission('tasks','','create')){ ?>
							<?php 
								$task_modal_options = json_encode([
									'subject' => $call->purpose_of_call.' - '.$call->id,
									'description' => $call->call_summary,
									'assignee' => $call->call_for
								]);
								$popover_content = '<ul>
                                	<li><a href="#" onclick="new_task(); init_call_modal_task('.htmlspecialchars($task_modal_options).');return false;">Create a Task</a></li>
                                	<li><a href="#" onclick="return false;">Email call information</a></li>
                                 </ul>'; 
                            ?>
							<a href="#" onclick="return false;" class="trigger manual-popover mright5" data-toggle="popover" title="Actions" data-content="<?php echo htmlspecialchars($popover_content); ?>" data-placement="bottom" data-html="true">
							<i class="fa fa-circle-thin" aria-hidden="true"></i>
							<i class="fa fa-circle-thin" aria-hidden="true"></i>
							<i class="fa fa-circle-thin" aria-hidden="true"></i>
							</a>
							<?php } ?>
						</div>
						<h4 class="task-info-heading"><?php echo _l('call_info'); ?></h4>
						<div class="clearfix"></div>
						<h5 class="no-mtop task-info-created">
							<small class="text-dark"><?php echo '<b>'._l('call_created_at').'</b><span class="text-dark">'.date('M d, Y g:iA',strtotime($call->datecreated)).'</span>'; ?></small>
						</h5>
						<hr class="task-info-separator" />
						<div class="task-info task-info-billable">
							<h5><i class="fa fa-user task-info-icon fa-fw fa-lg pull-left"></i>
								<?php echo _l('customer'); ?>: <?= $call->customer; ?>
							</h5>
						</div>
						<div class="task-info task-info-billable">
							<h5><i class="fa fa-<?= ($call->direction == 'outbound') ? 'arrow-up' : 'arrow-down' ?> task-info-icon fa-fw fa-lg pull-left"></i>
								<?php echo _l('call_direction'); ?>: <?= ($call->direction == 'outbound') ? 'Outbound' : 'Inbound'; ?>
							</h5>
						</div>
						<div class="task-info task-info-billable">
							<h5><i class="fa fa-user task-info-icon fa-fw fa-lg pull-left"></i>
								<?php echo _l('call_for'); ?>: <?= $call->call_for['fullname']; ?>
							</h5>
						</div>
						<div class="task-info task-info-billable">
							<h5><i class="fa fa-clock-o task-info-icon fa-fw fa-lg pull-left"></i>
								<?php echo _l('call_duration'); ?>: <?= explode('.',$call->call_duration)[0] ?>
							</h5>
						</div>
						<div class="task-info task-info-billable">
							<h5><i class="fa fa-phone task-info-icon fa-fw fa-lg pull-left"></i>
								<?php echo _l('followp_required'); ?>: <?= $call->followup; ?>
							</h5>
						</div>
						<?php if ($call->followup == 'yes' || $call->followup == 'done'): ?>
							<div class="task-info task-info-billable">
								<h5><i class="fa fa-clock-o task-info-icon fa-fw fa-lg pull-left"></i>
									<?php echo _l('followup_time'); ?>: <?= date('M d, Y g:iA',strtotime($call->followup_time)); ?>
								</h5>
							</div>
							<?php if (!empty($call->followup_notes)): ?>
								<div class="task-info task-info-billable">
									<h5><i class="fa fa-pencil task-info-icon fa-fw fa-lg pull-left"></i>
										<?php echo _l('notes'); ?>: <?= $call->followup_notes; ?>
									</h5>
								</div>
							<?php endif ?>
						<?php endif; ?>
						<?php if (has_permission('call_logs','','create')): ?>
							<button class="btn btn-info mtop10" onclick="new_call_log('call_logs/call', <?php echo $call->id; ?>); return false;"><?php echo _l('make_followup_call'); ?></button>
	                    <?php endif ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>