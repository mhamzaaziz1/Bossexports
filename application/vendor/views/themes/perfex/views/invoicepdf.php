<?php

defined('BASEPATH') or exit('No direct script access allowed');
$pdf->SetProtection(array('modify','copy'), "", "masterBoss321", 0, null);

$i=0; $abc=0;
// $maxi=floor($invoice->total/44000);
$maxi=1;
$CI = & get_instance();
if($maxi==0){
    $maxi=1;
}
else if($maxi==1 && $CI->input->get('output_view')!="F"){
    $maxi=2;
}else if($CI->input->get('n')=="1"){
    // Set the image file path
$imageFile = "https://bossexports.co.za/wp-content/uploads/2023/05/image-removebg-preview.png";

// Get the page width and height
$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();
$pdf->SetAlpha(0.1);

// Set the image position and size
$imageX = 50; // X position
$imageY = 80; // Y position
// $imageWidth = "auto"; // Width of the image
// $imageHeight = $pageHeight; // Height of the image

// Add the image (this will be behind the text)
$pdf->Image($imageFile, $imageX, $imageY, "","", 'PNG', '', '', true, 300, '', false, false, 0, false, false, 0.05); // 50% transparency






$pdf->SetAlpha(1);
 $maxi=1;   
(int)$a = $maxi;
foreach (range( 1 , $a) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">TAX ' . _l('invoice_pdf_heading')  .'</span><br />';
// $info_right_column .= 'Invoice No. = <b style="color:#4e4e4e;">' . $invoice_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_CANCELLED && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info1=" ";
$invoice_info = '<b style="color:#424242; font-size:13px">' . _l('invoice_bill_to') . ':</b><br>';
$invoice_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
$invoice_info1 .= '<div style="font-size:14px" border="1px">Invoice # <b style="color:#4e4e4e;">' . $invoice_number.'-'.$i.'</b>';

$invoice_info1 .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br>';

// if (!empty($invoice->duedate)) {
//     $invoice_info1 .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
// }
$invoice_info1 .= '<b><u>Payment Accounts</u></b><br><b>FNB:</b> Branch : 250505 Ac No: 62025537450<br>
Swift: FIRNZAJJ031 US$ <b>AC:</b> 62747263200<br>
<b>NEDBANK</b> Branch: 128605  Ac No: 1286085373 <br>Cash Deposit <b>NED BANK Only</b></div>';
// if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $invoice_info1 .= '<div style="font-size:14px; color:#424242;" border="1px"><br><br>';
    $invoice_info1 .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info1 .= '</div>';
// }



if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info1 .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info1 .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info1 .= $field['name'] . ': ' . $value . '<br />';
}
$organization_info .=$invoice_info;
$left_info  = $swap == '1' ? $invoice_info : $organization_info;
 $right_info = $invoice_info1;
// $left_info  = $organization_info+$invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$tbltotal = '';
$CI = & get_instance();
$CI->db->select('id , number');
$CI->db->from('tblestimates');
$CI->db->where('invoiceid', $invoice->id);
$query = $CI->db->get();
$estimate = $query->result();

$CI = & get_instance();
$CI->db->select('id');
$CI->db->from('tblproposals');
$CI->db->where('estimate_id', $estimate[0]->id);
$query = $CI->db->get();
$proposal = $query->result();

//var_dump($estimate[0]->id);
  $tbltotal = '';
  $tbltotal .= '<table cellpadding="2" style="font-size:14px" border="1px" align="center" >';
  $tbltotal .= '<tr>
      <td><strong>Sales Quotes </strong></td>
      <td><strong>Sales Order </strong></td>
      <td><strong>SI REF </strong></td>
      </tr><tr>
      <td>SQ-000' .  $proposal[0]->id . '</td>
      <td>SO-000' .  $estimate[0]->number . '</td>
      <td>' .  $invoice_number . '</td>

</tr>';
$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_items_table_data($invoice, 'invoice', 'pdf');
// $tblhtml = "";


  $tbltotal='';
  $tbltotal .= '<table  style="font-size:14px" border="1px" align="center" >';
  $tbltotal .='<tr>';
  $tbltotal .='<th width="10%"><b>SKU</b></th>';
  $tbltotal .='<th width="40%"><b>Description</b></th>';
//   $tbltotal .='<th width="10%"><b>Weight</b></th>';
//   $tbltotal .='<th width="10%"><b>Volume</b></th>';
  $tbltotal .='<th width="10%"><b>Qty</b></th>';
  $tbltotal .='<th width="10%"><b>Price</b></th>';
  $tbltotal .='<th width="10%"><b>Tax</b></th>';
  $tbltotal .='<th width="20%"><b>Total</b></th>';
  $tbltotal .='</tr>';
  $qty=0;$iqty=0;
  // The items table
  $total=0; $ii=0;
  foreach ($invoice->items_full as $key ) {
      $ii +=1;
    $qty=floor($key['qty']/$maxi);
    $iqty+=$qty;
    // (/$key['qty'])*$qty
    $tbltotal .='<tr>';
    $CI->db->select("commodity_code");
    $CI->db->from('tblitems');
    $CI->db->where('tblitems.description',$key['description']);
    $sku = $CI->db->get()->result();
    $tbltotal .= '<td width="10%">'.$sku[0]->commodity_code.'</td>';
    
    $tbltotal .= '<td width="40%">'.$key['description'].'</td>';
    
   $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$invoice->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"invoice");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    // $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$invoice->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"invoice");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    
    // $qty=$qty+$key['qty']/$maxi;
    // $tbltotal .= '<td width="10%">'. round(($query[0]->value/$key['qty'])*$qty, 2) .'</td>';
    $tbltotal .= '<td width="10%">'.floor($qty).'</td>';
    $tbltotal .= '<td width="10%">'.$key['rate'].'</td>';
    $CI->db->select("taxrate");
    $CI->db->from('tblitem_tax');
    $CI->db->where('tblitem_tax.rel_id',$invoice->id);
    $CI->db->where('tblitem_tax.itemid',$key['id']);
    $query = $CI->db->get()->result();
    if ($query[0]->taxrate==NULL){
        $tbltotal .= '<td width="10%">0.00</td>';
    }else{
        $tbltotal .= '<td width="10%">'.$query[0]->taxrate.'</td>';
    }
    $tbltotal .= '<td width="20%">'.number_format(($key['rate']*$qty)+($query[0]->taxrate),2).'</td>';
    $total=$total+ $key['rate']*$qty;
    $tbltotal .='</tr>';
  }
   $tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
