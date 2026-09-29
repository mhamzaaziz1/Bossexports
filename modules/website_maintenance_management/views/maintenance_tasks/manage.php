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
							if (staff_can('create', 'website_maintenance_tasks'))
							{ ?>
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#taskModal" onclick="clearTaskForm()">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
									<?php
									echo _l('wmm_add_new_task'); ?>
                                </a>
								<?php
							} ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
						<?php
						render_datatable([
							_l('#'),
							_l('wmm_task_name'),
							_l('wmm_category'),
							_l('wmm_priority'),
							_l('wmm_created_at'),
							_l('options'),
						], 'maintenance-tasks'); ?>
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
			echo form_open(admin_url('website_maintenance_management/maintenance_tasks/save'), ['id' => 'task-form']); ?>
            <div class="modal-body">
                <input type="hidden" name="id" id="task_id">

                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_input('name', 'wmm_task_name', '', 'text', ['required' => TRUE]); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
						<?php
						$this->load->model('website_maintenance_management/maintenance_categories_model');
						$categories = $this->maintenance_categories_model->get_active();
						echo render_select('category', $categories, ['id', 'name'], 'wmm_category', '', [], [], '', '', FALSE);
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
						$this->load->model('staff_model');
						$staff_members = $this->staff_model->get('', ['active' => 1]);
						$staff_array   = [];
						foreach ($staff_members as $staff)
						{
							$staff_array[] = [
								'id'   => $staff['staffid'],
								'name' => $staff['firstname'].' '.$staff['lastname'],
							];
						}
						echo render_select('assignees[]', $staff_array, ['id', 'name'], 'wmm_assignees', '', ['multiple' => TRUE], [], '', '', FALSE);
						?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
						<?php
						echo render_textarea('description', 'wmm_description'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked>
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
    $(function () {
        initDataTable('.table-maintenance-tasks', admin_url + 'website_maintenance_management/maintenance_tasks/table', [5], [5], {}, [0, 'desc']);
    });

    function clearTaskForm() {
        $('#task-form')[0].reset();
        $('#task_id').val('');
        $('select[name="category"]').selectpicker('refresh');
        $('select[name="priority"]').selectpicker('refresh');
        $('select[name="assignees[]"]').selectpicker('deselectAll');
        $('#task-form').attr('action', admin_url + 'website_maintenance_management/maintenance_tasks/save');
    }

    function editTask(id) {
        $.get(admin_url + 'website_maintenance_management/maintenance_tasks/get/' + id, function (response) {
            var task = JSON.parse(response);

            // Basic fields
            $('#task_id').val(task.id);
            $('input[name="name"]').val(task.name);
            $('select[name="category"]').val(task.category.id).selectpicker('refresh');
            $('select[name="priority"]').val(task.priority || 'medium').selectpicker('refresh');
            $('textarea[name="description"]').val(task.description);

            // Assignees
            if (task.assignees && task.assignees.length > 0) {
                var assigneeIds = task.assignees.map(function (a) {
                    return a.staffid;
                });
                $('select[name="assignees[]"]').selectpicker('val', assigneeIds);
            } else {
                $('select[name="assignees[]"]').selectpicker('deselectAll');
            }

            // Checkboxes
            $('#is_active').prop('checked', task.is_active == 1);

            $('#task-form').attr('action', admin_url + 'website_maintenance_management/maintenance_tasks/save/' + task.id);
            $('#taskModal').modal('show');
        });
    }

    function deleteTask(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/maintenance_tasks/delete/' + id, function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    alert_float('success', data.message);
                    $('.table-maintenance-tasks').DataTable().ajax.reload();
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
</body>
</html>