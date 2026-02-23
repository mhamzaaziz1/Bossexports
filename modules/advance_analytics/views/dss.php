<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            Decision Support System <small class="text-muted">AI-Driven Forecasting</small>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-8">
                                <div class="relative" style="height:400px">
                                    <canvas id="dssChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="widget">
                                    <div class="panel-body bg-info text-white">
                                        <h4><i class="fa fa-lightbulb-o"></i> Executive Insight</h4>
                                        <hr class="hr-white" />
                                        
                                        <!-- Tabs -->
                                        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist" style="border-bottom:0; margin-bottom:10px;">
                                            <li role="presentation" class="active">
                                                <a href="#tab_hw" aria-controls="tab_hw" role="tab" data-toggle="tab" style="color:white;">Holt-Winters</a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#tab_lr" aria-controls="tab_lr" role="tab" data-toggle="tab" style="color:white;">Linear</a>
                                            </li>
                                            <li role="presentation">
                                                <a href="#tab_ma" aria-controls="tab_ma" role="tab" data-toggle="tab" style="color:white;">Moving Avg</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane active" id="tab_hw">
                                                <p id="narrative_hw" class="font-medium" style="font-size:1.1em; line-height:1.6; min-height:80px;">Loading...</p>
                                                <small class="text-white-50">Best for: Seasonal sales cycles</small>
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="tab_lr">
                                                <p id="narrative_lr" class="font-medium" style="font-size:1.1em; line-height:1.6; min-height:80px;">Loading...</p>
                                                <small class="text-white-50">Best for: Long-term trend direction</small>
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="tab_ma">
                                                <p id="narrative_ma" class="font-medium" style="font-size:1.1em; line-height:1.6; min-height:80px;">Loading...</p>
                                                <small class="text-white-50">Best for: Conservative baseline</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget p-top-20">
                                    <div class="panel-body">
                                        <h4>Algorithm Details</h4>
                                        <p class="text-muted">
                                            Use the tabs above to compare AI interpretations. 
                                            <b>Holt-Winters</b> is usually most accurate for recurring business patterns, while <b>Linear Regression</b> shows the raw growth trend.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dssPayload = <?php echo $dss_data; ?>;
        
        // Narratives
        document.getElementById('narrative_hw').innerHTML = dssPayload.narratives.holt_winters;
        document.getElementById('narrative_lr').innerHTML = dssPayload.narratives.linear;
        document.getElementById('narrative_ma').innerHTML = dssPayload.narratives.moving_avg;
        
        // Preparation for Chart
        var history = dssPayload.history;
        var forecasts = dssPayload.forecasts; // info: {holt_winters: [], linear: [], moving_avg: []}
        var labels = dssPayload.labels;
        
        // We assume all forecasts have same length (6)
        var forecastLen = forecasts.holt_winters.length;
        
        // Generate future labels
        var lastLabel = labels[labels.length - 1]; // "YYYY-MM"
        var dateObj = new Date(lastLabel + "-01");
        
        for(let i=0; i < forecastLen; i++) {
            dateObj.setMonth(dateObj.getMonth() + 1);
            let m = dateObj.getMonth() + 1;
            let monthStr = m < 10 ? '0' + m : m;
            labels.push(dateObj.getFullYear() + "-" + monthStr);
        }
        
        // Pad history with nulls for forecast part
        var chartHistory = history.slice();
        for(let i=0; i < forecastLen; i++) {
            chartHistory.push(null);
        }
        
        // Helper to pad forecast
        function padForecast(forecastData) {
             var padded = new Array(history.length - 1).fill(null);
             padded.push(history[history.length - 1]); // Connect lines
             return padded.concat(forecastData);
        }

        var ctx = document.getElementById('dssChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                {
                    label: 'Historical Revenue',
                    data: chartHistory,
                    borderColor: '#2563eb', // Blue
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: false,
                    tension: 0.1,
                    order: 1
                }, 
                {
                    label: 'Holt-Winters (Seasonal)',
                    data: padForecast(forecasts.holt_winters),
                    borderColor: '#9333ea', // Purple
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.3,
                    borderWidth: 3
                },
                {
                    label: 'Linear Regression (Trend)',
                    data: padForecast(forecasts.linear),
                    borderColor: '#10b981', // Green
                    borderDash: [2, 2],
                    fill: false,
                    tension: 0, // Straight line
                    borderWidth: 2
                },
                {
                    label: 'Moving Average (Baseline)',
                    data: padForecast(forecasts.moving_avg),
                    borderColor: '#f59e0b', // Orange
                    borderDash: [10, 5],
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Multi-Algorithm Forecast Comparison'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    });
</script>
