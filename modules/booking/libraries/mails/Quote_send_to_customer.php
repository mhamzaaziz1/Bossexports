<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quote_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $quote;

    protected $attach_pdf;

    public $slug = 'quote-send-to-customer';

    public $rel_type = 'quote';

    public function __construct($quote, $attach_pdf, $cc = '')
    {
        parent::__construct();

        $this->quote   = $quote;
        $this->attach_pdf = $attach_pdf;
        $this->cc         = $cc;
    }

    public function build()
    {
        if ($this->attach_pdf) {
            set_mailing_constant();
            $pdf    = quote_pdf($this->quote);
            $attach = $pdf->Output(slug_it($this->quote->subject) . '.pdf', 'S');
            $this->add_attachment([
                'attachment' => $attach,
                'filename'   => slug_it($this->quote->subject) . '.pdf',
                'type'       => 'application/pdf',
            ]);
        }

        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->quotes_model->get_attachments($this->quote->id, $attachment);
                $this->add_attachment([
                    'attachment' => get_upload_path_by_type('quote') . $this->quote->id . '/' . $_attachment->file_name,
                    'filename'   => $_attachment->file_name,
                    'type'       => $_attachment->filetype,
                    'read'       => true,
                ]);
            }
        }

        $this->to($this->quote->email)
        ->set_rel_id($this->quote->id)
        ->set_merge_fields('quotes_merge_fields', $this->quote->id);
    }
}
