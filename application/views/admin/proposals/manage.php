<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        
                        <?php
                            $selected_period = $this->input->get('stats_period');
                            if(!$selected_period) $selected_period = 'all_time';

                            // $statuses = $proposal_statuses; // Removed: The controller passes named $statuses, this line was breaking it.

                            $date_where = [];
                            $period_text = 'All Time';

                            // Calculate Date Ranges
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
                            } elseif($selected_period == 'last_6_months'){
                                $date_where['date >='] = date('Y-m-d', strtotime('-6 months'));
                                $date_where['date <='] = date('Y-m-d');
                                $period_text = 'Last 6 Months';
                            }

                            // Base Permissions
                            $where_own = [];
                            if (!has_permission('proposals', '', 'view')) {
                                $where_own['addedfrom'] = get_staff_user_id();
                            }
                            
                            // Merge Date Filter with Permissions
                            $final_where = array_merge($where_own, $date_where);
                        ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin pull-left">
                                    <?php echo _l('proposals_summary'); ?> 
                                    <small class="text-success"> &bull; <?php echo $period_text; ?></small>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <form method="get" action="" class="form-inline display-inline-block mright10" style="vertical-align: top;">
                                    <?php 
                                    // Loop through existing GET params to keep table filters active while changing stats
                                    foreach($_GET as $key => $val){
                                        if($key == 'stats_period') continue;
                                        echo '<input type="hidden" name="'.$key.'" value="'.$val.'">';
                                    }
                                    ?>
                                    <select name="stats_period" class="selectpicker" data-width="150px" data-style="btn-default btn-xs" onchange="this.form.submit()">
                                        <option value="all_time" <?php if($selected_period == 'all_time'){echo 'selected';} ?>>All Time</option>
                                        <option value="this_month" <?php if($selected_period == 'this_month'){echo 'selected';} ?>>This Month</option>
                                        <option value="last_month" <?php if($selected_period == 'last_month'){echo 'selected';} ?>>Last Month</option>
                                        <option value="this_year" <?php if($selected_period == 'this_year'){echo 'selected';} ?>>This Year</option>
                                        <option value="last_year" <?php if($selected_period == 'last_year'){echo 'selected';} ?>>Last Year</option>
                                        <option value="last_6_months" <?php if($selected_period == 'last_6_months'){echo 'selected';} ?>>Last 6 Months</option>
                                    </select>
                                </form>
                                
                                <a href="#" onclick="$('#proposals_analytics').slideToggle(); return false;" class="text-muted" data-toggle="tooltip" title="Toggle Analytics">
                                    <i class="fa fa-angle-up fa-lg"></i>
                                </a>
                            </div>
                        </div>
                        
                        <hr class="hr-panel-heading" />
                        
                        <div id="proposals_analytics">
                            <div class="row">
                                <?php
                                $base_currency = get_base_currency();
                                $total_period_proposals = total_rows(db_prefix() . 'proposals', $final_where);
                                $status_financials = [];

                                // --- STATS CARDS LOOP ---
                                foreach ($statuses as $status) {
                                    $where = array_merge(['status' => $status], $final_where);
                                    $count = total_rows(db_prefix() . 'proposals', $where);
                                    
                                    $percent = ($total_period_proposals > 0) ? ($count / $total_period_proposals) * 100 : 0;
                                    
                                    $this->db->select('currency, sum(total) as total');
                                    $this->db->where($where);
                                    $this->db->group_by('currency');
                                    $sums = $this->db->get(db_prefix() . 'proposals')->result();
                                    $status_financials[$status] = $sums;

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

                            <hr class="hr-panel-heading" />

                            <div class="row mtop20">
                                <div class="col-md-8">
                                    <p class="text-muted font-medium-xs text-uppercase mbot10">
                                        Volume Trend: <span class="text-dark"><?php echo $period_text; ?></span>
                                        <?php if($selected_period == 'all_time' || $selected_period == 'last_6_months' || $selected_period == 'this_year'){ ?>
                                            <span class="label label-default mleft5">Confidence Interval: 20%</span>
                                        <?php } ?>
                                    </p>
                                    <div style="height: 300px;">
                                        <canvas id="proposalsTrendChart"></canvas>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-muted font-medium-xs text-uppercase mbot10">Pipeline Value (<?php echo $period_text; ?>)</p>
                                    <ul class="list-group">
                                        <?php 
                                        $grand_totals = [];
                                        foreach($statuses as $status){ 
                                            $amounts = $status_financials[$status];
                                            if(empty($amounts)) continue; 
                                        ?>
                                        <li class="list-group-item">
                                            <?php echo format_proposal_status($status, '', false); ?>
                                            <div class="pull-right text-right">
                                            <?php 
                                                foreach($amounts as $row){
                                                    $currency_amount = $row->total;
                                                    $currency_id = $row->currency;
                                                    if(!$currency_id) $currency_id = $base_currency->id; // Fallback
                                                    
                                                    $currency = get_currency($currency_id);
                                                    echo '<span class="display-block">' . app_format_money($currency_amount, $currency) . '</span>';
                                                    
                                                    if(!isset($grand_totals[$currency_id])) $grand_totals[$currency_id] = 0;
                                                    $grand_totals[$currency_id] += $currency_amount;
                                                }
                                            ?>
                                            </div>
                                        </li>
                                        <?php } ?>
                                        <li class="list-group-item list-group-item-info">
                                            <span class="bold">Total Pipeline</span>
                                            <div class="pull-right bold text-right">
                                            <?php
                                                foreach($grand_totals as $currency_id => $total){
                                                    $currency = get_currency($currency_id);
                                                    echo '<span class="display-block">' . app_format_money($total, $currency) . '</span>';
                                                }
                                            ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="_filters _hidden_inputs">
                <?php
                foreach ($statuses as $_status) {
                    $val = '';
                    if ($_status == $this->input->get('status')) { $val = $_status; }
                    echo form_hidden('proposals_' . $_status, $val);
                }
                foreach ($years as $year) { echo form_hidden('year_' . $year['year'], $year['year']); }
                foreach ($proposals_sale_agents as $agent) { echo form_hidden('sale_agent_' . $agent['sale_agent']); }
                echo form_hidden('leads_related');
                echo form_hidden('customers_related');
                echo form_hidden('expired');
                ?>
            </div>
            <div class="col-md-12">
                <div class="panel_s mbot10">
                    <div class="panel-body _buttons">
                        <?php if (has_permission('proposals', '', 'create')) { ?>
                            <a href="<?php echo admin_url('proposals/proposal'); ?>" class="btn btn-info pull-left display-block"><?php echo 'New Sale Quotes'; ?></a>
                        <?php } ?>
                        <a href="<?php echo admin_url('proposals/pipeline/' . $switch_pipeline); ?>" class="btn btn-default mleft5 pull-left hidden-xs"><?php echo _l('switch_to_pipeline'); ?></a>
                        <div class="display-block text-right">
                             <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-filter" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu width300">
                                    <li><a href="#" data-cview="all" onclick="dt_custom_view('','.table-proposals',''); return false;"><?php echo _l('proposals_list_all'); ?></a></li>
                                    <li class="divider"></li>
                                    <?php foreach ($statuses as $status) { ?>
                                        <li class="<?php if ($this->input->get('status') == $status) { echo 'active'; } ?>">
                                            <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-proposals','proposals_<?php echo $status; ?>'); return false;"><?php echo format_proposal_status($status, '', false); ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-proposals','#proposal'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="small-table">
                        <div class="panel_s">
                            <div class="panel-body">
                                <?php echo form_hidden('proposal_id', $proposal_id); ?>
                                <?php
                                $table_data = array(_l('proposal') . ' #', _l('proposal_date'), _l('proposal_open_till'), _l('proposal_to'), _l('proposal_subject'), _l('proposal_total'), _l('tags'), _l('proposal_date_created'), _l('proposal_status'));
                                $custom_fields = get_custom_fields('proposal', array('show_on_table' => 1));
                                foreach ($custom_fields as $field) { array_push($table_data, $field['name']); }
                                $table_data = hooks()->apply_filters('proposals_table_columns', $table_data);
                                render_datatable($table_data, 'proposals', [], ['data-last-order-identifier' => 'proposals', 'data-default-order' => get_table_last_order('proposals')]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 small-table-right-col">
                        <div id="proposal" class="hide"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [4, 5, 6, 7];</script>

<?php
    // 1. Fetch Data with Date Filters
    $this->db->select('date');
    if (!empty($final_where)) { $this->db->where($final_where); }
    $this->db->order_by('date', 'ASC');
    $all_dates = $this->db->get(db_prefix().'proposals')->result_array();

    // 2. Aggregate
    $temp_chart_data = [];
    foreach($all_dates as $row){
        if(!$row['date']) continue;
        // If range is small (e.g. this month), we group by Day instead of Month
        if($selected_period == 'this_month' || $selected_period == 'last_month'){
             $key = date('d M', strtotime($row['date']));
        } else {
             $key = date('Y-m', strtotime($row['date']));
        }
        
        if(!isset($temp_chart_data[$key])) $temp_chart_data[$key] = 0;
        $temp_chart_data[$key]++;
    }
    
    $hist_labels = array_keys($temp_chart_data);
    $hist_values = array_values($temp_chart_data);
    $n = count($hist_values);

    // 3. Linear Regression (Only run if we have enough data points)
    $forecast_values = [];
    $upper_bound = [];
    $lower_bound = [];
    $future_labels = [];
    
    // Only forecast if not viewing a single month (forecasting on days is noisy)
    $enable_forecast = ($n > 1 && $selected_period != 'this_month' && $selected_period != 'last_month');
    
    if ($enable_forecast) {
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $hist_values[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumXX += ($x * $x);
        }
        
        $m = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumXX) - ($sumX * $sumX));
        $b = ($sumY - ($m * $sumX)) / $n;
        
        $forecast_months = 6;
        $confidence_interval = 0.20; 
        
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
                $last_date_str = end($hist_labels);
                // Simple string addition for label projection
                $future_labels[] = "Forecast +" . ($i - $n + 1); 
                $history_pad[] = null;
                
                $predicted_y = ($m * $i) + $b;
                $predicted_y = max(0, $predicted_y);
                
                $forecast_values[] = $predicted_y;
                $upper_bound[] = $predicted_y * (1 + $confidence_interval);
                $lower_bound[] = $predicted_y * (1 - $confidence_interval);
            }
        }
    } else {
        // No forecast for short periods
        $history_pad = $hist_values;
        $future_labels = $hist_labels;
    }
?>

<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
    var proposal_id;
    $(function() {
        var Proposals_ServerParams = {};
        $.each($('._hidden_inputs._filters input'), function() {
            Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'], Proposals_ServerParams, [7, 'desc']);
        init_proposal();

        // --- CHART JS ---
        var ctx = document.getElementById('proposalsTrendChart');
        if(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($future_labels); ?>,
                    datasets: [
                        {
                            label: '<?php echo _l('proposals'); ?> (Actual)',
                            backgroundColor: '#1e293b', 
                            borderColor: '#1e293b',
                            data: <?php echo json_encode($history_pad); ?>,
                            fill: false,
                            tension: 0,
                            pointRadius: 4,
                            pointBackgroundColor: '#1e293b'
                        },
                        <?php if($enable_forecast): ?>
                        {
                            label: 'Forecast Trend',
                            borderColor: '#b91c1c', 
                            borderDash: [5, 5],
                            data: <?php echo json_encode($forecast_values); ?>,
                            fill: false,
                            pointRadius: 0,
                            borderWidth: 2
                        },
                        {
                            label: 'Upper Confidence (20%)',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)',
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
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'No. of Proposals' }
                        }
                    }
                }
            });
        }
    });
</script>
</body>
</html>