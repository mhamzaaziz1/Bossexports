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
							if (staff_can('create', 'website_maintenance_categories'))
							{ ?>
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#categoryModal" onclick="clearCategoryForm()">
                                    <i class="fa-regular fa-plus tw-mr-1"></i>
									<?php
									echo _l('wmm_add_new_category'); ?>
                                </a>
								<?php
							} ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
						<?php
						render_datatable([
							_l('wmm_category_name'),
							_l('wmm_category_slug'),
							_l('wmm_category_icon'),
							_l('wmm_category_color'),
							_l('wmm_display_order'),
							_l('wmm_status'),
							_l('options'),
						], 'maintenance-categories'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php
					echo _l('wmm_category'); ?></h4>
            </div>
			<?php
			echo form_open(admin_url('website_maintenance_management/categories/save'), ['id' => 'category-form']); ?>
            <div class="modal-body">
                <input type="hidden" name="id" id="category_id">

                <div class="row">
                    <div class="col-md-8">
						<?php
						echo render_input('name', 'wmm_category_name', '', 'text', ['required' => TRUE]); ?>
                    </div>
                    <div class="col-md-4">
						<?php
						echo render_input('display_order', 'wmm_display_order', '0', 'number'); ?>
                    </div>
                </div>

				<?php
				echo render_input('slug', 'wmm_category_slug', '', 'text', ['placeholder' => _l('wmm_auto_generated')]); ?>
                <small class="text-muted"><?php
					echo _l('wmm_slug_help'); ?></small>

				<?php
				echo render_textarea('description', 'wmm_description'); ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="icon"><?php
								echo _l('wmm_category_icon'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-tasks" id="icon-preview"></i>
                                </span>
                                <input type="text" name="icon" id="icon" class="form-control" placeholder="fa-tasks">
                            </div>
                            <small class="text-muted">
                                <a href="https://fontawesome.com/icons" target="_blank">
									<?php
									echo _l('wmm_browse_icons'); ?>
                                </a>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="color"><?php
								echo _l('wmm_category_color'); ?></label>
                            <input type="color" name="color" id="color" class="form-control" value="#3b82f6">
                        </div>
                    </div>
                </div>

                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    <label for="is_active"><?php
						echo _l('wmm_is_active'); ?></label>
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
        initDataTable('.table-maintenance-categories', admin_url + 'website_maintenance_management/categories/table', [6], [6], {}, [4, 'asc']);

        // Icon preview
        $('#icon').on('input', function () {
            var iconClass = $(this).val();
            if (iconClass) {
                $('#icon-preview').attr('class', 'fa ' + iconClass);
            } else {
                $('#icon-preview').attr('class', 'fa fa-tasks');
            }
        });
    });

    function clearCategoryForm() {
        $('#category-form')[0].reset();
        $('#category_id').val('');
        $('#icon-preview').attr('class', 'fa fa-tasks');
        $('#color').val('#3b82f6');
        $('#category-form').attr('action', admin_url + 'website_maintenance_management/categories/save');
    }

    function editCategory(id) {
        $.get(admin_url + 'website_maintenance_management/categories/get/' + id, function (response) {
            var category = JSON.parse(response);
            $('#category_id').val(category.id);
            $('input[name="name"]').val(category.name);
            $('input[name="slug"]').val(category.slug);
            $('textarea[name="description"]').val(category.description);
            $('input[name="icon"]').val(category.icon);
            $('input[name="color"]').val(category.color);
            $('input[name="display_order"]').val(category.display_order);
            $('#is_active').prop('checked', category.is_active == 1);

            // Update icon preview
            if (category.icon) {
                $('#icon-preview').attr('class', 'fa ' + category.icon);
            }

            $('#category-form').attr('action', admin_url + 'website_maintenance_management/categories/save/' + category.id);
            $('#categoryModal').modal('show');
        });
    }

    function deleteCategory(id) {
        if (confirm_delete()) {
            $.post(admin_url + 'website_maintenance_management/categories/delete/' + id, function (response) {
                var data = JSON.parse(response);
                if (data.success) {
                    alert_float('success', data.message);
                    $('.table-maintenance-categories').DataTable().ajax.reload();
                } else {
                    alert_float('danger', data.message);
                }
            });
        }
    }

    function toggleCategoryStatus(id, status) {
        $.post(admin_url + 'website_maintenance_management/categories/toggle_status/' + id + '/' + status, function (response) {
            var data = JSON.parse(response);
            if (data.success) {
                alert_float("success", data.message);
                $('.table-maintenance-categories').DataTable().ajax.reload();
            }
        });
    }
</script>
</body>
</html>