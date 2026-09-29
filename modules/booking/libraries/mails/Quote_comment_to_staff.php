<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote_comment_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $quote_id;

    protected $staff_email;

    public $slug = 'quote-comment-to-admin';

    public $rel_type = 'quote';

    public function __construct($quote_id, $staff_email)
    {
        parent::__construct();

        $this->quote_id = $quote_id;
        $this->staff_email = $staff_email;

        // For SMS
        $this->set_merge_fields('quotes_merge_fields', $this->quote_id);
    }

    public function build()
    {
        $this->to($this->staff_email)
        ->set_rel_id($this->quote_id);
    }
}
