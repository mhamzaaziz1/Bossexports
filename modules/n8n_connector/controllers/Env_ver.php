<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController {

    protected $bn_lcb;

    public function __construct() {
        parent::__construct();
        $this->load->library('n8n_connector/n8n_connector_aeiou');
    }

    public function index() {
        show_404();
    }

    public function check_update() {
        $module = $this->app_modules->get(N8N_CONNECTOR_MODULE);
        $postData = $this->input->post();
        if (empty($postData)) {
            $this->n8n_connector_aeiou->checkUpdate($module);
            return;
        }
        $this->n8n_connector_aeiou->downloadUpdate($module, $postData);
    }

    public function check_license() {
      return true;

      
    }

}
