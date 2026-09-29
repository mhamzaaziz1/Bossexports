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
						<?php
						if ($log)
						{ ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="tw-flex tw-items-center tw-justify-between no-margin">
                                        <span>
                                            <i class="fa-solid fa-clipboard-check tw-mr-2"></i>
											<?php
											echo _l('wmm_maintenance_log').' #'.$log->id; ?>
											<?php
											if ($log->email_sent == 1)
											{ ?>
                                                <span class="label label-success tw-ml-2">
                                                    <i class="fa fa-check"></i> <?php
													echo _l('wmm_email_sent'); ?>
                                                </span>
												<?php
											} else
											{ ?>
                                                <span class="label label-warning tw-ml-2">
                                                    <i class="fa fa-times"></i> <?php
													echo _l('wmm_email_not_sent'); ?>
                                                </span>
												<?php
											} ?>
                                        </span>
                                        <a href="<?php
										echo admin_url('website_maintenance_management/maintenance_logs'); ?>" class="btn btn-default">
                                            <i class="fa-solid fa-arrow-left tw-mr-1"></i>
											<?php
											echo _l('back'); ?>
                                        </a>
                                    </h4>
                                    <hr class="hr-panel-heading"/>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Left Column - Main Content -->
                                <div class="col-md-8">
                                    <!-- Timer Alert (if maintenance is in progress) -->
									<?php
									if ( ! $log->is_completed && $log->start_time)
									{ ?>
                                        <div class="alert alert-warning tw-mb-4" id="timer-alert">
                                            <div class="tw-flex tw-items-center tw-justify-between">
                                                <div>
                                                    <h5 class="tw-mb-1 tw-font-semibold">
                                                        <i class="fa fa-clock fa-spin"></i> <?php
														echo _l('wmm_maintenance_in_progress'); ?>
                                                    </h5>
                                                    <p class="tw-mb-0">
														<?php
														echo _l('wmm_started_at'); ?>: <strong><?php
															echo _dt($log->start_time); ?></strong>
                                                    </p>
                                                    <p class="tw-mb-0 tw-mt-1">
														<?php
														echo _l('wmm_elapsed_time'); ?>: <strong id="elapsed-time">--:--:--</strong>
                                                    </p>
                                                </div>
												<?php
												if (staff_can('edit', 'website_maintenance_logs'))
												{ ?>
                                                    <div>
                                                        <button type="button" class="btn btn-danger" id="stop-timer-btn" onclick="stopMaintenanceTimer(<?php
														echo $log->id; ?>)">
                                                            <i class="fa fa-stop"></i> <?php
															echo _l('wmm_stop_timer'); ?>
                                                        </button>
                                                    </div>
													<?php
												} ?>
                                            </div>
                                        </div>
										<?php
									} else
									{ ?>
                                        <!-- Info Alert -->
                                        <div class="alert alert-info tw-mb-4">
                                            <p class="tw-mb-0 tw-font-medium">
												<?php
												if ($log->firstname && $log->lastname)
												{ ?>
													<?php
													echo _l('wmm_performed_by', '<span class="tw-font-normal">'.html_escape($log->firstname.' '.$log->lastname).'</span>'); ?>
                                                    <i class="fa-regular fa-clock tw-ml-2" data-toggle="tooltip"
                                                       data-title="<?php
												       echo html_escape(_dt($log->performed_at)); ?>"></i>
													<?php
												} else
												{ ?>
													<?php
													echo _l('task_created_at', '<span class="tw-font-normal">'.html_escape(_dt($log->performed_at)).'</span>'); ?>
													<?php
												} ?>
                                            </p>
                                        </div>
										<?php
									} ?>

                                    <!-- Project/Website Info -->
                                    <div class="tw-mb-4">
                                        <div><span class="tw-font-medium"><?php
												echo _l('wmm_project'); ?>:</span>
                                            <a href="<?php
											echo admin_url('projects/view/'.$log->project_id); ?>" target="_blank">
												<?php
												echo html_escape($log->project_name); ?>
                                            </a>
                                        </div>
										<?php
										if ($log->website_url)
										{ ?>
                                            <div><span class="tw-font-medium"><?php
													echo _l('wmm_website'); ?>:</span>
                                                <a href="<?php
												echo html_escape($log->website_url); ?>" target="_blank">
													<?php
													echo html_escape($log->website_url); ?>
                                                </a>
                                            </div>
											<?php
										} ?>
                                        <div><span class="tw-font-medium"><?php
												echo _l('wmm_customer'); ?>:</span>
                                            <a href="<?php
											echo admin_url('clients/client/'.$log->client_id); ?>" target="_blank">
												<?php
												echo html_escape($log->client_name); ?>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>
                                    <hr class="hr-10"/>

                                    <!-- Tasks Completed Section -->
                                    <h4 class="tw-font-semibold tw-text-base tw-mb-3">
										<?php
										echo _l('tasks'); ?>
                                    </h4>

									<?php
									if ( ! empty($tasks))
									{ ?>
                                        <div class="row">
											<?php
											foreach ($tasks as $task)
											{ ?>
                                                <div class="col-md-6">
                                                    <a href="<?php
													echo admin_url('website_maintenance_management/maintenance_tasks/view/'.$task['id']) ?>" class="checkbox checkbox-success">
                                                        <div>
                                                            <strong><?php
																echo html_escape($task['name']); ?></strong>
															<?php
															if ($task['description'])
															{ ?>
                                                                <br><small class="text-muted"><?php
																echo html_escape($task['description']); ?></small>
																<?php
															} ?>
                                                            <br><?php
															echo wmm_format_category($task['category_name'], $task['category_icon'], $task['category_color']); ?>
                                                        </div>
                                                    </a>
                                                </div>
												<?php
											} ?>
                                        </div>
										<?php
									} else
									{ ?>
                                        <p class="text-muted"><?php
											echo _l('wmm_no_tasks_completed'); ?></p>
										<?php
									} ?>

									<?php
									if ($log->notes)
									{ ?>
                                        <hr/>
                                        <h4 class="tw-font-semibold tw-text-base tw-mb-2"><?php
											echo _l('wmm_notes'); ?></h4>
                                        <div class="tc-content">
											<?php
											echo nl2br(html_escape($log->notes)); ?>
                                        </div>
										<?php
									} ?>

                                    <!-- Attachments Section -->
                                    <hr/>
                                    <h4 class="tw-font-semibold tw-text-base tw-mb-3">
                                        <i class="fa fa-paperclip tw-mr-1"></i>
										<?php
										echo _l('wmm_attachments'); ?>
                                    </h4>

                                    <!-- Existing Attachments -->
                                    <div id="log-attachments-list" class="tw-mb-4">
										<?php
										if ( ! empty($attachments))
										{ ?>
                                            <ul class="list-unstyled">
												<?php
												foreach ($attachments as $attachment)
												{ ?>
                                                    <li class="tw-flex tw-items-center tw-justify-between tw-py-2 tw-px-3 tw-bg-neutral-50 tw-rounded tw-mb-2"
                                                        data-attachment-id="<?php
													    echo $attachment['id']; ?>">
                                                        <div class="tw-flex tw-items-center">
                                                            <i class="fa fa-file tw-text-neutral-400 tw-mr-2"></i>
                                                            <span class="tw-text-sm">
                                                            <?php
                                                            echo html_escape($attachment['file_name']); ?>
                                                        </span>
                                                        </div>
                                                        <div class="tw-space-x-2">
                                                            <a href="<?php
															echo admin_url('website_maintenance_management/maintenance_logs/download_attachment/'.$attachment['id'].'/'.$log->id); ?>"
                                                               class="btn btn-xs btn-default"
                                                               data-toggle="tooltip"
                                                               data-title="<?php
															   echo _l('download'); ?>">
                                                                <i class="fa fa-download"></i>
                                                            </a>
															<?php
															if (staff_can('delete', 'website_maintenance_logs'))
															{ ?>
                                                                <a href="#"
                                                                   class="btn btn-xs btn-danger"
                                                                   onclick="deleteAttachment(<?php
																   echo $attachment['id']; ?>, <?php
																   echo $log->id; ?>); return false;"
                                                                   data-toggle="tooltip"
                                                                   data-title="<?php
																   echo _l('delete'); ?>">
                                                                    <i class="fa fa-trash"></i>
                                                                </a>
																<?php
															} ?>
                                                        </div>
                                                    </li>
													<?php
												} ?>
                                            </ul>
											<?php
											if (count($attachments) > 1)
											{ ?>
                                                <div class="tw-mt-2">
                                                    <a href="<?php
													echo admin_url('website_maintenance_management/maintenance_logs/download_all_attachments/'.$log->id); ?>"
                                                       class="btn btn-sm btn-default">
                                                        <i class="fa fa-download tw-mr-1"></i>
														<?php
														echo _l('wmm_download_all'); ?>
                                                    </a>
                                                </div>
												<?php
											} ?>
											<?php
										} else
										{ ?>
                                            <p class="text-muted tw-text-sm"><?php
												echo _l('wmm_no_attachments'); ?></p>
											<?php
										} ?>
                                    </div>
                                </div>

                                <!-- Right Column - Sidebar Info -->
                                <div class="col-md-4">
                                    <h4 class="task-info-heading tw-font-semibold tw-text-base tw-mb-0 tw-text-neutral-800">
                                        <i class="fa-regular fa-circle-question fa-fw fa-lg task-info-icon tw-text-neutral-500 -tw-ml-1.5 tw-mr-1"></i>
										<?php
										echo _l('wmm_maintenance_log').' '._l('task_info'); ?>
                                    </h4>
                                    <div class="clearfix"></div>

                                    <!-- Log ID -->
                                    <div class="task-info tw-mt-3">
                                        <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                            <i class="fa fa-hashtag fa-fw fa-lg task-info-icon"></i>
											<?php
											echo _l('id'); ?>:
                                            <span class="tw-text-neutral-800">#<?php
												echo $log->id; ?></span>
                                        </h5>
                                    </div>

                                    <!-- Performed Date -->
                                    <div class="task-info">
                                        <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                            <i class="fa-regular fa-calendar fa-fw fa-lg task-info-icon"></i>
											<?php
											echo _l('wmm_performed_at'); ?>:
                                            <span class="tw-text-neutral-800" data-toggle="tooltip"
                                                  data-title="<?php
											      echo html_escape(_dt($log->performed_at)); ?>">
                                                <?php
                                                echo time_ago($log->performed_at); ?>
                                            </span>
                                        </h5>
                                    </div>

                                    <!-- Time Spent (if completed) -->
									<?php
									if ($log->is_completed && $log->start_time && $log->end_time)
									{ ?>
                                        <div class="task-info">
                                            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                                <i class="fa fa-clock fa-fw fa-lg task-info-icon"></i>
												<?php
												echo _l('wmm_time_spent'); ?>:
                                                <span class="tw-text-neutral-800">
                                                <?php
                                                $hours   = floor($log->time_spent / 3600);
                                                $minutes = floor(($log->time_spent % 3600) / 60);
                                                echo $hours.'h '.$minutes.'m';
                                                ?>
                                            </span>
                                            </h5>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-ml-8">
												<?php
												echo _l('wmm_from'); ?>: <?php
												echo _dt($log->start_time); ?><br>
												<?php
												echo _l('wmm_to'); ?>: <?php
												echo _dt($log->end_time); ?>
                                            </p>
                                        </div>
										<?php
									} ?>

                                    <!-- Email Status -->
                                    <div class="task-info">
                                        <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                            <i class="fa fa-envelope fa-fw fa-lg task-info-icon"></i>
											<?php
											echo _l('wmm_email_status'); ?>:
											<?php
											if ($log->email_sent == 1)
											{ ?>
                                                <span class="label label-success">
                                                <i class="fa fa-check"></i> <?php
													echo _l('wmm_email_sent'); ?>
                                            </span>
												<?php
											} else
											{ ?>
                                                <span class="label label-warning">
                                                <i class="fa fa-times"></i> <?php
													echo _l('wmm_email_not_sent'); ?>
                                            </span>
												<?php
											} ?>
                                        </h5>
										<?php
										if ($log->email_sent == 1 && $log->email_sent_at)
										{ ?>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-ml-8">
												<?php
												echo time_ago($log->email_sent_at); ?>
                                            </p>
											<?php
										} ?>
                                    </div>

                                    <!-- Performed By -->
                                    <div class="task-info">
                                        <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                            <i class="fa fa-user fa-fw fa-lg task-info-icon"></i>
											<?php
											echo _l('wmm_performed_by'); ?>:
                                        </h5>
                                        <div class="task_users_wrapper tw-ml-8">
                                            <div class="task-user" data-toggle="tooltip"
                                                 data-title="<?php
											     echo html_escape($log->firstname.' '.$log->lastname); ?>">
                                                <a href="<?php
												echo admin_url('staff/profile/'.$log->performed_by); ?>" target="_blank">
													<?php
													echo staff_profile_image($log->performed_by, ['staff-profile-image-small']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Invoice Status -->
									<?php
									if ($log->is_completed)
									{ ?>
                                        <div class="task-info">
                                            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                                <i class="fa fa-file-invoice fa-fw fa-lg task-info-icon"></i>
												<?php
												echo _l('invoice'); ?>:
												<?php
												if ($log->invoice_id && $log->invoice_created)
												{ ?>
                                                    <a href="<?php
													echo admin_url('invoices/invoice/'.$log->invoice_id); ?>" target="_blank" class="label label-success">
														<?php
														echo format_invoice_number($log->invoice_id); ?>
                                                    </a>
													<?php
												} else
												{ ?>
                                                    <span class="label label-default">
                                                    <i class="fa fa-times"></i> <?php
														echo _l('wmm_not_invoiced'); ?>
                                                </span>
													<?php
												} ?>
                                            </h5>
                                        </div>

                                        <!-- Billable Info -->
										<?php
										if ($log->is_billable && $log->hourly_rate)
										{ ?>
                                            <div class="task-info">
                                                <p class="tw-text-sm tw-text-neutral-500 tw-ml-8">
                                                    <strong><?php
														echo _l('wmm_hourly_rate'); ?>:</strong> <?php
													echo app_format_money($log->hourly_rate, get_base_currency()); ?><br>
													<?php
													if ($log->time_spent)
													{
														$hours = round($log->time_spent / 3600, 2); ?>
                                                        <strong><?php
															echo _l('wmm_billable_hours'); ?>:</strong> <?php
														echo $hours; ?><?php
														echo _l('wmm_hours'); ?>
														<?php
													} ?>
                                                </p>
                                            </div>
											<?php
										} ?>
										<?php
									} ?>

                                    <!-- Package Info -->
									<?php
									if ($log->package_id && $log->package_name)
									{ ?>
                                        <div class="task-info">
                                            <h5 class="tw-inline-flex tw-items-center tw-space-x-1.5">
                                                <i class="fa fa-box fa-fw fa-lg task-info-icon"></i>
												<?php
												echo _l('wmm_support_package'); ?>:
                                            </h5>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-ml-8">
                                                <a href="<?php
												echo admin_url('website_maintenance_management/support_packages/view/'.$log->package_id); ?>" target="_blank">
                                                    <strong><?php
														echo html_escape($log->package_name); ?></strong>
                                                </a>
												<?php
												if ($log->deducted_from_package && $log->hours_consumed)
												{ ?>
                                                    <br>
                                                    <span class="text-success">
                                                    <i class="fa fa-check-circle"></i>
														<?php
														echo _l('wmm_hours_deducted'); ?>: <strong><?php
															echo $log->hours_consumed; ?> h</strong>
                                                </span>
													<?php
												} else
												{ ?>
                                                    <br>
                                                    <span class="text-muted">
                                                    <i class="fa fa-info-circle"></i> <?php
														echo _l('wmm_not_deducted'); ?>
                                                </span>
													<?php
												} ?>
                                            </p>
                                        </div>
										<?php
									} ?>

                                    <hr class="task-info-separator"/>

                                    <!-- Actions -->
									<?php
									if (staff_can('edit', 'website_maintenance_logs') || staff_can('delete', 'website_maintenance_logs') || (staff_can('create', 'invoices') && $log->is_completed && ! $log->invoice_created))
									{ ?>
                                        <div class="tw-space-y-2">
											<?php
											if (staff_can('edit', 'website_maintenance_logs') && $log->email_sent == 0)
											{ ?>
                                                <button type="button" class="btn btn-info btn-block" onclick="resendNotification(<?php
												echo $log->id; ?>)">
                                                    <i class="fa fa-envelope tw-mr-1"></i>
													<?php
													echo _l('wmm_send_email'); ?>
                                                </button>
												<?php
											} ?>

											<?php
											if (staff_can('create', 'invoices') && $log->is_completed && ! $log->invoice_created)
											{ ?>
                                                <button type="button" class="btn btn-success btn-block" onclick="createInvoiceFromLog(<?php
												echo $log->id; ?>)">
                                                    <i class="fa fa-file-invoice tw-mr-1"></i>
													<?php
													echo _l('wmm_create_invoice'); ?>
                                                </button>
												<?php
											} ?>

											<?php
											if (staff_can('edit', 'website_maintenance_logs') && $log->is_completed && $log->invoice_created && $log->invoice_id)
											{ ?>
                                                <button type="button" class="btn btn-warning btn-block" onclick="unlinkInvoiceFromLog(<?php
												echo $log->id; ?>)">
                                                    <i class="fa fa-unlink tw-mr-1"></i>
													<?php
													echo _l('wmm_unlink_invoice'); ?>
                                                </button>
												<?php
											} ?>

											<?php
											if (staff_can('delete', 'website_maintenance_logs'))
											{ ?>
                                                <a onclick="deleteLog(<?php
												echo $log->id; ?>); return false;"
                                                   class="btn btn-danger btn-block">
                                                    <i class="fa fa-trash tw-mr-1"></i>
													<?php
													echo _l('delete'); ?>
                                                </a>
												<?php
											} ?>
                                        </div>
										<?php
									} ?>
                                </div>
                            </div>
							<?php
						} else
						{ ?>
                            <div class="alert alert-warning">
								<?php
								echo _l('wmm_log_not_found'); ?>
                            </div>
							<?php
						} ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>
