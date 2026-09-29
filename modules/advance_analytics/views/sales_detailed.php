<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Proposal Analytics -->
<div class="row mtop20">
    <div class="col-md-12">
        <h4 class="text-success bold"><i class="fa fa-file-powerpoint-o"></i> Proposal Analytics</h4>
        <hr class="hr-panel-heading" />
    </div>
    <div class="col-md-12">
        <div class="row">
        <?php foreach($proposal_analytics['statuses'] as $status){ 
            $count = $proposal_analytics['status_counts'][$status] ?? 0;
            $total = $proposal_analytics['total'] > 0 ? $proposal_analytics['total'] : 1;
            $percent = ($count / $total) * 100;
            
            $bar_color = 'progress-bar-default';
            if($status == 6) $bar_color = 'progress-bar-success'; 
            elseif($status == 5) $bar_color = 'progress-bar-danger'; 
            elseif(in_array($status, [2,3,4])) $bar_color = 'progress-bar-info';
        ?>
            <div class="col-md-2 col-xs-6 border-right">
                <h3 class="bold no-margin"><?php echo $count; ?></h3>
                <span class="text-muted mtop5 display-block">
                    <?php echo format_proposal_status($status, '', false); ?>
                    <span class="pull-right text-muted" style="font-size: 11px;"><?php echo round($percent, 1); ?>%</span>
                </span>
                <div class="progress no-margin mtop10 progress-bar-mini" style="height:8px;">
                    <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%;" data-toggle="tooltip" title="<?php echo round($percent, 1).'%'; ?>"></div>
                </div>
            </div>
        <?php } ?>
        </div>
        
        <div class="row mtop20">
            <div class="col-md-8">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Volume Forecast <span class="label label-default mleft5">Confidence: 20%</span></p>
                 <div style="height: 300px;"><canvas id="proposalAnalyticsChart"></canvas></div>
            </div>
            <div class="col-md-4">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Pipeline Value</p>
                 <ul class="list-group">
                 <?php $grand_total = 0; foreach($proposal_analytics['statuses'] as $status){ 
                     $val = $proposal_analytics['status_financials'][$status] ?? 0;
                     $grand_total += $val;
                     if($val == 0) continue;
                 ?>
                    <li class="list-group-item">
                        <?php echo format_proposal_status($status, '', false); ?>
                        <span class="pull-right"><?php echo app_format_money($val, get_base_currency()); ?></span>
                    </li>
                 <?php } ?>
                    <li class="list-group-item list-group-item-success">
                        <span class="bold">Total</span>
                        <span class="pull-right bold"><?php echo app_format_money($grand_total, get_base_currency()); ?></span>
                    </li>
                 </ul>
            </div>
        </div>
    </div>
</div>

<!-- Estimate Analytics -->
<div class="row mtop20">
    <div class="col-md-12">
        <h4 class="text-warning bold"><i class="fa fa-calculator"></i> Estimate Analytics</h4>
        <hr class="hr-panel-heading" />
    </div>
     <div class="col-md-12">
        <div class="row">
        <?php foreach($estimate_analytics['statuses'] as $status){ 
            $count = $estimate_analytics['status_counts'][$status] ?? 0;
            $total = $estimate_analytics['total'] > 0 ? $estimate_analytics['total'] : 1;
            $percent = ($count / $total) * 100;
            
            $bar_color = 'progress-bar-default';
            if($status == 4) $bar_color = 'progress-bar-success';
            elseif($status == 3) $bar_color = 'progress-bar-danger';
            elseif($status == 2) $bar_color = 'progress-bar-info';
        ?>
            <div class="col-md-2 col-xs-6 border-right">
                <h3 class="bold no-margin"><?php echo $count; ?></h3>
                <span class="text-muted mtop5 display-block">
                    <?php echo format_estimate_status($status, '', false); ?>
                    <span class="pull-right text-muted" style="font-size: 11px;"><?php echo round($percent, 1); ?>%</span>
                </span>
                <div class="progress no-margin mtop10 progress-bar-mini" style="height:8px;">
                    <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%;" data-toggle="tooltip" title="<?php echo round($percent, 1).'%'; ?>"></div>
                </div>
            </div>
        <?php } ?>
        </div>
        
        <div class="row mtop20">
            <div class="col-md-8">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Volume Forecast</p>
                 <div style="height: 300px;"><canvas id="estimateAnalyticsChart"></canvas></div>
            </div>
            <div class="col-md-4">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Pipeline with Retained Funds</p>
                 <div class="row">
                    <div class="col-md-6">
                        <div class="panel_s" style="background:#fffcf0; border:1px solid #f0ad4e; padding:10px;">
                            <span class="text-warning bold text-uppercase" style="font-size:10px;">Retained (Held)</span>
                            <h4 class="bold no-margin text-warning"><?php echo app_format_money($estimate_analytics['retained_held'], get_base_currency()); ?></h4>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="panel_s" style="background:#f0fbf0; border:1px solid #4caf50; padding:10px;">
                             <span class="text-success bold text-uppercase" style="font-size:10px;">Released</span>
                            <h4 class="bold no-margin text-success"><?php echo app_format_money($estimate_analytics['retained_released'], get_base_currency()); ?></h4>
                        </div>
                    </div>
                 </div>
                 <ul class="list-group mtop10">
                 <?php $grand_total = 0; foreach($estimate_analytics['statuses'] as $status){ 
                     $val = $estimate_analytics['status_financials'][$status] ?? 0;
                     $grand_total += $val;
                     if($val == 0) continue;
                 ?>
                    <li class="list-group-item">
                        <?php echo format_estimate_status($status, '', false); ?>
                        <span class="pull-right"><?php echo app_format_money($val, get_base_currency()); ?></span>
                    </li>
                 <?php } ?>
                    <li class="list-group-item list-group-item-success">
                        <span class="bold">Total</span>
                        <span class="pull-right bold"><?php echo app_format_money($grand_total, get_base_currency()); ?></span>
                    </li>
                 </ul>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Analytics -->
