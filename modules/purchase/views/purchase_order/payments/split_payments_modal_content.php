<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table dt-table table-hover">
                <thead>
                    <tr>
                        <th><?php echo _l('payment_mode'); ?></th>
                        <th><?php echo _l('transaction_id'); ?></th>
                        <th><?php echo _l('amount'); ?></th>
                        <th><?php echo _l('date'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment){ ?>
                        <tr>
                            <td><?php echo get_payment_mode_by_id($payment['paymentmode']); ?></td>
                            <td><?php echo $payment['transactionid']; ?></td>
                            <td><?php echo app_format_money($payment['amount'], $payment['currency']); ?></td>
                            <td><?php echo _d($payment['date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
