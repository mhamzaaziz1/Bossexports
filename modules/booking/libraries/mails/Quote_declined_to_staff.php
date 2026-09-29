<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote_declined_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $quote;

    protected $staff_email;

    public $slug = 'quote-client-declined';

    public $rel_type = 'quote';

    public function __construct($quote, $staff_email)
    {
        parent::__construct();

        $this->quote    = $quote;
        $this->staff_email = $staff_email;
    }

    public function build()
    {
        $this->to($this->staff_email)
        ->set_rel_id($this->quote->id)
        ->set_merge_fields('quotes_merge_fields', $this->quote->id);
    }
}
