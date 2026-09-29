<?php
defined('BASEPATH') or exit('No direct script access allowed');

function si_sms_get_customer_merge_fields($clientid)
{
	$merge_fields = array();
	$phonenumber = '';
	$CI = &get_instance();
	$client = $CI->clients_model->get($clientid);
	if (!$client) {
		return $merge_fields;
	}
	$contact = $CI->clients_model->get_contact(get_primary_contact_user_id($clientid));
	if ($contact) {
		if(!is_null($contact->phonenumber) && $contact->phonenumber!=='')
			$phonenumber = $contact->phonenumber;
		elseif(get_option(SI_SMS_MODULE_NAME.'_send_to_alt_client'))
			$phonenumber = $client->phonenumber;	
		$merge_fields['{contact_firstname}'] = $contact->firstname;
		$merge_fields['{contact_lastname}'] = $contact->lastname;
	}elseif(get_option(SI_SMS_MODULE_NAME.'_send_to_alt_client')){
		$phonenumber = $client->phonenumber;
		$merge_fields['{contact_firstname}'] = $client->company;
		$merge_fields['{contact_lastname}'] = '';
	}
	$merge_fields['{client_company}'] = $client->company;
	$merge_fields['{client_id}'] = $clientid;
	$merge_fields['phone_number'] = $phonenumber;
	return $merge_fields;
}