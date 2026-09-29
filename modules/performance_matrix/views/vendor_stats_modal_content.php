<div class="row">
    <div class="col-md-6">
        <h4 class="no-margin bold text-primary font-medium-size"><i class="fa fa-bar-chart mright5"></i> <?php echo _l('Performance Matrix'); ?> (Vendor)</h4>
    </div>
    <div class="col-md-6 text-right">
        <select class="selectpicker" data-width="240px" id="vendor_period_select" onchange="load_vendor_stats('<?php echo $vendor_id; ?>', this.value)">
            <option value="all_time" <?php echo $period == 'all_time' ? 'selected' : ''; ?>>All Time</option>
            <option value="this_month" <?php echo $period == 'this_month' ? 'selected' : ''; ?>>This Month</option>
            <option value="last_month" <?php echo $period == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
            <option value="last_3_months" <?php echo $period == 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
            <option value="last_6_months" <?php echo $period == 'last_6_months' ? 'selected' : ''; ?>>Last 6 Months</option>
            <option value="last_12_months" <?php echo $period == 'last_12_months' ? 'selected' : ''; ?>>Last 12 Months</option>
            <option value="this_year" <?php echo $period == 'this_year' ? 'selected' : ''; ?>>This Year</option>
            <option value="last_year" <?php echo $period == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
        </select>
    </div>
</div>
<hr />
<!-- Scores Row -->
<div class="row">
    <div class="col-md-4 text-center border-right">
        <h3 class="bold text-info no-margin"><?php echo round($stats['scores']['health'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Vendor Health</span>
    </div>
    <div class="col-md-4 text-center border-right">
        <h3 class="bold text-success no-margin"><?php echo round($stats['scores']['performance'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Purchasing Perf.</span>
    </div>
    <div class="col-md-4 text-center">
        <h3 class="bold text-warning no-margin"><?php echo round($stats['scores']['reliability'], 1); ?>/100</h3>
        <span class="text-muted small uppercase">Reliability Score</span>
    </div>
</div>
<hr />
<div class="row">
    <!-- Spend Performance -->
    <div class="col-md-6">
        <div class="panel_s no-margin borderShadow p15">
            <h4 class="no-margin font-bold"><i class="fa fa-money"></i> Spend Performance Metrics</h4>
            <hr />
            <table class="table table-striped table-condensed">
                <tbody>
                    <tr>
                        <td><strong>Gross Spend (Approved POs)</strong></td>
                        <td class="text-right"><?php echo app_format_money($stats['spend']['gross'], ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Returns / Credit Notes</strong></td>
                        <td class="text-right text-danger">-<?php echo app_format_money($stats['spend']['returns'], ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Net Spend</strong></td>
                        <td class="text-right bold"><?php echo app_format_money($stats['spend']['net'], ''); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Spend Growth % (YoY)</strong></td>
                        <td class="text-right <?php echo $stats['spend']['growth'] >= 0 ? 'text-danger' : 'text-success'; ?>">
                            <?php echo number_format($stats['spend']['growth'], 2); ?>%
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Payment Rate %</strong></td>
                        <td class="text-right bold text-info"><?php echo number_format($stats['spend']['payment_rate'], 2); ?>%</td>
                    </tr>
                    <tr>
                        <td class="vertical-align-middle"><strong>Outstanding Balance</strong></td>
                        <td class="text-right text-warning bold"><?php echo app_format_money($stats['spend']['balance'], ''); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Items -->
    <div class="col-md-6">
        <div class="panel_s no-margin borderShadow p15">
            <h4 class="no-margin font-bold"><i class="fa fa-shopping-basket"></i> Top Purchased Items</h4>
            <hr />
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th class="small uppercase font-bold">Item Description</th>
                            <th class="small uppercase font-bold text-center">Qty</th>
                            <th class="small uppercase font-bold text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($stats['top_items'])): ?>
                            <?php foreach($stats['top_items'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['description'] ? $item['description'] : $item['item_code']; ?></td>
                                    <td class="text-center"><?php echo $item['total_qty']; ?></td>
                                    <td class="text-right"><?php echo app_format_money($item['total_val'], ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No item data found for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
