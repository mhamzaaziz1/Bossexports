<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:35px; "><b style="color:#0c5191;">' . _l('proforma_invoice') . '</span><br />';
$info_right_column .= '<span style="font-weight:bold;font-size:20px; "><b style="color:#4e4e4e;"># ' . $proforma_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    if(function_exists('proforma_status_color_pdf') && function_exists('format_proforma_status')){
         $info_right_column .= '<br /><span style="color:rgb(' . proforma_status_color_pdf($status) . ');text-transform:uppercase;">' . format_proforma_status($status, '', false) . '</span>';
    }
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$proforma_info = '<b>' . _l('invoice_bill_to') . ':</b>';
$proforma_info .= '<div style="color:#424242;">';
$proforma_info .= format_customer_info($proforma, 'invoice', 'billing');
$proforma_info .= '</div>';

// ship to to
if ($proforma->include_shipping == 1 && $proforma->show_shipping_on_invoice == 1) {
    $proforma_info .= '<br /><b>' . _l('ship_to') . ':</b>';
    $proforma_info .= '<div style="color:#424242;">';
    $proforma_info .= format_customer_info($proforma, 'invoice', 'shipping');
    $proforma_info .= '</div>';
}

$proforma_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($proforma->date) . '<br />';

if (! empty($proforma->duedate)) {
    $proforma_info .= _l('invoice_data_duedate') . ' ' . _d($proforma->duedate) . '<br />';
}

if ($proforma->sale_agent && get_option('show_sale_agent_on_invoices') == 1) {
    $proforma_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($proforma->sale_agent) . '<br />';
}

// Custom fields
foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($proforma->id, $field['id'], 'proforma');
    if ($value == '') {
        continue;
    }
    $proforma_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info  = $swap == '1' ? $proforma_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $proforma_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
// Use customPdfItemsTableData instead of get_items_table_data to invoke the custom class with styles
$items = customPdfItemsTableData($proforma, 'proforma', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proforma->subtotal, $proforma->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($proforma)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($proforma, 'percent')) {
        $tbltotal .= ' (' . app_format_number($proforma->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($proforma->discount_total, $proforma->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $proforma->currency_name) . '</td>
</tr>';
}

if ((int) $proforma->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proforma->adjustment, $proforma->currency_name) . '</td>
</tr>';
}

// Custom PDF styles for total row
$total_bg_color = !empty(getPdfOptions('proforma', 'items_table', 'total_row_bg_color')) ? getPdfOptions('proforma', 'items_table', 'total_row_bg_color') : "#f0f0f0";
$total_text_color = !empty(getPdfOptions('proforma', 'items_table', 'total_row_text_color')) ? getPdfOptions('proforma', 'items_table', 'total_row_text_color') : "#000000";

$tbltotal .= '
<tr style="background-color:' . $total_bg_color . '; color: ' . $total_text_color . '">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($proforma->total, $proforma->currency_name) . '</td>
</tr>';

// Payments
if (count($proforma->payments) > 0) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix() . 'proforma_invoice_payment_records', [
        'field' => 'amount',
        'where' => [
            'proforma_invoice_id' => $proforma->id,
        ],
    ]), $proforma->currency_name) . '</td>
    </tr>';
    
    // Amount Due
    $tbltotal .= '<tr style="background-color:#e5e7eb;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . app_format_money($proforma->total - sum_from_table(db_prefix() . 'proforma_invoice_payment_records', ['field' => 'amount', 'where' => ['proforma_invoice_id' => $proforma->id]]), $proforma->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($proforma->total, $proforma->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (! empty($proforma->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $proforma->clientnote, 0, 1, false, true, 'L', true);
}

if (! empty($proforma->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions') . ':', 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $proforma->terms, 0, 1, false, true, 'L', true);
}

if (get_option('show_proforma_signature') == 1) {
    $signatureImage = get_option('signature_image');
    if ($signatureImage != '' && file_exists(get_upload_path_by_type('company') . $signatureImage)) {
        $pdf->Ln(8);
        $pdf->SetFont($font_name, 'B', $font_size);
        $pdf->Cell(0, 0, _l('authorized_signature_text'), 0, 1, 'R', 0, '', 0);
        $pdf->SetFont($font_name, '', $font_size);
        $pdf->Ln(2);
        
        $pdf->setX($dimensions['wk'] - 80); // Adjust X to right side
        $pdf->Image(get_upload_path_by_type('company') . $signatureImage, '', '', 60, 0, '', '', 'R', false, 300, 'R', false, false, 0);
    }
}
