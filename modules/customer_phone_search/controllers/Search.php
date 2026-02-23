<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Search extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Add the 'api' module path to load its libraries and models
        $this->load->add_package_path(APP_MODULES_PATH . 'api/');
        
        $this->load->library('Authorization_Token');
        $this->load->model('Api_model');
        
        // Remove the package path so it doesn't interfere with other core loads
        $this->load->remove_package_path(APP_MODULES_PATH . 'api/');
        
        $is_valid_token = $this->authorization_token->validateToken();
        $token = $this->authorization_token->get_token();
        $check_token = $this->Api_model->check_token($token);
        
        if ((isset($is_valid_token['status']) && $is_valid_token['status'] == false) || $check_token === false) {
            $message = isset($is_valid_token['message']) ? $is_valid_token['message'] : 'Invalid Token';
            $this->response_json([
                'status' => FALSE,
                'message' => $message
            ], 404);
        }
    }

    public function phone($phone = '')
    {
        // Only allow GET method
        if ($this->input->server('REQUEST_METHOD') !== 'GET') {
            $this->response_json([
                'status' => FALSE,
                'message' => 'Method Not Allowed'
            ], 405);
        }

        $q = trim($phone);
        if (!$q) {
            $this->response_json([
                'status' => FALSE,
                'message' => 'No data were found'
            ], 404);
        }

        $this->load->model('clients_model');
        
        $where_clients = db_prefix() . 'clients.active=1 AND (';
        $where_clients .= db_prefix() . 'clients.phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%" OR ';
        $where_clients .= db_prefix() . 'clients.userid IN (SELECT userid FROM ' . db_prefix() . 'contacts WHERE phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%")';
        $where_clients .= ')';

        $data = $this->clients_model->get('', $where_clients);

        if ($data) {
            $this->response_json($data, 200);
        } else {
            $this->response_json([
                'status' => FALSE,
                'message' => 'No data were found'
            ], 404);
        }
    }

    private function response_json($data, $status_code)
    {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}
