<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zatca_api
{
    private $ci;
    private $base_url_sandbox = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
    private $base_url_simulation = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
    private $base_url_production = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core';

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    private function get_base_url()
    {
        $mode = get_option('zatca_mode');
        if ($mode == 'production') {
            return $this->base_url_production;
        } elseif ($mode == 'simulation') {
            return $this->base_url_simulation;
        }
        return $this->base_url_sandbox;
    }

    /**
     * Step 1: Issue Compliance CSID
     * @param string $csr The generated CSR
     * @param string $otp The OTP from Fatoora Portal
     */
    public function issue_compliance_certificate($csr, $otp)
    {
        $url = $this->get_base_url() . '/compliance';
        
        $headers = [
            'otp: ' . $otp,
            'Accept-Version: V2',
            'Content-Type: application/json'
        ];

        $body = json_encode(['csr' => base64_encode($csr)]);

        return $this->send_request('POST', $url, $headers, $body);
    }

    /**
     * Step 2: Check Compliance (Optional/Implicit usually in Phase 2 flows, but good for validation)
     * For simplified reporting or standard clearance.
     */
    
    /**
     * Step 3: Issue Production CSID
     * @param string $compliance_request_id (From Step 1 response)
     */
    public function issue_production_certificate($compliance_csid, $compliance_secret)
    {
        $url = $this->get_base_url() . '/production/csids';
        
        // Basic Auth with Compliance credentials
        $auth = base64_encode($compliance_csid . ':' . $compliance_secret);
        
        $headers = [
            'Authorization: Basic ' . $auth,
            'Accept-Version: V2',
            'Content-Type: application/json'
        ];

        $body = json_encode(['compliance_request_id' => '123']); // Typically requires the ID from compliance step if needed, but often just the CSR again for renewal. 
        // CORRECT PATH: For onboarded solution, we use the COMPLIANCE CSID to sign a request to get the PRODUCTION CSID.
        // Actually, the endpoint expects the COMPLIANCE CSID as Basic Auth.
        // The Request Body usually contains the COMPLIANCE_REQUEST_ID from Step 1.
        
        return $this->send_request('POST', $url, $headers, $body);
    }

    /**
     * Send HTTP Request
     */
    private function send_request($method, $url, $headers, $body = null)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        return [
            'code' => $http_code,
            'response' => json_decode($response, true),
            'error' => $error
        ];
    }
}
