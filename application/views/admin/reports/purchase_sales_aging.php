<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
  <div class="col-md-12">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('purchase_sales_aging'); ?></h4>
        <hr class="hr-panel-heading" />

        <?php if(isset($purchase_model_loaded) && !$purchase_model_loaded){ ?>
          <div class="alert alert-warning">
            <?php echo _l('purchase_module_not_available'); ?>
            <br>
            <?php echo _l('purchase_module_not_available_report_note'); ?>
          </div>
        <?php } ?>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="product_id"><?php echo _l('product'); ?></label>
              <select class="selectpicker" name="product_id" id="product_id" data-width="100%" data-live-search="true" required>
                <option value=""><?php echo _l('select_product'); ?></option>
                <?php if(isset($items)): foreach($items as $item){ ?>
                  <option value="<?php echo $item['id']; ?>"><?php echo isset($item['commodity_code']) && $item['commodity_code'] ? $item['commodity_code'].'-'.$item['description'] : $item['description']; ?></option>
                <?php } endif; ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="report_months"><?php echo _l('period_datepicker'); ?></label>
              <select class="selectpicker" name="months-report" id="months-report" data-width="100%">
                <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                <option value="this_month"><?php echo _l('this_month'); ?></option>
                <option value="1"><?php echo _l('last_month'); ?></option>
                <option value="this_year"><?php echo _l('this_year'); ?></option>
                <option value="last_year"><?php echo _l('last_year'); ?></option>
                <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>" selected><?php echo _l('report_sales_months_twelve_months'); ?></option>
                <option value="custom"><?php echo _l('period_datepicker_custom'); ?></option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="currency"><?php echo _l('currency'); ?></label>
              <select class="selectpicker" name="currency" data-width="100%">
                <?php if(isset($currencies)): foreach($currencies as $currency){ ?>
                  <option value="<?php echo $currency['id']; ?>" <?php if($currency['isdefault'] == 1){echo 'selected';} ?>><?php echo $currency['name']; ?></option>
                <?php } endif; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row mtop15">
          <div class="col-md-12">
            <div id="date-range" class="hide mbot15">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="report-from"><?php echo _l('report_sales_from_date'); ?></label>
                    <div class="input-group date">
                      <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar calendar-icon"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="report-to"><?php echo _l('report_sales_to_date'); ?></label>
                    <div class="input-group date">
                      <input type="text" class="form-control datepicker" id="report-to" name="report-to">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar calendar-icon"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="text-right mtop15">
              <button type="button" class="btn btn-info" id="generate-report"><?php echo _l('generate'); ?></button>
            </div>
          </div>
        </div>

        <div class="row mtop15 hide" id="report-results">
          <div class="col-md-12">
            <h4 class="text-success"><?php echo _l('purchase_summary'); ?></h4>
            <hr />
            <div class="row">
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('total_purchase_amount'); ?></div>
                  <div class="panel-body" id="purchase-total-amount">0</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('number_of_purchase_transactions'); ?></div>
                  <div class="panel-body" id="purchase-transaction-count">0</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('average_purchase_amount_per_transaction'); ?></div>
                  <div class="panel-body" id="purchase-average-amount">0</div>
                </div>
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-12">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th><?php echo _l('time_interval'); ?></th>
                      <th><?php echo _l('total_amount'); ?></th>
                      <th><?php echo _l('transaction_count'); ?></th>
                      <th><?php echo _l('average_amount'); ?></th>
                    </tr>
                  </thead>
                  <tbody id="purchase-intervals-body">
                    <!-- Will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-12">
                <div id="purchase-chart-container" style="height: 300px;"></div>
              </div>
            </div>

            <h4 class="text-primary mtop30"><?php echo _l('sales_summary'); ?></h4>
            <hr />
            <div class="row">
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('total_sales_amount'); ?></div>
                  <div class="panel-body" id="sales-total-amount">0</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('number_of_sales_transactions'); ?></div>
                  <div class="panel-body" id="sales-transaction-count">0</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="panel panel-default">
                  <div class="panel-heading"><?php echo _l('average_sale_amount_per_transaction'); ?></div>
                  <div class="panel-body" id="sales-average-amount">0</div>
                </div>
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-12">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th><?php echo _l('time_interval'); ?></th>
                      <th><?php echo _l('total_amount'); ?></th>
                      <th><?php echo _l('transaction_count'); ?></th>
                      <th><?php echo _l('average_amount'); ?></th>
                    </tr>
                  </thead>
                  <tbody id="sales-intervals-body">
                    <!-- Will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-12">
                <div id="sales-chart-container" style="height: 300px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    // Initialize datepicker
    init_datepicker();

    // Show/hide date range based on selection
    $('select[name="months-report"]').on('change', function() {
      var value = $(this).val();
      if (value == 'custom') {
        $('#date-range').removeClass('hide');
      } else {
        $('#date-range').addClass('hide');
      }
    });

    // Generate report button click
    $('#generate-report').on('click', function() {
      var product_id = $('#product_id').val();
      
      if (!product_id) {
        alert_float('warning', '<?php echo _l('please_select_product'); ?>');
        return;
      }
      
      generateReport();
    });

    // Function to generate the report
    function generateReport() {
      var product_id = $('#product_id').val();
      var report_months = $('#months-report').val();
      var report_from = $('#report-from').val();
      var report_to = $('#report-to').val();
      var currency = $('select[name="currency"]').val();
      
      $.ajax({
        url: admin_url + 'reports/purchase_sales_aging',
        type: 'POST',
        data: {
          product_id: product_id,
          report_months: report_months,
          report_from: report_from,
          report_to: report_to,
          report_currency: currency
        },
        dataType: 'json',
        success: function(response) {
          // Show results section
          $('#report-results').removeClass('hide');
          
          // Update purchase summary
          $('#purchase-total-amount').text(response.purchase_summary.total_amount);
          $('#purchase-transaction-count').text(response.purchase_summary.transaction_count);
          $('#purchase-average-amount').text(response.purchase_summary.average_amount);
          
          // Update sales summary
          $('#sales-total-amount').text(response.sales_summary.total_amount);
          $('#sales-transaction-count').text(response.sales_summary.transaction_count);
          $('#sales-average-amount').text(response.sales_summary.average_amount);
          
          // Update purchase intervals table
          var purchaseIntervalsHtml = '';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_1_week'); ?></td><td>' + response.purchase_summary.time_intervals['1_week'].total + '</td><td>' + response.purchase_summary.time_intervals['1_week'].count + '</td><td>' + response.purchase_summary.time_intervals['1_week'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_2_weeks'); ?></td><td>' + response.purchase_summary.time_intervals['2_weeks'].total + '</td><td>' + response.purchase_summary.time_intervals['2_weeks'].count + '</td><td>' + response.purchase_summary.time_intervals['2_weeks'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_1_month'); ?></td><td>' + response.purchase_summary.time_intervals['1_month'].total + '</td><td>' + response.purchase_summary.time_intervals['1_month'].count + '</td><td>' + response.purchase_summary.time_intervals['1_month'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_2_months'); ?></td><td>' + response.purchase_summary.time_intervals['2_months'].total + '</td><td>' + response.purchase_summary.time_intervals['2_months'].count + '</td><td>' + response.purchase_summary.time_intervals['2_months'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_3_months'); ?></td><td>' + response.purchase_summary.time_intervals['3_months'].total + '</td><td>' + response.purchase_summary.time_intervals['3_months'].count + '</td><td>' + response.purchase_summary.time_intervals['3_months'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_6_months'); ?></td><td>' + response.purchase_summary.time_intervals['6_months'].total + '</td><td>' + response.purchase_summary.time_intervals['6_months'].count + '</td><td>' + response.purchase_summary.time_intervals['6_months'].average + '</td></tr>';
          purchaseIntervalsHtml += '<tr><td><?php echo _l('past_12_months'); ?></td><td>' + response.purchase_summary.time_intervals['12_months'].total + '</td><td>' + response.purchase_summary.time_intervals['12_months'].count + '</td><td>' + response.purchase_summary.time_intervals['12_months'].average + '</td></tr>';
          $('#purchase-intervals-body').html(purchaseIntervalsHtml);
          
          // Update sales intervals table
          var salesIntervalsHtml = '';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_1_week'); ?></td><td>' + response.sales_summary.time_intervals['1_week'].total + '</td><td>' + response.sales_summary.time_intervals['1_week'].count + '</td><td>' + response.sales_summary.time_intervals['1_week'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_2_weeks'); ?></td><td>' + response.sales_summary.time_intervals['2_weeks'].total + '</td><td>' + response.sales_summary.time_intervals['2_weeks'].count + '</td><td>' + response.sales_summary.time_intervals['2_weeks'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_1_month'); ?></td><td>' + response.sales_summary.time_intervals['1_month'].total + '</td><td>' + response.sales_summary.time_intervals['1_month'].count + '</td><td>' + response.sales_summary.time_intervals['1_month'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_2_months'); ?></td><td>' + response.sales_summary.time_intervals['2_months'].total + '</td><td>' + response.sales_summary.time_intervals['2_months'].count + '</td><td>' + response.sales_summary.time_intervals['2_months'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_3_months'); ?></td><td>' + response.sales_summary.time_intervals['3_months'].total + '</td><td>' + response.sales_summary.time_intervals['3_months'].count + '</td><td>' + response.sales_summary.time_intervals['3_months'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_6_months'); ?></td><td>' + response.sales_summary.time_intervals['6_months'].total + '</td><td>' + response.sales_summary.time_intervals['6_months'].count + '</td><td>' + response.sales_summary.time_intervals['6_months'].average + '</td></tr>';
          salesIntervalsHtml += '<tr><td><?php echo _l('past_12_months'); ?></td><td>' + response.sales_summary.time_intervals['12_months'].total + '</td><td>' + response.sales_summary.time_intervals['12_months'].count + '</td><td>' + response.sales_summary.time_intervals['12_months'].average + '</td></tr>';
          $('#sales-intervals-body').html(salesIntervalsHtml);
          
          // Generate charts
          generatePurchaseChart(response.purchase_summary);
          generateSalesChart(response.sales_summary);
        },
        error: function(xhr, status, error) {
          alert_float('danger', '<?php echo _l('error_generating_report'); ?>');
          console.error(error);
        }
      });
    }
    
    // Function to generate purchase chart
    function generatePurchaseChart(purchaseData) {
      if (typeof Chart === 'undefined') {
        return;
      }
      
      var ctx = document.createElement('canvas');
      $('#purchase-chart-container').html('');
      $('#purchase-chart-container').append(ctx);
      
      var labels = [
        '<?php echo _l('past_1_week'); ?>',
        '<?php echo _l('past_2_weeks'); ?>',
        '<?php echo _l('past_1_month'); ?>',
        '<?php echo _l('past_2_months'); ?>',
        '<?php echo _l('past_3_months'); ?>',
        '<?php echo _l('past_6_months'); ?>',
        '<?php echo _l('past_12_months'); ?>'
      ];
      
      var totalData = [
        parseFloat(purchaseData.time_intervals['1_week'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['2_weeks'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['1_month'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['2_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['3_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['6_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(purchaseData.time_intervals['12_months'].total.replace(/[^0-9.-]+/g, ''))
      ];
      
      var countData = [
        purchaseData.time_intervals['1_week'].count,
        purchaseData.time_intervals['2_weeks'].count,
        purchaseData.time_intervals['1_month'].count,
        purchaseData.time_intervals['2_months'].count,
        purchaseData.time_intervals['3_months'].count,
        purchaseData.time_intervals['6_months'].count,
        purchaseData.time_intervals['12_months'].count
      ];
      
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: '<?php echo _l('total_amount'); ?>',
              data: totalData,
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 1,
              yAxisID: 'y-axis-1'
            },
            {
              label: '<?php echo _l('transaction_count'); ?>',
              data: countData,
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1,
              type: 'line',
              yAxisID: 'y-axis-2'
            }
          ]
        },
        options: {
          responsive: true,
          title: {
            display: true,
            text: '<?php echo _l('purchase_summary_chart'); ?>'
          },
          scales: {
            yAxes: [
              {
                id: 'y-axis-1',
                type: 'linear',
                position: 'left',
                ticks: {
                  beginAtZero: true
                },
                scaleLabel: {
                  display: true,
                  labelString: '<?php echo _l('amount'); ?>'
                }
              },
              {
                id: 'y-axis-2',
                type: 'linear',
                position: 'right',
                ticks: {
                  beginAtZero: true
                },
                scaleLabel: {
                  display: true,
                  labelString: '<?php echo _l('count'); ?>'
                },
                gridLines: {
                  drawOnChartArea: false
                }
              }
            ]
          }
        }
      });
    }
    
    // Function to generate sales chart
    function generateSalesChart(salesData) {
      if (typeof Chart === 'undefined') {
        return;
      }
      
      var ctx = document.createElement('canvas');
      $('#sales-chart-container').html('');
      $('#sales-chart-container').append(ctx);
      
      var labels = [
        '<?php echo _l('past_1_week'); ?>',
        '<?php echo _l('past_2_weeks'); ?>',
        '<?php echo _l('past_1_month'); ?>',
        '<?php echo _l('past_2_months'); ?>',
        '<?php echo _l('past_3_months'); ?>',
        '<?php echo _l('past_6_months'); ?>',
        '<?php echo _l('past_12_months'); ?>'
      ];
      
      var totalData = [
        parseFloat(salesData.time_intervals['1_week'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['2_weeks'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['1_month'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['2_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['3_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['6_months'].total.replace(/[^0-9.-]+/g, '')),
        parseFloat(salesData.time_intervals['12_months'].total.replace(/[^0-9.-]+/g, ''))
      ];
      
      var countData = [
        salesData.time_intervals['1_week'].count,
        salesData.time_intervals['2_weeks'].count,
        salesData.time_intervals['1_month'].count,
        salesData.time_intervals['2_months'].count,
        salesData.time_intervals['3_months'].count,
        salesData.time_intervals['6_months'].count,
        salesData.time_intervals['12_months'].count
      ];
      
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: '<?php echo _l('total_amount'); ?>',
              data: totalData,
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              borderColor: 'rgba(255, 99, 132, 1)',
              borderWidth: 1,
              yAxisID: 'y-axis-1'
            },
            {
              label: '<?php echo _l('transaction_count'); ?>',
              data: countData,
              backgroundColor: 'rgba(153, 102, 255, 0.2)',
              borderColor: 'rgba(153, 102, 255, 1)',
              borderWidth: 1,
              type: 'line',
              yAxisID: 'y-axis-2'
            }
          ]
        },
        options: {
          responsive: true,
          title: {
            display: true,
            text: '<?php echo _l('sales_summary_chart'); ?>'
          },
          scales: {
            yAxes: [
              {
                id: 'y-axis-1',
                type: 'linear',
                position: 'left',
                ticks: {
                  beginAtZero: true
                },
                scaleLabel: {
                  display: true,
                  labelString: '<?php echo _l('amount'); ?>'
                }
              },
              {
                id: 'y-axis-2',
                type: 'linear',
                position: 'right',
                ticks: {
                  beginAtZero: true
                },
                scaleLabel: {
                  display: true,
                  labelString: '<?php echo _l('count'); ?>'
                },
                gridLines: {
                  drawOnChartArea: false
                }
              }
            ]
          }
        }
      });
    }
  });
</script>