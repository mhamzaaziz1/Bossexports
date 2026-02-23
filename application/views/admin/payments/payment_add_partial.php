<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel-body">
    <h4 class="no-margin"><?php echo _l('payment'); ?></h4>
    <hr class="hr-panel-heading" />
    <form action="<?php echo admin_url('payments/payment/-1'); ?>" id="add-payment-form" method="post" accept-charset="utf-8">
        <div class="f_client_id">
            <div class="form-group select-placeholder">
                <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                <select id="clientid_add" name="client_id" data-live-search="true" data-width="100%" class="ajax-search customer-removed" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                </select>
            </div>
        </div>
        <?php echo render_input('amount', 'payment_edit_amount_received', '', 'number'); ?>
        <?php echo render_date_input('date', 'payment_edit_date', _d(date('Y-m-d'))); ?>
        <?php echo render_select('paymentmode', $payment_modes, array('id', 'name'), 'payment_mode'); ?>
        <div class="row">
            <div class="col-md-12">
                <label for="pur_order"><?php echo _l('Invoices'); ?></label>
                <select id="pur_order_add" name="pur_order[]" class="selectpicker" multiple="1" data-none-selected-text="<?php echo _l('No Data') ?>" data-width="100%" data-live-search="true" data-actions-box="true">
                </select>
            </div>
        </div>
        <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('payment_method_info'); ?>"></i>
        <?php echo render_input('paymentmethod', 'payment_method', ''); ?>
        <?php echo render_input('transactionid', 'payment_transaction_id', date('YmdHis')); ?>
        <?php echo render_textarea('note', 'note', '', array('rows' => 7)); ?>
        <div class="text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
        </div>
    </form>
</div>
