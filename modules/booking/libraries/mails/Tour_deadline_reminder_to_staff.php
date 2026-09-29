<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tour_deadline_reminder_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $staff_email;

    protected $staffid;

    protected $tour_id;

    public $slug = 'tour-deadline-notification';

    public $rel_type = 'tour';

    public function __construct($staff_email, $staffid, $tour_id)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->staffid     = $staffid;
        $this->tour_id     = $tour_id;
    }

    public function build()
    {
        $this->to($this->staff_email)
        ->set_rel_id($this->tour_id)
        ->set_staff_id($this->staffid)
        ->set_merge_fields('staff_merge_fields', $this->staffid)
        ->set_merge_fields('tours_merge_fields', $this->tour_id);
    }
}
