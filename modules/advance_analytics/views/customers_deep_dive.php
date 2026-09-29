<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-users" aria-hidden="true"></i> Vast Customer Analytics</h4>
                <p class="text-muted">Deep behavioral intelligence: RFM Segmentation, Churn Risk, & Lifecycle Metrics.</p>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <!-- Row 0: General KPIs -->
        <div class="row mbot15">
             <?php $kpis = $retention_kpis; ?>
             <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-info">Total Customers</h4>
                        <h3 class="no-margin bold"><?php echo $kpis['total_customers']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-success">New This Month</h4>
                        <h3 class="no-margin bold">+<?php echo $kpis['new_this_month']; ?></h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-warning">Retention Rate</h4>
                        <h3 class="no-margin bold"><?php echo round($kpis['retention_rate'], 1); ?>%</h3>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold text-danger">Churn Rate</h4>
                        <h3 class="no-margin bold"><?php echo round($kpis['churn_rate'], 1); ?>%</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Row 0.5: Growth & Geography -->
        <div class="row">
             <div class="col-md-8">
                <div class="panel_s">
                     <div class="panel-body">
                        <h4 class="no-margin">Customer Growth (<?php echo date('Y'); ?>)</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="customer_growth_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="panel_s">
                     <div class="panel-body">
                        <h4 class="no-margin">Top 10 Countries</h4>
                        <hr class="hr-panel-heading" />
                        <div class="relative" style="max-height:300px">
                            <canvas id="geography_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 1: RFM & Churn -->
        <div class="row">
            <!-- RFM Segmentation -->
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">RFM Segmentation (Recency, Frequency, Monetary)</h4>
                        <p class="text-muted small mbot15">Classifies customers based on their buying behavior.</p>
                        <div class="relative" style="max-height:400px">
                            <canvas id="rfm_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Churn Risk -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-danger">Churn Risk Prediction</h4>
                         <p class="text-muted small mbot15">Based on deviation from personal buying cycle.</p>
                        <div class="relative" style="max-height:400px">
                            <canvas id="churn_chart"></canvas>
                        </div>
                         <div class="mtop20 text-center">
                             <span class="label label-danger">High Risk: >3x Cycle Late</span>
                             <span class="label label-warning">Medium Risk: >1.5x Cycle Late</span>
                         </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Row 2: Cohort & CLV -->
        <div class="row">
            <!-- Cohort Analysis -->
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin"><i class="fa fa-th"></i> Cohort Analysis (Retention Heatmap)</h4>
                         <p class="text-muted small mbot15">Shows the % of customers who made a repeat purchase X months after acquisition.</p>
                         
                         <div class="table-responsive">
                             <table class="table table-bordered table-condensed text-center font-medium-xs">
                                 <thead>
                                     <tr style="background:#f3f4f6;">
                                         <th class="text-left">Cohort</th>
                                         <th>Size</th>
                                         <?php for($i=0; $i<12; $i++){ echo "<th>M{$i}</th>"; } ?>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php foreach($cohort_data as $row){ ?>
                                     <tr>
                                         <td class="text-left bold"><?php echo $row['month']; ?></td>
                                         <td class="text-muted"><?php echo $row['size']; ?></td>
                                         <?php 
                                            for($i=0; $i<12; $i++){
                                                $pct = isset($row['data'][$i]) ? $row['data'][$i] : '';
                                                $bg = '';
                                                if($pct !== '') {
                                                    // Heatmap coloring
                                                    if($pct >= 80) $bg = '#d1fae5'; // Green
                                                    elseif($pct >= 50) $bg = '#fae8ff'; // Purple
                                                    elseif($pct >= 20) $bg = '#fff7ed'; // Orange
                                                    elseif($pct > 0) $bg = '#fff1f2'; // Red
                                                    
                                                    echo "<td style='background:{$bg}'>{$pct}%</td>";
                                                } else {
                                                    echo "<td></td>";
                                                }
                                            }
                                         ?>
                                     </tr>
                                     <?php } ?>
                                 </tbody>
                             </table>
                         </div>
                    </div>
                </div>
            </div>
            
            <!-- CLV Distribution -->
            <div class="col-md-5">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-diamond"></i> Customer Lifetime Value (CLV)</h4>
                        <p class="text-muted small mbot15">Distribution of total revenue per client.</p>
                        
                        <div class="row text-center mbot20">
                            <div class="col-xs-6">
                                <h3 class="bold text-success no-margin"><?php echo app_format_money($clv_stats['avg_clv'], get_base_currency()); ?></h3>
                                <span class="text-muted">Avg. CLV</span>
                            </div>
                            <div class="col-xs-6">
                                <h3 class="bold text-info no-margin"><?php echo app_format_money($clv_stats['top_clv'], get_base_currency()); ?></h3>
                                <span class="text-muted">Top CLV</span>
                            </div>
                        </div>

                        <div class="relative" style="max-height:300px">
                            <canvas id="clv_chart"></canvas>
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
        
        // 0. Legacy Charts
        new Chart($('#customer_growth_chart'), {
            type: 'line',
            data: <?php echo $new_vs_returning; ?>,
            options: { maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
        });

        new Chart($('#geography_chart'), {
            type: 'horizontalBar',
            data: <?php echo $geography; ?>,
            options: { maintainAspectRatio: false, legend: { display: false } }
        });

        // 1. RFM Chart (Bar)
        var rfmRaw = <?php echo $rfm_data; ?>;
        var rfmLabels = Object.keys(rfmRaw);
        var rfmValues = Object.values(rfmRaw);
        
        // Custom colors for buckets
        var rfmColors = rfmLabels.map(function(label){
            if(label === 'Champions') return '#10b981'; // Green
            if(label === 'Loyal Customers') return '#34d399';
            if(label === 'At Risk') return '#f59e0b'; // Orange
            if(label === 'Cant Lose Them') return '#f472b6'; // Pink
            if(label === 'Hibernating') return '#64748b'; // Gray
            if(label === 'Lost') return '#1e293b'; // Dark
            return '#3b82f6'; // Blue default
        });

        new Chart($('#rfm_chart'), {
            type: 'bar',
            data: {
                labels: rfmLabels,
                datasets: [{
                    label: 'Customer Count',
                    data: rfmValues,
                    backgroundColor: rfmColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true } }]
                }
            }
        });
        
        // 2. Churn Analysis (Doughnut)
        var churnRaw = <?php echo $churn_data; ?>;
        new Chart($('#churn_chart'), {
            type: 'doughnut',
            data: {
                labels: ['High Risk', 'Medium Risk', 'Safe'],
                datasets: [{
                    data: [churnRaw.high_risk, churnRaw.medium_risk, churnRaw.safe],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
        
        // 3. CLV Histogram
        var clvBuckets = <?php echo $clv_buckets; ?>;
        new Chart($('#clv_chart'), {
            type: 'bar',
            data: {
                labels: Object.keys(clvBuckets),
                datasets: [{
                    label: 'Customers',
                    data: Object.values(clvBuckets),
                    backgroundColor: '#8b5cf6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: { yAxes: [{ ticks: { beginAtZero: true } }] }
            }
        });
    });
</script>
