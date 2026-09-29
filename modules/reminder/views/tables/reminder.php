<?php
defined('BASEPATH') or exit('No direct script access allowed');
$baseCurrency = get_base_currency();
$aColumns = [
    'date',
    'assigned_to',
    'customer',
    'contact',
    'description',
    'rel_type'
];
$reminderStatus=$this->ci->input->post('complete_reminder');
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'reminders';
if($reminderStatus=='0'){
    $where=[];
}else{
   $where  = ['AND is_complete="1"'];
}
$filter = [];
//Check number filter
if ($this->ci->input->post('reminder_filter_number')) {
    array_push($filter, "AND " . db_prefix() . "reminders.id LIKE '%" . removeQ($this->ci->input->post('reminder_filter_number')) . "%'");
}
//Check date filter
if ($this->ci->input->post('reminder_filter_date_f')) {
    array_push($filter, "AND date >= '" .  strtotimemod($this->ci->input->post('reminder_filter_date_f')) . "'");
}
if ($this->ci->input->post('reminder_filter_date_t')) {
    array_push($filter, "AND date <= '" .  strtotimemod($this->ci->input->post('reminder_filter_date_t')) . "'");
}
//Check status filter
if ($this->ci->input->post('reminder_filter_related')) {
    array_push($filter, "AND " . db_prefix() . "reminders.rel_type = '" . $this->ci->input->post('reminder_filter_related')."'");
}
//Check company filter
if ($this->ci->input->post('reminder_filter_company')) {
    array_push($filter, "AND customer = " . get_client_id_by_company($this->ci->input->post('reminder_filter_company')));
}
//Check name filter
if ($this->ci->input->post('reminder_filter_contact_val')) {
    array_push($filter, "AND contact = " . get_contact_id_by_full_name($this->ci->input->post('reminder_filter_contact_val')));
}
//Check status filter
if ($this->ci->input->post('reminder_filter_assigned')) {
    array_push($filter, "AND " . db_prefix() . "reminders.assigned_to = " . $this->ci->input->post('reminder_filter_assigned'));
}
//Check email filter
if ($this->ci->input->post('reminder_filter_description')) {
    array_push($filter, "AND description LIKE '%" . $this->ci->input->post('reminder_filter_description') . "%'");
}
//Check follow up date filter
if ($this->ci->input->post('filter_date_follow_f') && $this->ci->input->post('filter_date_follow_t')) {
    array_push($filter, "AND follow_up_date BETWEEN '" . strtotimemod($this->ci->input->post('filter_date_follow_f')) . "' AND '" . strtotimemod($this->ci->input->post('filter_date_follow_t')) . "'");
}
$agents    = $this->ci->reminder_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned_to IN (' . implode(', ', $agentsIds) . ')');
}
$customer    = $this->ci->reminder_model->getCustomersData();
$customerIds = [];
foreach ($customer as $agent) {
    if ($this->ci->input->post('customer_' . $agent['userid'])) {
        array_push($customerIds, $agent['userid']);
    }
}
if (count($customerIds) > 0) {
    array_push($filter, 'AND customer IN (' . implode(', ', $customerIds) . ')');
}
$years      = $this->ci->reminder_model->get_reminder_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}
if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}
$join          = [];
$custom_fields = get_table_custom_fields('reminder');
$aColumns = hooks()->apply_filters('reminder_table_sql_columns', $aColumns);
// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'reminders.id as id']);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $numberOutput = '<a href="' . admin_url('reminder/list_reminder/' . $aRow['id']) . '" onclick="init_reminder(' . $aRow['id'] . '); return false;">' ._d($aRow['date']) . '</a>';
    $row[] = $numberOutput;
    $row[] = !empty(get_staff($aRow['assigned_to'])) ? get_staff($aRow['assigned_to'])->firstname.' '.get_staff($aRow['assigned_to'])->lastname : '';
    $row[] = !empty(get_client($aRow['customer'])) ? get_client($aRow['customer'])->company : '';
    $row[] =  get_contact_full_name($aRow['contact']);
    $row[] = $aRow['description'];
    
    $row[] = ucfirst(_l($aRow['rel_type']));
    $row['DT_RowClass'] = 'has-row-options'.' complete-'.get_complete_reminder($aRow['id']);
    $row['DT_RowLink'] = $aRow['id'];
    $row = hooks()->apply_filters('reminder_table_row_data', $row, $aRow);
    $output['aaData'][] = $row;
}
