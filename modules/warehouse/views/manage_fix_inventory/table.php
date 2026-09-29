<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();
$results = $CI->warehouse_model->get_inventory_reconciliation();

$data = [];
foreach ($results as $aRow) {
    $row = [];

    $row[] = '<a href="' . admin_url('warehouse/view_commodity_detail/' . $aRow['item_id']) . '">' . $aRow['item_name'] . ' (' . $aRow['sku_code'] . ')</a>';
    $row[] = $aRow['warehouse_name'];
    $row[] = app_format_number($aRow['actual_qty']);
    $row[] = app_format_number($aRow['expected_qty']);
    
    $gap = (float)$aRow['expected_qty'] - (float)$aRow['actual_qty'];
    $row[] = '<span class="text-' . ($gap > 0 ? 'success' : 'danger') . '"><b>' . app_format_number($gap) . '</b></span>';

    $options = '<button class="btn btn-warning btn-xs fix-item-btn" data-item-id="' . $aRow['item_id'] . '" data-warehouse-id="' . $aRow['warehouse_id'] . '"><i class="fa fa-wrench"></i> Fix</button>';
    $row[] = $options;

    $data[] = $row;
}

$output = [
    'draw'            => 1,
    'iTotalRecords'   => count($data),
    'iTotalDisplayRecords' => count($data),
    'aaData'          => $data,
];

echo json_encode($output);
die;
