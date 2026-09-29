<?php

defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
   _l('the_number_sign'),
   _l('booking_name'),
    [
         'name'     => _l('booking_customer'),
         'th_attrs' => ['class' => isset($client) ? 'not_visible' : ''],
    ],
   _l('tags'),
   _l('booking_start_date'),
   [
      'name' => _l('booking_deadline'),
      'th_attrs' => ['class' => 'not_visible'],
    ],
   _l('booking_members'),
   _l('booking_status'),
];

$custom_fields = get_custom_fields('bookings', ['show_on_table' => 1]);
foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = hooks()->apply_filters('bookings_table_columns', $table_data);

render_datatable($table_data, isset($class) ?  $class : 'bookings', [], [
  'data-last-order-identifier' => 'bookings',
  'data-default-order'  => get_table_last_order('bookings'),
]);
