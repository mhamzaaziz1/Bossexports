<?php

defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
    _l('the_number_sign'),
    _l('tours_dt_name'),
    _l('tour_status'),
    _l('tours_dt_datestart'),
    [
        'name'     => _l('tour_duedate'),
        'th_attrs' => ['class' => 'duedate'],
    ],
    _l('tour_assigned'),
    _l('tags'),
    _l('tours_list_priority'),
];

array_unshift($table_data, [
    'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tours"><label></label></div>',
    'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visible')],
]);

$custom_fields = get_custom_fields('tours', [
    'show_on_table' => 1,
]);

foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = hooks()->apply_filters('tours_table_columns', $table_data);

render_datatable($table_data, 'tours', [], [
        'data-last-order-identifier' => 'tours',
        'data-default-order'         => get_table_last_order('tours'),
]);
