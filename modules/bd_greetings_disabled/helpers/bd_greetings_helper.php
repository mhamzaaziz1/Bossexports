<?php
defined('BASEPATH') or exit('No direct script access allowed');

//check if email attachment is added for greetings
function handle_bd_greetings_attachment_upload()
{
	$success   = true;
	$attachmentIndex = ['bday', 'anniversary'];
	foreach ($attachmentIndex as $attachment) {
		$index = BD_GREETINGS_MODULE_NAME.'_email_attachment_'.$attachment;
		
		if (isset($_FILES[$index]['name']) && $_FILES[$index]['name'] != '') {
			$path = BD_GREETINGS_UPLOAD_PATH;
			// Get the temp file path
			$tmpFilePath = $_FILES[$index]['tmp_name'];
			// Make sure we have a filepath
			if (!empty($tmpFilePath) && $tmpFilePath != '') {
				// Getting file extension
				$extension          = strtolower(pathinfo($_FILES[$index]['name'], PATHINFO_EXTENSION));
				$allowed_extensions = [
					'jpg',
					'jpeg',
					'png',
					'gif',
				];
	
				if (!in_array($extension, $allowed_extensions)) {
					set_alert('warning', 'Image extension not allowed.');
	
					return false;
				}
	
				// Setup our new file path
				$filename    = "email_attachment_{$attachment}_".time()."." . $extension;
				$newFilePath = $path . $filename;
				_maybe_create_upload_path($path);
				// Upload the file into the company uploads dir
				if (move_uploaded_file($tmpFilePath, $newFilePath)) {
					update_option($index, $filename);
					$success = true;
				}
			}   
		}
	}
    return $success;
}

function bd_greetings_triggers_get()
{
	$triggers = [];
	$customer_merge_fields = [
		'{contact_firstname}',
		'{contact_lastname}',
		'{client_company}',
	];
	$triggers[BD_GREETINGS_SMS_TRIGGER_CLIENT_BDAY] = [
								'merge_fields' => $customer_merge_fields,
								'label' => _l('bd_greetings_label_sms_bday'),
								'info'  => _l('bd_greetings_info_sms_bday'),
	];
	$triggers[BD_GREETINGS_SMS_TRIGGER_CLIENT_ANNIVERSARY] = [
								'merge_fields' => $customer_merge_fields,
								'label' => _l('bd_greetings_label_sms_anniversary'),
								'info'  => _l('bd_greetings_info_sms_anniversary'),
	];
	foreach($triggers as $trigger_id => $triger)
	{
		$triggers[$trigger_id]['value'] = get_instance()->app_sms->get_trigger_value($trigger_id);
	}
	return $triggers;
	
}

function bd_greetings_send_greetings()
{
	$send_sms 	= intval(get_option(BD_GREETINGS_MODULE_NAME.'_send_sms'));
	$send_email = intval(get_option(BD_GREETINGS_MODULE_NAME.'_send_email'));
	$field_bday = get_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_bday');
	$field_anniversary = get_option(BD_GREETINGS_MODULE_NAME.'_contact_cust_field_anniversary');
	if((!$send_sms && !$send_email) || ($field_bday == '' && $field_anniversary == ''))//nothing set
		return;
	$fieldids = [];
	if($send_sms || $send_email){
		if($field_bday != '') 			$fieldids[] = $field_bday;
		if($field_anniversary != '') 	$fieldids[] = $field_anniversary;
	}		 
	$CI = &get_instance();
	$CI->db->select('c.*,cf.value,cf.fieldid');
    $CI->db->where('cf.fieldto','contacts');
    $CI->db->where('DATE_FORMAT(value,"%m-%d") = "'.date('m-d').'"');
    $CI->db->where_in('cf.fieldid',$fieldids);
	$CI->db->join(db_prefix() . 'contacts as c','`c`.`id` = `cf`.`relid`');
    $contacts = $CI->db->get(db_prefix() . 'customfieldsvalues as cf')->result_array();
    $notified_users = [];
    $merge_fields = '';

    foreach ($contacts as $contact) {

	   	if($contact['fieldid']==$field_bday){
	   		$template_name = 'bd_greetings_cron_birthday_wish_to_contact';
			$trigger_name = BD_GREETINGS_SMS_TRIGGER_CLIENT_BDAY;
			$attachment = get_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_bday');
		}elseif($contact['fieldid']==$field_anniversary){	
			$template_name = 'bd_greetings_cron_anniversary_wish_to_contact';
			$trigger_name = BD_GREETINGS_SMS_TRIGGER_CLIENT_ANNIVERSARY;
			$attachment = get_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_anniversary');
		}	
			
       	$template = mail_template($template_name, 'bd_greetings', array_to_object($contact));
		if($attachment!='')
		{
			$template->add_attachment([
				'attachment'=> BD_GREETINGS_UPLOAD_PATH.$attachment,
				'filename'  => $attachment,
				'read'		=>true,
			]);
		}		

		$merge_fields =  $template->get_merge_fields();
		
		if($send_email && $contact['email']!=''){
			$template->send();
		}	
		if ($send_sms && $contact['phonenumber']!='') {
			$CI->app_sms->trigger($trigger_name, $contact['phonenumber'], $merge_fields);
		}
    }
	update_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_last_run',date('Y-m-d'));
}