<?php

defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
    _l('the_number_sign'),
    _l('booking_customer'),
    _l('booking_name'),
    _l('bond_amount'),
];

render_datatable($table_data, isset($class) ?  $class : 'bond_returns', [], [
  'data-last-order-identifier' => 'booking',
  'data-default-order'  => get_table_last_order('booking'),
]);
