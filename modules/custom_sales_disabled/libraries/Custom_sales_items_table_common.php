<?php

defined('BASEPATH') or exit('No direct script access allowed');

trait Custom_sales_items_table_common
{
    /**
     * All taxes used for the preview
     * This is used to display the taxes on the bottom of the invoice
     * @var array
     */
    public function taxes()
    {
        if (empty($this->transaction->deposit_from_estimate_id)) return parent::taxes();

        $amount = $this->transaction->subtotal;
        foreach ($this->taxes as $tax) {

            $total_tax = (floatval($tax['taxrate']) / 100) * $amount;

            $this->taxes[$tax['tax_name']]['total_tax'] = $total_tax;
            // Tax name is in format NAME|PERCENT
            $tax_name_array                           = explode('|', $tax['tax_name']);
            $this->taxes[$tax['tax_name']]['taxname'] = $tax_name_array[0];
        }

        return $this->order_taxes($this->taxes);
    }
}