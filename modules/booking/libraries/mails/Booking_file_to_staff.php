<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Booking_file_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $booking;

    protected $staff;

    protected $additional_data;

    public $slug = 'new-booking-file-uploaded-to-staff';

    public $rel_type = 'booking';

    public function __construct($booking, $staff, $additional_data)
    {
        parent::__construct();
        $this->booking         = $booking;
        $this->staff           = $staff;
        $this->additional_data = $additional_data;
    }

    public function build()
    {
        $this->to($this->staff['email'])
        ->set_rel_id($this->booking->id)
        ->set_merge_fields('client_merge_fields', $this->booking->clientid)
        ->set_merge_fields('staff_merge_fields', $this->staff['staff_id'])
        ->set_merge_fields('bookings_merge_fields', $this->booking->id, $this->additional_data);
    }
}
