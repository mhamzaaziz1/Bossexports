<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Genz_theme extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
    }

    /**
     * Save theme settings
     * @return redirect
     */
    public function save_settings()
    {
        if (!has_permission('settings', '', 'edit')) {
            access_denied('settings');
        }

        // Validate form input
        $this->form_validation->set_rules('genz_theme_accent_color', 'Accent Color', 'required');
        $this->form_validation->set_rules('genz_theme_secondary_color', 'Secondary Color', 'required');

        if ($this->form_validation->run() === false) {
            set_alert('danger', _l('validation_error'));
            redirect(admin_url('settings?group=perfex-theme-dark-settings'));
        }

        // Get checkbox values (0 if unchecked, 1 if checked)
        $genz_theme_staff = $this->input->post('genz_theme_staff') ? 1 : 0;
        $genz_theme_customers = $this->input->post('genz_theme_customers') ? 1 : 0;
        $genz_theme_dark_mode = $this->input->post('genz_theme_dark_mode') ? 1 : 0;
        $genz_theme_animations = $this->input->post('genz_theme_animations') ? 1 : 0;

        // Get color values
        $genz_theme_accent_color = $this->input->post('genz_theme_accent_color');
        $genz_theme_secondary_color = $this->input->post('genz_theme_secondary_color');

        // Save settings
        update_option('genz_theme_staff', $genz_theme_staff);
        update_option('genz_theme_customers', $genz_theme_customers);
        update_option('genz_theme_dark_mode', $genz_theme_dark_mode);
        update_option('genz_theme_animations', $genz_theme_animations);
        update_option('genz_theme_accent_color', $genz_theme_accent_color);
        update_option('genz_theme_secondary_color', $genz_theme_secondary_color);

        set_alert('success', _l('settings_updated'));
        redirect(admin_url('settings?group=genz-theme-settings'));
    }

    /**
     * Save dark mode preference for admin user
     * @return json
     */
    public function save_dark_mode_preference()
    {
        // Check if user is logged in
        if (!is_staff_logged_in()) {
            ajax_access_denied();
        }

        $dark_mode = $this->input->post('dark_mode') ? 1 : 0;
        $staff_id = get_staff_user_id();

        // Save preference to staff meta
        $this->db->where('staffid', $staff_id);
        $this->db->where('meta_key', 'genz_dark_mode');
        $exists = $this->db->get(db_prefix() . 'staff_meta')->row();

        if ($exists) {
            $this->db->where('staffid', $staff_id);
            $this->db->where('meta_key', 'genz_dark_mode');
            $this->db->update(db_prefix() . 'staff_meta', ['meta_value' => $dark_mode]);
        } else {
            $this->db->insert(db_prefix() . 'staff_meta', [
                'staffid' => $staff_id,
                'meta_key' => 'genz_dark_mode',
                'meta_value' => $dark_mode
            ]);
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Save dark mode preference for client
     * @return json
     */
    public function save_client_dark_mode_preference()
    {
        // Check if client is logged in
        if (!is_client_logged_in()) {
            ajax_access_denied();
        }

        $dark_mode = $this->input->post('dark_mode') ? 1 : 0;
        $contact_id = get_contact_user_id();

        // Save preference to contacts meta
        $this->db->where('contact_id', $contact_id);
        $this->db->where('meta_key', 'genz_dark_mode');
        $exists = $this->db->get(db_prefix() . 'contacts_meta')->row();

        if ($exists) {
            $this->db->where('contact_id', $contact_id);
            $this->db->where('meta_key', 'genz_dark_mode');
            $this->db->update(db_prefix() . 'contacts_meta', ['meta_value' => $dark_mode]);
        } else {
            $this->db->insert(db_prefix() . 'contacts_meta', [
                'contact_id' => $contact_id,
                'meta_key' => 'genz_dark_mode',
                'meta_value' => $dark_mode
            ]);
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Get user's dark mode preference
     * @return boolean
     */
    public function get_dark_mode_preference()
    {
        // Default to system setting
        $dark_mode = get_option('genz_theme_dark_mode') == '1';

        // If staff is logged in, get their preference
        if (is_staff_logged_in()) {
            $staff_id = get_staff_user_id();
            $this->db->where('staffid', $staff_id);
            $this->db->where('meta_key', 'genz_dark_mode');
            $meta = $this->db->get(db_prefix() . 'staff_meta')->row();

            if ($meta) {
                $dark_mode = $meta->meta_value == '1';
            }
        }
        // If client is logged in, get their preference
        elseif (is_client_logged_in()) {
            $contact_id = get_contact_user_id();
            $this->db->where('contact_id', $contact_id);
            $this->db->where('meta_key', 'genz_dark_mode');
            $meta = $this->db->get(db_prefix() . 'contacts_meta')->row();

            if ($meta) {
                $dark_mode = $meta->meta_value == '1';
            }
        }

        return $dark_mode;
    }
}