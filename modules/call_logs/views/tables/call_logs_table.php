<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    "(case 
        when caller_type = 'caller' then tbl_call_logs.caller_name
        when caller_type = 'customer' then tblclients.company
        when caller_type = 'driver' then (
            SELECT CONCAT(tblstaff.firstname,' ',tblstaff.lastname) 
            FROM tblstaff 
            WHERE tblstaff.staffid = tbl_call_logs.driver_id
        )
    END) as customer",
    'caller_type',
    db_prefix().'_call_logs.direction as direction', 
    'purpose_of_call', 
    'TIMEDIFF('.db_prefix().'_call_logs.end_time, '.db_prefix().'_call_logs.start_time) as call_duration',
    'followup', 
    '(SELECT CONCAT('.db_prefix().'staff.firstname," ",'.db_prefix().'staff.lastname) FROM '.db_prefix().'staff WHERE '.db_prefix().'staff.staffid = '.db_prefix().'_call_logs.call_for) as call_for', 
    '(SELECT CONCAT('.db_prefix().'staff.firstname," ",'.db_prefix().'staff.lastname) FROM '.db_prefix().'staff WHERE '.db_prefix().'staff.staffid = '.db_prefix().'_call_logs.call_owner) as call_owner',
    'invoice_id',
    db_prefix().'_call_logs.datecreated as log_datecreated'
];
$sIndexColumn     = 'id';
$sTable           = db_prefix().'_call_logs';
$additionalSelect = [db_prefix().'_call_logs.driver_id','tblclients.company as company'];
$join = [
    'LEFT JOIN tblstaff ON tblstaff.staffid = '.db_prefix().'_call_logs.call_for',
    'LEFT JOIN tblstaff as tbl_staff ON tbl_staff.staffid = '.db_prefix().'_call_logs.driver_id',
    'LEFT JOIN tblclients ON tblclients.userid = '.db_prefix().'_call_logs.clientid',
];
$where    = ['AND parent = 1'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];

// print_r($result);
foreach ($rResult as $aRow) {
    $row = [];
    $_data = '';
    $row[] = $aRow['id'];
    $row[] = $aRow['customer'];

    $row[] = ucfirst($aRow['caller_type']);
    
    $row[] = ($aRow['direction'] == 'outbound') ? '<i class="fa fa-arrow-up " style="font-size: 20px; color: #84c529" title="Outbound"></i>' : '<i class="fa fa-arrow-down " title="Inbound" style="font-size: 20px; color: #ed143d"></i>';
    
    
    $outputName = '';
    $outputName .= '<a href="' . admin_url('call_logs/get_call_data/' . $aRow['id']) . '" class="display-block main-tasks-table-href-name' . (!empty($aRow['rel_id']) ? ' mbot5' : '') . '" onclick="init_call_modal(' . $aRow['id'] . '); return false;">' . $aRow['purpose_of_call'] . '</a>';


    $outputName .= '<div class="row-options">';

    $class = 'text-success bold';

    $outputName .= has_permission('call_logs', '', 'edit') ? '<a href="#" onclick="edit_call(' . $aRow['id'] . '); return false">' . _l('edit') . '</a>' : '';

    $outputName .= has_permission('call_logs', '', 'delete') ? '<span class="text-dark"> | </span><a href="' . admin_url('call_logs/delete_call/' . $aRow['id']) . '" class="text-danger _delete task-delete">' . _l('delete') . '</a>': '';
    $outputName .= '</div>';

    $row[] = $outputName;

    $row[] = explode('.', $aRow['call_duration'])[0];
    if ($aRow['followup'] == 'yes') {
        $labelClass = 'primary';
    } elseif ($aRow['followup'] == 'done') {
        $labelClass = 'success';
    } else {
        $labelClass = 'danger';
    }
    $row[] = '<span class="label label-'.$labelClass.' text-capitalize">'.$aRow['followup'].'</span>';
    $row[] = $aRow['call_for'];
    $row[] = $aRow['call_owner'];
    $row[] = $aRow['invoice_id'];
    $row[] = $aRow['log_datecreated'];

    $output['aaData'][] = $row;
}