<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<style>
    body.login_admin {
        margin: 0;
        padding: 0;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
    }
    .login-container {
        display: flex;
        height: 100vh;
        width: 100%;
    }
    .login-left {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 2rem;
        background-color: #f8f9fa;
        overflow-y: auto;
    }
    .login-wrapper {
        width: 100%;
        max-width: 450px;
        margin: 0 auto;
    }
    .login-right {
        display: none;
        width: 50%;
        position: relative;
        background-color: #eeeeee;
    }
    .login-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-size: cover;
        background-position: center;
        background-image: url('<?= base_url('assets/images/login_bg.png'); ?>');
    }
    .login-heading {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    .login-subheading {
        color: #6b7280;
        font-size: 0.875rem;
    }
    .form-control-custom {
        width: 100%;
        display: block;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        height: auto !important;
    }
    .form-control-custom:focus {
        border-color: #FFC107;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    .btn-custom-yellow {
        background-color: #FFC107;
        color: #111827;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: none;
        width: 100%;
        transition: background-color 0.2s;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .btn-custom-yellow:hover {
        background-color: #FFB300;
        color: #000;
    }
    .company-logo {
        margin-bottom: 2rem;
        padding: 0;
    }
    .company-logo img {
        margin: 0;
        max-height: 60px;
    }
    
    @media (min-width: 1024px) {
        .login-left {
            width: 50%;
            padding: 0 6rem;
        }
        .login-right {
            display: block;
        }
    }
</style>

<body class="login_admin">
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-wrapper">
                <!-- Branding -->
                <div class="tw-mb-8 company-logo-wrapper">
                    <div class="company-logo">
                         <?php get_dark_company_logo(); ?>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h1 class="login-heading">
                        Employee Login
                    </h1>
                    <p class="login-subheading">
                        Please enter your details to sign in
                    </p>
                </div>

                <?php $this->load->view('authentication/includes/alerts'); ?>

                <?= form_open($this->uri->uri_string(), ['class' => 'login-form']); ?>
                
                <?= validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

                <?php hooks()->do_action('after_admin_login_form_start'); ?>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="email" class="control-label" style="display:block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                        <?= _l('admin_auth_login_email'); ?>
                    </label>
                    <input type="email" id="email" name="email" class="form-control-custom" autofocus="1" placeholder="Enter your email">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="password" class="control-label" style="display:block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">
                        <?= _l('admin_auth_login_password'); ?>
                    </label>
                    <input type="password" id="password" name="password" class="form-control-custom" placeholder="••••••••">
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                     <div class="checkbox checkbox-inline" style="margin: 0; display: flex; align-items: center;">
                        <input type="checkbox" id="remember" name="remember" style="margin-top: 0;">
                        <label for="remember" style="margin-left: 0.5rem; padding-left: 0;">
                            <?= _l('admin_auth_login_remember_me'); ?>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-custom-yellow">
                    <?= _l('admin_auth_login_button'); ?>
                </button>
                
                 <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="<?= admin_url('authentication/forgot_password'); ?>" style="font-size: 0.875rem; color: #6b7280; text-decoration: none;">
                        <?= _l('admin_auth_login_fp'); ?>
                    </a>
                </div>

                <?php hooks()->do_action('before_admin_login_form_close'); ?>
                
                <?= form_close(); ?>
                
                 <div style="margin-top: 2rem; text-align: center; font-size: 0.75rem; color: #9ca3af;">
                    &copy; <?= date('Y'); ?> <?= get_option('companyname'); ?>. All rights reserved.
                </div>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="login-right">
             <div class="login-bg-image">
                 <div class="tw-absolute tw-inset-0 tw-bg-black/10" style="background: rgba(0,0,0,0.1); width: 100%; height: 100%;"></div>
             </div>
        </div>
    </div>
</body>
</html>