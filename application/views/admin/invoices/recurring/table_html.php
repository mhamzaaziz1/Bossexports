<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
  _l('invoice_dt_table_heading_number'),
  _l('invoice_dt_table_heading_date'),
  _l('invoice_dt_table_heading_duedate'),
  array(
    'name'=>_l('invoice_dt_table_heading_client'),
    'th_attrs'=>array('class'=>(isset($client) ? 'not_visible' : ''))
  ),
  _l('invoice_dt_table_heading_amount'),
  _l('invoice_total_tax'),
  [
    'name'=>_l('Year'),
    'th_attrs'=>['class'=>'next-recurring-date']
  ],
  _l('project'),
  _l('tags'),
  _l('invoice_dt_table_heading_status')
];
render_datatable($table_data, 'invoices');
