<?php echo form_open_multipart('lead_checklist/checklist/definition_save/'.$data->id ); ?>

    <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title">

            <span class="edit-title"><?php echo _l('lead_checklist')?></span>

        </h4>

    </div>

    <div class="modal-body">

        <div class="row">

            <div class="col-md-12">

                <?php echo render_input('checklist_text', 'name' , $data->checklist_text , '' , [ 'required' => true , 'autofocus' => true ]); ?>

            </div>

            <div class="col-md-12">

                <div class="form-group" style="margin-top: 15px">

                    <div class="checkbox checkbox-primary checkbox-inline">

                        <input type="checkbox" id="add_auto" name="add_auto" <?php if (  $data->add_auto == 1) { echo 'checked'; } ?> value="1" >

                        <label for="add_auto" ><?php echo _l('lead_checklist_add_auto'); ?></label>

                    </div>

                </div>

            </div>



        </div>

    </div>

    <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

    </div>

<?php echo form_close(); ?>
