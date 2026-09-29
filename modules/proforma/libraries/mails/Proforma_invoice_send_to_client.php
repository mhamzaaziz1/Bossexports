<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proforma_invoice_send_to_client extends App_mail_template
{
    protected $for = 'proforma';

    protected $proforma;

    protected $contact_id;

    public $slug = 'proforma_invoice_send_to_client';

    public $rel_type = 'proforma';

    protected $custom_message = '';

    public function __construct($proforma, $contact_id, $cc = '')
    {
        parent::__construct();

        $this->proforma = $proforma;
        $this->contact_id = $contact_id;
        $this->cc = $cc;
    }

    public function set_custom_message($message) {
        $this->custom_message = $message;
        return $this;
    }

    public function build()
    {
        $this->ci->load->model('proforma/proforma_model');
        $proforma = $this->ci->proforma_model->get($this->proforma);

        $this->to($this->ci->clients_model->get_contact($this->contact_id)->email)
        ->set_rel_id($proforma->id)
        ->set_merge_fields('proforma_merge_fields', $this->proforma, $this->contact_id);
    }

    public function prepare($email = null, $template = null, $params = [])
    {
        $template = parent::prepare($email, $template, $params);

        if ($this->custom_message != '') {
            $template->message = $this->custom_message;
        }

        return $template;
    }
}
