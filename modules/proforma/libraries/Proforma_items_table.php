<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/App_items_table.php');

class Proforma_items_table extends App_items_table
{
    public function __construct($transaction, $type = '', $for = 'html', $admin_preview = false)
    {
        // Check if arguments are passed as an array (common with CI/MX Loader)
        if (is_array($transaction)) {
            // Unpack arguments
            $params = $transaction;
            $transaction = isset($params['transaction']) ? $params['transaction'] : (isset($params[0]) ? $params[0] : null);
            $type        = isset($params['type']) ? $params['type'] : (isset($params[1]) ? $params[1] : 'proforma');
            $for         = isset($params['for']) ? $params['for'] : (isset($params[2]) ? $params[2] : 'html');
            $admin_preview = isset($params['admin_preview']) ? $params['admin_preview'] : (isset($params[3]) ? $params[3] : false);
        }

        parent::__construct($transaction, $type, $for, $admin_preview);
    }
    
    // Override set_headings to fallback to invoice keys if proforma keys are missing
    public function set_headings($alias = '')
    {
        $this->headings['number'] = _l('the_number_sign', '', false);
        $this->headings['item']   = _l('invoice_table_item_heading', '', false);
        $this->headings['qty']    = _l('invoice_table_quantity_heading', '', false);
        $this->headings['rate']   = _l('invoice_table_rate_heading', '', false);
        $this->headings['tax']    = _l('invoice_table_tax_heading', '', false);
        $this->headings['amount'] = _l('invoice_table_amount_heading', '', false);

        return $this;
    }
}
