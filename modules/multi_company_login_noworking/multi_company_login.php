<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: MultiCompanyLogin
Description: Multi Company Login Module for Perfex CRM
Version: 1.0.0
Requires at least: 2.3.2
*/

define('MULTI_COMPANY_LOGIN_MODULE_NAME', 'multi_company_login');

/**
* Register activation hook
*/
register_activation_hook(MULTI_COMPANY_LOGIN_MODULE_NAME, 'multi_company_login_activation_hook');

function multi_company_login_activation_hook()
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'company_directories')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'company_directories` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `company_name` VARCHAR(200) NOT NULL,
          `db_host` VARCHAR(200) NOT NULL,
          `db_user` VARCHAR(200) NOT NULL,
          `db_pass` VARCHAR(200) NOT NULL,
          `db_name` VARCHAR(200) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
}

/**
* App Init hook for dynamic database switching
*/
hooks()->add_action('app_init', 'multi_company_login_app_init_hook');

function multi_company_login_app_init_hook()
{
    $CI = &get_instance();
    
    // 1. Handle Login Attempt (POST)
    $post_company_id = $CI->input->post('company_id');
    if ($post_company_id && $CI->uri->segment(2) == 'authentication' && $CI->input->post('email')) {
        $company = $CI->db->get_where(db_prefix() . 'company_directories', ['id' => $post_company_id])->row();
        
        if ($company) {
            $config = [
                'hostname' => $company->db_host,
                'username' => $company->db_user,
                'password' => $company->db_pass,
                'database' => $company->db_name,
                'dbdriver' => 'mysqli',
                'db_debug' => (ENVIRONMENT !== 'production'),
            ];

            // Store in session for persistence across redirects
            $CI->session->set_userdata('active_company_config', $config);
            $CI->session->set_userdata('active_company_id', $company->id);

            // Switching logic
            try {
                // Backup master settings in case of failure
                $master_db = [
                    'hostname' => $CI->db->hostname,
                    'username' => $CI->db->username,
                    'password' => $CI->db->password,
                    'database' => $CI->db->database
                ];

                foreach ($config as $key => $val) {
                    $CI->db->$key = $val;
                }
                $CI->db->close();
                
                $mysqli = mysqli_init();
                mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 1);
                if (!@mysqli_real_connect($mysqli, $config['hostname'], $config['username'], $config['password'], $config['database'])) {
                    throw new Exception("Host Unreachable or Database Missing");
                }
                mysqli_close($mysqli);

                $CI->db->db_debug = false;
                if (!$CI->db->initialize()) {
                    throw new Exception("Connection failed");
                }
                $CI->db->db_debug = (ENVIRONMENT !== 'production');
            } catch (Exception $e) {
                // Connection failed!
                $CI->session->unset_userdata('active_company_config');
                $CI->session->unset_userdata('active_company_id');
                
                // Revert to master DB
                foreach ($master_db as $key => $val) {
                    $CI->db->$key = $val;
                }
                $CI->db->initialize();
                
                set_alert('danger', 'Failed to connect to the selected company database. Connection timed out.');
            }
            
            // Set debug info for the UI
            $CI->session->set_userdata('multi_company_last_attempt_debug', [
                'status' => 'Switched to ' . $company->company_name,
                'email' => $CI->input->post('email'),
                'company_name' => $company->company_name,
                'db_name' => $company->db_name,
                'db_host' => $company->db_host
            ]);
            
            return;
        }
    }

    // 2. Handle Continuous Session
    $config = $CI->session->userdata('active_company_config');
    if ($config) {
        try {
            // We use a temporary check to see if host is reachable
            // or just let initialize() handle it but with error suppression/catching
            
            // Backup original properties in case we need to roll back
            $original_db = [
                'hostname' => $CI->db->hostname,
                'username' => $CI->db->username,
                'password' => $CI->db->password,
                'database' => $CI->db->database
            ];

            foreach ($config as $key => $val) {
                $CI->db->$key = $val;
            }
            
            $CI->db->close();
            
            // Set a short 1-second timeout for the connection attempt to avoid hanging the site
            $mysqli = mysqli_init();
            mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 1);
            if (!@mysqli_real_connect($mysqli, $config['hostname'], $config['username'], $config['password'], $config['database'])) {
                throw new Exception("Host Unreachable or Database Missing");
            }
            mysqli_close($mysqli);

            $CI->db->db_debug = false; 
            if (!$CI->db->initialize()) {
                throw new Exception("Connection failed");
            }
            $CI->db->db_debug = (ENVIRONMENT !== 'production');
        } catch (Exception $e) {
            // Connection failed! Clear the session to prevent infinite loop of failures
            $CI->session->unset_userdata('active_company_config');
            $CI->session->unset_userdata('active_company_id');
            
            // Revert to master DB
            foreach ($original_db as $key => $val) {
                $CI->db->$key = $val;
            }
            $CI->db->initialize();
            
            set_alert('danger', 'Failed to connect to the selected company database. Connection timed out.');
        }
    }
}

