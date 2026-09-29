<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            if (isset($category_data)) {
                $requestUrl = 'approvify/create_type/' . $category_data->id;
            } else {
                $requestUrl = 'approvify/create_type';
            }
            echo form_open(admin_url($requestUrl));
            ?>
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-4">
                            <?php echo render_input('category_name', 'approvify_category_name', $category_data->category_name ?? ''); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_input('category_description', 'approvify_category_description', $category_data->category_description ?? ''); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_input('category_icon', 'approvify_category_icon', $category_data->category_icon ?? ''); ?>
                        </div>

                        <div class="col-md-12">
                            <?php
                            $selectedStaff = '';
                            if (isset($category_data)) {
                                $selectedStaff = json_decode($category_data->approve_list);
                            }
                            echo render_select('approve_list[]', $staff_list, ['staffid', ['firstname', 'lastname']], 'approvify_approvers', $selectedStaff, ['multiple' => true], [], '', '', false);
                            ?>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

