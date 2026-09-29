<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Payment Signature
Description: Adds signature and stamp to payment transaction PDF
Version: 1.0.0
Requires at least: 2.3.*
*/

define('PAYMENT_SIGNATURE_MODULE_NAME', 'payment_signature');

hooks()->add_action('admin_init', 'payment_signature_init_menu_items');
hooks()->add_filter('payment_pdf_build_path', 'payment_signature_pdf_path');
// hooks()->add_filter('before_settings_updated', 'payment_signature_handle_uploads'); // Removed duplicate/incorrect filter

function payment_signature_init_menu_items()
{
    $CI = &get_instance();
    
    $CI->app->add_settings_section_child('other', 'payment_signature', [
        'name'     => 'Payment Signature',
        'view'     => 'payment_signature/settings',
        'position' => 60,
    ]);
}

function payment_signature_pdf_path($path)
{
    if (get_option('payment_signature_enable') == 1) {
        return module_views_path(PAYMENT_SIGNATURE_MODULE_NAME, 'paymentpdf.php');
    }
    return $path;
}

function payment_signature_handle_uploads($data)
{
    // Check if we are saving payment_signature settings
    // The settings page sends the 'settings' array (optional) but mostly we check input post
    // But here we want to handle the file upload specifically.
    
    // We check $_FILES
    
    if (isset($_FILES['payment_signature_image']) && !empty($_FILES['payment_signature_image']['name'])) {
        $path = get_upload_path_by_type('company');
        $uploader_library = load_upload_library();
        
        // Upload Signature
        $config = [];
        $config['upload_path'] = $path;
        $config['allowed_types'] = 'jpg|jpeg|png|bmp';
        $config['overwrite'] = true;
        
        $uploader_library->initialize($config);
        
        if ($uploader_library->do_upload('payment_signature_image')) {
            $upload_data = $uploader_library->data();
            $filename = $upload_data['file_name'];
            
            // Delete old file if exists
            $old_image = get_option('payment_signature_image_path');
            if ($old_image && file_exists($path . '/' . $old_image)) {
                unlink($path . '/' . $old_image);
            }
            
            // We use a separate option to store the path/filename, or just add it to $data['settings']
            // But before_settings_updated is an action or filter?
            // Hooks::do_action('before_settings_updated', $data); -> It is an ACTION.
            // Wait, if it is an action, I cannot modify $data which is passed by value usually unless passed by ref.
            // But I can use update_option directly.
            
            // Actually, let's check core usage.
            // Settings controller lines 57 calls $this->settings_model->update($post_data);
            // Inside update: hooks()->do_action('before_settings_updated', $data);
            
            // So I can just update the option here.
            
            // However, the 'settings' array in POST might overwrite what I manually update if I am not careful?
            // No, the model iterates over the passed data. 
            // My upload handling is separate.
            
            // But wait, if I want to save the filename to the database, I should probably do it here.
            // We can resolve this by adding the filename to the $_POST['settings'] array? 
            // The hook is called with arguments.
            
            /*
             * core code:
             * hooks()->do_action('before_settings_updated', $data);
             * ...
             * foreach ($data['settings'] as $name => $val) { ... }
             */
             
            // Since it is an action, I can't modify $data unless it's an object or passed by reference. 
            // The hook definition in hooks helper is:
            // function do_action($tag, ...$args) { ... call_user_func_array ... }
            // So I cannot modify $data in the caller.
            
            // So I should call update_option directly.
            update_option('payment_signature_image_path', $filename);
        }
    }
    
    if (isset($_FILES['payment_stamp_image']) && !empty($_FILES['payment_stamp_image']['name'])) {
        $path = get_upload_path_by_type('company');
        $uploader_library = load_upload_library(); // Initialize again?
        // CI Upload library might need re-initialization or different config
        
        // Actually, load_upload_library() is not a standard helper?
        // Usually: $CI->load->library('upload');
        // Let's use CI instance.
        $CI = &get_instance();
        $CI->load->library('upload');
        
        $config = [];
        $config['upload_path'] = $path;
        $config['allowed_types'] = 'jpg|jpeg|png|bmp';
        $config['overwrite'] = false; // Maybe verify uniqueness
        // But for settings usually we might want to overwrite or keep unique
        
        $CI->upload->initialize($config);
        
        if ($CI->upload->do_upload('payment_stamp_image')) {
            $upload_data = $CI->upload->data();
            $filename = $upload_data['file_name'];
            
            $old_image = get_option('payment_stamp_image_path');
             if ($old_image && file_exists($path . '/' . $old_image)) {
                unlink($path . '/' . $old_image);
            }
            update_option('payment_stamp_image_path', $filename);
        }
    }
    
    return $data; // Filters should return data, actions do not.
    // Wait, I registered it as a filter: hooks()->add_filter(...);
    // Is 'before_settings_updated' a filter or action?
    // In Settings_model.php it is: hooks()->do_action('before_settings_updated', $data);
    // It is an ACTION. So add_filter is wrong if I want to properly hook as an action.
    // But add_filter and add_action are essentially the same int he hooks system, but semantics matter.
    // If it is an action, I cannot modify the data flow directly.
}

// Correcting the registration to add_action
hooks()->add_action('before_settings_updated', 'payment_signature_handle_uploads');

// Also register the options
hooks()->add_action('admin_init', 'payment_signature_add_options');

function payment_signature_add_options(){
    if (get_option('payment_signature_enable') === null) {
        add_option('payment_signature_enable', 0);
    }
    if (get_option('payment_signature_image_path') === null) {
        add_option('payment_signature_image_path', '');
    }
    if (get_option('payment_stamp_image_path') === null) {
        add_option('payment_stamp_image_path', '');
    }
         if (get_option('payment_signature_text') === null) {
        add_option('payment_signature_text', '');
    }
}

function load_upload_library()
{
    $CI = &get_instance();
    $CI->load->library('upload');
    return $CI->upload;
}
