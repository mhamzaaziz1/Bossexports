<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h3 class="no-margin font-bold text-center"><i class="fa fa-pie-chart" aria-hidden="true"></i> FULL REPORT ANALYTICS</h3>
                <p class="text-center text-muted">Consolidated Executive Overview: Sales, Customers, Items, & Strategy.</p>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <!-- ================= SALES SECTION ================= -->
        <div class="row mtop20">
            <div class="col-md-12"><h4 class="bold text-info"><i class="fa fa-line-chart"></i> 1. SALES PERFORMANCE</h4><hr /></div>
            
            <!-- Cards -->
            <div class="col-md-3">
                <div class="panel_s"><div class="panel-body text-center">
                    <h4 class="bold no-margin"><?php echo app_format_money($sales_velocity['velocity'], get_base_currency()); ?></h4>
                    <span class="text-muted">Sales Velocity (Last 30 Days)</span>
                </div></div>
            </div>
            <div class="col-md-3">
                 <div class="panel_s"><div class="panel-body text-center">
                    <h4 class="bold no-margin"><?php echo round($win_rate['win_rate'], 1); ?>%</h4>
                    <span class="text-muted">Win Rate (Leads -> Customers)</span>
                </div></div>
            </div>
            <div class="col-md-3">
                 <div class="panel_s"><div class="panel-body text-center">
                    <h4 class="bold no-margin"><?php echo app_format_money($arpa, get_base_currency()); ?></h4>
                    <span class="text-muted">Avg Revenue Per Acc (ARPA)</span>
                </div></div>
            </div>
             <div class="col-md-3">
                 <div class="panel_s"><div class="panel-body text-center">
                    <h4 class="bold no-margin"><?php echo $sales_cycle['avg_days']; ?> Days</h4>
                    <span class="text-muted">Avg Sales Cycle Length</span>
                </div></div>
            </div>

            <!-- Charts Sales -->
            <div class="col-md-6">
                <div class="panel_s"><div class="panel-body">
                    <p class="bold">Cost vs Revenue vs Profit</p>
                    <div style="height:300px"><canvas id="profit_trend"></canvas></div>
                </div></div>
            </div>
             <div class="col-md-6">
                 <div class="panel_s"><div class="panel-body">
                    <p class="bold">Revenue Concentration (Risk)</p>
                    <div style="height:300px"><canvas id="concentration_chart"></canvas></div>
                </div></div>
            </div>
        </div>


        <!-- ================= CUSTOMER SECTION ================= -->
        <div class="row mtop20">
            <div class="col-md-12"><h4 class="bold text-success"><i class="fa fa-users"></i> 2. CUSTOMER INTELLIGENCE</h4><hr /></div>

             <!-- Customer KPIs -->
             <?php $kpis = $retention_kpis; ?>
             <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
                 <h4 class="bold no-margin"><?php echo $kpis['total_customers']; ?></h4><span class="text-muted">Total Customers</span>
             </div></div></div>
             <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
                 <h4 class="bold no-margin"><?php echo round($kpis['retention_rate'], 1); ?>%</h4><span class="text-muted">Retention Rate</span>
             </div></div></div>
             <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
                 <h4 class="bold no-margin text-danger"><?php echo round($kpis['churn_rate'], 1); ?>%</h4><span class="text-muted">Churn Rate</span>
             </div></div></div>
             <div class="col-md-3"><div class="panel_s"><div class="panel-body text-center">
                 <h4 class="bold no-margin text-success"><?php echo app_format_money($clv_stats['avg_clv'], get_base_currency()); ?></h4><span class="text-muted">Avg Lifetime Value (CLV)</span>
             </div></div></div>

             <!-- RFM & Churn -->
             <div class="col-md-8">
                 <div class="panel_s"><div class="panel-body">
                    <p class="bold">RFM Segmentation</p>
                    <div style="height:350px"><canvas id="rfm_chart"></canvas></div>
                 </div></div>
             </div>
             <div class="col-md-4">
                  <div class="panel_s"><div class="panel-body">
                    <p class="bold">Churn Risk Prediction</p>
                    <div style="height:350px"><canvas id="churn_chart"></canvas></div>
                 </div></div>
             </div>
             
             <!-- Cohort -->
             <div class="col-md-12">
                 <div class="panel_s"><div class="panel-body">
                     <p class="bold">Cohort Analysis (Retention Heatmap)</p>
                     <div class="table-responsive">
                         <table class="table table-bordered table-condensed text-center font-medium-xs">
                                 <thead><tr style="background:#f3f4f6;"><th class="text-left">Cohort</th><th>Size</th><?php for($i=0; $i<12; $i++){ echo "<th>M{$i}</th>"; } ?></tr></thead>
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
                                                    if($pct >= 80) $bg = '#d1fae5'; elseif($pct >= 50) $bg = '#fae8ff'; elseif($pct >= 20) $bg = '#fff7ed'; elseif($pct > 0) $bg = '#fff1f2';
                                                    echo "<td style='background:{$bg}'>{$pct}%</td>";
                                                } else { echo "<td></td>"; }
                                            }
                                         ?>
                                     </tr>
                                     <?php } ?>
                                 </tbody>
                         </table>
                     </div>
                 </div></div>
             </div>
        </div>


        <!-- ================= ITEMS SECTION ================= -->
        <div class="row mtop20">
            <div class="col-md-12"><h4 class="bold text-warning"><i class="fa fa-cubes"></i> 3. PRODUCT / SERVICE INSIGHTS</h4><hr /></div>
            
            <div class="col-md-6">
                <div class="panel_s"><div class="panel-body">
                    <p class="bold">ABC Analysis (Pareto)</p>
                     <div class="row">
                            <div class="col-md-5"><div style="height:250px"><canvas id="abc_chart"></canvas></div></div>
                            <div class="col-md-7">
                                <table class="table table-condensed text-xs">
                                    <thead><tr><th>Class</th><th>Items</th><th>Examples</th></tr></thead>
                                    <tbody>
                                        <tr><td class="text-success bold">A</td><td><?php echo $abc['classes']['A']; ?></td><td><?php echo implode(', ', array_slice($abc['details']['A'], 0, 1)); ?></td></tr>
                                        <tr><td class="text-warning bold">B</td><td><?php echo $abc['classes']['B']; ?></td><td><?php echo implode(', ', array_slice($abc['details']['B'], 0, 1)); ?></td></tr>
                                        <tr><td class="text-danger bold">C</td><td><?php echo $abc['classes']['C']; ?></td><td><?php echo implode(', ', array_slice($abc['details']['C'], 0, 1)); ?></td></tr>
                                    </tbody>
                                </table>
                            </div>
                     </div>
                </div></div>
            </div>

            <div class="col-md-6">
                 <div class="panel_s"><div class="panel-body">
                    <p class="bold">Seasonality Heatmap (Top 10)</p>
                    <div style="height:250px"><canvas id="seasonality_chart"></canvas></div>
                 </div></div>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        Chart.defaults.global.defaultFontFamily = 'Roboto';

        // --- SALES CHARTS ---
        var profitJSON = <?php echo $profit_trend; ?>;
        new Chart($('#profit_trend'), {
            type: 'bar',
            data: profitJSON,
            options: { responsive:true, maintainAspectRatio:false, scales: {yAxes:[{ticks:{beginAtZero:true}}]} }
        });

        var concJSON = <?php echo $concentration_struct; ?>;
        new Chart($('#concentration_chart'), {
            type: 'doughnut',
            data: concJSON,
            options: { responsive:true, maintainAspectRatio:false }
        });

        // --- CUSTOMER CHARTS ---
        var rfmRaw = <?php echo $rfm_data; ?>;
        var rfmColors = Object.keys(rfmRaw).map(function(label){
            if(label === 'Champions') return '#10b981'; if(label === 'Loyal Customers') return '#34d399'; if(label === 'At Risk') return '#f59e0b'; return '#3b82f6';
        });
        new Chart($('#rfm_chart'), {
            type: 'bar',
            data: { labels: Object.keys(rfmRaw), datasets: [{ label: 'Count', data: Object.values(rfmRaw), backgroundColor: rfmColors }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{display:false}, scales:{yAxes:[{ticks:{beginAtZero:true}}]} }
        });

        var churnRaw = <?php echo $churn_data; ?>;
        new Chart($('#churn_chart'), {
            type: 'doughnut',
            data: { labels: ['High Risk', 'Medium Risk', 'Safe'], datasets: [{ data: [churnRaw.high_risk, churnRaw.medium_risk, churnRaw.safe], backgroundColor: ['#ef4444', '#f59e0b', '#10b981'] }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{position:'bottom'} }
        });

        // --- ITEM CHARTS ---
        var abcData = <?php echo json_encode(array_values($abc['classes'])); ?>;
        new Chart($('#abc_chart'), {
            type: 'doughnut',
            data: { labels: ['A', 'B', 'C'], datasets: [{ data: abcData, backgroundColor: ['#10b981', '#f59e0b', '#ef4444'] }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{position:'right'} }
        });

        var seasonRaw = <?php echo json_encode($seasonality); ?>;
        var seasonSets = [];
        var colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6'];
        var ci = 0;
        for(var item in seasonRaw){
            if(seasonRaw.hasOwnProperty(item)){
                 seasonSets.push({ label: item.substring(0,10), data: Object.values(seasonRaw[item]), borderColor: colors[ci % colors.length], fill: false });
                 ci++;
            }
        }
        new Chart($('#seasonality_chart'), {
            type: 'line',
            data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: seasonSets },
            options: { responsive:true, maintainAspectRatio:false, tooltips:{mode:'index', intersect:false} }
        });
    });
</script>
