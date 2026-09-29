<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo html_escape($title); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <div id="cfm-builder-wrapper" data-area="<?php echo html_escape($area); ?>">
                            <!-- Groups Section -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="bold  tw-mb-5"><?php echo _l('cfm_groups'); ?>
                                    <button type="button" class="btn btn-success btn-sm pull-right" id="cfm-add-group">
                                        <i class="fa fa-plus"></i> <?php echo _l('cfm_add_group'); ?>
                                    </button>
                                </h4>
                                <div class="panel_s panel-table-full">
                                    <div class="panel-body">
                                        <table class="table dt-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('ID'); ?></th>
                                                    <th><?php echo _l('cfm_group_name'); ?></th>
                                                    <th><?php echo _l('cfm_group_slug'); ?></th>
                                                    <th><?php echo _l('cfm_sort_order'); ?></th>
                                                    <th><?php echo _l('cfm_item_active'); ?></th>
                                                    <th><?php echo _l('options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="cfm-groups-table-body">
                                                <!-- Groups loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items Section -->
                        <div class="row mtop30">
                            <div class="col-md-12">
                                <h4 class="bold  tw-mb-5"><?php echo _l('cfm_menu_items'); ?>
                                <button type="button" class="btn btn-primary btn-sm pull-right" id="cfm-add-item">
                                    <i class="fa fa-plus"></i> <?php echo _l('cfm_add_item'); ?>
                                </button>
                            </h4>
                            <div class="panel_s panel-table-full">
                                <div class="panel-body">
                                    <table class="table dt-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('ID'); ?></th>
                                                <th><?php echo _l('cfm_item_label'); ?></th>
                                                <th><?php echo _l('cfm_item_group'); ?></th>
                                                <th><?php echo _l('cfm_item_parent'); ?></th>
                                                <th><?php echo _l('cfm_item_url'); ?></th>
                                                <th><?php echo _l('cfm_item_icon'); ?></th>
                                                <th><?php echo _l('cfm_item_active'); ?></th>
                                                <th><?php echo _l('options'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cfm-items-table-body">
                                            <!-- Items loaded dynamically -->
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
</div>

<!-- Group Modal -->
<div class="modal fade" id="cfm-group-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo _l('cfm_add_group'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="cfm-group-form">
                    <input type="hidden" name="id" id="group-id">
                    <input type="hidden" name="area" id="group-area" value="<?php echo html_escape($area); ?>">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    
                    <div class="form-group">
                        <label for="group-name"><?php echo _l('cfm_group_name'); ?> *</label>
                        <input type="text" class="form-control" name="name" id="group-name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="group-slug"><?php echo _l('cfm_group_slug'); ?> *</label>
                        <input type="text" class="form-control" name="slug" id="group-slug" required>
                        <small class="text-muted">Lowercase, no spaces (e.g., sales, support)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="group-sort-order"><?php echo _l('cfm_sort_order'); ?></label>
                        <input type="number" class="form-control" name="sort_order" id="group-sort-order" value="0">
                    </div>
                    
                    <div class="checkbox">
                        <input type="checkbox" name="active" id="group-active" value="1" checked>
                        <label for="group-active"><?php echo _l('cfm_item_active'); ?></label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cfm_action_cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="cfm-save-group"><?php echo _l('cfm_action_save'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="cfm-item-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo _l('cfm_add_item'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="cfm-item-form">
                    <input type="hidden" name="id" id="item-id">
                    <input type="hidden" name="area" id="item-area" value="<?php echo html_escape($area); ?>">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-label"><?php echo _l('cfm_item_label'); ?> *</label>
                                <input type="text" class="form-control" name="label" id="item-label" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-url"><?php echo _l('cfm_item_url'); ?> *</label>
                                <input type="text" class="form-control" name="url" id="item-url" required>
                                <small class="text-muted">Relative URL (e.g., clients, invoices/list)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-icon"><?php echo _l('cfm_item_icon'); ?></label>
                                <input type="text" class="form-control" name="icon" id="item-icon">
                                <small class="text-muted">Phosphor icon (e.g., ph-users, ph-file-text)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-group"><?php echo _l('cfm_item_group'); ?></label>
                                <select class="form-control selectpicker" name="group_id" id="item-group" data-live-search="true">
                                    <option value="">-- None --</option>
                                    <!-- Groups loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-parent">
                                <label for="item-parent"><?php echo _l('cfm_item_parent'); ?></label>
                                <select class="form-control selectpicker" name="parent_id" id="item-parent" data-live-search="true">
                                    <option value="">-- None --</option>
                                    <!-- Parents loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-badge-text"><?php echo _l('cfm_item_badge_text'); ?></label>
                                <input type="text" class="form-control" name="badge_text" id="item-badge-text">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-badge-color"><?php echo _l('cfm_item_badge_color'); ?></label>
                                <select class="form-control selectpicker" name="badge_color_class" id="item-badge-color">
                                    <option value="">Default</option>
                                    <option value="cfm-badge-primary">Primary (Blue)</option>
                                    <option value="cfm-badge-success">Success (Green)</option>
                                    <option value="cfm-badge-info">Info (Light Blue)</option>
                                    <option value="cfm-badge-warning">Warning (Orange)</option>
                                    <option value="cfm-badge-danger">Danger (Red)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-permission"><?php echo _l('cfm_item_permission'); ?></label>
                                <input type="text" class="form-control" name="permission" id="item-permission">
                                <small class="text-muted">Permission slug (e.g., customers, invoices)</small>
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item-sort-order"><?php echo _l('cfm_sort_order'); ?></label>
                                <input type="number" class="form-control" name="sort_order" id="item-sort-order" value="0">
                            </div>
                        </div>

                        <div class="col-md-6 mtop20">
                            <div class="checkbox">
                                <input type="checkbox" name="active" id="item-active" value="1" checked>
                                <label for="item-active"><?php echo _l('cfm_item_active'); ?></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cfm_action_cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="cfm-save-item"><?php echo _l('cfm_action_save'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script src="<?php echo module_dir_url('custom_flyout_menu', 'assets/js/menu_builder.js'); ?>?v=1.0.0"></script>
