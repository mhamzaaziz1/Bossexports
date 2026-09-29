<?php

defined('BASEPATH') or exit('No direct script access allowed');

$booking_id = $this->input->post('booking_id');

$aColumns = [
    'number',
    'total',
    'total_tax',
    'YEAR(date) as year',
    'date',
    get_sql_select_client_company(),
    db_prefix() . 'bookings.name as booking_name',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'invoices.id and rel_type="invoice" ORDER by tag_order ASC) as tags',
    'duedate',
    db_prefix() . 'invoices.status',
    ];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'invoices';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
    'LEFT JOIN ' . db_prefix() . 'bookings ON ' . db_prefix() . 'bookings.id = ' . db_prefix() . 'invoices.booking_id',
];

$custom_fields = get_table_custom_fields('invoice');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'invoices.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where  = [];
$filter = [];

if ($this->input->post('not_sent')) {
    array_push($filter, 'AND sent = 0 AND ' . db_prefix() . 'invoices.status NOT IN('.Invoices_model::STATUS_PAID.','.Invoices_model::STATUS_CANCELLED.')');
}
if ($this->input->post('not_have_payment')) {
    array_push($filter, 'AND ' . db_prefix() . 'invoices.id NOT IN(SELECT invoiceid FROM ' . db_prefix() . 'invoicepaymentrecords) AND ' . db_prefix() . 'invoices.status != '.Invoices_model::STATUS_CANCELLED);
}
if ($this->input->post('recurring')) {
    array_push($filter, 'AND recurring > 0');
}

$statuses  = $this->invoices_model->get_statuses();
$statusIds = [];
foreach ($statuses as $status) {
    if ($this->input->post('invoices_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->invoices_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}

$modesIds = [];
foreach ($data['payment_modes'] as $mode) {
    if ($this->input->post('invoice_payments_by_' . $mode['id'])) {
        array_push($modesIds, $mode['id']);
    }
}
if (count($modesIds) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.id IN (SELECT invoiceid FROM ' . db_prefix() . 'invoicepaymentrecords WHERE paymentmode IN ("' . implode('", "', $modesIds) . '"))');
}

$years     = $this->invoices_model->get_invoices_years();
$yearArray = [];
foreach ($years as $year) {
    if ($this->input->post('year_' . $year['year'])) {
        array_push($yearArray, $year['year']);
    }
}
if (count($yearArray) > 0) {
    array_push($where, 'AND YEAR(date) IN (' . implode(', ', $yearArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if ($clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid=' . $this->db->escape_str($clientid));
}

if ($booking_id) {
    array_push($where, 'AND booking_id=' . $this->db->escape_str($booking_id));
}

if (!has_permission('invoices', '', 'view')) {
    $userWhere = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

$aColumns = hooks()->apply_filters('invoices_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'invoices.id',
    db_prefix() . 'invoices.clientid',
    db_prefix(). 'currencies.name as currency_name',
    'booking_id',
    'hash',
    'recurring',
    'deleted_customer_name',
    ]);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '';

    // If is from client area table
    if (is_numeric($clientid) || $booking_id) {
        $numberOutput = '<a href="' . admin_url('booking/invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('booking/invoices/list_invoices/' . $aRow['id']) . '" onclick="load_small_table_item(' . $aRow['id'] . ', \'#invoice\', \'invoiceid\', \'booking/invoices/get_invoice_data_ajax\', \'.table-invoices\'); return false;">' . format_invoice_number($aRow['id']) . '</a>';
    }

    if ($aRow['recurring'] > 0) {
        $numberOutput .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('invoice_recurring_indicator') . '</span>';
    }

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('invoices', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('booking/invoices/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = app_format_money($aRow['total'], $aRow['currency_name']);

    $row[] = app_format_money($aRow['total_tax'], $aRow['currency_name']);

    $row[] = $aRow['year'];

    $row[] = _d($aRow['date']);

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('booking/clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    $row[] = '<a href="' . admin_url('booking/bookings/view/' . $aRow['booking_id']) . '">' . $aRow['booking_name'] . '</a>';
    ;

    $row[] = render_tags($aRow['tags']);

    $row[] = _d($aRow['duedate']);

    $row[] = format_invoice_status($aRow[db_prefix() . 'invoices.status']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('invoices_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
