<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';

// HEADER: Right Column (Title & Number)
$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('shipment') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $shipment->shipment_number . '</b>';

if(isset($shipment->status)) {
    $info_right_column .= '<br /><span style="text-transform:uppercase;color:#4e4e4e;">' . _l('status') . ': ' . $shipment->status . '</span>';
}

// HEADER: Left Column (Logo)
$info_left_column .= pdf_logo_url();

// Write top header
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

// DETAILS: Organization Info
$organization_info = '<div style="color:#424242;">';
$organization_info .= '<b style="color:#4e4e4e;">' . _l('company_info') . '</b><br />';
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// DETAILS: Shipment Info (Carrier, Dates, etc)
$shipment_info = '<div style="color:#424242;">';
$shipment_info .= '<b style="color:#4e4e4e;">' . _l('shipment_details') . '</b><br />';
$shipment_info .= _l('carrier') . ': ' . $shipment->carrier . '<br />';
$shipment_info .= _l('etd') . ': ' . _d($shipment->etd) . '<br />';
$shipment_info .= _l('eta') . ': ' . _d($shipment->eta) . '<br />';
$shipment_info .= '</div>';

// Write details rows
// Left: Organization, Right: Shipment Details
pdf_multi_row($organization_info, $shipment_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// ITEMS TABLE
// Define standard styles
$pdf->SetFont($font_name, '', $font_size - 1);
$items_html = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8" border="0">';
$items_html .= '<thead>';
$items_html .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">';
$items_html .= '<th width="5%"  style="border-bottom:1px solid #323a45;">#</th>';
$items_html .= '<th width="40%" style="border-bottom:1px solid #323a45;">' . _l('item_name') . '</th>';
$items_html .= '<th width="15%" style="border-bottom:1px solid #323a45;">' . _l('quantity') . '</th>';
$items_html .= '<th width="20%" style="border-bottom:1px solid #323a45;" align="right">' . _l('fob_price') . '</th>';
$items_html .= '<th width="20%" style="border-bottom:1px solid #323a45;" align="right">' . _l('total_value') . '</th>';
$items_html .= '</tr>';
$items_html .= '</thead>';
$items_html .= '<tbody>';

$i = 1;
$total_fob = 0;
$total_qty = 0;

foreach ($shipment->lines as $line) {
    $line_total = (float)$line['qty_shipped'] * (float)$line['unit_fob_price'];
    $total_fob += $line_total;
    $total_qty += $line['qty_shipped'];
    
    $items_html .= '<tr style="border-bottom:1px solid #f0f0f0;">';
    $items_html .= '<td width="5%">' . $i++ . '</td>';
    
    // Item Description & Details
    $item_desc = (isset($line['item_name']) ? $line['item_name'] : $line['item_id']);
    $item_details = '<span style="color:#777;font-size:12px;">Wt: ' . number_format($line['net_weight_kg'], 2) . ' | Vol: ' . number_format($line['volume_cbm'], 2) . '</span>';
    
    $items_html .= '<td width="40%" style="color:#777;font-size:12px;">' . $item_desc . '<br />' . $item_details . '</td>';
    $items_html .= '<td width="15%" style="color:#777;font-size:12px;">' . number_format($line['qty_shipped'], 2) . '</td>';
    $items_html .= '<td width="20%" align="right" style="color:#777;font-size:12px;">' . app_format_money($line['unit_fob_price'], $shipment->currency_base) . '</td>';
    $items_html .= '<td width="20%" align="right" style="color:#777;font-size:12px;">' . app_format_money($line_total, $shipment->currency_base) . '</td>';
    $items_html .= '</tr>';
}

$items_html .= '</tbody></table>';

$pdf->writeHTML($items_html, true, false, false, false, '');

$pdf->Ln(8);

// TOTALS SUMMARY
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 3) . 'px">';

// Total FOB
$tbltotal .= '
<tr>
    <td align="right" width="75%"><strong>' . _l('total_fob_value') . '</strong></td>
    <td align="right" width="25%">' . app_format_money($total_fob, $shipment->currency_base) . '</td>
</tr>';

// Total Qty
$tbltotal .= '
<tr>
    <td align="right" width="75%"><strong>' . _l('total_qty') . '</strong></td>
    <td align="right" width="25%">' . number_format($total_qty, 2) . '</td>
</tr>';
$total_cost = 0;
// Costs
if (isset($shipment->costs) && count($shipment->costs) > 0) {
    foreach ($shipment->costs as $cost) {
        $total_cost += $cost['total_amount'];
        $tbltotal .= '
        <tr>
            <td align="right" width="75%"><strong>' . $cost['cost_name'] . '</strong></td>
            <td align="right" width="25%">' . app_format_money($cost['total_amount'], $cost['currency']) . '</td>
        </tr>';
    }
}

// Total
$tbltotal .= '
<tr>
    <td align="right" width="75%"><strong>' . _l('total') . '</strong></td>
    <td align="right" width="25%">' . app_format_money($total_fob + $total_cost, $shipment->currency_base) . '</td>
</tr>';

$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');
