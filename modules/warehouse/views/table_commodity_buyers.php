<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'clients.company',
    'SUM(' . db_prefix() . 'itemable.qty) as total_quantity',
    'SUM(' . db_prefix() . 'itemable.qty * ' . db_prefix() . 'itemable.rate) as total_amount',
];

$sIndexColumn = 'userid';
$sTable       = db_prefix() . 'clients';

$join = [
    'JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.clientid = ' . db_prefix() . 'clients.userid',
    'JOIN ' . db_prefix() . 'itemable ON ' . db_prefix() . 'itemable.rel_id = ' . db_prefix() . 'invoices.id AND ' . db_prefix() . 'itemable.rel_type = "invoice"',
];

$where = [];

// Filter by item description
$commodity_id = $this->ci->input->post('commodity_ft');
if($commodity_id){
    $item = $this->ci->warehouse_model->get_commodity($commodity_id);
    if($item){
        array_push($where, 'AND ' . db_prefix() . 'itemable.description = "' . $item->description . '"');
    }
}

array_push($where, 'AND ' . db_prefix() . 'invoices.status != 5');

$groupBy = 'GROUP BY ' . db_prefix() . 'clients.userid';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBy);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '">' . $aRow[db_prefix() . 'clients.company'] . '</a>';
    $row[] = $aRow['total_quantity'];
    $row[] = app_format_money($aRow['total_amount'], '');

    $output['aaData'][] = $row;
}
