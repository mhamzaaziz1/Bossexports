<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#login_settings" aria-controls="login_settings" role="tab" data-toggle="tab">
                    Login Settings
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="login_settings">
        <h4 class="bold">Default Login Page</h4>
        <p class="text-muted">Choose which login page should be displayed when accessing the base URL of your application.</p>
        
        <div class="form-group">
            <label for="default_login_page" class="control-label clearfix">Landing Page</label>
            <div class="radio radio-primary">
                <input type="radio" id="login_admin" name="settings[default_login_page]" value="admin" <?php if (get_option('default_login_page') == 'admin' || !get_option('default_login_page')) {
    echo 'checked';
} ?>>
                <label for="login_admin">
                    <strong>Admin Login</strong>
                    <span class="text-muted"> - Show admin/staff login page</span>
                </label>
            </div>
            <div class="radio radio-primary">
                <input type="radio" id="login_client" name="settings[default_login_page]" value="client" <?php if (get_option('default_login_page') == 'client') {
    echo 'checked';
} ?>>
                <label for="login_client">
                    <strong>Client Login</strong>
                    <span class="text-muted"> - Show client/customer login page</span>
                </label>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Note:</strong> This setting only affects the default landing page. Users can still access both login pages directly via their respective URLs:
            <ul class="mtop10">
                <li>Admin Login: <code><?php echo admin_url('authentication'); ?></code></li>
                <li>Client Login: <code><?php echo site_url('authentication/login'); ?></code></li>
            </ul>
        </div>
    </div>
</div>
