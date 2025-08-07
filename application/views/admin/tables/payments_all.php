<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = [
    db_prefix() . 'invoicepaymentrecords.id as id',
    'invoiceid',
    'paymentmode',
    'transactionid',
    db_prefix() . 'clients.company as company',
    db_prefix() . 'clients.userid as clientid',
    'SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as total_amount',
    db_prefix() . 'invoicepaymentrecords.date as date',
    'note',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
    'LEFT JOIN ' . db_prefix() . 'payment_modes ON ' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode',
];

$where = []; $having='';

if (!empty($clientid)) {
    $where[] = 'AND ' . db_prefix() . 'clients.userid=' . $this->ci->db->escape_str($clientid);
}
// Add HAVING condition for SUM(amount)
if ($this->ci->input->post('amount') && $this->ci->input->post('amount') != '') {
    $escapedAmount = $this->ci->db->escape_str($this->ci->input->post('amount'));
    $having = ' HAVING total_amount = ' . $escapedAmount;
} else {
    $having = '';
}


if ($from = $this->ci->input->post('payment_from')) {
    $where[] = 'AND ' . db_prefix() . 'invoicepaymentrecords.date >= "' . $this->ci->db->escape_str(to_sql_date($from)) . '"';
}

if ($to = $this->ci->input->post('payment_to')) {
    $where[] = 'AND ' . db_prefix() . 'invoicepaymentrecords.date <= "' . $this->ci->db->escape_str(to_sql_date($to)) . '"';
}

if (!has_permission('payments', '', 'view')) {
    $staff_id = get_staff_user_id();
    $whereUser = 'AND (
        invoiceid IN (
            SELECT id FROM ' . db_prefix() . 'invoices
            WHERE (addedfrom=' . $staff_id . ' AND addedfrom IN (
                SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "invoices"
            ))
        )';

    if (get_option('allow_staff_view_invoices_assigned') == 1) {
        $whereUser .= ' OR invoiceid IN (
            SELECT id FROM ' . db_prefix() . 'invoices WHERE sale_agent=' . $staff_id . '
        )';
    }

    $whereUser .= ')';
    $where[] = $whereUser;
}

$sIndexColumn = 'id';
$sTable = db_prefix() . 'invoicepaymentrecords';
$sGroupBy = 'GROUP BY ' . db_prefix() . 'invoicepaymentrecords.transactionid, ' . db_prefix() . 'invoicepaymentrecords.date' . $having;

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'payment_modes.name as payment_mode_name',
    db_prefix() . 'payment_modes.id as paymentmodeid',
    'paymentmethod',
], $sGroupBy);

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

    $numberOutput .= '<br><a href="javascript:void(0)" class="btn btn-success btn-icon" data-toggle="modal" data-target="#noteModal" data-id="' . $aRow['id'] . '">' . _l('Add Note') . '</a>';
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    // Invoice Link
    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . format_invoice_number($aRow['invoiceid']) . '</a>';

    // Payment mode
    $outputPaymentMode = $aRow['payment_mode_name'];
    if (is_null($aRow['paymentmodeid'])) {
        foreach ($payment_gateways as $gateway) {
            if ($aRow['paymentmode'] == $gateway['id']) {
                $outputPaymentMode = $gateway['name'];
                break;
            }
        }
    }
    if (!empty($aRow['paymentmethod'])) {
        $outputPaymentMode .= ' - ' . $aRow['paymentmethod'];
    } elseif (empty($outputPaymentMode)) {
        $outputPaymentMode = "Retainer Released";
    }

    $row[] = $outputPaymentMode;
    $row[] = $aRow['transactionid'];

    // Client company
    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    // Amount (already grouped)
    $row[] = app_format_number($aRow['total_amount']);

    $row[] = _d($aRow['date']);
    $row[] = $aRow['note'];

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
