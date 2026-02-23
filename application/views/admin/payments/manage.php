<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        
        <div class="panel_s">
            <div class="panel-body">
                
                <?php
                    $CI = & get_instance();
                    $base_currency = get_base_currency();

                    // --- 1. DETERMINE DATE RANGE ---
                    $selected_period = $this->input->get('stats_period');
                    if(!$selected_period) $selected_period = 'all_time';

                    $date_where = [];
                    $period_text = 'All Time';
                    
                    // Default Manual Inputs
                    $manual_from = $this->input->get('payment_from');
                    $manual_to   = $this->input->get('payment_to');

                    // If Dropdown is used, override manual inputs
                    if($selected_period == 'this_month'){
                        $manual_from = date('Y-m-01');
                        $manual_to   = date('Y-m-t');
                        $period_text = 'This Month';
                    } elseif($selected_period == 'last_month'){
                        $manual_from = date('Y-m-01', strtotime('last month'));
                        $manual_to   = date('Y-m-t', strtotime('last month'));
                        $period_text = 'Last Month';
                    } elseif($selected_period == 'this_year'){
                        $manual_from = date('Y-01-01');
                        $manual_to   = date('Y-12-31');
                        $period_text = 'This Year';
                    } elseif($selected_period == 'last_year'){
                        $manual_from = date('Y-01-01', strtotime('last year'));
                        $manual_to   = date('Y-12-31', strtotime('last year'));
                        $period_text = 'Last Year';
                    } elseif($selected_period == 'last_30_days'){
                        $manual_from = date('Y-m-d', strtotime('-30 days'));
                        $manual_to   = date('Y-m-d');
                        $period_text = 'Last 30 Days';
                    }

                    // --- 2. BUILD QUERY ---
                    $where = [];
                    if($manual_from){ $where['date >='] = $manual_from; }
                    if($manual_to){   $where['date <='] = $manual_to; }
                    
                    $f_amt = $this->input->get('amount');
                    if($f_amt){ $where['amount'] = $f_amt; }

                    // --- QUERY A: TOTALS ---
                    $CI->db->select('SUM(amount) as total_money, COUNT(id) as total_trans');
                    if(!empty($where)){ $CI->db->where($where); }
                    $totals = $CI->db->get(db_prefix().'invoicepaymentrecords')->row();
                    
                    $total_money = $totals->total_money ?? 0;
                    $total_count = $totals->total_trans ?? 0;
                    $avg_payment = ($total_count > 0) ? ($total_money / $total_count) : 0;

                    // --- QUERY B: BY PAYMENT MODE ---
                    $CI->db->order_by('name','asc');
                    $modes_query = $CI->db->get(db_prefix().'payment_modes')->result();
                    $mode_names = [];
                    foreach($modes_query as $m){ $mode_names[$m->id] = $m->name; }

                    $CI->db->select('paymentmode, SUM(amount) as amount, COUNT(id) as count');
                    if(!empty($where)){ $CI->db->where($where); }
                    $CI->db->group_by('paymentmode');
                    $by_mode = $CI->db->get(db_prefix().'invoicepaymentrecords')->result();
                ?>

                <div class="row">
                    <div class="col-md-5">
                         <h4 class="no-margin">
                            <?php echo _l('payments'); ?> Dashboard
                            <small class="text-success"> &bull; <?php echo $period_text; ?></small>
                        </h4>
                    </div>
                    <div class="col-md-7 text-right">
                        <form method="get" action="" class="form-inline display-inline-block mright10" style="vertical-align: top;">
                            <?php if($f_amt){ echo '<input type="hidden" name="amount" value="'.$f_amt.'">'; } ?>
                            <label class="mright5 text-muted small">Quick Filter:</label>
                            <select name="stats_period" class="selectpicker" data-width="150px" data-style="btn-default btn-xs" onchange="this.form.submit()">
                                <option value="all_time" <?php if($selected_period == 'all_time'){echo 'selected';} ?>>All Time</option>
                                <option value="this_month" <?php if($selected_period == 'this_month'){echo 'selected';} ?>>This Month</option>
                                <option value="last_month" <?php if($selected_period == 'last_month'){echo 'selected';} ?>>Last Month</option>
                                <option value="this_year" <?php if($selected_period == 'this_year'){echo 'selected';} ?>>This Year</option>
                                <option value="last_year" <?php if($selected_period == 'last_year'){echo 'selected';} ?>>Last Year</option>
                                <option value="last_30_days" <?php if($selected_period == 'last_30_days'){echo 'selected';} ?>>Last 30 Days</option>
                            </select>
                        </form>
                        <a href="#" onclick="$('#payment_analytics').slideToggle(); return false;" class="text-muted" data-toggle="tooltip" title="Toggle Analytics">
                            <i class="fa fa-angle-up fa-lg"></i>
                        </a>
                    </div>
                </div>
                
                <hr class="hr-panel-heading" />

                <div id="payment_analytics">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="panel_s" style="border:1px solid #dce1ef; background: #f9fafc;">
                                <div class="panel-body text-center">
                                    <h3 class="bold text-success no-margin"><?php echo app_format_money($total_money, $base_currency); ?></h3>
                                    <span class="text-muted text-uppercase mtop5 display-block">Total Received</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel_s" style="border:1px solid #dce1ef; background: #f9fafc;">
                                <div class="panel-body text-center">
                                    <h3 class="bold text-info no-margin"><?php echo $total_count; ?></h3>
                                    <span class="text-muted text-uppercase mtop5 display-block">Transactions</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel_s" style="border:1px solid #dce1ef; background: #f9fafc;">
                                <div class="panel-body text-center">
                                    <h3 class="bold text-warning no-margin"><?php echo app_format_money($avg_payment, $base_currency); ?></h3>
                                    <span class="text-muted text-uppercase mtop5 display-block">Avg. Transaction</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mtop10">
                        <div class="col-md-8">
                             <p class="text-muted font-medium-xs text-uppercase mbot10">
                                 Collection Trend (<?php echo $period_text; ?>)
                                 <?php if($selected_period == 'all_time' || $selected_period == 'this_year'){ ?>
                                    <span class="label label-default mleft5">Forecast + Confidence Interval (20%)</span>
                                 <?php } ?>
                             </p>
                             <div style="height: 250px;">
                                 <canvas id="paymentTrendChart"></canvas>
                             </div>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted font-medium-xs text-uppercase mbot10">By Payment Mode</p>
                            <ul class="list-group">
                                <?php 
                                foreach($by_mode as $row){ 
                                    $name = $mode_names[$row->paymentmode] ?? 'Unknown/Deleted';
                                    $pct = ($total_money > 0) ? ($row->amount / $total_money) * 100 : 0;
                                ?>
                                <li class="list-group-item">
                                    <span class="bold"><?php echo $name; ?></span>
                                    <small class="text-muted">(<?php echo $row->count; ?>)</small>
                                    <div class="pull-right text-right">
                                        <span class="bold"><?php echo app_format_money($row->amount, $base_currency); ?></span>
                                        <div class="progress no-margin" style="height: 2px; width: 50px; float:right; clear:both; margin-top:3px;">
                                            <div class="progress-bar progress-bar-info" style="width: <?php echo $pct; ?>%;"></div>
                                        </div>
                                    </div>
                                </li>
                                <?php } ?>
                                <?php if(empty($by_mode)){ echo '<li class="list-group-item text-center text-muted">No data found for this period</li>'; } ?>
                            </ul>
                        </div>
                    </div>
                </div> </div>
        </div>
        <div class="panel_s">
            <div class="panel-body">
                <?php if(has_permission('payments','','create')){ ?>
                  <a href="<?php echo admin_url('payments/payment/-1'); ?>" class="btn btn-success">
                    <i class="fa fa-plus-square"></i> <?php echo _l('payment'); ?></a>
                     <a href="<?php echo admin_url('payments/all_payment'); ?>" class="btn btn-success">
                     <?php echo _l('All payment'); ?></a>
                  <?php } ?>
                  <br><br>

                  <form method="get" action="<?php echo admin_url('payments'); ?>" id="payment_filter_form">
                      <div class="row">
                         <div class="col-md-3">
                            <div class="form-group">
                               <label for="payment_from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                               <div class="input-group date">
                                  <input type="text" class="form-control datepicker" id="payment_from" name="payment_from" value="<?php echo $manual_from; ?>" autocomplete="off">
                                  <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                               </div>
                            </div>
                         </div>
                         <div class="col-md-3">
                            <div class="form-group">
                               <label for="payment_to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                               <div class="input-group date">
                                  <input type="text" class="form-control datepicker" id="payment_to" name="payment_to" value="<?php echo $manual_to; ?>" autocomplete="off">
                                  <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                               </div>
                            </div>
                         </div>
                         <div class="col-md-4">
                            <div class="form-group">
                               <label for="amount" class="control-label"><?php echo _l('amount'); ?></label>
                               <input type="number" class="form-control" id="amount" name="amount" value="<?php echo $f_amt; ?>">
                            </div>
                         </div>
                         <div class="col-md-2 mtop25">
                            <button type="submit" class="btn btn-info"><?php echo _l('filter'); ?></button>
                            <a href="<?php echo admin_url('payments'); ?>" class="btn btn-default">Reset</a>
                         </div>
                      </div>
                  </form>

                  <hr class="hr-panel-heading" />
                <?php $this->load->view('admin/payments/table_html'); ?>
            </div>
        </div>
    </div>
