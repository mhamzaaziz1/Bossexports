<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Delivery_note_pdf extends App_pdf
{
    protected $delivery_note;

    protected $is_ending_page = false;

    protected $page_width;
    protected $page_height;

    protected $render_cover_page = false;

    private $delivery_note_number;

    public function __construct($delivery_note, $tag = '')
    {
        $this->load_language($delivery_note->clientid);

        $delivery_note                = hooks()->apply_filters('delivery_note_html_pdf_data', $delivery_note);
        $GLOBALS['delivery_note_pdf'] = $delivery_note;

        parent::__construct();

        $this->tag             = $tag;
        $this->delivery_note        = $delivery_note;
        $this->delivery_note_number = format_delivery_note_number($this->delivery_note->id);

        $this->page_width  = $this->getPageDimensions()['wk'];
        $this->page_height = $this->getPageDimensions()['hk'];

        $this->SetTitle($this->delivery_note_number);

        // Add Cover page
        $this->getCoverPage();
    }

    public function prepare()
    {
        $this->with_number_to_word($this->delivery_note->clientid);

        $this->set_view_vars([
            'status'          => $this->delivery_note->status,
            'delivery_note_number' => $this->delivery_note_number,
            'delivery_note'        => $this->delivery_note,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'delivery_note';
    }

    // Page header
    public function Header()
    {
        if (($this->render_cover_page == false && $this->page > 1) || $this->is_ending_page == false) {
            $header_text = getPdfOptions($this->type(), 'header', 'text');

            $pdf_header_image = getPdfOptions($this->type(), 'header', 'image');
            $image_url        = base_url('uploads/custom_pdf/' . $this->type() . '/' . $pdf_header_image);

            $this->Image($image_url, 0, 0, $this->getPageDimensions()['wk'], 30);
            $this->writeHTMLCell(0, 0, 10, 12, $header_text, 0, 0, 0, true, '', true);

            $this->SetTopMargin(35);
        }
    }

    // Page footer
    public function Footer()
    {
        if (!$this->is_ending_page && (!$this->render_cover_page || $this->page > 1)) {
            $footer_text = getPdfOptions($this->type(), 'footer', 'text');

            $pdf_footer_image = getPdfOptions($this->type(), 'footer', 'image');
            $image_url        = base_url('uploads/custom_pdf/' . $this->type() . '/' . $pdf_footer_image);

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

        if (!empty(getPdfOptions($this->type(), 'closing_page', 'image')) || !empty(getPdfOptions($this->type(), 'closing_page', 'text'))) {
            $this->AddPage();
            $this->is_ending_page = true;
            $bMargin              = $this->getBreakMargin();
            $auto_page_break      = $this->getAutoPageBreak();
            $this->SetAutoPageBreak(false, 0);

            $pdf_cover_image = getPdfOptions($this->type(), 'closing_page', 'image');
            $close_page_text = getPdfOptions($this->type(), 'closing_page', 'text');

            $parsedClosePageText = parsePDFMergeFields($this->type(), $close_page_text, $this->delivery_note);

            $align_from_left = getPdfOptions($this->type(), 'closing_page', 'align_from_left');
            $align_from_top  = getPdfOptions($this->type(), 'closing_page', 'align_from_top');
            $img_file        = base_url('uploads/custom_pdf/' . $this->type() . '/' . $pdf_cover_image);

            $this->Image($img_file, 0, 0, $this->page_width, $this->page_height, '', '', '', false, 300, '', false, false, 0);
            $this->writeHTMLCell(0, 0, $align_from_left, $align_from_top, $parsedClosePageText, 0, 0, 0, true, '', true);

            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
        }

        $this->last_page_flag = true;

        TCPDF::Close();
    }

    // Cover page
    protected function getCoverPage()
    {
        if (!empty(getPdfOptions($this->type(), 'cover_page', 'image')) || !empty(getPdfOptions($this->type(), 'cover_page', 'text'))) {
            $this->render_cover_page = true;
            $bMargin         = $this->getBreakMargin();
            $auto_page_break = $this->getAutoPageBreak();
            $this->SetAutoPageBreak(false, 0);

            $pdf_cover_image = getPdfOptions($this->type(), 'cover_page', 'image');
            $cover_page_text = getPdfOptions($this->type(), 'cover_page', 'text');

            $parsedCoverPageText = parsePDFMergeFields($this->type(), $cover_page_text, $this->delivery_note);

            $align_from_left = getPdfOptions($this->type(), 'cover_page', 'align_from_left');
            $align_from_top  = getPdfOptions($this->type(), 'cover_page', 'align_from_top');

            $img_file = base_url('uploads/custom_pdf/' . $this->type() . '/' . $pdf_cover_image);

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
        $custom_template = module_views_path('custom_pdf', 'pdf_template/custom_delivery_note_pdf.php');
        return $custom_template;
    }

    /**
     * Append all signatures to PDF.
     * It add system pdf when enabled, staff pdf and client pdf
     *
     * @return void
     */
    public function processSignature()
    {
        $record = $this->delivery_note;

        $signatures = [];

        // Insert the system signature if allowed
        if (getPdfOptions($this->type(), 'signature', 'show_system_signature') == 1) {
            $signatureImage = get_option('signature_image');
            $signaturePath   = get_upload_path_by_type('company') . $signatureImage;
            $company_signature = (object)[
                'signature_title' => _l('authorized_signature_text'),
                'signature' => $signaturePath
            ];
            $signatures[] = $company_signature;
        }

        // Add staff signatures
        if (getPdfOptions($this->type(), 'signature', 'show_staff_signature') == 1) {
            $signatures = array_merge($signatures, $record->staff_signatures);
        }

        // Insert customer signature if signed
        if (getPdfOptions($this->type(), 'signature', 'show_customer_signature') == 1) {
            $customer_signature = $record;
            $customer_signature->signature_title = _l('document_customer_signature_text');
            if (!empty($record->signature)) {
                $customerSignatureImage = get_upload_path_by_type($this->type()) . $record->id . '/' . $record->signature;
                $customerSignatureImage = hooks()->apply_filters('pdf_customer_signature_image_path', $customerSignatureImage, $this->type());
                $customer_signature->signature = $customerSignatureImage;
            }
            unset($customer_signature->staff_signatures);
            $signatures[] = $customer_signature;
        }

        // Make filtering hooks for modules
        $hookData = [
            'pdf_instance'       => $this,
            'type'               => $this->type(),
            'signatures'         => $signatures
        ];
        $signatures = hooks()->apply_filters('delivery_note_pdf_signatures', $hookData)['signatures'];

        $signatory_allowed_fields = get_option('delivery_note_signatory_allowed_fields');
        $signatory_allowed_fields = empty($signatory_allowed_fields) ? [] : (array)json_decode($signatory_allowed_fields);
        $signatory_allowed_fields = hooks()->apply_filters('delivery_note_pdf_signatory_fields', $signatory_allowed_fields);

        // Render signatures
        if (!empty($signatures)) {
            $count = count($signatures);
            $i = 0;
            foreach ($signatures as $sign) {
                $i++;
                $dimensions       = $this->getPageDimensions();
                $width = ($dimensions['wk'] / 2) - $dimensions['lm'];

                $path = $sign->signature ?? '';

                if (!file_exists($path) && !empty($path)) {
                    $path = get_upload_path_by_type($this->type()) . $record->id . '/' . $sign->signature;
                    $path = hooks()->apply_filters('pdf_signature_image_path', $path, $this->type());
                }

                $signature = "<span>{$sign->signature_title}</span>";

                if (isset($sign->acceptance_firstname)) {
                    $signature .= '<br/><br/><span style="font-weight:bold;text-align: left;">';
                    if (in_array('name', $signatory_allowed_fields))
                        $signature .= _l('document_signed_by') . ": {$sign->acceptance_firstname} {$sign->acceptance_lastname}<br />";
                    if (in_array('date', $signatory_allowed_fields))
                        $signature .= _l('document_signed_date') . ': ' . _dt($sign->acceptance_date ?? $sign->datecreated) . '<br />';
                    if (in_array('ip', $signatory_allowed_fields))
                        $signature .= _l('document_signed_ip') . ": {$sign->acceptance_ip}";
                    $signature .= '</span>';
                } else {
                    $signature .= '<br/><br/>';
                }

                $signature .= '<br />';

                $signature .= str_repeat(
                    '<br />',
                    hooks()->apply_filters('pdf_signature_break_lines', 1)
                );

                $canWriteImage = !empty($path) && file_exists($path) && !is_dir($path);

                // Write image if possible
                if ($canWriteImage) {
                    $imageData = base64_encode(file_get_contents($path));
                    $staffSignatureSize = hooks()->apply_filters('customer_staff_signature_size', 0);
                    
                    $imageHtml = '<img src="@' . $imageData . '" />';
                    if ($staffSignatureSize > 0) {
                         $imageHtml = '<img src="@' . $imageData . '" width="' . $staffSignatureSize . 'px" />';
                    }
                    
                    $signature .= $imageHtml;
                } else {
                    // Write empty line for putting signature manaually if no image exist for the signature
                    $blankSignatureLine = hooks()->apply_filters('blank_signature_line', '_________________________');
                    $blankSignatureLine =  str_repeat('<br />', hooks()->apply_filters('pdf_signature_break_lines', 6)) . $blankSignatureLine;
                    $signature .= $blankSignatureLine;
                }
                
                // Layout logic
                $is_last = ($i == $count);
                $is_even = ($i % 2 == 0);
                
                // If specific requirement is Company Left (1) and Customer Right (2)
                // 1st (i=1): Left Align, ln=0 (stay on line)
                // 2nd (i=2): Right Align, ln=1 (new line)
                
                $align = $is_even ? 'R' : 'L';
                $ln = ($is_even || $is_last) ? 1 : 0;
                
                // Calculate width to match PDF_Signature logic
                if ($is_even) {
                    // Right column
                    $cellWidth = ($dimensions['wk'] / 2) - $dimensions['rm'];
                } else {
                    // Left column
                    $cellWidth = ($dimensions['wk'] / 2) - $dimensions['lm'];
                }

                $this->MultiCell($cellWidth, 0, '<div nobr="true">' . $signature . '</div>', 0, $align, 0, $ln, '', '', true, 0, true, true, 0);
                
                // Add spacing only if we finished a row (even) or it's the last item
                if ($ln == 1) {
                    $this->ln(18); 
                }
            }
        }
    }
}