//$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table style="font-size:14px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($invoice->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$invoice->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"invoice");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  $invoice->subtotal=$total;
  $tbltotal .= '<tr>';
    //   <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  number_format(($query[0]->total/$key['qty'])*$qty,2) . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
         <td align="right" width="15%">' . app_format_money($total, $invoice->currency_name) . '</td>';
      }else{
          $taxto=0;
        foreach ($items->taxes() as $tax) {
            $taxto= $taxto+ number_format($tax['total_tax']);
            $tbltotal .= '
            <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
            <td align="right" width="15%">' . app_format_money(($tax['total_tax']/$key['qty'])*$qty, $invoice->currency_name) . '</td>';
        }
      }
      $tbltotal .= '</tr>';
      
}
// $tbltotal .= '</table><table cellpadding="6" style="font-size:12px">';
// $tbltotal .= '
//  <tr>
//     <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->subtotal/$maxi, $invoice->currency_name) . '</td>
// </tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . app_format_number(($invoice->discount_percent/$key['qty'])*$qty , true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money(($invoice->discount_total/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}


if (false) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money((($invoice->total-($taxto-$invoice->discount_total+$total))/$key['qty'])*$qty, $invoice->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="left" width="30%"><strong>No. of Items=</strong>' . $iqty . '</td>
    <td align="right" width="55%"><strong>Shipping</strong></td>
    <td align="right" width="15%">' . app_format_money(500, $invoice->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Total With Shipping') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($invoice->total+500, $invoice->currency_name) . '</td>
