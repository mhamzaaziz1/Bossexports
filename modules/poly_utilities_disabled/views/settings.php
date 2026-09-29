<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

$is_sticky =isset($poly_utilities_settings['is_sticky'])?$poly_utilities_settings['is_sticky']:'false';
$is_search_menu =isset($poly_utilities_settings['is_search_menu'])?$poly_utilities_settings['is_search_menu']:'false';
$is_quick_access_menu =isset($poly_utilities_settings['is_quick_access_menu'])?$poly_utilities_settings['is_quick_access_menu']:'true';
$is_quick_access_menu_icons =isset($poly_utilities_settings['is_quick_access_menu_icons'])?$poly_utilities_settings['is_quick_access_menu_icons']:'true';
$is_table_of_content = isset($poly_utilities_settings['is_table_of_content'])?$poly_utilities_settings['is_table_of_content']:'false';
$is_active_scripts = isset($poly_utilities_settings['is_active_scripts'])?$poly_utilities_settings['is_active_scripts']:'true';
$is_active_styles = isset($poly_utilities_settings['is_active_styles'])?$poly_utilities_settings['is_active_styles']:'true';
$is_note_confirm_delete = isset($poly_utilities_settings['is_note_confirm_delete'])?$poly_utilities_settings['is_note_confirm_delete']:'true';
$is_operation_functions = isset($poly_utilities_settings['is_operation_functions'])?$poly_utilities_settings['is_operation_functions']:'true';
$is_scroll_to_top = isset($poly_utilities_settings['is_scroll_to_top'])?$poly_utilities_settings['is_scroll_to_top']:'false';
$is_toggle_sidebar_menu = isset($poly_utilities_settings['is_toggle_sidebar_menu'])?$poly_utilities_settings['is_toggle_sidebar_menu']:'false';
$is_edit=has_permission('poly_utilities_scripts_extend', '', 'edit');

?>
<div id="wrapper">
  <div class="content">
    <div class="row poly_utilities_settings">
      <div class="col-md-12">
        <div class="tw-mb-2 sm:tw-mb-4">
          <?php echo form_open($this->uri->uri_string(), array('class' => 'quick_access-form')); ?>
          <div class="panel_s">
            <div class="panel-body">
              <div class="row">
                
                <!-- Is search menu? -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_search_menu" id="poly_utilities_is_search_menu" <?php echo (($is_search_menu == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_is_search_menu"><?php echo _l('poly_utilities_is_search_menu'); ?></label>
                  </div>
                </div>
                <!-- Is search menu? -->

                <!-- Is sticky menu? -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_topbar_is_sticky" id="poly_utilities_topbar_is_sticky" <?php echo (($is_sticky == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_topbar_is_sticky"><?php echo _l('poly_utilities_topbar_is_sticky'); ?></label>
                  </div>
                </div>
                <!-- Is sticky menu? -->

                <!-- Is toggle sidebar menu? -->
                <?php
                $favicon = get_option('favicon');
                $favicon_path = (!empty($favicon))?base_url('uploads/company/'.$favicon):'';
                ?>
                <div class="form-group relative" style="display:table">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_toggle_sidebar_menu" id="poly_utilities_is_toggle_sidebar_menu" <?php echo (($is_toggle_sidebar_menu == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_is_toggle_sidebar_menu"><?php echo _l('poly_utilities_is_toggle_sidebar_menu_icon_help'); ?></label>
                  </div>
                  <div class="poly-favicon"><a href="<?php echo base_url('admin/settings?group=general')?>" target="_blank"><i class="fa fa-edit"></i></a><img class="poly-favicon-thumb" src="<?php echo $favicon_path?>"/></div>
                </div>
                <!-- Is toggle sidebar menu? -->

                <!-- Enable Quick Access Menu? -->
                <div class="form-group">
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_quick_access_menu" id="poly_utilities_is_quick_access_menu" <?php echo (($is_quick_access_menu == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_is_quick_access_menu"><?php echo _l('poly_utilities_is_quick_access_menu'); ?></label>
                  </div>
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_quick_access_menu_icons" id="poly_utilities_is_quick_access_menu_icons" <?php echo (($is_quick_access_menu_icons == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_is_quick_access_menu_icons"><?php echo _l('poly_utilities_is_quick_access_menu_icons'); ?></label>
                    <i class="fa-regular fa-circle-question cursor" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_is_quick_access_menu_icons_message') ?>">&nbsp;</i>
                  </div>
                </div>
                <!-- Enable Quick Access Menu? -->

                <!-- Is Table of content? -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_table_of_content" id="poly_utilities_is_table_of_content" <?php echo (($is_table_of_content == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_is_table_of_content"><?php echo _l('poly_utilities_is_table_of_content'); ?></label>
                  </div>
                </div>
                <!-- Is Table of content? -->

                <!-- Enable custom JS -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_scripts" id="poly_utilities_enable_scripts" <?php echo (($is_active_scripts == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_enable_scripts"><?php echo _l('poly_utilities_enable_scripts'); ?></label>
                  </div>
                </div>
                <!-- Enable cusom JS -->

                <!-- Enable custom CSS -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_styles" id="poly_utilities_enable_styles" <?php echo (($is_active_styles == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_enable_styles"><?php echo _l('poly_utilities_enable_styles'); ?></label>
                  </div>
                </div>
                <!-- Enable custom CSS -->

                <!-- Active confirm delete note -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_note_confirm_delete" id="poly_utilities_enable_note_confirm_delete" <?php echo (($is_note_confirm_delete == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_enable_note_confirm_delete"><?php echo _l('poly_utilities_enable_note_confirm_delete'); ?></label>
                  </div>
                </div>
                <!-- Active confirm delete note -->

                <!-- Active operation actions -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_operation_functions" id="poly_utilities_enable_operation_functions" <?php echo (($is_operation_functions == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_enable_operation_functions"><?php echo _l('poly_utilities_enable_operation_functions'); ?></label>
                  </div>
                </div>
                <!-- Active operation actions -->

                <!-- Active scroll to top -->
                <div class="form-group">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_scroll_to_top" id="poly_utilities_enable_scroll_to_top" <?php echo (($is_scroll_to_top == 'true') ? ' checked' : '').(!$is_edit?' disabled':'') ?>>
                    <label for="poly_utilities_enable_scroll_to_top"><?php echo _l('poly_utilities_enable_scroll_to_top'); ?></label>
                  </div>
                </div>
                <!-- Active scroll to top -->

              </div>
            </div>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php
echo '<script src="' . base_url('modules/poly_utilities/assets/js/admin/settings.js') . '"></script>';
?>