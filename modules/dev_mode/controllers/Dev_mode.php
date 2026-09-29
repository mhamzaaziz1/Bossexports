<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dev_mode extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!has_permission('dev_mode', '', 'view')) {
            access_denied('Dev Mode');
        }
    }

    public function switch($mode)
    {
        if ($mode != 'production' && $mode != 'development') {
            redirect(admin_url());
        }

        $currentFile = file_get_contents(FCPATH . 'index.php');
        
        // Search for the ENVIRONMENT definition
        // We use a regex to be more robust in case of extra spaces
        $pattern = "/define\('ENVIRONMENT', '(.*?)'\);/";
        $replacement = "define('ENVIRONMENT', '" . $mode . "');";
        
        $newPage = preg_replace($pattern, $replacement, $currentFile);

        if (file_put_contents(FCPATH . 'index.php', $newPage)) {
            set_alert('success', 'Environment switched to ' . ucfirst($mode));
        } else {
            set_alert('danger', 'Failed to update index.php. Check file permissions.');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url());
    }
}
