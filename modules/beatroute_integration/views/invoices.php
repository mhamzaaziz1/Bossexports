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
                        <h4 class="no-margin"><?php echo _l('beatroute_invoices'); ?></h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                            <a href="<?php echo admin_url('beatroute_integration/sync?type=invoices'); ?>" class="btn btn-info">
                                <i class="fa fa-refresh"></i> <?php echo _l('sync_invoices'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($invoices)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_invoices_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('invoice_number'); ?></th>
                                    <th><?php echo _l('customer'); ?></th>
                                    <th><?php echo _l('date'); ?></th>
                                    <th><?php echo _l('due_date'); ?></th>
                                    <th><?php echo _l('total'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('last_sync'); ?></th>
                                    <th><?php echo _l('sync_status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice) { ?>
                                    <tr>
                                        <td><?php echo $invoice['id']; ?></td>
                                        <td><?php echo $invoice['invoice_number']; ?></td>
                                        <td>
                                            <?php 
                                            $customer = $this->beatroute_model->get_customer_by_beatroute_id($invoice['beatroute_customer_id']);
                                            echo $customer ? $customer->first_name . ' ' . $customer->last_name : $invoice['beatroute_customer_id'];
                                            ?>
                                        </td>
                                        <td><?php echo _d($invoice['date']); ?></td>
                                        <td><?php echo _d($invoice['due_date']); ?></td>
                                        <td><?php echo app_format_money($invoice['total'], $invoice['currency']); ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($invoice['status'] == 'paid') {
                                                $status_badge = 'success';
                                            } elseif ($invoice['status'] == 'unpaid') {
                                                $status_badge = 'danger';
                                            } elseif ($invoice['status'] == 'partially_paid') {
                                                $status_badge = 'warning';
                                            } elseif ($invoice['status'] == 'overdue') {
                                                $status_badge = 'warning';
                                            } elseif ($invoice['status'] == 'cancelled') {
                                                $status_badge = 'default';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $invoice['status']; ?></span>
                                        </td>
                                        <td><?php echo _dt($invoice['last_sync']); ?></td>
                                        <td>
                                            <?php
                                            $sync_badge = 'default';
                                            if ($invoice['sync_status'] == 'synced') {
                                                $sync_badge = 'success';
                                            } elseif ($invoice['sync_status'] == 'pending') {
                                                $sync_badge = 'warning';
                                            } elseif ($invoice['sync_status'] == 'failed') {
                                                $sync_badge = 'danger';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $sync_badge; ?>"><?php echo $invoice['sync_status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a href="#" onclick="viewInvoiceDetails(<?php echo $invoice['id']; ?>); return false;">
                                                            <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                        </a>
                                                    </li>
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/push?type=invoice&id=' . $invoice['beatroute_id']); ?>">
                                                                <i class="fa fa-upload"></i> <?php echo _l('push_to_beatroute'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($invoice['invoice_id']) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('invoices/list_invoices/' . $invoice['invoice_id']); ?>" target="_blank">
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

<!-- Live Beatroute Invoices Section -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="no-margin"><?php echo _l('live_beatroute_invoices'); ?></h4>
                        <p class="text-muted"><?php echo _l('live_data_from_beatroute'); ?></p>
                    </div>
                </div>
                <hr class="hr-panel-heading" />

                <?php if (empty($live_invoices)) { ?>
                    <div class="alert alert-info">
                        <?php echo _l('no_live_invoices_found'); ?>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <th><?php echo _l('invoice_number'); ?></th>
                                    <th><?php echo _l('customer'); ?></th>
                                    <th><?php echo _l('date'); ?></th>
                                    <th><?php echo _l('due_date'); ?></th>
                                    <th><?php echo _l('total'); ?></th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($live_invoices as $invoice) { ?>
                                    <tr>
                                        <td><?php echo $invoice['id']; ?></td>
                                        <td><?php echo $invoice['invoice_number']; ?></td>
                                        <td>
                                            <?php 
                                            $customer = $this->beatroute_model->get_customer_by_beatroute_id($invoice['customer_id']);
                                            echo $customer ? $customer->first_name . ' ' . $customer->last_name : $invoice['customer_id'];
                                            ?>
                                        </td>
                                        <td><?php echo _d($invoice['date']); ?></td>
                                        <td><?php echo _d($invoice['due_date']); ?></td>
                                        <td><?php echo app_format_money($invoice['total'], $invoice['currency']); ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'default';
                                            if ($invoice['status'] == 'paid') {
                                                $status_badge = 'success';
                                            } elseif ($invoice['status'] == 'unpaid') {
                                                $status_badge = 'danger';
                                            } elseif ($invoice['status'] == 'partially_paid') {
                                                $status_badge = 'warning';
                                            } elseif ($invoice['status'] == 'overdue') {
                                                $status_badge = 'warning';
                                            } elseif ($invoice['status'] == 'cancelled') {
                                                $status_badge = 'default';
                                            }
                                            ?>
                                            <span class="label label-<?php echo $status_badge; ?>"><?php echo $invoice['status']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog"></i> <?php echo _l('options'); ?> <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <?php if (has_permission('beatroute_integration', '', 'edit')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('beatroute_integration/sync?type=invoices'); ?>">
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

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoice_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('invoice_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4><?php echo _l('invoice_info'); ?></h4>
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong><?php echo _l('id'); ?></strong></td>
                                    <td id="invoice_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('beatroute_id'); ?></strong></td>
                                    <td id="invoice_beatroute_id"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('invoice_number'); ?></strong></td>
                                    <td id="invoice_number"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('customer'); ?></strong></td>
                                    <td id="invoice_customer"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('date'); ?></strong></td>
                                    <td id="invoice_date"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('due_date'); ?></strong></td>
                                    <td id="invoice_due_date"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('currency'); ?></strong></td>
                                    <td id="invoice_currency"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('subtotal'); ?></strong></td>
                                    <td id="invoice_subtotal"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('total'); ?></strong></td>
                                    <td id="invoice_total"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('status'); ?></strong></td>
                                    <td id="invoice_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('last_sync'); ?></strong></td>
                                    <td id="invoice_last_sync"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('sync_status'); ?></strong></td>
                                    <td id="invoice_sync_status"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('created_at'); ?></strong></td>
                                    <td id="invoice_created_at"></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo _l('updated_at'); ?></strong></td>
                                    <td id="invoice_updated_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4><?php echo _l('invoice_items'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="invoice_items_table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('item'); ?></th>
                                        <th><?php echo _l('qty'); ?></th>
                                        <th><?php echo _l('rate'); ?></th>
                                        <th><?php echo _l('amount'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
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
    function viewInvoiceDetails(id) {
        $.get(admin_url + 'beatroute_integration/get_invoice_details/' + id, function(response) {
            var data = JSON.parse(response);
            var invoice = data.invoice;
            var items = data.items;

            $('#invoice_id').text(invoice.id);
            $('#invoice_beatroute_id').text(invoice.beatroute_id);
            $('#invoice_number').text(invoice.invoice_number);
            $('#invoice_customer').text(data.customer_name);

            // Use formatDateTime function if moment is not available
            if (typeof moment !== 'undefined') {
                $('#invoice_date').text(moment(invoice.date).format('YYYY-MM-DD'));
                $('#invoice_due_date').text(moment(invoice.due_date).format('YYYY-MM-DD'));
                $('#invoice_last_sync').text(moment(invoice.last_sync).format('YYYY-MM-DD HH:mm:ss'));
                $('#invoice_created_at').text(moment(invoice.created_at).format('YYYY-MM-DD HH:mm:ss'));
                $('#invoice_updated_at').text(moment(invoice.updated_at).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                $('#invoice_date').text(formatDateTime(invoice.date).split(' ')[0]);
                $('#invoice_due_date').text(formatDateTime(invoice.due_date).split(' ')[0]);
                $('#invoice_last_sync').text(formatDateTime(invoice.last_sync));
                $('#invoice_created_at').text(formatDateTime(invoice.created_at));
                $('#invoice_updated_at').text(formatDateTime(invoice.updated_at));
            }

            $('#invoice_currency').text(invoice.currency);
            $('#invoice_subtotal').text(format_money(invoice.subtotal, invoice.currency));
            $('#invoice_total').text(format_money(invoice.total, invoice.currency));

            var status_badge = 'default';
            if (invoice.status == 'paid') {
                status_badge = 'success';
            } else if (invoice.status == 'unpaid') {
                status_badge = 'danger';
            } else if (invoice.status == 'partially_paid') {
                status_badge = 'warning';
            } else if (invoice.status == 'overdue') {
                status_badge = 'warning';
            } else if (invoice.status == 'cancelled') {
                status_badge = 'default';
            }
            $('#invoice_status').html('<span class="label label-' + status_badge + '">' + invoice.status + '</span>');

            var sync_badge = 'default';
            if (invoice.sync_status == 'synced') {
                sync_badge = 'success';
            } else if (invoice.sync_status == 'pending') {
                sync_badge = 'warning';
            } else if (invoice.sync_status == 'failed') {
                sync_badge = 'danger';
            }
            $('#invoice_sync_status').html('<span class="label label-' + sync_badge + '">' + invoice.sync_status + '</span>');

            // Clear and populate items table
            var $itemsTable = $('#invoice_items_table tbody');
            $itemsTable.empty();

            if (items && items.length > 0) {
                $.each(items, function(i, item) {
                    var amount = parseFloat(item.qty) * parseFloat(item.rate);
                    $itemsTable.append(
                        '<tr>' +
                        '<td>' + item.description + '</td>' +
                        '<td>' + item.qty + '</td>' +
                        '<td>' + format_money(item.rate, invoice.currency) + '</td>' +
                        '<td>' + format_money(amount, invoice.currency) + '</td>' +
                        '</tr>'
                    );
                });
            } else {
                $itemsTable.append('<tr><td colspan="4" class="text-center"><?php echo _l('no_items_found'); ?></td></tr>');
            }

            $('#invoice_details_modal').modal('show');
        });
    }
</script>
</div>
</div>
<?php init_footer(); ?>
