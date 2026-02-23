<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <?php echo form_open(admin_url('payments/update_payment_ajax/'.$payment->paymentid), array('id'=>'payment-edit-form')); ?>
        <h4 class="no-margin"><?php echo _l('payment_edit_for_invoice'); ?> <a href="<?php echo admin_url('invoices/list_invoices/'.$payment->invoiceid); ?>"><?php echo format_invoice_number($payment->invoice->id); ?></a></h4>
        <hr class="hr-panel-heading" />
        
        <?php echo render_input('amount','payment_edit_amount_received',$payment->amount,'number'); ?>
        <?php echo render_date_input('date','payment_edit_date',_d($payment->date)); ?>
        <?php echo render_select('paymentmode',$payment_modes,array('id','name'),'payment_mode',$payment->paymentmode); ?>
        
        <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('payment_method_info'); ?>"></i>
        <?php echo render_input('paymentmethod','payment_method',$payment->paymentmethod); ?>
        <?php echo render_input('transactionid','payment_transaction_id',$payment->transactionid); ?>
        <?php echo render_textarea('note','note',$payment->note,array('rows'=>7)); ?>
        
        <div class="btn-bottom-toolbar text-right">
            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script>
    init_selectpicker();
    init_datepicker();
</script>
