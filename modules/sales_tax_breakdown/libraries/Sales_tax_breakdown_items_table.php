<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . 'libraries/App_items_table.php');

class Sales_tax_breakdown_items_table extends App_items_table
{
    public function __construct($transaction, $type, $for = 'html', $admin_preview = false)
    {
        parent::__construct($transaction, $type, $for, $admin_preview);
    }

    /**
     * Override items processing to add Tax Amount and handle Discounts
     */
    public function items()
    {
        $html = '';

        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth     = $this->get_regular_items_width(6);
        $customFieldsItems    = $this->get_custom_fields_for_table();

        if ($this->for == 'html') {
            $descriptionItemWidth = $descriptionItemWidth - 5;
            $regularItemWidth     = $regularItemWidth - 5;
        }

        $i = 1;
        foreach ($this->items as $item) {
            $itemHTML = '';

            // Open table row
            $itemHTML .= '<tr' . $this->tr_attributes($item) . '>';

            // Table data number
            $itemHTML .= '<td' . $this->td_attributes() . ' align="center" width="5%">' . $i . '</td>';

            $itemHTML .= '<td class="description" align="left" width="' . $descriptionItemWidth . '%">';

            /**
             * Item description
             */
            if (!empty($item['description'])) {
                $itemHTML .= '<span style="font-size:' . $this->get_pdf_font_size() . 'px;"><strong>'
                    . $this->period_merge_field($item['description'])
                    . '</strong></span>';

                if (!empty($item['long_description'])) {
                    $itemHTML .= '<br />';
                }
            }

            /**
             * Item long description
             */
            if (!empty($item['long_description'])) {
                $itemHTML .= '<span style="color:#424242;">' . $this->period_merge_field($item['long_description']) . '</span>';
            }

            $itemHTML .= '</td>';

            /**
             * Item custom fields
             */
            foreach ($customFieldsItems as $custom_field) {
                $itemHTML .= '<td align="left" width="' . $regularItemWidth . '%">' . get_custom_field_value($item['id'], $custom_field['id'], 'items') . '</td>';
            }

            /**
             * Item quantity
             */
            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . floatVal($item['qty']);

            /**
             * Maybe item has added unit?
             */
            if ($item['unit']) {
                $itemHTML .= ' ' . $item['unit'];
            }

            $itemHTML .= '</td>';

            /**
             * Item rate
             */
            $rate = hooks()->apply_filters(
                'item_preview_rate',
                app_format_money($item['rate'], $this->transaction->currency_name, $this->exclude_currency()),
                ['item' => $item, 'transaction' => $this->transaction]
            );

            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . $rate . '</td>';

            /**
             * Item discount - Logic from line_discounts module
             */
            $discount_amount = 0;
            $discount_amount_format = '';
            // Check if line_discount_rate exists (compatibility with line_discounts module)
            if (!empty($item['line_discount_rate']) && ($item['line_discount_rate'] > 0 || $item['line_discount_rate'] < 0)) {
                $discount_amount = $item['rate'] * $item['qty'] * $item['line_discount_rate'] / 100;
                $discount_amount_format = "%" . $item['line_discount_rate'] . " (" . app_format_money($discount_amount, $this->transaction->currency_name, $this->exclude_currency()) . ")";
            }

            // Always show discount column if configured or if we want to enforce structure
            // For now, only show if we detect the field or to keep column alignment
            if ($this->has_discount_column()) {
                $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . $discount_amount_format . '</td>';
            }

            /**
             * Items table taxes HTML
             */
            $itemHTML .= $this->taxes_html($item, $regularItemWidth);

            /**
             * Tax Amount Column
             */
            $taxAmount = 0;
            $subtotal_before_tax = ($item['rate'] * $item['qty']) - $discount_amount;
            
            // Calculate tax on the DISCOUNTED amount (standard behavior)
            if(isset($item['taxname']) && is_array($item['taxname'])){
                 foreach($item['taxname'] as $taxString){
                      $parts = explode('|', $taxString);
                      if(count($parts) >= 2){
                          $taxAmount += ($subtotal_before_tax * floatval($parts[1])) / 100;
                      }
                 }
            } else {
                 // Fallbacks
                 if(isset($item['taxrate'])) {
                     $taxAmount += ($subtotal_before_tax * $item['taxrate']) / 100;
                 }
                 if ($item['id'] && function_exists('get_invoice_item_taxes')) { 
                    $rel_taxes = [];
                    if($this->type == 'invoice') {
                        $rel_taxes = get_invoice_item_taxes($item['id']);
                    } elseif($this->type == 'estimate') {
                         $rel_taxes = get_estimate_item_taxes($item['id']);
                    } elseif ($this->type == 'proposal') {
                        $rel_taxes = get_proposal_item_taxes($item['id']);
                    } elseif ($this->type == 'credit_note') {
                        $rel_taxes = get_credit_note_item_taxes($item['id']);
                    }

                    foreach($rel_taxes as $t) {
                        $taxAmount += ($subtotal_before_tax * $t['taxrate']) / 100;
                    }
                 }
            }

            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . app_format_money($taxAmount, $this->transaction->currency_name, $this->exclude_currency()) . '</td>';


            /**
             * Item Amount (Inclusive of Tax)
             * Formula: (Qty * Rate) - Discount + Tax
             */
            $final_amount = ($item['qty'] * $item['rate']) - $discount_amount + $taxAmount;

            $item_amount_display = hooks()->apply_filters(
                'item_preview_amount_with_currency',
                app_format_money($final_amount, $this->transaction->currency_name, $this->exclude_currency()),
                $item,
                $this->transaction,
                $this->exclude_currency()
            );

            $itemHTML .= '<td class="amount" align="right" width="' . $regularItemWidth . '%">' . $item_amount_display . '</td>';

            // Close table row
            $itemHTML .= '</tr>';

            $html .= $itemHTML;

            $i++;
        }

        return $html;
    }

