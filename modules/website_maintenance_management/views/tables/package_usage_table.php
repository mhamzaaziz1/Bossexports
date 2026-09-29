<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'pu.id',
	'sp.package_name',
	'c.company',
	'COALESCE(w.website_url, p.name, "'._l('wmm_all_client_websites').'")',
	'pu.log_id',
	'pu.hours_consumed',
	'pu.consumed_at',
	'CONCAT(st.firstname, " ", st.lastname)',
];

$sIndexColumn = 'pu.id';
$sTable       = db_prefix().'wmm_package_usage pu';

$join = [
	'LEFT JOIN '.db_prefix().'wmm_support_packages sp ON sp.id = pu.package_id',
	'LEFT JOIN '.db_prefix().'clients c ON c.userid = sp.client_id',
	'LEFT JOIN '.db_prefix().'wmm_websites w ON w.id = sp.website_id',
	'LEFT JOIN '.db_prefix().'projects p ON p.id = w.project_id',
	'LEFT JOIN '.db_prefix().'wmm_maintenance_logs ml ON ml.id = pu.log_id',
	'LEFT JOIN '.db_prefix().'staff st ON st.staffid = ml.performed_by',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
	'pu.package_id',
	'sp.client_id',
	'sp.website_id',
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
	$package_name = '<a href="'.admin_url('website_maintenance_management/support_packages/view/'.$aRow['package_id']).'" target="_blank">'.$aRow['package_name'].'</a>';
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

	// Log ID
	$row[] = '<a href="'.admin_url('website_maintenance_management/maintenance_logs/view/'.$aRow['log_id']).'" target="_blank">#'.$aRow['log_id'].'</a>';

	// Hours Consumed
	$row[] = '<span class="bold text-success">'.$aRow['hours_consumed'].' h</span>';

	// Consumed At
	$row[] = _dt($aRow['consumed_at']);

	// Consumed By
	$row[] = $aRow['CONCAT(st.firstname, " ", st.lastname)'];

	$output['aaData'][] = $row;
}
