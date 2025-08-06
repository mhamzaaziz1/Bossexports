<?php
// This is a temporary file to hold the modified credit_cf calculation
// For the $select array (around line 1727)
$select_credit_cf = '                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',';

// For the $aColumns array (around line 1821)
$aColumns_credit_cf = '                \'(SELECT COALESCE(SUM(total),0) FROM \' . db_prefix() . \'creditnotes WHERE \' . db_prefix() . \'creditnotes.clientid=\' . db_prefix() . \'invoices.clientid AND \' . db_prefix() . \'creditnotes.date <= \' . db_prefix() . \'invoices.date) as credit_cf\',';
?>