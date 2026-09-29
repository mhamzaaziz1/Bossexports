<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotes_public extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('quotes_model');
    }
    
    public function index()
    {
        $where = 'rel_id =' . get_client_user_id() . ' AND rel_type ="customer"';

        if (get_option('exclude_quote_from_client_area_with_draft_status') == 1) {
            $where .= ' AND status != 6';
        }
        
        $this->db->select(db_prefix() . 'contacts.email');
        $this->db->where('userid', get_client_user_id());
        $email = $this->db->get(db_prefix() . 'contacts')->row()->email;

        $client = $this->clients_model->get(get_client_user_id());

        if(!isset($client->leadid)){
            redirect(site_url());
        }

        if (!is_null($email)) {
            $where .= " OR (email='" . $email . "' AND status !=6)";
        }
        
        $data['quotes'] = $this->quotes_model->get('', $where);
        $data['title']     = _l('quotes');
        $this->data($data);
        $this->view('clients/quotes/quotes');
        $this->layout();
    }
}