<div class="row mtop20">
    <div class="col-md-12">
        <h4 class="text-info bold"><i class="fa fa-file-text"></i> Invoice Analytics</h4>
        <hr class="hr-panel-heading" />
    </div>
    <div class="col-md-12">
        <div class="row">
        <?php foreach($invoice_analytics['statuses'] as $status){ 
            $count = $invoice_analytics['status_counts'][$status] ?? 0;
            $total = $invoice_analytics['total'] > 0 ? $invoice_analytics['total'] : 1;
            $percent = ($count / $total) * 100;
            
             $bar_color = 'progress-bar-default';
            if($status == 2) $bar_color = 'progress-bar-success';
            elseif($status == 1 || $status == 4) $bar_color = 'progress-bar-danger';
            elseif($status == 3) $bar_color = 'progress-bar-warning';
        ?>
            <div class="col-md-2 col-xs-6 border-right">
                <h3 class="bold no-margin"><?php echo $count; ?></h3>
                <span class="text-muted mtop5 display-block">
                    <?php echo format_invoice_status($status, '', false); ?>
                    <span class="pull-right text-muted" style="font-size: 11px;"><?php echo round($percent, 1); ?>%</span>
                </span>
                <div class="progress no-margin mtop10 progress-bar-mini" style="height:8px;">
                    <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%;" data-toggle="tooltip" title="<?php echo round($percent, 1).'%'; ?>"></div>
                </div>
            </div>
        <?php } ?>
        </div>
        
         <div class="row mtop20">
            <div class="col-md-8">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Invoicing Forecast</p>
                 <div style="height: 300px;"><canvas id="invoiceAnalyticsChart"></canvas></div>
            </div>
            <div class="col-md-4">
                 <p class="text-muted font-medium-xs text-uppercase mbot10">Financial Overview</p>
                 <?php
                    $paid_val = $invoice_analytics['status_financials'][2] ?? 0;
                    $due_val = ($invoice_analytics['status_financials'][1] ?? 0) + ($invoice_analytics['status_financials'][3] ?? 0) + ($invoice_analytics['status_financials'][4] ?? 0);
                 ?>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="panel_s" style="background:#f0fbf0; border:1px solid #4caf50; padding:10px;">
                            <span class="text-success bold text-uppercase" style="font-size:10px;">Collected</span>
                            <h4 class="bold no-margin text-success"><?php echo app_format_money($paid_val, get_base_currency()); ?></h4>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="panel_s" style="background:#fffcf0; border:1px solid #f0ad4e; padding:10px;">
                             <span class="text-warning bold text-uppercase" style="font-size:10px;">Outstanding</span>
                            <h4 class="bold no-margin text-warning"><?php echo app_format_money($due_val, get_base_currency()); ?></h4>
                        </div>
                    </div>
                 </div>
                 <ul class="list-group mtop10">
                 <?php $grand_total = 0; foreach($invoice_analytics['statuses'] as $status){ 
                      $val = $invoice_analytics['status_financials'][$status] ?? 0;
                     $grand_total += $val;
                     if($val == 0) continue;
                 ?>
                    <li class="list-group-item">
                        <?php echo format_invoice_status($status, '', false); ?>
                        <span class="pull-right"><?php echo app_format_money($val, get_base_currency()); ?></span>
                    </li>
                 <?php } ?>
                    <li class="list-group-item list-group-item-info">
                        <span class="bold">Total</span>
                        <span class="pull-right bold"><?php echo app_format_money($grand_total, get_base_currency()); ?></span>
                    </li>
                 </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        // Helper
        function renderTrendChart(canvasId, data, label) {
            var ctx = document.getElementById(canvasId);
            if(ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: label + ' (Actual)',
                                backgroundColor: '#1e293b',
                                borderColor: '#1e293b',
                                data: data.history,
                                tension: 0, fill: false,
                                pointRadius: 4, pointBackgroundColor: '#1e293b'
                            },
                            {
                                label: 'Forecast',
                                borderColor: '#b91c1c', borderDash: [5, 5],
                                data: data.forecast,
                                fill: false, pointRadius: 0, borderWidth: 2
                            },
                             {
                                label: 'Upper Conf.',
                                backgroundColor: 'rgba(30, 41, 59, 0.15)', borderColor: 'transparent',
                                data: data.upper,
                                fill: '+1', pointRadius: 0
                            },
                            {
                                label: 'Lower Conf.',
                                backgroundColor: 'rgba(30, 41, 59, 0.15)', borderColor: 'transparent',
                                data: data.lower,
                                fill: '-1', pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                         tooltips: { mode: 'index', intersect: false },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }
        
        renderTrendChart('proposalAnalyticsChart', <?php echo json_encode($proposal_analytics['chart_data']); ?>, 'Proposals');
        renderTrendChart('estimateAnalyticsChart', <?php echo json_encode($estimate_analytics['chart_data']); ?>, 'Estimates');
        renderTrendChart('invoiceAnalyticsChart', <?php echo json_encode($invoice_analytics['chart_data']); ?>, 'Invoices');
    });
</script>
