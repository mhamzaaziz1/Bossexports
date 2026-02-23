<?php

defined('BASEPATH') || exit('No direct script access allowed');

include_once APPPATH.'/libraries/pdf/App_pdf.php';

class Proforma_pdf extends App_pdf
{
    protected $proforma;
    protected $is_ending_page = false;

    protected $page_width;
    protected $page_height;

    private $proforma_number;

    protected $render_cover_page = false;

    public function __construct($proforma, $tag = '')
    {
        // Handle parameters whether passed as array (from proforma module helper) or variables
        if (is_array($proforma)) {
            $params = $proforma;
            $proforma = $params['proforma'];
            $tag = $params['tag'] ?? '';
        }

        $this->load_language($proforma->clientid);
        $proforma                = hooks()->apply_filters('proforma_html_pdf_data', $proforma);
        $GLOBALS['proforma_pdf'] = $proforma;

        parent::__construct();

        if (!class_exists('Proforma_model', false)) {
            $this->ci->load->model('proforma/proforma_model');
        }

        $this->tag             = $tag;
        $this->proforma        = $proforma;
        $this->proforma_number = format_proforma_number($this->proforma->id);

        $this->page_width  = $this->getPageDimensions()['wk'];
        $this->page_height = $this->getPageDimensions()['hk'];

        $this->SetTitle($this->proforma_number);

        // Add Cover page
        $this->getCoverPage();
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

    // Page header
    public function Header()
    {
        if (($this->render_cover_page == false && $this->page > 1) || $this->is_ending_page == false) {
            $header_text = getPdfOptions('proforma', 'header', 'text');

            $pdf_header_image = getPdfOptions('proforma', 'header', 'image');
            $image_url        = base_url('uploads/custom_pdf/proforma/' . $pdf_header_image);

            $this->Image($image_url, 0, 0, $this->getPageDimensions()['wk'], 30);
            $this->writeHTMLCell(0, 0, 10, 12, $header_text, 0, 0, 0, true, '', true);

            $this->SetTopMargin(35);
        }
    }

    // Page footer
    public function Footer()
    {
        if (!$this->is_ending_page && (!$this->render_cover_page || $this->page > 1)) {
            $footer_text = getPdfOptions('proforma', 'footer', 'text');

            $pdf_footer_image = getPdfOptions('proforma', 'footer', 'image');
            $image_url        = base_url('uploads/custom_pdf/proforma/' . $pdf_footer_image);

            $this->Image($image_url, 0, $this->page_height - 30, $this->page_width, 30);
            $this->writeHTMLCell(0, 0, 10, $this->page_height - 15, $footer_text, 0, 0, 0, true, '', true);

            }
    }

    // Closing page
    public function Close()
    {
        if (hooks()->apply_filters('process_pdf_signature_on_close', true)) {
            $this->processSignature();
        }

        hooks()->do_action('pdf_close', ['pdf_instance' => $this, 'type' => $this->type()]);

        if (!empty(getPdfOptions('proforma', 'closing_page', 'image')) || !empty(getPdfOptions('proforma', 'closing_page', 'text'))) {
            $this->AddPage();
            $this->is_ending_page = true;
            $bMargin              = $this->getBreakMargin();
            $auto_page_break      = $this->getAutoPageBreak();
            $this->SetAutoPageBreak(false, 0);

            $pdf_cover_image = getPdfOptions('proforma', 'closing_page', 'image');
            $close_page_text = getPdfOptions('proforma', 'closing_page', 'text');

            $parsedClosePageText = parsePDFMergeFields('proforma', $close_page_text, $this->proforma);

            $align_from_left = getPdfOptions('proforma', 'closing_page', 'align_from_left');
            $align_from_top  = getPdfOptions('proforma', 'closing_page', 'align_from_top');
            $img_file        = base_url('uploads/custom_pdf/proforma/' . $pdf_cover_image);

            $this->Image($img_file, 0, 0, $this->page_width, $this->page_height, '', '', '', false, 300, '', false, false, 0);
            $this->writeHTMLCell(0, 0, $align_from_left, $align_from_top, $parsedClosePageText, 0, 0, 0, true, '', true);

            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
        }

        $this->last_page_flag = true;

        TCPDF::Close();
    }

    protected function type()
    {
        return 'proforma';
    }

    // Cover page
    protected function getCoverPage()
    {
        if (!empty(getPdfOptions('proforma', 'cover_page', 'image')) || !empty(getPdfOptions('proforma', 'cover_page', 'text'))) {
            $this->render_cover_page = true;
            $bMargin         = $this->getBreakMargin();
            $auto_page_break = $this->getAutoPageBreak();
            $this->SetAutoPageBreak(false, 0);

            $pdf_cover_image = getPdfOptions('proforma', 'cover_page', 'image');
            $cover_page_text = getPdfOptions('proforma', 'cover_page', 'text');

            $parsedCoverPageText = parsePDFMergeFields('proforma', $cover_page_text, $this->proforma);

            $align_from_left = getPdfOptions('proforma', 'cover_page', 'align_from_left');
            $align_from_top  = getPdfOptions('proforma', 'cover_page', 'align_from_top');

            $img_file = base_url('uploads/custom_pdf/proforma/' . $pdf_cover_image);

            $this->Image($img_file, 0, 0, $this->page_width, $this->page_height, '', '', '', false, 300, '', false, false, 0);
            $this->writeHTMLCell(0, 0, $align_from_left, $align_from_top, $parsedCoverPageText, 0, 0, 0, true, '', true);

            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            // set the starting point for the page content
            $this->setPageMark();

            $this->AddPage();
        }
    }

    protected function file_path()
    {
        $customPath = APPPATH.'views/themes/'.active_clients_theme().'/views/my_proformapdf.php';
        $actualPath = module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_proforma_pdf.php');

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
