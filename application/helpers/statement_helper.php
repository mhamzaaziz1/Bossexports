<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get customer balance before specific date
 * @param  mixed $customer_id customer id
 * @param  string $date        date to check
 * @return decimal
 */
function before_balance($customer_id, $date)
{
    $CI = &get_instance();
    
    // Load required models if not already loaded
    if (!class_exists('invoices_model', false)) {
        $CI->load->model('invoices_model');
    }
    
    if (!class_exists('clients_model', false)) {
        $CI->load->model('clients_model');
    }
    
    // Beginning balance is all invoices amount before the date - payments received before date
    $balance = $CI->db->query('
        SELECT (
        COALESCE(SUM(' . db_prefix() . 'invoices.total),0) - (
        (
        SELECT COALESCE(SUM(' . db_prefix() . 'invoicepaymentrecords.amount),0)
        FROM ' . db_prefix() . 'invoicepaymentrecords
        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
        WHERE ' . db_prefix() . 'invoicepaymentrecords.date < "' . $CI->db->escape_str($date) . '"
        AND ' . db_prefix() . 'invoices.clientid=' . $CI->db->escape_str($customer_id) . '
        ) + (
            SELECT COALESCE(SUM(' . db_prefix() . 'creditnotes.total),0)
            FROM ' . db_prefix() . 'creditnotes
            WHERE ' . db_prefix() . 'creditnotes.date < "' . $CI->db->escape_str($date) . '"
            AND ' . db_prefix() . 'creditnotes.clientid=' . $CI->db->escape_str($customer_id) . '
        )+(
        SELECT COALESCE(SUM(' . db_prefix() . 'invoicepaymentrecords.amount),0)
        FROM ' . db_prefix() . 'invoicepaymentrecords
        WHERE ' . db_prefix() . 'invoicepaymentrecords.date < "' . $CI->db->escape_str($date) . '"
        AND ' . db_prefix() . 'invoicepaymentrecords.invoiceid = 0
        AND ' . db_prefix() . 'invoicepaymentrecords.client_id=' . $CI->db->escape_str($customer_id) . '
        )-(
        SELECT COALESCE(SUM(' . db_prefix() . 'expenses.amount),0)
        FROM ' . db_prefix() . 'expenses
        WHERE ' . db_prefix() . 'expenses.clientid = ' . $CI->db->escape_str($customer_id) . ' AND tblexpenses.billable !=1
        AND ' . db_prefix() . 'expenses.date < "' . $CI->db->escape_str($date) . '"
        )
    )
        )
        as balance FROM ' . db_prefix() . 'invoices
        WHERE date < "' . $CI->db->escape_str($date) . '"
        AND clientid = ' . $CI->db->escape_str($customer_id) . '
        AND status != ' . Invoices_model::STATUS_DRAFT . '
        AND status != ' . Invoices_model::STATUS_CANCELLED)
          ->row()->balance;

    if ($balance === null) {
        $balance = 0;
    }
    
    // Add client's opening balance
    $client = $CI->db->select("balance")->from('tblclients')->where('userid', $customer_id)->get()->row();
    if ($client) {
        $balance += (float)$client->balance;
    }

    return hooks()->apply_filters('customer_before_balance', $balance, ['customer_id' => $customer_id, 'date' => $date]);
}