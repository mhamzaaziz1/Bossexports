<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <span class="tw-text-neutral-700">
                                        <?php
                                        echo html_escape($task->name); ?>
                                    </span>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
								<?php
								if (staff_can('edit', 'website_maintenance_tasks'))
								{ ?>
                                    <a href="#" class="btn btn-primary" onclick="editTask(<?php
									echo $task->id; ?>); return false;">
                                        <i class="fa fa-edit"></i> <?php
										echo _l('edit'); ?>
                                    </a>
									<?php
								} ?>
								<?php
								if (staff_can('delete', 'website_maintenance_tasks'))
								{ ?>
                                    <a href="#" class="btn btn-danger" onclick="deleteTask(<?php
									echo $task->id; ?>); return false;">
                                        <i class="fa fa-trash"></i> <?php
										echo _l('delete'); ?>
                                    </a>
									<?php
								} ?>
                                <a href="<?php
								echo admin_url('website_maintenance_management/maintenance_tasks'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php
									echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr/>

                        <div class="row">
                            <!-- Left Column - Main Content -->
                            <div class="col-md-8">
                                <!-- Description -->
								<?php
								if ($task->description)
								{ ?>
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h5><i class="fa fa-align-left"></i> <?php
												echo _l('wmm_description'); ?></h5>
                                            <div class="mtop10">
												<?php
												echo nl2br(html_escape($task->description)); ?>
                                            </div>
                                        </div>
                                    </div>
									<?php
								} ?>

                                <!-- Maintenance History -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h5><i class="fa fa-history"></i> <?php
											echo _l('wmm_maintenance_history'); ?></h5>
										<?php
										if (empty($task->maintenance_logs))
										{ ?>
                                            <p class="text-muted text-center mtop10"><?php
												echo _l('wmm_never_performed'); ?></p>
											<?php
										} else
										{ ?>
                                            <table class="table table-striped mtop10">
                                                <thead>
                                                <tr>
                                                    <th><?php
														echo _l('wmm_website'); ?></th>
                                                    <th><?php
														echo _l('wmm_performed_by'); ?></th>
                                                    <th><?php
														echo _l('wmm_performed_at'); ?></th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
												<?php
												foreach ($task->maintenance_logs as $log)
												{ ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php
																echo html_escape($log['client_name']); ?></strong><br>
                                                            <small><?php
																echo html_escape($log['project_name']); ?></small>
                                                        </td>
                                                        <td><?php
															echo html_escape($log['firstname'].' '.$log['lastname']); ?></td>
                                                        <td><?php
															echo _dt($log['performed_at']); ?></td>
                                                        <td>
                                                            <a href="<?php
															echo admin_url('website_maintenance_management/maintenance_logs/view/'.$log['id']); ?>" class="btn btn-xs btn-default">
                                                                <i class="fa fa-eye"></i> <?php
																echo _l('view'); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
													<?php
												} ?>
                                                </tbody>
                                            </table>
											<?php
										} ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Sidebar -->
                            <div class="col-md-4">
                                <!-- Assignees -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h5><?php
											echo _l('wmm_assignees'); ?></h5>
										<?php
										if (empty($task->assignees))
										{ ?>
                                            <p class="text-muted"><?php
												echo _l('wmm_no_assignees'); ?></p>
											<?php
										} else
										{ ?>
                                            <div class="mtop10">
                                                <div class="task_users_wrapper tw-ml-8">

													<?php
													foreach ($task->assignees as $assignee)
													{ ?>
                                                        <div class="media mtop5">
                                                            <div class="media-left">
                                                                <a href="<?php
																echo admin_url('staff/profile/'.$assignee['staffid']) ?>" target="_blank">
																	<?php
																	echo staff_profile_image($assignee['staffid'], ['class' => 'media-object img-small staff-profile-image-small', 'width' => '30']); ?> </a>
                                                            </div>
                                                            <div class="media-body">
																<?php
																echo html_escape($assignee['full_name']); ?>
                                                            </div>
                                                        </div>
														<?php
													} ?>
                                                </div>

                                            </div>
											<?php
										} ?>
                                    </div>
                                </div>

                                <!-- Task Details -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h5><?php
											echo _l('wmm_task_details'); ?></h5>
                                        <table class="table table-sm">
                                            <tbody>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_category'); ?></td>
                                                <td><?php
													echo $task->category?->name ?? '' ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_priority'); ?></td>
                                                <td>
													<?php
													$priority_class = '';
													switch ($task->priority)
													{
														case 'urgent':
															$priority_class = 'danger';
															break;
														case 'high':
															$priority_class = 'warning';
															break;
														case 'medium':
															$priority_class = 'info';
															break;
														default:
															$priority_class = 'default';
													}
													?>
                                                    <span class="label label-<?php
													echo $priority_class; ?>">
                                                            <?php
                                                            echo $this->maintenance_tasks_model->get_priority_name($task->priority); ?>
                                                        </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_is_active'); ?></td>
                                                <td>
													<?php
													if ($task->is_active)
													{ ?>
                                                        <span class="label label-success"><?php
															echo _l('wmm_active'); ?></span>
														<?php
													} else
													{ ?>
                                                        <span class="label label-default"><?php
															echo _l('wmm_inactive'); ?></span>
														<?php
													} ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_created_by'); ?></td>
                                                <td><?php
													echo html_escape($task->creator->firstname.' '.$task->creator->lastname); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_created_at'); ?></td>
                                                <td><?php
													echo _dt($task->created_at); ?></td>
                                            </tr>
											<?php
											if ($task->updated_at)
											{ ?>
                                                <tr>
                                                    <td class="bold"><?php
														echo _l('wmm_last_updated'); ?></td>
                                                    <td><?php
														echo _dt($task->updated_at); ?></td>
                                                </tr>
												<?php
											} ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php
					echo _l('wmm_maintenance_task'); ?></h4>
            </div>
			<?php
			echo form_open(admin_url('website_maintenance_management/maintenance_tasks/save/'.$task->id), ['id' => 'task-form']); ?>
            <div class="modal-body">
                <input type="hidden" name="id" id="task_id" value="<?php
				echo $task->id; ?>">
                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_input('name', 'wmm_task_name', $task->name, 'text', ['required' => TRUE]); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
						<?php
						echo render_select('category', $categories, ['id', 'name'], 'wmm_category', $task->category->id ?? '', [], [], '', '', FALSE);
						?>
                    </div>
                    <div class="col-md-6">
						<?php
						$priorities = [
							['id' => 'low', 'name' => _l('wmm_priority_low')],
							['id' => 'medium', 'name' => _l('wmm_priority_medium')],
							['id' => 'high', 'name' => _l('wmm_priority_high')],
							['id' => 'urgent', 'name' => _l('wmm_priority_urgent')],
						];
						echo render_select('priority', $priorities, ['id', 'name'], 'wmm_priority', 'medium', [], [], '', '', FALSE);
						?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_select('assignees[]', $staff_array, ['id', 'name'], 'wmm_assignees', $assignees, ['multiple' => TRUE], [], '', '', FALSE);
						?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_textarea('description', 'wmm_description', $task->description); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php
							echo($task->is_active ? 'checked' : '') ?>>
                            <label for="is_active"><?php
								echo _l('wmm_is_active'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php
					echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php
					echo _l('submit'); ?></button>
            </div>
			<?php
			echo form_close(); ?>
        </div>
    </div>
</div>

<?php
init_tail(); ?>

<script>
    function editTask(id) {
        $.get(admin_url + 'website_maintenance_management/maintenance_tasks/get/' + id, function (response) {

            $('#taskModal').modal('show');
        });
    }

    function deleteTask(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/maintenance_tasks/delete/' + id, function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    alert_float('success', data.message);
                    window.location.href = admin_url + 'website_maintenance_management/maintenance_tasks';
                } else {
                    alert_float('danger', data.message);
                }
            });
        }
    }

    appValidateForm($('#task-form'), {
        name: 'required',
        category: 'required',
        priority: 'required',
        'assignees[]': 'required',
    })
</script>
