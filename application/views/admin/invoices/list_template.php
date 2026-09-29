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

                // Invoices use 'date' column
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
                if (!has_permission('invoices', '', 'view')) {
                    $where_own['addedfrom'] = get_staff_user_id();
                }
                
                $final_where = array_merge($where_own, $date_where);
                
                // Invoice Statuses: 1=Unpaid, 2=Paid, 3=Partially, 4=Overdue, 5=Cancelled, 6=Draft
                // We use the $invoices_statuses variable usually passed to this view
                if(!isset($invoices_statuses)) {
                    $invoices_statuses = [1,2,3,4,5,6]; // Fallback if not set
                }
            ?>

            <div class="row">
                <div class="col-md-6">
                    <h4 class="no-margin pull-left">
                        <?php echo _l('invoices'); ?> Analytics
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
                    <a href="#" onclick="$('#invoices_analytics').slideToggle(); return false;" class="text-muted"><i class="fa fa-angle-up fa-lg"></i></a>
                </div>
            </div>
            
            <hr class="hr-panel-heading" />

            <div id="invoices_analytics">
                
                <div class="row">
                    <?php
                    $total_period_invoices = total_rows(db_prefix() . 'invoices', $final_where);
                    $status_financials = [];

                    foreach ($invoices_statuses as $status) {
                        $where = array_merge(['status' => $status], $final_where);
                        $count = total_rows(db_prefix() . 'invoices', $where);
                        $percent = ($total_period_invoices > 0) ? ($count / $total_period_invoices) * 100 : 0;
                        
                        // Calculate Money (Total)
                        $CI->db->select_sum('total');
                        $CI->db->where($where);
                        $sum_query = $CI->db->get(db_prefix() . 'invoices')->row();
                        $status_financials[$status] = $sum_query->total ?? 0;

                        // Color Logic (Standard Perfex Colors)
                        // 1=Unpaid(Red), 2=Paid(Green), 3=Partially(Orange), 4=Overdue(Orange), 5=Cancelled(Grey), 6=Draft(Grey)
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
                            <div class="progress no-margin mtop10 progress-bar-mini" style="height:6px;">
                                <div class="progress-bar <?php echo $bar_color; ?>" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <hr class="hr-panel-heading" />

                <div class="row mtop20">
                    <div class="col-md-8">
                        <p class="text-muted font-medium-xs text-uppercase mbot10">
                            Invoicing Trend 
                            <?php if($selected_period == 'all_time' || $selected_period == 'this_year'){ ?>
                                <span class="label label-default mleft5">Confidence: 20%</span>
                            <?php } ?>
                        </p>
                        <div style="height: 300px;"><canvas id="invoicesTrendChart"></canvas></div>
                    </div>
                    
                    <div class="col-md-4">
                         <p class="text-muted font-medium-xs text-uppercase mbot10">Financial Summary</p>
                        <ul class="list-group">
                            <?php 
                            $grand_total = 0;
                            // Sort so Paid/Unpaid are at top if possible, or just loop
                            foreach($invoices_statuses as $status){ 
                                $val = $status_financials[$status];
                                $grand_total += $val;
                                if($val == 0) continue; 
                            ?>
                            <li class="list-group-item">
                                <?php echo format_invoice_status($status, '', false); ?>
                                <span class="pull-right"><?php echo app_format_money($val, $base_currency); ?></span>
                            </li>
                            <?php } ?>
                            <li class="list-group-item list-group-item-info">
                                <span class="bold">Total Invoiced</span>
                                <span class="pull-right bold"><?php echo app_format_money($grand_total, $base_currency); ?></span>
                            </li>
                        </ul>
                        
                        <?php
                            // Calculate simple Paid vs Unpaid for a quick snapshot
                            $paid_val = $status_financials[2] ?? 0; // Status 2 is Paid
                            $due_val = ($status_financials[1] ?? 0) + ($status_financials[3] ?? 0) + ($status_financials[4] ?? 0); // Unpaid + Partial + Overdue
                        ?>
                        <div class="row mtop15">
                             <div class="col-md-6">
                                 <div class="text-center p10" style="background:#f0fbf0; border:1px solid #4caf50;">
                                     <h4 class="bold text-success no-margin"><?php echo app_format_money($paid_val, $base_currency); ?></h4>
                                     <span class="text-success text-uppercase font-medium-xs">Collected</span>
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="text-center p10" style="background:#fffcf0; border:1px solid #f0ad4e;">
                                     <h4 class="bold text-warning no-margin"><?php echo app_format_money($due_val, $base_currency); ?></h4>
                                     <span class="text-warning text-uppercase font-medium-xs">Outstanding</span>
                                 </div>
                             </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="panel_s mbot10">
      <div class="panel-body _buttons">
         
         <?php if(has_permission('invoices','','create')){ ?>
            <a href="<?php echo admin_url('invoices/invoice'); ?>" class="btn btn-info pull-left new new-invoice-list mright5"><?php echo _l('create_new_invoice'); ?></a>
         <?php } ?>
         
         <a href="<?php echo admin_url('invoices/recurring'); ?>" class="btn btn-info pull-left mright5">
            <?php echo _l('invoices_list_recurring'); ?>
         </a>

         <div class="display-block text-right">
            <div class="btn-group pull-right mleft4 invoice-view-buttons btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fa fa-filter" aria-hidden="true"></i>
               </button>
               <ul class="dropdown-menu width300">
                  <li>
                     <a href="#" data-cview="all" onclick="dt_custom_view('','.table-invoices',''); return false;">
                     <?php echo _l('invoices_list_all'); ?>
                     </a>
                  </li>
                  <li class="divider"></li>
                  <li class="<?php if($this->input->get('filter') == 'not_sent'){echo 'active';} ?>">
                     <a href="#" data-cview="not_sent" onclick="dt_custom_view('not_sent','.table-invoices','not_sent'); return false;">
                     <?php echo _l('not_sent_indicator'); ?>
                     </a>
                  </li>
                  <li>
                     <a href="#" data-cview="not_have_payment" onclick="dt_custom_view('not_have_payment','.table-invoices','not_have_payment'); return false;">
                     <?php echo _l('invoices_list_not_have_payment'); ?>
                     </a>
                  </li>
                  <li>
                     <a href="#" data-cview="recurring" onclick="dt_custom_view('recurring','.table-invoices','recurring'); return false;">
                     <?php echo _l('invoices_list_recurring'); ?>
                     </a>
                  </li>
                  <li class="divider"></li>
                  <?php foreach($invoices_statuses as $status){ ?>
                  <li class="<?php if($status == $this->input->get('status')){echo 'active';} ?>">
                     <a href="#" data-cview="invoices_<?php echo $status; ?>" onclick="dt_custom_view('invoices_<?php echo $status; ?>','.table-invoices','invoices_<?php echo $status; ?>'); return false;"><?php echo format_invoice_status($status,'',false); ?></a>
                  </li>
                  <?php } ?>
                  <?php if(count($invoices_years) > 0){ ?>
                  <li class="divider"></li>
                  <?php foreach($invoices_years as $year){ ?>
                  <li class="active">
                     <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-invoices','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php } ?>
                  <?php if(count($invoices_sale_agents) > 0){ ?>
                  <div class="clearfix"></div>
                  <li class="divider"></li>
                  <li class="dropdown-submenu pull-left">
                     <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                     <ul class="dropdown-menu dropdown-menu-left">
                        <?php foreach($invoices_sale_agents as $agent){ ?>
                        <li>
                           <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view(<?php echo $agent['sale_agent']; ?>,'.table-invoices','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo $agent['full_name']; ?>
                           </a>
                        </li>
                        <?php } ?>
                     </ul>
                  </li>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <?php if(count($payment_modes) > 0){ ?>
                  <li class="divider"></li>
                  <?php } ?>
                  <?php foreach($payment_modes as $mode){
                     if(total_rows(db_prefix().'invoicepaymentrecords',array('paymentmode'=>$mode['id'])) == 0){continue;}
                     ?>
                  <li>
                     <a href="#" data-cview="invoice_payments_by_<?php echo $mode['id']; ?>" onclick="dt_custom_view('<?php echo $mode['id']; ?>','.table-invoices','invoice_payments_by_<?php echo $mode['id']; ?>'); return false;">
                     <?php echo _l('invoices_list_made_payment_by',$mode['name']); ?>
                     </a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-invoices','#invoice'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md-12" id="small-table">
         <div class="panel_s">
            <div class="panel-body">
               <?php echo form_hidden('invoiceid',$invoiceid); ?>
               <?php $this->load->view('admin/invoices/table_html'); ?>
            </div>
         </div>
      </div>
      <div class="col-md-7 small-table-right-col">
         <div id="invoice" class="hide">
         </div>
      </div>
   </div>
</div>

<?php
    // 1. Fetch Data
    $CI->db->select('date');
    if (!empty($final_where)) { $CI->db->where($final_where); }
    $CI->db->order_by('date', 'ASC');
    $all_dates = $CI->db->get(db_prefix().'invoices')->result_array();

    // 2. Aggregate
    $temp_chart_data = [];
    foreach($all_dates as $row){
        if(!$row['date']) continue;
        $key = ($selected_period == 'this_month' || $selected_period == 'last_month') 
               ? date('d M', strtotime($row['date'])) 
               : date('Y-m', strtotime($row['date']));
        
        if(!isset($temp_chart_data[$key])) $temp_chart_data[$key] = 0;
        $temp_chart_data[$key]++;
    }
    
    $hist_labels = array_keys($temp_chart_data);
    $hist_values = array_values($temp_chart_data);
    $n = count($hist_values);

    // 3. Forecasting Logic (Linear Regression)
    $forecast_values = [];
    $upper_bound = [];
    $lower_bound = [];
    $future_labels = [];
    
    $enable_forecast = ($n > 1 && $selected_period != 'this_month' && $selected_period != 'last_month');
    
    if ($enable_forecast) {
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $x = $i; $y = $hist_values[$i];
            $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumXX += ($x * $x);
        }
        
        $m = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumXX) - ($sumX * $sumX));
        $b = ($sumY - ($m * $sumX)) / $n;
        
        $forecast_months = 6;
        $history_pad = $hist_values; 
        
        for ($i = 0; $i < ($n + $forecast_months); $i++) {
            if($i < $n) {
                $future_labels[] = $hist_labels[$i];
                $forecast_values[] = null; $upper_bound[] = null; $lower_bound[] = null;
            } else {
                $future_labels[] = "Forecast +" . ($i - $n + 1); 
                $history_pad[] = null;
                $predicted_y = max(0, ($m * $i) + $b);
                $forecast_values[] = $predicted_y;
                $upper_bound[] = $predicted_y * 1.2;
                $lower_bound[] = $predicted_y * 0.8;
            }
        }
    } else {
        $history_pad = $hist_values;
        $future_labels = $hist_labels;
    }
?>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var ctx = document.getElementById('invoicesTrendChart');
        if(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($future_labels); ?>,
                    datasets: [
                        {
                            label: '<?php echo _l('invoices'); ?> (Actual)',
                            backgroundColor: '#1e293b',
                            borderColor: '#1e293b',
                            data: <?php echo json_encode($history_pad); ?>,
                            tension: 0, fill: false,
                            pointRadius: 4, pointBackgroundColor: '#1e293b'
                        },
                        <?php if($enable_forecast): ?>
                        {
                            label: 'Forecast',
                            borderColor: '#b91c1c', borderDash: [5, 5],
                            data: <?php echo json_encode($forecast_values); ?>,
                            fill: false, pointRadius: 0, borderWidth: 2
                        },
                        {
                            label: 'Upper Conf.',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)', borderColor: 'transparent',
                            data: <?php echo json_encode($upper_bound); ?>,
                            fill: '+1', pointRadius: 0
                        },
                        {
                            label: 'Lower Conf.',
                            backgroundColor: 'rgba(30, 41, 59, 0.15)', borderColor: 'transparent',
                            data: <?php echo json_encode($lower_bound); ?>,
                            fill: '-1', pointRadius: 0
                        }
                        <?php endif; ?>
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    tooltips: { mode: 'index', intersect: false },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>