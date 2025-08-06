<?php

defined('BASEPATH') or exit('No direct script access allowed');


$aColumns = [
    db_prefix().'estimates.id',
    db_prefix().'estimates.number',
    db_prefix().'clients.company',
    db_prefix().'estimates.date',
    db_prefix().'itemable.qty',
    db_prefix().'items.description',
    ];
    
$sIndexColumn = 'id';
$sTable       = db_prefix().'estimates';




$where=[];
$warehouse_ft = $this->ci->input->post('warehouse_ft');
$commodity_ft = $this->ci->input->post('commodity_ft'); 
$status_ft = $this->ci->input->post('status_ft'); 


$join =[
  'INNER JOIN '.db_prefix().'itemable ON '.db_prefix().'itemable.rel_id = '.db_prefix().'estimates.id',
  'INNER JOIN '.db_prefix().'items ON '.db_prefix().'items.description = '.db_prefix().'itemable.description',
  'INNER JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'estimates.clientid ',
  
//   'LEFT JOIN '.db_prefix().'goods_delivery ON '.db_prefix().'goods_delivery.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 2',
//   'LEFT JOIN '.db_prefix().'wh_loss_adjustment ON '.db_prefix().'wh_loss_adjustment.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 3',
//   'LEFT JOIN '.db_prefix().'internal_delivery_note ON '.db_prefix().'internal_delivery_note.id = '.db_prefix().'goods_transaction_detail.goods_receipt_id AND  '.db_prefix().'goods_transaction_detail.status = 4'
];



 array_push($where, 'AND ' . db_prefix() . 'estimates.status=1 and tblitemable.rel_type="estimate"');

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
    $row[] = $aRow['tblestimates.id'];
    $row[] = '<a href="https://app.bossexports.co.za/admin/estimates/list_estimates/'.$aRow['tblestimates.id'].'" return false;">'.$aRow['tblestimates.number'].'</a>';
    $row[] = _d($aRow['tblestimates.date']);
    $row[] = $aRow['tblclients.company'];
    $row[] = $aRow['tblitemable.qty'];
    
     
    $output['aaData'][] = $row;

    }

