<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('pur_order_payment'))
    ->outputUsing(function ($params) {
        extract($params);

        $aColumns = [
            db_prefix().'pur_order_payment.id as pur_order_name',
            'transactionid',
             db_prefix().'pur_order_payment.date as order_date',
            'COALESCE('.db_prefix().'pur_orders.vendor,'.db_prefix().'pur_order_payment.vendor) as vendor',
            'pur_order',
            'SUM(amount) as total',
        ];

        // Original logic for column override if vendor is set?
        // Note: The original code overrode $aColumns if isset($vendor). 
        // We should check if 'vendor' is passed in params.
        if (isset($vendor)) {
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
        
        $join = [];
        array_push($join, 'LEFT JOIN '.db_prefix().'pur_orders ON '.db_prefix().'pur_orders.id = '.db_prefix().'pur_order_payment.pur_order');

        $where = [];
        // Note: Original code used $vendor variable. It might come from params.
        if (isset($vendor)) {
            array_push($where, ' AND COALESCE('.db_prefix().'pur_orders.vendor,'.db_prefix().'pur_order_payment.vendor) = ' . $this->ci->db->escape($vendor));
        }

        if ($this->ci->input->post('to_date') && $this->ci->input->post('to_date') != '') {
            array_push($where, 'AND date <= "' . to_sql_date($this->ci->input->post('to_date')) . '"');
        }

        if ($this->ci->input->post('from_date') && $this->ci->input->post('from_date') != '') {
            array_push($where, 'AND date >= "' . to_sql_date($this->ci->input->post('from_date')) . '"');
        }
        
        $having = '';
        if ($this->ci->input->post('amount') && $this->ci->input->post('amount') != '') {
            $having = ' HAVING SUM(amount) = ' . $this->ci->db->escape($this->ci->input->post('amount'));
        }

        if ($this->ci->input->post('vendor') && count($this->ci->input->post('vendor')) > 0) {
            array_push($where, 'AND COALESCE('.db_prefix().'pur_orders.vendor,'.db_prefix().'pur_order_payment.vendor) IN (' . implode(',', $this->ci->input->post('vendor')) . ')');
        }
        
        // Group by clause needs to be part of the final query construction.
        // data_tables_init doesn't directly support adding raw SQL suffix like GROUP BY inside 'where' correctly unless hacky.
        // But the original code appends it to $where:
        // array_push($where, 'group by tblpur_order_payment.Date, tblpur_order_payment.transactionid'.$having);
        // We will keep this behavior but ensure keys are correct.
        
        array_push($where, 'GROUP BY '.db_prefix().'pur_order_payment.date, '.db_prefix().'pur_order_payment.transactionid' . $having);

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'pur_order_payment.id as id', 'transactionid']);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            // Since we might have different columns based on vendor, we need to be careful with indexing or names.
            // The loop in original code used $aColumns[$i] which assumes knowing the structure.
            // Let's stick to building the row manually based on the fixed columns we expect for the output table.
            
            // Note: The output columns in original code loop:
            // 1. pur_order_name
            // 2. order_date
            // 3. vendor
            // 4. transactionid
            // 5. total
            // 6. delete action
            
            // However, the $aColumns definition had:
            // pur_order_name, transactionid, order_date, vendor, pur_order, total
            
            // Let's reconstruct consistent with original display logic
            
            $numberOutput = '<a href="#" >'.$aRow['pur_order_name'].'</a>';
            $row[] = $numberOutput;
            
            $row[] = _d($aRow['order_date']);

            if($aRow['vendor'] != "0"){
                $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" >' .  wh_get_vendor_company_name($aRow['vendor']) . '</a>';
            } else {
                // Fetch vendor from order if not in payment?
                // Original code: $vendor=$this->ci->db->select('*')->from('tblpur_orders')->where('id',$aRow['pur_order'])->get()-> row()->vendor;
                
                 if(isset($aRow['pur_order']) && $aRow['pur_order'] > 0){
                      $order = $this->ci->db->select('vendor')->from(db_prefix().'pur_orders')->where('id', $aRow['pur_order'])->get()->row();
                      $vendorId = $order ? $order->vendor : 0;
                 } else {
                     $vendorId = 0;
                 }
                $row[] = '<a href="' . admin_url('purchase/vendor/' . $vendorId) . '" >'.wh_get_vendor_company_name($vendorId).'</a>';
            }
            
            $row[] = $aRow['transactionid'];
            $row[] = app_format_money($aRow['total'], '');
            
            $row[] = '<a href="#" onclick="view_split_payments(\'' . $aRow['transactionid'] . '\'); return false;" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a> <a href="' . admin_url('purchase/delete_payments/' . $aRow['pur_order_name']) . '" class="btn btn-danger btn-icon _delete"><i class="fa fa-trash" style="color: white;"></i></a>';
            
            $output['aaData'][] = $row;
        }

        return $output;
    });
