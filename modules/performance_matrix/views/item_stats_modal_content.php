<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$commodity_item = $item; // For compatibility with migrated code
?>
<div class="row">
    <div class="col-md-12">
        <h4 class="modal-title font-bold mbot15 text-primary"><i class="fa fa-line-chart"></i> <?php echo _l('Performance Matrix'); ?> - <?php echo html_entity_decode($commodity_item->description); ?></h4>
        <hr />
    </div>
</div>

<div class="row">
    <!-- 1. Critical Inventory Alerts -->
    <div class="col-md-12">
        <h4 class="text-danger bold"><i class="fa fa-exclamation-triangle"></i> 1. Critical Inventory Alerts</h4>
        <hr class="m5">
        <div class="alert alert-warning">
             <div class="row">
                <div class="col-md-8">
                    <strong>Stock Level Warning:</strong> 
                    Current stock is approaching the safety threshold.
                    <p class="no-margin small">Suggested Action: Expedite next PO to avoid stockout during upcoming peak season.</p>
                </div>
                <div class="col-md-4 text-right">
                    <h3 class="no-margin bold">782 Units</h3>
                    <span class="small">Available Now</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Inventory Health Analytics -->
    <div class="col-md-12 mtop10">
        <h4 class="text-success bold"><i class="fa fa-heartbeat"></i> 2. Inventory Health Analytics</h4>
        <hr class="m5">
        <div class="row">
           <div class="col-md-4">
               <ul class="list-group no-margin borderShadow">
                   <li class="list-group-item d-flex justify-content-between align-items-center">
                       Total Capacity Occupied
                       <span class="badge badge-primary badge-pill pull-right">45%</span>
                   </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                       Average Days in Stock
                       <span class="badge badge-info badge-pill pull-right">32 Days</span>
                   </li>
                   <li class="list-group-item d-flex justify-content-between align-items-center">
                       Safety Stock
                       <span class="badge badge-success badge-pill pull-right">200 Units</span>
                   </li>
               </ul>
           </div>
           <div class="col-md-8">
               <table class="table table-bordered no-margin borderShadow">
                   <thead>
                       <tr class="bg-light">
                           <th>Metric</th>
                           <th>Value</th>
                           <th>Status</th>
                           <th>Action</th>
                       </tr>
                   </thead>
                   <tbody>
                       <tr>
                           <td>Inventory Turnover Ratio</td>
                           <td>4.5</td>
                           <td><span class="label label-success">Healthy</span></td>
                           <td>Maintain current ordering schedule.</td>
                       </tr>
                       <tr>
                           <td>Average Days to Sell</td>
                           <td>45 Days</td>
                           <td><span class="label label-warning">Monitor</span></td>
                           <td>Consider a small discount to speed up velocity.</td>
                       </tr>
                       <tr>
                           <td>Stockout Risk</td>
                           <td>Low</td>
                           <td><span class="label label-success">Good</span></td>
                           <td>N/A</td>
                       </tr>
                   </tbody>
               </table>
           </div>
        </div>
    </div>

    <!-- 3. Customer Metrics -->
    <div class="col-md-12 mtop20">
       <h4 class="text-info bold"><i class="fa fa-users"></i> 3. Customer Metrics</h4>
       <hr class="m5">
       <div class="row">
           <div class="col-md-6 text-center border-right">
               <h5>Return Rate Analysis</h5>
               <div class="progress mtop15">
                  <div class="progress-bar progress-bar-success" role="progressbar" style="width: 95%">
                    Kept (95%)
                  </div>
                  <div class="progress-bar progress-bar-danger" role="progressbar" style="width: 5%">
                    Returned (5%)
                  </div>
                </div>
                <p class="text-muted small">The industry average return rate for this category is 7%. This product is performing better than average.</p>
           </div>
           <div class="col-md-6">
               <h5 class="bold">Demographic Appeal</h5>
               <p><strong>Primary Audience:</strong> Adults aged 25-45, primarily located in urban areas.</p>
               <p><strong>Feedback Sentiment:</strong> 92% Positive. Common accolades include "Durability" and "Value for Money". Common complaints focus on "Packaging".</p>
           </div>
       </div>
    </div>

    <!-- 4. Supply Chain -->
    <div class="col-md-12 mtop20">
       <h4 class="text-danger bold"><i class="fa fa-truck"></i> 4. Supply Chain & Logistics</h4>
       <hr class="m5">
       <div class="row">
           <div class="col-md-4">
               <div class="panel_s borderShadow">
                   <div class="panel-body text-center">
                       <h5 class="bold uppercase text-muted">Lead Time</h5>
                       <h3 class="no-margin bold">14 Days</h3>
                       <p class="text-muted small">Standard deviation of +/- 2 days.</p>
                   </div>
               </div>
           </div>
            <div class="col-md-4">
               <div class="panel_s borderShadow">
                   <div class="panel-body text-center">
                       <h5 class="bold uppercase text-muted">Vendor Performance</h5>
                       <h3 class="no-margin bold">4.8 / 5.0</h3>
                       <p class="text-muted small">Consistently on time with minimal defects.</p>
                   </div>
               </div>
           </div>
            <div class="col-md-4">
               <div class="panel_s borderShadow">
                   <div class="panel-body text-center">
                       <h5 class="bold uppercase text-muted">Next Shipment</h5>
                       <h3 class="no-margin bold">Oct 15, 2026</h3>
                       <p class="text-muted small">Quantity: 1,000 Units (In Transit)</p>
                   </div>
               </div>
           </div>
       </div>
    </div>

    <!-- 5. Forecasting & Seasonality -->
    <div class="col-md-12 mtop10">
       <h4 class="text-secondary bold"><i class="fa fa-calendar"></i> 5. Forecasting & Seasonality</h4>
       <hr class="m5">
       <div class="alert alert-info">
           <strong>Peak Season Alert:</strong> This product historically sees a 40% spike in sales during November/December.
       </div>
       <table class="table table-striped table-bordered borderShadow">
           <thead class="bg-light">
               <tr>
                   <th>Month</th>
                   <th>Predicted Sales</th>
                   <th>Confidence Level</th>
               </tr>
           </thead>
           <tbody>
               <tr>
                   <td>October</td>
                   <td>1,500 Units</td>
                   <td>High</td>
               </tr>
               <tr>
                   <td>November</td>
                   <td>2,100 Units</td>
                   <td>High</td>
               </tr>
               <tr>
                   <td>December</td>
                   <td>2,800 Units</td>
                   <td>Medium</td>
               </tr>
           </tbody>
       </table>
    </div>

    <!-- 6. Retail Optimization -->
    <div class="col-md-12 mtop20">
       <h4 class="text-success bold"><i class="fa fa-shopping-cart"></i> 6. Retail Optimization</h4>
       <hr class="m5">
       <div class="row">
           <div class="col-md-6 border-right">
               <h5 class="bold">Shelf Placement Logic</h5>
               <p>Optimized for eye-level placement. Best performance observed when placed adjacent to "Complementary Product A". Avoid bottom shelf due to lower visibility for premium packaging.</p>
           </div>
            <div class="col-md-6">
               <h5 class="bold">Bundle Opportunities</h5>
               <p>Frequently bought with: <strong>Product B (SKU-123)</strong> and <strong>Product C (SKU-456)</strong>. Consider creating a holiday bundle with these items for a 10% discount to drive volume.</p>
           </div>
       </div>
       <div class="well mtop15">
           <h5 class="bold">Discount Elasticity</h5>
           <p>A 5% discount typically yields a 12% increase in sales volume. A 10% discount yields a 30% increase. The optimal discount for clearing excess inventory is 15%.</p>
       </div>
    </div>

    <!-- 7. Advanced Analytics -->
    <div class="col-md-12 mtop20">
        <h4 class="text-primary bold"><i class="fa fa-bar-chart"></i> 7. Advanced Analytics</h4>
        <hr class="m5">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s borderShadow">
                    <div class="panel-body">
                        <h5 class="bold uppercase text-muted">Sales Performance (Trend)</h5>
                        <div class="relative" style="height:300px">
                            <canvas id="item_sales_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s borderShadow">
                    <div class="panel-body">
                        <h5 class="bold uppercase text-muted">Purchase Performance (Trend)</h5>
                        <div class="relative" style="height:300px">
                            <canvas id="item_purchases_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mtop20">
            <div class="col-md-6">
                <div class="panel_s borderShadow">
                    <div class="panel-body">
                        <h5 class="bold uppercase text-muted">Customer Demographics</h5>
                        <div class="relative" style="height:300px">
                            <canvas id="customer_demographics_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s borderShadow">
                    <div class="panel-body">
                        <h5 class="bold uppercase text-muted">Peak Shopping Hours</h5>
                        <div class="relative" style="height:300px">
                            <canvas id="peak_hours_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var itemId = <?php echo $item_id; ?>;
    var currentYear = <?php echo date('Y'); ?>;

    function renderItemChart(transactionType, canvasId, label, color) {
        $.post(admin_url + 'performance_matrix/get_item_performance_data', {
            item_id: itemId,
            transaction_type: transactionType,
            year: currentYear
        }).done(function(response) {
            var data = JSON.parse(response);
            var ctx = document.getElementById(canvasId).getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: label,
                            data: data.amounts,
                            backgroundColor: 'rgba(' + color + ', 0.1)',
                            borderColor: 'rgba(' + color + ', 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Trend Line',
                            data: data.trend,
                            borderColor: '#e11',
                            borderWidth: 1.5,
                            borderDash: [5, 5],
                            fill: false,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return app.format_money(value); }
                            }
                        }
                    }
                }
            });
        });
    }

    renderItemChart('sales', 'item_sales_chart', 'Monthly Sales', '54, 162, 235');
    renderItemChart('purchases', 'item_purchases_chart', 'Monthly Purchases', '67, 160, 71');

    // Dummy Demographic Chart
    new Chart(document.getElementById('customer_demographics_chart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Retail', 'Wholesale', 'Ecommerce', 'Other'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#4285F4', '#34A853', '#FBBC05', '#EA4335']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Dummy Peak Hours Chart
    new Chart(document.getElementById('peak_hours_chart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['8-10', '10-12', '12-14', '14-16', '16-18', '18-20'],
            datasets: [{
                label: 'Order Density',
                data: [12, 45, 78, 92, 65, 34],
                backgroundColor: '#673ab7'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
<div class="modal-footer mtop20 no-padding-bottom">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print Matrix</button>
</div>
