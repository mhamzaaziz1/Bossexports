<?php
$CI = &get_instance();
$CI->load->model('omni_sales/omni_sales_model');
$period = $CI->input->get('period') ? $CI->input->get('period') : 'all_time';
$performance = $CI->omni_sales_model->get_client_performance_stats(isset($client) ? $client->userid : $GLOBALS['client']->userid, $period);
?>
<div class="row">
    <div class="col-md-6">
        <h4 class="customer-profile-group-heading"><?php echo _l('Performance Matrix'); ?></h4>
    </div>
    <div class="col-md-6 text-right">
        <select class="selectpicker" data-width="200px" onchange="window.location.href = this.value">
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=all_time'); ?>" <?php echo $period == 'all_time' ? 'selected' : ''; ?>>All Time</option>
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=this_month'); ?>" <?php echo $period == 'this_month' ? 'selected' : ''; ?>>This Month</option>
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=last_month'); ?>" <?php echo $period == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
             <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=last_3_months'); ?>" <?php echo $period == 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=last_6_months'); ?>" <?php echo $period == 'last_6_months' ? 'selected' : ''; ?>>Last 6 Months</option>
             <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=last_12_months'); ?>" <?php echo $period == 'last_12_months' ? 'selected' : ''; ?>>Last 12 Months</option>
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=this_year'); ?>" <?php echo $period == 'this_year' ? 'selected' : ''; ?>>This Year</option>
            <option value="<?php echo admin_url('clients/client/' . (isset($client) ? $client->userid : $GLOBALS['client']->userid) . '?group=performance_matrix&period=last_year'); ?>" <?php echo $period == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
        </select>
    </div>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />

<!-- Row 1: Composite Scores -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-trophy"></i> Performance Scores</h4>
                <hr />
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-info"><?php echo round($performance['scores']['sales_health'], 1); ?>/100</h3>
                        <span class="text-muted">Sales Health Score</span>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-success"><?php echo round($performance['scores']['customer_perf'], 1); ?>/100</h3>
                        <span class="text-muted">Customer Performance Score</span>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="bold text-warning"><?php echo round($performance['scores']['product_perf'], 1); ?>/100</h3>
                        <span class="text-muted">Product Performance Score</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Sales & Profitability -->
