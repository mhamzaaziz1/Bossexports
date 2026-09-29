<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

$obj_storage = clear_textarea_breaks(get_option(POLY_SCRIPTS));
$obj_old_data = [];
if (!empty($obj_storage)) {
    $obj_old_data = json_decode($obj_storage);
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                <?php
                if(has_permission('poly_utilities_scripts_extend', '', 'create'))
                {
                ?>
                    <a href="<?php echo admin_url('poly_utilities/scripts_add'); ?>">
                        <i class="far fa-plus-square tw-mr-1"></i>
                        <?php echo _l('new_poly_utilities_script'); ?>
                    </a>
              <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        if (is_array($obj_old_data) && count($obj_old_data)) {
                        ?>
                            <ol>
                                <?php
                                $idx = 0;
                                foreach ($obj_old_data as $key => $value) {
                                    $idx++;
                                ?>
                                    <li class="col-md-12" data-title="<?php echo "{$value->title}"; ?>" data-index="<?php echo "{$value->file}"; ?>" data-id="<?php echo "mn_{$idx}"; ?>">
                                        <div>
                                            <span><?php echo $idx ?>. <a href="<?php echo base_url('modules/poly_utilities/uploads/js/' . $value->file) . '.js' ?>" target="_blank" rel="nofollow"><?php echo $value->title ?></a></span>
                                            <?php
                                            if(has_permission('poly_utilities_scripts_extend', '', 'delete')){
                                                ?>
                                            <span class="tw-mr-1 poly-resource-delete delete text-muted pull-right" data-id="<?php echo "{$value->file}" ?>"><i class="fas fa-trash"></i></span>
                                            <?php
                                            }
                                            ?>
                                            <span class="tw-mr-1 pull-right">
                                                <a rel="nofollow" target="_blank" download="<?php echo $value->file . '.js' ?>" href="<?php echo base_url('modules/poly_utilities/uploads/js/' . $value->file) . '.js' ?>">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                            </span>
                                            <span class="tw-mr-1 pull-right"><a href="#" class="text-muted toggle-menu-options main-item-options"><i class="fas fa-cog"></i></a></span>
                                            <?php
                                            if(has_permission('poly_utilities_scripts_extend', '', 'edit')){
                                                ?>
                                            <span class="tw-mr-1 pull-right">
                                                <a href="<?php echo admin_url('poly_utilities/scripts_add?id=' . $value->file); ?>">
                                                    <i class="fa-regular fa-pen-to-square poly-icon"></i>
                                                </a>
                                            </span>
                                            <?php
                                            }
                                            ?>
                                            <div class="row">
                                                <!-- Is Admin? -->
                                                <div class="col-md-6">
                                                    <div class="checkbox checkbox-primary">
                                                        <input disabled type="checkbox" name="poly_utilities_resource_is_admin" id="poly_utilities_resource_is_admin" <?php echo (($value->mode == 'admin_customers' || $value->mode == 'admin') ? ' checked' : '') ?>>
                                                        <label for="poly_utilities_resource_is_admin"><?php echo _l('poly_utilities_resource_is_admin_short'); ?></label>
                                                    </div>
                                                </div>
                                                <!-- Is Admin -->

                                                <!-- Is Clients? -->
                                                <div class="col-md-6">
                                                    <div class="checkbox checkbox-primary">
                                                        <input disabled type="checkbox" name="poly_utilities_resource_is_customers" id="poly_utilities_resource_is_customers" <?php echo (($value->mode == 'admin_customers' || $value->mode == 'customers') ? ' checked' : '') ?>>
                                                        <label for="poly_utilities_resource_is_customers"><?php echo _l('poly_utilities_resource_is_customers_short'); ?></label>
                                                    </div>
                                                </div>
                                                <!-- Is Clients? -->
                                            </div>
                                        </div>

                                        <!-- Toggle -->
                                        <div class="menu-options main-item-options poly-hide" data-menu-options="<?php echo "mn_{$idx}" ?>">
                                            <div class="row">
                                                <?php echo render_input("poly_utilities_scripts_name_{$value->file}", 'poly_utilities_resource_name', $value->title, 'text', array('readonly' => true), [], 'col-md-12'); ?>
                                                <?php echo render_input("poly_utilities_scripts_file_{$value->file}", 'poly_utilities_file_name', base_url('modules/poly_utilities/uploads/js/' . $value->file) . '.js', 'text', array('readonly' => true), [], 'col-md-12'); ?>
                                            </div>
                                        </div>
                                        <hr />
                                        <!-- Toggle -->
                                    </li>
                                <?php
                                }
                                ?>
                            </ol>
                        <?php
                        } else {
                            $this->load->view('poly_utilities/blank');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
init_tail();
echo '<script src="' . base_url('modules/poly_utilities/assets/js/admin/manage_scripts.js') . '"></script>';
?>