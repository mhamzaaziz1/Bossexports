<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-6">
        <h4 class="customer-profile-group-heading"><?php echo _l('Performance Matrix'); ?> (Vendor)</h4>
    </div>
    <div class="col-md-6 text-right">
        <select class="selectpicker" data-width="200px" onchange="window.location.href = this.value">
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=all_time'); ?>" <?php echo $period == 'all_time' ? 'selected' : ''; ?>>All Time</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=this_month'); ?>" <?php echo $period == 'this_month' ? 'selected' : ''; ?>>This Month</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=last_month'); ?>" <?php echo $period == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=last_3_months'); ?>" <?php echo $period == 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=last_6_months'); ?>" <?php echo $period == 'last_6_months' ? 'selected' : ''; ?>>Last 6 Months</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=last_12_months'); ?>" <?php echo $period == 'last_12_months' ? 'selected' : ''; ?>>Last 12 Months</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=this_year'); ?>" <?php echo $period == 'this_year' ? 'selected' : ''; ?>>This Year</option>
            <option value="<?php echo admin_url('purchase/vendor/' . $client->userid . '?group=performance_matrix&period=last_year'); ?>" <?php echo $period == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
        </select>
    </div>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />

<!-- Row 1: Scores -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-trophy"></i> Vendor Performance Scores</h4>
                <hr />
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-info"><?php echo round($performance['scores']['health'], 1); ?>/100</h3>
                        <span class="text-muted">Vendor Health Score</span>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-success"><?php echo round($performance['scores']['performance'], 1); ?>/100</h3>
                        <span class="text-muted">Purchasing Performance</span>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-warning"><?php echo round($performance['scores']['reliability'], 1); ?>/100</h3>
                        <span class="text-muted">Reliability Score</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Spend & Pipeline -->
<div class="row">
    <!-- Spend Performance -->
     <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-money"></i> Spend Performance Metrics</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Gross Spend (Approved POs)</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['spend']['gross'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Returns / Credits</strong></td>
                            <td class="text-right text-danger">-<?php echo app_format_money($performance['spend']['returns'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Net Spend</strong></td>
                            <td class="text-right bold"><?php echo app_format_money($performance['spend']['net'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Spend Growth % (YoY)</strong></td>
                            <td class="text-right <?php echo $performance['spend']['growth'] >= 0 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo number_format($performance['spend']['growth'], 2); ?>%
                                <!-- Growth in spend is usually bad (red) unless planned? Let's stick to standard Red for positive growth (cost increase)?? No, context dependent. I'll make > 0 red for COST. -->
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Payment Rate %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['spend']['payment_rate'], 2); ?>%</td>
                        </tr>
                         <tr>
                            <td><strong>Outstanding Balance</strong></td>
                            <td class="text-right text-warning"><?php echo app_format_money($performance['spend']['balance'], ''); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Procurement Pipeline -->
    <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-handshake-o"></i> Procurement Pipeline & Contracts</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Total Quotations (Estimates)</strong></td>
                            <td class="text-right"><span class="badge"><?php echo $performance['pipeline']['estimates_count']; ?></span></td>
                        </tr>
                          <tr>
                            <td><strong>Quotations Value</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['pipeline']['estimates_val'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Converted to Orders</strong></td>
                            <td class="text-right"><?php echo $performance['pipeline']['converted_orders']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Conversion Rate</strong></td>
                            <td class="text-right"><?php echo number_format($performance['pipeline']['conversion_rate'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Active Contracts</strong></td>
                            <td class="text-right"><span class="badge badge-info"><?php echo $performance['pipeline']['contracts']; ?></span></td>
                        </tr>
                         <tr>
                            <td><strong>Related Expenses</strong></td>
                            <td class="text-right text-warning"><?php echo app_format_money($performance['expenses'], ''); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Top Items -->
<div class="row">
      <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-shopping-basket"></i> Top Purchased Items</h4>
                <hr />
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th class="text-center">Quantity Purchased</th>
                            <th class="text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($performance['top_items'])): ?>
                            <?php foreach($performance['top_items'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['description'] ? $item['description'] : $item['item_code']; ?></td>
                                    <td class="text-center"><?php echo $item['total_qty']; ?></td>
                                    <td class="text-right"><?php echo app_format_money($item['total_val'], ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">No item data found for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
