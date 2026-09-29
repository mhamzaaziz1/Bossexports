<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn = db_prefix().'wmm_maintenance_logs.id';
$sTable       = db_prefix().'wmm_maintenance_logs';

$aColumns = [
	$sTable.'.id as id',
	db_prefix().'clients.company as client_name',
	db_prefix().'projects.name as project_name',
	'website_url',
	'CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as performed_by_name',
	'performed_at',
	$sTable.'.is_completed as is_completed',
	'email_sent',
	'client_id',
	'project_id',
];

$join              = [
	'LEFT JOIN '.db_prefix().'wmm_websites ON '.db_prefix().'wmm_websites.id = '.db_prefix().'wmm_maintenance_logs.website_id',
	'LEFT JOIN '.db_prefix().'projects ON '.db_prefix().'projects.id = '.db_prefix().'wmm_websites.project_id',
	'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wmm_websites.client_id',
	'LEFT JOIN '.db_prefix().'staff ON '.db_prefix().'staff.staffid = '.db_prefix().'wmm_maintenance_logs.performed_by',
];
$additionalColumns = [

];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	$row[] = $aRow['id'];

	$row[] = '<a href="'.admin_url('clients/client/'.$aRow['client_id']).'" target="_blank">'.html_escape($aRow['client_name']).'</a>';

	$row[] = '<a href="'.admin_url('projects/view/'.$aRow['project_id']).'" target="_blank">'.html_escape($aRow['project_name']).'</a>';

	$row[] = $aRow['website_url'] ? '<a href="'.html_escape($aRow['website_url']).'" target="_blank">'.html_escape($aRow['website_url']).'</a>' : '-';

	$row[] = html_escape($aRow['performed_by_name']);

	$row[] = _dt($aRow['performed_at']);

	// Status column
	$status = '';
	if ($aRow['is_completed'] == 1)
	{
		$status = '<span class="label label-success"><i class="fa fa-check"></i> '._l('wmm_completed').'</span>';
	} else
	{
		$status = '<span class="label label-warning"><i class="fa fa-clock"></i> '._l('wmm_in_progress').'</span>';
	}
	$row[] = $status;

	$email_status = '';
	if ($aRow['email_sent'] == 1)
	{
		$email_status = '<span class="label label-success"><i class="fa fa-check"></i> '._l('wmm_email_sent').'</span>';
		if (staff_can('edit', 'website_maintenance_logs'))
		{
			$email_status .= ' <a href="#" onclick="resendNotification('.$aRow['id'].'); return false;" class="text-muted" title="'._l('wmm_resend_email').'"><i class="fa fa-envelope"></i></a>';
		}
	} else
	{
		$email_status = '<span class="label label-warning"><i class="fa fa-times"></i> '._l('wmm_email_not_sent').'</span>';
		if (staff_can('edit', 'website_maintenance_logs'))
		{
			$email_status .= ' <a href="#" onclick="resendNotification('.$aRow['id'].'); return false;" class="text-primary" title="'._l('wmm_send_email').'"><i class="fa fa-envelope"></i></a>';
		}
	}
	$row[] = $email_status;

	$options = '';
	if (staff_can('view', 'website_maintenance_logs'))
	{
		$options .= '<a href="#" onclick="viewLog('.$aRow['id'].') ;return false;" class="btn btn-default btn-icon" data-toggle="tooltip" data-title="'._l('view').'"><i class="fa-regular fa-eye"></i></a> ';
	}
	if (staff_can('delete', 'website_maintenance_logs'))
	{
		$options .= '<a href="#" onclick="deleteLog('.$aRow['id'].') ;return false;" class="btn btn-danger btn-icon" data-toggle="tooltip" data-title="'._l('delete')
		            .'"><i class="fa-regular fa-trash-can"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();
