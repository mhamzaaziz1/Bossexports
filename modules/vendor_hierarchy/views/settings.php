<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$CI = &get_instance();
$CI->load->model('vendor_hierarchy/vendor_hierarchy_model');
$labels = $CI->vendor_hierarchy_model->get_level_labels();
?>

<div class="row">
    <div class="col-md-12">
        <h4 class="no-margin font-bold">
            <i class="fa fa-sitemap text-info mright5"></i>
            <?php echo _l('vendor_hierarchy_labels'); ?>
        </h4>
        <hr class="hr-panel-heading" />
        <p class="text-muted mbot15">
            Define the label names for each level of the vendor hierarchy. These labels will appear throughout the system — in dropdowns, tables, breadcrumbs, and reports.
        </p>

                        <div class="row">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level_<?php echo $i; ?>_label" class="control-label">
                                        <span class="label label-<?php
                                            $badge_classes = ['primary', 'success', 'warning', 'info'];
                                            echo $badge_classes[$i - 1];
                                        ?> mright5">Level <?php echo $i; ?></span>
                                        <?php echo _l('vendor_hierarchy_level_' . $i); ?>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-tag"></i></div>
                                        <input type="text" name="settings[level_<?php echo $i; ?>_label]"
                                            id="level_<?php echo $i; ?>_label"
                                            class="form-control"
                                            value="<?php echo html_entity_decode($labels['level_' . $i . '_label']); ?>"
                                            placeholder="e.g. <?php
                                                $defaults = ['Group', 'Company', 'Regional', 'Branch'];
                                                echo $defaults[$i - 1];
                                            ?>">
                                    </div>
                                    <small class="text-muted">
                                        <?php
                                        $descriptions = [
                                            'Top-level grouping (e.g., Holding Company, Group)',
                                            'Second level (e.g., Company, Entity)',
                                            'Third level (e.g., Regional Office, Division)',
                                            'Lowest level (e.g., Branch, Location, Unit)'
                                        ];
                                        echo $descriptions[$i - 1];
                                        ?>
                                    </small>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>

                        <hr />

                        <div class="panel panel-default mbot15">
                            <div class="panel-heading">
                                <strong><i class="fa fa-info-circle mright5"></i>Preview Hierarchy Structure</strong>
                            </div>
                            <div class="panel-body" id="hierarchy-preview">
                                <div class="vh-preview-tree">
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <div class="vh-preview-node" style="margin-left: <?php echo ($i - 1) * 30; ?>px;">
                                        <i class="fa fa-<?php
                                            $icons = ['building', 'industry', 'map-marker', 'home'];
                                            echo $icons[$i - 1];
                                        ?> text-<?php echo $badge_classes[$i - 1]; ?>"></i>
                                        <span class="vh-preview-label" id="preview_level_<?php echo $i; ?>">
                                            <?php echo html_entity_decode($labels['level_' . $i . '_label']); ?>
                                        </span>
                                        <small class="text-muted">(Level <?php echo $i; ?>)</small>
                                        <?php if ($i < 4): ?>
                                        <span class="vh-preview-connector">│</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

    </div>
</div>

<style>
.vh-preview-tree { padding: 15px; }
.vh-preview-node {
    padding: 8px 12px;
    margin-bottom: 4px;
    border-radius: 4px;
    background: #f9f9f9;
    border-left: 3px solid #e0e0e0;
    transition: all 0.2s;
}
.vh-preview-node:hover { background: #f0f7ff; }
.vh-preview-node i { margin-right: 8px; font-size: 14px; }
.vh-preview-label { font-weight: 600; font-size: 14px; }
.vh-preview-connector {
    display: block;
    margin-left: 8px;
    color: #ccc;
    font-size: 12px;
    line-height: 1;
}
</style>
<script>
$(function(){
    // Live preview of labels
    $('input[name^="settings[level_"]').on('input', function() {
        var nameAttr = $(this).attr('name');
        var match = nameAttr.match(/settings\[level_(\d+)_label\]/);
        if (match) {
            var level = match[1];
            $('#preview_level_' + level).text($(this).val() || 'Level ' + level);
        }
    });
});
</script>
