<?php

defined('BASEPATH') or exit('No direct script access allowed');
set_time_limit(0);

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mailwizzsettings_model');
        $this->load->model('leads_model');

    }

    public function index()
    {

        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();
        $data['title'] = _l('mailwizz_marketing_settings');
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();

        $this->load->view('mailwizz_marketing_api_settings', $data);

    }

    public function settings_submit()
    {

        $data['public_key'] = $this->input->post('public_key', false);
        $data['secret_key'] = $this->input->post('secret_key', false);
        $data['mailwizz_marketing_base_url'] = $this->input->post('mailwizz_marketing_base_url', false);

        $data['mailwizz_marketing_lead_status'] = $this->input->post('view_status', false);
        $data['mailwizz_marketing_view_source'] = $this->input->post('view_source', false);
        $data['mailwizz_marketing_lead_assigned'] = $this->input->post('staffid', false);

        $id = $this->Mailwizzsettings_model->update_mailwizz_marketing_settings($data);
        $data['title'] = _l('mailwizz_marketing_settings');

        if ($id) {

            set_alert('success', _l('update_mailwizz_marketing_settings', _l('mailwizz_marketing')));
            redirect(admin_url('mailwizz_marketing/settings'));
        }

    }

}
