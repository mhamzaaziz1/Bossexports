<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: BecomeDigital Custom SMS
Description: Using This Module you can send extra SMS, than default SMS of Perfex. like Trigger on Invoice, Contract, Ticket, Project, Credit Note, Estimate Raised
Author: <a href="https://becomedigital.in" target="_blank">Become Digital (An Initiative of DSU INFOTECH&reg;)</a>
Version: 1.0.0
Requires at least: 1.0.*
*/

define('bd_sms_MODULE_NAME', 'bd_sms');

define('bd_sms_TRIGGER_PROJECT_CREATED', 'bd_sms_project_created');
define('bd_sms_TRIGGER_INVOICE_CREATED', 'bd_sms_invoice_created');
define('bd_sms_TRIGGER_PROPOSAL_CREATED', 'bd_sms_proposal_created');
define('bd_sms_TRIGGER_ESTIMATE_CREATED', 'bd_sms_estimate_created');
define('bd_sms_TRIGGER_CONTRACT_CREATED', 'bd_sms_contract_created');
define('bd_sms_TRIGGER_TICKET_CREATED', 'bd_sms_ticket_created');
define('bd_sms_TRIGGER_CREDIT_NOTE_CREATED','bd_sms_credit_note_created');

$CI = &get_instance();

hooks()->add_filter('module_'.bd_sms_MODULE_NAME.'_action_links', 'module_bd_sms_action_links');
hooks()->add_filter('sms_gateway_available_triggers','bd_sms_hook_sms_gateway_available_triggers');
hooks()->add_action('after_add_project','bd_sms_hook_after_add_project');
hooks()->add_action('after_invoice_added','bd_sms_hook_after_invoice_added');
hooks()->add_action('proposal_created','bd_sms_hook_proposal_created');
hooks()->add_action('after_estimate_added','bd_sms_hook_after_estimate_added');
hooks()->add_action('after_contract_added','bd_sms_hook_after_contract_added');
hooks()->add_action('ticket_created','bd_sms_hook_ticket_created');
hooks()->add_action('after_create_credit_note','bd_sms_hook_after_create_credit_note');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_bd_sms_action_links($actions)
{
	$actions[] = '<a href="' . admin_url('settings?group=sms') . '">' . _l('settings') . '</a>';
	return $actions;
}

/**
* Load the module model
*/
//$CI->load->model(bd_sms_MODULE_NAME . '/bd_sms_model');

/**
* Register activation module hook
*/
register_activation_hook(bd_sms_MODULE_NAME, 'bd_sms_activation_hook');

function bd_sms_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(bd_sms_MODULE_NAME, [bd_sms_MODULE_NAME]);

/**
*	Admin Init Hook for module
*/
/*function bd_sms_admin_init_hook()
{
	
}*/

function get_customer_merge_fields($clientid)
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
		else
			$phonenumber = $client->phonenumber;	
		$merge_fields['{contact_firstname}'] = $contact->firstname;
		$merge_fields['{contact_lastname}'] = $contact->lastname;
	}else{
		$phonenumber = $client->phonenumber;
		$merge_fields['{contact_firstname}'] = $client->company;
		$merge_fields['{contact_lastname}'] = '';
	}
	$merge_fields['{client_company}'] = $client->company;
	$merge_fields['{client_id}'] = $clientid;
	$merge_fields['phone_number'] = $phonenumber;
	return $merge_fields;
}

