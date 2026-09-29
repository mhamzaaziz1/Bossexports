<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'sp.id',
	'sp.package_name',
	'c.company',
	'COALESCE(w.website_url, p.name, "'._l('wmm_all_client_websites').'")',
	'sp.total_hours',
	'sp.hours_used',
	'sp.hours_remaining',
	'sp.status',
	'sp.expiry_date',
];

$sIndexColumn = 'sp.id';
$sTable       = db_prefix().'wmm_support_packages sp';

$join = [
	'LEFT JOIN '.db_prefix().'clients c ON c.userid = sp.client_id',
	'LEFT JOIN '.db_prefix().'wmm_websites w ON w.id = sp.website_id',
	'LEFT JOIN '.db_prefix().'projects p ON p.id = w.project_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
	'sp.client_id',
	'sp.website_id',
	'sp.low_balance_threshold',
	'w.website_url',
	'p.name as project_name',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// ID
	$row[] = '#'.$aRow['id'];

	// Package Name
	$package_name = '<a href="'.admin_url('website_maintenance_management/support_packages/view/'.$aRow['id']).'" class="bold">'.$aRow['package_name'].'</a>';
	$row[]        = $package_name;

	// Customer
	$row[] = '<a href="'.admin_url('clients/client/'.$aRow['client_id']).'" target="_blank">'.$aRow['company'].'</a>';

	// Website
	$website_display = $aRow['website_url'] ?: $aRow['project_name'] ?: _l('wmm_all_client_websites');
	if ($aRow['website_id'])
	{
		$website_display = '<a href="'.admin_url('website_maintenance_management/websites/view/'.$aRow['website_id']).'">'.$website_display.'</a>';
	} else
	{
		$website_display = '<span class="text-muted">'.$website_display.'</span>';
	}
	$row[] = $website_display;

	// Total Hours
	$row[] = $aRow['total_hours'].' h';

	// Hours Used
	$row[] = $aRow['hours_used'].' h';

	// Hours Remaining
	$hours_remaining = $aRow['hours_remaining'];
	$is_low_balance  = $hours_remaining <= $aRow['low_balance_threshold'] && $hours_remaining > 0;
	$hours_class     = $hours_remaining <= 0 ? 'text-danger' : ($is_low_balance ? 'text-warning' : 'text-success');
	$row[]           = '<span class="bold '.$hours_class.'">'.$hours_remaining.' h</span>';

	// Status
	$status       = $aRow['status'];
	$status_class = '';
	switch ($status)
	{
		case 'active':
			$status_class = 'success';
			break;
		case 'exhausted':
			$status_class = 'danger';
			break;
		case 'expired':
			$status_class = 'warning';
			break;
		case 'cancelled':
			$status_class = 'default';
			break;
	}
	$row[] = '<span class="label label-'.$status_class.'">'.ucfirst($status).'</span>';

	// Expiry Date
	$expiry_date = $aRow['expiry_date'] ? _d($aRow['expiry_date']) : '<span class="text-muted">-</span>';
	$row[]       = $expiry_date;

	// Options
	$options = '';
	if (staff_can('view', 'website_maintenance_packages'))
	{
		$options .= '<a href="'.admin_url('website_maintenance_management/support_packages/view/'.$aRow['id']).'" class="btn btn-default btn-xs"><i class="fa fa-eye"></i></a> ';
	}
	if (staff_can('edit', 'website_maintenance_packages'))
	{
		$options .= '<a href="#" onclick="edit_package('.$aRow['id'].'); return false;" class="btn btn-default btn-xs"><i class="fa fa-edit"></i></a> ';
	}
	if (staff_can('delete', 'website_maintenance_packages'))
	{
		$options .= '<a href="#" onclick="delete_package('.$aRow['id'].'); return false;" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}