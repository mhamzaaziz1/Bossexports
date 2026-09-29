<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Gradient background for login form */
        .login-gradient {
            background: linear-gradient(to top, #2069eb, #02aaef);
            border-radius: 40px;
            padding-bottom: 28px;
        }

        .login-button {
            background: linear-gradient(to right, #099cee, #0f75eb);
            color: white;
            font-weight: 600;
            border: none;
        }

        .login-button:hover {
            background: linear-gradient(to right, #0f75eb, #099cee);
        }

        .register-button {
            background: linear-gradient(to right, #38a84f, #28c13f);
            color: white;
            font-weight: 600;
            border: none;
        }

        .register-button:hover {
            background: linear-gradient(to right, #28c13f, #38a84f);
        }
    </style>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }
    </script>
</head>

<body class="d-flex flex-column min-vh-100">

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left Section -->
            <div class="d-none d-lg-flex col-lg-7 col-xl-6 align-items-center justify-content-center"
                style="background-image: url('<?= module_dir_url('authentication', 'assets/images/right-sec-image.webp'); ?>'); background-size: cover; background-position: center;">
                <div class="text-center text-white px-4">
                    <!-- Optional text/logo -->
                </div>
            </div>

            <!-- Right Section -->
            <div class="col-12 col-lg-5 col-xl-6 d-flex align-items-center justify-content-center"
                style="min-height: 100vh; background-image: url('<?= module_dir_url('authentication', 'assets/images/right-sec-image-3.webp'); ?>'); background-size: cover; background-position: center; border-left: 2px solid #0f75eb; border-top-left-radius: 70px; border-bottom-left-radius: 80px;">

                <div class="w-100 px-4 px-md-5" style="max-width: 450px;">

                    <!-- Header Text -->
                    <div class="text-center mb-4">
                        <h4 style="font-size:18px; font-family:'MLUKavitha', sans-serif; color: rgb(56,83,164);">
                            Your Trusted Partner in Technology and Innovation
                        </h4>
                        <div class="company-logo" style="text-align:center; margin:0 auto; width:280px;">
                            <img src="<?php echo  module_dir_url('authentication','assets/images/logo.png'); ?>" alt="Company Logo" style="width:100%; height:auto;">
                        </div>

                    </div>

                    <!-- Login Card -->
                    <div class="login-gradient shadow p-4 text-white">

                        <h1 class="text-center mb-4 fw-bold" style="font-size: 34px;">Login</h1>

                        <?php echo form_open($this->uri->uri_string(), ['class' => 'login-form']); ?>
                        <?php hooks()->do_action('clients_login_form_start'); ?>

                        <!-- Language -->
                        <?php if (!is_language_disabled()) { ?>
                            <div class="mb-3 text-start">
                                <label for="language" class="form-label">Language</label>
                                <select name="language" id="language" class="form-select" style="background-color:#099cee; color:white; border:1px solid #0f75eb;">
                                    <?php $selected = (get_contact_language() != '') ? get_contact_language() : get_option('active_language'); ?>
                                    <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                                        <option value="<?= $availableLanguage; ?>" <?= ($availableLanguage == $selected) ? 'selected' : '' ?>>
                                            <?= ucfirst($availableLanguage); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <!-- Email -->
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="text" id="email" name="email" autofocus class="form-control" />
                            <?php echo form_error('email'); ?>
                        </div>

                        <!-- Password -->
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" />
                                <button type="button" class="btn btn-outline-light" onclick="togglePassword()">👁️</button>
                            </div>
                            <?php echo form_error('password'); ?>
                        </div>

                        <!-- Recaptcha -->
                        <?php if (show_recaptcha_in_customers_area()) { ?>
                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="<?= get_option('recaptcha_site_key'); ?>"></div>
                                <?php echo form_error('g-recaptcha-response'); ?>
                            </div>
                        <?php } ?>

                        <!-- Register -->
                        <?php if (get_option('allow_registration') == 1) { ?>
                            <a href="<?= site_url('authentication/register'); ?>" class="btn register-button w-100 mb-3">Register</a>
                        <?php } ?>

                        <!-- Forgot Password -->
                        <div class="text-center mb-3">
                            <a href="<?= site_url('authentication/forgot_password'); ?>" class="text-white text-decoration-none">
                                Forgot Password?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn login-button w-100">Login</button>

                        <?php hooks()->do_action('clients_login_form_end'); ?>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

</body>