</div>

<?php
    // 1. Fetch Data
    $CI->db->select('date, amount');
    if(!empty($where)){ $CI->db->where($where); }
    $CI->db->order_by('date','asc');
    $chart_rows = $CI->db->get(db_prefix().'invoicepaymentrecords')->result_array();
    
    $temp_chart = [];
    foreach($chart_rows as $r){
        // Group by Month for large ranges, Day for small ranges
        if($selected_period == 'all_time' || $selected_period == 'last_year' || $selected_period == 'this_year'){
             $d = date('Y-m', strtotime($r['date']));
        } else {
             $d = date('d M', strtotime($r['date']));
        }
        if(!isset($temp_chart[$d])) $temp_chart[$d] = 0;
        $temp_chart[$d] += $r['amount'];
    }
    
    $hist_labels = array_keys($temp_chart);
    $hist_values = array_values($temp_chart);
    $n = count($hist_values);

    // 2. Linear Regression Forecasting
    $forecast_values = [];
    $upper_bound = [];
    $lower_bound = [];
    $future_labels = [];
    
    // Disable forecast for short periods (Daily data is too noisy)
    $enable_forecast = ($n > 1 && $selected_period != 'this_month' && $selected_period != 'last_month' && $selected_period != 'last_30_days');

    if ($enable_forecast) {
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $x = $i; $y = $hist_values[$i];
            $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumXX += ($x * $x);
        }
        
        $m = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumXX) - ($sumX * $sumX));
        $b = ($sumY - ($m * $sumX)) / $n;
        
        $forecast_periods = 6;
        $history_pad = $hist_values; 
        
        for ($i = 0; $i < ($n + $forecast_periods); $i++) {
            if($i < $n) {
                // Historical Data
                $future_labels[] = $hist_labels[$i];
                $forecast_values[] = null; $upper_bound[] = null; $lower_bound[] = null;
            } else {
                // Future Data
                $future_labels[] = "Fcst +" . ($i - $n + 1); 
                $history_pad[] = null;
                
                $predicted_y = max(0, ($m * $i) + $b);
                $forecast_values[] = $predicted_y;
                $upper_bound[] = $predicted_y * 1.2; // +20%
                $lower_bound[] = $predicted_y * 0.8; // -20%
            }
        }
    } else {
        $history_pad = $hist_values;
        $future_labels = $hist_labels;
    }
