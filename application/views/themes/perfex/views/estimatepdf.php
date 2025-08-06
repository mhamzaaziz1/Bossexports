<?php
$CI = & get_instance();
defined('BASEPATH') or exit('No direct script access allowed');
if($CI->input->get('type')=='pslip'){
$i=0;
$maxi=(int)(O/49500)+1;
if($maxi<=0){
    $maxi=1;
}
foreach (range( $maxi , 1) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;" border="1px">' . _l('Sales Order') . '</span><br />';
// $info_right_column .= 'estimate No. = <b style="color:#4e4e4e;">' . $estimate_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . estimate_status_color_pdf($status) . ');text-transform:uppercase;">' . format_estimate_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
// pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$estimate_info = '<b style="color:#424242; font-size:13px">' . _l('To') . ':</b><br>';
$estimate_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $estimate_info .= format_customer_info($estimate, 'estimate', 'billing');
$estimate_info .= '</div>';

// ship to to
$estimate_info1 .= '<div style="font-size:150px" align="center"><b style="color:#4e4e4e;">' . format_estimate_number($estimate->id).'</b>';

$estimate_info1 .= ' </div>';
$organization_info .=$estimate_info;
$left_info  = $swap == '1' ? $estimate_info : $organization_info;
 $right_info = $estimate_info1;

// pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$pdf->writeHTML($estimate_info1, true, false, false, false, '');
// $estimate_info11='<div style="font-size:18px" align="center"><br />' . _l('Date') . ' ' . _d($estimate->date).'</div>';
// $pdf->writeHTML($estimate_info11, true, false, false, false, '');
// The items table
$items = get_items_table_data($estimate, 'estimate', 'pdf');

$pdf->writeHTML($tbltotal, true, false, false, false, '');


$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<br><br><br><br><br><br><br><br><br><br><br><table style="font-size:24px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($estimate->id);
$tbltotal .= '<table  style="font-size:34px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$estimate->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"estimate");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  
  $tbltotal .= '<tr>
      <td align="left" width="30%" style="font-size:48px;"><strong>Total ' .  $custom->name . '=   </strong>' .  $query[0]->total/$maxi . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="left" width="70%"><b>Packer:</b><br><br><br><br><br></td>';
      }else{
      $tbltotal .= '<td align="left" width="70%"><b>Checker:</b><br><br><br><br><br></td>';
      }
      $tbltotal .= '</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="left" width="30%"><strong>Total Items=</strong>' . $qty . '</td>
</tr>';
$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
}
}
else if($CI->input->get('type')=='performa'){
$i=0;
$maxi=(int)(O/49500)+1;
if($maxi<=0){
    $maxi=1;
}
foreach (range( $maxi , 1) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">' . _l('Packing Slip') . '</span><br />';
// $info_right_column .= 'estimate No. = <b style="color:#4e4e4e;">' . $estimate_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . estimate_status_color_pdf($status) . ');text-transform:uppercase;">' . format_estimate_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$estimate_info = '<b style="color:#424242; font-size:13px">' . _l('To') . ':</b><br>';
$estimate_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $estimate_info .= format_customer_info($estimate, 'estimate', 'billing');
$estimate_info .= '</div>';

// ship to to
$estimate_info1 .= '<div style="font-size:14px" border="1px"> Sales Order # <b style="color:#4e4e4e;">' . format_estimate_number($estimate->id).'</b>';
$estimate_info1 .= '<br />' . _l('Valid Till') . ' ' . _d($estimate->duedate) ;
$estimate_info1 .= '<br />   ' . _l('Date') . ' ' . _d($estimate->date) . '   Reference= '.$estimate->reference_no;

// if (!empty($estimate->duedate)) {
//     $estimate_info1 .= _l('estimate_data_duedate') . ' ' . _d($estimate->duedate) . '<br />';
// }
$estimate_info1 .= '<p style="color:white; font-size:11.4px">.</p></div>';
if (true) {
    $estimate_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $estimate_info1 .= '<div style="font-size:14px; color:#424242;" border="1px"><br><br>';
    $estimate_info1 .= format_customer_info($estimate, 'estimate', 'shipping');
    $estimate_info1 .= '</div>';
}
$organization_info .=$estimate_info;
$left_info  = $swap == '1' ? $estimate_info : $organization_info;
 $right_info = $estimate_info1;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
//$pdf->writeHTML($tbltotal, true, false, false, false, '');
// The items table
$items = get_items_table_data($estimate, 'estimate', 'pdf');
// $tblhtml = "";
  $tbltotal='';
  $tbltotal .= '<table  style="font-size:18px" border="1px" align="center">';
  $tbltotal .='<tr>';
  $tbltotal .='<th width="20%"><b>SKU</b></th>';
  $tbltotal .='<th width="40%"><b>Description</b></th>';
  $tbltotal .='<th width="10%"><b>Weight</b></th>';
  $tbltotal .='<th width="10%"><b>Volume</b></th>';
  $tbltotal .='<th width="20%"><b>Qty</b></th>';

  $tbltotal .='</tr>';
  $qty=0;
  // The items table
  foreach ($estimate->items as $key ) {
    $tbltotal .='<tr>';
    $CI->db->select("commodity_code");
    $CI->db->from('tblitems');
    $CI->db->where('tblitems.description',$key['description']);
    $sku = $CI->db->get()->result();
    $tbltotal .= '<td width="20%">'.$sku[0]->commodity_code.'</td>';
    $tbltotal .= '<td width="40%">'.$key['description'].'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$estimate->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"estimate");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$estimate->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"estimate");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    $qty=$qty+$key['qty']/$maxi;
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $tbltotal .= '<td width="20%">'.$key['qty']/$maxi.'</td>';
    $tbltotal .='</tr>';
  }
   $tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');


$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table style="font-size:14px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($estimate->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$estimate->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"estimate");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  
  $tbltotal .= '<tr>
      <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  $query[0]->total/$maxi . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="55%"> </td>
         <td align="right" width="15%"></td>';
      }else{
          $taxto=0;
        foreach ($items->taxes() as $tax) {
            $taxto= $taxto+ $tax['total_tax'];
            $tbltotal .= '
            <td align="right" width="55%"> </td>
            <td align="right" width="15%"> </td>';
        }
      }
      $tbltotal .= '</tr>';
      
}
if (false) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->total-($taxto-$estimate->discount_total+$estimate->subtotal))/$maxi, $estimate->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="left" width="30%"><strong>Total Items=</strong>' . $qty . '</td>
</tr>';


