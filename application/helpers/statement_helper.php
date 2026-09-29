<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Clean and convert any date string format to standard YYYY-MM-DD for SQL queries
 * @param string $date
 * @return string|null
 */
if (!function_exists('clean_date_to_sql')) {
    function clean_date_to_sql($date)
    {
        if (empty($date)) return null;
        $date = trim($date);
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $date, $m)) {
            $p1 = intval($m[1]);
            $p2 = intval($m[2]);
            $year = intval($m[3]);
            if ($p1 > 12) {
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            } else if ($p2 > 12) {
                return sprintf('%04d-%02d-%02d', $year, $p1, $p2);
            } else {
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            }
        }
        if (function_exists('to_sql_date')) {
            $converted = to_sql_date($date);
            if (!empty($converted) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $converted)) {
                return $converted;
            }
        }
        return $date;
    }
}

/**
 * Get customer balance before specific date
 * @param  mixed $customer_id customer id
 * @param  string $date        date to check
 * @return decimal
 */
function before_balance($customer_id, $date)
{
    $CI = &get_instance();
    $date = clean_date_to_sql($date);

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
            (
                SELECT COALESCE(SUM(' . db_prefix() . 'invoices.total+' . db_prefix() . 'invoices.ship_expense+' . db_prefix() . 'invoices.other_expense),0)
                FROM ' . db_prefix() . 'invoices
                WHERE date < "' . $CI->db->escape_str($date) . '"
                AND clientid = ' . $CI->db->escape_str($customer_id) . '
                AND status != ' . Invoices_model::STATUS_DRAFT . '
                AND status != ' . Invoices_model::STATUS_CANCELLED . '
            ) - (
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
                ) + (
                    SELECT COALESCE(SUM(' . db_prefix() . 'invoicepaymentrecords.amount),0)
                    FROM ' . db_prefix() . 'invoicepaymentrecords
                    WHERE ' . db_prefix() . 'invoicepaymentrecords.date < "' . $CI->db->escape_str($date) . '"
                    AND ' . db_prefix() . 'invoicepaymentrecords.invoiceid = 0
                    AND ' . db_prefix() . 'invoicepaymentrecords.client_id=' . $CI->db->escape_str($customer_id) . '
                ) - (
                    SELECT COALESCE(SUM(' . db_prefix() . 'expenses.amount),0)
                    FROM ' . db_prefix() . 'expenses
                    WHERE ' . db_prefix() . 'expenses.clientid = ' . $CI->db->escape_str($customer_id) . ' AND tblexpenses.billable !=1
                    AND ' . db_prefix() . 'expenses.date < "' . $CI->db->escape_str($date) . '"
                )
            )
        ) as balance
    ')->row()->balance;

    if ($balance === null) {
        $balance = 0;
    }

    // Add client's opening balance
    $client = $CI->db->select("balance")->from(db_prefix() . 'clients')->where('userid', $customer_id)->get()->row();
    if ($client && isset($client->balance)) {
        $balance += (float)$client->balance;
    }

    return hooks()->apply_filters('customer_before_balance', $balance, ['customer_id' => $customer_id, 'date' => $date]);
}

/**
 * Get vendor balance before specific date
 * @param  mixed $vendor_id vendor id
 * @param  string $date     date to check
 * @return decimal
 */
function vendor_before_balance($vendor_id, $date)
{
    $CI = &get_instance();
    if (function_exists('to_sql_date')) {
        $converted = to_sql_date($date);
        if (!empty($converted) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $converted)) {
            $date = $converted;
        } else if (function_exists('clean_date_to_sql')) {
            $date = clean_date_to_sql($date);
        }
    } elseif (function_exists('clean_date_to_sql')) {
        $date = clean_date_to_sql($date);
    }

    $po_total = 0;
    $returns_total = 0;
    $payments_total = 0;
    $vendor_balance = 0;

    // Check if pur_orders table exists
    if ($CI->db->table_exists(db_prefix() . 'pur_orders')) {
        // Purchase orders before date
        $po_row = $CI->db->query('
            SELECT COALESCE(SUM(total), 0) as po_total 
            FROM ' . db_prefix() . 'pur_orders 
            WHERE order_date < "' . $CI->db->escape_str($date) . '" 
            AND vendor = ' . $CI->db->escape_str($vendor_id) . ' 
            AND returns = 0
        ')->row();
        if ($po_row) {
            $po_total = (float)$po_row->po_total;
        }

        // Purchase returns before date
        $returns_row = $CI->db->query('
            SELECT COALESCE(SUM(total), 0) as returns_total 
            FROM ' . db_prefix() . 'pur_orders 
            WHERE order_date < "' . $CI->db->escape_str($date) . '" 
            AND vendor = ' . $CI->db->escape_str($vendor_id) . ' 
            AND returns = 1
        ')->row();
        if ($returns_row) {
            $returns_total = (float)$returns_row->returns_total;
        }
    }

    // Purchase payments before date
    if ($CI->db->table_exists(db_prefix() . 'pur_order_payment')) {
        $payments_row = $CI->db->query('
            SELECT COALESCE(SUM(amount), 0) as payments_total 
            FROM ' . db_prefix() . 'pur_order_payment 
            WHERE date < "' . $CI->db->escape_str($date) . '" 
            AND (
                vendor = ' . $CI->db->escape_str($vendor_id) . ' 
                OR pur_order IN (SELECT id FROM ' . db_prefix() . 'pur_orders WHERE vendor = ' . $CI->db->escape_str($vendor_id) . ')
            )
        ')->row();
        if ($payments_row) {
            $payments_total = (float)$payments_row->payments_total;
        }
    }

    // Add vendor's stored balance field from tblpur_vendor
    if ($CI->db->table_exists(db_prefix() . 'pur_vendor')) {
        $vendor = $CI->db->select("balance")->from(db_prefix() . 'pur_vendor')->where('userid', $vendor_id)->get()->row();
        if ($vendor && isset($vendor->balance)) {
            $vendor_balance = (float)$vendor->balance;
        }
    }

    $balance = ($po_total - $returns_total - $payments_total) + $vendor_balance;

    return hooks()->apply_filters('vendor_before_balance', $balance, [
        'vendor_id' => $vendor_id,
        'date'      => $date
    ]);
}
