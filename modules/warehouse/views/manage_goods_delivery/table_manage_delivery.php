<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('table_manage_delivery'))
    ->outputUsing(function ($params) {
        $CI =& get_instance();
        extract($params);

        $aColumns = [
            'id',
            'goods_delivery_code',
            'customer_code',
            'date_add',
            'invoice_id',
            'to_',
            'address',
            'staff_id',
            'approval',
            'delivery_status',
        ];
        $sIndexColumn = 'id';
        $sTable       = db_prefix().'goods_delivery';
        $join         = [ ];
        $where = [];

        if($CI->input->post('day_vouchers')){
            $day_vouchers = to_sql_date($CI->input->post('day_vouchers'));
        }

        if (isset($day_vouchers)) {
            $where[] = 'AND tblgoods_delivery.date_add <= "' . $day_vouchers . '"';
        }
        
        if (staff_cant('wh_stock_export', '', 'view')) {
            array_push($where, 'AND (' . db_prefix() . 'goods_delivery.addedfrom=' . get_staff_user_id().' OR ' . db_prefix() . 'goods_delivery.staff_id=' . get_staff_user_id() .')');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id','date_add','date_c','goods_delivery_code', 'customer_code']);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            for ($i = 0; $i < count($aColumns); $i++) {

                $_data = $aRow[$aColumns[$i]];
                
                if($aColumns[$i] == 'customer_code'){
                    $_data = '';
                    if($aRow['customer_code']){
                        $CI->db->select('company');
                        $CI->db->where('userid', $aRow['customer_code']);
                        $client = $CI->db->get(db_prefix().'clients')->row();
                        if($client){
                            $_data = $client->company;
                        }
                    }
                }elseif($aColumns[$i] == 'date_add'){
                    $_data = _d($aRow['date_add']);
                }elseif($aColumns[$i] == 'goods_delivery_code'){
                    $name = '<a href="' . admin_url('warehouse/view_delivery/' . $aRow['id'] ).'" onclick="init_goods_delivery('.$aRow['id'].'); return false;">' . $aRow['goods_delivery_code'] . '</a>';

                    $name .= '<div class="row-options">';

                    $name .= '<a href="' . admin_url('warehouse/edit_delivery/' . $aRow['id'] ).'" >' . _l('view') . '</a>';

                    if((has_permission('wh_stock_export', '', 'edit') || is_admin()) && ($aRow['approval'] == 0)){
                        $name .= ' | <a href="' . admin_url('warehouse/goods_delivery/' . $aRow['id'] . '/true').'" >' . _l('edit') . '</a>';
                    }

                    if ((has_permission('wh_stock_export', '', 'delete') || is_admin()) && ($aRow['approval'] == 0)) {
                        $name .= ' | <a href="' . admin_url('warehouse/delete_goods_delivery/' . $aRow['id'] ).'" class="text-danger _delete" >' . _l('delete') . '</a>';
                    }
                    
                    $name .= '</div>';

                    $_data = $name;
                }elseif($aColumns[$i] == 'staff_id'){
                     $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staff_id']) . '">' . staff_profile_image($aRow['staff_id'] ?? 0, [
                        'staff-profile-image-small',
                    ]) . '</a>';
                    $_data .= ' <a href="' . admin_url('staff/profile/' . $aRow['staff_id']) . '">' . get_staff_full_name($aRow['staff_id']) . '</a>';
                }elseif($aColumns[$i] == 'approval') {
                
                    if($aRow['approval'] == 1){
                        $_data = '<span class="label label-tag tag-id-1 label-tab1"><span class="tag">'._l('approved').'</span><span class="hide">, </span></span>&nbsp';
                    }elseif($aRow['approval'] == 0){
                        $_data = '<span class="label label-tag tag-id-1 label-tab2"><span class="tag">'._l('not_yet_approve').'</span><span class="hide">, </span></span>&nbsp';
                    }elseif($aRow['approval'] == -1){
                        $_data = '<span class="label label-tag tag-id-1 label-tab3"><span class="tag">'._l('reject').'</span><span class="hide">, </span></span>&nbsp';
                    }
                }elseif($aColumns[$i] == 'invoice_id'){
                    $_data = '';
                    if($aRow['invoice_id']){
                         $_data = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoice_id']) . '">' . format_invoice_number($aRow['invoice_id']) . '</a>';
                    }
                }elseif($aColumns[$i] == 'delivery_status'){
                     $_data = _l($aRow['delivery_status']);
                }

                $row[] = $_data;
            }
            $output['aaData'][] = $row;
        }

        return $output;
    });