if (count($estimate->payments) > 0 && get_option('show_total_paid_on_estimate') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'estimatepaymentrecords', [
        'field' => 'amount',
        'where' => [
            'estimateid' => $estimate->id,
        ],
    ])/$maxi, $estimate->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_estimate') == 1 && $credits_applied = total_credits_applied_to_estimate($estimate->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money($credits_applied/$maxi, $estimate->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_estimate') == 1 && $estimate->status != estimates_model::STATUS_CANCELLED) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('estimate_amount_due') . '</strong></td>
       <td align="right" width="15%">' . app_format_money($estimate->total_left_to_pay/$maxi, $estimate->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
if($maxi!=1){
$pdf->AddPage();
}
}
if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($estimate->total, $estimate->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (count($estimate->payments) > 0 && get_option('show_transactions_on_estimate_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('estimate_received_payments') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="50%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1px">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($estimate->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $estimate->currency_name) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}
}
else if($CI->input->get('type')!='performa'){
$i=0;
$maxi=(int)(O/49500)+1;
if($maxi<=0){
    $maxi=1;
}
$tbltotal='<style>
body {background: url("https://app.bossexports.co.za/uploads/company/481b267630d99744792ca1fff9fbe6ca.jpg");
background-repeat: repeat-y;
background-position: center;
background-attachment: fixed;
background-size: 100%;
}
</style>';

foreach (range( $maxi , 1) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">' . _l('Sales Order') . '</span><br />';
// $info_right_column .= 'estimate No. = <b style="color:#4e4e4e;">' . $estimate_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . estimate_status_color_pdf($status) . ');text-transform:uppercase;">' . format_estimate_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$estimate_info = '<b style="color:#424242; font-size:13px">' . _l('To') . ':</b><br>';
$estimate_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $estimate_info .= format_customer_info($estimate, 'estimate', 'billing');
$estimate_info .= '</div>';

// ship to to
$estimate_info1 .= '<div style="font-size:14px" border="1px"> Sales Order # <b style="color:#4e4e4e;">' . format_estimate_number($estimate->id).'-'.$estimate->reference_no.'</b>';

$estimate_info1 .= '<br />' . _l('Date') . ' ' . _d($estimate->date);
$estimate_info1 .= '   ' . _l('Valid Till') . ' ' . _d($estimate->expirydate) . '<br>' ;


// if (!empty($estimate->duedate)) {
//     $estimate_info1 .= _l('estimate_data_duedate') . ' ' . _d($estimate->duedate) . '<br />';
// }
$estimate_info1 .= '<b><u>Payment Accounts</u></b><br><b>FNB:</b> Branch : 250505 Ac No: 62025537450<br>
Swift: FIRNZAJJ031 US$ <b>AC:</b> 62747263200<br>
<b>NEDBANK</b> Branch: 128605  Ac No: 1286085373 <br>Cash Deposit <b>NED BANK Only</b></div>';
if (true) {
    $estimate_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $estimate_info1 .= '<div style="font-size:14px; color:#424242;" border="1px"><br><br>';
    $estimate_info1 .= format_customer_info($estimate, 'estimate', 'shipping');
    $estimate_info1 .= '</div>';
}
$organization_info .=$estimate_info;
$left_info  = $swap == '1' ? $estimate_info : $organization_info;
 $right_info = $estimate_info1;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
//$pdf->writeHTML($tbltotal, true, false, false, false, '');
// The items table
$items = get_items_table_data($estimate, 'estimate', 'pdf');
// $tblhtml = "";
  $tbltotal='';
  $tbltotal .= '<table  style="font-size:14px" border="1px" align="center">';
  $tbltotal .='<tr >';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>SKU</b></th>';
  $tbltotal .='<th style="font-size:14px" width="30%"><b>Description</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Weight</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Volume</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Qty</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Price</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Tax</b></th>';
  $tbltotal .='<th style="font-size:14px" width="10%"><b>Total</b></th>';
  $tbltotal .='</tr>';
  $qty=0;
  // The items table
  foreach ($estimate->items as $key ) {
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
    $CI->db->where('tblitemable.rel_id',$estimate->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"estimate");
    $CI->db->group_by('tblitemable.description');
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$estimate->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"estimate");
    $CI->db->group_by('tblitemable.description');
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
    $CI->db->where('tblitem_tax.rel_id',$estimate->id);
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
$sum_custom = $CI->invoices_model->sumcustom($estimate->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$estimate->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"estimate");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  
  $tbltotal .= '<tr>
      <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  $query[0]->total/$maxi . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="55%"><strong>' . _l('estimate_subtotal') . '</strong></td>
         <td align="right" width="15%">' . app_format_money($estimate->subtotal/$maxi, $estimate->currency_name) . '</td>';
      }
      $tbltotal .= '
            <td align="right" width="55%"><strong>vat</strong></td>
            <td align="right" width="15%">' . app_format_money($estimate->total_tax/$maxi, $estimate->currency_name) . '</td>';
      $tbltotal .= '</tr>';
      
}
// $tbltotal .= '</table><table cellpadding="6" style="font-size:12px">';
// $tbltotal .= '
//  <tr>
//     <td align="right" width="85%"><strong>' . _l('estimate_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($estimate->subtotal/$maxi, $estimate->currency_name) . '</td>
// </tr>';

if (is_sale_discount_applied($estimate)) {
    $tbltotal .= '
    <tr>
        
        <td align="right" width="85%"><strong>' . _l('estimate_discount');
    if (is_sale_discount($estimate, 'percent')) {
        $tbltotal .= '(' . app_format_number($estimate->discount_percent/$maxi, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($estimate->discount_total/$maxi, $estimate->currency_name) . '</td>
    </tr>';
}


if (true) {
    $tbltotal .= '<tr>
    <td align="left" width="30%"><strong>Total Items=</strong>' . $qty . '</td>
    <td align="right" width="55%"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->total-($estimate->total_tax-$estimate->discount_total+$estimate->subtotal))/$maxi, $estimate->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('estimate_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->total/$maxi), $estimate->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Shipping Expense') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->ship_expense/$maxi), $estimate->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Other Expense') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->other_expense/$maxi), $estimate->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Total All') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($estimate->total+$estimate->ship_expense+$estimate->other_expense/$maxi), $estimate->currency_name) . '</td>
</tr>';

if (count($estimate->payments) > 0 && get_option('show_total_paid_on_estimate') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'estimatepaymentrecords', [
        'field' => 'amount',
        'where' => [
            'estimateid' => $estimate->id,
        ],
    ])/$maxi, $estimate->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_estimate') == 1 && $credits_applied = total_credits_applied_to_estimate($estimate->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money($credits_applied/$maxi, $estimate->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_estimate') == 1 && $estimate->status != estimates_model::STATUS_CANCELLED) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('estimate_amount_due') . '</strong></td>
       <td align="right" width="15%">' . app_format_money($estimate->total_left_to_pay/$maxi, $estimate->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
if($maxi!=1){
$pdf->AddPage();
}
}
if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($estimate->total, $estimate->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (count($estimate->payments) > 0 && get_option('show_transactions_on_estimate_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('estimate_received_payments') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="50%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1px">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('estimate_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($estimate->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $estimate->currency_name) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}
// die();
}