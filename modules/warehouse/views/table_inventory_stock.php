<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('table_inventory_stock'))
    ->outputUsing(function ($params) {
        $CI =& get_instance();
        extract($params);

        $aColumns = [
            db_prefix().'items.commodity_code',
            db_prefix().'items.id as image',
            db_prefix().'items.description',
            db_prefix().'inventory_manage.warehouse_id',
            db_prefix().'items.commodity_type',
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
                $columnName = $aColumns[$i];
                $_data = '';

                if($columnName == db_prefix().'items.commodity_code') {
                    $_data = '<a href="' . admin_url('warehouse/view_commodity_detail/' . $aRow['id']) . '" >' . $aRow['commodity_code'] . '</a>';

                }elseif($columnName == db_prefix().'items.id as image'){
                    $_data = '<div class="row-image"><img src="'. $CI->warehouse_model->get_image_items($aRow['id']).'" ></div>';

                }elseif ($columnName == db_prefix().'items.description') {
                    if(get_status_inventory($aRow['commodity_id'], $aRow['inventory_number'])){
                        $_data = '<a href="' . admin_url('warehouse/view_commodity_detail/' . $aRow['id']) . '" >' . $aRow['description'] . '</a>';
                    }else{
                        $_data = '<a href="' . admin_url('warehouse/view_commodity_detail/' . $aRow['id']) . '" class="text-danger" >' . $aRow['description'] . '</a>';
                    }

                }elseif($columnName == db_prefix().'inventory_manage.warehouse_id'){
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
                    } else {
                        $_data = '';
                    }

                }elseif($columnName == db_prefix().'items.commodity_type'){
                    $_data = get_commodity_type_name($aRow['commodity_type']) != null ? get_commodity_type_name($aRow['commodity_type'])->name : '';

                }elseif ($columnName == db_prefix().'items.unit_id') {
                    if($aRow['unit_id'] != null){
                        $_data = get_unit_type($aRow['unit_id']) != null ? get_unit_type($aRow['unit_id'])->unit_name : '';
                    }else{
                        $data = '';
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
                    
                }elseif ($columnName == db_prefix().'items.origin') {
                    if($aRow['origin'] != null){
                        $_data = get_commodity_type_name($aRow['origin']) != null ? get_commodity_type_name($aRow['origin'])->name : '';
                    }
                }
            
                $row[] = $_data;
            }
            $output['aaData'][] = $row;
        }

        return $output;
    });
