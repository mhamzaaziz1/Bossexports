<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('table_warehouse_PO'))
    ->outputUsing(function ($params) {
        $CI =& get_instance();
        extract($params);

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

        $where = [];
        $warehouse_ft = $CI->input->post('warehouse_ft');
        $commodity_ft = $CI->input->post('commodity_ft'); 
        $status_ft = $CI->input->post('status_ft'); 

        $join =[
          'INNER JOIN '.db_prefix().'itemable ON '.db_prefix().'itemable.rel_id = '.db_prefix().'estimates.id',
          'INNER JOIN '.db_prefix().'items ON '.db_prefix().'items.description = '.db_prefix().'itemable.description',
          'INNER JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'estimates.clientid ',
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
                            $where_commodity_ft .= ' AND itemable.description = "'.$commodity_id.'"';
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

        $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'estimates.id', db_prefix().'estimates.number', db_prefix().'estimates.date', db_prefix().'clients.company', db_prefix().'itemable.qty']);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = $aRow['id'];
            $row[] = '<a href="'.admin_url('estimates/list_estimates/'.$aRow['id']).'" >'.$aRow['number'].'</a>';
            $row[] = _d($aRow['date']);
            $row[] = $aRow['company'];
            $row[] = $aRow['qty'];
            
            $output['aaData'][] = $row;
        }

        return $output;
    });
