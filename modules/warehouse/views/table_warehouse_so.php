<?php

defined('BASEPATH') or exit('No direct script access allowed');


$aColumns = [
    db_prefix().'pur_estimates.id',
    db_prefix().'pur_estimates.number',
    db_prefix().'pur_vendor.company',
    db_prefix().'pur_estimates.date',
    db_prefix().'pur_estimate_detail.quantity as qty',
    ];
    
$sIndexColumn = 'id';
$sTable       = db_prefix().'pur_estimates';




$where=[];
$warehouse_ft = $this->ci->input->post('warehouse_ft');
$commodity_ft = $this->ci->input->post('commodity_ft'); 
$status_ft = $this->ci->input->post('status_ft'); 


$join =[
  'left JOIN '.db_prefix().'pur_estimate_detail ON '.db_prefix().'pur_estimate_detail.pur_estimate = '.db_prefix().'pur_estimates.id',
  'INNER JOIN '.db_prefix().'items ON '.db_prefix().'items.id = '.db_prefix().'pur_estimate_detail.item_code',
  'INNER JOIN '.db_prefix().'pur_vendor ON '.db_prefix().'pur_vendor.userid = '.db_prefix().'pur_estimates.buyer',
  
//   'LEFT JOIN '.db_prefix().'goods_delivery ON '.db_prefix().'goods_delivery.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 2',
//   'LEFT JOIN '.db_prefix().'wh_loss_adjustment ON '.db_prefix().'wh_loss_adjustment.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 3',
//   'LEFT JOIN '.db_prefix().'internal_delivery_note ON '.db_prefix().'internal_delivery_note.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 4'
];


 array_push($where, 'AND ' . db_prefix() . 'pur_estimates.status=1');

if(isset($commodity_ft)){
        if(!is_array($commodity_ft)){
            $where_commodity_ft = ' AND tblitems.id = "'.$commodity_ft.'"';
            array_push($where, $where_commodity_ft);
            
        }else{

            $where_commodity_ft = '';
            foreach ($commodity_ft as $commodity_id) {
                if($commodity_id != '')
                {
                    if($where_commodity_ft == ''){
                        $where_commodity_ft .= ' AND itemable.description= = "'.$commodity_id.'"';
                    }else{
                        $where_commodity_ft .= ' or itemable.description = "'.$commodity_id.'"';
                    }
                }
            }
            if($where_commodity_ft != '')
            {
                $where_commodity_ft .= ')';

                array_push($where, $where_commodity_ft);
            }
        }

    }


$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);

$output  = $result['output'];
$rResult = $result['rResult'];
// var_dump($rResult);die;



    foreach ($rResult as $aRow) {
        $row = [];
    $row[] = $aRow['tblpur_estimates.id'];
    $row[] = '<a href="https://beta.bossexports.co.za/admin/estimates/list_estimates/'.$aRow['tblpur_estimates.id'].'" return false;">PO-'.$aRow['tblpur_estimates.number'].'</a>';
    $row[] = _d($aRow['tblpur_estimates.date']);
    $row[] = $aRow['tblpur_vendor.company'];
    $row[] = $aRow['qty'];
    
     
    $output['aaData'][] = $row;

    }

