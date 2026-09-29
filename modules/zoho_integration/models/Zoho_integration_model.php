<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a zoho integration model.
 */
class Zoho_integration_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * update general setting
     *
     * @param      array   $data   The data
     *
     * @return     boolean
     */
    public function update_setting($data)
    {
        $affectedRows = 0;
        if (!isset($data['settings']['acc_integration_zoho_active'])) {
            $data['settings']['acc_integration_zoho_active'] = 0;
        }

        if (!isset($data['settings']['acc_integration_zoho_sync_from_system'])) {
            $data['settings']['acc_integration_zoho_sync_from_system'] = 0;
        }

        if (!isset($data['settings']['acc_integration_zoho_sync_to_system'])) {
            $data['settings']['acc_integration_zoho_sync_to_system'] = 0;
        }

        if (isset($data['settings']['acc_integration_zoho_client_id'])) {
            $data['settings']['acc_integration_zoho_client_id'] = $this->encryption->encrypt($data['settings']['acc_integration_zoho_client_id']);
        }

        if (isset($data['settings']['acc_integration_zoho_client_secret'])) {
            $data['settings']['acc_integration_zoho_client_secret'] = $this->encryption->encrypt($data['settings']['acc_integration_zoho_client_secret']);
        }


        if (isset($data['acc_integration_zoho_sync_from_system_organizations'])) {
             $data['settings']['acc_integration_zoho_sync_from_system_organizations'] = implode(',', $data['acc_integration_zoho_sync_from_system_organizations']);
        } else {
           $data['settings']['acc_integration_zoho_sync_from_system_organizations'] = '';
        }

        if (isset($data['acc_integration_zoho_sync_to_system_organizations'])) {
            $data['settings']['acc_integration_zoho_sync_to_system_organizations']  = implode(',', $data['acc_integration_zoho_sync_to_system_organizations']);
        }else{
           $data['settings']['acc_integration_zoho_sync_to_system_organizations'] = '';
        }

        foreach ($data['settings'] as $key => $value) {
            if (update_option($key, $value)) {
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    public function init_zoho_config(){
        $client_id = get_option('acc_integration_zoho_client_id');
        $client_secret = get_option('acc_integration_zoho_client_secret');
        $refreshTokenKey = get_option('acc_integration_zoho_refresh_token');
        $access_token_expires = get_option('acc_integration_zoho_access_token_expires');

        // Prep Data Services
        $params = [
            'refresh_token' => $refreshTokenKey,
            'client_id' => $this->encryption->decrypt($client_id), 
            'client_secret' => $this->encryption->decrypt($client_secret), 
            'grant_type' => "refresh_token",
            'prompt' => 'consent'
        ];

        $http_header = array(
                 'Accept' => 'application/json',
                 'Content-Type' => 'application/x-www-form-urlencoded'
            );

        if($access_token_expires <= time()){

            $result = $this->executeRequest('https://accounts.zoho.com/oauth/v2/token', $params, $http_header,'POST');
            if(isset($result['access_token'])){
                update_option('acc_integration_zoho_access_token', $result['access_token']);
                update_option('acc_integration_zoho_api_domain', $result['api_domain']);
                update_option('acc_integration_zoho_access_token_expires', time() + $result['expires_in']);
            }
        }
    }

    public function init_expense_category($name)
    {
        $this->db->where('name', $name);

        $expenses_categorie = $this->db->get(db_prefix() . 'expenses_categories')->row();

        if($expenses_categorie){
            return $expenses_categorie->id;
        }
        
        $this->load->model('expenses_model');
        $id = $this->expenses_model->add_category(['name' => $name, 'description' => '']);

        return $id;
    }

    public function executeRequest($url, $parameters = array(), $http_header = '', $http_method = '')
    {

      $curl_options = array();

      switch($http_method){
            case 'GET':
              $curl_options[CURLOPT_HTTPGET] = 'true';
              if (is_array($parameters) && count($parameters) > 0) {
                $url .= '?' . http_build_query($parameters);
              } elseif ($parameters) {
                $url .= '?' . $parameters;
              }
              break;
            case 'POST':
              $curl_options[CURLOPT_POST] = '1';
              if(is_array($parameters) && count($parameters) > 0){
                $body = http_build_query($parameters);
                $curl_options[CURLOPT_POSTFIELDS] = $body;
              }
              break;
            default:
              break;
      }
      /**
      * An array of HTTP header fields to set, in the format array('Content-type: text/plain', 'Content-length: 100')
      */
      if(is_array($http_header)){
            $header = array();
            foreach($http_header as $key => $value) {
                $header[] = "$key: $value";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
      }

      $curl_options[CURLOPT_URL] = $url;
      $ch = curl_init();

      //debug_backtrace
      //curl_setopt($ch, CURLOPT_VERBOSE, true);
      //$verbose = fopen('php://temp', 'w+');
      //curl_setopt($ch, CURLOPT_STDERR, $verbose);


      curl_setopt_array($ch, $curl_options);

      //curl_setopt($ch, CURLINFO_HEADER_OUT, true);
      //Don't display, save it on result
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //Execute the Curl Request
      $result = curl_exec($ch);

      $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );

      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


      $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
       if ($curl_error = curl_error($ch)) {
           throw new Exception($curl_error);
       } else {
           $json_decode = json_decode($result, true);
       }
       curl_close($ch);

       return $json_decode;
    }

    public function delete_integration_error_log($organization_id, $rel_id, $rel_type, $software){
        $this->db->where('organization_id', $organization_id);
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->delete(db_prefix().'acc_integration_error_logs');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function check_connect_id($organization_id, $connect_id, $rel_type, $software = 'quickbook'){
        $this->db->where('organization_id', $organization_id);
        $this->db->where('connect_id', $connect_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->where('software', $software);
        $log = $this->db->get(db_prefix().'acc_integration_logs')->row();

        if($log){
            return $log->rel_id;
        }

        return 0;
    }


    public function callAPI($url, $params, $header, $method = 'POST'){
            $data_string = json_encode($params);

            $curl = curl_init($url);
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

            if($method == 'POST' || $method == 'PUT'){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            
            $result = curl_exec($curl);

            $result = json_decode($result, true);

            return $result;
    }

    /**
     * [get_invoice_item_tax description]
     * @param  [type] $item
     * @return [type]      
     */
    public function get_invoice_item_tax($item, $invoice){
        $data_return = [];
        $data_return['tax_id'] = [];
        $data_return['tax_amount'] = [];
        $data_return['tax_rate'] = [];
        $item_total = $item['rate'] * $item['qty'];
        $this->db->where('itemid', $item['id']);
        $item_tax = $this->db->get(db_prefix().'item_tax')->result_array();

        foreach($item_tax as $tax){
            $this->db->where('taxrate', $tax['taxrate']);
            $this->db->where('name', $tax['taxname']);
            $_tax = $this->db->get(db_prefix().'taxes')->row();
            if($_tax){
                $data_return['tax_rate'][] = $tax['taxrate'];

                $data_return['tax_id'][] = $_tax->id;
                if($invoice['discount_type'] == 'before_tax'){
                    $total_tax = $item_total * $tax['taxrate'] / 100;
                    $t = ($invoice['discount_total'] / $invoice['subtotal']) * 100;

                    $data_return['tax_amount'][] = ($total_tax - $total_tax*$t/100);
                }else{
                    $data_return['tax_amount'][] = $item_total * $tax['taxrate'] / 100;
                }

            }
        }

        return $data_return;
    }

    public function create_zoho_customer($organization_id, $customer_id = ''){
        $this->db->select('*, '.db_prefix() . 'clients.userid as userid');
        if($customer_id != ''){
            $this->db->where(db_prefix().'clients.userid', $customer_id);
        }
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid=' . db_prefix() . 'clients.userid AND '.db_prefix() . 'contacts.is_primary = "1"', 'left');
        $this->db->join(db_prefix() . 'acc_integration_logs', db_prefix() . 'acc_integration_logs.rel_id=' . db_prefix() . 'clients.userid AND '.db_prefix() . 'acc_integration_logs.rel_type = "customer" AND software = "zoho" AND ' . db_prefix() . 'acc_integration_logs.organization_id = "'.$organization_id.'"', 'left');

        $clients = $this->db->get(db_prefix().'clients')->result_array();
        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $customer_data = [];
        $customer_name = [];
        $continue = true;
        $page = 1;

        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/contacts?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['contacts'])) {
                foreach ($entities['contacts'] as $customer) {
                    if($customer['contact_type'] != 'customer'){
                        continue;
                    }
                    $customer_data[$customer['contact_id']] = $customer;
                    $customer_name[$customer['customer_name']] = $customer['contact_id'];
                }
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }

        foreach ($clients as $client) {
            $customerObj = [
             "contact_name"=>  $client['company'],
             "company_name"=>  $client['company'],
             "contact_type"=>  "customer",
             "website"=>  $client['website'],

              "billing_address" => [
                 "address"=>  $client['billing_street'],
                 "city"=>  $client['billing_city'],
                 "country"=> $client['billing_country'],
                 "state"=>  $client['billing_state'],
                 "zip"=>  $client['billing_zip']
             ],
             "shipping_address" => [
                 "address"=>  $client['shipping_street'],
                 "city"=>  $client['shipping_city'],
                 "country"=> $client['shipping_country'],
                 "state"=>  $client['shipping_state'],
                 "zip"=>  $client['shipping_zip']
             ],

             "contact_persons" => [
                [
                "first_name" => $client['firstname'],
                "last_name" => $client['lastname'],
                "email" => $client['email'],
                "phone" => $client['phonenumber'],
                "is_primary_contact" => true
                ]
             ]
         ];

            if(isset($customer_name[$client['company']]) || ($client['connect_id'] != '' && isset($customer_data[$client['connect_id']]))){
                if(isset($customer_name[$client['company']])){
                    $connect_id = $customer_name[$client['company']];
                }else{
                    $connect_id = $client['connect_id'];
                }

                $url = $api_domain.'/books/v3/contacts/'.$connect_id.'?organization_id='.$organization_id;
                $result = $this->callAPI($url, $customerObj, $header, 'PUT');
            }else{
                $url = $api_domain.'/books/v3/contacts?organization_id='.$organization_id;
                $result = $this->callAPI($url, $customerObj, $header, 'POST');
            }

            
            $this->delete_integration_error_log($organization_id, $client['userid'], 'customer', 'zoho');

            if ($result['code'] != 0) {

                $this->db->insert(db_prefix().'acc_integration_error_logs', [
                    'rel_id' => $client['userid'],
                    'rel_type' => 'customer',
                    'software' => 'zoho',
                    'organization_id' => $organization_id,
                    'error_detail' => $result['message'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ]);

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'rel_id' => $client['userid'],
                    'rel_type' => 'customer',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'organization_id' => $organization_id,
                    'status' => 0,
                    'connect_id' => '',
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);
                
                if($customer_id != ''){
                    return $result['message'];
                }
            }else{
                if($client['connect_id'] == ''){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'rel_id' => $client['userid'],
                        'rel_type' => 'customer',
                        'organization_id' => $organization_id,
                        'software' => 'zoho',
                        'connect_id' => $result['contact']['contact_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }else{
                    $this->db->where('organization_id', $organization_id);
                    $this->db->where('rel_id', $client['userid']);
                    $this->db->where('rel_type', 'customer');
                    $this->db->where('software', 'zoho');
                    $this->db->update(db_prefix().'acc_integration_logs', [
                        'connect_id' => $result['contact']['contact_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'rel_id' => $client['userid'],
                    'rel_type' => 'customer',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'organization_id' => $organization_id,
                    'status' => 1,
                    'connect_id' => $result['contact']['contact_id'],
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);
                
                if($customer_id != ''){
                    return true;
                }
            }
        }
    }

    public function create_zoho_invoice($organization_id, $invoice_id = ''){
        $this->db->select('*, ' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'invoices.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        if($invoice_id != ''){
            $this->db->where(db_prefix().'invoices.id', $invoice_id);
        }
        $this->db->join(db_prefix() . 'currencies', '' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency', 'left');
        $this->db->join(db_prefix() . 'acc_integration_logs', db_prefix() . 'acc_integration_logs.rel_id=' . db_prefix() . 'invoices.id AND '.db_prefix() . 'acc_integration_logs.rel_type = "invoice" AND software = "zoho" AND ' . db_prefix() . 'acc_integration_logs.organization_id = "'.$organization_id.'"', 'left');

        $invoices = $this->db->get(db_prefix().'invoices')->result_array();

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $invoice_data = [];
        $continue = true;
        $page = 1;

        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/invoices?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['invoices'])) {
                foreach ($entities['invoices'] as $invoice) {
                    $invoice_data[$invoice['invoice_id']] = $invoice;
                }
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }

        $item_data = [];
        $continue = true;
        $page = 1;

        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/items?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['items'])) {
                foreach ($entities['items'] as $item) {
                    $item_data[$item['sku']] = $item;
                }
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }

        foreach ($invoices as $invoice) {
            $customer_connect_id = $this->get_connect_id($organization_id, $invoice['clientid'], 'customer', 'zoho');

            $items  = get_items_by_type('invoice', $invoice['id']);
            $item_array = [];
            $tax_amount = 0;

            $insert_item = [];
            foreach ($items as $item) {
                $item_code = get_item_code_by_name($item['description']);
                if(!isset($item_data[$item_code])){
                    $item['code'] = $item_code;
                    $insert_item[] = $item;
                    $item_data[$item_code] = 1;
                }

                $data_tax = $this->get_invoice_item_tax($item, $invoice);
                $tax_connect_id = '';
                foreach ($data_tax['tax_id'] as $k => $tax_id) {
                    if($tax_connect_id == ''){
                        $tax_connect_id = $this->get_connect_id($organization_id, $tax_id, 'tax', 'zoho');
                        if($tax_connect_id == ''){
                            $tax_amount += $data_tax['tax_amount'][$k];
                        }
                    }else{
                        $tax_amount += $data_tax['tax_amount'][$k];
                    }

                    

                }

                $item_array[] = [
                    "item_id" => '',
                    "quantity" => (float)$item['qty'],
                    "rate" => (float)$item['rate'],
                    'description' => $item['description'],
                    'item_total' => (float)($item['rate'] * $item['qty']),
                    'tax_id' => $tax_connect_id,
                ];
            }

            if(count($insert_item) > 0){
                $this->create_zoho_item($organization_id, $insert_item);
            }
            
            $is_discount_before_tax = true;
            if($invoice['discount_type'] == "after_tax"){
                $is_discount_before_tax = false;
            }

            $invoiceObj = [
                "customer_id"=> $customer_connect_id,
                "reference_number" => format_invoice_number($invoice['id']),
                "line_items" => $item_array,
                "date" => $invoice['date'],
                "due_date" => $invoice['duedate'],
                "sub_total" => (float)$invoice['subtotal'],
                "total" => (float)$invoice['total'],
                "notes" => $invoice['clientnote'],
                "terms" => $invoice['terms'],
                "discount" => (float)$invoice['discount_total'],
                "discount_type" => "entity_level",
                "is_discount_before_tax" => $is_discount_before_tax
            ];

            $invoiceObj['adjustment'] = 0;
            if($tax_amount > 0){
                $invoiceObj["adjustment"] = (float)$tax_amount;
                $invoiceObj["adjustment_description"] = 'Tax remaining';
            }

            if($invoice['adjustment'] != 0){
                $invoiceObj['adjustment'] += (float)$invoice['adjustment'];

                if(isset($invoiceObj["adjustment_description"])){
                    $invoiceObj["adjustment_description"] .= ' + Adjustment';
                }else{
                    $invoiceObj["adjustment_description"] = 'Adjustment';
                }
            }

            if($invoice['connect_id'] != '' && isset($invoice_data[$invoice['connect_id']])){
                $invoiceObj['reason'] = "Automatic synchronization";

                $url = $api_domain.'/books/v3/invoices/'.$invoice['connect_id'].'?organization_id='.$organization_id;
                $result = $this->callAPI($url, $invoiceObj, $header, 'PUT');
            }else{
                $url = $api_domain.'/books/v3/invoices?organization_id='.$organization_id;
                $result = $this->callAPI($url, $invoiceObj, $header, 'POST');
            }

            $this->delete_integration_error_log($organization_id, $invoice['id'], 'invoice', 'zoho');
            if ($result['code'] != 0) {

                $this->db->insert(db_prefix().'acc_integration_error_logs', [
                    'rel_id' => $invoice['id'],
                    'rel_type' => 'invoice',
                    'software' => 'zoho',
                    'organization_id' => $organization_id,
                    'error_detail' => $result['message'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ]);

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'rel_id' => $invoice['id'],
                    'rel_type' => 'invoice',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'status' => 0,
                    'organization_id' => $organization_id,
                    'connect_id' => '',
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($invoice_id != ''){
                    return $result['message'];
                }
            }else{
                if($invoice['connect_id'] == ''){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'rel_id' => $invoice['id'],
                        'rel_type' => 'invoice',
                        'software' => 'zoho',
                        'organization_id' => $organization_id,
                        'connect_id' => $result['invoice']['invoice_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }else{
                    $this->db->where('organization_id', $organization_id);
                    $this->db->where('rel_id', $invoice['id']);
                    $this->db->where('rel_type', 'invoice');
                    $this->db->where('software', 'zoho');
                    $this->db->update(db_prefix().'acc_integration_logs', [
                        'connect_id' => $result['invoice']['invoice_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'rel_id' => $invoice['id'],
                    'rel_type' => 'invoice',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'organization_id' => $organization_id,
                    'status' => 1,
                    'connect_id' => $result['invoice']['invoice_id'],
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($invoice_id != ''){
                    return true;
                }
            }
        }
    }

    public function create_zoho_taxes($organization_id){
        $this->db->select('*, ' . db_prefix() . 'taxes.id as id');
        $this->db->where('connect_id is NULL');
        $this->db->join(db_prefix() . 'acc_integration_logs', db_prefix() . 'acc_integration_logs.rel_id=' . db_prefix() . 'taxes.id AND '.db_prefix() . 'acc_integration_logs.rel_type = "tax" AND software = "zoho" AND ' . db_prefix() . 'acc_integration_logs.organization_id = "'.$organization_id.'"', 'left');

        $taxes = $this->db->get(db_prefix().'taxes')->result_array();

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        foreach ($taxes as $tax) {
            $taxObj = [
                "tax_name" => $tax['name'],
                "tax_percentage" => $tax['taxrate'],
                "tax_type" => "tax",
            ];

            $url = $api_domain.'/books/v3/settings/taxes?organization_id='.$organization_id;

            $result = $this->callAPI($url, $taxObj, $header, 'POST');

            if ($result['code'] != 0) {

                continue;   
            }else{
                $this->db->insert(db_prefix().'acc_integration_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $tax['id'],
                    'rel_type' => 'tax',
                    'software' => 'zoho',
                    'connect_id' => $result['tax']['tax_id'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function create_zoho_payment($organization_id, $payment_id = ''){

        $this->db->select('*, ' . db_prefix() . 'invoicepaymentrecords.id as id');
        if($payment_id != ''){
            $this->db->where(db_prefix().'invoicepaymentrecords.id', $payment_id);
        }
        $this->db->join(db_prefix() . 'acc_integration_logs', db_prefix() . 'acc_integration_logs.rel_id=' . db_prefix() . 'invoicepaymentrecords.id AND '.db_prefix() . 'acc_integration_logs.rel_type = "payment" AND software = "zoho" AND ' . db_prefix() . 'acc_integration_logs.organization_id = "'.$organization_id.'"', 'left');

        $payments = $this->db->get(db_prefix().'invoicepaymentrecords')->result_array();

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $payment_data = [];
        $continue = true;
        $page = 1;

        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/customerpayments?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['customerpayments'])) {
                foreach ($entities['customerpayments'] as $payment) {
                    $payment_data[$payment['payment_id']] = $payment;
                }
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }
        
        foreach ($payments as $payment) {
            $this->db->where('id', $payment['invoiceid']);
            $invoice = $this->db->get(db_prefix().'invoices')->row();

            $invoice_connect_id = $this->get_connect_id($organization_id, $payment['invoiceid'], 'invoice', 'zoho');
            $customer_connect_id = $this->get_connect_id($organization_id, $invoice->clientid, 'customer', 'zoho');

            $paymentObj = [
                "customer_id"=> $customer_connect_id,
                "payment_mode" => "cash",
                "amount" => $payment['amount'],
                "date" => $payment['date'],
                "invoices" => [
                    [
                        "invoice_id" => $invoice_connect_id,
                        "amount_applied" => $payment['amount']
                    ]
                ],
                "invoice_id" => $invoice_connect_id,
                "amount_applied" => $payment['amount'],
                "description" => $payment['note']
            ];
            if($payment['connect_id'] != '' && isset($payment_data[$payment['connect_id']])){
                $url = $api_domain.'/books/v3/customerpayments/'.$payment['connect_id'].'?organization_id='.$organization_id;
                $result = $this->callAPI($url, $paymentObj, $header, 'PUT');
            }else{
                $url = $api_domain.'/books/v3/customerpayments?organization_id='.$organization_id;
                $result = $this->callAPI($url, $paymentObj, $header, 'POST');
            }

            $this->delete_integration_error_log($organization_id, $payment['id'], 'payment', 'zoho');
            if ($result['code'] != 0) {

                $this->db->insert(db_prefix().'acc_integration_error_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $payment['id'],
                    'rel_type' => 'payment',
                    'software' => 'zoho',
                    'error_detail' => $result['message'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ]);   

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $payment['id'],
                    'rel_type' => 'payment',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'status' => 0,
                    'connect_id' => '',
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($payment_id != ''){
                    return $result['message'];
                }
            }else{
                if($payment['connect_id'] == ''){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $payment['id'],
                        'rel_type' => 'payment',
                        'software' => 'zoho',
                        'connect_id' => $result['payment']['payment_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }else{
                    $this->db->where('organization_id', $organization_id);
                    $this->db->where('rel_id', $payment['id']);
                    $this->db->where('rel_type', 'payment');
                    $this->db->where('software', 'zoho');
                    $this->db->update(db_prefix().'acc_integration_logs', [
                        'connect_id' => $result['payment']['payment_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $payment['id'],
                    'rel_type' => 'payment',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'status' => 1,
                    'connect_id' => $result['payment']['payment_id'],
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($payment_id != ''){
                    return true;
                }
            }
        }
    }

    public function get_zoho_expense_account($organization_id){
        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $url = $api_domain.'/books/v3/chartofaccounts?filter_by=AccountType.Expense&organization_id='.$organization_id;
        $result = $this->callAPI($url, [], $header, 'GET');

        if ($result['code'] != 0) {
            return 0;   
        }else{
            foreach ($result['chartofaccounts'] as $key => $value) {
                if($value['account_name'] == 'Other Expenses'){
                    return $value['account_id'];
                }
            }

            return 0;
        }
    }

    public function create_zoho_expense($organization_id, $expense_id = ''){
        $this->db->select('*, ' . db_prefix() . 'expenses.id as id');
        if($expense_id != ''){
            $this->db->where(db_prefix().'expenses.id', $expense_id);
        }
        $this->db->join(db_prefix() . 'acc_integration_logs', db_prefix() . 'acc_integration_logs.rel_id=' . db_prefix() . 'expenses.id AND '.db_prefix() . 'acc_integration_logs.rel_type = "expense" AND software = "zoho" AND ' . db_prefix() . 'acc_integration_logs.organization_id = "'.$organization_id.'"', 'left');

        $expenses = $this->db->get(db_prefix().'expenses')->result_array();

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $expense_data = [];
        $continue = true;
        $page = 1;

        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/expenses?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['expenses'])) {
                foreach ($entities['expenses'] as $expense) {
                    $expense_data[$expense['expense_id']] = $expense;
                }
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }

        $expense_account_id = $this->get_zoho_expense_account($organization_id);
        
        foreach ($expenses as $expense) {
            $customer_connect_id = '';

            if($expense['clientid'] != 0){
                $customer_connect_id = $this->get_connect_id($organization_id, $expense['clientid'], 'customer', 'zoho');
            }

            $tax_connect_id = '';

            $total_tax = 0;
            if($expense['tax'] != 0){
                $this->db->where('id', $expense['tax']);
                $tax = $this->db->get(db_prefix().'taxes')->row();
                if($tax){
                    $total_tax += ($tax->taxrate/100) * $expense['amount'];
                }
            }

            if($expense['tax2'] > 0){
                $this->db->where('id', $expense['tax2']);
                $tax = $this->db->get(db_prefix().'taxes')->row();
                if($tax){
                    $total_tax += ($tax->taxrate/100) * $expense['amount'];
                }
            }

            $expenseObj = [
                "tax_id" => $tax_connect_id,
                "account_id" => $expense_account_id,
                "date" => $expense['date'],
                "customer_id" => $customer_connect_id,
                "amount" => $expense['amount']+$total_tax,
                "description" => $expense['note'],
            ];

            if($expense['connect_id'] != '' && isset($expense_data[$expense['connect_id']])){
                $url = $api_domain.'/books/v3/expenses/'.$expense['connect_id'].'?organization_id='.$organization_id;
                $result = $this->callAPI($url, $expenseObj, $header, 'PUT');
            }else{
                $url = $api_domain.'/books/v3/expenses?organization_id='.$organization_id;
                $result = $this->callAPI($url, $expenseObj, $header, 'POST');
            }
            $this->delete_integration_error_log($expense['id'], 'expense', 'zoho');
            if ($result['code'] != 0) {

                $this->db->insert(db_prefix().'acc_integration_error_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $expense['id'],
                    'rel_type' => 'expense',
                    'software' => 'zoho',
                    'error_detail' => $result['message'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ]);

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $expense['id'],
                    'rel_type' => 'expense',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'status' => 0,
                    'connect_id' => '',
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($expense_id != ''){
                    return $result['message'];
                }
            }else{
                if($expense['connect_id'] == ''){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $expense['id'],
                        'rel_type' => 'expense',
                        'software' => 'zoho',
                        'connect_id' => $result['expense']['expense_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }else{
                    $this->db->where('organization_id', $organization_id);
                    $this->db->where('rel_id', $expense['id']);
                    $this->db->where('rel_type', 'expense');
                    $this->db->where('software', 'zoho');
                    $this->db->update(db_prefix().'acc_integration_logs', [
                        'connect_id' => $result['expense']['expense_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }

                $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                    'organization_id' => $organization_id,
                    'rel_id' => $expense['id'],
                    'rel_type' => 'expense',
                    'software' => 'zoho',
                    'type' => 'sync_up',
                    'status' => 1,
                    'connect_id' => $result['expense']['expense_id'],
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);

                if($expense_id != ''){
                    return true;
                }
            }
        }
    }

    public function get_zoho_customer($organization_id, $page = 0){
        $customer_list = $this->clients_model->get();
     
        $customer_arr = [];
        foreach ($customer_list as $customer) {
            $customer_arr[$customer['userid']] = $customer;
        }

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $list_results = [];
        $has_more_page = false;

        if ($page > 0) {
            $url = $api_domain.'/books/v3/contacts?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            if (isset($entities['contacts'])) {
                $list_results = $entities['contacts'];
            }
            if(isset($entities['page_context']['has_more_page']) && $entities['page_context']['has_more_page'] == true){
                $has_more_page = true;
            }
        } else {
            $continue = true;
            $page = 1;
            while ($continue) {
                $continue = false;
                $url = $api_domain.'/books/v3/contacts?page='.$page.'&organization_id='.$organization_id;
                $entities = $this->callAPI($url, [], $header, 'GET');
                $page++;

                if (isset($entities['contacts'])) {
                    $list_results = array_merge($list_results, $entities['contacts']);
                }

                if(isset($entities['page_context']['has_more_page']) && $entities['page_context']['has_more_page'] == true){
                    $continue = true;
                }
            }
        }

        foreach ($list_results as $customer) {
            if($customer['contact_type'] != 'customer'){
                continue;
            }
            $check_connect_id = $this->check_connect_id($organization_id, $customer['contact_id'], 'customer', 'zoho');

            $customer_data = [];
            $customer_data['company'] = $customer['company_name'] != '' ? $customer['company_name'] : $customer['customer_name'];
            $customer_data['website'] = $customer['website'];
            $customer_data['phonenumber'] = $customer['phone'];


            $billing_street = '';
            $billing_city = '';
            $billing_zip = '';
            

            $customer_data['billing_street'] = $billing_street;
            $customer_data['billing_city'] = $billing_city;
            $customer_data['billing_state'] = '';
            $customer_data['billing_zip'] = $billing_zip;
            $customer_data['billing_country'] = '';

            $shipping_street = '';
            $shipping_city = ''; 
            $shipping_zip = '';
            

            $customer_data['shipping_street'] = $shipping_street;
            $customer_data['shipping_city'] = $shipping_city;
            $customer_data['shipping_state'] = '';
            $customer_data['shipping_zip'] = $shipping_zip;
            $customer_data['shipping_country'] = '';

            $currency = get_currency($customer['currency_code']);
            $customer_data['default_currency'] = $currency ? $currency->id : '';

            if($check_connect_id != 0 && isset($customer_arr[$check_connect_id])){
                $this->clients_model->update($customer_data, $check_connect_id);
                $client_id = $check_connect_id;
            }else{
                if($check_connect_id != 0){
                    $this->delete_integration_log($organization_id, $check_connect_id, 'customer', 'zoho');
                }
                $client_id = $this->clients_model->add($customer_data);
            }

            $sync_status = 0;
            if($client_id){
            $sync_status = 1;
                if(!isset($customer_arr[$check_connect_id])){
                    if($customer['email'] != ''){
                        $contact_data = [];
                        $contact_data['firstname'] = $customer['first_name'];
                        $contact_data['lastname'] = $customer['last_name'];
                        $contact_data['phonenumber'] = $customer['phone'];
                        $contact_data['email'] = $customer['email'];
                        $contact_data['title'] = '';
                        $contact_data['direction'] = '';
                        $contact_data['fakeusernameremembered'] = '';
                        $contact_data['fakepasswordremembered'] = '';
                        $contact_data['password'] = '123456@';
                        $contact_data['is_primary'] = 'on';
                        $contact_data['donotsendwelcomeemail'] = 'on';
                        $contact_data['permissions'] = ['1','2','3','4','5','6'];
                        $contact_data['invoice_emails'] = 'invoice_emails';
                        $contact_data['estimate_emails'] = 'estimate_emails';
                        $contact_data['credit_note_emails'] = 'credit_note_emails';
                        $contact_data['project_emails'] = 'project_emails';
                        $contact_data['ticket_emails'] = 'ticket_emails';
                        $contact_data['task_emails'] = 'task_emails';
                        $contact_data['contract_emails'] = 'contract_emails';
                        $this->clients_model->add_contact($contact_data, $client_id);
                    }

                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $client_id,
                        'rel_type' => 'customer',
                        'software' => 'zoho',
                        'connect_id' => $customer['contact_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $client_id,
                        'rel_type' => 'customer',
                        'software' => 'zoho',
                        'type' => 'sync_down',
                        'status' => $sync_status,
                        'connect_id' => $customer['contact_id'],
                        'datecreated' => date('Y-m-d H:i:s'),
                    ]);
        }

        return ['success' => true, 'has_more_page' => $has_more_page, 'count' => count($list_results)];
    }

    public function get_zoho_invoice($organization_id, $page = 0){
        $this->get_zoho_item($organization_id);
        $this->load->model('invoices_model');
        $invoice_list = $this->invoices_model->get();
     
        $invoice_arr = [];
        foreach ($invoice_list as $invoice) {
            $invoice_arr[$invoice['id']] = $invoice;
        }

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $list_results = [];
        $has_more_page = false;

        if ($page > 0) {
            $url = $api_domain.'/books/v3/invoices?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            if (isset($entities['invoices'])) {
                $list_results = $entities['invoices'];
            }
            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $has_more_page = true;
            }
        } else {
            $continue = true;
            $page = 1;

            while ($continue) {
                $continue = false;
                $url = $api_domain.'/books/v3/invoices?page='.$page.'&organization_id='.$organization_id;
                $entities = $this->callAPI($url, [], $header, 'GET');
                $page++;

                if (isset($entities['invoices'])) {
                    $list_results = array_merge($list_results, $entities['invoices']);
                }

                if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                    $continue = true;
                }
            }
        }
        
        $this->load->model('payment_modes_model');

        $payment_modes = $this->payment_modes_model->get();

        $payment_model_list = [];
        if ($payment_modes) {
            foreach($payment_modes as $payment_mode){
                $payment_model_list[] = $payment_mode['id'];
            }
        }

        foreach ($list_results as $invoice) {
            $check_connect_id = $this->check_connect_id($organization_id, $invoice['invoice_id'], 'invoice', 'zoho');

            $customer_connect_id = $this->check_connect_id($organization_id, $invoice['customer_id'], 'customer', 'zoho');
            if($customer_connect_id == 0){
                $this->get_zoho_customer($organization_id);
                $customer_connect_id = $this->check_connect_id($organization_id, $invoice['customer_id'], 'customer', 'zoho');
            }

            $invoice_data = [];

            $currency = get_currency($invoice['currency_code']);
            $invoice_data['currency'] = $currency ? $currency->id : '';

            $invoice_data['date'] = $invoice['date'];
            $invoice_data['duedate'] = $invoice['due_date'];

            $invoice_data['clientid']         = $customer_connect_id;

            $invoice_data['include_shipping']         = 1;
            $invoice_data['show_shipping_on_invoice'] = 1;

            $invoice_data["allowed_payment_modes"] = $payment_model_list;

            $billing_street = '';
            $billing_city = '';
            $billing_state = '';
            $billing_zip = '';
            if($invoice['billing_address']){
                $billing_street = $invoice['billing_address']['address'];
                $billing_city = $invoice['billing_address']['city'];
                $billing_state = $invoice['billing_address']['state'];
                $billing_zip = $invoice['billing_address']['zipcode'];
            }

            $invoice_data['billing_street'] = $billing_street;
            $invoice_data['billing_city'] = $billing_city;
            $invoice_data['billing_state'] = $billing_state;
            $invoice_data['billing_zip'] = $billing_zip;
            $invoice_data['billing_country'] = '';

            $shipping_street = '';
            $shipping_city = '';
            $shipping_state = '';
            $shipping_zip = '';
            if($invoice['shipping_address']){
                $shipping_street = $invoice['shipping_address']['address'];
                $shipping_city = $invoice['shipping_address']['city'];
                $shipping_state = $invoice['shipping_address']['state'];
                $shipping_zip = $invoice['shipping_address']['zipcode'];
            }


            
            $invoice_data['shipping_street'] = $shipping_street;
            $invoice_data['shipping_city'] = $shipping_city;
            $invoice_data['shipping_state'] = $shipping_state;
            $invoice_data['shipping_zip'] = $shipping_zip;
            $invoice_data['shipping_country'] = '';

            $invoice_data['total']               = $invoice['total'];

            $url = $api_domain.'/books/v3/invoices/'.$invoice['invoice_id'].'?organization_id='.$organization_id;
            $invoice_detail = $this->callAPI($url, [], $header, 'GET');
            if(!isset($invoice_detail['invoice'])){
                continue;
            }
            $invoice_data['subtotal']            = $invoice_detail['invoice']['sub_total'];

            if($invoice_detail['invoice']['discount_total'] > 0){
                if($invoice_detail['invoice']['is_discount_before_tax'] == true){
                    $invoice_data['discount_type'] = "before_tax";
                }else{
                    $invoice_data['discount_type'] = "after_tax";
                }

                $invoice_data['discount_total']               = $invoice_detail['invoice']['discount_total'];
                $invoice_data['discount_percent']               = $invoice_detail['invoice']['discount_percent'];
            }

            $newitems = [];
            foreach ($invoice_detail['invoice']['line_items'] as $key => $value) {
                $tax_connect_id = $this->check_connect_id($organization_id, $value['tax_id'], 'tax', 'zoho');
                $tax_arr = '';
                if($tax_connect_id != 0){
                    $this->db->where('id', $tax_connect_id);
                    $_tax = $this->db->get(db_prefix().'taxes')->row();
                    if($_tax){
                        $tax_arr = [$_tax->name.'|'.$_tax->taxrate];
                    }
                }else{
                    if($value['tax_id'] != '' && $value['tax_id'] != null){
                        $url = $api_domain.'/books/v3/settings/taxes/'.$value['tax_id'].'?organization_id='.$organization_id;
                        $tax = $this->callAPI($url, [], $header, 'GET');
                         
                        if(isset($tax['tax'])){
                            $this->db->insert(db_prefix().'taxes', [
                                'name' => $tax['tax']['tax_name'],
                                'taxrate' => $tax['tax']['tax_percentage'],
                            ]);

                            $tax_id = $this->db->insert_id();

                            if($tax_id){
                                $this->db->insert(db_prefix().'acc_integration_logs', [
                                    'organization_id' => $organization_id,
                                    'rel_id' => $tax_id,
                                    'rel_type' => 'tax',
                                    'software' => 'zoho',
                                    'connect_id' => $tax['tax']['tax_id'],
                                    'date_updated' => date('Y-m-d H:i:s'),
                                ]);

                                $tax_arr = [$tax['tax']['tax_name'].'|'.$tax['tax']['tax_percentage']];
                            }
                        }
                    }
                }

                array_push($newitems, array(
                    'order' => $value['item_order'], 
                    'description' => $value['name'] != '' ? $value['name'] : $value['description'], 
                    'long_description' => '', 
                    'qty' => $value['quantity'] ?? 1, 
                    'unit' => '', 
                    'rate' => $value['rate'], 
                    'taxname' => $tax_arr));
            }

            $invoice_data['adjustment'] = 0;
            if($invoice_detail['invoice']['shipping_charge'] > 0){
                $invoice_data['adjustment'] += $invoice_detail['invoice']['shipping_charge'];
            }

            if($invoice_detail['invoice']['adjustment'] != 0){
                $invoice_data['adjustment'] += $invoice_detail['invoice']['adjustment'];
            }

            $invoice_data['newitems'] = $newitems;
            
            if($check_connect_id != 0 && isset($invoice_arr[$check_connect_id])){
                $this->delete_invoice_item($check_connect_id);
                $this->invoices_model->update($invoice_data, $check_connect_id);
                $invoice_id = $check_connect_id;
            }else{
                if($check_connect_id != 0){
                    $this->delete_integration_log($organization_id, $check_connect_id, 'invoice', 'zoho');
                }

                $__number        = get_option('next_invoice_number');
                $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                $invoice_data['number']              = $_invoice_number;

                $invoice_id = $this->invoices_model->add($invoice_data);
            }

            $sync_status = 0;
            if($invoice_id){
            $sync_status = 1;
                if(!isset($invoice_arr[$check_connect_id])){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $invoice_id,
                        'rel_type' => 'invoice',
                        'software' => 'zoho',
                        'connect_id' => $invoice['invoice_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $invoice_id,
                        'rel_type' => 'invoice',
                        'software' => 'zoho',
                        'type' => 'sync_down',
                        'status' => $sync_status,
                        'connect_id' => $invoice['invoice_id'],
                        'datecreated' => date('Y-m-d H:i:s'),
                    ]);
        }
        return ['success' => true, 'has_more_page' => $has_more_page, 'count' => count($list_results)];
    }

    public function get_zoho_payment($organization_id, $page = 0){
        $this->load->model('payments_model');
        $payment_list = $this->db->get(db_prefix() . 'invoicepaymentrecords')->result_array();
     
        $payment_arr = [];
        foreach ($payment_list as $payment) {
            $payment_arr[$payment['id']] = $payment;
        }

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $list_results = [];
        $has_more_page = false;

        if ($page > 0) {
            $url = $api_domain.'/books/v3/customerpayments?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            if (isset($entities['customerpayments'])) {
                $list_results = $entities['customerpayments'];
            }
            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $has_more_page = true;
            }
        } else {
            $continue = true;
            $page = 1;
            while ($continue) {
                $continue = false;
                $url = $api_domain.'/books/v3/customerpayments?page='.$page.'&organization_id='.$organization_id;
                $entities = $this->callAPI($url, [], $header, 'GET');
                $page++;

                if (isset($entities['customerpayments'])) {
                    $list_results = array_merge($list_results, $entities['customerpayments']);
                }

                if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                    $continue = true;
                }
            }
        }

        $this->load->model('payment_modes_model');

        $payment_modes = $this->payment_modes_model->get();

        $fisrt_payment_mode = 0;
        if (isset($payment_modes[0])) {
            $fisrt_payment_mode = $payment_modes[0]['id'];
        }

        foreach ($list_results as $payment) {
            $check_connect_id = $this->check_connect_id($organization_id, $payment['payment_id'], 'payment', 'zoho');

            if($payment['payment_type'] != 'Invoice Payment'  || $payment['invoice_numbers'] == ''){
                continue;
            }

            $url = $api_domain.'/books/v3/customerpayments/'.$payment['payment_id'].'?organization_id='.$organization_id;
            $payment_detail = $this->callAPI($url, [], $header, 'GET');
            $invoice_id = 0;
            $amount = round($payment['amount'], 2);
            foreach ($payment_detail['payment']['invoices'] as $key => $value) {
                $invoice_id = $this->check_connect_id($organization_id, $value['invoice_id'], 'invoice', 'zoho');
                $amount = round($value['amount_applied'], 2);
            }

            if($invoice_id == 0){
                continue;
            }

            $payment_data = [];

            $payment_data['invoiceid'] = $invoice_id;
            $payment_data['amount'] = $amount;
            $payment_data['date'] = $payment['date'];
            $payment_data['transactionid'] = $payment_detail['payment']['online_transaction_id'];
            $payment_data['note'] = $payment['description'];

            if($check_connect_id != 0 && isset($payment_arr[$check_connect_id])){
                $this->payments_model->update($payment_data, $check_connect_id);
                $payment_id = $check_connect_id;
            }else{
                if($check_connect_id != 0){
                    $this->delete_integration_log($organization_id, $check_connect_id, 'payment', 'zoho');
                }

                $payment_data['do_not_send_email_template'] = 'on';
                $payment_data['do_not_redirect'] = 'on';
                $payment_data["paymentmode"] = $fisrt_payment_mode;
                $payment_id = $this->payments_model->add($payment_data);
            }

            $sync_status = 0;
            if($payment_id){
            $sync_status = 1;
                if(!isset($payment_arr[$check_connect_id])){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $payment_id,
                        'rel_type' => 'payment',
                        'software' => 'zoho',
                        'connect_id' => $payment['payment_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $payment_id,
                        'rel_type' => 'payment',
                        'software' => 'zoho',
                        'type' => 'sync_down',
                        'status' => $sync_status,
                        'connect_id' => $payment['payment_id'],
                        'datecreated' => date('Y-m-d H:i:s'),
                    ]);
        }
        return ['success' => true, 'has_more_page' => $has_more_page, 'count' => count($list_results)];
    }

    public function get_zoho_expense($organization_id, $page = 0){
        $this->load->model('expenses_model');
        $expense_list = $this->expenses_model->get();
     
        $expense_arr = [];
        foreach ($expense_list as $expense) {
            $expense_arr[$expense['id']] = $expense;
        }

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        $list_results = [];
        $has_more_page = false;

        if ($page > 0) {
            $url = $api_domain.'/books/v3/expenses?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            if (isset($entities['expenses'])) {
                $list_results = $entities['expenses'];
            }
            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $has_more_page = true;
            }
        } else {
            $continue = true;
            $page = 1;
            while ($continue) {
                $continue = false;
                $url = $api_domain.'/books/v3/expenses?page='.$page.'&organization_id='.$organization_id;
                $entities = $this->callAPI($url, [], $header, 'GET');
                $page++;

                if (isset($entities['expenses'])) {
                    $list_results = array_merge($list_results, $entities['expenses']);
                }

                if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                    $continue = true;
                }
            }
        }

        $this->load->model('payment_modes_model');
        $payment_modes = $this->payment_modes_model->get();

        $fisrt_payment_mode = 0;
        if (isset($payment_modes[0])) {
            $fisrt_payment_mode = $payment_modes[0]['id'];
        }

        foreach ($list_results as $expense) {
            $check_connect_id = $this->check_connect_id($organization_id, $expense['expense_id'], 'expense', 'zoho');
            
            $url = $api_domain.'/books/v3/expenses/'.$expense['expense_id'].'?organization_id='.$organization_id;
            $expense_detail = $this->callAPI($url, [], $header, 'GET');
            $customer_connect_id = $this->check_connect_id($organization_id, $expense['customer_id'], 'customer', 'zoho');

            $expense_data = [];

            $expense_data['vendor'] = '';
            $expense_data['expense_name'] = '';
            $expense_data['note'] = $expense['description'];
            $expense_data['category'] = $this->init_expense_category('Zoho Expenses');
            $expense_data['date'] = $expense['date'];
            $expense_data['amount'] = $expense['total_without_tax'];
            $expense_data['clientid'] = $customer_connect_id;
            $expense_data['project_id'] = '';

            $currency = get_currency($expense['currency_code']);
            $expense_data['currency'] = $currency ? $currency->id : '';

            $tax_connect_id = $this->check_connect_id($organization_id, $expense_detail['expense']['tax_id'], 'tax', 'zoho');
            if($tax_connect_id != 0){
                $expense_data['tax'] = $tax_connect_id;
            }else{
                $this->db->insert(db_prefix().'taxes', [
                    'name' => $expense_detail['expense']['tax_name'],
                    'taxrate' => $expense_detail['expense']['tax_percentage'],
                ]);

                $tax_id = $this->db->insert_id();

                if($tax_id){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $tax_id,
                        'rel_type' => 'tax',
                        'software' => 'zoho',
                        'connect_id' => $expense_detail['expense']['tax_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);

                    $expense_data['tax'] = $tax_id;
                }
            }

            $expense_data['tax2'] = '';
            $expense_data['paymentmode'] = '';
            $expense_data['reference_no'] = '';
            $expense_data['repeat_every'] = '';
            $expense_data['repeat_every_custom'] = '1';
            $expense_data['repeat_type_custom'] = 'day';

            if($check_connect_id != 0 && isset($expense_arr[$check_connect_id])){
                $this->expenses_model->update($expense_data, $check_connect_id);
                $expense_id = $check_connect_id;
            }else{
                if($check_connect_id != 0){
                    $this->delete_integration_log($organization_id, $check_connect_id, 'expense', 'zoho');
                }

                $expense_id = $this->expenses_model->add($expense_data);
            }

            $sync_status = 0;
            if($expense_id){
            $sync_status = 1;
                if(!isset($expense_arr[$check_connect_id])){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $expense_id,
                        'rel_type' => 'expense',
                        'software' => 'zoho',
                        'connect_id' => $expense['expense_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->insert(db_prefix().'acc_integration_sync_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $expense_id,
                        'rel_type' => 'expense',
                        'software' => 'zoho',
                        'type' => 'sync_down',
                        'status' => $sync_status,
                        'connect_id' => $expense['expense_id'],
                        'datecreated' => date('Y-m-d H:i:s'),
                    ]);
        }
        return ['success' => true, 'has_more_page' => $has_more_page, 'count' => count($list_results)];
    }

    public function get_zoho_taxes($organization_id){
        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();
        $url = $api_domain.'/books/v3/settings/taxes?organization_id='.$organization_id;

        $entities = $this->callAPI($url, [], $header, 'GET');
        if ($entities['code'] != 0) {
            
        } else {
            $this->load->model('payment_modes_model');
            $payment_modes = $this->payment_modes_model->get();

            $fisrt_payment_mode = 0;
            if (isset($payment_modes[0])) {
                $fisrt_payment_mode = $payment_modes[0]['id'];
            }

            foreach ($entities['taxes'] as $tax) {
                $check_connect_id = $this->check_connect_id($organization_id, $tax['tax_id'], 'tax', 'zoho');
                if($check_connect_id != 0){
                    continue;
                }

                $this->db->insert(db_prefix().'taxes', [
                    'name' => $tax['tax_name'],
                    'taxrate' => $tax['tax_percentage'],
                ]);

                $insert_id = $this->db->insert_id();

                if($insert_id){
                    $this->db->insert(db_prefix().'acc_integration_logs', [
                        'organization_id' => $organization_id,
                        'rel_id' => $insert_id,
                        'rel_type' => 'tax',
                        'software' => 'zoho',
                        'connect_id' => $tax['tax_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }

    public function delete_integration_log($organization_id, $rel_id, $rel_type, $software){
        $this->db->where('organization_id', $organization_id);
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->where('software', $software);
        $this->db->delete(db_prefix().'acc_integration_logs');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_connect_id($organization_id, $rel_id, $rel_type, $software = 'quickbook'){
        $this->db->where('organization_id', $organization_id);
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->where('software', $software);
        $log = $this->db->get(db_prefix().'acc_integration_logs')->row();

        if($log){
            return $log->connect_id;
        }

        return '';
    }

    public function delete_invoice_item($id){
        $items = get_items_by_type('invoice', $id);

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'invoice');
        $this->db->delete(db_prefix() . 'itemable');

        foreach ($items as $item) {
            $this->db->where('item_id', $item['id']);
            $this->db->delete(db_prefix() . 'related_items');
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'invoice');
        $this->db->delete(db_prefix() . 'item_tax');

        return true;
    }


    public function get_zoho_organizations(){
        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();
        $url = $api_domain.'/books/v3/organizations';

        $entities = $this->callAPI($url, [], $header, 'GET');

        if ($entities['code'] != 0) {
            return [];
        } else {
            return $entities['organizations'];
        }
    }

    public function check_old_data(){
        $this->db->where('organization_id IS NULL');
        $this->db->where('software', 'zoho');
        $log = $this->db->get(db_prefix().'acc_integration_logs')->row();

        if($log){
            return true;
        }

        return false;
    }

    public function migrate_legacy_data($organization_id){
        $this->db->where('organization_id IS NULL');
        $this->db->where('software', 'zoho');
        $this->db->update(db_prefix().'acc_integration_logs', ['organization_id' => $organization_id]);

        $this->db->where('organization_id IS NULL');
        $this->db->where('software', 'zoho');
        $this->db->update(db_prefix().'acc_integration_error_logs', ['organization_id' => $organization_id]);

        $this->db->where('organization_id IS NULL');
        $this->db->where('software', 'zoho');
        $this->db->update(db_prefix().'acc_integration_sync_logs', ['organization_id' => $organization_id]);

        return true;
    }

    /**
     * [create_zoho_item description]
     * @param  string $organization_id [description]
     * @param  array  $item_array      [description]
     * @return [type]                  [description]
     */
    public function create_zoho_item($organization_id = '', $item_array = []){

        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();

        foreach ($item_array as $item) {
            $itemObj = [
                "sku" => $item['code'],
                "name" => $item['description'],
                "rate" => $item['rate'],
            ];

            $url = $api_domain.'/books/v3/items?organization_id='.$organization_id;
            $result = $this->callAPI($url, $itemObj, $header, 'POST');
        }
    }

    /**
     * [get_zoho_item description]
     * @param  string $organization_id [description]
     * @return [type]              [description]
     */
    public function get_zoho_item($organization_id = ''){
        $header = acc_get_zoho_header();
        $api_domain = acc_get_zoho_api_domain();
        
        $items = $this->db->get(db_prefix().'items')->result_array();
        $item_data = [];
        foreach ($items as $item) {
            $item_data[$item['commodity_code']] = 1;
        }

        $continue = true;
        $page = 1;

        $insert_item = [];
        $list_results = [];
        while ($continue) {
            $continue = false;
            $url = $api_domain.'/books/v3/items?page='.$page.'&organization_id='.$organization_id;
            $entities = $this->callAPI($url, [], $header, 'GET');
            $page++;

            if (isset($entities['items'])) {
                $list_results = array_merge($list_results, $entities['items']);
            }

            if(isset($entities['response']['page_context']['has_more_page']) && $entities['response']['page_context']['has_more_page'] == true){
                $continue = true;
            }
        }

        foreach ($list_results as $item) {
            if (!isset($item_data[$item['sku']])) {
                $insert_item[] = [
                    'commodity_code' => $item['sku'],
                    'description' => $item['name'],
                    'rate' => $item['rate'],
                ];
            }
        }

        if($insert_item != []){
            $affectedRows = $this->db->insert_batch(db_prefix().'items', $insert_item);
        }
    }

}