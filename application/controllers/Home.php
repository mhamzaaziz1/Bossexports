<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends App_Controller
{
    public function index()
    {
        // Check login redirect setting
        if (get_option('default_login_page') == 'admin') {
            redirect(admin_url('authentication'));
        }
        
        // Default behavior - redirect to clients
        redirect(site_url('clients'));
    }
}
