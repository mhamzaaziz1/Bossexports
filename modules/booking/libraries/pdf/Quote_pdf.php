<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Quote_pdf extends App_pdf
{
    protected $quote;

    private $quote_number;

    public function __construct($quote, $tag = '')
    {
        $quote                = hooks()->apply_filters('quote_html_pdf_data', $quote);
        $GLOBALS['quote_pdf'] = $quote;

        parent::__construct();

        $this->tag      = $tag;
        $this->quote = $quote;

        $this->quote_number = format_quote_number($this->quote->id);

        if ($quote->rel_id != null && $quote->rel_type == 'customer') {
            $this->load_language($quote->rel_id);
        }

        $this->SetTitle($this->quote_number);
        $this->SetDisplayMode('default', 'OneColumn');

        # Don't remove these lines - important for the PDF layout
        $this->quote->content = $this->fix_editor_html($this->quote->content);
    }

    public function prepare()
    {
        $number_word_lang_rel_id = 'unknown';

        if ($this->quote->rel_type == 'customer') {
            $number_word_lang_rel_id = $this->quote->rel_id;
        }

        $this->with_number_to_word($number_word_lang_rel_id);

        $total = '';
        if ($this->quote->total != 0) {
            $total = app_format_money($this->quote->total, get_currency($this->quote->currency));
            $total = _l('quote_total') . ': ' . $total;
        }

        $this->set_view_vars([
            'number'       => $this->quote_number,
            'quote'     => $this->quote,
            'total'        => $total,
            'quote_url' => site_url('booking/quote/index/' . $this->quote->id . '/' . $this->quote->hash),
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'quote';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_quotepdf.php';
        $actualPath = module_views_path(BOOKING_MODULE_NAME) . 'admin/quotes/quotepdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
