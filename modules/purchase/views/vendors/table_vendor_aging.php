<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('purchase', '', 'delete');

$custom_fields = get_table_custom_fields('vendors');
$this->ci->db->query("SET sql_mode = ''");

$aColumns = [
    '1',
    db_prefix().'pur_vendor.userid as userid',
    'company',
    'firstname',
    'email',
    db_prefix().'pur_vendor.phonenumber as phonenumber',
    db_prefix().'pur_vendor.active',
    
    db_prefix().'pur_vendor.datecreated as datecreated',
];

$sIndexColumn = 'userid';
$sTable       = db_prefix().'pur_vendor';
$where        = [];
// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN '.db_prefix().'pur_contacts ON '.db_prefix().'pur_contacts.userid='.db_prefix().'pur_vendor.userid AND '.db_prefix().'pur_contacts.is_primary=1',
];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN '.db_prefix().'customfieldsvalues as ctable_' . $key . ' ON '.db_prefix().'pur_vendor.userid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}



$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix().'pur_contacts.id as contact_id',
    'lastname',
    db_prefix().'pur_vendor.zip as zip',
    'registration_confirmed',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    // User id
    $row[] = $aRow['userid'];

    // Company
    $company  = $aRow['company'];
    $isPerson = false;

    if ($company == '') {
        $company  = _l('no_company_view_profile');
        $isPerson = true;
    }

    $url = admin_url('purchase/vendor/' . $aRow['userid']);

    if ($isPerson && $aRow['contact_id']) {
        $url .= '?contactid=' . $aRow['contact_id'];
    }

    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '<a href="' . $url . '">' . _l('view') . '</a>';

    if ($aRow['registration_confirmed'] == 0 && is_admin()) {
        $company .= ' | <a href="' . admin_url('purchase/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
    }
    if (!$isPerson) {
        $company .= ' | <a href="' . admin_url('purchase/vendor/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
    }
    if ($hasPermissionDelete) {
        $company .= ' | <a href="' . admin_url('purchase/delete_vendor/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $company .= '</div>';

    $row[] = $company;

    $CI = & get_instance();
        
        $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date <= CURDATE() and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date <= CURDATE() and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and date <= CURDATE()')->row()->amount_paid ;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date <= CURDATE()')->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['refunds_amount'], $dec, '.', '');
        // $extend.'<br>Balance='. $extend='Opening Balance: '. number_format($result['beginning_balance']).'<br>Purchases: '.number_format($result['invoiced_amount']).'<br>paid='.(number_format($result['amount_paid'])).'<br>Refund: '.number_format($result['refunds_amount']);
    $row[] = number_format($result['balance_due']);
        
        
        
        
        $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date >= CURDATE() - INTERVAL 7 DAY and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date >= CURDATE() - INTERVAL 7 DAY and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and order_date >= CURDATE() - INTERVAL 7 DAY and returns=0')->row()->amount_paid ;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date >= CURDATE()-INTERVAL 7 DAY')->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        // $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['refunds_amount'], $dec, '.', '');
        // $extend.'<br>Balance='. 
        $extend='Purchases: '.number_format($result['invoiced_amount']).'<br>paid='.number_format($result['amount_paid']);
        if(number_format($result['balance_due']) < 0){
            $result['balance_due']=0;
        }
    $row[] = number_format($result['balance_due']);
    //  $row[] = $extend;

    

    // Primary contact email
    $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 7 Day  and order_date >= CURDATE() -  INTERVAL 14 DAY and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 7 Day and order_date >= CURDATE() -  INTERVAL 14 DAY and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 7 Day  and order_date >= CURDATE() -  INTERVAL 14 DAY and returns=0')->row()->amount_paid;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date < CURDATE() -  INTERVAL 7 DAY and date >= CURDATE() -  INTERVAL 14 DAY')->row()->amount_paid ;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        // $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        // $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['refunds_amount'], $dec, '.', '');
        // $extend='Opening Balance: '. number_format($result['beginning_balance']).'<br>Purchases: '.number_format($result['invoiced_amount']).'<br>paid='.(number_format($result['amount_paid']));
        if(number_format($result['balance_due']) < 0){
            $result['balance_due']=0;
        }
    $row[] = number_format($result['balance_due']);

    $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 14 day and order_date >= CURDATE() -  INTERVAL 30 DAY and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 14 day and order_date >= CURDATE() -  INTERVAL 30 DAY and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 14 day and order_date >= CURDATE() -  INTERVAL 30 DAY and returns=0')->row()->amount_paid;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date < CURDATE() -  INTERVAL 14 day and date >= CURDATE() -  INTERVAL 30 DAY')->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        // $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['refunds_amount'], $dec, '.', '');
        // $extend='Opening Balance: '. number_format($result['beginning_balance']).'<br>Purchases: '.number_format($result['invoiced_amount']).'<br>paid='.(number_format($result['amount_paid']));
        
        if(number_format($result['balance_due']) < 0){
            $result['balance_due']=0;
        }
    $row[] = number_format($result['balance_due']);
        
    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
    <input type="checkbox"' . ($aRow['registration_confirmed'] == 0 ? ' disabled' : '') . ' data-switch-url="' . admin_url() . 'pur_vendor/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow[db_prefix().'pur_vendor.active'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow[db_prefix().'pur_vendor.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 30 day and order_date >= CURDATE() - INTERVAL 60 DAY and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 30 day and order_date >= CURDATE() - INTERVAL 60 DAY and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 30 day and order_date >= CURDATE() - INTERVAL 60 DAY and returns=0')->row()->amount_paid;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date < CURDATE() -  INTERVAL 30 day and date >= CURDATE() - INTERVAL 60 DAY')->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        // $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
            // $result['balance_due'] = $result['balance_due'] + number_format($result['refunds_amount'], $dec, '.', '');
        // $extend='Opening Balance: '. number_format($result['beginning_balance']).'<br>Purchases: '.number_format($result['invoiced_amount']).'<br>paid='.(number_format($result['amount_paid']));
    if(number_format($result['balance_due']) < 0){
            $result['balance_due']=0;
        }
    $row[] = number_format($result['balance_due']);
    // $row[] = $extend;


    $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders 
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 60 day and returns=0')->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }
        $result['invoiced_amount1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_orders.total) as invoiced_amount
        FROM ' . db_prefix() . 'pur_orders
        WHERE vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 60 day and returns=1')->row()->invoiced_amount;

        if ($result['invoiced_amount1'] === null) {
            $result['invoiced_amount1'] = 0;
        }
        $result['invoiced_amount']=$result['invoiced_amount']-$result['invoiced_amount1'];

        
        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        JOIN ' . db_prefix() . 'pur_orders ON ' . db_prefix() . 'pur_orders.id = ' . db_prefix() . 'pur_order_payment.pur_order
        WHERE ' .  db_prefix() . 'pur_orders.vendor = ' . $aRow['userid'].' and order_date < CURDATE() -  INTERVAL 60 day and returns=0')->row()->amount_paid ;
        
        $result['amount_paid1'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'pur_order_payment.amount) as amount_paid
        FROM ' . db_prefix() . 'pur_order_payment
        WHERE tblpur_order_payment.vendor = ' . $aRow['userid'].' and tblpur_order_payment.pur_order=0 and date < CURDATE() -  INTERVAL 60 day and date >= CURDATE() -  INTERVAL 90 DAY')->row()->amount_paid ;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        if ($result['amount_paid1'] === null) {
            $result['amount_paid1'] = 0;
        }
        // $result['amount_paid']=$result['amount_paid1']+$result['amount_paid'];
        $result['beginning_balance']=0;
        $abc =  $CI->db->select("balance")->from('tblpur_vendor')->where('userid', $aRow['userid'])->get()->row()->balance;
        // var_dump((float)$abc[0]->balance); die;
        // $result['beginning_balance'] += (float)$abc;

        $dec = get_decimal_places();
            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
    if(number_format($result['balance_due']) < 0){
            $result['balance_due']=0;
        }
    $row[] = number_format($result['balance_due']);
    // $row[]=$CI->db->last_query();

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    if ($aRow['registration_confirmed'] == 0) {
        $row['DT_RowClass'] .= ' alert-info requires-confirmation';
        $row['Data_Title']  = _l('customer_requires_registration_confirmation');
        $row['Data_Toggle'] = 'tooltip';
    }

    $row = hooks()->apply_filters('customers_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
