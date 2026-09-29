<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/** @var object $package */

?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php
							echo _l('wmm_package_details'); ?> #<?php
							echo $package->id; ?>
                            <span class="pull-right">
                                <?php
                                $status_class = '';
                                switch ($package->status)
                                {
	                                case 'active':
		                                $status_class = 'success';
		                                break;
	                                case 'exhausted':
		                                $status_class = 'danger';
		                                break;
	                                case 'expired':
		                                $status_class = 'warning';
		                                break;
	                                case 'cancelled':
		                                $status_class = 'default';
		                                break;
                                }
                                ?>
                                <span class="label label-<?php
                                echo $status_class; ?>">
                                    <?php
                                    echo ucfirst($package->status); ?>
                                </span>
                            </span>
                        </h4>
                        <hr class="hr-panel-heading"/>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Package Information -->
                                <h4 class="bold"><?php
									echo _l('wmm_package_information'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                    <tr>
                                        <td width="30%" class="bold"><?php
											echo _l('wmm_package_name'); ?></td>
                                        <td><?php
											echo $package->package_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_customer'); ?></td>
                                        <td>
                                            <a href="<?php
											echo admin_url('clients/client/'.$package->client_id); ?>" target="_blank">
												<?php
												echo $package->client_name; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_website'); ?></td>
                                        <td>
											<?php
											if ($package->website_id)
											{ ?>
                                                <a href="<?php
												echo admin_url('website_maintenance_management/websites/view/'.$package->website_id); ?>">
													<?php
													echo $package->website_url ?: $package->project_name; ?>
                                                </a>
												<?php
											} else
											{ ?>
                                                <span class="text-muted"><?php
													echo _l('wmm_all_client_websites'); ?></span>
												<?php
											} ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_total_hours'); ?></td>
                                        <td><?php
											echo $package->total_hours; ?>&nbsp;<?php
											echo _l('wmm_hours'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_hours_used'); ?></td>
                                        <td><?php
											echo $package->hours_used; ?>&nbsp;<?php
											echo _l('wmm_hours'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_hours_remaining'); ?></td>
                                        <td>
                                                <span class="bold <?php
                                                echo $package->hours_remaining <= $package->low_balance_threshold ? 'text-danger' : 'text-success'; ?>">
                                                    <?php
                                                    echo $package->hours_remaining; ?>&nbsp;<?php
	                                                echo _l('wmm_hours'); ?>
                                                </span>
                                        </td>
                                    </tr>
									<?php
									if ($package->hourly_rate)
									{ ?>
                                        <tr>
                                            <td class="bold"><?php
												echo _l('wmm_hourly_rate'); ?></td>
                                            <td><?php
												echo app_format_money($package->hourly_rate, get_base_currency()); ?></td>
                                        </tr>
										<?php
									} ?>
									<?php
									if ($package->package_price)
									{ ?>
                                        <tr>
                                            <td class="bold"><?php
												echo _l('wmm_package_price'); ?></td>
                                            <td><?php
												echo app_format_money($package->package_price, get_base_currency()); ?></td>
                                        </tr>
										<?php
									} ?>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_start_date'); ?></td>
                                        <td><?php
											echo $package->start_date ? _d($package->start_date) : '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_expiry_date'); ?></td>
                                        <td>
											<?php
											if ($package->expiry_date)
											{ ?>
												<?php
												echo _d($package->expiry_date); ?>
												<?php
												if ($statistics->days_until_expiry !== NULL)
												{ ?>
                                                    <span class="text-muted">
                                                            (<?php
														echo $statistics->days_until_expiry > 0 ? $statistics->days_until_expiry.' '._l('wmm_days_remaining') : _l('expired'); ?>)
                                                        </span>
													<?php
												} ?>
												<?php
											} else
											{ ?>
                                                <span class="text-muted"><?php
													echo _l('wmm_no_expiry'); ?></span>
												<?php
											} ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php
											echo _l('wmm_created_by'); ?></td>
                                        <td>
											<?php
											echo $package->creator_firstname.' '.$package->creator_lastname; ?>
                                            <span class="text-muted">on <?php
												echo _dt($package->created_at); ?></span>
                                        </td>
                                    </tr>
									<?php
									if ($package->notes)
									{ ?>
                                        <tr>
                                            <td class="bold"><?php
												echo _l('wmm_notes'); ?></td>
                                            <td><?php
												echo nl2br($package->notes); ?></td>
                                        </tr>
										<?php
									} ?>
                                    </tbody>
                                </table>

								<?php
								if (staff_can('edit', 'website_maintenance_packages'))
								{ ?>
                                    <div class="btn-group">
                                        <a href="<?php
										echo admin_url('website_maintenance_management/support_packages'); ?>" class="btn btn-default">
                                            <i class="fa fa-arrow-left"></i> <?php
											echo _l('back'); ?>
                                        </a>
                                        <!--                                        <button type="button" class="btn btn-info" onclick="edit_package(<?php
										echo $package->id; ?>)">
                                            <i class="fa fa-edit"></i> <?php
										echo _l('edit'); ?>
                                        </button>-->
                                    </div>
									<?php
								} ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Statistics -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php
											echo _l('wmm_statistics'); ?></h4>
                                        <hr/>

                                        <!-- Usage Progress -->
                                        <div class="mb-3">
                                            <p class="text-muted mb-1"><?php
												echo _l('wmm_usage_progress'); ?></p>
                                            <div class="progress">
                                                <div class="progress-bar <?php
												echo $statistics->usage_percentage >= 90 ? 'progress-bar-danger' : ($statistics->usage_percentage >= 70 ? 'progress-bar-warning' : 'progress-bar-success'); ?>"
                                                     role="progressbar"
                                                     style="width: <?php
												     echo $statistics->usage_percentage; ?>%"
                                                     aria-valuenow="<?php
												     echo $statistics->usage_percentage; ?>"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100"
                                                     data-percent="<?php
												     echo $statistics->usage_percentage; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <table class="table table-condensed">
                                            <tbody>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_total_usages'); ?></td>
                                                <td class="text-right"><?php
													echo $statistics->usage_count; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="bold"><?php
													echo _l('wmm_avg_hours_per_usage'); ?></td>
                                                <td class="text-right"><?php
													echo $statistics->avg_hours_per_usage; ?> h
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage History -->
                        <hr/>
                        <div class="_buttons">
							<?php
							if (staff_can('create', 'website_maintenance_logs'))
							{ ?>
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#performMaintenanceModal">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
									<?php
									echo _l('wmm_log_maintenance'); ?>
                                </a>
								<?php
							} ?>
                        </div>
                        <h4 class="bold"><?php
							echo _l('wmm_usage_history'); ?></h4>
						<?php
						if (count($usage_history) > 0)
						{ ?>
                            <table class="table table-striped dt-table">
                                <thead>
                                <tr>
                                    <th><?php
										echo _l('wmm_log_id'); ?></th>
                                    <th><?php
										echo _l('wmm_website'); ?></th>
                                    <th><?php
										echo _l('wmm_hours_consumed'); ?></th>
                                    <th><?php
										echo _l('wmm_performed_by'); ?></th>
                                    <th><?php
										echo _l('wmm_performed_at'); ?></th>
                                    <th><?php
										echo _l('options'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								foreach ($usage_history as $usage)
								{ ?>
                                    <tr>
                                        <td>
                                            <a href="<?php
											echo admin_url('website_maintenance_management/maintenance_logs/view/'.$usage['log_id']); ?>">
                                                #<?php
												echo $usage['log_id']; ?>
                                            </a>
                                        </td>
                                        <td><?php
											echo $usage['website_url'] ?: $usage['project_name']; ?></td>
                                        <td><?php
											echo $usage['hours_consumed']; ?> h
                                        </td>
                                        <td><?php
											echo $usage['firstname'].' '.$usage['lastname']; ?></td>
                                        <td><?php
											echo _dt($usage['consumed_at']); ?></td>
                                        <td>
                                            <a href="<?php
											echo admin_url('website_maintenance_management/maintenance_logs/view/'.$usage['log_id']); ?>" class="btn btn-default btn-xs">
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
						} else
						{ ?>
                            <p class="text-muted"><?php
								echo _l('wmm_no_usage_history'); ?></p>
							<?php
						} ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Perform Maintenance Modal -->
    <div class="modal fade" id="performMaintenanceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><?php
						echo _l('wmm_log_maintenance'); ?></h4>
                </div>
				<?php
				echo form_open(admin_url('website_maintenance_management/maintenance_logs/log'), ['id' => 'perform-maintenance-form']); ?>
                <div class="modal-body">
                    <!-- Maintenance Type Selection -->
                    <div class="form-group">
                        <label><?php
							echo _l('wmm_maintenance_type'); ?></label>
                        <div class="radio radio-primary">
                            <input type="radio" name="is_completed" id="type_in_progress" value="0" checked>
                            <label for="type_in_progress">
                                <strong><?php
									echo _l('wmm_start_new_maintenance'); ?></strong>
                                <br><small class="text-muted"><?php
									echo _l('wmm_start_new_maintenance_help'); ?></small>
                            </label>
                        </div>
                        <div class="radio radio-primary">
                            <input type="radio" name="is_completed" id="type_completed" value="1">
                            <label for="type_completed">
                                <strong><?php
									echo _l('wmm_log_completed_maintenance'); ?></strong>
                                <br><small class="text-muted"><?php
									echo _l('wmm_log_completed_maintenance_help'); ?></small>
                            </label>
                        </div>
                    </div>

                    <!-- Time Fields (for completed maintenance) -->
                    <div id="time-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
								<?php
								echo render_datetime_input('start_time', 'wmm_start_time'); ?>
                            </div>
                            <div class="col-md-6">
								<?php
								echo render_datetime_input('end_time', 'wmm_end_time'); ?>
                            </div>
                        </div>
                        <hr/>
                    </div>

                    <div class="form-group">
                        <label for="website_id"><?php
							echo _l('wmm_select_website'); ?> <span class="text-danger">*</span></label>
                        <select name="website_id" id="website_id" class="selectpicker form-control" data-width="100%" data-live-search="true" required>
                            <option value=""><?php
								echo _l('dropdown_non_selected_tex'); ?></option>
							<?php
							foreach ($website_options as $website)
							{ ?>
                                <option value="<?= $website['id']; ?>"
									<?php
									echo($package->website_id == $website['id'] ? 'selected' : '') ?>
                                        data-client-id="<?= $website['client_id']; ?>">
									<?php
									echo $website['name'].'  - '._l('customer').': '.html_escape($website['client_name']) ?>
                                </option>
								<?php
							} ?>
                        </select>
                    </div>

                    <div id="package-content">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group" id="package-select-group">
                                    <input name="package_id" id="package_id" type="hidden" value="<?= $package->id ?>">
                                    <label for="package_id_test"><?php
										echo _l('wmm_select_package'); ?></label>
                                    <select disabled readonly id="package_id_test" class="form-control" data-width="100%">
                                        <option selected disabled hidden><?php
											echo $package->package_name ?></option>
                                    </select>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="hidden" name="deduct_from_package" value="1"></input>
                                    <input type="checkbox" checked disabled>
                                    <label for="deduct_from_package">
										<?php
										echo _l('wmm_deduct_hours'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Selection -->
                    <div class="form-group">
                        <label for="task_ids"><?php
							echo _l('wmm_select_tasks'); ?></label>
                        <select name="task_ids[]" id="task_ids" class="selectpicker" multiple data-width="100%" data-live-search="true">
							<?php
							foreach ($active_tasks as $task)
							{
								echo '<option value="'.$task['id'].'">'.html_escape($task['name']).'</option>';
							}
							?>
                        </select>
                    </div>

					<?php
					echo render_textarea('notes', 'wmm_notes'); ?>

                    <!-- Billable Section (for completed maintenance) -->
                    <div id="billable_section" style="display: none;">
                        <hr/>
                        <h4 class="bold"><?php
							echo _l('wmm_billing_options'); ?></h4>

                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="is_billable" id="is_billable" value="1">
                            <label for="is_billable"><?php
								echo _l('wmm_is_billable'); ?></label>
                        </div>

                        <div id="hourly_rate_section" style="display: none;">
                            <div class="form-group">
                                <label for="hourly_rate"><?php
									echo _l('wmm_hourly_rate'); ?></label>
                                <input type="number" class="form-control" name="hourly_rate" id="hourly_rate"
                                       step="0.01" min="0" placeholder="<?php
								echo _l('wmm_hourly_rate_placeholder'); ?>">
                                <small class="text-muted"><?php
									echo _l('wmm_hourly_rate_help'); ?></small>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <div class="checkbox checkbox-primary" id="send_email_checkbox">
                        <input type="checkbox" name="send_email" id="send_email" value="1">
                        <label for="send_email"><?php
							echo _l('wmm_send_email_notification'); ?></label>
                    </div>

                    <div class="checkbox checkbox-primary" id="create_invoice_checkbox" style="display: none;">
                        <input type="checkbox" name="create_invoice" id="create_invoice" value="1">
                        <label for="create_invoice"><?php
							echo _l('wmm_create_invoice'); ?></label>
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
</div>

<?php
init_tail(); ?>

<script>
    $(function () {

        // Handle package selection
        $('body').on('change', '#package_id', function () {
            var packageId = $(this).val();
            var hoursRemaining = $(this).find('option:selected').data('hours');

            if (packageId && hoursRemaining !== undefined) {
                $('#package-balance-label').show();
                $('#package-balance-hours').text(hoursRemaining);

                // Show warning if balance is low
                if (hoursRemaining <= 2) {
                    $('#package-warning').show();
                    $('#package-warning-text').text('<?php echo _l('wmm_low_balance'); ?>: ' + hoursRemaining + ' hours remaining');
                } else {
                    $('#package-warning').hide();
                }

                // Auto-check deduct checkbox
                $('#deduct_from_package').prop('checked', true);

                // Show package info
                var packageInfo = '<?php echo _l('wmm_current_balance'); ?>: ' + hoursRemaining + ' <?php echo _l('wmm_hours'); ?>';
                $('#package-info').text(packageInfo).show();
            } else {
                $('#package-balance-label').hide();
                $('#package-warning').hide();
                $('#package-info').hide();
                $('#deduct_from_package').prop('checked', false);
            }
        });

        // Handle maintenance type selection
        $('input[name="is_completed"]').on('change', function () {
            if ($(this).val() == '1') {
                // Completed maintenance - show time fields, billable section, and create invoice checkbox
                $('#time-fields').slideDown();
                $('#billable_section').slideDown();
                $('#create_invoice_checkbox').slideDown();
                $('#send_email').prop('checked', true);
            } else {
                // In progress - hide time fields, billable section, and create invoice checkbox
                $('#time-fields').slideUp();
                $('#billable_section').slideUp();
                $('#create_invoice_checkbox').slideUp();
                $('#send_email').prop('checked', false);
                // Reset billable fields
                $('#is_billable').prop('checked', false);
                $('#hourly_rate_section').hide();
                $('#create_invoice').prop('checked', false);
            }
        });

        // Handle billable checkbox
        $('#is_billable').on('change', function () {
            if ($(this).is(':checked')) {
                $('#hourly_rate_section').slideDown();
            } else {
                $('#hourly_rate_section').slideUp();
                $('#hourly_rate').val('');
            }
        });

        // Form submission
        $('#perform-maintenance-form').on('submit', function (e) {
            e.preventDefault();

            // Validate time fields if completed maintenance
            if ($('#type_completed').is(':checked')) {
                var start_time = $('input[name="start_time"]').val();
                var end_time = $('input[name="end_time"]').val();

                if (!start_time || !end_time) {
                    alert_float('danger', '<?php echo _l('wmm_please_enter_start_end_time'); ?>');
                    return false;
                }

                // Check if end time is after start time
                if (new Date(end_time) <= new Date(start_time)) {
                    alert_float('danger', '<?php echo _l('wmm_end_time_must_be_after_start'); ?>');
                    return false;
                }
            }

            var formData = $(this).serialize();
            const submitButton = $(this).find('button[type=submit]');
            $(submitButton).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>')
            $.post($(this).attr('action'), formData, function (response) {
                if (response.success) {
                    $('#performMaintenanceModal').modal('hide');
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 2000)
                } else {
                    alert_float('danger', response.message);
                }
                $(submitButton).removeAttr('disabled').html('<?php echo _l('submit') ?>')
            }, 'json').fail(function () {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $(submitButton).removeAttr('disabled').html('<?php echo _l('submit') ?>')
            });
        });


    });
</script>
</body>
</html>