<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Pur_payment_pdf extends App_pdf
{
    protected $payment;

    public function __construct($payment)
    {
        $payment                = hooks()->apply_filters('pur_payment_html_pdf_data', $payment);
        $GLOBALS['pur_payment_pdf'] = $payment;

        parent::__construct();

        $this->payment = $payment;

        $this->SetTitle('payment_receipt');
        # Don't remove these lines - important for the PDF layout
        $this->payment = $this->fix_editor_html($this->payment);
    }

    public function prepare()
    {
        $this->set_view_vars('payment', $this->payment);

        return $this->build();
    }

    protected function type()
    {
        return 'pur_payment';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_paymentpdf.php';
        $actualPath = APP_MODULES_PATH . '/purchase/views/purchase_order/payments/payment_pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
