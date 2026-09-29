<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="no-margin tw-text-neutral-700 tw-font-medium tw-text-xl">
                            <?php echo $webhook ? _l('n8n_edit_webhook') : _l('n8n_add_webhook'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open($this->uri->uri_string(), ['id' => 'webhook-form']); ?>

                        <!-- Webhook Name -->
                        <?php echo render_input(
                            'name',
                            'n8n_webhook_name',
                            $webhook ? $webhook->name : '',
                            'text',
                            ['required' => true]
                        ); ?>

                        <!-- Webhook URL -->
                        <?php echo render_input('webhook_url', 'n8n_webhook_url', $webhook ? $webhook->webhook_url : '', 'url', ['required' => true, 'placeholder' => 'https://your-n8n-instance.com/webhook/...'], [], 'tw-mb-0'); ?>
                        <small class="text-muted mbot15 display-block">
                            <?php echo _l('n8n_webhook_url_help'); ?>
                        </small>

                        <!-- Event Types -->
                        <div class="form-group">
                            <label for="enabled_events"><span class="text-danger">*</span>
                                <?php echo _l('n8n_event_types'); ?>
                            </label>
                            <select
                                id="enabled_events"
                                name="enabled_events[]"
                                class="selectpicker"
                                multiple
                                data-width="100%"
                                data-live-search="true"
                                data-actions-box="true">
                                <?php
                                $selected_events = [];
                                if ($webhook && !empty($webhook->enabled_events)) {
                                    $selected_events = is_array($webhook->enabled_events)
                                        ? $webhook->enabled_events
                                        : json_decode($webhook->enabled_events, true);
                                }
                                ?>

                                <?php foreach ($all_events as $module_key => $module_data): ?>
                                    <optgroup label="<?php echo htmlspecialchars($module_data['label']); ?>">

                                        <?php foreach ($module_data['events'] as $event_key => $event): ?>
                                            <option
                                                value="<?php echo $event_key; ?>"
                                                <?php echo in_array($event_key, $selected_events) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($event['label']); ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                <?php echo _l('n8n_event_types_help'); ?>
                            </small>
                        </div>

                        <!-- Secret Key -->
                        <label for="secret_key">
                            <?php echo _l('n8n_secret_key'); ?>
                            <i class="fa fa-question-circle"
                                data-toggle="tooltip"
                                title="<?php echo _l('n8n_secret_key_help'); ?>"></i>
                        </label>
                        <div class="form-group no-margin">
                            <div class="input-group">
                                <input type="text" class="form-control" name="secret_key" data-ays-ignore="true" value="<?= $webhook ? $webhook->secret_key : '' ?>"
                                    placeholder="<?= _l('n8n_secret_key'); ?>">
                                <div class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="generateSecret()">
                                        <i class="fa fa-key"></i> <?php echo _l('n8n_generate'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?php echo _l('n8n_secret_key_description'); ?>
                        </small>

                        <!-- Advanced Configuration -->
                        <hr>
                        <h4 class="bold"><?php echo _l('n8n_advanced_settings'); ?></h4>

                        <!-- Custom Headers -->
                        <div class="form-group">
                            <label for="custom_headers">
                                <?php echo _l('n8n_custom_headers'); ?>
                                <i class="fa fa-question-circle"
                                    data-toggle="tooltip"
                                    title="<?php echo _l('n8n_custom_headers_help'); ?>"></i>
                            </label>
                            <textarea name="custom_headers" id="custom_headers" rows="4" class="form-control"
                                placeholder='{"Authorization": "Bearer token", "X-Custom-Header": "value"}'><?php
                                                                                                            if ($webhook && !empty($webhook->custom_headers)) {
                                                                                                                $headers = is_string($webhook->custom_headers) ? json_decode($webhook->custom_headers, true) : $webhook->custom_headers;
                                                                                                                echo htmlspecialchars(json_encode($headers, JSON_PRETTY_PRINT));
                                                                                                            }
                                                                                                            ?></textarea>
                            <small class="text-muted"><?php echo _l('n8n_custom_headers_description'); ?></small>
                        </div>

                        <div class="row mbot15">
                            <!-- Include Relationships -->
                            <div class="col-md-6">
                                <div class="form-group no-margin">
                                    <div class="checkbox checkbox-primary no-margin">
                                        <input type="checkbox" name="include_relationships" id="include_relationships" value="1"
                                            <?php echo (!$webhook || $webhook->include_relationships) ? 'checked' : ''; ?>>
                                        <label for="include_relationships">
                                            <?php echo _l('n8n_include_relationships'); ?>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo _l('n8n_include_relationships_help'); ?></small>
                            </div>

                            <!-- Track Changes -->
                        </div>

                        <div class="row">
                            <!-- Run in Background -->
                            <div class="col-md-12">
                                <div class="form-group no-margin">
                                    <div class="checkbox checkbox-primary no-margin">
                                        <input type="checkbox" name="run_in_background" id="run_in_background" value="1"
                                            <?php echo ($webhook && $webhook->run_in_background) ? 'checked' : ''; ?>>
                                        <label for="run_in_background">
                                            <?php echo _l('n8n_run_in_background'); ?>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo _l('n8n_run_in_background_help'); ?></small>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        <!-- Action Buttons -->
                        <div class="form-group pull-right tw-mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                            </button>

                            <button type="button" class="btn btn-info" onclick="testWebhookForm()">
                                <i class="fa fa-paper-plane"></i> <?php echo _l('n8n_test_webhook'); ?>
                            </button>

                            <a href="<?php echo admin_url(N8N_CONNECTOR_MODULE . '/webhooks'); ?>"
                                class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    var allEventsData = <?php echo json_encode($all_events); ?>;

    $(function() {
        // Initialize selectpicker
        $('.selectpicker').selectpicker('refresh');

        appValidateForm($("#webhook-form"), {
            webhook_url: 'required',
            name: 'required',
        });

        $('#enabled_events').rules('add', {
            selectpickerRequired: true
        });

        $.validator.addMethod('selectpickerRequired', function(value, element) {
            return $(element).val() && $(element).val().length > 0;
        }, 'This field is required.');
    });

    function generateSecret() {
        $.ajax({
            url: '<?= admin_url(N8N_CONNECTOR_MODULE . '/generate_secret') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('input[name="secret_key"]').val(response.secret);
                } else {
                    alert_float('danger', 'Can not create signature');
                }
            },
            error: function() {
                alert('Server error while generating secret');
            }
        });
    }

    function testWebhookForm() {
        var url = $('input[name="webhook_url"]').val();
        var secret = $('input[name="secret_key"]').val();

        if (!url) {
            alert_float('danger', '<?php echo _l('n8n_webhook_url_required'); ?>');
            return;
        }

        var btn = event.target;
        $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('n8n_testing'); ?>...');

        $.post('<?php echo admin_url(N8N_CONNECTOR_MODULE . '/test_webhook'); ?>', {
            webhook_url: url,
            secret_key: secret
        }, function(response) {
            $(btn).prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo _l('n8n_test_webhook'); ?>');

            if (response.success) {
                alert_float('success', '<?php echo _l('n8n_test_webhook_success'); ?> (' + response.status_code + ')');
            } else {
                alert_float('danger', '<?php echo _l('n8n_test_webhook_failed'); ?>: ' + (response.error || 'Unknown error'));
            }
        }, 'json').fail(function() {
            $(btn).prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo _l('n8n_test_webhook'); ?>');
            alert_float('danger', '<?php echo _l('n8n_test_webhook_failed'); ?>');
        });
    }
</script>

</body>

</html>