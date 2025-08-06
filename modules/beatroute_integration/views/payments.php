<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<div id="wrapper">
    <div class="content">
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('beatroute_payments'); ?></h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                            <a href="<?php echo admin_url('beatroute_integration/sync?type=payments'); ?>" class="btn btn-info">
                                <i class="fa fa-refresh"></i> <?php echo _l('sync_payments'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($payments)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_payments_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('invoice'); ?></th>
                                    <th><?php echo _l('payment_mode'); ?></th>
                                    <th><?php echo _l('payment_date'); ?></th>
                                    <th><?php echo _l('amount'); ?></th>
                                    <th><?php echo _l('transaction_id'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('last_sync'); ?></th>
                                    <th><?php echo _l('sync_status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment['id']; ?></td>
                                        <td>
                                            <?php 
                                            $invoice = $this->beatroute_model->get_invoice_by_beatroute_id($payment['beatroute_invoice_id']);
                                            echo $invoice ? $invoice->invoice_number : $payment['beatroute_invoice_id'];
                                            ?>
                                        </td>
                                        <td><?php echo $payment['payment_mode']; ?></td>
                                        <td><?php echo _d($payment['payment_date']); ?></td>
                                        <td>
                                            <?php 
                                            $currency = '';
                                            if ($invoice) {
                                                $currency = $invoice->currency;
                                            }
                                            echo app_format_money($payment['amount'], $currency); 
                                            ?>
                                        </td>
                                        <td><?php echo $payment['transaction_id'] ? $payment['transaction_id'] : '-'; ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($payment['status'] == 'completed') {
                                                $status_badge = 'success';
                                            } elseif ($payment['status'] == 'pending') {
                                                $status_badge = 'warning';
                                            } elseif ($payment['status'] == 'failed') {
                                                $status_badge = 'danger';
                                            } elseif ($payment['status'] == 'refunded') {
                                                $status_badge = 'info';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $payment['status']; ?></span>
                                        </td>
                                        <td><?php echo _dt($payment['last_sync']); ?></td>
                                        <td>
                                            <?php
                                            $sync_badge = 'default';
                                            if ($payment['sync_status'] == 'synced') {
                                                $sync_badge = 'success';
                                            } elseif ($payment['sync_status'] == 'pending') {
                                                $sync_badge = 'warning';
                                            } elseif ($payment['sync_status'] == 'failed') {
                                                $sync_badge = 'danger';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $sync_badge; ?>"><?php echo $payment['sync_status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a href="#" onclick="viewPaymentDetails(<?php echo $payment['id']; ?>); return false;">
                                                            <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                        </a>
                                                    </li>
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/push?type=payment&id=' . $payment['beatroute_id']); ?>">
                                                                <i class="fa fa-upload"></i> <?php echo _l('push_to_beatroute'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($payment['payment_id']) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('payments/payment/' . $payment['payment_id']); ?>" target="_blank">
                                                                <i class="fa fa-link"></i> <?php echo _l('view_in_perfex'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Live Beatroute Payments Section -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('live_beatroute_payments'); ?></h4>
                        <p class="text-muted"><?php echo _l('live_data_from_beatroute'); ?></p>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($live_payments)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_live_payments_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('invoice'); ?></th>
                                    <th><?php echo _l('payment_mode'); ?></th>
                                    <th><?php echo _l('payment_date'); ?></th>
                                    <th><?php echo _l('amount'); ?></th>
                                    <th><?php echo _l('transaction_id'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($live_payments as $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment['id']; ?></td>
                                        <td>
                                            <?php 
                                            $invoice = $this->beatroute_model->get_invoice_by_beatroute_id($payment['invoice_id']);
                                            echo $invoice ? $invoice->invoice_number : $payment['invoice_id'];
                                            ?>
                                        </td>
                                        <td><?php echo $payment['payment_mode']; ?></td>
                                        <td><?php echo _d($payment['payment_date']); ?></td>
                                        <td>
                                            <?php 
                                            $currency = '';
                                            if ($invoice) {
                                                $currency = $invoice->currency;
                                            }
                                            echo app_format_money($payment['amount'], $currency); 
                                            ?>
                                        </td>
                                        <td><?php echo isset($payment['transaction_id']) && $payment['transaction_id'] ? $payment['transaction_id'] : '-'; ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($payment['status'] == 'completed') {
                                                $status_badge = 'success';
                                            } elseif ($payment['status'] == 'pending') {
                                                $status_badge = 'warning';
                                            } elseif ($payment['status'] == 'failed') {
                                                $status_badge = 'danger';
                                            } elseif ($payment['status'] == 'refunded') {
                                                $status_badge = 'info';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $payment['status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/sync?type=payments'); ?>">
                                                                <i class="fa fa-refresh"></i> <?php echo _l('sync_to_local'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="payment_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('payment_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong><?php echo _l('id'); ?></strong></td>
                                    <td id="payment_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('beatroute_id'); ?></strong></td>
                                    <td id="payment_beatroute_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('invoice'); ?></strong></td>
                                    <td id="payment_invoice"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('payment_mode'); ?></strong></td>
                                    <td id="payment_mode"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('payment_date'); ?></strong></td>
                                    <td id="payment_date"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('amount'); ?></strong></td>
                                    <td id="payment_amount"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('transaction_id'); ?></strong></td>
                                    <td id="payment_transaction_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('status'); ?></strong></td>
                                    <td id="payment_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('last_sync'); ?></strong></td>
                                    <td id="payment_last_sync"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('sync_status'); ?></strong></td>
                                    <td id="payment_sync_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('created_at'); ?></strong></td>
                                    <td id="payment_created_at"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('updated_at'); ?></strong></td>
                                    <td id="payment_updated_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('modules/beatroute_integration/assets/js/beatroute.js'); ?>"></script>
<script>
    function viewPaymentDetails(id) {
        $.get(admin_url + 'beatroute_integration/get_payment_details/' + id, function(response) {
            var data = JSON.parse(response);
            var payment = data.payment;

            $('#payment_id').text(payment.id);
            $('#payment_beatroute_id').text(payment.beatroute_id);
            $('#payment_invoice').text(data.invoice_number);
            $('#payment_mode').text(payment.payment_mode);

            // Use formatDateTime function if moment is not available
            if (typeof moment !== 'undefined') {
                $('#payment_date').text(moment(payment.payment_date).format('YYYY-MM-DD'));
                $('#payment_last_sync').text(moment(payment.last_sync).format('YYYY-MM-DD HH:mm:ss'));
                $('#payment_created_at').text(moment(payment.created_at).format('YYYY-MM-DD HH:mm:ss'));
                $('#payment_updated_at').text(moment(payment.updated_at).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                $('#payment_date').text(formatDateTime(payment.payment_date).split(' ')[0]);
                $('#payment_last_sync').text(formatDateTime(payment.last_sync));
                $('#payment_created_at').text(formatDateTime(payment.created_at));
                $('#payment_updated_at').text(formatDateTime(payment.updated_at));
            }

            $('#payment_amount').text(format_money(payment.amount, data.currency));
            $('#payment_transaction_id').text(payment.transaction_id || '-');

            var status_badge = 'default';
            if (payment.status == 'completed') {
                status_badge = 'success';
            } else if (payment.status == 'pending') {
                status_badge = 'warning';
            } else if (payment.status == 'failed') {
                status_badge = 'danger';
            } else if (payment.status == 'refunded') {
                status_badge = 'info';
            }
            $('#payment_status').html('<span class="label label-' + status_badge + '">' + payment.status + '</span>');

            var sync_badge = 'default';
            if (payment.sync_status == 'synced') {
                sync_badge = 'success';
            } else if (payment.sync_status == 'pending') {
                sync_badge = 'warning';
            } else if (payment.sync_status == 'failed') {
                sync_badge = 'danger';
            }
            $('#payment_sync_status').html('<span class="label label-' + sync_badge + '">' + payment.sync_status + '</span>');

            $('#payment_details_modal').modal('show');
        });
    }
</script>
</div>
</div>
<?php init_footer(); ?>
