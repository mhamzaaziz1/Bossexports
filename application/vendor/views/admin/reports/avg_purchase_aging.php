<?php defined('BASEPATH') or exit('No direct script access allowed'); 

// Helper function to get readable bucket labels
function get_bucket_label($bucket) {
    // Handle special "over_X" buckets
    if (strpos($bucket, 'over_') === 0) {
        $days = str_replace('over_', '', $bucket);
        return _l('over') . ' ' . $days . ' ' . _l('days');
    }

    // Handle regular range buckets (e.g., "0_30")
    $parts = explode('_', $bucket);
    if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
        return $parts[0] . '-' . $parts[1] . ' ' . _l('days');
    }

    // Fallback for unknown formats
    return $bucket;
}
?>
<div class="row">
    <div class="col-md-12">
        <?php if (isset($report_data) && !empty($report_data)) { ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="no-margin"><?php echo _l('avg_purchase_aging'); ?></h4>
                            <hr class="hr-panel-heading" />

                            <!-- Summary Cards -->
                            <div class="row mtop15">
                                <?php 
                                // Calculate summary statistics
                                $total_items = count($report_data);
                                $total_value = 0;
                                $total_quantity = 0;
                                $high_risk_items = 0;
                                $medium_risk_items = 0;
                                $low_risk_items = 0;

                                foreach ($report_data as $row) {
                                    $total_value += isset($row['total_value']) ? $row['total_value'] : 0;
                                    $total_quantity += $row['total_quantity'];

                                    if (isset($row['risk_level'])) {
                                        if ($row['risk_level'] == 'high') {
                                            $high_risk_items++;
                                        } elseif ($row['risk_level'] == 'medium') {
                                            $medium_risk_items++;
                                        } elseif ($row['risk_level'] == 'low') {
                                            $low_risk_items++;
                                        }
                                    }
                                }

                                // Initialize overall aging buckets based on the first item's buckets
                                $overall_buckets = [];
                                if (!empty($report_data)) {
                                    $overall_buckets = array_fill_keys(array_keys($report_data[0]['aging_buckets']), 0);
                                }

                                foreach ($report_data as $row) {
                                    if (isset($row['aging_buckets'])) {
                                        foreach ($row['aging_buckets'] as $bucket => $value) {
                                            $overall_buckets[$bucket] += $value;
                                        }
                                    }
                                }
                                ?>

                                <!-- Total Items Card -->
                                <div class="col-md-3">
                                    <div class="panel_s">
                                        <div class="panel-body text-center" style="background-color: #03a9f4; color: white;">
                                            <h3 class="bold no-margin"><?php echo $total_items; ?></h3>
                                            <p class="no-margin"><?php echo _l('total_items'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Value Card -->
                                <div class="col-md-3">
                                    <div class="panel_s">
                                        <div class="panel-body text-center" style="background-color: #4caf50; color: white;">
                                            <h3 class="bold no-margin"><?php echo app_format_money($total_value, get_base_currency()); ?></h3>
                                            <p class="no-margin"><?php echo _l('total_value'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Quantity Card -->
                                <div class="col-md-3">
                                    <div class="panel_s">
                                        <div class="panel-body text-center" style="background-color: #ff9800; color: white;">
                                            <h3 class="bold no-margin"><?php echo $total_quantity; ?></h3>
                                            <p class="no-margin"><?php echo _l('total_quantity'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Risk Distribution Card -->
                                <div class="col-md-3">
                                    <div class="panel_s">
                                        <div class="panel-body text-center" style="background-color: #9c27b0; color: white;">
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <span class="bold"><?php echo $high_risk_items; ?></span>
                                                    <p class="no-margin"><span class="label label-danger"><?php echo _l('high'); ?></span></p>
                                                </div>
                                                <div class="col-xs-4">
                                                    <span class="bold"><?php echo $medium_risk_items; ?></span>
                                                    <p class="no-margin"><span class="label label-warning"><?php echo _l('medium'); ?></span></p>
                                                </div>
                                                <div class="col-xs-4">
                                                    <span class="bold"><?php echo $low_risk_items; ?></span>
                                                    <p class="no-margin"><span class="label label-success"><?php echo _l('low'); ?></span></p>
                                                </div>
                                            </div>
                                            <p class="no-margin mtop5"><?php echo _l('risk_distribution'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts Row -->
                            <div class="row mtop15">
                                <!-- Aging Buckets Chart -->
                                <div class="col-md-6">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="no-margin"><?php echo _l('aging_buckets_distribution'); ?></h4>
                                            <hr class="hr-panel-heading" />
                                            <canvas id="aging-buckets-chart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Risk Level Distribution Chart -->
                                <div class="col-md-6">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="no-margin"><?php echo _l('risk_level_distribution'); ?></h4>
                                            <hr class="hr-panel-heading" />
                                            <canvas id="risk-level-chart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trend Analysis Chart -->
                            <div class="row mtop15">
                                <div class="col-md-12">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="no-margin"><?php echo _l('aging_trend_analysis'); ?></h4>
                                            <hr class="hr-panel-heading" />
                                            <canvas id="trend-analysis-chart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Table -->
                            <div class="row mtop15">
                                <div class="col-md-12">
                                    <div class="panel_s">
                                        <div class="panel-body">
                                            <h4 class="no-margin"><?php echo _l('detailed_aging_report'); ?></h4>
                                            <hr class="hr-panel-heading" />
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped" id="avg-purchase-aging-table">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo _l('item'); ?></th>
                                                            <th><?php echo _l('avg_age_days'); ?></th>
                                                            <th><?php echo _l('risk_level'); ?></th>
                                                            <th><?php echo _l('total_purchases'); ?></th>
                                                            <th><?php echo _l('total_quantity'); ?></th>
                                                            <th><?php echo _l('total_value'); ?></th>
                                                            <th><?php echo _l('avg_value_per_unit'); ?></th>
                                                            <?php if (isset($report_data[0]['inventory_turnover'])) { ?>
                                                            <th><?php echo _l('inventory_turnover'); ?></th>
                                                            <?php } ?>
                                                            <th><?php echo _l('transaction_type'); ?></th>
                                                            <th><?php echo _l('aging_buckets'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($report_data as $row) { 
                                                            // Determine row color based on risk level
                                                            $row_class = '';
                                                            if (isset($row['risk_level'])) {
                                                                if ($row['risk_level'] == 'high') {
                                                                    $row_class = 'danger';
                                                                } elseif ($row['risk_level'] == 'medium') {
                                                                    $row_class = 'warning';
                                                                } elseif ($row['risk_level'] == 'low') {
                                                                    $row_class = 'success';
                                                                }
                                                            }
                                                        ?>
                                                            <tr class="<?php echo $row_class; ?>">
                                                                <td><?php echo $row['description']; ?></td>
                                                                <td><?php echo $row['avg_age']; ?></td>
                                                                <td>
                                                                    <?php if (isset($row['risk_level'])) { 
                                                                        $badge_class = 'label-default';
                                                                        if ($row['risk_level'] == 'high') {
                                                                            $badge_class = 'label-danger';
                                                                        } elseif ($row['risk_level'] == 'medium') {
                                                                            $badge_class = 'label-warning';
                                                                        } elseif ($row['risk_level'] == 'low') {
                                                                            $badge_class = 'label-success';
                                                                        }
                                                                    ?>
                                                                    <span class="label <?php echo $badge_class; ?>"><?php echo _l($row['risk_level']); ?></span>
                                                                    <?php } ?>
                                                                </td>
                                                                <td><?php echo $row['total_purchases']; ?></td>
                                                                <td><?php echo $row['total_quantity']; ?></td>
                                                                <td><?php echo isset($row['total_value']) ? app_format_money($row['total_value'], get_base_currency()) : ''; ?></td>
                                                                <td><?php echo isset($row['avg_value_per_unit']) ? app_format_money($row['avg_value_per_unit'], get_base_currency()) : ''; ?></td>
                                                                <?php if (isset($report_data[0]['inventory_turnover'])) { ?>
                                                                <td><?php echo isset($row['inventory_turnover']) ? $row['inventory_turnover'] : ''; ?></td>
                                                                <?php } ?>
                                                                <td>
                                                                    <?php 
                                                                    if ($row['type'] == 'purchase') {
                                                                        echo _l('purchases');
                                                                    } elseif ($row['type'] == 'sale') {
                                                                        echo _l('sales');
                                                                    } elseif ($row['type'] == 'combined') {
                                                                        echo _l('both_sales_and_purchases');
                                                                    } else {
                                                                        echo _l('no_data');
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (isset($row['aging_buckets'])) { ?>
                                                                    <div class="aging-buckets-bar">
                                                                        <?php foreach ($row['aging_buckets'] as $bucket => $value) { 
                                                                            $bucket_class = 'success';
                                                                            if ($bucket == '91_180' || $bucket == '181_365') {
                                                                                $bucket_class = 'warning';
                                                                            } elseif ($bucket == 'over_365') {
                                                                                $bucket_class = 'danger';
                                                                            }

                                                                            $percentage = isset($row['aging_percentages'][$bucket]) ? $row['aging_percentages'][$bucket] : 0;
                                                                            if ($percentage > 0) {
                                                                        ?>
                                                                        <div class="progress-bar progress-bar-<?php echo $bucket_class; ?>" 
                                                                             style="width: <?php echo $percentage; ?>%" 
                                                                             title="<?php echo get_bucket_label($bucket) . ': ' . $value . ' (' . $percentage . '%)'; ?>">
                                                                        </div>
                                                                        <?php } } ?>
                                                                    </div>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
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
                    // Initialize DataTable
                    var avgPurchaseAgingTable = $('#avg-purchase-aging-table').DataTable({
                        "order": [[1, "desc"]], // Sort by avg_age by default
                        "pageLength": 25,
                        "columnDefs": [
                            { "type": "numeric", "targets": [1, 3, 4, 5, 6<?php echo isset($report_data[0]['inventory_turnover']) ? ', 7' : ''; ?>] }
                        ],
                        "dom": 'Bfrtip',
                        "buttons": [
                            'copyHtml5',
                            'excelHtml5',
                            'csvHtml5',
                            'pdfHtml5'
                        ]
                    });

                    // Initialize Charts

                    // Aging Buckets Chart
                    var agingBucketsCtx = document.getElementById('aging-buckets-chart').getContext('2d');
                    var agingBucketsChart = new Chart(agingBucketsCtx, {
                        type: 'bar',
                        data: {
                            labels: [
                                <?php 
                                // Generate labels dynamically based on bucket keys
                                $labels = [];
                                $data_values = [];

                                foreach ($overall_buckets as $bucket => $value) {
                                    $label = get_bucket_label($bucket);
                                    $labels[] = "'" . $label . "'";
                                    $data_values[] = $value;
                                }

                                echo implode(', ', $labels);
                                ?>
                            ],
                            datasets: [{
                                label: '<?php echo _l('quantity'); ?>',
                                data: [
                                    <?php echo implode(', ', $data_values); ?>
                                ],
                                backgroundColor: [
                                    '#4caf50',
                                    '#8bc34a',
                                    '#cddc39',
                                    '#ffc107',
                                    '#ff9800',
                                    '#f44336'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Risk Level Chart
                    var riskLevelCtx = document.getElementById('risk-level-chart').getContext('2d');
                    var riskLevelChart = new Chart(riskLevelCtx, {
                        type: 'pie',
                        data: {
                            labels: [
                                '<?php echo _l('high'); ?> <?php echo _l('risk'); ?>', 
                                '<?php echo _l('medium'); ?> <?php echo _l('risk'); ?>', 
                                '<?php echo _l('low'); ?> <?php echo _l('risk'); ?>'
                            ],
                            datasets: [{
                                data: [
                                    <?php echo $high_risk_items; ?>,
                                    <?php echo $medium_risk_items; ?>,
                                    <?php echo $low_risk_items; ?>
                                ],
                                backgroundColor: [
                                    '#f44336',
                                    '#ff9800',
                                    '#4caf50'
                                ]
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });

                    // Trend Analysis Chart
                    <?php 
                    // Prepare trend data
                    $trend_months = [];
                    $trend_values = [];

                    if (!empty($report_data) && isset($report_data[0]['trend_data'])) {
                        // Get the first item's trend data for months
                        $first_item_trend = $report_data[0]['trend_data'];
                        $trend_months = array_keys($first_item_trend);

                        // Calculate average trend values across all items
                        foreach ($trend_months as $month) {
                            $month_sum = 0;
                            $month_count = 0;

                            foreach ($report_data as $row) {
                                if (isset($row['trend_data'][$month])) {
                                    $month_sum += $row['trend_data'][$month];
                                    $month_count++;
                                }
                            }

                            $trend_values[] = $month_count > 0 ? round($month_sum / $month_count, 2) : 0;
                        }

                        // Format month labels
                        foreach ($trend_months as &$month) {
                            $date = DateTime::createFromFormat('Y-m', $month);
                            $month = $date->format('M Y');
                        }

                        // Reverse arrays to show oldest to newest
                        $trend_months = array_reverse($trend_months);
                        $trend_values = array_reverse($trend_values);
                    }
                    ?>

                    var trendAnalysisCtx = document.getElementById('trend-analysis-chart').getContext('2d');
                    var trendAnalysisChart = new Chart(trendAnalysisCtx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($trend_months); ?>,
                            datasets: [{
                                label: '<?php echo _l('avg_age_days'); ?>',
                                data: <?php echo json_encode($trend_values); ?>,
                                borderColor: '#03a9f4',
                                backgroundColor: 'rgba(3, 169, 244, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Style the aging buckets bars
                    $('.aging-buckets-bar').each(function() {
                        $(this).css({
                            'display': 'flex',
                            'height': '20px',
                            'width': '100%',
                            'border-radius': '4px',
                            'overflow': 'hidden'
                        });
                    });
                });

                // Helper function to get readable bucket labels
                function get_bucket_label(bucket) {
                    // Handle special "over_X" buckets
                    if (bucket.startsWith('over_')) {
                        var days = bucket.replace('over_', '');
                        return '<?php echo _l('over'); ?> ' + days + ' <?php echo _l('days'); ?>';
                    }

                    // Handle regular range buckets (e.g., "0_30")
                    var parts = bucket.split('_');
                    if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                        return parts[0] + '-' + parts[1] + ' <?php echo _l('days'); ?>';
                    }

                    // Fallback for unknown formats
                    return bucket;
                }
            </script>
        <?php } else { ?>
            <div class="alert alert-info">
                <?php echo isset($selected_transaction_type) ? _l('no_data_found_for_selected_criteria') : _l('generate_report_to_view_data'); ?>
            </div>
        <?php } ?>
    </div>
</div>
