<?php defined('BASEPATH') or exit('No direct script access allowed');
class Bd_greetings extends AdminController
{
	public function __construct()
	{
		parent::__construct(); 
		if (!is_admin() && !has_permission('settings', '', 'view')) {
			access_denied(_l('settings'));
		}
	}
	
	public function remove_email_attachment($type='bday')
	{
        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $fileName = get_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_'.$type);

        $path = BD_GREETINGS_UPLOAD_PATH. $fileName;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option(BD_GREETINGS_MODULE_NAME.'_email_attachment_'.$type, '');
        redirect($_SERVER['HTTP_REFERER']);
	}
}