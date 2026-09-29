<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/App_items_table.php');
include_once(__DIR__ . '/Custom_sales_items_table_common.php');

class Custom_sales_deposit_items_table extends App_items_table
{
    use Custom_sales_items_table_common;

    public $orgital_items_tables_class;

    public function __construct($orgital_items_tables_class, $transaction, $type, $for = 'html', $admin_preview = false)
    {
        $this->orgital_items_tables_class = $orgital_items_tables_class;
        parent::__construct($transaction, $type, $for, $admin_preview);
    }
}