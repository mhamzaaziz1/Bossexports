<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo hooks()->apply_filters('appointments_form_title', _l('appointment_create_new_appointment')); ?></title>

    <?php app_external_form_header($form); ?>

    <!-- Add Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="<?= module_dir_url('appointly', 'assets/css/appointments_external_form.css'); ?>" rel="stylesheet" type="text/css">

    <style>
        /* Language Dropdown Styles */
        .language-selector {
            z-index: 10;
        }

        .language-selector select {
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
            transition: all 0.2s ease;
            font-size: 14px;
            cursor: pointer;
        }

        .language-selector select:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .language-selector select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Better mobile responsiveness */
        @media (max-width: 768px) {
            .appointment-header {
                flex-direction: column;
                align-items: center !important;
                gap: 1rem;
            }

            .appointment-header h4 {
                order: 1;
                text-align: center !important;
            }

            .appointment-header>div:last-child {
                order: 2;
                width: 100%;
                justify-content: center !important;
            }

            .appointment-header>div:first-child {
                display: none;
            }
        }

        .language-selector select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .language-selector select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #f7fafc;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .appointment-header {
                flex-direction: column;
                align-items: stretch !important;
            }

            .language-selector {
                align-self: flex-end;
                margin-top: 0.5rem;
            }

            .language-selector select {
                min-width: 100px;
            }
        }
    </style>

    <script>
        // Configure Tailwind
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        .logo-header {
            text-align: center;
            padding: 20px 0;
        }

        .logo {
            margin-top: 30px;
        }

        .logo-header .logo {
            display: inline-block;
            max-width: 200px;
        }

        .logo-header .logo img {
            max-height: 60px;
            width: auto;
        }

        .logo-header .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .logo-header .logo-text:hover {
            text-decoration: none;
            color: #0ea5e9;
        }
    </style>
</head>

