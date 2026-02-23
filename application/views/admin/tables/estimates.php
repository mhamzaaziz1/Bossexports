<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('estimates_model');

return App_table::find('estimates')
    ->outputUsing(function ($params) {
        extract($params);

        $project_id = $this->ci->input->post('project_id');

        $aColumns = [
            'number',
            'date',
            'expirydate',
            get_sql_select_client_company(),
            'total',
            'total_tax', // Placeholder for calculation if not direct
            'YEAR(date) as year',
            '1', // Retainer Amount placeholder
            '1', // Retainer Status placeholder
            '1', // Task placeholder
            db_prefix() . 'estimates.status',
        ];

        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'estimates';

        $join = [
            'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'estimates.clientid',
            'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'estimates.currency',
            'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'estimates.project_id',
        ];

        $custom_fields = get_table_custom_fields('estimate');

        $customFieldsColumns = [];
        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'estimates.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        if ($clientid != '') {
            array_push($where, 'AND ' . db_prefix() . 'estimates.clientid=' . $this->ci->db->escape_str($clientid));
        }

        if ($project_id) {
            array_push($where, 'AND project_id=' . $this->ci->db->escape_str($project_id));
        }

        if (staff_cant('view', 'estimates')) {
            $userWhere = 'AND ' . get_estimates_where_sql_for_staff(get_staff_user_id());
            array_push($where, $userWhere);
        }

        $aColumns = hooks()->apply_filters('estimates_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
            db_prefix() . 'estimates.id',
            db_prefix() . 'estimates.clientid',
            db_prefix() . 'estimates.invoiceid',
            db_prefix() . 'currencies.name as currency_name',
            'project_id',
            'hash',
            'deleted_customer_name',
        ]);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            $numberOutput = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" onclick="init_estimate(' . $aRow['id'] . '); return false;">' . format_estimate_number($aRow['id']) . '</a>';

            $numberOutput .= '<div class="row-options">';

            $numberOutput .= '<a href="' . site_url('estimate/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
            if (staff_can('edit', 'estimates')) {
                $numberOutput .= ' | <a href="' . admin_url('estimates/estimate/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }
            $numberOutput .= '</div>';

            $row[] = $numberOutput;

            $row[] = e(_d($aRow['date']));

            $row[] = e(_d($aRow['expirydate']));

            if (empty($aRow['deleted_customer_name'])) {
                $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';
            } else {
                $row[] = e($aRow['deleted_customer_name']);
            }

            $row[] = e(app_format_money($aRow['total'], $aRow['currency_name']));

            $row[] = e(app_format_money($aRow['total_tax'], $aRow['currency_name']));

            $row[] = $aRow['year'];

            $row[] = ''; // Retainer Amount logic missing
            $row[] = ''; // Retainer Status logic missing
            $row[] = ''; // Task logic missing

            $row[] = format_estimate_status($aRow[db_prefix() . 'estimates.status']);

            // Custom fields add values
            foreach ($customFieldsColumns as $customFieldColumn) {
                $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
            }

            $row['DT_RowClass'] = 'has-row-options';

            $row = hooks()->apply_filters('estimates_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules([
        App_table_filter::new('number', 'NumberRule')->label(_l('estimate_dt_table_heading_number')),
        App_table_filter::new('total', 'NumberRule')->label(_l('estimate_dt_table_heading_amount')),
        App_table_filter::new('subtotal', 'NumberRule')->label(_l('estimate_subtotal')),
        App_table_filter::new('date', 'DateRule')->label(_l('estimate_dt_table_heading_date')),
        App_table_filter::new('expirydate', 'DateRule')
            ->label(_l('estimate_dt_table_heading_expirydate'))
            ->withEmptyOperators(),
        App_table_filter::new('sale_agent', 'SelectRule')->label(_l('sale_agent_string'))
            ->withEmptyOperators()
            ->emptyOperatorValue(0)
            ->isVisible(fn () => staff_can('view', 'estimates'))
            ->options(function ($ci) {
                return collect($ci->estimates_model->get_sale_agents())->map(function ($data) {
                    return [
                        'value' => $data['sale_agent'],
                        'label' => get_staff_full_name($data['sale_agent']),
                    ];
                })->all();
            }),
        App_table_filter::new('status', 'MultiSelectRule')
            ->label(_l('estimate_dt_table_heading_status'))
            ->options(function ($ci) {
                return collect($ci->estimates_model->get_statuses())->map(fn ($status) => [
                    'value' => (string) $status,
                    'label' => format_estimate_status($status, '', false),
                ])->all();
            }),
    ]);
