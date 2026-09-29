<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Shipment_pdf extends App_pdf
{
    protected $shipment;

    public function __construct($shipment)
    {
        $this->shipment = $shipment;
        parent::__construct();
    }

    public function prepare()
    {
        $this->set_view_vars([
            'shipment' => $this->shipment,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'shipment';
    }

    protected function file_path()
    {
        return module_dir_path('shipments', 'views/pdf/shipment_pdf.php');
    }
}
