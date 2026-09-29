<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/App_items_table.php');
include_once(__DIR__ . '/Custom_sales_items_table_common.php');

class Custom_sales_items_table extends App_items_table
{
    use Custom_sales_items_table_common;

    public $orgital_items_tables_class;

    public function __construct($orgital_items_tables_class, $transaction, $type, $for = 'html', $admin_preview = false)
    {
        $this->orgital_items_tables_class = $orgital_items_tables_class;
        parent::__construct($transaction, $type, $for, $admin_preview);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->orgital_items_tables_class, $name)) {
            return call_user_func([$this->orgital_items_tables_class, $name], $arguments);
        }

        return parent::__call($name, $arguments);
    }

    protected function show_tax_per_item()
    {
        return custom_sales_can_show_column('tax', $this->transaction, $this->type) &&
            $this->tax_per_item && hooks()->apply_filters('show_tax_per_item', true, [
                'type'        => $this->type,
                'transaction' => $this->transaction,
            ]);
    }

    /**
     * Builds the actual table items rows preview - Overide parent method
     * @return string
     */
    public function items()
    {
        $html = '';

        /** Customization */
        $CI = &get_instance();
        $titles = $CI->custom_sales_model->get_items_titles($this->transaction->id, $this->type);
        $titlesMappedByOrder = [];
        $titlesWithoutOrder = [];
        foreach ($titles as $value) {

            $order = $value->child_item_order ?? '';

            if (empty($order)) {
                $titlesWithoutOrder[] = (array)$value;
                continue;
            }
            if (!isset($titlesMappedByOrder[$order]))
                $titlesMappedByOrder[$order] = [];

            $titlesMappedByOrder[$order][] = (array)$value;
        }
        /** End customization */


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

            /** Customization */
            $order = $item['item_order'];
            if (isset($titlesMappedByOrder[$order])) {
                $titles = $titlesMappedByOrder[$order];
                foreach ($titles as $value) {
                    $itemHTML .= $this->get_item_title_html($value);
                }
            }
            /** End customization */

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

                if (custom_sales_can_show_column('long_description', $this->transaction, $this->type))
                    if (!empty($item['long_description'])) {
                        $itemHTML .= '<br />';
                    }
            }

            /**
             * Item long description
             */
            if (custom_sales_can_show_column('long_description', $this->transaction, $this->type))
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
            if (custom_sales_can_show_column('qty', $this->transaction, $this->type)) {
                $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . floatVal($item['qty']);

                /**
                 * Maybe item has added unit?
                 */
                if ($item['unit']) {
                    $itemHTML .= ' ' . $item['unit'];
                }

                $itemHTML .= '</td>';
            }

            /**
             * Item rate
             * @var string
             */
            $rate = hooks()->apply_filters(
                'item_preview_rate',
                app_format_money($item['rate'], $this->transaction->currency_name, $this->exclude_currency()),
                ['item' => $item, 'transaction' => $this->transaction]
            );

            if (custom_sales_can_show_column('rate', $this->transaction, $this->type))
                $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . $rate . '</td>';

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
                app_format_money(($item['qty'] * $item['rate']), $this->transaction->currency_name, $this->exclude_currency()),
                $item,
                $this->transaction,
                $this->exclude_currency()
            );

            if (custom_sales_can_show_column('amount', $this->transaction, $this->type))
                $itemHTML .= '<td class="amount" align="right" width="' . $regularItemWidth . '%">' . $item_amount_with_quantity . '</td>';

            // Close table row
            $itemHTML .= '</tr>';

            $html .= $itemHTML;

            $i++;
        }

        /** Customization */
        if (!empty($titlesWithoutOrder)) {
            foreach ($titlesWithoutOrder as $value) {
                $html .= $this->get_item_title_html($value);
            }
        }
        /** End customization */

        return $html;
    }

    /**
     * Html headings preview
     * @return string
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

        if (custom_sales_can_show_column('qty', $this->transaction, $this->type))
            $html .= '<th align="right">' . $this->qty_heading() . '</th>';
        if (custom_sales_can_show_column('rate', $this->transaction, $this->type))
            $html .= '<th align="right">' . $this->rate_heading() . '</th>';

        if ($this->show_tax_per_item()) {
            $html .= '<th align="right">' . $this->tax_heading() . '</th>';
        }
        if (custom_sales_can_show_column('amount', $this->transaction, $this->type))
            $html .= '<th align="right">' . $this->amount_heading() . '</th>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * PDF headings preview
     * @return string
     */
    public function pdf_headings()
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth     = $this->get_regular_items_width(6);
        $customFieldsItems    = $this->get_custom_fields_for_table();

        $tblhtml = '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

        $tblhtml .= '<th width="5%;" align="center">' . $this->number_heading() . '</th>';
        $tblhtml .= '<th width="' . $descriptionItemWidth . '%" align="left">' . $this->item_heading() . '</th>';

        foreach ($customFieldsItems as $cf) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="left">' . $cf['name'] . '</th>';
        }

        if (custom_sales_can_show_column('qty', $this->transaction, $this->type))
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->qty_heading() . '</th>';
        if (custom_sales_can_show_column('rate', $this->transaction, $this->type))
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->rate_heading() . '</th>';

        if ($this->show_tax_per_item()) {
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->tax_heading() . '</th>';
        }
        if (custom_sales_can_show_column('amount', $this->transaction, $this->type))
            $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->amount_heading() . '</th>';
        $tblhtml .= '</tr>';

        return $tblhtml;
    }

    /**
     * Table title row attributes
     * @param  array $titleRow
     * @return string
     */
    protected function tr_attributes_for_title($titleRow)
    {
        $id = $titleRow['id'];

        $trAttributes = '';
        if ($this->admin_preview == true) {
            $trAttributes = ' class="sortable title" data-title-id="' . $id . '"';
        }

        if ($font_size = $this->get_pdf_font_size()) {
            $trAttributes .= ' style="font-size:' . $font_size . 'px;"';
        }

        return $trAttributes;
    }

    /**
     * Table items table data attributes
     * @return string
     */
    protected function td_attributes_for_title()
    {
        $tdAttributes = '';

        if ($this->admin_preview == true) {
            $tdAttributes = ' class="dragger"';
        }

        return $tdAttributes;
    }

    /**
     * Generate tr html for a title row
     *
     * @param array $titleRow
     * @return string
     */
    protected function get_item_title_html($titleRow)
    {
        $title = $titleRow['title'];

        $columns = $this->get_total_number_of_columns();
        $colspan = $columns - 1;

        $styles = get_option('custom_sales_item_title_styles');
        $styles = empty($styles) ? [] : json_decode($styles, true);
        $styles = $styles[$this->for] ?? [];
        $stylesString = "";
        $textColor = $styles['color'] ?? '#000000';
        $fontSize = $styles['font-size'] ?? '16';
        $fontWeight = $styles['font-weight'] ?? 'bold';
        $borderPosition = $styles['border_location'] ?? 'bottom';
        $borderSize = $styles['border-size'] ?? '1';
        $borderColor = $styles['border-color'] ?? 'black';
        $borderType = $styles['border-type'] ?? 'solid';
        $stylesString = "color: $textColor; font-size: {$fontSize}px; font-weight: $fontWeight; border-$borderPosition-width: {$borderSize}px; border-$borderPosition-style: $borderType; border-$borderPosition-color: $borderColor;";

        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth = $this->get_regular_items_width(6);
        $adjustment = $columns - 1; // remove the description column
        $adjustment = $adjustment - 1; // remove item no column
        // The span column width
        $width = $descriptionItemWidth + ($regularItemWidth * $adjustment);

        $titleHtml = '<tr' . $this->tr_attributes_for_title($titleRow) . '>';
        $titleHtml .= '<td ' . $this->td_attributes_for_title() . ' align="center" width="5%" style="' . $stylesString . '"></td>';
        $titleHtml .= '<td align="left" width="' . $width . '%" colspan="' . $colspan . '" class="item-title" style="width:95%;' . $stylesString . '">' . $title . '</td>';
        $titleHtml .= '</tr>';

        return $titleHtml;
    }

    protected function commonFields()
    {
        return ['qty', 'rate', 'amount'];
    }

    /**
     * Get total columns in the table
     *
     * @return int
     */
    protected function get_total_number_of_columns()
    {
        $customFieldsItems    = $this->get_custom_fields_for_table();
        // Calculate headings totals, in case there are custom fields for items
        $totalheadings = $this->show_tax_per_item() == 1 ? 6 : 5;
        foreach ($this->commonFields() as $field) {
            if (!custom_sales_can_show_column($field, $this->transaction, $this->type) && $totalheadings > 0)
                $totalheadings = $totalheadings - 1;
        }
        $totalheadings += count($customFieldsItems);
        return $totalheadings;
    }
    protected function get_description_item_width()
    {
        $item_width = hooks()->apply_filters('item_description_td_width', 38);

        if ($this->get_total_number_of_columns() < 3) return 100 - 5;

        // If show item taxes is disabled in PDF we should increase the item width table heading
        return $this->show_tax_per_item() == 0 ? $item_width + 15 : $item_width;
    }
    protected function get_regular_items_width($adjustment)
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $customFieldsItems    = $this->get_custom_fields_for_table();
        // Calculate headings width, in case there are custom fields for items
        $totalheadings = $this->show_tax_per_item() == 1 ? 4 : 3;
        foreach ($this->commonFields() as $field) {
            if (!custom_sales_can_show_column($field, $this->transaction, $this->type) && $totalheadings > 0) {
                $totalheadings = $totalheadings - 1;
                $adjustment--;
            }
        }
        $totalheadings += count($customFieldsItems);

        if ($totalheadings < 1)
            $totalheadings = 1;

        return (100 - ($descriptionItemWidth + $adjustment)) / $totalheadings;
    }
}