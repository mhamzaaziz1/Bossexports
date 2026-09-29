<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Aging_pdf extends App_pdf
{
    private $aging_data;

    public function __construct($aging_data)
    {
        $this->aging_data = $aging_data;
        parent::__construct();
    }

    public function prepare()
    {
        $this->set_view_vars($this->aging_data);

        return $this->build();
    }

    public function get_format_array()
    {
        $format = parent::get_format_array();
        $format['orientation'] = 'L'; // Force Landscape
        return $format;
    }

    public function get_default_font_size()
    {
        return 8; // Force small font
    }

    protected function type()
    {
        return 'aging';
    }

    protected function file_path()
    {
        return 'modules/' . AGING_MODULE_NAME . '/views/aging_pdf_template.php';
    }
}
