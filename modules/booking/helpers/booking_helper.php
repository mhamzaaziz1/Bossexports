<?php defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('after_email_templates', 'add_booking_email_templates');

if (!function_exists('add_booking_email_templates')) {
    /**
     * Init booking email templates and assign languages
     * @return void
     */
    function add_booking_email_templates()
    {
        $CI = &get_instance();

        $data['booking_templates'] = $CI->emails_model->get(['type' => 'booking', 'language' => 'english']);

        $CI->load->view('booking/email_templates', $data);
    }
}