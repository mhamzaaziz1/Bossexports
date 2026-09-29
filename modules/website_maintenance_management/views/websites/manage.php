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
						if (staff_can('create', 'website_maintenance_websites'))
						{ ?>
                            <h4 class="no-margin"><?php
								echo _l('wmm_add_website_to_maintenance'); ?></h4>
                            <hr class="hr-panel-heading"/>
							<?php
							echo form_open(admin_url('website_maintenance_management/websites/add'), ['id' => 'add-maintenance-website-form']); ?>
                            <div class="row">
                                <div class="col-md-4">
									<?php
									$client_options = [];
									foreach ($clients as $client)
									{
										$client_options[] = ['id' => $client['userid'], 'name' => $client['company']];
									}
									echo render_select('client_id', $client_options, ['id', 'name'], 'wmm_select_customer', '', ['required' => TRUE, 'data-live-search' => 'true'], [], '', '', FALSE);
									?>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="project-wrapper" style="display:none;">
                                        <label for="project_id"><?php
											echo _l('wmm_select_project'); ?> <span class="text-danger">*</span></label>
                                        <select name="project_id" id="project_id" class="selectpicker" data-width="100%" data-live-search="true" required>
                                            <option value=""><?php
												echo _l('wmm_select_project_first'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
									<?php
									echo render_input('website_url', 'wmm_website_url', '', 'url', ['required' => TRUE, 'placeholder' => 'https://example.com']); ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
								<?php
								echo _l('wmm_add_to_maintenance'); ?>
                            </button>
							<?php
							echo form_close(); ?>
                            <hr/>
							<?php
						} ?>

                        <h4 class="no-margin"><?php
							echo _l('wmm_websites_under_maintenance'); ?></h4>
                        <hr class="hr-panel-heading"/>
						<?php
						render_datatable([
							_l('#'),
							_l('wmm_customer'),
							_l('wmm_project'),
							_l('wmm_website_url'),
							_l('wmm_status'),
							_l('wmm_date_added'),
							_l('options'),
						], 'maintenance-websites'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-maintenance-websites', window.location.href, [6], [6], {}, [0, 'desc']);


        appValidateForm(
            $("#add-maintenance-website-form"),
            {
                client_id: "required",
                project_id: "required",
                website_url: "required",
            }
        );


        $('select[name="client_id"]').on('change', function () {
            var clientId = $(this).val();
            if (clientId) {
                $.get(admin_url + 'website_maintenance_management/websites/get_projects_by_client/' + clientId, function (response) {
                    var projects = JSON.parse(response);
                    var options = '<option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>';

                    $.each(projects, function (i, project) {
                        options += '<option value="' + project.id + '">' + project.name + '</option>';
                    });

                    $('#project_id').html(options).selectpicker('refresh');
                    $('#project-wrapper').show();
                });
            } else {
                $('#project-wrapper').hide();
                $('#project_id').html('<option value=""><?php echo _l('wmm_select_project_first'); ?></option>').selectpicker('refresh');
            }
        });
    });

    function toggleWebsiteStatus(id, status) {
        $.post(admin_url + 'website_maintenance_management/websites/toggle_status/' + id + '/' + status, function (response) {
            if (response.success) {
                alert_float("success", response.message);
                $('.table-maintenance-websites').DataTable().ajax.reload();
            }
        }, 'json');
    }

    function deleteWebsite(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/websites/delete/' + id, function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    alert_float('success', data.message);
                    $('.table-maintenance-websites').DataTable().ajax.reload();
                } else {
                    alert_float('danger', data.message);
                }
            });
        }
    }
</script>
