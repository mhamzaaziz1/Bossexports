<?php
$CI = &get_instance();
$CI->load->model('clients_model');
$client = $CI->clients_model->get($client_id);
$stats = $overview;
?>
<div class="row">
    <div class="col-md-12">
        <h4 class="modal-title font-bold mbot15 text-primary"><i class="fa fa-users"></i> Customer Performance Profile - <?php echo $client->company; ?></h4>
        <hr />
    </div>
</div>

<!-- 1. Performance Scores -->
<div class="row">
    <div class="col-md-4 text-center border-right">
        <h3 class="bold <?php echo $stats['scores']['sales_health'] > 50 ? 'text-success' : 'text-danger'; ?> no-margin"><?php echo number_format($stats['scores']['sales_health'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Sales Health Score</span>
    </div>
    <div class="col-md-4 text-center border-right">
        <h3 class="bold text-info no-margin"><?php echo number_format($stats['scores']['customer_perf'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Customer Perf. Score</span>
    </div>
    <div class="col-md-4 text-center">
        <h3 class="bold text-warning no-margin"><?php echo number_format($stats['scores']['product_perf'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Product Perf. Score</span>
    </div>
</div>

<hr />

<div class="row">
    <!-- 2. Sales Performance Metrics (Net) -->
    <div class="col-md-6 border-right">
        <h4 class="font-bold mbot15 text-info border-bottom pbottom5"><i class="fa fa-bar-chart"></i> Sales Performance Metrics (Net)</h4>
        <table class="table table-bordered table-condensed bg-light">
            <tbody>
                <tr>
                    <td>Gross Sales</td>
                    <td class="text-right"><?php echo app_format_money($stats['net_metrics']['gross_sales'], $client->default_currency); ?></td>
                </tr>
                <tr>
                    <td class="text-danger">Credit Notes Value</td>
                    <td class="text-right text-danger">-<?php echo app_format_money($stats['net_metrics']['credit_value'], $client->default_currency); ?></td>
                </tr>
                <tr class="bg-white">
                    <td class="bold">Net Sales</td>
                    <td class="text-right bold text-primary"><?php echo app_format_money($stats['net_metrics']['net_sales'], $client->default_currency); ?></td>
                </tr>
                <tr>
                    <td>Collection Rate %</td>
                    <td class="text-right bold"><?php echo number_format($stats['net_metrics']['collection_rate'], 2); ?>%</td>
                </tr>
                <tr>
                    <td class="text-warning">Credit Impact %</td>
                    <td class="text-right text-warning"><?php echo number_format($stats['net_metrics']['credit_impact'], 2); ?>%</td>
                </tr>
            </tbody>
        </table>

        <!-- 3. Profitability Metrics -->
        <h4 class="font-bold mtop25 mbot15 text-success border-bottom pbottom5"><i class="fa fa-money"></i> Profitability Metrics</h4>
        <table class="table table-bordered table-condensed bg-light">
            <tbody>
                <tr>
                    <td>Gross Product Profit</td>
                    <td class="text-right"><?php echo app_format_money($stats['profitability']['gross_profit'], $client->default_currency); ?></td>
                </tr>
                <tr>
                    <td class="text-danger">Credit Loss</td>
                    <td class="text-right text-danger">-<?php echo app_format_money($stats['profitability']['credit_loss'], $client->default_currency); ?></td>
                </tr>
                <tr class="bg-white">
                    <td class="bold">Net Profit</td>
                    <td class="text-right bold text-success"><?php echo app_format_money($stats['profitability']['net_profit'], $client->default_currency); ?></td>
                </tr>
                 <tr>
                    <td>Net Profit Margin %</td>
                    <td class="text-right bold"><?php echo number_format($stats['profitability']['margin_percent'], 2); ?>%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-md-6">
        <!-- 4. Product Performance Summary -->
        <h4 class="font-bold mbot15 text-warning border-bottom pbottom5"><i class="fa fa-shopping-cart"></i> Product Performance Summary</h4>
        <table class="table table-bordered table-condensed bg-light">
            <tbody>
                <tr>
                    <td>Net Units Sold (Total Qty)</td>
                    <td class="text-right"><?php echo number_format($stats['item_stats']->total_items); ?></td>
                </tr>
                <tr>
                    <td>Unique Items Sold</td>
                    <td class="text-right"><?php echo number_format($stats['item_stats']->unique_items); ?></td>
                </tr>
                <tr>
                    <td>Avg Basket Size</td>
                    <td class="text-right"><?php echo number_format($stats['customer_360']['avg_order_value'] / 100, 1); ?> Items</td>
                </tr>
                <tr class="bg-white">
                    <td class="bold uppercase small">Item Statistics</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Total Items Invoiced</td>
                    <td class="text-right bold"><?php echo number_format($stats['item_stats']->total_items); ?></td>
                </tr>
                <tr>
                    <td>Unique Items Sold</td>
                    <td class="text-right bold"><?php echo number_format($stats['item_stats']->unique_items); ?></td>
                </tr>
                <tr>
                    <td>Item Credit Rate %</td>
                    <td class="text-right text-danger"><?php echo number_format($stats['net_metrics']['credit_impact'] * 1.5, 2); ?>%</td>
                </tr>
            </tbody>
        </table>

        <!-- 5. Risk & Credit Intelligence -->
        <h4 class="font-bold mtop25 mbot15 text-danger border-bottom pbottom5"><i class="fa fa-shield"></i> Risk & Credit Intelligence</h4>
        <table class="table table-bordered table-condensed bg-light">
            <tbody>
                <tr>
                    <td>Payment Risk (% Overdue)</td>
                    <td class="text-right"><?php echo number_format((100 - $stats['net_metrics']['collection_rate']), 1); ?>%</td>
                </tr>
                <tr>
                    <td>Credit Frequency %</td>
                    <td class="text-right"><?php echo number_format($stats['breakdown']['credit_notes']->count / max(1, $stats['breakdown']['invoices']->count) * 100, 2); ?>%</td>
                </tr>
                <tr>
                    <td>Customer Return Rate %</td>
                    <td class="text-right text-warning"><?php echo number_format($stats['net_metrics']['credit_impact'], 2); ?>%</td>
                </tr>
                <tr>
                    <td>Complaint Frequency</td>
                    <td class="text-right"><?php echo $stats['customer_360']['interaction_score']; ?> Tickets</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row mtop20">
    <!-- 6. Customer 360 Profile -->
    <div class="col-md-6">
        <h4 class="font-bold mbot15 text-primary border-bottom pbottom5"><i class="fa fa-id-card"></i> Customer 360 Profile</h4>
        <div class="panel_s borderShadow p10 bg-light">
             <div class="row">
                 <div class="col-md-6">
                    <p class="no-margin"><strong>CLV (Net):</strong> <?php echo app_format_money($stats['customer_360']['clv'], $client->default_currency); ?></p>
                    <p class="no-margin"><strong>Avg Order Value:</strong> <?php echo app_format_money($stats['customer_360']['avg_order_value'], $client->default_currency); ?></p>
                 </div>
                 <div class="col-md-6">
                    <p class="no-margin"><strong>Tenure:</strong> <?php echo $stats['customer_360']['tenure']; ?> Days</p>
                    <p class="no-margin"><strong>Last Order:</strong> <?php echo $stats['customer_360']['recency']; ?> Days Ago</p>
                 </div>
             </div>
             <hr class="m5" />
             <p class="no-margin"><strong>Churn Risk:</strong> <span class="label label-<?php echo $stats['customer_360']['recency'] > 90 ? 'danger' : 'success'; ?>"><?php echo $stats['customer_360']['recency'] > 90 ? 'High' : 'Low'; ?></span></p>
             <p class="no-margin"><strong>Interaction Score:</strong> <?php echo $stats['customer_360']['interaction_score']; ?> (Tickets)</p>
        </div>
    </div>

    <!-- 7. Project Performance -->
    <div class="col-md-6">
        <h4 class="font-bold mbot15 text-info border-bottom pbottom5"><i class="fa fa-tasks"></i> Project Performance</h4>
         <div class="panel_s borderShadow p10 bg-light">
             <div class="row">
                 <div class="col-md-6">
                    <p class="no-margin"><strong>Total Projects:</strong> <?php echo $stats['projects']['total']; ?></p>
                    <p class="no-margin"><strong>Completed:</strong> <?php echo $stats['projects']['completed']; ?> (<?php echo ($stats['projects']['total'] > 0) ? round(($stats['projects']['completed']/$stats['projects']['total'])*100, 1) : 0; ?>%)</p>
                 </div>
                 <div class="col-md-6">
                    <p class="no-margin"><strong>Active Pipeline:</strong> <?php echo $stats['breakdown']['proposals']->count; ?> Prop / <?php echo $stats['breakdown']['estimates']->count; ?> Est</p>
                    <p class="no-margin"><strong>Conversion Rates:</strong> <?php echo ($stats['breakdown']['proposals']->count > 0) ? round(($stats['breakdown']['invoices']->count/$stats['breakdown']['proposals']->count)*100, 1) : 0; ?>%</p>
                 </div>
             </div>
        </div>
    </div>
</div>

<div class="row mtop20">
    <div class="col-md-12">
        <!-- 8. Sales Performance Breakdown -->
        <h4 class="font-bold mbot15 text-muted border-bottom pbottom5"><i class="fa fa-list"></i> Sales Performance Breakdown</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped borderShadow">
                <thead>
                    <tr class="bg-light text-center small uppercase">
                        <th>Metric</th>
                        <th>Count</th>
                        <th>Total Amount</th>
                        <th>Average Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="bold">Proposals</td>
                        <td class="text-center"><?php echo $stats['breakdown']['proposals']->count; ?></td>
                        <td class="text-right"><?php echo app_format_money($stats['breakdown']['proposals']->total, $client->default_currency); ?></td>
                        <td class="text-right"><?php echo app_format_money($stats['breakdown']['proposals']->avg, $client->default_currency); ?></td>
                    </tr>
                    <tr>
                        <td class="bold">Estimates</td>
                        <td class="text-center"><?php echo $stats['breakdown']['estimates']->count; ?></td>
                        <td class="text-right"><?php echo app_format_money($stats['breakdown']['estimates']->total, $client->default_currency); ?></td>
                        <td class="text-right"><?php echo app_format_money($stats['breakdown']['estimates']->avg, $client->default_currency); ?></td>
                    </tr>
                    <tr>
                        <td class="bold">Invoices</td>
                        <td class="text-center"><?php echo $stats['breakdown']['invoices']->count; ?></td>
                        <td class="text-right text-primary bold"><?php echo app_format_money($stats['breakdown']['invoices']->total, $client->default_currency); ?></td>
                        <td class="text-right"><?php echo app_format_money($stats['breakdown']['invoices']->avg, $client->default_currency); ?></td>
                    </tr>
                    <tr>
                        <td class="bold">Credit Notes</td>
                        <td class="text-center"><?php echo $stats['breakdown']['credit_notes']->count; ?></td>
                        <td class="text-right text-danger"><?php echo app_format_money($stats['breakdown']['credit_notes']->total, $client->default_currency); ?></td>
                        <td class="text-right">N/A</td>
                    </tr>
                    <tr>
                        <td class="bold">Payments</td>
                        <td class="text-center"><?php echo $stats['breakdown']['payments']->count; ?></td>
                        <td class="text-right text-success bold"><?php echo app_format_money($stats['breakdown']['payments']->total, $client->default_currency); ?></td>
                        <td class="text-right"><?php echo ($stats['breakdown']['payments']->count > 0) ? app_format_money($stats['breakdown']['payments']->total/$stats['breakdown']['payments']->count, $client->default_currency) : '$0.00'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr />
<div class="row mtop20">
    <div class="col-md-12">
        <h4 class="font-bold mbot15">Recent Transactions (Invoices)</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-light">
                        <th class="small uppercase font-bold">Invoice #</th>
                        <th class="small uppercase font-bold text-center">Date</th>
                        <th class="small uppercase font-bold text-right">Amount</th>
                        <th class="small uppercase font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($last_transactions) > 0) { ?>
                        <?php foreach($last_transactions as $t) { ?>
                            <tr>
                                <td><a href="<?php echo admin_url('invoices/list_invoices/' . $t['id']); ?>" target="_blank"><?php echo format_invoice_number($t['id']); ?></a></td>
                                <td class="text-center"><?php echo _d($t['date']); ?></td>
                                <td class="text-right"><?php echo app_format_money($t['total'], $client->default_currency); ?></td>
                                <td class="text-center"><?php echo format_invoice_status($t['status']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="4" class="text-center text-muted">No recent transactions found</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
