<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Booking_data_pdf extends App_pdf
{
    protected $booking_id;

    public function __construct($booking_id)
    {
        parent::__construct();

        $this->booking_id = $booking_id;
    }

    public function prepare()
    {
        $booking = $this->ci->bookings_model->get($this->booking_id);
        $this->SetTitle($booking->name);
        $members                = $this->ci->bookings_model->get_booking_members($booking->id);
        $booking->currency_data = $this->ci->bookings_model->get_currency($booking->id);

        // Add <br /> tag and wrap over div element every image to prevent overlaping over text
        $booking->description = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '<br><br><div>$1</div><br><br>', $booking->description);

        $data['booking']    = $booking;
        $data['milestones'] = $this->ci->bookings_model->get_milestones($booking->id);
        $data['timesheets'] = $this->ci->bookings_model->get_timesheets($booking->id);

        $data['tours']             = $this->ci->bookings_model->get_tours($booking->id, [], false);
        $data['total_logged_time'] = seconds_to_time_format($this->ci->bookings_model->total_logged_time($booking->id));
        if ($booking->deadline) {
            $data['total_days'] = round((human_to_unix($booking->deadline . ' 00:00') - human_to_unix($booking->start_date . ' 00:00')) / 3600 / 24);
        } else {
            $data['total_days'] = '/';
        }
        $data['total_members'] = count($members);
        $data['total_tickets'] = total_rows(db_prefix().'tickets', [
                'booking_id' => $booking->id,
            ]);
        $data['total_invoices'] = total_rows(db_prefix().'invoices', [
                'booking_id' => $booking->id,
            ]);

        $this->ci->load->model('invoices_model');

        $data['invoices_total_data'] = $this->ci->invoices_model->get_invoices_total([
                'currency'   => $booking->currency_data->id,
                'booking_id' => $booking->id,
            ]);

        $data['total_milestones']     = count($data['milestones']);
        $data['total_files_attached'] = total_rows(db_prefix().'booking_files', [
                'booking_id' => $booking->id,
            ]);
        $data['total_discussion'] = total_rows(db_prefix().'bookingdiscussions', [
                'booking_id' => $booking->id,
            ]);
        $data['members'] = $members;

        $this->set_view_vars($data);

        return $this->build();
    }

    protected function type()
    {
        return 'booking-data';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/admin/bookings/my_export_data_pdf.php';
        $actualPath = module_views_path(BOOKING_MODULE_NAME) . 'admin/bookings/export_data_pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }

    public function get_format_array()
    {
        return  [
            'orientation' => 'L',
            'format'      => 'landscape',
        ];
    }
}
