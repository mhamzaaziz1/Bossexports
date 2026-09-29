<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tour_assigned_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $staff_email;

    protected $assignee_id;

    protected $tour_id;

    public $slug = 'tour-assigned';

    public $rel_type = 'tour';

    public function __construct($staff_email, $assignee_id, $tour_id)
    {
        parent::__construct();

        $this->staff_email = $staff_email;
        $this->assignee_id    = $assignee_id;
        $this->tour_id     = $tour_id;
    }

    public function build()
    {

        $this->to($this->staff_email)
        ->set_rel_id($this->tour_id)
        ->set_staff_id($this->assignee_id)
        ->set_merge_fields('staff_merge_fields', $this->assignee_id)
        ->set_merge_fields('tours_merge_fields', $this->tour_id);
    }
}
