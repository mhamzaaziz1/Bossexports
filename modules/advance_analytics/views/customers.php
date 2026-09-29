<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
          <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin"><?php echo _l('analytics_customers'); ?></h4>
                <hr class="hr-panel-heading" />
            </div>

        <!-- Customer Retention & Growth KPIs -->
        <div class="row mtop20">
            <?php $kpis = $retention_kpis; ?>
            <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-info">Total Customers</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo $kpis['total_customers']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-success">New This Month (<?php echo date('M'); ?>)</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold">+<?php echo $kpis['new_this_month']; ?></h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-warning">Retention Rate</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo round($kpis['retention_rate'], 1); ?>%</h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-danger">Churn Rate</h4>
                        <div class="mtop15"></div>
                        <h3 class="no-margin bold"><?php echo round($kpis['churn_rate'], 1); ?>%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Growth & Geography Charts -->
        <div class="row">
             <div class="col-md-8">
                <div class="panel_s">
                     <div class="panel-body">
                        <h4 class="no-margin">Customer Growth (<?php echo date('Y'); ?>)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="customer_growth_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="panel_s">
                     <div class="panel-body">
                        <h4 class="no-margin">Top 10 Countries</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:400px">
                            <canvas id="geography_chart" class="animated fadeIn"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
         // 1. Customer Growth
         new Chart($('#customer_growth_chart'), {
            type: 'line',
            data: <?php echo $new_vs_returning; ?>,
            options: { maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
        });

         // 2. Geography
         new Chart($('#geography_chart'), {
            type: 'horizontalBar',
            data: <?php echo $geography; ?>,
            options: { maintainAspectRatio: false, legend: { display: false } }
        });

        // Leads Source
        new Chart($('#leads_source_chart'), {
            type: 'doughnut',
            data: <?php echo $lead_source_stats; ?>,
            options: { maintainAspectRatio: false }
        });

        // Customer Groups
        new Chart($('#customer_group_chart'), {
            type: 'doughnut',
            data: <?php echo $customer_group_stats; ?>,
            options: { maintainAspectRatio: false }
        });
    });
</script>
