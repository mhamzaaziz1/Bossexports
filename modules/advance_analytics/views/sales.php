<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                   <div class="col-md-6">
                      <h4 class="no-margin"><?php echo _l('analytics_sales'); ?> - Full Scale Report</h4>
                   </div>
                   <div class="col-md-6 text-right">
                       <!-- Date Filter Form -->
                       <form method="get" action="" class="form-inline display-inline-block">
                           <select name="stats_period" class="selectpicker" data-width="150px" data-style="btn-default" onchange="this.form.submit()">
                               <option value="all_time" <?php if($selected_period == 'all_time'){echo 'selected';} ?>>All Time</option>
                               <option value="this_month" <?php if($selected_period == 'this_month'){echo 'selected';} ?>>This Month</option>
                               <option value="last_month" <?php if($selected_period == 'last_month'){echo 'selected';} ?>>Last Month</option>
                               <option value="this_year" <?php if($selected_period == 'this_year'){echo 'selected';} ?>>This Year</option>
                               <option value="last_year" <?php if($selected_period == 'last_year'){echo 'selected';} ?>>Last Year</option>
                               <option value="last_6_months" <?php if($selected_period == 'last_6_months'){echo 'selected';} ?>>Last 6 Months</option>
                           </select>
                       </form>
                   </div>
                </div>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <!-- Sales Targets Section -->
        <?php if(isset($sales_targets) && count($sales_targets) > 0){ ?>
        <div class="row mtop20">
            <div class="col-md-12">
                <h4 class="text-uppercase bold text-muted mbot20">
                    <i class="fa fa-bullseye" aria-hidden="true"></i> <?php echo _l('sales_targets'); ?>
                </h4>
            </div>
            <?php foreach($sales_targets as $target){ 
                $percent = $target['achievement']['percent'];
                $progress_bar_percent = $target['achievement']['progress_bar_percent'] * 100;
                $color = 'progress-bar-success';
                if($progress_bar_percent < 50) $color = 'progress-bar-danger';
                elseif($progress_bar_percent < 80) $color = 'progress-bar-warning';
            ?>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-medium-xs">
                            <span class="bold"><?php echo $target['subject']; ?></span>
                            <span class="pull-right text-muted"><?php echo _d($target['end_date']); ?></span>
                        </h4>
                        <div class="clearfix mtop15"></div>
                        <div class="progress no-margin progress-bar-mini">
                            <div class="progress-bar <?php echo $color; ?> no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $progress_bar_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress_bar_percent; ?>%;" data-percent="<?php echo $progress_bar_percent; ?>">
                            </div>
                        </div>
                        <div class="row mtop10">
                            <div class="col-md-6 text-muted">
                                <small>Start: <?php echo _d($target['start_date']); ?></small>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="bold"><?php echo round($percent, 2); ?>%</span> / 100%
                                <br />
                                <small class="text-muted"><?php echo app_format_money($target['achievement']['total'], get_base_currency()); ?> / <?php echo app_format_money($target['achievement']['achievement'], get_base_currency()); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
             <div class="alert alert-info mtop20">
                No active Sales Targets found. You can set them up in <a href="<?php echo admin_url('goals'); ?>">Goals</a>.
            </div>
        <?php } ?>

        <!-- Top Row: Distributions -->
        <div class="row mtop20">
             <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-info"><i class="fa fa-file-text"></i> <?php echo _l('invoices'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="invoices_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-success"><i class="fa fa-file-powerpoint-o"></i> <?php echo _l('proposals'); ?></h4>
                        <hr class="hr-panel-heading" />
                         <div class="relative" style="max-height:300px">
                            <canvas id="proposals_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-warning"><i class="fa fa-calculator"></i> <?php echo _l('estimates'); ?></h4>
                        <hr class="hr-panel-heading" />
                         <div class="relative" style="max-height:300px">
                            <canvas id="estimates_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: Revenue & Funnels -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('total_sales'); ?> Trend (This Year vs Last Year)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="revenue_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Proposal Conversion Funnel</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="proposal_funnel_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Estimate Conversion Funnel</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="estimate_funnel_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Invoice Payment Funnel</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="invoice_funnel_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pipeline Trends Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('proposals'); ?> Trend (This Year vs Last Year)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="proposal_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('estimates'); ?> Trend (This Year vs Last Year)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="estimate_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Value Stats Row -->
        <div class="row">
             <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-info"><?php echo _l('invoices'); ?> (Amount)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="invoice_value_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-success"><?php echo _l('proposals'); ?> (Amount)</h4>
                        <hr class="hr-panel-heading" />
                         <div class="relative" style="max-height:300px">
                            <canvas id="proposal_value_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-warning"><?php echo _l('estimates'); ?> (Amount)</h4>
                        <hr class="hr-panel-heading" />
                         <div class="relative" style="max-height:300px">
                            <canvas id="estimate_value_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deep Dive Trends Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Activity Volume Trend (Count)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="activity_volume_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Average Deal Size Trend (Value)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="avg_deal_size_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level 12 Analytics Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-primary"><i class="fa fa-line-chart"></i> Win Rate Trend (%)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="win_rate_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-primary"><i class="fa fa-clock-o"></i> Sales Cycle Length (Days to Close)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="sales_cycle_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Broadened Analytics Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-info"><i class="fa fa-money"></i> Collection Efficiency (Payments vs Invoiced)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="collection_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-success"><i class="fa fa-balance-scale"></i> Cost vs Revenue vs Profit</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="profit_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Strategic Analytics Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-danger"><i class="fa fa-pie-chart"></i> Revenue Concentration Risk (Top 5 Clients)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="concentration_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-info"><i class="fa fa-refresh"></i> Customer Loyalty (New vs Recurring Revenue)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="loyalty_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Row: Items & Staff -->
         <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Top Selling Items (Revenue)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="top_items_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Top Sales Staff Leaderboard</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="top_staff_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics (Migrated from Old Version) -->
        <?php $this->load->view('advance_analytics/sales_detailed'); ?>

    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        // Chart Defaults
        Chart.defaults.global.defaultFontFamily = 'Roboto';

        // 1. Invoices
        new Chart($('#invoices_chart'), {
            type: 'doughnut',
            data: <?php echo $invoice_stats; ?>,
            options: { maintainAspectRatio: false, legend: { position: 'right' } }
        });

        // 2. Proposals
        new Chart($('#proposals_chart'), {
            type: 'doughnut',
            data: <?php echo $proposal_stats; ?>,
             options: { maintainAspectRatio: false, legend: { position: 'right' } }
        });

        // 3. Estimates
        new Chart($('#estimates_chart'), {
            type: 'doughnut',
            data: <?php echo $estimate_stats; ?>,
             options: { maintainAspectRatio: false, legend: { position: 'right' } }
        });

        // 4. Revenue Trend (Line)
         new Chart($('#revenue_trend_chart'), {
            type: 'line',
            data: <?php echo $revenue_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 5. Proposal Funnel (Bar as Funnel approximation)
         new Chart($('#proposal_funnel_chart'), {
            type: 'horizontalBar',
            data: <?php echo $proposal_funnel; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { xAxes: [{ ticks: { beginAtZero: true } }] },
                 legend: { display: false }
            }
        });

        // 5b. Estimate Funnel
         new Chart($('#estimate_funnel_chart'), {
            type: 'horizontalBar',
            data: <?php echo $estimate_funnel; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { xAxes: [{ ticks: { beginAtZero: true } }] },
                 legend: { display: false }
            }
        });

        // 5c. Invoice Funnel
         new Chart($('#invoice_funnel_chart'), {
            type: 'horizontalBar',
            data: <?php echo $invoice_funnel; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { xAxes: [{ ticks: { beginAtZero: true } }] },
                 legend: { display: false }
            }
        });

        // 5d. Invoice Value
         new Chart($('#invoice_value_chart'), {
            type: 'bar', // Using Bar for values to better visualize amounts
            data: <?php echo $invoice_value_stats; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } } }] },
                 legend: { display: false },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 5e. Proposal Value
         new Chart($('#proposal_value_chart'), {
            type: 'bar',
            data: <?php echo $proposal_value_stats; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } } }] },
                 legend: { display: false },
                  tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 5f. Estimate Value
         new Chart($('#estimate_value_chart'), {
            type: 'bar',
            data: <?php echo $estimate_value_stats; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } } }] },
                 legend: { display: false },
                  tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 5g. Proposal Trend
         new Chart($('#proposal_trend_chart'), {
            type: 'line',
            data: <?php echo $proposal_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

         // 5h. Estimate Trend
         new Chart($('#estimate_trend_chart'), {
            type: 'line',
            data: <?php echo $estimate_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 5i. Activity Volume Trend
         new Chart($('#activity_volume_trend_chart'), {
            type: 'line',
            data: <?php echo $activity_volume_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, stepSize: 1 } 
                     }]
                 }
            }
        });

        // 5j. Average Deal Size Trend
         new Chart($('#avg_deal_size_trend_chart'), {
            type: 'line',
            data: <?php echo $avg_deal_size_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 6. Top Items (Horizontal Bar)
         new Chart($('#top_items_chart'), {
            type: 'horizontalBar',
            data: <?php echo $top_items; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { xAxes: [{ ticks: { beginAtZero: true,  callback: function(value) { return value.toLocaleString(); } } }] },
                 legend: { display: false },
                  tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return  Number(tooltipItem.xLabel).toLocaleString();
                         }
                     }
                 }
            }
        });

        // 7. Top Staff (Vertical Bar)
        new Chart($('#top_staff_chart'), {
            type: 'bar',
            data: <?php echo $top_staff; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: { yAxes: [{ ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); }  } }] },
                 legend: { display: false }
            }
        });
        
        // 5k. Win Rate Trend
         new Chart($('#win_rate_chart'), {
            type: 'line',
            data: <?php echo $win_rate_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, max: 100, callback: function(value) { return value + '%'; } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel + '%';
                         }
                     }
                 }
            }
        });

        // 5l. Sales Cycle Trend
         new Chart($('#sales_cycle_chart'), {
            type: 'line',
            data: <?php echo $sales_cycle_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, stepSize: 1 }
                     }]
                 }
            }
        });
        
        // 5m. Collection Efficiency
         new Chart($('#collection_chart'), {
            type: 'line',
            data: <?php echo $collection_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value + '%'; } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel + '%';
                         }
                     }
                 }
            }
        });
        
        // 5n. Profit Trend (Mixed Chart)
         new Chart($('#profit_chart'), {
            type: 'bar', // Base type
            data: <?php echo $profit_trend; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     yAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return data.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toLocaleString();
                         }
                     }
                 }
            }
        });
    });
</script>
