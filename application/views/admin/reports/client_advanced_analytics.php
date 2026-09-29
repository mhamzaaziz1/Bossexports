<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
        <?php echo _l('advanced_analytics'); ?> - <?php echo $client->company; ?>
    </h4>

    <!-- Client Score/Category Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="no-margin"><?php echo _l('client_categorization'); ?></h4>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php
                            $category_class = '';
                            $badge_class = '';
                            switch($client_score['category']) {
                                case 'excellent':
                                    $category_class = 'text-success';
                                    $badge_class = 'bg-success';
                                    break;
                                case 'good':
                                    $category_class = 'text-info';
                                    $badge_class = 'bg-info';
                                    break;
                                case 'average':
                                    $category_class = 'text-muted';
                                    $badge_class = 'bg-default';
                                    break;
                                case 'below_average':
                                    $category_class = 'text-warning';
                                    $badge_class = 'bg-warning';
                                    break;
                                case 'poor':
                                    $category_class = 'text-danger';
                                    $badge_class = 'bg-danger';
                                    break;
                            }
                            ?>
                            <span class="label <?php echo $badge_class; ?>" style="font-size: 14px; padding: 5px 10px; border-radius: 4px;">
                                <?php echo _l($client_score['category']); ?>
                            </span>
                        </div>
                    </div>
                    <hr class="hr-panel-heading" />

                    <div class="row">
                        <div class="col-md-4">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; height: auto; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <div class="text-center">
                                    <canvas id="clientScoreChart" width="150" height="150"></canvas>
                                    <h2 class="bold mtop10" style="font-size: 28px; color: #03a9f4;"><?php echo $client_score['total']; ?>/100</h2>
                                    <p style="font-size: 16px;"><?php echo _l('client_score'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; height: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mtop10">
                                            <p style="font-size: 14px; margin-bottom: 5px;"><?php echo _l('payment_promptness'); ?></p>
                                            <div class="progress" style="height: 10px; margin-bottom: 15px; border-radius: 5px;">
                                                <div class="progress-bar progress-bar-info" role="progressbar" style="width: <?php echo ($client_score['payment_promptness']/25*100); ?>%; background-color: #03a9f4;">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="bold"><?php echo $client_score['payment_promptness']; ?>/25</span>
                                            </div>
                                        </div>

                                        <div class="mtop20">
                                            <p style="font-size: 14px; margin-bottom: 5px;"><?php echo _l('purchase_frequency'); ?></p>
                                            <div class="progress" style="height: 10px; margin-bottom: 15px; border-radius: 5px;">
                                                <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo ($client_score['purchase_frequency']/25*100); ?>%; background-color: #8bc34a;">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="bold"><?php echo $client_score['purchase_frequency']; ?>/25</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mtop10">
                                            <p style="font-size: 14px; margin-bottom: 5px;"><?php echo _l('purchase_value'); ?></p>
                                            <div class="progress" style="height: 10px; margin-bottom: 15px; border-radius: 5px;">
                                                <div class="progress-bar progress-bar-warning" role="progressbar" style="width: <?php echo ($client_score['purchase_value']/25*100); ?>%; background-color: #ff9800;">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="bold"><?php echo $client_score['purchase_value']; ?>/25</span>
                                            </div>
                                        </div>

                                        <div class="mtop20">
                                            <p style="font-size: 14px; margin-bottom: 5px;"><?php echo _l('loyalty'); ?></p>
                                            <div class="progress" style="height: 10px; margin-bottom: 15px; border-radius: 5px;">
                                                <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo ($client_score['loyalty']/25*100); ?>%; background-color: #f44336;">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="bold"><?php echo $client_score['loyalty']; ?>/25</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mtop20">
                                    <div class="col-md-12">
                                        <div class="alert alert-info" style="border-radius: 5px; margin-bottom: 0;">
                                            <p><strong><?php echo _l('client_categorization_info'); ?></strong></p>
                                            <p><?php echo _l('client_categorization_help'); ?></p>
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

    <div class="row">
        <!-- Last 30 Days Activity -->
        <div class="col-md-6">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h4 class="no-margin"><?php echo _l('last_30_days_activity'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div style="height: 250px;">
                        <canvas id="last30DaysChart"></canvas>
                    </div>

                    <div class="row mtop20">
                        <div class="col-md-4">
                            <div class="text-center" style="background: #f9f9f9; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold" style="color: #03a9f4;"><?php echo $invoices_30_days['count']; ?></h3>
                                <p><?php echo _l('invoices'); ?></p>
                                <span class="text-muted"><?php echo app_format_money($invoices_30_days['amount'], $base_currency); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center" style="background: #f9f9f9; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold" style="color: #8bc34a;"><?php echo $estimates_30_days['count']; ?></h3>
                                <p><?php echo _l('estimates'); ?></p>
                                <span class="text-muted"><?php echo app_format_money($estimates_30_days['amount'], $base_currency); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center" style="background: #f9f9f9; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold" style="color: #ff9800;"><?php echo $proposals_30_days['count']; ?></h3>
                                <p><?php echo _l('proposals'); ?></p>
                                <span class="text-muted"><?php echo app_format_money($proposals_30_days['amount'], $base_currency); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase History Summary -->
        <div class="col-md-6">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h4 class="no-margin"><?php echo _l('purchase_history_summary'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div style="height: 250px;">
                        <canvas id="purchaseHistoryChart"></canvas>
                    </div>

                    <div class="row mtop20">
                        <div class="col-md-12">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h4 class="bold" style="color: #03a9f4;"><?php echo app_format_money($total_invoiced, $base_currency); ?></h4>
                                        <p><?php echo _l('total_invoiced'); ?></p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="bold" style="color: #8bc34a;"><?php echo app_format_money($total_paid, $base_currency); ?></h4>
                                        <p><?php echo _l('total_paid'); ?></p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="bold" style="color: #ff9800;"><?php echo app_format_money($total_credits, $base_currency); ?></h4>
                                        <p><?php echo _l('credits_used'); ?></p>
                                    </div>
                                </div>
                                <div class="row mtop10">
                                    <div class="col-md-12">
                                        <?php
                                        $percent_paid = 0;
                                        if($total_invoiced > 0) {
                                            $percent_paid = ($total_paid / $total_invoiced) * 100;
                                        }
                                        ?>
                                        <div class="progress" style="height: 10px; margin-bottom: 5px; border-radius: 5px;">
                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent_paid; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent_paid; ?>%; background-color: #8bc34a;">
                                            </div>
                                        </div>
                                        <p class="text-center"><?php echo _l('percent_paid'); ?>: <span class="bold"><?php echo round($percent_paid, 2); ?>%</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Purchased Items -->
        <div class="col-md-6">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h4 class="no-margin"><?php echo _l('top_purchased_items'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <?php if(count($top_purchased_items) > 0) { ?>
                        <div style="height: 250px;">
                            <canvas id="topItemsChart"></canvas>
                        </div>
                        <div class="table-responsive mtop20">
                            <table class="table table-striped" style="border-radius: 5px; overflow: hidden;">
                                <thead>
                                <tr>
                                    <th><?php echo _l('item'); ?></th>
                                    <th><?php echo _l('quantity'); ?></th>
                                    <th><?php echo _l('amount'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($top_purchased_items as $item) { ?>
                                    <tr>
                                        <td><?php echo $item['description']; ?></td>
                                        <td><?php echo $item['total_quantity']; ?></td>
                                        <td><?php echo app_format_money($item['total_amount'], $base_currency); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center" style="padding: 30px;">
                            <i class="fa fa-search" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mtop20"><?php echo _l('no_data_available'); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-md-6">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h4 class="no-margin"><?php echo _l('recent_payments'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <?php if(count($recent_payments) > 0) { ?>
                        <div style="height: 250px;">
                            <canvas id="recentPaymentsChart"></canvas>
                        </div>
                        <div class="table-responsive mtop20">
                            <table class="table table-striped" style="border-radius: 5px; overflow: hidden;">
                                <thead>
                                <tr>
                                    <th><?php echo _l('date'); ?></th>
                                    <th><?php echo _l('amount'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($recent_payments as $payment) { ?>
                                    <tr>
                                        <td><?php echo _d($payment['date']); ?></td>
                                        <td><?php echo app_format_money($payment['amount'], $base_currency); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center" style="padding: 30px;">
                            <i class="fa fa-credit-card" style="font-size: 48px; color: #ccc;"></i>
                            <p class="mtop20"><?php echo _l('no_data_available'); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Future Purchase Predictions -->
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <h4 class="no-margin"><?php echo _l('future_purchase_predictions'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div class="alert alert-info" style="border-radius: 5px;">
                        <p><?php echo _l('future_purchase_predictions_help'); ?></p>
                    </div>

                    <div style="height: 250px;">
                        <canvas id="predictionsChart"></canvas>
                    </div>

                    <div class="row mtop20">
                        <div class="col-md-4">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold text-center" style="color: #03a9f4;"><?php echo date('F Y', strtotime('+1 month')); ?></h3>
                                <div class="row mtop15">
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_invoice_count'); ?></h4>
                                        <span class="bold" style="font-size: 24px;">2</span>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_amount'); ?></h4>
                                        <span class="bold" style="font-size: 18px;"><?php echo app_format_money(5000, $base_currency); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold text-center" style="color: #8bc34a;"><?php echo date('F Y', strtotime('+2 month')); ?></h3>
                                <div class="row mtop15">
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_invoice_count'); ?></h4>
                                        <span class="bold" style="font-size: 24px;">2</span>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_amount'); ?></h4>
                                        <span class="bold" style="font-size: 18px;"><?php echo app_format_money(5000, $base_currency); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h3 class="bold text-center" style="color: #ff9800;"><?php echo date('F Y', strtotime('+3 month')); ?></h3>
                                <div class="row mtop15">
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_invoice_count'); ?></h4>
                                        <span class="bold" style="font-size: 24px;">2</span>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h4><?php echo _l('predicted_amount'); ?></h4>
                                        <span class="bold" style="font-size: 18px;"><?php echo app_format_money(5000, $base_currency); ?></span>
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

<script>
    $(function() {
        // Client Score Chart
        var clientScoreCtx = document.getElementById('clientScoreChart').getContext('2d');
        var clientScoreChart = new Chart(clientScoreCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?php echo $client_score['total']; ?>, <?php echo 100 - $client_score['total']; ?>],
                    backgroundColor: [
                        '#03a9f4',
                        '#f5f5f5'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                cutoutPercentage: 75,
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: false
                },
                animation: {
                    animateScale: false,
                    animateRotate: true,
                    duration: 800
                }
            }
        });

        // Last 30 Days Activity Chart
        var last30DaysCtx = document.getElementById('last30DaysChart').getContext('2d');
        var last30DaysChart = new Chart(last30DaysCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . _l('invoices') . "', '" . _l('estimates') . "', '" . _l('proposals') . "'"; ?>],
                datasets: [{
                    label: '<?php echo _l('count'); ?>',
                    data: [
                        <?php echo $invoices_30_days['count']; ?>,
                        <?php echo $estimates_30_days['count']; ?>,
                        <?php echo $proposals_30_days['count']; ?>
                    ],
                    backgroundColor: [
                        '#03a9f4',
                        '#8bc34a',
                        '#ff9800'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });

        // Purchase History Chart
        var purchaseHistoryCtx = document.getElementById('purchaseHistoryChart').getContext('2d');
        var purchaseHistoryChart = new Chart(purchaseHistoryCtx, {
            type: 'pie',
            data: {
                labels: ['<?php echo _l('total_paid'); ?>', '<?php echo _l('unpaid'); ?>', '<?php echo _l('credits_used'); ?>'],
                datasets: [{
                    data: [
                        <?php echo $total_paid; ?>,
                        <?php echo $total_invoiced - $total_paid; ?>,
                        <?php echo $total_credits; ?>
                    ],
                    backgroundColor: [
                        '#8bc34a',
                        '#f44336',
                        '#ff9800'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });

        <?php if(count($top_purchased_items) > 0) { ?>
        // Top Items Chart
        var topItemsCtx = document.getElementById('topItemsChart').getContext('2d');
        var topItemsChart = new Chart(topItemsCtx, {
            type: 'horizontalBar',
            data: {
                labels: [
                    <?php
                    $items_labels = [];
                    $items_data = [];
                    $items_colors = [];
                    $colors = ['#03a9f4', '#8bc34a', '#ff9800', '#f44336', '#9c27b0'];
                    foreach($top_purchased_items as $key => $item) {
                        $items_labels[] = "'" . str_replace("'", "\'", $item['description']) . "'";
                        $items_data[] = $item['total_quantity'];
                        $items_colors[] = $colors[$key % count($colors)];
                    }
                    echo implode(', ', $items_labels);
                    ?>
                ],
                datasets: [{
                    label: '<?php echo _l('quantity'); ?>',
                    data: [<?php echo implode(', ', $items_data); ?>],
                    backgroundColor: [<?php echo "'" . implode("', '", $items_colors) . "'"; ?>],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                },
                legend: {
                    display: false
                }
            }
        });
        <?php } ?>

        <?php if(count($recent_payments) > 0) { ?>
        // Recent Payments Chart
        var recentPaymentsCtx = document.getElementById('recentPaymentsChart').getContext('2d');
        var recentPaymentsChart = new Chart(recentPaymentsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                    $payment_labels = [];
                    $payment_data = [];
                    foreach(array_reverse(array_slice($recent_payments, 0, 7)) as $payment) {
                        $payment_labels[] = "'" . _d($payment['date']) . "'";
                        $payment_data[] = $payment['amount'];
                    }
                    echo implode(', ', $payment_labels);
                    ?>
                ],
                datasets: [{
                    label: '<?php echo _l('amount'); ?>',
                    data: [<?php echo implode(', ', $payment_data); ?>],
                    backgroundColor: 'rgba(3, 169, 244, 0.2)',
                    borderColor: '#03a9f4',
                    borderWidth: 2,
                    pointBackgroundColor: '#03a9f4',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }]
                }
            }
        });
        <?php } ?>

        // Predictions Chart
        var predictionsCtx = document.getElementById('predictionsChart').getContext('2d');
        var predictionsChart = new Chart(predictionsCtx, {
            type: 'bar',
            data: {
                labels: [
                    '<?php echo date('F Y', strtotime('+1 month')); ?>',
                    '<?php echo date('F Y', strtotime('+2 month')); ?>',
                    '<?php echo date('F Y', strtotime('+3 month')); ?>'
                ],
                datasets: [{
                    label: '<?php echo _l('predicted_amount'); ?>',
                    data: [5000, 5000, 5000],
                    backgroundColor: [
                        '#03a9f4',
                        '#8bc34a',
                        '#ff9800'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }]
                }
            }
        });
    });
</script>
