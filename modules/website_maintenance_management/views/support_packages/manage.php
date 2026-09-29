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
							if (staff_can('create', 'website_maintenance_packages'))
							{ ?>
                                <a href="#" class="btn btn-primary" onclick="add_package(); return false;">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
									<?php
									echo _l('wmm_add_new_package'); ?>
                                </a>
								<?php
							} ?>
							<?php
							if (staff_can('view', 'website_maintenance_packages'))
							{ ?>
                                <a href="<?php
								echo admin_url('website_maintenance_management/support_packages/export_csv'); ?>" class="btn btn-default">
                                    <i class="fa fa-download tw-mr-1"></i>
									<?php
									echo _l('export_excel'); ?>
                                </a>
								<?php
							} ?>
                        </div>
                        <hr class="hr-panel-heading"/>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold" id="total_active_packages">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_active_packages'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold" id="total_hours_remaining">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_total_hours_remaining'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold text-warning" id="low_balance_count">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_low_balance_packages'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold text-danger" id="exhausted_packages">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_exhausted_packages'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Packages Table -->
						<?php
						render_datatable([
							_l('id'),
							_l('wmm_package_name'),
							_l('wmm_customer'),
							_l('wmm_website'),
							_l('wmm_total_hours'),
							_l('wmm_hours_used'),
							_l('wmm_hours_remaining'),
							_l('wmm_status'),
							_l('wmm_expiry_date'),
							_l('options'),
						], 'support-packages'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Package Modal -->
<div class="modal fade" id="package_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="add-title"><?php
	                    echo _l('wmm_add_new_package'); ?></span>
                    <span class="edit-title hide"><?php
						echo _l('wmm_edit_package'); ?></span>
                </h4>
            </div>
			<?php
			echo form_open('', ['id' => 'package_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_input('package_name', 'wmm_package_name', '', 'text', ['required' => TRUE]); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group select-placeholder">
                            <label for="client_id"
                                   class="control-label"><?= _l('project_customer'); ?></label>
                            <select id="client_id" name="client_id" data-live-search="true"
                                    data-width="100%"
                                    class="ajax-search">
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="package_scope"><?php
								echo _l('wmm_package_scope'); ?></label>
                            <select name="package_scope" id="package_scope" class="selectpicker" data-width="100%">
                                <option value="client"><?php
									echo _l('wmm_all_client_websites'); ?></option>
                                <option value="website"><?php
									echo _l('wmm_specific_website'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" id="website_row" style="display:none;">
                    <div class="col-md-12">
						<?php
						echo render_select('website_id', [], ['id', ['website_url', 'project_name']], 'wmm_website', '', [], [], '', '', FALSE); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
						<?php
						echo render_input('total_hours', 'wmm_total_hours', '', 'number', ['step' => '0.01', 'min' => '0', 'required' => TRUE]); ?>
                    </div>
                    <div class="col-md-4">
						<?php
						echo render_input('hourly_rate', 'wmm_hourly_rate', '', 'number', ['step' => '0.01', 'min' => '0']); ?>
                    </div>
                    <div class="col-md-4">
						<?php
						echo render_input('package_price', 'wmm_package_price', '', 'number', ['step' => '0.01', 'min' => '0']); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
						<?php
						echo render_date_input('start_date', 'wmm_start_date'); ?>
                    </div>
                    <div class="col-md-4">
						<?php
						echo render_date_input('expiry_date', 'wmm_expiry_date'); ?>
                    </div>
                    <div class="col-md-4">
						<?php
						echo render_input('low_balance_threshold', 'wmm_low_balance_threshold', '2', 'number', ['step' => '0.01', 'min' => '0']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mbot20">
                        <div class="checkbox checkbox-info mtop20 no-mbot">
                            <input type="checkbox" name="low_balance_notify"
                                   value="1" id="low_balance_notify">
                            <label
                                    for="low_balance_notify"><?php
								echo _l('wmm_low_balance_notify'); ?></label>
                        </div>
                        <small class="text-muted"><?php
		                    echo _l('wmm_low_balance_threshold_help'); ?></small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_textarea('notes', 'wmm_notes', '', ['rows' => 3]); ?>
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
    var PackagesServerParams = {};

    $(function () {
        initDataTable('.table-support-packages', admin_url + '/website_maintenance_management/support_packages/table', [9], [9], PackagesServerParams, [0, 'desc']);
        load_summary();
        init_ajax_search('customer', '#client_id.ajax-search');
    });

    function load_summary() {
        $.get(admin_url + 'website_maintenance_management/support_packages/get_summary', function (response) {
            if (response.success) {
                $('#total_active_packages').text(response.summary.total_active);
                $('#total_hours_remaining').text(response.summary.total_hours_remaining);
                $('#low_balance_count').text(response.summary.low_balance_count);
                $('#exhausted_packages').text(response.summary.total_exhausted);
            }
        }, 'json');
    }

    // Handle client change
    $('body').on('change', 'select[name="client_id"]', function () {
        var client_id = $(this).val();
        if (client_id) {
            load_client_websites(client_id);
        }
    });

    // Handle package scope change
    $('body').on('change', 'select[name="package_scope"]', function () {
        if ($(this).val() === 'website') {
            $('#website_row').show();
            $('select[name="website_id"]').attr('required', true);
        } else {
            $('#website_row').hide();
            $('select[name="website_id"]').attr('required', false);
        }
    });

    function load_client_websites(client_id) {
        $.post(admin_url + 'website_maintenance_management/support_packages/get_client_websites', {
            client_id: client_id
        }, function (response) {
            var select = $('select[name="website_id"]');
            select.html('');
            select.append('<option value=""></option>');
            $.each(response, function (i, website) {
                var label = website.website_url || website.project_name;
                select.append('<option value="' + website.id + '">' + label + '</option>');
            });
            select.selectpicker('refresh');
        }, 'json');
    }

    function add_package() {
        $('#package_modal').modal('show');
        $('#package_form').attr('action', admin_url + 'website_maintenance_management/support_packages/add');
        $('#package_modal .add-title').removeClass('hide');
        $('#package_modal .edit-title').addClass('hide');
        $('#package_form')[0].reset();
        $('#package_form select').selectpicker('refresh');
        $('#website_row').hide();
    }

    function edit_package(id) {
        $('#package_modal').modal('show');
        $('#package_form').attr('action', admin_url + 'website_maintenance_management/support_packages/edit/' + id);
        $('#package_modal .add-title').addClass('hide');
        $('#package_modal .edit-title').removeClass('hide');

        $.post(admin_url + 'website_maintenance_management/support_packages/get_package/' + id, function (response) {
            if (response.success) {
                var package = response.package;
                $('input[name="package_name"]').val(package.package_name);

                if (package.client_id) {
                    $('select[name="client_id"]').html('<option value="' + package.client_id + '">' + package.client_name + '</option>');
                    $('select[name="client_id"]').selectpicker('refresh')
                    setTimeout(function () {
                        $('select[name="client_id"]').selectpicker('val', package.client_id)
                        $('select[name="client_id"]').parent().find('.filter-option-inner-inner').html(package.client_name)
                    }, 500);
                    load_client_websites(package.client_id);
                }

                if (package.website_id) {
                    $('select[name="package_scope"]').val('website').selectpicker('refresh');
                    load_client_websites(package.client_id);
                    setTimeout(function () {
                        $('select[name="website_id"]').val(package.website_id).selectpicker('refresh');
                    }, 500);
                    $('#website_row').show();
                } else {
                    $('select[name="package_scope"]').val('client').selectpicker('refresh');
                    $('#website_row').hide();
                }

                $('input[name="low_balance_notify"]').prop('checked', package.low_balance_notify == 1);
                $('input[name="total_hours"]').val(package.total_hours);
                $('input[name="hourly_rate"]').val(package.hourly_rate);
                $('input[name="package_price"]').val(package.package_price);
                $('input[name="start_date"]').val(package.start_date);
                $('input[name="expiry_date"]').val(package.expiry_date);
                $('input[name="low_balance_threshold"]').val(package.low_balance_threshold);
                $('textarea[name="notes"]').val(package.notes);
            }
        }, 'json');
    }

    function delete_package(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/support_packages/delete/' + id, function (response) {
                if (response.success) {
                    alert_float('success', response.message || '<?php echo _l('wmm_package_deleted_successfully'); ?>');
                    $('.table-support-packages').DataTable().ajax.reload();
                    load_summary();
                } else {
                    alert_float('danger', response.message || '<?php echo _l('wmm_package_delete_failed'); ?>');
                }
            }, 'json');
        }
    }

    // Handle form submit
    $('body').on('submit', '#package_form', function (e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.post(form.attr('action'), data, function (response) {
            if (response.success) {
                alert_float('success', response.message);
                $('#package_modal').modal('hide');
                $('.table-support-packages').DataTable().ajax.reload();
                load_summary();
            } else {
                alert_float('danger', response.message);
            }
        }, 'json');
    });

    appValidateForm('#package_form', {
        name: "package_name",
        client_id: "required",
        package_scope: "required",
        total_hours: "required",
        hourly_rate: "required",
        package_price: "required",
        start_date: "required",
        expiry_date: "required",
        low_balance_threshold: "required",
    })
</script>
