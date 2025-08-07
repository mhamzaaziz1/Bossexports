<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Genz_theme_clients extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Save dark mode preference for client
     * @return json
     */
    public function save_dark_mode_preference()
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
     * Get client's dark mode preference
     * @return boolean
     */
    public function get_dark_mode_preference()
    {
        // Default to system setting
        $dark_mode = get_option('genz_theme_dark_mode') == '1';

        // If client is logged in, get their preference
        if (is_client_logged_in()) {
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