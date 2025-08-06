<?php

defined('BASEPATH') or exit('No direct script access allowed');
$i=0;
$maxi=(int)(O/49500)+1;
if($maxi<=0){
    $maxi=1;
}
foreach (range( $maxi , 1) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">' . _l('Sales Quotation') . '</span><br />';
// $info_right_column .= 'proposal No. = <b style="color:#4e4e4e;">' . $proposal_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . proposal_status_color_pdf($status) . ');text-transform:uppercase;">' . format_proposal_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$proposal_info = '<b style="color:#424242; font-size:13px">' . _l('Proposal To') . ':</b><br>';
$proposal_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $proposal_info .= format_proposal_info($proposal, 'pdf');
$proposal_info .= '</div>';

// ship to to
$proposal_info1 .= '<div style="font-size:14px" border="1px"> Quote # <b style="color:#4e4e4e;">' . format_proposal_number($proposal->id).'</b>';

$proposal_info1 .= '<br />' . _l('Quote Date') . ' ' . _d($proposal->date) . '<br>';

// if (!empty($proposal->duedate)) {
//     $proposal_info1 .= _l('proposal_data_duedate') . ' ' . _d($proposal->duedate) . '<br />';
// }
$proposal_info1 .= '<b><u>Payment Accounts</u></b><br><b>FNB:</b> Branch : 250505 Ac No: 62025537450<br>
Swift: FIRNZAJJ031 US$ <b>AC:</b> 62747263200<br>
<b>NEDBANK</b> Branch: 128605  Ac No: 1286085373 <br>Cash Deposit <b>NED BANK Only</b></div>';
if (true) {
    $proposal_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $proposal_info1 .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $proposal_info1 .= format_proposal_info($proposal, 'pdf');
    $proposal_info1 .= '</div>';
}



if ($proposal->sale_agent != 0 && get_option('show_sale_agent_on_proposals') == 1) {
    $proposal_info1 .= _l('sale_agent_string') . ': ' . get_staff_full_name($proposal->sale_agent) . '<br />';
}

if ($proposal->project_id != 0 && get_option('show_project_on_proposal') == 1) {
    $proposal_info1 .= _l('project') . ': ' . get_project_name_by_id($proposal->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($proposal->id, $field['id'], 'proposal');
    if ($value == '') {
        continue;
    }
    $proposal_info1 .= $field['name'] . ': ' . $value . '<br />';
}
$organization_info .=$proposal_info;
$left_info  = $swap == '1' ? $proposal_info : $organization_info;
 $right_info = $proposal_info1;
// $left_info  = $organization_info+$proposal_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
//$pdf->writeHTML($tbltotal, true, false, false, false, '');
$proposal_date = _l('proposal_date') . ': ' . _d($proposal->date);
$open_till     = '';

if (!empty($proposal->open_till)) {
    $open_till = _l('proposal_open_till') . ': ' . _d($proposal->open_till) . '<br />';
}

$qty_heading = _l('estimate_table_quantity_heading', '', false);

if ($proposal->show_quantity_as == 2) {
    $qty_heading = _l($this->type . '_table_hours_heading', '', false);
} elseif ($proposal->show_quantity_as == 3) {
    $qty_heading = _l('estimate_table_quantity_heading', '', false) . '/' . _l('estimate_table_hours_heading', '', false);
}
// The items table
$items = get_items_table_data($proposal, 'proposal', 'pdf');
// $tblhtml = "";
  $tbltotal='';
  $tbltotal .= '<table  style="font-size:14px" border="1px" align="center">';
  $tbltotal .='<tr>';
  $tbltotal .='<th width="10%"><b>SKU</b></th>';
  $tbltotal .='<th width="30%"><b>Description</b></th>';
  $tbltotal .='<th width="10%"><b>Weight</b></th>';
  $tbltotal .='<th width="10%"><b>Volume</b></th>';
  $tbltotal .='<th width="10%"><b>Qty</b></th>';
  $tbltotal .='<th width="10%"><b>Price</b></th>';
  $tbltotal .='<th width="10%"><b>Tax</b></th>';
  $tbltotal .='<th width="10%"><b>Total</b></th>';
  $tbltotal .='</tr>';
  $qty=0;
  // The items table
  foreach ($proposal->items as $key ) {
    $tbltotal .='<tr>';
    $CI->db->select("commodity_code");
    $CI->db->from('tblitems');
    $CI->db->where('tblitems.description',$key['description']);
    $sku = $CI->db->get()->result();
    $tbltotal .= '<td width="10%">'.$sku[0]->commodity_code.'</td>';
    $tbltotal .= '<td width="30%">'.$key['description'].'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$proposal->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"proposal");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$proposal->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"proposal");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    $qty=$qty+$key['qty']/$maxi;
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $tbltotal .= '<td width="10%">'.$key['qty']/$maxi.'</td>';
    $tbltotal .= '<td width="10%">'.$key['rate'].'</td>';
    $CI->db->select("taxrate");
    $CI->db->from('tblitem_tax');
    $CI->db->where('tblitem_tax.rel_id',$proposal->id);
    $CI->db->where('tblitem_tax.itemid',$key['id']);
    $query = $CI->db->get()->result();
    if ($query[0]->taxrate==NULL){
        $tbltotal .= '<td width="10%">0.00</td>';
    }else{
        $tbltotal .= '<td width="10%">'.$query[0]->taxrate.'</td>';
    }
    $tbltotal .= '<td width="10%">'.($key['qty']*$key['rate'])/$maxi.'</td>';
    $tbltotal .='</tr>';
  }
   $tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');


$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table style="font-size:14px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($proposal->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$proposal->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"proposal");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  
  $tbltotal .= '<tr>
      <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  $query[0]->total/$maxi . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="55%"><strong>' . _l('proposal_subtotal') . '</strong></td>
         <td align="right" width="15%">' . app_format_money($proposal->subtotal/$maxi, $proposal->currency_name) . '</td>';
      }else{
          $taxto=0;
        foreach ($items->taxes() as $tax) {
            $taxto= $taxto+ $tax['total_tax'];
            $tbltotal .= '
            <td align="right" width="55%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
            <td align="right" width="15%">' . app_format_money($tax['total_tax']/$maxi, $proposal->currency_name) . '</td>';
        }
      }
      $tbltotal .= '</tr>';
      
}
// $tbltotal .= '</table><table cellpadding="6" style="font-size:12px">';
// $tbltotal .= '
//  <tr>
//     <td align="right" width="85%"><strong>' . _l('proposal_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($proposal->subtotal/$maxi, $proposal->currency_name) . '</td>
// </tr>';

