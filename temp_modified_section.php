<?php
// This is a temporary file to hold the modified credit_cf calculation

// For the $select array (around line 1727)
$select_credit_cf = '                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',';

// For the $aColumns array (around line 1821)
$aColumns_credit_cf = '                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',';

// The entire $select array with the modified credit_cf calculation
$select_array = '            $select = [
                db_prefix() . \'invoices.number\',
                get_sql_select_client_company(),
                db_prefix() . \'invoices.status\',
                \'YEAR(\' . db_prefix() . \'invoices.date) as year\',
                db_prefix() . \'invoices.date\',
                db_prefix() . \'invoices.duedate\',
                db_prefix() . \'invoices.total as invoice_amount\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id) as cash_paid\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND DATE(\' . db_prefix() . \'invoicepaymentrecords.date) = DATE(\' . db_prefix() . \'invoices.date)) as cash_paid_out\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND note LIKE "%VAT%") as vat_refunded\',
                \'(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "\' . db_prefix() . \'sales_activity"), (SELECT COALESCE(rel_id, 0) FROM \' . db_prefix() . \'sales_activity WHERE rel_type="invoice" AND rel_id = \' . db_prefix() . \'invoices.id LIMIT 1), 0)) as sales_order\',
                \'(\' . db_prefix() . \'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id)) as amount_due\',
                \'(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "\' . db_prefix() . \'acc_accounts"), (SELECT COALESCE(balance,0) FROM \' . db_prefix() . \'acc_accounts WHERE key_name LIKE "%Zim%"  LIMIT 1), 0)) as zim_account\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE DATE(\' . db_prefix() . \'creditnotes.date) = DATE(\' . db_prefix() . \'invoices.date)) as credit_note\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND paymentmode != 2) as bank\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND paymentmode = 2) as cash\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date < \' . db_prefix() . \'invoices.date) as credit_bf\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',
                \'(\' . db_prefix() . \'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id) - (SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE DATE(\' . db_prefix() . \'creditnotes.date) = DATE(\' . db_prefix() . \'invoices.date))) as total_balance\',
                db_prefix() . \'invoices.adminnote as director_note\',
            ];';

// The entire $aColumns array with the modified credit_cf calculation
$aColumns_array = '            $aColumns = [
                db_prefix() . \'invoices.date\',
                db_prefix() . \'invoices.status\',
                db_prefix() . \'invoices.number\',
                \'company\',
                db_prefix() . \'invoices.total as invoice_amount\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id) as cash_paid\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND DATE(\' . db_prefix() . \'invoicepaymentrecords.date) = DATE(\' . db_prefix() . \'invoices.date)) as cash_paid_out\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND note LIKE "%VAT%") as vat_refunded\',
                \'(\' . db_prefix() . \'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id)) as amount_due\',
                \'(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "\' . db_prefix() . \'sales_activity"), (SELECT COALESCE(rel_id, 0) FROM \' . db_prefix() . \'sales_activity WHERE rel_type="invoice" AND rel_id = \' . db_prefix() . \'invoices.id LIMIT 1), 0)) as sales_order\',
                \'(SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "\' . db_prefix() . \'acc_accounts"), (SELECT COALESCE(balance,0) FROM \' . db_prefix() . \'acc_accounts WHERE key_name LIKE "%Zim%"  LIMIT 1), 0)) as zim_account\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE DATE(\' . db_prefix() . \'creditnotes.date) = DATE(\' . db_prefix() . \'invoices.date)) as credit_note\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND paymentmode != 2) as bank\',
                \'(SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id AND paymentmode = 2) as cash\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date < \' . db_prefix() . \'invoices.date) as credit_bf\',
                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',
                \'(\' . db_prefix() . \'invoices.total - (SELECT COALESCE(SUM(amount),0) FROM \' . db_prefix() . \'invoicepaymentrecords WHERE invoiceid = \' . db_prefix() . \'invoices.id) - (SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE DATE(\' . db_prefix() . \'creditnotes.date) = DATE(\' . db_prefix() . \'invoices.date))) as total_balance\',
                db_prefix() . \'invoices.adminnote as director_note\',
                db_prefix() . \'invoices.id\',
                db_prefix() . \'invoices.clientid\'
            ];';
?>