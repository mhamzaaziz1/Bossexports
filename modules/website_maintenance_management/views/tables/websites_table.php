<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn = 'id';
$sTable       = db_prefix().'wmm_websites';

$aColumns = [
	$sTable.'.id as id',
	db_prefix().'clients.company as client_name',
	db_prefix().'projects.name as project_name',
	'website_url',
	$sTable.'.is_active as is_active',
	$sTable.'.date_added as date_added',
];

$join = [
	'LEFT JOIN '.db_prefix().'projects ON '.db_prefix().'projects.id = '.db_prefix().'wmm_websites.project_id',
	'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wmm_websites.client_id',
];

$additionalColumns = [
	'client_id',
	'project_id',
];
$result            = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	$row[] = $aRow['id'];

	$row[] = '<a href="'.admin_url('clients/client/'.$aRow['client_id']).'" target="_blank">'.html_escape($aRow['client_name']).'</a>';

	$row[] = '<a href="'.admin_url('projects/view/'.$aRow['project_id']).'" target="_blank">'.html_escape($aRow['project_name']).'</a>';

	$row[] = $aRow['website_url'] ? '<a href="'.html_escape($aRow['website_url']).'" target="_blank">'.html_escape($aRow['website_url']).'</a>' : '-';

	if ($aRow['is_active'] == 1)
	{
		$status = '<span class="label label-success">'._l('wmm_active').'</span>';
		if (staff_can('edit', 'website_maintenance_websites'))
		{
			$status .= '<div class="onoffswitch no-mleft mtop5">';
			$status .= '<input type="checkbox" id="category_'.$aRow['id'].'" class="onoffswitch-checkbox" checked onchange="toggleWebsiteStatus('.$aRow['id'].', 0)">';
			$status .= '<label class="onoffswitch-label" for="category_'.$aRow['id'].'"></label>';
			$status .= '</div>';
		}
	} else
	{
		$status = '<span class="label label-default">'._l('wmm_inactive').'</span>';
		if (staff_can('edit', 'website_maintenance_websites'))
		{
			$status .= '<div class="onoffswitch no-mleft mtop5">';
			$status .= '<input type="checkbox" id="category_'.$aRow['id'].'" class="onoffswitch-checkbox" onchange="toggleWebsiteStatus('.$aRow['id'].', 1)">';
			$status .= '<label class="onoffswitch-label" for="category_'.$aRow['id'].'"></label>';
			$status .= '</div>';
		}
	}
	$row[] = $status;

	$row[] = _dt($aRow['date_added']);

	$options = '';
	if (staff_can('delete', 'website_maintenance_websites'))
	{
		$options .= '<a href="#" onclick="deleteWebsite('.$aRow['id'].'); return false;" class="btn btn-danger btn-icon"><i class="fa-regular fa-trash-can"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();
