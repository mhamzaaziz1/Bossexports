<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">

        <!-- Enable Logging -->
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="hidden" name="settings[n8n_connector_enable_logging]" value="0">
                <input type="checkbox" name="settings[n8n_connector_enable_logging]"
                    id="n8n_connector_enable_logging"
                    value="1"
                    <?php echo get_option('n8n_connector_enable_logging') ? 'checked' : ''; ?>>
                <label for="n8n_connector_enable_logging">
                    <?php echo _l('n8n_enable_logging'); ?>
                </label>
            </div>
            <p class="text-muted">
                <?php echo _l('n8n_enable_logging_help'); ?>
            </p>
        </div>
        <hr>

        <!-- Auto Remove Logs -->
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="hidden" name="settings[n8n_connector_auto_remove_logs]" value="0">
                <input type="checkbox" name="settings[n8n_connector_auto_remove_logs]"
                    id="n8n_connector_auto_remove_logs"
                    value="1"
                    <?php echo get_option('n8n_connector_auto_remove_logs') ? 'checked' : ''; ?>>
                <label for="n8n_connector_auto_remove_logs">
                    <?php echo _l('n8n_auto_remove_logs'); ?>
                </label>
            </div>
            <p class="text-muted">
                <?php echo _l('n8n_auto_remove_logs_help'); ?>
            </p>
        </div>
        <div class="row">
            <!-- Log Retention Days -->
            <div class="form-group col-md-6 no-margin">
                <label for="n8n_connector_log_retention_days" class="control-label">
                    <?php echo _l('n8n_log_retention_days'); ?>
                </label>
                <input type="number"
                    name="settings[n8n_connector_log_retention_days]"
                    id="n8n_connector_log_retention_days"
                    class="form-control"
                    value="<?php echo get_option('n8n_connector_log_retention_days', 30); ?>"
                    min="1"
                    max="365">
                <p class="text-muted">
                    <?php echo _l('n8n_log_retention_days_help'); ?>
                </p>
            </div>

            <!-- Request Timeout -->
            <div class="form-group col-md-6 no-margin">
                <label for="n8n_connector_default_timeout" class="control-label">
                    <?php echo _l('n8n_request_timeout'); ?>
                </label>
                <input type="number"
                    name="settings[n8n_connector_default_timeout]"
                    id="n8n_connector_default_timeout"
                    class="form-control"
                    value="<?php echo get_option('n8n_connector_default_timeout', 5); ?>"
                    min="1"
                    max="60">
                <p class="text-muted">
                    <?php echo _l('n8n_request_timeout_help'); ?>
                </p>
            </div>
        </div>
        <hr>

        <!-- Enable Signatures -->
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="hidden" name="settings[n8n_connector_enable_signatures]" value="0">
                <input type="checkbox" name="settings[n8n_connector_enable_signatures]"
                    id="n8n_connector_enable_signatures"
                    value="1"
                    <?php echo get_option('n8n_connector_enable_signatures') ? 'checked' : ''; ?>>
                <label for="n8n_connector_enable_signatures">
                    <?php echo _l('n8n_enable_signatures'); ?>
                </label>
            </div>
            <p class="text-muted">
                <?php echo _l('n8n_enable_signatures_help'); ?>
            </p>
        </div>
        <hr>

        <!-- Retry Settings -->
        <h4 class="bold"><?php echo _l('n8n_retry_settings'); ?></h4>

        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="hidden" name="settings[n8n_connector_retry_enabled]" value="0">
                <input type="checkbox" name="settings[n8n_connector_retry_enabled]"
                    id="n8n_connector_retry_enabled"
                    value="1"
                    <?php echo get_option('n8n_connector_retry_enabled') ? 'checked' : ''; ?>>
                <label for="n8n_connector_retry_enabled">
                    <?php echo _l('n8n_retry_enabled'); ?>
                </label>
            </div>
            <p class="text-muted">
                <?php echo _l('n8n_retry_enabled_help'); ?>
            </p>
        </div>
        <div class="row">
            <div class="form-group col-md-6 no-margin">
                <label for="n8n_connector_max_retries" class="control-label">
                    <?php echo _l('n8n_max_retries'); ?>
                </label>
                <input type="number"
                    name="settings[n8n_connector_max_retries]"
                    id="n8n_connector_max_retries"
                    class="form-control"
                    value="<?php echo get_option('n8n_connector_max_retries', 3); ?>"
                    min="0"
                    max="10">
                <p class="text-muted">
                    <?php echo _l('n8n_max_retries_help'); ?>
                </p>
            </div>

            <div class="form-group col-md-6 no-margin">
                <label for="n8n_connector_retry_delay" class="control-label">
                    <?php echo _l('n8n_retry_delay'); ?>
                </label>
                <input type="number"
                    name="settings[n8n_connector_retry_delay]"
                    id="n8n_connector_retry_delay"
                    class="form-control"
                    value="<?php echo get_option('n8n_connector_retry_delay', 60); ?>"
                    min="1"
                    max="600">
                <p class="text-muted">
                    <?php echo _l('n8n_retry_delay_help'); ?>
                </p>
            </div>
        </div>

    </div>
</div>