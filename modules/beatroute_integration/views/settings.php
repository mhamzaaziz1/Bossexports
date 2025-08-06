<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<div id="wrapper">
    <div class="content">
<div class="row">
    <div class="col-md-12">
        <?php echo form_open(admin_url('beatroute_integration/settings'), ['id' => 'beatroute-settings-form']); ?>
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><?php echo _l('beatroute_integration_settings'); ?></h4>
                <hr class="hr-panel-heading" />

                <div class="row">
                    <div class="col-md-12">
                        <h4><?php echo _l('general_settings'); ?></h4>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="beatroute_integration_enabled" class="control-label">
                                <?php echo _l('enable_beatroute_integration'); ?>
                            </label>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_integration_enabled_yes" name="beatroute_integration_enabled" value="1" <?php if (get_option('beatroute_integration_enabled') == '1') { echo 'checked'; } ?>>
                                <label for="beatroute_integration_enabled_yes"><?php echo _l('yes'); ?></label>
                            </div>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_integration_enabled_no" name="beatroute_integration_enabled" value="0" <?php if (get_option('beatroute_integration_enabled') == '0') { echo 'checked'; } ?>>
                                <label for="beatroute_integration_enabled_no"><?php echo _l('no'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="beatroute_auto_sync_enabled" class="control-label">
                                <?php echo _l('enable_auto_sync'); ?>
                            </label>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_auto_sync_enabled_yes" name="beatroute_auto_sync_enabled" value="1" <?php if (get_option('beatroute_auto_sync_enabled') == '1') { echo 'checked'; } ?>>
                                <label for="beatroute_auto_sync_enabled_yes"><?php echo _l('yes'); ?></label>
                            </div>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_auto_sync_enabled_no" name="beatroute_auto_sync_enabled" value="0" <?php if (get_option('beatroute_auto_sync_enabled') == '0') { echo 'checked'; } ?>>
                                <label for="beatroute_auto_sync_enabled_no"><?php echo _l('no'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="beatroute_webhook_enabled" class="control-label">
                                <?php echo _l('enable_webhooks'); ?>
                            </label>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_webhook_enabled_yes" name="beatroute_webhook_enabled" value="1" <?php if (get_option('beatroute_webhook_enabled') == '1') { echo 'checked'; } ?>>
                                <label for="beatroute_webhook_enabled_yes"><?php echo _l('yes'); ?></label>
                            </div>
                            <div class="radio radio-primary">
                                <input type="radio" id="beatroute_webhook_enabled_no" name="beatroute_webhook_enabled" value="0" <?php if (get_option('beatroute_webhook_enabled') == '0') { echo 'checked'; } ?>>
                                <label for="beatroute_webhook_enabled_no"><?php echo _l('no'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="beatroute_sync_interval" class="control-label">
                                <?php echo _l('sync_interval'); ?>
                            </label>
                            <select name="beatroute_sync_interval" id="beatroute_sync_interval" class="form-control selectpicker">
                                <option value="hourly" <?php if (get_option('beatroute_sync_interval') == 'hourly') { echo 'selected'; } ?>><?php echo _l('hourly'); ?></option>
                                <option value="daily" <?php if (get_option('beatroute_sync_interval') == 'daily') { echo 'selected'; } ?>><?php echo _l('daily'); ?></option>
                                <option value="weekly" <?php if (get_option('beatroute_sync_interval') == 'weekly') { echo 'selected'; } ?>><?php echo _l('weekly'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-md-12">
                        <h4><?php echo _l('api_settings'); ?></h4>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="api_key" class="control-label">
                                <?php echo _l('bearer_token'); ?>
                            </label>
                            <input type="text" id="api_key" name="api_key" class="form-control" value="<?php echo isset($api_config) ? $api_config->api_key : ''; ?>" required>
                            <p class="text-muted mtop5"><?php echo _l('bearer_token_help'); ?></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="api_url" class="control-label">
                                <?php echo _l('api_url'); ?>
                            </label>
                            <input type="url" id="api_url" name="api_url" class="form-control" value="<?php echo isset($api_config) ? $api_config->api_url : 'https://api.beatroute.io/v1'; ?>" required>
                            <p class="text-muted mtop5"><?php echo _l('api_url_help'); ?></p>
                            <div class="alert alert-info mtop10">
                                <strong><?php echo _l('note'); ?>:</strong> <?php echo _l('beatroute_live_skus_uses_v2_api'); ?>
                                <a href="<?php echo admin_url('beatroute_integration/live_skus'); ?>"><?php echo _l('beatroute_live_skus'); ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="webhook_secret" class="control-label">
                                <?php echo _l('webhook_secret'); ?>
                            </label>
                            <input type="password" id="webhook_secret" name="webhook_secret" class="form-control" value="<?php echo isset($api_config) ? $api_config->webhook_secret : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="webhook_url" class="control-label">
                                <?php echo _l('webhook_url'); ?>
                            </label>
                            <div class="input-group">
                                <input type="text" id="webhook_url" class="form-control" value="<?php echo site_url('beatroute_integration/webhook'); ?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="copyToClipboard('#webhook_url')">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </span>
                            </div>
                            <p class="text-muted mtop5"><?php echo _l('webhook_url_help'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="test_connection" class="control-label">
                                <?php echo _l('test_connection'); ?>
                            </label>
                            <button type="button" id="test_connection" class="btn btn-info"><?php echo _l('test_connection'); ?></button>
                            <span id="connection_test_result" class="mtop5"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer text-right">
                <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
    </div>
</div>

<script>
    $(function() {
        appValidateForm($('#beatroute-settings-form'), {
            api_key: 'required',
            api_url: 'required'
        });

        $('#test_connection').on('click', function() {
            var $btn = $(this);
            var $result = $('#connection_test_result');

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + "<?php echo _l('testing'); ?>");
            $result.html('');

            $.post(admin_url + 'beatroute_integration/test_connection', {
                api_key: $('#api_key').val(),
                api_url: $('#api_url').val()
            }).done(function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    $result.html('<div class="alert alert-success mtop10">' + response.message + '</div>');
                } else {
                    $result.html('<div class="alert alert-danger mtop10">' + response.message + '</div>');
                }
            }).fail(function() {
                $result.html('<div class="alert alert-danger mtop10"><?php echo _l('connection_test_failed'); ?></div>');
            }).always(function() {
                $btn.prop('disabled', false).html("<?php echo _l('test_connection'); ?>");
            });
        });
    });

    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).val()).select();
        document.execCommand("copy");
        $temp.remove();
        alert_float('success', "<?php echo _l('copied_to_clipboard'); ?>");
    }
</script>
<?php init_footer(); ?>
