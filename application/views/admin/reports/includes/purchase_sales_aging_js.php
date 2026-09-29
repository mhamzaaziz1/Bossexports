<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(function() {
        // Initialize the purchase & sales aging report when the page loads
        if (typeof(init_report) !== 'undefined') {
            // Add purchase & sales aging report to the init_report function
            var originalInitReport = init_report;
            init_report = function(e, type) {
                if (type == 'purchase-sales-aging-report') {
                    // Hide all reports
                    $('div[id$="-report"]').addClass('hide');
                    
                    // Show the report container
                    $('#report').removeClass('hide');
                    
                    // Show the purchase & sales aging report
                    $('.purchase-sales-aging-report').removeClass('hide');
                    
                    // Hide other reports
                    $('.directors-report').addClass('hide');
                    $('.invoices-report').addClass('hide');
                    $('.items-report').addClass('hide');
                    $('.sales-aging-report').addClass('hide');
                    $('.avg-sale-aging-report').addClass('hide');
                    $('.payments-received').addClass('hide');
                    $('.credit-notes').addClass('hide');
                    $('.proposals-report').addClass('hide');
                    $('.estimates-report').addClass('hide');
                    $('.customers-report').addClass('hide');
                    $('.total-income').addClass('hide');
                    $('.payment-modes').addClass('hide');
                    $('.customers-group').addClass('hide');
                    
                    // Show the date range filter
                    $('#report-time').removeClass('hide');
                    
                    // Set the active link
                    $('a[onclick*="init_report"]').removeClass('active');
                    $(e).addClass('active');
                    
                    // Initialize the report
                    init_purchase_sales_aging_report();
                    
                    return;
                }
                
                // Call the original init_report function for other report types
                originalInitReport(e, type);
            };
        }
        
        // Function to initialize the purchase & sales aging report
        function init_purchase_sales_aging_report() {
            // Show the report container
            $('#purchase-sales-aging').removeClass('hide');
            
            // Set up event handlers for the report
            $('#generate-report').on('click', function() {
                var product_id = $('#product_id').val();
                
                if (!product_id) {
                    alert_float('warning', '<?php echo _l('please_select_product'); ?>');
                    return;
                }
                
                generateReport();
            });
            
            // Show/hide date range based on selection
            $('select[name="months-report"]').on('change', function() {
                var value = $(this).val();
                if (value == 'custom') {
                    $('#date-range').removeClass('hide');
                } else {
                    $('#date-range').addClass('hide');
                }
            });
            
            // Function to generate the report
            function generateReport() {
                var product_id = $('#product_id').val();
                var report_months = $('select[name="months-report"]').val();
                var report_from = $('input[name="report-from"]').val();
                var report_to = $('input[name="report-to"]').val();
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
        }
    });
</script>