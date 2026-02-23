<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . 'libraries/App_items_table.php');

class Line_discounts_items_table extends App_items_table
{

    public function __construct($transaction, $type, $for = 'html', $admin_preview = false)
    {
        parent::__construct($transaction, $type, $for, $admin_preview);
    }

    private $has_discount = false;


    public function items()
    {

        $html = '';

        $this->check_has_discount();

        $descriptionItemWidth = $this->get_description_item_width();

        $regularItemWidth  = $this->get_regular_items_width(6);
        $customFieldsItems = $this->get_custom_fields_for_table();

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

            $itemHTML .= '<td class="description" align="left;" width="' . $descriptionItemWidth . '%">';

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
            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . e(floatVal($item['qty']));

            /**
             * Maybe item has added unit?
             */
            if ($item['unit']) {
                $itemHTML .= ' ' . e($item['unit']);
            }

            $itemHTML .= '</td>';

            /**
             * Item rate
             * @var string
             */
            $rate = hooks()->apply_filters(
                'item_preview_rate',
                app_format_money($item['rate'], $this->transaction->currency_name, $this->exclude_currency()),
                ['item' => $item, 'transaction' => $this->transaction]
            );

            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . e($rate) . '</td>';


            /**
             * Item discount
             */
            $discount_amount = 0;
            $discount_amount_format = '';
            if ( !empty( $item['line_discount_rate'] ) && ( $item['line_discount_rate'] > 0 || $item['line_discount_rate'] < 0 ) )
            {
                $discount_amount =  $item['rate'] * $item['qty'] * $item['line_discount_rate'] / 100 ;
                $discount_amount_format = "%".$item['line_discount_rate']." (".app_format_money( $discount_amount , $this->transaction->currency_name, $this->exclude_currency()).")";
            }

            if ( $this->has_discount )
                $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . e($discount_amount_format) . '</td>';



            /**
             * Items table taxes HTML custom function because it's too general for all features/options
             * @var string
             */
            $itemHTML .= $this->taxes_html($item, $regularItemWidth);

            /**
             * Possible action hook user to include tax in item total amount calculated with the quantiy
             * eq Rate * QTY + TAXES APPLIED
             */
            $item_amount_with_quantity = hooks()->apply_filters(
                'item_preview_amount_with_currency',
                app_format_money(($item['qty'] * $item['rate']) - $discount_amount , $this->transaction->currency_name, $this->exclude_currency()),
                $item,
                $this->transaction,
                $this->exclude_currency()
            );

            $itemHTML .= '<td class="amount" align="right" width="' . $regularItemWidth . '%">' . e($item_amount_with_quantity) . '</td>';

            // Close table row
            $itemHTML .= '</tr>';

            $html .= $itemHTML;

            $i++;
        }

        return $html;
    }

    private function check_has_discount()
    {

        $this->has_discount = true;

        $line_option = line_discount_get_option_value( 'hide_column_without_discount' , 0 );

        if ( $line_option == 1 )
            $this->has_discount = false;


        if ( !empty( $this->items ) )
        {

            foreach ($this->items as $item)
            {

                if ( !empty( $item['line_discount_rate'] ) && (  $item['line_discount_rate'] > 0 ||  $item['line_discount_rate'] < 0 ) )
                    $this->has_discount = true;

            }

        }

    }

    public function pdf_headings()
    {

        $this->check_has_discount();

        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth     = $this->get_regular_items_width(6);
        $customFieldsItems    = $this->get_custom_fields_for_table();


        $tblhtml = '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

        $tblhtml .= '<th width="5%;" align="center">' . $this->number_heading() . '</th>';
        $tblhtml .= '<th width="' . $descriptionItemWidth . '%" align="left">' . $this->item_heading() . '</th>';

        foreach ($customFieldsItems as $cf) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="left">' . $cf['name'] . '</th>';
        }

        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->qty_heading() . '</th>';
        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->rate_heading() . '</th>';


        if ( $this->has_discount )
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">'. _l('line_discount_rate') .'</th>';


        if ($this->show_tax_per_item()) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->tax_heading() . '</th>';
        }

        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->amount_heading() . '</th>';
        $tblhtml .= '</tr>';

        return $tblhtml;

    }


    public function html_headings()
    {

        $this->check_has_discount();

        $html = '<tr>';
        $html .= '<th align="center">' . $this->number_heading() . '</th>';
        $html .= '<th class="description" width="' . $this->get_description_item_width() . '%" align="left">' . $this->item_heading() . '</th>';

        $customFieldsItems = $this->get_custom_fields_for_table();
        foreach ($customFieldsItems as $cf) {
            $html .= '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
        }

        $html .= '<th align="right">' . $this->qty_heading() . '</th>';
        $html .= '<th align="right">' . $this->rate_heading() . '</th>';


        if ( $this->has_discount )
            $html .= '<th align="right">' . _l('line_discount_rate') . '</th>';


        if ($this->show_tax_per_item()) {
            $html .= '<th align="right">' . $this->tax_heading() . '</th>';
        }

        $html .= '<th align="right">' . $this->amount_heading() . '</th>';
        $html .= '</tr>';

        return $html;
    }

    protected function get_description_item_width()
    {
        $item_width = hooks()->apply_filters('item_description_td_width', 30);

        // If show item taxes is disabled in PDF we should increase the item width table heading
        return $this->show_tax_per_item() == 0 ? $item_width + 15 : $item_width;
    }


    protected function get_regular_items_width($adjustment)
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $customFieldsItems    = $this->get_custom_fields_for_table();
        // Calculate headings width, in case there are custom fields for items
        $totalheadings = $this->show_tax_per_item() == 1 ? 4 : 3;
        $totalheadings += count($customFieldsItems);

        if ( $this->has_discount )
            $totalheadings += 1;


        return (100 - ($descriptionItemWidth + $adjustment)) / $totalheadings;
    }

}