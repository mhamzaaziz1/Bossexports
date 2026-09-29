<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Booking_marked_as_finished_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $booking_id;

    protected $client_id;

    protected $contact;

    public $slug = 'booking-finished-to-customer';

    public $rel_type = 'booking';

    public function __construct($booking_id, $client_id, $contact)
    {
        parent::__construct();
        $this->booking_id = $booking_id;
        $this->client_id  = $client_id;
        $this->contact    = $contact;
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->booking_id)
        ->set_merge_fields('client_merge_fields', $this->client_id, $this->contact['id'])
        ->set_merge_fields('bookings_merge_fields', $this->booking_id, [
                    'customer_template' => true,
                ]);
    }
}
