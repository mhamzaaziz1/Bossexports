<div class="panel_s">
    <div class="panel-body">
        <h4 class="pull-left "><?php echo _l('payment_view_heading'); ?></h4>
        <div class="pull-right">
            <div class="btn-group">
                <a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid.'?output_type=I'); ?>" target="_blank" class="btn btn-default" data-toggle="tooltip" title="<?php echo _l('view_pdf_in_new_window'); ?>">
                    <i class="fa fa-file-pdf-o"></i>
                </a>
                <a href="#" class="btn btn-default" onclick="send_payment_to_email(<?php echo $payment->paymentid; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('send_to_email'); ?>">
                    <i class="fa fa-envelope"></i>
                </a>
                <a href="#" class="btn btn-default" onclick="edit_payment_modal(<?php echo $payment->paymentid; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('edit'); ?>">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
        <hr class="hr-panel-heading" />
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <address>
                    <?php echo format_organization_info(); ?>
                </address>
            </div>
            <div class="col-sm-6 text-right">
                <address>
                    <span class="bold">
                        <?php if($payment->invoice != "" && $payment->invoice != 0 && isset($payment->invoice->clientid)){?>
                        <?php echo format_customer_info($payment->invoice, 'payment', 'billing', true); ?>
                        <?php } elseif (isset($payment->client) && $payment->client) {
                            echo "<h3>" . _l('customer_details') . "</h3>";
                            echo "Name: " . $payment->client->company;
                        }?>
                    </span>
                </address>
            </div>
        </div>
        <div class="col-md-12 text-center">
            <h3 class="text-uppercase"><?php echo _l('payment_receipt'); ?></h3>
        </div>
        <div class="col-md-12 mtop30">
            <div class="row">
                <div class="col-md-6">
                    <p><?php echo _l('payment_date'); ?> <span class="pull-right bold"><?php echo _d($payment->date); ?></span></p>
                    <hr />
                    <p><?php echo _l('payment_view_mode'); ?>
                    <span class="pull-right bold">
                        <?php echo $payment->name; ?>
                        <?php if(!empty($payment->paymentmethod)){
                            echo ' - ' . $payment->paymentmethod;
                        }
                        ?>
                    </span></p>
                    <?php if(!empty($payment->transactionid)) { ?>
                        <hr />
                        <p><?php echo _l('payment_transaction_id'); ?>: <span class="pull-right bold"><?php echo $payment->transactionid; ?></span></p>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="payment-preview-wrapper">
                        <?php echo _l('payment_total_amount'); ?><br />
                        <?php echo app_format_money($payment->amount, $payment->invoice->currency_name); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mtop30">
            <h4><?php echo _l('payment_for_string'); ?></h4>
            <div class="table-responsive">
                <table class="table table-borderd table-hover">
                    <thead>
                        <tr>
                            <th><?php echo _l('payment_table_invoice_number'); ?></th>
                            <th><?php echo _l('payment_table_invoice_date'); ?></th>
                            <th><?php echo _l('payment_table_invoice_amount_total'); ?></th>
                            <th><?php echo _l('payment_table_payment_amount_total'); ?></th>
                            <?php if($payment->invoice->status != Invoices_model::STATUS_PAID
                                && $payment->invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
                                    <th><span class="text-danger"><?php echo _l('invoice_amount_due'); ?></span></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo format_invoice_number($payment->invoice->id); ?></td>
                                <td><?php echo _d($payment->invoice->date); ?></td>
                                <td><?php echo app_format_money($payment->invoice->total, $payment->invoice->currency_name); ?></td>
                                <td><?php echo app_format_money($payment->amount, $payment->invoice->currency_name); ?></td>
                                <?php if($payment->invoice->status != Invoices_model::STATUS_PAID
                                    && $payment->invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
                                        <td class="text-danger">
                                            <?php echo app_format_money(get_invoice_total_left_to_pay($payment->invoice->id, $payment->invoice->total), $payment->invoice->currency_name); ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
