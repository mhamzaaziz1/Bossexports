<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote_comment_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $quote;

    public $slug = 'quote-comment-to-client';

    public $rel_type = 'quote';

    public function __construct($quote)
    {
        parent::__construct();

        $this->quote = $quote;

        // For SMS
        $this->set_merge_fields('quotes_merge_fields', $this->quote->id);
    }

    public function build()
    {
        $this->to($this->quote->email)
        ->set_rel_id($this->quote->id);
    }
}