    /**
     * Override PDF Headings
     */
    public function pdf_headings()
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth     = $this->get_regular_items_width(6);
        $customFieldsItems    = $this->get_custom_fields_for_table();

        $tblhtml = '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

        $tblhtml .= '<th width="5%" align="center">' . $this->number_heading() . '</th>';
        $tblhtml .= '<th width="' . $descriptionItemWidth . '%" align="left">' . $this->item_heading() . '</th>';

        foreach ($customFieldsItems as $cf) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="left">' . $cf['name'] . '</th>';
        }

        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->qty_heading() . '</th>';
        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->rate_heading() . '</th>';

        // Discount Column
        if ($this->has_discount_column()) {
             $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">Discount</th>';
        }

        if ($this->show_tax_per_item()) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->tax_heading() . '</th>';
        }

        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">Tax Amount</th>';
        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->amount_heading() . '</th>';
        $tblhtml .= '</tr>';

        return $tblhtml;
    }

    /**
     * Override HTML Headings
     */
    public function html_headings()
    {
        $html = '<tr>';
        $html .= '<th align="center">' . $this->number_heading() . '</th>';
        $html .= '<th class="description" width="' . $this->get_description_item_width() . '%" align="left">' . $this->item_heading() . '</th>';

        $customFieldsItems = $this->get_custom_fields_for_table();
        foreach ($customFieldsItems as $cf) {
            $html .= '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
        }

        $html .= '<th align="right">' . $this->qty_heading() . '</th>';
        $html .= '<th align="right">' . $this->rate_heading() . '</th>';

        // Discount Column
        if ($this->has_discount_column()) {
             $html .= '<th align="right">Discount</th>';
        }

        if ($this->show_tax_per_item()) {
            $html .= '<th align="right">' . $this->tax_heading() . '</th>';
        }

        $html .= '<th align="right">Tax Amount</th>';
        $html .= '<th align="right">' . $this->amount_heading() . '</th>';
        $html .= '</tr>';

        return $html;
    }

    protected function get_regular_items_width($adjustment)
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $customFieldsItems    = $this->get_custom_fields_for_table();
        
        $totalheadings = $this->show_tax_per_item() == 1 ? 4 : 3; 
        $totalheadings += count($customFieldsItems);
        
        // Add one for Tax Amount
        $totalheadings += 1;
        
        // Add one for Discount if present
        if ($this->has_discount_column()) {
            $totalheadings += 1;
        }

        return (100 - ($descriptionItemWidth + $adjustment)) / $totalheadings;
    }
    
    private function has_discount_column() {
        // Check if any item has a discount or if explicitly enabled
        // For now, trigger if we find 'line_discount_rate' in any item
        foreach ($this->items as $item) {
            if (!empty($item['line_discount_rate'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Force show tax per item regardless of settings
     */
    protected function show_tax_per_item()
    {
        return true;
    }
}
