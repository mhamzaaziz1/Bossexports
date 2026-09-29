<?php

defined('BASEPATH') or exit('No direct script access allowed');

class poly_utilities_ajax_response_helper
{
    public static function response_success($message)
    {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => $message
        ];
        echo json_encode($response);
        exit;
    }
    public static function response_not_found($message)
    {
        $response = [
            'status' => 'error',
            'code' => 404,
            'message' => $message
        ];
        echo json_encode($response);
        exit;
    }
    public static function response_data_not_saved($message)
    {
        $response = [
            'status' => 'error',
            'code' => 500,
            'message' => $message
        ];
        echo json_encode($response);
        exit;
    }
    public static function response_data_exists($message)
    {
        $response = [
            'status' => 'error',
            'code' => 409,
            'message' => $message
        ];
        echo json_encode($response);
        exit;
    }
}
