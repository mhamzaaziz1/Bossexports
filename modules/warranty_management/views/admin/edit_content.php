<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin">
                    <i class="fa fa-file-text-o"></i>
                    Edit Warranty Content
                </h4>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <div class="row">
            <!-- LEFT SIDE : EDITOR -->
            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">

                        <?php echo form_open(
                            admin_url('warranty_management/save_content/' . $warranty['id'])
                        ); ?>

                        <h5 class="bold mbot10">
                            Warranty Template Content
                        </h5>

                        <textarea
                            name="content"
                            class="form-control tinymce"
                            rows="18"><?php echo html_escape($content); ?></textarea>

                        <div class="mtop15 text-right">
                            <a href="<?php echo admin_url('warranty_management/warranty_information/' . $warranty['id']); ?>"
                                class="btn btn-default mright5">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i>
                                Save
                            </button>
                        </div>

                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">

                        <h5 class="bold">
                            <i class="fa fa-code"></i>
                            Available Merge Fields
                        </h5>

                        <hr class="hr-panel-heading" />

                        <ul class="list-unstyled">
                            <?php foreach ($merge_fields as $field) { ?>
                               <li class="mbot5">
                                    <a href="javascript:void(0)"
                                    class="insert-merge-field"
                                    data-field="<?php echo html_escape($field); ?>">
                                        <code><?php echo html_escape($field); ?></code>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>

                        <p class="text-muted mtop10">
                            You can use these fields inside the content.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>



<script>
$(function () {
    $('.insert-merge-field').on('click', function () {
        var field = $(this).data('field');

        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.activeEditor;
            if (editor) {
                editor.execCommand('mceInsertContent', false, field);
            }
        } else {
            var textarea = $('textarea[name="content"]');
            textarea.val(textarea.val() + field);
        }
    });
});
</script>

