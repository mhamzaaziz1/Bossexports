<?php

defined('BASEPATH') || exit('No direct script access allowed');

include_once LIBSPATH . 'pdf/App_pdf.php';

class Statement_pdf extends App_pdf
{
    protected $statement;
    protected $page_width;
    protected $page_height;
    protected $settings;

    public function __construct($statement, $tag = '')
{
    parent::__construct();

    if (is_array($statement)) {
        $statement = json_decode(json_encode($statement));
    }

    $this->settings = json_decode(get_option('statement_pdf_settings'), true);

    $this->statement = $statement;
    $this->tag       = $tag;

    $this->page_width  = $this->getPageDimensions()['wk'];
    $this->page_height = $this->getPageDimensions()['hk'];

    $statement_number = isset($statement->number) ? $statement->number : '';

    $this->SetTitle('Statement #' . $statement_number);
}

    /* ================= HEADER ================= */

public function Header()
{
    $pdf_header_image = getPdfOptions('statement', 'header', 'image');

    if (!empty($pdf_header_image)) {
        $img_file = base_url('uploads/custom_pdf/statement/' . $pdf_header_image);
        $this->Image($img_file, 0, 0, $this->page_width, 30);
    }

    $header_text = getPdfOptions('statement', 'header', 'text');

    $this->writeHTMLCell(0, 0, 10, 12, $header_text, 0, 0, 0, true, '', true);

    $this->SetTopMargin(35);
}
    // public function statementHeader()
    // {
    //     $header_text = getPdfOptions('statement', 'header', 'text');
    //     $pdf_header_image = $this->settings['header']['image'] ?? '';

    //     if ($pdf_header_image) {
    //         $image_path = FCPATH . 'uploads/custom_pdf/statement/' . $pdf_header_image;

    //         if (file_exists($image_path)) {
    //             $this->Image($image_path, 0, 0, $this->page_width, 30);
    //         }
    //     }

    //     $parsedHeaderText = parsePDFMergeFields('statement', $header_text, $this->statement);

    //     $this->writeHTMLCell(0, 0, 10, 12, $parsedHeaderText, 0, 0, 0, true, '', true);

    //     $this->SetTopMargin(35);
    // }

    /* ================= FOOTER ================= */

    // public function statementFooter()
    // {
    //     $this->SetAutoPageBreak(false, 0);

    //     $footer_text = getPdfOptions('statement', 'footer', 'text');
    //     $pdf_footer_image = $this->settings['footer']['image'] ?? '';
        
    //     if ($pdf_footer_image) {
    //         $image_path = FCPATH . 'uploads/custom_pdf/statement/' . $pdf_footer_image;

    //         if (file_exists($image_path)) {
    //             $this->Image($image_path, 0, 0, $this->page_width, 30);
    //         }
    //     }

    //     $parsedFooterText = parsePDFMergeFields('statement', $footer_text, $this->statement);

    //     $this->writeHTMLCell(0, 0, 10, -12, $parsedFooterText, 0, 0, 0, true, '', true);

    //     $this->SetFooterMargin(20);
    // }
    public function Footer()
{
    $pdf_footer_image = getPdfOptions('statement', 'footer', 'image');

    if (!empty($pdf_footer_image)) {
        $img_file = base_url('uploads/custom_pdf/statement/' . $pdf_footer_image);
        $this->Image($img_file, 0, $this->page_height - 30, $this->page_width, 30);
    }

    $footer_text = getPdfOptions('statement', 'footer', 'text');

    $this->writeHTMLCell(0, 0, 10, -12, $footer_text, 0, 0, 0, true, '', true);

    $this->SetFooterMargin(20);
}

    /* ================= COVER PAGE ================= */

    protected function getCoverPage()
    {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->getAutoPageBreak();

        $this->SetAutoPageBreak(false, 0);

        $cover_image = $this->settings['cover_page']['image'] ?? '';
        $cover_text  = getPdfOptions('statement', 'cover_page', 'text');

        if ($cover_image) {
            
            $image_path = FCPATH . 'uploads/custom_pdf/statement/' . $cover_image;

            if (file_exists($image_path)) {
                $this->Image($image_path, 0, 0, $this->page_width, 30);
            }
        }

        $parsedText = parsePDFMergeFields('statement', $cover_text, $this->statement);

        $left = getPdfOptions('statement', 'cover_page', 'align_from_left');
        $top  = getPdfOptions('statement', 'cover_page', 'align_from_top');

        $this->writeHTMLCell(0, 0, $left, $top, $parsedText, 0, 0, 0, true, '', true);

        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }

    /* ================= CLOSING PAGE ================= */

    public function statementClosingPage()
    {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->getAutoPageBreak();

        $this->SetAutoPageBreak(false, 0);

        $closing_image = $this->settings['closing_page']['image'] ?? '';
        $closing_text  = getPdfOptions('statement', 'closing_page', 'text');

        if ($closing_image) {
            
            $image_path = FCPATH . 'uploads/custom_pdf/statement/' . $closing_image;

            if (file_exists($image_path)) {
                $this->Image($image_path, 0, 0, $this->page_width, 30);
            }
        }

        $parsedText = parsePDFMergeFields('statement', $closing_text, $this->statement);

        $left = getPdfOptions('statement', 'closing_page', 'align_from_left');
        $top  = getPdfOptions('statement', 'closing_page', 'align_from_top');

        $this->writeHTMLCell(0, 0, $left, $top, $parsedText, 0, 0, 0, true, '', true);

        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }

    /* ================= BUILD ================= */

   public function prepare()
{
    $this->set_view_vars([
        'statement' => $this->statement,
    ]);

    return $this->build();
}

    protected function type()
    {
        return 'statement';
    }

     protected function file_path()
    {
        $customPath = APPPATH.'views/themes/'.active_clients_theme().'/views/my_statementpdf.php';
        $actualPath = module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_statement_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}