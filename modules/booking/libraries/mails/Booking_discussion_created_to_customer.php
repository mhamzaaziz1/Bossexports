<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Booking_discussion_created_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $booking;

    protected $contact;

    protected $additional_data;

    public $slug = 'new-booking-discussion-created-to-customer';

    public $rel_type = 'booking';

    public function __construct($booking, $contact, $additional_data)
    {
        parent::__construct();
        $this->booking         = $booking;
        $this->contact         = $contact;
        $this->additional_data = $additional_data;
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->booking->id)
        ->set_merge_fields('client_merge_fields', $this->booking->clientid, $this->contact['id'])
        ->set_merge_fields('bookings_merge_fields', $this->booking->id, $this->additional_data);
    }
}
