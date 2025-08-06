<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'tblpur_order_payment.id as pur_order_name',
    _l('transactionid'),
    'Date as order_date',
    'COALESCE(tblpur_orders.Vendor,tblpur_order_payment.vendor) as vendor',
    'pur_order',
    'SUM(Amount) as total',
];

if(isset($vendor)){
    $aColumns = [
        'pur_order_name',
        'total',
        'total_tax',
        'vendor',
        'order_date',
        'subtotal',
        'approve_status',

    ];
}

$sIndexColumn = 'id';
$sTable       = db_prefix().'pur_order_payment';
// $join         = ['LEFT JOIN '.db_prefix().'pur_vendor ON '.db_prefix().'pur_vendor.userid = '.db_prefix().'pur_order_payment.vendor'];
$join =[];
array_push($join, '   left JOIN '.db_prefix().'pur_orders ON '.db_prefix().'pur_orders.id = '.db_prefix().'pur_order_payment.pur_order');
$i = 0;

// var_dump($this->ci->input->post());

$where=[];
if(isset($vendor)){
    array_push($where, ' AND COALESCE(tblpur_orders.Vendor,tblpur_order_payment.vendor) = '.$vendor);
}
if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND date <= "'.date("Y-m-d", strtotime($this->ci->input->post('to_date'))).'"');
}

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND date >= "'.date("Y-m-d", strtotime($this->ci->input->post('from_date'))).'"');
}

if ($this->ci->input->post('amount')
    && $this->ci->input->post('amount') != '') {
    $having=' having SUM(Amount) = ' . $this->ci->input->post('amount');
}


if ($this->ci->input->post('vendor')
    && count($this->ci->input->post('vendor')) > 0) {
    array_push($where, 'AND COALESCE(tblpur_orders.Vendor,tblpur_order_payment.vendor) IN (' . implode(',', $this->ci->input->post('vendor')) . ')');
}
array_push($where, 'group by tblpur_order_payment.Date, tblpur_order_payment.transactionid'.$having);


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'pur_order_payment.id as id']);
// var_dump($this->ci->db->last_query());die;

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
        $_data = $aRow[strafter($aColumns[$i], 'as ')];
    } else {
        $_data = $aRow[$aColumns[$i]];
    }

    $_data = app_format_money($aRow['total'], '');
    $numberOutput = '';
    $numberOutput = '<a href="#" >'.$aRow['pur_order_name'].'</a>';
    $row[] = $numberOutput;
    $row[] = _d($aRow['order_date']);

    if($aRow['vendor']!="0"){
        $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" >' .  wh_get_vendor_company_name($aRow['vendor']) . '</a>';
    }else{
        $vendor=$this->ci->db->select('*')->from('tblpur_orders')->where('id',$aRow['pur_order'])->get()-> row()->vendor;
        // var_dump($this->ci->db->last_query()); die;
        $row[] = '<a href="' . admin_url('purchase/vendor/' . $vendor) . '" >'.wh_get_vendor_company_name($vendor).'</a>';
    }
    $row[] = $aRow['transactionid'];
    $row[] = $aRow['total'];
    $row[] = '<a href="' . admin_url('purchase/delete_payments/' . $aRow['pur_order_name']) . '" class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></a>';
    $output['aaData'][] = $row;
    //  var_dump($row); die;

}
