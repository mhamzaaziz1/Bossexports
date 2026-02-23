<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-cubes" aria-hidden="true"></i> Deep Items Analytics</h4>
                <p class="text-muted">Product Performance, ABC Classification, and Bundling Opportunities.</p>
                <hr class="hr-panel-heading" />
            </div>
        </div>

        <!-- Row 1: ABC & Velocity -->
        <div class="row">
            <!-- ABC Analysis -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">ABC Analysis (Pareto Classification)</h4>
                        <p class="text-muted small mbot15">Class A (Top 80% Rev), Class B (Next 15%), Class C (Bottom 5%).</p>
                        
                        <div class="row">
                            <div class="col-md-5">
                                <div class="relative" style="max-height:250px">
                                    <canvas id="abc_chart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-condensed text-xs">
                                    <thead><tr><th>Class</th><th class="text-center">Items</th><th>Top Examples</th></tr></thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-success bold">Class A</td>
                                            <td class="text-center"><?php echo $abc['classes']['A']; ?></td>
                                            <td class="text-muted"><?php echo implode(', ', array_slice($abc['details']['A'], 0, 2)); ?>...</td>
                                        </tr>
                                        <tr>
                                            <td class="text-warning bold">Class B</td>
                                            <td class="text-center"><?php echo $abc['classes']['B']; ?></td>
                                            <td class="text-muted"><?php echo implode(', ', array_slice($abc['details']['B'], 0, 2)); ?>...</td>
                                        </tr>
                                        <tr>
                                            <td class="text-danger bold">Class C</td>
                                            <td class="text-center"><?php echo $abc['classes']['C']; ?></td>
                                            <td class="text-muted"><?php echo implode(', ', array_slice($abc['details']['C'], 0, 2)); ?>...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Velocity -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin text-info">Item Velocity (Most Frequently Invoiced)</h4>
                         <p class="text-muted small mbot15">Top 10 items by # of unique invoices.</p>
                        <div class="relative" style="max-height:250px">
                            <canvas id="velocity_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Row 2: Affinity & Seasonality -->
        <div class="row">
             <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-success"><i class="fa fa-link"></i> Frequently Bought Together</h4>
                         <p class="text-muted small mbot15">Top Product Affinity Pairs (Bundling Opportunities).</p>
                         
                         <table class="table table-striped font-medium-xs">
                             <thead>
                                 <tr>
                                     <th>Item 1</th>
                                     <th>Item 2</th>
                                     <th class="text-center">Common Invoices</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach($affinity as $pair){ ?>
                                 <tr>
                                     <td><?php echo $pair['item_a']; ?></td>
                                     <td><?php echo $pair['item_b']; ?></td>
                                     <td class="text-center bold"><?php echo $pair['frequency']; ?></td>
                                 </tr>
                                 <?php } ?>
                                 <?php if(empty($affinity)) echo "<tr><td colspan='3'>No significant pairings found yet.</td></tr>"; ?>
                             </tbody>
                         </table>
                    </div>
                </div>
             </div>
             
             <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin text-purple"><i class="fa fa-calendar"></i> Top Item Seasonality</h4>
                         <p class="text-muted small mbot15">Monthly sales count for your top 10 items.</p>
                         <div class="relative" style="max-height:350px">
                            <canvas id="seasonality_chart"></canvas>
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

        // 1. ABC Chart (Doughnut)
        var abcData = <?php echo json_encode(array_values($abc['classes'])); ?>;
        new Chart($('#abc_chart'), {
            type: 'doughnut',
            data: {
                labels: ['Class A (High Value)', 'Class B (Mid)', 'Class C (Low)'],
                datasets: [{
                    data: abcData,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right' }
            }
        });
        
        // 2. Velocity (Horizontal Bar)
        var velRaw = <?php echo json_encode($velocity); ?>;
        var velLabels = velRaw.map(x => x.description.substring(0, 20) + (x.description.length>20?'...':''));
        var velData = velRaw.map(x => x.invoice_count);
        
        new Chart($('#velocity_chart'), {
            type: 'horizontalBar',
            data: {
                labels: velLabels,
                datasets: [{
                    label: 'Invoices Count',
                    data: velData,
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: { xAxes: [{ ticks: { beginAtZero: true } }] }
            }
        });
        
        // 3. Seasonality (Line)
        var seasonRaw = <?php echo json_encode($seasonality); ?>;
        var seasonSets = [];
        var colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#d946ef', '#f43f5e'];
        var ci = 0;
        
        for(var item in seasonRaw){
            if(seasonRaw.hasOwnProperty(item)){
                 seasonSets.push({
                     label: item.substring(0, 15),
                     data: Object.values(seasonRaw[item]),
                     borderColor: colors[ci % colors.length],
                     fill: false
                 });
                 ci++;
            }
        }
        
        new Chart($('#seasonality_chart'), {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: seasonSets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                 tooltips: {
                     mode: 'index',
                     intersect: false
                 }
            }
        });
    });
</script>