/**Hook to add sms triggers**/
function bd_sms_hook_sms_gateway_available_triggers($triggers)
{
	$customer_merge_fields = [
		'{contact_firstname}',
		'{contact_lastname}',
		'{client_company}',
		'{client_id}',
	];
	$project_merge_fields = [
		'{project_name}',
		'{project_id}',
	];
	$invoice_merge_fields = [
		'{invoice_number}',
		'{invoice_link}',
		'{invoice_date}',
		'{invoice_subtotal}',
		'{invoice_total}',
		'{invoice_short_url}',
	];
	$proposal_merge_fields = [
		'{proposal_number}',
		'{proposal_id}',
		'{proposal_subject}',
		'{proposal_total}',
		'{proposal_open_till}',
		'{proposal_subtotal}',
		'{proposal_proposal_to}',
		'{proposal_link}',
		'{proposal_short_url}',
	];
	$estimate_merge_fields = [
		'{estimate_number}',
		'{estimate_date}',
		'{estimate_subtotal}',
		'{estimate_total}',
		'{estimate_link}',
		'{estimate_short_url}',
	];
	$contract_merge_fields = [
		'{contract_id}',
		'{contract_subject}',
		'{contract_datestart}',
		'{contract_dateend}',
		'{contract_contract_value}',
		'{contract_link}',
		'{contract_short_url}',
	];
	$ticket_merge_fields = [
		'{contact_firstname}',
		'{ticket_date}',
		'{ticket_subject}',
		'{ticket_status}',
		'{ticket_service}',
		'{ticket_department}',
		'{ticket_url}',
		'{ticket_public_url}',
	];
	$credit_note_merge_fields = [
		'{credit_note_number}',
		'{credit_note_subtotal}',
		'{credit_note_total}',
		'{credit_note_date}',
	];
	$triggers[bd_sms_TRIGGER_PROJECT_CREATED] = [
								'merge_fields' => array_merge($customer_merge_fields,$project_merge_fields),
								'label' => _l('bd_sms_label_project_created'),
								'info'  => _l('bd_sms_info_project_created'),
	];
	$triggers[bd_sms_TRIGGER_INVOICE_CREATED] = [
								'merge_fields' => array_merge($customer_merge_fields,$invoice_merge_fields),
								'label' => _l('bd_sms_label_invoice_created'),
								'info'  => _l('bd_sms_info_invoice_created'),
	];
	$triggers[bd_sms_TRIGGER_PROPOSAL_CREATED] = [
								'merge_fields' => $proposal_merge_fields,
								'label' => _l('bd_sms_label_proposal_created'),
								'info'  => _l('bd_sms_info_proposal_created'),
	];
	$triggers[bd_sms_TRIGGER_ESTIMATE_CREATED] = [
								'merge_fields' => array_merge($customer_merge_fields,$estimate_merge_fields),
								'label' => _l('bd_sms_label_estimate_created'),
								'info'  => _l('bd_sms_info_estimate_created'),
	];
	$triggers[bd_sms_TRIGGER_CONTRACT_CREATED] = [
								'merge_fields' => array_merge($customer_merge_fields,$contract_merge_fields),
								'label' => _l('bd_sms_label_contract_created'),
								'info'  => _l('bd_sms_info_contract_created'),
	];
	$triggers[bd_sms_TRIGGER_TICKET_CREATED] = [
								'merge_fields' => $ticket_merge_fields,
								'label' => _l('bd_sms_label_ticket_created'),
								'info'  => _l('bd_sms_info_ticket_created'),
	];
	$triggers[bd_sms_TRIGGER_CREDIT_NOTE_CREATED] = [
								'merge_fields' => array_merge($customer_merge_fields,$credit_note_merge_fields),
								'label' => _l('bd_sms_label_credit_note_created'),
								'info'  => _l('bd_sms_info_credit_note_created'),
	];

	return $triggers;
				
}
function bd_sms_hook_after_add_project($project_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($project_id)){
		$CI = &get_instance();
		$CI->db->select('clientid,name');
		$CI->db->where('id', $project_id);
		$project = $CI->db->get(db_prefix() . 'projects')->row();
		$merge_fields = get_customer_merge_fields($project->clientid);
		$merge_fields['{project_name}'] = $project->name;
		$merge_fields['{project_id}'] = $project_id;
		if(isset($merge_fields['phone_number']))
			$phonenumber = $merge_fields['phone_number'];
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$CI->app_sms->trigger(bd_sms_TRIGGER_PROJECT_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_after_invoice_added($invoice_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($invoice_id)){
		$CI = &get_instance();
		$CI->db->where('id', $invoice_id);
		$invoice = $CI->db->get(db_prefix() . 'invoices')->row();
		$currency = get_currency($invoice->currency);
		$merge_fields = get_customer_merge_fields($invoice->clientid);
		$merge_fields['{invoice_number}'] 	= format_invoice_number($invoice_id);
		$merge_fields['{invoice_link}'] 	= site_url('invoice/' . $invoice_id . '/' . $invoice->hash);
		$merge_fields['{invoice_date}'] 	= _d($invoice->date);
		$merge_fields['{invoice_subtotal}'] = app_format_money($invoice->subtotal, $currency);
		$merge_fields['{invoice_total}'] 	= app_format_money($invoice->total, $currency);
		$merge_fields['{invoice_short_url}']= (function_exists('get_invoice_shortlink')?get_invoice_shortlink($invoice) : site_url('invoice/' . $invoice_id . '/' . $invoice->hash));//invoice_short_url available from Perfex Version 2.7.3
		
		if(isset($merge_fields['phone_number']))
			$phonenumber = $merge_fields['phone_number'];
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$CI->app_sms->trigger(bd_sms_TRIGGER_INVOICE_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_proposal_created($proposal_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($proposal_id)){
		$CI = &get_instance();
		$proposal = $CI->proposals_model->get($proposal_id);
		if (!$proposal) {
            return;
        }
		if ($proposal->currency != 0) {
            $currency = get_currency($proposal->currency);
        } else {
            $currency = get_base_currency();
        }
		if(!is_null($proposal->phone) && $proposal->phone!==''){
			$phonenumber = $proposal->phone;
			$merge_fields['{proposal_number}'] 		= format_proposal_number($proposal_id);
			$merge_fields['{proposal_id}'] 			= $proposal_id;
			$merge_fields['{proposal_subject}'] 	= $proposal->subject;
			$merge_fields['{proposal_open_till}'] 	= _d($proposal->open_till);
			$merge_fields['{proposal_subtotal}'] 	= app_format_money($proposal->subtotal, $currency);
			$merge_fields['{proposal_total}'] 		= app_format_money($proposal->total, $currency);;
			$merge_fields['{proposal_proposal_to}'] = $proposal->proposal_to;
			$merge_fields['{proposal_link}']        = site_url('proposal/' . $proposal_id . '/' . $proposal->hash);
			$merge_fields['{proposal_short_url}']= (function_exists('get_proposal_shortlink')?get_proposal_shortlink($proposal) : site_url('proposal/' . $proposal_id . '/' . $proposal->hash));//proposal_short_url available from Perfex Version 2.7.3
				
			$CI->app_sms->trigger(bd_sms_TRIGGER_PROPOSAL_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_after_estimate_added($estimate_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($estimate_id)){
		$CI = &get_instance();
		$estimate = $CI->estimates_model->get($estimate_id);
		if (!$estimate) {
            return;
        }
        $currency = get_currency($estimate->currency);
		$merge_fields = get_customer_merge_fields($estimate->clientid);
		if(isset($merge_fields['phone_number']))
			$phonenumber = $merge_fields['phone_number'];
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$merge_fields['{estimate_total}']        = app_format_money($estimate->total, $currency);
			$merge_fields['{estimate_subtotal}']     = app_format_money($estimate->subtotal, $currency);
			$merge_fields['{estimate_link}']         = site_url('estimate/' . $estimate_id . '/' . $estimate->hash);
			$merge_fields['{estimate_number}']       = format_estimate_number($estimate_id);
			$merge_fields['{estimate_date}']         = _d($estimate->date);
			$merge_fields['{estimate_status}']       = format_estimate_status($estimate->status, '', false);
			$merge_fields['{estimate_short_url}']	 = (function_exists('get_estimate_shortlink')?get_estimate_shortlink($estimate) : site_url('estimate/' . $estimate_id . '/' . $estimate->hash));//estimate_short_url available from Perfex Version 2.7.3
			
			$CI->app_sms->trigger(bd_sms_TRIGGER_ESTIMATE_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_after_contract_added($contract_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($contract_id)){
		$CI = &get_instance();
		$contract = $CI->contracts_model->get($contract_id);
		if (!$contract) {
            return;
        }
        $currency = get_base_currency();
		$merge_fields = get_customer_merge_fields($contract->client);
		if(isset($merge_fields['phone_number']))
			$phonenumber = $merge_fields['phone_number'];
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$merge_fields['{contract_id}']        		= $contract->id;
			$merge_fields['{contract_subject}']        	= $contract->subject;
			$merge_fields['{contract_datestart}']      	= _d($contract->datestart);
			$merge_fields['{contract_dateend}']        	= _d($contract->dateend);
			$merge_fields['{contract_contract_value}'] 	= app_format_money($contract->contract_value, $currency);
        	$merge_fields['{contract_link}'] = site_url('contract/' . $contract->id . '/' . $contract->hash);
			$merge_fields['{contract_short_url}']	 	= (function_exists('get_contract_shortlink')?get_contract_shortlink($contract) : site_url('contract/' . $contract->id . '/' . $contract->hash));//contract_short_url available from Perfex Version 2.7.3
			
			$CI->app_sms->trigger(bd_sms_TRIGGER_CONTRACT_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_ticket_created($ticket_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($ticket_id)){
		$CI = &get_instance();
		$ticket = $CI->tickets_model->get($ticket_id,[db_prefix() . 'tickets.contactid > '=>0]);
		if (!$ticket) {
            return;
        }
        $contact = $CI->clients_model->get_contact($ticket->contactid);
		if ($contact) {
			if(!is_null($contact->phonenumber) && $contact->phonenumber!=='')
				$phonenumber = $contact->phonenumber;
				
			$merge_fields['{contact_firstname}'] = $contact->firstname;
		}
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$merge_fields['{ticket_date}']    = _dt($ticket->date);
        	$merge_fields['{ticket_subject}'] = $ticket->subject;
			$merge_fields['{ticket_status}'] = ticket_status_translate($ticket->status);
        	$merge_fields['{ticket_priority}'] = ticket_priority_translate($ticket->priority);
			
			$CI->db->where('departmentid', $ticket->department);
			$department = $CI->db->get(db_prefix().'departments')->row();
			if ($department) {
				$merge_fields['{ticket_department}']       = $department->name;
				$merge_fields['{ticket_department_email}'] = $department->email;
			}
			else {
				$merge_fields['{ticket_department}']       = '';
				$merge_fields['{ticket_department_email}'] = '';
			}
			$CI->db->where('serviceid', $ticket->service);
			$service = $CI->db->get(db_prefix().'services')->row();
			if ($service) {
				$merge_fields['{ticket_service}'] = $service->name;
			}
			else {
				$merge_fields['{ticket_service}'] = '';
			}
			$merge_fields['{ticket_url}'] = site_url('clients/ticket/' . $ticket_id);
			$merge_fields['{ticket_public_url}'] = (function_exists('get_ticket_public_url') ? get_ticket_public_url($ticket) : '');//ticket_public_url available from Perfex Version 2.4.2
			
			$CI->app_sms->trigger(bd_sms_TRIGGER_TICKET_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}

function bd_sms_hook_after_create_credit_note($credit_note_id)
{
	$merge_fields = array();
	$phonenumber = '';
	if(is_numeric($credit_note_id)){
		$CI = &get_instance();
		$credit_note = $CI->credit_notes_model->get($credit_note_id);
		if (!$credit_note) {
            return;
        }
        $merge_fields = get_customer_merge_fields($credit_note->clientid);
		if(isset($merge_fields['phone_number']))
			$phonenumber = $merge_fields['phone_number'];
		
		if(!is_null($phonenumber) && $phonenumber!==''){
			$merge_fields['{credit_note_number}']            = format_credit_note_number($credit_note_id);
			$merge_fields['{credit_note_total}']             = app_format_money($credit_note->total, $credit_note->currency_name);
			$merge_fields['{credit_note_subtotal}']          = app_format_money($credit_note->subtotal, $credit_note->currency_name);
			$merge_fields['{credit_note_credits_remaining}'] = app_format_money($credit_note->remaining_credits, $credit_note->currency_name);
			$merge_fields['{credit_note_credits_used}']      = app_format_money($credit_note->credits_used, $credit_note->currency_name);
			$merge_fields['{credit_note_date}']              = _d($credit_note->date);
			$merge_fields['{credit_note_status}']            = format_credit_note_status($credit_note->status, true);
			
			$CI->app_sms->trigger(bd_sms_TRIGGER_CREDIT_NOTE_CREATED, $phonenumber, $merge_fields);
		}	
	}
	return;
}