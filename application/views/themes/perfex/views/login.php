<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Hide the main navigation bar on login page */
nav.navbar.navbar-default.header {
    display: none !important; 
}
/* Also ensure the extra margin from the layout doesn't look weird */
#wrapper {
    margin-top: 0 !important;
}

/* Custom Login Page Styles */
.login-split-wrapper {
    display: flex;
    background: #fff;
    border-radius: 12px; /* Smooth rounded corners */
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow: hidden;
    min-height: 600px;
    margin-top: 50px;
    margin-bottom: 50px;
}

.login-left-section {
    flex: 1;
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #fff;
}

.login-right-section {
    flex: 1;
    background: #f0f4f8; /* Very light blue-grey */
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    border-left: 1px solid #eee;
}

.login-heading {
    text-align: center;
    font-weight: 700;
    color: #111;
    font-size: 28px;
    margin-bottom: 30px;
}

.login-form .form-group input.form-control {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
    height: 48px;
    padding: 10px 15px;
    transition: all 0.2s;
}

.login-form .form-group input.form-control:focus {
    background-color: #fff;
    border-color: #2563eb; /* Primary blue */
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.login-form .control-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-login {
    background-color: #2563eb; /* Strong blue */
    color: #fff;
    border-radius: 8px;
    padding: 14px;
    font-weight: 600;
    font-size: 16px;
    border: none;
    transition: background-color 0.2s;
    width: 100%;
}

.btn-login:hover, .btn-login:active, .btn-login:focus {
    background-color: #1d4ed8;
    color: #fff;
}

.password-label-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.right-section-content h3 {
    margin-top: 30px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 12px;
    font-size: 24px;
}
.right-section-content p {
    color: #4b5563;
    line-height: 1.6;
    max-width: 85%;
    margin: 0 auto;
    font-size: 16px;
}

@media (max-width: 768px) {
    .login-split-wrapper {
        flex-direction: column;
    }
    .login-right-section {
        display: none;
    }
    .login-left-section {
        padding: 40px 20px;
    }
}
</style>

<div class="col-md-10 col-md-offset-1">
    <div class="login-split-wrapper">
        <!-- Left Section: Login Form -->
        <div class="login-left-section">
            <h1 class="login-heading">Login</h1>
            
            <?= form_open($this->uri->uri_string(), ['class' => 'login-form']); ?>
            <?php hooks()->do_action('clients_login_form_start'); ?>

            <?php if (! is_language_disabled()) { ?>
            <div class="form-group">
                <label for="language" class="control-label"><?= _l('language'); ?></label>
                <select name="language" id="language" class="form-control selectpicker"
                    onchange="change_contact_language(this)"
                    data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                    data-live-search="true">
                    <?php $selected = (get_contact_language() != '') ? get_contact_language() : get_option('active_language'); ?>
                    <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                    <option value="<?= e($availableLanguage); ?>"
                        <?= ($availableLanguage == $selected) ? 'selected' : '' ?>>
                        <?= e(ucfirst($availableLanguage)); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>

            <div class="form-group">
                <label for="email" class="control-label"><?= _l('clients_login_email'); ?></label>
                <input type="text" autofocus="true" class="form-control" name="email" id="email" placeholder="Email Address">
                <?= form_error('email'); ?>
            </div>

            <div class="form-group">
                <div class="password-label-group">
                    <label for="password" class="control-label"><?= _l('clients_login_password'); ?></label>
                    <a href="<?= site_url('authentication/forgot_password'); ?>" class="text-muted" style="font-size:13px; color: #2563eb;"><?= _l('customer_forgot_password'); ?>?</a>
                </div>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <?= form_error('password'); ?>
            </div>

            <?php if (show_recaptcha_in_customers_area()) { ?>
            <div class="g-recaptcha tw-mb-4" data-sitekey="<?= get_option('recaptcha_site_key'); ?>"></div>
            <?= form_error('g-recaptcha-response'); ?>
            <?php } ?>

            <div class="checkbox">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember"><?= _l('clients_login_remember'); ?></label>
            </div>

            <div class="form-group tw-mt-6">
                <button type="submit" class="btn btn-login btn-block">
                    <?= _l('clients_login_login_string'); ?>
                </button>
            </div>

            <div class="tw-text-center tw-mt-4">
                <?php if (get_option('allow_registration') == 1) { ?>
                    <span class="text-muted">Don't have an account?</span> 
                    <a href="<?= site_url('authentication/register'); ?>" style="color: #2563eb; font-weight:600;">
                        <?= _l('clients_register_string'); ?>
                    </a>
                <?php } ?>
            </div>
            
            <?php hooks()->do_action('clients_login_form_end'); ?>
            <?= form_close(); ?>
        </div>

        <!-- Right Section: Illustration -->
        <div class="login-right-section">
            <div class="right-section-content">
                <!-- Illustration -->
                <img src="<?= base_url('assets/images/crm_growth_success.png'); ?>" 
                     alt="Empowering Business Growth" 
                     style="max-width: 100%; height: auto; max-height: 380px; margin-bottom: 20px; object-fit: contain;">
                
                <h3>Growth & Success</h3>
                <p>Empowering your business with streamlined project management and seamless collaboration. Achieve your goals with efficiency.</p>
                
                <!-- Simple pagination dots decoration -->
                <div style="margin-top:25px;">
                     <span style="height:4px; width:20px; background:#2563eb; display:inline-block; border-radius:2px;"></span>
                     <span style="height:4px; width:8px; background:#d1d5db; display:inline-block; border-radius:2px; margin-left:4px;"></span>
                     <span style="height:4px; width:8px; background:#d1d5db; display:inline-block; border-radius:2px; margin-left:4px;"></span>
                </div>
            </div>
        </div>
    </div>
</div>