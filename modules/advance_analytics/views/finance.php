<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
         <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin"><?php echo _l('analytics_finance'); ?> & Payment Analysis</h4>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <!-- Financial Health KPIs -->
         <div class="row mtop20">
             <?php
                $health = $financial_health;
                $profit_color = 'text-success';
                if($health['net_profit'] < 0) $profit_color = 'text-danger';
             ?>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-info"><?php echo _l('report_sales_type_income'); ?></h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo app_format_money($health['total_income'], get_base_currency()); ?></h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin bold text-danger"><?php echo _l('expenses'); ?></h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo app_format_money($health['total_expenses'], get_base_currency()); ?></h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin bold <?php echo $profit_color; ?>">Net Profit</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold <?php echo $profit_color; ?>"><?php echo app_format_money($health['net_profit'], get_base_currency()); ?></h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin bold text-warning">Profit Margin</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo round($health['profit_margin'], 2); ?>%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Analysis Row -->
         <div class="row">
           <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Expenses by Category (<?php echo date('Y'); ?>)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="expenses_by_category_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                     <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('payment_modes_report'); ?> (<?php echo date('Y'); ?>)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="payment_modes_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('income_vs_expenses_report'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="income_vs_expenses" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-md-6">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Cash Flow Trend (Received Payments)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="payment_receipts_trend_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Details -->
        <div class="row">
            <div class="col-md-5">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('payment_modes_report'); ?> (<?php echo date('Y'); ?>)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="payment_modes_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Top Paying Customers</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="top_customers_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        Chart.defaults.global.defaultFontFamily = 'Roboto';

        // 0. Expenses by Category
         new Chart($('#expenses_by_category_chart'), {
            type: 'doughnut',
            data: <?php echo $expenses_by_category; ?>,
            options: { maintainAspectRatio: false, legend: { position: 'right' } }
        });

        // 1. Income vs Expenses
        new Chart($('#income_vs_expenses'), {
            type: 'bar',
            data: <?php echo $income_vs_expenses; ?>,
            options: { maintainAspectRatio: false, responsive: true }
        });

        // 2. Payment Trend (Cash Flow)
        new Chart($('#payment_receipts_trend_chart'), {
            type: 'line',
            data: <?php echo $payment_receipts_trend; ?>,
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

        // 3. Payment Modes
        new Chart($('#payment_modes_chart'), {
            type: 'doughnut',
            data: <?php echo $payment_mode_stats; ?>,
            options: { maintainAspectRatio: false, legend: { position: 'right' } }
        });

        // 4. Top Customers
        new Chart($('#top_customers_chart'), {
            type: 'horizontalBar',
            data: <?php echo $top_customers; ?>,
             options: { 
                 maintainAspectRatio: false,
                 scales: {
                     xAxes: [{
                         ticks: { beginAtZero: true, callback: function(value) { return value.toLocaleString(); } }
                     }]
                 },
                 tooltips: {
                     callbacks: {
                         label: function(tooltipItem, data) {
                             return Number(tooltipItem.xLabel).toLocaleString();
                         }
                     }
                 }
            }
        });
    });
</script>