/**
* Logout cleanup
*/
hooks()->add_action('before_staff_logout', 'multi_company_login_before_staff_logout_hook');
function multi_company_login_before_staff_logout_hook()
{
    $CI = &get_instance();
    $CI->session->unset_userdata('active_company_config');
    $CI->session->unset_userdata('active_company_id');
    $CI->session->unset_userdata('multi_company_last_attempt_debug');
}

/**
* Filter upload paths to keep files isolated per company
*/
hooks()->add_filter('get_upload_path_by_type', 'multi_company_login_filter_upload_paths', 10, 2);
function multi_company_login_filter_upload_paths($path, $type)
{
    $CI = &get_instance();
    $company_id = $CI->session->userdata('active_company_id');
    if ($company_id) {
        $custom_path = 'uploads/company_' . $company_id . '/';
        if (!is_dir(FCPATH . $custom_path)) {
            mkdir(FCPATH . $custom_path, 0755, true);
        }
        // This is a simple implementation; real mapping would be more complex
        // But for many types, we prefix the company subfolder
        return $custom_path . $path;
    }
    return $path;
}

/**
* Admin menu item
*/
hooks()->add_action('admin_init', 'multi_company_login_admin_menu_items');
function multi_company_login_admin_menu_items()
{
    $CI = &get_instance();
    // Only show "Multi Company" if we are on the Master database and an admin
    if (is_admin() && !$CI->session->userdata('active_company_id')) {
        $CI->app_menu->add_sidebar_menu_item('multi_company_login_menu', [
            'name'     => 'Multi Company',
            'href'     => admin_url('multi_company_login'),
            'icon'     => 'fa fa-university',
            'position' => 30,
        ]);
    }
}

/**
* Add company selection dropdown to the login form
*/
hooks()->add_action('after_admin_login_form_start', 'multi_company_login_inject_company_dropdown');

