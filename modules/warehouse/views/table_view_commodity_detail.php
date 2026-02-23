<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('table_view_commodity_detail'))
    ->outputUsing(function ($params) {
        $CI =& get_instance();
        extract($params);

        $aColumns = [
            db_prefix().'items.description',
            db_prefix().'items.group_id',
            db_prefix().'items.color_id',
            db_prefix().'items.warehouse_id',
            db_prefix().'items.style_id',
            db_prefix().'items.unit_id',
            db_prefix().'items.rate',
            db_prefix().'items.purchase_price',
            db_prefix().'items.tax',
            db_prefix().'items.origin',
        ];
        $sIndexColumn = 'id';
        $sTable       = db_prefix().'items';

        $where = [];

        $warehouse_ft = $CI->input->post('warehouse_ft');
        $commodity_ft = $CI->input->post('commodity_ft');
        $alert_filter = $CI->input->post('alert_filter');

        $join = [
            'LEFT JOIN '.db_prefix().'inventory_manage ON '.db_prefix().'inventory_manage.commodity_id = '.db_prefix().'items.id',
        ];

        if(isset($warehouse_ft)){
            $where_warehouse_ft = '';
            foreach ($warehouse_ft as $warehouse_id) {
                if($warehouse_id != '')
                {
                    if($where_warehouse_ft == ''){
                        $where_warehouse_ft .= ' AND ('.db_prefix().'inventory_manage.warehouse_id = "'.$warehouse_id.'"';
                    }else{
                        $where_warehouse_ft .= ' or '.db_prefix().'inventory_manage.warehouse_id = "'.$warehouse_id.'"';
                    }
                }
            }
            if($where_warehouse_ft != '')
            {
                $where_warehouse_ft .= ')';
                array_push($where, $where_warehouse_ft);
            }
        }

        if(isset($commodity_ft)){
            $where_commodity_ft = ' AND '.db_prefix().'items.id = "'.$commodity_ft.'"';
            array_push($where, $where_commodity_ft);
        }

        $current_day = date('Y-m-d');
        $where_alert_filter1 = ' AND '.db_prefix().'inventory_manage.expiry_date < "'.$current_day.'"';
        array_push($where, $where_alert_filter1);

        $additionalColumns = [
            db_prefix().'items.id',
            db_prefix().'inventory_manage.commodity_id',
            db_prefix().'inventory_manage.warehouse_id as warehouse_ids',
            db_prefix().'inventory_manage.inventory_number',
            db_prefix().'inventory_manage.date_manufacture',
            db_prefix().'inventory_manage.expiry_date',
            db_prefix().'items.description',
            db_prefix().'items.unit_id',
            db_prefix().'items.commodity_code',
            db_prefix().'items.commodity_barcode',
            db_prefix().'items.commodity_type',
            db_prefix().'items.warehouse_id',
            db_prefix().'items.origin',
            db_prefix().'items.color_id',
            db_prefix().'items.style_id',
            db_prefix().'items.model_id',
            db_prefix().'items.size_id',
            db_prefix().'items.rate',
            db_prefix().'items.tax',
            db_prefix().'items.group_id' ,
            db_prefix().'items.long_description' ,
            db_prefix().'items.sku_code',
            db_prefix().'items.sku_name',
            db_prefix().'items.sub_group',
            db_prefix().'inventory_manage.lot_number'
        ];

        $result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];
            for ($i = 0; $i < count($aColumns); $i++) {
                // Determine which column we are handling
                $columnName = $aColumns[$i];
                $_data = '';

                if($columnName == db_prefix().'items.description') {
                    if(get_status_inventory($aRow['commodity_id'], $aRow['inventory_number'])){
                        $_data = '<a href="#"  data-name="'.$aRow['description'].'"  data-warehouse_id="'.$aRow['warehouse_ids'].'" data-commodity_id="'.$aRow['commodity_id'].'" data-expiry_date="'.$aRow['expiry_date'].'" >' . $aRow['commodity_code'] .'_'.$aRow['description']. '</a>';
                    }else{
                        $_data = '<a href="#" class="text-danger"   data-name="'.$aRow['description'].'" data-warehouse_id="'.$aRow['warehouse_ids'].'" data-commodity_id="'.$aRow['commodity_id'].'" data-expiry_date="'.$aRow['expiry_date'].'" >' . $aRow['commodity_code'] .'_'.$aRow['description']. '</a>';
                    }

                }elseif ($columnName == db_prefix().'items.group_id') {
                    if($aRow['expiry_date'] > date('Y-m-d')){
                        $_data = _d($aRow['expiry_date']);
                    }else{
                        $_data = '<a href="#" class="text-danger" >'._d($aRow['expiry_date']). '</a>';
                    }
                    
                }elseif ($columnName == db_prefix().'items.color_id') {
                    $_data = $aRow['lot_number'];

                }elseif($columnName == db_prefix().'items.warehouse_id'){
                    if($aRow['warehouse_ids'] != ''){
                        $arr_warehouse = explode(',', $aRow['warehouse_ids']);
                        $str = '';
                        $j = 0;
                        foreach ($arr_warehouse as $wh_id) {
                            $j++;
                            $warehouse_name = '';
                            $wh = get_warehouse_name($wh_id);
                            if ($wh) {
                                $warehouse_name = $wh->warehouse_name;
                            }
                            
                            $str .= '<span class="label label-tag tag-id-1"><span class="tag">' . $warehouse_name . '</span><span class="hide">, </span></span>&nbsp';
                            if ($j % 2 == 0) {
                                $str .= '<br><br/>';
                            }
                        }
                        $_data = $str;
                    }

                }elseif ($columnName == db_prefix().'items.unit_id') {
                    if($aRow['unit_id'] != null){
                        $_data = get_unit_type($aRow['unit_id']) != null ? get_unit_type($aRow['unit_id'])->unit_name : '';
                    }
                }elseif ($columnName == db_prefix().'items.rate') {
                    $_data = app_format_money((float)$aRow['rate'],'');
                }elseif($columnName == db_prefix().'items.purchase_price'){
                    $_data = app_format_money((float)$aRow['purchase_price'],''); 

                }elseif ($columnName == db_prefix().'items.tax') {
                    $tax_rate = get_tax_rate($aRow['tax']);
                    if($aRow['tax']){
                        if($tax_rate && $tax_rate != null && $tax_rate != 'null'){
                            $_data = $tax_rate->name;
                        }
                    }
                    
                }elseif ($columnName == db_prefix().'items.style_id') {
                    $_data = $aRow['inventory_number'];

                }elseif ($columnName == db_prefix().'items.origin') {
                    if(get_status_inventory($aRow['commodity_id'], $aRow['inventory_number'])){
                        $_data ='';
                    }else{
                        $_data = '<span class="label label-tag tag-id-1 label-tabus"><span class="tag">'._l('unsafe_inventory').'</span><span class="hide">, </span></span>&nbsp';
                    }
                }
            
                $row[] = $_data;
            }
            $output['aaData'][] = $row;
        }

        return $output;
    });