<body class="appointments-external-form tw-bg-neutral-50"
    <?php if (is_rtl(true)) {
        echo ' dir="rtl"';
    } ?>>
    <?php
    $clientUserData = $this->session->userdata();

    $this->load->model('appointly/appointly_model');

    // Get external form settings
    $externalHeading = get_option('external_form_heading');
    if (empty($externalHeading)) {
        $externalHeading = _l('appointment_schedule_appointment');
    }

    $externalDescription = get_option('external_form_description');
    if (empty($externalDescription)) {
        $externalDescription = _l('appointment_schedule_description');
    }

    // The baseCurrency is now passed from controller, no need to load it again
    if (is_client_logged_in()) {
        $logo = get_dark_company_logo($uri = '', $href_class = '');
        echo '<div class="logo-header">' . $logo . '</div>';
    }

    ?>
    <div id="wrapper" class="tw-min-h-screen tw-py-4 md:tw-py-12">
        <div id="content">
            <div class="container tw-px-2 md:tw-px-4">
                <div id="response"></div>


                <?php echo form_open('appointly/appointments_public/create_external_appointment', ['id' => 'appointment-form', 'class' => 'form-booking-steps', 'autocomplete' => 'off', 'data-prevent-submit' => 'true']); ?>
                <input type="hidden" name="rel_type" value="external">
                <input type="hidden" name="current_language" id="current_language" value="<?= $current_language ?>">
                <input type="hidden" name="hidden_staff_id" id="hidden_staff_id" value="">
                <input type="hidden" name="service_id" id="service_id" value="">
                <input type="hidden" name="staff_id" id="staff_id" value="">
                <input type="hidden" name="date" id="appointment_date_field" value="">
                <input type="hidden" name="start_hour" id="start_hour" value="">
                <input type="hidden" name="end_hour" id="end_hour" value="">

                <div class="row">
                    <div class="tw-rounded-md main_wrapper <?= ($this->input->get('col')) ?: 'col-md-8 col-md-offset-2' ?>">
                        <!-- Header Section -->
                        <div class="appointment-header tw-flex tw-justify-between tw-items-center tw-flex-wrap tw-gap-4 tw-mb-4">
                            <div class="tw-flex-1"></div>
                            <h4 class="tw-text-2xl tw-font-bold tw-text-neutral-900 tw-text-center">
                                <?= $externalHeading; ?>
                            </h4>
                            <div class="tw-flex-1 tw-flex tw-justify-end">

                                <?php if (isset($show_language_dropdown) && $show_language_dropdown && count($available_languages) > 1): ?>
                                    <!-- Language Dropdown -->
                                    <div class="language-selector tw-flex-shrink-0">
                                        <select id="language-dropdown" class="tw-form-select tw-text-sm tw-border tw-border-neutral-300 tw-rounded-lg tw-px-3 tw-py-2 tw-bg-white tw-min-w-[120px] tw-shadow-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                                            <?php
                                            // Language name mappings
                                            $language_names = [
                                                'english' => '🇺🇸 English',
                                                'spanish' => '🇪🇸 Español',
                                                'french' => '🇫🇷 Français',
                                                'german' => '🇩🇪 Deutsch',
                                                'italian' => '🇮🇹 Italiano',
                                                'dutch' => '🇳🇱 Nederlands',
                                                'portuguese_br' => '🇧🇷 Português',
                                                'polish' => '🇵🇱 Polski',
                                                'turkish' => '🇹🇷 Türkçe',
                                                'persian' => '🇮🇷 فارسی',
                                                'indonesia' => '🇮🇩 Indonesia'
                                            ];

                                            // Show English first, then other languages alphabetically
                                            $sorted_languages = [];
                                            if (in_array('english', $available_languages)) {
                                                $sorted_languages[] = 'english';
                                            }
                                            foreach ($available_languages as $lang) {
                                                if ($lang !== 'english') {
                                                    $sorted_languages[] = $lang;
                                                }
                                            }

                                            foreach ($sorted_languages as $lang):
                                                $display_name = isset($language_names[$lang]) ? $language_names[$lang] : ucfirst($lang);
                                            ?>
                                                <option value="<?= $lang; ?>" <?= ($lang === $current_language) ? 'selected' : ''; ?>>
                                                    <?= $display_name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="tw-text-neutral-600 tw-mt-5 tw-text-center">
                            <?= $externalDescription; ?>
                        </p>
                        <hr class="tw-border-t tw-border-neutral-200 tw-my-6" />

                        <!-- Progress steps) -->
                        <div>
                            <!-- Progress Steps Container -->
                            <div class="progress-steps-container tw-relative">
                                <!-- Step Indicators with Lines -->
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <!-- Step 1 -->
                                    <div class="step-indicator step-1 tw-text-center tw-flex tw-flex-col tw-items-center active" id="step-indicator-1">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-primary-500 tw-text-white tw-font-medium tw-mb-2">1</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_select_service'); ?></div>
                                    </div>

                                    <!-- Line 1-2 -->
                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-1-2"></div>

                                    <!-- Step 2 -->
                                    <div class="step-indicator step-2 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-2">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">2</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_select_provider'); ?></div>
                                    </div>

                                    <!-- Line 2-3 -->
                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-2-3"></div>

                                    <!-- Step 3 -->
                                    <div class="step-indicator step-3 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-3">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">3</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_date_time'); ?></div>
                                    </div>

                                    <!-- Line 3-4 -->
                                    <div class="step-line tw-mt-6 tw-flex-1 tw-h-1 tw-bg-neutral-300 tw-mx-2" id="line-3-4"></div>

                                    <!-- Step 4 -->
                                    <div class="step-indicator step-4 tw-text-center tw-flex tw-flex-col tw-items-center" id="step-indicator-4">
                                        <div class="step-number tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-neutral-300 tw-text-white tw-font-medium tw-mb-2">4</div>
                                        <div class="step-title tw-text-sm"><?= _l('appointment_your_details'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall Progress Bar -->
                            <div class="tw-mt-6 tw-bg-neutral-200 tw-h-2 tw-rounded-full tw-relative">
                                <div class="tw-h-full tw-bg-primary-500 tw-rounded-full progress-bar tw-absolute tw-left-0 tw-top-0" style="width: 25%"></div>
                            </div>
                        </div>
                        <hr class="tw-border-t tw-border-neutral-200 tw-my-6" />

                        <!-- Step 1: Service Selection -->
                        <div class="booking-step step-1-content active" id="step-1">
                            <h4 class="tw-text-lg tw-font-semibold tw-mb-4"><?= _l('appointments_service_heading'); ?></h4>
                            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-<?= min(2, count($services)) ?> lg:tw-grid-cols-<?= min(3, count($services)) ?> tw-gap-3 md:tw-gap-4">
                                <?php
                                // Get services from the model
                                if (!empty($services)) :
                                    foreach ($services as $service) :
                                        // Encode service data for the modal
                                        $serviceJson = htmlspecialchars(json_encode($service), ENT_QUOTES, 'UTF-8');
                                ?>
                                        <div class="service-card tw-bg-white tw-border tw-border-neutral-200 tw-p-4 tw-rounded-xl tw-transition-all hover:tw-border-primary-500 <?= isset($service['color']) ? 'service-card-' . trim($service['color'], '#') : '' ?>"
                                            onclick="window.selectService(<?= $service['id'] ?>);"
                                            data-service-id="<?= $service['id'] ?>"
                                            data-price="<?= isset($service['price']) ? app_format_money($service['price'], $baseCurrency) : 'Free' ?>"
                                            data-duration="<?= $service['duration'] ?>">
                                            <div class="service-header tw-flex tw-justify-between tw-items-center tw-mb-3">
                                                <h5 class="tw-font-semibold tw-text-lg"><?= $service['name'] ?></h5>
                                                <?php if (!empty($service['color'])) : ?>
                                                    <span class="tw-h-4 tw-w-4 tw-rounded-full" style="background-color: <?= $service['color'] ?>"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="service-details">
                                                <div class="tw-flex tw-justify-between tw-mb-1">
                                                    <span class="tw-text-neutral-600"><?= _l('appointment_service_duration') ?>:</span>
                                                    <span class="tw-font-medium"><?= $service['duration'] ?> <?= _l('appointly_duration_minutes') ?></span>
                                                </div>
                                                <?php if (isset($service['price']) && $service['price'] > 0) : ?>
                                                    <div class="tw-flex tw-justify-between tw-mb-1">
                                                        <span class="tw-text-neutral-600"><?= _l('appointment_service_price') ?>:</span>
                                                        <span class="tw-font-medium">
                                                            <?= app_format_money($service['price'], $baseCurrency); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($service['description']) && !empty($service['description'])) : ?>
                                                    <div class="tw-mt-2">
                                                        <p class="tw-text-neutral-600 tw-text-sm"><?= $service['description'] ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php
                                    endforeach;
                                else :
                                    ?>
                                    <div class="col-md-12">
                                        <div class="alert alert-warning">
                                            <?= _l('no_services_available') ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (count($services) > 0) : ?>
                                <!-- Navigation buttons -->
                                <div class="tw-flex tw-justify-end tw-mt-6">
                                    <button type="button" class="btn btn-primary btn-next" data-step="1"><?= _l('appointment_continue'); ?></button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Step 2: Provider Selection -->
                        <div class="booking-step step-2-content hidden" id="step-2">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0"><?= _l('appointment_select_provider'); ?></h4>
                            </div>
                            <div id="providers-container" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-3 md:tw-gap-4">
                                <!-- Providers will be loaded here dynamically -->
                                <div class="tw-col-span-full tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-12">
                                    <div class="tw-w-12 tw-h-12 tw-border-4 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin tw-mb-4"></div>
                                    <p class="tw-text-neutral-600 tw-text-center"><?= _l('appointment_loading_providers'); ?></p>
                                </div>
                            </div>

                            <!-- Navigation buttons -->
                            <div class="tw-flex tw-justify-end tw-mt-6">
                                <button type="button" class="btn btn-primary btn-next" data-step="2"><?= _l('appointment_continue'); ?></button>
                            </div>
                        </div>

                        <!-- Step 3: Date and Time -->
                        <div class="booking-step step-3-content hidden" id="step-3">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0"><?= _l('appointment_date_time'); ?></h4>
                            </div>

                            <!-- Date & Timezone Section -->
                            <div class="tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 md:tw-p-5 tw-mb-6">
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                                    <!-- Date Picker -->
                                    <div class="tw-flex tw-flex-col">
                                        <label for="appointment-date" class="tw-font-medium tw-mb-2"><?= _l('appointment_date'); ?></label>
                                        <div class="tw-relative">
                                            <input type="text" id="appointment-date" class="form-control tw-w-full" placeholder="<?= _l('appointment_select_date'); ?>" readonly onchange="return false;">
                                            <!-- Loading indicator for date picker -->
                                            <div id="date_loading" class="tw-absolute tw-right-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 hide">
                                                <div class="tw-w-5 tw-h-5 tw-border-2 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timezone Selector -->
                                    <div class="tw-flex tw-flex-col">
                                        <label class="tw-font-medium tw-mb-2"><?= _l('appointment_timezone') ?></label>
                                        <div class="input-group tw-w-full">
                                            <span class="input-group-addon">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                            <select name="timezone" id="timezone" class="form-control selectpicker tw-w-full" data-width="100%" data-live-search="true">
                                                <?php foreach (get_timezones_list() as $region => $timezones) { ?>
                                                    <optgroup label="<?= $region; ?>">
                                                        <?php foreach ($timezones as $timezone) { ?>
                                                            <option value="<?= $timezone; ?>"
                                                                <?= get_option('default_timezone') == $timezone ? 'selected' : ''; ?>>
                                                                <?= $timezone; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Slots Section (initially hidden) -->
                            <div id="time-slots-section" class="tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 md:tw-p-5 tw-hidden">
                                <h5 class="tw-font-medium tw-mb-4"><?= _l('appointly_available_time_slots'); ?></h5>
                                <div id="time-slots-container" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-3">
                                </div>
                                <!-- Loading indicator for time slots -->
                                <div id="slot-loading" class="tw-text-center tw-py-8 tw-hidden">
                                    <div class="tw-w-8 tw-h-8 tw-mx-auto tw-border-2 tw-border-primary-500 tw-border-t-transparent tw-rounded-full tw-animate-spin"></div>
                                    <p class="tw-mt-2 tw-text-sm tw-text-neutral-600"><?= _l('appointment_loading'); ?></p>
                                </div>
                            </div>

                            <!-- Navigation buttons -->
                            <div class="tw-flex tw-justify-end tw-mt-6">
                                <button type="button" class="btn btn-primary btn-next" data-step="3" disabled><?= _l('appointment_continue'); ?></button>
                            </div>
                        </div>

                        <!-- Step 4: Contact Information -->
                        <div class="booking-step step-4-content hidden" id="step-4">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                                <a href="#" class="btn-prev-step tw-text-primary-600 hover:tw-text-primary-700 tw-transition-colors tw-duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 tw-inline tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <?= _l('appointment_back'); ?>
                                </a>
                                <h4 class="tw-text-xl tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_your_details'); ?></h4>
                            </div>
                            <!-- Enhanced Appointment Summary -->
                            <div class="tw-mb-8 tw-rounded-lg tw-border tw-border-neutral-200 tw-shadow-sm tw-p-6">
                                <div class="tw-flex tw-items-center tw-mb-4">
                                    <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_summary'); ?></h5>
                                </div>
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
                                    <div class="tw-bg-white tw-p-4">
                                        <div class="tw-flex tw-items-center tw-mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4 tw-text-primary-500 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_service'); ?></span>
                                        </div>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-service-name">-</p>
                                        <p class="tw-text-sm tw-text-primary-600 tw-m-0 tw-font-medium tw-mt-1" id="final-service-price">-</p>
                                    </div>
                                    <div class="tw-bg-white tw-p-4">
                                        <div class="tw-flex tw-items-center tw-mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4 tw-text-primary-500 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_provider'); ?></span>
                                        </div>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-provider-name">-</p>
                                    </div>
                                    <div class="tw-bg-white tw-p-4">
                                        <div class="tw-flex tw-items-center tw-mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4 tw-text-primary-500 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('appointment_date_time'); ?></span>
                                        </div>
                                        <p class="tw-font-semibold tw-text-neutral-900 tw-m-0 tw-text-lg" id="final-date-time">-</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject & Description Section -->
                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                <div class="tw-flex tw-items-center tw-mb-4">
                                    <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_details'); ?></h5>
                                </div>
                                <div class="tw-space-y-4">
                                    <div class="form-group tw-mb-4">
                                        <label for="subject" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_subject') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="subject" id="subject" required>
                                    </div>
                                    <div class="form-group tw-mb-0">
                                        <label for="description" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_description') ?></label>
                                        <textarea class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="description" id="description" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <?php
                            // Check if client is logged in and get their contact info
                            $logged_in_client_data = [];
                            if (is_client_logged_in()) {
                                $contact_id = get_contact_user_id();
                                $client_id = get_client_user_id();
                                $CI = &get_instance();
                                $CI->db->select('firstname, lastname, email, phonenumber');
                                $CI->db->where('id', $contact_id);
                                $logged_in_contact = $CI->db->get(db_prefix() . 'contacts')->row();

                                if ($logged_in_contact) {
                                    $logged_in_client_data = [
                                        'firstname' => $logged_in_contact->firstname,
                                        'lastname' => $logged_in_contact->lastname,
                                        'email' => $logged_in_contact->email,
                                        'phone' => $logged_in_contact->phonenumber,
                                        'contact_id' => $contact_id
                                    ];
                                }
                            }
                            ?>
                            <input type="hidden" name="logged_in_client_id" value="<?= is_client_logged_in() ? get_client_user_id() : '' ?>">
                            <input type="hidden" name="logged_in_contact_id" value="<?= isset($logged_in_client_data['contact_id']) ? $logged_in_client_data['contact_id'] : '' ?>">

                            <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                <div class="tw-flex tw-items-center tw-mb-4">
                                    <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_contact'); ?></h5>
                                </div>
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                                    <div class="form-group tw-mb-4">
                                        <label for="firstname" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('client_firstname') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="firstname" id="firstname" value="<?= isset($logged_in_client_data['firstname']) ? htmlspecialchars($logged_in_client_data['firstname']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="lastname" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('client_lastname') ?> <small class="req text-danger">*</small></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="lastname" id="lastname" value="<?= isset($logged_in_client_data['lastname']) ? htmlspecialchars($logged_in_client_data['lastname']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="email" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_your_email') ?> <small class="req text-danger">*</small></label>
                                        <input type="email" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="email" id="email" value="<?= isset($logged_in_client_data['email']) ? htmlspecialchars($logged_in_client_data['email']) : '' ?>" required>
                                    </div>
                                    <div class="form-group tw-mb-4">
                                        <label for="phone" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_your_phone') ?></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="phone" id="phone" value="<?= isset($logged_in_client_data['phone']) ? htmlspecialchars($logged_in_client_data['phone']) : '' ?>" placeholder="<?= _l('appointment_your_phone_example') ?>">
                                    </div>
                                    <div class="form-group tw-mb-0 tw-col-span-full">
                                        <label for="address" class="control-label tw-text-sm tw-font-medium tw-text-neutral-700 tw-mb-2"><?= _l('appointment_location_address') ?></label>
                                        <input type="text" class="form-control tw-border-neutral-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-sm focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-duration-200" name="address" id="address" value="<?= isset($logged_in_client_data['address']) ? htmlspecialchars($logged_in_client_data['address']) : '' ?>" placeholder="<?= _l('appointment_location_placeholder'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Fields Section -->
                            <?php $custom_fields = get_custom_fields('appointly'); ?>
                            <?php if (!empty($custom_fields)): ?>

                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <div class="tw-flex tw-items-center tw-mb-4">
                                        <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                        </div>
                                        <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_additional_info'); ?></h5>
                                    </div>
                                    <div class="tw-space-y-4">
                                        <?= render_custom_fields('appointly'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Terms & Conditions if enabled -->
                            <?php if (get_option('appointments_enable_terms_conditions')): ?>

                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <div class="tw-flex tw-items-center tw-mb-4">
                                        <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_terms_link'); ?></h5>
                                    </div>
                                    <div class="form-group tw-mb-0">
                                        <div class="checkbox checkbox-primary tw-flex tw-items-start tw-flex-col">
                                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" required class="tw-mr-3 tw-mt-1">
                                            <label for="terms_accepted" class="tw-text-sm tw-text-neutral-700 tw-cursor-pointer">
                                                <?= _l('appointment_accept_terms') ?>
                                            </label>
                                        </div>
                                        <p class="text-muted small tw-mt-2 tw-text-xs tw-text-neutral-500"><?= _l('appointment_terms_description') ?> <a href="<?= site_url('terms_and_conditions') ?>" target="_blank" class="tw-text-primary-600 hover:tw-text-primary-700 tw-underline"><?= _l('appointment_terms_link') ?></a></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- reCAPTCHA -->
                            <?php if ($form->recaptcha == 1): ?>
                                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-neutral-200 tw-mb-6 tw-shadow-sm tw-p-6">
                                    <div class="tw-flex tw-items-center tw-mb-4">
                                        <div class="tw-bg-primary-500 tw-rounded-full tw-p-2 tw-mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <h5 class="tw-text-lg tw-font-semibold tw-m-0 tw-text-neutral-800"><?= _l('appointment_security_verification'); ?></h5>
                                    </div>
                                    <div class="form-group tw-mb-0">
                                        <div class="g-recaptcha" data-sitekey="<?= get_option('recaptcha_site_key'); ?>"></div>
                                        <div id="recaptcha_response_field" class="text-danger tw-mt-2"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Submit Button -->
                            <div class="tw-flex tw-justify-end tw-mt-8">
                                <button type="submit" class="btn-primary btn-lg tw-w-full sm:tw-w-auto tw-transform hover:tw-scale-105" id="book-appointment-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-mr-2 tw-inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <?= _l('appointment_book_now') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    </div>

    <?php app_external_form_footer($form); ?>

    <?php if (isset($form)): ?>
        <script>
            site_url = "<?= site_url(); ?>";
            appointlyLang = {
                appointment_select_service: "<?= addslashes(_l('appointment_select_service')); ?>",
                appointment_select_provider: "<?= addslashes(_l('appointment_select_provider')); ?>",
                appointment_date_time: "<?= addslashes(_l('appointment_date_time')); ?>",
                appointment_your_details: "<?= addslashes(_l('appointment_your_details')); ?>",
                appointment_summary: "<?= addslashes(_l('appointment_summary')); ?>",
                appointment_book_now: "<?= addslashes(_l('appointment_book_now')); ?>",
                appointment_submitting: "<?= addslashes(_l('appointment_submitting')); ?>",
                service_required: "<?= addslashes(_l('appointment_service_required')); ?>",
                provider_required: "<?= addslashes(_l('appointment_select_provider_warning')); ?>",
                date_required: "<?= addslashes(_l('appointment_date_required')); ?>",
                time_required: "<?= addslashes(_l('appointment_time_required')); ?>",
                name_required: "<?= addslashes(_l('appointment_name_required')); ?>",
                email_required: "<?= addslashes(_l('appointment_email_required')); ?>",
                email_invalid: "<?= addslashes(_l('appointment_email_invalid')); ?>",
                select_time: "<?= addslashes(_l('appointment_select_time')); ?>",
                loading: "<?= addslashes(_l('appointment_loading')); ?>",
                error_loading_providers: "<?= addslashes(_l('appointment_error_loading_providers')); ?>",
                no_providers: "<?= addslashes(_l('appointment_no_providers')); ?>",
                no_working_hours: "<?= addslashes(_l('appointly_no_working_hours_found')); ?>",
                no_providers_with_hours: "<?= addslashes(_l('appointly_no_providers_with_hours')); ?>",
                closed: "<?= addslashes(_l('appointment_closed')); ?>",
                error_loading_slots: "<?= addslashes(_l('appointment_error_loading_slots')); ?>",
                no_slots_available: "<?= addslashes(_l('appointment_no_slots_available')); ?>",
                monday: "<?= addslashes(_l('monday')); ?>",
                tuesday: "<?= addslashes(_l('tuesday')); ?>",
                wednesday: "<?= addslashes(_l('wednesday')); ?>",
                thursday: "<?= addslashes(_l('thursday')); ?>",
                friday: "<?= addslashes(_l('friday')); ?>",
                saturday: "<?= addslashes(_l('saturday')); ?>",
                sunday: "<?= addslashes(_l('sunday')); ?>",
                minutes: "<?= addslashes(_l('appointment_minutes')); ?>",
                submitting: "<?= addslashes(_l('appointment_submitting')); ?>",
                view_details: "<?= addslashes(_l('appointment_view_details')); ?>",
                select: "<?= addslashes(_l('appointment_select')); ?>",
                appointment_unavailable: "<?= addslashes(_l('appointment_unavailable')); ?>",
                appointment_unavailable_slots_shown: "<?= addslashes(_l('appointment_unavailable_slots_shown')); ?>",
                firstname_required: "<?= addslashes(_l('client_firstname') . ' ' . _l('is_required')); ?>",
                lastname_required: "<?= addslashes(_l('client_lastname') . ' ' . _l('is_required')); ?>"
            }
            app.locale = "<?= get_locale_key($form->language); ?>";
            // Pass the currency symbol to JavaScript
            var baseCurrencySymbol = "<?= is_object($baseCurrency) ? $baseCurrency->symbol : $baseCurrency ?>";
            // Pass the setting for showing staff email addresses
            var appointlyShowStaffEmail = "<?= get_option('appointly_show_staff_email') != '0' ? '1' : '0' ?>";
            // Pass the setting for showing staff phone numbers
            var appointlyShowStaffPhone = "<?= get_option('appointly_show_staff_phone') != '0' ? '1' : '0' ?>";

            // Add CSRF token variables for AJAX requests
            <?php if ($this->security->get_csrf_token_name() && $this->security->get_csrf_hash()) { ?>
                var csrfTokenName = "<?= $this->security->get_csrf_token_name(); ?>";
                var csrfTokenValue = "<?= $this->security->get_csrf_hash(); ?>";
            <?php } ?>
        </script>
    <?php endif; ?>

    <!-- Include the helper JS file first -->
    <script src="<?= module_dir_url('appointly', 'assets/js/helpers/appointly_helpers.js'); ?>?v=<?= time(); ?>"></script>

    <!-- Then include the form-specific JS -->
    <?php require('modules/appointly/assets/js/appointments_external_form_js.php'); ?>

    <!-- Fix terms checkbox by cleaning validation artifacts -->
    <script>
        $(document).ready(function() {
            $('#terms_accepted').on('click change', function() {
                var $checkbox = $(this);
                var $formGroup = $checkbox.closest('.form-group');

                // Remove all validation-related attributes
                $checkbox.removeAttr('aria-invalid');
                $checkbox.removeAttr('aria-describedby');

                // Remove validation error classes
                $formGroup.removeClass('has-error');
                $checkbox.removeClass('error');

                // Remove error message elements
                $formGroup.find('label.error').remove();
                $formGroup.find('.text-danger').not('.req').remove();
                $('#terms_accepted-error').remove();

                // Force checkbox visual state update
                if ($checkbox.is(':checked')) {
                    $checkbox.prop('checked', true);
                }
            });
        });
    </script>

    <!-- Provider Details Modal -->
    <div id="providerDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title provider-name"></h4>
                </div>
                <div class="modal-body">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                        <div class="tw-w-20 tw-h-20 tw-rounded-full tw-overflow-hidden tw-bg-neutral-100">
                            <img src="" alt="" class="provider-avatar tw-w-full tw-h-full tw-object-cover">
                        </div>
                        <div>
                            <h5 class="provider-name tw-font-medium tw-text-xl tw-text-neutral-900"></h5>
                            <p class="provider-email tw-text-sm tw-text-neutral-600"></p>
                            <p class="provider-phone tw-text-sm tw-text-neutral-600"></p>
                        </div>
                    </div>

                    <div class="provider-schedule">
                        <h6 class="tw-font-medium tw-mb-3"><?= _l('appointly_working_hours'); ?></h6>
                        <div class="schedule-list tw-space-y-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="button" class="btn btn-primary select-provider" data-provider-id=""><?= _l('service_provider_select'); ?></button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
