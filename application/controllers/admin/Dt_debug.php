<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dt_debug extends AdminController
{
    public function index()
    {
        $data = [];
        $data['csrf_enabled'] = $this->config->item('csrf_protection');
        $data['csrf_token_name'] = $this->security->get_csrf_token_name();
        $data['csrf_hash'] = $this->security->get_csrf_hash();
        
        $hooks = $this->hooks->hooks;
        $data['app_admin_head_hooks'] = isset($hooks['app_admin_head']) ? $hooks['app_admin_head'] : 'Not found';

        echo '<pre>';
        print_r($data);
        echo '</pre>';
        
        // Also check if we can generate the same CSRF data that should be in the head
        echo '<h3>CSRF for AJAX:</h3>';
        echo '<pre>';
        print_r(get_csrf_for_ajax());
        echo '</pre>';

        // Check assets helper function availability
        echo '<h3>Function check:</h3>';
        echo 'app_compile_scripts exists: ' . (function_exists('app_compile_scripts') ? 'Yes' : 'No') . '<br>';
        echo 'csrf_jquery_token exists: ' . (function_exists('csrf_jquery_token') ? 'Yes' : 'No') . '<br>';
    }
}
