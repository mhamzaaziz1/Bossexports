<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<!-- Tailwind CDN first -->
<link href="<?= module_dir_url('authentication','src/output.css'); ?>" rel="stylesheet">


<!-- Custom Tailwind Config with Prefix -->
<script>
  if (window.tailwind) {
    window.tailwind.config = {
      prefix: 'tw-', // Prefix for all Tailwind classes
      theme: {
        extend: {},
      },
    };
  }
</script>


<body class="tw-bg-neutral-100 login_admin" style="background-color: #0c73ea;">

    <div class="tw-min-h-screen tw-flex tw-flex-col md:tw-flex-row tw-w-full">

        <!-- Left Section (Desktop only) -->
        <div class="tw-hidden lg:tw-flex tw-w-3/5 tw-relative tw-bg-center tw-bg-no-repeat"
            style="background-image: url('<?php echo module_dir_url('authentication', 'assets/images/left-sec-image-4.webp'); ?>'); background-size: contain; background-position:center;">

            <!-- Heading -->
            <div class="tw-absolute tw-top-[15%] tw-left-1/2 tw-transform tw--translate-x-1/2 tw-text-center tw-w-[80%]">
                <h1 class="tw-text-white tw-text-3xl tw-font-bold tw-leading-snug tw-tracking-wide tw-max-w-[500px] tw-mx-auto tw-break-words">
                    “ Where great minds<br>Collaborate for greater results!”
                </h1>
            </div>
        </div>

        <!-- Right Section -->
        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center 
            tw-w-full md:tw-w-full lg:tw-w-2/5 xl:tw-w-[45%] 
            tw-p-6 tw-overflow-hidden 
            tw-min-h-screen md:tw-min-h-[600px] lg:tw-min-h-screen
            tw-bg-white tw-bg-[url('<?php echo  module_dir_url('authentication','assets/images/left-sec-image-5.webp'); ?>')] 
            tw-bg-cover tw-bg-center tw-bg-no-repeat 
            md:tw-rounded-none lg:tw-rounded-tl-[50px] lg:tw-rounded-bl-[50px]">

            <div class="tw-w-[90%] sm:tw-w-[85%] md:tw-w-[80%] lg:tw-w-[70%] xl:tw-w-[65%] tw-max-w-lg">

                <!-- Logo -->
                <div class="company-logo text-center tw-mx-auto tw-w-125">
                    <?php get_dark_company_logo(); ?>
                </div>

                <!-- Form container -->
                <?php echo form_open($this->uri->uri_string()); ?>
                <div class="tw-justify-center tw-p-0 tw-w-full tw-mx-auto"
                    style="background-color: rgba(198,225,250,0.5); border-radius: 40px;">

                    <div class="tw-mx-0 tw-py-8 tw-px-8 sm:tw-px-10 tw-shadow tw-text-white"
                        style="background: linear-gradient(to top, #2069eb, #02aaef); border-radius: 40px;">

                        <!-- Form Heading -->
                        <h1 class="tw-text-2xl tw-font-semibold tw-mb-5 text-center tw-py-0.5">
                            Sign In
                        </h1>

                        <?php $this->load->view('authentication/includes/alerts'); ?>
                        <?php echo validation_errors('<div class="alert alert-danger text-center tw-py-0.5">', '</div>'); ?>
                        <?php hooks()->do_action('after_admin_login_form_start'); ?>

                        <!-- Email Field -->
                        <div class="form-group tw-py-0.5">
                            <label for="email" class="control-label tw-text-white tw-py-0.5">Email Address</label>
                            <input type="email" id="email" name="email"
                                class="form-control tw-bg-white tw-text-blue-600 tw-border tw-border-white tw-py-0.5 tw-w-full"
                                autofocus="1">
                        </div>

                        <!-- Password Field -->
                        <div class="form-group tw-relative tw-py-0.5">
                            <label for="password" class="control-label tw-text-white tw-py-0.5">Password</label>
                            <input type="password" id="password" name="password"
                                class="form-control tw-bg-white tw-text-blue-600 tw-border tw-border-white tw-py-0.5 tw-pr-10 tw-w-full">

                            <!-- Eye icon button -->
                            <button type="button" id="togglePassword"
                                class="tw-absolute tw-right-2 tw-top-1/2 tw--translate-y-1/2 tw--mr-[-15px] tw--mt-[-15px] tw-text-gray-600 hover:tw-text-gray-900">
                                👁️
                            </button>
                        </div>

                        <!-- Forgot Password -->
                        <div class="tw-text-right tw-mb-4 tw-py-0.5">
                            <a href="<?php echo admin_url('authentication/forgot_password'); ?>"
                                class="tw-text-white hover:tw-underline">
                                Forgot Password?
                            </a>
                        </div>

                    </div>

                    <!-- Sign In Button -->
                    <div class="tw-flex tw-justify-center tw-py-8 tw-rounded-b-lg">
                        <button type="submit"
                            class="tw-w-4/5 tw-py-2 tw-border tw-border-white tw-text-white tw-bg-[#0f8dec] tw-rounded-lg">
                            Sign In
                        </button>
                    </div>
                </div>
                <?php hooks()->do_action('before_admin_login_form_close'); ?>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</body>


<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
    });
</script>