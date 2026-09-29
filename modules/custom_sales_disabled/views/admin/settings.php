<?php

defined('BASEPATH') or exit('No direct script access allowed');
// Font Weight Options
$font_weight_options = array(
    array('value' => 'normal', 'name' => _l('custom_sales_normal')),
    array('value' => 'bold', 'name' => _l('custom_sales_bold')),
    array('value' => 'bolder', 'name' => _l('custom_sales_bolder')),
    array('value' => 'lighter', 'name' => _l('custom_sales_lighter')),
);

// Border Style Options
$border_style_options = array(
    array('value' => 'solid', 'name' => _l('custom_sales_solid')),
    array('value' => 'dashed', 'name' => _l('custom_sales_dashed')),
    array('value' => 'dotted', 'name' => _l('custom_sales_dotted')),
);

// Border Location Options
$border_location_options = array(
    array('value' => 'bottom', 'name' => _l('custom_sales_bottom')),
    array('value' => 'top', 'name' => _l('custom_sales_top')),
);

$items_types = custom_sales_get_item_types(true);

// Items fields
$item_fields_options = array(
    array('value' => 'qty', 'name' => _l('invoice_table_quantity_heading', '', false)),
    array('value' => 'rate', 'name' => _l('invoice_table_rate_heading', '', false)),
    array('value' => 'amount', 'name' => _l('invoice_table_amount_heading', '', false)),
    array('value' => 'tax', 'name' => _l('invoice_table_tax_heading', '', false)),
    array('value' => 'long_description', 'name' => _l('invoice_item_long_description', '', false)),
);

$options_entry = ['value', ['name']];

$groups = ['html', 'pdf'];

$styles = get_option('custom_sales_item_title_styles');
$styles = empty($styles) ? [] : json_decode($styles, true);

