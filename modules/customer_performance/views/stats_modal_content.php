<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Performance Stats: <?php echo isset($client->company) ? $client->company : ''; ?></h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-3 col-xs-6 text-center border-right">
            <h3 class="bold text-success"><?php echo app_format_money($stats['total_invoiced'], $client->default_currency); ?></h3>
            <span class="text-success">Total Invoiced</span>
        </div>
        <div class="col-md-3 col-xs-6 text-center border-right">
            <h3 class="bold text-info"><?php echo app_format_money($stats['total_paid'], $client->default_currency); ?></h3>
            <span class="text-info">Total Paid</span>
        </div>
        <div class="col-md-3 col-xs-6 text-center border-right">
            <h3 class="bold text-danger"><?php echo app_format_money($stats['outstanding_balance'], $client->default_currency); ?></h3>
            <span class="text-danger">Outstanding Balance</span>
        </div>
        <div class="col-md-3 col-xs-6 text-center">
            <h3 class="bold text-warning"><?php echo $stats['open_invoices']; ?> / <?php echo $stats['open_estimates']; ?></h3>
            <span class="text-warning">Open Inv / Est</span>
        </div>
    </div>
    <hr />
    <h4>Last 05 Transactions (Invoices)</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0) { ?>
                    <?php foreach($transactions as $t) { ?>
                        <tr>
                            <td><a href="<?php echo admin_url('invoices/list_invoices/' . $t['id']); ?>" target="_blank"><?php echo format_invoice_number($t['id']); ?></a></td>
                            <td><?php echo _d($t['date']); ?></td>
                            <td><?php echo app_format_money($t['total'], $client->default_currency); ?></td>
                            <td><?php echo format_invoice_status($t['status']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="4" class="text-center">No recent transactions found</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
