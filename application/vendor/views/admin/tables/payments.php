<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = [
    db_prefix() . 'invoicepaymentrecords.id as id',
    'invoiceid',
    'paymentmode',
    'transactionid',
    get_sql_select_client_company(),
    'amount',
    db_prefix() . 'invoicepaymentrecords.date as date',
    ];

$join = [
    'LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
    'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode',
    ];

$where = [];
// Check if clientid is set in the params
$clientid = isset($clientid) ? $clientid : '';
if ($clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid=' . $this->ci->db->escape_str($clientid));
}

// Filter by amount
if ($this->ci->input->post('amount') && $this->ci->input->post('amount') != '') {
    array_push($where, 'AND ' . db_prefix() . 'invoicepaymentrecords.amount = ' . $this->ci->db->escape_str($this->ci->input->post('amount')));
}

// Filter by date
if ($this->ci->input->post('payment_from') && $this->ci->input->post('payment_from') != '') {
    array_push($where, 'AND ' . db_prefix() . 'invoicepaymentrecords.date >= "' . $this->ci->db->escape_str(to_sql_date($this->ci->input->post('payment_from'))) . '"');
}

if ($this->ci->input->post('payment_to') && $this->ci->input->post('payment_to') != '') {
    array_push($where, 'AND ' . db_prefix() . 'invoicepaymentrecords.date <= "' . $this->ci->db->escape_str(to_sql_date($this->ci->input->post('payment_to'))) . '"');
}

if (!has_permission('payments', '', 'view')) {
    $whereUser = '';
    $whereUser .= 'AND (invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE (addedfrom=' . get_staff_user_id() . ' AND addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "invoices" AND capability="view_own")))';
    if (get_option('allow_staff_view_invoices_assigned') == 1) {
        $whereUser .= ' OR invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE sale_agent=' . get_staff_user_id() . ')';
    }
    $whereUser .= ')';
    array_push($where, $whereUser);
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'invoicepaymentrecords';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'payment_modes.name as payment_mode_name',
    db_prefix() . 'payment_modes.id as paymentmodeid',
    'paymentmethod',
    ]);

$output  = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->model('payment_modes_model');
$payment_gateways = $this->ci->payment_modes_model->get_payment_gateways(true);

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('payments/payment/' . $aRow['id']);


    $options = icon_btn('payments/payment/' . $aRow['id'], 'pencil-square-o');

    if ($hasPermissionDelete) {
        $options .= icon_btn('payments/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $numberOutput = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . $link . '">' . _l('view') . '</a>';
    if ($hasPermissionDelete) {
        $numberOutput .= ' | <a href="' . admin_url('payments/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    if(!empty($aRow['invoiceid'])){
    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . format_invoice_number($aRow['invoiceid']) . '</a>';
        }else{
            $row[] = '<a href="' . $link . '"><button class="btn btn-info">' . _l('Allocate Invoice') . '</button></a>';
        }
    $outputPaymentMode = $aRow['payment_mode_name'];


    // Since version 1.0.1
    if (is_null($aRow['paymentmodeid'])) {
        foreach ($payment_gateways as $gateway) {
            if ($aRow['paymentmode'] == $gateway['id']) {
                $outputPaymentMode = $gateway['name'];
            }
        }
    }

    if (!empty($aRow['paymentmethod'])) {
        $outputPaymentMode .= ' - ' . $aRow['paymentmethod'];
    }
    else if(empty($outputPaymentMode)){
        $outputPaymentMode = "Retainer Released";
    }
    $row[] = $outputPaymentMode;

    $row[] = $aRow['transactionid'];

        $this->ci->db->select('company , tblinvoicepaymentrecords.client_id as cid');
        $this->ci->db->from('tblinvoicepaymentrecords');
        $this->ci->db->where('tblinvoicepaymentrecords.id', $aRow['id']);
        $this->ci->db->join('tblclients', 'tblclients.userid = tblinvoicepaymentrecords.client_id');
        $query = $this->ci->db->get()->result_array();

    if($aRow['clientid']!=""){
    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    }else{
    $row[] = '<a href="' . admin_url('clients/client/' . $query[0]['cid']) . '">' . $query[0]['company'] . '</a>';
    }
    // else{
    //     $row[] = '<a href="' . get_relation_values(get_relation_data('customer',$aRow['client_id']),'customer')['link'] . '">' . get_relation_values(get_relation_data('customer',$aRow['client_id']),'customer')['Name']. '</a>';
    // }
    $row[] = app_format_money($aRow['amount'], $aRow['currency_name']);

    $row[] = _d($aRow['date']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
