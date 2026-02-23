<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'pur_vendor.company',
    'SUM(' . db_prefix() . 'itemable.qty) as total_quantity',
    'SUM(' . db_prefix() . 'itemable.qty * ' . db_prefix() . 'itemable.rate) as total_amount',
];

$sIndexColumn = 'userid';
$sTable       = db_prefix() . 'pur_vendor';

$join = [
    'JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.vendor = ' . db_prefix() . 'pur_vendor.userid',
    'JOIN ' . db_prefix() . 'itemable ON ' . db_prefix() . 'itemable.rel_id = ' . db_prefix() . 'pur_orders.id AND ' . db_prefix() . 'itemable.rel_type = "pur_order"',
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

array_push($where, 'AND ' . db_prefix() . 'pur_orders.approve_status = 2');

$groupBy = 'GROUP BY ' . db_prefix() . 'pur_vendor.userid';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBy);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['userid']) . '">' . $aRow[db_prefix() . 'pur_vendor.company'] . '</a>';
    $row[] = $aRow['total_quantity'];
    $row[] = app_format_money($aRow['total_amount'], '');

    $output['aaData'][] = $row;
}
