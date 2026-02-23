<div class="tab-pane" id="environment">
    <?php if (is_admin()) { ?>
    <h4 class="bold">
        <?php echo _l('environment_settings'); ?>
    </h4>
    <p class="text-muted">
        <?php echo _l('environment_settings_info'); ?>
    </p>
    <hr />
    <?php echo render_input('settings[environment_mode]', 'environment_mode', get_option('environment_mode'), 'text', ['list' => 'environment_modes']); ?>
    <datalist id="environment_modes">
        <option value="development">
        <option value="production">
        <option value="testing">
    </datalist>
    <hr />
    <div class="alert alert-warning">
        <strong><?php echo _l('warning'); ?>!</strong> <?php echo _l('environment_mode_warning'); ?>
    </div>
    <?php } else { ?>
    <div class="alert alert-danger">
        <?php echo _l('access_denied'); ?>
    </div>
    <?php } ?>
</div>
