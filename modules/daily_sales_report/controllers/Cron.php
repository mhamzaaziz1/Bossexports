<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
    public function index($key = '')
    {
        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $this->load->model('daily_sales_report/daily_sales_report_model');
        $reports = $this->daily_sales_report_model->get_reports(['active' => 1]);
        
        $count = 0;
        foreach ($reports as $report) {
            if ($this->daily_sales_report_model->send_cron_report($report['id'])) {
                $count++;
            }
        }
        
        echo "Daily Sales Report Cron Executed. Sent {$count} reports.";
    }
}
