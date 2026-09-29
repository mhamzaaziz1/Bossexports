<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    _l('Payment ID'),
    _l('Date'),
    _l('Vendor'),
    _l('transactionid'),
    _l('Total'),
    _l('action'),
    ];

if(isset($vendor)){
    $aColumns = [
    'pur_order_name',
    'total',
    'total_tax',
    'vendor', 
    'transactionid',
    'order_date',
    'SUM(subtotal) as subtotal',
    'approve_status',

    ];
}

$sIndexColumn = 'id';
$sTable       = db_prefix().'pur_order_payment';
$join         = ['full outer JOIN '.db_prefix().'pur_vendor ON '.db_prefix().'pur_vendor.userid = '.db_prefix().'pur_orders.vendor'];
$i = 0;

$where = [];
if(isset($vendor)){
    array_push($where, ' AND '.db_prefix().'pur_orders.vendor = '.$vendor);
}

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND order_date >= "'.date("Y-m-d", strtotime($this->ci->input->post('from_date'))).'"');
}

if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND order_date <= "'.date("Y-m-d", strtotime($this->ci->input->post('to_date'))).'"');
}

if ($this->ci->input->post('vendor')
    && count($this->ci->input->post('vendor')) > 0) {
    array_push($where, 'AND vendor IN (' . implode(',', $this->ci->input->post('vendor')) . ')');
}

// Filter by amount
if ($this->ci->input->post('amount')
    && $this->ci->input->post('amount') != '') {
    array_push($where, 'AND ' . db_prefix() . 'pur_order_payment.amount = ' . $this->ci->db->escape_str($this->ci->input->post('amount')));
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'pur_order_payment.id as id','company','vendor']);

$output  = $result['output'];
$rResult = $result['rResult'];
//  echo $this->ci->last_query(123); die;

foreach ($rResult as $aRow) {
    $row = [];

   for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }

        if($aColumns[$i] == 'total'){
            $_data = app_format_money($aRow['total'], '');
        }elseif($aColumns[$i] == 'pur_order_name'){

            $numberOutput = '';

            $numberOutput = '<a href="' . admin_url('purchase/purchase_order/' . $aRow['id']) . '"  onclick="init_pur_order(' . $aRow['id'] . '); return false;" >'.$aRow['id'].'</a>';

            $numberOutput .= '<div class="row-options">';

            if (has_permission('purchase', '', 'view')) {
                $numberOutput .= ' <a href="' . admin_url('purchase/purchase_order/' . $aRow['id']) . '" onclick="init_pur_order(' . $aRow['id'] . '); return false;" >' . _l('view') . '</a>';
            }
            if ((has_permission('purchase', '', 'edit') || is_admin()) && $aRow['approve_status'] != 2 ) {
                $numberOutput .= ' | <a href="' . admin_url('purchase/pur_order/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }
            if (has_permission('purchase', '', 'delete') || is_admin()) {
                $numberOutput .= ' | <a href="' . admin_url('purchase/delete_pur_order/' . $aRow['id']) . '" class="text-danger">' . _l('delete') . '</a>';
            }
            $numberOutput .= '</div>';

            $_data = $numberOutput;

        }elseif($aColumns[$i] == 'vendor'){
            if($aRow['vendor']!=0){
            $_data = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" >' .  $aRow['company'] . '</a>';
            }else{
                $vendor=$this->ci->Select('*')->from('tblpur_orders')->where($aRow['vendor'])->get()-> row()->vendor;
                $_data = '<a href="' . admin_url('purchase/vendor/' . $vendor) . '" >Open Vendor</a>';
            }
        }elseif ($aColumns[$i] == 'transactionid') {
            $row[] = $aRow['transactionid'];
        }elseif ($aColumns[$i] == 'order_date') {
            $_data = _d($aRow['order_date']);
        }elseif($aColumns[$i] == 'approve_status'){
            $_data = get_status_approve($aRow['approve_status']);
        }elseif($aColumns[$i] == 'total_tax'){
            $_data = app_format_money($aRow['total_tax'], '');
        }elseif($aColumns[$i] == 'subtotal'){
            $paid = $aRow['total'] - purorder_left_to_pay($aRow['id']);
            $percent = 0;
            if($aRow['total'] > 0){
                $percent = ($paid / $aRow['total'] ) * 100;
            }

            $_data = '<div class="progress">
                          <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40"
                          aria-valuemin="0" aria-valuemax="100" style="width:'.round($percent).'%">
                           ' .round($percent).' % 
                          </div>
                        </div>';
        }elseif($aColumns[$i] == 'expense_convert'){
            if($aRow['returns']!=1){
                if($aRow['expense_convert'] == 0){
                 $_data = '<a href="javascript:void(0)" onclick="convert_expense('.$aRow['id'].','.$aRow['total'].'); return false;" class="btn btn-warning btn-icon">'._l('Expense').'</a>';
                }else{
                    $_data = '<a href="'.admin_url('expenses/list_expenses/'.$aRow['expense_convert']).'" class="btn btn-success btn-icon">'._l('view_expense').'</a>';
                }
                // $_data .='<a href="'.admin_url('purchase/pur_order/' . $aRow['id']).'?return=1" class="btn btn-success btn-icon">'._l('Return').'</a>';
                if($aRow['pur_order_name']==""){
                $_data .='<a href="javascript:void(0)" class="btn btn-success btn-icon" onclick="supplier_no('.$aRow['id'].')" >'._l('pur_order_number').'</a>';
                }
            }
            else{
                $_data ="";
            }

        }elseif($aColumns[$i] == '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'pur_orders.id and rel_type="pur_order" ORDER by tag_order ASC) as tags'){
                $this->ci->load->model('purchase_model');
                $_data = '<a href="'.admin_url('purchase/quotations/'.$this->ci->purchase_model->get_pur_order($aRow['id'])->estimate) .'" class="btn btn-default btn-with-tooltip">'.format_pur_estimate_number($this->ci->purchase_model->get_pur_order($aRow['id'])->estimate).'</a>';

        }else {
            if (strpos($aColumns[$i], 'date_picker_') !== false) {
                $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
            }
        }

        $row[] = $_data;
    }
    $output['aaData'][] = $row;

}
