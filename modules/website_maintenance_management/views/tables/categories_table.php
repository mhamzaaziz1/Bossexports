<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'name',
	'slug',
	'icon',
	'color',
	'display_order',
	'is_active',
	'description',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'wmm_categories';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// Name with icon
	$nameHtml = '<i class="fa '.html_escape($aRow['icon'] ?: 'fa-tasks').' tw-mr-2" style="color: '.html_escape($aRow['color'] ?: '#3b82f6').'"></i>';
	$nameHtml .= '<strong>'.html_escape($aRow['name']).'</strong>';
	if ($aRow['description'])
	{
		$nameHtml .= '<br><small class="text-muted">'.html_escape($aRow['description']).'</small>';
	}
	$row[] = $nameHtml;

	// Slug
	$row[] = '<code>'.html_escape($aRow['slug']).'</code>';

	// Icon
	$row[] = '<i class="fa '.html_escape($aRow['icon'] ?: 'fa-tasks').' fa-2x"></i>';

	// Color
	$row[] = '<span class="label" style="background-color: '.html_escape($aRow['color'] ?: '#3b82f6').'; color: white;">'.html_escape($aRow['color'] ?: '#3b82f6').'</span>';

	// Display order
	$row[] = $aRow['display_order'];

	// Status

	if ($aRow['is_active'] == 1)
	{
		$status = '<span class="label label-success">'._l('wmm_active').'</span>';
		if (staff_can('edit', 'website_maintenance_category'))
		{
			$status .= '<div class="onoffswitch no-mleft mtop5">';
			$status .= '<input type="checkbox" id="category_'.$aRow['id'].'" class="onoffswitch-checkbox" checked onchange="toggleCategoryStatus('.$aRow['id'].', 0)">';
			$status .= '<label class="onoffswitch-label" for="category_'.$aRow['id'].'"></label>';
			$status .= '</div>';
		}
	} else
	{
		$status = '<span class="label label-default">'._l('wmm_inactive').'</span>';
		if (staff_can('edit', 'website_maintenance_category'))
		{
			$status .= '<div class="onoffswitch no-mleft mtop5">';
			$status .= '<input type="checkbox" id="category_'.$aRow['id'].'" class="onoffswitch-checkbox" onchange="toggleCategoryStatus('.$aRow['id'].', 1)">';
			$status .= '<label class="onoffswitch-label" for="category_'.$aRow['id'].'"></label>';
			$status .= '</div>';
		}
	}
	$row[] = $status;

	// Options
	$options = '';
	if (staff_can('edit', 'website_maintenance_category'))
	{
		$options .= '<a href="#" onclick="editCategory('.$aRow['id'].'); return false;" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a> ';
	}
	if (staff_can('delete', 'website_maintenance_category'))
	{
		$options .= '<a href="#" onclick="deleteCategory('.$aRow['id'].'); return false;" class="btn btn-danger btn-icon"><i class="fa-regular fa-trash-can"></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();