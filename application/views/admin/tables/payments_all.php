<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = [
    db_prefix() . 'invoicepaymentrecords.id as id',
    'invoiceid',
    'paymentmode',
    'transactionid',
    get_sql_select_client_company(),
    'SUM(amount) as amount',
    db_prefix() . 'invoicepaymentrecords.date as date',
    'note',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
    'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode',
];

$where = [];
// Check if clientid is defined and not empty
if (isset($clientid) && $clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'clients.userid=' . $this->ci->db->escape_str($clientid));
}

if (!has_permission('payments', '', 'view')) {
    $whereUser = '';
    $whereUser .= 'AND (invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE (addedfrom=' . get_staff_user_id() . ' AND addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "invoices"")))';
    if (get_option('allow_staff_view_invoices_assigned') == 1) {
        $whereUser .= ' OR invoiceid IN (SELECT id FROM ' . db_prefix() . 'invoices WHERE sale_agent=' . get_staff_user_id() . ')';
    }
    $whereUser .= ')';
    array_push($where, $whereUser);
}

// Date filtering
$custom_date_select = '';
$months_report = $this->ci->input->post('report_months');
if ($months_report != '') {
    if (is_numeric($months_report)) {
        // Last month
        if ($months_report == '1') {
            $beginMonth = date('Y-m-01', strtotime('first day of last month'));
            $endMonth   = date('Y-m-t', strtotime('last day of last month'));
        } else {
            $months_report = (int) $months_report;
            $months_report--;
            $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
            $endMonth   = date('Y-m-t');
        }

        $custom_date_select = ' AND (' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
    } elseif ($months_report == 'this_month') {
        $custom_date_select = ' AND (' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
    } elseif ($months_report == 'this_year') {
        $custom_date_select = ' AND (' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
            date('Y-m-d', strtotime(date('Y-01-01'))) .
            '" AND "' .
            date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
    } elseif ($months_report == 'last_year') {
        $custom_date_select = ' AND (' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
            date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
            '" AND "' .
            date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
    } elseif ($months_report == 'custom') {
        $from_date = $this->ci->input->post('report_from');
        $to_date = $this->ci->input->post('report_to');

        if ($from_date != '' && $to_date != '') {
            // Convert dates to SQL format
            $from_date_sql = to_sql_date($from_date);
            $to_date_sql = to_sql_date($to_date);

            if ($from_date == $to_date) {
                $custom_date_select = ' AND ' . db_prefix() . 'invoicepaymentrecords.date = "' . $this->ci->db->escape_str($from_date_sql) . '"';
            } else {
                $custom_date_select = ' AND (' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $this->ci->db->escape_str($from_date_sql) . '" AND "' . $this->ci->db->escape_str($to_date_sql) . '")';
            }
        }
    }

    if ($custom_date_select != '') {
        array_push($where, $custom_date_select);
    }
}

// Amount filtering
$amount = $this->ci->input->post('amount');

if ($amount != '') {
    // Since we're using SUM(amount) in the SELECT, we need to filter in the WHERE clause
    // This will filter records where the amount matches exactly
    array_push($where, ' AND ' . db_prefix() . 'invoicepaymentrecords.amount = ' . $this->ci->db->escape_str($amount));
}

array_push($where, 'Group by ' . db_prefix() . 'invoicepaymentrecords.transactionid, ' . db_prefix() . 'invoicepaymentrecords.date');

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'invoicepaymentrecords';

// Debug the WHERE clause (uncomment to debug)
// var_dump($where);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'payment_modes.name as payment_mode_name',
    db_prefix() . 'payment_modes.id as paymentmodeid',
    'paymentmethod',
    'amount',
    'transactionid',
    'tblinvoicepaymentrecords.date',
]);
// var_dump($this->ci->db->last_query());

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
    $numberOutput .= '<br><a href="javascript:void(0)" class="btn btn-success btn-icon" data-toggle="modal" data-target="#noteModal" data-id="'.$aRow['id'].'" >'._l('Add Note') . '</a>';
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . format_invoice_number($aRow['invoiceid']) . '</a>';

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
    if(!empty($aRow['transactionid'])){
        $this->ci->db->select('sum(amount) as amount');
        $this->ci->db->from('tblinvoicepaymentrecords');
        $this->ci->db->where('tblinvoicepaymentrecords.transactionid', $aRow['transactionid']);
        $this->ci->db->group_by('tblinvoicepaymentrecords.transactionid');
        $pquery = $this->ci->db->get()->result_array();
        $row[] = $pquery[0]['amount'];
    }else{
        $row[] = $aRow['amount'];
    }

    $row[] = _d($aRow['date']);
    $row[] = $aRow['note'];

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
