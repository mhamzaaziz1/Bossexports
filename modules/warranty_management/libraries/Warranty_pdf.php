<?php defined('BASEPATH') or exit('No direct script access allowed');

class Warranty_pdf extends TCPDF
{
    public function Header()
    {
        // Logo (optional)
        $header = FCPATH . 'modules/warranty_management/assets/pdf/header.png';
        if (file_exists($header)) {
            $this->Image(
                $header,
                0,          // X = 0 (start from edge)
                0,          // Y = 0 (top)
                $this->getPageWidth(), // FULL WIDTH
                35,         // Height (adjust if needed)
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0
            );
        }
        $this->Ln(28);
        // Company Name
        // $this->SetFont('times', 'B', 14);
        // $this->Cell(0, 10, 'SAFETY MATIC', 0, 1, 'R');

        // Line
        // $this->Line(15, 25, 195, 25);
    }

    public function Footer()
    {
        $footer = FCPATH . 'modules/warranty_management/assets/pdf/footer.png';

        if (file_exists($footer)) {
            $this->Image(
                $footer,
                0,
                $this->getPageHeight() - 30,
                $this->getPageWidth(),
                30,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0
            );
        }
    }
}