</tr>';


if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'invoicepaymentrecords', [
        'field' => 'amount',
        'where' => [
            'invoiceid' => $invoice->id,
        ],
    ])/$maxi, $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(($credits_applied/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}

// if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
//     $tbltotal .= '<tr style="background-color:#f0f0f0;">
//       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
//       <td align="right" width="15%">' . app_format_money(($invoice->total_left_to_pay/$key['qty'])*$qty+500, $invoice->currency_name) . '</td>
//   </tr>';
// }

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
if($maxi!=1){
$pdf->AddPage();
}
}
}
else if($CI->input->get('output_view')=="F"){
    // Set the image file path
$imageFile = "https://bossexports.co.za/wp-content/uploads/2023/05/image-removebg-preview.png";

// Get the page width and height
$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();
$pdf->SetAlpha(0.1);

// Set the image position and size
$imageX = 50; // X position
$imageY = 80; // Y position
// $imageWidth = "auto"; // Width of the image
// $imageHeight = $pageHeight; // Height of the image

// Add the image (this will be behind the text)
$pdf->Image($imageFile, $imageX, $imageY, "","", 'PNG', '', '', true, 300, '', false, false, 0, false, false, 0.05); // 50% transparency






$pdf->SetAlpha(1);
 $maxi=1;   
(int)$a = $maxi;
foreach (range( 1 , $a) as $i) {
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">TAX ' . _l('invoice_pdf_heading')  .'</span><br />';
// $info_right_column .= 'Invoice No. = <b style="color:#4e4e4e;">' . $invoice_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_CANCELLED && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info1=" ";
$invoice_info = '<b style="color:#424242; font-size:13px">' . _l('invoice_bill_to') . ':</b><br>';
$invoice_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
$invoice_info1 .= '<div style="font-size:14px" border="1px">Invoice # <b style="color:#4e4e4e;">' . $invoice_number.'-'.$i.'</b>';

$invoice_info1 .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br>';

// if (!empty($invoice->duedate)) {
//     $invoice_info1 .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
// }
$invoice_info1 .= '<b><u>Payment Accounts</u></b><br><b>FNB:</b> Branch : 250505 Ac No: 62025537450<br>
Swift: FIRNZAJJ031 US$ <b>AC:</b> 62747263200<br>
<b>NEDBANK</b> Branch: 128605  Ac No: 1286085373 <br>Cash Deposit <b>NED BANK Only</b></div>';
// if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $invoice_info1 .= '<div style="font-size:14px; color:#424242;" border="1px"><br><br>';
    $invoice_info1 .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info1 .= '</div>';
// }



if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info1 .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info1 .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info1 .= $field['name'] . ': ' . $value . '<br />';
}
$organization_info .=$invoice_info;
$left_info  = $swap == '1' ? $invoice_info : $organization_info;
 $right_info = $invoice_info1;
// $left_info  = $organization_info+$invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$tbltotal = '';
$CI = & get_instance();
$CI->db->select('id , number');
$CI->db->from('tblestimates');
$CI->db->where('invoiceid', $invoice->id);
$query = $CI->db->get();
$estimate = $query->result();

$CI = & get_instance();
$CI->db->select('id');
$CI->db->from('tblproposals');
$CI->db->where('estimate_id', $estimate[0]->id);
$query = $CI->db->get();
$proposal = $query->result();

//var_dump($estimate[0]->id);
  $tbltotal = '';
  $tbltotal .= '<table cellpadding="2" style="font-size:14px" border="1px" align="center">';
  $tbltotal .= '<tr>
      <td><strong>Sales Quotes </strong></td>
      <td><strong>Sales Order </strong></td>
      <td><strong>SI REF </strong></td>
      </tr><tr>
      <td>SQ-000' .  $proposal[0]->id . '</td>
      <td>SO-000' .  $estimate[0]->number . '</td>
      <td>' .  $invoice_number . '</td>

</tr>';
$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_items_table_data($invoice, 'invoice', 'pdf');
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
  $qty=0;$iqty=0;
  // The items table
  $total=0; $ii=0;
  foreach ($invoice->items_full as $key ) {
      $ii +=1;
    $qty=floor($key['qty']/$maxi);
    $iqty+=$qty;
    // (/$key['qty'])*$qty
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
    $CI->db->where('tblitemable.rel_id',$invoice->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"invoice");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    $tbltotal .= '<td width="10%">'.$query[0]->value/$maxi.'</td>';
    $CI->db->select("sum(value) as value");
    $CI->db->from('tblitemable');
    $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
    $CI->db->where('tblitemable.rel_id',$invoice->id);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $CI->db->where('tblitemable.description',$key['description']);
    $CI->db->where('tblitemable.rel_type',"invoice");
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    
    // $qty=$qty+$key['qty']/$maxi;
    $tbltotal .= '<td width="10%">'. round(($query[0]->value/$key['qty'])*$qty, 2) .'</td>';
    $tbltotal .= '<td width="10%">'.floor($qty).'</td>';
    $tbltotal .= '<td width="10%">'.$key['rate'].'</td>';
    $CI->db->select("taxrate");
    $CI->db->from('tblitem_tax');
    $CI->db->where('tblitem_tax.rel_id',$invoice->id);
    $CI->db->where('tblitem_tax.itemid',$key['id']);
    $query = $CI->db->get()->result();
    if ($query[0]->taxrate==NULL){
        $tbltotal .= '<td width="10%">0.00</td>';
    }else{
        $tbltotal .= '<td width="10%">'.$query[0]->taxrate.'</td>';
    }
    $tbltotal .= '<td width="10%">'.number_format(($key['rate']*$qty)+($query[0]->taxrate),2).'</td>';
    $total=$total+ $key['rate']*$qty;
    $tbltotal .='</tr>';
  }
   $tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
//$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table style="font-size:14px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($invoice->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$invoice->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"invoice");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  $invoice->subtotal=$total;
  $tbltotal .= '<tr>
      <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  number_format(($query[0]->total/$key['qty'])*$qty,2) . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="55%"><strong>' . _l('invoice_subtotal') . '</strong></td>
         <td align="right" width="15%">' . app_format_money($total, $invoice->currency_name) . '</td>';
      }else{
          $taxto=0;
        foreach ($items->taxes() as $tax) {
            $taxto= $taxto+ number_format($tax['total_tax']);
            $tbltotal .= '
            <td align="right" width="55%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
            <td align="right" width="15%">' . app_format_money(($tax['total_tax']/$key['qty'])*$qty, $invoice->currency_name) . '</td>';
        }
      }
      $tbltotal .= '</tr>';
      
}
// $tbltotal .= '</table><table cellpadding="6" style="font-size:12px">';
// $tbltotal .= '
//  <tr>
//     <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->subtotal/$maxi, $invoice->currency_name) . '</td>
// </tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . app_format_number(($invoice->discount_percent/$key['qty'])*$qty , true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money(($invoice->discount_total/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}


