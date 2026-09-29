<?php defined('BASEPATH') or exit('No direct script access allowed');
// These lines should always be at the end of the document. Do not indent.
$html = <<<EOF
<div class="div_pdf">
$payment
</div>
EOF;

$pdf->writeHTML($html, true, false, true, false, '');
