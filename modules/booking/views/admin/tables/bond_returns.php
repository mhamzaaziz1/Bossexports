<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit   = has_permission('bond_returns', '', 'edit');
$hasPermissionDelete = has_permission('bond_returns', '', 'delete');
$hasPermissionCreate = has_permission('bond_returns', '', 'create');

$aColumns = [
    db_prefix() . 'bookings.id as id',
    get_sql_select_client_company(),
    'name',
    ];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'bookings';

$join = [
    'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'bookings.clientid',
];

$where  = [];
$filter = [];

if ($clientid != '') {
    array_push($where, ' AND clientid=' . $this->ci->db->escape_str($clientid));
}

$handle = fopen("asdfasdf_data.txt", "w");
fwrite($handle, $start_date);
fwrite($handle, $end_date);
fclose($handle);

if ($start_date != ''){
    array_push($where, ' AND start_date >= "' . $start_date . '"');
}
if ($end_date != ''){
    array_push($where, ' AND start_date <= "' . $end_date . '"');
}

if (!has_permission('bond_returns', '', 'view') || $this->ci->input->post('my_bond_returns')) {
    array_push($where, ' AND ' . db_prefix() . 'bookings.id IN (SELECT booking_id FROM ' . db_prefix() . 'booking_members WHERE staff_id=' . get_staff_user_id() . ')');
}

array_push($where, ' AND "' . TOUR_COMPLETED . '" IN(SELECT name FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'bookings.id and rel_type="booking" ORDER by tag_order ASC)');
array_push($where, ' AND "' . DO_NOT_RETURN_BOND . '" NOT IN(SELECT name FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'bookings.id and rel_type="booking" ORDER by tag_order ASC)');

$statusIds = [];

foreach ($this->ci->bookings_model->get_booking_statuses() as $status) {
    if ($this->ci->input->post('bond_return_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}

if (count($statusIds) > 0) {
    array_push($filter, 'OR status IN (' . implode(', ', $statusIds) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$custom_fields = get_table_custom_fields('bond_returns');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'bookings.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = hooks()->apply_filters('bond_returns_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    '(SELECT GROUP_CONCAT(staff_id SEPARATOR ",") FROM ' . db_prefix() . 'booking_members WHERE booking_id=' . db_prefix() . 'bookings.id ORDER BY staff_id) as members_ids',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('booking/bookings/view/' . $aRow['id']);

    $row[] = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $name = '<a href="' . $link . '">' . $aRow['name'] . '</a>';

    $row[] = '<a href="' . admin_url('booking/clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    
    $row[] = $name;
    
    $row[] = '$200';

    $row['DT_RowClass'] = '';

    $row = hooks()->apply_filters('bond_returns_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
