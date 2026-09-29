<?php defined('BASEPATH') or exit('No direct script access allowed');

class Bd_greetings_cron_birthday_wish_to_contact extends App_mail_template
{
    protected $for = 'contact';
    protected $contact;
    public $slug = 'bd-greetings-cron-birthday-wish-to-contact';

    public function __construct($contact)
    {
        parent::__construct();
        $this->contact = $contact;
        $this->set_merge_fields('bd_greetings_merge_fields', $this->contact->id);
    }
    public function build()
    {
        $this->to($this->contact->email);
    }
}