?>

<?php init_tail(); ?>

<script>
    $(function(){
        // Pre-fill datepicker if empty (Visual only)
        var today = new Date();
        if (!$('input[name="payment_from"]').val()) { $('input[name="payment_from"]').datepicker('setDate', today); }
        if (!$('input[name="payment_to"]').val()) { $('input[name="payment_to"]').datepicker('setDate', today); }

        // Datatable Params
        var fnServerParams = {
            "payment_from": 'input[name="payment_from"]',
            "payment_to": 'input[name="payment_to"]',
            "amount": 'input[name="amount"]'
        };

        initDataTable('.table-payments', admin_url+'payments/table', undefined, undefined, fnServerParams, <?php echo hooks()->apply_filters('payments_table_default_order', json_encode(array(0,'desc'))); ?>);

        // --- CHART JS ---
        var ctx = document.getElementById('paymentTrendChart');
        if(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($future_labels); ?>,
                    datasets: [
                        {
                            label: 'Received',
                            backgroundColor: '#1e293b',
                            borderColor: '#1e293b', // Midnight Blue
                            data: <?php echo json_encode($history_pad); ?>,
                            tension: 0, fill: false,
                            pointRadius: 4, pointBackgroundColor: '#1e293b'
                        },
                        <?php if($enable_forecast): ?>
                        {
                            label: 'Forecast Trend',
                            borderColor: '#b91c1c', // Dark Red
                            borderDash: [5, 5],
                            data: <?php echo json_encode($forecast_values); ?>,
                            fill: false, pointRadius: 0, borderWidth: 2
                        },
                        {
                            label: 'Upper Conf. (20%)',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)', // Dark Wash
                            borderColor: 'transparent',
                            data: <?php echo json_encode($upper_bound); ?>,
                            fill: '+1', pointRadius: 0
                        },
                        {
                            label: 'Lower Conf. (20%)',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)',
                            borderColor: 'transparent',
                            data: <?php echo json_encode($lower_bound); ?>,
                            fill: '-1', pointRadius: 0
                        }
                        <?php endif; ?>
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    tooltips: {
                        mode: 'index', intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) { label += ': '; }
                                label += '<?php echo $base_currency->symbol; ?> ' + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                return label;
                            }
                        }
                    }
                }
            });
        }
    });
</script>
</body>
</html>