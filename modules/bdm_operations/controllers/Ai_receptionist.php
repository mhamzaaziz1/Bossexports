<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ai_receptionist extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * View the Configuration Guide
     */
    public function config_guide()
    {
        if (!has_permission('settings', '', 'view')) {
            access_denied('settings');
        }
        $data['title'] = 'AI Receptionist Configuration';
        $this->load->view('ai_receptionist_config', $data);
    }
}
