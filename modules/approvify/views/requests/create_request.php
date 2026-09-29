<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div id="response"></div>
            <?php echo form_open(current_full_url(), ['id' => 'requestForm', 'class' => 'disable-on-submit']); ?>
            <div class="col-md-12">
                <h2>
                    <?php echo isset($type_data) ? $type_data->category_name : ''; ?>
                </h2>
                <p><?php echo isset($type_data) ? $type_data->category_description : ''; ?></p>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?php echo render_input('request_title', 'approvify_request_title'); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('request_content', 'approvify_request_content', '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>

                        <div class="col-md-12">
                            <button type="submit"
                                    class="btn btn-primary saveDocument pull-right"><?php echo _l('approvify_create_request'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-12 mbot10">
                            <div class="attachments">
                                <div class="attachment">
                                        <label for="attachment"
                                               class="control-label"><?php echo _l('ticket_form_attachments'); ?></label>
                                        <div class="input-group">
                                            <input type="file"
                                                   extension="<?php echo str_replace('.', '', get_option('ticket_attachments_file_extensions')); ?>"
                                                   filesize="<?php echo file_upload_max_size(); ?>" class="form-control"
                                                   name="attachments[]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
                                            <span class="input-group-btn">
                                        <button class="btn btn-primary add_more_attachments"
                                                data-max="<?php echo get_option('maximum_allowed_ticket_attachments'); ?>"
                                                type="button"><i class="fa fa-plus"></i></button>
                                    </span>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo form_close();
            ?>
        </div>

    </div>
</div>
<?php init_tail(); ?>
</body>
<script>
    "use strict";

    var form_id = '#requestForm';

    $(function () {

        $(form_id).appFormValidator({

            onSubmit: function (form) {

                $("input[type=file]").each(function () {
                    if ($(this).val() === "") {
                        $(this).prop('disabled', true);
                    }
                });
                $('#form_submit .fa-spin').removeClass('hide');

                var formURL = $(form).attr("action");
                var formData = new FormData($(form)[0]);

                $.ajax({
                    type: $(form).attr('method'),
                    data: formData,
                    mimeType: $(form).attr('enctype'),
                    contentType: false,
                    cache: false,
                    processData: false,
                    url: formURL
                }).always(function () {
                    $('#form_submit').prop('disabled', false);
                    $('#form_submit .fa-spin').addClass('hide');
                }).done(function (response) {

                    response = JSON.parse(response);
                    // In case action hook is used to redirect
                    if (response.redirect_url) {
                        if (window.top) {
                            window.top.location.href = response.redirect_url;
                        } else {
                            window.location.href = response.redirect_url;
                        }
                        return;
                    }
                    if (response.success == false) {
                        $('#recaptcha_response_field').html(response
                            .message); // error message
                    } else if (response.success == true) {
                        $(form_id).remove();
                        $('#response').html(
                            '<div class="alert alert-success" style="margin-bottom:0;">' +
                            response.message + '</div>');
                        $('html,body').animate({
                            scrollTop: $("#online_payment_form").offset().top
                        }, 'slow');
                    } else {
                        $('#response').html('Something went wrong...');
                    }
                    if (typeof (grecaptcha) != 'undefined') {
                        grecaptcha.reset();
                    }
                }).fail(function (data) {

                    if (typeof (grecaptcha) != 'undefined') {
                        grecaptcha.reset();
                    }

                    if (data.status == 422) {
                        $('#response').html(
                            '<div class="alert alert-danger">Some fields that are required are not filled properly.</div>'
                        );
                    } else {
                        $('#response').html(data.responseText);
                    }
                });
                return false;
            }
        });
    });

</script>
</html>
