<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('cfm_settings'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php echo form_open(admin_url('custom_flyout_menu/settings')); ?>
                        
                        <div class="form-group">
                            <label for="flyout_trigger_mode"><?php echo _l('cfm_setting_trigger_mode'); ?></label>
                            <select name="settings[flyout_trigger_mode]" id="flyout_trigger_mode" class="form-control selectpicker">
                                <option value="hover" <?php echo cfm_get_setting('flyout_trigger_mode', 'hover') == 'hover' ? 'selected' : ''; ?>>
                                    <?php echo _l('cfm_setting_trigger_hover'); ?>
                                </option>
                                <option value="click" <?php echo cfm_get_setting('flyout_trigger_mode') == 'click' ? 'selected' : ''; ?>>
                                    <?php echo _l('cfm_setting_trigger_click'); ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="hidden" name="settings[admin_menu_enabled]" value="0">
                                <input type="checkbox" id="admin_menu_enabled" name="settings[admin_menu_enabled]" value="1" 
                                    <?php echo cfm_get_setting('admin_menu_enabled', '1') == '1' ? 'checked' : ''; ?>>
                                <label for="admin_menu_enabled"><?php echo _l('cfm_setting_admin_enabled'); ?></label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="hidden" name="settings[client_menu_enabled]" value="0">
                                <input type="checkbox" id="client_menu_enabled" name="settings[client_menu_enabled]" value="1" 
                                    <?php echo cfm_get_setting('client_menu_enabled', '1') == '1' ? 'checked' : ''; ?>>
                                <label for="client_menu_enabled"><?php echo _l('cfm_setting_client_enabled'); ?></label>
                            </div>
                        </div>
                        
                        <?php if (function_exists('perfex_saas_is_tenant')): ?>
                        <div class="form-group">
                            <div class="checkbox">
                                <input type="hidden" name="settings[exclude_super_admin]" value="0">
                                <input type="checkbox" id="exclude_super_admin" name="settings[exclude_super_admin]" value="1" 
                                    <?php echo cfm_get_setting('exclude_super_admin', '1') == '1' ? 'checked' : ''; ?>>
                                <label for="exclude_super_admin"><?php echo _l('cfm_setting_exclude_super_admin'); ?></label>
                                <p class="help-block"><?php echo _l('cfm_setting_exclude_super_admin_help'); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
