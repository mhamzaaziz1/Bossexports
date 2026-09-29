<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tour_new_attachment_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $contact_email;

    protected $client_id;

    protected $contact_id;

    protected $tour_id;

    public $slug = 'tour-added-attachment-to-contacts';

    public $rel_type = 'tour';

    public function __construct($contact_email, $client_id, $contact_id, $tour_id)
    {
        parent::__construct();

        $this->contact_email = $contact_email;
        $this->client_id     = $client_id;
        $this->contact_id    = $contact_id;
        $this->tour_id       = $tour_id;
    }

    public function build()
    {
        $this->to($this->contact_email)
        ->set_rel_id($this->tour_id)
        ->set_merge_fields('client_merge_fields', $this->client_id, $this->contact_id)
        ->set_merge_fields('tours_merge_fields', $this->tour_id, true);
    }
}
