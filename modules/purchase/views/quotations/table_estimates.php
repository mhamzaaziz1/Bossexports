<?php

defined('BASEPATH') or exit('No direct script access allowed');

return (new App_table('pur_estimates'))
    ->outputUsing(function ($params) {
        extract($params);

        $aColumns = [
            'number',
            'total',
            'total_tax',
            'YEAR(date) as year',
            'vendor',
            'pur_request',
            'date',
            'expirydate',
            db_prefix() . 'pur_estimates.status',
        ];

        $join = [
            'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'pur_estimates.currency',
            'LEFT JOIN ' . db_prefix() . 'pur_vendor ON ' . db_prefix() . 'pur_vendor.userid = ' . db_prefix() . 'pur_estimates.vendor',
            'LEFT JOIN ' . db_prefix() . 'pur_request ON ' . db_prefix() . 'pur_request.id = ' . db_prefix() . 'pur_estimates.pur_request',
        ];

        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'pur_estimates';

        $custom_fields = get_table_custom_fields('estimate');

        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'pur_estimates.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where  = [];
        $filter = [];

        $aColumns = hooks()->apply_filters('estimates_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
            db_prefix() . 'pur_estimates.id',
            db_prefix() . 'pur_estimates.vendor',
            db_prefix() . 'pur_estimates.invoiceid',
            db_prefix() . 'currencies.name as currency_name',
            'pur_request',
            'deleted_vendor_name',
            'company',
            'pur_rq_name',
            'pur_rq_code'
        ]);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            $numberOutput = '';
            
            $numberOutput = '<a href="' . admin_url('purchase/quotations/' . $aRow['id']) . '" onclick="init_pur_estimate(' . $aRow['id'] . '); return false;">' . format_pur_estimate_number($aRow['id']) . '</a>';

            $numberOutput .= '<div class="row-options">';

            if (has_permission('purchase', '', 'view')) {
                $numberOutput .= ' <a href="' . admin_url('purchase/quotations/' . $aRow['id']) . '" onclick="init_pur_estimate(' . $aRow['id'] . '); return false;">' . _l('view') . '</a>';
            }
            if ( (has_permission('purchase', '', 'edit') || is_admin()) && $aRow[db_prefix() . 'pur_estimates.status'] != 2) {
                $numberOutput .= ' | <a href="' . admin_url('purchase/estimate/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }
            if (has_permission('purchase', '', 'delete') || is_admin()) {
                $numberOutput .= ' | <a href="' . admin_url('purchase/delete_estimate/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $numberOutput .= '</div>';

            $row[] = $numberOutput;

            $amount = app_format_money($aRow['total'], '');

            if ($aRow['invoiceid']) {
                $amount .= '<br /><span class="hide"> - </span><span class="text-success">' . _l('estimate_invoiced') . '</span>';
            }

            $row[] = $amount;

            $row[] = app_format_money($aRow['total_tax'], '');

            $row[] = $aRow['year'];

            if (empty($aRow['deleted_vendor_name'])) {
                $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '" >' .  $aRow['company'] . '</a>';
            } else {
                $row[] = $aRow['deleted_vendor_name'];
            }

            $row[] = '<a href="' . admin_url('purchase/view_pur_request/' . $aRow['pur_request']) . '" onclick="init_pur_estimate(' . $aRow['id'] . '); return false;">' . $aRow['pur_rq_code'] .' - '.$aRow['pur_rq_name'] . '</a>' ;

            $row[] = _d($aRow['date']);

            $row[] = _d($aRow['expirydate']);

            $status = '';
            if($aRow[db_prefix() . 'pur_estimates.status'] == 1){
                $status = '<span class="label label-primary"> Open </span>';
            }elseif($aRow[db_prefix() . 'pur_estimates.status'] == 2){
                $status = '<span class="label label-success"> Close </span>';
            }elseif($aRow[db_prefix() . 'pur_estimates.status'] == 3){
                $status = '<span class="label label-warning"> '._l('reject').' </span>';
            }

            $row[] = $status;

            $row['DT_RowClass'] = 'has-row-options';

            $row = hooks()->apply_filters('estimates_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }

        return $output;
    });
