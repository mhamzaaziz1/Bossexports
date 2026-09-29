<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Authentication extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form', 'cookie', 'language', 'clients']);

        if ($this->app->is_db_upgrade_required()) {
            redirect(admin_url());
        }

        load_admin_language();
        $this->load->model('Authentication_model');
        $this->load->library('form_validation');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));

        hooks()->do_action('admin_auth_init');
    }

    public function index()
    {
        $this->admin();
    }

    public function admin()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }

        $this->form_validation->set_rules('password', _l('admin_auth_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|valid_email');
        if (show_recaptcha()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $email    = $this->input->post('email');
                $password = $this->input->post('password', false);
                $remember = $this->input->post('remember');

                $data = $this->Authentication_model->login($email, $password, $remember, true);

                if (is_array($data) && isset($data['memberinactive'])) {
                    set_alert('danger', _l('admin_auth_inactive_account'));
                    redirect(admin_url('authentication'));
                } elseif (is_array($data) && isset($data['two_factor_auth'])) {
                    $this->session->set_userdata('_two_factor_auth_established', true);
                    if ($data['user']->two_factor_auth_enabled == 1) {
                        $this->Authentication_model->set_two_factor_auth_code($data['user']->staffid);
                        $sent = send_mail_template('staff_two_factor_auth_key', $data['user']);

                        if (!$sent) {
                            set_alert('danger', _l('two_factor_auth_failed_to_send_code'));
                            redirect(admin_url('authentication'));
                        } else {
                            $this->session->set_userdata('_two_factor_auth_staff_email', $email);
                            set_alert('success', _l('two_factor_auth_code_sent_successfully', $email));
                            redirect(admin_url('authentication/two_factor'));
                        }
                    } else {
                        set_alert('success', _l('enter_two_factor_auth_code_from_mobile'));
                        redirect(admin_url('authentication/two_factor/app'));
                    }
                } elseif ($data == false) {
                    set_alert('danger', _l('admin_auth_invalid_email_or_password'));
                    redirect(admin_url('authentication'));
                }

                $this->load->model('announcements_model');
                $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                // is logged in
                maybe_redirect_to_previous_url();

                hooks()->do_action('after_staff_login');
                redirect(admin_url());
            }
        }

        $data['title'] = _l('admin_auth_login_heading');
        $this->load->view('authentication/login_admin', $data);
    }
    public function login()
    {
        if (is_client_logged_in()) {
            redirect(site_url());
        }

        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');

        if (show_recaptcha_in_customers_area()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->form_validation->run() !== false) {
            $this->load->model('Authentication_model');

            $success = $this->Authentication_model->login(
                $this->input->post('email'),
                $this->input->post('password', false),
                $this->input->post('remember'),
                false
            );

            if (is_array($success) && isset($success['memberinactive'])) {
                set_alert('danger', _l('inactive_account'));
                redirect(site_url('authentication/login'));
            } elseif ($success == false) {
                set_alert('danger', _l('client_invalid_username_or_password'));
                redirect(site_url('authentication/login'));
            }

            if ($this->input->post('language') && $this->input->post('language') != '') {
                set_contact_language($this->input->post('language'));
            }

            $this->load->model('announcements_model');
            $this->announcements_model->set_announcements_as_read_except_last_one(get_contact_user_id());

            hooks()->do_action('after_contact_login');

            maybe_redirect_to_previous_url();
            redirect(site_url());
        }
        if (get_option('allow_registration') == 1) {
            $data['title'] = _l('clients_login_heading_register');
        } else {
            $data['title'] = _l('clients_login_heading_no_register');
        }
        $data['bodyclass'] = 'customers_login';
        
        $this->load->view('login', $data);

        // $this->data($data);
        // $this->view('login');
        // $this->layout();
    }

    public function register()
    {
        if (get_option('allow_registration') != 1 || is_client_logged_in()) {
            redirect(site_url());
        }

        $honeypot = get_option('enable_honeypot_spam_validation') == 1;

        $fields = [
            'firstname' => $honeypot ? 'firstnamemjxw' : 'firstname',
            'lastname'  => $honeypot ? 'lastnamemjxw' : 'lastname',
            'email'     => $honeypot ? 'emailmjxw' : 'email',
            'company'   => $honeypot ? 'companymjxw' : 'company',
        ];

        if (get_option('company_is_required') == 1) {
            $this->form_validation->set_rules($fields['company'], _l('client_company'), 'required');
        }

        if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions') == 1) {
            $this->form_validation->set_rules(
                'accept_terms_and_conditions',
                _l('terms_and_conditions'),
                'required',
                ['required' => _l('terms_and_conditions_validation')]
            );
        }

        $this->form_validation->set_rules($fields['firstname'], _l('client_firstname'), 'required');
        $this->form_validation->set_rules($fields['lastname'], _l('client_lastname'), 'required');
        $this->form_validation->set_rules($fields['email'], _l('client_email'), 'trim|required|is_unique[' . db_prefix() . 'contacts.email]|valid_email');
        $this->form_validation->set_rules('password', _l('clients_register_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('clients_register_password_repeat'), 'required|matches[password]');

        if (show_recaptcha_in_customers_area()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }

        $custom_fields = get_custom_fields('customers', [
            'show_on_client_portal' => 1,
            'required'              => 1,
        ]);

        $custom_fields_contacts = get_custom_fields('contacts', [
            'show_on_client_portal' => 1,
            'required'              => 1,
        ]);

        foreach ($custom_fields as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }

        foreach ($custom_fields_contacts as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }

        if ($this->input->post()) {
            if (
                $honeypot &&
                count(array_filter($this->input->post(['email', 'firstname', 'lastname', 'company']))) > 0
            ) {
                show_404();
            }

            if ($this->form_validation->run() !== false) {
                $data      = $this->input->post();
                $countryId = is_numeric($data['country']) ? $data['country'] : 0;

                if (is_automatic_calling_codes_enabled()) {
                    $customerCountry = get_country($countryId);

                    if ($customerCountry) {
                        $callingCode = '+' . ltrim($customerCountry->calling_code, '+');

                        if (startsWith($data['contact_phonenumber'], $customerCountry->calling_code)) { // with calling code but without the + prefix
                            $data['contact_phonenumber'] = '+' . $data['contact_phonenumber'];
                        } elseif (!startsWith($data['contact_phonenumber'], $callingCode)) {
                            $data['contact_phonenumber'] = $callingCode . $data['contact_phonenumber'];
                        }
                    }
                }

                define('CONTACT_REGISTERING', true);

                $clientid = $this->clients_model->add([
                    'billing_street'      => $data['address'],
                    'billing_city'        => $data['city'],
                    'billing_state'       => $data['state'],
                    'billing_zip'         => $data['zip'],
                    'billing_country'     => $countryId,
                    'firstname'           => $data[$fields['firstname']],
                    'lastname'            => $data[$fields['lastname']],
                    'email'               => $data[$fields['email']],
                    'contact_phonenumber' => $data['contact_phonenumber'],
                    'website'             => $data['website'],
                    'title'               => $data['title'],
                    'password'            => $data['passwordr'],
                    'company'             => $data[$fields['company']],
                    'vat'                 => isset($data['vat']) ? $data['vat'] : '',
                    'phonenumber'         => $data['phonenumber'],
                    'country'             => $data['country'],
                    'city'                => $data['city'],
                    'address'             => $data['address'],
                    'zip'                 => $data['zip'],
                    'state'               => $data['state'],
                    'custom_fields'       => isset($data['custom_fields']) && is_array($data['custom_fields']) ? $data['custom_fields'] : [],
                    'default_language'    => (get_contact_language() != '') ? get_contact_language() : get_option('active_language'),
                ], true);

                if ($clientid) {
                    hooks()->do_action('after_client_register', $clientid);

                    if (get_option('customers_register_require_confirmation') == '1') {
                        send_customer_registered_email_to_administrators($clientid);

                        $this->clients_model->require_confirmation($clientid);
                        set_alert('success', _l('customer_register_account_confirmation_approval_notice'));
                        redirect(site_url('authentication/login'));
                    }

                    $this->load->model('authentication_model');

                    $logged_in = $this->authentication_model->login(
                        $data[$fields['email']],
                        $this->input->post('password', false),
                        false,
                        false
                    );

                    $redUrl = site_url();

                    if ($logged_in) {
                        hooks()->do_action('after_client_register_logged_in', $clientid);
                        set_alert('success', _l('clients_successfully_registered'));
                    } else {
                        set_alert('warning', _l('clients_account_created_but_not_logged_in'));
                        $redUrl = site_url('authentication/login');
                    }

                    send_customer_registered_email_to_administrators($clientid);
                    redirect($redUrl);
                }
            }
        }

        $data['title']     = _l('clients_register_heading');
        $data['bodyclass'] = 'register';
        $data['honeypot']  = $honeypot;
        $data['fields']    = $fields;
        $this->load->view('themes/' . active_clients_theme() . '/header', $data);
$this->load->view('authentication/register', $data);
$this->load->view('themes/' . active_clients_theme() . '/footer');
        // $this->data($data);
        // $this->view('register');
        // $this->layout();
    }
    public function two_factor($type = 'email')
    {
        if (!$this->session->has_userdata('_two_factor_auth_established')) {
            show_404();
        }

        $this->form_validation->set_rules('code', _l('two_factor_authentication_code'), 'required');

        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $code  = $this->input->post('code');
                $code  = trim($code);
                $email = $this->session->userdata('_two_factor_auth_staff_email');
                if ($this->Authentication_model->is_two_factor_code_valid($code, $email) && $type = 'email') {
                    $this->session->unset_userdata('_two_factor_auth_staff_email');

                    $user = $this->Authentication_model->get_user_by_two_factor_auth_code($code);
                    $this->Authentication_model->clear_two_factor_auth_code($user->staffid);
                    $this->Authentication_model->two_factor_auth_login($user);
                    $this->session->unset_userdata('_two_factor_auth_established');
                    $this->load->model('announcements_model');
                    $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                    maybe_redirect_to_previous_url();

                    hooks()->do_action('after_staff_login');
                    redirect(admin_url());
                } elseif ($this->Authentication_model->is_google_two_factor_code_valid($code) && $type = 'app') {
                    $user = get_staff($this->session->userdata('tfa_staffid'));
                    $this->Authentication_model->two_factor_auth_login($user);
                    $this->session->unset_userdata('_two_factor_auth_established');
                    $this->load->model('announcements_model');
                    $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                    maybe_redirect_to_previous_url();

                    hooks()->do_action('after_staff_login');
                    redirect(admin_url());
                } else {
                    log_activity('Failed Two factor authentication attempt [Staff Name: ' . get_staff_full_name() . ', IP: ' . $this->input->ip_address() . ']');

                    set_alert('danger', _l('two_factor_code_not_valid'));
                    redirect(admin_url('authentication/two_factor/' . $type));
                }
            }
        }

        $this->load->view('authentication/set_two_factor_auth_code');
    }

    public function forgot_password()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|valid_email|callback_email_exists');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $success = $this->Authentication_model->forgot_password($this->input->post('email'), true);
                if (is_array($success) && isset($success['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                    redirect(admin_url('authentication/forgot_password'));
                } elseif ($success == true) {
                    set_alert('success', _l('check_email_for_resetting_password'));
                    redirect(admin_url('authentication'));
                } else {
                    set_alert('danger', _l('error_setting_new_password_key'));
                    redirect(admin_url('authentication/forgot_password'));
                }
            }
        }
        $this->load->view('authentication/forgot_password');
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        if (!$this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(admin_url('authentication'));
        }
        $this->form_validation->set_rules('password', _l('admin_auth_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                hooks()->do_action('before_user_reset_password', [
                    'staff'  => $staff,
                    'userid' => $userid,
                ]);
                $success = $this->Authentication_model->reset_password($staff, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    hooks()->do_action('after_user_reset_password', [
                        'staff'  => $staff,
                        'userid' => $userid,
                    ]);
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(admin_url('authentication'));
            }
        }
        $this->load->view('authentication/reset_password');
    }

    public function set_password($staff, $userid, $new_pass_key)
    {
        if (!$this->Authentication_model->can_set_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            if ($staff == 1) {
                redirect(admin_url('authentication'));
            } else {
                redirect(site_url('authentication'));
            }
        }
        $this->form_validation->set_rules('password', _l('admin_auth_set_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_set_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $success = $this->Authentication_model->set_password($staff, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                if ($staff == 1) {
                    redirect(admin_url('authentication'));
                } else {
                    redirect(site_url());
                }
            }
        }
        $this->load->view('authentication/set_password');
    }

    public function logout()
    {
        $this->Authentication_model->logout();
        hooks()->do_action('after_user_logout');
        redirect(admin_url('authentication'));
    }

    public function email_exists($email)
    {
        $total_rows = total_rows(db_prefix() . 'staff', [
            'email' => $email,
        ]);
        if ($total_rows == 0) {
            $this->form_validation->set_message('email_exists', _l('auth_reset_pass_email_not_found'));

            return false;
        }

        return true;
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }

    public function get_qr()
    {
        if (!is_staff_logged_in()) {
            ajax_access_denied();
        }

        $company_name = preg_replace('/:/', '-', get_option('companyname'));

        if ($company_name == '') {
            // Colons is not allowed in the issuer name
            $company_name = rtrim(preg_replace('/^https?:\/\//', '', site_url()), '/') . ' - CRM';
        }

        $data = $this->authentication_model->get_qr($company_name);
        $this->load->view('admin/includes/google_two_factor', $data);
    }
}
