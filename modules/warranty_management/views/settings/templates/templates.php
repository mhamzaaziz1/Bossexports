<script src="<?php echo module_dir_url('warranty_management', 'assets/js/warranty_template.js'); ?>"></script>

<div role="tabpanel" class="tab-pane" id="tab_templates">
    <div class="row proposal-templates">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary"
                onclick="add_warranty_template();">
                <?php echo _l('add_template'); ?>
            </button>
            <hr>
        </div>
        <div class="col-md-12">
            <div id="proposal-templates" class="proposal-templates-wrapper"></div>
        </div>
    </div>
</div>

<?php defined('BASEPATH') or exit('No direct script access allowed');

$templates = $templates ?? [];
$len = count($templates);
$i   = 0;
?>
<div id="templates-wrapper" class="ptop15" data-total="<?php echo e($len); ?>">

    <?php foreach ($templates as $template) { ?>
        <div class="media templates-wrapper <?php if ($i == 0) {
                                                echo 'mtop15';
                                            } ?>">
            <div class="media-body">

                <?php if ($template['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                    <button class="btn pull-right btn-danger"
                        onclick="delete_warranty_template(this, <?php echo (int)$template['id']; ?>);return false;">
                        <i class="fa fa fa-times"></i>
                    </button>
                    <button class="btn pull-right btn-primary mright5 "
                        onclick="edit_warranty_template(<?php echo (int)($template['id']); ?>);return false;">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                <?php } ?>
                <button class="btn pull-right btn-default mright5"
                    onclick="insert_warranty_template(<?php echo (int)($template['id']); ?>,  <?php echo (int)$rel_id; ?>);
                        return false;">
                    <?php echo _l('insert_template') ?>
                </button>
                <div data-template-content="<?php echo e($template['id']); ?>" class="bold">
                    <?php echo check_for_links($template['name']); ?>
                </div>
            </div>
            <?php if ($i >= 0 && $i != $len - 1) {
                echo '<hr />';
            }
            ?>
        </div>
    <?php
        $i++;
    } ?>
</div>

<div id="modal-wrapper"></div>