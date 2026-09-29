<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fast_load extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('Fast Load Settings');
        }
    }

    public function settings()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            
            $minify_html = isset($data['fast_load_minify_html']) ? 1 : 0;
            $browser_cache = isset($data['fast_load_browser_cache']) ? 1 : 0;
            $db_cache = isset($data['fast_load_enable_db_cache']) ? 1 : 0;

            update_option('fast_load_minify_html', $minify_html);
            update_option('fast_load_browser_cache', $browser_cache);
            update_option('fast_load_enable_db_cache', $db_cache);

            // If db cache enabled, we might need to modify application/config/database.php programmatically
            // but for safety, we just use CI's $this->db->cache_on() for guest queries.
            
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('fast_load/settings'));
        }

        $data['title'] = 'Fast Load & Cache Settings';
        $this->load->view('settings', $data);
    }
    
    public function clear_cache()
    {
        // Clear CodeIgniter cache folder
        $CI = &get_instance();
        $CI->load->helper('file');
        
        $cache_path = APPPATH . 'cache/';
        $files = get_filenames($cache_path);
        $cleared = 0;
        
        if ($files) {
            foreach ($files as $file) {
                if ($file != 'index.html' && $file != '.htaccess') {
                    if (is_file($cache_path . $file)) {
                        unlink($cache_path . $file);
                        $cleared++;
                    }
                }
            }
        }
        
        // Also clear db queries cache if db cache is on
        $CI->db->cache_delete_all();

        set_alert('success', 'Cache Cleared Successfully ('.$cleared.' files removed)');
        redirect(admin_url('fast_load/settings'));
    }
}
