<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-12">
    <div class="panel_s">
        <div class="panel-body">
            
            <?php
                $CI = & get_instance();
                $base_currency = get_base_currency();
                
                // --- Date Filter Logic ---
                $selected_period = $this->input->get('stats_period');
                if(!$selected_period) $selected_period = 'all_time';

                $date_where = [];
                $period_text = 'All Time';

                if($selected_period == 'this_month'){
                    $date_where['date >='] = date('Y-m-01');
                    $date_where['date <='] = date('Y-m-t');
                    $period_text = 'This Month';
                } elseif($selected_period == 'last_month'){
                    $date_where['date >='] = date('Y-m-01', strtotime('last month'));
                    $date_where['date <='] = date('Y-m-t', strtotime('last month'));
                    $period_text = 'Last Month';
                } elseif($selected_period == 'this_year'){
                    $date_where['date >='] = date('Y-01-01');
                    $date_where['date <='] = date('Y-12-31');
                    $period_text = 'This Year';
                } elseif($selected_period == 'last_year'){
                    $date_where['date >='] = date('Y-01-01', strtotime('last year'));
                    $date_where['date <='] = date('Y-12-31', strtotime('last year'));
                    $period_text = 'Last Year';
                }

                // Permissions
                $where_own = [];
                if (!has_permission('estimates', '', 'view')) {
                    $where_own['addedfrom'] = get_staff_user_id();
                }
                
                $final_where = array_merge($where_own, $date_where);
            ?>

            <div class="row">
                <div class="col-md-6">
                    <h4 class="no-margin pull-left">
                        <?php echo _l('estimates'); ?> Analytics
                        <small class="text-success"> &bull; <?php echo $period_text; ?></small>
                    </h4>
                </div>
                <div class="col-md-6 text-right">
                    <form method="get" action="" class="form-inline display-inline-block mright10" style="vertical-align: top;">
                        <?php foreach($_GET as $key => $val){ if($key == 'stats_period') continue; echo '<input type="hidden" name="'.$key.'" value="'.$val.'">'; } ?>
                        <select name="stats_period" class="selectpicker" data-width="150px" data-style="btn-default btn-xs" onchange="this.form.submit()">
                            <option value="all_time" <?php if($selected_period == 'all_time'){echo 'selected';} ?>>All Time</option>
                            <option value="this_month" <?php if($selected_period == 'this_month'){echo 'selected';} ?>>This Month</option>
                            <option value="last_month" <?php if($selected_period == 'last_month'){echo 'selected';} ?>>Last Month</option>
                            <option value="this_year" <?php if($selected_period == 'this_year'){echo 'selected';} ?>>This Year</option>
                            <option value="last_year" <?php if($selected_period == 'last_year'){echo 'selected';} ?>>Last Year</option>
                        </select>
                    </form>
                    <a href="#" onclick="$('#estimates_analytics').slideToggle(); return false;" class="text-muted"><i class="fa fa-angle-up fa-lg"></i></a>
                </div>
            </div>
            
            <hr class="hr-panel-heading" />

            <div id="estimates_analytics">
                
                <div class="row">
                    <?php
                    $total_period_estimates = total_rows(db_prefix() . 'estimates', $final_where);
                    $status_financials = [];

                    foreach ($estimate_statuses as $status) {
                        $where = array_merge(['status' => $status], $final_where);
                        $count = total_rows(db_prefix() . 'estimates', $where);
                        $percent = ($total_period_estimates > 0) ? ($count / $total_period_estimates) * 100 : 0;
                        
                        // Store money for later
                        $CI->db->select_sum('total');
                        $CI->db->where($where);
                        $sum_query = $CI->db->get(db_prefix() . 'estimates')->row();
                        $status_financials[$status] = $sum_query->total ?? 0;

                        $bar_color = 'progress-bar-default';
                        if($status == 4) $bar_color = 'progress-bar-success';
                        elseif($status == 3) $bar_color = 'progress-bar-danger';
                        elseif($status == 2) $bar_color = 'progress-bar-info';
                    ?>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold no-margin"><?php echo $count; ?></h3>
                            <span class="text-muted mtop5 display-block"><?php echo format_estimate_status($status, '', false); ?></span>
                            <div class="progress no-margin mtop10 progress-bar-mini" style="height:6px;">
                                <div class="progress-bar <?php echo $bar_color; ?>" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <?php if (get_option('show_shipping_on_sales') == 1) { ?>
                <hr class="hr-panel-heading" />

                <?php
                    // 1. Available Retained (Held)
                    $where_held = array_merge($final_where, ['retained' => "0"]);
                    $CI->db->select('(SUM(total)*0.15) as retained_amount');
                    $CI->db->where($where_held);
                    $q_held = $CI->db->get(db_prefix().'estimates')->row();
                    $amount_held = $q_held->retained_amount ?? 0;

                    // 2. Released Retained
                    $where_released = array_merge($final_where, ['retained' => "1"]);
                    $CI->db->select('(SUM(total)*0.15) as retained_amount');
                    $CI->db->where($where_released);
                    $q_released = $CI->db->get(db_prefix().'estimates')->row();
                    $amount_released = $q_released->retained_amount ?? 0;
                ?>
                <div class="row mtop20">
                    <div class="col-md-12"><h4 class="bold no-margin text-uppercase font-medium-xs text-muted">Retained Funds Overview (15%)</h4></div>
                    
                    <div class="col-md-6 mtop10">
                        <div class="panel_s" style="background: #fffcf0; border: 1px solid #f0ad4e;">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-warning bold text-uppercase">Available Retained (Held)</span>
                                        <h3 class="bold no-margin text-warning mtop5"><?php echo app_format_money($amount_held, $base_currency); ?></h3>
                                    </div>
                                    <div class="col-md-4 text-right"><i class="fa fa-lock fa-3x text-warning" style="opacity:0.3;"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mtop10">
                        <div class="panel_s" style="background: #f0fbf0; border: 1px solid #4caf50;">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-success bold text-uppercase">Total Released</span>
                                        <h3 class="bold no-margin text-success mtop5"><?php echo app_format_money($amount_released, $base_currency); ?></h3>
                                    </div>
                                    <div class="col-md-4 text-right"><i class="fa fa-check-circle fa-3x text-success" style="opacity:0.3;"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="hr-panel-heading" />
                <?php } ?>

                <div class="row mtop20">
                    <div class="col-md-8">
                        <p class="text-muted font-medium-xs text-uppercase mbot10">
                            Volume Forecast 
                            <?php if($selected_period == 'all_time' || $selected_period == 'this_year'){ ?>
                                <span class="label label-default mleft5">Confidence: 20%</span>
                            <?php } ?>
                        </p>
                        <div style="height: 300px;"><canvas id="estimatesTrendChart"></canvas></div>
                    </div>
                    <div class="col-md-4">
                         <p class="text-muted font-medium-xs text-uppercase mbot10">Total Pipeline Value</p>
                        <ul class="list-group">
                            <?php 
                            $grand_total = 0;
                            foreach($estimate_statuses as $status){ 
                                $val = $status_financials[$status];
                                $grand_total += $val;
                                if($val == 0) continue; 
                            ?>
                            <li class="list-group-item">
                                <?php echo format_estimate_status($status, '', false); ?>
                                <span class="pull-right"><?php echo app_format_money($val, $base_currency); ?></span>
                            </li>
                            <?php } ?>
                            <li class="list-group-item list-group-item-info">
                                <span class="bold">Total</span>
                                <span class="pull-right bold"><?php echo app_format_money($grand_total, $base_currency); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="_filters _hidden_inputs hidden">
                <?php
                if(isset($estimates_sale_agents)){ foreach($estimates_sale_agents as $agent){ echo form_hidden('sale_agent_'.$agent['sale_agent']); } }
                if(isset($estimate_statuses)){ foreach($estimate_statuses as $_status){ $val = ''; if($_status == $this->input->get('status')){ $val = $_status; } echo form_hidden('estimates_'.$_status,$val); } }
                if(isset($estimates_years)){ foreach($estimates_years as $year){ echo form_hidden('year_'.$year['year'],$year['year']); } }
                echo form_hidden('not_sent',$this->input->get('filter'));
                echo form_hidden('project_id');
                echo form_hidden('invoiced');
                echo form_hidden('not_invoiced');
                ?>
            </div>
        </div>
    </div>
</div>

<?php
    // 1. Fetch Data
    $CI->db->select('date');
    if (!empty($final_where)) { $CI->db->where($final_where); }
    $CI->db->order_by('date', 'ASC');
    $all_dates = $CI->db->get(db_prefix().'estimates')->result_array();

    // 2. Aggregate
    $temp_chart_data = [];
    foreach($all_dates as $row){
        if(!$row['date']) continue;
        // Grouping: Use Days for short periods, Months for long periods
        $key = ($selected_period == 'this_month' || $selected_period == 'last_month') 
               ? date('d M', strtotime($row['date'])) 
               : date('Y-m', strtotime($row['date']));
        
        if(!isset($temp_chart_data[$key])) $temp_chart_data[$key] = 0;
        $temp_chart_data[$key]++;
    }
    
    $hist_labels = array_keys($temp_chart_data);
    $hist_values = array_values($temp_chart_data);
    $n = count($hist_values);

    // 3. Regression Logic
    $forecast_values = [];
    $upper_bound = [];
    $lower_bound = [];
    $future_labels = [];
    
    // Only forecast if not viewing "This Month" (Data too granular/noisy for regression)
    $enable_forecast = ($n > 1 && $selected_period != 'this_month' && $selected_period != 'last_month');
    
    if ($enable_forecast) {
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $hist_values[$i];
            $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumXX += ($x * $x);
        }
        
        $m = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumXX) - ($sumX * $sumX));
        $b = ($sumY - ($m * $sumX)) / $n;
        
        $forecast_months = 6;
        $history_pad = $hist_values; 
        
        for ($i = 0; $i < ($n + $forecast_months); $i++) {
            if($i < $n) {
                // Historical
                $future_labels[] = $hist_labels[$i];
                $forecast_values[] = null;
                $upper_bound[] = null;
                $lower_bound[] = null;
            } else {
                // Future
                $future_labels[] = "Forecast +" . ($i - $n + 1); 
                $history_pad[] = null;
                
                $predicted_y = ($m * $i) + $b;
                $predicted_y = max(0, $predicted_y);
                
                $forecast_values[] = $predicted_y;
                $upper_bound[] = $predicted_y * 1.2; // 20% Upper
                $lower_bound[] = $predicted_y * 0.8; // 20% Lower
            }
        }
    } else {
        $history_pad = $hist_values;
        $future_labels = $hist_labels;
    }
