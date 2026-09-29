<?php

use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Configuration;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/storage.php';

class XeroAPIWrapper {


    private $ci;
    public function __construct() {

        $this->baseUrl = 'https://api.xero.com/api.xro/2.0/';
        $this->accessToken = '';
        $this->invoice_id = '';

        $this->storageInstance = new StorageClass();
        $this->ci = &get_instance();
    }

    public function getProviderDetails(){
        $configuration = $this->ci->db->get(db_prefix() . 'xero')->row();
        if(isset($configuration)){
            $client_id = $configuration->client_id;
            $client_secret = $configuration->client_secret;
            $redirect_uri = $configuration->redirect_uri;
        }
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $client_id,
            'clientSecret'            => $client_secret,
            'redirectUri'             => $redirect_uri,
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
        ]);
        return $provider;
    }

    public function setToken($accessToken, $tenantId){
        if(!isset($this->accessToken)){
            $this->accessToken = $accessToken;
        }
        $this->storageInstance ->setToken(
            $accessToken->getToken(),
            $accessToken->getExpires(),
            $tenantId,
            $accessToken->getRefreshToken() != "" ? $accessToken->getRefreshToken() : $accessToken->getToken(),
            $accessToken->getValues()["id_token"]
        );
    }

    public function getConfigForCalls($use_session, $access_token = ''){
        if(!isset($this->accessToken)){
            $this->accessToken = $access_token;
        }
        if($use_session){
            $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$this->storageInstance->getSession()['token'] );
        }else{
            $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$access_token->getToken() );
        }
        return $config;
    }

    public function getIdentityApi($access_token){
        $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
            new GuzzleHttp\Client(),
            $this->getConfigForCalls(false, $access_token)
        );
        return $identityApi;
    }

    public function getAccountingApi(){
        $accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
            new GuzzleHttp\Client(),
            $this->getConfigForCalls(true)
        );
        return $accountingApi;
    }

    public function getAssetApi(){
        $assetApi = new XeroAPI\XeroPHP\Api\AssetApi(
            new GuzzleHttp\Client(),
            $this->getConfigForCalls(true)
        );
        return $assetApi;
    }

    public function getProjectApi(){
        $projectApi = new XeroAPI\XeroPHP\Api\ProjectApi(
            new GuzzleHttp\Client(),
            $this->getConfigForCalls(true)
        );
        return $projectApi;
    }

    public function getTenantId(){
        $xeroTenantId = (string)$this->storageInstance->getSession()['tenant_id'];
        return $xeroTenantId;
    }

    public function createInvoice($id){
        if (!class_exists('clients_model')) {
            $this->ci->load->model('clients_model');
        }
        if (!class_exists('invoices_model')) {
            $this->ci->load->model('invoices_model');
        }
        $invoice = $this->ci->invoices_model->get($id);
        $invoice_array = json_decode(json_encode($invoice), true);

        $insert_id = $invoice->id;
        $summarize_errors = true; // bool | If false return 200 OK and mix of successfully created obejcts and any with validation errors
        $unitdp = 4; // int | e.g. unitdp=4 – (Unit Decimal Places) You can opt in to use four decimal places for unit amounts
        $data = array();

        $contact = $this->ci->clients_model->get_contacts($invoice->clientid);
        $data['Type'] = 'ACCREC';
        $data['clientid'] = $invoice->clientid;
        $data['Date'] = $invoice->date;
        $data['DueDate'] = $invoice->duedate;
        foreach ($invoice->items as $key => $value){
            $data[$key]['Description'] = $value['description'];
            $data[$key]['Quantity'] = $value['qty'];
            $data[$key]['UnitAmount'] = $value['rate'];
        }
        if(isset($contact)){
            $data['FirstName'] = $contact[0]['firstname'];
            $data['LastName'] = $contact[0]['lastname'];
            $data['Name'] = $contact[0]['firstname']." ".$contact[0]['lastname'];
            $data['EmailAddress'] = $contact[0]['email'];
            $data['ContactStatus'] = $contact[0]['active'] == 1 ? 'ACTIVE' : 'INACTIVE';
            $data['PhoneNumber'] = $contact[0]['phonenumber'];
            $contact_id = $contact[0]['id'];
        }
     $invoice_xml2 = "<Invoice>
                              <Type>".$data['Type']."</Type>
                              <Contact>
                                  <Name>".$data['Name']."</Name>
                                  <ContactStatus>".$data['ContactStatus']."</ContactStatus>
                                  <EmailAddress>".$data['EmailAddress']."</EmailAddress>
                                  <FirstName>".$data['FirstName']."</FirstName>
                                  <LastName>".$data['LastName']."</LastName>
                                  <Phone>
                                      <PhoneType>DEFAULT</PhoneType>
                                      <PhoneNumber>".$data['PhoneNumber']."</PhoneNumber>
                                  </Phone>
                              </Contact>
                              <CurrencyCode>".$invoice_array['currency_name']."</CurrencyCode>
                              <Status>AUTHORISED</Status>
                              <LineAmountTypes>Inclusive</LineAmountTypes>
                              <SubTotal>".$invoice_array['subtotal']."</SubTotal>
                              <TotalTax>".$invoice_array['total_tax']."</TotalTax>
                              <Total>".$invoice_array['total']."</Total>
                              <Date>".$data['Date']."</Date>
                              <DueDate>".$data['DueDate']."</DueDate>
                              <LineItems>";
        foreach ($invoice->items as $key => $value){
            $invoice_xml2 .= "<LineItem>
                                  <Description>".$data[$key]['Description']."</Description>
                                  <Quantity>".$data[$key]['Quantity']."</Quantity>
                                  <UnitAmount>".$data[$key]['UnitAmount']."</UnitAmount>
                                  <TaxType>OUTPUT</TaxType>
                                  <AccountCode>200</AccountCode>
                                </LineItem>";
        }

        $invoice_xml2 .= "</LineItems>
                            </Invoice>";
        try {
            $accountingApi = $this->getAccountingApi();
            $result = $accountingApi->createInvoices($this->getTenantId(), $invoice_xml2, $summarize_errors, $unitdp);

            if(isset($result)){
                $contact_id = $result->getInvoices()[0]->getContact()->getContactId();
                $invoice_id = $result->getInvoices()[0]->getInvoiceId();
                $data = array();
                $data['xero_contactid'] = $contact_id;
                $this->ci->clients_model->update_contact($data, $contact_id);
                return array('perfex_invoice_id' => $insert_id, 'xero_invoice_id' => $invoice_id, 'data' => $invoice_array);
            }

        } catch (Exception $e) {
            $delete_invoice = $this->ci->invoices_model->delete($id);
            if(($e->getResponseHeaders()['WWW-Authenticate'][0] == "Bearer error=invalid_token") || (trim($e->getResponseHeaders()['WWW-Authenticate'][0]) == "Bearer")){
                $location = admin_url('xero/xeroAuthorization');
                header('Location: ' . $location);
            }else{
                echo 'Exception when calling AccountingApi->createInvoices: ', $e->getMessage(), PHP_EOL;
            }
            exit;
        }

    }

    public function updateInvoiceWithId($id, $xero_invoice_id, $data1) {
        if (!class_exists('invoices_model')) {
            $this->ci->load->model('invoices_model');
        }
        $data2 = array();

        $data2['xero_invoiceid'] = $xero_invoice_id;
        $data2['number'] = $data1['number'];
        $data2['allowed_payment_modes'] = unserialize($data1['allowed_payment_modes']);
        $invoice_update = $this->ci->invoices_model->update($data2, $id);
        return true;
    }

    public function createXeroAccountForTransfer($data){
        if (!class_exists('payment_modes_model')) {
            $this->ci->load->model('payment_modes_model');
        }
        $data1 = $this->ci->payment_modes_model->get($data['paymentmode']);
        if($data1->xero_accountid == ""){

            $code = rand(100, 900);
            $name = $data1->name . "_" . $code;
            $account_creation_xml = "<Account>
                                        <Code>" . $code . "</Code>
                                        <Name>" . $name . "</Name>
                                        <Type>Revenue</Type>   
                                        <EnablePaymentsToAccount>true</EnablePaymentsToAccount>
                                        <ShowInExpenseClaims>true</ShowInExpenseClaims> 
                                    </Account>";
            try {
                $accountingApi = $this->getAccountingApi();
                $result = $accountingApi->createAccount($this->getTenantId(), $account_creation_xml);
                $account_id = $result->getAccounts()[0]->getAccountId();
            } catch (Exception $e) {
                if(($e->getResponseHeaders()['WWW-Authenticate'][0] == "Bearer error=invalid_token") || (trim($e->getResponseHeaders()['WWW-Authenticate'][0]) == "Bearer")){
                    $location = admin_url('xero/xeroAuthorization');
                    header('Location: ' . $location);
                }else{
                    echo 'Exception when calling AccountingApi->createAccount: ', $e->getMessage(), PHP_EOL;
                }
                exit;
            }
        }else{
            $account_id = $data1->xero_accountid;
        }
        $data2 = $data;
        $data2['name'] = $data1->name;
        $data2['active'] = 1;
        $data2['paymentmodeid'] = $data['paymentmode'];
        $data2['xero_accountid'] = $account_id;

        $this->ci->payment_modes_model->edit($data2);

        if (!class_exists('invoices_model')) {
            $this->ci->load->model('invoices_model');
        }
        $invoice_update_record = $this->ci->invoices_model->get($data['invoiceid']);
        $xero_invoice_id = $invoice_update_record->xero_invoiceid;


        $payment_creation_xml = "<Payments>
                                  <Payment> 
                                    <Invoice>
                                      <InvoiceID>".$xero_invoice_id."</InvoiceID>
                                    </Invoice>
                                    <Account>
                                      <AccountID>".$account_id."</AccountID>
                                    </Account>
                                    <Amount>".$data['amount']."</Amount>
                                  </Payment>
                                </Payments>";

        $summarize_errors = true; // bool | If false return 200 OK and mix of successfully created obejcts and any with validation errors


        try {
            $accountingApi = $this->getAccountingApi();
            $result = $accountingApi->createPayments($this->getTenantId(), $payment_creation_xml, $summarize_errors);

        } catch (Exception $e) {
            if(($e->getResponseHeaders()['WWW-Authenticate'][0] == "Bearer error=invalid_token") || (trim($e->getResponseHeaders()['WWW-Authenticate'][0]) == "Bearer")){
                $location = admin_url('xero/xeroAuthorization');
                header('Location: ' . $location);
            }else{
                echo "<pre>"; print_r($e); exit;
                echo 'Exception when calling AccountingApi->createPayments: ', $e->getMessage(), PHP_EOL;
            }
            exit;
        }

        return true;
    }

}