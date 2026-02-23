<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Custom_pdf extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('custom_pdf/custom_pdf');
    }

    public function settings($type = 'proposals')
    {
        if (!is_admin()) {
            access_denied('custom_pdf');
        }

        $data['pdf_type'] = $type;
        $data['title']    = _l('custom_pdf');
        $this->load->view('custom_pdf/settings/index', $data);
    }

    public function store()
    {
        if (!is_admin()) {
            access_denied('custom_pdf');
        }

        $settings = $this->input->post('settings');

        if ($settings) {
            foreach ($settings as $type => $data) {
                
                // Get existing settings to merge properly
                $saved_settings = get_option($type . '_pdf_settings');
                $current_settings = $saved_settings ? json_decode($saved_settings, true) : [];
                
                // Initializing default structure if empty
                if (!is_array($current_settings)) {
                    $current_settings = [];
                }

                // Update text/number fields
                foreach ($data as $section => $fields) {
                    if (!isset($current_settings[$section])) {
                        $current_settings[$section] = [];
                    }
                    foreach ($fields as $key => $value) {
                         $current_settings[$section][$key] = $value;
                    }
                }

                // Uploads
                if (isset($_FILES['settings']['name'][$type])) {
                    foreach ($_FILES['settings']['name'][$type] as $section => $files) {
                        foreach ($files as $key => $filename) {
                            if (!empty($filename)) {
                                $upload_data = $this->_handle_upload($type, $section, $key);
                                if ($upload_data) {
                                    $current_settings[$section][$key] = $upload_data['file_name'];
                                }
                            }
                        }
                    }
                }

                update_option($type . '_pdf_settings', json_encode($current_settings));
            }
        }

        set_alert('success', _l('settings_updated'));
        
        // Redirect to the last modified type or default
        $redirect_type = $settings ? key($settings) : 'proposals';
        redirect(admin_url('custom_pdf/settings/' . $redirect_type));
    }

    public function remove_pdf_image($type, $section)
    {
        if (!is_admin()) {
            access_denied('custom_pdf');
        }

        $settings = json_decode(get_option($type . '_pdf_settings'), true);
        
        if (isset($settings[$section]['image'])) {
            $filename = $settings[$section]['image'];
            $path = FCPATH . 'uploads/custom_pdf/' . $type . '/' . $filename;
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            unset($settings[$section]['image']);
            update_option($type . '_pdf_settings', json_encode($settings));
        }

        set_alert('success', _l('deleted', _l('image')));
        redirect(admin_url('custom_pdf/settings/' . $type));
    }
    
    private function _handle_upload($type, $section, $key)
    {
        $path = FCPATH . 'uploads/custom_pdf/' . $type . '/';
        
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // We need to simulate $_FILES array for the library
        $file_index = 'custom_pdf_upload_' . $type . '_' . $section . '_' . $key;
        
        $_FILES[$file_index] = [
            'name'     => $_FILES['settings']['name'][$type][$section][$key],
            'type'     => $_FILES['settings']['type'][$type][$section][$key],
            'tmp_name' => $_FILES['settings']['tmp_name'][$type][$section][$key],
            'error'    => $_FILES['settings']['error'][$type][$section][$key],
            'size'     => $_FILES['settings']['size'][$type][$section][$key],
        ];

        $config['upload_path']   = $path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['encrypt_name']  = true;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload($file_index)) {
            return $this->upload->data();
        } else {
            return false;
        }
    }
}