?>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var ctx = document.getElementById('estimatesTrendChart');
        if(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($future_labels); ?>,
                    datasets: [
                        {
                            label: '<?php echo _l('estimates'); ?> (Actual)',
                            backgroundColor: '#1e293b',
                            borderColor: '#1e293b',
                            data: <?php echo json_encode($history_pad); ?>,
                            tension: 0, 
                            fill: false,
                            pointRadius: 4,
                            pointBackgroundColor: '#1e293b'
                        },
                        <?php if($enable_forecast): ?>
                        {
                            label: 'Forecast Trend',
                            borderColor: '#b91c1c', // Dark Red
                            borderDash: [5, 5],
                            data: <?php echo json_encode($forecast_values); ?>,
                            fill: false,
                            pointRadius: 0,
                            borderWidth: 2
                        },
                        {
                            label: 'Upper Confidence (20%)',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)', // Dark Grey Wash
                            borderColor: 'transparent',
                            data: <?php echo json_encode($upper_bound); ?>,
                            fill: '+1', 
                            pointRadius: 0
                        },
                        {
                            label: 'Lower Confidence (20%)',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)',
                            borderColor: 'transparent',
                            data: <?php echo json_encode($lower_bound); ?>,
                            fill: '-1', 
                            pointRadius: 0
                        }
                        <?php endif; ?>
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: { mode: 'index', intersect: false },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>