if (false) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money((($invoice->total-($taxto-$invoice->discount_total+$total))/$key['qty'])*$qty, $invoice->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="left" width="30%"><strong>No. of Items=</strong>' . $iqty . '</td>
    <td align="right" width="55%"><strong>Shipping</strong></td>
    <td align="right" width="15%">' . app_format_money(500, $invoice->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Total With Shipping') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($invoice->total+500, $invoice->currency_name) . '</td>
</tr>';


if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'invoicepaymentrecords', [
        'field' => 'amount',
        'where' => [
            'invoiceid' => $invoice->id,
        ],
    ])/$maxi, $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(($credits_applied/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Shipping Expense') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($invoice->ship_expense/$maxi), $estimate->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Other Expense') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($invoice->other_expense/$maxi), $estimate->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('Total All') . '</strong></td>
    <td align="right" width="15%">' . app_format_money(($invoice->total+$invoice->ship_expense+$invoice->other_expense/$maxi), $invoice->currency_name) . '</td>
</tr>';

                              $CI =&get_instance();
                                      // Invoiced amount during the period
                                        $result['invoiced_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoices.total) as invoiced_amount
                                        FROM ' . db_prefix() . 'invoices
                                        WHERE clientid = ' . $invoice->clientid . '
                                        AND status != ' . Invoices_model::STATUS_DRAFT . ' AND status != ' . Invoices_model::STATUS_CANCELLED . '')
                                            ->row()->invoiced_amount;
                                
                                        if ($result['invoiced_amount'] === null) {
                                            $result['invoiced_amount'] = 0;
                                        }
                                
                                        $result['credit_notes_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'creditnotes.total) as credit_notes_amount
                                        FROM ' . db_prefix() . 'creditnotes
                                        WHERE clientid = ' . $invoice->clientid)->row()->credit_notes_amount;
                                
                                        if ($result['credit_notes_amount'] === null) {
                                            $result['credit_notes_amount'] = 0;
                                        }
                                        $result['refunds_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'creditnote_refunds.amount) as refunds_amount
                                        FROM ' . db_prefix() . 'creditnote_refunds
                                        WHERE credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes where clientid=' . $invoice->clientid . ')')->row()->refunds_amount;
                                
                                        if ($result['refunds_amount'] === null) {
                                            $result['refunds_amount'] = 0;
                                        }
                                
                                        $result['invoiced_amount'] = $result['invoiced_amount'];
                                
                                        // Amount paid during the period
                                        $result['amount_paid'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
                                        FROM ' . db_prefix() . 'invoicepaymentrecords
                                        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
                                        WHERE ' . db_prefix() . 'invoices.clientid = ' . $invoice->clientid)
                                            ->row()->amount_paid;
                                
                                        if ($result['amount_paid'] === null) {
                                            $result['amount_paid'] = 0;
                                        }
                                        
                                        
                                        $result['direct_paid'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
                                        FROM ' . db_prefix() . 'invoicepaymentrecords
                                        WHERE client_id = ' . $invoice->clientid)
                                            ->row()->amount_paid;
                                        if ($result['direct_paid'] === null) {
                                            $result['direct_paid'] = 0;
                                        }
                                
                                
                                            $result['beginning_balance'] = 0;
                                            $abc =  ($CI->db->select("balance")->from('tblclients')->where('userid', $invoice->clientid)->get()->result());
                                        $result['beginning_balance'] += (float)$abc[0]->balance;
                                        
                                        $sql_expense = 'SELECT
                                        SUM(' . db_prefix() . 'expenses.amount) as invoice_amount
                                        FROM ' . db_prefix() . 'expenses
                                        WHERE '. db_prefix() . 'expenses.clientid = ' . $invoice->clientid . '
                                        ORDER by ' . db_prefix() . 'expenses.date DESC';
                                
                                        $sumexpense = $CI->db->query($sql_expense)->row()->invoice_amount;
                                        if ($sumexpense==NULL){
                                            $sumexpense=0;
                                        }
                                        $dec = get_decimal_places();
                                        
                                            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
                                            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
                                            $result['balance_due'] = $result['balance_due'] - number_format($result['direct_paid'], $dec, '.', '');
                                            // var_dump($CI->db->last_query());die;
                                            $result['balance_due'] = $result['balance_due'] - number_format($result['refunds_amount'], $dec, '.', '');
                                        $result['balance_due']=$result['balance_due'] + $sumexpense;
                                        
                              $tbltotal .= '<tr><td align="right" width="85%"><b>Total Balance:</b></td>
                                 <td align="right" width="15%">'.round($result['balance_due'] + $invoice->ship_expense+$invoice->other_expense/$maxi).'</td>
                                 
                              </tr>';

// if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
//     $tbltotal .= '<tr style="background-color:#f0f0f0;">
//       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
//       <td align="right" width="15%">' . app_format_money(($invoice->total_left_to_pay/$key['qty'])*$qty+500, $invoice->currency_name) . '</td>
//   </tr>';
// }

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
if($maxi!=1){
$pdf->AddPage();
}
}
}

// Set the image file path
$imageFile = "https://bossexports.co.za/wp-content/uploads/2023/05/image-removebg-preview.png";

// Get the page width and height
$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();
$pdf->SetAlpha(0.1);

// Set the image position and size
$imageX = 50; // X position
$imageY = 80; // Y position
// $imageWidth = "auto"; // Width of the image
// $imageHeight = $pageHeight; // Height of the image

// Add the image (this will be behind the text)
$pdf->Image($imageFile, $imageX, $imageY, "","", 'PNG', '', '', true, 300, '', false, false, 0, false, false, 0.05); // 50% transparency






$pdf->SetAlpha(1);
// foreach ($invoice->items as $key ) {
//     $qty=intval($key['qty']%$maxi);
//     $total=$total+ $key['rate']*$qty;
// }
// $maximum= $ii;
// if ($maximum <1){
//     $maximum=1;
// }
if($maxi!=1){
$data=0;
// foreach (range($maxi+1, $maxi+$maximum)as $b){
if (1){
if($maxi!=0){
    $maximum= $ii;
if ($maximum <1){
    $maximum=1;
}
foreach ($invoice->items as $key) {
    // Set the image file path
$imageFile = "https://bossexports.co.za/wp-content/uploads/2023/05/image-removebg-preview.png";

// Get the page width and height
$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();
$pdf->SetAlpha(0.1);

// Set the image position and size
$imageX = 50; // X position
$imageY = 80; // Y position
// $imageWidth = "auto"; // Width of the image
// $imageHeight = $pageHeight; // Height of the image

// Add the image (this will be behind the text)
$pdf->Image($imageFile, $imageX, $imageY, "","", 'PNG', '', '', true, 300, '', false, false, 0, false, false, 0.05); // 50% transparency






$pdf->SetAlpha(1);
    $abc=$abc+1;
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:24px;">TAX ' . _l('invoice_pdf_heading') . '</span><br />';
// $info_right_column .= 'Invoice No. = <b style="color:#4e4e4e;">' . $invoice_number.'-'.$i. '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    //$info_right_column .= '<span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_CANCELLED && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$organization_info = '<div style="color:#424242; font-size:14px" border="1px">';

$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info1=" ";
$invoice_info = '<b style="color:#424242; font-size:13px">' . _l('invoice_bill_to') . ':</b><br>';
$invoice_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
// $maxi = $maxi+1;
$invoice_info1 .= '<div style="font-size:14px" border="1px"> Invoice # <b style="color:#4e4e4e;">' . $invoice_number.'-'.($abc).'</b>';

$invoice_info1 .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br>';

// if (!empty($invoice->duedate)) {
//     $invoice_info1 .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
// }
$invoice_info1 .= '<b><u>Payment Accounts</u></b><br><b>FNB:</b> Branch : 250505 Ac No: 62025537450<br>
Swift: FIRNZAJJ031 US$ <b>AC:</b> 62747263200<br>
<b>NEDBANK</b> Branch: 128605  Ac No: 1286085373 <br>Cash Deposit <b>NED BANK Only</b></div>';
// if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info1 .= '<b style="font-size:14px">' . _l('ship_to') . ':</b><br>';
    $invoice_info1 .= '<div style="font-size:14px; color:#424242;" border="1px"><br><br>';
    $invoice_info1 .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info1 .= '</div>';
// }



if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info1 .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info1 .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info1 .= $field['name'] . ': ' . $value . '<br />';
}
$organization_info .=$invoice_info;
$left_info  = $swap == '1' ? $invoice_info : $organization_info;
 $right_info = $invoice_info1;
// $left_info  = $organization_info+$invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$tbltotal = '';
$CI = & get_instance();
$CI->db->select('id,number');
$CI->db->from('tblestimates');
$CI->db->where('invoiceid', $invoice->id);
$query = $CI->db->get();
$estimate = $query->result();

$CI = & get_instance();
$CI->db->select('id');
$CI->db->from('tblproposals');
$CI->db->where('estimate_id', $estimate[0]->id);
$query = $CI->db->get();
$proposal = $query->result();

//var_dump($estimate[0]->id);
  $tbltotal = '';
  $tbltotal .= '<table cellpadding="2" style="font-size:14px" border="1px" align="center">';
  $tbltotal .= '<tr>
      <td><strong>Sales Quotes </strong></td>
      <td><strong>Sales Order </strong></td>
      <td><strong>SI REF</strong></td>
      </tr><tr>
      <td>SQ-000' .  $proposal[0]->id . '</td>
      <td>SO-000' .  $estimate[0]->number . '</td>
      <td>' .  $invoice_number . '</td>

</tr>';
$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_items_table_data($invoice, 'invoice', 'pdf');
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
  $total=0;
//   foreach ($invoice->items as $key )
  {
    // $key=$invoice->items[$data];
    
    $billed_qty=(floor($key['qty']/$maxi)*$maxi);
    $qty=$key['qty'];
    // $qty=$key['qty'] - $billed_qty;
    for($b;$qty<1 && $b<=$ii;$b++){
    // $tbltotal .=$b;    
        // $b++;
        $key=$invoice->items[$data];
        $billed_qty=(floor($key['qty']/$maxi)*$maxi);
        $qty=$key['qty'] - $billed_qty;
        $data++;
    }
    $tbltotal .='<tr>';
    $CI->db->select("commodity_code");
    $CI->db->from('tblitems');
    $CI->db->where('tblitems.description',$key['description']);
    $sku = $CI->db->get()->result();
    $tbltotal .= '<td width="10%">'.$sku[0]->commodity_code.'</td>';
    $check=0;
    $qty<0?$check=1:$check=0;
    $tbltotal .= '<td width="30%">'.$key['description'].'</td>';
    
    $CI->db->select("value");
    $CI->db->from('tblcustomfieldsvalues');
    $CI->db->where('tblcustomfieldsvalues.relid',$key['id']);
    $CI->db->where('tblcustomfieldsvalues.fieldid',1);
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    $tbltotal .= '<td width="10%">'.round($query[0]->value, 2).'</td>';
    
    $CI->db->select("value");
    $CI->db->from('tblcustomfieldsvalues');
    $CI->db->where('tblcustomfieldsvalues.relid',$key['id']);
    $CI->db->where('tblcustomfieldsvalues.fieldid',5);
    $query = $CI->db->get()->result();
    if ($query[0]->value==""){
        $query[0]->value=0;
    }
    
    
    // $qty=$qty+$key['qty']/$maxi;
    $tbltotal .= '<td width="10%">'. round($query[0]->value, 2) .'</td>';
    $tbltotal .= '<td width="10%">'.$qty.'</td>';
    $tbltotal .= '<td width="10%">'.$key['rate'].'</td>';
    $CI->db->select("taxrate");
    $CI->db->from('tblitem_tax');
    $CI->db->where('tblitem_tax.rel_id',$invoice->id);
    $CI->db->where('tblitem_tax.itemid',$key['id']);
    $query = $CI->db->get()->result();
    if ($query[0]->taxrate==NULL){
        $tbltotal .= '<td width="10%">0.00</td>';
    }else{
        $tbltotal .= '<td width="10%">'.$query[0]->taxrate.'</td>';
    }
    $tbltotal .= '<td width="10%">'.$key['rate']*$qty.'</td>';
    $total=$total+ $key['rate']*$qty;
    $tbltotal .='</tr>';
  }
   $tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
//$pdf->writeHTML($tblhtml, true, false, false, false, '');
if($data<$ii){
        $data++;
    }

$pdf->Ln(1);

$tbltotal = '';
$tbltotal .= '<table style="font-size:14px; border-width:5px;  border-style:solid;">';
$CI = & get_instance();
$CI->load->model('invoices_model');
$sum_custom = $CI->invoices_model->sumcustom($invoice->id);
$tbltotal .= '<table  style="font-size:14px" border="1px">';
foreach ($sum_custom as $custom){
  $CI->db->select("fieldid,sum(value) as total");
        $CI->db->from('tblitemable');
        $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
        $CI->db->where('tblitemable.rel_id',$invoice->id);
        $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
        $CI->db->where('tblitemable.rel_type',"invoice");
        $CI->db->where('tblcustomfieldsvalues.value!=1');
        $CI->db->order_by("fieldid","asc");
        $query = $CI->db->get()->result();
        if($query[0]->total==""){
            $query[0]->total=0;
        }
        
  $invoice->subtotal=$total;
  $tbltotal .= '<tr>
      <td align="left" width="30%"><strong>Total ' .  $custom->name . '=   </strong>' .  ($query[0]->total/$key['qty'])*$qty . '</td>';
      if($custom->name=="Weight"){
      $tbltotal .= '<td align="right" width="55%"><strong>' . _l('invoice_subtotal') . '</strong></td>
         <td align="right" width="15%">' . app_format_money($total, $invoice->currency_name) . '</td>';
      }else{
          $taxto=0;
        foreach ($items->taxes() as $tax) {
            $taxto= $taxto+ $tax['total_tax'];
            $tbltotal .= '
            <td align="right" width="55%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
            <td align="right" width="15%">' . app_format_money(($tax['total_tax']/$key['qty'])*$qty, $invoice->currency_name) . '</td>';
        }
      }
      $tbltotal .= '</tr>';
      
}
// $tbltotal .= '</table><table cellpadding="6" style="font-size:12px">';
// $tbltotal .= '
//  <tr>
//     <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->subtotal/$maxi, $invoice->currency_name) . '</td>
// </tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . app_format_number(($invoice->discount_percent/$key['qty'])*$qty , true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money(($invoice->discount_total/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}


if (false) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money((($invoice->total-($taxto-$invoice->discount_total+$total))/$key['qty'])*$qty, $invoice->currency_name) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="left" width="30%"><strong>No. of Items=</strong>'.$qty.'</td>
    <td align="right" width="55%"><strong>Shipping</strong></td>
    <td align="right" width="15%">' . app_format_money(500, $invoice->currency_name) . '</td>
</tr>';
$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($total+500, $invoice->currency_name) . '</td>
</tr>';


if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'invoicepaymentrecords', [
        'field' => 'amount',
        'where' => [
            'invoiceid' => $invoice->id,
        ],
    ])/$maxi, $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . app_format_money(($credits_applied/$key['qty'])*$qty, $invoice->currency_name) . '</td>
    </tr>';
}

// if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
//     $tbltotal .= '<tr style="background-color:#f0f0f0;">
//       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
//       <td align="right" width="15%">' . app_format_money(($invoice->total_left_to_pay/$key['qty'])*$qty+500, $invoice->currency_name) . '</td>
//   </tr>';
// }

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');
// if($b<$maxi+$maximum-1){
    $pdf->AddPage();
// }
}
}

// if (get_option('total_to_words_enabled') == 1) {
//     // Set the font bold
//     $pdf->SetFont($font_name, 'B', $font_size);
//     $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, false, true, 'C', true);
//     // Set the font again to normal like the rest of the pdf
//     $pdf->SetFont($font_name, '', $font_size);
//     $pdf->Ln(4);
// }

if (0) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="50%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1px">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $invoice->currency_name) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (0) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}
}
}

if (1) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (1) {
    $invoice_info="";
    $invoice_info = '<b style="color:#424242; font-size:14px">' . _l('terms_and_conditions') . ':</b><br>';
    $invoice_info .= '<div style="font-size:14px; color:#424242;" border="1px">';
    $invoice_info .= $invoice->terms;
    $invoice_info .= '</div>';
    $pdf->writeHTMLCell('', '', '', '',$invoice_info , 0, 1, false, true, 'L', true);
    
}
// die();