function multi_company_login_inject_company_dropdown()
{
    $CI = &get_instance();
    // We must ensure we are on Master DB to get the list of companies
    // If we are already switched (due to a previous failed attempt), we temporarily switch back
    $current_is_switched = $CI->session->userdata('active_company_id');
    
    // Get companies from Master DB (using the original config from app-config.php)
    require_once(APPPATH . 'config/app-config.php');
    $master_db = mysqli_init();
    mysqli_options($master_db, MYSQLI_OPT_CONNECT_TIMEOUT, 1);
    @mysqli_real_connect($master_db, APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);

    $companies = [];
    if($master_db && !$master_db->connect_error) {
        $res = $master_db->query("SELECT * FROM " . db_prefix() . "company_directories");
        if($res) {
            while($row = $res->fetch_assoc()) { $companies[] = $row; }
        }
        $master_db->close();
    }

    // Prepare company catalog for JS feedback
    $company_catalog = [];
    $company_catalog[''] = [
        'name' => 'Master / Main Database',
        'db' => APP_DB_NAME,
        'host' => APP_DB_HOSTNAME
    ];
    foreach ($companies as $company) {
        $company_catalog[$company['id']] = [
            'name' => $company['company_name'],
            'db' => $company['db_name'],
            'host' => $company['db_host']
        ];
    }
    
    $options = '<option value="">Master / Main Database</option>';
    foreach ($companies as $company) {
        $selected = ($current_is_switched == $company['id']) ? 'selected' : '';
        $options .= '<option value="' . $company['id'] . '" ' . $selected . '>' . $company['company_name'] . '</option>';
    }
    ?>
    <style>
        #company_id {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236B7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            cursor: pointer;
            border: 2px solid #e1e4e8 !important;
            font-weight: 500;
        }
        #company_id:focus {
            border-color: #FFC107 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.1) !important;
        }
        .db-feedback-badge {
            animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="form-group" style="margin-bottom: 2rem;">
        <label for="company_id" class="control-label" style="display:block; margin-bottom: 0.75rem; font-weight: 700; color: #111827; font-size: 0.95rem;">
            Select Directory
        </label>
        <select name="company_id" id="company_id" class="form-control-custom" style="width:100%; border-radius: 6px; padding: 10px;">
            <?php echo $options; ?>
        </select>
        <div id="company_db_notice" style="margin-top: 1rem; display: <?php echo $current_is_switched ? 'block' : 'none'; ?>;">
            <div class="db-feedback-badge" style="padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center;">
                <i class="fa fa-database" style="margin-right: 10px;"></i>
                <span>Database: <strong id="notice_db_name"></strong> on <strong id="notice_db_host"></strong></span>
            </div>
        </div>
    </div>
          
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const select = document.getElementById("company_id");
            const notice = document.getElementById("company_db_notice");
            const dbName = document.getElementById("notice_db_name");
            const dbHost = document.getElementById("notice_db_host");
            const data = <?php echo json_encode($company_catalog); ?>;

            function updateNotice(val) {
                const info = data[val];
                if(info) {
                    notice.style.display = "block";
                    dbName.textContent = info.db;
                    dbHost.textContent = info.host;
                    if(val !== "") {
                        testConnection(val, info);
                    }
                } else {
                    notice.style.display = "none";
                }
            }

            select.addEventListener("change", function() {
                updateNotice(this.value);
            });
            
            // Trigger on load if something is selected (failed attempt)
            if(select.value !== "") { updateNotice(select.value); }

            function testConnection(companyId, info) {
                const badge = notice.querySelector('.db-feedback-badge');
                dbHost.innerHTML = info.host + ' <i class="fa fa-spinner fa-spin" style="margin-left: 5px;"></i>';
                
                fetch("<?php echo site_url('multi_company_login/gate/test_db'); ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + companyId + "&<?php echo $CI->security->get_csrf_token_name(); ?>=<?php echo $CI->security->get_csrf_hash(); ?>"
                })
                .then(response => response.json())
                .then(res => {
                    if(res.success) {
                        badge.style.background = "#ecfdf5";
                        badge.style.borderColor = "#6ee7b7";
                        badge.style.color = "#065f46";
                        dbHost.innerHTML = info.host + ' <i class="fa fa-check-circle" style="color: #10b981; margin-left: 5px;"></i>';
                    } else {
                        badge.style.background = "#fef2f2";
                        badge.style.borderColor = "#fca5a5";
                        badge.style.color = "#991b1b";
                        dbHost.innerHTML = res.message + ' <i class="fa fa-times-circle" style="color: #ef4444; margin-left: 5px;"></i>';
                    }
                });
            }
        });
    </script>
    <?php
    // Show debug info for the last login attempt
    $last_attempt = $CI->session->userdata('multi_company_last_attempt_debug');
    if ($last_attempt) {
        $CI->session->unset_userdata('multi_company_last_attempt_debug');
        echo '<div class="alert alert-info" style="font-size: 11px; margin-bottom: 2rem; background-color: #f0f7ff; border-color: #c0d9ff; color: #1e429f;">
                <p style="margin-bottom: 5px; font-weight: 700; text-transform: uppercase;">Last Login Attempt Details:</p>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><strong>Status:</strong> ' . $last_attempt['status'] . '</li>
                    <li><strong>Email:</strong> ' . $last_attempt['email'] . '</li>
                    <li><strong>Company:</strong> ' . $last_attempt['company_name'] . '</li>
                    <li><strong>Database:</strong> ' . $last_attempt['db_name'] . ' (' . $last_attempt['db_host'] . ')</li>
                </ul>
              </div>';
    }
}
