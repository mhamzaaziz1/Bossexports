<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gocardless_gateway extends App_gateway
{
    protected $sandbox_url = 'https://api-sandbox.gocardless.com/';
    protected $production_url = 'https://api.gocardless.com/';

    protected $support_currency = ["AUD", "CAD", "DKK", "EUR", "GBP", "NZD", "SEK", "USD" ];

    protected $client;

    public function __construct()
    {       
        /**
        * Call App_gateway __construct function
        */
        parent::__construct();
        
 
        $this->ci = & get_instance();
        $this->ci->load->database();

        /**
         * Gateway unique id - REQUIRED
         */
        $this->setId('gocardless');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('GoCardless');

        /**
         * Add gateway settings
         */
        $this->setSettings([
            [
                'name'              => 'api_top_secret_access_token',
                'label'             => 'Secret Access Token',
                'type'              => 'input'
            ],
            [
                'name'              => 'api_version',
                'label'             => 'API version',
                'type'              => 'input',
                'default_value'     => '2015-07-06'
            ],
            [
                'name'              => 'test_mode_enabled',
                'type'              => 'yes_no',
                'default_value'     => 0,
                'label'             => 'settings_paymentmethod_testing_mode',
            ],
            [
                'name'              => 'currencies',
                'label'             => 'settings_paymentmethod_currencies',
                'default_value'     => 'USD,CAD'
            ],
        ]);

        $this->setClient();

    }

    public function __call($method, $args) {
        return gocardlessPaymentSuccess($method, $args);
    }

    private function setClient(){
        
        if ($this->getSetting('test_mode_enabled')) {
            $this->client = new \GoCardlessPro\Client(array(
                'access_token' => $this->getSetting('api_top_secret_access_token'),
                'environment'  => \GoCardlessPro\Environment::SANDBOX
            ));
        } else {
            $this->client = new \GoCardlessPro\Client(array(
                'access_token' => $this->getSetting('api_top_secret_access_token'),
                'environment'  => \GoCardlessPro\Environment::LIVE
            ));
        }
    }
    
    public function process_payment( $data )
    {
        // print("<pre>");
        // print_r($data);
        // die();

        $id = substr(uri_string(), strpos(uri_string(), "/")+1,1);
        $num = substr(uri_string(), strrpos(uri_string(), "/")+1);
        $url = $_SERVER['HTTP_REFERER'];
        
        


        $_SESSION['url'] = $url;
        $_SESSION['invoice_id'] = $id;
        
        $user =  $this->ci->db->select('tblstaff.*');
        $this->ci->db->from('tblstaff');
        $this->ci->db->where('staffid',$data['invoice']->client->userid);
        $user = $this->ci->db->get()->result()[0];

        if (in_array($data['invoice']->currency_name, $this->support_currency)) {

                $redirectFlow = $this->client->redirectFlows()->create([
                    "params" => [
                        // A description of what the Direct Debit is for to be shown to the customer
                        "description" => "GoCardless Invoice Payment",
                        // A unique token for the customer's session
                        "session_token" => "dummy_session_token",
                        // The URL for a success page you host, to send the customer to when they finish
                        "success_redirect_url" => base_url()."Payments/gocardlessPayment",
                        // "success_redirect_url"=> $this->gocardlessPaymentSuccess(),

                        // Optionally, prefill customer details on the payment page
                        "prefilled_customer" => [
                            "given_name" => "",
                            "family_name" => "",
                            "email" => "",
                            "address_line1" => "",
                            "city" => "",
                            "postal_code" => ""
                        ]
                    ]
                ]);

                $_SESSION['data'] = $data;
                redirect($redirectFlow->redirect_url);

        }
    }

    public function complete_client( $redirect_flow_id ){
        $redirectFlow = $this->client->redirectFlows()->complete(
            $redirect_flow_id, 
            ["params" =>
                ["session_token" => "dummy_session_token"]
            ]
        );

        $mandatID = $redirectFlow->links->mandate;
        $data = $_SESSION['data']; 
        $this->gocardlessPayment( $data, $mandatID);
    }

    public function gocardlessPayment( $data , $mandatID ){

        $this->client->payments()->create([
          "params" => [
                "amount" => (int)($data['amount'] * 100),
                "currency" => 'GBP',
                "description" => $data['invoice']->clientnote,
                "metadata" => [
                    "invoice_number" => 'Invoice-ID-' . date("YmdHis") . "-". $data['invoice']->id
                ],
                "links" => [
                    "mandate" => $mandatID
                ],
            ]
        ]);
        
        $_SESSION['payment'] = $this->client->payments()->list();

    }
    public function get_action_url() {

    }

    public function finish_payment($post_data) {

    }
}