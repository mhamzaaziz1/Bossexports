<?php

defined('BASEPATH') or exit('No direct script access allowed');
set_time_limit(0);

class Xero extends AdminController
{
    public function __construct()
    {
        parent::__construct();
       
        $this->load->library('XeroAPIWrapper');
    }


   public function xeroAuthorization(){
       $xeroWrapper = new XeroAPIWrapper();
       session_start();
       $provider = $xeroWrapper->getProviderDetails();

       // Scope defines the data your app has permission to access.
       // Learn more about scopes at https://developer.xero.com/documentation/oauth2/scopes
       $options = [
//              'scope' => ['openid email profile offline_access accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments']
           'scope' => ['accounting.settings accounting.transactions']
       ];

       // This returns the authorizeUrl with necessary parameters applied (e.g. state).
       $authorizationUrl = $provider->getAuthorizationUrl($options);

       // Save the state generated for you and store it to the session.
       // For security, on callback we compare the saved state with the one returned to ensure they match.
       $_SESSION['oauth2state'] = $provider->getState();

       // Redirect the user to the authorization URL.
       header('Location: ' . $authorizationUrl);
       exit();
   }

    public function xerocallback(){

        $xeroWrapper = new XeroAPIWrapper();
        $provider = $xeroWrapper->getProviderDetails();

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
            echo "Something went wrong, no authorization code found";
            exit("Something went wrong, no authorization code found");

            // Check given state against previously stored one to mitigate CSRF attack
        }

        else {
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                $identityApi = $xeroWrapper->getIdentityApi($accessToken);

                $result = $identityApi->getConnections();
                $xeroWrapper->setToken($accessToken, $result[0]->getTenantId());
                $location = admin_url('xero/xeroAuthorizedResource');
                header('Location: ' . $location);
                exit();

            } catch (Exception $e) {
                echo '<pre>'; print_r($e);
                echo "Callback failed";
                exit();
            }
        }
    }

    public function xeroAuthorizedResource(){
        // Use this class to deserialize error caught
//        use XeroAPI\XeroPHP\AccountingObjectSerializer;

        // Storage Classe uses sessions for storing token > extend to your DB of choice
        $xeroWrapper = new XeroAPIWrapper();
        $xeroTenantId = $xeroWrapper->getTenantId();
        try{
            if ($xeroWrapper->storageInstance->getHasExpired()) {
                $provider = $xeroWrapper->getProviderDetails();

                if(isset($xeroWrapper->storageInstance)){
                    $newAccessToken = $provider->getAccessToken('refresh_token', [
                        'refresh_token' => $xeroWrapper->storageInstance->getRefreshToken()
                    ]);
                    // Save my token, expiration and refresh token
                    $xeroWrapper->setToken($newAccessToken, $xeroTenantId);
                }else{
                    $location = admin_url('xero/xeroAuthorization');
                    header('Location: ' . $location);
                }

            }
        }catch(Exception $e){
            $location = admin_url('xero/xeroAuthorization');
            header('Location: ' . $location);
        }
        set_alert('success', _l('xero_configuration_saved_and_session_started', _l('xero')));
        redirect(admin_url('xero'));

    }


    public function index()
    {
        $data['title']= _l('xero_configuration');
        $configuration = $this->db->get(db_prefix() . 'xero')->row();
        if(isset($configuration)){
            $data['client_id'] = $configuration->client_id;
            $data['client_secret'] = $configuration->client_secret;
            $data['redirect_uri'] = $configuration->redirect_uri;
        }
        $this->load->view('xero_configuration', $data);
       
    }

    public function save_configuration(){
        $client_id = html_purify($this->input->post('client_id', false));
        $client_secret   = html_purify($this->input->post('client_secret', false));
        $redirect_uri   = html_purify($this->input->post('redirect_uri', false));

        $data = array(
            'client_id'=>$client_id,
            'client_secret'=>$client_secret,
            'redirect_uri'=>$redirect_uri,
        );

        $configuration = $this->db->get(db_prefix() . 'xero')->row();
        if(isset($configuration)){
            $this->db->update( db_prefix().'xero',$data);
        }else{
            $this->db->insert( db_prefix().'xero',$data);
        }

        redirect(admin_url('xero/xeroAuthorization'));
    }
	
}