<script>
    // Timer functionality for in-progress maintenance
	<?php if ($log && ! $log->is_completed && $log->start_time) { ?>
    var startTime = new Date('<?php echo $log->start_time; ?>').getTime();
    var timerInterval = setInterval(function () {
        var now = new Date().getTime();
        var elapsed = Math.floor((now - startTime) / 1000);

        var hours = Math.floor(elapsed / 3600);
        var minutes = Math.floor((elapsed % 3600) / 60);
        var seconds = elapsed % 60;

        var display =
            (hours < 10 ? '0' : '') + hours + ':' +
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds;

        $('#elapsed-time').text(display);
    }, 1000);
	<?php } ?>

    function stopMaintenanceTimer(id) {
        if (confirm('<?php echo _l('wmm_confirm_stop_timer'); ?>')) {
            $('#stop-timer-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + '<?php echo _l('wmm_stopping'); ?>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/stop_timer/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    alert_float('danger', response.message);
                    $('#stop-timer-btn').prop('disabled', false).html('<i class="fa fa-stop"></i> <?php echo _l('wmm_stop_timer'); ?>');
                }
            }, 'json').fail(function () {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $('#stop-timer-btn').prop('disabled', false).html('<i class="fa fa-stop"></i> <?php echo _l('wmm_stop_timer'); ?>');
            });
        }
    }

    function resendNotification(id) {
        if (confirm('<?php echo _l('wmm_confirm_send_email'); ?>')) {
            var $btn = $('button[onclick*="resendNotification"]');
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('please_wait'); ?>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/resend_notification/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            }, 'json').fail(function() {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $btn.prop('disabled', false).html(originalHtml);
            });
        }
    }

    function createInvoiceFromLog(id) {
        if (confirm('<?php echo _l('wmm_confirm_create_invoice'); ?>')) {
            var $btn = $('button[onclick*="createInvoiceFromLog"]');
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('please_wait'); ?>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/create_invoice/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            }, 'json').fail(function() {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $btn.prop('disabled', false).html(originalHtml);
            });
        }
    }

    function unlinkInvoiceFromLog(id) {
        if (confirm('<?php echo _l('wmm_confirm_unlink_invoice'); ?>')) {
            var $btn = $('button[onclick*="unlinkInvoiceFromLog"]');
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('please_wait'); ?>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/unlink_invoice/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            }, 'json').fail(function() {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $btn.prop('disabled', false).html(originalHtml);
            });
        }
    }

    function deleteLog(id) {
        if (confirm_delete()) {
            var $btn = $('a[onclick*="deleteLog"]');
            var originalHtml = $btn.html();
            $btn.addClass('disabled').css('pointer-events', 'none').html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('please_wait'); ?>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/delete/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        window.location.href = admin_url + 'website_maintenance_management/maintenance_logs';
                    }, 500);
                } else {
                    alert_float('danger', response.message);
                    $btn.removeClass('disabled').css('pointer-events', 'auto').html(originalHtml);
                }
            }, 'json').fail(function() {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $btn.removeClass('disabled').css('pointer-events', 'auto').html(originalHtml);
            });
        }
    }

    function deleteAttachment(attachment_id, log_id) {
        if (confirm_delete()) {
            var $btn = $('a[onclick*="deleteAttachment(' + attachment_id + ')"]');
            var originalHtml = $btn.html();
            $btn.addClass('disabled').css('pointer-events', 'none').html('<i class="fa fa-spinner fa-spin"></i>');

            $.post(admin_url + 'website_maintenance_management/maintenance_logs/delete_attachment/' + attachment_id + '/' + log_id, function (response) {
                if (response.success) {
                    alert_float('success', '<?php echo _l('deleted', _l('wmm_attachment')); ?>');
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    alert_float('danger', '<?php echo _l('problem_deleting', _l('wmm_attachment')); ?>');
                    $btn.removeClass('disabled').css('pointer-events', 'auto').html(originalHtml);
                }
            }, 'json').fail(function() {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $btn.removeClass('disabled').css('pointer-events', 'auto').html(originalHtml);
            });
        }
    }

    init_selectpicker();
</script>