$pdf_background_image = get_option('custom_sales_pdf_background_image');
$pdf_background_image_url = !empty($pdf_background_image) ? protected_file_url_by_path(get_upload_path_by_type('company') . $pdf_background_image, true) : '';
?>
<div class="tw-flex tw-flex-col">

    <div>
        <h4><?= _l('custom_sales_settings_deposit_invoice'); ?></h4>
        <hr />

        <?php $key = 'custom_sales_allow_deposit_invoice'; ?>
        <?= render_yes_no_option($key, $key, $key . '_hint'); ?>

        <?php
        $key = 'custom_sales_max_deposit_invoice';
        $value = (int)get_option($key);
        $label = custom_sales_input_label_with_hint($key);
        ?>
        <?= render_input('settings[' . $key . ']', $label, ($value > 1 ? $value : 5), 'number', ['min' => 2, 'step' => 1]); ?>

        <?php $key = 'custom_sales_disable_updating_deposit_invoice'; ?>
        <?= render_yes_no_option($key, $key, $key . '_hint'); ?>

        <?php $key = 'custom_sales_hide_pricing_on_deposit_invoice'; ?>
        <?= render_yes_no_option($key, $key); ?>

        <!-- default payment options -->
        <?php
        $key = 'custom_sales_depoist_invoice_allowed_payment_modes';
        $payment_modes = $this->payment_modes_model->get('');
        $value = get_option($key);
        $value = empty($value) ? [] : json_decode($value);
        ?>
        <label><?= _l('custom_sales_settings_deposit_invoice'); ?> <?php echo _l('payment_methods'); ?></label>
        <select class="selectpicker" name="settings[<?= $key; ?>][]" data-actions-box="true" multiple="true"
            data-width="100%" data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <?php foreach ($payment_modes as $mode) {
                $selected = '';
                if (!empty($value)) {
                    if (in_array($mode['id'], $value))
                        $selected = ' selected';
                } else {
                    if ($mode['selected_by_default'] == 1) {
                        $selected = ' selected';
                    }
                }
            ?>
            <option value="<?php echo $mode['id']; ?>" <?php echo $selected; ?>><?php echo $mode['name']; ?></option>
            <?php } ?>
        </select>

    </div>

    <div class="tw-mt-4 tw-mb-4">
        <hr />
    </div>

    <div>
        <h4><?= _l('custom_sales_settings_items_field'); ?></h4>
        <hr />

        <?php
        $items_types = hooks()->apply_filters('custom_sales_configurable_item_types', $items_types);
        foreach ($items_types as $type) : ?>
        <?php
            $label_key = $type == 'deposit_invoice' ? 'custom_sales_client_items_hidden_field_for_all' : 'custom_sales_client_items_hidden_field';
            $key = 'custom_sales_client_items_hidden_field_for_' . $type;

            $label = custom_sales_input_label_with_hint($label_key, _l($type));
            $value = custom_sales_client_hidden_fields($type);
            ?>

        <?php echo render_select('settings[' . $key . '][]', $item_fields_options, $options_entry, $label, $value, ['multiple' => true], [], '', '', true); ?>

        <?php endforeach; ?>
    </div>

    <div class="tw-mt-4 tw-mb-4">
        <hr />
    </div>

    <?php foreach ($groups as $group) : ?>
    <div>
        <h4><?= _l('custom_sales_settings_' . $group); ?></h4>
        <hr />
        <div class="row">
            <?php
                $key = 'color';
                $label =  'custom_sales_text_color';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? '#000000';
                ?>
            <div class="col-md-6">
                <?php echo render_input($name, $label, $value, 'color'); ?>
            </div>

            <?php
                $key = 'font-weight';
                $label =  'custom_sales_font_weight';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? 'bold';
                ?>
            <div class="col-md-6">
                <?php echo render_select($name, $font_weight_options, $options_entry, $label, $value, [], [], '', '', false); ?>
            </div>

            <?php
                $key = 'font-size';
                $label =  'custom_sales_font_size';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? '16';
                ?>
            <div class="col-md-6">
                <?php echo render_input($name, _l($label) . ' (px)', $value, 'number', array('min' => '1', 'step' => '1')); ?>
            </div>

            <?php
                $key = 'border-size';
                $label =  'custom_sales_border_size';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? '1';
                ?>
            <div class="col-md-6">
                <?php echo render_input($name, _l($label) . ' (px)', $value, 'number', array('min' => '1', 'step' => '1')); ?>
            </div>

            <?php
                $key = 'border-color';
                $label =  'custom_sales_border_color';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? '#000000';
                ?>
            <div class="col-md-6">
                <?php echo render_input($name, $label, $value, 'color'); ?>
            </div>

            <?php
                $key = 'border-style';
                $label =  'custom_sales_border_style';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? 'solid';
                ?>
            <div class="col-md-6">
                <?php echo render_select($name, $border_style_options, $options_entry, $label, $value, [], [], '', '', false); ?>
            </div>

            <?php
                $key = 'border_location';
                $label =  'custom_sales_border_location';
                $name = 'settings[custom_sales_item_title_styles][' . $group . '][' . $key . ']';
                $value = $styles[$group][$key] ?? 'bottom';
                ?>
            <div class="col-md-6">
                <?php echo render_select($name, $border_location_options, $options_entry, $label, $value, [], [], '', '', false); ?>
            </div>

        </div>

    </div>


    <div class="tw-mt-4 tw-mb-4">
        <hr />
    </div>
    <?php endforeach; ?>

    <!-- pdf backgroun image -->
    <div>
        <h4><?php echo _l('custom_sales_pdf_background_image'); ?></h4>
        <hr />

        <?php if ($pdf_background_image != '') { ?>
        <div class="row">
            <div class="col-md-9">
                <img src="<?php echo site_url($pdf_background_image_url); ?>" class="img img-responsive">
            </div>
            <?php if (has_permission('settings', '', 'delete')) { ?>
            <div class="col-md-3 text-right">
                <a href="<?php echo admin_url('custom_sales/remove_pdf_background_image'); ?>" data-toggle="tooltip"
                    title="<?php echo _l('delete'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
            </div>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
        <?php } else { ?>
        <div class="form-group">
            <input type="file" name="custom_sales_pdf_background_image" class="form-control" value=""
                data-toggle="tooltip" title="<?php echo _l('custom_sales_pdf_background_image_hint'); ?>">
        </div>
        <?php } ?>
    </div>

    <div class="tw-mt-4 tw-mb-4">
        <hr />
    </div>

    <div>
        <h4><?php echo _l('custom_sales_other_settings'); ?></h4>
        <hr />

        <?php
        $key = 'custom_sales_add_tax_to_item_table_amounts';
        render_yes_no_option($key, $key, $key . '_hint');
        ?>

        <?php
        $key = 'custom_sales_disable_invoice_update_status';
        $selected = custom_sales_disable_invoice_update_status();
        echo '<input name="settings[' . $key . '][]" value="" type="hidden" />';
        echo render_select(
            'settings[' . $key . '][]',
            array_values(custom_sales_invoice_status_label_map()),
            ['key', ['label']],
            custom_sales_input_label_with_hint($key),
            $selected,
            ['multiple' => true],
            []
        );
        ?>

        <?php
        $key = 'custom_sales_disable_invoice_delete_status';
        $selected = custom_sales_disable_invoice_delete_status();
        echo '<input name="settings[' . $key . '][]" value="" type="hidden" />';
        echo render_select(
            'settings[' . $key . '][]',
            array_values(custom_sales_invoice_status_label_map()),
            ['key', ['label']],
            custom_sales_input_label_with_hint($key),
            $selected,
            ['multiple' => true],
            []
        );
        ?>
    </div>
</div>