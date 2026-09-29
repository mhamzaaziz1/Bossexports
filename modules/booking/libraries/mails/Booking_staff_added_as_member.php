<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Booking_staff_added_as_member extends App_mail_template
{
    protected $for = 'staff';

    protected $booking_id;

    protected $client_id;

    protected $staff;

    public $slug = 'staff-added-as-booking-member';

    public $rel_type = 'booking';

    public function __construct($staff, $booking_id, $client_id)
    {
        parent::__construct();

        $this->staff      = $staff;
        $this->booking_id = $booking_id;
        $this->client_id  = $client_id;
    }

    public function build()
    {
        $this->to($this->staff['email'])
        ->set_rel_id($this->booking_id)
        ->set_merge_fields('client_merge_fields', $this->client_id)
        ->set_merge_fields('staff_merge_fields', $this->staff['staff_id'])
        ->set_merge_fields('bookings_merge_fields', $this->booking_id);
    }
}