if (is_sale_discount_applied($proposal)) {
    $tbltotal .= '
    <tr>
        
        <td align="right" width="85%"><strong>' . _l('proposal_discount');
    if (is_sale_discount($proposal, 'percent')) {
        $tbltotal .= '(' . app_format_number($proposal->discount_percent/$maxi, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($proposal->discount_total/$maxi, $proposal->currency_name) . '</td>
    </tr>';
}


if (true) {
    $tbltotal .= '<tr>
    <td align="left" width="30%"><strong>Total Items=</strong>' . $qty . '</td>
    <td align="right" width="55%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($proposal->total-($taxto-$proposal->discount_total+$proposal->subtotal))/$maxi, $proposal->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('proposal_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($proposal->total/$maxi), $proposal->currency_name) . '</td>
</tr>';


if (count($proposal->payments) > 0 && get_option('show_total_paid_on_proposal') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('proposal_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'proposalpaymentrecords', [
        'field' => 'amount',
        'where' => [
            'proposalid' => $proposal->id,
        ],
    ])/$maxi, $proposal->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_proposal') == 1 && $credits_applied = total_credits_applied_to_proposal($proposal->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money($credits_applied/$maxi, $proposal->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_proposal') == 1 && $proposal->status != proposals_model::STATUS_CANCELLED) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('proposal_amount_due') . '</strong></td>
       <td align="right" width="15%">' . app_format_money($proposal->total_left_to_pay/$maxi, $proposal->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
// $pdf->SetWatermarkText('DRAFT');
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if($maxi!=1){
$pdf->AddPage();
}
}
if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($proposal->total, $proposal->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (count($proposal->payments) > 0 && get_option('show_transactions_on_proposal_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('proposal_received_payments') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="50%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1px">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('proposal_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('proposal_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('proposal_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('proposal_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($proposal->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $proposal->currency_name) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}
// die();
