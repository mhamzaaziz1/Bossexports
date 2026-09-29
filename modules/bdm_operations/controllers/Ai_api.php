<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ai_api extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');
    }

    /**
     * Webhook to receive call details from Yeastar/n8n
     * PUBLIC ENDPOINT
     */
    public function incoming_call()
    {
        $data = $this->input->post();
        if (empty($data['CallerID']) || empty($data['DID'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Missing CallerID or DID']);
            return;
        }

        // Log to MySQL
        $this->db->insert(db_prefix() . 'call_logs', [
            'caller_id' => $data['CallerID'],
            'did'       => $data['DID'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Get Company Profile
        $this->db->where('did', $data['DID']);
        $company = $this->db->get(db_prefix() . 'company_configs')->row();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'company_profile' => $company ? $company : null
        ]);
    }

    /**
     * API to create a lead from AI extraction
     * PUBLIC ENDPOINT
     */
    public function create_lead()
    {
        $payload = json_decode($this->input->raw_input_stream, true);
        
        if (!$payload || empty($payload['name'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            return;
        }

        $lead_data = [
            'name'        => $payload['name'],
            'source'      => 1, // AI Receptionist Source ID
            'status'      => 1, // Default Lead Status
            'description' => "Service: {$payload['service']}\nUrgency: {$payload['urgency']}\nCallback: {$payload['callback']}\nSummary: " . ($payload['summary'] ?? ''),
            'dateadded'   => date('Y-m-d H:i:s')
        ];

        $id = $this->leads_model->add($lead_data);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'lead_id' => $id]);
    }
}
