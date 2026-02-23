<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Proforma_invoice_pdf extends App_pdf
{
    protected $proforma;
    private $proforma_number;

    public function __construct($params)
    {
        $proforma = $params['proforma'];
        $tag      = $params['tag'] ?? '';
        
        $this->load_language($proforma->clientid);
        $proforma                = hooks()->apply_filters('proforma_html_pdf_data', $proforma);
        $GLOBALS['proforma_pdf'] = $proforma;

        parent::__construct();

        if (!class_exists('Proforma_model', false)) {
            $this->ci->load->model('proforma/proforma_model');
        }

        $this->tag            = $tag;
        $this->proforma       = $proforma;
        $this->proforma_number = format_proforma_number($this->proforma->id);

        $this->SetTitle($this->proforma_number);
    }

    public function prepare()
    {
        $this->with_number_to_word($this->proforma->clientid);

        $this->set_view_vars([
            'status'          => $this->proforma->status,
            'proforma_number' => $this->proforma_number,
            'payment_modes'   => $this->get_payment_modes(),
            'proforma'        => $this->proforma,
            'content_html'    => isset($this->proforma->content_html) ? $this->proforma->content_html : '',
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'proforma';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proformapdf.php';
        $actualPath = FCPATH . 'modules/proforma/views/proformapdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath; 
    }

    private function get_payment_modes()
    {
        $this->ci->load->model('payment_modes_model');
        $payment_modes = $this->ci->payment_modes_model->get();

        // In case user want to include {invoice_number} or {client_id} in PDF offline mode description
        foreach ($payment_modes as $key => $mode) {
            if (isset($mode['description'])) {
                $payment_modes[$key]['description'] = str_replace('{invoice_number}', $this->proforma_number, $mode['description']);
                $payment_modes[$key]['description'] = str_replace('{client_id}', $this->proforma->clientid, $mode['description']);
            }
        }

        return $payment_modes;
    }
}
