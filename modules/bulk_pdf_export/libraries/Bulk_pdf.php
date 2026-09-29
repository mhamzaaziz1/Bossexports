<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Bulk_pdf extends App_pdf
{
    private $items = [];

    public function __construct($items, $tag = '')
    {
        $this->items = $items;
        $this->tag = $tag;
        
        $GLOBALS['bulk_pdf_export'] = true;

        parent::__construct();

        $this->SetTitle(_l('bulk_pdf_export_report') ?: 'Bulk PDF Export');
    }

    public function prepare()
    {
        return $this->build();
    }

    protected function type()
    {
        return 'invoice'; // Use invoice formatting for the wrapper
    }

    protected function file_path()
    {
        return '';
    }

    protected function build()
    {
        _bulk_pdf_export_maybe_tag($this->tag, $this);

        $pdf = $this;
        $CI = $this->ci;
        
        $is_first = true;

        foreach ($this->items as $item) {
            $this->view_vars = [];
            $this->set_default_view_vars_local();

            if ($item['type'] == 'invoice') {
                $this->render_invoice($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'estimate') {
                $this->render_estimate($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'proposal') {
                $this->render_proposal($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'credit_note') {
                $this->render_credit_note($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'pur_request') {
                $this->render_pur_request($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'pur_quotation') {
                $this->render_pur_quotation($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'pur_order') {
                $this->render_pur_order($item['data'], $pdf, $CI, $is_first);
            } elseif ($item['type'] == 'pur_return') {
                $this->render_pur_return($item['data'], $pdf, $CI, $is_first);
            }
            $is_first = false;
        }

        if (ob_get_length() > 0 && ENVIRONMENT == 'production') {
            ob_end_clean();
        }

        return $this;
    }

    private function render_invoice($invoice, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }
        
        $this->load_language($invoice->clientid);
        
        $CI->load->model('payment_modes_model');
        $payment_modes = $CI->payment_modes_model->get();
        $invoice_number = format_invoice_number($invoice->id);
        
        foreach ($payment_modes as $key => $mode) {
            if (isset($mode['description'])) {
                $payment_modes[$key]['description'] = str_replace('{invoice_number}', $invoice_number, $mode['description']);
                $payment_modes[$key]['description'] = str_replace('{client_id}', $invoice->clientid, $mode['description']);
            }
        }
        
        $this->with_number_to_word($invoice->clientid);
        
        $this->set_view_vars([
            'status'         => $invoice->status,
            'invoice_number' => $invoice_number,
            'payment_modes'  => $payment_modes,
            'invoice'        => hooks()->apply_filters('invoice_html_pdf_data', $invoice),
        ]);
        
        $this->set_view_vars('pdf_custom_fields', get_custom_fields('invoice', ['show_on_pdf' => 1]));
        
        extract($this->view_vars);
        $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/invoicepdf.php';
        if(file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_invoicepdf.php')) {
            $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_invoicepdf.php';
        }
        include $path;
    }

    private function render_estimate($estimate, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }
        
        $this->load_language($estimate->clientid);
        $this->with_number_to_word($estimate->clientid);
        
        $estimate_number = format_estimate_number($estimate->id);
        
        $this->set_view_vars([
            'status'          => $estimate->status,
            'estimate_number' => $estimate_number,
            'estimate'        => hooks()->apply_filters('estimate_html_pdf_data', $estimate),
        ]);
        
        $this->set_view_vars('pdf_custom_fields', get_custom_fields('estimate', ['show_on_pdf' => 1]));

        extract($this->view_vars);
        $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/estimatepdf.php';
        if(file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_estimatepdf.php')) {
            $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_estimatepdf.php';
        }
        include $path;
    }
    
    private function render_proposal($proposal, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }
        
        if ($proposal->rel_id != null && $proposal->rel_type == 'customer') {
            $this->load_language($proposal->rel_id);
        } else if ($proposal->rel_id != null && $proposal->rel_type == 'lead') {
            $CI->db->select('default_language')->where('id', $proposal->rel_id);
            $language = $CI->db->get(db_prefix() . 'leads')->row()->default_language;
            load_pdf_language($language);
        }
        
        $proposal->content = $this->fix_editor_html($proposal->content);
        $proposal = hooks()->apply_filters('proposal_html_pdf_data', $proposal);
        $proposal_number = format_proposal_number($proposal->id);
        
        $number_word_lang_rel_id = 'unknown';
        if ($proposal->rel_type == 'customer') {
            $number_word_lang_rel_id = $proposal->rel_id;
        }
        $this->with_number_to_word($number_word_lang_rel_id);
        
        $total = '';
        if ($proposal->total != 0) {
            $total = app_format_money($proposal->total, get_currency($proposal->currency));
            $total = _l('proposal_total') . ': ' . $total;
        }
        
        $this->set_view_vars([
            'number'       => $proposal_number,
            'proposal'     => $proposal,
            'total'        => $total,
            'proposal_url' => site_url('proposal/' . $proposal->id . '/' . $proposal->hash),
        ]);
        
        $this->set_view_vars('pdf_custom_fields', get_custom_fields('proposal', ['show_on_pdf' => 1]));

        extract($this->view_vars);
        $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/proposalpdf.php';
        if(file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proposalpdf.php')) {
            $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_proposalpdf.php';
        }
        include $path;
    }
    
    private function set_default_view_vars_local()
    {
        $this->set_view_vars([
            'pdf_custom_fields' => [],
            'swap'              => $this->swap,
            'font_size'         => $this->get_font_size(),
            'font_name'         => $this->get_font_name(),
        ]);
    }

    private function render_credit_note($credit_note, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }

        $this->load_language($credit_note->clientid);
        $this->with_number_to_word($credit_note->clientid);
        $credit_note_number = format_credit_note_number($credit_note->id);

        $this->set_view_vars([
            'status'             => $credit_note->status,
            'credit_note_number' => $credit_note_number,
            'credit_note'        => $credit_note,
        ]);

        extract($this->view_vars);
        $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/credit_note_pdf.php';
        if(file_exists(APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_credit_note_pdf.php')) {
            $path = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_credit_note_pdf.php';
        }
        include $path;
    }

    private function render_pur_request($pur_request, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }
        
        $pur_request = hooks()->apply_filters('request_html_pdf_data', $pur_request);
        $pur_request = $this->fix_editor_html($pur_request);

        $this->set_view_vars('pur_request', $pur_request);

        extract($this->view_vars);
        $path = APP_MODULES_PATH . '/purchase/views/purchase_request/request_quotationpdf.php';
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        if(file_exists($customPath)) {
            $path = $customPath;
        }
        include $path;
    }

    private function render_pur_quotation($pur_quotation, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }

        $pur_quotation = hooks()->apply_filters('request_html_pdf_data', $pur_quotation);
        $pur_quotation = $this->fix_editor_html($pur_quotation);

        $this->set_view_vars('estimate', $pur_quotation);

        extract($this->view_vars);
        $path = APP_MODULES_PATH . '/purchase/views/quotations/estimatepdf.php';
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_estimatepdf.php';
        if(file_exists($customPath)) {
            $path = $customPath;
        }
        include $path;
    }

    private function render_pur_order($pur_order, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }

        $pur_order = hooks()->apply_filters('request_html_pdf_data', $pur_order);
        $pur_order = $this->fix_editor_html($pur_order);

        $this->set_view_vars('pur_order', $pur_order);

        extract($this->view_vars);
        $path = APP_MODULES_PATH . '/purchase/views/purchase_order/pur_orderpdf.php';
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        if(file_exists($customPath)) {
            $path = $customPath;
        }
        include $path;
    }

    private function render_pur_return($pur_return, $pdf, $CI, $is_first) {
        if (!$is_first) {
            $pdf->AddPage($this->get_format_array()['orientation'], $this->get_format_array()['format']);
        }

        $pur_return = hooks()->apply_filters('request_html_pdf_data', $pur_return);
        $pur_return = $this->fix_editor_html($pur_return);

        $this->set_view_vars('return_order', $pur_return);

        extract($this->view_vars);
        $path = APP_MODULES_PATH . '/purchase/views/purchase_order/pur_returnpdf.php';
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        if(file_exists($customPath)) {
            $path = $customPath;
        }
        include $path;
    }
}
