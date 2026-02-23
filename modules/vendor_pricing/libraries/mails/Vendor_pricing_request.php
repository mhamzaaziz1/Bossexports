<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_pricing_request extends App_mail_template
{
    protected $for = 'contact';
    protected $data;
    public $slug = 'vendor-pricing-request';

    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
        $this->set_merge_fields('vendor_pricing_merge_fields', clone $this->data);
    }

    public function build()
    {
        $this->to($this->data->receiver);
    }
}
