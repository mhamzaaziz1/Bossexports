<?php
defined('BASEPATH') or exit('No direct script access allowed');

function get_acc_zoho_config(){
    $CI = &get_instance();

    $client_id = get_option('acc_integration_zoho_client_id'); 
    $client_secret = get_option('acc_integration_zoho_client_secret'); 

    return array(
      'authorizationRequestUrl' => 'https://accounts.zoho.com/oauth/v2/auth', 
      'client_id' => $CI->encryption->decrypt($client_id), 
      'client_secret' => $CI->encryption->decrypt($client_secret), 
      'scope' => 'ZohoBooks.fullaccess.all', 
      'redirect_uri' => admin_url('zoho_integration/connect'), 
    );
}

function acc_get_zoho_header(){
    $header = array(
        'Content-Type: application/json',
        'Authorization: Zoho-oauthtoken '. get_option('acc_integration_zoho_access_token'));

    return $header;
}

function acc_get_zoho_api_domain(){
    $api_domain = get_option('acc_integration_zoho_api_domain');

    return $api_domain;
}

/**
 * Gets the url by type identifier.
 */
if(!function_exists('sync_get_url_by_type_id')){
  function sync_get_url_by_type_id($rel_id, $rel_type){
    $url = '#';
    $name = '';
    switch ($rel_type) {
          case 'invoice':
              $name = format_invoice_number($rel_id);
              $url = admin_url('invoices/list_invoices/'.$rel_id);
          break;

          case 'expense':
              $name = _l('sync_expense').' #'.$rel_id;
              $url = admin_url('expenses/list_expenses/'.$rel_id);
          break;

          case 'payment':
              $name = _l('sync_payment').' #'.$rel_id;
              $url = admin_url('payments/payment/'.$rel_id);
          break;

          case 'customer':
              $name = get_company_name($rel_id);
              $url = admin_url('clients/client/'.$rel_id);
          break;
      }

      if($name == ''){
        $name = _l('sync_'.$rel_type).' #'.$rel_id;
      }

      return '<a href="'.$url.'">'.$name.'</a>';
  }
}