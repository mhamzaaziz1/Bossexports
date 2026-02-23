<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#dev_mode_settings" aria-controls="dev_mode_settings" role="tab" data-toggle="tab">
                    <?php echo 'Dev Mode Settings'; ?>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="dev_mode_settings">
        <div class="alert alert-warning">
            <strong>Warning:</strong> Changing the environment mode will modify the <code>index.php</code> file. Ensure the file is writable. Switching to "Development" environment will show errors on the screen, which is useful for debugging but can expose sensitive information. Use with caution.
        </div>
        <div class="form-group">
            <label for="dev_mode_environment" class="control-label clearfix"><?php echo 'Environment Mode'; ?></label>
            <div class="radio radio-primary radio-inline">
                <input type="radio" id="env_production" name="dev_mode_environment" value="production" <?php if (ENVIRONMENT == 'production') {
    echo 'checked';
} ?>>
                <label for="env_production"><?php echo 'Production'; ?></label>
            </div>
            <div class="radio radio-primary radio-inline">
                <input type="radio" id="env_development" name="dev_mode_environment" value="development" <?php if (ENVIRONMENT == 'development') {
    echo 'checked';
} ?>>
                <label for="env_development"><?php echo 'Development'; ?></label>
            </div>
        </div>
    </div>
</div>
