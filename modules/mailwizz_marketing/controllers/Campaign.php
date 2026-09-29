<?php

defined('BASEPATH') or exit('No direct script access allowed');
set_time_limit(0);

class Campaign extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('campaign_model');
        $this->load->model('Mailwizzsettings_model');

    }

    public function index()
    {

        $data['title'] = _l('mailwizz_marketing_new_campaign');

        $this->load->view('mailwizz_marketing_new_campaign', $data);

    }

    public function campaign_list()
    {

        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();

        $settings = [
            'public_key' => $data['settings'][0]['public_key'], // Create a new user
            'secret_key' => $data['settings'][0]['secret_key'], // Make it a secure password
        ];
        $apiUrl = $data['settings'][0]['mailwizz_marketing_base_url'];

        // configuration object
        $config = new MailWizzApi_Config(array(
            'apiUrl' => $apiUrl,
            'publicKey' => $settings['public_key'],
            'privateKey' => $settings['secret_key'],

            // components
            'components' => array(
                'cache' => array(
                    'class' => 'MailWizzApi_Cache_File',
                    'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
                ),
            ),
        ));

        // now inject the configuration and we are ready to make api calls
        MailWizzApi_Base::setConfig($config);

        // CREATE THE ENDPOINT
        $endpoint = new MailWizzApi_Endpoint_Campaigns();

        /*===================================================================================*/

        // GET ALL ITEMS
        $response = $endpoint->getCampaigns($pageNumber = 1, $perPage = 10);

        $res_campaigns = $response->body;

        $response_campaigns = $res_campaigns['data']['records'];

        foreach ($response_campaigns as $cmp) {

            $id = $cmp['campaign_uid'];
            $exist = $this->campaign_model->check_campaign_exist($id);

            if (empty($exist)) {

                $data2 = array(
                    'id' => $id,
                    'mailwizz_marketing_campaign_name' => $cmp['name'],
                    'mailwizz_marketing_campaign_status' => $cmp['status'],
                    'mailwizz_marketing_campaign_group' => $cmp['group']['name'],

                );
                $this->campaign_model->insert_campaign($data2);

            }

        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('mailwizz_marketing', 'mailwizz_marketing_campaign_list'));
        }
        $data['title'] = _l('mailwizz_marketing_campaign_list');
        $this->load->view('mailwizz_marketing_campaign', $data);

    }

    public function mailwizz_marketing_create_campaign()
    {
        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();

        // ApiAuth->newAuth() will accept an array of Auth settings
        $settings = [
            'public_key' => $data['settings'][0]['public_key'], // Create a new user
            'secret_key' => $data['settings'][0]['secret_key'], // Make it a secure password
        ];
        $apiUrl = $data['settings'][0]['mailwizz_marketing_base_url'];

        // configuration object
        $config = new MailWizzApi_Config(array(
            'apiUrl' => $apiUrl,
            'publicKey' => $settings['public_key'],
            'privateKey' => $settings['secret_key'],

            // components
            'components' => array(
                'cache' => array(
                    'class' => 'MailWizzApi_Cache_File',
                    'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
                ),
            ),
        ));

        // now inject the configuration and we are ready to make api calls
        MailWizzApi_Base::setConfig($config);

        // create the lists endpoint:
        $endpoint = new MailWizzApi_Endpoint_Lists();

        // GET ALL ITEMS
        $response = $endpoint->getLists($pageNumber = 1, $perPage = 10);

        $data['list'] = $response->body['data'];
        
        $data['title'] = _l('mailwizz_marketing_create_campaign');
        $this->load->view('mailwizz_marketing_create_campaign', $data);

    }

    public function mailwizz_marketing_add_campaign()
    {

        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();

        // ApiAuth->newAuth() will accept an array of Auth settings
        $settings = [
            'public_key' => $data['settings'][0]['public_key'], // Create a new user
            'secret_key' => $data['settings'][0]['secret_key'], // Make it a secure password
        ];
        $apiUrl = $data['settings'][0]['mailwizz_marketing_base_url'];

        // configuration object
        $config = new MailWizzApi_Config(array(
            'apiUrl' => $apiUrl,
            'publicKey' => $settings['public_key'],
            'privateKey' => $settings['secret_key'],

            // components
            'components' => array(
                'cache' => array(
                    'class' => 'MailWizzApi_Cache_File',
                    'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
                ),
            ),
        ));

        // now inject the configuration and we are ready to make api calls
        MailWizzApi_Base::setConfig($config);

        // CREATE THE ENDPOINT
        $endpoint = new MailWizzApi_Endpoint_Campaigns();

        // CREATE CAMPAIGN
        $response = $endpoint->create(array(
            'name' => $this->input->post('mailwizz_marketing_campaign_name', false), // required
            'type' => 'regular', // optional: regular or autoresponder
            'from_name' => $this->input->post('mailwizz_marketing_from_name', false), // required
            'from_email' => $this->input->post('mailwizz_marketing_from_email', false), // required
            'subject' => $this->input->post('mailwizz_marketing_subject', false), // required
            'reply_to' => $this->input->post('mailwizz_marketing_to_email', false), // required
            'send_at' => date('Y-m-d H:i:s', strtotime('+10 hours')), // required, this will use the timezone which customer selected
            'list_uid' => $this->input->post('lisd_id', false), // required
            'segment_uid' => '', // optional, only to narrow down

            // optional block, defaults are shown
            'options' => array(
                'url_tracking' => 'no', // yes | no
                'json_feed' => 'no', // yes | no
                'xml_feed' => 'no', // yes | no
                'plain_text_email' => 'yes', // yes | no
                'email_stats' => null, // a valid email address where we should send the stats after campaign done

                // - if autoresponder uncomment bellow:
                //'autoresponder_event'            => 'AFTER-SUBSCRIBE', // AFTER-SUBSCRIBE or AFTER-CAMPAIGN-OPEN
                //'autoresponder_time_unit'        => 'hour', // minute, hour, day, week, month, year
                //'autoresponder_time_value'       => 1, // 1 hour after event
                //'autoresponder_open_campaign_id' => 1, // INT id of campaign, only if event is AFTER-CAMPAIGN-OPEN,

                // - if this campaign is advanced recurring, you can set a cron job style frequency.
                // - please note that this applies only for regular campaigns.
                //'cronjob'         => '0 0 * * *', // once a day
                //'cronjob_enabled' => 1, // 1 or 0
            ),

            // required block, archive or template_uid or content => required.
            'template' => array(
                //'archive'         => file_get_contents(dirname(__FILE__) . '/template-example.zip'),
                //'template_uid'    => 'TEMPLATE-UNIQUE-ID',
                'content' => file_get_contents(dirname(__FILE__) . '/template-example.html'),
                'inline_css' => 'no', // yes | no
                'plain_text' => null, // leave empty to auto generate
                'auto_plain_text' => 'yes', // yes | no
            ),
        ));

        if (isset($response)) {

            set_alert('success', _l('mailwizz_marketing_campaign_added', _l('mailwizz_marketing')));
            redirect(admin_url('mailwizz_marketing/campaign/campaign_list'));

        }

    }

    public function create_list()
    {

        $data['title'] = _l('mailwizz_list_button');
        $this->load->view('mailwizz_marketing_create_list', $data);

    }

    public function list_submit()
    {
        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();

        // ApiAuth->newAuth() will accept an array of Auth settings
        $settings = [
            'public_key' => $data['settings'][0]['public_key'], // Create a new user
            'secret_key' => $data['settings'][0]['secret_key'], // Make it a secure password
        ];
        $apiUrl = $data['settings'][0]['mailwizz_marketing_base_url'];

        // configuration object
        $config = new MailWizzApi_Config(array(
            'apiUrl' => $apiUrl,
            'publicKey' => $settings['public_key'],
            'privateKey' => $settings['secret_key'],

            // components
            'components' => array(
                'cache' => array(
                    'class' => 'MailWizzApi_Cache_File',
                    'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
                ),
            ),
        ));

        // now inject the configuration and we are ready to make api calls
        MailWizzApi_Base::setConfig($config);

        // CREATE THE ENDPOINT
        $endpoint = new MailWizzApi_Endpoint_Lists();

        // create a new list
        // please see countries.php example file for a list of allowed countries/zones for list company
        $response = $endpoint->create([
            // required
            'general' => [
                'name' => $this->input->post('mailwizz_list_name', false), // required
                'description' => $this->input->post('mailwizz_list_description', false), // required
            ],
            // required
            'defaults' => [
                'from_name' => $this->input->post('mailwizz_list_fromname', false), // required
                'from_email' => $this->input->post('mailwizz_marketing_from_email', false), // required
                'reply_to' => $this->input->post('mailwizz_marketing_replyto', false), // required
                'subject' => $this->input->post('mailwizz_marketing_listsubject', false),
            ],

        ]);

        if (isset($response)) {

            set_alert('success', _l('mailwizz_marketing_list_added', _l('mailwizz_marketing')));
            redirect(admin_url('mailwizz_marketing/campaign/create_list'));

        }

    }

    public function delete_campaign()
    {
        $id = $this->uri->segment(5);

        $data['settings'] = $this->Mailwizzsettings_model->get_api_settings();

        // ApiAuth->newAuth() will accept an array of Auth settings
        $settings = [
            'public_key' => $data['settings'][0]['public_key'], // Create a new user
            'secret_key' => $data['settings'][0]['secret_key'], // Make it a secure password
        ];
        $apiUrl = $data['settings'][0]['mailwizz_marketing_base_url'];

        // configuration object
        $config = new MailWizzApi_Config(array(
            'apiUrl' => $apiUrl,
            'publicKey' => $settings['public_key'],
            'privateKey' => $settings['secret_key'],

            // components
            'components' => array(
                'cache' => array(
                    'class' => 'MailWizzApi_Cache_File',
                    'filesPath' => dirname(__FILE__) . '/../MailWizzApi/Cache/data/cache', // make sure it is writable by webserver
                ),
            ),
        ));

        // now inject the configuration and we are ready to make api calls
        MailWizzApi_Base::setConfig($config);

        // CREATE THE ENDPOINT
        $endpoint = new MailWizzApi_Endpoint_Campaigns();
        // Delete CAMPAIGN
        $response = $endpoint->delete($id);

        $this->campaign_model->delete_campaign($id);

        if (isset($response)) {

            set_alert('success', _l('mailwizz_marketing_campaign_deleted', _l('mailwizz_marketing')));
            redirect(admin_url('mailwizz_marketing/campaign/campaign_list'));

        }

    }

}