<div class="row">
    <!-- Sales Performance Metrics -->
     <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-chart-line"></i> Sales Performance Metrics (Net)</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Gross Sales</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['revenue']['total_revenue'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Credit Notes Value</strong></td>
                            <td class="text-right text-danger">-<?php echo app_format_money($performance['revenue']['total_revenue'] - $performance['revenue']['net_sales'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Net Sales</strong></td>
                            <td class="text-right bold"><?php echo app_format_money($performance['revenue']['net_sales'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Sales Growth % (Net)</strong></td>
                            <td class="text-right <?php echo $performance['revenue']['sales_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo number_format($performance['revenue']['sales_growth'], 2); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Collection Rate %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['revenue']['collection_rate'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Overdue Rate %</strong></td>
                            <td class="text-right text-danger"><?php echo number_format($performance['revenue']['overdue_rate'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Credit Impact %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['revenue']['credit_impact'], 2); ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Profitability Metrics -->
    <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-coins"></i> Profitability Metrics</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Gross Product Profit</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['product_performance']['gross_profit'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Credit Loss</strong></td>
                            <td class="text-right text-danger">-<?php echo app_format_money($performance['product_performance']['credit_loss'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Net Profit</strong></td>
                            <td class="text-right bold text-success"><?php echo app_format_money($performance['product_performance']['net_profit'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Net Profit Margin %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['product_performance']['net_margin_percent'], 2); ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Product & Risk/Credit Intelligence -->
<div class="row">
    <!-- Product Performance Metrics -->
     <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-boxes"></i> Product Performance Summary</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Net Product Revenue</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['product_performance']['net_revenue'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Net Units Sold</strong></td>
                            <td class="text-right"><?php echo $performance['product_performance']['net_units']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Avg Basket Size</strong></td>
                            <td class="text-right"><?php echo number_format($performance['behavior']['basket_size'], 1); ?> Items</td>
                        </tr>
                         <tr>
                            <td><strong>Item Credit Rate %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['product_performance']['credit_rate_percent'], 2); ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

     <!-- Risk & Credit Intelligence -->
     <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-exclamation-triangle"></i> Risk & Credit Intelligence</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Payment Risk (% Overdue)</strong></td>
                            <td class="text-right <?php echo $performance['risk']['payment_risk'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo number_format($performance['risk']['payment_risk'], 1); ?>%
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Credit Frequency %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['credit_intelligence']['frequency_percent'], 2); ?>%</td>
                        </tr>
                         <tr>
                            <td><strong>Customer Return Rate %</strong></td>
                            <td class="text-right"><?php echo number_format($performance['behavior']['return_rate'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Complaint Frequency</strong></td>
                            <td class="text-right"><?php echo $performance['risk']['complaint_freq']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Customer 360 & Project -->
<div class="row">
    <!-- Customer 360 Profile -->
    <div class="col-md-6">
        <div class="panel_s">
             <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-user-circle"></i> Customer 360 Profile</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                         <tr>
                            <td><strong>CLV (Net)</strong></td>
                            <td class="text-right bold text-info"><?php echo app_format_money($performance['revenue']['clv_net'], ''); ?></td>
                        </tr>
                         <tr>
                            <td><strong>Avg Order Value (Net)</strong></td>
                            <td class="text-right"><?php echo app_format_money($performance['revenue']['aov_net'], ''); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Recency / Frequency</strong></td>
                            <td class="text-right">
                                <?php echo $performance['behavior']['recency']; ?> Days / 
                                <?php echo number_format($performance['behavior']['frequency'], 1); ?> mo
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tenure</strong></td>
                            <td class="text-right"><?php echo $performance['retention']['tenure_days']; ?> Days</td>
                        </tr>
                         <tr>
                            <td><strong>Churn Risk</strong></td>
                            <td class="text-right">
                                <span class="label <?php echo $performance['retention']['churn_risk'] == 'High' ? 'label-danger' : ($performance['retention']['churn_risk'] == 'Medium' ? 'label-warning' : 'label-success'); ?>">
                                    <?php echo $performance['retention']['churn_risk']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Interaction Score</strong></td>
                            <td class="text-right"><?php echo $performance['engagement']['interaction_score']; ?> (<?php echo $performance['engagement']['tickets']; ?> Tickets)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Project Performance -->
     <div class="col-md-6">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa-solid fa-chart-gantt"></i> Project Performance</h4>
                <hr />
                 <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td><strong>Total Projects</strong></td>
                            <td class="text-right"><?php echo $performance['projects']['total_projects']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Completed Projects</strong></td>
                            <td class="text-right">
                                <?php echo $performance['projects']['completed_projects']; ?>
                                <?php if($performance['projects']['total_projects'] > 0){ ?>
                                <small class="text-success">(<?php echo number_format(($performance['projects']['completed_projects'] / $performance['projects']['total_projects']) * 100, 1); ?>%)</small>
                                <?php } ?>
                            </td>
                        </tr>
                         <tr>
                            <td><strong>Active Pipeline</strong></td>
                            <td class="text-right">
                                <?php echo $performance['pipeline']['total_proposals']; ?> Props / 
                                <?php echo $performance['pipeline']['total_estimates']; ?> Ests
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Conversion Rates</strong></td>
                            <td class="text-right">
                                <?php echo number_format($performance['pipeline']['proposal_conv'], 0); ?>% Prop / 
                                <?php echo number_format($performance['pipeline']['estimate_conv'], 0); ?>% Est
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin font-bold"><i class="fa fa-balance-scale"></i> Sales Performance Breakdown</h4>
                <hr />
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="info">
                                <th>Metric</th>
                                <th class="text-center">Count</th>
                                <th class="text-right">Total Amount</th>
                                <th class="text-right">Average Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fa fa-file-powerpoint-o"></i> Proposals</td>
                                <td class="text-center"><?php echo $performance['sales_counts']['proposals']; ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['sales_amounts']['proposals'], ''); ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['averages']['proposal'], ''); ?></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-file-text-o"></i> Estimates</td>
                                <td class="text-center"><?php echo $performance['sales_counts']['estimates']; ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['sales_amounts']['estimates'], ''); ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['averages']['estimate'], ''); ?></td>
                            </tr>
                             <tr>
                                <td><i class="fa fa-file-text"></i> Invoices</td>
                                <td class="text-center"><?php echo $performance['sales_counts']['invoices']; ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['sales_amounts']['invoices'], ''); ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['averages']['invoice'], ''); ?></td>
                            </tr>
                             <tr>
                                <td><i class="fa fa-sticky-note-o"></i> Credit Notes</td>
                                <td class="text-center"><?php echo $performance['sales_counts']['credit_notes']; ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['sales_amounts']['credit_notes'], ''); ?></td>
                                <td class="text-right">N/A</td>
                            </tr>
                             <tr>
                                <td><i class="fa fa-money"></i> Payments</td>
                                <td class="text-center"><?php echo $performance['sales_counts']['payments']; ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['sales_amounts']['payments'], ''); ?></td>
                                <td class="text-right"><?php echo app_format_money($performance['averages']['payment'], ''); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mtop20">
                    <div class="col-md-6">
                         <h5 class="bold">Item Statistics</h5>
                         <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Items Invoiced
                                <span class="badge badge-primary badge-pill"><?php echo $performance['sales_counts']['items_invoiced']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Unique Items Sold
                                <span class="badge badge-primary badge-pill"><?php echo $performance['sales_counts']['unique_items']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
