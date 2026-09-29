<?php
defined('BASEPATH') or exit('No direct script access allowed');
function get_complete_reminder($id)
{
	$CI = &get_instance();
	if ($id) {
		$CI->db->where('id', $id);
		$data = $CI->db->select('is_complete')->from(db_prefix() . 'reminders')->get()->row();
	}
	if ($data) {
		return $data->is_complete;
	}
	return '';
}
function format_reminder_status($status, $classes = '', $label = true)
{
	$id = $status;
	if ($status == 1) {
		$status      = _l('reminder_run');
		$label_class = 'success';
	} elseif ($status == 0) {
		$status      = _l('complete');
		$label_class = 'danger';
	}
	if ($label == true) {
		return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status proposal-status-' . $id . '">' . $status . '</span>';
	}
	return $status;
}
function reminder_send_mail_template()
{ 
	$params = func_get_args();
	return reminder_mail_template(...$params)->send();
}
function reminder_mail_template($class)
{
	$CI = &get_instance();
	$params = func_get_args();
	unset($params[0]);
	$params = array_values($params);
	$path = get_reminder_mail_template_path($class, $params);
	
	if (!file_exists($path)) {
		if (!defined('CRON')) {
			show_error('Mail Class Does Not Exists [' . $path . ']');
		} else {
			return false;
		}
	}
	if (!class_exists($class, false)) {
		include_once($path);
	}
	$instance = new $class(...$params);
	return $instance;
}
function get_reminder_mail_template_path($class, &$params)
{
	$CI  = &get_instance();
	$dir = APP_MODULES_PATH . 'reminder/libraries/mails/';
	if (isset($params[0]) && is_string($params[0]) && is_dir(module_dir_path($params[0]))) {
		$module = $CI->app_modules->get($params[0]);
		if ($module['activated'] === 1) {
			$dir = module_libs_path($params[0]) . 'mails/';
		}
		unset($params[0]);
		$params = array_values($params);
	}
	return $dir . ucfirst($class) . '.php';
}
function send_sms_reminder($to, $message){
	$account_sid = get_option('sms_twilio_account_sid');
	$auth_token = get_option('sms_twilio_auth_token');
	$twilio_number = get_option('sms_twilio_phone_number');
	$client = new Twilio\Rest\Client($account_sid, $auth_token);
	$sms_ids = [];
	$errors = [];
	try {
		$message = $client->messages->create($to,array('from' => $twilio_number,'body' => $message));
		$sms_ids[] = $message->sid;
	} catch (\Throwable $e) {
		$errors[] = [
			'phone_number' => $to,
			'message' => $e->getMessage()
		];
	}
	return json_encode([
		'success' => true,
		'sent' => $sms_ids,
		'errors'  => $errors
	]);
}
function is_email_template_active_reminder($slug)
{
	return total_rows(db_prefix() . 'emailtemplates', ['slug' => $slug, 'active' => 1]) > 0;
}
function get_staff_phone_number($staff_id){
	$CI  = &get_instance();
	$data = null;
	if($staff_id){
		$CI->db->where('staffid', $staff_id);
		$staff = $CI->db->select('phonenumber')->from(db_prefix() . 'staff')->get()->row();
		if(isset($staff) && isset($staff->phonenumber)){
			$data = $staff->phonenumber;
		}
	}
	return $data;
}
