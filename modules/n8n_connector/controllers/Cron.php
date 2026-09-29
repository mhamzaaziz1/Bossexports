<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * n8n Connector Cron Controller
 * Processes background webhook queue
 */
class Cron extends App_Controller
{
    public function webhookcron_status()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        update_option('webhook_cron_status', $data['status'] ?? '');
        update_option('webhook_cron_type', $data['type'] ?? '');
        update_option('webhook_cron_last_status_update', date('Y-m-d H:i:s'));
        $response = [
            'success'  => true,
            'message' => 'Webhook cron status working successfully!',
            'status' => get_option('webhook_cron_status')
        ];

        echo json_encode($response);
    }
}