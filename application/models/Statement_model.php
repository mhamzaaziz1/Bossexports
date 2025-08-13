<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Statement_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get customer statement formatted
     * @param  mixed $customer_id customer id
     * @param  string $from        date from
     * @param  string $to          date to
     * @return array
     */
    /**
     * Calculate aging buckets for customer invoices
     * @param  mixed $customer_id customer id
     * @return array
     */
    public function get_invoices_aging($customer_id)
    {
        // Define aging buckets
        $aging = [
            'current' => 0,
            '1_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            'over_90' => 0,
            'total' => 0
        ];

        // Get all unpaid/partially paid invoices for this customer
        $this->db->select('id, total, date, duedate, status');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('clientid', $customer_id);
        $this->db->where('status !=', Invoices_model::STATUS_PAID);
        $this->db->where('status !=', Invoices_model::STATUS_CANCELLED);
        $this->db->where('status !=', Invoices_model::STATUS_DRAFT);
        $invoices = $this->db->get()->result_array();

        // Get current date for aging calculation
        $today = new DateTime(date('Y-m-d'));

        foreach ($invoices as $invoice) {
            // Calculate amount due for this invoice
            $this->db->select('SUM(amount) as total_paid');
            $this->db->from(db_prefix() . 'invoicepaymentrecords');
            $this->db->where('invoiceid', $invoice['id']);
            $payments = $this->db->get()->row();

            $total_paid = $payments ? $payments->total_paid : 0;
            $amount_due = $invoice['total'] - $total_paid;

            if ($amount_due <= 0) {
                continue; // Skip if fully paid
            }

            // Calculate days overdue
            $due_date = new DateTime($invoice['duedate']);
            $days_overdue = $today->diff($due_date)->days;

            // If due date is in the future, it's not overdue
            if ($today < $due_date) {
                $aging['current'] += $amount_due;
            } elseif ($days_overdue <= 30) {
                $aging['1_30'] += $amount_due;
            } elseif ($days_overdue <= 60) {
                $aging['31_60'] += $amount_due;
            } elseif ($days_overdue <= 90) {
                $aging['61_90'] += $amount_due;
            } else {
                $aging['over_90'] += $amount_due;
            }

            $aging['total'] += $amount_due;
        }

        return $aging;
    }

    public function get_statement($customer_id, $from, $to)
    {
        if (!class_exists('Invoices_model', false)) {
            $this->load->model('invoices_model');
        }

        $sql = 'SELECT
        ' . db_prefix() . 'invoices.id as invoice_id,
        hash,
        ' . db_prefix() . 'invoices.date as date,
        ' . db_prefix() . 'invoices.duedate,
        concat(' . db_prefix() . 'invoices.date, \' \', RIGHT(' . db_prefix() . 'invoices.datecreated,LOCATE(\' \',' . db_prefix() . 'invoices.datecreated) - 3)) as tmp_date,
        ' . db_prefix() . 'invoices.duedate as duedate,
        ' . db_prefix() . 'invoices.total+' . db_prefix() . 'invoices.ship_expense+' . db_prefix() . 'invoices.other_expense as invoice_amount
        FROM ' . db_prefix() . 'invoices WHERE clientid =' . $this->db->escape_str($customer_id);

        if ($from == $to) {
            $sqlDate = 'date="' . $this->db->escape_str($from) . '"';
        } else {
            $sqlDate = '(date BETWEEN "' . $this->db->escape_str($from) . '" AND "' . $this->db->escape_str($to) . '")';
        }

        $sql .= ' AND ' . $sqlDate;

        $invoices = $this->db->query($sql . '
            AND status != ' . Invoices_model::STATUS_DRAFT . '
            AND status != ' . Invoices_model::STATUS_CANCELLED . '
            ORDER By date DESC')->result_array();

        // Credit notes
        // $sql_credit_notes = 'SELECT
        // ' . db_prefix() . 'creditnotes.id as credit_note_id,
        // ' . db_prefix() . 'creditnotes.date as date,
        // concat(' . db_prefix() . 'creditnotes.date, \' \', RIGHT(' . db_prefix() . 'creditnotes.datecreated,LOCATE(\' \',' . db_prefix() . 'creditnotes.datecreated) - 3)) as tmp_date,
        // ' . db_prefix() . 'creditnotes.total as credit_note_amount
        // FROM ' . db_prefix() . 'creditnotes WHERE clientid =' . $this->db->escape_str($customer_id) . ' AND status != 3';

        // $sql_credit_notes .= ' AND ' . $sqlDate;

        // $credit_notes = $this->db->query($sql_credit_notes)->result_array();

        // Credits applied
        $sql_credits_applied = 'SELECT
        ' . db_prefix() . 'credits.id as credit_id,
        invoice_id as credit_invoice_id,
        ' . db_prefix() . 'credits.credit_id as credit_applied_credit_note_id,
        ' . db_prefix() . 'credits.date as date,
        concat(' . db_prefix() . 'credits.date, \' \', RIGHT(' . db_prefix() . 'credits.date_applied,LOCATE(\' \',' . db_prefix() . 'credits.date_applied) - 3)) as tmp_date,
        ' . db_prefix() . 'credits.amount as credit_amount
        FROM ' . db_prefix() . 'credits
        JOIN ' . db_prefix() . 'creditnotes ON ' . db_prefix() . 'creditnotes.id = ' . db_prefix() . 'credits.credit_id
        ';

        $sql_credits_applied .= '
        WHERE clientid =' . $this->db->escape_str($customer_id);

        $sqlDateCreditsAplied = str_replace('date', db_prefix() . 'credits.date', $sqlDate);

        $sql_credits_applied .= ' AND ' . $sqlDateCreditsAplied;
        $credits_applied = $this->db->query($sql_credits_applied)->result_array();

        // Replace error ambigious column in where clause
        $sqlDatePayments = str_replace('date', db_prefix() . 'invoicepaymentrecords.date', $sqlDate);

        $sql_payments = 'SELECT
        ' . db_prefix() . 'invoicepaymentrecords.id as payment_id,
        ' . db_prefix() . 'invoicepaymentrecords.date as date,
        concat(' . db_prefix() . 'invoicepaymentrecords.date, \' \', RIGHT(' . db_prefix() . 'invoicepaymentrecords.daterecorded,LOCATE(\' \',' . db_prefix() . 'invoicepaymentrecords.daterecorded) - 3)) as tmp_date,
        ' . db_prefix() . 'invoicepaymentrecords.invoiceid as payment_invoice_id,
        ' . db_prefix() . 'invoicepaymentrecords.amount as payment_total,
         ' . db_prefix() . 'invoicepaymentrecords.note as pnote
        FROM ' . db_prefix() . 'invoicepaymentrecords
        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
        WHERE ' . $sqlDatePayments . ' AND ' . db_prefix() . 'invoices.clientid = ' . $this->db->escape_str($customer_id) . ' and ' . db_prefix() . 'invoicepaymentrecords.transactionid = ""
        ORDER by ' . db_prefix() . 'invoicepaymentrecords.date DESC';

        $payments = $this->db->query($sql_payments)->result_array();

        $sql_payments = 'SELECT
        ' . db_prefix() . 'invoicepaymentrecords.id as payment_id,
        ' . db_prefix() . 'invoicepaymentrecords.date as date,
        concat(' . db_prefix() . 'invoicepaymentrecords.date, \' \', RIGHT(' . db_prefix() . 'invoicepaymentrecords.daterecorded,LOCATE(\' \',' . db_prefix() . 'invoicepaymentrecords.daterecorded) - 3)) as tmp_date,
        ' . db_prefix() . 'invoicepaymentrecords.invoiceid as payment_invoice_id,
        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as payment_total,
         ' . db_prefix() . 'invoicepaymentrecords.note as pnote
        FROM ' . db_prefix() . 'invoicepaymentrecords
        LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
        WHERE ' . $sqlDatePayments . ' AND (' . db_prefix() . 'invoices.clientid = ' . $this->db->escape_str($customer_id) . ' or ' . db_prefix() . 'invoicepaymentrecords.client_id = ' . $this->db->escape_str($customer_id) . ' ) and ' . db_prefix() . 'invoicepaymentrecords.transactionid != "" group by ' . db_prefix() . 'invoicepaymentrecords.transactionid  
        ORDER by ' . db_prefix() . 'invoicepaymentrecords.date DESC';

        $payments02 = $this->db->query($sql_payments)->result_array();
        // var_dump($this->db->last_query());die;


        $sql_payments = 'SELECT
        ' . db_prefix() . 'invoicepaymentrecords.id as payment_id,
        ' . db_prefix() . 'invoicepaymentrecords.date as date,
        concat(' . db_prefix() . 'invoicepaymentrecords.date, \' \', RIGHT(' . db_prefix() . 'invoicepaymentrecords.daterecorded,LOCATE(\' \',' . db_prefix() . 'invoicepaymentrecords.daterecorded) - 3)) as tmp_date,
        ' . db_prefix() . 'invoicepaymentrecords.invoiceid as payment_invoice_id,
        ' . db_prefix() . 'invoicepaymentrecords.amount as payment_total
        FROM ' . db_prefix() . 'invoicepaymentrecords
        WHERE ' . $sqlDatePayments . ' AND ' . db_prefix() . 'invoicepaymentrecords.client_id = ' . $this->db->escape_str($customer_id) . ' and ' . db_prefix() . 'invoicepaymentrecords.transactionid = ""
        ORDER by ' . db_prefix() . 'invoicepaymentrecords.date DESC';

        $payments01 = $this->db->query($sql_payments)->result_array();

        $sqlDateexpense = str_replace('date', db_prefix() . 'expenses.date', $sqlDate);
        $sql_expense = 'SELECT
        ' . db_prefix() . 'expenses.id as invoice_id,
        ' . db_prefix() . 'expenses.date as date,
        concat(' . db_prefix() . 'expenses.date) as tmp_date,
        ' . db_prefix() . 'expenses.invoiceid as payment_invoice_id,
        ' . db_prefix() . 'expenses.amount as invoice_amount
        FROM ' . db_prefix() . 'expenses
        WHERE ' . $sqlDateexpense . ' AND ' . db_prefix() . 'expenses.clientid = ' . $this->db->escape_str($customer_id) . ' AND tblexpenses.billable !=1
        ORDER by ' . db_prefix() . 'expenses.date DESC';

        $expense = $this->db->query($sql_expense)->result_array();


        $sql_expense = 'SELECT
        SUM(' . db_prefix() . 'expenses.amount) as invoice_amount
        FROM ' . db_prefix() . 'expenses
        WHERE ' . $sqlDateexpense . ' AND ' . db_prefix() . 'expenses.clientid = ' . $this->db->escape_str($customer_id) . ' AND tblexpenses.billable !=1
        ORDER by ' . db_prefix() . 'expenses.date DESC';

        $sumexpense = $this->db->query($sql_expense)->row()->invoice_amount;


        $sqlCreditNoteRefunds = str_replace('date', 'refunded_on', $sqlDate);

        $sql_credit_notes_refunds = 'SELECT id as credit_note_refund_id,
        credit_note_id as refund_credit_note_id,
        (amount) as refund_amount,
        concat(' . db_prefix() . 'creditnote_refunds.refunded_on, \' \', RIGHT(' . db_prefix() . 'creditnote_refunds.created_at,LOCATE(\' \',' . db_prefix() . 'creditnote_refunds.created_at) - 3)) as tmp_date,
        refunded_on as date FROM ' . db_prefix() . 'creditnote_refunds
        WHERE ' . $sqlCreditNoteRefunds . ' AND credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes WHERE clientid=' . $this->db->escape_str($customer_id) . ')
        ';

        $credit_notes_refunds = $this->db->query($sql_credit_notes_refunds)->result_array();

        // merge results
        $merged = array_merge($invoices, $payments,$payments01,$payments02,$expense,  $credits_applied, $credit_notes_refunds);
        //var_dump($merged);

        // sort by date
        usort($merged, function ($a, $b) {
            // fake date select sorting
            return strtotime($a['tmp_date']) - strtotime($b['tmp_date']);
        });

        // Define final result variable
        $result = [];
        // Store in result array key
        $result['result'] = $merged;

        // Invoiced amount during the period
        $result['invoiced_amount'] = $this->db->query('SELECT
        SUM(' . db_prefix() . 'invoices.total+' . db_prefix() . 'invoices.ship_expense+' . db_prefix() . 'invoices.other_expense) as invoiced_amount
        FROM ' . db_prefix() . 'invoices
        WHERE clientid = ' . $this->db->escape_str($customer_id) . '
        AND ' . $sqlDate . ' AND status != ' . Invoices_model::STATUS_DRAFT . ' AND status != ' . Invoices_model::STATUS_CANCELLED . '')
            ->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }

        $result['credit_notes_amount'] = $this->db->query('SELECT
        SUM(' . db_prefix() . 'creditnotes.total) as credit_notes_amount
        FROM ' . db_prefix() . 'creditnotes
        WHERE clientid = ' . $this->db->escape_str($customer_id) . '
        AND ' . $sqlDate . ' AND status != 3')
            ->row()->credit_notes_amount;

        if ($result['credit_notes_amount'] === null) {
            $result['credit_notes_amount'] = 0;
        }

        $result['refunds_amount'] = $this->db->query('SELECT
        SUM(' . db_prefix() . 'creditnote_refunds.amount) as refunds_amount
        FROM ' . db_prefix() . 'creditnote_refunds
        WHERE ' . $sqlCreditNoteRefunds . ' AND credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes WHERE clientid=' . $this->db->escape_str($customer_id) . ')
        ')->row()->refunds_amount;

        if ($result['refunds_amount'] === null) {
            $result['refunds_amount'] = 0;
        }

        $result['invoiced_amount'] = $result['invoiced_amount'];

        // Amount paid during the period
        $result['amount_paid'] = $this->db->query('SELECT
        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
        FROM ' . db_prefix() . 'invoicepaymentrecords
        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
        WHERE ' . $sqlDatePayments . ' AND ' . db_prefix() . 'invoices.clientid = ' . $this->db->escape_str($customer_id))
            ->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }


        $result['direct_paid'] = $this->db->query('SELECT
        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
        FROM ' . db_prefix() . 'invoicepaymentrecords
        WHERE ' . $sqlDatePayments . ' AND client_id = ' . $this->db->escape_str($customer_id))
            ->row()->amount_paid;
        if ($result['direct_paid'] === null) {
            $result['direct_paid'] = 0;
        }



        // Get beginning balance using the helper function
        $result['beginning_balance'] = before_balance($customer_id, $from);

        $dec = get_decimal_places();
        $result['invoiced_amount']=$result['invoiced_amount']+ $sumexpense;
        // var_dump($sumexpense);die;
        if (function_exists('bcsub')) {
            $result['balance_due'] = bcsub($result['invoiced_amount'], $result['amount_paid'], $dec);
            $result['balance_due'] = bcsub($result['balance_due'], $result['direct_paid'], $dec);
            $result['balance_due'] = bcadd($result['balance_due'], $result['beginning_balance'], $dec);
            // var_dump($result['direct_paid']);die;
            $result['balance_due'] = bcsub($result['balance_due'], $result['refunds_amount'], $dec);
        } else {
            $result['balance_due'] = number_format($result['invoiced_amount'] - ($result['amount_paid']+$result['direct_paid']), $dec, '.', '');
            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // var_dump($result['balance_due']);die;
            $result['balance_due'] = $result['balance_due'] - number_format($result['refunds_amount'], $dec, '.', '');
        }
        // $result['balance_due']= ;
        // $result['balance_due']=$result['balance_due'] + $sumexpense;

        // Subtract amount paid - refund, because the refund is not actually paid amount
        $result['amount_paid'] = $result['amount_paid'] + $result['direct_paid'] - $result['refunds_amount'];

        $result['client_id'] = $customer_id;
        $result['client']    = $this->clients_model->get($customer_id);
        $result['from']      = $from;
        $result['to']        = $to;

        $customer_currency = $this->clients_model->get_customer_default_currency($customer_id);
        $this->load->model('currencies_model');

        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency);
        } else {
            $currency = $this->currencies_model->get_base_currency();
        }

        $result['currency'] = $currency;

        // Add aging data to the result
        $result['aging'] = $this->get_invoices_aging($customer_id);

        return hooks()->apply_filters('(statement)', $result);
    }

    /**
     * Send customer statement to email
     * @param  mixed $customer_id customer id
     * @param  array $send_to     array of contact emails to send
     * @param  string $from        date from
     * @param  string $to          date to
     * @param  string $cc          email CC
     * @return boolean
     */
    public function send_statement_to_email($customer_id, $send_to, $from, $to, $cc = '')
    {
        $sent = false;
        if (is_array($send_to) && count($send_to) > 0) {

            $statement = $this->get_statement($customer_id, to_sql_date($from), to_sql_date($to));
            set_mailing_constant();
            $pdf = statement_pdf($statement);

            $pdf_file_name = slug_it(_l('customer_statement') . '-' . $statement['client']->company);

            $attach = $pdf->Output($pdf_file_name . '.pdf', 'S');

            $i = 0;
            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    $template = mail_template('customer_statement', $contact->email, $contact_id, $statement, $cc);

                    $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $pdf_file_name . '.pdf',
                            'type'       => 'application/pdf',
                        ]);

                    if ($template->send()) {
                        $sent = true;
                    }
                }
                $i++;
            }

            if ($sent) {
                return true;
            }
        }

        return false;
    }
}
