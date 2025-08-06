<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$aColumns = [
    'title',
    'report_type',
    "CONCAT(date_range_start, ' - ', date_range_end) as date_range",
    '(SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'staff WHERE staffid = ' . db_prefix() . 'smart_reports.created_by) as created_by',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'smart_reports';

$join = [];
$where = [];

// If not admin, show only own reports
if (!is_admin()) {
    $where[] = 'AND created_by = ' . get_staff_user_id();
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    
    // Title
    $row[] = '<a href="' . admin_url('smart_reports/report/' . $aRow['id']) . '">' . $aRow['title'] . '</a>';
    
    // Report Type
    $reportTypes = [
        'sales' => _l('sales'),
        'purchases' => _l('purchases'),
        'inventory' => _l('inventory'),
        'payments' => _l('payments'),
        'leads' => _l('leads'),
        'tasks' => _l('tasks'),
        'custom_query' => _l('custom_query')
    ];
    
    $reportType = isset($reportTypes[$aRow['report_type']]) ? $reportTypes[$aRow['report_type']] : $aRow['report_type'];
    $row[] = $reportType;
    
    // Date Range
    $dateRange = $aRow['date_range'];
    if ($dateRange == ' - ') {
        $dateRange = _l('not_set');
    } else {
        $dates = explode(' - ', $dateRange);
        if (count($dates) == 2) {
            $dateRange = _d($dates[0]) . ' - ' . _d($dates[1]);
        }
    }
    $row[] = $dateRange;
    
    // Created By
    $row[] = $aRow['created_by'];
    
    // Created At
    $row[] = _dt($aRow['created_at']);
    
    // Options
    $options = '';
    
    // View button
    $options .= '<a href="' . admin_url('smart_reports/report/' . $aRow['id']) . '" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a>';
    
    // Edit button
    if (has_permission('smart_reports', '', 'edit')) {
        $options .= '<a href="' . admin_url('smart_reports/report/' . $aRow['id']) . '" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>';
    }
    
    // Delete button
    if (has_permission('smart_reports', '', 'delete')) {
        $options .= '<a href="' . admin_url('smart_reports/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>';
    }
    
    $row[] = $options;
    
    $output['aaData'][] = $row;
}