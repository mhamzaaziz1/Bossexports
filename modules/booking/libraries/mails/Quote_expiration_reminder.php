<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote_expiration_reminder extends App_mail_template
{
    protected $for = 'customer';

    protected $quote;

    public $slug = 'quote-expiry-reminder';

    public $rel_type = 'quote';

    public function __construct($quote)
    {
        parent::__construct();

        $this->quote = $quote;

        // For SMS
        $this->set_merge_fields('quotes_merge_fields', $this->quote->id);
    }

    public function build()
    {
        set_mailing_constant();
        $pdf    = quote_pdf($this->quote);
        $attach = $pdf->Output(slug_it($this->quote->subject) . '.pdf', 'S');

        $this->add_attachment([
            'attachment' => $attach,
            'filename'   => slug_it($this->quote->subject) . '.pdf',
            'type'       => 'application/pdf',
        ]);

        $this->to($this->quote->email)
        ->set_rel_id($this->quote->id);
    }
}
