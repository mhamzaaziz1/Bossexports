<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn  = 'id';
$sTable        = db_prefix().'wmm_maintenance_tasks';
$categoryTable = db_prefix().'wmm_categories';

$aColumns = [
	$sTable.'.id as id',
	$sTable.'.name as name',
	$categoryTable.'.name as category',
	$sTable.'.priority as priority',
	$sTable.'.created_at as created_at',
];

$joins   = [
	'LEFT JOIN '.db_prefix().'wmm_categories ON '.db_prefix().'wmm_categories.id = '.db_prefix().'wmm_maintenance_tasks.category',
];
$columns = [
	$categoryTable.'.icon as icon',
	$categoryTable.'.color as color',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $joins, [], $columns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row   = [];
	$row[] = $aRow['id'];

	$row[] = '<a href="#" onclick="editTask('.$aRow['id'].'); return false;">'.html_escape($aRow['name']).'</a>';

	if (empty($aRow['category']))
	{
		$row[] = '';
	} else
	{
		$nameHtml = '<i class="fa '.html_escape($aRow['icon'] ?: 'fa-tasks').' tw-mr-2" style="color: '.html_escape($aRow['color'] ?: '#3b82f6').'"></i>';
		$nameHtml .= '<strong>'.html_escape($aRow['category']).'</strong>';

		$row[] = '<span class="label label-default">'.$nameHtml.'</span>';
	}

	// Priority
	$priority_colors = [
		'low'    => 'default',
		'medium' => 'info',
		'high'   => 'warning',
		'urgent' => 'danger',
	];
	$priority_color  = isset($priority_colors[$aRow['priority']]) ? $priority_colors[$aRow['priority']] : 'default';
	$priority_label  = _l('wmm_priority_'.$aRow['priority']);
	$row[]           = '<span class="label label-'.$priority_color.'">'.$priority_label.'</span>';

	$row[] = _dt($aRow['created_at']);

	$options = '<a href="'.admin_url('website_maintenance_management/maintenance_tasks/view/'.$aRow['id']).'" class="btn btn-default btn-icon" data-toggle="tooltip" data-title="'._l('view').'"><i class="fa-regular fa-eye"></i></a> ';

	if (staff_can('edit', 'website_maintenance_tasks'))
	{
		$options .= '<a href="#" onclick="editTask('.$aRow['id'].'); return false;" class="btn btn-default btn-icon"><i class="fa-regular fa-pen-to-square"></i></a> ';
	}
	if (staff_can('delete', 'website_maintenance_tasks'))
	{
		$options .= '<a href="#" onclick="deleteTask('.$aRow['id'].'); return false;" class="btn btn-danger btn-icon"><i class="fa-regular fa-trash-can"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();
