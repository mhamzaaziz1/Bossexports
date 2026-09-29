<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_recurring_recreated_to_contacts extends App_mail_template
{
    public $slug = 'appointment-recurring-recreated-to-contacts';
    /**
     * Relation ID, e.q. appointment id
     *
     * @var mixed
     */
    public $rel_id;
    /**
     * Relation ID, e.q. appointment
     *
     * @var mixed
     */
    public $rel_type;
    protected $for = 'contact';
    protected $appointment;
    /**
     * Relation ID, e.q. staff
     *
     * @var mixed
     */
    protected $staff;

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;
        $this->rel_id = $appointment->id;
        $this->rel_type = 'appointment';

        // For SMS and merge fields for email
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build()
    {
        $this->to($this->appointment->email);
    }
}
