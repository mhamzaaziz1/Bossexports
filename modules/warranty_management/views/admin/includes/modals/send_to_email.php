<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="TemplateModal">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Send warranty to client</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default close_btn"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>