<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('table_warehouse_so'))
    ->outputUsing(function ($params) {
        $CI =& get_instance();
        extract($params);

        $aColumns = [
            db_prefix().'pur_estimates.id',
            db_prefix().'pur_estimates.number',
            db_prefix().'pur_vendor.company',
            db_prefix().'pur_estimates.date',
            db_prefix().'pur_estimate_detail.quantity as qty',
        ];

            
        $sIndexColumn = 'id';
        $sTable       = db_prefix().'pur_estimates';

        $where = [];
        $warehouse_ft = $CI->input->post('warehouse_ft');
        $commodity_ft = $CI->input->post('commodity_ft'); 
        $status_ft = $CI->input->post('status_ft'); 

        $join =[
          'left JOIN '.db_prefix().'pur_estimate_detail ON '.db_prefix().'pur_estimate_detail.pur_estimate = '.db_prefix().'pur_estimates.id',
          'INNER JOIN '.db_prefix().'items ON '.db_prefix().'items.id = '.db_prefix().'pur_estimate_detail.item_code',
          'INNER JOIN '.db_prefix().'pur_vendor ON '.db_prefix().'pur_vendor.userid = '.db_prefix().'pur_estimates.buyer',
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
                            $where_commodity_ft .= ' AND '.db_prefix().'pur_estimate_detail.item_code = "'.$commodity_id.'"';
                        }else{
                            $where_commodity_ft .= ' or '.db_prefix().'pur_estimate_detail.item_code = "'.$commodity_id.'"';
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

        $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'pur_estimates.id', db_prefix().'pur_estimates.number', db_prefix().'pur_estimates.date', db_prefix().'pur_vendor.company', 'quantity']);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = $aRow['id'];
            $row[] = '<a href="'.admin_url('estimates/list_estimates/'.$aRow['id']).'" >PO-'.$aRow['number'].'</a>';
            $row[] = _d($aRow['date']);
            $row[] = $aRow['company'];
            $row[] = $aRow['qty'];
            
            $output['aaData'][] = $row;
        }

        return $output;
    });
