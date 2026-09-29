<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Become Digital Client Wishes Message
Description: Module will allow to send greetings SMS and emails to Client on their Birthday and Anniversary.
Author: <a href="https://becomedigital.in" target="_blank">Become Digital (An Initiative of DSU INFOTECH&reg;)</a>
Version: 1.0.0
Requires at least: 1.0.*
*/

define('BD_GREETINGS_MODULE_NAME', 'bd_greetings');

define('BD_GREETINGS_UPLOAD_PATH',APP_MODULES_PATH . BD_GREETINGS_MODULE_NAME.'/uploads/');
define('BD_GREETINGS_SMS_TRIGGER_CLIENT_BDAY', BD_GREETINGS_MODULE_NAME.'_client_bday');
define('BD_GREETINGS_SMS_TRIGGER_CLIENT_ANNIVERSARY', BD_GREETINGS_MODULE_NAME.'_client_anniversary');

$CI = &get_instance();

hooks()->add_filter('module_'.BD_GREETINGS_MODULE_NAME.'_action_links', 'module_bd_greetings_action_links');
hooks()->add_action('admin_init', 'bd_greetings_admin_init_hook');
hooks()->add_filter('sms_gateway_available_triggers', 'bd_greetings_hook_sms_gateway_available_triggers');
hooks()->add_filter('before_settings_updated','bd_greetings_hook_before_settings_updated');
hooks()->add_action('after_cron_run', 'bd_greetings_hook_send_email_templates');
hooks()->add_filter('other_merge_fields_available_for', 'bd_greetings_hook_register_other_merge_fields');
hooks()->add_action('after_email_templates', 'bd_greetings_hook_add_email_templates');
register_merge_fields('bd_greetings/merge_fields/bd_greetings_merge_fields');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_bd_greetings_action_links($actions)
{
	$actions[] = '<a href="' . admin_url('settings?group=bd_greetings_settings') . '">' . _l('settings') . '</a>';
	return $actions;
}

/**
* Load the module helper
*/
$CI->load->helper(BD_GREETINGS_MODULE_NAME . '/bd_greetings');

/**
* Register activation module hook
*/
register_activation_hook(BD_GREETINGS_MODULE_NAME, 'bd_greetings_activation_hook');

function bd_greetings_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(BD_GREETINGS_MODULE_NAME, [BD_GREETINGS_MODULE_NAME]);

/**
*	Admin Init Hook for module
*/
function bd_greetings_admin_init_hook()
{
	/**  Add Tab In Settings Tab of Setup **/
	if (is_admin()) {
		get_instance()->app_tabs->add_settings_tab('bd_greetings_settings', [
			'name'     => _l('bd_greetings_settings'),
			'view'     => 'bd_greetings/bd_greetings_settings',
			'position' => 100,
		]);
	}
}
/** Hook for SMS triggers**/
function bd_greetings_hook_sms_gateway_available_triggers($triggers)
{
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
	return $triggers;
}
/** Hook to upload image befoe settings updated**/
function bd_greetings_hook_before_settings_updated($data)
{
	$attachment_uploaded     = (handle_bd_greetings_attachment_upload() ? true : false);
	if(!$attachment_uploaded)
	{
		return false;
	}
	else	
		return $data;
}

/**
 * Hook to Register extra email templates
 */
function bd_greetings_hook_register_other_merge_fields($for)
{
    $for[] = 'bd_greetings';
    return $for;
}

/**
 * Hook to add extra email templates
 */
function bd_greetings_hook_add_email_templates()
{
	$CI = &get_instance();
	$data['bd_greetings_templates'] = $CI->emails_model->get(['type' => 'bd_greetings', 'language' => 'english']);
	$CI->load->view('bd_greetings/email_templates', $data);
}

/**
 * Hook to Register cronjob to send greeetings via sms and email templates 
 *
 * @return void
 */
function bd_greetings_hook_send_email_templates($manually)
{
    $hour_auto_operations = get_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_hour');
	$last_run = get_option(BD_GREETINGS_MODULE_NAME.'_trigger_auto_operations_last_run');
	if ($hour_auto_operations == '') {
		$hour_auto_operations = 9;
	}
	$hour_auto_operations = intval($hour_auto_operations);
	$hour_now                     = date('G');
	if ($hour_now != $hour_auto_operations && $manually === false && $last_run==date('Y-m-d')) {
		return;
	}
	bd_greetings_send_greetings();
	return;
}