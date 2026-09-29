<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (strpos(uri_string(), 'estimates') !== false) : ?>
<div id="deposit-invoice-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= _l('estimate_create_deposit_invoices'); ?></h4>
            </div>
            <div class="modal-body">
                <?= form_open('', ['id' => 'depositInvoiceForm']); ?>
                <div id="serialRows">
                    <!-- Default 2 rows will be added here -->
                    <?php for ($i = 1; $i <= 2; $i++) : ?>
                    <div class="form-group tw-flex tw-justify-between tw-flex custom-sales-deposit-invoice-row">
                        <div><?= _l('invoice'); ?> <span class="sn"><?= $i; ?></span></div>
                        <?php
                                $label = _l('custom_sales_date');
                                $date = $i === 1 ? date('Y-m-d') : '';
                                ?>
                        <?php $input = render_input('date[]', '', $date, 'date', ['placeholder' => $label, 'min' => date('Y-m-d'), 'required' => 'required'], ['data-toggle' => 'tooltip', 'data-title' => $label, 'title' => $label], 'tw-flex');
                                echo str_replace('id="date[]"', '', $input);
                                ?>
                        <?php $label = _l('custom_sales_amount') . ' (' . get_base_currency()->symbol . ')'; ?>
                        <?php
                                $input = render_input('amount[]', '', '', 'number', ['placeholder' => $label, 'min' => 0, 'step' => '0.01', 'required' => 'required'], ['data-toggle' => 'tooltip', 'data-title' => $label, 'title' => $label, 'data-class' => 'amount'], 'tw-flex');
                                echo str_replace('id="amount[]"', '', $input);
                                ?>
                        <div class=""></div>
                    </div>
                    <?php endfor; ?>
                </div>
                <hr />
                <div class="form-group tw-flex tw-justify-between tw-flex">
                    <div></div>
                    <div></div>
                    <div class="col-md-5 bold"><?= _l('invoice_total'); ?>: <span
                            class="custom-sales-deposit-invoice-total"></span>
                    </div>
                </div>

                <button type="button" class="btn btn-warning" id="addRowBtn"><?= _l('custom_sales_add_more'); ?> <i
                        class="fa fa-plus"></i></button>
                <div class="tw-flex tw-justify-end">
                    <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const CUSTOM_SALES_MODULE = {
    lang: {
        custom_sales_add_item_title: "<?= _l('custom_sales_add_item_title'); ?>",
        estimate_convert_to_invoice: "<?= _l('estimate_convert_to_invoice'); ?>",
        estimate_create_deposit_invoices: "<?= _l('estimate_create_deposit_invoices'); ?>",
        'custom_sales_max_deposit_invoices_reached': "<?= _l('custom_sales_max_deposit_invoices_reached', get_option('custom_sales_max_deposit_invoice')); ?>"
    },
    settings: {
        custom_sales_allow_deposit_invoice: <?= (int)get_option('custom_sales_allow_deposit_invoice'); ?>,
        custom_sales_max_deposit_invoice: <?= (int)get_option('custom_sales_max_deposit_invoice'); ?>,
        custom_sales_disable_updating_deposit_invoice: <?= custom_sales_disable_updating_deposit_invoice(); ?>,
        custom_sales_add_tax_to_item_table_amounts: <?= (int)get_option('custom_sales_add_tax_to_item_table_amounts'); ?>,
    },
    item_types: <?= json_encode(custom_sales_get_item_types()); ?>,
    currency: <?= json_encode(get_base_currency()); ?>,
};
</script>
<!-- Module custom admin script -->
<script src="<?= module_dir_url(CUSTOM_SALES_MODULE_NAME, 'assets/js/admin.js'); ?>"></script>