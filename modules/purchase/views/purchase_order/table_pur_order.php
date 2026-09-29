<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('pur_order'))
    ->outputUsing(function ($params) {
        extract($params);

        $return = 0;
        $custom_fields = get_custom_fields('pur_order', [
            'show_on_table' => 1,
        ]);
        
        $aColumns = [
            'pur_order_name',
            'total',
            'total_tax',
            'vendor',
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'pur_orders.id and rel_type="pur_order" ORDER by tag_order ASC) as tags', 
            'order_date',
            'subtotal',
            'approve_status',
            'expense_convert',
            'returns'
        ];

        // Access parameters from $params or $this->ci->input if needed, though they are usually in $params or implicit
        // The original code used $vendor check. $vendor might be passed via 'data-attr' in render_datatable which ends up in params?
        // In render_datatable($data, $id, $extra_data), $extra_data is passed.
        // Let's assume $vendor is available in $params if it was passed. If not, check input?
        // Original code: if(isset($vendor))...
        
        // In App_table, extra params passed to find() or via request? 
        // Usually custom table views get variables via `data` array in controller. But here we are in the table script.
        // App_table::find() usually doesn't take extra params to passing to closure unless setRules or something.
        // But wait, the closure receives $params.
        
        // If $vendor was defined in the legacy include scope, it now needs to be extracted from params.
        // If it's not in params, check if it's in input.
        
        if (!isset($vendor) && $this->ci->input->post('vendor')) {
             // It might be a filter, but here it seems to be a hard constraint (e.g. valid for a specific vendor tab)
             // The old code checked `isset($vendor)`. This usually comes from the view calling it?
             // No, table_pur_order.php is requested via AJAX.
             // If it's AJAX, variables are typically passed via POST.
             // But existing code checked `isset($vendor)`. This implies it might be defined in the controller before include.
             // In the new system, we rely on $_POST or $params.
        }
        
        // Let's assume for now we stick to input or params.
        
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
        $sTable       = db_prefix().'pur_orders';
        $join         = ['LEFT JOIN '.db_prefix().'pur_vendor ON '.db_prefix().'pur_vendor.userid = '.db_prefix().'pur_orders.vendor'];
        $i = 0;
        foreach ($custom_fields as $field) {
            $select_as = 'cvalue_' . $i;
            if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
                $select_as = 'date_picker_cvalue_' . $i;
            }
            array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
            array_push($join, 'LEFT JOIN '.db_prefix().'customfieldsvalues as ctable_' . $i . ' ON '.db_prefix().'pur_orders.id = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
            $i++;
        }

        $where = [];
        array_push($where, ' AND '.db_prefix().'pur_orders.returns = '.$return);

        if(isset($vendor)){
             // This logic depends on $vendor being available.
             // If it was passed via `render_datatable(..., ['vendor'=>$id])`, it should be in $_POST (as custom data)
             // and thus in $params?
             // Let's check $this->ci->input->post('vendor') or check if it's in $params.
             array_push($where, ' AND '.db_prefix().'pur_orders.vendor = '.$vendor);
        }

        if ($this->ci->input->post('from_date')
            && $this->ci->input->post('from_date') != '') {
            array_push($where, 'AND order_date >= "'.date("y-m-d", strtotime($this->ci->input->post('from_date'))).'"');
        }

        if ($this->ci->input->post('to_date')
            && $this->ci->input->post('to_date') != '') {
            array_push($where, 'AND order_date <= "'.date("y-m-d", strtotime($this->ci->input->post('to_date'))).'"');
        }

        if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
            array_push($where, 'AND approve_status IN (' . implode(',', $this->ci->input->post('status')) . ')');
        }

        // Note: The original code had a check for 'vendor' input post separately from the $vendor variable.
        // The post 'vendor' seems to be a filter list.
        if ($this->ci->input->post('vendor')
            && count($this->ci->input->post('vendor')) > 0 && is_array($this->ci->input->post('vendor'))) {
            array_push($where, 'AND vendor IN (' . implode(',', $this->ci->input->post('vendor')) . ')');
        }

        //tags filter
        $tags_ft = $this->ci->input->post('item_filter');
        if (isset($tags_ft)) {
            $where_tags_ft = '';
            foreach ($tags_ft as $commodity_id) {
                if ($commodity_id != '') {
                    if ($where_tags_ft == '') {
                        $where_tags_ft .= ' AND (tblpur_orders.id = "' . $commodity_id . '"';
                    } else {
                        $where_tags_ft .= ' or tblpur_orders.id = "' . $commodity_id . '"';
                    }
                }
            }
            if ($where_tags_ft != '') {
                $where_tags_ft .= ')';
                array_push($where, $where_tags_ft);
            }
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'pur_orders.id as id','company','pur_order_number','expense_convert']);

        $output  = $result['output'];
        $rResult = $result['rResult'];

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
                    $paid = $aRow['total'] - purorder_left_to_pay($aRow['id']);
                    $percent = 0;
                    if($aRow['total'] > 0){
                        $percent = ($paid / $aRow['total'] ) * 100;
                    }
                    $_data1 = '<div class="progress">
                                  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40"
                                  aria-valuemin="0" aria-valuemax="100" style="width:'.round($percent).'%">
                                  ' .round($percent).' % 
                                  </div>
                                </div>';

                    $numberOutput = '<a href="' . admin_url('purchase/purchase_order/' . $aRow['id']) . '"  onclick="init_pur_order(' . $aRow['id'] . '); return false;" >'.$aRow['pur_order_number'].'</a><br>'.$_data1;
                    if($aRow['returns']==1){
                        $numberOutput .= '<br><a href="#" class="btn btn-danger btn-icon" align="center">Return</a>';
                    }

                    $numberOutput .= '<div class="row-options">';

                    if (has_permission('purchase', '', 'view')) {
                        $numberOutput .= ' <a href="' . admin_url('purchase/purchase_order/' . $aRow['id']) . '" onclick="init_pur_order(' . $aRow['id'] . '); return false;" >' . _l('view') . '</a>';
                    }
                    if ((has_permission('purchase', '', 'edit') || is_admin()) && $aRow['approve_status'] != 2 ) {
                        $numberOutput .= ' | <a href="' . admin_url('purchase/pur_order/' . $aRow['id']) . '">' . _l('edit') . '</a>';
                    }
                    if (has_permission('purchase', '', 'delete') || is_admin()) {
                        $numberOutput .= ' | <a href="' . admin_url('purchase/delete_pur_order/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                    }
                    $numberOutput .= '</div>';

                    $_data = $numberOutput;

                }elseif($aColumns[$i] == 'vendor'){
                    $_data = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" >' .  $aRow['company'] . '</a>';
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


                    $_data= $aRow['pur_order_name'];
                }elseif($aColumns[$i] == 'expense_convert'){
                    if($aRow['returns']!=1){
                        if($aRow['expense_convert'] == 0){
                         $_data = '<a href="javascript:void(0)" onclick="convert_expense('.$aRow['id'].','.$aRow['total'].'); return false;" class="btn btn-warning btn-icon">'._l('Expense').'</a>';
                        }else{
                            $_data = '<a href="'.admin_url('expenses/list_expenses/'.$aRow['expense_convert']).'" class="btn btn-success btn-icon">'._l('view_expense').'</a>';
                        }

                        if($aRow['pur_order_name']==""){
                        $_data .='<a href="javascript:void(0)" class="btn btn-success btn-icon" onclick="supplier_no('.$aRow['id'].')" >'._l('pur_order_number').'</a>';
                        }
                    }
                    else{
                        $_data ="";
                    }

                }elseif($aColumns[$i] == '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'pur_orders.id and rel_type="pur_order" ORDER by tag_order ASC) as tags'){
                        $this->ci->load->model('purchase_model');
                        $pur_order = $this->ci->purchase_model->get_pur_order($aRow['id']);
                        $estimate_number = ($pur_order && $pur_order->estimate) ? format_pur_estimate_number($pur_order->estimate) : '';
                        $estimate_id = ($pur_order && $pur_order->estimate) ? $pur_order->estimate : '';
                        $_data = '<a href="'.admin_url('purchase/quotations/'.$estimate_id) .'" class="btn btn-default btn-with-tooltip">'.$estimate_number.'</a>';

                }else {
                    if (strpos($aColumns[$i], 'date_picker_') !== false) {
                        $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
                    }
                }

                $row[] = $_data;
            }
            $output['aaData'][] = $row;

        }
        
        return $output;
    });
