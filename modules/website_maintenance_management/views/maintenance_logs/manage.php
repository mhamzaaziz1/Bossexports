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
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <!-- Table View -->
						<?php
						render_datatable([
							_l('#'),
							_l('wmm_customer'),
							_l('wmm_project'),
							_l('wmm_website'),
							_l('wmm_performed_by'),
							_l('wmm_performed_at'),
							_l('wmm_status'),
							_l('wmm_email_status'),
							_l('options'),
						], 'maintenance-logs'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Log Modal -->
<div class="modal fade" id="maintenance-log-modal" tabindex="-1" role="dialog" aria-labelledby="maintenanceLogModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" data-log-id="">
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
                            <option value="<?php
							echo $website['id']; ?>"
                                    data-client-id="<?php
							        echo $website['client_id']; ?>">
								<?php
								echo $website['name'].'  - '._l('customer').': '.html_escape($website['client_name']) ?>
                            </option>
							<?php
						} ?>
                    </select>
                </div>
				<?php
				// Include package selector component if user has permission
				if (staff_can('view', 'website_maintenance_packages'))
				{
					include(dirname(__FILE__).'/../support_packages/package_selector.php');
				} ?>


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

<?php
init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-maintenance-logs', admin_url + 'website_maintenance_management/maintenance_logs/table', [8], [8], {}, [5, 'desc']);

        // Check if URL has log ID (for direct links)
        var log_id = getUrlParameter('log_id');
        if (log_id) {
            init_maintenance_log_modal(log_id);
        }


        $('#website_id').on('change', function () {
            var websiteId = $(this).val();
            var clientId = $(this).find('option:selected').data('client-id');
            console.log($(this).find('option:selected').data());
            if (websiteId) {
                $('#tasks-section').hide();
                $('#loading-tasks').show();
                $('#submit-maintenance').hide();

                // Load packages for selected website
                if (typeof loadPackagesForWebsite === 'function') {
                    loadPackagesForWebsite(websiteId, clientId);
                }
            } else {
                $('#tasks-section').hide();
                $('#submit-maintenance').hide();
            }
        });

        // Clear log_id from URL when modal closes
        $('#maintenance-log-modal').on('hidden.bs.modal', function () {
            var url = admin_url + 'website_maintenance_management/maintenance_logs';
            if (typeof (history.pushState) != 'undefined') {
                history.pushState(null, '', url);
            }
            $(this).find('.modal-content').html('').attr('data-log-id', '');
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

        // Reset form when modal closes
        $('#performMaintenanceModal').on('hidden.bs.modal', function () {
            $('#perform-maintenance-form')[0].reset();
            $('#time-fields').hide();
            $('.selectpicker').selectpicker('refresh');
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
                    $('.table-maintenance-logs').DataTable().ajax.reload();

                    // If maintenance is in progress, show modal to view it
                    if ($('#type_in_progress').is(':checked') && response.log_id) {
                        setTimeout(function () {
                            init_maintenance_log_modal(response.log_id);
                        }, 500);
                    }
                } else {
                    alert_float('danger', response.message);
                }
                $(submitButton).removeAttr('disabled').html('<?php echo _l('submit') ?>')
            }, 'json').fail(function () {
                alert_float('danger', '<?php echo _l('error_occurred'); ?>');
                $(submitButton).removeAttr('disabled').html('<?php echo _l('submit') ?>')
            });
        });


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

        // Handle deduct checkbox
        $('body').on('change', '#deduct_from_package', function () {
            if ($(this).is(':checked') && !$('#package_id').val()) {
                alert('<?php echo _l('wmm_select_package'); ?>');
                $(this).prop('checked', false);
            }
        });
    });


    let activePackages = [];
    let selectedWebsiteClientId = null;

    function init_maintenance_log_modal(id = '') {
        if (id === '') {
            return;
        }

        var url = admin_url + 'website_maintenance_management/maintenance_logs/view/' + id;

        $.get(url, function (response) {
            $('#maintenance-log-modal .modal-content').html(response);
            $('#maintenance-log-modal .modal-content').attr('data-log-id', id);

            // Open modal
            $('#maintenance-log-modal').modal('show');

            // Update URL without reload
            if (typeof (history.pushState) != 'undefined') {
                var state_url = admin_url + 'website_maintenance_management/maintenance_logs?log_id=' + id;
                history.pushState({log_id: id}, '', state_url);
            }
        }).fail(function (response) {
            alert_float('danger', response.responseText);
        });
    }

    function viewLog(id) {
        init_maintenance_log_modal(id);
    }

    function deleteLog(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/maintenance_logs/delete/' + id, function (response) {
                var data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    $('#maintenance-log-modal').modal('hide');
                    alert_float('success', data.message);
                    $('.table-maintenance-logs').DataTable().ajax.reload();
                } else {
                    alert_float('danger', data.message);
                }
            }).fail(function () {
                alert_float('danger', '<?php echo _l('problem_deleting', _l('wmm_maintenance_log')); ?>');
            });
        }
    }

    function resendNotification(id) {
        if (confirm('<?php echo _l('wmm_confirm_resend_email'); ?>')) {
            $.post(admin_url + 'website_maintenance_management/maintenance_logs/resend_notification/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('.table-maintenance-logs').DataTable().ajax.reload();
                    // Reload modal if open
                    if ($('#maintenance-log-modal').hasClass('in')) {
                        init_maintenance_log_modal(id);
                    }
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        }
    }

    function createInvoiceFromLog(id) {
        if (confirm('<?php echo _l('wmm_confirm_create_invoice'); ?>')) {
            alert_float('success', '<?php echo _l('wmm_generating_invoice') ?>');
            $(this).attr('disabled', true);
            $.post(admin_url + 'website_maintenance_management/maintenance_logs/create_invoice/' + id, function (response) {
                setTimeout(function () {
                    if (response.success) {
                        alert_float('success', response.message);
                        $('.table-maintenance-logs').DataTable().ajax.reload();
                        // Reload modal if open
                        if ($('#maintenance-log-modal').hasClass('in')) {
                            init_maintenance_log_modal(id);
                        }
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 1000);
            }, 'json');
            $(this).removeAttr('disabled');
        }
    }

    function unlinkInvoiceFromLog(id) {
        if (confirm('<?php echo _l('wmm_confirm_unlink_invoice'); ?>')) {
            $.post(admin_url + 'website_maintenance_management/maintenance_logs/unlink_invoice/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('.table-maintenance-logs').DataTable().ajax.reload();
                    // Reload modal if open
                    if ($('#maintenance-log-modal').hasClass('in')) {
                        init_maintenance_log_modal(id);
                    }
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        }
    }

    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Load packages when website is selected
    function loadPackagesForWebsite(websiteId, clientId) {
        if (!websiteId) {
            $('#package-section').hide();
            return;
        }

        $('#package-loading').show();
        $('#package-content').hide();
        $('#package-section').show();

        // If clientId is provided directly, use it. Otherwise fetch website details
        if (clientId) {
            selectedWebsiteClientId = clientId;
            loadActivePackages(clientId, websiteId);
        } else {
            // Fallback: Get website details to get client_id
            $.ajax({
                url: admin_url + 'website_maintenance_management/websites/get_website/' + websiteId,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.website) {
                        selectedWebsiteClientId = response.website.client_id;
                        loadActivePackages(response.website.client_id, websiteId);
                    } else {
                        $('#package-section').hide();
                    }
                },
                error: function () {
                    $('#package-section').hide();
                }
            });
        }
    }

    function loadActivePackages(clientId, websiteId) {
        $.ajax({
            url: admin_url + 'website_maintenance_management/support_packages/get_active_packages',
            type: 'POST',
            data: {
                client_id: clientId,
                website_id: websiteId
            },
            dataType: 'json',
            success: function (response) {
                $('#package-loading').hide();
                $('#package-content').show();

                if (response.success && response.packages && response.packages.length > 0) {
                    activePackages = response.packages;
                    populatePackageSelect(response.packages);
                    $('#no-packages-alert').hide();
                    $('#package-select-group').show();
                } else {
                    activePackages = [];
                    $('#package-select-group').hide();
                    $('#no-packages-alert').show();
                    $('#deduct_from_package').prop('checked', false).prop('disabled', true);
                }
            },
            error: function () {
                $('#package-loading').hide();
                $('#package-content').show();
                $('#no-packages-alert').show();
            }
        });
    }

    function populatePackageSelect(packages) {
        var select = $('#package_id');
        select.html('<option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>');

        $.each(packages, function (i, package) {
            var label = package.package_name + ' (' + package.hours_remaining + 'h ' + '<?php echo _l('wmm_hours_remaining'); ?>)';
            if (package.website_id == null) {
                label += ' - <?php echo _l('wmm_all_client_websites'); ?>';
            }
            select.append('<option value="' + package.id + '" data-hours="' + package.hours_remaining + '">' + label + '</option>');
        });

        select.selectpicker('refresh');
        $('#deduct_from_package').prop('disabled', false);
    }

</script>
</body>

</html>